<?php
namespace IslamCompanionApi\UiObjects;

use \IslamCompanionApi\DataObjects\Hadith as Hadith;
use \IslamCompanionApi\DataObjects\HadithNavigation as HadithNavigation;
use \IslamCompanionApi\DataObjects\Authors as Authors;
/**
 * This class implements the HadithNavigator class
 *
 * It contains functions used to generate the Hadith Navigator widget
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class HadithNavigator extends \Framework\Object\UiObject
{
    /**
     * Used to get list of user interface objects of the Hadith Navigator
     *
     * It returns the user interface object details
     *
     * @return array $ui_objects the details of the user interface objects of the Hadith Navigator
     */
    private function GetUiObjectDetails() 
    {
        $ui_objects = array(
            "titledropdown" => array(
                "parameters" => array(
                    "hadith_title" => $this->data['navigator_data']['hadith_title'],
                    "hadith_book" => $this->data['navigator_data']['hadith_book'],
                    "hadith_source" => $this->data['navigator_data']['hadith_source'],
                    "hadith_language" => $this->data['navigator_data']['hadith_language']
                ) ,
                "template_parameter" => "hadith_titles_select_box",
            ) ,
            "bookdropdown" => array(
                "parameters" => array(
                    "hadith_book" => $this->data['navigator_data']['hadith_book'],
                    "hadith_source" => $this->data['navigator_data']['hadith_source'],
                    "hadith_language" => $this->data['navigator_data']['hadith_language']
                ) ,
                "template_parameter" => "hadith_books_select_box",
            ) ,
            "hadithtext" => array(
                "parameters" => array(
                    "parameters" => array(
                        "hadith_title" => $this->data['navigator_data']['hadith_title'],
                        "hadith_book" => $this->data['navigator_data']['hadith_book'],
                        "hadith_source" => $this->data['navigator_data']['hadith_source'],
                        "hadith_language" => $this->data['navigator_data']['hadith_language'],
                        "hadith_tools" => $this->data['navigator_data']['tools']
                     ),
                     "user_interface" => "navigator"
                 ),
                "template_parameter" => "hadith_text"
            ) ,
            "hadithsourcedropdown" => array(
                "parameters" => array(
                    "hadith_source" => $this->data['navigator_data']['hadith_source']
                ) ,
                "template_parameter" => "hadith_source_select_box"
            ) ,            
            "hadithmoreoptions" => array(
                "parameters" => array(
                    "type" => "Hadith",
                    "hadith_title" => $this->data['navigator_data']['hadith_title'],
                    "hadith_book" => $this->data['navigator_data']['hadith_book'],
                    "hadith_source" => $this->data['navigator_data']['hadith_source'],
                    "hadith_language" => $this->data['navigator_data']['hadith_language']
                ) ,
                "template_parameter" => "hadith_more_options"
            ) ,          
            "hadithsearchbox" => array(
                "parameters" => array(
                    "type" => "Hadith",
                    "language" => $this->data['navigator_data']['hadith_language'],
                    "narrator" => "Mohammed Marmaduke William Pickthall"
                ) ,
                "template_parameter" => "search_box"
            ) ,          
            "hadithsubscription" => array(
                "parameters" => array(
                    "type" => "Hadith"
                ) ,
                "template_parameter" => "subscription"
            ) ,
            "hadithnavigatoroptions" => array(
                "parameters" => array(
                    "type" => "Hadith",
                    "options" => $this->data['navigator_data']['options']
                ) ,
                "template_parameter" => "options"
            )
        );
        return $ui_objects;
    }
    /**
     * Used to load the Hadith Navigator with data
     *
     * It loads the data from database to the object
     *
     * @param array $data data used to read hadith information from database
     *    hadith_language => the language for the hadith text
     *    hadith_source => the source for the hadith text
     *    hadith_book => the current hadith book
     *    hadith_title => the current hadith book title
     *    template => string [full~dashboard] the type of template. it controls the layout of the navigator     
     *    action => [next~previous~current~book_box~title_box] the action performed by the user on the Hadith Navigator
     */
    public function Read($data = "") 
    {
        /**
         * The updated data is calculated based on user selection
         * e.g if user had selected book dropdown then the title information will change
         */
        $this->data['navigator_data'] = $this->GetUpdatedNavigatorData($data);
        /** The ui object names and their parameters */
        $this->data['ui_object_details'] = $this->GetUiObjectDetails();
        /** Each user interface object is loaded with data */
        foreach ($this->data['ui_object_details'] as $object_name => $object_details) 
        {
            /** The user interface object is fetched */
            $this->sub_items[$object_name] = $this->GetComponent($object_name);
            /** The user interface object is loaded with data */
            $this->sub_items[$object_name]->Read($object_details['parameters']);
        }
    }
    /**
     * Used to return the Navigator data
     *
     * It returns the data used to display the Navigator
     * This data can be considered as the state of the Hadith Navigator
     * By saving this data, the state of the Hadith Navigator is saved
     *
     * @return array $data data used to display the Hadith Navigator
     *    hadith_language => the language for the hadith text
     *    hadith_source => the source for the hadith text
     *    hadith_book => the current hadith book
     *    hadith_title => the current hadith book title
     */
    public function GetNavigatorData() 
    {
        /** The hadith language */
        $hadith_language = $this->data['navigator_data']['hadith_language'];
        /** The hadith source */
        $hadith_source = $this->data['navigator_data']['hadith_source'];
        /** The hadith book */
        $hadith_book = $this->data['navigator_data']['hadith_book'];
        /** The hadith book title */
        $hadith_book_title = $this->data['navigator_data']['hadith_title'];
        /** The template name to use */
        $template = $this->data['navigator_data']['template'];        
        /** The Hadith navigator data */
        $data = array(
            "hadith_language" => $hadith_language,
            "hadith_source" => $hadith_source,
            "hadith_book" => $hadith_book,
            "hadith_title" => $hadith_book_title,
            "template" => $template
        );
        return $data;
    }
    /**
     * Used to display the Hadith Navigator
     *
     * It returns the html of the Hadith Navigator
     *
     * @return string $hadith_navigator_html the html string for the Hadith Navigator
     */
    public function Display() 
    {
        /** The configuration object is fetched */
        $parameters['configuration'] = $this->GetConfigurationObject();
        /** The authors object is created */
        $authors = new Authors($parameters);
        /** The template file name for the Hadith Navigator */
        $template_file_name = "hadith_" . $this->data["navigator_data"]["template"] . ".html";
        /** The rtl attribute of the language */
        $rtl = $authors->GetLanguageRtl($this->data["navigator_data"]["hadith_language"]);
        /** The navigator buttons css class */
        $navigator_buttons_css_class = ($rtl) ? "ic-navigator-class-rtl" : "ic-navigator-class-ltr";
        /** The path to the plugin template folder */
        $plugin_template_path = $this->GetConfig("path", "application_template_path");
        /** The hadith books class name */
        $hadith_books_class = "hadith-books-class";
        /** The hadith title class name */
        $hadith_title_class = "hadith-titles-class";
        /** The hadith source class name */
        $hadith_source_class = "hadith-source-class";
        /** The url to the template folder */
        $application_template_url = $this->GetConfig("path", "application_template_url");
        /** The more options image url */
        $more_options_image_url = $application_template_url . "/images/more.png"; 
        /** The less options image url */
        $less_options_image_url = $application_template_url . "/images/less.png";
        /** The dictionary url and language rtl information are fetched */
        $dictionary_information = $authors->GetDictionaryInformation($this->data["navigator_data"]["hadith_language"]);
        /** The dictionary url */
        $dictionary_url = $dictionary_information['dictionary_url'];
        /** The template parameters */
        $template_parameters = array(
            "id" => "ic-hadith-dictionary_url",
            "name" => "ic-hadith-dictionary_url",
            "value" => $dictionary_url
        );
        $dictionary_url_html = $this->GetComponent("template")->Render("hidden", $template_parameters);
        /** The template parameters */
        $template_parameters = array(
            "id" => "ic-hadith-more-options",
            "src" => $more_options_image_url,
            "alt" => "More Options",
            "title" => "More Options",
            "css_class" => "ic-cursor",
            "onclick" => 'IC_Navigators.ToggleMoreOptions("show", "div.section-padding.hadith-more-options-section", "ic-hadith-more-options", "ic-hadith-less-options", "Hadith");'
        );
        $more_options_html = $this->GetComponent("template")->Render("image", $template_parameters);
        /** The template parameters */
        $template_parameters = array(
            "id" => "ic-hadith-less-options",
            "src" => $less_options_image_url,
            "alt" => "Less Options",
            "title" => "Less Options",
            "css_class" => "ic-cursor ic-hidden",
            "onclick" => 'IC_Navigators.ToggleMoreOptions("hide", "div.section-padding.hadith-more-options-section", "ic-hadith-more-options", "ic-hadith-less-options", "Hadith");'
        );
        $less_options_html = $this->GetComponent("template")->Render("image", $template_parameters);
        /** The navigator settings */
        $navigator_settings    = array("hadith_language" => $this->data["navigator_data"]["hadith_language"]);
        /** The navigator settings are encoded */
        $navigator_settings    = $this->GetComponent("encryption")->EncodeData($navigator_settings);
        /** The parameters used to render the Hadith Navigator Template */
        $template_parameters = array(
            "hadith_books_class" => $hadith_books_class,
            "hadith_titles_class" => $hadith_title_class,
            "hadith_source_class" => $hadith_source_class,
            "more_options_image" => $more_options_html,
            "less_options_image" => $less_options_html,
            "navigator_class" => $navigator_buttons_css_class,
            "dictionary_url" => $dictionary_url_html,
            "navigator_settings" => $navigator_settings,
            "clipboard_text_name" => "ic_hadith_clipboard_text",
            "clipboard_text_id" => "ic_hadith_clipboard_text"
        );
        /** Each user interface object is displayed */
        foreach ($this->data['ui_object_details'] as $object_name => $object_details) 
        {
            /** The user interface object */
            $ui_object = $this->sub_items[$object_name];
            /** The user interface object html */
            $ui_object_html = $ui_object->Display();
            /** The name of the template parameter that will be replaced with html of the user interface object */
            $template_parameter_name = $object_details['template_parameter'];
            /** The user interface object html is added to the Hadith Navigator template parameters */
            $template_parameters[$template_parameter_name] = $ui_object_html;
        }       
        /** The Hadith Navigator template is rendered using the template parameters */
        $hadith_navigator_html = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . $template_file_name, $template_parameters);
        return $hadith_navigator_html;
    }
    /**
     * Used to get the updated navigator data
     *
     * It checks the value of action and calculates the new Hadith Navigator configuration data
     * It returns the calculated navigation data
     * This updated data is used to generate the Hadith Navigator
     *
     * @param array $data the updated navigator data
     *
     * @return array $updated_data the updated Hadith Navigator data
     */
    private function GetUpdatedNavigatorData($data) 
    {
        /** The updated data */
        $updated_data = array();
        /** The configuration object is fetched */
        $parameters['configuration'] = $this->GetConfigurationObject();
        /** The Navigation object is created */
        $navigation = new HadithNavigation($parameters);
        /** The Hadith object is created */
        $hadith = new Hadith($parameters);
        /** The data type or short table name to use */
        $data_type = "hadith";
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => $data_type,
            "key_field" => ""
        );
        /** If the action is hadith_source_box */
        if ($data['action'] == "hadith_source_box") {
            /** Hadith book and Hadith title are set to empty */
            $data['hadith_book'] = $data['hadith_title'] = "";
        }
        /** The table name and field name are set */
        $hadith->SetMetaInformation($meta_information);
        /** If the book name is empty then the first book name in the hadith source is fetched */
        if ($data['hadith_book'] == "") 
        {
            /** The condition for fetching the hadith data */
            $where_clause = array(
                array(
                    'field' => "source",
                    'value' => $data['hadith_source'],
                    'operation' => "=",
                    'operator' => ""
                )
            );
            /** The parameters used to read the data from database */
            $parameters = array(
                "fields" => "DISTINCT(book) as book, title",
                "condition" => $where_clause,
                "read_all" => true,
                "order" => array(
                    "field" => "id",
                    "type" => "int",
                    "direction" => "ASC"
                )
            );
            /** The meta data is read */
            $hadith->Read($parameters);
            /** The book data is fetched */
            $books = $hadith->GetData();
            /** The first book is used as the current hadith book */
            $data['hadith_book'] = $books[0]['book'];
            /** The first book title is used as the current hadith book title */
            $data['hadith_title'] = $books[0]['title'];
        }
        /** The updated data containing the book, title */
        $updated_data = $navigation->NextPreviousSelection($data['hadith_source'], $data['hadith_language'], $data['hadith_book'], $data['hadith_title'], $data['action']);
        /** The new hadith book is set */
        $data['hadith_book'] = (isset($updated_data['hadith_book'])) ? $updated_data['hadith_book'] : $data['hadith_book'];
        /** The new hadith title is set */
        $data['hadith_title'] = (isset($updated_data['hadith_title'])) ? $updated_data['hadith_title'] : $data['hadith_title'];
        /** The updated data is set to data */
        $updated_data = $data;
        return $updated_data;
    }
}

