<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;

/**
 * Defines the complement type, use it to consume "CFDI Regulares" service
 *
 * @method static self undefined()
 * @method static self acreditamientoIeps10()
 * @method static self aerolineas10()
 * @method static self cartaporte10()
 * @method static self cartaporte20()
 * @method static self certificadoDestruccion10()
 * @method static self cfdiRegistroFiscal10()
 * @method static self comercioExterior10()
 * @method static self comercioExterior11()
 * @method static self consumoCombustibles10()
 * @method static self consumoCombustibles11()
 * @method static self detallista()
 * @method static self divisas10()
 * @method static self donatarias11()
 * @method static self estadoCuentaCombustibles11()
 * @method static self estadoCuentaCombustibles12()
 * @method static self gastosHidrocarburos10()
 * @method static self institucionesEducativasPrivadas10()
 * @method static self impuestosLocales10()
 * @method static self ine11()
 * @method static self ingresosHidrocarburos10()
 * @method static self leyendasFiscales10()
 * @method static self nomina11()
 * @method static self nomina12()
 * @method static self notariosPublicos10()
 * @method static self obrasArtePlasticasYAntiguedades10()
 * @method static self pagoEnEspecie10()
 * @method static self recepcionPagos10()
 * @method static self recepcionPagos20()
 * @method static self personaFisicaIntegranteCoordinado10()
 * @method static self renovacionYSustitucionVehiculos10()
 * @method static self serviciosParcialesConstruccion10()
 * @method static self spei()
 * @method static self terceros11()
 * @method static self turistaPasajeroExtranjero10()
 * @method static self valesDespensa10()
 * @method static self vehiculoUsado10()
 * @method static self ventaVehiculos11()
 *
 * @method bool isUndefined()
 * @method bool isAcreditamientoIeps10()
 * @method bool isAerolineas10()
 * @method bool isCartaporte10()
 * @method bool isCartaporte20()
 * @method bool isCertificadoDestruccion10()
 * @method bool isCfdiRegistroFiscal10()
 * @method bool isComercioExterior10()
 * @method bool isComercioExterior11()
 * @method bool isConsumoCombustibles10()
 * @method bool isConsumoCombustibles11()
 * @method bool isDetallista()
 * @method bool isDivisas10()
 * @method bool isDonatarias11()
 * @method bool isEstadoCuentaCombustibles11()
 * @method bool isEstadoCuentaCombustibles12()
 * @method bool isGastosHidrocarburos10()
 * @method bool isInstitucionesEducativasPrivadas10()
 * @method bool isImpuestosLocales10()
 * @method bool isIne11()
 * @method bool isIngresosHidrocarburos10()
 * @method bool isLeyendasFiscales10()
 * @method bool isNomina11()
 * @method bool isNomina12()
 * @method bool isNotariosPublicos10()
 * @method bool isObrasArtePlasticasYAntiguedades10()
 * @method bool isPagoEnEspecie10()
 * @method bool isRecepcionPagos10()
 * @method bool isRecepcionPagos20()
 * @method bool isPersonaFisicaIntegranteCoordinado10()
 * @method bool isRenovacionYSustitucionVehiculos10()
 * @method bool isServiciosParcialesConstruccion10()
 * @method bool isSpei()
 * @method bool isTerceros11()
 * @method bool isTuristaPasajeroExtranjero10()
 * @method bool isValesDespensa10()
 * @method bool isVehiculoUsado10()
 * @method bool isVentaVehiculos11()
 */
final class ComplementoCfdi extends Enum implements ComplementoInterface
{
    use ComplementoTrait;

    /** @var array<string, array{satCode: string, label: string}> */
    private const MAP = [
        self::UNDEFINED_KEY => self::UNDEFINED_VALUES,
        'acreditamientoIeps10' => [
            'satCode' => 'acreditamientoieps10',
            'label' => 'Acreditamiento del IEPS 1.0',
        ],
        'aerolineas10' => [
            'satCode' => 'aerolineas',
            'label' => 'Aerolíneas 1.0',
        ],
        'cartaporte10' => [
            'satCode' => 'cartaporte10',
            'label' => 'Carta Porte 1.0',
        ],
        'cartaporte20' => [
            'satCode' => 'cartaporte20',
            'label' => 'Carta Porte 2.0',
        ],
        'certificadoDestruccion10' => [
            'satCode' => 'certificadodedestruccion',
            'label' => 'Certificado de destrucción 1.0',
        ],
        'cfdiRegistroFiscal10' => [
            'satCode' => 'cfdiregistrofiscal',
            'label' => 'CFDI Registro fiscal 1.0',
        ],
        'comercioExterior10' => [
            'satCode' => 'comercioexterior10',
            'label' => 'Comercio Exterior 1.0',
        ],
        'comercioExterior11' => [
            'satCode' => 'comercioexterior11',
            'label' => 'Comercio Exterior 1.1',
        ],
        'consumoCombustibles10' => [
            'satCode' => 'consumodecombustibles',
            'label' => 'Consumo de combustibles 1.0',
        ],
        'consumoCombustibles11' => [
            'satCode' => 'consumodecombustibles11',
            'label' => 'Consumo de combustibles 1.1',
        ],
        'detallista' => [
            'satCode' => 'detallista',
            'label' => 'Detallista',
        ],
        'divisas10' => [
            'satCode' => 'divisas',
            'label' => 'Divisas 1.0',
        ],
        'donatarias11' => [
            'satCode' => 'donat11',
            'label' => 'Donatarias 1.1',
        ],
        'estadoCuentaCombustibles11' => [
            'satCode' => 'ecc11',
            'label' => 'Estado de cuenta de combustibles 1.1',
        ],
        'estadoCuentaCombustibles12' => [
            'satCode' => 'ecc12',
            'label' => 'Estado de cuenta de combustibles 1.2',
        ],
        'gastosHidrocarburos10' => [
            'satCode' => 'gastoshidrocarburos10',
            'label' => 'Gastos Hidrocarburos 1.0',
        ],
        'institucionesEducativasPrivadas10' => [
            'satCode' => 'iedu',
            'label' => 'Instituciones educativas privadas 1.0',
        ],
        'impuestosLocales10' => [
            'satCode' => 'implocal',
            'label' => 'Impuestos locales 1.0',
        ],
        'ine11' => [
            'satCode' => 'ine11',
            'label' => 'INE 1.1',
        ],
        'ingresosHidrocarburos10' => [
            'satCode' => 'ingresoshidrocarburos',
            'label' => 'Ingresos Hidrocarburos 1.0',
        ],
        'leyendasFiscales10' => [
            'satCode' => 'leyendasfisc',
            'label' => 'Leyendas Fiscales 1.0',
        ],
        'nomina11' => [
            'satCode' => 'nomina11',
            'label' => 'Nómina 1.1',
        ],
        'nomina12' => [
            'satCode' => 'nomina12',
            'label' => 'Nómina 1.2',
        ],
        'notariosPublicos10' => [
            'satCode' => 'notariospublicos',
            'label' => 'Notarios públicos 1.0',
        ],
        'obrasArtePlasticasYAntiguedades10' => [
            'satCode' => 'obrasarteantiguedades',
            'label' => 'Obras de arte plásticas y antigüedades 1.0',
        ],
        'pagoEnEspecie10' => [
            'satCode' => 'pagoenespecie',
            'label' => 'Pago en especie 1.0',
        ],
        'recepcionPagos10' => [
            'satCode' => 'pagos10',
            'label' => 'Recepción de pagos 1.0',
        ],
        'recepcionPagos20' => [
            'satCode' => 'pagos20',
            'label' => 'Recepción de pagos 2.0',
        ],
        'personaFisicaIntegranteCoordinado10' => [
            'satCode' => 'pfic',
            'label' => 'Persona física integrante de coordinado 1.0',
        ],
        'renovacionYSustitucionVehiculos10' => [
            'satCode' => 'renovacionysustitucionvehiculos',
            'label' => 'Renovación y sustitución de vehículos 1.0',
        ],
        'serviciosParcialesConstruccion10' => [
            'satCode' => 'servicioparcialconstruccion',
            'label' => 'Servicios parciales de construcción 1.0',
        ],
        'spei' => [
            'satCode' => 'spei',
            'label' => 'SPEI',
        ],
        'terceros11' => [
            'satCode' => 'terceros11',
            'label' => 'Terceros 1.1',
        ],
        'turistaPasajeroExtranjero10' => [
            'satCode' => 'turistapasajeroextranjero',
            'label' => 'Turista pasajero extranjero 1.0',
        ],
        'valesDespensa10' => [
            'satCode' => 'valesdedespensa',
            'label' => 'Vales de despensa 1.0',
        ],
        'vehiculoUsado10' => [
            'satCode' => 'vehiculousado',
            'label' => 'Vehículo usado 1.0',
        ],
        'ventaVehiculos11' => [
            'satCode' => 'ventavehiculos11',
            'label' => 'Venta de vehículos 1.1',
        ],
    ];
}
