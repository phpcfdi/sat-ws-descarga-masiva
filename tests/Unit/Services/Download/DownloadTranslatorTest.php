<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Download;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Internal\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadTranslator;
use PhpCfdi\SatWsDescargaMasiva\Tests\EnvelopSignatureVerifier;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class DownloadTranslatorTest extends TestCase
{
    use InteractsXmlTrait;

    public function testCreateDownloadResultFromSoapResponseWithPackage(): void
    {
        $expectedStatusCode = 5000;
        $expectedMessage = 'Solicitud Aceptada';

        $translator = new DownloadTranslator();
        $responseBody = Helpers::nospaces($this->fileContents('download/response-with-package.xml'));
        $result = $translator->createDownloadResultFromSoapResponse($responseBody);
        $status = $result->getStatus();

        $this->assertGreaterThan(0, $result->getPackageLenght());
        $this->assertNotEmpty($result->getPackageContent());
        $this->assertEquals($expectedStatusCode, $status->getCode());
        $this->assertEquals($expectedMessage, $status->getMessage());
        $this->assertTrue($status->isAccepted());
    }

    public function testCreateSoapRequest(): void
    {
        $translator = new DownloadTranslator();
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();

        $packageId = '4e80345d-917f-40bb-a98f-4a73939343c5_01';

        $requestBody = $translator->createSoapRequest($requestBuilder, $packageId);
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('download/request.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'PeticionDescargaMasivaTercerosEntrada');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }
}
