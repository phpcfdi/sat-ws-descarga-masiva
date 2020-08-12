<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;

final class QueryParameters
{
    /** @var DateTimePeriod */
    private $period;

    /** @var DownloadType */
    private $downloadType;

    /** @var RequestType */
    private $requestType;

    public function __construct(DateTimePeriod $period, DownloadType $downloadType, RequestType $requestType) {
        $this->period = $period;
        $this->downloadType = $downloadType;
        $this->requestType = $requestType;
    }

    public static function create(DateTimePeriod $period, DownloadType $downloadType, RequestType $requestType): self
    {
        return new self($period, $downloadType, $requestType);
    }


    public static function createDates(DateTime $start, DateTime $end, DownloadType $downloadType, RequestType $requestType): self
    {
        return new self(new DateTimePeriod($start, $end), $downloadType, $requestType);
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
}
