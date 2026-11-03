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
class voice_broadcast_model extends CI_Model {
   	function __construct() {
		parent::__construct ();
	}
	function get_voice_broadcast_list($flag, $start = 0, $limit = 0) {
		$this->db_model->build_search ( 'voice_broadcast_list_search' );
		$where = array (
				//"status" => "0" 
		);
		if ($flag) {
			$query = $this->db_model->select ( "*", "voice_broadcast", $where, "id", "ASC", $limit, $start );
		} else {
			$query = $this->db_model->countQuery ( "*", "voice_broadcast", $where );
		}
		return $query;
	}

	function add_voice_broadcast($add_array) {
		unset ( $add_array ["action"] );
		unset ( $add_array ["id"] );
		unset ( $add_array ["reseller_id_search_drp"] );
		unset ( $add_array ["accountid_search_drp"] );
		unset ( $add_array ["sip_device_id_search_drp"] );
		$add_array ['created_date'] = gmdate ( 'Y-m-d H:i:s' );
		$this->db->insert ( "voice_broadcast", $add_array );
		return true;
	}

	function remove_voice_broadcast($id) {
		$this->db->where ( "id", $id );
		$this->db->delete ( 'voice_broadcast' );
		return true;
	}

}
