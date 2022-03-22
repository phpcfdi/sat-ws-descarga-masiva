<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType;
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
        // this is the query with default parameters
        $query = QueryParameters::create()
            ->withPeriod(DateTimePeriod::createFromValues('2019-01-01 00:00:00', '2019-01-01 00:04:00'))
            ->withServiceType(ServiceType::cfdi())
        ;

        $requestBody = $translator->createSoapRequest($requestBuilder, $query, false);
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('query/request-issued.xml'))),
            $this->xmlFormat($requestBody)
        );
    }
}
