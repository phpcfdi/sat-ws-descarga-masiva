<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use InvalidArgumentException;

/**
 * Defines a period of time by start of period and end of period values
 */
final class DateTimePeriod
{
    /** @var DateTime */
    private $start;

    /** @var DateTime */
    private $end;

    public function __construct(DateTime $start, DateTime $end)
    {
        if ($end->compareTo($start) < 0) {
            throw new InvalidArgumentException('The final date must be greater than the initial date');
        }

        $this->start = $start;
        $this->end = $end;
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }
}
