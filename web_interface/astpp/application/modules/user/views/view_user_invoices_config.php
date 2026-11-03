<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript">
$(document).ready(function() {

});

</script>
<script type="text/javascript">
$( "#imagePreview" ).hide();

$(function() {
	$( "#logo_delete" ).live('click', function() {	
	var id =  "<?= $account_data['accountid'] ?>";
	var url='<?php echo base_url()."user/user_invoice_logo_delete/"; ?>'+id;
	var confirm_string = 'Are you sure want to remove logo?';
	var answer = confirm(confirm_string);
	if(answer){
		$.ajax({
			type:"POST",
			url:url,
			success: function(response){
				window.location = '<?php echo base_url()."user/user_invoice_config/"; ?>';
			}
		});
	}else{
		return false;
	}
	});
	$( "#imagePreview" ).live('click', function() {	
		$( "#imagePreview" ).fadeOut();
		$( "#uploadFile" ).val('');
	});
	$("#uploadFile").on("change", function()
	{
		var files = !!this.files ? this.files : [];
		if (!files.length || !window.FileReader) return; 
		if (/^image/.test( files[0].type)){ 
			var reader = new FileReader(); 
			reader.readAsDataURL(files[0]);
	
			reader.onloadend = function(){ 
				$("#imagePreview").show();
				$("#imagePreview").css("background", "url("+this.result+") no-repeat left top");
				$("#imagePreview").css("background-size", "90% 60%");
			}
		}
	});
});
</script>
<?php endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<?php startblock('content') ?>
<div id="main-wrapper">       
    <div id="content" class="container-fluid">   
        <div class="row"> 
            <div class="col-md-12 color-three border_box"> 
                <div class="float-left m-2 lh19">
                    <nav aria-label="breadcrumb">
						 <ol class="breadcrumb m-0 p-0">
                         <li class="breadcrumb-item"><a href="<?= base_url() . "user/user_myprofile/"; ?>">My Profile</a></li>
						 <li class="breadcrumb-item active">
                             <a href="<?= base_url() . "user/user_invoice_config/"; ?>"><?php echo gettext('Company Profile')?></a>
                          </li>
                        </ol>
                    </nav>
                </div>
                <div class="m-2 float-right">
						<a class="btn btn-light btn-hight" href="<?= base_url()."user/user_myprofile/"; ?>"> <i class="fa fa-fast-backward" aria-hidden="true"></i> <?php echo gettext('Back');?></a>
                </div>
            </div>

			<div class="my-4 slice color-three float-left content_border col-md-12" id="left_panel_form">
                    <div id="floating-label" class="card pb-4">
						<h3 class="bg-secondary text-light p-2 rounded-top"><?php echo gettext('Company Profile');?> </h3>
                        <form class="row px-4" method='POST' id="reseller_form" name="reseller_form" action="<?= base_url()?>user/user_invoice_config/"   enctype="multipart/form-data">
							<input type="hidden" name='id' id='id' value="<?= $account_data['id'] ?>">		
							<input type="hidden" name='accountid' id='accountid' value="<?= $account_data['accountid'] ?>">
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Company Name');?></label>
									<input type='text' name="company_name" value="<?= $account_data['company_name'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Invoice Prefix');?></label>
									<input type='text' name="invoice_prefix" value="<?= $account_data['invoice_prefix'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Address');?></label>
									<input type='text' name="address" value="<?= $account_data['address'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Invoice Due Notification'); ?></label>
									<select name="invoice_due_notification" value="<?= $account_data['invoice_due_notification'] ?>" class="form-control selectpicker">
										<option value=0 <?=astpp_eq0($account_data['invoice_due_notification']) ? 'selected' : ''; ?>><?php echo gettext('Enable');?></option>
										<option value=1 <?=$account_data['invoice_due_notification'] == 1 ? 'selected' : ''; ?>><?php echo gettext('Disable');?></option>
									</select>
								</div>
								<div class='col-md-4 form-group'>
									<label class="no-padding control-label"><?php echo gettext('City');?></label>
									<input type='text' name="city" value="<?= $account_data['city'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="no-padding control-label"><?php echo gettext('Address');?></label>
									<input type='text' name="address" value="<?= $account_data['address'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Invoice Notification');?></label>
									<select name="invoice_notification" value="<?= $account_data['invoice_notification'] ?>" class="selectpicker form-control">
										<option value=0 <?=astpp_eq0($account_data['invoice_notification']) ? 'selected' : ''; ?> ><?php echo gettext('Enable');?></option>
										<option value=1 <?=$account_data['invoice_notification'] == 1 ? 'selected' : ''; ?> ><?php echo gettext('Disable');?></option>
									</select>
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Province');?></label>
									<input type='text' name="province" value="<?= $account_data['province'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Invoice Date Interval');?></label>
									<input type='text' name="interval" value="<?= $account_data['interval'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Country');?></label>
									<input type='text' name="country" value="<?= $account_data['country'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Invoice Date Interval');?></label>
									<input type='text' name="interval" value="<?= $account_data['interval'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Notify Before Days');?></label>
									<input type='text' name="notify_before_day" value="<?= $account_data['notify_before_day'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Zipcode');?></label>
									<input type='text' name="zipcode" value="<?= $account_data['zipcode'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Invoice Start Form');?></label>
									<input type='text' name="invoice_start_from" value="<?= $account_data['invoice_start_from'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Telephone');?></label>
									<input type='text' name="telephone" value="<?= $account_data['telephone'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Email Address');?></label>
									<input type='text' name="emailaddress" value="<?= $account_data['emailaddress'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Fax');?></label>
									<input type='text' name="fax" value="<?= $account_data['fax'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Website');?></label>
									<input type='text' name="website" value="<?= $account_data['website'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Website Domain'); ?></label>
									<input type='text' name="fax" value="<?= $account_data['domain'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Website Header');?></label>
									<input type='text' name="website" value="<?= $account_data['website_title'] ?>" class="form-control">
								</div>
								<div class='col-md-4 form-group'>
									<label class="p-0 control-label"><?php echo gettext('Website Footer');?></label>
									<input type='text' name="fax" value="<?= $account_data['website_footer'] ?>" class="col-md-12 form-control">
								</div>

									 <div class="col-12 my-4 text-center">
										<input class=" btn btn-success btn-lg" name="action" value=<?php echo gettext("Save");?> type="submit" id="submit">

									</div>
						</form>				
					</div>
            </div>
        </div>
    </div>
</div>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>
