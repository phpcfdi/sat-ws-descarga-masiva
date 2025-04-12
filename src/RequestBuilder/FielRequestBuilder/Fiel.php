<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder;

use PhpCfdi\Credentials\Credential;

/**
 * Defines a eFirma/FIEL/FEA
 * This object is based on phpcfdi/credentials Credential object
 *
 * @see Credential
 */
final class Fiel
{
    public function __construct(private readonly Credential $credential)
    {
    }

    /**
     * Create a Fiel based on certificate and private key contents
     *
     * @param string $certificateContents Contents of X.509 formats PEM, DER or DER as base64
     * @param string $privateKeyContents Contents of PKCS#8 DER, PKCS#8 PEM or PKCS#5 PEM
     * @param string $passPhrase Private key pass phrase
     */
    public static function create(string $certificateContents, string $privateKeyContents, string $passPhrase): self
    {
        $credential = Credential::create($certificateContents, $privateKeyContents, $passPhrase);
        return new self($credential);
    }

    public function sign(string $toSign, int $algorithm = OPENSSL_ALGO_SHA1): string
    {
        return $this->credential->sign($toSign, $algorithm);
    }

    public function isValid(): bool
    {
        if (! $this->credential->certificate()->satType()->isFiel()) {
            return false;
        }
        return $this->credential->certificate()->validOn();
    }

    public function getCertificatePemContents(): string
    {
        return $this->credential->certificate()->pem();
    }

    public function getRfc(): string
    {
        return $this->credential->rfc();
    }

    public function getCertificateSerial(): string
    {
        return $this->credential->certificate()->serialNumber()->decimal();
    }

    public function getCertificateIssuerName(): string
    {
        return $this->credential->certificate()->issuerAsRfc4514();
    }
}
