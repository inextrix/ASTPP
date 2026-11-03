<?php

require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Gateways extends Account {
	
	protected $postdata = "";
	function __construct() {
		parent::__construct ();
		$this->load->model ( 'common_model' );
		$this->load->model ( 'db_model' );
		$this->load->library('Form_validation');
		$this->load->library('common');
		$this->accountinfo = $this->get_account_info(); 
		$rawinfo = $this->post();
		$this->postdata = array();
		if($this->accountinfo['type'] != '-1'  && $this->accountinfo ['type'] != '2' ){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'error_invalid_key' )
			), 400 );
		}
		$this->postdata = array();
		foreach ( $rawinfo as $key => $value ) {
			$this->postdata [$key] = $this->_xss_clean ( $value, TRUE );
		}
	}
	public function index() {
		$function = isset ( $this->postdata ['action'] ) ? $this->postdata ['action'] : '';
		$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
		$this->api_log->write_log ( 'Params : ', json_encode($this->postdata) );
		$accountid = $this->postdata ['id'];
		if($this->accountinfo['type'] == '1'){
			$where = array('id' => $this->accountinfo['id'] , 'type' => 1,'deleted'=>0,'status'=>0);
		}else{
			$type = array(-1,2);
			$where = array('id'=>$accountid,'deleted'=>0,'status'=>0);
			$this->db->where_in('type',$type);
		}
		$this->db->where($where);
		$accountinfo = (array)$this->db->get('accounts')->first_row();
		if(empty($accountinfo) || !isset($accountinfo)){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'account_not_found' )
			), 400 );
		}
		$accountinfo = $this->_authorize_account ( $accountinfo,true,true);
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
	
	function _gateway_create() {
		$postdata = $this->postdata;
		if(!$this->form_validation->required($postdata['name'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('required_gateway_name')
			),400 );
		}
		if(preg_match('/\s/', $postdata['name'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('invalid_gateway_name')
			),400 );
		}else{
			$gateway_name = $this->common->get_field_name('name','gateways',array('name' => $postdata['name'] ));
			if(!empty($gateway_name)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('duplicate_gateway_name')
				),400 );
			}
		}
		if(!$this->form_validation->required($postdata['sip_profile_id'] ) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'required_sip_profile_id' ) 
			), 400 );
		}else{
			$sip_profile_id =  $this->common->get_field_name('id','sip_profiles',array('id' => $postdata['sip_profile_id']));
			if(empty($sip_profile_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'sip_profile_not_found' ) 
				), 400 );
			}
		}
		if(!$this->form_validation->required($postdata['proxy'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'required_proxy' ) 
			), 400 );
		}
		if(!$this->form_validation->valid_ip_domain($postdata['proxy'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_proxy' ) 
			), 400 );
		}
		if(!is_numeric($postdata['expire-seconds']) && !empty($postdata['expire-seconds']) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_expire_seconds' ) 
			), 400 );
		}
		if(!is_numeric($postdata['ping']) && !empty($postdata['ping']) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_ping' ) 
			), 400 );
		}
		if(!is_numeric($postdata['retry-seconds']) && !empty($postdata['retry-seconds']) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_retry_seconds' ) 
			), 400 );
		}
		$final_arr=array(
			'name'            =>isset($this->postdata['name'])?$this->postdata['name']:'',
			'sip_profile_id'  =>isset($postdata['sip_profile_id'])?$postdata['sip_profile_id']:'',
			'dialplan_variable' => isset($this->postdata['dialplan_variable'])?$this->postdata['dialplan_variable']:'',
			'last_modified_date' =>gmdate('Y-m-d H:i:s'),
			'created_date'      => gmdate('Y-m-d H:i:s'),
			'accountid'  => 0,
			'status' => $postdata['status'] == '0' || $postdata['status'] == '1' ? $postdata['status'] : '0',
			'gateway_data' => json_encode(array(
				'username'        =>isset($this->postdata['username'])?$this->postdata['username']:'',
				'password'        =>isset($this->postdata['password'])? $this->common->encode($this->postdata['password']):'',
				'proxy'           =>isset($postdata['proxy'])?$postdata['proxy']:'',
				'outbound-proxy'  =>isset($postdata['outbound-proxy'])?$postdata['outbound-proxy']:'',
				'register'        =>$postdata['register'] == 'true' || $postdata['register'] == 'false' ? $postdata['register'] : 'false',
				'caller-id-in-from' => $postdata['caller-id-in-from'] == 'true' || $postdata['caller-id-in-from'] == 'false' ? $postdata['caller-id-in-from'] : 'true',
				'extension-in-contact' => $postdata['extension-in-contact'] == 'true' || $postdata['extension-in-contact'] == 'false' ? $postdata['extension-in-contact'] : 'false',
				'from-domain'      =>isset($postdata['from-domain'])?$postdata['from-domain']:'',
				'from-user'	       => isset($this->postdata['from-user'])?$this->postdata['from-user']:'',
				'realm'	     => isset($this->postdata['realm'])?$this->postdata['realm']:'',
				'extension'	 => isset($this->postdata['extension'])?$this->postdata['extension']:'',
				'expire-seconds'=> isset($this->postdata['expire-seconds'])?$this->postdata['expire-seconds']:'',
				'register-transport' => isset($this->postdata['register-transport'])?$this->postdata['register-transport']:'',
				'contact-params' => isset($this->postdata['contact-params'])?$this->postdata['contact-params']:'',
				'ping' => isset($this->postdata['ping'])?$this->postdata['ping']:'',
				'retry-seconds' => isset($this->postdata['retry-seconds'])?$this->postdata['retry-seconds']:'',
				'register-proxy' => isset($this->postdata['register-proxy'])?$this->postdata['register-proxy']:''
			))
			);
			$this->db->insert("gateways", $final_arr);
			$last_id = $this->db->insert_id();
			if(!empty(($last_id))){
				$this->response ( array (
					'status'  => true,
					'success' => $this->lang->line ( 'gateway_created' )  
				), 200 );
			}else {
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('something_wrong_try_again')	
				), 400 );
			}
	}
}
?>
