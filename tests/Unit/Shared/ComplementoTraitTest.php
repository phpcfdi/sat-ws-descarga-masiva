<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class ComplementoTraitTest extends TestCase
{
    public function testCreateFactoryMethod(): void
    {
        $expectedLabels = [
            '' => 'Sin complemento definido',
            'foo10' => 'Complemento Foo 1.0',
            'bar20' => 'Complemento Bar 2.0',
        ];

        $this->assertSame($expectedLabels, ComplementoForTesting::getLabels());
    }
}
