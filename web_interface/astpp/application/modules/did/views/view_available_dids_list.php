<? extend('master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript">
 $(document).ready(function() {
	
    $( window ).load(function() {
	     $(".did_dropdown").removeClass("col-md-5");  
             $(".did_dropdown").addClass("col-md-3"); 
    });
    build_grid("available_did_grid","",<? echo $grid_fields; ?>,'');
    $('.checkall').click(function () {
            $('.chkRefNos').attr('checked', this.checked); //if you want to select/deselect checkboxes use this
    });
    $("#available_did_search_btn").click(function(){        
            post_request_for_search("available_did_grid","","available_did_search");
    });        
    $("#available_did_id_reset").click(function(){ 
      clear_search_request("available_did_grid","");
    });
    $('#purchase_did_form').validate({
      rules: {
        free_did_list: {
            required: true,
        }
      },
      messages:{
        free_did_list:{
	    required: "The Available DIDs field is required."
        }
      },
      errorPlacement: function(error, element) {
        error.appendTo('#err');
      }
    });
    $("#purchase_did").click(function () {
      $("#search_generate_bar").slideToggle("slow");
    });
 });


function account_change(val){ 
	      	$.ajax({
		type: "POST",
		url: "<?= base_url()?>/accounts/customer_account_change/"+val,
		data:'',
		success:function(alt) {
			  $("#accountid").html(alt);    
		}
	});
}
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

<?php
if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
    $permissioninfo = $this->session->userdata('permissioninfo');
    $logintype = $this->session->userdata('logintype');
    $ret_url = '';
    ?>
			
<?php }?>
<section class="slice color-three">
	<div class="w-section inverse p-0">
		<div class="container">
			<div class="row">
				<div class="portlet-content" id="search_bar"
					style="cursor: pointer; display: none">
	<?php echo $form_search; ?>
      </div>
			</div>
		</div>
	</div>
</section>
<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class="card col-md-12 py-4">  
     			<?php if(($logintype==1) &&  (isset($permissioninfo['did']['did_list']['available_did'])  and astpp_eq0($permissioninfo['did']['did_list']['available_did']))) { ?>
				<div>
				<input type="button" class="btn btn-line-warning"
					onclick="return redirect_page('/did/did_list/')"
					name="purchase_did" value="Purchased DID" id="purchase_did">
			</div>    
			<?php } ?>
			
				
				 <form method="POST" action="" enctype="multipart/form-data">
				<table id="available_did_grid" align="left" style="display: none;"></table>

			</form>
		</div>
	</div>
</section>
<? endblock() ?>
<? end_extend() ?>
