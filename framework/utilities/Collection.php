<?php

namespace Framework\Utilities;

/**
 * Collection class provides functions for manipulating collections of objects
 * 
 * It provides functions such as deleting an element from an array
 * 
 * @category   Framework
 * @package    Utilities
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class Collection
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
     * @return String static::$instance name the instance of the correct child class is returned 
     */
    public static function GetInstance($parameters)
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
    /**
     * Used to delete an array element
	 * 
     * @param array $array the array to be updated
     * @param int $position the position of the element to be deleted. the first position starts from 0		 
     * 		 
     * @return array $updated_array the updated array is returned
     */
    public function DeleteArrayElement($array, $position)
    {
        /** The updated array */
        $updated_array              = array();
        /** Each array element is checked */
        for ($count = 0; $count < count($array); $count++) {
        	/** If the current counter is not equal to the position of element to be deleted */
        	if ($count != $position) {
        		/** The array element is added to updated array */
        		$updated_array[]    = $array[$count];
        	}
        }
        
        return $updated_array;
    }   
}