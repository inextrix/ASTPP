<? extend('master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
      
        build_grid("fs_sip_devices_grid","",<? echo $grid_fields; ?>);
        
        $("#fssipdevice_search_btn").click(function(){
	  
	post_request_for_search("fs_sip_devices_grid","","freeswith_search");
        });        
        $("#id_reset").click(function(){
            clear_search_request("fs_sip_devices_grid","");
        });
        $('.checkall').click(function () { 
                $('.chkRefNos').prop('checked', $(this).prop('checked')); 
        });
	$(".tDiv").addClass("mt-0").removeClass("tDiv"); 
	$(".reseller_id_search_drp").change(function(){
                if(this.value!=""){
					$.ajax({
						type:'POST',
						url: "<?= base_url()?>/accounts/customer_depend_list/",
						data:"reseller_id="+this.value, 
						success: function(response) {
							 $("#accountid_search_drp").html(response);
							 $("#accountid_search_drp").prepend("<option value='' selected='selected'><?php echo gettext('--Select--'); ?></option>");
							 $('.accountid_search_drp').selectpicker('refresh');
						}
					});
				}	
        });
        
        $(".reseller_id_search_drp").change();   
    });

</script>


<? endblock() ?>

<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>
<?php
$permissioninfo = $this->session->userdata('permissioninfo');
?>
<section class="slice color-three">
	<div class="w-section inverse p-0">
		<div class="col-12">
			<div class="portlet-content mb-4" id="search_bar"
				style="display: none">
                        <?php echo $form_search; ?>
                </div>
		</div>
	</div>
</section>
<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class="card p-4">
			<div class="col-12">
				<div class="col-12 mb-4" style="z-index: 9;">
		<?php
if ((isset($permissioninfo['freeswitch']['fssipdevices']['create']) && astpp_eq0($permissioninfo['freeswitch']['fssipdevices']['create']) or ($permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3'))) {
    ?>

                    <div class="float-left">
						<a href='<?php echo base_url()."freeswitch/fssipdevices_add/"; ?>'
							rel="facebox_medium" title="Add"> <span
							class="btn btn-line-warning create"> <i
								class="fa fa-plus-circle fa-lg"></i> <?php echo gettext('Create')?>
                            
                        </span>
						</a>
					</div>

<?php
}
if ((isset($permissioninfo['freeswitch']['fssipdevices']['delete'])) && (astpp_eq0($permissioninfo['freeswitch']['fssipdevices']['delete'])) or ($permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '3')) {
    ?>
                    <div id="left_panel_delete"
						class="pull-left margin-t-0 padding-x-4"
						onclick="delete_multiple('/freeswitch/fssipdevices_delete_multiple/')">
						<span class="btn btn-line-danger"> <i
							class="fa fa-times-circle fa-lg"></i>
                            <?php echo gettext('Delete')?>
                        </span>
					</div>
				</div>
                <?php } ?>
</div>
			<table id="fs_sip_devices_grid" align="left" style="display: none;"></table>

		</div>

</section>



<? endblock() ?>	

<? end_extend() ?>  
