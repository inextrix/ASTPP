<? extend('master.php') ?>
<? startblock('extra_head') ?>
<? endblock() ?>
<? startblock('page-title') ?>
    <?= $page_title ?>
<? endblock() ?>
<? startblock('content') ?>  


   <?php if ( ! isset($csv_tmp_data)) { ?>

<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<form method="post"
			action="<?= base_url()?>rates/termination_rate_preview_file/"
			enctype="multipart/form-data" id="termination_rate" name="import_termination_rate">
			<div class="row">
				<div class="col-md-12">
					<div class="col-md-10 clo-sm-12 float-left p-0">
						<div class="w-box card py-3">
							<?

if (isset($error) && ! empty($error)) {
        echo "<span class='row alert alert-danger m-2'>" . $error . "</span>";
    }
    ?>
						   <h3 class="px-4"><?php echo gettext("File must be in the following format")."(.csv):"; ?></h3>
						   <?php echo "<p>Code,Destination,Connection Cost ($currency),Grace Time,Cost / Min ($currency),Initial Increment,Increment,Strip,Prepend.</p>";?>
						 </div>
					</div>
					<div class="col-md-2 col-sm-12 float-left pl-md-4 p-0">
						<div class="w-box card col-md-12 form-group px-0">
							<label class="card-header text-center m-0"><?php echo gettext("Get Sample file"); ?></label>
							<div class="col-md-12 p-3">
								<a
									href="<?= base_url(); ?>rates/customer_rates_download_sample_file/terminationrates_sample"
									class="btn btn-success btn-block text-light"><i
									class="fa fa-download"></i> <?php echo gettext("Download"); ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="card col-md-12 p-0 mb-4">
						<div class="pb-4" id="floating-label">
							<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext("Import Termination Rates"); ?></h3>
							<div class="p-0">
								<div class="col-md-4 form-group">
									<label class="p-0 control-label"><?php echo gettext("Trunk List"); ?></label>
								   <?

$trunklist = form_dropdown('trunk_id', $this->db_model->build_dropdown("id,name", "trunks", "where_arr", array(
        "status " => "0"
    )), '');
    echo $trunklist;
    ?>
							   </div>
								<input type="hidden" name="mode" value="import_termination_rate" />
								<input type="hidden" name="logintype"
									value="<?= $this->session->userdata('logintype') ?>" /> <input
									type="hidden" name="username"
									value="<?= $this->session->userdata('username') ?>" />
								<div class="col-md-12 form-group">
									<label class="control-label mb-4"><?php echo gettext("Select the file"); ?></label>
									<div class="col-12 mt-4 d-flex">
										<div class="col-md-4 float-left" data-ripple="">
											<input type="file" name="termination_rate_import"
												id="termination_rate_import" title="Only CSV Files are allowed. You must upload a file smaller than <?php echo str_replace("M","MB", ini_get('upload_max_filesize')).'.'; ?>"
												class="custom-file-input fileupload" /> <label
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

								<div class="col-sm-12">
									<label class="p-0"><span class="mr-4 align-middle"><?php echo gettext("Skip Header"); ?></span>
										<input type='checkbox' class="align-middle"
										name='check_header' /></label>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="text-center">
							<button class="btn btn-success" id="import_termination_rate"
								type="submit" name="action" value="import"><?php echo gettext("Import"); ?></button>
							<button class="btn btn-secondary mx-2" id="ok" type="button"
								name="action" value="cancel"
								onclick="return redirect_page('/rates/termination_rates_list/')"><?php echo gettext("Cancel");?></button>
						</div>
					</div>
				</div>
		
		</form>
	</div>
</section>

<?}?>    
        
<?php
if (isset($csv_tmp_data) && ! empty($csv_tmp_data)) {
    ?>

<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class="row">
			<div class="col-md-12">
				<form id="import_form" name="import_form"
					action="<?=base_url()?>rates/termination_rate_rates_import/<?= $trunkid?>/<?=$check_header?>/"
					method="POST">
					<div class="card p-4 table-responsive">
						<table width="100%" border="1"
							class="table table-bordered details_table">
                <?

// $cnt = 10;
    $cnt = 1;
    foreach ($csv_tmp_data as $csv_key => $csv_value) {
        if ($csv_key < 15) {
            echo "<tr class='thead-light'>";
            foreach ($csv_value as $field_name => $field_val) {
                if (astpp_eq0($csv_key)) {
                    echo "<th>" . ucwords($field_val) . "</th>";
                } else {
                    echo "<td>" . $field_val . "</td>";
                    	$cnt ++;
                }
            }
            echo "</tr>";
        }
    }

    echo "<tr><td colspan='" . $cnt . "'>";
    ?>
                        <button type="button"
								class="btn btn-secondary ml-2 float-right"
								onclick="return redirect_page('/rates/termination_rate_import/')"><?php echo gettext("Back"); ?></button>
							<button type="submit" class="btn btn-success float-right"
								id="Process" value="Process"><?php echo gettext("Process"); ?></button>
                        
                   <?php

echo "</td></tr>";
    ?> </table>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<?} ?>
<script>
 $('input[type="file"]').change(function(e){
        var fileName = e.target.files[0].name;
        $('.custom-file-label').html(fileName);
        $("#welcomeDiv").removeClass('d-none');
    });

 $("#welcomeDiv button").on("click",function(){
  $(".custom-file-label").text("");
  document.getElementById("termination_rate_import").value = null;
  $("#welcomeDiv").addClass('d-none');
 });
        </script>
<? endblock() ?>	
<? end_extend() ?>  
