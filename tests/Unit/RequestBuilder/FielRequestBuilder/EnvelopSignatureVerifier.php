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

        // this call **must** be made and before validateReference
        $signedInfo = $dSig->canonicalizeSignedInfo();
        if (empty($signedInfo)) {
            throw new RuntimeException('Cannot obtain canonicalized SignedInfo');
        }

        $referenceIsValidated = $dSig->validateReference();
        if (true !== $referenceIsValidated) {
            throw new RuntimeException('Cannot locate referenced object');
        }

        $objKey = $dSig->locateKey();
        if (null === $objKey) {
            throw new RuntimeException('Cannot locate XMLSecurityKey object');
        }
        if ('' !== $certificateContents) {
            $objKey->loadKey($certificateContents, false, true);
        }

        // must call, otherwise verify will not have the public key to check signature
        $keyInfo = XMLSecEnc::staticLocateKeyInfo($objKey, $signature);
        if (null === $keyInfo) {
            throw new RuntimeException('Cannot extract RSAKeyValue');
        }

        $verifyResult = $dSig->verify($objKey);
        if (1 !== $verifyResult) {
            throw new RuntimeException('Xml Signature verify fail');
        }

        return true;
    }
}
