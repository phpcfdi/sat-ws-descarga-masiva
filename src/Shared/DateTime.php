<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;
use Throwable;

/**
 * Defines a date and time
 */
final class DateTime implements JsonSerializable
{
    private readonly DateTimeImmutable $value;

    /**
     * If $value is an integer is used as a timestamp, if is a string is evaluated
     * as an argument for DateTimeImmutable and if it is DateTimeImmutable is used as is.
     *
     * @param int|string|DateTimeImmutable|mixed $value
     * @throws InvalidArgumentException if unable to create a DateTime
     */
    public function __construct(mixed $value = null)
    {
        $value ??= 'now';
        if (is_int($value)) {
            $value = sprintf('@%d', $value);
        }
        if (is_string($value)) {
            try {
                $value = new DateTimeImmutable($value);
            } catch (Throwable $exception) {
                $message = sprintf('Unable to create a Datetime("%s")', strval($value));
                throw new InvalidArgumentException($message, 0, $exception);
            }
        }
        if (! $value instanceof DateTimeImmutable) {
            throw new InvalidArgumentException('Unable to create a Datetime');
        }
        $this->value = $value;
    }

    /**
     * Create a DateTime instance
     *
     * If $value is an integer is used as a timestamp, if is a string is evaluated
     * as an argument for DateTimeImmutable and if it is DateTimeImmutable is used as is.
     *
     * @param int|string|DateTimeImmutable|null $value
     */
    public static function create(mixed $value = null): self
    {
        return new self($value);
    }

    public static function now(): self
    {
        return new self();
    }

    public function formatSat(): string
    {
        return $this->formatTimeZone('Z');
    }

    public function format(string $format, string $timezone = ''): string
    {
        if ('' === $timezone) {
            $timezone = date_default_timezone_get();
        }

        return $this->value->setTimezone(new DateTimeZone($timezone))->format($format);
    }

    public function formatDefaultTimeZone(): string
    {
        return $this->formatTimeZone(date_default_timezone_get());
    }

    public function formatTimeZone(string $timezone): string
    {
        return $this->value->setTimezone(new DateTimeZone($timezone))->format('Y-m-d\TH:i:s.000T');
    }

    public function modify(string $modify): self
    {
        return new self($this->value->modify($modify));
    }

    public function compareTo(self $otherDate): int
    {
        return $this->formatSat() <=> $otherDate->formatSat();
    }

    public function equalsTo(self $expectedExpires): bool
    {
        return $this->formatSat() === $expectedExpires->formatSat();
    }

    public function jsonSerialize(): int
    {
        return intval($this->value->format('U'));
    }
}
