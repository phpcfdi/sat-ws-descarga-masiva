<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Verify;

use PhpCfdi\SatWsDescargaMasiva\Internal\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Shared\CodeRequest;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusRequest;

/** @internal */
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
        $status = new StatusCode(intval($values['codestatus'] ?? 0), strval($values['mensaje'] ?? ''));
        $statusRequest = new StatusRequest(intval($values['estadosolicitud'] ?? 0));
        $codeRequest = new CodeRequest(intval($values['codigoestadosolicitud'] ?? 0));
        $numberCfdis = intval($values['numerocfdis'] ?? 0);
        $packages = $this->findContents(
            $env,
            ...['body', 'VerificaSolicitudDescargaResponse', 'VerificaSolicitudDescargaResult', 'IdsPaquetes']
        );

        return new VerifyResult($status, $statusRequest, $codeRequest, $numberCfdis, ...$packages);
    }

    public function createSoapRequest(RequestBuilderInterface $requestBuilder, string $requestId): string
    {
        return $requestBuilder->verify($requestId);
    }
}
