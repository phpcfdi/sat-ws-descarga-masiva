<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\CfdiUuid;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
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

    /** @var DocumentStatus */
    private $documentStatus;

    /** @var CfdiUuid */
    private $uuid;

    /** @var RfcOnBehalf */
    private $rfcOnBehalf;

    /** @var string */
    private $rfcMatch;

    public function __construct(
        DateTimePeriod $period,
        DownloadType $downloadType,
        RequestType $requestType,
        DocumentType $documentType,
        DocumentStatus $documentStatus,
        CfdiUuid $uuid,
        RfcOnBehalf $rfcOnBehalf,
        string $rfcMatch
    ) {
        $this->period = $period;
        $this->downloadType = $downloadType;
        $this->requestType = $requestType;
        $this->documentType = $documentType;
        $this->documentStatus = $documentStatus;
        $this->uuid = $uuid;
        $this->rfcOnBehalf = $rfcOnBehalf;
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
     * @param CfdiUuid|null $uuid If null uses empty
     * @param RfcOnBehalf|null $rfcOnBehalf If null uses empty
     * @param string $rfcMatch Only when counterpart matches this Rfc
     * @return self
     */
    public static function create(
        DateTimePeriod $period,
        DownloadType $downloadType = null,
        RequestType $requestType = null,
        DocumentType $documentType = null,
        DocumentStatus $documentStatus = null,
        CfdiUuid $uuid = null,
        RfcOnBehalf $rfcOnBehalf = null,
        string $rfcMatch = ''
    ): self {
        return new self(
            $period,
            $downloadType ?? DownloadType::issued(),
            $requestType ?? RequestType::metadata(),
            $documentType ?? DocumentType::undefined(),
            $documentStatus ?? DocumentStatus::undefined(),
            $uuid ?? CfdiUuid::empty(),
            $rfcOnBehalf ?? RfcOnBehalf::empty(),
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

    public function getUuid(): CfdiUuid
    {
        return $this->uuid;
    }

    public function getRfcOnBehalf(): RfcOnBehalf
    {
        return $this->rfcOnBehalf;
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
            'uuid' => $this->uuid,
            'rfcOnBehalf' => $this->rfcOnBehalf,
            'rfcMatch' => $this->rfcMatch,
        ];
    }
}
