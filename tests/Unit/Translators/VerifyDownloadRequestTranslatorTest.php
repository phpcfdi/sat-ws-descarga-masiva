<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Translators;

use PhpCfdi\SatWsDescargaMasiva\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\Translators\VerifyDownloadRequestTranslator;

class VerifyDownloadRequestTranslatorTest extends TestCase
{
    public function testCreateVerifyDownloadRequestResponseFromSoapResponseZeroPackages(): void
    {
        $expectedStatusCode = 5000;
        $expectedStatusRequest = 5;
        $expectedStatusCodeRequest = 5004;
        $expectedNumberCfdis = 0;
        $expectedMessage = 'Solicitud Aceptada';
        $expectedPackages = [];

        $translator = new VerifyDownloadRequestTranslator();
        $responseBody = $translator->nospaces($this->fileContents('soap_res_verify_download_request_zero_packages.xml'));
        $downloadResponse = $translator->createVerifyDownloadRequestResultFromSoapResponse($responseBody);

        $this->assertEquals($downloadResponse->getStatusCode(), $expectedStatusCode);
        $this->assertEquals($downloadResponse->getStatusRequest(), $expectedStatusRequest);
        $this->assertEquals($downloadResponse->getStatusCodeRequest(), $expectedStatusCodeRequest);
        $this->assertEquals($downloadResponse->getNumberCfdis(), $expectedNumberCfdis);
        $this->assertEquals($downloadResponse->getMessage(), $expectedMessage);
        $this->assertEquals($downloadResponse->getPackages(), $expectedPackages);
        $this->assertTrue($downloadResponse->isRejected());
    }

    public function testCreateVerifyDownloadRequestResponseFromSoapResponseTwoPackages(): void
    {
        $expectedStatusCode = 5000;
        $expectedStatusRequest = 3;
        $expectedStatusCodeRequest = 5000;
        $expectedNumberCfdis = 12345;
        $expectedMessage = 'Solicitud Aceptada';
        $expectedPackages = [
            '4e80345d-917f-40bb-a98f-4a73939343c5_01',
            '4e80345d-917f-40bb-a98f-4a73939343c5_02',
        ];

        $translator = new VerifyDownloadRequestTranslator();
        $responseBody = $translator->nospaces($this->fileContents('soap_res_verify_download_request_two_packages.xml'));
        $downloadResponse = $translator->createVerifyDownloadRequestResultFromSoapResponse($responseBody);

        $this->assertEquals($expectedStatusCode, $downloadResponse->getStatusCode());
        $this->assertEquals($expectedStatusRequest, $downloadResponse->getStatusRequest());
        $this->assertEquals($expectedStatusCodeRequest, $downloadResponse->getStatusCodeRequest());
        $this->assertEquals($expectedNumberCfdis, $downloadResponse->getNumberCfdis());
        $this->assertEquals($expectedMessage, $downloadResponse->getMessage());
        $this->assertEquals($expectedPackages, $downloadResponse->getPackages());
        $this->assertTrue($downloadResponse->isFinished());
    }

    public function testCreateSoapRequest(): void
    {
        $translator = new VerifyDownloadRequestTranslator();
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            trim($this->fileContents('fake-fiel/password.txt'))
        );

        $rfc = 'AAA010101AAA';
        $requestId = '3f30a4e1-af73-4085-8991-e4d97eef16bd';

        $requestBody = $translator->createSoapRequestWithData($fiel, $rfc, $requestId);
        $this->assertXmlStringEqualsXmlFile($this->filePath('soap_req_body_verify_download_request.xml'), $requestBody);
    }
}
