<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodEndInvalidDateFormatException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodStartGreaterThanEndException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\PeriodStartInvalidDateFormatException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RequestTypeInvalidException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RfcIsNotIssuerOrReceiverException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions\RfcIssuerAndReceiverAreEmptyException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;

/**
 * Provides signatures based on a Fiel object
 */
final class FielRequestBuilder implements RequestBuilderInterface
{
    /** @var Fiel */
    private $fiel;

    public function __construct(Fiel $fiel)
    {
        $this->fiel = $fiel;
    }

    public function getFiel(): Fiel
    {
        return $this->fiel;
    }

    public function authorization(string $created, string $expires, string $securityTokenId = ''): string
    {
        $uuid = $securityTokenId ?: $this->createXmlSecurityTokenId();
        $certificate = Helpers::cleanPemContents($this->getFiel()->getCertificatePemContents());

        $keyInfoData = <<<EOT
            <KeyInfo>
                <o:SecurityTokenReference>
                    <o:Reference URI="#${uuid}" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"/>
                </o:SecurityTokenReference>
            </KeyInfo>
            EOT;
        $toDigestXml = <<<EOT
            <u:Timestamp xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" u:Id="_0">
                <u:Created>${created}</u:Created>
                <u:Expires>${expires}</u:Expires>
            </u:Timestamp>
            EOT;
        $signatureData = $this->createSignature($toDigestXml, '#_0', $keyInfoData);

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

    public function query(string $start, string $end, string $rfcIssuer, string $rfcReceiver, string $requestType): string
    {
        // normalize input
        $rfcSigner = mb_strtoupper($this->getFiel()->getRfc());
        $rfcIssuer = mb_strtoupper((self::USE_SIGNER === $rfcIssuer) ? $rfcSigner : $rfcIssuer);
        $rfcReceiver = mb_strtoupper((self::USE_SIGNER === $rfcReceiver) ? $rfcSigner : $rfcReceiver);

        // check inputs
        if (! boolval(preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $start))) {
            throw new PeriodStartInvalidDateFormatException($start);
        }
        if (! boolval(preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $end))) {
            throw new PeriodEndInvalidDateFormatException($end);
        }
        if ($start > $end) {
            throw new PeriodStartGreaterThanEndException($start, $end);
        }
        if ('' === $rfcReceiver && '' === $rfcIssuer) {
            throw new RfcIssuerAndReceiverAreEmptyException();
        }
        if (! in_array($rfcSigner, [$rfcReceiver, $rfcIssuer], true)) {
            throw new RfcIsNotIssuerOrReceiverException($rfcSigner, $rfcIssuer, $rfcReceiver);
        }
        if (! in_array($requestType, ['CFDI', 'Metadata'], true)) {
            throw new RequestTypeInvalidException($requestType);
        }

        $solicitudAttributes = array_filter([
            'RfcSolicitante' => $rfcSigner,
            'FechaInicial' => $start,
            'FechaFinal' => $end,
            'TipoSolicitud' => $requestType,
            'RfcEmisor' => $rfcIssuer,
            'RfcReceptor' => $rfcReceiver,
        ]);
        ksort($solicitudAttributes);

        $solicitudAttributesAsText = implode(' ', array_map(
            function (string $name, string $value): string {
                return sprintf('%s="%s"', htmlspecialchars($name, ENT_XML1), htmlspecialchars($value, ENT_XML1));
            },
            array_keys($solicitudAttributes),
            $solicitudAttributes,
        ));

        $toDigestXml = <<<EOT
            <des:SolicitaDescarga xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
                <des:solicitud ${solicitudAttributesAsText}></des:solicitud>
            </des:SolicitaDescarga>
            EOT;
        $signatureData = $this->createSignature($toDigestXml);

        $xml = <<<EOT
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
                <s:Header/>
                <s:Body>
                    <des:SolicitaDescarga>
                        <des:solicitud ${solicitudAttributesAsText}>
                            ${signatureData}
                        </des:solicitud>
                    </des:SolicitaDescarga>
                </s:Body>
            </s:Envelope>
            EOT;

        return $this->nospaces($xml);
    }

    public function verify(string $requestId): string
    {
        $rfc = $this->getFiel()->getRfc();

        $toDigestXml = <<<EOT
            <des:VerificaSolicitudDescarga xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
                <des:solicitud IdSolicitud="${requestId}" RfcSolicitante="${rfc}"></des:solicitud>
            </des:VerificaSolicitudDescarga>
            EOT;
        $signatureData = $this->createSignature($toDigestXml);

        $xml = <<<EOT
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
                <s:Header/>
                <s:Body>
                    <des:VerificaSolicitudDescarga>
                        <des:solicitud IdSolicitud="${requestId}" RfcSolicitante="${rfc}">
                            ${signatureData}
                        </des:solicitud>
                    </des:VerificaSolicitudDescarga>
                </s:Body>
            </s:Envelope>
            EOT;

        return $this->nospaces($xml);
    }

    public function download(string $packageId): string
    {
        $rfcOwner = $this->getFiel()->getRfc();

        $toDigestXml = <<<EOT
            <des:PeticionDescargaMasivaTercerosEntrada xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
                <des:peticionDescarga IdPaquete="${packageId}" RfcSolicitante="${rfcOwner}"></des:peticionDescarga>
            </des:PeticionDescargaMasivaTercerosEntrada>
            EOT;
        $signatureData = $this->createSignature($toDigestXml);

        $xml = <<<EOT
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
                <s:Header/>
                <s:Body>
                    <des:PeticionDescargaMasivaTercerosEntrada>
                        <des:peticionDescarga IdPaquete="${packageId}" RfcSolicitante="${rfcOwner}">
                            ${signatureData}
                        </des:peticionDescarga>
                    </des:PeticionDescargaMasivaTercerosEntrada>
                </s:Body>
            </s:Envelope>
            EOT;

        return $this->nospaces($xml);
    }

    private static function createXmlSecurityTokenId(): string
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

    private function createSignature(string $toDigest, string $signedInfoUri = '', string $keyInfo = ''): string
    {
        $toDigest = $this->nospaces($toDigest);
        $digested = base64_encode(sha1($toDigest, true));
        $signedInfo = $this->createSignedInfoCanonicalExclusive($digested, $signedInfoUri);
        $signatureValue = base64_encode($this->getFiel()->sign($signedInfo, OPENSSL_ALGO_SHA1));
        $signedInfo = str_replace('<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">', '<SignedInfo>', $signedInfo);

        if ('' === $keyInfo) {
            $keyInfo = $this->createKeyInfoData();
        }

        return <<<EOT
            <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
                ${signedInfo}
                <SignatureValue>${signatureValue}</SignatureValue>
                ${keyInfo}
            </Signature>
            EOT;
    }

    private function createSignedInfoCanonicalExclusive(string $digested, string $uri = ''): string
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

    private function createKeyInfoData(): string
    {
        $fiel = $this->getFiel();
        $certificate = Helpers::cleanPemContents($fiel->getCertificatePemContents());
        $serial = $fiel->getCertificateSerial();
        $issuerName = $fiel->getCertificateIssuerName();

        return <<<EOT
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
    }

    private function nospaces(string $input): string
    {
        return preg_replace(['/^\h*/m', '/\h*\r?\n/m'], '', $input) ?? '';
    }
}
