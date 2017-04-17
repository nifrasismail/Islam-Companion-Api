<?php
namespace IslamCompanionApi\DataObjects;
/**
 * This class implements the Suras class
 *
 * An object of this class allows access to Holy Quran suras
 * The suras can be fetched using different criteria
 * For example set of suras for given division and division number
 *
 * @category   IslamCompanionApi
 * @package    DataObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class Suras extends \Framework\Object\DataObjectAbstraction
{
    /**
     * Used to get the suras in the given division number
     *
     * It fetches list of all the suras in given division and division number
     * If the all parameter is set to true then all suras are fetched
     *
     * @param string $division the division
     * @param string $division_number the division number
     * @param boolean $all it is equal to true if all suras need to be fetched
     *
     * @param int $sura_list the list of all the suras in the given division
     */
    public function GetSurasInDivision($division, $division_number, $all) 
    {
        /** The total number of rukus */
        $total_rukus = array();
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "meta",
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The start and end sura index numbers */
        $start_sura_index = 1;
        $end_sura_index = 1;
        /** If suras in given division need to be fetched */
        if (!$all) 
        {
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
                "fields" => "DISTINCT(sura)",
                "condition" => $where_clause,
                "read_all" => true,
                "order" => array(
                    "field" => "sura",
                    "type" => "numeric",
                    "direction" => "ASC"
                )
            );
            /** The meta data is read */
            $this->Read($parameters);
            /** The meta data */
            $meta_data = $this->GetData();
            /** The start sura number is set */
            $start_sura_index = $meta_data[0]['sura'];
            /** The end sura number is set */
            $end_sura_index = $meta_data[count($meta_data) - 1]['sura'];
        }
        /** If all suras need to be fetched */
        else 
        {
            /** The start sura number is set */
            $start_sura_index = '1';
            /** The end sura number is set */
            $end_sura_index = HolyQuran::GetMaxDivisionCount("sura");
        }
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "sura",
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The where clause used to fetch division data from database */
        $where_clause = array(
            array(
                'field' => "sindex",
                'value' => $start_sura_index,
                'operation' => ">=",
                'operator' => "AND"
            ) ,
            array(
                'field' => "sindex",
                'value' => $end_sura_index,
                'operation' => "<=",
                'operator' => ""
            ) ,
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "sindex, tname, ename",
            "condition" => $where_clause,
            "read_all" => true,
            "order" => array(
                "field" => "sindex",
                "type" => "numeric",
                "direction" => "ASC"
            )
        );
        /** The meta data is read */
        $this->Read($parameters);
        /** The sura data */
        $sura_data = $this->GetData();
        /** The list of suras */
        $sura_list = array();
        /** For each sura id, the sura name is fetched */
        for ($count = 0;$count < count($sura_data);$count++) 
        {
            /** The sura list is updated */
            $sura_list[] = array(
                "sindex" => $sura_data[$count]['sindex'],
                "tname" => $sura_data[$count]['tname'],
                "ename" => $sura_data[$count]['ename']
            );
        }
        return $sura_list;
    }
    /**
     * Used to get the sura ruku number
     *
     * It returns the sura ruku number from the given ruku id
     *
     * @param int $ruku the ruku id
     * @throws object Exception an exception is thrown if the sura ruku number could not be determined
     *
     * @return int $sura_ruku_number the sura ruku number
     */
    public function GetSuraRukuNumber($ruku) 
    {
        /** The sura ruku number */
        $sura_ruku_number = - 1;
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
                'field' => "ruku",
                'value' => $ruku,
                'operation' => "=",
                'operator' => ""
            )
        );
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "sura_ruku",
            "condition" => $where_clause,
            "read_all" => false
        );
        /** The meta data is read */
        $this->Read($parameters);
        /** The meta data */
        $meta_data = $this->GetData();
        /** The sura ruku number */
        $sura_ruku_number = $meta_data['sura_ruku'];
        return $sura_ruku_number;
    }
    /**
     * Used to get the data for the given sura
     *
     * It returns the sura data
     *
     * @param int sura the sura id
     *
     * @return array $sura_data the sura data
     *
     */
    public function GetSuraData($sura) 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "sura",
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The where clause used to fetch division data from database */
        $where_clause = array(
            array(
                'field' => 'sindex',
                'value' => $sura,
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
        /** The meta data is read */
        $this->Read($parameters);
        /** The sura data */
        $sura_data = $this->GetData();
        return $sura_data;
    }
    /**
     * Used to get the list of all the suras
     *
     * It returns the sura list
     *
     * @return int $sura_list the list of all suras names
     */
    public function GetSuraList() 
    {
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "sura",
            "key_field" => ""
        );
        /** The table name and field name are set */
        $this->SetMetaInformation($meta_information);
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => false,
            "read_all" => true,
            "order" => array(
                "field" => "sindex",
                "type" => "numeric",
                "direction" => "ASC"
            )
        );
        /** The meta data is read */
        $this->Read($parameters);
        /** The sura data */
        $sura_list = $this->GetData();
        return $sura_list;
    }
}

