<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Verify;

use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;

class VerifyResult
{
    /** @var StatusCode */
    private $status;

    /** @var int */
    private $statusRequest;

    /** @var int */
    private $statusCodeRequest;

    /** @var int */
    private $numberCfdis;

    /** @var string[] */
    private $packagesIds;

    public function __construct(
        StatusCode $statusCode,
        int $statusRequest,
        int $statusCodeRequest,
        int $numberCfdis,
        string ...$packagesIds
    ) {
        $this->status = $statusCode;
        $this->statusRequest = $statusRequest;
        $this->statusCodeRequest = $statusCodeRequest;
        $this->numberCfdis = $numberCfdis;
        $this->packagesIds = $packagesIds;
    }

    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    public function getStatusRequest(): int
    {
        return $this->statusRequest;
    }

    public function getStatusCodeRequest(): int
    {
        return $this->statusCodeRequest;
    }

    public function getNumberCfdis(): int
    {
        return $this->numberCfdis;
    }

    /** @return string[] */
    public function getPackagesIds(): array
    {
        return $this->packagesIds;
    }

    public function countPackages(): int
    {
        return count($this->packagesIds);
    }

    public function isAccepted(): bool
    {
        return (1 === $this->getStatusRequest());
    }

    public function inProgress(): bool
    {
        return (2 === $this->getStatusRequest());
    }

    public function isFinished(): bool
    {
        return (3 === $this->getStatusRequest());
    }

    public function hasError(): bool
    {
        return (4 === $this->getStatusRequest());
    }

    public function isRejected(): bool
    {
        return (5 === $this->getStatusRequest());
    }

    public function isExpired(): bool
    {
        return (6 === $this->getStatusRequest());
    }
}
