<?php
/*
  $Id: affiliate_affiliates.php,v 1.1.1.1 2004/03/04 23:38:06 ccwjr Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 - 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  define('DIR_WS_AFFILIATE_COBRAND', DIR_WS_CATALOG_IMAGES . 'affiliate_cobrand/');
  define('DIR_FS_AFFILIATE_COBRAND', DIR_FS_CATALOG_IMAGES . 'affiliate_cobrand/');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  if (isset($_GET['action'])) {
    switch ($_GET['action']) {
      case 'update':
        $affiliate_id = tep_db_prepare_input($_GET['acID']);
        $affiliate_gender = (isset($_POST['affiliate_gender']) ? tep_db_prepare_input($_POST['affiliate_gender']) : '');
        $affiliate_firstname = tep_db_prepare_input($_POST['affiliate_firstname']);
        $affiliate_lastname = tep_db_prepare_input($_POST['affiliate_lastname']);
        $affiliate_dob = (isset($_POST['affiliate_dob']) ? tep_db_prepare_input($_POST['affiliate_dob']) : '');
        $affiliate_email_address = tep_db_prepare_input($_POST['affiliate_email_address']);
        $affiliate_company = (isset($_POST['affiliate_company']) ? tep_db_prepare_input($_POST['affiliate_company']) : '');
        $affiliate_company_taxid = (isset($_POST['affiliate_company_taxid']) ? tep_db_prepare_input($_POST['affiliate_company_taxid']) : '');
        $affiliate_payment_check = (isset($_POST['affiliate_payment_check']) ? tep_db_prepare_input($_POST['affiliate_payment_check']) : '');
        $affiliate_payment_paypal = (isset($_POST['affiliate_payment_paypal']) ? tep_db_prepare_input($_POST['affiliate_payment_paypal']) : '');
        $affiliate_payment_bank_name = (isset($_POST['affiliate_payment_bank_name']) ? tep_db_prepare_input($_POST['affiliate_payment_bank_name']) : '');
        $affiliate_payment_bank_branch_number = (isset($_POST['affiliate_payment_bank_branch_number']) ? tep_db_prepare_input($_POST['affiliate_payment_bank_branch_number']) : '');
        $affiliate_payment_bank_swift_code = (isset($_POST['affiliate_payment_bank_swift_code']) ? tep_db_prepare_input($_POST['affiliate_payment_bank_swift_code']) : '');
        $affiliate_payment_bank_account_name = (isset($_POST['affiliate_payment_bank_account_name']) ? tep_db_prepare_input($_POST['affiliate_payment_bank_account_name']) : '');
        $affiliate_payment_bank_account_number = (isset($_POST['affiliate_payment_bank_account_number']) ? tep_db_prepare_input($_POST['affiliate_payment_bank_account_number']) : '');
        $affiliate_street_address = tep_db_prepare_input($_POST['affiliate_street_address']);
        $affiliate_suburb = tep_db_prepare_input($_POST['affiliate_suburb']);
        $affiliate_postcode=tep_db_prepare_input($_POST['affiliate_postcode']);
        $affiliate_city = tep_db_prepare_input($_POST['affiliate_city']);
        $affiliate_country_id=tep_db_prepare_input($_POST['affiliate_country_id']);
        $affiliate_telephone=tep_db_prepare_input($_POST['affiliate_telephone']);
        $affiliate_fax=tep_db_prepare_input($_POST['affiliate_fax']);
        $affiliate_homepage=tep_db_prepare_input($_POST['affiliate_homepage']);
        $affiliate_state = tep_db_prepare_input($_POST['affiliate_state']);
        $affiliate_zone_id = (isset($_POST['affiliate_zone_id']) ? tep_db_prepare_input($_POST['affiliate_zone_id']) : '');
        $affiliate_commission_percent = tep_db_prepare_input($_POST['affiliate_commission_percent']);
        $affiliate_template = (isset($_POST['affiliate_template']) ? tep_db_prepare_input($_POST['affiliate_template']) : 'NULL');
        if ($affiliate_zone_id > 0) $affiliate_state = '';
        $delete_cobrand_image = (isset($_POST['delete_cobrand_image']) ? tep_db_prepare_input($_POST['delete_cobrand_image']) : '');
        $affiliate_cobrand_name = (isset($_POST['affiliate_cobrand_name']) ? tep_db_prepare_input($_POST['affiliate_cobrand_name']) : '');
        $affiliate_cobrand_slogan = (isset($_POST['affiliate_cobrand_slogan']) ? tep_db_prepare_input($_POST['affiliate_cobrand_slogan']) : '');
        $affiliate_cobrand_url = (isset($_POST['affiliate_cobrand_url']) ? tep_db_prepare_input($_POST['affiliate_cobrand_url']) : '');
        $affiliate_cobrand_support_email = (isset($_POST['affiliate_cobrand_support_email']) ? tep_db_prepare_input($_POST['affiliate_cobrand_support_email']) : '');
        $affiliate_cobrand_support_phone = (isset($_POST['affiliate_cobrand_support_phone']) ? tep_db_prepare_input($_POST['affiliate_cobrand_support_phone']) : '');
        $affiliate_cobrand_image_existing = (isset($_POST['affiliate_cobrand_image_existing']) ? tep_db_prepare_input($_POST['affiliate_cobrand_image_existing']) : '');
        $affiliate_cobrand_image = (isset($_POST['affiliate_cobrand_image']) ? tep_db_prepare_input($_POST['affiliate_cobrand_image']) : $affiliate_cobrand_image_existing);

        //delete $delete_cobrand_image image
        if(isset($delete_cobrand_image) && $delete_cobrand_image == 'yes') {
            
            if (!is_writeable(DIR_FS_AFFILIATE_COBRAND)) {
                $messageStack->add('search', sprintf(AFFILITE_ERROR_DIRECTORY_NOT_WRITABLE,DIR_FS_AFFILIATE_COBRAND), 'warning');
            } else {
            if(is_writable(DIR_FS_AFFILIATE_COBRAND . $affiliate_cobrand_image_existing)){
                unlink(DIR_FS_AFFILIATE_COBRAND . $affiliate_cobrand_image_existing);
                tep_db_query("UPDATE " . TABLE_AFFILIATE . " set affiliate_cobrand_image = '' where affiliate_id = '" . tep_db_input($affiliate_id) . "'");
                $affiliate_cobrand_image_existing = '';
                $affiliate_cobrand_image ='';
                $messageStack->add_session('search', AFFILIATE_SUCCESS_DELETEED_IMAGE, 'success'); 
            }else if (!file_exists(DIR_FS_AFFILIATE_COBRAND . $affiliate_cobrand_image_existing)) {
                tep_db_query("UPDATE " . TABLE_AFFILIATE . " set affiliate_cobrand_image = '' where affiliate_id = '" . tep_db_input($affiliate_id) . "'");
                $affiliate_cobrand_image_existing = '';
                $affiliate_cobrand_image ='';
                $messageStack->add_session('search', AFFILIATE_SUCCESS_DELETEED_IMAGE, 'success'); 
            }else {
                $messageStack->add_session('search', sprintf(AFFILITE_ERROR_DIRECTORY_NOT_WRITABLE,DIR_FS_AFFILIATE_COBRAND), 'warning'); 
            }
            }
        }        
        //delete eof
        //upload cobrand logo 
        $file_to_check = tep_db_fetch_array(tep_db_query("SELECT affiliate_cobrand_image from " . TABLE_AFFILIATE . " where affiliate_id = '" . tep_db_input($affiliate_id) . "'"));
        $affiliate_cobrand_image_file = (isset($_FILES['affiliate_cobrand_image']['name']) ? tep_db_prepare_input($_FILES['affiliate_cobrand_image']['name']) : '');
        
        if($affiliate_cobrand_image_file != ''){
            $new_cobrand_image = 'brand_' . (int)$affiliate_id . '_' . $affiliate_cobrand_image_file;
            
            if($file_to_check['affiliate_cobrand_image'] != $new_cobrand_image && is_writable(DIR_FS_AFFILIATE_COBRAND . $file_to_check['affiliate_cobrand_image'])){
                @unlink(DIR_FS_AFFILIATE_COBRAND . $file_to_check['affiliate_cobrand_image']);
            if (move_uploaded_file($_FILES['affiliate_cobrand_image']['tmp_name'], DIR_FS_AFFILIATE_COBRAND . $new_cobrand_image)){
                $affiliate_cobrand_image = $new_cobrand_image;
                $messageStack->add_session('search', AFFILIATE_SUCCESS_UPLOADED_IMAGE, 'success');
            } else {
                $messageStack->add_session('search', AFFILIATE_ERROR_UPLOADING_IMAGE, 'error');
            }              
            } else {
                $messageStack->add_session('search', AFFILIATE_ERROR_UPLOADING_IMAGE, 'error');
                
            }

        }
        //upload eof

       // If someone uses , instead of .

        $affiliate_commission_percent = str_replace (',' , '.' , $affiliate_commission_percent);

        $sql_data_array = array('affiliate_firstname' => $affiliate_firstname,
                                'affiliate_lastname' => $affiliate_lastname,
                                'affiliate_email_address' => $affiliate_email_address,
                                'affiliate_payment_check' => $affiliate_payment_check,
                                'affiliate_payment_paypal' => $affiliate_payment_paypal,
                                'affiliate_payment_bank_name' => $affiliate_payment_bank_name,
                                'affiliate_payment_bank_branch_number' => $affiliate_payment_bank_branch_number,
                                'affiliate_payment_bank_swift_code' => $affiliate_payment_bank_swift_code,
                                'affiliate_payment_bank_account_name' => $affiliate_payment_bank_account_name,
                                'affiliate_payment_bank_account_number' => $affiliate_payment_bank_account_number,
                                'affiliate_street_address' => $affiliate_street_address,
                                'affiliate_postcode' => $affiliate_postcode,
                                'affiliate_city' => $affiliate_city,
                                'affiliate_country_id' => $affiliate_country_id,
                                'affiliate_telephone' => $affiliate_telephone,
                                'affiliate_fax' => $affiliate_fax,
                                'affiliate_homepage' => $affiliate_homepage,
                                'affiliate_commission_percent' => $affiliate_commission_percent,
                                'affiliate_template' => $affiliate_template,
                                'affiliate_cobrand_image' => $affiliate_cobrand_image,
                                'affiliate_cobrand_name' => $affiliate_cobrand_name,
                                'affiliate_cobrand_slogan' => $affiliate_cobrand_slogan,
                                'affiliate_cobrand_url' => $affiliate_cobrand_url,
                                'affiliate_cobrand_support_email' => $affiliate_cobrand_support_email,
                                'affiliate_cobrand_support_phone' => $affiliate_cobrand_support_phone,
                                'affiliate_agb' => '1');

        if (ACCOUNT_DOB == 'true') $sql_data_array['affiliate_dob'] = tep_date_raw($affiliate_dob);
        if (ACCOUNT_GENDER == 'true') $sql_data_array['affiliate_gender'] = $affiliate_gender;
        if (ACCOUNT_COMPANY == 'true') {
          $sql_data_array['affiliate_company'] = $affiliate_company;
          $sql_data_array['affiliate_company_taxid'] =  $affiliate_company_taxid;
        }
        if (ACCOUNT_SUBURB == 'true') $sql_data_array['affiliate_suburb'] = $affiliate_suburb;
        if (ACCOUNT_STATE == 'true') {
          $sql_data_array['affiliate_state'] = $affiliate_state;
          $sql_data_array['affiliate_zone_id'] = $affiliate_zone_id;
        }

        $sql_data_array['affiliate_date_account_last_modified'] = 'now()';

        tep_db_perform(TABLE_AFFILIATE, $sql_data_array, 'update', "affiliate_id = '" . tep_db_input($affiliate_id) . "'");

        tep_redirect(tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID', 'action')) . 'acID=' . $affiliate_id));
        break;
      case 'deleteconfirm':
        $affiliate_id = tep_db_prepare_input($_GET['acID']);
        $file_to_delete = tep_db_fetch_array(tep_db_query("SELECT affiliate_cobrand_image from " . TABLE_AFFILIATE . " where affiliate_id = '" . tep_db_input($affiliate_id) . "'"));
        if($file_to_delete['affiliate_cobrand_image'] != $new_cobrand_image && is_writable(DIR_FS_AFFILIATE_COBRAND . $file_to_delete['affiliate_cobrand_image'])){
            @unlink(DIR_FS_AFFILIATE_COBRAND . $file_to_delete['affiliate_cobrand_image']);
        } 
        affiliate_delete(tep_db_input($affiliate_id));

        tep_redirect(tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID', 'action'))));
        break;
    }
  }
  if (is_dir(DIR_FS_AFFILIATE_COBRAND)) {
  if (!is_writeable(DIR_FS_AFFILIATE_COBRAND)) {
      $messageStack->add('search', sprintf(AFFILITE_ERROR_DIRECTORY_NOT_WRITABLE,DIR_FS_AFFILIATE_COBRAND), 'warning');
  }
  } else {
       $messageStack->add('search', sprintf(AFFILIATE_ERROR_DIRECTORY_DOES_NOT_EXIST,DIR_FS_AFFILIATE_COBRAND), 'error');
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
<?php
  if (isset($_GET['action']) && $_GET['action'] == 'edit') {
?>
<script language="javascript"><!--
function resetStateText(theForm) {
  theForm.affiliate_state.value = '';
  if (theForm.affiliate_zone_id.options.length > 1) {
    theForm.affiliate_state.value = '<?php echo JS_STATE_SELECT; ?>';
  }
}

function resetZoneSelected(theForm) {
  if (theForm.affiliate_state.value != '') {
    theForm.affiliate_zone_id.selectedIndex = '0';
    if (theForm.affiliate_zone_id.options.length > 1) {
      theForm.affiliate_state.value = '<?php echo JS_STATE_SELECT; ?>';
    }
  }
}

function update_zone(theForm) {
  var NumState = theForm.affiliate_zone_id.options.length;
  var SelectedCountry = '';

  while(NumState > 0) {
    NumState--;
    theForm.affiliate_zone_id.options[NumState] = null;
  }

  SelectedCountry = theForm.affiliate_country_id.options[theForm.affiliate_country_id.selectedIndex].value;

<?php echo tep_js_zone_list('SelectedCountry', 'theForm', 'affiliate_zone_id'); ?>

  resetStateText(theForm);
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var affiliate_firstname = document.affiliate.affiliate_firstname.value;
  var affiliate_lastname = document.affiliate.affiliate_lastname.value;
<?php if (ACCOUNT_COMPANY == 'true') echo 'var affiliate_company = document.affiliate.affiliate_company.value;' . "\n"; ?>
  var affiliate_email_address = document.affiliate.affiliate_email_address.value;
  var affiliate_street_address = document.affiliate.affiliate_street_address.value;
  var affiliate_postcode = document.affiliate.affiliate_postcode.value;
  var affiliate_city = document.affiliate.affiliate_city.value;
  var affiliate_telephone = document.affiliate.affiliate_telephone.value;

<?php if (ACCOUNT_GENDER == 'true') { ?>
  if (document.affiliate.affiliate_gender[0].checked || document.affiliate.affiliate_gender[1].checked) {
  } else {
    error_message = error_message + "<?php echo JS_GENDER; ?>";
    error = 1;
  }
<?php } ?>

  if (affiliate_firstname = "" || affiliate_firstname.length < <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_FIRST_NAME; ?>";
    error = 1;
  }

  if (affiliate_lastname = "" || affiliate_lastname.length < <?php echo ENTRY_LAST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_LAST_NAME; ?>";
    error = 1;
  }

  if (affiliate_email_address = "" || affiliate_email_address.length < <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_EMAIL_ADDRESS; ?>";
    error = 1;
  }

  if (affiliate_street_address = "" || affiliate_street_address.length < <?php echo ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_ADDRESS; ?>";
    error = 1;
  }

  if (affiliate_postcode = "" || affiliate_postcode.length < <?php echo ENTRY_POSTCODE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_POST_CODE; ?>";
    error = 1;
  }

  if (affiliate_city = "" || affiliate_city.length < <?php echo ENTRY_CITY_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_CITY; ?>";
    error = 1;
  }

<?php if (ACCOUNT_STATE == 'true') { ?>
  if (document.affiliate.affiliate_zone_id.options.length <= 1) {
    if (document.affiliate.affiliate_state.value == "" || document.affiliate.affiliate_state.length < 4 ) {
       error_message = error_message + "<?php echo JS_STATE; ?>";
       error = 1;
    }
  } else {
    document.affiliate.affiliate_state.value = '';
    if (document.affiliate.affiliate_zone_id.selectedIndex == 0) {
       error_message = error_message + "<?php echo JS_ZONE; ?>";
       error = 1;
    }
  }
<?php } ?>

  if (document.affiliate.affiliate_country_id.value == 0) {
    error_message = error_message + "<?php echo JS_COUNTRY; ?>";
    error = 1;
  }

  if (affiliate_telephone = "" || affiliate_telephone.length < <?php echo ENTRY_TELEPHONE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_TELEPHONE; ?>";
    error = 1;
  }
  
    if (document.affiliate.affiliate_cobrand_support_contact.value.length < <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_COBRANDING_SUPPORT_ERROR; ?>";
    error = 1;
  }

    if ( document.affiliate.affiliate_cobrand_billing_contact.value.length < <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_COBRANDING_BILLING_ERROR; ?>";
    error = 1;
  }

  if (error == 1) {
    alert(error_message);
    return false;
  } else {
    return true;
  }
}
//--></script>
<?php
  }
?>
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
    <td class="page-container" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
  if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $affiliate_query = tep_db_query("select * from " . TABLE_AFFILIATE . " where affiliate_id = '" . $_GET['acID'] . "'");
    $affiliate = tep_db_fetch_array($affiliate_query);
    $aInfo = new objectInfo($affiliate);
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr><?php echo tep_draw_form('affiliate', FILENAME_AFFILIATE, tep_get_all_get_params(array('action')) . 'action=update', 'post', 'enctype="multipart/form-data" onSubmit="return check_form();"'); ?>
        <td class="formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
<?php
    if (ACCOUNT_GENDER == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_GENDER; ?></td>
            <td class="main"><?php echo tep_draw_radio_field('affiliate_gender', 'm', false, $aInfo->affiliate_gender) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('affiliate_gender', 'f', false, $aInfo->affiliate_gender) . '&nbsp;&nbsp;' . FEMALE; ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_firstname', $aInfo->affiliate_firstname, 'maxlength="32"', true); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_lastname', $aInfo->affiliate_lastname, 'maxlength="32"', true); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_email_address', $aInfo->affiliate_email_address, 'maxlength="96"', true); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_telephone', $aInfo->affiliate_telephone, 'maxlength="32"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_fax', $aInfo->affiliate_fax, 'maxlength="32"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_HOMEPAGE; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_homepage', $aInfo->affiliate_homepage, 'maxlength="64"', true); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
   if (AFFILATE_INDIVIDUAL_PERCENTAGE == 'true') {
?>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_COMMISSION; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_COMMISSION; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_commission_percent', $aInfo->affiliate_commission_percent, 'maxlength="5"'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    }
?>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_COMPANY; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_COMPANY; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_company', $aInfo->affiliate_company, 'maxlength="32"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_COMPANY_TAXID; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_company_taxid', $aInfo->affiliate_company_taxid, 'maxlength="64"'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_PAYMENT_DETAILS; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
<?php
  if (AFFILIATE_USE_CHECK == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_PAYMENT_CHECK; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_payment_check', $aInfo->affiliate_payment_check, 'maxlength="100"'); ?></td>
          </tr>
<?php
  }
  if (AFFILIATE_USE_PAYPAL == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_PAYMENT_PAYPAL; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_payment_paypal', $aInfo->affiliate_payment_paypal, 'maxlength="64"'); ?></td>
          </tr>
<?php
  }
  if (AFFILIATE_USE_BANK == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_PAYMENT_BANK_NAME; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_payment_bank_name', $aInfo->affiliate_payment_bank_name, 'maxlength="64"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_PAYMENT_BANK_BRANCH_NUMBER; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_payment_bank_branch_number', $aInfo->affiliate_payment_bank_branch_number, 'maxlength="64"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_PAYMENT_BANK_SWIFT_CODE; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_payment_bank_swift_code', $aInfo->affiliate_payment_bank_swift_code, 'maxlength="64"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_PAYMENT_BANK_ACCOUNT_NAME; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_payment_bank_account_name', $aInfo->affiliate_payment_bank_account_name, 'maxlength="64"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_PAYMENT_BANK_ACCOUNT_NUMBER; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_payment_bank_account_number', $aInfo->affiliate_payment_bank_account_number, 'maxlength="64"'); ?></td>
          </tr>
<?php
  }
?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_STREET_ADDRESS; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_street_address', $aInfo->affiliate_street_address, 'maxlength="64"', true); ?></td>
          </tr>
<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_SUBURB; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_suburb', $aInfo->affiliate_suburb, 'maxlength="64"', false); ?></td>
          </tr>
<?php
  }
?>
          <tr>
            <td class="main"><?php echo ENTRY_CITY; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_city', $aInfo->affiliate_city, 'maxlength="32"', true); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_POST_CODE; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_postcode', $aInfo->affiliate_postcode, 'maxlength="8"', true); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_COUNTRY; ?></td>
            <td class="main"><?php echo tep_draw_pull_down_menu('affiliate_country_id', tep_get_countries(), $aInfo->affiliate_country_id, 'onChange="update_zone(this.form);"'); ?></td>
          </tr>
          <?php
          if (ACCOUNT_STATE == 'true') {
            ?>
            <tr>
              <td class="main"><?php echo ENTRY_STATE; ?></td>
              <td class="main"><?php echo tep_draw_pull_down_menu('affiliate_zone_id', tep_prepare_country_zones_pull_down($aInfo->affiliate_country_id), $aInfo->affiliate_zone_id, 'onChange="resetStateText(this.form);"'); ?></td>
            </tr>
            <tr>
              <td class="main">&nbsp;</td>
              <td class="main"><?php echo tep_draw_input_field('affiliate_state', $aInfo->affiliate_state, 'maxlength="32" onChange="resetZoneSelected(this.form);"'); ?></td>
            </tr>
            <?php
          }
          ?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo TITLE_AFFILIATE_COBRANDING; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_COBRANDING_COMPANY_LOGO; ?></td>
            <td class="main"><?php echo tep_draw_file_field('affiliate_cobrand_image');  if (tep_not_null($aInfo->affiliate_cobrand_image) && file_exists( DIR_FS_AFFILIATE_COBRAND . $aInfo->affiliate_cobrand_image )) { echo '<br>' . DIR_WS_AFFILIATE_COBRAND . $aInfo->affiliate_cobrand_image; }?>
            </td>
          </tr>
          <?php
          if (tep_not_null($aInfo->affiliate_cobrand_image) ) {
          ?>
          <tr>
            <td class="main"></td>
            <td class="main" valign="middle"><?php 
            if( file_exists( DIR_FS_AFFILIATE_COBRAND . $aInfo->affiliate_cobrand_image )) { 
                echo tep_image(DIR_WS_AFFILIATE_COBRAND . $aInfo->affiliate_cobrand_image) ;
            } else {
                echo '<span class="errorText">' . AFFILIATE_ERROR_IMAGE_MISSING .'</span>';
            }
            echo ' &nbsp; ' . tep_draw_hidden_field('affiliate_cobrand_image_existing', $aInfo->affiliate_cobrand_image) . tep_draw_checkbox_field('delete_cobrand_image','yes') . '&nbsp;' .  DELETE_COBRANDING_COMPANY_LOGO;;?></td>
          </tr>
          <?php
          }
          ?>
          <tr>
            <td class="main"><?php echo ENTRY_COBRANDING_COMPANY_NAME; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_cobrand_name', $aInfo->affiliate_cobrand_name); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_COBRANDING_SLOGAN; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_cobrand_slogan', $aInfo->affiliate_cobrand_slogan); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_COBRANDING_URL; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_cobrand_url', $aInfo->affiliate_cobrand_url); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_COBRANDING_SUPPORT_EMAIL; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_cobrand_support_email',$aInfo->affiliate_cobrand_support_email); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_COBRANDING_SUPPORT_PHONE; ?></td>
            <td class="main"><?php echo tep_draw_input_field('affiliate_cobrand_support_phone',$aInfo->affiliate_cobrand_support_phone); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_AFFILIATE_TEMPLATE; ?></td>
            <td class="main"><?php echo cre_template_switch('affiliate_template',$aInfo->affiliate_template); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
       <tr>
        <td align="right" class="main"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('action'))) .'">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_update.gif', IMAGE_UPDATE);?></td>
      </tr></form>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo tep_draw_form('search', FILENAME_AFFILIATE, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('search'); ?></td>
          </form></tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_AFFILIATE_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LASTNAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FIRSTNAME; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_COMMISSION; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_USERHOMEPAGE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $search = '';
    if ( isset($_GET['search']) && (tep_not_null($_GET['search'])) ) {
      $keywords = tep_db_input(tep_db_prepare_input($_GET['search']));
      $search = " where affiliate_id like '" . $keywords . "' or affiliate_firstname like '" . $keywords . "' or affiliate_lastname like '" . $keywords . "' or affiliate_email_address like '" . $keywords . "'";
    }
    $affiliate_query_raw = "select * from " . TABLE_AFFILIATE . $search . " order by affiliate_lastname";
    $affiliate_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS,
    $affiliate_query_raw, $affiliate_query_numrows);
    $affiliate_query = tep_db_query($affiliate_query_raw);
    while ($affiliate = tep_db_fetch_array($affiliate_query)) {
      $info_query = tep_db_query("select affiliate_commission_percent, affiliate_date_account_created as date_account_created, affiliate_date_account_last_modified as date_account_last_modified, affiliate_date_of_last_logon as date_last_logon, affiliate_number_of_logons as number_of_logons from " . TABLE_AFFILIATE . " where affiliate_id = '" . $affiliate['affiliate_id'] . "'");
      $info = tep_db_fetch_array($info_query);

      if (  (!isset($_GET['acID'])) || ($_GET['acID'] == $affiliate['affiliate_id'])  && (!isset($aInfo)) ) {
        $country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . $affiliate['affiliate_country_id'] . "'");
        $country = tep_db_fetch_array($country_query);

        $affiliate_info = array_merge($country, $info);

        $aInfo_array = array_merge($affiliate, $affiliate_info);
        $aInfo = new objectInfo($aInfo_array);
      }

      $tmp_c++;
      if (isset($_GET['acID'])) {
        $tmp_acID = $_GET['acID'];
      } else if (!isset($_GET['acID']) && $tmp_c == 1) {
        $tmp_acID = $affiliate['affiliate_id'];
      }

      if ( (is_object($aInfo)) && ($tmp_acID == $aInfo->affiliate_id) ) {
        echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID', 'action')) . 'acID=' . $aInfo->affiliate_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID')) . 'acID=' . $affiliate['affiliate_id']) . '\'">' . "\n";
      }
      if (substr($affiliate['affiliate_homepage'],0,7) != "http://") $affiliate['affiliate_homepage']="http://".$affiliate['affiliate_homepage'];
?>
                <td class="dataTableContent"><?php echo $affiliate['affiliate_id']; ?></td>
                <td class="dataTableContent"><?php echo $affiliate['affiliate_lastname']; ?></td>
                <td class="dataTableContent"><?php echo $affiliate['affiliate_firstname']; ?></td>
                <td class="dataTableContent" align="right">
                <?php if(isset($affiliate['affiliate_commission_percent'])){
                echo $affiliate['affiliate_commission_percent'];
                }else{
                echo  AFFILIATE_PERCENT_NOT_SET;
                }
                ;?>
                  %</td>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID', 'action')) . 'acID=' . $affiliate['affiliate_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'magnifier.png', ICON_PREVIEW) . '</a>'; echo '<a href="' . $affiliate['affiliate_homepage'] . '" target="_blank">' . $affiliate['affiliate_homepage'] . '</a>'; ?></td>
                <td class="dataTableContent" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_STATISTICS, tep_get_all_get_params(array('acID')) . 'acID=' . $affiliate['affiliate_id']) . '">' . tep_image(DIR_WS_ICONS . 'chart_line.png', ICON_STATISTICS) . '</a>&nbsp;'; if ( (is_object($aInfo)) && ($tmp_acID == $aInfo->affiliate_id) ) { echo tep_image(DIR_WS_IMAGES . 'arrow_right_blue.png', ''); } else { echo '<a href="' . tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID')) . 'acID=' . $affiliate['affiliate_id']) . '">' . tep_image(DIR_WS_IMAGES . 'information.png', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $affiliate_split->display_count($affiliate_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_AFFILIATES); ?></td>
                    <td class="smallText" align="right"><?php echo $affiliate_split->display_links($affiliate_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], tep_get_all_get_params(array('page', 'info', 'x', 'y', 'acID'))); ?></td>
                  </tr>
<?php
    if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
?>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE) . '">' . tep_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
                  </tr>
<?php
    }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  switch ($action) {
    case 'confirm':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</b>');
      $contents = array('form' => tep_draw_form('affiliate', FILENAME_AFFILIATE, tep_get_all_get_params(array('acID', 'action')) . 'acID=' . $aInfo->affiliate_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO . '<br><br><b>' . $aInfo->affiliate_firstname . ' ' . $aInfo->affiliate_lastname . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID', 'action')) . 'acID=' . $aInfo->affiliate_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_delete.gif', IMAGE_DELETE));
      break;
    default:
      if (isset($aInfo) && is_object($aInfo)) {
        $heading[] = array('text' => '<b>' . $aInfo->affiliate_firstname . ' ' . $aInfo->affiliate_lastname . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID', 'action')) . 'acID=' . $aInfo->affiliate_id . '&action=edit') . '">' . tep_image_button('button_page_edit.png', IMAGE_EDIT) . '</a><a href="' . tep_href_link(FILENAME_AFFILIATE, tep_get_all_get_params(array('acID', 'action')) . 'acID=' . $aInfo->affiliate_id . '&action=confirm') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a><a href="' . tep_href_link(FILENAME_AFFILIATE_CONTACT, 'selected_box=affiliate&affiliate=' . $aInfo->affiliate_email_address) . '">' . tep_image_button('button_email.gif', IMAGE_EMAIL) . '</a>');
        $affiliate_sales_raw = "SELECT count(*) as count, sum(affiliate_value) as total, sum(affiliate_payment) as payment
                                  from " . TABLE_AFFILIATE_SALES . " a,
                                       " . TABLE_ORDERS . " o
                                WHERE o.orders_id = a.affiliate_orders_id 
                                  and o.orders_status = " . AFFILIATE_PAYMENT_ORDER_MIN_STATUS . " 
                                  and a.affiliate_id = '" . $aInfo->affiliate_id . "' ";
        $affiliate_sales_values = tep_db_query($affiliate_sales_raw);
        $affiliate_sales = tep_db_fetch_array($affiliate_sales_values);
        $contents[] = array('text' => '<br>' . TEXT_DATE_ACCOUNT_CREATED . ' <b>' . tep_date_short($aInfo->date_account_created) . '</b>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ACCOUNT_LAST_MODIFIED . '<br><b>' . tep_date_short($aInfo->date_account_last_modified) . '</b>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_LAST_LOGON . '<br><b>'  . tep_date_short($aInfo->date_last_logon) . '</b>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_OF_LOGONS . '<b>' . $aInfo->number_of_logons . '</b>');
    $contents[] = array('text' => '' . TEXT_INFO_TEMPLATE_ASIGNED . ' <b>' . $aInfo->affiliate_template . '</b>');
        $contents[] = array('text' => '' . TEXT_INFO_COMMISSION . ' <b>' . $aInfo->affiliate_commission_percent . ' %</b>');
        $contents[] = array('text' => '' . TEXT_INFO_COUNTRY . ' <b>' . $aInfo->countries_name . '</b>');
        $contents[] = array('text' => '' . TEXT_INFO_NUMBER_OF_SALES . ' <b>' . $affiliate_sales['count'] . '</b>');
        $contents[] = array('text' => '' . TEXT_INFO_SALES_TOTAL . ' <b>' . $currencies->display_price($affiliate_sales['total'],'') . '</b>');
        $contents[] = array('text' => '' . TEXT_INFO_AFFILIATE_TOTAL . ' <b>' . $currencies->display_price($affiliate_sales['payment'],'') . '</b>');
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
        </table></td>
      </tr>
<?php
  }
?>
    </table></td>
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