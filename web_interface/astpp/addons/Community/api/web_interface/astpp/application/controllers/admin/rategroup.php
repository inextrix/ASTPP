	<?php

//require APPPATH . '/libraries/API_Controller.php';
require APPPATH . '/controllers/common/account.php';

#[\AllowDynamicProperties]
class Rategroup extends Account {
	
	protected $postdata = "";
	function __construct() {
		parent::__construct ();
		$this->load->model ( 'common_model' );
		$this->load->model ( 'db_model' );
		$this->load->library('Form_validation');
		$this->accountinfo = $this->get_account_info();
		if($this->accountinfo['type'] != -1 && $this->accountinfo ['type'] != 1 && $this->accountinfo ['type'] != 2 ){
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line ( 'error_invalid_key' )
			), 400 );
		}
		$rawinfo = $this->post();
		$this->postdata = array();
		foreach ( $rawinfo as $key => $value ) {
			$this->postdata [$key] = $this->_xss_clean ( $value, TRUE );
		}
		
	}

	public function index() {
		$function = isset ( $this->postdata ['action'] ) ? $this->postdata ['action'] : '';
		$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
		$this->api_log->write_log ( 'Params : ', json_encode($this->postdata) );
		// Kinjal issue no 4532
		if($this->accountinfo['type'] == '-1' || $this->accountinfo['type'] == '2'){
			$accountid = $this->postdata ['id'];
			$type = array(-1,2);
			$where = array('id'=>$accountid,'deleted'=>0,'status'=>0);
			$this->db->where_in('type',$type);
		}else{
			$where = array('id' => $this->accountinfo['id'] , 'type' => 1,'deleted'=>0,'status'=>0);
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
		// END
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
		}die;
	}
	
	
	function _rategroup_list(){
		
		if (!isset($this->postdata['start_limit']) || $this->postdata['start_limit'] == "" || !isset($this->postdata['end_limit']) || $this->postdata['end_limit'] == ""){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_param_missing' )
			), 400 );
		}else{
			if(astpp_le($this->postdata['start_limit'], 0) || astpp_le($this->postdata['end_limit'], 0)) {
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('number_greater_zero')
				), 400 );
			}
		$start = astpp_n($this->postdata['start_limit'])-1;
		$limit = $this->postdata['end_limit'];
		$object_where_params = $this->postdata['object_where_params'];
		
		$where = '';
		foreach($object_where_params as $object_where_key => $object_where_value) {
			if($object_where_key == "routing_type" && $object_where_value != "") {
				if(!$this->form_validation->integer($object_where_value)){
					$this->response ( array (
						'status' => false,
						'success' =>  $this->lang->line ('valid_routing_type')  
					), 400 );
				}
			}
			if($object_where_key == "initially_increment" && $object_where_value != "") {
				if(!$this->form_validation->integer($object_where_value)){
					$this->response ( array (
						'status' => false,
						'success' =>  $this->lang->line ('initially_increment_number')  
					), 400 );
				}
			}
			if($object_where_key == "inc" && $object_where_value != "") {
				if(!$this->form_validation->integer($object_where_value)){
					$this->response ( array (
						'status' => false,
						'success' =>  $this->lang->line ('inc_number')  
					), 400 );
				}
			}
			if($object_where_key == "reseller_id" && $object_where_value != "") {
				if(!$this->form_validation->integer($object_where_value)){
					$this->response ( array (
						'status' => false,
						'success' =>  $this->lang->line ('valid_reseller_id')  
					), 400 );
				}
			}
			if($object_where_key == "status" && $object_where_value != "") {
				if(!$this->form_validation->integer($object_where_value)){
					$this->response ( array (
						'status' => false,
						'success' =>  $this->lang->line ('valid_status')  
					), 400 );
				}
			}
			if($object_where_value != '') {
				$where = $object_where_key . ' = "' . $object_where_value . '" AND ';
			}
		}
		if(!empty($where)) {
			$where = rtrim($where,"AND ");
			$this->db->where($where);
		}
		$no_of_records = (int)$limit - (int)$start;
		if($this->accountinfo['type'] == 1){
		$where = array(
                "status != " => "2",
                "reseller_id" => $this->accountinfo['id']
            );
		}else{
			$where = array(
                "status != " => "2",
            );
		}
		$available_rategroups = $this->db_model->Select("id as rategroup_id,name,routing_prefix,routing_type,initially_increment,inc,markup,call_count,reseller_id,creation_date,last_modified_date,status", "pricelists", $where, "id", "ASC",$no_of_records,$start);
		$available_rategroup = $available_rategroups->result_array();
		$count = $available_rategroups->num_rows();
		if (empty($available_rategroup)) {
		$this->response ( array (
			'total_count'=>0,
			'data' => $available_rategroup,
			'error'   => $this->lang->line ( 'no_records_found' )
			), 200 );
		}else{
			foreach ($available_rategroup as $key => $value) {
				if ($this->accountinfo['type'] == 1) {
				unset($value['reseller_id']);
				unset($value['routing_type']);
			}
			$available_rategroup[$key] = $value;
			$available_rategroup[$key]['creation_date'] = $this->common->convert_GMT_to('','',$available_rategroup[$key]['creation_date'],$this->accountinfo['timezone_id']);
			$available_rategroup[$key]['last_modified_date'] = $this->common->convert_GMT_to('','',$available_rategroup[$key]['last_modified_date'],$this->accountinfo['timezone_id']);
				if ($this->accountinfo['type'] != 1) {
					$available_rategroup[$key]['reseller_id'] = $this->common->reseller_select_value('first_name,last_name,number,company_name','accounts',$value['reseller_id']);
				}
				if ($this->accountinfo['type'] != 1) {
					if(astpp_eq0($value['routing_type'])) {
						$available_rategroup[$key]['routing_type'] = "LCR";
					}
					elseif ($value['routing_type'] == 1) {
						$available_rategroup[$key]['routing_type'] = "COST";
					}
					else{
						$available_rategroup[$key]['routing_type'] = "PRIORITY";
					}
				}
			}
			$this->response ( array (
					'total_count'=>$count,
					'data' => $available_rategroup,
					'success' => $this->lang->line( "rategroup_list_information" )
				), 200 );
			}
		}
	}

	function _rategroup_delete(){
		if (! isset ( $this->postdata ['rategroup_id']) || $this->postdata['rategroup_id'] == '') {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_param_missing' ) . "integer:rategroup_id" 
			), 400 );
		}
		if(!$this->form_validation->numeric_with_comma($this->postdata ['rategroup_id'])){
			$this->response ( array (
				'status' => false,
				'success' =>  $this->lang->line ('valid_ipmap_id')  
			), 400 );
		}
		$rategroup_id = $this->postdata['rategroup_id'];
		unset ( $this->postdata ['action'] );
		if($this->accountinfo['type'] == 1){
			$where = array(
                "reseller_id" => $this->accountinfo['id'],
                'id' => $rategroup_id
            );
		}
		else{
			$where = array(
                'id' => $rategroup_id
            );		
		}
		$rategroup_info =(array)$this->db_model->getSelect ( "*", "pricelists", $where )->row_array();
		if (!empty($rategroup_info)){
			$this->db->where("id IN (".$this->postdata['rategroup_id'].") ");
			$this->db->delete("pricelists");
			$this->response ( array (
				'status' => true,
				'success' =>  $this->lang->line ( 'rategroup_delete' )  
			), 200 );
		} else {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'rategroup_not_found' ) 
			), 400 );
		}
	}
	
	function _rategroup_create() {
		if (!isset($this->postdata['name']) || empty($this->postdata['name'])){
			$this->response ( array (
				'status' => false,	
				'error' => $this->lang->line ('name_required' ) 
			), 400 );
		}
		if (!isset($this->postdata['initially_increment']) || empty($this->postdata['initially_increment'])){
			$this->response ( array (
				'status' => false,	
				'error' => $this->lang->line ('initially_increment_required') 
			), 400 );
		}
		if(!isset($this->postdata['inc']) || empty($this->postdata['inc'])){
			$this->response ( array (
				'status' => false,	
				'error' => $this->lang->line ('inc_required' ) 
			), 400 );
		}
		
		$add_array = array();
		if($this->accountinfo['type'] == 1) {
			$add_array['reseller_id'] = $this->postdata['id'] ;		
		}
		else{
			if(!$this->form_validation->integer($this->postdata['reseller_id']) && $this->postdata['reseller_id'] != ""){
				$this->response ( array (
					'status' => true,
					'success' =>  $this->lang->line ('valid_reseller_id')  
				), 400 );
			}
			if ($this->postdata['reseller_id'] == "" || astpp_eq0($this->postdata['reseller_id'])) {
				$add_array['reseller_id'] = 0 ;
			}
			else{
				$resellerinfo = (array) $this->db->get_where('accounts', array("id" => $this->postdata['reseller_id'],"status"=>0,"type" => 1,"deleted"=>0))->first_row();
			   empty($resellerinfo) ?  $this->response ( array (
	                'status'  => false,
	                'error'   => $this->lang->line ( 'reseller_account_not_found' )
	        	), 400 ) : $add_array['reseller_id'] = $this->postdata['reseller_id'];
			}
		}
		if(!$this->form_validation->integer($this->postdata['status']) && $this->postdata['status'] != ""){
			$this->response ( array (
				'status' => true,
				'success' =>  $this->lang->line ('valid_status')  
			), 400 );
		}
		$initially_increment = $this->integer($this->postdata['initially_increment']);
		$add_array['initially_increment'] = $initially_increment == 1 ? $this->postdata['initially_increment'] : $this->response ( array (
				'status' => false,
				'error' => $this->lang->line('initially_increment_number')
			), 400 );
		$inc = $this->integer($this->postdata['inc']);
		$add_array['inc'] = $inc == 1 ? $this->postdata['inc'] : $this->response ( array (
				'status' => false,
				'error' => $this->lang->line('inc_number')
			), 400 );
		if ($this->postdata['routing_prefix'] != "") {
		$prefix_unique = (array)$this->db->get_where('pricelists', array('routing_prefix' => $this->postdata['routing_prefix']))->first_row();
		if(!empty($prefix_unique)) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('unique_routing_prefix')
			), 400 );
		}else{
		$add_array['routing_prefix'] = (is_numeric($this->postdata['routing_prefix'])) ? $this->postdata['routing_prefix'] : $this->response ( array (
				'status' => false,
				'error' => $this->lang->line('number_routing_prefix')
			), 400 );
		}}
		$add_array['creation_date'] = gmdate("Y-m-d H:i:s");
        $add_array['last_modified_date'] = gmdate("Y-m-d H:i:s");
        
		if(is_numeric($this->postdata['markup']) == 1){
			if($this->postdata['markup'] == '' || $this->postdata['markup'] <= 100 ){ 
				$add_array['markup'] = $this->postdata['markup'] ? $this->postdata['markup'] : 0;
			} else{
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('markup_valdation')
				), 400 );
			}
		}else{
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('markup_valdation_numeric')
			), 400 );
		}
		if(!$this->form_validation->greater_than($this->postdata['markup'],-1)){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('min_markup')
			), 400 );
		}
		$add_array['status'] = isset($this->postdata['status']) && ($this->postdata['status'] != "") ? $this->postdata['status'] : 0;
		$routing_type_id = (array)$this->db->get_where('pricelists', array('id' => $this->postdata['rategroup_id']))->first_row();
		if ($this->accountinfo['type'] == -1 || $this->accountinfo['type'] == 2) {
			$add_array['routing_type'] = (astpp_eq0($routing_type_id['reseller_id'])) ? $this->postdata['routing_type'] : 0;
		}else{
			$add_array['routing_type'] =  0;
		}
		$add_array['name'] = $this->postdata['name'];
		if(!$this->form_validation->numeric_with_comma($this->postdata ['trunk_id'])){
			$this->response ( array (
				'status' => false,
				'success' =>  $this->lang->line ('invalid_trunk')  
			), 400 );
		}
		if (!strpos($this->postdata['trunk_id'], ',') !== false) {
			$trunk_id = $this->common->get_field_name('id','trunks',array('status' => 0, 'id' => $this->postdata['trunk_id']));
			if($trunk_id == ''){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('trunk_not_found')	
				), 400 );
			}
		}
		$this->db->insert("pricelists", $add_array);
		$last_id = $this->db->insert_id();
		if ($this->postdata['reseller_id'] == "" || astpp_eq0($this->postdata['reseller_id'])) {
			if (strpos($this->postdata['trunk_id'], ',') !== false) {
				$trunk_ids = explode(',',$this->postdata['trunk_id']);
				foreach ($trunk_ids as $key => $value) {
					$trunk_id = $this->db_model->getSelect("GROUP_CONCAT(id) as id","trunks",array("status" => 0,"id" => $value))->row_array();
					if($value == $trunk_id['id']){
						$routing_arr = array(
							"trunk_id" => $value,
							"pricelist_id" => $last_id
						);
						$this->db->insert("routing", $routing_arr);
					}
				}
			}else{
				$routing_arr = array(
					"trunk_id" => $this->postdata['trunk_id'],
					"pricelist_id" => $last_id
				);
				$this->db->insert("routing", $routing_arr);
			}
		}
		if(!empty(($last_id)) || !empty($add_array)){
			$this->response ( array (
				'status'  => true,
				'data'    => $add_array,
				'success' => $this->lang->line ( 'rategroup_create' )  
			), 200 );
		}else {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line('rategroup_not_found')	
			), 400 );
		}
	}
	
	public function integer($str)
	{
		return (bool)preg_match('/^[\-+]?[0-9]+$/', $str);
	}
}
?>
