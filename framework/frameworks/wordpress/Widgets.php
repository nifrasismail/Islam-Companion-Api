<?php

namespace Framework\Frameworks\WordPress;

use \Framework\Configuration\Base as Base;

/**
 * This class implements the widgets class
 * It provides functions that help in construction of wordpress widgets
 * 
 * It is used to implement the main functions of the plugin
 * 
 * @category   Framework
 * @package    WordPress
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0 
 */
abstract class Widgets extends \WP_Widget
{	    
    /** The plugin text domain */
	private $plugin_text_domain;
	/** The application configuration object */
	protected $configuration;
	/** The plugin options */		
	protected $options;
	/**
	 * Used to register the widget with WordPress
	 */
	function __construct() {
		/** The widget information is fetched */
		$widget_information               = $this->GetWidgetInformation();
		/** The application configuration object is created */
		$this->configuration              = new $widget_information['application_configuration_class'](array("context" => "local api"));
		/** The application configuration object is initialized */
		$this->configuration->Initialize();
		/** The options id is fetched */
	    $options_id                       = $this->configuration->GetComponent("application")->GetOptionsId("options");			           
		/** The current plugin options are fetched */
		$this->options                    = $this->configuration->GetComponent("application")->GetPluginOptions($options_id);		
		/** The parent constructor is called */
		parent::__construct(
			$widget_information['widget_id'], // Base ID
			$widget_information['widget_name'], // Name
			$widget_information['widget_description'] // Args
		);
	}
	
	/**
	 * Returns the main content of the widget frontend
	 * Abstract function. Must be implemented by child class
	 *
	 * It returns html for the main widget content
	 *	 
	 * @param array $instance saved values from database.
	 */
	abstract protected function GetMainWidgetContent($instance);
	
	/**
	 * Used to return the widget information
	 * Abstract function. Must be implemented by child class
	 * 
	 * @return array $widget_information the widget meta information
	 *    widget_id => int the widget id
	 *    widget_name => string the widget name
	 *    widget_description => string the widget description
	 */
	abstract protected function GetWidgetInformation();		
	
	/**
	 * Used to return the widget form fields
	 * Abstract function. Must be implemented by child class
	 * 
	 * @param array $instance Previously saved values from database
	 * 
	 * @return array $field_list the list of form fields is returned
	 */
	abstract protected function RenderFormFields($instance);
	
	/**
	 * Displays the widget contents on the frontend
	 *
	 * It displays the html of the widget
	 *
	 * @param array $args widget arguments
	 * @param array $instance saved values from database.
	 */
	public function widget($args, $instance) {
		/** The before widget html is displayed */		
		echo $args['before_widget'];
		/** If the widget title was set in widget configuration, then it is displayed */
		if ( ! empty( $instance['title'] ) ) {
			/** The widget title is displayed */
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		/** The main widget content. It is provided by the child class */
		$main_widget_content    = $this->GetMainWidgetContent($instance);
		/** The shortcode html is displayed */
		echo $main_widget_content;
		/** The after widget html is displayed */
		echo $args['after_widget'];
	}

	/**
	 * Used to generate html of widget form fields
	 *
	 * It generates html of form fields given the form field definition	 
	 *
	 * @param array $instance Previously saved values from database
	 */
	public function form($instance) {
		/** The form fields are fetched */
		$field_list                                  = $this->RenderFormFields($instance);		
		/** The widget form html */
		$widget_form_html                            = "";		
		/** Each field is rendered to html */
		foreach ($field_list as $field_name => $field_details) {
			/** The field id */
			$field_id                                = $this->get_field_id($field_name);			
			/** The field value is set */
	        $field_value                             = ! empty($instance[$field_name]) ? $instance[$field_name] : (isset($field_details['value']) ? $field_details['value'] : "");			
			/** The css class value is set */
	        $css_class                               = (isset($field_details['css_class']) ? $field_details['css_class'] : "widefat");
			/** The template parameters */
		    $template_parameters                     = array("css_class" => $css_class, "id" => $field_id, "name" => $this->get_field_name($field_name), "value" => $field_value, "type" => $field_details["type"]);
			/** If the field type is text, hidden or number */
			if ($field_details["type"] == "text" || $field_details["type"] == "number" || $field_details["type"] == "hidden") {				
				/** If the field type is text */
				if ($field_details["type"] == "text") {
					/** The field readonly value */
				    $template_parameters['readonly'] = (isset($field_details['readonly'])) ? $field_details['readonly']: "";					 
				}
				/** If the field type is number */
				else if ($field_details["type"] == "number") {
					/** The field min value */
					$template_parameters['min']     = (isset($field_details['min'])) ? $field_details['min']: "";
					/** The field max value */
					$template_parameters['max']     = (isset($field_details['max'])) ? $field_details['max']: "";
				}				
				/** The input field html is fetched */
				$form_field_html                     = $this->configuration->GetComponent("template")->Render($field_details["type"], $template_parameters);				
			}
			/** If the field type is dropdown */
			else if ($field_details["type"] == "dropdown") {
				/** The dropdown field options */
			    $field_options                       = (isset($field_details['options'])) ? $field_details['options'] : "";
				/** The template parameters */
			    $template_parameters["options"]      = $field_options;
				/** The dropdown html is fetched */
				$form_field_html                     = $this->GetDropdownHtml($template_parameters);
			}
			/** If the field type is hidden, then the label is not added */
			if ($field_details["type"] != "hidden") {
			    /** The form label template parameters */
			    $form_label_parameters               = array("label_id" => $field_id, "label_text" => ucwords(str_replace("_", " ",$field_name)));
			    /** If the extra label text is present then it is used */
			    if (isset($field_details['extra_label_text'])) {
				    $form_label_parameters['label_text'] = $form_label_parameters['label_text'] . " " . $field_details['extra_label_text']; 
			    }
			    /** the form label is fetched */
			    $form_label_html                     = $this->configuration->GetComponent("template")->Render("label", $form_label_parameters);
				/** The tag replacement array is built */ 
                $template_parameters                 = array("content" => $form_label_html . $form_field_html);			    
             }
			else {
				/** The tag replacement array is built */ 
                $template_parameters                 = array("content" => $form_field_html);
			}
			/** The template file name */
		    $template_file_name                      = plugin_dir_path( __FILE__ ) . "templates" . DIRECTORY_SEPARATOR . "paragraph.html";            
			/** The form label and form field are placed in a paragraph */
            $form_field_html                         = $this->configuration->GetComponent("template")->Render("paragraph", $template_parameters);        
			/** The form field html is added to the widget form */
			$widget_form_html                        = $widget_form_html . $form_field_html;		 		
		}
		/** The widget form is displayed */
		echo ($widget_form_html);
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance values just sent to be saved.
	 * @param array $old_instance previously saved values from database.
	 *
	 * @return array $instance updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		/** Each field is sanitized */
		foreach ($new_instance as $field_name => $field_value) {
			/** The field id */
			$field_id            = ($field_name);
			/** The field value is updated */
			$instance[$field_id] = ( ! empty( $new_instance[$field_id] ) ) ? strip_tags( $new_instance[$field_id] ) : '';
		}

		return $new_instance;
	}
	
	/** 
     * Fetches the dropdown field html
     * 
     * @param array $dropdown_data an array containing the dropdown information
	 *    field_id => string the field id
	 *    default_value => string the default field value
	 *    options => array the dropdown options
	 *    css_class => string the css class for the select box
	 * 
	 * @return string $select_field_html the dropdown html
     */
    private function GetDropdownHtml($dropdown_data)
    {
        /** The field name */
		$field_name           = $dropdown_data['name'];		
		/** The field id */
		$field_id             = $dropdown_data['id'];		
		/** The default field value */
		$default_value        = $dropdown_data['value'];
		/** The css class for the select box */
		$css_class            = $dropdown_data['css_class'];
		/** The select option array is initialized */
		$select_options       = array();
		/** The information used to create the select dropdown */
        $dropdown_information = array(
            "options" => $dropdown_data["options"],
            "name" => $field_name
        );      		
        /** The select options are built */
        for ($count = 0; $count < count($dropdown_information['options']); $count++) {        	
        	/** The options list*/
            $options          = $dropdown_information['options'][$count];
			/** The select box options */
            $select_options[] = array(
                "text" => $options['text'],
                "value" => $options['value'],
                "selected" => ($default_value == $options['value']) ? "SELECTED" : ""
            );
        }
        /** The tag replacement array is built */
        $template_parameters = ($select_options);
        /** The select option template is rendered */
        $option_field_html   = $this->configuration->GetComponent("template")->Render("option", $template_parameters);
        /** The tag replacement array is built */
        $template_parameters = array(
            array(
                "id" => $field_id,
                "name" => $field_name,
                "options" => $option_field_html,
                "css_class" => $css_class
            )
        );
		/** The select option template is rendered */
        $select_field_html   = $this->configuration->GetComponent("template")->Render("select", $template_parameters);
       
	    return $select_field_html;
    }
}