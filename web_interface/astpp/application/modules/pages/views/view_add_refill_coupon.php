<?php include(FCPATH.'application/views/popup_header.php'); ?>
<script>
var login_type='<?php echo $login_type; ?>';
var accountid='<?php echo $accountid; ?>';
</script>
<script>

$('#submit').click(function(e){
    
    var refill_coupon_no =  $("#refill_coupon_no").val();
    //alert(refill_coupon_no);
    if(login_type != 0)
    {
        accountid =  $("#accountid").val();
    }
    if(refill_coupon_no !='' && accountid != ''){
	    if (!/^[0-9]+$/.test(refill_coupon_no)) {
	      $("#refill_coupon_number_error").html('<?php echo gettext("The Coupon Number field must contain only numbers."); ?>');
	      document.getElementById('refill_coupon_no').focus();
          return false;
	    }else{
            $.ajax({
			type: "POST",
			url: "<?= base_url()?>pages/user_refill_coupon_number/"+refill_coupon_no+"/"+accountid+"/",
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
                        window.location = "<?= base_url()?>products/products_topuplist/";	
					}
                    return false;
				}
			});
        } 
    }else{
        
        if(refill_coupon_no == '')
        {
            $("#refill_coupon_number_error").html('<?php echo gettext("The Coupon Number field is required."); ?>');
	        document.getElementById('refill_coupon_no').focus();
        }
        if(accountid == ''){
            $("#accountid_error").html('<?php echo gettext("Select Customer."); ?>');
	      //document.getElementById('accountid').focus();
        }
        return false;
    }

    return false;
});



$("#subsmit").click(function (e) {
	  var refill_coupon_no = document.getElementById('refill_coupon_number').value; 
      alert(refill_coupon_no);
      return false;
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
						$("#refill_coupon_number_error").html('<?php echo gettext("The  Coupon Number field have inactive refill coupon."); ?>');
					}else if (response ==2){
						$("#refill_coupon_number_error").html('<?php echo gettext("This Coupon Number is already in use."); ?>');
					}else if (response ==3){
						$("#refill_coupon_number_error").html('<?php echo gettext("This Coupon Number is not found."); ?>');
					}
					else{
						$("#amount_refill").html(data.amount);
						$("#new_balance").html(data.new_balance);
						jQuery.facebox({div: '#dialog'});
						// $("#dialog").dialog({modal: true, height: 230, width: 400 });
						// window.location = '<?= base_url()?>user/user_recharge_info/'+refill_coupon_no+'/';
						//document.getElementById("refill_coupon_recharge").href='<?php echo base_url()."/user/user_recharge_info"; ?>'; 
					}
				}
			});
	  }
	  }else{
	      $("#refill_coupon_number_error").html('<?php echo gettext("The Coupon Number field is required."); ?>');
	      document.getElementById('refill_coupon_number').focus();
	  }
	});
	


</script>
<section class="slice m-0">
   <div class="w-section inverse p-0">
    <div class="col-md-12 p-0 card-header">
      <h3 class="fw4 p-4 m-0"><?php echo gettext("Add Voucher"); ?></h3>
    </div>
  </div>    
</section>
<section class="slice m-0">
  <div class="w-section inverse p-4">
      <div class="pop_md col-12 pb-4">
      <form method="post" name="refill_coupon" id="refill_coupon" action="" enctype="multipart/form-data">
            <div class="col-12">
            <ul class="p-0">
            
               <?php if((!astpp_eq0($login_type)) && $login_type != 3){ ?>
                <div class="pb-4" id="floating-label">
                    <div class="col-md-12 form-group">
                        <label class="p-0 control-label"><?php echo gettext("Account"); ?></label>
                        <select name="accountid" id="accountid" class='col-md-12 form-control account form-control-lg' data-live-search='true' data-live-search-style="begins">
                                <option value=""><?php echo gettext("--Select--")?></option>
                                    <?php
                                    $whr= array("reseller_id" => $accountid,"status" => "0", "deleted" => "0", "type" => "0");
                                                $account = $this->db_model->getSelect("*", "accounts", $whr);
                                            foreach ($account->result_array() as $value) {
                                            if(isset($value['company_name']) && !empty($value['company_name'])){?>
                                            <option value="<?php echo $value['id']; ?>"><?php echo $value['company_name'].' '.'('.$value['number'].')'; ?></option>
                                            <?php }else { ?>
                                    <option value="<?php echo $value['id']; ?>"><?php echo $value['first_name'].' '.$value['last_name'] .'('.$value['number'].')'; ?></option>
                                    <?php   }
                                    ?>
                                    <?php } ?>
                            </select>
                    </div>
                    <div  class="ml-4" style="color:red;" id="accountid_error" name='accountid_error'></div>
                </div>
               <?php } ?>
                <div class="col-md-12">
                        <div class="form-group">	
                            <label class="p-0 control-label"><?php echo gettext('Coupon Number:')?></label>
                            <input type="input" class="col-md-12 form-control form-control-lg" name="refill_coupon_no" id="refill_coupon_no" maxlength="20">
                        </div>
                        <div  class="ml-4" style="color:red;" id="refill_coupon_number_error" name='refill_coupon_number_error'></div>
                </div>
              
            </ul>
          </div>
          
          <div class="col-12 mt-4 text-center">
            <button name="" type="submit" value="add" class="btn btn-success" id="submit" ><?php echo gettext("Add"); ?></button>
            <a onclick="return redirect_page('NULL')" type="button" value="cancel" class="btn btn-secondary mx-2 text-light" style="font-size: 14px;"><?php echo gettext("Close"); ?></a>
          </div>
          </form>
    </div>
    </div>
</section>
