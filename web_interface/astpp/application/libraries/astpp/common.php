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
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
class common {
	protected $CI; 
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
		$this->CI->load->library ( "timezone" );
		$this->CI->load->model ( 'db_model' );
		$this->CI->load->library ( 'email' );
		$this->CI->load->library ( 'session' );
	}
	function generate_password() {

		$length         = (common_model::$global_config ['system_config'] ['pinlength'] < 8) ? 8 : common_model::$global_config ['system_config'] ['pinlength'];
		
		$length=(int)$length;
		$password_type  = common_model::$global_config ['system_config'] ['password_type'];
		$type = !empty($type) ? $type : ($password_type == '0' ? 'hard':'simple');

		if(  $type == 'hard' ) {

			$available_sets = 'luds';
			$sets = array();
			if(strpos($available_sets, 'l') !== false)
				$sets[] = 'abcdefghjkmnpqrstuvwxyz';
			if(strpos($available_sets, 'u') !== false)
				$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
			if(strpos($available_sets, 'd') !== false)
				$sets[] = '23456789';
			if(strpos($available_sets, 's') !== false)
				$sets[] = '!@#$^*()';

			$all = '';
			$password = '';
			foreach($sets as $set)
			{
				$password .= $set[array_rand(str_split($set))];
				$all .= $set;
			}

			$all = str_split($all);
			for($i = 0; $i < $length - count($sets); $i++)
				$password .= $all[array_rand($all)];
				$pass = str_shuffle($password);

		} else {
		
			$alphabet    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
   			$pass        = array(); 
   			$alphaLength = strlen($alphabet) - 1; 

			for ($i = 0; $i < 8; $i++) {
			    $n = rand(0, $alphaLength);
			    $pass[] = $alphabet[$n];
			}
   		 return implode($pass); 
		}
		return $pass;
	}
	function find_uniq_rendno($size = '', $field = '', $tablename = '') {
		if ($tablename != '') {
			$size=(int)$size;
			$accounttype_array = array ();
			$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
			$where = array (
					$field => $uname
			);
			$acc_result = $this->CI->db_model->getSelect ( 'Count(*) as count', $tablename, $where );
			$acc_result = (array)$acc_result->first_row ();
			if ( $acc_result ['count'] != 0 ) {
				$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
				$acc_result = $this->CI->db_model->getSelect ( 'Count(*) as count', $tablename, $where );
			}
		} else {
			$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
		}
		return $uname;
	}
	function find_uniq_rendno_customer($size = '', $field = '', $tablename = '') {

		//ASTPPCOM-1183 Start
		$size=(isset($size) && $size>0)?$size:'10';
		//ASTPPCOM-1183 End

		if ($tablename != '') {
			$accounttype_array = array ();
			$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
			$where = array (
					$field => $uname
			);
			$acc_result = $this->CI->db_model->getSelect ( 'Count(*) as count', $tablename, $where );
			$acc_result = $acc_result->result ();
			while ( $acc_result [0]->count != 0 ) {
				$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
				$acc_result = $this->CI->db_model->getSelect ( 'Count(*) as count', $tablename, $where );
			}
		} else {
			$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
		}
		$start_prifix_value = common_model::$global_config ['system_config'] ['startingdigit'];
		if ($tablename == 'accounts' && $start_prifix_value != 0) {
			$length = strlen ( $start_prifix_value );
			$uname = substr ( $uname, $length );
			$uname = $start_prifix_value . $uname;
		}
		return $uname;
	}
	function find_uniq_rendno_customer_length($min = '',$max = '', $field = '', $tablename = '') {
			
			$this->CI->db->select("count(".$field.") as count");
			$this->CI->db->where($field." >= ",(int)$min);
			$this->CI->db->where($field." <= ",(int)$max);
			$this->CI->db->where("deleted","0");
			$count_result =(array)$this->CI->db->get($tablename)->first_row();
			$difference= (int)$max - ((int)$min-1);
			if(($count_result['count'] >= $difference) || ($min > $max)){
				$uname = $this->find_uniq_rendno_customer(common_model::$global_config ['system_config'] ['cardlength'],$field,$tablename);
			}else{
				$uname = mt_rand ($min,$max);
				$length=strlen($max);
				$uname=str_pad($uname,$length, '0', STR_PAD_LEFT);
				$where = array (
					$field => $uname,
					"deleted"=>0
				);
				$acc_result = $this->CI->db_model->getSelect ( 'Count(*) as count', $tablename, $where );
				$acc_result = (array)$acc_result->first_row ();
				while ( $acc_result['count'] != 0 ) {
					$uname = mt_rand ($min,$max);
					$length=strlen($max);
					$uname=str_pad($uname,$length, '0', STR_PAD_LEFT);
					$where = array (
							$field => $uname,
							"deleted"=>0
					);
					$acc_result = $this->CI->db_model->getSelect ( 'Count(*) as count', $tablename, $where );
					$acc_result = (array)$acc_result->first_row ();
				}
			}
			return $uname;
	}
	function random_string($length) {
		$chars = "1234567890"; 
		$final_rand = '';
		for($i = 0; $i < $length; $i ++) {
			$final_rand .= $chars [rand ( 0, strlen ( $chars ) - 1 )];
		}
		return $final_rand;
	}
	function find_uniq_rendno_accno($length = '', $field = '', $tablename = '', $default, $creation_count) {
		$number = array ();
		$j = 0;

		$total_count = pow ( 10, $length );
		for($i = 1; $i <= $total_count; $i ++) {

			$flag = false;
			$uname = $this->random_string ( $length );
			$uname = strtolower ( $uname );
			if (isset ( $default ))
				$uname = $default . $uname;
			if (! in_array ( $uname, $number )) {
				$where = array (
						$field => $uname
				);
				$acc_result = $this->CI->db_model->getSelect ( 'Count(id) as count', $tablename, $where );
				$acc_result = $acc_result->result_array ();
				if ($acc_result [0] ['count'] == 0 && ! in_array ( $uname, $number )) {
					$number [] = $uname;
					$j ++;
				}
				if ($j == $creation_count) {
					break;
				}
			} else {
				$total_count ++;
			}
		}
		return $number;
	}

	function get_field_count($select, $table, $where) {
		if (is_array ( $where )) {
			$where = $where;
		} else {
			$where = array (
					$select => $where
			);
		}
		$field_name = $this->CI->db_model->countQuery ( $select, $table, $where );
		if (isset ( $field_name ) && ! empty ( $field_name )) {
			return $field_name;
		} else {
			return "0";
		}
	}
	// function get_field_name($select, $table, $where) {
	// 	if (is_array ( $where )) {
	// 		$where = $where;
	// 	} else {
	// 		$where = array (
	// 				"id" => $where
	// 		);
	// 	}
	// 	$field_name = $this->CI->db_model->getSelect ( $select, $table, $where );
	// 	$field_name = $field_name->result ();
	// 	if (isset ( $field_name ) && ! empty ( $field_name )) {
	// 		return $field_name [0]->{$select};
	// 	} else {
	// 		return "";
	// 	}
	// }

	function get_field_name($select, $table, $where) {
		$timezone_name = $where;
		if (is_array ( $where )) {
			$where = $where;
		} else {
			if($select == 'gmtoffset'){
				$where = array("timezone_name"=>$where);
			}else{
				$where = array (
					"id" => $where
				);
				// ASTPPCOM-953 Kinjal Start
				if($select == 'call_type'){
					$where = array (
						"id" => $where['id'],
						"status" => 0
					);
				}
				// ASTPPCOM-953 Kinjal END
			}
		}
		$field_name = $this->CI->db_model->getSelect ( $select, $table, $where );
		$field_name = $field_name->result ();
		if (isset ( $field_name ) && ! empty ( $field_name )) {
			if($select == 'gmtoffset'){
				if($field_name [0]->{$select} >= 0){
					$time = gmdate("H:i", $field_name [0]->{$select});
					$gmthours = "+".$time;
					return $gmthours;
				}else{
					$gmtoffset = str_replace("-","",$field_name [0]->{$select});
					$time = gmdate("H:i", $gmtoffset);
					$gmthours = "-".$time;
					return $gmthours;
				}

			}else{
				return $field_name [0]->{$select};
			}	 
		} else {
			return "";
		}
	}

	function get_field_name_coma_new($select, $table, $where) {

		$value = '';
		if (is_array ( $where )) {
			$where = $where;
		} else {
			$where = explode ( ',', $where );
		}
		$select1 = explode ( ',', $select );
		for($i = 0; $i < count ( $where ); $i ++) {
			$where_in = array (
					"id" => $where [$i]
			);

			$field_name = $this->CI->db_model->getSelect ( $select, $table, $where_in );
			$field_name = $field_name->result ();
			if ($select1[0] == "first_name" && $select1[1] == "last_name" && isset($field_name[0]->company_name ) && $field_name[0]->company_name != '') {
				$value = $field_name [0]->company_name ."(" . $field_name [0]->number.")";
			} else {
				if (isset ( $field_name ) && ! empty ( $field_name )) {
					foreach ( $select1 as $sel ) {
						if ($sel == 'number') {
							$value .= "(" . $field_name [0]->{$sel} . ")";
						} else {
							$value .= $field_name [0]->{$sel} . " ";
						}
					}
				} else {
					$value = "";
				}
			}
		}

		return rtrim ( $value, ',' );
	}
	
	function check_did_avl($select, $table, $where) {
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$flag_status = "";
		$where = array (
				"number" =>$where,
				"status"=>0
		);
		$field_name = $this->CI->db_model->getSelect ( "id,accountid,parent_id,product_id", 'dids', $where );
		$field_name = $field_name->result ();
		if (isset ( $field_name ) && ! empty ( $field_name )) { 
			if (isset ( $field_name [0] ) && $accountinfo ['type'] != 1) { 
				if ($field_name [0]->accountid != 0 && $field_name [0]->parent_id == 0) { 
					if($accountinfo['type'] == 0 || $accountinfo['type'] == 3 ){
					$flag_status = "<a href='../user_list_release/" . $field_name [0]->id . "' title='Release' onClick='return get_reliase_msg();'><span class=' label label-sm label-inverse_blue arrowed_blue-in' title='release'>Release(C)<span></a>";
					}else{
					$flag_status = "<a href='../did_list_release/" . $field_name [0]->id . "' title='Release' onClick='return get_reliase_msg();'><span class=' label label-sm label-inverse_blue arrowed_blue-in' title='release'>Release(C)<span></a>";
					}
				}else if ($field_name [0]->parent_id != 0 && $field_name [0]->accountid != 0 ) {
					if($accountinfo['type'] == 0 || $accountinfo['type'] == 3){
						$flag_status = "<a href='../user_list_release/" . $field_name [0]->id . "' title='Release' onClick='return get_reliase_msg();'><span class=' label label-sm label-inverse_blue arrowed_blue-in' title='release'>Release(C)<span></a>";

					}else{
						$flag_status = "<a href='../did_list_release/" . $field_name [0]->id . "' title='Release' onClick='return get_reliase_msg();'><span class=' label label-sm label-inverse_blue arrowed_blue-in' title='release'>Release(C)<span></a>";
					}
				} else if ($field_name [0]->parent_id != 0) { 
					$flag_status = "<a href='../did_list_release/" . $field_name [0]->id . "' title='Release' onClick='return get_reliase_msg();'><span class=' label label-sm label-inverse_blue arrowed_blue-in' title='release'>Release(R)</span></a>";
				}else {
					$flag_status = "<a href='../did_assgin_reseller/" . $field_name [0]->id . "' title='Assign Number' rel='facebox'><span class=' label label-sm label-inverse_blue arrowed_blue-in' title='release'>Assign Number</span></a>";
				}
			} else {  

				$reseller_id = $accountinfo ['type'] != 1 ? 0 : $accountinfo ['id'];
				$where = array (
						"product_id" => $field_name [0]->product_id
				);
				$field_name_re = $this->CI->db_model->getSelect ( "*", 'dids', $where );
				$field_name_re = $field_name_re->result ();
				if ($field_name_re[0]->accountid !='0' ) {
					$flag_status = "<a href='../did_list_release/" . $field_name [0]->id . "' title='Release' onClick='return get_reliase_msg();'><span class=' label label-sm label-inverse_blue arrowed_blue-in' title='release'>Release(c)</span></a>";
				} else {
					$flag_status = "<a href='../did_assgin_reseller/" . $field_name [0]->id . "' title='Assign Number' rel='facebox'><span class=' label label-sm label-inverse_blue arrowed_blue-in' title='release'>Assign Number</span></a>";
				}
			}
		} else {  
			$flag_status = "<span class='label label-sm label-inverse arrowed-in' title='Not in use'>Inactive </span>";
		}
		return $flag_status;
	}
	function check_did_avl_export($number) {
		$this->CI->db->where ( 'number', $number );
		$this->CI->db->select ( 'id,accountid,parent_id' );
		$status = null;
		$did_info = ( array ) $this->CI->db->get ( 'dids' )->first_row ();
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		if ($did_info ['accountid'] == 0 && $did_info ['parent_id'] == 0) {
			$status = 'Not in use';
		} elseif ($accountinfo ['type'] != 1) {
			if ($did_info ['accountid'] == 0 && $did_info ['parent_id'] > 0) {
				$status = 'Purchase by Reseller';
			}
			if ($did_info ['accountid'] > 0 && $did_info ['parent_id'] == 0) {
				$status = 'Purchase by Customer';
			}
		} else {
			$where_arr = array (
					'note' => $did_info ['number'],
					"parent_id" => $accountinfo ['id']
			);
			$this->db->where ( $where );
			$this->CI->db->select ( 'reseller_id,parent_id' );
			$reseller_pricing = ( array ) $this->db->get ( 'reseller_pricing' )->first_row ();
			if ($reseller_pricing ['reseller_id'] == 0 && $did_info ['accountid'] == 0 && $did_info ['parent_id'] == $accountinfo ['id']) {
				$status = 'Not in use';
			}
			if ($reseller_pricing ['reseller_id'] == 0 && $did_info ['accountid'] == 0) {
				$status = 'Not in use';
			}
		}
		return $status;
	}
	function get_field_name_coma($select, $table, $where) {
		$value = '';
		if (is_array ( $where )) {
			$where = $where;
		} else {
			$where = explode ( ',', $where );
		}
		for($i = 0; $i < count ( $where ); $i ++) {
			$where_in = array (
					"id" => $where [$i]
			);

			$field_name = $this->CI->db_model->getSelect ( $select, $table, $where_in );
			$field_name = $field_name->result ();
			if (isset ( $field_name ) && ! empty ( $field_name )) {

				$value .= $field_name [0]->{$select} . ",";
			} else {
				$value = "";
			}
		}
		return rtrim ( $value, ',' );
	}
	function set_invoice_option($select = "", $table = "", $call_type = "", $edit_value = '') {
		$invoice_date = false;
		$uri_segment = $this->CI->uri->segments;
		if (isset ( $uri_segment [3] ) && $uri_segment [3] > 0 && empty ( $edit_value )) {
			$field_name = $this->CI->db_model->getSelect ( "sweep_id,invoice_day", "accounts", array (
					"id" => $uri_segment [3]
			) );
			$field_name = $field_name->result_array ();
			$select = $field_name [0] ["sweep_id"];
			$invoice_date = $field_name [0] ["invoice_day"];
		} else {
			$invoice_date = $edit_value;
		}
		if ($select == "" || $select == "0") {
			$daily_arr = array (
					"0" => "0"
			);
			return $daily_arr;
		}
		if ($select == 1) {
			$week_arr = array (
					"1" => "Monday",
					"2" => "Tuesday",
					"3" => "Wednesday",
					"4" => "Thursday",
					"5" => "Friday",
					"6" => "Saturday",
					"7" => "Sunday"
			);
			$rawDate = date ( "Y-m-d" );
			$day = date ( 'N', strtotime ( $rawDate ) );
			if (isset ( $uri_segment [3] )) {
				return $week_arr;
			} else {
				$week_drp = form_dropdown ( array (
						"name" => 'invoice_day',
						'style' => "width: 100% !important;",
						"class" => "invoice_day"
				), $week_arr, $day );
				return $week_drp;
			}
		}
		if ($select != 0 && $select != 1) {
			for($i = 1; $i < 29; $i ++) {
				$mon_arr [$i] = $i;
			}
			if (isset ( $uri_segment [3] ) && $uri_segment [3] > 0 && empty ( $edit_value )) {
				return $mon_arr;
			} else {
				$day = $invoice_date > 0 ? $invoice_date : date ( 'd' );
				$month_drp = form_dropdown ( array (
						"name" => 'invoice_day',
						"id" => 'invoice_day',
						"class" => "width_dropdown invoice_day"
				), $mon_arr, $day );
				return $month_drp;
			}
		}
	}
	function set_status($status = '') {
		$status_array = array (
				'0' => gettext ( 'Active' ),
				'1' => gettext ( 'Inactive' )
		);
		return $status_array;
	}
	function set_routetype($status = '') {
		$status_array = array (
				'0' => gettext ( 'LCR' ),
				'1' => gettext ( 'COST' )
		);
		return $status_array;
	}
	function set_prorate($status = '') {
		$status_array = array (
				'0' => gettext ( 'Yes' ),
				'1' => gettext ( 'No' )
		);
		return $status_array;
	}

	function set_prorate_verification($status = '') {
		$status_array = array (
				'0' => gettext ( 'Email' ),
				'1' => gettext ( 'SMS' ),
				'2' => gettext ( 'Both (Email & SMS)' )
		);
		return $status_array;
	}

	function set_localization_verification() {
		$this->CI->db->select ( "id,name" );
		$this->CI->db->where ( "status", 0 );
		$did_localization_result = $this->CI->db->get ( "localization" )->result_array ();
		$did_localization_dropdown = array ();
		$did_localization_dropdown [0] = gettext("--Select--");
		foreach ( $did_localization_result as $result ) {
			$did_localization_dropdown [$result ['id']] = $result ['name'];
		}
		return $did_localization_dropdown;
		
	}
	function set_package_type($applicable_for = "") {
		$package_applicable = array (
				'0' => gettext ( 'Outbound' ),
				'1' => gettext ( 'Inbound' ),
				'2' => gettext ( 'Both' )
		);
		return $package_applicable;
	}
	function get_package_type($status = '', $table = "", $applicable_for) {
		$package_applicable = array (
				'0' => gettext ( 'Outbound' ),
				'1' => gettext ( 'Inbound' ),
				'2' => gettext ( 'Both' )
		);
		return $package_applicable [$applicable_for];
	}
	function set_allow($status = '') {
		$status_array = array (
				'1' => gettext ( 'Yes' ),
				'0' => gettext ( 'No' )
		);
		return $status_array;
	}
	function set_allow_invoice($status = '') {
		$status_array = array (
				'1' => gettext ( 'Yes' ),
				'0' => gettext ( 'No' )
		);
		return $status_array;
	}
	function set_pin_allow($status = '') {
		$status_array = array (
				'0' => gettext ( 'Disable' ),
				'1' => gettext ( 'Enable' )
		);
		return $status_array;
	}
	function set_pin_allow_customer($status = '') {
		$status_array = array (
				'0' => gettext ( 'No' ),
				'1' => gettext ( 'Yes' )
		);
		return $status_array;
	}
	function get_allow($select = "", $table = "", $status) {
		return ($status == 1) ? "Yes" : "No";
	}
	function set_call_type($call_type = "") {
		$call_type_array = array (
				"" => gettext ( "--Select--" )	
		);
		$custom_did_call_types_result = $this->CI->db->get ( "did_call_types" )->result_array ();		
		foreach ( $custom_did_call_types_result as $result ) {
			$call_type_array [$result ['call_type_code']] = $result ['call_type'];
		}
		return $call_type_array;
	}
	 function set_call_type_search() {
		$call_type_array = array (
				"" => gettext ( "--Select--" )
		);
		$custom_did_call_types_result = $this->CI->db->get ( "did_call_types" )->result_array ();	
	
		foreach ( $custom_did_call_types_result as $result ) {
			$call_type_array [$result ['call_type_code']] = gettext($result ['call_type']);
		}
		return $call_type_array;
	}
	function get_call_type($select = "", $table = "", $call_type) {
		$call_type_array = array (
				'' => ""
		);
		$custom_did_call_types_result = $this->CI->db->get ( "did_call_types" )->result_array ();	

		foreach ( $custom_did_call_types_result as $result ) {
			$call_type_array [$result ['call_type_code']] = $result ['call_type'];
		}

		return $call_type_array [$call_type];
	}
	function get_custom_call_type($call_type) {
		$call_type_array = array (
				"" => ""
		);

		$custom_did_call_types_result = $this->CI->db->get ( "did_call_types" )->result_array ();		
		foreach ( $custom_did_call_types_result as $result ) {
			$call_type_array [$result ['call_type']] = $result ['call_type_code'];
		}
		return $call_type_array [$call_type];
	}
	function set_sip_config_option($option = "") {
		$config_option = array (
				"true" => gettext("True"),
				"false" => gettext("False")
		);
		return $config_option;
	}
	function set_option_default_false($option = "") {
                $config_option = array (
                                "false" => gettext("False"),
                                "true" => gettext("True")
                );
                return $config_option;
        }
	function get_entity_type($select = "", $table = "", $entity_type) {
		$entity_array = array (
				'-1' => "Administrator",
				'0' => 'Customer',
				'1' => 'Reseller',
				'2' => 'Admin',
				'3' => "Provider",
				"4" => "Subadmin",
				"5" => "Callshop"
		);
		return ($entity_array [$entity_type]);
	}
	function set_entity_type_customer($entity_type = "") {
		$entity_array = array (
				'' => gettext ( "--Select--" ),
				'0' => gettext ( 'Customer' ),
				'3' => gettext ( "Provider" )
		);
		return $entity_array;
	}
	function set_entity_type_admin($entity_type = "") {
		$entity_array = array (
				'' => gettext ( "--Select--" ),
				'-1' => gettext ( 'Admin' ),
				"2" => gettext ( "Sub Admin" )
		);
		return $entity_array;
	}
	function set_entity_type_email_mass($entity_type = "") {
		$entity_array = array (
				'' => gettext ( "--Select--" ),
				'0' => gettext ( 'Customer' ),
				'1' => gettext ( 'Reseller' ),
				'3' => gettext ( "Provider" )
		);
		return $entity_array;
	}
	function set_sip_config_options($option = "") {
		$config_option = array (
				"false" => gettext ( "False" ),
				"true" => gettext ( "True" )
		);
		return $config_option;
	}
	function set_sip_config_default($option = "") {
		$config_option = array (
				"" => gettext ( "--SELECT--" ),
				"false" => gettext ( "False" ),
				"true" => gettext ( "True" )
		);
		return $config_option;
	}
	function set_sip_bind_params($option = "") {
		$config_option = array (
				"" => gettext ( "--SELECT--" ),
				"udp" => gettext ( "UDP" ),
				"tcp" => gettext ( "TCP" )
		);
		return $config_option;
	}
	function set_sip_vad_option() {
		$config_option = array (
				"in" => gettext ( "In" ),
				"out" => gettext ( "Out" ),
				"both" => gettext ( "Both" )
		);
		return $config_option;
	}
	function set_sip_drp_option($option = "") {
		$status_array = array (
				'no' => gettext ( 'No' ),
				'yes' => gettext ( 'Yes' )
		);
		return $status_array;
	}
	function set_status_callingcard($status = '') {
		$status_array = array (
				'1' => gettext ( 'Active' ),
				'0' => gettext ( 'Inactive' ),
				'2' => gettext ( 'Deleted' )
		);
		return $status_array;
	}
	function get_status($select = "", $table = "", $status) {
		if(isset($status['reseller_status'])){
			$status['status'] = $status['reseller_status'];
		}
		if ($select != 'export') {
			$status_tab = $this->encode ( $table );
			$status ['table'] = "'" . $status_tab . "'";
			if ($status ['status'] == 0) {
				$status_array = '<label class="switch">
				  <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id=switch' . $status ['id'] . ' value=' . $status ['status'] . ' onclick="javascript:processForm(' . $status ['id'] . ',' . $status ['table'] . ')" checked>
				  <span class="slider round"></span>
				</label>';

			} else {
				$status_array = '<label class="switch">
				  <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id=switch' . $status ['id'] . ' value=' . $status ['status'] . ' onclick="javascript:processForm(' . $status ['id'] . ',' . $status ['table'] . ')">
				  <span class="slider round"></span>
				</label>';
			}
		} else {
			return ($status == 0) ? "Active" : "Inactive";
		}
		return $status_array;
	}
	
	function get_email_status($select = "", $table = "", $email_status) {
		
		if ($select != 'export') {
			$status_tab = $this->encode ( $table );
			$email_status ['table'] = "'" . $status_tab . "'";
			if ($email_status ['is_email_enable'] == 0) {
				$email_status_array = '<label class="switch">
										  <input type="checkbox" name="onoffswitch_email" class="onoffswitch-checkbox" id=switch_email' . $email_status ['id'] . ' value=' . $email_status ['is_email_enable'] . ' onclick="javascript:process_email(' . $email_status ['id'] . ',' . $email_status ['table'] . ')" checked>
										  <span class="slider round"></span>
										</label>';
			} else {
				$email_status_array = '<label class="switch">
										  <input type="checkbox" name="onoffswitch_email" class="onoffswitch-checkbox" id=switch_email' . $email_status ['id'] . ' value=' . $email_status ['is_email_enable'] . ' onclick="javascript:process_email(' . $email_status ['id'] . ',' . $email_status ['table'] . ')">
										  <span class="slider round"></span>
										</label>';
			}
		} else {
			return ($email_status == 0) ? "Active" : "Inactive";
		}
		return $email_status_array;
	}
	
	function get_sms_status($select = "", $table = "", $sms_status) {
		if ($select != 'export') {
			$status_tab = $this->encode ( $table );
			$sms_status ['table'] = "'" . $status_tab . "'";
			if ($sms_status ['is_sms_enable'] == 0) {
				$sms_status_array = '<label class="switch">
									  <input type="checkbox" name="onoffswitch_sms" class="onoffswitch-checkbox" id=switch_sms' . $sms_status ['id'] . ' value=' . $sms_status ['is_sms_enable'] . ' onclick="javascript:process_sms(' . $sms_status ['id'] . ',' . $sms_status ['table'] . ')" checked>
									  <span class="slider round"></span>
									</label>';
			} else {
				$sms_status_array = '<label class="switch">
									  <input type="checkbox" name="onoffswitch_sms" class="onoffswitch-checkbox" id=switch_sms' . $sms_status ['id'] . ' value=' . $sms_status ['is_sms_enable'] . ' onclick="javascript:process_sms(' . $sms_status ['id'] . ',' . $sms_status ['table'] . ')">
									  <span class="slider round"></span>
									</label>';
			}
		} else {
			return ($sms_status == 0) ? "Active" : "Inactive";
		}
		return $sms_status_array;
	}
	
	function get_alert_status($select = "", $table = "", $alert_status) {
		if ($select != 'export') {
			$status_tab = $this->encode ( $table );
			$alert_status ['table'] = "'" . $status_tab . "'";
			if ($alert_status ['is_alert_enable'] == 0) {
				$alert_status_array = '<label class="switch">
										  <input type="checkbox" name="onoffswitch_alert" class="onoffswitch-checkbox" id=switch_alert' . $alert_status ['id'] . ' value=' . $alert_status ['is_alert_enable'] . ' onclick="javascript:process_alert(' . $alert_status ['id'] . ',' . $alert_status ['table'] . ')" checked>
										  <span class="slider round"></span>
										</label>';
			} else {
				$alert_status_array = '<label class="switch">
										  <input type="checkbox" name="onoffswitch_alert" class="onoffswitch-checkbox" id=switch_alert' . $alert_status ['id'] . ' value=' . $alert_status ['is_alert_enable'] . ' onclick="javascript:process_alert(' . $alert_status ['id'] . ',' . $alert_status ['table'] . ')">
										  <span class="slider round"></span>
										</label>';
			}
		} else {
			return ($alert_status == 0) ? "Active" : "Inactive";
		}
		return $alert_status_array;
	}

	function get_routetype($select = "", $table = "", $status) {
		return ($status == 0) ? "LCR" : "COST";
	}
	function get_prorate($select = "", $table = "", $status) {
		return ($status == 0) ? "Yes" : "No";
	}
	function get_import_status($status) {
		return strtolower ( trim ( $status ) ) == 'active' ? 0 : 1;
	}
	function get_did_status($select, $table, $status) {
		return ($status ['status'] == 0) ? "<span class='label label-sm label-inverse arrowed-in' title='release'>Active<span>" : "<span class='label label-sm' title='release'>Inactive<span>";
	}
	function get_invoice_date($select, $accountid = 0, $reseller_id, $order_by = 'id') {

		$where = array (
				"reseller_id" => $reseller_id
		);
		if ($accountid > 0) {
			$where ['accountid'] = $accountid;
			$posttoexternal = $this->get_field_name ( "posttoexternal", "accounts", array (
					"id" => $accountid
			) );
			if ($posttoexternal == 1)
				$where ['type'] = "I";			
		}		
		$invoice_res = $this->CI->db_model->select ( $select, "invoices", $where, $order_by, "DESC", "1", "0" );

		if ($invoice_res->num_rows () > 0) {
			$invoice_info = ( array ) $invoice_res->first_row ();
			return $invoice_info [$select];
		}

		return false;
	}
	function convert_to_date($select = '', $table = '', $from_date) {
		$from_date = date ( 'Y-m-d', strtotime ( $from_date ) );
		return $from_date;
	}
	function get_account_balance($select = "", $table = "", $amount) {
		$this->CI->load->model ( 'common_model' );
		if ($amount == 0) {
			return $amount;
		} else {
			$balance = $this->CI->common_model->add_calculate_currency ( ($amount), "", '', true, true );

			return $balance;
		}
	}
	function convert_to_currency($select = "", $table = "", $amount) {
		$this->CI->load->model ( 'common_model' );
		return $this->CI->common_model->calculate_currency ( $amount, '', '', true, false );
	}
	function account_number_icon($select = "", $table = "", $number) {
		$return_value = '';
		$where = array (
				'number' => $number
		);
		$this->CI->db->where("deleted",0);
		$account_res = ( array ) $this->CI->db->get_where ( "accounts", $where )->first_row ();
		if ($account_res ['type'] == 0) {
			$return_value = " <span title='Edit'>" . $account_res ['number'] . " </span>" . '<span class="badge badge-success float-left mt-1" title="Customer">'.gettext("Customer").'</span>';
		}
		if ($account_res ['type'] == 3) {
			$return_value = " <span title='Edit'>" . $account_res ['number'] . " </span>" . '<span class="badge badge-primary float-left mt-1" title="Provider">'.gettext("Provider").'</span>';
		}
		if ($account_res ['type'] == - 1 || $account_res ['type'] == 2) {
			$return_value = " <span title='Edit'>" . $account_res ['number'] . " </span>" . '<span class="badge badge-danger" title="Admin"></span>';
		}
		if ($account_res ['type'] == 4) {
			$return_value = " <span title='Edit'>" . $account_res ['number'] . " </span>" . '<span class="badge badge-secondary" title="Subadmin">'.gettext("Subadmin").'</span>';
		}
		return $return_value;
	}
	function convert_to_currency_account($select = "", $table = "", $amount) {
		$this->CI->load->model ( 'common_model' );
		return $this->CI->common_model->calculate_currency_customer ( $amount );
	}
	function get_paid_status($select = "", $table = "", $status) {
		return ($status == 1) ? "Paid" : "Unpaid";
	}
	function set_account_type_recharge($select = "", $table = "", $status) {
		return ($status == 1) ? "Postpaid" : "Prepaid";
	}
	function set_account_type($status = '') {
		$status_array = array (
				'0' => gettext ( 'Prepaid' ),
				'1' => gettext ( 'Postpaid' )
		);
		return $status_array;
	}
	function set_account_type_search($status = '') {
		$status_array = array (
				'' => gettext ( "--Select--" ),
				'0' => gettext ( 'Prepaid' ),
				'1' => gettext ( 'Postpaid' )
		);
		return $status_array;
	}
	function get_account_type($select = "", $table = "", $PTE) {
		return ($PTE == 1) ? gettext("Postpaid") : gettext("Prepaid");
	}
	function get_refill_by($select = "", $table = "", $type) {
		if ($type == '-1') {
			$type = "Admin";
		} else {
			$type = $this->get_field_name("number", "accounts", array("id" => $type));
		}
		return $type;
	}
	function get_payment_by($select = "", $table = "", $type) {
		if ($type == '-1') {
			$type = "Admin";
		} else {
			$type = $this->get_field_name ( "number", "accounts", array (
					"id" => $type
			) );
		}
		return $type;
	}
	function set_payment_type($payment_type = '') {
		$status_array = array (
				'0' => gettext ( 'Refill' ),
				'1' => gettext ( 'Postcharge' )
		);
		return $status_array;
	}
	function search_int_type($status = '') {
		$status_array = array (
				'1' => gettext ( 'Is Equal To' ),
				'2' => gettext ( 'Is Not Equal To' ),
				'3' => gettext ( 'Greater Than' ),
				'4' => gettext ( 'Less Than' ),
				'5' => gettext ( 'Greater Or Equal Than' ),
				'6' => gettext ( 'Less Or Equal Than' )
		);
		return $status_array;
	}
	function update_int_type($status = '') {
		$status_array = array (
				'1' => gettext ( 'Preserve' ),
				'2' => gettext ( 'Set To' ),
				'3' => gettext ( 'Increase By' ),
				'4' => gettext ( 'Decrease By' )
		);
		return $status_array;
	}
	function update_drp_type($status = '') {
		$status_array = array (
				'1' => gettext ( 'Preserve' ),
				'2' => gettext ( 'Set To' )
		);
		return $status_array;
	}
	function search_string_type($status = '') {
		$status_array = array (
				'5' => gettext ( "Begins With" ),
				'1' => gettext ( 'Contains' ),
				'2' => gettext ( "Doesnt Contain" ),
				'3' => gettext ( 'Is Equal To' ),
				'4' => gettext ( 'Is Not Equal To' ),
				"6" => gettext ( "Ends With" )
		);
		return $status_array;
	}
	// Ashish ASTPPCOM-751
	function search_mail_to_type($status = '') {
		$status_array = array (
				'5' => gettext ( "Begins With" ),
				'1' => gettext ( 'Contains' ),
				'2' => gettext ( "Doesnt Contain" ),
				// '3' => gettext ( 'Is Equal To' ),
				// '4' => gettext ( 'Is Not Equal To' ),
				// "6" => gettext ( "Ends With" )
		);
		return $status_array;
	}
	// Ashish End
	function set_protocal($protpcal = '') {
		$status_array = array (
				'SIP' => gettext ( 'SIP' ),
				'IAX2' => gettext ( 'IAX2' ),
				'Zap' => gettext ( 'Zap' ),
				'Local' => gettext ( 'Local' ),
				'OH323' => gettext ( 'OH323' ),
				'OOH323C' => gettext ( 'OOH323C' )
		);
		return $status_array;
	}
	function set_notify_by($status = '') {
		$status_array = array (
				'' => gettext ( 'Select Notify By' ),
				'0' => gettext ( 'CSV' ),
				'1' => gettext ( 'Email' )
		);
		return $status_array;
	}
	function convert_to_percentage($select = "", $table = "", $amount) {
		return round ( $amount, 2 ) . " %";
	}
	function convert_to_minutes($select = "", $table = "", $amount) {
		return str_replace ( '.', ':', round ( $amount / 60, 2 ) );
	}
	function set_filter_type_search($status = '') {
		$status_array = array (
				'pricelist_id' => gettext ( 'Rate Group' ),
				'accountid' => gettext ( 'Customer' ),
				'reseller_id' => gettext ( 'Reseller' )
		);
		return $status_array;
	}
	function set_routetype_status($select = '') {
		$status_array = array (
				"" => gettext ( "--Select--" ),
				"0" => gettext ( "LCR" ),
				"1" => gettext ( "COST" )
		);
		return $status_array;
	}
	function attachment_icons($select = "", $table = "", $attachement = "") {
		if ($attachement != "") {
			$array = explode ( ",", $attachement );
			$str = '';
			foreach ( $array as $key => $val ) {
				$link = base_url () . "email/email_history_list_attachment/" . $val;
				$str .= "<a href='" . $link . "' title='" . $val . "' class='btn btn-royelblue btn-sm'><i class='fa fa-paperclip fa-fw'></i></a>&nbsp;&nbsp;";
			}
			return $str;
		} else {
			return "";
		}
	}
	function set_despostion($dis = '') {
		$status_array = array (
				"" => gettext("--Select Disposition--"),
				"ACCOUNT_INACTIVE_DELETED" => gettext("ACCOUNT_INACTIVE_DELETED"),
				"ACCOUNT_EXPIRE" => gettext("ACCOUNT_EXPIRE"),
				"ALLOTTED_TIMEOUT" => gettext("ALLOTTED_TIMEOUT"),
				"AUTHENTICATION_FAIL" => gettext("AUTHENTICATION_FAIL"),
				"BEARERCAPABILITY_NOTAUTH" => gettext("BEARERCAPABILITY_NOTAUTH"),
				"BEARERCAPABILITY_NOTAVAIL" => gettext("BEARERCAPABILITY_NOTAVAIL"),
				"BEARERCAPABILITY_NOTIMPL" => gettext("BEARERCAPABILITY_NOTIMPL"),
				"CALL_REJECTED" => gettext("CALL_REJECTED"),
				"CHAN_NOT_IMPLEMENTED" => gettext("CHAN_NOT_IMPLEMENTED"),
				"CHANNEL_UNACCEPTABLE" => gettext("CHANNEL_UNACCEPTABLE"),
				"DESTINATION_OUT_OF_ORDER" => gettext("DESTINATION_OUT_OF_ORDER"),
				"DESTINATION_BLOCKED" => gettext("DESTINATION_BLOCKED"),
				"DID_DESTINATION_NOT_FOUND" => gettext("DID_DESTINATION_NOT_FOUND"),
				"FACILITY_REJECTED" => gettext("FACILITY_REJECTED"),
				"FACILITY_NOT_SUBSCRIBED" => gettext("FACILITY_NOT_SUBSCRIBED"),
				"FACILITY_NOT_IMPLEMENTED" => gettext("FACILITY_NOT_IMPLEMENTED"),
				"FRAUD_CALL_PER_ACCOUNT" => gettext("FRAUD_CALL_PER_ACCOUNT"),
				"FRAUD_CALL_PER_DESTINATION " => gettext("FRAUD_CALL_PER_DESTINATION "),
				"FRAUD_COST_PER_ACCOUNT" => gettext("FRAUD_COST_PER_ACCOUNT"),
				"FRAUD_COST_PER_DESTINATION" => gettext("FRAUD_COST_PER_DESTINATION"),
				"INVALID_NUMBER_FORMAT" => gettext("INVALID_NUMBER_FORMAT"),
				"INCOMPATIBLE_DESTINATION" => gettext("INCOMPATIBLE_DESTINATION"),
				"MANAGER_REQUEST" => gettext("MANAGER_REQUEST"),
				"MEDIA_TIMEOUT" => gettext("MEDIA_TIMEOUT"),
				"NO_ROUTE_DESTINATION" => gettext("NO_ROUTE_DESTINATION"),
				"NORMAL_CLEARING" => gettext("NORMAL_CLEARING"),
				"NETWORK_OUT_OF_ORDER" => gettext("NETWORK_OUT_OF_ORDER"),
				"NORMAL_UNSPECIFIED" => gettext("NORMAL_UNSPECIFIED"),
				"NORMAL_CIRCUIT_CONGESTION" => gettext("NORMAL_CIRCUIT_CONGESTION"),
				"NORMAL_TEMPORARY_FAILURE" => gettext("NORMAL_TEMPORARY_FAILURE"),
				"NO_SUFFICIENT_FUND" => gettext("NO_SUFFICIENT_FUND"),
				"NO_USER_RESPONSE" => gettext("NO_USER_RESPONSE"),
				"NO_ANSWER" => gettext("NO_ANSWER"),
				"NUMBER_CHANGED" => gettext("NUMBER_CHANGED"),
				"ORIGINATOR_CANCEL" => gettext("ORIGINATOR_CANCEL"),
				"ORIGNATION_RATE_NOT_FOUND" => gettext("ORIGNATION_RATE_NOT_FOUND"),
				"OUTGOING_CALL_BARRED" => gettext("OUTGOING_CALL_BARRED"),
				"PROGRESS_TIMEOUT" => gettext("PROGRESS_TIMEOUT"),
				"RECOVERY_ON_TIMER_EXPIRE" => gettext("RECOVERY_ON_TIMER_EXPIRE"),
				"RESELLER_COST_CHEAP" => gettext("RESELLER_COST_CHEAP"),
				"SERVICE_NOT_IMPLEMENTED" => gettext("SERVICE_NOT_IMPLEMENTED"),
				"SERVICE_UNAVAILABLE" => gettext("SERVICE_UNAVAILABLE"),
				"SUCCESS" => gettext("SUCCESS"),
				"SWITCH_CONGESTION" => gettext("SWITCH_CONGESTION"),
				"TERMINATION_RATE_NOT_FOUND" => gettext("TERMINATION_RATE_NOT_FOUND"),
				"UNSPECIFIED" => gettext("UNSPECIFIED"),
				"UNALLOCATED_NUMBER" => gettext("UNALLOCATED_NUMBER"),
				"USER_BUSY" => gettext("USER_BUSY"),
				"USER_NOT_REGISTERED" => gettext("USER_NOT_REGISTERED"),
				"REQUESTED_CHAN_UNAVAIL"=> gettext("REQUESTED_CHAN_UNAVAIL")
		);
		return $status_array;
	}
	function set_calltype($type = '') {
		// ASTPPCOM-982 Ashish start
		$voice_broadcast_status = $this->CI->db_model->countQuery("*", "addons", array(
            "package_name" => "voice_broadcast"
		));
		if($voice_broadcast_status == 1){
			$status_array = array (
				"" => gettext ( "--Select Type--" ),
				"STANDARD" => gettext ( "STANDARD" ),
				"DID" => gettext ( "DID" ),
				"CALLINGCARD" => gettext ( "CALLINGCARD" ),
				"FREE" => gettext ( "FREE" ),
				"LOCAL" => gettext ( "LOCAL" ),
				"BROADCAST" => gettext ( "BROADCAST" ),
			);		
		}else{
			$status_array = array (
					"" => gettext ( "--Select Type--" ),
					"STANDARD" => gettext ( "STANDARD" ),
					"DID" => gettext ( "DID" ),
					"CALLINGCARD" => gettext ( "CALLINGCARD" ),
					"FREE" => gettext ( "FREE" ),
					"LOCAL" => gettext ( "LOCAL" ),
			);
		}
		// ASTPPCOM-982 Ashish End
		return $status_array;
	}
	function set_search_status($select = '') {
		$status_array = array (
				"" => gettext ( "--Select--" ),
				"0" => gettext ( "Active" ),
				"1" => gettext ( "Inactive" )
		);
		return $status_array;
	}
	function set_Billing_Schedule_status($select = '') {
		$status_array = array (
				"" => gettext ( "--Select--" ),
				"0" => gettext ( "Daily" ),
				"2" => gettext ( "Monthly" )
		);
		return $status_array;
	}
	function get_action_buttons($buttons_arr, $linkid) { 
		$ret_url = '';
		if (! empty ( $buttons_arr ) && $buttons_arr != '') {
			$currnet_url=current_url();
                        $url_explode= explode('/',$currnet_url);
                        $module_name= strtolower($url_explode[3]);
                        $sub_module_name= $url_explode[4];
                        $sub_module_name= str_replace("_json","",$sub_module_name);
                        $permissioninfo = $this->CI->session->userdata('permissioninfo');
                        $logintype = $this->CI->session->userdata('logintype');
			foreach ( $buttons_arr as $button_key => $buttons_params ) {
			if((isset($permissioninfo[$module_name][$sub_module_name][strtolower($button_key)]) && $permissioninfo[$module_name][$sub_module_name][strtolower($button_key)] == 0  or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3')){
				if (strtoupper ( $button_key ) == "EDIT") {
					$ret_url .= $this->build_edit_button ( $buttons_params, $linkid );
				}
				if (strtoupper ( $button_key ) == "RESEND") {
					$ret_url .= $this->build_edit_button_resend ( $buttons_params, $linkid );
				}
				if (strtoupper ( $button_key ) == "EDIT_RESTORE") {
					$ret_url .= $this->build_edit_button_restore ( $buttons_params, $linkid );
				}
				if (strtoupper ( $button_key ) == "DELETE") {
					$ret_url .= $this->build_delete_button ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "VIEW") {
					$ret_url .= $this->build_view_button ( $buttons_params, $linkid );
				}
				if (strtoupper ( $button_key ) == "TAXES") {
					$ret_url .= $this->build_add_taxes_button ( $buttons_params, $linkid );
				}
				if (strtoupper ( $button_key ) == "BLUEBOX_LOGIN") {
					$ret_url .= $this->build_bluebox_login ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "CALLERID") {
					$ret_url .= $this->build_add_callerid_button ( $buttons_params, $linkid );
				}
				if (strtoupper ( $button_key ) == "PAYMENT") {
					$ret_url .= $this->build_add_payment_button ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "DOWNLOAD") {
					$ret_url .= $this->build_add_download_button ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "START") {
					$ret_url .= $this->build_start_button ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "STOP") {
					$ret_url .= $this->build_stop_button ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "RELOAD") {
					$ret_url .= $this->build_reload_button ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "RESCAN") {
					$ret_url .= $this->build_rescan_button ( $buttons_params->url, $linkid );
				}

				if (strtoupper ( $button_key ) == "DOWNLOAD_DATABASE") {
					$ret_url .= $this->build_add_download_database_button ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "DELETE_ANIMAP") {
					$ret_url .= $this->build_delete_button_animap ( $buttons_params->url, $linkid );
				}
				if (strtoupper ( $button_key ) == "EDIT_ANIMAP") {
					$ret_url .= $this->build_edit_button_animap ( $buttons_params, $linkid );
				}
				if (strtoupper ( $button_key ) == "ANIMAP") {
					$ret_url .= $this->build_animap_button ( $buttons_params, $linkid );
				}
			}
		}
		}
		return $ret_url;
	}
	function build_delete_button_animap($url, $linkid) {
		$link = base_url () . $url . "" . $linkid;
		return '<a href="javascript:void(0)" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_msg_destination(' . $linkid . ');"><i class="fa fa-trash fa-fw"></i></a>';
	}
	function build_edit_button_animap($button_params, $linkid) {
		$link = base_url () . $button_params->url . "" . $linkid;
		return '<a href="javascript:void(0);" id="destination_new" class="btn btn-royelblue btn-sm" onclick="return get_destination(' . $linkid . ');" title="Update"><i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;';
	}
	function build_animap_button($button_params, $linkid) {
		$link = base_url () . $button_params->url . "" . $linkid;
		return '<a href="' . $link . '" class="btn btn-royelblue btn-sm animap_image" rel="facebox" title="ANI Map"><i class="fa fa-reorder fa-fw"></i></a>&nbsp;';
	}
	function build_edit_button($button_params, $linkid) {
		$link = base_url () . $button_params->url . "" . $linkid;

		if ($button_params->mode == 'popup') {
			$rel = (isset ( $button_params->layout ) && $button_params->layout != '') ? "facebox_" . $button_params->layout : "facebox";
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" rel="' . $rel . '" title="Edit" ="small"><i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;';
		} else if (strpos ( $link, 'customer_edit' ) !== false) {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Edit"><i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;';
		} else {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Edit"><i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;';
		}
	}
	function build_custome_edit_button($button_params, $field, $linkid) {
		$linkid = str_replace("#","",$linkid);
		$link = base_url () . $button_params->url . "" . $linkid;

		if (isset ( $button_params->layout )) {
			if ($button_params->mode == 'popup') {
				return '<a href="' . $link . '" style="color:#3b3280" rel="facebox_medium" title="Update">' . $field . '</a>&nbsp;';
			} else {
				return '<a href="' . $link . '" style="color:#3b3280" title="Edit">' . $field . '</a>&nbsp;';
			}
		} else {
			if ($button_params->mode == 'popup') {
				return '<a href="' . $link . '" style="color:#3b3280" rel="facebox" title="Update">' . $field . '</a>&nbsp;';
			} else {
				return '<a href="' . $link . '" style="color:#3b3280" title="Edit">' . $field . '</a>&nbsp;';
			}
		}
	}
	function build_edit_button_restore($button_params, $linkid) {
		$link = base_url () . $button_params->url . "" . $linkid;
		if ($button_params->mode == 'popup') {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" rel="facebox" title="Restore" onClick="return get_alert_msg();"><i class="fa fa-reorder fa-fw"></i></a>&nbsp;';
		} else {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Restore" onClick="return get_alert_msg_restore();"><i class="fa fa-reorder fa-fw"></i></a>&nbsp;';
		}
	}
	function build_delete_button($url, $linkid) {
		$flag = '0';
		$data = explode ( "/", $url );
		$link = base_url () . $url . "" . $linkid;
		foreach ( $data as $key => $value ) {
			if ($value == 'price_delete')
				$flag = '1';
			if ($value == 'trunk_remove')
				$flag = '2';
			if ($value == 'customer_delete' || $value == 'provider_delete')
				$flag = '3';
			if ($value == 'reseller_delete')
				$flag = '4';
		}
		if ($flag == '1') {
			$where = array (
					'pricelist_id' => $linkid,
					'deleted !=' => '1'
			);
			$customer_cnt = $this->get_field_count ( 'id', 'accounts', $where );
			$where = array (
					'pricelist_id' => $linkid
			);
			$rategroup_cnt = $this->get_field_count ( 'id', 'routes', $where );
			if ($rategroup_cnt > 0 || $customer_cnt > 0) {

				return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_message(' . $rategroup_cnt . ',' . $customer_cnt . ',' . $linkid . ',1);"><i class="fa fa-trash fa-fw"></i></a>';
			} else {
				return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_msg();"><i class="fa fa-trash fa-fw"></i></a>';
			}
		}
		if ($flag == '2') {
			$where = array (
					'trunk_id' => $linkid
			);
			$trunk_cnt = $this->get_field_count ( 'id', 'outbound_routes', $where );
			if ($trunk_cnt > 0) {
				return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_message(' . $trunk_cnt . ',null,' . $linkid . ',2);"><i class="fa fa-trash fa-fw"></i></a>';
			} else {
				return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_msg();"><i class="fa fa-trash fa-fw"></i></a>';
			}
		}
		if ($flag == '3') {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_message(0,null,' . $linkid . ',3);">
		<i class="fa fa-trash fa-fw"></i></a>';
		}
		if ($flag == '4') {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_message(0,null,' . $linkid . ',4);">
		<i class="fa fa-trash fa-fw"></i></a>';
		}
		if ($flag == '0' && $url . $linkid != 'accounts/admin_delete/1') {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_msg();"><i class="fa fa-trash fa-fw"></i></a>';
		}
	}
	function build_view_button($button_params, $linkid) {
		$link = base_url () . $button_params->url . "" . $linkid;
		if ($button_params->mode == 'popup') {
			$rel = (isset ( $button_params->layout ) && $button_params->layout != '') ? "facebox_" . $button_params->layout : "facebox";
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" rel="' . $rel . '" title="View Details"><i class="fa fa-reorder fa-fw"></i></a>&nbsp;';
		} else {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="View Details"><i class="fa fa-reorder fa-fw"></i></a>&nbsp;';
		}
	}
	function build_add_taxes_button($button_params, $linkid) {
		$link = base_url () . $button_params->url . "" . $linkid;
		if ($button_params->mode == 'popup') {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" rel="facebox" title="Add Account Taxes"><i class="fa fa-reorder fa-fw"></i></a>&nbsp;';
		} else {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Add Account Taxes"><i class="fa fa-reorder fa-fw"></i></a>&nbsp;';
		}
	}
	function build_add_download_database_button($url, $linkid) {
		$link = base_url () . $url . "" . $linkid;
		return '<a href="' . $link . '" class="btn btn-royelblue btn-sm "  title="Download Database" ><i class="fa-fw fa fa-file-archive-o"></i></a>&nbsp;';
	}
	function build_add_callerid_button($button_params, $linkid) {
		$link = base_url () . $button_params->url . "" . $linkid;
		if ($button_params->mode == 'popup') {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" rel="facebox" title="Force Caller Id"><i class="fa fa-mobile-phone fa-fw"></i></a>&nbsp;';
		} else {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="CallerID"><i class="fa fa-mobile-phone fa-fw"></i></a>&nbsp;';
		}
	}
	function build_start_button($url, $linkid) {
		$link = base_url () . $url . "" . $linkid;

		return '<a href="' . $link . '" class=""  title="Start" style="text-decoration:none;color: #428BCA;"><b>'.gettext("Start").' |</b></a>&nbsp;';
	}
	function build_stop_button($url, $linkid) {
		$link = base_url () . $url . "" . $linkid;
		return '<a href="' . $link . '" class=""  title="Stop" style="text-decoration:none;color: #428BCA;" ><b>'.gettext("Stop").' |</b></a>&nbsp;';
	}
	function build_reload_button($url, $linkid) {
		$link = base_url () . $url . "" . $linkid;
		// return '<a href="' . $link . '" class=""  title="reload" style="text-decoration:none;color: #428BCA;"><b>Reload |</b></a>&nbsp;';
		return "<a onclick=reload_port('$link')  class=''  title='reload' style='text-decoration:none;color: #428BCA;cursor:pointer'><b>".gettext('Reload')." |</b></a>&nbsp;";
	}
	function build_rescan_button($url, $linkid) {
		$link = base_url () . $url . "" . $linkid;
		return '<a href="' . $link . '" class=""  title="rescan" style="text-decoration:none;color: #428BCA;"><b>'.gettext("Rescan").'</b></a>&nbsp;';
	}
	function build_add_payment_button($url, $linkid) {
		$link = base_url () . $url . "" . $linkid;
		return '<a href="' . $link . '" style="cursor:pointer;color:#3b3280" rel="facebox_medium" title="Refill">PAYMENT</a>&nbsp;';
	}
	function build_add_download_button($url, $linkid) {
		$link = base_url () . $url . "" . $linkid;
		return '<a href="' . $link . '" class="btn btn-royelblue btn-sm"  title="Download Invoice" ><i class="fa fa-cloud-download fa-fw"></i></a>&nbsp;';
	}
	function build_edit_button_resend($button_params, $linkid) {
		$link = base_url () . $button_params->url . "" . $linkid;
		if ($button_params->mode == 'popup') {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" rel="facebox" title="Resend Mail"><i class="fa fa-repeat"></i></a>&nbsp;';
		} else {
			return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" title="Resend Mail"><i class="fa fa-repeat"></i></a>&nbsp;';
		}
	}
	function get_only_numeric_val($select = "", $table = "", $string) {
		if($table=='routes' || $table=='outbound_routes'){
			return preg_replace('/[^\d+0-9]/', '', $string);
		}else{
			return filter_var ( $string, FILTER_SANITIZE_NUMBER_INT );
		}
	}
	// Kinjal ASTPPCOM-978 Start
	function mail_to_users($type, $accountinfo, $attachment = "", $amount = "",$filePath ="") {
	// Kinjal ASTPPCOM-978 END
		$subject              = "";
		$settings_reply_email = 'astpp@astpp.com';
		$reseller_id          = $accountinfo['reseller_id'] > 0 ? $accountinfo['reseller_id'] : 0;
		$invoiceconf = (array)$this->CI->db_model->getSelect('company_name,website,emailaddress' , 'invoice_conf' , array("accountid" => 1))->first_row();
		if($type == 'email_sent_support_ticket' || $type == 'auto_reply_mail_support'){
			$department_id = $this->get_field_name('department_id','support_ticket' , array("support_ticket_number" => $accountinfo['ticket_number']));
			$department_smtp = $this->get_field_name('smtp_user','department',array("id" => $department_id));
			if($department_smtp !=''){
				$settings_reply_email = $department_smtp;
			}
		}else{
			$smtp_user = $this->get_field_name('value' , 'system' , array("name" => "smtp_user"));
			if($smtp_user != ''){
				if($smtp_user == 'SMTP_USER_NAME'){
					$settings_reply_email = $invoiceconf['emailaddress'];
				}else{
					$settings_reply_email = $smtp_user;
				}
			}else{
				$this->CI->db->select('domain');
				$domain_arr=(array)$this->CI->db->get_where("invoice_conf")->first_row();
				$this->CI->db->where('domain',$_SERVER['HTTP_HOST']);
				$this->CI->db->or_where("accountid",1);
				$this->CI->db->order_by("id","asc");
				$this->CI->db->limit(1);
				$invoiceconf=(array)$this->CI->db->get_where("invoice_conf")->first_row();
				$settings_reply_email=$invoiceconf['emailaddress'];
			}
		}
		$company_name=$invoiceconf['company_name'];
		$company_website=$invoiceconf['website'];
		$where = array('name' => $type);
		$query = $this->CI->db_model->getSelect("*", "default_templates", $where);
		$query = $query->result();
		$sms_message=$query[0]->sms_template;
		$alert_template=$query[0]->alert_template;
		$message = $query[0]->template;
		$subject = $query [0]->subject;	
		$accountinfo['email']=(isset($accountinfo['notification_email']) && $accountinfo['notification_email'] != '')?$accountinfo['notification_email'] : $accountinfo['email'];
		// ASTPPCIM-979 Ashish start
		$useremail = $accountinfo['email'];
		// ASTPPCOM-979 Ashish End
		$userdata = (array)$this->CI->db->get_where("accounts",array('email'=>$useremail,'status'=> 0))->first_row();
		$usermobile=(isset($accountinfo['telephone_1']) && $accountinfo['telephone_1'] != '')?$accountinfo['telephone_1'] : $userdata['telephone_1'];
		$message = html_entity_decode($message);
		$message = str_replace("#COMPANY_EMAIL#", $settings_reply_email, $message);
		$message = str_replace("#COMPANY_NAME#", $company_name, $message);
		$sms_message = str_replace("#COMPANY_NAME#", $company_name, $sms_message);
		$message = str_replace("#COMPANY_WEBSITE#", $company_website, $message);
		$message = str_replace("</p>", "", $message);
		if(isset($accountinfo['refill_amount']) && $accountinfo['refill_amount']!= ""){
			$refillamount = $accountinfo['refill_amount'];
		}else{
			$refillamount = "0";
		}

		$sip_user_name     = isset($accountinfo ['sip_user_name']) && $accountinfo ['sip_user_name'] != "" ? $accountinfo ['sip_user_name'] : '';
		$callkit_token     = isset($accountinfo ['callkit_token']) && $accountinfo ['callkit_token'] != "" ? $accountinfo ['callkit_token'] : '';
		$status_code      = isset($accountinfo ['status_code']) && $accountinfo ['status_code'] != "" ? $accountinfo ['status_code'] : '';
	switch ($type) {
	    case 'product_renewed':

	        $subject = str_replace('#PRODUCT_NAME#', $accountinfo['name'], $subject);
       	    $subject = str_replace('#NUMBER#', $accountinfo['number'], $subject);
			$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
        	$message = str_replace('#PRODUCT_NAME#', $accountinfo['name'], $message);
        	$message = str_replace('#NEXT_BILL_DATE#', $accountinfo['next_billing_date'], $message);
			$message = str_replace('#QUANTITY#', $accountinfo['quantity'], $message);
			$message = str_replace('#TOTAL_PRICE#', isset($accountinfo['quantity']) && $accountinfo['quantity'] != 0 ?$accountinfo['price'] * $accountinfo['quantity'] : $accountinfo['price'], $message);
			$message = str_replace('#PRODUCT_AMOUNT#', $accountinfo['price'], $message);
		break;

	   	// Jaimin ASTPPCOM-738
	    case 'account_refilled':
		$subject = str_replace('#REFILLBALANCE#', $accountinfo['refill_amount'], $subject);
		$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
        $message = str_replace('#REFILLBALANCE#', $accountinfo['refill_amount'], $message);
        $message = str_replace('#BALANCE#', $accountinfo['balance'], $message);
        $message = str_replace('#COMPANY_WEBSITE#', $company_website, $message);
		$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
		break;

		case 'account_postcharge':
		$subject = str_replace('#REFILLBALANCE#', $accountinfo['refill_amount'], $subject);
		$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
        $message = str_replace('#REFILLBALANCE#', $accountinfo['refill_amount'], $message);
        $message = str_replace('#BALANCE#', $accountinfo['balance'], $message);
        $message = str_replace('#COMPANY_WEBSITE#', $company_website, $message);
		$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
		break;
		// END

		case 'create_account':
		$subject = str_replace('#COMPANY_NAME#', $company_name, $subject);
		
		$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
        $message = str_replace('#NUMBER#', $accountinfo['number'], $message);
        $message = str_replace('#PASSWORD#', $accountinfo['password'], $message);
        $message = str_replace('#COMPANY_WEBSITE#', $company_website, $message);
		$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
		break;


        case 'create_sip_device':
		
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#USERNAME#', $accountinfo['number'], $message);
                $message = str_replace('#PASSWORD#', $accountinfo['password'], $message);
                $message = str_replace('#COMPANY_WEBSITE#', $accountinfo['number'], $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
		break;
		
        // Ashish ASTPPCOM-748
		case 'new_invoice':
			$subject = str_replace('#INVOICE_NUMBER#',isset($accountinfo['invoice_number'])?$accountinfo['invoice_number']:'', $subject);
			$subject = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:"", '', '', true,true),$subject);
			$subject = str_replace('#INVOICE_DATE#', isset($accountinfo['generate_date'])?$accountinfo['generate_date']:"", $subject);
			$subject = str_replace('#DUE_DATE#',isset($accountinfo['due_date'])?$accountinfo['due_date']:'', $subject);
			
			$message = str_replace('#INVOICE_DATE#', $this->convert_GMT_to('generate_date','invoices',isset($accountinfo['generate_date'])?$accountinfo['generate_date']:"", isset($accountinfo['timezone_id']) ? $accountinfo['timezone_id'] : ''), $message);
			$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
			$message = str_replace('#INVOICE_NUMBER#', isset($accountinfo['invoice_number'])?$accountinfo['invoice_number']:'', $message);
			$message = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:"", '', $to_currency, true,true), $message);
			$message = str_replace('#DUE_DATE#',isset($accountinfo['due_date'])?$accountinfo['due_date']:'', $message);
			
			$sms_message = str_replace('#INVOICE_NUMBER#',isset($accountinfo['invoice_number'])?$accountinfo['invoice_number']:'', $sms_message);
			$sms_message = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:"", '', $to_currency, true,true), $sms_message);		
			$sms_message = str_replace('#INVOICE_DATE#', $this->convert_GMT_to('generate_date','invoices',isset($accountinfo['generate_date'])?$accountinfo['generate_date']:"", isset($accountinfo['timezone_id']) ? $accountinfo['timezone_id'] : ''), $sms_message);
			$sms_message = str_replace('#DUE_DATE#',isset($accountinfo['due_date'])?$accountinfo['due_date']:'', $sms_message);

			$alert_template = str_replace('#INVOICE_DATE#', isset($accountinfo['generate_date'])?$accountinfo['generate_date']:"", $alert_template);
			$alert_template = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:"", '', '', true,true), $alert_template);
			$alert_template = str_replace('#DUE_DATE#', isset($accountinfo['due_date'])?$accountinfo['due_date']:'', $alert_template);
	        $alert_template = str_replace('#INVOICE_NUMBER#', isset($accountinfo['invoice_number'])?$accountinfo['invoice_number']:'', $alert_template);
		break;
        // Ashish ASTPPCOM-748 End
        case 'low_balance':
			    $subject     = str_replace('#NUMBER#', $accountinfo['number'], $subject);
				$message = str_replace('#NAME#', $accountinfo['first_name'] , $message);
				$message = str_replace('#BALANCE#', $accountinfo['balance'], $message);
                $message = str_replace('#COMPANY_WEBSITE#', $accountinfo['number'], $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
				$message = str_replace('#COMPANY_NAME#', $company_name, $message);
		break;
		// Kinjal ASTPPCOM-978 Start
		case 'automated_report':
			$subject = str_replace('#Report Name#', $accountinfo['subject_title'], $subject);
			$subject = str_replace('#Interval Freq. of Email#', $accountinfo['interval_freq_of_email'], $subject);
			$sms_message    = "";
			$alert_template = "";
			$message = str_replace('#Report Name#', $accountinfo['subject_title'], $message);
			$message = str_replace('#Integer value#', $accountinfo['report_interval_days'], $message);
			$message = str_replace('#Interval#', $accountinfo['report_interval_recurring'], $message);
			$message = str_replace('#Interval Filter On#', $accountinfo['interval_filter_on'], $message);
			if($filePath != '')
			{
				$today_date = gmdate('Y-m-d H:i:s');
				$purge_date = common_model::$global_config['system_config']['automated_report_attachment_deleted'];
				$last_date = date('Y-m-d' , strtotime("+ ".$purge_date." day"));
				$filelink = $purge_date != -1 ? '<p>You can download Automated report from following link until '.$last_date.' </p>' : '<p>You can download Automated report from following link </p>';
				$filelink .= '<a href="'.$filePath.'" target="_blank">'.$filePath.'</a>';
				$message = $message.'<br/>'.$filelink;
			}
		break;
		// Kinjal ASTPPCOM-978 END
		case 'schedule_report':
	         	$subject = str_replace('#title#', $accountinfo['subject_title'], $subject);
				$sms_message    = "";
				$alert_template = "";
				$attachment     = $accountinfo['attachment'];
       break;

       case 'ported_number':
	         	$subject = str_replace('#user#', $accountinfo['first_name'], $subject);
				$sms_message    = "";
				$alert_template = "";
				$attachment     = $accountinfo['attachment'];
        break;
        case 'ported_number_ftp_connect':
	         	$subject = str_replace('#user#', $accountinfo['first_name'], $subject);
				$sms_message    = "";
				$alert_template = "";
        break;
                              
            case 'signup_confirmation':
		
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#OTP#', $accountinfo['number'], $message);
                $message = str_replace('#COMPANY_WEBSITE#', $accountinfo['number'], $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
            break;

            case 'new_password':
			
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#PASSWORD#', $accountinfo['password'], $message);
                $message = str_replace('#COMPANY_WEBSITE#', $company_website, $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
			
           	break;
           
           	case 'reset_password':
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#PASSWORD#', $accountinfo['password'], $message);
                $message = str_replace('#COMPANY_WEBSITE#', $company_website, $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
			
           	break;		

            case 'forgot_password_confirmation':
		
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#OTP#', $accountinfo['number'], $message);
                $message = str_replace('#COMPANY_WEBSITE#', $accountinfo['number'], $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
            break;

            case 'invoice_due_reminder':
                $subject = str_replace('#INVOICE_NUMBER#', $accountinfo['refillbalance'], $subject);
		
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#INVOICE_NUMBER#', $accountinfo['number'], $message);
                $message = str_replace('#INVOICE_DATE#', $accountinfo['password'], $message);
                $message = str_replace('#DUE_DATE#', $accountinfo['number'], $message);
                $message = str_replace('#AMOUNT#', $accountinfo['password'], $message);
                $message = str_replace('#COMPANY_WEBSITE#', $accountinfo['number'], $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
            break;

            case 'new_archive_table':
                $subject = str_replace('#TABLE_NAME#', $accountinfo['refillbalance'], $subject);
		
				$message = str_replace('#TABLE_NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
            break;

   	//case 'balance_tranfer':
	// 	$subject = str_replace('#AMOUNT#', $accountinfo['refillbalance'], $subject);
   	//  $subject = str_replace('#RECEIVER_ACCOUNT_NUMBER#', $accountinfo['number'], $subject);
	// 	$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
   	// 	$message = str_replace('#AMOUNT#', $accountinfo['refillbalance'], $message);
   	// 	$message = str_replace('#RECEIVER_ACCOUNT_NUMBER#', $accountinfo['number'], $message);
   	// 	break;
        case 'balance_transfer':
			$subject = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:'',"","",true,true), $subject);
			$subject = str_replace('#RECEIVER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:'', $subject);
			$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);

			$message = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:'',"","",true,true), $message);
			$message = str_replace('#RECEIVER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $message);

			$sms_message = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:'',"","",true,true), $sms_message);
			$sms_message = str_replace('#RECEIVER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $sms_message);

			$alert_template = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:'',"","",true,true), $alert_template);
			$alert_template = str_replace('#RECEIVER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $alert_template);
		break;
				
		case 'customer_refill_balance':
			$subject = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:'',"",$accountinfo['to_currency'],true,true), $subject);
			$subject = str_replace('#SENDER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:'', $subject);
			$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);

			$message = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:'',"",$accountinfo['to_currency'],true,true), $message);
			$message = str_replace('#SENDER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $message);

			$sms_message = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:'',"",$accountinfo['to_currency'],true,true), $sms_message);
			$sms_message = str_replace('#SENDER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $sms_message);		

			$alert_template = str_replace('#AMOUNT#',$this->CI->common_model->calculate_currency(isset($accountinfo['amount'])?$accountinfo['amount']:'',"",$accountinfo['to_currency'],true,true), $alert_template);
			$alert_template = str_replace('#SENDER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $alert_template);
		break;

			case 'product_purchase':
				// Kinjal ASTPPENT-1028 Start		
				$accountinfo['total_price_amount'] = isset($accountinfo['quantity']) && $accountinfo['quantity'] != 0 ?$accountinfo['price'] * $accountinfo['quantity'] : (isset($accountinfo['price']) ?$accountinfo['price']:'' );
				// Kinjal ASTPPENT-1028 END		
                $subject = str_replace('#NAME#', $accountinfo['first_name'], $subject);
                $subject = str_replace('#PRODUCT_NAME#', $accountinfo['name'], $subject);
				$subject = str_replace('#NUMBER#', $accountinfo['number'], $subject);
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#PRODUCT_NAME#', $accountinfo['name'], $message);
                $message = str_replace('#PRODUCT_CATEGORY#', $accountinfo['category_name'], $message);
                $message = str_replace('#PAYMENT_METHOD#', $accountinfo['payment_by'], $message);
				$message = str_replace('#PRODUCT_AMOUNT#', $accountinfo['total_price_amount'], $message);
				$message = str_replace('#QUANTITY#', $accountinfo['quantity'], $message);
				// $message = str_replace('#TOTAL_PRICE#', $accountinfo['total_price'], $message);
				// Arbaaz ASTPPCOM-1026 Start
				$message = str_replace('#TOTAL_PRICE#', $accountinfo['total_price_amount'], $message);
				// Arbaaz ASTPPCOM-1026 End 
                $message = str_replace('#NEXT_BILL_DATE#', $accountinfo['next_billing_date'], $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
            break;

    		// Ashish ASTPPCOM-743
            case 'product_release';

			$product_name = (isset($accountinfo['name'])) ? $accountinfo['name'] : (isset($accountinfo['product_name'])  ? $accountinfo['product_name'] : $product_name);

			$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
			$message = str_replace('#PRODUCT_NAME#', $product_name, $message);
			$message = str_replace('#NEXT_BILL_DATE#', isset($accountinfo['next_billing_date'])?$accountinfo['next_billing_date']:"", $message);
			$message = str_replace('#RECEIVER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $message);

			$sms_message = str_replace('#PRODUCT_NAME#',$product_name, $sms_message);
			$sms_message = str_replace("#NEXT_BILL_DATE#", isset($accountinfo['next_billing_date'])?$accountinfo['next_billing_date']:"", $sms_message);
			$sms_message = str_replace('#RECEIVER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:'', $sms_message);

			$subject = str_replace('#PRODUCT_NAME#', $product_name, $subject);
            $subject = str_replace('#NUMBER#', $accountinfo['number'], $subject);

			$subject = str_replace('#NEXT_BILL_DATE#',isset($accountinfo['next_billing_date'])?$accountinfo['next_billing_date']:"", $subject);
			$subject = str_replace('#RECEIVER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $subject);

			$alert_template = str_replace('#PRODUCT_NAME#',$product_name, $alert_template);
			$alert_template = str_replace('#NEXT_BILL_DATE#',isset($accountinfo['next_billing_date'])?$accountinfo['next_billing_date']:"", $alert_template);
			$alert_template = str_replace('#RECEIVER_ACCOUNT_NUMBER#', isset($accountinfo['number'])?$accountinfo['number']:"", $alert_template);
            break;
            // // Ashish ASTPPCOM-743 End

            case 'product_renewal_notice';

                $subject = str_replace('#PRODUCT_NAME#', $accountinfo['product_name'], $subject);
				$subject = str_replace('#NUMBER#', $accountinfo['number'], $subject);
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#PRODUCT_NAME#', $accountinfo['name'], $message);
                $message = str_replace('#NEXT_BILL_DATE#', $accountinfo['next_billing_date'], $message);
                $message = str_replace('#PRODUCT_AMOUNT#', $accountinfo['price'], $message);
				$message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);

            break;

             case 'product_commission';
				$subject = str_replace('#AMOUNT#', $accountinfo['amount'], $subject);
				$subject = str_replace('#PRODUCT_NAME#', $accountinfo['product_name'], $subject);
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
				$message = str_replace('#AMOUNT#', $accountinfo['amount'], $message);
                $message = str_replace('#PRODUCT_NAME#', $accountinfo['name'], $message);
                $message = str_replace('#BALANCE#', $accountinfo['balance'], $message);
                $message = str_replace('#COMPANY_EMAIL#', $settings_reply_email, $message);
            break;

            case 'fraud_whitelisted_login';
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#THRESHOLD#.', $accountinfo['number'], $message);
                $message = str_replace('#IP#', $accountinfo['password'], $message);
                $message = str_replace('#COMPANY_NAME#', $company_name, $message);
                break;

	     	case 'fraud_detection_notification':
				$message = str_replace('#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $message);
                $message = str_replace('#ACCOUNTCODE#', $accountinfo['number'], $message);
                $message = str_replace('#REASON#', $accountinfo['password'], $message);
                $message = str_replace('#COMPANY_NAME#', $company_name, $message);
			break;
			
			case 'email_sent_support_ticket':
				$system_config = common_model::$global_config['system_config'];
				$ticket = $system_config['ticket_digits']; 
				$useremail = strtolower($this->CI->common->get_field_name("email","accounts",array("id" => $accountinfo['customer_account_id'])));
				$cc_email_ids = isset($accountinfo['cc_email_ids']) ? $accountinfo['cc_email_ids'] : '';
				$message = str_replace("#TICKET_ID#", sprintf('%0'.$ticket.'d', $accountinfo['ticket_number']), $message);
				$message = str_replace("#REPLY_TYPE#", "Close", $message);
				$message = str_replace("#NAME#", $this->CI->common->get_field_name_coma_new('first_name,last_name,number,company_name','accounts',$accountinfo['id']),$message);
				$message = str_replace("#MESSAGE#", "" , $message);
				$subject = str_replace("#TICKET_ID#", sprintf('%0'.$ticket.'d', $accountinfo['ticket_number']), $subject);
				$subject = str_replace("#TICKET_SUBJECT#", $accountinfo['ticket_subject'],$subject);
					
			break;
		}
		if($subject == ""){
				$subject = $query[0]->subject;
				$subject = str_replace("#NAME#", $accountinfo['first_name'] . " " . $accountinfo['last_name'], $subject);
				$subject = str_replace("#COMPANY_NAME#", $company_name, $subject);	
		}
			$account_id = (isset($accountinfo['last_id']) && $accountinfo['last_id'] != "") ? $accountinfo['last_id'] : $accountinfo['id'];

			if ($type == 'schedule_report') {
				$account_id = 0;
		    }
			if ($type == 'email_sent_support_ticket') {
				$account_id = $accountinfo['customer_account_id'];
			}
        	$reseller_id = $accountinfo['reseller_id'];

      		// ASTPPCOM-924 Start
			if($query[0]->is_email_enable == '1')
			{
				$accountinfo['email']='';
				$subject='';
				$message='';
				$emailstatus = 2;
			}else{
				$emailstatus = 1;
			}
      		// ASTPPCOM-924 END
			if($query[0]->is_sms_enable == '1')
			{
				$usermobile='';
				$sms_message='';				
			}
			if($query[0]->is_alert_enable == '1')
			{
				$sip_user_name     = '';
				$callkit_token     = '';
				$alert_template    = '';
				$status_code       = '';
			}	
			if($query[0]->is_alert_enable == '0' || $query[0]->is_sms_enable == '0' || $query[0]->is_email_enable == '0')
			{		
				$last_id=$this->emailFunction($settings_reply_email, $useremail, $subject, $message,$alert_template,$usermobile,$sms_message,$company_name, $attachment, $account_id, $reseller_id,$sip_user_name,$callkit_token,$status_code,$type,$emailstatus);
				return $last_id;
			} else {
				return true;
			}
		

    }

    function emailFunction($from, $to, $subject, $message,$alert_template="",$usermobile="",$sms_message, $company_name = "", $attachment = "", $account_id, $reseller_id,$sip_user_name='',$callkit_token='',$status_code='',$type,$emailstatus = '') {

    				$sms_message = '';
					$alert_template = '';
					$usermobile = '';
					$sms_message = '';
					$sip_user_name = '';
					$alert_template = '';
					$callkit_token = '';
					$status_code = '';


					$subject = str_replace('<p>', '', $subject);
					$subject = str_replace('</p>', '', $subject);
					$subject = str_replace('<strong>', '', $subject);
					$subject = str_replace('</strong>', '', $subject);
					$subject = str_replace('&nbsp;', '', $subject);
					$subject = str_replace('<br />', '', $subject);


					
					$message = str_replace('<p>', '', $message);
					$message = str_replace('</p>', '', $message);
					$message = str_replace('<strong>', '', $message);
					$message = str_replace('</strong>', '', $message);
					$message = str_replace('&nbsp;', '', $message);
					$message = str_replace('<br />', '', $message);
					

					$sms_message = str_replace('<p>', '', $sms_message);
					$sms_message = str_replace('</p>', '', $sms_message);
					$sms_message = str_replace('<strong>', '', $sms_message);
					$sms_message = str_replace('</strong>', '', $sms_message);
					$sms_message = str_replace('&nbsp;', '', $sms_message);
					$sms_message = str_replace('<br />', '', $sms_message);


					$alert_template = str_replace('<p>', '', $alert_template);
					$alert_template = str_replace('</p>', '', $alert_template);
					$alert_template = str_replace('<strong>', '', $alert_template);
					$alert_template = str_replace('</strong>', '', $alert_template);
					$alert_template = str_replace('&nbsp;', '', $alert_template);
					$alert_template = str_replace('<br />', '', $alert_template);

					$message .= "<br>\n<br>\nASTPP - #1 Open Source VoIP Solution Powered by Inextrix Technologies Pvt. Ltd.";
					// Kinjal ASTPPCOM-1078 Start
					$message = str_replace('<br>', '', $message);
					// Kinjal ASTPPCOM-1078 END

			        $send_mail_details = array(
			        	'from'          => $from,
			            'to'            => $to,
			            'subject'       => $subject,
			            'body'          => $message,
			            'accountid'     => $account_id,
			           	'status'        => $emailstatus,
			            'attachment'    => $attachment,
			            'reseller_id'   => $reseller_id,
						'template'      => $type,
						'cc'            => $cc_email_ids,
						'to_number'     => (isset($usermobile) && $usermobile != '')?$usermobile:'',
						'sms_body'      => $sms_message,
						'sip_user_name' => $sip_user_name,
						'push_message_body' => $alert_template,
						'callkit_token'     => $callkit_token,
						'status_code'       => $status_code
			        );
        			$this->CI->db->insert('mail_details', $send_mail_details);

        			$last_id=$this->CI->db->insert_id();
					return $last_id;
    }
	// ASTPPCOM-891 Ashish start
	function convert_GMT_to($select = "", $table = "", $date, $timezone_id = '', $table_key = '') {
	// ASTPPCOM-891 Ashish end
		if ($date == '0000-00-00 00:00:00') {
			return $date;
		} else {
			// ASTPPCOM-891 Ashish start
			return $this->CI->timezone->display_GMT ( $date, 1, $timezone_id , $table_key);
			// ASTPPCOM-891 Ashish end
		}
	}
	function convert_GMT($date) { 
		return $this->CI->timezone->convert_to_GMT ($date );
	}
	function convert_GMT_to_new($select = "", $table = "", $date, $timezone_id = '') {

		if ($date == '0000-00-00 00:00:00') {
			return $date;
		} else {

			return  date("Y-m-d" , strtotime($this->CI->timezone->display_GMT ( $date, 1, $timezone_id )));
		}
	}

	// @todo : Not sure how above function working. Rather impacting on existing application, creating new function.
	function custom_convert_GMT($date, $timezone_id) {
		return $this->CI->timezone->convert_to_GMT ( $date, 1, $timezone_id );
	}

	function convert_GMT_new($date){
		return $this->CI->timezone->convert_to_GMT_new ( $date );
	}
	
	function convert_to_ucfirst($select = "", $table = "", $str_value) {
		return ucfirst ( $str_value );
	}
	function set_charge_type($status = '') {
		$status_array = array (
				'1' => gettext ( 'Accounts' ),
				'2' => gettext ( 'Rate Group' )
		);
		return $status_array;
	}
	function build_concat_string($select, $table, $id_where = '') {
		$select_params = explode ( ',', $select );
		$where = array (
				"1"
		);
		if ($id_where != '') {
			$where = array (
					"id" => $id_where
			);
		}
		$select_params = explode ( ',', $select );
		if (isset ( $select_params [3] ) && !empty($select_params [3]) ) {
			$cnt_str = " $select_params[3],'(',$select_params[2],')' ";
		}else{
			if (isset ( $select_params [2] )) {
				$cnt_str = " $select_params[0],' ',$select_params[1],' ','(',$select_params[2],')' ";
			} else {
				$cnt_str = " $select_params[0],' (',$select_params[1],')' ";
			}
		}
		$select =  $select_params [3] ;
		$drp_array = $this->CI->db_model->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		if(empty($drp_array[0]->company_name)){
			if (isset ( $select_params [2] )) {
				$cnt_str = " $select_params[0],' ',$select_params[1],' ','(',$select_params[2],')' ";
			} else {
				$cnt_str = " $select_params[0],' (',$select_params[1],')' ";
			}
		}
		$select = "concat($cnt_str) as $select_params[0] ";
		$drp_array = $this->CI->db_model->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		if (isset ( $drp_array [0] )) {
			return $drp_array [0]->{$select_params [0]};
		}
	}
	function get_invoice_total($select = '', $table = '', $id) {
		$where_arr = array (
				'invoiceid' => $id,
				'item_type <>' => "FREE"
		);
		$this->CI->db->where ( $where_arr );
		$this->CI->db->select ( '*' );
		$result = $this->CI->db->get ( 'invoice_details' );
		if ($result->num_rows () > 0) {
			$result = $result->result_array ();
			if ($select == 'debit') {
				if ($result [0] ['item_type'] == 'POSTCHARG') {
					return $this->convert_to_currency ( '', '', $result [0] ['debit'] );
				} else {
					return $this->convert_to_currency ( '', '', $result [0] ['credit'] );
				}
			} else {
				return $result [0] [$select];
			}
		} else {
			return null;
		}
	}
	function get_array($select, $table_name, $where = false) {
		$new_array = array ();
		$select_params = array ();
		$select_params = explode ( ",", $select );
		if (isset ( $select_params [3] )) {
			$cnt_str = " $select_params[1],'(',$select_params[2],' ',$select_params[3],')' ";
			$select = "concat($cnt_str) as $select_params[3] ";
			$field_name = $select_params [3];
		} elseif (isset ( $select_params [2] )) {
			$cnt_str = " $select_params[1],' ','(',$select_params[2],')' ";
			$select = "concat($cnt_str) as $select_params[2] ";
			$field_name = $select_params [2];
		} else {
			$select = $select_params [1];
			$field_name = $select_params [1];
		}
		if ($where) {
			$this->CI->db->where ( $where );
		}
		$this->CI->db->select ( "$select_params[0],$select", false );
		$result = $this->CI->db->get ( $table_name );
		foreach ( $result->result_array () as $key => $value ) {
			$new_array [$value [$select_params [0]]] = $value [$field_name];
		}
		ksort ( $new_array );
		return $new_array;
	}
	function get_timezone_offset() {
		$gmtoffset = 0;
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$account_result = $this->CI->db->get_where ( 'accounts', array (
				'id' => $accountinfo ['id']
		) );
		$account_result = $account_result->result_array ();
		$accountinfo = $account_result [0];
		$timezone_id_arr = array (
				$accountinfo ['timezone_id']
		);
		$this->CI->db->where_in ( 'id', $timezone_id_arr );
		$this->CI->db->select ( 'gmtoffset' );
		$this->CI->db->from ( 'timezone' );
		$timezone_result = $this->CI->db->get ();
		if ($timezone_result->num_rows () > 0) {

			$timezone_result = $timezone_result->result_array ();
			foreach ( $timezone_result as $data ) {
				$gmtoffset += $data ['gmtoffset'];
			}
		}
		return $gmtoffset;
	}
	function sip_profile_date() {
		$defualt_profile_data = '{"rtp-ip":"$${local_ip_v4}","dialplan":"XML","user-agent-string":"ASTPP","debug":"0","sip-trace":"no","tls":"false","inbound-reg-force-matching-username":"true","disable-transcoding":"true","all-reg-options-ping":"false","unregister-on-options-fail":"true","log-auth-failures":"true","status":"0","inbound-bypass-media":"false","inbound-proxy-media":"false","disable-transfer":"true","enable-100rel":"false","rtp-timeout-sec":"60","dtmf-duration":"2000","manual-redirect":"false","aggressive-nat-detection":"false","enable-timer":"false","minimum-session-expires":"120","session-timeout-pt":"1800","auth-calls":"true","apply-inbound-acl":"default","inbound-codec-prefs":"PCMU,PCMA,G729","outbound-codec-prefs":"PCMU,PCMA,G729","inbound-late-negotiation":"false"}';
		return $defualt_profile_data;
	}
	function set_refill_coupon_status($select = '', $table = '', $status = '') {
		$refill_coupon_array = array (
				"" => gettext ( "--Select--" ),
				'2' => gettext ( 'Yes' ),
				'0' => gettext ( 'No' )
		);
		return $refill_coupon_array;
	}
	function get_refill_coupon_status($select = '', $table = '', $status) {
		$refill_coupon_array = array (
				'0' => gettext ( 'Unused' ),
				'1' => gettext ( 'Active' ),
				'2' => gettext ( 'Used' ),
				"3" => gettext ( "Expired" )
		);
		return $refill_coupon_array [$status];
	}
	function firstused_check($select = '', $table = '', $status) {
		if ($status == '0000-00-00 00:00:00') {
			return '-';
		}
		return $status;
	}
	function get_refill_coupon_used($select = '', $table = '', $status) {
		return $status ['status'] == 2 ? '<img src= "' . base_url () . 'assets/images/true.png" style="height:20px;width:20px;" title="Yes">' : '<img src= "' . base_url () . '/assets/images/false.png" style="height:20px;width:20px;" title="No">';
	}
	function encode_params($string) {
		$data = base64_encode ( $string );
		$data = str_replace ( array (
				'+',
				'/',
				'='
		), array (
				'-',
				'$',
				''
		), $data );
		return $data;
	}

	function encode($value) {
		$ivSize = openssl_cipher_iv_length('BF-ECB');
		$iv = ($ivSize > 0) ? openssl_random_pseudo_bytes($ivSize) : false;
		$encrypted = openssl_encrypt($value, 'BF-ECB', $this->CI->config->item ( 'private_key' ), OPENSSL_RAW_DATA, $iv);
		$encrypted = $this->encode_params($encrypted);
		return  $encrypted;
	}
	function decode_params($string) {
		$data = str_replace ( array (
				'-',
				'$'
		), array (
				'+',
				'/'
		), $string );
		$mod4 = strlen ( $data ) % 4;
		if ($mod4) {
			$data .= substr ( '====', $mod4 );
		}
		return base64_decode ( $data );
	}
	function decode($value) {
		$crypttext = $this->decode_params($value);
		$ivSize = openssl_cipher_iv_length('BF-ECB');
		$iv = substr($crypttext, 0, $ivSize);
		$data = openssl_decrypt(substr($crypttext, $ivSize), 'BF-ECB', $this->CI->config->item ( 'private_key' ), OPENSSL_RAW_DATA, $iv);
		return $data;
	}
	function set_recording($status = '') {
		$status_array = array (
				'0' => 'On',
				'1' => 'Off'
		);
		return $status_array;
	}

	// Kinjal ASTPPCOM-978 Start
	function get_automated_report_status($status = '') {
		$status_array = array (
			'0' => gettext ( 'Day' )
		);
		return $status_array;
	}
	function get_automated_report_weeks($status = '') {
		$status_array = array (
			'1' => gettext ( 'Week' ),
		);
		return $status_array;
	}
	function get_automated_report_days() {
		$day_array = array (
			'monday' => gettext ( 'Monday' ),
			'tuesday' => gettext ( 'Tuesday' ),
			'wednesday' => gettext ( 'Wednesday' ),
			'thursday' => gettext ( 'Thursday' ),
			'friday' => gettext ( 'Friday' ),
			'saturday' => gettext ( 'Saturday' ),
			'sunday' => gettext ( 'Sunday' ),
		);
		return $day_array;
	}
	
	function convert_GMT_to_date($select = "", $table = "", $date, $timezone_id = '' ) {
		return $this->CI->timezone->display_GMT ( $date,2,$timezone_id);
	}
	function set_automated_report_status($select='',$table='',$status = '') {
		if($table == 'automated_reports'){
			return ($status == 0) ? "Day" : (($status == 1)  ? "Week" : (($status == 2) ? "Month" : "Biweekly" ));
		}else{
			return ($status == 0) ? "Day" : (($status == 1)  ? "Week" : "Month");
		}
	}
	function get_automatedreport_date($automated_fields){
		switch ($automated_fields['interval_frequency_on']) {
			case 1:
				$next_week = strtotime('next week');
				$add_array['execution_date'] = date("Y-m-d", strtotime($automated_fields['week_day'], $next_week));
				return $add_array['execution_date'];
			break;
		}		
	}
	function get_automated_report_module($select='',$table='',$status = '') {
 		$decoded_fields = json_decode($status,true);
		return $decoded_fields['module'];
	}
	// Kinjal ASTPPCOM-978 END
	function email_status($select = "", $table = "", $status) {
		// ASTPPCOM-924 Start
		if($status['status'] == 0){
			$status= gettext("Sent");
		}else if($status['status'] == 2){
			$status=gettext("Failed");
		}else{
			$status=gettext("Pending");
		}
		// ASTPPCOM-924 END
		return $status;
	}
	function email_search_status($select = '') {
		$status_array = array (
				"" => gettext ( "--Select--" ),
				"0" => gettext ( "Sent" ),
				"1" => gettext ( "Not Sent" ),
				"2" => gettext ( "Failed" )
		);
		return $status_array;
	}
	function paypal_status($status = '') {
		$status_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function playback_audio_notification($status = '') {
		$status_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function custom_status($status='') {
		$status_array = array (
				'0' => gettext ( 'Yes' ),
				'1' => gettext ( 'No' )
		);
		return $status_array;
	}
	function custom_status_recording($status='') {
		$status_array = array (
				'1' => gettext ( 'No' ),
				'0' => gettext ( 'Yes' )
		);
		return $status_array;
	}
	function custom_status_active($status) {
		$status_array = array (
				'0' => gettext ( 'Active' ),
				'1' => gettext ( 'InActive' )
		);
		return $status_array;
	}
	function custom_status_true($status) {
		$status_array = array (
				'0' => gettext ( 'TRUE' ),
				'1' => gettext ( 'FALSE' )
		);
		return $status_array;
	}
	function custom_status_voicemail($status) {
		$status_array = array (
				'true' => gettext ( 'True' ),
				'false' => gettext ( 'False' )
		);
		return $status_array;
	}
	function create_sipdevice($status = '') {
		$status_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function enable_signup($status = '') {
		$status_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function balance_announce($status = '') {
		$status_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function minutes_announce($status = '') {
		$status_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function enable_disable_option() {
		$option_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $option_array;
	}
	function paypal_mode($status = '') {
		$status_array = array (
				'0' => gettext ( 'Live' ),
				'1' => gettext ( 'Sandbox' )
		);
		return $status_array;
	}
	function paypal_fee($status = '') {
		$status_array = array (
				'0' => gettext ( 'Paid By Admin' ),
				'1' => gettext ( 'Paid By Customer' )
		);
		return $status_array;
	}
	function email() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function smtp() {
		return $this->set_allow ();
	}
	function debug() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function default_signup_rategroup() {
		$this->CI->db->select ( "id,name" );
		$this->CI->db->where ( "status", 0 );
		$this->CI->db->where ( "reseller_id", 0 );
		$pricelist_result = $this->CI->db->get ( "pricelists" )->result_array ();
		$pricelist_arr = array ();
		$pricelist_arr [0] = gettext("--Select--");
		foreach ( $pricelist_result as $result ) {
			$pricelist_arr [$result ['id']] = $result ['name'];
		}
		return $pricelist_arr;
	}
	function outbound_fax() {
		$status_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function inbound_fax() {
		$status_array = array (
				'0' => gettext ( 'Enable' ),
				'1' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function opensips() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function cc_ani_auth() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function calling_cards_balance_announce() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function calling_cards_timelimit_announce() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function calling_cards_rate_announce() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function startingdigit() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function SMPT() {
		$status_array = array (
				'1' => gettext ( 'Enable' ),
				'0' => gettext ( 'Disable' )
		);
		return $status_array;
	}
	function country() {
		return $this->CI->common_model->get_country_list ();
	}
	function default_timezone() {
		return $this->CI->db_model->build_dropdown_timezone ( 'id,timezone_name', 'timezone' );
	}
	function timezone() {
		return $this->CI->db_model->build_dropdown ( 'gmttime,gmttime', 'timezone' );
	}
	function base_currency() {
		return $this->CI->db_model->build_dropdown_country_camel ( 'currency,currencyname', 'currency' );
	}
	function automatic_invoice() {
		$status_array = array (
				'0' => gettext ( 'Confirmed' ),
				'1' => gettext ( 'Draft' )
		);
		return $status_array;
	}
	function calculate_currency_manually($currency_info, $amount, $show_currency_flag = true, $number_format = true) {
		$decimal_points = $currency_info ['decimalpoints'];
		$system_currency_rate = $currency_info ['base_currency'] ['currencyrate'];
		$user_currency_rate = $currency_info ['user_currency'] ['currencyrate'];
		$calculated_amount = ( float ) (($amount * $currency_info ['user_currency'] ['currencyrate']) / $currency_info ['base_currency'] ['currencyrate']);
		if ($number_format) {
			$calculated_amount = number_format ( $calculated_amount, $currency_info ['decimalpoints'] );
		}
		if ($show_currency_flag) {
			return $calculated_amount . " " . $currency_info ['user_currency'] ['currency'];
		} else {
			return $calculated_amount;
		}
	}
	function search_report_in($select = '') {
		$status_array = array (
				"minutes" => gettext("Minutes"),
				"seconds" => gettext("Seconds")
		);
		return $status_array;
	}
	function set_summarycustomer_groupby($status = '') {
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		
		$status_array = array (
				'' => gettext ( "--Select--" ),
				'accountid' => gettext ( 'Account' ),
				'pattern' => gettext ( 'Code' ),
				'package_id' => gettext ( "Package" ),
				'sip_user'=>gettext("SIP User"),
				'call_direction'=>gettext("Direction"),
		);
		if($accountinfo['type'] == -1 || $accountinfo['type'] == 2)
		{
			$trunk=array('trunk_id'=>gettext("Trunk"));
			$status_array=array_merge($status_array,$trunk);
			$calltype=array('calltype'=>gettext("Call Type"));
			$status_array=array_merge($status_array,$calltype);
		}
		return $status_array;
	}
	function set_summaryprovider_groupby($status = '') {
		$status_array = array (
				'' => gettext ( "--Select--" ),
				'provider_id' => gettext ( 'Account' ),
				'trunk_id' => gettext ( "Trunks" ),
				'pattern' => gettext ( 'Code' ),
		);
		return $status_array;
	}
	function get_currency_info() {
		$base_currency = Common_model::$global_config ['system_config'] ['base_currency'];
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$user_currency_id = $accountinfo ['currency_id'] > 0 ? $accountinfo ['currency_id'] : $base_currency;
		$where = "currency = '" . $base_currency . "' OR id= " . $user_currency_id;
		$this->CI->db->where ( $where );
		$this->CI->db->select ( '*' );
		$currency_result = $this->CI->db->get ( 'currency' );

		if ($currency_result->num_rows () == 2) {
			$currency_result = $currency_result->result_array ();
			foreach ( $currency_result as $key => $records ) {
				if ($records ['id'] == $user_currency_id) {
					$currency_info ['user_currency'] = $records;
				}
				if ($records ['currency'] == Common_model::$global_config ['system_config'] ['base_currency']) {
					$currency_info ['base_currency'] = $records;
				}
			}
		} else if ($currency_result->num_rows () == 1) {
			$currency_info ['user_currency'] = $currency_info ['base_currency'] = ( array ) $currency_result->first_row ();
		}
		$currency_info ['decimalpoints'] = Common_model::$global_config ['system_config'] ['decimalpoints'];
		return $currency_info;
	}
	function convert_to_show_in($search_name = "", $table = "", $second) {
		$search_arr = $this->CI->session->userdata ( $search_name );
		$show_seconds = (! empty ( $search_arr ['search_in'] )) ? $search_arr ['search_in'] : 'minutes';
		return ($show_seconds === 'minutes') ? ($second > 0) ? sprintf ( '%02d', $second / 60 ) . ":" . sprintf ( '%02d', ($second % 60) ) : "00:00" : sprintf ( '%02d', $second );
	}
	function array_column($input, $columnKey, $indexKey = null) {
		$array = array ();
		foreach ( $input as $value ) {
			if (! isset ( $value [$columnKey] )) {
				trigger_error ( "Key \"$columnKey\" does not exist in array" );
				return false;
			}

			if (is_null ( $indexKey )) {
				$array [] = $value [$columnKey];
			} else {
				if (! isset ( $value [$indexKey] )) {
					trigger_error ( "Key \"$indexKey\" does not exist in array" );
					return false;
				}
				if (! is_scalar ( $value [$indexKey] )) {
					trigger_error ( "Key \"$indexKey\" does not contain scalar value" );
					return false;
				}
				$array [$value [$indexKey]] = $value [$columnKey];
			}
		}

		return $array;
	}
	function group_by_time() {
		$status_array = array (
				'' => gettext ( "--Select--" ),
				'HOUR' => gettext ( 'Hour' ),
				'DAY' => gettext ( "Day" ),
				'MONTH' => gettext ( 'Month' ),
				"YEAR" => gettext ( "Year" )
		);
		return $status_array;
	}
	function currency_decimal($amount) {
		$amount = str_replace ( ',', '', $amount );	
		$decimal_amount = Common_model::$global_config ['system_config'] ['decimalpoints'];
		$number_convert = number_format ( ( float ) $amount, $decimal_amount, '.', '' );
		return $number_convert;
	}
	function commission_decimal($select,$table,$id_where='') {
		$this->CI->db->select('commission');
		$commission=(array)$this->CI->db->get_where("commission",array("id"=>$id_where))->first_row();	
		$decimal_amount = Common_model::$global_config ['system_config'] ['decimalpoints'];
		$number_convert = number_format ( ( float ) $commission['commission'], $decimal_amount, '.', '' );
		return $number_convert;
	}
	function decimal_currency($select,$table,$id_where='') {

		$amount = str_replace ( ',', '', $id_where );
		return number_format ( $amount, Common_model::$global_config ['system_config'] ['decimalpoints'] );
	}
	function add_invoice_details($account_arr, $charge_type, $amount, $description) {		
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$reseller_id = ($account_arr['reseller_id'] > 0)?$account_arr['reseller_id']:1;
		$where = "accountid IN ('" . $reseller_id . "')";
		$this->CI->db->where ( $where );
		$this->CI->db->select ( '*' );
		$this->CI->db->order_by ( 'accountid', 'desc' );
		$this->CI->db->limit ( 1 );
		$invoiceconf = $this->CI->db->get ( 'invoice_conf' );
		$invoice_conf = ( array ) $invoiceconf->first_row ();
		$last_invoiceid = $this->get_invoice_date ( 'invoiceid', '', $account_arr ['reseller_id'] );

		if ($last_invoiceid && $last_invoiceid > 0) {
			$last_invoiceid = ($last_invoiceid + 1);			
			if ($last_invoiceid < $invoice_conf ['invoice_start_from'])
					$last_invoiceid = $invoice_conf ['invoice_start_from'];	
		} else {
			$last_invoiceid = $invoice_conf ['invoice_start_from'];
		}
		$last_invoiceid = str_pad ( $last_invoiceid, 6 , '0', STR_PAD_LEFT );
		$invoice_prefix = $invoice_conf ['invoice_prefix'];
		$due_date = $invoice_conf ['interval']  > 0 ? date ( "Y-m-d H:i:s", strtotime ( gmdate ( "Y-m-d H:i:s" ) . " +" . $invoice_conf ['interval'] . " days" ) ) : gmdate ( "Y-m-d H:i:s" );

		$invoiceid = $account_arr ['posttoexternal'] == 0 ? $this->CI->common_model->generate_receipt ( $account_arr ['id'], $amount, $account_arr, $last_invoiceid, $invoice_prefix, $due_date ) : 0;

		$balance = ($account_arr ['posttoexternal'] == 1) ? $account_arr ['credit_limit'] - $account_arr ['balance'] : $account_arr ['balance'];

		$insert_arr = array (
				"accountid" => $account_arr ['id'],
				"description" => $description,
				"debit" => $amount,
				"credit" => '0',
				"created_date" => gmdate ( "Y-m-d H:i:s" ),
				"invoiceid" => $invoiceid,
				"reseller_id" => $account_arr ['reseller_id'],
				"item_type" => $charge_type,
				"item_id" => '0',
				"before_balance" => $balance,
				"after_balance" => $balance - $amount
		);
		$this->CI->db->insert ( "invoice_details", $insert_arr );
		return true;
	}
	function customer_delete_dependencies($id,$entity=0) {
		$this->delete_data ( 'ani_map', array (
				'accountid' => $id
		) );
		$this->delete_data ( 'block_patterns', array (
				'accountid' => $id
		) );
		$this->delete_data ( 'counters', array (
				'accountid' => $id
		) );
		$this->delete_data ( 'ip_map', array (
				'accountid' => $id
		) );
		$this->delete_data ( 'sip_devices', array (
				'accountid' => $id
		) );
		$this->delete_data ( 'speed_dial', array (
				'accountid' => $id
		) );
		$this->delete_data ( 'taxes_to_accounts', array (
				'accountid' => $id
		) );
		$this->delete_data ( 'mail_details', array (
				'accountid' => $id
		) );
		$this->update_data ( 'dids', array (
				"accountid" => $id
		), array (
				'accountid' => 0,
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
		) );
		$this->update_data ( "accounts", array (
				"id" => $id
		), array (
				"deleted" => 1,
				'deleted_date'=>gmdate('Y-m-d H:i:s')
		) );

		if ($entity == 3)
		{
			$this->update_data ( "trunks", array (
					"provider_id" => $id
			), array (
					"status" => 1
			) );
		}
	}
	function reseller_delete_dependencies($id) {

		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$child_arr = $this->select_data ( "accounts", array (
				"reseller_id" => $id,
				"type" => 0
		), 'id,reseller_id' );
		if ($child_arr) {
			foreach ( $child_arr as $value ) {
				$this->customer_delete_dependencies ( $value ['id'] );
			}
		}

		$acc_arr = $this->select_data ( 'accounts', array (
				"id" => $id
		), 'id,reseller_id' );
		$parent_id = 0;
		if ($acc_arr) {
			$parent_id = $acc_arr [0] ['reseller_id'];
		}
		$pricelist_arr = $this->select_data ( 'pricelists', array (
				'reseller_id' => $id
		), 'id' );
		if ($pricelist_arr) {
			foreach ( $pricelist_arr as $value ) {
				$this->delete_data ( "routing", array (
						"pricelist_id" => $value ['id']
				) );
				$this->delete_data ( "routes", array (
						"pricelist_id" => $value ['id']
				) );
				$this->update_data ( 'pricelists', array (
						'id' => $value ['id']
				), array (
						'status' => 2
				) );
			}
		}

		$this->update_data ( 'dids', array (
				'parent_id' => $id
		), array (
				"parent_id" => $parent_id
		) );
		$this->delete_data ( 'refill_coupon', array (
				"reseller_id" => $id
		) );
		$this->delete_data ( 'mail_details', array (
				'accountid' => $id
		) );
		$taxes_arr = $this->select_data ( 'taxes', array (
				"reseller_id" => $id
		), 'id' );
		if ($taxes_arr) {
			foreach ( $taxes_arr as $value ) {
				$this->delete_data ( "taxes_to_accounts", array (
						"taxes_id" => $value ['id']
				) );
			}
		}
		$this->delete_data ( 'taxes', array (
				"reseller_id" => $id
		) );
		$this->delete_data ( 'default_templates', array (
				"reseller_id" => $id
		) );
		$this->delete_data ( 'invoice_conf', array (
				'accountid' => $id
		) );
		$this->update_data ( 'accounts', array (
				"id" => $id
		), array (
				"deleted" => 1,
				'deleted_date'=>gmdate('Y-m-d H:i:s')
		) );
	}
	function subreseller_list($parent_id = '') {
		$customer_id = $parent_id;
		$this->reseller_delete_dependencies ( $parent_id );
		$query = 'select id,type from accounts where reseller_id = ' . $parent_id . ' AND deleted =0';
		$result = $this->CI->db->query ( $query );
		if ($result->num_rows () > 0) {
			$result = $result->result_array ();
			foreach ( $result as $data ) {
				if ($data ['type'] == 1) {
					$this->reseller_delete_dependencies ( $data ['id'] );
					$this->subreseller_list ( $data ['id'] );
				} else {
					$this->customer_delete_dependencies ( $data ['id'] );
				}
			}
		}
	}

	function delete_data($table_name, $where_arr) {
		$this->CI->db->where ( $where_arr );
		if ($table_name == 'accounts') {
			$this->CI->db->where ( 'type <>', '-1' );
		}
		$this->CI->db->delete ( $table_name );
	}


	function update_data($table_name, $where_arr, $update_arr) {
		$this->CI->db->where ( $where_arr );
		if ($table_name == 'accounts') {
			$this->CI->db->where ( 'type <>', '-1' );
		}
		$this->CI->db->update ( $table_name, $update_arr );
	}

	function select_data($table_name, $where_arr, $select) {
		$this->CI->db->where ( $where_arr );
		$this->CI->db->select ( $select );
		$result = $this->CI->db->get ( $table_name );
		if ($result->num_rows () > 0) {
			return $result->result_array ();
		} else {
			return false;
		}
	}
	function set_call_waiting($status = '') {
		$status_array = array (
				'0' => 'Enable',
				'1' => 'Disable'
		);
		return $status_array;
	}
	function get_call_waiting($select = "", $table = "", $status) {
		return ($status == 0) ? "Enable" : "Disable";
	}
	function set_invoice_details($select = '') {
		$status_array = array (
				"invoice_select" => gettext ( "--Select--" ),
				"invoice_inactive" => gettext ( "Deleted Invoices" ),
				"invoice_active" => gettext ( "All Invoices" )
		);
		return $status_array;
	}
	function reseller_select_value($select, $table, $id_where = '') {
		$select_params = explode ( ',', $select );
		
		if ($id_where != '') {
			$where = array (
					"id" => $id_where
			);
		}
		$select_params = explode ( ',', $select );
		if(isset($select_params[3]) && $select_params[3] != ''){
			$cnt_str = " $select_params[3],' ','(',$select_params[2],')' ";
		}
		else{
			$cnt_str = " $select_params[0],' ',$select_params[1],' ','(',$select_params[2],')' ";
		}
		$select = $select_params[3];
		$drp_array = $this->CI->db_model->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		if(empty($drp_array[0]->company_name)){
			$cnt_str = " $select_params[0],' ',$select_params[1],' ','(',$select_params[2],')' ";
		}
		$select = "concat($cnt_str) as $select_params[2] ";
		$drp_array = $this->CI->db_model->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		if (isset ( $drp_array [0] ))
			return $drp_array [0]->{$select_params [2]};
		else
			return 'Admin';
	}
	function get_subreseller_info($parent_id) {

		if (! empty ( $parent_id )) {
			$str = $parent_id . ",";
		} else {
			$str = null;
		}
		$query = "select id from accounts where reseller_id = '$parent_id' AND deleted =0 AND type=1";
		$result = $this->CI->db->query ( $query );
		if ($result->num_rows () > 0) {
			$result = $result->result_array ();
			foreach ( $result as $data ) {
				if (! empty ( $data ['id'] )) {
					$str .= $this->get_subreseller_info ( $data ['id'] );
				}
			}
		}
		return $str;
	}
	function get_parent_info($child_id, $parent_id) {
		if (! empty ( $child_id )) {
			$str = $child_id . ",";
		} else {
			$str = null;
		}
		if ($child_id > 0) {
			$query = "select reseller_id from accounts where id = '$child_id'";
			$result = $this->CI->db->query ( $query );
			if ($result->num_rows () > 0) {
				$parent_info = ( array ) $result->first_row ();
				if ($parent_info ['reseller_id'] != $parent_id) {
					$str .= $this->get_parent_info ( $parent_info ['reseller_id'], $parent_id );
				}
			}
		}
		return $str;
	}
	function get_did_accountid($select, $table, $where) {
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$this->CI->db->where ( 'note', $where );
		$this->CI->db->where ( 'parent_id', $accountinfo ['id'] );
		$this->CI->db->select ( 'reseller_id' );
		$reseller_pricing_result = $this->CI->db->get ( 'reseller_pricing' );
		$account_info = ( array ) $reseller_pricing_result->first_row ();
		$account_name = $this->get_field_name_coma_new ( 'first_name,last_name,number', 'accounts', $account_info ['reseller_id'] );
		return $account_name;
	}
	function get_status_new($select = "", $table = "", $status = "") {
		return ($status ['status'] == 0) ? "<span class='label label-sm label-inverse arrowed-in' title='release'>Active<span>" : "<span class='label label-sm' title='release'>Inactive<span>";
	}
	function Get_Invoice_configuration($AccountData) {
	    	    
		$InvoiceConf = array ();
		$reseller_id = ($AccountData ['reseller_id'] == 0) ? 1 : $AccountData ['reseller_id'];
		$where = "accountid IN ('" . $reseller_id . "','1')";
		$this->CI->db->select ( '*' );
		$this->CI->db->where ( $where );
		$this->CI->db->order_by ( 'accountid', 'desc' );
		$this->CI->db->limit ( 1 );
		$InvoiceConf = $this->CI->db->get ( 'invoice_conf' );
		$InvoiceConf = ( array ) $InvoiceConf->first_row ();

		return $InvoiceConf;
	}
	function csv_to_array($filename = '', $delimiter = ',') {
		if (! file_exists ( $filename ) || ! is_readable ( $filename ))
			return FALSE;
		$header = NULL;
		$data = array ();
		if (($handle = fopen ( $filename, 'r' )) !== FALSE) {
			while ( ($row = fgetcsv ( $handle, 1000, $delimiter )) !== FALSE ) {
				
				if (! $header)
					$header = $row;
				else{
					if(!empty($row)){
						$data [] = array_combine ( $header, $row );
					}	
				}	
			}
			
			fclose ( $handle );
		}
		
		return $data;
	}
	function utf8_converter($array) {
		array_walk_recursive ( $array, function (&$item, $key) {
			if (! mb_detect_encoding ( $item, 'utf-8', true )) {
				$item = utf8_encode ( $item );
			}
		} );
		return $array;
	}
	
	function get_value_by_option($value,$default_value){
		$options = array("Yes"=>0,"No"=>1);
		return isset($options[$value]) ?  ($options[$value]) : $options[$default_value];
	}
	function get_values(){
		return array("Yes"=>0,"No"=>1);
	}
	function get_account_type_value($value){
		return $value =='Postpaid' ? '1' : '0';
	}
	function get_sipinfo_array(){
		return array("Random"=>"Random");
	}
    function default_system_type() {
        $option_array = array('0' => gettext('Half Year'), '1' => gettext('Year'));
        return $option_array;
    }
    function set_year_dropdown($type = '') {
	if($type != ''){
		$type = $type."_archive";
	}
        $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() and table_name like '".$type."_%'";
        $status_array = array("" => gettext("--Select Type--"),
                "0" => gettext("Main"),
        );
        $result=$this->CI->db->query($query);
        $result=$result->result_array();
        foreach($result as $key => $value){
                $get_year = str_replace($type.'_','',$value['TABLE_NAME']);
                $get_year = str_replace('_1','_jan_to_jun',$get_year);
                $get_year = str_replace('_2','_jul_to_dec',$get_year);
                $status_array[$value['TABLE_NAME']] = $get_year;
        }
        return $status_array;
    }
    function get_did_destination($select = "", $table = "", $id){ 
		$name='';
		if($this->CI->db->table_exists('pbx_ringgroup') && $this->CI->db->table_exists('tbl_conference_specification') && $this->CI->db->table_exists('tbl_ivr_specification')) {
			$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
			$did_arr = array();
			$this->CI->db->select ( '*' );
			$this->CI->db->where ( 'id', $id );

			$table_name = ($accountinfo['type'] == 1)?'reseller_pricing':'dids';
			$did_arr = $this->CI->db->get ($table_name );
			$did_arr = ( array ) $did_arr->first_row ();
			$extension = $did_arr['extensions'];
			$call_type = $did_arr['call_type'];
			if($call_type == '6'){
					$name = $this->get_field_name ( "name", "pbx_ringgroup", array (
						"id" => $extension 
				) );
			}elseif($call_type == '7'){
					$name = $this->get_field_name ("name", "tbl_conference_specification", array (
						"id" => $extension 
			        ));
			}elseif($call_type == '8'){
					$name = $this->get_field_name ("name", "tbl_ivr_specification", array (
						"id" => $extension 
			        ));
			}
			else{
				 $name = $did_arr['extensions'];
		    }
		 }
		 return $name;	
	}   
	function default_password_input($status = '') {
		$password_array = array (
				'0' => gettext ( 'Strong' ),
				'1' => gettext ( 'Moderate' )
		);
		return $password_array;
	}  
	function sms_rategroup() {
		$accountinfo = $this->CI->session->userdata("accountinfo");
		$reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] ==5 ?  $accountinfo['id']: 0 ;
        	return $this->CI->db_model->build_dropdown('id,name', 'sms_pricelists',array("status"=>0,"reseller_id"=>$reseller_id));
    	}
    	function pin_generate($size = '', $field = '', $tablename = ''){
		if ($tablename != '') {
			$accounttype_array = array ();
			$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
			$where = array (
					$field => $uname
			);
			$acc_result = $this->CI->db_model->getSelect ( 'Count(*) as count', $tablename, $where );
			$acc_result = $acc_result->result ();
			while ( $acc_result [0]->count != 0 ) {
				$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
				$acc_result = $this->CI->db_model->getSelect ( 'Count(*) as count', $tablename, $where );
			}
		} else {
			$uname = rand ( pow ( 10, $size - 1 ), pow ( 10, $size ) - 1 );
		}
		$start_prifix_value = common_model::$global_config ['system_config'] ['startingdigit'];
		if ($tablename == 'accounts' && $start_prifix_value != 0) {
			$length = strlen ( $start_prifix_value );
			$uname = substr ( $uname, $length );
			$uname = $start_prifix_value . $uname;
		}
		return $uname;
	}
	
	function get_reseller_info(){
			 
			 $reseller_info    = $this->CI->db_model->getSelect("*","accounts",array("type"=>1,"deleted"=>0,"status"=>0));
			 if($reseller_info->num_rows > 0) {
			    	$reseller_data = $reseller_info->result_array();
			 
				 foreach($reseller_data as $k =>$reseller) {
					$drp_value[0]               ="Admin";
										if(isset($reseller['company_name']) && $reseller['company_name'] != ''){
						$drp_value[$reseller['id']] = $reseller['company_name'].' '.'('.$reseller['number'].')';
					}else{
						$drp_value[$reseller['id']] = $reseller['first_name'].' '.$reseller['last_name'].'('.$reseller['number'].')';
					}
				 }	
			} else {
					$drp_value[0]="Admin";
			}
				return $drp_value;
		}


	function get_reseller_info_company_profile(){
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$logintype = $this->CI->session->userdata('logintype');
		if($logintype == 1)
		{
			$drp_value[$accountinfo ['id']]=$accountinfo['first_name'].' '.$accountinfo['last_name'].'('.$accountinfo['number'].')';
		}else if($logintype==2){
			$where=array("type"=>1,"deleted"=>0);
			$drp_value[0]="Admin";
			$reseller_info    = $this->CI->db_model->getSelect("*","accounts",$where);
			if($reseller_info->num_rows > 0) {
				$reseller_data = $reseller_info->result_array();
				foreach($reseller_data as $k =>$reseller) {
					$drp_value[$reseller['id']] = $reseller['first_name'].' '.$reseller['last_name'].'('.$reseller['number'].')';
				}	
			}
		}
		
		return $drp_value;
	}
	 function tax_type(){
		$this->CI->db->select ( "id,taxes_description" );
		$this->CI->db->where ( "status", 0 );
		$this->CI->db->where ( "tax_type", 0 );
		$tax_type_result = $this->CI->db->get ( "taxes" )->result_array ();
		$tax_type_dropdown = array ();
		$tax_type_dropdown [0] = gettext("--Select--");
		foreach ( $tax_type_result as $result ) {
			$tax_type_dropdown [$result ['id']] = $result ['taxes_description'];
		}
		return $tax_type_dropdown;
	}
	function set_tax_type($status = '') {
		$status_array = array (
				'0' => gettext ( 'Default' ),
				'1' => gettext ( 'Other' )
		);
		return $status_array;
	}
	function get_tax_type($select = "", $table = "", $status) {
		return ($status == 0) ? "Default" : "Other";
	}
	function set_tax_search_type($select = '') {
		$status_array = array (
				"" => gettext ( "--Select--" ),
				"0" => gettext ( "Default" ),
				"1" => gettext ( "Other" )
		);
		return $status_array;
	}
	function set_cron_type($cron = "") {
		
		$cron_type_array = array (
				"" => gettext ( "--Select--" ),
				'minutes' => gettext ( 'Minute' ),
				'hours' => gettext ( 'Hour' ),
				'days' => gettext ( 'Day' ),
				'months' => gettext ( 'Month' ),
				'years' => gettext ( 'Year' )
		);
		return $cron_type_array;
	}
	
	function get_cron_type($select = "", $table = "", $cron_type) {
		$cron_type_array = array (
				"" => gettext ( "--Select--" ),
				'minutes' => gettext ( 'Minute' ),
				'hours' => gettext ( 'Hour' ),
				'days' => gettext ( 'Day' ),
				'months' => gettext ( 'Month' ),
				'years' => gettext ( 'Year' )
		);
		
		return $cron_type_array[$cron_type];

	}
	
	function set_search_cron_type($cron = "") {
		$cron_type_array = array (
				"" => gettext ( "--Select--" ),
				'minutes' => gettext ( 'Minute' ),
				'hours' => gettext ( 'Hour' ),
				'days' => gettext ( 'Day' ),
				'months' => gettext ( 'Month' ),
				'years' => gettext ( 'Year' )
		);
		
		return $cron_type_array;
	}
	function renewal_type_category($status) {
		$status_array = array (
				'' => gettext ( '--Select--' ),
				'0' => gettext ( 'One Time' ),
				'1' => gettext ( 'Recurring' ),
		);
		return $status_array;
	}
	function get_renewal_type_category_list($status = '', $table = "", $category) {
		
		$plans_category = array (
				'0' => gettext ( 'One Time' ),
				'1' => gettext ( 'Recurring' ),
		);
		return $plans_category[$category];
	}
	function get_renewal_type_category($status = '', $table = "", $id) {
		$renewal_data=$this->CI->db_model->getSelect('renew_count,renewal_type',$table,array('id'=>$id));
		$renewaldata=$renewal_data->result_array();
		
		$renew_count=$renewaldata[0]['renew_count'];
		$renewal_type=$renewaldata[0]['renewal_type'];
		$plans_category = array (
		'0' => gettext ( 'One Time' ),
		'1' => gettext ( 'Recurring' ),
		);
		
		return $plans_category[$renewal_type].'('.$renew_count.')';
	}
	function get_available_seconds_for_plans($plan_id = '', $tablename = "", $plan_id2 = ""){ 
		
		$plan_result = $this->CI->db_model->getSelect ( '*', "counters",array('id' => $plan_id2) );
		$plan_result = $plan_result->result_array ();
		$plan_result = $plan_result[0];
		$used_seconds = $plan_result['seconds'];
		$included_seconds = $this->get_field_name ( "included_seconds", "plans", array (
					"id" => $plan_result['plans_id'] 
			) );
		$available_seconds = $included_seconds - $used_seconds;
		return  $available_seconds;	
	 }
	function get_type($select = "", $table = "", $type){
		$get_type_array = array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'Black List' ),
				'1' => gettext ( 'White List' )
		);
		
		return $get_type_array[$type];	
	}
	
	function set_type($type){
		$set_type_array = array (
				'0' => gettext ( 'Black List' ),
				'1' => gettext ( 'White List' )
		);
		
		return $set_type_array;	
	}
	
	function set_search_type($type){
		$set_search_type_array = array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'Black List' ),
				'1' => gettext ( 'White List' )
		);
		
		return $set_search_type_array;	
	}
	
	
	function get_number_type($select = "", $table = "", $number_type){
		$get_number_type = array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'CLI' ),
				'1' => gettext ( 'Destination' )
		);
		
		return $get_number_type[$number_type];	
	}
	
	function set_number_type($number_type){
		$set_number_type = array (
				'0' => gettext ( 'CLI' ),
				'1' => gettext ( 'Destination' )
		);
		
		return $set_number_type;	
	}
	
	
	function set_search_number_type($number_type){
		$set_number_type = array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'CLI' ),
				'1' => gettext ( 'Destination' )
		);
		
		return $set_number_type;	
	}
	
	function set_action_type($action_type){
		$set_action_type = array (
				'0' => gettext ( 'Allow' ),
				'1' => gettext ( 'Reject' )
		);
		return $set_action_type;		
	}
	
	function get_action_type($select = "", $table = "", $action_type){
		$get_action_type= array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'Allow' ),
				'1' => gettext ( 'Reject' )
		);
		
		return $get_action_type[$action_type];		
	}
	function set_search_action_type($action_type){
		$set_search_action_type = array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'Allow' ),
				'1' => gettext ( 'Reject' )
		);
		return $set_search_action_type;		
	}
	function set_destination_type($destination_type){
		$set_destination_type = array (
				'0' => gettext ( 'Inbound' ),
				'1' => gettext ( 'Outbound' ),
				'2' => gettext ( 'Both' ),
		);
		return $set_destination_type;		
	}
	function get_destination_type($select = "", $table = "",$destination_type){
		$get_destination_type = array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'Inbound' ),
				'1' => gettext ( 'Outbound' ),
				'2' => gettext ( 'Both' ),
		);
		return $get_destination_type[$destination_type];		
	}
	function set_search_destination_type($destination_type){
		$set_search_destination_type = array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'Inbound' ),
				'1' => gettext ( 'Outbound' ),
				'2' => gettext ( 'Both' ),
		);
		return $set_search_destination_type;		
	}
	function varification_with($status = '') {
		$status_array = array (
				'0' => gettext ( 'OTP' ),
				'1' => gettext ( 'Email' )
		);
		return $status_array;
	}
	function ewallet_payment_gateway(){
		$paypal_array = array (
				'paypal' => gettext ( 'PAYPAL' )
		);
		return $paypal_array;
	}
	function card_payment_gateway(){
		$paypal_array = array (
				'0' => gettext ( '--Select--' ),
				'1' => gettext ('AUTHORIZED.NET')
		);
		return $paypal_array;
	}
	function authorize_mode($status = '') {
		$status_array = array (
				'0' => gettext ( 'Live' ),
				'1' => gettext ( 'Sandbox' )
		);
		return $status_array;
	}
	function optin_status($select = "", $table = "", $status) {
		$link =  base_url () ."products/products_edit_reseller_optinproduct/" . $status;
		return  '<a href="'.$link.'" class="btn btn-royelblue btn-sm animap_image" rel="facebox" title="Optin" onclick="return javascript:optinproduct(' . $status  . ')"><i class="fa fa-reorder fa-fw"></i></a>';
				
	}
	
	function check_did_available_reseller($select='', $table='', $number) 
	{
		//ASTPPCOM - 1342 start
		$did_id =(array)$this->CI->db->get_where("dids",array("number"=>$number))->first_row();
        $link =  base_url () ."did/did_available_purchase/" .$did_id['id'];
        //ASTPPCOM - 1342 end
		return  '<a href="'.$link.'" <span  class="label label-sm label-inverse_blue arrowed_blue-in" rel="facebox" title="release">Purchase<span></a>';
	}
	function build_did_forward($select='', $table='', $id) {
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		if($accountinfo['type'] == 0 || $accountinfo['type'] == 3  ){
			$link =  base_url () ."user/user_did_forward/" . $id;
		}else{
			$link =  base_url () ."did/did_forward/" . $id;
		}
		
		    return '<a href="' . $link . '" class="btn btn-royelblue btn-sm" rel="facebox" title="Did Forward"><i class="fa fa-mail-forward"></i></a>&nbsp;';

       }
	function payment_status($status = '') {
		$status_array = array (
				''=>gettext('--Select--'),
				'PAID' => gettext ( 'Paid' ),
				'PENDING' => gettext ( 'PENDING' ),
				'FAIL' => gettext ( 'FAIL' )
		);
		return $status_array;
	}

	 function order_date($select="",$table= "",$date= ""){ 
		$date = $this->convert_GMT_to($select = "", $table = "", $date, $timezone_id = '');
		$converted_date = date("Y-m-d",strtotime($date));
		return $converted_date;
	}
	
	function get_did_billing_type($select="",$table= "",$product_id= ""){
		$billing_type = $this->get_field_name("billing_type","products",array("id"=>$product_id));
		return $billing_type = ($billing_type == 0) ?"One Time" :"Reccuring";
	}

	function get_localization($select = "", $table = "", $type = '') {

			if ($type == '0') { 
				$status_array = 'Global';
			} else {
				$status_array = 'Manually';
			}
		return $status_array;

	}
	function get_order_id($select='', $table='', $where) {
		if (is_array ( $where )) {
			$where = $where;
		} else {
			$where = array (
					"id" => $where
			);
		}
		$field_name = $this->CI->db_model->getSelect ( $select, $table, $where );
		$field_name = $field_name->result ();
		if (isset ( $field_name ) && ! empty ( $field_name )) {
			return "#".$field_name [0]->{$select};
		} else {
			return "";
		}
	}
	
	function payment_status_icon($select = "", $table = "", $commission_status) {
		$return_value = '';
		$where = array (
				'commission_status' => $commission_status
		);
		$commission_status = ( array ) $this->CI->db->get_where ( "commission", $where )->first_row ();
		if ($commission_status ['commission_status'] == "PAID") {
			$return_value = " <span title='PAID'> </span>" . '<span class="badge badge-success float-center mt-1" title="PAID">'.gettext(ucwords(strtolower("PAID"))).'</span>';
		}
		if ($commission_status ['commission_status'] == "PENDING") {
			$return_value = " <span title='PENDING'> </span>" . '<span class="badge badge-primary float-center mt-1" title="PENDING">'.gettext(ucwords(strtolower("PENDING"))).'</span>';
		}
		
		return $return_value;
	}
	
	function set_cli_pool($status=''){
		$status_array = array(
			'0' => 'Disable',
			'1' => 'DID (CLI Match, If not matched then use random allocated DID)',
			'2' => 'Caller Id (CLI Match, If not matched then use random allocated Caller Id)',
			'3' => 'Use NON-CLI Rate Group (If CLI not match with DID)',
			'4' => 'Use NON-CLI Rate Group (If CLI not match with Caller ID)',
			'5' => 'Use NON-CLI Rate Group (If CLI not match with DID & Caller id)',
			'6' => 'Reject Call (If CLI not match with DID)',
			'7' => 'Reject Call (If CLI not match with Caller ID)',
			'8' => 'Reject Call (If CLI not match with DID & Caller id)',
		);
		return $status_array;
	}
	function get_cli_pool($select = "", $table = "",$status){
		$status_array = array(
			'0' => 'Disable',
			'1' => 'DID (CLI Match, If not matched then use random allocated DID)',
			'2' => 'Caller Id (CLI Match, If not matched then use random allocated Caller Id)',
			'3' => 'Use NON-CLI Rate Group (If CLI not match with DID)',
			'4' => 'Use NON-CLI Rate Group (If CLI not match with Caller ID)',
			'5' => 'Use NON-CLI Rate Group (If CLI not match with DID & Caller id)',
			'6' => 'Reject Call (If CLI not match with DID)',
			'7' => 'Reject Call (If CLI not match with Caller ID)',
			'8' => 'Reject Call (If CLI not match with DID & Caller id)',
		);
		return $status_array[$status];
	}
//ekta	
	function set_product_groupby($status = '') {
		$status_array = array (
				'' => gettext ( "--Select--" ),
				'product_id' => gettext ( 'Product' ),
				'accountid' => gettext ( "Account" )
		);
		return $status_array;
	}
	function group_by_time_for_product() {
		$status_array = array (
				'' => gettext ( "--Select--" ),
				'DAY' => gettext ( "Day" ),
				'MONTH' => gettext ( 'Month' ),
				"YEAR" => gettext ( "Year" )
		);
		return $status_array;
	}
//HP: Permission changes 25-Jan-2019
	function permission_info(){
		$permission_array = array();
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$this->CI->db->where ( 'id', $accountinfo['permission_id'] );
		$this->CI->db->select ( 'login_type' );
		$permission_info = ( array ) $this->CI->db->get ( 'permissions' )->first_row ();
		if(!empty($permission_info)){
			$roles_and_permission_array = $this->CI->db_model->getSelect("*", "roles_and_permission", array('login_type'=>$permission_info['login_type'],'status'=>0))->result_array();
			if(!empty($roles_and_permission_array)){
				foreach($roles_and_permission_array as $key => $value){
					$permission_array[$value['module_name']][$value['module_url']] = json_decode($value['permissions'],true);
				}
			}
		}
		$permission_array['login_type'] = $accountinfo['type'];
		return $permission_array;
	}
	function permission_login_type($select = "", $table = "", $status) {
		return ($status == 0) ? "Admin" : "Reseller";
	}
	function set_search_permission_login_type($select = '') {
		$status_array = array (
				"" => gettext ( "--Select--" ),
				"0" => gettext ( "Admin" ),
				"1" => gettext ( "Reseller" )
		);
		return $status_array;
	}
	function menu_permission_info(){
		$menu_name_array = array();
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		
		$this->CI->db->where ( 'id', $accountinfo['permission_id'] );
		$this->CI->db->select ( 'permissions' );
		
		$permission_info = ( array ) $this->CI->db->get ( 'permissions' )->first_row ();
		
		if(!empty($permission_info)){
		$permission_array = json_decode($permission_info['permissions'],true);
		if(!empty($permission_array)){
			foreach($permission_array as $key => $value){
				foreach($value as $sub_key => $sub_value){
					$menu_name = $this->get_field_name('menu_name','roles_and_permission',array('module_url'=>$sub_key));
					if($menu_name != ''){
						$menu_name = ($menu_name == 'call_Reports')?'reports':$menu_name;
						$menu_name_array[] = str_replace(' ','_',strtolower($menu_name));
					}

				}
			}
		}
	      }
		return $menu_name_array;
	}
	function sub_module_permission_info(){
		$sub_module_name_array = array();
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$this->CI->db->where ( 'id', $accountinfo['permission_id'] );
		$this->CI->db->select ( 'permissions' );
		$permission_info = ( array ) $this->CI->db->get ( 'permissions' )->first_row ();
		
		if(isset($permission_info) && (!empty($permission_info)) ){  
			$permission_array = json_decode($permission_info['permissions'],true);
			if(is_array($permission_array)){
				foreach($permission_array as $key => $value){
					foreach($value as $sub_key => $sub_value){
						$sub_module_name = $this->get_field_name('sub_module_name','roles_and_permission',array('module_url'=>$sub_key));
						if($sub_module_name != ''){
							$sub_module_name_array[] = str_replace(' ','_',strtolower($sub_module_name));
						}

					}
				}
			}
		}
		return $sub_module_name_array;
	}

	function set_transaction_type($select,$table,$where){

		$transaction_type = json_decode($where,true);
	        if($transaction_type ['payment_status'] == "PAID") {
			$set_transaction_type = " <span title='Edit'>" . " </span>" . '<span class="badge badge-success" title="Admin">SUCESS</span>';
		}
		if($transaction_type ['payment_status'] == "PENDING") {
			$set_transaction_type = " <span title='Edit'>" . " </span>" . '<span class="badge badge-warning" title="Admin">PENDING</span>';
		}
		if($transaction_type ['payment_status'] == "UNPAID") {
			$set_transaction_type = " <span title='Edit'>" . " </span>" . '<span class="badge badge-danger" title="Admin">FAIL</span>';
		}
		return $set_transaction_type;
	}
	
	function set_search_localization_type($action_type){
		$set_search_localization_type_type = array (
				"" => gettext ( "--Select--" ),
				'0' => gettext ( 'Global' ),
				'1' => gettext ( 'Manually' )
		);
		return $set_search_localization_type_type;		
	}
	function convert_to_currency_cdrs_debit($select = "", $table = "", $uniqueid) {
		$query = $this->CI->db->get_where ( "cdrs", array("uniqueid" => $uniqueid));
		$data=$query->row_array();
		$this->CI->load->model ( 'common_model' );
		if(isset($data['calltype']) && $data['calltype'] == 'FREE')
		{
			return $this->CI->common_model->calculate_currency ( '0', '', '', true, false );
		}else{
			return $this->CI->common_model->calculate_currency ( $data['debit'], '', '', true, false );
		}
		
	}
	function direction_search_type($select = '') {
		$direction_array = array (
				"" => gettext ( "--Select--" ),
				"inbound" => gettext ( "Inbound" ),
				"outbound" => gettext ( "Outbound" )
		);
		return $direction_array;
	}
	function get_price_orders($select = "", $table = "", $orderid){
		$price = $this->get_field_name("price","order_items",array("order_id"=>$orderid));
		$setup_fee = $this->get_field_name("setup_fee","order_items",array("order_id"=>$orderid));
		$amount = $price+$setup_fee;
		return $this->CI->common_model->calculate_currency ( $amount, '', '', true, false );
	}
	function check_recording_exist($select = "", $table = "" ,$uid){
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );

		$cdrs_result = $this->CI->db_model->getSelect ( 'calltype,call_direction,billseconds', "cdrs", array("uniqueid"=>$uid) );
		$cdrs_result = (array)$cdrs_result->first_row ();
		$calltype = $cdrs_result['calltype'];
		$call_direction = $cdrs_result['call_direction'];
		$billseconds = $cdrs_result['billseconds'];
		if($calltype == 'LOCAL' && $call_direction == 'inbound')
		{
			$uid=rtrim($uid,'LOCAL_'.$accountinfo['id']);
		}
		if(file_exists($this->CI->config->item('recordings_path').$uid.".wav") && $calltype != 'FAX'){	
			$url =base_url()."user/user_report_recording_download/".$uid.".wav";
			$play_img_url =base_url()."assets/images/play_file.png";
			$pause_img_url =base_url()."assets/images/pause.png";
			$action = '<audio id="myAudio_'.$uid.'">
			<source src="'.$url.'" type="audio/mpeg">
			Your browser does not support the audio element.
			</audio>';
			$action .= "<button onclick='playAudio(\"$uid\",\"$billseconds\")' type='button' class='btnplay'  id='play_".$uid."'  style='display:block;margin:0px 0 0 25px;border:0px !important; float:left; padding:0px'><img src=".$play_img_url." height='25px' width='25px' style='cursor: pointer;'/></button>";

			$action .= "<button onclick='pauseAudio(\"$uid\")' type='button'  class='btnplay' id='pause_".$uid."' style='display: none;margin:0px 0 0 25px;border:0px !important; float:left;padding:0px'><img src=".$pause_img_url." height='25px' width='25px' style='cursor: pointer;'/></button>";

			$recording = ($accountinfo['is_recording'] == 0) ? '<a title="Recording file" href="'.$url.'"><img src="' . base_url() . 'assets/images/download.png" height="20px" width="20px"/></a>' : '<img src="' . base_url() . 'assets/images/false.png" height="20px" alt="file not found" width="20px"/>';
					
		}else{
			$recording='<img src="' . base_url() . 'assets/images/false.png" height="20px" title="Record file is not available" width="20px"/>';
			$action = '<img src="' . base_url() . 'assets/images/false.png" height="20px" title="Play file is not available" width="20px"/>';
		}
		 return $recording."  ".$action;
	}
	function getContents($str, $startDelimiter, $endDelimiter) {
		$static_templete_words=array(
			'PRODUCT_NAME'=>'Product Name',
			'NUMBER'=>'Account Number',
			'NAME'=>'Combination of  Firstname + Lastname',
			'NEXT_BILL_DATE'=>'What will be the next billing date.',
			'PRODUCT_AMOUNT'=>'It will indicate Amount of the product.',
			'COMPANY_EMAIL'=>"It will replaced with Company's Email Address.",
			'COMPANY_NAME'=>"It will replaced with Company's Name.",
			'TIME'=>"It will replaced with Configurable Minutes ",
			'QUANTITY'=>"It will indicate no of Quantity of product.",
			'TOTAL_PRICE'=>"It will indicate total price of product.",
			'FIRST_NAME'=>'It will indicate first name.',
			'TABLE_NAME'=>'It will indicate Table name.',
			'AMOUNT'=>'It will indicate Amount.',
			'BALANCE'=>'It will indicate Balance',
			'PRODUCT_CATEGORY'=>'It will indicate type of product.',
			'PAYMENT_METHOD'=>'It will indicate type of payment.',
			'RECEIVER_ACCOUNT_NUMBER'=>"It will indicate receiver's account number",
			'INVOICE_NUMBER'=>'It will indicate Invoice Number',
			'INVOICE_DATE'=>'It will indicate Invoice Date',
			'DUE_DATE'=>'It will indicate Next Payment Due date.',
			'OTP'=>'It will indicate one time password.',
			'COMPANY_WEBSITE'=>"It will replaced with Company's Website.",
			'PASSWORD'=>'It will indicate password.',
			'USERNAME'=>'It will indicate User Name.',
			'REFILLBALANCE'=>'It will indicate amount of credite in account.',
			'USER'=>'It will indicate Name of account person',

		);

		$contents = array();
		$startDelimiterLength = strlen($startDelimiter);
		$endDelimiterLength = strlen($endDelimiter);
		$startFrom = $contentStart = $contentEnd = 0;
		while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
			$contentStart += $startDelimiterLength;
			$contentEnd = strpos($str, $endDelimiter, $contentStart);
			if (false === $contentEnd) {
				break;
			}
			$temp = substr($str, $contentStart, $contentEnd - $contentStart);
			$contents[$temp] = isset($static_templete_words[$temp]) ? $static_templete_words[$temp] :'';
			$startFrom = $contentEnd + $endDelimiterLength;
		}
	
		return $contents;
	}
	//HP: PBX_ADDON
	function get_call_type_grid($select = "", $table = "" ,$did_id="") {
		$query = (array)$this->CI->db_model->getSelect("call_type,extensions", "dids", array("id"=>$did_id))->first_row();
		$call_type = $query['call_type'];
		$extensions = $query['extensions'];
		if($call_type > 6){
			$this->CI->load->library('astpp/pbx_feature');
			$destination_name = $this->CI->pbx_feature->pbx_destination_name($call_type,$extensions);
			return $destination_name;	
		}
		else{
			return $extensions;
		}
	}
	//END
	//HP: PBX_ADDON
	function sip_dropdown($id,$accountid,$value){
  		$drop_down = "";
		$drop_down .= '<select name="'.$id.'" id="'.$id.'" class="form-control float-left col-md-12 form-control-lg selectpicker '.$id.'" data-live-search="true">';
		$drop_down .=	'<option value="0">-- Select --</option>';
		$query = $this->CI->db_model->getSelect("*", "sip_devices",array('accountid' => $accountid, 'status' => 0)); 
		if ($query->num_rows() > 0){
			$sip_devices = $query->result_array();
			foreach($sip_devices as $key=>$val){
				if($val['username'] == $value){
					$selected = "selected = selected";
				}else{
					$selected = "";
				}
				$drop_down .= '<option value="'.$val['username'].'" '.$selected.'>'.$val['username'].'</option>';
			}
		}
		$drop_down .= '</select>';
		return $drop_down;
  }
  
  function ipsettigs_account_number_icon($select = "", $table = "", $number) {
		$return_value = '';
		$where = array (
				'number' => $number
		);
		$this->CI->db->where("deleted",0);
		$account_res = ( array ) $this->CI->db->get_where ( "accounts", $where )->first_row ();
		
		if ($account_res ['type'] == 0) {
			$return_value .= " <span title='Edit'> </span>" . '<div class="col-md-12 p-0"><span class="badge badge-success float-left mr-2 mt-1" title="Customer">'.gettext("Customer").'</span>';
		}
		if ($account_res ['type'] == 3) {
			$return_value .= " <span title='Edit'> </span>" . '<div class="col-md-12 p-0"><span class="badge badge-primary float-left mr-2 mt-1" title="Provider">'.gettext("Provider").'</span>';
		}
		if ($account_res ['posttoexternal'] == 0 || $account_res ['posttoexternal'] == 1) {
			$return_value .= "<span class='badge badge-dark float-left ml-1 mt-1'>".$this->get_account_type ( "", "", $account_res ['posttoexternal'] ) ."</span></div>"; 
		}
		return $return_value;
	}
	function refillcoins_cost() {
		$status_array = array (
				'0.01' => gettext ( '0.01' ),
				'0.02' => gettext ( '0.02' ),
				'0.03' => gettext ( '0.03' ),
				'0.04' => gettext ( '0.04' ),
				'0.05' => gettext ( '0.05' ),
		);
		return $status_array;
	}


	function coins_consider_for_cost() {

		$status_array = array (
				'100' => gettext ( '100' ),
				'200' => gettext ( '200' ),
				'300' => gettext ( '300' ),
				'400' => gettext ( '400' ),
				'500' => gettext ( '500' ),
		);
		return $status_array;

	}

	function tap_coins() {

		$status_array = array (
				'2' => gettext ( '2' ),
				'4' => gettext ( '4' ),
				'6' => gettext ( '6' ),
				'8' => gettext ( '8' ),
				'10'=> gettext ( '10' ),
		);
		return $status_array;
	}

	function video_coins(){

		$status_array = array (
				'2' => gettext ( '2' ),
				'4' => gettext ( '4' ),
				'6' => gettext ( '6' ),
				'8' => gettext ( '8' ),
				'10' => gettext ( '10' ),
		);
		return $status_array;
	}
	
	function default_pagination_size(){
		$pagination_array = array (
				10 =>10,
				25=>25,
				50=>50,
				100=>100,
				200=>200,
				250=>250,
				500=>500
		);
		return $pagination_array;
	}
	function ios_notification_mode($status = '') {
		$status_array = array (
				'0' => gettext ( 'Live' ),
				'1' => gettext ( 'Sandbox' )
		);
		return $status_array;
	}

	function get_available_seconds_for_package($productid = '',$free_minutes,$used_seconds, $show_seconds=""){ 
		 $reseller_id = $this->CI->session->userdata('logintype') == 1 || $this->CI->session->userdata('logintype') == 5 ? $this->CI->session->userdata['accountinfo']['id'] : 0;
					$available_seconds =  $free_minutes - $used_seconds;
					 if ($show_seconds == 'minutes') {
						$available_seconds = $available_seconds;
						$available_seconds = $available_seconds > 0 ? sprintf('%02d', $available_seconds / 60) . ":" . sprintf('%02d', ($available_seconds % 60)) : "00:00";						
					    } else { 
						$available_seconds = $available_seconds;
						$available_seconds = sprintf('%02d', $available_seconds);	
					    }
					return  $available_seconds;	
	 }
	function get_total_available_minutes($free_minutes,$used_seconds){ 
					$available_seconds =  $free_minutes - $used_seconds;
					return  $available_seconds;	
	}
	function get_current_login_type_timezone(){
		return $this->CI->timezone->get_login_type_timezone();
	}
	function reseller_type_dropdown($status = '') {
		$status_array = array (
				'0' => gettext ( 'Distributor' ),
				'1' => gettext ( 'Commission Based' )
		);
		return $status_array;
	}
	// ASTPPCOM-941 Start
	function get_field_name_balance($select, $table, $where) {
		if (is_array ( $where )) {
			$where = $where;
		} else {
			$where = array (
					"id" => $where
			);
		}
		$field_name = $this->CI->db_model->getSelect ('*',"accounts", $where );
		$field_name = $field_name->row_array ();
		$prepaid='';
		$acc_creditlimit='';
		if($field_name['posttoexternal'] == 1){
			$acc_creditlimit = $field_name['credit_limit'] - $field_name['balance'];
		}else{
			$prepaid = $field_name['balance'];
		}
	
		if (isset ( $select ) && $select == 'balance') {
			return $this->convert_to_currency($select, $table, $prepaid);
		} else {
			return $this->convert_to_currency($select, $table, $acc_creditlimit);
		}
	}
	// ASTPPCOM-941 END

	// ASTPPCOM-944 Jaimin Start
	function sip_cid_types($status = '') {
		$status_array = array (
				'none' => gettext ('None'),
				'rpid' => gettext ('Remote-Party-ID'),
				'pid' => gettext ('P-Asserted-Identity')
		);
		return $status_array;
	}

	function get_sip_cid_type($select = "", $table = "", $value) {
		$data=array("none"=>"None","rpid"=>"Remote-Party-ID","pid"=>" P-Asserted-Identity");
		return  $data[$value] ;
	} 
	// END
	// ASTPPCOM-975 Start
	function encrypt( $string, $action = 'e' ) {
        $secret_key =  config_item('api_decipher_auth_token');
 		$secret_key = $secret_key != "" ? $secret_key : "YPPFWnaYBUmuINDsUsfnpceeJgGrGwFQ";
        $secret_iv =  config_item('iv_key');
		$secret_iv = isset($secret_iv) && $secret_iv != "<IV_KEY>" ? $secret_iv : "UwCs*^jjkNU53u%?QjGb2CAycS3Wqg94";
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }
        return $output;
    }
    function get_field_name_country_camel($select, $table, $where) {
		$timezone_name = $where;
		if (is_array ( $where )) {
			$where = $where;
		} else {
			if($select == 'gmtoffset'){
				$where = array("timezone_name"=>$where);
			}else{
				$where = array (
						"id" => $where
				);
			}
		}
		$field_name = $this->CI->db_model->getSelect ( $select, $table, $where );
		$field_name = $field_name->result ();
		if (isset ( $field_name ) && ! empty ( $field_name )) {
			if($select == 'gmtoffset'){
				if($field_name [0]->{$select} >= 0){
					$time = gmdate("H:i", $field_name [0]->{$select});
					$gmthours = "+".$time;
					return $gmthours;
				}else{
					$gmtoffset = str_replace("-","",$field_name [0]->{$select});
					$time = gmdate("H:i", $gmtoffset);
					$gmthours = "-".$time;
					return $gmthours;
				}

			}else{
				return ucwords(strtolower($field_name [0]->{$select}));
			}	 
		} else {
			return "";
		}
	}
	function notification_mode($status = '')
	{
		$status_array = array(
			'0' => gettext('ASTPP'),
			'1' => gettext('Personalized')
		);
		return $status_array;
	}
    // ASTPPCOM-975 END

	// ASTPPCOM-1005 Jaimin Start
	    function validate_module_access_level($module_name,$type,$module_url='',$redirect_url=""){
			$accountinfo = $this->CI->session->userdata('accountinfo');
			$permissioninfo = $this->CI->session->userdata('permissioninfo'); 
			if($accountinfo['type'] == 1 || $accountinfo['type'] == 2 ){
				if(isset($module_url) && $module_url ==''){
					$module_url=$module_name.'_list';
				}
			}
			if($module_name == "custom_rates"){
				$module_name = "personalized_rates";
			}
			if($module_url == "custom_rates_list"){
				$module_url = "personalized_rates_list";
			}
			if($accountinfo['type'] != -1){
				$account_permission = $permissioninfo[$module_name][$module_url];
			}
			if (is_array($permissioninfo) && !array_key_exists($type, $account_permission) || $accountinfo['type'] == 3) {
				if($accountinfo['type'] != -1){
					$this->CI->session->set_flashdata ( 'astpp_notification','Permission Denied!');
					if($redirect_url != ''){
						redirect(base_url().$redirect_url);
					}else{
						if ($accountinfo['type'] == '-1' || $accountinfo['type'] == '2' || $accountinfo['type'] == '4' || $accountinfo['type'] == '1') {
							redirect(base_url().'dashboard/');
						} else {
							redirect(base_url().'user/user/');
						}
					}
				}
			}
		}
	// End
	// ASTPPCOM-982 Ashish start
	function get_status_voice_broadcast($select = "", $table = "", $status) {
		if($status['status'] == 0){
			$status = gettext("InProgress");
		}elseif($status['status'] == 1){
			$status = gettext("Pending");
		}elseif($status['status'] == 2){
			$status = gettext("Completed");
		}
		return $status;
	}
	// ASTPPCOM-982 Ashish End

	function set_routetype_origination($status = '') {
		$status_array = array( ""=>'--Select--','0' => 'Priority','1' => 'Percentage');
		return $status_array;
	}
	// ASTPPCOM-982 Ashish start
	function set_voice_broadcast_status($status) {
		$status_array = array (
				'' => gettext ( '--Select--' ),
				'0' => gettext ( 'InProgress' ),
				'1' => gettext ( 'Pending' ),
				'2' => gettext ( 'Completed' )
		);
		return $status_array;
	}
	// ASTPPCOM-982 Ashish End

	//Gautam ASTPPENT-6696 Start ASTPPCOM-1348_kanu_start
	function convert_to_currency_change($select = "", $table = "", $amount) {
		$this->CI->load->model ( 'common_model' );
		return $this->CI->common_model->calculate_currency_locale( $amount, '', '', true, false );
	}
	//Gautam ASTPPENT-6696 End ASTPPCOM-1348_kanu_end

	//Gautam ASTPPENT-6696 Start ASTPPCOM-1348_kanu_start
	function convert_currency_in_locale_new($select = "", $table = "", $amount,$key = ""){
		$current_language =$this->CI->session->userdata('user_language'); 
		$amount = $this->convert_to_currency_change('','',$amount);
			if(isset($current_language) && ($current_language == 'es_ES' ||  $current_language == 'pt_BR') && strpos($amount, ',') !== false){

				$amount = str_replace ( ',', '.', $amount );
			}

		return $amount;
	}
	//Gautam ASTPPENT-6696 End ASTPPCOM-1348_kanu_end
}
