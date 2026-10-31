<? extend('left_panel_master.php') ?>
<? startblock('extra_head') ?>
<?php endblock() ?>
<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>
<?php startblock('content') ?>
<div class="sidebar collapse">
	<ul class="sidemenu">
		<li class="active"><a
			href="<?php echo base_url(); ?>addons/addons_refill"><?php echo gettext("Refill"); ?></a></li>
		<li><a href="<?php echo base_url(); ?>addons/addons_packages"><?php echo gettext("Packages"); ?></a></li>
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
					<h2 class="m-0 text-light"><?php echo gettext("Refill"); ?></h2>
				</div>
			</div>
			<div class="container-fluid px-sm-0 px-md-4">
				<div class="customer_refill">
					<div class="col-12">
						<div class="row">
							<div class="col-sm-3 mt-4 d-flex">
								<div class="card text-center col-md-12 p-0 border border-info">
									<a href="" class="btn p-4 h-100 text-info">
										<h1 class="my-5">
											<sup>$</sup> 10
										</h1>
									</a>
								</div>
							</div>
							<div class="col-sm-3 mt-4 d-flex">
								<div class="card text-center col-md-12 p-0 border border-info">
									<a href="" class="btn p-4 h-100 text-info">
										<h1 class="my-5">
											<sup>$</sup> 15
										</h1>
									</a>
								</div>
							</div>
							<div class="col-sm-3 mt-4 d-flex">
								<div class="card text-center col-md-12 p-0 border border-info">
									<a href="" class="btn p-4 h-100 text-info">
										<h1 class="my-5">
											<sup>$</sup> 20
										</h1>
									</a>
								</div>
							</div>
							<div class="col-sm-3 mt-4 d-flex">
								<div class="card text-center col-md-12 p-0 bg-info">
									<a href="" class="btn p-4 h-100 text-white">
										<h2 class="py-5"><?php echo gettext("Use Voucher"); ?></h2>
									</a>
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
