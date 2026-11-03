<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<?php endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<?php startblock('content') ?>      
<script>
function install_addon(){
	$(".overlay").show();
}
</script>
<?php
$addon_type = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1);
?>
<div id="main-wrapper">
	<section class="slice color-three pb-4">
		<div class="w-section inverse no-padding">
			<div class="col-md-12 float-left addon_upload color-three border_box">
				<div class="my-2 float-right">
				<?php
    // if($addon_type != "Thirdparty" && !empty($module_array)){ ?>
					<!-- <button class="btn btn-light">Update</button> -->
				<?//}?>
			  </div>
			</div>

			<div class="container-fluid content-row">
				<div class="addons">

					<div class="row no-gutters">
						<!-- <div class="card-columns mt-4"> -->

						  <?
        foreach ($module_array as $module => $elements) {
            $update_flag = 'false';
            $version_error = 'false';
            $package_name = $elements->tech_name;
            $addon_name = $elements->name;
            $new_version = $elements->version;
            $license = $elements->license;
            $old_version = "0.0.0";
            if (isset($installed_addons) && ! empty($installed_addons)) {
                foreach ($installed_addons as $key => $val) {
                    if ($key == $package_name) {
                        foreach ($val as $x => $y) {
                            if ($x == 'version') {
                                $old_version = $y;
                                if ($new_version > $old_version) {
                                    $update_flag = 'true';
                                }
                                if ($new_version < $old_version) {
                                    $version_error = 'true';
                                }
                            }
                        }
                    }
                }
            }
			
           // ASTPPCOM-1374 mittal start
		   if($addon_type == 'Premium'){
			if($package_name == 'salesforce' || $package_name == 'whmcs'  || $package_name == 'zoho' || $package_name == 'stir_shaken'  ){ ?>
				<div class="col-sm-6 col-md-6 col-lg-4 mt-4">
				<div class="card h-100">
					<div class="row no-gutters">
						<div class="col-3 p-3">
									<img src="<?php echo base_url()."addons/$type/$package_name/static/images/".$package_name."_icon.png";?>"
											class="img-fluid img-rounded" alt="icon">
						</div>
						<div class="col-9">
							<div class="card-body py-3 pl-0 pr-3">
							<a class="btn p-0 addon_title" href="#"><h3 class="text-dark card-title mb-1"><?= $elements->name ?></h3></a>
									<?php if($package_name == 'language_demo'){?>
										<p class="card-text"><a href="https://docs.astppbilling.org/pages/viewpage.action?pageId=4197155"><?= $elements->description ?></a></p>
											<?php } else { ?> 
											<p class="card-text" style="text-align:left;"><?= $elements->description ?></p>	
											<?php } ?>
							</div>
						</div>
						<div class="col-md-12 p-3 d-flex">
										<div class="addon_version px-0 float-left mt-4 col"><?php echo gettext("Version :"); ?> <?= $elements->version ?></div>
										<div
											class="float-right text-right align-self-center p-0 col-4">
									 		 <?
											if ($version_error == 'false') {
																$request_uri = explode("/", $_SERVER['REQUEST_URI']);
																 if(isset($request_uri) && $request_uri[3] == 'Premium') { 
																		if($package_name == 'whmcs') /* PHP8: was an undefined constant */{?>
																			<a href="https://astppbilling.org/astpp-whmcs-integration/"
																						class="btn btn-block btn-index btn-outline-primary" target="_blank">More Information</a> 
																		<?php }else if($package_name == 'zoho') /* PHP8: was an undefined constant */{ ?>
																			<a href="https://astppbilling.org/zoho-integration/"
																						class="btn btn-block btn-index btn-outline-primary" target="_blank">More Information</a> 
																		<?php } else if($package_name == 'salesforce') /* PHP8: was an undefined constant */{?>
																			<a href="https://astppbilling.org/salesforce-integration/"
																						class="btn btn-block btn-index btn-outline-primary" target="_blank">More Information</a> 
																		<?php } else {?>
																			<a href="https://astppbilling.org/stir-shaken-solution/"
																						class="btn btn-block btn-index btn-outline-primary" target="_blank">More Information</a> 
																		<?php }
																 }else {?>
																			<a href="#" class="btn btn-block btn-info" target="_blank">More Information</a>
																<?php }
											} else {
             						  		 ?>
											<p style="color: red; font-weight: bold;"><?php echo gettext("Addon broken"); ?></p>
											<?} ?>
										</div>
						</div>
					</div>
				</div>
			</div>
			<?php } }	//ASTPPCOM-1374 mittal end
			//ASTPPENT-7738 mittal start
			
			else { 
				//ASTPPENT-7738 mittal start
				if($package_name == 'whmcs'){
					unset($package_name);
				} else{
				?>
					<div class="col-sm-6 col-md-6 col-lg-4 mt-4">
							<div class="card h-100">
								<div class="row no-gutters">
									<div class="col-3 p-3">
										<a class="btn p-0"
											href="<?php echo base_url();?>addons/addons_details/<?php echo $type;?>/<?php echo $elements->tech_name; ?>/<?php echo $old_version; ?>">
								  <?php //echo FCPATH; exit;?>
									<img
											src="<?php echo base_url()."addons/$type/$package_name/static/images/".$package_name."_icon.png";?>"
											class="img-fluid img-rounded" alt="icon">
										</a>
									</div>
									<div class="col-9">
										<div class="card-body py-3 pl-0 pr-3">
											<a class="btn p-0 addon_title"
												href="<?= base_url()?>addons/addons_details/<?php echo $type;?>/<?= $elements->tech_name ?>/<?php echo $old_version; ?>">
												<h3 class="text-dark card-title mb-1"><?= $elements->name ?>
												<?php if($elements->license == 'Commercial') { ?>
													<span id="Enterprise" class="badge badge-warning Enterprise" style="font-size:9px">Commercial</span>
												<?php } ?></h3>
											</a>
											<?php if($package_name == 'language_demo'){?>
												<p class="card-text"><a href="https://docs.astppbilling.org/pages/viewpage.action?pageId=4197155"><?= $elements->description ?></a></p>
											<?php } else { ?> 
											<p class="card-text"><?= $elements->description ?></p>	
											<?php }?>
										</div>
									</div>
									<div class="col-md-12 p-3 d-flex">
										<div class="addon_version px-0 float-left mt-4 col"><?php echo gettext("Version :"); ?> <?= $elements->version ?></div>
										<div
											class="float-right text-right align-self-center p-0 col-4">
									  <?
									    if ($version_error == 'false') {
           								     if ($update_flag == 'true') {
               									 	$request_uri = explode("/", $_SERVER['REQUEST_URI']);
													if (isset($request_uri) && $request_uri[3] == 'Enterprise') { ?>
														<a href="<?= base_url() ?>addons/addons_enterprise_license/<?= $type."/".$package_name ?>/install/<?= $new_version."/".$old_version ?>"
															class="btn btn-info btn-block" rel="facebox" >Update</a>
													<?php }
													else { ?>
														<a href="<?= base_url() ?>addons/addons_install/<?= $type."/".$package_name ?>/update/<?= $new_version."/".$old_version ?>"
														class="btn btn-block btn-info" onclick='return install_addon()'><?php echo gettext("Update"); ?></a>
													<? } 
												}
												else {
                   									 if ($new_version == $old_version) {  ?>
														<a href="#" class="btn btn-light border btn-block"><?php echo gettext("Installed"); ?></a>
														<?}
													else{ ?>
														<!-- <a href="<?= base_url() ?>addons/addons_install/<?= $type."/".$package_name ?>/install/<?= $new_version."/".$old_version ?>" class="btn btn-block btn-info"><?php echo gettext("Install"); ?></a>  -->
														<?php
                     								  	 $request_uri = explode("/", $_SERVER['REQUEST_URI']);
                     								  	 if (isset($request_uri) && $request_uri[3] == 'Enterprise') {?>
															<a 	href="<?= base_url() ?>addons/addons_enterprise_license/<?= $type."/".$package_name ?>/install/<?= $new_version."/".$old_version ?>"
															class="btn btn-info btn-block" rel="facebox" >Install</a> 
													 	 <?php }else{?>
															<a
															href="<?= base_url() ?>addons/addons_install/<?= $type."/".$package_name ?>/install/<?= $new_version."/".$old_version ?>"
															class="btn btn-block btn-info" onclick='return install_addon()'>Install</a> 
														 <?php }
                    									}
               									}
           							    } 
										else { ?>
											<p style="color: red; font-weight: bold;"><?php echo gettext("Addon broken"); ?></p>
										<? } ?>
									</div>
									</div>
								</div>
							</div>
						</div>
				<? } //ASTPPENT-7738 mittal start
			} } ?>

                    <!-- </div> -->
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<? endblock() ?>  

<? end_extend() ?>
<script type="text/javascript">
  $(document).ready(function(){
		$('.page-wrap').addClass('addon_wrap');

		$(function(){
			var current = location.pathname;
			$('.sidemenu li a').each(function(){
				var $this = $(this);
				// if the current path is like this link, make it active
				if($this.attr('href').indexOf(current) !== -1){
					$this.parents('.sidemenu li ').addClass('active');
				}
			})
		})

		$(".addon_title").removeAttr("data-ripple",""); 
  });
</script>
