<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use JsonSerializable;

/**
 * @method bool isUndefined()
 */
interface FilterComplement extends JsonSerializable
{
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
