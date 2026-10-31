<? extend('master.php') ?>
<? startblock('extra_head') ?>
<?php endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>

<?php startblock('content') ?>
<style>
	.StripeElement {
  background-color: white;
  height: 40px;
  padding: 10px 12px;
  border-radius: 4px;
  border: 1px solid transparent;
  box-shadow: 0 1px 3px 0 #e6ebf1;
  -webkit-transition: box-shadow 150ms ease;
  transition: box-shadow 150ms ease;
}

.StripeElement--focus {
  box-shadow: 0 1px 3px 0 #cfd7df;
}

.StripeElement--invalid {
  border-color: #fa755a;
}

.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;
}
.displayamount_error{
	font-size:10px;
	color:red;
}
</style>
<?php $ewallet_payment = "paypal";?>
<?php $permissioninfo = $this->session->userdata('permissioninfo');?> 
<section class="slice color-three pb-4">
  <div class="w-section inverse p-0">
	
        <div class="col-md-6 float-left pl-0">
          <input type="hidden" name="accountid" value="<?php echo $accountid; ?>" /> 
          <div class="card p-4">
            <div class="col-12">
            <?php
	    if(!empty($productdata)){
            foreach ($productdata as $key => $value) {?>
                <button class="test btn btn-outline-info mb-4 p-5 c-active"  id="<?php echo $value ['id'];?>" value="<?php echo $value ['id'];?>"><?php echo isset($value['price'])? $value['price']  :'0' ?></button>
              <?php } } ?>
                <a id ='add_voucher' href='<?php echo base_url()."pages/refill_coupon_add_view/"; ?>' rel="facebox_medium" class="btn btn-info mb-4 py-4 px-3" id=" " value="" style="line-height:200%;"><i class="fa fa-ticket fa-2x"></i> <br><span class=""><?php echo gettext("Use Voucher"); ?></span></a>
            </div>
          </div>
        </div>
        <div class="card float-right col-md-6">
            <div class="p-4">
		<form method="post" id="topup_form" action="<?php base_url().'pages/stripe_response/'?>">
                  <label class="font-weight-bold"><?php echo gettext("Selected Plan"); ?></label>
                  <br>
                  <?php echo gettext("Amount Without Tax"); ?><h1 id="amount_without_tax_label"></h1>
                  <?php echo gettext("Total Tax"); ?><h1 id="total_tax_label"></h1>
                  <span id="paypal_status"><?php echo gettext("Paypal Tax"); ?><span class="paypal_tax"></span><h1 id="paypal_tax_label"></h1></span>
                  <?php echo gettext("Amount With Tax"); ?> <h1 id="amount_with_tax_label"></h1>
	
			<input type="hidden" id="stripeToken" name="stripeToken" />
			<input type="hidden" id="stripeEmail" name="stripeEmail" />
			
			<input type="hidden" id="amount_without_tax" name="amount_without_tax" />
			<input type="hidden" id="total_tax" name="total_tax" />			
			<input type="hidden" id="amount_with_tax" name="amount_with_tax" />
			<input type="hidden" id="from_currency" name="from_currency" value="<?= $to_currency?>"/>
		</form>
            </div>
        </div>
	
        <div class="col-12 text-center mt-4">
          <?php if( (Common_model::$global_config['system_config']['paypal_status'] == '0')){ ?>
            <button type="button" id= "btn_submit" class="btn btn-success"><?php echo gettext("Pay with Paypal"); ?></button>
          <?php } ?>
        </div>
</div>
</section>
<? endblock() ?>
<? end_extend() ?>
<script src="https://js.stripe.com/v3/"></script>
<script src="https://checkout.stripe.com/checkout.js"></script>
<script type="text/javascript">

$(document).ready(function(){
  $('#paypal_status').hide();
});

		
  var id1 ='';
  $('#btn_submit').on("click",function() {
    if (id1 == '') {
      alert('please select any recharge amount');
      return false;
    } else {
      window.location.href = csrf_url("<?php echo base_url()."pages/proceed_payment/"; ?>"+id1); // CSRF token  
    }
  });


  $('body').on('click', 'button', function(e) {   
        id1 = $(this).val();
        $('.c-active').addClass('btn-outline-info').removeClass('btn-info');
        $(this).addClass('btn-info').removeClass('btn-outline-info');
        $.ajax({
        type : "POST",
        url  : "/pages/get_product_info/",
        data : { 
            id   : $(this).val()
        },
        success: function(result) {
          var objJSON = JSON.parse(result);
          if(objJSON.paypal_taxes > 0){
            $('#paypal_status').show();
          }
          document.getElementById('amount_with_tax').val = objJSON.amount_with_tax;
          document.getElementById('amount_without_tax').val = objJSON.amount_without_tax;
          document.getElementById('total_tax').val = objJSON.total_tax;
	            document.getElementById('amount_with_tax_label').innerHTML = objJSON.amount_with_tax;
          document.getElementById('amount_without_tax_label').innerHTML = objJSON.amount_without_tax;
          document.getElementById('total_tax_label').innerHTML = objJSON.total_tax;
          var paypal_tax = parseInt(objJSON.amount_with_tax) * objJSON.paypal_taxes / 100;
          $('.paypal_tax').html('('+objJSON.paypal_taxes+'%'+')');
          $('#paypal_tax_label').html(paypal_tax);
          var total_tax = parseInt(objJSON.amount_with_tax) + paypal_tax
          $('#amount_with_tax_label').html(total_tax);
        },
        error: function(result) {
          alert('error');
        }
  });
});


</script>
