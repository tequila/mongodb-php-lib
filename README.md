# Tequila MongoDB PHP Library (driver library)

The Tequila MongoDB PHP library provides the high-level abstraction around the new low-level [PHP MongoDB driver](https://github.com/mongodb/mongo-php-driver), e.g.
schema-management, abstractions around connections, databases, collections, indexes etc.

This library follows the [MongoDB Driver Specifications](https://github.com/mongodb/specifications), and it's API is quite similar to the API of the official [MongoDB PHP Library](https://github.com/mongodb/mongo-php-driver).
There are also differences with official lib API and implementation:
- Better bulk write API and implementation - this lib has "write models", more consistent implementation of the write results and makes more strict input validation.
- This library has a MIT license.
- This library is planned to have weakly release cycle after the version 1.0. 
- This library uses Symfony's OptionsResolver component to manage options. It makes library code more clear and organized, and so eases the contribution process.
- This library does not support old versions of MongoDB. This allows for fewer compromises in the library architecture, and eases the contribution process, support and testing. 
- The implementation of this library separates the Commands, WriteModels and Operations.

The library works on PHP 5.6.0 or higher, PHP 7.0 or higher, MongoDB 3.2 or higher. It also requires the PHP `mongodb` extension (the MongoDB driver for PHP) to be installed.

The library is under an active development and is NOT ready for use right now. The first release is planned for October 12, 2016.
Contributions are appreciated.

### Todo before first release:

- FindAnd* commands;
- Tests.

The documentation will come soon.
