<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Authenticate;

use PhpCfdi\SatWsDescargaMasiva\Services\Authenticate\AuthenticateTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class AuthenticateTranslatorTest extends TestCase
{
    public function testCreateSoapRequest(): void
    {
        $translator = new AuthenticateTranslator();
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            trim($this->fileContents('fake-fiel/password.txt'))
        );

        $since = new DateTime('2019-08-01T03:38:19Z');
        $until = new DateTime('2019-08-01T03:43:19Z');
        $uuid = 'uuid-cf6c80fb-00ae-44c0-af56-54ec65decbaa-1';
        $requestBody = $translator->createSoapRequestWithData($fiel, $since, $until, $uuid);
        $this->assertXmlStringEqualsXmlFile($this->filePath('authenticate/request.xml'), $requestBody);
    }

    public function testCreateTokenFromSoapResponseWithToken(): void
    {
        $expectedCreated = new DateTime('2019-08-01T03:38:20.044Z');
        $expectedExpires = new DateTime('2019-08-01T03:43:20.044Z');

        $translator = new AuthenticateTranslator();
        $responseBody = $translator->nospaces($this->fileContents('authenticate/response-with-token.xml'));
        $token = $translator->createTokenFromSoapResponse($responseBody);
        $this->assertFalse($token->isValueEmpty());
        $this->assertTrue($token->isExpired());
        $this->assertTrue($token->getCreated()->equalsTo($expectedCreated));
        $this->assertTrue($token->getExpires()->equalsTo($expectedExpires));
        $this->assertFalse($token->isValid());
    }

    public function testNoSpacesContents(): void
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
