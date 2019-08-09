<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Authenticate;

use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Shared\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Shared\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\Shared\Token;

/** @internal */
class AuthenticateTranslator
{
    use InteractsXmlTrait;

    public function createTokenFromSoapResponse(string $content): Token
    {
        $env = $this->readXmlElement($content);
        $created = new DateTime($this->findContent($env, 'header', 'security', 'timestamp', 'created') ?: 0);
        $expires = new DateTime($this->findContent($env, 'header', 'security', 'timestamp', 'expires') ?: 0);
        $value = $this->findContent($env, 'body', 'autenticaResponse', 'autenticaResult');
        return new Token($created, $expires, $value);
    }

    public function createSoapRequest(Fiel $fiel): string
    {
        $since = DateTime::now();
        $until = $since->modify('+ 5 minutes');
        $uuid = Helpers::createUuid();
        return $this->createSoapRequestWithData($fiel, $since, $until, $uuid);
    }

    public function createSoapRequestWithData(Fiel $fiel, DateTime $since, DateTime $until, string $uuid): string
    {
        $created = $since->formatSat();
        $expires = $until->formatSat();
        $toDigest = $this->nospaces(
            <<<EOT
<u:Timestamp xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" u:Id="_0">
    <u:Created>${created}</u:Created>
    <u:Expires>${expires}</u:Expires>
</u:Timestamp>
EOT
        );
        $digested = base64_encode(sha1($toDigest, true));
        $signedInfoData = $this->createSignedInfoCanonicalExclusive($digested, '#_0');
        $signed = base64_encode($fiel->sign($signedInfoData, OPENSSL_ALGO_SHA1));
        $keyInfoData = $this->createKeyInfoSecurityToken($uuid);
        $signatureData = $this->createSignatureData($signedInfoData, $signed, $keyInfoData);
        $certificate = Helpers::cleanPemContents($fiel->getCertificatePemContents());

        $xml = <<<EOT
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
    <s:Header>
        <o:Security xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" s:mustUnderstand="1">
            <u:Timestamp u:Id="_0">
                <u:Created>${created}</u:Created>
                <u:Expires>${expires}</u:Expires>
            </u:Timestamp>
            <o:BinarySecurityToken u:Id="${uuid}" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
                ${certificate}
            </o:BinarySecurityToken>
            ${signatureData}
        </o:Security>
    </s:Header>
    <s:Body>
        <Autentica xmlns="http://DescargaMasivaTerceros.gob.mx"/>
    </s:Body>
</s:Envelope>
EOT;

        return $this->nospaces($xml);
    }

    public function createKeyInfoSecurityToken(string $uuid): string
    {
        $xml = <<<EOT
<KeyInfo>
    <o:SecurityTokenReference>
        <o:Reference URI="#${uuid}" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"/>
    </o:SecurityTokenReference>
</KeyInfo>
EOT;
        return $xml;
    }
}
