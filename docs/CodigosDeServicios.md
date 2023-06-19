# Códigos de servicios

Los 3 servicios de consumo `Consulta/Query`, `Verificación/Verify` y `Descarga/Download` entregan información predefinida.
`CodEstatus` y `Mensaje` se repite en los 3 servicios.

* Consulta/Query
    - CodEstatus: Código de estado de la llamada.
    - Mensaje: Pequeña descripcion del código de estado.
    
* Verificación/Verify
    - CodEstatus: Código de estado de la llamada.
    - Mensaje: Pequeña descripcion del código de estado.
    - CodigoEstadoSolicitud: `CodeRequest` Estado de la solicitud de la descarga.
    - EstadoSolicitud: `StatusRequest` número correspondiente al estado de la solicitud de descarga.
    
* Descarga/Download
    - CodEstatus: Código de estado de la llamada.
    - Mensaje: Pequeña descripcion del código de estado.

## Acerca de `CodEstatus`

Códigos de estado de la petición realizada. No se cuenta con un catálogo específico porque el mensaje está devuelto en `Mensaje`.

Ambos valores se pueden obtener con el objeto `StatusCode` que contiene las propiedades
`getCode(): int` y `getMessage(): string`.

Las respuestas de los servivios cuentan con la propiedad `getStatusCode(): StatusCode`, por ejemplo `VerifyResult::getStatusCode()`.

| Servicio          | Code | Descripción                                                                             |
|-------------------|------|-----------------------------------------------------------------------------------------|
| All               | 300  | Usuario no válido                                                                       |
| All               | 301  | XML mal formado                                                                         |
| All               | 302  | Sello mal formado                                                                       |
| All               | 303  | Sello no corresponde con RfcSolicitante                                                 |
| All               | 304  | Certificado revocado o caduco                                                           |
| All               | 305  | Certificado inválido                                                                    |
| All               | 5000 | Solicitud recibida con éxito                                                            |
| Query             | 5001 | Tercero no autorizado                                                                   |
| Query             | 5002 | Se agotó las solicitudes de por vida: Máximo para solicitudes con los mismos parámetros |
| Verify & download | 5004 | No se encontró la solicitud                                                             |
| Query             | 5005 | Solicitud duplicada: Si existe una solicitud vigente con los mismos parámetros          |
| Query             | 5006 | Error interno en el proceso                                                             |
| Verify & download | 404  | Error no controlado: Reintentar más tarde la petición                                   |

Notas:

- A pesar de que el estado `CodEstatus: 404` solo debería presentarse en el servicio de solicitud, 
  pues ya no está documentado, se ha encontrado en la práctica que sí lo devuelve.

## Acerca de `CodigoEstadoSolicitud`

Este campo se parece mucho a `StatusCode` sin embargo tiene algunas diferencias: solo aparece en el servicio de
verificación y no contiene todos los valores posibles, incluso agrega el código `5003`.

La documentación del servicio dice: *Contiene el código de estado de la solicitud de descarga, los cuales pueden ser
5000, 5002, 5003, 5004 o 5005 para más información revisar la tabla “Códigos Solicitud Descarga Masiva”.*

Está implementado en el objeto `CodeRequest` disponible desde `VerifyResult::getCodeRequest()`.

El valor del código se puede obtener con `CodeRequest::getValue(): int`.
Aunque la descripción no es devuelta como respuesta del servicio, se ha documentado en la clase
y se puede obtener con el método `CodeRequest::getMessage(): string`.

Este objeto también permite la comprobación por *nombre clave*, por lo que puedes usar por ejemplo
`CodeRequest::isEmptyResult()` para conocer si se encuentra en el estado `5004: No se encontró la solicitud`.  

| Code | Name               | Descripción                                                                             |
|------|--------------------|-----------------------------------------------------------------------------------------|
| 5000 | Accepted           | Solicitud recibida con éxito                                                            |
| 5002 | Exhausted          | Se agotó las solicitudes de por vida: Máximo para solicitudes con los mismos parámetros |
| 5003 | MaximumLimitReaded | Tope máximo: Indica que se está superando el tope máximo de CFDI o Metadata             |
| 5004 | EmptyResult        | No se encontró la solicitud                                                             |
| 5005 | Duplicated         | Solicitud duplicada: Si existe una solicitud vigente con los mismos parámetros          |

## Acerca de `EstadoSolicitud`

Este código solo está presente en el servicio de verificación.

Está implementado en el objeto `StatusRequest` disponible desde `VerifyResult::getStatusRequest()`.

El valor del código se puede obtener con `StatusRequest::getValue(): int`.
Aunque la descripción no es devuelta como respuesta del servicio, se ha documentado en la clase
y se puede obtener con el método `StatusRequest::getMessage(): string`.

Este objeto también permite la comprobación por *nombre clave*, por lo que puedes usar por ejemplo
`StatusRequest::isExpired()` para conocer si se encuentra en el estado `6: Vencida`.  

| Code | Name       | Descripción |
|------|------------|-------------|
| 1    | Accepted   | Aceptada    |
| 2    | InProgress | En proceso  |
| 3    | Finished   | Terminada   |
| 4    | Failure    | Error       |
| 5    | Rejected   | Rechazada   |
| 6    | Expired    | Vencida     |

