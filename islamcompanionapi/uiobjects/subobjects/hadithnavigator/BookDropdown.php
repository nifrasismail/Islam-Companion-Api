<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HadithNavigator;

use \IslamCompanionApi\DataObjects\Hadith as Hadith;

/**
 * This class implements the Book dropdown
 *
 * It contains functions used to generate the html for the Book dropdown
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
class BookDropdown extends \Framework\Object\UiObject
{
    /**
     * Used to load the Book Dropdown object with data
     *
     * It loads the data from database to the object
     *
     * @since 1.0.0
     * @param array $data data used to read book title information from database    
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
        $this->data['book_list'] = $hadith->GetBooks($hadith_source, $hadith_language);
    }
    /**
     * Used to display the Book Dropdown
     *
     * It returns the html of the Book dropdown
     *
     * @since 1.0.0
     *
     * @return string $book_dropdown_html the html string for the Book Dropdown
     */
    public function Display() 
    {
        /** The current book name */
        $hadith_book = $this->data['hadith_book'];
        /** The path to the plugin template folder */
        $plugin_template_path = $this->GetConfig("path", "application_template_path");
        /** The options html is fetched */
        $template_parameters = array();
        /** The book data is prepared */
        for ($count = 0;$count < count($this->data['book_list']);$count++) 
        {
            /** The information for single book */
            $book_information = $this->data['book_list'][$count];
            /** Used to indicate if the current book should be selected in the dropdown */
            $selected = ( $book_information['book'] ==  $hadith_book) ? 'SELECTED' : '';
            /** The shortened dropdown text */
            $dropdown_text = $book_information['book'];
            /** The book name is shortened */
            $dropdown_text = (strlen($dropdown_text) > 55) ? substr($dropdown_text, 0, 55)  . "..." : $dropdown_text;
            /** The title information is added to template parameters */
            $template_parameters[] = array(
                "text" => $dropdown_text,
                "value" => $book_information['book'],                
                "selected" => $selected
            );
        }
        /** The Hadith Title dropdown options are rendered using template parameters */
        $options_html = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "option.html", $template_parameters);
        /** The Ruku dropdown templates parameters */
        $template_parameters = array(
            "name" => "ic_hadith_book",
            "id" => "ic-hadith-book",
            "options" => $options_html,
            "title" => $hadith_book
        );
        /** The Hadith Book dropdown template is rendered using the template parameters */
        $book_dropdown_html = $this->GetComponent("template_helper")->RenderTemplateFile($plugin_template_path . DIRECTORY_SEPARATOR . "select.html", $template_parameters);
        
        return $book_dropdown_html;
    }
}

