Made by: Nadir Latif (nadir@pakjiddat.com, http://pakjiddat.com)

The Utilities Framework package is a set of utility classes that provide easy access to utility functions. The goal of the package is to provide the developer with commonly used functions such as error handling. The main functions provided by the package are error handling, function caching, encryption/decryption, http digest authentication, template engine and database abstraction. Each function category for example encryption is implemented by a separate class. The package implements the Factory design pattern. To access a utility function such as encryption the following code is used:

$encryption=UtilitiesFramework.Factory("encryption");

This creates an object of the Encryption class which can be used to encrypt and decrypt text. See the file documentation/example.php and example.class.php for examples on how to use the UtilitiesFramework classes. See the file documentation\documentation.html for further documentation on the Utilities Framework