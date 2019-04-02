<?php
require('includes/application_top.php');
require('includes/classes/class.phpmailer.php');
include('includes/classes/admin.php');
include('includes/classes/customers.php'); 

$admin = new admin();

$cID = (isset($_GET['cID']) ? $_GET['cID'] : '');

$cLink="";

if(tep_not_null($cID)) {
	$cLink = "&cID=".$cID; 
	$customers = new customers($cID);
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$callsID = (isset($_GET['callsID']) ? $_GET['callsID'] : '');

$current_admin_id = $_SESSION['login_id'];

if (tep_not_null($action)) {

	switch ($action) {
		
		case 'insert':
		case 'save':
				$calls_id = tep_db_prepare_input($_POST["calls_id"]);
				
				$customers_id = tep_db_prepare_input($_POST['customers_id']);

				$address_book_id = tep_db_prepare_input($_POST['address_book_id']);
				
				$orders_id = "";
				
				$orders_id = tep_db_prepare_input($_POST['orders_id']);

				$name = tep_db_prepare_input($_POST['name']);

				$status = tep_db_prepare_input($_POST['status']);

				$description = tep_db_prepare_input($_POST['description']);				

				$call_date = tep_db_prepare_input($_POST['call_date']);

				$final_call_date = strtotime($call_date);

				$final_call_date = date("Y-m-d H:i:s", $final_call_date);				

				$start_date = tep_db_prepare_input($_POST['start_date']) . " " . tep_db_prepare_input($_POST['start_date_hours']) . ":" . tep_db_prepare_input($_POST['start_date_minutes']).":00";

				$final_start_date = strtotime($start_date);

				$final_start_date = date("Y-m-d H:i:s", $final_start_date);				

				$duration_hours = tep_db_prepare_input($_POST['duration_hours']);

				$duration_minutes = tep_db_prepare_input($_POST['duration_minutes']);

				$reminder_checked = tep_db_prepare_input($_POST['reminder_checked']);

				$reminder_time = tep_db_prepare_input($_POST['reminder_time']);

				$assigned_user_id = tep_db_prepare_input($_POST['assigned_user_id']);

				$notify_other_user_id = tep_db_prepare_input($_POST['notify_other_user_id']);

				$is_notify_user = tep_db_prepare_input($_POST['is_notify_user']);

				
				$sql_data_array = array('name' => $name,

										'status' => $status,

										'description' => $description,

										'customers_id' => $customers_id,								

										'address_book_id' => $address_book_id,
										
										'orders_id' => $orders_id,

										'duration_hours' => $duration_hours,

										'duration_minutes' => $duration_minutes,

										'date_start' => $final_call_date,

										'date_end' => $final_start_date,

										'reminder_time' => $reminder_time,

										'date_modified'=> 'now()',

										'modified_user_id'=> $current_admin_id,
										
										'notify_other_user_id'=> $notify_other_user_id,

										'assigned_user_id' => $assigned_user_id);

				
				if ($action == 'insert' && empty($callsID)) {
					$insert_sql_data = array('date_entered' => 'now()', 'created_user_id' => $current_admin_id,);
					$sql_data_array = array_merge($sql_data_array, $insert_sql_data);
					
					tep_db_perform(TABLE_CALLS, $sql_data_array);
					
				} elseif ($action == 'save') {
					
					tep_db_perform(TABLE_CALLS, $sql_data_array, 'update', "calls_id = '" . (int)$callsID . "'");
					
				}
				
				if(isset($is_notify_user)) {
					$email_templates = new email_templates();				
					$call_notify_arr = $email_templates->get_email_template(1);
					
					$assigned_to_name = $admin->get_admin_name($assigned_user_id);				
					$assigned_to_email = $admin->get_admin_email_address($assigned_user_id);
					
					$created_admin_name = $admin->get_admin_name($current_admin_id);
					$created_admin_email = $admin->get_admin_email_address($current_admin_id);
					$created_admin_signature = $admin->get_admin_signature($current_admin_id);
					
					$notify_admin_name = $admin->get_admin_name($notify_other_user_id);
					$notify_admin_email = $admin->get_admin_email_address($notify_other_user_id);				
					
					$contacts_name = "Not Available";
					$contacts_email = "Not Available";
					$contacts_phone = "Not Available";
						
					if(tep_not_null($cID)) {
						$contacts_arr = $customers->get_customers_contact($cID,$address_book_id);															
						$contacts_name = $contacts_arr["entry_firstname"] . " " . $contacts_arr["entry_lastname"];
						$contacts_email = $contacts_arr["entry_email_address"];
						$contacts_phone = $contacts_arr["entry_telephone"];
					}
					
					
					$template_search_arr = array("{#assigned_to_name#}", "{#name#}", "{#description#}", "{#call_date#}", "{#start_date#}", "{#duration#}", "{#reminder_time#}", "{#status#}", "{#created_admin_name#}", "{#created_admin_signature#}", "{#customers_id#}", "{#address_book_id#}", "{#orders_id#}", "{#contacts_name#}", "{#contacts_email#}", "{#contacts_phone#}");
					$template_replace_arr   = array($assigned_to_name, $name, $description, $call_date, $start_date, $duration_hours.":".$duration_minutes, $reminder_time, $status, $created_admin_name, $created_admin_signature, $customers_id, $address_book_id, $orders_id, $contacts_name, $contacts_email, $contacts_phone);

					$call_notify_email_subject = str_replace($template_search_arr, $template_replace_arr, $call_notify_arr["email_subject"]);
					$call_notify_email_body = str_replace($template_search_arr, $template_replace_arr, $call_notify_arr["email_body"]);
					
					//Use PHP Mailer to send email - Start
					
					if(!empty($assigned_to_email)) {
						
						$mail = new PHPMailer(); 
						
						$mail->AddReplyTo($created_admin_email,$created_admin_name);
						
						$mail->SetFrom($created_admin_email,$created_admin_name);
						
						$mail->AddAddress($assigned_to_email, $assigned_to_name);
						
						if(!empty($notify_admin_email)) {  $mail->AddAddress($notify_admin_email, $notify_admin_name);  }
						
						$mail->Subject    = $call_notify_email_subject;
						
						//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
						
						$mail->MsgHTML($call_notify_email_body);
									
						//$mail->AddAttachment($files);      // attachment			
						
						$mail->Send();
				
						//Used PHP Mailer to send emails - End	
					
					}
				}
								
				tep_redirect(tep_href_link("calls.php", '', 'SSL'));
				
		break;
		
		case 'deleteconfirm':
			$calls_id = tep_db_prepare_input($_GET['callsID']); 
			tep_db_query("delete from " . TABLE_CALLS . " where calls_id = '" . (int)$calls_id . "'");
			tep_redirect(tep_href_link("calls.php", '', 'SSL'));
		break;  
		
		default:
		
			
				
	}

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>

<head>

	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<title><?php echo TITLE; ?></title>
	<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
	<script src="includes/javascript/jquery-ui-1.12.0/external/jquery/jquery.js"></script>
	
</head>

<body>

	<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

	<table width="100%"  border="0" align="center" cellpadding="2" cellspacing="2">
	
	  <tr>
	  
		<td valign="top" width="150">
		
		  <table border="0" width="150" cellspacing="1" cellpadding="1" class="columnLeft" align="center">
			
			<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
			
		  </table>
		  
		</td>
		  
		<td valign="top">
			
		<div style="float:left; width:95%; padding:10px; min-height:400px; border:1px solid #ccc;">
		  
		<?php
		  
		if ($action == 'view' && tep_not_null($callsID)) {
				
			include_once("calls_view.php");
			
		}  else if ($action == 'edit') { 
			
			include_once("calls_edit.php");
			
		} else if($action == 'new') {
			
			include_once("calls_edit.php");
		
		}  else {
			
			?>
			
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
			  </tr>
			</table>
			
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
			  <tr>
				<td valign="top">
					
					<a class="cssButton" href="<?php echo tep_href_link(FILENAME_CALLS,'action=new'.$cLink,'SSL'); ?>" title="Create Call" style="float:right; clear:both;">Create</a><br/>
					
				 <table border="0" width="100%" cellspacing="0" cellpadding="2" class="data-table">
				  <tr class="dataTableHeadingRow">
<?php

			$listing = (isset($_GET['listing']) ? $_GET['listing'] : '');
			
            switch ($listing) {
				  case "id-asc":
				  $order = "calls_id";
				  break;
				
				  case "name":
				  $order = "name";
				  break;
				  case "name-desc":
				  $order = "name DESC";
				  break;
				  
				  case "de":
				  $order = "date_entered";
				  break;
				  case "de-desc":
				  $order = "date_entered DESC";
				  break;
				  
				  default:
				  $order = "calls_id DESC";
            }
          
?>
                <td class="dataTableHeadingContent" valign="top" width="120"><a href="<?php echo tep_href_link(FILENAME_CALLS,'listing=name'); ?>"><img src="images/arrow_up.gif" border="0"></a>&nbsp;<a href="<?php echo tep_href_link(FILENAME_CALLS,'listing=name-desc'); ?>"><img src="images/arrow_down.gif" border="0"></a><br><?php echo TABLE_HEADING_NAME; ?></td>
				
				<td class="dataTableHeadingContent" valign="top"><a href="<?php echo tep_href_link(FILENAME_CALLS,'listing=de'); ?>"><img src="images/arrow_up.gif" border="0"></a>&nbsp;<a href="<?php echo tep_href_link(FILENAME_CALLS,'listing=de-desc'); ?>"><img src="images/arrow_down.gif" border="0"></a><br><?php echo TABLE_HEADING_DATE_CREATED; ?></td>
				
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_CREATED_BY; ?></td>
				
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_ASSIGNED_TO; ?></td>
              
			    <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_START_DATE; ?></td>
				
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_END_DATE; ?></td>
												
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_CALL_DURATION; ?></td>
				
				<td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_STATUS; ?></td>
				 
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
			
				<?php
  
    
				$search = '';
				
				if (tep_not_null($cID)) {
				  $search = " where customers_id = '" . $cID . "' ";
				}
	
				$keywords_de = isset($_GET['de']) ? tep_db_input(tep_db_prepare_input($_GET['de'])) : '';
				
				if (isset($_POST['de'])) {
					$keywords_de =  tep_db_input(tep_db_prepare_input($_POST['de']));
				}
				
				if ($keywords_de != '') {
					if(strpos($search, "where")===false) {
						$search .= " where ";			
					} else {
						$search .= " or ";
					}
							
					if(isset($_GET['de'])) {
						$search .= " date_entered = '" . $keywords_de . "' ";
					} else {
						$search .= " date_entered like '%" . $keywords_de . "%' ";
					}
					
					$_GET['de'] = $keywords_de;
				}
	
	$calls_query_raw = "SELECT * FROM " . TABLE_CALLS . " " . $search . " order by $order";
	
    $info = array();
    $calls = array();
    $calls_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $calls_query_raw, $calls_query_numrows);
    $calls_query = tep_db_query($calls_query_raw);
    while ($calls = tep_db_fetch_array($calls_query)) {
        if ((!isset($_GET['callsID']) || (isset($_GET['callsID']) && ($_GET['callsID'] == $calls['calls_id']))) && !isset($cInfo)) {
			$cInfo = new objectInfo($calls);
		}
		
		if (isset($cInfo) && is_object($cInfo) && ($calls['calls_id'] == $cInfo->calls_id)) {
			echo '          <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID', 'action', 'cID')) . 'callsID=' . $cInfo->calls_id . '&action=edit&cID='.$cInfo->customers_id) . '\'">' . "\n";
		} else {
			echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID','cID')) . 'callsID=' . $cInfo->calls_id.'&cID='.$cInfo->customers_id) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo $calls['name'];  ?></td>
			    <td class="dataTableContent"><?php echo tep_date_short($calls['date_entered']); ?></td>
			    <td class="dataTableContent"><?php echo $admin->get_admin_name($calls['created_user_id']); ?></td>
                <td class="dataTableContent"><?php echo $admin->get_admin_name($calls['assigned_user_id']); ?></td>
                <td class="dataTableContent"><?php echo $calls['date_start']; ?></td>				
				<td class="dataTableContent"><?php echo $calls['date_end']; ?></td>
				<td class="dataTableContent"><?php echo $calls["duration_hours"] . " : " . $calls["duration_minutes"]; ?></td>
                <td class="dataTableContent"><?php echo $calls['status']; ?></td>
                <td class="dataTableContent" align="right">
				
					<?php echo '<a href="' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID', 'action','cID')) . 'callsID=' . $calls['calls_id'] . '&action=edit&cID='.$calls["customers_id"], 'SSL') . '">' . tep_image(DIR_WS_ICONS . 'magnifier.png', ICON_PREVIEW) . '</a>&nbsp;'; ?>
					
					<?php if (isset($cInfo) && is_object($cInfo) && ($calls['calls_id'] == $cInfo->calls_id)) { echo tep_image(DIR_WS_IMAGES . 'arrow_right_blue.png', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID')) . 'callsID=' . $calls['calls_id']) . '">' . tep_image(DIR_WS_IMAGES . 'information.png', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;
					
				</td>
				
              </tr>
<?php
	}	
?>
                </table>
            
				<table border="0" width="100%" cellspacing="0" cellpadding="2">
					  <tr>
						<td><table border="0" width="100%" cellspacing="0" cellpadding="2">
						  <tr>
							<td class="smallText" valign="top"><?php echo $calls_split->display_count($calls_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
							<td class="smallText" align="right"><?php echo $calls_split->display_links($calls_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], tep_get_all_get_params(array('page', 'info', 'x', 'y', 'callsID','cID'))); ?></td>
						  </tr>
						  
						</table>
						</td>
					  </tr>

				</table>
			
			</td>
			
			<?php
				  $heading = array();
				  $contents = array();

				  switch ($action) {
					case 'confirm':
					  $heading[] = array('text' => ''. tep_draw_separator('pixel_trans.gif', '11', '12') .'&nbsp;<br><b>' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</b>');
					  $contents = array('form' => tep_draw_form('calls', FILENAME_CALLS, tep_get_all_get_params(array('callsID', 'action','cID')) . 'callsID=' . $cInfo->calls_id . '&action=deleteconfirm&cID='.$cInfo->customers_id));
					  $contents[] = array('text' => TEXT_DELETE_INTRO . '<br><br><b>' . $cInfo->name . '</b>');
					  $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID', 'action', 'cID')) . 'callsID=' . $cInfo->calls_id.'&cID='.$cInfo->customers_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_delete.gif', IMAGE_DELETE));
					  break;
					default:
					  if (isset($cInfo) && is_object($cInfo)) {
						$heading[] = array('text' => ''. tep_draw_separator('pixel_trans.gif', '11', '12') .'&nbsp;<br><b>[' . $cInfo->calls_id . ']&nbsp;' . $cInfo->name . '</b>');
						
							$contents[] = array('align' => 'center',
											'text' => '<br><a href="' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID', 'action', 'cID')) . 'callsID=' . $cInfo->calls_id . '&action=edit&cID='.$cInfo->customers_id) . '">' . tep_image_button('button_page_edit.png', IMAGE_EDIT) . '</a>' .
											'<a href="' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID', 'action')) . 'callsID=' . $cInfo->calls_id . '&action=confirm') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a><br>');

							$contents[] = array('align' => 'center', 'text' => $returned_old_rci . $returned_rci);
							$contents[] = array('text' => '<br>' . TEXT_DATE_CREATED . ' <b>' . tep_date_short($cInfo->date_created) . '</b>');
							$contents[] = array('text' => '<br>' . TEXT_DATE_MODIFIED . ' <b>' . tep_date_short($cInfo->date_modified) . '</b>');
							$contents[] = array('text' => $returned_rci);
					  }
					  break;
				  }

				  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
					echo '            <td width="25%" valign="top">' . "\n";

					$box = new box;
					echo $box->infoBox($heading, $contents);

					echo '            </td>' . "\n";
				  }
				?>
          </tr>
        </table>
			
			
			<?php
			
}
			
		?>

		</div>
		  
		  <br/>
		  
		  <table width="100%"  border="0" cellspacing="0" cellpadding="0" summary="Footer Banner Table">
			<tr>
			  <td align="center">
				<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
			  </td>
			</tr>
		  </table>
		
		</td>
		
	  </tr>
	  
	</table>
	
		
</body>

</html>