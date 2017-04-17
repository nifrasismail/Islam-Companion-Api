<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\Authors as Authors;
use \IslamCompanionApi\DataObjects\Rukus as Rukus;

/**
 * This class implements the HolyQuranShortcodeImage class
 *
 * It contains functions used to generate the html for the shortcode image
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class HolyQuranShortcodeImage extends \Framework\Object\UiObject
{
    /**
     * Used to fetch the shortcode information
     *
     * It fetches the shortcode image source url and shortcode link
     *
     * @param array $data data containing language and translator
     *    language => string the current language
     *    narrator => string the current narrator
     *    sura => string the current translator
     *    ruku => int the ruku id
     */
    public function Read($data = "") 
    {
        /** The language */
        $this->data['language'] = $data['language'];
        /** The narrator */
        $this->data['narrator'] = $data['narrator'];
        /** The sura */
        $this->data['sura'] = $data['sura'];
        /** The ruku */
        $ruku = $data['ruku'];
        /** The configuration object is fetched */
        $parameters['configuration'] = $this->GetConfigurationObject();
        /** The Rukus object is created  */
        $rukus = new Rukus($parameters);
        /** The authors object is created */
        $authors = new Authors($parameters);
        /** The rtl attribute of the language */
        $rtl = $authors->GetLanguageRtl($this->data["language"]);
        /** The start and end ayat for the ruku */
        $ayat_information = $rukus->GetStartAndEndAyatOfRuku($ruku);
        /** The author translator information is fetched */
        $translation_information = $authors->GetTranslationInformation($this->data['narrator'], $this->data['language']);
        /** The file name containing the verse text */
        $file_name = $translation_information['file_name'];
        /** The css attributes for the language */
        $this->data['css_attributes'] = $translation_information['css_attributes'];
        /** The start aya */
        $this->data['start_aya'] = $ayat_information['start_ayat'];
        /** The end aya */
        $this->data['end_aya'] = $ayat_information['end_ayat'];
        /** The container tag */
        $this->data['container'] = 'ordered list';
        /** The css class */
        $this->data['css_class'] = ($rtl) ? 'ic-rtl-text' : 'ic-ltr-text';
        /** The audio player */
        $this->data['audio_player'] = 'yes';
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
        $shortcode_string = base64_encode('[get-verses narrator="' . $this->data['narrator'] . '" 
		                                               language="' . $this->data['language'] . '"
		                                               sura="' . $this->data['sura'] . '"
		                                               start_ayat="' . $this->data['start_aya'] . '"
		                                               end_ayat="' . $this->data['end_aya'] . '"
		                                               container="' . $this->data['container'] . '"
		                                               css_class="' . $this->data['css_class'] . '" 
		                                               audio_player="' . $this->data['audio_player'] . '"]');
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

