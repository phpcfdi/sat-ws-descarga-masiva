# Excepciones de `phpcfd/sat-ws-descarga-masiva`

El manejo de excepciones en esta librería está basado en el artículo
<https://www.alainschlesser.com/structuring-php-exceptions/>.

Al implementar la librería no se espera que tu implementación fabrique ninguna de estas excepciones.
Lo que se espera es que, cuando se hacen llamadas a los métodos de esta librería, tú puedas atrapar
las excepciones de forma plenamente identificada y con todos los datos contextuales para que las
puedas aprovechar.

## Excepciones lógicas

La librería utiliza la excepción SPL `LogicException` o alguna de sus derivadas como `InvalidArgumentException`
para identificar excepciones de implementación. Cuando se encuentra una excepción de este tipo significa
que se está utilizando erróneamente la librería, es una excepción que te debería llevar a modificar tu
código para que no vuelva a ocurrir.

## Excepciones de tiempo de ejecución

La librería utiliza excepciones de tipo `RuntimeException` para identificar condiciones inesperadas.
Las excepciones identificadas dentro de la librería son de este tipo. 

## Excepciones de PackageReader

`PackageReaderException` es una interfaz para englobar las excepciones lanzadas desde el espacio de nombres
`PhpCfdi\SatWsDescargaMasiva\PackageReader`.
De esta forma se puede utilizar un flujo para atrapar estas excepciones con `try {} catch {PackageReaderException $e}`.

```
- PackageReaderException
    - OpenZipFileException
    - CreateTemporaryZipFileException
```

`OpenZipFileException` es una `PackageReaderException`. Es lanzada cuando no ha sido posible leer un archivo ZIP.
Dentro de sus propiedades está `getFileName(): string` para conocer exactamente la ubicación del archivo que no fue
posible abrir, así como `getCode(): int` para saber el código de error devuelto por el objeto `ZipArchive`.

`CreateTemporaryZipFileException` es una `PackageReaderException`. Es lanzada cuando no ha sido posible almacenar
un archivo temporal con el contenido provisto.

## Excepciones de WebClient

`WebClientException` es una clase para englobar las excepciones lanzadas desde el espacio de nombres
`\PhpCfdi\SatWsDescargaMasiva\WebClient`.
De esta forma se puede utilizar un flujo para atrapar estas excepciones con `try {} catch {WebClientException $e}`.

```
- WebClientException
    - HttpServerError
    - HttpClientError
        - SoapFaultError
```

Las excepciones son de dos tipos `HttpServerError` y `HttpClientError` con una especialización `SoapFaultError`
para cuando la respuesta no fue un error de tipo HTTP, pero el servidor SOAP sí reportó un error.

Lo principal es que `WebClientException` contiene los métodos `getRequest(): Request` y `getResponse(): Response`,
por lo que siempre puedes conocer la comunicación básica HTTP cuando ocurre un error de este tipo.

Adicionalmente, `SoapFaultError` contiene el método `getFault(): SoapFaultInfo`, con lo que se puede conocer
el código y mensaje de error SOAP devuelto por el servidor.

## Excepciones de RequestBuilder

El objeto `FielRequestBuilder` implementa la interfaz `RequestBuilderInterface`.
Los métodos de `RequestBuilderInterface` podrían devolver excepciones de tipo `RequestBuilderException`.

Sin embargo, no es necesario peocuparse por estos métodos, dado que el objeto `FielRequestBuilder` no se
utiliza directamente, solo se utiliza indirectamente a través del objeto `Service`.

Si recibe alguna excepción de este tipo por favor levante un ticket porque significa un error en nuestra librería.
