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

Al hacer una petición con alguno o muchos de estos filtros, el paquete de información entregado por el SAT
**no parece considerarlos**, siendo los únicos filtros tomados en cuenta los mismos que anteriormente usaba:

- Periodo
- Emitidos a cualquiera o algún RFC específico.
- Recibidos de cualquiera o de algún RFC específico.

## Actualizaciones

### 2022-03-04

El SAT actualiza el servicio a la versión 1.2, no resuelve las solicitudes ingresadas.

### 2022-03-25

El SAT permite el ingreso de solicitudes con filtros, pero parece no considerarlas.
