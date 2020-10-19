<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Download;

use PhpCfdi\SatWsDescargaMasiva\Internal\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

/** @internal */
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

    public function createSoapRequest(RequestBuilderInterface $requestBuilder, string $packageId): string
    {
        return $requestBuilder->download($packageId);
    }
}
