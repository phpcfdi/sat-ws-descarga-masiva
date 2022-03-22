<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Internal\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

/** @internal */
class QueryTranslator
{
    use InteractsXmlTrait;

    public function createQueryResultFromSoapResponse(string $content): QueryResult
    {
        $env = $this->readXmlElement($content);

        $values = $this->findAttributes($env, 'body', 'solicitaDescargaResponse', 'solicitaDescargaResult');
        $status = new StatusCode(intval($values['codestatus'] ?? 0), strval($values['mensaje'] ?? ''));
        $requestId = strval($values['idsolicitud'] ?? '');
        return new QueryResult($status, $requestId);
    }

    public function createSoapRequest(
        RequestBuilderInterface $requestBuilder,
        QueryParameters $parameters,
        bool $isRetenciones
    ): string {
        $start = $parameters->getPeriod()->getStart()->format('Y-m-d\TH:i:s');
        $end = $parameters->getPeriod()->getEnd()->format('Y-m-d\TH:i:s');
        $rfcIssuer = $parameters->getDownloadType()->isIssued() ? RequestBuilderInterface::USE_SIGNER : $parameters->getRfcMatch();
        $rfcReceiver = $parameters->getDownloadType()->isReceived() ? RequestBuilderInterface::USE_SIGNER : $parameters->getRfcMatch();
        $requestType = $parameters->getRequestType()->value();
        if ($parameters->getRequestType()->isCfdi() && $isRetenciones) {
            $requestType = 'Retencion';
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $requestBuilder->query($start, $end, $rfcIssuer, $rfcReceiver, $requestType);
    }
}
