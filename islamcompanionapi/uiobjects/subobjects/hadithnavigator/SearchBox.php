<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HadithNavigator;

use \IslamCompanionApi\DataObjects\Authors as Authors;
use \IslamCompanionApi\DataObjects\Rukus as Rukus;

/**
 * This class implements the SearchBox class
 *
 * It contains functions used to generate the html for the SearchBox template
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class SearchBox extends \Framework\Object\UiObject
{
    /**
     * Used to set the Searchbox information
     *
     * It sets the searchbox options
     *
     * @param array $data the data used to render the searchbox section
     *    language => string the current language
     *    narrator => string the current narrator
     */
    public function Read($data = "") 
    {
        /** Data for searchbox section */
        $this->data = $data;
    }
    /**
     * Used to display the Search image
     *
     * It returns the html of the Search image
     *
     * @return string $searchbox_html the html string for the Searchbox section
     */
    public function Display() 
    {
        /** The url to the template folder */
        $application_template_url = $this->GetConfig("path", "application_template_url");        
        /** The search button image url */
        $search_button_image_url = $application_template_url . "/images/search.png";
        /** The searchbox element type */
        $searchbox_type = str_replace(" ", "-", strtolower($this->data['type']));
        /** The template parameters */
        $template_parameters = array(
            "id" => "ic-" . $searchbox_type . "-search-button",
            "src" => $search_button_image_url,
            "alt" => "Search",
            "css_class" => "ic-cursor",
            "title" => "Search",
            "onclick" => ($searchbox_type == 'holy-quran') ? 'holy_quran_navigator_object.SearchVerseData("1");' : 'hadith_navigator_object.SearchHadithData("1");'
        );
        /** Search button image html */
        $search_button_image_html = $this->GetComponent("template")->Render("image", $template_parameters);
        /** The translation information for the language and author */
        $language_data = $this->GetComponent("authors")->GetTranslationInformation($this->data['language']);
        /** The css class for the searchbox text */
        $css_class = ($language_data['rtl']) ? "ic-navigator-class-rtl" : ".ic-navigator-class-ltr";
        /** The css attributes are appended to the css class for the search box */
        $css_class .= " " . $language_data['css_attributes'];
        /** The template parameters */
        $template_parameters = array(
            "searchbox_container_id" => "ic-" . $searchbox_type . "-search-box-container",
            "searchbox_id" => "ic-" . $searchbox_type . "-search-box",
            "css_class" => $css_class,
            "search_button_image_html" => $search_button_image_html
        );
        /** The html template is rendered using the given parameters */
        $searchbox_html = $this->GetComponent("template")->Render("search_box", $template_parameters);
        
        return $searchbox_html;
    }
}

