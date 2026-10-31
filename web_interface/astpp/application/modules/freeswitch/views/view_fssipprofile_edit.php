<? extend('master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript">
$(document).ready(function() {
  build_grid("sip_profile_grid","<?php echo base_url(); ?>freeswitch/fssipprofile_params_json/<?=$edited_id?>",<? echo $grid_fields ?>,'');
  jQuery('#sip_port').removeClass('borderred');
  jQuery('#sip_ip').removeClass('borderred');
  jQuery('#sip_name').removeClass('borderred');
  jQuery('#sip_port').removeClass('borderred');
});
   function validateForm(){
	  var formflag = true;
      var ipaddress = document.getElementById("sip_ip").value;
      var pattern =/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/; 
      var match = ipaddress.match(pattern);
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
		  formflag = true;


	} 
      if(document.getElementById('sip_name').value == "")
      {
		  document.getElementById('name_error').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('Name is Required.'); ?></span>";
		  document.getElementById('sip_name').focus();
		  jQuery('#sip_name').addClass('borderred');
		  formflag = false;
      }
      if(document.getElementById('sip_port').value == ""){
		  document.getElementById('error_msg_port').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('The SIP Port field is Required.'); ?></span>";
		  document.getElementById('sip_port').focus();
		  jQuery('#sip_port').addClass('borderred');
		  formflag = false;
	  }
      if(numbers == true)
      {      
		  document.getElementById('error_msg_port').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('The SIP Port field must contain only numbers.'); ?></span>";
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
      if(formflag){
      	alert('Request to reload the sip profile to reflect the applied changes in Freeswitch.');
		$('#myForm1').submit();
	  }
}

function validate_setting(){
	 var settingformflag = true;

	 if(document.getElementById('params_name').value.trim() == ""){
		 document.getElementById('params_name_error').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'><?php echo gettext('Name is Required.'); ?></span>";
		 document.getElementById('params_name').focus();
		 jQuery('#params_name').addClass('borderred');
		 settingformflag = false;	
	 }
	 
	 else{
		   $("#params_name_error").remove();
		  jQuery('#params_name').removeClass('borderred');
		  settingformflag = true;
	 }
	  if(document.getElementById('params_value').value.trim() == "")
      {
		  document.getElementById('params_value_error').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'>Value is Required.</span>";
		  jQuery('#params_value').addClass('borderred');
		  settingformflag = false;
      }
      else{
			$("#params_value_error").remove();
			jQuery('#params_value').removeClass('borderred');
			settingformflag = true;
	  }
      
	 
      if(settingformflag){
		$('#form2').submit();
	  }	
}

function cancel(){
      $('#params_name').val('');
      $('#params_value').val('');
      document.getElementById('params_name_error').innerHTML ="";
      document.getElementById('params_value_error').innerHTML ="";
      jQuery('#params_name').removeClass('borderred');
      jQuery('#params_value').removeClass('borderred');
}
function reload_port(h){
	var sip_port = confirm("Are you sure about the applied config changed? This will affect how the calls will be accepted and handled by Freeswitch. Please note: If the switch is currently having live calls, the reload will not reflect the applied changes in Freeswitch.");
		if(sip_port == true){
			window.location.href = csrf_url(h); // CSRF token
		}
}
</script>
<?php endblock() ?>
<?php startblock('page-title') ?>
<?= $page_title ?>
<?php endblock() ?>
<?php startblock('content') ?>
<div class="card-two card-columns">
	<div class="card">
		<div class="pop_md col-12 pb-4">
			<div id="floating-label" class="pb-4">
				<form class="row px-4" method="post"
					action="<?= base_url() ?>freeswitch/fssipprofile_add/edit/"
					enctype="multipart/form-data" name='form1' id="myForm1">
					<input type='hidden' name='id' value="<?=$id;?>" />
					<div class="col-md-6 form-group">
						<label class="p-0 control-label"><?php echo gettext('Name')?> *</label>
						<input class="col-md-12 form-control form-control-lg"
							value="<?=$sip_name; ?>" id="sip_name" name="name" size="20"
							type="text">
						<div id="name_error" class="tooltips error_div float-left p-0"
							style="display: block;"></div>
					</div>
					<div class="col-md-6 form-group">
						<label class="p-0 control-label"><?php echo gettext('SIP IP ')?> *</label>
						<input class="col-md-12 form-control form-control-lg"
							value="<?=@$sip_ip; ?>" name="sip_ip" size="20" id="sip_ip"
							type="text">
						<div id="error_msg_ip" class="tooltips error_div float-left p-0"
							style="display: block;"></div>
					</div>
					<div class="col-md-6 form-group">
						<label class="p-0 control-label"><?php echo gettext('SIP Port')?> *</label>
						<input class="col-md-12 form-control form-control-lg"
							value="<?=@$sip_port; ?>" name="sip_port" size="20" id="sip_port"
							type="text">
						<div id="error_msg_port" class="tooltips error_div float-left p-0"
							style="display: block;"></div>
					</div>
					<div class="col-md-6 form-group">
						<label class="p-0 control-label"><?php echo gettext('Status')?> * </label>
						<select name="sipstatus"
							class="col-md-12 form-control form-control-lg selectpicker"
							data-live-search='true'>
							<option value="0" <?if ($status == 0)echo 'selected=selected;'?>><?php echo gettext("Active"); ?></option>
							<option value="1" <?if ($status == 1)echo 'selected=selected;'?>><?php echo gettext("Inactive"); ?></option>
						</select>
					</div>
					<div class="col-12 mt-4 text-center">
						<button class="btn btn-success" name="action" value="Save"
							type="button" onclick="validateForm();"><?php echo gettext('Save')?></button>
						<button class="btn btn-secondary ml-2"
							onclick="javascript:window.location ='<?= base_url() ?>freeswitch/fssipprofile/'"
							name="action" value="Cancel" type="button"><?php echo gettext('Cancel')?></button>
					</div>
			
			</div>
			</form>
		</div>
	</div>
	<div class="card m-0">
		<div class="pop_md col-12 pb-4">
			<div id="floating-label" class="pb-4">
				<form method="post" name="form2" id="form2"
					action="<?= base_url() ?>freeswitch/fssipprofile_edit"
					enctype="multipart/form-data">
					<input type='hidden' name='id' value=<?=$id?> /> <input
						type='hidden' name='type' value='save' />
					<div class="col-md-12 form-group">
						<label class="p-0 control-label"><?php echo gettext('Name')?> * </label>
						<input class="col-md-12 form-control form-control-lg"
							value="<?php echo isset($params_name)?$params_name:"";?>"
							name="params_name" id='params_name' size="25" type="text">
						<div id="params_name_error"
							class="tooltips error_div float-left p-0" style="display: block;"></div>
					</div>
					<div class="col-md-12 form-group">
						<label class="p-0 control-label"><?php echo gettext('VALUE')?> * </label>
						<input class="col-md-12 form-control form-control-lg"
							value="<?php echo isset($params_value)?$params_value:"";?>"
							name="params_value" id='params_value' size="25" type="text">
						<div id="params_value_error"
							class="tooltips error_div float-left p-0" style="display: block;"></div>
					</div>
															<?
            if (isset($params_name) && $params_name != '') {
                $type = "edit_setting";
            } else {
                $type = "add_setting";
            }
            ?>
															<input type='hidden' name='type_settings'
						value='<?=$type?>' />
					<div class='col-12 mt-2 text-center'>
						<button class="btn btn-success" name="action" type="button"
							onclick="validate_setting();"><?php echo gettext($button_name);?></button>
						<button class="btn btn-secondary" name="action" value="Reset"
							type="button" onclick="cancel();"><?php echo gettext('Reset')?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="card p-2 text-right">
		<div class="ml-auto">
			<a
				href="<?= base_url()?>/freeswitch/fssipprofile_action/start/<?=$id?>"
				class="" title="Start"
				style="text-decoration: none; color: #428BCA;"><b><?php echo gettext('Start')." |"; ?></b></a>
			&nbsp;<a
				href="<?= base_url()?>/freeswitch/fssipprofile_action/stop/<?=$id?>"
				class="" title="Stop" style="text-decoration: none; color: #428BCA;"><b><?php echo gettext('Stop')." |"; ?></b></a>
			<?php 
					$url = "/freeswitch/fssipprofile_action/reload/".$id;
					$link = base_url () . $url;
				?>
			&nbsp;<a onclick="reload_port('<?=$link?>')"
				class="" title="reload"
				style="text-decoration: none; color: #428BCA; cursor:pointer;"><b><?php echo gettext('Reload')." |"; ?></b></a>
			&nbsp;<a
				href="<?= base_url()?>/freeswitch/fssipprofile_action/rescan/<?=$id?>"
				class="" title="rescan"
				style="text-decoration: none; color: #428BCA;"><b><?php echo gettext('Rescan')?></b></a>
		</div>
	</div>
</div>




<section class="slice color-three">
	<div class="w-section inverse p-0">
		<div class="container">
			<div class="row">
				<div class="portlet-content" id="search_bar"
					style="cursor: pointer; display: none">
                    	<?php

echo $form_search;
                    ?>
    	        </div>
			</div>
		</div>
	</div>
</section>
<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class='card col-md-12 pb-4'>
			<form method="POST" action="del/0/" enctype="multipart/form-data"
				id="ListForm">
				<table id="sip_profile_grid" align="left"></table>
			</form>
		</div>
	</div>
</section>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>
