<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<?php endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<?php startblock('content') ?>
<div class="sidebar collapse">
	<ul class="sidemenu">
		<li><a href="<?php echo base_url(); ?>addons/addons_refill"><?php echo gettext("Refill"); ?></a></li>
		<li class="active"><a
			href="<?php echo base_url(); ?>addons/addons_packages"><?php echo gettext("Packages"); ?></a></li>
		<li><a href=""><?php echo gettext("Subscription"); ?></a></li>
		<li><a href=""><?php echo gettext("DIDs"); ?></a></li>
		<li><a href=""><?php echo gettext("Other"); ?></a></li>
	</ul>
</div>

<div id="main-wrapper">
	<section class="slice color-three pb-4">
		<div class="w-section inverse p-0">
			<div class="main_wrapper_title color-three border_box">
				<div class="col-md-8 float-left my-2">
					<h2 class="m-0 text-light"><?php echo gettext("Packages"); ?></h2>
				</div>
			</div>
			<div class="container-fluid px-sm-0 px-md-4">
				<div class="customer_packages">
					<div class="col-12">
						<div class="row">
							<div class="col-sm-4 mt-4">
								<div class="row no-gutters">
									<div class="card flex-row flex-wrap">
										<div class="col-8 py-3 px-0">
											<div class="card-body px-3 py-0">
												<h3 class="h3 text-truncate text-dark card-title mb-1"><?php echo gettext("Special title treatment"); ?></h3>
												<p class="card-text"><?php echo gettext("Lorem Ipsum is simply dummy text of the printing"); ?></p>
											</div>
										</div>
										<div class="col-4 p-3">
											<h4 class="text-info m-0 text-nowrap">
												$20 <sub class="text-secondary">/ 25 <?php echo gettext("days"); ?></sub>
											</h4>
											<div class="badge float-right py-2 mb-2 fw-n"><?php echo gettext("(one time)");?></div>
											<div class="badge text-dark p-0 mb-2 fw-n"><?php echo gettext("Free :");?> <span
													class="">50 <?php echo gettext("min"); ?></span>
											</div>
											<a
												href="<?php echo base_url(); ?>addons/addons_package_order"
												class="btn btn-block btn-info"><i
												class="fa fa-shopping-cart"></i> <?php echo gettext("Order"); ?></a>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-4 mt-4">
								<div class="row no-gutters">
									<div class="card flex-row flex-wrap">
										<div class="col-8 py-3 px-0">
											<div class="card-body px-3 py-0">
												<h3 class="h3 text-truncate text-dark card-title mb-1"><?php echo gettext("Special title treatment"); ?></h3>
												<p class="card-text"><?php echo gettext("Lorem Ipsum is simply dummy text of the printing"); ?></p>
											</div>
										</div>
										<div class="col-4 p-3">
											<h4 class="text-info m-0 text-nowrap">
												$80 <sub class="text-secondary">/ 25 <?php echo gettext("days"); ?></sub>
											</h4>
											<div class="badge float-right py-2 mb-2 fw-n"><?php echo gettext("(one time)"); ?></div>
											<div class="badge text-dark p-0 mb-2 fw-n"><?php echo gettext("Free :"); ?> <span
													class="">20 <?php echo gettext("min"); ?></span>
											</div>
											<a
												href="<?php echo base_url(); ?>addons/addons_package_order"
												class="btn btn-block btn-info"><i
												class="fa fa-shopping-cart"></i> <?php echo gettext("Order"); ?></a>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-4 mt-4">
								<div class="row no-gutters">
									<div class="card flex-row flex-wrap">
										<div class="col-8 py-3 px-0">
											<div class="card-body px-3 py-0">
												<h3 class="h3 text-truncate text-dark card-title mb-1"><?php echo gettext("Special title treatment"); ?></h3>
												<p class="card-text"><?php echo gettext("Lorem Ipsum is simply dummy text of the printing"); ?></p>
											</div>
										</div>
										<div class="col-4 p-3">
											<h4 class="text-info m-0 text-nowrap">
												$100 <sub class="text-secondary">/ 25 <?php echo gettext("days"); ?></sub>
											</h4>
											<div class="badge float-right py-2 mb-2 fw-n"><?php echo gettext("(one time)"); ?></div>
											<div class="badge text-dark p-0 mb-2 fw-n"><?php echo gettext("Free : "); ?><span
													class="">80 <?php echo gettext("min"); ?></span>
											</div>
											<a
												href="<?php echo base_url(); ?>addons/addons_package_order"
												class="btn btn-block btn-info"><i
												class="fa fa-shopping-cart"></i> <?php echo gettext("Order"); ?></a>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-4 mt-4">
								<div class="row no-gutters">
									<div class="card flex-row flex-wrap">
										<div class="col-8 py-3 px-0">
											<div class="card-body px-3 py-0">
												<h3 class="h3 text-truncate text-dark card-title mb-1"><?php echo gettext("Special title treatment"); ?></h3>
												<p class="card-text"><?php echo gettext("Lorem Ipsum is simply dummy text of the printing"); ?></p>
											</div>
										</div>
										<div class="col-4 p-3">
											<h4 class="text-info m-0 text-nowrap">
												$150 <sub class="text-secondary">/ 25 <?php echo gettext("days"); ?></sub>
											</h4>
											<div class="badge float-right py-2 mb-2 fw-n"><?php echo gettext("(one time)"); ?></div>
											<div class="badge text-dark p-0 mb-2 fw-n"><?php echo gettext("Free :"); ?> <span
													class="">50<?php echo gettext(" min"); ?></span>
											</div>
											<a
												href="<?php echo base_url(); ?>addons/addons_package_order"
												class="btn btn-block btn-info"><i
												class="fa fa-shopping-cart"></i><?php echo gettext("Order"); ?></a>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-4 mt-4">
								<div class="row no-gutters">
									<div class="card flex-row flex-wrap">
										<div class="col-8 py-3 px-0">
											<div class="card-body px-3 py-0">
												<h3 class="h3 text-truncate text-dark card-title mb-1"><?php echo gettext("Special title treatment"); ?></h3>
												<p class="card-text"><?php echo gettext("Lorem Ipsum is simply dummy text of the printing"); ?></p>
											</div>
										</div>
										<div class="col-4 p-3">
											<h4 class="text-info m-0 text-nowrap">
												$200 <sub class="text-secondary">/ 25 <?php echo gettext("days"); ?></sub>
											</h4>
											<div class="badge float-right py-2 mb-2 fw-n"><?php echo gettext("(one time)"); ?></div>
											<div class="badge text-dark p-0 mb-2 fw-n"><?php echo gettext("Free :"); ?> <span
													class="">30 <?php echo gettext("min"); ?></span>
											</div>
											<a
												href="<?php echo base_url(); ?>addons/addons_package_order"
												class="btn btn-block btn-info"><i
												class="fa fa-shopping-cart"></i> <?php echo gettext("Order"); ?></a>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-4 mt-4">
								<div class="row no-gutters">
									<div class="card flex-row flex-wrap">
										<div class="col-8 py-3 px-0">
											<div class="card-body px-3 py-0">
												<h3 class="h3 text-truncate text-dark card-title mb-1"><?php echo gettext("Special title treatment"); ?></h3>
												<p class="card-text"><?php echo gettext("Lorem Ipsum is simply dummy text of the printing"); ?></p>
											</div>
										</div>
										<div class="col-4 p-3">
											<h4 class="text-info m-0 text-nowrap">
												$200 <sub class="text-secondary">/ 25 <?php echo gettext("days"); ?></sub>
											</h4>
											<div class="badge float-right py-2 mb-2 fw-n"><?php echo gettext("(one time)"); ?></div>
											<div class="badge text-dark p-0 mb-2 fw-n"><?php echo gettext("Free :"); ?> <span
													class="">10 <?php echo gettext("min"); ?></span>
											</div>
											<a
												href="<?php echo base_url(); ?>addons/addons_package_order"
												class="btn btn-block btn-info"><i
												class="fa fa-shopping-cart"></i> <?php echo gettext("Order"); ?></a>
										</div>
									</div>
								</div>
							</div>
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

      $(".addon_title").removeAttr("data-ripple",""); 
  });
</script>
