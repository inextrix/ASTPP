<? extend('master.php') ?>
<? startblock('extra_head') ?>


<script type="text/javascript" language="javascript">
    $(document).ready(function() {
//~ window.setInterval(function(){
 //~ alert('here');
 //~ $('#support_ticket_grid').flexReload() /// call your function here
//~ }, 60000);
		
		$( window ).load(function() {
			 $(".selectpicker").removeClass("col-md-5");  
			 $(".selectpicker").addClass("col-md-3"); 
			 $("#ticket_type").removeClass("col-md-3");  
			 $("#ticket_type").addClass("col-md-12");
			 $("#department_search").removeClass("col-md-3");  
			 $("#department_search").addClass("col-md-12");
		});
      
        build_grid("support_ticket_grid","",<? echo $grid_fields; ?>,<? echo $grid_buttons; ?>);
        $('.checkall').click(function () {
	    $('.chkRefNos').prop('checked', $(this).prop('checked'));
	    //if you want to select/deselect checkboxes use this
        });
        $("#supportticket_search_btn").click(function(){
		 
            post_request_for_search("support_ticket_grid","","supportticket_search");
        });        
        $("#id_reset").click(function(){
            clear_search_request("support_ticket_grid","");
        });
        $("#close_btn").click(function(){
		$("#search_bar").slideToggle("");
		$("#left_panel_search_form").slideToggle("slow");
	}); 
       
    });
</script>

<script>
function deleteItem(){
    if (confirm("Are you sure you want to close?")){               
        var notChecked = [], checked = [];
         
      var result = "";                        
    var idarr = [];
    $(".chkRefNos").each( function () {
        if(this.checked == true) {     
            result += ",'"+$(this).val()+"'";
	    idarr.push($(this).val());
        } 
    });     
     result1 = result.substr(1);
        var destination = "<?= base_url();?>supportticket/supportticket_close_multiple";
        
       $.ajax({
						type: "POST",
						cache    : false,
						async    : true, 
						url: destination,
						 data: "selected_ids="+result1,
						 
				  		success: function(data) { 
							if(data == 1)
                                {
/*				    $('.flex_grid').flexOptions({
					newp:1
				    }).flexReload(); 
*/
			            $('.flex_grid').flexReload();
				    $('input:checkbox').removeAttr('checked');
				    $("#toast-container_error").css("display","block");
				    $(".toast-message").html("Selected records has been closed.");
				    $('.toast-top-right').delay(5000).fadeOut();
                                } else{
                                    alert("Please select atleast one record to close.");
                                }

						}
					});
    }
}
</script>
<script>
function create(){
				var url = "<?= base_url();?>supportticket/supportticket_add";
			window.location.href=url;
}
</script>
<!--
<script type="text/javascript">
setInterval( "refreshAjax();", 10000 );  ///////// 20 seconds

$(function() {
  refreshAjax = function(){$("#support_ticket_grid").flexReload();
}
});
</script>
-->
<style>
	
	
	.flexigrid {
    font-family: "Noto Sans",sans-serif;
    font-size: 12px;
    position: relative;
    border: 0px solid #eee;
    overflow-y: inherit!important;
    color: #333232;
    text-decoration: none;
    background-color: #fff;
    padding:0px 0px 10px 0px;
}
	
.flexigrid div.fbutton {
    float: left;
    display: block;
    cursor: pointer;
    padding: 0px 0px 0px 0px !important;
}
.tDiv2 div:nth-child(2){
    margin-left: 5px;
 }
 
 

</style>
<? endblock() ?>

<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>       

		<div class="col-md-12 px-0">
            	<div class="portlet-content main-box card card-block mt-0 mb-4"  id="search_bar" style="cursor:pointer; display:none">
                    	 
                    <?php //echo $form_search; ?>
                    <form action="<?php echo base_url(); ?>supportticket/supportticket_list_search/" method="post" accept-charset="utf-8" id="supportticket_search">

					  <span class="pull-right btn-close" id="global_clearsearch_filter" style="margin-top: -32px;margin-right: -38px;"><div class="card6_fb bg-info text-center text-white" style="box-shadow:0 0 0 0 rgba(0,0,0,0),0 6px 20px 0 rgba(0,0,0,0);">
					  <span class="ti-close text-white">
					  	
					  </span></div></span>
					  <div class="col-md-12 p-0" id="floating-label">
					   <div class="float-right panel_close text-light p-3" id="close_btn" style="cursor:pointer;"><i class="fa fa-remove"></i></div>
					  
					  <h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext("Search"); ?></h3>
								<div class="col-md-12">
								<div class="row">
									
									<div class="col-4 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Ticket No"); ?></label>
											<input name="support_ticket_number[support_ticket_number]" value="" 0='' size="20" class="col-md-12 form-control form-control-lg" type="text">
												<select name="support_ticket_number[support_ticket_number-string]" class="col-md-12 form-control form-control-lg selectpicker" data-live-search="true">
													<option value="5"><?php echo gettext("Begins With"); ?></option>
													<option value="1"><?php echo gettext("Contains"); ?></option>
													<option value="2"><?php echo gettext("Doesnt Contain"); ?></option>
													<option value="3"><?php echo gettext("Is Equal To"); ?></option>
													<option value="4"><?php echo gettext("Is Not Equal To"); ?></option>
													<option value="6"><?php echo gettext("Ends With"); ?></option>
												</select>	
									</div>
									
									<div class="col-4 input-group">
												<label class="search_label col-md-12 p-0"><?php echo gettext("Priority"); ?></label>
													<select name="priority" class="col-md-12 form-control form-control-lg selectpicker" data-live-search="true">
														<option value="" selected="selected">--Select--</option>
														<option value="0"><?php echo gettext("High"); ?></option>
														<option value="1"><?php echo gettext("Normal"); ?></option>
														<option value="2"><?php echo gettext("Low"); ?></option>
													</select>
									</div> 
									
									<div class="col-4 input-group">
											<label class="search_label col-md-12 p-0"><?php echo gettext("Subject"); ?></label>
													<input name="subject[subject]" value="" 0='' size="20" class="col-md-12 form-control form-control-lg" type="text">
														<select name="subject[subject-string]" class="col-md-12 form-control form-control-lg selectpicker" data-live-search="true">
															<option value="5"><?php echo gettext("Begins With"); ?></option>
															<option value="1"><?php echo gettext("Contains"); ?></option>
															<option value="2"><?php echo gettext("Doesnt Contain");?></option>
															<option value="3"><?php echo gettext("Is Equal To"); ?></option>
															<option value="4"><?php echo gettext("Is Not Equal To"); ?></option>
															<option value="6"><?php echo gettext("Ends With"); ?></option>
														</select>
									</div>
							 
									
									
									<div class="col-4 input-group">
												<label class="search_label col-md-12 p-0"><?php echo gettext("Department"); ?></label>
													<select id="department_search" name="department_id" class="form-control form-control-lg selectpicker" data-live-search="true">
														<option value="" selected="selected">--Select--</option>
														 <?php foreach($department_list_result as $key=>$val){?>
															<option value=<?php echo $val['id'];?>><?php echo $val['name'];?></option>	  
															  
<!--
														  <option value="1">deep (deep.patel@inextrix.com)</option>
														  <option value="2">test (harsh.patel@inextrix.com)</option>
														  <option value="3">bansifaldu (bansi@gmail.com)</option>
-->
														  <?php }?>
													</select>
									</div>
									<!-- Manish Issue no 694 -->
									<div class="col-4 input-group">
												<label class="search_label col-md-12 p-0"><?php echo gettext("Account"); ?></label>
													<select id="accountdata_search" name="accountid" class="form-control form-control-lg selectpicker" data-live-search="true">
														<option value="" selected="selected">--Select--</option>
														 <?php foreach($accounts_data as $key=>$value){?>
															<option value="<?php echo $value['id']; ?>"><?php echo $value['first_name'].' '.$value['last_name'] .'('.$value['number'].')'; ?></option>

														  <?php }?>
													</select>
									</div>
									<!-- END --> 
									<div class="col-4 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Status"); ?></label>
										<select id="ticket_type" name="ticket_type" class="form-control form-control-lg selectpicker" data-live-search="true">
											<option value="" selected="selected">--All--</option>
											<option value="0"><?php echo gettext("Open"); ?></option>
											<option value="1"><?php echo gettext("Answered"); ?></option>
											<option value="2"><?php echo gettext("Customer-Reply"); ?></option>
											<option value="3"><?php echo gettext("On-hold"); ?></option>
											<option value="4"><?php echo gettext("Progress"); ?></option>
											<option value="5"><?php echo gettext("Close"); ?></option>
										</select>
									</div>
								</div>
							</div>
									
									
									
								<div class="col-12 p-4">
										 <input type ="hidden" name ='ajax_search' value ="1" /> 
									 <input type ="hidden" name ='advance_search' value ="1" /> 
										<div class="col-md-12 my-4">
											<button name="action" type="reset" id="id_reset" value="cancel" class="btn btn-secondary float-right mx-2">Clear</button>
											<button name="action" type="button" id="supportticket_search_btn" value="save" class="btn btn-success float-right">Search</button>
										 
										</div>
								</div>
					  </div>
					</form>

    	        </div>
    	     
            </div>



<section class="slice color-three">
	<div class="w-section inverse p-0">
    	<div class="card col-md-12 pb-4">
			<form method="POST" action="del/0/" enctype="multipart/form-data" id="ListForm">
				<div href="" class="mt-4" style="position:relative;z-index:2;"> 
					<!-- Manish issue 694 -added deleteItem() in span -->
					<span onClick="deleteItem()">
					<!-- End -->
					<?php
//harsh_27_06
				    $permissioninfo = $this->session->userdata('permissioninfo');
				    $currnet_url=current_url();

          	if((isset($permissioninfo['supportticket']['supportticket_list']['close'])) && (astpp_eq0($permissioninfo['supportticket']['supportticket_list']['close'])) && ($permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '1') or $permissioninfo['login_type'] == '-1'){?>
            	<span class="btn btn-info">
								<i class="fa fa-times-circle fa-lg"></i> <?php echo gettext("Close Support Ticket"); ?>
							</span>
						<?php
						}
						?>			
					</span>
				</div>
				<?php $account_data = $this->session->userdata("accountinfo");?>
				<table id="support_ticket_grid" align="left" style="display:none;"></table>
			</form>
		</div>
	</div>
</section>	
			


<? endblock() ?>	

<? end_extend() ?>  
