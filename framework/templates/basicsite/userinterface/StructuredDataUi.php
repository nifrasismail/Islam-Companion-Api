<?php
namespace Framework\Templates\BasicSite\UserInterface;
use \Framework\Configuration\Base as Base;
use \Framework\Templates\BasicSite\UserInterface\ListPageInterface as ListPageInterface;
use \Framework\Object\MysqlDataObject as MysqlDataObject;
/**
 * This class implements the StructuredDataUi class
 * It extends the Base class
 *
 * It provides functions for building form and list pages
 * The pages are built from structured data stored in mysql database
 *
 * @category   Framework
 * @package    UserInterface
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class StructuredDataUi extends UiData
{
    /**
     * Used to return the parameters for rendering the input button
     *
     * It returns the parameters used to render the input button
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $input_button_parameters the parameters used to render the input button
     *    title => string input button title
     *    parameters => array the parameters for the input button url
     *    onclick => string the onclick button event
     */
    public function GetInputButtonParameters($parameters) 
    {
        /** The format field is added to input button parameters */
        $parameters['format'] = "structured";
        /** The input button parameters are fetched */
        $input_button_parameters = parent::GetInputButtonParameters($parameters);
        return $input_button_parameters;
    }
    /**
     * Used to return the presentation data for the list page
     *
     * It returns the presentation data for the list page
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $presentation_data the presentation data for the list page
     *    header_widths => array the width of each list page header
     *    table_headers => array the list page table headers
     *    column_css_class => array the css class for each table column
     */
    public function GetPresentationData($parameters) 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The current database object is fetched */
        $database_object = $this->GetCurrentDatabaseObject($parameters);        
        /** The key field for the data */
        $key_field = (isset($parameters['key_field'])) ? $parameters['key_field'] : 'id';
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => $key_field,
            "database_object" => $database_object,
            "data_type" => $parameters['data_type']
        );
        /** The MysqlDataObject is created for the excel object*/
        $data_object = new MysqlDataObject($meta_information);
        /** The field information is fetched */
        $field_information = $data_object->GetFieldInformation();
        /** The number of fields */
        $field_count = count($field_information);
        /** The field names */
        $field_names = array();
        /** Each field is added to presentation data */
        for ($count = 0;$count < $field_count;$count++) 
        {
            /** The list page table headers */
            $field_names[$count]['field_name'] = $field_information[$count]['Field'];
        }
        /** The presentation data is fetched */
        $presentation_data = parent::GetPresentationData($field_names);
        
        return $presentation_data;
    }
    /**
     * Used to return the database data for the list page
     *
     * It returns the database data for the list page
     *
     * @param array $parameters the parameters for the current application request
     *
     * @return array $database_data the database data for the list page
     *    key_field => string the key field for the database
     *    data_type => string the short table name for the data
     *    fields => array the list of database fields
     *    condition => array the condition used to fetch the data from database
     *    format => string [structured~unstructured] the format of the data. structured implies usual mysql table data
     *              unstructured implies the field names are stored in table rows
     *    database_object => object an object of type DatabaseFunctions
     */
    public function GetDatabaseInformation($parameters) 
    {
        /** The database data */
        $database_data = array(
            "key_field" => "",
            "data_type" => $parameters['data_type'],
            "condition" => false,
            "fields" => array()
        );
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The current database object is fetched */
        $database_object = $this->GetCurrentDatabaseObject($parameters);
        /** The parameters for the data object */
        $meta_information = array(
            "database_object" => $database_object,
            "configuration" => $configuration,
            "key_field" => "id",
            "data_type" => $parameters['data_type']
        );
        /** The MysqlDataObject is created for the excel object*/
        $data_object = new MysqlDataObject($meta_information);
        /** The field information is fetched */
        $field_information = $data_object->GetFieldInformation();
        /** The number of fields */
        $field_count = count($field_information);
        /** Each field is added to presentation data */
        for ($count = 0;$count < $field_count;$count++) 
        {
            /** If the field is a primary field then it is added to the database data */
            if (strtolower($field_information[$count]['Key']) == 'pri') $database_data['key_field'] = $field_information[$count]['Field'];
            /** The field is added to the database field list */
            $database_data['fields'][] = $field_information[$count]['Field'];
        }
        /** The data format is set to structured by default. It can be overriden by child classes */
        $database_data['format'] = "structured";
        /** The database name */
        $database_data['database_object'] = $database_object;
        /** The current application parameters */
        $parameters = $this->GetConfig("general", "parameters");
        /** The key field is set in application configuration, so it can be used in other functions */
        $parameters["key_field"] = $database_data['key_field'];

        /** The application parameters are updated */
        $this->SetConfig("general", "parameters", $parameters);
        return $database_data;
    }
    /**
     * Used to get the current database object
     *
     * It returns a database object
     * If the database_name parameter is given in the url
     * Then a new database object is created
     * Otherwise the current database object is returned
     *
     * @param array $parameters the parameters for the current application request
     *
     * @return $database_object object an object of class DatabaseFunctions is returned
     */
    public function GetCurrentDatabaseObject($parameters) 
    {
        /** The contents of the application cache are fetched */
        $application_cache = $this->GetComponent("application")->FetchApplicationCache();
        /** The encoded application parameters */
        $encoded_parameters = $this->GetComponent("encryption")->EncodeData($parameters);
        /** The key for the data */
        $data_key = $this->GetComponent("encryption")->EncodeData("GetCurrentDatabaseObject" . $encoded_parameters);
        /** If the data was found in application configuration then it is fetched and returned */
        if (isset($application_cache[$data_key])) {
            /** The database object */
            $database_object = $application_cache[$data_key];
            return $database_object;
        }
        /** If the database_name parameters is not set in url */
        if (!isset($parameters['database_name'])) 
        {
            $database_object = $this->GetComponent("database");
        }
        /** If the database_name parameter is given in url, then it is used to create a new DatabaseFunctions classs object */
        else 
        {
            /** The database name is set */
            $database_name = $parameters['database_name'];
            /** The database name is removed from application parameters */
            unset($parameters['database_name']);
            /** The application parameters are updated */
            $this->SetConfig("general", "parameters", $parameters);
            /** The list of all databases is fetched */
            $database_names = $this->GetComponent("unstructureddataui")->GetFormData("databases", "*", false);
            /** The database name is set in application configuration */
            $parameters['database_name'] = $database_name;
            /** The application parameters are updated */
            $this->SetConfig("general", "parameters", $parameters);
            /** The list if database names is set to the structured data */
            $database_names = $database_names['structured_data'];
            /** The database information for the given database */
            $database_information = array();
            /** Each database name is checked */
            for ($count = 0;$count < count($database_names);$count++) 
            {
                $database_information = $database_names[$count];
                /** If the database information is for the given database */
                if ($database_information['database'] == $parameters['database_name']) break;
            }
            /** If the database information could not be found, then an exception is thrown */
            if (!is_array($database_information)) throw new \Exception("Invalid database name: " . $parameters['database_name']);
            /** If the database name is valid, then a database object is created */
            else 
            {
                /** The application configuration is fetched */
                $configuration = $this->GetConfigurationObject();
                /** The database charset is set */
                $database_information['charset'] = "utf8";
                /** The debug parameter is set */
                $database_information['debug'] = "1";
                /** The external database object is created */
                $database_object = $configuration->InitializeObject("externaldatabase", $database_information);
            }
        }
        /** The database object is set in application cache */
        $cache_data[$data_key] = $database_object;
        /** The application cache is updated */
        $this->GetComponent("application")->UpdateApplicationCache($cache_data);
        
        return $database_object;
    }
    /**
     * Used to get the table row data
     *
     * It fetches the table row data for the given row id
     *
     * @param array $data the data needed for fetching the table rows
     *    all_rows => boolean used to indicate if all table rows should be fetched
     *    key_field => string name of the field used to fetch the table data
     *    data_type => string the short name of the table that contains the table data
     *    key_field_value => string the value of the key field
     */
    public function GetRowData($data) 
    {
        /** The application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current database object is fetched */
        $database_object = $this->GetCurrentDatabaseObject($parameters);
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "database_object" => $database_object,
            "key_field" => $data['key_field'],
            "data_type" => $data['data_type']
        );
        /** Mysqldataobject is created */
        $data_object = new MysqlDataObject($meta_information);
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "read_all" => $data['all_rows']
        );
        /** The condition is added if all_rows is set to false */
        $parameters["condition"] = (!$data['all_rows']) ? $data['key_field_value'] : false;
        /** The table row data is read from database */
        $data_object->Read($parameters);
        /** The table row data is fetched */
        $row_data = $data_object->GetData();
        return $row_data;
    }
    /**
     * Used to update the table row data before it is displayed
     * By default the function replaces the table row id value with row number
     * It should be overriden by a child class
     *
     * @param array $data the row data to be filtered
     * @param int $row_number the row number
     *
     * @return array $data the updated row data
     */
    public function FilterRowData($data, $row_number) 
    {
        /** The parent function is called */
        $data = parent::FilterRowData($data, $row_number);
        return $data;
    }
    /**
     * Used to filter table rows
     *
     * It removes rows from the table that do not match some criteria
     *
     * @param array $table_rows the table data
     *
     * @return array $updated_table_rows the table data
     */
    public function FilterTableData($table_rows) 
    {
        /** The updated table rows */
        $updated_table_rows = array();
        /** The application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The parent field name */
        $parent_field_name = (isset($parameters['parent_field_name'])) ? $parameters['parent_field_name'] : '-1';
        /** The table row data is updated */
        $table_rows = array(
            "table_rows" => $table_rows,
            "parent_field_name" => $parent_field_name
        );
        /** The parent function is called */
        $updated_table_rows = parent::FilterTableData($table_rows);
        return $updated_table_rows;
    }
    /**
     * It is used to save form data
     *
     * It fetches the form data from application parameters
     * It saves the form data to database
     *
     * @param array $form_data the data submitted by the form
     * @param array $parameters the application parameters
     *
     * @return $is_saved boolean indicates if the data was successfully saved
     */
    public function SaveFormData($form_data, $parameters) 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The current database object is fetched */
        $database_object = $this->GetCurrentDatabaseObject($parameters);
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "database_object" => $database_object,
            "key_field" => "id",
            "data_type" => $parameters['data_type']
        );
        /** Mysqldataobject is created */
        $data_object = new MysqlDataObject($meta_information);
        /** The data is set to the mysqldataobject */
        $data_object->SetData($form_data);
        /** If the form is in add mode, then the database is checked for existing record */
        if ($parameters['mode'] == 'add') 
        {
            /** If the record already exists in database, then the function returns -1 */
            if ($data_object->RecordExists()) 
            {
                $is_saved = - 1;
                return $is_saved;
            }
        }
        /** The Mysqldataobject is set to read/write */
        $data_object->SetReadonly(false);
        /** The form data is saved to database */
        $is_saved = ($data_object->Save()) ? true : false;
        return $is_saved;
    }
    /**
     * Used to delete data from database
     *
     * It deletes the given data
     * {@internal context browser,command line}
     *
     * @param array $parameters the application parameters
     *
     * @return string $redirect_url the url at which the user is redirected
     */
    public function DeleteData($parameters) 
    {
        /** The current database object is fetched */
        $database_object = $this->GetCurrentDatabaseObject($parameters);
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "database_object" => $database_object,
            "key_field" => "id",
            "data_type" => $parameters['data_type'],
            "readonly" => false
        );
        /** Mysqldataobject is created */
        $data_object = new MysqlDataObject($meta_information);
        /** The parameters used to read the data from database */
        $db_parameters = array(
            "fields" => "*",
            "condition" => $parameters['id'],
            "read_all" => false
        );
        /** The table row data is read from database */
        $data_object->Read($db_parameters);
        /** The table row data is deleted */
        $data_object->Delete();
        /** The redirect url */
        $redirect_url = $parameters['redirect_url'];
        return $redirect_url;
    }
}

