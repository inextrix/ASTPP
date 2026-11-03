<? extend('master.php') ?>
<? startblock('extra_head') ?>

<script type="text/javascript">
	var number_originate;
	var number_originate=<?php echo json_encode($details['number_originate']); ?>;
	var number_originate_value="";
	if(number_originate){
			for(var i=1;i<number_originate.length;i++){
				number_originate_value+="<div class='number_translation' id='number_translation_"+i+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='number_originate["+i+"][0]' id='strip_number_translation_"+i+"' value='"+number_originate[i][0]+"' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='number_originate["+i+"][1]' id='prepend_number_translation_"+i+"' value='"+number_originate[i][1]+"' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-left col' onclick='removeRows("+i+");' ><i class='fa fa-minus'></i></button></div></div>";
			}
	}
	
	var in_caller_id_originate=<?php echo json_encode($details['in_caller_id_originate']); ?>;
	var in_caller_id_originate_value='';
	if(in_caller_id_originate){
		for(var i=1;i<in_caller_id_originate.length;i++){
			in_caller_id_originate_value+="<div class='incaller_translation' id='incaller_translation_"+i+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='in_caller_id_originate["+i+"][0]' id='strip_incaller_translation_"+i+"' value='"+in_caller_id_originate[i][0]+"' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='in_caller_id_originate["+i+"][1]' id=prepend_incaller_translation_"+i+"' value='"+in_caller_id_originate[i][1]+"' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-left col' onclick='removeincaller("+i+");'><i class='fa fa-minus'></i></button></div></div>";
		}	
	}
	
	var out_caller_id_originate=<?php echo json_encode($details['out_caller_id_originate']); ?>;
	var out_caller_id_originate_value='';
	if(out_caller_id_originate){
		for(var i=1;i<out_caller_id_originate.length;i++){
			out_caller_id_originate_value+="<div class='outcaller_translation' id='outcaller_translation_"+i+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='out_caller_id_originate["+i+"][0]' id='strip_outcaller_translation_"+i+"' value='"+out_caller_id_originate[i][0]+"' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='out_caller_id_originate["+i+"][1]' id='prepend_outcaller_translation_"+i+"' value='"+out_caller_id_originate[i][1]+"' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-right col' onclick='removeoutcaller("+i+");'><i class='fa fa-minus'></i></button></div></div>";
		}	
	}
	
	var number_terminate_terminate=<?php echo json_encode($details['number_terminate']); ?>;
	var number_terminate_terminate_value='';
	if(number_terminate_terminate){
		for(var i=1;i<number_terminate_terminate.length;i++){
			number_terminate_terminate_value+="<div class='terminatenumber_translation' id='terminate_number_translation_"+i+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='number_terminate["+i+"][0]  id='strip_terminate_number_translation_"+i+"' value='"+number_terminate_terminate[i][0]+"' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='number_terminate["+i+"][1]' id='prepend_terminate_number_translation_"+i+"' value='"+number_terminate_terminate[i][1]+"' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-right col' onclick='removeterminatetranslation("+i+");'><i class='fa fa-minus'></i></button></div></div>";
		}
	}
	
	
	
	var out_caller_id_terminate=<?php echo json_encode($details['out_caller_id_terminate']); ?>;
	var out_caller_id_terminate_value='';
	if(out_caller_id_terminate){
		for(var i=1;i<out_caller_id_terminate.length;i++){
			out_caller_id_terminate_value+="<div class='terminateoutcaller_translation' id='terminate_outcaller_translation_"+i+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='out_caller_id_terminate["+i+"][0]' id='strip_terminate_outcaller_translation_"+i+"' value='"+out_caller_id_terminate[i][0]+"' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='out_caller_id_terminate["+i+"][1]' id='prepend_terminate_outcaller_translation_"+i+"' value='"+out_caller_id_terminate[i][1]+"' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-right col' onclick='removeterminateoutcallertranslation("+i+");'><i class='fa fa-minus'></i></button></div></div>";
		}
	}			
			
	$(document).ready(function() {
		$('#numbertranslation').append(number_originate_value);
		$('#incallertranslation').append(in_caller_id_originate_value);
		$('#outcallertranslation').append(out_caller_id_originate_value);
		$('#terminate_number_ranslation').append(number_terminate_terminate_value);
		$('#terminate_outcaller_translation').append(out_caller_id_terminate_value);
	});			
	
</script>
<script type="text/javascript">
   function addMoreRows() {
        var numItems=$('#numbertranslation .number_translation').length;
        var alt="<div class='number_translation' id='number_translation_"+numItems+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='number_originate["+numItems+"][0]' id='strip_number_translation_"+numItems+"' value='' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='number_originate["+numItems+"][1]' id='prepend_number_translation_"+numItems+"' value='' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-left col' onclick='removeRows("+numItems+");' ><i class='fa fa-minus'></i></button></div></div>";
        jQuery('#numbertranslation').append(alt);
   }
   function removeRows(cnt) {
		jQuery('#number_translation_'+cnt).remove();
   }
   
   function addincallerRows(){
	  var incallerrows=$('#incallertranslation .incaller_translation').length;
	  var alt="<div class='incaller_translation' id='incaller_translation_"+incallerrows+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='in_caller_id_originate["+incallerrows+"][0]' id='strip_incaller_translation_"+incallerrows+"' value='' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='in_caller_id_originate["+incallerrows+"][1]' id=prepend_incaller_translation_"+incallerrows+"' value='' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-left col' onclick='removeincaller("+incallerrows+");'><i class='fa fa-minus'></i></button></div></div>";
        jQuery('#incallertranslation').append(alt);   
   }
   function removeincaller(cnt) {
		jQuery('#incaller_translation_'+cnt).remove();
   }
   
   function addoutcallerRows(){
	  var outcallerrows=$('#outcallertranslation .outcaller_translation').length;
	  var alt="<div class='outcaller_translation' id='outcaller_translation_"+outcallerrows+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='out_caller_id_originate["+outcallerrows+"][0]' id='strip_outcaller_translation_"+outcallerrows+"' value='' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='out_caller_id_originate["+outcallerrows+"][1]' id='prepend_outcaller_translation_"+outcallerrows+"' value='' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-right col' onclick='removeoutcaller("+outcallerrows+");'><i class='fa fa-minus'></i></button></div></div>";
      jQuery('#outcallertranslation').append(alt);   
   }
   function removeoutcaller(cnt) {
		jQuery('#outcaller_translation_'+cnt).remove();
   }
   
   function addterminatetranslation(){
	  var terminate_translation_rows=$('#terminate_number_ranslation .terminatenumber_translation').length;
	  var alt="<div class='terminatenumber_translation' id='terminate_number_translation_"+terminate_translation_rows+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='number_terminate["+terminate_translation_rows+"][0]  id='strip_terminate_number_translation_"+terminate_translation_rows+"' value='' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='number_terminate["+terminate_translation_rows+"][1]' id='prepend_terminate_number_translation_"+terminate_translation_rows+"' value='' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-right col' onclick='removeterminatetranslation("+terminate_translation_rows+");'><i class='fa fa-minus'></i></button></div></div>";
      jQuery('#terminate_number_ranslation').append(alt);   
   }
   function removeterminatetranslation(cnt) {
		jQuery('#terminate_number_translation_'+cnt).remove();
   }
   function removeterminateincallertranslation(cnt) {
		jQuery('#terminate_incaller_translation_'+cnt).remove();
   }
   function addterminateoutcallertranslation(){
	  var terminate_outcaller_rows=$('#terminate_outcaller_translation .terminateoutcaller_translation').length;
	  var alt="<div class='terminateoutcaller_translation' id='terminate_outcaller_translation_"+terminate_outcaller_rows+"'><div class='col-md-12 form-group'><label class='no-padding control-label'></label><input name='out_caller_id_terminate["+terminate_outcaller_rows+"][0]' id='strip_terminate_outcaller_translation_"+terminate_outcaller_rows+"' value='' size='20' class='col-md-5 form-control float-left mr-4 form-control-lg' type='text'><input name='out_caller_id_terminate["+terminate_outcaller_rows+"][1]' id='prepend_terminate_outcaller_translation_"+terminate_outcaller_rows+"' value='' size='20' class='col-md-5 form-control float-left form-control-lg' type='text'><button type='button' class='btn btn-danger ml-4 float-right col' onclick='removeterminateoutcallertranslation("+terminate_outcaller_rows+");'><i class='fa fa-minus'></i></button></div></div>";
      jQuery('#terminate_outcaller_translation').append(alt);   
   }
   function removeterminateoutcallertranslation(cnt) {
		jQuery('#terminate_outcaller_translation_'+cnt).remove();
   }
   
</script>
<?php endblock() ?>
<?php startblock('page-title') ?>
<?= $page_title ?>
<?php endblock() ?>
<?php startblock('content') ?>
<div class="p-0">
	<section class="slice color-three">
		<div class="w-section inverse p-0">
			<div class="pop_md col-12 pb-4">
				<form id="localization" name="localization_add"
					action="<?=base_url()."localization/localization_save/";?>"
					method="post">
					<div class="col-md-12 p-0">
						<ul class="card p-0">
							<div class="pb-4" id="floating-label">
								<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo $fieldsets;?></h3>
								<input name="id"
									value="<?php echo isset($details['id']) ? $details['id'] : "";?>"
									type="hidden">
								<div class="col-md-6 col-sm-12 float-left px-0">
									<div class="col-md-12 form-group">
										<label class="p-0 control-label"><?php echo gettext('Name')?><span
											style="color: black;"> *</span></label> <input name="name"
											id="name_input"
											value="<?php echo isset($details['name']) ? $details['name']:"";?>"
											size="20" class="col-md-12 form-control form-control-lg"
											type="text">
										<div class="tooltips error_div pull-left no-padding"
											id="name_error_div" style="display: none;">
											<i class="fa fa-exclamation-triangle error_triangle"></i><span
												class="popup_error error  no-padding" id="name_error"></span>
										</div>
									</div>

									<div class="col-md-12 form-group">
										<label class="no-padding control-label"><?php echo gettext('Status')?><span
											style="color: black;"></span></label> <select name="status"
											class="col-md-12 form-control selectpicker form-control-lg mr-4 country_id col-md-3"
											data-live-search="true">
											<option value="0"
												<?php if(isset($details) && (astpp_eq0($details['status']))){?>
												selected="select" <?php } ?>><?php echo gettext("Active")?></option>
											<option value="1"
												<?php if(isset($details) && ($details['status'] == 1)){?>
												selected="select" <?php } ?>><?php echo gettext("Inactive")?></option>
										</select>
									</div>


								</div>
								<div class="col-md-6 col-sm-12 float-left px-0">
									<div class="col-md-12 form-group">
										<label class="no-padding control-label"><?php echo gettext('Country')?><span
											style="color: black;"></span></label> <select
											name="country_id" id="country_error"
											class="col-md-12 form-control selectpicker form-control-lg mr-4 country_id col-md-3"
											data-live-search="true">
											<option value=""><?php echo gettext("--Select--")?></option>
													<?php
            $selected = "";
            if (isset($country_drp) && $country_drp != "") {
                foreach ($country_drp as $key => $val) {
                    if (isset($details['country_id']) && $details['country_id'] != "") {
                        if ($val['id'] == $details['country_id']) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }
                    }
                    ?>
																<option value='<?php echo $val['id']; ?>'
												<?php echo $selected; ?>><?php echo strtolower(ucwords(gettext($val['country']))); ?></option>
															<?php

}
            }
            ?>
												</select>
									</div>
								</div>
							</div>
						</ul>
					</div>
					<div class="col-md-12 p-0 card-two card-columns">
						<div class="card">
							<ul class="col-md-12 p-0">
								<div class="pb-4" id="floating-label">
									<div class="col-md-12 p-0">
										<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Origination')?></h3>
										<div class="col-md-12 text-center">
											<div class="col-md-5 float-left text-center">
												<b><?php echo gettext('CUT')?></b>
											</div>
											<div class="col-md-5 float-left text-center ml-4">
												<b><?php echo gettext(ucfirst(strtolower('ADD')))?></b>
											</div>
										</div>
										<div id="numbertranslation">
											<div class="number_translation" id="number_translation_0">
												<div class="col-md-12 form-group">
													<label class="no-padding control-label"><?php echo gettext('Destination Number Translation')?></label>
													<input name="number_originate[0][0]"
														id="strip_number_translation_0"
														value="<?php echo isset($details['number_originate'][0][0]) ? $details['number_originate'][0][0]:"";?>"
														size="20"
														class="col-md-5 form-control float-left mr-4 form-control-lg"
														type="text"> <input name="number_originate[0][1]"
														id="prepend_number_translation_0"
														value="<?php echo isset($details['number_originate'][0][1]) ? $details['number_originate'][0][1]:"";?>"
														size="20"
														class="col-md-5 form-control float-left form-control-lg"
														type="text">

													<button type="button"
														class="btn btn-success ml-4 float-left col"
														onclick="addMoreRows();">
														<i class="fa fa-plus"></i>
													</button>
												</div>
											</div>
										</div>
										<div id="incallertranslation">
											<div class="incaller_translation" id="incaller_translation_0">
												<div class="col-md-12 form-group">
													<label class="no-padding control-label"><?php echo gettext('Inbound Callerid Translation')?></label>
													<input name="in_caller_id_originate[0][0]"
														id="strip_incaller_translation_0"
														value="<?php echo isset($details['in_caller_id_originate'][0][0]) ? $details['in_caller_id_originate'][0][0]:"";?>"
														size="20"
														class="col-md-5 form-control float-left mr-4 form-control-lg"
														type="text"> <input name="in_caller_id_originate[0][1]"
														id="prepend_incaller_translation_0"
														value="<?php echo isset($details['in_caller_id_originate'][0][1]) ? $details['in_caller_id_originate'][0][1]:"";?>"
														size="20"
														class="col-md-5 form-control float-left form-control-lg"
														type="text">
													<button type="button"
														class="btn btn-success ml-4 float-left col"
														onclick="addincallerRows();">
														<i class="fa fa-plus"></i>
													</button>
												</div>
											</div>
										</div>
										<div id="outcallertranslation">
											<div class="outcaller_translation"
												id="outcaller_translation_0">
												<div class="col-md-12 form-group">
													<label class="no-padding control-label"><?php echo gettext('Outbound Callerid Translation')?></label>
													<input name="out_caller_id_originate[0][0]"
														id="strip_outcaller_translation_0"
														value="<?php echo isset($details['out_caller_id_originate'][0][0]) ? $details['out_caller_id_originate'][0][0]:"";?>"
														size="20"
														class="col-md-5 form-control float-left mr-4 form-control-lg"
														type="text"> <input name="out_caller_id_originate[0][1]"
														id="prepend_outcaller_translation_0"
														value="<?php echo isset($details['out_caller_id_originate'][0][1]) ? $details['out_caller_id_originate'][0][1]:"";?>"
														size="20"
														class="col-md-5 form-control float-left form-control-lg"
														type="text">
													<button type="button"
														class="btn btn-success ml-4 float-left col"
														onclick="addoutcallerRows();">
														<i class="fa fa-plus"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</ul>
						</div>
						<div class="card">
							<ul class="col-md-12 p-0">
								<div class="pb-4" id="floating-label">
									<div class="col-md-12 no-padding">
										<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Termination')?></h3>
										<div class="col-md-12">
											<div class="col-md-5 float-left text-center">
												<b><?php echo gettext('CUT')?></b>
											</div>
											<div class="col-md-5 float-left text-center ml-4">
												<b><?php echo gettext(ucfirst(strtolower('ADD')))?></b>
											</div>
										</div>
										<div id="terminate_number_ranslation">
											<div class="terminatenumber_translation"
												id="terminate_number_translation_0">
												<div class="col-md-12 form-group">
													<label class="p-0 control-label"><?php echo gettext('Destination Number Translation')?></label>
													<input name="number_terminate[0][0]"
														id="strip_terminate_number_translation_0"
														value="<?php echo isset($details['number_terminate'][0][0]) ? $details['number_terminate'][0][0]:"";?>"
														size="20"
														class="col-md-5 form-control float-left mr-4 form-control-lg"
														type="text"> <input name="number_terminate[0][1]"
														id="prepend_terminate_number_translation_0"
														value="<?php echo isset($details['number_terminate'][0][1]) ? $details['number_terminate'][0][1]:"";?>"
														size="20"
														class="col-md-5 form-control float-left form-control-lg"
														type="text">
													<button type="button"
														class="btn btn-success ml-4 float-left col"
														onclick="addterminatetranslation();">
														<i class="fa fa-plus"></i>
													</button>
												</div>
											</div>
										</div>

										<div id="terminate_outcaller_translation">
											<div class="terminateoutcaller_translation"
												id="terminate_outcaller_translation_0">
												<div class="col-md-12 form-group">
													<label class="no-padding control-label"><?php echo gettext('Outbound Callerid Translation')?></label>
													<input name="out_caller_id_terminate[0][0]"
														id="strip_terminate_outcaller_translation_0"
														value="<?php echo isset($details['out_caller_id_terminate'][0][0]) ? $details['out_caller_id_terminate'][0][0]:"";?>"
														size="20"
														class="col-md-5 form-control float-left mr-4 form-control-lg"
														type="text"> <input name="out_caller_id_terminate[0][1]"
														id="prepend_terminate_outcaller_translation_0"
														value="<?php echo isset($details['out_caller_id_terminate'][0][1]) ? $details['out_caller_id_terminate'][0][1]:"";?>"
														size="20"
														class="col-md-5 form-control float-left form-control-lg"
														type="text">
													<button type="button"
														class="btn btn-success ml-4 float-left col"
														onclick="addterminateoutcallertranslation();">
														<i class="fa fa-plus"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</ul>
						</div>
					</div>
					<div class="col-12 my-4 text-center">
						<button class="btn btn-success" name="action" value="save"
							type="submit"><?php echo gettext('Save')?></button>
						<button type="button" value="cancel"
							class="btn btn-secondary ml-2"
							onclick="return redirect_page('/localization/localization_list/')"><?php echo gettext('Cancel')?></button>
					</div>
				</form>
				<?php 
					if (isset($validation_errors) && $validation_errors != '') { ?>
						<script>
							var ERR_STR = '<?php echo $validation_errors; ?>';
							print_error(ERR_STR);
						</script>
					<? } ?>
			</div>
		</div>
	</section>
</div>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>

