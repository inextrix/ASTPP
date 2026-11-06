<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
  
<title>
	<?php       
        $this->db->where('domain',$_SERVER['HTTP_HOST']);
        $this->db->select('*');
        $this->db->order_by('accountid', 'desc');
        $this->db->limit(1);
        $invoiceconf = $this->db->get('invoice_conf');
        $invoiceconf = (array)$invoiceconf->first_row();
				if (isset($invoiceconf['website_title']) && $invoiceconf['website_title'] != '') {    
					echo gettext("OTP Verification")." | "; echo $invoiceconf['website_title']; 
				} else {
					echo gettext("OTP Verification")." | ".gettext("ASTPP - A Smart TelePhony Platform");
				}
	?>

</title>
<?php { 
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
 } ?>
<?php if(!empty($user_favicon['favicon'])) {  ?>
        <link rel="icon" href="<? echo base_url(); ?>upload/<? echo $user_favicon['favicon'] ?>"/>
<?php } else { ?>
    <link rel="icon" href="<? echo base_url(); ?>assets/images/favicon.ico"/>
<?php } ?>
<link href="<?php echo base_url(); ?>/assets/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= base_url() ?>assets/fonts/font-awesome-4.7.0/css/font-awesome.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>/assets/css/global-style.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-select.css"/>
<link href="<?php echo base_url(); ?>assets/css/global-style.css" rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/custome_index.css" rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/com-style.css" rel="stylesheet" type="text/css">

<link href="<?php echo base_url(); ?>assets/fonts/webfonts/fonts.css" rel="stylesheet"> 


<script src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/status/dist/js/bootstrap-select.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/module_js/generate_grid.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/fileinput.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/tabcontent.js"></script>
<!-- IE -->
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/respond.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/respond.src.js"></script>
<!-- -->    
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/custome_index.js"></script>
<noscript>	
<div id="noscript-warning">
<?php echo gettext("ASTPP work best with JavaScript enabled"); ?>
</div>
</noscript>

</script>

<style>
	html,
	body {
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

<script type="text/javascript">
     function FocusOnInput() {
        document.getElementById("otp_number").focus();
       }
  function validateForm() {
		if (document.forms["otp_form"]["otp_number"].value == "") {
			document.getElementById("otp_number_error").innerHTML="<?php echo gettext('OTP Number is Required'); ?>";
			return false;
		}else{
			var account_id=document.forms["otp_form"]["account_id"].value;
			var otp_number=document.forms["otp_form"]["otp_number"].value;
			var number=document.forms["otp_form"]["number"].value;
			var creation_date=document.forms["otp_form"]["creation_date"].value;
			var email=document.forms["otp_form"]["email"].value;
				$(document).ready(function(){
					$.ajax({
					type: "POST",
					url:"<?= base_url() ?>signup/check_otp/",
					data: {"account_id":account_id,"otp_number":otp_number,"number":number,"email":email,"creation_date":creation_date},
					success:function(response) { 
						response = $.trim(response);  
						if(response == "success"){ 
							 $("#toast-container").css("display","block");
						   	 $(".toast-message").html("<?php echo gettext('Your account created successfully and account details sent to your registered email address'); ?>");
						   	 $('.toast-top-right').delay(900000).fadeOut(40000).fadeIn(2000);
							 $("#submit_otp").attr("disabled", true);
							 $('.resend_otp').unbind('click');
							 setTimeout(function(){window.location.href = "<?= base_url() ?>"} , 5000);   
						}else if(response == "forgot"){
							 $("#toast-container").css("display","block");
						   	 $(".toast-message").html("<?php echo gettext('Your password change successfully and new password sent to your registered email address'); ?>");
						   	 $('.toast-top-right').delay(900000).fadeOut(40000).fadeIn(2000);
							 $("#submit_otp").attr("disabled", true);
							 $('.resend_otp').unbind('click');
							 setTimeout(function(){window.location.href = "<?= base_url() ?>"} , 5000); 
						}else{
							$("#otp_number_error").html("<?php echo gettext('OTP Number is Wrong Please try again Or OTP expire Please Resend'); ?>"); 
						}
					}
				}); 
            }); 
		}
  }
  
  function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }
  
</script>

<script>
$(document).ready(function(){
   $(".resend_otp").click(function(){
				$(".resend_otpdiv").hide();
				startTimer();
				$(".timer").show();
				var account_id=$("#account_id").val();
				var otp_number=$("#otp_number").val();
				var number=$("#number").val();
				var email=$("#email").val();
				var creation_date=$("#creation_date").val();
				$.ajax({
					type: "POST",
					url:"<?= base_url() ?>signup/resend_otp",
					data:{"account_id":account_id,"otp_number":otp_number,"number":number,"email":email,"creation_date":creation_date},
					success:function(response) {
					}
				}); 
				
   });    
  });
  
  function startTimer(){
      var counter = 15;
      $(".resend_otpdiv").hide();
      setInterval(function() {
        counter--;
      	counter = (counter < 10) ? '0' + counter : counter;
        if (counter >= 0) {
          span = document.getElementById("count");
          span.innerHTML = counter;
        }
        if (counter == "00") {
            $(".resend_otpdiv").show();
            $(".timer").hide();
            clearInterval(counter);            
        }
      }, 1000);
      
      
    }
   
</script>
<script>
$(document).ready(function() {
    $("#submit_otp").click(function(){
	$('#submit_otp').prop('disabled', true);
	$('input[type="text"]').keyup(function() {
	if($(this).val() != '') {
	$('#submit_otp').prop('disabled', false);
	}
	});
    });
 });
</script>  
</head>
<? extend('master.php') ?>
<? startblock('extra_head') ?>
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
<body style="background: url(<?= base_url() ?>assets/images/login_bg.jpg);background-size: cover;background-attachment: fixed;" onload="FocusOnInput()">
            <div class="col-md-12 m-auto form_card">
				<form class="card p-4 col-12"  method="post" accept-charset="utf-8" id="otp_form" name="otp_form" style="background:rgba(255,255,255,0.8);">
						<h2 class="text-center">	
							<?php
								if(isset($this->session->userdata['user_logo']) && $this->session->userdata['user_logo'] != ""){
										$logo = $this->session->userdata['user_logo'];
								}else{
								     	$logo = 'logo.png';
								}
														
								if ($this->session->userdata('userlevel_logintype') != '0') {?>
										
										<a class="logo_title" href="<?php echo base_url(); ?>"><img class="mb-2 img-fluid" id="logo" alt="dashboard" src="<?php echo base_url(); ?>upload/<?php echo$logo;?>"></a>
								<? } else {?> 
										
										<a class="logo_title" href="<?php echo base_url(); ?>"><img class="mb-2 img-fluid" id="logo" title='ASTPP - Open Source Voip Billing Solution' alt='ASTPP - Open Source Voip Billing Solution' src="<?php echo base_url(); ?>upload/<?php echo$logo;?>"></a>
								<? }?>
								
						</h2>
					<div class="card p-4">
						<div class="text-success" style="font-size:11px;font-weight: normal;"><?php echo gettext('OTP has been sent on your email or Telephone number')?>
						</div>
								<input type="hidden" name="account_id" value="<?php echo isset($account_id)?$account_id:""; ?>" id="account_id" size="15" maxlength="40" class="form-control"/>

<input type="hidden" name="creation_date" value="<?php echo isset($creation_date)?$creation_date:""; ?>" id="creation_date" size="15" maxlength="40" class="form-control"/>
<input type="hidden" name="email" value="<?php echo isset($email)?$email:""; ?>" id="email" size="15" maxlength="40" class="form-control"/>
						
							<div class="form-group mt-4">
								<input type="text" name="number" readonly value="<?php echo isset($number)?$number:""; ?>" id="number" size="15" maxlength="40" class="form-control"/>
								<label class="control-label" for="number"><?php echo gettext('User Name')?></label>
							</div>
							
							<div class="form-group">
								<input type="text" name="otp_number" value="" id="otp_number" size="15" maxlength="20" class="form-control m-0" onkeypress="return isNumberKey(event)"/>
								<label class="error_label">
									<span id="otp_number_error" class="text-danger"></span>
								</label>
								<label class="control-label" for="otp_number"><?php echo gettext('OTP')?></label>
							</div>
							
							<div class="col-md-12 p-0">
								<button name="action" type="button" id="submit_otp" value="Signup" class="btn btn-block text-uppercase border_box" onclick="validateForm();" ><?php echo gettext('Submit')?></button>
							</div>
							
									<div class="col-md-12 text-center">
													<label style="display:none;" class="mt-4 mb-0 text-center timer"><?php echo gettext('OTP Sent time Out')?> <span class="text-primary">00:<span id="count">15</span></span></label>
									</div>
									<div class="gray_lohin col-md-12 mt-4 resend_otpdiv text-center">
												<label class="resend_otp my-2">
														<?php echo gettext('Not Get OTP ?'); ?> <a class="btn text-primary"><?php echo gettext('Resend OTP')?></a>
												</label>
												
									</div>		
						
					</div>
			<p class="text-danger my-2" style="font-size:11px;font-weight: normal;"><b><?php echo gettext("Note:");?></b> <?php echo gettext("Please make sure do not refresh the page or don't go back"); ?></p>			
				</form>	
			</div>

</body>