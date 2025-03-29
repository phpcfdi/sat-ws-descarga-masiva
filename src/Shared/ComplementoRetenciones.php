<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;

/**
 * Defines the complement type, use it to consume "CFDI de retenciones e información de pagos" service
 *
 * @method static self undefined()
 * @method static self arrendamientoEnFideicomiso()
 * @method static self dividendos()
 * @method static self enajenacionAcciones()
 * @method static self fideicomisoNoEmpresarial()
 * @method static self intereses()
 * @method static self interesesHipotecarios()
 * @method static self operacionesConDerivados()
 * @method static self pagosAExtranjeros()
 * @method static self planesRetiro10()
 * @method static self planesRetiro11()
 * @method static self premios()
 * @method static self sectorFinanciero()
 * @method static self serviciosPlataformasTecnologicas()
 *
 * @method bool isUndefined()
 * @method bool isArrendamientoEnFideicomiso()
 * @method bool isDividendos()
 * @method bool isEnajenacionAcciones()
 * @method bool isFideicomisoNoEmpresarial()
 * @method bool isIntereses()
 * @method bool isInteresesHipotecarios()
 * @method bool isOperacionesConDerivados()
 * @method bool isPagosAExtranjeros()
 * @method bool isPlanesRetiro10()
 * @method bool isPlanesRetiro11()
 * @method bool isPremios()
 * @method bool isSectorFinanciero()
 * @method bool isServiciosPlataformasTecnologicas()
 */
final class ComplementoRetenciones extends Enum implements ComplementoInterface
{
    use ComplementoTrait;

    /** @var array<string, array{satCode: string, label: string}> */
    private const MAP = [
        self::UNDEFINED_KEY => self::UNDEFINED_VALUES,
        'arrendamientoEnFideicomiso' => [
            'satCode' => 'arrendamientoenfideicomiso',
            'label' => 'Arrendamiento en fideicomiso',
        ],
        'dividendos' => [
            'satCode' => 'dividendos',
            'label' => 'Dividendos',
        ],
        'enajenacionAcciones' => [
            'satCode' => 'enajenaciondeacciones',
            'label' => 'Enajenación de acciones',
        ],
        'fideicomisoNoEmpresarial' => [
            'satCode' => 'fideicomisonoempresarial',
            'label' => 'Fideicomiso no empresarial',
        ],
        'intereses' => [
            'satCode' => 'intereses',
            'label' => 'Intereses',
        ],
        'interesesHipotecarios' => [
            'satCode' => 'intereseshipotecarios',
            'label' => 'Intereses hipotecarios',
        ],
        'operacionesConDerivados' => [
            'satCode' => 'operacionesconderivados',
            'label' => 'Operaciones con derivados',
        ],
        'pagosAExtranjeros' => [
            'satCode' => 'pagosaextranjeros',
            'label' => 'Pagos a extranjeros',
        ],
        'planesRetiro10' => [
            'satCode' => 'planesderetiro',
            'label' => 'Planes de retiro 1.0',
        ],
        'planesRetiro11' => [
            'satCode' => 'planesderetiro11',
            'label' => 'Planes de retiro 1.1',
        ],
        'premios' => [
            'satCode' => 'premios',
            'label' => 'Premios',
        ],
        'sectorFinanciero' => [
            'satCode' => 'sectorfinanciero',
            'label' => 'Sector Financiero',
        ],
        'serviciosPlataformasTecnologicas' => [
            'satCode' => 'serviciosplataformastecnologicas10',
            'label' => 'Servicios Plataformas Tecnológicas',
        ],
    ];
}
