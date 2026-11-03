<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>

<script type="text/javascript" language="javascript">



    $(document).ready(function() {
       $('a[rel*=facebox]').facebox();
       build_grid("sidevices_grid","<?php echo base_url()."accounts/customer_details_json/freeswitch/$edit_id/"; ?>",<? echo $grid_fields ?>,"");
       $('.checkall').click(function () {
	$('.chkRefNos').prop('checked', $(this).prop('checked')); 
});
       $("#left_panel_quick_search").keyup(function(){
        quick_search("accounts/customer_details_search/"+'<?php echo $accounttype ?>'+"_sipdevices/");
    });
	
});
</script>
<script type="text/javascript">
  $(document).ready(function(){
      $(".breadcrumb li a").removeAttr("data-ripple",""); 
  });
</script>
<? endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<? startblock('content') ?>  
<?php

$permissioninfo = $this->session->userdata('permissioninfo');
$currnet_url = current_url();
?>
<div id="main-wrapper">
	<div id="content" class="container-fluid">
		<div class="row">
			<div class="col-md-12 color-three border_box">
				<div class="float-left m-2 lh19">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb m-0 p-0">
							<li class="breadcrumb-item"><a
								href="<?= base_url()."accounts/".strtolower($accounttype)."_list/"; ?>"><?= gettext(ucfirst($accounttype)); ?>s</a></li>
							<li class="breadcrumb-item"><a
								href="<?= base_url()."accounts/".strtolower($accounttype)."_edit/".$edit_id."/"; ?>"> <?php echo gettext('Profile');?> </a>
							</li>
							<li class="breadcrumb-item active" aria-current="page"><a
								href="<?= base_url()."accounts/".strtolower($accounttype)."_sipdevices/".$edit_id."/"; ?>">
                                <?php echo gettext('SIP Devices');?>
                            </a></li>
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
				<div class="col-12 px-0 pb-4">
                <?php
                if ((isset($permissioninfo['freeswitch']['fssipdevices']['create']) && astpp_eq0($permissioninfo['freeswitch']['fssipdevices']['create']) or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')) {
                    ?>

                    <div class="float-left">
						<a
							href='<?php echo base_url()."freeswitch/customer_fssipdevices_add/".$edit_id."/"; ?>'
							rel="facebox_medium" title="Add"> <span
							class="btn btn-line-warning create"> <i
								class="fa fa-plus-circle fa-lg"></i> <?php echo gettext('Create');?>
                            
                        </span>
						</a>
					</div>

                <?php
                }
                if ((isset($permissioninfo['freeswitch']['fssipdevices']['delete'])) && (astpp_eq0($permissioninfo['freeswitch']['fssipdevices']['delete'])) && ($permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')) {
                    ?>
                <div id="left_panel_delete"
						class="pull-left margin-t-0 padding-x-4"
						onclick="delete_multiple('/freeswitch/customer_fssipdevices_delete_multiple/')">
						<span class="btn btn-line-danger"> <i
							class="fa fa-times-circle fa-lg"></i>
                        <?php echo gettext('Delete');?>
                    </span>
					</div>
            <?php

}
                if ((isset($permissioninfo['freeswitch']['fssipdevices']['search'])) && (astpp_eq0($permissioninfo['freeswitch']['fssipdevices']['search'])) && ($permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')) {
                    ?>
                <div id="show_search" class="float-right col-md-4 p-0">
						<input type="text" name="left_panel_quick_search"
							id="left_panel_quick_search"
							class="form-control form-control-lg mb-1"
							value="<?php echo $this->session->userdata('left_panel_search_'.$accounttype.'_sipdevices') ?>"
							placeholder=<?php echo gettext("Search"); ?> />
					</div>
            <?php } ?>
        </div>

				<div
					class="col-md-12 color-three slice float-left content_border p-0"
					id="package_patterns">
					<div class="card col-md-12 pb-4">
						<table id="sidevices_grid" align="left" style="display: none;"></table>
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
      $('.page-wrap').addClass('addon_wrap');
  });
</script>
