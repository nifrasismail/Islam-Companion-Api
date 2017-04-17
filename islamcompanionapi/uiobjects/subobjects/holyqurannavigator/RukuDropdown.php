<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\HolyQuran as HolyQuran;
use \IslamCompanionApi\DataObjects\Rukus as Rukus;

/**
 * This class implements the Ruku dropdown
 * 
 * It contains functions used to generate the html for the Ruku dropdown
 * 
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
class RukuDropdown extends \Framework\Object\UiObject
{	
	/**
     * Used to load the Ruku Dropdown object with data
     * 
     * It loads the data from database to the object
     * 
     * @since 1.0.0
	 * @param array $data data used to read verse information from database
	 * it is an array with following keys:
	 * division => the current division
	 * division_number => the current divsion number
	 * ruku => the current ruku
	 * sura => the current sura
     */
    public function Read($data="")
	{
	    /** The data is set to the objects local data property */
		$this->data                          = $data;
		/** The configuration object is fetched */
		$parameters['configuration']         = $this->GetConfigurationObject();
		/** The Rukus object is created */
		$rukus                               = new Rukus($parameters);
		/** The sura */
		$sura                                = $data['sura'];
		/** The division number */
		$division_number                     = $data['division_number'];
		/** The division */
		$division                            = $data['division'];
		/** The list of rukus in given division number is fetched */
		$this->data['ruku_list']             = $rukus->GetRukusInDivision($sura,$division,$division_number);		
	}
	
    /**
     * Used to display the Ruku Dropdown
     * 
     * It returns the html of the Ruku dropdown
     * 
     * @since 1.0.0
	 * 
	 * @return string $ruku_dropdown_html the html string for the Ruku Dropdown
     */
    public function Display()
	{
		/** The current ruku */
		$current_ruku                  = $this->data['ruku'];
		/** The path to the plugin template folder */
    	$plugin_template_path          = $this->GetConfig("path","application_template_path");
		/** The options html is fetched */
		$template_parameters           = array();
		/** The sura data is prepared */
		for($count = 0; $count < count($this->data['ruku_list']); $count++) {
		    /** The information for single ruku */
			$ruku_information          = $this->data['ruku_list'][$count];
			/** Used to indicate if the current ruku should be selected in the dropdown */
			$selected                  = ($ruku_information['id'] == $current_ruku)?'SELECTED':'';
			/** The ruku information is added to template parameters */
			$template_parameters[]     = array("text"=>$ruku_information['sura_ruku'],"value"=>$ruku_information['id'],"selected"=>$selected);
		}

		/** The Ruku dropdown options are rendered using template parameters */
        $options_html                  = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "option.html", $template_parameters);			
		/** The Ruku dropdown templates parameters */
		$template_parameters           = array("name"=>"ic-ruku","id"=>"ic-ruku","options"=>$options_html, "title" => "");
		/** The Ruku dropdown template is rendered using the template parameters */
        $ruku_dropdown_html            = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "select.html", $template_parameters);
		
		return $ruku_dropdown_html;
	}
}
