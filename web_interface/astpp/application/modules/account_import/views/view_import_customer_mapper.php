<?php extend('master.php') ?>
<? startblock('extra_head') ?>
<?php
if (! isset($csv_tmp_data)) {
    ?>

<?php }?>	   
<?php endblock() ?>
<?php startblock('page-title') ?>
<?php echo $page_title; ?>
<?php endblock() ?>
<?php startblock('content') ?>
<?php
if (! isset($csv_tmp_data)) {
    ?>
<section class="slice color-three">
	<div class="w-section">

		<form method="post"
			action="<?php echo base_url() ?>account_import/customer_import_preview/"
			enctype="multipart/form-data" id="account_import" name="account_import_form">
			<div class="row">
				<div class="col-md-12">
					<div class="col-md-10 col-sm-12 float-left p-0">
						<div class="w-box card py-3">
								<?php

    if (isset($error) && ! empty($error)) {
        echo "<span class='row alert alert-danger m-2'>" . $error . "</span>";
    }
    ?>
								<h3 class="px-4"><?php echo gettext("You must either select a field from your file OR provide a default value for the following fields:");?></h3>
							<p><?php echo gettext("Account Number,Password,First Name,Last Name,Company,Phone,Mobile,Email,Address,city,Province/State,Zip/Postal Code,Number Translation,Out Callerid Translation,In Callerid Translation,Concurrent Calls,CPS,Balance,Credit Limit,SIP Username,SIP Password"); ?></p>
							<i class="px-4"><?php echo gettext("Note : Records with duplicate account number and email will be ignored.");?></i>
						</div>
					</div>

					<div class="col-md-2 col-sm-12 float-left pl-md-4 p-0">
						<div class="w-box card col-md-12 form-group px-0">
							<label class="card-header text-center m-0"><?php echo gettext("Get Sample file");?></label>
							<div class="col-md-12 p-3">
								<a
									href="<?= base_url(); ?>account_import/customer_import_download_sample_file/"
									class="btn btn-success btn-block"><i class="fa fa-download"></i> <?php echo gettext("Download"); ?></a>
							</div>
						</div>
					</div>

				</div>
				<div class="col-md-12">
					<div class="card col-md-12 p-0 mb-4">
						<div class="pb-4" id="floating-label">
							<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext("Import Customers"); ?></h3>
							<div class="col-md-12">
								<div class="p-0 row">
									<input type="hidden" name="mode" value="import_account_mapper" />

									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Generate Pin"); ?></label>	 
											
											<?php echo $config_array['pin']; ?>
										</div>
									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Allow Recording"); ?> </label>	 
											<?php echo $config_array['is_recording']; ?> 
										</div>
									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Allow IP Management"); ?> </label>	 
											<?php echo $config_array['allow_ip_management']; ?> 
										</div>
									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Rate Group"); ?> </label>	 
											<?php echo $config_array['pricelist_id'];?>
										</div>
									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Create SIP Device"); ?> </label>	 
											<?php echo $config_array['sipdevice_flag']; ?> 
										</div>


									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Timezone"); ?> </label>	 
											<?php echo $config_array['timezone_id']; ?> 
										</div>

										<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Localization"); ?> </label>	 
											<?php echo $config_array['localization_id']; ?> 
										</div>

									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Country"); ?> </label>	 
											<?php echo $config_array['country_id']; ?> 
										</div>
									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Currency"); ?> </label>	 
											<?php echo $config_array['currency_id']; ?> 
										</div>

									<div class='col-md-4 form-group'>
										<label class="p-0 control-label" for='sweep_id'><?php echo gettext("Billing Schedule"); ?> </label>	 
											<?php echo $config_array['sweep_id']; ?> 
										</div>
									<div class='col-md-4 form-group'>
										<label class="p-0 control-label" for="invoice_day"><?php echo gettext("Billing Day"); ?> </label>	 
											<?php echo $config_array['invoice_day']; ?> 
										</div>

									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Account Type"); ?> </label>	 
											<?php echo $config_array['posttoexternal']; ?>
										</div>

									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Allow Local Calls"); ?> </label>	 
											<?php echo $config_array['local_call'];?>
										</div>

									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("LC Charge/Min"); ?></label>	 
											<?php echo $config_array['charge_per_min']; ?>
										</div>


									<div class='col-md-4 form-group'>
										<label class="p-0 control-label"><?php echo gettext("Email Alerts ?"); ?> </label>	 
										<?php echo $config_array['notify_flag']; ?>
									</div>



									<div class="col-md-12 form-group">
										<label class="control-label mb-4"><?php echo gettext("Select the file"); ?></label>
										<div class="col-12 mt-4">
											<div class="col-md-6 float-left" data-ripple="">
												<input type="file" name="customer_import_mapper" title="Only CSV Files are allowed. You must upload a file smaller than <?php echo str_replace("M","MB", ini_get('upload_max_filesize')).'.'; ?>"
													id="customer_import_mapper" class="custom-file-input" /> <label
													class="custom-file-label btn-primary btn-file text-left"
													for="file"> </label>
											</div>
											<div class="col-md-6 float-left">
												<span id="welcomeDiv" class="answer_list float-left d-none">
													<button type="button" title="Cancel" class="btn btn-danger"><?php echo gettext("Remove"); ?></button>
												</span>
											</div>
										</div>
									</div>


								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="text-center">
						<button class="btn btn-success" type="submit" name="action"
							value="Import"><?php echo gettext("Import");?></button>
						<a href="<?php echo base_url() . 'accounts/customer_list/' ?>">
							<button class="btn btn-secondary mx-2" id="ok" type="button"
								name="action" value="Cancel"><?php echo gettext("Cancel");?></button>
						</a>
					</div>
				</div>
		
		</form>

	</div>
	</div>
</section>
<script type="text/javascript" language="javascript">
	$(document).ready(function() {
		
		$(".sweep_id").change(function(){
			var sweep_id =$('.sweep_id option:selected').val();
			if(sweep_id != 0){
				$.ajax({
					type:'POST',
					url: "<?= base_url() ?>/accounts/customer_invoice_option/<?= $invoice_date ?>",
					data:"sweepid="+sweep_id, 
					success: function(response) {
                       $('.invoice_day').parents('div.form-group').removeClass("d-none");               
                       $('.invoice_day').selectpicker();
                       $('.invoice_day').html(response);
                       $('.selectpicker').selectpicker();

                    }
                });
			}else{

                $('.invoice_day').parents('div.form-group').addClass("d-none");                  
            }
        });
		$(".sweep_id").change();
	});


</script>
<?php
}
if (!empty($csv_tmp_data)) { ?>
<section class="slice color-three padding-b-20">
	<div class="w-section inverse p-0">
		<div class="content">
			<div class="pop_md col-12 pb-6 pt-2">
				<form id="import_form" name="import_form"
					action="<?php echo base_url() ?>account_import/customer_import_data/"
					enctype="multipart/form-data" method="POST">
					<div class="col-md-6 col-sm-6 col-sm-12 no-padding float-left">
						<div class="col-12 padding-x-4">
							<ul class="card no-padding">
								<div class="pb-6" id="floating-label">
									<h3 class="bg-secondary text-light p-3 m-0 rounded-top"><?php gettext("Account Information"); ?></h3>
									<table class="table">
										<thead>
											<tr>
												<th><?php echo gettext("Account");?></th>
												<th><?php echo gettext("DEFAULT VALUE");?></th>
												<th><?php echo gettext("Select Account");?></th>
											</tr>
										</thead>
										<tbody>
								 <?php
        foreach ($mapto_fields['general_info'] as $csv_key => $csv_value) {
        	// ASTPPCOM-1358_gautam_start
            $custom_value = $csv_value . gettext("-select");
            // ASTPPCOM-1358_gautam_end
            $params_arr = array(
                "id" => $custom_value,
                "name" => $custom_value,
                "class" => $custom_value
            );
            echo "<tr>";
            echo "<td><b>" . gettext($csv_key) . '(' . gettext($csv_value) . ")</b></td>";
            echo "<td>$mapper_array[$csv_value]</td>";
            echo "<td>";
            if ($csv_value != "number" && $csv_value != "email") {
                echo str_replace('col-md-5', 'col-md-12', str_replace('form-control', '', form_dropdown_all($params_arr, $file_data, '')));
            } else {
                echo str_replace('col-md-5', 'col-md-12', str_replace('form-control', '', form_dropdown($params_arr, $file_data, '')));
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
								</tbody>
									</table>
								</div>
							</ul>
						</div>
					</div>
					<div class="col-md-6 col-sm-6 col-sm-12 no-padding float-left">
						<div class="col-12 padding-x-4">
							<ul class="card no-padding">
								<div class="pb-6" id="floating-label">
									<h3 class="bg-secondary text-light p-3 m-0 rounded-top"><?php echo gettext("Billing Information");?></h3>

									<table class="table">
										<thead>
											<tr>
												<th><?php echo gettext("Account");?></th>
												<th><?php echo gettext("DEFAULT VALUE");?></th>
												<th><?php echo gettext("Select Account");?></th>
											</tr>
										</thead>
										<tbody>
								<?php
        foreach ($mapto_fields['settings'] as $csv_key => $csv_value) {
        	// ASTPPCOM-1358_gautam_start
            $custom_value = $csv_value . gettext("-select");
            // ASTPPCOM-1358_gautam_end
            $params_arr = array(
                "id" => $custom_value,
                "name" => $custom_value,
                "class" => $custom_value
            );
            echo "<tr>";
            echo "<td><b>" . gettext($csv_key) . '(' . gettext($csv_value) . ")</b></td>";
            echo "<td>$mapper_array[$csv_value]</td>";
            echo "<td>";
            echo str_replace('col-md-5', 'col-md-12', str_replace('form-control', '', form_dropdown_all($params_arr, $file_data, '')));
            echo "</td>";
            echo "</tr>";
        }
        ?>
							</tbody>
									</table>
								</div>
							</ul>
						</div>
					</div>
			
			</div>
			<input type="hidden" name="post_array"
				value="<?php echo htmlspecialchars($post_array); ?>" /> <input
				type="hidden" name="mode" value="import_customer_mapper" />
			<div class="col-12 card">
				<h2 class="h2 card-header"> <?php echo gettext("Import File Data.");?></h2>
				<div class="p-4">
					<div class="table-responsive">
						<table width="100%" border="1"
							class="table table-bordered details_table">
					<?php
    $cnt = 1;
    foreach ($csv_tmp_data as $csv_key => $csv_value) {
        if ($csv_key <= 5) {
            echo "<tr>";
            foreach ($csv_value as $field_name => $field_val) {
                if (astpp_eq0($csv_key)) {
                    echo "<th>" . ucfirst($field_val) . "</th>";
                } else {
                    echo "<td class='portlet-content'>" . $field_val . "</td>";
                    $cnt ++;
                }
            }
            echo "</tr>";
        }
    }
    echo "<tr><td colspan='" . $cnt . "'>
                     <input type='submit' class='btn btn-success float-left' id='Process' value='".gettext('Process Records')."'/>
                     <a href='" . base_url() . "accounts/customer_list/'><input type='button' class='btn btn-secondary mx-2 float-left' value='".gettext('Back')."'/></a></td></tr>";
    ?>
                 </table>
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
	</div>
</section>
<?php
} ?>

<script>
	$('input[type="file"]').change(function(e){
		var fileName = e.target.files[0].name;
		$('.custom-file-label').html(fileName);
		$("#welcomeDiv").removeClass('d-none');
	});

	$("#welcomeDiv button").on("click",function(){
		$(".custom-file-label").text("");
		document.getElementById("customer_import_mapper").value = null;
		$("#welcomeDiv").addClass('d-none');
	});
</script>
<?php
endblock() ?>
<?php
end_extend() ?>

