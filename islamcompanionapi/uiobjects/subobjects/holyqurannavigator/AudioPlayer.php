<?php

namespace IslamCompanionApi\UiObjects\SubObjects\HolyQuranNavigator;

use \IslamCompanionApi\DataObjects\Suras as Suras;
/**
 * This class implements the AudioPlayer class
 *
 * It contains functions used to generate the html for the audio player
 *
 * @category   IslamCompanionApi
 * @package    UiObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class AudioPlayer extends \Framework\Object\UiObject
{
    /**
     * Used to load the Audio Player object with data
     *
     * It loads the data from database to the object
     *
     * @param array $data data used to read verse information from database
     * it is an array with following keys:
     * sura => the current sura
     * ruku => the current ruku
     */
    public function Read($data = "") 
    {
        /** The configuration object is fetched */
        $parameters['configuration'] = $this->GetConfigurationObject();
        /** The suras object is created */
        $suras = new Suras($parameters);
        /** The sura data is fetched */
        $sura_data = $suras->GetSuraData($data['sura']);
        /** The audio file suffix */
        $audio_file_suffix = $sura_data["audiofile"];
        /** The sura ruku */
        $sura_ruku = $suras->GetSuraRukuNumber($data['ruku']);
        /** The sura ruku is left padded with 0 if it is less than 10 */
        $sura_ruku = ($sura_ruku < 10) ? "0" . $sura_ruku : $sura_ruku;
        /** The audio file name */
        $audio_file_name = "rukoo" . $sura_ruku . $audio_file_suffix . ".mp3";
        /** The absolute url of the audio file */
        $this->audio_file_url = $this->GetConfig("custom", "audio_file_base_url") . $audio_file_name;
    }
    /**
     * Used to display the Audio Player
     *
     * It returns the html of the audio player
     *
     * @return string $audio_player_html the html string for the Audio Player
     */
    public function Display() 
    {
        /** The path to the template folder */
        $template_folder_path = $this->GetConfig("path", "application_template_path");
        /** The path to the audio player template file */
        $template_file_path = $template_folder_path . DIRECTORY_SEPARATOR . "audio_player.html";
        /** The template parameters */
        $template_parameters = array(
            "audio_control_id" => "ic-holy-quran-audio-control",
            "audio_url" => $this->audio_file_url
        );
        /** The html template is rendered using the given parameters */
        $audio_player_html = $this->GetComponent("template_helper")->RenderTemplateFile($template_file_path, $template_parameters);
        return $audio_player_html;
    }
}

