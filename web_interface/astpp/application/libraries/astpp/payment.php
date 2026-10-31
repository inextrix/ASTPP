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
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class payment {
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
		$this->CI->load->model ( 'db_model' );
		$this->CI->load->library ( 'astpp/invoice' );
	}

	public function add_payments_transcation($payment_info,$account_info,$currency_info){ 
		$tax_calculation = '';
		$is_apply_tax = (isset($payment_info['is_apply_tax']) && $payment_info['is_apply_tax'] == "false")?"false":"true";
		 if($account_info ['posttoexternal'] == 0 &&  $is_apply_tax == "true"){
				$tax_calculation=$this->CI->common_model->calculate_taxes($account_info,$payment_info['price']);
			  if($tax_calculation){
				if($payment_info['product_category'] == 3 || (isset($payment_info['add_invoice_credit']) && $payment_info['add_invoice_credit'] == "true")){
					$payment_info['price'] = $payment_info['price'];
				}else{
					$payment_info['price'] = $tax_calculation['amount_with_tax'];
				}
			}
		} 
		$account_balance = $account_info ['posttoexternal'] == 1 ? $account_info ['credit_limit'] - ($account_info ['balance']) : $account_info ['balance'];
		$reseller_id = $account_info['type']== 1 ? $account_info['id'] : 0;
		$total_amt=  isset($tax_calculation['amount_with_tax'])?$tax_calculation['amount_with_tax']:$payment_info['price'];
		$after_balance = $account_balance - $total_amt;
		$insert_payment_arr = array (
					"accountid" => $account_info ['id'],
					"reseller_id"=>$account_info ['reseller_id'],
					"amount" => isset($tax_calculation['amount_without_tax'])?$tax_calculation['amount_without_tax']:$payment_info['price'],
					"tax"=>isset($tax_calculation['total_tax'])?$tax_calculation['total_tax']:0,
					'payment_method' => isset($payment_info['payment_by'])?$payment_info['payment_by']:"Account Balance",
					'actual_amount' => $payment_info['price'],
					"payment_fee" => isset($payment_info['payment_fee'])?$payment_info['payment_fee']:0,
					"user_currency" =>isset($currency_info['currency'])?$currency_info['currency'] : 0,
					"currency_rate" =>isset($currency_info['currencyrate'])?$currency_info['currencyrate']:0,
					"customer_ip"=>$this->getRealIpAddr(),
					"transaction_details"=>json_encode($payment_info),
					"transaction_id"=> (isset($payment_info['transaction_id']) && $payment_info['transaction_id'] != '')?$payment_info['transaction_id']:crc32(uniqid()),
					"date"=>gmdate ( 'Y-m-d H:i:s' )
				 
				     );

		$this->CI->db->insert("payment_transaction",$insert_payment_arr);
		$last_payment_id = $this->CI->db->insert_id();
		$payment_info['invoice_type'] = isset($payment_info['invoice_type'])?$payment_info['invoice_type']:"debit";

		$payment_info['add_invoice_credit'] = isset($payment_info['add_invoice_credit'])?$payment_info['add_invoice_credit']:"false";

		if(isset($payment_info['add_invoice_credit']) && $payment_info['add_invoice_credit'] == "true"){ 
			$invoiceid = $this->CI->invoice->generate_invoice ($account_info,$payment_info['price'],$last_payment_id);
			$payment_info['charge_type'] = "INVPAY";
			$payment_info['description'] = "Payment received from ".$payment_info['payment_by']." for product ".$payment_info['name'];
			$payment_info['invoice_type'] = "credit";
			$invoiceid = $this->CI->invoice->receive_payment($payment_info,$account_info,$tax_calculation,$last_payment_id,$currency_info,$invoiceid);

		}elseif(isset($payment_info['INV_DIRECT_PAY']) && $payment_info['INV_DIRECT_PAY'] == "true"){
			$payment_info['charge_type'] = "INVPAY";
			$payment_info['description'] = "Payment received from ".$payment_info['payment_by']." for product ".$payment_info['name'];
			$payment_info['invoice_type'] = "credit";
			$invoiceid = $this->CI->invoice->receive_payment($payment_info,$account_info,$tax_calculation,$last_payment_id,$currency_info,$payment_info['invoiceid']);
		}else{
			$invoiceid = $this->CI->invoice->add_invoice_details($payment_info,$account_info,$tax_calculation,$last_payment_id,$currency_info);
		}

		return $invoiceid;
	}

	/*
	 * Redeem a refill coupon for an account: only an active coupon (status 0) of the account's own
	 * seller (refill_coupon.reseller_id = accounts.reseller_id, 0 = administrator), only for an
	 * active prepaid account (as the portal shows it), and only once: the coupon is marked as used
	 * in the same UPDATE that checks its status, so two requests at the same time cannot both book
	 * it. Returns 10 (booked), 1 (inactive), 2 (already used), 3 (not found / not of this seller).
	 */
	public function redeem_coupon($number, $account_id) {
		$number = trim((string) $number);
		if ($number === '' || strlen($number) > 30 || ! ctype_digit($number)) {
			return 3;
		}
		$account = $this->CI->db->get_where('accounts', array('id' => (int) $account_id, 'status' => 0, 'deleted' => 0))->row_array();
		if (empty($account) || $account['posttoexternal'] == 1) {
			return 3;
		}
		$coupon = $this->CI->db->get_where('refill_coupon', array('number' => $number, 'reseller_id' => (int) $account['reseller_id']))->row_array();
		if (empty($coupon)) {
			return 3;
		}
		if ($coupon['status'] != 0) {
			return $coupon['status'] == 1 ? 1 : 2;
		}
		$this->CI->db->where(array('id' => $coupon['id'], 'status' => 0));
		$this->CI->db->update('refill_coupon', array('status' => 2, 'account_id' => $account['id'], 'firstused' => gmdate('Y-m-d H:i:s')));
		if ($this->CI->db->affected_rows() != 1) {
			return 2;
		}
		$payment_info = array(
			"price" => $coupon['amount'],
			"payment_by" => "Voucher",
			"payment_fee" => 0,
			"invoice_type" => "credit",
			"product_category" => 3,
			"description" => "Refill done using voucher (" . $number . ")",
			"order_item_id" => 0,
			"charge_type" => "Voucher",
			"is_apply_tax" => "true"
		);
		$currency_info = (array) $this->CI->db->get_where('currency', array('id' => $account['currency_id']))->row_array();
		$this->add_payments_transcation($payment_info, $account, $currency_info);
		return 10;
	}

	function getRealIpAddr(){
			    if (!empty($_SERVER['HTTP_CLIENT_IP']))   
			    {
			      $ip=$_SERVER['HTTP_CLIENT_IP'];
			    }
			    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   
			    {
			      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
			    }
			    elseif( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '')
			    {
			      $ip=$_SERVER['REMOTE_ADDR'];
			    }else{
				$ip= getHostByName(getHostName());
			    }
			    return $ip;
	 }
}
