<?php

namespace IslamCompanionApi\UiObjects\Helpers;

/**
 * This class implements the AyatFormatter Trait
 *
 * An object of this class allows formatting ayats
 * It provides functions for formattings Holy Quran ayat text
 *
 * @category   IslamCompanionApi
 * @package    UiObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait AyatFormatter
{    
    /**
     * Used to format the given ayats
     *
     * It formats the ayats in the given format
     *
     * @param array $ayat_list the list of ayats to format
     * @param string $format [double column~paragraph~search results~list]
     * @param array $parameters the parameters used to format the given text
     * @param array $options the options for formatting the ayats
     *
     * @return string $formatted_ayat_html the formatted ayat html
     */
    public function FormatAyas($ayat_list, $format, $parameters, $options) 
    {
        /** The formatted ayat text */
        $formatted_ayat_html         = "";
        /** If the required format is double column */
        if ($format == 'double column') {
            /** The ayat list is formatted */
            $formatted_ayat_html = $this->FormatAsDoubleColumn($ayat_list, $options, $parameters);
        }
        /** If the required format is paragraph */
        else if ($format == 'paragraph') {
            /** The ayat list is formatted */
            $formatted_ayat_html = $this->FormatAsParagraph($ayat_list, $options, $parameters);
        }
        /** If the required format is search results */
        else if ($format == 'search results') {
            /** If the layout is navigator */
            if ($parameters['parameters']['layout'] == 'navigator') {
                /** The ayat list is formatted */
                $formatted_ayat_html = $this->FormatAsNavigatorSearchResults($ayat_list, $options, $parameters);
            }
            /** If the layout is plain */
            else if ($parameters['parameters']['layout'] == 'plain') {
                /** The ayat list is formatted */
                $formatted_ayat_html = $this->FormatAsPlainSearchResults($ayat_list, $options, $parameters);
            }
        }
        /** If the required format is plain text */
        else if ($format == 'plain text') {
            /** The ayat list is formatted */
            $formatted_ayat_html = $this->FormatAsPlainText($ayat_list, $options, $parameters);
        }
        /** If the required format is list */
        else if ($format == 'list') {
            /** The ayat list is formatted */
            $formatted_ayat_html = $this->FormatAsList($ayat_list, $options, $parameters);
        }
        
        return $formatted_ayat_html;
    }
    /**
     * Used to post process the given ayat list
     *
     * It applies a transformation to the given ayat list after it has been formatted
     *
     * @param array $ayat_list the ayat list to transform
     * @param string $ayat_html the formatted ayat text
     * @param string $transformation [none~random~slideshow] the transformation to be applied to the given ayat list
     *     
     * @return string $transformed_ayat_html the transformed and formatted ayat html
     */
    private function PostProcessAyatList($ayat_list, $ayat_html, $transformation)
    {    
        /** If the slideshow transformation is required */
        if ($transformation == 'slideshow') {
            /** The formatted ayat list */
            $formatted_ayat_list      = array();
            /** The ayat list is formatted */
            for ($count = 0; $count < count($ayat_list); $count++) {
                $formatted_ayat_list[]= '"' . $ayat_list[$count] . '"';
            }
            /** The formatted verse text as required by the slideshow template */
            $slideshow_text           = implode(",", $formatted_ayat_list);
            /** The slideshow template is rendered using the given parameters */
            $slideshow_text           = $this->GetComponent("template")->Render("slideshow", array("container_id" => "holy-quran-text", "verse_text" => $slideshow_text));
            /** The slideshow text is appended to the div html */
            $transformed_ayat_html    = $ayat_html . $slideshow_text;
        }
        /** If no transformation is required then the formatted ayat html is returned */
        else {
            $transformed_ayat_html    = $ayat_html;
        }
        
        return $transformed_ayat_html;
    }
    /**
     * Used to pre process the given ayat list
     *
     * It applies a transformation to the given ayat list before it has been formatted
     *
     * @param array $ayat_list the list of ayas to transform
     * @param string $transformation [none~random~slideshow] the transformation to be applied to the given ayat list
     *     
     * @return array $transformed_ayat_list the transformed ayat list
     */
    private function PreProcessAyatList($ayat_list, $transformation)
    {
        /** If a random ayat needs to be returned */
        if ($transformation == 'random') {
            /** The week of the year */
            $week                           = date("W");
            /** The ayat data index */
            $index                          = ($week > count($ayat_data)) ? ($week % count($ayat_data)) : $week;
            /** The ayat data for the week is extracted */
            $transformed_ayat_list          = array($ayat_data[$index]);
        }
        /** If the ayas need to be transformed to a slideshow */
        else if ($transformation == 'slideshow') {
            /** The ayat data for the week is extracted */
            $transformed_ayat_list          = array($ayat_list[0]);
        }
        /** If no transformation is given */
        else {
            $transformed_ayat_list          = $ayat_list;
        }
        
        return $transformed_ayat_list;
    }
    /**
     * Used to format the given verse text as plain search results
     *
     * It appends meta data to each search result. The search results are json encoded and then returned
     *
     * @param string $ayat_list the holy quran search results text
     * @param array $options the options for formatting the given verses
     * @param array $parameters the parameters used to format the given text    
     *     
     * @return string $search_results the search results in json format
     */
    private function FormatAsPlainSearchResults($ayat_list, $options, $parameters)
    {
        /** The holy quran search results */
        $search_results                           = array();
        /** The template parameters are built for the table row html template */
        for ($count = 0; $count < count($ayat_list); $count++) {
            /** The sura information for the first ayat */
            $sura_data                           = $this->GetComponent("suras")->GetSuraData($ayat_list[$count]['sura']);
            /** The search results are added to the results array */
            $search_results                      []= $ayat_list[$count]['translated_text'] . " (" .$sura_data['tname']. " " .$sura_data['id']. ":" .$ayat_list[$count]['sura_ayat_id']. ")";
        }
        /** The search results are json encoded */
        $search_results                          = json_encode($search_results);
        
        return $search_results;
    }
    /**
     * Used to format the given verse text as plain search results
     *
     * It renders the given verse text as an ordered list
     *
     * @param array $parameters the options for formatting the given verse
     *     
     * @return string $formatted_text_html the formatted text
     */
    private function GetSearchResultsPagination($parameters)
    {
        /** The parameters used to render the pagination select box */
        $template_parameters = array(
          "selectbox_name" => "ic-holy-quran-search-pages", 
          "selectbox_id" => "ic-holy-quran-search-pages",
          "selectbox_selected_value" => $parameters['page_number'],
          "selectbox_onchange" => "holy_quran_navigator_object.SearchVerseData(document.getElementById('ic-holy-quran-search-pages').value)",
          "selectbox_options" => range(1, $parameters['total_number_of_pages'])
        );
        /** The selectbox is rendered */
        $total_pages_html = $this->GetComponent("template")->Render("selectbox", $template_parameters);
        /** The template parameters for the search results pagination */
        $template_parameters = array(
            "total_pages" => $parameters['total_number_of_pages'],            
            "total_pages_html" => $total_pages_html,
            "total_results" => $parameters['total_results'],
            "search_results_center_heading_class" => "holy-quran-search-results-center-heading"
        );
        /** The search results pagination */
        $search_results_pagination = $this->GetComponent("template")->Render("search_results_pagination", $template_parameters);
        
        return $search_results_pagination;
    }
    /**
     * Used to format the given verse text as search results for the navigator
     *
     * It renders the given verse text within a navigator html template
     * It appends copy image and scroll to top image to each search result
     *
     * @param string $ayat_list the text to format
     * @param array $options the options for formatting the given verse
     * @param array $parameters the parameters used to format the given text    
     *     
     * @return string $formatted_text_html the formatted text
     */
    private function FormatAsNavigatorSearchResults($ayat_list, $options, $parameters)
    {
        /** The scroll top id and formatted ayat html */
        $scroll_top_id = $formatted_text_html                      = "";
        /** The template parameters for verse_text template */
        $template_parameters  = array(
            "language_code" => $parameters['language_code'],
            "text" => "",
            "list_item_id" => "",
            "list_class" => $parameters['css_class'],
            "number_list_class" => $parameters['css_attributes']
        );
        /** The css class for extra search results */
        $search_results_extra_css                = ($parameters['rtl']) ? "right-align-text" : "left-align-text";
        /** The template parameters are built for the table row html template */
        for ($count = 0; $count < count($ayat_list); $count++) {
            /** The list item id is updated */
            $template_parameters['list_item_id'] = $ayat_list[$count]['sura'] . "-" . $ayat_list[$count]['sura_ayat_id'];
            /** The ayat id */
            $ayat_id                             = (isset($ayat_list[$count]['ayat_id'])) ? $ayat_list[$count]['ayat_id'] : $ayat_list[$count]['id'];
            /** The list item start value is updated */
            $template_parameters['start']        = $ayat_list[$count]['sura_ayat_id'];
            /** The meta data for the ayat */
            $meta_data                           = $this->GetComponent("ayat")->GetAyatMetaData($ayat_id);
            /** The sura information for the first ayat */
            $sura_data                           = $this->GetComponent("suras")->GetSuraData($ayat_list[$count]['sura']);
            /** The sura name is set */
            $meta_data[0]['sura_name']           = $sura_data['tname'];
            /** The parameters used to format the ayat */
            $formatting_parameters               = array("meta_data" => $meta_data[0], "is_translation" => true, "template_name" => "list_item", "template_parameters" => $template_parameters, "language" => $parameters['language'], "search_text" => $parameters['search_text']);
            /** The translated text is formatted as a list item */
            $formatted_text_html                 .= $this->FormatAyat($ayat_list[$count]['translated_text'], $options, $formatting_parameters);
            /** The html id of the top element. It is used to scroll to the top of the navigator */
            $scroll_top_id                        = ($count > 0) ? $scroll_top_id : "ic-" . $ayat_list[$count]['sura_ayat_id'];
            /** The search result extra text is fetched */
            $search_results_extra_text            = $this->GetSearchResultsExtraText($meta_data[0], $parameters['narrator'], $parameters['language'], $ayat_list[$count]['translated_text'], $scroll_top_id, $search_results_extra_css, $options);
            /** The search results extra text is appended to the verse text */
            $formatted_text_html                  .= $search_results_extra_text;
        }
        /** The template parameters are set */
        $template_parameters                     = array("content" => $formatted_text_html, "list_class" => $parameters['css_class']);
        /** The start number of the verses */
        $start_number                            = (($parameters['page_number'] - 1) * $parameters['verses_per_page']) + 1;
        /** The start ayat number is set */
        $template_parameters['start']            = ($start_number > 0) ? $start_number : '1';
        /** The css list class is set */
        $template_parameters['list_class']       = $template_parameters['list_class'] . " " . $parameters['css_attributes'] . " search-results";
        /** The verse text */
        $formatted_text_html = $this->GetComponent("template")->Render("ordered_list", $template_parameters);
        /** The search results pagination is fetched */
        $search_results_pagination               = $this->GetSearchResultsPagination($parameters);
        /** The pagination is prepended to the verse text html */
        $formatted_text_html = $search_results_pagination . $formatted_text_html;
        
        return $formatted_text_html;
    }
    /**
     * Used to format the given verse text as an html unordered list
     *
     * It renders the given verse text within a html template
     *
     * @param string $ayat_list the text to format
     * @param array $options the options for formatting the given verse
     * @param array $parameters the parameters used to format the given text    
     *     
     * @return string $formatted_text_html the formatted text
     */
    private function FormatAsList($ayat_list, $options, $parameters)
    {      
        /** The formatted ayat html */
        $formatted_text_html                              = "";
        /** The template parameters are built for the table row html template */
        for ($count = 0; $count < count($ayat_list); $count++) {           
            /** The sura name is set */
            $meta_data['sura']                            = $ayat_list[$count]['sura_data']['tname'];
            /** The sura ayat id is set */
            $meta_data['sura_ayat_id']                    = $ayat_list[$count]['sura_ayat_id'];
            /** The meta data for the ayat text */
            $meta_data['meta_data']                       = $ayat_list[$count]['sura_data']['tname'] . " (" .$ayat_list[$count]['sura_data']['sindex']. ":" . $ayat_list[$count]['sura_ayat_id'] . ")";
            /** The trailing '.' and ',' are removed since they are not needed for list item */
            $ayat_list[$count]['translated_text']         = $ayat_list[$count]['translated_text'];
            /** The parameters used to format the ayat */
            $formatting_parameters                        = array("meta_data" => $meta_data, "is_translation" => true, "template_name" => "list_item", "template_parameters" => array(), "language" => $parameters['language']);
            /** The translated text is formatted as a list item */
            $formatted_text_html                         .= $this->FormatAyat($ayat_list[$count]['translated_text'], $options, $formatting_parameters);            
        }
        /** The template parameters are set */
        $template_parameters                              = array("content" => $formatted_text_html, "list_class" => $parameters['css_classes']);
        /** The verse text */
        $formatted_text_html                              = $this->GetComponent("template")->Render("unordered_list", $template_parameters);
        
        return $formatted_text_html;
    }
    /**
     * Used to format the given verse text optionally with verse tools
     *
     * It renders the given verse text within the given html template
     * Depending on the option given by the user, it appends copy image, shortcode image and scroll to top image
     *
     * @param string $text the text to format
     * @param array $options the options for formatting the given verse
     * @param array $parameters the parameters used to format the given text   
     *
     * @return string $formatted_text_html the formatted text
     */
    private function FormatAyat($text, $options, $parameters) 
    {
        /** The current language */
        $language                                  = ($parameters['is_translation']) ? $parameters['language'] : "arabic";
        /** The text id */
        $text_id                                   = ($parameters['is_translation']) ? "holy-quran-translation-" : "holy-quran-arabic-";
        /** The list item id is set */
        $list_item_id                              = $text_id . $parameters['meta_data']['sura'] . "-" . $parameters['meta_data']['sura_ayat_id'];
        /** The dictionary url and language rtl information are fetched for the current language */
        $dictionary_information                    = $this->GetComponent("authors")->GetDictionaryInformation($language);
        /** 
         * The Arabic verse text is converted to Normalization Form C (NFC) - Canonical Decomposition followed by Canonical Composition
         * This is done to prevent errors during html validation
         * The text is only normalized if the php intl extension is loaded
         */
        if (extension_loaded("intl")) $text        = \Normalizer::normalize($text, \Normalizer::FORM_C);           
        /** If the sura and ayat should be appended to the text */
        if (is_array($options) && in_array("sura and ayat meta", $options['tools_list'])) {
            /** The template for rendering the ayat meta data */
            $template_parameters                   = array("content" => $parameters['meta_data']['meta_data'], "css_class" => "ayat-meta-data");
            /** The ayat meta data is rendered using the given parameters */
            $ayat_meta                             = $this->GetComponent("template")->Render("span", $template_parameters);
            /** The ayat meta data is appended to the arabic text */
            $text                                 .= $ayat_meta;
        }
        /** If a certain word needs to be highlighted */
        if (is_array($options) && in_array("highlight text", $options['tools_list'])) {
            /** The highlighted search keyword template parameters */
            $template_parameters                   = array("content" => $parameters['search_text'], "css_class" => "highlight-search-keyword");
            /** The span template is rendered using the template parameters */
            $search_text_html = $this->GetComponent("template")->Render("span", $template_parameters);
            /** The search keyword is replaced by the hightlighted text */
            $text                                  = preg_replace("/([\s,\.\"'])" . $parameters['search_text'] . "([\s,\.\"'])/iU", "$1" . $search_text_html . "$2", $text);
        }
        /** The short code image is fetched if it is given as an option */
        if (is_array($options) && in_array('shortcode', $options['tools_list'])) {
            $parameters['template_parameters']['shortcode_image']= $this->GetAyatShortcodeImage($parameters['meta_data']['sura_ayat_id'], $parameters['meta_data']['sura'], $text, $parameters['narrator'], $parameters['language']);
        }
        else {
            $parameters['template_parameters']['shortcode_image']= '';
        }
        /** The copy image is fetched if it is given as an option */
        if (is_array($options) && in_array('copy', $options['tools_list'])) {
            /** The ayat meta data is appended to the ayat text */
            $copy_text                                            = $text . " (" . $parameters['meta_data']['sura_name'] . " " . $parameters['meta_data']['sura'] . "-" . $parameters['meta_data']['sura_ayat_id'];
            
            $parameters['template_parameters']['copy_image']      = $this->GetCopyImage($parameters['meta_data']['sura_ayat_id'], $copy_text, "Holy Quran");
        }
        else {
            $parameters['template_parameters']['copy_image']      = "";
        }
        
        /** The scroll top image is fetched if it is given as an option */
        if (is_array($options) && in_array('scroll to top', $options['tools_list'])) {
            $parameters['template_parameters']['scroll_top_image'] = $this->GetScrollTopImage("holy-quran-" . $parameters['meta_data']['sura_ayat_id'], "holy-quran");
        }
        else {
            $parameters['template_parameters']['scroll_top_image'] = "";
        }
        /** The words in the ayat text are wrapped */
        if (is_array($options) && in_array('dictionary links', $options['tools_list'])) {
            /** The text to exclude. It is set to the search text if it is set */
            $exclude_text                           = (isset($parameters['search_text'])) ? $parameters['search_text'] : "";
            $text                                   = $this->WrapWordsInText($text, $exclude_text, $list_item_id, "holy-quran", $dictionary_information['dictionary_url']);
        }
        /** The verse id */
        $parameters['template_parameters']['id']    = $list_item_id;
        /** The verse text */
        $parameters['template_parameters']['text']  = $text;
        /** The translated verse text */
        $formatted_text_html                        = $this->GetComponent("template")->Render($parameters['template_name'], $parameters['template_parameters']);
        
        return $formatted_text_html;
    }
    /**
     * Used to format the given ayas as plain text
     *
     * It formats the ayat as plain text
     *
     * @param array $ayat_list the list of ayats to format
     * @param array $options the options for formatting the ayats
     * @param array $parameters the parameters used to format the given text     
     *
     * @return string $ayat_text the ayat text
     */
    private function FormatAsPlainText($ayat_list, $options, $parameters) 
    {
        /** The translated text is initialized */
        $translated_text = array();
        /** The template parameters are built for the table row html template */
        for ($count = 0; $count < count($ayat_list); $count++) {
            /** The translated text */
            $ayat_text                = $ayat_list[$count]['translated_text'];
            /** The meta data for the ayat text */
            $ayat_meta_data           = $ayat_list[$count]['sura_data']['tname'] . " (" .$ayat_list[$count]['sura_data']['sindex']. ":" . $ayat_list[$count]['sura_ayat_id'] . ")";
            /** The sura meta data is appended to the translated text */
            $ayat_text                = $ayat_text . " - " . $ayat_meta_data;
            /** The translated text is formatted as a list item */
            $translated_text[]        = $ayat_text;
        }
        /** The ayat list is pre processed according to the given transformation */
        $processed_ayat_list          = $this->PreProcessAyatList($translated_text, $parameters['transformation']);
        /** The translated text */
        $ayat_text                    = implode(" . ", $processed_ayat_list);        
        /** The html template is rendered using the given parameters */
        $ayat_text                    = $this->GetComponent("template")->Render("div", array("content" => $ayat_text, "id" => "holy-quran-text", "css_class" => $parameters['css_classes']));
        /** The ayat list is post processed according to the given transformation */
        $ayat_text                    = $this->PostProcessAyatList($translated_text, $ayat_text , $parameters['transformation']);
        
        return $ayat_text;
    }    
    /**
     * Used to format the given ayas as a paragraph
     *
     * It formats the ayats as a paragraph list
     *
     * @param array $ayat_list the list of ayats to format
     * @param array $options the options for formatting the ayats
     * @param array $parameters the parameters used to format the given text     
     *
     * @return string $formatted_ayat_html the formatted ayat html
     */
    private function FormatAsParagraph($ayat_list, $options, $parameters) 
    {
        /** The translated and arabic text is initialized */
        $translated_text = $arabic_text = array();
        /** The start ayat id */
        $start_ayat_id                      = $ayat_list[0]['sura_ayat_id'];
        /** The end ayat id */
        $end_ayat_id                        = $ayat_list[count($ayat_list) -1]['sura_ayat_id'];        
        /** The template parameters are built for the table row html template */
        for ($count = 0; $count < count($ayat_list); $count++) {
            /** The translated text is formatted as a list item */
            $translated_text[]              = $ayat_list[$count]['translated_text'];
            /** The arabic text is formatted as a list item */
            $arabic_text[]                  = $ayat_list[$count]['arabic_text'];
        }
        /** The ayat meta data */
        $ayat_meta_data                     = $ayat_list[0];
        /** The sura information for the first ayat */
        $ayat_data['sura_data']             = $this->GetComponent("suras")->GetSuraData($ayat_list[0]['sura']);
        /** The sura name is appended to the meta data */
        $ayat_meta_data['meta_data']        = $ayat_data['sura_data']['tname'] . ":" . $start_ayat_id . "-" . $end_ayat_id;
        /** If the first verse translation contains a '.', then the translated text is joined with ''. Otherwise it is joined with ' . ' */
        $join_string                        = (strpos($translated_text[0], ".") ===false) ? " . " : "";
        /** The translated text */
        $translated_text                    = implode($join_string, $translated_text);
        /** The arabic text */
        $arabic_text                        = implode(" . ", $arabic_text);

        /** The parameters used to format the ayat */
        $format_parameters                  = array("meta_data" => $ayat_meta_data, "is_translation" => false, "template_name" => "paragraph", "template_parameters" => array("css_class" => "ic-rtl-text arabic-indic"), "language" => "Arabic");
        /** The arabic text is formatted */
        $formatted_ayat_html                = $this->FormatAyat($arabic_text, $options, $format_parameters);
        /** The parameters used to format the ayat */
        $format_parameters                  = array("meta_data" => $ayat_meta_data, "is_translation" => true, "template_name" => "paragraph", "template_parameters" => array("css_class" => $parameters['css_class'] . " " . $parameters['css_attributes']), "language" => $parameters['language']);
        /** The translated text is formatted */
        $formatted_ayat_html                .= $this->FormatAyat($translated_text, $options, $format_parameters);
        /** The html template is rendered using the given parameters */
        $formatted_ayat_html                = $this->GetComponent("template")->Render("div", array("content" => $formatted_ayat_html, "id" => "holy-quran-text", "css_class" => 'verse-table'));
        
        return $formatted_ayat_html;
    }
    /**
     * Used to format the given ayats as a double column list
     *
     * It formats the ayats as a double column list
     *
     * @param array $ayat_list the list of ayats to format
     * @param array $options the options for formatting the ayats
     * @param array $parameters the parameters used to format the given text     
     *
     * @return string $formatted_ayat_html the formatted ayat html
     */
    private function FormatAsDoubleColumn($ayat_list, $options, $parameters) 
    {
        /** The template parameters for table data template. It is used to render the translated text and arabic text */
        $template_parameters            = array("table_css_class" => "verse-table", "table_rows" => array());
        /** The arabic language parameters */
        $arabic_parameters              = $parameters;
        /** The language for the parameters is set to Arabic */
        $arabic_parameters['language']  = 'Arabic';
        /** The css class is set to 'ic-arabic-text' */
        $arabic_parameters['css_class'] = 'ic-arabic-text';
        /** The template parameters are built for the table row html template */
        for ($count = 0; $count < count($ayat_list); $count++) 
        {
            /** The ayat template parameters */
            $ayat_template_parameters1  = array(
						"language_code" => $this->data['language_code'],
						"list_item_id" => "translated-text-" . ($count+1),
						"start" => ($this->data['start_ayat'] + $count) ,
						"list_class" => $parameters['css_class'],
						"number_list_class" => $this->data['css_attributes']
					    );
            /** The ayat template parameters */
            $ayat_template_parameters2  = array("css_class" => $parameters['css_class'] . " " . $parameters['css_attributes']);         
            /** If the ayat list should only contain ayas in one sura and the current ayat is in different sura than first ayat then the loop ends */
            if (in_array("single sura", $options) && $ayat_list[$count]['sura'] != $ayat_list[0]['sura']) break;
            /** The sura information for the first ayat */
            $ayat_list[$count]['sura_data'] = $this->GetComponent("suras")->GetSuraData($ayat_list[$count]['sura']);
            /** The meta data for the ayat text */
            $ayat_list[$count]['meta_data'] = " - (" . $ayat_list[$count]['sura_data']['tname'] . " " . $ayat_list[$count]['sura_data']['sindex']. ":" . $ayat_list[$count]['sura_ayat_id'] . ")";
            /** The parameters used to format the ayat */
            $format_parameters          = array("meta_data" => $ayat_list[$count], "is_translation" => true, "template_name" => "translated_verse_text", "template_parameters" => $ayat_template_parameters1, "language" => $parameters['language'], "narrator" => $parameters['narrator']);
            /** The translated text is formatted as a list item */
            $translated_text_html       = $this->FormatAyat($ayat_list[$count]['translated_text'], $options, $format_parameters);
            /** The parameters used to format the ayat */
            $format_parameters          = array("meta_data" => $ayat_list[$count], "is_translation" => false, "template_name" => "arabic_verse_text", "template_parameters" => $ayat_template_parameters2, "language" => $parameters['language'], "narrator" => $parameters['narrator']);
            /** The arabic text is formatted as a list item */
            $arabic_text_html           = $this->FormatAyat($ayat_list[$count]['arabic_text'], $options, $format_parameters);
            /** The arabic verse text and translated verse text are added to table */
            $template_parameters['table_rows'][] = array(
                $translated_text_html,
                $arabic_text_html
            );
        }
        /** The html template is rendered using the given parameters */
        $formatted_ayat_html = $this->GetComponent("template")->Render("simple_html_table", $template_parameters);
        
        return $formatted_ayat_html;
    }   
}

