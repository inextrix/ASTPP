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
class Db_model extends CI_Model {
	public function __construct() {
		parent::__construct ();
		$this->db->query ( "SET time_zone='+0:00'" );
	}

	/*
	 * ********************************************************
	 * Function getCriteria(Where=Condition in Array Format)
	 * ********************************************************
	 */
	function getCriteria($condition = "", $tableName) {
		if ($condition != "") {
			$this->db->where ( $condition );
		}
		return $this->db->get ( $tableName );
	}

	/*
	 * ********************************************************
	 * Function save() for addingthe record
	 * ********************************************************
	 */
	function save($tableName, $arr, $val = 'false') {
		$str = $this->db->insert_string ( $tableName, $arr );
		$rs = $this->db->query ( $str );
		if ($val == true)
			return $this->db->insert_id ();
		else
			return $rs;
	}

	/*
	 * ********************************************************
	 * Function update() for editing the record
	 * ********************************************************
	 */
	function update($tableName, $arr, $where) {
		$str = $this->db->update_string ( $tableName, $arr, $where );
		$rs = $this->db->query ( $str );
		return $rs;
	}

	/*
	 * ********************************************************
	 * Function getSelect()n for displaying record
	 * ********************************************************
	 */
	function getSelect($select, $tableName, $where) {
		$this->db->select ( $select, false );
		$this->db->from ( $tableName );
		if ($where != '') {
			$this->db->where ( $where );
		}
		$query = $this->db->get ();
		return $query;
	}

	/*
	 * ********************************************************
	 * Function getSelectWithOrder()n for displaying record
	 * ********************************************************
	 */
	function getSelectWithOrder($select, $tableName, $where, $order_type, $order_by) { 
		$this->db->select ( $select );
		$this->db->from ( $tableName );
		$this->db->where ( $where );
		$this->db->order_by ( $order_by, $order_type );
		$query = $this->db->get ();
		return $query;
	}

	/*
	 * ********************************************************
	 * Function getSelectWithOrderAndLimit()n for displaying record
	 * ********************************************************
	 */
	function getSelectWithOrderAndLimit($select, $tableName, $where, $order_type, $order_by, $paging_limit) {
		$this->db->select ( $select );
		$this->db->from ( $tableName );
		$this->db->where ( $where );
		$this->db->order_by ( $order_by, $order_type );
		$this->db->limit ( $paging_limit );
		$query = $this->db->get ();
		return $query;
	}

	/*
	 * ********************************************************
	 * Function delete() for deletingthe record
	 * ********************************************************
	 */
	function delete($tableName, $where) {
		$this->db->where ( $where );
		$this->db->delete ( $tableName );
	}

	/*
	 * ********************************************************
	 * Function excecute() take compelet query
	 * ********************************************************
	 */
	function excecute($query) {
		$rs = $this->db->query ( $query );
		return $rs;
	}

	/*
	 * ********************************************************
	 * Function select() take full complete perms
	 * ********************************************************
	 */
	function select($select, $tableName, $where, $order_by, $order_type, $paging_limit = '', $start_limit = '', $groupby = '') {
		$this->db->select ( $select );
		$this->db->from ( $tableName );
		if ($where != "") {
			$this->db->where ( $where );
		}
		if ($paging_limit)
			$this->db->limit ( $paging_limit, $start_limit );
		if (! empty ( $groupby ))
			$this->db->group_by ( $groupby );
		if (isset ( $_GET ['sortname'] ) && $_GET ['sortname'] != 'undefined') {
			$this->db->order_by ( $_GET ['sortname'], ($_GET ['sortorder'] == 'undefined') ? 'desc' : $_GET ['sortorder'] );
		} else {
			if ($order_by)
				$this->db->order_by ( $order_by, $order_type );
		}
		$query = $this->db->get ();
		return $query;
	}

	/*
	 * ********************************************************
	 * Function select for In query () take full complete perms
	 * ********************************************************
	 */
	function select_by_in($select, $tableName, $where, $order_by, $order_type, $paging_limit, $start_limit, $groupby = '', $key, $where_in) {
		$this->db->select ( $select );
		$this->db->from ( $tableName );
		if ($where != "") {
			$this->db->where ( $where );
		}
		$this->db->where_in ( $key, $where_in );
		$this->db->order_by ( $order_by, $order_type );
		if ($paging_limit)
			$this->db->limit ( $paging_limit, $start_limit );
		if (! empty ( $groupby ))
			$this->db->groupby ( $groupby );
		$query = $this->db->get ();

		return $query;
	}

	/*
	 * ********************************************************
	 * Function countQuery() take table name and select field
	 * ********************************************************
	 */
	function countQuery($select, $table, $where = "") {
		$this->db->select ( $select );
		if ($where != "") {
			$this->db->where ( $where );
		}
		$this->db->from ( $table );
		$query = $this->db->get ();
		return $query->num_rows ();
	}

	/*
	 * ********************************************************
	 * Function countQuery for where in query() take table name and select field
	 * ********************************************************
	 */
	function countQuery_by_in($select, $table, $where = "", $key, $where_in) {
		$this->db->select ( $select );
		if ($where != "") {
			$this->db->where ( $where );
		}
		if (! empty ( $where_in )) {
			$this->db->where_in ( $key, $where_in );
		}
		$this->db->from ( $table );
		$query = $this->db->get ();
		return $query->num_rows ();
	}

	/*
	 * ********************************************************
	 * Function maxQuery() take table name and select field
	 * ********************************************************
	 */
	function maxQuery($table, $select, $where = "", $name) {
		$this->db->select ( $select );
		$this->db->from ( $table );
		if ($where != "") {
			$this->db->where ( $where );
		}
		$query = $this->db->get ();
		if ($query->num_rows () > 0) {
			$rowP = $query->row ();
			return $rowP->{$name};
		} else {
			return 0;
		}
	}

	/*
	 * ********************************************************
	 * Function getCurrent get current value of the field
	 * ********************************************************
	 */
	function getCurrent($table, $field, $where) {
		$this->db->select ( $field );
		$this->db->from ( $table );
		$this->db->where ( $where );
		$query = $this->db->get ();
		if ($query->num_rows () > 0) {

			$rowP = $query->row ();
			return $rowP->{$field};
		} else {
			return false;
		}
	}

	/*
	 * ********************************************************
	 * Function getJionQuery get result set on criteria
	 * ********************************************************
	 */
	function getJionQuery($table, $field, $where = "", $jionTable, $jionCondition, $type = 'inner', $start = '', $end = '', $order_type = '', $order_by = '', $group_by = '') {

		$start = ( int ) $start;
		$end = ( int ) $end;
		$this->db->select ( $field );
		$this->db->from ( $table );
		$this->db->join ( $jionTable, $jionCondition, $type );
		if ($where != "") {
			$this->db->where ( $where );
		}
		// OLD CODE
		/*if (isset ( $_GET ['sortname'] ) && $_GET ['sortname'] != 'undefined') {

		} else {
			if ($order_by)
				$this->db->order_by ( $order_by, $order_type );
		}
		*/
		// NEW CODE 
		if ($order_by){
				$this->db->order_by ( $order_by, $order_type );
		}

		if ($group_by != '') {
			$this->db->group_by ( $group_by );
		}
		if ($start != '' && $end != '') {
			$this->db->limit ( $start, $end );
		}

		if ($start != '' && $end == '') {
			$this->db->limit ( $start );
		}

		 return $query = $this->db->get ();
	}
	function getJionQueryCount($table, $field, $where = "", $jionTable, $jionCondition, $type = 'inner', $start = '', $end = '', $order_type = '', $order_by = '', $group_by = '') {
		$start = ( int ) $start;
		$end = ( int ) $end;
		$this->db->select ( $field );
		$this->db->from ( $table );
		$this->db->join ( $jionTable, $jionCondition, $type );
		if ($where != "") {
			$this->db->where ( $where );
		}

		if ($order_type != '' && $order_by != '') {
			$this->db->order_by ( $order_by, $order_type );
		}

		if ($group_by != '') {
			$this->db->group_by ( $group_by );
		}
		if ($start != '' && $end != '') {
			$this->db->limit ( $start, $end );
		}
		if ($start != '' && $end == '') {
			$this->db->limit ( $start );
		}
		$query = $this->db->get ();
		return $query->num_rows ();
	}
	function getAllJionQuery($table, $field, $where = "", $jionTable, $jionCondition, $type, $start = '', $end = '', $order_type = '', $order_by = '', $group_by = '') {
		$start = ( int ) $start;
		$end = ( int ) $end;
		$this->db->select ( $field );
		$this->db->from ( $table );
		$jion_table_count = count ( $jionTable );
		for($i = 0; $i < $jion_table_count; $i ++) {
			$this->db->join ( $jionTable [$i], $jionCondition [$i], $type [$i] );
		}
		if ($where != "") {
			$this->db->where ( $where );
		}
		if (isset ( $_GET ['sortname'] ) && $_GET ['sortname'] != 'undefined') {
			$this->db->order_by ( $_GET ['sortname'], ($_GET ['sortorder'] == 'undefined') ? 'desc' : $_GET ['sortorder'] );
		} else {
			if ($order_by)
				$this->db->order_by ( $order_by, $order_type );
		}

		if ($group_by != '') {
			$this->db->group_by ( $group_by );
		}

		if ($start != '' && $end != '') {
			$this->db->limit ( $start, $end );
		}

		if ($start != '' && $end == '') {
			$this->db->limit ( $start );
		}

		return $query = $this->db->get ();
	}
	function getCountWithJion($table, $field, $where = "", $jionTable, $jionCondition, $type, $group_by = '') {
		$this->db->select ( $field );
		$this->db->from ( $table );
		$jion_table_count = count ( $jionTable );
		for($i = 0; $i < $jion_table_count; $i ++) {
			$this->db->join ( $jionTable [$i], $jionCondition [$i], $type [$i] );
		}

		if ($where != "") {
			$this->db->where ( $where );
		}
		if ($group_by != '') {
			$this->db->group_by ( $group_by );
		}
		$query = $this->db->get ();
		if ($query->num_rows () > 0) {
			return $query->num_rows ();
		} else {
			return false;
		}
	}

	/*
	 * ********************************************************
	 * Function getCurrentWithOrder
	 * ********************************************************
	 */
	function getCurrentWithOrder($table, $field, $where, $order, $order_by, $limit, $option) {
		$this->db->select ( $field );
		$this->db->from ( $table );
		$this->db->where ( $where );
		$this->db->order_by ( $order, $order_by );
		if ($limit != 0) {
			$this->db->limit ( $limit );
		}
		$query = $this->db->get ();
		if ($query->num_rows () > 0) {
			$rowP = $query->row ();
			if ($option == 'yes') {
				return $rowP->{$field};
			} else {
				return $query;
			}
		} else {
			if ($option == 'no') {
				return $query;
			} else {
				return false;
			}
		}
	}

	/*
	 * ********************************************************
	 * Function getReferPatients
	 * ********************************************************
	 */
	function getAllWithOrder($table, $field, $where) {
		$this->db->select ( $field );
		$this->db->from ( $table );
		$this->db->where ( $where );
		$query = $this->db->get ();
		if ($query->num_rows () > 0) {
			$rowP = $query->row ();
			return $rowP->{$field};
		} else {
			return false;
		}
	}
	function getCommaSperated($table, $select, $where, $limit, $return_message = FALSE, $message = '') {
		if ($table != '') {
			$this->db->select ( $select );
			$this->db->from ( $table );
			$this->db->where ( $where );
			if ($limit != 0) {
				$this->db->limit ( $limit );
			}
			$query = $this->db->get ();
			$string = '';
			if ($query->num_rows () > 0) {
				foreach ( $query->result () as $rows ) {
					$string .= $rows->{$select} . ',';
				}

				return substr ( $string, '', - 1 );
			} else {
				if ($return_message == FALSE) {
					return '';
				} else {
					return $message;
				}
			}
		} else {
			return '';
		}
	}
	function build_concat_dropdown($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		if (isset ( $select_params [3] )) {
			$cnt_str = " $select_params[1],' ',$select_params[2],' ','(',$select_params[3],')' ";
		} else {
			$cnt_str = " $select_params[1],' (',$select_params[2],')' ";
		}
		$select = $select_params [0] . ", concat($cnt_str) as $select_params[1] ";
		$logintype = $this->session->userdata ( 'logintype' );
		if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr') {
			$account_data = $this->session->userdata ( "accountinfo" );
			$id_value ['reseller_id'] = $account_data ['id'];
		}
		if (isset ( $id_value ['type'] ) && $id_value ['type'] == '0,3') {
			$twhere = "type IN (" . $id_value ["type"] . ")";
			$this->db->where ( $twhere );
			unset ( $id_value ['type'] );
		}
		$where = $id_value;
		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();

		$drp_list = array ();
		foreach ( $drp_array as $drp_value ) {
			if($select_params[1] == 'first_name' && $select_params[2] == 'last_name'){
				$company_info = (array)$this->db->get_where($table,array("id"=>$drp_value->id))->first_row();
				if (!empty($company_info['company_name'])) {
					$drp_value->first_name = $company_info['company_name'] . '('.$company_info['number'].' )';
				}else{
					$drp_value->first_name = $company_info['first_name'] . ' ' . $company_info['last_name'] . ' ' .'('.$company_info['number'] .')';
				}
			}else{
				$drp_value->first_name = $company_info['first_name'] . ' ' . $company_info['last_name'] . ' ' .'('.$company_info['number'] .')';
			}
			$drp_list [$drp_value->{$select_params [0]}] = $drp_value->{$select_params [1]};
		}
		return $drp_list;
	}
	function build_concat_dropdown_refill_coupon($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		$account_data = $this->session->userdata ( "accountinfo" );
		if (isset ( $select_params [3] )) {
			$cnt_str = " $select_params[1],' ',$select_params[2],' ','(',$select_params[3],')' ";
		} else {
			$cnt_str = " $select_params[1],' (',$select_params[2],')' ";
		}
		$select = $select_params [0] . ", concat($cnt_str) as $select_params[1] ";
		$logintype = $this->session->userdata ( 'logintype' );
		if ($account_data ['type'] == 1 && $id_where == 'where_arr') {
			$id_value ['reseller_id'] = $account_data ['id'];
		} else {
			$this->db->or_where ( 'type', 3 );
		}
		$where = $id_value;
		$this->db->where ( $where );
		$drp_array = $this->getSelect ( $select, $table, '' );
		$drp_array = $drp_array->result ();
		$drp_list = array ();
		foreach ( $drp_array as $drp_value ) {
			$drp_list [$drp_value->{$select_params [0]}] = $drp_value->{$select_params [1]};
		}
		return $drp_list;
	}
	function build_concat_select_dropdown($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		if (isset ( $select_params [3] )) {
			$cnt_str = " $select_params[1],' ',$select_params[2],' ','(',$select_params[3],')' ";
		} else {
			$cnt_str = " $select_params[1],' (',$select_params[2],')' ";
		}
		$select = $select_params [0] . ", concat($cnt_str) as $select_params[1] ";
		$where = $id_value;
		$drp_array = $this->getSelect ( $select, $table, $id_value );
		$drp_array = $drp_array->result ();

		$drp_list = array ();
		$drp_list [0] = gettext("--Select--");
		foreach ( $drp_array as $drp_value ) {
			if($select_params[1] == 'first_name'){
				$company_info = (array)$this->db->get_where($table,array("id"=>$drp_value->id))->first_row();
				if (!empty($company_info['company_name'])) {
					$drp_value->first_name = $company_info['company_name'] . '('.$company_info['number'].' )';
				}else{
					$drp_value->first_name = $company_info['first_name'] . ' ' .'('.$company_info['number'] .')';
				}
			}else{
				$drp_value->first_name = $company_info['first_name'] . ' '  .'('.$company_info['number'] .')';
			}
			$drp_list [$drp_value->{$select_params [0]}] = $drp_value->{$select_params [1]};
		}
		return $drp_list;
	}
	function build_dropdown($select, $table, $id_where = '', $id_value = '') { 
		$select_params = explode ( ',', $select );
		$where = '';
		if (isset ( $id_value ["type"] ) && $id_value ["type"] == "GLOBAL") {
			$where = "type IN ('0','3')";
			$this->db->where ( $where );
			unset ( $id_value ["type"] );
		}

		if ($id_where != '' && $id_value != '') {   
			if ($id_where == 'group_by') {  
				$this->db->group_by ( $id_value );
			} else if ($id_where == "where_arr") { 
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr' && $this->db->field_exists ( 'reseller_id', $table )) {
					if($table != 'taxes'){
						$id_value ['reseller_id'] = $this->session->userdata ["accountinfo"] ['id'];
					} else {
						// taxes of the admin and the own ones, not those of other resellers
						$this->db->where_in ( 'reseller_id', array ( 0, ( int ) $this->session->userdata ["accountinfo"] ['id'] ) );
					}
				}
				$where = $id_value;

			} else { 
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'reseller_id') {
					$account_data = $this->session->userdata ( "accountinfo" );
					$id_value = $account_data ['id'];
				}
				$where = array (
						$id_where => $id_value
				);
			}
		}

		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();

		$drp_list = array ();
		foreach ( $drp_array as $drp_value ) {
			$drp_list [$drp_value->{$select_params [0]}] = gettext($drp_value->{$select_params [1]});
		}
		return $drp_list;
	}

	function build_concat_dropdown_timezone($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		if (isset ( $select_params [3] )) {
			$cnt_str = " $select_params[1],' ',$select_params[2],' ','(',$select_params[3],')' ";
		} else {
			$cnt_str = " $select_params[1],' (',$select_params[2],')' ";
		}
		$logintype = $this->session->userdata ( 'logintype' );
		if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr') {
			$account_data = $this->session->userdata ( "accountinfo" );
			$id_value ['reseller_id'] = $account_data ['id'];
		}
		if (isset ( $id_value ['type'] ) && $id_value ['type'] == '0,3') {
			$twhere = "type IN (" . $id_value ["type"] . ")";
			$this->db->where ( $twhere );
			unset ( $id_value ['type'] );
		}
		$where = $id_value;
		$this->db->order_by('timezone_name','asc');
		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();

		$drp_list = array ();
		foreach ( $drp_array as $drp_value ) {
			if($drp_value->{$select_params [2]} >=0){
				$time = gmdate("H:i",$drp_value->{$select_params [2]});
				$drp_value->{$select_params [2]} = "GMT +".$time;
			}else{
				$gmthour = str_replace("-","",$drp_value->{$select_params [2]});
				$time = gmdate("H:i",$gmthour);
				$drp_value->{$select_params [2]} = "GMT -".$time;
			}
			$drp_list [$drp_value->{$select_params [0]}] = gettext($drp_value->{$select_params [1]}).' ('.$drp_value->{$select_params [2]}.')';
		}
		return $drp_list;
	}
	
	function build_dropdown_reseller($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		$where = '';
		if (isset ( $id_value ["type"] ) && $id_value ["type"] == "GLOBAL") {
			$where = "type IN ('0','3')";
			$this->db->where ( $where );
			unset ( $id_value ["type"] );
		}
		if ($id_where != '' && $id_value != '') {
			if ($id_where == 'group_by') {
				$this->db->group_by ( $id_value );
			} else if ($id_where == "where_arr") {
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr' && $this->db->field_exists ( 'reseller_id', $table )) {
						$id_value ['reseller_id'] = $this->session->userdata ["accountinfo"] ['reseller_id'] ;
				}
				$where = $id_value;
			} else {
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'reseller_id') {
					$account_data = $this->session->userdata ( "accountinfo" );
					$id_value = $account_data ['id'];
				}
				$where = array (
						$id_where => $id_value
				);
			}
		}
		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		$drp_list = array ();
		$drp_list[0]=gettext("--Select--");
		foreach ( $drp_array as $drp_value ) {
			if(isset($select_params [2]) && $select_params [2] != ""){
				$drp_list [$drp_value->{$select_params [0]}] = $drp_value->{$select_params [1]} ."(".$drp_value->{$select_params [2]}.")";
			}else{
				$drp_list [$drp_value->{$select_params [0]}] = $drp_value->{$select_params [1]};
			}
		}
		return $drp_list;
	}
	
	function build_dropdown_deleted($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		if (isset ( $id_value ["type"] )) {
			$where = $id_value ["type"] == "GLOBAL" ? "type IN ('0','3')" : "type IN (" . $id_value ["type"] . ")";
			$this->db->where ( $where );
			unset ( $id_value ["type"] );
		}
		$where = '';
		if ($id_where != '' && $id_value != '') {
			if ($id_where == 'group_by') {
				$this->db->group_by ( $id_value );
			} else if ($id_where == "where_arr") {
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr') {
					$account_data = $this->session->userdata ( "accountinfo" );
					$id_value ['reseller_id'] = $account_data ['id'];
				}
				$where = $id_value;
			} else {
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'reseller_id') {
					$account_data = $this->session->userdata ( "accountinfo" );
					$id_value = $account_data ['id'];
				}
				$where = array (
						$id_where => $id_value
				);
			}
		}

		$drp_array = $this->getSelect ( $select, $table, $where );

                $drp_array = $drp_array->result_array();

                $name = explode ( "as", $select );
                 if (isset ( $name [3] ) && (!isset ( $name [4] ))  ) {
                        $name = trim ( $name [3] );
                }else if(isset ( $name [4] )){
		 	 $name = trim ( $name [4] );
		}else {
                        $name = trim ( $name [1] );
                }

                $drp_list = array ();
                $dele = array ();
		$inavtive = array ();
                foreach ( $drp_array as $drp_value ) {
                        $dele = explode ( "^", $drp_value[$name]);
			$inavtive = explode ( "*", $drp_value[$name]);
                        if (isset ( $dele [1] )) {
                                $drp_list ['Deleted'] [$drp_value[$select_params [0]]] = str_replace ( "^", "", $drp_value[$name] );
                        }else if(isset( $inavtive [1])){
			 	$drp_list ['Inactive'] [$drp_value[$select_params [0]]] = str_replace ( "*", "", $drp_value[$name] );
			} else {
                                $drp_list ['Active'] [$drp_value[$select_params [0]]] = $drp_value[$name];
                        }
                }
		ksort ( $drp_list );
		return $drp_list;
	}
	function build_search($accounts_list_search,$table_name='') { 
		if ($this->session->userdata ( 'advance_search' ) == 1) {
			$account_search = $this->session->userdata ( $accounts_list_search );
			unset ( $account_search ["ajax_search"] );
			unset ( $account_search ["advance_search"] );

			unset ( $account_search ['search_in'], $account_search ['time'] );

			if (! empty ( $account_search )) {

				foreach ( $account_search as $key => $value ) {
					if ($value != "") {
						if (is_array ( $value )) {
							if (array_key_exists ( $key . "-integer", $value )) {
								if($key == 'debit_exchange_rate' || $key == 'credit_exchange_rate' ){
									$this->get_interger_array_invoice_amount ( $table_name.$key, $value [$key . "-integer"], $value [$key]);
								}else{
								$this->get_interger_array ( $table_name.$key, $value [$key . "-integer"], $value [$key]);
								}
							}
							if (array_key_exists ( $key . "-string", $value )) {
								$this->get_string_array ( $table_name.$key, $value [$key . "-string"], $value [$key]);
							}
							if ($key == 'callstart' || $key == 'date' || $key == 'order_date' || $key == 'timestamp' || $key == 'payment_date' || $key == 'first_used' || $key == 'creation' || $key == 'from_date' || $key == 'generate_date' || $key == 'expiry' || $key == 'created_date' || $key == 'to_date' || $key == 'triggered_date' || $key == 'last_triggered_date') {

							$this->get_date_array ( $table_name.$key, $value );
							}
						} else {
							if($key == 'disposition'){
                                                                $str1 = $table_name.$key . " LIKE '%$value%'";
                                                                $this->db->where ( $str1 );
                                                        }else{
								if($key != 'cdrs_year'){											
									$this->db->where ( $table_name.$key, $value );
								}
							}
						}
					}
				}
				return true;
			}
		}
	}
	function get_date_array($field, $value) {

		if ($value != '') {
			if (! empty ( $value [0] )) {
				if ($field == 'generate_date') {
					$this->db->where ( $field . ' >= ', gmdate ( "Y-m-d", strtotime ( $value ['0'] ) ) . " 00:00:01" );
					$this->db->where ( $field . ' <= ', gmdate ( "Y-m-d", strtotime ( $value ['0'] ) ) . " 23:59:59" );
				} else if ($field == 'to_date') {
					$this->db->where ( $field . ' <= ', gmdate ( "Y-m-d", strtotime ( $value ['0'] ) ) . " 23:59:59" );
				}else if ($field == 'expiry'){
					$this->db->where ( $field . ' >= ', gmdate ( "Y-m-d", strtotime ( $value ['0'] ) ) . " 00:00:01" );
					$this->db->where ( $field . ' <= ', gmdate ( "Y-m-d", strtotime ( $value ['0'] ) ) . " 23:59:59" );
				}else if ($field == 'creation'){
					$this->db->where ( $field . ' >= ', gmdate ( "Y-m-d", strtotime ( $value ['0'] ) ) . " 00:00:01" );
					$this->db->where ( $field . ' <= ', gmdate ( "Y-m-d", strtotime ( $value ['0'] ) ) . " 23:59:59" );
				}else {
					$this->db->where ( $field . ' >= ', gmdate ( 'Y-m-d H:i:s', strtotime ( $value [0] ) ) );
				}
			}
			if (! empty ( $value [1] )) {
				$this->db->where ( $field . ' <= ', gmdate ( 'Y-m-d H:i:s', strtotime ( $value [1] ) ) );
			}
		}
	}
	function get_interger_array($field, $value, $search_array) {
		if ($search_array != '') {
			switch ($value) {
				case "1" :
					$this->db->where ( $field, $search_array,NULL,false );
					break;
				case "2" :
					$this->db->where ( $field . ' <>', $search_array,false,false );
					break;
				case "3" :
					$this->db->where ( $field . ' > ', $search_array,NULL,false );
					break;
				case "4" :
					$this->db->where ( $field . ' < ', $search_array,NULL,false );
					break;
				case "5" :
					$this->db->where ( $field . ' >= ', $search_array,NULL,false );
					break;
				case "6" :
					$this->db->where ( $field . ' <= ', $search_array,NULL,false );
					break;
			}
		}
	}
	function get_interger_array_invoice_amount($field, $value, $search_array) {
		if ($search_array != '') {
			switch ($value) {
				case "1" :
					$this->db->where ( "debit_exchange_rate", $search_array,NULL,false );
					$this->db->or_where ("credit_exchange_rate", $search_array,NULL,false );
					break;
				case "2" :
					$this->db->where ( "debit_exchange_rate" . ' <>', $search_array,false,false );
					$this->db->where ( "credit_exchange_rate" . ' <>', $search_array,false,false );
					break;
				case "3" :
					$this->db->where ( "debit_exchange_rate" . ' > ', $search_array,NULL,false );
					$this->db->or_where ( "credit_exchange_rate" . ' > ', $search_array,NULL,false );
					break;
				case "4" :
					$this->db->where ( "debit_exchange_rate" . ' < ', $search_array,NULL,false );
					$this->db->where ( "credit_exchange_rate" . ' < ', $search_array,NULL,false );
					break;
				case "5" :
					$this->db->where ( "debit_exchange_rate"  . ' >= ', $search_array,NULL,false );
					$this->db->or_where ( "credit_exchange_rate"  . ' >= ', $search_array,NULL,false );
					break;
				case "6" :
					$this->db->where ( "debit_exchange_rate" . ' <= ', $search_array,NULL,false );
					$this->db->where ( "credit_exchange_rate" . ' <= ', $search_array,NULL,false );
					break;
			}
		}
	}

	function get_string_array($field, $value, $search_array) {

		if($field == 'reseller_products.name' ){
			$field = 'products.name';

		}
		if ($search_array != '') {
			switch ($value) {
				case "1" :
					$str1 = $field . " LIKE '%$search_array%'";
					$this->db->where ( $str1 );
					break;
				case "2" :
					$str1 = $field . " NOT LIKE '%$search_array%'";
					$this->db->where ( $str1 );
					break;
				case "3" :
                                         if ($field == "pattern") {
                                                $str1 = $field . " = '^" . $search_array . ".*'";
                                                $this->db->where ( $str1 );
                                        }else{
                                                $this->db->where ( $field, $search_array );
                                        }
                                        break;
                                case "4" :
                                        if ($field == "pattern") {
                                                $str1 = $field . " <> '^" . $search_array . ".*'";
                                                $this->db->where ( $str1 );
                                        }else{
                                                 $this->db->where ( $field . ' <>', $search_array );
                                        }
                                        break;
				case "5" :
					if ($field == "pattern") {
						$str1 = $field . " LIKE '^" . $search_array . "%'";
						$this->db->where ( $str1 );
					} else {
						$str1 = $field . " LIKE '" . $search_array . "%'";
						$this->db->where ( $str1 );
					}

					break;
				case "6" :
					if ($field == "pattern") {
						$str1 = $field . " LIKE '%" . $search_array . ".*'";
						$this->db->where ( $str1 );
					} else {
						$str1 = $field . " LIKE '%" . $search_array . "'";
						$this->db->where ( $str1 );
					}

					break;
			}
		}
	}
	function build_search_string($accounts_list_search) {
		$where = null;
		$search = $this->session->userdata ( $accounts_list_search );
		if ($this->session->userdata ( 'advance_search' ) == 1) {
			$account_search = $this->session->userdata ( $accounts_list_search );
			unset ( $account_search ["ajax_search"] );
			unset ( $account_search ["advance_search"] );
			if (! empty ( $account_search )) {
				foreach ( $account_search as $key => $value ) {
					if ($value != "") {
						if (is_array ( $value )) {
							if (array_key_exists ( $key . "-integer", $value )) {
								$string = null;
								$string = $this->build_interger_where ( $key, $value [$key . "-integer"], $value [$key] );
								if ($string)
									$where .= "$string AND ";
							}
							if (array_key_exists ( $key . "-string", $value )) {
								$string = null;
								$string = $this->build_string_where ( $key, $value [$key . "-string"], $value [$key] );
								if ($string)
									$where .= "$string AND ";
							}
							if ($key == 'callstart' || $key == 'date' || $key == 'log_time') {
								$string = null;
								$string = $this->build_date_where ( $key, $value );
								if ($string)
									$where .= "$string AND ";
							}
						} else {
							$where .= "$key = '$value'AND ";
						}
					}
				}
			}
		}
		$where = rtrim ( $where, " AND " );
		return $where;
	}
	function build_string_where($field, $value, $search_array) {
		$where = null;
		if ($search_array != '') {
			switch ($value) {
				case "1" :
					$where = "$field LIKE '%$search_array%'";
					break;
				case "2" :
					$where = "$field NOT LIKE '%$search_array%'";
					break;
				case "3" :
                                        if ($field == "pattern" || $field == 'prefix') {
                                        	$str1 = $field . " = '^" . $search_array . ".*'";
                                        	$this->db->where ( $str1 );
                                        }else{
                                        	$this->db->where ( $field, $search_array );
                                        }
                                        break;
                                case "4" :
                                        if ($field == "pattern" || $field == 'prefix') {
                                        	$str1 = $field . " <> '^" . $search_array . ".*'";
                                        	$this->db->where ( $str1 );
                                        }else{
                                                 $this->db->where ( $field . ' <>', $search_array );
                                        }
                                        break;
				case "5" :
					if ($field == "pattern") {
						$where = $field . " LIKE '^" . $search_array . "%'";
					} else {
						$where = $field . " LIKE '" . $search_array . "%'";
					}
					break;
				case "6" :
					if ($field == "pattern") {
						$str1 = $field . " LIKE '%" . $search_array . ".*'";
					} else {
						$str1 = $field . " LIKE '%" . $search_array . "'";
					}
					break;
			}
		}
		return $where;
	}
	function build_interger_where($field, $value, $search_array) {
		$where = null;
		if ($search_array != '') {
			if (is_numeric ( $search_array )) {
				switch ($value) {
					case "1" :
						$where = "$field = '$search_array'";
						break;
					case "2" :
						$where = "$field <> '$search_array'";
						break;
					case "3" :
						$where = "$field > '$search_array'";
						break;
					case "4" :
						$where = "$field < '$search_array'";
						break;
					case "5" :
						$where = "$field >= '$search_array'";
						break;
					case "6" :
						$where = "$field <= '$search_array'";
						break;
				}
			} else {
				$this->db->where ( "$field IS NULL" );
				$where = "$field IS NULL";
			}
		}
		return $where;
	}
	function build_date_where($field, $value) {
		$where = null;
		if ($value != '') {
			if (! empty ( $value [0] )) {
				$string = null;
				$string = "$field >= '$value[0]'";
				if ($string)
					$where .= $string . " AND ";
			}
			if (! empty ( $value [1] )) {
				$string = null;
				$string = "$field <= '$value[1]'";
				if ($string)
					$where .= $string . " AND ";
			}
		}
		if ($where) {
			$where = rtrim ( $where, " AND " );
		}
		return $where;
	}
	function get_available_bal($account_info) {
		$available_bal = 0;
		$available_bal = ($account_info ["posttoexternal"] == 1) ? ($account_info ["credit_limit"] - $account_info ["balance"]) : ($account_info ["balance"]);

		return $available_bal;
	}
	function update_balance($amount, $accountid, $payment_type) {
		// amount and id are concatenated into SQL - numbers only (decimal(20,5))
		$amount = sprintf('%.5F', (float) $amount);
		$accountid = (int) $accountid;
		if ($payment_type == "debit" || $payment_type == "0") {

			$query = "update accounts set balance =  IF(posttoexternal=1,balance+" . $amount . ",balance-" . $amount . ") where id ='" . $accountid . "'";

			return $this->db->query ( $query );
		} else {
			$query = "update accounts set balance =  IF(posttoexternal=1,balance-" . $amount . ",balance+" . $amount . ") where id ='" . $accountid . "'";

			return $this->db->query ( $query );
		}
	}
	function build_batch_update_array($update_array) {
		$updateflg = false;
		foreach ( $update_array as $key => $update_fields ) {
			// The column name comes from the POST keys and is used unquoted
			if (! preg_match ( '/^[A-Za-z_][A-Za-z0-9_]*$/', ( string ) $key )) {
				continue;
			}
			if (is_array ( $update_fields )) {
				$operator = isset ( $update_fields ["operator"] ) ? ( string ) $update_fields ["operator"] : '';
				$value = isset ( $update_fields [$key] ) ? $update_fields [$key] : '';
				switch ($operator) {
					case "1" :
						break;
					case "2" :
						if ($value != '') {
							$updateflg = true;
							$this->db->set ( $key, $value );
						}
						break;
					// Increase/decrease were built as raw SQL from the POST value
					// (SQL injection) - only numbers are allowed
					case "3" :
						if (is_numeric ( $value )) {
							$this->db->set ( $key, $key . "+" . ( float ) $value, FALSE );
							$updateflg = true;
						}
						break;
					case "4" :
						if (is_numeric ( $value )) {
							$this->db->set ( $key, $key . "-" . ( float ) $value, FALSE );
							$updateflg = true;
						}
						break;
				}
			} else {
				if ($update_fields != "") {
					$this->db->set ( $key, $update_fields );
					$updateflg = true;
				}
			}
		}
		return $updateflg;
	}
	function build_search_opensips($opensips_db_obj, $accounts_list_search) {
		if ($this->session->userdata ( 'advance_search' ) == 1) {
			$account_search = $this->session->userdata ( $accounts_list_search );
			unset ( $account_search ["ajax_search"] );
			unset ( $account_search ["advance_search"] );
			foreach ( $account_search as $key => $value ) {
				if ($value != "") {
					if (is_array ( $value )) {
						if (array_key_exists ( $key . "-integer", $value )) {
							$this->get_opensips_interger_array ( $opensips_db_obj, $key, $value [$key . "-integer"], $value [$key] );
						}
						if (array_key_exists ( $key . "-string", $value )) {
							$this->get_opensips_string_array ( $opensips_db_obj, $key, $value [$key . "-string"], $value [$key] );
						}
					} else {
						$opensips_db_obj->where ( $key, $value );
					}
				}
			}
		}
	}
	function get_opensips_interger_array($opensips_db_obj, $field, $value, $search_array) {
		if ($search_array != '') {
			switch ($value) {
				case "1" :
					$opensips_db_obj->where ( $field, $search_array );
					break;
				case "2" :
					$opensips_db_obj->where ( $field . ' <>', $search_array );
					break;
				case "3" :
					$opensips_db_obj->where ( $field . ' > ', $search_array );
					break;
				case "4" :
					$opensips_db_obj->where ( $field . ' < ', $search_array );
					break;
				case "5" :
					$opensips_db_obj->where ( $field . ' >= ', $search_array );
					break;
				case "6" :
					$opensips_db_obj->where ( $field . ' <= ', $search_array );
					break;
			}
		}
	}
	function get_opensips_string_array($opensips_db_obj, $field, $value, $search_array) {
		if ($search_array != '') {
			switch ($value) {
				case "1" :
					$opensips_db_obj->like ( $field, $search_array );
					break;
				case "2" :
					$opensips_db_obj->not_like ( $field, $search_array );
					break;
				case "3" :
					$opensips_db_obj->where ( $field, $search_array );
					break;
				case "4" :
					$opensips_db_obj->where ( $field . ' <>', $search_array );
					break;
				case "5" :
					$str1 = $field . " LIKE '" . $search_array . "%'";
					$opensips_db_obj->where ( $str1 );
					break;
				case "6" :
					$str1 = $field . " LIKE '%" . $search_array . "'";
					$opensips_db_obj->where ( $str1 );
					break;
			}
		}
	}

	function build_dropdown_invoices($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		$select_params = explode ( ',', $select );
		if (isset ( $select_params [3] )) {
			$cnt_str = " $select_params[1],' ',$select_params[2],' ','(',$select_params[3],')' ";
		} else {
			$cnt_str = " $select_params[1],' (',$select_params[2],')' ";
		}
		$select = $select_params [0] . ", concat($cnt_str) as $select_params[1] ," . $select_params [4];
		$logintype = $this->session->userdata ( 'logintype' );
		if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr') {
			$account_data = $this->session->userdata ( "accountinfo" );
			$id_value ['reseller_id'] = $account_data ['id'];
		}
		$where = $id_value;
		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		$drp_list = array ();
		foreach ( $drp_array as $drp_value ) {
			if($select_params[1] == 'first_name' && $select_params[2] == 'last_name'){
				$company_info = (array)$this->db->get_where($table,array("id"=>$drp_value->id))->first_row();
				if (!empty($company_info['company_name'])) {
					$drp_value->first_name = $company_info['company_name'] . '('.$company_info['number'].' )';
				}else{
					$drp_value->first_name = $company_info['first_name'] . ' ' . $company_info['last_name'] . ' ' .'('.$company_info['number'] .')';
				}
			}else{
				$drp_value->first_name = $company_info['first_name'] . ' ' . $company_info['last_name'] . ' ' .'('.$company_info['number'] .')';
			}
			if ($drp_value->type == 3) {
				$drp_list ['Provider'] [$drp_value->id] = $drp_value->first_name;
			} elseif ($drp_value->type == 1) {
				$drp_list ['Reseller'] [$drp_value->id] = $drp_value->first_name;
			} else {
				$drp_list ['Customer'] [$drp_value->id] = $drp_value->first_name;
			}
		}
		ksort ( $drp_list );
		return $drp_list;
	}
	
	function build_dropdown_for_alarm_module(){
        $drp_list = array();
        $trunkdata = $this->getSelect( 'id,name' ,'trunks',array("status"=>0));

            if ($trunkdata->num_rows > 0) {
                $trunk_table_data=$trunkdata->result_array();
                foreach ($trunk_table_data as $value) {
                    $drp_list['Trunk']['trunk_'.$value['id']] =  $value['name'];
                }
            }
         return $drp_list; 
    }
    function build_dropdown_for_alarm_report_module(){
        $drp_list = array();
        $trunkdata = $this->getSelect( 'id,name' ,'trunks',"");

            if ($trunkdata->num_rows > 0) {
                $trunk_table_data=$trunkdata->result_array();
                foreach ($trunk_table_data as $value) {
                    $drp_list['Trunk']['trunk_'.$value['id']] =  $value['name'];
                }
            }
         return $drp_list; 
    }
    function build_dropdown_origination_rate($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		$where = '';
		if (isset ( $id_value ["type"] ) && $id_value ["type"] == "GLOBAL") {
			$where = "type IN ('0','3')";
			$this->db->where ( $where );
			unset ( $id_value ["type"] );
		}
		if ($id_where != '' && $id_value != '') {
			if ($id_where == 'group_by') {
				$this->db->group_by ( $id_value );
			} else if ($id_where == "where_arr") {
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr' && $this->db->field_exists ( 'reseller_id', $table )) {
					$id_value ['reseller_id'] = $this->session->userdata ["accountinfo"] ['id'];
				}
				$where = $id_value;
			} else {
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'reseller_id') {
					$account_data = $this->session->userdata ( "accountinfo" );
					$id_value = $account_data ['id'];
				}
				$where = array (
						$id_where => $id_value
				);
			}
		}

		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		$drp_list = array ();
		$drp_list [0] = "--Select--";
		foreach ( $drp_array as $drp_value ) {
			$drp_list [$drp_value->{$select_params [0]}] = $drp_value->{$select_params [1]};
		}
		return $drp_list;
	}


	function build_concat_dropdown_reseller($select, $table, $id_where = '', $id_value = '') {
        $account_data = $this->session->userdata("accountinfo");
		$where=array();
		if($account_data['type'] == -1 || $account_data['type'] == 2){
			$where = array('type' =>'1','status'=>'0','deleted'=>'0');
		}
        $select_params = explode(',', $select);
        $select_params = explode(',', $select);
        if (isset($select_params[3])) {
            $cnt_str = " $select_params[1],' ',$select_params[2],' ','(',$select_params[3],')' ";
        } else {
            $cnt_str = " $select_params[1],' (',$select_params[2],')' ";
        }
        $select = $select_params[0] . ", concat($cnt_str) as $select_params[1] ";
        $logintype = $this->session->userdata('logintype');
        if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr') {
		
            $id_value = $account_data['id'];
			$where = array('reseller_id'=>$id_value,'type' =>'1','status'=>'0','deleted'=>'0');
		
        }
        $drp_array = $this->getSelect($select, $table, $where);
        $drp_array = $drp_array->result();
        $drp_list = array();
	if($account_data['type'] == -1 || $account_data['type'] == 2){
	$drp_list[0]='Admin';
	}
	if (($logintype == 1 || $logintype == 5) && $select_params[1] == 'first_name' && $select_params[2] == 'last_name') {
		$this->db->where('id', $id_value);
		$reseller_drp = $this->getSelect('*', $table, '')->row_array();
		$drp_list[$reseller_drp['id']] = isset($reseller_drp['company_name']) && $reseller_drp['company_name'] != '' ? $reseller_drp['company_name'] . '('.$reseller_drp['number'].' )' : $reseller_drp['first_name'] . ' ' . $reseller_drp['last_name'] . ' ' .'('.$reseller_drp['number'] .')';
	}
        foreach ($drp_array as $drp_value) {
        	if($select_params[1] == 'first_name' && $select_params[2] == 'last_name'){
				$company_info = (array)$this->db->get_where($table,array("id"=>$drp_value->id))->first_row();
				if (!empty($company_info['company_name'])) {
					$drp_value->first_name = $company_info['company_name'] . '('.$company_info['number'].' )';
				}else{
					$drp_value->first_name = $company_info['first_name'] . ' ' . $company_info['last_name'] . ' ' .'('.$company_info['number'] .')';
				}
			}else{
				$drp_value->first_name = $company_info['first_name'] . ' ' . $company_info['last_name'] . ' ' .'('.$company_info['number'] .')';
			}
            $drp_list[$drp_value->{$select_params [0]}] = $drp_value->{$select_params[1]};
        }
        return $drp_list;
	}
	function build_concat_dropdown_only_reseller($select, $table, $id_where = '', $id_value = '') {
        $account_data = $this->session->userdata("accountinfo");
		$where=array();
		if($account_data['type'] == -1){
			$where = array('type' =>'1','status'=>'0','deleted'=>'0','reseller_id'=>'0');
		}
        $select_params = explode(',', $select);
        $select_params = explode(',', $select);
        if (isset($select_params[3])) {
            $cnt_str = " $select_params[1],' ',$select_params[2],' ','(',$select_params[3],')' ";
        } else {
            $cnt_str = " $select_params[1],' (',$select_params[2],')' ";
        }
        $select = $select_params[0] . ", concat($cnt_str) as $select_params[1] ";
        $logintype = $this->session->userdata('logintype');
	
        if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr') {
		
			$where = array('reseller_id'=>'0','type' =>'1','status'=>'0','deleted'=>'0');
		
        }
        
        $drp_array = $this->getSelect($select, $table, $where);
        $drp_array = $drp_array->result();
        $drp_list = array();
	if($account_data['type'] == -1){
	$drp_list[0]='Admin';
	}
        foreach ($drp_array as $drp_value) {
        	if($select_params[1] == 'first_name' && $select_params[2] == 'last_name'){
				$company_info = (array)$this->db->get_where($table,array("id"=>$drp_value->id))->first_row();
				if (!empty($company_info['company_name'])) {
					$drp_value->first_name = $company_info['company_name'] . '('.$company_info['number'].' )';
				}else{
					$drp_value->first_name = $company_info['first_name'] . ' ' . $company_info['last_name'] . ' ' .'('.$company_info['number'] .')';
				}
			}else{
				$drp_value->first_name = $company_info['first_name'] . ' ' . $company_info['last_name'] . ' ' .'('.$company_info['number'] .')';
			}
            $drp_list[$drp_value->{$select_params [0]}] = $drp_value->{$select_params[1]};
        }
        return $drp_list;
    }

    function build_concat_dropdown_reseller_order($select, $table, $id_where = '', $id_value = '') {

        $account_data = $this->session->userdata("accountinfo");
	$select_params = explode(',', $select);
        $logintype = $this->session->userdata('logintype');
	$where=array();
        if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr') {
		
            $id_value = $account_data['id'];
			 $where ="(accountid = ".$id_value." OR reseller_id =".$id_value.")";
        }

        $drp_array = $this->getSelect($select, $table, $where);
        $drp_array = $drp_array->result();
        $drp_list = array();

        foreach ($drp_array as $drp_value) {
            $drp_list[$drp_value->{$select_params [0]}] = $drp_value->{$select_params[1]};
        }
        return $drp_list;
    }

    /** products a reseller works with - its own and those it opted in (not all products). */
    function build_dropdown_reseller_products($select, $table, $id_where = '', $id_value = '') {
        $account_data = $this->session->userdata("accountinfo");
        $me = (int) $account_data['id'];
        $select_params = explode(',', $select);
        $this->db->select($select, false);
        $this->db->from('products');
        $this->db->where("is_deleted = 0 AND (reseller_id = $me OR id IN (SELECT product_id FROM reseller_products WHERE account_id = $me))", NULL, FALSE);
        $drp_list = array();
        foreach ($this->db->get()->result() as $drp_value) {
            $drp_list[$drp_value->{$select_params [0]}] = $drp_value->{$select_params[1]};
        }
        return $drp_list;
    }
	function build_concat_dropdown_cutomer_reseller($select, $table, $id_where = '', $id_value = '') {
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$reseller_id=$accountinfo['id'];
		$this->db->where_in('type',array("0","1"));
		$this->db->where('deleted',"0");
		$account_arr = $reseller_arr = $final_array = array();
		$dropdown_params= array("name" => "accountid" ,"id" => "accountid_search_drp", "class" => "col-md-12 form-control selectpicker form-control-lg accountid_search_drp col-md-3");
		$final_array = array(""=>"--Select--");
		$account_result =$this->db->get_where('accounts',array("reseller_id"=>$reseller_id,"status"=>0));
		if($account_result->num_rows () > 0)
		{
			$final_array = array();
			$account_result = $account_result -> result_array();
			foreach ($account_result as $key=>$value) {
					if($value['type'] ==1){
						$reseller_arr[$value['id']] =  $value['first_name']." ".$value['last_name']."( ".$value['number']." )";
					}else{
						$account_arr[$value['id']] =  $value['first_name']." ".$value['last_name']."( ".$value['number']." )";
					}
			}
			if(!empty($reseller_arr))
				$final_array['Reseller'] = $reseller_arr;
			if(!empty($account_arr))
				$final_array['Customer'] = $account_arr; 
		}
		return $final_array;
	}
	function build_dropdown_languages($select, $table, $id_where = '', $id_value = '') {
		$drp_array = $this->getSelect('name', 'languages', '');
		$drp_array = $drp_array->result();
		$drp_list = array();	
		foreach ($drp_array as $drp_value) {
	   		$drp_list[$drp_value->name] = $drp_value->name;
		}
		return $drp_list;
	}
	function build_dropdown_country_camel($select, $table, $id_where = '', $id_value = '') { 
		$select_params = explode ( ',', $select );
		$where = '';
		if (isset ( $id_value ["type"] ) && $id_value ["type"] == "GLOBAL") {
			$where = "type IN ('0','3')";
			$this->db->where ( $where );
			unset ( $id_value ["type"] );
		}

		if ($id_where != '' && $id_value != '') {   
			if ($id_where == 'group_by') {  
				$this->db->group_by ( $id_value );
			} else if ($id_where == "where_arr") { 
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr' && $this->db->field_exists ( 'reseller_id', $table )) {
					if($table != 'taxes'){
						$id_value ['reseller_id'] = $this->session->userdata ["accountinfo"] ['id'];
					} else {
						// taxes of the admin and the own ones, not those of other resellers
						$this->db->where_in ( 'reseller_id', array ( 0, ( int ) $this->session->userdata ["accountinfo"] ['id'] ) );
					}
				}
				$where = $id_value;

			} else { 
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'reseller_id') {
					$account_data = $this->session->userdata ( "accountinfo" );
					$id_value = $account_data ['id'];
				}
				$where = array (
						$id_where => $id_value
				);
			}
		}
		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		$drp_list = array ();
		foreach ( $drp_array as $drp_value ) {
			$drp_list [$drp_value->{$select_params [0]}] = ucwords(strtolower(gettext($drp_value->{$select_params [1]})));
		}
		return $drp_list;
	}
	function build_concat_dropdown_currency($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode ( ',', $select );
		if (isset ( $select_params [3] )) {
			$cnt_str = " $select_params[1],' ',$select_params[2],' ','(',$select_params[3],')' ";
		} else {
			$cnt_str = " $select_params[1],' (',$select_params[2],')' ";
		}
		// $select = $select_params [0] . ", concat($cnt_str) as $select_params[1] ";
		$logintype = $this->session->userdata ( 'logintype' );
		if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr') {
			$account_data = $this->session->userdata ( "accountinfo" );
			$id_value ['reseller_id'] = $account_data ['id'];
		}
		if (isset ( $id_value ['type'] ) && $id_value ['type'] == '0,3') {
			$twhere = "type IN (" . $id_value ["type"] . ")";
			$this->db->where ( $twhere );
			unset ( $id_value ['type'] );
		}
		$where = $id_value;
		//copy of build_concat_dropdown function just added below line
		$this->db->order_by('currencyname','asc');//end
		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();

		$drp_list = array ();
		foreach ( $drp_array as $drp_value ) {
			$drp_list [$drp_value->{$select_params [0]}] = gettext($drp_value->{$select_params [1]}). ' ('. $drp_value->{$select_params [2]}.')';
		}
		return $drp_list;
	}
	function build_dropdown_timezone($select, $table, $id_where = '', $id_value = '') { 
		$select_params = explode ( ',', $select );
		$where = '';
		if (isset ( $id_value ["type"] ) && $id_value ["type"] == "GLOBAL") {
			$where = "type IN ('0','3')";
			$this->db->where ( $where );
			unset ( $id_value ["type"] );
		}

		if ($id_where != '' && $id_value != '') {   
			if ($id_where == 'group_by') {  
				$this->db->group_by ( $id_value );
			} else if ($id_where == "where_arr") { 
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'where_arr' && $this->db->field_exists ( 'reseller_id', $table )) {
					if($table != 'taxes'){
						$id_value ['reseller_id'] = $this->session->userdata ["accountinfo"] ['id'];
					} else {
						// taxes of the admin and the own ones, not those of other resellers
						$this->db->where_in ( 'reseller_id', array ( 0, ( int ) $this->session->userdata ["accountinfo"] ['id'] ) );
					}
				}
				$where = $id_value;

			} else { 
				$logintype = $this->session->userdata ( 'logintype' );
				if (($logintype == 1 || $logintype == 5) && $id_where == 'reseller_id') {
					$account_data = $this->session->userdata ( "accountinfo" );
					$id_value = $account_data ['id'];
				}
				$where = array (
						$id_where => $id_value
				);
			}
		}
		$drp_array = $this->getSelect ( $select, $table, $where );
		$drp_array = $drp_array->result ();
		$drp_list = array ();
		foreach ( $drp_array as $drp_value ) {
			// $drp_list [$drp_value->{$select_params [0]}] = $drp_value->{$select_params [1]};
			$drp_list [$drp_value->{$select_params [0]}] = gettext($drp_value->{$select_params [1]});
		}
		return $drp_list;
	}
}

?>
