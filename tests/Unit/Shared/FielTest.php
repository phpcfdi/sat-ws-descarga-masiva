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

    public function testFielUnprotected(): void
    {
        $fiel = (new FielData(
            $this->filePath('fake-fiel/aaa010101aaa_FIEL.cer'),
            $this->filePath('fake-fiel/aaa010101aaa_FIEL.key.pem'),
            ''
        ))->createFiel();
        $this->assertTrue($fiel->isValid());
    }

    public function testFielCreatingFromContents(): void
    {
        $fiel = Fiel::create(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.key.pem'),
            ''
        );
        $this->assertTrue($fiel->isValid());
    }
}
