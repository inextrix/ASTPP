<? extend('master.php') ?>
<? startblock('extra_head') ?>
<link href="<?= base_url() ?>assets/css/shop_popup.css" rel="stylesheet" type="text/css"/>
<script src="<?= base_url() ?>assets/js/jquery-ui.min.js"></script>
<script type="text/javascript" language="javascript">
$(document).ready(function() {
        build_grid("refill_coupon_grid","",<? echo $grid_fields; ?>,"");
	$("#refill_coupon_recharge").click(function () {
	  var refill_coupon_no = document.getElementById('refill_coupon_number').value; 
	  if(refill_coupon_no !=''){
	    if (!/^[0-9]+$/.test(refill_coupon_no)) {
	      $("#refill_coupon_number_error").html('<?php echo gettext("The Coupon Number field must contain only numbers."); ?>');
	      document.getElementById('refill_coupon_number').focus();
	    }
	    else{
			$.ajax({
			type: "POST",
			url: "<?= base_url()?>user/user_refill_coupon_number/"+refill_coupon_no+"/",
			data:'',
			success:function(response) {
				var data = jQuery.parseJSON(response);
					if(response == 1){
						$("#refill_coupon_number_error").html('<?php echo gettext("The Coupon Number field have inactive refill coupon."); ?>');
					}else if (response ==2){
						$("#refill_coupon_number_error").html('<?php echo gettext("This Coupon Number is already in use."); ?>');
					}else if (response ==3){
						$("#refill_coupon_number_error").html('<?php echo gettext("This Coupon Number is not found."); ?>');
					}
					else{
						$("#amount_refill").html(data.amount);
						$("#new_balance").html(data.new_balance);
						jQuery.facebox({div: '#dialog'});
						
					}
				}
			});
	  }
	  }else{
	      $("#refill_coupon_number_error").html('<?php echo gettext("The Coupon Number field is required."); ?>');
	      document.getElementById('refill_coupon_number').focus();
	  }
	});
	

    });
    function test(){
		var refill_coupon_no = document.getElementById('refill_coupon_number').value;
	    window.location = csrf_url("<?= base_url()?>user/user_refill_coupon_action/"+encodeURIComponent(refill_coupon_no)+"/"); // CSRF token

	}
</script>
<style>
    #err
    {
         height:20px !important;width:100% !important;float:left;
    }
    label.error {
        float: left; color: red;
        padding-left: .3em; vertical-align: top;  
        padding-left:0px;
        margin-top:-10px;
        width:100% !important;
       
    }
</style>
<? endblock() ?>

<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>
<div class="p-0">   
<section class="slice color-three">
    <div class="w-section inverse p-0">
        <div class="content">
            <div class="col-md-12 p-0">
                
            </div>
        </div>
    </div>
</section>
</div>
<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
    			<div class="card col-md-12 pb-4">
                    <table id="refill_coupon_grid" align="left" style="display:none;"></table>
                </div>   				
	</div>
</section>
<div id="dialog" style="display:none;"  title="REFILL COUPON RECHARGE">
<div class="p-0">   
<section class="slice m-0">
 <div class="w-section inverse p-0 card-header">
       <div class="col-md-12 p-0 card-header">
	        <h3 class="fw4 p-4 m-0"><?php echo gettext('REFILL COUPON RECHARGE')?></h3>
	  </div>
  </div>    
</section>
<section class="slice color-three">
    <div class="w-section inverse p-0">
        <div class="content">
            <div class="col-md-12">
                <div class="pop_md col-12 py-4">
						<div class="col-12 px-12">
							<ul class="p-0 m-0" id="floating-label">
									<p class="text-center m-0"><b><?php echo gettext('You just recharged');?> <span id="amount_refill" name="amount_refill" style="color:red"></span></font>  <?php echo gettext('account with The new balance will be');?> <span id="new_balance" name="new_balance" style="color:red"></span></b></p>
										
										
									<div class="col-md-12">
										<div class="text-center">
										<input type= "button" class="btn btn-success" id= "submit_refill_coupon"  value = "Continue" onclick="test()"/>
										</div>
									</div>	
							</ul>
						</div>             			
				</div>
			</div>
		</div>
	</div>
</section>	
</div>			
<? endblock() ?>	

<? end_extend() ?>  

