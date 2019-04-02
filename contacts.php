<?php
$addID = $_GET["addID"];
$action = $_GET["action"];
$pass = 0;
if(!empty($addID)) {
	$title = "Update Contacts";
	$entry = tep_get_address($addID);
} else {
	$title = "Add new contact";
	$default_add_id = $customers->get_customers_default_address($cID);
	$entry = tep_get_address($default_add_id);
	$pass = 1;
}

//print_r($customers->customers[$cID]["customers_access_group_id"]);

?>
<div class="info-box-head"><?php echo $title; ?></div>

	<link rel="stylesheet" type="text/css" href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . TEMPLATE_NAME ;?>/css/jquery.ui.combogrid.css" />

	<!--<link rel="stylesheet" type="text/css" href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . TEMPLATE_NAME ;?>/css/jquery-ui-1.10.1.custom.css" />-->
	
	

	<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . TEMPLATE_NAME ;?>/js/jquery.ui.combogrid-1.6.3.js"></script>

	<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . TEMPLATE_NAME ;?>/js/jquery.metadata.js"></script>

	<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . TEMPLATE_NAME ;?>/js/jquery.validate.min.js"></script>

	<script type="text/javascript">

		function assignCombo(countryId) {
						
				$( "#postcode" ).combogrid({
					
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
						$( "#postcode" ).val( ui.item.postcode );
						$( "#postcode" ).removeClass("error");
						$('span[for="postcode"]').hide();
						
						$( "#city" ).val( ui.item.city );
						$( "#city" ).removeClass("error");				
						$('span[for="city"]').hide();
						
						$( "#state" ).val( ui.item.state );
						$( "#state" ).removeClass("error");
						$('span[for="state"]').hide();
						
						
						/*$("#country").selectbox('detach');*/
						$( "#country" ).val( ui.item.country_id );
						/*$("#country").selectbox('attach');*/

						$( "#country" ).removeClass("error");
						$('span[for="country"]').hide();
						
						return false;
					},
					
				});
				
		}

		function setFields(postcode){	   
			
			$.post(		   
			"../get_details_by_postcode.php", 	   
			{ pCode: postcode }, 		
			function(data) {
				if(data.returnValue==1) {		
					$('#country').val("");
					$("#state").val("");
					$("#city").val("");
				}
			},	   		
			"json"
			);   
		}

			
		jQuery(document).ready(function(){
			
			//"keyup" event handler to reset input fields
			$( "#postcode" ).on('keyup', function(){
				if($( "#postcode" ).val().length==0){
					$('#city').val(""); $('#state').val(""); 
					$('#country').val(""); 
				}
				
				setFields($( "#postcode" ).val());
				
			});
			
			//alert($("select#country").val());
			
			assignCombo($("select#country").val());
			
			//Form validation
			$.validator.setDefaults({
				submitHandler: function(form) {					
					form.submit();			
				}
			});		
			$.metadata.setType("attr", "validate");
			
			$("#frmAddEditAddress").validate({
				
				onfocusout: function(element, event) {
					this.element(element);
				},
				errorElement: "span",
				errorPlacement: function (error, element) {
					var type = $(element).attr("type");
					if (type === "radio") {
						error.insertAfter(element).wrap('<div class="clear"></div>');
					} else if (type === "checkbox") {
						error.insertAfter(element).wrap('<div class="clear"></div>');
					} else {
						error.insertAfter(element).wrap('<div class="clear"></div>');
					}
				},		
				onkeyup: false,
				rules: {
					firstname: {
					  required: true,
					  maxlength: 20,
					  minlength: 2
					},
					lastname: {
					  required: true,			  
					  maxlength: 20,
					  minlength: 2
					},
					customers_email_address: {
					  required: true,
					  email: true,
					  remote: {
						url: "../check_email.php",
						type: "post",
						data: {
						  cID: function() {
							return $( "#customers_id" ).val();
						  }
						}
				     }

					},
					
					company: {
					  required: true,			  
					  maxlength: 30,
					  minlength: 2
					},
					company_type: {
					  required: true,
					  minlength: 1
					},
					
					street_address: {
					  required: true,
					  maxlength: 40,
					  minlength: 5
					},
					postcode: { required: {
								depends: function(element){
									if ($('#country').val() == '13') {
										return true;
									} else {
										return false;
									}
								}
							  }},
					city: { required: {
								depends: function(element){
									if ($('#country').val() == '13') {
										return true;
									} else {
										return false;
									}
								}
							  }},
					state: { required: {
							 depends: function(element){
								if ($('#country').val() == '13') {
									return true;
								} else {
									return false;
								}
							 }
						  }},
					country: {
					  required: true			  
					},
					telephone: {
					  required: true			  
					},
					<?php if($pass==1) { ?>
					password: {
					  required: true			  
					},
					confirmation: {
					  required: true			  
					}
					<?php } ?>
					
				},
				messages: {            
					firstname: {
					  required: "Please enter Firstname",
					  maxlength: "Firstname should not exceeds 20 characters"
					},
					lastname: {
					  required: "Please enter Lastname",		  
					  maxlength: "Lastname should not exceeds 20 characters"
					},
					customers_email_address: {
					  required: "Please enter Email address",
					  email: "Email address must be valid",
					  remote: "Email Address already exists."
					},					
					company: {
					  required: "Please enter Company name",		  
					  maxlength: "Company name should not exceeds 30 characters"
					},
					company_type: "Please select industry type",					
					street_address: { 
					  required: "Please enter Street address", 
					  maxlength: "Street Address should not exceeds 40 characters"
					},
					postcode: {
						required: "Please enter PostCode",
						minlength: "PostCode must contain at least 4 characters" 
					},
					city: "Please enter City",
					state: "Please enter State",
					country: "Please enter Country",
					telephone: "Please enter Telephone",
					<?php if($pass==1) { ?>
					password: "Please enter Password",
					confirmation: "Please enter Confirm Password"
					<?php } ?>
				}
				
			});	
			//Form validation
			
		});

	</script>
	


	<table width="100%"  border="0" align="center" cellpadding="2" cellspacing="2">
	
	  <tr>
	
		<td valign="top">
		
		<form id="frmAddEditAddress" action="<?php echo tep_href_link("customers_index.php","action=add_update_address&cID=".$cID,"SSL"); ?>" method="post" name="frmAddEditAddress">
		<input type="hidden" name="address_id" value="<?php echo $addID; ?>" />
		<input type="hidden" name="customers_id" id="customers_id" value="<?php echo $cID; ?>" />
		<table>
			<?php if ($addID != $customers_info["customers_default_address_id"]) { ?>
			<tr><td colspan="2" align="center"><input type="checkbox" name="primary" /> Set as Primary Contact</td></tr>
			<?php } ?>
			<tr>
				<td><?php echo ENTRY_GENDER; ?></td>
				<td valign="top">				
					<input type="radio" name="gender" <?php if($entry["entry_gender"]=="m") echo "checked"; ?> value="m"> Male<br>
				    <input type="radio" name="gender" <?php if($entry["entry_gender"]=="f") echo "checked"; ?> value="f"> Female<br>
				</td>
			</tr>
			<tr><td><?php echo ENTRY_FIRST_NAME; ?></td><td>: <?php echo tep_draw_input_field('firstname', (isset($entry['entry_firstname']) ? $entry['entry_firstname'] : ''),'class="login-text"'); ?></td></tr>
			
			<tr><td><?php echo ENTRY_LAST_NAME; ?></td><td>: <?php echo tep_draw_input_field('lastname', (isset($entry['entry_lastname']) ? $entry['entry_lastname'] : ''),'class="login-text"'); ?></td></tr>
			
			<tr><td><?php echo ENTRY_EMAIL_ADDRESS; ?></td><td>: <?php echo tep_draw_input_field('customers_email_address', (isset($entry['entry_email_address']) ? $entry['entry_email_address'] : ''),'class="login-text"'); ?></td></tr>
			
			<tr><td><?php echo ENTRY_TELEPHONE_NUMBER; ?></td><td>: <?php echo tep_draw_input_field('telephone', (isset($entry['entry_telephone']) ? $entry['entry_telephone'] : ''),'class="login-text"'); ?></td></tr>
			
			<tr><td><?php echo ENTRY_FAX_NUMBER; ?></td><td>: <?php echo tep_draw_input_field('fax', (isset($entry['entry_fax']) ? $entry['entry_fax'] : ''),'class="login-text"'); ?></td></tr>
			
			<tr><td><?php echo ENTRY_COMPANY; ?></td><td>: <?php echo tep_draw_input_field('company', (isset($entry['entry_company']) ? $entry['entry_company'] : ''),'class="login-text"'); ?></td></tr>
			
			<tr>
				<td><?php echo ENTRY_COMPANY_TYPE; ?></td>
				<td>: <?php   
					$industry_array = tep_get_industries();							
					echo tep_draw_pull_down_menu('company_type', $industry_array, ($entry['entry_company_type']) ? $entry['entry_company_type']:'', ' id="company_type" style="width:66%;" ');
					?>
				</td>
			</tr>
			
			<!-- // BOF Customers extra fields -->
		    <?php 
				$extra_fields_arr = array();
				$extra_fields_arr = tep_get_extra_fields_as_array($cID,$addID,$customers->customers[$cID]["customers_access_group_id"],$languages_id); 
				
				//print_r($extra_fields_arr);
				
				if(count($extra_fields_arr)>0) {
					foreach($extra_fields_arr as $key=>$val) {
						echo "<tr><td>".$val["fields_name"]."</td><td>:";
							
							foreach($val["fields_type"] as $ckey=>$cval) {
								echo $cval;
							}
							//$val["fields_type"];
						
						echo "</td></tr>";
					}
				}
				
			?>
		    <!-- // EOF Customers extra fields -->
			
			<tr><td><?php echo ENTRY_STREET_ADDRESS; ?></td><td>: <?php echo tep_draw_input_field('street_address', (isset($entry['entry_street_address']) ? $entry['entry_street_address'] : ''),'class="login-text"'); ?></td></tr>
			
			<tr><td><?php echo ENTRY_SUBURB; ?></td><td>: <?php echo tep_draw_input_field('suburb', (isset($entry['entry_suburb']) ? $entry['entry_suburb'] : ''),'class="login-text"'); ?></td></tr>
			<tr><td><?php echo ENTRY_POST_CODE; ?></td><td>: <?php echo tep_draw_input_field('postcode', (isset($entry['entry_postcode']) ? $entry['entry_postcode'] : ''),'class="login-text" id="postcode" '); ?></td></tr>
			<tr><td><?php echo ENTRY_CITY; ?></td><td>: <?php echo tep_draw_input_field('city', (isset($entry['entry_city']) ? $entry['entry_city'] : ''),'class="login-text" id="city" '); ?></td></tr>
			<tr>
				<td><?php echo ENTRY_STATE; ?></td>
				<td>:										
				<?php
				if ($process == true) {
				  if ($entry_state_has_zones == true) {
					$zones_array = array();
					$zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' order by zone_name");
					while ($zones_values = tep_db_fetch_array($zones_query)) {
					  $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
					}
					
					echo '<div class="styled-select">';
					echo tep_draw_pull_down_menu('state', $zones_array,($entry['entry_state'])?$entry['entry_state']:"",' id="state" class="login-text" ');
					echo '</div>';
					
					
				  } else {						
					echo tep_draw_input_field('state',$entry['entry_state'], ' id="state" class="login-text" ');
				  }
				} else {
				  echo tep_draw_input_field('state', tep_get_zone_name((isset($entry['entry_country_id']) ? $entry['entry_country_id'] : 0), (isset($entry['entry_zone_id']) ? $entry['entry_zone_id'] : 0 ), (isset($entry['entry_state']) ? $entry['entry_state'] : 0)),' id="state" class="login-text" ');
				}
				?>					
				</td>
			</tr>
			<tr><td><?php echo ENTRY_COUNTRY; ?></td><td>: <?php echo tep_get_country_list('country',($entry['entry_country_id'])?$entry['entry_country_id']:'13',' id="country" style="width:66%;" '); ?></td></tr>
			<tr>
				<td><?php echo ENTRY_PASSWORD; ?></td>
				<td><?php echo tep_draw_password_field('customers_password'); ?></td>
			</tr>
			<tr>
				<td><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></td>
				<td><?php echo tep_draw_password_field('customers_password_confirm'); ?></td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2" align="center"><?php echo tep_image_submit('button_submit.gif', "Submit");  ?> &nbsp; <?php echo ' <a href="' . tep_href_link("customers_index.php", "cID=".$cID,"SSL") .'">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';  ?></td></tr>
		</table>
	</form>
	</td>
		
	  </tr>
	  
	</table>