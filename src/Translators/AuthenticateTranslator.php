<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Translators;

use PhpCfdi\SatWsDescargaMasiva\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Token;
use PhpCfdi\SatWsDescargaMasiva\Traits\InteractsXmlTrait;

/** @internal */
class AuthenticateTranslator
{
    use InteractsXmlTrait;

    public function createTokenFromSoapResponse(string $content): Token
    {
        $env = $this->readXmlElement($content);
        $created = new DateTime($this->findContent($env, 'header', 'security', 'timestamp', 'created'));
        $expires = new DateTime($this->findContent($env, 'header', 'security', 'timestamp', 'expires'));
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
        $digested = base64_encode(sha1(str_replace(PHP_EOL, '', $toDigest), true));

        $toSign = $this->nospaces(
            <<<EOT
<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
    <CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></CanonicalizationMethod>
    <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod>
    <Reference URI="#_0">
        <Transforms>
            <Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></Transform>
        </Transforms>
        <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod>
        <DigestValue>${digested}</DigestValue>
    </Reference>
</SignedInfo>
EOT
        );
        $signed = base64_encode($fiel->sign($toSign, OPENSSL_ALGO_SHA1));

        $certificate = Helpers::cleanPemContents($fiel->getCertificatePemContents());

        $xml = <<<EOT
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><s:Header><o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
    <u:Timestamp u:Id="_0">
        <u:Created>${created}</u:Created>
        <u:Expires>${expires}</u:Expires>
    </u:Timestamp>
        <o:BinarySecurityToken u:Id="${uuid}" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
            ${certificate}
        </o:BinarySecurityToken>
        <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
            <SignedInfo>
                <CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
                <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
                <Reference URI="#_0">
                    <Transforms>
                        <Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
                    </Transforms>
                    <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                    <DigestValue>${digested}</DigestValue>
                </Reference>
            </SignedInfo>
            <SignatureValue>${signed}</SignatureValue>
            <KeyInfo>
                <o:SecurityTokenReference>
                    <o:Reference ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" URI="#${uuid}"/>
                </o:SecurityTokenReference>
            </KeyInfo>
        </Signature>
            </o:Security>
        </s:Header>
        <s:Body>
            <Autentica xmlns="http://DescargaMasivaTerceros.gob.mx"/>
        </s:Body>
    </s:Envelope>
EOT;

        return $this->nospaces($xml);
    }
}
