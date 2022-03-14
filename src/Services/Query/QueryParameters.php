<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;

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

    /** @var DocumentStatus */
    private $documentStatus;

    /** @var string */
    private $rfcMatch;

    public function __construct(
        DateTimePeriod $period,
        DownloadType $downloadType,
        RequestType $requestType,
        DocumentType $documentType,
        DocumentStatus $documentStatus,
        string $rfcMatch
    ) {
        $this->period = $period;
        $this->downloadType = $downloadType;
        $this->requestType = $requestType;
        $this->documentType = $documentType;
        $this->documentStatus = $documentStatus;
        $this->rfcMatch = $rfcMatch;
    }

    /**
     * Query static constructor method
     *
     * @param DateTimePeriod $period
     * @param DownloadType|null $downloadType if null uses Issued
     * @param RequestType|null $requestType If null uses Metadata
     * @param DocumentType|null $documentType If null uses Undefined
     * @param DocumentStatus|null $documentStatus If null uses Undefined
     * @param string $rfcMatch Only when counterpart matches this Rfc
     * @return self
     */
    public static function create(
        DateTimePeriod $period,
        DownloadType $downloadType = null,
        RequestType $requestType = null,
        DocumentType $documentType = null,
        DocumentStatus $documentStatus = null,
        string $rfcMatch = ''
    ): self {
        return new self(
            $period,
            $downloadType ?? DownloadType::issued(),
            $requestType ?? RequestType::metadata(),
            $documentType ?? DocumentType::undefined(),
            $documentStatus ?? DocumentStatus::undefined(),
            $rfcMatch
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

    public function getDocumentStatus(): DocumentStatus
    {
        return $this->documentStatus;
    }

    public function getRfcMatch(): string
    {
        return $this->rfcMatch;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'period' => $this->period,
            'downloadType' => $this->downloadType,
            'requestType' => $this->requestType,
            'documentType' => $this->documentType,
            'documentStatus' => $this->documentStatus,
            'rfcMatch' => $this->rfcMatch,
        ];
    }
}
