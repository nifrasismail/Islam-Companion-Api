<?php

namespace IslamCompanionApi;

/**
 * This is the main application class
 * It implements all controller actions of the application
 *
 * It is used to implement all the controller actions of the application
 *
 * @category   IslamCompanionApi
 * @package    IslamCompanionApi
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
trait HolyQuranApi
{
    /**
     * This function is used to get the Holy Quran Navigator widget
     * It generates the frontend for the widget
     *
     * It generates the Holy Quran Navigator using the given parameters
     * It returns the html and state information for the Holy Quran Navigator
     * {@internal context any}
     *
     * @param array $parameters the parameters for the function
     *    language => string [custom] the language for the quran translation
     *    narrator => string [custom] the narrator for the quran translation
     *    sura => int [custom] the current sura number
     *    ruku => int [custom] the current sura ruku number
     *    division => string [manzil~juz~hizb~ruku] the current division
     *    division_number => int [custom] the current division number
     *    ayat => int [custom] the current sura ayat
     *    template => string [plain~website~dashboard] the html template to use for the navigator  
     *    tools => string [custom] the tools to use for the navigator. for example: "copy", "dictionary links", "shortcode", "scroll to top", "highlight text"
     *    options => string [custom] the option provided by the navigator. for example: search,audio,layout,subscription,sura-ruku-ayat,language-narrator-division 
     *    layout => string [paragraph~double column] the type of layout. it controls the layout of the navigator
     *    action => string [next~previous~current~sura_box~ruku_box~division_number_box] the action performed by the user on the Holy Quran Navigator
     *
     * @return array $holy_quran_navigator_data the data used to display the Holy Quran Navigator
     *    html => string the holy quran navigator html
     *    state => array $state the holy quran navigator state data
     *        sura => int [custom] the current sura number
     *        ruku => int [custom] the current sura ruku number
     *        division_number => int [custom] the current division number
     *        ayat => int [custom] the current sura ayat
     *        division => string [manzil~juz~hizb~ruku] the current division
     *        narrator => string [custom] the narrator for the quran translation
     *        language => string [custom] the language for the quran translation
     *        layout => string [paragraph~double column] the type of layout. it controls the layout of the navigator
     *
     */
    public function HandleGetHolyQuranNavigator($parameters) 
    {
        /** The narrator data is decoded */
        $parameters['parameters']['narrator'] = urldecode(urldecode($parameters['parameters']['narrator']));
        /** The cached data is fetched */
        $holy_quran_navigator_data = $this->GetCachedData("HandleGetHolyQuranNavigator", $parameters);
        /** If the cached data was found then it is returned */
        if ($holy_quran_navigator_data !== false) return $holy_quran_navigator_data;
        /** The Holy Quran Navigator object is fetched and loaded with data */
        $this->GetComponent("holyqurannavigator")->Read($parameters['parameters']);
        /** The Holy Quran Navigator object is displayed */
        $holy_quran_navigator_html = $this->GetComponent("holyqurannavigator")->Display();
        /** The Holy Quran Navigator data is fetched */
        $navigator_data = $this->GetComponent("holyqurannavigator")->GetNavigatorData();
        
        $holy_quran_navigator_data = array(
            "html" => $holy_quran_navigator_html,
            "state" => $navigator_data
        );
        
        /** The cached data is saved to database */
        $this->SaveDataToCache("HandleGetHolyQuranNavigator", $parameters, $holy_quran_navigator_data);
        
        return $holy_quran_navigator_data;
    }    
    /**
     * Used to fetch Holy Quran verses
     * This function is used as the handler function for the shortcode get_verse_text
     *
     * It fetches Holy Quran verses for the given parameters
     * {@internal context any}
     *
     * @param array $parameters the parameters for the function
     *    narrator => string the narrator for the translation
     *    language => string the language for the translation
     *    ayas => string the list of ayas in sura. for example: 1:1, 1:2
     *    container => string [plain text~list] the layout for the verse text
     *    transformation => string [none~random~slideshow] the transformation for the text
     *    css_classes => string the css classes for the container elements
     *
     * @return string $verse_text
     */
    public function HandleGetVerseText($parameters) 
    {
        /** The narrator data is decoded */
        $parameters['parameters']['narrator']    = $parameters['parameters']['narrator'];
        /** The cached data is fetched */
        $verse_text                              = $this->GetCachedData("HandleGetVerseText", $parameters);
        /** If the cached data was found then it is returned */
        if ($verse_text !== false) return $verse_text;
        
        /** The parameters for reading the verse text */
        $parameters                              = array("parameters" => $parameters['parameters'], "user_interface" => $parameters['parameters']['container']);
        /** The Holy Quran text object is loaded with data */
        $this->GetComponent("holyqurantext")->Read($parameters);
        /** The Holy Quran text object is displayed with given layout */
        $verse_text                              = $this->GetComponent("holyqurantext")->Display();
        
        /** The cached data is saved to database */
        $this->SaveDataToCache("HandleGetVerseText", $parameters, $verse_text);
        
        return $verse_text;
    }
    /**
     * Used to search Holy Quran verses
     * This function is used to search Holy Quran verse text
     *
     * It fetches Holy Quran verses that match the given search parameters
     * {@internal context any}
     *
     * @param array $parameters the parameters for the function
     *    narrator => string the narrator for the translation
     *    language => string the language for the translation
     *    search_text => string the search text    
     *    layout => string [plain~navigator] the layout to use for formatting the search results
     *    page_number => int the page number of the search results
     *
     * @return string $holy_quran_search_results the data used to display the Holy Quran search results
     */
    public function HandleGetHolyQuranSearchResults($parameters) 
    {
        /** The narrator data is decoded */
        $parameters['parameters']['narrator']    = urldecode(urldecode($parameters['parameters']['narrator']));
        /** The search text data is decoded */
        $parameters['parameters']['search_text'] = urldecode(urldecode($parameters['parameters']['search_text']));
        /** The cached data is fetched */
        $verse_text                              = $this->GetCachedData("HandleGetHolyQuranSearchResults", $parameters);
        /** If the cached data was found then it is returned */
        if ($verse_text !== false) return $verse_text;
        
        /** The parameters for reading the verse text */
        $parameters                              = array("parameters" => $parameters['parameters'], "user_interface" => "search results");
        /** The Holy Quran text object is loaded with data */
        $this->GetComponent("holyqurantext")->Read($parameters);
        /** The Holy Quran text object is displayed as search results */
        $holy_quran_search_results               = $this->GetComponent("holyqurantext")->Display();       
        /** The cached data is saved to database */
        $this->SaveDataToCache("HandleGetHolyQuranSearchResults", $parameters, $holy_quran_search_results);
        
        return $holy_quran_search_results;
    }  
    /**
     * Used to search Ayat text for email subscribers
     * This function is used to search Ayat text
     *
     * It fetches Ayat text that match the given search parameters
     * {@internal context any}
     *
     * @param array $parameters the parameters for the function
     *    language => string the language for the translation
     *    narrator => string the narrator for the translation     
     *    search_text => string the search text    
     *    number_of_results => int the number of search results
     *    result_type => string [ayat~ruku] indicates if ruku should be fetched or ayas
     *    start => int the position from where the search results should be returned
     *    order => string [sequence~random] the order of the search results
     *    times => string the times at which the verse text should be sent
     *    email_address => string the email addresses at which the email should be sent     
     *
     * @return array $ayat_subscriber_data the data requested by the subscriber
     *    html => string the ayat data html
     *    state => array the meta information for the ayat subscription. it contains the total number of search results and the next ayat
     */
    public function HandleGetHolyQuranVersesForEmail($parameters) 
    {
        /** The parameters for reading the verse text */
        $parameters  = array("parameters" => $parameters['parameters'], "user_interface" => "email");
        /** The verse text is read */
        $this->GetComponent("holyqurantext")->Read($parameters);
        /** The Ayat verse text is fetched for the given parameters */
        $ayat_subscriber_data = $this->GetComponent("holyqurantext")->Display();
        
        return $ayat_subscriber_data;
    }
    /**
     * This function is used to get required Holy Quran meta data
     *
     * It fetches the required meta information from database
     * If author information is required then it extracts
     * The distinct language and/or translators from the author meta information depending on user option
     * If sura information is required then it fetches the sura data
     * {@internal context any}
     *
     * @param array $parameters the parameters for the function
     *    type => string [all~sura~language~translator~language and translator~sura, language and translator] the type of data that is required
     *
     * @return array $meta_data the required meta data
     */
    public function HandleGetHolyQuranMetaData($parameters) 
    {
        /** The cached data is fetched */
        $meta_data = $this->GetCachedData("HandleGetHolyQuranMetaData", $parameters);
        /** If the cached data was found then it is returned */
        if ($meta_data !== false) return $meta_data;
        
        /** The required meta data */
        $meta_data = array();
        /** The type of information that is required */
        $type = $parameters['parameters']['type'];
        /** If language and translator information is required */
        if ($type == "language" || $type == "translator" || $type == "language and translator" || $type == "all" || $type == "sura, language and translator") 
        {
            /** The Authors class object is created */
            $authors = $this->GetComponent("authors");
            /** The list of supported languages and/or translators is fetched */
            $meta_data['languages_narrators'] = $authors->GetSupportedLanguagesAndTranslators($type);
        }
        /** If the sura information is required */
        if ($type == "sura" || $type == "all" || $type == "sura, language and translator") 
        {
            /** The Suras class object is created */
            $suras = $this->GetComponent("suras");
            /** The list of all suras is fetched */
            $meta_data['suras'] = $suras->GetSuraList();
        }
        
        /** The cached data is saved to database */
        $this->SaveDataToCache("HandleGetHolyQuranMetaData", $parameters, $meta_data);
        
        return $meta_data;
    }
}
