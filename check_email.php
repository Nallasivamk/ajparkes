<?php
require('includes/configure.php');
require('includes/filenames.php');
require('includes/database_tables.php');
require('includes/functions/database.php');
require('includes/functions/general.php');
require('includes/functions/html_output.php');
tep_db_connect();
$opt = "true";

if (isset($_REQUEST['customers_email_address'])){

    $email = $_REQUEST['customers_email_address'];
	$cID = (int)$_REQUEST["cID"];	
	
	if(!empty($email)) {
		
		$email_query = tep_db_query("select customers_email_address from customers where customers_email_address = '" . $email . "'");		
		$email_add_query = tep_db_query("select entry_email_address from address_book where entry_email_address = '" . $email . "'");		
		
		if(tep_db_num_rows($email_query)>0 || tep_db_num_rows($email_add_query)>0) {
			$opt = 	"false";
		} 
		
		if($cID>0) {
			
			$email_contact_query = tep_db_query("select entry_email_address from address_book where customers_id = '".$cID."' AND entry_email_address = '" . $email . "'");		
			if(tep_db_num_rows($email_contact_query)>0) {
				$opt = 	"true";
			} 
			
		}
		
    }
	//echo json_encode(array("returnValue"=>$opt)); 	
}
echo $opt;
?>