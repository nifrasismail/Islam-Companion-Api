<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

/**
 * This class implements the Options class
 *
 * It contains functions used to generate the html for the Holy Quran options
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class Options extends \Framework\Object\UiObject
{
    /**
     * Used to set the Holy Quran Options information
     *
     * It sets the Holy Quran Subscription options
     *
     * @param string $options the options to be displayed on the Holy Quran Navigator
     */
    public function Read($data = "") 
    {
        /** Data for Holy Quran Options section */
        $this->data = $data;
    }
    /**
     * Used to display the Holy Quran Options section
     *
     * It returns the html of the Holy Quran Options section
     *
     * @return string $holy_quran_options_html the html string for the Holy Quran Options section
     */
    public function Display() 
    {
        /** The list of options for the Holy Quran navigator */
        $options                      = explode(",", $this->data['options']);
        /** The required Holy Quran options */
        $holy_quran_options_html      = "";
        /** Each option is checked */
        for($count = 0; $count < count($options); $count++) {
            /** The navigator option value */
            $option_value             = $options[$count];
            /** The navigator option text */
            $option_text              = ucwords(str_replace("-", ", ", $option_value));
            /** The last ',' is replaced with 'and' */
            $option_text              = (strpos($option_text,",") !==false) ? substr_replace($option_text, " and", strrpos($option_text, ","), 1) : $option_text;
            /** Indicates if the option should be selected */
            $selected                 = ($option_value == "sura-ruku-ayat") ? "SELECTED" : "";            
            /** The parameters for the navigator selectbox options */
            $parameters               = array("text" => $option_text, "value" => $option_value, "selected" => $selected);
            /** The selectbox is rendered */
            $holy_quran_options_html  .= $this->GetComponent("template")->Render("option", $parameters);
        }
        
        return $holy_quran_options_html;
    }
}

