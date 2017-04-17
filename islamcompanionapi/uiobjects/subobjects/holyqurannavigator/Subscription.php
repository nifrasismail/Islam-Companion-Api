<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\Authors as Authors;
use \IslamCompanionApi\DataObjects\Rukus as Rukus;

/**
 * This class implements the Subscription class
 *
 * It contains functions used to generate the html for the Holy Quran Subscription template
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class Subscription extends \Framework\Object\UiObject
{
    /**
     * Used to set the Holy Quran Subscription information
     *
     * It sets the Holy Quran Subscription options
     *
     * @param array $data the data used to render the Holy Quran Subscription section
     *    language => string the current language
     *    narrator => string the current narrator
     */
    public function Read($data = "") 
    {
        /** Data for Holy Quran Subscription section */
        $this->data = $data;
    }
    /**
     * Used to display the Holy Quran Subscription section
     *
     * It returns the html of the Holy Quran Subscription section
     *
     * @return string $holy_quran_subscription_html the html string for the Holy Quran Subscription section
     */
    public function Display() 
    {
        /** The ayat count options */
        $ayat_count_options = array("1 ayat", "2 ayat", "3 ayat", "4 ayat", "5 ayat", "6 ayat", "7 ayat", "8 ayat", "9 ayat", "10 ayat", "11 ayat", "12 ayat", "13 ayat", "14 ayat", "15 ayat", "16 ayat", "17 ayat", "18 ayat", "19 ayat", "20 ayat", "1 Ruku");
        /** The parameters used to render the ayat count select box */
        $parameters = array(
          "selectbox_name" => "ic-holy-quran-subscription-ayat-count",
          "selectbox_id" => "ic-holy-quran-subscription-ayat-count",
          "selectbox_selected_value" => '1 Ruku',
          "selectbox_onchange" => "",
          "selectbox_options" => $ayat_count_options
        );
        /** The selectbox is rendered */
        $ayat_count_select_html = $this->GetComponent("template")->Render("selectbox", $parameters);        
        /** The default subscription times */
        $default_subscription_times = "";
        
        /** The template parameters */
        $template_parameters = array(
            "subscription_times" => $default_subscription_times,
            "subscription_ayat_count" => $ayat_count_select_html
        );
        /** The html template is rendered using the given parameters */
        $holy_quran_subscription_html = $this->GetComponent("template")->Render("quran_subscription", $template_parameters);
        
        return $holy_quran_subscription_html;
    }
}

