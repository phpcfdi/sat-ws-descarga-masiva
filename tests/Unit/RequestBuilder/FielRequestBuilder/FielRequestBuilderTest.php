<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\FielRequestBuilder;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoCfdi;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class FielRequestBuilderTest extends TestCase
{
    public function testFielRequestImplementsRequestBuilderInterface(): void
    {
        $expected = RequestBuilderInterface::class;
        $interfaces = class_implements(FielRequestBuilder::class) ?: [];
        $this->assertContains($expected, $interfaces);
    }

    public function testFielRequestContainsGivenFiel(): void
    {
        $fiel = $this->createFielUsingTestingFiles();
        $requestBuilder = new FielRequestBuilder($fiel);
        $this->assertSame($fiel, $requestBuilder->getFiel());
    }

    public function testAuthorization(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $created = DateTime::create('2019-08-01T03:38:19.000Z');
        $expires = DateTime::create('2019-08-01T03:43:19.000Z');
        $token = 'uuid-cf6c80fb-00ae-44c0-af56-54ec65decbaa-1';
        $requestBody = $requestBuilder->authorization($created, $expires, $token);

        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('authenticate/request.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())->verify(
            $requestBody,
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            ['http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'],
            $requestBuilder->getFiel()->getCertificatePemContents()
        );
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testAuthorizationWithoutSecurityTokenUuidCreatesRandom(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $created = DateTime::create('2019-08-01T03:38:19.000Z');
        $expires = DateTime::create('2019-08-01T03:43:19.000Z');

        $requestBody = $requestBuilder->authorization($created, $expires);
        $securityTokenId = $this->extractSecurityTokenFromXml($requestBody);
        $this->assertNotEmpty($securityTokenId);

        $otherRequestBody = $requestBuilder->authorization($created, $expires);
        $otherSecurityTokenId = $this->extractSecurityTokenFromXml($otherRequestBody);
        $this->assertNotEmpty($otherSecurityTokenId);
        $this->assertNotEquals($securityTokenId, $otherSecurityTokenId, 'Both generated tokens must not be equal');
    }

    private function extractSecurityTokenFromXml(string $requestBody): string
    {
        preg_match('/o:BinarySecurityToken u:Id="(?<id>.*?)"/u', $requestBody, $matches);
        return $matches['id'] ?? '';
    }

    public function testQueryReceived(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $parameters = QueryParameters::create()
            ->withServiceType(ServiceType::cfdi())
            ->withPeriod(DateTimePeriod::createFromValues('2019-01-01T00:00:00', '2019-01-01T00:04:00'))
            ->withDownloadType(DownloadType::received())
            ->withRequestType(RequestType::cfdi())
            ->withDocumentType(DocumentType::nomina())
            ->withComplement(ComplementoCfdi::nomina12())
            ->withDocumentStatus(DocumentStatus::active())
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
            ->withRfcOnBehalf(RfcOnBehalf::create('XXX01010199A'))
            ->withRfcMatch(RfcMatch::create('AAA010101AAA'))
        ;
        $requestBody = $requestBuilder->query($parameters);

        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('query/request-received.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'SolicitaDescarga');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testQueryIssued(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $parameters = QueryParameters::create()
            ->withServiceType(ServiceType::cfdi())
            ->withPeriod(DateTimePeriod::createFromValues('2019-01-01T00:00:00', '2019-01-01T00:04:00'))
            ->withDownloadType(DownloadType::issued())
        ;
        $requestBody = $requestBuilder->query($parameters);

        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('query/request-issued.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'SolicitaDescarga');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testVerify(): void
    {
        $fiel = Fiel::create(
            $this->fileContents('fake-fiel/EKU9003173C9.cer'),
            $this->fileContents('fake-fiel/EKU9003173C9.key'),
            trim($this->fileContents('fake-fiel/EKU9003173C9-password.txt'))
        );
        $requestBuilder = new FielRequestBuilder($fiel);

        $requestId = '3f30a4e1-af73-4085-8991-e4d97eef16bd';
        $requestBody = $requestBuilder->verify($requestId);

        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('verify/request.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'VerificaSolicitudDescarga');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testDownload(): void
    {
        $fiel = Fiel::create(
            $this->fileContents('fake-fiel/EKU9003173C9.cer'),
            $this->fileContents('fake-fiel/EKU9003173C9.key'),
            trim($this->fileContents('fake-fiel/EKU9003173C9-password.txt'))
        );
        $requestBuilder = new FielRequestBuilder($fiel);

        $packageId = '4e80345d-917f-40bb-a98f-4a73939343c5_01';
        $requestBody = $requestBuilder->download($packageId);

        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('download/request.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'PeticionDescargaMasivaTercerosEntrada');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }
}
