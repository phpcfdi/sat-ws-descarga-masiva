<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

/**
 * This class contains the end points to consume the service
 * Use ServiceEndpoints::cfdi() for "CFDI regulares"
 * Use ServiceEndpoints::retenciones() for "CFDI de retenciones e información de pagos"
 *
 * @see ServiceEndpoints::cfdi()
 * @see ServiceEndpoints::retenciones()
 */
final class ServiceEndpoints
{
    public function __construct(private readonly string $authenticate, private readonly string $query, private readonly string $verify, private readonly string $download, private readonly ServiceType $serviceType)
    {
    }

    /**
     * Create an object with known endpoints for "CFDI regulares"
     */
    public static function cfdi(): self
    {
        return new self(
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/Autenticacion/Autenticacion.svc',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/SolicitaDescargaService.svc',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/VerificaSolicitudDescargaService.svc',
            'https://cfdidescargamasiva.clouda.sat.gob.mx/DescargaMasivaService.svc',
            ServiceType::cfdi()
        );
    }

    /**
     * Create an object with known endpoints for "CFDI de retenciones e información de pagos"
     */
    public static function retenciones(): self
    {
        return new self(
            'https://retendescargamasivasolicitud.clouda.sat.gob.mx/Autenticacion/Autenticacion.svc',
            'https://retendescargamasivasolicitud.clouda.sat.gob.mx/SolicitaDescargaService.svc',
            'https://retendescargamasivasolicitud.clouda.sat.gob.mx/VerificaSolicitudDescargaService.svc',
            'https://retendescargamasiva.clouda.sat.gob.mx/DescargaMasivaService.svc',
            ServiceType::retenciones()
        );
    }

    public function getAuthenticate(): string
    {
        return $this->authenticate;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getVerify(): string
    {
        return $this->verify;
    }

    public function getDownload(): string
    {
        return $this->download;
    }

    public function getServiceType(): ServiceType
    {
        return $this->serviceType;
    }
}
