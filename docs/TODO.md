# phpcfdi/sat-ws-descarga-masiva To Do List

## Tareas pendientes para la siguiente versión mayor 

- Usar enumeradores de PHP en lugar de `eclipxe/enum`.

- Cambiar de *getters* a propiedades públicas de solo lectura.

- Cambiar `DocumentStatus` para que los valores contengan los datos del SAT.

- Cambiar `RequestType::getQueryAttributeValue` para que no reciba el parámetro `ServiceType $serviceType`.

## Ejecutar deprecaciones

- No hay deprecaciones actualmente.

## Posibles ideas

- Separar `PhpCfdi\SatWsDescargaMasiva\RequestBuilder` y `PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Fiel`
  a sus propios proyectos de librería. O implementar un "monorepo" que genere las tres librerías:
  `phpcfdi/sat-ws-descarga-masiva`, `phpcfdi/sat-ws-request-builder` y `phpcfdi/sat-ws-request-builder-fiel`.

- Separar `PhpCfdi\SatWsDescargaMasiva\PackageReader` a su propio proyecto, no es necesario combinar
  la lectura de los paquetes con el uso del servicio del SAT.

## Tareas resueltas

- Llevar el code coverage a 100% con las pruebas
  2025-04-12: Version 1.0.0 100%
  2020-10-09: Version 0.4.0 99%
  2020-05-01: Version 0.3.0 93%
  2019-12-06: Version 0.2.4 92%

- Remover el método `DownloadResult::getPackageLenght()`.
    2025-03-29: Hecho en v0.5.

- Mover la herramienta CLI de consumo con credenciales válidas a su propio proyecto dependiente de este.
    2020-10-14: Ya inició el desarrollo de `phpcfdi/sat-ws-descarga-masiva-cli`

- Generar excepciones del proyecto en lugar de excepciones genéricas.
    2020-10-09: Hecho en v0.4

- Los objetos CfdiPackageReader y MetadataPackageReader deberían de utilizar objetos independientes para
  el filtrado, las forma de estructurarlo a través de un AbstractPackageReader no es la mejor opción.
  Un ejemplo claro es la imposibilidad de crear tests unitarios correctos, porque el objeto encargado
  de leer las entradas del archivo zip comparte la responsabilidad de filtrar por nombre o por contenido, estas
  últimas dos responsabilidades deberían ser independientes.
    2020-10-09: Hecho en v0.4

- Poner la versión mínima de PHP a 7.3
    2020-05-01: ¡Hecho!

- Traducir CHANGELOG.md a español
    2019-12-06: ¡Hecho!

- Crear documentación de la librería y README, CI, etc.
    2019-09-09: ¡Hecho!

- Crear un objeto para los valores del servicio de verificación de CodigoEstadoSolicitud
    2019-09-09: ¡Hecho!
 
- Crear un objeto para los valores del servicio de verificación de EstadoSolicitud 
    2019-09-09: ¡Hecho!

- Crear lector de archivos ZIP de Metadata
    2019-09-09: ¡Hecho!

- Crear lector de archivos ZIP de CFDI
    2019-09-09: ¡Hecho!

- Mejorar los objetos Result para que puedan compartir la misma lógica donde la comparten
    2019-08-08: Se creó el StatusCode para exponer el código y mensaje en los servicios comúnes
    
- Mejorar la búsqueda de elementos con DOMXPath
  2019-08-08: No se cambia, aun cuando la búsqueda es costosa, si se cambia,
  nos meteremos en problemas de espacios de nombres y soporte de mayúsculas y minúsculas
  con los nombres de los atributos.
  Lo que se podría hacer es usar una librería de lectura rápida como QuickReader de CfdiUtils,
  pero esta librería no soporta nodos con contenido, solo atributos. La ventaja es que no será
  necesario tener una representación del DOM cargada en memoria.
  Resolución: Dejar como está, hacer cambio cuando se cree phpcfdi/xml-quickreader

- Averiguar cómo se puede abrir un archivo ZIP sin usar el sistema de archivos
  2018-08-08: No es posible, el wrapper de la librería requiere que exista el archivo.

- Create a test suite for a valid and current FIEL (instead of testing AAA010101AAA)
  2019-08-07: Se creó el script test/Scripts/sat-ws-descarga-masiva.php que consume los servicios.
  Lo más seguro es que esto sea extraído a su propio proyecto con un framework de CLI bien hecho.

- Change `Service::authenticate()` behavior, store the last valid token,
  if token still valid return it instead of creating a new one.
  2019-08-07: Mientras el Token sea válido se reutiliza

- Verificar que los atributos en QueryTranslator SolicitaDescarga/solicitud son importantes,
  En caso de ser posible, poner los atributos en orden alfabético.
  2019-08-08: Se agregó la verificación de XmlSecLib para garantizar que está firmado correctamente

- Mover la dependencia del certificado a `phpcfdi/sat-credentials` una vez que exista el proyecto
  2019-08-13: Se agregó la dependencia a `phpcfdi/credentials` y el uso y explotación de certificados
  y llaves privadas se recarga en esta otra librería.
