<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Translators;

use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\Translators\DownloadRequestTranslator;

class DownloadRequestTranslatorTest extends TestCase
{
    public function testCreateDownloadRequestResponseFromSoapResponse(): void
    {
        $exptedRequestId = 'd49af78d-1c80-4221-a48d-345ace91626b';
        $exptedStatusCode = 5000;
        $exptedMessage = 'Solicitud Aceptada';

        $translator = new DownloadRequestTranslator();
        $responseBody = $translator->nospaces($this->fileContents('soap_res_download_request.xml'));
        $downloadResponse = $translator->createDownloadRequestResultFromSoapResponse($responseBody);

        $this->assertEquals($downloadResponse->getRequestId(), $exptedRequestId);
        $this->assertEquals($downloadResponse->getStatusCode(), $exptedStatusCode);
        $this->assertEquals($downloadResponse->getMessage(), $exptedMessage);
        $this->assertTrue($downloadResponse->isAccepted());
    }
}
