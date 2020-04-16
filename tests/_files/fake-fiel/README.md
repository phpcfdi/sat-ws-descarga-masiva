Estos archivos fueron descargados desde
<http://omawww.sat.gob.mx/tramitesyservicios/Paginas/certificado_sello_digital.htm>

En el archivo ZIP <http://omawww.sat.gob.mx/tramitesyservicios/Paginas/documentos/RFC-PAC-SC.zip>
se tomaron los correspondientes al RFC EKU9003173C9.

Para convertir la llave privada key se usó:

```
openssl pkcs8 -inform DER -in EKU9003173C9.key -out EKU9003173C9.key.pem
```
