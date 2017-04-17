<?php

namespace Framework\Utilities;

/**
 * Singleton class
 * Reflection class provides functions related to class reflection
 * 
 * It includes functions such as parsing doc block comments of methods
 * 
 * @category   Framework
 * @package    Utilities
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.3
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class Reflection
{    
    /**
     * The single static instance
     */
    protected static $instance;   
    /**
     * Used to return a single instance of the class
     * 
     * Checks if instance already exists
     * If it does not exist then it is created
     * The instance is returned
	 * 
     * @return Utilities static::$instance name the instance of the correct child class is returned 
     */
    public static function GetInstance()
    {        
        if (static::$instance == null) {
            static::$instance = new static();
        }
        return static::$instance;       
    }
	  
	/**
     * Used to extract the description text from Doc Block comments
     * 
     * It uses regular expressions to extract text from Doc Block comments
	 * It extracts both short and long description text	 
	 * 
     * @param string $comments the method comments string	 
	 * 
	 * @return array $parsed_description the parsed method description
	 * short_description => string the short description of the method
	 * long_description => string the long description of the method	                              
	 *                               
     */
    final private function ExtractDescriptionText($comments)
    {
    	/** The parsed description and context */
    	$parsed_description                                         = array();
		/** The comments are split on '*' */
		$line_arr                                                   = explode("*", $comments);
		/** The start array index for the description */
		$start_index                                                = -1;
		/** The end array index for the description */
		$end_index                                                  = -1;
		/** Each line is checked */
		for ($count = 0; $count < count($line_arr); $count++) {
			/** The comment line. Carriage return and line feed are removed from the line */
			$line_arr[$count]                                       = str_replace("\n","",str_replace("\r","",$line_arr[$count]));
			/** '/' and ' ' are trimmed from the line */
			$line_arr[$count]                                       = trim(trim($line_arr[$count],"/ "));		
			/** If the line is not empty then start index is set */
			if ($line_arr[$count] != "" && $start_index == -1) {
			    $start_index                                        = $count;
				/** If the short description is not set then the line is added to the short description */
				if (!isset($parsed_description['short_description'])) {
					$parsed_description['short_description'][]      = $line_arr[$count];
				}
				/** 
				 * If the long description is not set then the line is added to the long description.
				 * The line is only added if it does not contain an internal tag
				 */
				else if (!isset($parsed_description['long_description']) && strpos($line_arr[$count], "@internal") === false) {
					$parsed_description['long_description'][]       = $line_arr[$count];					
				}
				/** The start and end indexes are set to -1 */
				$start_index                                        = -1;
				$end_index                                          = -1; 
			}			
			/** If the line is empty and start index, end index and long description have been set then the loop ends */
			if ($line_arr[$count] == "" && $start_index != -1 && isset($parsed_description['long_description'])) {
			    break;
			}
		}		

		/** If the short description is set */
		if (isset($parsed_description['short_description'])) {
		    $parsed_description['short_description']               = implode(". ",$parsed_description['short_description']);
		}
		/** If the long description is set */
		if (isset($parsed_description['long_description'])) {
		    $parsed_description['long_description']                = implode(". ",$parsed_description['long_description']);			
		}

		return $parsed_description;
    }
	 
	/**
     * Used to extract the version tags from the Doc Block comments
     * 
     * It uses regular expressions to extract version and since tags from Doc Block comments	 
	 * 
     * @param string $comments the method comments string	 
	 * 
	 * @return array $parsed_version the parsed version tags
	 * since => the since tag
	 * version => the version tag
     */
    final private function ExtractVersion($comments)
    {
    	/** The parsed version */
    	$parsed_version                                            = array();
		/** The since tag is extracted using regular expression */
		preg_match_all("/@since\s+([\d\.]+)/i", $comments, $matches);
		/** The since version number */
		$parsed_version['since']                                   = (isset($matches[1][0]))?$matches[1][0]:'';
		/** The since tag is extracted using regular expression */
		preg_match_all("/@version\s+([\d\.]+)/i", $comments, $matches);
		/** The version number */
		$parsed_version['version']                                 = (isset($matches[1][0]))?$matches[1][0]:'';
		
		return $parsed_version;
    }
	
	/**
     * Used to extract the internal tags from the Doc Block comments
     * 
     * It uses regular expressions to extract internal tags from Doc Block comments
	 * 
     * @param string $comments the comments string for the method	 
	 * 
	 * @return array $internal_tag_list the parsed internal tags
	 * 0 => array the context for the method
	 *      context => string  
     */
    final private function ExtractInternal($comments)
    {
    	/** The list of internal tags */
    	$internal_tag_list                                       = array();
		/** The internal tags are extracted using regular expression */
		preg_match_all("/\{@internal\s+([^\s]+)\s+(.+)\}/i", $comments, $matches);	
		/** All the internal tags are extracted */
		for ($count = 0 ; $count < count($matches[0]); $count++) {
		    /** The internal tag name */
		    $internal_tag_name                                   = (isset($matches[1][$count]))?$matches[1][$count]:'';		    
		    /** The internal tag description */
		    $internal_tag_list[$internal_tag_name]               = (isset($matches[2][$count]))?$matches[2][$count]:'';
		}		
		
		
		return $internal_tag_list;
    }
	
	/**
     * Used to validate a variable
     * 
     * It checks if the variable value matches the given type	 
     * It checks if the variable value is in given range provided the range is given
	 * 
	 * @param array $parsed_parameters details of the method parameters	 
	 * @param int $parameter_value the value of the method parameter
	 * @param array $custom_validation_callback the custom validation callback function
	 * @param array $all_parameter_values the value of all the method parameters
	 * @param array $all_parsed_parameters details of all the method parameters
	 * @param array $all_return_values the return value of the function
	 * @param array $all_return_parameters details of all the return value parameters
	 * @param bool $is_return if set to true then the return value needs to be validated
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 * is_valid => boolean indicates if the parameters are valid
	 * validation_message => the validation message if the parameters could not be validated
     */
    final private function ValidateVariable($parsed_parameters, $parameter_value, $custom_validation_callback, $all_parameter_values, $all_parsed_parameters,$return_value, $parsed_return_value, $is_return)
    {
    	/** The result of validating the method */
    	$validation_result                                              = array("is_valid"=>false,"validation_message"=>"");
		/** If the parameter type is an integer */
		if ($parsed_parameters['type'] == "int") {
		    /** If the parameter value is not an integer */
		    if (!is_numeric($parameter_value))
		        $validation_result["validation_message"]                = "Parameter: ".$parsed_parameters['variable_name']." is not an integer";			
		    /** If the parameter value range is not equal to custom and is not empty */
			else if ($parsed_parameters["range"] != "custom" && $parsed_parameters["range"] != "") {			
			    /** The minimum and maximum values for the parameter */
			    list($min_value, $max_value)                            = explode("-", $parsed_parameters['range']);
			    /** If the parameter value is out of range then the error message is set */
			    if ($parameter_value < $min_value || $parameter_value > $max_value) {
			        $validation_result["validation_message"]            = "Parameter value: ".$parameter_value." for the parameter: ".$parsed_parameters['variable_name']." is out of range";
		        }
		    }
		}
		/** If the parameter type is a string */
		else if ($parsed_parameters['type'] == "string") {
		    /** If the parameter value is not a string */
		    if (!is_string($parameter_value))
		        $validation_result["validation_message"]                 = "Parameter: ".$parsed_parameters['variable_name']." is not a string";
		    /** If the parameter value range is not equal to custom and is not empty */
			else if ($parsed_parameters["range"] != "custom" && $parsed_parameters["range"] != "") {
			    /** The possible values for the string. The values must be separated with ~ */
			    $possible_string_values                                  = explode("~", $parsed_parameters['range']);
			    /** If the parameter value is not one of the possible values then the error message is set */
			    if (!in_array($parameter_value, $possible_string_values)) {
			        $validation_result["validation_message"]             = "Parameter value: " . $parameter_value . " for the parameter: " . 
			                                                               $parsed_parameters['variable_name'] . " is not an allowed value. " . 
			                                                               "Allowed values: ".str_replace("~",",",$parsed_parameters['range']);
				}
		    }
		}
		
		/** If the parameter type is boolean */
		else if ($parsed_parameters['type'] == "boolean") {
		    /** If the parameter value is not a boolean */
		    if (!is_bool($parameter_value))
		        $validation_result["validation_message"]                 = "Parameter: ".$parsed_parameters['variable_name']." is not a boolean";		    
		}
		
		/** If the parameter type is object */
		else if ($parsed_parameters['type'] == "object") {
		    /** If the parameter value is not an object */
		    if (!is_object($parameter_value))
		        $validation_result["validation_message"]                 = "Parameter: ".$parsed_parameters['variable_name']." is not an object";		    		   
		}
		
		/** If the parameter type is array */
		else if ($parsed_parameters['type'] == "array") {
		    /** If the parameter value is not an array */
		    if (!is_array($parameter_value))
		        $validation_result["validation_message"]                 = "Parameter: ".$parsed_parameters['variable_name']." is not an array";
		    /** If the parameter value range is not given */
			else if ($parsed_parameters["range"] == '' && isset($parsed_parameters['values'])) {		        
		        /** Each parsed comment is checked */
		        for ($count = 0; $count < count($parsed_parameters['values']); $count++) {
		        	/** The sub option name */
		        	$sub_option_name                                     = $parsed_parameters['values'][$count]['variable_name'];		        
		        	/** Used to check if array sub option was found */	        	
					if (!isset($parameter_value[$parsed_parameters['values'][$count]['variable_name']])) {
		    	        $validation_result["validation_message"]         = "Array element: ".$sub_option_name." could not be found";
						break;
		    	    }
					/** An array element */
		    	    $array_element                                       = $parsed_parameters['values'][$count];			
		    	    /** The array element value */
		    	    $array_element_value                                 = $parameter_value[$parsed_parameters['values'][$count]['variable_name']];
		    	    /** The array element is validated */
					$validation_result                                   = $this->ValidateVariable($array_element, $array_element_value, $custom_validation_callback, $all_parameter_values, $all_parsed_parameters, $return_value, $parsed_return_value, $is_return);
				    /** If the validation message is not empty then it is updated and the loop ends */
				    if ($validation_result["validation_message"] != "") {
				        $validation_result["validation_message"]         = "Invalid value: ".var_export($array_element_value,true)." for array element: ".$array_element['variable_name'].
 				                                                           ". Details: ".$validation_result["validation_message"];						
					}		  					    	   
		        }
		    }		    
		}		

		/** 
		 * If the validation message is empty and if the parameter value range is given
		 * And the parameter range is set to custom, then the custom validation callback function is called
		 */
		if ($validation_result["validation_message"] == "" && isset($parsed_parameters["range"]) && $parsed_parameters["range"] == "custom") {			
		    /** The callback parameters */
			$callback_parameters                                = array($parsed_parameters['variable_name'], $parameter_value, $all_parameter_values, $all_parsed_parameters, $return_value, $parsed_return_value, $is_return);					
			/** The validation result. The custom callback function is called */
			$validation_result                                  = call_user_func_array($custom_validation_callback, $callback_parameters);            			  
	    }
	  
		/** 
		 * If the validation message is empty
		 * Then the result of validation is set to true and the validation result is returned
		 */
		if ($validation_result["validation_message"] == "")
		    $validation_result["is_valid"]                       = true;
				
		return $validation_result;
    }
	
	/**
     * Used to extract the array values for the given array parameter
     * 
     * It uses regular expressions to extract the information about an array's elements
	 * 
     * @param string $array_parameter_string the parameter string for the array
	 * @param string $comments the parameter's Doc Block comment string
	 * @param int $level the array nesting level
	 * 
	 * @return array $parsed_array_values the parsed array values	 
     */
    final private function GetArrayValues($array_parameter_string, $comments, $level)
    {
    	/** The array values */
    	$parsed_array_values                                       = array();
		/** The comments are split on '*' */
		$line_arr                                                  = explode("*", $comments);
		/** The start array index for the description */
		$start_index                                               = -1;
		/** Each line is checked */
		for ($count1 = 0; $count1 < count($line_arr); $count1++) {
			/** The comment line. Carriage return and line feed are removed from the line */
			$line_arr[$count1]                                     = str_replace("\n","",str_replace("\r","",$line_arr[$count1]));
			/** '/' and ' ' are trimmed from the line */
			$line_arr[$count1]                                     = trim($line_arr[$count1],"/");
			/** If the line is not empty then start index is set */
			if (trim($line_arr[$count1]) == trim($array_parameter_string) && $start_index == -1) {
			    $start_index                                       = $count1;				
				continue;
			}
			/** If the line is empty and start index has been set then the loop ends */
			else if ((trim($line_arr[$count1]) == "" || strpos(trim($line_arr[$count1]), "@") !== false) && $start_index != -1) {
			    break;
			}			
			/** If start index is set */
			if ($start_index != -1) {
				/** The parsed parameters */
		    	$parsed_parameters                                 = array();
				/** The param tags are extracted using regular expression */
				preg_match_all('/@\s{'.($level*4).'}([^\s]+) => (array|string|int|boolean|object|mixed)\s(\[.+\])?\s*(.+)/i', "@".$line_arr[$count1], $matches);
				/** If the regular expression did not find any matches then the loop continues */
				if (!isset($matches[0][0])) {
					/** If the current line number is 1 more than the parameter line number then the loop ends */
					if (($start_index+1) == $count1) break;					
					else continue;
				}
				/** The parameter string for the sub array */
				$sub_array_parameter_string                        = trim(trim($matches[0][0]),"@");			
				/** The variable name */
				$parsed_parameters['variable_name']                = trim($matches[1][0]);
				/** The parameter type */
				$parsed_parameters['type']                         = trim($matches[2][0]);
				/** The range of values for the parameter value */
				$parsed_parameters['range']                        = str_replace("]","",str_replace("[","",trim($matches[3][0])));				
				/** The parameter description */
				$parsed_parameters['description']                  = trim($matches[4][0]);
				/** If the parameter type is an array */
				if ($parsed_parameters['type'] == 'array') {
					/** The values of the array are fetched */
			        $array_values                                  = $this->GetArrayValues($sub_array_parameter_string, $comments, ($level+1));
				    /** If the array values are empty then the values are not set */
				    if (count($array_values) > 0)
				        $parsed_parameters['values']               = $array_values;				   				
				} 				
				/** The parsed array values */
				$parsed_array_values[]                             = $parsed_parameters;			
			}			
		}
	    return $parsed_array_values;
    }

	/**
     * Used to extract the parameters from the Doc Block comments
     * 
     * It uses regular expressions to extract param tags from Doc Block comments
	 * 	 
	 * @param string $tag_name the name of the tag that is to be extracted
     * @param string $comments the method comments string
	 * 
	 * @return array $parsed_parameters the parsed parameter tags
     */
    final private function ExtractParameters($tag_name, $comments)
    {
    	/** The parsed parameters */
    	$parsed_parameters                            = array();
		/** The param tags are extracted using regular expression */
		preg_match_all('/@'.$tag_name.'\s+([a-z\s]+)\s([$a-z_0-9]+)\s?(\[.+\])?\s(.+)/i', $comments, $matches);		
		/** The details of each parameter is checked */
		for ($count = 0; $count < count($matches[0]); $count++) {
			/** The parameter string */
			$parameter_string                         = trim($matches[0][$count]);
			/** The parameter type */
			$parameters['type']                       = trim($matches[1][$count]);
			/** The variable name */
			$parameters['variable_name']              = str_replace("$","",trim($matches[2][$count]));
			/** The parameter description */
			$parameters['description']                = trim($matches[4][$count]);
			/** The range of possible values of the parameter */
			$parameters['range']                      = str_replace("]","",str_replace("[","",trim($matches[3][$count])));
			if ($parameters['type'] == 'array') {
			    /** The values of the array are fetched */
			    $array_values                         = $this->GetArrayValues($parameter_string, $comments, 1);
				/** If the array values are empty then the values are not set */
				if (count($array_values) > 0)
				    $parameters['values']             = $array_values;		
			}
			/** The parameters are added to the parsed parameters array */
			$parsed_parameters[]                      = $parameters;
		}
	
		return $parsed_parameters;
    }
	
	/**
     * Used to validate the given parameters
     * 
     * It checks if the given parameters are valid
	 * 
     * @param array $parameters the value of the method parameters. it is an associative array
	 * @param array $parsed_parameters details of the method parameters. the parameter values are checked against these details
	 * @param array $custom_validation_callback the custom validation callback function	 
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 * is_valid => boolean indicates if the parameters are valid
	 * validation_message => the validation message if the parameters could not be validated
     */
    final private function ValidateParameters($parameters, $parsed_parameters, $custom_validation_callback)
    {
    	/** The result of validating the method */
    	$validation_result                                   = array("is_valid"=>false,"validation_message"=>"");
		/** The number of parameters */
		$parameter_count                                     = count($parsed_parameters);		
		/** Each parameter is checked */
		for ($count = 0 ; $count < $parameter_count; $count++) {
			/** The parameter name */
			$parameter_name                                  = $parsed_parameters[$count]['variable_name'];			
			/** If the parameter name does not exist then an error message is set */		
			if (!isset($parameters[$parameter_name])) {
				/** The validation message is set */
				$validation_result["validation_message"]     = "Value not given for the parameter: ".$parameter_name;
				/** The validation result is returned */
				return $validation_result;
			}
			/** If the parameter name matches the parsed parameter name */
			else {
				/** The parameter value */
				$parameter_value                             = $parameters[$parameter_name];
				/** The type of the parameter */
				$variable_type                               = $parsed_parameters[$count]['type'];
				/** If the variable is an integer, then it is validated */
				if ($variable_type == "int")
				    $validation_result                       = $this->ValidateVariable($parsed_parameters[$count], $parameter_value, $custom_validation_callback, $parameters, $parsed_parameters, array(), array(), false);
				/** If the variable is a string, then it is validated */
				else if ($variable_type == "string")
				    $validation_result                       = $this->ValidateVariable($parsed_parameters[$count], $parameter_value, $custom_validation_callback, $parameters, $parsed_parameters, array(), array(), false);
				/** If the variable is an object, then it is validated */
				else if ($variable_type == "object")
				    $validation_result                       = $this->ValidateVariable($parsed_parameters[$count], $parameter_value, $custom_validation_callback, $parameters, $parsed_parameters, array(), array(), false);
				/** If the variable is a boolean, then it is validated */
				else if ($variable_type == "boolean")
				    $validation_result                       = $this->ValidateVariable($parsed_parameters[$count], $parameter_value, $custom_validation_callback, $parameters, $parsed_parameters, array(), array(), false);
				/** If the variable is an array, then it is validated */
				else if ($variable_type == "array")
				    $validation_result                       = $this->ValidateVariable($parsed_parameters[$count], $parameter_value, $custom_validation_callback, $parameters, $parsed_parameters, array(), array(), false);
			
				/** If the validation message is not empty, then the validation result is returned */
				if ($validation_result['validation_message'] != "")
				    return $validation_result; 
			}
		}
		/** The result of validation is set to true and the validation result is returned */
		$validation_result["is_valid"]                       = true; 
					
		return $validation_result;
    }
	
	/**
     * Used to parse the Doc Block comments of a method
     * 
     * It first extracts the Doc Block comments of the given object's method
	 * It then parses the comments into an array containing the sections inside the comments
	 * 
     * @param object $controller_object the object that contains the function
	 * @param string $function_name the function name
	 * 
	 * @return array $parsed_comments the parsed doc block comments
     */
    final public function ParseMethodDocBlockComments($controller_object, $function_name)
    {
    	/** The parsed doc block comments */
    	$parsed_comments                       = array();
    	/** The class name */
        $class_name                            = get_class($controller_object);    
		/** The reflection object for the class name */    
        $reflector                             = new \ReflectionClass($class_name);    
        /** The function doc block comments */
        $comments                              = $reflector->getMethod($function_name)->getDocComment();
	    /** The description text. It is extracted using regular expression */
	    $parsed_comments['description_text']   = $this->ExtractDescriptionText($comments);
		/** The method version. It includes since and version tags. It is extracted using regular expression */
	    $parsed_comments['version']            = $this->ExtractVersion($comments);
		/** The internal tags. They are extracted using regular expressions */
	    $parsed_comments['internal']           = $this->ExtractInternal($comments);
		/** The method parameters */
	    $parsed_comments['parameters']         = $this->ExtractParameters("param",$comments);
		/** The method return value */
	    $parsed_comments['return_value']       = $this->ExtractParameters("return",$comments);
		$parsed_comments['return_value']       = (isset($parsed_comments['return_value'][0])) ? $parsed_comments['return_value'][0] : '';
		
		return $parsed_comments;
    }

	/**
     * Used to decode the given data
     * 
     * It first base64 decodes the string
	 * If the resulting string is json encoded then it is json decoded
	 * 
	 * @param object $controller_object the object that contains the function
	 * @param string $function_name the function name
	 * @param array $parameters the parameters for the callback function
	 * @param array $custom_validation_callback the custom validation callback function
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => boolean indicates if the parameters are valid
	 *    validation_message => the validation message if the parameters could not be validated
     */
    final public function ValidateMethodParameters($controller_object, $function_name, $parameters, $custom_validation_callback)
    {
    	/** The method doc block comments are parsed */
		$parsed_comments                       = $this->ParseMethodDocBlockComments($controller_object, $function_name);
		/** The parsed parameter information */
		$parsed_parameters                     = $parsed_comments['parameters'];		
		/** The result of validating the method parameters against the parsed parameters */ 
		$validation_result                     = $this->ValidateParameters($parameters, $parsed_parameters, $custom_validation_callback);
		/** If the validation message is not empty then is_valid is set to false */
		if ($validation_result['validation_message'] != "") {
			$validation_result['is_valid']     = false;
		}
		return $validation_result;
    }
	
	/**
     * Used to validate the method context
     * 
     * It checks if the method can be called in the current application context
	 * It checks if the application context occurs in the list of possible method contexts
	 * 
	 * @param string [any, local api, remote api, command line, browser] $method_context a comma separated list containing possible contexts for the method
	 * @param string $context the application context
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 * is_valid => boolean indicates if the parameters are valid
	 * validation_message => the validation message if the parameters could not be validated
     */
    final public function ValidateMethodContext($method_context, $context)
    {
    	/** The validation result */
		$validation_result       = array("is_valid" => false, "validation_message" => "");
    	/** The method context list */
		$method_context_list     = explode(",", $method_context);
		/** If the application context is not in the list of allowed contexts for the method and it is not equal to 'any' */
		if (strpos($method_context, "any") === false && !in_array($context, $method_context_list)) {
			/** The validation message is set */
			$validation_result['validation_message'] = "The method context: " . $method_context .
			                                           " does not allow the method to be called in the current application context: ".$context;
		}
		/** If the validation message has not been set then the is_valid property is set to true */
		if ($validation_result['validation_message'] == "") {
		    $validation_result['is_valid']           = true;
		}
		
		return $validation_result;
    }
	
	/**
     * Used to validate the return value of the given method
     * 
     * It first parses the Doc Block comments of the method
	 * It then validates the given return value
	 *  
	 * @param object $controller_object the object that contains the function
	 * @param string $function_name the function name
	 * @param string $return_value the return value of the function	 
	 * @param array $custom_validation_callback the custom validation callback function
	 * @param array $all_parameter_values the value of all the method parameters	 
	 *  
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => boolean indicates if the parameters are valid
	 *    validation_message => string the validation message if the parameters could not be validated
     */
    final public function ValidateMethodReturnValue($controller_object, $function_name, $return_value, $custom_validation_callback, $all_parameter_values)
    {
    	/** The method doc block comments are parsed */
		$parsed_comments           = $this->ParseMethodDocBlockComments($controller_object, $function_name);		
		/** The parsed method parameter information */
		$all_parsed_parameters     = $parsed_comments['parameters'];
		/** The parsed return value information information */
		$parsed_return_value       = $parsed_comments['return_value'];
		/** If the function returns a value then it is validated */
		if ($parsed_return_value != "") {
		    /** The result of validating the return value */
		    $validation_result     = $this->ValidateVariable($parsed_return_value, $return_value, $custom_validation_callback, $all_parameter_values, $all_parsed_parameters, $return_value, $parsed_return_value, true);	
		}
		else {
			$validation_result     = array("is_valid" => true, "validation_message" => "");
		}
		
	
		return $validation_result;
    }
	
	/**
     * Used to validate the given method
     * 
     * It first parses the Doc Block comments of the method
	 * It then validates the parameters
	 * Then it validates the method context if one was set in the long description
	 * 
	 * @param object $controller_object the object that contains the function
	 * @param string $function_name the function name
	 * @param string $context the current application context
	 * @param array $parameters the parameters for the callback function
	 * @param array $custom_validation_callback the custom validation callback function
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => boolean indicates if the parameters are valid
	 *    validation_message => string the validation message if the parameters could not be validated
     */
    final public function ValidateMethodParametersAndContext($controller_object, $function_name, $context, $parameters, $custom_validation_callback)
    {
    	/** The method doc block comments are parsed */
		$parsed_comments        = $this->ParseMethodDocBlockComments($controller_object, $function_name);		
		/** The parsed parameter information */
		$parsed_parameters      = $parsed_comments['parameters'];
		/** If the function does not accept any parameters */
		if (!isset($parsed_parameters[0])) {
		    $validation_result  = array("is_valid" => true, "validation_message" => "");
		}
		else {
		    /** The result of validating the method parameters against the parsed parameters */ 
		    $validation_result  = $this->ValidateMethodParameters($controller_object, $function_name, $parameters, $custom_validation_callback);
		}
		/** If the parameters are valid, then the method context is validated */
		if ($validation_result['is_valid']) {			
		    /** The method context information */
		    $method_context     = $parsed_comments['internal']['context'];
		    /** The method context is validated */
		    $validation_result  = $this->ValidateMethodContext($method_context, $context);
		}
		return $validation_result;
    }
	
	/**
     * It returns a closure that calls the given function and validates it
	 * It allows the function to be called in a safe way
     * 
     * It returns a closure that validates the parameters of the user given function
	 * It then calls the function
	 * After that it validates the return value of the function
	 * In case of validation errors, the closure displays an error message and ends script execution	 
	 * 
	 * @return object $closure an object of class Closure is returned. The closure function calls the given function with the given parameters	 
     */
    final public static function GetClosure()
    {
    	/** The closure that validates and calls the user given function */    	 
    	$closure = function ($class_object, $function_name, $parameters, $application_context, $custom_validation_callback) {
    		/** The reflection object is created. It provides the functions for parsing comments */
			$reflection                   = new Reflection();
			/** The parsed method comments */			
	    	$parsed_comments              = $reflection->ParseMethodDocBlockComments($class_object, $function_name);
			/** The internal tags. They are extracted using regular expressions */
			$interal_tags                 = $parsed_comments['internal'];
			/** The list of allowed application contexts for the method */
			$method_context               = $interal_tags['context'];
			/** The application context is validated */
			$validation_result            = $reflection->ValidateMethodContext($method_context, $application_context);
			/** The validation result is checked */
			if ($validation_result['is_valid'] === false) {
				throw new \Exception("Invalid method context. Details: ".$validation_result['validation_message']);
			}
    		/** The test function parameters are validated */
			$validation_result            = $reflection->ValidateMethodParameters($class_object, $function_name, $parameters, $custom_validation_callback);
			/** The validation result is checked */
			if ($validation_result['is_valid'] === false) {
				throw new \Exception("Function parameters could not be validated. Details: ".$validation_result['validation_message']);
			}
			/** The parameter values are extracted */
			$parameters                   = array_values($parameters);
			/** The test function is called */
			$result                       = $class_object->$function_name($parameters[0], $parameters[1], $parameters[2], $parameters[3]);			
			/** The test function return value is validated */
			$validation_result            = $reflection->ValidateMethodReturnValue($class_object, "AddNumbers", $result, $custom_validation_callback, $parameters);
			/** The validation result is checked */
			if ($validation_result['is_valid'] === false) {
				throw new \Exception("Function return value could not be validated. Details: ".$validation_result['validation_message']);
			}
			return $result;
		};
		
		return $closure;
    	
    }	
}
