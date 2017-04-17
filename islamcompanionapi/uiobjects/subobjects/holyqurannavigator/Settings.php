<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\Authors as Authors;
use \IslamCompanionApi\DataObjects\Rukus as Rukus;

/**
 * This class implements the Settings class
 *
 * It contains functions used to generate the html for the Holy Quran Settings template
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class Settings extends \Framework\Object\UiObject
{
    /**
     * Used to set the Holy Quran Settings information
     *
     * It sets the Holy Quran Settings options
     *
     * @param array $data the data used to render the HolyQuranSettings section
     *    language => string the current language
     *    narrator => string the current narrator
     *    division => string the current division     
     */
    public function Read($data = "") 
    {
        /** Data for Holy Quran Settings section */
        $this->data = $data;
    }
    /**
     * Used to display the Search image
     *
     * It returns the html of the Search image
     *
     * @return string $holy_quran_settings_html the html string for the Holy Quran Settings section
     */
    public function Display() 
    {
        /** The Authors class object is created */
        $authors = $this->GetComponent("authors");
        /** The list of supported languages and/or translators is fetched */
        $languages_narrators = $authors->GetSupportedLanguagesAndTranslators("sura, language and translator");
        /** The list of distinct languages */
        $language_list = array();
        /** The list of narrators for each language */
        $narrator_language_list = array();
        /** Each language and narrator is checked */
        for ($count = 0; $count < count($languages_narrators); $count++) {
            /** Single language and its narrator */
            $data = $languages_narrators[$count];
            /** The narrator */
            $narrator = $data['narrator'];
            /** The language */
            $language = $data['language'];
            /** If the language has not be added then it is added */
            if (!in_array($language, $language_list)) $language_list[] = $language;
            /** If the language has not been added then it is initialized */
            if (!isset($narrator_language_list))$narrator_language_list[$language] = array();
            /** The narrator is added for the language */
            $narrator_language_list[$language][] = $narrator;
        }
        /** The parameters used to render the language select box */
        $parameters = array(
          "selectbox_name" => "ic-holy-quran-settings-language", 
          "selectbox_id" => "ic-holy-quran-settings-language",
          "selectbox_selected_value" => $this->data['language'],
          "selectbox_onchange" => "holy_quran_navigator_object.SelectLanguage()",
          "selectbox_options" => $language_list
        );
        /** The selectbox is rendered */
        $selectbox_language_html = $this->GetComponent("template")->Render("selectbox", $parameters);
        /** The parameters used to render the narrator select box */
        $parameters = array(
          "selectbox_name" => "ic-holy-quran-settings-narrator", 
          "selectbox_id" => "ic-holy-quran-settings-narrator",
          "selectbox_selected_value" => $this->data['narrator'],
          "selectbox_onchange" => "holy_quran_navigator_object.SelectNarrator()",
          "selectbox_options" => $narrator_language_list[$this->data['language']]
        );
        /** The selectbox is rendered */
        $selectbox_narrator_html = $this->GetComponent("template")->Render("selectbox", $parameters);
        /** The parameters used to render the division select box */
        $parameters = array(
          "selectbox_name" => "ic-holy-quran-settings-division", 
          "selectbox_id" => "ic-holy-quran-settings-division",
          "selectbox_selected_value" => $this->data['division'],
          "selectbox_onchange" => "holy_quran_navigator_object.SelectDivision()",
          "selectbox_options" => array(array("value" => "hizb", "text" => "Hizb"), array("value" => "juz", "text" => "Juz"), array("value" => "page", "text" => "Page"), array("value" => "manzil", "text" => "Manzil"),  array("value" => "ruku", "text" => "Ruku"))
        );
        /** The selectbox is rendered */
        $selectbox_division_html = $this->GetComponent("template")->Render("selectbox", $parameters);
        /** The encoded meta data */
        $encoded_narrator_language_list = $this->GetComponent("encryption")->EncodeData($narrator_language_list);
        /** The template parameters */
        $template_parameters = array(
            "language" => $selectbox_language_html,
            "narrator" => $selectbox_narrator_html,
            "division" => $selectbox_division_html,
            "meta_data" => $encoded_narrator_language_list
        );
        /** The html template is rendered using the given parameters */
        $holy_quran_settings_html = $this->GetComponent("template")->Render("quran_settings", $template_parameters);
        
        return $holy_quran_settings_html;
    }
}

