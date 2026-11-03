<?php

require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Trunk extends Account {
	
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
		if($this->accountinfo['type'] != '-1' && $this->accountinfo ['type'] != '2' ){
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
		$type = array(-1,2);
		$where = array('id'=>$accountid,'deleted'=>0,'status'=>0);
		$this->db->where_in('type',$type);
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
	
	function  _trunk_create() {
		$postdata = $this->postdata;
		if(!$this->form_validation->required($postdata['name'] ) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'trunk_name' ) 
			), 400 );
		}
		if(!$this->form_validation->required($postdata['provider'] ) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_provider_id' ) 
			), 400 );
		}else{
			$provider_id = $this->common->get_field_name('id','accounts',array('id' => $postdata['provider'],'type' => 3,'status' => '0'));
			if(empty($provider_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'provider_account_not_found' ) 
				), 400 );
			}
		}
		if(!$this->form_validation->required($postdata['gateway_name'] ) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'required_gateway' ) 
			), 400 );
		}else{
			$gateway_id = $this->common->get_field_name('id','gateways',array('id' => $postdata['gateway_name'],'status' => 0));
			if(empty($gateway_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'gateway_not_found' ) 
				), 400 );
			}
		}
		if(!empty($postdata['failover_gateway_name_1'])){
			$gateway_id = $this->common->get_field_name('id','gateways',array('id' => $postdata['failover_gateway_name_1'],'status' => 0));
			if(empty($gateway_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'failover_gateway_not_found' ) 
				), 400 );
			}
		}
		if(!empty($postdata['failover_gateway_name_2'])){
			$gateway_id = $this->common->get_field_name('id','gateways',array('id' => $postdata['failover_gateway_name_2'],'status' => 0));
			if(empty($gateway_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'failover_gateway_not_found' ) 
				), 400 );
			}
		}

		$postdata['status'] = $postdata['status'] == '0' || $postdata['status'] == '1' ? $postdata['status'] : '0'; 
		if(!empty($postdata['localization'])){
			$localization_id = $this->common->get_field_name('id','localization',array('id' => $postdata['localization'],'status' => 0));
			if(empty($localization_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'localization_not_found' ) 
				), 400 );
			}
		}
		if (!is_numeric($postdata['call_timeout']) && !empty($postdata['call_timeout'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_call_timeout' )
			), 400 );
		}
		if (!is_numeric($postdata['concurrent_calls']) && !empty($postdata['concurrent_calls'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'integer_concurrent_calls' )
			), 400 );
		}
		if (!is_numeric($postdata['cps']) && !empty($postdata['cps'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_cps' )
			), 400 );
		}
		if (!is_numeric($postdata['priority']) && !empty($postdata['priority'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_priority' )
			), 400 );
		}
		$final_arr=array(
			'name'            	 =>isset($postdata['name'])?$postdata['name']:'',
			'provider_id'     	 =>isset($postdata['provider'])?$postdata['provider']:'',
			'gateway_id'       	 =>isset($postdata['gateway_name'])?$postdata['gateway_name']:'',
			'failover_gateway_id'=>isset($postdata['failover_gateway_name_1'])?$postdata['failover_gateway_name_1']:'',
			'failover_gateway_id1'=>isset($postdata['failover_gateway_name_2'])?$postdata['failover_gateway_name_2']:'',
			'status'              =>isset($postdata['status'])?$postdata['status']:'',
			'localization_id'    =>isset($postdata['localization'])?$postdata['localization']:'',
			'codec'              =>isset($postdata['codec'])?$postdata['codec']:'',
			'leg_timeout'        =>isset($postdata['call_timeout'])?$postdata['call_timeout']:'',
			'maxchannels'        =>isset($postdata['concurrent_calls'])?$postdata['concurrent_calls']:'',
			'cps'	             => isset($postdata['cps'])?$postdata['cps']:'',
			'precedence'	     => isset($postdata['priority'])?$postdata['priority']:'',
			'last_modified_date' =>gmdate('Y-m-d H:i:s'),
			'creation_date'      => gmdate('Y-m-d H:i:s'),
			'tech'               => '',
			'sip_cid_type' => $postdata['sip_cid_type'] == "rpid" || $postdata['sip_cid_type'] == "pid" ?  $postdata['sip_cid_type'] : 'none'
			);
			$this->db->insert("trunks", $final_arr);
			$last_id = $this->db->insert_id();
			if(!empty(($last_id))){
				unset($final_arr['tech']);
				$this->response ( array (
					'status'  => true,
					'success' => $this->lang->line ( 'trunk_create' )  
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
