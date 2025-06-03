# phpcfdi/sat-ws-descarga-masiva

[![Source Code][badge-source]][source]
[![Packagist PHP Version Support][badge-php-version]][php-version]
[![Discord][badge-discord]][discord]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Reliability][badge-reliability]][reliability]
[![Maintainability][badge-maintainability]][maintainability]
[![Code Coverage][badge-coverage]][coverage]
[![Violations][badge-violations]][violations]
[![Total Downloads][badge-downloads]][downloads]

> Librería para usar el servicio web del SAT de Descarga Masiva

:us: The documentation of this project is in spanish as this is the natural language for intented audience.

:mexico: La documentación del proyecto está en español porque ese es el lenguaje principal de los usuarios.
También te esperamos en [el canal #phpcfdi de discord](https://discord.gg/aFGYXvX)

Esta librería contiene un cliente (consumidor) del servicio del SAT de
**Servicio Web de Descarga Masiva de CFDI y Retenciones** versión 1.5 (2025-05-30).

## Instalación

Utiliza [composer](https://getcomposer.org/), instala de la siguiente forma:

```shell
composer require phpcfdi/sat-ws-descarga-masiva
```

## Ejemplos de uso

Todos los objetos de entrada y salida se pueden exportar como JSON para su fácil depuración.

### Creación el servicio

Ejemplo creando el servicio usando una FIEL disponible localmente.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;

// Creación de la FIEL, puede leer archivos DER (como los envía el SAT) o PEM (convertidos con openssl)
$fiel = Fiel::create(
    file_get_contents('certificado.cer'),
    file_get_contents('llaveprivada.key'),
    '12345678a'
);

// verificar que la FIEL sea válida (no sea CSD y sea vigente acorde a la fecha del sistema)
if (! $fiel->isValid()) {
    return;
}

// creación del web client basado en Guzzle que implementa WebClientInterface
// para usarlo necesitas instalar guzzlehttp/guzzle, pues no es una dependencia directa
$webClient = new GuzzleWebClient();

// creación del objeto encargado de crear las solicitudes firmadas usando una FIEL
$requestBuilder = new FielRequestBuilder($fiel);

// Creación del servicio
$service = new Service($requestBuilder, $webClient);
```

### Cliente para consumir los servicios de CFDI de Retenciones

Existen dos tipos de Comprobantes Fiscales Digitales, los regulares (ingresos, egresos, traslados, nóminas y pagos),
y los CFDI de retenciones e información de pagos (retenciones).

Puede utilizar esta librería para consumir los CFDI de Retenciones. Para lograrlo construya el servicio con
la especificación de `ServiceEndpoints::retenciones()`.

Los constructores `ServiceEndpoints::cfdi()` y `ServiceEndpoints::retenciones()` agregan automáticamente
la propiedad `ServiceType` al objeto. Esta propiedad será después utilizada el servicio para especificar
el valor en la consulta antes de consumirla.

```php
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;

/**
 * @var GuzzleWebClient $webClient Cliente de Guzzle previamente fabricado
 * @var RequestBuilderInterface $requestBuilder Creador de solicitudes, previamente fabricado
 */
// Creación del servicio
$service = new Service($requestBuilder, $webClient, null, ServiceEndpoints::retenciones());
```

Aunque no es recomendado, también puedes construir el objeto `ServiceEndpoints` con direcciones URL del
servicio personalizadas utilizando el constructor del objeto en lugar de los métodos estáticos.

### Realizar una consulta

Una vez creado el servicio, se puede presentar la consulta, si se pudo presentar devolverá el identificador de la solicitud,
y con este identificador se podrá continuar al servicio de verificación.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;

// Crear la consulta
$request = QueryParameters::create(
    DateTimePeriod::createFromValues('2019-01-13 00:00:00', '2019-01-13 23:59:59'),
);

// presentar la consulta
$query = $service->query($request);

// verificar que el proceso de consulta fue correcto
if (! $query->getStatus()->isAccepted()) {
    echo "Fallo al presentar la consulta: {$query->getStatus()->getMessage()}";
    return;
}

// el identificador de la consulta está en $query->getRequestId()
echo "Se generó la solicitud {$query->getRequestId()}", PHP_EOL;
```

### Parámetros de la consulta

#### Periodo (`DateTimePeriod`)

Fecha y hora de inicio y fin de la consulta.
Si no se especifica crea un periodo del segundo exacto de la creación del objeto.

#### Tipo de descarga (`DownloadType`)

Establece si la solicitud es de documentos emitidos `DownloadType::issued()` o recibidos `DownloadType::received()`.
Si no se especifica utiliza el valor de emitidos.

#### Tipo de solicitud (`RequestType`)

Establece si la solicitud es de Metadatos `RequestType::metadata()` o archivos XML `RequestType::xml()`.
Si no se especifica utiliza el valor de Metadatos.

#### Tipo de comprobante (`DocumentType`)

Filtra la solicitud por tipo de comprobante. Si no se especifica utiliza no utiliza el filtro.

- Cualquiera: `DocumentType::undefined()` (predeterminado).
- Ingreso: `DocumentType::ingreso()`.
- Egreso: `DocumentType::egreso()`.
- Traslado: `DocumentType::traslado()`.
- Nómina: `DocumentType::nomina()`.
- Pago: `DocumentType::pago()`.

#### Tipo de complemento (`ComplementoCfdi` o `ComplementoRetenciones`)

Filtra la solicitud por la existencia de un tipo de complemento dentro del comprobante.
Si no se especifica utiliza `ComplementoUndefined::undefined()` que excluye el filtro.

Hay dos tipos de objetos que satisfacen este parámetro, depende del tipo de comprobante que se está solicitando.
Si se trata de comprobantes de CFDI Regulares entonces se usa la clase `ComplementoCfdi`.
Si se trata de CFDI de retenciones e información de pagos entonces se usa la clase `ComplementoRetenciones`.

Estos objetos se pueden crear nombrados (`ComplementoCfdi::leyendasFiscales10()`),
por constructor (`new ComplementoCfdi('leyendasfisc')`), o bien,
por el método estático `create` (`ComplementoCfdi::create('leyendasfisc')`).

Además, se puede acceder al nombre del complemento utilizando el método `label()`, por ejemplo,
`echo ComplementoCfdi::leyendasFiscales10()->label(); // Leyendas Fiscales 1.0`.

A su vez, este objeto ofrece un método estático `getLabels(): array` para obtener un arreglo con los datos,
en donde la llave es el identificador del complemento y el valor es el nombre del complemento.

#### Estado del comprobante (`DocumentStatus`)

Filtra la solicitud por el estado de comprobante: Vigente (`DocumentStatus::active()`) y Cancelado (`DocumentStatus::cancelled()`).
Si no se especifica utiliza `DocumentStatus::undefined()` que excluye el filtro.

#### UUID (`Uuid`)

Filtra la solicitud por UUID.
Para crear el objeto del filtro hay que usar `Uuid::create('96623061-61fe-49de-b298-c7156476aa8b')`.
Si no se especifica utiliza `Uuid::empty()` que excluye el filtro.

#### Filtrado a cuenta de terceros (`RfcOnBehalf`)

Filtra la solicitud por el RFC utilizado a cuenta de terceros.
Para crear el objeto del filtro hay que usar `RfcOnBehalf::create('XXX01010199A')`.
Si no se especifica utiliza `RfcOnBehalf::empty()` que excluye el filtro.

#### Filtrado por RFC contraparte (`RfcMatch`/`RfcMatches`)

Filtra la solicitud por el RFC en contraparte, es decir, que
si la consulta es de emitidos entonces filtrará donde el RFC especificado sea el receptor,
si la consulta es de recibidos entonces filtrará donde el RFC especificado sea el emisor.

Para crear el objeto del filtro hay que usar `RfcMatch::create('XXX01010199A')`.
Si no se especifica utiliza una lista vacía `RfcMatches::create()` que excluye el filtro.

```php
$rfcMatch = RfcMatch::create('XXX01010199A');
$parameters = $parameters->withRfcMatch();
var_dump($rfcMatch === $parameters->getRfcMatch()); // bool(true)
```

El servicio del SAT permite especificar hasta 5 RFC Receptores, al menos así lo establecen en su documentación.
Sin embargo, al tratarse de receptores, solo se puede utilizar en una consulta de documentos emitidos.
En el caso de una consulta de documentos recibidos, solo se utilizará el primero de la lista.

Por lo regular utilizará solamente los métodos `QueryParameter::getRfcMatch(): RfcMatch`
y `QueryParameter::withRfcMatch(RfcMatch $rfcMatch)`.

Sin embargo, si fuera necesario especificar el listado de RFC, se puede realizar de la siguiente manera:

```php
$parameters = $parameters->withRfcMatches(
    RfcMatches::create(
        RfcMatch::create('AAA010101000'),
        RfcMatch::create('AAA010101001'),
        RfcMatch::create('AAA010101002')
    )
);
```

O bien, utilizar una lista de RFC como cadenas de texto:

```php
$parameters = $parameters->withRfcMatches(
    RfcMatches::createFromValues('AAA010101000', 'AAA010101001', 'AAA010101002')
);
```

##### Acerca de `RfcMatches`

Este objeto mantiene una lista de `RfcMatches`, pero con características especiales:

- Los objetos `RfcMatch` *vacíos* o *repetidos* son ignorados, solo se mantienen valores no vacíos únicos.
- El método `RfcMatch::getFirst()` devuelve siempre el primer elemento, si no existe entonces devuelve uno vacío.
- La clase `RfcMatch` es *iterable*, se puede hacer `foreach()` sobre los elementos.
- La clase `RfcMatch` es *contable*, se puede hacer `count()` sobre los elementos.

#### Tipo de servicio (`ServiceType`)

Esta es una propiedad que bien se podría considerar interna y no necesitas especificarla en la consulta.
Por defecto está no definida y con el valor `null`. Se puede conocer si la propiedad ha sido definida
con la propiedad `hasServiceType(): bool` y cambiar con `withServiceType(ServiceType): self`.

No se recomienda definir esta propiedad y dejar que el servicio establezca el valor correcto
según a donde esté apuntando el servicio.

Cuando se ejecuta una consulta, el servicio (`Service`) automáticamente define esta propiedad si es que
no está definida estableciéndole el mismo valor que está definido en el objeto `ServiceEndpoints`.
Si esta propiedad ya estaba definida, y su valor no es el mismo que el definido en el objeto `ServiceEndpoints`
entonces se genera una `LogicException`.

#### Ejemplo de especificación de parámetros

En el siguiente ejemplo, se crea una consulta sin parámetros y posteriormente se van modificando.
Los métodos no cambian la propiedad del objeto (no son `set*`), lo que hacen es crear una nueva
instancia de la consulta con los nuevos valores (son `with*`).

Puede que los cambios del ejemplo no sean lógicos, es solo para ilustrar cómo se establecen los valores:

- Un periodo específico de `2019-01-13 00:00:00` a `2019-01-13 23:59:59` (inclusive).
- Sobre los documentos recibidos.
- Solicitando los archivos XML.
- Filtrando por documentos de tipo ingreso.
- Filtrando por los que tengan el complemento de leyendas fiscales.
- Filtrando por únicamente documentos vigentes (excluye cancelados).
- Filtrando por el RFC a cuenta de terceros `XXX01010199A`.
- Filtrando por el RFC contraparte `MAG041126GT8`. Como se solicitan recibidos, entonces son los emidos por ese RFC.
- Filtrando por el UUID `96623061-61fe-49de-b298-c7156476aa8b`.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoCfdi;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;

$query = QueryParameters::create()
    ->withPeriod(DateTimePeriod::createFromValues('2019-01-13 00:00:00', '2019-01-13 23:59:59'))
    ->withDownloadType(DownloadType::received())
    ->withRequestType(RequestType::xml())
    ->withDocumentType(DocumentType::ingreso())
    ->withComplement(ComplementoCfdi::leyendasFiscales10())
    ->withDocumentStatus(DocumentStatus::active())
    ->withRfcOnBehalf(RfcOnBehalf::create('XXX01010199A'))
    ->withRfcMatch(RfcMatch::create('MAG041126GT8'))
    ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
;
```

#### Ejemplo de consulta por UUID

En este caso se especifica solamente el UUID a consultar, en el ejemplo es `96623061-61fe-49de-b298-c7156476aa8b`.

Nota: **Todos los demás argumentos de la consulta son ignorados**.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;

$query = QueryParameters::create()
    ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
;
```

#### Prevalidación de una consulta

Hay algunos casos que seguramente resultarán en un error al momento de presentar la consulta al SAT.
Para prevenir esta situación *opcionalmente* se puede validar la consulta antes de presentarla.
Estos errores son devueltos en un listado de cadenas de caracteres.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;

$query = QueryParameters::create()
    ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
    ->withDocumentType(DocumentType::nomina())
    ->withDocumentStatus(DocumentStatus::active())
;

// obtener el listado de errores
$errors = $query->validate();
if ([] !== $errors) { // si hay errores
    echo 'Errores de consulta: ', PHP_EOL;
    foreach ($errors as $error) {
        echo '  - ', $error, PHP_EOL;
    }
}
```

### Verificar una consulta

La verificación depende de que la consulta haya sido aceptada.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Service;

/**
 * @var Service $service Objeto de ayuda de consumo de servicio, previamente fabricado
 * @var string $requestId Identificador generado al presentar la consulta, previamente fabricado
 */

// consultar el servicio de verificación
$verify = $service->verify($requestId);

// revisar que el proceso de verificación fue correcto
if (! $verify->getStatus()->isAccepted()) {
    echo "Fallo al verificar la consulta {$requestId}: {$verify->getStatus()->getMessage()}";
    return;
}

// revisar que la consulta no haya sido rechazada
if (! $verify->getCodeRequest()->isAccepted()) {
    echo "La solicitud {$requestId} fue rechazada: {$verify->getCodeRequest()->getMessage()}", PHP_EOL;
    return;
}

// revisar el progreso de la generación de los paquetes
$statusRequest = $verify->getStatusRequest();
if ($statusRequest->isExpired() || $statusRequest->isFailure() || $statusRequest->isRejected()) {
    echo "La solicitud {$requestId} no se puede completar", PHP_EOL;
    return;
}
if ($statusRequest->isInProgress() || $statusRequest->isAccepted()) {
    echo "La solicitud {$requestId} se está procesando", PHP_EOL;
    return;
}
if ($statusRequest->isFinished()) {
    echo "La solicitud {$requestId} está lista", PHP_EOL;
}

echo "Se encontraron {$verify->countPackages()} paquetes", PHP_EOL;
foreach ($verify->getPackagesIds() as $packageId) {
    echo " > {$packageId}", PHP_EOL;
}
```

### Descargar los paquetes de la consulta

La descarga de los paquetes depende de que la consulta haya sido correctamente verificada.

Una consulta genera un identificador de la solicitud,
la verificación retorna **uno o varios** identificadores de paquetes.
Necesitas descargar todos y cada uno de los paquetes para tener la información completa de la consulta.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Service;

/**
 * @var Service $service Objeto de ayuda de consumo de servicio, previamente fabricado
 * @var string[] $packagesIds Listado de identificadores de paquetes generado en la verificación, previamente fabricado
 */

// consultar el servicio de verificación
foreach($packagesIds as $packageId) {
    $download = $service->download($packageId);
    if (! $download->getStatus()->isAccepted()) {
        echo "El paquete {$packageId} no se ha podido descargar: {$download->getStatus()->getMessage()}", PHP_EOL;
        continue;
    }
    $zipfile = "$packageId.zip";
    file_put_contents($zipfile, $download->getPackageContent());
    echo "El paquete {$packageId} se ha almacenado", PHP_EOL;
}
```

### Lectura de paquetes

Los paquetes de Metadata y CFDI se pueden leer con las clases `MetadataPackageReader` y `CfdiPackageReader` respectivamente.
Para fabricar los objetos, se pueden usar sus métodos `createFromFile` para crearlo a partir de un archivo existente
o `createFromContents` para crearlo a partir del contenido del archivo en memoria.

Cada paquete puede contener uno o más archivos internos. Cada paquete se lee individualmente.

#### Lectura de paquetes de tipo Metadata

```php
<?php
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions\OpenZipFileException;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataPackageReader;

/**
 * @var string $zipfile Contiene la ruta al archivo de paquete de Metadata
 */

// abrir el archivo de Metadata
try {
    $metadataReader = MetadataPackageReader::createFromFile($zipfile);
} catch (OpenZipFileException $exception) {
    echo $exception->getMessage(), PHP_EOL;
    return;
}

// leer todos los registros de metadata dentro de todos los archivos del archivo ZIP
foreach ($metadataReader->metadata() as $uuid => $metadata) {
    echo $metadata->uuid, ': ', $metadata->fechaEmision, PHP_EOL;
}
```

#### Lectura de paquetes de tipo CFDI

```php
<?php
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions\OpenZipFileException;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\CfdiPackageReader;

/**
 * @var string $zipfile Contiene la ruta al archivo de paquete de archivos ZIP
 */
try {
    $cfdiReader = CfdiPackageReader::createFromFile($zipfile);
} catch (OpenZipFileException $exception) {
    echo $exception->getMessage(), PHP_EOL;
    return;
}

// leer todos los CFDI dentro del archivo ZIP con el UUID como llave
foreach ($cfdiReader->cfdis() as $uuid => $content) {
    file_put_contents("cfdis/$uuid.xml", $content);
}
```

## Información técnica

### Acerca de la interfaz `RequestBuilderInterface`

El Servicio Web del SAT de Descarga Masiva requiere comunicación SOAP especial, con autenticación
y mensajes firmados. Generar estos mensajes requiere de gran detalle porque si el mensaje contiene
errores será inmediatamente rechazado.

La firma de estos mensajes es con la FIEL, así que se puede utilizar la clase `FielRequestBuilder` que
junto con la clase `Fiel` y la librería [phpcfdi/credentials](https://github.com/phpcfdi/credentials)
hacen la combinación adecuada para firmar los mensajes.

Sin embargo, existen escenarios distribuidos donde lo mejor sería contar con la creación de estos mensajes
firmados en un lugar externo, de esta forma la FIEL (la llave privada y contraseña) no se necesita exponer
al exterior. Para estos (u otros) escenarios, es posible crear una implementación de `RequestBuilderInterface`
que contenga la lógica adecuada y entregue los mensajes firmados necesarios para la comunicación. 

### Acerca de la interfaz `WebClientInterface`

Para hacer esta librería compatible con diferentes formas de comunicación se utiliza una interfaz de cliente HTTP.
Tú *puedes* crear tu implementación para poderla utilizar.
 
Si lo prefieres -como en el ejemplo de uso- podrías instalar Guzzle `composer require guzzlehttp/guzzle` y usar la clase
[`GuzzleWebClient`](https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/main/src/WebClient/GuzzleWebClient.php).

### Recomendación de fábrica del servicio

Te recomendamos configurar el framework de tu aplicación (Dependency Injection Container) o crear una clase que
fabrique los objetos `Service`, `RequestBuilder` y `WebClient`, usando tus propias configuraciones de `Fiel`
en caso de que tengas disponible el certificado, llave privada y contraseña.

### Manejo de excepciones

Al trabajar con el lector de paquetes (PackageReader) o con la comunicación HTTP con el servidor
web set SAT (WebClient), la librería puede lanzar excepciones que puedes atrapar y analizar, ya
sea en el momento de implementación o para personalizar los mensajes de error.

- [Documentación específica de excepciones de `phpcfd/sat-ws-descarga-masiva`](docs/Excepciones.md).

## Acerca del Servicio Web de Descarga Masiva de CFDI y Retenciones

El servicio se compone de 4 partes:

1. Autenticación: Esto se hace con tu FIEL y la librería oculta la lógica de obtener y usar el Token.
2. Solicitud: Presentar una solicitud incluyendo la fecha de inicio, fecha de fin, tipo de solicitud
   emitidas/recibidas y tipo de información solicitada (cfdi o metadata).
3. Verificación: pregunta al SAT si ya tiene disponible la solicitud.
4. Descargar los paquetes emitidos por la solicitud.

Una forma burda de entenderlo es: imagina que el servicio del SAT se compone de tres ventanillas con tres
personas diferentes atendiendo cada una de estas ventanillas.

* En la primera vas y presentas una solicitud de información. Te firman de recibido, pero eso no significa que tu
información esté lista, solo que han recibido tu solicitud.

* En la segunda ventanilla preguntas por tu número de solicitud y te responden que aún no tienen lista la solicitud,
regresas después y te dicen que aún no está lista, hasta que finalmente te dicen que ya está completada, y te piden
pasar a otra ventanilla por las cajas con tu información.

* En la última ventanilla llegas y pides cada una de las cajas, una a la vez, te las entregan y te las llevas.
Si perdiste tu caja y regresaste varios días después y pides la caja, puede que ya no esté disponible.
Si le pides muchas veces una caja puede que te digan que dejes de estar pidiendo la misma caja y haces enojar
al funcionario del SAT y no te la da más.

* Todo esto sucede con un máximo de seguridad, cada vez que hablas con un funcionario te pide que le enseñes tu permiso
y si no lo tienes o ya está vencido (duran apenas unos minutos) te mandan con la persona de seguridad para que le
demuestres que eres tú y te extienda un nuevo permiso.

### Información oficial

A la fecha de liberación del *Servicio web de descarga masiva de terceros para CFDI y CFDI de Retenciones* (2025-05-30)
el SAT no ha publicado información oficial en su página. Esta información se ha recopilado por miembros de la comunidad
de diferentes fuentes.

- Información oficial del SAT (apartado de *Documentos relacionados*):
  <https://www.sat.gob.mx/portal/public/tramites/factura-electronica>
- Solicitud de descargas para CFDI y retenciones:
  <https://ampocdevbuk01a.s3.us-east-1.amazonaws.com/1_WS_Solicitud_Descarga_Masiva_V1_5_VF_89183c42e9.pdf>
- Verificación de descargas de solicitudes exitosas:
  <https://ampocdevbuk01a.s3.us-east-1.amazonaws.com/2_WS_Verificacion_de_Descarga_Masiva_V1_5_VF_5e53cc2bb5.pdf>
- Descarga de solicitudes exitosas:
  <https://ampocdevbuk01a.s3.us-east-1.amazonaws.com/3_WS_Descarga_de_Solicitudes_Exitosas_V1_5_VF_74f66e46ec.pdf>

Notas importantes del web service:

- Podrás recuperar hasta 200 mil registros por petición y hasta 1,000,000 en metadata.
- No existe limitante en cuanto al número de solicitudes siempre que no se descargue en más de dos ocasiones un XML.
- No se pueden consultar comprobantes a un periodo máximo de cinco años hacia atrás.
- No se pueden consultar comprobantes a un periodo máximo de seis ejercicios, incluyendo el actual.

### Notas de uso

- No se aplica la restricción de la documentación oficial: *que no se descargue en más de dos ocasiones un XML*.

Se ha encontrado que la regla relacionada con las descargas de tipo CFDI no se aplica en la forma como está redactada.
Sin embargo, se ha encontrado que la regla que sí aplica es: *no solicitar en más de 2 ocasiones el mismo periodo*.
Cuando esto ocurre, el proceso de solicitud devuelve el mensaje *"5002: Se han agotado las solicitudes de por vida"*.

Recuerda que, si se cambia la fecha inicial o final en al menos un segundo ya se trata de otro periodo,
por lo que si te encuentras en este problema podrías solucionarlo de esta forma.

En consultas del tipo Metadata no se aplica la limitante mencionada anteriormente, por ello es recomendable
hacer las pruebas de implementación con este tipo de consulta.

- Tiempo de respuesta entre la presentación de la consulta y su verificación exitosa.

No se ha podido encontrar una constante para suponer el tiempo que puede tardar una consulta en regresar un estado
de verificación exitosa y que los paquetes estén listos para descargarse.

En nuestra experiencia, entre más grande el periodo y más consultas se presenten más lenta es la respuesta,
y puede ser desde minutos a horas. Por lo general es raro que excedan 24 horas.
Sin embargo, varios usuarios han experimentado casos raros (posiblemente por problemas en el SAT) en donde las
solicitudes han llegado a tardar hasta 72 horas para ser completadas.

#### Cambios en el webservice versión 1.5 (2025-05-30)

- Ya no es posible consultar un instante.

El SAT ha puesto la restricción de que la fecha de inicio de la consulta debe ser menor (y no igual)
a la fecha final de la consulta. Por lo que es imposible consultar un solo instante.
Es obligatorio ahora consultar como mínimo un intervalo de dos segundos.

- Cambia el límite inferior en el periodo de consulta.

El SAT ha cambiado su validación del límite inferior en la fecha consultada,
ahora el límite inferior es la fecha actual seis años atrás sin tiempo.

Por ejemplo, si la fecha actual fuera `2025-01-13 14:15:16`, entonces el límite inferior sería `2019-01-13 00:00:00`.
Si se solicita `2019-01-12 23:59:59` como fecha de inicio del periodo entonces la consulta falla.

- Falla al solicitar Recibidos XML que incluyan cancelados.

El SAT en su nueva versión del webservice ha puesto una nueva validación en la que,
al presentar una solicitud de documentos recibidos (`DownloadType::received()`)
y el tipo de paquete solicitado sea XML (`DownloadType::xml()`),
fallará a menos que se especifique que se solicitan los documentos con estado activo (`DocumentStatus::active()`).

Para corregir este problema se recomienda que implementes algo como el siguiente ejemplo:

```php
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;

/**
 * @var QueryParameters $query Consulta elaborada previamente, antes de presentarla.  
 */

if ($query->getDownloadType()->isReceived() && $query->getRequestType()->isXml()) {
    $query = $query->withDocumentStatus(DocumentStatus::active());
}
```

## Compatibilidad

Esta librería se mantendrá compatible con al menos la versión con
[soporte activo de PHP](https://www.php.net/supported-versions.php) más reciente.

También utilizamos [Versionado Semántico 2.0.0](https://semver.org/lang/es/)
por lo que puedes usar esta librería sin temor a romper tu aplicación.

### Actualizaciones

- [Guía de actualización de versión 0.3 a 0.4](docs/UPGRADE_0.3_0.4.md).
- [Guía de actualización de versión 0.4 a 0.5](docs/UPGRADE_0.4_0.5.md).
- [Guía de actualización de versión 0.5 a 1.0](docs/UPGRADE_0.5_1.0.md).

## Contribuciones

Las contribuciones son bienvenidas. Por favor lee [CONTRIBUTING][] para más detalles
y recuerda revisar el archivo de tareas pendientes [TODO][] y el archivo [CHANGELOG][].

## Copyright and License

The `phpcfdi/sat-ws-descarga-masiva` library is copyright © [PhpCfdi](https://www.phpcfdi.com)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/main/CONTRIBUTING.md
[changelog]: https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/main/docs/CHANGELOG.md
[todo]: https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/main/docs/TODO.md

[source]: https://github.com/phpcfdi/sat-ws-descarga-masiva
[php-version]: https://packagist.org/packages/phpcfdi/sat-ws-descarga-masiva
[discord]: https://discord.gg/aFGYXvX
[release]: https://github.com/phpcfdi/sat-ws-descarga-masiva/releases
[license]: https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/main/LICENSE
[build]: https://github.com/phpcfdi/sat-ws-descarga-masiva/actions/workflows/build.yml?query=branch:main
[reliability]:https://sonarcloud.io/component_measures?id=phpcfdi_sat-ws-descarga-masiva&metric=Reliability
[maintainability]: https://sonarcloud.io/component_measures?id=phpcfdi_sat-ws-descarga-masiva&metric=Maintainability
[coverage]: https://sonarcloud.io/component_measures?id=phpcfdi_sat-ws-descarga-masiva&metric=Coverage
[violations]: https://sonarcloud.io/project/issues?id=phpcfdi_sat-ws-descarga-masiva&resolved=false
[downloads]: https://packagist.org/packages/phpcfdi/sat-ws-descarga-masiva

[badge-source]: https://img.shields.io/badge/source-phpcfdi/sat--ws--descarga--masiva-blue?logo=github
[badge-discord]: https://img.shields.io/discord/459860554090283019?logo=discord
[badge-php-version]: https://img.shields.io/packagist/php-v/phpcfdi/sat-ws-descarga-masiva?logo=php
[badge-release]: https://img.shields.io/github/release/phpcfdi/sat-ws-descarga-masiva?logo=git
[badge-license]: https://img.shields.io/github/license/phpcfdi/sat-ws-descarga-masiva?logo=open-source-initiative
[badge-build]: https://img.shields.io/github/actions/workflow/status/phpcfdi/sat-ws-descarga-masiva/build.yml?branch=main&logo=github-actions
[badge-reliability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_sat-ws-descarga-masiva&metric=reliability_rating
[badge-maintainability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_sat-ws-descarga-masiva&metric=sqale_rating
[badge-coverage]: https://img.shields.io/sonar/coverage/phpcfdi_sat-ws-descarga-masiva/main?logo=sonarcloud&server=https%3A%2F%2Fsonarcloud.io
[badge-violations]: https://img.shields.io/sonar/violations/phpcfdi_sat-ws-descarga-masiva/main?format=long&logo=sonarcloud&server=https%3A%2F%2Fsonarcloud.io
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/sat-ws-descarga-masiva?logo=packagist
