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

    public function createSoapRequest(RequestBuilderInterface $requestBuilder, QueryParameters $parameters): string
    {
        return $requestBuilder->query($parameters);
    }
}
