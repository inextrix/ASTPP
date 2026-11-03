<?php if(!empty($this->session->userdata('accountinfo'))) { 
 include('header.php'); 
 }else{
 include('header_webpage.php');
} ?>

<section class="slice color-one">
 <div class="w-section inverse border_box p-0 border-top">
   <div class="">
     <div class="row">
<?php

$url = "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

if (strpos($url, 'customer_cdrs') != true) {
   
?>
        <div class="col-md-12 d-flex">
	        <div class="col-8 float-left align-self-center dashboard-left">
                    <h2 class="text-light my-3 fw4"><? start_block_marker('page-title') ?><? end_block_marker() ?>	</h2>
	        
	        
                <span id="error_msg" class=" success"></span>
              </div>
                  <?php if(isset($dashboard_flag) && $dashboard_flag){?>
                  <div class="col-4 float-right pl-2 align-self-center dashboard-right">
                      <ul class="">
                          <li class="active float-right">
                          <a data-ripple=" " class="btn text-light" href="/addons/addons_list/Community" style="position: relative;background: #3b3280;"> <i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i> <?php echo gettext('Get Addons'); ?></a>
                          </li>
                      </ul>       
                  </div>
                  <?php } ?>
                  <?php if(isset($back) && $back){?>
                  <div class="col-4 float-right pl-2 align-self-center dashboard-right">
					  
						<div class="float-right">
						  <a class="btn btn-primary" href="<?php echo base_url().$back_url;?>"><i class="fa fa-fast-backward"></i> <?php echo gettext('Back'); ?></a>
						</div>
						
                      
                  </div>
                  <?php } ?>
                 <div class="float-right"> </div>

	        <?php if (isset($test_email_flag) && $test_email_flag) { ?>
	                <div id="show_search" class="float-right btn btn-warning btn margin-t-51"><a data-ripple onclick="PopupCenter('<?=base_url()?>newmail/',resizable=1,width=580,height=700) "><font color="#fff"><i class= " fa fa-envelope-o"></i> &nbsp;<?php echo gettext('Test Mail'); ?></font></a></div>
                <?php } ?>
	        <div class="col-4 float-right pl-2 align-self-center dashboard-right">
		 <?php if (isset($batch_update_flag) && $batch_update_flag) { 
			 
			 $permissioninfo = $this->session->userdata('permissioninfo');
                        $currnet_url=current_url();
                        $url_explode= explode('/',$currnet_url);
                        $module_name= $url_explode[3];
                        $sub_module_name= $url_explode[4];

                        $logintype = $this->session->userdata('logintype');
                        if((isset($permissioninfo[$module_name][$sub_module_name]['batch_update']) && astpp_eq0($permissioninfo[$module_name][$sub_module_name]['batch_update'])) or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' ){



                      
			 ?>
			 
                <div id="updatebar" class="float-right btn-update btn ml-3 py-1"><i class="fa fa-wrench fa-lg"></i> <?php  ?></div>
                <?php }}?>
	        <?php if (isset($search_flag) && $search_flag) {
                        $permissioninfo = $this->session->userdata('permissioninfo');
                        $currnet_url=current_url();
                        $url_explode= explode('/',$currnet_url);
                        $module_name= $url_explode[3];
                        $sub_module_name= $url_explode[4];
                        $logintype = $this->session->userdata('logintype');
                        // ASTPPCOM-941 Start
                        if($module_name == 'login_activity' && $permissioninfo['login_type'] == '2'){ ?>
                          <div id="show_search" <?php if($color_flag == 1){ ?> style="background:green;border-color:green;color:aliceblue" <?php }  ?> class="float-end btn btn-warning py-1"><i class="fa fa-search"></i> <?php  ?></div>
                        <?php 
                        }
                        // ASTPPCOM-941 END
                        if((isset($permissioninfo[$module_name][$sub_module_name]['search']) && astpp_eq0($permissioninfo[$module_name][$sub_module_name]['search'])) or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3' ){
                                ?>

	                <div id="show_search" class="float-right btn btn-warning py-1"><i class="fa fa-search"></i> <?php  ?></div>
                <?php }} ?>
                <!-- ASTPPCOM-941 Start -->
                <?php if (isset($report_flag) && $report_flag ) {
                 ?>
                  <div class='d-flex justify-content-end mainmenu report_dd text-end'>
                    <?php $action=$this->uri->segment(2);
                      $activityReport='';
                      $login_activity_list='';
                      if(isset($action) && $action =='login_activity_list'){
                        $login_activity_list="selected='selected'";
                      }else{
                        $activityReport="selected='selected'";
                      }
                    ?>
                  <select class="selectpicker form-control col-md-4 mr-4" id="report_change" >
                    <option value="<?php echo base_url();?>activity_report/activityReport/" <?php echo $activityReport; ?> ><?php echo gettext('Call Activity Report'); ?></option>
                    <option value="<?php echo base_url();?>login_activity/login_activity_list/" <?php echo $login_activity_list; ?> ><?php echo gettext('Login Activity Report'); ?></option>
                  </select>
                  </div>
              
             <?php } ?> 
             <!-- ASTPPCOM-941 END -->
                <?php if (isset($back_flag) && $back_flag) {?>
                
					<ul class="">
                <li class="active float-right">
				<a data-ripple class="btn btn-primary" href="<?= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI']?>"> <i class="fa fa-fast-backward" aria-hidden="true"></i> <?php echo gettext('Back'); ?></a>
				</li></ul>
                <?php } ?>
                
                 <?php if (isset($full_screen) && $full_screen) {?>
					<div id="show_full_screen" class="fullscreen float-right btn btn-warning ml-3 py-1"><i class="glyphicon glyphicon-fullscreen"></i> <?php echo gettext('Full Screen'); ?></div>
				<?php } ?>
                
                </div>
        	<div class="col-md-12 p-0"></div>        	
	  </div>
<?php } ?>
     </div>
    </div>
  </div>    
</section>
<section class="page-wrap p-4">
<div id="toast-container" class="toast-top-right col-md-6" style="display:none;" >
 <div class="toast fa-check toast-success1">
        <button class="toast-close-button">
            <i class="fa fa-close"></i>
        </button>
        <div class="toast-message">
                    Success message
        </div>
  </div>
</div>

<div id="toast-container_error" class="toast-top-right col-md-6" style="display:none;z-index:999"> 
<div class="toast fa fa-times toast-danger1">
        <button class="toast-close-button">
            <i class="fa fa-close"></i>
        </button>
        <div class="toast-message">
                    Error message light
        </div>
  </div>
</div>
<div id="toast-container_ok" class="toast-top-right col-md-6" style="display:none;z-index:999;">
<div class="toast toast-danger1">
        <div class="toast-message">
                    Error message light
        </div>
  </div>
</div>
<?php
	$astpp_msg = false;
	$msg_type = "";
	$astpp_err_msg = $this->session->flashdata('astpp_errormsg');
	if ($astpp_err_msg) {
		$astpp_msg = ucfirst($astpp_err_msg);
		$msg_type = "error";
	}
    
   $astpp_notify_msg = $this->session->flashdata('astpp_notification');
   if ($astpp_notify_msg) {  
		$astpp_msg = ucfirst($astpp_notify_msg);
		$msg_type = "notification";
   }
   if ($astpp_msg) {
?>
<script> 
    var validate_ERR = '<?= $astpp_msg; ?>';
    var ERR_type = '<?= $msg_type; ?>';
    display_astpp_message(validate_ERR,ERR_type);
</script>
<?php } ?>

<?if ($this->session->flashdata('astpp_danger_alert')!=''){?>
<div class="container-fluid alert alert-danger alert-dismissible fade show">
  <strong>Danger!</strong> <?=$this->session->flashdata('astpp_danger_alert');?>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<?}?>

<? start_block_marker('content') ?><? end_block_marker() ?>
<?php if(!empty($this->session->userdata('accountinfo'))) { 
 include('footer.php'); 
} else {
include('footer_webpage.php'); 
 } ?>


