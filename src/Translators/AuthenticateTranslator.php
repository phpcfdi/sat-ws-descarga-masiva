<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Translators;

use DOMAttr;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PhpCfdi\SatWsDescargaMasiva\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Token;

/** @internal */
class AuthenticateTranslator
{
    public function createTokenFromSoapResponse(string $content): Token
    {
        $env = $this->readXmlElement($content);
        $created = new DateTime($this->findContent($env, 'header', 'security', 'timestamp', 'created'));
        $expires = new DateTime($this->findContent($env, 'header', 'security', 'timestamp', 'expires'));
        $value = $this->findContent($env, 'body', 'autenticaResponse', 'autenticaResult');
        return new Token($created, $expires, $value);
    }

    public function createSoapRequest(Fiel $fiel, DateTime $since = null): string
    {
        $uuid = $this->createUuid();
        $since = $since ?? DateTime::now();
        $created = $since->formatSat();
        $expires = $since->modify('+ 10 minutes')->formatSat();
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

        $certificate = base64_encode($fiel->getCertificatePemContents());

        $xml = <<<EOT
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><s:Header><o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
    <u:Timestamp u:Id="_0">
        <u:Created>${created}</u:Created>
        <u:Expires>${expires}</u:Expires>
    </u:Timestamp>
        <o:BinarySecurityToken u:Id="${uuid}" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
            $certificate
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
                            <DigestValue>${toDigest}</DigestValue>
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

    public function nospaces(string $input): string
    {
        return implode(
            '',
            array_filter(
                array_map(
                    function (string $line): string {
                        return trim($line);
                    },
                    explode("\n", str_replace("\r", '', $input))
                )
            )
        );
    }

    public function createUuid(): string
    {
        $md5 = md5(uniqid());
        return sprintf(
            'uuid-%08s-%04s-%04s-%04s-%012s-1',
            substr($md5, 0, 8),
            substr($md5, 8, 4),
            substr($md5, 12, 4),
            substr($md5, 16, 4),
            substr($md5, 20)
        );
    }

    public function readXmlDocument(string $source): DOMDocument
    {
        if ('' === $source) {
            throw new InvalidArgumentException('Cannot load an xml with empty content');
        }
        $document = new DOMDocument();
        $document->loadXML($source);
        return $document;
    }

    public function readXmlElement(string $source): DOMElement
    {
        $document = $this->readXmlDocument($source);
        /** @var DOMElement|null $element */
        $element = $document->documentElement;
        if (null === $element) {
            throw new InvalidArgumentException('Cannot load an xml without document element');
        }
        return $element;
    }


    public function findElement(DOMElement $element, string ... $names): ? DOMElement
    {
        $current = array_shift($names);
        $current = strtolower($current);
        foreach ($element->childNodes as $child) {
            if ($child->nodeType instanceof DOMElement) {
                $localName = strtolower($child->localName);
                if ($localName === $current) {
                    if (count($names) > 0) {
                        return $this->findElement($child, ... $names);
                    } else {
                        return $child;
                    }
                }
            }
        }
        return null;
    }

    public function findContent(DOMElement $element, string ... $names): string
    {
        $found = $this->findElement($element, ... $names);
        if (null === $found) {
            return '';
        }
        return $found->textContent;
    }

    public function findAttribute(DOMElement $element, string ...$search): string
    {
        $attributeName = strtolower(array_pop($search));
        $found = $this->findElement($element, ... $search);
        if (null === $found) {
            return '';
        }
        foreach ($found->attributes as $attribute) {
            if ($attribute instanceof DOMAttr) {
                $name = strtolower($attribute->localName);
                if ($name === $attributeName) {
                    return $attribute->textContent;
                }
            }
        }
        return '';
    }
}
