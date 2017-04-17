<?php

namespace Framework\Utilities;

/**
 *	This class provides an easy method for accessing utility objects. It implements the Factory design pattern
 * 
 *  This class contains a single factory method that returns utility objects depending on the user requirements
 *  The utility objects perform useful functions such as logging, encrpytion, database abstraction and general purpose functions
 *
 * @category   Framework
 * @package    Utilities
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.1.5
 */
abstract class UtilitiesFramework
{
    /**
     * List of aliases for utility object classes
     * An object class can be accessed using its alias name
     * The class alias can be used if the class name 
     * Cannot be calculated from the file name	 
     */
    static private $object_alias_list = array("database" => "DatabaseFunctions", "errorhandler" => "ErrorHandler", "filesystem" => "FileSystem");
    /**
     * List of utility objects supported by the utilities framework		 
     */
    static private $object_list = array();
    /**
     * This method create an instance of an object of one of the supported utility classes
     * The object is stored in an array along with a hash of its parameters
     * The object is also returned
     * 
     * The method calculates the hash of the parameters and stores the object in an array with the hash as the array key
     * This method can instantiate and store multiple instances of an object
     * For example an application can request multiple instances of a database abstraction object
     * Each instance has its own parameters. such as one object per datababse
     * The method throws an exception if the requested object type is not supported
     *      
     * @param string $object_type the type of the object that is required. e.g utilities, logging, encrpytion, database etc
     * The $object_type must match the file name of the utility object class. e.g if the file name is authentication.class.php then the $object_type should be authentication
     * The $object_type can also match an alias defined in the $object_alias_list static property
     * @param array $parameters the optional parameters for the object. for e.g for database object it will contain the database connection information
     * 
     * @return object $utility_object an object of the required utility class		 
     */
    final public static function Factory($object_type, $parameters = array())
    {        
        $object_hash = base64_encode(json_encode($parameters));
        
        foreach (UtilitiesFramework::$object_list as $stored_object_hash => $stored_object) {
            if ($stored_object_hash == $object_hash)
                return $stored_object;
        }
        
        /** If the object type matches a class alias then it is set to the class name */
        $object_type               = (isset(self::$object_alias_list[$object_type])) ? self::$object_alias_list[$object_type] : $object_type;
        /** Otherwise the class name is calculated */
        $class_name                = '\Framework\Utilities\\' . ucfirst($object_type);
        /**
         * Used to check if utility class implments Singleton pattern
         * If it has a static function called GetInstance then
         * It is assumed to be a Singleton class
         * The GetInstance method is used to get class instance
         */
        $callable_singleton_method = array(
            $class_name,
            "GetInstance"
        );
        if (is_callable($callable_singleton_method)) {
            /** If the object parameters are associative then the parameters are considered as single parameter for the callback function */
            $is_associative = (bool) count(array_filter(array_keys($parameters), 'is_string'));
            //if($is_associative)$parameters=array($parameters);								
            $utility_object = call_user_func_array($callable_singleton_method, array(
                $parameters
            ));
        }
        /** Otherwise the utility object is created using new operator */
        else
            $utility_object = UtilitiesFramework::$object_list[$object_type][$object_hash] = new $class_name($parameters);
        
        UtilitiesFramework::$object_list[$object_type][$object_hash] = $utility_object;
        
        return $utility_object;        
    }
}
