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
    private $packageSize;

    public function __construct(StatusCode $statusCode, string $packageContent)
    {
        $this->status = $statusCode;
        $this->packageContent = $packageContent;
        $this->packageSize = strlen($this->packageContent);
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
     * Contains the package contents size in bytes
     *
     * @return int
     */
    public function getPackageSize(): int
    {
        return $this->packageSize;
    }

    /**
     * If available, contains the package contents length in bytes
     *
     * @return int
     * @deprecated 0.5.0
     */
    public function getPackageLenght(): int
    {
        trigger_error(
            'Method DownloadResult::getPackageLenght() is deprecated, use DownloadResult::getPackageSize() instead',
            E_USER_DEPRECATED
        );
        return $this->getPackageSize();
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
