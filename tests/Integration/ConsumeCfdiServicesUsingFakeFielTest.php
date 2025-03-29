<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoCfdi;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;

final class ConsumeCfdiServicesUsingFakeFielTest extends ConsumeServiceTestCase
{
    protected function getServiceEndpoints(): ServiceEndpoints
    {
        return ServiceEndpoints::cfdi();
    }

    public function testQueryChangeAllParameters(): void
    {
        $service = $this->createService();

        $startDate = DateTime::now()->modify('first day of last month midnight');
        $endDate = $startDate->modify('+5 days');
        $period = DateTimePeriod::create($startDate, $endDate);

        $parameters = QueryParameters::create()
            ->withPeriod($period)
            ->withDownloadType(DownloadType::received())
            ->withRequestType(RequestType::xml())
            ->withDocumentType(DocumentType::nomina())
            ->withComplement(ComplementoCfdi::nomina12())
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

    public function testQueryUuid(): void
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

        $otherServiceType = ServiceType::retenciones();
        $parameters = QueryParameters::create()->withServiceType($otherServiceType);

        $this->expectException(LogicException::class);
        $service->query($parameters);
    }
}
