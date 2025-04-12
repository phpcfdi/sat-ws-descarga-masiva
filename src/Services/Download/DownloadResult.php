<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Download;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

final class DownloadResult implements JsonSerializable
{
    private readonly int $packageSize;

    public function __construct(private readonly StatusCode $status, private readonly string $packageContent)
    {
        $this->packageSize = strlen($this->packageContent);
    }

    /**
     * Status of the download call
     */
    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    /**
     * If available, contains the package contents
     */
    public function getPackageContent(): string
    {
        return $this->packageContent;
    }

    /**
     * Contains the package contents size in bytes
     */
    public function getPackageSize(): int
    {
        return $this->packageSize;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'size' => $this->packageSize,
        ];
    }
}
