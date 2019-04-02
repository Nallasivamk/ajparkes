<?php

$admin_quotes = array();
$admin_quotes = $admin->get_admin_quotes($current_admin_id);

//print_r($admin_quotes);

?>

<table border="0" width="100%" cellspacing="0" cellpadding="2" class="data-table">
		<tr class="dataTableHeadingRow">
			<td class="dataTableHeadingContent" width="10%"><?php echo TABLE_HEADING_ORDERID; ?></td>
			<td class="dataTableHeadingContent" width="15%"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
			<td class="dataTableHeadingContent" width="10%" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
			<td class="dataTableHeadingContent" align="center" width="15%"><?php echo TABLE_HEADING_COMPANY; ?></td>
			<td class="dataTableHeadingContent" align="center" width="10%"><?php echo TABLE_HEADING_PAYMENT; ?></td>
			<td class="dataTableHeadingContent" width="15%" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
			<td class="dataTableHeadingContent" align="center" width="15%"><?php echo TABLE_HEADING_STATUS; ?></td>
			<td class="dataTableHeadingContent" align="right" width="5%"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
		</tr>
		
		<?php
		
		foreach($admin_quotes as $key=>$val) {
			
			$order_total = tep_get_orders_total($key);
									
			echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID','action')) . 'oID=' . $key.'&action=edit') . '\'">' . "\n";
		
		?>
			
			<td class="dataTableContent"><?php echo $key;  ?></td>
			<td class="dataTableContent"><?php echo $val['customers_name']; ?></td>
			<td class="dataTableContent"><?php echo $order_total; ?></td>
			<td class="dataTableContent"><?php echo $val['customers_company']; ?></td>
			<td class="dataTableContent"><?php echo $val['payment_method']; ?></td>				
			<td class="dataTableContent"><?php echo tep_datetime_short($val['date_purchased']); ?></td>
			<td class="dataTableContent"><?php echo $val['orders_status_name']; ?></td>
			<td class="dataTableContent" align="right">
			
				<?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action','oID')) . '&action=edit&oID='.$key, 'SSL') . '">' . tep_image(DIR_WS_ICONS . 'magnifier.png', ICON_PREVIEW) . '</a>&nbsp;'; ?>
				
			</td>
				
        </tr>
		
		<?php } ?>
		
	</table>	