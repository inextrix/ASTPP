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
class Product_model extends CI_Model {
	function Plans_model() {
		parent::__construct ();
	}
	function getproduct_list($flag, $start = 0, $limit = 0) {
		$categoryinfo = $this->db_model->getSelect("GROUP_CONCAT('''',id,'''') as id","category","code NOT IN ('REFILL','DID')");
			if($categoryinfo->num_rows > 0 ){ 
				$categoryinfo = $categoryinfo->result_array()[0]['id']; 

			}   
		$accountinfo=$this->session->userdata('accountinfo');
		
		if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {				
			if (isset ( $_GET ['sortname'] ) && $_GET ['sortname'] != 'undefined') {
						$this->db->order_by ( $_GET ['sortname'], ($_GET ['sortorder'] == 'undefined') ? 'desc' : $_GET ['sortorder'] );
			} else {
						$this->db->order_by("products.id","DESC");
			}
			if($accountinfo["reseller_id"] > 0){
				$this->db_model->build_search ( 'product_list_search','reseller_products.' );
				$this->db->where ("product_id NOT IN (select CONCAT(product_id) from reseller_products where is_owner = 1 and is_optin = 0 and account_id = ".$accountinfo['id']." )");
				$this->db->where("product_category IN (".$categoryinfo.")",NULL, false);
			
				if($flag){
					 
					$query = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.country_id,reseller_products.status as reseller_status,reseller_products.buy_cost,reseller_products.reseller_id,products.commission,reseller_products.setup_fee,reseller_products.price as
buycost,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('products.status'=>0,'products.is_deleted'=>0,'reseller_products.status'=>0,'products.can_resell'=>0,'products.can_purchase'=>0,'reseller_products.account_id'=>$accountinfo['reseller_id']), 'reseller_products','products.id=reseller_products.product_id', 'inner', $limit , $start,'','');
				}else{
					
					$query = $this->db_model->getJionQueryCount('products', 'products.id,products.name,products.product_category,products.country_id,reseller_products.status as reseller_status,reseller_products.buy_cost,reseller_products.reseller_id,products.commission,reseller_products.setup_fee,reseller_products.price as buycost,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('products.status'=>0,'products.is_deleted'=>0,'reseller_products.status'=>0,'products.can_resell'=>0,'products.can_purchase'=>0,'reseller_products.account_id'=>$accountinfo['reseller_id']), 'reseller_products','products.id=reseller_products.product_id', 'inner', '' , '','','');
				}

			}else{
			$this->db_model->build_search ( 'product_list_search' );
			if($flag){
				  $this->db->where("product_category IN (".$categoryinfo.")",NULL, false);
				  $this->db->where ("id NOT IN (select product_id from reseller_products where is_optin = 0 and account_id = ".$accountinfo["id"]." )");
				  $query = $this->db_model->select("*,price as buycst","products",array("status"=>0,"reseller_id"=>0,"can_resell"=>0,"can_purchase"=>0,'products.is_deleted'=>0),"id","ASC",$limit,$start,""); 
			}else{
				$this->db->where("product_category IN (".$categoryinfo.")",NULL, false);
				 $this->db->where ("id NOT IN (select product_id from reseller_products where is_optin = 0 and account_id = ".$accountinfo["id"]." )");
				 $query = $this->db_model->countQuery ( "*", "products", array("status"=>0,"reseller_id"=>0,"can_resell"=>0,"can_purchase"=>0,'products.is_deleted'=>0) );
			}
			
		}
		return $query;

	}
}

	function getreseller_products_list($flag, $start = 0, $limit = 0) {  
			if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {
			
			$categoryinfo = $this->db_model->getSelect("GROUP_CONCAT('''',id,'''') as id","category","code NOT IN ('REFILL','DID')");
			if($categoryinfo->num_rows > 0 ){ 
				$categoryinfo = $categoryinfo->result_array()[0]['id']; 
				$this->db->where("product_category IN (".$categoryinfo.")",NULL, false);
			}
			$this->db_model->build_search ( 'product_list_search','reseller_products.' );			
			$accountinfo=$this->session->userdata('accountinfo');
			$temp_where = "(reseller_products.is_optin = 0 OR reseller_products.is_owner=0)";
			$this->db->where($temp_where);
			$tmp_where = "(reseller_products.status = 0 OR reseller_products.status =1)";
			$this->db->where($tmp_where);
			$str_where = "(products.status = 0 OR reseller_products.is_owner=0)";
			$this->db->where($str_where);
			$this->db->where('reseller_products.account_id',$accountinfo['id']);
			
			if($accountinfo['reseller_id'] > 0){
				if ($flag) {
					if (isset ( $_GET ['sortname'] ) && $_GET ['sortname'] != 'undefined') {
						$this->db->order_by ( $_GET ['sortname'], ($_GET ['sortorder'] == 'undefined') ? 'desc' : $_GET ['sortorder'] );
					} else {
						$this->db->order_by("products.id","DESC");
					}
					$query = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.country_id,reseller_products.status as reseller_status,reseller_products.buy_cost,reseller_products.reseller_id,products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('products.is_deleted'=>0), 'reseller_products','products.id=reseller_products.product_id', 'inner', $limit , $start,'','');
				} else {
					$query = $this->db_model->getJionQueryCount('products', 'products.id,products.name,products.product_category,products.country_id,reseller_products.status as reseller_status,reseller_products.buy_cost,reseller_products.reseller_id,products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('products.is_deleted'=>0), 'reseller_products','products.id=reseller_products.product_id', 'inner','', '','','');

				}
			}else{
				if ($flag) {
					if (isset ( $_GET ['sortname'] ) && $_GET ['sortname'] != 'undefined') {
						$this->db->order_by ( $_GET ['sortname'], ($_GET ['sortorder'] == 'undefined') ? 'desc' : $_GET ['sortorder'] );
					} else {
						$this->db->order_by("products.id","DESC");
					}
					$query = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.country_id,reseller_products.status as reseller_status,reseller_products.buy_cost,reseller_products.reseller_id,products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('products.is_deleted'=>0), 'reseller_products','products.id=reseller_products.product_id', 'inner', $limit , $start,'','');
				} else {
					$query = $this->db_model->getJionQueryCount('products', 'products.id,products.name,products.product_category,products.country_id,reseller_products.status as reseller_status,reseller_products.buy_cost,reseller_products.reseller_id,products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array('products.is_deleted'=>0), 'reseller_products','products.id=reseller_products.product_id', 'inner','', '','','');
				}
		       }
	      return $query;
	      }else{
			$this->db_model->build_search ( 'product_list_search');
			$this->db->order_by("id","DESC");
			$where = array ("is_deleted" => "0","product_category <>"=>4);
			if ($flag) {
				$query = $this->db_model->select ( "*", "products", $where, "", "", $limit, $start );
			} else {
				$query = $this->db_model->countQuery ( "*", "products", $where );
			}

		return $query;
	  }

	}
	function add_product($add_array,$patternSearchArr='') {
		$accountinfo = $this->session->userdata ( "accountinfo" );
		unset ( $add_array ["action"] );
		unset ( $add_array ["email_notify"] );
		$insert_array = array(
				"name"=>($add_array['product_category'] == 4 || $add_array['product_category'] == "DID" )?$add_array['number']:$add_array['product_name'],
				"country_id"=>(isset($add_array['country_id']) && $add_array['country_id'] > 0) ? $add_array['country_id'] : "",
				"description"=>isset($add_array['product_description'])?$add_array['product_description']:"",
				"product_category"=>$add_array['product_category'],
				"buy_cost"=>isset($add_array['product_buy_cost'])?$this->common_model->add_calculate_currency ($add_array['product_buy_cost'], "", '', false, false ):"0.00",
				"price"=>$this->common_model->add_calculate_currency ($add_array['price'], "", '', false, false ),
				"setup_fee"=>isset($add_array['setup_fee'])?$this->common_model->add_calculate_currency ($add_array['setup_fee'], "", '', false, false ):"0.00",
				"can_purchase"=>isset($add_array['can_purchase'])?$add_array['can_purchase']:"",
				"status"=>$add_array['status'],	
				"can_resell"=>isset($add_array['can_resell'])?$add_array['can_resell']:"0",
				"commission"=>isset($add_array['commission'])?$add_array['commission']:"0",
				"billing_type"=>isset($add_array['billing_type'])?$add_array['billing_type']:"",
				"billing_days"=>isset($add_array['billing_days'])?$add_array['billing_days']:"",
				"free_minutes"=>isset($add_array['free_minutes'])?$add_array['free_minutes']:"",
				"apply_on_rategroups"=>isset($add_array['product_rate_group'])?implode(",",$add_array['product_rate_group']):"",
				"destination_rategroups"=>isset($patternSearchArr['destination_rategroups'])?implode(",",$patternSearchArr['destination_rategroups']):"",
				"destination_countries"=>isset($patternSearchArr['destination_countries'])?implode(",",$patternSearchArr['destination_countries']):"",
				"destination_calltypes"=>isset($patternSearchArr['destination_calltypes'])?implode(",",$patternSearchArr['destination_calltypes']):"",
				"apply_on_existing_account"=>isset($add_array['apply_on_existing_account'])?$add_array['apply_on_existing_account']:0,
				"applicable_for"=>isset($add_array['applicable_for'])?$add_array['applicable_for']:0,
				"release_no_balance"=>isset($add_array['release_no_balance'])?$add_array['release_no_balance']:"0",
				"created_by"=>$add_array['accountid'],
				"reseller_id"=>isset($add_array['reseller_id'])?$add_array['reseller_id']:0,
				"creation_date"=>gmdate("Y-m-d H:i:s"),
				"last_modified_date"=>gmdate("Y-m-d H:i:s")
		);
		$this->db->insert ( "products", $insert_array );
		$last_id = $this->db->insert_id();
		if($add_array['product_category']==4 || $add_array['product_category']=="DID" ){
				$did_insert_array = array(
					"number"=>$add_array['number'],
					"accountid"=>0,
					"parent_id"=>isset($add_array['parent_id'])?$add_array['parent_id']:"0",
					"connectcost"=>isset($add_array['connectcost'])?$this->common_model->add_calculate_currency ($add_array['connectcost'], "", '', false, false ):"0",
					"includedseconds"=>isset($add_array['includedseconds'])?$add_array['includedseconds']:"0",
					"monthlycost"=>isset($add_array['price'])?$this->common_model->add_calculate_currency ($add_array['price'], "", '', false, false ):"0",
					"cost"=>isset($add_array['cost'])?$this->common_model->add_calculate_currency ($add_array['cost'], "", '', false, false ):"0.00",
					"init_inc"=>isset($add_array['init_inc'])?$add_array['init_inc']:"0",
					"inc"=>isset($add_array['inc'])?$add_array['inc']:"0",
					"extensions"=>isset($add_array['extensions'])?$add_array['extensions']:"",
					"status"=>isset($add_array['status'])?$add_array['status']:"0",
					"provider_id"=>isset($add_array['provider_id'])?$add_array['provider_id']:"0",
					"country_id"=>isset($add_array['country_id'])?$add_array['country_id']:"0",
					"province"=>isset($add_array['province'])?$add_array['province']:"",
					"city"=>isset($add_array['city'])?$add_array['city']:"",
					"setup"=>isset($add_array['setup_fee'])?$this->common_model->add_calculate_currency ($add_array['setup_fee'], "", '', false, false ):"0",
					"maxchannels"=>isset($add_array['maxchannels'])?$add_array['maxchannels']:"0",
					"call_type"=>isset($add_array['call_type'])?$add_array['call_type']:"",
					"leg_timeout"=>isset($add_array['leg_timeout'])?$add_array['leg_timeout']:"30",
					"last_modified_date"=>gmdate("Y-m-d H:i:s"),
					"product_id"=>$last_id
					
				   );
			$this->db->insert ( "dids", $did_insert_array );
			$did_last_id = $this->db->insert_id();
		}

		if($accountinfo['type'] == 1){
			$reseller_products_array = array(
					"product_id"=>$last_id,
					"account_id"=>isset($add_array['reseller_id'])?$add_array['reseller_id']:0,							"buy_cost"=>isset($add_array['product_buy_cost'])?$add_array['product_buy_cost']:"",
					"country_id"=>($add_array['country_id'] > 0) ? $add_array['country_id'] : "",
					"price"=>$this->common_model->add_calculate_currency ($add_array['price'], "", '', false, false ),
					"buy_cost"=>isset($add_array['product_buy_cost'])?$this->common_model->add_calculate_currency ($add_array['product_buy_cost'], "", '', false, false ):"0.00",
					"setup_fee"=>isset($add_array['setup_fee'])?$this->common_model->add_calculate_currency ($add_array['setup_fee'], "", '', false, false ):"0.00",
					"reseller_id"=>isset($accountinfo ['reseller_id'])?$accountinfo ['reseller_id']:0,
					"commission"=>isset($add_array['commission'])?$this->common_model->add_calculate_currency ($add_array['commission'], "", '', false, false ):"0",
					"billing_type"=>isset($add_array['billing_type'])?$add_array['billing_type']:"",
					"billing_days"=>isset($add_array['billing_days'])?$add_array['billing_days']:"",
					"free_minutes"=>isset($add_array['free_minutes'])?$add_array['free_minutes']:"",
					"status"=>$add_array['status'],
					"is_owner"=>0,
					"is_optin"=>1
					);

			$this->db->insert ( "reseller_products", $reseller_products_array );
			$reseller_products_last_id = $this->db->insert_id();

		}	
		$this->session->unset_userdata('product_package_pattern_search');
		return $last_id;
	}

	function get_pacakge_pattern($patternSearchArr){
		$this->db->where_in('pricelist_id',$patternSearchArr['destination_rategroups']);
		$this->db->where_in('country_id',$patternSearchArr['destination_countries']);
		$this->db->where_in('call_type',$patternSearchArr['destination_calltypes']);
		$this->db->select("*");
		$this->db->from("routes");
		$pattern_array = $this->db->get();
		return $pattern_array;
	}
	function insert_pacakge_pattern($product_last_id,$pattern_array){ 

		$update_fields [] = "patterns=VALUES(patterns),destination=VALUES(destination),country_id=VALUES(country_id)";
		$insert_string = "INSERT INTO package_patterns (product_id,country_id,patterns,destination) VALUES ";
		$update_string = " ON DUPLICATE KEY UPDATE " . implode ( ', ', $update_fields );

		$pattern_str = "";
		if($pattern_array->num_rows != 0){
		$pattern_array = $pattern_array->result_array();	

			foreach($pattern_array as $key => $package_pattern){
			$package_pattern_update = array();
			$package_pattern_update = array($product_last_id,$package_pattern['country_id'],$package_pattern['pattern'],$package_pattern['comment']);
				$pattern_str.= "('" . implode ( "','", $package_pattern_update) . "'),";

			}
			$pattern_str= rtrim ( $pattern_str, "," );
			$this->db->query ( $insert_string . $pattern_str . $update_string, false );
		}
		return true;

	}
	function edit_product($add_array, $id,$editpatternSearchArr='') {
		$accountinfo = $this->session->userdata ( "accountinfo" );
		unset($add_array['apply_on_existing_account']);
		unset($add_array['product_rate_group']);
		unset ( $add_array ["email_notify"] );
		$add_array['product_category'] = isset($add_array['product_category'])?$add_array['product_category']:'';
		if($add_array['product_category']=='DID' || $add_array['product_category']== 4){
				$destination_info = $this->db_model->getSelect("call_type,extensions","dids",array("number"=>$add_array['number']));
				$destination_info = $destination_info->result_array()[0];

				$did_update_array = array(
					"number"=>$add_array['number'],
					"accountid"=>$add_array['accountid'],
					"parent_id"=>isset($add_array['parent_id'])?$add_array['parent_id']:"0",
					"connectcost"=>isset($add_array['connectcost'])?$this->common_model->add_calculate_currency ($add_array['connectcost'], "", '', false, false ):"0",
					"includedseconds"=>isset($add_array['includedseconds'])?$add_array['includedseconds']:"0",
					"monthlycost"=>isset($add_array['price'])?$this->common_model->add_calculate_currency ($add_array['price'], "", '', false, false ):"0",
					"cost"=>isset($add_array['cost'])?$this->common_model->add_calculate_currency ($add_array['cost'], "", '', false, false ):"0.00",
					"init_inc"=>isset($add_array['init_inc'])?$add_array['init_inc']:"0",
					"inc"=>isset($add_array['inc'])?$add_array['inc']:"0",
					"extensions"=>isset($destination_info['extensions'])?$destination_info['extensions']:"",
					"status"=>isset($add_array['status'])?$add_array['status']:"0",
					"provider_id"=>isset($add_array['provider_id'])?$add_array['provider_id']:"0",
					"country_id"=>isset($add_array['country_id'])?$add_array['country_id']:"0",
					"province"=>isset($add_array['province'])?$add_array['province']:"",
					"city"=>isset($add_array['city'])?$add_array['city']:"",
					"setup"=>isset($add_array['setup_fee'])?$this->common_model->add_calculate_currency ($add_array['setup_fee'], "", '', false, false ):"0",
					"maxchannels"=>isset($add_array['maxchannels'])?$add_array['maxchannels']:"0",
					"call_type"=>isset($destination_info['call_type'])?$destination_info['call_type']:"",
					"leg_timeout"=>isset($add_array['leg_timeout'])?$add_array['leg_timeout']:"30"
					
					
				   );

		$this->db->where ( "number", $add_array['name'] );
		$this->db->update ( "dids", $did_update_array );
	}

		$update_array = array(
				"name"=>($add_array['product_category'] == "DID" || $add_array['product_category'] == 4)?$add_array['number']:$add_array['product_name'],
				"country_id"=>($add_array['country_id'] > 0) ? $add_array['country_id'] : "",
				"description"=>isset($add_array['product_description'])?$add_array['product_description']:"",
				"buy_cost"=>isset($add_array['product_buy_cost'])?$this->common_model->add_calculate_currency ($add_array['product_buy_cost'], "", '', false, false ):"0.00",
				"price"=>$this->common_model->add_calculate_currency ($add_array['price'], "", '', false, false ),
				"can_purchase"=>isset($add_array['can_purchase'])?$add_array['can_purchase']:"",
				"status"=>$add_array['status'],	
				"setup_fee"=>isset($add_array['setup_fee'])?$this->common_model->add_calculate_currency ($add_array['setup_fee'], "", '', false, false ):"0",
				"can_resell"=>isset($add_array['can_resell'])?$add_array['can_resell']:"0",
				"commission"=>isset($add_array['commission'])?$add_array['commission']:"0",
				"billing_type"=>isset($add_array['billing_type'])?$add_array['billing_type']:"",
				"billing_days"=>isset($add_array['billing_days'])?$add_array['billing_days']:"",
				"free_minutes"=>isset($add_array['free_minutes'])?$add_array['free_minutes']:"",
				"applicable_for"=>isset($add_array['applicable_for'])?$add_array['applicable_for']:0,
				"destination_rategroups"=>isset($editpatternSearchArr['destination_rategroups'])?implode(",",$editpatternSearchArr['destination_rategroups']):"",
				"destination_countries"=>isset($editpatternSearchArr['destination_countries'])?implode(",",$editpatternSearchArr['destination_countries']):"",
				"destination_calltypes"=>isset($editpatternSearchArr['destination_calltypes'])?implode(",",$editpatternSearchArr['destination_calltypes']):"",
				"release_no_balance"=>isset($add_array['release_no_balance'])?$add_array['release_no_balance']:"0",
				"created_by"=>$add_array['accountid'],
				"creation_date"=>gmdate("Y-m-d H:i:s"),
				"last_modified_date"=>gmdate("Y-m-d H:i:s")
		);

		$this->db->where ( "id", $id );
		$this->db->update ( "products", $update_array );
		if($accountinfo['type'] == 1){

			$reseller_products_array = array(
						"product_id"=>$add_array['id'],
						"account_id"=>isset($add_array['accountid'])?$add_array['accountid']:0,						"buy_cost"=>isset($add_array['product_buy_cost'])?$this->common_model->add_calculate_currency ($add_array['product_buy_cost'], "", '', false, false ):"0.00",
						"country_id"=>($add_array['country_id'] > 0) ? $add_array['country_id'] : "",
						"price"=>$this->common_model->add_calculate_currency ($add_array['price'], "", '', false, false ),
						"setup_fee"=>isset($add_array['setup_fee'])?$this->common_model->add_calculate_currency ($add_array['setup_fee'], "", '', false, false ):"0.00",
						"reseller_id"=>isset($accountinfo ['reseller_id'])?$accountinfo ['reseller_id']:0,
						"commission"=>isset($add_array['commission'])?$this->common_model->add_calculate_currency ($add_array['commission'], "", '', false, false ):"0",
						"billing_type"=>isset($add_array['billing_type'])?$add_array['billing_type']:"",
						"billing_days"=>isset($add_array['billing_days'])?$add_array['billing_days']:"",
						"free_minutes"=>isset($add_array['free_minutes'])?$add_array['free_minutes']:"",
						"status"=>$add_array['status'],
						"is_owner"=>0,
						"is_optin"=>1
					);
			$this->db->where ( "product_id", $add_array['id'] );
			$this->db->where ( "account_id", $add_array['accountid'] );
			$this->db->update ( "reseller_products", $reseller_products_array );
			$reseller_products_last_id = $this->db->insert_id();
		}
		$edit_pattern_array = array();
		if(isset($editpatternSearchArr) && $editpatternSearchArr != '' ){
			$edit_pattern_array = $this->get_pacakge_pattern($editpatternSearchArr);
		}
			if(isset($edit_pattern_array) && $edit_pattern_array !=''){
				$this->insert_pacakge_pattern($add_array['id'],$edit_pattern_array);
			}
		return true;
	}
	function remove_product($id) {
		$this->db->where ( "id", $id );
		$this->db->delete ( "products" );
		return true;
	}
	function products_release($product_info,$accountinfo){
		if($this->session->userdata ['userlevel_logintype'] == '-1'){
			$product_update_array  = array("is_deleted"=>1);
			$order_where = array("is_terminated"=>0,"product_id"=>$product_info['id']);
		}
		$this->db->where(array("id"=>$product_info['id']));
		$this->db->update("products",$product_update_array);
		$order_update_array = array("is_terminated"=>1,"termination_date"=>gmdate('Y-m-d H:i:s'),"termination_note"=> "Product (".$product_info['name'].") has been released by ".$accountinfo['number']."( ".$accountinfo['first_name']." ".$accountinfo['last_name'].") ");
		$this->db->where($order_where);
		$this->db->update("order_items",$order_update_array);
		return true;
	}
	function update_reseller_optin_product($add_array,$productid,$accountinfo){
		if($this->session->userdata ( 'logintype' ) == 1){
		 	if($accountinfo ['reseller_id'] > 0 ){
				$product_info = $this->db_model->getSelect ( "*", " reseller_products", array ('product_id' =>$productid,'reseller_products.account_id'=>$accountinfo['id'],'reseller_products.reseller_id'=>$accountinfo['reseller_id']));
			}else{
				$product_info = $this->db_model->getSelect ( "*", " reseller_products", array ('product_id' => $productid,'reseller_products.account_id'=>$accountinfo['id']));
				}
		}else{
			$product_info = $this->db_model->getSelect ( "*", " products", array ('id' => $productid,'status'=>0));
		}
		// selling price and setup fee only as plain amounts; purchase price from the parent's offer
		// (the reseller's own selling price was stored as buy_cost)
		$price = order::amount(isset($add_array['price']) ? $add_array['price'] : '');
		$setup_fee = order::amount(isset($add_array['setup_fee']) ? $add_array['setup_fee'] : '');
		$offer = $this->order->parent_offer($productid, $accountinfo);
		if ($price === false || $setup_fee === false || $offer === null) {
			return false;
		}
		if($product_info->num_rows > 0)
		 {
		 $product_info = $product_info->result_array()[0];
		 $optin_product_update = array(
					"buy_cost"=>$offer['price'],
					"commission"=>$product_info['commission'],
					"setup_fee"=>$this->common_model->add_calculate_currency ($setup_fee, "", '', false, false ),
					"price"=>$this->common_model->add_calculate_currency ($price, "", '', false, false ),
					"free_minutes"=>$product_info['free_minutes'],
					"billing_type"=>$product_info['billing_type'],
					"billing_days"=>$product_info['billing_days'],
					"status"=>0,
					"is_optin"=>0,
					"is_owner"=>1,
					"modified_date"=>gmdate("Y-m-d H:i:s")
					);
		
			$this->db->where('product_id',$productid);
		        $this->db->where('account_id',$accountinfo['id']);
			$this->db->update("reseller_products",$optin_product_update);
			return true;
	  }

	}
}
?>
