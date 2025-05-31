<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\FielRequestBuilder;

use DOMDocument;
use DOMXPath;
use LogicException;
use PhpCfdi\Credentials\Certificate;
use PhpCfdi\Credentials\Credential;
use PhpCfdi\Credentials\PrivateKey;
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

        /** @see tests/_files/authenticate/request.xml */
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
        $this->assertNotSame($securityTokenId, $otherSecurityTokenId, 'Both generated tokens must not be equal');
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
            ->withRequestType(RequestType::xml())
            ->withDocumentType(DocumentType::nomina())
            ->withComplement(ComplementoCfdi::nomina12())
            ->withDocumentStatus(DocumentStatus::active())
            ->withRfcOnBehalf(RfcOnBehalf::create('XXX01010199A'))
            ->withRfcMatch(RfcMatch::create('AAA010101AAA'))
        ;
        $requestBody = $requestBuilder->query($parameters);

        /** @see tests/_files/query/request-received.xml */
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('query/request-received.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'SolicitaDescargaRecibidos');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testQueryByUuid(): void
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $parameters = QueryParameters::create()
            ->withServiceType(ServiceType::cfdi())
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
        ;
        $requestBody = $requestBuilder->query($parameters);

        /** @see tests/_files/query/request-item.xml */
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('query/request-item.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'SolicitaDescargaFolio');
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

        /** @see tests/_files/query/request-issued.xml */
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('query/request-issued.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'SolicitaDescargaEmitidos');
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

        /** @see tests/_files/verify/request.xml */
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('verify/request.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'VerificaSolicitudDescarga');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testVerifyUsingSpecialInvalidXmlCharacters(): void
    {
        $requestId = '"&"';
        $rfc = 'E&E010101AAA';
        $issuerName = 'O=Compañía "Tú & Yo", Inc.';

        $certificate = $this->createMock(Certificate::class);
        $certificate->method('rfc')->willReturn($rfc);
        $certificate->method('issuerAsRfc4514')->willReturn($issuerName);
        $privateKey = $this->createMock(PrivateKey::class);
        $privateKey->method('belongsTo')->willReturn(true);
        $credential = new Credential($certificate, $privateKey);
        $fiel = new Fiel($credential);

        $requestBuilder = new FielRequestBuilder($fiel);
        $requestBody = $requestBuilder->verify($requestId);

        $document = new DOMDocument();
        $this->assertTrue($document->loadXML($requestBody));
        $xpath = new DOMXPath($document);
        $this->assertSame($requestId, $this->xpathValue($xpath, '//des:solicitud/@IdSolicitud'));
        $this->assertSame($rfc, $this->xpathValue($xpath, '//des:solicitud/@RfcSolicitante'));
        $this->assertSame($issuerName, $this->xpathValue($xpath, '//xd:X509IssuerName'));
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

        /** @see tests/_files/download/request.xml */
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('download/request.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())
            ->verify($requestBody, 'http://DescargaMasivaTerceros.sat.gob.mx', 'PeticionDescargaMasivaTercerosEntrada');
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testDownloadUsingSpecialInvalidXmlCharacters(): void
    {
        $packageId = '"&"';
        $rfc = 'E&E010101AAA';
        $issuerName = 'O=Compañía "Tú & Yo", Inc.';

        $certificate = $this->createMock(Certificate::class);
        $certificate->method('rfc')->willReturn($rfc);
        $certificate->method('issuerAsRfc4514')->willReturn($issuerName);
        $privateKey = $this->createMock(PrivateKey::class);
        $privateKey->method('belongsTo')->willReturn(true);
        $credential = new Credential($certificate, $privateKey);
        $fiel = new Fiel($credential);

        $requestBuilder = new FielRequestBuilder($fiel);
        $requestBody = $requestBuilder->download($packageId);

        $document = new DOMDocument();
        $this->assertTrue($document->loadXML($requestBody));
        $xpath = new DOMXPath($document);
        $this->assertSame($packageId, $this->xpathValue($xpath, '//des:peticionDescarga/@IdPaquete'));
        $this->assertSame($rfc, $this->xpathValue($xpath, '//des:peticionDescarga/@RfcSolicitante'));
        $this->assertSame($issuerName, $this->xpathValue($xpath, '//xd:X509IssuerName'));
    }

    private function xpathValue(DOMXPath $xpath, string $query): string
    {
        $result = $xpath->query($query);
        if (false === $result) {
            throw new LogicException("Invalid XPath query: $query");
        }
        $node = $result->item(0);
        if (null === $node || null === $node->nodeValue) {
            return '';
        }
        return $node->nodeValue;
    }
}
