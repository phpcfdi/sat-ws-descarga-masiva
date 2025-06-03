<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use DOMElement;
use PhpCfdi\SatWsDescargaMasiva\Internal\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

/** @internal */
class QueryTranslator
{
    use InteractsXmlTrait;

    /** @return list<string> */
    private function resolveResponsePath(DOMElement $envelope): array
    {
        if (null !== $this->findElement($envelope, 'body', 'solicitaDescargaEmitidosResponse')) {
            return ['body', 'solicitaDescargaEmitidosResponse', 'solicitaDescargaEmitidosResult'];
        }
        if (null !== $this->findElement($envelope, 'body', 'solicitaDescargaRecibidosResponse')) {
            return ['body', 'solicitaDescargaRecibidosResponse', 'solicitaDescargaRecibidosResult'];
        }
        if (null !== $this->findElement($envelope, 'body', 'SolicitaDescargaFolioResponse')) {
            return ['body', 'SolicitaDescargaFolioResponse', 'SolicitaDescargaFolioResult'];
        }
        return [];
    }

    public function createQueryResultFromSoapResponse(string $content): QueryResult
    {
        $env = $this->readXmlElement($content);
        $path = $this->resolveResponsePath($env);

        $values = $this->findAttributes($env, ...$path);
        $status = new StatusCode(intval($values['codestatus'] ?? 0), strval($values['mensaje'] ?? ''));
        $requestId = strval($values['idsolicitud'] ?? '');
        return new QueryResult($status, $requestId);
    }

    public function createSoapRequest(RequestBuilderInterface $requestBuilder, QueryParameters $parameters): string
    {
        return $requestBuilder->query($parameters);
    }
}
