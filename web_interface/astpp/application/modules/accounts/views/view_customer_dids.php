<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
      $('.page-wrap').addClass('addon_wrap');
  });
</script>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        build_grid("dids_list","<?php echo base_url()."accounts/customer_details_json/did/$edit_id/"; ?>",<? echo $grid_fields; ?>,"");
        
        $('.checkall').click(function () {
       		 $('.chkRefNos').prop('checked', $(this).prop('checked')); 
         });
        var country_id= $('#country_id').val();
        <?php  // ASTPPCOM-1333 Start ?>
		var accountid ='<?php echo $edit_id; ?>';
		<?php  // ASTPPCOM-1333 END ?>

            $("#country_id" ).change(function() {
                var country_id= $('#country_id').val();
    var url ='<?php echo base_url()."accounts/customer_did_country/"; ?>';
    var accountid ='<?php echo $edit_id; ?>';
    $.ajax({
        type: "POST",
        url: url,
        data:{ country_id : country_id, accountid : accountid },
                success:function(response) { 
                   var objJSON = JSON.parse(response);
                   if(objJSON.state_list && objJSON.state_list!=""){
                      $("#provience_id_search_drp").html(objJSON.state_list);
                      $("#provience_id_search_drp").prepend("<option value='' selected> <?php echo gettext('--Select--'); ?> </option>"); 
                      $('#provience_id_search_drp').selectpicker('refresh');
                  }else{
                      $("#provience_id_search_drp").html("");
                      $("#provience_id_search_drp").prepend("<option value='' selected> <?php echo gettext('--Select--'); ?> </option>"); 
                      $('#provience_id_search_drp').selectpicker('refresh');
                  }

                  if(objJSON.didlist && objJSON.didlist!=""){
                      $("#free_didlist").html(objJSON.didlist); 
                      $('.free_didlist').selectpicker('refresh'); 	
                  }else{
                      $("#free_didlist").html(""); 
                      $('.free_didlist').selectpicker('refresh'); 	
                  }      
              }
          });
});
            $("#left_panel_quick_search").keyup(function(){
                quick_search("accounts/customer_details_search/"+'<?php echo $accounttype?>'+"_did/");
            });
            $("#country_id" ).change();


            $("#provience_id_search_drp").change(function() {
				var country_id = $('#country_id').val();
				var provience = $('#provience_id_search_drp').val();
				var url_new ='<?php echo base_url() . "accounts/customer_did_country/"; ?>';
				$.ajax({
				type: "POST",
				url: url_new,
				// ASTPPCOM-1333 Start
				data:{ country_id : country_id,provience:provience,accountid:accountid},
				// ASTPPCOM-1333 END
				success:function(response) {
						var objJSON = JSON.parse(response);
						console.log(objJSON);
						if(objJSON.city_list && objJSON.city_list!=""){
							$("#city_id_search_drp").html(objJSON.city_list);
							$("#city_id_search_drp").prepend("<option value='' selected> <?php echo gettext('--Select--'); ?> </option>"); 
							$('#city_id_search_drp').selectpicker('refresh');
						}else{
							$("#city_id_search_drp").html("");
							$("#city_id_search_drp").prepend("<option value='' selected> <?php echo gettext('--Select--'); ?> </option>"); 
							// ASTPPCOM-1333 Start
							$('#city_id_search_drp').selectpicker('refresh');
							// ASTPPCOM-1333 END
						}

						if(objJSON.didlist && objJSON.didlist!=""){
							$("#free_didlist").html(objJSON.didlist); 
							$('#free_didlist').selectpicker('refresh'); 	
						}
				}});  
         });

		 
            $("#city_id_search_drp").change(function() {
             var country_id= $('#country_id').val();
             var provience= $('#provience_id_search_drp').val();
             var city=$('#city_id_search_drp').val();
             var url_new ='<?php echo base_url() . "accounts/customer_did_country/"; ?>';
             $.ajax({
              type: "POST",
              url: url_new,
              // ASTPPCOM-1333 Start
              data:{ country_id: country_id,provience:provience,city:city,accountid:accountid},
			  // ASTPPCOM-1333 END
              success:function(response) {
               var objJSON = JSON.parse(response);
               if(objJSON.didlist && objJSON.didlist!=""){
                  $("#free_didlist").html(objJSON.didlist); 
                  $('#free_didlist').selectpicker('refresh'); 	
              }
          }});  
         }); 
            $('#did_purchase').validate({
                rules: {
                    free_didlist: {
                        required: true
                    }
                },
                messages: {
                    free_didlist: { 
                      required: '<i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i><span class="popup_error error  p-0">This field is required</span>',
                  }
              },
              errorPlacement: function (error, element) {
               var name = $(element).attr("name");
               error.appendTo($("#" + name + "_validate"));
           }
       });
    });
    </script>
<style>
#err {
	height: 20px !important;
	width: 100% !important;
	float: left;
}

label.error {
	float: left;
	color: red;
	padding-left: .3em;
	vertical-align: top;
	padding-left: 0px;
	margin-top: -10px;
	width: 100% !important;
}
</style>
<? endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<? startblock('content') ?>
<?php $permissioninfo = $this->session->userdata('permissioninfo');?>
<div id="main-wrapper">
	<div id="content" class="container-fluid">
		<div class="row">
			<div class="col-md-12 color-three border_box">
				<div class="float-left m-2 lh19">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb m-0 p-0">
							<li class="breadcrumb-item"><a
								href="<?= base_url()."accounts/".strtolower($accounttype)."_list/"; ?>"><?= gettext(ucfirst($accounttype)."s"); ?></a></li>
							<li class="breadcrumb-item"><a
								href="<?= base_url()."accounts/".strtolower($accounttype)."_edit/".$edit_id."/"; ?>"> <?php echo gettext('Profile');?> </a>
							</li>
							<li class="breadcrumb-item active"><a
								href="<?= base_url()."accounts/".strtolower($accounttype)."_dids/".$edit_id."/"; ?>"><?= gettext($page_title); ?></a>
							</li>
						</ol>
					</nav>
				</div>
				<div class="m-2 float-right">
					<a class="btn btn-light btn-hight"
						href="<?= base_url()."accounts/".strtolower($accounttype)."_edit/".$edit_id."/"; ?>">
						<i class="fa fa-fast-backward" aria-hidden="true"></i> <?php echo gettext('Back');?></a>
				</div>
			</div>
			<div class="p-4 col-md-12">
				<div class="col-md-12 p-0">
           <?php  if((isset($permissioninfo['did']['did_list']['purchase'])) && (astpp_eq0($permissioninfo['did']['did_list']['purchase']))  || ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')){ ?>   
            <div class="float-left" id="left_panel_add">
						<span class="btn btn-line-warning"> <i
							class="fa fa-plus-circle fa-lg"></i> <?php echo gettext('Purchase');?></span>
					</div>
        <?php } ?>
        <?php  if((isset($permissioninfo['did']['did_list']['delete'])) && (astpp_eq0($permissioninfo['did']['did_list']['delete']))  || ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')){ ?>   
            <div class="pull-left margin-t-0 padding-x-4"
						id="left_panel_add"
						onclick="delete_multiple('/accounts/customer_did_delete/<?php echo $edit_id; ?>')")>
						<span class="btn btn-line-danger"> <i
							class="fa fa-times-circle fa-lg"></i><?php echo gettext('Delete');?></span>
					</div>
       <?php } ?>
       <?php  if((isset($permissioninfo['did']['did_list']['search'])) && (astpp_eq0($permissioninfo['did']['did_list']['search']))  && ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')){ ?>   
        <div id="show_search" class="float-right col-md-4 p-0">
						<input type="text" name="left_panel_quick_search"
							id="left_panel_quick_search"
							class="form-control form-control-lg m-0"
							value="<?php echo $this->session->userdata('left_panel_search_'.$accounttype.'_did')?>"
							placeholder=<?php echo gettext("Search"); ?> />
					</div>
    <?php } ?>
</div>
				<div
					class="mt-4 slice color-three float-left content_border col-md-12 p-0"
					id="left_panel_form" style="display: none;">
					<div id="floating-label" class="card pb-4">
						<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Purchase DID');?></h3>
						<form class="row px-4" method="post" id="did_purchase"
							name="did_purchase"
							action="<?= base_url()."accounts/customer_dids_action/add/$edit_id/$accounttype/"; ?>"
							enctype="multipart/form-data">
							<div class="col-md-3 form-group">
								<label for="Country" class="p-0 control-label"><?php echo gettext('Country');?> : </label>
            <?php
            $country_arr = array(
                "id" => "country_id",
                "name" => "country_id",
                "class" => "country_id"
            );
            $country = form_dropdown($country_arr, $this->db_model->build_dropdown_country_camel("id,country", "countrycode", "", ""), $country_id);
            echo $country;
            ?>
        </div>
							<div class='form-group col-md-3'>
								<label class="control-label p-0" id="provience"
									name="provience_didlist_command"><?php echo gettext('Province')?>: </label>
								<select name="provience" id="provience_id_search_drp"
									class="col-md-12 form-control form-control-lg selectpicker provience_id_search_drp selectpicker"
									data-live-search="true" tabindex="-98">
									<option></option>
								</select>

							</div>
							<div class='form-group col-md-3'>
								<label class="control-label p-0" id="city"
									name="city_didlist_command"><?php echo gettext('City')?>: </label>
								<select name="city" id="city_id_search_drp"
									class="col-md-12 form-control form-control-lg selectpicker city_id_search_drp selectpicker"
									data-live-search="true" tabindex="-98">
									<option></option>
								</select>

							</div>
							<div class="col-md-3">
								<div class="col-md-12 form-group p-0">
									<label class="p-0 control-label"><?php echo gettext('Available DIDs');?> : </label>
           <? echo $didlist; ?>
           <div id="free_didlist_validate"
										class="tooltips error_div float-left p-0"
										style="display: block;"></div>
								</div>
							</div>

							<div class="col-md-12">
								<center>
									<input class="btn btn-success btn-lg" name="action"
										value=<?php echo gettext("Purchase DID");?> type="submit">
								</center>
							</div>
						</form>
					</div>
				</div>
				<div
					class="col-md-12 color-three slice float-left content_border mt-4 p-0">
					<div class="card col-md-12 pb-4">
						<table id="dids_list" align="left" style="display: none;"></table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<? endblock() ?>	

<? end_extend() ?>
<script type="text/javascript">
  $(document).ready(function(){
      $(".breadcrumb li a").removeAttr("data-ripple",""); 
  });
</script>
