<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva;

use PhpCfdi\SatWsDescargaMasiva\Internal\SoapFaultInfoExtractor;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Services\Authenticate\AuthenticateTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\Token;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpClientError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\HttpServerError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\SoapFaultError;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Exceptions\WebClientException;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;

/**
 * Main class to consume the SAT web service Descarga Masiva
 */
class Service
{
    /** @var RequestBuilderInterface */
    private $requestBuilder;

    /** @var WebClientInterface */
    private $webclient;

    /** @var Token|null */
    public $currentToken;

    public function __construct(RequestBuilderInterface $requestBuilder, WebClientInterface $webclient, Token $currentToken = null)
    {
        $this->requestBuilder = $requestBuilder;
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

    /**
     * Perform authentication and return a Token, the token might be invalid
     *
     * @return Token
     */
    public function authenticate(): Token
    {
        $authenticateTranslator = new AuthenticateTranslator();
        $soapBody = $authenticateTranslator->createSoapRequest($this->requestBuilder);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.gob.mx/IAutenticacion/Autentica',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/Autenticacion/Autenticacion.svc',
            $soapBody
        );
        return $authenticateTranslator->createTokenFromSoapResponse($responseBody);
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
            $response = $exception->getResponse();
        }
        $this->webclient->fireResponse($response);

        // evaluate SoapFaultInfo
        $fault = SoapFaultInfoExtractor::extract($response->getBody());
        if (null !== $fault) {
            throw new SoapFaultError($request, $response, $fault);
        }

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

    /**
     * Consume the "SolicitaDescarga" web service
     *
     * @param QueryParameters $parameters
     * @return QueryResult
     */
    public function query(QueryParameters $parameters): QueryResult
    {
        $queryTranslator = new QueryTranslator();
        $soapBody = $queryTranslator->createSoapRequest($this->requestBuilder, $parameters);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/ISolicitaDescargaService/SolicitaDescarga',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/SolicitaDescargaService.svc',
            $soapBody,
            $this->obtainCurrentToken()
        );
        return $queryTranslator->createQueryResultFromSoapResponse($responseBody);
    }

    /**
     * Consume the "VerificaSolicitudDescarga" web service
     *
     * @param string $requestId
     * @return VerifyResult
     */
    public function verify(string $requestId): VerifyResult
    {
        $verifyTranslator = new VerifyTranslator();
        $soapBody = $verifyTranslator->createSoapRequest($this->requestBuilder, $requestId);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/IVerificaSolicitudDescargaService/VerificaSolicitudDescarga',
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/VerificaSolicitudDescargaService.svc',
            $soapBody,
            $this->obtainCurrentToken()
        );
        return $verifyTranslator->createVerifyResultFromSoapResponse($responseBody);
    }

    /**
     * Consume the "Descargar" web service
     *
     * @param string $packageId
     * @return DownloadResult
     */
    public function download(string $packageId): DownloadResult
    {
        $downloadTranslator = new DownloadTranslator();
        $soapBody = $downloadTranslator->createSoapRequest($this->requestBuilder, $packageId);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/IDescargaMasivaTercerosService/Descargar',
            'https://cfdidescargamasiva.clouda.sat.gob.mx/DescargaMasivaService.svc',
            $soapBody,
            $this->obtainCurrentToken()
        );
        return $downloadTranslator->createDownloadResultFromSoapResponse($responseBody);
    }
}
