<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class InteractsXmlTraitTest extends TestCase
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
        $specimen = new InteractsXmlTraitSpecimen();
        $this->assertSame($expected, $specimen->nospaces($source));
    }
}
