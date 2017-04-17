<?php
namespace Framework\Templates\BasicSite\UserInterface;

use Framework\Object\UiObject as UiObject;
use Framework\Object\MysqlDataObject as MysqlDataObject;

/**
 * This class extends the UiObject class
 *
 * It contains functions that help in generating html tables
 *
 * @category   Framework
 * @package    UserInterface
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class HtmlTable extends UiObject
{
    /**
     * Used to read the data from database
     *
     * It reads the data from database and sets the data to sub_items object property
     * It returns the sub_items property
     */
    public function Read($data = "") 
    {
        /** The table data */
        $table_data = array();
        /** The data object property. It is set to the database reader and database presentation parameters */
        $this->data = $data;
        /** The sub items for the html table. The sub items are the table data */
        $this->sub_items = $this->GetComponent("databasereader")->Read($this->data['database_reader_data']);
        /** The sub items are converted to the correct format */
        for ($count = 0;$count < count($this->sub_items);$count++) 
        {
            $sub_item = $this->sub_items[$count];
            $table_data[] = ($sub_item);
        }
        $this->sub_items = $table_data;
        return $this->sub_items;
    }
    /**
     * Used to load the object with data
     *
     * It set the data to the object's sub_items property
     *
     * @param array $data data for the object's sub items property
     */
    function Load($data) 
    {
        $this->sub_items = $data;
    }
    /**
     * Used to display the table data in a html table
     *
     * It renders the table data to a html table.
     *
     * @return string $table_html html of table containing the table data
     */
    public function Display() 
    {
        /** If no database data was found then a message is shown */
        if (count($this->sub_items) == 0) 
        {
            /** The path to the heading template file */
            $template_file_path = $this->GetConfig("path", "framework_template_path") . DIRECTORY_SEPARATOR . "large_heading.html";
            /** The html table string is generated using the table parameters */
            $table_html = $this->GetComponent("template_helper")->RenderTemplateFile($template_file_path, array(
                "heading_text" => "No Data was found !"
            ));
        }
        else
        {
            /** The table data */
            $this->data['presentation_data']['table_rows'] = $this->sub_items;
            /** The table parameters used to render the table data */
            $table_parameters = $this->GetComponent("htmltablepresentation")->GetTableParameters($this->data['presentation_data']);
            /** The cell attributes callback is set to empty */
            $table_parameters['cell_attributes_callback'] = (isset($this->data['cell_attributes_callback'])) ? $this->data['cell_attributes_callback'] : false;
            /** The html table string is generated using the table parameters */
            $table_html = $this->GetComponent("template")->Render("html_table", $table_parameters);
        }
        return $table_html;
    }
}    
