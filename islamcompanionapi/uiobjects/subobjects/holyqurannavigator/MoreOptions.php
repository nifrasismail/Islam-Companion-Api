<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\Authors as Authors;
use \IslamCompanionApi\DataObjects\Rukus as Rukus;

/**
 * This class implements the MoreOptions class
 *
 * It contains functions used to generate the html for the More Options template
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class MoreOptions extends \Framework\Object\UiObject
{
    /**
     * Used to set the Holy Quran More Options information
     *
     * It sets the more options for Holy Quran navigator
     *
     * @param array $data the data used to render the more options section
     *    language => string the current language
     *    narrator => string the current narrator
     *    sura => string the current translator
     *    ruku => int the ruku id
     */
    public function Read($data = "") 
    {
        /** Data for Holy Quran more options */
        $this->data = $data;
    }
    /**
     * Used to display the More Options Image
     *
     * It returns the html of the More Options image
     *
     * @return string $more_options_html the html string for the More Options section
     */
    public function Display() 
    {
        /** The url to the template folder */
        $application_template_url = $this->GetConfig("path", "application_template_url");
        /** The less options image url */
        $less_options_image_url = $application_template_url . "/images/less.png";
        /** The search button image url */
        $search_button_image_url = $application_template_url . "/images/search.png";      
        /** The template parameters */
        $template_parameters = array(
            "id" => "ic-holy-quran-search",
            "src" => $search_button_image_url,
            "alt" => "Search",
            "css_class" => "ic-cursor",
            "title" => "Search",
            "onclick" => 'IC_Holy_Quran_Dashboard_Widget.SearchVerseData("1");'
        );
        /** Search button image html */
        $search_button_image_html = $this->GetComponent("template")->Render("image", $template_parameters);
        /** The translation information for the language and author */
        $language_data = $this->GetComponent("authors")->GetTranslationInformation($this->data['language']);
        /** The css class for the searchbox text */
        $css_class = ($language_data['rtl']) ? "searchbox-text-rtl" : "searchbox-text-ltr";
        /** The css attributes are appended to the css class for the search box */
        $css_class .= " " . $language_data['css_attributes'];
        /** The template parameters */
        $template_parameters = array(
            "more_options_id" => "ic-holy-quran-more-options-container",
            "searchbox_name" => "ic-holy-quran-searchbox",
            "searchbox_id" => "ic-holy-quran-searchbox",
            "css_class" => $css_class,
            "search_button_image_html" => $search_button_image_html
        );
        /** The html template is rendered using the given parameters */
        $more_options_html = $this->GetComponent("template")->Render("more_options", $template_parameters);
        
        return $more_options_html;
    }
}

