<?php
namespace Framework\Utilities;
/**
 * String class provides string manipulation functions
 *
 * It provides functions such as converting from relative url to absolute url, reading excel file contents
 * Exporting data as rss feed and more
 *
 * @category   Framework
 * @package    Utilities
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.7
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class Strings
{
    /**
     * The single static instance
     */
    protected static $instance;
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists
     * If it does not exist then it is created
     * The instance is returned
     *
     * @return String static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance($parameters) 
    {
        if (static ::$instance == null) 
        {
            static ::$instance = new static ($parameters);
        }
        return static ::$instance;
    }
    /**
     * Used to convert a relative url to an absolute url
     *
     * @param string $main_url base url
     * @param string $rel_url url to get converted to abs url
     *
     * @return string $absolute_url. the absolute url with domain name
     */
    public function ConvertRelUrlToAbsUrl($main_url, $rel_url) 
    {
        $abs_url = $rel_url;
        $temp_arr = explode("/", $main_url);
        $domain_name = $temp_arr[0] . "//" . $temp_arr[2];
        if (strpos($rel_url, "/") === 0) 
        {
            $abs_url = $domain_name . $rel_url;
        }
        else
        {
            $abs_url = $main_url . $rel_url;
        }
        return $abs_url;
    }
    /**
     * Checks if given string is valid json
     *
     * @param string $data string to be checked
     *
     * @return boolean $is_valid true if string is valid json. returns false otherwise.
     */
    public function IsJson($data) 
    {
        $is_valid = false;
        if (is_string($data)) 
        {
            @json_decode($data);
            $is_valid = (json_last_error() === JSON_ERROR_NONE);
        }
        return $is_valid;
    }
    /**
     * Checks if given string contains html tags
     *
     * @param string $data string to be checked
     *
     * @return boolean $is_valid true if string contains html. returns false otherwise.
     */
    public function IsHTML($data) 
    {
        $is_valid = false;
        if (is_string($data)) 
        {
            $is_valid = (strip_tags($data) != $data);
        }
        return $is_valid;
    }
    /**
     * Checks if given string is valid base64 encoded
     *
     * @param string $data the string to be checked
     *
     * @return boolean $is_valid true if string is valid base64. returns false otherwise
     */
    public function IsBase64($data) 
    {
        /** If the given data is not a string then function returns false */
        if (!is_string($data)) 
        {
            $is_valid = false;
            return $is_valid;
        }
        $is_valid = false;
        /** The data is base64 decoded. It is decoded for use in ctype_print function */
        $decoded_data = base64_decode($data, false);
        /** The newlines and tabs are removed from base64 decoded data */
        $decoded_data = str_replace("\r", "", $decoded_data);
        $decoded_data = str_replace("\n", "", $decoded_data);
        $decoded_data = str_replace("\t", "", $decoded_data);
        /** 
         * The decoded data is encoded again
         * If the result is same as the original string and the base64 decoded string consists only of printable characters,
         * Then the string is valid base64
         */
        if (base64_encode($decoded_data) == $data)
        {
            /** If the data is utf8 encoded */
            if (mb_detect_encoding($decoded_data, 'UTF-8', true) == 'UTF-8') {
                $is_valid = ctype_print(utf8_decode($decoded_data));
            }
            else 
                $is_valid = ctype_print($decoded_data);
        }
        return $is_valid;
    }
    /**
     * Used to convert a string to camel case
     *
     * @param string $string text to be converted to camel case
     * e.g part1_part2
     *
     * @return string $camelcase_text camel case string
     */
    public function CamelCase($string) 
    {
        $string = str_replace("_", " ", $string);
        $string = ucwords($string);
        $camelcase_text = str_replace(" ", "", $string);
        return $camelcase_text;
    }
    /**
     * Used to concatenate the given strings
     *
     * The function supports variable number of arguments
     *
     * @param string $string text to be concatenated
     * @param string $string text to be concatenated
     *
     * @return string $concatenated_text the concatenated string
     */
    public function Concatenate() 
    {
        $concatenated_text = "";
        for ($count = 0;$count < func_num_args();$count++) 
        {
            $text = func_get_arg($count);
            $concatenated_text.= $text;
        }
        return $concatenated_text;
    }
    /**
     * Used to get the file name and extension from the given url
     *
     * It parses the given url
     * It extracts the file and file extension
     *
     * @param string $url the file url
     *
     * @return array $file_data the file data. it is an array with 2 keys:
     * file_name => the file name
     * file_extension => the file extension
     */
    public function GetFileNameAndExtension($url) 
    {
        /** The file extension */
        $temp_arr = explode(".", $url);
        $file_data['file_extension'] = $temp_arr[count($temp_arr) - 1];
        /** The file name */
        $temp_arr = explode("/", $url);
        $file_data['file_name'] = $temp_arr[count($temp_arr) - 1];
        return $file_data;
    }
    /**
     * Used to export the given data as rss feed
     *
     * It returns a xml data string containing rss feed data
     *
     * @link http://www.w3schools.com/xml/xml_syntax.asp
     * @param array $data the data to be exported to rss format
     * @param array $xml_namespace the xml namespace for the document
     *     prefix => string the xml namespace prefix
     *     name => string the xml namespace name
     *     uri => string the xml namespace uri
     * @param array $namespace_attributes the list of tags that need to be prefixed with namespace
     *
     * @return string $rss_file the contents of the rss file
     */
    public function ExportToRss($data, $xml_namespace, $namespace_attributes) 
    {
        /** The XMLWriter class object is created. The XMLWriter php extension is enabled by default */
        $writer = new \XMLWriter;
        $writer->openMemory();
        /** The xml prolog is added */
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);
        $writer->startAttributeNS($xml_namespace['prefix'], $xml_namespace['name'], $xml_namespace['uri']);
        /** The rss tag is opened */
        $writer->startElement('rss');
        $writer->startAttribute('version');
        $writer->text('2.0');
        $writer->endAttribute();
        $writer->startElement('channel');
        /** Each Item is added to the rss feed */
        for ($count = 0;$count < count($data);$count++) 
        {
            $data_item = $data[$count];
            $writer->startElement('item');
            /** Xml tag is created for each data item */
            foreach ($data_item as $tag_name => $tag_value) 
            {
                /** If the tag name is in the list of tags that need to be prefixed with namespace */
                if (in_array($tag_name, $namespace_attributes)) 
                {
                    /** The namespace is added to the tag name */
                    $tag_name = $xml_namespace['name'] . ":" . $tag_name;
                }
                $writer->startElement($tag_name);
                $writer->text($tag_value);
                $writer->endElement();
            }
            $writer->endElement();
        }
        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();
        /** The xml data is exported to string */
        $rss_file = $writer->outputMemory(TRUE);
        return $rss_file;
    }
}

