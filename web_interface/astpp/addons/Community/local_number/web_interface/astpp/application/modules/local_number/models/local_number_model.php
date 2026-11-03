<?php
// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
//
// Copyright (C) 2016 Inextrix Technologies Pvt. Ltd.
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
class local_number_model extends CI_Model {
   	function __construct() {
		parent::__construct ();
	}
	function get_local_number_list($flag, $start = 0, $limit = 0) {
		$this->db_model->build_search ( 'local_number_list_search' );
		$where = array (
				//"status" => "0" 
		);
		if ($flag) {
			$query = $this->db_model->select ( "*", "local_number", $where, "id", "ASC", $limit, $start );
		} else {
			$query = $this->db_model->countQuery ( "*", "local_number", $where );
		}
		return $query;
	}

	function get_local_number_list_customer($flag, $start = 0, $limit = 0) {

		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$this->db_model->build_search ( 'local_number_list_search' );
		$where_arr = array (
				"local_number_destination.account_id" => $accountinfo['id']
		);
		if ($flag) {
			$query = $this->db_model->getJionQuery('local_number_destination', 'local_number_destination.id,local_number_destination.destination_name,local_number_destination.destination_number,local_number.country_id,local_number.province,local_number.city,local_number_destination.local_number_id,local_number_destination.creation_date',$where_arr, 'local_number','local_number_destination.local_number_id=local_number.id', 'inner',$limit, $start,'','');

			// echo $this->db->last_query();die();

		} else {
			$query = $this->db_model->getJionQueryCount('local_number_destination', '*',$where_arr, 'local_number','local_number_destination.local_number_id=local_number.id', 'inner','', '','','');
		}
		//echo $this->db->last_query();exit;
		return $query;
	}


	function add_local_number($add_array) {


		unset ( $add_array ["action"] );
		unset ( $add_array ["id"] );
		$add_array ['created_date'] = gmdate ( 'Y-m-d H:i:s' );
		// echo "<pre>";
		// print_r($add_array);die();
		$this->db->insert ( "local_number", $add_array );
		return true;
	}
	function edit_local_number($data, $id) {
		unset ( $data ["action"] );
		$data ['last_modified_date'] = gmdate ( 'Y-m-d H:i:s' );
		$this->db->where ( "id", $id );
		$this->db->update ( "local_number", $data );
	}
	function edit_local_number_destination($data, $id) {

//harsh s todo
		// print_r($data);die;

		$accountinfo = $this->session->userdata ( 'accountinfo' );
		unset ( $data ["action"] );
		unset ( $data ["id"] );

		$data ['last_modified_date'] = gmdate ( 'Y-m-d H:i:s' );
		$this->db->where ( "id", $id );
		$this->db->update ( "local_number_destination", $data );
		$local_number_id = $this->db->query("select local_number_id from local_number_destination where id = '$id'");
		$local_number_id = $local_number_id->first_row();
		$local_number_id = $local_number_id->local_number_id;
		

		$local_number    = $this->db->query("select number from local_number where id = '$local_number_id'");
		$local_number    = $local_number->first_row();

		$speed_dial['speed_num'] = $local_number->number;
		$speed_dial['number']    = $data['destination_number'];
		$speed_dial['accountid'] = $accountinfo['id'];

		$this->db->where('speed_num', $local_number->number);
  		$this->db->delete('speed_dial');

		// $this->db->insert('speed_dial',$speed_dial);
		$this->db->insert('speed_dial',$speed_dial);
	}

	function edit_local_number_destination_admin($data, $id){


		$accountinfo['id'] = $data['user_edit_id'];
		unset ( $data ["user_edit_id"] );
		unset ( $data ["action"] );
		unset ( $data ["id"] );

		$data ['last_modified_date'] = gmdate ( 'Y-m-d H:i:s' );
		$this->db->where ( "id", $id );
		$this->db->update ( "local_number_destination", $data );
		$local_number_id = $this->db->query("select local_number_id from local_number_destination where id = '$id'");
		$local_number_id = $local_number_id->first_row();
		$local_number_id = $local_number_id->local_number_id;
		

		$local_number    = $this->db->query("select number from local_number where id = '$local_number_id'");
		$local_number    = $local_number->first_row();

		$speed_dial['speed_num'] = $local_number->number;
		$speed_dial['number']    = $data['destination_number'];
		$speed_dial['accountid'] = $accountinfo['id'];

		$this->db->where('speed_num', $local_number->number);
  		$this->db->delete('speed_dial');

		// $this->db->insert('speed_dial',$speed_dial);
		$this->db->insert('speed_dial',$speed_dial);


	}

	function edit_local_number_destinations($data, $id) {
		unset ( $data ["action"] );
		$data ['last_modified_date'] = gmdate ( 'Y-m-d H:i:s' );
		$this->db->where ( "id", $id );
		$this->db->update ( "local_number", $data );
	}
	function remove_local_number($id) {
		$this->db->where ( "id", $id );
		$this->db->delete ( 'local_number' );
		return true;
	}



	function add_local_number_customer($add_array) {

		unset ( $add_array ["action"] );
		unset ( $add_array ["id"] );
		// print_r($add_array);die();
	//	$add_array ['created_date'] = gmdate ( 'Y-m-d H:i:s' );
		// echo "<pre>";
		// print_r($add_array);die();
		$this->db->insert ( "local_number_destination", $add_array );

		return true;
	}
	function edit_local_number_customer($data, $id) {
		unset ( $data ["action"] );
		$data ['last_modified_date'] = gmdate ( 'Y-m-d H:i:s' );
		$this->db->where ( "id", $id );
		$this->db->update ( "local_number_destination", $data );
	}
	function remove_local_number_customer($id) {
		$this->db->where ( "id", $id );
		$this->db->delete ( 'local_number_destination' );
		return true;
	}
	function bulk_insert_local_number($field_value) {
		$this->db->insert_batch ( 'local_number', $field_value );
		$affected_row = $this->db->affected_rows ();
		return $affected_row;
	}
}
