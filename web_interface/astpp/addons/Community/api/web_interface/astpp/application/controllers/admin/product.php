<?php

require APPPATH . '/controllers/common/account.php';

#[\AllowDynamicProperties]
class Product extends Account
{

    protected $postdata = "";

    function __construct()
    {
        parent::__construct();
        $this->load->model('common_model');
        $this->load->model('db_model');
        $this->load->library('Form_validation');
        $this->accountinfo = $this->get_account_info();
        if ($this->accountinfo['type'] != -1 && $this->accountinfo ['type'] != 1 && $this->accountinfo ['type'] != 2) {
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
        $function = isset ($this->postdata ['action']) ? $this->postdata ['action'] : '';
        $this->api_log->write_log('API URL : ', base_url() . "" . $_SERVER['REQUEST_URI']);
        $this->api_log->write_log('Params : ', json_encode($this->postdata));
        if ($function != '') {
            $function = 'product_' . $function;
            if (astpp_gt(( int )method_exists($this, $function), 0)) {
                $this->$function ();
            } else {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('unknown_method')
                ), 400);
            }
        } else {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('unknown_method')
            ), 400);
        }
        die;
    }
     function product_optin(){
        if($this->accountinfo['type'] == '1'){
            if(!isset ( $this->postdata ['product_id']) || $this->postdata['product_id'] == '' ) {
            $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('product_not_found') ), 400 );
            }
            $reseller_products_info = $this->db_model->getSelect("*", "reseller_products", array("product_id" => $this->postdata['product_id'], "account_id" => $this->accountinfo['id'],"reseller_id"=>$this->accountinfo['reseller_id']))->first_row();
            if(!empty($reseller_products_info)){
                $this->response ( array (
                'status' => false,
                'error' => $this->lang->line ('product_already_optin')  ), 400 );
            }
            if(astpp_gt($this->accountinfo['reseller_id'], 0)){
                $products_info = $this->db_model->getSelect("*", "products", array("id" => $this->postdata['product_id'], "status" => 0))->row_array();
                if($products_info['product_category'] == 1 || $products_info['product_category'] == 2){
                $this->db->where(array('product_id' => $this->postdata['product_id'],'is_owner' => 0));
                $product_data = (array)$this->db->get('reseller_products')->first_row();
                //print_r($this->db->last_query());die;
                }
            }else{
                $this->db->where(array('id' => $this->postdata['product_id'],'can_resell' => 0,'product_category' => 1,'status'=> 0 ));
                $this->db->or_where('product_category',2);
                $product_data = (array)$this->db->get('products')->first_row();
            }
            if(empty($product_data)){
                    $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line ('product_not_found' )  
                ), 400 );
            }
            if(astpp_eq0($product_data['can_resell'])){
                if(astpp_eq0($this->accountinfo['is_distributor'])){
                    $insert_array['setup_fee'] = isset($this->postdata['setup_fee']) && $this->postdata['setup_fee'] != "" ? $this->postdata['setup_fee'] : $product_data['setup_fee'];
                    $insert_array['price'] = isset($this->postdata['setup_fee']) && $this->postdata['price'] != "" ? $this->postdata['price'] : $product_data['price'];
                }
                else{
                    $insert_array['setup_fee'] = $product_data['setup_fee'];
                    $insert_array['price'] = $product_data['price'];
                }
                $insert_array['product_id'] = $this->postdata['product_id'];
                $insert_array['account_id'] = $this->accountinfo['id'];
                $insert_array['reseller_id'] = astpp_gt($this->accountinfo['reseller_id'], 0) ? $this->accountinfo['reseller_id'] : 0;
                $insert_array['is_owner'] = 1;
                $insert_array['is_optin'] = 0;
                $insert_array['optin_date'] = gmdate("Y-m-d H:i:s");
                $insert_array['status'] = $product_data['status'];
                $insert_array['free_minutes'] = $product_data['free_minutes'];
                $insert_array['commission'] = $product_data['commission'];
                $insert_array['modified_date'] =  gmdate("Y-m-d H:i:s");
                $insert_array['country_id'] = $product_data['country_id'];
                $insert_array['buy_cost'] = $product_data['buy_cost'];
                $insert_array['billing_days'] = $product_data['billing_days'];
                $insert_array['billing_type'] = $product_data['billing_type'];
                $this->db->insert("reseller_products", $insert_array);
            }
        }
        if (!empty($insert_array)) {
            $this->response(array(
                'status' => true,
                'data' => $insert_array,
                'success' => $this->lang->line('product_optin')
            ), 200);
        } else {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('product_not_found')
            ), 400);
        }
    }

    function product_list()
    {
        $currency_id = $this->common->get_field_name('currency', 'currency', array('id' => $this->accountinfo['currency_id']));
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
                if(isset($object_where_key) && $object_where_key == 'pattern'){
                    $this->db->like('pattern', '^'.$object_where_params['pattern'].'.*');
                }else{
                    // Kinjal ASTPPCOM-1259 Start
                    if($object_where_key == 'country_id' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('invalid_country')  
                            ), 400 );
                        }
                    }
                    if($object_where_key == 'buy_cost' && $object_where_value!= "" ){
                        if(!$this->form_validation->greater_than($object_where_value,-1)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('enter_correct_buy_cost')  
                            ), 400 );
                        }
                    }
                    if($object_where_key == 'setup_fee' && $object_where_value!= "" ){
                        if(!$this->form_validation->numeric($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('enter_setup_fee')  
                            ), 400 );
                        }
                    }
                    if($object_where_key == 'price' && $object_where_value!= "" ){
                        if(!$this->form_validation->numeric($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('numeric_price')  
                            ), 400 );
                        }
                    }
                    if($object_where_key == 'commission' && $object_where_value!= "" ){
                        if(!$this->form_validation->numeric($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('numeric_commision')  
                            ), 400 );
                        }
                    } 
                    if($object_where_key == 'billing_days' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('numeric_billing_days')  
                            ), 400 );
                        }
                    } 
                    if($object_where_key == 'billing_type' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('enter_correct_billing_type')  
                            ), 400 );
                        }
                    }
                    if($object_where_key == 'free_minutes' && $object_where_value!= "" ){
                        if(!$this->form_validation->integer($object_where_value)){
                            $this->response ( array (
                                'status' => false,
                                'success' =>  $this->lang->line ('numeric_free_minutes')  
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
                    // Kinjal ASTPPCOM-1259 END
                    $where[$object_where_key] = $object_where_value;
                }
            }
        }
        if(isset($where['product_category']) && $where['product_category']!=""){
            $product_category = $this->common->get_field_name('id','category',array('id'=>$where['product_category']));
            if($product_category == ""){
                $this->response ( array (
                    'status' => false,
                    'error' => $this->lang->line('invalid_product_category')
                ), 400 );
            }
        }
        $categoryinfo = $this->db_model->getSelect("GROUP_CONCAT('''',id,'''') as id", "category", "code NOT IN ('REFILL','DID')");
        if(!empty($where)) {
            $this->db->where($where);
        }
        $start = astpp_n($this->postdata['start_limit'])-1;
        $limit = $this->postdata['end_limit'];
        $no_of_records = (int)$limit - (int)$start;
        $this->db->limit($no_of_records, $start);
            if ($this->accountinfo['type'] == 1) {
                if ($categoryinfo->num_rows > 0) {
                    $categoryinfo = $categoryinfo->result_array()[0]['id'];
                    $this->db->where("product_category IN (" . $categoryinfo . ")", NULL, false);
                }
                $temp_where = "(reseller_products.is_optin = 0 OR reseller_products.is_owner=0)";
                $this->db->where($temp_where);
                $tmp_where = "(reseller_products.status = 0 OR reseller_products.status =1)";
                $this->db->where($tmp_where);
                $str_where = "(products.status = 0 OR reseller_products.is_owner=0)";
                $this->db->where($str_where);
                $this->db->where('reseller_products.account_id', $this->accountinfo['id']);
                $available_product = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.country_id,reseller_products.status as reseller_status,reseller_products.buy_cost,reseller_products.reseller_id,products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,(CASE WHEN reseller_products.billing_type = 2 THEN "Monthly" ELSE reseller_products.billing_days END) as billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('products.is_deleted' => 0), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', '', '');
            } else {
                $this->db->order_by("id", "DESC");
                $where = array("is_deleted" => "0", "product_category <>" => 4);
                $available_product = $this->db_model->select("name,product_category,country_id,buy_cost,reseller_id,commission,setup_fee,price,billing_type,billing_days,free_minutes,status,(CASE WHEN billing_type = 2 THEN 'Monthly' ELSE  billing_days END) as billing_days", "products", $where, "", "", '', '');
            }
            $available_products = $available_product->result_array();
            $count = $available_product->num_rows();

            if (empty($available_products)) {
                $this->response(array(
                    'total_count' => 0,
                    'data' => $available_products,
                    'error' => $this->lang->line('no_records_found')
                ), 200);
            } else {
                foreach ($available_products as $key => $value) {
                    if ($this->accountinfo['type'] == 1) {
                        unset($value['status']);
                        unset($value['commission']);
                    }
                    $available_products[$key] = $value;
                    if ($this->accountinfo['type'] != 1) {
                    $available_products[$key]['retired'] = $available_products[$key]['status'];
                    }
                    if ($this->accountinfo['type'] != 1) {
                        $available_products[$key]['reseller_name'] = $this->common->reseller_select_value('first_name,last_name,number,company_name', 'accounts', $value['reseller_id']);
                        unset($available_products[$key]['last_modified_date']);

                    }

                    $available_products[$key]['country_name'] = $this->common->get_field_name_country_camel("country", "countrycode", $value['country_id']);
                    $available_products[$key]['product_category'] = $this->common->get_field_name("name", "category", $value['product_category']);
                    $available_products[$key]['buy_cost'] = $this->common_model->to_calculate_currency($value['buy_cost'], '', $currency_id);
                    $available_products[$key]['setup_fee'] = $this->common_model->to_calculate_currency($value['setup_fee'], '', $currency_id);
                    $available_products[$key]['price'] = $this->common_model->to_calculate_currency($value['price'], '', $currency_id);
                    $available_products[$key]['billing_type'] = $this->common->get_renewal_type_category_list('billing_type', 'billing_type', $value['billing_type']);
                    $available_products[$key]['retired'] = astpp_eq0($value['status']) ? "No" : "Yes";
                    unset($available_products[$key]['country_id'],$available_products[$key]['reseller_id']);
                }
                $this->response(array(
                    'total_count' => $count,
                    'data' => $available_products,
                    'success' => $this->lang->line("product_list_information")

                ), 200);
            }
    }

    function product_optin_list()
    {
        $currency_id = $this->common->get_field_name('currency', 'currency', array('id' => $this->accountinfo['currency_id']));
        if (!isset($this->postdata['start_limit']) || $this->postdata['start_limit'] == "" || !isset($this->postdata['end_limit']) || $this->postdata['end_limit'] == "") {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('error_param_missing')
            ), 400);
        } else {
            if (astpp_le($this->postdata['start_limit'], 0) || astpp_le($this->postdata['end_limit'], 0)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('number_greater_zero')
                ), 400);
            }
            $start = astpp_n($this->postdata['start_limit']) - 1;
            $limit = $this->postdata['end_limit'];
            $object_where_params = $this->postdata['object_where_params'];
            $where = '';
            foreach ($object_where_params as $object_where_key => $object_where_value) {
                if ($object_where_value != '') {
                    $where = $object_where_key . ' = "' . $object_where_value . '" AND ';
                }
            }
            if (!empty($where)) {
                $where = rtrim($where, "AND ");
                $this->db->where($where);
            }
            $no_of_records = (int)$limit - (int)$start;

            if ($this->accountinfo['type'] == 1) {
                $categoryinfo = $this->db_model->getSelect("GROUP_CONCAT('''',id,'''') as id", "category", "code NOT IN ('REFILL','DID')");
                if ($categoryinfo->num_rows > 0) {
                    $categoryinfo = $categoryinfo->result_array()[0]['id'];
                }
                if (astpp_gt($this->accountinfo["reseller_id"], 0)) {

                    $this->db->where("product_id NOT IN (select CONCAT(product_id) from reseller_products where is_owner = 1 and is_optin = 0 and account_id = " . $this->accountinfo['id'] . " )");
                    $this->db->where("product_category IN (" . $categoryinfo . ")", NULL, false);

                    $optin_list = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.country_id,reseller_products.status as reseller_status,reseller_products.buy_cost,reseller_products.reseller_id,products.commission,reseller_products.setup_fee,reseller_products.price as
buycost,reseller_products.price,reseller_products.billing_type,(CASE WHEN reseller_products.billing_type = 2 THEN "Monthly" ELSE reseller_products.billing_days END) as billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('products.status' => 0, 'products.is_deleted' => 0, 'reseller_products.status' => 0, 'products.can_resell' => 0, 'products.can_purchase' => 0, 'reseller_products.account_id' => $this->accountinfo['reseller_id']), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', '', '');

                } else {
                    $this->db->where("product_category IN (" . $categoryinfo . ")", NULL, false);
                    $this->db->where("id NOT IN (select product_id from reseller_products where is_optin = 0 and account_id = " . $this->accountinfo["id"] . " )");
                    $optin_list = $this->db_model->select("*,price as buycst,(CASE WHEN billing_type = 2 THEN 'Monthly' ELSE billing_days END) as billing_days", "products", array("status" => 0, "reseller_id" => 0, "can_resell" => 0, "can_purchase" => 0, 'products.is_deleted' => 0), "id", "ASC", '', '', "");
                }
            }
            $optin_lists = $optin_list->result_array();
            $count = $optin_list->num_rows();

            if (empty($optin_lists)) {
                $this->response(array(
                    'total_count' => 0,
                    'data' => $optin_lists,
                    'error' => $this->lang->line('no_records_found')
                ), 200);
            } else {
                foreach ($optin_lists as $key => $value) {
                    if ($this->accountinfo['type'] == 1) {
                        unset($value['status']);
                        unset($value['commission']);
                    }
                    $optin_lists[$key] = $value;
                    if ($this->accountinfo['type'] != 1) {
                        $optin_lists[$key]['reseller_id'] = $this->common->reseller_select_value('first_name,last_name,number,company_name', 'accounts', $value['reseller_id']);
                        unset($optin_lists[$key]['last_modified_date']);

                    }

                    $optin_lists[$key]['country_id'] = $this->common->get_field_name_country_camel("country", "countrycode", $value['country_id']);
                    $optin_lists[$key]['product_category'] = $this->common->get_field_name("name", "category", $value['product_category']);
                    $optin_lists[$key]['buy_cost'] = $this->common_model->to_calculate_currency($value['buy_cost'], '', $currency_id);
                    $optin_lists[$key]['setup_fee'] = $this->common_model->to_calculate_currency($value['setup_fee'], '', $currency_id);
                    $optin_lists[$key]['price'] = $this->common_model->to_calculate_currency($value['price'], '', $currency_id);
                    $optin_lists[$key]['billing_type'] = $this->common->get_renewal_type_category_list('billing_type', 'billing_type', $value['billing_type']);
                }
                $this->response(array(
                    'total_count' => $count,
                    'data' => $optin_lists,
                    'success' => $this->lang->line("product_list_information")

                ), 200);
            }
        }
    }

    function product_delete()
    {
        if (!isset ($this->postdata ['product_id']) || $this->postdata['product_id'] == '') {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('error_param_missing') . "integer:product_id"
            ), 400);
        }
        if(!$this->form_validation->numeric_with_comma($this->postdata['product_id'])){
			$this->response ( array (
				'status' => false,
				'success' =>  $this->lang->line ('valid_product_id')  
			), 400 );
		}
        $product_id = $this->postdata['product_id'];
        unset ($this->postdata ['action']);
       
        $product_info = (array)$this->db_model->getSelect("*", "products", $where)->row_array();

        if (!empty($product_info)) {

            if ($product_id != '') {
                $where = $this->db->where("id IN (" . $this->postdata['product_id'] . ") ");

                $product_id = astpp_gt($this->accountinfo ['reseller_id'], 0) ? $this->accountinfo ['reseller_id'] : 0;
                $accountid = $this->accountinfo['type'] == 1 ? $this->accountinfo['id'] : 0;
                if ($this->accountinfo['type'] == 1 || $this->accountinfo['type'] == 5) {
                    $where = $this->db->where("product_id IN (" . $this->postdata['product_id'] . ")");
                    $product_info = ( array )$this->db->get_where("reseller_products", $where)->result_array();
                    foreach ($product_info as $key => $value) {
                        if (astpp_eq0($value['is_owner'])) {
                            $this->db->where("id", $value['product_id']);
                            $this->db->update("products", array("is_deleted" => 1));
                            $affected_rows = $this->db->affected_rows(); 
                            $this->db->where("id", $value['id']);
                            $this->db->update("reseller_products", array("is_optin" => 1));
                        } else {
                            $this->db->update("reseller_products", array("is_optin" => 1));
                        }
                    }

                } else {
                    $product_info = ( array )$this->db->get_where("products", $where)->result_array();
                    foreach ($product_info as $key => $value) {
                            $this->db->where("id", $value['id']);
                            if ($this->accountinfo['type'] != 2) {
                                $this->db->where("created_by", $this->accountinfo['id']);
                            }
                            $this->db->update("products", array("is_deleted" => 1));
                            $affected_rows = $this->db->affected_rows(); 
                            $this->db->where("product_id", $value['id']);
                            $this->db->update("reseller_products", array("is_optin" => 1, "modified_date" => gmdate("Y-m-d H:i:s")));
                    }
                }
            }
            if(astpp_eq0($affected_rows)){
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('product_not_found')
                ), 400);
            }
            $this->response(array(
                'status' => true,
                'success' => $this->lang->line('product_delete')
            ), 200);
        } else {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('product_not_found')
            ), 400);
        }
    }
    function product_package($postdata){
        $this->postdata = $postdata;
        if (!$this->form_validation->required($this->postdata['country_id'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('require_country_id')
            ), 400);
        }else{
            $country_id =  $this->common->get_field_name('id','countrycode',array('id' => $this->postdata['country_id']));
            if(empty($country_id) && $country_id == ""){
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('invalid_country')
                ), 400);
            }
        }
        if ($this->postdata['product_buy_cost'] != "") {
            if (!$this->form_validation->numeric($this->postdata['product_buy_cost'])) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('numeric_buy_cost')
                ), 400);
            }
            if (!$this->form_validation->max_length($this->postdata['product_buy_cost'], 15)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('max_buy_cost')
                ), 400);
            }
            if (!$this->form_validation->greater_than($this->postdata['product_buy_cost'], -1)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('min_buy_cost')
                ), 400);
            }
        }
        
        $this->postdata['can_purchase'] = $this->postdata['can_purchase'] == "1" ? $this->postdata['can_purchase'] : 0;
        $this->postdata['can_resell'] = $this->postdata['can_resell'] == "1" ? $this->postdata['can_resell'] : 0;
        if ($this->postdata['commission'] != '' && !$this->form_validation->numeric($this->postdata['commission'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_commision')
            ), 400);
        }
        if ($this->postdata['setup_fee'] != '' && !$this->form_validation->numeric($this->postdata['setup_fee'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_setup_fee')
            ), 400);
        }
        $this->postdata['billing_type'] = $this->postdata['billing_type'] == "1" ? $this->postdata['billing_type'] : 0;
        if(!$this->form_validation->required($this->postdata['billing_days'])){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('billing_days_required')
            ), 400);
        }
        if (!$this->form_validation->numeric($this->postdata['billing_days'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_billing_days')
            ), 400);
        }
        if ($this->postdata['billing_days'] != "" && !$this->form_validation->max_length($this->postdata['billing_days'], 3)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_billing_days')
            ), 400);
        }
        if ($this->postdata['billing_days'] != "" && !$this->form_validation->greater_than($this->postdata['billing_days'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_billing_days')
            ), 400);
        }
        if ($this->postdata['billing_days'] != "" && !$this->form_validation->integer($this->postdata['billing_days'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_billing_days')
            ), 400);
        }
        $this->postdata['apply_on_existing_account'] = $this->postdata['apply_on_existing_account'] == "0" ? $this->postdata['apply_on_existing_account'] : 1;
        if(!$this->form_validation->required($this->postdata['free_minutes'])){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('free_minutes_required')
            ), 400);
        }
        if (!$this->form_validation->numeric($this->postdata['free_minutes'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_charge_type')
            ), 400);
        }
        $this->postdata['release_no_balance'] = $this->postdata['release_no_balance'] == "0" ? $this->postdata['release_no_balance'] : 1;
        $this->postdata['status'] = $this->postdata['status'] == "1" ? $this->postdata['status'] : 0;
        $this->postdata['applicable_for'] = ($this->postdata['applicable_for'] == "1" || $this->postdata['applicable_for'] == "0") ? $this->postdata['applicable_for'] : "1"; // PHP8: nested ternary
        if($this->postdata['apply_on_rategroups'] != ""){
            $explode_ids= explode(',',$this->postdata['apply_on_rategroups']);
            $this->db->where_in('id',$explode_ids);
            $available_rategroups = $this->db_model->getSelect("*", "pricelists", array("status" => 0, "reseller_id" => 0))->result_array();
            unset($this->postdata['apply_on_rategroups']);
            foreach ($available_rategroups as $key => $value) {
                if(astpp_in_array($value['id'], $explode_ids)!=FALSE){
                    $this->postdata['apply_on_rategroups'] .= $value['id'].',';
                }
            }
            $this->postdata['apply_on_rategroups'] =  rtrim($this->postdata['apply_on_rategroups'],',');
        }
        if ($this->postdata['commission'] != "") {
            if (!$this->form_validation->numeric($this->postdata['commission'])) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('numeric_commision')
                ), 400);
            }
            if (!$this->form_validation->max_length($this->postdata['commission'], 15)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('max_commision')
                ), 400);
            }
            if (!$this->form_validation->greater_than($this->postdata['commission'], -1)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('min_commision')
                ), 400);
            }
        }
        $add_array['commission'] = isset($this->postdata['commission']) ? $this->postdata['commission'] : 0;
        $insert_array = array(
            'name' => $this->postdata['product_name'],
            'country_id' => $this->postdata['country_id'],
            'description' => $this->postdata['product_description'],
            'buy_cost' => $this->postdata['product_buy_cost'],
            'product_category' => $this->postdata['product_category'],
            'price' => $this->postdata['price'],
            'setup_fee' => $this->postdata['setup_fee'],
            'can_resell' => $this->postdata['can_resell'],
            'commission' => $this->postdata['commission'],
            'billing_type' => $this->postdata['billing_type'],
            'billing_days' => $this->postdata['billing_days'],
            'free_minutes' => $this->postdata['free_minutes'],
            'applicable_for' => $this->postdata['applicable_for'],
            'apply_on_existing_account' => $this->postdata['apply_on_existing_account'],
            'apply_on_rategroups' => $this->postdata['apply_on_rategroups'],
            'release_no_balance' => $this->postdata['release_no_balance'],
            'can_purchase' => $this->postdata['can_purchase'],
            'status' => $this->postdata['status'],
            'is_deleted' => 0,
            'reseller_id' => $this->accountinfo['reseller_id'],
            'created_by' => 1,
            'creation_date' => gmdate("Y-m-d H:i:s"),
            'last_modified_date' => gmdate("Y-m-d H:i:s"),
        );
        return $insert_array;
    }
    function product_refill($postdata){
        $this->postdata = $postdata;
        $insert_array = array(
            'name' => $this->postdata['product_name'],
            'country_id' => 0,
            'description' => $this->postdata['product_description'],
            'buy_cost' => 0,
            'product_category' => $this->postdata['product_category'],
            'price' => $this->postdata['price'],
            'setup_fee' => 0,
            'can_resell' => 0,
            'commission' => 0,
            'billing_type' => 0,
            'billing_days' => 0,
            'free_minutes' => 0,
            'applicable_for' => 0,
            'apply_on_existing_account' => 0,
            'release_no_balance' => 0,
            'can_purchase' => 0,
            'status' => $this->postdata['status'],
            'is_deleted' => 0,
            'created_by' => 1,
            'reseller_id' => $this->accountinfo['reseller_id'],
            'creation_date' => gmdate("Y-m-d H:i:s"),
            'last_modified_date' => gmdate("Y-m-d H:i:s"),
        );
        return $insert_array;
    }
    function product_validation($postdata){
        $this->postdata = $postdata;
        if (!$this->form_validation->required($this->postdata['product_name'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('name_required')
            ), 400);
        }
        $this->postdata['status'] = $this->postdata['status'] == "1" ? $this->postdata['status'] : 0;
        if(!$this->form_validation->required($this->postdata['price'])){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_price')
            ), 400);
        }
        if (!$this->form_validation->numeric($this->postdata['price'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_valid_price')
            ), 400);
        }   
    }
    function product_did($postdata){
        $this->postdata = $postdata;
        if (!$this->form_validation->numeric($this->postdata['product_name'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_product_name')
            ), 400);
        }   
        $did_id = $this->common->get_field_name('id','dids',array('number'=> $postdata['product_name']));
        if($did_id != ""){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('unique_did_number')
            ), 400);
        } 
        if ($this->postdata['product_buy_cost'] != "") {
            if (!$this->form_validation->numeric($this->postdata['product_buy_cost'])) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('numeric_buy_cost')
                ), 400);
            }
            if (!$this->form_validation->max_length($this->postdata['product_buy_cost'], 15)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('max_buy_cost')
                ), 400);
            }
            if (!$this->form_validation->greater_than($this->postdata['product_buy_cost'], -1)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('min_buy_cost')
                ), 400);
            }
        }
        if ($this->postdata['setup_fee'] != "") {
            if (!$this->form_validation->numeric($this->postdata['setup_fee'])) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('numeric_setup_fee')
                ), 400);
            }
            if (!$this->form_validation->max_length($this->postdata['setup_fee'], 15)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('max_setup_fee')
                ), 400);
            }
            if (!$this->form_validation->greater_than($this->postdata['setup_fee'], -1)) {
                $this->response(array(
                    'status' => false,
                    'error' => $this->lang->line('min_setup_fee')
                ), 400);
            }
        }
        $this->postdata['status'] = $this->postdata['status'] == 1 ? $this->postdata['status'] : 0;
        $this->postdata['billing_type'] = $this->postdata['billing_type'] == 1 ? $this->postdata['billing_type'] : 0;
        if (!$this->form_validation->required($this->postdata['billing_days'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('billing_days_required')
            ), 400);
        }
        if (!$this->form_validation->numeric($this->postdata['billing_days'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_billing_days')
            ), 400);
        }
        if ($this->postdata['billing_days'] != "" && !$this->form_validation->max_length($this->postdata['billing_days'], 3)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('max_billing_days')
            ), 400);
        }
        if ($this->postdata['billing_days'] != "" && !$this->form_validation->greater_than($this->postdata['billing_days'], -1)) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('min_billing_days')
            ), 400);
        }
        if ($this->postdata['billing_days'] != "" && !$this->form_validation->integer($this->postdata['billing_days'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('integer_billing_days')
            ), 400);
        }
        if ($this->postdata['connectcost'] != ""  && !$this->form_validation->numeric($this->postdata['connectcost'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_correct_correctcost')
            ), 400);
        }
        if ($this->postdata['cost_min'] != ""  && !$this->form_validation->numeric($this->postdata['cost_min'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_correct_cost_min')
            ), 400);
        }
        if ($this->postdata['includedseconds'] != ""  && !$this->form_validation->numeric($this->postdata['includedseconds'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_correct_includedseconds')
            ), 400);
        }
        if ($this->postdata['init_inc'] != ""  && !$this->form_validation->numeric($this->postdata['init_inc'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('initially_increment_number')
            ), 400);
        }
        if ($this->postdata['inc'] != ""  && !$this->form_validation->numeric($this->postdata['inc'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('inc_number')
            ), 400);
        }
        if ($this->postdata['setup_fee'] != ""  && !$this->form_validation->numeric($this->postdata['setup_fee'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_setup_fee')
            ), 400);
        }
        if ($this->postdata['setup_fee'] != ""  && !$this->form_validation->numeric($this->postdata['setup_fee'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_setup_fee')
            ), 400);
        }
        if ($this->postdata['setup_fee'] != ""  && !$this->form_validation->numeric($this->postdata['setup_fee'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_setup_fee')
            ), 400);
        }
        if ($this->postdata['setup_fee'] != ""  && !$this->form_validation->numeric($this->postdata['setup_fee'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('numeric_setup_fee')
            ), 400);
        }
        if ($this->postdata['leg_timeout'] != ""  && !$this->form_validation->numeric($this->postdata['leg_timeout'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_leg_timeout')
            ), 400);
        }
        if(!$this->form_validation->required($this->postdata['provider_id'])){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('enter_provider_id')
            ), 400);
        }
        $provider_id = $this->common->get_field_name('id','accounts',array('id'=> $this->postdata['provider_id'],'type'=>3,'status'=>0));
        if($provider_id == ""){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('provider_id_not_found')
            ), 400);
        }
        $insert_array = array(
            'name' => $this->postdata['product_name'],
            'country_id' => $this->postdata['country_id'],
            'product_category' => $this->postdata['product_category'],
            'buy_cost' => $this->postdata['product_buy_cost'],
            'price' => $this->postdata['price'],
            'setup_fee' => $this->postdata['setup_fee'],
            'can_resell' => 0,
            'commission' => 0,
            'billing_type' => $this->postdata['billing_type'],
            'billing_days' => $this->postdata['billing_days'],
            'free_minutes' => 0,
            'applicable_for' => 0,
            'apply_on_existing_account' => 0,
            'apply_on_rategroups' => '',
            'destination_rategroups' => '',
            'destination_countries' => '',
            'destination_calltypes' => '',
            'release_no_balance' => 0,
            'can_purchase' => 0,
            'status' => $this->postdata['status'],
            'is_deleted' => 0,
            'created_by' => 1,
            'reseller_id' => $this->accountinfo['reseller_id'],
            'creation_date' => gmdate("Y-m-d H:i:s"),
            'last_modified_date' => gmdate("Y-m-d H:i:s")
        );
        return $insert_array;
    }
    function product_create()
    {
        if (!$this->form_validation->required($this->postdata['product_category'])) {
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('required_product_category')
            ), 400);
        }
        $product_category =  $this->common->get_field_name('id','category',array('id' => $this->postdata['product_category']));
        if(empty($product_category)){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line('invalid_product_category')
            ), 400);
        }
        // Package
        if($this->postdata['product_category'] == 1){
            $this->product_validation($this->postdata);
            $postdata = $this->product_package($this->postdata);
        }
        // Refill
        if($this->postdata['product_category'] == 3){
            $this->product_validation($this->postdata);
            $postdata = $this->product_refill($this->postdata);
        }
        // DID
        if($this->postdata['product_category'] == 4){
            $this->product_validation($this->postdata);
            $postdata = $this->product_did($this->postdata);
        }
        $this->db->insert("products", $postdata);
        $last_id = $this->db->insert_id();
        if($this->postdata['product_category'] == 4){
            $did_array = array(
                'number' => $this->postdata['product_name'],
                'accountid' => 0,
                'parent_id' => 0,
                'connectcost' => $this->postdata['connectcost'],
                'includedseconds' => $this->postdata['includedseconds'],
                'monthlycost' => $this->postdata['monthly_fee'],
                'cost' => $this->postdata['cost_min'],
                'init_inc' => $this->postdata['init_inc'],
                'inc' => $this->postdata['inc'],
                'extensions' => '',
                'status' => $this->postdata['status'],
                'provider_id' => $this->postdata['provider_id'],
                'country_id' => $this->postdata['country_id'],
                'province' => $this->postdata['province'],
                'city' => $this->postdata['city'],
                'setup' => $this->postdata['setup_fee'],
                'maxchannels' => $this->postdata['maxchannels'],
                'call_type' => 0,
                'leg_timeout' => $this->postdata['call_timeout'],
                'product_id' => $last_id,
                'always' =>  0,
                'always_destination' =>  '',
                'user_busy' =>  0,
                'user_busy_destination' => '',
                'user_not_registered' => 0,
                'user_not_registered_destination' => '',
                'no_answer' => 0,
                'no_answer_destination' => '',
                'failover_extensions' => '',
                'failover_call_type' => 1,
                'always_vm_flag' => 1,
                'user_busy_vm_flag' => 1,
                'user_not_registered_vm_flag' => 1,
                'no_answer_vm_flag' => 1,
                'call_type_vm_flag' => 1,
                'last_modified_date' => gmdate("Y-m-d H:i:s")
            );
        }
        if($last_id != ""){
            if($this->postdata['product_category'] == 4){
                $this->db->insert("dids", $did_array);
            }
            $postdata['creation_date'] = $this->common->convert_GMT_to('','',$postdata['creation_date'],$this->accountinfo['timezone_id']);
            $postdata['last_modified_date'] = $this->common->convert_GMT_to('','',$postdata['last_modified_date'],$this->accountinfo['timezone_id']);
            $this->response(array(
                'status' => true,
                'data' => $postdata,
                'success' => $this->lang->line('product_create')
            ), 200);
        }
    }
}
?>
