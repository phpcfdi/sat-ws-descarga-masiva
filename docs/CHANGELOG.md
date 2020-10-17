# CHANGELOG

## Acerca de los números de versiones

Respetamos el estándar [Versionado Semántico 2.0.0](https://semver.org/lang/es/).

En resumen, [SemVer](https://semver.org/) es un sistema de versiones de tres componentes `X.Y.Z`
que nombraremos así: ` Breaking . Feature . Fix `, donde:

- `Breaking`: Rompe la compatibilidad de código con versiones anteriores.
- `Feature`: Agrega una nueva característica que es compatible con lo anterior.
- `Fix`: Incluye algún cambio (generalmente correcciones) que no agregan nueva funcionalidad.

**Importante:** Las reglas de SEMVER no aplican si estás usando una rama (por ejemplo `master-dev`)
o estás usando una versión cero (por ejemplo `0.18.4`).

## Version 0.4.0 2020-10-14

- Guía de actualización de la versión 0.3.2 a la versión 0.4.0: [UPGRADE_0.3_0.4](UPGRADE_0.3_0.4.md)
- Se agregan [excepciones específicas en la librería](Excepciones.md). Además, cuando se detecta una respuesta
  que contiene un *SOAP Fault* se genera una excepción.
- Se rompe la dependencia directa de `Service` a `Fiel`, ahora depende de `RequestBuilderInterface`.
- Se crea la implementación `FielRequestBuilder` para seguir trabajando con la `Fiel`.
- Se mueve `Fiel` adentro del namespace `PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder`.
- Se modifican los servicios de autenticación, consulta, descarga y verificación para que,
  en lugar de que ellos mismos construyan las peticiones XML firmadas, ahora las deleguen a `RequestBuilderInterface`.
- Ahora se puede especificar un RFC específico en la consulta:
    - Si consultamos los emitidos podríamos filtrar por el RFC receptor.
    - Si consultamos los recibidos podríamos filtrar por el RFC emisor.
- Ahora se puede consumir el servicio para los CFDI de retenciones e información de pagos.
- Se agrega la interfaz `PackageReaderInterface` que contiene el contrato esperado por un lector de paquetes.
- Se crea la clase interna `FilteredPackageReader` que implementa `PackageReaderInterface`, también se agregan
  las clases `MetadataFileFilter` y `CfdiFileFilter` que permiten el filtrado de los archivos correctos dentro
  de los paquetes del SAT.
- Se restructura `MetadataPackageReader` para cumplir con la interfaz `PackageReaderInterface`,
  ahora se comporta como una fachada de un `FilteredPackageReader`.
- Se restructura `CfdiPackageReader` para cumplir con la interfaz `PackageReaderInterface`,
  ahora se comporta como una fachada de un `FilteredPackageReader`.
- Se agrega el método generador `CfdiPackageReader::cfdis()` que contiene en su llave el UUID del CFDI
  y en el valor el contenido del CFDI.
- Se agregan los constructores estáticos `::create()` de los objetos usados en `QueryParameters` y en la propia clase.
- Se convierten varias clases en finales: `StatusCode`, `DateTime`, `DateTimePeriod`, `DownloadType`, `Fiel`,
  `RequestType`, `Token`, `QueryParameters`, `QueryResult`, `VerifyResult`, `DownloadResult`.
- Se mueven y crean diferentes clases que solo deben ser utilizadas internamente al namespace "interno"
  `PhpCfdi\SatWsDescargaMasiva\Internal`: `Helpers`, `InteractsXmlTrait`, `ServiceConsumer`, `SoapFaultInfoExtractor`.
- Se marcan como clases internas los traductores usados dentro de los servicios.
- Se mueve lógica repetida en los servicios de autenticación, consulta, verificación y descarga hacia dentro
  del método `InteractsXmlTrait::createSignature`.
- Se implementa `JsonSerializable` en todos los DTO, en los lectores de paquetes y en las excepciones específicas.
- Se agregan muchas pruebas unitarias para comprobar el funcionamiento esperado y la cobertura de código.
- Se actualizan las dependencias:
    - `guzzlehttp/guzzle` de `6.3` a `7.0`
    - `robrichards/xmlseclibs` de `9.1` a `9.3`
- Documentación general:
    - Se agregan bloques de documentación a clases y métodos en toda la librería.
    - Se separan los bloques de ejemplos de uso en cada caso en lugar de usar solo un bloque.
    - Los códigos de servicios cambian de `Services-StatusCode.md` a `CodigosDeServicios`.

## Version 0.3.2 2020-07-28

- Se corrige el problema de cambio de formato al definir el nombre de los archivos contenidos en
  un paquete de Metadata, el formato anterior era `aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee_01.txt` y
  el nuevo es `aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee-0001.txt`. La corrección se relajó para que
  admita cualquier nombre de archivo con extensión `.txt` y que esté en el la raíz. Esta es la
  misma estrategia utilizada en el lector de paquetes de CFDI (issue #23).
- Se corrige el problema en que dentro de un archivo de Metadata donde puede contener caracteres
  extraños en los campos de *nombre emisor* y *nombre receptor*. La corrección se consideró tomando
  en cuenta que estos campos pueden contener *comillas* `"`, para ello se considera el pipe `|` como
  delimitador de cadenas. La segunda corrección identifica si el `EOL` es `<CR><LF>` y en ese caso
  elimina cualquier `<LF>` intermedio (issue #23).
- PHPStan estaba dando un falso positivo al detectar que `DOMElement::$attributes` puede contener `null`.
  Esto es solo cierto para cualquier `DOMNode` pero no para `DOMElement`.
- Se corrigieron las ligas a Travis-CI.
- Se agrega a Travis-CI la versión `php: nightly`, pero se le permite fallar.

## Version 0.3.1 2020-06-04

- Se corrige el problema de que recientemente los archivos ZIP de consultas de CFDI vienen con doble extensión,
  por ejemplo `aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml.xml`.

## Version 0.3.0 2020-05-01

- Se actualizan las dependencias `php: >=7.3` y `phpunit: ^9.1`.
- Se actualiza `php-cs-fixer` para usar `@PHP73Migration`.

## Version 0.2.6 2020-04-11

- Se actualizan los tests para que usen el RFC `EKU9003173C9`.
- Se agrega un test para probar qué ocurre al usar un `CSD` en lugar de una `FIEL`.
- Se actualiza el proyecto para satisfacer `phpstan: ^0.12`.
- En Scrutinizer-CI se eliminan las dependencias de desarrollo que no son útiles para la generación del *code coverage*.
- Se utiliza `eclipxe/micro-catalog` en lugar de la clase interna `OpenEnum`.
- Se renombra `Helpers::createUuid` a `Helpers::createXmlSecurityTokenId`.

## Version 0.2.5 2020-01-07

- Se actualiza el año de licencia a 2020.
- Se remueve método privado `FielData::readContents(): string` porque ya no está en uso.
- Se corrige la construcción con PHP 7.4 en Travis.
- Se cambia la dependencia de `phpstan-shim` a `phpstan`.


## Version 0.2.4 2019-12-06

- Se agrega la clase `PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient` que estaba en testing
  a el código distribuible, aunque no se agrega la dependencia `guzzlehttp/guzzle`.
- Se documenta el uso de `GuzzleWebClient`.
- Forzar la dependencia de `phpcfdi/credentials` a `^1.1` para leer llaves privadas en formato DER.
- Forzar la dependencia de `robrichards/xmlseclibs` a `^3.0.4` por reporte de seguridad `CVE-2019-3465`.
- Agregar ejemplo en la documentación para crear y verificar un objeto `Fiel`.
- Corrección en la documentación al crear una fiel, tenía los parámetros invertidos.
- Integración continua (Travis CI):
    - Se remueve la configuración `sudo: false`.
    - No se permite el fallo del build en PHP `7.4snapshot`.
- Integración continua (Scrutinizer):
    - Se instala la extensión `zip` con `pecl`.
    - Se elimina la información de la versión fija.
    - Se modifica el archivo de configuración para que actualice `composer`.


## Version 0.2.3 2019-09-23

- Improve usage of `ResponseInterface->getBody(): StreamInterface` using `__toString()` to retrieve contents at once.
- Include `docs/` in package, exclude development file `.phplint.yml`.
- Add PHP 7.4snapshot (allow fail) to Travis CI build matrix.
- Other minor documentation typos
 

## Version 0.2.2 2019-08-20

- Make sure when constructing a `DateTime` that it fails with an exception.
- Improve code coverage.
 

## Version 0.2.1 2019-08-20

- Make `PackageReader\MetadataContent` tolerant to non-strict CSV contents:
    - Ignore lead/inner/trail blank lines
    - Include as `#extra-01` any extra value (not listed in headers)
    - Prefill with empty strings if values are less than headers


## Version 0.2.0 2019-08-13

Breaking changes:

- `CodeRequest::isNotFound` is replaced by `CodeRequest::isEmptyResult`
- `Fiel` has been rewritten with other dependences.
  To create a Fiel object use any of this:
    - `FielData::createFiel()`
    - `Fiel::create($certificateContents, $privateKeyContents, $passPhrase)`
- XML SEC Signature now follow RFC 4514 on `X509IssuerName` node.
- Removed dependence to `eclipxe/cfdiutils`, it depends now on `phpcfdi/credentials`.

Other changes:

- Fix & improve composer/phpunit/travis/scrutinizer calls.
- Fix documentation typos.


## Version 0.1.0 2019-08-09

- Initial working release
