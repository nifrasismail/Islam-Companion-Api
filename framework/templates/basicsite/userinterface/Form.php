<?php
namespace Framework\Templates\BasicSite\UserInterface;
use Framework\Object\UiObject as UiObject;
use Framework\Object\MysqlDataObject as MysqlDataObject;
/**
 * This class extends the UiObject class
 *
 * It contains functions for displaying a list page
 *
 * @category   Framework
 * @package    Templates
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class Form extends Page
{
    /**
     * It generates template parameters and saves them to local object property
     *
     * It generates template parameters for the form and its sub items. e.g text box, textarea etc
     *
     * @param array $data contains parameters for the form page
     *    template_parameters => the parameters for the form page
     */
    public function Read($data = "") 
    {
        /** If the form format is table fields */
        if ($data['format'] == 'structured') 
        {
            $form_data = $this->GenerateFormFromTableFields($data['form_meta']);
        }
        /** If the form format is table rows */
        else 
        {
            $form_data = $this->GenerateFormFromTableRows($data['form_meta']);
        }
        /** The custom css files */
        $custom_css = (isset($data['custom_css'])) ? $data['custom_css'] : array();
        /** The custom javascript files */
        $custom_javascript = (isset($data['custom_js'])) ? $data['custom_js'] : array();
        /** The form page html */
        $form_page_html = $this->GetComponent('template')->Render("form", array(
            "form_id" => $form_data['form_id'],
            "form_title" => $data['form_meta']['title'],
            "form_action" => $form_data['form_action_url'],
            "form_fields" => $form_data['form_field_html'],
            "form_onsubmit" => $data['form_meta']['form_onsubmit'],
            "form_right_column" => $data['form_meta']['form_right_column'],
            "framework_template_url" => $this->GetConfig("path", "framework_template_url")
        ));
        /** The template parameters for the page */
        $page_parameters = array(
            "title" => $data['form_meta']['title'],
            "body" => $form_page_html,
            "custom_css" => $custom_css,
            "header" => $data['header'],
            "custom_javascript" => $custom_javascript
        );
        /** The parent Read function is called */
        parent::Read($page_parameters);
    }
    /**
     * It is used to generate the form data from table rows
     *
     * It reads the field information from database table rows
     * It generates form fields from the field information
     *
     * @param array $data contains form information
     *    form_data_id => int the id of the table row that contains the form data
     *    key_field => string the name of the table field used to fetch the data
     *    data_type => string the short name of the table that contains the data
     *    fields_to_hide => array the form fields that should be displayed as hidden input boxes
     *    mode => string [edit~add~readonly] the form mode
     *    title => string the form title
     *    form_onsubmit => string the form on submit event handler
     *    parameters => array the data used to construct the form action url
     *
     * @return array $form_data the form data
     *    form_id => int the form id
     *    form_action_url => string the form action url
     *    form_field_html => string the form field html
     */
    private function GenerateFormFromTableRows($data) 
    {
        /** The data id */
        $row_id = (isset($data['form_data_id'])) ? $data['form_data_id'] : "-1";
        /** The data needed to fetch the table row */
        $table_row_data = array(
            "all_rows" => false,
            "data_type" => $data['data_type'],
            "key_field" => $data['key_field'],
            "key_field_value" => $row_id
        );
        /** The form on submit event handler */
        $form_onsubmit = $data['form_onsubmit'];
        /** The form data for the input form */
        $row_data = ($data['mode'] == 'add' && isset($data['sr_no'])) ? array("sr_no" => $data['sr_no']) : array();
        /** The row data is fetched */
        $row_data = ($data['mode'] == 'add') ? $row_data : $this->GetComponent("application")->RouteFunction($data['data_type'], "GetRowData", array(
            $table_row_data
        ));
       
        /** The form fields are set */
        $form_field_html = $this->GenerateFieldsFromTableRows($data['data_type'], $data['fields_to_hide'], $row_data);
        /** The format parameter is set to unstructured */
        $data['format'] = "unstructured";
        /** The form action url is generated */
        $form_action_url = $this->GetFormAction($data['mode'], $data);
        /** The form id is set depending on the form mode */
        $form_id = ($data['mode'] == "edit") ? "edit_form" : "add_form";
        /** The form data */
        $form_data = array(
            "form_id" => $form_id,
            "form_action_url" => $form_action_url,
            "form_field_html" => $form_field_html,
            "form_onsubmit" => $form_onsubmit
        );
        return $form_data;
    }
    /**
     * It is used to generate the form data from table fields
     *
     * It reads the field information from database tables
     * It generates form fields from the field information
     *
     * @param array $data contains form information
     *    form_data_id => int the id of the table row that contains the form data
     *    key_field => string the name of the table field used to fetch the data
     *    data_type => string the short name of the table that contains the data
     *    fields_to_hide => array the form fields that should be ignored
     *    mode => string [edit~add~readonly] the form mode
     *    title => string the form title
     *    form_onsubmit => string the form on submit event handler
     *    parameters => array the data used to construct the form action url
     *
     *
     * @return array $form_data the form data
     *    form_id => int the form id
     *    form_action_url => string the form action url
     *    form_field_html => string the form field html
     */
    private function GenerateFormFromTableFields($data) 
    {
        /** The data id */
        $row_id = (isset($data['form_data_id'])) ? $data['form_data_id'] : "-1";
        /** The data needed to fetch the table row */
        $table_row_data = array(
            "all_rows" => false,
            "data_type" => $data['data_type'],
            "key_field" => $data['key_field'],
            "key_field_value" => $row_id
        );
        /** The row data is fetched */
        $row_data = ($data['mode'] == 'add') ? array() : $this->GetComponent("application")->RouteFunction($data['data_type'], "GetRowData", array(
            $table_row_data
        ));
        /** The form on submit event handler */
        $form_onsubmit = $data['form_onsubmit'];
        /** The form fields are set */
        $form_field_html = $this->GenerateFieldsFromTableFields($data['data_type'], $data['fields_to_hide'], $row_data);
        /** The format parameter is set to structured */
        $data['format'] = "structured";
        /** The form action url is generated */
        $form_action_url = $this->GetFormAction($data['mode'], $data);
        /** The form id is set depending on the form mode */
        $form_id = ($data['mode'] == "edit") ? "edit_form" : "add_form";
        /** The form data */
        $form_data = array(
            "form_id" => $form_id,
            "form_action_url" => $form_action_url,
            "form_field_html" => $form_field_html
        );
        return $form_data;
    }
    /**
     * It is used to generate the form action url
     *
     * It generates the form action url using the given parameters
     *
     * @param string $mode the form mode
     * @param array $parameters the form action url parameters
     *
     * @param string $form_url the form action url
     */
    private function GetFormAction($mode, $parameters) 
    {
        /** The form mode is set in the form action. The form url parameters are merged with the form url parameters given by the user */
        $parameters = array_merge(array(
            "mode" => $mode,            
        ) , $parameters);
        /** The current module name */
        $module_name = $this->GetConfig("general", "module");
        /** The form action url parameters */
        $form_action_url_parameters = array("option" => "save_data", "module_name" => $module_name, "output_format" => 'html', 
        "parameters" => $parameters, "is_link" => true, "url" => "", "object_name" => "form", "encode_parameters" => false, "transformed_url_request" => "");      
        /** The input button url */
        $form_url = $this->GetComponent("unstructureddataui")->GenerateUrl($form_action_url_parameters);
       
        return $form_url;
    }
    /**
     * It is used to generate form fields from database table row information
     *
     * It fetches field information from table rows
     * For each field given by a row it generates a html form field. e.g text field, textarea etc
     *
     * @param string $data_type the short table name. it is used to generate form fields
     * @param string $fields_to_hide the list of fields to hide
     * @param array $row_data the table row data
     *
     * @return string $form_fields_html the html for the form fields
     */
    private function GenerateFieldsFromTableRows($data_type, $fields_to_hide, $row_data) 
    {
        /** The field information */
        $field_information = array();
        /** The form id is fetched */
        $form_id = $this->GetComponent("databasereader")->GetFormId($data_type);
        /** The form fields are fetched */
        $field_data = $this->GetComponent("databasereader")->GetUnstructuredFieldNames($form_id);
        /** Data from each row is formatted */
        for ($count = 0;$count < count($field_data);$count++) 
        {
            /** A single field item */
            $field_item = $field_data[$count];
            /** If the field type is enum or selectbox, then the field meta is appended to the field type */
            if ($field_item['field_type'] == 'enum' || $field_item['field_type'] == 'selectbox') 
            {
                $field_item['field_type'] = $field_item['field_type'] . "(" . $field_item['field_meta'] . ")";
            }
            /** The updated field item */
            $updated_field_item = array(
                "Field" => $field_item['field_name'],
                "Type" => $field_item['field_type']
            );
            /** The updated field item is added to the field information */
            $field_information[] = $updated_field_item;
        }
        /** The id field is prepended to the data */
        $field_information = array_merge(array(
            array(
                "Field" => "id",
                "Type" => "number"
            )
        ) , $field_information);
        /** The id_list field is prepended to the data */
        $field_information = array_merge(array(
            array(
                "Field" => "id_list",
                "Type" => "hidden"
            )
        ) , $field_information);
        /** If the row data is empty. e.g the form is in add mode, then the row data is set to empty values */
        if (count($row_data) == 0) 
        {
            $row_data = array_fill(0, count($row_data) , "");
        }
        /** All form fields are rendered */
        $form_fields_html = $this->RenderAllFormFields($field_information, $fields_to_hide, $row_data);
        return $form_fields_html;
    }
    /**
     * It is used to generate form fields from database table field information
     *
     * It fetches field information from database tables
     * For each field in the table it generates a html form field. e.g text field, textarea etc
     *
     * @param string $data_type the short table name. it is used to generate form fields
     * @param string $fields_to_hide the list of fields to hide
     * @param array $row_data the table row data
     *
     * @return string $form_fields_html the html for the form fields
     */
    private function GenerateFieldsFromTableFields($data_type, $fields_to_hide, $row_data) 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The current application parameters */
        $parameters = $this->GetConfig("general", "parameters");       
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => "id",
            "data_type" => $data_type
        );
        /** Mysqldataobject is created */
        $data_object = new MysqlDataObject($meta_information);
        /** The field information */
        $field_information = $data_object->GetFieldInformation();
        /** All form fields are rendered */
        $form_fields_html = $this->RenderAllFormFields($field_information, $fields_to_hide, $row_data);
        return $form_fields_html;
    }
    /**
     * It is used to extract values from field definition
     * The extracted values are converted to an array suitable for use as selectbox options
     *
     * It extracts the values from the definition of enum field
     * The extracted values are converted to select option array
     *
     * @param string $field_name the field name
     * @param string $field_description the field description
     *
     * @return array $selectbox_options the selectbox options
     */
    private function GetSelectOptionsFromField($field_name, $field_description) 
    {
        /** The data type */
        $data_type = $this->GetConfig("general", "parameters", "data_type");
        /** The selectbox options */
        $selectbox_options = array();
        /** The field values are fetched from custom page */
        $field_values = $this->GetComponent("application")->RouteFunction($data_type, "GetSelectBoxValues", array(
            $field_name
        ));
        /** If the selectbox values were not found then the values are fetched from field description */
        if (!is_array($field_values)) 
        {
            /** If the field options are given as enum values */
            if (strpos($field_description, "enum") !== false) 
            {
                /** The field values. The '(' and ')' are removed from field description */
                $field_values = trim(str_replace("enum(", "", $field_description));
                $field_values = rtrim($field_values, ")");
                /** The list of field values */
                $field_values = explode(",", $field_values);
            }
            /** If the field values are given as textbox lines */
            else if (strpos($field_description, "selectbox") !== false) 
            {
                /** The field values. The '(' and ')' are removed from field description */
                $field_values = trim(str_replace("selectbox(", "", $field_description));
                $field_values = rtrim($field_values, ")");
                /** The list of field values */
                $field_values = explode("\n", $field_values);
            }
        }
        /** Each field value is added to the selectbox options */
        for ($count = 0;$count < count($field_values);$count++) 
        {          
            /** The field value. The "'" is removed from field value */
            $field_value = trim($field_values[$count], "'\n\r");
            /** The field value is added to selectbox_options */
            $selectbox_options[] = array(
                "text" => $field_value,
                "value" => $field_value
            );
        }
        return $selectbox_options;
    }
    /**
     * It is used to render a form field
     *
     * It generates html for a form field
     * From the given form field parameters
     *
     * @param string $template_name the name of the template to use
     * @param array $field_parameters the field parameters
     *
     * @return string $form_field_html the form field html
     */
    private function RenderFormField($template_name, $field_parameters) 
    {
        /** The field label class */
        $field_visibility_class = (isset($field_parameters['input_type']) && $field_parameters['input_type'] == "hidden") ? "hidden" : "visible";
        /** The template object is fetched */
        $template_obj = $this->GetComponent('template');
        /** The framework template url is fetched */
        $framework_template_url = $this->GetConfig("path", "framework_template_url");
        /** The form field html */
        $form_field_html = $template_obj->Render($template_name, $field_parameters);
        /** The table row parameters. The form field html is placed inside a table row */
        $field_row_parameters = array(
            "field_label" => $field_parameters['field_label'],
            "field_html" => $form_field_html,
            "field_visibility_class" => $field_visibility_class,
            "framework_template_url" => $framework_template_url
        );
        /** The form field html */
        $form_field_html = $template_obj->Render("form_field", $field_row_parameters);
        return $form_field_html;
    }
    /**
     * It is used to render all form fields
     *
     * It generates html for all form fields
     *
     * @param array $field_information the form field information
     * @param string $fields_to_hide the list of fields to hide
     *
     * @return string $form_field_html the form field html
     */
    private function RenderAllFormFields($field_information, $fields_to_hide, $row_data) 
    {
        /** The html for the form fields */
        $form_fields_html = "";
        /** Each form field is generated */
        for ($count = 0;$count < count($field_information);$count++) 
        {
            /** The field data */
            $field_data = $field_information[$count];
            /** The field value is set */
            $field_value = isset($row_data[$field_data['Field']]) ? str_replace('"', "&quot;", $row_data[$field_data['Field']]) : "";            
            /** The field name */
            $field_name = ucwords(str_replace("_", " ", $field_data['Field']));
            /** The field id is generated from field name */
            $field_id = preg_replace("/\(.+\)/i", "", $field_data['Field']);
            /** The field id is generated from field name */
            $field_id = trim(preg_replace("/\[.+\]/i", "", $field_id));
            /** The space is removed from field_id */
            $field_id = str_replace(" ", "", $field_id);
            /** The field parameters */
            $field_parameters = array();
            /** The template name */
            $template_name = "";
            /** If the field type is longtext or text and the field does not need to be hidden then it is rendered using textarea */
            if (($field_data['Type'] == "longtext" || $field_data['Type'] == "text") && !in_array($field_data['Field'], $fields_to_hide)) 
            {
                /** The template name */
                $template_name = "textarea";
                /** The field parameters are set */
                $field_parameters['textarea_name'] = "form[textarea_" . $field_data['Field'] . "]";
                $field_parameters['textarea_id'] = $field_id;
                $field_parameters['textarea_value'] = $field_value;
                $field_parameters['textarea_cols'] = "42";
                $field_parameters['textarea_rows'] = "4";
                $field_parameters['field_label'] = $field_name;
            }
            /** If the field type contains "enum" or "selectbox" and the field does not need to be hidden then it is rendered using dropdown */
            else if ((strpos($field_data['Type'], "enum") === 0 || strpos($field_data['Type'], "selectbox") === 0) && !in_array($field_data['Field'], $fields_to_hide)) 
            {
                /** The template name */
                $template_name = "selectbox";
                /** The field parameters are set */
                $field_parameters['selectbox_name'] = "form[selectbox_" . $field_data['Field'] . "]";
                $field_parameters['selectbox_id'] = $field_id;
                $field_parameters['selectbox_selected_value'] = $field_value;
                $field_parameters['selectbox_onchange'] = "";
                $field_parameters['selectbox_options'] = $this->GetSelectOptionsFromField($field_data['Field'], $field_data['Type']);
                $field_parameters['field_label'] = $field_name;
            }
            /** Otherwise the field is rendered using an input field */
            else 
            {
                $template_name = "input";
                if (strpos($field_data['Type'], "int") !== false || strpos($field_data['Type'], "number") !== false) $input_type = "number";
                else if ($field_data['Type'] == "url") $input_type = "url";
                else if ($field_data['Type'] == "date") $input_type = "date";
                else if ($field_data['Type'] == "datetime") $input_type = "datetime-local";
                else if ($field_data['Type'] == "time") $input_type = "time";
                else if ($field_data['Type'] == "password") $input_type = "password";
                else if ($field_data['Type'] == "email") $input_type = "email";
                else if ($field_data['Type'] == "hidden") $input_type = "hidden";
                else $input_type = "text";
                /** If the given field is in the list of fields to hide then it is hidden */
                if (in_array($field_data['Field'], $fields_to_hide)) $input_type = "hidden";
                /** The field parameters are set */
                $field_parameters['input_name'] = "form[input_" . $field_data['Field'] . "]";
                $field_parameters['input_id'] = $field_id;
                $field_parameters['input_value'] = $field_value;
                $field_parameters['input_css'] = ($input_type == "number") ? "" : "CSStextFIELDlarge";
                $field_parameters['input_type'] = $input_type;
                $field_parameters['field_label'] = $field_name;
            }
            /** The form field is rendered */
            $form_field_html = $this->RenderFormField($template_name, $field_parameters);
            /** The final form field html is rendered */
            $form_fields_html = $form_fields_html . $form_field_html;
        }
        return $form_fields_html;
    }
    /**
     * It is used to save form data
     *
     * It fetches the form data from application parameters
     * It saves the form data to database
     *
     * @param array $parameters the application parameters
     *
     * @return $is_saved boolean indicates if the data was successfully saved
     */
    private function SaveFormData($parameters) 
    {
        /** The data to be saved to database */
        $form_data = array();
        /** The submited form data is fetched */
        foreach ($parameters['form'] as $key => $value) 
        {
            /** Only textarea, selectbox and input fields are saved */
            if (strpos($key, "hidden") === false && strpos($key, "textarea") === false && strpos($key, "input") === false && strpos($key, "selectbox") === false) continue;
            /** The database field name is fetched */
            else 
            {
                $database_field_name = str_replace("textarea_", "", $key);
                $database_field_name = str_replace("input_", "", $database_field_name);
                $database_field_name = str_replace("selectbox_", "", $database_field_name);
                $database_field_name = str_replace("hidden_", "", $database_field_name);
                $form_data[$database_field_name] = $value;
            }
        }
        /** If the form is in edit mode, then the row id is added to the form data */
        if ($parameters['mode'] == 'edit') $form_data['id'] = $parameters['form_data_id'];
        /** If the form is in add mode, then the row id is removed from the form data */
        else if ($parameters['mode'] == 'add') unset($form_data['id']);
        /** The data to be saved is filtered */
        $form_data = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "FilterSaveData", array(
            $form_data,
            $parameters
        ));

        /** The data is saved */
        $is_saved = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "SaveFormData", array(
            $form_data,
            $parameters
        ));
        return $is_saved;
    }
    /**
     * Used to save data to database
     *
     * It saves the submited form data to database
     * {@internal context browser,command line}
     *
     * @param array $parameters the application parameters
     *
     * @return string $alert_confirmation_html the javascript code that displays confirmation box and redirects the user to the list page
     */
    public function HandleSaveData($parameters) 
    {
        /** The template object is fetched */
        $template_obj = $this->GetComponent('template');
        /** The form data is saved */
        $is_saved = $this->SaveFormData($parameters);
        /** The alert text is set depending on the value of the form mode */
        $alert_text = ($parameters['mode'] == 'edit') ? "Data was successfully updated" : "Data was successfully added";
        /** If the form data could not be saved, then the alert message is updated depending on the value of $is_saved */
        if ($is_saved < 0) $alert_text = "Data already exists";
        else if ($is_saved == 0) $alert_text = "Data could not be saved to database";
        /** The alert confirmation html */
        $alert_confirmation_html = $template_obj->Render("alert_confirmation", array(
            "alert_text" => $alert_text,
            "optional_javascript" => "parent.location.href='" . addslashes($parameters['redirect_url']) . "'"
        ));
        return $alert_confirmation_html;
    }
    /**
     * Used to delete data from database
     *
     * It deletes the given data
     * {@internal context browser,command line}
     *
     * @param array $parameters the application parameters
     *
     * @return string $redirect_url the url at which the user is redirected
     */
    public function HandleDeleteData($parameters) 
    {
        /** The data is deleted */
        $redirect_url = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "DeleteData", array(
            $parameters
        ));
        return $redirect_url;
    }
    /**
     * Used to display the new item form
     *
     * It displays the add new item form
     *
     * {@internal context browser}
     * {@internal note the function returns an array but the final output of the function is a string}
     *
     * @param array $parameters the application parameters
     *
     * @return string $page_html the page html is returned
     */
    public function HandleFormPage($parameters) 
    {
        /** The database table name for the form */
        $this->data['form_meta']['data_type'] = $parameters['data_type'];
        /** The sr_no for the form if it is set in the url */
        $this->data['form_meta']['sr_no'] = (isset($parameters['sr_no'])) ? $parameters['sr_no'] : '-1';
        /** The format for the form fields */
        $this->data['format'] = $parameters['format'];
        /** If it is given then the database name is set */
        if (isset($parameters['database_name'])) $this->data['form_meta']['database_name'] =  $parameters['database_name'];
        /** If the form is in edit mode */
        if ($parameters['mode'] == 'edit') 
        {
            /** The form title is set */
            $this->data['form_meta']['title'] = "Edit " . rtrim(ucwords(str_replace("_", " ", $parameters['data_type'])) , "s");
        }
        /** If the form is in add mode */
        else if ($parameters['mode'] == 'add') 
        {
            /** The form title is set */
            $this->data['form_meta']['title'] = "Add " . rtrim(ucwords(str_replace("_", " ", $parameters['data_type'])) , "s");
            /** If the application parameters contains the parameter name */
            if (isset($parameters['extra_link_field'])) 
            {
                $this->data['form_meta']['title'] = $this->data['form_meta']['title'] . " to: " . ucwords(str_replace("_", " ", $parameters['extra_link_field'])) . " Form";
            }
        }
        /** If the form is in custom mode */
        else if ($parameters['mode'] == 'custom' && isset($parameters['extra_link_field']) && isset($parameters['form_title'])) 
        {
            /** The form title is set */
            $this->data['form_meta']['title'] = str_replace("{name}", $parameters['extra_link_field'], $parameters['form_title']);
        }
        /** The value of the id field for the data */
        $this->data['form_meta']['form_data_id'] = $parameters['id'];
        /** The form fields to hide */
        $this->data['form_meta']['fields_to_hide'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetHiddenFormFields", array(
            $parameters
        ));
        /** The key field used to fetch data from database */
        $this->data['form_meta']['key_field'] = "id";
        /** The mode for the form. i.e add or edit */
        $this->data['form_meta']['mode'] = $parameters['mode'];
        /** The parameters for the form action url */
        $this->data['form_meta']["redirect_url"] = $parameters['redirect_url'];
        /** The parent page id */
        $this->data['form_meta']['parent_row_id'] = (isset($parameters['parent_row_id'])) ? $parameters['parent_row_id'] : '-1';
        /** The extra link field */
        $this->data['form_meta']['extra_link_field'] = (isset($parameters['extra_link_field'])) ? $parameters['extra_link_field'] : '';

        /** The header template information. It is fetched if the user did not choose to hide the header */
        if (!isset($parameters['hide_header'])) $this->data['header'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetHeaderTemplateParameters", array(
            $parameters
        ));
        else $this->data['header'] = false;
        
        /** The form right column html */
        $this->data['form_meta']['form_right_column'] = "";
        /** The custom css files */
        $this->data['custom_css'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetCustomCssFiles", array(
            $parameters
        ));
        /** The custom js files */
        $this->data['custom_js'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetCustomJsFiles", array(
            $parameters
        ));
        /** The form onsubmit event handler */
        $this->data['form_meta']['form_onsubmit'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetFormOnsubmit", array(
            $parameters
        ));
        return $this->data;
    }
}

