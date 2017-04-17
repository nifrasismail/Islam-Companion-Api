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
final class WordPressQuranicDataImport extends Base
{
    /**
     * Used to delete custom posts
     *
     * It deletes custom posts of the type Suras, Ayas, Authors and Hadith depending on the users choice
     * 
     * @param int $post_count [1~500] the number of posts to delete
     * @param array $post_type_list [Suras~Authors~Ayas~Hadith]
     *
     * @return $delete_count the number of deleted posts
     */
    public function DeleteData($post_count, $post_type_list) 
    {
        /** The number of deleted posts */
        $delete_count = 0;
        /** The posts in each post type are deleted */
        for ($count1 = 0;$count1 < count($post_type_list);$count1++) 
        {
            $post_type = $post_type_list[$count1];
            /** 10 posts are fetched at a time and then deleted */
            for ($count2 = 0;$count2 < ceil($post_count / 10);$count2++) 
            {
                /** Parameters used to fetch the Ayas posts */
                $args = array(
                    'posts_per_page' => 10,
                    'post_type' => $post_type
                );
                /** All the posts in custom type Ayas are fetched */
                $posts_array = get_posts($args);
                /** Each post is deleted */
                for ($count3 = 0;$count3 < count($posts_array);$count3++) 
                {
                    /** A post is fetched */
                    $post = $posts_array[$count3];
                    /** The post id */
                    $post_id = $post->ID;
                    /** The post is deleted */
                    wp_delete_post($post_id, true);
                }
                $delete_count+= count($posts_array);
            }            
            if ($delete_count >= $post_count) break;
        }
        
        if ($delete_count <=0) $delete_count = $post_count;
        
        return $delete_count;
    }
    /**
     * Used to add Holy Quran meta data
     *
     * It adds Holy Quran meta data to sura and author custom post types
     * It reads the Quran meta data in the form of csv files from wordpress.org
     * The meta data is located in assets folder of the plugin directory
     * It saves the sura and author meta data to WordPress
     *
     * @param int $user_id the id of the logged in user
     * @param string $data_type [sura~author] the type of data to import
     *
     * @return int posts_added the number of custom posts that were added
     */
    public function AddHolyQuranMetaData($user_id, $data_type) 
    {
        /** The number of meta data posts that were added */
        $posts_added = 0;
        /** The Holy Quran meta data file information */
        $meta_data_files = $this->GetConfig("custom", "meta_data_files");
        /** The author and sura meta data files are downloaded from wordpres.org */
        $file_list = array(
            "author" => $meta_data_files['author_meta'],
            "sura" => $meta_data_files['sura_meta']
        );
        /** The callback function for parsing the lines in the file */
        $line_parsing_callback = array(
            $this,
            "ParseLine"
        );
        /** The absolute path to the local folder where the url contents should be downloaded */
        $local_folder = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data";
        /** The file details */
        $file_details = $file_list[$data_type];
        /** The file url */
        $file_url = $file_details['url'];
        /** The user id is set */
        $file_details['user_id'] = $user_id;
        /** The file data. The file contents are downloaded and parsed */
        $file_details['data'] = $this->GetComponent("filesystem")->DownloadAndParseFile($file_url, $local_folder, $line_parsing_callback);
        /** The given file is imported and the number of posts added is increased */
        $posts_added = $posts_added + $this->GetComponent("wordpressapplication")->ImportFile($data_type, $file_details);
        
        return $posts_added;
    }
    /**
     * Used to add ayas data to WordPress custom posts
     *
     * It adds ayas to following ayas custom post type
     *
     * @param int $user_id the user_id of the logged in user
     * @param int $start_ayat the start ayat
     * @param int $total_ayat_count the total number of ayas to import
     * @param string $translator the name of the narrator
     * @param string $language the language of the translation that needs to be imported
     *
     * @return int posts_added the number of custom posts that were added
     */
    public function AddHolyQuranData($user_id, $start_ayat, $total_ayat_count, $translator, $language) 
    {
        /** The Holy Quran meta data file information */
        $meta_data_files = $this->GetConfig("custom", "meta_data_files");
        /** The ayat data */
        $file_details['data'] = $this->GetAyatData($start_ayat, $total_ayat_count, $translator, $language);
        /** The key field for uniquely identifying a post */
        $file_details['key_field'] = "custom_ayat_id";
        /** The title field */
        $file_details['title_field'] = "ayat_id";
        /** The content field */
        $file_details['content_field'] = "ayat_id";
        /** The user id of the logged in user */
        $file_details['user_id'] = $user_id;
        /** The list of fields */
        $file_details['fields'] = array_merge($meta_data_files['quran_meta']['fields'], $meta_data_files['quran_simple']['fields'], $meta_data_files['quran_ayas']['fields']);
        /** The list of fields to ignore */
        $file_details['fields_to_ignore'] = array_merge($meta_data_files['quran_meta']['fields_to_ignore'], $meta_data_files['quran_simple']['fields_to_ignore'], $meta_data_files['quran_ayas']['fields_to_ignore']);
        /** The given file is imported and the number of posts added is increased */
        $posts_added = $this->GetComponent("wordpressapplication")->ImportFile("aya", $file_details);
        
        return $posts_added;
    }
    /**
     * Used to parse the given line
     *
     * The line can be from a csv file or a txt file
     * If line is from a csv file, then the fields must be enclosed with "" and separated with ,
     * If the line is from a txt file, the the fields must be separated with |
     *
     * @param string $line the line text
     * @param string $file_extension it can be csv or txt
     *
     * @return array $parsed_line the parsed line. it contains the fields in the line or false if the line was commented
     */
    public function ParseLine($file_extension, $line) 
    {
        /** If the line contains '#' then it is returned */
        if (strpos($line, '#') !== false) return false;
        /** The parsed line data */
        $parsed_line = array();
        /** If the file is a csv file then the data is extracted using regular expression */
        if ($file_extension == "csv") 
        {
            preg_match_all('/"(.+)"/iU', $line, $parsed_line);
            $parsed_line = $parsed_line[1];
        }
        /** If the file is a txt file then the data is extracted using explode function */
        else if ($file_extension == "txt") 
        {
            $parsed_line = explode("|", $line);
        }
        
        return $parsed_line;
    }
    /**
     * Used to fetch the ayat file contents
     *
     * It downloads each ayat file from wordpress.org
     * It returns the contents of each file
     *
     * @param int $start_ayat the start ayat
     * @param int $total_ayat_count the total number of ayas to import
     * @param string $translator the name of the narrator
     * @param string $language the language of the translation that needs to be imported
     *
     * @return array $ayat_data the ayat data. it is an array with 3 keys:
     * quran_meta => contains the ayat meta data
     * quran_simple => contains the original arabic ayat text
     * quran_ayas => contains the translation of the ayas
     */
    private function GetAyatData($start_ayat, $total_ayat_count, $translator, $language) 
    {
        /** The ayat data */
        $ayat_data = array();
        /** The file name for the given translator and language */
        $file_name = $this->GetTranslatorFileName($translator, $language);
        /** The Holy Quran meta data file information */
        $meta_data_files = $this->GetConfig("custom", "meta_data_files");
        /** Each ayat data file is downloaded */
        foreach ($meta_data_files as $file_type => $file_details) 
        {
            /** The type of data */
            $type = $file_details['type'];
            /** If the file type is not equal to ayat_data then file is ignored */
            if ($type != "ayat_data") continue;
            /** The file url */
            $file_url = $file_details['url'];
            /** If the url contains a {file_name} placeholder, then the placeholder is replaced with the file name */
            $file_url = str_replace("{file_name}", $file_name, $file_url);
            /** The callback function for parsing the lines in the file */
            $line_parsing_callback = array(
                $this,
                "ParseLine"
            );
            /** The absolute path to the local folder where the url contents should be downloaded */
            $local_folder = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data";
            /** The file contents are fetched and parsed */
            $data = $this->GetComponent("filesystem")->DownloadAndParseFile($file_url, $local_folder, $line_parsing_callback);
            /** The data is merged with the previous data */
            for ($count = ($start_ayat - 1) , $counter = 0;$count < ((($start_ayat - 1) + $total_ayat_count));$count++, $counter++) 
            {
                /** If the index is out of range, then the loop ends */
                if (!isset($data[$count])) break;
                /** The file name is set in the data if the file containing aya meta data is being imported */
                if (count($data[$count]) == 10) 
                {
                    $data[$count][10] = urldecode($file_name);
                }
                /** If the counter value is outside the range then the loop ends */
                if (!isset($data[$count])) break;
                /** If the ayat data is not an array then it is initialized with an array */
                if (!isset($ayat_data[$counter])) $ayat_data[$counter] = array();
                /** The ayat data array is merged with the new data array */
                $ayat_data[$counter] = array_merge($ayat_data[$counter], $data[$count]);
            }
        }
        
        return $ayat_data;
    }
    /**
     * Used to get the file name for the given translator and language
     *
     * It returns the file name for the given translator and language
     * The file name is returned from the Author custom post type
     *
     * @param string $translator the name of the narrator
     * @param string $language the language of the translation that needs to be imported
     *
     * @return string $file_name the file_name custom field for the given translator and language
     */
    private function GetTranslatorFileName($translator, $language) 
    {
        /** The required file name */
        $file_name = "";
        /** The configuration object is fetched */
        $configuration_object = $this->GetConfigurationObject();
        /** The configuration object is fetched */
        $parameters['configuration'] = $configuration_object;
        /** WordPress data object is created */
        $wordpress_data_object = new WordPressDataObject($parameters);
        /** The parameters for the WordPress object. It indicates the type of object to be created */
        $meta_information = array(
            "data_type" => "author",
            "object_type" => "post"
        );
        /** The table name and field name are set */
        $wordpress_data_object->SetMetaInformation($meta_information);
        /** The data used to fetch the WordPress posts */
        $parameters = array(
            "post_type" => "Authors",
            "meta_key" => "language",
            "meta_value" => $language,
            "posts_per_page" => 100
        );
        /** The key field for the object is set */
        $wordpress_data_object->SetKeyField("custom_language");
        /** The data is read from database */
        $wordpress_data_object->Read($parameters);
        /** The data is fetched */
        $data = $wordpress_data_object->GetData();
        /** Each post data is checked for matching translator */
        for ($count = 0;$count < count($data);$count++) 
        {
            /** The value of custom field, translator */
            $custom_translator = $data[$count]['translator'];
            /** The value of custom field, file name */
            $custom_file_name = $data[$count]['file_name'];
            /** If the translator value matches the value of custom field translator */
            if ($translator == $custom_translator) $file_name = $custom_file_name;
        }
        /** The brackets are removed and spaces are replaced with - */
        $file_name = str_replace(" ", "%20", $file_name);
        
        return $file_name;
    }
}

