<?php
/*
  $Id: create_account.php,v 2.0 2008/05/05 00:36:41 datazen Exp $

  CRE Loaded, Commerical Open Source eCommerce
  http://www.creloaded.com

  Copyright (c) 2008 CRE Loaded
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
require('includes/application_top.php');

include('includes/classes/admin.php');
  
$admin = new admin();
  
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
<script src="includes/javascript/jquery-ui-1.12.0/external/jquery/jquery.js"></script>

<link rel="stylesheet" type="text/css" href="includes/javascript/jquery-ui-1.12.0/jquery-ui.css"/>

<link rel="stylesheet" type="text/css" href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/css/jquery.ui.combogrid.css" />

<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/js/vendor/jquery-ui.min.js"></script>

<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/js/vendor/jquery.ui.combogrid-1.6.3.js"></script>

<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/js/vendor/jquery.metadata.js"></script>

<script language="JavaScript" type="text/javascript" src="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_TEMPLATES . DEFAULT_TEMPLATE ;?>/js/vendor/jquery.validate.min.js"></script>

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
			
			$("#account_edit").validate({
				
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
					email_address: {
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
					email_address: {
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
	
	
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
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
    <td valign="top" class="page-container"><form name="account_edit" id="account_edit" method="post" <?php echo 'action="' . tep_href_link(FILENAME_CREATE_ACCOUNT_PROCESS, '', 'SSL') . '"'; ?> >
      <input type="hidden" name="action" value="process"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <?php
      if (isset($navigation->snapshot) && is_array($navigation->snapshot) && sizeof($navigation->snapshot) > 0) {
        ?>
        <tr>
          <td class="smallText"><br><?php echo sprintf(TEXT_ORIGIN_LOGIN, tep_href_link(FILENAME_LOGIN, tep_get_all_get_params(), 'SSL')); ?></td>
        </tr>
        <?php
      }
      ?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
      <tr>
        <td>
          <?php
          //$email_address = tep_db_prepare_input($_GET['email_address']);
          $account['entry_country_id'] = STORE_COUNTRY;
          $account['entry_zone_id'] = STORE_ZONE;
          require(DIR_WS_MODULES . 'account_details.php');
          ?>
        </td>
      </tr>
      <tr>
        <td align="right" class="main">
         <br><?php echo tep_image_submit('button_confirm.gif', IMAGE_BUTTON_CONTINUE); ?></td>
      </tr>
    </table></form></td>
    <!-- body_text_eof //-->
  </tr>
</table>
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php
require(DIR_WS_INCLUDES . 'footer.php');
?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>