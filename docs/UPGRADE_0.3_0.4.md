# Actualizar de `0.3.x` a `0.4.x`

## Lectura de paquetes

Las clases que permiten la lectura de paquetes descargados de CFDI ahora son diferentes:

- Cambio en el constructor del lector de Metadata, antes usaba `$reader = new MetadataPackageReader($filename);`
  y ahora `$reader = MetadataPackageReader::createFromFile($filename);`.
- Cambio en el constructor del lector de CFDI, antes usaba `$reader = new CfdiPackageReader($filename);`
  y ahora `$reader = CfdiPackageReader::createFromFile($filename);`.
- Se agregó el método `CfdiPackageReader::cfdis()` que devuelve un objeto trasversable con UUID como llave
  y el contenido XML valor. Esto garantiza que pueda guardar el archivo con su nombre correcto porque el SAT
  en algunas ocasiones entrega como nombre de archivo un valor distinto al UUID del CFDI.
- El valor devuelto en `count(MetadataPackageReader)` o `MetadataPackageReader::count()` antes era el número
  de archivos contenidos en el paquete de Metadata. Ahora corresponde al conteo de registros de los archivos
  contenidos en todo el paquete. Si necesita obtener el conteo de los archivos puede hacerlo de la siguiente
  forma: `iterator_count(MetadataPackageReader::fileContents())`.
- Se eliminó `MetadataPackageReader::createMetadataContent()`.
- Antes se exponía la clase `MetadataContent` en el método `MetadataPackageReader::createMetadataContent()`.
  Ahora la clase es enteramente interna.

