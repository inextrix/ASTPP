<?php

require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Origination_rate extends Account {
	
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
		if($this->accountinfo['type'] != '-1' && $this->accountinfo ['type'] != '1' && $this->accountinfo ['type'] != '2' ){
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
	
	function _origination_list(){
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
					$this->db->where('pattern', '^'.$object_where_params['code'].'.*');
				}else{
					if(isset($object_where_key) && $object_where_key == 'destination'){
						$where['comment'] = $object_where_value;
					}
					if($object_where_key == 'country_id' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('invalid_country')  
                            ), 400 );
                        }
                    }
					if($object_where_key == 'connectcost' && $object_where_value!= "" ){
                        if(!$this->form_validation->numeric($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('invalid_connectcost')  
                            ), 400 );
                        }
                    }
					if($object_where_key == 'includedseconds' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('enter_correct_includedseconds')  
                            ), 400 );
                        }
                    }
					if($object_where_key == 'cost' && $object_where_value!= "" ){
                        if(!$this->form_validation->numeric($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('enter_cost')  
                            ), 400 );
                        }
                    }
					if($object_where_key == 'init_inc' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('integer_initial_increment')  
                            ), 400 );
                        }
                    }
					if($object_where_key == 'inc' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('integer_increment')  
                            ), 400 );
                        }
                    }
					if($object_where_key == 'reseller_id' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('numeric_reseller_id')  
                            ), 400 );
                        }
                    }
					if($object_where_key == 'pricelist_id' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('valid_pricelist_id')  
                            ), 400 );
                        }
                    }
					if($object_where_key == 'status' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('valid_status')  
                            ), 400 );
                        }
                    }
					$where[$object_where_key] = $object_where_value;
					unset($where['destination']);
				}
			}
		}
		if(!empty($where)) {
			$this->db->where($where);
		}
		$start = astpp_n($this->postdata['start_limit'])-1;
		$limit = $this->postdata['end_limit'];
		$no_of_records = (int)$limit - (int)$start;
		if($this->accountinfo['type'] == '1'){
			$where = array('reseller_id' => $this->postdata['id']);
			$this->db->where($where);
		}
		$available_origination_rate = $this->db->limit($no_of_records, $start)
			-> select('*')
			->from('routes')
			->get();
		$count = $available_origination_rate->num_rows();
		$available_origination_rate = $available_origination_rate->result_array();
		if (empty($available_origination_rate)) {
		$this->response ( array (
			'status'  => true,
			'error'   => $this->lang->line ( 'no_records_found' )
			), 200 );
		}else{
		$new_array = array();
		$currency_id = $this->accountinfo['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
		foreach ($available_origination_rate as $key => $value) {
			$value['origination_id'] = $value['id'];
			$value['code'] = preg_replace('/[^\d+0-9]/', '',  $value['pattern']);
			$value['connectcost'] = $value['connectcost']." ". $currency ;
			$value['cost'] = $value['cost']." ". $currency ;
			$value['pricelist_name'] = $this->common->get_field_name('name','pricelists',array('id' => $value['pricelist_id'])) ;
			$value['reseller_name'] = $this->common->reseller_select_value('first_name,last_name,number,company_name','accounts',$value['reseller_id']); 
			$value['last_modified_date'] = $this->common->convert_GMT_to('','',$value['last_modified_date'],$this->accountinfo['timezone_id']);
			$value['creation_date'] = $this->common->convert_GMT_to('','',$value['creation_date'],$this->accountinfo['timezone_id']);
			$value['country_name'] = $this->common->get_field_name('country','countrycode',array('id' => $value['country_id'])) ;
			$value['destination'] =$value['comment'];
			unset($value['id'],$value['pattern'],$value['country_id'],$value['reseller_id'],$value['precedence'],$value['call_type'],$value['pattern'],$value['routing_type'],$value['percentage'],$value['call_count'],$value['trunk_id'],$value['accountid'],$value['comment']);
			$new_array[] = $value;
		}
			$this->response ( array (
				'total_count'=>$count,
				'data' => $new_array,
				'success' => $this->lang->line( "or_list_information" )
			), 200 );
		}
	}
	function _origination_delete(){
		if(!$this->form_validation->required($this->postdata['origination_id'])){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'required_origination_id' ) 
			), 400 );
		}
		if(!$this->form_validation->numeric_with_comma($this->postdata['origination_id'])){
			$this->response ( array (
				'status' => false,
				'success' =>  $this->lang->line ('valid_origination_id')  
			), 400 );
		}
		if($this->accountinfo['type'] == '1'){
			$originationrate_info =(array)$this->db_model->getSelect ( "*", "routes", array ('id' => $this->postdata['origination_id'],'reseller_id' => $this->postdata['id']) )->first_row();	
		}else{
			$originationrate_info =(array)$this->db_model->getSelect ( "*", "routes", array ('id' => $this->postdata['origination_id']) )->first_row();
		}
		
		if (!empty($originationrate_info)){
			$this->db->where("id IN (".$this->postdata['origination_id'].") ");
			$this->db->delete("routes");
			$this->response ( array (
				'status' => true,
				'success' =>  $this->lang->line ( 'or_delete' )  
			), 200 );
		} else {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'or_not_found' ) 
			), 400 );
		}
	}
	function _origination_create() {
		$postdata = $this->postdata;
		if($this->accountinfo['type'] != '1'){
			if(!$this->form_validation->required($postdata['reseller_id'] ) ){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'enter_reseller_id' ) 
				), 400 );
			}else{
				if($postdata['reseller_id'] != '0'){
					$reseller_id =  $this->common->get_field_name('id','accounts',array('id' => $postdata['reseller_id'],'type' => 1, 'deleted' => 0));
					if(empty($reseller_id)){
						$this->response ( array (
							'status' => false,
							'error' => $this->lang->line ( 'valid_reseller_id' ) 
						), 400 );
					}
				}
			}
		}
		if(!$this->form_validation->required($postdata['rategroup_id'] ) ){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'enter_rategroup_id' ) 
			), 400 );
		}else{
			if($this->accountinfo['type'] == '1'){
				$pricelist_id =  $this->common->get_field_name('id','pricelists',array('reseller_id' => $postdata['id'],'id'=>$postdata['rategroup_id']));
			}else{
				$pricelist_id =  $this->common->get_field_name('id','pricelists',array('reseller_id' => $postdata['reseller_id'],'id'=>$postdata['rategroup_id']));
			}
			if(empty($pricelist_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'rategroup_not_found' ) 
				), 400 );
			}
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
		if(!empty($postdata['country_id'])){
			$country_id = $this->common->get_field_name('id','countrycode',array('id' => $postdata['country_id']));
			if(empty($country_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'valid_country_id' ) 
				), 400 );
			}
		}
		if(!empty($postdata['call_type'])){
			$call_type_id = $this->common->get_field_name('id','calltype',array('id' => $postdata['call_type']));
			if(empty($call_type_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'call_type_not_found' ) 
				), 400 );
			}
		}
		$postdata['status'] = $postdata['status'] == '0' || $postdata['status'] == '1' ? $postdata['status'] : '0'; 
		$connectcost = isset($this->postdata['connectcost']) && ($this->postdata['connectcost'] != "") ? $this->common_model->add_calculate_currency($this->postdata['connectcost'], '', '', false, false) : 0;
		$cost = isset($this->postdata['cost']) && ($this->postdata['cost'] != "") ? $this->common_model->add_calculate_currency($this->postdata['cost'], '', '', false, false) : 0;
		
		if(!empty($postdata['trunk_id'])){
			$trunk_id = $this->common->get_field_name('id','trunks',array('id' => $postdata['trunk_id']));
			if(empty($trunk_id)){
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line('trunk_not_found')
				),400 );
			}
		}
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
		if($this->accountinfo['type'] == '1'){
			$this->postdata['reseller_id'] = $this->postdata['id'];
		}
		// Kinjal ASTPPCOM-1250 Start
		$trunk_id  = isset($this->postdata['trunk_id']) && $this->postdata['trunk_id'] != "" ? $this->postdata['trunk_id']:'0';
		$originationrate_info =(array)$this->db_model->getSelect ( "*", "routes", array ('pricelist_id' => $this->postdata['rategroup_id'],'pattern' => '^'.$this->postdata['code'].'.*','trunk_id' => $trunk_id) )->first_row();	
		if($originationrate_info != "" && !empty($originationrate_info)){
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'duplicate_or' ) 
			), 400 );
		}
		// Kinjal ASTPPCOM-1250 END
		$final_arr=array(
			'pricelist_id'       =>isset($this->postdata['rategroup_id'])?$this->postdata['rategroup_id']:'',
			'reseller_id'        =>isset($this->postdata['reseller_id']) ? $this->postdata['reseller_id']:'',
			'comment'            =>isset($this->postdata['destination'])?$this->postdata['destination']:'',
			'status'             =>isset($postdata['status'])?$postdata['status']:'',
			'country_id'         =>isset($this->postdata['country_id'])?$this->postdata['country_id']:'',
			'call_type'          =>isset($postdata['call_type'])?$postdata['call_type']:'',
			'routing_type'       =>$routing_type,
			'connectcost'        =>$connectcost,
			'routing_type'       =>$connectcost,
			'percentage'         =>'0,0,0',
			'includedseconds'    =>isset($this->postdata['includedseconds'])?$this->postdata['includedseconds']:'',
			'cost'               =>$cost,
			'init_inc'           =>isset($postdata['init_inc'])?$postdata['init_inc']:'',
			'inc'                =>isset($postdata['inc'])?$postdata['inc']:'',
			'percentage'	     =>isset($perc)?$perc : '0,0,0',
			'trunk_id'	         =>$trunk_id,
			'last_modified_date' =>gmdate('Y-m-d H:i:s'),
			'creation_date' => gmdate('Y-m-d H:i:s'),
			'pattern' => "^" . $this->postdata['code'] . ".*"
			);
			$this->db->insert("routes", $final_arr);
			$last_id = $this->db->insert_id();
			if(!empty(($last_id))){
				$final_array = (array)$this->db->get_where('routes',array('id' => $last_id))->first_row();	
				$final_array['destination'] = $final_array['comment'];
				$final_array['code'] = str_replace(str_split('^.*'), '', $final_array['pattern']);
				$final_array['creation_date'] = $this->common->convert_GMT_to('','',$final_array['creation_date'],$this->accountinfo['timezone_id']);
				$final_array['last_modified_date'] = $this->common->convert_GMT_to('','',$final_array['last_modified_date'],$this->accountinfo['timezone_id']);
				unset($final_array['comment'],$final_array['pattern'],$final_array['accountid'],$final_array['percentage'],$final_array['call_count'],$final_array['precedence']);
				$this->response ( array (
					'status'  => true,
					'data'    => $final_array,
					'success' => $this->lang->line ( 'or_create' )  
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
