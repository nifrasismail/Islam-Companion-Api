<?php
namespace Framework\Object;
/**
 * This class implements the WordPress Object class
 *
 * Each object of this class represents a single WordPress item. e.g post, taxonomy, tag etc
 * It contains functions that help in constructing WordPress data objects
 *
 * @category   Framework
 * @package    Object
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    Release: 1.0.0
 * @link       N.A
 */
class WordPressDataObject extends DataObject
{
    /**
     * Used to load the data from database
     *
     * It reads data from WordPress and returns it
     * It uses the WordPress WP_Query object for fetching posts
     *
     * @since 1.0.0
     * @param mixed $parameters used to read the data from database. it is same as parameters for WP_Query
     *
     * @return $data the WordPress object data or false if the data was not found
     */
    final public function LoadWordPressObject($parameters) 
    {
        /** The data that needs to be returned */
        $data = false;
        /** If the parameters are set */
        if (count($parameters) > 0) 
        {
            $data = get_posts($parameters);
            /** If the post was found */
            if (isset($data[0])) 
            {
                /** The custom fields for each post is fetched */
                for ($count = 0;$count < count($data);$count++) 
                {
                    /** The WordPress post. The post is converted from object to an array */
                    $post = get_object_vars($data[$count]);
                    /** The post id */
                    $post_id = $post['ID'];
                    /** The custom fields of the post are fetched */
                    $custom_fields = get_post_custom($post_id);
                    /** The concatenation of all custom field values */
                    $custom_field_values = '';
                    /**
                     * The data in each custom field is converted to string from array
                     * This is done because WordPress allows multiple custom fields with same name
                     * The checksum of all field values is also calculated
                     * It is calculated on the concatenation of all custom field values
                     */
                    foreach ($custom_fields as $field_name => $field_value) 
                    {
                        /** If the custom field value is an array, then it is converted to tilde separated list */
                        if (is_array($field_value)) $field_value = implode("~", $field_value);
                        $custom_fields[$field_name] = $field_value;
                    }
                    /** The custom fields are sorted by key */
                    ksort($custom_fields, SORT_STRING);
                    /**					
                     * The checksum of all field values is calculated
                     * It is calculated on the concatenation of all custom field values
                     */
                    foreach ($custom_fields as $field_name => $field_value) 
                    {
                        /** The checksum and _edit_lock fields are ignored */
                        if ($field_name != "checksum" && $field_name != "_edit_lock") $custom_field_values = $custom_field_values . base64_encode($field_value);
                    }
                    /** The checksum of all field values */
                    $checksum = md5($custom_field_values);
                    /** If a custom field called checksum exists and it is not equal to the above checksum then an exception is thrown */
                    if (isset($custom_fields["checksum"]) && $custom_fields["checksum"] != $checksum) throw new \Exception("Checksum of custom post of type " . $this->meta_information['object_name'] . " with ID: " . $post_id . " does not match. Calculated checksum: " . $checksum . ". Stored checksum: " . $custom_fields["checksum"]);
                    /** The custom fields are added to the post */
                    $post = array_merge($post, $custom_fields);
                    /** The post is added to the $data variable */
                    $data[$count] = $post;
                }
            }
        }
        /** If one post needs to be fetched */
        if ($parameters['posts_per_page'] == '1' && isset($data[0])) 
        {
            $data = $data[0];
        }
        return $data;
    }
    /**
     * Used to load the data from database to the data object property
     *
     * It reads data from WordPress and loads it to the $data property of the object
     * The type of WordPress object to load
     * For example custom post or category is given in the parameters argument of the constructor
     *
     * @since 1.0.0
     * @param mixed $parameters used to read the data from database. it is same as the parameters used by the WP_Query object
     * @link https://codex.wordpress.org/Class_Reference/WP_Query
     * @throws object Exception an exception is thrown if the type of data is not correct
     *
     * @return $is_valid used to indicate that data was found in database
     */
    final public function Read($parameters) 
    {
        /** Used to indicate if the record exists */
        $is_valid = false;
        /** The WordPress object is loaded */
        $data = $this->LoadWordPressObject($parameters);
        /** If the object was found */
        if ($data) 
        {
            /** then it is set to the data property of the current object */
            $this->data = $data;
            /** is_valid is set to true */
            $is_valid = true;
        }
        return $is_valid;
    }
    /**
     * Used to filter the given data
     *
     * It checks the given parameters and only includes the fields given in the parameters
     * If the field list given in the parameters has a DISTINCT clause then the data is filtered so it only
     * Includes records that have distinct values for the field
     *
     * @since 1.0.0
     * @param mixed $parameters the parameters used to fetch the data. for relational data, it should have following keys:
     * fields => list of field names to fetch
     * condition => the condition used to fetch the data from database
     * It can be a single string or integer value. in this case the previously set field name and table name are used
     * Or an array with following fields: field,value,table,operation and operator
     * read_all => used to indicate if all data should be returned
     * In case of non relational data, it can be empty
     * @param array $data the data read from the data source
     *
     * @link https://codex.wordpress.org/Class_Reference/WP_Query
     * @throws object Exception an exception is thrown if the type of data is not correct
     *
     * @return array $filtered_data the filtered data
     */
    final public function FilterData($parameters, $data) 
    {
        /** If the fields parameter is empty or equal to * then the function returns the original data without filtering it */
        if ($parameters['fields'] == '' || $parameters['fields'] == '*') return $data;
        /** The filtered data */
        $filtered_data = array();
        /** Distinct values for the DISTINCT field given in the parameters */
        $distinct_values = array();
        /** The list of fields to fetch */
        $field_list = explode(",", $parameters['fields']);
        /** If one post needs to be fetched */
        if (!$parameters['read_all']) 
        {
            $data = array(
                $data
            );
        }
        /** Each record in the data is checked */
        for ($count1 = 0;$count1 < count($data);$count1++) 
        {
            /** The data item */
            $data_item = $data[$count1];
            /** The filtered data item */
            $filtered_data_item = array();
            /** Used to indicate if the data item should be added to the filtered data list */
            $exclude_data_item = false;
            /** Each field is checked */
            for ($count2 = 0;$count2 < count($field_list);$count2++) 
            {
                /** The field name */
                $field_name = trim($field_list[$count2]);
                /** The transformed field name. e.g the field name given in as statement */
                $transformed_field_name = $field_name;
                /** If the field name has a distinct clause */
                if (strpos($field_name, "DISTINCT") !== false) 
                {
                    /** The distinct clause is removed */
                    $field_name = str_replace("DISTINCT", "", $field_name);
                    $field_name = str_replace("(", "", $field_name);
                    $field_name = trim(str_replace(")", "", $field_name));
                    $transformed_field_name = $field_name;
                    /** If the field name contains 'as' */
                    if (strpos(strtolower($field_name), "as") !== false) {
                        list($field_name, $new_field_name) = explode("as", $field_name);
                        $field_name = trim($field_name);
                        $transformed_field_name = trim($new_field_name);                        
                    }
                   
                    /** If the field value already exists then the field value is not added to the filtered data and the loop ends */
                    if (in_array($data_item[$field_name], $distinct_values)) 
                    {
                        $exclude_data_item = true;
                        break;
                    }
                    /** If the field value was not found then it is added to the list of distinct field values */
                    else 
                    {
                        $distinct_values[] = $data_item[$field_name];
                    }
                }
                /** If the field value exists for the field name then the function returns the original data */
                if (isset($data_item[$field_name])) {
                    /** The field value is added to the filtered data item */
                    $filtered_data_item[$transformed_field_name] = $data_item[$field_name];
                }
            }
            /** If the data item should not be excluded from the filter list then it is added */
            if (!$exclude_data_item) 
            {
                /** The filtered data item is added to the filtered data */
                $filtered_data[] = $filtered_data_item;
            }
        }

        /** If one post needs to be fetched */
        if (isset($filtered_data[0]) && !$parameters['read_all']) 
        {
            $filtered_data = $filtered_data[0];
        }
        return $filtered_data;
    }
    /**
     * Used to indicate if the WordPress object already exists in database
     *
     * If the current WordPress object is a post, then the method will check if
     * A post with the value in the key_field object property exists
     *
     * @since 1.0.0
     *
     * @return boolean $record_exists it is true if the record already exists. it is false otherwise
     */
    public function RecordExists() 
    {
        /** Used to indicate if the record exists */
        $record_exists = false;
        /** The key field name */
        $key_field = $this->GetKeyField();
        /** The key field value of the post. If not found then function returns false */
        if (isset($this->data[$key_field])) 
        {
            /** The post key field value */
            $key_field_value = $this->data[$key_field];
            /** If the type of WordPress object is post */
            if ($this->meta_information['object_type'] == "post") 
            {
                /** If the key field is post_title */
                if ($key_field == "post_title") 
                {
                    $post = get_page_by_title($post_title, "ARRAY_A", $this->meta_information['object_name']);
                }
                /** If the key field is a custom post */
                else 
                {
                    /** The custom_ prefix is removed from the key field */
                    $key_field = str_replace("custom_", "", $key_field);
                    /** The parameters used to search for the WordPress post */
                    $parameters = array(
                        'post_type' => $this->meta_information['object_name'],
                        'posts_per_page' => '1',
                        'meta_key' => $key_field,
                        'meta_value' => $key_field_value
                    );
                    /** The post is searched by key field */
                    $post = $this->LoadWordPressObject($parameters);
                }
                if (count($post) > 0) $record_exists = true;
            }
        }
        return $record_exists;
    }
    /**
     * Used to delete the WordPress data
     *
     * It deletes data from WordPress
     *
     * @since 1.0.0
     * @throws object Exception an exception is thrown if the object is read only
     * @throws object Exception an exception is thrown if the object could not be deleted
     */
    final public function Delete() 
    {
        /** If the current object is set to read only then an exception is thrown */
        if ($this->IsReadonly()) throw new \Exception("Cannot delete readonly object");
        /** If the post already exists, then it is deleted */
        if (isset($this->data[0]['ID'])) wp_delete_post($this->data[0]['ID'], true);
    }
    /**
     * Used to save the object data
     *
     * It saves the object data to a WordPress object. e.g post
     *
     * @since 1.0.0
     * @throws object Exception an exception is thrown if the object is read only
     * @throws object Exception an exception is thrown if the object's data could not be saved
     */
    final public function Save() 
    {
        /** If the current object is set to read only then an exception is thrown */
        if ($this->IsReadonly()) throw new \Exception("Cannot save readonly object");
        /** If the data needs to be saved to a post */
        if ($this->meta_information['object_type'] == "post") 
        {
            /** If the post_status is not set then it is set to publish */
            $this->data['post_status'] = (isset($this->data['post_status'])) ? $this->data['post_status'] : 'publish';
            /** If the post_type is not set then it is set to the type_name parameter */
            $this->data['post_type'] = (isset($this->data['post_type'])) ? $this->data['post_type'] : $this->meta_information['object_name'];
            /** If the post_type is empty then it is set to post */
            $this->data['post_type'] = ($this->data['post_type'] == '') ? 'post' : $this->data['post_type'];
            /** If the post title is not set then it is set to the string 'title' */
            $this->data['post_title'] = (isset($this->data['post_title'])) ? $this->data['post_title'] : 'title';
            /** If the post content is not set then it is set to the post title */
            $this->data['post_content'] = (isset($this->data['post_content'])) ? $this->data['post_content'] : $this->data['post_title'];
            /** If the post excerpt is not set then it is set to the post title */
            $this->data['post_excerpt'] = (isset($this->data['post_title'])) ? $this->data['post_title'] : ' ';
            /** The post is added to database */
            $insert_post_result = wp_insert_post($this->data, true);
            /** If the result of adding the new post is a WP_Error object then an exception is thrown */
            if (is_wp_error($insert_post_result)) 
            {
                throw new \Exception("New post could not be added. Details: " . $insert_post_result->get_error_message());
            }
            /** If the post was successfully added then the post id is set */
            else $post_id = $insert_post_result;
            /** Each data field is checked */
            foreach ($this->data as $field_name => $field_value) 
            {
                /** If the field name starts with custom_ then it is considered to be a custom field */
                if (strpos($field_name, "custom_") !== false) 
                {
                    /** The custom_ prefix is removed from field name */
                    $field_name = str_replace("custom_", "", $field_name);
                    /** The custom field is added to the last added post */
                    add_post_meta($post_id, $field_name, $field_value, true);
                }
            }
        }
        /** Otherwise an exception is thrown */
        else throw new \Exception("Invalid type given");
    }
    /**
     * Used to return the custom post name
     *
     * It returns the custom post name from the given short custom post name
     * It fetched the data from application configuration
     *
     * @since 1.0.0
     * @param string $short_name the short custom post name
     *
     * @return string $custom_post_name name of the WordPress custom post
     */
    final public function GetCustomPostByName($short_custom_post_name) 
    {
        /** The data table names are fetched from application configuration */
        $wordpress_custom_post_types = $this->GetConfig("wordpress", "custom_post_types");
        $custom_post_name = $wordpress_custom_post_types[$short_custom_post_name];
        return $custom_post_name;
    }
    /**
     * Used to get the custom post name
     *
     * It gets the custom post name
     *
     * @since 1.0.0
     *
     * @return string $custom_post_name the custom post name
     */
    final public function GetCustomPostName() 
    {
        $custom_post_name = $this->meta_information['data_type'];
        return $custom_post_name;
    }
    /**
     * Used to set the custom post name
     *
     * It sets the custom post name
     *
     * @since 1.0.0
     * @param string $custom_post_name the custom post name
     */
    final public function SetCustomPostName($custom_post_name) 
    {
        $this->meta_information['object_name'] = $custom_post_name;
    }
    /**
     * Used to set the meta information
     *
     * It sets the object_type for the current object
     * object_type indicates the type of WordPress object. e.g post
     * It also sets the custom post name for the WordPress object
     *
     * @since 1.0.0
     * @param mixed $meta_information the meta information to set
     * it is an array with following keys:
     * object_type => the type of WordPress object e.g post
     * data_type => the name of the WordPress object e.g name of custom post
     * key_field => the key field used to search the current object
     */
    final public function SetMetaInformation($meta_information) 
    {
        /** The object type is set */
        $this->meta_information['object_type'] = (isset($meta_information['object_type'])) ? $meta_information['object_type'] : "post";
        /** The custom post name is set */
        if (isset($meta_information['data_type'])) 
        {
            $this->meta_information['object_name'] = $this->GetCustomPostByName($meta_information['data_type']);
        }
        if (isset($meta_information['key_field'])) 
        {
            /** The field name for the current object is set */
            $this->SetKeyField($meta_information['key_field']);
        }
    }
    /**
     * Used to transform query parameters so they can be used to fetch WordPress objects using WP_Query
     *
     * It takes relational query parameters
     * It transforms them into a form that can be used with WP_Query
     * If the type of object is a WordPress post, then the parameters are transformed
     * So they search the custom fields of the given custom post
     * The parameters are transformed into arguments for WP_Query
     *
     * @since 1.0.0
     * @param mixed $parameters the parameters used to fetch the data. it contains relational data, it should have following keys:
     * fields => string comma separated list of field names to fetch
     * condition => array the condition used to fetch the data from database
     * it can be a single string or integer value. in this case the previously set field name and table name are used
     * or an array with following fields: field,value,table,operation and operator
     * read_all => bool used to indicate if all data should be returned
     * order => array the sort information
     *     field => string the sort field
     *     direction => string [ASC~DESC] the sort direction
     *
     * @return $transformed_parameters the transformed parameters
     */
    final public function TransformParameters($parameters) 
    {
        /** If the parameters is not an array */
        if (!is_array($parameters['condition'])) 
        {
            /** The search value */
            $value = $parameters['condition'];
            /** The search key */
            $key = $this->GetKeyField();
            /** If the key field was set then it is added to the where clause */
            if ($key != "") 
            {
                /** The where clause used to fetch data from database */
                $where_clause = array(
                    array(
                        'field' => $key,
                        'value' => $value,
                        'operation' => "=",
                        'operator' => ""
                    )
                );
                /** The parameters are updated */
                $updated_parameters = array(
                    "fields" => "*",
                    "condition" => $where_clause,
                    "read_all" => $parameters['read_all']
                );
                /** If the order parameters was given */
                if (isset($parameters['order'])) 
                {
                    $updated_parameters['order'] = $parameters['order'];
                }
                $parameters = $updated_parameters;
            }
        }
        /** If the object type is set to post or it is not set then the WP_Query parameters for custom posts are generated */
        if (!isset($this->meta_information['object_type']) || (isset($this->meta_information['object_type']) && $this->meta_information['object_type'] == 'post')) 
        {
            /** The custom post type. It is fetched from the object's meta information */
            $custom_post_name = $this->meta_information['object_name'];
            /** The custom post meta query */
            $meta_query = array();
            /** The relation between query elements is set if there are more than one query elements */
            if (count($parameters['condition']) > 1) 
            {
                /** The relation between query elements. It is set if it is not empty */
                $meta_query['relation'] = trim($parameters['condition'][0]['operator']);
            }
            /** Each element of the where clause is checked */
            for ($count = 0;$count < count($parameters['condition']) && is_array($parameters['condition']);$count++) 
            {
                /** Single where clause */
                $where_condition = $parameters['condition'][$count];
                /** The operation is trimmed */
                $where_condition['operation'] = trim($where_condition['operation']);
                /** If the operation is LIKE, then the '%' is removed from the operation */
                if ($where_condition['operation'] == 'LIKE') {
                    $where_condition['value'] = str_replace("%", "", $where_condition['value']);
                }
                /** The query field type */
                $field_type = is_numeric($where_condition['value']) ? "NUMERIC" : "CHAR";
                /** The meta query is updated */
                $meta_query = array_merge($meta_query, array(
                    array(
                        'key' => $where_condition['field'],
                        'value' => $where_condition['value'],
                        'compare' => $where_condition['operation'],
                        'type' => $field_type
                    )
                ));
            }
            /** If the order clause is given in parameters then it is used */
            if (isset($parameters['order'])) 
            {
                /** The sort order of the posts */
                $transformed_parameters['orderby'] = ($parameters['order']['type'] == "numeric") ? "meta_value_num" : "meta_value";
                /** The sort order of the posts */
                $transformed_parameters['order'] = $parameters['order']['direction'];
            }
            /** The custom post type */
            $transformed_parameters['post_type'] = $custom_post_name;
            /** The meta query */
            $transformed_parameters['meta_query'] = $meta_query;
            /** If all posts need to fetched */
            if ($parameters['read_all']) 
            {
                $transformed_parameters['posts_per_page'] = '-1';
            }
            else $transformed_parameters['posts_per_page'] = '1';
        }
        return $transformed_parameters;
    }
}

