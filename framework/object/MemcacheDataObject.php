<?php

namespace Framework\Object;

/**
 * This class implements the Memcache Object class 
 * 
 * Each object of this class represents a single Memcache data item. e.g a memcache key/value pair
 * It contains functions that help in constructing Memcache data objects
 * 
 * @category   Framework
 * @package    Object
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0 
 */
class MemcacheDataObject extends DataObject
{
	/**
	 * Sets the object parameters
	 * Used to add all the memcache servers to the memcache object
	 * 
	 * It adds each memcache server given in application configuration
	 * It also sets the configuration object
	 */
	public function SetParameters()
	{
		/** Ip address of memcache server */
		$memcache_server        = $this->GetConfig("general","memcache_server");	
	    /** The memcache object connects to the memcache server */
	    $this->GetComponent("memcache")->pconnect($memcache_server);
	}
	
    /**
     * Used to load the data from memcache to the data property of the object
     * 
     * It reads data from memcache and loads it to the $data property of the object
     * It uses the object's key_field property as the memcache key
     * The current object corresponds to a memcache value that corresponds to a single memcache key
	 * 
	 * @param mixed $parameters used to read the data from memcache
	 * 
	 * @return $is_valid used to indicate that data was found in memcache
     */
    final public function Read($parameters)
    {
    	/** The value is fetched from memcache */
    	$value                =  $this->GetComponent("memcache")->get($this->meta_information['key_field']);
		/** If the value exists then it is unserialized */
		if($value) {
			$this->data       = unserialize($value);					
		}
		/** Otherwise the result is set to false */
		else $this->data      = false;
		/** Used to indicate if the data was found by memcache or not */
		$is_valid             = ($this->data)?true:false;
		
		return $is_valid;
    }
	
    /**
     * Used to delete the memcache key data
     * 
     * It deletes data from memcache
	 *  
	 * @throws object Exception an exception is thrown if the object is read only
     * @throws object Exception an exception is thrown if the object could not be deleted
     */
    final public function Delete()
    {
    	/** If the current object is set to read only then an exception is thrown */
    	if ($this->readonly) throw new \Exception("Cannot delete readonly object.");        
        /** The value is deleted from memcache */
    	$is_deleted                =  $this->GetComponent("memcache")->delete($this->meta_information['key_field']);
		/** An exception is thrown if the value could not be deleted */
		if (!$is_deleted) throw new \Exception("Memcache value for the key: ".$this->meta_information['key_field']." could not be deleted");
    }

    /**
     * Used to indicate if the record already exists in memcache
     * 
     * It checks if the key field of the record already exists in memcache
     * If it does then the function returns true
     * Otherwise it returns false
	 * 
     * @return boolean $record_exists it is true if the record already exists. it is false otherwise 
     */
    final public function RecordExists()
    {        
        /** The value is fetched from memcache */
    	$value                   =  $this->GetComponent("memcache")->get($this->meta_information['key_field']);
		/** If the value exists then $record_exists is set to true. Otherwise it is set to false */
		$record_exists           = ($value !==false)?true:false;		
		
		return $record_exists;
    }

    /**
     * Used to save the object data
     * 
     * It saves the object data to memcache
	 * 
	 * @throws object Exception an exception is thrown if the object is read only
	 * @throws object Exception an exception is thrown if the object's data could not be saved
     */
    final public function Save()
    {
    	/** If the current object is set to read only then an exception is thrown */
    	if ($this->meta_information['readonly']) throw new \Exception("Cannot save readonly object.");
		/** The data stored in memache. It is serialized before storing */
		$value                     = serialize($this->data); 
        /** The value is saved to memcache */
    	$is_saved                  =  $this->GetComponent("memcache")->set($this->meta_information['key_field'],$value,0,0);
		/** An exception is thrown if the value could not be saved */
		if (!$is_saved) throw new \Exception("Memcache value for the key: ".$this->meta_information['key_field']." could not be saved");
    }
}