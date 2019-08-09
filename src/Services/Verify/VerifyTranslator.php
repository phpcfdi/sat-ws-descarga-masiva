<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Verify;

use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Shared\InteractsXmlTrait;

class VerifyTranslator
{
    use InteractsXmlTrait;

    public function createVerifyResultFromSoapResponse(string $content): VerifyResult
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
        return new VerifyResult($statusCode, $requestStatus, $statusRequestCode, $numberCfdis, $message, ...$packages);
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
        $digested = base64_encode(sha1($toDigest, true));
        $signedInfoData = $this->createSignedInfoCanonicalExclusive($digested);
        $signed = base64_encode($fiel->sign($signedInfoData, OPENSSL_ALGO_SHA1));
        $keyInfoData = $this->createKeyInfoData($fiel);
        $signatureData = $this->createSignatureData($signedInfoData, $signed, $keyInfoData);
        $xml = <<<EOT
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
                <s:Header/>
                <s:Body>
                    <des:VerificaSolicitudDescarga>
                        <des:solicitud IdSolicitud="${requestId}" RfcSolicitante="${rfc}">
                            ${signatureData}
                        </des:solicitud>
                    </des:VerificaSolicitudDescarga>
                </s:Body>
            </s:Envelope>
EOT;

        return $this->nospaces($xml);
    }
}
