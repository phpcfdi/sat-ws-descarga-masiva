<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

trait ComplementoTrait
{
    public static function create(string $id): self
    {
        return new self($id);
    }

    public static function undefined(): self
    {
        return new self('');
    }

    protected static function overrideValues(): array
    {
        return array_combine(
            array_keys(self::MAP),
            array_column(self::MAP, 'satCode')
        );
    }

    public static function getLabels(): array
    {
        return array_combine(
            array_column(self::MAP, 'satCode'),
            array_column(self::MAP, 'label')
        );
    }

    public function label(): string
    {
        $current = $this->value();
        foreach (self::MAP as ['satCode' => $code, 'label' => $label]) {
            if ($code === $current) {
                return $label;
            }
        }
        return ''; // @codeCoverageIgnore
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
