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
trait HadithApi
{   
    /**
     * This function is used to get the Hadith Navigator widget
     * It generates the frontend for the widget
     *
     * It generates the Hadith Navigator using the given parameters
     * It returns the html and state information for the Hadith Navigator
     * {@internal context any}
     *
     * @param array $parameters the parameters for the function
     *    hadith_language => string [custom] the current hadith language
     *    hadith_source => string [custom] the current hadith source
     *    hadith_book => string [custom] the current hadith book number
     *    hadith_title => string [custom] the current hadith title
     *    template => string [website~dashboard] the type of template. it controls the layout of the navigator 
     *    tools => string [custom] the tools to use for the navigator. for example: "copy", "dictionary links", "shortcode", "scroll to top", "highlight text"
     *    options => string [custom] the option provided by the navigator. for example: book-title, source, search, subscription
     *    action => string [next~previous~current~hadith_book_box~hadith_title_box~hadith_source_box] the action performed by the user on the Hadith Navigator
     *
     * @return array $hadith_navigator_data the data used to display the Hadith Navigator
     *    html => string the hadith navigator html
     *    state => array $state the hadith navigator state data
     *        hadith_book => string [custom] the current hadith book number
     *        hadith_title => string [custom] the current hadith number
     */
    public function HandleGetHadithNavigator($parameters) 
    {
        /** The cached data is fetched */
        $hadith_navigator_data = $this->GetCachedData("HandleGetHadithNavigator", $parameters);
        /** If the cached data was found then it is returned */
        if ($hadith_navigator_data !== false) return $hadith_navigator_data;
        
        /** The Hadith Navigator object is fetched and loaded with data */
        $this->GetComponent("hadithnavigator")->Read($parameters['parameters']);
        /** The Hadith Navigator object is displayed */
        $hadith_navigator_html = $this->GetComponent("hadithnavigator")->Display();
        /** The Hadith Navigator data is fetched */
        $navigator_data = $this->GetComponent("hadithnavigator")->GetNavigatorData();
        $hadith_navigator_data = array(
            "html" => $hadith_navigator_html,
            "state" => $navigator_data
        );
        /** The cached data is saved to database */
        $this->SaveDataToCache("HandleGetHadithNavigator", $parameters, $hadith_navigator_data);
        
        return $hadith_navigator_data;
    }   
    /**
     * Used to search Hadith text
     * This function is used to search Hadith text
     *
     * It fetches Hadith text that match the given search parameters
     * {@internal context any}
     *
     * @param array $parameters the parameters for the function
     *    language => string the language for the translation
     *    search_text => string the search text
     *    layout => string [plain~navigator] the layout to use for formatting the search results     
     *    page_number => int the page number of the search results
     *
     * @return string $hadith_navigator_data the data used to display the Hadith Navigator
     */
    public function HandleGetHadithSearchResults($parameters) 
    {
        /** The search text data is decoded */
        $parameters['parameters']['search_text'] = urldecode(urldecode($parameters['parameters']['search_text']));
        /** The cached data is fetched */
        $hadith_text = $this->GetCachedData("HandleGetHadithSearchResults", $parameters);
        /** If the cached data was found then it is returned */
        if ($hadith_text !== false) return $hadith_text;
        
        /** The parameters for reading the hadith text */
        $parameters                              = array("parameters" => $parameters['parameters'], "user_interface" => "search results");
        /** The Hadith text object is loaded with data */
        $this->GetComponent("hadithtext")->Read($parameters);
        /** The Hadith text object is displayed as search results */
        $hadith_search_results                   = $this->GetComponent("hadithtext")->Display();        
        /** The cached data is saved to database */
        $this->SaveDataToCache("HandleGetHadithSearchResults", $parameters, $hadith_search_results);
        

        return $hadith_search_results;
    }
    /**
     * Used to search Hadith text for email subscribers
     * This function is used to search Hadith text
     *
     * It fetches Hadith text that match the given search parameters
     * {@internal context any}
     *
     * @param array $parameters the parameters for the function
     *    language => string the language for the translation
     *    search_text => string the search text
     *    number_of_results => int the number of search results
     *    start => int the position from where the search results should be returned
     *    order => string [sequence~random] the order of the search results
     *    times => string the times at which the hadith should be sent
     *    email_address => string the email addresses at which the Hadith email should be sent
     *     
     * @return array $hadith_subscriber_data the data requested by the subscriber
     *    html => string the hadith data html
     *    state => array the meta information for the hadith subscription. it contains the total number of search results
     */
    public function HandleGetHadithTextForEmail($parameters) 
    {
        /** The parameters for reading the hadith text */
        $parameters             = array("parameters" => $parameters['parameters'], "user_interface" => "email");
        /** The hadith text is read */
        $this->GetComponent("hadithtext")->Read($parameters);
        /** The Hadith text is fetched for the given parameters */
        $hadith_subscriber_data = $this->GetComponent("hadithtext")->Display();
        
        /** The cached data is saved to database */
        $this->SaveDataToCache("HandleGetHadithSearchResults", $parameters, $hadith_subscriber_data);
        
        return $hadith_subscriber_data;
    }    
    /**
     * Used to fetch Hadith text
     * This function is used as the handler function for the shortcode get_hadith_text
     *
     * It fetches Hadith text for the given parameters
     * {@internal context any}
     *
     * @param array $parameters the shortcode parameters given by the user
     *    hadith_numbers => string the comma separated list of hadith ids
     *    container => string [plain text~paragraph~list] the layout for the hadith text
     *    css_classes => string the css classes for the container elements    
     *
     * @return string $hadith_text
     */
    public function HandleGetHadithText($parameters) 
    {
        /** The cached data is fetched */
        $hadith_text            = $this->GetCachedData("HandleGetHadithText", $parameters);
        /** If the cached data was found then it is returned */
        if ($hadith_text !== false) return $hadith_text;
        /** The parameters for reading the hadith text */
        $parameters             = array("parameters" => $parameters['parameters'], "user_interface" => $parameters['parameters']['container']);
        /** The hadith text is read */
        $this->GetComponent("hadithtext")->Read($parameters);
        /** The Hadith text is fetched for the given parameters */
        $hadith_text            = $this->GetComponent("hadithtext")->Display();
        
        return $hadith_text;
    }
    /**
     * This function is used to get required Hadith meta data
     *
     * If hadith information is required then it fetches the hadith data
     * {@internal context any}
     *
     * @return array $meta_data the required meta data
     */
    public function HandleGetHadithMetaData($parameters) 
    {
        /** The cached data is fetched */
        $meta_data = $this->GetCachedData("HandleGetHadithMetaData", $parameters);
        /** If the cached data was found then it is returned */
        if ($meta_data !== false) return $meta_data;
        
        /** The Hadith class object is created */
        $hadith = $this->GetComponent("hadith");
        /** The hadith meta information is fetched */
        $meta_data = $hadith->GetHadithMetaInformation();
        
        /** The cached data is saved to database */
        $this->SaveDataToCache("HandleGetHadithMetaData", $parameters, $meta_data);
        
        return $meta_data;
    }
}
