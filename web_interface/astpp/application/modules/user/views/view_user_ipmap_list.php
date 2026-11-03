<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/style.css" type="text/css"/>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
      $('.page-wrap').addClass('addon_wrap');
  });
</script>
<style>
    #err {
         height:20px !important;width:100% !important;float:left;
    }
    label.error {
        float: left; color: red;
        padding-left: .3em; 
        vertical-align: top;  
        padding-left:0px;
        width:100% !important;
    }
</style>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        build_grid("ipsettings_grid","",<? echo $grid_fields; ?>,"");
        $("#user_ipmap_search_btn").click(function(){
            post_request_for_search("ipsettings_grid","","user_ipmap_search");
        });        
        $("#id_reset").click(function(){
            clear_search_request("ipsettings_grid","");
        });
        $.validator.addMethod('IP4Checker', function(value) {
            var n = value.indexOf("/");
            if(n > 0) {
              var pattern = /^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,3}$/;
            }
            else {
              var pattern = /^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/;
            }
            return pattern.test(value);
        }, 'The IP field have not valid ip.');
        jQuery.validator.addMethod("IP_Validate", function() {
          var message_flag;
          var prefix=$("#prefix").val();
          var ip =$("#ip").val();
          $.ajax({
                 url: "<?=base_url(); ?>user/user_customer_validate_ip/",
                 type: "post",
                 async:false,
                 data: {
                        "prefix" : prefix,
                        "ip"     : ip,
                       },
                 success: function (data_response) {
                     message_flag=data_response.trim();
                 }
          });
          if(message_flag =='FALSE') {
              return false;
          }
          else {
           return true;
          }
       }, "<?php echo gettext('The IP field must contain a unique value.'); ?>"); 
        $('#ip_map').validate({
            rules: {
                ip: {
                    
                    IP4Checker  : true,
                    IP_Validate : true
                }
            },

            errorPlacement: function(error, element) {
                error.appendTo('#err');
            }
        });
        $('#name').change(function() {
            $('#err_name').html('');
        });
        $('#ip').change(function() {
            $('#err').html('');
        });
        $('#submit_form').on("click",function() {
            var error_msg='';
            if($.trim($('#ip').val()) == ''){
                $('#err').html('<?php echo gettext("The IP field is required."); ?>');
                error_msg="true";
            }
            if($.trim($('#name').val()) == ''){
                $('#err_name').html('<?php echo gettext("The Name field is required."); ?>');
                error_msg="true";
            }
            if(error_msg != ''){
                return false;
            }else{
                return true;
            }
        });

    });
</script>
<script type="text/javascript">
  $(document).ready(function(){
      $(".breadcrumb li a").removeAttr("data-ripple",""); 
  });
</script>
<? endblock() ?>
<? startblock('content') ?>        
<div id="main-wrapper">  
    <div id="content" class="container-fluid">   
        <div class="row"> 
            <div class="col-md-12 color-three border_box"> 
                <div class="float-left m-2 lh19">
                    <nav aria-label="breadcrumb">
						<ol class="breadcrumb m-0 p-0">
                         <?php $accountinfo=$this->session->userdata('accountinfo');
						  if($accountinfo['type']==1){ ?>
                          <li class="breadcrumb-item"><a href="<?= base_url() . "user/user_myprofile/"; ?>"><?php echo gettext('My Profile'); ?></a></li>
                          <?php }
                          else if(astpp_eq0($accountinfo['type']) || $accountinfo['type']==3){ ?>
								<li class="breadcrumb-item"><a href=<?= base_url() . "user/user_myprofile/"; ?>><?php echo gettext('My Account')?></a></li>
						  <?php }
                          else{ ?>
							<li class="breadcrumb-item"><a href="#"><?php echo gettext('Configuration')?></a></li>
                          <?php }?>
							
                        <li class="breadcrumb-item active">
                            <a href="<?= base_url() . "user/user_ipmap/"; ?>"><?php echo gettext('IP Settings')?></a>
                        </li>
                    </ol>
                    </nav>
                </div>

                <div class="m-2 float-right">
                  <a class="btn btn-light btn-hight" href="<?= base_url()."user/user_myprofile/"; ?>"> <i class="fa fa-fast-backward" aria-hidden="true"></i> <?php echo gettext('Back'); ?></a>
                </div>
                <?php if($accountinfo['type']==1) { ?>
                   <div class="m-2 float-right">
                    					<a class="btn btn-light btn-hight" href="<?= base_url() . "user/user_myprofile/"; ?>"> <i class="fa fa-fast-backward" aria-hidden="true"></i><?php echo gettext('Back');?></a>
                   </div>
                <?php }?>
            </div>     
            <div class="p-4 col-md-12">
                <div class="col-md-12 p-0">
                    <div class="float-left" id="left_panel_add">
                        <span class="btn btn-line-warning"> <i class="fa fa-plus-circle fa-lg"></i><?php echo gettext('Create');?></span>
                    </div>


                    <div id="left_panel_delete" class="pull-left margin-t-0 padding-x-4" onclick="delete_multiple('/user/user_ipmap_delete_multiple/')">
                            <span class="btn btn-line-danger">
                                <i class="fa fa-times-circle fa-lg"></i>
                                <?php echo gettext('Delete');?>
                            </span>
                    </div>

                    
                        <div  id="show_search" class= "btn btn-warning btn float-right"><i class="fa fa-search"></i><?php echo gettext('Search');?></div>
                    
                </div>   
                <div class="col-12">
					<div class="portlet-content my-4" id="left_panel_form" style="display: none;">
                     <div id="floating-label" class="card pb-4">
                        <h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext('New IP');?></h3>
                        <form class="row px-4" method="post" name="ip_map" id="ip_map" action="<?= base_url()."user/user_ipmap_action/add/" ?>">
                                <div class='col-md-4'> 
                                    <div class='col-md-12 form-group p-0'>
                                        <label class="no-padding control-label"><?php echo gettext('Name');?> *</label>
                                        <input class="col-md-12 form-control form-control-lg m-0" id="name" name="name" size="16" type="text"/>
                                    </div>
                                    <span id="err_name" style="color:red"></span>
                                </div>
                                <div class='col-md-4'>
                                    <div class='col-md-12 form-group p-0'>
                                        <label class="no-padding control-label"><?php echo gettext('IP'); ?> *</label>
                                        <input id='ip' class="col-md-12 form-control form-control-lg m-0" name="ip" size="22" type="text">
                                    </div>
                                    <span id="err" style="color:red"></span>
                                </div>
                                <div class='col-md-4 form-group'>
                                    <label class="no-padding control-label"><?php echo gettext('Prefix');?></label>
                                    <input id='prefix' class="col-md-12 form-control form-control-lg m-0" name="prefix" size="16" type="text">
                                    
                                </div>
                            <div class="col-12 my-4 text-center">
                              <button class="btn btn-success" name="action" value="Save" id="submit_form" type="submit"><?php echo gettext('Save');?></button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>

                    <div class="col-12">
                        <div class="portlet-content my-4"  id="search_bar" style="display:none">
                                <?php echo $form_search; ?>
                        </div>
                    </div>
                    <div class="col-12 px-4">
                        <div class="card px-4 pb-4">
                            <table id="ipsettings_grid" align="left" style="display:none;"></table>
                        </div>
                    </div>
      </div>
    </div>
  </div>
</div>
<? endblock() ?>	
<? end_extend() ?>  
<script type="text/javascript">
  $(document).ready(function(){
      $('.page-wrap').addClass('addon_wrap');
      $('.checkall').click(function () {
          $('.chkRefNos').prop('checked', $(this).prop('checked'));
      });
  });
</script>
