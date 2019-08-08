<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class FielTest extends TestCase
{
    public function testFielWithIncorrectPassword(): void
    {
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL_password.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            'this is not the password'
        );

        $this->assertFalse($fiel->isValid());
    }

    public function testFielWithCorrectPassword(): void
    {
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL_password.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            trim($this->fileContents('fake-fiel/password.txt'))
        );

        $this->assertTrue($fiel->isValid());
    }
}
