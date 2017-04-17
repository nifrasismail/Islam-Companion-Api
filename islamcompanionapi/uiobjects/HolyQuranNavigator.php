<?php

namespace IslamCompanionApi\UiObjects;

use \IslamCompanionApi\DataObjects\HolyQuran as HolyQuran;
use \IslamCompanionApi\DataObjects\Authors as Authors;
use \IslamCompanionApi\DataObjects\Rukus as Rukus;
use \IslamCompanionApi\DataObjects\HolyQuranNavigation as HolyQuranNavigation;

/**
 * This class implements the HolyQuranNavigator class
 *
 * It contains functions used to generate the Holy Quran Navigator widget
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class HolyQuranNavigator extends \Framework\Object\UiObject
{
    /**
     * Used to get list of user interface objects of the Holy Quran Navigator
     *
     * It returns the user interface object details
     *
     * @return array $ui_objects the details of the user interface objects of the Holy Quran Navigator
     */
    private function GetUiObjectDetails() 
    {
        $ui_objects = array(
            "audioplayer" => array(
                "parameters" => array(
                    "sura" => $this->data['navigator_data']['sura'],
                    "ruku" => $this->data['navigator_data']['ruku']
                ) ,
                "template_parameter" => "audio_player",
            ) ,
            "divisionnumberdropdown" => array(
                "parameters" => array(
                    "division" => $this->data['navigator_data']['division'],
                    "division_number" => $this->data['navigator_data']['division_number']
                ) ,
                "template_parameter" => "division_select_box",
            ) ,
            "suradropdown" => array(
                "parameters" => array(
                    "division" => $this->data['navigator_data']['division'],
                    "division_number" => $this->data['navigator_data']['division_number'],
                    "sura" => $this->data['navigator_data']['sura'],
                    "ruku" => $this->data['navigator_data']['ruku']
                ) ,
                "template_parameter" => "sura_select_box",
            ) ,
            "rukudropdown" => array(
                "parameters" => array(
                    "division" => $this->data['navigator_data']['division'],
                    "division_number" => $this->data['navigator_data']['division_number'],
                    "sura" => $this->data['navigator_data']['sura'],
                    "ruku" => $this->data['navigator_data']['ruku']
                ) ,
                "template_parameter" => "ruku_select_box",
            ) ,
            "holyqurantext" => array(
                "parameters" => array(
                    "parameters" => array(
                        "sura" => $this->data['navigator_data']['sura'],
                        "ruku" => $this->data['navigator_data']['ruku'],
                        "language" => $this->data['navigator_data']['language'],
                        "narrator" => $this->data['navigator_data']['narrator'],
                        "layout" => $this->data['navigator_data']['layout'],
                        "tools" => $this->data['navigator_data']['tools'],                            
                     ) ,               
                 "user_interface" => "navigator"               
               ) ,
                "template_parameter" => "verse_text"
            ) ,          
            "holyquranmoreoptions" => array(
                "parameters" => array(
                    "type" => "Holy Quran",
                    "sura" => $this->data['navigator_data']['sura'],
                    "ruku" => $this->data['navigator_data']['ruku'],
                    "language" => $this->data['navigator_data']['language'],
                    "narrator" => $this->data['navigator_data']['narrator']
                ) ,
                "template_parameter" => "holy_quran_more_options"
            ) ,          
            "holyquransearchbox" => array(
                "parameters" => array(
                    "type" => "Holy Quran",
                    "language" => $this->data['navigator_data']['language'],
                    "narrator" => $this->data['navigator_data']['narrator']
                ) ,
                "template_parameter" => "search_box"
            ) ,          
            "holyquransettings" => array(
                "parameters" => array(
                    "type" => "Holy Quran",
                    "language" => $this->data['navigator_data']['language'],
                    "narrator" => $this->data['navigator_data']['narrator'],
                    "division" => $this->data['navigator_data']['division']                    
                ) ,
                "template_parameter" => "settings"
            ) ,          
            "holyquransubscription" => array(
                "parameters" => array(
                    "type" => "Holy Quran",
                    "language" => $this->data['navigator_data']['language'],
                    "narrator" => $this->data['navigator_data']['narrator']
                ) ,
                "template_parameter" => "subscription"
            ) ,          
            "holyqurannavigatoroptions" => array(
                "parameters" => array(
                    "type" => "Holy Quran",
                    "options" => $this->data['navigator_data']['options']
                ) ,
                "template_parameter" => "options"
            )
        );
        return $ui_objects;
    }
    /**
     * Used to load the Holy Quran Navigator with data
     *
     * It loads the data from database to the object
     *
     * @param array $data data used to read verse information from database
     *    language => string the language for the quran translation
     *    narrator => string the narrator for the quran translation
     *    sura => int the current sura number
     *    ruku => int the current sura ruku number
     *    division => string the current division
     *    division_number => int the current division number
     *    ayat => int the current sura ayat
     *    template => string [full~dashboard] the type of template. it controls the layout of the navigator
     *    action => string [next~previous~current~sura_box~ruku_box~division_number_box] the action performed by the user on the Holy Quran Navigator
     */
    public function Read($data = "") 
    {
        /**
         * The updated data is calculated based on user selection
         * e.g if user had selected division number dropdown then the sura, ruku and ayat information will change
         */
        $this->data['navigator_data'] = $this->GetUpdatedNavigatorData($data);
        /** The ui object names and their parameters */
        $this->data['ui_object_details'] = $this->GetUiObjectDetails($data);
        /** Each user interface object is loaded with data */
        foreach ($this->data['ui_object_details'] as $object_name => $object_details) 
        {
            /** The user interface object is fetched */
            $this->sub_items[$object_name] = $this->GetComponent($object_name);
            /** The user interface object is loaded with data */
            $this->sub_items[$object_name]->Read($object_details['parameters']);
        }
    }
    /**
     * Used to return the Navigator data
     *
     * It returns the data used to display the Navigator
     * This data can be considered as the state of the Holy Quran Navigator
     * By saving this data, the state of the Holy Quran Navigator is saved
     *
     * @return array $data data used to display the Holy Quran Navigator
     * it is an array with following keys:
     *    sura => the current sura number
     *    ruku => the current sura ruku number
     *    division number => the current division number
     *    ayat => the current sura ayat
     *    division => the current division
     *    narrator => the narrator for the quran translation
     *    language => the language for the quran translation
     *    layout => the type of layout. it controls the layout of the navigator     
     */
    public function GetNavigatorData() 
    {
        /** The sura id */
        $sura = $this->data['navigator_data']['sura'];
        /** The ruku id */
        $ruku = $this->data['navigator_data']['ruku'];
        /** The division number */
        $division_number = $this->data['navigator_data']['division_number'];
        /** The ayat */
        $ayat = $this->data['navigator_data']['ayat'];
        /** The division */
        $division = $this->data['navigator_data']['division'];
        /** The narrator */
        $narrator = $this->data['navigator_data']['narrator'];
        /** The language */
        $language = $this->data['navigator_data']['language'];
        /** The layout name */
        $layout = $this->data['navigator_data']['layout'];        
        /** The navigator state data */
        $data = array(
            "sura" => $sura,
            "ruku" => $ruku,
            "division_number" => $division_number,
            "ayat" => $ayat,
            "division" => $division,
            "narrator" => $narrator,
            "language" => $language,
            "layout" => $layout
        );
        
        return $data;
    }
    /**
     * Used to display the Holy Quran Navigator
     *
     * It returns the html of the Holy Quran Navigator
     *
     * @return string $holy_quran_navigator_html the html string for the Holy Quran Navigator
     */
    public function Display() 
    {    
        /** The navigator buttons css class */
        $navigator_buttons_css_class = ($this->GetComponent("authors")->GetLanguageRtl($this->data["navigator_data"]["language"])) ? "ic-navigator-class-rtl" : "ic-navigator-class-ltr";
        /** The rukus in the current division are fetched */
        $rukus_in_division           = $this->GetComponent("rukus")->GetRukusInDivision($this->data["navigator_data"]["sura"], $this->data["navigator_data"]["division"], $this->data["navigator_data"]["division_number"]);
        /** The last sura ruku number in the division */
        $last_ruku_number            = $rukus_in_division[count($rukus_in_division) - 1]['sura_ruku'];
        /** The maximum rukus in sura are fetched */
        $max_rukus                   = $this->GetComponent("rukus")->GetMaxRukus($this->data["navigator_data"]["sura"]);
        /** The total ruku information. It is the number of rukus in the current division */
        $total_rukus                 = ($this->data['navigator_data']['division'] == 'ruku') ? "of " . $max_rukus : "- " . $last_ruku_number;
        /** The start and end ayat are fetched */
        $ayat_information            = $this->GetComponent("rukus")->GetStartAndEndAyatOfRuku($this->data["navigator_data"]["ruku"], $this->data["navigator_data"]["sura"]);
        /** The division name */
        $division_name               = ($this->data['navigator_data']['division'] == "ruku") ? "" : ucfirst($this->data['navigator_data']['division']);
        /** The division class name */
        $division_class              = ($this->data['navigator_data']['division'] == "ruku") ? "division-class ic-hidden" : "division-class";
        $more_options_html = $this->GetComponent("template")->Render("image", array("id" => "ic-holy-quran-more-options", "src" => $this->GetConfig("path", "application_template_url") . "/images/more.png", "alt" => "More Options", "title" => "More Options", "css_class" => "ic-cursor", "onclick" => 'IC_Navigators.ToggleMoreOptions("show", "div.section-padding.holy-quran-more-options-section", "ic-holy-quran-more-options", "ic-holy-quran-less-options", "Holy Quran");'));
        $less_options_html = $this->GetComponent("template")->Render("image", array("id" => "ic-holy-quran-less-options", "src" => $this->GetConfig("path", "application_template_url") . "/images/less.png", "alt" => "Less Options", "title" => "Less Options", "css_class" => "ic-cursor ic-hidden", "onclick" => 'IC_Navigators.ToggleMoreOptions("hide", "div.section-padding.holy-quran-more-options-section", "ic-holy-quran-more-options", "ic-holy-quran-less-options", "Holy Quran");'));
        
        /** The dictionary url and language rtl information are fetched */
        $dictionary_information = $this->GetComponent("authors")->GetDictionaryInformation($this->data["navigator_data"]["language"]);
        $translation_dictionary_url_html = $this->GetComponent("template")->Render("hidden", array("id" => "ic-translated-dictionary-url", "name" => "ic-translated-dictionary-url", "value" => $dictionary_information['dictionary_url']));
        /** The dictionary url and language rtl information are fetched */
        $dictionary_information = $this->GetComponent("authors")->GetDictionaryInformation("Arabic");
        /** The navigator settings */
        $navigator_settings    = array("language" => $this->data["navigator_data"]["language"], "narrator" => $this->data["navigator_data"]["narrator"], "division" => $this->data["navigator_data"]["division"], "ayat" => $this->data["navigator_data"]["ayat"], "division_number" => $this->data["navigator_data"]["division_number"]);
        /** The navigator settings are encoded */
        $navigator_settings    = $this->GetComponent("encryption")->EncodeData($navigator_settings);
        $arabic_dictionary_url_html = $this->GetComponent("template")->Render("hidden", array("id" => "ic-arabic-dictionary-url", "name" => "ic-arabic-dictionary-url", "value" => $dictionary_information['dictionary_url']));
        /** The template parameters */
        $layout_select_html    = $this->GetComponent("template")->Render("quran_layout", array());
        /** The parameters used to render the Holy Quran Navigator Template */
        $template_parameters = array(
            "division_class" => $division_class,            
            "division_name" => $division_name,
            "total_rukus" => $total_rukus,
            "ayat_start" => $ayat_information['start_ayat'],
            "ayat_end" => $ayat_information['end_ayat'],
            "more_options_image" => $more_options_html,
            "less_options_image" => $less_options_html,            
            "navigator_class" => $navigator_buttons_css_class,
            "arabic_dictionary_url" => $arabic_dictionary_url_html,
            "translation_dictionary_url" => $translation_dictionary_url_html,
            "navigator_settings" => $navigator_settings,
            "layout" => $layout_select_html,
            "clipboard_text_name" => "ic_holy_quran_clipboard_text",
            "clipboard_text_id" => "ic_holy_quran_clipboard_text"
        );
        /** Each user interface object is displayed */
        foreach ($this->data['ui_object_details'] as $object_name => $object_details) 
        {
            /** The user interface object */
            $ui_object = $this->sub_items[$object_name];
            /** The user interface object html */
            $ui_object_html = $ui_object->Display();
            /** The name of the template parameter that will be replaced with html of the user interface object */
            $template_parameter_name = $object_details['template_parameter'];
            /** The user interface object html is added to the Holy Quran Navigator template parameters */
            $template_parameters[$template_parameter_name] = $ui_object_html;
        }
       
        /** The Holy Quran Navigator template is rendered using the template parameters */
        $holy_quran_navigator_html = $this->GetComponent("template_helper")->RenderTemplateFile($this->GetConfig("path", "application_template_path") . DIRECTORY_SEPARATOR . "quran_" . $this->data["navigator_data"]["template"] . ".html", $template_parameters);
        
        return $holy_quran_navigator_html;
    }
    /**
     * Used to get the updated navigator data
     *
     * It checks the value of action and generates the new Holy Quran Navigator meta data
     *
     * @param array $data data the current Holy Quran Navigator meta data
     *
     * @return array $updated_data the updated Holy Quran Navigator meta data
     */
    private function GetUpdatedNavigatorData($data) 
    {
        /** The updated data */
        $updated_data = array();
        /** The configuration object is fetched */
        $parameters['configuration'] = $this->GetConfigurationObject();
        /** The Navigation object is created */
        $navigation = new HolyQuranNavigation($parameters);
        /** If a sura was selected from the sura dropdown */
        if ($data['action'] == "sura_box") 
        {
            /** The updated data containing the new sura and ruku */
            $updated_data = $navigation->SuraSelection($data['sura'], $data['division_number'], $data['division']);
        }
        /** If a ruku was selected from the ruku dropdown */
        else if ($data['action'] == "ruku_box") 
        {
            /** The updated data containing the new ayat */
            $updated_data = $navigation->RukuSelection($data['ruku'], $data['sura']);
        }
        /** If a division number was selected from the division number dropdown */
        else if ($data['action'] == "division_number_box") 
        {
            /** The updated data containing the sura, ruku and ayat */
            $updated_data = $navigation->DivisionNumberSelection($data['division_number'], $data['division']);
        }
        /** If the next or previous button was clicked */
        else if ($data['action'] == "next" || $data['action'] == "previous") 
        {
            /** The updated data containing the sura, ruku and ayat */
            $updated_data = $navigation->NextPreviousSelection($data['ruku'], $data['division'], $data['action']);
        }
        /** The new ayat is set */
        $data['ayat'] = (isset($updated_data['ayat'])) ? $updated_data['ayat'] : $data['ayat'];
        /** The new ruku is set */
        $data['ruku'] = (isset($updated_data['ruku'])) ? $updated_data['ruku'] : $data['ruku'];
        /** The new sura is set */
        $data['sura'] = (isset($updated_data['sura'])) ? $updated_data['sura'] : $data['sura'];
        /** The new division number is set */
        $data['division_number'] = (isset($updated_data['division_number'])) ? $updated_data['division_number'] : $data['division_number'];
        /** The new division number is set if the current division is ruku */
        $data['division_number'] = ($data['division'] == 'ruku') ? $data['ruku'] : $data['division_number'];
        /** The updated data is set to data */
        $updated_data = $data;
        
        return $updated_data;
    }
}

