<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/> 

<?php  $user_favicon = $this->session->userdata('user_favicon'); ?>
<?php if($user_favicon) {  ?>
        <link rel="icon" href="<? echo base_url(); ?>upload/<? echo $user_favicon ?>"/>
<?php } else { ?>
    <link rel="icon" href="<? echo base_url(); ?>assets/images/favicon.ico"/>
<?php } ?>

   
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
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet"> 


     <script src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.js"></script>
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>
	 <script type="text/javascript" src="<?php echo base_url(); ?>assets/status/dist/js/bootstrap-select.js"></script>  
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


</head>
<body>   
<nav class="navbar navbar-expand-lg navbar-light bg-light mainmenu px-lg-4">
 <? if ($this->session->userdata('userlevel_logintype') != '0') {
			$user_logo = $this->session->userdata('user_logo');	
			if ( ! isset($user_logo) && $user_logo == '') { 
				echo "logo.png";
			}

		?>
			<a class="navbar-brand col p-lg-0 m-0" href="<?php echo base_url(); ?>dashboard/">
                	<img id="logo" class="img-fluid" alt="dashboard" src="<?php echo base_url(); ?>upload/<?= $user_logo?>" >
	      <? } else {
			$user_logo = $this->session->userdata('user_logo');	
			if ( ! isset($user_logo) && $user_logo == '') { 
				echo "logo.png";
			}
		?> 
                	<a class="navbar-brand col p-0 mt-3" href="<?php echo base_url(); ?>user/user/">
                	<img id="logo" alt="user_logo" src="" width="187" height="71" border="0">
		<? }?>
                </a>

  
	
  <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#main_menu" aria-controls="main_menu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="navbar-collapse collapse pl-lg-2 pr-0" id="main_menu" style="">
  	
   <ul class="navbar-nav col-lg-10 col-md-12 p-0 mr-auto">

    	<?php 
			$menu_info = unserialize($this->session->userdata("menuinfo"));
			$permissioninfo = $this->session->userdata('permissioninfo');
		$allow_menu_url = $this->config->item('allow_menu_url');
			foreach($menu_info as $menu_key => $menu_values){				
				if ($menu_key == ""){continue;}
	  ?>
                
     <?php
		  if(astpp_eq0(common_model::$global_config['system_config']['opensips']) &&  $menu_key !='SIP Devices'){
			  echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';
		  }
		  if(common_model::$global_config['system_config']['opensips']== 1 &&  $menu_key != 'Opensips'){
			  echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" role="button" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.gettext($menu_key).'<span class="sr-only">(current)</span></a>';  
		  }
	?>

        <ul class="dropdown-menu animated faster fadeIn" aria-labelledby="">
          <? foreach($menu_values as $sub_menu_key => $sub_menu_values){?>
                       
                           <?  if($sub_menu_key === 0){ ?>
                            <? foreach($sub_menu_values as $sub_key => $sub_menu_lables){
								$module_url = explode('/',$sub_menu_lables['module_url']);
								
								if(isset($permissioninfo[$module_url[0]][$module_url[1]]) || $this->session->userdata('userlevel_logintype') == '-1' || $this->session->userdata('userlevel_logintype') == '0' || $this->session->userdata('userlevel_logintype') == '3' || astpp_in_array($module_url[1], $allow_menu_url)){
				if((common_model::$global_config['system_config']['paypal_status']== 1 && strtolower($sub_menu_lables["menu_label"]) =='recharge')|| (astpp_eq0(common_model::$global_config['system_config']['enterprise']) &&  $sub_menu_lables["menu_label"] =='Opensips devices')  || (astpp_eq0(common_model::$global_config['system_config']['enterprise']) &&  $sub_menu_lables["menu_label"] =='Opensips')  || (common_model::$global_config['system_config']['opensips']== 1 &&  $sub_menu_lables["menu_label"] =='Dispatcher list') || (common_model::$global_config['system_config']['opensips']== 1 &&  $sub_menu_lables["menu_label"] =='Opensips devices')  || (common_model::$global_config['system_config']['opensips']== 1 &&  $sub_menu_lables["menu_label"] =='Opensips') || (common_model::$global_config['system_config']['enterprise']== 1 && astpp_eq0(common_model::$global_config['system_config']['opensips']) &&  $sub_menu_lables["menu_label"] =='SIP Devices') || (($acc_info['type'] == '3' || $acc_info['type'] == '0') && $acc_info['allow_ip_management'] == '1' && strtolower($sub_menu_lables["menu_label"]) == 'ip settings')){
								}else{?>
                                <li><a class="dropdown-item" href="<?php echo base_url().$sub_menu_lables["module_url"];?>"><?php echo gettext($sub_menu_lables["menu_label"]);?></a>
				<?}?>
				<?}} ?>
                            <?php }else{
				if(astpp_eq0(common_model::$global_config['system_config']['opensips']) && $menu_key !='System Configuration'){ ?>    
				   <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#"><span><?=$sub_menu_key;?></span></a>
				<? } if(common_model::$global_config['system_config']['opensips']==1 && $sub_menu_key!="Customers") {?>
				    <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#"><span><?=$sub_menu_key;?></span></a>
				<?php }
				if(common_model::$global_config['system_config']['opensips']==1 && $sub_menu_key=="Customers") {?>
				    <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="<?php echo base_url(); ?>accounts/customer_list/"><span><?=$sub_menu_key;?></span></a>
				<?php }
				if(($acc_info['type']==3 || astpp_eq0($acc_info['type'])) && $acc_info['allow_ip_management']== 1 && strtolower($sub_menu_lables["menu_label"]) !='ip settings'){ ?>
				    <li class="dropdown-submenu"><a class="nav-link dropdown-toggle dropdown-item" href="#"><span><?=$sub_menu_key;?></span></a>
				<? }
							?>

							<ul class="col-4 col-12 dropdown-menu">
                                <? foreach($sub_menu_values as $sub_menu_lables){ 
									$module_url = explode('/',$sub_menu_lables['module_url']);

                                    if(isset($permissioninfo[$module_url[0]][$module_url[1]]) || $this->session->userdata('userlevel_logintype') == '-1' || $this->session->userdata('userlevel_logintype') == '0' || $this->session->userdata('userlevel_logintype') == '3' || astpp_in_array($module_url[1], $allow_menu_url)){
					 if($sub_menu_lables['menu_label'] != 'Configuration'){
				  if(astpp_eq0(common_model::$global_config['system_config']['opensips']) &&  $sub_menu_lables["menu_label"] !='SIP Devices'){
					  ?>
				      <li><a class="dropdown-item" href="<?php echo base_url().$sub_menu_lables["module_url"];?>"><?php echo gettext($sub_menu_lables["menu_label"]);?></a></li>
				  <? }
				  if(common_model::$global_config['system_config']['opensips']== 1 && $sub_menu_key !='opensips'){ ?>
				      <li><a class="dropdown-item" href="<?php echo base_url().$sub_menu_lables["module_url"];?>"><?=$sub_menu_lables["menu_label"];?></a></li>
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

    <div class="col navbar-profile dropdown">
         	 
         	
				
              
               
               
              </div>  
  </div>
</nav>

<div class="btn-quick">
	<div  href="#q_menu" id="q_toggle" class="btn rounded-circle p-btn-1" data-action="rotate">	<i class="fa fa-plus fa-2x"></i></div>
  

  <div id="q_menu">
	  <ul>
	    <li><a href="#info" rel="facebox">Reseller</a></li>
	    <li><a href="#info" rel="facebox">Provider</a></li>
	    <li><a href="#info" rel="facebox">Refill Coupon</a></li>
	    <li><a href="#info" rel="facebox">Order</a></li>
	    <li><a href="#info" rel="facebox">Product</a></li>
	    <li><a href="#info" rel="facebox">Termination Rate</a></li>
	    <li><a href="#info" rel="facebox">Gateway</a></li>
	    <li><a href="#info" rel="facebox">Origination Rate</a></li>
	    <li><a href="#info" rel="facebox">Rate Group</a></li>
	    <li><a href="#info" rel="facebox">Caller Id</a></li>
	    <li><a href="#info" rel="facebox">IP Setting </a></li>
	    <li><a href="#info" rel="facebox">SIP Device</a></li>
	    <li><a href="#info" rel="facebox">Customer</a></li>
	  </ul>
  </div>

  
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
