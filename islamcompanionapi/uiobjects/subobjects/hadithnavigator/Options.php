<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HadithNavigator;

/**
 * This class implements the Options class
 *
 * It contains functions used to generate the html for the Hadith options
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
     * Used to set the Hadith Options information
     *
     * It sets the Hadith Subscription options
     *
     * @param string $options the options to be displayed on the Hadith Navigator
     */
    public function Read($data = "") 
    {
        /** Data for Hadith Options section */
        $this->data = $data;
    }
    /**
     * Used to display the Hadith Options section
     *
     * It returns the html of the Hadith Options section
     *
     * @return string $hadith_options_html the html string for the Hadith Options section
     */
    public function Display() 
    {
        /** The list of options for the Hadith navigator */
        $options                      = explode(",", $this->data['options']);
        /** The required Hadith options */
        $hadith_options_html      = "";
        /** Each option is checked */
        for($count = 0; $count < count($options); $count++) {
            /** The navigator option value */
            $option_value             = $options[$count];
            /** The navigator option text */
            $option_text              = ucwords(str_replace("-", ", ", $option_value));
            /** The last ',' is replaced with 'and' */
            $option_text              = (strpos($option_text,",") !==false) ? substr_replace($option_text, " and", strrpos($option_text, ","), 1) : $option_text;
            /** Indicates if the option should be selected */
            $selected                 = ($option_value == "book-title") ? "SELECTED" : "";            
            /** The parameters for the navigator selectbox options */
            $parameters               = array("text" => $option_text, "value" => $option_value, "selected" => $selected);
            /** The selectbox is rendered */
            $hadith_options_html  .= $this->GetComponent("template")->Render("option", $parameters);
        }
        
        return $hadith_options_html;
    }
}

