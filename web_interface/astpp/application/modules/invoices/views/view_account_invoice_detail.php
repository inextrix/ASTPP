<?php extend('master.php') ?>

<?php startblock('extra_head') ?>

<script type="text/javascript" language="javascript">
function get_alert_msg(id)
{
    confirm_string = '<?php echo gettext("are you sure to delete?"); ?>';
    var answer = confirm(confirm_string);
    return answer 
}
</script>



<style>
fieldset {
	text-align: center;
}

.invoice_heading {
	font-weight: bold;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
}
</style>
<?= endblock() ?>

    <?= startblock('page-title') ?>
        <?=$page_title?>
    <?= endblock() ?>
    
	<?= startblock('content') ?>
<br>

<div
	class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
	<div class="portlet-header ui-widget-header">
		&nbsp;&nbsp;<strong>I N V O I C E</strong>
	</div>
	<div class="portlet-content">
		<div class="hastable">
			<div align="center">
				<table width="100%" border="0" class="details_table">
					<tr class="invoice_heading">
						<td width="50%"><?=$invoiceconf['company_name']?></td>
						<td><div align="right"><?=$invoiceconf['website']?></div></td>
					</tr>
					<tr class="invoice_heading">
						<td width="50%"><?=$invoiceconf['address']?></td>
						<td><div align="right"><?=$invoiceconf['telephone']?></div></td>
					</tr>
					<tr class="invoice_heading">
						<td width="50%"><?=$invoiceconf['city']?>  <?=$invoiceconf['zipcode']?></td>
						<td><div align="right"><?=$invoiceconf['emailaddress']?></div></td>
					</tr>
					<tr class="invoice_heading">
						<td width="50%"><?=$invoiceconf['province']?>  <?=$invoiceconf['country']?></td>
						<td><div align="right"></div></td>
					</tr>
				</table>
				<hr />
				<p class="invoice_heading">I N V O I C E</p>
			</div>

			<div align="left">
				<table border="0" class="details_table">
					<tr class="invoice_heading">
						<td>
To : <?=$accountinfo['first_name']; ?> <?=$accountinfo['last_name']?>
</td>
					</tr>
					<tr>
						<td>
Company name : <?=$accountinfo['company_name']?>
</td>
					</tr>
					<tr>
						<td>
Addres : <?=$accountinfo['address_1']?>
</td>
					</tr>
					<tr>
						<td>
<?=$accountinfo['address_2']?>  <?=$accountinfo['address_3']?>
</td>
					</tr>
					<tr>
						<td>
<?=$accountinfo['city']?> <?=$accountinfo['postal_code']?>
</td>
					</tr>
					<tr>
						<td>
<?=$accountinfo['province']?>, <?=$accountinfo['country']?>
</td>
					</tr>
				</table>
			</div>
			<p>&nbsp;</p>
			<table width="100%" border="1" cellspacing="0" class="details_table">
				<tr class="invoice_heading">
					<td width="25%"><div align="center"><?php echo gettext('Card Number')?></div></td>
					<td width="25%"><div align="center"><?php echo gettext('Account Number')?></div></td>
					<td width="25%"><div align="center"><?php echo gettext('Invoice Number')?></div></td>
					<td width="25%"><div align="center"><?php echo gettext('Invoice Date')?></div></td>
				</tr>
				<tr>
					<td><div align="center"><?=$accountinfo['cc']; ?></div></td>
					<td><div align="center"><?=$accountinfo['number']; ?></div></td>
					<td><div align="center"><?=@$invoiceid?></div></td>
					<td><div align="center"><?=@$invoicedate?></div></td>
				</tr>
			</table>
			<p>&nbsp;</p>
			<table width="100%" class="details_table">
				<tr class="invoice_heading"
					style="background: url(../gray_lightness/images/ui-bg_highlight-soft_50_dddddd_1x100.png) repeat-x scroll 50% 50% #DDDDDD">
					<td width="20%"><?php echo gettext('Date & Time')?></td>
					<td width="20%"><?php echo gettext('Caller*ID')?></td>
					<td width="20%"><?php echo gettext('Called Number')?></td>
					<td width="20%"><?php echo gettext('Disposition')?></td>
					<td width="10%"><?php echo gettext('Duration')?></td>
					<td width="10%"><?php echo gettext('Charge')?></td>
				</tr>
     
      <?php
    if (astpp_sizeof($invoice_cdr_list) > 0) {
        foreach ($invoice_cdr_list as $key => $value) {
            ?>
            <TR>
					<TD><?=$value['callstart']?></TD>
					<TD><?=$value['callerid']?></TD>
					<TD><?=$value['callednum']?></TD>
					<TD><?=$value['disposition']?></TD>
					<TD><?=$value['billseconds']?></TD>
					<TD><div align="right"><?=$value['charge']?></div></TD>
				</TR>
      <?php

}
    }
    ?>
</table>
			<br>
			<table width="100%" class="details_table">
				<tr class="invoice_heading"
					style="background: url(../gray_lightness/images/ui-bg_highlight-soft_50_dddddd_1x100.png) repeat-x scroll 50% 50% #DDDDDD;">
					<td width="40%"></td>
					<td width="20%"><?php echo gettext('Title')?></td>
					<td width="20%"><?php echo gettext('Text')?></td>
					<td width="20%"><?php echo gettext('Fee')?></td>
				</tr>
    
      <?php
    if (astpp_sizeof($invoice_total_list) > 0) {
        foreach ($invoice_total_list as $key => $value) {
            ?>
            <TR>
					<TD></td>
					<TD><?=$value['title']?></TD>
					<TD><?=$value['text']?></TD>
					<TD><div align="right"><?=$this->common_model->calculate_currency($value['value'])?></div></TD>
				</TR>
     <?php

}
    }
    ?>
</table>

		</div>
	</div>
</div>
<? endblock() ?>	
    <? startblock('sidebar') ?>
<br />
<br />
<br />
<br />
<br />
<br />

<? endblock() ?>
    
<? end_extend() ?>  
