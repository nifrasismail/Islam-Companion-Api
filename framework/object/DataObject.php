<?php

namespace Framework\Object;

use \Framework\Configuration\Base as Base;

/**
 * Abstract class. must be implemented by a child class
 * This class provides a base class for Data Objects
 * 
 * Each object of this class represents a single data object. e.g database row or database table
 * It contains functions that help in constructing  data objects
 * 
 * @category   Framework
 * @package    Object
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0 
 */
abstract class DataObject extends Base
{
	/**
     * The meta information about the object          
     */
    protected $meta_information;	
    /**
     * Object data     	
     */
    protected $data;	
	
	/**
     * Used to set the range from which the data should be fetched
     * 
     * It sets the start and end values for the data range	 
	 * 
     * @param string $start the index of the first record
	 * @param string $end the number of records to fetch
     */
    public function SetLimit($start, $end)
    {
    	$this->meta_information['limit']['start']  = $start;     
        $this->meta_information['limit']['end']    = $end;
    }
	
	/**
     * Used to get the key field
     *
     * It gets the name of the key field
	 * The key field is used to search for the data
	 * 
	 * @return string $field_name the field name for the object
     */
    public function GetKeyField()
    {
        $key_field = (isset($this->meta_information['key_field']))?$this->meta_information['key_field']:"";
		
		return $key_field; 
    }
	
	/**
     * Used to set the object meta information
     * 
     * It sets the meta information property of the object
	 *      
     * @param array $meta_information the object's meta information. it is an array with atleast one key:
	 *    configuration => the configuration object
     */
    public function __construct($meta_information)
    {
    	/** The configuration object */
		$configuration                = $meta_information['configuration'];
		/** The configuration object is set */
		$this->SetConfigurationObject($configuration);
		/** The object meta information is set */		
        $this->SetMetaInformation($meta_information);
    }
	
	/**
     * Used to set the key field
     * 
     * It sets the key field
	 *      
     * @param string $key_field key field used to search for the data     
     */
    public function SetKeyField($key_field)
    {        
        $this->meta_information['key_field'] = $key_field; 
    }
	
    /**
     * Used to get the object data
     * 
     * It returns the object data     
	 * 
     * @return $object_data array the object's data property is returned          	
     */
    public function GetData()
    {        
        /** The object data is returned */
        return $this->data;        
    }
	
    /**
     * Used to set the object data
     * 
     * It sets the object data		 
     *      
     * @param $data array the object data to be set 		 	
     */
    public function SetData($data)
    {        
        /** The  object data is set */
        $this->data = $data;        
    }
	
    /**
     * Used to edit the object data
     * 
     * It edits the given property of the data object
     * The user should call save function to save the state of the object
     *      
     * @param string $field_name name of the field
     * @param string $field_value new value of the field
     */
   public function Edit($field_name, $field_value)
   {        
        /** The field value is added to the $data property */
        $this->data[$field_name] = $field_value;       
   }

	/**
     * Used to set the readonly property
     * 
     * It sets the object property that indicated if object data is read only    
	 * It calls the SetReadOnly function of the underlying data object  	
     *      
     * @param boolean $readonly used to indicate if object data is read only 
     */
    public function SetReadonly($readonly)
	{
		/** The readonly property of the underlying data object is called */
    	$this->meta_information['readonly'] = $readonly;
	}

	/**
     * Used to indicate if the object is readonly
     * 
     * It returns the readonly meta information property
     *      
     * @return boolean $readonly used to indicate if object data is read only 
     */
    public function IsReadonly()
	{
		/** The readonly property of the object */
    	$readonly = $this->meta_information['readonly'];
		
		return $readonly;
	}
	
    /**
     * Used to get the value of required field
     * 
     * It returns the value of the required field		 
     *      
     * @throws object Exception an exception is thrown if the field value does not exist
     * 
     * @return string $field_value the value of the given field name 
     */
    public function Get($field_name)
    {        
        if (!isset($this->data[$field_name]))
            throw new \Exception("Value for the field: " . $field_name . " does not exist");
        
        /** The field value is fetched from $data property */
        $field_value = $this->data[$field_name];
        
        return $field_value;       
    }
	
	/**
     * Used to transform the given parameters
     * 
     * It transforms the given parameters into a suitable form
	 * For example from a general relational data format to a format
	 * That is suitable for WordPress queries		 
     *      
	 * @param mixed $parameters the parameters used to fetch the data
     * 
     * @return array $transformed_parameters the transformed parameters 
     */
    public function TransformParameters($parameters)
    {        
        /** The given parameters are transformed */
        $transformed_parameters    = $parameters;
		
		return $transformed_parameters;
    }
	
	/**
     * Used to filter the fetched data
     * 
     * It checks the given parameters and updates the returned data
	 * So it only includes the fields given in the parameters
	 * If the parameters specify distinct field name
	 * Then the function only returns records that have distinct values for that field	 
     * 
	 * @param mixed $parameters the parameters used to fetched the data. for relational data, it should have following keys:
	 * fields => list of field names to fetch
     * condition => the condition used to fetch the data from database
	 * It can be a single string or integer value. in this case the previously set field name and table name are used 
	 * Or an array with following fields: field,value,table,operation and operator
	 * read_all => used to indicate if all data should be returned
	 * In case of non relational data, it can be empty
	 * @param array $data the data read from the data source
     * 
	 * @return array $filtered_data the filtered data
     */
    public function FilterData($parameters, $data)
    {        
        /** The filtered data */
        $filtered_data    = $data;
		
		return $filtered_data;
    }
	
    /**
     * Used to load the data from database to the data property of the object
     * 
     * It reads data from database and loads it to the $data property of the object
     * It uses the field name value given as parameter
	 * 	 
	 * @param mixed $parameters the parameters used to read the data
     */
    abstract public function Read($parameters);
	
    /**
     * Used to delete the object data
     * 
     * It deletes data from database
	 *  
	 * @throws object Exception an exception is thrown if the object is read only
     * @throws object Exception an exception is thrown if the object could not be deleted
     */
    abstract public function Delete();
	    
    /**
     * Used to indicate if the record already exists in database
     * 
     * It checks if the field name of the record already exists in database
     * If it does then the function returns true
     * Otherwise it returns false
	 * 
     * 
     * @return boolean $record_exists it is true if the record already exists. it is false otherwise 
     */
    public function RecordExists(){}
	
	/**
     * Used to set the meta information for the given data type	 
     * 
     * It sets the meta information that is required by the given data type	 
     *
	 * @param mixed $meta_information the meta information that is required by the given data type	
     */
    public function SetMetaInformation($meta_information){}
	
    /**
     * Used to save the object data
     * 
     * It saves the object data to database. If the field name of the data contains a value
     * Then the data is updated. Otherwise it is added
	 * 
	 * @throws object Exception an exception is thrown if the object is read only
	 * 
     * @return int $record_id the value of the field name of the saved row 
     */
    abstract public function Save();
}