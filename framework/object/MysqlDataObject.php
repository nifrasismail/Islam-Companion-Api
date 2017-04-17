<?php
namespace Framework\Object;
/**
 * This class implements the MySQL Database Object class
 *
 * Each object of this class represents a single MySQL database object. e.g database row or database table
 * It contains functions that help in constructing MySQL data objects
 *
 * @category   Framework
 * @package    Object
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class MysqlDataObject extends DataObject
{
    /**
     * Used to set the order by field
     *
     * It sets the order by field
     *
     * @param string $table_name name of the MySQL database table
     * @param string $field_name sort field of the MySQL database table
     * @param string $order_by_direction sort direction. e.g ASC or DESC
     */
    final public function SetOrderBy($table_name, $field_name, $order_by_direction) 
    {
        $this->meta_information['order_by_direction'] = $order_by_direction;
        $this->meta_information['order_by'] = $table_name . "." . $field_name;
    }
    /**
     * Used to set the limit options
     *
     * It sets the limit by clause
     *
     * @param int $start the row number of the first row
     * @param int $end the number of rows to fetch
     */
    final public function SetLimit($start, $end) 
    {
        /** The row number of the first row */
        $this->meta_information['limit']['start'] = $start;
        /** The number of rows to fetch */
        $this->meta_information['limit']['end'] = $end;
    }
    /**
     * Used to get the table name
     *
     * It gets the table name
     *
     * @return string $table_name the table name for the object
     */
    final public function GetTableName() 
    {
        $table_name = $this->meta_information['table_name'];
        return $table_name;
    }
    /**
     * Used to get the table field information
     *
     * It gets the field information for the configured table name
     *
     * @return array $field_information the field information for the table
     */
    final public function GetFieldInformation() 
    {
        /** The database object to use */
        $database_object = $this->meta_information['database_object'];
        /** The database object is initialized and cleared */
        $database_object->df_initialize();
        /** The field information is fetched */
        $field_information = $database_object->df_get_field_names($this->meta_information['table_name']);
        return $field_information;
    }
    /**
     * Used to set the table name
     *
     * It sets the table name
     *
     * @param string $table_name name of the MySQL database table from which the data is loaded
     */
    final public function SetTableName($table_name) 
    {
        $this->meta_information['table_name'] = $table_name;
    }
    /**
     * Used to load the data from database to the data property of the object
     *
     * It reads data from database and loads it to the $data property of the object
     * It uses the key field value given as parameter
     * The current object corresponds to a single database row
     *
     * @param mixed $parameters the parameters used to fetched the data. for relational data, it should have following keys:
     *    fields => string list of field names to fetch
     *    condition => mixed the condition used to fetch the data from database
     *                 it can be a single string or integer value. in this case the previously set field name and table name are used
     *                 or an array with following fields: field,value,table,operation and operator
     *    read_all => boolean used to indicate if all data should be returned
     *                in case of non relational data, it can be empty
     *    order => array the order is which the data needs to be sorted
     *        field => string the mysql table field name
     *        type => string [numeric~string]the field type
     *        direction => string [ASC~DESC] the sort direction
     *
     * @return $is_valid used to indicate that data was found in database
     */
    final public function Read($parameters) 
    {
        /** The database object to use is set */
        $database_object = $this->meta_information['database_object'];
        /** The list of fields to fetch */
        $fields = $parameters['fields'];
        /** The condition for fetching the data */
        $condition = $parameters['condition'];
        /** Used to indicate if all data should be fetched */
        $read_all = $parameters['read_all'];
        /** Used to indicate that data was found in database */
        $data_found = true;
        /** The data array is initialized */
        $this->data = array();
        /** The database object is initialized and cleared */
        $database_object->df_initialize();
        /** The select query is built */
        $main_query = array();
        /** If the given field is a string then it is split on comma */
        if (is_string($fields)) 
        {
            $fields = explode(",", $fields);
        }
        /** The given field data is converted to an array */
        if (is_array($fields)) 
        {
            /** The field names are added to the select query */
            for ($count = 0;$count < count($fields);$count++) 
            {
                $main_query[$count]['field'] = trim($fields[$count]);
            }
        }
        /** The where clause used to fetch data from database */
        $where_clause = array();
        /** If the given condition is a string then it should be value of key field */
        if (is_string($condition) || is_int($condition)) 
        {
            $where_clause[0]['field'] = $this->meta_information['key_field'];
            $where_clause[0]['value'] = $condition;
            $where_clause[0]['table'] = $this->meta_information['table_name'];
            $where_clause[0]['operation'] = '=';
            $where_clause[0]['operator'] = '';
        }
        else if (is_array($condition)) 
        {
            /** The updated where clause containing default values */
            $updated_condition = array();
            for ($count = 0;$count < count($condition);$count++) 
            {
                $updated_condition[$count] = $condition[$count];
                /** If the table name is not set then the default table name is set */
                $updated_condition[$count]['table'] = (!isset($condition[$count]['table'])) ? $this->meta_information['table_name'] : $updated_condition[$count]['table'];
            }
            $where_clause = $updated_condition;
        }
        else if (!$condition) 
        {
            $where_clause = '';
            $database_object->df_set_table_name($this->meta_information['table_name']);
        }
        /** If the order by field is given in meta data then the data is sorted by this field */
        if (isset($this->meta_information['order_by'])) 
        {
            list($order_by_table_name, $order_by) = explode(".", $this->meta_information['order_by']);
            $database_object->df_set_order_by($order_by_table_name, $order_by, $this->meta_information['order_by_direction']);
        }
        /** If the order by field is given in the query parameters and it is an array, then the data is sorted by this field */
        else if (isset($parameters['order']) && is_array($parameters['order'])) 
        {
            /** The table name used in order by */
            $order_by_table_name = (isset($parameters['order']['table_name'])) ? $parameters['order']['table_name'] : $this->meta_information['table_name'];
            /** The order by field */
            $order_by = $parameters['order']['field'];
            /** The order by direction */
            $order_by_direction = $parameters['order']['direction'];
            /** The sort order is set */
            $database_object->df_set_order_by($order_by_table_name, $order_by, $order_by_direction);
        }
        /** If the order by is set to the string 'random' */
        else if (isset($parameters['order']) && $parameters['order'] == "random")
        {
            $database_object->df_set_order_by("", "", "RAND()");
        }
        /** If the limit is given then the data is limited */
        if (isset($this->meta_information['limit']) && $this->meta_information['limit']['end'] > 0) 
        {
            $database_object->df_set_limits($this->meta_information['limit']['start'], $this->meta_information['limit']['end']);
        }
        /** The data is fetched from database */
        $query = $database_object->df_build_query($main_query, $where_clause, 's');

        $db_rows = $database_object->df_all_rows($query);
        /** If no data was returned by select query then function returns false */
        if (!isset($db_rows[0])) $data_found = false;
        if ($data_found) 
        {
            if (!$read_all) 
            {
                /** The checksum of all field values */
                $checksum = "";
                /** The combined value of all the fields */
                $combined_values = "";
                foreach ($db_rows[0] as $field_name => $field_value) 
                {
                    /** The data value is added to the data property */
                    $this->data[$field_name] = $field_value;
                    /** If the checksum needs to be calculated */
                    if (isset($this->data['checksum']) && $this->meta_information['validate_checksum']) 
                    {
                        /** The value is combined if the field name is not equal to checksum */
                        if ($field_name != "checksum") 
                        {
                            $combined_values = $combined_values . $field_value;
                        }
                    }
                }
                /** If the checksum needs to be calculated */
                if (isset($this->data['checksum']) && $this->meta_information['validate_checksum']) 
                {
                    /** If the checksum of the combined values is not equal to the checksum field value then an exception is thrown */
                    if (md5($combined_values) != $this->data['checksum']) throw new \Exception("Checksum field of data does not match calculated checksum");
                }
            }
            else
            {
                /** The data value is added to the data property */
                $this->data = $db_rows;
                /** If the checksum needs to be calculated */
                if ($this->meta_information['validate_checksum']) 
                {
                    /** Each data row is checked */
                    for ($count = 0;$count < count($this->data);$count++) 
                    {
                        /** The checksum of all field values */
                        $checksum = "";
                        /** The combined value of all the fields */
                        $combined_values = "";
                        /** A single data row */
                        $db_row = $this->data[$count];
                        /** The db row is sorted */
                        ksort($db_row, SORT_STRING);
                        /** If the checksum needs to be calculated */
                        if (isset($db_row['checksum'])) 
                        {
                            foreach ($db_row as $field_name => $field_value) 
                            {
                                /** The value is combined if the field name is not equal to checksum */
                                if ($field_name != "checksum") 
                                {
                                    $combined_values = $combined_values . base64_encode($field_value);
                                }
                            }
                            /** If the checksum of the combined values is not equal to the checksum field value then an exception is thrown */
                            if (md5($combined_values) != $db_row['checksum']) 
                            {
                                //echo md5($combined_values)."<br/>".$db_row['checksum'];
                                throw new \Exception("Checksum field of data does not match calculated checksum");
                            }
                        }
                    }
                }
            }
        }
        return $data_found;
    }
    /**
     * Used to delete the object data
     *
     * It deletes data from database
     *
     * @throws object Exception an exception is thrown if the object is read only
     * @throws object Exception an exception is thrown if the object could not be deleted
     */
    final public function Delete() 
    {
        /** The database object to use is set */
        $database_object = $this->meta_information['database_object'];
        /** If the current object is set to read only then an exception is thrown */
        if ($this->meta_information['readonly']) throw new \Exception("Cannot delete readonly object.");
        /** The database object is initialized and cleared */
        $database_object->df_initialize();
        /** The where clause of the database query is created */
        $counter = 0;
        $where_clause = array();
        foreach ($this->data as $field_name => $field_value) 
        {
            $where_clause[$counter]['field'] = $field_name;
            $where_clause[$counter]['value'] = $field_value;
            $where_clause[$counter]['table'] = $this->meta_information['table_name'];
            $where_clause[$counter]['operation'] = '=';
            $where_clause[$counter]['operator'] = 'AND';
            $counter++;
        }
        /** If a where clause item was given */
        if ($counter > 0) $where_clause[$counter - 1]['operator'] = '';
        else $where_clause = false;
               
        /** The database query is built */
        $query = $database_object->df_build_query(array() , $where_clause, 'd');
        /** The database query is executed. An exception is thrown if the data could not be deleted */
        if (!$database_object->df_execute($query)) throw new \Exception("Data could not be deleted");
    }
    /**
     * Used to indicate if the record already exists in database
     *
     * It checks if the key field of the record already exists in database
     * If it does then the function returns true
     * Otherwise it returns false
     *
     * @return boolean $record_exists it is true if the record already exists. it is false otherwise
     */
    final public function RecordExists() 
    {
        /** The database object to use is set */
        $database_object = $this->meta_information['database_object'];
        /** The database object is initialized and cleared */
        $database_object->df_initialize();
        $main_query = array();
        $main_query[0]['field'] = $this->meta_information['key_field'];
        $main_query[0]['table'] = $this->meta_information['table_name'];
        $counter = 0;
        $where_clause = array();
        foreach ($this->data as $field_name => $field_value) 
        {
            $where_clause[$counter]['field'] = $field_name;
            $where_clause[$counter]['value'] = $field_value;
            $where_clause[$counter]['table'] = $this->meta_information['table_name'];
            $where_clause[$counter]['operation'] = '=';
            $where_clause[$counter]['operator'] = 'AND';
            $counter++;
        }
        if ($counter > 0) 
        {
            $where_clause[$counter - 1]['operator'] = '';
        }
        $query = $database_object->df_build_query($main_query, $where_clause, 's');
        $db_rows = $database_object->df_all_rows($query);
        if (isset($db_rows[0][$this->meta_information['key_field']])) $record_exists = true;
        else $record_exists = false;
        return $record_exists;
    }
    /**
     * Used to save the object data
     *
     * It saves the object data to database. If the key field of the data contains a value
     * Then the data is updated. Otherwise it is added
     *
     * @throws object Exception an exception is thrown if the object is read only
     *
     * @return int $record_id the value of the key field of the saved row
     */
    final public function Save() 
    {
        /** If the current object is set to read only then an exception is thrown */
        if ($this->meta_information['readonly']) throw new \Exception("Cannot save readonly object.");
        /** The database object to use is set */
        $database_object = $this->meta_information['database_object'];
        /** The $record_id variable is initialized */
        $record_id = '-1';
        /** The database object is initialized and cleared */
        $database_object->df_initialize();
        /** If the $data contains key field information then it is updated */
        if (isset($this->data[$this->meta_information['key_field']])) 
        {
            /** The update query fields are added to the database object */
            foreach ($this->data as $field_name => $field_value) {            
                $database_object->df_add_update_field("`" . $field_name . "`", $this->meta_information['table_name'], $field_value, true);
            }
            /** The where clause of the update query is set */
            $database_object->df_build_where_clause($this->meta_information['key_field'], $this->data[$this->meta_information['key_field']], true, $this->meta_information['table_name'], '=', '', '');
            /** The update query is fetched */
            $query_str = $database_object->df_get_query_string('u');
            /** The update query is run */
            $database_object->df_execute($query_str);
            /** The key field value for the data */
            $record_id = $this->data[$this->meta_information['key_field']];
        }
        /** If the $data does not contain key field information then it is added */
        else 
        {
            /** The insert query fields are added to the database object */
            foreach ($this->data as $field_name => $field_value) $database_object->df_build_insert_query($field_name, $field_value, true, $this->meta_information['table_name']);
            /** The insert query is fetched */
            $query_str = $database_object->df_get_query_string('i');
            /** The insert query is run */
            $database_object->df_execute($query_str);
            /** The id of the last added record */
            $record_id = $database_object->df_last_insert_id();
        }
        return $record_id;
    }
    /**
     * Used to return the data table for the given data type
     *
     * It returns the data table name from application configuration
     *
     * @param string $data_type the type of the data
     *
     * @return string $data_table_name name of the MySQL table for the given data type
     */
    final public function GetDatabaseTableName($data_type) 
    {
        /** The data table names are fetched from application configuration */
        $mysql_table_names = $this->GetConfig("general", "mysql_table_names");
        /** If the data type is not registered with the application framework then it is used as the mysql table */
        $data_table_name = (isset($mysql_table_names[$data_type])) ? $mysql_table_names[$data_type] : $data_type;
        /** The table name is encloded in '`' character */
        $data_table_name = "`" . $data_table_name . "`";
        
        return $data_table_name;
    }
    /**
     * Used to set the meta information for the given data type
     *
     * It sets the table name and field name for the given data type
     *
     * @param mixed $meta_information the meta information to set
     * it is an array with following keys:
     * data_type => string the type of the data. e.g author
     * key_field => string the name of field to be used in the search
     * validate_checksum => boolean optional indicates if the checksum of the data should be validated
     * database_object => object optional the database object to use. it is an object of type DatabaseFunctions
     */
    final public function SetMetaInformation($meta_information) 
    {
        /** The database object to use is set. If it is not set in the meta information, then it is fetched from application configuration */
        $this->meta_information['database_object'] = (isset($meta_information['database_object'])) ? $meta_information['database_object'] : $this->GetComponent("database");
        /** It indicates that checksum of data needs to be validated */
        $this->meta_information['validate_checksum'] = (isset($meta_information['validate_checksum'])) ? $meta_information['validate_checksum'] : false;
        if (isset($meta_information['data_type'])) 
        {
            /** The database table name for the data type */
            $data_type_table = $this->GetDatabaseTableName($meta_information['data_type']);
            /** The table name for the current object is set */
            $this->SetTableName($data_type_table);
        }
        if (isset($meta_information['key_field'])) 
        {
            /** The field name for the current object is set */
            $this->SetKeyField($meta_information['key_field']);
        }
        if (isset($meta_information['readonly'])) 
        {
            /** The readonly property for the current object is set */
            $this->SetReadonly($meta_information['readonly']);
        }
        if (isset($meta_information['limit'])) 
        {
            /** The limit property for the current object is set */
            $this->SetLimit($meta_information['limit']['start'], $meta_information['limit']['end']);
        }
    }
}

