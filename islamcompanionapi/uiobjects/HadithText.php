<?php

namespace IslamCompanionApi\UiObjects;

use \IslamCompanionApi\DataObjects\Hadith as Hadith;
use \IslamCompanionApi\DataObjects\Authors as Authors;

/**
 * This class implements the Hadith Text
 *
 * It contains functions used to generate the html for the Hadith Text
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class HadithText extends \Framework\Object\UiObject
{
    /** The class uses functions from HadithNavigatorTools trait */
    use \IslamCompanionApi\DataObjects\Helpers\HadithNavigatorTools;
    /** The class uses functions from HadithData trait */
    use \IslamCompanionApi\DataObjects\Helpers\HadithData;
    /** The class uses functions from HadithFormatter trait */
    use \IslamCompanionApi\UiObjects\Helpers\HadithFormatter;
    /** The class uses functions from HadithTemplate trait */
    use \IslamCompanionApi\UiObjects\Helpers\HadithTemplate;    
    /**
     * Used to load the Hadith Text object with data
     *
     * It loads the data from database to the object
     *
     * @param array $data data used to read hadith information from database
     *    parameters => array the data used to read the hadith information
     *    user_interface => string [navigator~email] the user interface where the hadith text will be shown
     */
    public function Read($data = "") 
    {
        /** The hadith data is read and saved to local data property */
        $this->data = $this->ReadHadithData($data['parameters'], $data['user_interface']);        
    }
    /**
     * Used to get formatted hadith text
     *
     * It returns html containing hadith text
     * The html is formatted according to the given user interface
     *
     * @return string $hadith_text_html the html table string for the Hadith Text
     */
    public function Display()
    {
        /** If the user interface for the hadith text is email */     
        if ($this->data['user_interface'] == 'email') {
            /** The options used to format the hadith text */
            $options                  = array("tools_list" => array());
            /** The hadith list is formatted */
            $hadith_text_html         = $this->FormatHadith($this->data['hadith_list'], "paragraph", $this->data['parameters'], $options);
            /** The meta data for the hadith text html */
            $meta_data                = array("keywords" => $this->data['parameters']['search_text'], "number_of_results" => $this->data['parameters']['number_of_results'], "order" => $this->data['parameters']['order'], "times" => $this->data['parameters']['times'], "email_address" => $this->data['parameters']['email_address']);
            /** The hadith data is enclosed within html tags */
            $hadith_text_html         = $this->PopulateEmailSubscriberTemplate($hadith_text_html, $meta_data);
            /** The ayat subscriber data */
            $hadith_text_html         = array("html" => $hadith_text_html, "state" => array("total_results" => $this->data['total_results']));
        }   
        /** If the user interface for the verse text is navigator */     
        else if ($this->data['user_interface'] == 'navigator') {
            /** The list of navigator tools */
            $options                  = array("tools_list" => explode(",", $this->data['parameters']['hadith_tools']));
            /** The hadith data is rendered as an ordered list */
            $hadith_text_html         = $this->FormatHadith($this->data['hadith_list'], "paragraph", $this->data['parameters'], $options);                                                
        }
        /** If the user interface for the verse text is hadith search */     
        else if ($this->data['user_interface'] == 'search results') {
            /** The options used to format the hadith */
            $options                  = array("tools_list" => array("dictionary links", "copy", "scroll to top"));
            /** If the search results are empty */
            if (is_string($this->data['hadith_list']))
                $hadith_text_html     = $this->data['hadith_list'];
            /** The hadith data is rendered as an ordered list */
            else 
                $hadith_text_html     = $this->FormatHadith($this->data['hadith_list'], "search results", $this->data, $options);
        }
        /** If the user interface for the verse text is plain text */     
        else if ($this->data['user_interface'] == 'paragraph' || $this->data['user_interface'] == 'list' || $this->data['user_interface'] == 'plain text') {
            /** The options used to format the ayat */
            $options              = array("tools_list" => array());
            /** If the search results are empty */
            if (is_string($this->data['hadith_list']))
                $hadith_text_html = $this->data['hadith_list'];
            /** The hadith data is rendered as plain text */
            else
                $hadith_text_html = $this->FormatHadith($this->data['hadith_list'], $this->data['user_interface'], $this->data['parameters'], $options);
        }
        
        return $hadith_text_html;
    }    
}

