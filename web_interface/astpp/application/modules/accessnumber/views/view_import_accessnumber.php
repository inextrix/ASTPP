<? extend('master.php') ?>
<? startblock('extra_head') ?>
<? endblock() ?>
<? startblock('page-title') ?>
<?php echo gettext('Access Numbers Import Process')?>
<? endblock() ?>
<? startblock('content') ?>  
<?php if ( ! isset($csv_tmp_data)) { ?>

<section class="slice color-three padding-t-20">
	<div class="w-section inverse no-padding">

		<form method="post"
			action="<?= base_url()?>accessnumber/accessnumber_preview_file/"
			enctype="multipart/form-data" id="accessnumber_rates">
			<div class="row">
				<div class="col-md-12">
					<div class="col-md-10 clo-sm-12 float-left p-0">
						<div class="w-box card py-3">
								<?

if (isset($error) && ! empty($error)) {
        echo "<span class='row alert alert-danger m-2'>" . $error . "</span>";
    }
    ?>
								
								<h3 class="px-4"><?php echo gettext( "File must be in the following format")."(.csv):"; ?></h3>
							<p><?= $fields;?></p>
						</div>
					</div>

					<div class="col-md-2 col-sm-12 float-left pl-md-4 p-0">
						<div class="card col-md-12 form-group px-0">
							<label class="card-header text-center m-0"><?php echo gettext("Get Sample file"); ?></label>
							<div class="col-md-12 p-3">
								<a
									href="<?= base_url(); ?>accessnumber/accessnumber_download_sample_file/accessnumber_sample"
									class="btn btn-success btn-block text-light"><i
									class="fa fa-download"></i> <?php echo gettext("Download"); ?></a>

							</div>
						</div>
					</div>

				</div>
				<div class="col-md-12">
					<div class=" card col-md-12 p-0 mb-4">
						<div class="pb-4" id="floating-label">
							<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext( "Import Access Numbers"); ?></h3>
							<div class="col-md-4 col-sm-12 float-left">

								<input type="hidden" name="mode" value="Import Accessnumber" />
								<input type="hidden" name="logintype"
									value="<?= $this->session->userdata('logintype') ?>" /> <input
									type="hidden" name="username"
									value="<?= $this->session->userdata('username') ?>" />
							</div>

							<div class="col-md-12 form-group">
								<label class="control-label mb-4"><?php echo gettext("Select the file"); ?></label>
								<div class="col-12 mt-4 d-flex">
									<div class="col-md-6 float-left" data-ripple="">
										<input type="file" name="accessnumberimport"
											id="accessnumberimport" class="custom-file-input" title="Only CSV Files are allowed. You must upload a file smaller than <?php echo str_replace("M","MB", ini_get('upload_max_filesize')).'.'; ?>" /> <label
											class="custom-file-label btn-primary btn-file text-left"
											for="file"> </label>
									</div>
									<div class="col-md-6 float-left align-self-center">
										<span id="welcomeDiv" class="answer_list float-left d-none">
											<button type="button" title="Cancel" class="btn btn-danger"><?php echo gettext( "Remove"); ?></button>
										</span>
									</div>
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="p-2"><span class="mr-4 align-middle"><?php echo gettext( "Skip Header").':'; ?></span>
									<input type='checkbox' class="align-middle" name='check_header' /></label>
							</div>

						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="text-center">
						<button class="btn btn-success" id="impoddrt_termination1"
							type="submit" name="action" value="Import"><?php echo gettext( "Import"); ?></button>
						<button class="btn btn-secondary mx-2" id="ok" type="button"
							name="action" value="Cancel"
							onclick="return redirect_page('/accessnumber/accessnumber_list/')"><?php echo gettext( "Cancel"); ?></button>
					</div>
				</div>
			</div>
		</form>

	</div>
</section>

<?php }?>    
	
	<?php
if (isset($csv_tmp_data) && ! empty($csv_tmp_data)) {
    ?>
<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class="row">
			<div class="col-md-12">
				<form id="import_form" name="import_form"
					action="<?=base_url()?>accessnumber/accessnumber_import_file/<?=$check_header;?>/"
					method="POST">
					<div class="card p-4 table-responsive">
						<table width="100%" border="1"
							class="table table-bordered details_table table">
									<?php

$cnt = 0;
    foreach ($csv_tmp_data as $csv_key => $csv_value) {
        if ($csv_key < 15) {
            echo "<tr>";
            foreach ($csv_value as $field_name => $field_val) {
                if (astpp_eq0($csv_key)) {
                    $cnt ++;
                    echo "<th>" . ucfirst($field_name) . "</th>";
                } else {
                    echo "<td>" . $field_val . "</td>";
                }
            }
            echo "</tr>";
        }
    }

    echo "<tr><td colspan='" . $cnt . "'>";
    ?>
									<button type="button"
								class="btn btn-secondary mx-2 float-right" value="Back"
								onclick="return redirect_page('/accessnumber/accessnumber_import/')">Back</button>
							<button type="submit" class="btn btn-success float-right"
								id="Process" value="Process">Process</button>
									<?php echo "</td></tr>";?> 
								</table>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>
<?php } ?>
<script>
		$('input[type="file"]').change(function(e){
			var fileName = e.target.files[0].name;
			$('.custom-file-label').html(fileName);
			$("#welcomeDiv").removeClass('d-none');
		});

		$("#welcomeDiv button").on("click",function(){
			$(".custom-file-label").text("");
			document.getElementById("accessnumberimport").value = null;
			$("#welcomeDiv").addClass('d-none');
		});
	</script>
<? endblock() ?>	
	<? end_extend() ?>   
