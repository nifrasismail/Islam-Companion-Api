<?php
namespace IslamCompanionApi\Scripts;
use \Framework\Configuration\Base as Base;
use \Framework\Object\MysqlDataObject as MysqlDataObject;
/**
 * This class implements the functionality of the Hadith Data Import
 *
 * It contains functions that are used to import hadith data from pdf to mysql database
 * It also contains function for downloading hadith data from hadithcollection.com website
 *
 * @category   IslamCompanionApi
 * @package    IslamCompanionApi
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class ImportHadithData extends Base
{
    /**
     * Used to verify the hadith count for the ahadith downloaded from www.hadithcollection.com
     *
     * It checks the hadith count for each hadith source
     * It compares the hadith count with the count of hadith stored in database
     * It throws an exception if there the counts do not match
     */
    public function VerifyHadithCollectionData() 
    {
        /** The list of hadith source urls */
        $hadith_source_urls = array(
            "Sahih Bukhari" => "http://www.hadithcollection.com/sahihbukhari.html",
            "Sahih Muslim" => "http://www.hadithcollection.com/sahihmuslim.html",
            "Maliks Muwatta" => "http://www.hadithcollection.com/maliksmuwatta.html",
            "Shamaa-il Tirmidhi" => "http://www.hadithcollection.com/shama-iltirmidhi.html",
            "Abu Dawud" => "http://www.hadithcollection.com/abudawud.html",
            "Hadith Qudsi" => "http://www.hadithcollection.com/hadith-qudsi.html",
            "An Nawawi's Fourty Hadiths" => "http://www.hadithcollection.com/an-nawawis-forty-hadith.html",
            "Authentic Supplications of the Prophet" => "http://www.hadithcollection.com/authentic-supplications-of-the-prophet.html"
        );
        /** Each source is checked */
        foreach ($hadith_source_urls as $hadith_source => $url) 
        {
            /** The application configuration is fetched */
            $configuration = $this->GetConfigurationObject();
            /** The configuration object is fetched */
            $parameters['configuration'] = $configuration;
            /** The mysql data object is created */
            $mysql_data_object = new MysqlDataObject($parameters);
            /** The mysql table name */
            $table_name = $this->GetConfig("general", "mysql_table_names", "hadith_english");
            /** The table name is set */
            $mysql_data_object->SetTableName($table_name);
            /** The key field is set */
            $mysql_data_object->SetKeyField("id");
            /** The mysql data object is set to read/write */
            $mysql_data_object->SetReadOnly(true);
            /** The condition used to read the data from database */
            $condition = array(
                array(
                    "field" => "source",
                    "value" => "%" . $hadith_source . "%",
                    "operator" => "",
                    "operation" => "LIKE"
                )
            );
            /** The parameters used to read the data */
            $parameters = array(
                "fields" => "count(*) as total",
                "condition" => $condition,
                "read_all" => false
            );
            /** The Mysql data is read from database */
            $mysql_data_object->Read($parameters);
            /** The data is fetched */
            $data = $mysql_data_object->GetData(true);   
            /** The total number of ahadith in database */
            $total_ahadith_in_database = $data['total'];                     
            /** The file contents */
            $file_contents = $this->GetComponent("filesystem")->GetFileContent($url);  
            /** The file count is checked */
            preg_match_all('/<span class="badge badge-info tip hasTooltip" title="Hadith Count:"> (\d+) <\/span> <\/h3>/iU', $file_contents, $matches);
            /** The total number of ahadith */
            $total = 0;
            /** The total is calculated */
            for ($count = 0; $count < count($matches[1]); $count++)
            {
                $total += $matches[1][$count];
            }
            /** The total in database is compared with total on website */
            echo("The total number of ahadith for the source: ". $hadith_source . "\nAhadith in database: " . $total_ahadith_in_database . "\nAhadith on website: ". $total . "\n\n");            
        }
    }
    /** 
     * Used to import the Hadith data from text files to database
     *
     * It scans the ahadith folder recursively and reads each text file
     * For each text file, it imports the data to database
     */
    public function ImportTextFiles() 
    {
        /** The txt folder path */
        $folder_path = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "ahadith";
        /** The ahadith folder is read */
        $file_list = $this->GetComponent("filesystem")->GetFolderContents($folder_path, true, ".txt", ".pdf");
        /** Each text file is read */
        for ($count = 0;$count < count($file_list);$count++) 
        {
            /** The source file name */
            $source_file_name = $file_list[$count];
            /** Indicates the language of the file */
            $language = (strpos($source_file_name, "urdu") !== false) ? "urdu_arabic" : "english";
            /** The file contents */
            $file_contents = $this->GetComponent("filesystem")->GetFileContent($source_file_name);
            //if(strpos($source_file_name, "Imam_Nawawi_Hadith") === false) continue;
            /** If the language for the hadith is English */
            if ($language == "english") $this->SaveHadithBookEnglish($file_contents, $source_file_name);
            /** If the language for the hadith is Urdu */
            //if ($language == "urdu_arabic") $this->SaveHadithBookUrdu($file_contents, $source_file_name);
            
        }
    }
    /**
     * Used to parse the ahadith urdu book data
     *
     * It parses the book data and returns the parsed data
     *
     * @param $text the hadith text
     *
     * @return array $ahadith_book_data the parsed ahadith book data
     */
    private function ParseAhadithUrduBookData($text) 
    {
        /** The data is split on newline */
        $data = explode("\n", $text);
        /** Indicates that text has started */
        $text_started = false;
        /** The book name */
        $book_name = "";
        /** The ahadith source */
        $source = "";
        /** The ahadith text data. It contains data for all hadith */
        $ahadith_text_data = array();
        /** The hadith text information. It contains data for single hadith */
        $hadith_text_information = array();
        /** Each data item is checked */
        for ($count = 0;$count < count($data);$count++) 
        {
            /** A single line of text */
            $line_text = trim($data[$count]);
            /** The chapter line is checked */
            if (mb_strpos($line_str,"‫‪:‬‬ ‫ابب‬") !== false) {
                echo $line_text;exit;
            }            
        }
        /** The last ahadith in the file is added */
        $ahadith_text_data[] = $hadith_text_information;
        /** The ahadith data is set */
        $ahadith_book_data = array(
            "source" => $source,
            "book_name" => $book_name,
            "text" => $ahadith_text_data
        );
        return $ahadith_book_data;
    }
    /**
     * Used to store the contents of the ahadith data to database
     *
     * It stores each hadith to database
     *
     * @param $text the hadith text
     * @param $file_name the hadith text file name
     */
    private function SaveHadithBookUrdu($text, $file_name) 
    {
        /** The text is parsed line by line */
        $ahadith_book_data = $this->ParseAhadithUrduBookData($text);
        /** Each hadith data is saved */
        for ($count = 0;$count < count($ahadith_book_data['text']);$count++) 
        {
            /** The application configuration is fetched */
            $configuration = $this->GetConfigurationObject();
            /** The configuration object is fetched */
            $parameters['configuration'] = $configuration;
            /** The mysql data object is created */
            $mysql_data_object = new MysqlDataObject($parameters);
            /** The mysql table name */
            $table_name = $this->GetConfig("general", "mysql_table_names", "hadith_urdu");
            /** The table name is set */
            $mysql_data_object->SetTableName($table_name);
            /** The key field is set */
            $mysql_data_object->SetKeyField("id");
            /** The mysql data object is set to read/write */
            $mysql_data_object->SetReadOnly(false);            
            /** The hadith data */
            $hadith_data = array(
                "source" => $ahadith_book_data['source'],
                "book" => $ahadith_book_data['book_name'],
                "book_number" => $ahadith_book_data['text'][$count]['book_number'],
                "title" => $ahadith_book_data['text'][$count]['title'],
                "hadith_number" => $ahadith_book_data['text'][$count]['hadith_number'],
                "hadith_text" => implode(" ", $ahadith_book_data['text'][$count]['hadith_text'])
            );
            /** The hadith data is set to the MysqlDataObject */
            $mysql_data_object->SetData($hadith_data);
            /** The mysql data is saved to database */
            $mysql_data_object->Save();
            /** The user is informed that data is saved to database */
            echo "Saved Hadith : " . $ahadith_book_data['text'][$count]['book_number'] . ":" . $ahadith_book_data['text'][$count]['hadith_number'] . " to database\n";
        }
    }
    /**
     * Used to parse the ahadith english book data
     *
     * It parses the book data and returns the parsed data
     *
     * @param $text the hadith text
     *
     * @return array $ahadith_book_data the parsed ahadith book data
     */
    private function ParseAhadithEnglishBookData($text) 
    {
        /** The data is split on newline */
        $data = explode("\n", $text);
        /** Indicates that text has started */
        $text_started = false;
        /** The book name */
        $book_name = "";
        /** The ahadith source */
        $source = "";
        /** The ahadith text data. It contains data for all hadith */
        $ahadith_text_data = array();
        /** The hadith text information. It contains data for single hadith */
        $hadith_text_information = array();
        /** Each data item is checked */
        for ($count = 0;$count < count($data);$count++) 
        {
            /** A single line of text */
            $line_text = trim($data[$count]);
            /** The 'ã' is replaced with 'a' */
            $line_text = str_replace("ã", "a", $line_text);
            $line_text = str_replace("", "", $line_text);            
            /** If the line contains non printable characters */
            if (!ctype_print($line_text)) continue;
            /** If the line contains text */
            if ($line_text != "" && !$text_started) 
            {
                $text_started = true;
                /** The source is set */
                $source = $line_text;
                /** The counter is increased */
                $count++;
                /** The next line is read */
                $line_text = $data[$count];
                /** If the book name is given in the next line */
                if (strpos($line_text, "Book") !== false || strpos($line_text, "Chapter") !== false) 
                {
                    $book_name = $line_text;
                }
                /** If the book name is not given in the next line */
                else 
                {
                    $book_name = $source;
                    $count--;
                }
            }
            /** If the book name and book source have been parsed */
            else 
            {
                /** The line is checked for narrator */
                preg_match("/([a-zA-Z0-9]{2,5}) : ([a-zA-Z0-9]{2,5}) : (.+)/i", $line_text, $matches);
                /** If the narrator was found */
                if (isset($matches[3])) 
                {
                    /** If the hadith text data is set */
                    if (isset($hadith_text_information['hadith_text'])) 
                    {
                        $ahadith_text_data[] = $hadith_text_information;
                        /** The hadith text information. It contains data for single hadith */
                        $hadith_text_information = array();
                    }
                    $hadith_text_information['book_number'] = $matches[1];
                    $hadith_text_information['hadith_number'] = $matches[2];
                    $hadith_text_information['title'] = $matches[3];
                }
                else
                {
                    /** 
                     * If the line does not contain page number and it does not contain url www.hadithcollection.com
                     * and the length of the string is greater than 2, then it is added to the hadith text list
                     */
                    if (strpos($line_text, "Page") === false && strpos($line_text, "www.hadithcollection.com") === false && ctype_print($line_text)) 
                    {
                        if (!isset($hadith_text_information['hadith_text'])) $hadith_text_information['hadith_text'] = array(
                            $line_text
                        );
                        else $hadith_text_information['hadith_text'][] = $line_text;
                    }
                }
            }
        }
        /** The last ahadith in the file is added */
        $ahadith_text_data[] = $hadith_text_information;
        /** The ahadith data is set */
        $ahadith_book_data = array(
            "source" => $source,
            "book_name" => $book_name,
            "text" => $ahadith_text_data
        );
        return $ahadith_book_data;
    }
    /**
     * Used to store the contents of the ahadith data to database
     *
     * It stores each hadith to database
     *
     * @param $text the hadith text
     * @param $file_name the hadith text file name
     */
    private function SaveHadithBookEnglish($text, $file_name) 
    {
        /** The text is parsed line by line */
        $ahadith_book_data = $this->ParseAhadithEnglishBookData($text);
        /** Each hadith data is saved */
        for ($count = 0;$count < count($ahadith_book_data['text']);$count++) 
        {
            /** The application configuration is fetched */
            $configuration = $this->GetConfigurationObject();
            /** The configuration object is fetched */
            $parameters['configuration'] = $configuration;
            /** The mysql data object is created */
            $mysql_data_object = new MysqlDataObject($parameters);
            /** The mysql table name */
            $table_name = $this->GetConfig("general", "mysql_table_names", "hadith_english");
            /** The table name is set */
            $mysql_data_object->SetTableName($table_name);
            /** The key field is set */
            $mysql_data_object->SetKeyField("id");
            /** The mysql data object is set to read/write */
            $mysql_data_object->SetReadOnly(false);
            /** If the hadith data is missing then the file name is displayed */
            if (!isset($ahadith_book_data['text'][$count]['book_number'])) 
            {
                echo $file_name . "\n";
                print_R($ahadith_book_data);
                exit;
            }
            //continue;
            
            /** The hadith data */
            $hadith_data = array(
                "source" => $ahadith_book_data['source'],
                "book" => $ahadith_book_data['book_name'],
                "book_number" => $ahadith_book_data['text'][$count]['book_number'],
                "title" => $ahadith_book_data['text'][$count]['title'],
                "hadith_number" => $ahadith_book_data['text'][$count]['hadith_number'],
                "hadith_text" => implode(" ", $ahadith_book_data['text'][$count]['hadith_text'])
            );
            /** The hadith data is set to the MysqlDataObject */
            $mysql_data_object->SetData($hadith_data);
            /** The mysql data is saved to database */
            $mysql_data_object->Save();
            /** The user is informed that data is saved to database */
            echo "Saved Hadith : " . $ahadith_book_data['text'][$count]['book_number'] . ":" . $ahadith_book_data['text'][$count]['hadith_number'] . " to database\n";
        }
    }
    /**
     * Used to convert Hadith pdf files to text files
     *
     * It uses pdftotext application for converting pdf file to text file
     * The text file is saved in same folder as the pdf file
     */
    public function ConvertPdfFiles() 
    {
        /** The pdf folder path */
        $folder_path = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "ahadith" . DIRECTORY_SEPARATOR . "urdu";
        /** The ahadith folder is read */
        $file_list = $this->GetComponent("filesystem")->GetFolderContents($folder_path, true, ".pdf", ".txt");
        /** Each pdf file is converted to text file */
        for ($count = 0;$count < count($file_list);$count++) 
        {
            /** The source file name */
            $source_file_name = $file_list[$count];
            /** The destination file name */
            $destination_file_name = str_replace(".pdf", ".txt", $source_file_name);
            /** The current file name */
            echo "Converting file: " . $source_file_name . "\n";
            /** The pdf file is converted to txt file using pdftotext tool */
            exec('pdftotext "' . $source_file_name . '" "' . $destination_file_name . '"');
        }
    }
    /**
     * Used to download Hadith data
     *
     * It downloads hadith data from hadithcollection.com
     */
    public function DownloadHadithData() 
    {
        $hadith_list = array(
            "Abu_Dawud" => "http://www.hadithcollection.com/download-abu-dawud.html~36",
            "Authentic_Supplications" => "http://www.hadithcollection.com/download-authentic-supplications-of-rasulullah.html~1",
            "Hadith_Qudsi" => "http://www.hadithcollection.com/download-hadith-qudsi.html~1",
            "Imam_Nawawi_Hadith" => "http://www.hadithcollection.com/download-an-nawawis-40-hadith.html~1",
            "Maliks_Muwatta" => "http://www.hadithcollection.com/download-maliks-muwatta.html~61",
            "Sahih_Bukhari" => "http://www.hadithcollection.com/download-sahih-bukhari.html~93",
            "Sahih_Muslim" => "http://www.hadithcollection.com/download-sahih-muslim.html~43",
            "Tirmidhi" => "http://www.hadithcollection.com/download-shama-il-tirmidhi.html~55"
        );
        /** Each hadith collection is downloaded to separate folder */
        foreach ($hadith_list as $folder_name => $download_details) 
        {
            /** The hadith collection base url and number of files to download */
            list($url, $file_count) = explode("~", $download_details);
            /** All the pages are downloaded */
            for ($count = 0;$count < ceil($file_count / 15);$count++) 
            {
                /** The page download url */
                $download_url = $url . "?start=" . ($count * 15);
                /** The page is downloaded */
                $page_contents = file_get_contents($download_url);
                /** The pdf links in the page are extracted */
                preg_match_all('/<a class="" href="(.+)">(.+pdf)<\/a>/iU', $page_contents, $matches);
                /** Each extracted link is downloaded */
                for ($count1 = 0;$count1 < count($matches[0]);$count1++) 
                {
                    /** The name of the pdf file */
                    $file_name = $matches[2][$count1];
                    /** The download url of the pdf file */
                    $pdf_file_url = "http://hadithcollection.com" . $matches[1][$count1];
                    /** The pdf file is downloaded and its contents are saved to the folder */
                    $file_contents = file_get_contents($pdf_file_url);
                    /** The pdf folder path */
                    $folder_path = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "ahadith" . DIRECTORY_SEPARATOR . "english" . DIRECTORY_SEPARATOR . $folder_name;
                    /** The absolute file path */
                    $file_path = $folder_path . DIRECTORY_SEPARATOR . $file_name;
                    /** The file is saved */
                    $fh = fopen($file_path, "w");
                    fwrite($fh, $file_contents);
                    fclose($fh);
                    echo "Downloaded file: " . $file_name . "\n";
                    flush();
                    sleep(2);
                }
            }
        }
    }
}

