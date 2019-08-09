# Project structure

## Source root level

At root level there are only:

- `Service`: Main class of the whole library
- `Services\<Service>`: Where the 4 main services specific classes are located
- `Shared\`: DTO, internal and helper objects
- `WebClient\`: http web client specification and classes

### `Services\<Service>`

There are 4 main services, objects related to this are located into `Services\<Service>`

- Service\Authenticate
- Service\Query
- Service\Verify
- Service\Download 

Each service can have different objects by whet they do:

- Translators: Create one object from other, create SOAP XML requests, read SOAP XML responses
- Result: DTO containing the result of an operation
- Parameters: DTO containing the parameters to perform an operation

### Shared objects

The objects located here are common for two or more services

### WebClient

Contains the web client abstraction/simplification, http requests, http responses and Web Client Interface

## Tests organization

- `bootstrap.php` PHP Unit boostrap file
- `TestCase.php` Main test case where all test cases depends on
- `_files/` Where common files lives, use helper methods on `TestCase` to retrieve path or contents
- `Unit\` Unit tests, they don't touch external world
- `Integration\` Integration tests, they touch the SAT web service
- `Scripts\` command line interface utility for testing

