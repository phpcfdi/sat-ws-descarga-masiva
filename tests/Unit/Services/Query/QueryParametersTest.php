<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoCfdi;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatches;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class QueryParametersTest extends TestCase
{
    public function testSetAllProperties(): void
    {
        $period = DateTimePeriod::createFromValues('2019-01-01 00:00:00', '2019-01-01 00:04:00');
        $downloadType = DownloadType::received();
        $requestType = RequestType::xml();
        $documentType = DocumentType::ingreso();
        $documentStatus = DocumentStatus::active();
        $uuid = Uuid::create('96623061-61fe-49de-b298-c7156476aa8b');
        $rfcOnBehalf = RfcOnBehalf::create('XXX01010199A');
        $rfcMatches = RfcMatches::createFromValues('ABA991231XX0');
        $complement = ComplementoCfdi::leyendasFiscales10();

        $query = QueryParameters::create()
            ->withPeriod($period)
            ->withDownloadType($downloadType)
            ->withRequestType($requestType)
            ->withDocumentType($documentType)
            ->withComplement($complement)
            ->withDocumentStatus($documentStatus)
            ->withUuid($uuid)
            ->withRfcOnBehalf($rfcOnBehalf)
            ->withRfcMatches($rfcMatches)
        ;
        $this->assertSame($period, $query->getPeriod());
        $this->assertSame($downloadType, $query->getDownloadType());
        $this->assertSame($requestType, $query->getRequestType());
        $this->assertSame($documentType, $query->getDocumentType());
        $this->assertSame($complement, $query->getComplement());
        $this->assertSame($documentStatus, $query->getDocumentStatus());
        $this->assertSame($uuid, $query->getUuid());
        $this->assertSame($rfcOnBehalf, $query->getRfcOnBehalf());
        $this->assertSame($rfcMatches, $query->getRfcMatches());

        $rfcMatch = RfcMatch::create('AAAA010101AAA');
        $query = $query->withRfcMatch($rfcMatch);
        $this->assertSame($rfcMatch, $query->getRfcMatch());
        $this->assertSame($rfcMatch, $query->getRfcMatches()->getFirst());
    }

    public function testMinimalCreate(): void
    {
        $period = DateTimePeriod::create(DateTime::create('2019-01-01 00:00:00'), DateTime::create('2019-01-01 00:04:00'));
        $query = QueryParameters::create($period);
        $this->assertSame($period, $query->getPeriod());
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
        $query = QueryParameters::create()
            ->withPeriod(DateTimePeriod::createFromValues('2019-01-01T00:00:00-06:00', '2019-01-01T00:04:00-06:00'))
            ->withDownloadType(DownloadType::received())
            ->withRequestType(RequestType::xml())
            ->withDocumentType(DocumentType::ingreso())
            ->withComplement(ComplementoCfdi::leyendasFiscales10())
            ->withDocumentStatus(DocumentStatus::cancelled())
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
            ->withRfcOnBehalf(RfcOnBehalf::create('XXX01010199A'))
            ->withRfcMatch(RfcMatch::create('AAAA010101AAA'))
        ;
        $this->assertInstanceOf(JsonSerializable::class, $query);
        $expectedFile = $this->filePath('json/query-parameters.json');
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($query) ?: '');
    }
}
