<? extend('master.php') ?>
<? startblock('extra_head') ?>
<?php endblock() ?>
<?php startblock('page-title') ?>
<?= $page_title ?>
<?php endblock() ?>
<?php startblock('content') ?>
<style>
body {
	padding: 0;
}

table.datatable {
	border-collapse: separate;
	border-spacing: 1px;
}

table.datatable th {
	padding: 4px;
	background-color: #375c7c;
	font-weight: bold;
	text-align: center;
}
</style>
<script type="text/javascript" language="javascript">
function form_confirm() {
	var confirm_string = "<?php echo gettext('Are you sure want to confirm this invoice ? once you confirm it'); ?>, <?php echo gettext('can not able to edit invoice again.'); ?>";
	var answer = confirm(confirm_string);
	if(answer){
		document.getElementById("payment_form").submit();
	}
	else{
		return false;
	}

}
var cnt="<?php echo $auto_count; ?>";
function calculate() {
	var sum = 0;
	var auto_sum = 0;
	if(cnt >0){
		for (k = 1; k <= cnt; k++) {
			var auto_amt = document.getElementById('auto_invoice_amount_'+k).value;
			$('#auto_invoice_amount_'+k).keypress(function (event) {
				return isNumber(event, this)
			});
			if(auto_amt != ''){
				auto_sum += parseFloat(auto_amt);
			}
		}
	}
	var row_count= '<?= $row_count ?>';
	for (j = 1; j <= row_count; j++) {
		var amt = document.getElementById('invoice_amount_'+j).value;
		$('#invoice_amount_'+j).keypress(function (event) {
			return isNumber(event, this)
		});
		if(amt != ''){
			sum += parseFloat(amt);
		}
	}
	var total_sum = auto_sum + sum;
	$("#amount_val").html(total_sum.toFixed(2));
	document.getElementById("total_val").value = total_sum.toFixed(2);
	var abc= 0;
	var tax_cnt = '<?= $taxes_count; ?>';
	for (a = 0; a < tax_cnt; a++) {
		var taxes_rate = document.getElementById('total_tax_input_'+a).value;
		var tax_cut = total_sum*taxes_rate/100;
		abc += tax_cut;
		$("#total_tax_"+a).html(tax_cut.toFixed(2));
		document.getElementById("total_tax_input_"+a).value = tax_cut.toFixed(2);
	}
	var final_amt =total_sum+abc;
$("#amount_val_final").html(final_amt.toFixed(2));
document.getElementById("total_val_final").value = final_amt.toFixed(2);
} 

$(document).ready(function() {	 
	var row_count= '<?= $row_count ?>';
	for (i = 1; i <= row_count; i++) {
		$("#auto_invoice_date_"+i).datetimepicker({
			uiLibrary: 'bootstrap4',
			iconsLibrary: 'fontawesome',
			modal:true,
			format: 'yyyy-mm-dd HH:MM:ss',
			footer:true
     		});  
	}
	for (l = 1; l <= cnt; l++) {
		$("#invoice_from_date_"+l).datetimepicker({
			uiLibrary: 'bootstrap4',
			iconsLibrary: 'fontawesome',
			modal:true,
			format: 'yyyy-mm-dd HH:MM:ss',
			footer:true
     });  	

	}


});

function isNumber(evt, element) {
	var charCode = (evt.which) ? evt.which : event.keyCode

	if ((charCode != 45 || $(element).val().indexOf('-') != -1) && (charCode != 46 || $(element).val().indexOf('.') != -1) && ((charCode < 48 && charCode != 8) || charCode > 57)){
		return false;

	}
	else {
		return true;
	}

} 
</script>

<section class="slice color-three p-5">
	<div class="w-section inverse">
		<div class="row pb-5">
			<section class="slice color-three pb-4 mx-auto col-md-8"
				id="floating-label">

				<div class="col-md-12 card"
					style="border: 1px solid #ccc; border-radius: 5px;">

					<form name="payment_form" id="payment_form"
						action="/invoices/invoice_automatically_payment_edit_save/"
						method="POST">
						<br /> <input type='hidden' name="row_count"
							value="<?= $row_count ?>"> <input type='hidden'
							name="taxes_count" value="<?= $taxes_count; ?>"> <input
							type='hidden' name="count" value="<?= $count; ?>"> <input
							type='hidden' name="auto_count" value="<?= $auto_count; ?>"> <input
							type="hidden" id="auto_sum_input" name="auto_sum_input"
							value="<?= $invoice_auto_res; ?>"> <input type="hidden"
							id="total_tax_input" name="total_tax_input"
							value="<?= $total_tax; ?>"> <input type="hidden"
							id="total_val_final" name="total_val_final"
							value="<?= $amount; ?>"> <input type="hidden" id="total_val"
							name="total_val"
							value="<?= isset($total_credit_dis)?$total_credit_dis:''; ?>"> <input
							type='hidden' name="invoiceid" value="<?= $invoiceid; ?>"> <input
							type='hidden' name="accountid" value="<?= $accountdata['id']; ?>">
						<input type='hidden' name="reseller_id"
							value="<?= $accountdata['reseller_id']; ?>">
						<table class="invoice_table" width="100%" border="0">
							<tr class="form_invoice_head">

								<td width="50%">
									<table class="invoice_table1">
										<tr style="color: #163B80;">
											<th><h4>
													<b><?php echo gettext('Customer Details')?>  </b>
												</h4></th>
										</tr>
										<tr>
											<td><font style="font-weight: bold;"><?php echo gettext('Account Number')." :"; ?> </font>
												<font><span style="font-size: bold; color: green;"><?php echo $accountdata['number']; ?></span></font></font>

											</td>
										</tr>
										<tr>
											<td><font style="font-weight: bold;"><?php echo gettext('Name')." :"; ?> </font>
												<font color="#a09d9d"><?php echo $accountdata['first_name']; ?> <?php echo $accountdata['last_name']; ?> </font></font>

											</td>
										</tr>

										<tr>
											<td><font style="font-weight: bold;"><?php echo gettext('Email')." :"; ?></font>
												<font style="color: #a09d9d;">
				<?php
    if ($accountdata['email'] != "") {
        $attac_exp = explode(",", $accountdata['email']);
        foreach ($attac_exp as $key => $value) {
            if ($value != '') {
                echo $value;
            }
        }
    }
    ?> 

			</font></td>
										</tr>
									</table>
								</td>
								<td width="50%">
									<table class="invoice_table1  float-right">
										<tr style="color: #163B80;";>
											<th><h4>
													<b><?php echo gettext('Invoice Details')?> </b>
												</h4></th>
										</tr>
										<tr>
											<td><b><?php echo gettext('Invoice Number')?> : </b><span
												style="color: #a09d9d;"><?php echo $invoices['prefix']; ?><?php echo $invoices['number']; ?> </span>
											</td>
										</tr>
										<tr>
											<td><b><?php echo gettext('From Date')?>  :</b><span
												style="color: #a09d9d;"> <?php echo astpp_date('Y-m-d', strtotime($invoices['from_date'])) ; ?></span>
											</td>
										</tr>
										<tr>
											<td><b><?php echo gettext('Due Date')?> : </b><span
												style="color: #a09d9d;"><?php
            echo astpp_date('Y-m-d', strtotime($invoices['due_date']));
            ?></span></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<table width="50%" border="1" colspan="2"
							class="invoice_table4 float-right mt-5">
							<tr style='background-color: #375c7c; color: #fff; height: 30px;'>
								<td style="padding: 0 10px;"><b><?php echo gettext('Invoice Amount')?> :</b></td>
								<td style="padding: 0 10px;"><span class="float-right"><b><?php echo $this->common->currency_decimal($amount); ?> <?php  echo isset($invoice_details['account_currency'])?$invoice_details['account_currency']:''; ?></b>
								</span></td>
								<input type="hidden" name="total_amount" id="total_amount"
									class="article" value="<?php echo $amount; ?>">
								<input type="hidden" readonly name="recharge"
									value="paypal_invoice">
							</tr>
						</table>
						<div class="col-md-6 pl-0 mt-4">
							<label class=""><h5>
									<b style="color: #163B80;";><?php echo gettext('Invoice Note')?>:</b>
								</h5></label>
							<textarea id="invoice_notes" name="invoice_notes" style=''
								class="form-control form-control-lg"><?= $invoice_info['notes']; ?></textarea>
						</div>
						<br />
						<th><h5>
								<b style="color: #163B80;";><?php echo gettext('Invoice Item')?></b>
							</h5></th>
						<table class="datatable" width="100%" border="0" cellspacing="1"
							cellpadding="3">
							<tr style='background-color: #375c7c; color: #fff; height: 40px;'>
								<th width="25%"><?php echo gettext('Date')?></th>
								<th width="50%"><?php echo gettext('Description')?></th>
								<th><?php echo gettext('Amount')?></th>
							</tr>
	<?php
$j = 1;
foreach ($invoice_details as $value) {
    $id = $value['id'];
    $debit = $value['debit'];
    $created_date = $value['created_date'];
    ?>
	
		<tr>
								<td width="20" align="center"><input
									id="auto_invoice_date_<?= $j ?>"
									name="auto_invoice_date[<?= $id ?>]" type='text'
									class='form-control form-control-lg'
									value="<?= $created_date ?>"></td>
								<td><textarea id="auto_invoice_description_<?= $j ?>"
										name="auto_invoice_description[<?= $id ?>]"
										style='height: 35px;' class="form-control form-control-lg"><?php echo $value['description']; ?>  <?php echo $paypalid; ?></textarea>
								</td>
								<td><input type='text' id="auto_invoice_amount_<?= $j ?>"
									name="auto_invoice_amount[<?= $id ?>]" onkeyup="calculate()"
									class='form-control form-control-lg' style="text-align: right;"
									value="<?php echo $this->common->currency_decimal($this->common_model->calculate_currency($debit)); ?>">
								</td>
							</tr> 	

		<?php
    $j ++;
}

for ($i = 1; $i <= $row_count; $i ++) {
    if (isset($get_data['invoice_amount_' . $i]) && astpp_gt($get_data['invoice_amount_' . $i], 0)) { // HP:
        $invoice_amt = $this->common->currency_decimal($this->common_model->calculate_currency($get_data['invoice_amount_' . $i]));
    } else {
        $invoice_amt = '';
    }

    ?>
		<tr>
								<td width="20" align="center"><input
									id="invoice_from_date_<?= $i ?>"
									name="invoice_from_date_<?= $i ?>" type='text'
									class='form-control form-control-lg'
									value="<?= isset($get_data['invoice_from_date_'.$i])?$get_data['invoice_from_date_'.$i]:''; ?>">
								</td>
								<td><textarea id="invoice_description_<?= $i ?>"
										name="invoice_description_<?= $i ?>" style='height: 35px;'
										class="form-control form-control-lg"><?= isset($get_data['invoice_description_'.$i])?$get_data['invoice_description_'.$i]:''; ?></textarea>
								</td>
								<td style="text-align: right;"><input type="text" step="any"
									id="invoice_amount_<?= $i ?>" name="invoice_amount_<?= $i ?>"
									onkeyup="calculate()" class='form-control form-control-lg'
									style="text-align: right;" value="<?= $invoice_amt ?>"></td>

							</tr>

		<?php } ?>
		<tr style='background-color: #375c7c; color: #fff; height: 30px;'>
								<td colspan="2" style="text-align: right;"><?php echo gettext('Sub Total')?>:&nbsp; 
			</td>
								<td style="text-align: right; padding: 0 10px;"><div
										id='amount_val' name='amount_val'><?= $this->common->currency_decimal($this->common_model->calculate_currency($total_credit_sum)); ?></div>
								</td>
							</tr>

<?php

if (isset($taxes_to_accounts) && ! empty($taxes_to_accounts)) {
    $total_tax = 0;
    $tax = 0;
    foreach ($taxes_to_accounts as $tax_val) {
        $total_tax = astpp_n($total_tax) + astpp_n($tax_val['debit']);

        ?>
	<input type="hidden" id="total_tax_id_<?= $tax; ?>"
								name="total_tax_id_<?= $tax; ?>" value="<?= $tax_val['id']; ?>">
							<input type="hidden"
								id="description_total_tax_input_<?= $tax; ?>"
								name="description_total_tax_input_<?= $tax; ?>"
								value="<?= $tax_val['description']; ?>">

							<tr style='background-color: #375c7c; color: #fff; height: 30px;'>
								<td colspan="2" style="text-align: right;"><?php echo gettext('Tax'); ?> ( <?= $tax_val['description'] ?> ):&nbsp; 
		</td>

								<td style="text-align: right; padding: 0 10px;"><div
										id="total_tax_<?= $tax; ?>" name="total_tax_<?= $tax; ?>"><?= $this->common->currency_decimal($tax_val['debit']); ?></div>
								</td>
							</tr>

	<? $tax++; }} ?>
	<?php  ?>

	<tr style='background-color: #375c7c; color: #fff; height: 30px;'>
								<td colspan="2" style="text-align: right;"><?php echo gettext('Total Due')?>:&nbsp;
		</td>
		<?php  if(astpp_gt($total_tax, 0)){ ?>
		<td style="text-align: right; padding: 0 10px;"><div
										id='amount_val_final' name='amount_val_final'><?= $this->common->currency_decimal(astpp_n($total_credit_sum) + astpp_n($total_tax) ); ?></div>
		<?php }else{ ?>
		
								
								<td style="text-align: right; padding: 0 10px;"><div
										id='amount_val_final' name='amount_val_final'><?= $this->common->currency_decimal($total_credit_sum); ?></div>

		<?php } ?>
		</td>
							</tr>

						</table>

						</br>
						</br>
						<center>
							<div class="col-md-12 pb-4">
								<div class="mt-4 ">
									<input class="btn btn-success search_generate_bar" name="save"
										id="save" value=<?php echo gettext("Submit"); ?> type="submit"> <input
										class="btn btn btn-warning search_generate_bar margin-x-10"
										name="confirm" id="save" value="Confirm" type="button"
										onclick='form_confirm()'> <a href="../invoice_list/"> <input
										id="ok" class="btn btn-secondary" type="button" value=<?php echo gettext("Cancel"); ?>
										name="action">
									</a>
								</div>
							</div>
						</center>
						<br />
					</form>
				</div>
			</section>
			<script>
$(document).ready(function(){
	$(".page-wrap.p-4").removeClass("p-4");
});



</script>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>
