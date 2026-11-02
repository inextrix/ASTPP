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
	echo gettext("Signup")." | "; echo $invoiceconf['website_title']; 
} else {
	echo gettext("Signup")." | ".gettext("ASTPP - A Smart TelePhony Platform");
}
?>
</title>
			<link rel="icon"
				href="<?php echo base_url(); ?>/assets/images/favicon.ico">
				<link href="<?php echo base_url(); ?>/assets/css/bootstrap.min.css"
					rel="stylesheet">
					<link
						href="<?php echo base_url(); ?>/assets/fonts/font-awesome-4.2.0/css/font-awesome.css"
						rel="stylesheet">
						<link rel="stylesheet"
							href="<?php echo base_url(); ?>assets/css/bootstrap-select.css" />
						<link href="<?php echo base_url(); ?>assets/css/global-style.css"
							rel="stylesheet" type="text/css">
							<link
								href="<?php echo base_url(); ?>assets/css/custome_index.css"
								rel="stylesheet" type="text/css">
							<link href="<?php echo base_url(); ?>assets/css/com-style.css"
							rel="stylesheet" type="text/css">

								<link
									href="<?php echo base_url(); ?>assets/fonts/webfonts/fonts.css"
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
										src="<?php echo base_url(); ?>assets/js/fileinput.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/tabcontent.js"></script>

									<script type="text/javascript"
										src="<?php echo base_url(); ?>/assets/js/respond.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>/assets/js/respond.src.js"></script>

									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/custome_index.js"></script>

									<noscript>
										<div id="noscript-warning"><?php echo gettext("ASTPP work best with JavaScript enabled"); ?></div>
									</noscript>

									</script>

</head>
<? extend('master.php') ?>
<? startblock('extra_head') ?>
<body style="background: url(<?= base_url() ?>assets/images/login_bg.png);background-size: cover;">
	<div class="container">
		<div class="col-md-6 mx-auto">

			<div class="card p-4 mt-5 row">
				<div class="col-md-12">		
						<?php
    if (isset($this->session->userdata['user_logo']) && $this->session->userdata['user_logo'] != "") {
        $logo = $this->session->userdata['user_logo'];
    } else {
        $logo = 'logo.png';
    }

    if ($this->session->userdata('userlevel_logintype') != '0') {
        ?>
							<a class="col-md-9 logo_title" href="<?php echo base_url(); ?>">
						<img class="my-2 img-fluid" id="logo" alt="dashboard"
						src="<?php echo base_url(); ?>upload/<?php echo$logo;?>">
							<? } else {?> 
							<a class="col-md-9 logo_title" style="padding: 0px 0px 20px 0px"
							href="<?php echo base_url(); ?>"> <img class="my-2 img-fluid"
								id="logo" title='ASTPP - Open Source Voip Billing Solution'
								alt='ASTPP - Open Source Voip Billing Solution'
								src="<?php echo base_url(); ?>upload/<?php echo$logo;?>">
							<? }?>	
							</a>

							<div class="col-md-3 float-right mt-4">
								<a href="<?php echo base_url(); ?>"><input type="submit"
									value="Login" name="Login"
									class="btn btn-success col-md-12 margin-t-10"></a>
							</div>
				
				</div>

				<form class="card p-4 mt-5"
					action="<?php echo base_url(); ?>signup/successpassword/"
					method="post" accept-charset="utf-8" id="customer_form"
					name="customer_form">
					<div class="row">
						<div class="col-md-12" style="color: #232222;">
							<input type="hidden" name="email" value=<?php echo $email; ?>> <br />	<?php echo gettext('Sorry, we cannot process for singup at this time.')?><br><?php echo gettext('Please contact administrator for more information')?><br><br>
						
						</div>
					</div>

				</form>
			</div>
		</div>
	</div>
</body>
</html>
<script type="text/javascript">
$("#country_id").val(<?=$country_id?>);
$("#timezone_id").val(<?=$timezone_id?>);
$("#currency_id").val(<?=$currency_id?>);
</script>