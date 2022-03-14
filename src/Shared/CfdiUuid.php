<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use InvalidArgumentException;
use JsonSerializable;
use Throwable;

final class CfdiUuid implements JsonSerializable
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function create(string $value): self
    {
        $value = strtolower($value);
        self::check($value);
        return new self($value);
    }

    public static function empty(): self
    {
        return new self('');
    }

    public static function parse(string $value): ?self
    {
        try {
            return self::create($value);
        } catch (Throwable $exception) {
            return null;
        }
    }

    public static function check(string $value): void
    {
        if (! preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/', $value)) {
            throw new InvalidArgumentException('UUID does not have the correct format');
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
