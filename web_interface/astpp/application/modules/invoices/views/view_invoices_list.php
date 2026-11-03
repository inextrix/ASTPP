<? extend('master.php') ?>
<? startblock('extra_head') ?>

<script type="text/javascript" language="javascript">
	function invoice_delete(inv_id){
				$.ajax({
			type: "POST",
			url: "<?= base_url()?>/invoices/invoice_delete_statically/"+inv_id,
			data:'',
			success:function(alt) {
				   var confirm_string = "Are you sure want to delete record.";
				var answer = confirm(confirm_string);
				if(answer){
					window.location.href="<?= base_url()?>/invoices/invoice_delete_massege/";
				}
				else{
					return false;
				}
			}
		});
				
	}
	function validateForm(){
		  if(document.getElementById('from_date').value == ""){
			$('#error_msg_from').html( "<i class='fa fa-exclamation-triangle error_triangle'></i><span class='popup_error error p-0'>Please select from date</span>" );
			document.getElementById('from_date').focus();
			 document.getElementById("error_msg_from").style.display = "block"; 
			return false;
		  }
		  if(document.getElementById('to_date').value == ""){
			$('#error_msg_to').html( "<i class='fa fa-exclamation-triangle error_triangle'></i><span class='popup_error error p-0'>Please select to date</span>" );
			document.getElementById('to_date').focus();
			document.getElementById("error_msg_to").style.display = "block"; 
			return false;
		  }
		  if(document.getElementById('to_date').value < document.getElementById('from_date').value){
			$('#error_msg_to').html( "<i class='fa fa-exclamation-triangle error_triangle'></i><span class='popup_error error p-0'>Please select to date bigger than from date</span>" );
				document.getElementById('to_date').focus();
				document.getElementById("error_msg_to").style.display = "block"; 
				return false;
		  }
		  document.getElementById('invoice').disabled = 'true';
		  document.getElementById("myForm2").submit();     
		  event.preventDefault();
	}
    $(document).ready(function() {
    	var resellerid ="<?php echo isset($reseller_id) ? $reseller_id : ''; ?>";
		$(".reseller_id_search_drp").val(resellerid);
        build_grid("invoices_grid","",<? echo $grid_fields; ?>,<? echo $grid_buttons; ?>);
        $('.checkall').click(function () {
            $('.chkRefNos').attr('checked', this.checked);
        });
        $("#invoice_search_btn").click(function(){
            post_request_for_search("invoices_grid","","invoice_search");
        });        
        $("#id_reset").click(function(){
            clear_search_request("invoices_grid","");
            $("#accountid_search_drp").html("<option value='' selected='selected'><?php echo gettext("--Select--")?></option>");
        });
        $("#generate_search").click(function(){
			$("#search_generate_bar").slideToggle("slow");
        });
        $("#invoice_date").datepicker({uiLibrary: 'bootstrap4',
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd',
            footer:true
         });		
        var invoice_from_date = date;
    	var invoice_to_date = date;

        $("#invoice_from_date").datepicker({
			value:invoice_from_date,
			uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd',
            footer:true
        });
        $("#invoice_to_date").datepicker({
			value:invoice_to_date,
			uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd',
            footer:true
        });
        $("#from_date").val('');
        $("#from_date").datepicker({
			uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd',
            footer:true
        });		
        $("#from_date").change(function(){
			$('#error_msg_from').text('');
			return false;
		});
        $("#to_date").val('');
        $("#to_date").datepicker({
			uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd',
            footer:true
        });
	    $("#to_date").change(function(){
			$('#error_msg_to').text('');
			return false;
		});
		$("#accountid").change(function(){
			$('#error_msg_port').text('');
			return false;
		});
		$("span.input-group-append").addClass('align-self-end').removeClass('input-group-append');
		
		
		$(".reseller_id_search_drp").change(function(){
			
                if(this.value!=""){
					$.ajax({
						type:'POST',
						url: "<?= base_url()?>/invoices/reseller_customerlist/",
						data:"reseller_id="+this.value, 
						success: function(response) {
							 $("#accountid_search_drp").html(response);
							 $("#accountid_search_drp").prepend("<option value='' selected='selected'><?php echo gettext("--Select--")?></option>");
							 $('.accountid_search_drp').selectpicker('refresh');
						}
					});
				}else{
						$("#accountid_search_drp").html("<option value='' selected='selected'><?php echo gettext("--Select--")?></option>");
						$('.accountid_search_drp').selectpicker('refresh');
					}	
        });
        $(".reseller_id_search_drp").change();   
		
    });
</script>

<? endblock() ?>

<? startblock('page-title') ?>
    <?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>     
<?php
$login_type = $this->session->userdata['userlevel_logintype'];
$account_data = $this->session->userdata("accountinfo");
$id = $account_data['id'];
$permissioninfo = $this->session->userdata('permissioninfo');
$logintype = $this->session->userdata('logintype');
if ((isset($permissioninfo['invoices']['invoice_list']['generate'])) && (astpp_eq0($permissioninfo['invoices']['invoice_list']['generate'])) && ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3') or ($permissioninfo['login_type'] == '-1')) {
    ?>

<div class="col-md-12 pb-4 px-0 text-right">
	<span id="generate_search" style="cursor: pointer;"
		class='btn btn-info'><?php echo gettext('Generate Invoice'); ?></span>
</div>
<?php }?>
<section class="slice color-three">
	<div class="w-section inverse p-0">
		<div class="col-12">
			<div class="portlet-content mb-4" id="search_bar"
				style="display: none">
                    	<?php echo $form_search; ?>
    	        </div>
		</div>
	</div>
</section>


<div class="main-wrapper" id="search_generate_bar"
	style="display: none;">
	<div class="content" class="container-fluid">
		<div class="col-md-12">
			<div class="row">
				<div class="p-0 col-md-12">
					<div class="mb-4 slice color-three">
						<div id="floating-label" class="card pb-4">
							<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('Generate Invoice'); ?></h3>
							<form class="" method="post"
								action="<?= base_url() ?>invoices/invoice_screen/"
								enctype="multipart/form-data" name='form1' id="myForm2">

								<div class="col-md-12">
									<div class="row">
										<div class="col-3">
											<div class="col-12 form-group">
												<label class="p-0 control-label"><?php echo gettext('From Date'); ?></label>
												<input class="col-md-12 form-control form-control-lg "
													id="from_date" name="fromdate" size="20" type="text">
											</div>
											<div class="text-danger tooltips error_div float-left p-0"
												id="error_msg_from"></div>
										</div>


										<div class="col-3">
											<div class="col-12 form-group">
												<label class="p-0 control-label"><?php echo gettext('To Date'); ?></label>
												<input class="col-md-12 form-control form-control-lg"
													value="" id="to_date" name="todate" size="20" type="text">
											</div>
											<div class="text-danger tooltips error_div float-left p-0"
												id="error_msg_to"></div>
										</div>

										<div class="col-3 form-group">
											<label class="p-0 control-label"><?php echo gettext('Accounts'); ?></label>
														<?php
            if ($login_type == - 1 || $login_type == 4 || $login_type == 2) {
                $where = "deleted = '0' AND status = '0' AND (type= '0' OR type= '3' OR type= '1')";
            }
            if ($login_type == 1) {

                $where = "deleted = '0' AND reseller_id = '$id' AND status = '0' AND (type= '0' OR type= '3' OR type='1')";
            }
            $account = $this->db_model->build_dropdown_invoices('id,first_name,last_name,number,type', 'accounts', '', $where);
            ?>
															
													   <?php  echo form_dropdown_all('accountid', $account,''); ?>
													</div>


										<div class="col-3">
											<div class="col-12 form-group">
												<label class="p-0 control-label"><?php echo gettext('Notes'); ?></label>
												<input class="col-md-12 form-control form-control-lg"
													value="" id="notes" name="notes" size="20" type="text">
											</div>
											<div class="text-danger tooltips error_div float-left p-0"
												id="error_msg_from"></div>
										</div>

									</div>
								</div>
								<div class="col-md-12 text-right">
									<input type="button" class="btn btn-success" name="invoice"
										value=<?php echo gettext("Generate Invoice"); ?> id="invoice"
										onClick="validateForm();">
								</div>

							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class="card col-md-12 pb-4">
			<form method="POST" action="del/0/" enctype="multipart/form-data"
				id="ListForm">
				<table id="invoices_grid" align="left" style="display: none;"></table>
			</form>
		</div>
	</div>
</section>

<? endblock() ?>	

<? end_extend() ?> 
