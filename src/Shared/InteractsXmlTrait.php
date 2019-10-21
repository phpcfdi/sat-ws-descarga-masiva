<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use DOMAttr;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;

/** @internal */
trait InteractsXmlTrait
{
    public function nospaces(string $input): string
    {
        return implode('', array_filter(array_map(function (string $line): string {
            return trim($line);
        }, explode("\n", str_replace("\r", '', $input)))));
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

    public function findElement(DOMElement $element, string ...$names): ?DOMElement
    {
        $current = strtolower(strval(array_shift($names)));
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
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

    public function findContent(DOMElement $element, string ...$names): string
    {
        $found = $this->findElement($element, ... $names);
        if (null === $found) {
            return '';
        }
        return $this->extractElementContent($found);
    }

    private function extractElementContent(DOMElement $element): string
    {
        $buffer = [];
        /** @var \DOMNode $node */
        foreach ($element->childNodes as $node) {
            if (XML_TEXT_NODE === $node->nodeType) {
                $buffer[] = trim($node->textContent);
            }
        }
        return implode('', $buffer);
    }

    /**
     * @param DOMElement $element
     * @param string ...$names
     * @return DOMElement[]
     */
    public function findElements(DOMElement $element, string ...$names): array
    {
        $current = strtolower(strval(array_pop($names)));
        $element = $this->findElement($element, ...$names);
        if (null === $element) {
            return [];
        }

        $found = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $localName = strtolower($child->localName);
                if ($localName === $current) {
                    $found[] = $child;
                }
            }
        }
        return $found;
    }

    /**
     * @param DOMElement $element
     * @param string ...$names
     * @return string[]
     */
    public function findContents(DOMElement $element, string ...$names): array
    {
        return array_map(
            function (DOMElement $element) {
                return $this->extractElementContent($element);
            },
            $this->findElements($element, ... $names)
        );
    }

    public function findAttributes(DOMElement $element, string ...$search): array
    {
        $found = $this->findElement($element, ...$search);
        if (null === $found) {
            return [];
        }
        $attributes = [];
        /** @var DOMAttr $attribute */
        foreach ($found->attributes as $attribute) {
            $attributes[$attribute->localName] = $attribute->value;
        }
        return array_change_key_case($attributes, CASE_LOWER);
    }

    protected function createSignedInfoCanonicalExclusive(string $digested, $uri = ''): string
    {
        // see https://www.w3.org/TR/xmlsec-algorithms/ to understand the algorithm
        // http://www.w3.org/2001/10/xml-exc-c14n# - Exclusive Canonicalization XML 1.0 (omit comments)
        $xml = <<<EOT
            <SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                <CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></CanonicalizationMethod>
                <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod>
                <Reference URI="${uri}">
                    <Transforms>
                        <Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></Transform>
                    </Transforms>
                    <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod>
                    <DigestValue>${digested}</DigestValue>
                </Reference>
            </SignedInfo>
EOT;
        return $this->nospaces($xml);
    }

    protected function createKeyInfoData(Fiel $fiel): string
    {
        $certificate = Helpers::cleanPemContents($fiel->getCertificatePemContents());
        $serial = $fiel->getCertificateSerial();
        $issuerName = $fiel->getCertificateIssuerName();

        $xml = <<<EOT
            <KeyInfo>
                <X509Data>
                    <X509IssuerSerial>
                        <X509IssuerName>${issuerName}</X509IssuerName>
                        <X509SerialNumber>${serial}</X509SerialNumber>
                    </X509IssuerSerial>
                    <X509Certificate>${certificate}</X509Certificate>
                </X509Data>
            </KeyInfo>
EOT;
        return $xml;
    }

    protected function createSignatureData(string $signedInfo, string $signatureValue, string $keyInfo): string
    {
        $signedInfo = str_replace('<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">', '<SignedInfo>', $signedInfo);
        $xml = <<<EOT
            <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
                ${signedInfo}
                <SignatureValue>${signatureValue}</SignatureValue>
                ${keyInfo}
            </Signature>
EOT;
        return $xml;
    }
}
