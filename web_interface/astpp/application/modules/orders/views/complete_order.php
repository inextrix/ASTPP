<? extend('master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        
    });
</script>
<? endblock() ?>

<? startblock('page-title') ?>
<?php echo gettext("Order"); ?> #<?php  echo $order_items['orderid']?>


<? endblock() ?>

<? startblock('content') ?> 
<?php
$accountinfo = $this->session->userdata("accountinfo");
if (astpp_eq0($accountinfo['type']) || $accountinfo['type'] == 3) {
    $order_terminate_url = "/user/user_orders_terminate/";
} else {
    $order_terminate_url = "/orders/orders_terminate/";
}
?>
<section class="slice color-three">
	<div class="w-section inverse p-0">
		<div class="col-12">
			<div class="portlet-content mb-4" id="search_bar"
				style="cursor: pointer; display: none">
                      <?php echo $form_search; ?>
              </div>
		</div>
	</div>
</section>

<?php $url= (astpp_eq0($this->session->userdata ( 'logintype' )) ||$this->session->userdata ( 'logintype' ) == 3) ? "/user/user_invoice_download/" : '/invoices/invoice_download/'; ?>
<section class="slice color-three pb-4">
	<div class="w-section inverse row p-0">

		<div class="col-md-12">
			<div class="card col-md-4 float-left">
				<div id="floating-label" class="row p-4 manage_order">
					<div class="col-lg-6 col-md-6 col-sm-12 p-0">
						<div class="card col-12 p-4 alert-secondary h-100">
							<div class="col-lg-10 col-9 float-left p-0">
								<label class="text-secondary" for=""><?php echo gettext("Date"); ?></label>
				   <?php $order_date = $this->common->convert_GMT_to($date= "",$date ="",$order_items['order_date'],$date = ""); ?>
                                   <h2 class="h4"><?php echo $date = astpp_date("Y-m-d",strtotime($order_date)) ; ?></h2>
							</div>
							<div class="col-lg-2 col-3 float-left p-0">
								<i class="fa fa-calendar-o text-secondary float-left fa-2x"></i>
							</div>
						</div>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12 p-0">
						<div class="card col-12 p-4 alert-secondary h-100">
							<div class="col-lg-10 col-9 float-left p-0">
								<label class="text-secondary" for=""><?php echo gettext("Account"); ?></label>
								<h2 class="h4"><?php echo isset($account_info)? $account_info['first_name']:''?></h2>
							</div>
							<div class="col-lg-2 col-3 float-left p-0">
								<i class="fa fa-user-o text-secondary float-left fa-2x"></i>
							</div>
						</div>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12 p-0">
						<div class="card col-12 p-4 alert-secondary h-100">
							<div class="col-lg-10 col-9 float-left p-0">
								<label class="text-secondary" for=""><?php echo gettext("TOTAL AMOUNT");?></label>
				<?php $total_amt = astpp_n($order_items['setup_fee']) + astpp_n($order_items['price']); ?>
                                   <h2 class="h4"><?php echo (isset($total_amt))?$this->common->convert_to_currency ( '', '', $total_amt ):'0' ?> <?php echo '('.$currency.')';?></h2>
							</div>
							<div class="col-lg-2 col-3 float-left p-0">
								<i class="fa fa-money text-secondary float-left fa-2x"></i>
							</div>
						</div>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12 p-0">
						<div class="card col-12 p-4 alert-secondary h-100">
							<div class="col-lg-10 col-9 float-left p-0">
								<label class="text-secondary" for=""><?php echo gettext("Payment Method"); ?></label>
								<h2 class="h4"><?php echo $order_items['payment_gateway'];?></h2>
							</div>
							<div class="col-lg-2 col-3 float-left p-0">
								<i class="fa fa-credit-card text-secondary float-left fa-2x"></i>
							</div>
						</div>
					</div>

					<div class="col-lg-6 col-md-6 col-sm-12 p-4 card">
						<label class="text-secondary" for=""><?php echo gettext("Order");?> #</label>
						<h2 class="h4"><?php echo $order_items['orderid']?></h2>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12 p-0">
			<?php if(astpp_eq0($account_info['posttoexternal']) && $order_items['payment_status'] != "PENDING"){ ?>
                        <div class="card col-12 p-4">
							<div class="col-lg-10 col-9 float-left p-0">
								<label class="text-secondary" for=""><?php echo gettext("Invoice"); ?> #</label>

			  <?php if(isset($invoice_data)){?>
                           <h2 class="h4"><?php echo  (!empty($invoice_data))?$invoice_data ['prefix'].''.$invoice_data ['number'] :'_';?></h2>
								<div class="col-lg-2 col-3 float-left p-0">
									<a
										href="<?php echo $url.$invoice_data ['id']."/".$invoice_data ['prefix'] . $invoice_data ['number']?>"
										class="" title='Download Invoice'><i
										class='fa fa-cloud-download fa-fw fa-2x'></i></a>
								</div>
			 <?php } ?>
                            </div>

						</div>
					</div>
			<?php } else { ?>
			<?php if(isset($invoice_data)) { ?>
				 <div class="card col-12 p-4">
						<div class="col-lg-10 col-9 float-left p-0">
							<label class="text-secondary" for=""><?php echo gettext("Invoice"); ?> #</label>
							 <h2 class="h4"><?php echo  (!empty($invoice_data))?$invoice_data ['prefix'].''.$invoice_data ['number'] :'-';?></h2>
						<div class="col-lg-2 col-3 float-left p-0">
									<a
										href="<?php echo $url.$invoice_data ['id']."/".$invoice_data ['prefix'] . $invoice_data ['number']?>"
										class="" title='Download Invoice'><i
										class='fa fa-cloud-download fa-fw fa-2x'></i></a>
								</div>
					
						</div>
					</div>
				</div>
			<?php } else { ?>
			  <div class="card col-12 p-4">
						<div class="col-lg-10 col-9 float-left p-0">
							<label class="text-secondary" for=""><?php echo gettext("Invoice"); ?> #</label>
							<h2 class="h4">_</h2>
						</div>
						<div class="col-lg-2 col-3 float-left p-0"></div>
					</div>
				</div>



			<?php } } ?>
                       <div class="col-lg-6 col-md-6 col-sm-12 p-4 card">
					<label class="text-secondary" for=""><?php echo gettext("IP Address"); ?></label>
					<h2 class="h4"><?php echo $order_items['ip'];?></h2>
				</div>
				<div class="col-lg-6 col-md-6 col-sm-12 p-4 card">
					<label class="text-secondary" for=""><?php echo gettext("Payment Status"); ?></label>
					<div>
						<span class="badge badge-success"><?php echo $order_items['payment_status'] ?></span>
					</div>
				</div>
				<?php
			$orderids = $this->db_model->getSelect("type", "accounts", array("id" => $order_items['accountid']))->row_array();
				if (($orderids['type'] != 1 && (!astpp_eq0($order_items['is_terminated']))) || $order_items['product_category'] != 4) {?>
				<div class="col-md-6 mt-4 mx-auto">
			<?php if($order_items['is_terminated'] != 1 ){ ?>	
			<a
						href="<?php echo base_url().$order_terminate_url.$order_items['orderid'];?>"
						rel='facebox'><span class="btn btn-block btn-line-sky"><?php echo gettext("Terminate"); ?></span></a>
			<?php } ?>
                    </div>
					<?php } ?>
			</div>
		</div>

		<div class="col-md-8 float-left p-0 pl-md-4 mt-4 mt-md-0">
			<div id="floating-label" class="p-4 manage_order card">
				<div>
					<div class="row">
						<div class="col-md-6">
							<dl class="border p-3">
							<dt><?php echo gettext("Account Number"); ?></dt>
								<?php echo isset($account_info)? $account_info['number']:''?></dd>
							</dl>
							<dl class="border p-3">
								<dt><?php echo gettext("Product");?></dt>
								<dd><?php  echo isset($product_info)?$product_info['name']:$this->common->get_field_name("name","products",array("id"=>$order_items['product_id'])); ?></dd>
							</dl>
							<dl class="border p-3">
								<dt><?php echo gettext("Registration Date"); ?></dt>
				<?php $order_items['billing_date']=  $this->common->convert_GMT_to($date= "",$date ="",$order_items['billing_date'],$date = "");?>
                                <dd><?php echo $billing_date = astpp_date("Y-m-d",strtotime($order_items['billing_date'])) ; ?></dd>
							</dl>
							 <dl class="border p-3">
                                				<dt><?php echo gettext("Billing Cycle"); ?></dt>
                              					<dd><?php  echo (astpp_eq0($order_items['billing_type'])) ? "One Time" : (( $order_items['billing_type'] == 1 ) ? "Recurring" : "Monthly Recurring"); ?></dd>

                           				 </dl>
							<dl class="border p-3 ">
								<dt><?php echo gettext("Setup Fee"); ?> <?php echo '('.$currency.')';?></dt>
								<dd><?php echo (isset($order_items['setup_fee']))?$this->common->convert_to_currency ( '', '', $order_items['setup_fee'] ):'' ?></dd>
							</dl>
							<dl class="border p-3 ">
								<dt><?php echo gettext("Payment By"); ?> </dt>
				<?php

$order_generate_by = $this->db_model->getSelect("first_name,number", "accounts", array(
        "id" => $order_items['order_generated_by']
    ));
    $order_generate_by = $order_generate_by->result_array()[0];
    ?>
                                <dd><?php  echo $order_generate_by['first_name'] .'('.$order_generate_by['number'] .')' ?></dd>
							</dl>
			 
			
                        </div>
						<div class="col-md-6 pl-4 pt-4 pt-md-0 pl-lg-0">
							<dl class="border p-3">
								<dt><?php echo gettext("Product Category"); ?></dt>
								<dd><?php echo$this->common->get_field_name("name","category",array("id"=>$order_items['product_category'])); ?></dd>
							</dl>
			<?php if(astpp_eq0($order_items['is_terminated']) ){ ?>
							<dl class="border p-3">
								<dt><?php echo gettext("Next Bill Date"); ?></dt>
				<?php $order_items['next_billing_date']=  $this->common->custom_convert_GMT($order_items['next_billing_date'],'');?>

                                <dd><?php echo $next_billing_date = astpp_date("Y-m-d",strtotime($order_items['next_billing_date'])) ; ?></dd>
							</dl>
	<?php } else {?>

	
				<dl class="border p-3 ">
								<dt><?php echo gettext("Termination Date"); ?></dt>
				 <?php $termination_date = $order_items['termination_date']; ?>
                                <dd><?php echo $date = astpp_date("Y-m-d",strtotime($termination_date)) ; ?></dd>
							</dl>
			<?php } ?>
							<dl class="border p-3">
								<dt><?php echo gettext("Billing Days"); ?></dt>
								<dd><?php  echo $order_items['billing_days'] ?></dd>
							</dl>
							<dl class="border p-3">
								<dt><?php echo gettext("Price"); ?> <?php echo '('.$currency.')';?></dt>
								<dd><?php echo (isset($order_items['price']))?$this->common->convert_to_currency ( '', '', $order_items['price'] ):'' ?></dd>
							</dl>
			   <?php if($order_items['product_category'] == 1){ ?>
                            <dl class="border p-3 ">
								<dt><?php echo gettext("Free Minutes"); ?></dt>
								<dd><?php  echo $order_items['free_minutes'] ?></dd>
							</dl>
			<?php } ?>

			<dl class="border p-3 ">
								<dt><?php echo gettext("Product Status"); ?></dt>

				<?php if($order_items['is_terminated'] == 1 ){ ?>
					<dd>
									<span class="badge badge-danger"> <?php echo gettext("Terminated"); ?></span>
								</dd>
				<?php  } else {?>
				<?php if(isset( $product_info['status'])){ ?>
                                <dd>
									<span class="badge badge-success"><?php echo $product_info['status'] == "0"?gettext("Active") : gettext("Inactive") ?></span>
								</dd>
				<?php } } ?>
                            </dl>
				 <?php if($order_items['product_category'] == 2){ ?>
                            <dl class="border p-3 ">
								<dt><?php echo gettext("Quantity"); ?></dt>
								<dd><?php  echo $order_items['quantity'] ?></dd>
							</dl>
			<?php } ?>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<? endblock() ?>	
<? end_extend() ?>  
