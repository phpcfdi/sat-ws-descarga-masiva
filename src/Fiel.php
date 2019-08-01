<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva;

use CfdiUtils\OpenSSL\OpenSSL;
use CfdiUtils\PemPrivateKey\PemPrivateKey;

class Fiel
{
    /** @var PemPrivateKey */
    private $privateKey;

    /** @var Certificado*/
    private $certificate;

    /** @var string */
    private $passPhrase;

    public function __construct(string $pemPrivateKey, string $pemCertificate, string $passPhrase)
    {
        $openSsl = new OpenSSL();
        $this->privateKey = new PemPrivateKey($pemPrivateKey, $openSsl);
        $this->certificate = new Certificado($pemCertificate, $openSsl);
        $this->passPhrase = $passPhrase;
    }

    public function sign(string $toSign, int $algorithm = OPENSSL_ALGO_SHA1): string
    {
        $this->privateKey->open($this->passPhrase);
        try {
            return $this->privateKey->sign($toSign, $algorithm);
        } finally {
            $this->privateKey->close();
        }
    }

    public function isValid(): bool
    {
        if (! $this->privateKey->open($this->passPhrase)) {
            return false;
        }
        try {
            return $this->privateKey->belongsTo($this->getCertificatePemContents());
        } finally {
            $this->privateKey->close();
        }
    }

    public function getCertificatePemContents(): string
    {
        return $this->certificate->getPemContents();
    }
}
