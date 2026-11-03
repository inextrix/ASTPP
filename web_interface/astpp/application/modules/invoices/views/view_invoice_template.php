<style type="text/css">
@media print {
	::before, ::after {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	hr {
		margin-top: 20px;
		margin-bottom: 20px;
		border: 0;
		border-top: solid 0.3mm #eee;
	}
	address {
		margin-bottom: 20px;
		font-style: normal;
		line-height: 1.428571429;
	}
	table {
		max-width: 100%;
		background-color: transparent;
		border-collapse: collapse;
		border-spacing: 0;
	}
	table tr td {
		padding: 5px;
	}
	.table {
		width: 100%;
		margin-bottom: 20px;
	}
	.table>tr>th, .table>tr>th, .table>tr>th, .table>tr>td, .table>tr>td,
		.table>tr>td {
		padding: 8px;
		line-height: 1.428571429;
		vertical-align: top;
		border-top: solid 0.3mm #ddd;
	}
	.col-xs-6 {
		width: 49%;
		float: left;
		position: relative;
		min-height: 1px;
	}
	.col-md-6 {
		width: 50%;
		float: left;
		position: relative;
		min-height: 1px;
	}
	.panel {
		margin-bottom: 20px;
		background-color: #fff;
		border: solid 0.5mm #ddd;
		border-radius: 4px;
		-webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
		box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
	}
	.bg_gray {
		background: #f5f5f5;
	}
	.box_border {
		border: solid 0.3mm !important;
		border-color: #000000;
		padding: 10px;
	}
	.float-right {
		float: right !important;
	}
	.text-right {
		text-align: right;
	}
	.py-2 {
		padding: 20px 0;
	}
	.mb-20 {
		margin-bottom: 20px;
	}
}
</style>
<page backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm"  style="font-size: 8pt">
<table class="page_header">
	<tr>
            <?php if ($logo == '' || getimagesize('upload/'.$logo) === False) { ?>
                <td style="width: 50%; text-align: left">
			<img height="50" width="181" style="background-color: #ffffff;" src="assets/images/logo.png" alt="LOGO">
		</td>
            <?php } else { ?>
                <td style="width: 50%; text-align: left">
			<img height="50" width="181" style="background-color: #ffffff;" src="upload/<?php echo $logo; ?>" alt="LOGO">
		</td>
            <?php } ?>
                <td
			style="width: 50%; text-align: right; font-size: 12pt"><b><?php echo gettext('INVOICE :')?> <?php echo $invoicenumber;?></b>
		</td>
	</tr>
</table>

<table class="table py-2">
	<tbody>
		<tr>
			<td style="width: 50%; text-align: left">
				<address>
					<br>
                        <?php echo $cmp_name;?>,<br>
                            <?php echo $cmp_address;?>,<br>
                            <?php echo $cmp_city_zipcode;?><br>
                            <?php echo $cmp_province_country;?><br>
                     <?php echo gettext('Phone').":"; ?> <?php echo $cmp_telephone;?><br>
                            <?php echo $cmp_tax;?><br>
				</address>
			</td>

			<td style="width: 50%; text-align: right">
				<address>
					<b><?php echo gettext('Bill To').":"; ?></b><br>
                            <?php echo $fullname;?><br>
                            <?php
                            if (isset($address_1) && trim($address_1) != '') {
                                echo ucfirst($address_1);
                            }
                            ?>
                            <?php
                            if (isset($address_2) && trim($address_2) != '') {
				if(isset($address_1) && trim($address_1) != ''){
                                	echo ",<br>";
				}
                                echo ucfirst($address_2);
                            }
                            ?>
                            <?php
                            if (isset($city_postalcode) && $city_postalcode != '') {
				if(isset($address_2) && trim($address_2) != ''){
                                	echo ",<br>";
				}
                                echo ucfirst($city_postalcode);
                            }
                            ?>
                            <?php
                            if (isset($province_country) && $province_country != '') {
			        if(isset($city_postalcode) && $city_postalcode != ''){
                                	echo ",<br>";
				}
                                echo ucfirst($province_country);
                            }
                            ?>
                            <?php
                            if (isset($tax_number) && $tax_number != '') {
				if(isset($tax_number) && $tax_number != ''){
                                	echo ",<br>";
				}
                                echo $tax_number;
                            }
                            ?>
                    </address>
			</td>
		</tr>
	</tbody>
</table>
<table class="table">
	<tr style="width: 100%;" class="mb-20">
		<td class="box_border col-xs-6" style="padding: 0;">
			<table class="table">
				<tbody>
					<tr>
						<td class="col-md-6"><b><?php echo gettext('Invoice Number')?> </b></td>
						<td class="col-md-6 text-right"><?php echo $invoicenumber;?></td>
					</tr>
					<tr>
						<td class="col-md-6"><b><?php echo gettext('Invoice Date')?> </b></td>
						<td class="col-md-6 text-right"><?php echo astpp_date("d-m-Y", strtotime($invoice_date));?></td>
					</tr>
					<tr>
						<td class="col-md-6"><b><?php echo gettext('Invoice Due Date')?> </b></td>
						<td class="col-md-6 text-right"><?php echo astpp_date("d-m-Y", strtotime($invoice_due_date));?></td>

					</tr>
					<tr>
						<td class="col-md-6"><b><?php echo gettext('Account Number')?> </b></td>
						<td class="col-md-6 text-right"><?php echo $account_number;?></td>
					</tr>
				</tbody>
			</table>
		</td>
		<td class="box_border"
			style="width: 2%; padding: 0; border-top: none !important; border-bottom: none !important;">
		</td>

		<td class="box_border col-xs-6" style="padding: 0;">
			<table class="table">
				<tr>
					<td class="col-md-6"><?php echo gettext('This month credits')?> </td>
					<td class="col-md-6 text-right"><?php echo $this->common_model->calculate_currency ( $this_month_recharges, '', '', true, false );?></td>
				</tr>
				<tr>
					<td class="col-md-6"><?php echo gettext('Product & Services')?> </td>
					<td class="col-md-6 text-right"><?php echo $this->common_model->calculate_currency ( $product_service_total, '', '', true, false );?></td>
				</tr>
				<tr>
					<td class="col-md-6"><?php echo gettext('Total Calls Amount')?> </td>
					<td class="col-md-6 text-right"><?php echo (astpp_gt($total_calls_amount, 0))?$this->common_model->calculate_currency ($total_calls_amount, '', '', true, false):$this->common->currency_decimal(0);?></td>
				</tr>
				<tr class="bg_gray">
					<td class="col-md-6"><b><?php echo gettext('Total Amount (Inc. tax)')?></b></td>
					<td class="col-md-6 text-right"><?php echo $this->common_model->calculate_currency ( $this->common->currency_decimal($all_total_count), '', '', true, false );?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
        
        <?php if (trim($invoice_notes) != '') { ?>
            <table class="table py-2">
	<tr>
		<td style="width: 10%;"><b><?php echo gettext('Note:')?> </b></td>
		<td style="width: 90%;"><?php echo $invoice_notes;?></td>
	</tr>
</table>     
        <?php    } else { ?>
            <br>
        <?php } ?>

            

        <div class="panel">
	<table class="table table-condensed" style="padding: 0;">
		<tr class="bg_gray" style="width: 100%;">
			<td style="width: 15%; border-bottom: 1px solid #f5f5f5;"><b><?php echo gettext('Date')?></b></td>
			<td style="width: 55%; border-bottom: 1px solid #f5f5f5;"><b><?php echo gettext('Description')?></b></td>
			<td style="width: 15%; border-bottom: 1px solid #f5f5f5;"
				class="text-right"><b><?php echo gettext('Amount')?> (<?php echo $currency ?>)</b></td>
			
		</tr>
                <?php

		$total_amt = 0;
                foreach ($invoice_details_data as $invdatakey => $inv_data_value) { 
                    ?>
                      
		 <tr style="width: 100%;">
			<td style="width: 15%; border-bottom: 1px solid #f5f5f5;"><?php echo astpp_date("d-m-Y", strtotime($inv_data_value ['created_date']));?></td>
			<td style="width: 55%; border-bottom: 1px solid #f5f5f5;"><?php echo $inv_data_value ['description'];?></td>
			<td style="width: 30%; border-bottom: 1px solid #f5f5f5;"
				class="text-right">
			<?php if(astpp_eq0($posttoexternal) && astpp_gt($inv_data_value['credit'], 0) && $inv_data_value['credit'] != ''){ ?>
				 <?php echo $this->common_model->calculate_currency ( $this->common->currency_decimal(astpp_n($inv_data_value ['credit']) - astpp_n($debit_data)), '', '', true, false );?>
			
			<?php } else { ?>
                       	<?php 
                       		// ASTPPCOM-1369_kinjal_start
                       		$payment = $this->common_model->calculate_currency ( $this->common->currency_decimal($inv_data_value ['debit']), '', '', true, false );
							if(astpp_eq0($inv_data_value['order_item_id']) &&($inv_data_value['charge_type'] == "INVPAY" || $inv_data_value['charge_type'] == "" ) && astpp_eq0($inv_data_value['generate_type']) ){
								$payment = $this->common_model->calculate_currency ( $this->common->currency_decimal($inv_data_value ['credit']), '', '', true, false );	
							}else{
								$payment = $this->common_model->calculate_currency ( $this->common->currency_decimal($inv_data_value ['debit']), '', '', true, false );	
							}
							echo $payment; 
                       		// ASTPPCOM-1369_kinjal_end
                       	?>
			<?php } ?>
                    </td>
		</tr> 
	         <?php if(astpp_eq0($posttoexternal) && astpp_gt($inv_data_value['credit'], 0) && $inv_data_value['credit'] != '') {
			 $total_amt = astpp_n($total_amt)+(astpp_n($inv_data_value['credit'])-astpp_n($debit_data));
		?>
		<?php } else { ?>                 
                <?php  $total_amt = astpp_n($total_amt)+astpp_n($inv_data_value['debit']); } }  ?>
		

	    </table>
	<hr>
	<table class="table table-condensed" style="padding: 0;">
		<tr style="width: 100%;">
			<td style="width: 60%; border-bottom: 1px solid #f5f5f5;">&nbsp;</td>
			<td class="thick-line text-right" style="width: 20%; border-bottom: 1px solid #f5f5f5;"><?php echo gettext('Subtotal')?></td>
			<td class="thick-line text-right" style="width: 20%; border-bottom: 1px solid #f5f5f5;"><?php echo $this->common_model->calculate_currency($this->common->currency_decimal($total_amt));?></td>
		</tr>
                  <?php foreach ($invoicetax_details_data as $key => $value) { ?>
		
                    <tr style="width: 100%;">
			  <?php if($value ['description'] != '' || astpp_gt($value ['debit'], 0) ){ ?>
			<td style="width: 60%; border-bottom: 1px solid #f5f5f5;">&nbsp;</td>

			<td class="thick-line text-right" style="width: 20%; border-bottom: 1px solid #f5f5f5;"><?php echo $value ['description'];?></td>

			<td class="thick-line text-right" style="width: 20%; border-bottom: 1px solid #f5f5f5;"><?php echo $this->common_model->calculate_currency ($this->common->currency_decimal($value ['debit']), '', '', true, false );?></td>
		 <?php $total_amt = astpp_n($total_amt)+astpp_n($value['debit']); } ?>
                    </tr>
                <?php }   ?>
		 <?php if(astpp_gt($this_month_recharges, 0)){ ?>
                    <tr style="width: 100%;">
			<td style="width: 60%; border-bottom: 1px solid #f5f5f5;">&nbsp;</td>
			<td style="width: 20%; border-bottom: 1px solid #f5f5f5;"
				class="text-right"><b><?php echo gettext('Total')?></b></td>
			<td style="width: 20%; border-bottom: 1px solid #f5f5f5;"
				class="text-right"><b><?php echo $this->common_model->calculate_currency($this->common->currency_decimal($total_amt), '', '', true, false );?></b></td>
		</tr>
		<?php } else{?>
			<tr style="width: 100%;">
			<td style="width: 60%; border-bottom: 1px solid #f5f5f5;">&nbsp;</td>
			<td style="width: 20%; border-bottom: 1px solid #f5f5f5;"
				class="text-right"><b><?php echo gettext('Total')?></b></td>
			<td style="width: 15%; border-bottom: 1px solid #f5f5f5;"
				class="text-right"><b><?php echo $this->common_model->calculate_currency($this->common->currency_decimal($total_amt), '', '', true, false );?></b></td>
			</tr>

		<?php } ?>
            </table>
</div>

<div>
	<p>
                <?php
                echo "<b>".gettext("NOTE"). ": </b>";
                echo gettext($cmp_invoice_note);
                ?>
            </p>
</div>
</page>
