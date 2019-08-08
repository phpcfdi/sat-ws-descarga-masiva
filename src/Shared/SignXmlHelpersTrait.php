<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

trait SignXmlHelpersTrait
{
    public function createSignedInfoCanonicalExclusive(string $digested, $uri = ''): string
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

    public function createKeyInfoData(Fiel $fiel): string
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

    public function createSignatureData(string $signedInfo, string $signatureValue, string $keyInfo): string
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
