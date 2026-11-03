<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
	$('a[rel*=facebox]').facebox();
        build_grid("user_sipdevices_grid","",<? echo $grid_fields; ?>,"");
        $("#user_sipdevices_search_btn").click(function(){
            post_request_for_search("user_sipdevices_grid","","user_sipdevices_search");
        });
        $('.checkall').click(function () {
            $('.chkRefNos').prop('checked', $(this).prop('checked'));
        });
        $("#id_reset").click(function(){
            clear_search_request("user_sipdevices_grid","");
        });
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
						  if($accountinfo['type']==1){ ?>
                          <li class="breadcrumb-item"><a href="<?= base_url() . "user/user_myprofile/"; ?>"><?php echo gettext('My Profile');?></a></li>
                          <?php }
                          else if(astpp_eq0($accountinfo['type']) || $accountinfo['type']==3){?>
							  <li class="breadcrumb-item"><a href="<?= base_url() . "user/user_myprofile/"; ?>"><?php echo gettext('My Account');?></a></li>
							<?php }
                           else{ ?>
								<li class="breadcrumb-item"><a href="#"><?php echo gettext('Configuration')?></a></li>
                          <?php }
					?>
							<li class="breadcrumb-item active">
							   <a href="<?= base_url() . "user/user_sipdevices/"; ?>"><?php echo gettext('SIP Devices')?></a>
							</li>
                        </ol>
                    </nav>
                </div>

                <div class="m-2 float-right">
                    <a class="btn btn-light btn-hight" href="<?= base_url()."user/user_myprofile/"; ?>"> <i class="fa fa-fast-backward" aria-hidden="true"></i><?php echo gettext('Back');?></a>
                </div>
                <?php if($accountinfo['type']==1) { ?>
						<div class="m-2 float-right">
					<a href="<?= base_url() . "user/user_myprofile/"; ?>"> <i class="fa fa-fast-backward" aria-hidden="true"></i> <?php echo gettext('Back');?></a>
                   </div>
                <?php }?>
            </div>     
            <div class="p-4 col-md-12">
                <div class="col-md-12 p-0">
                    <div class="float-left">
                        <a href='<?php echo base_url()."user/user_sipdevices_add/"; ?>' rel="facebox_medium" title="Add">
                            <span class="btn btn-line-warning">
                                <i class="fa fa-plus-circle fa-lg"></i><?php echo gettext('Create');?>
                            </span>
                        </a>
                    </div>
                    <div id="left_panel_delete" class="float-left mt-0 px-2" onclick="delete_multiple('/user/user_sipdevices_delete_multiple/')">
                        <span class="btn btn-line-danger">
                            <i class="fa fa-times-circle fa-lg"></i>
                            <?php echo gettext('Delete');?>
                        </span>
                    </div>
                    
                        <div  id="show_search" class= "btn btn-warning float-right"><i class="fa fa-search"></i><?php echo gettext('Search');?> </div>
                </div> 
              
                        <div class="col-12">
                                <div class="portlet-content my-4"  id="search_bar" style="display:none">
                                        <?php echo $form_search; ?>
                                </div>
                        </div>
                    <div class="col-12 px-4">
                        <div class="card px-4 pb-4">
                                <table id="user_sipdevices_grid" align="left" style="display:none;"></table>
                        </div>
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
