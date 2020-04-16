<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use Exception;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Helpers\FielData;
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
        $fiel = (new FielData(
            $this->filePath('fake-fiel/EKU9003173C9.cer'),
            $this->filePath('fake-fiel/EKU9003173C9.key.pem'),
            ''
        ))->createFiel();
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
        $fiel = Fiel::create(
            $this->fileContents('fake-csd/EKU9003173C9.cer'),
            $this->fileContents('fake-csd/EKU9003173C9.key'),
            trim($this->fileContents('fake-csd/EKU9003173C9-password.txt'))
        );
        $this->assertFalse($fiel->isValid());
    }
}
