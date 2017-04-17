<?php

namespace IslamCompanionApi\UiObjects\Helpers;

/**
 * This class implements the AyatTemplate Trait
 *
 * An object of this class allows formatting verses
 * It provides functions for formattings Holy Quran verse text
 *
 * @category   IslamCompanionApi
 * @package    UiObjects\Helpers
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
trait AyatTemplate
{
    /**
     * Used to format the verse data for email
     *
     * It formats the given verse data so it can be viewed in email
     *
     * @param string $verse_text_html the verse text to format
     * @param array $meta_data the meta data for the ayas
     *    narrator => string the verse text narrator
     *    language => string the verse text language
     *    sura => string the sura name
     *    start_ayat => int the start ayat number
     *    end_ayat => int the end ayat number
     *          
     * @return string $verse_text_html the html for the verse text
     */
    public function PopulateEmailSubscriberTemplate($verse_text_html, $meta_data)
    {
        /** The language information is fetched */
        $language_data                     = $this->GetLanguageInformation($meta_data['language']);
        /** The css class for the verse text */
        $css_class                         = $language_data['css_class'];
        /** The translation information is fetched */
        $translation_information           = $this->GetComponent("authors")->GetTranslationInformation($meta_data['language']);
        /** The template parameters are set */
        $template_parameters               = array("list_class" => $css_class . " verse-search-result " . $translation_information['css_attributes']);
        /** The template parameters are set */
        $template_parameters               = $this->GetConfig("custom", "css_url_list");
        /** The css tags */
        $css_tags                          = $this->GetComponent("template")->Render("css_tags", $template_parameters);
        
        /** The template parameters are set */
        $template_parameters               = $this->GetConfig("custom", "javascript_url_list");
        /** The javascript text */
        $javascript_tags                   = $this->GetComponent("template")->Render("javascript_tags", $template_parameters);
        
        /** The font url list */
        $font_url_list                     = $this->GetConfig("custom", "font_url_list");
        /** The template parameters are set */
        $template_parameters               = array_values($font_url_list);
        /** The font tags */
        $font_tags                         = $this->GetComponent("template")->Render("font_tags", $template_parameters);
        /** The unsubscribe link. It takes the user to the Hadith navigator page */
        $unsubscribe_link = $this->GetConfig("general", "site_url") . "hadith";
        /** The template parameters are set */
        $template_parameters               = array("body" => $verse_text_html, "title" => "Islam Companion", "css_tags" => $css_tags, "javascript_tags" => $javascript_tags, "font_tags" => $font_tags, "language" => $meta_data['language'], "narrator" => $meta_data['narrator'], "sura" => $meta_data['sura'], "start_ayat" => $meta_data['start_ayat'], "end_ayat" => $meta_data['end_ayat'], "ayat_keywords" => $meta_data['keywords'], "ayat_times" => $meta_data['times'], "ayat_count" => $meta_data['number_of_results'], "email_addresses" => $meta_data['email_address'], "ayat_order" => $meta_data['order'], "unsubscribe_link" => $unsubscribe_link);
        /** The css list class is set */
        $template_parameters['list_class'] = "verse-search-results";
        /** The verse text */
        $verse_text_html                   = $this->GetComponent("template")->Render("quran_email", $template_parameters);
        
        return $verse_text_html;
    }
}

