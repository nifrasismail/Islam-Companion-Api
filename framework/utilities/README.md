<html lang="en-US">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
   </head>
   <body>
      <div>
         <header>
            <h1>PHP Utilities Framework Documentation</h1>
         </header>
         <div>
            <div>
               <ul>
                  <li><a href="#introduction">1 Introduction</a></li>
                  <li><a href="#the-benefits-of-building-your-own-utilities-framework">2 The Benefits of Building Your Own Utilities Framework</a></li>
                  <li><a href="#the-structure-of-the-php-utilities-framework">3 The Structure of the PHP Utilities Framework</a></li>
                  <li><a href="#using-the-php-utilities-framework">4 Using the Php Utilities Framework</a></li>
                  <li>
                     <a href="#php-utilities-framework-classes-description">5 PHP Utilities Framework Classes Description</a>
                     <ul>
                        <li><a href="#authentication">5.1 Authentication</a></li>
                        <li><a href="#template">5.2 Template</a></li>
                        <li><a href="#caching">5.3 Caching</a></li>
                        <li><a href="#databasefunctions">5.4 DatabaseFunctions</a></li>
                        <li><a href="#email">5.5 Email</a></li>
                        <li><a href="#encryption">5.6 Encryption</a></li>
                        <li><a href="#errorhandler">5.7 ErrorHandler</a></li>
                        <li><a href="#excel">5.8 Excel</a></li>
                        <li><a href="#filesystem">5.9 FileSystem</a></li>
                        <li><a href="#string">5.10 String</a></li>
                     </ul>
                  </li>
                  <li><a href="#extending-the-php-utilities-framework">6 Extending the PHP Utilities Framework</a></li>
                  <li><a href="#practical-use-of-the-php-utilities-framework">7 Practical Use of the PHP Utilities Framework</a></li>
                  <li><a href="#conclusion">8 Conclusion</a></li>
               </ul>
            </div>            
            <p><img class="size-medium wp-image-1739" src="http://pakjiddat.com/wp-content/uploads/2015/11/utilitiesframework-code-screenshot-300x160.png" alt="Utilities Framework Code Screenshot"></p>
            <h2><span id="Introduction">Introduction</h2>
            <p>Recently I worked on a project for a customer that required creating custom PHP&nbsp;scripts. While developing these scripts, I noticed that the code for most of the scripts was very similar. I realized that It would save me a lot of time and effort If I were to create a custom PHP library and use it for developing the scripts. I decided to develop a set of easy to use PHP components called the “Php Utilities Framework”. In this article I will describe the Php Utilities Framework and how web developers can use it for developing well tested and reliable PHP components.</p>
            <h2><span id="The_Benefits_of_Building_Your_Own_Utilities_Framework">The Benefits of Building Your Own Utilities Framework</h2>
            <p>In my experience using your own code is more useful then using someone else’s code.
            There are many reasons for this. Firstly often you do not know if the third party code is well tested. Secondly you do not know if its secure or has some limitations.
            As you get more experience with writing PHP code you will notice that much of the work you have to do, you have already done before. Maybe you wrote code some time ago that meets some immediate requirement.
            Instead of downloading code from third party sites you would benefit more from reusing your existing code.</p>
            <p>This has many advantages. For example it makes you think more about how your code works and draws your attention towards code quality and good documentation.
            If you have properly documented your code then you will enjoy reusing and applying it to solve your problems. The PHP Utilities Framework provides a set of general purpose PHP classes that you can use to develop your PHP scripts.</p>
            <h2><span id="The_Structure_of_the_PHP_Utilities_Framework">The Structure of the PHP Utilities Framework</h2>
            <p>The framework is easy to extend, so you can just add your own custom library to the framework or even a third party library that you have tested and find useful.
            The PHP Utilities Framework consists of utility classes. Each class performs a specific task. For example the class Encryption.php allows encrypting and decrypting text.</p>
            <p>All classes implement the&nbsp;<a href="https://en.wikipedia.org/wiki/Singleton_pattern" rel="nofollow">Singleton design pattern</a>&nbsp;except for the DatabaseFunctions class. The Singleton design pattern allows instantiation of only one object of a class. This makes use of the classes more efficient because only one instance of the class is created.
            The PHP Utilities Framework also implements the Factory Design Pattern. All classes are accessed through a factory class called UtilitiesFramework.</p>
            <p>This class contains a single static method called Factory that takes two parameters. First one is the name of the required utilities object, for example: encryption. The second one is the list of parameters for the object.
            The function returns an instance of the required class. The factory class allows multiple instances of a class provided each instance has different parameters. For example The DatabaseFunctions class does not implement the Singleton Design Pattern since it needs to allow for the possibility of having several connections to multiple database servers.</p>
            <p>The PHP Utilities Framework also implements&nbsp;<a href="http://www.php-fig.org/psr/psr-0/" rel="nofollow">PSR-0</a>&nbsp;and&nbsp;<a href="http://www.php-fig.org/psr/psr-4/" rel="nofollow">PSR-4</a>&nbsp;autoloading standards. These are standards for auto loading of PHP classes that are proposed by the PHP Framework Interoperability Group.
            This makes it easy to use the utilities classes in your own projects. The autoload.php file that is included with the UtilitiesFramework is not a complete implementation of the PSR standard. In order to autoload a utility class, the utility class file name must match the class name. Each utility class is documented using&nbsp;<a href="https://en.wikipedia.org/wiki/PHPDoc" rel="nofollow">PhpDoc</a>&nbsp;Syntax.</p>
            <h2><span id="Using_the_Php_Utilities_Framework">Using the Php Utilities Framework</h2>
            <p>You can download the code of PHP Utilities Framework from <a href="http://www.phpclasses.org/package/9388-PHP-General-purpose-collection-of-classes.html#download">PHP Classes</a>&nbsp;site as a ZIP file or by using composer.</p>
            <p>Using the PHP Utilities Framework is simple and requires just two lines of code. First you have to include the autoload.php file in your code. After that you have to create an instance of the utilities class. You can do this with the command:</p>
            ```php
             $utilities_obj = \Framework\Utilities\UtilitiesFramework :: Factory( $object_name ).
            ``` 
            <p><b>\Framework\Utilities</b>&nbsp;is the package namespace. All classes belong to this namespace.</p>
            <p>Once you have created the object, you can use it in your code like any class calling its functions.</p>            
               <h2><span id="PHP_Utilities_Framework_Classes_Description">PHP Utilities Framework Classes Description</h2>
               <p>Some of the classes depend on third party tools or extensions. An example usage of all the classes is given in <a href="http://www.phpclasses.org/package/9388-PHP-General-purpose-collection-of-classes.html#view_files/files/66076">Example.php</a>&nbsp;script.</p>
               <p>A brief description of each class is as follows:</p>
               <ol>
                  <li>
                     <h3><span id="authentication">Authentication</h3>
                     <p>This class provides a quick way to add basic HTTP authentication support to your site. It provides a single public method called AuthenticateUser. The method takes 2 parameters. The first is the list of user credentials.</p>
                     <p>The second is the title of the HTTP authentication box also known an HTTP authentication realm. When this function is called, the HTTP authentication headers are sent to the browser. The browser then displays HTTP authentication box. The Authentication class is used in following way:</p>
```php 
/** List of valid user credentials. used to test the http digest authentication */
$credentials = array(
    array(
     "user_name" => "admin",
     "password" => "admin"
    ),
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
if (!$authentication->AuthenticateUser($credentials, $authentication_box_title))
    echo "You pressed the cancel button!.";
/** If the user entered the correct login information then the following message is show */
else
    echo "You entered the correct login information!.";
``` 
</li>                 
<li>
                     <h3><span id="template">Template</h3>
                     <p>This class allows rendering templates. It has one public function called RenderTemplateFile. This function allows rendering a HTML template file. The HTML file contains variables inside {} tags.</p>
                     <p>The function replaces these tags with values that are given as parameters. The main features of this function is that the template parameters can contain template names. This allows a template to include other templates. The main use of this function is to separate the HTML from the PHP code.</p>
                     <p>HTML elements with complex layouts such as tables can also be rendered using this function. For example you can have separate template files for table, row and column tags. You give this function the table data and it renders the table template using the given data. The templates class is used in following way:</p>
```php 
/** The Template class object is fetched */
$template_obj = UtilitiesFramework::Factory("template");
$template_path = "templates".DIRECTORY_SEPARATOR."example.html";
$tag_replacement_arr = array(array("title"=>"Page title","body"=>;"Body title"));
/** The example template file is rendered */
$template_file_contents = $template_obj->RenderTemplateFile($template_path, $tag_replacement_arr);
print_R($template_file_contents);
``` 
</li>
                  <li>
                     <h3><span id="caching">Caching</h3>
                     <p>This class provides caching of data that may take some time to generate like for instance database results. It provides two public methods called SaveDataToCache and GetCachedData.</p>
                     <p>These methods are used to save data to cache and fetch data from cache. I used it for implementing function caching. If some function needs to do a lot of processing or needs to make expensive API calls, then you can use this method to save the function output to database.</p>
                     <p>The next time the function is called you can just use GetCachedData to return the cached data. The Caching class is used in following way:</p>
```php 
/** The database object is fetched */
$database_parameters = array(
    "host" => "localhost",
    "user" => "nadir",
    "password" => "xxxxxxxxxxxxxxxx",
    "database" => "dev_pakphp",
    "debug" => "1"
);
$database_obj = UtilitiesFramework::Factory("database", $database_parameters);
$db_link = $database_obj->df_get_id();
/** The prefix of the table. e.g if prefix name is example_ then table name is example_cached_data */
$table_prefix = "example_";
/** The caching object is fetched with given parameters */
$caching_parameters = array(
     "db_link" => $database_obj->df_get_id(),
     "table_prefix" => $table_prefix
);
$caching_obj = UtilitiesFramework::Factory("caching", $caching_parameters);
/** The data is saved to cache */
$caching_obj->SaveDataToCache("TestFunction", array(
    "parameter 1",
    "parameter 2"
), "test data");
/** The data is fetched from cache */
$cached_data = $caching_obj->GetCachedData("TestFunction", array(
    "parameter 1",
    "parameter 2"
), true);
print_R($cached_data);
``` 
</li>

<li>
                     <h3><span id="DatabaseFunctions">DatabaseFunctions</h3>
                     <p>This is a database wrapper class for mysqli functions. It contains functions that make it easier to work with MySQL databases. It also supports MySQL transactions and Query Debugging.</p>
                     <p>For example if you have some script that does a lot of database updates, then you can use the script to commit the database transactions at the end of all the database updates. This will prevent your data from being corrupted in case of errors.</p>
                     <p>To implement this using DatabaseFunctions you can use df_toggle_autocommit function. This enables or disables automatically committing the MySQL query. After your database updates have completed you can use df_commit to commit the database transactions. You can also use df_rollback function to rollback the transaction.</p>
                     <p>If you want to see the queries run by the DatabaseFunctions class then you can just call df_display_query_log function. This displays all the MySQL queries run by the object. The DatabaseFunctions class is used in following way:</p>
```php 
/** The database object is fetched */
$database_parameters = array(
    "host" => "localhost",
    "user" => "nadir",
    "password" => "kcbW5eFSCbPXbJGLHvUGG8T8",
    "database" => "dev_pakphp",
    "debug" => "1"
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
$where_clause[0]['table'] = "example_cached_data";
 
$query = $database_obj->df_build_query($main_query, $where_clause, 's');
$db_rows = $database_obj->df_all_rows($query);
print_R($db_rows); 
``` 
</li>

<li>
                     <h3><span id="email">Email</h3>
                     <p>This class allows sending HTML email with attachments. It uses the Mail and Mail_Mime PEAR classes. It contains a single public method SendEmail. This takes 5 parameters: $attachment_file, $from, $to, $subject and $text. The $attachment_files parameters is an array containing absolute paths to the files that need to be sent as attachments. The Email class is used in following way:</p>
```php 
/** 
 * The Email class object is fetched 
 * The Email class requires Mail and Mail_Mime pear package
 * Change the from and to emails to your email address
 */
include_once("Mail.php");
include_once("Mail/mime.php");

$from_email = "nadir@pakjiddat.com";
$to_email = "nadir@pakjiddat.com";

$email_obj = UtilitiesFramework::Factory("email");
$is_sent = $email_obj->SendEmail(array(
"test.xls"
), $from_email, $to_email, "Utilitiesframework Test", "<h3>testing html content</h3>");

if ($is_sent)
    echo "Email was successfully sent";
else
    echo "Email could not be sent";
``` 
</li> 
                  
<li>
                     <h3><span id="encryption">Encryption</h3>
                     <p>This class uses the php mcrypt extension to encrypt and decrypt text. It contains two public functions EncryptText and DecryptText. These functions allow encrypting and decrypting strings. The EncryptText function encrypts the text and then encodes it using base64 encoding. The DecryptText function does the reverse. i.e it decrypts the text and then decodes it using base64 decoding. The Encryption class is used in following way:</p>
```php 
/** The encryption object is fetched */
$encryption_obj = UtilitiesFramework::Factory("encryption");
/** The text to be encrypted */
$original_text = "test encryption";
/** The original text is encrypted */
$encrypted_text = $encryption_obj->EncryptText($original_text);
/** The encrypted text is decrypted */
$decrypted_text = $encryption_obj->DecryptText($encrypted_text);
/** If the original text matches the decrypted text then following message is shown */
if ($original_text == $decrypted_text)
    echo "Text sucessfully decrypted";
else
    echo "Text could not be decrypted";
``` 
</li>

<li>
                     <h3><span id="errorhandler">ErrorHandler</h3>
                     <p>This class provides error handling and logging functions. It allows the user to register custom error handler and shutdown functions using PHP callback functions. It also displays a well formatted error message that includes the debug_backtrace function information. The error message layout is displayed using HTML template files so it can be easily edited. The ErrorHandler class is used in following way:</p>
```php 
/** The parameters from ErrorHandler object */
$parameters = array();

/** Custom shutdown function. It is automatically called just before script exits */
$parameters['shutdown_function'] = array(
    $this,
    "CustomShutdown"
);

/** Used to indicate if the error message should be displayed */
$parameters['display_error'] = true;
/** The email address that will get the error message email */
$parameters['log_email'] = "";
/** The error log file name */
$parameters['log_file_name'] = "";
/** The smtp mail headers to include with the error email. e.g array("Subject"=>"Error email") */
$parameters['log_email_header'] = "";
/** Custom error handling function */
$parameters['custom_error_handler'] = array(
    $this,
    "CustomErrorHandler"
);

/** The ErrorHandling class object is fetched */
$errorhandling_obj = UtilitiesFramework::Factory("errorhandler", $parameters);
/** Throw an exception for testing the error handling */
throw new \Exception("Test exception!", 10);
``` 
</li> 
                  
<li>
                     <h3><span id="excel">Excel</h3>
                     <p>This class is a wrapper around the <a href="https://github.com/PHPOffice/PHPExcel" rel="nofollow">PhpExcel library</a>. It has one public function called ReadExcelFile. This function takes 3 parameters. The absolute path to the excel file to be read. The start and end cell coordinates like for instance A3. It returns a PHP array containing the excel file contents. The&nbsp;PHPExcel.php class must be included before calling this function. The Excel class is used as follows:</p>
```php 
/** The PhpExcel library is included */
$excel_file_path = "!Enter the path to the PHPExcel IOFactory.php file!";
if (!is_file($excel_file_path)) {
    echo "Please enter the path to the PHPExcel IOFactory.php file!";
    return;
}

include_once($excel_file_path);
/** The Excel class object is fetched */
$excel_obj = UtilitiesFramework::Factory("excel");
$data_arr = $excel_obj->ReadExcelFile("test.xls", "A2", "C13");
print_R($data_arr);
```  
</li>
                  
<li>
                     <h3><span id="filesystem">FileSystem</h3>
                     <p>This class provides simple functions for accessing the file system. It provides functions for reading, writing, deleting files and reading folders. It also allows uploading files and getting remote file contents using the HTTP POST method using the curl extension. The FileSystem class is used in following way:</p>
```php 
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
if ($is_browser_application)
    $validation_results = str_replace("style.css", $validator_url . "style.css", $validation_results);

print_R($validation_results);
``` 
</li>
 
<li>
                     <h3><span id="string">String</h3>
                     <p>This class provides useful string manipulation functions. It provides functions that convert relative to absolute urls, check string if it is in the JSON format, convert string to camel case and concatenate strings. The String class is used in following way:</p>
```php 
/** The String class object is fetched */
$string_obj = UtilitiesFramework::Factory("string");
$main_url = "https://pear.php.net/manual/en/";
$rel_url = "package.mail.mail.send.php";
$abs_url = $string_obj->ConvertRelUrlToAbsUrl($main_url, $rel_url);
print_R($abs_url);
``` 
</li>
                  
</ol>
               <h2><span id="Extending_the_PHP_Utilities_Framework">Extending the PHP Utilities Framework</h2>
               <p>It is easy to extend the PHP Utilities Framework with your own class or with a third party class. The only requirements are that your class name must match the name of the file that has the class. Also the namespace of the class must be <strong>\Framework\Utilities</strong>.</p>
               <h2><span id="Practical_Use_of_the_PHP_Utilities_Framework">Practical Use of the PHP Utilities Framework</h2>
               <p>The PHP Utilities Framework is well suited for use in PHP projects that need general purpose PHP classes. If you need to easily add error handling, HTTP authentication or a simple template system, then you will find the PHP Utilities Framework useful.</p>
               <h2><span id="conclusion">Conclusion</h2>
               <p>This article presented an example of a general purpose PHP utilities framework of classes. You can use this package yourself or build your own utilities framework package inspired by the approach of this package.</p>
               <p>If you liked this article or you have questions about building a PHP utilities framework, you can contact me on my website <a href="http://pakjiddat.com/contact-us">Pak Jiddat</a>.</p>
            </div>
      </div>
   </body>
   </html>
