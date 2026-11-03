<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript">
function form_submit(){
    var email = document.forms["reseller_form"]["notify_email"].value;
    if (email == null || email == "") {
	  $("#email_err").html('<?php echo gettext("Please enter your email"); ?>');    
        return false;
    }else{
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/; 
	var check = regex.test(email);
	if(check == true ){
	  $("#email_err").html('');    
	}else{
	  $("#email_err").html('<?php echo gettext("Please enter proper email"); ?>');    
          return false;
	}
    }
}
</script>
<script type="text/javascript">
  $(document).ready(function(){
      $(".breadcrumb li a").removeAttr("data-ripple","");
      
  });
</script>
<?php endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<?php startblock('content') ?>

<div id="main-wrapper">
    <div id="content" class="container-fluid">   
        <div class="row"> 
            <div class="col-md-12 color-three border_box"> 
                <div class="float-left m-2 lh19">
                     <nav aria-label="breadcrumb">
						<ol class="breadcrumb m-0 p-0">
                          <?php $accountinfo=$this->session->userdata('accountinfo');
						  if($accountinfo['type']==1){ ?>
                          <li class="breadcrumb-item"><a href="<?= base_url() . "user/user_myprofile/"; ?>"><?php echo gettext('My Profile')?></a></li>
                          <?php } 
                          else if(astpp_eq0($accountinfo['type']) || $accountinfo['type']==3){ ?>
								<li class="breadcrumb-item"><a href=<?= base_url() . "user/user_myprofile/"; ?>><?php echo gettext('My Account')?></a></li>
							 <?php } 
                          else{ ?>
								<li class="breadcrumb-item"><a href="#"><?php echo gettext('Configuration')?></a></li>
                          <?php } ?>
							<li class="breadcrumb-item active">
                             <a href="<?= base_url() . "user/user_alert_threshold/"; ?>"><?php echo gettext('Alert Threshold')?></a>
                          </li>
                    </ol>
                    </nav>
                </div>

                <div class="m-2 float-right">
					<a class="btn btn-light btn-hight" href="<?= base_url()."user/user_myprofile/"; ?>"> <i class="fa fa-fast-backward" aria-hidden="true"></i> <?php echo gettext("Back"); ?></a>
                </div>
                  

	     </div>
            <div class="p-4 col-md-12">
                <div class="col-md-12">
                    <?php 
					if(isset($form)){
						echo $form;
					}	 ?>
                    <?php if (isset($validation_errors) && $validation_errors != '') { ?>
                        <script>
                            var ERR_STR = '<?php echo $validation_errors; ?>';
                            print_error(ERR_STR);
                        </script>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>
<script type="text/javascript">
  $(document).ready(function(){
      $('.page-wrap').addClass('addon_wrap');
  });
</script>
