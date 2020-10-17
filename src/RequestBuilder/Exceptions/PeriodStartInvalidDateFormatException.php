<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;

final class PeriodStartInvalidDateFormatException extends LogicException implements RequestBuilderException
{
    /** @var string */
    private $periodStart;

    public function __construct(string $periodStart)
    {
        parent::__construct(sprintf('The start date time "%s" does not have the correct format', $periodStart));
        $this->periodStart = $periodStart;
    }

    public function getPeriodStart(): string
    {
        return $this->periodStart;
    }
}
