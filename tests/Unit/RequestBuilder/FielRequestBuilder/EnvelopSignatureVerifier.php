<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\FielRequestBuilder;

use DOMDocument;
use DOMElement;
use Exception;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RuntimeException;

class EnvelopSignatureVerifier
{
    /**
     * @param string[] $includeNamespaces
     * @throws Exception If an error on RobRichards\XMLSecLibs occurs
     */
    public function verify(
        string $soapMessage,
        string $namespaceURI,
        string $mainNodeName,
        array $includeNamespaces = [],
        string $certificateContents = '',
    ): bool {
        $soapDocument = new DOMDocument();
        $soapDocument->loadXML($soapMessage);
        $idNS = [];
        $idKeys = [];
        foreach ($includeNamespaces as $namespace) {
            $prefix = strval($soapDocument->lookupPrefix($namespace));
            if ('' !== $prefix) {
                $idNS[$prefix] = $namespace;
                $idKeys[] = "$prefix:Id";
            }
        }

        /** @var DOMElement $mainNode */
        $mainNode = $soapDocument->getElementsByTagNameNS($namespaceURI, $mainNodeName)->item(0);
        /** @var DOMElement $parentNode */
        $parentNode = $mainNode->parentNode;
        $parentNode->removeChild($mainNode);
        $soapDocument->appendChild($mainNode);

        $document = new DOMDocument();
        $document->loadXML(
            str_replace(
                ['<default:', '</default:', ' xmlns:default="http://www.w3.org/2000/09/xmldsig#"'],
                ['<', '</', ''],
                $soapDocument->saveXML($mainNode) ?: ''
            )
        );

        $dSig = new XMLSecurityDSig();
        $dSig->idNS = $idNS;
        $dSig->idKeys = $idKeys;
        $signature = $dSig->locateSignature($document);
        if (null === $signature) {
            throw new RuntimeException('Cannot locate Signature object');
        }

        // this call **must** be made while the signature is still attached to the document
        $signedInfo = $dSig->canonicalizeSignedInfo();
        if (null === $signedInfo || '' === $signedInfo) {
            throw new RuntimeException('Cannot obtain canonicalized SignedInfo');
        }

        // detach the signature, otherwise the enveloped signature content would be
        // included in the digest of the whole document reference (xmlseclibs 4.0)
        if (null !== $signature->parentNode) {
            $signature->parentNode->removeChild($signature);
        }

        $referenceIsValidated = $dSig->validateReference();
        if (true !== $referenceIsValidated) {
            throw new RuntimeException('Cannot locate referenced object');
        }

        $objKey = $dSig->locateKey();
        if (null === $objKey) {
            throw new RuntimeException('Cannot locate XMLSecurityKey object');
        }

        // On xmlseclibs 4.0 using XMLSecEnc::staticLocateKeyInfo fails and is better to extract certificate contents.
        // The method is able to read the certificate, but fail to load it.
        if ('' === $certificateContents) {
            $certificateContents = $this->extractCertificateContents($signature);
        }

        // Since xmlseclibs 4.0 declare isCert as true will fail, use isCert as false.
        // This happens because xmlseclibs is using phpseclib X509 interpreter instead of openssl;
        // this ANS.1 interpreter has trouble with non-standard attributes of signer (responsable: ACDMA-SAT)
        $objKey->loadKey($certificateContents, isFile: false, isCert: false);

        $verifyResult = $dSig->verify($objKey);
        if (1 !== $verifyResult) {
            throw new RuntimeException('Xml Signature verify fail');
        }

        return true;
    }

    private function extractCertificateContents(DOMElement $signature): string
    {
        $certificates = $signature->getElementsByTagNameNS(
            'http://www.w3.org/2000/09/xmldsig#',
            'X509Certificate'
        );
        $certificate = $certificates->item(0);
        if (! $certificate instanceof DOMElement) {
            throw new RuntimeException('Unable to locate element X509Certificate');
        }
        $contents = str_replace(["\r", "\n", ' ', "\t"], '', $certificate->textContent ?? '');
        if ('' === $contents) {
            throw new RuntimeException('Element X509Certificate is empty');
        }
        return "-----BEGIN CERTIFICATE-----\n" . chunk_split($contents, 64, "\n") . "-----END CERTIFICATE-----\n";
    }
}
