<?php
$admin_calls = array();
$admin_calls = $admin->get_admin_calls($current_admin_id);

//print_r($admin_calls);

?>

<table border="0" width="100%" cellspacing="0" cellpadding="2" class="data-table">
		<tr class="dataTableHeadingRow">
				<td class="dataTableHeadingContent" valign="top" width="120"><?php echo TABLE_HEADING_NAME; ?></td>
				
				<td class="dataTableHeadingContent" valign="top"><?php echo TABLE_HEADING_DATE_CREATED; ?></td>
				
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_CREATED_BY; ?></td>
				
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_ASSIGNED_TO; ?></td>
              
			    <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_START_DATE; ?></td>
				
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_END_DATE; ?></td>
												
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_CALL_DURATION; ?></td>
				
				<td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_STATUS; ?></td>
				 
                <td class="dataTableHeadingContent" valign="middle"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
        </tr>
		
		<?php
		
		foreach($admin_calls as $key=>$val) {
									
			echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID','cID')) . 'callsID=' . $key.'&cID='.$val["customers_id"]) . '\'">' . "\n";
		
		?>
			
			<td class="dataTableContent"><?php echo $val['name'];  ?></td>
			<td class="dataTableContent"><?php echo tep_date_short($val['date_entered']); ?></td>
			<td class="dataTableContent"><?php echo $admin->get_admin_name($val['created_user_id']); ?></td>
			<td class="dataTableContent"><?php echo $admin->get_admin_name($val['assigned_user_id']); ?></td>
			<td class="dataTableContent"><?php echo $val['date_start']; ?></td>				
			<td class="dataTableContent"><?php echo $val['date_end']; ?></td>
			<td class="dataTableContent"><?php echo $val["duration_hours"] . " : " . $val["duration_minutes"]; ?></td>
			<td class="dataTableContent"><?php echo $val['status']; ?></td>
			<td class="dataTableContent" align="right">
			
				<?php echo '<a href="' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID', 'action','cID')) . 'callsID=' . $key . '&action=edit&cID='.$val["customers_id"], 'SSL') . '">' . tep_image(DIR_WS_ICONS . 'magnifier.png', ICON_PREVIEW) . '</a>&nbsp;'; ?>
				<!--
				<?php if (isset($cInfo) && is_object($cInfo) && ($key == $cInfo->calls_id)) { echo tep_image(DIR_WS_IMAGES . 'arrow_right_blue.png', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CALLS, tep_get_all_get_params(array('callsID','cID')) . 'callsID=' . $key.'&cID='.$val["customers_id"]) . '">' . tep_image(DIR_WS_IMAGES . 'information.png', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;-->
				
			</td>
				
        </tr>
		
		<?php } ?>
		
	</table>	