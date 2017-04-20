# Tequila MongoDB PHP Library (high-level driver)

The Tequila MongoDB PHP library provides the high-level abstraction around the new low-level [PHP MongoDB driver](https://github.com/mongodb/mongo-php-driver), e.g.
schema-management, abstractions around connections, databases, collections, indexes etc.

This library follows the [MongoDB Driver Specifications](https://github.com/mongodb/specifications), and it's 
an alternative to the official [MongoDB PHP Library](https://github.com/mongodb/mongo-php-library).

It requires PHP 5.6 or higher, PHP 7.0 or higher and requires `mongodb` PHP extension
(new low-level driver) to be installed.

For usage examples see the [Documentation](https://tequila.github.io/mongodb-php-lib/).

### Current status of the library

At the moment, library is in an alpha stage, so it should be used with caution.
The current release is `v1.0.0-alpha4`.
Public API for now is stable, library has positive functional tests for the main classes (`Client`, `Database` and `Collection`) but library needs more tests to be marked as stable.
You can support this project by writing tests, testing the library with the MongoDB server and reporting bugs.
Contributions are appreciated.

#### Todo before the first stable release:
- Tests, bugfixes, documentation.
