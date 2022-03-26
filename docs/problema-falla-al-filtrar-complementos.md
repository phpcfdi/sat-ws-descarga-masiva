# Problema: Falla-al-filtrar-complementos

> Estado: **Presente**

Este problema corresponde a la versión 1.2 de la *Solicitud de descargas para CFDI y retenciones*.

## Detalles

En la versión 1.2 del servicio de solicitud de descargas se incluyen nuevos filtros,
uno de ellos es el de `Complemento`, en donde se especifica un texto que indica qué
complemento de CFDI o de Retenciones debe contener el resultado.

### Listado de complementos

El listado de complementos está incompleto, no incluye varios, entre ellos los complementos de "Carta Porte".

El listado de complementos no sigue una nomenclatura estándar, por ejemplo, para los complementos de "Nómina"
incluye las versiones, como `nomina11` y `nomina12` pero no para los complementos de "Consumo de Consumibles"
`consumodecombustibles` y `consumodecombustibles11`.

### Definiciones que no son complementos

El listado de complementos incluye `comprobante` y `retencionpago1`. En ambos casos estos no son complementos,
son tipos de CFDI, el primero es de tipo *Regular* y el segundo de *Retenciones e Información de Pagos*.

Cabe mencionar que `comprobante` no contiene versión, pero `retencionpago1` sí la incluye.
Y entonces faltaría `retencionpago2` que significaría el *CFD de Retenciones e Información de Pagos versión 2*.

## Actualizaciones

### 2022-03-04

El SAT actualiza el servicio a la versión 1.2.

### 2022-03-10

En este proyecto se hicieron pruebas con este filtro y se pudo constatar que las solicitudes que lo incluyen
son rechazadas con un estado `CodEstatus: 404`. A diferencia de los otros filtros, que simplemente son ignorados.
