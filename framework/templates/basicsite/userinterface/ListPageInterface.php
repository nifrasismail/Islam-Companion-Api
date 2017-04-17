<?php

namespace Framework\Templates\BasicSite\UserInterface;

use Framework\Configuration\Base as Base;

/**
 * This class implements the ListPageInterface interface
 * It extends the Base class
 * 
 * It provides interface for list pages
 * Each list page class should implement the ListPageInterface
 * 
 * @category   Application
 * @package    ListPageInterface
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
interface ListPageInterface
{	
	/**
	 * Used to return the list page title
	 *
	 * It returns the list page title
	 *
	 * @param array $parameters the parameters for the current list page
	 * 
	 * @return string $list_page_title the list page title
	 */
	public function GetPageTitle($parameters);
	/**
	 * Used to return the list of custom css files
	 *
	 * It returns the custom css files
	 *
	 * @param array $parameters the parameters for the current list page
	 * 
	 * @return array $custom_css_files the list of custom css files
	 */
	public function GetCustomCssFiles($parameters);
	/**
	 * Used to return the list of custom js files
	 *
	 * It returns the custom js files
	 *
	 * @param array $parameters the parameters for the current list page
	 *
	 * @return array $custom_js_files the list of custom js files
	 */
	public function GetCustomJsFiles($parameters);
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
	public function GetHeaderTemplateParameters($parameters);
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
	public function GetInputButtonParameters($parameters);
	/**
	 * Used to return the presentation data for the list page
	 *
	 * It returns the presentation data for the list page
	 *
	 * @param array $parameters the parameters for the current list page
	 * 
	 * @return array $presentation_data the presentation data for the list page
	 */
	public function GetPresentationData($parameters);
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
	 */
	public function GetDatabaseInformation($parameters);
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
	 *    option => string optional the link url option
	 */
	public function GetListPageLinks($parameters);
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
	public function GetTableCellAttributes($row, $column, $column_count);
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
	 */
	public function GetRowData($data);
	/**
	 * Used to get the url of a list page link
	 *
	 * It returns the link url
	 *
	 * @param array $parameters the application parameters
	 * 
	 * @return string $url the link url
	 */
	public function GetLinkUrl($parameters);
	/**
	 * Used to filter table rows
	 *
	 * It removes rows from the table that do not match some criteria
	 *
	 * @param array $table_rows the table data
	 *
	 * @return array $table_rows the table data
	 */
	public function FilterTableData($table_rows);
}
