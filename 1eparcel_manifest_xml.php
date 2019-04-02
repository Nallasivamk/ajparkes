<?php
include('includes/configure.php');
include('includes/filenames.php');
include('includes/database_tables.php');
include('includes/functions/database.php');
include('includes/functions/general.php');        
tep_db_connect();
        
$emID = $_GET["emID"];

$xml = "";

$qry = tep_db_query("select * from eparcel_consignment where manifest_id = '".$emID."'");

if(tep_db_num_rows($qry)>0) {
	
	$m_qry = tep_db_query("select manifest_number from eparcel_manifest where manifest_id = '".$emID."'");
	$m_arr = tep_db_fetch_array($m_qry);
	$date_submitted = date("Y-m-d")."T".date("H:i:s").".0Z"; //2010-01-13T16:30:00.0Z
	$created_datetime = date("Y-m-d")."T".date("H:i:s");
	
	$xml .='<?xml version="1.0" encoding="UTF-8"?>
				<PCMS xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance">
					<SendPCMSManifest>
						<header>
							<TransactionDateTime>'.$date_submitted.'</TransactionDateTime>
							<TransactionId>'.$emID.'</TransactionId>
							<TransactionSequence>'.$emID.'</TransactionSequence>
							<ApplicationId>MERCHANT</ApplicationId>
						</header>
						<body>
							<PCMSManifest>
								<MerchantLocationId>3WY</MerchantLocationId>
								<ManifestNumber>'.$m_arr["manifest_number"].'</ManifestNumber>
								<DateSubmitted>'.$date_submitted.'</DateSubmitted>
								<DateLodged>'.$date_submitted.'</DateLodged>
								';
	
	
	while($carr = tep_db_fetch_array($qry)) {
		
		$ord_qry = tep_db_query("select * from orders where orders_id = '".$carr["orders_id"]."'");
		$ord_arr = tep_db_fetch_array($ord_qry);
		
		// For getting the state code
		$city = $ord_arr["delivery_city"];
		if(empty($city)) {
			$city = ($ord_arr["delivery_suburb"]);
		}	
		
		$delivery_name = $ord_arr["delivery_name"];
		if(empty($delivery_name)) {
			$delivery_name = $ord_arr["delivery_company"];
		}
		
		if(!empty($ord_arr["customers_telephone"])) {
			$delivery_phone = $ord_arr["customers_telephone"];
		}
		
		if(!empty($ord_arr["customers_email_address"])) {
			$delivery_email_address = $ord_arr["customers_email_address"];
		}
						
		$zone_qry = tep_db_query("SELECT DISTINCT state_code FROM postcode WHERE postcode='".tep_db_input(trim($ord_arr["delivery_postcode"]))."' AND ( city LIKE '%".tep_db_input(trim($city))."%' OR  state LIKE '%".tep_db_input(trim($ord_arr["delivery_state"]))."%')"); 
		
		/*
		if($carr["orders_id"]=="49301") {
		echo $carr["orders_id"]."<br>";
		echo "SELECT DISTINCT state_code FROM postcode WHERE postcode='".tep_db_input(trim($ord_arr["delivery_postcode"]))."' AND ( city LIKE '%".tep_db_input(trim($city))."%' OR  state LIKE '%".tep_db_input(trim($ord_arr["delivery_state"]))."%')";
		echo "<br>";
		}
		*/
		
		$zone_arr = tep_db_fetch_array($zone_qry);
		
		$country_code = tep_get_country_code_by_name($ord_arr["delivery_country"]);
		
		if(empty($zone_arr["state_code"])) {
			$zones_query_raw = "select z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id from " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " c where z.zone_country_id = c.countries_id and (z.zone_name LIKE '%".tep_db_input(trim($city))."%' OR z.zone_name LIKE '%".tep_db_input(trim($ord_arr["delivery_state"]))."%') ";
			
			echo $zones_query_raw;
			echo "<br>".$carr["orders_id"];
			exit;
		}
		
						$xml .= "<PCMSConsignment>												
									<ConsignmentNumber>".$carr["consignment_number"]."</ConsignmentNumber>
									<ChargeCode>".$carr["charge_code"]."</ChargeCode>
									<DeliveryName>".htmlentities(cleanInput($delivery_name))."</DeliveryName>
									<EmailNotification>Y</EmailNotification>
									<DeliveryAddressLine1>".cleanInput($ord_arr["delivery_street_address"])."</DeliveryAddressLine1>";
						
						if(!empty($ord_arr["delivery_suburb"])) {
							$xml .= "<DeliveryAddressLine2>".cleanInput($ord_arr["delivery_suburb"])."</DeliveryAddressLine2>";
						}
						
						if(!empty($delivery_phone)) {
							$xml .= "<DeliveryPhoneNumber>".cleanInput($delivery_phone)."</DeliveryPhoneNumber>";
						}
						
						if(!empty($delivery_email_address)) {
							$xml .= "<DeliveryEmailAddress>".str_replace("&","&amp;",$delivery_email_address)."</DeliveryEmailAddress>";
						}
						
						$xml .= "<DeliverySuburb>".cleanInput($ord_arr["delivery_city"])."</DeliverySuburb>
									<DeliveryStateCode>".$zone_arr["state_code"]."</DeliveryStateCode>
									<DeliveryPostcode>".cleanInput($ord_arr["delivery_postcode"])."</DeliveryPostcode>
									<DeliveryCountryCode>".$country_code."</DeliveryCountryCode>
									<IsInternationalDelivery>".$carr["international_delivery"]."</IsInternationalDelivery>
									<ReturnName>AJ PARKES AND CO PTY LTD</ReturnName>
									<ReturnAddressLine1>PO BOX 234</ReturnAddressLine1>
									<ReturnSuburb>SALISBURY</ReturnSuburb>
									<ReturnStateCode>QLD</ReturnStateCode>
									<ReturnPostcode>4107</ReturnPostcode>
									<ReturnCountryCode>AU</ReturnCountryCode>
									<CreatedDateTime>".$created_datetime.".106+11:00</CreatedDateTime>
									<IsSignatureRequired>".$carr["signature_required"]."</IsSignatureRequired>
									<DeliverPartConsignment>".$carr["delivery_part_consignment"]."</DeliverPartConsignment>
									<ContainsDangerousGoods>".$carr["dangerous_goods"]."</ContainsDangerousGoods>
									";
				
			$art_qry = tep_db_query("SELECT * FROM eparcel_article where consignment_id='".$carr["consignment_id"]."' ORDER BY article_id ASC");
				$art_xml ="";
				if(tep_db_num_rows($art_qry)>0) {
					
					while($art_arr = tep_db_fetch_array($art_qry)) {				
						
							$art_xml .=	"<PCMSDomesticArticle>
										<ArticleNumber>".$art_arr["article_number"]."</ArticleNumber>
										<BarcodeArticleNumber>".$art_arr["barcode_number"]."</BarcodeArticleNumber>
										<ActualWeight>".$art_arr["actual_weight"]."</ActualWeight>
										<IsTransitCoverRequired>".$art_arr["transit_cover"]."</IsTransitCoverRequired>
										<ContentsItem/>
									</PCMSDomesticArticle>
									";
					}
				}	
					$xml .= $art_xml . 
							"</PCMSConsignment>
							";
		
			unset($city, $delivery_name, $delivery_phone, $delivery_email_address);
	}
		
	$xml .= 				'</PCMSManifest>
						</body>
					</SendPCMSManifest>
				</PCMS>';
	


	// Enable user error handling 
	libxml_use_internal_errors(true);


	$xml_obj = new DOMDocument(); 
	$xml_obj->loadXML($xml);

	if (!$xml_obj->schemaValidate('./eParcel/eParcel.xsd')) { 
	   echo "Invalid XML..<br>";
	   echo libxml_display_errors(); //defined in general.php
	   echo "<br><textarea style='width:700px; height:300px;'>".$xml."</textarea>";
	   echo '<br><br><a href="https://ajparkes.com.au/admin/eparcel_manifests.php?emID='.$emID.'&osCAdminID='.$_GET['osCAdminID'].'">Back</a>';
	} 
	else { 
	    //echo "validated<p/>"; 
	    //Update PCMS in order to submit to eParcel
		$xml = str_replace('xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"','xmlns="http://www.auspost.com.au/xml/pcms"',$xml);
			
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename="3WY_'.$m_arr["manifest_number"].'_ajparkes.xml "');
		print($xml);
		
	} 

	exit;


	
}
?>