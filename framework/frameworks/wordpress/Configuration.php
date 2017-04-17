<?php

namespace Framework\Frameworks\WordPress;

/**
 * Base configuration class for wordpress applications
 * 
 * Singleton class
 * Abstract class. must be inherited by a child class
 * It uses the DefaultApplicationConfiguration class
 * The DefaultApplicationConfiguration class contains default configuration values
 * Initializes objects and sets configuration
 * 
 * @category   Framework
 * @package    WordPress
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
abstract class Configuration extends \Framework\Configuration\Configuration
{   
	/**
     * Used to initialize the application
     * 
     * Initializes objects needed by the application
     * Sets application configuration
     * 
     * @since 1.0.0
	 * @throws object Exception an exception is thrown if the plugin name was not given in the user configuration
	 * @throws object Excepton an exception is thrown if the plugin version was not given in user configuration 
     */
    public function Initialize()
    {
		/** The configuration object for the current object is set */
    	$this->SetConfigurationObject($this);        
        /** User configuration settings are merged with default configuration settings */
        $default_configuration          = new DefaultConfiguration();
		/** The default configuration is merged with user configuration and the result is returned */
        $this->configuration            = $default_configuration->GetUpdatedConfiguration($this->user_configuration);
		/** The application authentication and error handling is enabled */		
        $this->EnableAuthenticationAndErrorHandling();
        /** All required classes are included */
        $this->IncludeRequiredClasses();
        /** Php Sessions are enabled if user requested sessions */
        $this->EnableSessions();
		
        /** The function to call after the plugin is loaded is registered. It loads the translation information */
		$this->GetComponent("application")->WP_AddAction( 'plugins_loaded', $this->GetComponent("application"), 'WP_LoadPluginTextDomain' );
		/** The css files for admin pages are registered */
		$this->GetComponent("application")->WP_AddAction( 'admin_enqueue_scripts', $this->GetComponent("application"), 'WP_AdminEnqueueStyles' );
		/** The javascript files for admin pages are registered */
		$this->GetComponent("application")->WP_AddAction( 'admin_enqueue_scripts', $this->GetComponent("application"), 'WP_AdminEnqueueScripts' );
		/** The javascript files for public pages are registered */
		$this->GetComponent("application")->WP_AddAction( 'wp_enqueue_scripts', $this->GetComponent("application"), 'WP_EnqueueScripts' );
		/** The css files for public pages are registered */
		$this->GetComponent("application")->WP_AddAction( 'wp_enqueue_scripts', $this->GetComponent("application"), 'WP_EnqueueStyles' );
		/** If the plugin should have a settings page and the admin user is logged in then the admin page is initialized and the settings page is created */
		if(is_admin()&&$this->GetConfig('wordpress','use_settings')) {
		    $this->GetComponent("application")->WP_AddAction( 'admin_menu', $this->GetComponent("application"), 'WP_DisplaySettings' );
			$this->GetComponent("application")->WP_AddAction( 'admin_init', $this->GetComponent("application"), 'WP_InitAdmin' );
		}
		/** The custom shortcodes */
		$custom_shortcodes                                                                                       = $this->GetConfig('wordpress','custom_shortcodes');			
		/** Each custom shortcode is registered */
		for($count=0; $count<count($custom_shortcodes); $count++) {		    
	        /** Each shortcode is registered */
			$shortcode                                                                                           = $custom_shortcodes[$count];			
			/** The shortcode callback object is fetched */
			$shortcode['callback'][0]                                                                            = $this->GetComponent($shortcode['callback'][0]);
			/** The shortcode is registered */
			$this->GetComponent("application")->RegisterShortcode($shortcode['name'],$shortcode['callback']);
		}		
		/** The custom xml-rpc methods */
		$custom_xml_rpc_methods                                                                                  = $this->GetConfig('wordpress','custom_xmlrpc_methods');
		/** The custom actions */
	    $custom_actions                                                                                          = $this->GetConfig('wordpress','custom_actions');
		/** The custom filters */
	    $custom_filters                                                                                          = $this->GetConfig('wordpress','custom_filters');
		/** The custom widget registration method is added to the custom actions list */
		$custom_actions                                                                                          = array_merge($custom_actions, array(array("name"=>"widgets_init","callback"=>array("application","RegisterWidgets"))));
		/** The custom xml-rpc methods are added to the custom filter list */
		$custom_filters                                                                                          = array_merge($custom_filters, array(array("name"=>"xmlrpc_methods","callback"=>array("application","RegisterXmlRpcMethods"))));		
		/** The custom actions and filters */
	    $custom_actions_filters                                                                                  = array("actions"=>$custom_actions,"filters"=>$custom_filters);		
		/** All the custom actions and filters are registered */		
		foreach ($custom_actions_filters as $hook_name => $hooks) {
		    for($count=0; $count<count($hooks); $count++) {		    
			    /** A custom action or filter */
			    $custom_hook                                                                                     = $custom_actions_filters[$hook_name][$count];
			    /** The name of the custom hook */
			    $name                                                                                            = $custom_hook['name'];
			    /** The callback of the custom hook */
			    $callback                                                                                        = $custom_hook['callback'];
			    /** The object used in callback function is fetched */
			    $custom_callback                                                                                 = array($this->GetComponent($callback[0]),$callback[1]);
			    /** If the custom callback is not callable then an exception is thrown */
			    if(!is_callable($custom_callback))throw new \Exception("Invalid custom callback function given. Details: ".var_export($callback, true));
				/** If the current hook type is action then the custom action is registered */
				if ($hook_name == "actions") {
			        /** The custom action is registered */
			        $this->GetComponent("application")->WP_AddAction(
			            $custom_hook['name'],
				        $custom_callback[0],
				        $custom_callback[1]
			        );
			    }
				else if ($hook_name == "filters") {
			        /** The custom filter is registered */
			        $this->GetComponent("application")->WP_AddFilter(
			            $custom_hook['name'],
				        $custom_callback[0],
				        $custom_callback[1]
			        );
			    }
            }
        }    
	}
}
