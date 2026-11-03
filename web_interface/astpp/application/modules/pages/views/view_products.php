<? extend('left_panel_setting_master.php') ?>
<? startblock('extra_head') ?>
<?php endblock() ?>
<? startblock('page-title') ?>

<? endblock() ?>
<?php startblock('content') ?>      
<script>
  
    $(document).ready(function(){
 $("#country_id").change(function () {	
        $("#product_submit").submit();	
    });
});
</script>
<div id="main-wrapper">                    
<section class="slice color-three pb-4">
  <div class="w-section inverse p-0">
            
          <form method="post" action="<?= base_url() ?>pages/services/" id="product_submit" onchange="this.form.submit()">
            <div class="col-md-12 main_wrapper_title color-three border_box d-flex"> 
                <div class="col-md-9 p-0 float-left align-self-center lh19">
                    <h2 class="m-0 text-light"><?php echo gettext("Products"); ?></h2>
                </div>
                <?php 
			if($category == 1){?>
                    <div class="align-self-center float-right col-md-3 p-0" id="floating-label">
		             <select class="col-md-12 form-control-lg form-control selectpicker" name="country_id" id="country_id" data-live-search="true">
		     <option value=""><?php echo gettext("--Select--")?></option>
                       <?php foreach($country as $key => $country_info) {    ?>
				<?php $selected = (astpp_loose_eq($country_id, $key)) ? /* PHP8 */ 'selected = selected' : '';?>
				<option value= "<?php echo $key; ?>" <?php echo $selected; ?>> <?php echo  strtolower(ucwords(gettext($country_info))) ?>  </option>

			<?php } ?>
                       
                      </select>  
                    </div>
                <? } ?>
            </div>
	</form>
      <div class="container-fluid px-sm-0 px-md-4">
          <div class="customer_packages">                
                  <div class="col-12">
					  
                      <div class="row no-gutters">
			<?php if(!empty($productdata)){
			       foreach($productdata as $key =>$value){ 	 ?>
				      <div class="col-sm-6 col-md-6 col-lg-4 mt-4">
                  <div class="card h-100 card_shine">
				          <div class="row no-gutters">
							  
				            <div class="col-7 px-0">
				              <div class="card-body p-3">
				                  <h3 class="h3 text-truncate text-dark card-title mb-1"><?php echo isset($value['name'])?$value['name']:'' ?></h3>
				               				                <p class="card-text"><?php echo isset($value['description'])?$value['description']:'' ?></p>
				              </div>
				            </div>
				            <div class="col-5 p-3 border-left">
				                <h4 class="text-info col-12"><?php echo $currency."  "; ?><?php echo isset($value['price'])?$this->common->convert_to_currency ( '', '', $value['price'] ):'' ?> <span style="font-size: 10px;" class="text-secondary text-right col-12">/ <?php echo isset($value['billing_days'])?$value['billing_days']:'' ?> <?php echo gettext("days"); ?></span></h4>

				                <div class="badge float-right py-2 mb-2 fw-n">(<?php echo astpp_eq0($value['billing_type'])?gettext("One Time"):gettext("Recurring") ?>)</div>
						
                        <div class="col-12">  
                            <a href="<?php echo base_url();?>/pages/checkout/<?php echo $value['id']?>" class="btn btn-block btn-info"><i class="fa fa-shopping-cart"></i> <?php echo gettext("Order"); ?></a>
                        </div>
                    </div>
                    
                    <div class="alert-secondary col-12 p-2 text-dark">
                      <div class="badge p-0 fw-n float-left">
                      <?php echo gettext("Setup Cost"); ?> : <span class=""><b><?php echo isset($value['setup_fee'])?$this->common->convert_to_currency ( '', '', $value['setup_fee'] ):'' ?></b></span>
                      </div>
                      <div class="badge p-0 fw-n float-right">
                      <?php echo gettext("Free Minutes"); ?>: <span class=""><b><?php echo isset($value['free_minutes'])?$value['free_minutes']:'' ?><?php echo gettext("min"); ?></b></span>
                      </div>
                    </div>
                  </div>
				          </div>
				      </div>
		  <?php } /*exit;*/ } else {?>
				<label class="error_label">
					<?php echo $product_msg;?>
				</label>
		<?php }?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
             </div>
         </div>
</section>
</div>

<? endblock() ?>  

<? end_extend() ?>  
<script type="text/javascript">
  $(document).ready(function(){
      $('.page-wrap').addClass('addon_wrap');

      $(".addon_title").removeAttr("data-ripple",""); 
  });
</script>
