# phpcfdi/sat-ws-descarga-masiva To Do List

- Crear documentación de la librería y README, CI, etc.

- Mejorar la búsqueda de elementos con DOMXPath

- Mejorar los objetos Result para que puedan compartir la misma lógica donde la comparten

- Averiguar cómo se puede abrir un archivo ZIP sin usar el sistema de archivos

- Crear lector de archivos ZIP de Metadata

- Crear lector de archivos ZIP de CFDI

## Tareas resueltas

- Create a test suite for a valid and current FIEL (instead of testing AAA010101AAA)
  2019-08-07: Se creó el script test/Scripts/sat-ws-descarga-masiva.php que consume los servicios.
  Lo más seguro es que esto sea extraído a su propio proyecto con un framework de CLI bien hecho.

- Change `Service::authenticate()` behavior, store the last valid token, if token still valid return that value instead
  of creating a new one.
  2019-08-07: Mientras el Token sea válido se reutiliza

- Verificar que los atributos en QueryTranslator SolicitaDescarga/solicitud son importantes,
  En caso de ser posible, poner los atributos en orden alfabético.
  2019-08-08: Se agregó la verificación de XmlSecLib para garantizar que está firmado correctamente
