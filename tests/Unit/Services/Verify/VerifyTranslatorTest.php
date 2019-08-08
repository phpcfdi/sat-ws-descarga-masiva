<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Verify;

use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyTranslator;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class VerifyTranslatorTest extends TestCase
{
    public function testCreateVerifyResultFromSoapResponseWithZeroPackages(): void
    {
        $expectedStatusCode = 5000;
        $expectedStatusRequest = 5;
        $expectedStatusCodeRequest = 5004;
        $expectedNumberCfdis = 0;
        $expectedMessage = 'Solicitud Aceptada';
        $expectedPackagesIds = [];

        $translator = new VerifyTranslator();
        $responseBody = $translator->nospaces($this->fileContents('verify/response-0-packages.xml'));
        $result = $translator->createVerifyResultFromSoapResponse($responseBody);

        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
        $this->assertEquals($expectedStatusRequest, $result->getStatusRequest());
        $this->assertEquals($expectedStatusCodeRequest, $result->getStatusCodeRequest());
        $this->assertEquals($expectedNumberCfdis, $result->getNumberCfdis());
        $this->assertEquals($expectedMessage, $result->getMessage());
        $this->assertEquals($expectedPackagesIds, $result->getPackagesIds());
        $this->assertTrue($result->isRejected());
    }

    public function testCreateVerifyResultFromSoapResponseWithTwoPackages(): void
    {
        $expectedStatusCode = 5000;
        $expectedStatusRequest = 3;
        $expectedStatusCodeRequest = 5000;
        $expectedNumberCfdis = 12345;
        $expectedMessage = 'Solicitud Aceptada';
        $expectedPackagesIds = [
            '4e80345d-917f-40bb-a98f-4a73939343c5_01',
            '4e80345d-917f-40bb-a98f-4a73939343c5_02',
        ];

        $translator = new VerifyTranslator();
        $responseBody = $translator->nospaces($this->fileContents('verify/response-2-packages.xml'));
        $result = $translator->createVerifyResultFromSoapResponse($responseBody);

        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
        $this->assertEquals($expectedStatusRequest, $result->getStatusRequest());
        $this->assertEquals($expectedStatusCodeRequest, $result->getStatusCodeRequest());
        $this->assertEquals($expectedNumberCfdis, $result->getNumberCfdis());
        $this->assertEquals($expectedMessage, $result->getMessage());
        $this->assertEquals($expectedPackagesIds, $result->getPackagesIds());
        $this->assertTrue($result->isFinished());
    }

    public function testCreateSoapRequest(): void
    {
        $translator = new VerifyTranslator();
        $fiel = $this->createFielUsingTestingFiles();

        $rfc = 'AAA010101AAA';
        $requestId = '3f30a4e1-af73-4085-8991-e4d97eef16bd';

        $requestBody = $translator->createSoapRequestWithData($fiel, $rfc, $requestId);
        $this->assertSame(
            $this->xmlFormat($translator->nospaces($this->fileContents('verify/request.xml'))),
            $this->xmlFormat($requestBody)
        );
    }
}
