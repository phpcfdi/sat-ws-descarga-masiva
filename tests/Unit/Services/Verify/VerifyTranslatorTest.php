<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Verify;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyTranslator;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class VerifyTranslatorTest extends TestCase
{
    public function testCreateVerifyResultFromSoapResponseWithZeroPackages(): void
    {
        $expectedStatusCode = 5000;
        $expectedStatusRequest = 5;
        $expectedCodeRequest = 5004;
        $expectedNumberCfdis = 0;
        $expectedMessage = 'Solicitud Aceptada';
        $expectedPackagesIds = [];

        $translator = new VerifyTranslator();
        $responseBody = Helpers::nospaces($this->fileContents('verify/response-0-packages.xml'));
        $result = $translator->createVerifyResultFromSoapResponse($responseBody);
        $status = $result->getStatus();
        $statusRequest = $result->getStatusRequest();
        $codeRequest = $result->getCodeRequest();

        $this->assertTrue($status->isAccepted());
        $this->assertSame($expectedStatusCode, $status->getCode());
        $this->assertSame($expectedMessage, $status->getMessage());
        $this->assertSame($expectedStatusRequest, $statusRequest->getValue());
        $this->assertTrue($statusRequest->isRejected());
        $this->assertSame($expectedCodeRequest, $codeRequest->getValue());
        $this->assertTrue($codeRequest->isEmptyResult());
        $this->assertSame($expectedNumberCfdis, $result->getNumberCfdis());
        $this->assertSame($expectedPackagesIds, $result->getPackagesIds());
    }

    public function testCreateVerifyResultFromSoapResponseWithTwoPackages(): void
    {
        $expectedPackagesIds = [
            '4e80345d-917f-40bb-a98f-4a73939343c5_01',
            '4e80345d-917f-40bb-a98f-4a73939343c5_02',
        ];

        $translator = new VerifyTranslator();
        $responseBody = Helpers::nospaces($this->fileContents('verify/response-2-packages.xml'));
        $result = $translator->createVerifyResultFromSoapResponse($responseBody);
        $this->assertSame($expectedPackagesIds, $result->getPackagesIds());
        $this->assertSame(2, $result->countPackages());
    }

    public function testCreateSoapRequest(): void
    {
        $translator = new VerifyTranslator();
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();

        $requestId = '3f30a4e1-af73-4085-8991-e4d97eef16bd';

        $requestBody = $translator->createSoapRequest($requestBuilder, $requestId);
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('verify/request.xml'))),
            $this->xmlFormat($requestBody)
        );
    }
}
