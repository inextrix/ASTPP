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
class Signup_lib {
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
		$this->CI->load->library ( 'astpp/payment' );
	}
	
	function create_account($accountinfo){
		$current_date = gmdate("Y-m-d H:i:s");
		$invoice_config = "1";
		if ($accountinfo ['type'] == 1) {
			
			$accountinfo['expiry'] = "0000-00-00 00:00:00";
			$invoice_config = $accountinfo ['invoice_config_flag'];
		}
		if($accountinfo['type'] ==0 || $accountinfo['type'] ==3){
			$accountinfo['permission_id'] =0;
		}
		$sip_flag = isset ( $accountinfo ['sip_device_flag'] ) ? $accountinfo ['sip_device_flag'] :Common_model::$global_config ['system_config'] ['create_sipdevice'];
		unset ( $accountinfo ['invoice_config_flag'],$accountinfo ['sip_device_flag'],  $accountinfo ['tax_id'] );
		$accountinfo['posttoexternal']=isset($accountinfo['posttoexternal']) ? $accountinfo['posttoexternal'] : '0';
		$accountinfo = $this->default_signup_configuration($accountinfo,$current_date);
		if ($accountinfo ['type'] == 1) {	
			$accountinfo['expiry'] = "0000-00-00 00:00:00";
		}
		$default_taxes = $accountinfo['tax_id'];
		unset($accountinfo['tax_id']);
		$accountinfo ['creation'] = $current_date;
		$accountinfo ['last_bill_date'] = $current_date; 
		if ($accountinfo ['posttoexternal'] == 0) {
			$accountinfo['credit_limit'] = 0;
		}
		$accountinfo['balance'] = 0;
		$accountinfo=array_map('trim',$accountinfo);
		$result = $this->CI->db->insert ( 'accounts', $accountinfo );
		$last_id = $this->CI->db->insert_id ();
		/*if($accountinfo['type'] ==1){
			$system_arr[0] =array(
				"name"=> "paypal_mode",
				"display_name"=> "Environment",
				"value"=> "1",
				"field_type"=> "paypal_mode",
				"comment"=> "Set paypal mode. Sandbox for testing",
				"timestamp"=> "0000-00-00 00:00:00",
				"reseller_id"=> $last_id,
				"is_display"=> "0",
				"group_title"=> "payment_methods",
				"sub_group"=> "Paypal"
			);
			$system_arr[1] =array(
				"name"=> "paypal_id",
				"display_name"=> "Live Id",
				"value"=> "your@paypal.com",
				"field_type"=> "default_system_input",
				"comment"=> "Set paypal live account id",
				"timestamp"=> "0000-00-00 00:00:00",
				"reseller_id"=> $last_id,
				"is_display"=> "0",
				"group_title"=> "payment_methods",
				"sub_group"=> "Paypal"
			);
			$system_arr[2] =array(
				"name"=> "paypal_status",
				"display_name"=> "Paypal",
				"value"=> "1",
				"field_type"=> "enable_disable_option",
				"comment"=> " Set enable to add paypal as payment gateway option",
				"timestamp"=> "0000-00-00 00:00:00",
				"reseller_id"=> $last_id,
				"is_display"=> "0",
				"group_title"=> "payment_methods",
				"sub_group"=> "Paypal"
			);
			$this->CI->db->insert_batch('system', $system_arr);	
		}*/
		if($accountinfo['type'] == 0 || $accountinfo['type'] ==1 || $accountinfo['type'] ==3 || $accountinfo['type'] ==5){
			$accountinfo['id']=$last_id;
			if(Common_model::$global_config ['system_config'] ['balance'] > 0){
				$balance = $accountinfo ['posttoexternal'] == 0 ? Common_model::$global_config ['system_config'] ['balance']: 0;
				if($accountinfo['posttoexternal'] == 0){
					$this->generate_receipt($accountinfo,$balance);
				}
			}
		
			if($sip_flag == '0' && $accountinfo['type'] != 1) {
				$sip_profile_info = $this->_get_sip_profile();
				if(!empty($sip_profile_info)){
					$this->_create_sip_device($accountinfo,$sip_profile_info);
				}
			}
			$this->account_taxes($default_taxes,$accountinfo['id'],$current_date);
			if(($accountinfo['type']==1 || $accountinfo['type'] ==5) && $invoice_config == '0') 	
				$this->_create_invoice_conf($accountinfo);
			$accountinfo ['confirm'] = base_url ();
			if ($accountinfo ['id'] == "") {
				$accountinfo ['id'] = $last_id;
			}
			if($accountinfo['notifications'] == 0){
				$this->_send_email($accountinfo);
			}
		}
		return $last_id;
	}
	public function account_taxes($taxes,$accountid,$current_date=''){
		$current_date = empty($current_date) ? gmdate("Y-m-d H:i:s") : $current_date;
		$taxes_array=explode(",",$taxes);
		if(!empty($taxes_array)){
			$tax_acc_arr = array();
			foreach($taxes_array as $key=>$value){
				$this->CI->db->select("id,taxes_priority");
				$taxes=(array)$this->CI->db->get_where("taxes",array("id"=>$value))->first_row();
				$tax_acc_arr[]= array(
						"accountid"=>$accountid,
						"taxes_id"=>$value,
						"taxes_priority"=>(!empty($taxes['taxes_priority']))?$taxes['taxes_priority']:'',
						"assign_date"=>$current_date,
				);
			}
			if(!empty($tax_acc_arr)){
				$this->CI->db->insert_batch("taxes_to_accounts",$tax_acc_arr);
			}
		}
	}
	private function _get_sip_profile(){
		$this->CI->db->select ( 'id' );
		$this->CI->db->order_by('id', 'ASC');
		$this->CI->db->limit('1');
		return ( array ) $this->CI->db->get ( 'sip_profiles' )->first_row ();
	}
	private function _create_sip_device($accountinfo,$sip_profile_info){
		$current_date = gmdate("Y-m-d H:i:s");
		$this->CI->db->select ( 'id' );
		$this->CI->db->where ( 'name', 'default' );
		$sipprofile_result = ( array ) $this->CI->db->get ( 'sip_profiles' )->first_row ();
		$digits=5;
		$random_password = random_int((int) pow(10, $digits - 1), (int) pow(10, $digits) - 1);
		$sipdevice_array = array (
				'username' => $accountinfo ['number'],
				'sip_profile_id' => $sip_profile_info ['id'],
				"reseller_id"=>$accountinfo ['reseller_id'],
				'accountid' => $accountinfo['id'],
				'dir_params' => json_encode(array(
					// own random password; the portal password was used, which
					// is no longer readable (and must not be readable in sip_devices either)
					"password"=> $this->CI->common->generate_password (),
					'vm-enabled' => "true",
					"vm-password"=> $random_password,
					"vm-mailto"=> (isset($accountinfo['email']))?$accountinfo['email']:'',
					"vm-attach-file"=>"true",
					"vm-keep-local-after-email"=>"true",
					"vm-email-all-messages"=>"true"
				)),
				"dir_vars"=>json_encode(array(
					'effective_caller_id_name' => $accountinfo ['number'],
					'effective_caller_id_number' => $accountinfo ['number'],
					"user_context"=>"default"
				)),
				'status' => isset($accountinfo ['status']) ? $accountinfo ['status'] : '0',
				'creation_date'=>$current_date,
				'last_modified_date'=>$current_date
		);
		$this->CI->db->insert("sip_devices",$sipdevice_array);
	}
	public function default_signup_configuration($accountinfo,$current_date){
		$accountinfo['local_call_cost'] = Common_model::$global_config ['system_config'] ['charge_per_min'];
		$accountinfo['is_recording'] = Common_model::$global_config ['system_config'] ['is_recording'];
		$accountinfo['notify_credit_limit'] = Common_model::$global_config ['system_config'] ['notify_credit_limit'];
		$accountinfo['allow_ip_management'] = Common_model::$global_config ['system_config'] ['allow_ip_management'];
		$accountinfo['notify_flag']  = Common_model::$global_config ['system_config'] ['notify_flag'];
		$accountinfo['validfordays'] = Common_model::$global_config ['system_config'] ['validfordays'];
		$days=Common_model::$global_config ['system_config'] ['validfordays'];
		$days=$days>0?$days:7300;
		$accountinfo ['expiry'] = gmdate ( 'Y-m-d H:i:s', strtotime ($current_date. '+'.$days.' days' ) );
		$accountinfo['reseller_id'] = isset($accountinfo['reseller_id']) ? $accountinfo['reseller_id'] : 0;
		$accountinfo['generate_invoice'] = isset($accountinfo['generate_invoice']) ? $accountinfo['generate_invoice'] : Common_model::$global_config ['system_config'] ['generate_invoice'];
		$accountinfo['paypal_permission'] = isset($accountinfo['paypal_permission']) ? $accountinfo['paypal_permission'] : Common_model::$global_config ['system_config'] ['paypal_permission'];
		$accountinfo['notifications'] = isset($accountinfo['notifications']) ? $accountinfo['notifications'] :common_model::$global_config ['system_config'] ['notifications'];
		$accountinfo['tax_id'] = common_model::$global_config ['system_config'] ['tax_type'];
		$accountinfo['charge_per_min'] = common_model::$global_config ['system_config'] ['charge_per_min'];
		if(isset($accountinfo['type'])){
		if($accountinfo['type'] == -1 || $accountinfo['type'] == 2){
			$accountinfo['timezone_id'] = common_model::$global_config ['system_config'] ['default_timezone'];
			$currency_id = common_model::$global_config ['system_config'] ['base_currency'];
			$currency_info = (array)$this->CI->db->get_where("currency",array("currency"=>$currency_id))->first_row();
			$accountinfo['currency_id']= $currency_info['id'];
			$accountinfo['country_id'] = common_model::$global_config ['system_config'] ['country'];
		}
		}
		$accountinfo['deleted'] =0;
		$accountinfo['status'] =0;
		return $accountinfo;
	}

	private function _create_invoice_conf($accountinfo){
		if ($accountinfo ['country_id'] == NULL) {
			$accountinfo ['country_id'] = "";
		} else {
			$data = $this->CI->db_model->getSelect ( "country", "countrycode", array (
					"id" => $accountinfo ['country_id'] 
			) );
			$data = $data->result_array ();
			$country_name = $data [0];
		}
		if ($accountinfo ['postal_code'] == NULL) {
			$accountinfo ['postal_code'] = "";
		}
			
		$invoice_config = array (
				'accountid' => $accountinfo['id'],
				'company_name' =>(!empty($accountinfo ['company_name'])) ? $accountinfo ['company_name']:$accountinfo ['first_name'],
				'address' => $accountinfo ['address_1'],
				'city' => $accountinfo ['city'],
				'province' => $accountinfo ['province'],
				'country' => $accountinfo ['country_id'] ,
				'zipcode' => $accountinfo ['postal_code'],
				'telephone' => $accountinfo ['telephone_1'],
				'emailaddress' => $accountinfo ['email'] 
		);
		$this->CI->db->insert ( 'invoice_conf', $invoice_config );
	}
	private function _localization($accountinfo){
		$this->CI->db->select("id,country_id");
		$where=array("country_id"=>$accountinfo['country_id'],"status"=>"0");
		$localization_info = (array)$this->CI->db->get_where("localization",$where)->first_row();
		$accountinfo['localization_id'] =!empty($localization_info) ? $localization_info['id'] : Common_model::$global_config ['system_config'] ['localization_id'];
		return $accountinfo;
	}

	public function generate_receipt($accountinfo,$balance){
			$this->CI->db->select("currency,currencyrate");
			$currency_info = (array)$this->CI->db->get_where("currency",array("id"=>$accountinfo['currency_id']))->first_row();
			
			$payment_arr = array(
				"price"=>  $balance,
				"payment_by"=>"Account Balance",
				"description"=> "Initial Balance",
				'invoice_type'=>"credit",
				"order_item_id"=>"0",
				"product_category"=>"0",
				"charge_type"=> "REFILL",
				"is_update_balance" => "true",
				"is_apply_tax"=>"false"
			);
			$this->CI->payment->add_payments_transcation($payment_arr,$accountinfo,$currency_info);
	}
	private function _send_email($accountinfo){
		// no mail without an address (the insert into mail_details failed with "Column 'to'
		// cannot be null" after the account had already been created)
		if ((! isset ( $accountinfo ['email'] ) || $accountinfo ['email'] == '') && (! isset ( $accountinfo ['notification_email'] ) || $accountinfo ['notification_email'] == '')) {
			return;
		}
		$accountinfo ['confirm'] = base_url ();
		if ($accountinfo ['id'] == "") {
			$accountinfo ['id'] = $last_id;
		}
		// the welcome mail names the account number and links the page where the
		// user sets the password (#PASSWORD#, see common::password_mail_hint())
		$accountinfo ['password'] = '';
		$this->CI->common->mail_to_users ( 'create_account', $accountinfo );
	}
	public function bulk_account_creation($add_array){
		$current_date = gmdate ( "Y-m-d H:i:s" );

		$creation_limit = $this->get_max_limit ( $add_array );
		$count = $add_array ['count'];
		$prefix = $add_array ['prefix'];
		$account_length = $add_array ['account_length'];
		
		$length = strlen ( $prefix );
		$number_length = ($length != 0) ? $account_length - $length : $account_length;

		$numberlength = common_model::$global_config ['system_config']['pinlength'];
		$numberlength=($numberlength < 6 ) ? 6:common_model::$global_config ['system_config']['pinlength'];
		$pin = $add_array ['pin'] ==0  ? $this->CI->common->find_uniq_rendno_accno ($numberlength, 'pin', 'accounts', '', $count ) : '';
		$number = $this->CI->common->find_uniq_rendno_accno ( $number_length, 'number', 'accounts', $prefix, $count );
		$add_array = $this->_localization($add_array);
		
		$default_settings=array();
		$sip_device_flag = isset ( $accountinfo ['sip_device_flag'] ) ? $accountinfo ['sip_device_flag'] :Common_model::$global_config ['system_config'] ['create_sipdevice'];
		unset ( $add_array ['count'],$add_array ['account_length'], $add_array ['prefix']);
		if($sip_device_flag == '0') $sip_profile_info = $this->_get_sip_profile();
		
		$default_settings = $this->default_signup_configuration($default_settings,$current_date);
		$default_taxes = $default_settings['tax_id'];
		$add_array ['expiry'] = gmdate ( 'Y-m-d H:i:s', strtotime ($current_date. '+'.$add_array ['validfordays'].' days' ) );
		for($i = 0; $i < $count; $i ++) {
			$acc_num = $number [$i];
			$email="$acc_num"."@gmail.com";
			
			$insert_array = array();
			$insert_array = $default_settings;
			$tax_id = $insert_array['tax_id'];
			unset($insert_array['tax_id']);
			$insert_array['number'] =$acc_num;
			$insert_array['email']=$email;
			$insert_array['validfordays']=$add_array ['validfordays'];
			$insert_array['first_name']=$acc_num;
			// a random password nobody was told was stored; now a value that
			// matches no password (set it in the account form or with "forgot password")
			$insert_array['password'] =$this->CI->common->password_locked ();
			$insert_array['pricelist_id'] =$add_array ['pricelist_id'];
			$insert_array['reseller_id'] =$add_array ['reseller_id'];
			$insert_array['status'] = "0";
			$insert_array['credit_limit'] = $add_array ['posttoexternal'] == 1 ? $add_array ['credit_limit'] : 0;
			$insert_array['balance'] = $add_array ['posttoexternal'] == 0 ? $add_array ['balance'] : 0;
			$insert_array['currency_id'] =$add_array ['currency_id'];
			$insert_array['country_id'] =$add_array ['country_id'];
			$insert_array['timezone_id'] =$add_array ['timezone_id'];
			$insert_array['local_call']=Common_model::$global_config ['system_config'] ['local_call'];
			$insert_array['type'] =0;
			$insert_array['localization_id'] = $add_array['localization_id'];
			$insert_array['creation'] =$current_date;
			$insert_array['sweep_id'] =$add_array ['sweep_id'];
			$insert_array['invoice_day'] =date ( 'Y', strtotime ( $current_date) );
			$insert_array['expiry'] =$add_array ['expiry'];
			$insert_array['posttoexternal'] = $add_array ['posttoexternal'];
			if ($add_array ['pin'] == 0) {
				$insert_array ['pin'] = $pin [$i];
			}
			$balance=$insert_array['balance'];
			$insert_array['balance']=0;
			$this->CI->db->insert ( 'accounts', $insert_array );
			$last_id = $this->CI->db->insert_id ();
			$insert_array['id'] = $last_id;
			if ($sip_device_flag == '0') {
				$this->_create_sip_device($insert_array,$sip_profile_info);
			}
			if($balance > 0){
				$this->generate_receipt($insert_array,$balance);
			}
			$this->account_taxes($tax_id,$insert_array['id'],$current_date);
		}
	}
	function get_max_limit($add_array) {
		$this->CI->db->where ( 'deleted', '0' );
		$this->CI->db->where ( "length(number)", $add_array ['account_length'] );
		$this->CI->db->like ( 'number', $add_array ['prefix'], 'after' );
		$this->CI->db->select ( "count(id) as count" );
		$this->CI->db->from ( 'accounts' );
		$result = $this->CI->db->get ();
		$result = $result->result_array ();
		$count = $result [0] ['count'];
		$remaining_length = 0;
		$remaining_length = $add_array ['account_length'] - strlen ( $add_array ['prefix'] );
		$currentlength = pow ( 10, $remaining_length );
		$currentlength = $currentlength - $count;
		return $currentlength;
	}
}
?>
