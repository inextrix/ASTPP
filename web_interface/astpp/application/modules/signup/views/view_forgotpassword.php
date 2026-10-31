
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

			<title>
	<?php
$this->db->where('domain', $_SERVER['HTTP_HOST']);
$this->db->select('*');
$this->db->order_by('accountid', 'desc');
$this->db->limit(1);
$invoiceconf = $this->db->get('invoice_conf');
$invoiceconf = (array) $invoiceconf->first_row();
if (isset($invoiceconf['website_title']) && $invoiceconf['website_title'] != '') {    
	echo gettext("Forgot Password")." |"; echo $invoiceconf['website_title']; 
} else {
	echo gettext("Forgot Password")." | ".gettext("ASTPP - A Smart TelePhony Platform");
}
?>
</title>

<?php

{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
        $domain = "https://" . $_SERVER["HTTP_HOST"] . "/";
    } else {
        $domain = "http://" . $_SERVER["HTTP_HOST"] . "/";
    }
    $http_host = $_SERVER["HTTP_HOST"];
    $this->db->select('favicon');
    $this->db->where("domain LIKE '%$domain%'");
    $this->db->or_where("domain LIKE '%$http_host%'");
    $user_favicon = (array) $this->db->get_where("invoice_conf")->first_row();
}
?>
<?php if(!empty($user_favicon['favicon'])) {  ?>
        <link rel="icon"
				href="<? echo base_url(); ?>upload/<? echo $user_favicon['favicon'] ?>" />
<?php } else { ?>
        <link rel="icon"
				href="<? echo base_url(); ?>assets/images/favicon.ico" />
<?php } ?>
<link href="<?php echo base_url(); ?>/assets/css/bootstrap.min.css"
				rel="stylesheet">
				<link
					href="<?= base_url() ?>assets/fonts/font-awesome-4.7.0/css/font-awesome.css"
					rel="stylesheet">
					<link href="<?php echo base_url(); ?>assets/css/global-style.css"
						rel="stylesheet" type="text/css">
						<link href="<?php echo base_url(); ?>assets/css/custome_index.css"
							rel="stylesheet" type="text/css">
							<link href="<?php echo base_url(); ?>assets/css/style.css"
								rel="stylesheet" type="text/css">
							<link href="<?php echo base_url(); ?>assets/css/com-style.css"
							rel="stylesheet" type="text/css">

								<link
									href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i"
									rel="stylesheet">

									<script
										src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/status/dist/js/bootstrap-select.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/module_js/generate_grid.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>


									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/respond.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/respond.src.js"></script>

									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/custome_index.js"></script>

									<noscript>
										<div id="noscript-warning"><?php echo gettext("ASTPP work best with JavaScript enabled"); ?></div>
									</noscript>

									</script>

									<style>
html, body {
	height: 100%;
}

body {
	display: -ms-flexbox;
	display: flex;
	-ms-flex-align: center;
	align-items: center;
	padding-top: 40px;
	padding-bottom: 40px;
	background-color: #f5f5f5;
}
</style>

</head>
<? extend('master.php') ?>
<? startblock('extra_head') ?>
<body style="background: url(<?= base_url() ?>assets/images/login_bg.jpg);background-size: cover;background-attachment: fixed;" onload="FocusOnInput()">


	<div class="container">
		<div class="row">
			<div class="col-md-4 m-auto form_card">
                        <?php if (isset($astpp_notification)){ ?>
                        <div class="col-md-12">
					<div
						class="container alert alert-danger alert-dismissible mt-5 fade show">
						<strong><?php echo gettext('Login unsuccessful')?></strong> <?php echo gettext('Login unsuccessful. Please make sure you entered the correct username and password, and that your account is active.')?>
		                        <button type="button" class="close"
							data-dismiss="alert" aria-label="Close" data-ripple=" ">
							<span aria-hidden="true">×</span>
						</button>
					</div>
				</div>
						<?php

} else {
                            echo "&nbsp;";
                        }
                        ?>
                </div>
		</div>

		<div class="row">
			<div class="col-md-6 mx-auto form_card">

				<form class="card p-4 col-12"
					action="<?php echo base_url(); ?>confirmpassword/"
					class="form-light col-md-12 no-padding"
					onsubmit="return validateemail();" method="post"
					accept-charset="utf-8" id="customer_form" name="customer_form"
					style="background: rgba(255, 255, 255, 0.8);">
					<div>
						<h2 class="text-center">
											  <?php
            if (isset($this->session->userdata['user_logo']) && $this->session->userdata['user_logo'] != "") {
                $logo = $this->session->userdata['user_logo'];
            } else {
                $logo = 'logo.png';
            }

            if ($this->session->userdata('userlevel_logintype') != '0') {
                ?>
											
													
														<a class="logo_title" href="<?php echo base_url(); ?>"><img
								class="mb-2 img-fluid" alt="dashboard" id="logo" alt="login"
								src="<?php echo base_url(); ?>upload/<?php echo $logo;?>"></a>
												<? } else {?> 
														
														<a class="logo_title" href="<?php echo base_url(); ?>"><img
								class="mb-2 img-fluid" id="logo"
								title='ASTPP - Open Source Voip Billing Solution'
								alt='ASTPP - Open Source Voip Billing Solution'
								src="<?php echo base_url(); ?>upload/<?php echo $logo;?>"></a>
												<? }?>
											
										</h2>
					</div>





					<div class="card px-4 py-5">
						<div class="form-group">
							<input type="text" class="form-control m-0" id="number"
								name="number" placeholder=""
								value="<?php if (isset($value['number'])) echo htmlspecialchars((string) $value['number'], ENT_QUOTES, 'UTF-8'); ?>">
											
												<?php if (isset($error['number'])) echo $error['number']; ?>              
												<label class="error_label"> <span id="n_number"
									class="text-danger"> </span>
							</label> <label for="number" class="control-label"
								style="text-align: left;"><?php echo gettext('Account Number')?></label>
						
						</div>

						<div class="form-group">
							<input type="text" class="form-control m-0" id="email"
								name="email" placeholder=""
								value="<?php if (isset($value['email'])) echo htmlspecialchars((string) $value['email'], ENT_QUOTES, 'UTF-8'); ?>">
											
												<?php if (isset($error['email'])) echo $error['email']; ?>              
												<label class="error_label"> <span id="e_name"
									class="text-danger"> </span>
							</label> <label for="email" class="control-label"
								style="text-align: left;"><?php echo gettext('Email')?></label>
						
						</div>

						<button type="submit" id="submit"
							class="btn btn-block text-uppercase border_box"><?php echo gettext('Forgot Password')?></button>
					</div>
					<div class="col-12 mt-4">
						<p class="text-center px-4 m-0">
							<a class="btn btn-outline-primary btn-block btn_index"
								href="<?php echo base_url(); ?>"><?php echo gettext('Login')?></a>
						</p>

					</div>

				</form>
			</div>
		</div>
	</div>
	<script type="text/javascript"
		src="<?php echo base_url(); ?>assets/js/custome_index.js"></script>
	<script type="text/javascript">

     function FocusOnInput() {
        document.getElementById("number").focus();
       }

		function validateemail()  
		{
			var ename = document.customer_form.email; 
			var nnumber = document.customer_form.number;
			if(emailLetter(ename))  
			{ 
				return true;
			}

			
			if(numberletter(nnumber))  
			{ 
				return true;
			}
			return false;
		}
									
		function emailLetter(ename)  
		{   
			
			if((email.value) == "")
			{
				document.getElementById("e_name").innerHTML = "<?php echo gettext('The Email field is Required'); ?>";
				return false;   
			}else{
				return true;
			}
		
		} 

		function numberletter(number)  
		{   
			if((number.value) == "")
			{
				document.getElementById("n_number").innerHTML = "<?php echo gettext('The Account Number field is Required'); ?>";
				return false;   
			}else{
				return true;
			}
		
		}  
</script>