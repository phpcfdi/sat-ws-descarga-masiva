# CHANGELOG

## About SemVer

In summary, [SemVer](https://semver.org/) can be viewed as ` Breaking . Feature . Fix `, where:

- Breaking version = includes incompatible changes to the API
- Feature version = adds new feature(s) in a backwards-compatible manner
- Fix version = includes backwards-compatible bug fixes

**Version `0.x.x` doesn't have to apply any of the SemVer rules**


## Version 0.2.1 2019-08-20

- Make `PackageReader\MetadataContent` tolerant to non-strict CSV contents:
    - Ignore lead/inner/trail blank lines
    - Include as `#extra-01` any extra value (not listed in headers)
    - Prefill with empty strings if values are less than headers


## Version 0.2.0 2019-08-13

Breaking changes:

- `CodeRequest::isNotFound` is replaced by `CodeRequest::isEmptyResult`
- `Fiel` has been rewritten with other dependences.
  To create a Fiel object use any of this:
    - `FielData::createFiel()`
    - `Fiel::create($certificateContents, $privateKeyContents, $passPhrase)`
- XML SEC Signature now follow RFC 4514 on `X509IssuerName` node.
- Removed dependence to `eclipxe/cfdiutils`, it depends now on `phpcfdi/credentials`.

Other changes:

- Fix & improve composer/phpunit/travis/scrutinizer calls.
- Fix documentation typos.


## Version 0.1.0 2019-08-09

- Initial working release
