<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Download;

use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Shared\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Shared\InteractsXmlTrait;

class DownloadTranslator
{
    use InteractsXmlTrait;

    public function createDownloadResultFromSoapResponse(string $content): DownloadResult
    {
        $env = $this->readXmlElement($content);

        $values = $this->findAttributes(
            $env,
            ...['header', 'respuesta']
        );
        $statusCode = intval($values['codestatus'] ?? 0);
        $message = $values['mensaje'] ?? '';
        $package = $this->findContent(
            $env,
            ...['body', 'RespuestaDescargaMasivaTercerosSalida', 'Paquete']
        );
        return new DownloadResult(
            $statusCode,
            $message,
            $package
        );
    }

    public function createSoapRequest(Fiel $fiel, string $packageId): string
    {
        return $this->createSoapRequestWithData($fiel, $fiel->getRfc(), $packageId);
    }

    public function createSoapRequestWithData(
        Fiel $fiel,
        string $rfc,
        string $packageId
    ): string {
        $toDigest = $this->nospaces(
            <<<EOT
<des:PeticionDescargaMasivaTercerosEntrada xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
    <des:peticionDescarga IdPaquete="${packageId}" RfcSolicitante="${rfc}"></des:peticionDescarga>
</des:PeticionDescargaMasivaTercerosEntrada>
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
		<des:PeticionDescargaMasivaTercerosEntrada>
			<des:peticionDescarga IdPaquete="${packageId}" RfcSolicitante="${rfc}">
				<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
					<SignedInfo>
						<CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
						<SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
						<Reference URI="">
							<Transforms>
								<Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/></Transforms>
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
			</des:peticionDescarga>
		</des:PeticionDescargaMasivaTercerosEntrada>
	</s:Body>
</s:Envelope>
EOT;

        return $this->nospaces($xml);
    }
}
