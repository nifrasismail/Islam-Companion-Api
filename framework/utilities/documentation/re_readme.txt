Made by: Nadir Latif (nadir@pakjiddat.com, http://pakjiddat.com)

The Reflection class provides functions for inspecting the source code of php classes. Currently the class provides functions for validating the parameters, return value and context of a given function using the Doc Block comments of the function. The Doc Block comments need to be in PhpDoc format

The class provides following public functions:

1) ParseMethodDocBlockComments
It takes an object and function name as parameters. It parses the functions Doc Block comments. It returns an array containing the information in the Doc Block comments

2) ValidateMethodParameters
It extracts the @param tags in the given functions Doc Block comments. It checks each function parameter against the information in the @param tag for the parameter. For example if a @param tag is given as: "@param int [1-100] an integer between 1 and 100", then the ValidateMethodParameters function will first check if the parameter value is an integer. Then it will check if the value is between 1 and 100.

If [custom] was given in place of [1-100], then the ValidateMethodParameters function will validate the parameter using a custom validation function given by the user.

The ValidateMethodParameters function can also validate the values inside array parameters. For example if the parameter tag is given as: 

"@param array $data contains the type of the numbers and a string
type => string [integer~float] the type of number to be added
random_string => string [custom] a random string that is returned by the function" 

Then the ValidateMethodParameters will validate the $data array as well as the type and random_string variables in the $data array

3) ValidateMethodContext
It extracts the @internal tag given in the method long description section. See this link for details about the @internal tag: http://www.phpdoc.org/docs/latest/references/phpdoc/tags/internal.html. For example if a function has the tag "{@internal context browser}", then that means the function can only be run if the context of the application is browser. If the application is being run from the command line then this function cannot be called and the ValidateMethodContext will return an error.

4) ValidateMethodReturnValue
It extracts the @return tag given in the Doc Block comments of the method. It checks the return value of the function against the information in the @return tag.

For example if the @return tag is given as: "@return int $sum [1-100] sum of the three integers", then the ValidateMethodReturnValue function will first check if the return value of the function is an integer. It will then check if this value is between 1 and 100. The return value can also contain arrays.

5) ValidateMethodParametersAndContext
This method validates both the method parameters, return value and context. It calls the ValidateMethodParameters, ValidateMethodContext and ValidateMethodReturnValue functions.

6) GetClosure
This method returns an anonymous function that validates the user given function. It calls each of the functions described above. It makes it easy for the user to validate a given function

The file re_example.php shows how to use the Reflection class for validating a user given function