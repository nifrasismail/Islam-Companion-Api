<?php

namespace IslamCompanionApi\Test;
use \IslamCompanionApi\DataObjects\HolyQuran as HolyQuran;
use IslamCompanionApi\Scripts\Etl as Etl;

/**
 * This class implements the test class for the application
 * It contains functions that help in testing the application
 * It tests the application functions by making xmlrpc calls
 *
 * It contains unit tests for the class
 *
 * @category   IslamCompanionApi
 * @package    Testing
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class WordPressTesting extends \Framework\Testing\Testing
{
   /**     
     * This function provides test data for testing the given function
     * It may be overriden by child classes
     *
     * It reads the test data from database for the given url option
     * Each test data contains application parameters
     *
     * @param string $object_name the name of the object that contains the function to be tested
     * @param string $function_name the name of the function to be tested
     * @param string $option the name of the url option
     *
     * @return $test_data the test data contents
     */
    protected function LoadTestData($object_name="", $function_name="", $option="") 
    {
        /** The test data that will be used to test the given function */
        $test_data = array();
        /** If the function being tested is TestEtlScript */
        if ($function_name == "TestEtlScript") 
        {
            /** The test data for Etl Script is fetched */
            $test_data = $this->GetComponent("loadtestdata")->LoadTestData($function_name);
        }
        else
        {
            /** The function name is set to the API function name */
            $test_data_function_name = str_replace("Test", "Handle", $function_name);
            /** The test data for the XML-RPC function is fetched */
            $data = parent::LoadTestData("application", $test_data_function_name, "");
            /** The function name is set to the API function name */
            $test_function_name = "ic.Rpc" . str_replace("Test", "", $function_name);
            /** The admin user id of the WordPress site to be tested */
            $user_id = $this->GetConfig('wordpress', 'rpc_server_information', 'user_id');
            /** The blog id of the WordPress site to be tested */
            $blog_id = $this->GetConfig('wordpress', 'rpc_server_information', 'blog_id');
            /** The admin user name of the WordPress site to be tested */
            $user_name = $this->GetConfig('wordpress', 'rpc_server_information', 'user_name');
            /** The admin user password of the WordPress site to be tested */
            $user_password = $this->GetConfig('wordpress', 'rpc_server_information', 'password');
            /** The RPC server url */
            $rpc_server_url = $this->GetConfig('wordpress', 'rpc_server_information', 'server_url');
            /** If the test data is not an array, then it is initialized to a default value */
            if (!is_array($data)) {
                /** The test data item */
                $data = array(0 => array('function_parameters' => ''));
            }
            /** The test data is converted to parameters for the XML-RPC function */
            for ($count = 0;$count < count($data);$count++) 
            {
                /** The test data item */
                $data_item = $data[$count]['function_parameters'];
                /** The function name is set to the API function name */
                $test_function_name = "ic.Rpc" . str_replace("Test", "", $function_name);
                /** The admin user id of the WordPress site to be tested */
                $user_id = $this->GetConfig('wordpress', 'rpc_server_information', 'user_id');
                /** The blog id of the WordPress site to be tested */
                $blog_id = $this->GetConfig('wordpress', 'rpc_server_information', 'blog_id');
                /** The admin user name of the WordPress site to be tested */
                $user_name = $this->GetConfig('wordpress', 'rpc_server_information', 'user_name');
                /** The admin user password of the WordPress site to be tested */
                $user_password = $this->GetConfig('wordpress', 'rpc_server_information', 'password');
                /** The RPC server url */
                $rpc_server_url = $this->GetConfig('wordpress', 'rpc_server_information', 'server_url');
                /** The request type. it is either local or remote */
                $data_item['request_type'] = 'local';
		/** The module name is set to IslamCompanionApi */
		$data_item['module']       = 'IslamCompanionApi';
		/** The output_format is set to json */
		$data_item['output_format']= 'json';
		/** The database type is set to wordpress */
		$data_item['database_type']= 'wordpress';
		/** The test mode is set to false */
                $data_item['test_mode'] = false;
                /** The api key is base64 encoded */
                $data_item['api_key'] = $this->GetConfig("general", "api_key");
                /** The RPC login information */
                $rpc_login_information = array(
                    "blog_id" => $blog_id,
                    "user_id" => $user_id,
                    "user_name" => $user_name,
                    "user_password" => $user_password
                );
                /** The RPC login information is merged with the RPC function parameters */
                $data_item = array_merge($rpc_login_information, $data_item);
                /** The test data for the XML-RPC function */
                $test_data = array_merge($test_data, array(
                    array(
                        "rpc_function" => $test_function_name,
                        "rpc_function_parameters" => $data_item
                    )
                ));
            }
        }
        return $test_data;
    }
    /**
     * This function is used to check if the ayat division information is correct
     * It checks the ayat division data in the generated table
     *
     * It checks the given division table
     * If the given ayat is not in the correct division then the function throws an exception
     *
     * @throws object Exception an exception is thrown if the given ayat is not in the correct division
     *
     * @return boolean $is_valid used to indicate if the ayat data is valid or invalid
     */
    public function CheckAyatDivisionInformation($sura_ayat_id, $sura_id, $division_name, $division_number, $division_sura_id) 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The mysql data object is created */
        $holy_quran = new HolyQuran($configuration);
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $division_name,
            "field_name" => "id"
        );
        /** The table name and field name are set */
        $holy_quran->SetMetaInformation($meta_information);
        /** The where clause */
        $where_clause = array(
            array(
                "field" => "sura",
                "value" => $sura_id,
                "operation" => "<=",
                "operator" => ""
            )
        );
        /** The mysql data object is loaded with data in database */
        $holy_quran->Read("*", $where_clause, true);
        /** The mysql data is fetched */
        $table_rows = $holy_quran->GetData();
        /** The division data */
        $division_data = array(
            $division_name => $table_rows
        );
        /** The Etl class object is created */
        $etl_object = new Etl();
        /** The ayat data */
        $ayat = array(
            "surah" => $sura_id,
            "ayat" => $sura_ayat_id
        );
        /** The division data is fetched */
        $division_data = $etl_object->GetDivisionData($division_data, $ayat);
        /** The division number from the division table is checked against the given division number */
        $this->AssertEqual($division_data[$division_name], $division_number);
        /** The sura id in from the division table is checked against the given sura id */
        $this->AssertEqual($division_data["sura_division"], $division_sura_id);
    }
    /**
     * This function is used to test the etl script
     * It checks if the information for each ayat in the generated table is correct
     *
     * For each ayat in the generated table
     * It checks if the hizb, juz, page, manzil and ruku data is correct
     */
    public function dontTestEtlScript() 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigurationObject();
        /** The mysql data object is created */
        $holy_quran = new HolyQuran($configuration);
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "meta",
            "field_name" => "id"
        );
        /** The table name and field name are set */
        $holy_quran->SetMetaInformation($meta_information);
        /** The mysql data object is set to read/write */
        $holy_quran->SetReadonly(true);
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => false,
            "read_all" => true
        );
        /** The author information is read */
        $this->Read($parameters);
        /** The data is fetched from object */
        $table_data = $holy_quran->GetData();
        /** The division list */
        $division_list = array(
            "hizb",
            "juz",
            "manzil",
            "page",
            "ruku"
        );
        /** Each row is tested */
        for ($count1 = 0;$count1 < count($table_data);$count1++) 
        {
            $data = $table_data[$count1];
            /** Each division table is checked to see if it has the correct ayat information */
            for ($count2 = 0;$count2 < count($division_list);$count2++) 
            {
                /** The division name */
                $division_name = $division_list[$count2];
                /** The sura ayat id */
                $sura_ayat_id = $data['ayat_sura_id'];
                /** The division number in the table data row */
                $division_number = $data[$division_name];
                /** The sura id */
                $sura_id = $data['sura'];
                /** The division sura id */
                $division_sura_id = ($division_name == "ruku") ? $data['sura_ruku'] : '0';
                /** The ayat division information is tested */
                $this->CheckAyatDivisionInformation($sura_ayat_id, $sura_id, $division_name, $division_number, $division_sura_id);
            }
        }
    }
    /**
     * This function is used to test the GetMetaData function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleGetMetaData function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestGetMetaData($parameters) 
    {
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        $this->AssertTrue(strpos($response, "success") , "Function name: TestGetMetaData . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the DeleteHolyQuranData function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleDeleteHolyQuranData function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestDeleteHolyQuranData($parameters) 
    {
        /** The user_id is set to 1 */
        $parameters['rpc_function_parameters']['user_id']          = '1';     
        /** The request type is set to local */
        $parameters['rpc_function_parameters']['request_type']     = 'local';
        /** The language for the ayas */
        $parameters['rpc_function_parameters']['post_count']       = '100';
        /** The option for the data import */
        $parameters['rpc_function_parameters']['option']           = 'delete_holy_quran_data';
        /** The ayat import loop is started */
        for ($count = 0; $count <= 63; $count++) {     
            /** The RPC function response. The RPC function is called with the given parameters */
            $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
            $this->AssertTrue(strpos($response, "success") , "Function name: TestDeleteHolyQuranData . Response from RPC server: " . $response);
        }
    }
    /**
     * This function is used to test the DeleteHolyQuranMetaData function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleDeleteHolyQuranData function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestDeleteHolyQuranMetaData($parameters) 
    {
        /** The option is set to local */
        $parameters['rpc_function_parameters']['option']       = 'delete_holy_quran_meta_data';
        /** The post count is set to 100. It is the number of posts to delete */
        $parameters['rpc_function_parameters']['post_count']   = '100';
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
   
        $this->AssertTrue(strpos($response, "success") , "Function name: TestDeleteHolyQuranData . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the AddHolyQuranMetaData function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleAddHolyQuranMetaData function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestAddHolyQuranMetaData($parameters) 
    {       
        /** The option is set to local */
        $parameters['rpc_function_parameters']['option']       = 'add_holy_quran_meta_data';
        /** The data type is set to author */
        $parameters['rpc_function_parameters']['data_type']    = 'sura';
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);

        $this->AssertTrue(strpos($response, "success") , "Function name: TestAddHolyQuranMetaData . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the AddHolyQuranData function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleAddHolyQuranData function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestAddHolyQuranData($parameters) 
    {
        /** The user_id is set to 1 */
        $parameters['rpc_function_parameters']['user_id']          = '1';     
        /** The request type is set to local */
        $parameters['rpc_function_parameters']['request_type']     = 'local';
        /** The number of ayas to import */
        $parameters['rpc_function_parameters']['total_ayat_count'] = '100';
        /** The narrator for the ayas */
        $parameters['rpc_function_parameters']['narrator']         = "Mohammed Marmaduke William Pickthall";
        /** The language for the ayas */
        $parameters['rpc_function_parameters']['language']         = 'English';
        /** The option for the data import */
        $parameters['rpc_function_parameters']['option']           = 'add_holy_quran_data';
        /** The ayat import loop is started */
        for ($count = 0; $count <= 63; $count++) {
            /** The start_ayat is set to 1 */
            $parameters['rpc_function_parameters']['start_ayat']   = ($count * $parameters['rpc_function_parameters']['total_ayat_count']) + 1;        
            /** The RPC function response. The RPC function is called with the given parameters */
            $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
            $this->AssertTrue(strpos($response, "success") , "Function name: TestAddHolyQuranData . Response from RPC server: " . $response);
        }
    }
    /**
     * This function is used to test the AddHadithMetaData function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin

     * This function in turn calls the HandleAddHadithMetaData function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function TestAddHadithMetaData($parameters) 
    {
        /** The user_id is set to 1 */
        $parameters['rpc_function_parameters']['user_id']          = '1';
        /** The option for the data import */
        $parameters['rpc_function_parameters']['option']           = 'add_hadith_meta_data';       
        /** The request type is set to local */
        $parameters['rpc_function_parameters']['request_type']     = 'local';
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        echo $response;exit;
        $this->AssertTrue(strpos($response, "success") , "Function name: TestAddHadithMetaData . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the AddHadithData function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleAddHadithData function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestAddHadithData($parameters) 
    {
        /** The user_id is set to 1 */
        $parameters['rpc_function_parameters']['user_id']          = '1';
        /** The option for the data import */
        $parameters['rpc_function_parameters']['option']           = 'add_hadith_data';
        /** The start hadith number is set */
        $parameters['rpc_function_parameters']['start_hadith']     = '1';
        /** The hadith count is set */
        $parameters['rpc_function_parameters']['hadith_count']     = '100';        
        /** The request type is set to local */
        $parameters['rpc_function_parameters']['request_type']     = 'local';
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        $this->AssertTrue(strpos($response, "success") , "Function name: TestAddHadithData . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the GetHolyQuranNavigator function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleGetHolyQuranNavigator function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestGetHolyQuranNavigator($parameters) 
    {
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        $this->AssertTrue(strpos($response, "success") , "Function name: TestGetHolyQuranNavigator . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the GetVerseText function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleGetVerseText function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestGetVerseText($parameters) 
    {
        /** The user_id is set to 1 */
        $parameters['rpc_function_parameters']['user_id']          = '1';     
        /** The request type is set to local */
        $parameters['rpc_function_parameters']['request_type']     = 'local';
        /** The required ayas */
        $parameters['rpc_function_parameters']['ayas']             = '6:96';
        /** The narrator for the ayas */
        $parameters['rpc_function_parameters']['narrator']         = "Abul A'ala Maududi";
        /** The language for the ayas */
        $parameters['rpc_function_parameters']['language']         = 'Urdu';
        /** The option for the data import */
        $parameters['rpc_function_parameters']['option']           = 'get_verse_text';
        /** The layout for the verse text */
        $parameters['rpc_function_parameters']['container']        = 'plain text';
        /** The css classes for the verse text */
        $parameters['rpc_function_parameters']['css_classes']      = ''; 
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);

        $this->AssertTrue(strpos($response, "success") , "Function name: TestGetVerseText . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the GetHadithText function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleGetHadithText function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestGetHadithText($parameters) 
    {
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        $this->AssertTrue(strpos($response, "success") , "Function name: TestGetHadithText. Response from RPC server: " . $response);
    }
   /**
     * This function is used to test the GetVisitorStatistics function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleGetVisitorStatistics function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestGetVisitorStatistics($parameters) 
    {
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        $this->AssertTrue(strpos($response, "success") , "Function name: TestGetVisitorStatistics. Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the GetHadithNavigator function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleGetHadithNavigator function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestGetHadithNavigator($parameters) 
    {
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        $this->AssertTrue(strpos($response, "success") , "Function name: TestGetHadithNavigator . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the GetHadithSearchResults function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleGetHadithSearchResults function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestGetHadithSearchResults($parameters) 
    {
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        $this->AssertTrue(strpos($response, "success") , "Function name: TestGetHadithSearchResults . Response from RPC server: " . $response);
    }
    /**
     * This function is used to test the GetHolyQuranSearchResults function of the Islam Companion API
     *
     * It calls the relavant XML-RPC function of the Islam Companion plugin
     * This function in turn calls the HandleGetHolyQuranSearchResults function of the Islam Companion API
     *
     * @param array $parameters the parameters for the test function
     *    rpc_function => string the name of the RPC function
     *    rpc_function_parameters => array the parameters used by rpc function
     */
    final public function dontTestGetHolyQuranSearchResults($parameters) 
    {
        /** The RPC function response. The RPC function is called with the given parameters */
        $response = $this->GetComponent("application")->MakeXmlRpcCall($parameters);
        $this->AssertTrue(strpos($response, "success") , "Function name: TestGetHolyQuranSearchResults . Response from RPC server: " . $response);
    }
}

