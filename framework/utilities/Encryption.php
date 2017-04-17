<?php
namespace Framework\Utilities;
/**
 * Singleton class
 * Encryption class provides functions related to encryption
 *
 * It includes functions such as encrypting and decrypting text
 *
 * @category   Framework
 * @package    Utilities;
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.1
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class Encryption
{
    /**
     * The single static instance
     */
    protected static $instance;
    /**
     * Holds the key used for encrypting and decrypting text
     */
    private $key;
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists
     * If it does not exist then it is created
     * The instance is returned
     *
     * @return Utilities static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance() 
    {
        if (static ::$instance == null) 
        {
            static ::$instance = new static ();
        }
        return static ::$instance;
    }
    /**
     * Initialize the class and set its properties
     *
     * @param string $name the name of the plugin.
     * @param string $version the version of this plugin.
     */
    public function __construct() 
    {
        # the key should be random binary, use scrypt, bcrypt or PBKDF2 to
        # convert a string into a key
        # key is specified using hexadecimal
        $this->key = pack('H*', "c7ea6ad07a6bb93686bbfb64a592c1c23c6b6e35c17a9ab73ee6b3bc25f4cf08");
    }
    /**
     * Function used to encrypt given text
     *
     * @param string $text the text to encrypt
     *
     * @return string $ciphertext the encrypted text
     */
    public function EncryptText($text) 
    {
        # create a random IV to use with CBC encoding
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        # creates a cipher text compatible with AES (Rijndael block size = 128)
        # to keep the text confidential
        # only suitable for encoded input that never ends with value 00h
        # (because of default zero padding)
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $text, MCRYPT_MODE_CBC, $iv);
        # prepend the IV for it to be available for decryption
        $ciphertext = $iv_size . $iv . $ciphertext;
        # base64 encode the cipher text
        $ciphertext = base64_encode($ciphertext);
        return $ciphertext;
    }
    /**
     * Function used to decrypt given text
     *
     * @param string $ciphertext_base64 the encrypted text
     *
     * @return string $decrypted_string the decrypted text
     */
    public function DecryptText($ciphertext_base64) 
    {
        $ciphertext_dec = base64_decode($ciphertext_base64);
        # retrieves the IV size
        $iv_size = substr($ciphertext_dec, 0, 2);
        # retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
        $iv_dec = substr($ciphertext_dec, 2, $iv_size);
        # retrieves the cipher text (everything except the $iv_size in the front)
        $ciphertext_dec = substr($ciphertext_dec, $iv_size + 2);
        # may remove 00h valued characters from end of plain text
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
        # remove null character from end of string and return the string */
        $decrypted_string = rtrim($plaintext_dec, "\0");
        return $decrypted_string;
    }
    /**
     * Used to encode the given data
     *
     * It first json encodes the data if it is an array
     * Then it applies base64 encoding to the string
     * The resulting string is returned
     *
     * @param mixed $data the data to be encoded
     *
     * @return string $encoded_data the encoded data
     */
    final public function EncodeData($data) 
    {
        /** If the data is an array then it is json encoded */
        if (is_array($data)) 
        {
            $data = json_encode($data);
        }
        /** The data is base64 decoded */
        $encoded_data = base64_encode($data);
        return $encoded_data;
    }
     /**
     * Used to decode the given data
     *
     * It first base64 decodes the string
     * If the resulting string is json encoded then it is json decoded
     *
     * @param string $encoded_data the encoded data
     * @param boolean $force_decoding used to indicate that the data is base64 encoded and should be decoded without checking
     *
     * @return mixed $original_data the original data
     */
    final public function DecodeData($data, $force_decoding=false) 
    {
        /** If the given data string is not base64 encoded then it is returned without decoding */
        if (!$force_decoding && !UtilitiesFramework::Factory("strings")->IsBase64($data)) return $data;
        /** The data is base64 decoded */
        $original_data = base64_decode($data);
        /** If the data is a json string then it is json decoded */
        if (UtilitiesFramework::Factory("strings")->IsJson($original_data)) 
        {
            $original_data = json_decode($original_data, true);
        }
        return $original_data;
    }
    /**
     * Function used to generate random string
     *
     * @param int $string_length the number characters in the generated string
     * @param string $type [alphnum~numeric~alpha] the type of characters to include
     *
     * @return string $random_string the random string
     */
    public function GenerateRandomString($string_length, $type) 
    {
        /** The start asci decimal value for the characters */
        $start = 48;
        /** The end asci decimal value for the characters */
        $end = 126;
        /** The list of characters used to generate the random string */
        $character_list = array();
        /** Each character is added to an array */
        for ($count = $start;$count <= $end;$count++) 
        {
            /** The type of the character */
            $char_type = array();
            if ($count >= 65 && $count <= 90) 
            {
                $char_type[] = "alpha";
                $char_type[] = "alphanumeric";
            }
            if ($count >= 97 && $count <= 122) 
            {
                $char_type[] = "alpha";
                $char_type[] = "alphanumeric";
            }
            if ($count >= 48 && $count <= 57) 
            {
                $char_type[] = "numeric";
                $char_type[] = "alphanumeric";
            }
            /** If the type of the character is valid */
            if (in_array($type, $char_type) || $type == "all") $character_list[] = chr($count);
        }        
        /** The characters are shuffled */
        shuffle($character_list);
        /** A random string is extracted using array_rand function. It returns random array keys */
        $random_string_arr_keys = array_rand($character_list, $string_length);
        /** The random string array values */
        $random_string_arr_values = array();
        /** The random string values are generated */
        for ($count = 0;$count < count($random_string_arr_keys);$count++) 
        {
            $random_string_arr_values[] = $character_list[$random_string_arr_keys[$count]];
        }
        /** The random string is imploded */
        $random_string = implode("", $random_string_arr_values);
        return $random_string;
    }
}

