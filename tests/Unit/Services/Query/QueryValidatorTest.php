<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoCfdi;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoRetenciones;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatches;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class QueryValidatorTest extends TestCase
{
    public function testQueryInvalidPeriod(): void
    {
        $date = DateTime::create('2025-01-02 03:04:05');
        $query = QueryParameters::create(DateTimePeriod::create($date, $date));
        $this->assertContains(
            'La fecha de inicio (2025-01-02 03:04:05) no puede ser mayor o igual a la fecha final (2025-01-02 03:04:05) del periodo de consulta.',
            $query->validate(),
        );
    }

    public function testQueryReceivedXmlCancelled(): void
    {
        $query = QueryParameters::create()
            ->withDownloadType(DownloadType::received())
            ->withRequestType(RequestType::xml())
            ->withDocumentStatus(DocumentStatus::cancelled())
        ;
        $this->assertContains(
            'No es posible hacer una consulta de XML Recibidos Cancelados.',
            $query->validate(),
        );
    }

    public function testQueryReceivedWithMoreThanOneCounterpart(): void
    {
        $query = QueryParameters::create()
            ->withDownloadType(DownloadType::received())
            ->withRfcMatches(RfcMatches::createFromValues('AAA010101AAA', 'BBB010101AAA'))
        ;
        $this->assertContains(
            'No es posible hacer una consulta de Recibidos con más de 1 RFC emisor.',
            $query->validate(),
        );
    }

    public function testQueryIssuedWithMoreThanFiveCounterparts(): void
    {
        $rfcMatches = RfcMatches::createFromValues(
            'AAA010101AAA',
            'BBB010101AAA',
            'CCC010101AAA',
            'DDD010101AAA',
            'EEE010101AAA',
            'FFF010101AAA',
        );
        $query = QueryParameters::create()
            ->withDownloadType(DownloadType::issued())
            ->withRfcMatches($rfcMatches)
        ;
        $this->assertContains(
            'No es posible hacer una consulta de Emitidos con más de 5 RFC receptores.',
            $query->validate(),
        );
    }

    public function testQueryCfdiInvalidComplement(): void
    {
        $wrongComplement = ComplementoRetenciones::intereses();
        $query = QueryParameters::create()
            ->withServiceType(ServiceType::cfdi())
            ->withComplement($wrongComplement)
        ;
        $this->assertContains(
            "El complemento de CFDI definido no es un complemento registrado de este tipo ({$wrongComplement->label()}).",
            $query->validate(),
        );
    }

    public function testQueryRetencionInvalidComplement(): void
    {
        $wrongComplement = ComplementoCfdi::comercioExterior11();
        $query = QueryParameters::create()
            ->withServiceType(ServiceType::retenciones())
            ->withComplement($wrongComplement)
        ;
        $this->assertContains(
            "El complemento de Retenciones definido no es un complemento registrado de este tipo ({$wrongComplement->label()}).",
            $query->validate(),
        );
    }

    public function testQueryUuidWithCounterpart(): void
    {
        $query = QueryParameters::create()
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
            ->withRfcMatch(RfcMatch::create('AAA010101AAA'))
        ;
        $this->assertContains(
            'En una consulta por UUID no se debe usar el filtro de RFC.',
            $query->validate(),
        );
    }

    public function testQueryUuidWithComplement(): void
    {
        $query = QueryParameters::create()
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
            ->withComplement(ComplementoCfdi::comercioExterior11())
        ;
        $this->assertContains(
            'En una consulta por UUID no se debe usar el filtro de complemento.',
            $query->validate(),
        );
    }

    public function testQueryUuidWithDocumentStatus(): void
    {
        $query = QueryParameters::create()
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
            ->withDocumentStatus(DocumentStatus::active())
        ;
        $this->assertContains(
            'En una consulta por UUID no se debe usar el filtro de estado de documento.',
            $query->validate(),
        );
    }

    public function testQueryUuidWithDocumentType(): void
    {
        $query = QueryParameters::create()
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
            ->withDocumentType(DocumentType::nomina())
        ;
        $this->assertContains(
            'En una consulta por UUID no se debe usar el filtro de tipo de documento.',
            $query->validate(),
        );
    }
}
