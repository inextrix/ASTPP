<script type="text/javascript">
  $(document).ready(function() {
	$("#add_account").validate({
		rules: {
			brand: "required",
			value: "required",
			count: "required"
		}
	});
  });
	</script>
<style type="text/css">
table.details_table th {
	text-align: left;
}

table.details_table td {
	text-align: center;
}
</style>
<div
	class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
	<div class="portlet-header ui-widget-header"><?= $page_title; ?><span
			class="ui-icon ui-icon-circle-arrow-s"></span>
	</div>
	<div class="portlet-content">
		<table class="details_table" style="width: 700px;">
			<tr>
				<th><?php echo gettext('Refill Coupon Number')?></th>
				<td><?=$refill_coupon_details['number']?></td>
				<th><?php echo gettext('Amount')?></th>
				<td><?=$refill_coupon_details['amount']?></td>
				<th><?php echo gettext('Tag')?></th>
				<td><?=$refill_coupon_details['tag']?></td>
				<th><?php echo gettext('Currency')?></th>
				<td><?=$refill_coupon_details['currency']?></td>
			</tr>
			<tr>
				<th><?php echo gettext('Start Date')?></th>
				<td><?=$refill_coupon_details['start_date']?></td>
				<th><?php echo gettext('Expiry Date')?></th>
				<td><?=$refill_coupon_details['expiry_date']?></td>
				<th><?php echo gettext('Callingcard')?></th>
				<td><?=$refill_coupon_details['callingcard']?></td>
			</tr>
			<th><?php echo gettext('Status')?></th>
			<td><?php if (astpp_eq0($refill_coupon_details['status'])) { echo "Inactive"; } elseif ($refill_coupon_details['status'] == 1) { echo "Active"; } elseif ($refill_coupon_details['status'] == 2) { echo "Inuse"; } else { echo "Expired"; }?></td>
			</tr>
		</table>
        
        <?php if (isset($cdrs) && is_array($cdrs)) {?>
        <table class="details_table" style="width: 700px;">
			<tr>
				<th><?php echo gettext('Destination')?></th>
				<th><?php echo gettext('Disposition')?></th>
				<th><?php echo gettext('CallerID')?></th>
				<th><?php echo gettext('Starting Time')?></th>
				<th><?php echo gettext('Length in Seconds')?></th>
				<th><?php echo gettext('Cost(CAD)')?></th>
			</tr>
        <?php

foreach ($cdrs as $cdr) {
                $debit = '';
                $debit = (astpp_n($cdr['debit']) / 10000);
                $debit = astpp_n($debit) + 0;
                ?>
        <tr>
				<td><?=$cdr['destination']?></td>
				<td><?=$cdr['disposition']?></td>
				<td><?=$cdr['clid']?></td>
				<td><?=$cdr['callstart']?></td>
				<td><?=$cdr['seconds']?></td>
				<td><?=$cdr['debit']?></td>
			</tr>
        <?php }?>
        </table>
        <?php }?>
    
  </div>
</div>
