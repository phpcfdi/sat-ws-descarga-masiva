<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva;

use PhpCfdi\SatWsDescargaMasiva\Translators\AuthenticateTranslator;
use PhpCfdi\SatWsDescargaMasiva\Translators\DownloadRequestTranslator;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpClientError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpServerError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;

class Service
{
    /** @var Fiel */
    private $fiel;

    /** @var WebClientInterface */
    private $webclient;

    public function __construct(Fiel $fiel, WebClientInterface $webclient)
    {
        $this->fiel = $fiel;
        $this->webclient = $webclient;
    }

    public function authenticate(): Token
    {
        $authenticateTranslator = new AuthenticateTranslator();
        $soapBody = $authenticateTranslator->createSoapRequest($this->fiel);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.gob.mx/IAutenticacion/Autentica',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/Autenticacion/Autenticacion.svc',
            $soapBody
        );
        $token = $authenticateTranslator->createTokenFromSoapResponse($responseBody);
        return $token;
    }

    public function consume(string $soapAction, string $uri, string $body, ?Token $token = null): string
    {
        $headers = ['SOAPAction' => $soapAction];

        if (null !== $token) {
            $headers['Authorization'] = 'WRAP access_token="' . $token->getValue() . '"';
        }

        $request = new Request('POST', $uri, $body, $headers);
        $response = $this->webclient->call($request);
        if ($response->statusCodeIsClientError()) {
            throw new HttpClientError(
                sprintf('Unexpected client error status code %d', $response->getStatusCode()),
                $request
            );
        }
        if ($response->statusCodeIsServerError()) {
            throw new HttpServerError(
                sprintf('Unexpected client error status code %d', $response->getStatusCode()),
                $request
            );
        }
        if ($response->isEmpty()) {
            throw new HttpServerError('Unexpected empty response from server', $request);
        }

        return $response->getBody();
    }

    public function downloadRequest(DownloadRequestQuery $downloadRequestQuery): DownloadRequestResult
    {
        $downloadRequestTranslator = new DownloadRequestTranslator();
        $soapBody = $downloadRequestTranslator->createSoapRequest($this->fiel, $downloadRequestQuery);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/ISolicitaDescargaService/SolicitaDescarga',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/SolicitaDescargaService.svc',
            $soapBody,
            $this->authenticate()
        );
        $downloadRequestResponse = $downloadRequestTranslator->createDownloadRequestResultFromSoapResponse($responseBody);
        return $downloadRequestResponse;
    }
}
