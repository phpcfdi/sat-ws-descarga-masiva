# Actualizar de `0.3.x` a `0.4.x`

Nota: De antemano lamento que las implementaciones hechas en la versión `0.3` se rompan, era necesario.
El cambio de la versión `0.4` a la versión `1.0` podría tener también algunos cambios importantes. 

## Servicio principal

Anteriormente la clase `Service` dependía directamente del objeto `Fiel`, ahora utiliza un fabricador
de solicitudes `RequestBuilderInterface`. El constructor de solicitudes `FielRequestBuilder` es una
implementación de la interfaz `RequestBuilderInterface`, que a su vez depende de `Fiel`.

```text
// antes:
$service = new Service($fiel, $webClient);

// ahora
$service = new Service(new FielRequestBuilder($fiel), $webClient);
```

Gracias al cambio anterior, ahora es posible implementar la lógica de creación de mensajes firmados de
manera remota y no solo local, por lo que se podría tener una implementación que no necesite tener
siempre disponible la eFirma como un archivo local.

## Construcción de consultas

Las consultas se construyen con el objeto `QueryParameters`, ahora se incluyen constructores estáticos
`Object::create` y se favorece que se utilicen en lugar de los constructores naturales `new Object()`.

## Uso del WebClient

La interfaz `WebClientInterface` y la clase `GuzzleWebClient` no ha cambiado, pero ahora `Service` utiliza
el conector ligeramente diferente, esto es porque ahora es capaz de detectar si se recibió una respuesta
de error tipo SOAP desde el servicio web del SAT y entonces desencadenar la excepción.

## Excepciones

La librería cuenta ahora con excepciones específicas, por lo que es más sencillo atrapar errores
y gestionar qué hacer cuando ocurren. [Vea la documentación de excepciones](Excepciones.md).

## Lectura de paquetes

Las clases que permiten la lectura de paquetes descargados ahora son diferentes.

En el caso de paquetes CFDI se agregó el método `CfdiPackageReader::cfdi()` que contiene
el UUID como clave y el contenido como valor.

En el caso de paquetes Metadata el conteo `Metadata::count()` o `count($metadata)` devuelve
el número de registros y no el conteo de archivos.

```text
// la forma recomendada para leer paquetes de CFDI es ahora en el método iterador `cfdi()`
$cfdiReader = CfdiPackageReader::createFromFile($zipfile);
foreach ($cfdiReader->cfdis() as $uuid => $content) {
    file_put_contents("cfdis/$uuid.xml", $content);
}
```

- Se agregó el método `CfdiPackageReader::cfdis()` que devuelve un objeto traversable con UUID como llave
  y el contenido XML valor. Esto garantiza que pueda guardar el archivo con su nombre correcto porque el SAT
  en algunas ocasiones entrega como nombre de archivo un valor distinto al UUID del CFDI.
- Cambio en el constructor del lector de Metadata, antes usaba `$reader = new MetadataPackageReader($filename);`
  y ahora `$reader = MetadataPackageReader::createFromFile($filename);`.
- Cambio en el constructor del lector de CFDI, antes usaba `$reader = new CfdiPackageReader($filename);`
  y ahora `$reader = CfdiPackageReader::createFromFile($filename);`.
- El valor devuelto en `count(MetadataPackageReader)` o `MetadataPackageReader::count()` antes era el número
  de archivos contenidos en el paquete de Metadata. Ahora corresponde al conteo de registros de los archivos
  contenidos en todo el paquete. Si necesita obtener el conteo de los archivos puede hacerlo de la siguiente
  forma: `iterator_count(MetadataPackageReader::fileContents())`.
- Se eliminó `MetadataPackageReader::createMetadataContent()`.
- Antes se generaban excepciones estándar `\RuntimeException`, ahora se usan excepciones específicas
  `OpenZipFileException` y `CreateTemporaryZipFileException` que extienden `\RuntimeException`.
- Antes se exponía la clase `MetadataContent` en el método `MetadataPackageReader::createMetadataContent()`.
  Ahora la clase `MetadataContent` es totalmente interna.

## Otros cambios importantes

Los parámetros y resultados de los servicios así como los objetos de valor se han marcado como inmutables
y ahora se pueden exportar a JSON usando `json_decode` porque implementan la interfaz `JsonSerializable`. 
