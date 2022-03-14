<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\AbstractRfcFilter;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class RfcMatchTest extends TestCase
{
    public function testExtendsAbstractRfcFilter(): void
    {
        $this->assertInstanceOf(AbstractRfcFilter::class, RfcMatch::empty());
    }
}
