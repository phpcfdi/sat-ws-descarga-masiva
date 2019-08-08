<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Translators;

use PhpCfdi\SatWsDescargaMasiva\Enums\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Enums\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class DownloadRequestTranslatorTest extends TestCase
{
    public function testCreateDownloadRequestResponseFromSoapResponse(): void
    {
        $exptedRequestId = 'd49af78d-1c80-4221-a48d-345ace91626b';
        $exptedStatusCode = 5000;
        $exptedMessage = 'Solicitud Aceptada';

        $translator = new \PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryTranslator();
        $responseBody = $translator->nospaces($this->fileContents('soap_res_download_request.xml'));
        $downloadResponse = $translator->createDownloadRequestResultFromSoapResponse($responseBody);

        $this->assertEquals($downloadResponse->getRequestId(), $exptedRequestId);
        $this->assertEquals($downloadResponse->getStatusCode(), $exptedStatusCode);
        $this->assertEquals($downloadResponse->getMessage(), $exptedMessage);
        $this->assertTrue($downloadResponse->isAccepted());
    }

    public function testCreateSoapRequest(): void
    {
        $translator = new QueryTranslator();
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            trim($this->fileContents('fake-fiel/password.txt'))
        );

        $requestBody = $translator->createSoapRequestWithData(
            $fiel,
            'aaa010101aaa', // the file was created using rfc in lower case
            new DateTime('2019-01-01 00:00:00'),
            new DateTime('2019-01-01 00:04:00'),
            DownloadType::received(),
            RequestType::cfdi()
        );
        $this->assertXmlStringEqualsXmlFile($this->filePath('soap_req_body_download_request.xml'), $requestBody);
    }
}
