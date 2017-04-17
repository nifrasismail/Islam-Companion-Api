<?php
namespace Framework\Configuration;
use \Framework\Configuration\Base as Base;
/**
 * Default application configuration class
 *
 * Final class. cannot be extended by child class
 * It provides default application configuration
 *
 * @category   Framework
 * @package    Configuration
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class DefaultConfiguration extends Base
{
    /**ST
     * Used to get default general configuration
     *
     * It returns configuration containing general parameters
     *
     * @param array $user_configuration the user configuration
     * @throws object Exception an exception is thrown if the application name was not set in the user configuration
     * @throws object Exception an exception is thrown if no option parameter was given in url and no default option was defined
     *
     * @return array $configuration the default configuration information
    */
    private function GetGeneralConfig($user_configuration) 
    {
        /** If the application name is not set then an exception is thrown */
        if (!isset($user_configuration['general']['application_name'])) throw new \Exception("Application name was not set in configuration settings");
        /** If the application display name is not set then it is set to the application name */
        if (!isset($user_configuration['general']['application_display_name'])) 
        {
            $user_configuration['general']['application_display_name'] = $user_configuration['general']['application_name'];
        }     
        /** If parameters were given by the user */
        if (isset($user_configuration['general']['parameters'])) 
        {            
            /** The given user configuration parameters are decoded since they can be passed in url */
            foreach ($user_configuration['general']['parameters'] as $key => $value) 
            {
                /** If the application context is browser, then the parameters are url decoded */
                if (isset($user_configuration['general']['parameters']['context']) && $user_configuration['general']['parameters']['context'] == 'browser' && !is_array($value)) $value = urldecode($value);
                /** The parameters are decoded */
                $user_configuration['general']['parameters'][$key] = (!is_array($value) && strlen($value) > 4) ? \Framework\Utilities\UtilitiesFramework::Factory("encryption")->DecodeData($value) : $value;
            }
        }
        /** The application context */
        $configuration["general"]["parameters"]["context"] = (isset($user_configuration['general']['parameters']['context'])) ? $user_configuration['general']['parameters']['context'] : 'browser';
        /** The module name is saved to application configuration */
        if (!isset($user_configuration['general']['module'])) $user_configuration['general']['module'] = str_replace(" ", "", $user_configuration['general']['application_name']);
        /** The application option and the option parameters are saved to application configuration */
        if (!isset($user_configuration['general']['default_option'])) $user_configuration['general']['default_option'] = "index";
        $configuration['general']['option'] = isset($user_configuration['general']['parameters']['option']) ? $user_configuration['general']['parameters']['option'] : '';
        $configuration['general']['object_name'] = isset($user_configuration['general']['parameters']['object_name']) ? $user_configuration['general']['parameters']['object_name'] : 'application';
        $configuration['general']['development_mode'] = true;
        $configuration['general']['parameters'] = (isset($user_configuration['general']['parameters'])) ? $user_configuration['general']['parameters'] : array();
        $configuration['general']['parameters']['uploads'] = (isset($_FILES)) ? $_FILES : array();
        /** The HTTP Request Method */
        $configuration['general']['request_method'] = (isset($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : "N.A";
        /** The total number of rows per page */
        $configuration['general']['rows_per_page'] = 20;  
        /** The application url mappings are set */
        $configuration['general']['application_url_mappings'] = array();
        /** The default output format for a function */
        $configuration['general']['default_output_format'] = "html";
        /** Used to indicate if application should use formatted urls */
        $configuration['general']['use_formatted_urls'] = false;
        /** The default function type */
        $configuration['general']['default_function_type'] = "controller";
        /** Used to indicate that the user access should be logged */
        $configuration['general']['log_user_access'] = false;        
        /** Used to indicate that the application should log error to database */
        $configuration['general']['log_error_to_database'] = false;        
        /** The default presentation option */
        $configuration['general']['default_use_presentation'] = false;
        /** Used to indicate if application should use sessions */
        $configuration['general']['enable_sessions'] = false;
        /** The default css files */
        $configuration['general']['default_css_files'] = array();
        /** The default javascript files */
        $configuration['general']['default_javascript_files'] = array();        
        /** The site url */
        if (!isset($user_configuration['general']['site_url'])) $user_configuration['general']['site_url'] = "";
        /** If the output buffering needs to be enabled, then output buffering is started */
        if (isset($user_configuration['general']['enable_output_buffering'])) {
            ob_start();
        }
        else $user_configuration['general']['enable_output_buffering'] = false;
        /** The option for enabling cross domain ajax calls */
        if (!isset($user_configuration['general']['enable_cross_domain_ajax_calls']))
            $user_configuration['general']['enable_cross_domain_ajax_calls'] = false;
        /** The folder name of the application */
        $configuration['general']['application_name'] = "";
        /** The server name of the http api */
        $configuration['general']['api_server'] = "pakjiddat.com";
        /** The url for the http api */
        $configuration['general']['api_url'] = "http://pakjiddat.com/api/index.php";
        /** The custom configuration */
        $configuration['general']['application_cache'] = "";
        /** If the application type is given by the user then it is set */
        if (isset($user_configuration['general']['application_type'])) $configuration['general']['application_type'] = $user_configuration['general']['application_type'];
        /** The ip address of the memcached server */
        if (!isset($user_configuration['general']['memcache_server'])) $configuration['general']['memcache_server'] = "127.0.0.1";
        /** The mappings from database type to database object */
        if (!isset($user_configuration['general']['data_source_class_mapping'])) $configuration['general']['data_source_class_mapping'] = array(
            "wordpress" => "WordPressDataObject",
            "mysql" => "MysqlDataObject"
        );;
        /** The type of database used by the application */
        if (!isset($user_configuration['general']['database_type'])) $configuration['general']['database_type'] = "mysql";
        else $configuration['general']['database_type'] = $user_configuration['general']['database_type'];
        
        /** The DataObject class name is set */
        if ($configuration['general']['database_type'] == "mysql") $configuration['general']['database_object_class'] = "\Framework\Object\MysqlDataObject";
        else if ($configuration['general']['database_type'] == "wordpress") $configuration['general']['database_object_class'] = "\Framework\Object\WordpressDataObject";
        /** The names of the MySQL database tables to be used by the application */
        $configuration['general']['mysql_table_names'] = array(
            "test_data" => "pakphp_test_data",
            "variable_data" => "pakphp_variable_data",
            "error_data" => "pakphp_error_data",
            "access_data" => "pakphp_access_data",
            "cache_data" => "pakphp_cache_data",
            "fields" => "pakphp_form_fields",
            "forms" => "pakphp_forms",
            "field_values" => "pakphp_form_field_values",
        );
        /** The center column index is set */
        $configuration['general']['center_column_index'] = isset($user_configuration['general']['center_column_index']) ? $user_configuration['general']['center_column_index'] : 2;
        /** The line break character for the application is set */
        $configuration['general']['line_break'] = ($configuration["general"]["parameters"]["context"] != "command line") ? "<br/>" : "\n";
        /** If no option is set in url and no default option is given by user an exception is thrown */
        if ($configuration['general']['option'] == "" && isset($user_configuration['general']) && !is_string($user_configuration['general']['default_option'])) throw new \Exception("Option parameter is not given in url and no default option is specified");
        /** If no option is set in url then the default option is used */
        if ($configuration['general']['option'] == "" && isset($user_configuration['general']['default_option'])) $configuration['general']['option'] = $user_configuration['general']['default_option'];
        /** The access log information. It is saved to the api access log */
        $configuration['general']['access_log_information'] = array(
            "request_type" => "",
            "option" => "",
            "parameters" => "",
            "response_format" => "",
            "response" => array(
                "result" => "error"
            )
        );
        /** User configuration is merged */
        $configuration  = array_replace_recursive($configuration, $user_configuration);
       
        return $configuration;
    }
    /**
     * Used to get default authentication configuration
     *
     * It returns configuration containing api, http and session authentication
     *
     * @param array $configuration the default configuration
     * @param array $user_configuration the user configuration
     *
     * @return array $configuration the default configuration information
     */
    private function GetAuthConfig($configuration, $user_configuration) 
    {
        /** The title of the authentication box */
        $configuration['http_auth']['realm'] = "";
        /** The supported authentication methodd */
        $authentication_methods = array(
            "api",
            "http",
            "session"
        );
        /** The default values for each authentication method are set */
        for ($count = 0;$count < count($authentication_methods);$count++) 
        {
            /** The authentication method */
            $authentication_method = $authentication_methods[$count];
            /** Used to indicate if application should be protected by http authentication */
            $configuration[$authentication_method . '_auth']['enable'] = false;
            /** The valid user names and passwords */
            $configuration[$authentication_method . '_auth']['credentials'] = array(
                array(
                    "user_name" => "",
                    "password" => ""
                )
            );
            /** The callback function to call for checking if user is logged in */
            $configuration[$authentication_method . '_auth']['auth_callback'] = "";
            /** User configuration is merged */
            if (isset($user_configuration[$authentication_method . '_auth'])) $configuration[$authentication_method . '_auth'] = array_replace_recursive($configuration[$authentication_method . '_auth'], $user_configuration[$authentication_method . '_auth']);
        }
        return $configuration;
    }
    /**
     * Used to get default testing related configuration
     *
     * It returns configuration containing test information
     *
     * @param array $configuration the default configuration
     * @param array $user_configuration the user configuration
     *
     * @return array $configuration the default configuration information
     */
    private function GetTestConfig($configuration, $user_configuration) 
    {
        /** Test parameters */
        /** Test mode indicates the application will be tested when its run */
        $configuration['testing']['test_mode'] = false;
        /** Test type indicates the type of application testing. i.e script, functional, functional-database or unit */
        $configuration['testing']['test_type'] = 'functional';
        /** Indicates the files that need to be included during testing */
        $configuration['testing']['include_files'] = array();
        /** The application test class */
        $configuration['testing']['test_class'] = "";
        /** Test parameters used during testing */
        /** The list of classes to test */
        $configuration['testing']['test_classes'] = array(
            "testing"
        );
        /** The list of application url options to test. It is used for functional testing */
        $configuration['testing']['test_options'] = array();
        /** The url of html validator to use during testing */
        $configuration['testing']['validator_url'] = "http://validator.pakjiddat.pk:8888";
        /** Used to indicate if the application should save page parameters to test_data folder */
        $configuration['testing']['save_test_data'] = false;
        /** Used to indicate if the application should append the parameters to test data file */
        $configuration['testing']['append_test_data'] = false;
        /** Used to indicate if the application should collect code coverage information */
        $configuration['testing']['enable_code_coverage'] = false;
        /** Used to indicate if the application should save test results */
        $configuration['testing']['save_test_results'] = true;
        /** If the test mode is given in command line then it is used */
        if (isset($user_configuration['general']['parameters']['test_mode']) && $user_configuration['general']['parameters']['test_mode'] == 'true') $user_configuration['testing']['test_mode'] = true;      
        /** If the test type is given in command line then it is used */
        if (isset($user_configuration['general']['parameters']['test_type'])) $user_configuration['testing']['test_type'] = $user_configuration['general']['parameters']['test_type'];
        /** The name of the test results file */
        if (!isset($user_configuration['testing']['test_results_file'])) $user_configuration['testing']['test_results_file'] = 'test_results.txt';
        /** The path to the application documentation folder */
        if (!isset($user_configuration['testing']['documentation_folder'])) $user_configuration['testing']['documentation_folder'] = 'documentation';
        /** The path to the application documentation folder is set in user configuration */
        if (isset($user_configuration['testing']['documentation_folder'])) $user_configuration['testing']['documentation_folder'] = $configuration['path']['application_path'] . DIRECTORY_SEPARATOR . $user_configuration['testing']['documentation_folder'];
        /** The full path to the test results file is set */
        if (isset($user_configuration['testing']['test_results_file'])) $user_configuration['testing']['test_results_file'] = $user_configuration['testing']['documentation_folder'] . DIRECTORY_SEPARATOR . $user_configuration['testing']['test_results_file'];
        /** User configuration is merged */
        if (isset($user_configuration['testing'])) $configuration['testing'] = array_replace_recursive($configuration['testing'], $user_configuration['testing']);
        return $configuration;
    }
    /**
     * Used to get default path related configuration
     *
     * It returns configuration containing path information
     *
     * @param array $configuration the default configuration
     * @param array $user_configuration the user configuration
     * @throws
     * @return array $configuration the default configuration information
     */
    private function GetPathConfig($configuration, $user_configuration) 
    {
        /** The document root of the application is set */
        $configuration['path']['document_root'] = $_SERVER['DOCUMENT_ROOT'];
        /** The base folder path is set. All the application files including the framework are in this folder */
        $configuration['path']['base_path'] = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
        /** If the application template name is not set then the default template basicsite is used */
        if (!isset($user_configuration['general']['template'])) $user_configuration['general']['template'] = "basicsite";
        /** The application folder name is derived from the application name */
        if (isset($user_configuration['general']['application_name']) && !isset($user_configuration['path']['application_folder'])) 
        {
            $user_configuration['path']['application_folder'] = strtolower(\Framework\Utilities\UtilitiesFramework::Factory("strings")->CamelCase($user_configuration['general']['application_name']));
            /** The namespace separator is replaced with DIRECTORY_SEPARATOR */
            $user_configuration['path']['application_folder'] = str_replace("\\", DIRECTORY_SEPARATOR, $user_configuration['path']['application_folder']);
        }
        /** The application domain name is set */
        if (isset($parameters['web_domain'])) $user_configuration['path']['web_domain'] = $parameters['web_domain'];
        else if (isset($_SERVER['HTTP_HOST']) || isset($_SERVER['HTTPS_HOST'])) $user_configuration['path']['web_domain'] = (isset($_SERVER['HTTPS_HOST'])) ? "https://" . $_SERVER['HTTPS_HOST'] : "http://" . $_SERVER['HTTP_HOST'];
        else $user_configuration['path']['web_domain'] = "http://example.com";
        /** The web path to the framework */
        if (!isset($user_configuration['path']['relative_web_domain'])) $user_configuration['path']['relative_web_domain'] = str_replace($configuration['path']['document_root'], "", $configuration['path']['base_path']);      
        /** The framework url is set */
        $user_configuration['path']['framework_url'] = $user_configuration['path']['web_domain'] . "/" . $user_configuration['path']['relative_web_domain'] . "index.php";
        /** The framework folder url is set */
        $user_configuration['path']['framework_folder_url'] = $user_configuration['path']['web_domain'] . "/" . $user_configuration['path']['relative_web_domain'];
        /** The web path to the application */
        if (!isset($user_configuration['path']['application_folder_url']))
        /** The web path to the application */
        $user_configuration['path']['application_folder_url'] = $user_configuration['path']['web_domain'] . str_replace("//", "/", ("/" . $user_configuration['path']['relative_web_domain'] . "/")) .  $user_configuration['path']['application_folder'];
        /** The url to the framework's template folder */
        if (!isset($user_configuration['path']['framework_template_url'])) $user_configuration['path']['framework_template_url'] = $user_configuration['path']['web_domain'] . "/" . $user_configuration['path']['relative_web_domain'] . "framework/templates/" . $user_configuration['general']['template'];
        /** The url to the application's template folder */
        if (!isset($user_configuration['path']['application_template_folder'])) $user_configuration['path']['application_template_folder'] = "templates";
        /** If the application is run from browser */
        if (isset($_SERVER['REQUEST_URI']))
        {
            $configuration['path']['current_request_url'] = $_SERVER['REQUEST_URI'];
            $configuration['path']['current_url'] = (isset($_SERVER['HTTPS_HOST']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace("\\", '%25255C', $_SERVER['REQUEST_URI']);
        }
        else 
        {
            $configuration['path']['current_url'] = "N.A";
            $configuration['path']['current_request_url'] = "N.A";
        }
        $user_configuration['path']['application_template_url'] = $user_configuration['path']['web_domain'] . $user_configuration['path']['relative_web_domain'] . "/" . $user_configuration['path']['application_folder'] . "/" . $user_configuration['path']['application_template_folder'];
    
        /** If the vendors folder url is not set */
        if (!isset($user_configuration['path']['vendor_folder_url'])) {
            /** The web path to the application's vendors folder */
            if (isset($user_configuration['path']['vendor_folder'])) $user_configuration['path']['vendor_folder_url'] = $user_configuration['path']['application_folder_url'] . $user_configuration['path']['vendor_folder'];
            else $user_configuration['path']['vendor_folder_url'] = $user_configuration['path']['application_folder_url'] . "/vendors";
        }
        /** The path to the framework folder */
        $configuration['path']['framework_path'] = $configuration['path']['base_path'] . DIRECTORY_SEPARATOR . "framework";
        /** The path to the application folder */
        $configuration['path']['application_path'] = $configuration['path']['base_path'] . DIRECTORY_SEPARATOR . $user_configuration['path']['application_folder'];
        /** The path to the framework templates html folder */
        $configuration['path']['framework_template_path'] = $configuration['path']['framework_path'] . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $user_configuration['general']['template'] . DIRECTORY_SEPARATOR . "html";
        /** The path to the application templates folder */
        $configuration['path']['application_template_path'] = $configuration['path']['application_path'] . DIRECTORY_SEPARATOR . $user_configuration['path']['application_template_folder'];
        /** The path to the application tmp folder */
        $configuration['path']['tmp_folder_path'] = $configuration['path']['application_path'] . DIRECTORY_SEPARATOR . 'tmp';
        /** The path to the vendor folder */
        $configuration['path']['vendor_folder_path'] = $configuration['path']['base_path'] . DIRECTORY_SEPARATOR . 'vendors';
        /** The path to the pear folder */
        $configuration['path']['pear_folder_path'] = DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "share" . DIRECTORY_SEPARATOR . "pear";
        /** Indicates the files that need to be included for all application requests */
        $configuration['path']['include_files'] = array();
        /** The path to the application data folder is set in user configuration */
        if (isset($user_configuration['path']['data_folder'])) $user_configuration['path']['data_folder'] = $configuration['path']['application_path'] . DIRECTORY_SEPARATOR . $user_configuration['path']['data_folder'];        
        /** The folder path to the application's vendors folder */
        if (isset($user_configuration['path']['vendor_folder_path'])) $user_configuration['path']['vendor_folder_path'] = $configuration['path']['application_path'] . DIRECTORY_SEPARATOR . $user_configuration['path']['vendor_folder_path'];
        else $user_configuration['path']['vendor_folder_path'] = $configuration['path']['base_path'] . DIRECTORY_SEPARATOR . 'vendors';
        /** User configuration is merged */
        if (isset($user_configuration['path'])) $configuration['path'] = array_replace_recursive($configuration['path'], $user_configuration['path']);
        /** The updated path configuration */
        $updated_configuration['path'] = $configuration['path'];
        /** The placeholders used in the path configuration are updated with the actual values */
        foreach ($configuration['path'] as $config_name => $config_value) {
            /** The placeholder config name */
            $placeholder_config_name    = "{" .$config_name. "}";
            foreach ($updated_configuration['path'] as $updated_config_name => $updated_config_value) {
                /** If the configuration value is an array then the loop continues */
                if (is_array($updated_config_value) || is_array($config_value)) continue;
                /** The placeholder config value replaces the placeholder config name */
                $updated_config_value   = str_replace($placeholder_config_name, $config_value, $updated_config_value);
                /** The path configuration is updated */
                $updated_configuration['path'][$updated_config_name] = $updated_config_value;
            }
        }
        
        /** The user configuration is updated */
        $configuration['path'] = $updated_configuration['path'];

        return $configuration;
    }
    /**
     * Used to get default required frameworks configuration
     *
     * It returns configuration containing required frameworks information
     *
     * @param array $configuration the default configuration
     * @param array $user_configuration the user configuration
     *
     * @return array $configuration the application configuration information
     */
    private function GetRequiredObjectsConfig($configuration, $user_configuration) 
    {
        /** The parameters array is initialized */
        $error_handler_parameters = $db_parameters = $filesystem_parameters = array();
        /** The logging class parameters are set */
        /** The shutdown function callable */
        $error_handler_parameters['shutdown_function'] = "";
        /** Used to indicate if application should use custom error handler */
        $error_handler_parameters['register_error_handler'] = true;
        /** Used to indicate if application is in development mode */
        $error_handler_parameters['development_mode'] = (isset($user_configuration['general']['development_mode'])) ? $user_configuration['general']['development_mode'] : true;
        /** Custom error handler callback */
        $error_handler_parameters['custom_error_handler'] = array(
            "application",
            "CustomErrorHandler"
        );
        /** Used to indicate if application is being run from browser */
        $error_handler_parameters['context'] = $user_configuration["general"]["parameters"]["context"];
        /** The application folder path */
        $error_handler_parameters['application_folder'] = $configuration['path']['base_path'];
        /** The database class parameters are set */
        $db_parameters = array(
            "host" => "",
            "user" => "",
            "password" => "",
            "database" => "",
            "debug" => "",
            "charset" => "utf8"
        );
        /** The utilities class parameters are set */
        $filesystem_parameters['table_prefix'] = "";
        $filesystem_parameters['function_cache_duration'] = array();
        $filesystem_parameters['upload_folder'] = $configuration['path']['tmp_folder_path'];
        $filesystem_parameters['allowed_extensions'] = array(
            "xls",
            "xlsx",
            "txt"
        );
        $filesystem_parameters['max_allowed_file_size'] = "2048";
        $filesystem_parameters['link'] = '';
        /** The required framework objects are defined */
        $configuration['required_objects'] = array(
            "application" => array(
                "class_name" => "",
                "parameters" => array()
            ) ,
            "string" => array(
                "class_name" => "Framework\Utilities\Strings",
                "parameters" => array()
            ) ,
            "database" => array(
                "class_name" => "Framework\Utilities\DatabaseFunctions",
                "parameters" => $db_parameters
            ) ,
            "frameworkdatabase" => array(
                "class_name" => "Framework\Utilities\DatabaseFunctions",
                "parameters" => $db_parameters
            ) ,
            "externaldatabase" => array(
                "class_name" => "Framework\Utilities\DatabaseFunctions",
                "parameters" => $db_parameters
            ) ,
            "filesystem" => array(
                "class_name" => "Framework\Utilities\FileSystem",
                "parameters" => $filesystem_parameters
            )
        );
        /** If the application context is browser, then the required objects are included */
        if ($configuration['general']['parameters']['context'] == 'browser') 
        {
            $configuration['required_objects']['htmltable']['class_name'] = '\Framework\Templates\BasicSite\UserInterface\HtmlTable';
            $configuration['required_objects']['htmltablepresentation']['class_name'] = '\Framework\Templates\BasicSite\Presentation\HtmlTablePresentation';
            $configuration['required_objects']['form']['class_name'] = '\Framework\Templates\BasicSite\UserInterface\Form';
            $configuration['required_objects']['template_helper']['class_name'] = '\Framework\Utilities\Template';
            $configuration['required_objects']['template']['class_name'] = '\Framework\Templates\BasicSite\Presentation\BasicSiteTemplate';
            $configuration['required_objects']['listpage']['class_name'] = '\Framework\Templates\BasicSite\UserInterface\ListPage';
            $configuration['required_objects']['page']['class_name'] = '\Framework\Templates\BasicSite\UserInterface\Page';
            $configuration['required_objects']['databasereader']['class_name'] = '\Framework\Templates\BasicSite\Presentation\DatabaseReader';
            $configuration['required_objects']['reflection']['class_name'] = '\Framework\Utilities\Reflection';
            $configuration['required_objects']['profiling']['class_name'] = '\Framework\Utilities\Profiling';
            $configuration['required_objects']['encryption']['class_name'] = '\Framework\Utilities\Encryption';
            $configuration['required_objects']['string']['class_name'] = '\Framework\Utilities\Strings';
            $configuration['required_objects']['collection']['class_name'] = '\Framework\Utilities\Collection';
            $configuration['required_objects']['logging']['class_name'] = '\Framework\Utilities\Logging';
            $configuration['required_objects']['authentication']['class_name'] = '\Framework\Utilities\Authentication';
            $configuration['required_objects']['filesystem']['class_name'] = '\Framework\Utilities\FileSystem';
            $configuration['required_objects']['caching']['class_name'] = '\Framework\Utilities\Caching';
            $configuration['required_objects']['caching']['parameters'] = array(
                "table_name" => "pakphp_cached_data",
                "db_link" => "",
                "site_url" => ""
            );
            $configuration['required_objects']['unstructureddataui']['class_name'] = '\Framework\Templates\BasicSite\UserInterface\UnstructuredDataUi';
            $configuration['required_objects']['structureddataui']['class_name'] = '\Framework\Templates\BasicSite\UserInterface\StructuredDataUi';
            $configuration['required_objects']['email']['class_name'] = '\Framework\Utilities\Email';
        }
        /** If the application type is not browser, then the required objects are included */
        else
        {           
            $configuration['required_objects']['reflection']['class_name'] = '\Framework\Utilities\Reflection';
            $configuration['required_objects']['profiling']['class_name'] = '\Framework\Utilities\Profiling';
            $configuration['required_objects']['encryption']['class_name'] = '\Framework\Utilities\Encryption';
            $configuration['required_objects']['string']['class_name'] = '\Framework\Utilities\Strings';
            $configuration['required_objects']['collection']['class_name'] = '\Framework\Utilities\Collection';
            $configuration['required_objects']['logging']['class_name'] = '\Framework\Utilities\Logging';
            $configuration['required_objects']['authentication']['class_name'] = '\Framework\Utilities\Authentication';
            $configuration['required_objects']['filesystem']['class_name'] = '\Framework\Utilities\FileSystem';
            $configuration['required_objects']['template']['class_name'] = '\Framework\Templates\BasicSite\Presentation\BasicSiteTemplate';            
            $configuration['required_objects']['template_helper']['class_name'] = '\Framework\Utilities\Template';
            $configuration['required_objects']['caching']['class_name'] = '\Framework\Utilities\Caching';
            $configuration['required_objects']['caching']['parameters'] = array(
                "table_name" => "pakphp_cached_data",
                "db_link" => "",
                "site_url" => ""
            );
            $configuration['required_objects']['email']['class_name'] = '\Framework\Utilities\Email';
        }        
        /** If the user did not disable error handling then the errorhandler object is added to the required frameworks list */
        if (!isset($user_configuration['general']['parameters']['disable_error_handling'])) 
        {
            $configuration['required_objects']["errorhandler"] = array(
                "class_name" => "Framework\Utilities\ErrorHandler",
                "parameters" => $error_handler_parameters
            );
        }
        /** User configuration is merged */
        if (isset($user_configuration['required_objects'])) $configuration['required_objects'] = array_replace_recursive($configuration['required_objects'], $user_configuration['required_objects']);
        return $configuration;
    }
    /**
     * Used to initialize php settings
     *
     * It sets the php settings
     *
     * @param array $user_configuration the user configuration
     */
    private function InitializePhpSettings($user_configuration) 
    {
        /** The time zone used by the application */
        $default_timezone = (isset($user_configuration['general']['timezone'])) ? $user_configuration['general']['timezone'] : 'Asia/Karachi';
        /** The error reporting used by the application */
        $default_error_reporting = (isset($user_configuration['general']['error_reporting'])) ? $user_configuration['general']['error_reporting'] : E_ALL;
        /** All error reporting value is set */
        error_reporting($default_error_reporting);
        /** The default time zone is set */
        date_default_timezone_set($default_timezone);
        if ($user_configuration['general']['development_mode']) 
        {
            ini_set('display_errors', E_ALL);
            ini_set('display_startup_errors', true);
        }
        else
        {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', false);
        }
    }
    /**
     * Used to get updated application configuration data
     * The configuration data can be overridden by child classes
     *
     * It returns an array containing application configuration data
     * Used to set default php settings
     * It also merges the user settings with the default settings
     * Certain settings are individually updated
     * For example the application name given by the user is used to update folder path information
     * If the application is under development then all errors are displayed
     * If the application is not under development then no errors are displayed
     * It also sets the default time zone
     *
     * @param array $user_configuration user defined application configuration
     *
     * @return array $configuration the application configuration information
     */
    public function GetUpdatedConfiguration($user_configuration) 
    {
        /** The general default configuration is fetched */
        $configuration = self::GetGeneralConfig($user_configuration);
        /** The php error configuration is set */
        $this->InitializePhpSettings($configuration);
        /** The http and session authentication default configuration is fetched */
        $configuration = array_replace_recursive($configuration, self::GetAuthConfig($configuration, $user_configuration));
        /** The path default configuration is fetched */
        $configuration = array_replace_recursive($configuration, self::GetPathConfig($configuration, $user_configuration));
        /** The test default configuration is fetched */
        $configuration = array_replace_recursive($configuration, self::GetTestConfig($configuration, $user_configuration));
        /** The required frameworks default configuration is fetched */
        $configuration = array_replace_recursive($configuration, self::GetRequiredObjectsConfig($configuration, $user_configuration));
        
        return $configuration;
    }
}
?>
