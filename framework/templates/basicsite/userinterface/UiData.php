<?php
namespace Framework\Templates\BasicSite\UserInterface;

use \Framework\Configuration\Base as Base;
use \Framework\Application\UiApplication as UiApplication;
use \Framework\Templates\BasicSite\UserInterface\ListPageInterface as ListPageInterface;
use \Framework\Object\MysqlDataObject as MysqlDataObject;

/**
 * This class implements the UiData class
 * It extends the Base class
 *
 * It provides functions for building form and list pages
 * The pages are built from data stored in mysql database
 * The class is abstract and should be extended by a child class such as StructuredDataUi or UnstructuredDataUi
 *
 * @category   Framework
 * @package    UserInterface
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
abstract class UiData extends UiApplication
{
    /**
     * Used to return the list of header template parameters
     *
     * It returns the header template parameters
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $header_template_parameters the parameters used to render the header for the list page
     *    template_name => string the header template name
     *    template_parameters => array the header template parameters
     */
    public function GetHeaderTemplateParameters($parameters) 
    {
        /** The current database object is fetched */
        $database_object = $this->GetComponent("database");
        /** The condition used to fetch the form data from database */
        $condition = false;
        /** The data used to fetch the form items */
        $meta_information = array(
            "key_field" => "id",
            "data_type" => "forms",
            "fields" => "*",
            "condition" => $condition,
            "database_object" => $database_object
        );
        /** The list of header menu items is fetched */
        $form_items = $this->GetComponent("databasereader")->ReadStructuredData($meta_information);
        /** Header menu items */
        $header_menu_items = array();
        /** The current data type */
        $data_type = $parameters['data_type'];
        /** The parent data type. It is used to generate the up one level link */
        $parent_data_type = "";
        /** Each header menu item name is saved */
        for ($count = 0;$count < count($form_items);$count++) 
        {
            /** If the data type matches the form item name */
            if ($form_items[$count]['name'] == $data_type && $form_items[$count]['category'] != "none") $parent_data_type = $form_items[$count]['category'];            
            /** The parameters used to get the header link url */
            $parameters = array(
                "format" => "unstructured",
                "type" => "custom",
                "data_type" => $form_items[$count]['name'],
                "option" => "list_page"
            );
            /** If the data type is equal to settings then it is set to forms */
            if ($parameters['data_type'] == "settings") {
                $parameters['data_type'] = "forms";
                $parameters['format'] = "structured";
            }
            /** If the menu item needs to be added to the site menu */
            if ($form_items[$count]['add_to_site_menu'] == 'yes') 
            {
                /** The header item url */
                $header_item_url = $this->GetLinkUrl($parameters);
                /** The parameters used to render the header item html */
                $header_menu_items[] = array(
                    "header_item_name" => ucwords(str_replace("_", " ", $form_items[$count]['name'])) ,
                    "header_item_url" => $header_item_url
                );
            }
        }
        /** The header menu items */
        $header_menu_items_html = $this->GetComponent("template")->Render("header_item", $header_menu_items);
        /** The current module name */
        $module_name = $this->GetConfig("general", "module");
        /** The application name is fetched */
        $application_name = $this->GetConfig("general", "application_display_name");
        /** The parameters of the logout url */
        $logout_url_parameters = array(
            'option' => 'logout',
            'module_name' => $module_name,
            'output_format' => 'html',
            'parameters' => array(
                "context" => "browser"
            ) ,
            'is_link' => false,
            'url' => '',
            'object_name' => 'application',
            'encode_parameters' => false,
            'transformed_url_request' => 'index.php?id={url_id}'
        );
        /** The url of the logout page */
        $logout_url = $this->GenerateUrl($logout_url_parameters);
        /** The parameters of the form list page url */
        $form_url_parameters = array(
            'option' => 'list_page',
            'module_name' => "Admin",
            'output_format' => 'html',
            'parameters' => array(
                "data_type" => "forms",
                "object_name" => "listpage",
                "format" => "structured"
            ) ,
            'is_link' => false,
            'url' => '',
            'object_name' => 'application',
            'encode_parameters' => false,
            'transformed_url_request' => 'index.php?id={url_id}'
        );
        /** The url of the logout page */
        $form_page_url = $this->GenerateUrl($form_url_parameters);
        /** The list of visited pages */
        $visited_pages = $this->GetSessionConfig("visited_pages");
        /** The current application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current page type is fetched */
        $page_type = (isset($parameters['data_type'])) ? $parameters['data_type'] : '';
        /** If the data type is not set then the function returns */
        if ($page_type == "" || (isset($parameters['option']) && ($parameters['option'] == 'save_data' || $parameters['option'] == 'delete_data'))) return;
        /** The current url */
        $current_url = $this->GetConfig("path", "current_url");
        /** The page link parameters */
        $link_parameters = array(
            "link" => $current_url,
            "id" => "navigation_link_" . rand(0, 1000) ,
            "target" => "_self",
            "text" => str_replace("_", " ", $page_type)
        );
        /** The page link */
        $page_link = $this->GetComponent("template")->Render("link", $link_parameters);
        /** If the visited_pages has not been set */
        if (!is_array($visited_pages)) 
        {
            $visited_pages = array(
                $page_link
            );
        }
        else if ($visited_pages[count($visited_pages) - 1] != $page_link) 
        {
            $visited_pages[] = $page_link;
        }
        /** The bread crumbs string */
        $bread_crumbs = array();
        /** The up one level link */
        $up_one_level_link = "";
        /** If the visited pages has been recorded */
        if (is_array($visited_pages)) 
        {
            /** The visited pages array is reversed */
            $visited_pages = array_reverse($visited_pages);
        }
        /** The number of visited pages */
        $visited_pages_count = (count($visited_pages) - 1);
        /** The list of visited pages is set */
        $this->SetSessionConfig("visited_pages", $visited_pages, false);
        /** The list of visited pages. The last 5 pages are displayed */
        for ($count = 0;$count < count($visited_pages);$count++) 
        {
            /** The bread crumb link */
            $page_data = $visited_pages[$count];
            if ($count < 6) $bread_crumbs[] = $page_data;
            /** The bread crumb link is parsed */
            preg_match("/<a href='(.+)' id='.+' target='_self'>(.+)<\/a>/iU", $page_data, $matches);
            /** If the link data was not found then function continues */
            if (!isset($matches[2])) continue;
            /** The data type for the link */
            $data_type = str_replace(" ", "_", strip_tags($matches[2]));
            /** If the data type matches the parent data type */
            if ($data_type == $parent_data_type) 
            {
                $up_one_level_link = $matches[1];
            }
            /** The first link */
            else if ($count == 1 && $up_one_level_link == "") 
            {
                $up_one_level_link = $matches[1];
            }
        }
        /** The site url */
        $site_url = $this->GetConfig("general", "company_url");
        /** The page link parameters */
        $link_parameters = array(
            "link" => $site_url,
            "id" => "navigation_link_" . rand(0, 1000) ,
            "target" => "_self",
            "text" => $application_name
        );
        /** The page link */
        $application_name_link = $this->GetComponent("template")->Render("link", $link_parameters);
        /** The array entries are reversed */
        $bread_crumbs = array_reverse($bread_crumbs);
        /** The bread crumbs are formatted */
        $bread_crumbs = implode("&raquo;", $bread_crumbs);
        /** The header template parameters */
        $template_parameters = array(
            "company_name" => $application_name_link,
            "logout_url" => $logout_url,
            "greeting_text" => "<b> " . $this->GetConfig("custom", "greeting_text") . " " . $this->GetSessionConfig("full_name") . "!</b>",
            "up_one_level_link" => $up_one_level_link,
            "bread_crumbs" => $bread_crumbs,
            "form_page_url" => $form_page_url,
            "header_menu_items" => $header_menu_items_html
        );
        /** The header template information */
        $template_parameters = array(
            "template_name" => "header",
            "template_parameters" => $template_parameters
        );
        return $template_parameters;
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
        /** The cell attributes */
        $cell_attributes = array();
        /** The index of the first column to be centered is fetched. The index is taken starting from the right */
        $center_column_index = $this->GetConfig("general", "center_column_index");
        /** The column span value */
        $colspan = 1;
        /** If the current column is before the last two columns */
        if ($column < ($column_count - $center_column_index)) 
        {
            /** The table column css */
            $column_class = "left-align";
        }
        else
        {
            /** The table column css */
            $column_class = "center-align";
        }
        /** The column span is calculated */
        $colspan = 1;
        $cell_attributes['column_span'] = $colspan;
        $cell_attributes['css_class'] = $column_class;
        return $cell_attributes;
    }
    /**
     * Used to return the list page title
     *
     * It returns the list page title
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return string $list_page_title the list page title
     */
    public function GetPageTitle($parameters) 
    {
        /** The list page title */
        $list_page_title = "Manage " . ucwords(str_replace("_", " ", $parameters['data_type']));
        /** If the name option is set then it is added to the list page title */
        if (isset($parameters['extra_link_field'])) $list_page_title = $list_page_title . " for: " . ucwords(str_replace("_", " ", $parameters['extra_link_field']));
        return $list_page_title;
    }
    /**
     * Used to return the list of custom css files
     *
     * It returns the custom css files
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $custom_css_files the list of custom css files
     */
    public function GetCustomCssFiles($parameters) 
    {
        /** The custom css files are fetched from application configuration */
        $custom_css_files = $this->GetConfig("general", "default_css_files");
        return $custom_css_files;
    }
    /**
     * Used to return the list of custom javascript files
     *
     * It returns the custom javascript files
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $custom_js_files the list of custom js files
     */
    public function GetCustomJsFiles($parameters) 
    {
        /** The custom javascript files are fetched from application configuration */
        $custom_js_files = $this->GetConfig("general", "default_javascript_files");
        return $custom_js_files;
    }
    /**
     * Used to return the html for the right column of form page
     * It should be overriden by a child class
     *
     * It returns the html text for the right column of form page
     *
     * @param array $parameters the parameters used to render the right hand column
     *
     * @return string $right_form_column_html the html text for the right column of form page
     */
    public function GetPageRightColumnHtml($parameters = array()) 
    {
        $right_form_column_html = "";
        return $right_form_column_html;
    }
    /**
     * Used to return the html for the right column of list page
     *
     * It returns the html text for the right column of list page
     * It can add hyper links, linked images and selectboxes
     *
     * @param array $parameters the parameters used to render the right hand column
     *
     * @return string $right_list_column_html the html text for the right column of list page
     */
    public function GetListRightColumnHtml($parameters = array()) 
    {
        /** The right column html */
        $right_list_column_html = "";
        /** The parameters for rendering the top right column of list page */
        $template_parameters = array();
        /** The current module name */
        $module_name = $this->GetConfig("general", "module");
        /** A new link is created for each data type */
        for ($count = 0;isset($parameters['data']) && $count < count($parameters['data']);$count++) 
        {
            /** The type of item. e.g selectbox, image or hyperlink */
            $item_type = $parameters['item_type'];
            /** If the item type is image or hyperlink */
            if ($item_type == "image" || $item_type == "hyperlink") {                
		/** The link parameters */
		$link_parameters = array(
		    "object_name" => "listpage",
		    "data_type" => $parameters['data'][$count]['data_type'],
		    "format" => "unstructured"
		);
		/** The parameters of the link url */
		$link_url_parameters = array(
		    'option' => 'list_page',
		    'module_name' => $module_name,
		    'output_format' => 'html',
		    'parameters' => $link_parameters,
		    'is_link' => true,
		    'url' => '',
		    'object_name' => 'application',
		    'encode_parameters' => false,
		    'transformed_url_request' => 'index.php?id={url_id}'
		);
		/** If the link url parameter is set then it overrides the default link url parameters */
		if (isset($parameters['data'][$count]['link_url_parameters']))
		$link_url_parameters = array_merge($link_url_parameters, $parameters['data'][$count]['link_url_parameters']);
		/** The url of the link */
		$link_url = $this->GenerateUrl($link_url_parameters);
		/** If the link type is hyper link */
		if ($item_type == "hyperlink") {
                    /** The link parameters */
		    $link_parameters = array(
		            "href" => $link_url,
		            "class" => "CSSnavTOP",
		            "target" => "_self",
		            "text" => str_replace("_", " ", $parameters['data'][$count]['data_type']) ,
		            "name" => "manage_" . $parameters['data'][$count]['data_type'],
		            "id" => "manage_" . $parameters['data'][$count]['data_type'],
		            "onclick" => $parameters['data'][$count]['onclick'],
		        );
		        /** The template name */
		        $template_name = "right_column_link";
	        }
		/** If the item type is image */
		else if ($item_type == "image") {
                    /** The link parameters */
		    $link_parameters = array(
		            "link" => $link_url,
		            "onclick" => "return true",
		            "link_css_class" => "CSSnavTOP",
		            "image_src" => $parameters['data'][$count]['image_src'],
		            "image_css_class" => "custom-button-image",
		            "button_title" => "Manage " . ucwords(str_replace("_", " ", $parameters['data'][$count]['data_type'])) ,
		            "id" => "manage_" . ucwords($parameters['data'][$count]['data_type'])
		    );
	            /** The template name */
		    $template_name = "image_link";
	        }
	        /** The template parameters are set to the link parameters */
	        $template_parameters = $link_parameters;
            }
            /** If the link type is selectbox */
            else if ($item_type == "selectbox") {
                /** The template name */
                $template_name = "selectbox";
                /** The selectbox template parameters are set to the selectbox parameters */
                $template_parameters    = $parameters['data'][$count]['selectbox'];
                /** The selectbox options are set to the selectbox options parameters */
                $template_parameters['selectbox_options'] = $parameters['data'][$count]['selectbox_options'];
            }
            /** The link html is rendered */
            $right_list_column_html.= $this->GetComponent("template")->Render($template_name, $template_parameters);
            if ($count < count($parameters['data']) - 1) $right_list_column_html.= "&nbsp;|&nbsp;";
            else $right_list_column_html.= "&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        return $right_list_column_html;
    }
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
        /** The input button title */
        $input_button_parameters['title'] = "Add " . rtrim(ucwords(str_replace("_", " ", $parameters['data_type'])) , "s");
        /** If the name option is set then it is added to the input button title */
        if (isset($parameters['extra_link_field'])) $input_button_parameters['title'] = $input_button_parameters['title'] . " to: " . ucwords(str_replace("_", " ", $parameters['extra_link_field']));
        /** The input button url parameters */
        $input_button_parameters['parameters'] = array(
            "option" => "form_page",
            "object_name" => "form",
            "data_type" => $parameters['data_type'],
            "mode" => "add",
            "id" => "-1",
            "format" => $parameters['format'],
            "parent_row_id" => (isset($parameters['id']) ? $parameters['id'] : "-1")
        );
        /** If the database_name option is set then it is added to the input button */
        if (isset($parameters['database_name'])) $input_button_parameters['parameters']['database_name'] = $parameters['database_name'];
        /** If the name parameters is set then it is added to the input button url parameters */
        if (isset($parameters['extra_link_field'])) 
        {
            $input_button_parameters['parameters']['extra_link_field'] = $parameters['extra_link_field'];
        }
        /** The onclick button event */
        $input_button_parameters['onclick'] = "";
        return $input_button_parameters;
    }
    /**
     * Used to return the presentation data for the list page
     *
     * It returns the presentation data for the list page
     *
     * @param array $field_information the list of field names
     *
     * @return array $presentation_data the presentation data for the list page
     *    header_widths => array the width of each list page header
     *    table_headers => array the list page table headers
     *    column_css_class => array the css class for each table column
     */
    public function GetPresentationData($field_information) 
    {
        /** The presentation data */
        $presentation_data = array(
            "header_widths" => array() ,
            "table_headers" => array() ,
            "column_css_class" => array()
        );
        /** The number of fields */
        $field_count = count($field_information);
        /** Each field is added to presentation data */
        for ($count = 0;$count < $field_count;$count++) 
        {
            /** The width of each list page header */
            $presentation_data['header_widths'][] = ($count == 0) ? "5%" : (($field_count - 3) / 100) . "%";
            /** The list page table headers */
            $presentation_data['table_headers'][] = ucwords(str_replace("_", " ", $field_information[$count]['field_name']));
            /** The css class for each table column */
            $presentation_data['column_css_class'][] = "left-align";
        }
        /** The edit and delete columns are appended */
        $presentation_data['header_widths'] = array_merge($presentation_data['header_widths'], array(
            "5%",
            "5%"
        ));
        /** The edit and delete columns are appended */
        $presentation_data['table_headers'] = array_merge($presentation_data['table_headers'], array(
            "Edit",
            "Delete"
        ));
        /** The edit and delete columns are appended */
        $presentation_data['column_css_class'] = array_merge($presentation_data['column_css_class'], array(
            "center-align",
            "center-align"
        ));
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
     */
    public function GetDatabaseInformation($parameters) 
    {
    }
    /**
     * Used to return the parameters needed for rendering the list page links
     *
     * It returns the parameters needed for rendering the list page icons
     *
     * @param array $parameters the parameters for the current list page
     *
     * @return array $list_page_links the parameters used to render the links on the list page
     *    type => string the key field for the database
     *    is_ajax => string optional the short table name for the data
     *    is_popup => boolean optional indicates if the link contents should be displayed in a popup
     *    image_name => string optional the short name of the link icon image. the image should be in the application image folder
     *    image_css_class => string optional the css class of the link icon image
     *    button_title => string optional the link icon title
     *    onclick => string the onlick event handler function
     *    option => string optional the link url option
     */
    public function GetListPageLinks($parameters) 
    {
        /** The parent_row_id field is set */
        $link_parameters['parent_row_id'] = (isset($parameters['id'])) ? $parameters['id'] : '-1';
        /** The extra_field_link field is set */
        $link_parameters['extra_field_link'] = (isset($parameters['extra_field_link'])) ? $parameters['extra_field_link'] : '-1';
        /** The edit link */
        $edit_link = array_merge(array(
            "data_type" => $parameters['data_type'],
            "type" => "edit",
            "button_title" => "Edit",
            "format" => $parameters['format'],
            "onclick" => "Edit"
        ) , $link_parameters);
        /** The delete link */
        $delete_link = array_merge(array(
            "data_type" => $parameters['data_type'],
            "type" => "delete",
            "button_title" => "Delete",
            "format" => $parameters['format'],
            "onclick" => "BasicSite.DeleteItem('{url}')"
        ) , $link_parameters);
        /** The list page links */
        $list_page_links = array(
            $edit_link,
            $delete_link
        );
        return $list_page_links;
    }
    /**
     * Used to add a column to list page presentation data
     *
     * It adds a new column to the given list page presentation data
     * The column is added to the given position
     *
     * @param array $presentation_data the presentation data for the list page
     *    header_widths => array the width of each list page header
     *    table_headers => array the list page table headers
     *    column_css_class => array the css class for each table column
     * @param string $header_width the header width
     * @param string $header_name the name of the header column
     * @param string $header_css_class the name of the header column css class
     * @param int $header_position the position at which the new header should be added. the first position is 0
     *
     * @return array $updated_presentation_data the updated presentation data containing the new column
     *    header_widths => array the width of each list page header
     *    table_headers => array the list page table headers
     *    column_css_class => array the css class for each table column
     */
    public function InsertColumn($presentation_data, $header_width, $header_name, $header_css_class, $header_position) 
    {
        /** The updated presentation data */
        $updated_presentation_data = $presentation_data;
        /** Empty value is added to each presentation data item */
        foreach ($updated_presentation_data as $type => $value) 
        {
            /** All elements upto the given header position are removed */
            $arr1 = array_slice($value, 0, ($header_position));
            /** All elements from from given header position are removed */
            $arr2 = array_slice($value, ($header_position));
            /** The updated presentation data value */
            $updated_presentation_data[$type] = array_merge($arr1, array(
                ""
            ) , $arr2);
        }
        /** The header width for the new column is added */
        $updated_presentation_data['header_widths'][$header_position] = $header_width;
        /** The header name for the new column is added */
        $updated_presentation_data['table_headers'][$header_position] = $header_name;
        /** The header css class for the new column is added */
        $updated_presentation_data['column_css_class'][$header_position] = $header_css_class;
        return $updated_presentation_data;
    }
    /**
     * Used to delete a column from the list page presentation data
     *
     * It removes a column from the list page presentation data
     * The column is removed from the given position
     *
     * @param array $presentation_data the presentation data for the list page
     *    header_widths => array the width of each list page header
     *    table_headers => array the list page table headers
     *    column_css_class => array the css class for each table column
     * @param array $header_positions the positions from which the column should be removed
     *
     * @return array $updated_presentation_data the updated presentation data without the removed column
     *    header_widths => array the width of each list page header
     *    table_headers => array the list page table headers
     *    column_css_class => array the css class for each table column
     */
    public function DeleteColumns($presentation_data, $header_positions) 
    {
        /** The updated presentation data */
        $updated_presentation_data = array();
        /** Each presentation data item is copied */
        foreach ($presentation_data as $key => $value) 
        {
            /** The presentation data at each position is removed */
            for ($count = 0;$count < count($presentation_data[$key]);$count++) 
            {
                /** If the current count value is not included in the header position data */
                if (!in_array($count, $header_positions)) $updated_presentation_data[$key][] = $presentation_data[$key][$count];
            }
        }
        return $updated_presentation_data;
    }
    /**
     * Used to return hidden form fields
     *
     * It returns the form fields that need to be hidden
     *
     * @return array $hidden_form_field_list list of form fields that need to be hidden
     */
    public function GetHiddenFormFields() 
    {
        /** The list of form fields that need to be hidden */
        $hidden_form_field_list = array(
            "id",
            "sr_no",
            "id_list"
        );
        return $hidden_form_field_list;
    }
    /**
     * Used to update the form data before it is saved to database
     * It should be overriden by a child class
     * It updates the form data before it is saved to database
     *
     * @param array $data the row data to be filtered
     * @param array $parameters the form page parameters
     *
     * @return array $data the data to be saved to database
     */
    public function FilterSaveData($data, $parameters) 
    {
        return $data;
    }
    /**
     * Used to get the url of a link on the list page
     *
     * It returns the link url
     *
     * @param array $parameters the application parameters
     *
     * @return string $url the link url
     */
    public function GetLinkUrl($parameters) 
    {
        /** If the table_row field is set */
        if (isset($parameters['table_row'])) 
        {
            /** If a parameter needs a field value then it is added */
            foreach ($parameters['table_row'] as $field_name => $field_value) 
            {
                /** The html tags are removed from the field value and the field value is trimmed */
                $field_value = trim(strip_tags($field_value));
                /** Each link parameter is checked */
                foreach ($parameters as $parameter_key => $parameter_value) 
                {
                    /** If one of the parameter values contains the field name */
                    if ($parameter_value == "{" . $field_name . "}") 
                    {
                        /** The event handler function is updated */
                        $parameters[$parameter_key] = str_replace("{" . $field_name . "}", $field_value, $parameters[$parameter_key]);
                    }
                }
            }
        }
        /** The link url parameters are set. row id and data type are passed as parameters */
        $link_parameters = array(
            "data_type" => $parameters['data_type'],
            "format" => $parameters['format']
        );
        /** If the link parameters are given, then the link parameters given in the application parameters are added */
        if (isset($parameters['link_parameters']) && $parameters['link_parameters'] != "") 
        {
            $link_parameters = array_merge($link_parameters, $parameters['link_parameters']);
        }
        /** The current module name */
        $module_name = $this->GetConfig("general", "module");
        /** The url option */
        if ($parameters['type'] == 'edit')
        {
            $url_option = "form_page";
            $link_parameters['mode'] = "edit";
            $link_parameters['object_name'] = "listpage";
            $link_parameters['redirect_url'] = $this->GetConfig("path", "current_url");
            $link_parameters['id'] = (isset($parameters['row_id']) && $parameters['row_id'] > 0) ? $parameters['row_id'] : '-1';
        }
        else if ($parameters['type'] == 'delete') 
        {
            $url_option = "delete_data";
            $link_parameters['object_name'] = "form";
            $link_parameters['redirect_url'] = $this->GetConfig("path", "current_url");
            $link_parameters['id'] = (isset($parameters['row_id']) && $parameters['row_id'] > 0) ? $parameters['row_id'] : '-1';
        }        
        else {
            $url_option = $parameters['option'];
            $link_parameters['id'] = (isset($parameters['sr_no']) && $parameters['sr_no'] > 0) ? $parameters['sr_no'] : '-1';
        }
        if (isset($parameters['extra_link_field'])) 
        {
            /** The extra field link value */
            $link_parameters['extra_link_field'] = $parameters['table_row'][$parameters['extra_link_field']];
        }
        /** If the database name is given in the url and the format is unstructured then it is also added to the new url */
        if (isset($parameters['database_name']) && $parameters['format'] != 'unstructured') 
        {
            /** The database name */
            $link_parameters['database_name'] = $parameters['database_name'];
        }
        /** If the database name is given in url then it is set */
        if (isset($parameters['database_name'])) $link_parameters['database_name'] = $parameters['database_name'];
        /** The format for the data */
        $link_parameters['format'] = $parameters['format'];        
        /** If the parent row id option is given then parent row id is set to the given value */
        if (!isset($link_parameters['parent_row_id'])) {
        /** The parent page id */
        $link_parameters['parent_row_id'] = (isset($parameters['row_id']) && $parameters['row_id'] > 0) ? $parameters['row_id'] : '-1';       
        }                            

        /** The parameters of the link url */
        $link_url_parameters = array(
            'option' => $url_option,
            'module_name' => $module_name,
            'output_format' => 'json',
            'parameters' => $link_parameters,
            'is_link' => true,
            'url' => '',            
            'encode_parameters' => false,
            'transformed_url_request' => 'index.php?id={url_id}'
        );
        /** If the type of parameters is delete */
        if ($parameters['type'] == 'delete') {
            $link_url_parameters['transformed_url_request'] = '';
        }
        /** The object name is set if it is not given by the user */
        $link_url_parameters['object_name'] = (isset($link_parameters['object_name'])) ? $link_parameters['object_name'] : 'application';       
        /** The required url */
        $url = $this->GenerateUrl($link_url_parameters);

        return $url;
    }
    /**
     * Used to get the selectbox option values
     *
     * It returns the values for a selectbox
     *
     * @param string $field_name the name of the selectbox field
     *
     * @return array $selectbox_values the selectbox values
     */
    public function GetSelectBoxValues($field_name) 
    {
        /** Header menu items */
        $selectbox_values = "";
        return $selectbox_values;
    }
    /**
     * Used to update the table row data before it is displayed
     * By default the function replaces the table row id value with row number
     * It should be overriden by a child class
     *
     * @param array $data the row data to be filtered
     * @param int $row_number the row number
     *
     * @return array $data the updated row data
     */
    public function FilterRowData($data, $row_number) 
    {
        /** The current application parameters */
        $parameters = $this->GetConfig("general", "parameters");
        /** The total number of rows per page */
        $rows_per_page = $this->GetConfig("general", "rows_per_page");
        /** The current page number */
        $current_page = (isset($parameters['current_page'])) ? $parameters['current_page'] : '1';
        /** The key field value */
        $key_field = $parameters['key_field'];
        /** The application parameters are fetched */
        $application_parameters = $this->GetConfig("general", "parameters");
        /** If the key field is not empty then it is set to row number */
        if ($key_field != "") $data[$key_field] = ($current_page < 0) ? $row_number: ($row_number + ($current_page - 1) * $rows_per_page);
        /** If the data contains a created_on column */
        if (isset($data['created_on'])) 
        {
            /** If the created_on field is numeric, then it is assumed to be a unix timestamp and is formatted */
            $data['created_on'] = (is_numeric($data['created_on'])) ? date("d-m-Y H:i:s", $data['created_on']) : $data['created_on'];
        }
        /** If the data contains a url column, then the column is linked */
        if (isset($data['url'])) 
        {
            /** The page link parameters */
            $link_parameters = array(
                "link" => $data['url'],
                "id" => "navigation_link_" . $data['id'],
                "target" => "_self",
                "text" => wordwrap($data['url'], 75, "<br/>", true)
            );
            /** The page link */
            $data['url'] = $this->GetComponent("template")->Render("link", $link_parameters);
        }
        /** Each row field is checked */
        foreach ($data as $field_name => $field_value) 
        {
            /** If a field value is base64 encoded, then it is decoded */
            //if($this->GetComponent("string")->IsBase64($data[$field_name]))
            // $data[$field_name]   = base64_decode($data[$field_name]);
            
            /** If the field value length is larger than 100 characters, and the field name is not url, then it is clipped */
           //if (strlen($data[$field_name]) > 100 && $field_name != "url") $data[$field_name] = substr(strip_tags($data[$field_name]) , 0, 100) . "...";
        }
        return $data;
    }
    /**
     * Used to filter table rows
     *
     * It removes rows from the table that do not match some criteria
     * It also divides the data into pages
     *
     * @param array $table_data the table rows and parent field id
     *    table_rows => array the table row data
     *    parent_field_name => int the parent table field name
     *
     * @return array $updated_table_rows the table data
     */
    public function FilterTableData($table_data) 
    {
        /** The current application parameters */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current page number */
        $current_page = (isset($parameters['current_page'])) ? $parameters['current_page'] : '1';
        /** The parent field name */
        $parent_field_name = $table_data['parent_field_name'];
        /** The table rows */
        $table_rows = $table_data['table_rows'];
        /** The updated table rows */
        $updated_table_rows = array();
        /** The application parameters are fetched */
        $parameters = $this->GetConfig("general", "parameters");
        /** The parent row id is fetched */
        $parent_row_id = (isset($parameters['parent_row_id'])) ? $parameters['parent_row_id'] : '-1';
        /** The updated table rows */
        $updated_table_rows = array();
        /** If the parent row id is set */
        if ($parent_row_id > 0) 
        {
            /** Each table row is checked */
            for ($count = 0;$count < count($table_rows);$count++) 
            {
                /** The table row */
                $table_row = $table_rows[$count];
                /** If the parent id of the row is set and it matches the parent id
                 *  Then the row is added to updated table rows
                 */
                if (($parent_row_id > 0 && isset($table_row[$parent_field_name]) && $table_row[$parent_field_name] == $parent_row_id) || $parent_field_name == "-1") 
                {
                    /** The parent row id column is removed */
                    unset($table_rows[$count][$parent_field_name]);
                    /** The table row is added to the list of updated table rows */
                    $updated_table_rows[] = $table_rows[$count];
                }
            }
        }
        else
        /** The updated table rows */
        $updated_table_rows = $table_rows;
        return $updated_table_rows;
    }
    /**
     * Used to get the value for form onsubmit event
     *
     * It returns the form onsubmit event handler
     *
     * @param array $parameters the form page parameters
     *
     * @return string $form_onsubmit the form onsubmit event handler
     */
    public function GetFormOnsubmit($parameters) 
    {
        $form_onsubmit = 'return true';
        return $form_onsubmit;
    }
    /**
     * Used to get the form information
     *
     * It returns the form information including the field information
     *
     * @param string $form_name the form name
     * @param array $condition the condition used to fetch the form data from database
     * @param boolean $force_fetch used to indicate that the application configuration should be force fetched from database
     * @param string $sort_field the field by which the data is sorted
     * @param string $sort_order the sort order for the data
     *
     * @return array $form_information the form information
     *    form_id => int the form id
     *    form_field_information => array the form field information
     *    form_data => array the structured form data     
     *    raw_form_data => array the raw form data          
     *    row_count => int the row count
     */
    public function GetFormInformation($form_name, $condition) 
    {
        /** The form data is read */
        $data = $this->GetComponent("unstructureddataui")->GetFormData($form_name, "*", $condition);
        /** The form id is fetched */
        $form_id = $this->GetComponent("databasereader")->GetFormId($form_name);
        /** The field information is read */
        $form_field_information = $this->GetComponent("databasereader")->GetUnstructuredFieldNames($form_id);
        /** The total row count is fetched */
        $row_count = $this->GetComponent("databasereader")->GetUnstructuredTableRowCount($form_name, $condition);
        /** The form information */
        $form_information = array(
            "form_id" => $form_id,
            "form_field_information" => $form_field_information,
            "form_data" => $data['structured_data'],
            "raw_form_data" => $data['raw_data'],
            "row_count" => $row_count
        );
        return $form_information;
    }
    /**
     * Used to return the table pagination
     *
     * It returns the table pagination information
     *
     * @param array $table_rows the table data
     *
     * @return array $pagination_information
     *    current_page => int the current page number
     *    total_page_count => int the total page count
     *    page_list => string the page list html
     */
    public function GetPaginationInformation($table_rows) 
    {
        /** The current application parameters */
        $parameters = $this->GetConfig("general", "parameters");
        /** The form data row count. It is the number of filtered table rows */
        $form_data_row_count = count($table_rows);
        /** The current page number */
        $current_page = (isset($parameters['current_page'])) ? $parameters['current_page'] : '1';
        /** The total number of rows per page */
        $rows_per_page = $this->GetConfig("general", "rows_per_page");
        /** The total number of pages */
        $total_page_count = ceil($form_data_row_count / $rows_per_page);
        /** The page list */
        $page_list = "";
        /** If the current page is greater than 1 then the previous link is included */
        if ($current_page > 1) 
        {
            $page_list = $this->GetPageLink("[Prev]", ($current_page - 1)) . "&nbsp;";;
        }
        /** The page list is built */
        for ($count = 1;$count <= $total_page_count && $total_page_count > 1;$count++) 
        {
            /** If the count is equal to the current page number, then the page number is not linked */
            if ($count == $current_page) $page_list.= $count . "&nbsp;";
            else $page_list.= $this->GetPageLink($count, $count) . "&nbsp;";
        }
        /** If the current page is less than total number of pages, then next link is included */
        if ($current_page < $total_page_count) 
        {
            $page_list.= $this->GetPageLink("[Next]", ($current_page + 1)) . "&nbsp;";
        }
        /** If the total page count is greater than 1 then the All link is appended to the page list */
        if ($total_page_count > 1) 
        {
            $page_list.= $this->GetPageLink("[All]", "-1");
        }
        /** If the current page is -1 then the current and total pages is set to 1 */
        if ($current_page == - 1) 
        {
            $current_page = $total_page_count = 1;
        }
        /** The current page. It is set to empty if the total page count is 0 */
        $current_page_text = ($total_page_count == 0) ? '' : 'PAGE ' . $current_page . ' of ' . $total_page_count;
        /** The pagination information */
        $pagination_information = array(
            "current_page_text" => $current_page_text,
            "page_list" => $page_list
        );
        return $pagination_information;
    }
    /**
     * Used to return the page link
     *
     * It returns the page link for the given page number
     *
     * @param string $link_text the link text
     * @param int $page_number the page number
     *
     * @return string $page_link the page html link
     */
    private function GetPageLink($link_text, $page_number) 
    {
        /** The current application parameters */
        $parameters = $this->GetConfig("general", "parameters");
        /** The page number is added to current page parameters */
        $parameters['current_page'] = $page_number;
        /** The current page number */
        $current_page = (isset($parameters['current_page'])) ? $parameters['current_page'] : '1';
        /** The add url parameters */
        $link_parameters = array(
            'option' => $this->GetConfig("general", "option") ,
            'module_name' => $this->GetConfig("general", "module") ,
            'output_format' => 'html',
            'parameters' => $parameters,
            'is_link' => true,
            'url' => '',
            'object_name' => 'application',
            'encode_parameters' => false,
            'transformed_url_request' => ''
        );
        /** The input button url */
        $page_link = $this->GenerateUrl($link_parameters);
        /** The page link parameters */
        $link_parameters = array(
            "link" => $page_link,
            "id" => "pagination_link_" . rand(0, 1000) ,
            "target" => "_self",
            "text" => $link_text
        );
        /** The page link */
        $page_link = $this->GetComponent("template")->Render("link", $link_parameters);
        return $page_link;
    }
    /**
     * Used to return a link
     *
     * It returns the link html for the given link parameters
     *
     * @param array $parameters the link parameters
     *    link => string the link url
     *    link_id => int the link id
     *    link_text => string the link text
     *    link_parameters => array the link parameters
     *
     * @return string $page_link the page html link
     */
    protected function GetLink($parameters) 
    {
        /** The link url */
        $link_url = $this->GenerateUrl($parameters['link_parameters']);
        /** The page link parameters */
        $link_parameters = array(
            "link" => $link_url,
            "id" => $parameters['link_id'],
            "target" => "_self",
            "text" => $parameters['link_text']
        );
        /** The page link */
        $page_link = $this->GetComponent("template")->Render("link", $link_parameters);
        return $page_link;
    }
    /**
     * Used to return the rows in the current page
     *
     *
     * It returns the rows for the current page
     *
     * @param array $table_rows the table data
     *
     * @return array $updated_table_rows the table data
     */
    public function GetRowsInCurrentPage($table_rows) 
    {
        /** The current application parameters */
        $parameters = $this->GetConfig("general", "parameters");
        /** The current page number */
        $current_page = (isset($parameters['current_page'])) ? $parameters['current_page'] : '1';
        /** If the current page is not -1 then the data is split into pages */
        if ($current_page != '-1') 
        {
            /** The total number of rows per page */
            $rows_per_page = $this->GetConfig("general", "rows_per_page");
            /** The index of the first row */
            $start_index = ($current_page - 1) * $rows_per_page;
            /** The table rows for the current page are extracted */
            $table_rows = array_slice($table_rows, $start_index, $rows_per_page);
        }
        return $table_rows;
    }
    /**
     * Used to create an encoded url for the application
     *
     * It creates a url that can optionally include encoded parameters
     * A parameters base64 encoded
     * Array parameters are first json encoded
     *
     * @param array $parameters the parameters used to create the encoded url
     *    string $option the url option
     *    string $module_name the name of the module
     *    array $parameters the list of url parameters. it is an associative array. if set to false then the parameters are not used
     *    boolean $is_link used to indicate if url will be used in link. if it will be used in link then url & will be
     *    string optional $url the server url. if omitted then the framework url is used
     *    string optional object_name the name of the object that should be used to handler the request
     *        if this is not set then the object name is automatically calculated as the name of the object that called this function
     *    boolean $encode_parameters used to indicate if the url parameters should be encoded
     *    string $transformed_url_request the transformed_url_request to use. it can contain the placeholder:{url_id}. e.g index.php?id={url_id}
     *
     * @return string $formatted_url the formatted url is returned
     */
    public function GenerateUrl($parameters) 
    {
        /** The formatted url */
        $formatted_url                                 = "";
        /** The login url */
        $site_url                                      = $this->GetConfig("general", "site_url");
        /** Indicates if application should use formatted urls */
        $use_formatted_urls                            = $this->GetConfig("general", "use_formatted_urls");
        /** The transformed url request */
        $transformed_url_request                       = $parameters['transformed_url_request'];
        /** The parameters used to get the base url */
        $updated_parameters                            = $parameters;
        /** The transformed_url_request is set to empty */
        $updated_parameters['transformed_url_request'] = '';
        /** The parent function is called */
        $url = parent::GenerateUrl($updated_parameters);        
        /** If the url should be generated as a long url */
        if ($transformed_url_request == "" || $use_formatted_urls === false) return $url;
        
        /** The url parameters */
        $url_parameters = $this->GetComponent("encryption")->EncodeData($parameters);
        /** The contents of the application cache are fetched */
        $application_cache                             = $this->GetComponent("application")->FetchApplicationCache();
        /** If the formatted form information is set and the url exists in application cache, then it is returned */
        if (isset($application_cache['formatted_form_information']) && isset($application_cache['formatted_form_information'][$url_parameters])) {
            $formatted_url                             = $application_cache['formatted_form_information'][$url_parameters];
            return $formatted_url;
        }
        /** The formatted form information */
        $formatted_form_information                    = array();
        /** The form information */
        $form_information                              = $this->GetFormInformation("urls", false);
        /** Each form item is checked */
        for ($count = 0; $count < count($form_information['form_data']); $count++) {
            /** The form item */
            $form_item                                 = $form_information['form_data'][$count];
            /** The form item is added to the formatted form information */
            $formatted_form_information[$form_item['url_parameters']] = $form_item['request_url'];
        }                       
        /** The number of generated urls */
        $generated_url_count                           = $form_information['row_count'];
        /** The data to cache */
        $cache_data                                    = array("formatted_form_information" => $formatted_form_information, "row_count" => $generated_url_count);
        /** The formatted form information is saved to application cache */
        $this->GetComponent("application")->UpdateApplicationCache($cache_data);
        
        /** The url_id placeholder in the transformed_url_request is replaced with (generated_url_count+1) */
        $transformed_url_request                       = str_replace("{url_id}", ($generated_url_count + 1) , $transformed_url_request);
        /** The row data */
        $formatted_url                                 = (isset($form_information['form_data'][0])) ? $form_information['form_data'][0]['request_url'] : $site_url . $transformed_url_request;
        /** If the url was previously saved in database then it is returned */
        if (isset($form_information['form_data'][0])) 
        {
            /** The formatted url in database */
            $formatted_url = $form_information['form_data'][0]['request_url'];
            /** The formatted url is returned */
            return $formatted_url;
        }
       
        /** The parameters for saving the form data. The form mode is set to add */
        $parameters['mode'] = 'add';
        /** The form data to be saved */
        $form_data = array(
            array(
                "field_id" => $form_information['form_field_information'][0]['id'],
                "field_value" => $formatted_url,
                "updated_on" => time()
            ) ,
            array(
                "field_id" => $form_information['form_field_information'][1]['id'],
                "field_value" => $url_parameters,
                "updated_on" => time()                
            ) ,
        );
        /** The data type is set to urls */
        $parameters['parameters']['data_type'] = 'urls';
        /** The url data is saved to database */
        $this->GetComponent("unstructureddataui")->SaveFormData($form_data, $parameters);
        
        return $formatted_url;
    }
    /**
     * Used to update url parameters
     *
     * This function allows updating url parameters
     */
    public function UpdateUrlParameters() 
    {
        /** The current request url */
        $current_request_url = $this->GetConfig("path", "current_url");
        /** Indicates if the application should use formatted urls */
        $use_formatted_urls = $this->GetConfig("general", "use_formatted_urls");
        /** If the application should not use formatted urls then the function returns */
        if (!$use_formatted_urls) return;       
        /** The condition used to fetch the data from database */
        $condition = array(
            "request_url" => $current_request_url
        );
        /** The form information */
        $form_information = $this->GetFormInformation("urls", $condition);
        /** If the url parameter information was not found then the function returns */
        if (!isset($form_information['form_data'][0])) return;
        /** The row data */
        $row_data = $form_information['form_data'][0];
        /** The decoded url parameters */
        $url_parameters = $this->GetComponent("encryption")->DecodeData($row_data['url_parameters']);
        /** The general application parameters are fetched from application configuration and merged with the parameters in database */
        $application_parameters = $this->GetConfig("general", "parameters");        
        /** Each url parameter is set in application configuration */
        foreach ($url_parameters as $key => $value) 
        {
            /** If the key is parameters */
            if ($key == "parameters") {
                /** The configuration data is merged */
                $value = array_merge($application_parameters, $value);
            }
            /** The url parameters are saved */
            $this->SetConfig("general", $key, $value);
        }
    }
}

