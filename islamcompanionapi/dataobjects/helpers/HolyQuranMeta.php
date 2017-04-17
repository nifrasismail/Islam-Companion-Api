<?php

namespace IslamCompanionApi\DataObjects\Helpers;

/**
 * It provides functions for fetching Holy Quran meta data
 *
 * It provides meta data such as narrator, language, sura meta data and ayat meta data
 *
 * @category   IslamCompanionApi
 * @package    DataObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait HolyQuranMeta
{
    /**
     * Used to get the translation information
     *
     * It returns the translation information for the given narrator and language
     *
     * @param string $language the language string
     *
     * @return array $language_data the language data
     *    language_code => string the language code
     *    rtl => boolean indicates if the language is right to left or left to right
     *    css_class => string the css class for the verse text
     *    css_attributes => string the css attributes for the language list items
     */
    public function GetLanguageInformation($language) 
    {
        /** The language data */
        $language_data                     = array();
        /** The author translator information is fetched */
        $translation_information           = $this->GetComponent("authors")->GetTranslationInformation($language);
        /** The file name containing the verse text */
        $file_name                         = $translation_information['file_name'];
        /** The language code is extracted from file id */
        list($language_code, $author_code) = explode(".", $translation_information['file_id']);
        /** The language code is set */
        $language_data['language_code']    = $language_code;
        /** The rtl attribute of the language */
        $language_data['rtl']              = $this->GetComponent("authors")->GetLanguageRtl($language);
        /** The verse text css class */
        $language_data['css_class']        = ($language_data['rtl']) ? "ic-rtl-text" : "ic-ltr-text";
        /** The css attributes for the language are set */
        $language_data['css_attributes']   = $translation_information['css_attributes'];
        
        return $language_data;
    }
}

