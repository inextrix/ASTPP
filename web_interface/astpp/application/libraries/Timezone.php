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
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
class Timezone {
	function __construct() {
		$this->CI = & get_instance ();
		$this->CI->load->library ( 'session' );
		$this->CI->load->database ();
	}
	function uset_timezone() {
		$account_data = $this->CI->session->userdata ( 'accountinfo' );
		return $account_data ['timezone_id'];
	}
	// ASTPPCOM-891 Ashish start
	function display_GMT($currDate, $fulldate = 1, $timezone_id = "",$table_key = '') {
	// ASTPPCOM-891 Ashish end
		$number = ($timezone_id == "") ? $this->uset_timezone () : $timezone_id;
		// ASTPPCOM-891 Ashish start
		$SERVER_GMT = ($table_key == "livecall") ? $this->get_server_timezone(): '0' ;
		// ASTPPCOM-891 Ashish end
		
		$result = $this->CI->db->query ( "select gmtoffset from timezone where id =" . $number );
		$timezone_offset = $result->result ();
		
		$USER_GMT = $timezone_offset ['0']->gmtoffset;
		
		$date_time_array = getdate ( strtotime ( $currDate ) );
		
		$hours = $date_time_array ['hours'];
		$minutes = $date_time_array ['minutes'];
		$seconds = $date_time_array ['seconds'];
		$month = $date_time_array ['mon'];
		$day = $date_time_array ['mday'];
		$year = $date_time_array ['year'];
		$timestamp = mktime ( $hours, $minutes, $seconds, $month, $day, $year );
		
		$timestamp = $timestamp + ($USER_GMT - $SERVER_GMT);
		if ($fulldate == 1) {
			$date = date ( "Y-m-d H:i:s", $timestamp );
		} else {
			$date = date ( "Y-m-d", $timestamp );
		}
		
		return $date;
	}
	function convert_to_GMT_new($currDate, $fulldate = 1, $timezone_id = '') {
		$number = ($timezone_id == "") ? $this->uset_timezone () : $timezone_id;
		// ASTPPCOM-891 Ashish start
		$SERVER_GMT = $this->get_server_timezone();
		// ASTPPCOM-891 Ashish End
		$result = $this->CI->db->query ( "select gmtoffset,timezone_name from timezone where id =" . $number );
		$timezone_offset = $result->result ();
		$USER_GMT = $timezone_offset ['0']->gmtoffset;
		$date_time_array = getdate ( strtotime ( $currDate ) );
		$hours = $date_time_array ['hours'];
		$minutes = $date_time_array ['minutes'];
		$seconds = $date_time_array ['seconds'];
		$month = $date_time_array ['mon'];
		$day = $date_time_array ['mday'];
		$year = $date_time_array ['year'];
		$timestamp = mktime ( $hours, $minutes, $seconds, $month, $day, $year );
		$timestamp = $timestamp - ($USER_GMT);
		if($fulldate == 1) {
			$date = date ( "Y-m-d H:i:s", $timestamp );
		} else {
			$date = date ( "Y-m-d", $timestamp );
		}
		return $date;
	}
	
	function convert_to_GMT($currDate, $fulldate = 1, $timezone_id = '') {
		$number = ($timezone_id == "") ? $this->uset_timezone () : $timezone_id;
		// ASTPPCOM-891 Ashish start
		$SERVER_GMT = $this->get_server_timezone();
		// ASTPPCOM-891 Ashish end
		$result = $this->CI->db->query ( "select gmtoffset from timezone where id =" . $number );
		$timezone_offset = $result->result ();
		$USER_GMT = $timezone_offset ['0']->gmtoffset;
		
		$date_time_array = getdate ( strtotime ( $currDate ) );
		$hours = $date_time_array ['hours'];
		$minutes = $date_time_array ['minutes'];
		$seconds = $date_time_array ['seconds'];
		$month = $date_time_array ['mon'];
		$day = $date_time_array ['mday'];
		$year = $date_time_array ['year'];
		$timestamp = mktime ( $hours, $minutes, $seconds, $month, $day, $year );
		$timestamp = $timestamp - ($SERVER_GMT - $USER_GMT);

		if ($fulldate == 1) {
			$date = date ( "Y-m-d H:i:s", $timestamp );
		} else {
			$date = date ( "Y-m-d", $timestamp );
		}
		return $date;
	}
	function get_login_type_timezone(){
		$accountinfo = $this->CI->session->userdata('accountinfo');
        $timezone_name = $this->CI->common->get_field_name('timezone_name','timezone',array("id" =>$accountinfo['timezone_id']));
        $date = new DateTime("now", new DateTimeZone($timezone_name));
        return $date->format('Y-m-d');
	}
	// ASTPPCOM-891 Ashish start
	function get_server_timezone(){
		$server_time_zone = exec("timedatectl | grep Time");
		$server_time_zone = explode(":",$server_time_zone);
		$server_time_zone = explode(" ",$server_time_zone[1]);
		$result = $this->CI->db->query ( "select gmtoffset from timezone where timezone_name = '".$server_time_zone[1]."'" );
		$timezone_offset = $result->result ();
		
		$SERVER_GMT = $timezone_offset ['0']->gmtoffset;
		return $SERVER_GMT;
	}
	// ASTPPCOM-891 Ashish end
}
?>
