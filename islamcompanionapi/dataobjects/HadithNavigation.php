<?php

namespace IslamCompanionApi\DataObjects;
use \IslamCompanionApi\DataObjects\Hadith as Hadith;

/**
 * This class implements the HadithNavigation class
 *
 * An object of this class contains functions for handling different Hadith navigation actions
 * For example a function that returns the sura, ruku and division number when a sura is selected
 *
 * @category   IslamCompanionApi
 * @package    DataObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class HadithNavigation extends \Framework\Object\DataObjectAbstraction
{
    /**
     * Used to determine the hadith book and hadith title when the next or previous hadith title is selected
     *
     * It fetches the book and hadith title when the next or previous hadith title is selected
     *
     * @param string $hadith_source the hadith sura     
     * @param string $hadith_language the hadith language     
     * @param string $hadith_book the hadith book
     * @param string $hadith_title the hadith title
     * @param string $action [next~previous~hadith_book_box~hadith_title_box~hadith_source_box] the action
     *
     * @return array $hadith_meta_data an array with 2 keys:
     * hadith_book => the next/previous hadith book
     * hadith_title => the next/previous hadith title     
     */
    public function NextPreviousSelection($hadith_source, $hadith_language, $hadith_book, $hadith_title, $action) 
    {    
        /** The next/prev hadith book and title are fetched */
        $hadith_meta_data = $this->GetNextPrevBookAndTitle($hadith_source, $hadith_language, $hadith_book, $hadith_title, $action);

        return $hadith_meta_data;
    }
    
    /**
     * Used to get the next/prev hadith book title
     *
     * It fetches the list of all books and titles in the given book
     * It then returns the next/prev hadith book title
     *
     * @param string $hadith_source the hadith sura
     * @param string $hadith_language the hadith language
     * @param string $hadith_book the hadith book     
     * @param string $hadith_book_title the hadith title     
     * @param string $action [next~previous~hadith_book_box~hadith_title_box] the action performed by user
     *
     * @return array $hadith_meta_data the required hadith meta data
     *    hadith_book => the next/previous hadith book
     *    hadith_title => the next/previous hadith title     
     */
    public function GetNextPrevBookAndTitle($hadith_source, $hadith_language, $hadith_book, $hadith_book_title, $action) 
    {
        /** The required hadith meta data */
        $hadith_meta_data = array();
        /** The configuration object is fetched */
	$parameters['configuration']         = $this->GetConfigurationObject();
        /** The Hadith object is created */
        $hadith = new Hadith($parameters);     
        /** The Hadith books are fetched */
        $hadith_books = $hadith->GetBooks($hadith_source, $hadith_language);
        /** The Hadith book titles are fetched */
        $hadith_book_titles = $hadith->GetBookTitles($hadith_source, $hadith_language, $hadith_book);
        /** The current book index */
        $current_book_index = -1;
        /** The current book title index */
        $current_book_title_index = -1;
        /** Each book is checked */
        for ($count = 0; $count < count($hadith_books); $count++)
        {
            /** The hadith book */
            $book = $hadith_books[$count]['book'];
            /** If the hadith book matches the given book */
            if ($book == $hadith_book) $current_book_index = $count;
        }
        
        /** Each book title is checked */
        for ($count = 0; $count < count($hadith_book_titles); $count++)
        {
            /** The hadith book title */
            $book_title = $hadith_book_titles[$count]['title'];
            /** If the hadith book matches the given book */
            if ($book_title == $hadith_book_title || ($count == 0 && $hadith_book_title == "")) $current_book_title_index = $count;
        }
        
        /** If the action is next */
        if ($action == "next") {
            /** The hadith book title index is increased */
            $current_book_title_index++;
            /** if the next book title index is outside the range, then book title index is set to 0 */
            if ($current_book_title_index >= count($hadith_book_titles)) {
                /** The current book title index is set to 0 */
                $current_book_title_index = 0;
                /** The hadith book index is increased */
                $current_book_index++;
                /** if the next book index is outside the range, then book index is set to 0 */
                if ($current_book_index >= count($hadith_books)) {
                    /** The current book index is set to 0 */
                    $current_book_index = 0;                    
                }
            }                        
            /** The hadith book is updated */
            $hadith_book = $hadith_books[$current_book_index]['book'];
            /** The Hadith book titles are fetched for the next book */
            $hadith_book_titles = $hadith->GetBookTitles($hadith_source, $hadith_language, $hadith_book);            
        }
        /** If the action is previous */
        else if ($action == "previous") {
            /** The hadith book title index is decreased */
            $current_book_title_index--;
            /** If the prev book title index is outside the range, then book title index is set to the last book title index of the previous book */
            if ($current_book_title_index < 0) {                
                /** The hadith book index is decreased */
                $current_book_index--;
                /** If the prev book index is outside the range, then book index is set to the last book index */
                if ($current_book_index < 0) {
                    /** The current book index is set to the last book index */
                    $current_book_index = (count($hadith_books) -1);                                 
                }
                /** The hadith book is updated */
                $hadith_book = $hadith_books[$current_book_index]['book'];           
                /** The Hadith book titles are fetched for the prev book */
                $hadith_book_titles = $hadith->GetBookTitles($hadith_source, $hadith_language, $hadith_book);       
                /** The current book title index is set to the last book title index */
                $current_book_title_index = (count($hadith_book_titles) -1);
            }                        
        }
        /** If the action is hadith_book_box */
        else if ($action == "hadith_book_box") {
            /** The current book title index is set to 0 */
            $current_book_title_index = 0;           
        }
        /** If the action is hadith_source_box */
        else if ($action == "hadith_source_box") {
            /** The current book title index is set to 0 */
            $current_book_title_index = 0;
            /** The current book index is set to 0 */
            $current_book_index = 0;                                  
        }
        /** If the action is hadith_source_box */
        if ($action == "hadith_source_box") {
            /** The current book title index is set to 0 */
            $current_book_title_index = 0;
            /** The current book index is set to 0 */
            $current_book_index = 0;                                  
        }
        /** If the action is not current */
        if ($action != "current") {
            /** The hadith book title */
            $hadith_book_title = $hadith_book_titles[$current_book_title_index]['title'];
            /** The hadith book */
            $hadith_book = $hadith_books[$current_book_index]['book'];
        }
        /** The hadith meta data */
        $hadith_meta_data = array(
            "hadith_book" => $hadith_book,
            "hadith_title" => $hadith_book_title,
        );
        
        return $hadith_meta_data;
    }
}

