<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva;

use PhpCfdi\SatWsDescargaMasiva\Enums\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Enums\RequestType;

class DownloadRequestQuery
{
    /** @var DateTimePeriod */
    private $dateTimePeriod;

    /** @var DownloadType */
    private $downloadType;

    /** @var RequestType */
    private $requestType;

    public function __construct(
        DateTimePeriod $dateTimePeriod,
        DownloadType $downloadType,
        RequestType $requestType
    ) {
        $this->dateTimePeriod = $dateTimePeriod;
        $this->downloadType = $downloadType;
        $this->requestType = $requestType;
    }

    public function getDateTimePeriod(): DateTimePeriod
    {
        return $this->dateTimePeriod;
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
