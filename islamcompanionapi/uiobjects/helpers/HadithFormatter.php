<?php

namespace IslamCompanionApi\UiObjects\Helpers;

/**
 * This class implements the HadithFormatter Trait
 *
 * An object of this class allows formatting ayats
 * It provides functions for formattings Holy Quran ayat text
 *
 * @category   IslamCompanionApi
 * @package    UiObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait HadithFormatter
{    
    /**
     * Used to format the given hadith text
     *
     * It formats the hadith in the given format
     *
     * @param array $hadith_list the list of hadith to format
     * @param string $format [paragraph~search results~list]
     * @param array $parameters the parameters used to format the given text
     * @param array $options the options for formatting the hadith
     *
     * @return string $formatted_hadith_html the formatted hadith html
     */
    public function FormatHadith($hadith_list, $format, $parameters, $options) 
    {
        /** If the required format is paragraph */
        if ($format == 'paragraph') {
            /** The hadith list is formatted */
            $formatted_hadith_html = $this->FormatAsParagraph($hadith_list, $options, $parameters);
        }
        /** If the required format is plain text */
        else if ($format == 'plain text') {
            /** The hadith list is formatted */
            $formatted_hadith_html = $this->FormatAsPlainText($hadith_list, $options, $parameters);
        }
        /** If the required format is search results */
        else if ($format == 'search results') {
            /** If the layout is navigator */
            if ($parameters['parameters']['layout'] == 'navigator') {
                /** The hadith list is formatted */
                $formatted_hadith_html = $this->FormatAsNavigatorSearchResults($hadith_list, $options, $parameters);
            }
            /** If the layout is plain */
            else if ($parameters['parameters']['layout'] == 'plain') {
                /** The ayat list is formatted */
                $formatted_hadith_html = $this->FormatAsPlainSearchResults($hadith_list, $options, $parameters);
            }
        }
        /** If the required format is list */
        else if ($format == 'list') {
            /** The hadith list is formatted */
            $formatted_hadith_html = $this->FormatAsList($hadith_list, $options, $parameters);
        }
        
        return $formatted_hadith_html;
    }
    /**
     * Used to format the given hadith text as plain text
     *
     * It renders the given hadith text as json encoded string
     *
     * @param string $hadith_list the text to format
     * @param array $options the options for formatting the given hadith
     * @param array $parameters the parameters used to format the given text    
     *     
     * @return string $hadith_text the hadith text formatted as json string is returned
     */
    private function FormatAsPlainText($hadith_list, $options, $parameters)
    {
        /** The formatted hadith text */
        $hadith_text                                      = array();
        /** The meta data is appended to each Hadith text */
        for ($count = 0; $count < count($hadith_list); $count++) {
            /** The hadith text */
            $hadith_text                                  []= $hadith_list[$count]['hadith_text'] . " " . $hadith_list[$count]['source'] . " - " . $hadith_list[$count]['book'] . " - " . $hadith_list[$count]['hadith_number'] . " (" . $hadith_list[$count]['title'] . ")";
        }
        /** The hadith text is json encoded */
        $hadith_text                                      = json_encode($hadith_text);
        
        return $hadith_text;
    }
    /**
     * Used to format the given hadith text as an html unordered list
     *
     * It renders the given hadith text within a html template
     *
     * @param string $hadith_list the text to format
     * @param array $options the options for formatting the given hadith
     * @param array $parameters the parameters used to format the given text    
     *     
     * @return string $formatted_text_html the formatted text
     */
    private function FormatAsList($hadith_list, $options, $parameters)
    {
        /** The formatted hadith html */
        $formatted_text_html                              = "";
        /** The template parameters are built for the table row html template */
        for ($count = 0; $count < count($hadith_list); $count++) {
            /** The parameters used to format the hadith */
            $formatting_parameters                        = array("meta_data" => $hadith_list[$count]['source'] . " - " . $hadith_list[$count]['book'] . " - " . $hadith_list[$count]['hadith_number'] . ":" . $hadith_list[$count]['id'] . " (" . $hadith_list[$count]['title'] . ")");
            /** The hadith text */
            $hadith_text                                  = $this->FormatText($hadith_list[$count]['hadith_text'], array("meta data"), $formatting_parameters);
            /** The template parameters are set */
            $template_parameters                          = array("text" => $hadith_text);
            /** The hadith text */
            $formatted_text_html                          .= $this->GetComponent("template")->Render("list_item", $template_parameters);
        }
        /** The template parameters are set */
        $template_parameters                              = array("content" => $formatted_text_html, "list_class" => $parameters['css_classes']);
        /** The verse text */
        $formatted_text_html                              = $this->GetComponent("template")->Render("unordered_list", $template_parameters);
        
        return $formatted_text_html;
    }
    /**
     * Used to format the given hadith text as plain search results
     *
     * It appends meta data to each search result. The search results are json encoded and then returned
     *
     * @param string $hadith_list the hadith search results text
     * @param array $options the options for formatting the given search results
     * @param array $parameters the parameters used to format the given search results    
     *     
     * @return string $search_results the search results in json format
     */
    private function FormatAsPlainSearchResults($hadith_list, $options, $parameters)
    {
        /** The hadith search results */
        $search_results                           = array();
        /** The template parameters are built for the table row html template */
        for ($count = 0; $count < count($hadith_list); $count++) {
            /** The search results are added to the results array */
            $search_results                      []= $hadith_list[$count]['hadith_text'] . " " . $hadith_list[$count]['source']. " - " . $hadith_list[$count]['book'] . ":" . $hadith_list[$count]['book_number'] . ":" .$hadith_list[$count]['id']. ")";
        }
        /** The search results are json encoded */
        $search_results                          = json_encode($search_results);
        
        return $search_results;
    }
    /**
     * Used to get the pagination html for the hadith search results
     *
     * It generates a select box with page numbers for the hadith search results
     *
     * @param int $page_number the current page number of the Hadith search results
     * @param int $total_number_of_pages the total number of pages of the Hadith search results
     * @param int $total_results the total number of search results
     *     
     * @return string $formatted_text_html the formatted text
     */
    private function GetSearchResultsPagination($page_number, $total_number_of_pages, $total_results)
    {
        /** The parameters used to render the pagination select box */
        $template_parameters = array(
          "selectbox_name" => "ic-hadith-search-pages", 
          "selectbox_id" => "ic-hadith-search-pages",
          "selectbox_selected_value" => $page_number,
          "selectbox_onchange" => "hadith_navigator_object.SearchHadithData(document.getElementById('ic-hadith-search-pages').value)",
          "selectbox_options" => range(1, $total_number_of_pages)
        );
        /** The selectbox is rendered */
        $total_pages_html = $this->GetComponent("template")->Render("selectbox", $template_parameters);
        /** The template parameters for the search results pagination */
        $template_parameters = array(
            "total_pages" => $total_number_of_pages,            
            "total_pages_html" => $total_pages_html,
            "total_results" => $total_results,
            "search_results_center_heading_class" => "holy-quran-search-results-center-heading"
        );
        /** The search results pagination */
        $search_results_pagination = $this->GetComponent("template")->Render("search_results_pagination", $template_parameters);
        
        return $search_results_pagination;
    }
    /**
     * Used to format the given hadith text as search results
     *
     * It renders the given hadith text within a html template
     * It appends copy image and scroll to top image to each search result
     *
     * @param string $hadith_list the text to format
     * @param array $options the options for formatting the given hadith text
     * @param array $parameters the parameters used to format the given text    
     *     
     * @return string $formatted_text_html the formatted text
     */
    private function FormatAsNavigatorSearchResults($hadith_list, $options, $parameters)
    {
        /** The css class for extra search results */
        $search_results_extra_css = "ic-navigator-class-ltr";
        /** The dictionary url and language rtl information are fetched */
        $dictionary_information   = $this->GetComponent("authors")->GetDictionaryInformation($parameters['parameters']['language']);
        /** The highlighted search keyword template parameters */
        $template_parameters      = array("content" => $parameters['parameters']['search_text'], "css_class" => "highlight-search-keyword");
        /** The span template is rendered using the template parameters */
        $search_text_html         = $this->GetComponent("template")->Render("span", $template_parameters);
        /** The required formatted hadith text */
        $formatted_text_html      = "";
        /** Each hadith text is placed inside a list tag */
        for ($count = 0;$count < count($hadith_list);$count++) 
        {
            /** The hadith text */
            $hadith_text                   = $hadith_list[$count]['hadith_text'];
            /** The words in the hadith text are wrapped if required */
            if (in_array("dictionary links", $options['tools_list'])) {
                $hadith_text               = $this->WrapWordsInText($hadith_text, "hadith-", $hadith_list[$count]['hadith_number'], "hadith", $dictionary_information['dictionary_url']);
            }
            /** The hadith number is added to the hadith text */
            $hadith_text                   = "<u>" . $hadith_list[$count]['hadith_number'] . ".</u>&nbsp;&nbsp;" . $hadith_text;
            /** The hadith text. It is formatted by adding paragraph tags around every 3 sentences */
            $hadith_text                   = $this->FormatText($hadith_text, array("add paragraphs"));
            /** The html id of the top element. It is used to scroll to the top of the navigator */
            $scroll_top_id                 = ($count > 0) ? $scroll_top_id : "hadith-" . $hadith_list[$count]['hadith_number'];
            /** The search result extra text is fetched */
            $search_results_extra_text     = $this->GetSearchResultsExtraText($hadith_list[$count], $parameters['parameters']['language'], $hadith_list[$count]['hadith_text'], $scroll_top_id, $search_results_extra_css, $options);
            /** The search text is replaced with the highlighted search text */
            $hadith_text                   = str_ireplace($parameters['parameters']['search_text'], $search_text_html, $hadith_text);
            /** The html id is added to the hadith number */
            $hadith_text                   = str_replace("{hadith_id}", "hadith-" . $hadith_list[$count]['hadith_number'], "<div id='{hadith_id}'>" . $hadith_text . "</div>");
            /** The template parameters are set */
            $template_parameters           = array("text" => $hadith_text . $search_results_extra_text);
            /** The hadith text */
            $hadith_text                   = $this->GetComponent("template")->Render("list_item", $template_parameters);
            /** The hadith text is added to the verse text html */
            $formatted_text_html           = $formatted_text_html . " " . $hadith_text;
        }
        /** The template parameters are set */
        $template_parameters               = array("content" => $formatted_text_html, "list_class" => "");
        /** The start number of the verses */
        $start_number                      = (($parameters['page_number'] - 1) * $parameters['hadith_per_page']) + 1;
        /** The start ayat number is set */
        $template_parameters['start']      = ($start_number > 0) ? $start_number : '1';
        /** The css list class is set */
        $template_parameters['list_class'] = $template_parameters['list_class'] . " hadith-search-results";
        /** The verse text */
        $formatted_text_html               = $this->GetComponent("template")->Render("unordered_list", $template_parameters);
        /** The search results pagination */
        $search_results_pagination         = $this->GetSearchResultsPagination($parameters['page_number'], $parameters['total_number_of_pages'], $parameters['total_results']);
        /** The pagination is prepended to the hadith text html */
        $formatted_text_html               = $search_results_pagination . $formatted_text_html;
        
        return $formatted_text_html;
    }
    /**
     * Used to format the Hadith Text
     *
     * It adds paragraph tags around every 3 sentences
     * It also removes punctuation errors such as double '"'
     * It also adds (may peace be upon him) after prophet
     *
     * @param string $hadith_text the hadith text
     * @param array $options the options for formatting the given hadith text
     * @param array $parameters the parameters for formatting the given hadith text
     *     
     * @return string $formatted_hadith_text the formatted hadith text
     */
    private function FormatText($hadith_text, $options = array(), $parameters = array()) 
    {
        /** The formatted hadith text */
        $formatted_hadith_text = $hadith_text;
        /** If the option is set to "add paragraphs" */
        if (in_array("add paragraphs", $options)) {
            /** The formatted hadith text */
            $formatted_hadith_text = "";
	    /** The hadith text is divided into sentences */
	    $hadith_text_data = explode(". ", $hadith_text);
	    /** Single sentence */
  	    $sentence = "";
	    /** All sentences of combined length greater than 200 characters are enclosed in paragraph tags */
	    for ($count = 0;$count < count($hadith_text_data);$count++) {
    	        /** If the sentence is not empty */
		if ($hadith_text_data[$count] != "") {
 	            /** Single sentence */
		    $sentence.= trim($hadith_text_data[$count]) . ". ";
		    /** If the sentence length is greater than 200 characters */
		    if (strlen($sentence) > 300 && (!isset($hadith_text_data[$count+1]) || isset($hadith_text_data[$count+1]) && strpos($hadith_text_data[$count+1], '"') !== 0 )) {
	                $sentence = "<p> " . ($sentence) . " </p>";
		        $formatted_hadith_text.= $sentence;
		        $sentence = "";
		    }
		}
            }
	    /** The last sentence is added if it is not empty */
	    if ($sentence != "") {
	        /** If the last sentence contains closing paragraph tag */
		if (strpos($formatted_hadith_text, "</p>") !== false) {
		    /** The last position of the closing paragraph tag is fetched */
		    $last_index            = strrpos($formatted_hadith_text, "</p>");
		    /** The last sentence is added to the data */
		    $formatted_hadith_text = substr($formatted_hadith_text, 0, $last_index) . $sentence . "</p>";
		}
		else
		    $formatted_hadith_text = "<p> " . trim($sentence) . " </p>";
	    }      
        }
        /** If the option is set to "add paragraphs" */
        else if (in_array("meta data", $options)) {
            /** The template for rendering the ayat meta data */
            $template_parameters               = array("content" => $parameters['meta_data'], "css_class" => "hadith-meta-data");
            /** The hadith meta data is rendered using the given parameters */
            $hadith_meta                       = $this->GetComponent("template")->Render("span", $template_parameters);
            /** The hadith meta data is appended to the hadith text */
            $formatted_hadith_text             .= " " . $hadith_meta;
        }
        
        /** The double '"' are removed */
        $formatted_hadith_text                 = str_replace('""', '"', $formatted_hadith_text);
        /** The text 'prophet' is formatted */
        if (strpos($formatted_hadith_text, "prophet (pbuh)") === false && strpos($formatted_hadith_text, "prophet (may peace be upon him)") === false) {
            $formatted_hadith_text             = str_ireplace('prophet', 'Prophet (may peace be upon him)', $formatted_hadith_text);
        }
        
        return $formatted_hadith_text;
    }
    /**
     * Used to format the given hadith as a paragraph
     *
     * It formats the hadith as a paragraph list
     *
     * @param array $hadith_list the list of hadith to format
     * @param array $options the options for formatting the hadith
     * @param array $parameters the parameters used to format the given text     
     *
     * @return string $formatted_hadith_html the formatted hadith html
     */
    private function FormatAsParagraph($hadith_list, $options, $parameters) 
    {
        /** The hadith text */
        $formatted_hadith_html      = "";
        /** The dictionary url and language rtl information are fetched for the current language */
        $dictionary_information     = $this->GetComponent("authors")->GetDictionaryInformation($this->data['language']);
        /** Each hadith test item is checked */
        for ($count = 0; $count < count($this->data['hadith_list']); $count++) 
        {
            /** The hadith number */
            $hadith_number          = $this->data['hadith_list'][$count]['hadith_number'];
            /** The words in the hadith text are wrapped if required */
            if (in_array("dictionary links", $options['tools_list'])) {
                $hadith_text        = $this->WrapWordsInText($this->data['hadith_list'][$count]['hadith_text'], "hadith-", $hadith_number, "hadith", $dictionary_information['dictionary_url']);
            }
            else {
                $hadith_text        = $this->data['hadith_list'][$count]['hadith_text'];           
            }
            /** The hadith number is added to the hadith text */
            $hadith_text            = "<u>" . $hadith_number . ".</u>&nbsp;&nbsp;" . $hadith_text;
            /** The hadith text. It is formatted by adding paragraph tags around every 3 sentences */
            $hadith_text            = $this->FormatText($hadith_text, array("add paragraphs"));
            /** The html id is added to the hadith number */
            $hadith_text            = str_replace("{hadith_id}", "hadith-" . $hadith_number, "<div id='{hadith_id}'>" . $hadith_text . "</div>");
            /** The list of tools to be appended to the hadith text */
            $tools_list             = array();
            /** The short code image is fetched if it is given as an option */
            $tools_list[]           = (in_array('shortcode', $options['tools_list'])) ? $this->GetShortcodeImage($this->data['hadith_list'][$count], $this->data['language']) : "";
            /** The copy image is fetched if it is given as an option */
            $tools_list[]           = (in_array('copy', $options['tools_list'])) ? $this->GetCopyImage($hadith_number . "search-results", $this->data['hadith_list'][$count]['hadith_text'], "hadith"): "";
            /** The scroll top image is fetched if it is given as an option */
            $tools_list[]           = (in_array('scroll to top', $options['tools_list'])) ? $this->GetScrollTopImage("hadith-" . $hadith_number, "hadith") : "";
            /** If one of the tools is given, then it is appended to the hadith text */
            if (implode("", $tools_list) !== "") {
                /** The tools list is formatted */
                $tools_list         = implode("&nbsp;&nbsp;", $tools_list);
                /** The template parameters for list_item template */
                $template_parameters['text'] = $hadith_text . $tools_list;
            }
            else {
                /** The template parameters for list_item template */
                $template_parameters['text'] = $hadith_text;
            }
            /** The hadith text */
            $formatted_hadith_html.= $this->GetComponent("template")->Render('list_item', $template_parameters);
        }
        /** The template parameters for the hadith table html template */
        $template_parameters = array(
            "text" => $formatted_hadith_html
        );
        /** The hadith html text */
        $formatted_hadith_html = $this->GetComponent("template")->Render('hadith_table', $template_parameters);
        
        return $formatted_hadith_html;
    }    
}

