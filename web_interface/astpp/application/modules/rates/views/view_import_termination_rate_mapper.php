<?php
extend('master.php')?>
<?php
startblock('extra_head')?>
   <?
if (isset($csv_tmp_data) && ! empty($csv_tmp_data)) {
    echo '<script>';
    echo 'var csv_tmp_data = ' . json_encode($csv_tmp_data) . ';';
    echo '</script>';
}
?>
<script type="text/javascript" language="javascript">
   $(document).ready(function() {
   });
</script>
<script type="text/javascript" language="javascript"><?
if (isset($mapto_fields) && ! empty($mapto_fields)) {
    foreach ($mapto_fields as $csv_key => $csv_value) {
        echo '$("#' . $csv_value . '-prefix").live("change", function () {';
        echo 'var select = document.getElementById("' . $csv_value . '-select");';
        echo 'var answer = select.options[select.selectedIndex].value;';
        echo 'document.getElementById("' . $csv_value . '-display").value =  (!answer) ? document.getElementById("' . $csv_value . '-prefix").value : document.getElementById("' . $csv_value . '-prefix").value + csv_tmp_data[2][answer];';
        echo '});';
        echo '$("#' . $csv_value . '-select").live("change", function () {';
        echo 'var select = document.getElementById("' . $csv_value . '-select");';
        echo 'var answer = select.options[select.selectedIndex].value;';

        echo 'document.getElementById("' . $csv_value . '-display").value = (!answer) ? document.getElementById("' . $csv_value . '-prefix").value : document.getElementById("' . $csv_value . '-prefix").value + csv_tmp_data[2][answer];';
        echo '});';
    }
}
?></script>

<?php
endblock()?>
<?php
startblock('page-title')?>
<?php

echo $page_title;
?>

<?php
endblock()?>
<?php
startblock('content')?>
<?php
if (! isset($csv_tmp_data)) {
    ?>
<section class="slice color-three">
	<div class="w-section inverse p-0">
		<form method="post"
			action="<?php echo base_url() ?>rates/termination_rate_mapper_preview_file/"
			enctype="multipart/form-data" id="termination_rate" name="import_termination_rate_mapper">
			<div class="row">
				<div class="col-md-12">
					<div class="col-md-12 clo-sm-12 float-left p-0">
						<div class="w-box card py-3">
							<span style="margin-left: 10px;">
						 <?php
    if (isset($error) && ! empty($error)) {
        echo "<span class='row alert alert-danger m-2'>" . $error . "</span>";
    }
    ?>
						 </span>
							<h3 class="px-4"><?php echo gettext("You must either select a field from your file OR provide a default value for the following fields:"); ?></h3>
							<?php echo gettext("<p>Code,Destination,Connection Cost ($currency),Grace Time,Cost / Min ($currency),Initial Increment,Increment,Strip,Prepend.</p>");?>
					  </div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="card col-md-12 p-0">
						<div class="pb-4" id="floating-label">
							<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext("Import Termination Rates"); ?></h3>
							<div class="col-md-4 col-sm-12 float-left p-0">
								<div class='col-md-12 form-group'>
									<label class="p-0 control-label"><?php echo gettext("Trunk List"); ?></label>
							  
								  <?php
    $trunklist = form_dropdown('trunk_id', $this->db_model->build_dropdown("id,name", "trunks", "where_arr", array(
        "status " => "0"
    )), '');
    echo $trunklist;
    ?>
							  
							  </div>
							</div>
							<input type="hidden" name="mode"
								value="import_termination_rate_mapper" /> <input type="hidden"
								name="logintype"
								value="<?= $this->session->userdata('logintype') ?>" /> <input
								type="hidden" name="username"
								value="<?= $this->session->userdata('username') ?>" />


							<div class="col-md-12 form-group">
								<label class="control-label mb-4"><?php echo gettext("Select the file"); ?></label>
								<div class="col-12 mt-4 d-flex">
									<div class="col-md-4 float-left" data-ripple="">
										<input type="file" name="termination_rate_import_mapper"
											id="termination_rate_import_mapper" class="custom-file-input">
										<label
											class="custom-file-label btn-primary btn-file text-left"
											for="file"> </label>
									</div>
									<div class="col-md-6 float-left align-self-center">
										<span id="welcomeDiv" class="answer_list float-left d-none">
											<button type="button" title="Cancel" class="btn btn-danger"><?php echo gettext("Remove"); ?></button>
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="text-center">
						<button class="btn btn-success mt-4" id="import_terminationrate"
							type="submit" name="action" value="Import"><?php echo gettext("Import"); ?></button>
						<button class="btn btn-secondary ml-2 mt-4" id="ok" type="button"
							name="action" value="Cancel"
							onclick="return redirect_page('/rates/termination_rates_list/')"><?php echo gettext("Cancel"); ?></button>
					</div>
				</div>
			</div>
		</form>
	</div>
</section>
<?php
}
?>
<?php
if (isset($csv_tmp_data) && ! empty($csv_tmp_data)) {
    ?>
<section class="slice color-three">
	<div class="w-section">
		<div class="content">
			<div class="pop_md col-12 pt-2">
				<form id="import_form" name="import_form"
					action="<?php echo base_url() ?>rates/termination_rate_rates_mapper_import/"
					enctype="multipart/form-data" method="POST">
					<div class="col-md-12 col-sm-12 p-0 float-left">
						<div class="card p-4 mb-4">
							<ul class="m-0 card p-0" id="floating-label">
								<table class="table">
									<thead>
										<tr class='thead-light'>
											<th>ASTPP Field</th>
											<th>PREFIX/DEFAULT VALUE</th>
											<th>Map to Field</th>
											<th>Data Example</th>
										</tr>
									</thead>
									<tbody>
								<?php
    foreach ($mapto_fields as $csv_key => $csv_value) {
        echo "<tr>";
        echo "<td>" . ucwords($csv_key) . '(' . $csv_value . ")</td>";
        echo "<td><input class='form-control form-control-lg' type='text' name='" . $csv_value . "-prefix' id='" . $csv_value . "-prefix'></td>";
        echo "<td>";
        echo "<select class='form-control selectpicker form-control-lg' name='" . $csv_value . "-select' id='" . $csv_value . "-select'>";
        ?>
									<option value="">Select</option>
									<?php
        $keys = astpp_array_keys($file_data);
        for ($i = 0; $i < astpp_count($file_data); $i ++) {
            ?>
										<option value="<?php echo $file_data[$i]; ?>"><?php
            echo $file_data[$i];
            ?></option>
											<?php
        }
        echo "</td>";
        echo "<td><input class='form-control form-control-lg' type='text' name='" . $csv_value . "-display' id='" . $csv_value . "-display'></td>";
        echo "</tr>";
    }
    ?>
							</tbody>
								</table>

							</ul>
						</div>
					</div>
					<div class="card p-4 table-responsive">
						<input type="hidden" name="trunkid" value="<?php echo $trunkid ?>" />
						<input type="hidden" name="check_header"
							value="<?php echo $check_header ?>" /> <input type="hidden"
							name="mode" value="import_termination_rate_mapper" /> <input
							type="hidden" name="filefields"
							value="<?php echo htmlspecialchars($field_select); ?>" /> <input
							type="hidden" name="logintype"
							value="<?php echo $this->session->userdata('logintype') ?>" /> <input
							type="hidden" name="username"
							value="<?php echo $this->session->userdata('username') ?>" />
						<h3 class="bg-secondary text-light p-3 rounded-top mb-0">Import
							File Data..</h3>
						<table width="100%" border="1"
							class="table table-bordered details_table details_table">
							<?php
    $cnt = 1;
    foreach ($csv_tmp_data as $csv_key => $csv_value) {
        if ($csv_key < 15) {
            echo "<tr class='thead-light'>";
            foreach ($csv_value as $field_name => $field_val) {
                if (astpp_eq0($csv_key)) {
                    echo "<th>" . ucfirst($field_val) . "</th>";
                } else {
                    echo "<td>" . $field_val . "</td>";
                    $cnt ++;
                }
            }
            echo "</tr>";
        }
    }
    echo "<tr><td colspan='" . $cnt . "' class='text-right'>
							<button type='submit' class='btn btn-success' id='Process' value='Process Records'>Process</button>
                           <a href='" . base_url() . "rates/termination_rates_list/'> <input type='button' class='btn btn-secondary mx-2 float-right' value='Back'></a>
                            </td></tr>";
    ?>
						</table>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>
<?php
}
?>
<script>
   $('input[type="file"]').change(function(e){
    var fileName = e.target.files[0].name;
    $('.custom-file-label').html(fileName);
    $("#welcomeDiv").removeClass('d-none');
  });

   $("#welcomeDiv button").on("click",function(){
    $(".custom-file-label").text("");
    document.getElementById("termination_rate_import_mapper").value = null;
    $("#welcomeDiv").addClass('d-none');
  });
   </script>
<?php
endblock()?>
<?php
end_extend()?>
