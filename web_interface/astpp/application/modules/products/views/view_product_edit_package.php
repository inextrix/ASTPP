<? extend('master.php') ?>
<? startblock('extra_head') ?>

<? endblock() ?>

<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>        



<section class="slice color-three">
  <div class="w-section inverse p-0">
      <div class="container">
        <div class="row">
        <span id="error_msg" class=" success"></span>
              <div class="portlet-content"  id="update_bar" style="cursor:pointer; display:none">
                      <?php echo $form_batch_update; ?>
              </div>
            </div>
        </div>
    </div>
</section>
<section class="slice color-three padding-b-20">
  <div id="floating-label" class="w-section inverse p-0">
	<form method="post" name="product_add_form" id="product_edit_form" action="<?= base_url()."products/products_save/";?>">
	 <div class="col-md-4 float-left pl-0">      
	  <div class="card float-left p-4">      
          <div class="card pb-4 px-0 col-12">
              <h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Basic Information'); ?></h3>
		<div class="row px-4">
		<input class="col-md-12 form-control form-control-lg m-0" name="id" value="<?php echo $product_info['id']?>" size="16" type="hidden"/>
		 <div class='col-md-12 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Product Category'); ?></label>
                      <div class="col-md-12 form-control selectpicker form-control-lg p-0" >
                                <input class="col-md-12 form-control form-control-lg m-0" name= "product_category" value="<?php echo $this->common->get_field_name("name","category",array("id"=>$product_info['product_category']));?>" size="16" type="text" readonly/>
			</div>	
                  </div>

                  <div class='col-md-12 form-group'> 
                      <label class="p-0 control-label"><?php echo gettext('Name'); ?> *</label>
                      <input class="col-md-12 form-control form-control-lg m-0" value="<?php echo (isset($product_info['name']))?$product_info['name']:'' ?>" name="product_name" size="16" type="text"/>
			<div class="tooltips error_div pull-left no-padding" id="product_name_error_div" style="display: none;"><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i><span class="popup_error error  no-padding" id="product_name_error">  
 </span></div>	
                  </div>
			
		   <div class='col-md-12 form-group'> 
                      <label class="p-0 control-label"><?php echo gettext('Country')?></label>
                      <?php
								$country_arr = array("id" => "country_id", "name" => "country_id", "class" => "country_id");
								$country = form_dropdown_all($country_arr, $this->db_model->build_dropdown("id,country", "countrycode", "", ""), isset($country_id)?$country_id:"");
								echo $country;
								?>
			
                  </div>
                 
                  <div class='col-md-12 form-group'> 
                      <label class="p-0 control-label"><?php echo gettext('Description'); ?></label>
                       <input class="col-md-12 form-control form-control-lg m-0" value= "<?php echo (isset($product_info['description']))?$product_info['description']:'' ?>" name="product_description" size="16" type="textarea"/>
                  </div>

                  <div class='col-md-12 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Status'); ?></label>
                      <select  name="status" class="col-md-12 form-control selectpicker form-control-lg" data-live-search='true' datadata-live-search-style='begins'>
                          <option value="0" <?php if($product_info['status'] == '0'){ ?> selected="selected" <?php } ?>><?php echo gettext('Active'); ?></option>
			<option value="1" <?php if($product_info['status'] == '1'){ ?> selected="selected" <?php } ?>><?php echo gettext('Inactive'); ?></option>
                      </select>
                  </div>

                  <div class='col-md-12 form-group'> 
                      <label class="p-0 control-label"><?php echo gettext('Buy Cost'); ?> (<?php echo ($currency)?>)</label>
                      <input class="col-md-12 form-control form-control-lg m-0" value= "<?php echo (isset($product_info['buy_cost']))?$this->common->convert_to_currency ( '', '', $product_info['buy_cost'] ):'' ?>" name="product_buy_cost" size="16" type="text"/>
                  </div>

                  <div class='col-md-12 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Can be purchased?'); ?></label>
                      <select  name="can_purchase" class="col-md-12 form-control selectpicker form-control-lg" data-live-search='true' datadata-live-search-style='begins'>
                          <option value="0" <?php if($product_info['can_purchase'] == '0'){ ?> selected="selected" <?php } ?>><?php echo gettext('Yes'); ?></option>
			<option value="1" <?php if($product_info['can_purchase'] == '1'){ ?> selected="selected" <?php } ?>><?php echo gettext('No'); ?></option>
                      </select>
                  </div>
            
              </div>
             </div>
           </div>
          </div>

		<div id="package_view" class="card float-right col-md-8 py-4 mb-4">      
		  <div class="card pb-4 px-0">
		     <h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Product Details'); ?></h3>
		<div  class="row px-4">
		
                  <div class='col-md-6 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Reseller can resell'); ?></label>
                      <select  name="can_resell" class="col-md-12 form-control selectpicker  form-control-lg" data-live-search='true' datadata-live-search-style='begins'>
                        <option value="1" <?php if($product_info['can_resell'] == '1'){ ?> selected="selected" <?php } ?>><?php echo gettext('No'); ?></option>
			<option value="0" <?php if($product_info['can_resell'] == '0'){ ?> selected="selected" <?php } ?>><?php echo gettext('Yes'); ?></option>
                      </select>
                  </div>
		
                  <div class='col-md-6 form-group'> 
                      <label class="p-0 control-label"><?php echo gettext('Commission'); ?> (%)</label>
                     <input class="col-md-12 form-control form-control-lg m-0" name="commission" value="<?php echo (isset($product_info['commission']))?$product_info['commission']:'' ?>" size="16" type="text"/>
			<div class="tooltips error_div pull-left no-padding" id="commission_error_div" style="display: none;"><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i><span class="popup_error error  no-padding" id="commission_error">   </span></div>	
                  </div>
			<div class='col-md-6 form-group'> 
                      	  <label class="p-0 control-label"><?php echo gettext('Setup Fee').' ('.$currency.')'; ?></label>
                          <input class="col-md-12 form-control form-control-lg m-0" name="setup_fee" value = "<?php echo  $this->common->convert_to_currency ( '', '', $product_info['setup_fee'] )?>" size="16" type="text"/>
			<div class="tooltips error_div pull-left no-padding" id="setup_fee_error_div" style="display: none;"><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i><span class="popup_error error  no-padding" id="setup_fee_error">   </span></div>	
				
                  </div>
		   <div class='col-md-6 form-group'> 
                      <label class="p-0 control-label"><?php echo gettext('Price'); ?> (<?php echo ($currency)?>) *</label>
                     <input class="col-md-12 form-control form-control-lg m-0" name="price" value= "<?php echo ($product_info['price'] !='')?$this->common->convert_to_currency ( '', '', $product_info['price'] ):'' ?>" size="16" type="text"/>
			<div class="tooltips error_div pull-left no-padding" id="price_error_div" style="display: none;"><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i><span class="popup_error error  no-padding" id="price_error">   </span></div>	
		
                  </div>
		 <div class='col-md-6 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Billing Type'); ?></label>
                      <select  name="billing_type" class="col-md-12 form-control selectpicker form-control-lg" data-live-search='true' datadata-live-search-style='begins'>
                       <option value="0" <?php if($product_info['billing_type'] == '0'){ ?> selected="selected" <?php } ?>><?php echo gettext('One Time'); ?></option>
			<option value="1" <?php if($product_info['billing_type'] == '1'){ ?> selected="selected" <?php } ?>><?php echo gettext('Recurring'); ?></option>
                      </select>
                  </div>
                  <div class='col-md-6 form-group'> 
                      <label class="p-0 control-label"><?php echo gettext('Billing Days'); ?> *</label>
                        <input class="col-md-12 form-control form-control-lg m-0" name="billing_days" value= "<?php echo (isset($product_info['billing_days']))?$product_info['billing_days']:'' ?>" size="16" type="text"/>
			<div class="tooltips error_div pull-left no-padding" id="billing_days_error_div" style="display: none;"><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i><span class="popup_error error  no-padding" id="billing_days_error"></span></div>
                  </div>
		<div class='col-md-6 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Rate Group'); ?></label>
			<div class="dropdown bootstrap-select show-tick select field multiselectable  col-md-12 form-control form-control-lg dropup">
                      <select  name="product_rate_group[]"  multiple="multiple" class=" selectpicker select field multiselectable col-md-12 form-control form-control-lg" data-live-search='true' datadata-live-search-style='begins' disabled>
                        <?php $product_rategrp =explode(',',$product_info['apply_on_rategroups']);
				foreach($product_rate_group as $key => $rate_group) { 
					$selected ='';		
				if ( astpp_in_array($key, $product_rategrp)) {
					$selected = 'selected = selected';
				}

				?>
				<option value= "<?php echo $key; ?>" <?php echo $selected; ?>> <?php echo  $rate_group ?> </option>
			<?php }  ?>
                      </select>
		    </div>
                  </div>
                 <div class='col-md-6 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Apply on existing accounts'); ?> </label>
                      <select  name="apply_on_existing_account" class="col-md-12 form-control selectpicker form-control-lg" data-live-search='true' datadata-live-search-style='begins' disabled>
                        <option value="1" <?php if($product_info['apply_on_existing_account'] == '1'){ ?> selected="selected" <?php } ?>><?php echo gettext('No');?></option>
			<option value="0" <?php if($product_info['apply_on_existing_account'] == '0'){ ?> selected="selected" <?php } ?>><?php echo gettext('Yes');?></option>
                      </select>
                  </div>
		<div class='col-md-6 form-group'> 
                      <label class="p-0 control-label"><?php echo gettext('Free Minutes'); ?> *</label>
                     <input class="col-md-12 form-control form-control-lg m-0" name="free_minutes" value="<?php echo (isset($product_info['free_minutes']))?$product_info['free_minutes']:'' ?>" size="16" type="text"/>
			<div class="tooltips error_div pull-left no-padding" id="free_minutes_error_div" style="display: none;"><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i><span class="popup_error error  no-padding" id="free_minutes_error">   </span></div>	
                  </div>
         	 <div class='col-md-6 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Applicable For'); ?></label>
                      <select  name="applicable_for" class="col-md-12 form-control selectpicker form-control-lg" data-live-search='true' datadata-live-search-style='begins'>
                        <option value="0" <?php if($product_info['applicable_for'] == '0'){ ?> selected="selected" <?php }  ?>><?php echo gettext('Inbound'); ?></option>
                        <option value="1" <?php if($product_info['applicable_for'] == '1'){ ?> selected="selected" <?php }  ?>><?php echo gettext('Outbound'); ?></option>
			<option value="2" <?php if($product_info['applicable_for'] == '2'){ ?> selected="selected" <?php }  ?>>Both</option>
                      </select>
                  </div>
		  
		    <div class='col-md-6 form-group'>
                      <label class="p-0 control-label"><?php echo gettext('Release if no balance'); ?></label>
                      <select  name="release_no_balance" class="col-md-12 form-control selectpicker form-control-lg" data-live-search='true' datadata-live-search-style='begins'>
                        	<option value="1" <?php if($product_info['release_no_balance'] == '1'){ ?> selected="selected" <?php } ?>><?php echo gettext('No'); ?></option>
					<option value="0" <?php if($product_info['release_no_balance'] == '0'){ ?> selected="selected" <?php } ?>><?php echo gettext('Yes'); ?></option>
                      </select>
                  </div>

                 
		<div class="col-md-12 my-4">
                    <div class="col-md-4 float-left">
                      <button class="btn btn-outline-info btn-block" name="action" data-toggle="modal" data-target="#addDestination"  value="Add Destination" onclick="edit_package_destination();" type="button"> <i class="fa fa-plus-square-o"></i> <?php echo gettext('Add Destination'); ?> </button>
			
                    </div>
                    <div class="col-md-4 float-left">
                       <button class="btn btn-success btn-block" name="action" value="Save" type="submit"><?php echo gettext('Save'); ?> </button>
                    </div>
                    <div class="col-md-4 float-left">
                      <button class="btn btn-secondary mx-2 btn-block" name="cancel" onclick="return redirect_page('/products/products_list/')" value="Cancel" type="button">  <?php echo gettext('Cancel'); ?> </button>
                    </div>                        
                  </div>
		</div>
	 </form>
	<div class="modal fade" id="addDestination" role="dialog">
   	 <div class="card modal-dialog">
      <div id="floating-label" class="modal-content col-12">
       
          <div class="col-md-12 p-0 card-header">
            <h3 class="fw4 p-4 m-0"><?php echo gettext('Add Destination'); ?>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <img alt="close_image" src="/assets/images/closelabel.png" class="close_image" title="close" style="height:15px;width:15px;">
        </button>
        </h3>
          </div>
	<form action="#" id="product_package_pattern_grid" name = "product_package_pattern_grid">
    <div id="package_pattern" class="p-4">
    <div class="col-md-12 card pb-4">
    <div class="row">
		
		 <div class='col-md-6 form-group'>
                      <label class="p-0 control-label" data-toggle="tooltip" data-html="true" data-original-title= "Select the rate group for destination." data-placement="right"><?php echo gettext('Rate Group'); ?></label>
			<div class="dropdown bootstrap-select show-tick select field multiselectable  col-md-12 form-control form-control-lg dropup">
                      <select  name="destination_rategroups[]"  multiple="multiple" id="package_rate_group" class=" selectpicker select field multiselectable col-md-12 form-control form-control-lg" data-hide-disabled='true' data-actions-box='true' data-live-search='true' datadata-live-search-style='begins'>
                        <?php foreach($destination_rategroups as $key1 => $destination_rategroup) {  ?>
				<option value= "<?php echo $key1; ?>"> <?php echo  $destination_rategroup ?> </option>
			<?php } ?>
                      </select>
		    </div>
                  </div>
		<div class='col-md-6 form-group'>
       <!--Added Static Tooltip in Label-->
      <label class="p-0 control-label" data-toggle="tooltip" data-html="true" data-original-title= "Select the country to filter the rates according to the selected country." data-placement="right"><?php echo gettext('Country'); ?></label>
      <!-- End -->
			<select name="destination_countries[]" id="destination_countries" multiple="multiple" class="selectpicker select field multiselectable col-md-12 form-control form-control-lg" data-hide-disabled='true' data-actions-box='true' data-live-search='true' datadata-live-search-style='begins'>
			<?php $country_list =$this->db_model->getSelect("*","countrycode",""); 
				$country_info = $country_list->result_array(); ?>
				<?php foreach($country_info as $key => $country) {    ?>
				<option value= "<?php echo $country['id']; ?>"> <?php echo  $country['nicename'] ?> </option>
			<?php } ?>
		</select>
	   </div>
		<div class='col-md-6 form-group'>
      <!--Added Static Tooltip in Label-->  
      <label class="p-0 control-label" data-toggle="tooltip" data-html="true" data-original-title= "Select the call type to filter the rates according to selected code." data-placement="right"><?php echo gettext('Call Type'); ?></label>
      <!-- End -->
		<select name="destination_calltypes[]" id="destination_calltypes" multiple="multiple" class="selectpicker select field multiselectable col-md-12 form-control form-control-lg"  data-hide-disabled='true' data-actions-box='true' data-live-search='true' datadata-live-search-style='begins'>
					
					<?php $call_type_list =$this->db_model->getSelect("*","calltype",""); 
					$call_type_list = $call_type_list->result_array(); ?>
				<?php foreach($call_type_list as $key => $call_type) {    ?>
				<option value= "<?php echo $call_type['id']; ?>"> <?php echo  $call_type['call_type'] ?> </option>
				<?php } ?>
		</select>
		</div>
		<div class='col-md-6 form-group'>
      <!--Added Static Tooltip in Label-->
      <label class="p-0 control-label" data-toggle="tooltip" data-html="true" data-original-title= "Filter the rates using code (Prefix)." data-placement="right"><?php echo gettext('Code'); ?></label>
      <!-- End -->
			 <input class="col-md-12 form-control form-control-lg m-0" name="code" value= "" size="16" type="text"/>
			
		</div>
		<div class='col-md-6 form-group'>
       <!--Added Static Tooltip in Label-->
      <label class="p-0 control-label" data-toggle="tooltip" data-html="true" data-original-title= "Filter the rates using destination." data-placement="right"><?php echo gettext('Destination'); ?></label>
      <!-- End -->
			 <input class="col-md-12 form-control form-control-lg m-0" name="destination" value= "" size="16" type="text"/>
			
    </div>
    </div>
		</div>
   <div class="row mt-4">
    <div class="col-md-12">
       <div class="col-md-6 float-left">
           <button class="btn btn-success btn-block" id ="Search" name="Search" value="Search" onclick="load_product_prefixes();" type="button"><?php echo gettext('Search'); ?> </button>
       </div>
       <div class="col-md-6 float-left">
           <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal"><?php echo gettext('Close');?></button>
       </div>
     </div>

   </div>
      <div id="search_grid" class="col-md-12 p-0" style="display:none;">
     <div class="col-md-6 mx-auto">
         <button class="btn btn-line-warning btn-block mt-4" id ="add" name="add" value="Add" onclick="add_package_pattern();" type="button"><i class="fa fa-plus-circle fa-lg"></i><?php echo gettext('Add');?> </button>
     </div>

            <div class="col-md-12 p-0">
              <table id="product_pattern_grid" class="flex_grid" align="left" style="display:none;"></table>
            </div>
		</div>

		
	</div>
	</form>		
      </div>
	 
    </div>
  </div>
<form method="post" name="pattern_search_form" id ="pattern_search_form" >
	     
		</form> 
		<?php
						if (isset($validation_errors) && $validation_errors != '') { ?>
						<script>
							var ERR_STR = '<?php echo $validation_errors; ?>';
							print_error(ERR_STR);
						</script>
					<? } ?>
                </div>  


          <div class="card mt-4 p-4 col-12">
              <div class="col-12">
                <div class="row">
                  <div class="float-left col">
                    <span id="left_panel_delete" class="btn btn-line-danger" onclick="delete_multiple('/products/products_patterns_selected_delete/')">
                      <i class="fa fa-times-circle fa-lg"></i>
                      <?php echo gettext('Delete');?>
                    </span>
                  </div>  
                  <div class='col-md-6 float-right'>
                    <input type="text" name="left_panel_quick_search" id="left_panel_quick_search" class="form-control form-control-lg m-0" value="<?php echo $this->session->userdata('left_panel_search_package_patterns') ?>" placeholder="Search"/>
                  </div>
                </div>
              </div>

  		<div class="col-12">
			<form action="#">
                    <table id="prefix_list" class="flex_grid" align="left" style="display:none;"></table>
			</from>
                </div>  
                </div>  
  </div>
      
</div>

	
</section>
<script>

jQuery(document).ready(function() {  
var grid_list = "<?php echo base_url(); ?>products/products_pattern_list_json/<?php echo $edit_id; ?>";
var grid_list_rates = "<?php echo base_url(); ?>products/products_package_pattern/<?php echo $edit_id; ?>";
	build_grid("prefix_list",grid_list,<? echo $grid_field; ?>,"");
	build_grid("product_pattern_grid",grid_list_rates,<? echo $grid_fields; ?>,"");

	$("#product_category").change(function(){ 
		$('#product_edit_form').attr('action', "<?php echo base_url();?>products/products_edit/");
		$('#product_edit_form').submit();
	});
	$("#left_panel_quick_search").keyup(function(){ 
            quick_search("products/products_quick_search/");
  });

  //ASTPPCOM-1023 start
  $('.checkall').click(function () {
           $('.chkRefNos').prop('checked', $(this).prop('checked'));
  });
  //ASTPPCOM-1023 end

});




function load_product_prefixes(){  
document.getElementById("search_grid").style.display = "block"; 
var url =  "<?= base_url().'products/products_package_pattern_search/'?>";

  $.ajax({
        type:'POST',
        url: url,
	  data:$('#product_package_pattern_grid').serialize(), 
        success: function(response) { 
            $('.flex_grid').flexOptions({
                 newp:1
            }).flexReload();

        }
	
    });
}

 function add_package_pattern(){ 

                $.ajax({
                    type	: "POST",
                    url		: "<?= base_url(); ?>/products/products_patterns_add_info/<?= $edit_id ?>/",  
                    data	: '',
                    success : function(data){ 
                        if(data == 1)
                        { 
                            $('#product_pattern_grid').flexReload();
                            $('#prefix_list').flexReload();


                        } else{  
                            alert("Problem In Add Patterns to account.");
                        }
                    }
                });
            		
	}


</script>

<? endblock() ?>  
<? end_extend() ?> 
