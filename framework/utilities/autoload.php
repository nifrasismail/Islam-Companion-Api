<?php

namespace Framework\Utilities;

/** All errors are reported and displayed */
ini_set("display_errors",true);
error_reporting(E_ALL);

/**
 * Function used to auto load the framework classes
 * 
 * The class is assumed to be in the current folder
 * The class file names match the class name. e.g the class
 * Encryption is in the file encryption.php
 * So the classes can be autoloaded by any psr-4 compliant autoloader
 * 
 * @since 1.0.0
 * @param string $class_name name of the class that needs to be included
 */
function autoload_framework_classes($class_name)
	{
		/** The class name is split into namespace and short class name */
		$temp_arr=explode("\\",$class_name);
		/** The class file name should be same as class_name.php */
		$class_name=($temp_arr[2]).".php";
		/** The application folder is checked for file name */				
		$file_name=realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.$class_name;
		/** If the file exists then it is included */						
		if(is_file($file_name))include_once($file_name);
    }
	
/**
 * Registers the autoload function
 */
spl_autoload_register("\Framework\Utilities\autoload_framework_classes",true,true);

?>