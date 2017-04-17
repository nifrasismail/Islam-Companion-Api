<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\Suras as Suras;

/**
 * This class implements the Sura dropdown
 * 
 * It contains functions used to generate the html for the Sura dropdown
 * 
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
class SuraDropdown extends \Framework\Object\UiObject
{	
	/**
     * Used to load the Sura Dropdown object with data
     * 
     * It fetches the list of suras for the given division number and division
	 * The list of suras are saved to local data property
     * 
     * @since 1.0.0
	 * @param array $data data used to read verse information from database
	 * it is an array with following keys:
	 * sura => the current sura
	 * division_number => the current divsion number
	 * division => the current division
     */
    public function Read($data="")
	{
		/** The data is set to the objects local data property */
		$this->data                          = $data;
		/** The configuration object is fetched */
		$parameters['configuration']         = $this->GetConfigurationObject();
		/** The Suras object is created */
		$suras                               = new Suras($parameters);
		/** The sura */
		$sura                                = $data['sura'];
		/** The ruku */
		$ruku                                = $data['ruku'];
		/** The division number */
		$division_number                     = $data['division_number'];
		/** The division */
		$division                            = $data['division'];
		/** If the division is ruku then all the suras are fetched */
		$all_suras                           = ($division == "ruku")?true:false;
		/** The list of suras in given division number is fetched */
		$this->data['sura_list']             = $suras->GetSurasInDivision($division,$division_number,$all_suras);
	}
	
    /**
     * Used to display the Sura Dropdown
     * 
     * It returns the html of the sura dropdown
     * 
     * @since 1.0.0
	 * 
	 * @return string $sura_dropdown_html the html string for the Sura Dropdown
     */
    public function Display()
	{
		/** The current sura */
		$current_sura                  = $this->data['sura'];
		/** The path to the plugin template folder */
    	$plugin_template_path          = $this->GetConfig("path","application_template_path");
		/** The options html is fetched */
		$template_parameters           = array();
		/** The sura data is prepared */
		for($count = 0; $count < count($this->data['sura_list']); $count++) {
		    /** The information for single sura */
			$sura_information          = $this->data['sura_list'][$count];
			/** Used to indicate if the current sura should be selected in the dropdown */
			$selected                  = ($sura_information['sindex'] == $current_sura)?'SELECTED':'';
			/** The sura information is added to template parameters */
			$template_parameters[]     = array("text"=>$sura_information['tname'] . " (".$sura_information['ename'].")","value"=>$sura_information['sindex'],"selected"=>$selected);
		}

		/** The Sura dropdown options are rendered using template parameters */
        $options_html                  = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "option.html", $template_parameters);
		/** The Sura dropdown templates parameters */
		$template_parameters           = array("name"=>"ic-sura","id"=>"ic-sura","options"=>$options_html, "title" => "");
		/** The Sura dropdown template is rendered using the template parameters */
        $sura_dropdown_html            = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "select.html", $template_parameters);
		
		return $sura_dropdown_html;
	}
}
