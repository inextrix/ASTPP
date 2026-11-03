<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" language="javascript">
 $(document).ready(function() {
 $(".change_pass").click(function(){
            $.ajax({type:'POST',
                url: "<?= base_url()?>user/user_generate_password/",
                success: function(response) {
                    $('#password').val(response.trim());
                }
            });
        })
        $(".change_number").click(function(){
            $.ajax({type:'POST',
                url: "<?= base_url()?>user/user_generate_number/"+10,
                success: function(response) {
                    var data=response.replace('-',' ');
                    $('#number').val(data.trim());
                }
            });
        })
         });
</script>
<script type="text/javascript">
  $(document).ready(function(){
      $(".breadcrumb li a").removeAttr("data-ripple",""); 
  });
</script>
<? endblock() ?>

<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<? startblock('content') ?>        
<div id="main-wrapper">
    <div id="content" class="container-fluid">   
        <div class="row"> 
            <div class="col-md-12 color-three border_box"> 
                <div class="float-left m-2 lh19">
                    <nav aria-label="breadcrumb">
						<ol class="breadcrumb m-0 p-0">
							<?php $accountinfo=$this->session->userdata('accountinfo');
							if(astpp_eq0($accountinfo['type']) || $accountinfo['type']==3){ ?>
								<li class="breadcrumb-item"><a href=<?= base_url() . "user/user_myprofile/"; ?>><?php echo gettext('My Account')?></a></li>
							<?php } ?>	
							<li class="breadcrumb-item active" aria-current="page"><a href="<?= base_url()."user/user_myprofile/"; ?>"> <?php echo gettext('My Profile'); ?> </a></li>
						</ol>	
                    </nav>
                </div>
                	<?php
//HP: PBX_ADDON
						$addon_status = $this->db_model->countQuery ( "*", "addons", array('package_name' => 'pbx') );
						if($addon_status == 1 ){
							$val = "";
							$domains = $this->db_model->getSelect ( "domain", "domains", array('accountid'=>$accountinfo['id']) );
							$domain_name = (array) $domains->first_row();
							if(isset($domain_name['domain']) && $domain_name['domain'] != ""){
								$val=$domain_name['domain'];
							}
							if(astpp_eq0($accountinfo['type'])){
					?>
							<div class="col-3 float-right input-group m-2 pr-0">
								<label class="border border-light col m-0 p-1 rounded-left text-center text-light text-truncate"><?php echo $val; ?></label>
							</div>

					<?php }} ?>
            </div>
            <div class="p-4 col-md-12">
                <div class="slice color-three float-left content_border">
                    <?php echo $form; ?>
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
<? end_extend() ?>  
<script type="text/javascript">
  $(document).ready(function(){
      $('.page-wrap').addClass('addon_wrap');
  });
</script>
