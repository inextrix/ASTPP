<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/> 
 <title>
	 <?php
	$user_header = $this->session->userdata('user_header');

	if (isset($user_header) && $user_header != '') { ?>
		<? start_block_marker('page-title') ?><? end_block_marker() ?> | <?php echo $user_header; ?>
	<?php
	} else { ?>
		<? start_block_marker('page-title') ?><? end_block_marker() ?> | ASTPP - Open Source Voip Billing Solution
	<?php
	}
	?>
</title>
<?php  
	$date = $this->common->get_current_login_type_timezone();  
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
		$domain = "https://".$_SERVER["HTTP_HOST"]."/";
	}else{
		$domain = "http://".$_SERVER["HTTP_HOST"]."/";
        }
	$http_host=$_SERVER["HTTP_HOST"];
	$this->db->select('favicon');
	$this->db->where("domain LIKE '%$domain%'");
	$this->db->or_where("domain LIKE '%$http_host%'");
	$user_favicon=(array)$this->db->get_where("invoice_conf")->first_row();
        $acc_info=$this->session->userdata('accountinfo'); ?>
<?php if(!empty($user_favicon['favicon'])) {  ?>
        <link rel="icon" href="<? echo base_url(); ?>upload/<? echo $user_favicon['favicon'] ?>"/>
<?php } else { ?>
    <link rel="icon" href="<? echo base_url(); ?>assets/images/favicon.ico"/>
<?php } ?>
	<script>
		var page_gettext = "<?php echo gettext('Page '); ?>";
		var no_records_gettext = "<?php echo gettext('No Records'); ?>";
		var records_gettext = "<?php echo gettext(' Records'); ?>";
		var of_gettext = "<?php echo gettext(' of '); ?>";
		var date = "<?php echo gettext($date);?>";
	</script>

    <script language="javascript" type="text/javascript">
 	function Termination_Rates(){
 		var flag = 'termination_rates';
		$.ajax({
	   		type: "POST",
	   		url: "<?= base_url()?>login/test/",
	   		data:{'flag':flag},
	   		success:function(alt) {
	   			if (flag == 'termination_rates'){
					location.href = "<?php echo base_url();?>rates/termination_rates_list/";	   	
	   			}	
	   		}
	   	});

    }
	var base_url = '<?php echo base_url(); ?>';
	var num_default_grid_rows = '<?php echo Common_model::$global_config ["system_config"]["number_of_default_rows"]; ?>';
	function seetext(x){
		x.type = "text";
	}
	function hidepassword(x){
		x.type = "password";
	}
	function processForm(id,table) {
	  var url="<?php echo base_url(); ?>get_status/"+id; 

	  var status='false';
	  if($('#switch'+id).is(':checked')){
		status='true';
	  } 
	  $.ajax({
	      type:"POST",
	      url:url,
	      data:{"status":status,"id":id,"table":table},
	      // switch back if the server refused (no permission for this row)
	      error:function(){ $('#switch'+id).prop('checked', status != 'true'); },
			//~ success:function(data){ alert(data);
	
		//~ }
	  });
	}
	
	function process_email(id,table) {
	  var url="<?php echo base_url(); ?>email_status/"+id;
	  var status='false';
	  if($('#switch_email'+id).is(':checked')){
		status='true';
	  } 
	    $.ajax({
	      type:"POST",
	      url:url,
	      data:{"is_email_enable":status,"id":id,"table":table},
	      error:function(){ $('#switch_email'+id).prop('checked', status != 'true'); },
	      });
	 }
	 
	 function process_sms(id,table) {
	  var url="<?php echo base_url(); ?>sms_status/"+id;
	  var status='false';
	  if($('#switch_sms'+id).is(':checked')){
		status='true';
	  } 
	  $.ajax({
	      type:"POST",
	      url:url,
	      data:{"is_sms_enable":status,"id":id,"table":table},
	      error:function(){ $('#switch_sms'+id).prop('checked', status != 'true'); },
	      });
	 }
	 
	 function process_alert(id,table) {
	  var url="<?php echo base_url(); ?>alert_status/"+id;
	  var status='false';
	  if($('#switch_alert'+id).is(':checked')){
		status='true';
	  } 
	  $.ajax({
	      type:"POST",
	      url:url,
	      data:{"is_alert_enable":status,"id":id,"table":table},
	      error:function(){ $('#switch_alert'+id).prop('checked', status != 'true'); },
	      });
	 }
	 
	
    </script>      
     <link href="<?= base_url() ?>assets/css/checkbox.css" rel="stylesheet"/>
     <link href="<?= base_url() ?>assets/css/bootstrap.min.css" rel="stylesheet"/>
     <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-select.css"/>
     <link href="<?= base_url() ?>assets/css/sidebar_style.css" rel="stylesheet" type="text/css"/>
     <link href="<?php echo base_url(); ?>/assets/css/jquery.datetimepicker.min.css" rel="stylesheet" />
     <link rel="stylesheet" media="all" type="text/css" href="<?php echo base_url(); ?>/assets/css/tabcontent.css"/>
     <link href="<?= base_url() ?>assets/fonts/font-awesome-4.7.0/css/font-awesome.css" rel="stylesheet"/>
     <link href="<?= base_url() ?>assets/css/global-style.css" rel="stylesheet" type="text/css"/>
     <link href="<?php echo base_url(); ?>assets/css/facebox.css" rel="stylesheet" media="all" />	
     <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/flexigrid.css" type="text/css"/>
     <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">
     <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/animate.css" type="text/css"/>
     <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/style.css" type="text/css"/>
     <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/sticky_menu.css" type="text/css"/>
     <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/com-style.css" type="text/css"/>
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet"> 


     <script src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.js"></script>
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>
	 <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootstrap-select.js"></script>  
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.datetimepicker.min.js"></script>

     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/modernizr.custom.js"></script>
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/fileinput.js"></script>
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/tabcontent.js"></script>
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/respond.js"></script>
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/respond.src.js"></script>




     <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-browser/0.1.0/jquery.browser.js"></script>
 	 <!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
  -->

     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/facebox.js"></script>
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/flexigrid.js"></script>
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/module_js/generate_grid.js"></script>
        
    <noscript>
      <div id="noscript-warning">
	<?php echo gettext('ASTPP work best with JavaScript enabled'); ?>
      </div>
    </noscript>  
    <? start_block_marker('extra_head') ?>
    <? end_block_marker() ?>	

<script>

function PopupCenter(url, title, w, h) {
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
	top = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, ' width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    if (window.focus) {
        newWindow.focus();
    }
}        
</script> 
</head>
<body>   
<nav class="navbar navbar-expand-lg navbar-light bg-light mainmenu px-lg-4">
 <? if ($this->session->userdata('userlevel_logintype') != '0') {
			$http_host=$_SERVER["HTTP_HOST"];
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
                		$domain = "https://".$_SERVER["HTTP_HOST"]."/";
            		}else{
                		$domain = "http://".$_SERVER["HTTP_HOST"]."/";
         	        }
			$this->db->select('logo');
			$this->db->where("domain LIKE '%$domain%'");
			$this->db->or_where("domain LIKE '%$http_host%'");
			$user_logo=(array)$this->db->get_where("invoice_conf")->first_row();
			if ((empty($user_logo['logo'])) || (!isset($user_logo['logo']))) { 
				 $user_logo['logo']="logo.png";
			}
			?>
			
			<a class="navbar-brand p-lg-0 m-0" href="<?php echo base_url(); ?>dashboard/">
                	<img id="logo" class="img-fluid" alt="dashboard" src="<?php echo base_url(); ?>upload/<?= $user_logo['logo']?>" >
	      <? } else {
			$http_host=$_SERVER["HTTP_HOST"];
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
                		$domain = "https://".$_SERVER["HTTP_HOST"]."/";
            		}else{
                		$domain = "http://".$_SERVER["HTTP_HOST"]."/";
         	        }
			$this->db->select('logo');
			$this->db->where("domain LIKE '%$domain%'");
			$this->db->or_where("domain LIKE '%$http_host%'");
			$user_logo=(array)$this->db->get_where("invoice_conf")->first_row();
			if ((empty($user_logo['logo'])) || (!isset($user_logo['logo']))) { 
				 $user_logo['logo']="logo.png";
			}
		?> 
                	<a class="navbar-brand p-lg-0 m-0" href="<?php echo base_url(); ?>user/user/">
                	<img id="logo" class="img-fluid" alt="user_logo" src="<?php echo base_url(); ?>upload/<?= $user_logo['logo']?>" width="187" height="71" border="0">
		<? }?>
                </a>
  <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#main_menu" aria-controls="main_menu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="navbar-collapse collapse pl-lg-2 pr-0" id="main_menu" style="">
   <ul class="navbar-nav col-lg-10 col-md-12 p-0 mr-auto d-flex">
    	<?php 
			$menu_info = unserialize($this->session->userdata("menuinfo"));
			$permissioninfo = $this->session->userdata('permissioninfo');
		$allow_menu_url = $this->config->item('allow_menu_url');
		$allow_module=$this->config->item('allow_module');
			 $menu_permission_info = $this->common->menu_permission_info();
			 $sub_module_permission_info = $this->common->sub_module_permission_info();
			foreach($menu_info as $menu_key => $menu_values){
				if ($menu_key == ""){continue;}
	  ?>                
     <?php 
		  $check_menu_key =  str_replace(' ','_',strtolower($menu_key));
		  if($this->session->userdata('userlevel_logintype') == '-1' || $this->session->userdata('userlevel_logintype') == '0' || $this->session->userdata('userlevel_logintype') == '3' || in_array($check_menu_key, $menu_permission_info) || in_array($check_menu_key, $allow_module)){

			if(common_model::$global_config['system_config']['opensips']== 0 &&  $menu_key !='SIP Opensips'){
				if($acc_info['type'] == 0 && $menu_key!="Configuration"){
					echo '<li class="nav-item dropdown customer_menu text-center flex-fill"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';
				}else{
					
					if(($this->session->userdata('userlevel_logintype') == '1')){
							echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';
					}else{
						if(($this->session->userdata('userlevel_logintype') == '2')){
							if (in_array($check_menu_key, $menu_permission_info)){
								echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';
							}
						}else{
							echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';
						}	
					}
				}
			}
			if(common_model::$global_config['system_config']['opensips']== 1 &&  $menu_key != 'Opensips'){
				if($acc_info['type'] == 0 && $menu_key!="Configuration"){
					echo '<li class="nav-item dropdown customer_menu text-center flex-fill"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';  
				}else{
					
					if(($this->session->userdata('userlevel_logintype') == '1')){
							echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';
					}else{
						if(($this->session->userdata('userlevel_logintype') == '2')){
							if (in_array($check_menu_key, $menu_permission_info)){
								echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';
							}
						}else{
							echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';
						}	
					}
				}
			}
		  } 
	?>

        <ul class="dropdown-menu animated faster fadeIn" aria-labelledby="">
          <?
           foreach($menu_values as $sub_menu_key => $sub_menu_values){?>
                       
                           <?  if($sub_menu_key === 0){ 
                           	?>
								
                            <? foreach($sub_menu_values as $sub_key => $sub_menu_lables){
								$module_url = explode('/',$sub_menu_lables['module_url']);
								if(!empty($module_url) && !empty($module_url[0]) && isset($permissioninfo[$module_url[0]][$module_url[1]])  || $this->session->userdata('userlevel_logintype') == '-1' || $this->session->userdata('userlevel_logintype') == '0' || $this->session->userdata('userlevel_logintype') == '3' || in_array($module_url[1], $allow_menu_url)){


		
		if((common_model::$global_config['system_config']['paypal_status']== 1 && strtolower($sub_menu_lables["menu_label"]) =='recharge')|| (common_model::$global_config['system_config']['enterprise']== 0 &&  $sub_menu_lables["menu_label"] =='Opensips devices')  || (common_model::$global_config['system_config']['enterprise']== 0 && $sub_menu_lables["menu_label"] =='Opensips')  || (common_model::$global_config['system_config']['opensips']== 1
    &&  $sub_menu_lables["menu_label"] =='Dispatcher list') || (common_model::$global_config['system_config']['opensips']== 1 &&  $sub_menu_lables["menu_label"] =='Opensips devices') || (common_model::$global_config['system_config']['opensips']== 1 &&  $sub_menu_lables["menu_label"] =='Opensips') || (common_model::$global_config['system_config']['enterprise']== 1 && common_model::$global_config['system_config']['opensips']== 0 &&  $sub_menu_lables["menu_label"] =='SIP Devices') || (($acc_info['type'] == '3' || $acc_info['type'] == '0') && $acc_info['allow_ip_management'] == '1' && strtolower($sub_menu_lables["menu_label"]) == 'ip settings')){
								}else{
									?>
								<?php
									if(($this->session->userdata('userlevel_logintype') == '2')){
											if(isset($permissioninfo[$module_url[0]][$module_url[1]])){
										?>
										<li><a class="dropdown-item" href="<?php echo base_url().$sub_menu_lables["module_url"];?>"><?php echo gettext($sub_menu_lables["menu_label"]);?></a>
									<?php }}else{
								 ?>
									<li><a class="dropdown-item" href="<?php echo base_url().$sub_menu_lables["module_url"];?>"><?php echo gettext($sub_menu_lables["menu_label"]);?></a>
								<?}
								
								?>
                               
				<?}?>
				<?}
			} ?>

                            <?php 
                        }else{
				  $check_sub_module_key =  str_replace(' ','_',strtolower($sub_menu_key));
				  if($this->session->userdata('userlevel_logintype') == '-1' || $this->session->userdata('userlevel_logintype') == '0' || $this->session->userdata('userlevel_logintype') == '3' || in_array($check_sub_module_key, $sub_module_permission_info)){

				if(common_model::$global_config['system_config']['opensips']==0 && $menu_key !='System Configuration'){
				 ?>    
				   <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#"><span><?= gettext($sub_menu_key);?></span></a>
				<? } if(common_model::$global_config['system_config']['opensips']==1 && $sub_menu_key!="Customers") {

					?>
				    <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#"><span><?= gettext($sub_menu_key);?></span></a>
				<?php }
				if(common_model::$global_config['system_config']['opensips']==1 && $sub_menu_key=="Customers") {?>
				    <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="<?php echo base_url(); ?>accounts/customer_list/"><span><?= gettext($sub_menu_key);?></span></a>
				<?php }
				if(($acc_info['type']==3 || $acc_info['type']== 0) && $acc_info['allow_ip_management']== 1 && strtolower($sub_menu_lables["menu_label"]) !='ip settings' && $sub_menu_key!="Support Ticket"){ ?>
				    <li class="dropdown-submenu bbbb"><a class="nav-link dropdown-toggle dropdown-item" href="#"><span><?= gettext($sub_menu_key);?></span></a>
					<? }
				}else{ ?>
					    <li class="dropdown-submenu"></a>
				<?php	
				} 
							?>

							<ul class="col-4 col-12 dropdown-menu">
                                <? foreach($sub_menu_values as $sub_menu_lables){ 
									$module_url = explode('/',$sub_menu_lables['module_url']);

                                    if(isset($permissioninfo[$module_url[0]][$module_url[1]]) || $this->session->userdata('userlevel_logintype') == '-1' || $this->session->userdata('userlevel_logintype') == '0' || $this->session->userdata('userlevel_logintype') == '3' || in_array($module_url[1], $allow_menu_url)){
					 if($sub_menu_lables['menu_label'] != 'Configuration'){
				  if(common_model::$global_config['system_config']['opensips']==0 ){
				  	
					  ?>
				      <li><a class="dropdown-item" href="<?php echo base_url().$sub_menu_lables["module_url"];?>"><?php echo gettext($sub_menu_lables["menu_label"]);?></a></li>
				  <? }
				  if(common_model::$global_config['system_config']['opensips']== 1 && $sub_menu_key !='opensips'){ ?>
				      <li><a class="dropdown-item" href="<?php echo base_url().$sub_menu_lables["module_url"];?>"><?= gettext($sub_menu_lables["menu_label"]);?></a></li>
				  <? } 
			  }
				}
				} ?>
				</ul>
			  </li>
                        <?} }  ?> 
        	
        </ul>
        <? } 
        ?>

    </ul>
<?php
	$master_login_details=$this->session->userdata('master_login_details');
	if(isset($master_login_details) && $master_login_details !=''){
		$admin_id =$master_login_details['master_login_id'];
		$sub_login_arr = "<a href='".base_url()."login/login_as_admin/".$admin_id."' title='Re-Login in Admin'><i class='fa fa-sign-in mr-2' aria-hidden='true'></i></a>";
		echo $sub_login_arr;
	}
?>
    <div class="col navbar-profile dropdown">
         	 
         	 <?php
		 	 $acc_info=$this->session->userdata('accountinfo');
		 	 if($this->session->userdata('userlevel_logintype') != '0'){?>
		      <a class="btn dropdown-toggle" href="<?php echo base_url();?>dashboard/" role="button" id="admin_menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
		      	<? } else{ ?>
		    <a class="btn dropdown-toggle" href="<?php echo base_url();?>user/user/" role="button" id="admin_menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">	
		<? }
				if($this->session->userdata('logintype')!=2 && $this->session->userdata('logintype')!=4){
					$result=(array)$this->db->get_where('accounts',array("id"=>$acc_info['id']),1)->first_row();
			$variable =$result['posttoexternal']==1 ? 'Credit' : gettext('Bal');  
			$amount=$result['posttoexternal']==1 ? $result['credit_limit']-$result['balance'] :$result['balance'];

			
						$value= $this->common_model->calculate_currency($amount,'','',true);
						if($value >0){
							$color='#397A13';
						}
						if($value < 0){
							$color='#EE0E43';
						}
						if($value == 0){
							$color='#1A1919';
						}
						$balance_str = '<span style="color:'.$color.'; font-size: 10px;"><b>('.$variable.' : '.$value.')</b></span>';
				 }else{
					$balance_str = '';
				}
                
		$logged_user=$acc_info['first_name']." ".$acc_info['last_name'];
					?>
                	<span>
                            <span class="profile_name">
                                <?= gettext($logged_user)?>
                            </span>
                            <label class="text-success profile_label m-0"><?php echo $balance_str;?>
                            </label>
                        </span>                          
                 </a>
            <ul class="dropdown-menu dropdown-menu-right animated faster fadeIn rounded-0 m-0 w-100" aria-labelledby="admin_menu">
		    <? if($this->session->userdata('userlevel_logintype') != '0' && $this->session->userdata('userlevel_logintype') != '3'){?>
		      <li><a class="dropdown-item" href="<?php echo base_url();?>dashboard/"><i class="fa fa-home"></i> &nbsp;<?php echo gettext('Dashboard'); ?></a></li>
		    <? } else{?>    
		      <li><a class="dropdown-item" href="<?php echo base_url();?>user/user/"><i class="fa fa-home"></i> &nbsp;<?php echo gettext('Dashboard'); ?></a></li>
		    <? }?>
		   
<? if($this->session->userdata('userlevel_logintype') != '-1'){
?>
		    <li><a class="dropdown-item" href="<?php echo base_url();?>user/user_myprofile/"><i class= "fa fa-user"></i> &nbsp;
		    <?php echo gettext('My Profile'); ?></a></li>
		      <?
}?>
	    
		    <? if($this->session->userdata('userlevel_logintype') == '-1'){?>

		    
		    <li><a class="dropdown-item" href="https://docs.astppbilling.org/display/itplmars" target="_blank"><i class="fa fa-file-text"></i> &nbsp;<?php echo gettext('Documentation'); ?></a></li>

<li><a class="dropdown-item" href="https://jira.astppbilling.org/projects/ASTPPCOM/" target="_blank"><i class= "fa fa-bug"></i> &nbsp;<?php echo gettext('Report a Bug'); ?></a></li>




<li><a class="dropdown-item" href="/addons/addons_list/Community"><i class="fa fa-plus"></i> &nbsp;<?php echo gettext('Get Addons'); ?></a></li>
<?}?>
<li><a class="dropdown-item" href="<?php echo base_url();?>logout"><i class="fa fa-power-off"></i> &nbsp;<?php echo gettext('Log out'); ?></a></li>
        </ul>
              </div>  
  </div>
</nav>

<? if($this->session->userdata('userlevel_logintype') == '-1'){
?>
<div class="btn-quick">
	<div  href="#q_menu" id="q_toggle" class="btn rounded-circle p-btn-1" data-action="rotate">	<i class="fa fa-plus fa-2x"></i></div>
 
  <div id="q_menu">
	  <ul class="d-flex flex-column-reverse">
	    <li><a href="<?php echo base_url();?>accounts/customer_add/"><?php echo gettext("Customers"); ?></a></li>
	    <li><a href="<?php echo base_url();?>freeswitch/fssipdevices_add/"  rel="facebox"><?php echo gettext("SIP Devices"); ?></a></li>
	    <li><a href="<?php echo base_url();?>ipmap/ipmap_add/"  rel="facebox"> <?php echo gettext("IP Settings"); ?></a></li>
	    <li><a href="<?php echo base_url();?>animap/animap_add/"  rel="facebox"> <?php echo gettext("Caller IDs"); ?></a></li>
	    <li><a href="<?php echo base_url();?>products/products_did/"><?php echo gettext("DIDs"); ?></a></li>
	    <li><a href="<?php echo base_url();?>pricing/price_add/"  rel="facebox"><?php echo gettext("Rate Groups"); ?></a></li>
	    <li><a href="<?php echo base_url();?>rates/origination_rate_add/"  rel="facebox"><?php echo gettext("Origination Rates"); ?></a></li>
	    <li><a href="<?php echo base_url();?>freeswitch/fsgateway_add/" rel="facebox"><?php echo gettext("Gateways"); ?></a></li>
	    <li><a href="<?php echo base_url();?>trunk/trunk_add/"  rel="facebox"><?php echo gettext("Trunks"); ?></a></li>
	    

		<li><a href="#" onclick=Termination_Rates();><?php echo gettext("Termination Rates"); ?></a></li>


	    <li><a href="<?php echo base_url();?>products/products_add/"><?php echo gettext("Products"); ?></a></li>
	    <li><a href="<?php echo base_url();?>orders/orders_add/"><?php echo gettext("Orders"); ?></a></li>
	    <li><a href="<?php echo base_url();?>freeswitch/livecall_report/"><?php echo gettext("Live Calls"); ?></a></li>
	  </ul>
  </div>
 <?php }?> 
<div class="quick_menu_overlay"></div>
</div>
  <div id="info" style="display:none;">
  	<section class="slice m-0">
 <div class="w-section inverse p-0">
        <div class="col-md-12 p-0">
	        <h3 class="text-dark fw4 px-4 pt-4 rounded-top">Quick From</h3>
		</div>
   </div>    
</section>
<section class="slice m-0">
	<div class="w-section inverse p-0">
	  	<div class="pop_md col-12 px-4 pb-4 pt-2">
		  	<div class="col-12 p-0">
			  	<div class="col-12 p-0">
					<ul class="card p-0">
				  		<div class="pb-4" id="floating-label">
				  		<h3 class="bg-secondary text-light p-3 rounded-top">General Details</h3>
							<li class="col-md-12 form-group">
								<label class="p-0 control-label">Account<span style="color:black;"> *</span></label>
								<input name="count" value="" size="20" class="col-md-12 form-control form-control-lg" type="text">
							</li>
							<li class="col-md-12 form-group">
								<label class="p-0 control-label">Name <span style="color:black;"> *</span></label>
								<input name="count" value="" size="20" class="col-md-12 form-control form-control-lg" type="text">
							</li>
							<li class="col-md-12 form-group">
								<label class="p-0 control-label">Amount<span style="color:black;"> *</span></label>
								<input name="count" value="" size="20" class="col-md-12 form-control form-control-lg" type="text">
							</li>
						</div>
					</ul>
				</div>
			</div>
		</div>
    </div>
</section>
       </div>      

<span class="afer_row">
<span id="content">

