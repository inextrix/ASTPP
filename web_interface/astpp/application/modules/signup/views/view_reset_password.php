<?php
/*
 * Second step of "forgot password": code from the e-mail and the new password. The page and its
 * texts are the same whether an account exists or not.
 * Variables: $number, $email (as entered), $sent (code requested), $error, $done (password set).
 */
$h = function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$number = isset($number) ? $number : '';
$email = isset($email) ? $email : '';
$this->db->where('domain', $_SERVER['HTTP_HOST']);
$this->db->select('website_title');
$this->db->order_by('accountid', 'desc');
$this->db->limit(1);
$invoiceconf = (array) $this->db->get('invoice_conf')->first_row();
$site_title = (isset($invoiceconf['website_title']) && $invoiceconf['website_title'] != '') ? $invoiceconf['website_title'] : gettext("ASTPP - A Smart TelePhony Platform");
$logo = $this->session->userdata('user_logo');
$logo = ($logo != '') ? $logo : 'logo.png';
$expire = common_model::$global_config['system_config']['otp_expire'];
$expire = ($expire > 0) ? (int) $expire : 30;
$this->load->library('astpp/password_policy'); // rule hint and minimum length
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $h(gettext('Forgot Password') . ' | ' . $site_title); ?></title>
<link rel="icon" href="<?php echo base_url(); ?>assets/images/favicon.ico" />
<link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>assets/fonts/font-awesome-4.7.0/css/font-awesome.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>assets/css/global-style.css" rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/custome_index.css" rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/com-style.css" rel="stylesheet" type="text/css">
<style>
html, body { height: 100%; }
body {
	display: flex;
	align-items: center;
	padding-top: 40px;
	padding-bottom: 40px;
	background-color: #f5f5f5;
}
/* com-style.css makes every .border_box white with white text; same button colours as the forgot password form (#customer_form) */
#reset_form .border_box { background: rgb(46, 37, 112) !important; color: #fff !important; }
#reset_form .border_box:hover { background: rgb(123, 115, 176) !important; border-color: rgb(123, 115, 176) !important; }
.form_card .alert + a.border_box { background: rgb(46, 37, 112) !important; color: #fff !important; }
</style>
</head>
<body style="background: url(<?php echo base_url(); ?>assets/images/login_bg.jpg);background-size: cover;background-attachment: fixed;">
	<div class="container">
		<div class="row">
			<div class="col-md-6 mx-auto form_card">
				<div class="card p-4 col-12" style="background: rgba(255, 255, 255, 0.8);">
					<h2 class="text-center">
						<a class="logo_title" href="<?php echo base_url(); ?>"><img class="mb-2 img-fluid" id="logo" alt="<?php echo $h($site_title); ?>" src="<?php echo base_url(); ?>upload/<?php echo $h($logo); ?>"></a>
					</h2>
<?php if (! empty($done)) { ?>
					<div class="card px-4 py-5">
						<div class="alert alert-success" id="reset_done"><?php echo gettext('Your password has been changed. You can log in with the new password now.'); ?></div>
						<a class="btn btn-block text-uppercase border_box" href="<?php echo base_url(); ?>"><?php echo gettext('Login'); ?></a>
					</div>
<?php } else { ?>
					<form class="card px-4 py-4" action="<?php echo base_url(); ?>signup/reset_password/" method="post" accept-charset="utf-8" id="reset_form" name="reset_form" autocomplete="off">
<?php if (! empty($sent)) { ?>
						<div class="alert alert-info" id="reset_sent"><?php echo $h(sprintf(gettext('If the account number and e-mail address belong to an active account, a code has been sent to this e-mail address. The code is valid for %d minutes.'), $expire)); ?></div>
<?php } ?>
<?php if (! empty($error)) { ?>
						<div class="alert alert-danger" id="reset_error"><?php echo $h($error); ?></div>
<?php } ?>
						<input type="hidden" name="number" value="<?php echo $h($number); ?>">
						<input type="hidden" name="email" value="<?php echo $h($email); ?>">
						<div class="form-group">
							<label class="control-label" for="code"><?php echo gettext('Code from the e-mail'); ?></label>
							<input type="text" class="form-control m-0" id="code" name="code" value="" maxlength="20" inputmode="numeric" autocomplete="one-time-code" required autofocus>
						</div>
						<div class="form-group">
							<label class="control-label" for="password"><?php echo gettext('New Password'); ?></label>
							<input type="password" class="form-control m-0" id="password" name="password" value="" minlength="<?php echo Password_policy::MIN_LENGTH; ?>" maxlength="100" autocomplete="new-password" aria-describedby="password_hint" required>
							<small class="form-text text-muted" id="password_hint"><?php echo $h($this->password_policy->hint()); ?></small>
						</div>
						<div class="form-group">
							<label class="control-label" for="confirm_password"><?php echo gettext('Confirm Password'); ?></label>
							<input type="password" class="form-control m-0" id="confirm_password" name="confirm_password" value="" minlength="<?php echo Password_policy::MIN_LENGTH; ?>" maxlength="100" autocomplete="new-password" required>
						</div>
						<button type="submit" id="submit" class="btn btn-block text-uppercase border_box"><?php echo gettext('Set new password'); ?></button>
					</form>
					<form class="mt-3 text-center" action="<?php echo base_url(); ?>confirmpassword/" method="post" accept-charset="utf-8" id="resend_form" name="resend_form">
						<input type="hidden" name="number" value="<?php echo $h($number); ?>">
						<input type="hidden" name="email" value="<?php echo $h($email); ?>">
						<button type="submit" class="btn btn-link"><?php echo gettext('Send a new code'); ?></button>
					</form>
					<div class="col-12 mt-2">
						<a class="btn btn-outline-primary btn-block btn_index" href="<?php echo base_url(); ?>"><?php echo gettext('Login'); ?></a>
					</div>
<?php } ?>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
