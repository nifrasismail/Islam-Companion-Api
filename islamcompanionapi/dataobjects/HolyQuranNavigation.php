<?php
namespace IslamCompanionApi\DataObjects;
/**
 * This class implements the HolyQuranNavigation class
 *
 * An object of this class contains functions for handling different Holy Quran navigation actions
 * For example a function that returns the sura, ruku and division number when a sura is selected
 *
 * @category   IslamCompanionApi
 * @package    DataObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class HolyQuranNavigation extends \Framework\Object\DataObjectAbstraction
{
    /**
     * Used to determine the ruku and ayat when a sura is selected
     *
     * It fetches the start ruku number and start ayat when the given sura is selected
     * For the given division and division number
     *
     * @param string $sura the sura id
     * @param string $division_number the division number
     * @param string $division the division     
     *
     * @return array $ruku_ayat an array with 2 keys:
     * ruku => the id of the ruku
     * ayat => the ayat number. this will be equal to 1
     */
    public function SuraSelection($sura, $division_number, $division) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "meta",
            "key_field" => ""
        );
        /** If the division is ruku, then it is set to sura ruku */
        if ($division == "ruku") {$division = "sura_ruku";$division_number = "1";}
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The where clause used to fetch division data from database */
        $where_clause = array(
            array(
                'field' => "sura",
                'value' => $sura,
                'operation' => "=",
                'operator' => "AND"
            ) ,
            array(
                'field' => $division,
                'value' => $division_number,
                'operation' => "=",
                'operator' => ""
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $where_clause,
            "read_all" => false,
            "order" => array(
                "field" => "sura_ayat_id",
                "type" => "numeric",
                "direction" => "ASC"
            )
        );
        /** The sura information is read */
        $this->Read($parameters);
        /** The sura data */
        $sura_data = $this->GetData();
        /** The ruku and ayat data */
        $ruku_ayat = array();
        /** The ruku data */
        $ruku_ayat['ruku'] = $sura_data['ruku'];
        /** The ayat data */
        $ruku_ayat['ayat'] = $sura_data['sura_ayat_id'];
        return $ruku_ayat;
    }
    /**
     * Used to determine the ayat when a ruku is selected
     *
     * It fetches the start ayat when the given ruku is selected
     *
     * @param string $ruku the ruku id
     * @param string $sura the sura id
     *
     * @return int $start_ayat the start ayat of the ruku
     */
    public function RukuSelection($ruku, $sura) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "meta",
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
            ) ,
            array(
                'field' => "ruku",
                'value' => $ruku,
                'operation' => "=",
                'operator' => ""
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $where_clause,
            "read_all" => false,
            "order" => array(
                "field" => "sura_ayat_id",
                "type" => "numeric",
                "direction" => "ASC"
            )
        );
        /** The ayat information is read */
        $this->Read($parameters);
        /** The ruku data */
        $ruku_data = $this->GetData();
        /** The start ayat of the ruku */
        $start_ayat = $ruku_data['sura_ayat_id'];
        return $start_ayat;
    }
    /**
     * Used to determine the sura, ruku and ayat when a division number is selected
     *
     * It fetches the start ayat,ruku and sura when the given division number is selected
     *
     * @param string $division_number the division number
     * @param string $division the division
     *
     * @return array $division_information an array with 3 keys:
     * ayat => the start ayat of the first ruku that lies in the given division number and sura
     * ruku => the ruku number of the first ruku that lies in the given division number and sura
     * sura => the sura number of the first sura that lies in the given division number
     */
    public function DivisionNumberSelection($division_number, $division) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "meta",
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The where clause used to fetch division data from database */
        $where_clause = array(
            array(
                'field' => $division,
                'value' => $division_number,
                'operation' => "=",
                'operator' => ""
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $where_clause,
            "read_all" => false,
            "order" => array(
                "field" => "sura_ayat_id",
                "type" => "numeric",
                "direction" => "ASC"
            )
        );
        /** The ayat information is read */
        $this->Read($parameters);
        /** The division number data */
        $division_number_data = $this->GetData();
        /** The division information */
        $division_information = array(
            "ayat" => $division_number_data['sura_ayat_id'],
            "ruku" => $division_number_data['ruku'],
            "sura" => $division_number_data['sura']
        );
        return $division_information;
    }
    /**
     * Used to determine the division number, sura, ruku and ayat when the next or previous ruku is selected
     *
     * It fetches the division number, sura, ruku and ayat when the next or previous ruku is selected
     *
     * @param string $ruku the ruku id
     * @param string $division the division name
     * @param string $action the action. i.e next or previous
     *
     * @return array $division_data an array with 4 keys:
     * division_number => the next/previous division number
     * sura => the next/previous sura
     * ruku => the next/previous ruku
     * ayat => the next/previous ayat
     */
    public function NextPreviousSelection($ruku, $division, $action) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "meta",
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The total number of rukus in Holy Quran */
        $total_ruku_count = HolyQuran::GetMaxDivisionCount("ruku");
        /** If the next button was clicked then ruku count is increased by 1 */
        if ($action == "next") 
        {
            /** If the current ruku is equal to the last ruku then it is set to 1. Otherwise it is increased by 1 */
            if ($ruku == $total_ruku_count) $ruku = 1;
            /** Otherwise the ruku count is set to the last ruku */
            else $ruku = $ruku + 1;
        }
        /** If the previous button was clicked then ruku count is decreased by 1 */
        else if ($action == "previous") 
        {
            /** If the current ruku is greater than 1 then ruku count is decreased by 1 */
            if ($ruku > 1) $ruku = $ruku - 1;
            /** Otherwise the ruku count is set to the last ruku */
            else $ruku = $total_ruku_count;
        }
        /** The where clause used to fetch division data from database */
        $where_clause = array(
            array(
                'field' => "ruku",
                'value' => $ruku,
                'operation' => "=",
                'operator' => ""
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => $where_clause,
            "read_all" => false
        );
        /** The ayat information is read */
        $this->Read($parameters);
        /** The division number,sura,ruku,ayat data */
        $data = $this->GetData();
        /** The division data */
        $division_data = array(
            "division_number" => $data[$division],
            "sura" => $data['sura'],
            "ruku" => $data['ruku'],
            "ayat" => $data['sura_ayat_id']
        );
        return $division_data;
    }
}

