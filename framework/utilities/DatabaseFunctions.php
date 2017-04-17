<?php
namespace Framework\Utilities;
/**
 * Build and execute MySQL database queries
 *
 * This class is a wrapper around mysqli functions
 * It is specially usefully for executing very long insert and update queries
 * It can be used to generate select,insert,update and delete queries
 *
 * @category   Framework
 * @package    Utilities
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.3.1
 */
final class DatabaseFunctions
{
    /**
     * Used to indicate start of sub where clause
     */
    const START_SUB_WHERE = 100;
    /**
     * Used to indicate end of sub where clause
     */
    const END_SUB_WHERE = 101;
    /**
     * MySQL Database connection resource
     */
    private $Id;
    /**
     * Used to indicate the query debug. i.e 0=silent, 1=normal, 2=debug, 3=trace
     */
    private $debug;
    /**
     * The complete MySQL query
     */
    private $query = '';
    /**
     * The complete MySQL where clause
     */
    private $where_clause = '';
    /**
     * The type of the MySQL query. u=>update,s=>select,i=>insert,d=>delete
     */
    private $query_type = '';
    /**
     * The list of MySQL tables used in the query
     */
    private $table_list = array();
    /**
     * The list of field names used in the MySQL query
     */
    private $field_list = array();
    /**
     * The list of field values used in the MySQL query
     */
    private $value_list = array();
    /**
     * The list of display fields used in the MySQL query
     */
    private $display_fields = array();
    /**
     * The start value in LIMIT clause of MySQL query
     */
    private $start = 0;
    /**
     * The end value in LIMIT clause of MySQL query
     */
    private $end = 0;
    /**
     * The MySQL query log information
     */
    private $query_log = array();
    /**
     * The sort field used in ORDER BY clause of MySQL
     */
    private $sort_by = "";
    /**
     * The order by field used in ORDER BY clause of MySQL
     */
    private $order_by = "";
    /**
     * The group by field used in GROUP BY clause of MySQL
     */
    private $group_by = "";
    /**
     * Used to indicate if the current application is being run from a browser
     */
    private $is_browser_application = true;
    /**
     * Default table name to be used in MySQL queries
     */
    private $table_name = "";
    /**
     * Class constructor
     *
     * Initializes object variables. connects to database server
     * Calls the relevant constructor function depending on the parameters passed
     *
     * @param array $parameters database server connection information contains following keys:
     * host => database server host
     * user => database user
     * password => database
     * database => name of the database
     * debug => 0 implies no debugging. 1 implies logging sql queries. 2 implies logging query execution time
     * charset => the charset for database connection
     * @throws Exception object if database connection could not be established or an error occured
     */
    function __construct() 
    {
        $a = func_get_args();
        $i = func_num_args();
        if ($i == 1) $this->DatabaseFunctionsFactory($a[0]);
    }
    /**
     * Class constructor
     *
     * Initializes object variables. connects to database server
     *
     * @param array $parameters database server connection information OR
     * @throws Exception object if database connection could not be established or an error occured
     */
    public function DatabaseFunctionsFactory($parameters) 
    {
        $this->id = 0;
        /** The object variables are cleared */
        $this->df_initialize();
        /** Used to determine if application is running from browser or command line */
        $this->is_browser_application = (isset($_SERVER['HTTP_HOST'])) ? true : false;
        $this->debug = (isset($parameters['debug'])) ? $parameters['debug'] : 0; // 0=silent, 1=normal, 2=debug, 3=trace
        // Try to connect when instance is created
        if ($parameters['host'] !== '') 
        {
            $this->id = $this->internal_df_connect($parameters['host'], $parameters['user'], $parameters['password'], $parameters['database'], $parameters['charset']);
            /** The error details */
            $error_details = mysqli_error($this->id) . " . Host: " . $parameters['host'] . ". User: " . $parameters['user'] . " . Password: " . $parameters['password'] . ". Database: " . $parameters['database'] . ". Charset: " . $parameters['charset'];
            if (!$this->id) throw new \Exception("Error in establishing database server connection. Details: " . $error_details);
        }
    }
    /**
     * Class constructor
     *
     * Initializes object variables. connects to database server
     * Should be called by a factory class that accepts variable arguments
     *
     * @param string $srv database server host name
     * @param string $uid database user name
     * @param string $pwd database password
     * @param string $db database name
     * @param string $debug used to allow debugging of the sql queries
     * @param string $type type of database query. u=>update,s=>select,i=>insert,d=>delete
     * @param string $charset the charset for the database connection
     * @throws Exception object if database connection could not be established or an error occured
     */
    public function DatabaseFunctionsDirect($srv = '', $uid = '', $pwd = '', $db = '', $debug = 0, $type = 's', $charset = 'utf8') 
    {
        $this->id = 0;
        $this->df_initialize();
        $this->debug = $debug;
        // Try to connect when instance is created
        if ($srv !== '') 
        {
            $this->id = $this->internal_df_connect($srv, $uid, $pwd, $db, $charset);
            if (!$this->id) throw new \Exception("Error in establishing database server connection. Details: " . mysqli_error($this->id));
        }
    }
    /**
     * Creates a database table
     *
     * It creates a table using the given parameters
     *
     * @param string $table_name the name of the table to creat
     * @param array $field_list the list of table fields
     * name => string the field name
     * type => the type of the field. e.g int(11) NOT NULL or varchar(20) NOT NULL or text NOT NULL
     * @param string $primary_key the name of the primary key for the table
     * @param array $indexes optional the list of indexes that are unique
     * name => string the index name
     * field_list => array the list of field names that are unique
     * @param array $auto_increment optional the list of fields to auto increment. e.g `id` int (11)
     * @param string $comment optional the table comment
     * @param string $engine optional the mysql engine. default value is MyISAM
     * @param string $default_charset optional the default charset for the table. default value is utf8
     * @throws Exception object if table could not be created
     *
     * @return boolean $is_valid returns true if table was successfully created. throws exeption otherwise
     */
    public function df_create_table($table_name, $field_list, $primary_key, $auto_increment = array() , $indexes = array() , $comment = '', $engine = 'MyISAM', $default_charset = 'utf8') 
    {
        /** The list of table fields */
        $table_field_list = array();
        /** Each field is added to the table field list */
        for ($count = 0;$count < count($field_list);$count++) 
        {
            $table_field_list[] = '`' . $field_list[$count]['name'] . '` ' . $field_list[$count]['type'];
        }
        /** The list of table fields */
        $field_names = implode(",\n", $table_field_list);
        /** The create table sql */
        $create_table_sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (";
        /** The field list is added to the create table sql */
        $create_table_sql = $create_table_sql . $field_names;
        /** The other table attributes are added */
        $create_table_sql = $create_table_sql . ") ENGINE=" . $engine . " " . "AUTO_INCREMENT=1 " . "DEFAULT CHARSET=" . $default_charset . " " . "COMMENT='" . $comment . "'";
        /** The create table sql is run */
        $this->internal_df_open($create_table_sql);
        $indexes_sql = "ALTER TABLE `" . $table_name . "` " . "ADD PRIMARY KEY (`" . $primary_key . "`)";
        /** The indexes sql is run */
        $this->internal_df_open($indexes_sql);
        /** If given, then the indexes are added to the table */
        if (count($indexes) > 0) 
        {
            /** The indexes field list */
            $indexes_field_list = implode("`,`", $indexes['field_list']);
            $indexes_field_list = trim($indexes_field_list, "`");
            $indexes_field_list = trim($indexes_field_list, ",");
            $indexes_field_list = "`" . $indexes_field_list . "`";
            $indexes_sql = "ALTER TABLE `" . $table_name . "` " . "ADD UNIQUE KEY `" . $indexes['name'] . "` (" . $indexes_field_list . ")";
            /** The create table sql is run */
            $this->internal_df_open($indexes_sql);
        }
        /** If given, then the auto increment columns are added to the table */
        if (count($auto_increment) > 0) 
        {
            /** The auto increment sql is built */
            for ($count = 0;$count < count($auto_increment);$count++) 
            {
                $auto_increment_sql = "ALTER TABLE `" . $table_name . "` " . "MODIFY " . $auto_increment[$count] . " NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1";
                /** The create table sql is run */
                $this->internal_df_open($auto_increment_sql);
            }
        }
    }
    /**
     * Connects to mysql server
     *
     * Initializes object variables. connects to database server
     *
     * @param string $srv database server host name
     * @param string $uid database user name
     * @param string $pwd database password
     * @param string $db database name
     * @param string $debug used to allow debugging of the sql queries
     * @param string $type type of database query. u=>update,s=>select,i=>insert,d=>delete
     * @param string $charset the charset for the database connection
     * @throws Exception object if database connection could not be established or an error occured
     *
     * @return boolean $is_valid returns true if database connection succeeded. throws exeption otherwise
     */
    public function df_connect($srv, $uid, $pwd, $db, $debug = 0, $type = 's', $charset = 'utf8') 
    {
        $this->query_type = $type;
        $this->debug = $debug;
        $this->id = $this->internal_df_connect($srv, $uid, $pwd, $db, $charset);
        if ($this->id === false) throw new \Exception("Error in establishing database server connection. Details: " . mysqli_error($this->id) , $e);
        return true;
    }
    /**
     * Used to close the mysql connection
     */
    public function df_close() 
    {
        if (is_resource($this->id)) $this->internal_df_close();
    }
    /**
     * Used to set the table name
     *
     * @param string $table_name the name of the default MySQL table
     */
    public function df_set_table_name($table_name) 
    {
        $this->table_name = $table_name;
    }
    /**
     * Used to get the list of all the tables in the database
     *
     * @return array $table_list the list of all table names in the database
     */
    public function df_get_table_list() 
    {
        /** The sql query for fetching the list of tables */
        $sql = "SHOW TABLES";
        /** All table rows */
        $table_list = $this->df_all_rows($sql);
        return $table_list;
    }
    /**
     * Truncates a database table
     *
     * It truncates the given table
     *
     * @param string $table_name the name of the table to truncate
     *
     * @return boolean $is_valid returns true if table was successfully created. throws exeption otherwise
     */
    public function df_truncate_table($table_name) 
    {
        /** The truncate table sql */
        $truncate_table_sql = "TRUNCATE TABLE `" . $table_name . "`";
        /** The truncate table sql is run */
        $this->internal_df_open($truncate_table_sql);        
    }
    /**
     * Used to return all the table rows
     *
     * @param string $table_name the name of table
     *
     * @return array $rows all rows in the table are returned
     */
    public function df_all_table_rows($table_name) 
    {
        /** The select query */
        $sql = "SELECT * FROM " . $table_name;
        /** The query is run and all rows are returned */
        $rows = $this->df_all_rows($sql);
        
        return $rows;
    }
    /**
     * Used to drop the given table column
     *
     * @param string $table_name the name of the database table
     * @param string $column_name the name of the column that is to be dropped
     * @return boolean $column_dropped used to indicate if the given table column was successfully dropped
     */
    public function df_drop_column($table_name, $column_name) 
    {
        /** The sql for dropping the column */
        $sql = "ALTER TABLE `" . $table_name . "` DROP COLUMN " . $column_name;
        /** The result of the drop operation. The column is dropped */
        $column_dropped = $this->df_execute($sql);
        return $column_dropped;
    }
    /**
     * Used to rename the given table column
     *
     * @param string $table_name the name of the database table
     * @param string $old_column_name the name of the column that is to be renamed
     * @param string $new_column_name the new name of the column
     * @param string $column_type the type of the column
     *
     * @return boolean $column_renamed used to indicate if the given table column was successfully renamed
     */
    public function df_rename_column($table_name, $old_column_name, $new_column_name, $column_type) 
    {
        /** The sql for renaming the column */
        $sql = "ALTER TABLE `" . $table_name . "` CHANGE " . $old_column_name . " " . $new_column_name . " " . $column_type;
        /** The result of the rename operation. The column is renamed */
        $column_renamed = $this->df_execute($sql);
        return $column_renamed;
    }
    /**
     * Used to execute the given sql query
     *
     * @param string $sql sql query that needs to be executed
     *
     * @return boolean true if sql query was successfully executed. throws an exception otherwise
     */
    public function df_execute($sql) 
    {
        $rsid = $this->internal_df_open($sql);
        return true;
    }
    /**
     * Used to fetch the first row of the select query results
     *
     * @param string $sql sql query for which the data needs to be fetched
     *
     * @return array first row of the select query result
     */
    public function df_first_row($sql) 
    {
        $rsid = $this->internal_df_open($sql);
        $x = $this->internal_df_fetch($rsid);
        /** The memory associated with the result set is released */
        mysqli_free_result($rsid);
        
        return $x;
    }
    /**
     * Used to fetch all the rows of the select query results
     *
     * @param string $sql sql query for which the data needs to be fetched
     *
     * @return array all the rows of the select query result
     */
    public function df_all_rows($sql) 
    {
        $rsid = $this->internal_df_open($sql);
        $x = array();
        while ($r = $this->internal_df_fetch($rsid)) 
        {
            $x[] = $r;
        }
        /** The memory associated with the result set is released */
        mysqli_free_result($rsid);
        
        return $x;
    }
    /**
     * Used to get the number of rows affected by the last query
     *
     * @return int number of rows affected by last database query
     */
    public function df_affected_rows() 
    {
        return mysqli_affected_rows($this->id);
    }
    /**
     * Used to get the total number of rows in the database table
     *
     * @param string $table_name the name of the database table
     *     
     * @return int $row_count the total number of rows in the table
     */
    public function df_get_row_count($table_name) 
    {
        /** The sql for getting the total number of rows */
        $sql = "SELECT count(*) AS total FROM `" . $table_name . "`";
        /** The number of rows are fetched */
        $row = $this->df_all_rows($sql);
        /** The total number of rows */
        $row_count = $row[0]['total'];
        
        return $row_count;
    }
    /**
     * Used to get the names of all the table fields
     *
     * @param string $table_name the name of the table
     *
     * @return array $field_names the names of all the table fields
     */
    public function df_get_field_names($table_name) 
    {
        $field_names = array();
        $query = "SHOW COLUMNS FROM " . $table_name;
        $result = mysqli_query($this->id, $query);
        while ($row = mysqli_fetch_assoc($result)) $field_names[] = $row;
        
        /** The memory associated with the result set is released */
        mysqli_free_result($result);
        
        return $field_names;
    }
    /**
     * Used to get the row id of the last row that was added to database
     *
     * @return int row id of last row added to database
     */
    public function df_last_insert_id() 
    {
        return mysqli_insert_id($this->id);
    }
    /**
     * Used to set the sort order used in select query
     *
     * @param string $table_name the name of the table that contains the sort field
     * @param string $field_name the name of the field to sort by
     * @param string $sort_order the sort order. i.e asc or desc
     */
    public function df_set_order_by($table_name, $field_name, $sort_order) 
    {
        /** If the table name and field name are both given */
        if ($table_name != "" && $field_name != "")
            $this->sort_by = $table_name . "." . "`" . $field_name . "`";
        /** If the field name is given but not the table name */
        else if ($table_name == "" && $field_name != "")
            $this->sort_by = "`" . $field_name . "`";

        $this->order_by = $sort_order;
    }
    /**
     * Used to set the group by used in select query
     *
     * @param string $table_name the name of the table that contains the group by field
     * @param string $field_name the name of the field to group by
     */
    public function df_set_group_by($table_name, $field_name) 
    {
        $this->group_by = $table_name . "." . $field_name;
    }
    /**
     * Used to set the limit parameters used in select query
     *
     * @param int $start start number of the row
     * @param int $end end number of the row
     */
    public function df_set_limits($start, $end) 
    {
        $this->start = $start;
        $this->end = $end;
    }
    /**
     * Used to build the sql query string
     *
     * @param array $main_query 2d array. the array has three columns used to hold the field name,table name and field value
     * @param array $where_clause 2d array. the array has five columns used to hold the field name,table name,field value,operation (AND,OR) and operator (<,=,>,>=,>=)
     * @param string $query_type type should be i,s,u or d
     *
     * @return string sql query string created from the data
     */
    public function df_build_query($main_query, $where_clause, $query_type) 
    {
        $this->df_set_query_type($query_type);
        for ($count = 0;$count < count($main_query);$count++) 
        {
            $table_name = (isset($main_query[$count]['table'])) ? $main_query[$count]['table'] : $this->table_name;
            if ($query_type == 's') $this->df_add_select_field($main_query[$count]['field'], $table_name);
            else if ($query_type == 'i') $this->df_build_insert_query($main_query[$count]['field'], $main_query[$count]['value'], true, $table_name);
            else if ($query_type == 'u') $this->internal_df_build_update_query($main_query[$count]['field'], $main_query[$count]['value'], true, true, $table_name, "", "");
        }
        if ($where_clause != '') 
        {
            for ($count = 0;$count < count($where_clause);$count++) 
            {
                $operation = (isset($where_clause[$count]['operation'])) ? $where_clause[$count]['operation'] : "=";
                $operator = (isset($where_clause[$count]['operator'])) ? $where_clause[$count]['operator'] : "";
                $is_string = (isset($where_clause[$count]['is_string'])) ? $where_clause[$count]['is_string'] : true;
                $table_name = (isset($where_clause[$count]['table'])) ? $where_clause[$count]['table'] : $this->table_name;
                $this->df_build_where_clause($where_clause[$count]['field'], $where_clause[$count]['value'], $is_string, $table_name, $operation, $operator, false);
            }
        }
        return ($this->df_get_query_string());
    }
    /**
     * Used to build an insert query
     *
     * @param string $field table field name
     * @param string $value field value
     * @param boolean $is_string used to indicate if value is string
     * @param string $table name of the database table
     */
    public function df_build_insert_query($field, $value, $is_string, $table = '') 
    {
        $value = mysqli_escape_string($this->id, $value);
        if (count($this->table_list) == 0 && $table != '') $this->table_list[] = $table;
        $this->field_list[] = "`" . $field . "`";
        if ($is_string) $this->value_list[] = "'" . $value . "'";
        else $this->value_list[] = $value;
    }
    /**
     * Used to build a delete query
     *
     * @param string $field table field name
     * @param string $value field value
     * @param boolean $is_string used to indicate if value is string
     * @param string $table name of the database table
     * @param string $operator the comparision operator. e.g <,>,=,!=,LIKE etc
     */
    public function df_build_delete_query($field, $value, $is_string, $table, $operator) 
    {
        $value = mysqli_escape_string($this->id, $value);
        if (!in_array($table, $this->table_list)) $this->table_list[] = $table;
        if ($is_string) $this->where_clause.= $table . "." . $field . $operator . "'" . $value . "',";
        else $this->where_clause.= $table . "." . $field . $operator . $value . "',";
    }
    /**
     * Used to return the where clause of the sql query
     *
     * @return string $where_clause
     */
    public function df_get_where_clause() 
    {
        return $this->where_clause;
    }
    /**
     * Used to clear the query log
     */
    public function df_clear_query_log() 
    {
        $this->query_log = array();
    }
    /**
     * Used to display the query log
     *
     * @param boolean $is_return used to indicate if the query log should be displayed or returned
     *
     * @return string $query_string the mysql query log
     */
    public function df_display_query_log($return) 
    {
        /** The mysql query string */
        $query_string = "";
        if ($this->is_browser_application) $line_break = "<br/>";
        else $line_break = "\n";
        for ($count = 0;$count < count($this->query_log);$count++) 
        {
            $query = $this->query_log[$count];
            if ($this->debug > 1) $query_string.= ($count + 1) . ") time taken: " . $query['time_taken'] . " sec" . $line_break . "query: " . $query['sql'] . $line_break . $line_break;
            else if ($this->debug == 1) $query_string.= ($count + 1) . ") query: " . $query['sql'] . $line_break . $line_break;
        }
        if (!$return) echo $query_string;
        return $query_string;
    }
    /**
     * Used to fetch the final query string
     *
     * @param string $type type of sql query. e.g i=>insert,d=>delete,u=>update,s=>select
     *
     * @return string $sql the sql query
     */
    public function df_get_query_string($type = '') 
    {
        if ($type != '') $this->query_type = $type;
        if (strtolower($this->query_type) == 'i') 
        {
            $this->query = "INSERT INTO " . $this->table_list[0] . " (";
            for ($count = 0;$count < count($this->field_list);$count++) $this->query.= ($this->field_list[$count] . ",");
            $this->query = trim($this->query, ',');
            $this->query.= ") VALUES(";
            for ($count = 0;$count < count($this->value_list);$count++) $this->query.= ($this->value_list[$count] . ",");
            $this->query = trim($this->query, ',');
            $this->query.= ")";
        }
        else if (strtolower($this->query_type) == 's') 
        {
            if ($this->where_clause != "") $this->where_clause = trim($this->where_clause, ',');
            $this->query = "SELECT ";
            for ($count = 0;$count < count($this->display_fields);$count++) $this->query.= ($this->display_fields[$count] . ",");
            $this->query = trim($this->query, ',') . " FROM ";
            for ($count = 0;$count < count($this->table_list);$count++) $this->query.= ($this->table_list[$count] . ",");
            $this->query = trim($this->query, ',');
            if ($this->where_clause != "") $this->query.= " WHERE " . $this->where_clause;
            if ($this->group_by != "") $this->query.= " GROUP BY " . $this->group_by;
            if ($this->sort_by != "" || $this->order_by != "") $this->query.= " ORDER BY " . $this->sort_by . " " . $this->order_by;
            if (is_numeric($this->start) && is_numeric($this->end) && ($this->end) > 0) $this->query.= " LIMIT " . $this->start . "," . $this->end;
        }
        else if (strtolower($this->query_type) == 'u') 
        {
            if ($this->where_clause != "") $this->where_clause = trim($this->where_clause, ',');
            $this->query = "UPDATE ";
            for ($count = 0;$count < count($this->table_list);$count++) $this->query.= ($this->table_list[$count] . ",");
            $this->query = trim($this->query, ',');
            $this->query.= " SET ";
            for ($count = 0;$count < count($this->field_list);$count++) $this->query.= ($this->field_list[$count] . "=" . $this->value_list[$count] . ",");
            $this->query = trim($this->query, ',');
            if ($this->where_clause != "") $this->query.= " WHERE " . $this->where_clause;
        }
        else if (strtolower($this->query_type) == 'd') 
        {
            if ($this->where_clause != "") $this->where_clause = trim($this->where_clause, ',');
            $this->query = "DELETE FROM ";
            $this->query.= ($this->table_list[0] . ",");
            $this->query = trim($this->query, ',');
            if ($this->where_clause != "") $this->query.= " WHERE " . $this->where_clause;
        }
        else throw new \Exception("Invalid query type given");
        return $this->query;
    }
    /**
     * Used to get the mysql query link
     *
     * @return int $Id the mysql query link resource
     */
    public function df_get_id() 
    {
        return $this->id;
    }
    /**
     * Used to set the query type of the query
     *
     * @param string $type type of sql query. e.g i,s,u or d
     */
    function df_set_query_type($type) 
    {
        $this->query_type = $type;
    }
    /**
     * Used to initialize object variables
     */
    public function df_initialize() 
    {
        $this->query = '';
        $this->where_clause = '';
        $this->query_type = '';
        $this->sort_by = '';
        $this->group_by = '';
        $this->order_by = '';
        $this->table_name = '';
        $this->table_list = array();
        $this->field_list = array();
        $this->value_list = array();
        $this->display_fields = array();
    }
    /**
     * Used to set the table name
     *
     * @param string $table_name the name of the database table
     */
    public function df_set_table($table_name) 
    {
        $this->table_list[] = $table_name;
    }
    /**
     * Used to add the display fields of a select query
     *
     * @param string $field name of the table field
     * @param string $table name of the database table
     */
    public function df_add_select_field($field, $table) 
    {
        $this->internal_df_build_select_query($field, "", true, "", $table, "", "", false);
    }
    /**
     * Used to add the update fields of an update query
     *
     * @param string $field name of the table field
     * @param string $table name of the database table
     * @param string $value field value
     * @param boolean $is_string used to indicate if the value is a string
     */
    public function df_add_update_field($field, $table, $value, $is_string) 
    {
        $this->internal_df_build_update_query($field, $value, true, $is_string, $table, '', '');
    }
    /**
     * Used to add the where clause of a select,update query or delete query
     *
     * @param string $field name of the table field
     * @param string $value field value
     * @param boolean $is_string used to indicate if the value is a string
     * @param string $table name of the database table
     * @param string $operation the comparision operator. e.g <,>,=,!=,LIKE etc
     * @param string $operator the operation to be performed. e.g AND, OR, NOT
     * @param int $options an integer value that indicates an extra option. e.g start of sub where clause. i.e '('. following options are supported: (,)
     */
    public function df_build_where_clause($field, $value, $is_string, $table, $operation, $operator, $options) 
    {
        $this->internal_df_build_select_query($field, $value, false, $is_string, $table, $operation, $operator, $options);
    }
    /**
     * Commits the current transaction
     *
     * MySQL commit only works with transactional table types like innodb
     * It does not support MyISAM table type
     * Once the transaction is commited the changes are written to database
     *
     * @throws Exception object if transaction could not be commited
     */
    public function df_commit() 
    {
        if (!mysqli_commit($this->id)) throw new \Exception("Error in commiting transaction. Details: " . mysqli_error($this->id) , $e);
    }
    /**
     * Rolls back the current transaction
     *
     * MySQL rollback only works with transactional table types like innodb
     * It does not support MyISAM table type
     * Once the transaction is rolledback it cannot be saved to database
     *
     * @throws Exception object if transaction could not be commited
     */
    public function df_rollback() 
    {
        if (!mysqli_rollback($this->id)) throw new \Exception("Error in rolling back transaction. Details: " . mysqli_error($this->id) , $e);
    }
    /**
     * Turns on or off auto commit for MySQL queries
     *
     * MySQL autocommit only works with transactional table types like innodb
     * It does not support MyISAM table type
     * Autocommit should be turned off if queries need to be run as part of a transaction
     *
     * @param boolean $is_enable is true if autocommit needs to be enabled. its false if it needs to be disabled
     * @throws Exception object if autocommit could not be turned off
     */
    public function df_toggle_autocommit($is_enable) 
    {
        if (!mysqli_autocommit($this->id, $is_enable)) throw new \Exception("Error in changing autocommit value. Details: " . mysqli_error($this->id) , $e);
    }
    /**
     * Used to connect to the mysql database server
     *
     * @param string $srv database server host name
     * @param string $uid database user name
     * @param string $pwd database password
     * @param string $db database name
     * @param string $charset database connection charset
     * @throws Exception object if an error occured
     *
     * @return boolean $is_valid returns true if database connection succeeded. throws exeption otherwise
     */
    private function internal_df_connect($srv, $uid, $pwd, $db, $charset) 
    {
        $Id = mysqli_connect($srv, $uid, $pwd);       
        if (($Id !== false) and ($db !== '')) 
        {
            if (!mysqli_select_db($Id, $db)) throw new \Exception("Error in establishing database server connection. Details: " . mysqli_error($Id));
        }
        mysqli_set_charset($Id, $charset);
        return $Id;
    }
    /**
     * Used to close the connection to the mysqld database
     */
    private function internal_df_close() 
    {
        if (is_resource($this->id)) mysqli_close($this->id);
    }
    /**
     * Used to open a connection to a mysql database
     *
     * @param string $sql sql string that needs be executed
     * @throws Exception object if an error occured
     *
     * @return int $rsid mysql query result
     */
    private function internal_df_open($sql) 
    {
        /** If debugging mode is set to 1 or greater then the start time is noted */
        if ($this->debug >= 1) $start_time = microtime(true);
        $rsid = mysqli_query($this->id, $sql);
        if ($this->debug >= 1) 
        {
            /** If debugging mode is set to 1 or greater then the end time is noted and the query is logged along with time taken */
            $end_time = microtime(true);
            /** The logging information is saved to array */
            $sql_log = array(
                array(
                    "sql" => $sql,
                    "time_taken" => number_format(($end_time - $start_time) , 4)
                )
            );
            $this->query_log = array_merge($this->query_log, $sql_log);
        }
        if ($rsid) return $rsid;
        else throw new \Exception("Error in executing mysql query. Details: " . mysqli_error($this->id) . " sql: " . $sql);
    }
    /**
     * Used to fetch the results of mysql query
     *
     * @param int $rsid mysql resource id of the data that needs to be fetched
     *
     * @return array $row one row of the tables data
     */
    private function internal_df_fetch($rsid) 
    {
        /** The data is fetched */
        $row = mysqli_fetch_assoc($rsid);
        
        return $row;
    }
    /**
     * Used to build a select query
     *
     * @param string $field name of the table field
     * @param string $value field value
     * @param boolean $is_display_field used to indicate if the field is a display field or is used in where clause
     * @param boolean $is_string used to indicate if the value is a string
     * @param string $table name of the database table
     * @param string $operation the operation to be performed. e.g AND, OR, NOT
     * @param string $operator the comparision operator. e.g <,>,=,!=,LIKE etc
     * @param int $options an integer value that indicates an extra option. e.g start of sub where clause. i.e '('. following options are supported: (,)
     */
    private function internal_df_build_select_query($field, $value, $is_display_field, $is_string, $table, $operation, $operator, $options) 
    {
        /** If the field name is not '*' and it is not 'count(*) as total' and it does not contain the string DISTINCT then it is enclosed in "`" */
        if ($field != "*" && $field != "count(*) as total" && strpos($field, "DISTINCT") === false && strpos($field, "CONCAT") === false) $field = "`" . $field . "`";
        $value = mysqli_escape_string($this->id, $value);
        if ($table != "" && !in_array($table, $this->table_list)) $this->table_list[] = $table;
        if ($is_display_field) 
        {
            if ($table != "" && $field != "*" && strpos($field, "DISTINCT") != false) $this->display_fields[] = $table . "." . $field;
            else $this->display_fields[] = $field;
        }
        else
        {
            if ($options == DatabaseFunctions::START_SUB_WHERE) $this->where_clause.= "(";
            if ($table != "" && strpos($field, ".") === false) $field_name = $table . "." . $field;
            else $field_name = $field;
            if ($is_string) $this->where_clause.= $field_name . " ". $operation . "'" . $value . "'";
            else $this->where_clause.= $field_name . " ". $operation . $value;
            if ($options == DatabaseFunctions::END_SUB_WHERE) $this->where_clause.= ")";
            $this->where_clause.= " " . $operator . " ";
        }
    }
    /**
     * Used to build an update query
     *
     * @param string $field name of the table field
     * @param string $value field value
     * @param boolean $is_update_field used to indicate if the field is an update field or is used in where clause
     * @param boolean $is_string used to indicate if the value is a string
     * @param string $table name of the database table
     * @param string $operation the operation to be performed. e.g AND, OR, NOT
     * @param string $operator the comparision operator. e.g <,>,=,!=,LIKE etc
     */
    private function internal_df_build_update_query($field, $value, $is_update_field, $is_string, $table, $operation, $operator) 
    {
        $value = mysqli_escape_string($this->id, $value);
        if ($is_update_field) 
        {
            if (!in_array($table, $this->table_list)) $this->table_list[] = $table;
            $this->field_list[] = $field;
            if ($is_string) $this->value_list[] = "'" . $value . "'";
            else $this->value_list[] = $value;
        }
        else
        {
            if ($is_string) $this->where_clause.= $table . "." . $field . " ". $operation . "'" . $value . "',";
            else $this->where_clause.= $table . "." . $field . " ". $operation . $value . "',";
        }
    }
}

