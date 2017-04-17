<?php

namespace IslamCompanionApi;

/**
 * This is the API class for WordPress
 * It implements functions used to add, edit and delete Islam Companion data from the WordPress installation
 *
 * It is implements the functions for managing Islam Companion data in the WordPress installation
 *
 * @category   IslamCompanionApi
 * @package    IslamCompanionApi
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
trait WordPressAdminApi
{
    /**
     * Used to delete WordPress custom posts
     *
     * It deletes all custom posts of type Hadith
     * {@internal context local api}
     *
     * @param array $parameters the parameters for the function
     *    post_count => int [1-500] the number of posts to delete
     *
     * @return array $response the api function response
     *    posts_deleted => int the number of deleted custom posts
     */
    public function HandleDeleteHadithData($parameters) 
    {
        /** The number of posts to delete */
        $post_count      = $parameters['parameters']['post_count'];
        /** The type of posts to delete */
        $post_type_list  = array("Hadith");
        /** The WordPress data object is fetched and used to delete the Quranic data */
        $posts_deleted = $this->GetComponent("wordpressquranicdataimport")->DeleteData($post_count, $post_type_list);
        /** The api function response */
        $response = array(
            "posts_deleted" => $posts_deleted
        );
        return $response;
    }
    /**
     * Used to delete WordPress custom posts
     *
     * It deletes all custom posts of type Ayas
     * {@internal context local api}
     *
     * @param array $parameters the parameters for the function
     *    post_count => int [1-500] the number of posts to delete
     *
     * @return array $response the api function response
     *    posts_deleted => int the number of deleted custom posts
     */
    public function HandleDeleteHolyQuranData($parameters) 
    {
        /** The number of posts to delete */
        $post_count      = $parameters['parameters']['post_count'];
        /** The type of posts to delete */
        $post_type_list  = array("Ayas");
        /** The WordPress data object is fetched and used to delete the Quranic data */
        $posts_deleted = $this->GetComponent("wordpressquranicdataimport")->DeleteData($post_count, $post_type_list);
        /** The api function response */
        $response = array(
            "posts_deleted" => $posts_deleted
        );
        return $response;
    }
    /**
     * Used to delete WordPress custom posts
     *
     * It deletes all custom posts of type Books
     * {@internal context local api}
     *
     * @param array $parameters the parameters for the function
     *    post_count => int [1-500] the number of posts to delete
     *
     * @return array $response the api function response
     *    posts_deleted => int the number of deleted custom posts
     */
    public function HandleDeleteHadithMetaData($parameters) 
    {
        /** The number of posts to delete */
        $post_count      = $parameters['parameters']['post_count'];
        /** The type of posts to delete */
        $post_type_list  = array("Books");
        /** The WordPress data object is fetched and used to delete the Quranic data */
        $posts_deleted = $this->GetComponent("wordpressquranicdataimport")->DeleteData($post_count, $post_type_list);
        /** The api function response */
        $response = array(
            "posts_deleted" => $posts_deleted
        );
        return $response;
    } 
    /**
     * Used to delete WordPress custom posts
     *
     * It deletes all custom posts of type Suras and Authors
     * {@internal context local api}
     *
     * @param array $parameters the parameters for the function
     *    post_count => int [1-500] the number of posts to delete
     *
     * @return array $response the api function response
     *    posts_deleted => int the number of deleted custom posts
     */
    public function HandleDeleteHolyQuranMetaData($parameters) 
    {
        /** The number of posts to delete */
        $post_count      = $parameters['parameters']['post_count'];
        /** The type of posts to delete */
        $post_type_list  = array("Suras", "Authors");
        /** The WordPress data object is fetched and used to delete the Quranic data */
        $posts_deleted = $this->GetComponent("wordpressquranicdataimport")->DeleteData($post_count, $post_type_list);
        /** The api function response */
        $response = array(
            "posts_deleted" => $posts_deleted
        );
        return $response;
    }
    /**
     * Used to add WordPress custom posts of type Books
     *
     * It adds custom posts of type Books
     * {@internal context local api}
     *
     * @param array $parameters the parameters for the function
     *    user_id => int [1-10000] the user id of the user
     *
     * @return array $response the api function response
     *    posts_added => int the number of wordpress custom posts that were added
     */
    public function HandleAddHadithMetaData($parameters) 
    {
        /** The user id of the logged in user */
        $user_id = $parameters['parameters']['user_id'];
        /** The WordPress data object is fetched and used to add the Hadith Meta data */
        $posts_added = $this->GetComponent("wordpresshadithdataimport")->AddHadithMetaData($user_id);
        /** The api function response */
        $response = array(
            "posts_added" => $posts_added
        );
        return $response;
    }
    /**
     * Used to add WordPress custom posts of type Hadith
     *
     * It adds custom posts of type Hadith
     * {@internal context local api}
     *
     * @param array $parameters the parameters for the function
     *    user_id => int [1-10000] the user id of the user
     *    hadith_count => int [1-500] the number of hadith posts to add
     *    start_hadith => int [0-20000] the id of the first hadith to be imported
     *
     * @return array $response the api function response
     *    posts_added => int the number of wordpress custom posts that were added
     */
    public function HandleAddHadithData($parameters) 
    {
        /** The user id of the logged in user */
        $user_id = $parameters['parameters']['user_id'];
        /** The hadith count */
        $hadith_count = $parameters['parameters']['hadith_count'];  
        /** The start hadith */
        $start_hadith = $parameters['parameters']['start_hadith']; 
        /** The WordPress data object is fetched and used to add the Hadith data */
        $posts_added = $this->GetComponent("wordpresshadithdataimport")->AddHadithData($user_id, $start_hadith, $hadith_count);
        /** The api function response */
        $response = array(
            "posts_added" => $posts_added
        );
        return $response;
    }
    /**
     * Used to add WordPress custom posts
     *
     * It adds custom posts of type Suras and Authors
     * {@internal context local api}
     *
     * @param array $parameters the parameters for the function
     *    data_type => string [sura~author] the type of custom post to import
     *    user_id => int [1-1000000] the user id of the logged in user
     *
     * @return array $response the api function response
     *    posts_added => int the number of custom meta posts that were added
     */
    public function HandleAddHolyQuranMetaData($parameters) 
    {
        /** The user id of the logged in user */
        $user_id = $parameters['parameters']['user_id'];
        /** The type of data to import. i.e sura_meta or author_meta */
        $data_type = $parameters['parameters']['data_type'];
        /** The WordPress data object is fetched and used to add the Quranic data */
        $posts_added = $this->GetComponent("wordpressquranicdataimport")->AddHolyQuranMetaData($user_id, $data_type);
        /** The api function response */
        $response = array(
            "posts_added" => $posts_added
        );
        return $response;
    }
    /**
     * Used to add WordPress custom posts
     *
     * It adds custom posts of type Ayas
     * {@internal context local api}
     *
     * @api
     * @param array $parameters the parameters for the function
     *    user_id => int [1-1000000] the user id of the logged in user
     *    start_ayat => int [1-6236] the start ayat
     *    total_ayat_count => int [1-6236] the total number of ayas
     *    narrator => string [custom] the translator name
     *    language => string [custom] the language of the translation
     *
     * @return array $response the api function response
     *    posts_added => int the number of custom meta posts that were added
     */
    public function HandleAddHolyQuranData($parameters) 
    {
        /** The user id of the logged in user */
        $user_id = $parameters['parameters']['user_id'];
        /** The start ayat */
        $start_ayat = $parameters['parameters']['start_ayat'];
        /** The total number of ayas to add */
        $total_ayat_count = $parameters['parameters']['total_ayat_count'];
        /** The translator name */
        $translator = $parameters['parameters']['narrator'];
        /** The language for the translation */
        $language = $parameters['parameters']['language'];
        /** The WordPress data object is fetched and used to add the Quranic data */
        $posts_added = $this->GetComponent("wordpressquranicdataimport")->AddHolyQuranData($user_id, $start_ayat, $total_ayat_count, $translator, $language);
        /** The api function response */
        $response = array(
            "posts_added" => $posts_added
        );
        return $response;
    }   
}
