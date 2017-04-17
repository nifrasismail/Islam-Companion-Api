<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\HolyQuran as HolyQuran;

/**
 * This class implements the Division Number dropdown
 * 
 * It contains functions used to generate the html for the division number dropdown
 * 
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
class DivisionNumberDropdown extends \Framework\Object\UiObject
{	
	/**
     * Used to load the Division Number Dropdown object with data
     * 
     * It sets the division name and division count to the local data property
	 * If the divison name is ruku then the function returns
     * 
     * @since 1.0.0
	 * @param array $data data used to read verse information from database
	 * it is an array with following keys:
	 * division => the current division
	 * division number => the current division number
	 * javascript_function => the javascript function to be called when a dropdown is selected or button is clicked
     */
    public function Read($data="")
	{
		/** The division name */
		$this->data['division']                  = $data['division'];
		/** If the division name is ruku then the function returns */
		if ($this->data['division'] == 'ruku') return;		  
		/** The division number */
		$this->data['division_number']           = $data['division_number'];
	    /** The division count information is fetched */
	    $this->data['division_count']            = HolyQuran::GetMaxDivisionCount($data['division']);
	}     
    /**
     * Used to display the Division Number Dropdown
     * 
     * It returns the html of the division number dropdown
     * 
     * @since 1.0.0
	 * 
	 * @return string $division_number_dropdown_html the html string for the Division Number Dropdown
     */
    public function Display()
	{
		/** If the division name is ruku then the function returns an empty string */
		if ($this->data['division'] == 'ruku') return "";
			
		/** The path to the plugin template folder */
    	$plugin_template_path          = $this->GetConfig("path","application_template_path");
		/** The options html is fetched */
		$template_parameters           = array();
		/** The template parameters are generated */
		for ($count = 1; $count <= $this->data['division_count']; $count++) {
			$selected                  = ($this->data['division_number'] == $count)?"SELECTED":"";
			$template_parameters[]     = array("text"=>$count,"value"=>$count,"selected"=>$selected);
		}
	
		/** The Division Number dropdown options are rendered using template parameters */
        $options_html                  = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "option.html", $template_parameters);
		/** The division number dropdown templates parameters */
		$template_parameters           = array("name"=>"ic-division-number","id"=>"ic-division-number","options"=>$options_html, "title" => "");
		/** The Division Number dropdown template is rendered using the template parameters */
        $division_number_dropdown_html = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "select.html", $template_parameters);
		
		return $division_number_dropdown_html;
	}
}
