<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\FielRequestBuilder;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodEndInvalidDateFormatException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodStartGreaterThanEndException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodStartInvalidDateFormatException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RequestTypeInvalidException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RfcIsNotIssuerOrReceiverException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RfcIssuerAndReceiverAreEmptyException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
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
        $created = '2019-08-01T03:38:19.000Z';
        $expires = '2019-08-01T03:43:19.000Z';
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
        $created = '2019-08-01T03:38:19.000Z';
        $expires = '2019-08-01T03:43:19.000Z';

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

    public function testQuery(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $start = '2019-01-01T00:00:00';
        $end = '2019-01-01T00:04:00';
        $rfcIssuer = '';
        $rfcReceiver = $requestBuilder->getFiel()->getRfc();
        $requestType = 'CFDI';
        $requestBody = $requestBuilder->query($start, $end, $rfcIssuer, $rfcReceiver, $requestType);

        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('query/request.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'SolicitaDescarga');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testQueryWithInvalidStartDate(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $invalidDate = '2019-01-01 00:00:00'; // contains an space instead of T
        $validDate = '2019-01-01T00:00:00';
        $this->expectException(PeriodStartInvalidDateFormatException::class);
        $requestBuilder->query($invalidDate, $validDate, RequestBuilderInterface::USE_SIGNER, '', 'CFDI');
    }

    public function testQueryWithInvalidEndDate(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $invalidDate = '2019-01-01 00:00:00'; // contains an space instead of T
        $validDate = '2019-01-01T00:00:00';
        $this->expectException(PeriodEndInvalidDateFormatException::class);
        $requestBuilder->query($validDate, $invalidDate, RequestBuilderInterface::USE_SIGNER, '', 'CFDI');
    }

    public function testQueryWithStartGreaterThanEnd(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $lower = '2019-01-01T00:00:00';
        $upper = '2019-01-01T00:00:01';
        $this->expectException(PeriodStartGreaterThanEndException::class);
        $requestBuilder->query($upper, $lower, RequestBuilderInterface::USE_SIGNER, '', 'CFDI');
    }

    public function testQueryWithEmptyIssuerReceiver(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $date = '2019-01-01T00:00:00';
        $requestType = 'CFDI';
        $this->expectException(RfcIssuerAndReceiverAreEmptyException::class);
        $requestBuilder->query($date, $date, '', '', $requestType);
    }

    public function testQueryWithIssuerReceiverNotSigner(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $date = '2019-01-01T00:00:00';
        $requestType = 'CFDI';
        $this->expectException(RfcIsNotIssuerOrReceiverException::class);
        $requestBuilder->query($date, $date, 'FOO', 'BAR', $requestType);
    }

    public function testQueryWithInvalidRequestType(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $date = '2019-01-01T00:00:00';
        $this->expectException(RequestTypeInvalidException::class);
        $requestBuilder->query($date, $date, RequestBuilderInterface::USE_SIGNER, '', 'cfdi');
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
