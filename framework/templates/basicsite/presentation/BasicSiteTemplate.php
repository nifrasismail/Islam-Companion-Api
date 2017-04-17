<?php
namespace Framework\Templates\BasicSite\Presentation;
use \Framework\Application\TemplateEngine as TemplateEngine;
/**
 * This class provides functions for rendering Basic Site templates
 *
 * It extends the TemplateEngine class
 * It contains functions that allow creating html objects based on Basic Site templates
 * It has only one public method called Render which is declared in the abstract parent class
 *
 * @category   Framework
 * @package    Templates
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class BasicSiteTemplate extends TemplateEngine
{
    /**
     * Used to render the required user interface template
     *
     * It renders the required user interface template using the given parameters
     *
     * @param string $option the user interface element name. e.g textbox or html_table
     * @param array $parameters the parameters used to render the user interface item
     *
     * @return string $user_interface_html the html string of the user interface item. e.g table html string
     */
    public function Render($option, $parameters) 
    {
        $user_interface_html = "";
        if ($option == "html_table") $user_interface_html = $this->RenderHtmlTable($parameters);
        else if ($option == "simple_html_table") $user_interface_html = $this->RenderSimpleHtmlTable($parameters);
        else if ($option == "css_js_tags") $user_interface_html = $this->RenderCssJsFileTags($parameters);
        else if ($option == "datalist_options") $user_interface_html = $this->RenderDataListOptions($parameters);
        else if ($option == "root") $user_interface_html = $this->RenderApplicationTemplate($option);
        else if ($option == "selectbox") $user_interface_html = $this->RenderSelectbox($parameters);
        else if ($option == "link") $user_interface_html = $this->RenderLink($parameters);
        else if ($option == "base_page") $user_interface_html = $this->RenderBasePage($parameters);
        else 
        {
            /** The template file name */
            $template_file_name = $option . ".html";
            /** The general template file is rendered */
            $user_interface_html = $this->RenderTemplate($template_file_name, $parameters);
        }
        return $user_interface_html;
    }
    /**
     * Used to render a template file
     *
     * It renders the given template file using the given template parameters
     *
     * @param string $template_file_name the template file name
     * @param array $parameters the parameters used to render the given html template file
     *
     * @return string $template_html the template html string
     */
    private function RenderTemplate($template_file_name, $parameters) 
    {
        /** The path to the framework template folder is fetched */
        $framework_template_folder_path = $this->GetConfig("path", "framework_template_path");
        /** The path to the application template folder is fetched */
        $application_template_folder_path = $this->GetConfig("path", "application_template_path");
        /** The template file path */
        $template_file_path = $application_template_folder_path . DIRECTORY_SEPARATOR . $template_file_name;
        if (!is_file($template_file_path)) 
        {
            $template_file_path = $framework_template_folder_path . DIRECTORY_SEPARATOR . $template_file_name;
            if (!is_file($template_file_path)) throw new \Exception("Template file: " . $template_file_name . " could not be found");
        }
        /** The callback function for fetching missing template parameters */
        $callback = array(
            $this,
            "GetConfig",
            array(
                "path"
            )
        );
        /** The general template is rendered using the given template parameters */
        $template_html = \Framework\Utilities\UtilitiesFramework::Factory("template")->RenderTemplateFile($template_file_path, $parameters, $callback);
        return $template_html;
    }
    /**
     * Used to render select box
     *
     * It displays a selectbox using the given options
     *
     * @param array $parameters an array with following keys:
     *    selectbox_name => string name of the selectbox
     *    selectbox_id => int id of the selectbox
     *    selectbox_selected_value => string value of selectbox
     *    selectbox_onchange => string the javascript to run when the user selects an option
     *    selectbox_options => array selectbox options. the selectbox options. each array element contains the option text
     *              it can optionally contain an array with 2 keys.
     *        text => string the text of the option and
     *        value => string the value of the option
     *
     * @return string $selectbox_html the textbox html string
     */
    private function RenderSelectbox($parameters) 
    {
        /** The select box parameters are initialized */
        $selectbox_parameters = array();
        $selectbox_options_parameters = array();
        /** The value to select in the selectbox */
        $selected_value = $parameters['selectbox_selected_value'];
        /** The selected text */
        $selected_text = "";
        /** The selectbox parameters are formatted */
        for ($count = 0;$count < count($parameters['selectbox_options']);$count++) 
        {
            $option = $parameters['selectbox_options'][$count];
            /** If the selectbox options are given as a string */
            if (!is_array($option)) 
            {
                if ($option == $selected_value) 
                {
                    $selectbox_options_parameters[] = array(
                        "text" => $option,
                        "value" => $option,
                        "selected" => "SELECTED"
                    );
                    /** The selected text is set */
                    $selected_text = ($option);
                }
                else $selectbox_options_parameters[] = array(
                    "text" => $option,
                    "value" => $option,
                    "selected" => ""
                );
            }
            else
            {
                /** The value is html encoded */
                $option['value'] = ($option['value']);
                if ($selected_value == $option['value']) 
                {
                    $option['selected'] = "SELECTED";
                    /** The selected text is set */
                    $selected_text = $option['text'];                    
                }
                else $option['selected'] = "";
                /** If the selectbox options are given as an array */
                if (isset($option['text']) && isset($option['value'])) 
                {
                    $selectbox_options_parameters[] = $option;
                }
            }
        }
        /** If the selectbox options were given */
        if (count($selectbox_options_parameters) > 0) 
        {
            /** The selectbox options are rendered */
            $selectbox_options_html = $this->RenderTemplate("selectbox_option.html", $selectbox_options_parameters);
        }
        /** Else the selectbox options are set to empty string */
        else 
        {
            $selectbox_options_html = "";
        }
        /** The selectbox parameters */
        $selectbox_parameters = array(
            array(
                "selectbox_options" => $selectbox_options_html,
                "selectbox_name" => $parameters['selectbox_name'],
                "selectbox_id" => $parameters['selectbox_id'],
                "selectbox_selected_text" => $selected_text,
                "selectbox_onchange" => $parameters['selectbox_onchange']
            )
        );
        /** The selectbox html is rendered */
        $selectbox_html = $this->RenderTemplate("selectbox.html", $selectbox_parameters);
        return $selectbox_html;
    }
    /**
     * Used to render html string inside the base_page.html template
     *
     * It displays html content in the base_page.html template
     *
     * @param array $parameters the parameters containing the html content. it is an array with following keys:
     *    title => string the page title
     *    css_tags => string the css file names
     *    javascript_tags => string the javascript file names
     *    header => string the header html
     *    body => string the body html contents
     *
     * @return string $page_html the base page html string
     */
    private function RenderBasePage($parameters) 
    {
        $title = $parameters['title'];
        if (count($parameters['css_tags']) > 0) 
        {
            $css_file_list = array(
                "file_type" => "css",
                "file_list" => array()
            );
            for ($count = 0;$count < count($parameters['css_tags']);$count++) $css_file_list["file_list"][] = $parameters['css_tags'][$count];
            $css_tags = $this->RenderCssJsFileTags($css_file_list);
        }
        else $css_tags = "";
        if (count($parameters['javascript_tags']) > 0) 
        {
            $javascript_file_list = array(
                "file_type" => "javascript",
                "file_list" => array()
            );
            for ($count = 0;$count < count($parameters['javascript_tags']);$count++) $javascript_file_list["file_list"][] = $parameters['javascript_tags'][$count];
            $javascript_tags = $this->RenderCssJsFileTags($javascript_file_list);
        }
        else $javascript_tags = "";
        $body = $parameters['body'];
        $header = $parameters['header'];
        $base_page_parameters = array(
            array(
                "title" => $title,
                "css_tags" => $css_tags,
                "header" => $header,
                "javascript_tags" => $javascript_tags,
                "body" => $body
            )
        );
        /** The base page template is rendered */
        $page_html = $this->RenderTemplate("base_page.html", $base_page_parameters);
        return $page_html;
    }
    /**
     * Used to render a span tag
     *
     * It renders a span tag using the given parameters
     *
     * @param array $parameters the parameters containing the span tag text and css class
     *    class => string the css class
     *    text => string the inner html of the span tag
     *
     * @return string $link_html the hyperlink html string
     */
    private function RenderSpan($parameters) 
    {
        /** The full path to the template file */
        $template_file_path = $this->GetConfig("path", "framework_template_path") . DIRECTORY_SEPARATOR . "span.html";
        /** The span template is rendered using the given span parameters */
        $span_html = \Framework\Utilities\UtilitiesFramework::Factory("template")->RenderTemplateFile($template_file_path, $parameters);
        return $span_html;
    }
    /**
     * Used to render a list page
     *
     * It renders a list page using the given parameters
     *
     * @param array $parameters the parameters containing the list page parameters
     *    list_page_title => string the css class
     *    framework_template_url => string the inner html of the span tag
     *    table_data => the table data
     *
     * @return string $list_page_html the list page html
     */
    private function RenderListPage($parameters) 
    {
        /** The list page html is rendered */
        $list_page_html = $this->RenderTemplate("selectbox.html", $parameters);
        return $list_page_html;
    }
    /**
     * Used to render a hyperlink
     *
     * It returns a hyperlink string using given parameters
     *
     * @param array $parameters the parameters containing the hyperlink text and link
     *    link => string the hyperlink
     *    target => string the target for the hyper link
     *    text => string the link text
     *    name => string the link name
     *    id => int the link id
     *
     * @return string $link_html the hyperlink html string
     */
    private function RenderLink($parameters) 
    {
        $link = $parameters['link'];
        $text = $parameters['text'];
        $target = $parameters['target'];
        $id = (isset($parameters['id'])) ? $parameters['id'] : str_replace(" ", "_", strtolower($parameters['text']));
        /** The link parameters are set */
        $link_parameters = array(
            array(
                "href" => $link,
                "text" => $text,
                "target" => $target,
                "id" => $id
            )
        );
        /** The link html is rendered */
        $link_html = $this->RenderTemplate("link.html", $link_parameters);
        return $link_html;
    }
    /**
     * Used to render an alert box
     *
     * It displays a javascript alert box
     * And then runs custom javascript
     *
     * @param array $parameters the parameters containing the alert text and custom javascript
     *    alert_text => string the text in the alert box
     *    optional_javascript => string the custom javascript text
     *
     * @return string $textbox_html the textbox html string
     */
    private function RenderAlertConfirmation($parameters) 
    {
        $alert_text = $parameters['alert_text'];
        $optional_javascript = $parameters['optional_javascript'];
        /** The alert message parameters are set */
        $alert_confirmation_parameters = array(
            array(
                "alert_text" => $alert_text,
                "optional_javascript" => $optional_javascript
            )
        );
        /** The link html is rendered */
        $alert_confirmation_html = $this->RenderTemplate("alert_confirmation.html", $alert_confirmation_parameters);
        return $alert_confirmation_html;
    }
    /**
     * Used to render a form field
     *
     * It displays a form field table row
     *
     * @param array $parameters the parameters used to render the form field table row
     *    field_label => string the field label
     *    field_html => string the field html
     *
     * @return string $form_field_html the form field html string
     */
    private function RenderFormField($parameters) 
    {
        $field_label = $parameters['field_label'];
        $field_html = $parameters['field_html'];
        /** The form field parameters are set */
        $form_field_parameters = array(
            array(
                "field_label" => $field_label,
                "field_html" => $field_html
            )
        );
        /** The form field html is rendered */
        $inputbox_html = $this->RenderTemplate("form_field.html", $form_field_parameters);
        return $inputbox_html;
    }
    /**
     * Used to render an input box
     *
     * It displays a html input box
     *
     * @param array $parameters the parameters used to render the input box
     *    input_value => string the input box value
     *    input_name => string the input box name
     *    input_id => string the input box id
     *    input_type => string the input box type
     *
     * @return string $textbox_html the textbox html string
     */
    private function RenderInputBox($parameters) 
    {
        $input_value = $parameters['input_value'];
        $input_name = $parameters['input_name'];
        $input_id = $parameters['input_id'];
        $input_type = $parameters['input_type'];
        /** The inputbox parameters are set */
        $textbox_parameters = array(
            array(
                "input_value" => $input_value,
                "input_name" => $input_name,
                "input_id" => $input_id,
                "input_type" => $input_type
            )
        );
        /** The inputbox html is rendered */
        $inputbox_html = $this->RenderTemplate("input.html", $textbox_parameters);
        return $inputbox_html;
    }
    /**
     * Used to render a html text box
     *
     * It displays a html text box
     *
     * @param array $parameters the parameters used to render the textbox. It is an array with following keys
     *    textbox_list => string the textbox list name
     *    textbox_value => string the textbox value
     *    textbox_name => string the textbox name
     *    textbox_css_class => string the css class of the textbox
     *
     * @return string $textbox_html the textbox html string
     */
    private function RenderTextBox($parameters) 
    {
        $textbox_list = $parameters['textbox_list'];
        $textbox_value = $parameters['textbox_value'];
        $textbox_name = $parameters['textbox_name'];
        $textbox_id = $parameters['textbox_id'];
        $textbox_css_class = $parameters['textbox_css_class'];
        $textbox_onchange = $parameters['textbox_onchange'];
        /** The textbox parameters are set */
        $textbox_parameters = array(
            array(
                "textbox_list" => $textbox_list,
                "textbox_value" => $textbox_value,
                "textbox_name" => $textbox_name,
                "textbox_id" => $textbox_id,
                "textbox_css_class" => $textbox_css_class,
                "textbox_onchange" => $textbox_onchange
            )
        );
        /** The textbox html is rendered */
        $textbox_html = $this->RenderTemplate("textbox.html", $textbox_parameters);
        return $textbox_html;
    }
    /**
     * Used to get the datalist options string
     *
     * It renders the datalist_options.html template using the given option text array
     *
     * @param array $option_text list of option text values
     *
     * @return string $datalist_options_str the datalist options html string
     */
    private function RenderDataListOptions($option_text_arr) 
    {
        $datalist_names = array();
        for ($count = 0;$count < count($option_text_arr);$count++) 
        {
            $file_name = $option_text_arr[$count];
            $option_parameter = array(
                "option_text" => $file_name
            );
            $datalist_names[] = $option_parameter;
        }
        /** The datalist options html is rendered */
        $datalist_options = $this->RenderTemplate("datalist_option.html", $datalist_names);
        return $datalist_options;
    }
    /**
     * Used to get html string for basicsite table_data template
     * It uses simpler parameters as compared to RenderHtmlTable function
     *
     * It builds the html of the table_data template from the given parameters
     * It uses simple_table_row and simple_table_column templates
     *
     * @param array $parameters an array of table parameters. it contains 5 key value pairs
     *    table_rows => array an array whose each element is an array of column values
     *    table_css_class => the css class for the table
     * @throws Exception an object of type Exception is thrown if the number of elements in header_width array is not equal to number of elements in header_text array
     *
     * @return string $table_string the html table string containing all the table data
     */
    private function RenderSimpleHtmlTable($parameters) 
    {
        /** The CSS class for the table */
        $table_css_class = $parameters['table_css_class'];
        /** The table row html string is generated */
        $table_rows_params = array();
        for ($count1 = 0;$count1 < count($parameters['table_rows']);$count1++) 
        {
            /** The table_row_column.html template parameters are initialized */
            $table_col_params = array();
            /** The simple_table_column.html template parameters are generated */
            $table_row_col_text_arr = $parameters['table_rows'][$count1];
            for ($count2 = 0;$count2 < count($table_row_col_text_arr);$count2++) 
            {
                /** The column text */
                $column_text = $table_row_col_text_arr[$count2];
                /** The column css class */
                $table_col_params[] = array(
                    "column_data" => $column_text
                );
            }
            /** The full path to the template file */
            $template_file_path = $this->GetConfig("path", "framework_template_path") . DIRECTORY_SEPARATOR . "simple_table_column.html";
            /** The table_column.html template string is generated */
            $table_col_text = \Framework\Utilities\UtilitiesFramework::Factory("template")->RenderTemplateFile($template_file_path, $table_col_params);
            /** The generated table column html string is added to table_row.html template parameters */
            $table_rows_params[] = array(
                "table_column" => $table_col_text
            );
        }
        /** The full path to the template file */
        $template_file_path = $this->GetConfig("path", "framework_template_path") . DIRECTORY_SEPARATOR . "simple_table_row.html";
        /** The table_row_column.html template string is generated */
        $table_rows_text = \Framework\Utilities\UtilitiesFramework::Factory("template")->RenderTemplateFile($template_file_path, $table_rows_params);
        /** The table_data.html template parameters are generated */
        $table_data_params = array(
            array(
                "table_rows" => $table_rows_text,
                "table_css_class" => $table_css_class,
            )
        );
        /** The table_row_column.html template string is generated */
        $table_rows_str = $this->RenderTemplate("simple_table_data.html", $table_data_params);
        
        return $table_rows_str;
    }
    /**
     * Used to get html string for basicsite table_data template
     *
     * It builds the html of the table_data template from the given parameters
     * The html can be displayed in any template tag
     *
     * @param array $parameters an array of table parameters. it contains 5 key value pairs
     *    table_headers => array each element is a header element and is a text string
     *    header_widths => array header width of each column header
     *    table_rows => array each element is an array of column values
     *    table_row_css => array contains 2 elements. each element is a css class for a table row
     *    css_class => array each element is a css class that should be applied to each row column
     *    cell_attributes_callback => array a callback function that gives the cell attributes for given table row and column
     *    table_css_class => string the css class for the table
     * @throws Exception an object of type Exception is thrown if the number of elements in header_width array is not equal to number of elements in header_text array
     *
     * @return string $table_string the html table string containing all the table data
     */
    private function RenderHtmlTable($parameters) 
    {
        /** The table header */
        $table_header_params = "";
        if (is_array($parameters['table_headers']) && is_array($parameters['header_widths'])) 
        {
            if (count($parameters['table_headers']) != count($parameters['header_widths'])) throw new \Exception("Header width array count must match header text array count");
            /** The table header parameters are generated. each parameter contains a header_width and header_text */
            for ($count = 0;$count < count($parameters['table_headers']);$count++) 
            {
                /** The header text */
                $header_text = $parameters['table_headers'][$count];
                /** The width of a table header column */
                $header_width = ($parameters['header_widths'][$count]);
                /** The header column css class */
                $header_column_class = (isset($parameters['header_column_class'][$count])) ? $parameters['header_column_class'][$count] : "";
                /** The table header params are updated */
                $table_header_params[] = array(
                    "header_extra_css" => $header_width,
                    "header_text" => $header_text,
                    "header_column_class" => $header_column_class
                );
            }
        }
        /** The table css class */
        $table_css_class = $parameters['table_css_class'];
        /** The table header template is rendered using the table header parameters */
        $table_header_text = $this->RenderTemplate("table_header.html", $table_header_params);
        /** The table row html string is generated */
        $table_rows_params = array();
        for ($count1 = 0;$count1 < count($parameters['table_rows']);$count1++) 
        {
            /** The table_row_column.html template parameters are initialized */
            $table_col_params = array();
            /** The row css class is set. The css class repeats after each row */
            $row_css_class = (is_array($parameters['table_row_css'])) ? $parameters['table_row_css'][$count1 % 2] : "";
            /** The table_column.html template parameters are generated */
            $table_row_col_text_arr = $parameters['table_rows'][$count1];
            for ($count2 = 0;$count2 < count($table_row_col_text_arr);$count2++) 
            {
                $column_text = $table_row_col_text_arr[$count2];
                $cell_attributes_callback = (isset($parameters['cell_attributes_callback'])) ? $parameters['cell_attributes_callback'] : "";
                /** If the cell attributes callback function is callable then it is called */
                if (is_callable($cell_attributes_callback)) 
                {
                    $cell_attributes_callback_params = array(
                        $count1,
                        $count2,
                        count($table_row_col_text_arr)
                    );
                    $cell_attributes = call_user_func_array($cell_attributes_callback, $cell_attributes_callback_params);
                    $column_css_class = $cell_attributes['css_class'];
                    $column_span = $cell_attributes['column_span'];
                }
                else if ($cell_attributes_callback != "") throw new \Exception("Invalid table columnspan callback");
                else 
                {
                    $column_span = 1;
                    $column_css_class = "";
                }
                $table_col_params[] = array(
                    "column_data" => $column_text,
                    "css_class" => $column_css_class,
                    "column_span" => $column_span
                );
            }
            /** The table_column.html template string is generated */
            $table_col_text = $this->RenderTemplate("table_column.html", $table_col_params);
            /** The generated table column html string is added to table_row.html template parameters */
            $table_rows_params[] = array(
                "table_css_class" => $row_css_class,
                "table_column" => $table_col_text
            );
        }
        /** The table_row_column.html template string is generated */
        $table_rows_text = $this->RenderTemplate("table_row.html", $table_rows_params);
        /** The table_data.html template parameters are generated */
        $table_data_params = array(
            array(
                "table_headers" => $table_header_text,
                "table_rows" => $table_rows_text,
                "table_css_class" => $table_css_class
            )
        );
        /** The table_data.html template string is generated */
        $table_rows_str = $this->RenderTemplate("table_data.html", $table_data_params);
        
        return $table_rows_str;
    }
    /**
     * Used to get css and javascript tag values
     *
     * It returns css and javascript file tags for the given css and javascript files
     *
     * @param array $parameters the parameters used to render the css and javascript tags. It is an array with following keys:
     *    file_type => string the type of file to render. i.e css or javascript
     *    file_list => array each element is an absolute path of a css file
     *
     * @return string $script_tags_html html string containing the css or javascript tags
     */
    private function RenderCssJsFileTags($parameters) 
    {
        /** The path to the application template folder is fetched */
        $template_folder_path = $this->GetConfig("path", "framework_template_path");
        $file_list = $parameters['file_list'];
        $tag_arr = array();
        for ($count = 0;$count < count($file_list);$count++) 
        {
            $file_name = $file_list[$count];
            $tag_arr[$count]["url"] = $file_name;
        }
        /** If the file type is css then the css template file is rendered */
        if ($parameters['file_type'] == 'css') $script_tags_html = $this->RenderTemplate("css_tags.html", $tag_arr);
        /** If the file type is javascript then the javascript template file is rendered */
        else if ($parameters['file_type'] == 'javascript') $script_tags_html = $this->RenderTemplate("javascript_tags.html", $tag_arr);
        else throw new \Exception("Invalid file type given to RenderCssJsFileTags");
        
        return $script_tags_html;
    }
}

