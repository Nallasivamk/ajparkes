<?php 
$contacts_arr = array();
$orders_arr = array();
$readonly = "";

if(tep_not_null($cID)) {
	
	$customers_exists = $customers->is_set($cID);
	if($customers_exists===true) {
		$contacts_arr = $customers->get_customers_all_contacts($cID);
		$orders_arr = $customers->get_customers_orders($cID);
		$readonly = "readonly";
	}
	
}

//print_r($contacts_arr);

?>
<link href="includes/javascript/jquery-ui-1.12.0/jquery-ui.css" rel="stylesheet">
<script src="includes/javascript/jquery-ui-1.12.0/jquery-ui.js"></script>
<script src="includes/javascript/jquery.validate.min.js"></script>
<script type="text/javascript">
	$(function() {
		$( "#customers_id" ).autocomplete({
		  //source: "ajax_calls_search_relation.php"
		  source: function (request, response) {
			$.post("ajax_calls_search_relation.php", { term: request.term, relation: "Accounts" }, response, 'json');
		  }
		});
	
		$( ".calendar" ).datepicker({
			minDate: 0,
			dateFormat: "dd-mm-yy",
			showOn: "button",
			buttonImage: "images/calendar.gif",
			buttonImageOnly: true,
			timeFormat: 'hh:mm'
		});
		
		$("form[name='frmCalls']").validate({
			rules: {
			  name: "required",
			  description: "required",
			  call_date: "required",
			  start_date: "required",
			  duration: "required",
			  assigned_user_id: "required"
			},
			messages: {
			  name: "Please enter subject",
			  description: "Please enter description",
			  call_date: "Please enter call date",
			  start_date: "Please enter start date",
			  duration: "Please enter the calls duration",
			  assigned_user_id: "Please select the user to assign the call"
			},
			submitHandler: function(form) {
			  form.submit();
			}
		});
		
	});
	
</script>
<?php 
$calls_array = array();
if(tep_not_null($callsID)) { 
	$action_type = "save"; 
	
	/*$calls = new calls();
	$calls_array = $calls->get_call($callsID);*/
	
	$cqry = tep_db_query("SELECT * FROM calls WHERE calls_id = '".$callsID."'");
	$calls_array = tep_db_fetch_array($cqry);
	
	$call_date = date("d-m-Y H:i:s", strtotime($calls_array["date_start"]));
	$start_date = date("d-m-Y", strtotime($calls_array["date_end"]));
	$start_date_hours = date("H",strtotime($calls_array["date_end"]));
	$start_date_minutes = date("i",strtotime($calls_array["date_end"]));
	//print_r($calls_array);
} else { 
	$action_type = "insert"; 
}
echo tep_draw_form('frmCalls', FILENAME_CALLS, tep_get_all_get_params(array('action','cID')) . 'action='.$action_type.$cLink, 'post', 'id=""frmCalls', 'SSL'); ?>				
				<input type="hidden" name="calls_id" id="calls_id" value="<?php echo $callsID; ?>">
				
				<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionsContainer">
					<tbody>
						<tr>
							<td class="buttons">							  
								<input class="cssButtonSubmit" type="submit" value="Save" name="button" accesskey="S" title="Save [Alt+S]">
								<a class="cssButton" href="<?php echo $cLink; ?>">Cancel</a>
							</td>
							<td align="right"></td>
						</tr>
					</tbody>
				</table>
				
				<div id="EditView_tabs">
					<div>
						<div id="LBL_CALL_INFORMATION">
							<table width="100%" cellspacing="1" cellpadding="0" border="0" class="edit view">
							<tbody>
								<tr>
									<th align="left" colspan="4">
										<h4>Schedule or Notify a Follow up or Meeting</h4>
									</th>
								</tr>
								
								<?php if(tep_not_null($cID)) { ?>
								<tr>
									<td width="12.5%" valign="top">
									Account #: 
									<span class="required">*</span>
									</td>
									<td width="37.5%" valign="top">
									<input type="text" tabindex="100" title="" maxlength="50" size="30" id="customers_id" name="customers_id" <?php echo $readonly; ?> value="<?php echo $cID; ?>">
									</td>
									<td width="12.5%" valign="top">
									Contacts:
									<span class="required">*</span>
									</td>
									<td width="37.5%" valign="top">
										<div id="contacts">
										<select tabindex="101" id="address_book_id" name="address_book_id">
											<?php
												if(count($contacts_arr)>0) {
													foreach($contacts_arr as $key=>$val) {
														$selected = ($calls_array["address_book_id"]==$key)?"selected":"";
														echo "<option value='".$key."' ".$selected.">".$val["firstname"]." ".$val["lastname"]."</option>";
													}
												}
											?>
										</select>
										</div>										
									</td>
								</tr>																
								<tr>
									<td width="12.5%" valign="top">
										Orders/Quotes:
									</td>
									<td width="37.5%" valign="top">
										<div id="orders">
										<select tabindex="102" id="orders_id" name="orders_id">
											<option value="">Select Order</option>
											<?php
												if(count($orders_arr)>0) {
													foreach($orders_arr as $key=>$val) {
														$selected = ($calls_array["orders_id"]==$key)?"selected":"";
														echo "<option value='".$key."' ".$selected.">".$key."</option>";
													}
												}
											?>
										</select>
										</div>	
									</td>
									<td width="12.5%" valign="top"></td>									
									<td width="37.5%" valign="top"></td>
								</tr>
								<?php } ?>
								<tr>
									<td width="12.5%" valign="top">
									Subject:
									<span class="required">*</span>
									</td>
									<td width="37.5%" valign="top">
									<input type="text" tabindex="103" title="" maxlength="50" size="30" id="name" name="name" value="<?php echo $calls_array["name"]; ?>"> 
									</td>
									<td width="12.5%" valign="top" >
									Status:
									<span class="required">*</span>
									</td>
									<td width="37.5%" valign="top">
										<select tabindex="104" id="status" name="status">
											<option value="Planned" <?php echo ($calls_array["status"]=="Planned")? "selected":""; ?> >Planned</option>
											<option value="Held" <?php echo ($calls_array["status"]=="Held")? "selected":""; ?>>Held</option>
											<option value="Not Held" <?php echo ($calls_array["status"]=="Not Held")? "selected":""; ?>>Not Held</option>
											<option value="To call back" <?php echo ($calls_array["status"]=="To call back")? "selected":""; ?>>To call back</option>
											<option value="Phone Message Pending" <?php echo ($calls_array["status"]=="Phone Message Pending")? "selected":""; ?>>Phone Message Pending</option>
											<option value="Closed" <?php echo ($calls_array["status"]=="Closed")? "selected":""; ?>>Closed</option>
										</select>
									</td>
								
								</tr>
							<tr>
								<td width="12.5%" valign="top">
								Start Date & Time:
								<span class="required">*</span>
								</td>
								<td width="37.5%" valign="top">
									<table cellspacing="2" cellpadding="0" border="0">
										<tbody><tr valign="middle">
										<td nowrap="">								
											<input type="text" class="calendar" tabindex="103" value="<?php echo $start_date; ?>" maxlength="10" size="11" id="start_date" name="start_date">
										</td>
										<td nowrap="">
										<div id="date_start_time_section">
											<select name="start_date_hours" tabindex="104" id="start_date_hours" size="1">
												<option></option>
												<?php
													for($i=1; $i<24;$i++) {
														$hrs = sprintf("%02d", $i);
														$selected_hrs = ($start_date_hours==$hrs)?"selected":"";
														echo "<option value='".$hrs."' ".$selected_hrs.">".$hrs."</option>";														
													}
												?>
											</select>&nbsp;:
											&nbsp; 
											<select tabindex="104" name="start_date_minutes" id="start_date_minutes" size="1">
												<option></option>
												<?php
													for($i=1; $i<60;$i++) {
														$mins = sprintf("%02d", $i);
														$selected_mins = ($start_date_minutes==$mins)?"selected":"";
														echo "<option value='".$mins."' ".$selected_mins.">".$mins."</option>";														
													}
												?>
											</select>
										</div>
										</td>
										</tr>
										</tbody>	
									</table>						
								</td>
								<td width="12.5%" valign="top">
								Call Date & Time:
								</td>
								<td width="37.5%" valign="top">
									<input type="text" name="call_date" tabindex="105" <?php echo $readonly; ?> value="<?php echo ($call_date)?$call_date:date("d-m-Y H:i:s"); ?>" >
								</td>
							</tr>
							<tr>
								<td width="12.5%" valign="top" scope="row" id="duration_hours_label">
								Duration:
								<span class="required">*</span>
								</td>
								<td width="37.5%" valign="top">
									<script type="text/javascript">function isValidDuration() { form = document.getElementById('frmCalls'); if ( form.duration_hours.value + form.duration_minutes.value &lt;= 0 ) { alert('Duration time must be greater than 0'); return false; } return true; }</script>
									
									<input type="text" maxlength="2" size="2" name="duration_hours" value="<?php echo $calls_array["duration_hours"] ?>" tabindex="106">
									<select name="duration_minutes" tabindex="1">
									<option value="0" <?php echo ($calls_array["duration_minutes"]==0)?"selected":""; ?>>00</option>
									<option value="15" <?php echo ($calls_array["duration_minutes"]==15)?"selected":""; ?>>15</option>
									<option value="30" <?php echo ($calls_array["duration_minutes"]==30)?"selected":""; ?>>30</option>
									<option value="45" <?php echo ($calls_array["duration_minutes"]==45)?"selected":""; ?>>45</option></select>&nbsp;<span class="dateFormat">(hours/minutes)</span>
								</td>
								<td width="12.5%" valign="top" scope="row" id="reminder_time_label">
								Reminder:
								</td>
								<td width="37.5%" valign="top">
								
									<input type="checkbox" class="checkbox" <?php echo (isset($calls_array["reminder_checked"]))?true:false; ?> name="reminder_checked" tabindex="107">
									<div style="display:inline" id="should_remind_list">
										<select id="reminder_time" name="reminder_time">
										<option value="60" <?php echo ($calls_array["reminder_time"]==60)?"selected":""; ?>>1 minute prior</option>
										<option value="300" <?php echo ($calls_array["reminder_time"]==300)?"selected":""; ?>>5 minutes prior</option>
										<option value="600" <?php echo ($calls_array["reminder_time"]==600)?"selected":""; ?>>10 minutes prior</option>
										<option value="900" <?php echo ($calls_array["reminder_time"]==900)?"selected":""; ?>>15 minutes prior</option>
										<option value="1800" <?php echo ($calls_array["reminder_time"]==1800)?"selected":""; ?>>30 minutes prior</option>
										<option value="3600" <?php echo ($calls_array["reminder_time"]==3600)?"selected":""; ?>>1 hour prior</option>
										<option value="86400" <?php echo ($calls_array["reminder_time"]==86400)?"selected":""; ?>>1 Days Prior</option>
										<option value="259200" <?php echo ($calls_array["reminder_time"]==259200)?"selected":""; ?>>3 Days Prior</option>
										<option value="432000" <?php echo ($calls_array["reminder_time"]==432000)?"selected":""; ?>>5 Days Prior</option>
										<option value="604800" <?php echo ($calls_array["reminder_time"]==604800)?"selected":""; ?>>7 Days Prior</option>
										</select>
									</div>
								
								</td>
							</tr>
							<tr>
								<td width="12.5%" valign="top" scope="row" id="description_label">
								Description:
								</td>
								<td width="37.5%" valign="top" colspan="3">
								<textarea tabindex="108" title="" cols="80" rows="6" name="description" id="description"><?php echo $calls_array["description"]; ?></textarea>
								</td>
							</tr>
							
							</tbody></table>
						</div>
						<div id="LBL_PANEL_ASSIGNMENT">
							<table width="100%" cellspacing="1" cellpadding="0" border="0" class="edit view">
								<tbody>
									<tr>
										<th align="left" colspan="8">
											<h4>Other</h4>
										</th>
									</tr>
									<tr>
										<td width="12.5%" valign="top" scope="row" id="assigned_user_name_label">
										Assigned to:
										</td>
										<td width="37.5%" valign="top" colspan="3">
											
											<select name="assigned_user_id">
												<option value=""></option>
												<?php
												
												foreach($admin->admin as $key=>$val) {
													$selected = ($calls_array["assigned_user_id"]==$key)?"selected":"";
													echo "<option value='".$key."' ".$selected.">" . $val["admin_firstname"] . " " . $val["admin_lastname"] . "</option>";
												}
												
												?>
											</select>
												
										</td>
									</tr>
									<tr>
										<td width="12.5%" valign="top" scope="row" id="_label">
										Additional notification:
										</td>
										<td width="37.5%" valign="top" colspan="3">
											<select name="notify_other_user_id">

												<option value=""></option>

												<?php

												foreach($admin->admin as $key=>$val) {

													$selected = ($calls_array["notify_other_user_id"]==$key)?"selected":"";

													echo "<option value='".$key."' ".$selected.">" . $val["admin_firstname"] . " " . $val["admin_lastname"] . "</option>";

												}

												?>

											</select>
										</td>
									</tr>
									<tr>
										<td width="12.5%" valign="top" scope="row" id="_label">
											Notify User(s):
										</td>
										<td width="37.5%" valign="top" colspan="3">
											<input type="checkbox" checked="true" name="is_notify_user">
										</td>
									</tr>
									
								</tbody>
							</table>
						</div>
					</div>
				</div>
				
				<div class="buttons">
					<input class="cssButtonSubmit" type="submit" value="Save" name="button" class="button primary" accesskey="S" title="Save [Alt+S]">
					<a class="cssButton" href="<?php echo $cLink; ?>">Cancel</a>
				</div>
				
			</form>