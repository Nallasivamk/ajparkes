<?php
/*
  $Id: create_order.php,v 1.2 2004/03/05 00:36:41 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License

*/

require('includes/application_top.php');

include(DIR_WS_CLASSES . 'order.php');

require(DIR_WS_FUNCTIONS . 'c_orders.php');

include('includes/classes/admin.php');  

$admin = new admin();

$current_admin_id = $_SESSION['login_id'];

require(DIR_WS_CLASSES . 'currencies.php');

$currencies = new currencies();
  
$oID = (isset($_GET['oID']) ? $_GET['oID'] : '');
$duplicate_comment = (isset($_POST['duplicate_comment']) ? $_POST['duplicate_comment'] : '');

$setAsQuote = (isset($_GET['quote']) ? $_GET['quote'] : '');

$order = new order($oID);
		
		$default_orders_status_id = DEFAULT_ORDERS_STATUS_ID;
		
		$quote = (isset($_POST['setAsQuote']) ? $_POST['setAsQuote'] : '');
		
		if($quote==1) {
			$default_orders_status_id = "100005";
		}
		
		$sales_consultant_admin_id = $_SESSION['login_id'];
		$order_assigned_to = $admin->get_admin_name($sales_consultant_admin_id);
		$order_assigned_to_email = $admin->get_admin_email_address($sales_consultant_admin_id);

		$sql_data_array = array('customers_id' => $order->customer["id"],
				  'customers_name' => tep_db_input(stripslashes($order->customer["name"])),
				  'customers_company' => tep_db_input(stripslashes($order->customer["company"])),
				  'customers_street_address' => tep_db_input(stripslashes($order->customer["street_address"])),
				  'customers_suburb' => tep_db_input(stripslashes($order->customer["suburb"])),
				  'customers_city' => tep_db_input(stripslashes($order->customer["city"])),
				  'customers_postcode' => tep_db_input($order->customer["postcode"]),
				  'customers_state' => tep_db_input(stripslashes($order->customer["state"])),
				  'customers_country' => tep_db_input(stripslashes($order->customer["country"])),
				  'customers_telephone' => tep_db_input($order->customer["telephone"]),
				  'customers_email_address' => tep_db_input(strtolower($order->customer["email_address"])),
				  'customers_address_format_id' => $order->customer["format_id"],
				  'customers_address_book_id' => tep_db_input($order->customer["customers_default_address_id"]),
				  'delivery_name' => tep_db_input(stripslashes($order->delivery["name"])),
				  'delivery_company' => tep_db_input(stripslashes($order->delivery["company"])),
				  'delivery_street_address' => tep_db_input(stripslashes($order->delivery["street_address"])),
				  'delivery_suburb' => tep_db_input(stripslashes($order->delivery["suburb"])),
				  'delivery_city' => tep_db_input(stripslashes($order->delivery["city"])),
				  'delivery_postcode' => tep_db_input($order->delivery["postcode"]),
				  'delivery_state' => tep_db_input(stripslashes($order->delivery["state"])),
				  'delivery_country' => tep_db_input(stripslashes($order->delivery["country"])),
				  'delivery_address_format_id' => tep_db_input($order->delivery["format_id"]),
				  'delivery_address_book_id' => tep_db_input($order->delivery["address_book_id"]),
				  'billing_name' => tep_db_input(stripslashes($order->billing["name"])),
				  'billing_company' => tep_db_input(stripslashes($order->billing["company"])),
				  'billing_street_address' => tep_db_input(stripslashes($order->billing["street_address"])),
				  'billing_suburb' => tep_db_input(stripslashes($order->billing["suburb"])),
				  'billing_city' => tep_db_input(stripslashes($order->billing["city"])),
				  'billing_postcode' => tep_db_input($order->billing["postcode"]),
				  'billing_state' => tep_db_input(stripslashes($order->billing["state"])),
				  'billing_country' => tep_db_input(stripslashes($order->billing["country"])),
				  'billing_address_format_id' => tep_db_input($order->billing["format_id"]),
				  'billing_address_book_id' => tep_db_input($order->billing["address_book_id"]),
				  'date_purchased' => 'now()',
				  'orders_status' => $default_orders_status_id,
				  'currency' => $order->info["currency"],
				  'currency_value' => $order->info["currency_value"],
				  'sales_consultant_admin_id' => $sales_consultant_admin_id,
				  'order_assigned_to' => $order_assigned_to,
				  'order_assigned_to_email' => $order_assigned_to_email,
				  ); 

		tep_db_perform(TABLE_ORDERS, $sql_data_array);
	  
		$insert_id = tep_db_insert_id();
		
		//Start update customers table for orders count  - Sep 05 2011
		$selcust = tep_db_query("SELECT customers_orders_count FROM customers WHERE customers_id='".$order->customer["id"]."'");
		$cust_arr = tep_db_fetch_array($selcust);
		$cust_order_count = $cust_arr["customers_orders_count"] + 1;
		tep_db_query("UPDATE customers SET customers_orders_count='".$cust_order_count."' WHERE customers_id='".$order->customer["id"]."'"); 
		//End update customers table for orders count  - Sep 05 2011
		
		foreach($order->products as $key => $val) {
			
			$order->products[$key]["final_price"] = 0;
			$sql_data_array = array('products_id' => $order->products[$key]["id"], 'orders_id' => $insert_id, 
									'products_model' => $order->products[$key]["model"], 'products_name' => $order->products[$key]["name"], 
									'products_price' => $order->products[$key]["price"], 'final_price' => $order->products[$key]["final_price"], 
									'products_tax' => $order->products[$key]["tax"], 'products_quantity' => $order->products[$key]["qty"], 
									'products_description' => $order->products[$key]["desc"], 'products_purchase_number' => $order->products[$key]["purchase_number"],
									'print_file_name' => $order->products[$key]["print_file_name"], 'badge_comment' => $order->products[$key]["badge_comment"]
								);
			tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
			
			$insert_op_id = tep_db_insert_id();
			$opc_arr = tep_get_products_costs($order->products[$key]["id"]);
			
			$sql_data_array = array('products_id' => $order->products[$key]["id"], 'orders_id' => $insert_id, 'orders_products_id' => $insert_op_id, 'products_quantity' => $order->products[$key]["qty"], 'labour_cost' => $opc_arr["labour_cost"], 'material_cost' => $opc_arr["material_cost"], 'overhead_cost' => $opc_arr["overhead_cost"]);
			tep_db_perform("orders_products_costs", $sql_data_array);
			//print_r($sql_data_array);	
			//echo "<br/>";
			
		}
		
		foreach($order->totals as $key => $val) {
			
			if($order->totals[$key]["class"]=="ot_shipping") {
				$order->totals[$key]["value"] = 0; $order->totals[$key]["text"] = ""; $order->totals[$key]["title"] = "";
			} else if($order->totals[$key]["class"]=="ot_customer_discount") {
				$order->totals[$key]["value"] = 0; $order->totals[$key]["text"] = ""; $order->totals[$key]["title"] = "";
			}
			
			$sql_data_array = array('orders_id' => $insert_id, 
									'title' => $order->totals[$key]["title"], 'text' => $order->totals[$key]["text"], 
									'value' => $order->totals[$key]["value"], 'class' => $order->totals[$key]["class"], 
									'sort_order' => $order->totals[$key]["sort_order"]
								);
			tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
			//print_r($sql_data_array);	
			//echo "<br/>";
		}
		
		$sql_data_array = array('orders_id' => $insert_id,
				  'orders_status_id' => $default_orders_status_id,
				  'comments' => "Duplicated from ".$oID,
				  'admin_users_id' => (int)$_SESSION['login_id'],
				  'date_added' => 'now()');
		tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		
		if(!empty($duplicate_comment)) {
			$sql_data_array = array('orders_id' => $insert_id,
				  'orders_status_id' => $default_orders_status_id,
				  'comments' => $duplicate_comment,
				  'admin_users_id' => (int)$_SESSION['login_id'],
				  'date_added' => 'now()');
			tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		}
	
	tep_redirect(tep_href_link(FILENAME_EDIT_ORDERS, 'oID=' . $insert_id, 'SSL'));
	
exit;