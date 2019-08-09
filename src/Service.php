<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva;

use PhpCfdi\SatWsDescargaMasiva\Services\Authenticate\AuthenticateTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Shared\Token;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpClientError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpServerError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;

class Service
{
    /** @var Fiel */
    private $fiel;

    /** @var WebClientInterface */
    private $webclient;

    /** @var Token|null */
    public $currentToken;

    public function __construct(Fiel $fiel, WebClientInterface $webclient, Token $currentToken = null)
    {
        $this->fiel = $fiel;
        $this->webclient = $webclient;
        $this->currentToken = $currentToken;
    }

    /**
     * This method will reuse the current token,
     * it will create a new one if there is none or the current token is no longer valid
     *
     * @return Token
     */
    public function obtainCurrentToken(): Token
    {
        if (null === $this->currentToken || ! $this->currentToken->isValid()) {
            $this->currentToken = $this->authenticate();
        }
        return $this->currentToken;
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
        // prepare headers
        $headers = ['SOAPAction' => $soapAction];
        if (null !== $token) {
            $headers['Authorization'] = 'WRAP access_token="' . $token->getValue() . '"';
        }

        // webclient interaction and notifications
        $request = new Request('POST', $uri, $body, $headers);
        $this->webclient->fireRequest($request);
        try {
            $response = $this->webclient->call($request);
        } catch (WebClientException $exception) {
            $this->webclient->fireResponse($exception->getResponse());
            throw $exception;
        }
        $this->webclient->fireResponse($response);

        // evaluate response
        if ($response->statusCodeIsClientError()) {
            $message = sprintf('Unexpected client error status code %d', $response->getStatusCode());
            throw new HttpClientError($message, $request, $response);
        }
        if ($response->statusCodeIsServerError()) {
            $message = sprintf('Unexpected server error status code %d', $response->getStatusCode());
            throw new HttpServerError($message, $request, $response);
        }
        if ($response->isEmpty()) {
            throw new HttpServerError('Unexpected empty response from server', $request, $response);
        }

        return $response->getBody();
    }

    public function query(QueryParameters $parameters): QueryResult
    {
        $queryTranslator = new QueryTranslator();
        $soapBody = $queryTranslator->createSoapRequest($this->fiel, $parameters);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/ISolicitaDescargaService/SolicitaDescarga',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/SolicitaDescargaService.svc',
            $soapBody,
            $this->obtainCurrentToken()
        );
        $queryResult = $queryTranslator->createQueryResultFromSoapResponse($responseBody);
        return $queryResult;
    }

    public function verify(string $requestId): VerifyResult
    {
        $verifyTranslator = new VerifyTranslator();
        $soapBody = $verifyTranslator->createSoapRequest($this->fiel, $requestId);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/IVerificaSolicitudDescargaService/VerificaSolicitudDescarga',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/VerificaSolicitudDescargaService.svc',
            $soapBody,
            $this->obtainCurrentToken()
        );
        $verifyResult = $verifyTranslator->createVerifyResultFromSoapResponse($responseBody);
        return $verifyResult;
    }

    public function download(string $packageId): DownloadResult
    {
        $downloadTranslator = new DownloadTranslator();
        $soapBody = $downloadTranslator->createSoapRequest($this->fiel, $packageId);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/IDescargaMasivaTercerosService/Descargar',
            'https://cfdidescargamasiva.clouda.sat.gob.mx/DescargaMasivaService.svc',
            $soapBody,
            $this->obtainCurrentToken()
        );
        $downloadResult = $downloadTranslator->createDownloadResultFromSoapResponse($responseBody);
        return $downloadResult;
    }
}
