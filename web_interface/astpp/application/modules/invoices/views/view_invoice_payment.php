<? extend('master.php') ?>
<? startblock('extra_head') ?>
<link href="<?= base_url() ?>assets/css/invoice.css" rel="stylesheet">
<script type="text/javascript">
   $(document).ready(function() {
       $("#gross_amount").on("input", function(evt) {
   var self = $(this);
   self.val(self.val().replace(/[^0-9\.]/g, ''));
   if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) 
   {
     evt.preventDefault();
   }
    var amt = $("#gross_amount").val();	
    var Final_amt =0;
           if(amt != ''){
            Final_amt = parseFloat(amt);
    }
           var from_cur = "<?= $from_currency?>";
           var to_cur = "<?= $system_currency?>";
           var new_amt= '0';
           $.ajax({
               type:'POST',
               url: "<?= base_url()?>/user/user_payment/GET_AMT/",
               data:"value="+Final_amt, 
               success: function(response) {
     
                     $("#amt_in_currency").html(Final_amt +" "+from_cur + " To " + response +" "+to_cur);
                     $("#amount").val(response.trim());
               }
           });
       });
   }); 
</script>
<?php endblock() ?>
<?php startblock('page-title') ?>
<?= $page_title ?>
<?php endblock() ?>
<?php startblock('content') ?>
<div class="p-0">
   <section class="slice color-three">
      <div class="w-section inverse p-0">
         <div class="pop_md col-12 pb-4">
            <div class="card">
               <div class="col-12">
                  <div class="card-body table-responsive">
                     <?php $account_balance = $accountdata ['posttoexternal'] == 1 ? $accountdata ['credit_limit'] - ($accountdata ['balance']) : $accountdata ['balance'];  ?>
                     <div class="card-body">
                        <?php  if ($logintype == 0 || $logintype == 3 ) { ?>  
                        <form name="admin_form" id="admin_form"
                           action="<?php echo base_url()?>/user/user_invoice_unpaid_pay/"
                           method="POST">
                           <?php } else {?>
                        <form name="admin_form" id="admin_form"
                           action="/invoices/invoice_admin_payment/" method="POST">
                           <?php } ?>
                           <table class="table invoice_table col-md-6 float-left" border="0">
                              <tr class="form_invoice_head">
                                 <td class="p-0" style="border: 0;">
                                    <table class="table invoice_table1">
                                       <tr>
                                          <th>
                                             <h4>
                                                <b style="color: black;"><?php echo gettext('Customer Details')?>  </b>
                                             </h4>
                                          </th>
                                       </tr>
                                       <tr>
                                          <td scope="col"><b><?php echo gettext('Account Number :')?></b>
                                             <span style="font-size: bold; color: green;"><?php echo $accountdata['number']; ?></span>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td scope="col"><b><?php echo gettext('Name :')?></b> <font
                                             color="#a09d9d"><?php echo $accountdata['first_name']; ?> <?php echo $accountdata['last_name']; ?> </font>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td scope="col"><b><?php echo gettext('Email :')?> </b> <font
                                             style="color: #a09d9d;">
                                             <?php
                                                if ($accountdata['email'] != "") {
                                                    $attac_exp = explode(",", $accountdata['email']);
                                                    foreach ($attac_exp as $key => $value) {
                                                        if ($value != '') {
                                                            echo $value;
                                                        }
                                                    }
                                                }
                                                ?> 
                                             </font>
                                          </td>
                                       </tr>
                                    </table>
                                 </td>
                              </tr>
                           </table>
                           </td>
                           </tr>
                           </table>
                           <table border="0" class="col-md-6 float-left invoice_table4"
                              style="border: 0;">
                              <thead>
                                 <tr>
                                    <th style="padding: 4px 0px;" colspan="2">
                                       <h4>
                                          <b style="color: black;"><?php echo gettext('Invoice Details')?> </b>
                                       </h4>
                                    </th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <tr>
                                    <td><?php echo gettext('Invoice Number')?> :</td>
                                    <td><span class="float-right" style="color: #a09d9d;"><?php echo $invoice_info['number']; ?></span>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td><?php echo gettext('From Date')?> :</td>
                                    <td><span class="float-right" style="color: #a09d9d;"><?php echo date('Y-m-d', strtotime($invoice_info['from_date'])) ; ?> </span>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td><?php echo gettext('Due Date')?> :</td>
                                    <td><span class="float-right" style="color: #a09d9d;"><?php echo date('Y-m-d', strtotime($invoice_info['due_date'])) ; ?></span>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td width="100%" class="float-right"><b><?php echo gettext('Invoice Amount')?> :</b></td>
                                    <?php
                                       $amount = ($invoice_info['debit']);
                                           ?>
                                    <td><span class="pull-right" style="color: #3278b6"><b><?php echo $this->common->currency_decimal($this->common_model->calculate_currency($amount)); ?> <?php  echo $to_currency; ?></b>
                                       </span> <input type="hidden" name="total_amount"
                                          id="total_amount" class="article"
                                          value="<?php echo $amount; ?>"> <input type="hidden"
                                          readonly name="recharge" value="paypal_invoice">
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                           </br>
                           </br>
                           <th>
                              <h5>
                                 <b style="color: #163B80;";><?php echo gettext('INVOICE HISTORY')?></b>
                              </h5>
                           </th>
                           <div class="row">
                              <div class="col-md-6">
                                 <table width="100%" border="1" colspan="2"
                                    class="invoice_table2">
                                    <tr
                                       style="background-color: #375c7c; color: #fff; height: 40px;">
                                       <th width="70%">
                                          <?php echo gettext('Description')?>
                                       </th>
                                       <th>
                                          <div class="pull-right">
                                             <?php echo gettext('Amount')?>
                                          </div>
                                       </th>
                                    </tr>
                                    <?php
                                       $paypal = 0;
                                       if (isset($invoice_detail_info) && ! empty($invoice_detail_info)) {
                                           foreach ($invoice_detail_info as $value) {
                                               if ($value['charge_type'] != 'INVPAY' && $value['charge_type'] != 'REFILL' || $value['charge_type'] != 'REFILL' && $value['charge_type'] != 'COMMISSION') {
                                                   $debit = $value['debit'];
                                                   $credit = $value['credit'];
                                                   $paypal += $credit;
                                                   $created_date = $value['created_date'];
                                                   $paypalid = $value['order_item_id'];
                                                   $outstanding = $amount - $paypal;
                                                   $amount_visible = '0000';
                                                   ?>	
                                    <tr style="height: 20px;">
                                       <td><?php echo $value['description']; ?>  </td>
                                       <td>
                                          <div class="pull-right"><?php echo $this->common->currency_decimal($this->common_model->calculate_currency($debit)); ?> <?php  echo $to_currency;  ?></div>
                                       </td>
                                       <input type="hidden" name="new_amount" id="new_amount"
                                          value="<?php echo $this->common->currency_decimal($this->common_model->calculate_currency($amount)); ?>">
                                    </tr>
                                    <?php } } }  ?>
                                    <tr>
                                    </tr>
                                 </table>
                                 <br />
                                 <th>
                                    <h5>
                                       <b style="color: #163B80;";><?php echo gettext('PAYMENT HISTORY')?>   </b>
                                    </h5>
                                 </th>
                                 <table width="100%" border="1" colspan="2"
                                    class="invoice_table2">
                                    <tr
                                       style="background-color: #375c7c; color: #fff; height: 40px;">
                                       <th width="20%"> <?php echo gettext('Date')?></th>
                                       <th width="60%"><?php echo gettext('Description')?></th>
                                       <th>
                                          <div class="pull-right"><?php echo gettext('Amount')?></div>
                                       </th>
                                    </tr>
                                    <?php
                                       $paypal = 0;
                                       if (isset($invoice_detail_info) && ! empty($invoice_detail_info)) {
                                           foreach ($invoice_detail_info as $value) {
                                               if ($value['charge_type'] == 'INVPAY' || $value['charge_type'] == 'REFILL' || $value['charge_type'] == 'REFILL' || $value['charge_type'] == 'COMMISSION') {
                                                   $debit = $value['debit'];
                                                   $credit = $value['credit'];
                                                   $paypal += $credit;
                                                   $created_date = $value['created_date'];
                                                   $paypalid = $value['order_item_id'];
                                                   $outstanding = $amount - $paypal;
                                                   $amount_visible = '0000';
                                                   ?>	
                                    <tr>
                                       <td> <?php echo date('Y-m-d', strtotime($created_date)) ?></td>
                                       <td><?php echo $value['description']; ?>  <?php echo $paypalid; ?></td>
                                       <td>
                                          <div class="pull-right"><?php echo $this->common->currency_decimal($this->common_model->calculate_currency($credit)); ?> <?php  echo $to_currency; ?></div>
                                       </td>
                                       <input type="hidden" name="new_amount" id="new_amount"
                                          value="<?php echo $this->common->currency_decimal($this->common_model->calculate_currency($amount)); ?>">
                                    </tr>
                                    <?php
                                       }
                                               }
                                           }
                                           ?>
                                    <tr style="background-color: #f6f6f6;">
                                       <th colspan="2" width="30%"><span class="pull-right"
                                          style="color: #474747;"><?php echo gettext('Paid Amount')?></span>
                                       </th>
                                       <td align="right"><b><?php if ($paypal) { echo   $this->common->currency_decimal($this->common_model->calculate_currency($paypal)); } else { echo $this->common->currency_decimal($this->common_model->calculate_currency($invoice_info['credit']));}?> <?php  echo $to_currency; ?></b>
                                       </td>
                                    </tr>
                                    <tr>
                                    </tr>
                                 </table>
                                 <table width="100%" border="0" colspan="2"
                                    class="invoice_table4 mb-0">
                                    <tr style="">
                                       <td><b><?php echo gettext('Outstanding Amount')?> :</b></td>
                                       <td><span class="pull-right" style="color: #3278b6"><b><?php if (($invoice_info['debit'] - $invoice_info['credit']) >  0) { echo  $this->common->currency_decimal($this->common_model->calculate_currency(($invoice_info['debit'] - $invoice_info['credit']))); ?> <?php  echo $to_currency; } else {  echo "0.0000"; ?> <?php  echo $to_currency; } ?></b>
                                          </span>
                                       </td>
                                       <input type="hidden" name="total_amount" id="total_amount"
                                          class="article"
                                          value="<?php echo  $this->common->currency_decimal($amount); ?>">
                                    </tr>
                                 </table>
                              </div>
                              <div class="col-md-6 card">
                                 <table id="floating-label" class="invoice_table"
                                    width="100%" border="0">
                                    <tr class="form_invoice_head">
                                       <td width="100%" valign="top">
                                          <div class="table-responsive">
                                             <?php  if ($logintype == 0 || $logintype == 3) { ?>
                                             <table class="col-md-12">
                                                <tr>
                                                   <td class="border-0">
                                                      <div class="col-md-12 form-group p-0">
                                                         <label class="control-label"> <?php echo gettext('Please Insert Amount')?> :</label>
                                                         <input type="text" name="gross_amount" value=""
                                                            id="gross_amount"
                                                            class="col-md-12 form-control form-control-lg">
                                                      </div>
                                                      <div id="une"
                                                         style="padding-bottom: 10px; color: red;"></div>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="col-md-12 text-center p-0">
                                                      <div class="card py-2 border">
                                                         <label style="float: left;"><?php echo gettext('Your Amount In')?> <?= $system_currency?>:</label>
                                                         <div id="amt_in_currency"
                                                            style="color: green; font-weight: bold; text-align: center; font-size: 18px;">0 <?= $to_currency?> is equals to 0 <?= $system_currency?></div>
                                                      </div>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="form-group col-md-12 p-0"><label
                                                      class="control-label"><?php echo gettext('Net Payable Amount in')?> <?= $system_currency?>:</label>
                                                      <input type="text" name="amount" readonly id="amount"
                                                         value="0"
                                                         class="form-control form-control-lg col-md-12">
                                                   </td>
                                                </tr>
						 <tr><td><div id="description1"
                                                         style="padding-bottom: 10px; color: red;"></div></td></tr>
                                                <tr style="">
                                                   <td
                                                      class="col-md-12 px-0 form-group h-auto m-0 border-0">
                                                      <label class="control-label"><?php echo gettext('Special Note')?> :</label>
                                                      <textarea class="form-control form-control-lg mit-20"
                                                         name="item_name" id="description" cols="10" rows="1"></textarea>
                                                     
                                                   </td>
                                                </tr>
                                             </table>
                                             <?php }elseif ($logintype == 1 || $logintype == 5 ) {?>
                                             <table class="table col-md-12">
                                                <tr>
                                                   <td class="border-0">
                                                      <div class="col-md-12 form-group p-0">
                                                         <label class="control-label"><?php echo gettext('Please Insert Amount')?> :</label>
                                                         <input type="text" name="gross_amount" value=""
                                                            id="gross_amount"
                                                            class="col-md-12 form-control form-control-lg"> <input
                                                            type="hidden" name="amount" readonly id="amount"
                                                            value="0" class="form-control form-control-lg">
                                                      </div>
                                                      <div id="une"
                                                         style="padding-bottom: 10px; color: red;"></div>
                                                   </td>
                                                </tr>
						<tr><td><div id="description1" style="padding-bottom: 10px; color: red;"></div></td></tr>
                                                <tr>
                                                   <td class="col-md-12 px-0 form-group h-auto border-0">
                                                      <label class="control-label"><?php echo gettext('Special Note')?> :</label>
                                                      <textarea class="form-control form-control-lg mit-20"
                                                         name="item_name" id="description"></textarea>
                                                      
                                                   </td>
                                                </tr>
                                             </table>
                                             <?php } else { ?>
                                             <table class="table col-md-12">
                                                <tr>
                                                   <td class="border-0">
                                                      <div class="col-md-12 form-group p-0">
                                                         <label class="control-label"><?php echo gettext('Please Insert Amount')?> : </label>
                                                         <input type="text" name="amount"
                                                            value="<?php echo $this->common->currency_decimal($this->common_model->calculate_currency(($invoice_info['debit'] - $invoice_info['credit']))); ?>"
                                                            id="amount"
                                                            class="col-md-12 form-control form-control-lg" />
                                                      </div>
                                                      <div id="une"
                                                         style="padding-bottom: 10px; color: red;"></div>
                                                   </td>
                                                </tr>
						<tr><td><div id="description1"
                                                         style="padding-bottom: 10px; color: red;"></div></td></tr>
                                                <tr>
                                                   <td class="col-md-12 px-0 form-group h-auto border-0">
                                                      <label class="control-label"><?php echo gettext('Special Note')?> :</label>
                                                      <textarea class="form-control form-control-lg mit-20"
                                                         name="item_name" id="description"></textarea>
                                                      
                                                   </td>
                                                </tr>
                                             </table>
                                             <?php } ?>
                                          </div>
                                       </td>
                                    </tr>
                              </div>
                              </table>
                           </div>
                     </div>
                     <div class="col-md-12 py-4 text-center">
                     <?php  if ($logintype == 0 || $logintype == 3 ) { ?>
                     <?php
                        if (($logintype == 0 || $logintype == 3) && $accountdata['paypal_permission'] == 0) {
                            ?>
                     <input class="btn btn-success search_generate_bar" name="Paynow"
                        id="Paynow" value="Pay With Paypal" type="button"
                        onclick="return payment(); "> <input id="ok"
                        class="btn btn-secondary ml-2" type="button" value="Cancel"
                        onclick="window.history.back();" ; name="action">
                     <?php } } ?>
                     <?php  if ($logintype == 1 || $logintype == 5 ) { ?>
                      <?php if( (Common_model::$global_config['system_config']['paypal_status'] == '0')){ ?>
                     <input class="btn btn-success search_generate_bar" name="Paynow"
                        id="Paynow" value="Pay With Paypal" type="button"
                        onclick="return payment(); "> <input
                        class="btn btn-success search_generate_bar" name="action"
                        name="save" id="save" value="Pay Now" type="button"
                        onclick="return admin_pay(); ">
                        <?php } ?>
                        <input id="ok"
                        class="btn btn-secondary ml-2" type="button" value="Cancel"
                        onclick="window.history.back();" ; name="action">
                     <?php } ?>
                     <div class="mt-4 text-center">
                     <?php  if ($logintype == '-1' ||$logintype == 2) { ?>
                     <?php if( (Common_model::$global_config['system_config']['paypal_status'] == '0')){ ?>
                     <input class="btn btn-success search_generate_bar" name="action"
                        name="save" id="save" value="Pay Now" type="button"
                        onclick="return admin_pay(); ">
                        <?php } ?>
                        <input id="ok"
                        class="btn btn-secondary ml-2" type="button" value="Cancel"
                        onclick="window.history.back();" name="action">
                     </div>
                     </div>
                     <?php }?>	
                     <div class="msg_error"
                        style="width: 70%; margin-left: auto; margin-right: auto;"></div>
                  </div>
                  </li>
                  <div class="form-group">
                  <input type="hidden" readonly name="paypal_url"
                     value="<?php echo $paypal_url ?>"> <input type="hidden"
                     readonly name="cmd" value="_xclick"> <input type="hidden"
                     readonly name="business" value="<?=$paypal_email_id?>"> <input
                     type="hidden" readonly name="item_number" value="<?=$id?>"> <input
                     type="hidden" readonly name="LC" value="US"> <input
                     type="hidden" readonly name="country" value="USA"> <input
                     type="hidden" readonly name="quantity" value="1"> <input
                     type="hidden" readonly name="rm" value="2"> <input
                     type="hidden" readonly name="no_shipping" value="1"> <input type="hidden" readonly
                     name="currency_code" value="<?php echo $to_currency ?>"> <input
                     type="hidden" readonly name="notify_url"
                     value="<?=$notify_url?>"> <input type="hidden" readonly
                     name="return" value="<?=$response_url?>"> <input type="hidden"
                     readonly name="cancel_return" value="<?=$cancel_return?>"> <input
                     type="hidden" readonly name="custom" id='custom'
                     value="<?php echo $accountdata['id']; ?>"> <input type="hidden"
                     name="new_amount" id="new_amount" readonly
                     value="<?php echo $amount; ?>">
                  </form>
                  </div>
               </div>
               <br />
               <center>
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
<script>
   function payment(){ 
     var text =document.getElementById('amount').value;
     var amount =document.getElementById('new_amount').value;
     var description =document.getElementById('description').value;
     var new_amount=parseFloat(document.getElementById('new_amount').value);
        var total_amount =parseFloat(document.getElementById('amount').value);
	var panding_amount = "<?php echo $invoice_info['debit'] - $invoice_info['credit'] ?>";
     if(text == '' || text <= '0'){
    	 document.getElementById('une').innerHTML = "Please enter amount greater than 0";
    	 payment_form.amount.focus();
     	return(false);
     }else if(total_amount == '0'){
   	 document.getElementById('une').innerHTML = "Please enter amount greater than 0";
     	payment_form.amount.focus();
    	 return(false);
     }
      else if(isNaN(text))
           {  
            document.getElementById('une').innerHTML = "Please only enter numeric characters (Allowed input:0-9).";
         	 admin_form.amount.focus();
        	 return(false);
            
           }
   
     
     else if(panding_amount >= total_amount &&  total_amount > 0 ){  
             $(".search_generate_bar").attr('disabled','disabled');
   	  $('#admin_form').attr('action', "<?php echo base_url();?>user/user_invoice_unpaid_pay/");
             admin_form.submit();
         
       }
         
     else{  
     document.getElementById('description1').innerHTML = "Payment amount should not be higher than the Invoice Amount.";
        admin_form.Paynow.focus();
       return(false);
   
     
     
     }
    
       
   }
   function admin_pay(){
        var text =document.getElementById('amount').value;
        var amount =document.getElementById('new_amount').value;
        var description =document.getElementById('description').value;
        var new_amount= parseFloat(amount);
        var paypal='<?php echo $paypal; ?>';
        var total_amount =  parseFloat(text);
         if(text == '' || text <= '0'){
        document.getElementById('une').innerHTML = "Please Enter Amount greater than zero.";
        admin_form.amount.focus();
        return(false);
        }else if(total_amount == 0){
   	 document.getElementById('une').innerHTML = "Please Enter Amount greater than zero.";
        	admin_form.amount.focus();
        	return(false);
        }
       else if(isNaN(text))
        {
        document.getElementById('une').innerHTML = "Please only enter numeric characters (Allowed input:0-9).";
         admin_form.amount.focus();
        return(false);
        }
   
       else if(new_amount >= total_amount && total_amount >0){
            $(".search_generate_bar").attr('disabled','disabled');
   	$('#admin_form').attr('action', "<?php echo base_url();?>invoices/invoice_admin_payment/");
           admin_form.submit();
       }
       else{
           alert("please enter less amount then your current balance ");
          
       }   
   }
</script>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>

