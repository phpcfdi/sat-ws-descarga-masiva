<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Download;

class DownloadResult
{
    /** @var int */
    private $statusCode;

    /** @var string */
    private $message;

    /** @var string */
    private $package;

    public function __construct(int $statusCode, string $message, string $package)
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->package = $package;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getPackageDecoded(): string
    {
        return base64_decode($this->package, true) ?: '';
    }

    public function isAccepted(): bool
    {
        return (5000 === $this->getStatusCode());
    }
}
