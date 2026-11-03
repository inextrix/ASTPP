<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class User_sip_device extends Account
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
	
	private function _user_sipdevices_list()
	{
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
		$where = array('accountid' => $this->postdata['id'] );
		$this->db->where($where);
		$query = $this->db->limit($no_of_records, $start)
			->order_by('id','desc')
			->select('*')
			->get ('sip_devices');
		$count = $query->num_rows();
		$sipdevice_info = $query->result_array();
		foreach ($sipdevice_info as $key => $sipdevice_value) {
			// Kinjal issue no 3846
			$dir_params = json_decode($sipdevice_value['dir_params'], true);
			$sipdevice_value['password'] = $this->common->decode($dir_params['password']);
			$dir_vars = json_decode($sipdevice_value['dir_vars'], true);
			// END
			$sipdevice_value['accountid'] = $this->common->build_concat_string('first_name,last_name,number,company_name','accounts',$sipdevice_value['accountid']);
			$sipdevice_value['caller_name'] = $dir_vars['effective_caller_id_name'];
			$sipdevice_value['caller_number'] = $dir_vars['effective_caller_id_number'];
			$sipdevice_value['status'] = $sipdevice_value['status'] == '1' ? 'Inactive' : 'Active';
			$sipdevice_value['voice_mail'] =  $dir_params['vm-enabled'];
			$sipdevice_value['creation_date'] = $this->common->convert_GMT_to('','',$sipdevice_value['creation_date'],$this->accountinfo['timezone_id']);
			$sipdevice_value['last_modified_date'] = $this->common->convert_GMT_to('','',$sipdevice_value['last_modified_date'],$this->accountinfo['timezone_id']);
			// Kinjal issue no 3846
			unset($sipdevice_value['call_waiting'],$sipdevice_value['accountid'],$sipdevice_value['reseller_id'],$dir_params,$sipdevice_value['dir_params'] ,$sipdevice_value['sip_profile_id'],$dir_params,$sipdevice_value['dir_vars']);
			// END
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

	function _user_sipdevices_create() {
		$postdata = $this->postdata;
		if(!$this->form_validation->alpha_space_dash($postdata['caller_name']) && $postdata['caller_name'] != ''){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'invalid_caller_name' )
			), 400 );
		}
		if(!$this->form_validation->numeric($postdata['caller_number']) && $postdata['caller_number'] != ''){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'invalid_caller_number' )
			), 400 );
		}
		if(!($postdata['status'] == '1' || $postdata['status']=='0') ){
			$postdata['status'] = '0';
		}
		if ($postdata['username'] == "") {
			$postdata['username'] = $this->common->find_uniq_rendno('10', '', '');
		}else{
			$sip_device_id = $this->common->get_field_name('id','sip_devices',array('username' => $postdata['username']));
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
    	$reseller_id = $this->common->get_field_name('reseller_id','accounts', array('id' => $postdata['id']));
		$sipdevice_array = array (
			'username' => $postdata['username'],
			'sip_profile_id' => '1',
			'reseller_id' => isset($reseller_id) ? $reseller_id : '',
			'accountid' => $postdata['id'],
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
				"user_context"=>"default"
			)),
			'status' => isset($postdata ['status']) ? $postdata ['status'] : '0',
			'creation_date'=>gmdate('Y-m-d H:i:s'),
			'last_modified_date'=>gmdate('Y-m-d H:i:s'),
			'call_waiting' => '0',
			'codec' => $postdata['codec']
		);
		$this->db->insert("sip_devices",$sipdevice_array);
		$last_id = $this->db->insert_id ();
		$sipdevice_array['sipdevice_id'] = (string)$last_id;
		$final_array = $this->accountinfo;
		$final_array['sip_user_name'] = $sipdevice_array['number'];
		$final_array['password'] = $password;
		$template_name ='create_sip_device';
		$final_array['status_code'] = 306;
		$final_array['id'] = $postdata['accountid'];
		$final_array['last_id'] = $last_id;
		$this->common->mail_to_users($template_name,$final_array);	
		// END
		unset($sipdevice_array['id'],$sipdevice_array['call_waiting'],$sipdevice_array['reseller_id'],$sipdevice_array['accountid']);
		$sipdevice_array['dir_params'] = json_decode($sipdevice_array['dir_params'],true);
		$decoded_pass =  $this->common->decode($sipdevice_array['dir_params']['password']);
		$sipdevice_array['dir_params']['password'] = $this->common->encrypt($decoded_pass);
		$sipdevice_array['dir_params'] = json_encode($sipdevice_array['dir_params']);
		$sipdevice_array['creation_date'] = $this->common->convert_GMT_to('','',$sipdevice_array['creation_date'],$this->accountinfo['timezone_id']);
		$sipdevice_array['last_modified_date'] = $this->common->convert_GMT_to('','',$sipdevice_array['last_modified_date'],$this->accountinfo['timezone_id']);
		$this->response ( array (
			'status'=>true,
			'data' => $sipdevice_array,
			'success' => $this->lang->line( 'sipdevice_created' ) 
		), 200 );
	}

	function _user_sipdevices_update(){
		$postdata = $this->postdata;
		if (isset($postdata['reseller_id'])) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'reseller_update_not_allowed' ) 
			), 400 );	
		}
		if (isset($postdata['number'])) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'sipnumber_update_not_allowed' ) 
			), 400 );	
		}
		if($this->form_validation->required($postdata['sipdevice_id'] == '')){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'require_sip_id' ) 
			), 400 );
		}else{
			$sipdeviceinfo = (array)$this->db->get_where ("sip_devices",array("id"=>$postdata['sipdevice_id'],'accountid'=>$postdata['id']))->first_row();
			if(empty($sipdeviceinfo)){
				$this->response ( array (
					'status'  => false,
					'error'   => $this->lang->line ( 'sipdevice_not_found' )
				), 400 );
			}
			$vars = json_decode($sipdeviceinfo['dir_vars'],true);
			$vars_new = json_decode($sipdeviceinfo['dir_params'], true);
			if(!($postdata['voice_mail'] =='false' || $postdata['voice_mail'] == 'true')){
				$postdata['voice_mail'] = 'true';
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

			$update_array = array(
				"status" => isset($postdata['status'])?$postdata['status']:$sipdeviceinfo['status'],
				'dir_params' => json_encode(array(
					"password" => $vars_new['password'],
					"vm-enabled" => isset($postdata['voice_mail']) && !empty($postdata['voice_mail']) ? $postdata['voice_mail']:$vars_new['vm-enabled'],
					"vm-password" => isset($postdata['voicemail_password']) && !empty($postdata['voicemail_password']) ?$postdata['voicemail_password']:$vars_new['vm-password'],
					"vm-mailto" => isset($postdata['mailto']) && !empty($postdata['mailto']) ? $postdata['mailto'] :$vars_new['vm-mailto'],
					"vm-attach-file" => isset($postdata['attach_file']) && !empty($postdata['attach_file'])?$postdata['attach_file']:$vars_new['vm-attach-file'],
					"vm-keep-local-after-email" => isset($postdata['local_after_email']) && !empty($postdata['local_after_email'])?$postdata['local_after_email']:$vars_new['vm-keep-local-after-email'],
					"vm-email-all-messages" => isset($postdata['send_all_message']) && !empty($postdata['send_all_message'])?$postdata['send_all_message']:$vars_new['vm-email-all-messages']
				)),
				"dir_vars"=>json_encode(array(
					'effective_caller_id_name' => isset($postdata['caller_name']) && !empty($postdata['caller_name'])?$postdata['caller_name']:$vars['effective_caller_id_name'],
					'effective_caller_id_number' => isset($postdata['caller_number']) && !empty($postdata['caller_number'])?$postdata['caller_number']:$vars['effective_caller_id_number']
				)),
				'last_modified_date'=>gmdate('Y-m-d H:i:s')
			);
			$this->db->where ( 'id', $this->postdata ['sipdevice_id'] );
			$this->db->update ( 'sip_devices', $update_array );
			// Kinjal issue no 4071
			$update_array['dir_params'] = json_decode($update_array['dir_params'],true);
			$decoded_pass = $this->common->decode($update_array['dir_params']['password']);
			$update_array['dir_params']['password'] = $this->common->encrypt($decoded_pass);
			// END
			$this->response ( array (
				'status'=>true,
				'data' => $update_array,
				'success' => "SIP Device updated sucessfully." 
			), 200 );
		}
	}

	function _user_sipdevices_read_password()
	{
		$this->api_log->write_log('API URL : ', base_url() . "" . $_SERVER['REQUEST_URI']);
		$this->api_log->write_log('Params : ', json_encode($this->postdata));
		$rawinfo    = $this->postdata;
		if(!$this->form_validation->required($rawinfo['hash_string'])){
			$this->response ( array (
				'status'=> false,
				'error' => $this->lang->line ( 'required_sip_device' ) 
			), 400 );		
		}
		$string     = trim($rawinfo['hash_string']);
		$string_cnt = strlen($string);
		$status_code = substr($string, -3);
		if ($status_code != '303') {
			$this->api_log->write_log('ERROR', "Hash code not found");
			$this->response(array(
				'status' => false,
				'error'  => $this->lang->line('something_wrong_contact_admin')
			), 400);
		}
		$new_count  = astpp_n($string_cnt) - 3;
		$sip_number = substr($string, 0, $new_count);
		$this->db->where(array("username" => $sip_number));
		$sip_devices_res = (array) $this->db->get('sip_devices')->first_row();
		if (empty($sip_devices_res)) {
			$this->api_log->write_log('ERROR', "SIP device not found");
			$this->response(array(
				'status' => false,
				'error'  => $this->lang->line('sip_device_not_found')
			), 400);
		}
		$data = (array) json_decode($sip_devices_res['dir_params']);
		$this->response(array(
			'status'   => true,
			'password' => $this->common->decode($data['password'])
		), 200);
	}
	function _user_sipdevices_delete(){
		if($this->form_validation->required($this->postdata['sipdevice_id'] == '')){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'require_sip_id' )
			), 400 );
		}
		$sip_info =(array)$this->db_model->getSelect ( "*", "sip_devices", array ('id' => $this->postdata['sipdevice_id'], 'accountid' => $this->postdata['id'] ) )->result_array();
		if (!empty($sip_info)){
			$this->db->where("id IN (".$this->postdata['sipdevice_id'].") ");
			$this->db->delete("sip_devices");
			$this->response ( array (
				'status' => true,
				'success' =>  $this->lang->line ( 'sipdevice_deleted' )  
			), 200 );
		} else {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'sipdevice_not_found' ) 
			), 400 );
		}
	}

	function _user_sipdevices_read() {
		$postdata = $this->postdata;
		if($this->form_validation->required($postdata['sipdevice_id'] == '')){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'require_sip_id' )
			), 400 );
		}else{
			$sipdeviceinfo = (array)$this->db->get_where ("sip_devices",array("id"=>$postdata['sipdevice_id'],'accountid' => $postdata['id']))->first_row();
		    if(empty($sipdeviceinfo)){
				$this->response ( array (
					'status'  => false,
					'error'   => $this->lang->line ( 'sipdevice_not_found' )
				), 400 );
	        }else{
	        	$dir_params = json_decode($sipdeviceinfo['dir_params'],true);
	        	$dir_vars = json_decode($sipdeviceinfo['dir_vars'],true);
	        	// Kinjal issue no 3846
	        	$sipdeviceinfo['password'] = $this->common->decode($dir_params['password']);
	        	// END
	        	$sipdeviceinfo['vm-enabled'] = $dir_params['vm-enabled'];
	        	$sipdeviceinfo['effective_caller_id_name'] = $dir_vars['effective_caller_id_name'];
	        	$sipdeviceinfo['effective_caller_id_number'] = $dir_vars['effective_caller_id_number'];
	        	$sipdeviceinfo['status'] = $sipdeviceinfo['status'] == '0' ? 'Active' : 'Inactive'  ;
	        	$sipdeviceinfo['vm-enabled'] = $dir_params['vm-enabled'];
	        	$sipdeviceinfo['vm-password'] = $dir_params['vm-password'];
	        	$sipdeviceinfo['vm-attach-file'] = $dir_params['vm-attach-file'];
	        	$sipdeviceinfo['vm-mailto'] = $dir_params['vm-mailto'];
	        	$sipdeviceinfo['vm-email-all-messages'] = $dir_params['vm-email-all-messages'];
	        	$sipdeviceinfo['vm-keep-local-after-email'] = $dir_params['vm-keep-local-after-email'];
	        	unset($sipdeviceinfo['id'],$sipdeviceinfo['reseller_id'],$sipdeviceinfo['accountid'],$sipdeviceinfo['sip_profile_id']);
	        	unset($sipdeviceinfo['call_waiting'],$sipdeviceinfo['last_modified_date'],$sipdeviceinfo['creation_date'],$sipdeviceinfo['dir_params'],$sipdeviceinfo['dir_vars']);
	        	$this->response ( array (
					'status'=>true,
					'data' => $sipdeviceinfo,
					'success' => $this->lang->line( "read_sipdevice" ) 
				), 200 );
	        }
		}
	}
}