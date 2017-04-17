<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HadithNavigator;

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
     *    hadith_book => the current hadith book
     *    hadith_source => the current hadith source
     *    hadith_language => the current hadith language
     *    hadith_title => the current hadith title
     */
    public function Read($data = "") 
    {
        /** Data for Hadith more options */
        $this->data = $data;
        /** The language option is set */
        $this->data['language'] = $data['hadith_language'];
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
            "id" => "ic-hadith-search",
            "src" => $search_button_image_url,
            "alt" => "Search",
            "css_class" => "ic-cursor",
            "title" => "Search",
            "onclick" => 'IC_Hadith_Dashboard_Widget.SearchHadithData("1");'
        );
        /** Search button image html */
        $search_button_image_html = $this->GetComponent("template")->Render("image", $template_parameters);
        /** The template parameters */
        $template_parameters = array(
            "more_options_id" => "ic-hadith-more-options-container",        
            "searchbox_name" => "ic-hadith-searchbox",
            "searchbox_id" => "ic-hadith-searchbox",  
            "css_class" => "",
            "search_button_image_html" => $search_button_image_html
        );
        /** The html template is rendered using the given parameters */
        $more_options_html = $this->GetComponent("template")->Render("more_options", $template_parameters);
        
        return $more_options_html;
    }
}

