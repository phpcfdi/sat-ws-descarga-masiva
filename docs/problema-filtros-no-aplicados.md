# Problema: Filtros no aplicados

> Estado: **Presente**

Este problema corresponde a la versión 1.2 de la *Solicitud de descargas para CFDI y retenciones*.

## Detalles

El servicio de solicitud de descargas se ha mantenido inestable desde 2022-03-04,
cuando el SAT intentó actualizarlo a la versión 1.2.

En esta versión se agregan nuevos filtros, permitiendo hacer solicitudes más específicas como:

- Tipo de comprobante (ingreso, egreso, traslado, nómina o pago).
- Estado (vigente o cancelado).
- RFC a cuenta de terceros.
- Complemento.
- UUID.

Los siguientes filtros no están funcionando en la consulta de CFDI Regulares:

- Filtro de documentos cancelados cuando se solicita un paquete de documentos XML. 
  El filtro funciona correctamente para paquetes de Metadata.

Los siguientes filtros no están funcionando en la consulta de CFDI de retenciones e información de pagos:

- Filtro por complemento.

## Actualizaciones

### 2022-09-30

Hemos notado que la documentación del SAT en relación con la consulta por UUID está incorrecta:

- El campo no se llama `UUID`, se llama `Folio`.
- El campo `RfcSolicitante` se debe especificar.
- El campo `TipoSolicitud` se debe especificar.
- Los demás campos no deben existir.

### 2022-03-04

El SAT actualiza el servicio a la versión 1.2, no resuelve las solicitudes ingresadas.

### 2022-03-25

El SAT permite el ingreso de solicitudes con filtros, pero no aplica los filtros.

### 2022-07-25

El SAT activa algunos filtros.
