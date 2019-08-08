<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
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
        $responseBody = $translator->nospaces($this->fileContents('query/response-with-id.xml'));
        $result = $translator->createQueryResultFromSoapResponse($responseBody);

        $this->assertEquals($expectedRequestId, $result->getRequestId());
        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
        $this->assertEquals($expectedMessage, $result->getMessage());
        $this->assertTrue($result->isAccepted());
    }

    public function testCreateSoapRequest(): void
    {
        $translator = new QueryTranslator();
        $fiel = $this->createFielUsingTestingFiles();

        $requestBody = $translator->createSoapRequestWithData(
            $fiel,
            'aaa010101aaa', // the file was created using rfc in lower case
            new DateTime('2019-01-01 00:00:00'),
            new DateTime('2019-01-01 00:04:00'),
            DownloadType::received(),
            RequestType::cfdi()
        );
        $this->assertSame(
            $this->xmlFormat($translator->nospaces($this->fileContents('query/request.xml'))),
            $this->xmlFormat($requestBody)
        );
    }
}
