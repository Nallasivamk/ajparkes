<?php
$sales_followup_orders = array();
$startDate = strtotime(date('Y-m-01'));
$endDate = strtotime(date("Y-m-d"));

if(isset($_POST["smtSalesFollowup"])) {	
	$startDate = strtotime($_POST["sales_followup_from"]);
	$endDate = strtotime($_POST["sales_followup_to"]);	
}


$sales_followup_orders = $admin->get_admin_sales_followup_orders($current_admin_id,$startDate,$endDate);

?>

<div>
	<?php echo tep_draw_form('frmSalesFollowup', "index_crm.php", '', 'post', ' id="frmSalesFollowup" ', 'SSL'); ?>
	<label>From: </label>
	<input type="text" name="sales_followup_from" id="sales_followup_from" value="<?php echo date('d-m-Y',$startDate); ?>" class="sales_followup_date_picker" />
	<label>To: </label>
	<input type="text" name="sales_followup_to" id="sales_followup_to" value="<?php echo date('d-m-Y',$endDate); ?>" class="sales_followup_date_picker" />
	<input type="submit" name="smtSalesFollowup" value="Submit"/>
	</form>
</div>

<table border="0" width="100%" cellspacing="0" cellpadding="2" class="data-table">
		<tr class="dataTableHeadingRow">
			<td class="dataTableHeadingContent" width="10%"><?php echo TABLE_HEADING_ORDERID; ?></td>
			<td class="dataTableHeadingContent" width="15%"><?php echo "Customer#"; ?></td>
			<td class="dataTableHeadingContent" width="10%" align="center"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
			<td class="dataTableHeadingContent" width="15%" align="center"><?php echo "Processed Date"; ?></td>
			<td class="dataTableHeadingContent" align="center" width="15%"><?php echo TABLE_HEADING_STATUS; ?></td>
			<td class="dataTableHeadingContent" align="right" width="5%"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
		</tr>
		
		<?php
		
		foreach($sales_followup_orders as $key=>$val) {
			
			$order_total = tep_get_orders_total($key);
									
			echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
		
		?>
			
			<td class="dataTableContent"><?php echo $key;  ?></td>
			<td class="dataTableContent"><?php echo $val['customers_id']; ?></td>
			<td class="dataTableContent" align="center"><?php echo $order_total; ?></td>			
			<td class="dataTableContent" align="center"><?php echo tep_datetime_short($val['processing_date']); ?></td>
			<td class="dataTableContent" align="center"><?php echo $val['orders_status_name']; ?></td>
			<td class="dataTableContent" align="right">
			
				<?php //echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action','oID')) . '&action=edit&oID='.$key, 'SSL') . '">' . tep_image(DIR_WS_ICONS . 'magnifier.png', ICON_PREVIEW) . '</a>&nbsp;'; ?>
				
			</td>
				
        </tr>
		
		<?php } ?>
		
	</table>	