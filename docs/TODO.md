# phpcfdi/sat-ws-descarga-masiva To Do List

- Traducir CHANGELOG.md a español

- Mover el script de consumo con credenciales válidas a su propio proyecto dependiente de este.

- Llevar el code coverage a 100% con test unitarios
    2019-12-06: Cersion 0.2.4 92%
    2019-09-23: Version 0.2.3 93% 
    2019-08-23: Current 93%
    2019-08-09: Current 86%
    2019-08-08: Current 84%

## Tareas resueltas

- Crear documentación de la librería y README, CI, etc.
    2019-09-09: Hecho!

- Crear un objeto para los valores del servicio de verificación de CodigoEstadoSolicitud
    2019-09-09: Hecho!
 
- Crear un objeto para los valores del servicio de verificación de EstadoSolicitud 
    2019-09-09: Hecho!

- Crear lector de archivos ZIP de Metadata
    2019-09-09: Hecho!

- Crear lector de archivos ZIP de CFDI
    2019-09-09: Hecho!

- Mejorar los objetos Result para que puedan compartir la misma lógica donde la comparten
    2019-08-08: Se creó el StatusCode para exponer el código y mensaje en los servicios comúnes
    
- Mejorar la búsqueda de elementos con DOMXPath
  2019-08-08: No se cambia, aún cuando la búsqueda es costosa, si se cambia,
  nos meteremos en problemas de espacios de nombres y soporte de mayúsculas y minúsculas
  con los nombres de los atributos.
  Lo que se podría hacer es usar una librería de lectura rápida como QuickReader de CfdiUtils,
  pero esta librería no soporta nodos con contenido, solo atributos. La ventaja es que no será
  necesario tener una representación del DOM cargada en memoria.
  Resolución: Dejar como está, hacer cambio cuando se cree phpcfdi/xml-quickreader

- Averiguar cómo se puede abrir un archivo ZIP sin usar el sistema de archivos
  2018-08-08: No es posible, el wrapper de la libería requiere que exista el archivo.

- Create a test suite for a valid and current FIEL (instead of testing AAA010101AAA)
  2019-08-07: Se creó el script test/Scripts/sat-ws-descarga-masiva.php que consume los servicios.
  Lo más seguro es que esto sea extraído a su propio proyecto con un framework de CLI bien hecho.

- Change `Service::authenticate()` behavior, store the last valid token, if token still valid return that value instead
  of creating a new one.
  2019-08-07: Mientras el Token sea válido se reutiliza

- Verificar que los atributos en QueryTranslator SolicitaDescarga/solicitud son importantes,
  En caso de ser posible, poner los atributos en orden alfabético.
  2019-08-08: Se agregó la verificación de XmlSecLib para garantizar que está firmado correctamente

- Mover la dependencia del certificado a `phpcfdi/sat-credentials` una vez que exista el proyecto
  2019-08-13: Se agregó la dependencia a `phpcfdi/credentials` y el uso y explotación de certificados
  y llaves privadas se recarga en esta otra librería.
