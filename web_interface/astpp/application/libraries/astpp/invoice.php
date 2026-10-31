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
class invoice {
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
		$this->CI->load->model ( 'db_model' );
		$this->CI->load->library ( 'email' );
		$this->CI->load->model ( 'common_model' );
		$this->CI->load->library ( 'session' );
	}

	function get_invoice_date($select, $accountid = 0, $reseller_id, $order_by = 'id') {

		$where = array (
				"reseller_id" => $reseller_id
		);
		if ($accountid > 0) {
			$where ['accountid'] = $accountid;
		}
		
		
		$invoice_res = $this->CI->db_model->select ( $select, "invoices", $where, $order_by, "DESC", "1", "0" );

		if ($invoice_res->num_rows () > 0) {
			$invoice_info = ( array ) $invoice_res->first_row ();
			return $invoice_info [$select];
		}

		return false;
	}
	function generate_invoice($accountinfo,$amount,$payment_id) {
		$parent_id = ($accountinfo ['reseller_id'] == 0) ? 1 : $accountinfo ['reseller_id'];
		$where = "accountid IN('".$parent_id."','1')";
		$this->CI->db->select('*');
		$this->CI->db->where($where);
		$this->CI->db->order_by('accountid','desc');
		$this->CI->db->limit(1);
		$invoice_conf = $this->CI->db->get('invoice_conf');
		$invoice_conf = ( array ) $invoice_conf->first_row();

		$last_invoiceid = $this->get_invoice_date ( "number", '', $accountinfo["reseller_id"]);

		if ($last_invoiceid && $last_invoiceid > 0) {
			$last_invoiceid = ($last_invoiceid + 1);

			if ($last_invoiceid < $invoice_conf ['invoice_start_from'])
					$last_invoiceid = $invoice_conf ['invoice_start_from'];	
		} else {
			$last_invoiceid = $invoice_conf ['invoice_start_from'];
		}
		$last_invoiceid = str_pad ( $last_invoiceid, 0 , '0', STR_PAD_LEFT );

		$invoice_prefix = $invoice_conf ['invoice_prefix'];
		$due_date = $invoice_conf ['interval']  > 0 ? date ( "Y-m-d H:i:s", strtotime ( gmdate ( "Y-m-d H:i:s" ) . " +" . $invoice_conf ['interval'] . " days" ) ) : gmdate ( "Y-m-d H:i:s" );
		$invoice_data = array (
				"accountid" => $accountinfo['id'],
				"prefix" => $invoice_prefix,
				"payment_id"=>$payment_id,
				"number" =>  $last_invoiceid,
				"reseller_id" => $accountinfo ['reseller_id'],
				"generate_date" => gmdate ( "Y-m-d H:i:s" ),
				"from_date" => gmdate ( "Y-m-d H:i:s" ),
				"to_date" => gmdate ( "Y-m-d H:i:s" ),
				"due_date" => $due_date,
				"status" => 0,
				"confirm" => '1' 
		);
		$this->CI->db->insert ( "invoices", $invoice_data );
		$invoiceid = $this->CI->db->insert_id ();
		return $invoiceid;
	}
	public function receive_payment($product_info,$account_info,$tax_calculation='',$payment_id,$currency_info='',$invoice_id){
		$debit = "0.00";
		$credit = "0.00";
		$base_currency = Common_model::$global_config ['system_config'] ['base_currency'];
		$bal= $this->CI->common->get_field_name("balance","accounts",array("id"=>$account_info['id']));
		$account_balance = $account_info ['posttoexternal'] == 1 ? ($account_info ['credit_limit'] - $bal) : $bal;
		$total_amt=$product_info['price'];
		$after_balance = $account_balance + $total_amt; 

		if($product_info['invoice_type'] == 'credit'){
			$debit = "0.00";
			$credit = isset($tax_calculation['amount_with_tax'])?$tax_calculation['amount_with_tax']:$product_info['price'];
			$after_balance = $account_balance + $total_amt;
		}else{
			$debit = isset($tax_calculation['amount_with_tax'])?$tax_calculation['amount_with_tax']:$product_info['price'];
			$credit = "0.00";
			$after_balance = $account_balance - $total_amt;
		}
		$insert_arr = array (
				"accountid" =>$account_info['id'],
				"description" =>trim($product_info['description']),
				"created_date" =>gmdate("Y-m-d H:i:s"),
				"invoiceid" => $invoice_id,
				"reseller_id" => $account_info['reseller_id'],
				"is_tax"=>0,
				"order_item_id" =>$product_info['order_item_id'],
				"payment_id"=>$payment_id,
				'before_balance' =>$account_balance,
				'product_category'=>$product_info['product_category'],
				'charge_type'=>$product_info['charge_type'],
				'after_balance'=>$after_balance,
				'base_currency'=>$base_currency,
				'exchange_rate'=>$currency_info['currencyrate'],
				'account_currency'=>$currency_info['currency'],
				'debit'=>$debit,
				'credit'=>$credit
				
		      );

		$this->CI->db->insert("invoice_details",$insert_arr);
		if($product_info['is_update_balance'] == "true"){

			$balance = $this->update_balance ($total_amt, $account_info ['id'],$account_info ['posttoexternal'],$product_info['invoice_type']);

		}
		return $invoice_id;
	}

	public function add_invoice_details($product_info,$account_info,$tax_calculation='',$payment_id,$currency_info=''){
		$is_update_after_balance = "true";
		$debit = "0.00";
		$credit = "0.00";
		$bal= $this->CI->common->get_field_name("balance","accounts",array("id"=>$account_info['id']));
		$base_currency = Common_model::$global_config ['system_config'] ['base_currency'];
		$account_balance = $account_info ['posttoexternal'] == 1 ? $account_info ['credit_limit'] - ($bal) : $bal;
		$product_info['is_update_balance'] = isset($product_info['is_update_balance']) ? $product_info['is_update_balance']:"true";

	
		$total_amt=$product_info['price'];


		if( $account_info ['posttoexternal'] == 0){
			$invoice_id = $this->generate_invoice ($account_info,$total_amt,$payment_id);

		}else{
			// ASTPPCOM-1112_kinjal_start
			$invoice_id = '0';
			// ASTPPCOM-1112_kinjal_end
		}
		
		if($product_info['invoice_type'] == 'credit'){
			$debit = "0.00";
			if(isset($tax_calculation['tax']) && !empty($tax_calculation['tax'])){
				$credit = isset($tax_calculation['amount_with_tax'])?$tax_calculation['amount_with_tax']:$product_info['price'];
			}else{
				$credit = isset($tax_calculation['amount_without_tax'])?$tax_calculation['amount_without_tax']:$product_info['price'];
			}
			$after_balance = $account_balance + $credit;
		}else{
			$debit = isset($tax_calculation['amount_without_tax'])?$tax_calculation['amount_without_tax']:$product_info['price'];
			$credit = "0.00";
			$after_balance = $account_balance - $debit;
		}
		if(isset($product_info['is_update_balance']) && $product_info['is_update_balance'] == "false"){
			$after_balance = $account_balance;
			$is_update_after_balance = "false";
		}
		// Kinjal ASTPPCOM-1319 Start
		$after_balance = str_replace(',','.',$after_balance);
		// Kinjal ASTPPCOM-1319 END
		$insert_arr = array (
				"accountid" =>$account_info['id'],
				"description" =>trim($product_info['description']),
				"created_date" =>gmdate("Y-m-d H:i:s"),
				"invoiceid" => $invoice_id,
				"reseller_id" => $account_info['reseller_id'],
				"is_tax"=>0,
				"order_item_id" => $product_info['order_item_id'],
				"payment_id"=>$payment_id,
				'before_balance' =>$account_balance,
				'product_category'=>$product_info['product_category'],
				'charge_type'=>$product_info['charge_type'],
				'after_balance'=>$after_balance,
				'base_currency'=>$base_currency,
				'exchange_rate'=>isset($currency_info['currencyrate'])?$currency_info['currencyrate']:0,
				'account_currency'=>isset($currency_info['currency'])?$currency_info['currency']:0,
				'debit'=>$debit,
				'credit'=>$credit
				
		      );
		$this->CI->db->insert("invoice_details",$insert_arr);
		if(isset($tax_calculation['tax']) && !empty($tax_calculation['tax'])){
			foreach($tax_calculation['tax'] as $tax_key => $tax){
				$before_balance = $after_balance;
				$after_balance = $after_balance - $tax;
			$tax_insert_arr = array (
						"accountid" =>$account_info['id'],
						"description" =>$tax_key,
						"created_date" =>gmdate("Y-m-d H:i:s"),
						"invoiceid" => $invoice_id,
						"reseller_id" => $account_info['reseller_id'],
						"is_tax"=>1,
						"order_item_id" => $product_info['order_item_id'],
						"payment_id"=>$payment_id,
						'before_balance' =>$before_balance,
						'product_category'=>'0',
						'charge_type'=>"TAX",
						'after_balance'=>($is_update_after_balance == "true")?$after_balance:$account_balance,
						'base_currency'=>$base_currency,
						'exchange_rate'=>$currency_info['currencyrate'],
						'account_currency'=>$currency_info['currency'],
						'debit'=>$tax,
						'credit'=>0
						
				      );
				$this->CI->db->insert("invoice_details",$tax_insert_arr);
			}
		}
		if($product_info['is_update_balance'] == "true"){
			$balance = $this->update_balance ($total_amt, $account_info ['id'],$account_info ['posttoexternal'],$product_info['invoice_type']);

		}

		return $invoice_id;
	}

	function update_balance($amount, $accountid, $payment_type,$invoice_type) {
		// amount and id are concatenated into SQL - numbers only (decimal(20,5))
		$amount = sprintf('%.5F', (float) $amount);
		$accountid = (int) $accountid;
		if($invoice_type == 'credit'){
			  $query = "update accounts set balance =  IF(posttoexternal=1,balance-" . $amount . ",balance+" . $amount . ") where id ='" . $accountid . "'"; 
			return $this->CI->db->query ( $query );
		}else{
			 $query = "update accounts set balance =  IF(posttoexternal=1,balance+" . $amount . ",balance-" . $amount . ") where id ='" . $accountid . "'"; 
			return $this->CI->db->query ( $query );

		}

   }


}

