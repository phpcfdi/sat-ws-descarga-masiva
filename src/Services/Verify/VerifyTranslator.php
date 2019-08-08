<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Verify;

use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Shared\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Traits\InteractsXmlTrait;

class VerifyTranslator
{
    use InteractsXmlTrait;

    public function createVerifyDownloadRequestResultFromSoapResponse(string $content): VerifyResult
    {
        $env = $this->readXmlElement($content);

        $values = $this->findAttributes(
            $env,
            ...['body', 'VerificaSolicitudDescargaResponse', 'VerificaSolicitudDescargaResult']
        );
        $statusCode = intval($values['codestatus'] ?? 0);
        $requestStatus = intval($values['estadosolicitud'] ?? 0);
        $statusRequestCode = intval($values['codigoestadosolicitud'] ?? 0);
        $numberCfdis = intval($values['numerocfdis'] ?? 0);
        $message = $values['mensaje'] ?? '';
        $packages = $this->findContents(
            $env,
            ...['body', 'VerificaSolicitudDescargaResponse', 'VerificaSolicitudDescargaResult', 'IdsPaquetes']
        );
        return new VerifyResult(
            $statusCode,
            $requestStatus,
            $statusRequestCode,
            $numberCfdis,
            $message,
            $packages
        );
    }

    public function createSoapRequest(Fiel $fiel, string $requestId): string
    {
        return $this->createSoapRequestWithData($fiel, $fiel->getRfc(), $requestId);
    }

    public function createSoapRequestWithData(
        Fiel $fiel,
        string $rfc,
        string $requestId
    ): string {
        $toDigest = $this->nospaces(
            <<<EOT
<des:VerificaSolicitudDescarga xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
    <des:solicitud IdSolicitud="${requestId}" RfcSolicitante="${rfc}"></des:solicitud>
</des:VerificaSolicitudDescarga>
EOT
        );
        $digested = base64_encode(sha1(str_replace(PHP_EOL, '', $toDigest), true));

        $toSign = $this->nospaces(
            <<<EOT
<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
	<CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></CanonicalizationMethod>
	<SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod>
	<Reference URI="">
		<Transforms>
			<Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></Transform>
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
		<des:VerificaSolicitudDescarga>
			<des:solicitud IdSolicitud="${requestId}" RfcSolicitante="${rfc}">
				<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
					<SignedInfo>
						<CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
						<SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
						<Reference URI="">
							<Transforms>
								<Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
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
		</des:VerificaSolicitudDescarga>
	</s:Body>
</s:Envelope>
EOT;

        return $this->nospaces($xml);
    }
}
