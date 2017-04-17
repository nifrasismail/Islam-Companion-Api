<?php

namespace IslamCompanionApi\DataObjects\Helpers;

/**
 * This class implements the NavigatorTools Trait
 *
 * An object of this class allows access to general NavigatorTools tools
 * It provides functions for fetching the hmtl for navigator tools
 *
 * @category   IslamCompanionApi
 * @package    DataObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait NavigatorTools
{   
    /**
     * Used to return an image that allows copying the Ayat or Hadith
     *
     * It returns an image that allows copying the Ayat or Hadith
     *
     * @param int $text_id the ayat id or hadith id
     * @param string $text the ayat or hadith text
     * @param string $type [Hadith~Ayat] the type of text. i.e Hadith or Ayat
     *
     * @return string $copy_image_html the html for the copy image
     */
    public function GetCopyImage($text_id, $text, $type)
    {  
        /** The text is encoded */
        $text                            = $this->GetComponent("encryption")->EncodeData(urlencode($text));
        /** The application image folder */
        $application_template_folder_url = $this->GetConfig("path", "application_template_url") . "/images/";
        /** The url to the template folder */
        $application_template_url        = $this->GetConfig("path", "application_template_url");
        /** The shortcode image url */
        $verse_clipboard_image           = $application_template_url . "/images/copy.png";
        /** The copy image template parameters */
        $template_parameters             = array(
                                            "id" => "copy-ayat-" . $text_id,
                                            "src" => $verse_clipboard_image,
                                            "alt" => "Copy " . ($type),
                                            "title" => "Copy ". ($type),
                                            "css_class" => "ic-cursor",
                                            "onclick" => 'navigator_object.CopyText("' . $text . '", "' . $type . '")'
                                          );
        
        $copy_image_html = $this->GetComponent("template")->Render("image", $template_parameters);
        
        return $copy_image_html;
    }
    
    /**
     * Used to return an image that allows scrolling to top of the navigator
     *
     * It returns an image that allows scrolling to the top of the Holy Quran or Hadith navigator
     *
     * @param int $text_id the ayat id or hadith id
     * @param string $type [Hadith~Holy Quran] the type of text. i.e Hadith or Ayat
     *     
     * @return string $scroll_top_image_html the html for the scroll top image
     */
    public function GetScrollTopImage($text_id, $type)
    {
        /** The application image folder */
        $application_template_folder_url = $this->GetConfig("path", "application_template_url") . "/images/";
        /** The scroll top image */
        $verse_scroll_image = $application_template_folder_url . "scroll-top.png";
        /** The scroll top image template parameters */
        $template_parameters = array(
            "id" => "scroll-top-ayat-" . $text_id,
            "src" => $verse_scroll_image,
            "alt" => "Scroll to top",
            "title" => "Scroll to top",
            "css_class" => "ic-cursor",
            "onclick" => 'navigator_object.ScrollTop("ic-'. $type .'-navigator-text")'
        );
        $scroll_top_image_html = $this->GetComponent("template")->Render("image", $template_parameters);
        
        return $scroll_top_image_html;
    }
    
    /**
     * Used to add span tags around each word in the given text
     *
     * It divides the given text in to an array
     * It adds span tags to each word in the given text
     *
     * @param string $text the text that needs to be updated
     * @param string $exclude_text the text that should not be wrapped
     * @param string $text_id the id of the text. e.g the ayat id
     * @param string $type [hadith~holy-quran] the type of text. i.e Hadith or Ayat     
     * @param string $dictionary_url the url of the dictionary to use
     *
     * @return string $updated_text the updated text. each word in the text is wrapped inside span tag
     */
    public function WrapWordsInText($text, $exclude_text, $text_id, $type, $dictionary_url) 
    {
        /** The updated text */
        $updated_text = "";
        /** The given text is split on space character */
        $text_arr = explode(" ", $text);
        /** Each word in the text is wrapped */
        for ($count = 0;$count < count($text_arr);$count++) 
        {     
            /** The word to be wrapped */
            $word = $text_arr[$count];
            /** The word is stripped of html and '.' */
            $word = trim(strip_tags($word), ".");
            /** The text to exclude is not wrapped */
            if ($exclude_text != "" && strpos($text_arr[$count], $exclude_text) !== false || strlen($word) <=3) $updated_text.= $text_arr[$count] . " ";
            else 
            {
                /** The span tag id */
                $span_tag_id = $text_id . ($count + 1);
                /** The word is stripped of html */
                $word = ($type == "hadith") ? $word : $text_arr[$count];
                /** The word is wrapped inside span tag */
                $updated_word = "<span id='" . $span_tag_id . "' class='ic-" . $type . "-widget-word'>" . $word . "</span> ";
                /** The updated word is added to the updated text */
                $updated_text .= $updated_word;
            }
        }
        /** The trailing and leading spaces are trimmed */
        $updated_text = trim($updated_text);
        
        return $updated_text;
    }
    /**
     * Used to highlight the given text
     *
     * It encloses the given text inside span tags
     *
     * @param string $text the text to highlight
     *
     * @return string $hightlighted_text_html the hightlighted text
     */
    public function HighlightText($text) 
    {
        /** The highlighted search keyword template parameters */
        $template_parameters = array(
            "content" => $text,
            "css_class" => "highlight-search-keyword"
        );
        /** The span template is rendered using the template parameters */
        $hightlighted_text_html = $this->GetComponent("template")->Render("span", $template_parameters);
        
        return $hightlighted_text_html;
    }
}

