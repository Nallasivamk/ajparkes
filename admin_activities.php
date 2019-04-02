<?php
/*
  $Id: admin_activities.php,v 1.1.1.1 2004/03/04 23:38:51 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $oscid = '&' . tep_session_name() . '=' . $_GET[tep_session_name()];

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

	<script type="text/javascript" src="includes/javascript/jquery.js"></script>

	<script language="javascript" src="includes/general.js"></script>

	<script type="text/javascript">
		function popupWindow(url) {
			
			 window.open(url,'popupWindow','toolbar=yes,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=850,height=600,screenX=150,screenY=150,top=150,left=150');

		}
		
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
    <td valign="top" class="page-container"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td align="right"></td>
          </tr>
        </table></td>
      </tr>
	  
	  <tr><td>&nbsp;</td></tr>
	  <tr><td><h3>Recent activities&nbsp;</h3></td></tr>
	  
	  <tr>
        <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="data-table">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo "Admin"; ?>&nbsp;</td>
				<td class="dataTableHeadingContent"><?php echo "Type"; ?>&nbsp;</td>
				<td class="dataTableHeadingContent"><?php echo "Details"; ?>&nbsp;</td>
				<td class="dataTableHeadingContent"><?php echo "IP Address"; ?>&nbsp;</td>
				<td class="dataTableHeadingContent"><?php echo "Entry time"; ?></td>
              </tr>
<?php
  $aa_query_raw = "SELECT aa.*, aat.types_name FROM admin_activities aa LEFT JOIN admin_activities_types aat ON (aa.activities_types_id = aat.admin_activities_types_id) order by aa.admin_activities_id DESC";
  
  $aa_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $aa_query_raw, $aa_query_numrows);
  $aa_query = tep_db_query($aa_query_raw);
  while ($admin_activities = tep_db_fetch_array($aa_query)) {
	
	if ((!isset($_GET['aaID']) || (isset($_GET['aaID']) && ($_GET['aaID'] == $admin_activities['admin_activities_id']))) && !isset($oInfo) && (substr($action, 0, 3) != 'new')) {
      $oInfo = new objectInfo($admin_activities);
    }

    if (isset($oInfo) && is_object($oInfo) && ($admin_activities['admin_activities_id'] == $oInfo->admin_activities_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link("admin_activities.php", 'page=' . $_GET['page'] . '&aaID=' . $oInfo->admin_activities_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link("admin_activities.php", 'page=' . $_GET['page'] . '&aaID=' . $admin_activities['admin_activities_id']) . '\'">' . "\n";
    }

?>
				<td class="dataTableContent">
				<?php 
					$responsible = tep_get_admin_details($admin_activities["admin_id"]);
					echo $responsible["admin_firstname"] . " " . $responsible["admin_lastname"];
				?>				
				</td>
				
				<td class="dataTableContent"><?php echo $admin_activities['types_name']; ?></td>
				
				<td class="dataTableContent"><?php echo $admin_activities['details']; ?></td>
				
				<td class="dataTableContent"><?php echo $admin_activities['ip_address']; ?></td>
				
                <td class="dataTableContent"><?php echo date("d-m-Y H:i:s",$admin_activities['time_entry']); ?></td>
              </tr>
<?php
  }
?>          </table>
            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="data-table-foot">
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $aa_split->display_count($aa_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $aa_split->display_links($aa_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table>
		</td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_PRINT_BATCH . '</b>');
      $contents = array('form' => tep_draw_form('admin_activities', "admin_activities.php", 'page=' . $_GET['page'] . '&aaID=' . $oInfo->admin_activities_id  . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $oInfo->admin_activities_id . '</b>');
      break;
    default:
	  if (isset($oInfo) && is_object($oInfo)) {
		  
		$heading[] = array('text' => '<b>' . $oInfo->admin_activities_id . '</b>');
				
		$contents[] = array('align' => 'center', 'text' => '');		
		
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
