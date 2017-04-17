<?php
namespace Framework\Configuration;
/**
 * Base class for all applications
 *
 * Abstract class. must be inherited by a child class
 * It allows managing framework configuration
 * All classes must be derived from this class
 * It provides a central location for functions that need to be accessed from all classes
 *
 * @category   Framework
 * @package    Configuration
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
abstract class Base
{
    /** The configuration object */
    private $configuration_object;
    /**
     * Used to get the configuration object
     *
     * It returns the configuration object
     *
     * @return object $configuration_object the configuration object for the module
     */
    final public function GetConfigurationObject() 
    {
        /** The configuration object is set */
        $configuration_object = $this->configuration_object;
        return $configuration_object;
    }
    /**
     * Used to set the configuration object
     *
     * It sets the configuration object
     *
     * @param object $configuration_object the configuration object for the module
     */
    final public function SetConfigurationObject($configuration_object) 
    {
        /** The configuration object is set */
        $this->configuration_object = $configuration_object;
    }
    /**
     * Used to get the specified object
     *
     * Throws an exception if the specified object could not be found
     * Otherwise returns the object
     *
     * @param object $object_name name of the required object
     * @param array $parameters the optional object parameters
     * If not set then then the object parameters specified in the application configuration are used
     *
     * @return object $component_object the required component object
     */
    final public function GetComponent($object_name, $parameters = false) 
    {
        /** The component list is fetched from the configuration object */
        $component_list = $this->configuration_object->GetComponentList();
        if (!isset($component_list[$object_name])) $component_object = $this->configuration_object->InitializeObject($object_name, $parameters);
        else $component_object = $component_list[$object_name];
        return $component_object;
    }
    /**
     * Used to check if the given configuration exists
     *
     * It checks if given configuration exists
     *
     * @param string $config_name name of the required configuration
     * @param string $sub_config_name optional name of the required sub configuration
     * @param string $sub_sub_config_name optional name of the required third level sub configuration
     *
     * @return boolean $config_exists used to indicate if the configuration exists
     */
    final public function ConfigurationExists($config_name, $sub_config_name = "", $sub_sub_config_name = "") 
    {
        /** Used to indicate if the configuration exists */
        $config_exists = true;
        /** The configuration list is fetched from the configuration object */
        $configuration = $this->configuration_object->GetConfiguration();
        /** If the top level configuration could not be found then function returns false */
        if (!isset($configuration[$config_name])) $config_exists = false;
        /** If the second level configuration is given but its value could not be found then function returns false */
        if ($sub_config_name != "" && !isset($configuration[$config_name][$sub_config_name])) $config_exists = false;
        /** If the third level configuration is given but its value could not be found then function returns false */
        if ($sub_sub_config_name != "" && !isset($configuration[$config_name][$sub_config_name][$sub_sub_config_name])) $config_exists = false;
        return $config_exists;
    }
    /**
     * Used to get the specified configuration setting
     *
     * Throws an exception if the specified configuration setting could not be found
     * Otherwise returns the configuration setting
     *
     * @param string $config_name name of the required configuration
     * @param string $sub_config_name optional name of the required sub configuration
     * @param string $sub_sub_config_name optional name of the required third level sub configuration
     * @throws Exception an exception is thrown if the given configuration does not exist
     */
    final public function GetConfig($config_name, $sub_config_name = "", $sub_sub_config_name = "") 
    {
        /** The configuration list is fetched from the configuration object */
        $configuration = $this->configuration_object->GetConfiguration();
        /** If the top level configuration could not be found then an exception is thrown */
        if (!isset($configuration[$config_name])) throw new \Exception("Application configuration could not be found for config name: " . $config_name);
        /** If the second level configuration is given but its value could not be found then an exception is thrown */
        if ($sub_config_name != "" && !isset($configuration[$config_name][$sub_config_name])) throw new \Exception("Application configuration could not be found for config name: [" . $config_name . "][" . $sub_config_name . "]");
        /** If the third level configuration is given but its value could not be found then an exception is thrown */
        if ($sub_sub_config_name != "" && !isset($configuration[$config_name][$sub_config_name][$sub_sub_config_name])) throw new \Exception("Application configuration could not be found for config name: [" . $config_name . "][" . $sub_config_name . "][" . $sub_sub_config_name . "]");
        /** The configuration value is returned if the sub configuration is empty */
        if ($sub_config_name == "") return $configuration[$config_name];
        /** The configuration value is returned if the sub sub configuration is empty */
        else if ($sub_sub_config_name == "") return $configuration[$config_name][$sub_config_name];
        /** The configuration value is returned if the sub sub configuration is not empty */
        else return $configuration[$config_name][$sub_config_name][$sub_sub_config_name];
    }
    /**
     * Used to set the specified configuration setting
     *
     * Throws an exception if the specified configuration setting could not be found
     * Otherwise returns the configuration setting
     *
     * @param string $config_name name of the required configuration
     * @param string $sub_config_name name of the required sub configuration
     * @param string $config_value value of the required configuration
     * @throws Exception an exception is thrown if the given configuration does not exist
     */
    final public function SetConfig($config_name, $sub_config_name, $config_value) 
    {
        /** The configuration list is fetched from the configuration object */
        $configuration = $this->configuration_object->GetConfiguration();
        /** If the top level configuration could not be found then an exception is thrown */
        if (!isset($configuration[$config_name])) throw new \Exception("Application configuration could not be found for config name: " . $config_name);
        /** The configuration value is saved */
        if ($sub_config_name == "") $configuration[$config_name] = $config_value;
        else $configuration[$config_name][$sub_config_name] = $config_value;
        /** The configuration list is updated */
        $this->configuration_object->SetConfiguration($configuration);
    }
    /**
     * Used to set the session configuration
     *
     * It sets the given session variable
     *
     * @param string $config_name name of the required session configuration
     * @param string $config_value value of the required session configuration
     * @param boolean $unset indicates if the session variable should be unset
     */
    final public function SetSessionConfig($config_name, $config_value, $unset) 
    {
        /** If the session variable should be unset */
        if ($unset) 
        {
            unset($_SESSION[$config_name]);
        }
        else
        {
            /** Sets the given session variable */
            $_SESSION[$config_name] = $config_value;
        }
    }
    /**
     * Used to get the session configuration
     *
     * It gets the given session variable
     *
     * @param string $config_name name of the required session configuration
     *
     * @return string $session_config_value the session configuration value
     */
    final public function GetSessionConfig($config_name) 
    {
        /** If the given session variable is not set then function returns false */
        if (!isset($_SESSION[$config_name])) $session_config_value = false;
        else 
        {
            /** Returns the given session variable */
            $session_config_value = $_SESSION[$config_name];
        }
        return $session_config_value;
    }
    /**
     * Used to determine if a session has been started
     *
     * It returns true if a session has been started and false otherwise
     *
     * @return boolean $is_session_started true if session is already started. false if session has not been started
     */
    final protected function IsSessionStarted() 
    {
        $is_session_started = false;
        /** If the php is not being run from command line */
        if (php_sapi_name() !== 'cli') 
        {
            /** If the current php version is greater than or equal to 5.4.0 */
            if (version_compare(phpversion() , '5.4.0', '>=')) 
            {
                $is_session_started = session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            }
            else
            {
                $is_session_started = session_id() === '' ? FALSE : TRUE;
            }
        }
        return $is_session_started;
    }
}

