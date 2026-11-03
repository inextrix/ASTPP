<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
/**
 * ****************************************************************
 * IMPORTANT!! : This is API for Customer CURD Operation : IMPORTANT!!
 * ****************************************************************
 *
 * ===================================================
 * API Expected parameters :
 * ===================================================
 * Integer : start_limit (Start limit for customer list)
 * Integer : end_limit (End limit for customer list)
 * Integer : accountid (Unique accountid for each customer)
 * String : first_name (Customer name)
 * Integer : number (Unique account number for each customer)
 * String : email (Unique email id for each customer)
 * Integer : country_id (Unique Country id)
 * Integer : timezone_id (Unique Timezome id)
 * Integer : currency_id (Unique Currency id)
 * Integer : pricelist_id (Rategroup id)
 *
 * ===================================================
 * API Possible actions : create,read,update,delete,list,batch_update
 * ===================================================
 * mandatory fields;
 * create : number,email,first_name
 * read : accountid
 * update : accountid
 * delete : accountid
 * list : end_limit
 * batch_update :
 *
 * ===================================================
 * API URL
 * ===================================================
 * For Index : https://sparkz.service.astppbilling.org:8085/admin/customer/
 */

require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Customer extends Account {
	
	protected $postdata = "";
	protected $accountinfo = "";
	function __construct() {
		parent::__construct ();
		$this->load->model ( 'common_model' );
		$this->load->library ( 'common' );
		$this->load->model ( 'db_model' );
		$this->load->library('Form_validation');
		$this->accountinfo = $this->get_account_info(); 
		if($this->accountinfo['type'] != -1 && $this->accountinfo ['type'] != 1 && $this->accountinfo ['type'] != 2 ){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'error_invalid_key' )
			), 400 );
		}
		$rawinfo = $this->post ();
		$this->postdata = array();
		foreach ( $rawinfo as $key => $value ) {
				$this->postdata [$key] = $this->_xss_clean ( $value, TRUE );
		}
		$this->postdata ['client_ip'] = $_SERVER['SERVER_ADDR'];
		$this->reseller_allow = "true";
	}
	public function index() {
		$function = isset ( $this->postdata ['action'] ) ? $this->postdata ['action'] : '';
		$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
		$this->api_log->write_log ( 'Params : ', json_encode($this->postdata) );
		$accountid = $this->postdata ['id'];
		$where = array('id'=>$accountid,'status'=>0);
		if($this->accountinfo['type'] == -1 || $this->accountinfo['type'] == 2){
			$this->db->where_in('type',array(2,-1));
		}else{
			$where = array('reseller_id' => $this->accountinfo['id'] , 'type' => 0);
		}
		$this->db->where($where);
		$accountinfo = (array)$this->db->get('accounts')->first_row();
		if(empty($accountinfo) || !isset($accountinfo)){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'account_not_found' )
			), 400 );
		}
		$accountinfo = $this->_authorize_account ( $this->accountinfo,true,true);
		if ($function != '') {
			$function = '_' . $function;
			if (astpp_gt(( int ) method_exists ( $this, $function ), 0)) {
				$this->$function ();
			} else {
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'unknown_method' )
				), 400 );
			}
		} else {
			$this->response ( array (
				'status'=> false,
				'error' => $this->lang->line ( 'unknown_method' )
			), 400 );
		}
	}
	
	function _customer_list() {
		if (!isset($this->postdata['end_limit']) || !isset($this->postdata['start_limit'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_param_missing' ) . " integer:end_limit,integer:start_limit"
			), 400 );
		}else{
			if(astpp_le($this->postdata['start_limit'], 0) || astpp_le($this->postdata['end_limit'], 0)) {
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('number_greater_zero')
				), 400 );
			}

			if(!($this->postdata['start_limit'] < $this->postdata['end_limit'])){
				$this->response ( array (
						'status' => false,
						'error' => $this->lang->line('valid_start_limit')
				), 400 );
			}
			$start = astpp_n($this->postdata['start_limit'])-1;
			$limit = $this->postdata['end_limit'];
			$object_where_params = $this->postdata['object_where_params'];
			if($object_where_params['type'] != ''){
				if (!($object_where_params['type'] == '0' || $object_where_params['type'] == '3'  ) ) {
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('valid_type')
				), 400 );
				}
			}
			$currency_id = $this->common->get_field_name('currency','currency', array('id' => $this->accountinfo['currency_id']));
			$where = '';
			foreach($object_where_params as $object_where_key => $object_where_value) {
				if($object_where_value != '') {	
					$where = $object_where_key . ' = "' . $object_where_value . '" AND ';
					if(!empty($where)) {
						$where = rtrim($where,"AND ");
						$this->db->where($where);
					}
				}
			}
			
			$no_of_records = (int)$limit - (int)$start;
			if($this->accountinfo['type'] == -1 || $this->accountinfo['type'] == 2){
				$this->db->where_in('type',array(0,3));
			}else{
				$where = array('reseller_id' => $this->accountinfo['id'] , 'type' => 0);
				$this->db->where($where);
			}
			$query = $this->db->limit($no_of_records, $start)
				->order_by('id','desc')
				->select('id,number,first_name,timezone_id,last_name,company_name,reseller_id,pricelist_id,posttoexternal,currency_id,balance,credit_limit,first_used,expiry,localization_id,creation,status,permission_id')
				->where('deleted','0')
				->get ('accounts');
			$count = $query->num_rows();
			$query = $query->result_array();
			if($count > 0) {
				foreach($query as $key=>$value) {
					$value['accountid'] = $value['id'];
					$value['reseller_id'] = $value['reseller_id'] != null ? $value['reseller_id'] : '0';
					$value['pricelist_id'] = $this->common->get_field_name('name','pricelists',array('id' => $value['pricelist_id']));
					$value['localization_id'] = $this->common->get_field_name('name','localization',array('id' => $value['localization_id']));
					$value['creation'] = $this->common->convert_GMT_to('','',$value['creation'],$this->accountinfo['timezone_id']);
					$value['expiry'] = $this->common->convert_GMT_to('','',$value['expiry'],$this->accountinfo['timezone_id']);
					$value['balance'] = $this->common_model->to_calculate_currency($value['balance'],'',$currency_id);
					$value['posttoexternal'] = astpp_eq0($value['posttoexternal']) ? "Prepaid" : "Postpaid";
					unset($value['timezone_id']);
					$value['permission_id'] = $this->common->get_field_name('name','permissions',array('id' => $value['permission_id']));
					$insert_array = $this->_token($value['id'],"e",$value);
					$value['token'] = $insert_array['token'];
					unset($value['id']);
					$new_array[] = $value;
				}
					$this->response ( array (
						'total_count'=>$count,
						'data' => $new_array,
						'success' => $this->lang->line( "customer_list_information" )
					), 200 );
				} else {
					$this->response ( array (
						'total_count'=>0,
						'data' => $new_array,
						'success' => $this->lang->line("no_records_found")
					), 200 );
				}
			}
	}
	
	function _customer_read() {
		$postdata = $this->postdata;
		$currency_id = $this->common->get_field_name('currency' ,'currency' , array('id' => $this->accountinfo['currency_id']) );
		if (empty($postdata['accountid']) || !isset($postdata['accountid'])) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_param_missing' ) . " integer:accountid"
			), 400 );
		}else{
			$where = array('id' => $postdata['accountid'],'deleted'=>0,'status'=>0);
			$this->db->select('*');
			if($this->accountinfo['type'] == -1 || $this->accountinfo['type'] == 2){
				$this->db->where_in('type',array(0,3));
			}else{
				$where = array('reseller_id' => $postdata['id'],'id' => $postdata['accountid'],'deleted'=>0,'status'=>0);
			}
			$this->db->where($where);
			$accountinfo = (array)$this->db->get("accounts")->first_row();
	       	if(empty($accountinfo)){
				$this->response ( array (
					'status'  => false,
					'error'   => $this->lang->line ( 'account_not_found' )
				), 400 );
	        }

			$this->db->select('taxes_id');
			$this->db->where('accountid',$accountinfo['id']);
			$tax_id = (array)$this->db->get('taxes_to_accounts')->first_row();
			$read_array = array(
				"accountid"     => $accountinfo['id'],
				"number"        => $accountinfo['number'],
				"reseller_id"   => $accountinfo['reseller_id'],
				"pin"	        => $accountinfo['pin'],
				"email"	        => $accountinfo['email'],
				"first_used"	=> $this->common->convert_GMT_to('','',$accountinfo['first_used'],$this->accountinfo['timezone_id']),
				"validfordays"	=> $accountinfo['validfordays'],
				"expiry"	=> $this->common->convert_GMT_to('','',$accountinfo['expiry'],$this->accountinfo['timezone_id']),
				"status"	=> $accountinfo['status'],
				"maxchannels"   => $accountinfo['maxchannels'],
				"cps"		=> $accountinfo['cps'],
				"creation"   => $this->common->convert_GMT_to('','',$accountinfo['creation'],$this->accountinfo['timezone_id']),
				"balance"   => $this->common_model->to_calculate_currency($accountinfo['balance'],'',$currency_id),
				"localization_id"	=> $accountinfo['localization_id'],
				"local_call"		=> $accountinfo['local_call'],
				"is_recording"		=> $accountinfo['is_recording'],
				"allow_ip_management"	=> $accountinfo['allow_ip_management'],
				"notifications"		=> $accountinfo['notifications'],
				"paypal_permission"	=> $accountinfo['paypal_permission'],
				"first_name"		=> $accountinfo['first_name'],
				"last_name"		=> $accountinfo['last_name'],
				"company_name"		=> $accountinfo['company_name'],
				"telephone_1"		=> $accountinfo['telephone_1'],
				"telephone_2"		=> $accountinfo['telephone_2'],
				"notification_email"    => $accountinfo['notification_email'],
				"address_1"		=> $accountinfo['address_1'],
				"address_2"		=> $accountinfo['address_2'],
				"city"			=> $accountinfo['city'],
				"province"		=> $accountinfo['province'],
				"notify_credit_limit"   => $accountinfo['notify_credit_limit'],
				"notify_flag"   => $accountinfo['notify_flag'],
				"notify_email"   => $accountinfo['notify_email'],
				"language_id"   => $accountinfo['language_id'],
				"invoice_day"   => $accountinfo['invoice_day'],
				"invoice_interval"   => $accountinfo['invoice_interval'],
				"invoice_note"   => $accountinfo['invoice_note'],
				"local_call_cost"   => $accountinfo['local_call_cost'],
				"charge_per_min"   => $accountinfo['charge_per_min'],
				"postal_code"		=> $accountinfo['postal_code'],
				"country_id"		=> $accountinfo['country_id'],
				"timezone_id"		=> $accountinfo['timezone_id'],
				"currency_id"		=> $accountinfo['currency_id'],
				"posttoexternal"	=> $accountinfo['posttoexternal'],
				"credit_limit"		=> $accountinfo['credit_limit'],
				"pricelist_id"		=> $accountinfo['pricelist_id'],
				"sweep_id"		=> $accountinfo['sweep_id'],
				"tax_id"		=> $tax_id['taxes_id'],
				"tax_number"		=> $accountinfo['tax_number'],
				"invoice_note"		=> $accountinfo['invoice_note'],
				"reference"		=> $accountinfo['reference']
			);
			$this->response ( array (
				'status'=>true,
				'data' => $read_array,
				'success' => $this->lang->line( "read_customer" ) 
			), 200 );
		}
	}

	function _customer_create() {
			$postdata = $this->postdata;
			if($this->accountinfo ['type'] == 1){
				$postdata['reseller_id'] = $this->accountinfo ['id'];
			}else{
				if ($postdata['reseller_id'] == '' || astpp_eq0($postdata['reseller_id'])) {
					$postdata['reseller_id'] = 0;
				}else{
					if(astpp_gt($postdata['reseller_id'], 0)){
						$postdata['reseller_id'] = $this->common->get_field_name('id','accounts',array('id' => $postdata['reseller_id'],'type' => 1,'deleted'=> 0));
					}
					if($postdata['reseller_id'] == ''){
						$this->response ( array (
							'status' => false,
							'error' => $this->lang->line('valid_reseller_id')
						),400 );
					}
				}
			}
	        
        	if($this->form_validation->required($postdata['first_name'] ) == ''){
					$this->response ( array (
						'status' => false,
						'error' => $this->lang->line ( 'enter_first_name' ) 
					), 400 );
			}
			if (empty($postdata['email']) || $postdata['email'] == '' ) {
	        	$this->response ( array (
					'status'  => false,
					'error'    => $this->lang->line ( 'require_email' )
				), 400 );
			}else{
				if (!filter_var($postdata['email'], FILTER_VALIDATE_EMAIL)) {
					$this->response ( array (
						'status' => false,
						'error' => $this->lang->line('invalid_email_format')
					), 400 );
				}
				$this->db->where('deleted',0);
				$this->db->where(array("email"=> $postdata['email']));
				$check_email = $this->db->get('accounts')->num_rows();
				if(astpp_gt($check_email, 0)) {
					$this->response ( array (
						'status' => false,
						'error'  => $this->lang->line ( 'email_already_used' )
					), 400 );
				}
			}
        	if($this->accountinfo['type'] == 1){
				if($this->form_validation->required($postdata['telephone_1'] ) == ''){
					$this->response ( array (
						'status' => false,
						'error' => $this->lang->line ( 'enter_telephone' ) 
					), 400 );
				}
				if($postdata['currency_id'] == '' || !isset($postdata['currency_id'])){
					$postdata['currency_id'] =  $this->common->get_field_name('id','currency', array('currency' => 'USD'));
				}else{
					$postdata['currency_id'] = $this->common->get_field_name('id','currency',array('id'=>$postdata['currency_id']));
					if(empty($postdata['currency_id'])){
						$this->response ( array (
							'status' => false,
							'error' => $this->lang->line('enter_currency')
						), 400 );
					}
				}

				if($postdata['country_id'] == '' || !isset($postdata['country_id'])){
					$postdata['country_id'] =  $this->common->get_field_name('id','countrycode', array('country' => 'INDIA'));
				}else{
					$postdata['country_id'] = $this->common->get_field_name('id','countrycode',array('id'=>$postdata['country_id']));
					if(empty($postdata['country_id'])){
						$this->response ( array (
							'status' => false,
							'error' => $this->lang->line('valid_country_id')
						), 400 );
					}
				}

				if($postdata['timezone_id'] == '' || !isset($postdata['timezone_id'])){
					$postdata['timezone_id'] =  $this->common->get_field_name('id','timezone', array('timezone_name' => 'Europe/Sarajevo'));
				}else{
					$postdata['timezone_id'] = $this->common->get_field_name('id','timezone',array('id'=>$postdata['timezone_id']));
					if(empty($postdata['timezone_id'])){
						$this->response ( array (
							'status' => false,
							'error' => $this->lang->line('valid_timezone_id')
						), 400 );
					}
				}			
				if(!is_numeric($postdata['balance']) && $postdata['balance'] != '' ){
					$this->response ( array (
						'status'  => false,
						'error'   => $this->lang->line ( 'valid_balance' )
					), 400 );
				}
				if($postdata['pricelist_id'] == '' || !isset($postdata['pricelist_id'])){
					if($this->form_validation->required($postdata['pricelist_id'])== ''){
						if(empty($postdata['pricelist_id'])){
							$this->response ( array (
								'status' => false,
								'error' => $this->lang->line('require_pricelist_id')
							), 400 );
						}
					}
				}else{
					$postdata['pricelist_id'] = $this->common->get_field_name('id','pricelists',array('id'=>$postdata['pricelist_id'], 'reseller_id' => $postdata['id']));
				}
				if(empty($postdata['pricelist_id'] ) ){
					$this->response ( array (
						'status' => false,
						'error' => $this->lang->line('valid_pricelist_id')
					), 400 );
				}
			}
        	if($this->form_validation->alpha($postdata['city']) != '1' && $postdata['city'] != ''){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('enter_city')
				), 400 );
			}
			if($this->form_validation->alpha($postdata['province']) != '1' && $postdata['province'] != ''){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('enter_province')
				), 400 );
			}
	       
			$config = common_model::$global_config['system_config']['card_length'];
			if($config != ''){
				$uname = $this->common->find_uniq_rendno_customer(common_model::$global_config['system_config']['card_length'], 'number ', 'accounts');	
			}else{
				$uname = $this->common->find_uniq_rendno_customer(10, 'number ', 'accounts');
			}
			if(!is_numeric($postdata['postal_code']) && $postdata['postal_code'] != ''){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('enter_postal_code')
				), 400 );
			}
			if(!is_numeric($postdata['telephone_1']) && $postdata['telephone_1'] != ''){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('enter_valid_telephone')
				), 400 );
			}

			$pin_generate = Common_model::$global_config['system_config']['generate_pin'];
	        if (astpp_eq0($pin_generate) ) {
				$pin = (common_model::$global_config['system_config']['pin_length'] < 6) ? 6 : common_model::$global_config['system_config']['pin_length'];
		        $pin_number = $this->common->find_uniq_rendno_customer($pin, 'number', 'accounts');
		    }
			if($postdata['password'] == ''){
				$postdata['password'] = $this->common->generate_password();
			}
			$encoded_password = $this->common->encode($postdata['password'] );
			if($postdata['sweep_id'] == '' || $postdata['sweep_id'] >= '3' || $postdata['sweep_id'] == '1' ){
				$sweep_id = 2;
			}else{
				$sweep_id =  $postdata['sweep_id'];
			}	
			if(isset($postdata['notify_email']) && !empty($postdata['notify_email']) && (!filter_var($postdata['notify_email'], FILTER_VALIDATE_EMAIL))){
					$this->response ( array (
						'status' => false,
						'error' => $this->lang->line('invalid_email_format')
					), 400 );
			}
			$current_date = gmdate("Y-m-d H:i:s");
				if(!($postdata['invoice_day'] == -1 || $postdata['invoice_day'] <=  28)){
					$postdata['invoice_day'] = 1;
				}
			if($this->accountinfo['type'] != '1'){
				$currency_id = common_model::$global_config ['system_config'] ['base_currency']; 
				$currency_info = (array)$this->db->get_where("currency",array("currency"=>$currency_id))->first_row();
				$coutry_id = common_model::$global_config ['system_config'] ['country'];
				$timezone_id =  common_model::$global_config ['system_config'] ['default_timezone'];
				$localization_id = Common_model::$global_config['system_config']['localization_id'];
				if(empty($postdata['pricelist_id'])){
					$pricelist_id = Common_model::$global_config['system_config']['default_signup_rategroup'];
				}else{
					$pricelist_id = $this->common->get_field_name('id','pricelists',array('id'=>$postdata['pricelist_id'], 'reseller_id' => $postdata['reseller_id']));
					if(empty($pricelist_id)){
						$this->response ( array (
							'status' => false,
							'error' => $this->lang->line('rategroup_not_found')
						), 400 );
					}
				}
			}
		if(!(astpp_eq0($postdata['posttoexternal']) || $postdata['posttoexternal'] == 1) ) {
			$postdata['posttoexternal'] = '0';
		}
		if(!is_numeric($postdata['credit_limit']) && $postdata['credit_limit'] != ''){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'valid_credit_limit' )
			), 400 );
		}	
		if(!is_numeric($postdata['tax_number']) && $postdata['tax_number'] != ''){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'valid_tax_id' )
			), 400 );
		}
		$type = $this->postdata['action'] == 'provider_create' ? '3' : '0';
		$postdata['notification_email'] = $postdata['action'] == 'provider_create' ? $postdata['email'] : $postdata['notification_email'];
		$currency_id = common_model::$global_config ['system_config'] ['base_currency']; 
		$currency_info = (array)$this->db->get_where("currency",array("currency"=>$currency_id))->first_row();
		$insert_array = array(
			"number" => $uname,
			"password" => $encoded_password,
			"email" => $postdata['email'],
			"pin"	=> $pin_number,
			"currency_id" => isset($currency_info['id'])? $currency_info['id']:$postdata['currency_id'] ,
			"type" => $type,
			"country_id" => isset($coutry_id) ? $coutry_id : $postdata['country_id'],
			"timezone_id" => isset($timezone_id) ? $timezone_id : $postdata['timezone_id'],
			"cps" => Common_model::$global_config['system_config']['cps'],
			"reseller_id" =>$postdata['reseller_id'] ,
			"company_name"=> isset($postdata['company_name'])?$postdata['company_name']:'',
			"first_name"  => $postdata['first_name'],
			"last_name"  => isset($postdata['last_name'])?$postdata['last_name']:'',
			"telephone_1"  => isset($postdata['telephone_1'])?$postdata['telephone_1']:'',
			"maxchannels" => Common_model::$global_config['system_config']['maxchannels'],
			"permission_id" => isset($permission_id) ? $permission_id : $postdata['permission_id'],
			"local_call" => Common_model::$global_config['system_config']['local_call'],
			"localization_id" => isset($localization_id) ? $localization_id : '', 
			"is_distributor" => isset($postdata['is_distributor'])?$postdata['is_distributor']:'1',
			"std_cid_translation" => isset($postdata['std_cid_translation'])?$postdata['std_cid_translation']:'',
			"did_cid_translation" => isset($postdata['did_cid_translation'])?$postdata['did_cid_translation']:'',
			"address_1" => isset($postdata['address_1'])?$postdata['address_1']:'',
			"address_2" => isset($postdata['address_2'])?$postdata['address_2']:'',
			"city" => isset($postdata['city'])?$postdata['city']:'',
			"province" => isset($postdata['province'])?$postdata['province']:'',
			"postal_code" => isset($postdata['postal_code'])?$postdata['postal_code']:'',
			"posttoexternal" => isset($postdata['posttoexternal'])?$postdata['posttoexternal']:'1',
			"credit_limit" => isset($postdata['credit_limit'])?$postdata['credit_limit']:'',
			"sweep_id" => isset($sweep_id)?$sweep_id:'',
			"language_id" => isset($postdata['language_id'])?$postdata['language_id']:'0',
			"dialed_modify" => isset($postdata['dialed_modify'])?$postdata['dialed_modify']:'',
			"invoice_interval" => isset($postdata['invoice_interval'])?$postdata['invoice_interval']:'0',
			"invoice_note" => isset($postdata['invoice_note'])?$postdata['invoice_note']:'',
			"generate_invoice" => Common_model::$global_config['system_config']['generate_invoice'],
			"deleted" => isset($postdata['deleted'])?$postdata['deleted']:'0',
			"notification_email" => isset($postdata['notification_email'])?$postdata['notification_email']:'',
			"last_bill_date" =>  "0000-00-00 00:00:00",
			"inuse" => isset($postdata['inuse'])?$postdata['inuse']:'0',
			"invoice_day" => isset($postdata['invoice_day'])?$postdata['invoice_day']:'1',
			"commission_rate" => isset($postdata['commission_rate'])?$postdata['commission_rate']:'0',
			"reference" => isset($postdata['reference'])?$postdata['reference']:'',
			"notifications" => Common_model::$global_config['system_config']['notifications'],
			"is_recording" => Common_model::$global_config['system_config']['is_recording'],
			"charge_per_min" => Common_model::$global_config['system_config']['charge_per_min'],
			"paypal_permission" => Common_model::$global_config['system_config']['paypal_permission'],
			"notify_flag" => Common_model::$global_config['system_config']['notify_flag'],
			"tax_number" => isset($postdata['tax_number'])?$postdata['tax_number']:'',
			"pricelist_id" => isset($pricelist_id) ? $pricelist_id : $postdata['pricelist_id'],
			"invoice_note" => isset($postdata['invoice_note'])?$postdata['invoice_note']:'',
			"creation" => gmdate('Y-m-d H:i:s'),
			"first_used" => "0000-00-00 00:00:00"
		);
		$this->load->library("astpp/signup_lib");
		$last_id = $this->signup_lib->create_account($insert_array);
		$accountinfo = $this->db_model->getSelect("*",'accounts', array('id' => $last_id))->row_array();
		if(!empty($accountinfo)) {
			if($postdata['action'] != 'provider_create'){
				$this->response ( array (
					'status'=> true,
					'data' => $accountinfo,
					'success' => $this->lang->line('create_customer_success') 
				), 200 );
			}else{
				$this->response ( array (
					'status'=> true,
					'data' => $accountinfo,
					'success' => $this->lang->line('create_provider_success') 
				), 200 );
			}
			
		} else {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('something_wrong_contact_admin')
			), 400 );
		}
	}

	function _customer_delete() {
		$postdata = $this->postdata;
		if($this->form_validation->required($postdata['accountid'] ) == ''){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_account_id' ) 
			), 400 );
		}else{
			if(!$this->form_validation->numeric_with_comma($postdata['accountid'])){
				$this->response ( array (
					'status' => false,
					'success' =>  $this->lang->line ('enter_valid_accountid')  
				), 400 );
			}
			if($this->accountinfo['type'] == 1){
				$this->db->where('reseller_id' , $postdata['id']);
				$this->db->where_in('id',$postdata['accountid']);
			}else{
				$this->db->where_in('id',$postdata['accountid']);
			}
			$accountinfo = $this->db->get('accounts')->result_array();
		        if(empty($accountinfo)){
				$this->response ( array (
					'status'  => false,
					'error'   => $this->lang->line ( 'account_not_found' )
				), 400 );
	        	}
	        	$where = array('type' => 0);
				if($this->accountinfo['type'] == 1){
					$where['reseller_id'] = $this->accountinfo['id'];
				}
				$this->db->where($where);
				$this->db->where("id IN (".$postdata['accountid'].") ");
				$delete_user_info = $this->db->update("accounts", array("deleted" => "1" ) );
				$last_edit_id = $this->db->affected_rows();
				if(astpp_gt($last_edit_id, 0)){
					$this->db->where("accountid IN (".$postdata['accountid'].") ");
					$delete_sip_info = $this->db->delete("sip_devices" , '');
				}
				if(astpp_eq0($last_edit_id)){
					$this->response ( array (
					'status'  => false,
					'error'   => $this->lang->line ( 'account_not_found' )
					), 400 );
				}else{
					$this->response ( array (
					'status'=> true,
					'success' => $this->lang->line('account_deleted') 
				), 200 );
				}
		}
	}
}	
?>
