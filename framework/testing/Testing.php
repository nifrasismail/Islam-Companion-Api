<?php
namespace Framework\Testing;
use \Framework\Configuration\Base as Base;
/**
 * This class implements the base ApplicationTest class
 *
 * It contains functions that help in testing applications
 * The class is abstract and must be inherited by the application test class
 *
 * @category   Framework
 * @package    Testing
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class Testing extends Base
{
    /**
     * The number of valid assert statements in the test class
     */
    private $valid_assert_count = 0;
    /**
     * Used to validate the given url output
     *
     * It validates the given output produced by the application for a given url
     * The output is e.g html,array or json encoded string
     * It checks the type of the output and then call the relavant validation function
     *
     * @param string $output_type the type of output. e.g json or html
     * @param string $output the output of the application
     *
     * @return $array $validation_results the results of validation. the array contains 2 keys. result=> success or error
     * and message=> response returned by validation function
     */
    final public function ValidateOutput($output_type, $output) 
    {
        if ($output_type == "html") $validation_results = $this->ValidateMarkup($output, 'html5');
        //else if ($output_type == "xml") $validation_results = $this->ValidateMarkup($output, 'xml');
        else if ($output_type == "json") $validation_results = $this->ValidateJson($output);
        else if ($output_type == "array") $validation_results = $this->ValidateArray($output);
        else if ($output_type == "string") $validation_results = $this->ValidateString($output);
        return $validation_results;
    }
    /**
     * Used to validate the given string
     *
     * It checks if the given variable is a string
     *
     * @param string $output the string to validate
     *
     * @return $array $validation_results the results of validation
     *    error => success or error
     *    message => error message contained in json string message property
     */
    final function ValidateString($output) 
    {
        if (isset($output['result']) && $output['result'] == 'success' && is_string($output['data'])) $validation_results = array(
            "result" => "success",
            "message" => ""
        );
        else $validation_results = array(
            "result" => "error",
            "message" => $output['message']
        );
        return $validation_results;
    }
    /**
     * Used to validate the given array
     *
     * It checks if given array has valid data
     * The array has valid data if it has a key called result which is equal to success
     *
     * @param array $output the array to validate
     *
     * @return $array $validation_results the results of validation. the array contains 2 keys. error=> success or error
     * and message=> error message contained in json string message property
     */
    final function ValidateArray($output) 
    {
        if (isset($output['result']) && $output['result'] == 'success' && is_array($output['data'])) $validation_results = array(
            "result" => "success",
            "message" => ""
        );
        else $validation_results = array(
            "result" => "error",
            "message" => $output['message']
        );
        return $validation_results;
    }
    /**
     * Used to validate the given json string
     *
     * It checks if given string is valid json
     * It also validates json object
     * Json object is valid if it has a key called result which is equal to success
     *
     * @param string $output_string the json string to validate
     *
     * @return $array $validation_results the results of validation. the array contains 2 keys. result=> success or error
     * and message=> error message contained in json string message property
     */
    final function ValidateJson($output_string) 
    {
        $output_arr = json_decode($output_string, true);
        if (isset($output_arr['result']) && $output_arr['result'] == 'success') $validation_results = array(
            "result" => "success",
            "message" => ""
        );
        else $validation_results = array(
            "result" => "error",
            "message" => $output_arr['message']
        );
        return $validation_results;
    }
    /**
     * Used to validate the html of a component using the w3c validation service
     *
     * It validates the given html string and returns the response from the validation service
     * It uses the validation service given in application configuration file
     *
     * @param $string $html_content the html string to be validated
     * @param $string $type [xml~html5] the type of markup
     *
     * @return $array $validation_results the results of validation. the array contains 2 keys. result=> success or error
     * and message=> response returned by the validation service
     */
    final private function ValidateMarkup($html_content, $type) 
    {
        /** If the html does not have the html5 <!DOCTYPE html> text and the type of markup is html5 then the html is inserted into a base page template */
        if (strpos($html_content, "<!DOCTYPE html>") === false && $type == "html5") 
        {
            $html_content = $this->InsertHtmlToTemplate($html_content, "template");
        }
        /** The filesystem object is fetched */
        $filesystem_obj = $this->GetComponent("filesystem");
        /** The application url context */
        $context = $this->GetConfig("general", "parameters", "context");
        /** The application test parameters */
        $test_parameters = $this->GetConfig("testing");
        $html_content = str_replace("\r", "", $html_content);
        $html_content = str_replace("\n", "", $html_content);
        $validator_url = $test_parameters['validator_url'];
        $output_format = ($context == "browser") ? "html" : "text";
        $show_source = ($context == "browser") ? "yes" : "no";
        $content = array(
            "parser" => $type,
            "out" => $output_format,
            "showsource" => $show_source,
            "asciiquotes" => "yes",
            "content" => $html_content
        );
        $headers = array(
            "Content-type: multipart/form-data; boundary=---------------------------" . strlen($html_content)
        );
        $validation_results = $filesystem_obj->GetFileContent($validator_url, "POST", $content, $headers);
        if ($context == "browser") 
        {
            $validation_results = str_replace("style.css", $validator_url . "/style.css", $validation_results);
            $validation_results = str_replace("script.js", $validator_url . "/script.js", $validation_results);
        }
        if ($type == "html5" && strpos($validation_results, "The document validates according to the specified schema(s).") !== false) $result = 'success';
        else {
            $result = 'error';                    
            $validation_results .= ". Original HTML text: " . $html_content;
        }
            
        $validation_results = array(
            "result" => $result,
            "message" => $validation_results
        );
        return $validation_results;
    }
    /**
     * Used to save the test results to test folder
     *
     * It saves the results of testing to file given in application configuration
     * If the test results file does not exist
     * Then the function returns without saving the test results
     *
     * @param string $test_results results of testing
     */
    final private function SaveTestResults($test_results) 
    {
        /** The absolute path of the test results file */
        $test_configuration = $this->GetConfig("testing");
        $test_file_name = $test_configuration['test_results_file'];
        /** If the test results file does not exist */
        if (!is_file($test_file_name)) return;
        /** The html is removed from the test results. The <br/> is replaced with new line */
        $test_results = str_replace("<br/>", "\n", $test_results);
        $test_results = strip_tags($test_results);
        /** The application test results are written to test file */
        $this->GetComponent("filesystem")->WriteLocalFile($test_results, $test_file_name);
    }
    /**
     * Used to save the test data to database
     *
     * It fetches the application parameters from application configuration
     * It saves the application parameters to the framework database
     *
     * @param string $object_name The name of the object that has the function
     * @param string $function_name The name of the function used to handle the url request
     * @param string $function_type [template, controller, not defined] the type of function
     * @param string $output_format [json, html, array] the format of the function output
     */
    final public function SaveTestData($object_name, $function_name, $function_type, $output_format) 
    {
        /** The application parameters are fetched */
        $test_data = $this->GetConfig('general', 'parameters');
        /** The current option */
        $option = $this->GetConfig('general', 'option');
        /** The test data is encoded */
        $test_data = $this->GetComponent("encryption")->EncodeData($test_data);
        /** The current module name */
        $module_name = $this->GetConfig("general", "module");
        /** The site url */
        $site_url = $this->GetConfig("general", "site_url");
        /** The data that needs to be saved to database */
        $test_data = array(
            "option_name" => $option,
            "module_name" => $module_name,
            "function_type" => $function_type,
            "object_name" => $object_name,
            "function_name" => $function_name,
            "function_parameters" => $test_data,
            "site_url" => $site_url,
            "created_on" => time()
        );
        /** The mysql table name where the data will be logged */
        $test_table_name = $this->GetConfig("general", "mysql_table_names", "test_data");
        /** The logging information */
        $logging_information = array(
            "database_object" => $this->GetComponent("frameworkdatabase") ,
            "table_name" => $test_table_name
        );
        /** This configuration determines if the test data should be appended or not */
        $append_test_data = $this->GetConfig('testing', 'append_test_data');
        /** If the data should not be appended then the current data is cleared */
        if (!$append_test_data) 
        {
            /** The where clause condition used to fetch the data that is to be deleted */
            $condition = array(
                array(
                    'field_name' => "object_name",
                    'field_value' => $object_name,
                    'operation' => "=",
                    'operator' => "AND"
                ) ,
                array(
                    'field_name' => 'function_name',
                    'field_value' => $function_name,
                    'operation' => "=",
                    'operator' => ""
                )
            );
            /** The log data is cleared from database */
            $this->GetComponent("logging")->ClearLogDataFromDatabase($logging_information, $condition);
        }
        /** The parameters for saving log data */
        $parameters = array(
            "logging_information" => $logging_information,
            "logging_data" => $test_data,
            "logging_destination" => "database",
        );
        /** The test data is saved to database */
        $this->GetComponent("logging")->SaveLogData($parameters);
    }
    /**
     * Used to log variable values to database
     *
     * It saves the value of the given variable to the framework database
     *
     * @internal
     * @param string $variable_name the name of the variable
     * @param string $variable_value the value of the variable
     */
    public function LogVariableValueToDatabase($variable_name, $variable_value) 
    {
        /** The database object is initialized */
        $this->GetComponent("frameworkdatabase")->df_initialize();
        /** The mysql table name where the variable value will be logged */
        $variable_table_name = $this->GetConfig("general", "mysql_table_names", "variable_data");
        /** If the variable value is an array then it is json encoded */
        if (is_array($variable_value)) $variable_value = json_encode($variable_value);
        /** The logging information */
        $logging_information = array(
            "database_object" => $this->GetComponent("frameworkdatabase") ,
            "logging_table_name" => $variable_table_name
        );
        /** The parameters for saving log data */
        $parameters = array(
            "logging_information" => $logging_information,
            "logging_data" => array(
                "variable_name" => $variable_name,
                "variable_value" => $variable_value,
                "created_on" => time()
            ) ,
            "logging_destination" => "database",
        );
        /** The test data is saved to database */
        $this->GetComponent("logging")->SaveLogData($parameters);
    }
    /**
     * Used to check if given expression is true
     *
     * If the function parameter is true then the valid assert count is increased
     * Otherwise the invalid assert count is increased
     *
     * @param boolean $expression an expression that is either true or false
     * @param string $description the description of the assert
     */
    final public function AssertTrue($expression, $description) 
    {
        if ($expression) $this->valid_assert_count++;
        else throw new \Exception("Failed to assert that false is true. Details: " . $description);
        return $expression;
    }
    /**
     * Used to check if given parameters are equal
     *
     * It compares value of given function parameters
     *
     * @param boolean $is_valid indicates if the parameters are equal
     * @param string $description the description of the assert
     */
    final public function AssertEqual($parameter1, $parameter2, $description) 
    {
        $is_valid = ($parameter1 == $parameter2);
        if ($is_valid) $this->valid_assert_count++;
        else throw new \Exception("Failed to assert that " . $parameter1 . " is equal to " . $parameter2 . " Details: " . $description);
        return $is_valid;
    }
    /**
     * Used to call the given script
     *
     * It calls the relevant function of the given script
     * The script name is given in application configuration
     */
    final public function CallScript() 
    {
        /** The number of unit tests run */
        $test_count = 0;
        /** The results of testing all functions*/
        $test_results = $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
        /** The result of testing single function */
        $test_result = "";
        /** The script to be called */
        $script_class_name = $this->GetConfig('testing', 'test_classes', '0');
        /** Start time for the script */
        $start_time = time();
        /** The class object is fetched from application configuration */
        $script_object = $this->GetComponent($script_class_name);
        /** The script option given in application configuration */
        $script_option = $this->GetConfig("general", "parameters", "option");
        /** The script function to call */
        $class_function = $this->GetComponent("string")->CamelCase($script_option);
        /** The script callback function is defined */
        $script_callback = array(
            $script_object,
            $class_function
        );
        try
        {
            /** The test data */
            $test_data = $this->LoadTestData($script_class_name, $class_function);
            /** The number of test cases of the test function */
            $test_cases = 0;
            if (!is_callable($script_callback)) die("Script function: " . $class_function . " does not exist in class: " . $script_class_name);
            /** The test function is called for each parameter in test data file */
            for ($count = 0;$count < count($test_data);$count++) 
            {
                call_user_func_array($script_callback, array(
                    $test_data[$count]
                ));
                $test_cases++;
            }
        }
        catch(Exception $e) 
        {
            $errorhandler_obj = $this->GetComponent("errorhandler");
            $errorhandler_obj->ExceptionHandler($e);
        }
        /** End time for the script execution */
        $end_time = time();
        $test_results.= "Result of script execution: ";
        $test_results.= $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
        $test_results.= "Number of asserts: " . $this->valid_assert_count . $this->GetConfig('general', 'line_break');
        $test_results.= "Number of test cases: " . $test_cases . $this->GetConfig('general', 'line_break');
        $test_results.= "Time taken: " . ($end_time - $start_time) . " sec" . $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
        echo $test_results;
        /** The results of testing are saved to file */
        /** If the test data folder path is not defined then an exception is thrown */
        if ($this->GetConfig('testing', 'save_test_results')) $this->SaveTestResults($test_results);
    }
    /**
     * Used to test functions in test classes
     *
     * It tests all the classes given in appliation configuration
     * Only functions that start with "Test" will be run
     * Each function that is tested should return an array with 2 keys:
     * result=> the result of the test. i.e success or error
     * message=> the message from the function. e.g error message
     *
     * @throws Exception an object of type Exception if an exception occured
     */
    final public function RunUnitTests() 
    {
        /** The number of unit tests run */
        $test_count = 0;
        /** The results of testing all functions*/
        $test_results = $this->GetConfig('general', 'line_break');
        /** The result of testing single function */
        $test_result = "";
        /** The classes to be unit tested */
        $test_classes = $this->GetConfig('testing', 'test_classes');
        /** Start time for the unit tests */
        $start_time = time();
        /** The total number of test cases */
        $total_number_of_test_cases = 0;
        /** For each class all functions that start with Test are called */
        for ($count = 0;$count < count($test_classes);$count++) 
        {
            $object_name = $test_classes[$count];
            /** The required frameworks configuration */
            $require_frameworks = $this->GetConfig('required_objects');
            $class_name = $require_frameworks[$object_name]['class_name'];
            /** The class methods are fetched */
            $class_methods = get_class_methods($class_name);
            /** The class object is fetched from application configuration */
            $test_object = $this->GetComponent($object_name);
            /** 
             * The total number of asserts before calling the given function
             * It is used to find number of asserts by the given function
             */
            $current_assert_count = 0;
            /** Each object function that starts with "Test" is called */
            for ($count1 = 0;$count1 < count($class_methods);$count1++) 
            {
                $class_function = $class_methods[$count1];
                if (strpos($class_function, "Test") === 0) 
                {
                    /** The testing callback function is defined */
                    $testing_callback = array(
                        $test_object,
                        $class_function
                    );
                    try
                    {
                        /** The test count is increased by 1 */
                        $test_count++;
                        /** If the callback function is callable then it is called with parameters in test data file */
                        if (is_callable($testing_callback)) 
                        {
                            /** The test data */
                            $test_data = $this->LoadTestData($object_name, $class_function, "unittest");
                            /** The number of test cases of the test function */
                            $test_cases = 0;
                            /** The test function is called for each parameter in test data file */
                            for ($count2 = 0;$count2 < count($test_data);$count2++) 
                            {
                                call_user_func_array($testing_callback, array(
                                    $test_data[$count2]
                                ));
                                $test_cases++;
                                //sleep(2);                                
                            }
                        }
                        else throw new \Exception("Test function: " . $class_function . " does not exist in class: " . $class_name);
                        /** The test results are updated */
                        $test_results.= ($test_count) . ") Testing function: " . $class_name . "::" . $class_function . $this->GetConfig('general', 'line_break') . "Result: passed" . $this->GetConfig('general', 'line_break') . "Number of test cases: " . $test_cases . $this->GetConfig('general', 'line_break') . "Number of asserts: " . ($this->valid_assert_count - $current_assert_count) . $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
                        /** The current assert count is updated */
                        $current_assert_count = $this->valid_assert_count;
                        /** The total number of test cases is increased */
                        $total_number_of_test_cases+= $test_cases;
                    }
                    catch(Exception $e) 
                    {
                        $errorhandler_obj = $this->GetComponent("errorhandler");
                        $errorhandler_obj->ExceptionHandler($e);
                    }
                }
            }
        }
        /** End time for the unit tests */
        $end_time = time();
        $test_results.= $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
        $test_results.= "Result of unit testing: ";
        $test_results.= $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
        $test_results.= "Number of functions tested: " . $test_count . $this->GetConfig('general', 'line_break');
        $test_results.= "Number of asserts: " . $this->valid_assert_count . $this->GetConfig('general', 'line_break');
        $test_results.= "Number of test cases: " . $total_number_of_test_cases . $this->GetConfig('general', 'line_break');
        $test_results.= "Time taken: " . ($end_time - $start_time) . " sec" . $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
        echo $test_results;
        /** The results of testing are saved to file */
        /** If the test data folder path is not defined then an exception is thrown */
        if ($this->GetConfig('testing', 'save_test_results')) $this->SaveTestResults($test_results);
    }
    /**
     * Used to run functional tests based on test data
     *
     * It reads each test data item from database
     * For each test data item it calls the function given in the test data
     *
     * @throws Exception an object of type Exception if an exception occured
     */
    final public function RunFunctionalTestsFromDatabase() 
    {
        /** The required test data */
        $test_data = array();
        /** The current module name */
        $module_name = $this->GetConfig("general", "module");
        /** The mysql table name where the data will be logged */
        $test_table_name = $this->GetConfig("general", "mysql_table_names", "test_data");
        /** The logging information */
        $logging_information = array("database_object" => $this->GetComponent("frameworkdatabase"), "table_name" => $test_table_name);
        /** The set of application url options is set */
        $application_url_mappings = array();
        /** The parameters for saving log data */
        $parameters = array();        
        /** The module name is set */
        $parameters[] = array("field_name" => "module_name", "field_value" => $module_name);
        /** The log data is fetched from database */
        $log_data = $this->GetComponent("logging")->FetchLogDataFromDatabase($logging_information, $parameters);
        /** Each log data item is converted to test data item */
        for ($count = 0;$count < count($log_data);$count++) 
        {
            /** Log data item */
            $log_data_item = $log_data[$count];
            /** The option name */
            $option_name = $log_data_item['option_name'];
            /** If the option name is not set then it is set */
            if (!isset($application_url_mappings[$option_name])) {
               /** The testing property is initialized */
               $application_url_mappings[$option_name]["testing"] = array("object_name" => "", "function_name" => "", "skip_testing" => false);
               /** The function type */
               $application_url_mappings[$option_name]['function_type'] = $log_data_item['function_type'];
            }
        }
        /** The application url mapping is saved to application configuration */
        $this->SetConfig('general', 'application_url_mappings', $application_url_mappings);
        /** The functional tests are run */
        $this->RunFunctionalTests();
    }
    /**
     * Used to test all the application urls

     *
     * It tests each application url
     * For each url it calls the template functions or controller function for the url
     *
     * @throws Exception an object of type Exception if an exception occured
     */
    final public function RunFunctionalTests() 
    {
        /** The number of functional tests run */
        $test_count = 0;
        /** The results of testing */
        $test_results = "";
        /** Start time for the funtional tests */
        $start_time = time();
        /** The total number of test cases */
        $total_number_of_test_cases = 0;
        /** The application url options to test */
        $test_options = $this->GetConfig("testing", "test_options");
        /** The application url mapping */
        $application_url_mappings = $this->GetConfig('general', 'application_url_mappings');
        /** Each application url mapping is checked */
        foreach ($application_url_mappings as $option => $option_data) 
        {
            /** If certain application url options need to be tested */
            if (count($test_options) > 0 && !in_array("all", $test_options) && !in_array($option, $test_options)) continue;
            /** The current application option is set */
            $this->SetConfig("general", "option", $option);
            /** If skip_testing configuration is set to true then the url is not tested */
            if (isset($option_data['testing']['skip_testing']) && $option_data['testing']['skip_testing']) continue;
            /** The test data */
            $test_data = $this->LoadTestData("", "", $option);
            /** The total number of test cases */
            $number_of_test_cases = (is_array($test_data)) ? count($test_data) : 0;
            /** The application function is called for each test data item */
            for ($count = 0;$count < $number_of_test_cases;$count++) 
            {
                /** The context for the test function is set to the current application context */
                $test_data[$count]['function_parameters']['context'] = $this->GetConfig("general", "parameters", "context");
                /** If the output format for the test data is not set then it is set to the default output format */
                $test_data[$count]['output_format'] = (!isset($test_data[$count]['output_format'])) ? $this->GetConfig("general", "default_output_format") : $test_data[$count]['output_format'];
                $test_data[$count]['output_format'] = ($test_data[$count]['output_format'] == '') ? $this->GetConfig("general", "default_output_format") : $test_data[$count]['output_format'];
                /** The test data is set as the application parameters */
                $this->SetConfig("general", "parameters", $test_data[$count]['function_parameters']);
                /** The testing function is run if the test object name and function name are not empty */
                if (isset($option_data['testing']) && $option_data['testing']['object_name'] != "" && $option_data['testing']['function_name'] != "") 
                {
                    /** The test object */
                    $testing_object = $this->GetComponent($option_data['testing']['object_name']);
                    /** The test function name */
                    $function_name = $option_data['testing']['function_name'];
                    /** The testing callback function is defined */
                    $testing_callback = array(
                        $testing_object,
                        $function_name
                    );
                    /** If the test data preparation function is callable, then it is called */
                    if (is_callable($testing_callback)) call_user_func_array($testing_callback, array(
                        $test_data[$count]
                    ));
                    /** If the test data preparation function is not callable, then an exception is thrown */
                    else throw new \Exception("Testing function : " . $function_name . " was not found for test object: " . $option_data['testing']['object_name']);
                }
                /** The application function output */
                $function_output = $this->GetComponent("application")->RunApplicationFunction($option);
                /** The data to be tested is returned */
                $function_output['data'] = $this->GetDataToTest($function_output['data'], $test_data[$count]);
                /** The encoded function parameters */
                $encoded_function_parameters = $this->GetComponent("encryption")->EncodeData($test_data[$count]['function_parameters']);
                /** The output is validated */
                $test_result = $this->ValidateOutput($test_data[$count]['output_format'], $function_output['data']);
                /** If the result of validating the function output is not successfull, then an exception is thrown */
                $this->AssertTrue(isset($test_result['result']) && $test_result['result'] == 'success', "Functional test for url: " . $option . " returned invalid response. Validator response: " . $test_result['message'] . " Parameters: " . $encoded_function_parameters);
            }
            /** The test results is updated */
            echo ($test_count + 1) . ") Testing url: " . $option . " result: passed. number of test cases: " . $number_of_test_cases . $this->GetConfig('general', 'line_break');
            flush();
            /** The total number of tests is increased */
            $test_count++;
            /** The total number of test cases is increased */
            $total_number_of_test_cases+= $number_of_test_cases;
        }
        /** End time for the unit tests */
        $end_time = time();
        $test_results.= $this->GetConfig('general', 'line_break');
        $test_results.= "Result of functional testing: ";
        $test_results.= $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
        $test_results.= "Number of functions tested: " . $test_count . $this->GetConfig('general', 'line_break');
        $test_results.= "Number of test cases: " . $total_number_of_test_cases . $this->GetConfig('general', 'line_break');
        $test_results.= "Number of asserts: " . $this->valid_assert_count . $this->GetConfig('general', 'line_break');
        $test_results.= "Time taken: " . ($end_time - $start_time) . " sec" . $this->GetConfig('general', 'line_break') . $this->GetConfig('general', 'line_break');
        echo $test_results;
        /** The results of testing are saved to file */
        if ($this->GetConfig('testing', 'save_test_results')) $this->SaveTestResults($test_results);
    }
    /**     
     * This function provides the data to be tested
     * It may be overriden by child classes
     *
     * It returns the data from the given output data that needs to be tested
     *
     * @param mixed $data the data that needs to be tested
     * @param array $test_data the test data     
     *
     * @return $data_to_test the data to be tested by the validator
     */
    protected function GetDataToTest($data, $test_data)
    {
        $data_to_test = $data;
        
        return $data_to_test;
    }
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
    protected function LoadTestData($object_name = "", $function_name = "", $option = "") 
    {
        /** The required test data */
        $test_data = array();
        /** The current module name */
        $module_name = $this->GetConfig("general", "module");
        /** The mysql table name where the data will be logged */
        $test_table_name = $this->GetConfig("general", "mysql_table_names", "test_data");
        /** The logging information */
        $logging_information = array(
            "database_object" => $this->GetComponent("frameworkdatabase") ,
            "table_name" => $test_table_name
        );
        /** The parameters for saving log data */
        $parameters = array();
        /** If the object name is not empty then it is set */
        if ($object_name != "") 
        {
            $parameters[] = array(
                "field_name" => "object_name",
                "field_value" => $object_name
            );
        }
        /** If the function name is not empty then it is set */
        if ($function_name != "") 
        {
            $parameters[] = array(
                "field_name" => "function_name",
                "field_value" => $function_name
            );
        }
        /** If the application url option is not empty then it is set */
        if ($option != "") 
        {
            $parameters[] = array(
                "field_name" => "option_name",
                "field_value" => $option
            );
        }
        /** The module name is set */
        $parameters[] = array(
            "field_name" => "module_name",
            "field_value" => $module_name
        );
        /** The log data is fetched from database */
        $log_data = $this->GetComponent("logging")->FetchLogDataFromDatabase($logging_information, $parameters);
        /** Each log data item is converted to test data item */
        for ($count = 0;$count < count($log_data);$count++) 
        {
            /** Log data item */
            $log_data_item = $log_data[$count];
            /** The function parameters field which is the test data is decoded */
            $log_data_item['function_parameters'] = $this->GetComponent("encryption")->DecodeData($log_data_item['function_parameters']);
            /** The function parameters are added to the test data */
            $test_data = array_merge($test_data, array(
                $log_data_item
            ));
        }
        /** If there is no test data, then empty test data is used */
        if (count($test_data) == 0) $test_data = false;
        return $test_data;
    }
    /**
     * Used to insert the given html into a html5 template
     *
     * The given html is added to the html5 template
     * The output of this function should be valid html5
     * This function can be used to validate html5 content
     *
     * @param string $html the html to be added to the html5 template
     * @param string $template_object_name the name of the template object to use. it must support the base_page template
     *
     * @return string $page_html the html string of the complete page
     */
    final public function InsertHtmlToTemplate($html, $template_object_name) 
    {
        /** The parameters for the test page */
        $parameters = array(
            "title" => "Base Page",
            "css_tags" => array() ,
            "header" => "",
            "javascript_tags" => array() ,
            "body" => $html
        );
        /** The test page is rendered with the given parameters */
        $page_html = $this->GetComponent($template_object_name)->Render("base_page", $parameters);
        return $page_html;
    }
    /**
     * Used to save code coverage information to database
     *
     * The given code coverage information is saved to database
     *
     * @param string $file_name the name of the file for which code coverage information was collected
     * @param int $code_coverage_perc the percentage of code that was executed
     * @param int $line_numbers the list of line numbers and their execution status
     * @param int $project_id the id of the project that contains the file being tested
     * @param array $form_information the code coverage form information saved in database
     */
    final private function SaveCodeCoverageToDatabase($file_name, $code_coverage_perc, $line_numbers, $project_id, $form_information) 
    {
        /** The site url */
        $site_url = $this->GetConfig("general", "site_url");
        /** The time at which the code coverage information was collected. It is set to the current unix timestamp */
        $updated_on = time();
        /** The line number information is encoded */
        $line_numbers = $this->GetComponent("encryption")->EncodeData($line_numbers);
        /** The code coverage percentage is rounded to 2 decimal places */
        $code_coverage_perc = round($code_coverage_perc, 2);
        /** The form row values */
        $form_row_values = array(
            $site_url,
            $file_name,
            $code_coverage_perc,
            $line_numbers,
            $project_id
        );
        /** If code coverage information does not exist in database */
        if (!isset($form_information['form_data'][0])) 
        {
            /** The form mode is set to add */
            $parameters['mode'] = 'add';
            /** The form data to be saved */
            $form_data = array();
            /** Each form field is added to the form data */
            for ($count = 0;$count < count($form_information['form_field_information']);$count++) {
                $form_data[] = array(
                    "field_id" => $form_information['form_field_information'][$count]['id'],
                    "field_value" => $form_row_values[$count],
                    "updated_on" => time()
                );
            }           
           /** The data type is set to code_coverage */
           $parameters['parameters']['data_type'] = 'code_coverage';
           /** The code_coverage data is saved to database */
           $this->GetComponent("unstructureddataui")->SaveFormData($form_data, $parameters);
        }
        /** If code coverage information already exists in database */
        else
        {
            /** The form mode is set to edit */
            $parameters['mode'] = 'edit';
            /** The form data to be saved */           
            $form_data = $form_information['raw_form_data'][0];
            /** The code coverage percentage field is updated */
            $form_data[2]['field_value'] = $code_coverage_perc;
            $form_data[2]['updated_on'] = $updated_on;
            /** The line numbers field is updated */
            $form_data[3]['field_value'] = $line_numbers;
            $form_data[3]['updated_on'] = $updated_on;
            /** The project id field is updated */
            $form_data[4]['field_value'] = $project_id;
            $form_data[4]['updated_on'] = $updated_on;
            /** The data type is set to code_coverage */
            $parameters['parameters']['data_type'] = 'code_coverage';
            /** The code_coverage data is saved to database */
            $this->GetComponent("unstructureddataui")->SaveFormData($form_data, $parameters);        
        }              
    }
    /**
     * Used to update the given code coverage information
     *
     * The given code coverage information is updated
     * The old code coverage information is merged with the new code coverage information
     * If a line of code was previously executed then it is marked as executed in the new code coverage information
     *
     * @param array $new_line_numbers the new code coverage information
     * @param array $old_line_numbers the old code coverage information
     *
     * @return array $updated_line_numbers the updated code coverage information
     */
    final private function UpdateCodeCoverageLineNumbers($new_line_numbers, $old_line_numbers) 
    {
        /** The updated line numbers containing code coverage informatin */
        $updated_line_numbers = array();
        /** The number of lines of code that were executed is updated */
        foreach ($new_line_numbers as $line_number => $execution_status) 
        {
            /** If the line was executed in the old code coverage, then it is marked as covered in new code coverage data */
            if (isset($old_line_numbers[$line_number]) && $old_line_numbers[$line_number] == '1') $updated_line_numbers[$line_number] = '1';
            /** Otherwise the execution status of the line is set to the execution status in the new code coverage */
            else $updated_line_numbers[$line_number] = $execution_status;
        }
        /** The updated line numbers are returned */
        return $updated_line_numbers;
    }
    /**
     * Used to get the project id for the given project information and file name
     *
     * This function checks if the file name occurs in the given project information
     * If it does then it returns the project id
     * If it cannot match any project, then it returns '-1'
     *
     * @param array $project_information the details of all the projects
     * @param string $file_name the full path to the file being tested
     *
     * @return int $project_id the matching project id
     */
    final private function GetProjectId($project_information, $file_name) 
    {
        /** The required project id */
        $project_id = -1;
        /** The updated line numbers containing code coverage informatin */
        $updated_line_numbers = array();
        /** The number of lines of code that were executed is updated */
        for ($count1 = 0; $count1 < count($project_information['form_data']); $count1++)
        {
            /** The project data */
            $project_data = $project_information['form_data'][$count1];
            /** If the list of project folders is empty then the loop continues */
            if ($project_data['file_folder_names'] == "") continue;
            /** The list of valid project folders */
            $project_folders = explode("\n", $project_data['file_folder_names']);
            /** The number of lines of code that were executed is updated */
            for ($count2 = 0; $count2 < count($project_folders); $count2++) {
                $project_folder_name = $project_folders[$count2];
                /** If the project folder name occurs in the file path of the file being tested */
                if (strpos($file_name, $project_folder_name) !== false) {
                    $project_id = $project_data['id'];
                    break;
                }
            }
        }
        /** The project id is returned */
        return $project_id;
    }
    /**
     * Used to collect code coverage information
     *
     * It collects code coverage information and saves it to database
     */
    final public function SaveCodeCoverage() 
    {
        /** The code coverage information is fetched */
        $code_coverage = xdebug_get_code_coverage();
        /** The code coverage collection is stopped */
        xdebug_stop_code_coverage();
        /** The code coverage details */
        $code_coverage_details = array();
        /** The project information is fetched from database */
        $project_information = $this->GetComponent("unstructureddataui")->GetFormInformation("projects", false);
        /** Each code coverage item is fetched and processed */
        foreach ($code_coverage as $file_name => $line_numbers) 
        {
            /** The condition used to fetch the data from database */
            $condition = array(
                "file_name" => $file_name
            );
            /** The code coverage information is fetched from database */
            $form_information = $this->GetComponent("unstructureddataui")->GetFormInformation("code_coverage", $condition);
            /** If code coverage information was already saved in database then the code coverage information is updated */
            if (isset($form_information['form_data'][0])) 
            {
                $line_numbers = $this->UpdateCodeCoverageLineNumbers($line_numbers, $form_information['form_data'][0]['line_numbers']);
            }
            /** Total number of lines of code that were executed */
            $lines_of_executed_code = 0;
            /** The total number of lines of dead code */
            $lines_of_dead_code = 0;
            /** The number of lines of code that were executed is calculated */
            foreach ($line_numbers as $line_number => $execution_status) 
            {
                /** If the line was executed */
                if ($execution_status == '1') $lines_of_executed_code++;                
            }
            /** The total number of lines of code */
            $total_lines_of_code = count(array_keys($line_numbers));
            /** The percentage of code that was executed */
            $code_coverage_perc = ($lines_of_executed_code / $total_lines_of_code) * 100;
            /** The project id is fetched */
            $project_id   = $this->GetProjectId($project_information, $file_name);
            /** The code coverage information is saved to database */
            $this->SaveCodeCoverageToDatabase($file_name, $code_coverage_perc, $line_numbers, $project_id, $form_information);
        }
    }
}

