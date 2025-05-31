<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatches;

/**
 * Provides signatures based on a Fiel object
 */
final class FielRequestBuilder implements RequestBuilderInterface
{
    public function __construct(private readonly Fiel $fiel)
    {
    }

    public function getFiel(): Fiel
    {
        return $this->fiel;
    }

    public function authorization(DateTime $created, DateTime $expires, string $securityTokenId = ''): string
    {
        $uuid = $securityTokenId ?: $this->createXmlSecurityTokenId();
        $certificate = Helpers::cleanPemContents($this->getFiel()->getCertificatePemContents());

        $keyInfoData = <<<EOT
            <KeyInfo>
                <o:SecurityTokenReference>
                    <o:Reference URI="#$uuid" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"/>
                </o:SecurityTokenReference>
            </KeyInfo>
            EOT;
        $toDigestXml = <<<EOT
            <u:Timestamp xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" u:Id="_0">
                <u:Created>{$created->formatSat()}</u:Created>
                <u:Expires>{$expires->formatSat()}</u:Expires>
            </u:Timestamp>
            EOT;
        $signatureData = $this->createSignature($toDigestXml, '#_0', $keyInfoData);

        $xml = <<<EOT
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <s:Header>
                    <o:Security xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" s:mustUnderstand="1">
                        <u:Timestamp u:Id="_0">
                            <u:Created>{$created->formatSat()}</u:Created>
                            <u:Expires>{$expires->formatSat()}</u:Expires>
                        </u:Timestamp>
                        <o:BinarySecurityToken u:Id="$uuid" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
                            $certificate
                        </o:BinarySecurityToken>
                        $signatureData
                    </o:Security>
                </s:Header>
                <s:Body>
                    <Autentica xmlns="http://DescargaMasivaTerceros.gob.mx"/>
                </s:Body>
            </s:Envelope>
            EOT;

        return Helpers::nospaces($xml);
    }

    public function query(QueryParameters $queryParameters): string
    {
        if (! $queryParameters->getUuid()->isEmpty()) {
            return $this->queryFolio($queryParameters);
        }
        return $this->queryIssuedReceived($queryParameters);
    }

    private function queryFolio(QueryParameters $queryParameters): string
    {
        $rfcSigner = mb_strtoupper($this->getFiel()->getRfc());
        $attributes = [
            'RfcSolicitante' => $rfcSigner,
            'Folio' => $queryParameters->getUuid()->getValue(),
        ];
        return $this->buildFinalXml('SolicitaDescargaFolio', $attributes, '');
    }

    private function queryIssuedReceived(QueryParameters $queryParameters): string
    {
        $xmlRfcReceived = '';
        $requestType = $queryParameters->getRequestType()->getQueryAttributeValue($queryParameters->getServiceType());
        $rfcSigner = mb_strtoupper($this->getFiel()->getRfc());
        $start = $queryParameters->getPeriod()->getStart()->format('Y-m-d\TH:i:s');
        $end = $queryParameters->getPeriod()->getEnd()->format('Y-m-d\TH:i:s');
        if ($queryParameters->getDownloadType()->isIssued()) {
            // issued documents, counterparts are receivers
            $rfcIssuer = $rfcSigner;
            $rfcReceivers = $queryParameters->getRfcMatches();
        } else {
            // received documents, counterpart is issuer
            $rfcIssuer = $queryParameters->getRfcMatches()->getFirst()->getValue();
            $rfcReceivers = RfcMatches::create();
        }

        $attributes = [
            'RfcSolicitante' => $rfcSigner,
            'TipoSolicitud' => $requestType,
            'FechaInicial' => $start,
            'FechaFinal' => $end,
            'RfcEmisor' => $rfcIssuer,
            'TipoComprobante' => $queryParameters->getDocumentType()->value(),
            'EstadoComprobante' => $queryParameters->getDocumentStatus()->getQueryAttributeValue(),
            'RfcACuentaTerceros' => $queryParameters->getRfcOnBehalf()->getValue(),
            'Complemento' => $queryParameters->getComplement()->value(),
        ];
        if ($queryParameters->getDownloadType()->isReceived()) {
            $attributes['RfcReceptor'] = $rfcSigner;
        }
        if (! $rfcReceivers->isEmpty()) {
            $xmlRfcReceived = implode('', array_map(
                fn (RfcMatch $rfcMatch): string => sprintf(
                    '<des:RfcReceptor>%s</des:RfcReceptor>',
                    $this->parseXml($rfcMatch->getValue())
                ),
                iterator_to_array($rfcReceivers)
            ));
            $xmlRfcReceived = "<des:RfcReceptores>$xmlRfcReceived</des:RfcReceptores>";
        }

        $nodeName = $queryParameters->getDownloadType()->isIssued() ? 'SolicitaDescargaEmitidos' : 'SolicitaDescargaRecibidos';

        return $this->buildFinalXml($nodeName, $attributes, $xmlRfcReceived);
    }

    /** @param array<string, string> $attributes */
    private function buildFinalXml(string $nodeName, array $attributes, string $xmlExtra): string
    {
        $attributes = array_filter(
            $attributes,
            static fn (string $value): bool => '' !== $value
        );
        ksort($attributes);

        $solicitudAttributesAsText = implode(' ', array_map(
            fn (string $name, string $value): string => sprintf('%s="%s"', $this->parseXml($name), $this->parseXml($value)),
            array_keys($attributes),
            $attributes,
        ));

        $toDigestXml = <<<EOT
            <des:$nodeName xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
                <des:solicitud $solicitudAttributesAsText>
                    $xmlExtra
                </des:solicitud>
            </des:$nodeName>
            EOT;
        $signatureData = $this->createSignature($toDigestXml);

        $xml = <<<EOT
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
                <s:Header/>
                <s:Body>
                    <des:$nodeName>
                        <des:solicitud $solicitudAttributesAsText>
                            $xmlExtra
                            $signatureData
                        </des:solicitud>
                    </des:$nodeName>
                </s:Body>
            </s:Envelope>
            EOT;

        return Helpers::nospaces($xml);
    }

    public function verify(string $requestId): string
    {
        $xmlRequestId = $this->parseXml($requestId);
        $xmlRfc = $this->parseXml($this->getFiel()->getRfc());

        $toDigestXml = <<<EOT
            <des:VerificaSolicitudDescarga xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
                <des:solicitud IdSolicitud="$xmlRequestId" RfcSolicitante="$xmlRfc"></des:solicitud>
            </des:VerificaSolicitudDescarga>
            EOT;
        $signatureData = $this->createSignature($toDigestXml);

        $xml = <<<EOT
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
                <s:Header/>
                <s:Body>
                    <des:VerificaSolicitudDescarga>
                        <des:solicitud IdSolicitud="$xmlRequestId" RfcSolicitante="$xmlRfc">
                            $signatureData
                        </des:solicitud>
                    </des:VerificaSolicitudDescarga>
                </s:Body>
            </s:Envelope>
            EOT;

        return Helpers::nospaces($xml);
    }

    public function download(string $packageId): string
    {
        $xmlPackageId = $this->parseXml($packageId);
        $xmlRfcOwner = $this->parseXml($this->getFiel()->getRfc());

        $toDigestXml = <<<EOT
            <des:PeticionDescargaMasivaTercerosEntrada xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx">
                <des:peticionDescarga IdPaquete="$xmlPackageId" RfcSolicitante="$xmlRfcOwner"></des:peticionDescarga>
            </des:PeticionDescargaMasivaTercerosEntrada>
            EOT;
        $signatureData = $this->createSignature($toDigestXml);

        $xml = <<<EOT
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
                <s:Header/>
                <s:Body>
                    <des:PeticionDescargaMasivaTercerosEntrada>
                        <des:peticionDescarga IdPaquete="$xmlPackageId" RfcSolicitante="$xmlRfcOwner">
                            $signatureData
                        </des:peticionDescarga>
                    </des:PeticionDescargaMasivaTercerosEntrada>
                </s:Body>
            </s:Envelope>
            EOT;

        return Helpers::nospaces($xml);
    }

    private function createXmlSecurityTokenId(): string
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
        $toDigest = Helpers::nospaces($toDigest);
        $digested = base64_encode(sha1($toDigest, true));
        $signedInfo = $this->createSignedInfoCanonicalExclusive($digested, $signedInfoUri);
        $signatureValue = base64_encode($this->getFiel()->sign($signedInfo, OPENSSL_ALGO_SHA1));
        $signedInfo = str_replace('<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">', '<SignedInfo>', $signedInfo);

        if ('' === $keyInfo) {
            $keyInfo = $this->createKeyInfoData();
        }

        return <<<EOT
            <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
                $signedInfo
                <SignatureValue>$signatureValue</SignatureValue>
                $keyInfo
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
                <Reference URI="$uri">
                    <Transforms>
                        <Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></Transform>
                    </Transforms>
                    <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod>
                    <DigestValue>$digested</DigestValue>
                </Reference>
            </SignedInfo>
            EOT;
        return Helpers::nospaces($xml);
    }

    private function createKeyInfoData(): string
    {
        $fiel = $this->getFiel();
        $certificate = Helpers::cleanPemContents($fiel->getCertificatePemContents());
        $serial = $fiel->getCertificateSerial();
        $issuerName = $this->parseXml($fiel->getCertificateIssuerName());

        return <<<EOT
            <KeyInfo>
                <X509Data>
                    <X509IssuerSerial>
                        <X509IssuerName>$issuerName</X509IssuerName>
                        <X509SerialNumber>$serial</X509SerialNumber>
                    </X509IssuerSerial>
                    <X509Certificate>$certificate</X509Certificate>
                </X509Data>
            </KeyInfo>
            EOT;
    }

    private function parseXml(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_COMPAT);
    }
}
