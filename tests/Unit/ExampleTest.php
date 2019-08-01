<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit;

use PhpCfdi\SatWsDescargaMasiva\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Tests\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testAuthenticationUsingFakeFiel(): void
    {
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            trim($this->fileContents('fake-fiel/password.txt'))
        );
        $webclient = new GuzzleWebClient();

        $service = new Service($fiel, $webclient);
        $token = $service->authenticate();
        $this->assertTrue($token->isValid());
    }
}
