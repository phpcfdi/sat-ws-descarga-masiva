<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\CfdiComplemento;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\FilterComplement;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;

/**
 * This class contains all the information required to perform a query on the SAT Web Service
 */
final class QueryParameters implements JsonSerializable
{
    /** @var DateTimePeriod */
    private $period;

    /** @var DownloadType */
    private $downloadType;

    /** @var RequestType */
    private $requestType;

    /** @var DocumentType */
    private $documentType;

    /** @var FilterComplement */
    private $complement;

    /** @var DocumentStatus */
    private $documentStatus;

    /** @var Uuid */
    private $uuid;

    /** @var RfcOnBehalf */
    private $rfcOnBehalf;

    /** @var RfcMatch */
    private $rfcMatch;

    public function __construct(
        DateTimePeriod $period,
        DownloadType $downloadType,
        RequestType $requestType,
        DocumentType $documentType,
        FilterComplement $complement,
        DocumentStatus $documentStatus,
        Uuid $uuid,
        RfcOnBehalf $rfcOnBehalf,
        RfcMatch $rfcMatch
    ) {
        $this->period = $period;
        $this->downloadType = $downloadType;
        $this->requestType = $requestType;
        $this->documentType = $documentType;
        $this->complement = $complement;
        $this->documentStatus = $documentStatus;
        $this->uuid = $uuid;
        $this->rfcOnBehalf = $rfcOnBehalf;
        $this->rfcMatch = $rfcMatch;
    }

    /**
     * Query static constructor method
     *
     * @param DateTimePeriod|null $period
     * @param DownloadType|null $downloadType
     * @param RequestType|null $requestType
     * @return self
     */
    public static function create(
        ?DateTimePeriod $period = null,
        ?DownloadType $downloadType = null,
        ?RequestType $requestType = null
    ): self {
        return new self(
            $period ?? DateTimePeriod::createFromValues($currentTime = time(), $currentTime),
            $downloadType ?? DownloadType::issued(),
            $requestType ?? RequestType::metadata(),
            DocumentType::undefined(),
            CfdiComplemento::undefined(),
            DocumentStatus::undefined(),
            Uuid::empty(),
            RfcOnBehalf::empty(),
            RfcMatch::empty()
        );
    }

    public function getPeriod(): DateTimePeriod
    {
        return $this->period;
    }

    public function getDownloadType(): DownloadType
    {
        return $this->downloadType;
    }

    public function getRequestType(): RequestType
    {
        return $this->requestType;
    }

    public function getDocumentType(): DocumentType
    {
        return $this->documentType;
    }

    public function getComplement(): FilterComplement
    {
        return $this->complement;
    }

    public function getDocumentStatus(): DocumentStatus
    {
        return $this->documentStatus;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getRfcOnBehalf(): RfcOnBehalf
    {
        return $this->rfcOnBehalf;
    }

    public function getRfcMatch(): RfcMatch
    {
        return $this->rfcMatch;
    }

    public function withPeriod(DateTimePeriod $period): self
    {
        return $this->with('period', $period);
    }

    public function withDownloadType(DownloadType $downloadType): self
    {
        return $this->with('downloadType', $downloadType);
    }

    public function withRequestType(RequestType $requestType): self
    {
        return $this->with('requestType', $requestType);
    }

    public function withDocumentType(DocumentType $documentType): self
    {
        return $this->with('documentType', $documentType);
    }

    public function withComplement(FilterComplement $complement): self
    {
        return $this->with('complement', $complement);
    }

    public function withDocumentStatus(DocumentStatus $documentStatus): self
    {
        return $this->with('documentStatus', $documentStatus);
    }

    public function withUuid(Uuid $uuid): self
    {
        return $this->with('uuid', $uuid);
    }

    public function withRfcOnBehalf(RfcOnBehalf $rfcOnBehalf): self
    {
        return $this->with('rfcOnBehalf', $rfcOnBehalf);
    }

    public function withRfcMatch(RfcMatch $rfcMatch): self
    {
        return $this->with('rfcMatch', $rfcMatch);
    }

    /** @param mixed $value */
    private function with(string $property, $value): self
    {
        $clone = clone $this;
        $clone->{$property} = $value;
        return $clone;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'period' => $this->period,
            'downloadType' => $this->downloadType,
            'requestType' => $this->requestType,
            'documentType' => $this->documentType,
            'complement' => $this->complement,
            'documentStatus' => $this->documentStatus,
            'uuid' => $this->uuid,
            'rfcOnBehalf' => $this->rfcOnBehalf,
            'rfcMatch' => $this->rfcMatch,
        ];
    }
}
