<? extend('master.php') ?>
<? startblock('extra_head') ?>
<style>
body{
	
	background-color: #B7C1CF !important;
}
</style>
<script type="text/javascript">
    $(document).ready(function() {
      
        $("#gross_amount").change(function(e){
	    var amt = $("#gross_amount").val();
            var paypal_tax = '<?=$paypal_tax?>';
            var tax = (amt*paypal_tax)/100;
            var Final_amt = parseInt(amt)+parseFloat(tax);
            var from_cur = "<?= $from_currency?>";
            var to_cur = "<?= $to_currency?>";
            var new_amt= '0';
            
            $.ajax({
                type:'POST',
                url: "<?= base_url()?>/user/user_convert_amount/"+amt,
                data:"value="+new_amt, 
                success: function(response) {
		    
                    $("#custom").val(response);
                }
            })
            
            $.ajax({
                type:'POST',
                url: "<?= base_url()?>/user/user_payment/GET_AMT/",
                data:"value="+Final_amt, 
                success: function(response) {
					response = response.replace(",","");
                    $("#amt_in_currency").html(Final_amt +" "+from_cur + " To " + response +" "+to_cur);
                    $("#tax_amount").val(tax);
                    $("#tax_amount").val(tax);
					isNumeric = /^[-+]?(\d+|\d+\.\d*|\d*\.\d+)$/;
					if(!isNumeric.test(response.trim())){
							display_astpp_message(response.trim()+" "+to_cur,"notification");
							$("#amt_in_currency").html(Final_amt +" "+from_cur + " To " +" 0 "+to_cur);
							$("#amount").val("");	
					}
					else{
						$("#amount").val(response.trim());	
					}
                }
            })
        })
    })
function form_submit(){
    if($("#amount").val() > 0){
        return true;
    }else{
        alert("please enter recharge value");
    }

return false;
}    
</script>

<?php endblock() ?>
<?php startblock('page-title') ?>
<?=$page_title?>
<?php endblock() ?>
<?php startblock('content')?>

<div class="p-0">
	<section class="slice color-three">
		<div class="w-section inverse p-0">
			<div class="content">
				  <div class="col-md-12">
					  <?php if (isset($validation_errors)) {
							echo $validation_errors;
						}
						?>
						<div class="pop_md col-12 pb-4 pt-2">
							<div class="col-md-6 col-sm-6 mx-auto">
								<div class="col-12 px-12">
									<ul class="card p-0">
										<div class="pb-4" id="floating-label">
											<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Account Recharge')?></h3>
											
											<div class="col-md-12 form-group">
												<label class="p-0 control-label"><?php echo gettext('Enter Recharge Amount In')?> <?= $from_currency?>:</label>
												<input type="text" name="gross_amount" id="gross_amount" value="0" class="col-md-12 form-control form-control-lg">
											</div>
											
											<div class="col-md-12 form-group">
												<label class="p-0 control-label"><?php echo gettext('Tax Rate')?>: (<?= $paypal_tax; ?>%):</label>
												<input type="text" name="tax_amount" id="tax_amount" readonly value="0"  class="col-md-12 form-control form-control-lg">
											</div>
											

												<div class="col-md-12">
												<label class="col-md-3 col-md-12 ml-0 p-0 control-label"><?php echo gettext('Your Amount In')?> <?= $to_currency?>:</label>
												<span class="float-right" id="amt_in_currency" style="color:green;font-size:12px;">0 <?= $from_currency?> is equals to 0 <?= $to_currency?></span>
												</div>

											
											
											
											<?php /* the form goes to this server, which stores the payment and then sends the browser to PayPal */ ?>
											<form name="payment_form" id="payment_form" action="<?= base_url()?>user/user_payment/CHECKOUT/" method="POST" onSubmit="return form_submit();">
												  
												  <div class="col-md-12 form-group">
														<label class="p-0 control-label"><?php echo gettext('Net Payable Amount in')?> <?= $to_currency?>:</label>
														 <input type="text" name="amount" readonly id="amount" value="0"  class="col-md-12 form-control form-control-lg">
												  </div>
												  
												  <div> 
													  <input type="hidden" readonly name="custom" id='custom' value="">
												  </div>
										 
												  <div class="col-md-12">
															 <center>
																<input class="btn btn-line-parrot btn-lg" name="action" value="Recharge" type="submit">
															 </center>
														 
												  </div>
											  
												  <div class="col-md-12">
													   <center>
														<img src="<?php echo base_url(); ?>/assets/images/paypal_logo11.png" alt="paypal">
													  </center>			
												  </div>

											</form>
											
											
										</div>
									</ul>
								</div>
							</div>				
						</div>	 
				  </div>
			  </div>
		</div>
	</section>		  	  
</div>	

<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?> 
