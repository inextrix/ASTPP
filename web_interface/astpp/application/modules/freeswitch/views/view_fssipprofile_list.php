<? extend('master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        build_grid("fs_sipprofile_grid","",<? echo $grid_fields; ?>,<? echo $grid_buttons; ?>);
		$('.checkall').click(function () {
            $('.chkRefNos').prop('checked', $(this).prop('checked')); 
		});
        $("#fssipprofile_search_btn").click(function(){
            post_request_for_search("fs_sipprofile_grid","","freeswitch_search");
        });        
        $("#id_reset").click(function(){ 
            clear_search_request("fs_sipprofile_grid","");
        });
     });
	function reload_port(h){
		var sip_port = confirm("Are you sure about the applied config changed? This will affect how the calls will be accepted and handled by Freeswitch. Please note: If the switch is currently having live calls, the reload will not reflect the applied changes in Freeswitch.");
		if(sip_port == true){
			window.location.href = csrf_url(h); // CSRF token
		}
	}
</script>
<? endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<? startblock('content') ?>
<section class="slice color-three">
	<div class="w-section inverse p-0">
		<div class="col-12">
			<div class="portlet-content mb-4" id="search_bar"
				style="cursor: pointer; display: none">
                        <?php echo $form_search; ?>
                </div>
		</div>
	</div>
</section>
<section class="slice color-three pb-4 px-2">
	<div class="w-section inverse p-0">
		<div class="card col-md-12 pb-4">
			<table id="fs_sipprofile_grid" align="left" style="display: none;"></table>
		</div>
	</div>
</section>
<? endblock() ?>	
<? end_extend() ?>  
