<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
/**
 * ****************************************************************
 * IMPORTANT!! : This is API belongs to Accounts only : IMPORTANT!!
 * ****************************************************************
 *
 * ===================================================
 * API Expected parameters :
 * ===================================================
 * Integer : id
 * String : token
 * String : action (To call profile_update, profile_password_change. Expected value : update, password_chnage)
 * JSON : object_update_params FORMAT : "{\"first_name\":\"Samir Doshi\",\"last_name\":\"Samir Doshi\"}",
 * String : username
 * String : old_password
 * String : password
 * String : object (If you want to update account password as well then set value account)
 *
 * ===================================================
 * API URL
 * ===================================================
 * For Balance : http://192.168.1.2:8081/api/account/balance
 * For Profile : http://192.168.1.2:8081/api/account/profile
 */
/**
 *
 * @todo :
 *       Confirm validation and response codes
 *       Expection handing for query fail case
 */
/**
 * Included accounts api controller as that is having all functions belogs and accounts and we are validating account for each api request.
 */
require APPPATH . '/libraries/API_Controller.php';
#[\AllowDynamicProperties]
class Account extends API_Controller {
	/**
	 * Define blank variable to get post value
	 */
	protected $postdata = "";
	function __construct() {
		parent::__construct ();
		$this->load->model ( 'common_model' );
		
		$this->load->library ( 'common' );
		
		$this->load->model ( 'db_model' );
		$rawinfo = $this->post ();
		$this->postdata = array();
		
		foreach ( $rawinfo as $key => $value ) {
			$this->postdata [$key] = $this->_xss_clean ( $value, TRUE );
		}
		
		if (isset ( $this->postdata ['id'] )) {
			$token_id = isset ( $this->postdata ['id'] ) ? $this->_token ( $this->postdata ['id'], 'e' ) : '';
			if ($this->postdata ['token'] != $token_id) {
				$this->api_log->write_log ( 'info',' ID ' . $this->postdata['id'].' TOKEN_ID ' . $token_id);
		 		$this->response ( array (
		 			'status' => false,
		 			'error' => $this->lang->line ( 'error_invalid_key' ) 
		 		), 400 );
		 	}
		}
		$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
		$this->api_log->write_log ( 'Params : ', json_encode($this->postdata) );
	}
	// END


	/**
	 * ================================================================================================
	 * Public function to call using URL - START
	 * ================================================================================================
	 */
	
	/**
	 * Balance
	 *
	 * Get customer balance
	 *
	 * @param
	 *    integer id
	 */

	public function get_account_info(){
		$decoded_token =  $this->_token ( $this->postdata ['token'], 'd' );
		if($decoded_token != '' ){
			$account_info=(array)$this->db->get_where('accounts',array('id'=> $decoded_token))->first_row();
			return $account_info;
		}
	}
	public function balance() {
		$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
		$this->api_log->write_log ( 'Params : ', json_encode($this->postdata) );
		if (! isset ( $this->postdata ['id'] )) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_param_missing' ) . "integer:id" 
			), 400 );
		}
		$query = "SELECT ACC.id as accountid,ACC.credit_limit,ACC.posttoexternal,ACC.balance,(SELECT currency from currency where id=ACC.currency_id) as currency_code,currency_id,ACC.timezone_id,ACC.first_used,ACC.expiry,ACC.status,ACC.deleted FROM accounts AS ACC WHERE ACC.type = 0 AND id = '" . $this->postdata ['id'] . "' limit 1";
		$this->api_log->write_log ( 'info', $query );
		$account_info = (array)$this->db->query ( $query )->first_row();
		if (!empty($account_info)) {
			$account_info = $this->_authorize_account ( $account_info );
			$this->response ( array (
				'status' => true,
				'data' => $account_info
			), 200 );
		} else {
			$this->api_log->write_log ('ERROR',"The server encountered an unexpected condition which prevented it from fulfilling the request.");
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_500' ) 
			), 500 );
		}
	}

	public function profile($accid = '', $fields = 'id', $balance = true, $credit_limit = true) {
		$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
		$this->api_log->write_log ( 'Params : ', json_encode($this->postdata) );
		$function = isset ( $this->postdata ['action'] ) ? $this->postdata ['action'] : '';
		if ($function != '') {
			$function = '_profile_' . $function;
			if (astpp_gt(( int ) method_exists ( $this, $function ), 0)) {
				$this->$function ();
			} else {
				$this->api_log->write_log ('ERROR',"Unknown Method.");
				$this->response ( array (
					'status'=> false,
					'error' => $this->lang->line ( 'unknown_method' ) 
				), 400 );
			}
			exit ();
		}
		if ($accid == '') {
			$fields = $this->postdata ['object_params'];
			if ($fields == '') {
				$this->response ( array (
					'status'=> false,
					'error' => $this->lang->line ( 'error_param_missing' ) . " integer:id, string:object_params, string:action (optional)" 
				), 400 );
			}
		}
		$id = ($accid != '') ? $accid : $this->postdata ['id'];
		$query = "SELECT " . $fields . ",id,(SELECT currency from currency where id=currency_id) as currency_code,currency_id,timezone_id,first_used,expiry,status,deleted FROM accounts AS ACC WHERE ACC.type = 0 AND id = '" . $id . "' limit 1";
		$this->api_log->write_log ( 'info', $query );
		$account_info = ( array )$this->db->query ( $query )->first_row();
		if (!empty($account_info)) {
			$account_info  = $this->_authorize_account ( $account_info, $balance, $credit_limit );
			if ($accid == '')
				$this->response ( array (
				       'status' => true,
				       'data' => $account_info
			       ), 200 );
			else
				return $account_info;
		} else {
			$this->api_log->write_log ('ERROR',"The server encountered an unexpected condition which prevented it from fulfilling the request.");
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_500' ) 
			), 500 );
		}
	}	
	/**
	 * ================================================================================================
	 * Public function to call using URL - END
	 * ================================================================================================
	 */
	/**
	 * _profile_update
	 *
	 * Update customer profile
	 *
	 * @param
	 *        	integer id
	 *        	json object_update_params
	 */
	/*protected function _profile_update() {
		if (! isset ( $this->postdata ['id'] ) || ! isset ( $this->postdata ['object_update_params'] )) {
			$this->response ( array (
					'status'=> false,
					'error' => $this->lang->line ( 'error_param_missing' ) . " id,object_update_params" 
			), 400 );
		}
		$object_update_params = json_decode ( $this->postdata ['object_update_params'],true);
		$this->_validate_fields ( $object_update_params );
		$this->db->trans_start ();
		foreach ( $object_update_params as $param_key => $param_value ) {
			$accountinfo [$param_key] = $param_value;
		}
		$this->db->select('number');
		$account_number=(array)$this->db->get_where('accounts',array('id'=>$this->postdata ['id']))->first_row();
		$this->db->where ( 'id', $this->postdata ['id'] );
		$result = $this->db->update ( 'accounts', $accountinfo );
		$this->db->where ( 'number', $account_number['number'] );
		$result = $this->db->update ( 'account_unverified', $accountinfo );
		$this->api_log->write_log ( 'info', $this->db->last_query () );
		if ($this->db->trans_status () === FALSE) {
			$this->db->trans_rollback ();
			$this->response ( array (
					'status'=> false,
					'error' => $this->lang->line ( 'error_500' ) 
			), 500 );
		} else {
			$this->db->trans_commit ();
			$this->response ( array (
					'success' => $this->lang->line ( 'account_update_success' ) 
			), 200 );
		}
		$this->response ( $object_update_params, 200 );
	}*/
	protected function _profile_update() {
		if (! isset ( $this->postdata ['object_update_params'] )) {
			$this->response ( array (
				'status'=> false,
				'error' => $this->lang->line ( 'error_param_missing' ) . "object_update_params" 
			), 400 );
		}
		$object_update_params = json_decode ( $this->postdata ['object_update_params'],true);

		$this->_validate_fields ( $object_update_params );
		$this->db->trans_start ();
		foreach ( $object_update_params as $param_key => $param_value ) {
			$accountinfo [$param_key] = $param_value;
		}
		$account_number=(array)$this->db->get_where('accounts',array('id'=>$this->postdata ['id']))->first_row();

		if($accountinfo['first_name'] == $account_number['first_name'] && $accountinfo['last_name'] == $account_number['last_name'] && $accountinfo['email'] == $account_number['email']){
			$this->api_log->write_log ('ERROR',"Account information already updated.");
				$this->response ( array (
					'status'=> false,
					'success' => $this->lang->line( 'account_already_updated' )
				), 400 );
		}

		if($accountinfo['first_name'] == '') {
			$accountinfo['first_name'] = $account_number['first_name'];
		}	
		if($accountinfo['last_name'] == '') {
			$accountinfo['last_name'] = $account_number['last_name'];
		}	
		if($accountinfo['email'] == '') {
			$accountinfo['email'] = $account_number['email'];
		}
		
		$this->db->where ( 'id', $this->postdata ['id'] );
		$result = $this->db->update ( 'accounts', $accountinfo );
		$this->db->where ( 'number', $account_number['number'] );
		$result = $this->db->update ( 'account_unverified', $accountinfo );
		$this->api_log->write_log ( 'info', $this->db->last_query () );
		if ($this->db->trans_status () === FALSE) {
			$this->db->trans_rollback ();
			$this->response ( array (
				'status'=> false,
				'error' => $this->lang->line ( 'error_500' ) 
			), 500 );
		} else {
			$this->db->trans_commit ();
			$this->api_log->write_log ('ERROR',"Account information update successfully.");
			$this->response ( array (
				'status' => true,
				'data' => $accountinfo,
				'success' => $this->lang->line ( 'account_update_success' ) 
			), 200 );
		}
		$this->response ( array (
			'status' => true,
			'data' => $object_update_params
		), 200 );
	}
	/**
	 * _profile_password_change
	 *
	 * Update customer profile
	 *
	 * @param
	 *        	integer id
	 *        	string username
	 *        	string old_password
	 *        	string password
	 *        	string object (If you want to update account password as well then set value account)
	 */
	protected function _profile_password_change() {
		$sipdevice_info = (array)$this->db->get_where ("sip_devices",array("username"=>$this->postdata ['username'],"status"=>0,"accountid"=>$this->postdata['id']))->first_row();
		$password_update_flag=false;
		$password_updated_fields='';
		$incorrect_password=0;
		if (!empty($sipdevice_info)) {
			$dir_params = json_decode ( $sipdevice_info ['dir_params'],true );
			if ($dir_params ['password'] == $this->postdata ['old_password']) {
				$dir_params ['password'] = $this->postdata ['password'];
				$this->db->set('dir_params',json_encode ( $dir_params ));
				$this->db->where('username',$this->postdata['username']);
				$this->db->where('accountid',$this->postdata['id']);
				$this->db->update('sip_devices');
				$this->api_log->write_log ( 'info', $this->db->last_query() );
				$password_update_flag=true;
				$password_updated_fields.= 'Sip Device ';
			}else{
				$incorrect_password = astpp_n($incorrect_password) + astpp_n(astpp_n($incorrect_password)+1);
				$this->api_log->write_log ( 'info',"sip device password is not match with old password.");
			}
		}else{
			$this->api_log->write_log ( 'info',"sip device either inactive/not available/associated with different account.");
		}
		$where = array(
			'id'=> $this->postdata['id'],
			'number' => $this->postdata ['username'],
			'deleted'=> 0,
			'status'=>0,
		);
		$accountinfo = (array)$this->db->get_where ("accounts", $where)->first_row();
		if(!empty($accountinfo)){
			if($accountinfo['password'] == 	$this->common->encode ( $this->postdata ['old_password'] )){
				$this->db->where('id',$this->postdata['id']);
				$this->db->set('password',$this->common->encode ( $this->postdata ['password'] ) );
				$this->db->update('accounts');
				$password_update_flag=true;
				$password_updated_fields.= 'accounts,';	
			}else{
				$incorrect_password = astpp_n($incorrect_password) + 1;
				$this->api_log->write_log ( 'info',"Account password is not matched with old_password.");
			}
		}else{
			$this->api_log->write_log ( 'info',"Account not found.");
			if(!empty($sipdevice_info)){
				$accountinfo = (array)$this->db->get_where('accounts',array("id"=>$sipdevice_info['accountid'],"status"=>0,"deleted"=>0))->first_row();
			}
		}
		if(empty($sipdevice_info) && empty($accountinfo)){
			$this->api_log->write_log ('ERROR',"This account is exists");
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'account_not_exists' ) 
			), 400 );
		}elseif($incorrect_password==2){
			$this->api_log->write_log ('ERROR',"Your old password is incorrect");
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'incorrect_old_password' ) 
			), 400 );
		}elseif(!empty($accountinfo)){
			$template_name ='reset_password';
			$status_code= 301;
			$this->common->push_notification_all($accountinfo,$template_name,$status_code);	
			$this->api_log->write_log ('ERROR',"Your password change successfully");
			$this->response ( array (
				'status' => true,
				'success' => $this->lang->line ( 'password_changed' ) 
			), 200 );
		}else{
			$this->api_log->write_log ('ERROR',"Something wrong,Please check information");
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'wrong_msg' ) 
			), 400 );
		}
	}
	protected function _validate_account($id, $fields, $balance = true, $credit_limit = true) {
		return $this->profile ( $id, $fields, $balance, $credit_limit );
	}
	protected function _validate_fields($object_update_params,$rq_frm_signup=FALSE) {
        $this->postdata ['id'] = (isset ( $this->postdata ['id'] )) ? $this->postdata ['id'] : '0';
		if (isset ( $object_update_params ['email'] )) {			
			$where = array (
				'email = ' => $object_update_params ['email'],
				'id = ' => $this->postdata ['id'] 
			);
			$query = $this->db->limit ( 1 )->get_where ( 'accounts', $where );
			if (empty($query)) {
				$response_message = ($rq_frm_signup==TRUE)?$this->lang->line ( 'signup_account_exist' ):$this->lang->line ( 'error_email_unique' );
				$this->response ( array (
					'status' => false,
					'error' => $response_message
				), 400 );
			}
		}
		if (isset ( $object_update_params ['number'] )) {
			$where = array (
				'number = ' => $object_update_params ['number'],
				'id <> ' => $this->postdata ['id'] 
			);
			$query = $this->db->limit ( 1 )->get_where ( 'accounts', $where );
			$this->api_log->write_log ( 'info', $this->db->last_query () );
			if (!empty($query)) {
		                $response_message = ($rq_frm_signup==TRUE)?$this->lang->line ( 'signup_account_exist' ):$this->lang->line ( 'error_number_unique' );
				$this->response ( array (
					'status' => false,
					'error' => $response_message
				), 400 );
			}
		}
		// You can validate other fields upon your requirement. Make sure to create seprate functions to validate each field.
	}
	/**
	 * _device_info
	 *
	 * INSERT / DELETE mobile device information in login and logout process
	 *
	 * @param string $accountinfo
	 *       string $action (Expected value INSERT, DELETE)
	 */
	protected function _device_info($accountinfo, $action) {
		if ($action == "INSERT") {
			$where = array(
				'username'      => $this->postdata ['username']
			);
			$dialer_device_info_count = $this->db->get_where("dialer_device_info",$where)->num_rows;
			$this->api_log->write_log ( 'Select dialer_device_info query :', $this->db->last_query() );
			if ($dialer_device_info_count >= 1) {
				$update_array = array(
					'last_login_date' => gmdate ( 'Y-m-d H:i:s' ),
					'username' => $this->postdata['username'],
					'accountid'=> $accountinfo ['id']
				);
				$this->db->where($where);
				$this->db->update("dialer_device_info",$update_array);
				$this->api_log->write_log ( 'Update dialer_device_info query ', $this->db->last_query() );
			} else {
				// Kinjal issue no 3259 Mobile dialer api related changes in core files
				$query = "INSERT INTO dialer_device_info (accountid,username) VALUES (" . $accountinfo ['id'] . ",'" . $this->postdata ['username']."' )";
				// END
				$this->api_log->write_log ( 'Insert dialer_device_info query ', $query );
				$this->db->query ( $query );
			}
	    	} else {
			$where = array(
				'accountid'     => $accountinfo ['id'],
				'username'      => $this->postdata ['username'],
			);
			$this->db->delete('dialer_device_info',$where);
			// Kinjal issue no 3479
			return $this->db->affected_rows();
			// END
    		}
	}
	protected function _authorize_account($account_info, $balance = true, $credit_limit = true) {
		if (@astpp_gt($account_info ['sd_status'], 0)) {
			$this->response ( array (
				'status'=> false,
				'error' => $this->lang->line ( 'error_status' ) 
			), 400 );
		}
		if (astpp_gt($account_info ['deleted'], 0)) {
			$this->response ( array (
				'status'=> false,
				'error' => $this->lang->line ( 'error_status' ) 
			), 400 );
		}
		$account_info = $this->_account_info_reformat ( $account_info, $balance, $credit_limit );
		return $account_info;
	}
	protected function _account_info_reformat($account_info, $balance = true, $credit_limit = true ) {
		$account_info ['first_used'] = $this->common->convert_GMT_to ( '', '', $account_info ['first_used'], $account_info ['timezone_id'] );
		$account_info ['expiry']     = $this->common->convert_GMT_to ( '', '', $account_info ['expiry'], $account_info ['timezone_id'] );
		
		if (@$account_info ['balance'] != '' && @$account_info ['credit_limit'] != '' && @$account_info ['posttoexternal'] != '') {
			$account_info ['display_balance'] = (astpp_n($account_info ['balance'])) + (astpp_n($account_info ['credit_limit']) * astpp_n($account_info ['posttoexternal']));
			$account_info ['display_balance'] = $this->common_model->calculate_currency ( $account_info ['display_balance'], '', $account_info ['currency_code'], TRUE, TRUE );
		}
		if (isset ( $account_info ['credit_limit'] )) {
			$account_info ['credit_limit'] = $this->common_model->calculate_currency ( $account_info ['credit_limit'], '', $account_info ['currency_code'], $balance, $credit_limit );
		}
		if (isset ( $account_info ['balance'] )) {
			$account_info ['balance'] = $this->common_model->calculate_currency ( $account_info ['balance'], '', $account_info ['currency_code'], $balance, $credit_limit );
		}
		
		unset ( $account_info ['status'], $account_info ['deleted'], $account_info ['sd_status'] );
		return $account_info;
	}
	protected function _token($string, $action, $account_info='') {
		$key = hash ( 'sha256', config_item ( 'token_key' ) );
		$secret_iv = config_item ( 'iv_key' );
		$iv = substr ( hash ( 'sha256', $secret_iv ), 0, 16 );
		if ($action == "e") {
			$token = base64_encode ( openssl_encrypt ( $string, 'AES-256-CBC', $key, 0, $iv ) );
			if (is_array($account_info)) {
				$account_info['token'] = $token;				
				return $account_info;
			}
			return $token;
		}
		if ($action == "d") {
			$token = openssl_decrypt ( base64_decode ( $string ), 'AES-256-CBC', $key, 0, $iv );
			return $token;
		}
	}
	protected function _send_mail($template_name, $accountinfo) {
		// Getting template information
		$template_query = $this->db_model->getSelect ( "*", "default_templates", array (
			'name' => $template_name 
		) );
		$template = $template_query->result_array ();
		$template_info = $template [0];
		$this->api_log->write_log ( 'info', $this->db->last_query () );
		// Getting parent invoice configuration information
		$accountinfo ['reseller_id'] = (astpp_gt($accountinfo ['reseller_id'], 0)) ? $accountinfo ['reseller_id'] : 1;
		$invoice_conf_query = $this->db_model->getSelect ( "*", "invoice_conf", array (
			'accountid' => $accountinfo ['reseller_id'] 
		) );
		$invoice_conf = $invoice_conf_query->result_array ();
		$invoice_conf_info = $invoice_conf [0];
		$this->api_log->write_log ( 'info', $this->db->last_query () );
		
		// Subject, Body variable replacement
		$template_info ['subject'] = str_replace ( '#NAME#', @$accountinfo ['first_name'] . " " . @$accountinfo ['last_name'], $template_info ['subject'] );
		$template_info ['template'] = str_replace ( '#NAME#', @$accountinfo ['first_name'] . " " . @$accountinfo ['last_name'], $template_info ['template'] );
		$template_info ['template'] = str_replace ( '#NUMBER#', @$accountinfo ['number'], $template_info ['template'] );
		$template_info ['template'] = str_replace ( '#PASSWORD#', @$accountinfo ['password'], $template_info ['template'] );
		$template_info ['template'] = str_replace ( '#COMPANY_WEBSITE#', @$invoice_conf_info ['website'], $template_info ['template'] );
		$template_info ['template'] = str_replace ( '#LINK#', @$accountinfo ['confirm'], $template_info ['template'] );
		$template_info ['template'] = str_replace ( '#COMPANY_EMAIL#', @$invoice_conf_info ['emailaddress'], $template_info ['template'] );
		$template_info ['template'] = str_replace ( '#COMPANY_NAME#', @$invoice_conf_info ['company_name'], $template_info ['template'] );
		
		$template_info ['template'] = str_replace ( '#BALANCE#', @$accountinfo ['balance'], $template_info ['template'] );
		$template_info ['template'] = str_replace ( '#REFILLBALANCE#', @$accountinfo ['refill_balance'], $template_info ['template'] );
		// Insert email information in table to send emails
		$email_array = array (
			'accountid' => $accountinfo ['last_inserted_id'],
			'subject' => $template_info ['subject'],
			'body' => $template_info ['template'],
			'from' => $invoice_conf_info ['emailaddress'],
			'to' => $accountinfo ['email'],
			'status' => "1" 
		);
		$this->db->insert ( "mail_details", $email_array );
		$this->api_log->write_log ( 'info', $this->db->last_query () );
	}

	function account_list() {

		if (! isset ( $this->postdata ['id'] ) || ! isset ( $this->postdata ['token'] ) ) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_param_missing' ) . " integer:id,integer:country_code, string:number" 
			), 400 );
		}
		$id = $this->postdata ['id'];
		$this->db->select('id as accountid,number,reseller_id,pricelist_id,cli_pool,paypal_permission,reference,non_cli_pricelist_id,status,sweep_id,creation,credit_limit,posttoexternal,balance,password,first_name,last_name,company_name,address_1,address_2,postal_code,province,city,country_id,telephone_1,telephone_2,email,notification_email,language_id,currency_id,maxchannels,cps,dialed_modify,type,timezone_id,inuse,deleted,notify_credit_limit,notify_flag,notify_email,commission_rate,invoice_day,invoice_interval,invoice_note,last_bill_date,pin,first_used,expiry,validfordays,local_call_cost,pass_link_status,local_call,charge_per_min,is_recording,loss_less_routing,allow_ip_management,permission_id,deleted_date,localization_id,notifications,is_distributor,generate_invoice,std_cid_translation,did_cid_translation,tax_number');
		$this->db->from('accounts');
		$this->db->WHERE('id',$id);
		$query = $this->db->get();
		$query = (array)$query->first_row();
		$this->api_log->write_log ( 'info', $query );

		if (isset($query)) {
			$this->response ( array (
				'status' => true,	
				'data' => $query
			), 200 );
		} else {
			$this->api_log->write_log ('ERROR',"No record found");
			$this->response ( array (
				'status' => true,
				'success' => $this->lang->line ( 'no_records_found' ) 
			), 200 );
            	}
	}
	function account_delete() {
		$rawinfo = $this->input->post();
		foreach ( $rawinfo as $key => $value ) {
			$this->postdata [$key] = $this->db->escape_str($this->_xss_clean ( $value, TRUE ));
		}
		$token      = $this->postdata ['token'];
		$accountid  = $this->postdata ['id'];
		/*if(empty($token)) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('require_token')
			), 400 );
		}*/
		$where = array (
				'id = '      => $accountid,
				'deleted = ' => '1' 
			);
		
		$query = $this->db->limit ( 1 )->get_where ( 'accounts', $where );

		if (!empty($query)) {
			$this->api_log->write_log ('ERROR',"Account already deleted");
			$this->response ( array (
				'status'    => false,
				'message'   => $this->lang->line ( 'account_already_deleted' )
			), 400 );
		}

		$account_arr = array(
			'deleted'      => '1',
			'deleted_date' => gmdate ( 'Y-m-d H:i:s' )
		);
		$this->db->where('id', $accountid);
	        $this->db->update('accounts', $account_arr);
		$this->api_log->write_log ( 'info', $this->db->last_query() );
		$this->api_log->write_log ('ERROR',"Account deleted successfully");
		$this->response ( array (
			'status'    => true,
			'message'   => $this->lang->line ( 'account_deleted' )
		), 200 );
	}

	function get_data () {
		$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
		$this->api_log->write_log ( 'Params : ', json_encode($this->postdata) );
		$rawinfo    = $this->postdata;
		$string     = trim($rawinfo ['hash_string']);
		$string_cnt = strlen($string);
		//last 3 digits
		$status_code = substr($string, -3);
		if ($status_code != '303') {
			$this->api_log->write_log ('ERROR',"Something went wrong");
				$this->response ( array (
					'status' => false,
					'error'  => $this->lang->line ( 'something_wrong_contact_admin' )
				), 400 );
		}
		$new_count  = astpp_n($string_cnt) - 3 ;
		$sip_number = substr($string, 0, $new_count);
		$this->db->where(array("username"=>$sip_number));
		$sip_devices_res = (array) $this->db->get('sip_devices')->first_row();

		if (empty($sip_devices_res)) {
			$this->api_log->write_log ('ERROR',"Something went wrong");
				$this->response ( array (
					'status' => false,
					'error'  => $this->lang->line ( 'something_wrong_contact_admin' )
				), 400 );
		}
		$data = (array) json_decode($sip_devices_res ['dir_params']);
		$this->response ( array (
			'status'   => true,
			'password' => (string) trim($data ['password'])
		), 200 );
	}
}
