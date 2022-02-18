# phpcfdi/sat-ws-descarga-masiva

[![Source Code][badge-source]][source]
[![Discord][badge-discord]][discord]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Scrutinizer][badge-quality]][quality]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]

> Librería para usar el servicio web del SAT de Descarga Masiva

:us: The documentation of this project is in spanish as this is the natural language for intented audience.

:mexico: La documentación del proyecto está en español porque ese es el lenguaje principal de los usuarios.
También te esperamos en [el canal #phpcfdi de discord](https://discord.gg/aFGYXvX)

Esta librería contiene un cliente (consumidor) del servicio del SAT de
**Servicio Web de Descarga Masiva de CFDI y Retenciones**.

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
// para usarlo necesitas instalar guzzlehttp/guzzle pues no es una dependencia directa
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

```php
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;

/**
 * @var GuzzleWebClient $webClient
 * @var RequestBuilderInterface $requestBuilder
 */
// Creación del servicio
$service = new Service($requestBuilder, $webClient, null, ServiceEndpoints::retenciones());
```

### Realizar una consulta

Una vez creado el servicio, se puede presentar la consulta que tiene estos cuatro parámetros:

- Periodo: Fecha y hora de inicio y fin de la consulta.
- Tipo de descarga: CFDI emitidos `DownloadType::issued()` o recibidos `DownloadType::received()`.
- Tipo de solicitud: De metadatos `RequestType::metadata()` o de archivos CFDI `RequestType::cfdi()`.
- Filtrado por RFC: Si se establece, se filtran para obtener únicamente donde la contraparte tenga el RFC indicado.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;

/**
 * El servicio ya existe
 * @var Service $service
 */

// Explicación de la consulta:
// - Del 13/ene/2019 00:00:00 al 13/ene/2019 23:59:59 (inclusive)
// - Todos los emitidos por el dueño de la FIEL
// - Solicitando la información de Metadata
// - Filtrando los CFDI emitidos para RFC MAG041126GT8
$request = QueryParameters::create(
    DateTimePeriod::createFromValues('2019-01-13 00:00:00', '2019-01-13 23:59:59'),
    DownloadType::issued(),
    RequestType::metadata(),
    'MAG041126GT8'
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

### Consulta con valores predeterminados

Valores predeterminados de una consulta:

- Consultar comprobantes emitidos `DownloadType::issued()`.
- Solicitar información de metadata `RequestType::metadata()`.
- Sin filtro de RFC.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;

// Consulta del día 2019-01-13, solo los emitidos, información de tipo metadata, sin filtro de RFC.
$request = QueryParameters::create(
    DateTimePeriod::createFromValues('2019-01-13 00:00:00', '2019-01-13 23:59:59'),
);
```

### Verificar una consulta

La verificación depende de que la consulta haya sido aceptada.

```php
<?php

use PhpCfdi\SatWsDescargaMasiva\Service;

/**
 * @var Service $service
 * @var string $requestId es el identificador generado al presentar la consulta
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
 * @var Service $service
 * @var string[] $packagesIds El listado de identificadores de paquetes generado en la (correcta) verificación
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
 * @var string $zipfile contiene la ruta al archivo de paquete de Metadata
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
 * @var string $zipfile contiene la ruta al archivo de paquete de archivos ZIP
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
[`GuzzleWebClient`](https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/master/src/WebClient/GuzzleWebClient.php).

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

1. Autenticación: Esto se hace con tu FIEL y la libería oculta la lógica de obtener y usar el Token.
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

- Liga oficial del SAT
  <https://www.sat.gob.mx/consultas/42968/consulta-y-recuperacion-de-comprobantes-(nuevo)>
- Solicitud de descargas para CFDI y retenciones:
  <https://www.sat.gob.mx/cs/Satellite?blobcol=urldata&blobkey=id&blobtable=MungoBlobs&blobwhere=1579314716402&ssbinary=true>
- Verificación de descargas de solicitudes exitosas:
  <https://www.sat.gob.mx/cs/Satellite?blobcol=urldata&blobkey=id&blobtable=MungoBlobs&blobwhere=1579314716409&ssbinary=true>
- Descarga de solicitudes exitosas:
  <https://www.sat.gob.mx/cs/Satellite?blobcol=urldata&blobkey=id&blobtable=MungoBlobs&blobwhere=1579314716395&ssbinary=true>

Notas importantes del web service:

- Podrás recuperar hasta 200 mil registros por petición y hasta 1,000,000 en metadata.
- No existe limitante en cuanto al número de solicitudes siempre que no se descargue en más de dos ocasiones un XML.

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

## Compatibilidad

Esta librería se mantendrá compatible con al menos la versión con
[soporte activo de PHP](https://www.php.net/supported-versions.php) más reciente.

También utilizamos [Versionado Semántico 2.0.0](https://semver.org/lang/es/)
por lo que puedes usar esta librería sin temor a romper tu aplicación.

### Actualizaciones

- [Guía de actualización de versión 0.3 a 0.4](docs/UPGRADE_0.3_0.4.md).

## Contribuciones

Las contribuciones con bienvenidas. Por favor lee [CONTRIBUTING][] para más detalles
y recuerda revisar el archivo de tareas pendientes [TODO][] y el archivo [CHANGELOG][].

## Copyright and License

The `phpcfdi/sat-ws-descarga-masiva` library is copyright © [PhpCfdi](https://www.phpcfdi.com)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/master/CONTRIBUTING.md
[changelog]: https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/master/docs/CHANGELOG.md
[todo]: https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/master/docs/TODO.md

[source]: https://github.com/phpcfdi/sat-ws-descarga-masiva
[discord]: https://discord.gg/aFGYXvX
[release]: https://github.com/phpcfdi/sat-ws-descarga-masiva/releases
[license]: https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/master/LICENSE
[build]: https://travis-ci.com/phpcfdi/sat-ws-descarga-masiva?branch=master
[quality]: https://scrutinizer-ci.com/g/phpcfdi/sat-ws-descarga-masiva/
[coverage]: https://scrutinizer-ci.com/g/phpcfdi/sat-ws-descarga-masiva/code-structure/master/code-coverage/src/
[downloads]: https://packagist.org/packages/phpcfdi/sat-ws-descarga-masiva

[badge-source]: https://img.shields.io/badge/source-phpcfdi/sat--ws--descarga--masiva-blue?style=flat-square
[badge-discord]: https://img.shields.io/discord/459860554090283019?logo=discord&style=flat-square
[badge-release]: https://img.shields.io/github/release/phpcfdi/sat-ws-descarga-masiva?style=flat-square
[badge-license]: https://img.shields.io/github/license/phpcfdi/sat-ws-descarga-masiva?style=flat-square
[badge-build]: https://img.shields.io/travis/com/phpcfdi/sat-ws-descarga-masiva/master?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/phpcfdi/sat-ws-descarga-masiva/master?style=flat-square
[badge-coverage]: https://img.shields.io/scrutinizer/coverage/g/phpcfdi/sat-ws-descarga-masiva/master?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/sat-ws-descarga-masiva?style=flat-square
