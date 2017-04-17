<?php

namespace IslamCompanionApi;

use IslamCompanionApi\DataObjects\HolyQuran as HolyQuran;

/**
 * This is the main application class
 * It implements all controller actions of the application
 *
 * It is used to implement all the controller actions of the application
 *
 * @category   IslamCompanionApi
 * @package    IslamCompanionApi
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class IslamCompanionApi extends \Framework\Application\Api
{
    /** The class uses functions from HolyQuranApi trait */
    use \IslamCompanionApi\HolyQuranApi;
    /** The class uses functions from HadithApi trait */
    use \IslamCompanionApi\HadithApi;
    /** The class uses functions from WordPressAdminApi trait */
    use \IslamCompanionApi\WordPressAdminApi;
    /**
     * Used to validate certain api function parameters
     *
     * It checks if the given function parameter is valid
     * {@internal context any}
     *
     * @param string $parameter_name the name of the parameter
     * @param string $parameter_value the value of the parameter
     * @param array $all_parameter_values the value of all the method parameters
     * @param array $all_parsed_parameters details of all the method parameters
     * @param array $all_return_values the return value of the function
     * @param array $all_return_parameters details of all the return value parameters
     * @param boolean $is_return if set to true then the return value needs to be validated
     *
     * @return array $validation_result the result of validating the method parameters
     *    is_valid => boolean indicates if the parameters are valid
     *    validation_message => string the validation message if the parameters could not be validated
     */
    public function ValidateFunctionParameter($parameter_name, $parameter_value, $all_parameter_values, $all_parsed_parameters, $all_return_values, $all_return_parameters, $is_return) 
    {
        /** The result of validating the parameter */
        $validation_result = array(
            "is_valid" => true,
            "validation_message" => ""
        );
        /** The authors object is fetched */
        $authors_obj = $this->GetComponent("authors");
        /** The ayat object is fetched */
        $ayat_obj = $this->GetComponent("ayat");
        /** The rukus object is fetched */
        $rukus_obj = $this->GetComponent("rukus");
        /** The division value */
        $division = (isset($all_parameter_values['parameters']['division'])) ? $all_parameter_values['parameters']['division'] : '-1';
        /** The action value */
        $action = (isset($all_parameter_values['parameters']['action'])) ? $all_parameter_values['parameters']['action'] : 'next';
        /** If the return value needs to be validated */
        if ($is_return) 
        {
            /** The sura value */
            $sura = (isset($all_return_values['state']['sura'])) ? $all_return_values['state']['sura'] : '-1';
            /** The ruku value */
            $ruku = (isset($all_return_values['state']['ruku'])) ? $all_return_values['state']['ruku'] : '-1';
            /** The division number value */
            $division_number = (isset($all_return_values['state']['division_number'])) ? $all_return_values['state']['division_number'] : '-1';
            /** The ayat value */
            $ayat = (isset($all_return_values['state']['ayat'])) ? $all_return_values['state']['ayat'] : '-1';
        }
        /** If the method parameters need to be validated */
        else 
        {
            /** The sura value */
            $sura = (isset($all_parameter_values['parameters']['sura'])) ? $all_parameter_values['parameters']['sura'] : '-1';
            /** The division number value */
            $division_number = (isset($all_parameter_values['parameters']['division_number'])) ? $all_parameter_values['parameters']['division_number'] : '-1';
            /** The ayat value */
            $ayat = (isset($all_parameter_values['parameters']['ayat'])) ? $all_parameter_values['parameters']['ayat'] : '-1';
        }
        /** If the translator value needs to be validated */
        if ($parameter_name == "narrator" && !$authors_obj->IsTranslatorValid($parameter_value)) 
        {
            $validation_result["validation_message"] = "Invalid narrator value";
        }
        /** If the language value needs to be validated */
        else if ($parameter_name == "language" && !$authors_obj->IsLanguageValid($parameter_value)) 
        {
            $validation_result["validation_message"] = "Invalid language value";
        }
        /** If the sura value needs to be validated */
        else if ($parameter_name == "sura" && !HolyQuran::IsValidDivision($parameter_value, "sura")) 
        {
            $validation_result["validation_message"] = "Invalid sura value";
        }
        /** If the ruku value needs to be validated */
        else if ($parameter_name == "ruku" && !HolyQuran::IsValidDivision($parameter_value, "ruku")) 
        {
            $validation_result["validation_message"] = "Invalid ruku value";
        }
        /** If the division number value needs to be validated */
        else if ($parameter_name == "division_number" && !HolyQuran::IsValidDivision($parameter_value, $division)) 
        {
            $validation_result["validation_message"] = "Invalid division number value";
        }
        /** If the ayat value needs to be validated */
        else if ($parameter_name == "ayat" && !HolyQuran::IsValidDivision($parameter_value, "ayas")) 
        {
            $validation_result["validation_message"] = "Invalid ayat value";
        }
        /** If a validation error was set then the result of validation is set to true */
        if ($validation_result["validation_message"] != "") $validation_result["is_valid"] = false;
        
        return $validation_result;
    }
    /**
     * Used to pre process the application request
     *
     * This function is called before an application request is processed
     * It is used to validate the request parameters
     * It also sets the application database type to wordpress
     * {@internal context any}
     *
     * @param string $option the application option
     * @param object $controller_object the object that contains the api function
     * @param string $function_name the api function name
     * @param array $parameters the parameters for the callback function
     * @param string $function_type the type of function
     *
     * @throws object Exception an exception is thrown if one of the parameters could not be validated
     */
    protected function PreProcessRequest($option, $controller_object, $function_name, $parameters, $function_type) 
    {
        /** The response format is set in the application configuration */
        $this->SetConfig("general", "output_format", $parameters['output_format']);
        /** The database type is set */
        $this->SetConfig("general", "database_type", $parameters['parameters']['database_type']);
        /** If the language and translator are set then the aya table name is updated in the application configuration */
        if (isset($parameters['parameters']['language']) && isset($parameters['parameters']['narrator'])) 
        {
            /** The narrator data is decoded */
            $parameters['parameters']['narrator'] = urldecode(urldecode($parameters['parameters']['narrator']));
            /** The parameter values are set in the application configuration */
            $this->SetConfig("general", "parameters", $parameters);
            /** The translation information is fetched */
            $translation_information = $this->GetComponent("authors")->GetTranslationInformation($parameters['parameters']['language']);
            /** The translation file name */
            $translation_file_name = $translation_information['file_name'];
            /** The ayat table name */
            $ayat_table_name = "ic_quranic_text-" . str_replace(".txt", "", $translation_file_name);
            /** The mysql table names */
            $mysql_table_names = $this->GetConfig("general", "mysql_table_names");
            /** The ayat table name is updated */
            $mysql_table_names['aya'] = $ayat_table_name;
            /** The ayat table name is set in the application configuration */
            $this->SetConfig("general", "mysql_table_names", $mysql_table_names);
        }
        /** The parent function is called */
        parent::PreProcessRequest($option, $controller_object, $function_name, $parameters, $function_type);
    }
    /**
     * Used to post process the application request
     *
     * This function is called after an application request is processed
     * It is used to validate the application response
     * {@internal context any}
     *
     * @param string $option the application option
     * @param object $controller_object the object that contains the api function
     * @param string $function_name the api function name
     * @param mixed $response the api function response
     * @param string $output_format the function output format
     * @param array $parameters the parameters for the callback function
     *
     * @throws object Exception an exception is thrown if the result of validation is not valid
     *
     * @return array $processed_response an array containing the formatted output and validation results
     *    formatted_output => string formatted output
     *    validation_result => array the result of validating the function
     *        is_valid => boolean indicates if the parameters are valid
     *        validation_message => string the validation message if the parameters could not be validated
     */
    protected function PostProcessRequest($option, $controller_object, $function_name, $response, $output_format, $parameters) 
    {
        /** The parent function is called */
        $processed_response = parent::PostProcessRequest($option, $controller_object, $function_name, $response, $output_format, $parameters);
        /** If the parameter validation gave an error then an exception is thrown */
        if (!$processed_response['validation_result']['is_valid']) throw new \Exception("Return value for the option: " . $option . " could not be validated. Details: " . $processed_response['validation_result']['validation_message']);
        /** The type of request. i.e local or remote api call */
        $request_type = $parameters['parameters']['request_type'];
        /** The api response */
        $response = $processed_response['formatted_output'];

        return $processed_response;
    }
    /**
     * Custom error handling function
     *
     * Used to handle an error
     * {@internal context any}
     *
     * @param string $log_message the error log message
     * @param array $error_parameters the error parameters. it contains following keys:
     *    error_level => int the error level
     *    error_type => int [Error~Exception] the error type. it is either Error or Exception
     *    error_message => string the error message
     *    error_file => string the error file name
     *    error_line => int the error line number
     *    error_context => array the error context
     */
    public function CustomErrorHandler($log_message, $error_parameters) 
    {
        /** The server information is fetched */
        $server_database_information = $this->GetServerAndDatabaseInformation();
        /** The database information is added to the error parameters */
        $error_parameters['mysql_query_log'] = $server_database_information['mysql_query_log'];
        /** The server data is added to the error parameters */
        $error_parameters['server_data'] = $server_database_information['server_data'];
        /** The host name of the api server */
        $api_server_host_name = $this->GetConfig("general", "api_server");
        /** If the error message was generated on the api server, then it is logged to database */
        if ($this->GetConfig("custom", "is_api_server")) 
        {
            /** The error message is logged to database */
            $this->LogErrorToDatabase($error_parameters, $error_parameters["server_data"], $error_parameters["mysql_query_log"]);
        }
        /** If the error message was not generated on the api server, then the error message is logged using a web hook */
        else 
        {
            $this->LogErrorToWebHook("IslamCompanionApi", $error_parameters, false);
        }       
        /** The custom error handler function of the parent api class is called */
        parent::CustomErrorHandler($log_message, $error_parameters);
    }
    /** 
     * Used to get the number of downloads for Islam Companion wordpress plugin
     *
     * @return array $download_count_arr the download count array
     */
    private function GetPluginDownloadCount()
    {
        /** The cached data is fetched */
        $download_count_arr = $this->GetCachedData("GetPluginDownloadCount", array());
        /** If the cached data was found then it is returned */
        if ($download_count_arr !== false) return $download_count_arr;
        /** The plugin download stats url */
        $plugin_download_stats_url = $this->GetConfig("custom", "plugin_download_stats_url");
        
        /** The stats page content is fetched */
        $response = $this->GetComponent("filesystem")->GetFileContent($plugin_download_stats_url);
        /** The newlines are removed */
        $response = str_replace("\r", "", $response);
        $response = str_replace("\n", "", $response);
        /** The page contents are parsed */
        preg_match_all('/<div id="history">(.*)<\/div>/iU', $response, $matches);
        /** The html containing the download data */
        $download_count_arr = $matches[1][0];
        /** The download count html is parsed */
        preg_match_all('/<td>(.*)<\/td>/iU', $download_count_arr, $matches);        
        /** The download count array is initialized */
        $download_count_arr = array();
        $download_count_arr["statistics_for_today"]  = $matches[1][0];
        $download_count_arr["statistics_for_yesterday"] = $matches[1][1];
        $download_count_arr["statistics_for_last_seven_days"] = $matches[1][2];
        $download_count_arr["statistics_for_all_time"] = $matches[1][3];
        
        /** The cached data is saved to database */
        $this->SaveDataToCache("GetPluginDownloadCount", array(), $download_count_arr);
        
        return $download_count_arr;
    }
    /** 
     * Handler function for get_visitor_statistics option
     * {@internal context local api,command line}
     *
     * @param array $parameters the visitor statistics
     *
     * @return string $visitor_statistics_html the visitors statistics html
     */
    public function HandleGetVisitorStatistics($parameters)
    {
        /** The api function details */
        $api_function_details = array();
        /** Each visitor url is checked */
        for ($count = 0; $count < count($parameters['visitor_statistics']['all_urls']); $count++) {
            /** The visitor url */
            $visitor_url = parse_url($parameters['visitor_statistics']['all_urls'][$count]);
            /** If query url parameter is not set, then the function continues */
            if (!isset($visitor_url['query'])) continue;
            /** The visitor url query string is parsed */
            parse_str($visitor_url['query'], $query_data);
            /** The language is converted to upper case */
            if (isset($query_data['parameters']['language']))$query_data['parameters']['language'] = ucwords($query_data['parameters']['language']);
            /** The option count is updated */
            $api_function_details['api_function_count'][$query_data['option']] = (isset($api_function_details['api_function_count'][$query_data['option']])) ? $api_function_details['api_function_count'][$query_data['option']]+1 : "1";
            /** The holy quran language count is updated */
            if (isset($query_data['parameters']['language']))
                $api_function_details['language_count'][$query_data['parameters']['language']] = (isset($api_function_details['language_count'][$query_data['parameters']['language']])) ? $api_function_details['language_count'][$query_data['parameters']['language']]+1 : "1";
        }
        /** The api function count details */
        $parameters['visitor_statistics']['api_function_count'] = array();
        /** If the api function count is set */
        if (isset($api_function_details['api_function_count'])) {
            /** The api function details are formatted */
            foreach ($api_function_details['api_function_count'] as $field_name => $field_value) {
                $parameters['visitor_statistics']['api_function_count'][] = $field_name . " (" . $field_value . ")";
            }
        }        
        /** The language count details */
        $parameters['visitor_statistics']['language_count'] = array();
        /** If the language count is set */
        if (isset($api_function_details['language_count'])) {
            /** The api function details are formatted */
            foreach ($api_function_details['language_count'] as $field_name => $field_value) {
                $parameters['visitor_statistics']['language_count'][] = $field_name . " (" . $field_value . ")";
            }
        }
        /** The download count is fetched */
        $parameters['visitor_statistics']['download_count'] = $this->GetPluginDownloadCount();
        $visitor_statistics_html = "<b>Total Visitors:</b> " . $parameters['visitor_statistics']['total_visitors'];
        $visitor_statistics_html .= "<br/><b>Unique Visits:</b> " . $parameters['visitor_statistics']['unique_visitors'];
        $visitor_statistics_html .= "<br/><b>Api Function Count:</b> " . implode(" , ", $parameters['visitor_statistics']['api_function_count']);
        $visitor_statistics_html .= "<br/><b>Language Count:</b> " . implode(" , ", $parameters['visitor_statistics']['language_count']);
        if (isset($parameters['visitor_statistics']['download_count'][$parameters['time_range']]))$visitor_statistics_html .= "<br/><b>Download Count:</b> " . $parameters['visitor_statistics']['download_count'][$parameters['time_range']];
        $visitor_statistics_html .= "<br/><b>Average Time:</b> " . number_format($parameters['visitor_statistics']['average_time_taken'], 2) . " sec";
        $visitor_statistics_html .= "<br/><b>Max Time:</b> " . number_format($parameters['visitor_statistics']['max_time_taken'], 2) . " sec";
        $visitor_statistics_html .= "<br/><b>Min Time:</b> " . number_format($parameters['visitor_statistics']['min_time_taken'], 2) . " sec";
        
        return $visitor_statistics_html;
    }
    /** 
     * Used to save the cached data
     * It checks if the data needs to be cached
     * If the data does not need to be cached, then the function returns
     * If the data needs to be cached, then it is saved to database
     *
     * @param string $function_name the name of the function to cache
     * @param mixed $function_parameters the function parameters
     * @param mixed $function_data the function data to be cached
     */
    private function SaveDataToCache($function_name, $function_parameters, $function_data)
    {
        /** Indicates if function output should be cached */
        $enable_function_caching = $this->GetConfig("general", "enable_function_caching");
        /** If the function output should not be cached */
        if (!$enable_function_caching) return false;
        /** The cached data is saved to database */
        $this->SaveDataToCache($function_name, $function_parameters, $function_data);
    }
    /** 
     * Used to return the cached data
     * It returns the cached data for the given function name and function parameters
     *
     * @param string $function_name the name of the cached function
     * @param array $parameters the function parameters
     *
     * @return mixed $cached_data the cached data is returned. false is returned if the cached data does not exist
     */
    private function GetCachedData($function_name, $parameters)
    {
        /** Indicates if function output should be cached */
        $enable_function_caching = $this->GetConfig("general", "enable_function_caching");
        /** If the function output should not be cached */
        if (!$enable_function_caching) return false;
        /** The current site url */
        $site_url = $this->GetConfig("general", "site_url");
        /** The db link is fetched */
        $db_link  = $this->GetComponent("database")->df_get_id();
        /** The db link is set in the caching object */
        $this->GetComponent("caching")->SetDbLink($db_link);
        /** The site url is set in the caching object */
        $this->GetComponent("caching")->SetSiteUrl($site_url);
        /** The cache duration is set to the value given in configuration file */
        \Framework\Utilities\Caching::$function_cache_duration[$function_name] = $this->GetConfig("custom", "function_cache_duration");
        /** The data is fetched from database cache if it exists */
        $cached_data = $this->GetComponent("caching")->GetCachedData($function_name, $parameters, true);
        
        /** The cached cached is returned */
        return $cached_data;
    }
}
