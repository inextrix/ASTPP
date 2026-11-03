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
    build_grid("did_grid","",<? echo $grid_fields; ?>,<? echo $grid_buttons; ?>);
    $('.checkall').click(function () {
           $('.chkRefNos').prop('checked', $(this).prop('checked')); //if you want to select/deselect checkboxes use this
    });
    $("#did_search_btn").click(function(){        
            post_request_for_search("did_grid","","did_search");
    });        
    $("#id_reset").click(function(){ 
      clear_search_request("did_grid","");
    });
    $('#purchase_did_form').validate({
      rules: {
        free_did_list: {
            required: true,
        }
      },
      messages:{
        free_did_list:{
	    required: "<?php echo gettext('The Available DIDs field is required.'); ?>"
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
				style="display: none">
                    	<?php echo $form_search; ?>
    	        </div>
		</div>
	</div>
</section>

<?php
if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
    $permissioninfo = $this->session->userdata('permissioninfo');
    // echo "<pre>";print_r($permissioninfo);exit;
    $logintype = $this->session->userdata('logintype');
    $ret_url = '';
    ?>


<!-- <div class="main-wrapper">  
<div id="content" class="container-fluid">
	<div class="row">
    	<div class="p-4 col-md-12">
		
    <div class="my-4 slice color-three float-left content_border col-md-12" id="search_generate_bar" style="display:none;cursor: pointer;">
	<div id="floating-label" class="card pb-4">
		<form class="row px-4" id="purchase_did_form"  name='purchase_did_form' method="post" action="<?= base_url() ?>did/did_reseller_purchase/" enctype="multipart/form-data">
			<div class="col-md-4">
				<div class='col-md-12 form-group p-0'>
				  <label class="control-label">Available DIDs :</label>
				   <? echo $didlist; ?>
				</div>	
				<span id="err"></span>	                               
			</div>
			<div class="col-md-4">
				<div class='col-md-12 form-group p-0'>
				  <label class="control-label">Accounts :</label>
			<?php
    $where = array(
        "status" => 0,
        "deleted" => 0,
        "type" => 0,
        "reseller_id" => $account_id
    );
    $account_arr = array(
        "id" => "account_id",
        "name" => "account_id",
        "class" => "account_id"
    );
    $account = form_dropdown_all($account_arr, $this->db_model->build_concat_dropdown("id,first_name,last_name,number", "accounts", "", $where), '');

    echo $account;
    ?>
				</div>	
				<span id="err"></span>	                               
			</div>
			<div class="col-md-12">
				
				<center>
				<input class="margin-l-20 btn btn-success btn-lg" name="action" value="Purchase DID" type="submit">	
				</center>
			</div>
		</form>
	</div>
     </div>
	</div>  
    </div>
</div>
</div> -->
<?php }?>

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

<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">

		<form method="POST"
			action="<?php echo base_url(); ?>did/did_available_list/"
			enctype="multipart/form-data" id="">
				<?php

if (isset($permissioninfo['did']['did_list']['buy_did']) and astpp_eq0($permissioninfo['did']['did_list']['buy_did']) and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4')) {
        ?>
				<input type="submit" class="btn btn-info mb-4" name="purchase_did"
				value=<?php echo gettext("Buy DIDs")?> id="buy_did">    
				<?php } ?>
				</form>
		<div class="card col-md-12 pb-4">
			<!--<form method="POST" action="" enctype="multipart/form-data" id="">-->
			<table id="did_grid" align="left" style="display: none;"></table>

			<!-- </form>-->
		</div>
	</div>
</section>
<? endblock() ?>
<? end_extend() ?>
