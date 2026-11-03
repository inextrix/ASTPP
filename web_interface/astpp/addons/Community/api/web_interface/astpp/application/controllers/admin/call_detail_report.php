<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Call_detail_report extends Account
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
		$accountid = $this->accountinfo ['id'];
		if($this->accountinfo['type'] == '1'){
			$where = array('id' => $this->accountinfo['id'] , 'type' => 1);
		}else{
			$type = array(-1,2);
			$where = array('id'=>$accountid,'deleted'=>0,'status'=>0);
		}
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
	
	private function _customer_cdrs()
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
		$from_currency = Common_model::$global_config['system_config']['base_currency'];
		$to_currency = $this->common->get_field_name('currency', 'currency', $this->accountinfo['currency_id']);
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
	       		$object_where_params_date['callstart >='] = $this->timezone->convert_to_GMT_new ( $object_where_params['from_date'], '1' , $this->accountinfo['timezone_id']);
				 $object_where_params_date['callstart <='] = $this->timezone->convert_to_GMT_new ( $object_where_params['to_date'], '1',$this->accountinfo['timezone_id']);
				$this->db->where($object_where_params_date);
	       	}
		}
		unset($object_where_params['to_date'],$object_where_params['from_date'],$object_where_params['display_records']);
		foreach($object_where_params as $object_where_key => $object_where_value) {
			if($object_where_value != '') {	
				if(isset($object_where_key) && $object_where_key == 'destination'){
					$this->db->where('notes', $object_where_params['destination'] );
				}
				if(isset($object_where_key) && $object_where_key == 'duration'){
					$duration = explode(':', $object_where_params['duration']);
                    if (isset($duration[0]) && isset($duration[1])) {
                        if (is_numeric($duration[0]) && is_numeric($duration[1])) {
                            $object_where_params['duration'] = (60 * astpp_n($duration[0])) + astpp_n($duration[1]);
                        }
                    }
					$this->db->where('billseconds' , $object_where_params['duration']);
				}
				if(isset($object_where_key) && $object_where_key == 'code'){
					$this->db->like('pattern', '^'.$object_where_params['code'] );
				}
				$where[$object_where_key] = $object_where_value;
			}
		}
		if(!empty($where)){
			unset($where['destination'],$where['code'],$where['duration']);
			$this->db->like($where, $object_where_params );
		}
	 	if ($this->accountinfo['type'] == '1') {
			$this->db->where('reseller_id', $this->postdata['id']);
	 	} 
		$this->db->where_in('type',array(0,3));
		$this->db->order_by("callstart", "desc");
		$this->db->limit($no_of_records, $start);
        $this->db->select('callstart,sip_user,callerid,call_direction,callednum,notes,billseconds,disposition,debit,is_recording,country_id,pattern,cost,accountid,pricelist_id,calltype,trunk_id');
        $result = $this->db->get('cdrs');
        $count = $result -> num_rows();
        $cdrs_info = $result->result_array();
		foreach ($cdrs_info as $key => $cdrs_value) {  
            $show_seconds = $this->postdata['object_where_params']['display_records'] == 'minutes' || $this->postdata['object_where_params']['display_records'] == 'seconds' ? $this->postdata['object_where_params']['display_records'] : 'minutes';
            $cdrs_value['duration'] = ($show_seconds == 'minutes') ? (astpp_gt($cdrs_value['billseconds'], 0)) ? sprintf('%02d', astpp_n($cdrs_value['billseconds']) / 60) . ":" . sprintf('%02d', astpp_n($cdrs_value['billseconds']) % 60) : "00:00" : $cdrs_value['billseconds'];
            $cdrs_value['callstart'] = $this->common->convert_GMT_to('','',$cdrs_value['callstart'],$this->accountinfo['timezone_id']);
            $cdrs_value['debit'] = $this->common_model->calculate_currency_customer($cdrs_value['debit'],$from_currency,$to_currency,true,true)." ".$to_currency; 
            $cdrs_value['cost'] = $this->common_model->calculate_currency_customer($cdrs_value['cost'],$from_currency,$to_currency,true,true)." ".$to_currency; 
            $cdrs_value['accountid'] = $this->common->reseller_select_value('first_name,last_name,number,company_name','accounts',$cdrs_value['accountid']); 
            $cdrs_value['country_id'] = $this->common->get_field_name('country','countrycode',array('id' => $cdrs_value['country_id'])) ;
            $cdrs_value['pricelist_id'] = $this->common->get_field_name('name','pricelists',array('id' => $cdrs_value['pricelist_id'])) ;
            $cdrs_value['trunk_id'] = $this->common->get_field_name('name','trunks',array('id' => $cdrs_value['trunk_id'])) ;
            $cdrs_value['destination'] = $cdrs_value['notes'] ;
            $cdrs_value['code'] =  preg_replace('/[^\d+0-9]/', '',  $cdrs_value['pattern']);
            if($this->accountinfo['type'] == '1'){
            	unset($cdrs_value['calltype']);
            }
            unset($cdrs_value['notes'],$cdrs_value['billseconds'],$cdrs_value['pattern'],$cdrs_value['notes'],$cdrs_value['is_recording']);
			$cdrsinfo[] =$cdrs_value;
		}
    	if (!empty($cdrsinfo)) {
			$this->response ( array (
				'status' => true,
				'total_count' => $count,
				'data' => $cdrsinfo,
				'success' => $this->lang->line( "cdrs_list" )
			), 200 );
        }else{
			$this->response ( array (
				'status' => true,
				'data' => array(),
				'success' => $this->lang->line( "no_records_found" )
			), 200 );
		}
	}

	private function _provider_cdrs()
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
		$from_currency = Common_model::$global_config['system_config']['base_currency'];
		$to_currency = $this->common->get_field_name('currency', 'currency', $this->accountinfo['currency_id']);
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
	       		$object_where_params_date['callstart >='] = $this->timezone->convert_to_GMT_new ( $object_where_params['from_date'], '1' , $this->accountinfo['timezone_id']);
				 $object_where_params_date['callstart <='] = $this->timezone->convert_to_GMT_new ( $object_where_params['to_date'], '1',$this->accountinfo['timezone_id']);
				$this->db->where($object_where_params_date);
	       	}
		}
		unset($object_where_params['to_date'],$object_where_params['from_date'],$object_where_params['display_records']);
		foreach($object_where_params as $object_where_key => $object_where_value) {
			if($object_where_value != '') {	
				if(isset($object_where_key) && $object_where_key == 'destination'){
					$this->db->where('notes', $object_where_params['destination'] );
				}
				if(isset($object_where_key) && $object_where_key == 'duration'){
					$duration = explode(':', $object_where_params['duration']);
                    if (isset($duration[0]) && isset($duration[1])) {
                        if (is_numeric($duration[0]) && is_numeric($duration[1])) {
                            $object_where_params['duration'] = (60 * astpp_n($duration[0])) + astpp_n($duration[1]);
                        }
                    }
					$this->db->where('billseconds' , $object_where_params['duration']);
				}
				if(isset($object_where_key) && $object_where_key == 'code'){
					$this->db->like('pattern', '^'.$object_where_params['code'] );
				}
				if(isset($object_where_key) && $object_where_key == 'accountid'){
					$this->db->where('provider_id', $object_where_params['accountid'] );
				}
				$where[$object_where_key] = $object_where_value;
			}
		}
		if(!empty($where)){
			unset($where['destination'],$where['code'],$where['duration'],$where['accountid']);
			$this->db->where($where, $object_where_params );
		}
		$this->db->where('trunk_id !=', '');
		$this->db->order_by("callstart", "desc");
		$this->db->limit($no_of_records, $start);
        $this->db->select('calltype,callstart,sip_user,call_direction,country_id,callerid,callednum,pattern,notes,billseconds,provider_call_cost,disposition,provider_id,cost');
        $result = $this->db->get('cdrs');
        $count = $result -> num_rows();
        $cdrs_info = $result->result_array();
		foreach ($cdrs_info as $key => $cdrs_value) { 
            $show_seconds = $this->postdata['object_where_params']['display_records'] == 'minutes' || $this->postdata['object_where_params']['display_records'] == 'seconds' ? $this->postdata['object_where_params']['display_records'] : 'minutes';
            $cdrs_value['duration'] = ($show_seconds == 'minutes') ? (astpp_gt($cdrs_value['billseconds'], 0)) ? sprintf('%02d', astpp_n($cdrs_value['billseconds']) / 60) . ":" . sprintf('%02d', astpp_n($cdrs_value['billseconds']) % 60) : "00:00" : $cdrs_value['billseconds'];
            $cdrs_value['callstart'] = $this->common->convert_GMT_to('','',$cdrs_value['callstart'],$this->accountinfo['timezone_id']);
            $cdrs_value['cost'] = $this->common_model->calculate_currency_customer($cdrs_value['cost'],$from_currency,$to_currency,true,true)." ".$to_currency; 
            $cdrs_value['accountid'] = $this->common->reseller_select_value('first_name,last_name,number,company_name','accounts',$cdrs_value['provider_id']); 
            $cdrs_value['country_id'] = $this->common->get_field_name('country','countrycode',array('id' => $cdrs_value['country_id'])) ;
            $cdrs_value['destination'] = $cdrs_value['notes'] ;
            $cdrs_value['code'] =  preg_replace('/[^\d+0-9]/', '',  $cdrs_value['pattern']);
            unset($cdrs_value['notes'],$cdrs_value['billseconds'],$cdrs_value['pattern'],$cdrs_value['provider_id'],$cdrs_value['is_recording'],$cdrs_value['provider_call_cost']);
			$cdrsinfo[] =$cdrs_value;
		}
    	if (!empty($cdrsinfo)) {
			$this->response ( array (
				'status' => true,
				'total_count' => $count,
				'data' => $cdrsinfo,
				'success' => $this->lang->line( "provider_cdrs_list" )
			), 200 );
        }else{
			$this->response ( array (
				'status' => true,
				'data' => array(),
				'success' => $this->lang->line( "no_records_found" )
			), 200 );
		}
	}
	private function _reseller_cdrs_list()
	{
		$this->_reseller_cdrs();
	}
	private function _reseller_cdrs()
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
		$from_currency = Common_model::$global_config['system_config']['base_currency'];
		$to_currency = $this->common->get_field_name('currency', 'currency', $this->accountinfo['currency_id']);
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
	       		$object_where_params_date['callstart >='] = $this->timezone->convert_to_GMT_new ( $object_where_params['from_date'], '1' , $this->accountinfo['timezone_id']);
				 $object_where_params_date['callstart <='] = $this->timezone->convert_to_GMT_new ( $object_where_params['to_date'], '1',$this->accountinfo['timezone_id']);
				$this->db->where($object_where_params_date);
	       	}
		}
		unset($object_where_params['to_date'],$object_where_params['from_date'],$object_where_params['display_records']);
		foreach($object_where_params as $object_where_key => $object_where_value) {
			if($object_where_value != '') {	
				if(isset($object_where_key) && $object_where_key == 'destination'){
					$this->db->where('notes', $object_where_params['destination'] );
				}
				if(isset($object_where_key) && $object_where_key == 'duration'){
					$this->db->where('billseconds' , $object_where_params['duration']);
				}
				if(isset($object_where_key) && $object_where_key == 'code'){
					$this->db->where('pattern', '^'.$object_where_params['code'] );
				}
				$where[$object_where_key] = $object_where_value;
			}
		}
		if(!empty($where)){
			unset($where['destination'],$where['code'],$where['duration']);
			$this->db->where($where, $object_where_params );
		}
		if($this->accountinfo['type'] == '1' && $this->postdata['action'] != 'reseller_cdrs_list'){
			$where = array(
                "reseller_id" => $this->postdata['id'],
                "accountid <>" => $this->postdata['id']
            );
			$this->db->where($where);
		}
		$this->db->order_by("callstart", "desc");
		$this->db->limit($no_of_records, $start);
		if($this->postdata['action'] != 'reseller_cdrs_list'){
			 $this->db->select('callstart,callerid,call_direction,callednum,notes,billseconds,disposition,debit,country_id,pattern,cost,accountid,pricelist_id,calltype,trunk_id');
		}else{
			$this->db->where('accountid', $this->postdata['id']);
			$this->db->select('callstart,callerid,callednum,notes,billseconds,debit,cost,disposition,calltype');
		}
 		$result = $this->db->get('reseller_cdrs');       
        $count = $result -> num_rows();
        $reseller_cdrs_info = $result->result_array();
		foreach ($reseller_cdrs_info as $key => $cdrs_value) {  
            $show_seconds = $this->postdata['object_where_params']['display_records'] == 'minutes' || $this->postdata['object_where_params']['display_records'] == 'seconds' ? $this->postdata['object_where_params']['display_records'] : 'minutes';
            $cdrs_value['duration'] = ($show_seconds == 'minutes') ? (astpp_gt($cdrs_value['billseconds'], 0)) ? sprintf('%02d', astpp_n($cdrs_value['billseconds']) / 60) . ":" . sprintf('%02d', astpp_n($cdrs_value['billseconds']) % 60) : "00:00" : $cdrs_value['billseconds'];
            $cdrs_value['callstart'] = $this->common->convert_GMT_to('','',$cdrs_value['callstart'],$this->accountinfo['timezone_id']);
            $cdrs_value['debit'] = $this->common_model->calculate_currency_customer($cdrs_value['debit'],$from_currency,$to_currency,true,true)." ".$to_currency; 
            $cdrs_value['cost'] = $this->common_model->calculate_currency_customer($cdrs_value['cost'],$from_currency,$to_currency,true,true)." ".$to_currency; 
            $cdrs_value['accountid'] = $this->common->reseller_select_value('first_name,last_name,number,company_name','accounts',$cdrs_value['accountid']); 
            $cdrs_value['country_id'] = $this->common->get_field_name('country','countrycode',array('id' => $cdrs_value['country_id'])) ;
            $cdrs_value['pricelist_id'] = $this->common->get_field_name('name','pricelists',array('id' => $cdrs_value['pricelist_id'])) ;
            $cdrs_value['trunk_id'] = $this->common->get_field_name('name','trunks',array('id' => $cdrs_value['trunk_id'])) ;
            $cdrs_value['destination'] = $cdrs_value['notes'] ;
            $cdrs_value['code'] =  preg_replace('/[^\d+0-9]/', '',  $cdrs_value['pattern']);
            if( $this->postdata['action'] == 'reseller_cdrs_list'){
            	unset($cdrs_value['cost'],$cdrs_value['accountid'],$cdrs_value['country_id'],$cdrs_value['trunk_id'],$cdrs_value['pricelist_id'],$cdrs_value['code']);
            }
            unset($cdrs_value['notes'],$cdrs_value['billseconds'],$cdrs_value['pattern'],$cdrs_value['notes'],$cdrs_value['is_recording']);
			$cdrsinfo[] =$cdrs_value;
		}
    	if (!empty($cdrsinfo)) {
			$this->response ( array (
				'status' => true,
				'total_count' => $count,
				'data' => $cdrsinfo,
				'success' => $this->lang->line( "cdrs_list" )
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