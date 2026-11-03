<? extend('left_panel_master.php') ?>
<?php error_reporting(E_ERROR); ?>
<? startblock('extra_head') ?>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript">
    $(document).ready(function() { 
        build_grid("products_list","<?php echo base_url()."accounts/customer_details_json/products/$edit_id/"; ?>",<? echo $grid_fields; ?>,"");
        $("#left_panel_quick_search").keyup(function(){
            quick_search("accounts/customer_details_search/"+'<?php echo $accounttype?>'+"_products/");
        });

        $('.checkall').click(function () {
       		 $('.chkRefNos').prop('checked', $(this).prop('checked')); 
               });
        $('#purchase_products').validate({
            rules: { 
                applayable_product: {
                    required: true
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo('#err');
            }
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
	padding-left: 18px;
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
							<ol class="breadcrumb m-0 p-0">
								<li class="breadcrumb-item"><a
									href="<?= base_url()."accounts/".strtolower($accounttype)."_list/"; ?>"><?= gettext(ucfirst($accounttype)."s"); ?></a></li>
								<li class="breadcrumb-item"><a
									href="<?= base_url()."accounts/".strtolower($accounttype)."_edit/".$edit_id."/"; ?>"> <?php echo gettext('Profile');?></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page"><a
									href="<?= base_url()."accounts/".strtolower($accounttype)."_ipmap/".$edit_id."/"; ?>"> <?php echo gettext('Products');?></a>
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
            <?php  if((isset($permissioninfo['products']['products_list']['assign'])) && (astpp_eq0($permissioninfo['products']['products_list']['assign']))  || ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')){ ?> 
                <div class="float-left" id="left_panel_add">
						<span class="btn btn-line-warning"> <i
							class="fa fa-plus-circle fa-lg"></i><?php echo gettext('Assign');?></span>
					</div>
            <?php } ?>
            <?php  if((isset($permissioninfo['did']['did_list']['delete'])) && (astpp_eq0($permissioninfo['did']['did_list']['delete']))  || ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')){ ?>   
                <div class="pull-left margin-t-0 padding-x-4"
						id="left_panel_add"
						onclick="delete_multiple('/accounts/customer_product_delete/<?php echo $edit_id; ?>')")>
						<span class="btn btn-line-danger"> <i
							class="fa fa-times-circle fa-lg"></i><?php echo gettext('Delete');?></span>
					</div>
           <?php } ?>
           <?php  if((isset($permissioninfo['products']['products_list']['search'])) && (astpp_eq0($permissioninfo['products']['products_list']['search']))  || ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '1') or ($permissioninfo['login_type'] == '-1')){ ?>     
            <div id="show_search" class="float-right col-md-4 p-0">
						<input type="text" name="left_panel_quick_search"
							id="left_panel_quick_search"
							class="form-control form-control-lg m-0"
							value="<?php echo $this->session->userdata('left_panel_search_'.$accounttype.'_products')?>"
							placeholder=<?php echo gettext("Search")?> />
					</div>
        <?php } ?>   
    </div>

				<div
					class="my-4 slice color-three float-left content_border col-md-12"
					id="left_panel_form" style="cursor: pointer; display: none;">
					<div id="floating-label" class="card pb-4">
						<h3 class="bg-secondary text-light p-2 rounded-top"><?php echo gettext('Products');?></h3>
						<form class="row px-4" method="post" name="purchase_products"
							id="purchase_products"
							action="<?= base_url()."accounts/customer_products_action/add/".$edit_id."/".$accounttype."/"; ?>"
							enctype="multipart/form-data">
							<div class='col-md-12'>
								<div class='col-md-4 form-group'>
									<label class="no-padding control-label"><?php echo gettext('Products');?>:</label>
                  <? echo $productslist; ?>
              </div>
								<span id="err"></span>
							</div>
							<div class="col-md-12">
								<center>
									<input class="btn btn-success btn-lg" name="action"
										value=<?php echo gettext("Assign");?> type="submit">
								</center>
							</div>

						</form>

					</div>
				</div>
				<div
					class="col-md-12 color-three slice float-left content_border p-0">
					<div class="card col-md-12 pb-4">
						<table id="products_list" align="left" style="display: none;"></table>
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