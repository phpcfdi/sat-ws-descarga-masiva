<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Helpers\FielData;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class FielTest extends TestCase
{
    public function testFielWithIncorrectPassword(): void
    {
        $fiel = $this->createFielUsingTestingFiles('invalid password');
        $this->assertFalse($fiel->isValid());
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
}
