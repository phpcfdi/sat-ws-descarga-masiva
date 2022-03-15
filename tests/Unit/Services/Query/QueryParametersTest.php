<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\CfdiComplemento;
use PhpCfdi\SatWsDescargaMasiva\Shared\CfdiUuid;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class QueryParametersTest extends TestCase
{
    public function testAllProperties(): void
    {
        $period = DateTimePeriod::create(DateTime::create('2019-01-01 00:00:00'), DateTime::create('2019-01-01 00:04:00'));
        $downloadType = DownloadType::received();
        $requestType = RequestType::cfdi();
        $documentType = DocumentType::ingreso();
        $documentStatus = DocumentStatus::active();
        $uuid = CfdiUuid::create('96623061-61fe-49de-b298-c7156476aa8b');
        $rfcOnBehalf = RfcOnBehalf::create('XXX01010199A');
        $rfcMatch = RfcMatch::create('AAAA010101AAA');
        $complement = CfdiComplemento::leyendasFiscales10();
        $query = QueryParameters::create(
            $period,
            $downloadType,
            $requestType,
            $documentType,
            $complement,
            $documentStatus,
            $uuid,
            $rfcOnBehalf,
            $rfcMatch
        );
        $this->assertSame($period, $query->getPeriod());
        $this->assertSame($downloadType, $query->getDownloadType());
        $this->assertSame($requestType, $query->getRequestType());
        $this->assertSame($documentType, $query->getDocumentType());
        $this->assertSame($complement, $query->getComplement());
        $this->assertSame($documentStatus, $query->getDocumentStatus());
        $this->assertSame($uuid, $query->getUuid());
        $this->assertSame($rfcOnBehalf, $query->getRfcOnBehalf());
        $this->assertSame($rfcMatch, $query->getRfcMatch());
    }

    public function testMinimalCreate(): void
    {
        $period = DateTimePeriod::create(DateTime::create('2019-01-01 00:00:00'), DateTime::create('2019-01-01 00:04:00'));
        $query = QueryParameters::create($period);
        $this->assertTrue($query->getRequestType()->isMetadata());
        $this->assertTrue($query->getDownloadType()->isIssued());
        $this->assertTrue($query->getDocumentType()->isUndefined());
        $this->assertTrue($query->getComplement()->isUndefined());
        $this->assertTrue($query->getDocumentStatus()->isUndefined());
        $this->assertTrue($query->getUuid()->isEmpty());
        $this->assertTrue($query->getRfcOnBehalf()->isEmpty());
        $this->assertTrue($query->getRfcMatch()->isEmpty());
    }

    public function testJson(): void
    {
        $query = QueryParameters::create(
            DateTimePeriod::createFromValues('2019-01-01T00:00:00-06:00', '2019-01-01T00:04:00-06:00'),
            DownloadType::received(),
            RequestType::cfdi(),
            DocumentType::ingreso(),
            CfdiComplemento::leyendasFiscales10(),
            DocumentStatus::cancelled(),
            CfdiUuid::create('96623061-61fe-49de-b298-c7156476aa8b'),
            RfcOnBehalf::create('XXX01010199A'),
            RfcMatch::create('AAAA010101AAA')
        );
        $this->assertInstanceOf(JsonSerializable::class, $query);
        $expectedFile = $this->filePath('json/query-parameters.json');
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($query) ?: '');
    }
}
