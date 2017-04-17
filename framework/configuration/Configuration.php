<?php
namespace Framework\Configuration;
use \Framework\Configuration\Base as Base;
/**
 * Base configuration class for browser based applications
 *
 * Abstract class. must be inherited by a child class
 * It uses the DefaultApplicationConfiguration class
 * The DefaultApplicationConfiguration class contains default configuration values
 * Initializes objects and sets configuration
 *
 * @category   Framework
 * @package    Configuration
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
abstract class Configuration extends Base
{
    /** The user defined configuration */
    protected $user_configuration;
    /** Specifies the configuration information required by the application */
    protected $configuration;
    /** List of objects that can be used by the application */
    protected $component_list;
    /**
     * Used to set the user configuration
     *
     * Defines the user configuration
     * Updated the user configuration using the user defined application parameters
     * Sets the user defined configuration as object property
     *
     * @param array $parameters the application parameters given by the user
     */
    public function __construct($parameters) 
    {
        /** The user defined application configuration */
        $this->user_configuration = array();
        /** The parameters given by the user are set in the application configuration */
        $this->user_configuration['general']['parameters'] = $parameters;
        /** The default application name is set in the application configuration */
        $this->user_configuration['general']['application_name'] = "Default";
        /** The default application option is set */
        $this->user_configuration['general']['default_option'] = "index";
    }
    /**
     * Used to create the given object
     *
     * Creates the given object
     * The object must be mentioned by the user in the application configuration file
     * The object is created using GetInstance method if it is supported or new operator if class is not Singleton
     *
     * @param array $parameters the optional object parameters
     * If not set then then the object parameters specified in the application configuration are used
     *
     * @return object $framework_class_obj the initialized object is returned
     */
    final public function InitializeObject($object_name, $parameters = false) 
    {
        /** If class name is not specified for given object name, then the class name is fetched */
        if (!$this->ConfigurationExists("required_objects", $object_name)) $framework_class_name = $this->GetClassName($object_name);
        else 
        {
            $object_information = $this->GetConfig('required_objects', $object_name);
            /** The class parameters are initialized */
            if ($parameters) $object_information['parameters'] = $parameters;
            else if (!isset($object_information['parameters'])) $object_information['parameters'] = "";
            /** The name of the framework class */
            $framework_class_name = $object_information['class_name'];
        }
        /** 
         * Used to check if class exists
         * The class is autoloaded if it is not already included
         * If it does not exist then an exception is thrown
         */
        if (!class_exists($framework_class_name, true)) throw new \Exception("Class: " . $framework_class_name . " does not exist for object name: " . $object_name);
        /**
         * Used to check if class implments Singleton pattern
         * If it has a  function called GetInstance then
         * It is assumed to be a Singleton class
         * The GetInstance method is used to get class instance
         */
        $callable_singleton_method = array(
            $framework_class_name,
            "GetInstance"
        );
        if (is_callable($callable_singleton_method)) $framework_class_obj = call_user_func_array($callable_singleton_method, array(
            $object_information['parameters']
        ));
        /** If it is not a Singleton class then an object of the class is created using new operator */
        else $framework_class_obj = new $framework_class_name($object_information['parameters']);
        /** The callable that allows SetConfigurationObject function to be called */
        $callable_method = array(
            $framework_class_obj,
            "SetConfigurationObject"
        );
        /** Used to check if class implements SetConfigurationObject function */
        if (is_callable($callable_method)) 
        {
            /** The configuration object is set for each object */
            $framework_class_obj->SetConfigurationObject($this);
        }
        /** The object is saved to object list */
        $this->component_list[$object_name] = $framework_class_obj;
        
        return $framework_class_obj;
    }
    /**
     * Used to include required files
     *
     * It gets list of all files that need to be included
     * Including the files given in test parameters and url handling parameters
     */
    final protected function IncludeRequiredClasses() 
    {
        /** Test mode status is returned */
        $test_mode = $this->GetConfig("testing", "test_mode");
        /** The list of files to be included for testing is fetched from configuration */
        if ($test_mode) $include_files = $this->GetConfig('testing', 'include_files');
        /** The list of files to be included for application requests is fetched from configuration */
        else $include_files = $this->GetConfig('path', 'include_files');
        /** The application url mappings */
        $application_url_mappings = $this->GetConfig("general", "application_url_mappings");
        /** The current application option */
        $option = $this->GetConfig("general", "option");
        /** 
         * The files to be included for the current application request are merged with the files to include for testing
         * Or they are merged with the files to include for all application requests
         */
        if (isset($application_url_mappings[$option]) && isset($application_url_mappings[$option]['include_files'])) $include_files = array_merge_recursive($include_files, $application_url_mappings[$option]['include_files']);
        /** All files that need to be included are included */
        foreach ($include_files as $include_type => $include_files) 
        {
            for ($count = 0;$count < count($include_files);$count++) 
            {
                $file_name = $include_files[$count];
                /** If the include type is equal to vendors then the vendor folder path is prepended to the include file path */
                if ($include_type == "vendors") $file_name = $this->GetConfig('path', 'vendor_folder_path') . DIRECTORY_SEPARATOR . $file_name;
                /** If the include type is equal to pear then the pear folder path is prepended to the include file path */
                if ($include_type == "pear") $file_name = $this->GetConfig('path', 'pear_folder_path') . DIRECTORY_SEPARATOR . $file_name;
                if (is_file($file_name)) require_once ($file_name);
                else throw new \Exception("Invalid include file name: " . $file_name . " given for page option: " . $this->GetConfig("general", "option"));
            }
        }
    }
    /**
     * Used to initialize the application
     *
     * Initializes objects needed by the application
     * Sets application configuration
     */
    public function Initialize() 
    {
        /** The configuration object for the current object is set */
        $this->SetConfigurationObject($this);
        /** The default configuration settings */
        $default_configuration = new DefaultConfiguration();
        /** The default configuration is merged with user configuration and the result is returned */
        $this->configuration = $default_configuration->GetUpdatedConfiguration($this->user_configuration);
        /** It sends the required http headers. It also checks if cross domain ajax calls need to be enabled */
        $this->SendHttpHeaders();
        /** Php Sessions are enabled if user requested sessions */
        $this->EnableSessions();
        /** All required classes are included */
        $this->IncludeRequiredClasses();
        /** The translation text is read */
        $this->ReadTranslationText();
        /** The application authentication and error handling is enabled */
        $this->EnableAuthenticationAndErrorHandling();
        /** If the use_formatted_urls option is set */
        if ($this->GetConfig("general", "use_formatted_urls")) 
        {
            /** The url parameters are updated. It allows rewritting urls */
            $this->UpdateUrlParameters();
        }
    }
    /**
     * Used to read the translation text
     *
     * This function reads the translation file if it is given in application configuration
     * The translation data is saved to application configuration
     */
    protected function ReadTranslationText()
    {
        /** The site text */
        $site_text         = array();
        /** If language_folder and language configuration options are given */
        if ($this->ConfigurationExists("general", "language") && $this->ConfigurationExists("path", "language_folder")) {
            /** The absolute path to the language file */
            $language_file  = $this->GetConfig("path", "language_folder") . DIRECTORY_SEPARATOR . $this->GetConfig("general", "language") . ".txt";
            /** If the language file does not exist, then an exception is thrown */
            if (!is_file($language_file)) throw new \Exception("Language file: " . $language_file . " does not exist");
            /** If the language file exists then it is read */
            else {
                $translation_data = $this->GetComponent("filesystem")->GetFileContent($language_file);
                /** The language file contents are converted to array */
                $translation_data = explode("\n", trim($translation_data));
                /** Each translation data item is parsed */
                for ($count = 0; $count < count($translation_data); $count++) {
                    /** If the line is empty, then the loop continues */
                    if ($translation_data[$count] == '') continue;
                    /** The translation line */
                    list($context, $translation_key, $translation_value) = explode("~", $translation_data[$count]);
                    /** The site text */
                    $site_text[$context][$translation_key] = $translation_value;
                }
            }
            /** The site text is saved to application configuration */
            $this->SetConfig("general", "site_text", $site_text);
        }
    }
    /**
     * Used to send http headers
     *
     * This function checks if cross domain ajax calls need to be enabled
     */
    protected function SendHttpHeaders() 
    {
        /** The application object is called */
        $application_obj = $this->GetComponent("application");
        /** If cross domain ajax calls need to be enabled and the application object derives from the Api class, then the function EnableCrossDomainAjaxCalls is called */
        if ($this->GetConfig("general", "enable_cross_domain_ajax_calls") && is_callable(array($application_obj, "EnableCrossDomainAjaxCalls"))) $application_obj->EnableCrossDomainAjaxCalls();
    }
    /**
     * Used to update url parameters
     * This function should be overriden by child class
     *
     * This function allows updating url parameters
     */
    protected function UpdateUrlParameters() 
    {
    }
    /**
     * Used to enable authentication and error handling
     *
     * This function checks for user defined callbacks
     * It replaces callbacks with objects
     * If the user has not defined callbacks then the default application callbacks are used
     * It also checks if the application needs to enable cross domain ajax calls
     */
    final protected function EnableAuthenticationAndErrorHandling()
    {
        /** If the user configuration includes error handler */
        if (isset($this->configuration['required_objects']['errorhandler'])) 
        {
            /** The errorhandler callback is checked */
            $errorhandler_callback = $this->configuration['required_objects']['errorhandler']['parameters']['custom_error_handler'];
            /** If the errorhandler callback is defined but is not callable, then the object string in the callback is replaced with the object */
            if (is_array($errorhandler_callback) && !is_callable($errorhandler_callback)) 
            {
                $errorhandler_callback[0] = $this->GetComponent($errorhandler_callback[0]);
                $this->configuration['required_objects']['errorhandler']['parameters']['custom_error_handler'] = $errorhandler_callback;
            }
            /** The shutdown function callback is checked */
            $shutdown_callback = $this->configuration['required_objects']['errorhandler']['parameters']['shutdown_function'];
            /** If the shutdown function callback is defined but is not callable, then the object string in the callback is replaced with the object */
            if (is_array($shutdown_callback) && !is_callable($shutdown_callback)) 
            {
                $shutdown_callback[0] = $this->GetComponent($shutdown_callback[0]);
                $this->configuration['required_objects']['errorhandler']['parameters']['shutdown_function'] = $shutdown_callback;
            }
            /** Otherwise the default application shutdown callback is used */
            else 
            {
                $errorhandler_callback[0] = $this->GetComponent("application");
                $this->configuration['required_objects']['errorhandler']['parameters']['shutdown_function'] = array(
                    $errorhandler_callback[0],
                    "CustomShutdownFunction"
                );
            }
            /** The errorhandler class object is created */
            $this->InitializeObject("errorhandler");
        }
        /** The authentication methods */
        $authentication_methods = array(
            "api",
            "session",
            "http"
        );
        /** Both session and http authentication are enabled */
        for ($count = 0;$count < count($authentication_methods);$count++) 
        {
            /** The authentication method */
            $authentication_method = $authentication_methods[$count];
            /** 
             * If authentication is enabled
             * Then authentication callback defined by the user configuration is called
             * If the user has not defined the authentication callback
             * Then the default authentication callback is called
             */
            if ($this->GetConfig($authentication_method . '_auth', 'enable')) 
            {
                /** The authentication callback is checked */
                $auth_callback = $this->GetConfig($authentication_method . '_auth', 'auth_callback');
                /** If the auth callback is defined but is not callable, then the object string in the callback is replaced with the object */
                if (is_array($auth_callback) && !is_callable($auth_callback)) 
                {
                    $auth_callback[0] = $this->GetComponent($auth_callback[0]);
                    $this->SetConfig($authentication_method . '_auth', 'auth_callback', $auth_callback);
                }
                if (is_callable($this->GetConfig($authentication_method . '_auth', 'auth_callback'))) call_user_func($this->GetConfig($authentication_method . '_auth', 'auth_callback'));
                else 
                {
                    /** The default authentication callback is called */
                    call_user_func(array(
                        $this->GetComponent("application") ,
                        ucfirst($authentication_method) . "Authentication"
                    ));
                }
            }
        }
    }
    /**
     * Used to enable php sessions
     *
     * This function enables php sessions
     */
    final protected function EnableSessions() 
    {
        /** 
         * If the application needs session support and application is called from browser then session_start() is called
         * And $_SESSION data is saved to session parameter
         */
        if ($this->GetConfig('general', 'enable_sessions') && php_sapi_name() != "cli") 
        {
            /** If the session is not started then it is started */
            if (!$this->IsSessionStarted()) 
            {
                session_start();
                session_regenerate_id();
            }
            $this->SetConfig('general', 'session', $_SESSION);
        }
    }
    /**
     * Used to return the application configuration
     *
     * This function runs the application configuration
     *
     * @return array $configuration the application configuration
     */
    final public function GetConfiguration() 
    {
        $configuration = $this->configuration;
        return $configuration;
    }
    /**
     * Used to get the list of component objects
     *
     * This function returns the list of component objects
     *
     * @return array $components the application components
     */
    final public function GetComponentList() 
    {
        $component_list = $this->component_list;
        return $component_list;
    }
    /**
     * Used to set the application configuration
     *
     * This function sets the application configuration
     *
     * @param array $configuration the application configuration
     */
    final public function SetConfiguration($configuration) 
    {
        $this->configuration = $configuration;
    }
    /**
     * Used to return the class name
     *
     * This function returns the class name for the given object
     * It should be overriden by child class
     * By default the function generates the fully qualified class name by prepending the application name to the class description
     *
     * @param string $class_description description of the class
     *
     * @return string $class_name the fully qualified class name
     */
    protected function GetClassName($class_description) 
    {
        /** The application name */
        $application_name = $this->GetConfig("general", "application_name");
        /** The full class name */
        $class_name = $application_name . "\\" . $class_description;
        return $class_name;
    }
    /**
     * Used to determine if the application request should be handled by the current module
     * It should be overridden by child class
     *
     * It returns true
     *
     * @param string $module_name the current module name
     *
     * @return boolean $is_valid indicates if the application request is valid
     */
    public static function IsValidRequest($module_name) 
    {
        /** The current application request */
        $current_url = strtolower($_SERVER['REQUEST_URI']);
        /** Indicates that application request can be handled by current module */
        $is_valid = true;
        return $is_valid;
    }
    /**
     * Used to run the application
     *
     * This function runs the application
     * It first initializes the application configuration
     * It then runs the application by calling the Main function of the application
     *
     * @return string $response the application response
     */
    final public function RunApplication() 
    {
        /** The application response. it contains the string that will be returned by the application as output */
        $response = "";
        /** The application is initialized */
        $this->Initialize();
        /** The application object is fetched */
        $application_object = $this->GetComponent("application");
        /** The application is run and response is returned */
        $response = $application_object->Main();
        
        return $response;
    }
}

