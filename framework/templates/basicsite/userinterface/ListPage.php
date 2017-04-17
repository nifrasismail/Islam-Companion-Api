<?php
namespace Framework\Templates\BasicSite\UserInterface;
use Framework\Object\UiObject as UiObject;
use Framework\Object\MysqlDataObject as MysqlDataObject;
use Framework\Templates\BasicSite\UserInterface\HtmlTable as HtmlTable;
/**
 * This class extends the Page class
 *
 * It contains functions for displaying a list page
 *
 * @category   Framework
 * @package    UserInterface
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class ListPage extends Page
{
    /**
     * It generates template parameters and saves them to local object property
     *
     * Used to set the extra javascript and css files for the list page
     * It also sets the parameters for the list page
     *
     * @param array $data contains parameters for the list page table
     *    template_parameters => the parameters for the list page
     */
    public function Read($data = "") 
    {
        /** The template object is fetched */
        $template_obj = $this->GetComponent('template');
        /** The html table object is fetched */
        $html_table_obj = $this->GetComponent('htmltable');
        /** The callback for formatting the table cells */
        if (is_callable(array(
            $this,
            "GetTableCellAttributes"
        )) && isset($data['html_table'])) 
        {
            $data['html_table']['cell_attributes_callback'] = array(
                $this,
                "GetTableCellAttributes"
            );
        }
        /** If the html table parameters are given then data is read from database */
        $table_rows = (isset($data['html_table'])) ? $html_table_obj->Read($data['html_table']) : array();
        /** The custom css files */
        $custom_css = (isset($data['list_page']['custom_css'])) ? $data['list_page']['custom_css'] : array();
        /** The custom javascript files */
        $custom_javascript = (isset($data['list_page']['custom_javascript'])) ? $data['list_page']['custom_javascript'] : array();        
        /** If the input button is defined then the sr_no field for the new row is set to the total number of rows + 1 */
        if (is_array($data['list_page']['input_button']))
            $data['list_page']['input_button']['parameters']['sr_no'] = $this->GetLargestSno($table_rows);
        /** The table rows are filtered */
        $table_rows = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "FilterTableData", array(
            $table_rows
        ));
        /** The html table pagination */
        $data['html_table']['pagination'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetPaginationInformation", array(
            $table_rows
        ));
        /** The rows in the current page are fetched */
        $table_rows = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetRowsInCurrentPage", array(
            $table_rows
        ));
        /** The edit and delete links are added to each row */
        for ($count1 = 0;$count1 < count($table_rows);$count1++) 
        {            
            /** The row_id is set to the key field value */
            $table_rows[$count1]['row_id'] = (isset($table_rows[$count1][$data['html_table']['database_reader_data']['key_field']])) ? $table_rows[$count1][$data['html_table']['database_reader_data']['key_field']] : '-1';
            /** The sr_no is set */
            $sr_no = (isset($table_rows[$count1]['sr_no'])) ? $table_rows[$count1]['sr_no'] : $table_rows[$count1]['row_id'];
            /** The row data is filtered */
            $table_rows[$count1] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "FilterRowData", array(
                $table_rows[$count1],
                ($count1 + 1)
            ));
            /** The table link columns are added */
            for ($count2 = 0;is_array($data['list_page']['links']) && $count2 < count($data['list_page']['links']);$count2++) 
            {                
                /** The parameters for fetching the link */
                $link_details = $data['list_page']['links'][$count2];
                /** The row id is added to the link parameters */
                $link_details['row_id'] = $table_rows[$count1]['row_id'];
                /** If the database name is given then it is added to the link parameters */
                if (isset($data['database_name']) && !isset($link_details['database_name'])) $link_details['database_name'] = $data['database_name'];
                /** The data type is added to the link parameters */
                $link_details['data_type'] = (isset($link_details['data_type']) ) ? $link_details['data_type'] : $data['html_table']['database_reader_data']['data_type'];
                /** The table row */
                $link_details['table_row'] = $table_rows[$count1];
                /** The row_id field is set to the sr_no field if the button type is custom and sr_no is > 0 */
                $link_details['row_id'] = ($link_details['type'] == 'custom' && $sr_no > 0) ? $sr_no : $link_details['row_id'];
                /** The sr_no field is set */
                $link_details['sr_no'] = $sr_no;
                /** The link html is fetched */
                $table_rows[$count1][$link_details['button_title']] = $this->GetLink($link_details);
            }
            /** The row_id column is removed */
            unset($table_rows[$count1]['row_id']);
        }
        /** The array key is removed from all the rows */
        for ($count = 0;$count < count($table_rows);$count++) 
        {
            /** The updated row is added back to the table rows */
            $table_rows[$count] = array_values($table_rows[$count]);
        }
        /** The right column html is fetched */
        $right_column_html = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetListRightColumnHtml", array());
        /** The table rows are loaded to the html table */
        $html_table_obj->Load($table_rows);
        /** Data is displayed */
        $table_data = $html_table_obj->Display();
        /** If the input button data is given then input button is generated */
        if (is_array($data['list_page']['input_button'])) 
        {
            /** The redirect url is added to input button link parameters */
            $data['list_page']['input_button']['parameters'] = array_merge($data['list_page']['input_button']['parameters'], array(
                "redirect_url" => $this->GetConfig("path", "current_url")
            ));          
            /** The input button html */
            $input_button_html = $this->GetInputButton($data['list_page']['input_button']['title'], $data['list_page']['input_button']['onclick'], $data['list_page']['input_button']['parameters']);
            /** The list page separator */
            $separator = " | ";
        }
        else
        {
            $input_button_html = "";
            /** The list page separator */
            $separator = "";
        }
        /** The list page html */
        $list_page_html = $template_obj->Render("list_page", array(
            "list_page_title" => $data['list_page']['list_page_title'],
            "framework_template_url" => $this->GetConfig("path", "framework_template_url") ,
            "separator" => $separator,
            "current_page_text" => $data['html_table']['pagination']['current_page_text'],
            "page_list" => $data['html_table']['pagination']['page_list'],
            "input_button" => $input_button_html,
            "right_column" => $right_column_html,
            "table_data" => $table_data
        ));
        /** The template parameters for the base page */
        $page_parameters = array(
            "title" => $data['list_page']['title'],
            "custom_css" => $custom_css,
            "custom_javascript" => $custom_javascript,
            "header" => $data['header'],
            "body" => $list_page_html
        );
        /** The parent Read function is called */
        parent::Read($page_parameters);
    }
    /**
     * Used to get the link html
     *
     * It returns the link html string
     *
     * @param array $parameters the parameters used to generate the link
     *    type => string [edit~delete~custom] the type of link
     *    row_id => int the table row id
     *    sr_no => int the serial number of the row
     *    data_type => string the table data type
     *    table_row => array the table row
     *    parent_row_id => int the parent row id field
     *    option => string the url option for the link
     *    is_ajax => boolean indicates if the link is an ajax link
     *    is_popup => boolean indicates if the link should show as a popup
     *    image_name => string the link image name
     *    image_css_class => string the link image css class
     *    button_title => string the link button title
     *    extra_link_field => string the extra table row field to include with the link
     *    link_parameters => array the link parameters
     *
     * @return string $link_html the html string for the link
     */
    protected function GetLink($parameters) 
    {
        /** The parameters used to render the link */
        $template_parameters = array();
        /** If the link type is delete */
        if ($parameters['type'] == 'delete') 
        {
            $template_parameters['option'] = (isset($parameters['option'])) ? $parameters['option'] : "delete_data";
            $template_parameters['is_ajax'] = true;
            $template_parameters['link'] = "javascript:";
            $template_parameters['is_popup'] = false;
            $template_parameters['image_css_class'] = "delete-button-image";
            $template_parameters['link_id'] = "delete_link_" . $parameters['row_id'];
            $template_parameters['button_title'] = "Delete Item";
            $template_parameters['image_name'] = $this->GetConfig('path', 'framework_template_url') . "/images/btn_delete.gif";
        }
        /** If the link type is edit */
        else if ($parameters['type'] == 'edit') 
        {
            $template_parameters['option'] = (isset($parameters['option'])) ? $parameters['option'] : "form_page";
            $template_parameters['is_ajax'] = false;
            $template_parameters['is_popup'] = (isset($parameters['is_popup'])) ? $parameters['is_popup'] : false;
            $template_parameters['image_css_class'] = "edit-button-image";
            $template_parameters['link_id'] = "edit_link_" . $parameters['row_id'];
            $template_parameters['button_title'] = "Edit Item";
            $template_parameters['image_name'] = $this->GetConfig('path', 'framework_template_url') . "/images/btn_edit.gif";
        }
        /** If the link type is custom */
        else if ($parameters['type'] == 'custom') 
        {
            $template_parameters = $parameters;
            $template_parameters['link_id'] = urlencode($template_parameters['option']) . "_link_" . $parameters['data_type'] . "_" . $parameters['row_id'];
            $template_parameters['image_name'] = $this->GetConfig('path', 'application_folder_url') . "/images/" . $parameters['image_name'];
            /** If the onclick parameter is set */
            if (isset($template_parameters['onclick'])) 
            {
                /** Each field is added to the event handler */
                foreach ($parameters['table_row'] as $field_name => $field_value) 
                {
                    /** The html tags are removed from the field value and the field value is trimmed */
                    $field_value = trim(strip_tags($field_value));
                    /** The event handler function is updated */
                    $template_parameters['onclick'] = str_replace("{" . $field_name . "}", $field_value, $template_parameters['onclick']);
                }
            }
        }
        /** The url for the link */
        $url = $this->GetComponent("application")->RouteFunction($this->data['html_table']['database_reader_data']['data_type'], "GetLinkUrl", array(
            $parameters
        ));
        /** If the link should be ajax based */
        if ($template_parameters['is_ajax']) 
        {
            /** The link value is set */
            $template_parameters['link'] = (isset($template_parameters['link'])) ? $template_parameters['link'] : "return true";
            $template_parameters['link_css_class'] = "";
            $template_parameters['onclick'] = (isset($template_parameters['onclick'])) ? $template_parameters['onclick'] : $parameters['onclick'];
        }
        /** If the link should not be ajax based and should be a popup */
        else if ($template_parameters['is_popup']) 
        {
            $template_parameters['link'] = $url;
            $template_parameters['link_css_class'] = "popup_link";
            $template_parameters['onclick'] = "return true;";
        }
        /** If the link should not be ajax based and should not be a popup */
        else 
        {
            $template_parameters['link'] = $url;
            $template_parameters['link_css_class'] = "";
            $template_parameters['onclick'] = "";
        }
        /** The url is added to the onclick event handler */
        $template_parameters['onclick'] = str_replace("{url}", $url, $template_parameters['onclick']);
        /** The template parameters for rendering the movie input button */
        $template_parameters = array(
            "link" => $template_parameters['link'],
            "id" => $template_parameters['link_id'],
            "link_css_class" => $template_parameters['link_css_class'],
            "image_css_class" => $template_parameters['image_css_class'],
            "onclick" => $template_parameters['onclick'],
            "image_src" => $template_parameters['image_name'],
            "button_title" => $template_parameters['button_title']
        );
        /** The image link html is rendered */
        $link_html = $this->GetComponent("template")->Render("image_link", $template_parameters);
        return $link_html;
    }
    /**
     * Used to get the largest serial number
     *
     * It returns the largest serial number for the given data
     *
     * @param $rows the rows containing serial number
     *
     * @return int $largest_sno the largest serial number for the given data
     */
    private function GetLargestSno($rows) 
    {
        /** If the serial number field is not set then the function return '-1' */
        if (!isset($rows[0]['sr_no'])) return '-1';
        /** The largest serial number */
        $largest_sno = 0;
        /** Each row is checked */
        for ($count = 0; $count < count($rows); $count++) {
            /** If the sr_no is not set for the row then the loop continues */
            if (!isset($rows[$count]['sr_no'])) continue;
            /** The sr_no for the row */
            $sr_no = $rows[$count]['sr_no'];
            /** The largest serial number */
            $largest_sno = ($sr_no > $largest_sno) ? $sr_no : $largest_sno;
        }
        /** The largest serial number is increased by 1 */
        $largest_sno++;
        
        return $largest_sno;
    }
    /**
     * Used to get the input button
     *
     * Generates the input button
     *
     * @param string $button_title the button title
     * @param string $onclick_event the javascript function to call when the input button is clicked
     * @param string $option the url option for the input button
     * @param array $parameters the input button url option parameters
     *
     * @return string $input_button_html the html string for the input button
     */
    private function GetInputButton($button_title, $onclick_event, $parameters) 
    {
        /** The button id is auto generated from the button title */
        $button_id = strtolower(str_replace(" ", "_", $button_title));
        /** The add url parameters */
        $add_url_parameters = array(
            'option' => $parameters['option'],
            'module_name' => $this->GetConfig("general", "module") ,
            'output_format' => 'html',
            'parameters' => $parameters,
            'is_link' => true,
            'url' => '',
            'object_name' => 'application',
            'encode_parameters' => false,
            'transformed_url_request' => 'index.php?id={url_id}'
        );
        /** The input button url */
        $url = $this->GetComponent("application")->GenerateUrl($add_url_parameters);
        /** The template parameters for rendering the input button */
        $template_parameters = array(
            "link" => $url,
            "id" => $button_id,
            "link_css_class" => "popup_link",
            "image_css_class" => "input-button-image",
            "onclick" => $onclick_event,
            "image_src" => $this->GetConfig('path', 'framework_template_url') . "/images/btn_newEvent.gif",
            "button_title" => $button_title
        );
        /** The movie input button html is rendered */
        $template_file_path = $this->GetConfig("path", "framework_template_path") . DIRECTORY_SEPARATOR . "input_button.html";
        /** The input button template file is rendered */
        $input_button_html = $this->GetComponent("template_helper")->RenderTemplateFile($template_file_path, $template_parameters);
        return $input_button_html;
    }
    /**
     * Used to display the forms list page
     *
     * It displays the list of forms
     *
     * {@internal context browser}
     * {@internal note the function returns an array but the final output of the function is a string}
     *
     * @param array $parameters the application parameters
     *
     * @return mixed $data the parameters used to render the list page
     */
    public function HandleListPage($parameters) 
    {
        /** The short table name for the requested page */
        $this->data['data_type'] = (isset($parameters['data_type'])) ? $parameters['data_type'] : "listpage";
        /** If it is given then the database name is set */
        if (isset($parameters['database_name'])) $this->data['database_name'] = $parameters['database_name'];
        /** The list page main title */
        $this->data['list_page']['title'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetPageTitle", array(
            $parameters
        ));
        /** The title for the list page */
        $this->data['list_page']['list_page_title'] = $this->data['list_page']['title'];
        /** The custom css files */
        $this->data['list_page']['custom_css'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetCustomCssFiles", array(
            $parameters
        ));
        /** The custom javascript files */
        $this->data['list_page']['custom_javascript'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetCustomJsFiles", array(
            $parameters
        ));
        /** The header template information */
        $this->data['header'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetHeaderTemplateParameters", array(
            $parameters
        ));
        /** The input button parameters */
        $this->data['list_page']['input_button'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetInputButtonParameters", array(
            $parameters
        ));
        /** The parameters for list page links */
        $this->data['list_page']['links'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetListPageLinks", array(
            $parameters
        ));
        /** The html table presentation data */
        $this->data['html_table']['presentation_data'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetPresentationData", array(
            $parameters
        ));
        /** The database reader data */
        $this->data['html_table']['database_reader_data'] = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetDatabaseInformation", array(
            $parameters
        ));
        /** The page format is set */
        $this->data['html_table']['database_reader_data']['format'] = $parameters['format'];
        /** The sort by field is set */
        $this->data['html_table']['database_reader_data']['sort_by'] = (isset($parameters['sort_by'])) ? $parameters['sort_by'] : $this->data['html_table']['database_reader_data']['key_field'];
        /** The order by field is set */
        $this->data['html_table']['database_reader_data']['order_by'] = (isset($parameters['order_by'])) ? $parameters['order_by'] : 'ASC';

        return $this->data;
    }
    /**
     * Used to return table cell attributes
     *
     * It returns the table cell attributes for the given table row and column
     *
     * @param int $row the table row number
     * @param int $column the table column number
     * @param int $column_count the total number of columns
     *
     * @return array $cell_attributes an array with following keys:
     *    column_span => int the column span
     *    css_class => string the css class
     */
    public function GetTableCellAttributes($row, $column, $column_count) 
    {
        $cell_attributes = $this->GetComponent("application")->RouteFunction($this->data['data_type'], "GetTableCellAttributes", array(
            $row,
            $column,
            $column_count
        ));
        return $cell_attributes;
    }
}

