<?php
require('includes/configure.php');
require('includes/filenames.php');
require('includes/database_tables.php');
require('includes/functions/database.php');
require('includes/functions/general.php');
require('includes/functions/html_output.php');
tep_db_connect();



    $term = tep_db_prepare_input($_POST['term']);
	$relation = tep_db_prepare_input($_POST['relation']);
	
	if($relation=="Accounts") {
		
		$qry = "SELECT customers_id as id, CONCAT(entry_firstname, ' ', entry_lastname) AS customers_name FROM address_book WHERE (entry_firstname like '%".$term."%' OR entry_lastname like '%".$term."%' OR entry_email_address like '%".$term."%' OR customers_id like '%".$term."%') LIMIT 0,10";
		
	} else if($relation=="Orders") { 
		$qry = "SELECT orders_id as id, customers_name FROM orders WHERE (orders_id like '%".$term."%' OR customers_name like '%".$term."%') LIMIT 0,10";
	}
	
	$result = array();
	$rst = tep_db_query($qry);
	while($arr = tep_db_fetch_array($rst)) {
		$result[] = array("label" => $arr["id"], "value" => $arr["id"]);
	}

/*	
	$companies = array(
	array( "label" => "JAVA", "value" => "1" ),
	array( "label" => "DATA IMAGE PROCESSING", "value" => "2" ),
	array( "label" => "JAVASCRIPT", "value" => "3" ),
	array( "label" => "DATA MANAGEMENT SYSTEM", "value" => "4" ),
	array( "label" => "COMPUTER PROGRAMMING", "value" => "5" ),
	array( "label" => "SOFTWARE DEVELOPMENT LIFE CYCLE", "value" => "6" ),
	array( "label" => "LEARN COMPUTER FUNDAMENTALS", "value" => "7" ),
	array( "label" => "IMAGE PROCESSING USING JAVA", "value" => "8" ),
	array( "label" => "CLOUD COMPUTING", "value" => "9" ),
	array( "label" => "DATA MINING", "value" => "10" ),
	array( "label" => "DATA WAREHOUSE", "value" => "11" ),
	array( "label" => "E-COMMERCE", "value" => "12" ),
	array( "label" => "DBMS", "value" => "13" ),
	array( "label" => "HTTP", "value" => "14" )
	
);

$result = array();
foreach ($companies as $company) {
	$companyLabel = $company[ "label" ];
	if ( strpos( strtoupper($companyLabel), strtoupper($term) )
	  !== false ) {
		array_push( $result, $company );
	}
}

*/

echo json_encode( $result );

	
/*	
	$opt = '';
	if($cID!="" || $cID!=0) {
		
		$zones_query = tep_db_query("select zone_name from zones where zone_country_id = '" . $cID . "' order by zone_name");
		
		if(tep_db_num_rows($zones_query)>0) {
			
			$disabled ="";
			if($cID=="13") { $disabled ="readonly"; } 
			$opt .= "<select name='state' id='state' ".$disabled." style='width:200px;'>";
			
			while ($zones_values = tep_db_fetch_array($zones_query)) {
	
			  //$zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
			  $opt .= '<option value="'.$zones_values['zone_name'].'"> '.$zones_values['zone_name'].' </option>';
	
			}
	
			$opt .= "</select> <span class='inputRequirement'>*</span>";
			
		} else {
		
			$opt .= '<input type="text" name="state" id="state"> <span class="inputRequirement">*</span>';
			
		}

    } else {
		
		 $opt .= '<input type="text" name="state" id="state"> <span class="inputRequirement">*</span>';

    }
	
	echo json_encode(array("returnValue"=>$opt)); 
*/
?>
