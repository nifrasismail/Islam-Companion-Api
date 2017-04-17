<?php

namespace IslamCompanionApi\DataObjects;
use \IslamCompanionApi\DataObjects\Authors as Authors;

/**
 * This class implements the Hadith class
 *
 * An object of this class allows access to Hadith
 * The hadith can be fetched using different criteria
 * For example set of hadith for given hadith source, language and book
 *
 * @category   IslamCompanionApi
 * @package    DataObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class Hadith extends \Framework\Object\DataObjectAbstraction
{
    /**
     * The total number of hadith for each hadith type
     */
    private static $hadith_details = array(
        "Sahih Muslim" => 6242,
        "Sahih Bukhari" => 6328,
        "Abu Dawud" => 3172,
        "Authentic Supplications of the Prophet" => 255,
        "Hadith Qudsi" => 41,
        "An Nawawi's Fourty Hadiths" => 42,
        "Maliks Muwatta" => 1541,
        "Shamaa-il Tirmidhi" => 396
    );
    /**
     * Used to get the hadith source names
     *
     * It returns the hadith source names
     *
     * @return array $hadith_source_names the list of hadith source names
     */
    public static function GetHadithSourceNames() 
    {
        /** The hadith source names */
        $hadith_source_names = array_keys(self::$hadith_details);
        
        return $hadith_source_names;
    }
    /**
     * Used to get the hadith books total
     *
     * It returns the total number of hadith books in all the hadith sources
     *
     * @param int $total_hadith_books_count the total number of hadiths books
     */
    public static function GetTotalHadithBooksCount() 
    {
        /** The total hadith books count */
        $total_hadith_books_count = 276;

        return $total_hadith_books_count;
    }
    /**
     * Used to get the hadith total
     *
     * It returns the total number of hadith in all the hadith sources
     */
    public static function GetTotalHadithCount() 
    {
        /** The total hadith count */
        $total_hadith_count = 0;
        /** The hadith source totals */
        $hadith_source_totals = array_values(self::$hadith_details);
        /** Each hadith item is added to the total */
        for ($count = 0;$count < count($hadith_source_totals);$count++) 
        {
            $total_hadith_count+= $hadith_source_totals[$count];
        }
        return $total_hadith_count;
    }
    /**
     * Used to get the hadith text
     *
     * It fetches all the hadith text
     * For given hadith source, hadith language, hadith book and hadith title
     *
     * @param string $hadith_source the hadith sura
     * @param string $hadith_language the hadith language
     * @param string $hadith_book the hadith book number
     * @param string $hadith_title the current hadith title
     *
     * @param array $hadith_text the hadith text
     */
    public function GetHadithText($hadith_source, $hadith_language, $hadith_book, $hadith_title) 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The data type or short table name to use */
        $data_type = "hadith";
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $data_type,
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The condition for fetching the hadith data */
        $where_clause = array(
            array(
                'field' => "source",
                'value' => $hadith_source,
                'operation' => "=",
                'operator' => "AND"
            ) ,
            array(
                'field' => "book",
                'value' => $hadith_book,
                'operation' => "=",
                'operator' => "AND"
            ) ,
            array(
                'field' => "title",
                'value' => $hadith_title,
                'operation' => "=",
                'operator' => ""
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $where_clause,
            "read_all" => true,
            "order" => array(
                "field" => "id",
                "type" => "int",
                "direction" => "ASC"
            )
        );
        /** The meta data is read */
        $this->Read($parameters);
        /** The hadith text data is fetched */
        $hadith_text = $this->GetData();
 
        return $hadith_text;
    }
    /**
     * Used to get the hadith text
     *
     * It fetches all the hadith text that contains the given search text
     *
     * @param string $search_text the text used to search the hadith
     * @param string $hadith_language [English] the hadith language
     * @param string $order [sequence~random] the ordering for the search results
     * @param int $start the index of the first hadith to fetch
     * @param int $limit the number of hadith to fetch
     * @param array $where_clause optional the condition used to search for the hadith. if empty, then the hadith_text field and book fields are searched     
     *          
     * @return array $hadith_data the hadith data
     *    hadith_text => array the hadith text
     *    hadith_count => int the total number of hadith
     */
    public function SearchHadith($search_text, $hadith_language = "English", $order = "sequence", $start = "-1", $limit = "-1", $where_clause = false) 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The data type or short table name to use */
        $data_type = "hadith";
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $data_type,
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** If the where clause is not given */
        if (!$where_clause) {
            /** If the search text is empty then the where clause is set to false */
            if ($search_text == "") {
                /** The where clause is set to false */
                $where_clause = false;
            }
            else {
                /** The condition for fetching the hadith data */
                $where_clause = array(
                    array(
                        'field' => "book",
                        'value' => "%" . $search_text . "%",
                        'operation' => " LIKE ",
                        'operator' => " OR "
                    ),
                    array(
                        'field' => "hadith_text",
                        'value' => "%" . $search_text . "%",
                        'operation' => " LIKE ",
                        'operator' => ""
                    ));
            }
       }
       /** If the order is set to sequence or it is empty */
       if ($order == "" || $order == "sequence") {
           $order_by = array("field" => "id", "type" => "numeric", "direction" => "ASC");
       }
       /** If the order is set to random */
       else
           $order_by = "random";
      
       /** The parameters used to read the data from database */
       $parameters = array(
           "fields" => "*",
           "condition" => $where_clause,
           "read_all" => true,
           "order" => $order_by
       );
       /** The meta data is read */
       $this->Read($parameters);
       /** The hadith data is fetched */
       $hadith_data  = $this->GetData();
       /** The hadith count */
       $hadith_count = count($hadith_data);
       /** If the start and limit parameters are given */
       if ($start > "-1" && $limit > "-1") {
           /** The required section of the hadith data is fetched */
           $hadith_data  = array_slice($hadith_data, $start, $limit);
       }
       /** The required hadith data */
       $hadith_data = array("hadith_text" => $hadith_data, "hadith_count" => $hadith_count);
       
       return $hadith_data;
    }
    /**
     * Used to get the hadith meta information
     *
     * It fetches the hadith meta information
     * It fetches the hadith book and title information for each hadith source    
     */
    public function GetHadithMetaInformation() 
    {
        /** The required hadith meta data */
        $hadith_meta = array();
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The data type or short table name to use */
        $data_type = "hadith";
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $data_type,
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The list of hadith sources is fetched */
        $hadith_source_list = array_values(Hadith::GetHadithSourceNames());
        /** Data for each hadith source is fetched */
        for ($count1 = 0; $count1 < count($hadith_source_list); $count1++) {
            /** The hadith source */
            $hadith_source = $hadith_source_list[$count1];
            /** The condition for fetching the hadith data */
            $where_clause = array(
                array(
                    'field' => "source",
                    'value' => $hadith_source,
                    'operation' => "=",
                    'operator' => ""
                )
            );
            /** The parameters used to read the data from database */
            $parameters = array(
                    "fields" => "*",
                    "condition" => $where_clause,
                    "read_all" => true,
                    "order" => array(
                        "field" => "id",
                        "type" => "int",
                        "direction" => "ASC"
                    )
            );
            /** The meta data is read */
            $this->Read($parameters);
            /** The hadith data for the source is fetched */
            $hadith_in_source = $this->GetData();
            /** Each hadith book in source is checked */
            for ($count2 = 0; $count2 < count($hadith_in_source); $count2++) {
                /** The hadith data */
                $hadith_data = $hadith_in_source[$count2];
                if (!isset($hadith_meta[$hadith_source][$hadith_data['book']]))
                    $hadith_meta[$hadith_source][$hadith_data['book']] = array();
                else if (!in_array($hadith_data['title'], $hadith_meta[$hadith_source][$hadith_data['book']]))
                    $hadith_meta[$hadith_source][$hadith_data['book']][] = $hadith_data['title'];
            }
        }

        return $hadith_meta;
    }
    /**
     * Used to get the book titles
     *
     * It fetches the list of all book titles
     * For given hadith source, hadith language and hadith book
     *
     * @param string $hadith_source the hadith sura
     * @param string $hadith_language the hadith language
     * @param string $hadith_book the hadith book number
     * @param string $hadith_title the current hadith title
     *
     * @return array $book_titles the list of book titles
     */
    public function GetBookTitles($hadith_source, $hadith_language, $hadith_book) 
    {
        /** The hadith data */
        $book_titles = array();
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The data type or short table name to use */
        $data_type = "hadith";
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $data_type,
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The condition for fetching the hadith data */
        $where_clause = array(
            array(
                'field' => "source",
                'value' => $hadith_source,
                'operation' => "=",
                'operator' => "AND"
            ) ,
            array(
                'field' => "book",
                'value' => $hadith_book,
                'operation' => "=",
                'operator' => ""
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "DISTINCT(title) as title",
            "condition" => $where_clause,
            "read_all" => true,
            "order" => array(
                "field" => "title",
                "type" => "string",
                "direction" => "ASC"
            )
        );
        /** The meta data is read */
        $this->Read($parameters);
        /** The hadith title data is fetched */
        $book_titles = $this->GetData();
        return $book_titles;
    }
    /**
     * Used to get the books
     *
     * It fetches the list of all books
     * For given hadith source and hadith language
     *
     * @param string $hadith_source the hadith sura
     * @param string $hadith_language the hadith language
     *
     * @param array $books the list of books
     */
    public function GetBooks($hadith_source, $hadith_language) 
    {
        /** The hadith data */
        $book_titles = array();
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The data type or short table name to use */
        $data_type = "books";
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $data_type,
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The condition for fetching the hadith data */
        $where_clause = array(
            array(
                'field' => "source",
                'value' => $hadith_source,
                'operation' => "=",
                'operator' => ""
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "book",
            "condition" => $where_clause,
            "read_all" => true,
            "order" => array(
                "field" => "book_number",
                "type" => "int",
                "direction" => "ASC"
            )
        );
        /** The meta data is read */
        $this->Read($parameters);
        /** The hadith book data is fetched */
        $books = $this->GetData();
        return $books;
    }
}

