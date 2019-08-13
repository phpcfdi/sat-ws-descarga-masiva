<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use PhpCfdi\Credentials\Credential;

class Fiel
{
    /** @var Credential */
    private $credential;

    public function __construct(Credential $credential)
    {
        $this->credential = $credential;
    }

    public static function create(string $certificate, string $privateKey, string $passPhrase): self
    {
        $credential = Credential::create($certificate, $privateKey, $passPhrase);
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
        if (! $this->credential->certificate()->validOn()) {
            return false;
        }
        return true;
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
