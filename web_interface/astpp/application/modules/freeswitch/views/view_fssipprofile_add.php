<? extend('master.php') ?>
<? startblock('extra_head') ?>

<script type="text/javascript">
    $("#submit").click(function(){
        submit_form("fssipprofile_form");
    })
</script>
<script>
$(document).ready(function(){
  jQuery('#sip_port').removeClass('borderred');
  jQuery('#sip_ip').removeClass('borderred');
  jQuery('#sip_name').removeClass('borderred');
  jQuery('#sip_port').removeClass('borderred');
  
  $("#sip_name").change(function(){
    $('#error_msg_sip').text('');
    return false;
  });
  $("#sip_ip").change(function(){
    $('#error_msg_ip').text('');
    return false;
  });
  $("#sip_port").change(function(){
    $('#error_msg_port').text('');
    return false;
  });
});
</script>
<script type="text/javascript">
function validateForm(){
	var formflag = true;
      var ipaddress = document.getElementById("sip_ip").value;
      var pattern_ipv6 =/^((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*::((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*|((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4})){7}$/; 
	  var pattern =/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/; 

      var match = ipaddress.match(pattern);
	  var match_ipv6 = ipaddress.match(pattern_ipv6);
      var val=document.getElementById('sip_port').value;
      var length1=val.length;
      var numbers  = isNaN(val);
	  if(document.getElementById('sip_port').value == "")
		  {
			  document.getElementById('error_msg_port').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('SIP Port is Required.'); ?></span>";
			  document.getElementById('sip_port').focus();
			  jQuery('#sip_port').addClass('borderred');
			  formflag = false;
		  }

      if(document.getElementById('sip_ip').value == "")
		  { 
			  document.getElementById('error_msg_ip').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('SIP IP is Required.'); ?></span>";
			  document.getElementById('sip_ip').focus();
			  jQuery('#sip_ip').addClass('borderred');
			  formflag = false;
		  }else{
		
					$("#error_msg_ip").remove();
				 	jQuery('#sip_ip').removeClass('borderred');
				
		  }

      if(document.getElementById('sip_name').value == "")
		  {
			  
			  document.getElementById('error_msg_name').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('Name is Required.'); ?></span>";
			  document.getElementById('sip_name').focus();
			  jQuery('#sip_name').addClass('borderred');
			  formflag = false;
		  }else{
		$("#error_msg_name").remove();
		jQuery('#sip_name').removeClass('borderred');
		  }
      if(numbers == true)
		  {
				  document.getElementById('error_msg_port').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('The SIP Port field must contain only numbers'); ?>.</span>";
				  document.getElementById('sip_port').focus();
				  jQuery('#sip_port').addClass('borderred');
				  formflag = false;
		  }
	  if(length1 > 5)
		  {
			 document.getElementById('error_msg_port').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('The SIP Port field can not exceed 5 characters in length.'); ?></span>";
			 document.getElementById('sip_port').focus();
			  jQuery('#sip_port').addClass('borderred');
			 formflag = false;
		  }
		   
		  if(val < 0 || val > 65535)
		  {
			 document.getElementById('error_msg_port').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('The SIP Port field can not exceed value of 0 to 65535.'); ?></span>";
			 document.getElementById('sip_port').focus();
			  jQuery('#sip_port').addClass('borderred');
			 formflag = false;
		  }
      if(formflag){
		$('#myForm1').submit();
	  }      
}
</script>
<?php endblock() ?>
<?php startblock('page-title') ?>
<?= $page_title ?>
<?php endblock() ?>
<?php startblock('content') ?>
<section class="slice color-three m-0">
	<div class="w-section inverse p-0">


		<div class="pop_md col-md-12 mt-4 padding-x-8">
			<form method="post"
				action="<?= base_url() ?>freeswitch/fssipprofile_add/add/"
				enctype="multipart/form-data" name='form1' id="myForm1">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="pb-4" id="floating-label">
								<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Create SIP Profile')?></h3>
								<div class="col-md-12">
									<div class="row">
										<div class="col-md-6 form-group">
											<label class="p-0 control-label"><?php echo gettext('Name')?>*</label>
											<input class="col-md-12 form-control form-control-lg"
												id="sip_name" name="name" size="20" type="text"
												value="<?php echo isset($details['name'])?$details['name']:"";?>">
											<div id="error_msg_name"
												class="tooltips error_div float-left p-0"
												style="display: block;"></div>
										</div>
										<div class="col-md-6 form-group">
											<label class="p-0 control-label"><?php echo gettext('SIP IP')." *"; ?> </label>
											<input class="col-md-12 form-control form-control-lg"
												id="sip_ip" name="sip_ip" size="20" type="text"
												value="<?php echo isset($details['sip_ip'])?$details['sip_ip']:"";?>">
											<div id="error_msg_ip"
												class="tooltips error_div float-left p-0"
												style="display: block;"></div>
										</div>
										<div class="col-md-6 form-group">
											<label class="p-0 control-label"><?php echo gettext('SIP Port')." *"; ?></label>
											<input class="col-md-12 form-control form-control-lg"
												id="sip_port" name="sip_port" size="20" type="text"
												value="<?php echo isset($details['sip_port'])?$details['sip_port']:"";?>">
											<div id="error_msg_port"
												class="tooltips error_div float-left p-0"
												style="display: block;"></div>
										</div>
										<div class="col-md-6 form-group">
											<label class="p-0 control-label"><?php echo gettext('Status')?> </label>
											<select name="sipstatus"
												class="col-md-12 form-control selectpicker form-control-lg"
												data-live-search='true'>
												<option value="0"
													<?php if(isset($details['sipstatus']) && astpp_eq0($details['sipstatus'])){?>
													selected="select" <?php } ?>><?php echo gettext("Active"); ?></option>
												<option value="1"
													<?php if(isset($details['sipstatus']) && $details['sipstatus'] == 1){?>
													selected="select" <?php } ?>><?php echo gettext("Inactive"); ?></option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-12 my-4 text-center">
							<button class="btn btn-success" name="action" value="Save"
								type="button" onclick="validateForm();"><?php echo gettext('Save')?></button>
							<button class="btn btn-secondary mx-2"
								onclick="javascript:window.location ='<?= base_url() ?>freeswitch/fssipprofile/'"
								name="action" value="Cancel" type="button"><?php echo gettext('Cancel')?></button>
						</div>
					</div>
				</div>
			</form>
					<?php
    if (isset($validation_errors) && $validation_errors != '') {
        ?>
							<script>
								var ERR_STR = '<?php echo $validation_errors; ?>';
								print_error(ERR_STR);
							</script>
						<? }?>
				</div>
	</div>
</section>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>