<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class User_notifications extends Account
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
	
	private function _user_notifications_list()
	{
		if (empty($this->postdata['end_limit']) || empty($this->postdata['start_limit']) ){
			if(!( $this->postdata['start_limit'] == '	0' || $this->postdata['end_limit'] == '0' )){
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
		$start = astpp_n($this->postdata['start_limit'])-1;
		$limit = $this->postdata['end_limit'];
		$no_of_records = (int)$limit - (int)$start;

		$object_where_params = $this->postdata['object_where_params'];
		if(!empty($object_where_params['from_date']) || !empty($object_where_params['to_date'])  ){
			$from_dates = DateTime::createFromFormat("Y-m-d H:i:s", $object_where_params['from_date']);
	       	$to_dates = DateTime::createFromFormat("Y-m-d H:i:s", $object_where_params['to_date']);
	       	if(empty($from_dates) || empty($to_dates)){
	       		$this->response ( array (
						'status' => false,
						'error' => $this->lang->line('invalid_date_format')
				), 400 );
	       	}else{
	       		$object_where_params_date['date >='] = $this->timezone->convert_to_GMT_new ( $object_where_params['from_date'], '1' , $this->accountinfo['timezone_id']);
				 $object_where_params_date['date <='] = $this->timezone->convert_to_GMT_new ( $object_where_params['to_date'], '1',$this->accountinfo['timezone_id']);
				$this->db->where($object_where_params_date);
	       	}
		}
		unset($object_where_params['to_date'],$object_where_params['from_date']);
		foreach($object_where_params as $object_where_key => $object_where_value) {
			if($object_where_value != '') {	
				$where[$object_where_key] = $object_where_value;
			}
		}
		if(!empty($where)){
			$this->db->like($where, $object_where_params );
		}
		$where = array('accountid' => $this->postdata['id'] );
		$this->db->where($where);
		$this->db->order_by('id', 'DESC');
		$query =$this->db->limit($no_of_records, $start)
			->select('*')
			->get ('mail_details');
		$count = $query -> num_rows();
		$notifications_info = $query->result_array();
		foreach ($notifications_info as $key => $notifications_value) {
			$notifications_value['date'] = $this->common->convert_GMT_to('','',$notifications_value['date'],$this->accountinfo['timezone_id']);
			$notifications_value['emailstatus'] = $notifications_value['emailstatus'] == '1' ? 'Pending' : ($notifications_value['emailstatus'] == '0' ?'Sent' : 'Failed');
			$notifications_value['smsstatus'] = $notifications_value['smsstatus'] == '1' ? 'Pending' : ($notifications_value['smsstatus'] == '0' ?'Sent' : 'Failed');
			$notifications_value['alertstatus'] = $notifications_value['alertstatus'] == '1' ? 'Pending' : ($notifications_value['alertstatus'] == '0' ?'Sent' : 'Failed');
			$notifications_value['body'] = str_replace(array("\r\n", "\n", "\r"), '', $notifications_value['body'] );
			unset($notifications_value['reseller_id'],$notifications_value['accountid'],$notifications_value['id'],$notifications_value['status'],$notifications_value['template'],$notifications_value['sip_user_name'],$notifications_value['sip_user_name'],$notifications_value['push_message_body'],$notifications_value['callkit_token'],$notifications_value['status_code'],$notifications_value['cc']);
			$notificationsinfo[] =$notifications_value;
		}
    	if (!empty($notificationsinfo)) {
			$this->response ( array (
				'status' => true,
				'total_count' => $count,
				'data' => $notificationsinfo,
				'success' => $this->lang->line( "notifications_history_list" )
			), 200 );
        }else{
			$this->response ( array (
				'status' => true,
				'data' => array(),
				'success' => $this->lang->line( "no_records_found" )
			), 200 );
		}
	}
	private function _user_speed_dial_delete()
	{
		if(!$this->form_validation->required($this->postdata['speed_dial_id'])){
			$this->response ( array (
				'status'=> false,
				'error' => $this->lang->line ( 'required_speed_dial' )
			), 400 );
		}
		if($this->postdata['speed_dial_id'] > 9 ){
			$this->response ( array (
				'status'=> false,
				'error' => $this->lang->line ( 'invalid_speed_dial' )
			), 400 );
		}
		$updateinfo = array(
            'number' => ''
        );
        $where = array('accountid' => $this->postdata['id'],'speed_num' => $this->postdata['speed_dial_id']);
        $this->db->where($where);
        $result = $this->db->update('speed_dial', $updateinfo);
        $last_edit_id = $this->db->affected_rows();
		if(astpp_gt($last_edit_id, 0)){
			$this->response ( array (
				'status' => true,
				'success' => $this->lang->line( "remove_speed_dial" )
			), 200 );
        }else{
			$this->response ( array (
				'status' => true,
				'success' => $this->lang->line( "blank_speed_dial" )
			), 200 );
		}
	}

	function _user_speed_dial_update() {
		$postdata = $this->postdata;
		if(!$this->form_validation->required($postdata['speed_dial_id'])){
			$this->response ( array (
				'status' => true,
				'success' => $this->lang->line( "required_speed_dial" )
			), 200 );
		}else{
			if($this->postdata['speed_dial_id'] > 9 ){
				$this->response ( array (
					'status'=> false,
					'error' => $this->lang->line ( 'invalid_speed_dial' )
				), 400 );
			}
		}
		if(!$this->form_validation->required($postdata['number'])){
			$this->response ( array (
				'status' => true,
				'success' => $this->lang->line( "required_speed_dial_number" )
			), 200 );
		}
		if(!$this->form_validation->is_natural($postdata['number'])){
			$this->response ( array (
				'status' => true,
				'success' => $this->lang->line( "invalid_speed_dial_number" )
			), 200 );
		}
		$where = array(
            "accountid" => $postdata['id']
        );
        $this->db->select('count(id) as count');
        $this->db->where($where);
        $speed_dial_result = (array) $this->db->get('speed_dial')->first_row();
	    if (astpp_eq0($speed_dial_result['count'])) {
	        for ($i = 0; $i <= 9; $i ++) {
	            $dest_number = $postdata['speed_dial_id'] == $i ? $postdata['number'] : '';
	            $data[$i] = array(
	                "number" => $dest_number,
	                "speed_num" => $i,
	                'accountid' => $postdata['id']
	            );
	        }
	        $this->db->insert_batch('speed_dial', $data);
	        $id = $this->db->affected_rows();
	        if(astpp_gt($id, 0)){
	        	$this->response ( array (
				'status' => true,
				'success' => $this->lang->line( "insert_speed_dial" )
				), 200 );
	        }else{
	        	$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('something_wrong_contact_admin')
				), 400 );
	        }
	    }else {
	        $this->db->where('speed_num', $postdata['speed_dial_id']);
	        $this->db->where('accountid', $postdata['id']);
	        $result = $this->db->update('speed_dial', array(
	            'number' => $postdata['number']
	        ));
	        if(!empty($result)){
	        	$this->response ( array (
				'status' => true,
				'success' => $this->lang->line( "update_speed_dial" )
				), 200 );
	        }else{
	        	$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('something_wrong_contact_admin')
				), 400 );
	        }
	    }
	}
}