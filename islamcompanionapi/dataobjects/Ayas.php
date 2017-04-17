<?php
namespace IslamCompanionApi\DataObjects;
/**
 * This class implements the Ayas class
 *
 * An object of this class allows access to Holy Quran ayas
 * It provides functions for retreiving Holy Quran ayas
 *
 * @category   IslamCompanionApi
 * @package    DataObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class Ayas extends \Framework\Object\DataObjectAbstraction
{
    /**
     * Used to get the ayas in the given sura
     *
     * It fetches the list of ayas for the given sura,start aya and end aya
     *
     * @param string $sura the sura
     * @param string $start_ayat the start ayat. if it is less than or equal to 0 then it is not considered
     * @param string $end_ayat the end ayat. if it is less than or equal to 0 then it is not considered
     *
     * @return array $ayat_list the list of ayas in the given sura starting from start aya and ending at end aya
     *    id => the ayat id
     *    sura => the sura number
     *    ayat_id => the ayat number
     *    sura_ayat_id => the sura ayat number
     *    translated_text => the ayat translation
     *    arabic_text => the ayat text in arabic
     *    checksum_arabic => the checksum of arabic text
     *    checksum_translated => the checksum of translated text
     *    created_on => the date at which ayat was added to database
     */
    public function GetAyasInSura($sura, $start_ayat, $end_ayat) 
    {
        /** The data type to use */
        $data_type = array("aya", "arabic_aya");
        /** The required ayat list */
        $ayat_list = array();
        /** The data for each data type is fetched */
        for ($count = 0; $count < count($data_type); $count++) {
            /** The meta information used to fetch data */
            $meta_information = array(
                "data_type" => $data_type[$count],
                "key_field" => ""
            );
            /** The table name and field name are set */
            $this->SetMetaInformation($meta_information);
            /** The where clause used to fetch division data from database */
            $where_clause = array(
                array(
                    'field' => "sura",
                    'value' => $sura,
                    'operation' => "=",
                    'operator' => "AND"
                )
            );
           /** If both start ayat and end ayat are not given then the trailing AND is removed from the sura where clause */
           if ($start_ayat <= 0 && $end_ayat <= 0) {
               $where_clause[0]['operator'] = "";
            }
           /** If the start ayat is greater than 0, then the where clause is updated */
           if ($start_ayat > 0) {
               $where_clause[] = array(
                   'field' => "sura_ayat_id",
                   'value' => $start_ayat,
                   'operation' => ">=",
                   'operator' => "AND"
               );
           }
           /** If the end ayat is greater than 0, then the where clause is updated */
           if ($end_ayat > 0) {
               $where_clause[] = array(
                   'field' => "sura_ayat_id",
                   'value' => $end_ayat,
                   'operation' => "<=",
                   'operator' => ""
               );
           }
           /** The parameters used to read the data from database */
           $parameters = array(
               "fields" => "*",
               "condition" => $where_clause,
               "read_all" => true,
               "order" => array(
                   "field" => "sura_ayat_id",
                   "type" => "numeric",
                   "direction" => "ASC"
               )
           );
           /** The ayat information is read */
           $this->Read($parameters);
           /** The ayat data */
           $ayat_list[] = $this->GetData();           
        }        
        /** The ayat list is merged with the current ayat list */
        for ($count = 0; $count < count($ayat_list[0]); $count++) {
            /** The arabic text is copied */
            $ayat_list[0][$count]['arabic_text']         = $ayat_list[1][$count]['arabic_text'];
            /** The arabic ayat checksum is copied */
            $ayat_list[0][$count]['checksum_arabic']     = $ayat_list[1][$count]['checksum'];
            /** The check sum field is renamed */
            $ayat_list[0][$count]['checksum_translated'] = $ayat_list[0][$count]['checksum']; 
            unset($ayat_list[0][$count]['checksum']);
        }
        /** The ayat list is set to the first ayat list */
        $ayat_list  = $ayat_list[0];

        return $ayat_list;
    }
    /**
     * Used to get the aya meta data information
     *
     * It fetches the ayat details for the given ayas
     *
     * @param int $ayat_id the id of the ayat for which the meta data is required
     *
     * @return array $ayat_list the list of ayas
     */
    public function GetAyatMetaData($ayat_id) 
    {
        /** The data type to use */
        $data_type = "meta";
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $data_type,
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The where clause used to fetch division data from database */
        $where_clause = array(
            array(
                'field' => "ayat_id",
                'value' => $ayat_id,
                'operation' => " = ",
                'operator' => "",
                'is_string' => false
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $where_clause,
            "read_all" => true,
            "order" => array(
                "field" => "ayat_id",
                "type" => "numeric",
                "direction" => "ASC"
            )
        );
        /** The ayat information is read */
        $this->Read($parameters);
        /** The ayat data */
        $ayat_list = $this->GetData();

        return $ayat_list;
    }
    /**
     * Used to search the given ayas
     *
     * It fetches the list of ayas for the given search text
     *
     * @param string $search_text the search text
     * @param boolean $get_translated_text indicates if the translated text should be fetched
     * @param string $order optional [sequence~random] the ordering for the search results
     * @param array $where_clause optional the condition used to search for the ayas. if empty, then the translated_text field is searched
     *
     * @return array $ayat_list the list of ayas containing the given search text
     *    id => the ayat id
     *    sura => the sura number
     *    ayat => the sura ayat number
     *    file_name => the name of the text file containing the ayas
     *    created_on => the date at which ayat was added to database
     */
    public function SearchAyas($search_text, $get_translated_text, $order = "sequence", $where_clause = false) 
    {
        /** The data type to use */
        $data_type = ($get_translated_text) ? "aya" : "arabic_aya";
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $data_type,
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** If the where clause is not given */
        if (!$where_clause) {
            /** The where clause used to fetch division data from database */
            $where_clause = array(
                array(
                    'field' => "translated_text",
                    'value' => "%" . $search_text . "%",
                    'operation' => " LIKE "
                )
            );
            /** If the search text is empty then the where clause is set to false */
            if ($search_text == "") $where_clause = false;
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
        /** The ayat information is read */
        $this->Read($parameters);
        /** The ayat data */
        $ayat_list = $this->GetData();

        return $ayat_list;
    }
}

