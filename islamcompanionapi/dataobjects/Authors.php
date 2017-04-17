<?php

namespace IslamCompanionApi\DataObjects;

/**
 * This class implements the Authors class
 *
 * An object of this class allows access to Holy Quran authors information
 * The author information can be fetched using criteria such as language and translator name
 *
 * @category   IslamCompanionApi
 * @package    DataObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class Authors extends \Framework\Object\DataObjectAbstraction
{
    /**
     * Used to get list of all the supported languages and translators
     *
     * It gets the names of all the languages and translators that are supported depending on the parameters
     * Only language or only translator or both language and translator information may be returned
     *
     * @param string [all~language~translator~language and translator~sura, language and translator] $option used to determine what data is required
     * @return array $languages_translators the list of all supported languages and/or translators
     */
    public function GetSupportedLanguagesAndTranslators($option) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "author",
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => false,
            "read_all" => true,
            "order" => array(
                "field" => "language",
                "type" => "string",
                "direction" => "ASC"
            )
        );
        /** The author information is read */
        $this->Read($parameters);
        /** The list of distinct languages and/or translators */
        $languages_translators = array();
        /** The author data */
        $author_data = $this->GetData();
        /** The distinct language information is fetched */
        for ($count = 0;$count < count($author_data);$count++) 
        {
            $data = $author_data[$count];
            if ($option == "language") 
            {
                if (!in_array($data['language'], $languages_translators)) $languages_translators[] = $data['language'];
            }
            else if ($option == "translator") 
            {
                if (!in_array($data['translator'], $languages_translators)) $languages_translators[] = $data['translator'];
            }
            else if (strpos($option, "language and translator") !== false || $option == "all") 
            {
                $languages_translators[] = array(
                    "language" => $data['language'],
                    "narrator" => $data['translator']
                );
            }
        }
        return $languages_translators;
    }
    /**
     * Used to determine if the given language is right to left or left to right
     *
     * It gets the value of right to left or left to right for the given language
     *
     * @param string $language the given language
     *
     * @return boolean $rtl the rtl property of the language. it is true if the language is right to left
     */
    public function GetLanguageRtl($language) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "author",
            "key_field" => "language"
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $language,
            "read_all" => false
        );
        /** The author information is read */
        $this->Read($parameters);
        /** The author data */
        $author_data = $this->GetData();
        /** The css class for the given language */
        $rtl = $author_data['rtl'];
        return $rtl;
    }    
    /**
     * Used to check if the given translator is valid
     *
     * It checks if the value of the given translator is valid
     *
     * @param string $translator the given translator
     *
     * @return boolean $is_valid used to indicate if the given translator is valid
     */
    public function IsTranslatorValid($translator) 
    {
        /** Used to indicate if the given translator is valid */
        $is_valid = false;
        /** The list of all supported translators is fetched */
        $translator_list = $this->GetSupportedLanguagesAndTranslators("translator");
        /** Each translator is checked */
        for ($count = 0;$count < count($translator_list);$count++) 
        {
            /** The name of a translator */
            $translator_name = $translator_list[$count];
            /** If the translator name matches the given translator, then is_valid is set to true */
            $is_valid = true;
        }
        return $is_valid;
    }
    /**
     * Used to check if the given language is valid
     *
     * It checks if the value of the given language is valid
     *
     * @param string $language the given language
     *
     * @return boolean $is_valid used to indicate if the given language is valid
     */
    public function IsLanguageValid($language) 
    {
        /** Used to indicate if the given translator is valid */
        $is_valid = false;
        /** The list of all supported translators is fetched */
        $language_list = $this->GetSupportedLanguagesAndTranslators("language");
        /** Each language is checked */
        for ($count = 0;$count < count($language_list);$count++) 
        {
            /** The name of a language */
            $language_name = $language_list[$count];
            /** If the language name matches the given language, then is_valid is set to true */
            $is_valid = true;
        }
        return $is_valid;
    }
    /**
     * Used to get the translator information
     *
     * It gets the information about the given translator and language
     *
     * @param string $language the language for which the css class is required
     *
     * @param array $translator_information information about the given translator and language
     * file_name => name of the text file containing the Holy Quran translation
     * name => non english name of the translator
     * translator => english name of the translator
     * language => the translation language
     * file_id => the id of the translation file
     * last_update => the date of last update of the translation
     * source => the source for the translation
     * rtl => used to indicate if the language is right to left or left to right
     * css_attributes => the css attributes for the displaying the verses in points
     * dictionary_url => the url for the online dictionary that can be used to find the meaning of the words
     */
    public function GetTranslationInformation($language) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "author",
            "key_field" => "language"
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The where clause for fetching author meta information */
        $where_clause[0]['field'] = "language";
        $where_clause[0]['value'] = $language;
        $where_clause[0]['operation'] = '=';
        $where_clause[0]['operator'] = '';
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $where_clause,
            "read_all" => false
        );
        /** The author information is read */
        $this->Read($parameters);
        /** The author data */
        $translator_information = $this->GetData();        
        
        return $translator_information;
    }
    /**
     * Used to get the dictionary url
     *
     * It gets the dictionary url for the given language
     *
     * @param string $language the language for which the dictionary url is required
     *
     * @param array $dictionary_information the required dictionary information is returned
     *    rtl => boolean the rtl value
     *    dictionary_url => string the dictionary url is returned     
     */
    public function GetDictionaryInformation($language) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "author",
            "key_field" => "language"
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The where clause for fetching author meta information */
        $where_clause[0]['field'] = "language";
        $where_clause[0]['value'] = $language;
        $where_clause[0]['operation'] = '=';
        $where_clause[0]['operator'] = '';
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "dictionary_url, rtl",
            "condition" => $where_clause,
            "read_all" => false
        );
        /** The dictionary url is read */
        $this->Read($parameters);
        /** The dictionary url */
        $dictionary_information = $this->GetData();
        
        return $dictionary_information;
    }
}

