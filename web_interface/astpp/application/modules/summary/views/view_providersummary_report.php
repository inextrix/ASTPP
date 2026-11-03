<? extend('master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
      
        build_grid("providersummary_grid","",<? echo $grid_fields; ?>,<? echo $grid_buttons; ?>);
        
        $("#providersummary_search_btn").click(function(){
            document.providersummary_search.submit();
        });        
        $("#id_reset").click(function(){
            clear_search_request("providersummary_grid","");
            // Jaimin ASTPPCOM-847
            setTimeout(function(){
               var from_date = $('#provider_from_date').val("");
               var to_date=$('#provider_to_date').val("");
            },50);
            // END
        });
    });
</script>
<script>
    $(document).ready(function() {
        var from_date = date + " 00:00:00";
        var to_date = date + " 23:59:59";
        $("#provider_from_date").datetimepicker({
            value: from_date,
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd HH:MM:ss',
            footer:true
         });  
         $("#provider_to_date").datetimepicker({
            value: to_date,
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd HH:MM:ss',
            footer:true
         });  
    });
</script>
<? endblock() ?>

<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>
<section class="slice color-three">
	<div class="w-section inverse no-padding">

		<div class="col-12">
			<div class="portlet-content mb-4" id="search_bar"
				style="display: none">
				<div class="card">
					<form action="<?php echo base_url(); ?>/summary/provider_search/"
						method="post" accept-charset="utf-8" id="providersummary_search"
						name="providersummary_search">
						<div class="float-right panel_close text-light p-3"
							id="global_clearsearch_filter" style="cursor: pointer;">
							<i class="fa fa-remove"></i>
						</div>
						<ul id="floating-label" class="px-0 pb-4">
							<h3 class="bg-secondary text-light p-3 rounded-top"> <?php echo gettext("Search"); ?></h3>
							<div class="col-md-12">
								<div class="row">
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("From Date"); ?></label>
										<input type="text" name="callstart[]"
											value="<?php echo isset($session_info['callstart'][0]) ? $session_info['callstart'][0] : date("Y-m-d") . " 00:00:00"; ?>"
											id="provider_from_date" size="20"
											class="col-md-12 form-control form-control-lg " />
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("To Date"); ?></label>
										<input type="text" name="callstart[]"
											value="<?php echo isset($session_info['callstart'][1]) ? $session_info['callstart'][1] : date("Y-m-d") . " 23:59:59"; ?>"
											id="provider_to_date" size="20"
											class="col-md-12 form-control form-control-lg " />
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Account"); ?></label>

										<select name="provider_id"
											class='col-md-12 form-control form-control-lg selectpicker'
											data-live-search='true'>
											<option value=''><?php echo gettext("--Select--")?></option>
                                        <?php

                                        if (! empty($accountlist)) {
                                            foreach ($accountlist as $key => $value) {
                                                ?>
                                                <optgroup
												label="<?php echo $key ?>">
                                                    <?php
                                                foreach ($value as $sub_key => $sub_value) {
                                                    $selected = null;
                                                    if (isset($session_info['provider_id']) && astpp_gt($session_info['provider_id'], 0) && $sub_key == $session_info['provider_id']) {
                                                        $selected = "selected";
                                                    }
                                                    ?>
                                                        <option
													value='<?php echo $sub_key; ?>' <?php echo $selected; ?>><?php echo $sub_value ?></option>
                                                <?
                                                }
                                                ?>
                                                </optgroup>
    <?
                                            }
                                        }
                                        ?>
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Trunk"); ?></label>
										<select name="trunk_id"
											class='col-md-12 form-control form-control-lg selectpicker'
											data-live-search='true'>
											<option value=''><?php echo gettext("--Select--")?></option>
                                        <?php
                                        if (! empty($trunklist)) {
                                            foreach ($trunklist as $key => $value) {
                                                $selected = null;
                                                if (isset($session_info['trunk_id']) && astpp_gt($session_info['trunk_id'], 0) && $key == $session_info['trunk_id']) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value='<?php echo $key; ?>' <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?
                                            }
                                        }
                                        ?>
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Code"); ?> </label>
										<input type="text" name="pattern[pattern]"
											value="<?php echo (isset($session_info['pattern']) && isset($session_info['pattern']['pattern']) && !empty($session_info['pattern']['pattern'])) ? $session_info['pattern']['pattern'] : ''; ?>"
											size="20" maxlength="15"
											class="col-md-12 form-control form-control-lg " /> <select
											name="pattern[pattern-string]"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                        <?php
                                        if (! empty($search_string_type)) {
                                            foreach ($search_string_type as $key => $value) {
                                                $selected = null;
                                                if (isset($session_info['pattern']) && isset($session_info['pattern']['pattern']) && ! empty($session_info['pattern']['pattern']) && $session_info['pattern']['pattern-string'] == $key) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?php
                                            }
                                        }
                                        ?>       
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Destination"); ?> </label>
										<input type="text" name="notes[notes]"
											value="<?php echo (isset($session_info['notes']) && isset($session_info['notes']['notes']) && !empty($session_info['notes']['notes'])) ? $session_info['notes']['notes'] : ''; ?>"
											size="20" class="col-md-12 form-control form-control-lg " />
										<select name="notes[notes-string]"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                    <?php
                                    if (! empty($search_string_type)) {
                                        foreach ($search_string_type as $key => $value) {
                                            $selected = null;
                                            if (isset($session_info['notes']) && isset($session_info['notes']['notes']) && ! empty($session_info['notes']['notes']) && $session_info['notes']['notes-string'] == $key) {
                                                $selected = "selected";
                                            }
                                            ?>
                                            <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                    <?php
                                        }
                                    }
                                    ?>       
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Select Year"); ?></label>
										<select name="cdrs_year"
											class='col-md-12 form-control form-control-lg selectpicker'
											data-live-search='true'>
                                        <?php

                                        if (! empty($cdrs_year)) {
                                            foreach ($cdrs_year as $key => $value) {
                                                ?>
											<?php
                                                $selected = null;
                                                if (isset($cdrs_year_val) && $key == $cdrs_year_val) {
                                                    $selected = "selected";
                                                }
                                                ?>
											<option value='<?php echo $key; ?>' <?php echo $selected; ?>><?php echo $value ?></option>
										<?
                                            }
                                        }
                                        ?>
                                    </select>
									</div>
									<div class="col-3 input-group">
										<input type="hidden" name="ajax_search" value="1" />
									</div>
									<div class="col-3 input-group">
										<input type="hidden" name="advance_search" value="1" />
									</div>
								</div>
							</div>
						</ul>
						<ul id="floating-label" class="px-0 pb-4">

							<h3 class="alert-dark p-2 "> <?php echo gettext("Group By"); ?></h3>
							<div class="col-md-12 mb-4">
								<div class="row">
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Group By #Time"); ?></label>
										<select name="time"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                        <?php
                                        if (! empty($groupby_time)) {
                                            foreach ($groupby_time as $key => $value) {
                                                $selected = null;
                                                if (isset($session_info['time']) && ! empty($session_info['time']) && $session_info['time'] == $key) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?php
                                            }
                                        }
                                        ?>  
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Group By #1"); ?></label>
										<select name="groupby_1"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                        <?php
                                        if (! empty($groupby_field)) {
                                            foreach ($groupby_field as $key => $value) {
                                                $selected = null;
                                                if (isset($session_info['groupby_1']) && ! empty($session_info['groupby_1']) && $session_info['groupby_1'] == $key) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?php
                                            }
                                        }
                                        ?>        
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Group By #2"); ?></label>
										<select name="groupby_2"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                        <?php
                                        if (! empty($groupby_field)) {
                                            foreach ($groupby_field as $key => $value) {
                                                $selected = null;
                                                if (isset($session_info['groupby_2']) && ! empty($session_info['groupby_2']) && $session_info['groupby_2'] == $key) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
    <?php
                                            }
                                        }
                                        ?>
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Group By #3"); ?></label>
										<select name="groupby_3"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                        <?php
                                        if (! empty($groupby_field)) {
                                            foreach ($groupby_field as $key => $value) {
                                                $selected = null;
                                                if (isset($session_info['groupby_3']) && ! empty($session_info['groupby_3']) && $session_info['groupby_3'] == $key) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?php
                                            }
                                        }
                                        ?>
                                    </select>
									</div>
								</div>
							</div>

							<div class="col-12 p-4">
								<button name="action" type="reset" id="id_reset" value="cancel"
									class="btn btn-secondary float-right ml-2"><?php echo gettext("Clear"); ?></button>
								<button name="action" type="button"
									id="providersummary_search_btn" value="save"
									class="btn btn-success float-right"><?php echo gettext("Search"); ?></button>
								<div class="col-md-5 float-right">
									<label class="search_label col-md-6 text-right"><?php echo gettext("Display records in"); ?> </label>
									<select name="search_in"
										class='col-md-5 form-control form-control-lg selectpicker'
										data-live-search='true'>
<?php
if (! empty($search_report)) {
    foreach ($search_report as $key => $value) {
        $selected = null;
        if (isset($session_info['search_in']) && isset($session_info['search_in']) && ! empty($session_info['search_in']) && $session_info['search_in'] == $key) {
            $selected = "selected";
        }
        ?>
                                                <option
											value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
    <?php
    }
}
?>       
                                    </select>
								</div>
							</div>
						</ul>
					</form>
				</div>
			</div>
		</div>

</section>
<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class="card col-md-12 pb-4">
			<form method="POST" action="del/0/" enctype="multipart/form-data"
				id="ListForm">
				<table id="providersummary_grid" align="left" style="display: none;"></table>
			</form>
		</div>
	</div>
</section>




<? endblock() ?>	
<? end_extend() ?> 
