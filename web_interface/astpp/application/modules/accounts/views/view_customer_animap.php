<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>

<script type="text/javascript" language="javascript">
  $(document).ready(function() {
    build_grid("animap_list","<?php echo base_url()."accounts/customer_animap_json/$edit_id/$accounttype/"; ?>",<? echo $grid_fields; ?>,"");
    $('.checkall').click(function () {
         $('.chkRefNos').prop('checked', $(this).prop('checked'));  
       });
    $('#ani_map').validate({
      rules: {
        number: {
          required: true
        }
      },
      messages:{
       number:{
         required: '<i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i><span class="popup_error error  p-0"><?php echo gettext('This field is required'); ?></span>'
       }
     },
     
     errorPlacement: function (error, element) {
       var name = $(element).attr("name");
       error.appendTo($("#" + name + "_validate"));
     }
   });
    $("#left_panel_quick_search").keyup(function(){
      quick_search("accounts/customer_details_search/"+'<?php echo $accounttype?>'+"_animap/");
    });
  });

</script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.page-wrap').addClass('addon_wrap');
    $("#number").removeClass("borderred");
    $('#submit').click(function(){
     if($.trim($('#number').val()) == '')
     {
       $('#number').addClass("borderred");
       
     }
   });
  });
</script>
<script type="text/javascript">
  $(document).ready(function(){
    $(".breadcrumb li a").removeAttr("data-ripple",""); 
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
								href="<?= base_url()."accounts/".strtolower($accounttype)."_edit/".$edit_id."/"; ?>"> <?php echo gettext('Profile');?></a>
							</li>
							<li class="breadcrumb-item active" aria-current="page"><a
								href="<?= base_url()."accounts/".strtolower($accounttype)."_animap/".$edit_id."/"; ?>"> <?php echo gettext('Caller IDs');?></a>
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

      <?php
    $permissioninfo = $this->session->userdata('permissioninfo');
    ?>	
      <div class="p-4 col-md-12">
				<div class="col-md-12 p-0">
         <?php  if((isset($permissioninfo['animap']['animap_detail']['create'])) && (astpp_eq0($permissioninfo['animap']['animap_detail']['create']))  && ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')){ ?>
          <div class="float-left" id="left_panel_add">
						<span class="btn btn-line-warning"> <i
							class="fa fa-plus-circle fa-lg"></i><?php echo gettext('Add');?></span>
					</div>
          <?php
        }
        if ((isset($permissioninfo['freeswitch']['fssipdevices']['delete'])) && (astpp_eq0($permissioninfo['freeswitch']['fssipdevices']['delete'])) && ($permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')) {
            ?>
          <div id="left_panel_delete"
						class="pull-left margin-t-0 padding-x-4"
						onclick="delete_multiple('/animap/animap_delete_multiple/')">
						<span class="btn btn-line-danger"> <i
							class="fa fa-times-circle fa-lg"></i>
              <?php echo gettext('Delete');?>
            </span>
					</div>
        <?php  } if((isset($permissioninfo['animap']['animap_detail']['search'])) && (astpp_eq0($permissioninfo['animap']['animap_detail']['search']))  && ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or $permissioninfo['login_type'] == '3'){ ?>
          <div id="show_search" class="float-right col-md-4 p-0">
						<input type="text" name="left_panel_quick_search"
							id="left_panel_quick_search"
							class="form-control form-control-lg m-0"
							value="<?php echo $this->session->userdata('left_panel_search_'.$accounttype.'_animap')?>"
							placeholder=<?php echo gettext("Search")?> />
					</div>
        <?php } ?>
      </div>
				<div class="col-12">
					<div class="portlet-content mt-4" id="left_panel_form"
						style="display: none">



						<div id="floating-label" class="card pb-4">
							<h3 class="bg-secondary text-light p-2 rounded-top"><?php echo gettext('Caller ID');?></h3>
							<form class="row px-4" method="post" name="ani_map" id="ani_map"
								action="<?= base_url() ?>accounts/customer_animap_action/add/<?= $edit_id ?>/<?= $accounttype; ?>"
								enctype="multipart/form-data">
								<div class='col-md-4'>
									<div class='col-md-12 form-group p-0'>
										<label class="no-padding control-label"><?php echo gettext('Caller ID');?> :</label>
										<input type="input" class="form-control" name="number"
											id="number" maxlength="20">
										<div id="number_validate"
											class="tooltips error_div float-left p-0"
											style="display: block;"></div>
									</div>
								</div>
								<div class="col-md-12">
									<center>
										<input class=" btn btn-success btn-lg" name="action"
											value=<?php echo gettext("Save");?> type="submit" id="submit">
									</center>
								</div>
							</form>
						</div>
					</div>
				</div>

				<div
					class="mt-4 col-md-12 color-three slice float-left content_border p-0">
					<div class="card col-md-12 pb-4">
						<table id="animap_list" align="left" style="display: none;"></table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<? endblock() ?>	

<? end_extend() ?>  
