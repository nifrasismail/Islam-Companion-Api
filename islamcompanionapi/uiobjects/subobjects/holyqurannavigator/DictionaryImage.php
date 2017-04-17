<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\Authors as Authors;

/**
 * This class implements the DictionaryImage class
 *
 * It contains functions used to generate the html for the dictionary image
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class DictionaryImage extends \Framework\Object\UiObject
{
    /**
     * Used to fetch the dictionary information
     *
     * It fetches the dictionary image source url and dictionary link
     *
     * @param array $data data containing language and translator
     *    language => string the current language
     */
    public function Read($data = "") 
    {
        /** The language */
        $language = $data['language'];
        /** The configuration object is fetched */
        $parameters['configuration'] = $this->GetConfigurationObject();
        /** The authors object is created */
        $authors = new Authors($parameters);
        /** The dictionary url and language rtl information are fetched */
        $dictionary_information = $authors->GetDictionaryInformation($language);
        /** The dictionary url */
        $this->data['dictionary_url'] = $dictionary_information['dictionary_url'];
        /** The rtl property of the language */
        $this->data['rtl'] = $dictionary_information['rtl'];
    }
    /**
     * Used to display the Dictionary Image
     *
     * It returns the html of the dictionary image
     *
     * @return string $dictionary_image_url the html string for the Dictionary Image
     */
    public function Display() 
    {
        /** The url to the template folder */
        $application_template_url = $this->GetConfig("path", "application_template_url");
        /** The dictionary image url */
        $dictionary_image_url = $application_template_url . "/images/dictionary.png";
        /** The dictionary image css class */
        $dictionary_class = "ic-cursor";
        /** The path to the template folder */
        $template_folder_path = $this->GetConfig("path", "application_template_path");
        /** The path to the dictionary url template file */
        $template_file_path = $template_folder_path . DIRECTORY_SEPARATOR . "dictionary_image.html";
        /** The template parameters */
        $template_parameters = array(
            "dictionary_class" => $dictionary_class,
            "dictionary_url" => $this->data['dictionary_url'],
            "image_src" => $dictionary_image_url
        );
        /** The html template is rendered using the given parameters */
        $dictionary_image_url = $this->GetComponent("template_helper")->RenderTemplateFile($template_file_path, $template_parameters);
        return $dictionary_image_url;
    }
}

