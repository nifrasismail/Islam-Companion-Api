<?php
namespace IslamCompanionApi\Scripts;
use \Framework\Configuration\Base as Base;
use \Framework\Object\WordPressDataObject as WordPressDataObject;
/**
 * This class implements the functionality of the Holy Quran Data Import
 *
 * It contains functions that are used to import data from quranic text files to WordPress
 *
 * @category   IslamCompanion
 * @package    IslamCompanion
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    2.0.0
 */
final class WordPressHadithDataImport extends Base
{
    /**
     * Used to add hadith meta data to WordPress custom posts
     *
     * It adds hadith meta data to books custom post type
     *
     * @param int $user_id the user_id of the logged in user
     *
     * @return int posts_added the number of custom posts that were added
     */
    public function AddHadithMetaData($user_id) 
    {
        /** The Hadith meta data file information */
        $meta_data_files = $this->GetConfig("custom", "meta_data_files");
        /** The hadith download file name is extracted from the url */
        $temp_arr = explode("/", $meta_data_files['hadith']['url']);
        /** The hadith file name */
        $file_name  = $temp_arr[count($temp_arr) -1 ];
        /** The hadith data */
        $file_details['data'] = $this->GetHadithMetaData($file_name);        
        /** The key field for uniquely identifying a post */
        $file_details['key_field'] = "custom_id";
        /** The title field */
        $file_details['title_field'] = "book";
        /** The content field */
        $file_details['content_field'] = "hadith_meta_text";
        /** The user id of the logged in user */
        $file_details['user_id'] = $user_id;
        /** The list of fields */
        $file_details['fields'] = $meta_data_files['books']['fields'];
        /** The list of fields to ignore */
        $file_details['fields_to_ignore'] = $meta_data_files['hadith']['fields_to_ignore'];
        /** The given file is imported and the number of posts added is increased */
        $posts_added = $this->GetComponent("wordpressapplication")->ImportFile("books", $file_details);
        
        return $posts_added;
    }
    /**
     * Used to fetch the hadith meta data
     *
     * It returns the contents of the hadith file
     *
     * @param string $file_name the name of the file containing the hadith data
     *
     * @return array $hadith_meta_data the hadith data
     */
    private function GetHadithMetaData($file_name)
    {
        /** The required hadith meta data */
        $hadith_meta_data = array();
        /** The Hadith data is fetched */
        $hadith_data      = $this->GetHadithData(-1, -1, $file_name);
        /** The hadith book information */
        $hadith_books     = array();
        /** Each Hadith is checked */
        for ($count = 0; $count < count($hadith_data); $count++) {
            /** The hadith data */
            $hadith_data_item = $hadith_data[$count];
            /** The hadith book information is set, if it does not exist */
            if (!isset($hadith_books[$hadith_data_item[2]])) {
                $hadith_books[$hadith_data_item[2]] = array($hadith_data_item[3] . " : " . $hadith_data_item[1], $hadith_data_item[1], $hadith_data_item[2], $hadith_data_item[3]);
            }
        }
        /** The hadith books data is formatted */
        $hadith_books    = array_values($hadith_books);

        return $hadith_books;
    }
    /**
     * Used to add ayas data to WordPress custom posts
     *
     * It adds ayas to ayas custom post type
     *
     * @param int $user_id the user_id of the logged in user
     * @param int $start_hadith the start hadith
     * @param int $hadith_count the number of hadith to add
     *
     * @return int posts_added the number of custom posts that were added
     */
    public function AddHadithData($user_id, $start_hadith, $hadith_count) 
    {
        /** The Hadith meta data file information */
        $meta_data_files = $this->GetConfig("custom", "meta_data_files");
        /** The hadith download file name is extracted from the url */
        $temp_arr = explode("/", $meta_data_files['hadith']['url']);
        /** The hadith file name */
        $file_name  = $temp_arr[count($temp_arr) -1 ];
        /** The hadith data */
        $file_details['data'] = $this->GetHadithData($start_hadith, $hadith_count, $file_name);
        /** The key field for uniquely identifying a post */
        $file_details['key_field'] = "custom_id";
        /** The title field */
        $file_details['title_field'] = "title";
        /** The content field */
        $file_details['content_field'] = "hadith_text";
        /** The user id of the logged in user */
        $file_details['user_id'] = $user_id;
        /** The list of fields */
        $file_details['fields'] = $meta_data_files['hadith']['fields'];
        /** The list of fields to ignore */
        $file_details['fields_to_ignore'] = $meta_data_files['hadith']['fields_to_ignore'];
        /** The given file is imported and the number of posts added is increased */
        $posts_added = $this->GetComponent("wordpressapplication")->ImportFile("hadith", $file_details);
        
        return $posts_added;
    }
    
    /**
     * Used to parse the given hadith file line
     *
     * The line must contain fields encloded in ""
     *
     * @param string $line the line text
     * @param string $file_extension it can be csv or txt
     *
     * @return array $parsed_line the parsed line. it contains the fields in the line
     */
    public function ParseHadithFileLine($file_extension, $line) 
    {
        /** The parsed line data */
        $parsed_line = array();       
        /** The line is parsed using regular expression */
        preg_match_all('/(.+),"(.+)","(.+)",(.+),(.+),"(.+)","(.+)"/iU', $line, $parsed_line);
        /** The parsed line contents */
        $parsed_line = array_slice($parsed_line, 1);
        /** The updated parsed line */
        $updated_parsed_line = array();
        /** Each field value is checked */
        for ($count = 0; $count < count($parsed_line); $count++) {
            if(!isset($parsed_line[$count][0])) $parsed_line[$count][0] = "";
            $updated_parsed_line[]= $parsed_line[$count][0];
        }
        
        $parsed_line = $updated_parsed_line;

        return $parsed_line;
    }
    
    /**
     * Used to fetch the hadith file contents
     *
     * It returns the contents of the hadith file
     *
     * @param int $start_hadith the start hadith
     * @param int $hadith_count the total number of hadith to import
     * @param string $file_name the name of the file containing the hadith data
     *
     * @return array $hadith_data the hadith data
     */
    private function GetHadithData($start_hadith, $hadith_count, $file_name) 
    {
        /** The hadith data */
        $hadith_data = array();       
        /** The Hadith meta data file information */
        $meta_data_files = $this->GetConfig("custom", "meta_data_files");    
        /** The file url */
        $file_url = $meta_data_files['hadith']['url'];
        /** The callback function for parsing the lines in the file */
        $line_parsing_callback = array(
                $this,
                "ParseHadithFileLine"
        );
        /** The absolute path to the local folder where the url contents should be downloaded */
        $local_folder = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data";
        /** The file contents are fetched and parsed */
        $data = $this->GetComponent("filesystem")->DownloadAndParseFile($file_url, $local_folder, $line_parsing_callback);
        /** The hadith data is extracted if the start and count values are given */
        if ($start_hadith > -1 && $hadith_count > -1)
            $hadith_data = array_slice($data, $start_hadith, $hadith_count);
        else
            $hadith_data = $data;
        
        return $hadith_data;
    }    

}

