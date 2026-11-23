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
class Dashboard_model extends CI_Model {
	function __construct() {
		parent::__construct ();
	}
	function get_recent_recharge() {
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$userlevel_logintype = $this->session->userdata ( 'userlevel_logintype' );
		
		$where_arr = array (
				'payment_by' => - 1 
		);
		if ($userlevel_logintype == 1) {
			$where_arr = array (
					'payment_by' => $accountinfo ['id'] 
			);
		}
		if ($userlevel_logintype == 0 || $userlevel_logintype == 3) {
			$where_arr = array (
					'accountid' => $accountinfo ['id'] 
			);
		}
		$this->db->where ( $where_arr );
		$this->db->select ( 'id,accountid,credit,payment_date' );
		$this->db->limit ( 12 );
		$this->db->order_by ( 'payment_date', 'desc' );
		return $this->db->get ( 'payments' );
	}
	function get_call_statistics($table, $parent_id, $start_date = '', $end_date = '', $group_flag = true) {
		// ASTPPCOM-891 Ashish start
		$this->db->select ( "sum(total_calls) as sum,
                           SUM(total_answered_call) as answered,
                           MAX(mcd) AS mcd,
                           SUM(billseconds) AS duration,
                           SUM(total_fail_call) as failed,
                           SUM(billseconds) as billable,
                           sum(debit-cost) as profit,
                           sum(debit) as debit,
                           sum(cost) as cost,
                           SUM(total_answered_call) as completed,
                           calldate as day", false );
						// ASTPPCOM-891 Ashish end
		$this->db->where ( 'calldate >=', $start_date . " 00:00:00" );
		$this->db->where ( 'calldate <=', $end_date . " 23:59:59" );
		$this->db->where ( 'reseller_id', $parent_id );
		if ($group_flag)
			$this->db->group_by ( "DAY(calldate)" );
		$result = $this->db->get ( $table );
		return $result;
	}
	function get_customer_maximum_callminutes($start_date, $end_date) {
		$start_date = $start_date . " 00:00:00";
		$end_date = $end_date . " 23:59:59";
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$parent_id = ($accountinfo ['type'] == 1) ? $accountinfo ['id'] : 0;
		if ($this->session->userdata ( 'userlevel_logintype' ) != 0 && $this->session->userdata ( 'userlevel_logintype' ) != 3) {
			$where = "reseller_id ='$parent_id'";
		} else {
			$where = "accountid ='$parent_id'";
		}
		$where = $where . " AND calldate >= '" . $start_date . "' AND  calldate <= '" . $end_date . "'";
		$select_query = "SELECT sum( billseconds ) AS billseconds,account_id FROM (cdrs_day_by_summary) WHERE $where group by account_id order by sum(billseconds) desc limit 10";
		return $this->db->query ( $select_query );
	}
	
	function get_customer_maximum_countryminutes($start_date, $end_date) {
		$start_date = $start_date . " 00:00:00";
		$end_date = $end_date . " 23:59:59";
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$parent_id = ($accountinfo ['type'] == 1) ? $accountinfo ['id'] : 0;
		if ($this->session->userdata ( 'userlevel_logintype' ) != 0 && $this->session->userdata ( 'userlevel_logintype' ) != 3) {
			$where = "reseller_id ='$parent_id'";
		} else {
			$where = "accountid ='$parent_id'";
		}
		$where = $where . " AND country_id != '0' AND calldate >= '" . $start_date . "' AND  calldate <= '" . $end_date . "'";
		$select_query = "SELECT sum( billseconds ) AS billseconds,country_id FROM (cdrs_day_by_summary) WHERE $where group by country_id order by sum(billseconds) desc limit 10";
		
		return $this->db->query ( $select_query );
	}
	
	function get_customer_maximum_callcount($start_date, $end_date) {
		$start_date = $start_date . " 00:00:00";
		$end_date = $end_date . " 23:59:59";
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$parent_id = ($accountinfo ['type'] == 1) ? $accountinfo ['id'] : 0;
		if ($this->session->userdata ( 'userlevel_logintype' ) != 0 && $this->session->userdata ( 'userlevel_logintype' ) != 3) {
			$where = "reseller_id ='$parent_id'";
		} else {
			$where = "accountid ='$parent_id'";
		}
		$where = $where . " AND calldate >= '" . $start_date . "' AND  calldate <= '" . $end_date . "'";
	  $select_query = "SELECT SUM(total_calls) as call_count, `account_id` FROM (`cdrs_day_by_summary`) WHERE $where GROUP BY `account_id` ORDER BY `call_count` desc LIMIT 10";
	  
		return $this->db->query ( $select_query );
	}
	
	function get_customer_maximum_countrycount($start_date, $end_date) {
		$start_date = $start_date . " 00:00:00";
		$end_date = $end_date . " 23:59:59";
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$parent_id = ($accountinfo ['type'] == 1) ? $accountinfo ['id'] : 0;
		if ($this->session->userdata ( 'userlevel_logintype' ) != 0 && $this->session->userdata ( 'userlevel_logintype' ) != 3) {
			$where = "reseller_id ='$parent_id'";
		} else {
			$where = "accountid ='$parent_id'";
		}
		$where = $where . " AND country_id != '0' AND calldate >= '" . $start_date . "' AND  calldate <= '" . $end_date . "'";
	  $select_query = "SELECT SUM(total_calls) as call_count, `country_id` FROM (`cdrs_day_by_summary`) WHERE $where GROUP BY `country_id` ORDER BY `call_count` desc LIMIT 10";
	  
		return $this->db->query ( $select_query );
	}
}
?>
