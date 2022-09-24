# Actualizar de `0.4.x` a `0.5.x`

La versión `0.5` es compatible con el nuevo servicio de solicitud de consultas
de descarga masiva del SAT publicado en marzo 2022.

## Nueva forma de construir una consulta

### Método estático `QueryParameters::create(): self` ha cambiado

Anteriormente, el método `QueryParameters::create()` admitía 4 parámetros:

- `DateTimePeriod $period` (requerido).
- `DownloadType $downloadType = null` (por default a emitidos).
- `RequestType $requestType = null` (por default a metadata).
- `string $rfcMatch = '''` (por default a ninguno).

Ahora, la firma cambió, eliminando el parámetro de texto `$rfcMatch` y haciendo opcional el periodo:

- `?DateTimePeriod $period = null` (por default al tiempo exacto de la creación del objeto).
- `?DownloadType $downloadType = null` (por default a emitidos).
- `?RequestType $requestType = null` (por default a metadata).

Mira las siguientes dos secciones para entender cómo especificar el RFC contraparte
y cambiar cualquier parámetro de la consulta.

### Constructor de `QueryParameters` ha cambiado

Ya no es posible construir un objeto `QueryParameters` con `new`.
Usa el método de fabricación `QueryParameters::create()`.

De la misma forma, el método `QueryParameters::getRfcMatch()` antes devolvía un `string`.
Ahora devuelve un objeto de tipo `RfcMatch`.

### RFC contraparte

El RFC contraparte (parámetro `$rfcMatch`) fue eliminado y ahora se expresa con el objeto `RfcMatch`.
Ver [Filtrado por RFC contraparte](../README.md#filtrado-por-rfc-contraparte-rfcmatchrfcmatches).

### Parámetros encadenados

Ahora se puede crear una consulta sin parámetros e irlos agregando uno a uno con los métodos `with*`.
Ver [Ejemplo de especificación de parámetros](../README.md#ejemplo-de-especificación-de-parámetros).

### Estados de respuesta

Se supone que, después de realizar una consulta, ya no se devuelve el código `404 - Error no controlado`
y que este fue sustituído por `5006 - Error interno en el proceso`. Sin embargo, en la práctica esto
no es lo que está sucediendo y también se devuelve el código `404`. Por ello, al verificar una solicitud,
recomendamos que se verifique `404` y `5006`.

```php
/**
 * La variable result es lo que devolvió la llamada $result = $service->query($parameters);
 * @var \PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryResult $result 
 */

if (in_array($result->getStatus()->getCode(), [404, 5006])) {
    echo "Error del lado del servicio del SAT, intentar más tarde";
}
```

### Tipo de consulta `RequestType`

Anteriormente, se identificaba el tipo de consulta (XML y Metadatos) por las claves `cfdi` y `metadata`.
Ahora se identifican por las claves `xml` y `metadata`.

Si estás construyendo este enumerador tomando un valor de tu base de datos, cambia en tus datos el valor
de `CFDI` a `xml` y `Metadata` a `metadata`.

Revisa tu código y cambia estas llamadas:

- `RequestType::cfdi()` por `RequestType::xml()`.
- `$requestType->isCfdi()` por `$requestType->isXml()`.

### Tipo de servicio `ServiceType`

El SAT tiene los servicios de *CFDI Regulares* y *CFDI de retenciones e información de pagos*
separados en sus URL de consumo.

Si estás haciendo una implementación *normal* del servicio entonces este cambio es transparente.

Si estás haciendo una implementación muy personalizada donde creas el objeto `ServiceEndPoints`
usando su constructor entonces deberás tomar en cuenta las siguientes modificaciones:

En esta versión, el valor del atributo `TipoSolicitud` para *CFDI de Retenciones* cuando se solicitan
los archivos XML cambió de `CFDI` a `Retenciones`. Por ello, la librería ha necesitado cambiar y se
agregó el *enumerador* `ServiceType` y los métodos `QueryParameters::hasServiceType(): bool`,
`QueryParameters::getServiceType(): ServiceType`, `RequestType::getQueryAttributeValue(ServiceType): string`
y al constructor de `ServiceEndPoints` se le agregó la propiedad `ServiceType`.

### Salida JSON de `QueryParameters`

Como `QueryParameters` contiene ahora más propiedades entonces la salida en formato JSON refleja estos cambios.

## Cambios de `DownloadResult`

### Método `DownloadResult::getPackageSize()`

El método `DownloadResult::getPackageLenght()` fue deprecado y sustituido por `DownloadResult::getPackageSize()`.
Porque en PHP los tamaños de bytes regularmente se expresan como *Size* y no como *Length* y porque el método
tenía una falta de ortografía.

### Salida JSON de `DownloadResult`

Al cambiar el nombre de la propiedad que almacena el tamaño en bytes del paquete de *length* a *size*,
la salida JSON también cambió y la clave `length` cambió a `size`.

## Cambios a la API

### Cambios a `RequestBuilderInterface`

Si estás implementando tu propio constructor de mensajes, la interfaz `RequestBuilderInterface` se ha modificado
en los métodos `authorization` y `query`.

Si no estás implementando `RequestBuilderInterface` entonces ignora este cambio.

- El método `authorization` ahora espera recibir un objeto `DateTime` en lugar de un `string`.
- El método `query` ya no recibe parámetros de tipo `string` y ahora recibe un objeto `QueryParameters`.

### Excepciones de `FielRequestBuilder`

El objeto `FielRequestBuilder` generaba excepciones específicas heredadas de tipo `RequestBuilderException`.
En esta nueva versión dichas excepciones ya no son generadas y fueron eliminadas. En específico:

- `PeriodEndInvalidDateFormatException`
- `PeriodStartGreaterThanEndException`
- `PeriodStartInvalidDateFormatException`
- `RequestTypeInvalidException`
- `RfcIsNotIssuerOrReceiverException`
- `RfcIssuerAndReceiverAreEmptyException`
