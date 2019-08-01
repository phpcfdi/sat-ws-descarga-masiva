<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Translators;

use PhpCfdi\SatWsDescargaMasiva\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\Translators\AuthenticateTranslator;

class AuthenticateTranslatorTest extends TestCase
{
    public function testCreateSoapRequest()
    {
        $translator = new AuthenticateTranslator();
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            trim($this->fileContents('fake-fiel/password.txt'))
        );

        $requestBody = $translator->createSoapRequest($fiel, new DateTime('2019-01-13 14:15:16'));
        echo PHP_EOL, $requestBody;
        $this->markTestIncomplete('work in progress');
    }


    public function testNoSpacesContents()
    {
        $source = <<<EOT

<root>
    <foo a="1" b="2">foo</foo>
    
    <bar>
        <baz>
            BAZZ        
        </baz>
    </bar>
</root>

EOT;
        $expected = '<root><foo a="1" b="2">foo</foo><bar><baz>BAZZ</baz></bar></root>';
        $translator = new AuthenticateTranslator();
        $this->assertSame($expected, $translator->nospaces($source));
    }
}
