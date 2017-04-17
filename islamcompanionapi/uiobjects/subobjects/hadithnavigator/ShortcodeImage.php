<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HadithNavigator;

/**
 * This class implements the HadithShortcodeImage class
 *
 * It contains functions used to generate the html for the shortcode image
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class HadithShortcodeImage extends \Framework\Object\UiObject
{
    /**
     * Used to fetch the shortcode information
     *
     * It fetches the shortcode image source url and shortcode link
     *
     * @param array $data data containing language and translator
     *    hadith_book => the current hadith book
     *    hadith_source => the current hadith source
     *    hadith_language => the current hadith language
     *    hadith_title => the current hadith title
     */
    public function Read($data = "") 
    {
        /** The hadith language */
        $this->data['hadith_language'] = $data['hadith_language'];
        /** The hadith book */
        $this->data['hadith_book'] = $data['hadith_book'];
        /** The hadith source */
        $this->data['hadith_source'] = $data['hadith_source'];
        /** The hadith title */
        $this->data['hadith_title'] = $data['hadith_title'];
    }
    /**
     * Used to display the Shortcode Image
     *
     * It returns the html of the Shortcode image
     *
     * @return string $shortcode_image_url the html string for the Shortcode Image
     */
    public function Display() 
    {
        /** The url to the template folder */
        $application_template_url = $this->GetConfig("path", "application_template_url");
        /** The shortcode image url */
        $shortcode_image_url = $application_template_url . "/images/shortcode.png";
        /** The path to the template folder */
        $template_folder_path = $this->GetConfig("path", "application_template_path");
        /** The path to the shortcode url template file */
        $template_file_path = $template_folder_path . DIRECTORY_SEPARATOR . "shortcode_image.html";
        /** The shortcode string is set */
        $shortcode_string = base64_encode('[get-hadith hadith_language="' . $this->data['hadith_language'] . '" 
		                                       hadith_book="' . $this->data['hadith_book'] . '"
		                                       hadith_source="' . $this->data['hadith_source'] . '"
		                                       hadith_title="' . $this->data['hadith_title'] . '"
		                                       hadith_number_start="-1"
		                                       css_class="ic-ltr-text" 
		                                       hadith_number_end="-1"]
		                         ');
        /** The template parameters */
        $template_parameters = array(
            "css_class" => "ic-cursor",
            "image_src" => $shortcode_image_url,
            "shortcode_string" => $shortcode_string
        );
        /** The html template is rendered using the given parameters */
        $shortcode_image_url = $this->GetComponent("template_helper")->RenderTemplateFile($template_file_path, $template_parameters);
        return $shortcode_image_url;
    }
}

