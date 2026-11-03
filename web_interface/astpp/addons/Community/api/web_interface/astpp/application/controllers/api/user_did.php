<?php

require APPPATH . '/controllers/common/account.php';

#[\AllowDynamicProperties]
class User_did extends Account
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
        if ((!astpp_eq0($this->accountinfo['type']))) {
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
        if($did_result[0] ==  "INSUFFIECIENT_BALANCE"){
            $this->response(array(
                'status' => false,
                'error' => $this->lang->line("insufficient_balance")

            ), 200);
        }
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
                $next_bill_date =astpp_gmdate("Y-m-d H:i:s",strtotime($next_bill_date."+".(1)." months"));
                $next_bill_date =astpp_date ("Y-m-d H:i:s",strtotime($next_bill_date));
            }
            if($today > 28 && $didinfo['billing_type'] == "2"){
                $first_of_month = strtotime(gmdate("Y-m-1 H:i:s"));
                $days_of_month = $this->common->days_in_next_month();
                if($today == 29 && $today <= $days_of_month){
                    $next_bill_date = astpp_gmdate("Y-m-29 H:i:s",strtotime("+".(1)." months",$first_of_month));
                }
                elseif($today == 30 && $today <= $days_of_month){
                    $next_bill_date = astpp_gmdate("Y-m-30 H:i:s",strtotime("+".(1)." months",$first_of_month));
                }
                else{
                    $next_bill_date = astpp_gmdate("Y-m-".$days_of_month ." H:i:s",strtotime("+".(1)." months",$first_of_month));
                }
            }
            
            if(astpp_eq0($didinfo['billing_type'])){
                $next_bill_date = (astpp_eq0($didinfo['billing_days']))?astpp_gmdate('Y-m-d H:i:s', strtotime('+10 years')):astpp_gmdate("Y-m-d H:i:s",strtotime("+".($didinfo['billing_days'])." days"));
            }
            if($didinfo['billing_type'] == 1){
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
}
?>
