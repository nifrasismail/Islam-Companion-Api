<?php

namespace IslamCompanionApi\UiObjects;

/**
 * This class provides functions for displaying Holy Quran verses in different forms
 *
 * It contains functions for accessing Holy Quran verse text
 * The verse text can used for different purposes such as displaying in Holy Quran navigator, email or on websites
 * The verse text can be formatted in different ways such as single paragraph, multiple paragraphs, lists and rotation
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class HolyQuranText extends \Framework\Object\UiObject
{
    /** The class uses functions from HolyQuranMetaData trait */
    use \IslamCompanionApi\DataObjects\Helpers\HolyQuranMeta;
    /** The class uses functions from HolyQuranNavigatorTools trait */
    use \IslamCompanionApi\DataObjects\Helpers\HolyQuranNavigatorTools;
    /** The class uses functions from AyatData trait */
    use \IslamCompanionApi\DataObjects\Helpers\AyatData;
    /** The class uses functions from AyatFormatter trait */
    use \IslamCompanionApi\UiObjects\Helpers\AyatFormatter;
    /** The class uses functions from AyatTemplate trait */
    use \IslamCompanionApi\UiObjects\Helpers\AyatTemplate;
    /**
     * Used to load the Verse Text object with data
     *
     * It loads the data from database to the object
     *
     * @param array $data data used to read verse information from database
     *    parameters => array the data used to read the verse information
     *    user_interface => string [navigator~email] the user interface where the verse text will be shown
     */
    public function Read($data = "") 
    {
        /** The verse data is read and saved to local data property */
        $this->data = $this->ReadVerseData($data, $data['user_interface']);        
    }    
    /**
     * Used to get formatted verse text
     *
     * It returns html containing verse text
     * The html is formatted according to the given user interface
     *
     * @return mixed $verse_text the verse text information. it contains the Holy Quran text and may contain state information
     */
    public function Display()
    {
        /** If the user interface for the verse text is email */     
        if ($this->data['user_interface'] == 'email') {
            /** The css class is set */
            $this->data['parameters']['css_class']      = $this->data['css_class'];
            /** The css attributes are set */
            $this->data['parameters']['css_attributes'] = $this->data['css_attributes'];
            /** The total number of results */
            $total_results        = count($this->data['ayat_list']);
            /** The options used to format the ayat */
            $options              = array("tools_list" => array("sura and ayat meta", "single sura"));
            /** The ayat data is rendered as a paragraph */
            $ayat_text_html       = $this->FormatAyas($this->data['ayat_list'], "paragraph", $this->data['parameters'], $options);
            /** The next ayat id */
            $next_ayat_id         = ($this->data['ayat_list'][$total_results -1 ]['sura_ayat_id'] + 1);
            /** The first ayat */
            $first_ayat           = $this->data['ayat_list'][0]['sura_ayat_id'];
            /** The last ayat */
            $last_ayat            = $this->data['ayat_list'][$total_results-1]['sura_ayat_id'];
            /** The meta data for the ayat text html */
            $meta_data            = array("keywords" => $this->data['parameters']['search_text'], "number_of_results" => $this->data['parameters']['number_of_results'], "narrator" => $this->data['narrator'], "language" => $this->data['language'], "sura" => $this->data['sura_data']['tname'] . " (" . $this->data['sura_data']['ename'] . ")", "start_ayat" => $first_ayat, "end_ayat" => $last_ayat, "order" => $this->data['parameters']['order'], "times" => $this->data['parameters']['times'], "email_address" => $this->data['parameters']['email_address']);
            /** The ayat data is enclosed within html tags */
            $ayat_text_html       = $this->PopulateEmailSubscriberTemplate($ayat_text_html, $meta_data);
            /** The ayat subscriber data */
            $verse_text           = array("html" => $ayat_text_html, "state" => array("total_results" => $total_results, "next_ayat" => $next_ayat_id));
        }
        /** If the user interface for the verse text is navigator */     
        else if ($this->data['user_interface'] == 'navigator') {
            /** The list of navigator tools */
            $options              = array("tools_list" => explode(",", $this->data['parameters']['tools']));
            /** The ayat data is rendered as an ordered list */
            $verse_text           = $this->FormatAyas($this->data['ayat_list'], $this->data['parameters']['layout'], $this->data, $options);                                                
        }
        /** If the user interface for the verse text is holy quran search */     
        else if ($this->data['user_interface'] == 'search results') {
            /** The options used to format the ayat */
            $options              = array("tools_list" => array("highlight text", "dictionary links", "copy", "scroll to top"));

            /** If the search results are empty */
            if (is_string($this->data['ayat_list']))
                $verse_text       = $this->data['ayat_list'];
            /** The ayat data is rendered as an ordered list */
            else 
                $verse_text       = $this->FormatAyas($this->data['ayat_list'], "search results", $this->data, $options);                              
        }
        /** If the user interface for the verse text is plain text */     
        else if ($this->data['user_interface'] == 'plain text') {
            /** The options used to format the ayat */
            $options              = array("tools_list" => array());
            /** If the search results are empty */
            if (is_string($this->data['ayat_list']))
                $verse_text       = $this->data['ayat_list'];
            /** The ayat data is rendered as plain text */
            else
                $verse_text       = $this->FormatAyas($this->data['ayat_list'], "plain text", $this->data['parameters'], $options);
        }
        /** If the user interface for the verse text is list */     
        else if ($this->data['user_interface'] == 'list') {
            /** The options used to format the ayat */
            $options              = array("tools_list" => array("sura and ayat meta"));
            /** If the search results are empty */
            if (is_string($this->data['ayat_list']))
                $verse_text       = $this->data['ayat_list'];
            /** The ayat data is rendered as a list */
            else 
                $verse_text       = $this->FormatAyas($this->data['ayat_list'], "list", $this->data['parameters'], $options);                              
        }
        return $verse_text;                
    }
}

