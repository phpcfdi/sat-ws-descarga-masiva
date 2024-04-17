<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use Eclipxe\Enum\Enum;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoInterface;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoTrait;

/**
 * Class extending Enum and implementing ComplementoInterface to test ComplementoTrait
 *
 * @method static self undefined()
 * @method static self foo10()
 * @method static self bar20()
 *
 * @method bool isUndefined()
 * @method bool isFoo10()
 * @method bool isBar20()
 */
final class ComplementoForTesting extends Enum implements ComplementoInterface
{
    use ComplementoTrait;

    private const MAP = [
        self::UNDEFINED_KEY => self::UNDEFINED_VALUES,
        'foo' => [
            'satCode' => 'foo10',
            'label' => 'Complemento Foo 1.0',
        ],
        'bar20' => [
            'satCode' => 'bar20',
            'label' => 'Complemento Bar 2.0',
        ],
    ];
}
