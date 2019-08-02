<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Translators;

use PhpCfdi\SatWsDescargaMasiva\DateTime;
use PhpCfdi\SatWsDescargaMasiva\DownloadRequestQuery;
use PhpCfdi\SatWsDescargaMasiva\DownloadRequestResult;
use PhpCfdi\SatWsDescargaMasiva\Enums\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Enums\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Traits\InteractsXmlTrait;

/** @internal */
class DownloadRequestTranslator
{
    use InteractsXmlTrait;

    public function createDownloadRequestResultFromSoapResponse(string $content): DownloadRequestResult
    {
        $env = $this->readXmlElement($content);

        $values = $this->findAttributes($env, 'body', 'solicitaDescargaResponse', 'solicitaDescargaResult');
        $requestId = $values['idsolicitud'] ?? '';
        $statusCode = intval($values['codestatus'] ?? 0);
        $message = $values['mensaje'] ?? '';
        return new DownloadRequestResult($requestId, $statusCode, $message);
    }

    public function createSoapRequest(Fiel $fiel, DownloadRequestQuery $downloadRequestQuery): string
    {
        $dateTimePeriod = $downloadRequestQuery->getDateTimePeriod();

        return $this->createSoapRequestWithData(
            $fiel,
            $fiel->getRfc(),
            $dateTimePeriod->getStart(),
            $dateTimePeriod->getEnd(),
            $downloadRequestQuery->getDownloadType(),
            $downloadRequestQuery->getRequestType()
        );
    }

    public function createSoapRequestWithData(
        Fiel $fiel,
        string $rfc,
        DateTime $start,
        DateTime $end,
        DownloadType $downloadType,
        RequestType $requestType
    ): string {
        $start = $start->format('Y-m-d\TH:i:s');
        $end = $end->format('Y-m-d\TH:i:s');

        $rfcKey = $downloadType->value();
        $requestTypeValue = $requestType->value();

        $toDigest = $this->nospaces(
            <<<EOT
<des:SolicitaDescarga xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
    <des:solicitud ${rfcKey}="${rfc}" RfcSolicitante="${rfc}" FechaInicial="${start}" FechaFinal="${end}" TipoSolicitud="${requestTypeValue}"></des:solicitud>
</des:SolicitaDescarga>
EOT
        );
        $digested = base64_encode(sha1(str_replace(PHP_EOL, '', $toDigest), true));

        $toSign = $this->nospaces(
            <<<EOT
<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
  <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"></CanonicalizationMethod>
  <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod>
  <Reference URI="">
    <Transforms>
      <Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"></Transform>
    </Transforms>
    <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod>
    <DigestValue>${digested}</DigestValue>
  </Reference>
</SignedInfo>
EOT
        );
        $signed = base64_encode($fiel->sign($toSign, OPENSSL_ALGO_SHA1));

        $certificate = Helpers::cleanPemContents($fiel->getCertificatePemContents());
        $serial = $fiel->getCertificateSerial();
        $issuerName = $fiel->getCertificateIssuerName();

        $xml = <<<EOT
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
	<s:Header/>
	<s:Body>
		<des:SolicitaDescarga>
			<des:solicitud ${rfcKey}="${rfc}" RfcSolicitante="${rfc}" FechaFinal="${end}" FechaInicial="${start}" TipoSolicitud="${requestType}">
				<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
					<SignedInfo>
						<CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></CanonicalizationMethod>
						<SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod>
						<Reference URI="#_0">
							<Transforms>
								<Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></Transform>
							</Transforms>
							<DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod>
							<DigestValue>${digested}</DigestValue>
						</Reference>
					</SignedInfo>
					<SignatureValue>${signed}</SignatureValue>
					<KeyInfo>
						<X509Data>
							<X509IssuerSerial>
								<X509IssuerName>${issuerName}</X509IssuerName>
								<X509SerialNumber>${serial}</X509SerialNumber>
							</X509IssuerSerial>
							<X509Certificate>${certificate}</X509Certificate>
						</X509Data>
					</KeyInfo>
				</Signature>
			</des:solicitud>
		</des:SolicitaDescarga>
	</s:Body>
</s:Envelope>
EOT;

        return $this->nospaces($xml);
    }
}
