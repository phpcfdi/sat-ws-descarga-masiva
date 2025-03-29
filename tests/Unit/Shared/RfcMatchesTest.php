<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use Countable;
use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatches;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use Traversable;

final class RfcMatchesTest extends TestCase
{
    public function testObjectDefinition(): void
    {
        $list = RfcMatches::create();
        $this->assertInstanceOf(Countable::class, $list);
        $this->assertInstanceOf(Traversable::class, $list);
        $this->assertInstanceOf(JsonSerializable::class, $list);
    }

    public function testCreateEmpty(): void
    {
        $list = RfcMatches::create();
        $this->assertCount(0, $list);
        $this->assertTrue($list->isEmpty());
    }

    public function testCreateThreeDifferentElements(): void
    {
        $items = [
            RfcMatch::create('AAA010101001'),
            RfcMatch::create('AAA010101002'),
            RfcMatch::create('AAA010101003'),
        ];
        $list = RfcMatches::create(...$items);
        $this->assertCount(3, $list);
        $this->assertSame($items, iterator_to_array($list));
        $this->assertFalse($list->isEmpty());
    }

    public function testCreateWithEmptyAndRepeated(): void
    {
        $items = [
            RfcMatch::empty(),
            $first = RfcMatch::create('AAA010101001'),
            RfcMatch::empty(),
            $second = RfcMatch::create('AAA010101002'),
            RfcMatch::create('AAA010101001'), // repeated
            RfcMatch::create('AAA010101001'), // repeated
            RfcMatch::create('AAA010101002'), // repeated
            RfcMatch::create('AAA010101002'), // repeated
        ];
        $list = RfcMatches::create(...$items);
        $this->assertCount(2, $list);
        $this->assertSame([$first, $second], iterator_to_array($list));
    }

    public function testCreateFromValues(): void
    {
        $first = 'AAA010101001';
        $second = 'AAA010101002';
        $list = RfcMatches::createFromValues(
            '', // empty
            $first,
            '', // empty
            $second,
            $first,   // repeated
            $first,   // repeated
            $second,  // repeated
            $second   // repeated
        );
        $this->assertCount(2, $list);
        $this->assertEquals([RfcMatch::create($first), RfcMatch::create($second)], iterator_to_array($list));
    }

    public function testFirstWithEmptyList(): void
    {
        $list = RfcMatches::create();
        $this->assertEquals(RfcMatch::empty(), $list->getFirst());
    }

    public function testFirstWithPopulatedList(): void
    {
        $list = RfcMatches::create(
            $first = RfcMatch::create('AAA010101001'),
            RfcMatch::create('AAA010101002')
        );
        $this->assertSame($first, $list->getFirst());
    }

    public function testJsonSerialize(): void
    {
        $list = RfcMatches::create(
            RfcMatch::create('AAA010101001'),
            RfcMatch::create('AAA010101002'),
            RfcMatch::create('AAA010101003')
        );
        $expectedJson = json_encode(iterator_to_array($list)) ?: '';
        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            json_encode($list) ?: '',
            'Exported json should be an array of elements'
        );
    }
}
