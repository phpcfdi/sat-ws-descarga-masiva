Estos archivos fueron descargados desde
<http://omawww.sat.gob.mx/informacion_fiscal/factura_electronica/Paginas/certificado_sello_digital.aspx>

En el archivo ZIP <http://omawww.sat.gob.mx/informacion_fiscal/factura_electronica/Documents/solcedi/Cert_Sellos.zip>
en la carpeta /aaa010101aaa_FIEL/

Para convertir la llave privada key se usó:

```
openssl pkcs8 -inform DER -in Claveprivada_FIEL_AAA010101AAA_20170515_120909.key -out aaa010101aaa_FIEL.key.pem
```

Para protegerla con password nuevamente se usó:

```
openssl rsa -in aaa010101aaa_FIEL.key.pem -des3 -out aaa010101aaa_FIEL_password.key.pem
```
