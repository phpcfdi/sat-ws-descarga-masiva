<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Helpers;

use PhpCfdi\Credentials\Credential;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;

class FielData
{
    /** @var string */
    private $certificateFile;

    /** @var string */
    private $privateKeyFile;

    /** @var string */
    private $passPhrase;

    public function __construct(string $certificateFile, string $privateKeyFile, string $passPhrase)
    {
        $this->certificateFile = $certificateFile;
        $this->privateKeyFile = $privateKeyFile;
        $this->passPhrase = $passPhrase;
    }

    public function getCertificateFile(): string
    {
        return $this->certificateFile;
    }

    public function getPrivateKeyFile(): string
    {
        return $this->privateKeyFile;
    }

    public function getPassPhrase(): string
    {
        return $this->passPhrase;
    }

    public function createFiel(): Fiel
    {
        return new Fiel(
            Credential::openFiles(
                $this->getCertificateFile(),
                $this->getPrivateKeyFile(),
                $this->getPassPhrase()
            )
        );
    }
}
