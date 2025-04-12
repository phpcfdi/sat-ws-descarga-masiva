# Actualizar de `0.5.x` a `1.0.0`

La versión `1.0.0` es una actualización al código para poderlo mantener más fácilmente.
Se usa la numeración de versión mayor 1.0.
Ahora la versión mínima de PHP es 8.1.

Al tener implementada la versión `0.5`, el cambio a la versión 1.0 debe ser transparente.

## Tipos

Se mejoraron los tipos en todo el proyecto, ahora están en el código y no en anotaciones.

Asimismo, se pusieron la mayoría de las propiedades privadas como solo lectura.

## Cambios mínimos en el objeto `Service`

La propiedad `$currentToken` de la clase `Service` ya no es pública.
Se puede acceder al token registrado a través del método `getToken()`.
Asimismo, el parámetro de creación en la clase cambia de `$currentToken` a `$token`.

Se agregó el método `getEndpoints()` para obtener la propiedad `$endpoints`.

## Propiedad `QueryParameters#serviceType`

Se cambia el comportamiento de la propiedad `QueryParameters#serviceType`.
Anteriormente, si la consulta estaba en un tipo de servicio diferente al del servicio entonces
al momento de ejecutar la consulta se lanzaba una excepción lógica.
Ahora, simplemente se obvia este parámetro y se usa el del servicio.
Este cambio no debe ser significativo en el uso de la librería.

## Inmutabilidad de `GuzzleWebClient`

En la clase GuzzleWebClient ya no se permite modificar las funciones de eventos al generar un
*request* o un *response*.

## Código eliminado

Se eliminó el método previamente deprecado `DownloadResult#getPackageLenght()`.
Se eliminó el método (de uso interno) `QueryParameters#hasServiceType()`. 