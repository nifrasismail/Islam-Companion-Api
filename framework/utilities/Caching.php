<?php

namespace Framework\Utilities;

/**
 * Caching class provides functions related to caching data
 * 
 * It includes functions related to caching of data
 * Such as caching function return value 
 * 
 * @category   Framework
 * @package    Utilities
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.1
 * @link       https://github.com/nadirlc/Public-API-Functions
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class Caching
{
    /**
     * The caching duration for each function
     */
    public static $function_cache_duration;
    /**
     * The single static instance
     */
    protected static $instance;    
    /**
     * The mysqli link resource for the database	 
     */
    private $db_link;    
    /**
     * The table name for the mysql table containing cached data     
     */
    private $table_name;
    /**
     * The site url
     */
    private $site_url;
	
    /**
     * Used to return a single instance of the class
     * 
     * Checks if instance already exists
     * If it does not exist then it is created
     * The instance is returned
     * 
     * @since 1.0.0
     * @param array $parameters an array containing class parameters. it has following keys:
     * db_link=> the link to the database
     * table_name=> the table name of the database table where the cached data will be stored		 		
     *  
     * @return Caching static::$instance name the instance of the correct child class is returned 
     */
    public static function GetInstance($parameters)
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
	
    /**
     * Class constructor
     *
     * Used to set the database table name for the table that stores cached data
     * Also sets the database link resource
     * 
     * @since 1.0.0
     * @param array $parameters an array containing class parameters. it has following keys:
     * db_link => resource the link to the database
     * table_name => string the table of the database table where the cached data will be stored
     * site_url => string the site url					 		
     */
    protected function __construct($parameters)
    {
        /** The database connection link */
        $this->db_link                 = $parameters['db_link'];
        /** The database table name */
        $this->table_name              = $parameters['table_name'];
        /** The site url */
        $this->site_url                = $parameters['site_url'];
        /** The duration in seconds for which each functon should be cached */
        self::$function_cache_duration = array(
            "TestFunction" => (3600 * 24)
        );
    }
	
	/**
     * Used to set the database link resource
     *
     * Used to set the database link resource so it can be used to cache data to database     
     * 
     * @since 1.0.1
     * @param resource $db_link the mysqli link resource
     */
    public function SetDbLink($db_link)
    {
       $this->db_link=$db_link;
    }
	
    	/**
     * Used to set the site url
     *
     * Used to set the site url
     * 
     * @since 1.0.1
     * @param string $site_url the site url
     */
    public function SetSiteUrl($site_url)
    {
       $this->site_url=$site_url;
    }
    
    /**
     * Used to encode the function data
     *
     * Used to encode function data
     * If the data is an array it is first converted to json using json_encode function
     * The data string is then base64 encoded		 
     * 
     * @since 1.0.0
     * @param array $data an array
     * 
     * @return string $encoded_data base64 encoded parameters
     */
    private function EncodeFunctionData($data)
    {
        /** If the parameters is an array it is json encoded */
        if (is_array($data))
            $data = base64_encode(json_encode($data));
        /** The parameters are encoded to base64 in any case */
        $encoded_data = base64_encode($data);
        
        return $encoded_data;
    }
	
    /**
     * Gets the data in the function cache
     *
     * It checks if the data has been in cache for the configuration duration
     * If so then it returns the cached data
     * 		
     * @since 1.0.0
     * @param string $function_name name of the function whoose output is required
     * @param array $parameters function parameters		 
     * @param boolean $check_cache_duration used to indicate if the function cache duration should be checked
	 * if set to false then the data will be fetched from cache even if it is expired
	 * 
     * @return mixed the function data is returned or false if data was not found in cache
     */
    public function GetCachedData($function_name, $parameters, $check_cache_duration)
    {
        /** The string object is fetched */
        $string_obj     = new Strings();
        /** The encryption object is fetched */
        $encryption_obj     = new Encryption();
        /** The duration for which function is to be cached */
        $cache_duration = ($check_cache_duration) ? self::$function_cache_duration[$function_name] : '-1';
        /** The function parameters are encoded */
        $parameters     = $encryption_obj->EncodeData($parameters);
        /** The cached data is fetched from database */
        if ($cache_duration != -1 && $check_cache_duration)
            $select_str = "SELECT * FROM " . $this->table_name . " WHERE function_name='" . mysqli_escape_string($this->db_link, $function_name) . "' AND function_parameters='" . mysqli_escape_string($this->db_link, $parameters) . "' AND (created_on+" . mysqli_escape_string($this->db_link, $cache_duration) . ")>=" . time();
        else
            $select_str = "SELECT * FROM " . $this->table_name . " WHERE function_name='" . mysqli_escape_string($this->db_link, $function_name) . "' AND function_parameters='" . mysqli_escape_string($this->db_link, $parameters) . "'";
        $result = mysqli_query($this->db_link, $select_str);
        /** If the data is found then it is returned */
        if (mysqli_num_rows($result) == 1) {
            $row  = mysqli_fetch_assoc($result);
            $data = $encryption_obj->DecodeData($row['data'], true);
            return $data;
        }	
        /** If it is not found then false is returned */
        else {
            return false;
           }
    }
    
    /**
     * Saves the data returned by the function to cache
     *
     * It first encodes the data to base64
     * It checks if data exists in database
     * If not then data is added to database
     * Otherwise the data is updated
     * 
     * @since 1.0.0
     * @param string $function_name name of the function whoose output needs to be cached
     * @param array $parameters function parameters
     * @param array $data function data that needs to be cached			 
     */
    public function SaveDataToCache($function_name, $parameters, $data)
    {
        /** The encryption object is fetched */
        $encryption_obj     = new Encryption();
        /** The function parameters are encoded */
        $encoded_parameters = $encryption_obj->EncodeData($parameters);
        /** The function data is encoded */
        $encoded_data       = $encryption_obj->EncodeData($data);
        /** The data is fetched from cache. If it exists in cache then it is updated */
        if ($this->GetCachedData($function_name, $parameters, false)) {
            $update_str = "UPDATE " . $this->table_name . " SET created_on='" . time() . "',data='" . mysqli_escape_string($this->db_link, $encoded_data) . "',site_url='" . mysqli_escape_string($this->db_link, $this->site_url) . "' WHERE function_name='" . mysqli_escape_string($this->db_link, $function_name) . "' AND function_parameters='" . mysqli_escape_string($this->db_link, $encoded_parameters) . "'";
            mysqli_query($this->db_link, $update_str);
        }
        /** Otherwise it is added to database */
        else {
            $insert_str = "INSERT INTO " . $this->table_name . "(function_name,function_parameters,data,site_url,created_on) VALUES('" . mysqli_escape_string($this->db_link, $function_name) . "','" . mysqli_escape_string($this->db_link, $encoded_parameters) . "','" . mysqli_escape_string($this->db_link, $encoded_data) . "','" . mysqli_escape_string($this->db_link, $this->site_url) . "','". time() . "')";
            mysqli_query($this->db_link, $insert_str);
        }
    }
}
