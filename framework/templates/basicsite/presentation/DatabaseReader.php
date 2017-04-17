<?php
namespace Framework\Templates\BasicSite\Presentation;
use Framework\Configuration\Base as Base;
use Framework\Object\MysqlDataObject as MysqlDataObject;
/**
 * This class implements the database reader class
 *
 * It is used to read data from database
 *
 * @category   Framework
 * @package    Presentation
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.6
 */
class DatabaseReader extends Base
{
    /**
     * Used to read data from database
     *
     * It reads the data from database table
     * The data may be structured and stored in normal mysql table
     * Or the data may be unstructured and stored in key/value pairs
     *
     * @param array $meta_information the meta information used to fetch the data from database
     *
     * @return array $data an array containing the database table data. each array row corresponds to a table row
     */
    public function Read($meta_information) 
    {
        /** If the data to be read is structured */
        if ($meta_information['format'] == 'structured') $data = $this->ReadStructuredData($meta_information);
        /** If the data to be read is unstructured */
        else if ($meta_information['format'] == 'unstructured') {
            $data = $this->ReadUnStructuredData($meta_information);
            /** The structured data is returned */
            $data = $data['structured_data'];
        }
        
        return $data;
    }
    /**
     * Used to read unstructured data from database
     *
     * It reads the data stored as key/value pairs in mysql database table
     *
     * @param array $meta_information the meta information used to fetch the data from database
     *    key_field => string the table key field
     *    form_id => int the form id
     *    fields => string the comma separated list of fields to fetch
     *    data_type => string the data type. it is used to fetch the database table name
     *    condition => array the condition used to fetch the data from database
     *    sort_by => string the field name used to sort the data
     *    order_by => string the sort order  
     *    database_object => object an object of class DatabaseFunctions
     *
     * @return array $data an array containing the data in 2 formats. one is structured table format, the other is raw database format
     *    raw_data => array the raw data
     *    structured_data array the data formatted as structured table
     */
    public function ReadUnStructuredData($meta_information) 
    {
        /** The required form data */
        $data = array();
        /** The form field information is fetched from database */
        $field_information = $this->GetUnstructuredFieldNames($meta_information['form_id']);
        /** The field names */
        $field_names = array();
        /** The raw data */
        $raw_data = array();
        /** The structured data */
        $structured_data = array();
        /** The condition used to fetch the data from database */
        $condition = (isset($meta_information['condition'])) ? $meta_information['condition'] : false;        
        /** All field values are checked */
        for ($count1 = 0;$count1 < count($field_information);$count1++) 
        {
            /** The field id */
            $field_id = $field_information[$count1]['id'];
            /** The field name */
            $field_name = $field_information[$count1]['field_name'];
            /** All values for the field are fetched */
            $field_values = $this->GetUnstructuredFieldValues($field_id);
            /** Each field value is added */
            for ($count2 = 0;$count2 < count($field_values);$count2++) 
            {
                /** The id field is added to the data */
                $structured_data[$count2]["id"] = ($count2 + 1);
                /** The field value is appended to the data */
                $structured_data[$count2][$field_name] = $field_values[$count2]['field_value'];
                /** The updated on field is added to the data */
                $structured_data[$count2]['updated_on'] = $field_values[$count2]['updated_on'];                
                /** If the field value is not included in the raw data, then the raw data is initialized */
                if (!isset($raw_data[$count2]))$raw_data[$count2] = array();
                /** The raw data is updated */
                $raw_data[$count2][$count1] = $field_values[$count2];
            }
        }
        /** The filtered structured data */
        $temp_structured_data = array();
        /** The filtered raw data */
        $temp_raw_data        = array();
        /** Each row is checked */
        for ($count = 0; $count < count($structured_data); $count++) {
            /** Used to indicate that the given conditions match */
            $is_match     = true;
            /** If the condition was given then the data is filtered */
            if ($condition && count(array_keys($condition)) >= 1) {		
	        /** Each condition is checked */
	        foreach ($condition as $field_name => $field_value) {
                    /** If the data does not match the condition then it is not added to the filtered data */
		    if (!isset($structured_data[$count][$field_name]) || (isset($structured_data[$count][$field_name]) && $structured_data[$count][$field_name] != $field_value)) {
		        $is_match = false;
		        break;
		    }
                }                
            }
            /** If the condition matches, then the row is added */
            if ($is_match) {
                $temp_structured_data[] = $structured_data[$count];
                $temp_raw_data[]        = $raw_data[$count];                    
            }
        }
        /** The structured data is set to the filtered structured data */
        $structured_data                    = $temp_structured_data;
        /** The raw data is set to the filtered raw data */
        $raw_data                           = $temp_raw_data;     
        /** If the sort_by and order_by fields are defined, then the data is ordered by those fields */
        if (isset($meta_information['sort_by']) && isset($meta_information['order_by'])) 
        {
            /** The application parameters are fetched */
            $parameters = $this->GetConfig("general", "parameters");
            /** The sort_by field is set */
            $parameters['sort_by'] = $meta_information['sort_by'];
            /** The order_by field is set */
            $parameters['order_by'] = $meta_information['order_by'];
            /** The sort_by and order_by are saved to application configuration */
            $this->SetConfig("general", "parameters", $parameters);
            /** The structured data is sorted */
            usort($structured_data, array(
                $this,
                "SortRows"
            ));
        }

        /** The data to be returned */
        $data = array("raw_data" => $raw_data, "structured_data" => $structured_data);
        
        return $data;
    }
    /**
     * Used to compare 2 rows
     *
     * It compares two rows
     *
     * @param array $row1 the first row
     * @param array $row2 the second row
     *
     * @return int [-1~0~1] $result the result of comparing the 2 rows. -1 is returned if first row is less than the second row
     */
    public function SortRows($row1, $row2) 
    {
        /** The application parameters */
        $parameters = $this->GetConfig("general", "parameters");
        /** The sort by field */
        $sort_by = (isset($parameters['sort_by'])) ? $parameters['sort_by'] : $parameters['key_field'];
        /** The order by field */
        $order_by = (isset($parameters['order_by'])) ? $parameters['order_by'] : $parameters['order_field'];
        /** If the sort by field is not set then the function returns -1 */
        if (!isset($row1[$sort_by]) || !isset($row2[$sort_by])) return -1;
        /** If the field values are both numeric or float */
        if (is_numeric($row1[$sort_by]) || is_float($row1[$sort_by])) {
            if ($row1[$sort_by] < $row2[$sort_by]) $result = -1;
            else if ($row1[$sort_by] > $row2[$sort_by]) $result = 1;
            else if ($row1[$sort_by] == $row2[$sort_by]) $result = 0;            
        }
        /** Otherwise the field values are compared as strings */
        else {
            /** The compare function is called */
            $result = \strcmp($row1[$sort_by], $row2[$sort_by]);
        }
        /** If the order by is ASC */
        if ($order_by == "DESC") $result = $result * -1;
        return $result;
    }
    /**
     * Used to get field values for unstructured data
     *
     * It reads the field values stored as rows in mysql database table
     *
     * @param string field_id the form field id for which the data needs to be fetched
     *
     * @return array $data an array containing the database table data. each array row corresponds to a table row
     */
    public function GetUnstructuredFieldValues($field_id) 
    {
        /** The current application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current database object is fetched */
        $database_object = $this->GetComponent("structureddataui")->GetCurrentDatabaseObject($parameters);
        /** The contents of the application cache are fetched */
        $application_cache = $this->GetComponent("application")->FetchApplicationCache();
        /** The key for the data */
        $data_key = md5("GetUnstructuredFieldValues" . $field_id);
        /** If the data was found in application configuration then it is fetched and returned */
        if (isset($application_cache[$data_key])) {
            /** The unstructured field values */
            $data = $application_cache[$data_key];
            return $data;
        }
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => "id",
            "data_type" => "field_values",
            "database_object" => $database_object
        );
        /** The MysqlDataObject is created */
        $data_object = new MysqlDataObject($meta_information);
        /** The condition used to read the data from database */
        $where_condition = array(
            array(
                "field" => "field_id",
                "value" => $field_id,
                "operator" => "",
                "operation" => "="
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $where_condition,
            "read_all" => true
        );
        /** The Mysql data is read from database */
        $data_object->Read($parameters);
        /** The mysql table data */
        $data = $data_object->GetData();
        
        /** The field value data is added to cache data */
        $cache_data[$data_key] = $data;
        
        /** The application cache is updated */
        $this->GetComponent("application")->UpdateApplicationCache($cache_data);
        
        return $data;
    }
    /**
     * Used to get the total number of rows in the given unstructured database table
     *
     * It fetches the total number of rows for given table
     *
     * @param string form_name the form name
     * @param array condition the condition used to fetch the data from database
     *
     * @return int total_rows the total number of rows in the form
     */
    public function GetUnstructuredTableRowCount($form_name, $condition) 
    {
        /** The contents of the application cache are fetched */
        $application_cache = $this->GetComponent("application")->FetchApplicationCache();
        /** The key for the data */
        $data_key = $this->GetComponent("encryption")->EncodeData("GetUnstructuredTableRowCount" . $form_name . $this->GetComponent("encryption")->EncodeData($condition));
        /** If the data was found in application configuration then it is fetched and returned */
        if (isset($application_cache[$data_key])) {
            /** The unstructured field values */
            $data = $application_cache[$data_key];
            return $data;
        }
        /** The form id is fetched */
        $form_id = $this->GetFormId($form_name);
        /** The field information of the form is fetched */
        $field_information = $this->GetUnstructuredFieldNames($form_id);
        /** The index of the condition field */
        $field_information_index = 0;
        /** The condition field name */
        $condition_field_name = "id";
        /** The condition field value */
        $condition_field_value = "";
        /** If the condition was set */
        if (is_array($condition)) 
        {
            /** The condition field name */
            $condition_field_names = array_keys($condition);
            /** The condition field value */
            $condition_field_values = array_values($condition);
            /** Each field information is checked */
            for ($count = 0;$count < count($field_information);$count++) 
            {
                $field_data = $field_information[$count];
                /** If the field name matches the first condition field name */
                if ($field_data['field_name'] == $condition_field_names[0]) 
                {
                    $field_information_index = $count;
                    $condition_field_name = $field_data['field_name'];
                    $condition_field_value = $condition_field_values[0];
                }
            }
        }
        /** The id of the first field */
        $field_id = $field_information[$field_information_index]['id'];
        /** The current application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current database object is fetched */
        $database_object = $this->GetComponent("structureddataui")->GetCurrentDatabaseObject($parameters);
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => "id",
            "data_type" => "field_values",
            "database_object" => $database_object
        );
        /** The MysqlDataObject is created */
        $data_object = new MysqlDataObject($meta_information);
        /** The condition used to read the data from database */
        $where_condition = array(
            array(
                "field" => "field_id",
                "value" => $field_id,
                "operator" => "",
                "operation" => "="
            )
        );
        /** If the condition field value is set */
        if ($condition_field_value != "") 
        {
            /** The 'AND' operator is added to the where condition */
            $where_condition[0]['operator'] = 'AND';
            $where_condition = array_merge($where_condition, array(
                array(
                    "field" => "field_value",
                    "value" => $condition_field_value,
                    "operator" => "",
                    "operation" => "="
                )
            ));
        }
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "count(*) as total",
            "condition" => $where_condition,
            "read_all" => false
        );
        /** The Mysql data is read from database */
        $data_object->Read($parameters);
        /** The mysql table data */
        $data = $data_object->GetData();
        /** The total number of rows is returned */
        $total_rows = $data['total'];
        
        /** The total rows is set in application cache */
        $cache_data[$data_key] = $total_rows;
        /** The application cache is updated */
        $this->GetComponent("application")->UpdateApplicationCache($cache_data);
        
        return $total_rows;
    }
    /**
     * Used to get the total number of rows in the given structured database table
     *
     * It fetches the total number of rows for given table
     * The table is given by data_type url parameter
     *
     * @param string form_name the form name
     *
     * @return int $total_rows the total number of rows in the form
     */
    public function GetStructuredTableRowCount() 
    {
        /** The current application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current database object is fetched */
        $database_information = $this->GetComponent("structureddataui")->GetDatabaseInformation($parameters);
        /** The contents of the application cache are fetched */
        $application_cache = $this->GetComponent("application")->FetchApplicationCache();
        /** The key for the data */
        $data_key = $this->GetComponent("encryption")->EncodeData("GetStructuredTableRowCount" . $database_information['data_type']);
        /** If the data was found in application configuration then it is fetched and returned */
        if (isset($application_cache[$data_key])) {
            /** The unstructured field names */
            $total_rows = $application_cache[$data_key];
            return $total_rows;
        }
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();       
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => $database_information['key_field'],
            "database_object" => $database_information['database_object'],
            "data_type" => $database_information['data_type']
        );
        /** The MysqlDataObject is created */
        $data_object = new MysqlDataObject($meta_information);
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "count(*) as total",
            "condition" => false,
            "read_all" => false
        );
        /** The Mysql data is read from database */
        $data_object->Read($parameters);
        /** The mysql table data */
        $data = $data_object->GetData();
        /** The total number of rows is returned */
        $total_rows = $data['total'];
        
        /** The total rows is set in application cache */
        $cache_data[$data_key] = $total_rows;
        /** The application cache is updated */
        $this->GetComponent("application")->UpdateApplicationCache($cache_data);
        
        return $total_rows;
    }
    /**
     * Used to get field names for unstructured data
     *
     * It reads the field names stored as rows in mysql database table
     *
     * @param string form_id the id of the form
     *
     * @return array $field_names an array containing the field names for the unstructured data
     */
    public function GetUnstructuredFieldNames($form_id) 
    {
        /** The contents of the application cache are fetched */
        $application_cache = $this->GetComponent("application")->FetchApplicationCache();
        /** The key for the data */
        $data_key = $this->GetComponent("encryption")->EncodeData("GetUnstructuredFieldNames" . $form_id);
        /** If the data was found in application configuration then it is fetched and returned */
        if (isset($application_cache[$data_key])) {
            /** The unstructured field names */
            $field_names = $application_cache[$data_key];
            return $field_names;
        }
        /** The current application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current database object is fetched */
        $database_object = $this->GetComponent("structureddataui")->GetCurrentDatabaseObject($parameters);
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => "id",
            "data_type" => "fields",
            "database_object" => $database_object
        );
        /** The MysqlDataObject is created for the excel object*/
        $data_object = new MysqlDataObject($meta_information);
        /** The condition used to read the data from database */
        $condition = array(
            array(
                "field" => "form_id",
                "value" => $form_id,
                "operator" => "",
                "operation" => "="
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $condition,
            "read_all" => true
        );
        /** The Mysql data is read from database */
        $data_object->Read($parameters);
        /** The mysql table data */
        $field_names = $data_object->GetData();
        /** The field names are added to data key */
        $cache_data[$data_key] = $field_names;
        /** The application cache is updated */
        $this->GetComponent("application")->UpdateApplicationCache($cache_data);
        
        return $field_names;
    }
    /**
     * Used to read structured data from database
     *
     * It reads the data from database table
     *
     * @param array $meta_information the meta information used to fetch the data from database
     *    key_field => string the table key field
     *    fields => string the comma separated list of fields to fetch
     *    data_type => string the data type. it is used to fetch the database table name
     *    condition => array the condition used to fetch the data from database
     *    database_object => object an object of class DatabaseFunctions
     *
     * @return array $data an array containing the database table data. each array row corresponds to a table row
     */
    public function ReadStructuredData($meta_information) 
    {
        /** The condition for fetching the data */
        $condition = $meta_information['condition'];
        /** The comma separated list of fields to fetch */
        $fields = $meta_information['fields'];
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The sort_by field */
        $sort_by = (isset($meta_information['sort_by'])) ? $meta_information['sort_by'] : $meta_information['key_field'];
        /** The order_by field */
        $order_by = (isset($meta_information['order_by'])) ? $meta_information['order_by'] : 'ASC';
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => $meta_information['key_field'],
            "data_type" => $meta_information['data_type'],
            "database_object" => $meta_information['database_object'],
            "sort_by" => $sort_by,
            "order_by" => $order_by          
        );        
        /** The MysqlDataObject is created for the excel object*/
        $data_object = new MysqlDataObject($meta_information);
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => $fields,
            "condition" => $condition,
            "read_all" => true
        );
        /** If the sort by field is given */
        if ($sort_by != "") 
        {
            /** The sort by field */
            $parameters['order'] = array(
                "field" => $sort_by,
                "direction" => $order_by
            );
        }
        /** The Mysql data is read from database */
        $data_object->Read($parameters);
        /** The mysql table data */
        $data = $data_object->GetData();
        
        return $data;
    }
    /**
     * Used to get form id for unstructured data
     *
     * It reads the form data from database using the given form name
     *
     * @param string form_name the name of the form
     *
     * @return int $form_id the id of the form
     */
    public function GetFormId($form_name) 
    {
        /** The contents of the application cache are fetched */
        $application_cache = $this->GetComponent("application")->FetchApplicationCache();
        /** The key for the data */
        $data_key = $this->GetComponent("encryption")->EncodeData("GetFormId" . $form_name);
        /** If the data was found in application configuration then it is fetched and returned */
        if (isset($application_cache[$data_key])) {
            /** The unstructured field names */
            $form_id = $application_cache[$data_key];
            return $form_id;
        }
        /** The current application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current database object is fetched */
        $database_object = $this->GetComponent("structureddataui")->GetCurrentDatabaseObject($parameters);
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => "id",
            "data_type" => "forms",
            "database_object" => $database_object
        );
        /** The MysqlDataObject is created for the excel object*/
        $data_object = new MysqlDataObject($meta_information);
        /** The condition used to read the data from database */
        $condition = array(
            array(
                "field" => "name",
                "value" => $form_name,
                "operator" => "",
                "operation" => "="
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "id",
            "condition" => $condition,
            "read_all" => false
        );
        /** The Mysql data is read from database */
        $data_object->Read($parameters);
        /** The mysql table data */
        $data = $data_object->GetData();
        /** The form id */
        $form_id = $data['id'];
        /** The form id is set in application cache */
        $cache_data[$data_key] = $form_id;
        /** The application cache is updated */
        $this->GetComponent("application")->UpdateApplicationCache($cache_data);
        
        return $form_id;
    }
}
