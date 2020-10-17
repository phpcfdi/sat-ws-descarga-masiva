<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;

final class PeriodStartGreaterThanEndException extends LogicException implements RequestBuilderException
{
    /** @var string */
    private $periodStart;

    /** @var string */
    private $periodEnd;

    public function __construct(string $periodStart, string $periodEnd)
    {
        parent::__construct(sprintf('The period start "%s" is greater than end "%s"', $periodStart, $periodEnd));
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function getPeriodStart(): string
    {
        return $this->periodStart;
    }

    public function getPeriodEnd(): string
    {
        return $this->periodEnd;
    }
}
