<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Notifications extends Account
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
		if($this->accountinfo['type'] != '-1'  && $this->accountinfo ['type'] != '2'  && $this->accountinfo ['type'] != '1' ){
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
		$accountid = $this->postdata ['id'];
		if($this->accountinfo['type'] == '1'){
			$where = array('id' => $this->accountinfo['id'] , 'type' => 1);
		}else{
			$type = array(-1,2);
			$where = array('id'=>$accountid,'deleted'=>0,'status'=>0);
		}
		$this->db->where($where);
		$this->db->where_in('type',$type);
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
	
	private function _reseller_notifications_list(){
		$this->_notifications_list();
	}
	private function _notifications_list()
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
	       		if($this->postdata['action'] != 'reseller_notifications_list'){
	       			$object_where_params_date['date >='] = $this->timezone->convert_to_GMT_new ( $object_where_params['from_date'], '1' , $this->accountinfo['timezone_id']);
					$object_where_params_date['date <='] = $this->timezone->convert_to_GMT_new ( $object_where_params['to_date'], '1',$this->accountinfo['timezone_id']);
	       		}else{
	       			$object_where_params_date['date >='] =  $object_where_params['from_date'];
					$object_where_params_date['date <='] =  $object_where_params['to_date'];
	       		}
				$this->db->where($object_where_params_date);
	       	}
		}
		unset($object_where_params['to_date'],$object_where_params['from_date']);
		foreach($object_where_params as $object_where_key => $object_where_value) {
			if($object_where_value != '') {	
				if(isset($object_where_key['account']) || $object_where_key == 'account'){
					$this->db->where('accountid', $object_where_value);
				}else{
					$where[$object_where_key] = $object_where_value;
				}
				if(isset($object_where_key) && $object_where_key == 'subject'){
					$like_array['subject like'] = $object_where_params['subject'].'%';
				}
				if(isset($object_where_key) && $object_where_key == 'body'){
					$like_array['body like'] = $object_where_params['body'].'%';
				}
			}
		}
		if(!empty($where)) {
			unset($where['subject'],$where['body']); 
			$this->db->where($where);
		}
		if(!empty($like_array)) {
			$this->db->where($like_array); 
		}
		$this->db->order_by('id', 'DESC');
		if($this->accountinfo['type'] == '1' && $this->postdata['action'] != 'reseller_notifications_list'){
			$this->db->where('reseller_id', $this->postdata['id']); 
		}
		if($this->postdata['action'] == 'reseller_notifications_list'){
			$this->db->where('accountid', $this->postdata['id']); 
		}
		$query =$this->db->limit($no_of_records, $start)
			->select('*')
			->get ('mail_details');
		$count = $query -> num_rows();
		$notifications_info = $query->result_array();
		foreach ($notifications_info as $key => $notifications_value) {
			if($this->postdata['action'] != 'reseller_notifications_list'){
				$notifications_value['date'] = $this->common->convert_GMT_to('','',$notifications_value['date'],$this->accountinfo['timezone_id']);
			}
			$notifications_value['email_status'] = $notifications_value['status'] == '1' ? 'Pending' : ($notifications_value['status'] == '0' ?'Sent' : 'Failed');
			$notifications_value['notification_id'] = $notifications_value['id'];
			$notifications_value['body'] = str_replace(array("\r\n", "\n", "\r"), '', $notifications_value['body'] );
			if($this->postdata['action'] == 'reseller_notifications_list'){
				unset($notifications_value['to'],$notifications_value['to'],$notifications_value['sms_body'],$notifications_value['to_number'],$notifications_value['notification_id']);
			}
			unset($notifications_value['reseller_id'],$notifications_value['accountid'],$notifications_value['id'],$notifications_value['status'],$notifications_value['template'],$notifications_value['sip_user_name'],$notifications_value['push_message_body'],$notifications_value['callkit_token'],$notifications_value['status_code'],$notifications_value['cc']);
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
}