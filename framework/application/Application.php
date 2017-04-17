<?php

namespace Framework\Application;
use \Framework\Configuration\Base as Base;

/**
 * This class implements the base Application class
 *
 * It provides workflow related functions
 * The class should be inherited by the user application class
 *
 * @category   Framework
 * @package    Application
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
abstract class Application extends Base
{
    /**
     * Main application function
     * All requests to the application are handled by this function
     * It calls the function for handling the current request
     *
     * It checks the option to function mappings in the application configuration
     * And calls the function that is defined for the requested option
     * If no option to function mapping is found then a mapping is auto generated
     * The auto generated mapping function is then called
     *
     * @throws object Exception an exception is thrown if the mapping function was not found
     * @throws object Exception an exception is thrown if application is in test mode and invalid test type is given in configuration
     * valid test types are unit and functional
     *
     * @return array $function_output the application response or empty if the application is being tested
     */
    public function Main() 
    {
        /** The application response. it contains the string that will be returned by the application as output */
        $response = "";
        /** The application url mappings */
        $application_url_mappings = $this->GetConfig("general", "application_url_mappings");
        /** The application option */
        $option = $this->GetConfig("general", "option");
        /** The application function output */
        $function_output = array(
            "result" => "",
            "data" => ""
        );
        /** If the application is not in test mode then the url request is handled */
        if (!$this->GetConfig('testing', 'test_mode')) 
        {
            /** The application function output */
            $function_output = $this->RunApplicationFunction($option);
            /** If the save_test_data option is set to true then the page parameters are saved to test_data folder */
            if ($this->GetConfig('testing', 'save_test_data')) 
            {
                /** The callback function for the current option is feched */
                $callback = $this->GetOptionCallback($option);
                /** The application parameters are saved as test parameters */
                $this->GetComponent('testing')->SaveTestData($callback['object_name'], $callback['function'], $callback['function_type'], $function_output['output_format']);
            }
        }
        /** If the application is in test mode then the testing function is called for the current test parameters */
        else 
        {
            /** The short name of the test class */
            $test_class                 = $this->GetConfig("testing", "test_classes", "0");
            /** The application test class object is fetched */
            $application_test_class_obj = $this->GetComponent($test_class);
            /** If the application needs to be functional tested then all application urls are tested */
            if ($this->GetConfig('testing', 'test_type') == "functional") $application_test_class_obj->RunFunctionalTests();
            /** If the application needs to be functional tested from database then all functions given in test data are tested */
            else if ($this->GetConfig('testing', 'test_type') == "functional-from-database") $application_test_class_obj->RunFunctionalTestsFromDatabase();
            /** If the application needs to be unit tested then only given test class with be tested with test parameters */
            else if ($this->GetConfig('testing', 'test_type') == "unit") $application_test_class_obj->RunUnitTests();
            /** If the application script needs to be called then the test class given in application configuration will be used */
            else if ($this->GetConfig('testing', 'test_type') == "script") $application_test_class_obj->CallScript();
            /** If some other test type is given then an exception is thrown */
            else throw new \Exception("Invalid test type given in application configuration");
        }
        return $function_output['data'];
    }
    /**
     * Used to run the function for the given option
     *
     * It calls a controller function or a template function for the given option
     * If no controller function or template function is defined in the application configuration
     * Then a controller function name is auto generated from the application option and the corresponding function is called
     *
     * @param string $option the application option
     * @throws Exception an object of type Exception is thrown if no function was found for the given option
     *
     * @return array $function_output the application function output and the function type
     *    data => mixed the application function response
     *    function_type => the function type
     *    output_format => the output format
     */
    final public function RunApplicationFunction($option) 
    {
        /** The response of the application function */
        $response = "";
        /** The application url mappings */
        $application_url_mappings = $this->GetConfig("general", "application_url_mappings");
        /** The type of function */
        $function_type = (isset($application_url_mappings[$option]['function_type'])) ? $application_url_mappings[$option]['function_type'] : $this->GetConfig("general", "default_function_type");
        /** The application parameters */
        $parameters = $this->GetConfig("general", "parameters");
        /** If the option is given in application configuration along with parameters */
        if (isset($application_url_mappings[$option]) && isset($application_url_mappings[$option]['parameters'])) 
        {
            /** The option parameters given in the url are merged with the option parameters given in configuration file */
            $parameters = array_merge($application_url_mappings[$option]['parameters'], $parameters);
            /** The application configuration is updated */
            $this->SetConfig("general", "parameters", $parameters);
        }
        /** If the output format is given in application configuration, then it is used */
        if (isset($application_url_mappings[$option]['output_format'])) 
        {
            $output_format = $application_url_mappings[$option]['output_format'];
        }
        /** If the output format is given in url parameters, then it is used */
        else if (isset($parameters['output_format'])) 
        {
            $output_format = $parameters['output_format'];
        }
        /** Otherwise the default output format is used */
        else 
        {
            $output_format = $this->GetConfig("general", "default_output_format");
        }
        /** The option callback is fetched */
        $callback = $this->GetOptionCallback($option);
        /** If the parameters key is not set */
        if (!isset($parameters['parameters'])) $validation_parameters = array("parameters" => $parameters);
        else $validation_parameters = $parameters;
        
        /** The application request is pre processed */
        $this->PreProcessRequest($option, $callback['object'], $callback['function'], $validation_parameters, $function_type);
        /** The call back function */
        $callback_function = $callback['function'];
        /** If the function type is controller then callback function is called */
        if ($function_type == "controller") $response = $callback['object']->$callback_function($parameters);
        /** If the function type is template or recursive template then the application template is rendered */
        else if ($function_type == "template" || $function_type == "recursive template") 
        {
            /** The template is rendered */
            $response = $this->GetComponent("template")->Render("root", array());
        }
        /** If the function type is user interface then callback function is called */
        else if ($function_type == "user interface")
        {
            /** The call back function */
            $callback_function = $callback['function'];
            /** The call back function is called with the given parameters */
            $data = $callback['object']->$callback_function($parameters);
            /** The data is read by the user interface object */
            $callback['object']->Read($data);
            /** The data is displayed by the user interface object */
            $response = $callback['object']->Display();
        }
        /** The processed response. The application request is post processed */
        $processed_response = $this->PostProcessRequest($option, $callback['object'], $callback['function'], $response, $output_format, $validation_parameters);
        /** The function output */
        $function_output = array(
            "data" => $processed_response['formatted_output'],
            "function_type" => $function_type,
            "output_format" => $output_format
        );
        
        return $function_output;
    }
    /**
     * Used to echo output
     *
     * It simply echoes the given data
     * If the data is an array, then it is json encoded first
     *
     * @param mixed $text the text to echo. it can be an array or a string
     */
    final public function DisplayOutput($text) 
    {
        /** If the given data is an array, then it is json encoded */
        if (is_array($text)) 
        {
            $text = json_encode($text);
        }
        echo $text;
    }    
    /**
     * Used to display the error message
     *
     * This function displays the error message to the user
     * It stops script execution
     *
     * @param array $error_parameters the error parameters. it contains following keys:
     *    error_level => int the error level
     *    error_type => int [Error~Exception] the error type. it is either Error or Exception
     *    error_message => string the error message
     *    error_file => string the error file name
     *    error_line => int the error line number
     *    error_context => array the error context
     */
    final public function DisplayErrorMessage($error_parameters) 
    {
        /** The response format for the application request */
        $output_format = 'json';
        /** The error parameters are encoded */
        $error_parameters = $this->GetComponent("encryption")->EncodeData($error_parameters);
        /** The response is converted to an array. Error message is added to response */
        $response = array(
            "result" => "error",
            "data" => $error_parameters
        );
        /** The response is json encoded */
        $response = json_encode($response);
        /** The error response from api is displayed */
        die($response);
    }
    /**
     * Used to return the callback function for the option
     *
     * It fetches the object and function name from application configuration
     *
     * @param string $option the url option
     * @throws Exception an object of type Exception is thrown if the option callback is not callable
     *
     * @return array $callback
     *    object => the callback object
     *    function => the callback function
     *    object_name => the name of the callback object
     *    function_type => the type of callback
     */
    final public function GetOptionCallback($option) 
    {
        /** The application url mappings are fetched from application configuration */
        $application_url_mappings = $this->GetConfig('general', 'application_url_mappings');
        /** If the function type is recursive template, then the callback function of the first template item is used as the option callback */
        if (isset($application_url_mappings[$option]) && $application_url_mappings[$option]['function_type'] == "recursive template") 
        {
            $function_name = $application_url_mappings[$option]['templates'][0]['function_name'];            
            $object_name = $application_url_mappings[$option]['templates'][0]['object_name'];
            $function_type = $application_url_mappings[$option]['function_type'];
        }
        else
        {
            /** If the controller function name is set in application url mappings then it is used */
            if (isset($application_url_mappings[$option]['function_name'])) $function_name = $application_url_mappings[$option]['function_name'];
            /** Otherwise the function name is generated from the url option */
            else $function_name = $this->GetComponent("string")->Concatenate("Handle", $this->GetComponent("string")->CamelCase($option));
            /** If the name of the object was set in the application url mappings */
            if (isset($application_url_mappings[$option]['object_name'])) 
            {
                $object_name = $application_url_mappings[$option]['object_name'];
            }
            else
            {
                $object_name = $this->GetConfig("general", "object_name");
            }
        }
        
        /** The function type for the option is set */
        if (isset($application_url_mappings[$option])) 
        {
            $function_type = $application_url_mappings[$option]['function_type'];
        }
        else $function_type = "controller";
        /** The controller object is fetched */
        $controller_object = $this->GetComponent($object_name);
        /** If the url mapping is not callable then an exception is thrown */
        if (!is_callable(array($controller_object, $function_name)))
        {
            /** If the object contains a function called HandleUsage, then it is called and the application execution ends. The function should display usage of the application */
            if (is_callable(array($controller_object, "HandleUsage"))) {
                /** The HandleUsage function is called */
                call_user_func_array(array($controller_object, "HandleUsage"), array());
                /** The application execution ends */
                die();
            }
            /** If the object does not contain the HandleUsage function, then an exception is thrown */
            else
                throw new \Exception("Invalid url request sent to application");
        }
        $callback = array(
            "object" => $controller_object,
            "function" => $function_name,
            "object_name" => $object_name,
            "function_type" => $function_type
        );
        
        return $callback;
    }
    /**
     * Used to create an encoded url for the application
     *
     * It creates a url that can optionally include encoded parameters
     * A parameters base64 encoded
     * Array parameters are first json encoded
     *
     * @param array $parameters the parameters used to create the encoded url
     *    string $option the url option
     *    string $module_name the name of the module
     *    array $parameters the list of url parameters. it is an associative array. if set to false then the parameters are not used
     *    boolean $is_link used to indicate if url will be used in link. if it will be used in link then url & will be
     *    string optional $url the server url. if omitted then the framework url is used
     *    string optional object_name the name of the object that should be used to handler the request
     *        if this is not set then the object name is automatically calculated as the name of the object that called this function
     *    boolean $encode_parameters used to indicate if the url parameters should be encoded
     *
     * @return string $formatted_url the formatted url is returned
     */
    public function GenerateUrl($parameters)
    {
        /** The backtrace for the current function is generated */
        $backtrace = debug_backtrace();
        /** The class name of the object that is calling the current function */
        $class_name = '\\' . get_class($backtrace[1]['object']);
        /** The list of all classes required by the application is fetched */
        $required_class_list = $this->GetConfig("required_objects");
        /** If the object_name parameters was not given */
        if ($parameters['object_name'] == "") 
        {
            /** The short object name is set to application */
            $object_name = "application";
            /** Each class in the list is checked */
            foreach ($required_class_list as $object_class_name => $value) 
            {
                /** If the full class name of the object is found then the short name of the object is used */
                if ($value['class_name'] == $class_name) 
                {
                    $object_name = $object_class_name;
                }
            }
        }
        /** The web application base url. If not given as a parameter then it is fetched from application configuration */
        $web_application_url = ($parameters['url'] != "") ? $parameters['url'] : $this->GetConfig("path", "framework_url");
        /** The url parameters */
        $url_parameters = array(
            "option" => $parameters['option'],
            "module" => $parameters['module_name'],
            "output_format" => $parameters['output_format'],
            "object_name" => $parameters['object_name']
        );
        /** The given url parameters are added if they are given */
        if (is_array($parameters['parameters'])) $url_parameters = array_merge($url_parameters, $parameters['parameters']);
        /** If the url parameters need to be encoded */
        if ($parameters['encode_parameters']) 
        {
            /** Each url parameter is encoded */
            $url_parameters = $this->EncodeParameters($url_parameters);
        }
        /** If the url will be used in a tag link then the parameters are separated by &amp; */
        $separator = ($parameters['is_link']) ? "&amp;" : "&";
        /** The encoded url */
        $encoded_url = $web_application_url . "?" . http_build_query($url_parameters, '', $separator);
        return $encoded_url;
    }
    /**
     * It encodes the given url parameters
     *
     * It encodes each url parameter value
     *
     * @param array $url_parameters the url parameters that need to be encoded
     * @throws object Exception an exception is thrown if the length of an encoded parameter value is more than 400
     *
     * @return array $encoded_url_parameters the encoded url parameters
     */
    final protected function EncodeParameters($url_parameters) 
    {
        /** The encoded parameters array is initialized */
        $encoded_url_parameters = array();
        /** Each $parameters is encoded */
        foreach ($url_parameters as $key => $value) 
        {
            /** The application context, object name and module are not encoded */
            if ($key == "module" || $key == "context" || $key == "object_name") 
            {
                /** The url parameters are encoded */
                $encoded_url_parameters[$key] = $value;
            }
            else
            {
                /** The url parameters are encoded */
                $encoded_url_parameters[$key] = ($this->GetComponent("encryption")->EncodeData($value));
                /** An exception is thrown if encoded parameter length is larger then 400 characters */
                /** if (strlen($encoded_url_parameters[$key]) > 400)
                 throw new \Exception("The size of the encoded parameter: ".$key." must be less than 400 characters");
                 */
            }
        }
        return $encoded_url_parameters;
    }
    /**
     * Used to run the application
     *
     * It runs the correct application module
     * If the module name is not given, then it checks each module to see if it should handle the request
     *
     * @param string $module the application module
     * @param array $parameters the application parameters
     *
     * @return string $response the application response
     */
    final private static function RunApplication($module, $parameters) 
    {
        /** Indicates that the request was handled. It is set to false by default */
        $is_handled = false;
        /** If the module name was not given, then each module is checked */
        if (!$module) 
        {
            /** The path to the framework parent folder */
            $folder_path = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
            /** The full path to all the module Configuration.php files */
            $configuration_files_path = \Framework\Utilities\UtilitiesFramework::Factory("filesystem")->GetFolderContents($folder_path, 2, "Configuration.php", "framework");
            /** Each configuration file is checked to see if it should handle the current request */
            for ($count = 0;$count < count($configuration_files_path);$count++) 
            {
                /** The configuration file name */
                $configuration_file_name = $configuration_files_path[$count];
                /** The relative file path is determined */
                $relative_file_path = str_replace($folder_path, "", $configuration_file_name);
                /** The class name . DIRECTORY_SEPARATOR is removed from the relative file path */
                $class_name = str_replace(DIRECTORY_SEPARATOR, '\\', $relative_file_path);
                /** The .php extension is removed from the relative file path */
                $class_name = str_replace(".php", "", $class_name);
                /** If the class name is not valid then the loop continues; */
                if (!class_exists($class_name)) continue;
                /** The default module name is determined */                
                $module_name = trim(str_replace("Configuration", "", $class_name) , '\\');
                $module_name = trim(substr($module_name, strrpos($module_name, '\\')) , '\\');
                /** If the class should handle the current request */
                if ($class_name::IsValidRequest($module_name)) 
                {
                    /** An instance of the required module is created */
                    $configuration = new $class_name($parameters);
                    /** The application output */
                    $response = $configuration->RunApplication();
                    /** Indicates that the request was handled */
                    $is_handled = true;
                    /** No need to check other modules */
                    break;
                }
            }
        }
        /** If the module name is given */
        else 
        {
            /** The application configuration class name */
            $class_name = \Framework\Utilities\UtilitiesFramework::Factory("strings")->Concatenate('\\', $module, '\\', "Configuration");
            /** An instance of the required module is created */
            $configuration = new $class_name($parameters);
            /** Indicates that the request was handled */
            $is_handled = true;
            /** The application output */
            $response = $configuration->RunApplication();
        }
     
        /** If the application request was not handled then an exception is thrown */
        if (!$is_handled) die("No module was found for application request");       
        return $response;
    }
    /**
     * Used to handle application request
     *
     * It is the main entry point for the application
     * It initializes the application and returns the application response depending on the application context
     *
     * @param string $context the context of the application. e.g local api, remote api, browser or command line
     * @param array $parameters the application parameters
     * @param string $default_module optional the default module name to use in case no module name is specified by the calling application
     *
     * @throws object Exception exception is thrown if an invalid context was specified
     * @throws object Exception exception is thrown if module name was not specified by the application
     * @throws object Exception exception is thrown if the application context is command line and command line arguments are not in correct format
     *
     * @return string $response the application response
     */
    public static function HandleRequest($context, $parameters, $default_module = false) 
    {
        /** The application module to be called */
        $module = $default_module;
        /** If the application is being run from browser */
        if ($context == "browser" || $context == "local api" || $context == "remote api") 
        {
            /** The current module name is determined */
            $module = isset($parameters['module']) ? $parameters['module'] : $default_module;
            /** The application context is added to the application parameters */
            $parameters['context'] = $context;
        }
        /** If the application is being run from command line then the module name is determined */
        else if ($context == "command line") 
        {
            /** The updated application parameters in standard key => value format */
            $updated_parameters = array();
            /** The application context is added to the application parameters */
            $updated_parameters['context'] = $context;
            /** The application parameters are determined */
            for ($count = 1; $count < count($parameters) && isset($parameters[1]); $count++) 
            {
                /** Single command line argument */
                $command = $parameters[$count];
                /** If the command does not contain equal sign then an exception is thrown. only commands of the form --key=value are accepted */
                if (strpos($command, "--") !== 0 || strpos($command, "=") === false) die("Invalid command line argument was given. Command line arguments: " . var_export($parameters, true));
                else 
                {
                    $command = str_replace("--", "", $command);
                    list($key, $value) = explode("=", $command);
                    $updated_parameters[$key] = $value;
                }
            }
            /** The parameters are set */
            $parameters = $updated_parameters;
            /** The application module name is set */
            $module = (isset($parameters['module'])) ? $parameters['module'] : $default_module;
        }
        /** If an invalid application context is given then an exception is thrown */
        else die("Invalid application context: " . $context);
        /** The application request is handled */
        $response = self::RunApplication($module, $parameters);
      
        /** The output is returned */
        return $response;
    }
    /**
     * Custom error handling function
     *
     * Used to handle an error
     *
     * @param string $log_message the error log message
     * @param array $error_parameters the error parameters. it contains following keys:
     * error_level=> the error level
     * error_message=> the error message
     * error_file=> the error file name
     * error_line=> the error line number
     * error_context=> the error context
     */
    public function CustomErrorHandler($log_message, $error_parameters) 
    {
        /** The line break is fetched from application configuration **/
        $line_break = $this->GetConfig("general", "line_break");
        /** Lines breaks are added to the log message */
        $log_message = $log_message . $line_break . $line_break;
        /** The mysql query log and server information is fetched */
        $server_database_information = $this->GetServerAndDatabaseInformation("all");
        /** The mysql query log is appended to the log message */
        $log_message = $log_message . "MySQL query log: " . $line_break . $line_break . $server_database_information['mysql_query_log'];
        /** The error message is displayed to the browser and the application execution ends */
        if ($this->GetConfig("general", "log_error_to_database"))$this->LogErrorToDatabase($error_parameters, $server_database_information['server_data'], $server_database_information['mysql_query_log']);
        /** If the application is in production mode then a 404 error is sent */
        if (!$this->GetConfig("general", "development_mode")) {
            /** If http headers have not been sent, then 404 status http header is sent */
            if (!headers_sent()) {
                header("HTTP/1.0 404 Not Found");
                die("<h2>Page Not Found !</h2>");
            }
        }
        else {
            /** If the application is in development mode, then the error message is displayed and script execution ends */
            die($log_message);
        }
    }
    /**
     * Used to get server and database information
     *
     * It returns the mysql query log and also the server information
     *
     * @param string $type [server~database~all] optional the type of information that is needed. it can be either all, database or server
     *
     * @return array $server_database_information server and database information
     *    mysql_query_log => string the mysql query log
     *    server_data => array the server data
     *        remote_host => string the remote http host
     *        http_host => string the http host
     *
     */
    public function GetServerAndDatabaseInformation($type = "all") 
    {
        /** The server and database information is initialized */
        $server_database_information = array(
            "mysql_query_log" => "",
            "server_data" => ""
        );
        /** If all data is required or only database information is required */
        if ($type == "all" || $type == "database") 
        {
            /** The mysql query log */
            $server_database_information['mysql_query_log'] = $this->GetComponent("database")->df_display_query_log(true);
        }
        /** The site url is added to the error message */
        $site_url = $this->GetConfig("general", "site_url");
        /** If all data is required or only server information is required */
        if ($type == "all" || $type == "server") 
        {
            /** The http host */
            $http_host = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $this->GetConfig("general", "api_server");
            /** The remote host ip */
            $remote_addr = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1";
            /** The request uri */
            $request_uri = (isset($_SERVER['REQUEST_URI'])) ? $site_url . trim($_SERVER['REQUEST_URI'], "/") : "";
            /** The application parameters */
            $parameters = $this->GetConfig("general", "parameters");
            /** The http user agent */
            $http_user_agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "command line";
            /** The server data */
            $server_database_information['server_data'] = array(
                "remote_addr" => $remote_addr,
                "http_host" => $http_host,
                "parameters" => $parameters,
                "request_uri" => $request_uri
            );
        }
        return $server_database_information;
    }
    /**
     * The default application shutdown function
     *
     * Closes the database connections created by the application
     * This function is registered as a shutdown function with the ErrorHandling class
     */
    public function CustomShutdownFunction() 
    {
        /** If the enable code coverage option is enabled then the code coverage for current request is collected and saved to database */
        if ($this->GetConfig("testing", "enable_code_coverage")) $this->GetComponent("testing")->SaveCodeCoverage();
        /** The database connection is closed **/
        $this->GetComponent("database")->df_close();
    }
    /**
     * Used to pre process the application request
     *
     * This function is called before an application request is processed
     * It is used to validate the request parameters
     * This method should be overridden by child classes
     *
     * @throws object an object of type Exception is thrown if the function parameters cannot be validated
     * @param string $option the application option
     * @param object $controller_object the object that contains the api function
     * @param string $function_name the api function name
     * @param array $parameters the parameters for the callback function
     * @param string $function_type the type of function
     */
    protected function PreProcessRequest($option, $controller_object, $function_name, $parameters, $function_type) 
    {
        /** If the user access should be logged then it is logged */
        if ($this->GetConfig("general", "log_user_access")) {
          /** The execution time for the request */
          $execution_time = $this->GetComponent("profiling")-> StartProfiling('execution_time');
        }
        /** If the type of function is not controller, then the function returns */
        if ($function_type != "controller") return;
        /** The current context of the application */
        $context = $this->GetConfig("general", "parameters", "context");
        /** The custom validation callback */
        $custom_validation_callback = array(
            $this,
            "ValidateFunctionParameter"
        );
        /** The reflection object is fetched */
        $reflection = $this->GetComponent("reflection");
        /**
         * The result of validating the parameters
         * Method parameters are validated against the information in the Doc Block comments
         */
        $validation_result = $reflection->ValidateMethodParametersAndContext($controller_object, $function_name, $context, $parameters, $custom_validation_callback);
        /** If the method parameters cannot be validated, then an exception is thrown */
        if (!$validation_result['is_valid']) 
        {
            throw new \Exception("Function could not be validated. Details: " . $validation_result['validation_message']);
        }
        return $validation_result;
    }
    /**
     * Used to post process the application request
     *
     * This function is called after an application request has been handled
     * It formats the output of the request handling function according to the information given in the Doc Block comments
     * It also validates the output of the function against the return type given in the Doc Block comments
     * It also saves the details of function execution to database
     * For example execution time, ip address and function parameters
     *
     * @param string $option the application option
     * @param object $controller_object the object that contains the api function
     * @param string $function_name the api function name
     * @param mixed $response the api function response
     * @param string $output_format the function output format
     * @param array $parameters the value of all the method parameters
     *
     * @return array $processed_response an array containing the formatted output and validation results
     *    formatted_output => string formatted output
     *    validation_result => array the result of validating the function
     *        is_valid => boolean indicates if the parameters are valid
     *        validation_message => string the validation message if the parameters could not be validated
     */
    protected function PostProcessRequest($option, $controller_object, $function_name, $response, $output_format, $parameters) 
    {
        /** The processed response */
        $processed_response = array();
        /** The custom validation callback */
        $custom_validation_callback = array(
            $this,
            "ValidateFunctionParameter"
        );
        /** The reflection object is fetched */
        $reflection = $this->GetComponent("reflection");
        /** The result of validating the return value. The return value is validated against the information in the Doc Block comments */
        $validation_result = $reflection->ValidateMethodReturnValue($controller_object, $function_name, $response, $custom_validation_callback, $parameters);
        /** The validation result */
        $processed_response['validation_result'] = $validation_result;
        /** If the output is not valid */
        if (!$processed_response['validation_result']['is_valid']) 
        {
            /** If the output format is not html */
            if ($output_format != "html") 
            {
                /** The response is converted to an array. error string is added to response */
                $response = array(
                    "result" => "error",
                    "data" => $processed_response['validation_result']['validation_message']
                );
            }
            /** If the output format is html then response is replaced with error message */
            else $response = $processed_response['validation_result']['validation_message'];
        }
        /** If the response format is not html and not xml then response is converted to array */
        else if ($output_format != "html" && $output_format != "xml") 
        {
            /** The response is converted to an array. Success string is added to response */
            $response = array("result" => "success", "data" => $response);
        }
        /** If the required output format is json or encrypted json */
        if ($output_format == "json" || $output_format == "encrypted json") 
        {
            /** The response is json encoded */
            $response = json_encode($response);
            /** If the output format is encrypted json */
            if ($output_format == "encrypted json") 
            {
                /** The json string is encrypted */
                $response = $this->GetComponent("encryption")->EncryptText($response);
            }
        }
        $processed_response['formatted_output'] = $response;
        /** If the user access should be logged then it is logged */
        if ($this->GetConfig("general", "log_user_access")) {
          /** The url request is logged */
          $this->GetComponent("application")->LogUserAccess();
        }
        
        return $processed_response;
    }
    /**
     * It saves the api access information to database
     *
     * It saves the api access data to database log table
     */
    final public function LogUserAccess() 
    {     
        /** The current application request method */
        $request_method     = $this->GetConfig("general", "request_method");
        /** The current application parameters */
        $parameters     = $this->GetConfig("general", "parameters");
        /** The execution time for the request */
        $execution_time = $this->GetComponent("profiling")->GetExecutionTime();
        /** The mysql table name where the api data will be logged */
        $api_table_name = $this->GetConfig("general", "mysql_table_names", "access_data");
        /** The current url */
        $url            = $this->GetConfig("path", "current_url");
        /** If the current http request method is post */
        if ($request_method == "POST") {
            /** If the url does not contain '?' */
            if (strpos($url, "?") === false)
                $url        .= "?" . http_build_query($parameters);
            else
                $url        .= "&" . http_build_query($parameters);
        }
        /** If the url contains get_log_data or clear_log_data then it is not logged */
        if (strpos($url, "get_log_data") !== false || strpos($url, "clear_log_data") !== false) return;
        /** The site url */
        $site_url       = $this->GetConfig("general", "site_url");
        
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['REMOTE_ADDR'])) $ip_address = $_SERVER['REMOTE_ADDR'];
        else $ip_address = "script";
        
        /** The http user agent field */
        $http_user_agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "script";
        /** The logging information */
        $logging_information = array(
            "database_object" => $this->GetComponent("frameworkdatabase") ,
            "table_name" => $api_table_name
        );
        /** The api access data that needs to be logged */
        $api_access_data = array(
            "url" => $url,
            "ip_address" => $ip_address,
            "browser" => $http_user_agent,
            "site_url" => $site_url,            
            "time_taken_(sec)" => $execution_time,
            "created_on" => time()
        );
       
        /** The parameters for saving log data */
        $parameters = array(
            "logging_information" => $logging_information,
            "logging_data" => $api_access_data,
            "logging_destination" => "database",
        );
        /** The test data is saved to database */
        $this->GetComponent("logging")->SaveLogData($parameters);
    }
    /**
     * Used to validate certain function parameters
     *
     * It checks if the given function parameter is valid
     *
     * @internal
     * @param string $parameter_name the name of the parameter
     * @param string $parameter_value the value of the parameter
     * @param array $all_parameter_values the value of all the method parameters
     * @param array $all_parsed_parameters details of all the method parameters
     * @param array $all_return_values the return value of the function
     * @param array $all_return_parameters details of all the return value parameters
     * @param bool $is_return if set to true then the return value needs to be validated
     *
     * @return array $validation_result the result of validating the method parameters
     *    is_valid => boolean indicates if the parameters are valid
     *    validation_message => string the validation message if the parameters could not be validated
     */
    public function ValidateFunctionParameter($parameter_name, $parameter_value, $all_parameter_values, $all_parsed_parameters, $all_return_values, $all_return_parameters, $is_return) 
    {
        /** The result of validating the parameter */
        $validation_result = array(
            "is_valid" => true,
            "validation_message" => ""
        );
        return $validation_result;
    }
    /**
     * Used to save the error message to database
     *
     * This function formats the error message and saves it to database
     *
     * @param array $error_parameters the error parameters. it contains following keys:
     *    error_level => int the error level
     *    error_type => int [Error~Exception] the error type. it is either Error or Exception
     *    error_message => string the error message
     *    error_file => string the error file name
     *    error_line => int the error line number
     *    error_context => array the error context
     * @param array $server_data the information about the server that sent the error data
     */
    final protected function LogErrorToDatabase($error_parameters, $server_data, $mysql_query_log) 
    {
        /** The line break is fetched from application configuration **/
        $line_break = $this->GetConfig("general", "line_break");
        /** The server data is added to the error data */
        $error_parameters['server_data'] = $server_data;
        /** The mysql query log is added to the error data */
        $error_parameters['mysql_query_log'] = $mysql_query_log;
        /** The error parameters are encoded */
        $error_parameters = $this->EncodeParameters($error_parameters);
        /** The timestamp is added to the error message */
        $error_parameters['created_on'] = time();
        /** The site url is added to the error message */
        $error_parameters['site_url'] = $this->GetConfig("general", "site_url");
        /** The mysql table name where the error data will be logged */
        $error_table_name = $this->GetConfig("general", "mysql_table_names", "error_data");
        /** The logging information */
        $logging_information = array(
            "database_object" => $this->GetComponent("frameworkdatabase") ,
            "table_name" => $error_table_name
        );
        /** The parameters for saving log data */
        $parameters = array(
            "logging_information" => $logging_information,
            "logging_data" => $error_parameters,
            "logging_destination" => "database"
        );
        /** The error data is saved to database */
        $this->GetComponent("logging")->SaveLogData($parameters);
    }
    /**
     * Used to authenticate the client using api authentication
     *
     * This function checks the api key given in application parameters
     * The api key is checked against the valid api key given in application configuration
     */
    final public function ApiAuthentication() 
    {
        /** The application parameters containing api data are fetched **/
        $api_auth = $this->GetConfig("api_auth");
        /** The application parameters are fetched */
        $application_parameters = $this->GetConfig("general", "parameters");
        /** The API key */
        $api_key = "";
        /** The API key is fetched */
        if (isset($application_parameters['api_key'])) 
        {
            $api_key = $application_parameters['api_key'];
        }
        else if (isset($application_parameters['parameters']['api_key'])) 
        {
            $api_key = $application_parameters['parameters']['api_key'];
        }
        /** If the api key is not set in the application parameters then an exception is thrown */
        if ($api_key == "") throw new \Exception("API Key was not given in application parameters");
        if (!isset($api_auth['credentials'])) 
        {
            die("API credentials not stored");
        }
        else
        {
            if ($api_auth['credentials'] != $api_key) die("Invalid API Key: " . $api_key);
        }
    }
    /**
     * Used to route the function call to an application defined function
     *
     * This function works as a router function
     * It transforms the parameters into object name and function name
     * It then calls the object's function
     * It should be overriden by child classes
     * The child class definition of the function should specify the transformation of the parameters into object name and function name
     *
     * @param string $object_description the description of the object. by default it is considered to be a short object name
     * @param string $function_description the description of the function
     * @param string $function_parameters the parameters for the function
     *
     * @return mixed $response the response from the function
     */
    final public function RouteFunction($object_description, $function_description, $function_parameters) 
    {
        /** The routing function callback object is fetched */
        $object = $this->GetComponent($object_description);
        /** The routing function */
        $function = $function_description;
        /** The routing function callback */
        $callback = array(
            $object,
            $function
        );
        /** The routing callback is called */
        $response = call_user_func_array($callback, $function_parameters);
        return $response;
    }
    /** 
     * Used to save the given data to application cache
     * It allows an application to fetch the data later during the same application request
     *
     * @param mixed $data the data to save in application cache. It is merged with the data that already exists in the application cache
     */
    public function UpdateApplicationCache($data) 
    {
        /** The contents of the application cache are fetched */
        $application_cache = $this->GetConfig("general", "application_cache");
        /** If the application cache does not contain data then it is initialized */
        if (!$application_cache) $application_cache = array();
        /** The data is merged with application cache. If the data in the cache contains the same array keys as the new data then it will be overwritten */
        $application_cache = array_merge($application_cache, $data);
        /** The data is saved to application cache */
        $this->SetConfig("general", "application_cache", $application_cache);        
    }
    /** 
     * Used to fetch data from application cache
     * It returns the data inside the application cache
     *
     * @return mixed $application_cache the contents of the application cache
     */
    public function FetchApplicationCache() 
    {
        /** The contents of the application cache are fetched */
        $application_cache = $this->GetConfig("general", "application_cache");
        
        return $application_cache;     
    }
   /**
     * Used to get the long text for the given text key
     *
     * It returns the long text for the given text key
     *
     * @param string $context the context for the text
     * @param string $text_key the text key
     *    
     * @return mixed $long_text the long text is returned for the given key. if the key is not given, then all text for the given context is returned
     */
    public function GetText($context, $text_key = false) 
    {
        /** The translated data is fetched */
        $translated_data = $this->GetConfig("general", "site_text", $context);
        /** The long text */
        $long_text       = (!$text_key) ? $translated_data : $translated_data[$text_key];
        
        return $long_text;
    }
}


