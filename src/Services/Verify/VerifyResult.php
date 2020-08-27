<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Verify;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\CodeRequest;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusRequest;

/**
 * Service Verify Result
 */
final class VerifyResult implements JsonSerializable
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
        StatusCode $statusCode,
        StatusRequest $statusRequest,
        CodeRequest $codeRequest,
        int $numberCfdis,
        string ...$packagesIds
    ) {
        $this->status = $statusCode;
        $this->statusRequest = $statusRequest;
        $this->codeRequest = $codeRequest;
        $this->numberCfdis = $numberCfdis;
        $this->packagesIds = $packagesIds;
    }

    /**
     * Status of the verification call
     *
     * @return StatusCode
     */
    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    /**
     * Status of the query
     *
     * @return StatusRequest
     */
    public function getStatusRequest(): StatusRequest
    {
        return $this->statusRequest;
    }

    /**
     * Code related to the status of the query
     *
     * @return CodeRequest
     */
    public function getCodeRequest(): CodeRequest
    {
        return $this->codeRequest;
    }

    /**
     * Number of CFDI given by the query
     *
     * @return int
     */
    public function getNumberCfdis(): int
    {
        return $this->numberCfdis;
    }

    /**
     * An array containing the package identifications, required to perform the download process
     *
     * @return string[]
     */
    public function getPackagesIds(): array
    {
        return $this->packagesIds;
    }

    /**
     * Count of package identifications
     *
     * @return int
     */
    public function countPackages(): int
    {
        return count($this->packagesIds);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'codeRequest' => $this->codeRequest,
            'statusRequest' => $this->statusRequest,
            'numberCfdis' => $this->numberCfdis,
            'packagesIds' => $this->packagesIds,
        ];
    }
}
