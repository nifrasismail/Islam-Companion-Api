<?php
/**
 * The application bootstrap file
 *
 * This file is the main entry point for the application
 * All url requests to the application are handled by this file
 *
 * @link              http://pakjiddat.com
 * @version           1.1.6
 * @package           Framework
 *
 * Description:       Pak Php Framework
 * Version:           3.0.2
 * Author:            Nadir Latif
 * Author URI:        http://pakjiddat.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pakphp
 */
namespace Framework;
/** The autoload.php file is included */
require ("autoload.php");
/** The application parameters */
$parameters = (isset($argc)) ? $argv : $_REQUEST;
/** The application context is determined */
$context = (isset($parameters['context'])) ? $parameters['context'] : ((isset($argc)) ? "command line" : "browser");
/** The application request is handled */
$output = \Framework\Application\Application::HandleRequest($context, $parameters);
/** If the output is not suppressed then the application output is echoed back */
if (!defined("NO_OUTPUT")) echo $output;

