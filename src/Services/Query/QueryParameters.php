<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;

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

    /** @var string */
    private $rfcMatch;

    public function __construct(
        DateTimePeriod $period,
        DownloadType $downloadType,
        RequestType $requestType,
        string $rfcMatch
    ) {
        $this->period = $period;
        $this->downloadType = $downloadType;
        $this->requestType = $requestType;
        $this->rfcMatch = $rfcMatch;
    }

    /**
     * Query static constructor method
     *
     * @param DateTimePeriod $period
     * @param DownloadType|null $downloadType if null uses Issued
     * @param RequestType|null $requestType If null uses Metadata
     * @param string $rfcMatch Only when counterpart matches this Rfc
     * @return self
     */
    public static function create(
        DateTimePeriod $period,
        DownloadType $downloadType = null,
        RequestType $requestType = null,
        string $rfcMatch = ''
    ): self {
        $downloadType = $downloadType ?: DownloadType::issued();
        $requestType = $requestType ?? RequestType::metadata();
        return new self($period, $downloadType, $requestType, $rfcMatch);
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
            'rfcMatch' => $this->rfcMatch,
        ];
    }
}
