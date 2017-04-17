<?php

namespace Framework\Application;
use \Framework\Configuration\Base as Base;

/**
 * This class implements the Api class
 *
 * It provides api related functions
 * The class should be used by application classes that need to provide an api
 *
 * @category   Framework
 * @package    Application
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class Api extends Application
{
    /**
     * It makes an api request to the give local module
     *
     * It calls the HandleRequest
     *
     * @param string $option the url option
     * @param string $module_name the name of the module
     * @param string $output_format [array~json~string~encrypted string~encrypted json] used to indicate how the output of the function should be formatted by the framework
     * @param array $parameters list of parameters to send to local module
     * @throws object Exception an exception is thrown if the api response contains an error
     *
     * @return array $response the api response
     */
    final public function MakeApiRequestToLocalModule($option, $module_name, $output_format, $parameters) 
    {
        try
        {
            /** The api url parameters */
            $parameters                     = array("option" => $option, "module" => $module_name, "output_format" => $output_format, "parameters" => $parameters, "is_link" => false, "object_name" => "application");
            /** Used to indicate that the application context should be local api */
            $parameters['context']          = 'local api';
            /** Indicates whether application is in development mode */
            $parameters['development_mode'] = $this->GetConfig("general", "development_mode");
            /** The api response. It is fetched by making call to local module */
            $response                  = self::HandleRequest($parameters['context'], $parameters, $module_name);
            /** The api response is returned */
            return $response;
        }
        catch(\Exception $e) 
        {
            $this->GetComponent("errorhandler")->ExceptionHandler($e);
        }
    }
    /**
     * It makes an api request to the give remote module
     *
     * It builds the api url from the given parameters
     * It makes an http request to the api url and fetches the api response
     * If the response contains an error then an exception is thrown
     * Otherwise the api response is returned
     *
     * @param string $option the url option
     * @param string $module_name the name of the module
     * @param string $output_format [array~json~string~encrypted string~encrypted json] used to indicate how the output of the function should be formatted by the framework
     * @param array $parameters list of parameters to include with url
     * @param string $method [GET~POST] optional the http method to use. if GET is used then the parameters are encoded and included with url
     *
     * @return string $response the api response
     */
    final public function MakeApiRequestToRemoteModule($option, $module_name, $output_format, $parameters, $method) 
    {
        /** If the method is GET */
        if ($method == "GET") 
        {
            /** The context for the remote api call is set to 'remote api' */
            $parameters['context'] = "remote api";
            /** The url of the api server */
            $api_url = $this->GetConfig("general", "api_url");
            /** The api url parameters */
            $parameters = array("option" => $option, "module" => $module_name, "output_format" => $output_format, "parameters" => $parameters, "is_link" => false, "url" => $api_url, "object_name" => "application");
            /** Indicates whether application is in development mode */
            $parameters['development_mode'] = $this->GetConfig("general", "development_mode");
            /** The api key */
            $parameters['api_key'] = $this->GetConfig("general", "api_key");
            /** The api url with parameters is generated */
            $api_url = $this->GenerateUrl($parameters);
            /** The api response is fetched */
            $response = $this->GetComponent("filesystem")->GetFileContent($api_url);
        }
        /** If the method is POST */
        else if ($method == "POST") 
        {
            /** The url of the api server */
            $api_url = $this->GetConfig("general", "api_url");
            /** The api url parameters */
            $parameters = array("option" => $option, "module" => $module_name, "output_format" => $output_format, "parameters" => $parameters, "is_link" => false, "url" => $api_url, "object_name" => "application");            
            /** The context for the remote api call is set to 'remote api' */
            $parameters['context'] = "remote api";
            /** Indicates whether application is in development mode */
            $parameters['development_mode'] = $this->GetConfig("general", "development_mode");
            /** The api key */
            $parameters['api_key'] = $this->GetConfig("general", "api_key");
            /** Each api parameter is encoded */
            $parameters['parameters'] = $this->GetComponent("encryption")->EncodeData($parameters['parameters']);
            /** The api response is fetched */
            $response = $this->GetComponent("filesystem")->GetFileContent($api_url, "POST", $parameters);
        }
        /** The api response is returned */
        return $response;
    }
    
    /**
     * It makes an api request to the given module
     *
     * It makes an api requests to the local api or remote api
     * Depending on the request_type parameter
     *
     * @param string [local~remote] $request_type the type of module to call
     * @param string $option the url option
     * @param string $module_name the name of the module
     * @param string $output_format [array~json~string~encrypted string~encrypted json] used to indicate how the output of the function should be formatted by the framework
     * @param array $parameters list of parameters to include with api request
     * @param string $method [GET~POST] optional the http method to use. if GET is used then the parameters are encoded and included with url
     *
     * @return array $response the api response
     */
    final public function MakeApiRequest($request_type, $option, $module_name, $output_format, $parameters, $method = "GET") 
    {
        /** The profiling timer is started */
        $this->GetComponent("profiling")->StartProfiling("execution_time");
        /** The access log information */
        $access_log_information = array(
            "request_type" => $request_type,
            "parameters" => $parameters,
            "output_format" => $output_format,
            "response" => ''
        );
        /** The log access information is saved to application configuration so it can be used in other functions */
        $this->SetConfig("general", "access_log_information", $access_log_information);
        /** If the local api module needs to be called */
        if ($request_type == "local") 
        {
            $response = $this->MakeApiRequestToLocalModule($option, $module_name, $output_format, $parameters);
        }
        /** If the remote api module needs to be called */
        else if ($request_type == "remote") 
        {
            $response = $this->MakeApiRequestToRemoteModule($option, $module_name, $output_format, $parameters, $method);
        }
        /** If the response format is json, then the output is json decoded */
        if ($output_format == "json") 
        {
            $response = json_decode($response, true);
        }
        /** If the result is not set to success in the response then an exception is thrown */
        if (isset($response['result']) && $response['result'] != 'success') 
        {
            /** The data parameter is decoded */
            $response['data'] = $this->GetComponent("encryption")->DecodeData($response['data']);
            /** The error message is displayed by the error handler */
            $this->GetComponent("errorhandler")->ErrorHandler($response['data']['error_level'], $response['data']['error_message'], $response['data']['error_file'], $response['data']['error_line'], $response['data']['error_details']);
            
        }
        /** The api response is returned */
        return $response;
    }
    /**
     * This function is used to call the XML-RPC functions of the given server
     *
     * It calls the XML-RPC function given in the function parameters
     * It uses the php xmlrpc extension
     *
     * @param array $parameters it is an array with 2 keys:
     * rpc_function => the name of the RPC function
     * rpc_function_parameters => the parameters used by rpc function
     *
     * @return string $response the response from the xml rpc server
     */
    final public function MakeXmlRpcCall($parameters) 
    {
        /** The RPC function name */
        $rpc_function_name = $parameters['rpc_function'];
        /** The RPC function parameters */
        $rpc_function_parameters = $parameters['rpc_function_parameters'];
        /** The RPC server url */
        $rpc_server_url = $this->GetConfig('wordpress', 'rpc_server_information', 'server_url');
        /** The xml request */
        $xml_request = xmlrpc_encode_request($rpc_function_name, $rpc_function_parameters);
        /** The xml tags are removed. the new lines are also removed */
        $request = str_replace("\n", "", str_replace('<?xml version="1.0" encoding="iso-8859-1"?>', '', $xml_request));
        /** The url of the WordPress XML-RPC server */
        $url = $rpc_server_url;
        $request_headers[] = "Content-type: text/xml";
        $request_headers[] = "Content-length: " . strlen($request);

        $response = $this->GetComponent("filesystem")->GetFileContent($url, "POST", $request, $request_headers);
        
        return $response;
    }
    /**
     * Custom error handling function
     *
     * Used to handle an error
     *
     * @param string $log_message the error log message
     * @param array $error_parameters the error parameters. it contains following keys:
     *    error_level => int the error level
     *    error_type => int [Error~Exception] the error type. it is either Error or Exception
     *    error_message => string the error message
     *    error_file => string the error file name
     *    error_line => int the error line number
     *    error_context => array the error context
     *
     * @return boolean $is_handled indicates if the error was handled
     */
    public function CustomErrorHandler($log_message, $error_parameters) 
    {
        /** The error message is displayed to the user */
        $this->DisplayErrorMessage($error_parameters);
    }
    /**
     * Used to log the given error message using a web hook
     *
     * This function logs the error message to a remote url
     *
     * @param string $module_name the name of the remote module that will handle the error
     * @param array $error_parameters the error parameters. it contains following keys:
     *    error_level => int the error level
     *    error_type => int [Error~Exception] the error type. it is either Error or Exception
     *    error_message => string the error message
     *    error_file => string the error file name
     *    error_line => int the error line number
     *    error_context => array the error context
     * @param boolean $include_query_log indicates if the mysql query log should be sent with the error message
     */
    final public function LogErrorToWebHook($module_name, $error_parameters, $include_query_log) 
    {
        /** The line break is fetched from application configuration **/
        $line_break = $this->GetConfig("general", "line_break");
        /** If the mysql query log should be included */
        if ($include_query_log) 
        {
            /** The server and database information is fetched */
            $server_database_information = $this->GetServerAndDatabaseInformation("all");
        }
        /** If the mysql query log should not be included */
        else 
        {
            /** The server information is fetched */
            $server_database_information = $this->GetServerAndDatabaseInformation("server");
        }
        /** The database information is added to the error parameters */
        $parameters['mysql_query_log'] = $server_database_information['mysql_query_log'];
        /** The server data is added to the error parameters */
        $parameters['server_data'] = $server_database_information['server_data'];
        /** The api key is set in the error parameters */
        $parameters['api_key'] = $this->GetConfig("general", "api_key");
        /** The database type is set */
        $parameters['database_type'] = "mysql";
        /** The request type is set to remote */
        $parameters['request_type'] = "remote";
        /** The log message is set */
        $parameters['log_message'] = $error_parameters['error_message'];
        /** The error parameters are set */
        $parameters['error_parameters'] = $error_parameters;
        /** The error message is sent to remote api */
        $this->MakeApiRequestToRemoteModule("log_error_to_database", $module_name, "json", $parameters, "POST");
    }
    /**
     * Used to log the given error message sent using a web hook
     *
     * This function logs the error message to sent by a web hook to database
     * {@internal context remote api}
     *
     * @param array $parameters the error data
     *    server_data => array the server data
     *    mysql_query_log => string the mysql query log
     *    log_message => string the error log message
     *    error_parameters => array the error parameters
     *        error_level => int the error level
     *        error_type => int [Error~Exception] the error type. it is either Error or Exception
     *        error_message => string the error message
     *        error_file => string the error file name
     *        error_line => int the error line number
     *        error_context => array the error context
     */
    final public function HandleLogErrorToDatabase($parameters) 
    {
        /** The server data is fetched from the error parameters */
        $server_data = $parameters['parameters']['server_data'];
        /** The mysql query log data is fetched from the error parameters */
        $mysql_query_log = $parameters['parameters']['mysql_query_log'];
        /** The error message is logged to database */
        $this->LogErrorToDatabase($parameters['parameters']['error_parameters'], $server_data, $mysql_query_log);
    }
    /**
     * Used to fetch the required database data
     *
     * This function fetches the required data starting from the given offset
     * {@internal context remote api}
     *
     * @param array $parameters the parameters used to fetch the data
     *    data_type => string [error_data~access_data] the required data type
     *    site_url => string the site url
     *    offset => int the start index of the log data
     *    limit => int the number of rows of the log data to fetch
     *
     * @return mixed $error_data the required error data. it can be either the error data or the number of error data rows
     */
    final public function HandleGetLogData($parameters) 
    {
        /** The mysql table name containing the data */
        $table_name = $this->GetConfig("general", "mysql_table_names", $parameters['parameters']['data_type']);
        /** The logging information */
        $logging_information = array(
            "database_object" => $this->GetComponent("frameworkdatabase") ,
            "table_name" => $table_name
        );
        /** The parameters for fetching log data */
        $log_parameters = array();
        $log_parameters[] = array(
                "field_name" => "site_url",
                "field_value" => $parameters['parameters']['site_url']
        );
        /** The log data is fetched from database */
        $log_data = $this->GetComponent("logging")->FetchLogDataFromDatabase($logging_information, $log_parameters);
        /** The updated log data. It does not contain the urls containing get_log_data string */
        $updated_log_data = array();
        /** Each log data item is checked */
        for ($count = 0; $count < count($log_data); $count++) {
                /** If the browser field does not contain the term 'bot' then it is added */
                if ((isset($log_data[$count]['browser']) && strpos(strtolower($log_data[$count]['browser']), "bot") === false) || (!isset($log_data[$count]['browser']))) {
                    $updated_log_data[] = $log_data[$count];
                }            
        }       
        /** If the number of rows is required then it is returned */
        if ($parameters['parameters']['type'] == "number_of_rows") {
            $log_data   = count($updated_log_data);
        }
        else {
            /** The data at the required offset is fetched */
            $log_data       = array_slice($updated_log_data, $parameters['parameters']['offset'], $parameters['parameters']['limit']);
        }
        
        return $log_data;
    }
    /**
     * Used to clear the log data from database
     *
     * This function clears the log data of the given type from database
     * {@internal context remote api}
     *
     * @param array $parameters the parameters used to fetch the data
     *    data_type => string [error_data~access_data] the required data type
     *    site_url => string the site url
     *
     * @return boolean $is_cleared indicates if the log data was successfully cleared from database
     */
    final public function HandleClearLogData($parameters) 
    {
        /** The mysql table name containing the data */
        $table_name = $this->GetConfig("general", "mysql_table_names", $parameters['parameters']['data_type']);
        /** The logging information */
        $logging_information = array(
            "database_object" => $this->GetComponent("frameworkdatabase") ,
            "table_name" => $table_name
        );
        /** The parameters for fetching log data */
        $log_parameters = array();
        $log_parameters[] = array(
                "field_name" => "site_url",
                "field_value" => $parameters['parameters']['site_url']
        );
        /** The log data is cleared from local database */
        $this->GetComponent("logging")->ClearLogDataFromDatabase($logging_information, $log_parameters);
        
        $is_cleared = true;
        
        return $is_cleared;
    }
    /**
     * Enable cross domain ajax calls
     *
     * It sends http headers that allow ajax calls from all domains
     * If the application sends a http request with method OPTION, then the request is terminated
     */
    public function EnableCrossDomainAjaxCalls() 
    {
        /** It sends http header for allowing cross domain ajax calls */
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: X-Requested-With");
        /** If the client sent the http request with http method 'OPTIONS', then the application ends */
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') die();
    }
}
