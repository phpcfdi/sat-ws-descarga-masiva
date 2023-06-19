<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\FielRequestBuilder;

use Exception;
use PhpCfdi\Credentials\Certificate;
use PhpCfdi\Credentials\Credential;
use PhpCfdi\Credentials\Internal\SatTypeEnum;
use PhpCfdi\Credentials\PrivateKey;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class FielTest extends TestCase
{
    public function testFielWithIncorrectPasswordCreateAnException(): void
    {
        $this->expectException(Exception::class);
        $this->createFielUsingTestingFiles('invalid password');
    }

    public function testFielWithCorrectPassword(): void
    {
        $fiel = $this->createFielUsingTestingFiles();
        $this->assertTrue($fiel->isValid());
    }

    public function testFielUnprotectedPEM(): void
    {
        $fiel = Fiel::create(
            $this->fileContents('fake-fiel/EKU9003173C9.cer'),
            $this->fileContents('fake-fiel/EKU9003173C9.key.pem'),
            ''
        );
        $this->assertTrue($fiel->isValid());
    }

    public function testFielCreatingFromContents(): void
    {
        $fiel = Fiel::create(
            $this->fileContents('fake-fiel/EKU9003173C9.cer'),
            $this->fileContents('fake-fiel/EKU9003173C9.key'),
            trim($this->fileContents('fake-fiel/EKU9003173C9-password.txt'))
        );
        $this->assertTrue($fiel->isValid());
    }

    public function testIsNotValidUsingCsd(): void
    {
        $credential = Credential::openFiles(
            $this->filePath('fake-csd/EKU9003173C9.cer'),
            $this->filePath('fake-csd/EKU9003173C9.key'),
            trim($this->fileContents('fake-csd/EKU9003173C9-password.txt')),
        );
        $this->assertTrue($credential->isCsd(), 'Unable to perform test: The given CSD is not a CSD');
        $this->assertTrue(
            $credential->certificate()->validOn(),
            'Unable to perform test: The given CSD is not currently valid'
        );

        $fiel = new Fiel($credential);
        $this->assertFalse($fiel->isValid());
    }

    public function testIsNotValidExpiredCertificate(): void
    {
        $certificate = $this->createMock(Certificate::class);
        $certificate->method('satType')->willReturn(SatTypeEnum::fiel());
        $certificate->method('validOn')->willReturn(false);
        $privateKey = $this->createMock(PrivateKey::class);
        $privateKey->method('belongsTo')->willReturn(true);
        $credential = new Credential($certificate, $privateKey);
        $fiel = new Fiel($credential);
        $this->assertFalse($fiel->isValid());
    }
}
