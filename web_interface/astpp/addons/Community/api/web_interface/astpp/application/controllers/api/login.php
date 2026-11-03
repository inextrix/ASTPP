<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
require APPPATH . '/controllers/common/account.php';
	class Login extends Account {
		protected $object = "sip_devices";
		protected $account_reseller = "admin_reseller_accounts";
		protected $postdata = "";
		
		function __construct() {
			parent::__construct ();
			$this->load->library ( 'common' );
			$rawinfo = $this->post ();
			foreach ( $rawinfo as $key => $value ) {
				$this->postdata [$key] = $value;
			}
		}
		public function index() {
			$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
			$this->api_log->write_log ( 'Login API: ', json_encode($this->postdata) );
			
			if (!isset($this->postdata['username']) || !isset($this->postdata['password'])) {
				$this->response ( array (
					'status'=> false,
					'error' => $this->lang->line ( 'error_param_missing' ) . " string:username, string:password, string:object (optional), string:apns_token" 
				), 400 );
			}
			$where_number = array(
				"username" => $this->postdata ['username']
			);
			$sip_device_info = (array)$this->db->get_where( "sip_devices", $where_number )->first_row();
			$this->api_log->write_log ( 'Login API sip_devices_query : ', $this->db->last_query());
			if (!empty($sip_device_info)){
				$accounts_info = ( array ) $this->db->get_where( "accounts", array("id"=> $sip_device_info['accountid']) )->first_row();
			}else{
				$accounts_info = ( array ) $this->db->get_where( "accounts", array("number" => $this->postdata ['username'],"deleted"=>"0") )->first_row();
			}
			// print_r($accounts_info); die;
			$this->api_log->write_log ( 'Login API accounts_query : ', $this->db->last_query());
			if ((!empty($accounts_info)) && ($accounts_info['type'] == '0')) {
				$where_sip_check = array(
						"accountid" => $accounts_info ['id']
					);
				$accounts_info = ( array ) $this->db->get_where( "sip_devices", $where_sip_check )->result_array();
				if (empty($accounts_info)) {
					$this->api_log->write_log ('ERROR',"Create sip device on signup has been disabled.");
					$this->response ( array (
						'status'  => false,
						'error'   => $this->lang->line ( 'something_wrong_contact_admin' )
					), 400 );
				}
				$function = (isset($this->postdata['object']))?$this->postdata['object']:'';
				$function = (($function != '') ? "_".$function : "_".$this->object);
			}else{		
				$function = (isset($this->postdata['object']))?$this->postdata['object']:'';
				$function = (($function != '') ? "_".$function : "_".$this->account_reseller);
			}
			if (astpp_gt(( int ) method_exists ( $this, $function ), 0))
				$this->$function ();
			else {
				$this->api_log->write_log ('ERROR',"Unknown Method.");
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'unknown_method' ) 
				), 400 );
			}

		}

		private function _admin_reseller_accounts(){
			if($this->postdata['username']  == '' && empty($this->postdata['username']  )){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'enter_username' ) 
				), 400 );
			}
			if($this->postdata['password']  == '' && empty($this->postdata['password']  )){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'enter_password' ) 
				), 400 );
			}
			$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
			$this->api_log->write_log ( 'Logout Params: ', json_encode($this->postdata) );
			$dir_params = $this->common->encode ($this->postdata['password']);
			$query = "SELECT * FROM accounts where number = '".$this->postdata['username']."' and password = '$dir_params' order by id desc";
			$this->api_log->write_log ( 'info', $query );		
			$account_info = ( array )$this->db->query ( $query )->first_row();
			if (!empty($account_info) && $account_info['status'] != '1') {
					$account_info = $this->_authorize_account ( $account_info );
					$this->_device_info ( $account_info,"INSERT" );
					$account_info=$this->_token($account_info['id'],"e",$account_info);
					$this->response ( array (
						'status' => true,
						'data' => $account_info
					), 200 );
			} else {
				if($account_info['status'] != '1'){
					$this->api_log->write_log ('ERROR',"Username / Password incorrect.");
					$this->response ( array (
						'status' => false,
						'error' => $this->lang->line ( 'error_login_information' ) 
					), 400 );
				}else{
					$this->api_log->write_log ('ERROR',"Account is inactive.");
					$this->response ( array (
						'status' => false,
						'error' => $this->lang->line ( 'account_is_inactive' ) 
					), 400 );
				}
			}
		}
		public function logout() {

			$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
			$this->api_log->write_log ( 'Logout Params: ', json_encode($this->postdata) );
			if ($this->postdata['id'] == "" || $this->postdata['username'] == "") {
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'error_param_missing' ) . " integer:id, string:username"
				), 400 );
			}
			$accountinfo ['id'] = $this->postdata['id'];
			$deleted_rows = $this->_device_info ( $accountinfo, 'DELETE' );
			if(astpp_gt($deleted_rows, 0)){
				$this->api_log->write_log ('ERROR',"You Logout from account.");
				$this->response ( array (
					'status' => true,
					'success' => $this->lang->line ( 'logged_out' ) 
				), 200 );
			}else{
				$this->api_log->write_log ('ERROR',"Something wrong, Please try again later.");
				$this->response ( array (
					'status'  => false,
					'error'    => $this->lang->line ( 'something_wrong_try_again' )
				), 400 );
			}
			// END
		}
		
		private function _sip_devices() {
			$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
			$this->api_log->write_log ( 'SIP Devices Params: ', json_encode($this->postdata) );
			if(!isset($this->postdata ['username']) || empty($this->postdata ['username'])) {
				$this->api_log->write_log ('ERROR',"Enter Username.");
				$this->response ( array (
					'status'  => false,
					'error'    => $this->lang->line ( 'enter_username' )
				), 400 );
			}
			if(!isset($this->postdata ['password']) || empty($this->postdata ['password'])) {
				$this->api_log->write_log ('ERROR',"Enter Password.");
				$this->response ( array (
					'status'  => false,
					'error'    => $this->lang->line ( 'enter_password' )
				), 400 );
			}
			$query = "SELECT ACC.id as id,ACC.id as accountid, ACC.number, ACC.reseller_id,ACC.type,ACC.pricelist_id, ACC.credit_limit, ACC.posttoexternal, ACC.balance, ACC.first_name, ACC.last_name, ACC.company_name, ACC.address_1, ACC.address_2, ACC.postal_code, ACC.province, ACC.city, ACC.country_id, ACC.telephone_1, ACC.telephone_2, ACC.email, (SELECT currency from currency where id=ACC.currency_id) as currency_code,ACC.currency_id,ACC.timezone_id,ACC.first_used,ACC.expiry,ACC.is_recording,SD.username,SD.dir_params,SD.status as sd_status,ACC.status,ACC.deleted FROM sip_devices AS SD,accounts AS ACC WHERE ACC.id=SD.accountid AND SD.username='" . $this->postdata['username'] . "' limit 1";
			$this->api_log->write_log ( 'info', $query );		
			$account_info = ( array )$this->db->query ( $query )->first_row();
			if (!empty($account_info) && $account_info['status'] != '1') {
				$this->api_log->write_log ( 'info', $account_info ['dir_params']);
				$dir_params = json_decode ( $account_info ['dir_params'],true );
				$account_info ['dir_params'] = $dir_params;
				$this->api_log->write_log ( 'info', $this->postdata['password']."------".$dir_params ['password']);
				if ($this->postdata['password'] == $dir_params ['password']) {
					$account_info = $this->_authorize_account ( $account_info );
					if($account_info['type'] == '0'){
						$account_info['id'] = $account_info['accountid'];
					}
					$this->_device_info ( $account_info,"INSERT" );
					$account_info=$this->_token($account_info['accountid'],"e",$account_info);
					$this->response ( array (
						'status' => true,       
						'data' => $account_info
				       	 ) , 200 );
				} else {
					if($account_info['status'] != '1'){
						$this->api_log->write_log ('ERROR',"Username / Password incorrect.");
						$this->response ( array (
							'status' => false,
							'error' => $this->lang->line ( 'error_login_information' ) 
						), 400 );
					}else{
						$this->api_log->write_log ('ERROR',"Account is inactive");
						$this->response ( array (
							'status' => false,
							'error' => $this->lang->line ( 'account_is_inactive' ) 
						), 400 );
					}
				}
			} else {
				$this->api_log->write_log ('ERROR',"Account is inactive");
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'account_is_inactive' ) 
				), 400 );
			}
		}
		private function _accounts() {
			$query = "SELECT ACC.id,ACC.number,ACC.reseller_id,ACC.pricelist_id,ACC.credit_limit,ACC.posttoexternal,ACC.balance,ACC.first_name,ACC.last_name,ACC.company_name,ACC.address_1,ACC.address_2,ACC.postal_code,ACC.province,ACC.city,ACC.country_id,ACC.telephone_1,ACC.telephone_2,ACC.email,(SELECT currency from currency where id=ACC.currency_id) as currency_code,ACC.currency_id,ACC.timezone_id,ACC.first_used,ACC.expiry,ACC.is_recording,ACC.status,ACC.deleted FROM accounts AS ACC WHERE ACC.type = 0 AND (number='" . $this->postdata['username'] . "' OR email = '" . $this->postdata['username'] . "') AND password = '" . $this->common->encode ( $this->postdata['password'] ) . "' limit 1";
			$this->api_log->write_log ( 'info', $query );
			$account_info = $this->db->query ( $query )->first_row();
			if (!empty($account_info)) {
				$account_info = $this->_authorize_account ( $account_info );
				$this->_device_info ( $account_info,"INSERT" );
				$account_info=$this->_token($account_info['id'],"e",$account_info);
				$this->response ( array (
					'status' => true,
					'data' => $account_info
				), 200 );
			} else {
				$this->api_log->write_log ('ERROR',"Username / Password incorrect.");
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'error_login_information' ) 
				), 400 );
			}
		}
			
	}

