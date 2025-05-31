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
        return match (true) {
            null !== $this->findElement($envelope, 'body', 'solicitaDescargaEmitidosResponse')
                => ['body', 'solicitaDescargaEmitidosResponse', 'solicitaDescargaEmitidosResult'],
            null !== $this->findElement($envelope, 'body', 'solicitaDescargaRecibidosResponse')
                => ['body', 'solicitaDescargaRecibidosResponse', 'solicitaDescargaRecibidosResult'],
            null !== $this->findElement($envelope, 'body', 'SolicitaDescargaFolioResponse')
                => ['body', 'SolicitaDescargaFolioResponse', 'SolicitaDescargaFolioResult'],
            default => [], // throw an InvalidArgumentException ?
        };
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
