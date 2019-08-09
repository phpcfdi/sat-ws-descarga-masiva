
Los tres servicios tienen statusCode & Message

Códigos de error según la documentación.
Q V D  300   Usuario no válido
Q V D  301   XML mal formado
Q V D  302   Sello mal formado
Q V D  303   Sello no corresponde con RfcSolicitante
Q V D  304   Certificado revocado o caduco
Q V D  305   Certificado inválido
Q V D  5000  Solicitud recibida con éxito
Q - -  5001  Tercero no autorizado
Q - -  5002  Se agotó las solicitudes de por vida: Máximo para solicitudes con los mismos parámetros
- V D  5004  No se encontró la solicitud
Q - -  5005  Solicitud duplicada: Si existe una solicitud vigente con los mismos parámetros
Q - D  404   Error no controlado: Reintentar más tarde la petición

CodigoEstadoSolicitud:
Q V D  5000  Solicitud recibida con éxito
Q - -  5002  Se agotó las solicitudes de por vida: Máximo para solicitudes con los mismos parámetros
- - -  5003  Tope máximo: Indica que se está superando el tope máximo de CFDI o Metadata
- V D  5004  No se encontró la solicitud
Q - -  5005  Solicitud duplicada: Si existe una solicitud vigente con los mismos parámetros

EstadoSolicitud:
1 Aceptada
2 En proceso
3 Terminada
4 Error
5 Rechazada
6 Vencida

* Query
    CodStatus <- Código de estado de la llamada
    Mensaje <- Pequeña descripcion del código de estatus
    
* Verify
    CodEstatus <- Código de estado de la llamada
    Mensaje <- Pequeña descripcion del código de estado
    CodigoEstadoSolicitud <- (CodeRequest) Estado de la solicitud de la descarga (X)
    EstadoSolicitud <- (StatusRequest) número correspondiente al estado de la solicitud de descarga
    
* Download: Envelope/Header/respuesta
    CodEstatus <- Código de estado de la llamada
    Mensaje <- Pequeña descripcion del código de estado
    
