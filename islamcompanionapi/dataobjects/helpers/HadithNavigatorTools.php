<?php

namespace IslamCompanionApi\DataObjects\Helpers;

/**
 * This class implements the HadithNavigatorTools Trait
 *
 * An object of this class allows access to Holy Quran tools
 * It provides functions for fetching the hmtl for Holy Quran snavigator tools
 *
 * @category   IslamCompanionApi
 * @package    DataObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait HadithNavigatorTools
{
    /** The class uses functions from NavigatorTools trait */
    use \IslamCompanionApi\DataObjects\Helpers\NavigatorTools;
    /**
     * Used to get the shortcode image html
     *
     * It returns html containing the shortcode image
     *
     * @param array $hadith_meta_data the hadith meta data
     * @param string $hadith_text the hadith text
     * @param string $hadith_language the hadith language
     *
     * @return array $shortcode_image_html the shortcode image html
     */
    public function GetShortcodeImage($hadith_meta_data, $hadith_language)
    {
        /** The application image folder */
        $application_template_folder_url = $this->GetConfig("path", "application_template_url") . "/images/";
        /** The url to the template folder */
        $application_template_url = $this->GetConfig("path", "application_template_url");
        /** The shortcode image url */
        $shortcode_image_url = $application_template_url . "/images/shortcode-small.png";
        /** The hadith text is encoded */
        $hadith_meta_data['hadith_text'] = $this->GetComponent("encryption")->EncodeData(urlencode($hadith_meta_data['hadith_text']));
        /** The shortcode string is set */
        $shortcode_string = base64_encode('[get-hadith user_interface="plain text" hadith_numbers="' . $hadith_meta_data['id'] . '" css_classes="ic-ltr-text"]');
        /** The template parameters */
        $template_parameters = array(
            "css_class" => "ic-cursor",
            "image_src" => $shortcode_image_url,
            "shortcode_string" => $shortcode_string,
            "type" => "Hadith"
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
     * The search results extra text ends with <hr/> tag
     *
     * @param array $hadith_meta_data the hadith meta data
     * @param string $language the language for the translation
     * @param string $hadith_text the hadith text
     * @param string $scroll_top_id the id of the top hadith in the navigator
     * @param string $search_results_extra_css the css class for search results extra text
     * @param array $options the options for formatting the given hadith     
     *
     * @return string $search_results_extra_text the extra text to be appended to the search result
     */
    public function GetSearchResultsExtraText($hadith_meta_data, $language, $hadith_text, $scroll_top_id, $search_results_extra_css, $options)
    {
        /** The hadith meta data is encoded */
        $hadith_meta_data_search_result = $this->GetComponent("encryption")->EncodeData($hadith_meta_data);
        /** The short code image is fetched */
        $shortcode_image                = (in_array('shortcode', $options['tools_list'])) ? $this->GetShortcodeImage($hadith_meta_data['sura_ayat_id'], $hadith_meta_data['sura'], $hadith_text, $narrator, $language) : "";
        /** The copy image is fetched */
        $copy_image                     = (in_array('copy', $options['tools_list'])) ? $this->GetCopyImage($hadith_meta_data['id'] . "-" . "search-results", $hadith_text, "hadith") : "";
        /** The scroll top image is fetched */
        $scroll_top_image               = (in_array('scroll to top', $options['tools_list'])) ? $this->GetScrollTopImage("search-results-" . $hadith_meta_data['id'], "hadith") : "";
        /** The extra text to be appended to the search result */
        $search_results_extra_text = "<div class='search-results-extra-text " . $search_results_extra_css . "'><u onclick='hadith_navigator_object.DisplayHadithChapter(\"" . $hadith_meta_data_search_result . "\")' id='ic-" . $hadith_meta_data['hadith_number'] . "'>" . $hadith_meta_data['source'] . " - " . $hadith_meta_data['book'] . " : " . $hadith_meta_data['hadith_number'] . ":" . $hadith_meta_data['id'] . "</u> &nbsp;&nbsp;" . $shortcode_image . "&nbsp;&nbsp;" . $copy_image  . "&nbsp;&nbsp;" . $scroll_top_image . "</div><br/> <hr/>";
        
        return $search_results_extra_text;
    }
}

