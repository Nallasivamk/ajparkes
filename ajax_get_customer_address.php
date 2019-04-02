<?php
require('includes/configure.php');
require('includes/filenames.php');
require('includes/database_tables.php');
require('includes/functions/database.php');
require('includes/functions/general.php');
require('includes/functions/html_output.php');
include('includes/classes/customers.php'); 

tep_db_connect();

$cID = $_POST["cID"];
$aID = $_POST["aID"];
$customers = new customers($cID);
$address_arr = $customers->get_customers_contact($cID,$aID);
echo json_encode($address_arr);
?>