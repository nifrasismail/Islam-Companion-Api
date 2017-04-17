<?php

namespace IslamCompanionApi\Test;

/**
 * This class implements the functional testing class
 *
 * It is derived from the framework testing class
 *
 * @category   Framework
 * @package    Testing
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
class Testing extends \Framework\Testing\Testing
{
    /**     
     * This function provides the data to be tested
     * It may be overriden by child classes
     *
     * It returns the data from the given output data that needs to be tested
     *
     * @param mixed $data the data that needs to be tested
     * @param array $test_data the test data
     *
     * @return $data_to_test the data to be tested by the validator
     */
    protected function GetDataToTest($data, $test_data) 
    {
        /** If the output data is a string */
        if (is_string($data)) {
            /** The data to test */
            $data_to_test  = json_decode($data, true);
        }
        else {
            $data_to_test  = $data;
        }
        /** The data is added to array if it is not part of data array */
        if (!isset($data_to_test['data']))
            $data_to_test = array("data" => $data_to_test);
        /** The data to test is set to the html */
        $data_to_test  = (isset($data_to_test['data']['html'])) ? $data_to_test['data']['html']: $data_to_test['data'];
        /** If the html does not have the html5 <!DOCTYPE html> text and the type of markup is html5 then the html is inserted into a base page template */
        if (strpos($data_to_test, "<!DOCTYPE html>") === false) {
            $data_to_test  = $this->InsertHtmlToTemplate($data_to_test, "template");
        }
        /** The language tag is added to the output data */
        $data_to_test  = str_replace("<html>", "<html lang='en'>", $data_to_test);

        return $data_to_test;
    }
}

