<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Download;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

final class DownloadResult implements JsonSerializable
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

    /**
     * Status of the download call
     *
     * @return StatusCode
     */
    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    /**
     * If available, contains the package contents
     *
     * @return string
     */
    public function getPackageContent(): string
    {
        return $this->packageContent;
    }

    /**
     * If available, contains the package contents length in bytes
     *
     * @return int
     */
    public function getPackageLenght(): int
    {
        return $this->packageLength;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'length' => $this->packageLength,
        ];
    }
}
