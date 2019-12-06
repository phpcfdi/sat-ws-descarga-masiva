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
También te esperamos en el [canal #phpcfdi de discord](https://discord.gg/aFGYXvX)

Esta librería contiene un cliente (consumidor) del servicio del SAT de
**Servicio Web de Descarga Masiva de CFDI y Retenciones**.

## Instalación

Utiliza [composer](https://getcomposer.org/), instala de la siguiente forma:

```shell
composer require phpcfdi/sat-ws-descarga-masiva
```

## Ejemplo básico de uso

```php
<?php
use PhpCfdi\SatWsDescargaMasiva\PackageReader\CfdiPackageReader;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataPackageReader;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;

// Creación de la fiel, puede leer archivos DER (como los envía el SAT) o PEM (convertidos)
$fiel = Fiel::create(
    file_get_contents('certificado.cer'),
    file_get_contents('llaveprivada.key'),
    '12345678a'
);

// verificar que la fiel sea válida (no sea CSD y sea vigente acorde a la fecha del sistema)
if (! $fiel->isValid()) {
    return;
}

// creación del web client basado en Guzzle que implementa WebClientInterface
// para usarlo necesitas instalar guzzlehttp/guzzle pues no es una dependencia directa
$webClient = new GuzzleWebClient();

// Creación del servicio
$service = new Service($fiel, $webClient);

// presentar una solicitud
$request = new QueryParameters(
    new DateTimePeriod(new DateTime('2019-01-13 00:00:00'), new DateTime('2019-01-13 23:59:59')),
    DownloadType::issued(),
    RequestType::metadata()
);
$query = $service->query($request);
$requestId = $query->getRequestId();

// consultar el servicio de verificación
$verify = $service->verify($requestId);
$packageId = $verify->getPackagesIds()[0];

// descargar
$download = $service->download($packageId);
$zipfile = "$packageId.zip";
file_put_contents($zipfile, $download->getPackageContent());

// obtener los CFDI del archivo ZIP
$cfdiReader = new CfdiPackageReader($zipfile);
foreach ($cfdiReader->fileContents() as $name => $content) {
    file_put_contents("cfdis/$name", $content);
}

// y si el contenido fuera un metadata
$metadataReader = new MetadataPackageReader($zipfile);
foreach ($metadataReader->metadata() as $metadata) {
    echo $metadata->uuid, PHP_EOL;
}
```

### Acerca de la interfaz `WebClientInterface`

Para hacer esta librería compatible con diferentes formas de comunicación se utiliza una interfaz de cliente HTTP.
Tu *puedes* crear tu implementación para poderla utilizar.
 
Si lo prefieres -como en el ejemplo de uso- podrías instalar Guzzle `composer require guzzlehttp/guzzle` y usar la clase
[`GuzzleWebClient`](https://github.com/phpcfdi/sat-ws-descarga-masiva/blob/master/src/WebClient/GuzzleWebClient.php).

### Recomendación de fábrica del servicio

Te recomendamos configurar el framework de tu aplicación (Dependency Injection Container) o crear una clase que
fabrique los objetos `Service`, `Fiel` y `WebClient` usando tus propias configuraciones de certificado, llave privada
y contraseña.

## Acerca del Servicio Web de Descarga Masiva de CFDI y Retenciones

El servicio se compone de 4 partes:

1. Autenticación: Esto se hace con tu fiel y la libería oculta la lógica de obtener y usar el Token.
2. Solicitud: Presentar una solicitud incluyendo la fecha de inicio, fecha de fin, tipo de solicitud
   emitidas/recibidas y tipo de información solicitada (cfdi o metadata).
3. Verificación: pregunta al SAT si ya tiene disponible la solicitud.
4. Descargar los paquetes emitidos por la solicitud.

La mejor manera de entenderlo es la siguiente, imagina que el servicio del SAT se compone de tres ventanillas con tres
personas diferentes atendiendo cada una de estas ventanillas.

* En la primera vas y presentas una solicitud de información. Te firman de recibido, pero eso no significa que tu
información esté lista, solo que han recibido tu solicitud.

* En la segunda ventanilla preguntas por tu número de solicitud y te responden que aun no la tienen lista, regresas
después y te dicen que aun no la tienen hasta que finalmente te dicen que ya está lista, y te piden pasar a otra
ventanilla por las cajas con tu información.

* En la última ventanilla llegas y pides cada una de las cajas, una a la vez, te las entregan y te las llevas.
Si perdiste tu caja y regresaste varios días después y pides la caja, puede que ya no esté disponible.
Si le pides muchas veces una caja puede que te digan que dejes de estar pidiendo la misma caja y haces enojar
al funcionario del SAT y no te la da más.

* Todo esto sucede con un máximo de seguridad, cada vez que hablas con un funcionario te pide que le enseñes tu permiso
y si no lo tienes o ya está vencido (duran apenas unos minutos) te mandan con el de la entrada para que le demuestres
que eres tu y te extienda un nuevo permiso.

### Información oficial

-  Liga oficial del SAT
<https://www.sat.gob.mx/consultas/42968/consulta-y-recuperacion-de-comprobantes-(nuevo)>
- Solicitud de descargas para CFDI y retenciones:
<https://www.sat.gob.mx/cs/Satellite?blobcol=urldata&blobkey=id&blobtable=MungoBlobs&blobwhere=1461173578576&ssbinary=true>
- Verificación de descargas de solicitudes exitosas:
<https://www.sat.gob.mx/cs/Satellite?blobcol=urldata&blobkey=id&blobtable=MungoBlobs&blobwhere=1461173578594&ssbinary=true>
- Descarga de solicitudes exitosas:
<https://www.sat.gob.mx/cs/Satellite?blobcol=urldata&blobkey=id&blobtable=MungoBlobs&blobwhere=1461173578561&ssbinary=true>

Notas importantes del web service:
- Podrás recuperar hasta 200 mil registros por petición y hasta un millón en metadata.
- No existe limitante en cuanto al número de solicitudes siempre que no se descargue en más de una ocasión un XML.

## Compatilibilidad

Esta librería se mantendrá compatible con al menos la versión con
[soporte activo de PHP](https://www.php.net/supported-versions.php) más reciente.

También utilizamos [Versionado Semántico 2.0.0](https://semver.org/lang/es/)
por lo que puedes usar esta librería sin temor a romper tu aplicación.

## Contribuciones

Las contribuciones con bienvenidas. Por favor lee [CONTRIBUTING][] para más detalles
y recuerda revisar el archivo de tareas pendientes [TODO][] y el [CHANGELOG][].

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
[build]: https://travis-ci.org/phpcfdi/sat-ws-descarga-masiva?branch=master
[quality]: https://scrutinizer-ci.com/g/phpcfdi/sat-ws-descarga-masiva/
[coverage]: https://scrutinizer-ci.com/g/phpcfdi/sat-ws-descarga-masiva/code-structure/master/code-coverage/src/
[downloads]: https://packagist.org/packages/phpcfdi/sat-ws-descarga-masiva

[badge-source]: https://img.shields.io/badge/source-phpcfdi/sat--ws--descarga--masiva-blue?style=flat-square
[badge-discord]: https://img.shields.io/discord/459860554090283019?logo=discord&style=flat-square
[badge-release]: https://img.shields.io/github/release/phpcfdi/sat-ws-descarga-masiva?style=flat-square
[badge-license]: https://img.shields.io/github/license/phpcfdi/sat-ws-descarga-masiva?style=flat-square
[badge-build]: https://img.shields.io/travis/phpcfdi/sat-ws-descarga-masiva/master?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/phpcfdi/sat-ws-descarga-masiva/master?style=flat-square
[badge-coverage]: https://img.shields.io/scrutinizer/coverage/g/phpcfdi/sat-ws-descarga-masiva/master?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/sat-ws-descarga-masiva?style=flat-square
