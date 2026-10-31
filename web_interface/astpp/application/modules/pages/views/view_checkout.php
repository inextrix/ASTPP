<? extend('master.php') ?>
<? startblock('extra_head') ?>
<style>
.info-box-text {
   text-transform: uppercase;
}
.info-box-number {
   display: block;
   /*font-weight: bold;*/
   font-size: 18px;
}
</style>
<? endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<? startblock('content') ?> 

<section class="slice color-three pb-4">
   <div id="floating-label" class="w-section inverse p-0">

      <div id="package_view" class="card float-right col-md-8 py-4 mb-4">
         <div class="card pb-4 px-0">
            <h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext("Payment Method"); ?></h3>
            <div class="row px-4">
            
               <link href="<?=base_url('/assets/special.css')?>" rel="stylesheet" type="text/css" />
               <link href="<?=base_url('/assets/checkout.css')?>" rel="stylesheet" type="text/css" />
               <link rel="stylesheet" type="text/css" href="<?=base_url('/assets/base.css')?>" data-rel-css="" />
               <link rel="stylesheet" type="text/css" href="<?=base_url('/assets/example5.css')?>" data-rel-css="" />
               <div class="col-md-12 pt-4">

                  <div class="col-12">
			<?php if($product_info['setup_price'] == 0){?>
				<h2 class="text-center text-primary"><?php echo gettext("Its Free"); ?></h2>
				
			<?php } ?>
			<?php if($product_info['product_category'] != 3 && $product_info['setup_price'] > 0) {?>
                     <div class="input-group mb-4">
                       <div class="input-group-prepend">
                         <div class="input-group-text">
                           <input type="radio" name="payment_method" id="pay_with_account">
                         </div>
                       </div>
                       
                       <label class="m-0 p-4 border flex-grow-1" id="wallet-tab" for="pay_with_account">
                        <div class="card-logos m-top-5">
                           <img class="img-fluid" src="<?=base_url('/assets/images/wallet.png')?>">
                        </div>

                        <div class="radio-input">
                           <input name="payment_method" class="d-none" id="optionsRadios4" value="" required="" checked="" type="radio">
                           <h3 class="text-left"><?php echo gettext("Pay with Account"); ?></h3>
                        </div>
                       </label>
                     </div>
		<?php }?>
			<?php if($account_info['posttoexternal'] != 1  && $product_info['setup_price'] > 0){ ?>
                     <div class="input-group">
                       <div class="input-group-prepend">
                         <div class="input-group-text">
                           <input type="radio" name="payment_method" id="pay_with_paypal">
                         </div>
                       </div>

                       <label class="p-4 m-0 border flex-grow-1" id="paypal-tab" for="pay_with_paypal">
                        <div class="card-logos m-top-5">
                           <img src="<?=base_url('/assets/images/paypal_logo.png')?>">
                        </div>
			
                        <div class="radio-input">
                           <input name="payment_method" class="d-none" id="optionsRadios4" value="" required="" checked="" type="radio">
                           <h3 class="text-left"><?php echo gettext("Pay with Paypal"); ?></h3>
                        </div>
                       </label>
                     </div>
		<?php } ?>
                     <span class="text-danger float-left" id="error"></span>
                  </div>

				<?php if($product_info['product_category']== 3) {
							// top-up: PayPal only (the PayPal button was always hidden, and the account
							// balance form, which is hidden once PayPal is selected, cannot pay a top-up)
							$class="";
							$button_class="";
							$product_payment_class="d-none";
					}else{
							$class="d-none";
							$button_class="";
							$product_payment_class="pay_now";
					}?>

                  <div class="col-12 paypal_box <?php echo $class;?>">

			
	
                       <?php if($ewallet_payment == "paypal") {   ?>
                       <form method="post" name="product_add_form" id="product_add_form" action="<?= base_url()."pages/proceed_payment/".$product_info['id']."";?>">
						<input type = "hidden" value="paypal" name = "pay_from_account" />
			<input type = "hidden" value="1" id="product_quantity_paypal" class="product_quantity_paypal" name ="product_quantity" />
                        <div id="package_view" class="border border-top-0 p-4">      
                           <div class="col-md-12 form-group">
                              <label class="p-0 control-label" for="example5-email" data-tid="elements_examples.form.email_label"><?php echo gettext("Email");?></label>
                              <input name="email" id="cardholder" class="col-md-12 form-control form-control-lg m-0" name="product_name" value=" <?php  echo $account_info['email']; ?>" size="16" type="text"/>
                           </div>
                           <div class="col-md-12">
                              <label class="pointer shop-cart-agree">
                             
                            </label>
                         </div>     
                         <div class="col-md-6 mx-auto mt-4">
                         	<?php if( (Common_model::$global_config['system_config']['paypal_status'] == '0')){ ?>
                           <button class="btn btn-success btn-block <?php echo $button_class;?>" name="paypal_btn" value="Add Product" type="submit"> <i class="fa fa-paypal"></i> <?php echo gettext("Pay Now"); ?></button>
                           <?php } ?>
                        </div>      
                     </div>
                  </form>
                  <?php } ?>
   
                  </div>
                  <div class="col-12 <?php echo $product_payment_class;?> px-4">
                     <form method="post" name="product_add_form_payment" id="product_add_form_payment" action="<?= base_url()."pages/proceed_payment/".$product_info['id']."";?>">
                           <div class="col-md-6 p-4 mx-auto">
                              <div class="col-md-6 mx-auto mt-4">
                               <input type = "hidden" value="account_balance" name = "pay_from_account" />
				<input type = "hidden" value="1" id= "product_quantity" class="product_quantity" name = "product_quantity" />
                            </div>
                            <?php //if( (Common_model::$global_config['system_config']['paypal_status'] == '0')){ ?>
                            <button class="btn btn-success btn-block" type="button" onclick="validate_form()"> <i class="fa fa-money"></i> <?php echo gettext("Pay Now"); ?></button>
                            <?php //} ?>
                         </div>

                      </form>
                  </div>
           
          <div id="menu1" class="tab-pane fade active show">
            <main>
               <section class="">
                  <div class="">

            </div>
         </section>
      </main>
   </div>					    
</div>				  
</div>
</div>
</div>

<div class="col-md-4 float-left pl-0">
   <div class="card float-left p-4 col-12">
      <div class="card pb-4 px-0">
         <h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext("Product Information"); ?></h3>
         <div class="row px-4">
            <div class='col-md-12 form-group'>
               <label class="p-0 control-label"><?php echo gettext("Name"); ?></label>
               <input class="col-md-12 form-control form-control-lg m-0" name="product_name" value="<?php echo $product_info['name'] ?>" size="16" type="text" disabled/>
            </div>
            <div class='col-md-12 form-group'>
               <label class="p-0 control-label"><?php echo gettext("(Price+SetUp Fee)"); ?></label>
               <input class="col-md-12 form-control form-control-lg m-0" name="product_price" value=" <?php echo isset($product_info['setup_price'])?$this->common->convert_to_currency ( '', '', $product_info['setup_price'] ):'' ?>" size="16" type="text" disabled/>
            </div>
	    <?php if($product_info['product_category'] == 2) {?>
	    <div class='col-md-12 form-group numbers-row'>
               <label class="p-0 control-label"><?php echo gettext("Quantity"); ?></label>
		  <input class="form-control form-control-lg ml-2" type = "text" value="1" id = "product_temp_quantity" name = "product_temp_quantity" />
            </div>
	    <?php } ?>
         </div>
      </div>
   </div>
</div>
</div>
</section>
<script>
function validate_form(){
	var form_submit=false;
	var payment_method = document.getElementsByName("payment_method");
	var price_setup = "<?php echo $product_info['setup_price'] ?>";
	if(price_setup == 0){
		document.getElementById("product_add_form_payment").submit(); 	
	}
	if(payment_method[0].checked==false && payment_method[1].checked==false){
		document.getElementById("error").innerHTML="Please Select Payment Method";
	}else{
		var form_submit=true;
		document.getElementById("error").innerHTML="";
	}
	
	if(form_submit){
		document.getElementById("product_add_form_payment").submit(); 	
	}
}	
	
$(document).ready(function(){


     
  /* $("#another_card").click(function(){
	        	//alert($('#another_card').is(':checked'));
           if($('#another_card').is(':checked') == false){ 
             $(".chinput").prop('disabled',false);
             $(".chselect").prop('disabled',false);
             for(var i=0; i<input.length; i++) {                       
               $(input[i]).val('');
            }  
            $('.ex_month').prop('selectedIndex',0);
            $('.ex_year').prop('selectedIndex',0);		
            $("#card_number").val("<?php echo $card_info['cardnumber']; ?>").prop('disabled',true);
            $("#card_name").val("<?php echo $card_info['ccname']; ?>").prop('disabled',true);		
            $("#city").val("<?php echo $card_info['city']; ?>").prop('disabled',true);
            $("#province").val("<?php echo $card_info['state']; ?>").prop('disabled',true);
            $("#email").val("<?php echo $account_info['email']; ?>").prop('disabled',true);
            $("#address").val("<?php echo $card_info['address']; ?>").prop('disabled',true);
            $("#country").val("<?php echo $card_info['country']; ?>").prop('disabled',true);
         }
         if($('#another_card').is(':checked') == true){ 

	             // $(".chselect").prop('disabled',true);
            		//$(".chinput").prop('disabled',true);
                  $(".ex_month").val($("#selectedmonth").val());
                  $(".ex_year").val($("#selectedyear").val());
                  $("#card_number").val("");
                  $("#cvv_number").val("");
                  $("#card_name").val("");	            	
                  $("#city").val("");
                  $("#province").val("");
                  $("#email").val("");
                  $("#address").val("");
                  $("#country").val("");
               }	            
            });*/

   // $('input[type="radio"]').click(function() {
   //     if($(this).attr('id') == 'pay_with_paypal') {
   //          $('.paypal_box').removeClass('d-none');           
   //          $('.pay_now').addClass('d-none');
   //     }

   //     else {
   //          $('.paypal_box').addClass('d-none');   
   //          $('.pay_now').removeClass('d-none');           
   //     }
   // });
      $('input[type="radio"]').click(function() {
            if($(this).attr('id') == 'pay_with_paypal') {
                  $('.paypal_box').removeClass('d-none'); 
                  $('.payfast_box').addClass('d-none');          
                  $('.paypal_box').removeClass('d-none');           
                  $('.pay_now').addClass('d-none');
                  $('.iCredit_box').addClass('d-none'); 
                  $("#product_add_form_payment").hide();
            }

            else {
                  $('.paypal_box').addClass('d-none');   
                  $('.pay_now').removeClass('d-none');
                  $("#product_add_form_payment").show();           
            }
         });

});
$(function() {

  $(".numbers-row").append('<div class="btn btn-success button d-flex h-50 inc justify-content-center mit-20 ml-2"><i class="fa fa-plus align-self-center"><span class="d-none">+</span></i></div><div class="btn btn-danger button d-flex dec h-50 justify-content-center mit-20 ml-2"><i class="fa fa-minus align-self-center"></i></div>');

  $(".button").on("click", function() {

    var $button = $(this);
    var oldValue = $button.parent().find("input").val();

    if ($button.text() == "+") {
      var newVal = parseFloat(oldValue) + 1;
    } else {
     // Don't allow decrementing below zero
      if (oldValue > 1) {
        var newVal = parseFloat(oldValue) - 1;
      } else {
        newVal = 1;
      }
    }

    $button.parent().find("input").val(newVal);
	$('#product_quantity').val(newVal);
	$('#product_quantity_paypal').val(newVal);
  });

});
</script>
<? endblock() ?>    
<? end_extend() ?>
