<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use InvalidArgumentException;
use JsonSerializable;
use PhpCfdi\Rfc\Exceptions\InvalidExpressionToParseException;
use PhpCfdi\Rfc\Rfc;
use Throwable;

abstract class AbstractRfcFilter implements JsonSerializable
{
    final protected function __construct(private readonly ?Rfc $value)
    {
    }

    /** @return static */
    public static function create(string $value): self
    {
        try {
            return new static(Rfc::parse($value));
        } catch (InvalidExpressionToParseException $exception) {
            throw new InvalidArgumentException('RFC is invalid', 0, $exception);
        }
    }

    /** @return static */
    public static function empty(): self
    {
        return new static(null);
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
        return null === $this->value;
    }

    public function getValue(): string
    {
        if (null === $this->value) {
            return '';
        }
        return $this->value->getRfc();
    }

    public function jsonSerialize(): ?Rfc
    {
        return $this->value;
    }
}
