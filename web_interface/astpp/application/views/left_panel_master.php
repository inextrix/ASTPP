<? include('header.php'); ?>
<section class="slice color-one">
 <div class="w-section inverse p-0 border_box">
   <div class="container">
     <div class="">
	  </div>
     </div>
    </div>
  </div>    
</section>
<section class="page-wrap">
    <div class="container-fluid">
        <div class="row">
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

<div id="toast-container_error" class="toast-top-right col-md-6" style="display:none;z-index:999"> <!--  style="display:none;" -->
<div class="toast fa fa-times toast-danger1">
        <button class="toast-close-button">
            <i class="fa fa-close"></i>
        </button>
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
		$astpp_msg = $astpp_err_msg;
		$msg_type = "error";
	}
    
   $astpp_notify_msg = $this->session->flashdata('astpp_notification');
   if ($astpp_notify_msg) {  
		$astpp_msg = $astpp_notify_msg;
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
<button class="navbar-toggler bg-secondary text-light d-lg-none d-block btn-block rounded-0 py-2 collapsed btn" type="button" data-toggle="collapse" data-target=".sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle Side Navigation">
    <i class="fa fa-bars fa-2x"></i>
  </button>
<div class="sidebar collapse">
	
	<?php if(isset($addon_detail_flag) && $addon_detail_flag){?>
	<ul class="sidemenu">
		<?php 
		if ($this->uri->segment(3) == 'Community'){
		?>
		<li class="active"><a href="<?php echo base_url();?>addons/addons_list/Community">Community</a></li>
		<li><a href="<?php echo base_url();?>addons/addons_list/Enterprise">Enterprise</a></li>
		<li><a href="<?php echo base_url();?>addons/addons_list/Premium">Premium</a></li> 	<!-- ASTPPCOM-1374 mittal start -->
		<?php
		} else if($this->uri->segment(3) == 'Enterprise')  {
		?>
		<li><a href="<?php echo base_url();?>addons/addons_list/Community">Community</a></li>
		<li class="active"><a href="<?php echo base_url();?>addons/addons_list/Enterprise">Enterprise</a></li>
		<li><a href="<?php echo base_url();?>addons/addons_list/Premium">Premium</a></li> 	<!-- ASTPPCOM-1374 mittal start-end-->
		<?php
		} else{ ?>
		<li><a href="<?php echo base_url();?>addons/addons_list/Community">Community</a></li>
		<li ><a href="<?php echo base_url();?>addons/addons_list/Enterprise">Enterprise</a></li>
		<li class="active"><a href="<?php echo base_url();?>addons/addons_list/Premium">Premium</a></li> 	
		<?php }
		?><!-- ASTPPCOM-1374 mittal end-->
		</ul>
	<?php } ?>


	<?php if(isset($addon_flag) && $addon_flag){?>
	  <ul class="sidemenu">
		<li><a href="<?php echo base_url();?>addons/addons_list/Community">Community</a></li>
	    <li><a href="<?php echo base_url();?>addons/addons_list/Enterprise">Enterprise</a></li>
		<li><a href="<?php echo base_url();?>addons/addons_list/Premium">Premium</a></li> 	<!-- ASTPPCOM-1374 mittal start-end-->

	  </ul>
  	<?php } ?>
             <?php 
			 $uri_string= uri_string();
			 $uri_arr=explode("/",$uri_string);
			 $entity_name=false;
			 if(isset($uri_arr[1]) && !empty($uri_arr[1])){
					$function_explode=explode("_",$uri_arr[1]);
				   $entity_name = isset($function_explode[1]) && !empty($function_explode[1]) ? $function_explode[0] : false;
			 }
             
			 $accountinfo=$this->session->userdata('accountinfo');
			 if((!astpp_eq0($accountinfo['type']))  && $accountinfo['type'] !=3){
			  $menu_info_temp= ($uri_arr[0]=="user"
							? unserialize(RESELLERPROFILE_ARRAY):($uri_arr[0]=="accounts"&& $entity_name =='customer'
							? unserialize(CUSTOMEREDIT_ARRAY)   :($uri_arr[0]=="accounts"&& $entity_name =='provider'
							? unserialize(PROVIDEREDIT_ARRAY)   :($uri_arr[0]=="accounts"&& $entity_name =='reseller' 
							? unserialize(RESELLEREDIT_ARRAY) :($uri_arr[0]=="plans"
							? unserialize(RESELLEREDIT_ARRAY) : ($uri_arr[0] =="package" ? unserialize(PACKAGEEDIT_ARRAY):false) )))));
				if($accountinfo['type'] == '-1'){
					$menu_info = $menu_info_temp;
				}else{
					$menu_info = array();
					$permission_edit_array = unserialize(PERMISSION_EDIT_ARRAY);
					$permissioninfo=$this->session->userdata('permissioninfo');

					foreach($menu_info_temp as $menu_key => $menu_value){
						if(isset($permission_edit_array[$menu_key])){
							$permission_check_array = explode('/',$permission_edit_array[$menu_key]);
							if(isset($permissioninfo[$permission_check_array[0]][$permission_check_array[1]][$permission_check_array[2]])){
							$check_permission_flag = $permissioninfo[$permission_check_array[0]][$permission_check_array[1]][$permission_check_array[2]];
							if(isset($check_permission_flag) && astpp_eq0($check_permission_flag)){
								$menu_info[$menu_key] = $menu_value;
							}
						}else{
							$menu_info[$menu_key] = $menu_value;
						}
					   }else{

						$menu_info[$menu_key] = $menu_value;
					  }
					}
				}
			 }else{
			   $menu_info=null;
			   $current_menu_url=$uri_arr[0]."/".$uri_arr[1]."/";
			   $new_menu_info=array();
				$menus=  unserialize($this->session->userdata('menuinfo'));
				foreach($menus as $entity_key=>$entity_menu){
					foreach($entity_menu as $entity_subkey=>$entity_submenu){
						 foreach($entity_submenu as $subkey=>$submenus){
							 if($submenus['module_url']==$current_menu_url){
								 $new_menu_info=$entity_menu;
							 }
						 }
					}
				}
				foreach($new_menu_info as $key=>$value){
					foreach($value as $subvalue){
						$menu_info[$subvalue['menu_label']]=$subvalue['module_url'];
					}
				}
			 }
			 if(astpp_eq0($accountinfo['type']) || $accountinfo['type']==3 || $accountinfo['type']==4){
					$menu_info=unserialize(CUSTOMERPROFILE_ARRAY);
				
			 }
			 
			 if(!empty($menu_info)){
				echo "<ul class='sidemenu'>";
				$i=0;
				foreach($menu_info as $key=>$value){ 
				$url=($entity_name=='provider'||$entity_name =='customer' || $entity_name =='reseller' || $uri_arr[0] =="package" || $uri_arr[0] =="plans") && isset($uri_arr[2]) && !empty($uri_arr[2])
					?
				base_url().$value.$uri_arr[2]."/" : 
				base_url().$value;
				$value_flag=false;
		if($acc_info['type'] == '3' || $acc_info['type'] == '0'){
		  if($value == "user/user_ipmap/" && $acc_info['allow_ip_management'] == '1'){
			$value_flag=false;
		  }elseif(astpp_in_array('user/user_sipdevices/',$menu_info) && $value == "user/user_sipdevices/" && astpp_eq0(common_model::$global_config['system_config']['opensips'])){
			$value_flag=true;
		  }else{
			$value_flag=true;
		  }
				}else{
		  if(common_model::$global_config['system_config']['opensips'] == 1 ){
			  if($value != "accounts/".$entity_name."_opensips/"){
			  $value_flag=true;
			  }else{
			$value_flag=false;
			  }
		  }
		  if(astpp_eq0(common_model::$global_config['system_config']['opensips']) ){
			  if($value != "accounts/".$entity_name."_sipdevices/"){
			  $value_flag=true;
			  }else{
			$value_flag=false;
			  }
		  }
		  if(astpp_eq0(common_model::$global_config['system_config']['enterprise']) ){
			  if(astpp_eq0(common_model::$global_config['system_config']['opensips']) && $value == "accounts/".$entity_name."_sipdevices/"){
						  $value_flag=true;	
			  }
			  if(astpp_eq0(common_model::$global_config['system_config']['opensips']) && $value == "accounts/".$entity_name."_opensips/"){
						  $value_flag=false;	
			  }
		  }
				}
				
				$edit_permissioninfo=$this->session->userdata('edit_permissioninfo');
				
				if($value_flag){
					$class = ($value == $uri_arr[0]."/".$uri_arr[1]."/" ) ? 'active' : '';
					if($i==0)
						$class=($uri_arr[1]== $entity_name."_save") ? 'active': $class;
						$edit_permissioninfo=$this->session->userdata('edit_permissioninfo');
						$url_current = $this->uri->uri_string;
						$url_name = explode("/", $url_current);
						$key_permission="";
						if($entity_name=='customer' || $entity_name=='provider'){
								$key_permission='customer_edit';
						}else{
							$key_permission='reseller_edit';
						}
					if(astpp_eq0($this->session->userdata('logintype')) || $this->session->userdata('logintype')==3){
						echo "<li class='$class'><a href ='$url'>".gettext($key)."</a></li>";
					}else{
							echo "<li class='$class'><a href ='$url'>".gettext($key)."</a></li>";

					}
				}
				$i++;
				}
				echo "</ul>";
				}
			 ?>
</div>	
<?php

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if (astpp_eq0($length)) {
        return true;
    }

    return (substr($haystack, -astpp_n($length)) === $needle);
}
?>	
<? start_block_marker('content') ?><? end_block_marker() ?>	


<?php include('footer.php'); ?>

