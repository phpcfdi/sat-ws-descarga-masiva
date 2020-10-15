# Estructura del proyecto

## Organización del código

El código está en `src/` y tiene la siguiente estructura:

- `Service` Clase principal de toda la librería.
- `Internal` Objetos privados de la librería.
- `Services\<Service>` Donde los 4 servicios están ubicados.
- `Shared` Objetos compartidos, en su mayoría DTO.
- `PackageReader` Objetos relacionados con la lectura de paquetes descargados del SAT.
- `RequestBuilder` Interfaz de generación de solicitudes XML e implementación local usando `Fiel` y `Credentials`.
- `WebClient` Cliente HTTP de comunicación con el Webservice del SAT, definición e implementación.

### `Services\<Service>`

Hay cuatro servicios fundamentales, los objetos particulares de estos servicios están almacenados en cada directorio

- `Service\Authenticate`
- `Service\Query`
- `Service\Verify`
- `Service\Download` 

Cada servicio puede contener algunos objetos con propósito especial.

- `Translators` Crean o transforman un objeto de dominio a SOAP y viceversa.
- `Result` Resultado de la operación de consumir el servicio.
- `Parameters` Parámetros para realizar la operación.

## Organización de las pruebas `tests/`

- `bootstrap.php` PHP Unit boostrap file
- `TestCase.php` Main test case where all test cases depends on
- `_files/` Where common files lives, use helper methods on `TestCase` to retrieve path or contents
- `Unit\` Unit tests, they don't touch external world
- `Integration\` Integration tests, they touch the SAT web service
