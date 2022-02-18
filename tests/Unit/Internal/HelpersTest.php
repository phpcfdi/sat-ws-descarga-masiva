<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Internal;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function testNoSpacesContents(): void
    {
        $source = <<<EOT

            <root>
                <foo a="1" b="2">foo</foo>

                <bar>
                    <baz>
                        BAZZ
                    </baz>
                </bar>
            </root>

            EOT;

        $expected = '<root><foo a="1" b="2">foo</foo><bar><baz>BAZZ</baz></bar></root>';
        $this->assertSame($expected, Helpers::nospaces($source));
    }
}
