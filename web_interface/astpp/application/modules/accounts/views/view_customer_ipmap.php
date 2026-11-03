<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<link rel="stylesheet"
	href="<?php echo base_url(); ?>assets/css/style.css" type="text/css" />
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript">
  $(document).ready(function() {
    build_grid("ipsettings_list","<?php echo base_url()."accounts/customer_ipmap_json/$edit_id/$accounttype/"; ?>",<? echo $grid_fields; ?>,"");
    $('.checkall').click(function () {
        $('.chkRefNos').prop('checked', $(this).prop('checked')); 
      });
    jQuery.validator.addMethod("noSpace", function(value, element) { 
      return value.indexOf(" ") < 0 && value != ""; 
    }, "No space please and don't leave it empty");
      
          jQuery.validator.addMethod("IP_Validate", function() {
           var message_flag;
           var prefix=$("#prefix").val();        
           var ip =$("#ip").val();
           $.ajax({
             url: "<?=base_url(); ?>accounts/customer_validate_ip/",
             type: "post",
             async:false,
             data: {
              "prefix": prefix,
              "ip": ip,
            },
            success: function (data_response) {
             message_flag=data_response.trim();
           }
         });
           if(message_flag =='FALSE'){
             return false;
           }
           else{
             return true;
           }
         }, "<?php echo gettext('The IP field must contain a unique value.'); ?>"); 
          $('#ip_map').validate({
          rules: {
            ip: {
              required: true,
              IP4Checker: true,
              IP_Validate : true
            }
            
          },
          messages:{
           ip:{
            required: "<i class='fa fa-exclamation-triangle error_triangle'></i><span class='popup_error error  p-0'><?php echo gettext('The IP field is required.'); ?></span>"
          }
          
        },
        errorPlacement: function (error, element) {
          var name = $(element).attr("name");
          error.appendTo($("#" + name + "_validate"));
        }
        
      });
          $("#left_panel_quick_search").keyup(function(){
            quick_search("accounts/customer_details_search/"+'<?php echo $accounttype?>'+"_ipmap/");
          });
        });

      </script>
<script type="text/javascript">
        $(document).ready(function(){
          $('.page-wrap').addClass('addon_wrap');
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
	width: 100% !important;
}
</style>
<? endblock() ?>
  <? startblock('page-title') ?>
  <?= $page_title ?>
  <? endblock() ?>
  <? startblock('content') ?>
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
								href="<?= base_url()."accounts/".strtolower($accounttype)."_edit/".$edit_id."/"; ?>"><?php echo gettext('Profile');?></a>
							</li>
							<li class="breadcrumb-item active" aria-current="page"><a
								href="<?= base_url()."accounts/".strtolower($accounttype)."_ipmap/".$edit_id."/"; ?>"><?php echo gettext('IP Settings');?></a>
							</li>
						</ol>
					</nav>
				</div>
				<div class="m-2 float-right">
					<a class="btn btn-light btn-hight"
						href="<?= base_url()."accounts/".strtolower($accounttype)."_edit/".$edit_id."/"; ?>">
						<i class="fa fa-fast-backward" aria-hidden="true"></i><?php echo gettext('Back');?></a>
				</div>
			</div>


			<div class="p-4 col-md-12">
				<div class="col-md-12 p-0">
            <?php
            $permissioninfo = $this->session->userdata('permissioninfo');
            $currnet_url = current_url();

            if ((isset($permissioninfo['ipmap']['ipmap_detail']['create'])) && (astpp_eq0($permissioninfo['ipmap']['ipmap_detail']['create'])) || ($permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')) {
                ?>
              <div class="float-left" id="left_panel_add">
						<span class="btn btn-line-warning"> <i
							class="fa fa-plus-circle fa-lg"></i><?php echo gettext('Add');?></span>
					</div>
              <?php
            }
            if ((isset($permissioninfo['freeswitch']['fssipdevices']['delete'])) && (astpp_eq0($permissioninfo['freeswitch']['fssipdevices']['delete'])) || ($permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')) {
                ?>
              <div id="left_panel_delete"
						class="pull-left margin-t-0 padding-x-4"
						onclick="delete_multiple('/ipmap/ipmap_delete_multiple/')">
						<span class="btn btn-line-danger"> <i
							class="fa fa-times-circle fa-lg"></i>
                  <?php echo gettext('Delete');?>
                </span>
					</div>
            <?php

}
            if ((isset($permissioninfo['ipmap']['ipmap_detail']['search'])) && (astpp_eq0($permissioninfo['ipmap']['ipmap_detail']['search'])) && ($permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')) {
                ?>
              <div id="show_search" class="float-right col-md-4 p-0">
						<input type="text" name="left_panel_quick_search"
							id="left_panel_quick_search"
							class="form-control form-control-lg m-0"
							value="<?php echo $this->session->userdata('left_panel_search_'.$accounttype.'_ipmap')?>"
							placeholder=<?php echo gettext("Search"); ?> />
					</div>
            <?php } ?>   
          </div>
				<div class="col-12">
					<div class="portlet-content mt-4" id="left_panel_form"
						style="display: none">

						<div id="floating-label" class="card pb-4">
							<h3 class="bg-secondary text-light p-2 rounded-top"><?php echo gettext('New IP');?></h3>
							<form class="row px-4" method="post" name="ip_map" id="ip_map"
								action="<?= base_url()."accounts/customer_ipmap_action/add/$edit_id/$accounttype/" ?>">
								<div class='col-md-4 form-group'>
									<label class="no-padding control-label"><?php echo gettext('Name');?></label>
									<input class="col-md-12 form-control form-control-lg m-0"
										name="name" size="16" type="text" />
								</div>
								<div class='col-md-4'>
									<div class='col-md-12 form-group p-0'>
										<label class="no-padding control-label"><?php echo gettext('IP');?> *</label>
										<input id='ip'
											class="col-md-12 form-control form-control-lg m-0" name="ip"
											size="22" type="text">
										<div id="ip_validate"
											class="tooltips error_div float-left p-0"
											style="display: block;"></div>
									</div>
								</div>
								<div class='col-md-4'>
									<div class='col-md-12 form-group p-0'>
										<label class="no-padding control-label"><?php echo gettext('Prefix');?> </label>
										<input id='prefix'
											class="col-md-12 form-control form-control-lg m-0"
											name="prefix" size="16" type="text">
										<div id="prefix_validate"
											class="tooltips error_div float-left p-0"
											style="display: block;"></div>
									</div>

								</div>

								<div class="col-md-12">
									<center>
										<input class="btn btn-success btn-lg" name="action"
											value=<?php echo gettext("Save");?> type="submit">
									</center>
								</div>
							</form>
						</div>
					</div>
				</div>

				<div
					class="col-md-12 color-three slice float-left content_border p-0 mt-4">
					<div class="card col-md-12 pb-4">
						<table id="ipsettings_list" align="left" style="display: none;"></table>
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
