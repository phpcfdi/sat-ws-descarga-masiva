<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Download;

use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

class DownloadResult
{
    /** @var StatusCode */
    private $status;

    /** @var string */
    private $packageContent;

    /** @var int */
    private $packageLength;

    public function __construct(StatusCode $statusCode, string $packageContent)
    {
        $this->status = $statusCode;
        $this->packageContent = $packageContent;
        $this->packageLength = strlen($this->packageContent);
    }

    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    public function getPackageContent(): string
    {
        return $this->packageContent;
    }

    public function getPackageLenght(): int
    {
        return $this->packageLength;
    }
}
