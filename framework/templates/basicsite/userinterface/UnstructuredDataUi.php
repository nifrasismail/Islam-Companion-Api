<?php

namespace Framework\Templates\BasicSite\UserInterface;
use \Framework\Configuration\Base as Base;
use \Framework\Templates\BasicSite\UserInterface\ListPageInterface as ListPageInterface;
use \Framework\Object\MysqlDataObject as MysqlDataObject;
use Framework\Utilities\DatabaseFunctions;

/**
 * This class implements the UnstructuredDataUi class
 * It extends the Base class
 *
 * It provides functions for building form and list pages
 * The pages are built from unstructured data stored in mysql database
 *
 * @category   Framework
 * @package    UserInterface
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class UnstructuredDataUi extends UiData
{
    /**
     * Used to return the parameters for rendering the input button
     *
     * It returns the parameters used to render the input button
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $input_button_parameters the parameters used to render the input button
     *    title => string input button title
     *    parameters => array the parameters for the input button url
     *    onclick => string the onclick button event
     */
    public function GetInputButtonParameters($parameters) 
    {
        /** The format field is added to input button parameters */
        $parameters['format'] = "unstructured";
        /** The input button parameters are fetched */
        $input_button_parameters = parent::GetInputButtonParameters($parameters);
        return $input_button_parameters;
    }
    /**
     * Used to return the presentation data for the list page
     *
     * It returns the presentation data for the list page
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $presentation_data the presentation data for the list page
     *    header_widths => array the width of each list page header
     *    table_headers => array the list page table headers
     *    column_css_class => array the css class for each table column
     */
    public function GetPresentationData($parameters) 
    {
        /** The application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The parent row id */
        $parent_row_id = (isset($parameters['parent_row_id'])) ? $parameters['parent_row_id'] : '-1';
        /** The form id is fetched */
        $form_id = $this->GetComponent("databasereader")->GetFormId($parameters['data_type']);
        /** The list of field names is fetched from databasereader object */
        $field_names = $this->GetComponent("databasereader")->GetUnstructuredFieldNames($form_id);
        /** The presentation data is fetched */
        $presentation_data = parent::GetPresentationData($field_names);
        /** The id column is added at the begining of the presentation data */
        $presentation_data = $this->InsertColumn($presentation_data, "5%", "Id", "left-align", 0);
        /** Each table header name is checked */      
        for ($count = 0;$count < count($presentation_data['table_headers']);$count++) 
        {
            if ($presentation_data['table_headers'][$count] == "Parent Row Id" || $presentation_data['table_headers'][$count] == "Sr No") 
            {
                /** The parent id and sr no columns are deleted from the presentation data */
                $presentation_data = $this->DeleteColumns($presentation_data, array(
                    $count
                ));
                /** The counter is reset to 0 */
                $count = 0;
            }
        }
        return $presentation_data;
    }
    /**
     * Used to return the database data for the list page
     *
     * It returns the database data for the list page
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $database_data the database data for the list page
     *    key_field => string the key field for the database
     *    data_type => string the short table name for the data
     *    form_id => int the form id
     *    format => string [structured~unstructured] the format of the data. structured implies usual mysql table data
     *              unstructured implies the field names are stored in table rows
     */
    public function GetDatabaseInformation($parameters) 
    {
        /** The form id */
        $form_id = $this->GetComponent("databasereader")->GetFormId($parameters['data_type']);
        /** The current database object is fetched */
        $database_object = $this->GetComponent("structureddataui")->GetCurrentDatabaseObject($parameters);
        /** The database reader data */
        $database_data = array(
            "form_id" => $form_id,
            "format" => "unstructured",
            "key_field" => "id",
            "data_type" => "fields",
            "database_object" => $database_object
        );
        /** The key field is set in application configuration, so it can be used in other functions */
        $parameters["key_field"] = $database_data['key_field'];
        /** The application parameters are updated */
        $this->SetConfig("general", "parameters", $parameters);
        return $database_data;
    }
    /**
     * Used to get the table row data
     *
     * It fetches the table row data for the given row id
     *
     * @param array $data the data needed for fetching the table rows
     *    all_rows => boolean used to indicate if all table rows should be fetched
     *    key_field => string name of the field used to fetch the table data
     *    data_type => string the short name of the table that contains the table data
     *    key_field_value => string the value of the key field
     *
     *  @return array $row_data the required row data
     */
    public function GetRowData($data) 
    {
        /** The row data */
        $row_data = array();
        /** The form id is fetched */
        $form_id = $this->GetComponent("databasereader")->GetFormId($data['data_type']);
        /** The field information is fetched */
        $field_information = $this->GetComponent("databasereader")->GetUnstructuredFieldNames($form_id);
        /** The list of ids of the rows */
        $id_list = array();
        /** The field values are formatted so they can be saved */
        for ($count = 0;$count < count($field_information);$count++) 
        {
            /** The field id */
            $field_id = $field_information[$count]['id'];
            /** The field name */
            $field_name = $field_information[$count]['field_name'];
            /** The field values for the field are fetched */
            $field_values = $this->GetComponent("databasereader")->GetUnstructuredFieldValues($field_id);
            /** If the no data was stored for the field */
            if (!$field_values) continue;
            /** If all the table data is required */
            if ($data['all_rows']) 
            {
                /** All row ids are added */
                for ($count1 = 0;$count1 < count($field_values);$count1++) 
                {
                    /** The id list is updated */
                    $id_list[] = $field_values[$count1]['id'];
                }
            }
            else
            {
                /** The required field value */
                $row_data[$field_name] = (isset($field_values[$data['key_field_value'] - 1])) ? $field_values[$data['key_field_value'] - 1]['field_value'] : '';
                /** If the field has a value then the field value row id is added to id list */
                if (isset($field_values[$data['key_field_value'] - 1])) 
                {
                    /** The id list is updated */
                    $id_list[] = (isset($field_values[$data['key_field_value'] - 1])) ? $field_values[$data['key_field_value'] - 1]['id'] : '';
                }
            }
        }
     
        /** The serial number is added to the row data */
        $row_data['id'] = $data['key_field_value'];
        /** The row ids are added to the row data */
        $row_data['id_list'] = implode(",", $id_list);
        return $row_data;
    }
    /**
     * Used to update the form data before it is saved to database
     * It updates the form data before it is saved to database
     * It adds the form_id field to the data to be saved
     * The fields are only added if the form is in add mode
     *
     * @param array $data the row data to be filtered
     * @param array $parameters the form page parameters
     *
     * @return array $updated_data the updated form data
     */
    public function FilterSaveData($data, $parameters) 
    {
        /** If the form is in edit mode */
        if ($parameters['mode'] == 'add' && isset($parameters['parent_row_id'])) 
        {
            /** The parent row id of the data is set */
            $data['parent_row_id'] = $parameters['parent_row_id'];
        }
        /** If the form is in edit mode and the id_list parameters is set, then the id list is fetched */
        if ($parameters['mode'] == 'edit' && isset($data['id_list'])) 
        {
            $id_list = explode(",", $data['id_list']);
        }
        /** If the form is in edit mode and the id parameter is set */
        else if (isset($data['id'])) 
        {
            $id_list = array(
                $data['id']
            );
        }
        /** The form id is fetched */
        $form_id = $this->GetComponent("databasereader")->GetFormId($parameters['data_type']);
        /** The field information is fetched */
        $field_information = $this->GetComponent("databasereader")->GetUnstructuredFieldNames($form_id);
        /** The field values are formatted so they can be saved */
        for ($count = 0;$count < count($field_information);$count++) 
        {
            /** The field id */
            $field_id = $field_information[$count]['id'];
            /** The field name */
            $field_name = $field_information[$count]['field_name'];
            /** The field value */
            $field_value = $data[$field_name];
            /** The form data is updated */
            $updated_data[$count] = array(
                "field_id" => $field_id,
                "field_value" => $field_value,
                "updated_on" => time()
            );
            /** If the form is in edit mode and the field value id exists then the row id is added to the updated data */
            if ($parameters['mode'] == 'edit' && isset($id_list[$count])) 
            {
                /** The row id is added to the updated data */
                $updated_data[$count]['id'] = $id_list[$count];
            }
        }
        return $updated_data;
    }
    /**
     * It is used to save form data
     *
     * It fetches the form data from application parameters
     * It saves the form data to database
     *
     * @param array $form_data the data submitted by the form
     * @param array $parameters the application parameters
     *
     * @return $is_saved int indicates if the data was successfully saved
     */
    public function SaveFormData($form_data, $parameters) 
    {
        /** The current application parameters are fetched */
        $application_parameters = $this->GetConfig("general", "parameters");
        /** The current database object is fetched */
        $database_object = $this->GetComponent("structureddataui")->GetCurrentDatabaseObject($application_parameters);
        /** Each form data item is saved to database */
        for ($count = 0; $count < count($form_data); $count++) 
        {
            /** Table row data */
            $table_row = $form_data[$count];
            /** The application configuration is fetched */
            $configuration = $this->GetConfigurationObject();
            /** The parameters for the data object */
            $meta_information = array(
                "configuration" => $configuration,
                "key_field" => "id",
                "data_type" => "field_values",
                "database_object" => $database_object
            );
            /** Mysqldataobject is created */
            $data_object = new MysqlDataObject($meta_information);
            /** The data is set to the mysqldataobject */
            $data_object->SetData($table_row);
            /** If the form is in add mode, then the database is checked for existing record */
            if ($parameters['mode'] == 'add') 
            {
                /** If the record already exists in database, then the function returns -1 */
               /** if ($data_object->RecordExists()) 
                {
                    $is_saved = false;
                    return $is_saved;
                }*/
            }
            /** The Mysqldataobject is set to read/write */
            $data_object->SetReadonly(false);
            /** The form data is saved to database */
            $is_saved = ($data_object->Save()) ? true : false;
            /** If the data was not saved then the function returns */
            if (!$is_saved) break;
        }
        return $is_saved;
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
    public function DeleteData($parameters) 
    {
        /** The current application parameters are fetched */
       // $parameters = $this->GetConfig("general", "parameters");
        /** The current database object is fetched */
        $database_object = $this->GetComponent("structureddataui")->GetCurrentDatabaseObject($parameters);
        /** The data used to fetch the row data */
        $data = array(
            "all_rows" => false,
            "key_field" => "id",
            "data_type" => $parameters['data_type'],
            "key_field_value" => $parameters['id']
        );
        /** If delete_all is set to true */
        if (isset($parameters['delete_all']) && $parameters['delete_all']) $data['all_rows'] = true;
        /** The row data along with id list is fetched */
        $row_data = $this->GetRowData($data);
        /** If the id list is not empty */
        if ($row_data['id_list'] != "") {
        /** The list of row ids to delete */
        $id_list = explode(",", $row_data['id_list']);
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The parameters for the data object */
        $meta_information = array(
            "configuration" => $configuration,
            "key_field" => "id",
            "data_type" => "field_values",
            "readonly" => false,
            "database_object" => $database_object
        );
        /** Mysqldataobject is created */
        $data_object = new MysqlDataObject($meta_information);       
        /** Each field value row is deleted */
        for ($count = 0;$count < count($id_list);$count++) 
        {            
            /** The parameters used to read the data from database */
            $db_parameters = array(
                "fields" => "*",
                "condition" => $id_list[$count],
                "read_all" => false
            );
            /** The table row data is read from database */
            $data_object->Read($db_parameters);
            /** The table row data is deleted */
            $data_object->Delete();
        }
}
        /** The redirect url */
        $redirect_url = $parameters['redirect_url'];
        return $redirect_url;
    }
    /**
     * Used to filter table rows
     *
     * It removes rows from the table that do not match some criteria
     *
     * @param array $table_rows the table data
     *
     * @return array $updated_table_rows the table data
     */
    public function FilterTableData($table_rows) 
    {
        /** The table row data is updated */
        $table_rows = array(
            "table_rows" => $table_rows,
            "parent_field_name" => "parent_row_id"
        );
        /** The parent function is called */
        $updated_table_rows = parent::FilterTableData($table_rows);
        return $updated_table_rows;
    }
    /**
     * Used to update the table row data before it is displayed
     * By default the function replaces the table row id value with row number
     * It also removes the parent_row_id, updated_on and sr_no columns
     * It should be overriden by a child class
     *
     * @param array $data the row data to be filtered
     * @param int $row_number the row number
     *
     * @return array $data the updated row data
     */
    public function FilterRowData($data, $row_number) 
    {
        /** The parent function is called */
        $data = parent::FilterRowData($data, $row_number);
        /** The parent_row_id field is removed */
        unset($data['parent_row_id']);
        /** The updated_on field is removed */
        unset($data['updated_on']);
        /** The sr_no field is removed */
        unset($data['sr_no']);
        
        return $data;
    }
    /**
     * Used to get rows for given form
     *
     * It reads rows for given form
     * It returns the value of given field for each row
     * If the field name is equal to * then the entire row is returned
     *
     * @param string $form_name the name of the form
     * @param string $field_name the field name. If * is used then all field names are returned
     * @param array $condition the condition used to fetch the form data from database
     * @param array $sort the sort order of the data
     *
     * @return array $data an array containing the data in 2 formats. one is structured table format, the other is raw database format
     *    raw_data => array the raw data
     *    structured_data array the data formatted as structured table
     */
    public function GetFormData($form_name, $field_name, $condition, $sort = false) 
    {
        /** The form id is fetched */
        $meta_information['form_id'] = $this->GetComponent("databasereader")->GetFormId($form_name);
        /** The condition is added to the meta information */
        $meta_information['condition'] = $condition;       
        /** If the sort order is given then it is set */
        if (is_array($sort)) {
            $meta_information['sort_by'] = $sort['sort_by'];
            $meta_information['order_by'] = $sort['order_by'];
        }
        /** The table data for the scripts form is fetched */
        $data = $this->GetComponent("databasereader")->ReadUnStructuredData($meta_information);
        /** The table rows */
        $table_rows = $data['structured_data'];
        /** The form rows */
        $rows = array();
        /** The data for each table row is fetched */
        for ($count = 0;$count < count($table_rows);$count++) 
        {
            /** The table row data is fetched */
            $row_data = $table_rows[$count];
            /** The form data is updated */
            $rows[] = ($field_name != "*") ? $row_data[$field_name] : $row_data;
        }
        
        $data = array("raw_data" => $data['raw_data'], "structured_data" => $rows);
        
        return $data;
    }
    /**
     * Used to return the html for the page body
     *
     * It returns the html for the page
     * @param array $parameters the page parameters
     *
     * @return string $page_html the html for the page
     */
    public function GetPageBody($parameters) 
    {
        $page_html = "";
        return $page_html;
    }
    /**
     * Used to return the values for displaying customer dropdown
     *
     * It returns the list of customers
     * The list is displayed on different admin pages
     *
     * @return array $selectbox_values the selectbox values
     */
    public function GetSelectBoxValues($field_name) 
    {
        /** The selectbox values */
        $selectbox_values = false;
        /** If the selectbox name is customer */
        if ($field_name == "customer") 
        {
            $data = $this->GetFormData("customers", "name", false);
            /** The selectbox values are set to the structured data */
            $selectbox_values = $data['structured_data'];
        }
        /** If the selectbox name is form */
        else if ($field_name == "form") 
        {
            /** The condition used to fetch the form data from database */
            $condition = array(
                array(
                    "field" => "category",
                    "value" => "tools",
                    "operator" => "",
                    "operand" => "="
                )
            );
            /** The current application parameters are fetched */
            $parameters = $this->GetConfig("general", "parameters");
            /** The current database object is fetched */
            $database_object = $this->GetComponent("structureddataui")->GetCurrentDatabaseObject($parameters);
            /** The data used to fetch the form items */
            $meta_information = array(
                "key_field" => "id",
                "data_type" => "forms",
                "database_object" => $database_object,
                "fields" => "*",
                "condition" => $condition
            );
            /** The list of forms for the tools page are fetched */
            $form_items = $this->GetComponent("databasereader")->ReadStructuredData($meta_information);
            /** Header menu items */
            $selectbox_values = array();
            /** Each form item name is fetched and added to selectbox */
            for ($count = 0;$count < count($form_items);$count++) 
            {
                /** The parameters used to render the header item html */
                $selectbox_values[] = $form_items[$count]['name'];
            }
        }
        /** If the selectbox name is project */
        else if ($field_name == "project") 
        {
            $data = $this->GetFormData("projects", "name", false);
            /** The selectbox values are set to the structured data */
            $selectbox_values = $data['structured_data'];
        }
        /** If the selectbox name is host */
        else if ($field_name == "host") 
        {
            $data = $this->GetFormData("hosts", "name", false);
            /** The selectbox values are set to the structured data */
            $selectbox_values = $data['structured_data'];            
        }
        /** If the selectbox name contains database */
        else if (strpos($field_name, "database") !== false)
        {
            $data = $this->GetFormData("databases", "database", false);
            /** The selectbox values are set to the structured data */
            $selectbox_values = $data['structured_data'];
        }        
        return $selectbox_values;
    }
}

