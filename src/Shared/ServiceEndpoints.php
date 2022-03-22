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
    /** @var string */
    private $authenticate;

    /** @var string */
    private $query;

    /** @var string */
    private $verify;

    /** @var string */
    private $download;

    /** @var ServiceType */
    private $serviceType;

    public function __construct(
        string $authenticate,
        string $query,
        string $verify,
        string $download,
        ServiceType $serviceType
    ) {
        $this->authenticate = $authenticate;
        $this->query = $query;
        $this->verify = $verify;
        $this->download = $download;
        $this->serviceType = $serviceType;
    }

    /**
     * Create an object with known endpoints for "CFDI regulares"
     *
     * @return self
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
     *
     * @return self
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
