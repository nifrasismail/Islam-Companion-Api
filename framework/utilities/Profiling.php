<?php
namespace Framework\Utilities;
/**
 * Singleton class
 * Profiling class provides functions related to profiling
 *
 * It includes functions for getting the function execution time,
 * stack trace, cpu and memory usage and code coverage data
 *
 * @category   Framework
 * @package    Utilities
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 * @author 	   Nadir Latif <nadir@pakiddat.com>
 */
final class Profiling
{
    /**
     * The single static instance
     */
    protected static $instance;
    /**
     * The start execution time
     */
    private $start_time;
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists
     * If it does not exist then it is created
     * The instance is returned
     *
     * @since 1.0.0
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
     * Main logging function
     * It is used to log the given data
     *
     * It logs the data to the given destination
     *
     * @since 1.0.0
     * @param string $required_data [execution_time] the profiling data that is required
     *
     * @return int $start_time the current unix timestamp in milliseconds
     */
    public function StartProfiling($required_data)
    {
        /** If the execution time is required */
        if (strpos($required_data, "execution_time") !== false) 
        {
            $this->start_time = microtime(true);
        }
        
        return $this->start_time;
    }
    /**
     * Used to get the total execution time
     *
     * It gets the difference between the current time and the start time
     * The time difference is returned
     *
     * @since 1.0.0
     * @return int $execution_time the total execution time in microseconds
     */
    public function GetExecutionTime() 
    {
        /** The total execution time */
        $execution_time = (microtime(true) - $this->start_time);
        
        return $execution_time;
    }
    
    /**
     * Used to return formatted time
     *
     * It returns the time passed since the given time
     *
     * @param int $time the time that needs to be formatted. it should be in unix timestamp format
     *
     * @return string $formatted_time the formatted time
     */
    public function FormatTime($time) 
    {
        /** The number of seconds since the given time */
        $time = (time() - $time);
        /** The unit is set to seconds */
        $unit = "sec";
        /** If the time is larger then 60 seconds then it is converted to minutes */
        if ($time > 60) 
        {
            $time = ceil($time / 60);
            /** The unit is set to minutes */
            $unit = "min";
            /** If the time is larger then 60 minutes then it is converted to hours */
            if ($time > 60) 
            {
                $time = ceil($time / 60);
                /** The unit is set to hours */
                $unit = "hours";
            }
        }
        /** The formatted time */
        $formatted_time = $time . " " . $unit . " ago";
        
        return $formatted_time;
    }
}
