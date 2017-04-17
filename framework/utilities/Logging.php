<?php
namespace Framework\Utilities;
/**
 * Singleton class
 * Logging class provides functions related to logging
 *
 * It includes functions for logging data to database, email or web hooks
 *
 * @category   Framework
 * @package    Utilities;
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.1
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class Logging
{
    /**
     * The single static instance
     */
    protected static $instance;
    /**
     * The information needed for logging the data
     * For example email address, log file name, database object, web hook url
     */
    private $logging_information;
    /**
     * The data that needs to be logged
     */
    private $logging_data;
    /**
     * The logging destination
     * Currently following log destinations are supported: email, file, database and web hook
     */
    private $logging_destination;
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists
     * If it does not exist then it is created
     * The instance is returned
     *
     * @return Utilities static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance() 
    {
        if (static ::$instance == null) 
        {
            static ::$instance = new static();
        }
        return static ::$instance;
    }
    /**
     * Main logging function
     * It is used to log the given data
     *
     * It logs the data to the given destination
     *
     * @param array $parameters it contains the data for the logging class object
     * logging_information => array the information needed for logging the data. e.g email address or database information
     * logging_data => array the data that needs to be logged. it should be an associative array with depth of 1
     * logging_destination => string [email~file~database~web hook] the logging destination
     */
    public function SaveLogData($parameters) 
    {
        /** The information needed for logging the data	*/
        $this->logging_information = $parameters['logging_information'];
        /** The data that needs to be logged */
        $this->logging_data = $parameters['logging_data'];
        /** The logging destination */
        $this->logging_destination = $parameters['logging_destination'];
        /** If the logging destination is database */
        if ($this->logging_destination == "database") 
        {
            $this->SaveLogDataToDatabase($parameters['logging_information'], $parameters['logging_data']);
        }
    }
    /**
     * Saves the log data to database
     *
     * It saves the given log data to database
     * The log data must be an associative array with depth of 1 level
     *
     * @param array $logging_information the information needed for logging the data. e.g email address or database information
     *    database_object => the database object. it is an object of type MysqlDataObject
     *    table_name => string the name of the database table where the log data will be stored
     * @param array $logging_data the data that needs to be logged. it should be an associative array with depth of 1
     */
    private function SaveLogDataToDatabase($logging_information, $logging_data) 
    {
        /** If the test data already exists, then the function returns */
        if ($this->LogDataExists($logging_information, $logging_data)) return;
        /** The DatabaseFunctions object is created */
        $database = $logging_information['database_object'];
        /** The database object is initialized */
        $database->df_initialize();
        /** The database table name */
        $table_name = $logging_information['table_name'];
        /** The table name is set */
        $database->df_set_table($table_name);
        /** The fields to add */
        $main_query = array();
        /** The field counter */
        $field_counter = 0;
        /** Each field is added */
        foreach ($logging_data as $field_name => $field_value) 
        {
            /** The field name is added to the main insert query */
            $main_query[$field_counter]['field'] = $field_name;
            /** The field value is added to the main insert query */
            $main_query[$field_counter]['value'] = $field_value;
            /** The field counter is increased */
            $field_counter++;
        }
        /** The database query is fetched */
        $query = $database->df_build_query($main_query, array() , 'i');

        /** The database query is run */
        $database->df_execute($query);
    }
    /**
     * Used to check if the given log data already exists in database
     *
     * It checks if the log data to be saved already exists in database
     * If the data already exists then the function returns true
     *
     * @param array $logging_information the information needed for logging the data. e.g email address or database information
     *    database_object => the database object. it is an object of type MysqlDataObject
     *    table_name => string the name of the database table where the log data will be stored
     * @param array $logging_data the data that needs to be logged. it should be an associative array with depth of 1
     *
     * @return boolean $data_exists indicates if the test data already exists in database
     */
    private function LogDataExists($logging_information, $logging_data) 
    {
        /** The DatabaseFunctions object is fetched */
        $database = $logging_information['database_object'];
        /** The database object is initialized */
        $database->df_initialize();
        /** The database table name */
        $table_name = $logging_information['table_name'];
        /** The table name is set */
        $database->df_set_table($table_name);
        /** The fields to display */
        $main_query = array();
        /** The where clause */
        $where_clause = array();
        /** The field name to fetch. All fields are fetched */
        $main_query[0]['field'] = "*";
        /** The field counter */
        $field_counter = 0;
        /** Each field is added to the where clause */
        foreach ($logging_data as $field_name => $field_value) 
        {
            /** The created_on field is ignored since the test data could have been saved earlier */
            if ($field_name == "created_on") continue;
            /** The field name is added to the where clause */
            $where_clause[$field_counter]['field'] = $field_name;
            /** The field value is added to the where clause */
            $where_clause[$field_counter]['value'] = $field_value;
            /** The operation is added to the where clause */
            $where_clause[$field_counter]['operation'] = "=";
            /** The operator is added to the where clause */
            $where_clause[$field_counter]['operator'] = "AND";
            /** The field counter is increased */
            $field_counter++;
        }
        /** The operator value in the last where clause is set to empty */
        $where_clause[$field_counter - 1]['operator'] = "";
        /** The database query is fetched */
        $query = $database->df_build_query($main_query, $where_clause, 's');     
        /** All rows are fetched from database */
        $all_rows = $database->df_all_rows($query);
        /** Indicates if the test data already exists in database */
        $data_exists = (count($all_rows) > 0) ? true : false;
        return $data_exists;
    }
    /**    
     * It is used to fetch log data from database
     *
     * It reads log data from database and returns the data
     *
     * @param array $logging_information the information needed for logging the data. e.g email address or database information
     *    database_object => the database object. it is an object of type MysqlDataObject
     *    table_name => the name of the log table
     * @param array $parameters it contains the information used to fetch the log data from database
     * it contains sub arrays. each sub array contains the field name and field value
     *
     * @return array $log_data the log data
     */
    public function FetchLogDataFromDatabase($logging_information, $parameters) 
    {
        /** The DatabaseFunctions object is fetched */
        $database = $logging_information['database_object'];
        /** The database object is initialized */
        $database->df_initialize();
        /** The database table name */
        $table_name = $logging_information['table_name'];
        /** The table name is set */
        $database->df_set_table($table_name);
        /** The fields to display */
        $main_query = array();
        /** The where clause */
        $where_clause = array();
        /** The field name to fetch. All fields are fetched */
        $main_query[0]['field'] = "*";
        /** Each field is added to the where clause */
        for ($count = 0;$count < count($parameters);$count++) 
        {
            /** The log table field information */
            $field_information = $parameters[$count];
            /** The field name is added to the where clause */
            $where_clause[$count]['field'] = $field_information['field_name'];
            /** The field value is added to the where clause */
            $where_clause[$count]['value'] = $field_information['field_value'];
            /** The operation is added to the where clause */
            $where_clause[$count]['operation'] = "=";
            /** The operator is added to the where clause */
            $where_clause[$count]['operator'] = "AND";
        }
        /** The operator value in the last where clause is set to empty */
        if ($count > 0) $where_clause[$count - 1]['operator'] = "";
        else $where_clause = false;
        
        /** The database query is fetched */
        $query = $database->df_build_query($main_query, $where_clause, 's');
        /** All rows are fetched from database */
        $log_data = $database->df_all_rows($query);
        return $log_data;
    }
    /**
     * It is used to clear the log data from database
     *
     * It removes the log files from database using the given field information
     *
     * @param array $logging_information the information needed for logging the data
     *    database_object => the database object. it is an object of type MysqlDataObject
     *    table_name => the name of the log table
     * @param array $parameters it contains the information used to fetch the log data from database
     * it contains sub arrays. each sub array contains the field name and field value
     */
    public function ClearLogDataFromDatabase($logging_information, $parameters) 
    {
        /** The DatabaseFunctions object is fetched */
        $database = $logging_information['database_object'];
        /** The database object is initialized */
        $database->df_initialize();
        /** The database table name */
        $table_name = $logging_information['table_name'];
        /** The table name is set */
        $database->df_set_table($table_name);
        /** The fields to display */
        $main_query = array();
        /** The where clause */
        $where_clause = array();
        /** The field name to fetch. All fields are fetched */
        $main_query[0]['field'] = "*";
        /** Each field is added to the where clause */
        for ($count = 0;$count < count($parameters);$count++) 
        {
            /** The log data */
            $log_data = $parameters[$count];
            /** The field name is added to the where clause */
            $where_clause[$count]['field'] = $log_data['field_name'];
            /** The field value is added to the where clause */
            $where_clause[$count]['value'] = $log_data['field_value'];
            /** The operation is added to the where clause */
            $where_clause[$count]['operation'] = "=";
            /** The operator is added to the where clause */
            $where_clause[$count]['operator'] = "AND";
        }
        /** The operator value in the last where clause is set to empty */
        $where_clause[$count - 1]['operator'] = "";
        /** The database query is fetched */
        $query = $database->df_build_query($main_query, $where_clause, 's');
        /** All rows are fetched from database */
        $log_data = $database->df_all_rows($query);
        /** The test data is deleted from database */
        for ($count = 0;$count < count($log_data);$count++) 
        {
            /** The test data item */
            $test_data_item = $log_data[$count];
            /** The where clause */
            $where_clause = array();
            /** The field counter in initialized */
            $field_counter = 0;
            /** The database object is initialized */
            $database->df_initialize();
            /** The table name is set */
            $database->df_set_table($table_name);
            /** Each test data item field is added to delete query */
            foreach ($test_data_item as $field_name => $field_value) 
            {
                /** The created_on field is ignored since the test data could have been saved earlier */
                if ($field_name == "created_on") continue;
                /** The field name is added to the where clause */
                $where_clause[$field_counter]['field'] = $field_name;
                /** The field value is added to the where clause */
                $where_clause[$field_counter]['value'] = $field_value;
                /** The operation is added to the where clause */
                $where_clause[$field_counter]['operation'] = "=";
                /** The operator is added to the where clause */
                $where_clause[$field_counter]['operator'] = "AND";
                /** The field counter is increased */
                $field_counter = ($field_counter + 1);
            }
            /** The operator value in the last where clause is set to empty */
            $where_clause[$field_counter - 1]['operator'] = "";
            /** The data is deleted from database */
            $query = $database->df_build_query(array() , $where_clause, 'd');
            /** The database query is run */
            $database->df_execute($query);
        }
    }
}

