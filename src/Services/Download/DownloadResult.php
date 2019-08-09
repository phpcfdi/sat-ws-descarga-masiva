<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Download;

use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

class DownloadResult
{
    /** @var StatusCode */
    private $status;

    /** @var string */
    private $package;

    public function __construct(StatusCode $statusCode, string $package)
    {
        $this->status = $statusCode;
        $this->package = $package;
    }

    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getPackageDecoded(): string
    {
        return base64_decode($this->package, true) ?: '';
    }
}
