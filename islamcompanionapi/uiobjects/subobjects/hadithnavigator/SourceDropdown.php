<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HadithNavigator;

use \IslamCompanionApi\DataObjects\Hadith as Hadith;

/**
 * This class implements the Hadith Source dropdown
 *
 * It contains functions used to generate the html for the Hadith Source dropdown
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
class SourceDropdown extends \Framework\Object\UiObject
{
    /**
     * Used to load the Hadith Source dropdown object with data
     *
     * It returns static data for displaying in html selectbox
     *
     * @since 1.0.0
     * @param array $data data used to read book title information from database    
     * hadith_book => the current hadith book
     * hadith_source => the current hadith source
     * hadith_language => the current hadith language
     */
    public function Read($data = "") 
    {
        /** The data is set to the objects local data property */
        $this->data = $data;
        /** The configuration object is fetched */
        $parameters['configuration'] = $this->GetConfigurationObject();
        /** The Hadith object is created */
        $hadith = new Hadith($parameters);
        /** The list of book titles in given book are fetched */
        $this->data['hadith_source_list'] = $hadith->GetHadithSourceNames();
    }
    /**
     * Used to display the Hadith Source dropdown
     *
     * It returns the html of the Hadith Source dropdown
     *
     * @since 1.0.0
     *
     * @return string $hadith_source_dropdown_html the html string for the Hadith Source dropdown
     */
    public function Display() 
    {
        /** The current hadith source name */
        $hadith_source = $this->data['hadith_source'];
        /** The path to the plugin template folder */
        $plugin_template_path = $this->GetConfig("path", "application_template_path");
        /** The options html is fetched */
        $template_parameters = array();
        /** The hadith source data is prepared */
        for ($count = 0;$count < count($this->data['hadith_source_list']);$count++) 
        {
            /** The information for single hadith source */
            $hadith_source_information = $this->data['hadith_source_list'][$count];
            /** Used to indicate if the current book should be selected in the dropdown */
            $selected = ($hadith_source_information ==  $hadith_source) ? 'SELECTED' : '';
            /** The title information is added to template parameters */
            $template_parameters[] = array(
                "text" => $hadith_source_information,
                "value" => $hadith_source_information,                
                "selected" => $selected
            );
        }
        /** The Hadith Title dropdown options are rendered using template parameters */
        $options_html = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "option.html", $template_parameters);
        /** The Ruku dropdown templates parameters */
        $template_parameters = array(
            "name" => "ic-hadith-source",
            "id" => "ic-hadith-source",
            "options" => $options_html,
            "title" => ""
        );
        /** The Hadith Source dropdown template is rendered using the template parameters */
        $hadith_source_dropdown_html = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "select.html", $template_parameters);
        
        return $hadith_source_dropdown_html;
    }
}

