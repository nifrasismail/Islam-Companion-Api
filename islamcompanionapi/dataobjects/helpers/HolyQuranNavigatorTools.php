<?php

namespace IslamCompanionApi\DataObjects\Helpers;

/**
 * This class implements the HolyQuranNavigatorTools Trait
 *
 * An object of this class allows access to Holy Quran tools
 * It provides functions for fetching the hmtl for Holy Quran navigator tools
 *
 * @category   IslamCompanionApi
 * @package    DataObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait HolyQuranNavigatorTools
{
    /** The class uses functions from NavigatorTools trait */
    use \IslamCompanionApi\DataObjects\Helpers\NavigatorTools;
    /**
     * Used to return an image that allows copying the Ayat Shortcode
     *
     * It returns an image that allows copying the Ayat shortcode for WordPress
     *
     * @param int $ayat_id the ayat id
     * @param int $sura the sura id
     * @param int $ayat the ayat id
     * @param string $narrator the narrator for the translation
     * @param string $language the language for the translation
     *
     * @return string $shortcode_image_html the html for the shortcode image
     */
    public function GetAyatShortcodeImage($ayat_id, $sura, $ayat_text, $narrator, $language)
    {
        /** The application image folder */
        $application_template_folder_url = $this->GetConfig("path", "application_template_url") . "/images/";
        /** The url to the template folder */
        $application_template_url = $this->GetConfig("path", "application_template_url");
        /** The shortcode image url */
        $shortcode_image_url = $application_template_url . "/images/shortcode-small.png";
        /** The ayat text is encoded */
        $ayat_text = $this->GetComponent("encryption")->EncodeData($ayat_text);
        /** The shortcode string is set */
        $shortcode_string = $this->GetComponent("encryption")->EncodeData('[get-verses narrator="' . $narrator . '" 
		                                               language="' . $language . '"
		                                               ayas="' . $sura . ":" . $ayat_id . '"
		                                               container="list"
		                                               css_classes=""]');
        /** The template parameters */
        $template_parameters = array(
            "css_class" => "ic-cursor",
            "image_src" => $shortcode_image_url,
            "shortcode_string" => $shortcode_string,
            "type" => "Holy Quran"
        );
        /** The html template is rendered using the given parameters */
        $shortcode_image_html = $this->GetComponent("template")->Render("shortcode_image", $template_parameters);
        
        return $shortcode_image_html;
    }        
    /**
     * Used to get the extra text for the search result
     *
     * It returns html containing the extra search result text
     * It includes the sura name, sura and ayat number and ruku number
     * It also includes shortcode link, copy link and scroll to top link
     * The search results extra text ends with <hr/> tag
     *
     * @param array $ayat_meta_data the ayat meta data
     * @param string $narrator the narrator for the translation
     * @param string $language the language for the translation
     * @param string $ayat_text the ayat text
     * @param string $scroll_top_id the id of the top verse in the navigator
     * @param string $search_results_extra_css the css class for search results extra text
     * @param array $options the options for formatting the given verse     
     *    
     * @return string $search_results_extra_text the extra text to be appended to the search result
     */
    public function GetSearchResultsExtraText($ayat_meta_data, $narrator, $language, $ayat_text, $scroll_top_id, $search_results_extra_css, $options) 
    {
        /** The ayat meta data is encoded */
        $ayat_meta_data_search_result = $this->GetComponent("encryption")->EncodeData($ayat_meta_data);
        /** The short code image is fetched */
        $shortcode_image              = (in_array('shortcode', $options['tools_list'])) ? $this->GetAyatShortcodeImage($ayat_meta_data['sura_ayat_id'], $ayat_meta_data['sura'], $ayat_text, $narrator, $language) : "";
        /** The ayat meta data is appended to the ayat text */
        $ayat_text                    .= " (" . $ayat_meta_data['sura_name'] . " - " . $ayat_meta_data['sura'] . ")";
        /** The copy image is fetched */
        $copy_image                   = (in_array('copy', $options['tools_list'])) ? $this->GetCopyImage($ayat_meta_data['sura_ayat_id'] . "-" . "search-results", $ayat_text, "Holy Quran") : "";
        /** The scroll top image is fetched */
        $scroll_top_image             = (in_array('scroll to top', $options['tools_list'])) ? $this->GetScrollTopImage("search-results-" . $ayat_meta_data['sura_ayat_id'], "holy-quran") : "";
        /** The search results id */
        $search_results_id            = ($ayat_meta_data['sura'] . "-" . $ayat_meta_data['sura_ayat_id']);
        /** The extra text to be appended to the search result */
        $search_results_extra_text = "<div class='search-results-extra-text " . $search_results_extra_css . "'><u onclick='holy_quran_navigator_object.DisplaySuraRuku(\"" . $ayat_meta_data_search_result . "\")' id='" . $search_results_id . "'>" . $ayat_meta_data['sura_name'] . " - " . $ayat_meta_data['sura'] . ":" . $ayat_meta_data['sura_ayat_id'] . "</u>&nbsp;&nbsp;" . $shortcode_image . "&nbsp;&nbsp;" . $copy_image . "&nbsp;&nbsp;" . $scroll_top_image . "</div> <hr/>";
        
        return $search_results_extra_text;
    }    
}

