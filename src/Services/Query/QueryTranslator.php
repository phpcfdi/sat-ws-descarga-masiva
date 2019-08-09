<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Shared\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
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

    public function createSoapRequest(Fiel $fiel, QueryParameters $parameters): string
    {
        $dateTimePeriod = $parameters->getDateTimePeriod();

        return $this->createSoapRequestWithData(
            $fiel,
            $fiel->getRfc(),
            $dateTimePeriod->getStart(),
            $dateTimePeriod->getEnd(),
            $parameters->getDownloadType(),
            $parameters->getRequestType()
        );
    }

    public function createSoapRequestWithData(
        Fiel $fiel,
        string $rfc,
        DateTime $start,
        DateTime $end,
        DownloadType $downloadType,
        RequestType $requestType
    ): string {
        $start = $start->format('Y-m-d\TH:i:s');
        $end = $end->format('Y-m-d\TH:i:s');

        $rfcKey = $downloadType->value();
        $requestTypeValue = $requestType->value();

        $toDigest = $this->nospaces(
            <<<EOT
            <des:SolicitaDescarga xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
                <des:solicitud FechaFinal="${end}" FechaInicial="${start}" ${rfcKey}="${rfc}" RfcSolicitante="${rfc}" TipoSolicitud="${requestTypeValue}"></des:solicitud>
            </des:SolicitaDescarga>
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
                    <des:SolicitaDescarga>
                        <des:solicitud FechaFinal="${end}" FechaInicial="${start}" ${rfcKey}="${rfc}" RfcSolicitante="${rfc}" TipoSolicitud="${requestTypeValue}">
                            ${signatureData}
                        </des:solicitud>
                    </des:SolicitaDescarga>
                </s:Body>
            </s:Envelope>
EOT;

        return $this->nospaces($xml);
    }
}
