<?php
namespace Framework\Utilities;
/**
 * Singleton class
 * FileSystem class provides functions related to working with file system
 *
 * It includes functions such as reading files, writting files fetch remote file contents
 *
 * @category   FileSystem
 * @package    UtilitiesFramework
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.2
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class FileSystem
{
    /**
     * Location of upload folder
     */
    private $upload_folder;
    /**
     * Maximum size of file that can be uploaded
     */
    private $max_allowed_file_size;
    /**
     * Contains extensions for each file type that is permitted for uploading
     */
    private $allowed_extensions;
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
     * @return FileSystem static::$instance name the instance of the correct child class is returned
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
     * Class constructor
     *
     * @param array $parameters an array with following keys:
     * upload_folder=> the location of the upload_folder
     * allowed_extensions=> the file types that are allowed to be uploaded
     * max_allowed_file_size=> the maximum allowed size for uploaded files
     *
     * @return int returns the number of elements.
     */
    public function __construct($parameters) 
    {
        $this->upload_folder = isset($parameters['upload_folder']) ? $parameters['upload_folder'] : '';
        $this->allowed_extensions = isset($parameters['allowed_extensions']) ? $parameters['allowed_extensions'] : '';
        $this->max_allowed_file_size = isset($parameters['max_allowed_file_size']) ? $parameters['max_allowed_file_size'] : '';
    }
    /**
     * Reads the contents of the given folder
     *
     * @param string $folder_path absolute path to the folder whoose contents are to be read
     * @param int $max_levels the number of folder levels to search. if set to -1 then folders at all levels are checked
     * @param string $include_file_pattern optional the file pattern to check. these must be present in the file path
     * @param string $exclude_file_pattern optional the file pattern to check. these must not be present in the file path
     * @param boolean $include_folder_names indicates that the folder names should be included in return value
     * @param int $current_level indicates the current level of recursion
     *
     * @return array $valid_file_list list of files in folder. the . and .. items are removed from the list
     *                          if the file pattern is given then only files matching the pattern are fetched 
     */
    public function GetFolderContents($folder_path, $max_levels = 1, $include_file_pattern = "", $exclude_file_pattern = "", $include_folder_names = false, $current_level = 1) 
    {
        /** If the folder is not readable then an exception is thrown */
        if (!is_dir($folder_path)) throw new \Exception("Error in reading folder: " . $folder_path);
        /** The list of files in folder is fetched */
        $file_list = scandir($folder_path);
        /** The . and .. entries are removed from list */
        $file_list = array_slice($file_list, 2);
        /** Valid file list */
        $valid_file_list = array();
        /** Each file name is checked */
        for ($count = 0; $count < count($file_list); $count++)
        {
            /** The file/directory name */
            $item_name = $folder_path . DIRECTORY_SEPARATOR . $file_list[$count];            
            /** If the include file pattern is empty or it matches the given file/directory */
            if ((is_file($item_name) || (is_dir($item_name) && $include_folder_names)) && ($include_file_pattern == "" || strpos($item_name, $include_file_pattern) !== false)) {
                /** If the exclude file pattern is empty or it is not empty and it does not match the given file */
                if ($exclude_file_pattern == "" || strpos($item_name, $exclude_file_pattern) === false)
                    $valid_file_list[] = $item_name;
            }
            /** If the file is a directory then it is scanned */
            if (is_dir($item_name) && $current_level < $max_levels){
                $valid_file_list = array_merge($valid_file_list, $this->GetFolderContents($item_name, $max_levels, $include_file_pattern, $exclude_file_pattern, $include_folder_names, ($current_level+1)));
            }
        }
        /** The file list is returned */
        return $valid_file_list;
    }
    /**
     * Deletes the given file from local disk
     *
     * @param string $file_path the absolute path to the file
     * @throws Exception throws an exception if the file could not be deleted
     */
    public function DeleteLocalFile($file_path) 
    {
        if (!unlink($file_path)) throw new \Exception("File could not be deleted");
    }
    /**
     * Writes the given text to a file on local disk
     *
     * @param string $file_text the text that needs to be written to local file
     * @param string $file_path the absolute path to the file
     * @throws Exception throws an exception if the file could not be written
     *
     * @return string returns the contents of the file
     */
    public function WriteLocalFile($file_text, $file_path) 
    {
        $fh = fopen($file_path, "w");
        if (!fwrite($fh, $file_text)) throw new \Exception("Text could not be written to the file: " . $file_path);
        else fclose($fh);
    }
    /**
     * Reads the contents of a file on disk
     *
     * @param string $file_path absolute path to the file to be read
     *
     * @return string returns the contents of the file
     */
    public function ReadLocalFile($file_path) 
    {
        $fh = fopen($file_path, "r");
        $contents = fread($fh, filesize($file_path));
        fclose($fh);
        return $contents;
    }
    /**
     * Copies the source file to the target file
     *
     * It overwrites the destination file
     *
     * @param string $source_file_name the source file to copy
     * @param string $target_file_name the target file name
     */
    public function CopyFile($source_file_name, $target_file_name) 
    {
        if (!copy($source_file_name, $target_file_name)) throw new \Exception("Source file: " . $source_file_name . " could not be copied to target file: " . $target_file_name);
    }
    /**
     * Copies as uploaded file to a given location. the location is set in the private class variable
     *
     * @param array $file_data data for uploaded file.
     * @throws Exception throws an exception if the file size is greater than a limit
     *   or the file extension is not valid or the uploaded file could not be copied.
     *   The upload limit and valid file extensions are specifed in private class variables
     *
     * @return string $path_of_uploaded_file full path to the uploaded file.
     */
    public function UploadFile($file_data) 
    {
        if (!isset($file_data["name"])) throw new \Exception("No file to upload");
        $max_allowed_file_size = $this->max_allowed_file_size;
        $allowed_extensions = $this->allowed_extensions;
        $size_of_uploaded_file = ceil($file_data['size'] / 1024);
        $file_name = $file_data["name"];
        $type_of_uploaded_file = substr($file_name, strrpos($file_name, ".") + 1);
        if ($size_of_uploaded_file > $max_allowed_file_size) throw new \Exception("Size of file should be less than " . $max_allowed_file_size . " Kb");
        //------ Validate the file extension -----
        $allowed_ext = false;
        for ($i = 0;$i < sizeof($allowed_extensions);$i++) 
        {
            if (strcasecmp($allowed_extensions[$i], $type_of_uploaded_file) == 0) 
            {
                $allowed_ext = true;
            }
        }
        if (!$allowed_ext) throw new \Exception("The uploaded file is not a supported file type. Only the following file types are supported: " . implode(',', $allowed_extensions));
        //copy the temp. uploaded file to uploads folder
        $path_of_uploaded_file = $this->upload_folder . DIRECTORY_SEPARATOR . $file_name;
        $tmp_path = $file_data["tmp_name"];
        /** 
         * If the script is running from browser then the file will be checked is_uploaded_file function
         * This checks if file was uploaded by http post
         * Otherwise script will use is_file function
         *
         */
        if (isset($_SERVER['HTTP_HOST']) && is_uploaded_file($tmp_path) || (!isset($_SERVER['HTTP_HOST']) && is_file($tmp_path))) 
        {
            if (!copy($tmp_path, $path_of_uploaded_file)) 
            {
                throw new \Exception("Error while copying the uploaded file");
            }
        }
        return $path_of_uploaded_file;
    }
    /**
     * Used to get the contents of a url
     *
     * @param string $url url to be fetched
     * @param string $method optional http method. defaults to "get". http method for the request
     * @param array $parameters optional parameters. the data to be sent to the remote server
     * @param array $request_headers the http headers to include in the url request
     * @param boolean $fetch_header indicates that only the http response header is required
     *
     * @return mixed $file_contents. the contents of the file or the http response headers
     */
    public function GetFileContent($url, $method = "GET", $parameters = "", $request_headers = "", $fetch_header = false) 
    {
        /** If the http response headers are required */
        if($fetch_header) {
            $file_contents      = get_headers($url);
        }
        else {
            if ($method == "GET") $file_contents = file_get_contents($url);
            else {
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_URL, $url);
		    if ($parameters != "") curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
		    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		    $file_contents = curl_exec($ch);
		    curl_close($ch);
            }
        }
        
        return $file_contents;
    }
    /**
     * Used to determine if the current internet connection is valid
     *
     * It tries to make a tcp connection to google's server on port 80
     * It returns true if the connection works and false otherwise
     *
     * @return boolean $is_valid indicates if the tcp connection succeeded
     */
    public function IsInternetConnectionValid()
    {
        try {
            /** The connection is made to googles server on port 80 */
            $connected = @fsockopen("www.google.com", 80, $errno, $errstr, 30);
            /** If the connection succeeded */
            if ($connected) {
                $is_valid = true;
                fclose($connected);
            }
            /** If the connection failed */
            else {
                $is_valid = false;
            }
        }
        catch(Exception $e) {
            /** If the connection failed */
            $is_valid = false;
        }
        
        return $is_valid;        
    }
    /**
     * Used to determine if the given url is valid
     *
     * It checks the http response headers for the given url
     * If the response headers contain an error code, i.e 4xx, then function returns false
     * Otherwise the function returns true
     * See http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @param string $url url to be checked
     *
     * @return boolean $is_valid indicates if the given url is valid or not
     */
    public function IsUrlValid($url)
    {
        /** Used to indicate if the url is valid or not */
        $is_valid = false;
        /** The http headers for the url are fetched */
        $url_headers = @get_headers($url);
        /** Each header is checked for http error code */
        for ($count = 0;$count < count($url_headers);$count++) 
        {
            /** The http header */
            $http_header = ($url_headers[$count]);
            /** The http header is checked for 4xx code */
            preg_match("/(http\/1\.[0,1] 4\d\d\s+[a-z]+)/i", $http_header, $matches);
            /** Indicates if the url contains error code or not */
            $is_valid = (isset($matches[0]) && isset($matches[1])) ? false : true;
            /** If the http header contains an error code then no need to check other http headers */
            if (!$is_valid) break;
        }
        return $is_valid;
    }
    /**
     * Used to download and parse the given file
     *
     * It downloads the file from the given url
     * It then converts the file contents into an array
     * Each line is parsed and the field values are extracted
     * CSV and TXT files are supported
     * Field names in csv files must be enclosed with "" and separated with ,
     *
     * @param string $file_url the url of the file
     *
     * @return array $data the contents of the file. each array element contains the data for a line
     */
    public function DownloadAndParseFile($file_url, $local_file_path, $line_parsing_callback) 
    {
        /** The parsed contents of the file */
        $data = array();
        /** The file data containing file name and extension */
        $file_details = \Framework\Utilities\UtilitiesFramework::Factory("strings")->GetFileNameAndExtension($file_url);
        /** The file name */
        $file_name = $file_details['file_name'];
        /** The file name is url decoded */
        $file_name = urldecode($file_name);
        /** The file extension */
        $file_extension = $file_details['file_extension'];
        /** The absolute path to the downloaded file */
        $file_path = $local_file_path . DIRECTORY_SEPARATOR . $file_name;
        /** If the file exists locally then it is read */
        if (is_file($file_path)) 
        {
            $file_contents = $this->ReadLocalFile($file_path);
        }
        /** Otherwise the file contents are fetched and saved to local file */
        else 
        {
            $file_contents = $this->GetFileContent($file_url);
            /** The file contents are saved locally */
            $this->WriteLocalFile($file_contents, $file_path);
        }
        /** The file contents are converted to array */
        $file_contents = explode("\n", $file_contents);
        /** The components of each meta data item is extracted using regular expression */
        for ($count = 0;$count < count($file_contents);$count++) 
        {
            /** The line string */
            $line = trim($file_contents[$count]);
            /** If the line is empty then it is ignored */
            if ($line == '') break;
            /** The parametes for the callback function */
            $parameters[0] = $file_extension;
            $parameters[1] = $line;
            /** The parsed lined. It is generated by the line parsing callback function */
            $parsed_line = call_user_func_array($line_parsing_callback, $parameters);
            /** If the line was successfully parsed then it is appended to the data */
            if ($parsed_line) $data[] = $parsed_line;
        }
        return $data;
    }
    /**
     * Used to recursively copy a folder
     *
     * It copies the contents of a folder recursively from source folder to destination folder
     *
     * @param string $source_folder the full path to the source folder
     * @param string $target_folder the full path to the target folder
     * @param boolean $is_recursive used to indicate if the folder should be copied recursively
     */
    public function CopyFolder($source_folder, $target_folder, $is_recursive)
    {
        /** The folder contents are fetched recursively */
        $folder_contents = $this->GetFolderContents($source_folder, $is_recursive, "", "", true) ;
        /** For each file/folder in the source folder */
        for ($count = 0; $count < count($folder_contents); $count++) {
            /** The source item name */            
            $source_item_name   = $folder_contents[$count];
            /** The source folder is replaced with target folder */
            $target_item_name   = str_replace($source_folder, $target_folder, $source_item_name);
            /** If the item is a folder then it is created in the target folder */
            if (is_dir($source_item_name)) mkdir($target_item_name);
            /** If the item is a file and the target file does not exist, then it is copied to target folder */
            if (is_file($source_item_name) && !is_file($target_item_name)) copy($source_item_name, $target_item_name);
        }
    }
}
