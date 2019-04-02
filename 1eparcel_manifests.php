<?php
/*
  $Id: orders.php,v 1.2 2004/03/05 00:36:41 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/


 require('includes/application_top.php');
 
  // RCI code start
  echo $cre_RCI->get('global', 'top', false);
  echo $cre_RCI->get('orders', 'top', false); 
  // RCI code eof
   
if (isset($_GET['row_by_page'])) {   $row_by_page = (int)$_GET['row_by_page'];}  
if (isset($_GET['manufacturer'])) {   $manufacturer = (int)$_GET['manufacturer'];} else { $manufacturer = ""; } 
if (isset($_GET['sort_by'])) {   $sort_by = $_GET['sort_by'];}  
if (isset($_GET['page'])) {   $page = $_GET['page'];}


//end page select fix v2.8.2
 ($row_by_page) ? define('MAX_DISPLAY_ROW_BY_PAGE' , $row_by_page ) : $row_by_page = MAX_DISPLAY_SEARCH_RESULTS; define('MAX_DISPLAY_ROW_BY_PAGE' , MAX_DISPLAY_SEARCH_RESULTS );

$xml_error = "";
$xml_succ = "";
if(isset($_GET['submit'])) { 
	
	$emID = $_GET["emID"];

	$xml = "";

	$qry = tep_db_query("select * from eparcel_consignment where manifest_id = '".$emID."' and status='0'");

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
									<MerchantLocationId>'.EPARCEL_MERCHANT_LOCATION_ID.'</MerchantLocationId>
									<ManifestNumber>'.$m_arr["manifest_number"].'</ManifestNumber>
									<DateSubmitted>'.$date_submitted.'</DateSubmitted>
									<DateLodged>'.$date_submitted.'</DateLodged>
									';
		
		$m=0;
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
			$zone_arr = tep_db_fetch_array($zone_qry);
			
			$country_code = tep_get_country_code_by_name($ord_arr["delivery_country"]);
			$xml .= "<PCMSConsignment>												
						<ConsignmentNumber>".$carr["consignment_number"]."</ConsignmentNumber>
						<ChargeCode>".$carr["charge_code"]."</ChargeCode>
						<DeliveryName>".cleanInput($delivery_name)."</DeliveryName>
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
			$m++;
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
		   $xml_error .= "Manifests XML have errors...<br>";
		   $xml_error .= libxml_display_errors(); //defined in general.php		   
		} 
		else {
			
			//Update PCMS in order to submit to eParcel
			$xml = str_replace('xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"','xmlns="http://www.auspost.com.au/xml/pcms"',$xml);
						
			$now = date("Y-m-d H:i:s");
			include('phpseclib1.0.9/Net/SFTP.php');
			
			$eparcel_sftp = "st.auspost.com.au";
			if(EPARCEL_ENVIRONMENT=="test") {
				$eparcel_sftp = "stdev.auspost.com.au";
			} 				
			$sftp = new Net_SFTP($eparcel_sftp);
			
			print_r($sftp);
			
			if (!$sftp->login(EPARCEL_USERNAME, EPARCEL_PASSWORD)) {
				exit('Login Failed');
			} 
			//echo $sftp->pwd() . "<br>";		
			
			if($sftp->put(EPARCEL_MERCHANT_LOCATION_ID.'_'.$m_arr["manifest_number"].'_'.EPARCEL_USERNAME.'.xml', $xml)) {
				$xml_succ .= EPARCEL_MERCHANT_LOCATION_ID.'_'.$m_arr["manifest_number"].'_'.EPARCEL_USERNAME.'.xml' . " submitted to eParcel successfully.";
				tep_db_query("UPDATE eparcel_manifest SET status=1, date_submitted='".$now."', transcation_id='".$emID."', date_lodged='".$now."',	number_of_consignments='".$m."' where manifest_id = '".$emID."'");
				tep_db_query("UPDATE eparcel_consignment SET status=1 where manifest_id = '".$emID."'");
				
				
				//tep_redirect(tep_href_link("eparcel_manifests.php", 'emID=' . $emID, 'SSL'));
				//break;
			} else {
				echo "There was some error occurred.";
			}		
			
			//print_r($sftp->getSFTPErrors());
					
			//tep_redirect(tep_href_link("eparcel_manifests.php", 'emID=' . $emID, 'SSL'));	
		}
   } else { $xml_error .= "Manifest already submitted.."; }
}   




?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<script type="text/javascript" src="includes/prototype.js"></script>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="includes/stylesheet-ie.css">
<![endif]-->
<script language="javascript" src="includes/general.js"></script>
<link type="text/css" rel="StyleSheet" href="includes/helptip.css">
<script type="text/javascript" src="includes/javascript/helptip.js"></script>
<script type="text/javascript" src="includes/javascript/jquery.js"></script>

<script type="text/javascript" src="includes/javascript/thickbox.js"></script>
<link rel="stylesheet" href="includes/javascript/thickbox.css" type="text/css" media="screen" />

<?php 
// rci for javascript include
echo $cre_RCI->get('orders', 'javascript');
?>
<style type="text/css">
.img-data img { vertical-align:middle; margin-right:1px; }
.smallText { padding:5px; }
</style>

<script type="text/javascript">
    
    
    $(document).ready(function() {
        
        $(".checkbox").click(function() {           
            sel = "";                   
            $(".checkbox").each( function() {               
                if($(this).attr("checked")==true) {
                    curval = $(this).val();
                    sel = sel + "_"+curval;
                }
            });
            //alert(sel);
            $("#sel_status").val(sel);
            
            $(".checkbox").each( function() {   
            
                if($(this).attr("checked")==false) {
                    $("#os_all").attr("checked",false);                 
                    return false;
                } else {
                    $("#os_all").attr("checked",true);
                    
                }               
    
            });
                        
                        
        });
        
        //create manifest via AJAX
        $("#smtCreateManif").live("click", function(){            
            emid = $("#new_manifest_number").val();                                  
            $.post(        
            "eparcel_create_manifest.php", 
            { em: emid }, 
            function(data){                 
                if(data.returnValue=="true") {
                    location.reload();    
                } else {
                    $("#rst").html(data.returnValue);
                }                                 
            },          
            "json"
            );   
                     
        });
		
		//alert for submit
		 $("#smtManifest").bind("click", function(){   
			if(confirm("Are you sure want to submit the Manifest to eParcel Secure Transfer?")) {
				return true;
			}
			return false;
		 });
		//remove consignment from Manifest list
		$("#delCons").live("click", function(){            
            cid = $(this).val();  			
            $.post(        
            "eparcel_remove_consignment.php", 
            { em: cid }, 
            function(data){                                 
				if(data==true) {
					alert("Consignment removed from the list.");
                    location.reload();    
                } else {                    
					alert("Error occurred. Can't remove the Consignment.");					
                }                                 
            },          
            "json"
            );   
                     
        });
		
    });
    
</script>

</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<div id="body">
  <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="body-table">
    <tr>
    
    <!-- left_navigation //-->
    <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
    <!-- left_navigation_eof //-->
    <!-- body_text //-->
    <td valign="top" class="page-container">
    <table border="0" width="100%" cellspacing="0" cellpadding="0">

    <?php
      // RCI start
      echo $cre_RCI->get('orders', 'listingtop');
      // RCI eof
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td class="pageHeading">              
                <?php echo HEADING_TITLE; ?>               
              </td>
            </tr>
          </table></td>
      </tr>      
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
				<td valign="top"> 
					<?php
						if(!empty($xml_error)) { echo "<font color='red'>".$xml_error."</font><br>"; } 
						if(!empty($xml_succ)) { echo "<font color='blue'>".$xml_succ."</font><br>"; }
					?>
				</td>
			</tr>
			<tr>
              <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="data-table">
                  <?php
                    $oscid = '&' . tep_session_name() . '=' . $_GET[tep_session_name()];
                    if (isset($_GET['SoID'])) {
                      $oscid .= '&SoID=' . $_GET['SoID'];
                    }
                  ?>
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent">
                        <?php echo " <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=manifest_number ASC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_up.gif', TEXT_SORT_ALL . TABLE_HEADING_MANIFESTS_NUMBER . TEXT_ASCENDINGLY)."</a>
                                 <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'cPath='. $current_category_id .'&sort_by=manifest_number DESC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_down.gif', TEXT_SORT_ALL . TABLE_HEADING_MANIFESTS_NUMBER . TEXT_DESCENDINGLY)."</a>
                                 "  . TABLE_HEADING_MANIFESTS_NUMBER;
                                 
                         ?>
                    </td>
                    <td class="dataTableHeadingContent">
                        <?php echo " <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=date_submitted ASC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_up.gif', TEXT_SORT_ALL . TABLE_HEADING_DATE_SUBMITTED . TEXT_ASCENDINGLY)."</a>
                                 <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=date_submitted DESC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_down.gif', TEXT_SORT_ALL . TABLE_HEADING_DATE_SUBMITTED . TEXT_ASCENDINGLY)."</a>
                                 ".TABLE_HEADING_DATE_SUBMITTED; 
                         ?>
                    </td>
                    <td class="dataTableHeadingContent" align="right">
                        
                        <?php echo " <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=date_lodged ASC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_up.gif', TEXT_SORT_ALL . TABLE_HEADING_DATE_LODGED . TEXT_ASCENDINGLY)."</a>
                                 <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=date_lodged DESC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_down.gif', TEXT_SORT_ALL . TABLE_HEADING_DATE_LODGED . TEXT_DESCENDINGLY)."</a>" . TABLE_HEADING_DATE_LODGED; ?>
                        
                    </td>
                    <td class="dataTableHeadingContent" align="center" width="20%">
                        <?php echo " <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=number_of_consignments ASC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_up.gif', TEXT_SORT_ALL . TABLE_HEADING_NUMBER_OF_MANIFESTS . TEXT_ASCENDINGLY)."</a>
                                 <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=number_of_consignments DESC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_down.gif', TEXT_SORT_ALL . TABLE_HEADING_NUMBER_OF_MANIFESTS . TEXT_DESCENDINGLY)."</a>
                                 " . TABLE_HEADING_NUMBER_OF_MANIFESTS; 
                        ?>
                    </td>
                    <td class="dataTableHeadingContent" align="center" width="10%">
                        <?php echo " <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=status ASC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_up.gif', TEXT_SORT_ALL . TABLE_HEADING_STATUS . ' ' . TEXT_ASCENDINGLY)."</a>
                                 <a href=\"" . tep_href_link( FILENAME_EPARCEL_MANIFESTS_ADMIN, 'sort_by=status DESC&page=' . $page.'&row_by_page=' . $row_by_page)."\" >".tep_image(DIR_WS_IMAGES . 'icon_down.gif', TEXT_SORT_ALL . TABLE_HEADING_STATUS . ' ' . TEXT_DESCENDINGLY)."</a>
                                 " . TABLE_HEADING_STATUS; ?>
                        
                    </td>                    
                  </tr>
                  <?php
    
                 //// control string sort page
                 if ($sort_by && !preg_match('/^[a-z][ad]$/',$sort_by)) $sort_by = 'order by '.$sort_by ;
                
				if(empty($sort_by)) {
					$sort_by = ' order by manifest_id DESC ';
				}
				
				//// define the string parameters for good back preview product
                 $origin = FILENAME_EPARCEL_MANIFESTS_ADMIN."?info_back=$sort_by-$page-$row_by_page";
                //// controle lenght (lines per page)
                 $split_page = $page;
                 if ($split_page > 1) $rows = $split_page * MAX_DISPLAY_ROW_BY_PAGE - MAX_DISPLAY_ROW_BY_PAGE;
                 
      $orders_query_raw = "select * from eparcel_manifest " . $sort_by;    
      $orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    
    while ($orders = tep_db_fetch_array($orders_query)) {
              
       if ((!isset($_GET['emID']) || (isset($_GET['emID']) && ($_GET['emID'] == $orders['manifest_id']))) && !isset($oInfo))         
       {
            $oInfo = new objectInfo($orders);
       }

       if (isset($oInfo) && is_object($oInfo) && ($orders['manifest_id'] == $oInfo->manifest_id)) {
          echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_EPARCEL_MANIFESTS_ADMIN, 'emID=' . $oInfo->manifest_id . '&action=edit&page='.$_GET['page'], 'SSL') . '\'">' . "\n";
       } else {
          echo '        <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_EPARCEL_MANIFESTS_ADMIN, 'emID=' . $orders['manifest_id'].'&page='.$_GET['page'], 'SSL') . '\'">' . "\n";
       }

                $count_cons_qry = tep_db_query("select count(*) as cons_count from eparcel_consignment where manifest_id = '".$orders['manifest_id']."'");
                $count_arr = tep_db_fetch_array($count_cons_qry);
                
                            echo "<td class=\"smallText\" align=\"center\">".$orders['manifest_number']."</td>\n";                            
                                    
                            echo "<td class=\"smallText\" align=\"center\">".$orders['date_submitted']."</td>";
                                    
                            echo "<td class=\"smallText\" align=\"center\">".$orders['date_lodged']."</td>";
                            
                            echo "<td class=\"smallText\" align=\"center\">".$count_arr["cons_count"]."</td>";
                    
                           if ($orders['status'] == '1') {
                               
                                echo "<td class=\"smallText\" align=\"center\">Submitted</td>\n";
                               
                            } else {
                                
                                echo "<td class=\"smallText\" align=\"center\">Not Submitted</td>\n";
                                
                            }

?>
        
                  </tr>
                  <?php
      }  // RCO eof
    
?>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="data-table-foot">
                  <tr>
                    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr>
                          <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                          <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], tep_get_all_get_params(array('page', 'emID', 'action'))); ?></td>
                        </tr>
                      </table></td>
                  </tr>
                  <tr>
                    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr>
                          
                          <?php
                            // RCI code start
                            echo $cre_RCI->get('orders', 'listingbottom');
                            // RCI code eof
                          ?>
                        </tr>
                      </table></td>
                  </tr>
                  <tr>
                      <td align="right">
                          <a href="eparcel_create_manifest_form.php?height=150&width=250" id="badge_help" title="" class="thickbox">                              
                              <?php echo tep_image_button('button_edit_status.gif', "Create Manifests",' id="badge_help" '); ?>  
                          </a>    
                          
                      </td>
                  </tr>
                </table></td>
              <?php
    $heading = array();
    $contents = array();

    //switch ($action) {
              
      //default:
        if (isset($oInfo) && is_object($oInfo)) {
          $heading[] = array('text' => '<b>[' . $oInfo->manifest_id . ']&nbsp;&nbsp;' . $oInfo->manifest_number . '</b>');          
          // RCO start
          if ($cre_RCO->get('orders', 'sidebarbuttons') !== true) {
                        
            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link("eparcel_list_consignments.php", tep_get_all_get_params(array('emID', 'action','height','width')) .'emID=' . $oInfo->manifest_id . '&action=consignments&height=300&width=600', 'SSL'). '" class="thickbox" title="List of Consignments" >' . tep_image_button('button_edit_order.gif', "View Consignments") . '</a><a href="' . tep_href_link(FILENAME_EPARCEL_MANIFESTS_ADMIN, 'submit=1&emID='.$oInfo->manifest_id, 'SSL') . '">' . tep_image_button('button_invoice.gif', "Submit", ' id="smtManifest" ') . '</a> <a href="' . tep_href_link("eparcel_manifest_xml.php", 'emID='.$oInfo->manifest_id, 'SSL') . '">' . tep_image_button('button_invoice.gif', "Manifest XML") . '</a> ');
          
          }
          // RCO eof        
          // RCI sidebar buttons
          $returned_rci = $cre_RCI->get('orders', 'sidebarbuttons');          
          // RCI sidebar bottom
          $returned_rci = $cre_RCI->get('orders', 'sidebarbottom');
          $contents[] = array('text' => $returned_rci);
        }
        //break;
    //}
    $multiStatusSearch = 1;
    
    //if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
      
      echo '            <td width="25%" valign="top">' . "\n";
      
      if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
        $box = new box;
        echo $box->infoBox($heading, $contents);
      }
           
      echo '            </td>' . "\n";
    //} //hided for showing right panel
    
?>
            </tr>
            
          </table></td>
      </tr>
      <?php
  
  // RCI code start
  echo $cre_RCI->get('global', 'bottom');                                        
  // RCI code eof
?>
    </table>
    </td>
    
    <!-- body_text_eof //-->
    </tr>
    
  </table>
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>