<?php
namespace Framework\Frameworks\WordPress;
use \Framework\Object\WordPressDataObject as WordPressDataObject;
/**
 * This class implements the base WordPress Application class
 *
 * It contains functions that help in constructing wordpress plugins
 * The class should be inherited by the users application class
 *
 * @category   Framework
 * @package    WordPress
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.2
 */
class Application extends \Framework\Application\Application
{
    /**
     * Used to activate the plugin
     *
     * This function is called when the plugin is activated
     */
    public function WP_Activate() 
    {
        /** The plugin options and meta options are deleted */
        $this->DeletePluginOptions();
    }
    /**
     * Used to deactivate the plugin
     *
     * This function is called when the plugin is deactivated
     */
    public function WP_Deactivate() 
    {
        /** The plugin options and meta options are deleted */
        $this->DeletePluginOptions();
    }
    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param      string               $hook             the name of the WordPress action that is being registered
     * @param      object               $component        a reference to the instance of the object on which the action is defined
     * @param      string               $callback         the name of the function definition on the $component
     * @param      int      optional    $priority         the priority at which the function should be fired
     * @param      int      optional    $accepted_args    the number of arguments that should be passed to the $callback
     */
    public function WP_AddAction($hook, $component, $callback, $priority = 10, $accepted_args = 1) 
    {
        /** The WordPress callback function is defined */
        $wordpress_callback = array(
            $component,
            $callback
        );
        /** If the function is not callable then an exception is thrown */
        if (!is_callable($wordpress_callback)) throw new \Exception("Function : " . $callback . " was not found");
        /** The wordpress configuration is fetched */
        $wordpress_configuration = $this->GetConfig("wordpress");
        $actions = $wordpress_configuration['actions'];
        /** The new action is added to the WordPress actions array */
        $actions = $this->WP_Add($actions, $hook, $component, $callback, $priority, $accepted_args);
        /** The updated actions data is saved to the application configuration */
        $this->SetConfig("wordpress", "actions", $actions);
    }
    /**
     * Add a new filter to the collection to be registered with WordPress
     *
     * @param      string               $hook             the name of the WordPress filter that is being registered
     * @param      object               $component        a reference to the instance of the object on which the filter is defined
     * @param      string               $callback         the name of the function definition on the $component
     * @param      int      optional    $priority         the priority at which the function should be fired
     * @param      int      optional    $accepted_args    the number of arguments that should be passed to the $callback
     */
    public function WP_AddFilter($hook, $component, $callback, $priority = 10, $accepted_args = 1) 
    {
        /** The WordPress callback function is defined */
        $wordpress_callback = array(
            $component,
            $callback
        );
        /** If the function is not callable then an exception is thrown */
        if (!is_callable($wordpress_callback)) throw new \Exception("Function : " . $callback . " was not found");
        /** The wordpress configuration is fetched */
        $wordpress_configuration = $this->GetConfig("wordpress");
        $filters = $wordpress_configuration['filters'];
        /** The new filter is added to the WordPress filters array */
        $filters = $this->WP_Add($filters, $hook, $component, $callback, $priority, $accepted_args);
        /** The updated actions data is saved to the application configuration */
        $this->SetConfig("wordpress", "filters", $filters);
    }
    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection
     *
     * @param    array                $hooks            the collection of hooks that is being registered (that is, actions or filters)
     * @param    string               $hook             the name of the WordPress filter that is being registered
     * @param    object               $component        a reference to the instance of the object on which the filter is defined
     * @param    string               $callback         the name of the function definition on the $component
     * @param    int      optional    $priority         the priority at which the function should be fired
     * @param    int      optional    $accepted_args    the number of arguments that should be passed to the $callback
     *
     * @return   type                                   the collection of actions and filters registered with WordPress
     */
    private function WP_Add($hooks, $hook, $component, $callback, $priority, $accepted_args) 
    {
        $hooks[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );
        return $hooks;
    }
    /**
     * Load the plugin text domain for translation
     */
    public function WP_LoadPluginTextDomain() 
    {
        /** Used to load the plugin's text domain */
        load_plugin_textdomain($this->GetConfig("wordpress", "plugin_text_domain") , false, $this->GetConfig("wordpress", "plugin_language_path"));
    }
    /**
     * The css files for admin pages are registered
     */
    public function WP_AdminEnqueueStyles() 
    {
        $this->WP_Enqueue("admin_styles", false);
    }
    /**
     * The javascript files for admin pages are registered
     */
    public function WP_AdminEnqueueScripts() 
    {
        $this->WP_Enqueue("admin_scripts", true);
    }
    /**
     * The javascript files for public pages are registered
     */
    public function WP_EnqueueScripts() 
    {
        $this->WP_Enqueue("public_scripts", true);
    }
    /**
     * The css files for public pages are registered
     */
    public function WP_EnqueueStyles() 
    {
        $this->WP_Enqueue("public_styles", false);
    }
    /**
     * The WordPress settings menu is created
     */
    public function WP_DisplaySettings() 
    {
        /** The wordpress configuration is fetched */
        $wordpress_configuration = $this->GetConfig("wordpress");
        /** The object used to set the settings page content is fetched */
        $object_name = $wordpress_configuration['settings_page_content_callback'][0];
        $object = $this->GetComponent($object_name);
        $wordpress_configuration['settings_page_content_callback'][0] = $object;
        /** If the settings page callback is not callable then an exception is thrown */
        if (!is_callable($wordpress_configuration['settings_page_content_callback'])) throw new \Exception("Invalid callback function defined for settings page");
        /** The settings menu is created */
        \add_options_page($wordpress_configuration['settings_page_title'], $wordpress_configuration['settings_menu_title'], $wordpress_configuration['settings_menu_permissions'], $wordpress_configuration["settings_page_url"], $wordpress_configuration['settings_page_content_callback']);
    }
    /**
     * The WordPress admin page is initialized
     *
     * It calls the admin init callback registered by the user in the application configuration
     */
    public function WP_InitAdmin() 
    {
        /** The user id of the logged in user */
        $user_id = get_current_user_id();
        /** The user id is set in the application configuration */
        $this->SetConfig("wordpress", "user_id", $user_id);
        /** The wordpress configuration is fetched */
        $wordpress_configuration = $this->GetConfig("wordpress");
        /** If the user did not specify the admin init callback, then the function returns */
        if (!isset($wordpress_configuration['admin_init_callback'][0])) return;
        /** The object used to set the settings page content is fetched */
        $object_name = $wordpress_configuration['admin_init_callback'][0];
        $object = $this->GetComponent($object_name);
        $wordpress_configuration['admin_init_callback'][0] = $object;
        /** If the init admin page callback is not callable then an exception is thrown */
        if (!is_callable($wordpress_configuration['admin_init_callback'])) throw new \Exception("Invalid callback function defined for initializing admin page");
        /** If the init admin page callback is callable then it is called */
        call_user_func($wordpress_configuration['admin_init_callback']);
    }
    /**
     * The given wordpress scripts/styles are enqueued
     *
     * @param $configuration_name the name of the application configuration that contains the scripts/styles to enqueue
     * @param $is_script used to indicate if the given application configuration is a script or style
     */
    public function WP_Enqueue($configuration_name, $is_script) 
    {
        /** The wordpress configuration is fetched */
        $wordpress_configuration = $this->GetConfig("wordpress");
        /** If the configuration is not set then it is returned */
        if (!isset($wordpress_configuration[$configuration_name])) return;
        for ($count = 0;$count < count($wordpress_configuration[$configuration_name]);$count++) 
        {
            if (!$is_script) wp_enqueue_style($wordpress_configuration[$configuration_name][$count]['name'], $this->GetConfig("wordpress", "plugin_url") . "/" . $wordpress_configuration[$configuration_name][$count]['file'], $wordpress_configuration[$configuration_name][$count]['dependencies'], $this->GetConfig("wordpress", "plugin_version") , $wordpress_configuration[$configuration_name][$count]['media']);
            else wp_enqueue_script($wordpress_configuration[$configuration_name][$count]['name'], $this->GetConfig("wordpress", "plugin_url") . "/" . $wordpress_configuration[$configuration_name][$count]['file'], $wordpress_configuration[$configuration_name][$count]['dependencies'], $this->GetConfig("wordpress", "plugin_version") , false);
            /** If the localization data is specified for the script then it is loaded to WordPress using wp_localize_script */
            if (isset($wordpress_configuration[$configuration_name][$count]['localization'])) 
            {
                wp_localize_script($wordpress_configuration[$configuration_name][$count]['localization']["name"], $wordpress_configuration[$configuration_name][$count]['localization']["variable_name"], $wordpress_configuration[$configuration_name][$count]['localization']["data"]);
            }
        }
    }
    /**
     * Used to display admin notices
     *
     * This function is used to display admin notice messages
     * It is called each time an admin page is loaded
     * It displays a notifcation message if the plugin has not been configured from the settings page
     *
     * @param string $information_message the information message to display to the user
     */
    public function DisplayAdminNotices($information_message) 
    { 
        /** The path to the template folder */
        $template_folder_path = $this->GetConfig("path", "application_template_path");
        /** The template parameters for the admin notice html template */
        $template_parameters = array(
            "admin-notice-class" => "update-nag is-dismissable",
            "admin-notice-message" => $information_message
        );
        /** The path to the admin notice html template file */
        $template_file_path = $template_folder_path . DIRECTORY_SEPARATOR . "admin_notice.html";
        /** The html template is rendered using the given parameters */
        $admin_notice_html = $this->GetComponent("template_helper")->RenderTemplateFile($template_file_path, $template_parameters);
        /** The admin notice message box is displayed */
        $this->DisplayOutput($admin_notice_html);
    }
    /**
     * The plugin options are fetched
     *
     * It fetches the plugin options
     *
     * @param string $option_id id of the option to fetch
     *
     * @return array $options the plugin options
     */
    public function GetPluginOptions($option_id) 
    {
        /** The current plugin options are fetched from WordPress */
        $options = get_option($option_id);
        /** If the options is a json encoded string then it is decoded */
        if ($this->GetComponent("string")->IsJson($options)) $options = json_decode($options, true);
        return $options;
    }
    /**
     * The plugin options are deleted
     *
     * It deletes the plugin options and meta options
     *
     * @return boolean $is_deleted used to indicate if the option was successfully deleted
     */
    public function DeletePluginOptions() 
    {
        /** The options id is fetched */
        $option_id = $this->GetComponent("application")->GetOptionsId("options");
        /** The meta options id is fetched */
        $meta_option_id = $this->GetComponent("application")->GetOptionsId("meta_options");
        /** The current plugin options are deleted from WordPress */
        $is_deleted = delete_option($option_id) & delete_option($meta_option_id);
        return $is_deleted;
    }
    /**
     * The rpc function is authenticated
     *
     * It escapes the rpc arguments
     * It also authenticates the rpc function call
     * It uses the given user name and password
     * And checks if WordPress has a user with this information
     *
     * @param array $args the parameters of the rpc function
     *
     * @return mixed $rpc_function_parameters the escaped rpc parameters or
     * rpc server error if the user login information is not correct
     */
    public function RpcAuthentication($args) 
    {
        /** The global xmlrpc server object */
        global $wp_xmlrpc_server;
        /** The rpc function parameters */
        $rpc_function_parameters = array();
        /** The rpc arguments are escaped */
        $wp_xmlrpc_server->escape($args);
        /** The user name */
        $username = $args[1];
        /** The password */
        $password = $args[2];
        /** If the login info is not correct then false is returned */
        if (!$user = $wp_xmlrpc_server->login($username, $password)) $rpc_function_parameters = $wp_xmlrpc_server->error;
        else $rpc_function_parameters = $args;
        return $rpc_function_parameters;
    }
    /**
     * Used to add a dashboard widget
     *
     * It adds the given dashboard widget
     *
     * @param string $option_id id of the option to fetch
     *
     * @return array $options the plugin options
     */
    public function AddDashboardWidget($widget_id, $widget_title, $widget_callback) 
    {
        wp_add_dashboard_widget($widget_id, $widget_title, $widget_callback);
    }
    /**
     * The options id is fetched
     *
     * It fetches the options id
     * The options id is the option name with plugin prefix as the prefix and user id as the suffix
     *
     * @param string $option_name the name of the option
     *
     * @return string $option_id the id of the option
     */
    public function GetOptionsId($option_name) 
    {
        /** The user id of the logged in user */
        $user_id = get_current_user_id();
        /** The wordpress configuration is fetched */
        $wordpress_configuration = $this->GetConfig("wordpress");
        /** The plugin settings id */
        $options_id = $wordpress_configuration['plugin_prefix'] . '_' . $option_name . '_' . $user_id;
        
        return $options_id;
    }
    /**
     * Used to add a new custom taxonomy type
     *
     * It adds a new custom taxonomy type using the given parameters
     * If some parameters are not given. e.g the labels and args parameters are not given
     * Then they are auto generated
     * The user given parameters are merged with the default parameters
     *
     * @param string $name the full name of the custom taxonomy
     * @param string $singular_name the singular name for the custom taxonomy
     * @param string $post_type the post type or custom post type that will be used as object type of the taxonomy
     * @param array $labels the labels for the new custom taxonomy
     * @param array $args the parameters for the new custom taxonomy
     */
    public function AddNewCustomTaxonomy($name, $singular_name, $post_type, $labels = array() , $args = array()) 
    {
        /** The post type is converted to lower case. spaces are replaced with - */
        $post_type = str_replace(" ", "-", strtolower($post_type));
        /** The default labels for new custom taxonomy */
        $default_labels = array(
            'name' => _x($name, $singular_name) ,
            'singular_name' => _x($singular_name, $singular_name) ,
            'search_items' => __('Search ' . $singular_name) ,
            'popular_items' => __('Popular ' . $singular_name) ,
            'all_items' => __('All ' . $singular_name) ,
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit ' . $singular_name) ,
            'update_item' => __('Update ' . $singular_name) ,
            'add_new_item' => __('Add New ' . $singular_name) ,
            'new_item_name' => __('New ' . $singular_name . ' Name') ,
            'separate_items_with_commas' => __('Separate ' . $name . ' with commas') ,
            'add_or_remove_items' => __('Add or remove ' . $name) ,
            'choose_from_most_used' => __('Choose from the most used ' . $name) ,
            'not_found' => __('No ' . $name . ' found.') ,
            'menu_name' => __($name) ,
        );
        /** The default arguments for the new custom taxonomy */
        $default_args = array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array(
                'slug' => strtolower($singular_name)
            ) ,
        );
        /** The default labels are merged with user given labels */
        $default_labels = array_merge($default_labels, $labels);
        /** The default arguments are merged with user given arguments */
        $default_args = array_merge($default_args, $args);
        /** The labels are added to the arguments */
        $default_args['labels'] = $default_labels;
        /** The new custom taxonomy is registered */
        register_taxonomy(strtolower($singular_name) , $post_type, $default_args);
    }
    /**
     * Used to add a new custom post type
     *
     * It adds a new custom post type using the given parameters
     * If some parameters are not given. e.g the labels and args parameters are not given
     * Then they are auto generated
     * The user given parameters are merged with the default parameters
     *
     * @param string $name the full name of the custom post type
     * @param string $singular_name the singular name for the custom post type
     * @param array $labels the labels for the new custom post type
     * @param array $args the parameters for the new custom post type
     */
    public function AddNewCustomPostType($name, $singular_name, $labels = array() , $args = array()) 
    {
        /** The plugin text domain */
        $plugin_text_domain = $this->GetConfig("wordpress", "plugin_text_domain");
        /** The default labels for new custom post type */
        $default_labels = array(
            'name' => _x($name, $name, $plugin_text_domain) ,
            'singular_name' => _x($singular_name, $name, $plugin_text_domain) ,
            'menu_name' => _x($name, $name, $plugin_text_domain) ,
            'name_admin_bar' => _x($singular_name, $name, $plugin_text_domain) ,
            'add_new' => _x('Add New', $name, $plugin_text_domain) ,
            'add_new_item' => __('Add New ' . $singular_name, $plugin_text_domain) ,
            'new_item' => __('New ' . $singular_name, $plugin_text_domain) ,
            'edit_item' => __('Edit ' . $singular_name, $plugin_text_domain) ,
            'view_item' => __('View ' . $singular_name, $plugin_text_domain) ,
            'all_items' => __('All ' . $name, $plugin_text_domain) ,
            'search_items' => __('Search ' . $name, $plugin_text_domain) ,
            'parent_item_colon' => __('Parent :' . $name, $plugin_text_domain) ,
            'not_found' => __('No ' . $name . ' found.', $plugin_text_domain) ,
            'not_found_in_trash' => __('No ' . $name . ' found in Trash.', $plugin_text_domain)
        );
        /** The default arguments for the new custom post type */
        $default_args = array(
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array(
                'slug' => strtolower($singular_name)
            ) ,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array(
                'title',
                'custom-fields'
            )
        );
        /** The default labels are merged with user given labels */
        $default_labels = array_merge($default_labels, $labels);
        /** The default arguments are merged with user given arguments */
        $default_args = array_merge($default_args, $args);
        /** The labels are added to the arguments */
        $default_args['labels'] = $default_labels;
        /** The new custom post type is registered */
        register_post_type($name, $default_args);
    }
    /**
     * Used to save plugin options
     *
     * It saves the given WordPress plugin options
     *
     * @param array $options the plugin options
     * @param string $option_id id of the option to save
     */
    public function SavePluginOptions($options, $option_id) 
    {
        /** The options values are saved */
        update_option($option_id, $options);
    }
    /**
     * Used to register a shortcode
     *
     * It registers the given shortcode
     *
     * @param string $name the shortcode name
     * @param string $handler the shortcode handler function
     */
    public function RegisterShortcode($name, $handler) 
    {
        /** The shortcode is registered */
        add_shortcode($name, $handler);
    }
    /**
     * Used to register the widgets that are given in application configuration
     *
     * It registers the Holy Quran widget
     */
    public function RegisterWidgets() 
    {
        /** The list of widget classes to register */
        $widget_classes = $this->GetConfig("wordpress", "custom_widgets");
        /** Each widget class is registered */
        for ($count = 0;$count < count($widget_classes);$count++) 
        {
            /** The name of the widget class */
            $widget_class = $widget_classes[$count]['class'];
            /** The widget class is registered */
            register_widget($widget_class);
        }
    }
    /**
     * Used to register functions that extend the WordPress XML-RPC interface
     *
     * It adds functions to the WordPress XML-RPC interface
     *
     * @since 1.0.0
     * @param array $methods the list of xml-rpc methods provided by WordPress
     *
     * @return array $methods the list of updated WordPress XML-RPC methods
     */
    public function RegisterXmlRpcMethods($methods) 
    {
        /** The xml-rpc methods are fetched from application configuration */
        $rpc_methods = $this->GetConfig("wordpress", "custom_xmlrpc_methods");
        /** The WordPress plugin prefix */
        $plugin_prefix = $this->GetConfig("wordpress", "plugin_prefix");
        /** Each rpc methods is registered with wordpress */
        for ($count = 0;$count < count($rpc_methods);$count++) 
        {
            /** The xml-rpc method callback */
            $rpc_method_callback = $rpc_methods[$count];
            /** The callback object is fetched */
            $rpc_method_callback[0] = $this->GetComponent($rpc_method_callback[0]);
            /** The xml-rpc method is registered */
            $methods[$plugin_prefix . '.' . $rpc_method_callback[1]] = $rpc_method_callback;
        }
        return $methods;
    }
    /**
     * Used to check the current plugin version
     *
     * It checks the version of the plugin on plugins.svn.wordpress.org
     * If the version of the plugin does not match the version of the installed plugin then
     * Plugin returns false. Otherwise plugin returns true
     *
     * @return boolean $is_latest_version indicates if the latest version of the plugin is installed
     */
    public function IsLatestPluginVersionInstalled() 
    {
        /** Indicates if the current plugin version is the latest version */
        $is_latest_version = true;
        /** If the current internet connection is not active */
        if (!$this->GetComponent("filesystem")->IsInternetConnectionValid()) return $is_latest_version;
        /** The options id for the plugin meta options is fetched */
        $meta_options_id = $this->GetComponent("application")->GetOptionsId("meta_options");
        /** The plugin meta options are fetched */
        $meta_options = $this->GetComponent("application")->GetPluginOptions($meta_options_id);
        /** If the plugin version was not saved in the meta options */
        if (!isset($meta_options['plugin_version_check_date']) || (isset($meta_options['plugin_version_check_date']) && (time() - $meta_options['plugin_version_check_date']) > (2 * 7 * 3600 * 24))) 
        {
            /** The url of the readme.txt file */
            $readme_file_url = $this->GetConfig("path", "readme_file_url");
            /** The readme.txt file of the plugin is fetched from the WordPress plugin repository */
            $file_contents = $this->GetComponent("filesystem")->GetFileContent($readme_file_url);
            /** The stable tag value is extracted */
            preg_match_all("/Stable tag: (.+)/i", $file_contents, $matches);
            /** The latest plugin version */
            $latest_plugin_version = $matches[1][0];
            /** The plugin file path */
            $plugin_file_path = $this->GetConfig("path", "base_path") . DIRECTORY_SEPARATOR . "index.php";
            /** The plugin meta data is fetched */
            $plugin_data = get_plugin_data($plugin_file_path);
            /** The current plugin version */
            $current_plugin_version = $plugin_data['Version'];
            /** If the latest plugin version is installed */
            if (trim(str_replace(".", "", $latest_plugin_version)) != trim(str_replace(".", "", $current_plugin_version))) $is_latest_version = false;
            /** The plugin version check date is updated */
            $meta_options['plugin_version_check_date'] = time();
            /** The plugin meta options are updated */
            $this->GetComponent("application")->SavePluginOptions($meta_options, $meta_options_id);
        }
        return $is_latest_version;
    }
    /**
     * Used to import the contents of an array to WordPress
     *
     * It creates a new custom post for each element in the array
     *
     * @param string $post_type the name of the post type
     * @param array $file_details the details of the file to be imported
     *    data => the array contents
     *    key_field => the name of the key field used to uniquely identify the post
     *    title_field => the field name that will be used for the post title
     *    content_field => the field name that will be used for the post content
     *    fields => the list of field names. all field names except for title and content field names are considered as custom fields
     *    fields_to_ignore => the list of fields to exlude from the import
     *
     * @return int posts_added the number of custom posts that were added
     */
    public function ImportFile($post_type, $file_details) 
    {
        /** The number of meta data posts that were added */
        $posts_added = 0;
        /** The configuration object is fetched */
        $configuration_object = $this->GetConfigurationObject();
        /** The data to be imported */
        $data = $file_details['data'];
        /** The name of the key field */
        $key_field = $file_details['key_field'];
        /** The title field */
        $title_field = $file_details['title_field'];
        /** The content field */
        $content_field = $file_details['content_field'];
        /** The name of all the fields of the file */
        $field_list = $file_details['fields'];
        /** The list of fields to ignore */
        $fields_to_ignore = $file_details['fields_to_ignore'];
        /** Each data item is added to a wordpress post */
        for ($count1 = 0;$count1 < count($data);$count1++) 
        {
            /** The data to be saved */
            $post_data = array();
            /** The post author is set to the user id of the logged in user */
            $post_data['post_author'] = $file_details['user_id'];
            /** The concatenation of all the field values */
            $combined_values = "";
            /** The data to be saved is generated */
            for ($count2 = 0;$count2 < count($data[$count1]);$count2++) 
            {
                /** The field name */
                $field_name = $field_list[$count2];
                /** The field value */
                $field_value = $data[$count1][$count2];
                /** If the field name is not in list of fields to ignore */
                if (!in_array($field_name, $fields_to_ignore)) 
                {
                    /** If the field name does not match the title field then the field is added to custom field list */
                    if ($field_name == $title_field) 
                    {
                        /** The title for custom post */
                        $post_data['post_title'] = $field_value;
                    }
                    /** The field name and value are added as custom field */
                    $post_data["custom_" . $field_name] = $field_value;
                }
            }
            /** The post data is sorted by key */
            ksort($post_data, SORT_STRING);
            /** Each field value is base64 encoded */
            foreach ($post_data as $field_name => $field_value) 
            {
                /** If the field is a custom field */
                if (strpos($field_name, "custom_") !== false) 
                {
                    /** All the custom field values are combined. each value is first base64 encoded */
                    $combined_values = $combined_values . base64_encode($field_value);
                }
            }
            /** The md5 checksum of all the field values. It is used to ensure data has not been updated */
            $post_data['custom_checksum'] = md5($combined_values);
            /** The configuration object is fetched */
            $parameters['configuration'] = $configuration_object;
            /** WordPress data object is created */
            $wordpress_data_object = new WordPressDataObject($parameters);
            /** The parameters for the WordPress object. It indicates the type of object to be created */
            $meta_information = array(
                "data_type" => $post_type,
                "object_type" => "post"
            );
            /** The table name and field name are set */
            $wordpress_data_object->SetMetaInformation($meta_information);
            /** The WordPress data object is set to read/write */
            $wordpress_data_object->SetReadOnly(false);
            /** The data is set to the WordPress data object */
            $wordpress_data_object->SetData($post_data);
            /** The key field for the object is set */
            $wordpress_data_object->SetKeyField($key_field);
            /** If the WordPress post does not exist then it is saved */
            if (!$wordpress_data_object->RecordExists()) 
            {
                /** The WordPress data is saved */
                $wordpress_data_object->Save();
            }
            /** The posts added are increased */
            $posts_added++;
        }
        return $posts_added;
    }
    /**
     * Register the filters and actions with WordPress
     *
     * @return boolean $output used to indicate that application has no output
     * all output is sent by wordpress actions
     */
    public function Main() 
    {
        /** The wordpress configuration is fetched */
        $wordpress_configuration = $this->GetConfig("wordpress");
        /** Used to register the function that will be called when the plugin is activated */
        \register_activation_hook($this->GetConfig("wordpress", "plugin_file_path") , array(
            $this->GetComponent("application") ,
            'WP_Activate'
        ));
        /** Used to register the function that will be called when the plugin is deactivated */
        \register_activation_hook($this->GetConfig("wordpress", "plugin_file_path") , array(
            $this->GetComponent("application") ,
            'WP_Deactivate'
        ));
        $filters = $wordpress_configuration['filters'];
        $actions = $wordpress_configuration['actions'];
        /** The filters are registered with WordPress */
        foreach ($filters as $hook) 
        {
            \add_filter($hook['hook'], array(
                $hook['component'],
                $hook['callback']
            ) , $hook['priority'], $hook['accepted_args']);
        }
        /** The actions are registered with WordPress */
        foreach ($actions as $hook) 
        {
            \add_action($hook['hook'], array(
                $hook['component'],
                $hook['callback']
            ) , $hook['priority'], $hook['accepted_args']);
        }
        $output = false;
        return ($output);
    }
}
