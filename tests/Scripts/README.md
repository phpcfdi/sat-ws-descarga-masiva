# About sat-ws-descarga-masiva.php

The script `sat-ws-descarga-masiva.php` is a command line interface utility to consume the webservice provided by SAT.

It works with credentials (FIEL certificate, private key and pass phrase).

## The credentials

To setup the credentials use the following parameters:

- `-c|--certificate file.cer`: Certificate file.
- `-k|--private-key file.key`: Private key file.
- `-p|--pass-phrase passphrase`: Pass phrase to open the private key.

## The output folder

The query, verify and download commands can create output files, you must set the folder to use
for dumping the request and response information.

The files dumped has the format `<output-folder>/<date>_<name>.<format>` where:

- output-folder: as provided
- date: by example 20191231-235959.987654
- name: request or response
- format: json includes headers and body, xml includes only the xml body

You can specify the output directory path using the parameter `[-o|--output example/storage/logs]` like:

```shell
php tests/Scripts/sat-ws-descarga-masiva.php query --output example/storage/logs
```

## The Actions

It offers the following actions:

- `credentials`: show information about the credentials to use and if they are valid or not.
- `query`: submit a *query*, the result should contain a *request id*.
- `verify`: verify a *request id*, the result contains codes information and zero, one or more *package id*.
- `download`: download a *package id*.

On query, verify and download you can use output folder to dump request and responses.

### Credentials

Show information about the credentials to use and if they are valid or not.

It does not contact the webservice.

### Query

```shell
php tests/Scripts/sat-ws-descarga-masiva.php query -h
Perform a request, uses the following parameters:
  -s, --since: start date time expression for period
  -u, --until: end date time expression for period
  -d, --download-type: "issued" or "received"
  -r, --request-type: "cfdi" or "metadata"
```

### Verify

```shell
php tests/Scripts/sat-ws-descarga-masiva.php verify -h
Verify a request id, the result contains codes information and zero, one or more package id
  -r, --request-id: request-id as received by request command
```

### Download

```shell
php tests/Scripts/sat-ws-descarga-masiva.php verify -h

work-in-progress...

```

## About this sub-project

Surely this script will be removed from this project and will find a place by its own.
I don't think any of the namespace in `PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\` would survive.

The probable future for this is to create a Laravel/Symfony CLI that requires the library as a dependence.
