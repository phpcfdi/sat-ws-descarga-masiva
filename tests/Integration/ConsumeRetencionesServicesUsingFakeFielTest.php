<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoRetenciones;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;

/**
 * @todo Parameter ComplementoRetenciones is failing, enable when SAT is working
 */
final class ConsumeRetencionesServicesUsingFakeFielTest extends ConsumeServiceTestCase
{
    protected function getServiceEndpoints(): ServiceEndpoints
    {
        return ServiceEndpoints::retenciones();
    }

    public function testQueryChangeFilters(): void
    {
        $service = $this->createService();

        $startDate = DateTime::now()->modify('first day of last month midnight');
        $endDate = $startDate->modify('+5 days');
        $period = DateTimePeriod::create($startDate, $endDate);

        $parameters = QueryParameters::create()
            ->withPeriod($period)
            ->withDownloadType(DownloadType::received())
            ->withRequestType(RequestType::xml())
            ->withComplement(ComplementoRetenciones::undefined())
            ->withDocumentStatus(DocumentStatus::active())
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

    public function testQueryByUuid(): void
    {
        $service = $this->createService();

        $parameters = QueryParameters::create()
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
        ;

        $result = $service->query($parameters);
        $this->assertSame(
            305,
            $result->getStatus()->getCode(),
            'Expected to receive a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }

    public function testServiceEndpointsDifferentThanQueryEndpointsThrowsError(): void
    {
        $service = $this->createService();

        $otherServiceType = ServiceType::cfdi();
        $parameters = QueryParameters::create()->withServiceType($otherServiceType);

        $this->expectException(LogicException::class);
        $service->query($parameters);
    }
}
