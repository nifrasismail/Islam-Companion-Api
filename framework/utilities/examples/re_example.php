<?php

namespace Framework\Utilities;
error_reporting(E_ALL);
ini_set("display_errors",1);
include('ReflectionExampleClass.php');

try {
    /** The reflection example class object is created. It provides the test function */
    $reflection_example         = new ReflectionExampleClass();
    /** The function that provides custom validation for the test function parameters. It signals an error if the length of the random string is larger than 80 characters */
    $custom_validation_callback = array($reflection_example, "CustomValidation");
    /** The safe_function_caller closure is fetched from the Reflection class */
    $safe_function_caller       = Reflection::GetClosure();
    /** The parameters for the test function */
    $parameters                 = array("number1"=>30,
									"number2"=>10,
									"number3"=>10,
 									"data"=>array(
 										  "type"=>"integer",
                                          "random_string"=>"<b style='text-align:center'>The result of adding the three integers is: </b>"
									    )
								  );
    /** The current application context */
    $context                    = "browser";							 
    /** The test function is called through the safe function caller */
    $result                     = $safe_function_caller($reflection_example, "AddNumbers", $parameters, $context, $custom_validation_callback, $context);			
    /** The result of adding the numbers is displayed */
    echo $result['random_string'].$result['sum'];
}
catch(\Exception $e) {
	die($e->getMessage());	
}
?>