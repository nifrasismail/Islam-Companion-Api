<?php
namespace Framework\Templates\BasicSite\Presentation;
use Framework\Configuration\Base as Base;
/**
 * It defines a presentation class for html tables
 *
 * Contains functions that are used to render html tables
 *
 * @category   Framework
 * @package    Templates
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class HtmlTablePresentation extends Base
{
    /**
     * Used to format the data so its suitable for displaying in html table
     *
     * It extracts the data and formats it so it can be displayed in a html table
     *
     * @param array $table_data an array of objects containing table data
     *
     * @return array $table_parameters the formatted data
     *    header_widths => array the width of the table headers
     *    table_headers => array the list of table headers
     *    table_rows => array the table row data
     */
    final public function GetTableParameters($table_data) 
    {
        /** The table data is set */
        $this->data = $table_data;
        /** The alignment and width information of the table */
        $table_width_alignment = $this->GetHeaderWidthAlignment();
        /** Used to get the table sort information */
        $sort_information = $this->GetTableSortInformation();
        /** The table column header links */
        $table_links = $this->GetHeaderLinks($sort_information);
        /** The table headers */
        $table_headers = $this->GetTableHeaders($table_links);
        /** Used to get html table data */
        $table_rows = $this->GetRowParameters($table_data['table_rows']);
        /** Used to get html table css class */
        $table_css_class = $this->GetTableCssClass($table_data);
        /** The table row css values. The css class alternates between the rows **/
        $table_row_css = array(
            "CSSlistDARK",
            "CSSlistLIGHT"
        );
        /** The table parameters are returned **/
        $table_parameters = array(
            "header_widths" => $table_width_alignment['header_widths'],
            "header_column_class" => $table_width_alignment['column_css_class'],
            "table_headers" => $table_headers,
            "table_rows" => $table_rows,
            "table_row_css" => $table_row_css,
            "table_css_class" => $table_css_class
        );
        return $table_parameters;
    }
    /**
     * Used to get the table data
     *
     * It returns an array containing the table data
     * Each array element is an array that represents a table row
     * Each element in this array is a column for a given row
     *
     * @param array $table_rows an array containing table rows
     *
     * @return array $table_rows each element in the array contains the data for a row
     */
    protected function GetRowParameters($table_rows) 
    {
        return $table_rows;
    }
    /**
     * Used to get the table sort state
     *
     * It returns an array containing the table sort state
     *
     * @return array $table_data an array the table sort information
     */
    protected function GetTableSortInformation() 
    {
        return array();
    }
    /**
     * Used to get the movie table column links
     *
     * It returns an array containing the header links
     * The links allow data to be sorted by these columns
     *
     * @param array $sort_information an array containing sort information for the column
     *
     * @return array $column_links an array containing column link information
     */
    protected function GetHeaderLinks($sort_information) 
    {
        return array();
    }
    /**
     * Used to get the table header
     *
     * It returns an array containing the header text for each table header
     *
     * @param array $table_links the table header links
     *
     * @return array $table_header each element in the array contains the text for a header column
     */
    protected function GetTableHeaders($header_links) 
    {
        /** The table header data */
        $table_header = array();
        if (isset($this->data['table_headers'])) 
        {
            /** The table header columns */
            $table_header = $this->data['table_headers'];
            /** The general application options are fetched */
            $general_options = $this->GetConfig("general");
            /** The sort by field given in url parameters */
            $original_sort_by    = (isset($general_options['parameters']['sort_by'])) ? $general_options['parameters']['sort_by'] : "";
            /** The order by field given in url parameters */
            $original_order_by    = (isset($general_options['parameters']['order_by'])) ? $general_options['parameters']['order_by'] : "";          
            /** The center column index */
            $icon_column_index = (count($table_header) - $this->GetConfig("general", "center_column_index"));
            /** Each header column in converted to link except for id column */
            for ($count = 0;$count < ($icon_column_index);$count++) 
            {
                /** The header text */
                $header_text = $table_header[$count];
                /** The sort by field */
                $sort_by_field = str_replace(" ", "_", strtolower($header_text));                
                /** If the sort by field exists */                
                if ($original_sort_by == $sort_by_field) 
                {
                    /** The order by */
                    $sort_by_order = ($original_order_by == 'ASC') ? 'DESC' : 'ASC';                    
                }
                else $sort_by_order = "ASC";
                /** The sort by field */
                $general_options['parameters']['sort_by'] = $sort_by_field;
                /** The order by field */
                $general_options['parameters']['order_by'] = $sort_by_order;                
                /** The parameters for the column link */
                $link_parameters = array(
                    'option' => $general_options['option'],
                    'module_name' => $general_options['module'],
                    'output_format' => 'html',
                    'parameters' => $general_options['parameters'],
                    'is_link' => true,
                    'sort_by' => $sort_by_field,
                    'order_by' => $sort_by_order,
                    'url' => '',
                    'object_name' => $general_options['object_name'],
                    'encode_parameters' => false,
                    'transformed_url_request' => 'index.php?id={url_id}'
                );
                /** The url of the header link */
                $url = $this->GetComponent("unstructureddataui")->GenerateUrl($link_parameters);
                /** The page link parameters */
                $link_parameters = array(
                    "link" => $url,
                    "id" => "header_link_" . md5($url) ,
                    "target" => "_self",
                    "text" => str_replace("_", " ", $header_text)
                );
                /** The page link */
                $link = $this->GetComponent("template")->Render("link", $link_parameters);
                /** The table header item */
                $table_header[$count] = ($header_text == "Id") ? $header_text : $link;
            }
        }
        return $table_header;
    }
    /**
     * Used to get the table header widths and column alignments
     *
     * It gets the header widths and header alignments
     * So they can be used to display html table
     *
     * @return array $table_data an array with two keys:
     *    header_widths => array the width of each table column
     *    column_css_class => the css class for the columns
     */
    protected function GetHeaderWidthAlignment() 
    {
        /** The table data */
        $table_data = array();
        if (isset($this->data['header_widths'])) 
        {
            $table_data["header_widths"] = $this->data['header_widths'];
        }
        if (isset($this->data['column_css_class'])) 
        {
            $table_data["column_css_class"] = $this->data['column_css_class'];
        }
        return $table_data;
    }
    /**
     * Used to get the table css class
     *
     * It returns the table css class
     *
     * @return string $table_css_class the table css class
     */
    protected function GetTableCssClass() 
    {
        return "";
    }
}

