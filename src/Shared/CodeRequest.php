<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

/**
 * @method bool isAccepted()
 * @method bool isExhausted()
 * @method bool isMaximumLimitReaded()
 * @method bool isEmptyResult()
 * @method bool isDuplicated()
 */
final class CodeRequest extends OpenEnum
{
    protected const VALUES = [
        5000 => [
            'name' => 'Accepted',
            'message' => 'Solicitud recibida con éxito',
        ],
        5002 => [
            'name' => 'Exhausted',
            'message' => 'Se agotó las solicitudes de por vida: Máximo para solicitudes con los mismos parámetros',
        ],
        5003 => [
            'name' => 'MaximumLimitReaded',
            'message' => 'Tope máximo: Indica que se está superando el tope máximo de CFDI o Metadata',
        ],
        5004 => [
            'name' => 'EmptyResult',
            'message' => 'No se encontró la información: Indica que no generó paquetes por falta de información.',
        ],
        5005 => [
            'name' => 'Duplicated',
            'message' => 'Solicitud duplicada: Si existe una solicitud vigente con los mismos parámetros',
        ],
    ];
}
