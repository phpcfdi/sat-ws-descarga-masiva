# phpcfdi/sat-ws-descarga-masiva To Do List

- Create a test suite for a valid and current FIEL (instead of testing AAA010101AAA)

- Change `Service::authenticate()` behavior, store the last valid token, if token still valid return that value instead
  of creating a new one.

- Verificar que los atributos en QueryTranslator SolicitaDescarga/solicitud son importantes,
  En caso de ser posible, poner los atributos en orden alfabético
  Probar el resultado de la canonalización xml-exc-c14n de  
