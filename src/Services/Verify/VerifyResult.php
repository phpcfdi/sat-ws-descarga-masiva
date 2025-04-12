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
    /** @var list<string> */
    private readonly array $packagesIds;

    public function __construct(
        private readonly StatusCode $status,
        private readonly StatusRequest $statusRequest,
        private readonly CodeRequest $codeRequest,
        private readonly int $numberCfdis,
        string ...$packagesIds,
    ) {
        $this->packagesIds = array_values($packagesIds);
    }

    /**
     * Status of the verification call
     */
    public function getStatus(): StatusCode
    {
        return $this->status;
    }

    /**
     * Status of the query
     */
    public function getStatusRequest(): StatusRequest
    {
        return $this->statusRequest;
    }

    /**
     * Code related to the status of the query
     */
    public function getCodeRequest(): CodeRequest
    {
        return $this->codeRequest;
    }

    /**
     * Number of CFDI given by the query
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
