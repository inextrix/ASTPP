<?php include(FCPATH.'application/views/popup_header.php'); ?>
<script type="text/javascript" language="javascript">
	 var date=new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()); 
	var next_billing_date='<?php echo $next_billing_date;?>';
	var next_billing_date_value=new Date(next_billing_date); 
    $(document).ready(function() {
	$("#creation").datepicker({		
             uiLibrary: 'bootstrap4',
             iconsLibrary: 'fontawesome',
	     minDate:-0,
	     maxDate:next_billing_date,
             modal:true,
            format: 'yyyy-mm-dd',
            footer:true
          }); 

	$("span.input-group-append").addClass('align-self-end').removeClass('input-group-append');
});
</script>
<style>
.gj-modal {
	z-index: 99999;
}
</style>

<script type="text/javascript">
    $("#submit").click(function(){
        submit_form("terminate_form");
    });

    var jqueryarray = <?php echo json_encode($this->tooltip_data); ?>;
    if(jqueryarray && jqueryarray !=''){
      var form_name = 'terminate_form';
      $.each(jqueryarray, function (key, val) {
        key = key.replace(form_name+'_','');
      $('select[name="'+key+'"] , input[name="'+key+'"]  , textarea[name="'+key+'"]').closest('.form-group').find('label').attr({'data-toggle':'tooltip', 'data-html':"true",'data-original-title':val, 'data-placement' : 'right'});
        });
    }

    $('[data-toggle="tooltip"]').tooltip(
        { boundary: 'window' }
    );

</script>
<?php
$accountinfo = $this->session->userdata("accountinfo");
if (astpp_eq0($accountinfo['type']) || $accountinfo['type'] == 3) {
    $order_terminate_url = "/user/user_orders_termination/";
} else {
    $order_terminate_url = "/orders/orders_users_terminate/";
}
?>
<section class="slice color-three pb-4">
	<div id="floating-label" class="w-section inverse p-0">
		<form method="post" name="terminate_form" id="terminate_form"
			action="<?= base_url().$order_terminate_url;?>">
			<div class="col-md-12 p-0 card-header">
				<h3 class="fw4 p-4 m-0"><?php echo gettext("Terminate Order"); ?> #<?php echo $orderid;?></h3>
			</div>
			<div class='pop_md col-12'>
				<input type="hidden" name="order_id" value="<?php echo $orderid; ?>" />
				<div class="col-md-12">
					<div class="row">
						<div class='col-md-6 form-group'>
							<label class="p-0 control-label"><?php echo gettext("Date"); ?></label>
							<input name="creation" value="" placeholder="" 0="" size="20"
								class="col-md-12 form-control form-control-lg" id="creation"
								role="input" type="text">
							<div class="tooltips error_div pull-left no-padding"
								id="creation_error_div" style="display: none;">
								<i
									style="color: #D95C5C; padding-right: 6px; padding-top: 10px;"
									class="fa fa-exclamation-triangle"></i><span
									class="popup_error error  no-padding" id="creation_error"> </span>
							</div>

						</div>
						<div class='col-md-6 form-group'>
							<label class="p-0 control-label"><?php echo gettext("Note"); ?> </label>
							<input class="col-md-12 form-control form-control-lg" value=""
								name="note" size="16" type="text" />
						</div>
					</div>
				</div>
				<div class='col-md-6 mx-auto mt-4'>
					<button class="submit btn btn-success btn-block" id="submit"
						name="action" value="Save" type="button"><?php echo gettext("Terminate"); ?></button>
				</div>
			</div>
			<?php
// echo "<pre>"; print_r($validation_errors); exit;
if (isset($validation_errors) && $validation_errors != '') {
    ?>
						<script>
							var ERR_STR = '<?php echo $validation_errors; ?>';
							print_error(ERR_STR);
						</script>
					<? } ?>  	
  	</form>

</section>


