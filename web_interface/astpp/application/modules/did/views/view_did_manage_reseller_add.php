<?php include(FCPATH.'application/views/popup_header.php'); ?>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/jquery-1.7.1.js"></script>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/facebox.js"></script>
<script type="text/javascript"
	src="<?php echo base_url(); ?>assets/js/flexigrid.js"></script>
<script type="text/javascript" src="/js/validate.js"></script>

<script type="text/javascript">
    $("#submit").click(function(){
		submit_form("frm_manage_did");
    })
</script>
<script type="text/javascript">
    $(document).ready(function() {

        $("#frm_manage_did").validate({
            rules: {
                number: "required",
                limittime: "required"
            }
        });
    });
</script>
<section class="slice gray no-margin">
	<div class="w-section inverse no-padding">
		<div>
			<div>
				<div class="col-md-12 no-padding margin-t-15 margin-b-10">
					<div class="col-md-10">
						<b><? echo $page_title; ?></b>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<div>
	<div>
		<section class="slice color-three no-margin">
			<div class="w-section inverse no-padding">
				<form
					action="<?= base_url() ?><?= isset($did) ? "did/did_reseller_edit/edit" : "did/did_reseller_edit/add" ?>"
					id="frm_manage_did" method="POST" enctype="multipart/form-data">
					<fieldset>
						<legend><?php echo gettext("DID Information"); ?></legend>
						<ul class="padding-15">
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("DID "); ?>*</label>
								<input type="text" class="col-md-5 form-control" readonly
								name="note" value="<?= $did ?>" /></li>
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("Call Type"); ?></label>
								<select name="call_type"
								class="col-md-5 form-control selectpicker"
								data-live-search='true'>
                  		  <?php

$calltype = $this->common->set_call_type();
                    foreach ($calltype as $key => $value) {
                        $selected = astpp_loose_eq($reseller_didinfo['call_type'], $key) ? /* PHP8 */ "selected='selected'" : '';
                        echo "<option value='$key'$selected>$value</option>";
                    }
                    ?>
                		    </select></li>
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("Destinations"); ?> </label>
								<input type="text" class="col-md-5 form-control"
								name="extensions" value="<?= $reseller_didinfo['extensions'] ?>" />
							</li>
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("Setup Fee"); ?> </label>
								<input type="text" class="col-md-5 form-control" name="setup"
								value="<?= $reseller_didinfo['setup'] ?>" /></li>
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("Monthly Fee"); ?> </label>
								<input type="text" class="col-md-5 form-control"
								name="monthlycost"
								value="<?= $reseller_didinfo['monthlycost'] ?>" /></li>
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("Connection Fee"); ?> </label>
								<input type="text" class="col-md-5 form-control"
								name="connectcost"
								value="<?= $reseller_didinfo['connectcost'] ?>" /></li>
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("Included Seconds"); ?> </label>
								<input type="text" class="col-md-5 form-control"
								name="includedseconds"
								value="<?= $reseller_didinfo['includedseconds'] ?>" /></li>
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("Cost"); ?> </label>
								<input type="text" class="col-md-5 form-control" name="cost"
								value="<?= $reseller_didinfo['cost'] ?>" /></li>
							<li class="col-md-12"><label class="col-md-3 no-padding"><?php echo gettext("Increments"); ?> </label>
								<input type="text" class="col-md-5 form-control" name="inc"
								value="<?= $reseller_didinfo['inc'] ?>" /></li>
						</ul>
					</fieldset>
					<center>
						<div
							style="width: 100%; float: left; height: 50px; margin-top: 20px;">
							<input type="button" class="btn btn-line-parrot" id='submit'
								style="margin-left: 5px;" name="action"
								value="<?= isset($did) ? gettext("Save") : gettext("Insert"); ?>" />
							<button class="btn btn-line-sky margin-x-10" name="action"
								type="button" value="cancel"
								onclick="return redirect_page('NULL')"><?php echo gettext('Close'); ?></button>
						</div>
					</center>
				</form>
			</div>
		</section>
	</div>
</div>



