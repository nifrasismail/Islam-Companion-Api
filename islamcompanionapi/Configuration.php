<?php
namespace IslamCompanionApi;
/**
 * Application configuration class
 *
 * Contains application configuration information
 * It provides configuration information and helper objects to the application
 *
 * @category   IslamCompanionApi
 * @package    Configuration
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class Configuration extends \Framework\Configuration\Configuration
{
    /**
     * Used to set the user configuration
     *
     * Defines the user configuration
     * The user configuration is used to override the default configuration
     *
     * @param array $parameters the application parameters given by the user
     */
    public function __construct($parameters) 
    {
        /** The application parameters are set */
        $this->user_configuration['general']['parameters'] = $parameters;
        /** The current directory */
        $current_directory                                        = getcwd();
        /** The name of the application */
        $this->user_configuration['general']['application_name'] = "Islam Companion Api";
        /** The type of the application */
        $this->user_configuration['general']['application_type'] = "api";
        /** The default application option. It is used if no option is given in url */
        $this->user_configuration['general']['default_option'] = "index";
        /** The option for logging user request is logged */
        $this->user_configuration['general']['log_user_access'] = true;      
        /** It indicates that cross domain ajax calls should be enabled */
        $this->user_configuration['general']['enable_cross_domain_ajax_calls'] = false;  
        /** It indicates that output buffering should be enabled */
        $this->user_configuration['general']['enable_output_buffering'] = true;
        /** Test parameters */             
        /** Used to indicate if module is on api server */
        $this->user_configuration['custom']['is_api_server'] = true;
        /** The duration for which the data should be cached on the server */
        $this->user_configuration['custom']['function_cache_duration'] = (2*24*3600);
        /** Used to indicate the number of verses to display in the search results */
        $this->user_configuration['custom']['verses_per_page'] = 10;
        /** The url of the web page that shows the number of times the plugin has been downloaded */
        $this->user_configuration['custom']['plugin_download_stats_url'] = 'https://wordpress.org/plugins/islam-companion/stats/';
        /** Test mode indicates the application will be tested when its run */
        $this->user_configuration['testing']['test_mode'] = (isset($parameters['test_mode']) && $parameters['test_mode'] == 'true') ? $parameters['test_mode'] : false;
        /** Development mode indicates the application is in development mode */
        $this->user_configuration['general']['development_mode'] =  (strpos($current_directory, "islamcom") !== false) ? false : true;
        /** It indicates that function output should be cached */
        $this->user_configuration['general']['enable_function_caching'] = ($this->user_configuration['general']['development_mode']) ? false : false;
        /** Test type indicates the type of application testing. i.e functional, functional-database, unit or script */
        $this->user_configuration['testing']['test_type'] = 'unit';
        /** Used to indicate that the test data should be saved */
        $this->user_configuration['testing']['save_test_data'] = false;
        /** Used to indicate that the test data should be appended */
        $this->user_configuration['testing']['append_test_data'] = false;
        /** The data folder */
        $this->user_configuration['path']['data_folder'] = 'data';
        /** The list of classes to unit test */
        $this->user_configuration['testing']['test_classes'] = array("wordpresstesting");
        /** Used to indicate the type of database to use */
        $this->user_configuration['general']['database_type'] = (isset($parameters['parameters']['database_type'])) ? $parameters['parameters']['database_type'] : "mysql";
        /** The names of the MySQL database tables to be used by the application */
        $this->user_configuration['general']['mysql_table_names'] = array(
            "sura" => "ic_quranic_suras_meta",
            "author" => "ic_quranic_author_meta",
            "aya" => "ic_quranic_text",
            "arabic_aya" => "ic_quranic_text-quran-simple",
            "meta" => "ic_quranic_meta_data",
            "hadith" => "ic_hadith_english",
            "books" => "ic_hadith_books_english"
        );
        /** The rpc server information of the WordPress site to be tested */
        $this->user_configuration['wordpress']['rpc_server_information'] = array();
        /** The id of the WordPress blog to be tested */
        $this->user_configuration['wordpress']['rpc_server_information']['blog_id'] = '1';
        /** The admin user id of the WordPress site to be tested */
        $this->user_configuration['wordpress']['rpc_server_information']['user_id'] = '1';
        /** The admin user name of the WordPress site to be tested */
        $this->user_configuration['wordpress']['rpc_server_information']['user_name'] = 'admin';
        /** The admin user password of the WordPress site to be tested */
        $this->user_configuration['wordpress']['rpc_server_information']['password'] = '4CHcrTcm7tHup4KLZkxtvXcq';
        /** The names of the WordPress custom post types to be used by the application */
        $this->user_configuration['wordpress']['custom_post_types'] = array(
            "sura" => "Suras",
            "author" => "Authors",
            "aya" => "Ayas",
            "arabic_aya" => "Ayas",
            "meta" => "Ayas",
            "hadith" => "Hadith",
            "books" => "Books"
        );
        /** The url of the Holy Quran meta data files */
        $this->user_configuration['custom']['meta_data_files'] = array(
            "author_meta" => array(
                "url" => "http://plugins.svn.wordpress.org/islam-companion/assets/data/ic_quranic_author_meta.csv",
                "fields" => array(
                    "id",
                    "file_name",
                    "name",
                    "translator",
                    "language",
                    "file_id",
                    "last_update",
                    "source",
                    "rtl",
                    "css_attributes",
                    "dictionary_url",
                    "created_on"
                ) ,
                "title_field" => "translator",
                "fields_to_ignore" => array(
                    "created_on",
                    "id"
                ) ,
                "content_field" => "",
                "key_field" => "custom_checksum",
                "type" => "meta_data"
            ) ,
            "quran_meta" => array(
                "url" => "http://plugins.svn.wordpress.org/islam-companion/assets/data/ic_quranic_meta_data.csv",
                "fields" => array(
                    "id",
                    "ayat_id",
                    "sura_ayat_id",
                    "sura",
                    "hizb",
                    "juz",
                    "manzil",
                    "page",
                    "ruku",
                    "sura_ruku",
                    "file_name"
                ) ,
                "fields_to_ignore" => array(
                    "id"
                ) ,
                "type" => "ayat_data"
            ) ,
            "quran_simple" => array(
                "url" => "http://plugins.svn.wordpress.org/islam-companion/assets/data/quran-simple.txt",
                "fields" => array(
                    "sura",
                    "ayat",
                    "arabic_text"
                ) ,
                "fields_to_ignore" => array(
                    "ayat"
                ) ,
                "type" => "ayat_data"
            ) ,
            "quran_ayas" => array(
                "url" => "http://plugins.svn.wordpress.org/islam-companion/assets/data/{file_name}",
                "fields" => array(
                    "sura",
                    "ayat",
                    "translated_text"
                ) ,
                "fields_to_ignore" => array(
                    "ayat"
                ) ,
                "type" => "ayat_data"
            ) ,
            "sura_meta" => array(
                "url" => "http://plugins.svn.wordpress.org/islam-companion/assets/data/ic_quranic_suras_meta.csv",
                "fields" => array(
                    "id",
                    "sindex",
                    "ayas",
                    "start",
                    "name",
                    "tname",
                    "ename",
                    "type",
                    "sorder",
                    "rukus",
                    "audiofile",
                    "created_on"
                ) ,
                "fields_to_ignore" => array(
                    "created_on",
                    "id"
                ) ,
                "title_field" => "tname",
                "content_field" => "",
                "key_field" => "custom_sindex",
                "type" => "meta_data"
            ),
          "hadith" => array(
                "url" => "http://plugins.svn.wordpress.org/islam-companion/assets/data/ic_hadith_english.csv",
                "fields" => array(
                    "id",
                    "source",
                    "book",
                    "book_number",
                    "hadith_number",
                    "hadith_text",
                    "title"
                ) ,
                "title_field" => "id",
                "fields_to_ignore" => array() ,
                "content_field" => "hadith_text",
                "key_field" => "custom_id",
                "type" => "hadith_data"
            ) ,
          "books" => array(
                "url" => "http://plugins.svn.wordpress.org/islam-companion/assets/data/ic_hadith_english.csv",
                "fields" => array(
                    "id",
                    "source",
                    "book",
                    "book_number"                   
                ) ,
                "title_field" => "book",
                "fields_to_ignore" => array() ,
                "content_field" => "book",
                "key_field" => "custom_id",
                "type" => "hadith_data"
            )
        );
        $this->user_configuration['required_objects']['subscribers']['class_name'] = '\IslamCompanionApi\DataObjects\Subscribers';
        $this->user_configuration['required_objects']['unstructureddataui']['class_name'] = '\Framework\Templates\BasicSite\UserInterface\UnstructuredDataUi';
        $this->user_configuration['required_objects']['structureddataui']['class_name'] = '\Framework\Templates\BasicSite\UserInterface\StructuredDataUi';
        $this->user_configuration['required_objects']['application']['class_name'] = 'IslamCompanionApi\IslamCompanionApi';
        $this->user_configuration['required_objects']['testing']['class_name'] = 'IslamCompanionApi\Test\Testing';
        $this->user_configuration['required_objects']['wordpresstesting']['class_name'] = 'IslamCompanionApi\Test\WordPressTesting';
        $this->user_configuration['required_objects']['logging']['class_name'] = '\Framework\Utilities\Logging';
        $this->user_configuration['required_objects']['profiling']['class_name'] = '\Framework\Utilities\Profiling';
        $this->user_configuration['required_objects']['filesystem']['class_name'] = '\Framework\Utilities\FileSystem';
        $this->user_configuration['required_objects']['errorhandler']['class_name'] = '\Framework\Utilities\ErrorHandler';
        $this->user_configuration['required_objects']['api']['class_name'] = '\Framework\Application\Api';
        $this->user_configuration['required_objects']['errorhandler']['parameters']['application_folder'] = 'islamcompanion';
        $this->user_configuration['required_objects']['errorhandler']['parameters']['development_mode'] = $this->user_configuration['general']['development_mode'];
        $this->user_configuration['required_objects']['errorhandler']['parameters']['custom_error_handler'] = array(
            "application",
            "CustomErrorHandler"
        );
                   
        $this->user_configuration['required_objects']['holyqurannavigator']['class_name'] = '\IslamCompanionApi\UiObjects\HolyQuranNavigator';
        $this->user_configuration['required_objects']['hadithnavigator']['class_name'] = '\IslamCompanionApi\UiObjects\HadithNavigator';
        $this->user_configuration['required_objects']['audioplayer']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HolyQuranNavigator\AudioPlayer';
        $this->user_configuration['required_objects']['divisionnumberdropdown']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HolyQuranNavigator\DivisionNumberDropdown';
        $this->user_configuration['required_objects']['rukudropdown']['class_name'] = '\IslamCompanionApi\UiObjects\\Subobjects\HolyQuranNavigator\RukuDropdown';
        $this->user_configuration['required_objects']['suradropdown']['class_name'] = '\IslamCompanionApi\UiObjects\\Subobjects\HolyQuranNavigator\SuraDropdown';
        $this->user_configuration['required_objects']['dictionaryimage']['class_name'] = '\IslamCompanionApi\UiObjects\\Subobjects\HolyQuranNavigator\DictionaryImage';
        $this->user_configuration['required_objects']['holyquranshortcodeimage']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HolyQuranNavigator\HolyQuranShortcodeImage';
        $this->user_configuration['required_objects']['hadithshortcodeimage']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\HadithShortcodeImage';
        $this->user_configuration['required_objects']['holyqurantext']['class_name'] = '\IslamCompanionApi\UiObjects\HolyQuranText';
        $this->user_configuration['required_objects']['hadithtext']['class_name'] = '\IslamCompanionApi\UiObjects\HadithText';
        $this->user_configuration['required_objects']['hadithshortcodes']['class_name'] = '\IslamCompanion\Subobjects\HadithNavigator\HadithShortCodes';
        $this->user_configuration['required_objects']['etl']['class_name'] = '\IslamCompanionApi\Scripts\Etl';
        $this->user_configuration['required_objects']['backuprestorequranicdata']['class_name'] = '\IslamCompanionApi\Scripts\BackupAndRestoreQuranicData';
        $this->user_configuration['required_objects']['loadtestdata']['class_name'] = '\IslamCompanionApi\Scripts\LoadTestData';
        $this->user_configuration['required_objects']['importquranicverses']['class_name'] = '\IslamCompanionApi\Scripts\ImportQuranicVerses';
        $this->user_configuration['required_objects']['importquranicmetadata']['class_name'] = '\IslamCompanionApi\Scripts\ImportQuranicMetaData';
        $this->user_configuration['required_objects']['wordpressquranicdataimport']['class_name'] = '\IslamCompanionApi\Scripts\WordPressQuranicDataImport';
        $this->user_configuration['required_objects']['wordpresshadithdataimport']['class_name'] = '\IslamCompanionApi\Scripts\WordPressHadithDataImport';
        $this->user_configuration['required_objects']['wordpressapplication']['class_name'] = '\Framework\Frameworks\WordPress\Application';
        $this->user_configuration['required_objects']['authors']['class_name'] = '\IslamCompanionApi\DataObjects\Authors';
        $this->user_configuration['required_objects']['rukus']['class_name'] = '\IslamCompanionApi\DataObjects\Rukus';
        $this->user_configuration['required_objects']['ayat']['class_name'] = '\IslamCompanionApi\DataObjects\Ayas';
        $this->user_configuration['required_objects']['suras']['class_name'] = '\IslamCompanionApi\DataObjects\Suras';
        $this->user_configuration['required_objects']['importhadithdata']['class_name'] = '\IslamCompanionApi\Scripts\ImportHadithData';
        $this->user_configuration['required_objects']['titledropdown']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\TitleDropdown';
        $this->user_configuration['required_objects']['bookdropdown']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\BookDropdown';
        $this->user_configuration['required_objects']['hadith']['class_name'] = '\IslamCompanionApi\DataObjects\Hadith';
        $this->user_configuration['required_objects']['hadithsourcedropdown']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\SourceDropdown';
        $this->user_configuration['required_objects']['holyquranmoreoptions']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HolyQuranNavigator\MoreOptions';
        $this->user_configuration['required_objects']['hadithmoreoptions']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\MoreOptions';
        $this->user_configuration['required_objects']['hadithsearchbox']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\SearchBox';
        $this->user_configuration['required_objects']['holyquransearchbox']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HolyQuranNavigator\SearchBox';
        $this->user_configuration['required_objects']['holyquransettings']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HolyQuranNavigator\Settings';
        $this->user_configuration['required_objects']['holyquransubscription']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HolyQuranNavigator\Subscription';
        $this->user_configuration['required_objects']['hadithsubscription']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\Subscription';
        $this->user_configuration['required_objects']['hadithsettings']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\Settings';
        $this->user_configuration['required_objects']['holyqurannavigatoroptions']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HolyQuranNavigator\Options';
        $this->user_configuration['required_objects']['hadithnavigatoroptions']['class_name'] = '\IslamCompanionApi\UiObjects\Subobjects\HadithNavigator\Options';
        /** The api key is fetched */
        $this->user_configuration['general']['api_key'] = "cfFFmhZjuLsy7W3KVrnT8CGg";
        /** Used to indicate if application should be protected by api authentication. api authentication is enabled if application is not in test mode */
        $this->user_configuration['api_auth']['enable'] = ($this->user_configuration['testing']['test_mode']) ? false : true;
        /** If the application is in development mode */
        if ($this->user_configuration['general']['development_mode']) 
        {
            /** The server name of the Islam Companion http api */
            $this->user_configuration['general']['api_server'] = "dev.islamcompanion.org";
            /** The url of the development website for Islam Companion */
            $this->user_configuration['general']['website'] = "http://dev.islamcompanion.org";
            /** The url for the Islam Companion http api */
            $this->user_configuration['general']['api_url'] = "http://dev.islamcompanion.org/index.php";
            /** The base url of the audio files */
            $this->user_configuration['custom']['audio_file_base_url'] = "http://dev.islamcompanion.org/islamcompanionapi/data/audio/";
            /** The site url for islam companion api */
            $this->user_configuration['general']['site_url'] = "http://dev.islamcompanion.org/";            
        }
        /** If the application is in production mode */
        else 
        {
            /** The server name of the Islam Companion http api */
            $this->user_configuration['general']['api_server'] = "www.islamcompanion.org";
            /** The url of the production website for Islam Companion */
            $this->user_configuration['general']['website'] = "http://www.islamcompanion.org";
            /** The url for the Islam Companion http api */
            $this->user_configuration['general']['api_url'] = "http://www.islamcompanion.org/index.php";
            /** The base url of the audio files */
            $this->user_configuration['custom']['audio_file_base_url'] = "http://www.islamcompanion.org/islamcompanionapi/data/audio/";
            /** The site url for islam companion api */
            $this->user_configuration['general']['site_url'] = "http://www.islamcompanion.org/";
            /** The font url list */
            $this->user_configuration['custom']['font_url_list'] = array();
        }
        
        /** The font url list */
        $this->user_configuration['custom']['font_url_list'] = array("urdu" => array("font_url" => $this->user_configuration['general']['website'] . "/islamcompanion/font/NafeesWeb.ttf", "font_family" => "NafeesWeb"), "arabic" => array("font_url" => $this->user_configuration['general']['website'] . "/islamcompanion/font/amiri-quran.ttf", "font_family" => "amiri-quran"));
        /** The css url list */
        $this->user_configuration['custom']['css_url_list']  = array(array("url" => $this->user_configuration['general']['website'] . "/islamcompanion/css/ic-navigator-widgets.css"));
        /** The javascript url list */
        $this->user_configuration['custom']['javascript_url_list']  = array(array("url" => $this->user_configuration['general']['website'] . "/islamcompanion/js/ic-navigator-widgets.js"));
        
        /** The rpc server url */
        $this->user_configuration['wordpress']['rpc_server_information']['server_url'] = 'http://dev.gulbahao.org/xmlrpc.php';
        /** The valid api key */
        $this->user_configuration['api_auth']['credentials'] = "cfFFmhZjuLsy7W3KVrnT8CGg";
        /** The mysql database access class is specified with parameters for the pakphp_com database */
        $this->user_configuration['required_objects']['database']['parameters'] = array();                
        /** If the application is in development mode */
        if ($this->user_configuration['general']['development_mode']) 
        {
            /** The database parameters */
            $database_parameters = array(
                "host" => "localhost",
                "user" => "nadir",
                "password" => "kcbW5eFSCbPXbJGLHvUGG8T8",
                "database" => "dev_islamcompanion",
                "debug" => "1",
                "charset" => "utf8"
            );
            /** The framework database parameters */
            $framework_database_parameters = $database_parameters;
        }
        /** If the application is in production mode */
        else
        {
            /** The database parameters */
            $database_parameters = array(
                "host" => "localhost",
                "user" => "islamcom_user",
                "password" => "05bRujawfgFUDESJrtyhNOMoZ4KXs3",
                "database" => "islamcom_islamcompanion",
                "debug" => "1",
                "charset" => "utf8"
            );
            /** The framework database parameters */
            $framework_database_parameters = $database_parameters;
        }
        /** The framework database object parameters */
        $this->user_configuration['required_objects']['database']['parameters'] = $database_parameters;
        /** The mysql database access class is specified with parameters for the pakjiddat_com database */
        $this->user_configuration['required_objects']['frameworkdatabase']['parameters'] = $framework_database_parameters;        
        /** The parameters for the authors object is set */
        $this->user_configuration['required_objects']['authors']['parameters'] = array(
            "configuration" => $this,
            "database_object" => $this->user_configuration['required_objects']['database'],
            "validate_checksum" => true,
            "key_field" => "id",
            "data_type" => "author"
        );
        /** The parameters for the suras object is set */
        $this->user_configuration['required_objects']['suras']['parameters'] = array(
            "configuration" => $this,
            "database_object" => $this->user_configuration['required_objects']['database'],
            "validate_checksum" => true,
            "key_field" => "id",
            "data_type" => "sura"
        );
        /** The parameters for the rukus object is set */
        $this->user_configuration['required_objects']['rukus']['parameters'] = array(
            "configuration" => $this,
            "database_object" => $this->user_configuration['required_objects']['database'],
            "validate_checksum" => true,
            "key_field" => "id",
            "data_type" => "meta"
        );
        /** The parameters for the ayat object is set */
        $this->user_configuration['required_objects']['ayat']['parameters'] = array(
            "configuration" => $this,
            "database_object" => $this->user_configuration['required_objects']['database'],
            "validate_checksum" => true,
            "key_field" => "id",
            "data_type" => "aya"
        );
        /** The parameters for the hadith object is set */
        $this->user_configuration['required_objects']['hadith']['parameters'] = array(
            "configuration" => $this,
            "database_object" => $this->user_configuration['required_objects']['database'],
            "validate_checksum" => true,
            "key_field" => "id",
            "data_type" => "hadith"
        );
    }
    /**
     * Used to determine if the application request should be handled by the current module
     *
     * It returns true if the host name contains api.islamcompanion.org and the url does not include '/admin/'
     * Otherwise it returns false
     *
     * @param string $module_name the current module name
     *
     * @return boolean $is_valid indicates if the application request is valid
     */
    final public static function IsValidRequest($module_name) 
    {
        /** The current host name */
        $http_host = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "");
        /** The http request uri */
        $request_uri = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : "";
        /** If the host name contains api.islamcompanion.org and the url does not include '/admin/' */
        $is_valid = ($module_name == "IslamCompanionApi") ? true : false;
        return $is_valid;
    }
}

