<?php
require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Ip_map extends Account {

	public $postdata = '';
	function __construct() {
		parent::__construct ();
		$this->load->model ( 'db_model' );
		$this->load->library('Form_validation');
		$this->accountinfo = $this->get_account_info(); 
		$rawinfo = $this->post();
		$this->postdata = array();
		if($this->accountinfo['type'] != -1 && $this->accountinfo ['type'] != 1 && $this->accountinfo ['type'] != 2 ){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'error_invalid_key' )
			), 400 );
		}
		foreach ( $rawinfo as $key => $value ) {
			$this->postdata [$key] = $this->_xss_clean ( $value, TRUE );
		}
	}

	function index()
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

	function _ipmap_list(){
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
		if($this->accountinfo['type'] == '1'){
			$where = array('reseller_id' => $this->postdata['id']);
			$this->db->where($where);
		}
		$ipmap_list = $this->db->limit($no_of_records, $start)
			-> select('*')
			->from('ip_map')
			->get();
		$count = $ipmap_list->num_rows();
		$query = $ipmap_list->result_array();
		if($count > 0) {
			$new_array = array();
			foreach ($query as $key => $value) {
				$value['ipmap_id'] = $value['id'];
				$value['ipmap_name'] = $value['name'];
				$value['accountid'] = $this->common->build_concat_string('first_name,last_name,number,company_name','accounts',$value['accountid']); 
				$value['reseller_id'] = $this->common->reseller_select_value('first_name,last_name,number,company_name','accounts',$value['reseller_id']); 
				$value['created_date'] = $this->common->convert_GMT_to('','',$value['created_date'],$this->accountinfo['timezone_id']);
				$value['last_modified_date'] = $this->common->convert_GMT_to('','',$value['last_modified_date'],$this->accountinfo['timezone_id']);
				unset($value['id'],$value['name'],$value['context']);
				$new_array[] = $value;
			}
			$this->response( array (
				'total_count'=>$count,
				'data'=> $new_array,
				'success' => $this->lang->line ( 'ip_list_information' )
			), 200 );
			
		} else {
			$this->response ( array (
				'total_count'=>0,
				'data' => $query,
				'success' => $this->lang->line("no_records_found")
			), 200);
	}
}

	function _ipmap_create(){
        $post_array = $this->post();
 		if($this->accountinfo['type'] != '1'){
 			if(!isset($post_array['reseller_id']) || empty($post_array['reseller_id'] || $post_array['reseller_id'] >= '0') ) {
				$this->response ( array (
					'status'  => false,
					'error'    => $this->lang->line( "enter_reseller_id" )
				), 400 );
			}
 		}
        if(!isset($post_array['accountid']) || empty($post_array['accountid'])) {
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "enter_account_id" )
			), 400 );
		}
		if(!isset($post_array['name']) || empty($post_array['name'])) {
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "enter_ip_name" )
			), 400 );
		}
		if(!isset($post_array['accountid']) || empty($post_array['accountid'])) {
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "enter_account_id" )
			), 400 );
		}
		if(!isset($post_array['ip']) || empty($post_array['ip'])) {
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "enter_ip" )
			), 400 );
		}
		if(!$this->form_validation->integer($post_array['prefix'])){
			$this->response ( array (
				'status' => false,
				'success' =>  $this->lang->line ('invalid_prefix')  
			), 400 );
		}
        $reseller_id = $post_array['reseller_id'];
        if($this->accountinfo['type'] != '1'){
        	if(isset($post_array['reseller_id']) || ! empty($post_array['reseller_id'])) {
        		$this->db->where("type IN (0,3) ");
	        	if(astpp_gt($post_array['reseller_id'], 0) ){
	        		$post_data['reseller_id'] = $this->common->get_field_name('id','accounts', array('reseller_id' => $post_array['reseller_id'],'id' => $post_array['accountid']));
	        	}else{
	        		$post_data['reseller_id'] = $this->common->get_field_name('id','accounts', array('reseller_id' => 0,'id' => $post_array['accountid'],'deleted' => 0));
	        	}

			}
        }else{
        	$post_data['reseller_id'] = $this->common->get_field_name('id','accounts', array('reseller_id' => $post_array['id'],'id' => $post_array['accountid'],'deleted'=>0,'type' => 0));
        }

		if(!empty($post_data['reseller_id'])){
    		$post_array['accountid'] = $post_array['accountid'];
    		if ($this->accountinfo['type'] == '1') {
    			$post_array['reseller_id'] = $post_array['id']; 
    		}else{
    			$post_array['reseller_id'] = $reseller_id; 
    		}
        }else{
    		$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "account_not_found" )
			), 400 );
        }
		if(! $this->form_validation->valid_ip($post_array['ip'])){
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line( "valid_ip" )
			), 400 );

		}
		$this->db->select('prefix,ip');
        $this->db->where(['prefix' => $post_array['prefix'],'ip' => $post_array['ip']]);
        $ip_prefix = (array) $this->db->get('ip_map')->first_row();
        if (!empty($ip_prefix)) {
            $this->response ( array(
                'status'  => false,
                "error" => $this->lang->line( "unique_ip" )
            ), 400 );
        }
        $post_array['status'] = $post_array['status'] == '0' || $post_array['status'] == '1' ? $post_array['status'] : '0'; 
        $data = array(
            'created_date' => gmdate('Y-m-d H:i:s'),
            'last_modified_date' => gmdate('Y-m-d H:i:s'),
            'name' => $post_array['name'],
            'ip' => $post_array['ip'],
            'prefix' => $post_array['prefix'],
            'accountid' => $post_array['accountid'],
            'reseller_id' => $post_array['reseller_id'],
            'status' => $post_array['status'],
            'context' => 'default'
        );
        $this->db->insert("ip_map", $data);
        if(!empty($data)){
        	$data['created_date'] = $this->common->convert_GMT_to('','',$data['created_date'],$this->accountinfo['timezone_id']);
			$data['last_modified_date'] = $this->common->convert_GMT_to('','',$data['last_modified_date'],$this->accountinfo['timezone_id']);
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

	function _ipmap_delete()
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
					'success' =>  $this->lang->line ('valid_ipmap_id')  
				), 400 );
			}
			if($this->accountinfo['type'] != '1'){
				$ip_mapInfo = $this->db->get_where('ip_map',array('id'=>$postdata['ipmap_id']))->result_array();
			}else{
				$ip_mapInfo = $this->db->get_where('ip_map',array('id'=>$postdata['ipmap_id'],'reseller_id' => $postdata['id']))->result_array();
			}
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
