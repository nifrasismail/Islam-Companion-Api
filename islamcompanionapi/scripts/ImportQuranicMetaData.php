<?php
namespace IslamCompanionApi\Scripts;
use \Framework\Testing\Testing as Testing;
use \Framework\Object\MysqlDataObject as MysqlDataObject;
/**
 * This class implements the import quranic meta data script
 *
 * It contains functions used to import quranic meta data
 *
 * @category   IslamCompanionApi
 * @package    Scripts
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 * @link       N.A
 */
final class ImportQuranicMetaData extends Testing
{
    /**
     * Used to correct the sura names in database
     *
     * It reads Holy Quran sura names in Arabic from quran-data.xml file
     * It updates the name field in ic_quranic_suras_meta table
     *
     * @since 1.0.0
     */
    private function LoadXMLFile() 
    {
        /** The full path to the Quran meta data xml file */
        $quran_meta_data_file_name = $this->GetConfig("path", "application_folder") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "quran-data.xml";
        /** The Quran Data xml file is read */
        $xml_data = simplexml_load_file($quran_meta_data_file_name);
        return $xml_data;
    }
    /**
     * Used to import division data to database
     *
     * It saves the meta data for given division to database
     *
     * @since 1.0.0
     * @param string $division_name name of the division whoose meta data is to be imported
     * @param array $xml_data the quranic meta data
     */
    private function ImportDivisionData($division_name, $xml_data) 
    {
        for ($count = 0;$count < count($xml_data->juz);$count++) 
        {
            $juz = (array)$xml_data->juz[$count];
            $juz_attributes = $juz['@attributes'];
            $index = $juz_attributes['index'];
            $sura = $juz_attributes['sura'];
            $aya = $juz_attributes['aya'];
            $insert_str = "INSERT INTO ic_quranic_juzs_meta(jindex,sura,aya,created_on) VALUES('" . mysql_escape_string($index) . "','" . mysql_escape_string($sura) . "','" . mysql_escape_string($aya) . "','" . mysql_escape_string(time()) . "')";
            mysql_query($insert_str);
        }
    }
    /**
     * Used to import sura meta data
     *
     * It saves the sura data to database
     *
     * @since 1.0.0
     * @param array $xml_data the quranic meta data
     */
    private function ImportSuraData($xml_data) 
    {
        for ($count = 0;$count < count($xml_data->sura);$count++) 
        {
            $sura = (array)$xml_data->sura[$count];
            $sura_attributes = $sura['@attributes'];
            $index = $sura_attributes['index'];
            $ayas = $sura_attributes['ayas'];
            $start = $sura_attributes['start'];
            $name = $sura_attributes['name'];
            $tname = $sura_attributes['tname'];
            $ename = $sura_attributes['ename'];
            $type = $sura_attributes['type'];
            $order = $sura_attributes['order'];
            $rukus = $sura_attributes['rukus'];
            $insert_str = "INSERT INTO ic_quranic_suras_meta(sindex,ayas,start,name,tname,ename,type,sorder,rukus,created_on) VALUES('" . mysql_escape_string($index) . "','" . mysql_escape_string($ayas) . "','" . mysql_escape_string($start) . "','" . mysql_escape_string($name) . "','" . mysql_escape_string($tname) . "','" . mysql_escape_string($ename) . "','" . mysql_escape_string($type) . "','" . mysql_escape_string($order) . "','" . mysql_escape_string($rukus) . "','" . mysql_escape_string(time()) . "')";
            mysql_query($insert_str);
        }
    }
    /**
     * Used to correct the sura names in database
     *
     * It reads Holy Quran sura names in Arabic from quran-data.xml file
     * It updates the name field in ic_quranic_suras_meta table
     *
     * @since 1.0.0
     */
    public function UpdateSuraNamesInDatabase() 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The configuration object is fetched */
        $parameters['configuration'] = $configuration;
        /** The mysql data object is created */
        $mysql_data_object = new MysqlDataObject($parameters);
        /** The mysql table name */
        $table_name = $this->GetConfig("general", "mysql_table_names", "sura");
        /** The table name is set */
        $mysql_data_object->SetTableName($table_name);
        /** The key field is set */
        $mysql_data_object->SetKeyField("id");
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => false,
            "read_all" => true
        );
        /** The mysql data object is loaded with data from database */
        $mysql_data_object->Read($parameters);
        /** The mysql data is fetched */
        $data = $mysql_data_object->GetData();
        /** The mysql data object is set to read/write */
        $mysql_data_object->SetReadOnly(false);
        /** The Holy Quran meta data */
        $quran_meta_data = $this->LoadXMLFile();
        /** The sura meta data */
        $sura_meta_data = (array)$quran_meta_data->suras;
        /** For each sura meta data, the sura name in Arabic is saved to database */
        for ($count = 0;$count < count($sura_meta_data['sura']);$count++) 
        {
            /** The meta data for a single sura */
            $sura = (array)$sura_meta_data['sura'][$count];
            $sura_attributes = $sura['@attributes'];
            /** The sura index value */
            $index = $sura_attributes['index'];
            /** The sura name in Arabic */
            $name = $sura_attributes['name'];
            /** The sura name in English */
            $tname = $sura_attributes['tname'];
            /** The sura data in database */
            $sura_data = $data[$count];
            /** The sura data is set to the MysqlDataObject */
            $mysql_data_object->SetData($sura_data);
            /** The sura Arabic name is updated */
            $mysql_data_object->Edit("name", $name);
            /** The sura English name is updated */
            $mysql_data_object->Edit("tname", $tname);
            /** The mysql data is saved to database */
            $mysql_data_object->Save();
        }
    }
    /**
     * Used to correct the author names in database
     *
     * It reads the author data in database
     * It updates the name field in ic_quranic_author_meta table so it is utf8 encoded
     * It also sets the value of the checksum field
     *
     * @since 1.0.0
     */
    public function UpdateAuthorNamesInDatabase() 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The configuration object is fetched */
        $parameters['configuration'] = $configuration;
        /** The database object is fetched */
        $parameters['database_object'] = $this->GetComponent("database");
        /** Used to indicate if the checksum should be validated */
        $parameters['validate_checksum'] = false;
        /** The data type is set to authors */
        $parameters['data_type'] = "author";
        /** The key field is set to id */
        $parameters['key_field'] = "id";
        /** The mysql data object is created */
        $mysql_data_object = new MysqlDataObject($parameters);
        /** The mysql table name */
        $table_name = $this->GetConfig("general", "mysql_table_names", "author");
        /** The table name is set */
        $mysql_data_object->SetTableName($table_name);
        /** The key field is set */
        $mysql_data_object->SetKeyField("id");
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => false,
            "read_all" => true
        );
        /** The mysql data object is loaded with data from database */
        $mysql_data_object->Read($parameters);
        /** The mysql data is fetched */
        $data = $mysql_data_object->GetData();
        /** The mysql data object is set to read/write */
        $mysql_data_object->SetReadOnly(false);
        /** For each author meta data, the author name in Arabic is saved to database */
        for ($count = 0;$count < count($data);$count++) 
        {
            /** The author data in database */
            $author_data = $data[$count];
            /** The file name */
            $file_name = $author_data['file_name'];
            if ($file_name == "quran-simple.txt") continue;
            /** The text file containing author data is read */
            $line_arr = file($this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "original_files" . DIRECTORY_SEPARATOR . $file_name);
            /** The author section is extracted from the text file */
            list($temp_str, $name) = explode(":", $line_arr[count($line_arr) - 8]);
            /** The translator section is extracted from the text file */
            list($temp_str, $translator) = explode(":", $line_arr[count($line_arr) - 7]);
            /** The author data is sorted */
            ksort($author_data, SORT_STRING);
            /** The checksum field is removed from the author row */
            unset($author_data['checksum']);
            /** The base64 encoding of the data is combined */
            $combined_field_values = "";
            /** The author data values */
            $author_data_values = array_values($author_data);
            /** Each field value is encoded and combined */
            for ($count1 = 0;$count1 < count($author_data_values);$count1++) 
            {
                /** The field values in the author row are combined */
                $combined_field_values = $combined_field_values . base64_encode($author_data_values[$count1]);
            }
            /** The author data is set to the MysqlDataObject */
            $mysql_data_object->SetData($author_data);
            /** The data in the checksum field is set */
            $mysql_data_object->Edit("checksum", md5($combined_field_values));
            /** The author Arabic name is updated */
            $mysql_data_object->Edit("name", trim($name));
            /** The translator name is updated */
            $mysql_data_object->Edit("translator", trim($translator));
            /** The data is saved to database */
            $mysql_data_object->Save();
        }
    }
}
?>
