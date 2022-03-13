<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;
use JsonSerializable;

/**
 * Defines the request type (cfdi or metadata)
 *
 * @method static self undefined()
 * @method static self ingreso()
 * @method static self egreso()
 * @method static self traslado()
 * @method static self nomina()
 * @method static self pago()
 *
 * @method bool isUndefined()
 * @method bool isIngreso()
 * @method bool isEgreso()
 * @method bool isTraslado()
 * @method bool isNomina()
 * @method bool isPago()
 */
final class DocumentType extends Enum implements JsonSerializable
{
    protected static function overrideValues(): array
    {
        return [
            'undefined' => '',
            'ingreso' => 'I',
            'egreso' => 'E',
            'traslado' => 'T',
            'nomina' => 'N',
            'pago' => 'P',
        ];
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
