<?php

require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Termination_rate extends Account {
	
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
	
	function _termination_list(){
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
		$where = array();
		foreach($object_where_params as $object_where_key => $object_where_value) {
			if($object_where_value != '') {	
				if(isset($object_where_key) && $object_where_key == 'code'){
					$this->db->like('pattern', '^'.$object_where_params['code']);
				}else{
					if(isset($object_where_key) && $object_where_key == 'destination'){
						$this->db->like('comment', $object_where_params['destination']);
					}
					if(isset($object_where_key) && $object_where_key == 'priority'){
						$this->db->like('precedence', $object_where_params['priority']);
					}
					if($object_where_key == 'connectcost' && $object_where_value != ""){
						if(!$this->form_validation->numeric($object_where_value)){
							$this->response ( array (
								'status' => false,
								'success' =>  $this->lang->line ('enter_correct_correctcost')  
							), 400 );
						}
					}
					if($object_where_key == 'includedseconds' && $object_where_value != ""){
						if(!$this->form_validation->integer($object_where_value)){
							$this->response ( array (
								'status' => false,
								'success' =>  $this->lang->line ('enter_correct_includedseconds')  
							), 400 );
						}
					}
					if($object_where_key == 'cost' && $object_where_value != ""){
						if(!$this->form_validation->numeric($object_where_value)){
							$this->response ( array (
								'status' => false,
								'success' =>  $this->lang->line ('enter_cost')  
							), 400 );
						}
					}
					if($object_where_key == 'init_inc' && $object_where_value != ""){
						if(!$this->form_validation->integer($object_where_value)){
							$this->response ( array (
								'status' => false,
								'success' =>  $this->lang->line ('initially_increment_number')  
							), 400 );
						}
					}
					if($object_where_key == 'inc' && $object_where_value != ""){
						if(!$this->form_validation->integer($object_where_value)){
							$this->response ( array (
								'status' => false,
								'success' =>  $this->lang->line ('inc_number')  
							), 400 );
						}
					}
					if($object_where_key == 'trunk_id' && $object_where_value != ""){
						if(!$this->form_validation->integer($object_where_value)){
							$this->response ( array (
								'status' => false,
								'success' =>  $this->lang->line ('invalid_trunk')  
							), 400 );
						}
					}
					if($object_where_key == 'status' && $object_where_value != ""){
						if(!$this->form_validation->integer($object_where_value)){
							$this->response ( array (
								'status' => false,
								'success' =>  $this->lang->line ('valid_status')  
							), 400 );
						}
					}
					$where[$object_where_key] = $object_where_value;
					unset($where['destination'],$where['priority']);
				}
			}
		}
		if(!empty($where)) {
			$this->db->where($where);
		}
		$start = astpp_n($this->postdata['start_limit'])-1;
		$limit = $this->postdata['end_limit'];
		$no_of_records = (int)$limit - (int)$start;
		$this->db->order_by('id', 'DESC');
		$available_termination_rate = $this->db->limit($no_of_records, $start)
			-> select('*')
			->from('outbound_routes')
			->get();
		$count = $available_termination_rate->num_rows();
		$available_termination_rate = $available_termination_rate->result_array();
		if (empty($available_termination_rate)) {
		$this->response ( array (
			'status'  => true,
			'error'   => $this->lang->line ( 'no_records_found' )
			), 200 );
		}else{
		$new_array = array();
		$currency_id = $this->accountinfo['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
		foreach ($available_termination_rate as $key => $value) {
			$value['termination_id'] = $value['id'];
			$value['code'] = preg_replace('/[^\d+0-9]/', '',  $value['pattern']);
			$value['connectcost'] = $value['connectcost']." ". $currency ;
			$value['cost'] = $value['cost']." ". $currency ;
			$value['pricelist_id'] = $this->common->get_field_name('name','pricelists',array('id' => $value['pricelist_id'])) ;
			$value['last_modified_date'] = $this->common->convert_GMT_to('','',$value['last_modified_date'],$this->accountinfo['timezone_id']);
			$value['creation_date'] = $this->common->convert_GMT_to('','',$value['creation_date'],$this->accountinfo['timezone_id']);
			$value['country_id'] = $this->common->get_field_name('country','countrycode',array('id' => $value['country_id'])) ;
			$value['status'] = $value['status'] == '0' ? 'Active' : 'Inactive';
			$value['trunk_id'] = $this->common->get_field_name('name','trunks', array('id' => $value['trunk_id']));
			$value['destination'] =$value['comment'];
			$value['priority'] =$value['precedence'];
			unset($value['id'],$value['precedence'],$value['pattern'],$value['comment'],$value['reseller_id'],$value['pricelist_id'],$value['country_id']);
			$new_array[] = $value;
		}
			$this->response ( array (
				'total_count'=>$count,
				'data' => $new_array,
				'success' => $this->lang->line( "termination_list_information" )
			), 200 );
		}
	}

	function _termination_rate_delete(){
		if(!$this->form_validation->required($this->postdata['termination_rate_id'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'required_termination_id' ) 
			), 400 );
		}
		if(!$this->form_validation->numeric_with_comma($this->postdata['termination_rate_id'])){
			$this->response ( array (
				'status' => true,
				'success' =>  $this->lang->line ('valid_termination_id')  
			), 200 );
		}
		$this->db->where("id IN (".$this->postdata['termination_rate_id'].") ");
		$originationrate_info =(array)$this->db_model->getSelect ( "*", "outbound_routes", array () )->first_row();
		if (!empty($originationrate_info)){
			$this->db->where("id IN (".$this->postdata['termination_rate_id'].") ");
			$this->db->delete("outbound_routes");
			$this->response ( array (
				'status' => true,
				'success' =>  $this->lang->line ( 'termination_delete' )  
			), 200 );
		} else {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'termination_not_found' ) 
			), 400 );
		}
	}
	function _termination_create() {
		$postdata = $this->postdata;
		if(!empty($postdata['trunk_id'])){
			$trunk_id = $this->common->get_field_name('id','trunks',array('id' => $postdata['trunk_id']));
			if(empty($trunk_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('trunk_not_found')
				),400 );
			}
		}else{
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('required_trunk_id')
			),400 );
		}
		if(!$this->form_validation->required($postdata['code'] ) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_rategroup_code' ) 
			), 400 );
		}
		if(!is_numeric($postdata['code']) && !empty($postdata['code']) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'valid_code' ) 
			), 400 );
		}
		if(!$this->form_validation->alpha($postdata['destination']) && !empty($postdata['destination'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'valid_or_destination' ) 
			), 400 );
		}
		if (!is_numeric($postdata['strip']) && !empty($postdata['strip'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_strip' )
			), 400 );
		}
		if (!is_numeric($postdata['prepend']) && !empty($postdata['prepend'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_prepend' )
			), 400 );
		}
		$postdata['status'] = $postdata['status'] == '0' || $postdata['status'] == '1' ? $postdata['status'] : '0'; 
		if (!is_numeric($postdata['connectcost']) && !empty($postdata['connectcost'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_correct_correctcost' )
			), 400 );
		}
		if (!is_numeric($postdata['includedseconds']) && !empty($postdata['includedseconds'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_correct_includedseconds' )
			), 400 );
		}
		if (!is_numeric($postdata['cost']) && !empty($postdata['cost'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_cost' )
			), 400 );
		}
		if (!is_numeric($postdata['priority']) && !empty($postdata['priority'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'invalid_priority' )
			), 400 );
		}
		if (!is_numeric($postdata['init_inc']) && !empty($postdata['init_inc'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_correct_initial_increment' )
			), 400 );
		}
		if (!is_numeric($postdata['inc']) && !empty($postdata['inc'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_correct_increment' )
			), 400 );
		}
		if(!empty($postdata['code'])){
			$code = "^" . $this->postdata['code'] . ".*";
			$termination_rate_info = (array)$this->db->get_where('outbound_routes',array('trunk_id' => $postdata['trunk_id'],'pattern' => $code))->result_array();
			if(!empty($termination_rate_info)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'duplicate_termination_rate' )
				), 400 );
			}
		}
		$final_arr=array(
			'comment'            =>isset($this->postdata['destination'])?$this->postdata['destination']:'',
			'status'             =>isset($postdata['status'])?$postdata['status']:'',
			'connectcost'        =>isset($this->postdata['connectcost'])?$this->postdata['connectcost']:'',
			'includedseconds'    =>isset($this->postdata['includedseconds'])?$this->postdata['includedseconds']:'',
			'cost'               =>isset($postdata['cost'])?$postdata['cost']:'',
			'init_inc'           =>isset($postdata['init_inc'])?$postdata['init_inc']:'',
			'inc'                =>isset($postdata['inc'])?$postdata['inc']:'',
			'strip'              =>isset($postdata['strip'])?$postdata['strip']:'',
			'prepend'            =>isset($postdata['prepend'])?$postdata['prepend']:'',
			'trunk_id'	         => isset($this->postdata['trunk_id'])?$this->postdata['trunk_id']:'',
			'precedence'	     => isset($this->postdata['priority'])?$this->postdata['priority']:'',
			'last_modified_date' =>gmdate('Y-m-d H:i:s'),
			'creation_date'      => gmdate('Y-m-d H:i:s'),
			'pattern'            => "^" . $this->postdata['code'] . ".*",
			'reseller_id'        => 0
			);
			$this->db->insert("outbound_routes", $final_arr);
			$last_id = $this->db->insert_id();
			if(!empty(($last_id))){
				$final_array = (array)$this->db->get_where('outbound_routes',array('id' => $last_id))->first_row();	
				$final_array['destination'] = $final_array['comment'];
				$final_array['creation_date'] = $this->common->convert_GMT_to('','',$final_array['creation_date'],$this->accountinfo['timezone_id']);
				$final_array['last_modified_date'] = $this->common->convert_GMT_to('','',$final_array['last_modified_date'],$this->accountinfo['timezone_id']);
				$final_array['code'] = str_replace(str_split('^.*'), '', $final_array['pattern']);
				unset($final_array['comment'],$final_array['reseller_id'], $final_array['pattern']);
				$this->response ( array (
					'status'  => true,
					'data'    => $final_array,
					'success' => $this->lang->line ( 'termination_create' )  
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
