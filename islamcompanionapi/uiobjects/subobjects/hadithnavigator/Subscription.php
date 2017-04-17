<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HadithNavigator;

use \IslamCompanionApi\DataObjects\Authors as Authors;
use \IslamCompanionApi\DataObjects\Rukus as Rukus;

/**
 * This class implements the Subscription class
 *
 * It contains functions used to generate the html for the Hadith Subscription template
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
     * Used to set the Hadith Subscription information
     *
     * It sets the Hadith Subscription options
     *
     * @param array $data the data used to render the Hadith Subscription section
     *    language => string the current language
     *    narrator => string the current narrator
     */
    public function Read($data = "") 
    {
        /** Data for Hadith Subscription section */
        $this->data = $data;
    }
    /**
     * Used to display the Hadith Subscription section
     *
     * It returns the html of the Hadith Subscription section
     *
     * @return string $hadith_subscription_html the html string for the Hadith Subscription section
     */
    public function Display() 
    {
        /** The hadith count options */
        $hadith_count_options = array("1 hadith", "2 hadith", "3 hadith", "4 hadith", "5 hadith", "6 hadith", "7 hadith", "8 hadith", "9 hadith", "10 hadith", "11 hadith", "12 hadith", "13 hadith", "14 hadith", "15 hadith", "16 hadith", "17 hadith", "18 hadith", "19 hadith", "20 hadith");
        /** The parameters used to render the hadith count select box */
        $parameters = array(
          "selectbox_name" => "ic-hadith-subscription-hadith-count",
          "selectbox_id" => "ic-hadith-subscription-hadith-count",
          "selectbox_selected_value" => '1 Ruku',
          "selectbox_onchange" => "",
          "selectbox_options" => $hadith_count_options
        );
        /** The selectbox is rendered */
        $hadith_count_select_html = $this->GetComponent("template")->Render("selectbox", $parameters);        
        /** The default subscription times */
        $default_subscription_times = "";        
        /** The template parameters */
        $template_parameters = array(
            "subscription_times" => $default_subscription_times,
            "subscription_hadith_count" => $hadith_count_select_html
        );
        /** The html template is rendered using the given parameters */
        $hadith_subscription_html = $this->GetComponent("template")->Render("hadith_subscription", $template_parameters);
        
        return $hadith_subscription_html;
    }
}

