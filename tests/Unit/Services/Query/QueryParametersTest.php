<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class QueryParametersTest extends TestCase
{
    public function testAllProperties(): void
    {
        $period = DateTimePeriod::create(DateTime::create('2019-01-01 00:00:00'), DateTime::create('2019-01-01 00:04:00'));
        $downloadType = DownloadType::received();
        $requestType = RequestType::cfdi();
        $rfcMatch = 'AAAA010101AAA';
        $documentType = DocumentType::ingreso();
        $documentStatus = DocumentStatus::active();
        $query = QueryParameters::create(
            $period,
            $downloadType,
            $requestType,
            $documentType,
            $documentStatus,
            $rfcMatch
        );
        $this->assertSame($period, $query->getPeriod());
        $this->assertSame($downloadType, $query->getDownloadType());
        $this->assertSame($requestType, $query->getRequestType());
        $this->assertSame($documentType, $query->getDocumentType());
        $this->assertSame($documentStatus, $query->getDocumentStatus());
        $this->assertSame($rfcMatch, $query->getRfcMatch());
    }

    public function testMinimalCreate(): void
    {
        $period = DateTimePeriod::create(DateTime::create('2019-01-01 00:00:00'), DateTime::create('2019-01-01 00:04:00'));
        $query = QueryParameters::create($period);
        $this->assertTrue($query->getRequestType()->isMetadata());
        $this->assertTrue($query->getDownloadType()->isIssued());
        $this->assertTrue($query->getDocumentType()->isUndefined());
        $this->assertTrue($query->getDocumentStatus()->isUndefined());
        $this->assertEmpty($query->getRfcMatch());
    }

    public function testJson(): void
    {
        $query = QueryParameters::create(
            DateTimePeriod::createFromValues('2019-01-01T00:00:00-06:00', '2019-01-01T00:04:00-06:00'),
            DownloadType::received(),
            RequestType::cfdi(),
            DocumentType::ingreso(),
            DocumentStatus::cancelled(),
            'AAAA010101AAA'
        );
        $this->assertInstanceOf(JsonSerializable::class, $query);
        $expectedFile = $this->filePath('json/query-parameters.json');
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($query) ?: '');
    }
}
