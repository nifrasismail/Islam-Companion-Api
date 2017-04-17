<?php
namespace Framework\Application;
use \Framework\Configuration\Base as Base;
/**
 * This class implements the CommandLineApplication class
 *
 * Abstract class
 * Must be extended by child class
 * It contains functions that help in constructing command line applications
 *
 * @category   Framework
 * @package    Application
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
abstract class CommandLineApplication extends Application
{
    /**
     * Used to display information on how to use the script
     * {@internal context command line,browser}
     *     
     * It displays script usage information
     *
     * @return string $usage the description of how to run the command line script
     */
    abstract function HandleUsage();
}

