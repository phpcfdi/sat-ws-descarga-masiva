<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use InvalidArgumentException;
use JsonSerializable;
use Throwable;

final class Uuid implements JsonSerializable
{
    private function __construct(private readonly string $value)
    {
    }

    public static function create(string $value): self
    {
        $value = strtolower($value);
        if (! preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/', $value)) {
            throw new InvalidArgumentException('UUID does not have the correct format');
        }
        return new self($value);
    }

    public static function empty(): self
    {
        return new self('');
    }

    public static function check(string $value): bool
    {
        try {
            self::create($value);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function isEmpty(): bool
    {
        return '' === $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
