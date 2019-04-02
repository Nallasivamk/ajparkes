<?php
/*
  $Id: create_order.php,v 1.2 2004/03/05 00:36:41 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License

*/

require('includes/application_top.php');
require(DIR_WS_FUNCTIONS . 'c_orders.php');

include('includes/classes/admin.php');  
$admin = new admin();

  require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();
  
$cID = (isset($_GET['Customer']) ? $_GET['Customer'] : '');

$setAsQuote = (isset($_GET['quote']) ? $_GET['quote'] : '');

$customers = new customers($cID);


$customers_access_group_id = $customers->get_customers_access_group($cID);
$contacts_arr = $customers->get_customers_all_contacts($cID);
$default_address_id = $customers->get_customers_default_address($cID);

$default_contact = $contacts_arr[$default_address_id];

//print_r($customers->customers[$cID]);

$current_admin_id = $_SESSION['login_id'];

$action = "";

if (isset($_GET['action'])) {
      $action = $_GET['action'] ;
}else if (isset($_POST['action'])){
      $action = $_POST['action'] ;
}

if(tep_not_null($action)) {
	
	if($action=="create_order_confirm") {
		
		$format_id = "1";
		$currency_value = $currencies->currencies[DEFAULT_CURRENCY]['value'];
		
		$default_orders_status_id = DEFAULT_ORDERS_STATUS_ID;
		$quote = (isset($_POST['setAsQuote']) ? $_POST['setAsQuote'] : '');
		if($quote==1) {
			$default_orders_status_id = "100005";
		}
		
		$sales_consultant_admin_id = $_SESSION['login_id'];
		$order_assigned_to = $admin->get_admin_name($sales_consultant_admin_id);
		$order_assigned_to_email = $admin->get_admin_email_address($sales_consultant_admin_id);

		$c_country = tep_get_country_name(tep_db_input(stripslashes($_POST['c_customer_country_id'])));
		$b_country = tep_get_country_name(tep_db_input(stripslashes($_POST['b_customer_country_id'])));
		$s_country = tep_get_country_name(tep_db_input(stripslashes($_POST['s_customer_country_id'])));
	
		$sql_data_array = array('customers_id' => $cID,
				  'customers_name' => tep_db_input(stripslashes($_POST['c_customer_name'])),
				  'customers_company' => tep_db_input(stripslashes($_POST['c_customer_company'])),
				  'customers_street_address' => tep_db_input(stripslashes($_POST['c_customer_street_address'])),
				  'customers_suburb' => tep_db_input(stripslashes($_POST['c_customer_suburb'])),
				  'customers_city' => tep_db_input(stripslashes($_POST['c_customer_city'])),
				  'customers_postcode' => tep_db_input($_POST['c_customer_postcode']),
				  'customers_state' => tep_db_input(stripslashes($_POST['c_customer_state'])),
				  'customers_country' => $c_country,
				  'customers_telephone' => tep_db_input($_POST['c_customer_telephone']),
				  'customers_email_address' => strtolower(tep_db_input($_POST['c_customer_email_address'])),
				  'customers_address_format_id' => $format_id,
				  'customers_address_book_id' => tep_db_input($_POST['c_address_book_id']),
				  'delivery_name' => tep_db_input(stripslashes($_POST['s_customer_name'])),
				  'delivery_company' => tep_db_input(stripslashes($_POST['s_customer_company'])),
				  'delivery_street_address' => tep_db_input(stripslashes($_POST['s_customer_street_address'])),
				  'delivery_suburb' => tep_db_input(stripslashes($_POST['s_customer_suburb'])),
				  'delivery_city' => tep_db_input(stripslashes($_POST['s_customer_city'])),
				  'delivery_postcode' => tep_db_input($_POST['s_customer_postcode']),
				  'delivery_state' => tep_db_input(stripslashes($_POST['s_customer_state'])),
				  'delivery_country' => $s_country,
				  'delivery_address_format_id' => $format_id,
				  'delivery_address_book_id' => tep_db_input($_POST['s_address_book_id']),
				  'billing_name' => tep_db_input(stripslashes($_POST['b_customer_name'])),
				  'billing_company' => tep_db_input(stripslashes($_POST['b_customer_company'])),
				  'billing_street_address' => tep_db_input(stripslashes($_POST['b_customer_street_address'])),
				  'billing_suburb' => tep_db_input(stripslashes($_POST['b_customer_suburb'])),
				  'billing_city' => tep_db_input(stripslashes($_POST['b_customer_city'])),
				  'billing_postcode' => tep_db_input($_POST['b_customer_postcode']),
				  'billing_state' => tep_db_input(stripslashes($_POST['b_customer_state'])),
				  'billing_country' => $b_country,
				  'billing_address_format_id' => $format_id,
				  'billing_address_book_id' => tep_db_input($_POST['b_address_book_id']),
				  'date_purchased' => 'now()',
				  'orders_status' => $default_orders_status_id,
				  'currency' => DEFAULT_CURRENCY,
				  'currency_value' => $currency_value,
				  'sales_consultant_admin_id' => $sales_consultant_admin_id,
				  'order_assigned_to' => $order_assigned_to,
				  'order_assigned_to_email' => $order_assigned_to_email,
				  ); 

		tep_db_perform(TABLE_ORDERS, $sql_data_array);
	  
		$insert_id = tep_db_insert_id();
		
		//Start update customers table for orders count  - Sep 05 2011
		$selcust = tep_db_query("SELECT customers_orders_count FROM customers WHERE customers_id='".$cID."'");
		$cust_arr = tep_db_fetch_array($selcust);
		$cust_order_count = $cust_arr["customers_orders_count"] + 1;
		tep_db_query("UPDATE customers SET customers_orders_count='".$cust_order_count."' WHERE customers_id='".$cID."'"); 
		//End update customers table for orders count  - Sep 05 2011

		$sql_data_array = array('orders_id' => $insert_id,
			  'orders_status_id' => $default_orders_status_id,
			  'admin_users_id' => (int)$current_admin_id,
			  'date_added' => 'now()');
		tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

		$sql_data_array = array('orders_id' => $insert_id,
								'title' => TEXT_GRAND_SUBTOTAL,
								'text' => $temp_amount,
								'value' => "0.00",
								'class' => "ot_grand_subtotal",
								'sort_order' => "1");
		tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);

		$sql_data_array = array('orders_id' => $insert_id,
								'title' => TEXT_SHIPPING,
								'text' => $temp_amount,
								'value' => "0.00",
								'class' => "ot_shipping",
								'sort_order' => "2");
		tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);

		$sql_data_array = array('orders_id' => $insert_id,
								'title' => TEXT_GST_TOTAL,
								'text' => $temp_amount,
								'value' => "0.00",
								'class' => "ot_gst_total",
								'sort_order' => "3");
		tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);

		$sql_data_array = array('orders_id' => $insert_id,
								'title' => TEXT_CUSTOMER_DISCOUNT,
								'text' => $temp_amount,
								'value' => "0.00",
								'class' => "ot_customer_discount",
								'sort_order' => "4");
		tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);

		$sql_data_array = array('orders_id' => $insert_id,
								'title' => TEXT_GRAND_TOTAL,
								'text' => $temp_amount,
								'value' => "0.00",
								'class' => "ot_total",
								'sort_order' => "5");
		tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
	
	  
		tep_redirect(tep_href_link(FILENAME_EDIT_ORDERS, 'oID=' . $insert_id, 'SSL'));
		
	}
	
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html <?php echo HTML_PARAMS; ?>>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">

<title><?php echo TITLE; ?></title>

<script type="text/javascript" src="includes/prototype.js"></script>

<title><?php echo TITLE_1;?></title>

<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">

<link rel="stylesheet" type="text/css" href="includes/javascript/jquery-ui-1.12.0/jquery-ui.css"/>	

<script src="includes/javascript/jquery-ui-1.12.0/external/jquery/jquery.js"></script>

<script language="javascript" src="includes/general.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/css/jquery.ui.combogrid.css" />

<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/js/vendor/jquery-ui.min.js"></script>

<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/js/vendor/jquery.ui.combogrid-1.6.3.js"></script>

<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/js/vendor/jquery.metadata.js"></script>

<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/js/vendor/jquery.validate.min.js"></script>

<script language="javascript">
		function assignCombo(countryId, ref) {
						
				$( "#"+ref+"_customer_postcode" ).combogrid({
					
					//disabled: false,
					autoFocus: true,
					autoChoose: true,
					searchIcon: true,
					showOn: true,
					debug:true,			
					colModel: [{'columnName':'postcode','width':'10','label':'Postcode'}, 
							   {'columnName':'city','width':'45','label':'City'},
							   {'columnName':'state','width':'45','label':'State'}],
					url: '../get_combo_data.php?cid='+countryId,
					sord: "asc", 
					rows: 10,			
					sidx: "Postcode",
					rememberDrag: true,		  
					select: function( event, ui ) {
						$( "#"+ref+"_customer_postcode" ).val( ui.item.postcode );
						$( "#"+ref+"_customer_postcode" ).removeClass("error");
						$('span[for="postcode"]').hide();
						
						$( "#"+ref+"_customer_city" ).val( ui.item.city );
						$( "#"+ref+"_customer_city" ).removeClass("error");				
						$('span[for="city"]').hide();
						
						$( "#"+ref+"_customer_state" ).val( ui.item.state );
						$( "#"+ref+"_customer_state" ).removeClass("error");
						$('span[for="state"]').hide();
						
						$( "#"+ref+"_customer_country_id" ).val( ui.item.country_id );
						$( "#"+ref+"_customer_country_id" ).removeClass("error");
						$('span[for="country"]').hide();
						
						return false;
					},
					
				});
				
		}

		function setFields(postcode, ref){	   
			
			if($( "#"+ref+"_customer_postcode" ).val().length==0){
					$("."+ref+"_combo").val(""); 
			}
				
			$.post(		   
			"../get_details_by_postcode.php", 	   
			{ pCode: postcode }, 		
			function(data) {
				if(data.returnValue==1) {		
					$("."+ref+"_combo").val(""); 
				}
			},	   		
			"json"
			);   
		}
		
		jQuery(document).ready(function($) {
			
			//Assign Combogrid for customers details
			$( "#c_customer_postcode" ).on('keyup', function(){				
				setFields($( "#c_customer_postcode" ).val(), "c");				
			});			
			assignCombo($("select#c_customer_country").val(),"c");
			
			//Assign Combogrid for billing details
			$( "#b_customer_postcode" ).on('keyup', function(){				
				setFields($( "#b_customer_postcode" ).val(), "b");				
			});			
			assignCombo($("select#b_customer_country").val(),"b");
			
			//Assign Combogrid for shipping details
			$( "#s_customer_postcode" ).on('keyup', function(){				
				setFields($( "#s_customer_postcode" ).val(), "s");				
			});			
			assignCombo($("select#s_customer_country").val(),"s");
			
			//Form validation
			$.validator.setDefaults({
				submitHandler: function(form) {					
					form.submit();			
				}
			});		
			$.metadata.setType("attr", "validate");
			
			$("#create_order_confirm").validate({
				
				onfocusout: function(element, event) {
					this.element(element);
				},
				errorElement: "span",	
				onkeyup: false,
				rules: {
					c_customer_name: { required: true, maxlength: 40, minlength: 2 },
					c_customer_email_address: { required: true, email: true },
					c_customer_company: { required: true, maxlength: 30, minlength: 2 },
					c_customer_street_address: { required: true, maxlength: 40, minlength: 5 },
					c_customer_postcode: { required: true, minlength: 4 },
					c_customer_city: { required: true },
					c_customer_state: { required: true },
					c_customer_country: { required: true },
					s_customer_name: { required: true, maxlength: 40, minlength: 2 },
					s_customer_email_address: { required: true, email: true },
					s_customer_company: { required: true, maxlength: 30, minlength: 2 },
					s_customer_street_address: { required: true, maxlength: 40, minlength: 5 },
					s_customer_postcode: { required: true, minlength: 4 },
					s_customer_city: { required: true },
					s_customer_state: { required: true },
					s_customer_country: { required: true },
					sales_consultant_admin_id: {required: true }
					
				},
				messages: {            
					c_customer_name: { required: "Please enter customer name", maxlength: "Customers name should not exceeds 40 characters" },
					c_customer_email_address: { required: "Please enter Email address", email: "Email address must be valid", remote: "Email Address already exists." },
					c_customer_company: { required: "Please enter Company name", maxlength: "Company name should not exceeds 30 characters" },
					c_customer_street_address: {  required: "Please enter Street address", maxlength: "Street Address should not exceeds 40 characters" },
					c_customer_postcode: { required: "Please enter PostCode", minlength: "PostCode must contain at least 4 characters" },
					c_customer_city: "Please enter City",
					c_customer_state: "Please enter State",
					c_customer_country: "Please enter Country",
					s_customer_name: { required: "Please enter customer name", maxlength: "Customers name should not exceeds 40 characters" },
					s_customer_email_address: { required: "Please enter Email address", email: "Email address must be valid", remote: "Email Address already exists." },
					s_customer_company: { required: "Please enter Company name", maxlength: "Company name should not exceeds 30 characters" },
					s_customer_street_address: {  required: "Please enter Street address", maxlength: "Street Address should not exceeds 40 characters" },
					s_customer_postcode: { required: "Please enter PostCode", minlength: "PostCode must contain at least 4 characters" },
					s_customer_city: "Please enter City",
					s_customer_state: "Please enter State",
					s_customer_country: "Please enter Country",
					sales_consultant_admin_id: "Please select sales consultant"
				}
				
			});	
			//Form validation end
			
			$("#c_address_book_id").change(function(){
				var aID = $(this).val();
				$.ajax({
				  method: "POST",
				  url: "ajax_get_customer_address.php",
				  data: { "cID": <?php echo $cID; ?>, "aID": aID },
				  dataType: "json"
				})
				  .done(function( data ) {
						$("#c_customer_name").val(data.firstname + " " + data.lastname);
						$("#c_customer_company").val(data.company);
						$("#c_customer_street_address").val(data.street_address);
						$("#c_customer_suburb").val(data.suburb); $("#c_customer_city").val(data.city); 
						$("#c_customer_state").val(data.state); 
						if(data.state.length==0) { $("#c_customer_state").val(data.zone_name);  }					
						$("#c_customer_postcode").val(data.postcode);
						$("#c_customer_country").val(data.country_name);
						$("#c_customer_country_id").val(data.country_id);
						$("#c_customer_telephone").val(data.entry_telephone);
						$("#c_customer_email_address").val(data.entry_email_address);
				  });
			});	

			$("#b_address_book_id").change(function(){
				var aID = $(this).val();
				$.ajax({
				  method: "POST",
				  url: "ajax_get_customer_address.php",
				  data: { "cID": <?php echo $cID; ?>, "aID": aID },
				  dataType: "json"
				})
				  .done(function( data ) {
						$("#b_customer_name").val(data.firstname + " " + data.lastname);
						$("#b_customer_company").val(data.company);
						$("#b_customer_street_address").val(data.street_address);
						$("#b_customer_suburb").val(data.suburb); $("#b_customer_city").val(data.city); 
						$("#b_customer_state").val(data.state); 
						if(data.state.length==0) { $("#b_customer_state").val(data.zone_name);  }	
						$("#b_customer_postcode").val(data.postcode);
						$("#b_customer_country").val(data.country_name);
						$("#b_customer_country_id").val(data.country_id);
				  });
			});	
			
			$("#s_address_book_id").change(function(){
				var aID = $(this).val();
				$.ajax({
				  method: "POST",
				  url: "ajax_get_customer_address.php",
				  data: { "cID": <?php echo $cID; ?>, "aID": aID },
				  dataType: "json"
				})
				  .done(function( data ) {
						$("#s_customer_name").val(data.firstname + " " + data.lastname);
						$("#s_customer_company").val(data.company);
						$("#s_customer_street_address").val(data.street_address);
						$("#s_customer_suburb").val(data.suburb); $("#s_customer_city").val(data.city); 
						$("#s_customer_state").val(data.state); 
						if(data.state.length==0) { $("#s_customer_state").val(data.zone_name);  }	
						$("#s_customer_postcode").val(data.postcode);
						$("#s_customer_country").val(data.country_name);
						$("#s_customer_country_id").val(data.country_id);
				  });
			});	
			
		});
</script>


</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onLoad="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<div id="body">
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="body-table">
  <tr>
    <!-- left_navigation //-->
        <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
    <!-- left_navigation_eof //-->
    <!-- body_text //-->
    <td valign="top" class="page-container">
	
	
	
	
	
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
			<td class="pageHeading">
				<?php echo HEADING_TITLE; ?>
			</td>
        </tr>
		
		<tr>

		<td>
			<?php
			
			$state = $default_contact["state"];
			if(tep_not_null($default_contact["zone_id"])) {
				$state = $default_contact["zone_name"];
			}
			
			echo tep_draw_form('create_order_confirm', "create_order_confirm.php", tep_get_all_get_params(array('action','Customer')) . 'action=create_order_confirm&Customer='.$cID, 'post', 'id="create_order_confirm"', 'SSL');
            
			echo tep_draw_hidden_field('cID', $cID);
			echo tep_draw_hidden_field('setAsQuote', $setAsQuote);
			
			?>
						<table border="0" width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<td><b>&nbsp;</b></td>
								<td><b><?php echo ENTRY_CUSTOMER; ?></b></td>
								<td><b><?php echo ENTRY_SHIPPING_ADDRESS; ?></b></td>
								<td><b><?php echo ENTRY_BILLING_ADDRESS; ?></b></td>								
							</tr>
							<tr>
								<td>
									<table width="100%" border="0" cellspacing="0" cellpadding="2" class="infoBox">										
										<tr><td style="padding:8px;"><b>Contacts: </b></td></tr>
										<tr><td style="padding:8px;"><b><?php echo ENTRY_NAME; ?> </b></td></tr>
										<tr><td style="padding:8px;"><b><?php echo ENTRY_COMPANY; ?>: </b></td></tr>
										<tr><td style="padding:8px;"><b><?php echo ENTRY_CUSTOMER_ADDRESS; ?> </b></td></tr>
										<tr><td style="padding:8px;"><b><?php echo ENTRY_SUBURB; ?> </b></td></tr>
										<tr><td style="padding:8px;"><b><?php echo ENTRY_POST_CODE; ?> </b></td></tr>
										<tr><td style="padding:8px;"><b><?php echo ENTRY_CITY; ?> </b></td></tr>
										<tr><td style="padding:8px;"><b><?php echo ENTRY_STATE; ?> </b></td></tr>
										<tr><td style="padding:8px;"><b><?php echo "Country"; ?>: </b></td></tr>
									</table>
								</td>
								
								
								<!-- customer details -->
								<td>
									<table border="0" cellspacing="0" cellpadding="2" class="infoBox">										
										<tr>
											<td>
												<select id="c_address_book_id" name="c_address_book_id">
													<option value="">-Select-</option>
													<?php
														if(count($contacts_arr)>0) {
															foreach($contacts_arr as $key=>$val) {
																$selected = ($default_address_id==$key)?"selected":"";
																echo "<option value='".$key."' ".$selected.">".$val["firstname"]." ".$val["lastname"]."</option>";
															}
														}
													?>
												</select>
											</td>
										</tr>
										<tr>
										  <td><input name='c_customer_name' id='c_customer_name' size='37' value='<?php echo tep_html_quotes($default_contact["firstname"] . " " . $default_contact["lastname"]); ?>'></td>
										</tr>										
										
										<tr>
											<td><input name='c_customer_company' id='c_customer_company' size='37' value='<?php echo tep_html_quotes($default_contact["company"]); ?>'></td>
										</tr>
										<tr>
											<td><input name='c_customer_street_address' id='c_customer_street_address' size='37' value='<?php echo tep_html_quotes($default_contact["street_address"]); ?>' ></td>
										</tr>
										<tr>
											<td><input name='c_customer_suburb' id='c_customer_suburb' size='37' value='<?php echo tep_html_quotes($default_contact["suburb"]); ?>' ></td>
										</tr>
										
										<tr>
											<td><input name='c_customer_postcode' class="c_combo" id='c_customer_postcode' size='5' value='<?php echo$default_contact["postcode"]; ?>' ></td>
										</tr>
										
										<tr>
											<td><input name='c_customer_city' class="c_combo" id='c_customer_city' size='15' value='<?php echo $default_contact["city"]; ?>' > </td>
										</tr>
										<tr>
											<td><input name='c_customer_state' class="c_combo" id='c_customer_state' size='15' value='<?php echo $state; ?>' > </td>
										</tr>
										
										<tr>
											<td>
											<?php 
												echo sbs_get_country_list('c_customer_country_id',$default_contact["country_id"],' id="c_customer_country_id" ') ?>
											</td>
										</tr>
									</table>
								</td>
								
								<td>
									<!-- Shipping address block -->
									<table border="0" cellspacing="0" cellpadding="2" class="infoBox">										
										<tr>
											<td>
												<select id="s_address_book_id" name="s_address_book_id">
													<option value="">-Select-</option>
													<?php
														if(count($contacts_arr)>0) {
															foreach($contacts_arr as $key=>$val) {
																$selected = ($default_address_id==$key)?"selected":"";
																echo "<option value='".$key."' ".$selected.">".$val["firstname"]." ".$val["lastname"]."</option>";
															}
														}
													?>
												</select>
											</td>
										</tr>
										<tr>
										  <td><input name='s_customer_name' id='s_customer_name' size='37' value='<?php echo tep_html_quotes($default_contact["firstname"] . " " . $default_contact["lastname"]); ?>'></td>
										</tr>										
										
										<tr>
											<td><input name='s_customer_company' id='s_customer_company' size='37' value='<?php echo tep_html_quotes($default_contact["company"]); ?>'></td>
										</tr>
										<tr>
											<td><input name='s_customer_street_address' id='s_customer_street_address' size='37' value='<?php echo tep_html_quotes($default_contact["street_address"]); ?>'></td>
										</tr>
										<tr>
											<td><input name='s_customer_suburb' id='s_customer_suburb' size='37' value='<?php echo tep_html_quotes($default_contact["suburb"]); ?>' ></td>
										</tr>
										
										<tr>
											<td><input name='s_customer_postcode' class="s_combo" id='s_customer_postcode' size='5' value='<?php echo $default_contact["postcode"]; ?>'></td>
										</tr>
										
										<tr>
											<td><input name='s_customer_city' class="s_combo" id='s_customer_city' size='15' value='<?php echo $default_contact["city"]; ?>'> </td>
										</tr>
										<tr>
											<td><input name='s_customer_state' class="s_combo" id='s_customer_state' size='15' value='<?php echo $state; ?>'> </td>
										</tr>
										
										<tr>
											<td>											
												<?php 
												echo sbs_get_country_list('s_customer_country_id',$default_contact["country_id"],' id="s_customer_country_id" ') ?>
											</td>
										</tr>
									</table>
								</td>
								
								<td>
									<!-- Billing Address Block -->
									<table border="0" cellspacing="0" cellpadding="2" class="infoBox">										
										<tr>
											<td>
												<select id="b_address_book_id" name="b_address_book_id">
													<option value="">-Select-</option>
													<?php
														if(count($contacts_arr)>0) {
															foreach($contacts_arr as $key=>$val) {
																$selected = ($default_address_id==$key)?"selected":"";
																echo "<option value='".$key."' ".$selected.">".$val["firstname"]." ".$val["lastname"]."</option>";
															}
														}
													?>
												</select>
											</td>
										</tr>
										<tr>
										  <td><input name='b_customer_name' id='b_customer_name' size='37' value='<?php echo tep_html_quotes($default_contact["firstname"] . " " . $default_contact["lastname"]); ?>'></td>
										</tr>										
										
										<tr>
											<td><input name='b_customer_company' id='b_customer_company' size='37' value='<?php echo tep_html_quotes($default_contact["company"]); ?>' ></td>
										</tr>
										<tr>
											<td><input name='b_customer_street_address' id='b_customer_street_address' size='37' value='<?php echo tep_html_quotes($default_contact["street_address"]); ?>' ></td>
										</tr>
										<tr>
											<td><input name='b_customer_suburb' id='b_customer_suburb' size='37' value='<?php echo tep_html_quotes($default_contact["suburb"]); ?>'></td>
										</tr>
										
										<tr>
											<td><input name='b_customer_postcode' class="b_combo" id='b_customer_postcode' size='5' value='<?php echo $default_contact["postcode"]; ?>'></td>
										</tr>
										
										<tr>
											<td><input name='b_customer_city' class="b_combo" id='b_customer_city' size='15' value='<?php echo $default_contact["city"]; ?>'> </td>
										</tr>
										
										<tr>
											<td><input name='b_customer_state' class="b_combo" id='b_customer_state' size='15' value='<?php echo $state; ?>'> </td>
										</tr>
										
										<tr>
											<td>
											<?php 
												echo sbs_get_country_list('b_customer_country_id',$default_contact["country_id"],' id="b_customer_country_id" ') ?>
											</td>
										</tr>
									</table>
									<!-- Billing Address Block -->
								</td>
								
							</tr>
							</table>
							
							<table border="0" width="100%" cellspacing="0" cellpadding="0">
							
							<tr>
								
								<td width="50%">
									
									<table border="0" cellspacing="0" cellpadding="2" class="infoBox" width="100%">
									  <tr>
										<td class="main" width="18%"><b><?php echo ENTRY_TELEPHONE; ?></b></td>
										<td class="main" width="82%"><input id='c_customer_telephone' name='c_customer_telephone' size='15' value='<?php echo $default_contact['entry_telephone']; ?>'></td>
									  </tr>
									  <tr>
										<td class="main"><b><?php echo ENTRY_EMAIL_ADDRESS; ?></b></td>
										<td class="main"><input name='c_customer_email_address' id='c_customer_email_address' size='35' value='<?php echo $default_contact['entry_email_address']; ?>'></td>
									  </tr>
								 
									 <tr>
										<td class="main"><b>Sales Consultant:</b></td>
										<td class="main">
											
											<select name="sales_consultant_admin_id" id="sales_consultant_admin_id">
												<option value=""></option>
												<?php							
												foreach($admin->admin as $key=>$val) {
													$selected = ($current_admin_id==$key)?"selected":"";
													echo "<option value='".$key."' ".$selected.">" . $val["admin_firstname"] . " " . $val["admin_lastname"] . "</option>";
												}
												?>
											</select>
											
										</td>
										
									 </tr>	

									</table>
									
								</td>					
								
							</tr>
														
						</table>
						
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr>
							  <td class="main"><?php echo '<a href="'.tep_href_link(FILENAME_CUSTOMERS, '', 'SSL').'">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
							  <td class="main" align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM); ?></td>
							</tr>
						</table>
						
						</form>
						
				</td>
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
<?php
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>