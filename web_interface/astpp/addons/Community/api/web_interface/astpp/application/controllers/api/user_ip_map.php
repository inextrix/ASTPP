<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class User_ip_map extends Account
{
	protected $postdata = "";
	function __construct()
	{
		parent::__construct();
		$this->load->model('common_model');
		$this->load->library('common');
		$this->load->model('db_model');
		$this->load->model('common_model');
		$this->load->model('Astpp_common');
		$this->load->library('Form_validation');
		$this->load->library('astpp/payment');
		$rawinfo = $this->post();
		$this->accountinfo = $this->get_account_info(); 
		if($this->accountinfo['type'] != '0'){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'error_invalid_key' )
			), 400 );
		}
		foreach ($rawinfo as $key => $value) {
			$this->postdata[$key] = $this->_xss_clean($value, TRUE);
		}
	}
	public function index()
	{
		$accountid = $this->accountinfo ['id'];
		$where = array('id'=>$accountid,'deleted'=>0,'status'=>0);
		$this->db->where($where);
		$accountinfo = (array)$this->db->get('accounts')->first_row();
		if(empty($accountinfo) || !isset($accountinfo)){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'account_not_found' )
			), 400 );
		}
		$accountinfo = $this->_authorize_account ( $accountinfo,true,true);
		$function = isset ( $this->postdata ['action'] ) ? $this->postdata ['action'] : '';
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

	function _user_ipmap_create() {
		$postdata = $this->postdata;
		if((!astpp_eq0($this->accountinfo['allow_ip_management']))){
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "ipsetting_add_not_allowed" )
			), 400 );
		}
		if(!isset($postdata['name']) || empty($postdata['name'])) {
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "enter_ip_name" )
			), 400 );
		}
		if(!isset($postdata['ip']) || empty($postdata['ip'])) {
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "enter_ip" )
			), 400 );
		}
		if(! $this->form_validation->valid_ip($postdata['ip'])){
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "valid_ip" )
			), 400 );
		}
		if(!$this->form_validation->integer($postdata['prefix'])){
			$this->response ( array (
				'status' => false,
				'success' =>  $this->lang->line ('invalid_prefix')  
			), 400 );
		}
		$this->db->select('prefix,ip');
        $this->db->where(['prefix' => $postdata['prefix'],'ip' => $postdata['ip']]);
        $ip_prefix = (array) $this->db->get('ip_map')->first_row();
        if (!empty($ip_prefix)) {
            $this->response ( array(
                'status'  => false,
                "error" => $this->lang->line( "unique_ip" )
            ), 400 );
        }
        $postdata['status'] = $postdata['status'] == '0' || $postdata['status'] == '1' ? $postdata['status'] : '0'; 
        $data = array(
            'created_date' => gmdate('Y-m-d H:i:s'),
            'last_modified_date' => gmdate('Y-m-d H:i:s'),
            'name' => $postdata['name'],
            'ip' => $postdata['ip'],
            'prefix' => $postdata['prefix'],
            'accountid' => $postdata['id'],
            'reseller_id' => $this->accountinfo['reseller_id'],
            'status' => $postdata['status'],
            'context' => 'default'
        );
        $this->db->insert("ip_map", $data);
        if(!empty($data)){
        	$data['created_date'] = $this->common->convert_GMT_to('','',$data['created_date'],$this->accountinfo['timezone_id']);
			unset($data['accountid'],$data['reseller_id'],$data['context'],$data['last_modified_date']);
			$this->response ( array (
				'status' => true,
				'data' => $data,
			    'success' =>  $this->lang->line ('ip_added')  
	        ), 200 );
        }else{
        	$this->response ( array (
				'status' => true,
			    'success' =>  $this->lang->line ('something_wrong_contact_admin')  
	        ), 200 );
        }
	}	

	function _user_ipmap_delete()
	{
		$postdata = $this->postdata;
		if(!$this->form_validation->required($postdata['ipmap_id'] ) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_ip' ) 
			), 400 );
		}else{
			if(!$this->form_validation->numeric_with_comma($postdata['ipmap_id'])){
				$this->response ( array (
					'status' => false,
				    'success' =>  $this->lang->line ('enter_valid_id')  
		        ), 400 );
			}
			$this->db->where("id IN (".$postdata['ipmap_id'].") ");
			$ip_mapInfo = $this->db->get_where('ip_map',array('reseller_id' => $this->accountinfo['reseller_id']))->result_array();
			if (empty($ip_mapInfo)) {
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'ipmap_not_found' )
				), 400 );
			}else{
				$this->db->where("id IN (".$postdata['ipmap_id'].") ");
				$this->db->delete('ip_map');
				$this->response ( array (
					'status' => true,
					'success' =>$this->lang->line ( 'ipmap_delete' )
				), 200 );
			}
		}
	}
}