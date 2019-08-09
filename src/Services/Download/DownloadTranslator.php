<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Download;

use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Shared\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

class DownloadTranslator
{
    use InteractsXmlTrait;

    public function createDownloadResultFromSoapResponse(string $content): DownloadResult
    {
        $env = $this->readXmlElement($content);
        $values = $this->findAttributes($env, 'header', 'respuesta');
        $status = new StatusCode(intval($values['codestatus'] ?? 0), strval($values['mensaje'] ?? ''));
        $package = $this->findContent($env, 'body', 'RespuestaDescargaMasivaTercerosSalida', 'Paquete');
        return new DownloadResult($status, base64_decode($package, true) ?: '');
    }

    public function createSoapRequest(Fiel $fiel, string $packageId): string
    {
        return $this->createSoapRequestWithData($fiel, $fiel->getRfc(), $packageId);
    }

    public function createSoapRequestWithData(Fiel $fiel, string $rfc, string $packageId): string
    {
        $toDigest = $this->nospaces(
            <<<EOT
            <des:PeticionDescargaMasivaTercerosEntrada xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
                <des:peticionDescarga IdPaquete="${packageId}" RfcSolicitante="${rfc}"></des:peticionDescarga>
            </des:PeticionDescargaMasivaTercerosEntrada>
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
                    <des:PeticionDescargaMasivaTercerosEntrada>
                        <des:peticionDescarga IdPaquete="${packageId}" RfcSolicitante="${rfc}">
                            ${signatureData}
                        </des:peticionDescarga>
                    </des:PeticionDescargaMasivaTercerosEntrada>
                </s:Body>
            </s:Envelope>
EOT;
        return $this->nospaces($xml);
    }
}
