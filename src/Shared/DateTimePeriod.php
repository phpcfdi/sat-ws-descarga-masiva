<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Defines a period of time by start of period and end of period values
 */
final class DateTimePeriod implements JsonSerializable
{
    private readonly DateTime $start;

    private readonly DateTime $end;

    public function __construct(DateTime $start, DateTime $end)
    {
        if ($end->compareTo($start) < 0) {
            throw new InvalidArgumentException('The final date must be greater than the initial date');
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Create a new instance of the period object
     *
     * @param DateTime $start
     * @param DateTime $end
     */
    public static function create(DateTime $start, DateTime $end): self
    {
        return new self($start, $end);
    }

    /**
     * Create a new instance of the period object based on a string representations or unix timestamps
     *
     * @param int|string|DateTimeImmutable|null $start
     * @param int|string|DateTimeImmutable|null $end
     */
    public static function createFromValues($start, $end): self
    {
        return self::create(DateTime::create($start), DateTime::create($end));
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }
}
