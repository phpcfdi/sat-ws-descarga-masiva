<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Verify;

use PhpCfdi\SatWsDescargaMasiva\Shared\CodeRequest;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusRequest;

class VerifyResult
{
    /** @var StatusCode */
    private $status;

    /** @var StatusRequest */
    private $statusRequest;

    /** @var CodeRequest */
    private $codeRequest;

    /** @var int */
    private $numberCfdis;

    /** @var string[] */
    private $packagesIds;

    public function __construct(
        StatusCode $status,
        StatusRequest $statusRequest,
        CodeRequest $codeRequest,
        int $numberCfdis,
        string ...$packagesIds
    ) {
        $this->status = $status;
        $this->statusRequest = $statusRequest;
        $this->codeRequest = $codeRequest;
        $this->numberCfdis = $numberCfdis;
        $this->packagesIds = $packagesIds;
    }

    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    public function getStatusRequest(): StatusRequest
    {
        return $this->statusRequest;
    }

    public function getCodeRequest(): CodeRequest
    {
        return $this->codeRequest;
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
}
