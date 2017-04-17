<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HadithNavigator;

use \IslamCompanionApi\DataObjects\Hadith as Hadith;
/**
 * This class implements the Title dropdown
 *
 * It contains functions used to generate the html for the Title dropdown
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
class TitleDropdown extends \Framework\Object\UiObject
{
    /**
     * Used to load the Book Dropdown object with data
     *
     * It loads the data from database to the object
     *
     * @since 1.0.0
     * @param array $data data used to read book information from database
     * hadith_book => the current hadith book
     * hadith_source => the current hadith source
     * hadith_language => the current hadith language
     */
    public function Read($data = "") 
    {
        /** The data is set to the objects local data property */
        $this->data = $data;
        /** The configuration object is fetched */
        $parameters['configuration'] = $this->GetConfigurationObject();
        /** The Hadith object is created */
        $hadith = new Hadith($parameters);      
        /** The hadith book */
        $hadith_book = $data['hadith_book'];
        /** The hadith source */
        $hadith_source = $data['hadith_source'];
        /** The hadith language */
        $hadith_language = $data['hadith_language'];
        /** The list of book titles in given book are fetched */
        $this->data['title_list'] = $hadith->GetBookTitles($hadith_source, $hadith_language, $hadith_book);
    }
    /**
     * Used to display the Title Dropdown
     *
     * It returns the html of the Title dropdown
     *
     * @since 1.0.0
     *
     * @return string $book_title_dropdown_html the html string for the Title Dropdown
     */
    public function Display() 
    {
        /** The current book title */
        $hadith_title = htmlentities($this->data['hadith_title']);
        /** The path to the plugin template folder */
        $plugin_template_path = $this->GetConfig("path", "application_template_path");
        /** The options html is fetched */
        $template_parameters = array();
        /** The book title data is prepared */
        for ($count = 0;$count < count($this->data['title_list']);$count++) 
        {
            /** The information for single book title */
            $book_title_information = $this->data['title_list'][$count];
            /** The book title is encoded */
            $book_title_information['title'] = htmlentities($book_title_information['title']);
            /** Used to indicate if the current title should be selected in the dropdown */
            $selected = ( $book_title_information['title'] == $hadith_title) ? 'SELECTED' : '';
            /** The shortened dropdown text */
            $dropdown_text = $book_title_information['title'];
            /** The book name is shortened */
            $dropdown_text = (strlen($dropdown_text) > 40) ? substr($dropdown_text, 0, 40)  . "..." : $dropdown_text;
            /** The title information is added to template parameters */
            $template_parameters[] = array(
                "text" => $dropdown_text,
                "value" => $book_title_information['title'],
                "selected" => $selected
            );
        }
        /** The Hadith Title dropdown options are rendered using template parameters */
        $options_html = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "option.html", $template_parameters);
        /** The Ruku dropdown templates parameters */
        $template_parameters = array(
            "name" => "ic-hadith-title",
            "id" => "ic-hadith-title",
            "title" => $hadith_title,
            "options" => $options_html
        );
        /** The Hadith Title dropdown template is rendered using the template parameters */
        $title_dropdown_html = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "select.html", $template_parameters);
        
        return $title_dropdown_html;
    }
}

