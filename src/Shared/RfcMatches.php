<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/** @implements IteratorAggregate<int, RfcMatch> */
final class RfcMatches implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<int, RfcMatch> */
    private readonly array $items;

    /** @var int<0, max> */
    private readonly int $count;

    private function __construct(RfcMatch ...$items)
    {
        $this->items = array_values($items);
        $this->count = count($items);
    }

    public static function create(RfcMatch ...$items): self
    {
        $map = [];
        foreach ($items as $item) {
            $key = $item->getValue();
            if (! $item->isEmpty() && ! isset($map[$key])) {
                $map[$item->getValue()] = $item;
            }
        }
        return new self(...array_values($map));
    }

    public static function createFromValues(string ...$values): self
    {
        $values = array_map(
            static fn (string $value): RfcMatch => ('' === $value) ? RfcMatch::empty() : RfcMatch::create($value),
            $values
        );
        return self::create(...$values);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count;
    }

    public function getFirst(): RfcMatch
    {
        return $this->items[0] ?? RfcMatch::empty();
    }

    public function count(): int
    {
        return $this->count;
    }

    /** @return Traversable<int, RfcMatch> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /** @return array<int, RfcMatch> */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
