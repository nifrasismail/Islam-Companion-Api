<?php

namespace Framework\Object;

use \Framework\Configuration\Base as Base;

/**
 * Abstract class. must be implemented by a child class
 * This class provides a base class for data Backup/Restore scripts
 * 
 * Each object of this class represents a data backup/restore operation. e.g a mysql database backup script
 * It contains functions that help in constructing data backup and restore scripts
 * It backups up and restores objects of type DataObject to tilde separated value (TSV) files
 * 
 * @category   Framework
 * @package    Object
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
abstract class DataObjectBackupRestore extends Base
{
	/**
     * The absolute path to the folder that needs to be backedup
     * 
     * @since 1.0.0
     */
    private $data_folder_path;
	
   	/**
     * Used to set the data folder path
     * 
     * It sets the $data_folder_path attribute of the current object  
     * 
     * @since 1.0.0
     * @param string $data_folder_path the absolute path to the data folder
	 * 
     */
    public function SetDataFolderPath($data_folder_path)
	{
		$this->data_folder_path = $data_folder_path;
	}
		
	/**
     * Used to backup data
     * 
     * It fetches the data from the given object
	 * The data is converted to a tilde separated string
	 * The strings are combined and separated with newline
	 * The combined string is saved to the given file
	 * The field names are written to first line of the file
     * 
     * @since 1.0.0
     * @param array $object_information the information for the object. it is used to create the object
	 * type => string the name of the object class
	 * meta_information => array the meta information for the object. e.g key_field, configuration, data_type
	 * parameters => array the parameters for reading the object data
	 * @param string $file_path the absolute path to the backup file
	 * @param int $max_rows_per_file the maximum number of rows per backup file
	 * 
     */
    public function Backup($object_information, $file_path, $max_rows_per_file=-1)
	{		
		/** The start index for fetching the data */
		$start              = 0;
		/** The data object */
		$data_object        = new $object_information['type']($object_information['meta_information']);
		/** If the max_rows_per_file is set, then the start and end limits for the data are set */		
		if ($max_rows_per_file >= 0) {			
		    $data_object->SetLimit($start, $max_rows_per_file);
		}
		/** The loop counter */
		$counter            = 0;
		/** The data is read from the data object as long as it has data */
		while ($data_object->Read($object_information['parameters'])) {
			/** The counter is increased by 1 */
			$counter++;
			/** The file name is set */
			$file_name      = str_replace(".", "-".$counter.".",$file_path); 								  
			/** The data to be saved */
			$data           = $data_object->GetData();
			/** The data is saved to tsv file */
			$this->SaveDataToFile($data, $file_name);		
			/** If the max_rows_per_file was set then the start value is updated */
			if ($max_rows_per_file != -1) {
			    $start         += $max_rows_per_file;
				/** The limits for reading the data are set */
				$data_object->SetLimit($start, $max_rows_per_file);
			}			
		}
	
	}
	
	/**
     * Used to save data
     * 
     * It saves the given data to the given file
     * 
     * @since 1.0.0
     * @param array $data the data to be saved. it is an array of associative arrays
	 * @param string $file_path the absolute path to the backup file
	 * 
     */
    public function SaveDataToFile($data, $file_path)
	{
		/** The field list */
		$field_list        = array();
		/** The field names are fetched from the data */
		$field_names       = array_keys($data[0]);
		/** Each field names are converted to a displayable format */
		for ($count = 0; $count < count($field_names); $count++) {
			$field_list[]  = ucfirst(str_replace("_"," ",$field_names[$count]));
		}		
		/** The field names of the file */
		$field_names       = implode("~", $field_list);
		/** The data rows */
		$data_rows         = array($field_names);
		/** Each data item is converted to tilde separated line */
		for ($count = 0; $count < count ($data); $count++) {
			$data_item     = array_values($data[$count]);
			/** The data row */
			$data_row      = implode("~", $data_item);
			/** The data row is added */
			$data_rows[]   = $data_row;
		}
		/** The file contents. The data rows are saved in lines */
		$file_contents     = implode("\n",$data_rows);
		/** The file contents are written to file */
		$fh                = fopen($file_path, "w");
		/** The file contents are written */
		fwrite($fh, $file_contents);
		/** The file is closed */
		fclose($fh);
	}
	
	/**
     * Used to initialize the object data from the file header
     * 
     * It initializes the object data using the given file header data
     * 
     * @since 1.0.0
     * @param array $file_header the file header. each element is a header field name
	 * @param array $file_row contains file data. each file row is an array element
	 * @param array $object_information the information for the object. it is used to create the object
	 * type => string the name of the object class
	 * meta_information => array the meta information for the object. e.g key_field, configuration, data_type
	 * parameters => array the parameters for reading the object data
	 * 
	 * @return object $data_object an object with base class DataObject is created
     */
    private function InitializeObject($file_header, $file_row, $object_information)
	{			
		/** The data row */
		$data_row                              = array(); 
		/** The header field names are converted to table field names */
		for ($count = 0; $count < count($file_header); $count++) {
			/** The file header field name is formatted into table field name */
			$file_header_field_name            = str_replace(" ","_",strtolower($file_header[$count]));
			/** If the header field name is same as the primary key */
			if ($file_header_field_name == $object_information['meta_information']['key_field']) {
			    /** The key field is unset from the data row so that the data can be saved to database */
			    continue;
			}		
			/** The data row is updated */
			$data_row[$file_header_field_name] = $file_row[$count];
		}
		/** The data object */
		$data_object                           = new $object_information['type']($object_information['meta_information']);			
		/** The data is set to the object */
		$data_object->SetData($data_row);
		/** The data object is set to read/write */
		$data_object->SetReadOnly(false);
		/** The data object is returned */
		
		return $data_object;
	}

	/**
     * Used to restore data
     * 
     * It restores the data from the given backupe file to an object of the given class	 
	 * Each line in the file contains data for a single object
     * 
     * @since 1.0.0
     * @param array $object_information the information for the object. it is used to create the object
	 * type => string the name of the object class
	 * meta_information => array the meta information for the object. e.g key_field, configuration, data_type
	 * parameters => array the parameters for reading the object data
	 * @param string $file_path the absolute path to the backup file
	 * 
     */
    public function Restore($object_information, $file_path)
	{
		/** Indicates if a file was found */
		$file_found             = false;
		/** The loop counter */
		$counter                = 1;
		/** Each file for the table is read */
		do {
			/** The file name */
			$file_name          = str_replace(".tlv", "-".$counter.".tlv", $file_path);
			/** If the file exists */
			if (is_file($file_name)) {
			    /** The counter value is increased by 1 */
			    $counter++;
			    /** The file contents are read */
		        $file_contents = $this->GetComponent("filesystem")->ReadLocalFile($file_name);
		        /** The file contents are converted to an array */
		        $file_contents = explode("\n", $file_contents);
				/** The file header */
				$file_header   = $file_contents[0];
				/** The file header fields are extracted */
				$file_header   = explode("~", $file_header);
				/** Each data item is extracted and saved */
				for ($count = 1; $count < count($file_contents); $count++) {
					$file_row  = explode("~", $file_contents[$count]);
					/** The data object. The file row is added to data object */
					$data_obj  = $this->InitializeObject($file_header, $file_row, $object_information);
					/** The data object is saved */
					$data_obj->Save();
				} 
				/** The data for the object is initialized fromthe file header */   
				$file_found    = true;
			}
			else
				$file_found    = false;
		}
		/** The loop continues as long as a file is found */
		while ($file_found);		
		
	}
}