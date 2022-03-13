<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class QueryTranslatorTest extends TestCase
{
    public function testCreateQueryResultFromSoapResponse(): void
    {
        $expectedRequestId = 'd49af78d-1c80-4221-a48d-345ace91626b';
        $expectedStatusCode = 5000;
        $expectedMessage = 'Solicitud Aceptada';

        $translator = new QueryTranslator();
        $responseBody = Helpers::nospaces($this->fileContents('query/response-with-id.xml'));
        $result = $translator->createQueryResultFromSoapResponse($responseBody);
        $status = $result->getStatus();

        $this->assertEquals($expectedRequestId, $result->getRequestId());
        $this->assertEquals($expectedStatusCode, $status->getCode());
        $this->assertEquals($expectedMessage, $status->getMessage());
        $this->assertTrue($status->isAccepted());
    }

    public function testCreateSoapRequest(): void
    {
        $translator = new QueryTranslator();
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $query = QueryParameters::create(
            DateTimePeriod::create(DateTime::create('2019-01-01 00:00:00'), DateTime::create('2019-01-01 00:04:00')),
            DownloadType::received(),
            RequestType::cfdi()
        );

        $requestBody = $translator->createSoapRequest($requestBuilder, $query);
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('query/request-received.xml'))),
            $this->xmlFormat($requestBody)
        );
    }
}
