<?php

namespace IslamCompanionApi\DataObjects\Helpers;

/**
 * It provides functions for retrieving Hadith data
 *
 * It provides functions that allow searching Hadith data uses different conditions
 *
 * @category   IslamCompanionApi
 * @package    UiObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait HadithData
{
    /**
     * Used to read the hadith data from database
     *
     * It returns the required hadith data
     * The data includes hadith presentation information
     *
     * @param array $parameters the parameters needed for reading the hadith data
     * @param string $user_interface [navigator~email] the user interface where the hadith data will be displayed
     *
     * @return array $hadith_data the required hadith data
     */
    public function ReadHadithData($parameters, $user_interface)
    {
        /** The hadith parameters */
        $hadith_data['parameters']       = $parameters;
        /** The user interface property is set */
        $hadith_data['user_interface']   = $user_interface;
        /** The hadith language is set to English */
        $hadith_data['language']         = "English";
        /** If the user interface is email */
        if ($user_interface == "email") {
           /** The hadith information */
           $hadith_information           = $this->GetComponent("hadith")->SearchHadith($parameters['search_text'], $parameters['language'], $parameters['order'], $parameters['start'], $parameters['number_of_results']);
           /** The hadith list */
           $hadith_data['hadith_list']   = $hadith_information['hadith_text'];
           /** The total number of hadith */
           $hadith_data['total_results'] = $hadith_information['hadith_count'];
        }
        /** If the user interface is navigator */
        else if ($user_interface == "navigator") {
            /** The language code is fetched */
            $hadith_data['language_data']   = $this->GetComponent("holyqurantext")->GetLanguageInformation($parameters['hadith_language']);
            /** The list of hadith titles in given book are fetched */
            $hadith_data['hadith_list']     = $this->GetComponent("hadith")->GetHadithText($parameters['hadith_source'], $parameters['hadith_language'], $parameters['hadith_book'], $parameters['hadith_title']);
            /** The hadith language is set */
            $hadith_data['language']        = $parameters['hadith_language'];
        }
        /** If the user interface is plain text or list */
        if ($user_interface == "paragraph" || $user_interface == "list" || $user_interface == "plain text") {
            /** The hadith list */
            $hadith_data['hadith_list']     = $this->GetHadithDataById($parameters);
        }
        /** If the user interface is search results */
        else if ($user_interface == "search results") {
            /** The hadith list */
            $hadith_list                    = $this->GetComponent("hadith")->SearchHadith($parameters['search_text'], $parameters['language']);
            /** The number of hadith to display per page in the search results */
            $hadith_data['hadith_per_page'] = $this->GetConfig("custom", "verses_per_page");
            /** The page number of the search results */
            $hadith_data['page_number']     = $parameters['page_number'];
            /** The total number of results */
            $hadith_data['total_results']   = $hadith_list['hadith_count'];
            /** The total number of pages */
            $hadith_data['total_number_of_pages'] = ceil($hadith_data['total_results'] / $hadith_data['hadith_per_page']);
            /** If no results were found then notification message is returned */
            if ($hadith_data['total_number_of_pages'] == 0) {
                /** The search results pagination */
                $hadith_data['hadith_list'] = $this->GetComponent("template")->Render("no_search_results", array());
            }            
            /** The hadith in the search results */
            else {
                $hadith_data['hadith_list'] = array_slice($hadith_list['hadith_text'], (($hadith_data['page_number'] - 1) * $hadith_data['hadith_per_page']) , $hadith_data['hadith_per_page']);
            }
        }

        return $hadith_data;
    }
    /**
     * Used to get the required hadith data by id
     *
     * It returns the hadith data for the required hadith
     *
     * @param array $parameters the parameters for the function  
     *    hadith => string the comma separated list of hadith ids
     *
     * @return array $hadith_data the required hadith data
     */
    public function GetHadithDataById($parameters) 
    {
        /** The required hadith data */
        $hadith_data                        = array();
        /** The list of required hadith */
        $required_hadith_list               = explode(",", $parameters['hadith_numbers']);
        /** Each hadith text is placed inside a list tag */
        for ($count = 0; $count < count($required_hadith_list); $count++)
        {
            /** The hadith id */
            $hadith_id                      = $required_hadith_list[$count];
            /** The where clause used to search for the hadith */
            $where_clause                   = array(array('field' => "id", 'value' => $hadith_id, 'operation' => " = ", 'operator' => ""));
            /** The hadith list */
            $hadith_details                 = $this->GetComponent("hadith")->SearchHadith("", "English", "sequence", '1', '-1', $where_clause);
            /** The hadith data is updated */
            $hadith_data                    = array_merge($hadith_data, $hadith_details['hadith_text']);
        }

        return $hadith_data;        
    }
}
