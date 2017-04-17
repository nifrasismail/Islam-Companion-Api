<?php

namespace Framework\Utilities;

/**
 * Singleton class
 * Email class provides functions related to email
 * 
 * It includes functions such as sending email with attachment
 * It uses pear Mail_Mime package (https://pear.php.net/package/Mail_Mime/)
 * And Mail package (https://pear.php.net/package/Mail/) 
 * 
 * @category   Framework
 * @package    Utilities
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.2
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class Email
{
    /**
     * The single static instance
     */
    protected static $instance;
	
    /**
     * Used to return a single instance of the class
     * 
     * Checks if instance already exists
     * If it does not exist then it is created
     * The instance is returned
	 * 
     * @return Utilities static::$instance name the instance of the correct child class is returned 
     */
    public static function GetInstance($parameters)
    {
        try {
            if (static::$instance == null) {
                static::$instance = new static($parameters);
            }
            return static::$instance;
        }
        catch (\Exception $e) {
            throw new \Exception("Error in function GetInstance. Details: " . $e->getMessage(), 60, $e);
        }
    }
	
    /**
     * Sends an email
	 * 
     * @param array $attachment_files an array containing files to be attached with email.
     * @param string $from the sender of the email.
     * @param string $to the reciever of the email.
     * @param string $subject the subject of the email.
     * @param string $message the message of the email.			 			  			  					 
     * @throws Exception throws an exception if the file size is greater than a limit or the file extension is not valid or the uploaded file could not be copied
     * 
     * @return boolean $is_sent used to indicate if the email was sent.
     */
    public function SendEmail($attachment_files, $from, $to, $subject, $text)
    {
        try {
        	/** The email text is encoded */
            $processed = htmlentities($text);
			/** If the encoded text is same as the original text then the text is considered to be plain text */
            if ($processed == $text)
                $is_html = false;
			/** Otherwise the text is considered to be html */
            else
                $is_html = true;
			/** If the attachment files were given */
			if (is_array($attachment_files)) {
				/** Mail_mine object is created */
				$message = new \Mail_mime();
				/** If the message is not html */
	            if (!$is_html)
	                $message->setTXTBody($text);
				/** If the message is html */
	            else
	                $message->setHTMLBody($text);
			    /** Each given file is attached */
	            for ($count = 0; $count < count($attachment_files); $count++) {
	                $path_of_uploaded_file = $attachment_files[$count];
	                if ($path_of_uploaded_file != "")
	                    $message->addAttachment($path_of_uploaded_file);                   
			    }
				/** The message body is fetched */
				$body = $message->get();
				/** The extra headers */
				$extraheaders = array(
	                "From" => $from,
	                "Subject" => $subject,
	                "Reply-To" => $from
            	);
				/** The email headers */
            	$headers      = $message->headers($extraheaders);
			}
			else {
				/** The message body */
			    $body         = $text;
				/** The message headers */
				$headers      = array("From" => $from,"Subject" => $subject,"Reply-To" => $from, "Content-Type" => "text/html");
			}          
            /** The Mail class object is created */
            $mail    = new \Mail("mail");
			/** The email is sent */
            $is_sent = $mail->send($to, $headers, $body);
            
            if (!$is_sent)
                throw new \Exception("Email could not be sent");
            else
                return true;
        }
        catch (\Exception $e) {
            throw new \Exception("Email could not be sent. Details: " . $e->getMessage());
        }
    }
}
