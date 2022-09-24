<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use JsonSerializable;

/**
 * @method static static undefined()
 * @method bool isUndefined()
 */
interface ComplementoInterface extends JsonSerializable
{
    public const UNDEFINED_KEY = 'undefined';

    public const UNDEFINED_VALUES = [
        'satCode' => '',
        'label' => 'Sin complemento definido',
    ];

    /** @return static */
    public static function create(string $id);

    /**
     * Devuelve el identificador del complemento (útil para crear el objeto)
     * y en el valor contiene el nombre del complemento.
     * @return array<string, string>
     */
    public static function getLabels(): array;

    public function label(): string;

    public function value(): string;
}
