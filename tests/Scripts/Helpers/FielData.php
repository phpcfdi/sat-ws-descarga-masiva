<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Helpers;

use PhpCfdi\SatWsDescargaMasiva\Fiel;
use RuntimeException;

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
            $this->readContents($this->getPrivateKeyFile()),
            $this->readContents($this->getCertificateFile()),
            $this->getPassPhrase()
        );
    }

    private function readContents(string $filename): string
    {
        if (! file_exists($filename) || ! is_readable($filename) || is_dir($filename)) {
            throw new RuntimeException("File $filename does not exists, is not readable or is a directory");
        }
        return strval(file_get_contents($filename));
    }
}
