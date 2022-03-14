<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use InvalidArgumentException;
use JsonSerializable;
use PhpCfdi\Rfc\Exceptions\InvalidExpressionToParseException;
use PhpCfdi\Rfc\Rfc;
use Throwable;

final class RfcOnBehalf implements JsonSerializable
{
    /** @var Rfc|null */
    private $value;

    private function __construct(?Rfc $value)
    {
        $this->value = $value;
    }

    public static function create(string $value): self
    {
        try {
            return new self(Rfc::parse($value));
        } catch (InvalidExpressionToParseException $exception) {
            throw new InvalidArgumentException('RFC is invalid', 0, $exception);
        }
    }

    public static function empty(): self
    {
        return new self(null);
    }

    public static function check(string $value): bool
    {
        try {
            self::create($value);
            return true;
        } catch (Throwable $exception) {
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
