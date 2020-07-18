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

## UNRELEASED

- PHPStan estaba dando un falso positivo al detectar que `DOMElement::$attributes` puede contener `null`.
  Esto es solo cierto para cualquier `DOMNode` pero no para `DOMElement`.
- Se corrigieron las ligas a Travis-CI.
- Se agrega a Travis-CI la versión "php: nightly" pero se le permite fallar.

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
