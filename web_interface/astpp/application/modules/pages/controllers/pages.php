<?php
// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
//
// Copyright (C) 2016 iNextrix Technologies Pvt. Ltd.
// Samir Doshi <samir.doshi@inextrix.com>
// ASTPP Version 3.0 and above
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################

class Pages extends MX_Controller {
	
	function __construct() {

		parent::__construct ();
			$this->load->library ( 'astpp/payment');
			$this->load->library ( 'astpp/order');
			$this->load->library('session');
			$this->load->library ('ASTPP_Sms');
	}

	
function services($category= '') {
			$post=$this->input->post();
			$accountinfo=$this->session->userdata ( "accountinfo" );
			$account_info = $this->session->userdata ( 'token' );
			$accountinfo = ((isset($account_info)) && $account_info != '')?$account_info:$this->session->userdata ( "accountinfo" );
			$category = ($category== "")?1:$category;
			$query = "select * from category  where code <> 'DID' and code <> 'REFILL' order by FIELD(id, '3', '1', '2')";
			$product_category = $this->db->query($query);
		if($product_category->num_rows > 0){
			$data['product_category'] = $product_category->result_array();
			
			if($accountinfo['reseller_id'] > 0 && $category != 3 ){ 
				if(empty($post['country_id'])){
					$temp_where = '(`reseller_products`.`is_optin` = 0 OR `reseller_products`.`is_owner` = 0)';
				}else{
					$temp_where = '((`reseller_products`.`country_id` = '.(int)$post['country_id'].' ) AND (`reseller_products`.`is_optin` = 0 OR `reseller_products`.`is_owner` = 0))';
				}
				$this->db->where($temp_where);
				$productdata = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.country_id,products.buy_cost,products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('reseller_products.status'=>0,'products.can_purchase'=>0,'products.is_deleted'=>0,'products.product_category'=>$category,'reseller_products.account_id'=>$accountinfo['reseller_id']), 'reseller_products','products.id=reseller_products.product_id', 'inner','','','desc','products.id');
			}else{
				if(empty($post['country_id'])){
					$productdata = $this->db_model->select("*","products",array("product_category"=>$category,'status'=>0,'can_purchase'=>0,'is_deleted'=>0,'reseller_id'=>0),"id","desc","","");
				}else{
					$productdata = $this->db_model->select("*","products",array("product_category"=>$category,'status'=>0,'can_purchase'=>0,'is_deleted'=>0,'reseller_id'=>0,'country_id'=>$post['country_id']),"id","desc","","");
				}
			}
			if($productdata->num_rows > 0){
				$data['productdata'] = $productdata->result_array();
			}else{
				if($category == '1'){
					$data['product_msg']= gettext("Not Avaialable any Package for this Country.");
				}else{
					$data['product_msg']=  gettext("Not Avaialable any Subscription.");
				}
			}
			if(!empty($post['country_id'] != 0)){
				$data["country_id"] = $post['country_id'];
			}
			$data['country'] = $this->db_model->build_dropdown("id,country", "countrycode","","");
			$data['currency']= $this->common->get_field_name("currency","currency",array("id"=>$accountinfo['currency_id']));
			$data['category']=$category;
			$this->load->view( 'view_products',$data);
		}
	}


	function topup_reseller($category= '') {
		$data ['page_title'] = gettext ( 'TopUp' );
		$accountinfo = ((isset($accountinfo)) && $accountinfo != '')?$accountinfo:$this->session->userdata ( "accountinfo" );
		$this->db->select('*');
		$this->db->from('products');
		$this->db->where('product_category','3');
		$this->db->where('is_deleted','0');
		$this->db->where('status','0');
		$this->db->where('can_purchase','0');
		$productdata=$this->db->get();

		if($productdata->num_rows > 0){
			$data['productdata'] = $productdata->result_array();
			foreach ($productdata->result_array() as $key => $value) {
				$currency = $this->common->get_field_name ( 'currency', 'currency', $accountinfo ["currency_id"] );
				$data['productdata'][$key]['price']=number_format($this->common_model->calculate_currency ( $value['price'], "", '', false, false ), 4) . ' '.$currency;
			}
			
		}
		$result = $this->db_model->getSelect("*","accounts",array("reseller_id"=> $accountinfo['id']));
		$data['accountid'] = $accountinfo['id'];
		$data["to_currency"] = Common_model::$global_config['system_config']['base_currency'];
		$data['set_public_key'] ='pk_test_c63if3gfDqr85VUGi5cH966x007jbP7PwU';
		$this->load->view( 'view_products_reseller',$data);
	}
	

	
	function checkout($id){  
		
		if (!$this->session->userdata('accountinfo'))
		{
			redirect ( base_url ());
		}else{
			$account_info = $this->session->userdata ( 'token' );
			$account_info = ((isset($account_info)) && $account_info != '')?$account_info:$this->session->userdata ( "accountinfo" );
			$productarr =  $this->order->get_product_info($account_info,$id);
			if(!isset($productarr['error'])){
				$data['page_title']=gettext("Product Information");
				$data['product_info'] = $productarr;
				$data['ewallet_payment'] = common_model::$global_config ['system_config']['ewallet_payment'];
				$data['account_info'] = $account_info;
				$data['product_info']['setup_price']=($data['product_info']['price'] + $data['product_info']['setup_fee']);
				$this->load->view( 'view_checkout',$data);

			}else{

				$this->session->set_flashdata ( 'astpp_notification',  gettext('Something went worong !') );
							redirect ( base_url () . 'pages/services' );

			}
		}

		

	}
	private function reload_live_freeswitch($command)
	{
		$response = '';
		$query = $this->db_model->getSelect("*", "freeswich_servers", "");
		$fs_data = $query->result_array();
		$this->load->library('freeswitch_lib');
		foreach ($fs_data as $fs_key => $fs_value) {
		    $fp = $this->freeswitch_lib->event_socket_create($fs_value["freeswitch_host"], $fs_value["freeswitch_port"], $fs_value["freeswitch_password"]);
		    if ($fp) {
			$response .= $this->freeswitch_lib->event_socket_request($fp, $command);
			fclose($fp);
		    }
		}
		$response = str_replace("0 total.", "", $response);
		return $response;
	}
	private function check_calls_running(){
		 $accountinfo = $this->session->userdata('accountinfo');
		 $command = "api show channels";
		 
		 $response = $this->reload_live_freeswitch($command);
		 $calls = array();
		 $calls_final = array();
		 $data_header = array();
		 $k = 0;
		 $data = explode("\n", $response);
		 for ($i = 0; $i < count($data) - 2; $i ++) {
		     if (trim($data[$i]) != '') {
		         if (count($data_header) == 0 || substr($data[$i], 0, 4) == "uuid") {
		             $data_header = explode(",", $data[$i]);
		         } else {
		             $bridge_str = "";
		             $string = " " . $data[$i];
		             $ini = strpos($string, '[');
		             if ($ini != 0) {
		                 $ini += strlen('[');
		                 $len = strpos($string, ']', $ini) - $ini;
		                 $bridge_str = substr($string, $ini, $len);
		             }
		             if ($bridge_str != '') {
		                 $new_bridge_str = str_replace(',', '--', $bridge_str);
		                 $data[$i] = str_replace($bridge_str, $new_bridge_str, $data[$i]);
		             }
		             // HP:END.
		             $data_call = explode(",", $data[$i]);
		             for ($j = 0; $j < count($data_call); $j ++) {
		                 $calls[$k][@$data_header[$j]] = @$data_call[$j];
				 $calls_final[@$calls[$k]['uuid']] = @$calls[$k];
		             }
		             $k ++;
		         }
		     }
		 }
		 $json_data = array();
		 $count = 0;
		 $query = $this->db_model->select("username", "sip_devices", array(
		        'status' => 0,
		        'accountid'=>$accountinfo['id']), "id", "desc", "10", "");
			$sip_device_array=$query->result_array();
			
			$sip_device_final_array=array();
			foreach ($sip_device_array as $key => $value) {
				$sip_device_final_array[$value['username']] =$value['username'];
			}
			$response_return =true;
            if(!empty($calls)){
			
		 foreach ($calls as $key => $value) {
			 $calls[$i]['presence_data'] = @$calls_final[$value['call_uuid']]['presence_data'];
                	     $livecall_data = explode("|||", $calls[$i]['presence_data']);
		         $account_explode=explode('(',$livecall_data[1]);	
		         if (isset($value['state']) && ($value['state'] == 'CS_EXCHANGE_MEDIA' || $value['state'] == 'CS_CONSUME_MEDIA') ) {
				 if($accountinfo['number'] == rtrim($account_explode[1],')') || array_key_exists($value['initial_dest'],$sip_device_final_array)){
		             $response_return=false;
		 		}
		         } 
		        }
		 }
		return $response_return;
	}
	function proceed_payment($productid){
		$accountinfo = $this->session->userdata ( "accountinfo" );
		if(isset($productid) && $productid != '' && is_numeric($productid))
		{
		$account_info = $this->session->userdata ( 'token' );
		
		$account_info = ((isset($account_info)) && $account_info != '')?$account_info:$this->session->userdata ( "accountinfo" );
		$category_type =$this->common->get_field_name("product_category","products",array("id"=>$productid)); 
		if(($accountinfo['posttoexternal'] != '1') || (($category_type != 3))){  
		if($category_type == 3){
			$productinfo =  $this->order->get_parent_product_info($productid);
		}else{
			$productinfo =  $this->order->get_product_info($account_info,$productid);
		}
	
		if(!isset($productinfo['error'])){	
			$payment_method = isset($_POST['pay_from_account']) ? $_POST['pay_from_account'] : '';
			// whole pieces only (0.001 or 1e9 were accepted and charged price x quantity)
			$quantity = order::quantity(isset($_POST['product_quantity']) ? $_POST['product_quantity'] : '');
			if ($quantity === false) {
				$this->session->set_flashdata ( 'astpp_notification',  gettext('Invalid quantity!'));
				redirect ( base_url () . 'pages/services/'.$productinfo['product_category'].'' );
				exit;
			}
			$product_info = array();
			if($payment_method == "account_balance" && $productinfo['product_category'] != 3){  		
				$product_info['is_parent_billing'] = true;
				$product_info['product_id'] = $productid;
				$user_info =$this->db_model->getSelect("posttoexternal,balance,credit_limit","accounts",array("id"=>$account_info["id"])); 
			 	$user_info = (array)$user_info->first_row();
				$account_balance = $user_info ['posttoexternal'] == 1 ? $user_info ['credit_limit'] - ($user_info ['balance']) : $user_info ['balance'];

				$total_amt = (($productinfo['price'] + $productinfo['setup_fee'])*$quantity);
				if($account_info ['type'] == 0 && (!$this->check_calls_running())){
					$this->session->set_flashdata('astpp_notification', gettext('You can not purchase any new product when your calls are running!'));
					redirect ( base_url () . 'pages/services/'.$productinfo['product_category'].'' );
					exit;
				}

				if( $account_balance  >= $total_amt ){ 
					
					$product_info['invoice_type'] = ($productinfo['product_category'] == 3) ? "credit":"debit";
					$product_info['payment_by'] =  gettext("Account Balance");
					$product_info['category_name'] = $this->common->get_field_name("name","category",array("id"=>$productinfo['product_category']));
					$product_info['product_name'] = $this->common->get_field_name("name","products",array("id"=>$productinfo['id']));

					$product_info['next_billing_date']=($productinfo['billing_days'] == 0)?gmdate('Y-m-d 23:59:59', strtotime('+10 years')):gmdate("Y-m-d 23:59:59",strtotime("+".$productinfo['billing_days']." days"));
					$product_info['quantity'] = $quantity;	
					$order_id = $this->order->confirm_order($product_info,$account_info['id'],$account_info);
					
					if($order_id != ""){
						$product_info['price']=$productinfo['price'];	
						$final_array = array_merge($account_info,$productinfo);
						// Kinjal ASTPPENT-1028 Start
						$final_array['last_id'] = $account_info['id'];
						// Kinjal ASTPPENT-1028 END
						$final_array['quantity']=$quantity;
						$final_array['total_price']=($productinfo['setup_fee']+$productinfo['price'])*($final_array['quantity']);
						$final_array['price']=($productinfo['setup_fee']+$productinfo['price']);
						$final_array['name']=$product_info['product_name'];
						$final_array['category_name']=$product_info['category_name'];
						$final_array['next_billing_date']=$product_info['next_billing_date'];
						$final_array['payment_by']=$product_info['payment_by'];
						$this->common->mail_to_users ('product_purchase', $final_array );
						$this->session->set_flashdata ( 'astpp_errormsg',  gettext('Product Purchased successfully  !' ));
						redirect ( base_url () . 'pages/services/'.$productinfo['product_category'].'' );
					}
				}else{
					
					$this->session->set_flashdata ( 'astpp_notification',  gettext('Insufficent Balance to purchase product  !' ));
					redirect ( base_url () . 'pages/services/'.$productinfo['product_category'].'' );
			        }
			}else{
				// PayPal only while it is active for the receiving account (a pending order was created
				// also with PayPal switched off)
				$this->load->library('astpp/paypal');
				$paypal_owner = (array) $this->db->get_where("accounts",array("id"=>$account_info['id']))->first_row();
				if ($this->paypal->for_account($paypal_owner) === null) {
					$this->session->set_flashdata ( 'astpp_notification',  gettext('PayPal is not available.' ));
					redirect ( base_url () . 'pages/services/'.$productinfo['product_category'].'' );
					exit;
				}
				$productinfo['product_id'] = $productid;
				$productinfo['payment_by'] = "Paypal";
				$productinfo['quantity'] = $quantity;
				$productinfo['invoice_type'] = ($productinfo['product_category'] == 3) ? "credit":"debit";
				$accountid = $account_info['id'];

				$oreder_req_result = $this->order->confirm_product_order($productinfo,$accountid);
				if(!empty($oreder_req_result)){
			      		$this->refill_auto_form($oreder_req_result,$productinfo,$accountid);
				}else{
					$this->session->set_flashdata ( 'astpp_notification',  gettext('Something went worong !' ));
					redirect ( base_url () . 'pages/services/'.$productinfo['product_category'].'' );

				}
		      }
			}else{
				$this->session->set_flashdata ( 'astpp_notification',  gettext('Something went worong !'));
				redirect ( base_url () . 'pages/services' );
			}
		 }else{
			$this->session->set_flashdata ( 'astpp_notification',  gettext('Something went worong !'));
			redirect ( base_url () . 'pages/services' );
		  }
		}else{
				if($accountinfo['type'] == '0' || $accountinfo['type'] == '3'){
					$this->session->set_flashdata('astpp_danger_alert', gettext('Permission Denied!'));
					redirect(base_url() . 'user/user/');
				}else{
					$this->session->set_flashdata('astpp_danger_alert', gettext('Permission Denied!'));
					redirect(base_url() . 'dashboard/');
				}
		  }
	}
	
	function refill_auto_form($oreder_req_result,$product_info,$accountid){ 
		echo '<html>' . "\n";
		echo '<head><title>Processing Payment...</title></head>' . "\n";
		echo '<body class="col-md-12" style="text-align:center;" onLoad="document.forms[\'paypal_auto_form\'].submit();">' . "\n";
		
		
		echo '<div class="col-md-12 text-center order_completed no-padding " style="min-height:580px;">  
	    <section class="content col-md-6 text-center" style="padding-top: 150px;">';    
		 echo '<h3 style="text-align:center;line-height:35px;">Please wait, your order is being processed and you will be redirected to the paypal website.</h3>' . "\n";

		echo $this->oerder_request_form($oreder_req_result,$product_info,$accountid);
		  echo '</section>
	    </div>';
	       
		echo '</body></html>';
    }

    // Jaimin ASTPPCOM-737
    // Amount (incl. taxes and PayPal fee), currency and receiver are stored in paypal_payments;
    // the form only carries the token of that row as item_number.
    function oerder_request_form($oreder_req_result,$product_info,$accountid){
		$accountinfo=(array)$this->db->get_where("accounts",array("id"=>$accountid))->first_row();
		$this->load->library('astpp/paypal');
		$settings = $this->paypal->for_account($accountinfo);
		$amt = ($product_info['price'] + $product_info['setup_fee']) * $product_info['quantity'];
		$amount_with_tax = $this->common_model->calculate_taxes($accountinfo,$amt);
		$total = ($amount_with_tax != '') ? $amount_with_tax['amount_with_tax'] : $amt;
		$row = null;
		if ($settings !== null) {
			$total = $total + ($total * $settings['fee'] / 100);
			$row = $this->paypal->create($accountinfo, 'order', $oreder_req_result, $total, $amt, $settings);
		}
		if ($row === null) {
			$this->session->set_flashdata ( 'astpp_notification',  gettext('PayPal is not available.' ));
			redirect ( base_url () . 'pages/services/' );
			exit;
		}
		$data = $this->paypal->form_data($row, $settings, isset($product_info['name']) ? $product_info['name'] : 'Product');
		$this->load->view( 'paypal_redirect',$data);
    }
// END


    function get_product_info(){
		$accountinfo = $this->session->userdata ( 'token' );
		$accountinfo = ((isset($accountinfo)) && $accountinfo != '')?$accountinfo:$this->session->userdata ( "accountinfo" );
    	if (!empty($_POST['id'])) {
			
	    	$this->db->select('*');
			$this->db->from('products');
			$this->db->where('product_category','3');
			$this->db->where('id',$_POST['id']);
			$productdata     = $this->db->get();
			$productdata     = (array)$productdata->first_row();
			$account_info    = $this->db_model->getSelect("*","accounts",array("id"=>$accountinfo['id']));
			$account_info    = $account_info->result_array();
			$account_info    = $account_info ['0'];
			$productdata['price']=$this->common_model->calculate_currency ( $productdata['price'], "", '', false, false );
			
			$tax_calculation = $this->common_model->calculate_taxes($account_info,$productdata['price']);
			
			$currency = $this->common->get_field_name ( 'currency', 'currency', $accountinfo ["currency_id"] );
			$paypal_fee = $this->common->get_field_name('value','system',array("name" => "paypal_fee"));
	 		if($paypal_fee == 0){
	 			$paypal_tax = $this->common->get_field_name('value','system',array("name" => "paypal_tax"));
	 		}else{
	 			$paypal_tax = '';
	 		}
			if (!empty($tax_calculation) && isset($tax_calculation)){
				$tax_calculation['amount_without_tax']=$this->common->currency_decimal($tax_calculation['amount_without_tax']).' '.$currency;
				$tax_calculation['amount_with_tax']=$this->common->currency_decimal($tax_calculation['amount_with_tax']).' '.$currency;
				$tax_calculation['total_tax']=$this->common->currency_decimal($tax_calculation['total_tax']).' '.$currency;
				$tax_calculation['paypal_taxes'] = $paypal_tax;
				echo json_encode($tax_calculation);
	
			} else {
				
				$tax_calculation ['amount_without_tax'] =$productdata['price'].' '.$currency;
				$tax_calculation ['amount_with_tax']    = $productdata['price'].' '.$currency;
				$tax_calculation ['total_tax']          = 0 .' '.$currency;
				$tax_calculation['paypal_taxes'] = $paypal_tax;
				echo json_encode($tax_calculation);	
			}
    	}
    }

	/*
	 * PayPal IPN (notify_url of every PayPal form). Payments used to be booked from the POST data
	 * alone (also the browser return, also with PayPal switched off). Now:
	 *  - no active PayPal account at all: 404, nothing is read or written;
	 *  - only POST; the notification is verified with PayPal and against the pending payment
	 *    (paypal::ipn: amount, currency, receiver, status Completed, txn_id once) before booking;
	 *  - the browser comes back to pages/paypal_return, which books nothing.
	 */
	function paypal_response() {
		if ($this->db->get_where('system', array('name' => 'paypal_status', 'value' => '0'))->num_rows() === 0
			|| $this->input->server('REQUEST_METHOD') !== 'POST') {
			show_404();
		}
		$this->load->library('astpp/paypal');
		$result = $this->paypal->ipn(file_get_contents('php://input'));
		if (is_string($result)) {
			log_message('error', 'PayPal IPN not booked: ' . $result);
			echo 'IGNORED';
			exit();
		}
		$this->book_paypal($result['row'], $result['ipn']);
		log_message('error', 'PayPal IPN booked: payment ' . (int) $result['row']['id'] . ', account ' . (int) $result['row']['accountid'] . ', ' . $result['row']['kind']);
		echo 'OK';
		exit();
	}

	/** Browser return from PayPal: message only, the IPN books the payment. */
	function paypal_return() {
		$this->session->set_flashdata ( 'astpp_errormsg',  gettext('Thank you. The payment is booked as soon as PayPal confirms it.' ));
		$type = (string) $this->session->userdata('userlevel_logintype');
		redirect ( base_url () . (($type === '0' || $type === '3') ? 'user/user/' : 'dashboard/') );
	}

	/** Book a verified and claimed PayPal payment (paypal_payments row). */
	private function book_paypal($row, $ipn) {
		$account_info = (array) $this->db->get_where('accounts', array('id' => $row['accountid']))->row_array();
		$currency_info = (array) $this->db->get_where('currency', array('id' => $account_info['currency_id']))->row_array();
		$txn_id = $row['txn_id'];
		$orderarr = ($row['kind'] === 'order') ? $this->order->get_order_details($row['ref_id']) : false;
		if ($row['kind'] === 'invoice') {
			$payment_array = array(
				"is_apply_tax" => "false",
				'payment_by' => "Paypal",
				'price' => $row['credit'],
				"payment_fee" => $row['amount'] - $row['credit'],
				"invoiceid" => $row['ref_id'],
				"order_item_id" => 0,
				"charge_type" => "INVPAY",
				"product_category" => 0,
				"transaction_id" => $txn_id,
				"description" => "Invoice payment via PayPal",
				'invoice_type' => "debit",
				'INV_DIRECT_PAY' => "true",
				'name' => "Invoice Payment",
				'is_update_balance' => "true"
			);
			$this->payment->add_payments_transcation($payment_array, $account_info, $currency_info);
			return;
		}
		if ($row['kind'] === 'topup' || empty($orderarr)) {
			// top-up, or an order that is no longer pending (PaymentStatus marks it FAIL after
			// 30 minutes): the customer has paid, so the amount is credited to the balance
			$payment_info = array(
				"price" => $row['credit'],
				"payment_by" => "Paypal",
				"payment_fee" => $row['amount'] - $row['credit'],
				"invoice_type" => "credit",
				"product_category" => 3,
				"description" => ($row['kind'] === 'topup') ? "Refill via PayPal" : "Refill via PayPal (order " . (int) $row['ref_id'] . " was no longer pending)",
				"order_item_id" => 0,
				"charge_type" => "REFILL",
				"is_apply_tax" => "false",
				"transaction_id" => $txn_id
			);
			$this->payment->add_payments_transcation($payment_info, $account_info, $currency_info);
			return;
		}
		// product order: charges of the reseller chain, invoice and payment as before, then the
		// pending order items of the chain become active
		$product_name = $this->common->get_field_name("name","products",array("id"=>$orderarr['product_id']));
		$orderarr['transaction_id'] = $txn_id;
		$orderarr['name'] = $product_name;
		$orderarr['payment_by'] = "Paypal";
		$orderarr['charge_type'] = $this->common->get_field_name("code","category",array("id"=>$orderarr['product_category']));
		$chain = array((int) $orderarr['id']);
		for ($p = (int) $orderarr['parent_order_id']; $p > 0 && count($chain) < 20 && ! in_array($p, $chain, true); ) {
			$chain[] = $p;
			$p = (int) $this->common->get_field_name("parent_order_id","orders",array("id"=>$p));
		}
		if($orderarr['parent_order_id'] > 0){
			$temp_arr['description'] = "Payment has been received from customer ".$account_info['first_name']." ( ".$account_info['number']." )";
			$temp_arr['payment_by'] = "Paypal";
			$temp_arr['transaction_id'] = $txn_id;
			$temp_arr['name'] = $product_name;
			$temp_arr['charge_type']=$orderarr['charge_type'];
			$this->order->update_order_status($orderarr,$orderarr['parent_order_id'],$temp_arr,'PAID',$account_info);
		}
		$orderarr['invoice_type'] = ($orderarr['product_category'] == 3) ? "credit":"debit";
		$quantity = ($orderarr['quantity'] > 0) ? $orderarr['quantity'] : 1;
		$price=$orderarr['price']/$quantity;
		$setupfee=$orderarr['setup_fee']/$quantity;
		$orderarr['price']=$orderarr['price']+$orderarr['setup_fee'];
		$orderarr['description'] = ($orderarr['product_category'] == 3) ? "Refill Done success":" (".$orderarr['name'].") has been added.";
		$invoiceid =$this->payment->add_payments_transcation($orderarr,$account_info,$currency_info);
		if($invoiceid != ''){
			if($orderarr['product_category'] != 3 && $account_info['posttoexternal'] == 0){
				$orderarr['INV_DIRECT_PAY'] = "true";
				$orderarr['invoiceid'] = $invoiceid;
				$orderarr['is_update_balance'] = "true";
				$orderarr['payment_by'] = "Paypal";
				$orderarr['description'] =  "Refill Done success";
				$this->payment->add_payments_transcation($orderarr,$account_info,$currency_info);
			}
		}
		$this->db_model->update ( "orders",array("payment_status"=>"PAID"),array("id"=>$orderarr['id']));
		$this->db->where_in('order_id', $chain);
		$this->db->update('order_items', array('is_terminated' => 0, 'termination_date' => '0000-00-00 00:00:00', 'termination_note' => ''));
		$orderarr['category_name']=$this->common->get_field_name("name","category",array("id"=>$orderarr['product_category']));
		$orderarr['price']=$price;
		$orderarr['next_billing_date']=($orderarr['billing_days'] == 0)?gmdate('Y-m-d 23:59:59', strtotime('+10 years')):gmdate("Y-m-d 23:59:59",strtotime("+".$orderarr['billing_days']." days"));
		$final_array = array_merge($account_info,$orderarr);
		$final_array['total_price']=($price+$setupfee)*($orderarr['quantity']);
		$final_array['price']=($price+$setupfee);
		$this->common->mail_to_users ('product_purchase', $final_array );
	}

	function payment_proceed($id){ 
		$post_values = $this->input->post();
		$accountinfo = $this->session->userdata ( 'token' );
		if ($post_values ['another_card'] == '1'){
			$post_values ['defaultcard'] = '1';
			unset($post_values ['another_card']);
		}else {
			$post_values ['defaultcard'] = '0';
			unset($post_values ['another_card']);
		}
		if(!empty($post_values)){
		$account_info = $this->db_model->getSelect("*","accounts",array("id"=>$accountinfo['id']));
		if($account_info->num_rows > 0) { 
				$account_info = $account_info->result_array()[0];
				$tax_calculation=$this->common_model->calculate_taxes($account_info,$post_values['product_price']);
				$currency_info = $this->db_model->getSelect("*","currency",array("id"=>$account_info['currency_id']));

				if($currency_info->num_rows > 0){
					$currency_info = (array)$currency_info->result_array();
					$currency_info = $currency_info[0];
				}
				$refill_product_info =  $this->db_model->getSelect("*","products",array("id"=>$id));
				if($refill_product_info->num_rows > 0){
					$refill_product_info = $refill_product_info->result_array();
					$refill_product_info= $refill_product_info[0];

				}
				$post_values['order_id'] = $this->order->confirm_product_order($id,$accountinfo['id']);	
			 }
				$post_values['product_price'] = $tax_calculation['amount_with_tax'];

				$response =  $this->authorizenet->authorize_customer_profile_save($post_values,$account_info);
			 	if($response['status'] == 'success') {  
			 		$response1 = $this->authorizenet->user_authorize_connection($post_values,$account_info);

					if($response1['status'] == 'success'){
						$refill_product_info['order_item_id'] = $post_values['order_id'];
						$refill_product_info['payment_method'] = "Authorize";
						$invoiceid =$this->payment->add_payments_transcation($refill_product_info,$account_info,$tax_calculation,$currency_info);
					    $this->session->set_flashdata ( 'astpp_errormsg',  gettext('Product assigned successfully!' ));

					redirect ( base_url () . 'pages/services' );	
					}
				 }
				redirect ( base_url () . '' );
		}

	}

	
	function products_purchase(){
		$accountinfo = $this->session->userdata ( 'token' );
		$reseller_id = 0 ;
		$query = " SELECT * FROM `products` where reseller_id =$reseller_id and id NOT IN(select product_id from order_items where accountid=".$accountinfo['id'].")";

		$product_data = array ();
		$product_data = $this->db->query ( $query );
		if($product_data->num_rows > 0){
			$data['product_info'] = $product_data->result_array();
			$this->load->view("view_products_purchase",$data);
		}
		
	}
	function refill_coupon_add_view() {
		$accountinfo = $this->session->userdata ("accountinfo");
		 $data['login_type']=$accountinfo['type'];
		$data['accountid']=$accountinfo['id'];
		$this->load->view ( 'view_add_refill_coupon',$data);
	}
	function user_refill_coupon_number($refill_coupon_no,$accountid = '') {
		// only active coupons of the own seller, only once (payment::redeem_coupon()); always the
		// logged-in account (the account id in the URL is ignored, it is kept for the existing links)
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$result = $this->payment->redeem_coupon($refill_coupon_no, isset($accountinfo['id']) ? $accountinfo['id'] : 0);
		echo json_encode ( $result );
		if ($result == 10) {
			$this->session->set_flashdata ( 'astpp_errormsg',  gettext('Refill Coupon amount is Added Successfully' ));
		}
	}

			
	


}
?>
