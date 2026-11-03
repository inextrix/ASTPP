 <?php include(FCPATH.'application/views/popup_header.php'); ?>
 <script type="text/javascript">
 	var extensions = '';
 	$(document).ready(function() {
 		
 		var extensions = "<?php echo $accountid; ?>";
 		extensions_type(0);
 	});
 	$(".extensions<?= $count ?>").addClass("d-none");	
 	function extensions_type(value,cnt){
 		var extensions = "<?php echo $accountid; ?>";
 		var count = "<?php echo $count; ?>";
 		if(value == '0') {
 			$.ajax({
 				type: "POST",
 				url: "<?= base_url()?>/ringgroup/ringgroup_sip_list/"+extensions+"/"+count,
 				data:'',
 				success:function(alt) {
 					$(".extensions_set_<?= $count ?>").replaceWith(alt);
 					$('.selectpicker').selectpicker('refresh');
 					$(".extensions<?= $count ?>").addClass("d-none");
 					$('.extensions_set_<?= $count ?>').removeClass("d-none");
 				}
 			});
 		}
 		if(value == '1') {
 			$(".extensions<?= $count ?>").removeClass("d-none");
 			$('.extensions_set_<?= $count ?>').addClass("d-none");
 		}
 	}
 	function change_extensions_type(value,count){

 		var extensions = "<?php echo $accountid; ?>";
 		if(value == '0') {
 			$.ajax({
 				type: "POST",
 				url: "<?= base_url()?>/ringgroup/ringgroup_sip_list/"+extensions+"/"+count,
 				data:'',
 				success:function(alt) {
 					$(".extensions_set_"+count).replaceWith(alt);
 					$('.selectpicker').selectpicker('refresh');
 					$(".extensions"+count).addClass("d-none");
 					$('.extensions_set_'+count).removeClass("d-none");
 					$('[name=Promptdropdown_'+count +']').parent().show();
 				}
 			});
 		}
 		if(value == '1') {
 			$(".extensions"+count).removeClass("d-none");
 			$('.extensions_set_'+count).addClass("d-none");
 			$('[name=Promptdropdown_'+count +']').parent().hide();
 		}
 	}

 </script>


 <li class="" id="rowCount<?= $count?>" style="display:block">
 	<div class="col-md-12 mt-4">
 		<div class="row">
 			<div class="col-9 p-0">
 				<select class="col-md-6 mr-3 float-left form-control form-control-lg selectpicker extensions_type_<?= $count ?>" data-live-search="true" name="extensions_type_<?= $count ?>" onchange="change_extensions_type(this.value,<?= $count ?>)">
 					<option value="0"><?php echo gettext("Extension"); ?></option>
 				</select>

 				<select class="col-md-6 form-control form-control-lg selectpicker extensions_set_<?= $count ?>" data-live-search="true" name="extensions_set_<?= $count ?>">
 				</select>   
 				<input type="text" name="input_extensions_set_<?= $count ?>" value="" size="20" maxlength="30" id ="" class="col-md-6 form-control form-control-lg float-left extensions<?= $count ?>"  />

 			</div>

 			

 			<div class="col-md-2">
 				<select class="col-md-12 form-control form-control-lg selectpicker" data-live-search="true" data-size="6" name="time_out_<?= $count ?>">
 					<?php 
 					for($i=0; $i<=90; $i = astpp_n($i) + 5){
 						echo "<option value=".$i.">".$i."</option>";
 					}
 					?> 
 					<option value="120">120</option>
 					<option value="150">150</option>
 				</select>				
 			</div>


 			<span class="btn btn-danger" id="rowCount<?= $count?>" onclick="removeRow('<?= $count?>');"><?php echo gettext("Delete"); ?></span>
 		</div>
 	</div>
 </li>
