<?php

require APPPATH . '/controllers/common/account.php';

#[\AllowDynamicProperties]
class Did_admin extends Account
{
    protected $postdata = "";

    function __construct()
    { 

        parent::__construct();
        $this->load->model('common_model');
        $this->load->model('db_model');
        $this->load->library('Form_validation');
        $this->load->library ( 'astpp/order');
        $this->load->model("did/Did_model",'did_model');
        $this->accountinfo = $this->get_account_info();
        if ($this->accountinfo['type'] != -1 && $this->accountinfo ['type'] != 2) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('error_invalid_key')
            ), 400);
        }
        $rawinfo = $this->post();
        $this->postdata = array();
        foreach ($rawinfo as $key => $value) {
            $this->postdata [$key] = $this->_xss_clean($value, TRUE);
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
   
    function _create(){
        if($this->form_validation->required($this->postdata['did_number']) == '') {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('did_number_required')
            ), 400);
        }
        if($this->postdata['did_number'] != "" && !$this->form_validation->numeric($this->postdata['did_number'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_did_number')
            ), 400);
        }
        if ($this->postdata['did_number'] != "" && !$this->form_validation->is_unique_did_api($this->postdata['did_number'], 'products')) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('unique_did_number')
            ), 400);
        }
        $product_add_array['name'] = $this->postdata['did_number'];

        if ($this->form_validation->required($this->postdata['monthly_fee']) == '') {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('monthly_fee_required')
            ), 400);
        }
        if ($this->form_validation->required($this->postdata['billing_days']) == '') {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('billing_days_required')
            ), 400);
        }
        if($this->postdata['provider_id'] != "") {
            $provider_id = $this->db_model->getSelect("*", "accounts", array("id" => $this->postdata['provider_id'], "status" => 0, "type" => 3, "deleted" => 0))->row_array();
            if (empty($provider_id)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('provider_id_not_found')
                ), 400);
            }
            $did_add_array['provider_id'] =  $this->postdata['provider_id'];
        } else {
            $did_add_array['provider_id'] = 3;
        }
        if ($this->postdata['country_id'] != "" && !$this->form_validation->numeric($this->postdata['country_id'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('valid_country_id')
            ), 400);
        }
        $product_add_array['country_id'] = isset($this->postdata['country_id']) &&  $this->postdata['country_id'] != "" ? $this->postdata['country_id'] : 85 ;

        if($this->postdata['city'] != '' && $this->form_validation->alpha($this->postdata['city']) != '1') {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_city')
            ), 400);
        }
        if($this->postdata['province'] != '' && $this->form_validation->alpha($this->postdata['province']) != '1') {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_province')
            ), 400);
        }
        $did_add_array['city'] = $this->postdata['city'];
        $did_add_array['province'] = $this->postdata['province'];
        $product_add_array['product_category'] =  4;
        if ($this->postdata['buy_cost'] != "" && !$this->form_validation->numeric($this->postdata['buy_cost'])) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('numeric_buy_cost')
                ), 400);
        }
        if($this->postdata['buy_cost'] != "" && !$this->form_validation->max_length($this->postdata['buy_cost'], 15)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('max_buy_cost')
                ), 400);
        }
        if($this->postdata['buy_cost'] != "" && !$this->form_validation->greater_than($this->postdata['buy_cost'], -1)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('min_buy_cost')
                ), 400);
        }
        $product_add_array['buy_cost'] = isset($this->postdata['buy_cost']) ? $this->postdata['buy_cost'] : "0.00";
        //status
        if ($this->postdata['status'] != "" && !$this->form_validation->numeric($this->postdata['status'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_retired')
            ), 400);
        } 
        $product_add_array['status'] = isset($this->postdata['status']) && $this->postdata['status'] != "" ? $this->postdata['status'] : 0;

        if ($this->postdata['connection_cost'] != "" && !$this->form_validation->max_length($this->postdata['connection_cost'], 15)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_connectcost')
            ), 400);
        }
        if ($this->postdata['connection_cost'] != "" && !$this->form_validation->greater_than($this->postdata['connection_cost'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_connectcost')
            ), 400);
        }
        $did_add_array['connectcost'] = isset($this->postdata['connection_cost']) ? $this->postdata['connection_cost'] : "0";
        //includedseconds
        if ($this->postdata['grace_time'] != "" && !$this->form_validation->max_length($this->postdata['grace_time'], 15)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_includedseconds')
            ), 400);
        }
        if ($this->postdata['grace_time'] != "" && !$this->form_validation->greater_than($this->postdata['grace_time'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_includedseconds')
            ), 400);
        }
        $did_add_array['includedseconds'] = isset($this->postdata['grace_time']) ? $this->postdata['grace_time'] : 0;
        //cost/min(usd)
        if ($this->postdata['cost_min'] != "" && !$this->form_validation->max_length($this->postdata['cost_min'], 10)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_cost_min')
            ), 400);
        }
        if ($this->postdata['cost_min'] != "" && !$this->form_validation->greater_than($this->postdata['cost_min'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_cost_min')
            ), 400);
        }
        $did_add_array['cost'] = isset($this->postdata['cost_min']) ? $this->postdata['cost_min'] : "0.00";
        //Initial Increment
        if ($this->postdata['initial_increment'] != "" && !$this->form_validation->max_length($this->postdata['initial_increment'], 15)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_initial_increment')
            ), 400);
        }
        if ($this->postdata['initial_increment'] != "" && !$this->form_validation->greater_than($this->postdata['initial_increment'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_initial_increment')
            ), 400);
        }
        if($this->postdata['initial_increment'] != "" && !$this->form_validation->integer($this->postdata['initial_increment'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_initial_increment')
            ), 400);
        }
        $did_add_array['init_inc'] = isset($this->postdata['initial_increment']) ? $this->postdata['initial_increment'] : "0";
        //increment
        if ($this->postdata['increment'] != "" && !$this->form_validation->max_length($this->postdata['increment'], 15)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_increment')
            ), 400);
        }
        if ($this->postdata['increment'] != "" && !$this->form_validation->greater_than($this->postdata['increment'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_increment')
            ), 400);
        }
        if($this->postdata['increment'] != "" && !$this->form_validation->integer($this->postdata['increment'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_increment')
            ), 400);
        }
        $did_add_array['inc'] = isset($this->postdata['increment']) ? $this->postdata['increment'] : "0";
        //setup_fee
        if ($this->postdata['setup_fee'] != "" && !$this->form_validation->numeric($this->postdata['setup_fee'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_setup_fee')
            ), 400);
        }
        if ($this->postdata['setup_fee'] != "" && !$this->form_validation->max_length($this->postdata['setup_fee'], 15)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_setup_fee')
            ), 400);
        }
        if ($this->postdata['setup_fee'] != "" && !$this->form_validation->greater_than($this->postdata['setup_fee'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_setup_fee')
            ), 400);
        }
        $product_add_array['setup_fee'] = isset($this->postdata['setup_fee']) ? $this->postdata['setup_fee'] : "0.00";
        //monthly_fee
        if ($this->postdata['monthly_fee'] != "" && !$this->form_validation->numeric($this->postdata['monthly_fee'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_price')
            ), 400);
        }
        if ($this->postdata['monthly_fee'] != "" && !$this->form_validation->max_length($this->postdata['monthly_fee'], 15)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_price')
            ), 400);
        }
        if ($this->postdata['monthly_fee'] != "" && !$this->form_validation->greater_than($this->postdata['monthly_fee'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_price')
            ), 400);
        }
        $product_add_array['price'] = isset($this->postdata['monthly_fee']) ? $this->postdata['monthly_fee'] : "0.00";
        //call timeout
        if ($this->postdata['call_timeout'] != "" && !$this->form_validation->max_length($this->postdata['call_timeout'], 15)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_call_timeout')
            ), 400);
        }
        if ($this->postdata['call_timeout'] != "" && !$this->form_validation->greater_than($this->postdata['call_timeout'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_call_timeout')
            ), 400);
        }
        if($this->postdata['call_timeout'] != "" && !$this->form_validation->integer($this->postdata['call_timeout'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_call_timeout')
            ), 400);
        }
        $did_add_array['leg_timeout'] = isset($this->postdata['call_timeout']) && $this->postdata['call_timeout'] != "" ? $this->postdata['call_timeout'] : 0;
        //billing_type
        if($this->postdata['billing_type'] != "" && !$this->form_validation->numeric($this->postdata['billing_type'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_billing_type')
            ), 400);
        }
        if($this->postdata['billing_type'] != "" && !$this->form_validation->integer($this->postdata['billing_type'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_billing_type')
            ), 400);
        }
        $product_add_array['billing_type'] = isset($this->postdata['billing_type']) ? $this->postdata['billing_type'] : 0;
        //billing days
        if ($this->postdata['billing_days'] != "" && !$this->form_validation->numeric($this->postdata['billing_days'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_billing_days')
            ), 400);
        }
        if($this->postdata['billing_days'] != "" && !$this->form_validation->max_length($this->postdata['billing_days'], 3)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_billing_days')
            ), 400);
        }
        if($this->postdata['billing_days'] != "" && !$this->form_validation->greater_than($this->postdata['billing_days'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_billing_days')
            ), 400);
        }
        if($this->postdata['billing_days'] != "" && !$this->form_validation->integer($this->postdata['billing_days'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_billing_days')
            ), 400);
        }
        $product_add_array['billing_days'] = ($this->postdata['billing_type'] != 2) ? $this->postdata['billing_days'] : 28;
        //concurrent_calls
        if ($this->postdata['concurrent_calls'] != "" && !$this->form_validation->max_length($this->postdata['concurrent_calls'], 10)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_concurrent_calls')
            ), 400);
        }
        if ($this->postdata['concurrent_calls'] != "" && !$this->form_validation->greater_than($this->postdata['concurrent_calls'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_concurrent_calls')
            ), 400);
        }
        if ($this->postdata['concurrent_calls'] != "" && !$this->form_validation->integer($this->postdata['concurrent_calls'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_concurrent_calls')
            ), 400);
        }
        $product_add_array['creation_date'] = gmdate("Y-m-d H:i:s");
        $product_add_array['last_modified_date'] = gmdate("Y-m-d H:i:s");
        $product_add_array['created_by'] = $this->accountinfo['id'];
        $product_add_array['reseller_id'] = $this->accountinfo['reseller_id'];
        $this->db->insert("products", $product_add_array);
        $last_id = $this->db->insert_id();
        
        $did_add_array['maxchannels'] = isset($this->postdata['concurrent_calls']) ? $this->postdata['concurrent_calls'] : "0";
        $did_add_array['number'] = $product_add_array['name'];
        $did_add_array['status'] = $product_add_array['status'];
        $did_add_array['setup'] = $product_add_array['setup_fee'];
        $did_add_array['monthlycost'] =  $product_add_array['price'];
        $did_add_array['country_id'] = $product_add_array['country_id'];
        $did_add_array['last_modified_date'] = gmdate("Y-m-d H:i:s");
        $did_add_array['product_id'] = $last_id;
        $this->db->insert("dids", $did_add_array);
        $last_id_did = $this->db->insert_id();
        // Kinjal issue no 3808
        if ($last_id_did !='') {
            $did_info = $this->db_model->getSelect("*", "dids", array("id" => $last_id_did))->row_array();
            unset($did_info['id']);
            $did_info['did_id'] = $did_info['product_id'];
            $did_info['last_modified_date'] = $this->common->convert_GMT_to('','',$did_info['last_modified_date'],$this->accountinfo['timezone_id']);
            $did_info['concurrent_calls'] = $did_info['maxchannels'];
        }
        if (!empty(($last_id_did)) || !empty($did_add_array)) {
            $this->response(array(
                'status' => true,
                'data' => $did_info,
                'success' => $this->lang->line('did_create')
            ), 200);
        // END
        } else {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('did_not_found')
            ), 400);
        }
    }

    function _delete(){
        if (!isset ($this->postdata ['product_id']) || $this->postdata['product_id'] == '') {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('error_param_missing') . "integer:product_id"
            ), 400);
        }
        unset ($this->postdata ['action']);
        if($this->postdata['product_id'] != ''){
            if(!$this->form_validation->numeric_with_comma($this->postdata['product_id'])){
                $this->response ( array (
                    'status' => false,
                    'success' =>  $this->lang->line ('valid_product_id')  
                ), 400 );
            }
            $did_info_details = array();
            $where = "product_id IN (" . $this->postdata['product_id'] . ")";
            
            if($this->accountinfo['type'] != 1){
                 $where .= "and accountid = 0 ";
            }
            $did_info = (array) $this->db->get_where("dids", $where)->result_array();
            if (!empty($did_info)) {
            foreach ($did_info as $key => $value) {
                $where = "product_id =". $value['product_id'];
                $this->did_model->did_number_release($value, $this->accountinfo, 'remove');
                if($this->accountinfo['type'] == -1 || $this->accountinfo['type'] == 2){
                        $category_name = '';
                        $acc_id = '';
                        $order_items_id = '';
                        $order_id = '';
                        $did_delete = array();
                        $product_category_details = array();
                        $product_category_details_result = array();
                        $product_category_details = $this->db_model->getSelect("name,product_category", "products", array(
                            "id" => $value['product_id']
                        ));

                        if ($product_category_details->num_rows > 0) {
                            $product_category_details_result = $product_category_details->result_array()[0];

                            $did_delete['product_name'] = $product_category_details_result['name'];

                            $category_name = $this->common->get_field_name("name", "category", array(
                                "id" => $product_category_details_result['product_category']
                            ));
                            $acc_id = $this->common->get_field_name("accountid", "order_items", array(
                                "product_id" => $value['product_id']
                            ));
                            $order_items_id = $this->common->get_field_name("order_id", "order_items", array(
                                "product_id" => $value['product_id']
                            ));
                            $order_id = $this->common->get_field_name("order_id", "orders", array(
                                "id" => $order_items_id
                            ));
                            $did_delete['category_name'] = $category_name;
                            $did_delete['next_billing_date'] = gmdate('Y-m-d H:i:s');
                            $acc_info_result = array();
                            $did_delete['order_id'] = $order_id;
                            $acc_info = $this->db_model->getSelect("*", "accounts", array(
                                "id" => $acc_id
                            ));

                            if ($acc_info->num_rows > 0) {
                                $acc_info_result = $acc_info->result_array()[0];
                                $final_array = astpp_array_merge($acc_info_result, $did_delete);
                                $this->common->mail_to_users('product_release', $final_array);
                            }
                        }
                        $order_update_array = array(
                            "is_terminated" => 1,
                            "termination_date" => gmdate('Y-m-d H:i:s'),
                            "termination_note" => "DID(" . $value['number'] . ") has been released by " . $value['number'] . "( " . $value['first_name'] . " " . $value['last_name'] . ") "
                        );
                        $order_where = array(
                            "is_terminated" => 0,
                            "product_id" => $value['product_id'],
                        );
                        $this->db->where($order_where);
                        $this->db->update("order_items", $order_update_array);
                        $whr = "id IN (" . $this->postdata['product_id'] . ")";
                        $this->db->where($whr);
                        $this->db->delete('products');
                        $this->db->where(array(
                            "id" => $value['id']
                        ));
                        $this->db->delete('dids');
                }
            }
        
         $this->response(array(
                'status' => true,
                'success' => $this->lang->line('product_delete')
            ), 200);
        }else{
       $this->response(array(
                'status' => false,
                'error' => $this->lang->line('product_not_found')
            ), 400);
        }
        }
    }
    function _list()
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
        $where = array();
        foreach($object_where_params as $object_where_key => $object_where_value) {
            if($object_where_value != '') { 
                 if($object_where_key == "destination"){
                    $where = "dids.extensions" . ' = "' . $object_where_value . '" AND ';
                }
                elseif($object_where_key == "accountid") {
                    if(!$this->form_validation->integer($object_where_value)){
                        $this->response ( array (
                            'status' => false,   
                            'success' =>  $this->lang->line ('enter_valid_accountid')  
                        ), 400 );
                    }  
                    $where = "accountid" . ' = "' . $object_where_value . '" AND ';              
                }
                elseif($object_where_key == "reseller_id") {
                    if(!$this->form_validation->integer($object_where_value)){
                        $this->response ( array (
                            'status' => false,   
                            'success' =>  $this->lang->line ('valid_reseller_id')  
                        ), 400 );
                    }
                    $where = "parent_id" . ' = "' . $object_where_value . '" AND ';              
                }else{
                    if($object_where_key == 'status'){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,   
                                'success' =>  $this->lang->line ('valid_status')  
                            ), 400 );
                        }
                    }
                    if($object_where_key == 'call_type_id'){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,   
                                'success' =>  $this->lang->line ('invalid_call_type')  
                            ), 400 );
                        }
                        $object_where_key = 'call_type';
                    }
                    if($object_where_key == 'country_id'){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,   
                                'success' =>  $this->lang->line ('valid_country_id')  
                            ), 400 );
                        }
                    }
                    $where = 'dids.'.$object_where_key . ' = "' . $object_where_value . '" AND ';
                }
            }
        }
        if(!empty($where)) {
            $where = rtrim($where,"AND ");
            $this->db->where($where);
        }
        $start = astpp_n($this->postdata['start_limit'])-1;
        $limit = $this->postdata['end_limit'];
        $no_of_records = (int)$limit - (int)$start;
        
        $no_of_days =common_model::$global_config ['system_config'] ['reservation_days'];
        $this->db->order_by("did_id", "desc");
        $dids_array = $this->db_model->select("id as did_id_new,product_id as id,product_id as productid,id as did_id,number,status,country_id,accountid,parent_id,cost,setup,monthlycost,maxchannels,leg_timeout,init_inc,inc,call_type,extensions,last_modified_date,province,city", "dids", '', "", "", $limit, $start);
        $dids_arrays = $dids_array->result_array();
        $count = $dids_array->num_rows();
        $from_currency = Common_model::$global_config['system_config']['base_currency'];
        $to_currency = $this->common->get_field_name('currency', 'currency', $this->accountinfo['currency_id']);
        foreach ($dids_arrays as $key => $dids_array_value) { 

           $dids_arrays[$key] = $dids_array_value;
           //Riya 3599 Rename productid to did_id in DID list API
            $dids_arrays[$key]['did_id'] = $dids_arrays[$key]['productid'];
           $product_did =  $this->db_model->getSelect("*", "products", array('id' => $dids_arrays[$key]['did_id']))->row_array();
           //end
           $dids_arrays[$key]['country_id'] = $this->common->get_field_name_country_camel("country", "countrycode", $dids_arrays[$key]['country_id']);
          
           $dids_arrays[$key]['cost'] =  $this->common_model->calculate_currency_customer($dids_arrays[$key]['cost'],$from_currency,$to_currency,true,true)." ".$to_currency;
           $dids_arrays[$key]['monthlycost'] =  $this->common_model->calculate_currency_customer($dids_arrays[$key]['monthlycost'],$from_currency,$to_currency,true,true)." ".$to_currency;
           $dids_arrays[$key]['setup_fee'] =  $this->common_model->calculate_currency_customer($dids_arrays[$key]['setup'],$from_currency,$to_currency,true,true)." ".$to_currency;
           $dids_arrays[$key]['call_timeout'] =  $dids_arrays[$key]['leg_timeout'];
           $dids_arrays[$key]['cc'] =  $dids_arrays[$key]['maxchannels'];
           $dids_arrays[$key]['call_type'] = $this->common->get_call_type("call_type","dids",$dids_arrays[$key]['call_type']);
            //not anyone assign
            if(astpp_eq0($dids_arrays[$key]['accountid']) && astpp_eq0($dids_arrays[$key]['parent_id']) && astpp_eq0($dids_arrays[$key]['status'])){
                $dids_arrays[$key]['is_purchased'] = "Assign Number";
            }
            //assign to customer
            elseif((!astpp_eq0($dids_arrays[$key]['accountid'])) && astpp_eq0($dids_arrays[$key]['parent_id']) && astpp_eq0($dids_arrays[$key]['status'])){
                $dids_arrays[$key]['is_purchased'] = "Release(C)";
            }
            //just reseller purchased 
            elseif(astpp_eq0($dids_arrays[$key]['accountid']) && (!astpp_eq0($dids_arrays[$key]['parent_id'])) && astpp_eq0($dids_arrays[$key]['status'])){
                $dids_arrays[$key]['is_purchased'] = "Release(R)";
            }//customer of reseller
            elseif((!astpp_eq0($dids_arrays[$key]['accountid'])) && (!astpp_eq0($dids_arrays[$key]['parent_id'])) && astpp_eq0($dids_arrays[$key]['status'])){
                $dids_arrays[$key]['is_purchased'] = "Release(C)";
            }
            //customer of reseller
            elseif((!astpp_eq0($dids_arrays[$key]['accountid'])) && (!astpp_eq0($dids_arrays[$key]['parent_id'])) && astpp_eq0($dids_arrays[$key]['status'])){
                $dids_arrays[$key]['is_purchased'] = "Release(C)";
            }
            else{
                $dids_arrays[$key]['is_purchased'] = "Inactive";
            }

            if(astpp_eq0($product_did['billing_type'])){
                 $dids_arrays[$key]['billing_type'] ="One Time";
            }
            elseif ($product_did['billing_type'] == 1) {
                 $dids_arrays[$key]['billing_type'] ="Recurring";
            }
            else{
                 $dids_arrays[$key]['billing_type'] ="Recurring Monthly";
            }
            $dids_arrays[$key]['billing_days'] = $product_did['billing_days'];
          
           if(astpp_eq0($dids_arrays[$key]['status'])){
            $dids_arrays[$key]['status'] = "Active";
           }
           elseif ($dids_arrays[$key]['status'] == 1) {
            $dids_arrays[$key]['status'] = "Inactive";
           }
           else{
            $dids_arrays[$key]['status'] = "On Hold";
           }
            $dids_arrays[$key]['accountid'] = $this->common->get_field_name_coma_new('first_name,last_name,number,company_name','accounts', $dids_arrays[$key]['accountid']);
           $dids_arrays[$key]['reseller_id'] = astpp_eq0($dids_arrays[$key]['parent_id']) ? "Admin" : $this->common->get_field_name_coma_new('first_name,last_name,number,company_name','accounts', $dids_arrays[$key]['did_id']);
           unset($dids_arrays[$key]['maxchannels'],$dids_arrays[$key]['leg_timeout'],$dids_arrays[$key]['parent_id'],$dids_arrays[$key]['setup'],$dids_arrays[$key]['last_modified_date'],$dids_arrays[$key]['inc'],$dids_arrays[$key]['init_inc'], $dids_arrays[$key]['did_id_new'],$dids_arrays[$key]['id'],$dids_arrays[$key]['productid']);
        }
        if (empty($dids_arrays)) { 
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line("no_records_found")
            ), 200);
        }
        else{
            $this->response(array(
                'total_count' => $count,
                'data' => $dids_arrays,
                'success' => $this->lang->line("did_list_info")
            ), 200);
        }
    }

    function _did_purchase(){
        if(!$this->form_validation->required($this->postdata['did_number']) ){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('did_number_required' ) 
             ), 400 );
        }
        if(!is_numeric($this->postdata['did_number']) && $this->postdata['did_number'] != ""){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('numeric_did_number') 
            ), 400 );
        }
         if (astpp_gt($this->accountinfo['reseller_id'], 0)) {
            $this->db->select('dids.id, dids.number, reseller_products.setup_fee, reseller_products.price');
           $where =  array('dids.number' => $this->postdata['did_number'],'dids.status'=> 0,'dids.parent_id' => $this->accountinfo['reseller_id'],'dids.accountid'=> 0,'dids.number'=> $this->postdata['did_number']);
            $this->db->where($where);
            $this->db->from('dids');
            $this->db->join('reseller_products', 'dids.product_id = reseller_products.product_id');
            $dids_array = $this->db->get();
        } else {

            $this->db->select('dids.id, dids.number,products.setup_fee,products.price');
            
            $where = array('dids.accountid'=> '0','dids.parent_id' => $this->accountinfo['reseller_id'],'dids.status'=> 0,'dids.number'=> $this->postdata['did_number']);
            $this->db->where($where);
            $this->db->from('dids');
            $this->db->join('products', 'dids.product_id = products.id');
            $dids_array = $this->db->get();
        }
        $dids_data = $dids_array->row_array();
        $account_query = $this->db_model->getSelect("*", "accounts", array(
                'id' => $this->accountinfo['id']
            ));
        $account_arr = (array) $account_query->first_row();
        $did_arr = $this->db_model->getSelect ( "*", "dids", array ('number' => $this->postdata['did_number']) )->row_array();
        $did_result = $this->did_billing_process($this->accountinfo, $this->accountinfo['id'], $did_arr['id']);
        if ($did_result[0] == "SUCCESS") {
                    $did_arr['is_parent_billing'] = "false";
                    $order_id = '';
                    $payment_gateway = '';
                    if (astpp_gt($this->accountinfo['reseller_id'], 0)) {
                        $product_query = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.buy_cost,products.can_purchase,products.can_resell,products.commission,reseller_products.price,reseller_products.setup_fee,products.billing_type,products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id,reseller_products.setup_fee,reseller_products.is_optin', array(
                            'products.status' => 0,
                            'reseller_products.product_id' => $did_arr['product_id'],
                            'reseller_products.account_id' => $this->accountinfo['reseller_id']
                        ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', 'DESC', 'products.id');
                        $product_array = (array) $product_query->first_row();
                    } else {
                        $product_query = $this->db_model->getSelect("*", "products", array(
                            "id" => $did_arr['product_id']
                        ));
                        $product_array = (array) $product_query->first_row();
                    }

                    $did_arr['product_name'] = $product_array['name'];
                    $did_arr['category_name'] = $this->common->get_field_name('name', 'category', array(
                        'id' => $product_array['product_category']
                    ));
                    $did_arr['price'] = $this->common_model->calculate_currency($product_array['price'], '', '', true, false);
                    $did_arr['setup_fee'] = $this->common_model->calculate_currency($product_array['setup_fee'], '', '', true, false);
             $next_bill_date = gmdate('Y-m-d 23:59:59');
                     $today = gmdate("d");
            if($today <= 28 && $product_array['billing_type'] == "2"){
                $nxt_date =gmdate("Y-m-$today 23:59:59");   
                $next_bill_date =astpp_gmdate("Y-m-d 23:59:59",strtotime($nxt_date."+".(1)." months"));
            }
            if(astpp_gmdate("d",strtotime($next_bill_date)) > 28 && $product_array['billing_type'] == "2"){
                $next_bill_date =astpp_gmdate("Y-m-01 23:59:59",strtotime($next_bill_date."+".($product_array['billing_days'])." days"));
            }
            if($today > 28 && $product_array['billing_type'] == "2"){
                $next_bill_date = astpp_gmdate("Y-m-01 23:59:59",strtotime("+".(2)." months"));
            }
            if(astpp_eq0($product_array['billing_type'])){
                $next_bill_date = (astpp_eq0($product_array['billing_days']))?astpp_gmdate('Y-m-d 23:59:59', strtotime('+10 years')):astpp_gmdate("Y-m-d 23:59:59",strtotime("+".(astpp_n($product_array['billing_days'])-1)." days"));
            }
            if($product_array['billing_type'] == 1){
                $next_bill_date = (astpp_eq0($product_array['billing_days']))?astpp_gmdate('Y-m-d 23:59:59', strtotime('+10 years')):astpp_gmdate("Y-m-d 23:59:59",strtotime("+".(astpp_n($product_array['billing_days'])-1)." days"));
            }   
            
            $did_arr['next_billing_date'] = $next_bill_date;
            $did_arr['payment_by'] = "Account Balance";
            $last_id = $this->order->confirm_order($did_arr, $this->accountinfo['id'], $this->accountinfo);
            $final_array = astpp_array_merge($account_arr, $did_arr);
                    if (astpp_gt($last_id, 0)) {
                        $final_array['quantity'] = 1;
                        $final_array['price'] = (astpp_n($did_arr['setup_fee']) + astpp_n($did_arr['price']));
                        $final_array['total_price'] = (astpp_n($product_array['setup_fee']) + astpp_n($product_array['price'])) * (astpp_n($final_array['quantity']));
                        $final_array['total_price_amount'] = (astpp_n($product_array['setup_fee']) + astpp_n($product_array['price']));
                        $final_array['payment_by'] = "Account Balance";
                    }
                }

        if(isset($dids_data) && !empty($dids_data)){
            $this->response ( array (
                'status'  => true,
                'success' => $this->lang->line ('did_purchase' )  
            ), 200 );
        }else {
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('did_not_found' )
            ), 400 );   
        }

    }

   
    function _assign(){
        if(!$this->form_validation->required($this->postdata['did_id']) ){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('did_id_required' ) 
             ), 400 );
        }
        if(!$this->form_validation->required($this->postdata['accountid']) ){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('accountid_required' ) 
            ), 400 );
        }
        if(!is_numeric($this->postdata['did_id']) && $this->postdata['did_id'] != ""){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('numeric_did_id') 
            ), 400 );
        }
        if(!is_numeric($this->postdata['accountid']) && $this->postdata['accountid'] != ""){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('numeric_accountid') 
            ), 400 );
        }
        $accounts_data = $this->db_model->getSelect ("*", "accounts", array ("id" => $this->postdata['accountid'] , "reseller_id" => 0, "type" => 0,"status" => 0,"deleted" => 0))->row_array();
        if (empty($accounts_data) || $accounts_data == "") {
                $this->response(array(
                    'status' =>false,
                    'error' => $this->lang->line('account_not_found')
                ), 400);
        } 
        $dids_data = $this->db_model->getSelect ("*", "dids", array ("product_id" => $this->postdata['did_id'] , "accountid" => 0,"parent_id" => 0, "status" => 0))->row_array();
         if (empty($dids_data) || $dids_data == "") {
                $this->response(array(
                    'status' =>false,
                    'error' => $this->lang->line('did_not_found')
                ), 400);
        }
        if($accounts_data != "" && $dids_data != ""){
            $did_id = $this->common->get_field_name("id","dids",array("product_id"=>$this->postdata['did_id']));
            $did_result = $this->did_billing_process($accounts_data, $this->postdata['accountid'], $did_id);
            // Kinjal issue no 3808
            if($did_result[0] == "INSUFFIECIENT_BALANCE"){
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line("insufficient_balance")

                ), 200);
            }
            // END
            if ($did_result[0] == "SUCCESS") {
                $add_array['invoice_type'] = "debit";
                $add_array['payment_by'] = "Account Balance";
                $add_array['charge_type'] = "DID";
                $add_array['is_update_balance'] = "true";
                // Kinjal issue no 3808
                $add_array['is_parent_billing'] = "false";
                $add_array['product_id'] = $this->postdata['did_id'];
                // END
                $order_id = $this->order->confirm_order($add_array, $this->postdata['accountid'], $this->accountinfo);
                if (astpp_gt($order_id, 0)) {
                        $this->db->where("product_id", $this->postdata['did_id']);
                        $this->db->update("dids", array(
                            "accountid" =>$this->postdata['accountid']
                        ));
                        // Kinjal issue no 3808
                        $this->response(array(
                            'status' => true,
                            'error' => $this->lang->line("did_assigned")
                        ), 200);
                        // END
                }
                // Kinjal issue no 3808
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('something_wrong')
                ), 400);
                // END
            }
            $this->response(array(
                    'status' => true,
                    'error' => $this->lang->line("did_assigned")
            ), 200);
        }
        else{
            $this->response(array(
                'status' =>false,
                'error' => $this->lang->line('did_not_found')
            ), 400);
        }
    }
    // Kinjal issue no 3837
    function _assign_reseller(){
        if(!$this->form_validation->required($this->postdata['did_id']) ){
              $this->response ( array (
                  'status' => false,
                  'error' => $this->lang->line ('did_id_required' ) 
               ), 400 );
          }
        if(!$this->form_validation->required($this->postdata['reseller_id']) ){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('required_reseller_id' ) 
             ), 400 );
        }else{
            $account_info = $this->db_model->getSelect("*", " accounts", array('id' => $this->postdata['reseller_id'],'type' => 1,'deleted' => 0))->row_array();
            if(empty($account_info)){
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ('reseller_not_found') 
                ), 400 );
            }
        }
        if(!is_numeric($this->postdata['did_id']) && $this->postdata['did_id'] != ""){
              $this->response ( array (
                  'status' => false,
                  'error' => $this->lang->line ('numeric_did_id') 
              ), 400 );
        }
        if($this->postdata['account_id'] != ""){
            $account_info = $this->db_model->getSelect("*", " accounts", array('id' => $this->postdata['account_id'],'reseller_id'=>$this->postdata['reseller_id'],'type' => 0,'deleted' => 0))->row_array();
            if(empty($account_info)){
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ('account_not_found') 
                ), 400 );
            }
        }
          if(!is_numeric($this->postdata['price']) && $this->postdata['price'] != ""){
              $this->response ( array (
                  'status' => false,
                  'error' => $this->lang->line ('numeric_price') 
              ), 400 );
          }
          if(!is_numeric($this->postdata['setup_fee']) && $this->postdata['setup_fee'] != ""){
              $this->response ( array (
                  'status' => false,
                  'error' => $this->lang->line ('numeric_setup_fee') 
              ), 400 );
          }
        if($this->postdata['account_id'] == ""){
            $where = array('parent_id' => $this->accountinfo['reseller_id'], 'accountid' => 0, 'status' => 0,'product_id'=> $this->postdata['did_id']);
        }else{
            $where = array('parent_id' => $this->postdata['reseller_id'], 'accountid' => 0, 'status' => 0,'product_id'=> $this->postdata['did_id']);
        }
        if (astpp_gt($this->accountinfo['reseller_id'], 0)) {
            $where = array( 
                'reseller_products.account_id' => $this->accountinfo['reseller_id'],
                'dids.status' => 0,
                'dids.parent_id' => $this->accountinfo['reseller_id'],
                'dids.accountid' => 0
            );
          $buy_did_list = $this->db_model->getJionQuery('dids', 'dids.product_id as id,dids.number,,dids.country_id,dids.cost,dids.call_type,dids.leg_timeout,dids.maxchannels,dids.extensions,reseller_products.buy_cost,reseller_products.setup_fee,
  reseller_products.price,reseller_products.billing_type,(CASE WHEN reseller_products.billing_type = 2 THEN "Monthly" ELSE reseller_products.billing_days END) as billing_days,dids.province,dids.city
  ,reseller_products.product_id', $where, 'reseller_products', 'dids.product_id=reseller_products.product_id', 'inner', "", "", 'DESC', 'dids.id');
          }
          else{
            $buy_did_list = $this->db_model->select("*,product_id as productid,product_id as id", "dids", $where, "dids.id", "desc", "", "");
          }
          $dids_arrays = $buy_did_list->row_array();
        if($dids_arrays['id'] != $this->postdata['did_id'] || $dids_arrays == ""){
            if($this->postdata['account_id'] != ""){
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line("did_not_optin")
                ), 400);
            }else{
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line("did_not_found")
                ), 400);
            }
        }
          $add_array['price'] = $this->postdata['price'];
          $add_array['setup_fee'] = $this->postdata['setup_fee'];
          if($this->postdata['did_id'] != '' && (!astpp_eq0($this->postdata['did_id']))) {
                $did_id = $this->common->get_field_name("id", "dids", array(
                    "product_id" => $this->postdata['did_id']
                ));
                $did_product_id = $this->db_model->getSelect("*", " dids", array(
                        'product_id' => $this->postdata['did_id'],
                        'status' => 0
                ))->row_array();
                $did_result = $this->did_billing_process($account_info, $account_info['id'], $did_id, '', $add_array);
                  if ($did_result[0] == "INSUFFIECIENT_BALANCE") {
                      $this->response(array(
                          'status' => false,
                          'error' => $this->lang->line("insufficient_balance")
                      ), 400);
                  }
                $product_info = $this->db_model->getSelect("*", " products", array(
                    'id' => $did_product_id['product_id'],
                    'status' => 0
                ));
                if ($did_result[0] == "SUCCESS") {
                  if($this->postdata['account_id'] != ""){
                    $add_array['invoice_type'] = "debit";
                    $add_array['payment_by'] = "Account Balance";
                    $add_array['charge_type'] = "DID";
                    $add_array['is_update_balance'] = "true";
                    $add_array['is_parent_billing'] = "false";
                    $add_array['product_id'] = $this->postdata['did_id'];
                    $product_info = $this->db_model->getSelect("*", "reseller_products", array(
                        'product_id' => $did_product_id['product_id'],
                        'account_id' => $this->postdata['reseller_id']
                    ))->row_array();
                    $add_array['price'] = $product_info['price'];
                    $add_array['setup_fee'] = $product_info['setup_fee'];
                  }else{
                    if ($product_info->num_rows > 0) {
                        $product_info = $product_info->result_array()[0];
                        $add_array['billing_type'] = $product_info['billing_type'];
                        $add_array['billing_days'] = $product_info['billing_days'];
                        $add_array['commission'] = $product_info['commission'];
                        $add_array['free_minutes'] = isset($product_info['free_minutes']) ? $product_info['free_minutes'] : 0;
                        if ((astpp_gt($this->accountinfo['reseller_id'], 0) || $this->accountinfo['type'] == 1) && astpp_eq0($this->accountinfo['is_distributor'])) {
                            $add_array['buy_cost'] = $this->common_model->add_calculate_currency($did_product_id['cost'], "", '', false, false);
                        } else {
                            $add_array['buy_cost'] = $product_info['buy_cost'];
                        }
                        if (astpp_eq0($account_info['is_distributor'])) {
                            $add_array['price'] = isset($this->postdata['price']) && $this->postdata['price'] != "" ? $this->common_model->add_calculate_currency($this->postdata['price'], "", '', false, false) : $this->common_model->add_calculate_currency($product_info['price'], "", '', false, false);
                            $add_array['setup_fee'] = isset($this->postdata['setup_fee']) && $this->postdata['setup_fee'] != "" ? $this->common_model->add_calculate_currency($this->postdata['setup_fee'], "", '', false, false) : $this->common_model->add_calculate_currency($product_info['setup_fee'], "", '', false, false);
                        } else {
                            $add_array['price'] = $product_info['price'];
                            $add_array['setup_fee'] = $product_info['setup_fee'];
                        }
                        $query = "INSERT INTO reseller_products (product_id, account_id, reseller_id, buy_cost,commission, setup_fee, price, free_minutes, billing_type, billing_days, status, is_optin,is_owner,optin_date) VALUES(" . $did_product_id['product_id'] . "," . $account_info['id'] . "," . $account_info['reseller_id'] . "," . $add_array['buy_cost'] . "," . $add_array['commission'] . "," . $add_array['setup_fee'] . "," . $add_array['price'] . "," . $add_array['free_minutes'] . "," . $add_array['billing_type'] . "," . $add_array['billing_days'] . ", 0, 0, 1, '" . gmdate('Y-m-d H:i:s') . "') ON DUPLICATE KEY UPDATE product_id = VALUES(product_id), account_id = VALUES(account_id), reseller_id = VALUES(reseller_id),buy_cost = VALUES(buy_cost),commission = VALUES(commission), setup_fee = VALUES(setup_fee), price = VALUES(price), free_minutes = VALUES(free_minutes), billing_type = VALUES(billing_type), billing_days = VALUES(billing_days), status = VALUES(status), is_optin = VALUES(is_optin), is_owner = VALUES(is_owner), optin_date = VALUES(optin_date)";
                        $query = $this->db->query($query);
                        $add_array['is_parent_billing'] = 'false';
                        $add_array['payment_by'] = "Account Balance";
                        $add_array['product_id'] = $this->postdata['did_id'];
                    }
                }
                    $order_id = $this->order->confirm_order($add_array, $account_info['id'], $account_info);
                    if (astpp_gt($order_id, 0)) {
                        if($this->postdata['account_id'] != ""){
                            $this->db->where("product_id", $this->postdata['did_id']);
                            $did_update = $this->db->update("dids", array(
                                "accountid" =>$this->postdata['account_id']
                            ));
                            $this->response(array(
                                'status' => true,
                                'error' => $this->lang->line("did_assigned")
                            ), 200);
                        }else{  
                            $this->db->where("product_id", $this->postdata['did_id']);
                            $did_update = $this->db->update("dids", array(
                                "parent_id" => $account_info['id']
                            ));
                            if($did_update == 1){
                                $this->response(array(
                                    'status' => true,
                                    'success' => $this->lang->line("did_optin")
                                ), 200);
                            }else{
                                $this->response(array(
                                    'status' => false,
                                    'error' => $this->lang->line('something_wrong')
                                ), 400);
                            }
                        }
                    }else{
                    $this->response(array(
                        'status' => false,
                        'error' => $this->lang->line('something_wrong')
                    ), 400);
                }
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('something_wrong')
                ), 400);
        }
    }else{
        $this->response(array(
          'status' => false,
          'error' => $this->lang->line("did_not_found")
        ), 400);
        }
    }
    // END
    function _release(){
       if(!$this->form_validation->required($this->postdata['did_id']) ){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('did_id_required' ) 
             ), 400 );
        }
        if(!is_numeric($this->postdata['did_id']) && $this->postdata['did_id'] != ""){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('numeric_did_id') 
            ), 400 );
        }
        
        $dids_data = $this->db_model->getSelect ("*", "dids",
         array("product_id" => $this->postdata['did_id']))->row_array();
        if (empty($dids_data) || $dids_data == "" || (astpp_eq0($dids_data['accountid']) && astpp_eq0($dids_data['parent_id']) && astpp_eq0($dids_data['status'])) || ((!astpp_eq0($dids_data['status'])))) {
                $this->response(array(
                    'status' =>false,
                    'error' => $this->lang->line('did_not_found')
                ), 400);
        }
        if((!astpp_eq0($dids_data['parent_id'])) && astpp_eq0($dids_data['accountid']) && astpp_eq0($dids_data['status'])){
            //did_on_hold code
            $did_info = (array)$this->db->get_where('dids',array("product_id"=>$this->postdata['did_id']))->first_row();
            if(!empty($did_info)){
              if(astpp_gt($did_info['accountid'], 0)){
                $this->db->where('product_id',$this->postdata['did_id']);
                $this->db->update('dids',array('status'=>'0','accountid'=>'0','onhold_date'=> '0000-00-00 00:00:00'));
                $this->db->where('id',$did_info['product_id']);
                $this->db->update('products',array('status'=>'0'));
              }else{
                 if(astpp_gt($did_info['parent_id'], 0)){
                    $where = "product_id =". $did_info['product_id'];
                    $reseller_info = (array)$this->db->get_where('accounts',array("id"=>$did_info['parent_id']))->first_row();
                    $did_update_array = array(
                    "accountid" => 0,
                    "parent_id" => 0,
                    "call_type" => 0,
                    "extensions" => "",
                    "always" => 0,
                    "always_destination" => "",
                    "user_busy" => 0,
                    "user_busy_destination" => "",
                    "user_not_registered" => 0,
                    "user_not_registered_destination" => "",
                    "no_answer" => 0,
                    "no_answer_destination" => "",
                    "call_type_vm_flag" => 1,
                    "failover_call_type" => 1,
                    "always_vm_flag" => 1,
                    "user_busy_vm_flag" => 1,
                    "user_not_registered_vm_flag" => 1,
                    "no_answer_vm_flag" => 1,
                    "failover_extensions" => ""
                );
                $this->db->where(array(
                        "product_id" => $did_info['product_id'],
                ));
                $this->db->update("dids", $did_update_array);
                $this->db->where('id',$did_info['product_id']);
                $this->db->update('products',array('status'=>'0'));
                $order_where = array(
                        "is_terminated" => 0,
                        "product_id" => $did_info['product_id'],
                        "accountid" => $did_info['parent_id']
                    );
                $order_update_array = array(
                        "is_terminated" => 1,
                        "termination_date" => gmdate('Y-m-d H:i:s'),
                        "termination_note" => "DID(" . $did_info['number'] . ") has been released by " . $this->accountinfo['number'] . "( " . $this->accountinfo['first_name'] . " " . $this->accountinfo['last_name'] . ") "
                    );
                $this->db->where($order_where);
                $this->db->update("order_items", $order_update_array);
                $this->db->where($where);
                $this->db->delete("reseller_products");
                $order_update_array = array(
                        "is_terminated" => 1,
                        "termination_date" => gmdate('Y-m-d H:i:s'),
                        "termination_note" => "DID(" . $did_info['number'] . ") has been released by " . $did_info['number'] . "( " . $did_info['first_name'] . " " . $did_info['last_name'] . ") "
                    );
                $order_where = array(
                        "is_terminated" => 0,
                        "product_id" => $did_info['product_id'],
                    );
                $this->db->where($order_where);
                $this->db->update("order_items", $order_update_array);
                 }
              }

            }
        }
        else{
            //did_list_release code
            if($this->accountinfo['type'] != 1){
                $where = array(
                    'product_id' => $this->postdata['did_id']
                );
            }
            $did_info = (array) $this->db->get_where("dids", $where)->result_array()[0];
            if(!empty($did_info)) {
            $this->did_model->did_number_release($did_info, $this->accountinfo, 'release');
            
            $final_array = astpp_array_merge($did_info, $this->accountinfo);
            $final_array['next_billing_date'] = gmdate('Y-m-d H:i:s');
            $this->did_release($final_array);
            
        }

        }
        $this->response(array(
            'status' => true,
            'success' => $this->lang->line("did_release")

        ), 200);
        
    }
    function _on_hold(){
        if(!$this->form_validation->required($this->postdata['did_id']) ){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('did_id_required' ) 
             ), 400 );
        }
        if(!is_numeric($this->postdata['did_id']) && $this->postdata['did_id'] != ""){
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('numeric_did_id') 
            ), 400 );
        }
        $dids_data = $this->db_model->getSelect ("*", "dids",
         array("product_id" => $this->postdata['did_id']))->row_array();
        if (empty($dids_data) || $dids_data == "" || (astpp_eq0($dids_data['accountid']) && astpp_eq0($dids_data['parent_id']) && astpp_eq0($dids_data['status'])) || ($dids_data['status'] != 2)) {
                $this->response(array(
                    'status' =>false,
                    'error' => $this->lang->line('did_not_found')
                ), 400);
        }
        $did_info = (array)$this->db->get_where('dids',array("product_id"=>$this->postdata['did_id']))->first_row();
        if(!empty($did_info)){
            if(astpp_gt($did_info['accountid'], 0)){
                $this->db->where('product_id',$this->postdata['did_id']);
                $this->db->update('dids',array('status'=>'0','accountid'=>'0','onhold_date'=> '0000-00-00 00:00:00'));
                $this->db->where('id',$did_info['product_id']);
               $this->db->update('products',array('status'=>'0'));
            }else{
              if(astpp_gt($did_info['parent_id'], 0)){
                $where = "product_id =". $did_info['product_id'];
                $reseller_info = (array)$this->db->get_where('accounts',array("id"=>$did_info['parent_id']))->first_row();

                 $did_update_array = array(
                    "accountid" => 0,
                    "parent_id" => 0,
                    "call_type" => 0,
                    "extensions" => "",
                    "always" => 0,
                    "always_destination" => "",
                    "user_busy" => 0,
                    "user_busy_destination" => "",
                    "user_not_registered" => 0,
                    "user_not_registered_destination" => "",
                    "no_answer" => 0,
                    "no_answer_destination" => "",
                    "call_type_vm_flag" => 1,
                    "failover_call_type" => 1,
                    "always_vm_flag" => 1,
                    "user_busy_vm_flag" => 1,
                    "user_not_registered_vm_flag" => 1,
                    "no_answer_vm_flag" => 1,
                    "failover_extensions" => ""
                );
                     $this->db->where(array(
                        "product_id" => $did_info['product_id'],
                    ));
                    $this->db->update("dids", $did_update_array);
                    $this->db->where('id',$did_info['product_id']);
                    $this->db->update('products',array('status'=>'0'));
                    $order_where = array(
                        "is_terminated" => 0,
                        "product_id" => $did_info['product_id'],
                        "accountid" => $did_info['parent_id']
                    );
                     $order_update_array = array(
                        "is_terminated" => 1,
                        "termination_date" => gmdate('Y-m-d H:i:s'),
                        "termination_note" => "DID(" . $did_info['number'] . ") has been released by " . $this->accountinfo['number'] . "( " . $this->accountinfo['first_name'] . " " . $this->accountinfo['last_name'] . ") "
                    );
                     $this->db->where($order_where);
                     $this->db->update("order_items", $order_update_array);
                     $this->db->where($where);
                     $this->db->delete("reseller_products");
                    
                     $order_update_array = array(
                        "is_terminated" => 1,
                        "termination_date" => gmdate('Y-m-d H:i:s'),
                        "termination_note" => "DID(" . $did_info['number'] . ") has been released by " . $did_info['number'] . "( " . $did_info['first_name'] . " " . $did_info['last_name'] . ") "
                    );
                    $order_where = array(
                        "is_terminated" => 0,
                        "product_id" => $did_info['product_id'],
                    );
                    $this->db->where($order_where);
                    $this->db->update("order_items", $order_update_array);
                }  
            }
        }
        $this->response(array(
            'status' => true,
            'success' => $this->lang->line("did_onhold")

        ), 200);
    }
    function did_release($final_array) {
        $this->common->mail_to_users('product_release',$final_array);
    }
    function did_billing_process($request_from,$accountid,$did,$skip_balance_check=false,$extra_info='') {
        $account = $this->db_model->getSelect ( "*", "accounts", array ('id' => $accountid ));
        $accountinfo = ( array ) $account->first_row ();


        $currency_name = $this->common->get_field_name ( 'currency', "currency", array (
                'id' => $accountinfo ['currency_id'] 
        ) );

        $didinfo = $this->did_model->get_did_by_number ( $did );
        if ($request_from['type'] == 1 && astpp_gt($accountinfo ['reseller_id'], 0)){ 
            if (astpp_gt($didinfo['parent_id'], 0) && astpp_gt($didinfo['accountid'], 0) )
            {
                return array("NOT_AVAL_FOR_PURCHASE","This DID is already purchased by someone.");
            }else{
                $reseller_pricing_query = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,reseller_products.buy_cost,reseller_products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.status,reseller_products.billing_days,reseller_products.billing_type,dids.last_modified_date,dids.product_id', array('dids.number'=>$didinfo['number']), 'reseller_products','dids.product_id=reseller_products.product_id', 'inner','','','','');
            if(!empty($reseller_pricing_query)){ 
                $reseller_pricing_result = ( array ) $reseller_pricing_query->first_row ();
                $didinfo ['call_type'] = (astpp_gt($reseller_pricing_result ['call_type'], 0))?$reseller_pricing_result ['call_type']:0;
                $didinfo ['extensions'] = (astpp_gt($reseller_pricing_result ['extensions'], 0))?$reseller_pricing_result ['extensions']:'';
                $didinfo ['setup_fee'] = (astpp_gt($reseller_pricing_result ['setup_fee'], 0))?$reseller_pricing_result ['setup_fee']:0;
                $didinfo ['price'] = (astpp_gt($reseller_pricing_result ['price'], 0))?$reseller_pricing_result ['price']:0;
                $didinfo ['connectcost'] = (astpp_gt($reseller_pricing_result ['connectcost'], 0))?$reseller_pricing_result ['connectcost']:0;
                $didinfo ['includedseconds'] = (astpp_gt($reseller_pricing_result ['includedseconds'], 0))?$reseller_pricing_result ['includedseconds']:0;
                $didinfo ['cost'] = (astpp_gt($reseller_pricing_result ['cost'], 0))?$reseller_pricing_result ['cost']:0;
                $didinfo ['inc'] = (astpp_gt($reseller_pricing_result ['inc'], 0))?$reseller_pricing_result ['inc']:0;
                $didinfo ['billing_days'] = $reseller_pricing_result['billing_days'];
                $didinfo ['billing_type'] = $reseller_pricing_result['billing_type'];
                
            }

            }
        }
        if ($request_from['type'] == 1 && astpp_eq0($accountinfo ['reseller_id'])){ 
            if (astpp_gt($didinfo['parent_id'], 0) && astpp_gt($didinfo['accountid'], 0) )
            {
                return array("NOT_AVAL_FOR_PURCHASE","This DID is already purchased by someone.");
            }else{ 
                $reseller_pricing_query = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,products.buy_cost,products.commission,products.setup_fee,products.price,products.billing_type,products.billing_days,products.status,dids.last_modified_date,dids.product_id', array('dids.number'=>$didinfo['number']), 'products','dids.product_id=products.id', 'inner','','','','');
            if(!empty($reseller_pricing_query)){
                $reseller_pricing_result = ( array ) $reseller_pricing_query->first_row ();
                $didinfo ['call_type'] = (astpp_gt($reseller_pricing_result ['call_type'], 0))?$reseller_pricing_result ['call_type']:0;
                $didinfo ['extensions'] = (astpp_gt($reseller_pricing_result ['extensions'], 0))?$reseller_pricing_result ['extensions']:'';
                $didinfo ['setup_fee'] = (astpp_gt($reseller_pricing_result ['setup_fee'], 0))?$reseller_pricing_result ['setup_fee']:0;
                $didinfo ['price'] = (astpp_gt($reseller_pricing_result ['price'], 0))?$reseller_pricing_result ['price']:0;
                $didinfo ['connectcost'] = (astpp_gt($reseller_pricing_result ['connectcost'], 0))?$reseller_pricing_result ['connectcost']:0;
                $didinfo ['includedseconds'] = (astpp_gt($reseller_pricing_result ['includedseconds'], 0))?$reseller_pricing_result ['includedseconds']:0;
                $didinfo ['cost'] = (astpp_gt($reseller_pricing_result ['cost'], 0))?$reseller_pricing_result ['cost']:0;
                $didinfo ['inc'] = (astpp_gt($reseller_pricing_result ['inc'], 0))?$reseller_pricing_result ['inc']:0;
                $didinfo ['billing_days'] = $reseller_pricing_result['billing_days'];
                $didinfo ['billing_type'] = $reseller_pricing_result['billing_type'];
            }

            }
        }

        if (astpp_gt($accountinfo ['reseller_id'], 0) && astpp_eq0($accountinfo ['type'])  ) {

            if (astpp_gt($didinfo['accountid'], 0))
            {
                return array("NOT_AVAL_FOR_PURCHASE","This DID is already purchased by someone.");
            }else{ 
            
                $customer_result_array = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,reseller_products.buy_cost,reseller_products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.status,dids.last_modified_date,dids.product_id', array('dids.number'=>$didinfo['number'],'reseller_products.account_id'=>$accountinfo['reseller_id']), 'reseller_products','dids.product_id=reseller_products.product_id', 'inner','','','','');
                $customer_result_array = ( array ) $customer_result_array->first_row ();
                $didinfo ['call_type'] = $customer_result_array ['call_type'];
                $didinfo ['extensions'] = $customer_result_array ['extensions'];
                $didinfo ['setup_fee'] = $customer_result_array ['setup_fee'];
                $didinfo ['price'] = $customer_result_array ['price'];
                $didinfo ['connectcost'] = $customer_result_array ['connectcost'];
                $didinfo ['includedseconds'] = $customer_result_array ['includedseconds'];
                $didinfo ['cost'] = $customer_result_array ['cost'];
                $didinfo ['inc'] = $customer_result_array ['inc'];
                $didinfo ['billing_days'] = $customer_result_array['billing_days'];
                $didinfo ['billing_type'] = $customer_result_array['billing_type'];
            }
        }
        if(astpp_eq0($accountinfo ['reseller_id']) && astpp_eq0($accountinfo ['type'])){
            if (astpp_gt($didinfo['accountid'], 0))
            {
                return array("NOT_AVAL_FOR_PURCHASE","This DID is already purchased by someone.");
            }else{ 
                $did_info_arr = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,products.buy_cost,products.commission,products.setup_fee,products.price,products.billing_type,products.billing_days,products.status,dids.last_modified_date,dids.product_id', array('dids.number'=>$didinfo['number']), 'products','dids.product_id=products.id', 'inner','','','','');

                $did_info_arr = ( array ) $did_info_arr->first_row ();
                $didinfo ['call_type'] = $did_info_arr ['call_type'];
                $didinfo ['extensions'] = $did_info_arr ['extensions'];
                $didinfo ['setup_fee'] = $did_info_arr ['setup_fee'];
                $didinfo ['price'] = $did_info_arr ['price'];
                $didinfo ['connectcost'] = $did_info_arr ['connectcost'];
                $didinfo ['includedseconds'] = $did_info_arr ['includedseconds'];
                $didinfo ['cost'] = $did_info_arr ['cost'];
                $didinfo ['inc'] = $did_info_arr ['inc'];
                $didinfo ['billing_days'] = $did_info_arr['billing_days'];
                $didinfo ['billing_type'] = $did_info_arr['billing_type'];
            }


        }

        $available_bal = $this->db_model->get_available_bal ( $accountinfo );
        $accountinfo ['did_number'] = $didinfo ['number'];
        $accountinfo ['did_country_id'] = $didinfo ['country_id'];
        $accountinfo ['did_setup'] = $this->common_model->calculate_currency ( $didinfo ['setup_fee'], '', $currency_name, true, true );
        $accountinfo ['did_monthlycost'] = $this->common_model->calculate_currency ( $didinfo ['price'], '', $currency_name, true, true );
        $accountinfo ['did_maxchannels'] = $didinfo ['maxchannels'];

        //$account_balance = $accountinfo ['posttoexternal'] == 1 ? $accountinfo ['credit_limit'] - ($accountinfo ['balance']) : $accountinfo ['balance'];

        $account_balance = $accountinfo ['posttoexternal'] == 1 ? astpp_n($accountinfo ['credit_limit']) - (astpp_n($accountinfo ['balance'])) : $accountinfo ['balance'];        
        $account_balance =$this->common_model->calculate_currency($account_balance, '', $currency_name, true, false );  

        $didinfo ["setup_fee"] = (isset($extra_info['setup_fee']) && astpp_gt($extra_info['setup_fee'], 0))?$extra_info['setup_fee']:$didinfo ["setup_fee"];

        $didinfo ["price"] = (isset($extra_info['price']) && astpp_gt($extra_info['price'], 0))?$extra_info['price']:$didinfo ["price"];


        if ($account_balance  >= (astpp_n($didinfo ["setup_fee"])+astpp_n($didinfo ["price"])) || $skip_balance_check==true) {
            
            if ($request_from['type'] == 2 || ($request_from['type'] == 1 && $request_from['id']) == $accountinfo ['id']){

                $field_name = $accountinfo ['type'] == '1' ? "parent_id" : 'accountid';

                        
            }elseif($request_from['type'] == 1){

                $field_name = $accountinfo ['type'] == 1 ? "parent_id" : 'accountid';
                if (astpp_eq0($accountinfo ['type']) ||$accountinfo ['type'] == 3 )
                {
                    $this->db_model->update ( "dids", array (
                        "accountid" => $accountinfo ['id']
                    ), array (
                        "id" => $didinfo ['id']
                    ) );
                }

                
            }elseif(astpp_eq0($request_from['type']) || $request_from['type'] == 4 || $request_from['type'] == 3){
                $this->db_model->update ( "dids", array (
                    "accountid" => $accountinfo ['id'] 
                ), array (
                    "id" => $didinfo ['id']
                ) );
            }
            $next_bill_date = gmdate('Y-m-d H:i:s');
            $today = gmdate("d");
            
            if($today <= 28 && $didinfo['billing_type'] == "2"){
                //Riya  new issue no 2738 Monthly billing 28 days billing issue.
                //$next_bill_date =gmdate("Y-m-d H:i:s",strtotime("+".($didinfo['billing_days'])." days"));
                $next_bill_date =astpp_gmdate("Y-m-d H:i:s",strtotime($next_bill_date."+".(1)." months"));
                $next_bill_date =astpp_date ("Y-m-d H:i:s",strtotime($next_bill_date));
            }
            //Riya  new issue no 2738 Monthly billing 28 days billing issue.
            /*if(gmdate("d",strtotime($next_bill_date)) > 28 && $didinfo['billing_type'] == "2"){
                $next_bill_date =gmdate("Y-m-01 H:i:s",strtotime($next_bill_date."+".($didinfo['billing_days'])." days"));
            }*/
            if($today > 28 && $didinfo['billing_type'] == "2"){
                //$next_bill_date = gmdate("Y-m-01 H:i:s ",strtotime("+".(2)." months"));
                //Riya issue no 2738 Monthly billing 28 days billing issue.
                //Riya final new issue no 2738 Monthly billing 28 days billing issue.
                $first_of_month = strtotime(gmdate("Y-m-1 H:i:s"));
                //end
                $days_of_month = $this->common->days_in_next_month();
                if($today == 29 && $today <= $days_of_month){
                    //Riya final new issue no 2738 Monthly billing 28 days billing issue.
                    $next_bill_date = astpp_gmdate("Y-m-29 H:i:s",strtotime("+".(1)." months",$first_of_month));
                }
                elseif($today == 30 && $today <= $days_of_month){
                    //Riya final new issue no 2738 Monthly billing 28 days billing issue.
                    $next_bill_date = astpp_gmdate("Y-m-30 H:i:s",strtotime("+".(1)." months",$first_of_month));
                }
                else{
                    //Riya final new issue no 2738 Monthly billing 28 days billing issue.
                    $next_bill_date = astpp_gmdate("Y-m-".$days_of_month ." H:i:s",strtotime("+".(1)." months",$first_of_month));
                }
            //end
            }
            
            if(astpp_eq0($didinfo['billing_type'])){
                //Riya  new issue no 2738 Monthly billing 28 days billing issue.
                $next_bill_date = (astpp_eq0($didinfo['billing_days']))?astpp_gmdate('Y-m-d H:i:s', strtotime('+10 years')):astpp_gmdate("Y-m-d H:i:s",strtotime("+".($didinfo['billing_days'])." days"));
            }
            if($didinfo['billing_type'] == 1){
                //Riya  new issue no 2738 Monthly billing 28 days billing issue.
                $next_bill_date = (astpp_eq0($didinfo['billing_days']))?astpp_gmdate('Y-m-d H:i:s', strtotime('+10 years')):astpp_gmdate("Y-m-d H:i:s",strtotime("+".($didinfo['billing_days'])." days"));
            }   
            unset($didinfo['id']);
            $final_array = astpp_array_merge($accountinfo,$didinfo);
            $final_array['next_billing_date'] = $next_bill_date;
            $final_array['name'] =$final_array['number'];
            $final_array['category_name'] ="DID";
            $final_array['payment_by'] ="Account Balance";
            $final_array['quantity']=1;
            $final_array['price'] = $final_array['price'];
            $final_array['setup_fee'] = $final_array['setup_fee'];
            $final_array['total_price']=(astpp_n($final_array['setup_fee'])+astpp_n($final_array['price']))*(astpp_n($final_array['quantity']));
            $final_array['price']=(astpp_n($final_array['setup_fee'])+astpp_n($final_array['price']));
            $this->common->mail_to_users ( 'product_purchase',$final_array);            
            return array("SUCCESS","DID Purchased Successfully.");
        }else{          
            return array("INSUFFIECIENT_BALANCE","Insufficient fund to purchase this DID.");
        }
    }

    // Kinjal issue no 3625
    function _forward(){
        if($this->form_validation->required($this->postdata['did_id']) == '') {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('did_id_required')
            ), 400);
        }
        if($this->postdata['did_id'] != "" && !$this->form_validation->numeric($this->postdata['did_id'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('did_id_numeric')
            ), 400);
        }
        $did_info =(array) $this->db_model->getSelect("*", "dids", array("product_id" => $this->postdata['did_id']))->first_row();
        if(empty($did_info) || $did_info == ""){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('did_not_found')
            ), 400);
        }
        if($did_info != "" && (astpp_eq0($did_info['accountid']) && astpp_eq0($did_info['parent_id'])) ){
         $this->response(array(
                'status' => false,
                'error' => $this->lang->line('did_not_assign')
            ), 400);
        }
        if($this->postdata ['call_type'] == "" ) {
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ( 'call_type_required')
            ), 400 );
        }
        // Kinjal issue no 3737
        if(($this->postdata ['call_type'] == "0" || $this->postdata ['call_type'] == "5")  && $this->postdata ['call_type_destination'] == "" ) {
        // END
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ( 'call_type_destination_required')
            ), 400 );
        }
        $pbx_status = $this->db_model->countQuery("*", "addons", array(
            "package_name" => "pbx"
        ));

        // Kinjal issue no 4591
        $foip_status = $this->db_model->countQuery("*", "addons", array(
            "package_name" => "foip"
        ));
        if($pbx_status != '1'){
            if($foip_status == '1'){
                 if(!($this->postdata ['call_type'] == '0' || $this->postdata ['call_type'] == '1' || $this->postdata ['call_type'] == '2' || $this->postdata ['call_type'] == '3' || $this->postdata ['call_type'] == '4'|| $this->postdata ['call_type'] == '5' || $this->postdata['call_type'] == '6' )) {
                    $this->response ( array (
                        'status' => false,
                        'error' => $this->lang->line ( 'invalid_call_type')
                    ), 400 );
                }else{
                    $did_forward['call_type'] = $this->postdata['call_type'];
                    $did_forward['extensions'] = $this->postdata['call_type_destination'];   
                }
            }else{
                if(!($this->postdata ['call_type'] == '0' || $this->postdata ['call_type'] == '1' || $this->postdata ['call_type'] == '2' || $this->postdata ['call_type'] == '3' || $this->postdata ['call_type'] == '4'|| $this->postdata ['call_type'] == '5' )) {
                    $this->response ( array (
                        'status' => false,
                        'error' => $this->lang->line ( 'invalid_call_type')
                    ), 400 );
                }    
            }
            // END
            if($this->postdata ['always'] == "") {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'always_required')
                ), 400 );
            }
            if(($this->postdata ['always'] == "0" || $this->postdata ['always'] == "5")  && $this->postdata ['always_destination'] == "" ) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'always_destination_required')
                ), 400 );
            }
            if(!($this->postdata ['always'] == '0' || $this->postdata ['always'] == '1' || $this->postdata ['always'] == '2' || $this->postdata ['always'] == '3' || $this->postdata ['always'] == '4'|| $this->postdata ['always'] == '5' )) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'invalid_always')
                ), 400 );
            }
            if($this->postdata ['user_busy'] == "") {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'user_busy_required')
                ), 400 );
            }
            if(($this->postdata ['user_busy'] == "0" || $this->postdata ['user_busy'] == "5")  && $this->postdata ['user_busy_destination'] == "" ) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'user_busy_destination_required')
                ), 400 );
            }
            if(!($this->postdata ['user_busy'] == '0' || $this->postdata ['user_busy'] == '1' || $this->postdata ['user_busy'] == '2' || $this->postdata ['user_busy'] == '3' || $this->postdata ['user_busy'] == '4'|| $this->postdata ['user_busy'] == '5' )) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'invalid_user_busy')
                ), 400 );
            }
            if($this->postdata ['user_not_registered'] == "") {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'user_not_registered_required')
                ), 400 );
            }
            if(($this->postdata ['user_not_registered'] == "0" || $this->postdata ['user_not_registered'] == "5")  && $this->postdata ['user_not_registered_destination'] == "" ) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'user_not_registered_destination_required')
                ), 400 );
            }
            if(!($this->postdata ['user_not_registered'] == '0' || $this->postdata ['user_not_registered'] == '1' || $this->postdata ['user_not_registered'] == '2' || $this->postdata ['user_not_registered'] == '3' || $this->postdata ['user_not_registered'] == '4'|| $this->postdata ['user_not_registered'] == '5' )) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'invalid_user_not_registered')
                ), 400 );
            }
            if($this->postdata ['no_answer'] == "" ) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ('no_answer_required')
                ), 400 );
            }
            if(($this->postdata ['no_answer'] == "0" || $this->postdata ['no_answer'] == "5")  && $this->postdata ['no_answer_destination'] == "" ) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'no_answer_destination_required')
                ), 400 );
            }
            if(!($this->postdata ['no_answer'] == '0' || $this->postdata ['no_answer'] == '1' || $this->postdata ['no_answer'] == '2' || $this->postdata ['no_answer'] == '3' || $this->postdata ['no_answer'] == '4'|| $this->postdata ['no_answer'] == '5' )) {
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ( 'invalid_no_answer_destination')
                ), 400 );
            }
        }
        $did_forward['call_type'] = $this->postdata['call_type'] != "" ? $this->postdata['call_type'] : $did_info['call_type'];
        if($pbx_status == '1'){
            // Kinjal issue no 4591
            if($foip_status == '1'){
                if(!($this->postdata ['call_type'] == '0' || $this->postdata ['call_type'] == '1' || $this->postdata ['call_type'] == '2' || $this->postdata ['call_type'] == '3' || $this->postdata ['call_type'] == '4'|| $this->postdata ['call_type'] == '5' || $this->postdata ['call_type'] == '7' || $this->postdata ['call_type'] == '8' || $this->postdata ['call_type'] == '9' || $this->postdata ['call_type'] == '10' || $this->postdata ['call_type'] == '6' || $this->postdata ['call_type'] == '11')) {
                    $this->response ( array (
                        'status' => false,
                        'error' => $this->lang->line ( 'invalid_call_type')
                    ), 400 );
                    $did_forward['call_type'] = $this->postdata['call_type'];
                    $did_forward['extensions'] = $this->postdata['call_type_destination'];   
                }
            }else{
                if(!($this->postdata ['call_type'] == '0' || $this->postdata ['call_type'] == '1' || $this->postdata ['call_type'] == '2' || $this->postdata ['call_type'] == '3' || $this->postdata ['call_type'] == '4'|| $this->postdata ['call_type'] == '5' || $this->postdata ['call_type'] == '7' || $this->postdata ['call_type'] == '8' || $this->postdata ['call_type'] == '9' || $this->postdata ['call_type'] == '10' || $this->postdata ['call_type'] == '11')) {
                    $this->response ( array (
                        'status' => false,
                        'error' => $this->lang->line ( 'invalid_call_type')
                    ), 400 );
                }
            }
            // END
            if($this->postdata['call_type'] == 11){
                $time_condition_value = (array)$this->db_model->getSelect('*','time_condition',array("accountid"=>$did_info['accountid'],"id"=>$this->postdata['call_type_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['extensions']  = ($this->postdata['call_type_destination'] != "") ? ((!empty($time_condition_value) && $time_condition_value != "") ? $this->postdata['call_type_destination'] : $this->response(array(
                 'status' => false,'success' => $this->lang->line('timecondition_not_found')), 400)) : $did_info['extensions'];
                // END
            }
            if($this->postdata['call_type'] == 10){
                $ivr_value = (array)$this->db_model->getSelect('*','pbx_ivr_specification',array("accountid"=>$did_info['accountid'],"id"=>$this->postdata['call_type_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['extensions']  = ($this->postdata['call_type_destination'] != "") ? ((!empty($ivr_value) && $ivr_value != "") ? $this->postdata['call_type_destination'] : $this->response(array(
                'status' => false,'success' => $this->lang->line('ivr_not_found')), 400)) : $did_info['extensions'];
                // END
            }
            if($this->postdata['call_type'] == 9){
                $queue_value =(array)$this->db_model->getSelect('*','pbx_queue',array("account_id"=>$did_info['accountid'],"id"=>$this->postdata['call_type_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['extensions']  = ($this->postdata['call_type_destination'] != "") ? ((!empty($queue_value) && $queue_value != "") ? $this->postdata['call_type_destination'] : $this->response(array(
                'status' => false,'success' => $this->lang->line('queue_not_found')), 400)) : $did_info['extensions'];
                // END
            }
            if($this->postdata['call_type'] == 8){
                $conference_value =(array) $this->db_model->getSelect('*','pbx_conference_specification',array("accountid"=>$did_info['accountid'],"id"=>$this->postdata['call_type_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['extensions']  = ($this->postdata['call_type_destination'] != "") ? ((!empty($conference_value) && $conference_value != "") ? $this->postdata['call_type_destination'] : $this->response(array(
                'status' => false,'success' => $this->lang->line('conference_not_found')), 400)) : $did_info['extensions'];
                // END
            }
            if($this->postdata['call_type'] == 7){
                $ringgroup_value =(array)$this->db_model->getSelect('*','pbx_ringgroup',array("accountid"=>$did_info['accountid'],"id"=>$this->postdata['call_type_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['extensions']  = ($this->postdata['call_type_destination'] != "") ? ((!empty($ringgroup_value) && $ringgroup_value != "") ? $this->postdata['call_type_destination'] : $this->response(array(
                'status' => false,'success' => $this->lang->line('ringgroup_not_found')), 400)) : $did_info['extensions'];
                // END
            }
        }
        if($pbx_status != '1'){
            $did_forward['always'] = $this->postdata['always'] != "" ? $this->postdata['always'] : $did_info['always'];
            $did_forward['user_busy'] = $this->postdata['user_busy'] != "" ? $this->postdata['user_busy'] : $did_info['user_busy'];
            $did_forward['user_not_registered'] = $this->postdata['user_not_registered'] != "" ? $this->postdata['user_not_registered'] : $did_info['user_not_registered'];
            $did_forward['no_answer'] = $this->postdata['no_answer'] != "" ? $this->postdata['no_answer'] : $did_info['no_answer'];
        }
        if((astpp_eq0($this->postdata['call_type']) || $this->postdata['call_type'] == 5) && $this->postdata['call_type_destination'] != "" && $this->postdata['call_type'] != ""){
             $sip_call_type = (array)$this->db_model->getSelect('*','sip_devices',array("accountid"=>$did_info['accountid'],'status' => 0,"username"=>$this->postdata['call_type_destination']))->first_row();
             // Kinjal issue no 4591
              $did_forward['extensions']  = ($this->postdata['call_type_destination'] != "") ? ((!empty($sip_call_type) && $sip_call_type != "") ? $this->postdata['call_type_destination'] : $this->response(array(
              'status' => false,'success' => $this->lang->line('sip_device_not_found')), 400)) : $did_info['extensions'];
              // END
        }
        else{
            $did_forward['extensions'] = $this->postdata['call_type_destination'] != "" ? $this->postdata['call_type_destination'] : $did_info['extensions'];
        }
        if($pbx_status != '1'){
            if((astpp_eq0($this->postdata['always']) || $this->postdata['always'] == 5) && $this->postdata['always_destination'] != "" && $this->postdata['always'] != ""){
                $sip_always = (array)$this->db_model->getSelect('*','sip_devices',array("accountid"=>$did_info['accountid'],'status' => 0,"username"=>$this->postdata['always_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['always_destination']  = (!empty($sip_always) && $sip_always != "") ? $this->postdata['always_destination'] : $this->response(array(
                'status' => false,'success' => $this->lang->line('sip_device_not_found')), 400); 
                // END
            }else{
                $did_forward['always_destination'] = $this->postdata['always_destination'] != "" ? $this->postdata['always_destination'] : $did_info['always_destination'];
            }
            if((astpp_eq0($this->postdata['user_busy']) || $this->postdata['user_busy'] == 5) && $this->postdata['user_busy_destination'] != "" && $this->postdata['user_busy'] != ""){
                $sip_user_busy = $this->db_model->getSelect('*','sip_devices',array("accountid"=>$did_info['accountid'],'status' => 0,"username"=>$this->postdata['user_busy_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['user_busy_destination']  = (!empty($sip_user_busy) && $sip_user_busy != "") ? $this->postdata['call_type_destination'] : $this->response(array(
                'status' => false,'success' => $this->lang->line('sip_device_not_found')), 400); 
                // END
            }else{
                $did_forward['user_busy_destination'] = $this->postdata['user_busy_destination'] != "" ? $this->postdata['user_busy_destination'] : $did_info['user_busy_destination'];
            }
            if((astpp_eq0($this->postdata['user_not_registered']) || $this->postdata['user_not_registered'] == 5) && $this->postdata['user_not_registered_destination'] != "" && $this->postdata['user_not_registered_destination'] != ""){
                $sip_user_not_registered = $this->db_model->getSelect('*','sip_devices',array("accountid"=>$did_info['accountid'],'status' => 0,"username"=>$this->postdata['user_not_registered_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['user_not_registered_destination']  = (!empty($sip_user_not_registered) && $sip_user_not_registered != "") ? $this->postdata['user_not_registered_destination'] : $this->response(array(
                'status' => false,'success' => $this->lang->line('sip_device_not_found')), 400); 
                // END
            }else{
                $did_forward['user_not_registered_destination'] = $this->postdata['user_not_registered_destination'] != "" ? $this->postdata['user_not_registered_destination'] : $did_info['user_not_registered_destination'];
            }
            if((astpp_eq0($this->postdata['no_answer']) || $this->postdata['no_answer'] == 5) && $this->postdata['no_answer_destination'] != "" && $this->postdata['no_answer'] != ""){
                $sip_no_answer = (array)$this->db_model->getSelect('*','sip_devices',array("accountid"=>$did_info['accountid'],'status' => 0,"username"=>$this->postdata['no_answer_destination']))->first_row();
                // Kinjal issue no 4591
                $did_forward['user_not_registered_destination']  = (!empty($sip_no_answer) && $sip_no_answer != "") ? $this->postdata['no_answer_destination'] : $this->response(array(
                'status' => false,'success' => $this->lang->line('sip_device_not_found')), 400); 
                // END
            }else{
                $did_forward['no_answer_destination'] = $this->postdata['no_answer_destination'] != "" ? $this->postdata['no_answer_destination'] : $did_info['no_answer_destination'];
            }
        }
        $this->db->where('product_id',$this->postdata['did_id']);
        $this->db->update("dids", $did_forward);
        if (isset($did_forward) || !empty($did_forward)) {
            // Kinjal issue no 3808
            $did_forward['call_type_destination'] = $did_forward['extensions'];
            unset($did_forward['extensions']);
            $this->response(array(
                'status' => true,
                'data' => $did_forward,
                'success' => $this->lang->line('did_forward')
                ), 200);
            // END
            } else {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('something_wrong')
                ), 400);
            }
    }
    // END
}

?>
