<?php
/*
  $Id: invoice.php,v 1.2 2004/03/13 15:09:11 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
require('includes/application_top.php');

require_once('html2pdf/html2pdf.class.php');

require('includes/languages/english/invoice_pdf.php');

require(DIR_WS_CLASSES . 'currencies.php');

include(DIR_WS_CLASSES . 'order.php');


$currencies = new currencies();

$oID = tep_db_prepare_input($_GET['oID']);

$order = new order($oID);

if(!empty($order->customer["macola_number"])) {
	$customerNumber = $order->customer["macola_number"];
} else {
	$customerNumber = $order->customer['id'];
}

$orders_query = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id = '" . tep_db_input($oID) . "'");
$orders_arr = tep_db_fetch_array($orders_query);


//get eparcel details
$eparcel_qry = tep_db_query("SELECT * FROM eparcel_consignment ec LEFT JOIN eparcel_article ea ON (ec.consignment_id = ea.consignment_id) WHERE ec.orders_id = '".tep_db_input($oID)."'");
$eparcel_arr = tep_db_fetch_array($eparcel_qry);



ob_start();

?>
<style type="text/css">
	.pdf-prod-head { font-weight:bold; font-size:11px; padding:5px; border-bottom:1px solid #EFEFEF; }
	.pdf-prod { padding:5px; border-bottom:1px solid #EFEFEF;font-size:10px; vertical-align:top; }
	.pdf-footer { width:100%; text-align:center; font-size:11px; }
	.pdf-addr-box { vertical-align: top; font-size:12px; height:130px; border: 1px solid #000; border-radius: 2mm; background: #EFEFEF; border-collapse: collapse; padding-top:2mm; padding-left:5px; }
	.pdf-text { text-align:center; font-weight:bold; font-size:12px; }
	.left-line { border-left:1px solid #EFEFEF; }
	.right-line { border-right:1px solid #EFEFEF; }
</style>

<?php 
			$paid_watermark = "";
			if($order->info["is_paid"]==1) { $paid_watermark = " background: #FFF url(./images/watermark_light.png) center center no-repeat; "; }
			
		 ?>		
		 
	<page style="font-size:11px; font-family: Arial;" backleft="5mm" backright="5mm">  

				<table style="width: 100%;" cellspacing="0" cellpadding="0" >
					<tr>
						<td style="width:100%; text-align:center; vertical-align:top">
							
							<table border="0" width="100%" cellspacing="0" cellpadding="2">
								<tr>
									<td style="width:50%; text-align:left; vertical-align:middle;">
										<img src="images/loaded_header_logo.png" style="border:none; width:200px; height:51px;" border='0'>
									</td>
									<td style="width:50%; text-align:right; vertical-align:middle;">
										<img src='images/free-call-small.png' style="border:none;" border='0'>
									</td>
								</tr>
							  
								<tr>
									<td colspan="2" style="width:100%; text-align:right;">
										<font face="Verdana"> <?php echo ENTRY_CUSTOMER_NUMBER. "&nbsp;" . $customerNumber; ?></font>
										<br />
										<?php if(!empty($order->info['order_assigned_to'])) { ?>
										<font face="Verdana"> <?php echo "Sales Consultant:&nbsp;" . $order->info['order_assigned_to']; ?></font>
										<?php } ?>
										<br />
										<?php if(!empty($order->customer['customers_term'])) { ?>
										<font face="Verdana"> <?php echo "Term:&nbsp;" . tep_get_payment_terms_name($order->customer['customers_term'],$languages_id); ?></font>
										<?php } ?>
									</td>									
								</tr>								
							</table>							
							<table style="width:100%;" border="0" cellspacing="0" cellpadding="2">
								<tr>
								  <td style="width:10%;"><hr></td>
								  <td style="width:20%;">
								   <?php  if($order->info['orders_status_number']!=100006) {  ?> 
								  	<em><b><?php echo PRINT_INVOICE_HEADING; ?></b></em>
								   <?php } else { ?>
										<em><b><?php echo "Tax Invoice No. ".tep_db_input($oID); ?></b></em>
								   <?php } ?>
								  </td>
								  <td style="width:70%;"><hr></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
	
	<table align="center" style="width: 100%;" cellspacing="1mm" cellpadding="0">			
		<tr>
			<td style="width: 48%; vertical-align:top;">
				<div class="pdf-addr-box">						
					<div style="font-size:12px; width: 100%; font-weight:bold; padding:4px;"><?php echo ENTRY_SOLD_TO;  ?></div>
					<div style="padding-left:60px;font-size:11px;">
						<?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?>
						<br>
						<?php echo $order->customer['telephone']; ?>
						<br>
						<?php echo $order->customer['email_address']; ?>
					</div>									
				</div>
			</td>
			<td style="width: 3%;">&nbsp;</td>
			<td style="width: 48%; vertical-align:top;">
				<div class="pdf-addr-box">
					<div style="font-size:12px; width: 100%; font-weight:bold;padding:4px;"><?php echo ENTRY_SHIP_TO;  ?></div>
					<div style="padding-left:60px;font-size:11px;">
							<?php 
								echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); 
								echo tep_show_extra_fields($order->customer['id'],$order->delivery['address_book_id'],$order->customer['customers_access_group_id'],$languages_id);
							?>
							
					</div>										
				</div>
			</td>
		</tr>
	</table>
	<br>
	<table align="center" style="width: 97%;" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width:100%;">
				<div style="vertical-align: middle;text-align: center; border: 1px solid #000; border-radius: 1mm; background: #EFEFEF; border-collapse: collapse; padding:1mm; height:30px;">
				<table style="width: 100%;border: none;" cellspacing="0" cellpadding="0">
					<tr>
						
							<td style="width:15%;" class="pdf-text">
								<?php echo PRINT_INVOICE_ORDERNR . " " . tep_db_input($oID); ?>
							</td>
							
						<?php  if($order->info['orders_status_number']!=100006) {  ?>							
							<td style="width:20%;" class="pdf-text">
								<?php echo ENTRY_DATE_PURCHASED . " " . tep_date_aus_format($order->info['date_purchased'],"short"); ?>
							</td>
						<?php } else { ?>										
							<td style="width:20%;" class="pdf-text">
								<?php echo "Invoice Date: " . " " . tep_date_aus_format($order->info['last_modified'],"short"); ?>
							</td>
						<?php } ?>
						<td style="width:15%;" class="pdf-text">
							<?php echo "Due Date: " . " " . tep_date_aus_format($order->info['due_date'],"short"); ?>
						</td>
						<td style="width:25%;" class="pdf-text">
							<?php echo ENTRY_PURCHASE_NUMBER . " " . $order->info['purchase_number']; ?>
						</td>
						<td style="width:25%;" class="pdf-text">
							<?php echo ENTRY_PAYMENT_METHOD . " " . $order->info['payment_method']; ?>
						</td>
					</tr>
				</table>	
				</div>
			</td>
		</tr>
	</table>
	<br>	
	
	<div style="z-index:-1; width:100%; height:355px; <?php echo $paid_watermark; ?>">
		  
		 <table align="center" style='width: 100%;' cellspacing='0' cellpadding='0'>
		  <tr style='background: #EFEFEF;'>
			<td style='width: 6%; text-align:center;' class="pdf-prod-head right-line"> <?php echo TABLE_HEADING_QUANTITY; ?> </td>			
			<td style='width: 38%; text-align:left;' class="pdf-prod-head right-line"> <?php echo TABLE_HEADING_PRODUCTS; ?> </td>			
			<td style='width: 12%; text-align:right;' class="pdf-prod-head right-line"> <?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?> </td>
			<td style='width: 14%; text-align:right;' class="pdf-prod-head right-line"> <?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?> </td>
			<td style='width: 15%; text-align:right;' class="pdf-prod-head right-line"> <?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?> </td>
			<td style='width: 15%; text-align:right;' class="pdf-prod-head"> <?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?> </td>
		  </tr>
		<?php 
		for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
			
			echo '<tr>' . "\n";			
			
			echo '<td style="width: 6%; text-align:center;" class="pdf-prod right-line">' . tep_display_tax_value($order->products[$i]['qty']) . '</td>' . "\n";			
			
			echo '<td style="width: 38%;" class="pdf-prod right-line">' . $order->products[$i]['name'];
						
				//Modified Sep, 09, 2010
				if($order->products[$i]['product_original_final_price'] > 0) {
					$product_original_price = $order->products[$i]['product_original_final_price'];
				} else {
					$product_original_price = $order->products[$i]['final_price'];
				}
							
			echo '</td>' . "\n";
			
			echo '<td style="width: 12%; text-align:right;" class="pdf-prod right-line">' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n";
						
			echo '        <td style="width: 14%; text-align:right;" class="pdf-prod right-line">' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax_rate']), true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
			'        <td style="width: 15%; text-align:right;" class="pdf-prod right-line">' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
			
			'        <td style="width: 15%; text-align:right;" class="pdf-prod">' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax_rate']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n";
			echo '</tr>' . "\n";
		}
		?>
		
			<?php
			if($order->info["price_break_amount"]>0) {
			?>
				<tr>
					<td colspan="6" style="width:100%; text-align:center;">
					<p style="font-weight:bold; font-size:10px;" class="main">
						<?php echo STORE_NAME; ?> Price Break Discount : <br /><font style="color:#0099CC;">you save $<?php echo number_format($order->info["price_break_amount"],2); ?></font>	<br><br>
					</p>
					</td>
				</tr>					
			<?php } ?>
			<tr>
              <td colspan="6" style="width:100%; text-align:right;">
			  	<table cellspacing="0" cellpadding="2" align="right" style="width:100%;">
                 	<?php
					 for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
					   
					    $ot_label = str_replace("11th","<br>11th",$order->totals[$i]['title']);
						$ot_value = $order->totals[$i]['text'];
						$font_size = " font-size:10px; ";
						if($order->totals[$i]['class']=="ot_total") { 
							$ot_label = "<b>".$ot_label."</b>"; 
							$ot_value = "<b>".$order->totals[$i]['text']."</b>";
							$font_size = " font-size:15px; ";
						}
						
					   echo '         <tr>' . "\n" .
							'          <td align="right" style="padding:2px; '.$font_size.'">' . $ot_label . '</td>' . "\n" .
							'          <td align="right" style="padding:2px; '.$font_size.'">' . $ot_value . '</td>' . "\n" .
							'         </tr>' . "\n";
					 }
          			?>
              	</table>
			  </td>
		  </tr>
		  
		 </table>
	&nbsp;</div>
	
		<?php 
		$products_count = sizeof($order->products);
		
		//if(($products_count>=9) && ($products_count<=26) || ($products_count>55) && ($products_count<70)) { 
		
		if(($products_count>=9)) { 
		
			echo '</page><page style="font-size:11px; font-family: Arial;" backleft="5mm" backright="5mm"> '; 
		
		}
		
		?>    

		
		<page_footer>
		
		<!-- eParcel Label Page Start-->	
		
		<table align="left" style="width:100%; vertical-align:bottom;"  cellspacing="0" cellpadding="0">
		 <tr>
		  <td style="width:34%; padding:5px; vertical-align:top;">
			<table style="width:100%" cellspacing="0" cellpadding="0">            
				<tr>
					<td style="width:100%; line-height:14px; background: #EFEFEF; padding:2px 5px;font-size:11px;">
						<div style="vertical-align: middle; border-radius: 1mm; background: #CCC; border-collapse: collapse; padding:5px; margin-bottom:5px;">
							<b>Payment Methods: </b>
						</div>
						<b>Credit Card: </b>Call 1300 267 074 and follow the prompts. <b>Biller ID: 564864</b><br />
						<b>Bank Transfer: </b><br />
						AJ Parkes & Co Pty Ltd<br />
						Westpac: BSB 034-081 Acc 225097<br />
						Please use <?php echo "<b>".$oID."</b>"; ?> as your reference number for all payment methods<br /><br />
						<div style="text-align:center;"><b>AJ Parkes thanks you for your business</b></div>
					</td>
				</tr>
				<tr>
					<td style="width:100%; text-align:center;line-height:14px;font-size:11px;">					
						<br />
						AJ Parkes & Co Pty Ltd - Badges, Medallions, Trophies and Awards<br />
						<b>Free Call: </b> 1800 777 125<br />
						(Australia Only)<br />
						<b>PH: </b> +617 3277 2111<br />
						<b>Fax: </b> +617 3054 1912<br />
						PO Box 3304 - Darra, QLD 4076<br />
						<span style="font-size:18px;">ABN 61127091016</span><br /><br />
						&#169; Copyright <?php echo date('Y'); ?> AJ Parkes & Co Pty Ltd.					
					</td>
				</tr>
			</table>		
		  </td>
		  <td style="width:66%; padding:5px 20px 5px 20px; vertical-align:top;">
			<!-- NEW FORMAT -->
			<table style="width: 100%;" cellspacing="0" cellpadding="0">            
				<tr>
					<td style="width: 60%; vertical-align:top;">                
							<br/>
							<?php 
								if(tep_db_num_rows($eparcel_qry) > 0) { 
									
									if (preg_match("/(express|same|next)/i", $order->info["shipping_method"])) {
										echo '<img src="images/eparcel_express.JPG" style="width:210px; height:50px;">';
										
									} else {
										echo '<img src="images/eparcel_aus.jpg" style="width:210px; height:50px;">';										
									}													
								} else {
									echo '<div style="font-size:18px; padding:2px; background-color:#EE1c25; width:90%; color:#FFFFFF;">
									DELIVER TO:
									</div>';	
								}
								?>								
							<br>
							<div style="padding:2px;font-size:11px;">
								<?php 
									echo strtoupper($orders_arr["delivery_name"]."<br>" .
										 $orders_arr["delivery_company"]."<br>" .
										 $orders_arr["delivery_street_address"])."<br>";
									if(!empty($orders_arr["delivery_suburb"])) {
										echo strtoupper($orders_arr["delivery_suburb"])."<br>";
									}
									echo strtoupper($orders_arr["delivery_city"]." " .
										 $orders_arr["delivery_state"]." ".$orders_arr["delivery_postcode"]); 
								?>                      
							</div>                                                  
					</td>           
					<td style="width: 40%; vertical-align:middle;">             
						<br/><div style="padding:2px; text-align:right;font-size:11px;">
								PHONE: <?php echo $order->customer['telephone']; ?>                        
						</div>                  
					</td>
				</tr>
			</table>			
			<table style="width: 100%; padding:5%;" cellspacing="0" cellpadding="0">
				<tr>
					<td style="width:70%; font-size:11px; padding:5px; border-top:1px solid #CCC;">
						<b>DELIVERY INSTRUCTIONS</b>
					</td>
					<td style="text-align:right; padding:5px; font-size:11px; border-top:1px solid #CCC;">
							<b><?php 
								if(tep_db_num_rows($eparcel_qry) > 0) { echo $eparcel_arr["actual_weight"]."Kg"; }
							?></b>
					</td>
				</tr>
				<tr>
					<td style="width:70%; font-size:11px; padding:5px; border-top: solid 1px #CCC;">
						<?php if(tep_db_num_rows($eparcel_qry) > 0) { 
							if($eparcel_arr["signature_required"]=="Y") { echo "<b>Signature On Delivery Required</b>"; } else { echo "<b>Authority to Leave If Unattended</b>"; }
							echo "<br />";
						} ?>
					</td>
					<td style="border-top: solid 1px #CCC; border-left: solid 1px #CCC; padding:5px;font-size:10px;">
						<?php if(tep_db_num_rows($eparcel_qry) > 0) { 
							
								echo 'CON NO: '.$eparcel_arr["consignment_number"]."<br/><b>PARCEL:</b> 1";
							}
							?>
					</td>
				</tr>
				<tr>
					<td style="width:100%; text-align:center; padding:5px; font-size:11px; border-top:1px solid #CCC; line-height:12px;" colspan="2">
						<?php if(tep_db_num_rows($eparcel_qry) > 0) { 
							
							echo 'AP Article ID: '.$eparcel_arr["article_number"]; 
							
							if(!empty($eparcel_arr["consignment_number"]) && !empty($eparcel_arr["article_number"]) && !empty($eparcel_arr["barcode_number"])) {
							
								if(file_exists("./eParcel/barcode/".$eparcel_arr["article_number"].".gif")) {
									echo '<br><img src="./eParcel/barcode/'.$eparcel_arr["article_number"].'.gif" style="width:300px; height:70px;" />';
								}
							} 
							echo '<br>AP Article ID: '.$eparcel_arr["article_number"]; 
						} 
						?>
					</td>           
				</tr>
			</table>    
			<table style='width: 100%; border-top:1px solid #CCC;' cellspacing='0' cellpadding='0'>
				<tr>
					<td style="width:39%; padding-left:15px; padding:5px;font-size:9.1px; border-left: solid 1px #CCC;  "> 
						<b>SENDER</b><br>
						AJ PARKES AND CO PTY LTD<BR />
						PO BOX 3304<BR />
						Darra, QLD 4076<br/>
						Order number: <?php echo $oID; ?>						
					</td>
					<td style='width:59%; text-align:justify; font-size:9px; padding:5px; border-left: solid 1px #CCC;  '>        
						<b>Aviation Security and Dangerous Goods Declaration</b><br/>
						The sender acknowledges that this article may be carried by air and will be subject to aviation security and clearing procedures and the sender declares that the article does not contain any dangerous or prohibited goods, explosive or incendiary devices. A false declaration is a criminal offence.				                              
					</td>
				</tr>       
			</table>
			<br/><br/>
			<!-- NEW FORMAT -->
		  </td>
		 </tr>
		</table>
		<!-- eParcel Label Page Ends -->
		</page_footer>	
  </page> 
<?php 
	//exit;
	$name ="ajp_bottom_sticker_invoice.pdf";
	$content = ob_get_clean();	
	// encode data (puts attachment in proper format)
	try {
		$html2pdf = new HTML2PDF('P','A4', 'en', false, 'ISO-8859-15');
		$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
		//$pdfdoc = $html2pdf->Output($name, 'S');
		$pdfdoc = $html2pdf->Output($name);
	}
	catch(HTML2PDF_exception $e) { echo $e; }
	//require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>