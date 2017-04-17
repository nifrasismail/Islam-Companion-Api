<?php

namespace IslamCompanionApi\DataObjects;

/**
 * This class implements the Holy Quran class
 * 
 * An object of this class provides access to Holy Quran data 
 * 
 * @category   IslamCompanionApi
 * @package    DataObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class HolyQuran extends \Framework\Object\DataObjectAbstraction
{
	/**
     * The maximum number of divisions for each division type
     */
    private static $max_division_count  = array("hizb"=>240,"juz"=>30,"manzil"=>7,"page"=>604,"ruku"=>556,"sura"=>114,"ayas"=>6236,"author"=>"111");

	/**
     * Used to determine if the given division name is valid
     * 
     * It checks if the given division name is valid
	 * 
	 * @param string $division_number the required division number
	 * @param string $division the required division name
	 * @throws object Exception an exception is thrown if the given division number is not valid
	 * 
	 * @return boolean $is_valid returns true if the division name is valid for the given division
     */
    public static function IsValidDivision($division_number,$division)
	{
		/** Used to indicate if division number is valid */
		$is_valid           = false;
		/** The maximum division number for the division */
		$max_division_count = self::$max_division_count[$division];		
		/** If the required sura number is less than 1 or greater than the maximum sura count then an exception is thrown */ 
		if($division_number < 1 || $division_number > $max_division_count)
		    throw new \Exception("Invalid division number: ".$division_number." of division: ".$division);
		
		$is_valid           = true;
		
		return $is_valid;
	}
	
	/**
     * Used to return the max division count
     * 
     * It returns the max_division_count property
	 * 
	 * @param string $division the required division name
	 * 
	 * @return int $max_division_count the maximum number of divisions for the given division
     */
    public static function GetMaxDivisionCount($division)
	{
		$max_division_count = self::$max_division_count[$division];
		
		return $max_division_count;
	}
}