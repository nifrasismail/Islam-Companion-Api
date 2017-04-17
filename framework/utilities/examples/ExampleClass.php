<?php
namespace Framework\Utilities;
final class ExampleClass
{
    /** 
     * Authentication function test
     * Used to test http digest authentication
     */
    public function AuthenticationTest() 
    {
        echo "<h2>Testing function: AuthenticationTest </h2>";
        /** List of valid user credentials. used to test the http digest authentication */
        $credentials = array(
            array(
                "user_name" => "admin",
                "password" => "admin"
            ) ,
            array(
                "user_name" => "manager",
                "password" => "manager"
            )
        );
        /** The custom text to use in the authentication box that shows in the browser */
        $authentication_box_title = "Protected Area!";
        /** The authentication object is fetched */
        $authentication = UtilitiesFramework::Factory("authentication");
        /** 
         * If the user presses the cancel button then following message is shown
         * If the user entered the wrong credentials then he will be asked to login again
         */
        if (!$authentication->AuthenticateUser($credentials, $authentication_box_title)) echo "You pressed the cancel button!.";
        /** If the user entered the correct login information then the following message is shown */
        else echo "You entered the correct login information!.";
    }
    /** 
     * Caching function test
     * Used to test function caching
     */
    public function CachingTest() 
    {
        echo "<h2>Testing function: CachingTest </h2>";
        /** The database object is fetched */
        $database_parameters = array(
            "host" => "localhost",
            "user" => "nadir",
            "password" => "kcbW5eFSCbPXbJGLHvUGG8T8",
            "database" => "pakphp_com",
            "debug" => "1",
            "charset" => "utf8"
        );
        $database_obj = UtilitiesFramework::Factory("database", $database_parameters);
        $db_link = $database_obj->df_get_id();
        /** The name of the table where the cached data will be stored */
        $table_name = "pakphp_cached_data";
        /** The caching object is fetched with given parameters */
        $caching_parameters = array(
            "db_link" => $database_obj->df_get_id() ,
            "table_name" => $table_name
        );
        $caching_obj = UtilitiesFramework::Factory("caching", $caching_parameters);
        /** The data is saved to cache */
        $caching_obj->SaveDataToCache("TestFunction", array(
            "parameter 1",
            "parameter 2"
        ) , "test data");
        /** The data is fetched from cache */
        $cached_data = $caching_obj->GetCachedData("TestFunction", array(
            "parameter 1",
            "parameter 2"
        ) , true);
        var_export($cached_data);
    }
    /**
     * Encryption testing
     * Used to test encryption and decryption of text
     */
    public function EncryptionTest() 
    {
        echo "<h2>Testing function: EncryptionTest </h2>";
        /** The encryption object is fetched */
        $encryption_obj = UtilitiesFramework::Factory("encryption");
        /** The text to be encrypted */
        $original_text = "test encryption";
        /** The original text is encrypted */
        $encrypted_text = $encryption_obj->EncryptText($original_text);
        /** The encrypted text is decrypted */
        $decrypted_text = $encryption_obj->DecryptText($encrypted_text);
        /** If the original text matches the decrypted text then following message is shown */
        if ($original_text == $decrypted_text) echo "Text sucessfully decrypted";
        else echo "Text could not be decrypted";
    }
    /**
     * Database testing
     * Used to test database abstraction class
     */
    public function DatabaseTest() 
    {
        echo "<h2>Testing function: DatabaseTest </h2>";
        echo "<h4>Contents of table: pakphp_cached_data</h4>";
        /** The database object is fetched */
        $database_parameters = array(
            "host" => "localhost",
            "user" => "nadir",
            "password" => "kcbW5eFSCbPXbJGLHvUGG8T8",
            "database" => "pakphp_com",
            "debug" => "1",
            "charset" => "utf8"
        );
        $database_obj = UtilitiesFramework::Factory("database", $database_parameters);
        /** The $database_obj is initialized and cleared */
        $database_obj->df_initialize();
        /** The select query is built */
        $main_query = array();
        $main_query[0]['field'] = "*";
        /** The where clause of the query is built */
        $where_clause = array();
        $where_clause[0]['field'] = "function_name";
        $where_clause[0]['value'] = "TestFunction";
        $where_clause[0]['table'] = "pakphp_cached_data";
        $query = $database_obj->df_build_query($main_query, $where_clause, 's');
        $db_rows = $database_obj->df_all_rows($query);
        var_export($db_rows);
    }
    /**
     * Excel file testing
     * Used to test excel utility object functions
     */
    public function ExcelTest() 
    {
        echo "<h2>Testing function: ExcelTest </h2>";
        /** The PhpExcel library is included */
        $excel_file_path = "!Enter the path to the PHPExcel IOFactory.php file!";
        if (!is_file($excel_file_path)) 
        {
            echo "Please enter the path to the PHPExcel IOFactory.php file!";
            return;
        }
        include_once ($excel_file_path);
        /** The Excel class object is fetched */
        $excel_obj = UtilitiesFramework::Factory("excel");
        $data_arr = $excel_obj->ReadExcelFile("test.xls", "A2", "C13");
        var_export($data_arr);
    }
    /**
     * Email testing
     * Used to test email function
     * It support html content and attachments
     */
    public function EmailTest() 
    {
        echo "<h2>Testing function: EmailTest </h2>";
        /** 
         * The Email class object is fetched
         * The Email class requires Mail and Mail_Mime pear package
         * Change the from and to emails to your email address
         */
        include_once ("Mail.php");
        include_once ("Mail/mime.php");
        $from_email = "nadir@pakjiddat.com";
        $to_email = "nadir@pakjiddat.com";
        $email_obj = UtilitiesFramework::Factory("email");
        $is_sent = $email_obj->SendEmail(array(
            "test.xls"
        ) , $from_email, $to_email, "Utilitiesframework Test", "<h3>testing html content</h3>");
        if ($is_sent) echo "Email was successfully sent";
        else echo "Email could not be sent";
    }
    /**
     * Error handling test
     * Used to test error handling
     */
    public function ErrorHandlingTest() 
    {
        echo "<h2>Testing function: ErrorHandlingTest </h2>";
        /** The parameters from ErrorHandler object */
        $parameters = array();
        /** Custom shutdown function. It is automatically called just before script exits */
        $parameters['shutdown_function'] = array(
            $this,
            "CustomShutdown"
        );
        /** Used to indicate that application is being run from browser */
        $parameters['context'] = "browser";
        /** Used to indicate if the error message should be displayed */
        $parameters['development_mode'] = true;
        /** 
         * The name of the application folder
         * This name is checked against the file that raised the exception
         * If the file name does not include the application folder name then the exception is not handled
         */
        $parameters['application_folder'] = "utilities";
        /** Custom error handling function */
        $parameters['custom_error_handler'] = array(
            $this,
            "CustomErrorHandler"
        );
        /** The ErrorHandling class object is fetched */
        $errorhandling_obj = UtilitiesFramework::Factory("errorhandler", $parameters);
        /** Throw an exception for testing the error handling */
        throw new \Exception("Test exception!", 10);
    }
    /**
     * FileSystem testing
     * Used to test file system functions
     * It validates given html string using the W3C validator
     * It sends certain parameters as http post to the validator
     */
    public function FileSystemTest() 
    {
        echo "<h2>Testing function: FileSystemTest </h2>";
        $is_browser_application = true;
        $html_content = '<!DOCTYPE html>
									<html>
									<head>
									<meta charset="UTF-8">
									<title>Title of the document</title>
									</head>
									
									<body>
									Content of the document......
									</body>
								</html>';
        $html_content = str_replace("\r", "", $html_content);
        $html_content = str_replace("\n", "", $html_content);
        $validator_url = "https://html5.validator.nu/";
        $output_format = ($is_browser_application) ? "html" : "text";
        $show_source = ($is_browser_application) ? "yes" : "no";
        $content = array(
            "parser" => "html5",
            "out" => $output_format,
            "showsource" => $show_source,
            "asciiquotes" => "yes",
            "content" => $html_content
        );
        $headers = array(
            "Content-type: multipart/form-data; boundary=---------------------------" . strlen($html_content)
        );
        $errorhandling_obj = UtilitiesFramework::Factory("filesystem");
        $validation_results = $errorhandling_obj->GetFileContent($validator_url, "POST", $content, $headers);
        if ($is_browser_application) $validation_results = str_replace("style.css", $validator_url . "style.css", $validation_results);
        print_R($validation_results);
    }
    /**
     * String function test
     * Used to test relative to absolute conversion function
     */
    public function StringTest() 
    {
        //echo "<h2>Testing function: StringTest </h2>";
        
        /** The http content type header for xml is sent */
        header("Content-Type: text/xml");
        /** The String class object is fetched */
        $string_obj = UtilitiesFramework::Factory("string");
        /** The data that needs to be exported to rss format */
        $data = array(
            0 => array(
                'title' => 'Big Game Spot (Deutsch)',
                'image' => 'http://i.ytimg.com/vi/ohimezN65sE/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=ohimezN65sE'
            ) ,
            1 => array(
                'title' => 'Featurette "James Cameron',
                'image' => 'http://i.ytimg.com/vi/EcfmsxWf3X4/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=Kt_2nYcVwWc'
            ) ,
            2 => array(
                'title' => 'Featurette "John Connor"',
                'image' => 'http://i.ytimg.com/vi/ohimezN65sE/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=EcfmsxWf3X4'
            ) ,
            3 => array(
                'title' => 'Featurette "Kyle Reese"',
                'image' => 'http://i.ytimg.com/vi/9meOuhDHJ80/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=9meOuhDHJ80'
            ) ,
            4 => array(
                'title' => 'Featurette "Sarah Connor"',
                'image' => 'http://i.ytimg.com/vi/klDV4nM7fG4/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=klDV4nM7fG4'
            )
        );
        /** The list of tags that need to be prefixed with namespace */
        $namespace_attributes = array(
            "image",
            "source"
        );
        /** The xml namespace */
        $xml_namespace = array(
            "prefix" => "jwplayer",
            "name" => "jwplayer",
            "uri" => "http://rss.jwpcdn.com/"
        );
        /** The data is export as rss file */
        $rss_file = $string_obj->ExportToRss($data, $xml_namespace, $namespace_attributes);
        echo $rss_file;
    }
    /**
     * Custom error handling function
     * Its automatically called when there is an uncaught error or exception
     *
     * @param $log_message the error or exception message formatted by the ErrorHandling class. it contains the entire error stack trace including function parameters
     * @param $error_parameters the error parameters. they can be used to create custom error message.
     * It is an array with following keys: "error_level","error_message","error_file","error_line",
     * "error_context","error_type".
     */
    public function CustomErrorHandler($log_message, $error_parameters) 
    {
        echo "Custom error message: <br/><br/>" . $log_message;
        echo "<br/><br/>Error parameters: <br/><br/>" . var_export($error_parameters, true);
    }
    /**
     * Custom shutdown function
     * Its automatically called when the script exits
     */
    public function CustomShutdown() 
    {
        echo "Custom shudown function. Script has ended!";
    }
    /**
     * Template function test
     * Used to test Template class
     */
    public function TemplateTest() 
    {
        echo "<h2>Testing function: TemplateTest </h2>";
        /** The Template class object is fetched */
        $template_obj = UtilitiesFramework::Factory("template");
        $template_path = ".." . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "example.html";
        $tag_replacement_arr = array(
            array(
                "title" => "Page title",
                "body" => "Body title"
            )
        );
        /** The example template file is rendered */
        $template_file_contents = $template_obj->RenderTemplateFile($template_path, $tag_replacement_arr);
        var_export($template_file_contents);
    }
    /**
     * Reflection function test
     * Used to test Reflection class
     */
    public function ReflectionTest() 
    {
        echo "<h2>Testing function: ReflectionTest </h2>";
        /** 
         * The reflection example class object is created
         * It provides the test function
         * This will be the users class
         */
        include_once 'ReflectionExampleClass.php';
        $reflection_example = new ReflectionExampleClass();
        /**
         * The function that provides custom validation for the test function parameters
         * It signals an error if the length of the random string is larger than 80 characters
         */
        $custom_validation_callback = array(
            $reflection_example,
            "CustomValidation"
        );
        /** The safe_function_caller closure is fetched from the Reflection class */
        $safe_function_caller = Reflection::GetClosure();
        /** The parameters for the test function */
        $parameters = array(
            "number1" => 30,
            "number2" => 10,
            "number3" => 10,
            "data" => array(
                "type" => "integer",
                "random_string" => "<b style='text-align:center'>The result of adding the three integers is: </b>"
            )
        );
        /** The current application context */
        $context = "browser";
        /** The test function is called through the safe function caller */
        $result = $safe_function_caller($reflection_example, "AddNumbers", $parameters, $context, $custom_validation_callback);
        /** The result of adding the numbers is displayed */
        echo $result['random_string'] . $result['sum'];
    }
    /**
     * Used to get the test data
     * It returns test error data
     *
     * @return array $log_data it contains database object and test data which will be saved to database
     *     error_data => array the test error data to be logged
     *     database_obj => object the database object that will be used to store the data to database. it is an object of called DatabaseFunctions
     */
    private function GetTestErrorData() 
    {
        /** The database object is fetched */
        $database_parameters = array(
            "host" => "localhost",
            "user" => "nadir",
            "password" => "kcbW5eFSCbPXbJGLHvUGG8T8",
            "database" => "pakphp_com",
            "debug" => "1",
            "charset" => "utf8"
        );
        $database_obj = UtilitiesFramework::Factory("database", $database_parameters);
        /** The $database_obj is initialized and cleared */
        $database_obj->df_initialize();
        /** An exception object is created */
        $exception_obj = new \Exception("Test Exception");
        /** The error data */
        $error_data['error_level'] = $exception_obj->getCode();
        $error_data['error_type'] = "Exception";
        $error_data['error_message'] = $exception_obj->getMessage();
        $error_data['error_file'] = $exception_obj->getFile();
        $error_data['error_line'] = $exception_obj->getLine();
        $error_data['error_context'] = json_encode($exception_obj->getTrace());
        $error_data['server_data'] = json_encode($_SERVER);
        $error_data['mysql_query_log'] = $database_obj->df_display_query_log(true);
        $error_data['created_on'] = time();
        /** The log data to be returned */
        $log_data = array(
            "error_data" => $error_data,
            "database_obj" => $database_obj
        );
        return $log_data;
    }
    /**
     * Logging function test
     * Used to test Logging class
     * It shows how to log error data
     */
    public function LoggingTest() 
    {
        echo "<h2>Testing function: LoggingTest </h2>";
        /** The test error data is fetched */
        $log_data = $this->GetTestErrorData();
        /** The logging object is fetched */
        $logging = UtilitiesFramework::Factory("logging");
        /** The logging information */
        $logging_information = array(
            "database_object" => $log_data['database_obj'],
            "table_name" => "pakphp_error_data"
        );
        /** The parameters used to save the log data */
        $parameters = array(
            "logging_information" => $logging_information,
            "logging_data" => $log_data['error_data'],
            "logging_destination" => "database"
        );
        /** The error data is saved to database */
        $logging->SaveLogData($parameters);
        /** The log data should have been saved to database */
        echo "<h4>Error data was sucessfully logged to database</h4>";
        /** The parameters used to fetch the log data. All errors of type Exception are fetched */
        $parameters = array(
            array(
                "field_name" => "error_type",
                "field_value" => "Exception"
            )
        );
        /** The log data is fetched from database */
        $log_data = $logging->FetchLogDataFromDatabase($logging_information, $parameters);
        /** The log data is displayed */
        echo "<h4>Error data was successfully fetched from database. Following data was fetched: </h4>";
        print_r($log_data);
    }
    /**
     * Profiling function test
     * Used to test Profiling class
     * It shows how to get execution time of GetTestErrorData function
     */
    public function ProfilingTest() 
    {
        echo "<h2>Testing function: ProfilingTest </h2>";
        /** The profiling object is fetched */
        $profiling = UtilitiesFramework::Factory("profiling");
        /** The timer is started */
        $profiling->StartProfiling("execution_time");
        /** The GetTestErrorData function is called */
        $this->GetTestErrorData();
        /** The execution time for the function is returned in microseconds */
        $execution_time = $profiling->GetExecutionTime();
        /** The execution time is displayed */
        echo "<h4>The function GetTestErrorData took: " . $execution_time . " microseconds</h4>";
    }
    /**
     * Collection function test
     * Used to test Collection class
     * It shows how to remove an element from an array without losing key index sequence
     */
    public function CollectionTest() 
    {
        echo "<h2>Testing function: CollectionTest </h2>";
        /** The collection object is fetched */
        $collection = UtilitiesFramework::Factory("collection");
        /** The array to test */
        $test_data = array(
            'test1',
            'test2',
            'test3',
            'test4'
        );
        /** The item in second position is removed */
        $test_data = $collection->DeleteArrayElement($test_data, 1);
        /** The test data is displayed */
        var_dump($test_data);
    }
}
?>
