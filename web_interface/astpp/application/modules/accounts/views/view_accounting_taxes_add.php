<script>
  $(document).ready(function(){
    submit_form("account_taxes_form");
    $("#account_num").change(function(){
      var username = $("#account_num").val();
      if(username.length > 4)
      {
        $.ajax({ 
          type: "POST",
                    url: '<?php base_url() ?>/accounting/valid_account_tax/',
                    data: "username="+username,  
                    success: function(server_response){
                      var taxe_data=server_response;
                      var chk_id = taxe_data.split(",");
                      $("#account_id").val(chk_id[0].replace(/^\s+|\s+$/g,""));
                      
                      for(i=1; i< chk_id.length; i++){
                        var checkbox = document.getElementById('tax_'+(chk_id[i].replace(/^\s+|\s+$/g,"")));
                        if(checkbox) 
                          checkbox.checked = true;
                      }
                    }
                  });
      }
      else{
        $("#availability_status").html('<font color="#cc0000"><?php echo gettext("Please Enter Valid Account Number"); ?></font>');
      }
      return false;
    });
  });
</script>

<div
	class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
	<div class="portlet-header ui-widget-header">
		<?php echo gettext("Account Taxes"); ?><span class="ui-icon ui-icon-circle-arrow-s"></span>
	</div>
	<div class="portlet-content">
		<form method="post" id="account_taxes_form"
			action="<?= base_url() ?><?= isset($tax_id) ? "accounts/customer_account_taxes/edit" : "accounts/customer_account_taxes/add" ?>"
			enctype="multipart/form-data">
			<ul style="width: 600px">
				<fieldset style="width: 585px;">
					<legend>
						<span style="font-size: 14px; font-weight: bold; color: #000;"><?php echo gettext("Account Taxes Information"); ?></span>
					</legend>
					<li><label class="desc"><?php echo gettext("Account Number:");?></label>

              <?php if (isset($accountnum)) { ?>
                <input class="desc" value="<?php echo $accountnum; ?>"
						type="text" id="account_num" name="account_num" size="20"
						readonly="readonly" /> <input class="text field medium"
						type="hidden" id="account_id" name="account_id"
						value="<?php echo $account_id; ?>" size="20" />
              <? } else { ?>
                <input class="text field medium" type="text"
						id="account_num" name="account_num" size="20" /> <input
						class="text field medium" type="hidden" id="account_id"
						name="account_id" size="20" />
              <? } ?>
              <span id="availability_status"></span></li>
					<div style="float: left; width: 275px; margin-left: 6px;">
						<li>
                <?php
                $i = 1;

                if (isset($taxesList) && is_array($taxesList) && astpp_count($taxesList) > 0) {
                    echo "<table>";
                    foreach ($taxesList as $values) {
                        if ($i == 1)
                            echo "<tr>";
                        ?>
                  <td style="padding-left: 10px;"><input type="checkbox"
								id="tax_<?= $values->id; ?>" name="tax_<?= $values->id; ?>"
								value="<?= $values->id; ?>"
								<?

if (astpp_in_array($values->id, $tax_ids)) {
                            echo "checked";
                        }
                        ?> /> <?= $values->taxes_description; ?></label><br />
						</td>
                  <?php
                        if (astpp_n($i) % 3 == 0) {
                            echo "</tr>";
                            $i = 1;
                        } else {
                            $i ++;
                        }
                    }
                    echo "</table>";
                }
                ?>
           </li>
					</div>
				</fieldset>
			</ul>
			<div
				style="width: 100%; float: left; height: 50px; margin-top: 20px;">
				<input class="ui-state-default float-right ui-corner-all ui-button"
					type="submit" name="action"
					value="<?= isset($accountnum) ? gettext("Save") : gettext("Add") ?>" />
			</div>

		</form>
	</div>
</div>
