<?php

namespace IslamCompanionApi\DataObjects;

/**
 * This class implements the Rukus class
 * 
 * An object of this class allows access to Holy Quran rukus
 * The rukus can be fetched using different criteria
 * For example set of rukus in given sura and division
 * 
 * @category   IslamCompanionApi
 * @package    DataObjects
 * @author     Nadir Latif <nadir@pakjiddat.com>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @version    1.0.0
 */
final class Rukus extends \Framework\Object\DataObjectAbstraction
{
	/**
     * Used to get the rukus in the given sura, division 
     * 
     * It fetches the list of rukus for the given sura and division number
	 * If the division is ruku then all rukus are fetched
	 * 
	 * @param string $sura the sura
	 * @param string $division the division name	
	 * @param string $division_number the division number		
	 * 
	 * @param array $ruku_list the list of rukus for given sura and division number. it is an array with 2 keys:
	 * id => ruku id
     */
    public function GetRukusInDivision($sura,$division,$division_number)
	{
		/** The ruku data */
		$ruku_data                             = array();		
		/** The application configuration is fetched */
		$configuration                         = $this->GetConfigurationObject();				
		/** The meta information used to fetch data */
		$meta_information                      = array("data_type"=>"meta","key_field"=>"");
		/** The table name and field name are set */
		$this->SetMetaInformation($meta_information);		
		/* If the division is not ruku then all rukus in given sura and division are fetched */
		if ($division != "ruku") {
		   $where_clause                       = array(
		                                          array('field'=>$division,'value'=>$division_number,'operation'=>"=",'operator'=>"AND"),
		                                          array('field'=>"sura",'value'=>$sura,'operation'=>"=",'operator'=>"")
           );					
		}
		/** If the division is ruku then all rukus in given sura are fetched */
		else {
			/** The where clause used to fetch division data from database */
            $where_clause                      = array(
								                  array('field'=>"sura",'value'=>$sura,'operation'=>"=",'operator'=>"")								                  								                  								      					    
		    );
		}
		/** The parameters used to read the data from database */
		$parameters              			   = array("fields"=>"DISTINCT(sura_ruku),ruku",
		                                               "condition"=>$where_clause,"read_all"=>true,
		                                               "order"=>array("field"=>"sura_ruku","type"=>"numeric","direction"=>"ASC")
											 	 );     					
		/** The meta data is read */
		$this->Read($parameters);	
		/** The ruku data is fetched */
		$data                                  = $this->GetData();
		/** The ruku data */
		for ($count = 0; $count < count($data); $count++) {
		    /** The ruku id */
		    $ruku_id                           = $data[$count]['ruku'];
			/** The sura ruku id */
		    $sura_ruku_id                      = $data[$count]['sura_ruku'];
			/** The ruku data */
		    $ruku_data[]                       = array("id"=>$ruku_id,"sura_ruku"=>$sura_ruku_id);
		}

		return $ruku_data;
	}
	
	/**
     * Used to get the total rukus in given sura
     * 
     * It fetches the total number of rukus in given sura
	 * 
	 * @param string $sura the sura	
	 * 
	 * @return int $total_rukus the total number of rukus in the sura
     */
    public function GetMaxRukus($sura)
	{
		/** The total number of rukus */
		$total_rukus                           = -1;		
		/** The application configuration is fetched */
		$configuration                         = $this->GetConfigurationObject();				
		/** The meta information used to fetch data */
		$meta_information                      = array("data_type"=>"sura","key_field"=>"");
		/** The table name and field name are set */
		$this->SetMetaInformation($meta_information);		       
		/** The where clause used to fetch division data from database */
        $where_clause                          = array(array('field'=>"sindex",'value'=>$sura,'operation'=>"=",'operator'=>""));
		/** The parameters used to read the data from database */
		$parameters                            = array("fields"=>"rukus", "condition"=>$where_clause,
													  "read_all"=>false, "order"=> false);
		/** The ayat information is read */
		$this->Read($parameters);
		/** The meta data is read */
		$meta_data                             = $this->GetData();
		/** The total ruku count */
		$total_rukus                           = $meta_data['rukus'];
		
		return $total_rukus;
	}
	
    /**
     * Used to get the start and end ayas of the current ruku
     * 
     * It fetches the start and end ayat values of the current ruku
     * 
     * @param string $ruku the ruku
     * 
     * @return array $ayat_information an array with 2 keys:
     *    start_ayat => int the start ruku ayat
     *    end_ayat => int the end ruku ayat
    */
    public function GetStartAndEndAyatOfRuku($ruku)
	{
		/** The total number of rukus */
		$total_rukus                           = array();		
		/** The application configuration is fetched */
		$configuration                         = $this->GetConfigurationObject();				
		/** The meta information used to fetch data */
		$meta_information                      = array("data_type"=>"meta","key_field"=>"");
		/** The table name and field name are set */
		$this->SetMetaInformation($meta_information);		       
		/** The where clause used to fetch division data from database */
        $where_clause                          = array(array('field'=>"ruku",'value'=>$ruku,'operation'=>"=",'operator'=>""));		
		/** The parameters used to read the data from database */
		$parameters                           = array("fields"=>"sura_ayat_id,ruku",
		                                              "condition"=>$where_clause,"read_all"=>true,
		                                              "order"=>array("field"=>"sura_ayat_id","type"=>"numeric","direction"=>"ASC")
												);     					
		/** The ayat information is read */
		$this->Read($parameters);		
		/** The meta data is read */
		$meta_data                             = $this->GetData();
		/** The start ayat of the sura ruku */
		$start_ayat                            = $meta_data[0]['sura_ayat_id'];
		/** The end ayat of the sura ruku */
		$end_ayat                              = $meta_data[(count($meta_data)-1)]['sura_ayat_id'];
		/** The start and end ayat information */
		$ayat_information                      = array("start_ayat"=>$start_ayat,"end_ayat"=>$end_ayat);
			
		return $ayat_information;
	}

	/**
     * Used to get the ruku id for the given sura and ayat
     * 
     * It fetches the ruku id of the ruku for the given sura and ayat
	 * 
	 * @param string $sura the sura id
	 * @param string $ayat the ayat sura id
	 * 
	 * @return int $ruku the ruku id
	 */
    public function GetRukuId($sura, $ayat)
	{
		/** The application configuration is fetched */
		$configuration                         = $this->GetConfigurationObject();				
		/** The meta information used to fetch data */
		$meta_information                      = array("data_type"=>"meta","key_field"=>"id");
		/** The table name and field name are set */
		$this->SetMetaInformation($meta_information);		       
		/** The where clause used to fetch division data from database */
        $where_clause                          = array(
                                                        array('field'=>"sura",'value'=>$sura,'operation'=>"=",'operator'=>"AND"),
                                                        array('field'=>"sura_ayat_id",'value'=>$ayat,'operation'=>"=",'operator'=>""),
												);		
		/** The parameters used to read the data from database */
		$parameters                           = array("fields"=>"ruku","condition"=>$where_clause,"read_all"=>false);     					
		/** The meta information is read */
		$this->Read($parameters);	
		/** The meta data is read */
		$meta_data                             = $this->GetData();		
		/** The ruku id */
		$ruku                                  = $meta_data['ruku'];

		return $ruku;
	}
}
