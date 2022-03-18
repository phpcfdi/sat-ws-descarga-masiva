<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoCfdi;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;

/**
 * @todo Parameter ComplementoCfdi is failing, enable when SAT is working
 */
final class ConsumeCfdiServicesUsingFakeFielTest extends ConsumeServiceTestCase
{
    protected function getServiceEndpoints(): ServiceEndpoints
    {
        return ServiceEndpoints::cfdi();
    }

    public function testQueryChangeAllParameters(): void
    {
        $service = $this->createService();

        $parameters = QueryParameters::create()
            ->withPeriod(DateTimePeriod::createFromValues('2019-01-01 00:00:00', '2019-01-01 00:04:00'))
            ->withDownloadType(DownloadType::received())
            ->withRequestType(RequestType::cfdi())
            ->withDocumentType(DocumentType::nomina())
            ->withComplement(ComplementoCfdi::undefined())
            ->withDocumentStatus(DocumentStatus::active())
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
            ->withRfcOnBehalf(RfcOnBehalf::create('XXX01010199A'))
            ->withRfcMatch(RfcMatch::create('AAA010101AAA'))
        ;

        $result = $service->query($parameters);
        $this->assertSame(
            305,
            $result->getStatus()->getCode(),
            'Expected to receive a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }
}
