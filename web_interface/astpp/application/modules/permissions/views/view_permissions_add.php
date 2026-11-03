<? extend('master.php') ?>
<? startblock('extra_head') ?>
<? endblock() ?>
<?= startblock('page-title') ?>
<?= gettext($page_title); ?>
<? endblock() ?>
<?= startblock('content') ?>
<script>
function open_menu(module){	
	var div_id= module+"_menu";
	$('#'+div_id).toggle();
}
function open_sub_menu(parent_div_id,list,i){
	var div_id= list+"_menu";
	$('#'+div_id).toggle();
	if($("#"+parent_div_id+' .collaps-'+i).html()=='<i class="fa fa-plus"></i>'){
		$("#"+parent_div_id+' .collaps-'+i).html('<i class="fa fa-minus"></i>');
	}else{
		$("#"+parent_div_id+' .collaps-'+i).html('<i class="fa fa-plus"></i>');
	}
}

</script>
<style>
hr {
	display: block;
	height: 1.5%;
	border: 0;
	border-top: 3px solid #1B3280;
	margin: 1em 0;
	padding: 0;
}

table {
	background-color: transparent;
	margin: 10px 0 10px 0;
	border-collapse: inherit;
	border-radius: 5px;
	border: 1px solid #2ca0c8;
	color: #2ca0ca;
	line-height: 30px;
	padding: 10px 0 10px 0;
}

.heading {
	box-shadow: 5px 10px 100px rgba(100, 100, 100, 0.15) inset;
	border-radius: 4px;
	padding: 10px;
	margin: 0 0 10px 0;
}

.backgroundfff {
	background: #fff;
	border-radius: 3px;
	padding: 10px 0;
	margin: 10px 0 5px 0px;
	height: auto !important;
	display: flow-root;
}

.w-section li {
	line-height: 30px !important;
}

.bordergrey {
	border: 1px solid #c0c0c0;
	border-radius: 3px;
	padding: 10px 0 0 0;
	margin: 0 0 10px 0;
	background: #ebeef2;
}

.submenu {
	color: #2ca0c9;
	font-size: 16px;
}
</style>
<script>
function login_type_change(val){
	var post_data={};
	post_data['login_type']=val;
	post_data['role_name']=document.getElementById('name').value;
	post_data['description']=document.getElementById('description').value;
	$.ajax({
		type:'POST',
		async:false,
		url: base_url+"permissions/permissions_login_type_change/",
		data: post_data,
		success: function(response) {
		}
	});
	location.reload(); 
}
function form_submit(){
        name = document.getElementById('name').value;
        description = document.getElementById('description').value;
	document.getElementById("name_err").innerHTML="";
	document.getElementById("description_err").innerHTML="";  
	if(name == ''){
		document.getElementById("name_err").style.display = "block"; 
		document.getElementById("name_err").innerHTML="<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error p-0'> Name field is required</span>";
	}
	else if(description == ''){
		document.getElementById("description_err").style.display = "block"; 
		document.getElementById("description_err").innerHTML="<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error p-0'> Description field is required</span>";
	}else{
		document.getElementById('permissions_form').submit();
	}
}
$(document).ready(function(){
	$(".permission_checkbox").click(function(){
		var id = $(this)[0].id;
		if (id.match("_main$")) {
			if ($('#'+id).is(':checked')) {
					$("#"+id+"_table input[type=checkbox]" ).prop('checked',true);
			}else{
				$("#"+id+"_table input[type=checkbox]" ).prop('checked',false);
			}
		}else{
			var table_id =$("#"+$(this)[0].id).parent().parent().parent().parent()[0].id;
			var countchecked = $("#"+table_id+" input[type=checkbox]:checked").length;
			var main_id  = table_id.replace("_table","");
			var custom_id = main_id.replace("_main","");
			var current_event_id = id.replace(custom_id+"_","");
			if(current_event_id != "" && $("#"+id).prop('checked')){
				$("input:checkbox[id="+custom_id+"_list]" ).prop('checked',true);
				if($("input:checkbox[id="+custom_id+"_list]" ).prop('checked',true)){
					$("input:checkbox[id="+main_id+"]" ).prop('checked',true);
				}
				if(countchecked > 0){
					$("input:checkbox[id="+main_id+"]" ).prop('checked',true);
				}else{
					$("input:checkbox[id="+main_id+"]" ).prop('checked',false);
				}
			}else{
				if(current_event_id=="list"){
					if($("input:checkbox[id="+custom_id+"_list]" ).prop('checked',false)){
						$("input:checkbox[id="+main_id+"]" ).prop('checked',false);
						$("#"+id+"_table input[type=checkbox]" ).prop('checked',false);
						$("#"+custom_id+"_main_table").find(".permission_checkbox").prop('checked',false);
					}
				}
			}
		}
		
		
	});
});
</script>
<section class="slice color-three">
	<div class="w-section inverse p-0">
		<form id="permissions_form" method="POST"
			action="/permissions/permissions_save/" name="permissions_form">
			<div class="pop_md col-12 p-0">
				<div class="card">
					<ul class="p-0">
						<div class="col-md-12 p-0 padding">

							<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Roles & Permissions') ?></h3>

							<div id="floating-label" class="col-md-12 mb-4">
								<div class="row">
									<div class="col-md-3 form-group">
										<label class="p-0 control-label"><?php echo gettext('Role Name')." :" ?><span
											class="text-dark"> *</span></label> <input type="hidden"
											class="error form-control " value="" name="id" id="id"> <input
											type="text"
											class="error col-md-12 form-control form-control-lg"
											placeholder="Enter Name" name="name" id="name"
											value="<?= $role_name ?>">
										<div class="text-danger tooltips error_div float-left p-0"
											id="name_err"></div>
									</div>
									<div class="col-md-3 form-group">
										<label class="p-0 control-label"><?php echo gettext('Description').' :' ?><span
											class="text-dark"> *</span></label> <input type="text"
											class="error form-control form-control-lg"
											placeholder='Enter Description' value="<?= $description ?>"
											name="description" id="description">
										<div class="text-danger tooltips error_div float-left p-0"
											id="description_err"></div>
									</div>
									<div class="col-md-3 form-group">
										<label class="p-0 control-label"><?php echo gettext('Type').' :' ?><span
											class="text-dark"> *</span></label> <select name="login_type"
											id="login_type"
											class='col-md-12 form-control form-control-lg selectpicker'
											data-live-search='true'
											onchange="login_type_change(this.value);">
											<option value='0'
												<?php if (isset($login_type) && astpp_eq0($login_type)) { echo "selected"; } ?>><?php echo gettext("Admin")?></option>
											<option value='1'
												<?php if (isset($login_type) && $login_type == 1) { echo "selected"; } ?>><?php echo gettext("Reseller")?></option>
										</select>
									</div>
									<div class="col-md-12 text-right">
										<input id="save_button" class="save_button btn btn-success"
											name="save_button" value="<?php echo gettext('Save');  ?>" type="button"
											onclick="form_submit()"> <input name="action" type="button"
											value="<?php echo gettext('Cancel');  ?>" class="btn btn-secondary ml-2"
											onclick="return redirect_page('/permissions/permissions_list/')">

									</div>
								</div>
							</div>
							<div class="col-md-12">
<?php
$i = 0;
foreach ($permission_main_array as $module_key => $module_value) {
    $function_name = 'open_' . $module_key . '_menu();';
    $div_function_name = $module_key . '_menu';
    ?>
<div class="col-md-12 alert-primary heading">
									<a class="btn"
										onclick="open_menu('<?php echo $module_key; ?>');"><h3
											class="text-left m-0"><?php echo gettext(ucwords(str_replace("_"," ",$module_key))); ?></h3></a>
									<div id="<?php echo $div_function_name; ?>"
										class="backgroundfff" style="display: none;">
	<?php

    foreach ($module_value as $first_sub_module_key => $first_sub_module_value) {
        foreach ($first_sub_module_value as $sub_module_key => $sub_module_value) {
            $sub_menu_id = $sub_module_key . '_menu';
            $sub_menu_click = 'open_' . $sub_module_key . '_menu()';
            $main_check_box_name = "permission[" . $first_sub_module_key . '][' . $sub_module_key . "][main]";
            $link_id = "li_" . $sub_module_key . '_menu';
            ?>
 	  <ul>
											<li class="row mb-2" id="<?php echo $link_id; ?>";><input
												id="<?=$first_sub_module_key."_".$sub_module_key."_main";?>"
												class="float-left permission_checkbox <?=$first_sub_module_key."_".$sub_module_key;?>"
												type="checkbox" name="<?php echo $main_check_box_name; ?>"
												value="0" style="width: 20px;">
												<div class="float-left">
													<a
														onclick="open_sub_menu('<?php echo $link_id; ?>','<?php echo $sub_module_key; ?>','<?php echo $i?>');"
														class="btn btn-link"> <span
														class="collaps-<?php echo $i; ?>"><i class='fa fa-plus'></i></span>
				<?php
            echo gettext($display_name_array[$module_key][$first_sub_module_key][$sub_module_key]);
            ?>
			</a></li>

											<div class="col-md-12" id="<?php echo $sub_menu_id; ?>"
												style="display: none;">
												<table class="card" border=1 style="width: 100%;"
													bordercolor="#fff"
													id="<?=$first_sub_module_key."_".$sub_module_key."_main_table";?>">
													<tr>
		<?php
            $array_count = astpp_count($sub_module_value);
            for ($i = 0; $i < $array_count; $i ++) {
                if ($sub_module_value[$i] != 'main') {
                    $loop_flag = astpp_n($i) / 4;
                    $loop_value_explode = explode('.', $loop_flag);
                    ?>
			<td class="text-dark"
															style="width: 10%; border: none; padding-left: 20px;">
				<?php
                    $check_bax_name = $sub_module_value[$i];
                    $check_bax_name = "permission[" . $first_sub_module_key . '][' . $sub_module_key . '][' . $sub_module_value[$i] . ']';
                    ?>
				<input class="permission_checkbox"
															id="<?=$first_sub_module_key."_".$sub_module_key."_".strtolower(str_replace(" ","_",$sub_module_value[$i]));?>"
															type="checkbox" name="<?php echo $check_bax_name; ?>"
															value="0" style="width: 20px;">
				<?php
                    $sub_module_value[$i] = ($sub_module_value[$i] == 'Delete') ? "Delete Multiple":$sub_module_value[$i];
				 ?>
				<?php echo gettext(ucfirst(strtolower(str_replace("_"," ",$sub_module_value[$i])))); ?>
			</td>
		<?php if(!isset($loop_value_explode[1])){ ?>
			
													
													
													<tr>
													</tr>
		<?php }	?>

		<?php  } }  ?>
		     </tr>

												</table>
											</div>
										</ul>
	<?php } } ?>
        
        </div>
								</div>
<?php } ?>


		</div>
						</div>
					</ul>
				</div>
			</div>


		</form>

	</div>
</section>
<? endblock() ?>  

<? end_extend() ?>  



