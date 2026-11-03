<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
/**
 * ****************************************************************
 * IMPORTANT!! : This is API for SIP Device CURD Operation : IMPORTANT!!
 * ****************************************************************
 *
 * ==================================================
 * API Expected parameters :
 * ===================================================
 * Integer : start_limit (Start limit for customer list)
 * Integer : end_limit (End limit for customer list)
 * Integer : accountid (Unique accountid for each customer)
 * String : password (Customer Password (password must be alphabetic, numeric and sepcial characters))
 * Integer : username (Unique account username for each customer)
 * Integer : sipdevice_id (Unique sipdevice id for each customer)
 *
 * ===================================================
 * API Possible actions : create,read,update,delete,list
 * ===================================================
 * create : username,password,accountid
 * read : sipdevice_id,accountid
 * update : mandatory : sipdevice_id,accountid,password
 *			non-mandatory : all fields of database (exclude id)
 * delete : sipdevice_id,accountid
 * list : start_limit,end_limit,accountid
 *
 * ===================================================
 * API URL
 * ===================================================
 * For Index : 
 */

require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Sip_devices extends Account {

	protected $postdata = "";
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
			$where = array('id' => $this->accountinfo['id'] , 'type' => 1);
			$this->db->where($where);
		}
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

function _sip_devices_list(){
		if (empty($this->postdata['end_limit']) || empty($this->postdata['start_limit']) ){
			if(!( $this->postdata['start_limit'] == '0' || $this->postdata['end_limit'] == '0' )){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'error_param_missing' ) . " integer:end_limit,integer:start_limit"
				), 400 );
			}else{
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('number_greater_zero')
				), 400 );
			}
		}
		if(!($this->postdata['start_limit'] < $this->postdata['end_limit'])){
			$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('valid_start_limit')
			), 400 );
		}
		if($this->postdata['object_where_params']['mailto']){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'not_allowed_mailto_search' ) 
			), 400 );
		}
		$object_where_params = $this->postdata['object_where_params'];
		foreach($object_where_params as $object_where_key => $object_where_value) {
			if($object_where_value != '') {	
				$where = $object_where_key . ' = "' . $object_where_value . '" AND ';
				if(!empty($where)) {
					$where = rtrim($where,"AND ");
					$this->db->where($where);
				}
			}
		}
		$start = astpp_n($this->postdata['start_limit'])-1;
		$limit = $this->postdata['end_limit'];
		$no_of_records = (int)$limit - (int)$start;
		if($this->accountinfo['type'] == 1){
			$where = array('reseller_id' => $this->accountinfo['id'] );
			$this->db->where($where);
		}
		$query = $this->db->limit($no_of_records, $start)
			->order_by('id','desc')
			->select('*')
			->get ('sip_devices');
		$count = $query->num_rows();

		$sipdevice_info = $query->result_array();
		foreach ($sipdevice_info as $key => $sipdevice_value) {
			$sipdevice_value['dir_params'] = json_decode($sipdevice_value['dir_params'],true);
			$decoded_pass =  $this->common->decode($sipdevice_value['dir_params']['password']);
			$sipdevice_value['dir_params']['password'] = $this->common->encrypt($decoded_pass);
			$sipdevice_value['sip_profile_name'] = $this->common->get_field_name('name','sip_profiles',array('id'=>$sipdevice_value['sip_profile_id']));
			$sipdevice_value['accountid'] = $this->common->build_concat_string('first_name,last_name,number,company_name','accounts',$sipdevice_value['accountid']); 
			$sipdevice_value['reseller_id'] = $this->common->reseller_select_value('first_name,last_name,number,company_name','accounts',$sipdevice_value['reseller_id']); 
			$sipdevice_value['creation_date'] = $this->common->convert_GMT_to('','',$sipdevice_value['creation_date'],$this->accountinfo['timezone_id']);
			$sipdevice_value['last_modified_date'] = $this->common->convert_GMT_to('','',$sipdevice_value['last_modified_date'],$this->accountinfo['timezone_id']);
			$sipdevice_value['status'] = $sipdevice_value['status'] == '1' ? 'Inactive' : 'Active';
			unset($sipdevice_value['call_waiting'],$sipdevice_value['sip_profile_id']);
			if($this->accountinfo['type'] == '1'){
				unset($sipdevice_value['reseller_id']);
			}
			$sipdeviceinfo[] =$sipdevice_value;
		}
		
    	if (!empty($sipdeviceinfo)) {
			$this->response ( array (
				'status' => true,
				'total_count'=>$count,
				'data' => $sipdeviceinfo,
				'success' => $this->lang->line( "sipdevice_list_information" )
			), 200 );
        }else{
			$this->response ( array (
				'status' => true,
				'total_count'=>0,
				'data' => array(),
				'success' => $this->lang->line( "no_records_found" )
			), 200 );
		}
	}
		
	function _sip_devices_create() {
		$postdata = $this->postdata;
		if($this->form_validation->required($postdata['accountid'] == '')){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'account_not_found' )
			), 400 );
		}else{
			$account_info = '';
			if($this->accountinfo['type'] != '1'){
				if($this->form_validation->required($postdata['reseller_id'] == '')){
					$this->response ( array (
						'status'  => false,
						'error'   => $this->lang->line ( 'enter_reseller_id' )
					), 400 );
				}else{
					$resellerinfo = (array)$this->db->get_where ("accounts",array("reseller_id"=>$postdata['reseller_id'],"deleted"=>0,"status"=>0))->first_row();
					if(empty($resellerinfo)){
						$this->response ( array (
						'status'  => false,
						'error'   => $this->lang->line ( 'valid_reseller_id' )
						), 400 );
					}else{
							$account_info = $this->common->get_field_name('id','accounts',array('id' => $postdata['accountid'],'reseller_id'=> $postdata['reseller_id'],'deleted'=>0)); 
					}
				}
			}

			if($this->accountinfo['type'] == '1'){
				$account_info = $this->common->get_field_name('id','accounts',array('id' => $postdata['accountid'],'reseller_id'=> $postdata['id'],'deleted'=>0)); 
			}
			if(empty($account_info)){
				$this->response ( array (
					'status'  => false,
					'error'   => $this->lang->line ( 'account_not_found' )
				), 400 );
			}
			if(!($postdata['status'] == '1' || $postdata['status']=='0') ){
				$postdata['status'] = '0';
			}
			if ($postdata['username'] == "") {
				$postdata['username'] = $this->common->find_uniq_rendno('10', '', '');
			}else{
				if($this->accountinfo['type'] == '1'){
					$where_array = array('username' => $postdata['username'],'reseller_id' => $postdata['id'],'accountid'=> $postdata['accountid']);
				}else{
					$where_array = array('username' => $postdata['username'],'reseller_id' => $postdata['reseller_id'],'accountid'=> $postdata['accountid']);
				}
				$sip_device_id = $this->common->get_field_name('id','sip_devices',$where_array);
				if(!empty($sip_device_id)){
					$this->response ( array (
						'status'  => false,
						'error'   => $this->lang->line ( 'duplicate_sip_device' )
					), 400 );
				}
				if(!$this->form_validation->integer($postdata['username'])) {
					$this->response(array(
						'status' => false,
						'error' => $this->lang->line('invalid_sip_number')
					), 400);
				}
			}

			if(isset($postdata['mailto']) && !empty($postdata['mailto']) && (!filter_var($postdata['mailto'], FILTER_VALIDATE_EMAIL))){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('invalid_email_format')
				), 400 );
			}

			$password = $this->common->generate_password();
			if($this->form_validation->required($postdata['sip_profile_id'] == '')){
				$this->response ( array (
					'status'  => false,
					'error'   => $this->lang->line ( 'required_sip_profile_id' )
				), 400 );
			}else{
				$this->db->select ( '*' );
				$this->db->order_by('id', 'ASC');
				$this->db->where('id', $this->postdata['sip_profile_id']);
				$this->db->limit('1');
				$sip_profile_id = ( array ) $this->db->get ( 'sip_profiles' )->first_row ();
				if(empty($sip_profile_id) || $sip_profile_id == ''){
					$this->response ( array (
						'status' => false,
						'error' => $this->lang->line ( 'valid_sip_profile_id' ) 
					), 400 );
				}
			}

			if(!($postdata['voice_mail_enable'] =='false' || $postdata['voice_mail_enable'] == 'true')){
				$postdata['voice_mail_enable'] = 'true';
			}
			if(!($postdata['attach_file'] =='false' || $postdata['attach_file'] == 'true')){
				$postdata['attach_file'] = 'true';
			}
			if(!($postdata['local_after_email'] =='false' || $postdata['local_after_email'] == 'true')){
				$postdata['local_after_email'] = 'true';
			}
			if(!($postdata['send_all_message'] =='false' || $postdata['send_all_message'] == 'true')){
				$postdata['send_all_message'] = 'true';
			}
			$digits = 5;
        	$random_password = rand(pow(10, astpp_n($digits) - 1), pow(10, $digits) - 1);
			$sipdevice_array = array (
				'username' => $postdata['username'],
				'sip_profile_id' => $postdata ['sip_profile_id'],
				"reseller_id" => $this->accountinfo['type'] == '1' ? $postdata['id'] : $postdata ['reseller_id'],
				'accountid' => $postdata['accountid'],
				'dir_params' => json_encode(array(
					"password"=>  $password ,
					'vm-enabled' => $postdata['voice_mail_enable'] ,
					"vm-password"=> $random_password,
					"vm-mailto"=> $postdata['mailto'],
					"vm-attach-file"=>$postdata['attach_file'],
					"vm-keep-local-after-email"=> $postdata['local_after_email'],
					"vm-email-all-messages"=>$postdata['send_all_message']
				)),
				"dir_vars"=>json_encode(array(
					'effective_caller_id_name' => $postdata['caller_name'],
					'effective_caller_id_number' => $postdata['caller_number'],
				)),
				'codec' => $postdata['codec'],
				'status' => isset($postdata ['status']) ? $postdata ['status'] : '0',
				'creation_date'=>gmdate('Y-m-d H:i:s'),
				'last_modified_date'=>gmdate('Y-m-d H:i:s'),
				'call_waiting' => '0',
			);
			$this->db->insert("sip_devices",$sipdevice_array);
			$last_id = $this->db->insert_id ();
			$final_array = $this->accountinfo;
			$final_array['sip_user_name'] = $sipdevice_array['number'];
			$final_array['password'] = $password;
			$final_array['id'] = $postdata['accountid'];
			$final_array['status_code'] = 306;
			$this->common->mail_to_users('create_sip_device',$final_array);	
			$sipdevice_array['sipdevice_id'] = (string)$last_id;
			unset($sipdevice_array['id']);
			$sipdevice_array['dir_params'] = json_decode($sipdevice_array['dir_params'],true);
			$decoded_pass =  $this->common->decode($sipdevice_array['dir_params']['password']);
			$sipdevice_array['dir_params']['password'] = $this->common->encrypt($decoded_pass);
			$sipdevice_array['dir_params'] = json_encode($sipdevice_array['dir_params']);
			$sipdevice_array['creation_date'] = $this->common->convert_GMT_to('','',$sipdevice_array['creation_date'],$this->accountinfo['timezone_id']);
			$sipdevice_array['last_modified_date'] = $this->common->convert_GMT_to('','',$sipdevice_array['last_modified_date'],$this->accountinfo['timezone_id']);
			// END
			$this->response ( array (
				'status'=>true,
				'data' => $sipdevice_array,
				'success' => $this->lang->line( 'sipdevice_created' ) 
			), 200 );
		}
	}

	function _sip_devices_delete(){
		$postdata = $this->postdata;
		if($this->form_validation->required($postdata['sipdevice_id'] == '')){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'require_sip_id' )
			), 400 );
		}
		if(!$this->form_validation->numeric_with_comma($postdata['sipdevice_id'])){
			$this->response ( array (
				'status' => false,
				'success' =>  $this->lang->line ('valid_sip_id')  
			), 400 );
		}
		$where = array();
		if($this->accountinfo['type'] == '1'){
			$where = array('reseller_id' => $postdata['id']);
		}
		$this->db->where("id IN (".$postdata['sipdevice_id'].") ");
		$delete_sip_info = $this->db->delete('sip_devices',$where);
		$delete_sip_info = $this->db->affected_rows($delete_sip_info ); 
		if($delete_sip_info != '0'){
			$this->response ( array (
				'status'=>true,
				'success' => $this->lang->line( 'sipdevice_deleted' )
			), 200 );
		}else{
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line( 'sipdevice_not_found' )
			), 400 );
		}
	}
}
?>
