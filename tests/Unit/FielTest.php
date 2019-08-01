<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit;

use PhpCfdi\SatWsDescargaMasiva\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class FielTest extends TestCase
{
    public function testFielWithIncorrectPassword()
    {
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL_password.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            'this is not the password'
        );

        $this->assertFalse($fiel->isValid());
    }
}
