<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva;

use PhpCfdi\SatWsDescargaMasiva\Enums\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Enums\RequestType;

class DownloadRequestQuery
{
    /**
     * @var DateTimePeriod
     */
    private $dateTimePeriod;

    /**
     * @var string
     */
    private $rfc;

    /**
     * @var DownloadType
     */
    private $downloadType;

    /**
     * @var string
     */
    private $requestType;

    /**
     * DownloadRequestQuery constructor.
     *
     * @param DateTimePeriod $dateTimePeriod
     * @param string         $rfc
     * @param DownloadType   $downloadType
     * @param RequestType    $requestType
     */
    public function __construct(
        DateTimePeriod $dateTimePeriod,
        string $rfc,
        DownloadType $downloadType,
        RequestType $requestType
    ) {
        $this->dateTimePeriod = $dateTimePeriod;
        $this->rfc = $rfc;
        $this->downloadType = $downloadType;
        $this->requestType = $requestType;
    }

    public function getDateTimePeriod(): DateTimePeriod
    {
        return $this->dateTimePeriod;
    }

    public function getRfc(): string
    {
        return $this->rfc;
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
