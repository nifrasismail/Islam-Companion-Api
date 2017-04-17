<?php

namespace IslamCompanionApi\UiObjects\Helpers;

/**
 * This class implements the HadithTemplate Trait
 *
 * An object of this class allows formatting verses
 * It provides functions for formattings Holy Quran verse text
 *
 * @category   IslamCompanionApi
 * @package    UiObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait HadithTemplate
{
    /**
     * Used to format the hadith data for email
     *
     * It formats the given hadith data so it can be viewed in email
     *
     * @param string $hadith_text_html the hadith text to format
     * @param array $meta_data the meta data for the hadith
     *    language => string the language for the translation
     *    search_text => string the search text
     *    number_of_results => int the number of search results
     *    start => int the position from where the search results should be returned
     *    order => string [sequence~random] the order of the search results
     *    times => string the times at which the hadith should be sent
     *    email_address => string the email addresses at which the Hadith email should be sent
     *          
     * @return string $hadith_text_html the html for the hadith text
     */
    public function PopulateEmailSubscriberTemplate($hadith_text_html, $meta_data)
    {
        /** The template parameters are set */
        $template_parameters = array("content" => $hadith_text_html, "list_class" => "");
        /** The css list class is set */
        $template_parameters['list_class'] = $template_parameters['list_class'] . " hadith-search-results";
        /** The verse text */
        $hadith_text_html = $this->GetComponent("template")->Render("unordered_list", $template_parameters);
       
        /** The template parameters are set */
        $template_parameters = $this->GetConfig("custom", "css_url_list");
        /** The css tags */
        $css_tags = $this->GetComponent("template")->Render("css_tags", $template_parameters);
        /** The unsubscribe link. It takes the user to the Hadith navigator page */
        $unsubscribe_link = $this->GetConfig("general", "site_url") . "hadith";
        /** The template parameters are set */
        $template_parameters = array("body" => $hadith_text_html, "title" => "Islam Companion", "css_tags" => $css_tags, "javascript_tags" => "", "hadith_keywords" => $meta_data['keywords'], "hadith_times" => $meta_data['times'], "hadith_count" => $meta_data['number_of_results'], "email_addresses" => $meta_data['email_address'], "hadith_order" => $meta_data['order'], "unsubscribe_link" => $unsubscribe_link);
         /** The css list class is set */
         $template_parameters['list_class'] = "hadith-search-results";
         /** The hadith text */
         $hadith_text_html = $this->GetComponent("template")->Render("hadith_email", $template_parameters);
       
         return $hadith_text_html;
    }
}

