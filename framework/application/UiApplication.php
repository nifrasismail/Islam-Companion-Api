<?php
namespace Framework\Application;
use \Framework\Configuration\Base as Base;
/**
 * This class implements the UiApplication class
 *
 * Abstract class
 * Must be extended by child class
 * It contains functions that help in constructing user interfaces applications
 *
 * @category   Framework
 * @package    Application
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
abstract class UiApplication extends Application
{
    /**
     * The data for the user interface
     */
    protected $data;
    /**
     * Used to load the object with data
     *
     * It loads the data from a data source to the ui_object property. It must be implemented by a child class
     *
     * @param array $data optional data used to read from database
     */
    function Read($data = "") 
    {
    }
    /**
     * Used to save the data in the object
     *
     * It saves the data in the object to database. It must be implemented by child class
     */
    function Save() 
    {
    }
    /**
     * Used to display the data of the object in a template
     *
     * It renders the data in the object to a template. It must be implemented by child class
     */
    function Display() 
    {
    }
    /**
     * Used to delete the given object
     *
     * It deletes the current object. It must be implemented by child class
     */
    function Delete() 
    {
    }
    /**
     * Used to redirect the user
     *
     * It redirects the user by sending http location header
     *
     * @param string $url the redirect url
     * @throws object Exception an exception is thrown if http headers were already sent
     */
    final public function Redirect($url) 
    {
        /** If the http headers were not sent, then the user is redirected to the given url */
        if (!headers_sent($filename, $linenum)) 
        {
            header("Location: " . $url);
        }
        /** An exception is thrown if http headers were already sent */
        else 
        {
            throw new \Exception("Headers already sent in " . $filename . " on line " . $linenum . "\n");
        }
    }
    /**
     * Used to authenticate the user submitted login credentials
     *
     * It checks if the credentials submitted by the user match the credentials in the application configuration file
     * If the credentials match, then the user is redirected to the given url
     * If the credentials do not match then the function returns false
     *
     * @param string $user_name the user name
     * @param string $password the user password
     *
     * @return boolean $is_valid used to indicate if the given credentials are valid
     */
    public function ValidateCredentials($user_name, $password) 
    {
        /** Indicates if the given credentials are valid */
        $is_valid = false;
        /** The valid credentials are fetched */
        $credentials = $this->GetConfig("session_auth", "credentials");
        /** If single sign on is enabled in application configuration then the function returns true */
        if ($this->ConfigurationExists("general", "parameters", "single_sign_on")) {
            $is_valid = true;
            return $is_valid;
        }
        $credentials = $this->GetConfig("session_auth", "credentials");
        /** Each valid credential is checked against the given credentials */
        for ($count = 0;$count < count($credentials);$count++) 
        {
            /** If the user name and/or password incorrect then the user is redirected to the given url */
            if ($credentials[$count]['user_name'] == $user_name && $credentials[$count]['password'] == $password) 
            {
                /** is_valid is set to true */
                $is_valid = true;
                /** The session variable is_logged_in is set */
                $this->SetSessionConfig("is_logged_in", "1", false);
                /** The session variable full_name is set */
                $this->SetSessionConfig("full_name", $credentials[$count]['full_name'], false);
            }
        }
       
        return $is_valid;
    }
    /**
     * Used to handle application request
     *
     * It is the main entry point for the application
     * It initializes the application and returns the application response depending on the application context
     *
     * @param string $context the context of the application. e.g local api, remote api, browser or command line
     * @param array $parameters the application parameters
     * @param string $default_module optional the default module name to use in case no module name is specified by the calling application
     *
     * @throws object Exception exception is thrown if an invalid context was specified
     * @throws object Exception exception is thrown if module name was not specified by the application
     * @throws object Exception exception is thrown if the application context is command line and command line arguments are not in correct format
     *
     * @return string $response the application response
     */
    public static function HandleRequest($context, $parameters, $default_module = false) 
    {
        /** The http headers are sent */
        self::GenerateHeaders();
        /** The parent function is called */
        parent::HandleRequest($context, $parameters, $default_module);
    }
    /**
     * Default url option handler for logout action
     * Used to logout the user
     *
     * It unsets the is_logged_in session variable
     * It redirects the user to the login page
     *
     * {@internal context browser}
     */
    final public function HandleLogout() 
    {
        /** The session variable is_logged_in is unset */
        $this->SetSessionConfig("is_logged_in", false, true);
        /** The login url */
        $site_url = $this->GetConfig("general", "site_url");
        /** The user is redirected to the login url */
        $this->Redirect($site_url);
    }
        /**
     * Used to authenticate the user
     *
     * This function is used to authenticate the user
     * It first authenticates the user
     * If the user is not logged in then he is redirected to a url. e.g login page url
     *
     */
    public function SessionAuthentication() 
    {
        /** The application parameters containing session data are fetched **/
        $session = $this->GetConfig("general", "session");
        $session_authentication = $this->GetConfig("session_auth");
        /** The current module name */
        $module_name = $this->GetConfig("general", "module");
        /** The login url */
        $site_url = $this->GetConfig("general", "site_url");
        /** The current url */
        $current_url = $this->GetConfig("path", "current_url");       
        /** 
         * The user is redirected to the login url if the current page is not same as login url and
         * The logged_in session variable is not set
         */
        if ($current_url != $site_url && (!$this->GetSessionConfig('is_logged_in'))) 
        {
            $this->Redirect($site_url);
        }
    }
    /**
     * Used to authenticate the user using http authentication
     *
     * It displays an error to the user if http authentication fails
     *
     */
    public function HttpAuthentication() 
    {
        /** The http authentication parameters are fetched **/
        $http_authentication = $this->GetConfig('http_auth');
        /** The http authentication method is called **/
        $is_valid_user = \Framework\Utilities\UtilitiesFramework::Factory("authentication")->AuthenticateUser($http_authentication['credentials'], $http_authentication['realm']);
        /** If the authentication method returns an error then an error is shown to the user in browser and script execution ends **/
        if (!$is_valid_user) die("Please enter a valid user name and password");
    }    
}

