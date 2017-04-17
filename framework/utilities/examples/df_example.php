<?php
namespace Framework\Utilities;
error_reporting(E_ALL);
ini_set("display_errors",1);
include('../DatabaseFunctions.php');

echo "<h2>Testing DatabaseFunctions class</h2>";
/* Enter your database connection information. You should first import the sql file given in data folder */
$connection_information            = array("host"=>"localhost","user"=>"nadir","password"=>"kcbW5eFSCbPXbJGLHvUGG8T8","database"=>"pakphp_com","debug"=>"2","charset"=>"utf8");

SelectQuery($connection_information);
UpdateQuery($connection_information);
InsertQuery($connection_information);
//DeleteQuery($connection_information);

/**
 * Used to select data from database
 * 
 * @since 1.2.2
 * @param array $connection_information an array with following keys:
 * host => the database host name
 * user => the database user
 * password => the database password
 * database => the database name
 * debug => the debug level it can be 0,1 or 2
 * charset => utf8
 */
function SelectQuery($connection_information)
{
    /** The database connection details */
    $parameters                   = $connection_information;  		
    /** The DatabaseFunctions object is created */
	$database                     = new DatabaseFunctions($parameters);
	/** The database table name */
	$table_name                   = "pakphp_cached_data";
	/** The table name is set */
	$database->df_set_table($table_name);
	/** The database field names are fetched */
	$field_names                  = $database->df_get_field_names($table_name);
	/** The field names are displayed */
	echo "<h3>Field Names: </h3>";
    print_R($field_names);
	
	/** The select fields */
	$main_query                   = array();		
	/** Optional table name. Useful for multiple tables */
	$main_query[0]['table']       = "pakphp_cached_data";
	/** The select field for the above table */
	$main_query[0]['field']       = "*";
		
	/** The where clause */
	$where_clause                 = array();
	/** The field name in where clause */		
	$where_clause[0]['field']     = "function_name";
	/** The field value */
	$where_clause[0]['value']     = "InsertQuery";
	/** The option table name of the above field. Useful for multiple tables */
	$where_clause[0]['table']     = "pakphp_cached_data";
	/** The operation. e.g =,<,>,!= */
	$where_clause[0]['operation'] = "=";
	/** The operator. e.g AND, OR, NOT */
	$where_clause[0]['operator']  = "AND";
	
	/** The second field in where clause */
	$where_clause[1]['field']     = "id";
	/** The value of second field */
	$where_clause[1]['value']     = "1";
	/** The optional table name */
	$where_clause[1]['table']     = "pakphp_cached_data";
	/** The operation. e.g =,<,>,!= */
	$where_clause[1]['operation'] = ">=";
	/** The operator. e.g AND, OR, NOT */
	$where_clause[1]['operator']  = "";
	/** The order by clause is set */
	$database->df_set_order_by("pakphp_cached_data", "id", "DESC");
	/** The group by clause is set */
	$database->df_set_group_by("pakphp_cached_data", "created_on");
	/** The limit clause is set */
	$database->df_set_limits(0,1);
	/** The database query is fetched */
    $query                        = $database->df_build_query($main_query, $where_clause, 's');
	echo "<h3>Database query: </h3>";
	/** The query is displayed */		
	echo $query;
				
	/** All rows are fetched from database */
	$all_rows                     = $database->df_all_rows($query);
	echo "<h3>All Table rows: </h3>";
	print_r($all_rows);
	
	/** The first row is fetched from database */
	$row                          = $database->df_first_row($query);
	echo "<h3>First Table row: </h3>";
	print_r($row);
	
	/** The query log is displayed */
	echo "<h3>Query Log: </h3>";
	$database->df_display_query_log(false);
	
	/** The query log is cleared */
	$database->df_clear_query_log();
	
	/** The database connection is closed */
	$database->df_close();
}

/**
 * Used to update data in database
 * 
 * @since 1.2.2
 * @param array $connection_information an array with following keys:
 * host => the database host name
 * user => the database user
 * password => the database password
 * database => the database name
 * debug => the debug level it can be 0,1 or 2
 * charset => utf8
 */
function UpdateQuery($connection_information)
{
    /** The database connection details */
    $parameters                   = $connection_information;  		
    /** The DatabaseFunctions object is created */
	$database                     = new DatabaseFunctions($parameters);
	/** The database table name */
	$table_name                   = "pakphp_cached_data";
	/** The table name is set */
	$database->df_set_table($table_name);
	
	/** The fields to update */
	$main_query                   = array();		
	/** Optional table name. Useful for multiple tables */
	$main_query[0]['table']       = "pakphp_cached_data";
	/** The update field for the above table */
	$main_query[0]['field']       = "created_on";
	/** The new value for the field */
	$main_query[0]['value']       = time();
	
	/** The where clause */
	$where_clause                 = array();
	/** The field name in where clause */		
	$where_clause[0]['field']     = "function_name";
	/** The field value */
	$where_clause[0]['value']     = "TestFunction";
	/** The option table name of the above field. Useful for multiple tables */
	$where_clause[0]['table']     = "pakphp_cached_data";
	/** The operation. e.g =,<,>,!= */
	$where_clause[0]['operation'] = "=";
	/** The operator. e.g AND, OR, NOT */
	$where_clause[0]['operator']  = "";
	
	/** The database query is fetched */
        $query                        = $database->df_build_query($main_query, $where_clause, 'u');
	echo "<h3>Database query: </h3>";
	/** The query is displayed */		
	echo $query;
				
	/** The database query is run */
	$database->df_execute($query);
	
	/** The number of rows affected by the query */
	$affected_rows                 = $database->df_affected_rows($query);
	echo "<h3>Affected rows: </h3>";
	print_r($affected_rows);
	
	/** The query log is displayed */
	echo "<h3>Query Log: </h3>";
	$database->df_display_query_log(false);
	
	/** The query log is cleared */
	$database->df_clear_query_log();
	
	/** The database connection is closed */
	$database->df_close();
}

/**
 * Used to add data to database
 * 
 * @since 1.2.2
 * @param array $connection_information an array with following keys:
 * host => the database host name
 * user => the database user
 * password => the database password
 * database => the database name
 * debug => the debug level it can be 0,1 or 2
 * charset => utf8
 */
function InsertQuery($connection_information)
{	
    /** The database connection details */
    $parameters                   = $connection_information;  		
    /** The DatabaseFunctions object is created */
	$database                     = new DatabaseFunctions($parameters);
	/** Auto commit is disabled. So the data will only be saved to database when the commit function is called */
	$database->df_toggle_autocommit(false);
	/** The database table name */
	$table_name                   = "pakphp_cached_data";
	/** The table name is set */
	$database->df_set_table($table_name);
	
	/** The fields to add */
	$main_query                   = array();
	/** Field 1 */
	$main_query[0]['field']       = "function_name";
	/** Value for field 1 */
	$main_query[0]['value']       = "InsertQuery";
	/** Field 2 */
	$main_query[1]['field']       = "function_parameters";
	/** Value for field 2 */
	$main_query[1]['value']       = "test parameters";
	/** Field 3 */
	$main_query[2]['field']       = "data";
	/** Value for field 3 */
	$main_query[2]['value']       = "test data";
	/** Field 4 */
	$main_query[3]['field']       = "created_on";
	/** Value for field 4 */
	$main_query[3]['value']       = time();
	
	/** The database query is fetched */
    $query                        = $database->df_build_query($main_query, array(), 'i');
	echo "<h3>Database query: </h3>";
	/** The query is displayed */		
	echo $query;
	
	/** The database query is run */
	$database->df_execute($query);
	
	/** The number of rows affected by the query */
	$affected_rows                 = $database->df_affected_rows($query);
	echo "<h3>Affected rows: </h3>";
	print_r($affected_rows);
	
	/** The id of the last inserted row */
	$last_inserted_row_id          = $database->df_last_insert_id();
	echo "<h3>Last inserted row id: </h3>";
	print_r($last_inserted_row_id);
							
	/**
	 * The data is commited to database
	 * If you comment out the following line, and uncomment the rollback line
	 * Then the data will not be added to database
	 * But in both cases the mysql affected rows shows 1 record added
	 */
	$database->df_commit();	
	/** The changes are rolled back */
	//$database->df_rollback();	
	
	/** The query log is displayed */
	echo "<h3>Query Log: </h3>";
	$database->df_display_query_log(false);
	
	/** The query log is cleared */
	$database->df_clear_query_log();
	
	/** The database connection is closed */
	$database->df_close();
}

/**
 * Used to delete data from database
 * 
 * @since 1.2.2
 * @param array $connection_information an array with following keys:
 * host => the database host name
 * user => the database user
 * password => the database password
 * database => the database name
 * debug => the debug level it can be 0,1 or 2
 * charset => utf8
 */
function DeleteQuery($connection_information)
{
    /** The database connection details */
    $parameters                   = $connection_information;  		
    /** The DatabaseFunctions object is created */
	$database                     = new DatabaseFunctions($parameters);
	/** The database table name */
	$table_name                   = "pakphp_cached_data";
	/** The table name is set */
	$database->df_set_table($table_name);

	/** The where clause */
	$where_clause                 = array();
	/** The field name in where clause */		
	$where_clause[0]['field']     = "function_name";
	/** The field value */
	$where_clause[0]['value']     = "InsertQuery";
	/** The operation. e.g =,<,>,!= */
	$where_clause[0]['operation'] = "=";
	/** The operator. e.g AND, OR, NOT */
	$where_clause[0]['operator']  = "";
	
	/** The database query is fetched */
    $query                        = $database->df_build_query(array(), $where_clause, 'd');
	echo "<h3>Database query: </h3>";
	/** The query is displayed */		
	echo $query;
				
	/** The database query is run */
	$database->df_execute($query);
	
	/** The number of rows affected by the query */
	$affected_rows                 = $database->df_affected_rows($query);
	echo "<h3>Affected rows: </h3>";
	print_r($affected_rows);
		
	/** The query log is displayed */
	echo "<h3>Query Log: </h3>";
	$database->df_display_query_log(false);
	
	/** The query log is cleared */
	$database->df_clear_query_log();
	
	/** The database connection is closed */
	$database->df_close();
}
?>
