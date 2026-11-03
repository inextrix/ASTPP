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
#[\AllowDynamicProperties]
class Product_model extends CI_Model {
	function Plans_model() {
		parent::__construct ();
	}
	function getproduct_list($flag, $start = 0, $limit = 0) { 
		$this->db_model->build_search ( 'product_list_search' );
		if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {
			$account_data = $this->session->userdata ( "accountinfo" );
			$where = array (
					//"reseller_id" => $account_data ['id'] 
			);
		} else {
			$where = array (
					//"reseller_id" => "0" 
			);
		}
		if ($flag) {
			$query = $this->db_model->select ( "*", "products", $where, "id", "ASC", $limit, $start );
		} else {
			$query = $this->db_model->countQuery ( "*", "products", $where );
		}
		return $query;

	}
	function add_product($add_array,$patternSearchArr='') {
		
		unset ( $add_array ["action"] );
		$insert_array = array(
				"name"=>$add_array['product_name'],
				"description"=>$add_array['product_description'],
				"product_category"=>$add_array['product_category'],
				"buy_cost"=>$add_array['product_buy_cost'],
				"price"=>$add_array['cost'],
				"can_purchase"=>$add_array['can_purchase'],
				"status"=>$add_array['status'],	
				"can_resell"=>isset($add_array['can_resell'])?$add_array['can_resell']:"0",
				"commission"=>isset($add_array['commission'])?$add_array['commission']:"0",
				"billing_type"=>isset($add_array['billing_type'])?$add_array['billing_type']:"",
				"billing_days"=>isset($add_array['duration'])?$add_array['duration']:"",
				"free_minutes"=>isset($add_array['free_minutes'])?$add_array['free_minutes']:"",
				"apply_on_rategroups"=>isset($add_array['product_rate_group'])?astpp_implode(",",$add_array['product_rate_group']):"",
				"destination_rategroups"=>isset($patternSearchArr['destination_rategroups'])?astpp_implode(",",$patternSearchArr['destination_rategroups']):"",
				"destination_countries"=>isset($patternSearchArr['destination_countries'])?astpp_implode(",",$patternSearchArr['destination_countries']):"",
				"destination_calltypes"=>isset($patternSearchArr['destination_calltypes'])?astpp_implode(",",$patternSearchArr['destination_calltypes']):"",
				"apply_on_existing_account"=>isset($add_array['apply_on_existing_account'])?$add_array['apply_on_existing_account']:0	,
				"release_no_balance"=>isset($add_array['release_no_balance'])?$add_array['release_no_balance']:"0",
				"created_by"=>$add_array['accountid'],
				"reseller_id"=>isset($add_array['reseller_id'])?$add_array['reseller_id']:0,
				"creation_date"=>gmdate("Y-m-d H:i:s"),
				"last_modified_date"=>''
		);
		$this->db->insert ( "products", $insert_array );
		$last_id = $this->db->insert_id();
		$pattern_array = $this->get_pacakge_pattern($patternSearchArr);
		//$package_pattern_insert_id = $this->insert_pacakge_pattern($last_id,$pattern_array);
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

//ALTER TABLE `package_patterns` ADD UNIQUE( `product_id`, `patterns`);
		$update_fields [] = "patterns=VALUES(patterns),destination=VALUES(destination)";
		$insert_string = "INSERT INTO package_patterns (product_id,patterns,destination) VALUES ";
		$update_string = " ON DUPLICATE KEY UPDATE " . astpp_implode ( ', ', $update_fields );

		$pattern_str = "";
		if($pattern_array->num_rows > 0){
		$pattern_array = $pattern_array->result_array();	

			foreach($pattern_array as $key => $package_pattern){
			$package_pattern_update = array();
			$package_pattern_update = array($product_last_id,$package_pattern['pattern'],$package_pattern['comment']);

				$pattern_str.= "('" . astpp_implode ( "','", $package_pattern_update) . "'),";

			}
			$pattern_str= rtrim ( $pattern_str, "," );
			$this->db->query ( $insert_string . $pattern_str . $update_string, false );
		}
		return true;

	}
	function edit_product($add_array, $id,$editpatternSearchArr='') {
		$upadte_array = array(
				"name"=>$add_array['product_name'],
				"description"=>$add_array['product_description'],
				"product_category"=>$add_array['product_category'],
				"buy_cost"=>$add_array['product_buy_cost'],
				"price"=>$add_array['cost'],
				"can_purchase"=>$add_array['can_purchase'],
				"status"=>$add_array['status'],	
				"can_resell"=>isset($add_array['can_resell'])?$add_array['can_resell']:"0",
				"commission"=>isset($add_array['commission'])?$add_array['commission']:"0",
				"billing_type"=>isset($add_array['billing_type'])?$add_array['billing_type']:"",
				"billing_days"=>isset($add_array['duration'])?$add_array['duration']:"",
				"free_minutes"=>isset($add_array['free_minutes'])?$add_array['free_minutes']:"",
				"apply_on_rategroups"=>isset($add_array['product_rate_group'])?astpp_implode(",",$add_array['product_rate_group']):"",
				"destination_rategroups"=>isset($SearchArr['destination_rategroups'])?astpp_implode(",",$SearchArr['destination_rategroups']):"",
				"destination_countries"=>isset($SearchArr['destination_countries'])?astpp_implode(",",$SearchArr['destination_countries']):"",
				"destination_calltypes"=>isset($SearchArr['destination_calltypes'])?astpp_implode(",",$SearchArr['destination_calltypes']):"",
				"apply_on_existing_account"=>isset($add_array['apply_on_existing_account'])?$add_array['apply_on_existing_account']:0	,
				"release_no_balance"=>isset($add_array['release_no_balance'])?$add_array['release_no_balance']:"0",
				"created_by"=>$add_array['accountid'],
				"reseller_id"=>$add_array['reseller_id'],
				"creation_date"=>'',
				"last_modified_date"=>gmdate("Y-m-d H:i:s")
		);
		$this->db->where ( "id", $id );
		$this->db->update ( "products", $upadte_array );
		$edit_pattern_array = $this->get_pacakge_pattern($editpatternSearchArr);
		$this->insert_pacakge_pattern($add_array['id'],$edit_pattern_array);
		return true;

	}
	
	function remove_product($id) {
		$this->db->where ( "id", $id );
		$this->db->delete ( "products" );
		return true;
	}



}
?>
