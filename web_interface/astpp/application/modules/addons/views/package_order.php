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
					<h2 class="m-0 text-light"><?php echo gettext("Package Order"); ?></h2>
				</div>
			</div>
			<div class="container-fluid px-sm-0 px-md-4">
				<div class="customer_package_order">
					<div class="col-md-12 p-0">

						<div class="row p-4">
							<div class="card col-md-7 py-4">
								<div class="card p-4">
									<h4 class="h3 text-truncate text-dark card-title mb-1"><?php echo gettext("ASTPP - VoIP Billing Solution"); ?></h4>
									<p class="card-text"><?php echo gettext("Lorem Ipsum is simply dummy text of the printing"); ?></p>
								</div>
								<div>
									<ul class="p-5 alert-secondary">
										<li><?php echo gettext("Lorem ipsum dolor sit amet, consectetur adipiscing elit."); ?></li>
										<li><?php echo gettext("Sed sed augue vitae tortor finibus porttitor fringilla et nulla."); ?></li>
										<li><?php echo gettext("Aliquam et arcu gravida, volutpat elit vitae, egestas tellus."); ?></li>
										<li><?php echo gettext("Sed mattis tellus tempor lorem tempor, et viverra quam vestibulum."); ?></li>
										<li><?php echo gettext("Sed id nibh ut ipsum facilisis efficitur."); ?></li>
									</ul>
								</div>
								<div id="floating-label" class="col-12 mt-4">
									<h4 class="text-dark"><?php echo gettext("Configurable Options"); ?></h4>
									<div class="form-group">
										<label for="" class="control-label"><?php echo gettext("Training") ?></label>
										<select name="" id=""
											class="text-dark col-md-6 form-control selectpicker form-control-lg mr-4 ">
											<option value="">1</option>
											<option value="" selected>2</option>
											<option value="">3</option>
											<option value="">4</option>
										</select>
									</div>
								</div>
							</div>

							<div class="col-md-5 pr-0">
								<div class="card">
									<div class="card-header">
										<h2><?php echo gettext("Order Summary"); ?></h2>
									</div>
									<div class="card-body">
										<h4 class="h4"><?php echo gettext("ASTPP - Single Server Installation"); ?></h4>
										<p class="card-text"><?php echo gettext("ASTPP - VoIP Billing Solution"); ?></p>
										<dl class="row px-4">
											<dt class="text-truncate mr-auto"><?php echo gettext("ASTPP - Single Server Installation"); ?></dt>
											<dd>$450.00 USD</dd>
										</dl>
										<dl class="row px-4">
											<dt class="fw-n text-truncate mr-auto"><?php echo gettext("Training: 1 User"); ?></dt>
											<dd class="">$0.00 USD</dd>
										</dl>
										<hr class="mb-2">
										<dl class="row px-4 my-0">
											<dt class="fw-n text-truncate mr-auto"> <?php echo gettext("Setup Fees:"); ?></dt>
											<dd class="m-0">$0.00 USD</dd>
										</dl>
										<hr class="mt-2">
										<h2 class="h2 float-right">$450.00 USD</h2>

									</div>
									<div class="card-footer"></div>

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
