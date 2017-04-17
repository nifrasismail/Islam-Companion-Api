<?php

namespace Framework\Utilities;

final class ReflectionExampleClass
{    
    /**
	 * Used to add numbers 
	 * 
	 * This function adds the three numbers given as parameters
	 * It returns the sum of the numbers and a random string
	 * The random string is given as the last parameter
	 * 	 
	 * {@internal context browser}
	 * 
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param int $number1 [1-100] the first number
	 * @param int $number2 [1-100] the second number
	 * @param int $number3 [1-100] the third number
	 * @param array $data contains the type of the numbers and a string
	 * type => string [integer~float] the type of number to be added
	 * random_string => string [custom] a random string that is returned by the function
	 * 
	 * @return array $result the result of the function
	 * sum => int [1-100] the sum of the three numbers
	 * random_string => string the random string
 	*/
    public function AddNumbers($number1, $number2, $number3, $data)
	{
	   /** The result of adding the three numbers */
	   $sum    = $number1 + $number2 + $number3;
	   /** The result of the function */
	   $result = array("sum"=>$sum,"random_string"=>$data['random_string']);
	   
	   return $result;
	}
	
	/**
     * Used to validate certain function parameters
     * 
     * It checks if the given function parameter is valid
     * It signals an error if the length of the random string is larger than 10 characters
	 * 
     * @since 1.0.0	 
	 * @param string $parameter_name the name of the parameter
	 * @param string $parameter_value the value of the parameter
	 * @param array $all_parameter_values the value of all the method parameters
	 * @param array $all_parsed_parameters details of all the method parameters
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 * is_valid => boolean indicates if the parameters are valid
	 * validation_message => string the validation message if the parameters could not be validated
     */
    public function CustomValidation($variable_name, $parameter_value, $all_parameter_values, $all_parsed_parameters)
	{
		/** The result of validating the parameter */
	    $validation_result                           = array("is_valid"=>true,"validation_message"=>"");
		/** If the random_string variable needs to be validated */
		if ($variable_name == "random_string") {			
		    /** The length of the random string parameter value */
	        $string_length                           = strlen($parameter_value);
			/** If the length of the random string is larger than 10 characters then the string is marked as not valid */
			if ($string_length > 80) {
				$validation_result['validation_message'] = "Random string length must be less than 80 characters";
			}
		}
		
	    return $validation_result;
	}
}
?>
