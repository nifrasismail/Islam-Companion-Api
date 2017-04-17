<?php

namespace Framework\Object;

use \Framework\Configuration\Base as Base;

/**
 * Abstract class. must be implemented by a child class
 * This class provides a base class for Etl scripts
 * 
 * Each object of this class represents an extract transform and load operation. e.g an etl script
 * It contains functions that help in constructing etl scripts
 * 
 * @category   Framework
 * @package    Object
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
abstract class DataObjectEtl extends Base
{
	/**
     * The name of the DataObject class that will be used by the ETL
     * 
     * @since 1.0.0
     */
    protected $data_object_class;
	/**
     * The extracted data
     * 
     * @since 1.0.0
     */
    protected $extracted_data;
    /**
     * The transformed data
     * 
     * @since 1.0.0		
     */
    protected $transformed_data;
	
   	/**
     * Used to set the DataObject class name
     * 
     * It sets the $data_object_class properties to the given DataObject class name  
     * 
     * @since 1.0.0
     * @param string $data_object_class the name of the DataObject class
	 * 
     */
    public function SetDataObjectClass($data_object_class)
	{
		$this->data_object_class = $data_object_class;
	}
	
	/**
     * Used to set the DataObject class name to the default value
     * 
     * It sets the $data_object_class properties to the default DataObject class given in application parameters
     * 
     * @since 1.0.0
	 * 
     */
    protected function SetDefaultDataObjectClass()
	{
		/** The application database type. it is fetched from application configuration */
		$this->data_object_class = $this->GetConfig("general","database_object_class");
		
	}
	
	/**
     * Used to extract data	 
     * 
     * It extracts data using the given data extraction information
	 * For each table data, a DataObject instance is created
	 * The data from each DataObject is read and saved to local object property       
     * 
     * @since 1.0.0
     * @param mixed $data the data used to extract information from a data source
	 * It is an array with following keys:
	 * select => the columns to select. it is a comma separated list of field names
	 * table => the name of the table
	 * condition => the where clause used to fetch the data. it can be a string or an array
	 * it is same as condition clause for MySQLData objects
	 * 
     */
    protected function Extract($data)
	{
		/** If the data object class is not set then it is set to default value */
		if ($this->data_object_class == "")
		    $this->SetDefaultDataObjectClass();
		
		/** For each Mysql table data a Mysql data object is created */
		for ($count = 0; $count < count($data); $count++) {
			/** The data for a single table */
			$table_data              = $data[$count];
			/** The DataObject is created */
			$data_object             = new $this->data_object_class();
			/** The application configuration is fetched */
			$configuration           = $this->GetConfigurationObject();		
			/** The application configuration object is set in the new data object */
			$data_object->SetConfigurationObject($configuration);
			
			/** The columns to read */
			$fields                  = explode(",",$table_data['select']);
			/** The condition used to fetch the data */
			$condition               = $table_data['condition'];
			/** The table containing the data */
			$table_name              = $table_data['table'];			
			/** The table name is set */
			$data_object->SetTableName($table_name);
			/** The data is read by the data object */
			$data_object->Read($fields,$condition,true);  
			/** The extracted table data is saved as extracted data */
			$this->extracted_data[]  = $data_object->GetData();
		}
	}
	
	/**
     * Used to generate the extraction data
     * 
     * It generates the data that will be used in data extraction	       
     * 
     * @since 1.0.0    
	 * 
	 * @return array $data the data used to extract the main data
     */
    abstract protected function GetExtractionData();
	
	/**
     * Used to transform the data
     * 
     * It transforms the extracted data
	 * The transformed data is saved to local object property		       
     * 
     * @since 1.0.0    
	 * 
     */
    abstract protected function Transform();
	
	/**
     * Used to load the data
     * 
     * It loads the transformed data	 		     
     * 
     * @since 1.0.0    
	 * 
     */
    abstract protected function Load();
	
	/**
     * Used to call the Extract Transform and Load functions
     * 
     * It calls the etl functions 		     
     * 
     * @since 1.0.0
	 * 
     */
    public function Etl()
	{
		/** Used to generate the data needed for extracting the main data */
		$data = $this->GetExtractionData();
		/** Used to extract the data */
		$this->Extract($data);
		/** Used to transform the data */
		$this->Transform();
		/** Used to load the data */
		$this->Load();
	}
}