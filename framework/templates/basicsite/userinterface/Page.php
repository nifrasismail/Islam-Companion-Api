<?php
namespace Framework\Templates\BasicSite\UserInterface;
use Framework\Application\UiApplication as UiApplication;
use Framework\Object\MysqlDataObject as MysqlDataObject;
/**
 * This class extends the UiObject class and is used to display a html page
 *
 * It contains functions for displaying a html page
 *
 * @category   Framework
 * @package    UserInterface
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class Page extends UiApplication
{
    /**
     * It generates template parameters and saves them to local object property
     *
     * It sets the parameters for the base page
     *
     * @param array $data contains parameters for the base page
     *    body => the contents of the page body
     *    title => the title of the page
     *    custom_css => optional the custom css files
     *    custom_javascript => optional the custom javascript files
     */
    public function Read($data = "") 
    {
        /** The custom css files */
        $custom_css_files = (isset($data['custom_css'])) ? $data['custom_css'] : array();
        /** The custom javascript files */
        $custom_javascript_files = (isset($data['custom_javascript'])) ? $data['custom_javascript'] : array();
        /** The javascript and css tags are rendered and returned */
        $css_javascript_tags = $this->GetJavascriptAndCssFileUrls($custom_css_files, $custom_javascript_files);
        /** The header template file is rendered */
        $header_html = (isset($data['header']) && is_array($data['header'])) ? $this->GetComponent("template")->Render($data['header']['template_name'], $data['header']['template_parameters']): "";
                 
        /** The template parameters for the base page */
        $this->data_object['template_parameters'] = array(
            "title" => $data['title'],
            "body" => $data['body'],
            "header" => $header_html,
            "css_tags" => $css_javascript_tags['css_tags'],
            "javascript_tags" => $css_javascript_tags['javascript_tags']
        );
    }
    /**
     * Used to get the javascript and css tags
     *
     * It renders the javascript and css tags and returns the rendered tags
     *
     * @param array $custom_css the custom css files
     * @param array $custom_javascript the custom javascript files
     */
    private function GetJavascriptAndCssFileUrls($custom_css, $custom_javascript) 
    {
        /** The template object is fetched */
        $template_obj = $this->GetComponent('template');
        /** The default configuration parameters are fetched from application configuration */
        $framework_template_url = $this->GetConfig("path", "framework_template_url");
        /** The vendor folder url */
        $vendor_folder_url = $this->GetConfig("path", "vendor_folder_url");
        /** The application folder url */
        $application_folder_path = $this->GetConfig("path", "application_folder_url");
        /** The css and javascript files */
        $file_list = array(
            "css_files" => $custom_css,
            "javascript_files" => $custom_javascript
        );
        foreach ($file_list as $type => $file_names) 
        {
            /** The application folder path is appended to each css and javascript file */
            for ($count = 0;$count < count($file_names);$count++) 
            {
                /**  If the javascript file name is not an absolute url then the application folder path is prepended to the file name */
                if (strpos($file_names[$count], "http://") === false)
                    $file_names[$count] = $application_folder_path . "/" . $file_names[$count];
            }
            /** The file paths are updated */
            $file_list[$type] = $file_names;
        }
        /** The javascript and css tag strings are generated using the basicsite template object */
        $css_files = array_merge($file_list['css_files'], array(
            $framework_template_url . "/css/basicsite.css"
        ));
        $javascript_files = array_merge($file_list['javascript_files'], array(
            $framework_template_url . "/js/basicsite.js",
            $framework_template_url . "/js/utilities.js"
        ));
        $data = array(
            "javascript_tags" => $javascript_files,
            "css_tags" => $css_files
        );
        return $data;
    }
    /**
     * Used to return parameters required for displaying the page
     *
     * It returns the page parameters
     *
     * {@internal context browser}
     * {@internal note the function returns an array but the final output of the function is a string}
     *
     * @param array $parameters the application parameters
     *
     * @return mixed $data the parameters used to render the list page
     */
    public function HandlePage($parameters) 
    {
        /** The page main title */
        $this->data['title'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetPageTitle", array(
            $parameters
        ));
        /** The custom css files */
        $this->data['custom_css'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetCustomCssFiles", array(
            $parameters
        ));
        /** The custom javascript files */
        $this->data['custom_javascript'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetCustomJsFiles", array(
            $parameters
        ));
        /** The header template information */
        $this->data['header'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetHeaderTemplateParameters", array(
            $parameters
        ));
        /** The page body */
        $this->data['body'] = $this->GetComponent("application")->RouteFunction($parameters['data_type'], "GetPageBody", array(
            $parameters
        ));             
        
        return $this->data;
    }
    /**
     * Used to display the page
     *
     * It renders the page template file with the given template parameters
     *
     * @return string $page_html html of the page
     */
    public function Display() 
    {
        /** The template object is fetched */
        $template_obj = $this->GetComponent('template');
        /** The page is rendered */
        $page_html = $template_obj->Render("base_page", $this->data_object['template_parameters']);
        return $page_html;
    }
}

