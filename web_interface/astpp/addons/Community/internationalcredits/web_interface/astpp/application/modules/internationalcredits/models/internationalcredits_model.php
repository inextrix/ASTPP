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
class Internationalcredits_model extends CI_Model {
	function __construct() {
		parent::__construct ();
	}
		function get_internationalcredits_list($flag, $start = 0, $limit = 0) {
		$this->db_model->build_search ( 'internationalcredits_list_search' );
		if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {
			$account_data = $this->session->userdata ( "accountinfo" );
			$reseller = $account_data ['id'];
			$where = array (
					//"type" => "1,3"
						"deleted" => "0"
			);
		} else {
			$where = array (
					//"type" => "1,3"
						"deleted" => "0"
			);
		}
		//HP:
		$this->db->where_in('type',array(1,3));
		if ($flag) {
			$query = $this->db_model->select ( "*", "accounts", $where, "id", "ASC", $limit, $start );
		} else {
			$query = $this->db_model->countQuery ( "*", "accounts", $where );
		}
		return $query;
		}
		function get_customer_Account_list($flag, $start = 0, $limit = 0,$export=false) {
			$this->db_model->build_search ( 'internationalcredits_list_search' );
			if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {
				$account_data = $this->session->userdata ( "accountinfo" );
				$reseller = $account_data ['id'];
				$where = array (
					//		"type !=" => "-1,2",
							"deleted" => "0",
							"status" => "0",
							"reseller_id" =>$reseller,
						);
		} else {
			$where = array (
					//	"type !=" => "-1,2",
						"deleted" => "0",
						"status" => "0" 
						);
		}
		//HP:
		$this->db->where_not_in('type',array(-1,2));

		if ($flag) {
			$query = $this->db_model->select ( "*", "accounts", $where, "id", "ASC", $limit, $start );
		} else {
			$query = $this->db_model->countQuery ( "*", "accounts", $where );
		}
		return $query;
	}
	function add_recharge($add_array='') {
			$account_info = ( array ) $this->db->get_where ( 'accounts', array (
					"id" => $add_array ['accountid']
			) )->first_row ();
			$add_array ['int_balance'] = astpp_n($account_info['int_balance']) + astpp_n($add_array ['int_balance']);
			if(isset($add_array ['accountid']) && $add_array ['accountid'] != ''){
					$this->db->where('id', $add_array ['accountid']);
					unset($add_array ['accountid']);
					$this->db->update('accounts',$add_array);
					return true;
			}
	}
	function add_recharge_credit($add_array='') {
			$account_info = ( array ) $this->db->get_where ( 'accounts', array (
					"id" => $add_array ['accountid']
			) )->first_row ();
			$add_array ['int_credit_limit'] = astpp_n($account_info['int_credit_limit']) + astpp_n($add_array ['int_credit_limit']);
			if(isset($add_array ['accountid']) && $add_array ['accountid'] != '') {
					$this->db->where('id', $add_array ['accountid']);
					unset($add_array ['accountid']);
					$this->db->update('accounts',$add_array);
					return true;
			}
	}
}
