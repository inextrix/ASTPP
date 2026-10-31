<?php
// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
//
// Copyright (C) 2016 iNextrix Technologies Pvt. Ltd.
// Samir Doshi <samir.doshi@inextrix.com>
// ASTPP Version 3.0 and above
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################
class Products extends MX_Controller {
	var $ProductCategory;	
	function __construct() {
		parent::__construct ();
		$this->load->library ( 'session' );	
		$this->load->library ( 'product_form' );
		$this->load->library ( 'astpp/form' );
		$this->load->model ( 'product_model' );
		$this->load->library('form_validation');
		$this->load->library ( 'astpp/order' );
		$this->load->library ( 'did_lib' );
		$this->load->model ( 'Astpp_common' );
		if ($this->session->userdata ( 'user_login' ) == FALSE)
			redirect ( base_url () . '/astpp/login' );
		$accountinfo=$this->session->userdata('accountinfo');
		$paypal_permission =(array)$this->db_model->getSelect ( "paypal_permission", "accounts", array ('id' => $accountinfo ['id']) )->first_row();
		
		if($accountinfo['type'] == 0 && $paypal_permission['paypal_permission'] == 1){
				$this->session->set_flashdata('astpp_danger_alert',gettext("Your TopUp permission has been disabled please contact to your administrator"));
				redirect ( base_url () . 'dashboard' );
		}
		$this->get_product_category();
	}

	function get_product_category(){
		if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5){
			$categoryinfo = $this->db_model->getSelect("GROUP_CONCAT(id) as id","category","code NOT IN ('REFILL','DID','PACKAGE')");
			$categoryinfo_arr = $categoryinfo->result_array()[0]['id']; 
			if($categoryinfo->num_rows > 0 && $categoryinfo_arr['id'] != ''){ 
				$where_arr['where'] =$this->db->where("id IN (".$categoryinfo_arr.")",NULL, false);
				$this->ProductCategory = $this->db_model->build_dropdown("id,name,code", "category", "",$where_arr);
			}
		}else{
			$this->ProductCategory = $this->db_model->build_dropdown("id,name,code", "category", "", "");
		}
	}
	function products_list() { 
		$data['accountinfo']  = $this->session->userdata ( "accountinfo" );
		$data ['search_flag'] = true;
		$data ['page_title']  = gettext ('Products');
		$this->session->set_userdata ( 'product_list_search', 0 );
		unset($_POST['productlist']);
		if ($this->session->userdata ( 'logintype' ) == '-1' || $this->session->userdata ( 'logintype' ) == '2' ){
			$data ['page_title']  = gettext ('Products');	
		}else{
			$data ['page_title']  = gettext ('My Products');
		}
		$data ['grid_fields']  = $this->product_form->build_product_list_for_admin ();
		$data ["grid_buttons"] = $this->product_form->build_grid_buttons ();
		$data ['form_search']  = $this->form->build_serach_form ( $this->product_form->get_product_search_form() );

		$this->load->view ( 'view_product_list', $data );
	}
	function products_list_json() {
		$json_data = array ();
		$count_all = $this->product_model->getreseller_products_list( false );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );

		$json_data = $paging_data ["json_paging"];
		$query = $this->product_model->getreseller_products_list( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields = json_decode ( $this->product_form->build_product_list_for_admin() );
		$json_data ['rows'] = $this->form->build_grid ( $query, $grid_fields );

		echo json_encode ( $json_data );
	}
	function products_listing() {
		$data['accountinfo']  = $this->session->userdata ( "accountinfo" );   
		$data ['page_title']  = gettext ('Parent Products');
		$data ['search_flag'] = true;
		$this->session->set_userdata ( 'product_list_search', 0 );
		$data ['grid_fields']  = $this->product_form->build_product_list_for_admin_products();
		$data ['form_search']  = $this->form->build_serach_form ( $this->product_form->get_product_listing_search_form() );
		$this->load->view ( 'view_productlisting', $data );
	}

	function products_listing_json() {
		$json_data = array ();
		$count_res = $this->product_model->getproduct_list( false );
		$paging_data = $this->form->load_grid_config ( $count_res, $_GET ['rp'], $_GET ['page'] );
		$json_data = $paging_data ["json_paging"];
		$query = $this->product_model->getproduct_list( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields = json_decode ( $this->product_form->build_product_list_for_admin_products() );
		$json_data ['rows'] = $this->form->build_grid ( $query, $grid_fields );
		echo json_encode ( $json_data );
	}
	function products_add($category="") {
		$data ['page_title'] = gettext ( 'Create Product' );
		$data['product_category'] = $this->ProductCategory;
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		$data['currency'] = $this->common->get_field_name("currency","currency",array("id"=>$accountinfo['currency_id'])); 
		$where_arr= array("reseller_id"=>$reseller_id,"status"=>0);
		$data['product_rate_group'] = $this->db_model->build_dropdown("id,name", "pricelists","where_arr", $where_arr);
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		if(isset($_POST['product_category']) && $_POST['product_category'] != ''){
			$data['product_name'] = isset($_POST['product_name'])?$_POST['product_name']:'';
			$category =$data['product_category'][$_POST['product_category']];
			$data['add_array'] = $_POST;
			$this->load->view ( 'view_product_add_'.strtolower($category), $data);
		}else{
			if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5){
				$this->load->view ( 'view_product_add_subscription', $data);
			}else{
				$this->load->view ( 'view_product_add_package', $data);
				
			}
		}
	
	}
	function products_edit($edit_id = '') {  
		$data ['page_title'] = gettext ( 'Edit Product' );
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		$data['product_category'] = $this->ProductCategory;
		$data ['grid_fields'] = $this->product_form->build_block_pattern_list_for_customer($edit_id);
		$where_arr = array("reseller_id"=>$reseller_id,"status"=>0);
		$data['product_rate_group'] = $this->db_model->build_dropdown("id,name", "pricelists", "where_arr", $where_arr);
		$data ['grid_field'] = $this->product_form->build_pattern_list_for_customer( $edit_id );
		$data['destination_rategroups'] = $this->db_model->build_dropdown("id,name", "pricelists", "where_arr",  $where_arr);
		$data['currency'] = $this->common->get_field_name("currency","currency",array("id"=>$accountinfo['currency_id']));
		
		$add_array = $this->db_model->getSelect ( "*", " products", array ('id' => $edit_id));
		if ($add_array->num_rows > 0) {
			$product_info = ( array ) $add_array->first_row ();
			if($product_info['product_category'] == 4){
				$did_info = $this->db_model->getSelect ( "*", " dids", array ('number' => $product_info['name']));
				if($did_info->num_rows > 0){
					$did_info = ( array ) $did_info->first_row ();	
					$product_info = array_merge($product_info,$did_info);
				}
			}else{
				$product_info = ( array ) $add_array->first_row ();
			}
			$data['product_info']=$product_info;
			$data['edit_id']=$edit_id;
			$data['country_id']=$product_info['country_id'];
			$category = $this->common->get_field_name("name","category",array("id"=>$product_info['product_category']));

			if($accountinfo ['type'] == 1){
			 	if($accountinfo ['reseller_id'] > 0 ){
					$optin_product = $this->db_model->getSelect ( "*", " reseller_products", array ('product_id'=>$edit_id,'reseller_products.account_id'=>$accountinfo['id'],'reseller_products.reseller_id'=>$accountinfo['reseller_id']));
				}else{
					$optin_product = $this->db_model->getSelect ( "*", " reseller_products", array ('product_id' => $edit_id,'reseller_products.account_id'=>$accountinfo['id']));
				}
				if($optin_product->num_rows > 0){
					$data['optin_product'] = ( array ) $optin_product->first_row ();
					if($data['optin_product']['is_optin'] == 0){ 
						$this->products_reseller_edit($data['product_info'],$data['optin_product'],$category);
				}else{
					$data['accountinfo']=$accountinfo;
					$this->load->view ( 'view_product_edit_'.strtolower($category), $data);

				}
			 }
			}else{

				$data['accountinfo']=$accountinfo;
				$this->load->view ( 'view_product_edit_'.strtolower($category), $data);
			}
			$this->session->unset_userdata ( 'optin_product');	
		} else {
			redirect ( base_url () . 'product/product_list/' );
		}
	}

	function products_reseller_edit($product_info,$optin_product,$category){  
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$data ['page_title'] = gettext ( 'Edit Product' );
		$data['currency'] = $this->common->get_field_name("currency","currency",array("id"=>$accountinfo['currency_id']));
		$data['optin_product'] = $optin_product;
		$data['product_info'] = $product_info;
		$data['accountinfo'] = $accountinfo;
		$this->load->view ( 'view_reseller_optin_edit', $data);
	
	}
	function products_reseller_optin_save(){  
		$add_array = $this->input->post();
		$productid= $this->input->post('product_id');
		$accountinfo = $this->session->userdata ( "accountinfo" );
			if(!empty($add_array ) && $add_array !='' ){
				$product_category = $this->common->get_field_name("product_category","products",array("id"=>$productid));
				// false for an invalid price or setup fee
				if ($this->product_model->update_reseller_optin_product($add_array,$productid,$accountinfo ) === false) {
					$this->session->set_flashdata ( 'astpp_notification', gettext('Invalid price or setup fee!') );
				} else {
					$this->session->set_flashdata ( 'astpp_errormsg', gettext('Product updated successfully!') );
				}
				if($product_category == 4){
					redirect ( base_url () . 'did/did_list/' );
				}else{
					redirect ( base_url () . 'products/products_list/' );
				}	
	                }
	}
	
	function products_save() {
		$add_array = $this->input->post ();
		$data['product_category'] = $this->ProductCategory;
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		$data['currency'] = $this->common->get_field_name("currency","currency",array("id"=>$accountinfo['currency_id']));
		if(isset($add_array['product_name'])){
			$this->form_validation->set_rules('product_name', 'Name', 'required');
		}

		if(!isset($add_array['id'])){
		  if(isset($add_array['number'])){
				$this->form_validation->set_rules('connectcost', 'Connection Cost', 'greater_than[-1]|xss_clean');
				$this->form_validation->set_rules('includedseconds', 'Grace Time', 'greater_than[-1]|xss_clean');
				$this->form_validation->set_rules('cost', 'Cost/Min', 'greater_than[-1]|xss_clean');
				$this->form_validation->set_rules('init_inc', 'Initial Increment', 'greater_than[-1]|xss_clean');
				$this->form_validation->set_rules('inc', 'Increment', 'greater_than[-1]|xss_clean');
				$this->form_validation->set_rules('leg_timeout', 'Call Timeout (Sec.)', 'greater_than[-1]|xss_clean');
			$did_number = $this->common->get_field_name("number","dids",array("number"=>$add_array['number']));
			if($did_number != ""){
				$is_unique =  '|is_unique[dids.number]';
			}else{
				$is_unique =  '';
			}
			$this->form_validation->set_rules('number', 'DID', 'required|numeric|trim|xss_clean'.$is_unique);
		  }
		}else{ 
			if($add_array['product_category'] == "DID" || $add_array['product_category'] == 4 ){
				$did_number_info = $this->db_model->getSelect("*","dids",array("product_id"=>$add_array['id']));
				$this->form_validation->set_rules('connectcost', 'Connection Cost', 'greater_than[-1]|min_length[1]|max_length[15]|xss_clean');
				$this->form_validation->set_rules('includedseconds', 'Grace Time', 'greater_than[-1]|min_length[1]|max_length[15]|xss_clean');
				$this->form_validation->set_rules('cost', 'Cost/Min', 'min_length[1]|max_length[10]|greater_than[-1]|xss_clean');
				$this->form_validation->set_rules('init_inc', 'Initial Increment', 'greater_than[-1]|min_length[1]|max_length[15]|xss_clean');
				$this->form_validation->set_rules('inc', 'Increment', 'greater_than[-1]|xss_clean');
				$this->form_validation->set_rules('leg_timeout', 'Call Timeout (Sec.)', 'greater_than[-1]|min_length[1]|max_length[15]|xss_clean');
				if($did_number_info->num_rows > 0 ){
					$did_number_info = $did_number_info->result_array()[0];
					if($did_number_info['number'] != $add_array['number'] ){
						$is_unique =  '|is_unique[dids.number]';
						
					}else{
						$is_unique =  '';
					}
					$this->form_validation->set_rules('number', 'DID', 'required|numeric|trim|xss_clean'.$is_unique);
				}else{
					$this->form_validation->set_rules('number', 'DID', 'required|numeric|trim|xss_clean');
				}
		      }else{
					$this->form_validation->set_rules('product_name', 'Name', 'required|trim|xss_clean');
		      }
		}
		if(isset($add_array['billing_days'])){
			$this->form_validation->set_rules('billing_days', 'Billing Days', 'numeric|required|greater_than[-1]|min_length[0]|max_length[3]|integer');
		}

		if(isset($add_array['setup_fee'])){
			$this->form_validation->set_rules('setup_fee', 'Setup Fee', 'numeric|greater_than[-1]|min_length[1]|max_length[15]|xss_clean');
		}
		if(isset($add_array['buy_cost'])){
			$this->form_validation->set_rules('buy_cost', 'Setup Fee', 'greater_than[-1]|min_length[1]|max_length[15]|xss_clean');
		}
		if(isset($add_array['commission'])){
			$this->form_validation->set_rules('commission', 'Commission', 'numeric|greater_than[-1]|min_length[1]|max_length[15]|xss_clean');
		}
		if(isset($add_array['price'])){
			$this->form_validation->set_rules('price', 'Price', 'numeric|required|greater_than[-1]|min_length[1]|max_length[15]|xss_clean');
		}
		if(isset($add_array['free_minutes'])){
			$this->form_validation->set_rules('free_minutes', 'Free Minutes', 'numeric|required|is_natural|xss_clean');
		}
		$this->form_validation->set_message('max_length', '%s field can not excced  numbers in length %s');
		if(isset($add_array['id']) && $add_array['id'] != ''){ 
			$where_arr['where'] = $this->db->where(array("reseller_id"=>$reseller_id));
			$data['destination_rategroups'] = $this->db_model->build_dropdown("id,name", "pricelists", "", $where_arr);
			$product_info = $this->db_model->getSelect ( "*", "products", array ('id' => $add_array['id']));
			$product_info = ( array ) $product_info->first_row ();
			$did_acc_id = $this->common->get_field_name("accountid","dids",array("product_id"=>$add_array['id']));
			$category = $this->common->get_field_name("name","category",array("id"=>$product_info['product_category']));

			  if ($this->form_validation->run() == FALSE){ 	
				$data ['page_title'] = gettext ( 'Edit Product' );
				$data['product_info'] = $add_array ;
				$data['product_info']['description'] =  isset($add_array['product_description'])?$add_array['product_description']:['description'];
				$data['product_info']['buy_cost'] = isset($add_array['product_buy_cost'])?$add_array['product_buy_cost']:$product_info['buy_cost'];

				// the reseller filter was already used up by the query above, the list showed
				// the rate groups of all resellers
				$data['product_rate_group'] = $this->db_model->build_dropdown("id,name", "pricelists", "where_arr", array("reseller_id" => $reseller_id));

				$data['product_info']['apply_on_rategroups'] = $product_info['apply_on_rategroups'];
				$data['product_info']['name'] = (isset($add_array['product_name']) && $add_array['product_name'] !='' )?$add_array['product_name']: $product_info['name'];
				$data['product_info']['description'] = (isset($add_array['product_description']) && $add_array['product_description'] !='' )?$add_array['product_description']: $product_info['description'];
				$data['product_info']['product_category'] = $product_info['product_category'];
				$data['product_info']['apply_on_existing_account'] = $product_info['apply_on_existing_account'];
				$data['product_info']['id'] = $product_info['id'];
				$data['product_info']['product_id'] = $product_info['id'];
				$data['accountinfo'] = $accountinfo ;

				$data ['validation_errors'] = validation_errors ();

				$this->load->view ( 'view_product_edit_'.strtolower($category), $data);	
	       	         }else{  
				if(isset($add_array) && !empty($add_array)){
					$account_data = $this->session->userdata ( "accountinfo" );
					if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {
						$add_array['reseller_id'] = $account_data ['reseller_id'];
						
						
					} 
					/*to make product edit in reseller login*/
					$add_array['accountid'] = ($add_array['product_category'] =='DID')?$did_acc_id:$accountinfo ['id'];
					$add_array['parent_id'] = $this->common->get_field_name("parent_id","dids",array("product_id"=>$add_array['id']));
					$Method = strtolower($category)."_product";
					$add_array['name']=$product_info['name'];
					
					$this->$Method($add_array);
					if($add_array['product_category'] == "DID"){
						$this->session->set_flashdata ( 'astpp_errormsg', gettext('DID updated successfully!'));
						redirect ( base_url () . 'did/did_list/' );

					}else{
						$this->session->set_flashdata ( 'astpp_errormsg', gettext('Product updated successfully!'));
						redirect ( base_url () . 'products/products_list/' );
					}
		  }
		}
		}else{
			$category =$data['product_category'][$add_array['product_category']];
		 	$data['add_array'] = $add_array['product_category'];
			$where_arr['where'] = $this->db->where(array("reseller_id"=>$reseller_id));
			$data['product_rate_group'] = $this->db_model->build_dropdown("id,name", "pricelists", "", $where_arr);
		      if ($this->form_validation->run() == FALSE){  
				$data['add_array'] = $add_array;
				
				$data ['page_title'] = gettext ( 'Create Product' );
				$data ['validation_errors'] = validation_errors ();
//echo "<pre>"; print_r($data); exit;
				$this->load->view ( 'view_product_add_'.strtolower($category), $data);	
	       	     }else{  
			if(isset($add_array) && !empty($add_array)){
				$account_data = $this->session->userdata ( "accountinfo" );
				if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {
					$add_array['reseller_id'] = $account_data ['id'];
				} 

				$add_array['accountid'] = $account_data['id'];
				$Method = strtolower($category)."_product";
				$this->$Method($add_array); 

				if($add_array['product_category'] == 4){
					$this->session->set_flashdata ( 'astpp_errormsg', gettext('DID added successfully!'));
					redirect ( base_url () . 'did/did_list/' );
				
				}else{
					$this->session->set_flashdata ( 'astpp_errormsg', gettext('Product added successfully!'));
					redirect ( base_url () . 'products/products_list/' );

				}
			 }
		}	 
	  }	
	}
	function assign_product_to_exiting_account($productinfo,$product_id){
		$productinfo['product_id'] = $product_id;
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		if($productinfo['apply_on_existing_account'] == 0 && $productinfo['release_no_balance'] == 1 && (isset($productinfo['product_rate_group']) && $productinfo['product_rate_group'] > 0 )){      
			$this->db->select("*");
			$this->db->from("accounts");
			$this->db->where(array("status"=>0,"deleted"=>0,"type"=>0,"reseller_id"=>$reseller_id));
			$this->db->where_in("pricelist_id",$productinfo['product_rate_group']);
			$account_info = $this->db->get();
			if($account_info->num_rows > 0){
				$account_info = $account_info->result_array();
				foreach($account_info as $key =>$account)
				{
					$customer_data = $this->db_model->getSelect("*","accounts",array("id"=>$account['id'],"status"=>0,"deleted"=>0,"type"=>0));
					$productinfo['payment_by'] = "Account Balance";
					$last_id = $this->order->confirm_order($productinfo,$account['id'],$accountinfo);
					if($customer_data->num_rows > 0){
						$customer_data = $customer_data->result_array()[0];
						if((isset($productinfo['email_notify']) && $productinfo['email_notify']  == 1) && $last_id > 0){
							$productinfo['product_category'] = ($productinfo['product_category'] == 1) ? "PACKAGE" : (($productinfo['product_category'] == 2)  ? "SUBSCRIPTION" : "DID");
							$productinfo['next_billing_date'] = ($productinfo['billing_days'] == 0)?gmdate('Y-m-d 23:59:59', strtotime('+10 years')):gmdate("Y-m-d 23:59:59",strtotime("+".($productinfo['billing_days']-1)." days"));
							$final_array = array_merge($customer_data,$productinfo);
							if(isset($productinfo['product_category']) && $productinfo['product_category']==2){
								$final_array['quantity']=isset($productinfo['quantity'])?$productinfo['quantity']:1;
							}else{
								$final_array['quantity']=1;
							}
							$final_array['category_name']=$productinfo['product_category'];
							$final_array['price']=($productinfo['setup_fee']+$productinfo['price']);
							$final_array['total_price']=($productinfo['setup_fee']+$productinfo['price'])*($final_array['quantity']);
							$final_array['total_price_amount']=($productinfo['setup_fee']+$productinfo['price']);
						}
					}
				}
				
			}
			return true;
	     }else{ 
	    	 if($productinfo['apply_on_existing_account'] == 0 && $productinfo['release_no_balance'] == 0 && (isset($productinfo['product_rate_group']) && $productinfo['product_rate_group'] > 0 )){
		    $total_amt = $productinfo['price'] + $productinfo['setup_fee'];
		        $this->db->select("*");
			$this->db->from("accounts");
			$this->db->where(array("status"=>0,"deleted"=>0,"type"=>0,"reseller_id"=>$reseller_id));
			$this->db->where_in("pricelist_id",$productinfo['product_rate_group']);
			$account_info = $this->db->get();

			if($account_info->num_rows > 0){
				$account_info = $account_info->result_array();
				foreach($account_info as $key =>$account)
				{

					$customer_data = $this->db_model->getSelect("*","accounts",array("id"=>$account['id'],"status"=>0,"deleted"=>0,"type"=>0));
					if($customer_data->num_rows > 0){
						$customer_data = $customer_data->result_array()[0];		   
					 }

					$account_balance = $account['posttoexternal'] == 1 ? $account ['credit_limit'] - ($account ['balance']) : $account ['balance'];
						if($account_balance >= $total_amt ){
							$productinfo['payment_by'] = "Account Balance";
							$last_id =$this->order->confirm_order($productinfo,$account['id'],$accountinfo);

							if(!empty($customer_data) && isset($productinfo['email_notify'] ) && $productinfo['email_notify'] ==1  && $last_id  > 0 ){
								   $productinfo['product_category'] = ($productinfo['product_category'] == 1) ? "PACKAGE" : (($productinfo['product_category'] == 2)  ? "SUBSCRIPTION" : "DID");
								  $productinfo['next_billing_date'] = ($productinfo['billing_days'] = 0)?gmdate('Y-m-d 23:59:59', strtotime('+10 years')):gmdate("Y-m-d 23:59:59",strtotime("+".($productinfo['billing_days']-1)." days"));
								$final_array = array_merge($customer_data,$productinfo);
								if(isset($productinfo['product_category']) && $productinfo['product_category']==2){
								$final_array['quantity']=isset($productinfo['quantity'])?$productinfo['quantity']:1;
								}else{
									$final_array['quantity']=1;
								}
								$final_array['category_name']=$productinfo['product_category'];
								$final_array['price']=($productinfo['setup_fee']+$productinfo['price']);
								$final_array['total_price']=($productinfo['setup_fee']+$productinfo['price'])*($final_array['quantity']);
								$final_array['total_price_amount']=($productinfo['setup_fee']+$productinfo['price']);
								$this->common->mail_to_users("product_purchase",$final_array);
							}
				
						}
			    } 
				
			}
			
		}
	     }
	
         }
	function package_product($add_array){ 
		$SearchArr = '';
		// if(!empty($this->session->userdata('product_package_pattern_search'))){ 
		// 	$SearchArr = $this->session->userdata('product_package_pattern_search');
		// }
		if(isset($add_array['id']) && $add_array['id']!= ''){
			$this->product_model->edit_product($add_array,$add_array['id'],$SearchArr);
		}else{
			$last_id =$this->product_model->add_product($add_array,$SearchArr);

			if($add_array['apply_on_existing_account'] == 0){
				$this->assign_product_to_exiting_account($add_array,$last_id);
			}
			$this->session->set_flashdata ( 'astpp_errormsg', gettext('Package created successfully!'));
			 redirect ( base_url () . 'products/products_edit/'.$last_id.' ' );
		}
	}
	function did_product($add_array){ 
		if(isset($add_array['id']) && $add_array['id']!= ''){
			$this->product_model->edit_product($add_array,$add_array['id']);
		}else{
			$last_id =$this->product_model->add_product($add_array);
		}
	}
	function refill_product($add_array){
		if(isset($add_array['id']) && $add_array['id']!= ''){
			$this->product_model->edit_product($add_array,$add_array['id']);
		}else{
			$this->product_model->add_product($add_array);
		}
	}
	function products_package_pattern_search() {
		$package_search_data = $this->input->post ();
		$this->session->set_userdata ( 'product_package_pattern_search', $package_search_data );
		exit;
	}
	
	function products_quick_search(){
		$action = $this->input->post ();
		$this->session->set_userdata ( 'left_panel_search_package_pattern', "" );
		if (! empty ( $action ['left_panel_search'] )) {
			$this->session->set_userdata ( 'left_panel_search_package_pattern', $action ['left_panel_search'] );
		}
	}
	function products_patterns_selected_delete() {
		$ids = $this->input->post ( "selected_ids", true );
		$where = "id IN ($ids)";
		unset ( $_POST );
		echo $this->db->delete ( "package_patterns", $where );
	}
	function products_package_pattern($productid){ 
			$accountinfo = $this->session->userdata ( "accountinfo" );
			$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		if(!empty($this->session->userdata('product_package_pattern_search'))){
			$SearchArr = $this->session->userdata('product_package_pattern_search');
			$country = isset($SearchArr['destination_countries'])?$SearchArr['destination_countries']:'';
			$rate_group=isset($SearchArr['destination_rategroups'])?$SearchArr['destination_rategroups']:'';
			$call_type= isset($SearchArr['destination_calltypes'])?$SearchArr['destination_calltypes']:'';
			$code= $SearchArr['code'];
			$destination= $SearchArr['destination'] ;
		
			$where1 = '(pattern NOT IN (select DISTINCT patterns from package_patterns where product_id = "' . $productid . '" and reseller_id = "'.$reseller_id.'" ))';
			$this->db->where ( $where1 );
			if($rate_group !=''){
				$this->db->where_in('pricelist_id',$rate_group);
			}if($country!=''){
				$this->db->where_in('country_id',$country);
			}if($call_type!=''){
				$this->db->where_in('call_type',$call_type);
			}
			if($code != ''){
				$code = "^".$code.".*";
				$this->db->where('pattern',$code);
			}if($destination != ''){
				$this->db->where('comment',$destination);
			}
		}else{   
			$where1 = '(pattern NOT IN (select DISTINCT patterns from package_patterns where product_id = "' . $productid . '" ))';
			$this->db->where ( $where1 );
		
		}
		$this->db->select("*");
		$this->db->from("routes");
		$subQuery = $this->db->get();
		$count_all= $subQuery->num_rows();
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		if(!empty($this->session->userdata('product_package_pattern_search'))){
			$SearchArr = $this->session->userdata('product_package_pattern_search');
			$country = isset($SearchArr['destination_countries'])?$SearchArr['destination_countries']:'';
			$rate_group=isset($SearchArr['destination_rategroups'])?$SearchArr['destination_rategroups']:'';
			$call_type= isset($SearchArr['destination_calltypes'])?$SearchArr['destination_calltypes']:'';
			$code= $SearchArr['code'];
			$destination= $SearchArr['destination'] ;
		
			$where1 = '(pattern NOT IN (select DISTINCT patterns from package_patterns where product_id = "' . $productid . '" and reseller_id = "'.$reseller_id.'" ))';
			$this->db->where ( $where1 );
			if($rate_group !=''){
				$this->db->where_in('pricelist_id',$rate_group);
			}if($country!=''){
				$this->db->where_in('country_id',$country);
			}if($call_type!=''){
				$this->db->where_in('call_type',$call_type);
			}
			if($code != ''){
				$code = "^".$code.".*";
				$this->db->where('pattern',$code);
			}if($destination != ''){
				$this->db->where('comment',$destination);
			}
		}else{   
			$where1 = '(pattern NOT IN (select DISTINCT patterns from package_patterns where product_id = "' . $productid . '" ))';
			$this->db->where ( $where1 );
		}
		$this->db->limit ( $paging_data ["paging"] ["page_no"], $paging_data ["paging"] ["start"]);
		$this->db->select("*");
		$this->db->from("routes");
		$subQuery = $this->db->get();
		$json_data = $paging_data ["json_paging"];
		$grid_fields = json_decode ( $this->product_form->build_block_pattern_list_for_customer() );
		$json_data ['rows'] = $this->form->build_grid ( $subQuery, $grid_fields );
		echo json_encode ( $json_data );

	}
	function products_patterns_delete($productid,$id) { 
		$this->db->delete ( "package_patterns", array (
				"id" => $id
		) );
		redirect ( base_url () . "products/products_edit/$productid" );
	}
	function products_delete($id) {
		$this->product_model->remove_product ( $id );
		$this->session->set_flashdata ( 'astpp_notification', gettext('Product removed successfully!'));
		redirect ( base_url () . 'products/products_list/' );
	}
	function products_delete_multiple() { 
		
        $ids = $this->input->post("selected_ids", true);
		if($ids != ''){
			$where = "id IN ($ids)";
			$accountinfo = $this->session->userdata ( "accountinfo" );
			$reseller_id = $accountinfo ['reseller_id'] >0 ? $accountinfo ['reseller_id'] : 0;
			$accountid = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
			if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {	
				// get_where() got the DB object as condition, so the rows of all resellers were read,
				// and the opt-out below had no WHERE (reset the opt-in of every reseller).
				// Only the rows of this reseller now.
				$this->db->where("product_id IN (".$ids.")", NULL, FALSE);
				$product_info = ( array ) $this->db->get_where( "reseller_products", array("account_id" => $accountid) )->result_array();
				foreach ($product_info as $key => $value) {
					if ($value['is_owner']==  0 ) {
						
							$this->db->where ("id",$value['product_id']);
							$this->db->update("products",array("is_deleted"=>1));
							$this->db->where ("id",$value['id']);
							$this->db->update("reseller_products",array("is_optin"=>1));
						}
					else{
						$this->db->where ("id",$value['id']);
						$this->db->update("reseller_products",array("is_optin"=>1));
						echo TRUE;
					}
					 			
				}
				
			}else{
				$product_info = ( array ) $this->db->get_where( "products", $where )->result_array();
				foreach($product_info as $key => $value){
				
				$where_arr['product_id'] = $value['id'];
				$where_arr['is_terminated'] = 0;
				$order_item = $this->db_model->getSelect ( "*", "order_items",$where_arr);
				$did_where = array("product_id"=>$value['id']);
					if($order_item->num_rows == 0){
						$this->db->where("id", $value['id']);
				        if($accountinfo ['type'] != 2){
							$this->db->where ("created_by",$accountinfo['id'] );
						}
						$this->db->update("products",array("is_deleted"=>1));
						$this->db->where("product_id", $value['id']);
						$this->db->update("reseller_products",array("is_optin"=>1,"modified_date"=>gmdate("Y-m-d H:i:s")));
					
					}
				}
					echo TRUE; 
			}
		}
	}
	function products_list_search() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			if (isset ( $action ['buy_cost'] ['buy_cost'] ) && $action ['buy_cost'] ['buy_cost'] != '') {
			$action ['buy_cost'] ['buy_cost'] = $this->common_model->add_calculate_currency ( $action ['buy_cost'] ['buy_cost'], "", '', false, false );
			}
			if (isset ( $action ['price'] ['price'] ) && $action ['price'] ['price'] != '') {
			$action ['price'] ['price'] = $this->common_model->add_calculate_currency ( $action ['price'] ['price'], "", '', false, false );
			}
			if (isset ( $action ['setup_fee'] ['setup_fee'] ) && $action ['setup_fee'] ['setup_fee'] != '') {
			$action ['setup_fee'] ['setup_fee'] = $this->common_model->add_calculate_currency ( $action ['setup_fee'] ['setup_fee'], "", '', false, false );
			}
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			$this->session->set_userdata ( 'product_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'products/products_list/' );
		}
	}
	function products_listing_search() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();

			$accountinfo = $this->session->userdata ( "accountinfo" );
			
			if (isset ( $action ['buy_cost'] ['buy_cost'] ) && $action ['buy_cost'] ['buy_cost'] != '') {
				$action ['buy_cost'] ['buy_cost'] = $this->common_model->add_calculate_currency ( $action ['buy_cost'] ['buy_cost'], "", '', false, false );
				}
			
			if (isset ( $action ['price'] ['price'] ) && $action ['price'] ['price'] != '') {
			$action ['price'] ['price'] = $this->common_model->add_calculate_currency ( $action ['price'] ['price'], "", '', false, false );
			}
			if (isset ( $action ['setup_fee'] ['setup_fee'] ) && $action ['setup_fee'] ['setup_fee'] != '') {
			$action ['setup_fee'] ['setup_fee'] = $this->common_model->add_calculate_currency ( $action ['setup_fee'] ['setup_fee'], "", '', false, false );
			}
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			$this->session->set_userdata ( 'product_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'products/products_list/' );
		}
	}
	function products_list_clearsearchfilter() {
		$this->session->set_userdata ( 'advance_search', 0 );
		$this->session->set_userdata ( 'product_list_search', "" );
	}
	function products_pattern_list_json($productid) { 
		$json_data = array ();
		$instant_search = $this->session->userdata ( 'left_panel_search_package_pattern' );
		$like_str = ! empty ( $instant_search ) ? "(patterns like '%$instant_search%'  OR destination like '%$instant_search%' )" : null;

		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
		$where = array (
				'product_id' => $productid 
		);
		$count_all = $this->db_model->countQuery ( "*", "package_patterns", $where );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data = $paging_data ["json_paging"];
		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
		$pattern_data = $this->db_model->select ( "*", "package_patterns", $where, "id", "ASC", $paging_data ["paging"] ["page_no"], $paging_data ["paging"] ["start"] );
		$grid_fields = json_decode ( $this->product_form->build_pattern_list_for_customer ( $productid ) );
		$json_data ['rows'] = $this->form->build_grid ( $pattern_data, $grid_fields );
		echo json_encode ( $json_data );
	}
	function products_patterns_add_info($productid) {
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		if(!empty($this->session->userdata('product_package_pattern_search'))){
			$SearchArr = $this->session->userdata('product_package_pattern_search');
			$where1 = '(pattern NOT IN (select DISTINCT patterns from package_patterns where product_id = "' . $productid . '" and reseller_id = "'.$reseller_id.'"))';
			$this->db->where($where1);
			if(isset($SearchArr['destination_rategroups']) && $SearchArr['destination_rategroups'] != ''){
				$rate_group=$SearchArr['destination_rategroups'];
				$this->db->where_in('pricelist_id',$rate_group);
			}
			if(isset($SearchArr['destination_countries']) && $SearchArr['destination_countries'] != ''){
				$country = $SearchArr['destination_countries'];
				$this->db->where_in('country_id',$country);
			}
			if(isset($SearchArr['destination_calltypes']) && $SearchArr['destination_calltypes'] != ''){
				$call_type= $SearchArr['destination_calltypes'];
				$this->db->where_in('call_type',$call_type);
			}
			if(isset($SearchArr['code']) && $SearchArr['code'] != ''){
				$code= $SearchArr['code'];
				$this->db->where('pattern','^'.$code.'.*');
			}
			if(isset($SearchArr['destination']) && $SearchArr['destination'] != ''){
				$destination= $SearchArr['destination'];
				$this->db->where('comment',$destination);
			}
		}else{
			$where1 = '(pattern NOT IN (select DISTINCT patterns from package_patterns where product_id = "' . $productid . '" and reseller_id = "'.$reseller_id.'"))';
			$this->db->where($where1);
		}
		$this->db->select("*");
		$this->db->from("routes");
		$rates = $this->db->get();
		if($rates->num_rows > 0){
			$result = $this->product_model->insert_pacakge_pattern ($productid, $rates);
			if($result == 1){
				echo 1;
				exit ();
			}
		}else{
			echo 0; 
			exit ();
		}
	}
	function customer_products_list($accountid, $accounttype) { 
		$json_data = array ();
		$select = "products.id as id,order_items.id as id1,products.name,order_items.price,order_items.free_minutes,order_items.setup_fee,order_items.billing_type,order_items.billing_days";
		$table = "products";
		$jionTable = array (
				'order_items',
				'accounts' 
		);
		$jionCondition = array (
				'products.id = order_items.product_id',
				'accounts.id = order_items.accountid' 
		);
		$type = array (
				'left',
				'inner' 
		);
		$categoryinfo = $this->db_model->getSelect("GROUP_CONCAT('''',id,'''') as id","category","code NOT IN ('REFILL','DID')");
		if($categoryinfo->num_rows > 0 ){ 
				$categoryinfo = $categoryinfo->result_array()[0]['id']; 
		}
		$order_type = 'id';
		$order_by = "ASC";
		$instant_search = $this->session->userdata ( 'left_panel_search_' . $accounttype . '_products' );
		$like_str = ! empty ( $instant_search ) ? "(products.name like '%$instant_search%'
                                            OR  products.price like '%$instant_search%'
                                            OR  IF(order_items.billing_type=0, 'One Time', 'Recurring') like '%$instant_search%'
			
					     OR  order_items.billing_days like '%$instant_search%'
                                            OR  products.free_minutes like '%$instant_search%')" : null;

		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
			$this->db->where("order_items.accountid",$accountid );
			$this->db->where("order_items.is_terminated",0);
			$this->db->where("products.product_category IN (".$categoryinfo.")",NULL, false);
			$count_all = $this->db_model->getCountWithJion ( $table, $select, '', $jionTable, $jionCondition, $type );
			$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
			$json_data = $paging_data ["json_paging"];
		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
			$this->db->where("order_items.accountid",$accountid );
			$this->db->where("order_items.is_terminated",0);
			$this->db->where("products.product_category IN (".$categoryinfo.")",NULL, false);
			$account_product_list = $this->db_model->getAllJionQuery ( $table, $select, '', $jionTable, $jionCondition, $type, $paging_data ["paging"] ["page_no"], $paging_data ["paging"] ["start"], $order_by, $order_type, "" );
			$grid_fields = json_decode ( $this->product_form->build_products_list_for_customer ($accountid, $accounttype));
			$json_data ['rows'] = $this->form->build_grid ( $account_product_list, $grid_fields );
			echo json_encode ( $json_data );
	}
	function products_reseller_save(){
		if(!empty($this->input->post())){ 
			$add_array = $this->input->post();
			$accountinfo = $this->session->userdata ( "accountinfo" );
			$account_data = $this->db_model->getSelect("*","accounts",array("id"=>$add_array['accountid']));
			if($account_data->num_rows > 0){
				$data['account_data']= $account_data->result_array()[0];			
				$product_data = $this->db_model->getSelect("*","products",array("id"=>$add_array['product_id']));
				if($product_data->num_rows > 0){
					$data['product_data']=$product_data->result_array()[0];
					$where = array("id"=>$data['product_data']['product_category']);
					$data['category_list'] =  $this->common->get_field_name("name", "category",$where);	
					$this->load->view("view_reseller_orders_assign",$data);
				}
			}
		   }	
	}
	function products_reseller_confirm_order(){ 
		if(!empty($this->input->post())){
			$ProductData = $this->input->post(); 
			$account_id = $this->input->post('account_id');
			$accountinfo = $this->session->userdata ( "accountinfo" );
			// price, period and minutes come from the offer (the form shows them read-only)
			$order_id =$this->order->confirm_order($ProductData,$account_id,$accountinfo);
			if (! ($order_id > 0)) {
				$this->session->set_flashdata ( 'astpp_notification', gettext('Invalid price, quantity or product!'));
				redirect ( base_url () . 'products/products_list/' );
			}
			$this->session->set_flashdata ( 'astpp_errormsg', gettext('Product assigned successfully!'));
			redirect ( base_url () . 'products/products_list/' );
		}else{
			$this->session->set_flashdata ( 'astpp_errormsg', gettext('Somthing went wronge'));
			redirect ( base_url () . 'products/products_list/' );
		}

	}
	function products_did(){  
		$data ['page_title'] = gettext ( 'Create Product' );
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$data['product_category'] = $this->db_model->build_dropdown("id,name,code", "category", "name", "DID");
		$data['currency'] = $this->common->get_field_name("currency","currency",array("id"=>$accountinfo['currency_id']));
		$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		$this->load->view("view_product_add_did",$data);

	}
	function products_edit_reseller_optinproduct($productid=''){ 
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$data ['page_title'] = gettext ( 'Create Product' );
		$data['currency'] = $this->common->get_field_name("currency","currency",array("id"=>$accountinfo['currency_id']));
		if($accountinfo['reseller_id'] > 0){
			$temp_where = '(`reseller_products`.`account_id` = '.$accountinfo['reseller_id'].' AND `reseller_products`.`is_optin` = 0 OR `reseller_products`.`account_id` = '.$accountinfo['reseller_id'].' AND `reseller_products`.`is_owner` = 0)';
			$this->db->where($temp_where);
			$product_info  = $this->db_model->getJionQuery('products',' products.id,products.name,products.product_category,products.buy_cost,products.country_id,products.commission,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.setup_fee,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id',array('reseller_products.product_id'=>$productid), 'reseller_products','products.id=reseller_products.product_id', 'inner', '' ,'','DESC','products.id');
		}else{
			$product_info = $this->db_model->getSelect ( "*", " products", array ('id' => $productid,'status'=>0));

		}
		if ($product_info->num_rows > 0) {
			$product_info = ( array ) $product_info->first_row ();
		}
			$data['product_info']=$product_info;
			$data['accountinfo'] = $accountinfo;
			$this->load->view("view_optin_reseller_product",$data);
	}
	function products_reseller_option_save(){
		$add_array = $this->input->post();
		$productid= $this->input->post('productid');
		$accountinfo = $this->session->userdata ( "accountinfo" );
			if($productid != '' &&  $productid != 0){
				if($accountinfo['reseller_id'] > 0){
					$temp_where = '(`reseller_products`.`account_id` = '.$accountinfo['reseller_id'].' AND `reseller_products`.`is_optin` = 0 OR `reseller_products`.`account_id` = '.$accountinfo['reseller_id'].' AND `reseller_products`.`is_owner` = 0)';
					$this->db->where($temp_where);
					$product_info  = $this->db_model->getJionQuery('products',' products.id,products.name,products.product_category,reseller_products.buy_cost,products.commission,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.setup_fee,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id',array('reseller_products.product_id'=>$productid), 'reseller_products','products.id=reseller_products.product_id', 'inner', '' ,'','DESC','products.id');
				}else{
					$product_info = $this->db_model->getSelect ( "*", " products", array ('id' => $productid,'status'=>0));
				}
				// purchase price from the parent's offer (was the read-only form field
				// product_buy_cost), own selling price and setup fee only as plain amounts
				$offer = $this->order->parent_offer($productid, $accountinfo);
				if($product_info->num_rows > 0 && $offer !== null)
				{
					$product_info = $product_info->result_array()[0];
					$add_array['billing_type'] = $product_info['billing_type'];
					$add_array['billing_days'] = $product_info['billing_days'];
					$add_array['commission'] = $product_info['commission'];
					$add_array['free_minutes'] = $product_info['free_minutes'];
					$add_array['buy_cost'] = $offer['price'];
					if($accountinfo['is_distributor'] == 0){
						$price = isset($add_array['price']) ? order::amount($add_array['price']) : (float) $offer['price'];
						$setup_fee = isset($add_array['setup_fee']) ? order::amount($add_array['setup_fee']) : (float) $offer['setup_fee'];
						if ($price === false || $setup_fee === false) {
							$this->session->set_flashdata ( 'astpp_notification', gettext('Invalid price or setup fee!'));
							redirect ( base_url () . 'products/products_list/' );
						}
						$add_array['price']  = isset($add_array['price'])?$this->common_model->add_calculate_currency ($price, "", '', false, false ):$price;
						$add_array['setup_fee']  = isset($add_array['setup_fee'])?$this->common_model->add_calculate_currency ($setup_fee, "", '', false, false ):$setup_fee;
					}else{
						$add_array['price']  =$offer['price'];
						$add_array['setup_fee']  =  $offer['setup_fee'];
					}
				$query = "INSERT INTO reseller_products (product_id, account_id, reseller_id,country_id,commission, setup_fee, price, free_minutes,buy_cost,billing_type, billing_days, status, is_optin,is_owner,optin_date,modified_date)
	VALUES(" . (int)$productid . ",".(int)$accountinfo['id'].", ".(int)$accountinfo['reseller_id'].", ".(int)$add_array['country_id'].",".(float)$add_array['commission'].",'".(float)$add_array['setup_fee']."','".(float)$add_array['price']."',".(int)$add_array['free_minutes'].",".(float)$add_array['buy_cost'].",".(int)$add_array['billing_type'].",".(int)$add_array['billing_days'].", 0, 0, 1, '".gmdate("Y-m-d H:i:s")."','".gmdate("Y-m-d H:i:s")."') ON DUPLICATE KEY UPDATE product_id = VALUES(product_id), account_id = VALUES(account_id), reseller_id = VALUES(reseller_id), commission = VALUES(commission), setup_fee = VALUES(setup_fee), price = VALUES(price), free_minutes = VALUES(free_minutes), buy_cost = VALUES(buy_cost),billing_type = VALUES(billing_type), billing_days = VALUES(billing_days), status = VALUES(status), is_optin = VALUES(is_optin), is_owner = VALUES(is_owner), optin_date = VALUES(optin_date),modified_date = VALUES(modified_date)";
				$query = $this->db->query($query);
				$this->session->set_flashdata ( 'astpp_errormsg', gettext('Product optin successfully!'));
				redirect ( base_url () . 'products/products_list/' );
				}
			}else{
			redirect ( base_url () . 'products/products_list/' );
			}	
	}
	function products_optin(){
	  if($this->input->post()){
		$product_id = $this->input->post('product_id');
		$status = $this->input->post('status');
		$accountinfo = $this->session->userdata ( "accountinfo" );
			if($status == 'true'){
				$reseller_products_array = array(
							"product_id"=>$product_id,
							"account_id"=>$accountinfo['id'],
							"reseller_id"=>isset($accountinfo ['reseller_id'])?$accountinfo ['reseller_id']:0,
							"status"=>0,
							"creation_date"=>gmdate("Y-m-d H:i:s")
						   );

				$this->db->insert ( "reseller_products", $reseller_products_array );
				$reseller_products_last_id = $this->db->insert_id();
			} 
			if($status == 'false'){
				$orders = $this->db_model->getSelect("*","order_items",array("product_id"=>$product_id));
				// The opt-out deleted/disabled the opt-in of every reseller, not only the own one
				if($orders->num_rows == 0){
					$this->db->where("product_id",$product_id);
					$this->db->where("account_id",$accountinfo['id']);
					$this->db->delete("reseller_products");
				}else{
					$this->db->where("product_id",$product_id);
					$this->db->where("account_id",$accountinfo['id']);
					$this->db->update("reseller_products",array("status"=>1));			
				}

			}
	}
 }
 	function products_topuplist() { 
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$account_arr=(array)$this->db->get_where("accounts",array("id"=>$accountinfo['id'],"deleted"=>"0","status"=>"0"))->first_row();
		if(empty($account_arr)){
			$this->session->sess_destroy ();
			$this->load->helper('cookie');
			set_cookie('post_info',json_encode("text"),'20');
			redirect ( base_url ()."login/");
		}
		if($accountinfo['posttoexternal'] != '1'){ 
			$this->load->module("pages/pages");
			$this->pages->topup_reseller();
		}else{
			if($accountinfo['type'] == '0' || $accountinfo['type'] == '3'){
				$this->session->set_flashdata('astpp_danger_alert',gettext('Permission Denied!'));
				redirect(base_url() . 'user/user/');
			}else{
				$this->session->set_flashdata('astpp_danger_alert',gettext('Permission Denied!'));
				redirect(base_url() . 'dashboard/');
			}
		}
	}
}
?>
