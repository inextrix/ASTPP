<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN" "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html xml:lang="en" xmlns:fb="http://ogp.me/ns/fb#"
	xmlns="http://www.w3.org/1999/xhtml" lang="en">
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
        echo gettext("Log In")." | ";echo $invoiceconf['website_title'];
    } else {
        echo gettext("Log In")." | ".gettext("ASTPP - A Smart TelePhony Platform");
    }
    ?>
    </title>
    <?php  $user_favicon = $this->session->userdata('user_favicon'); ?>

    <link rel="icon"
	href="<? echo base_url(); ?>assets/images/<? echo $user_favicon ?>" />
<link href="<?= base_url() ?>assets/css/bootstrap.min.css"
	rel="stylesheet">
<link
	href="<?= base_url() ?>assets/fonts/font-awesome-4.7.0/css/font-awesome.css"
	rel="stylesheet">
<link href="<?= base_url() ?>assets/css/global-style.css"
	rel="stylesheet" type="text/css">
<link href="<?= base_url() ?>assets/css/custome_index.css"
	rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/style.css"
	rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/com-style.css"
rel="stylesheet" type="text/css">	

<script src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.js"></script>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/status/dist/js/bootstrap-select.js"></script>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
<!-- IE -->
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/respond.js"></script>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/respond.src.js"></script>

<script src="<?php echo base_url(); ?>assets/js/custome_index.js"></script>

<link
	href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i"
	rel="stylesheet">

<noscript>
	<div id="noscript-warning"><?php echo gettext("ASTPP work best with JavaScript enabled"); ?></div>
</noscript>


<script type="text/javascript">
      $(document).ready(function () {
        setTimeout(function () {
        var $Input = $('input');
        $Input.closest('.form-group').addClass('control-highlight');
        }, 200);
      });

     function FocusOnInput() {
        document.getElementById("username").focus();
       }

         $('document').ready(function() { 
             $("#login_form").validate({
                 rules: {
           
                     username: {
                         required: true,
                     },
                     password: {
                         required: true,
                     },
                },
                 messages: {
                     username: { 
                         required: '<span class="text-danger"><?php echo gettext("Username is Required"); ?></span>',
                     },
                     password: {
                        required: '<span class="text-danger"><?php echo gettext("Password is Required"); ?></span>',
                     },
                 },
                 errorClass: "error_label",
                 submitHandler: function(form) {
                     form.submit();
			$(':input[type="submit"]').prop('disabled', true);
			$('input[type="text"]').keyup(function() {
			if($(this).val() != '') {
			$(':input[type="submit"]').prop('disabled', false);
			}
			});
                 }
             });  
         });
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
<body style="background: url(<?= base_url() ?>assets/images/login_bg.jpg);background-size: cover;background-attachment: fixed;" onload="FocusOnInput()">
	<div class="col-md-12 m-auto form_card">
		<form role="form" id="login_form" class="card p-4 col-12 "
			name="login_form" action="<?php echo base_url(); ?>login/login"
			method="POST" novalidate
			style="background: rgba(255, 255, 255, 0.8);">
          <?php if (isset($astpp_notification)){ ?>
            <div class="alert alert-danger"
				style="font-size: 14px; line-height: 1.5;">
              <?php    echo $astpp_notification; ?>
            </div>
              <?php
        } else {
            echo " ";
        }
        ?>

          <div class="">
				<h2 class="text-center">
          
              <?php
            if (isset($this->session->userdata['user_logo']) && $this->session->userdata['user_logo'] != "") {
                $logo = $this->session->userdata['user_logo'];
            } else {
                $logo = 'logo.png';
            }

            if ($this->session->userdata('userlevel_logintype') != '0') {
                ?>
                      <img class="mb-4 img-fluid" id="logo" alt="login"
						src="<?php echo base_url(); ?>upload/<?php echo $logo;?>">
              <? } else {?> 
                      <img class="mb-4 img-fluid" id="logo" alt='login'
						src="<?php echo base_url(); ?>upload/<?php echo $logo;?>">
              <? }?>
              <div class="clear"></div>

				</h2>
			</div>

			<div class="card px-4 py-5">

				<div class="form-group">
					<input type="text" id="username" class="form-control error_login"
						name="username" value="" autocomplete="off" required> <label
						class="control-label" for="username"><?php echo gettext('Account No OR Email')?></label>
				</div>

				<div class="form-group">
					<input type="password" class="form-control error_login"
						id="password" name="password" value="" autocomplete="off" required>
					<label class="control-label" for="password"><?php echo gettext('Password')?></label>
				</div>

				<button type="submit" id="save_button" name="save_button"
					class="btn btn-block text-uppercase border_box"><?php echo gettext('Sign in')?></button>

			</div>
			<div class="col-12 mt-4">
				<p class="text-center">
					<a class="forgot_pass" href="forgotpassword"><?php echo gettext('Forgot Password?')?></a>
				</p>
				<p class="text-center px-4 m-0">
              <?php
            if (astpp_eq0(Common_model::$global_config['system_config']['enable_signup'])) {
                ?>
              <a class="btn btn-outline-primary btn-block btn_index"
						href="/signup/"><?php echo gettext('Signup now!')?></a> 
              <?php
            }
            ?>
            </p>

			</div>

		</form>

	</div>
</body>
</html>