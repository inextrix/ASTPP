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

// Parse user and rates array which we got in cdr xml
function parse_rates_array($xml_rate, $constant_array, $logger) {
	$rates_array = array ();
	
	// decode string using urldecode
	$xml_rate = urldecode ( $xml_rate );
	$xml_rate_array = explode ( "||", $xml_rate );
	
	foreach ( $xml_rate_array as $rate_key => $rate_value ) {
		$rates_array = explode ( "|", $rate_value );
		
		$user_id_param = $rates_array [astpp_count ( $rates_array ) - 1];
		$user_id = (substr ( $user_id_param, 0, 3 ) == 'UID') ? substr ( $user_id_param, 3 ) : 0;
		
		foreach ( $rates_array as $key => $value ) {
			$rates_array_info [$user_id] [$constant_array [substr ( $value, 0, 3 )]] = substr ( $value, 3 );
		}
	}
	return $rates_array_info;
}

// Process package
function process_package($xml_cdr, $user_id, $rates_array, $logger, $db) {
	$duration = $xml_cdr->variables->duration;
	$xml_cdr->variables->package_id = 0;
	$flag = false;
	if (astpp_gt($duration, 0) && $xml_cdr->variables->call_direction == 'outbound') {
		$destination_number = $xml_cdr->variables->effective_destination_number;
		
		$number_len = strlen ( $destination_number );
		$number_loop_str = '(';
		while ( astpp_gt($number_len, 0) ) {
			$number_loop_str .= " code='" . substr ( $destination_number, 0, $number_len ) . "' OR ";
			$number_len = astpp_n($number_len) - 1;
		}
		$number_loop_str .= " code='--')";
		
		$query = "SELECT A.id as package_id,code,includedseconds FROM tbl_package AS A ,tbl_package_codes AS B WHERE " . $number_loop_str . " AND B.package_id = A.id AND A.ratecard_id=" . $rates_array ['ratecard_id'] . " AND A.status=0 AND A.is_del=0 ORDER BY length(code) DESC";
		$logger->log ( "Package Query : " . $query );
		$res_package = $db->run ( $query );
		
		foreach ( $res_package as $res_package_key => $package_info ) {
			if (isset ( $package_info ['package_id'] )) {
				$query = "SELECT SUM(used_seconds) as used_seconds FROM tbl_package_usage WHERE code=" . $package_info ['code'] . " AND package_id=" . $package_info ['package_id'] . " AND user_id=" . $user_id;
				$logger->log ( "Package usage Query : " . $query );
				$res_pkg_usg = $db->run ( $query );
				$package_usage_info = $res_pkg_usg [0];
				
				$used_seconds = (isset ( $package_usage_info ['used_seconds'] )) ? $package_usage_info ['used_seconds'] : 0;
				
				$logger->log ( "Included seconds : " . $package_info ['includedseconds'] . ", Used seconds : " . $used_seconds );
				if ($package_info ['includedseconds'] > $used_seconds) {
					$remaining_seconds = astpp_n($package_info ['includedseconds']) - (astpp_n($duration) + astpp_n($used_seconds));
					if (astpp_gt($remaining_seconds, 0)) {
						$dud_sec = $duration;
						$duration = 0;
					} else {
						$dud_sec = astpp_n($duration) - astpp_abs ( $remaining_seconds );
						$duration = astpp_abs ( $remaining_seconds );
					}
					$flag = true;
					$xml_cdr->variables->package_id = $package_info ['package_id'];
					
					$query = "INSERT INTO tbl_package_usage (package_id,user_id,code,used_seconds) VALUES (" . $package_info ['package_id'] . "," . $user_id . ",'" . $package_info ['code'] . "'," . $dud_sec . ") ON DUPLICATE KEY UPDATE used_seconds=used_seconds+" . $dud_sec;
					$logger->log ( "Package Usage Query : " . $query );
					$db->run ( $query );
					
					break;
				}
			}
		}
	}
	return array (
			$duration,
			$flag 
	);
}

// Process user/vendor cdr
function do_cdr_process($xml_cdr, $debit, $cost, $vendor_cost, $rates_array, $parent_id, $parent_rates, $carrier_rates_array, $logger, $db) {
	$query_string = "'" . $xml_cdr->variables->uuid . "','" . $xml_cdr->variables->user_id . "','" . $xml_cdr->variables->entity_id . "','" . urldecode ( $xml_cdr->variables->effective_caller_id_name ) . "','" . $xml_cdr->variables->effective_caller_id_number . "','" . $xml_cdr->variables->effective_destination_number . "'," . $xml_cdr->variables->duration . ",'" . $xml_cdr->variables->carrier_id . "','" . $xml_cdr->callflow [0]->caller_profile->originatee->originatee_caller_profile->network_addr . "','" . $xml_cdr->variables->sip_contact_host . "','" . $xml_cdr->variables->hangup_cause . "','" . urldecode ( $xml_cdr->variables->start_stamp ) . "'," . $debit . "," . $cost . ",'" . $xml_cdr->variables->vendor_id . "'," . $vendor_cost . "," . $rates_array ['ratecard_id'] . "," . $xml_cdr->variables->package_id . ",'" . $rates_array ['code'] . "','" . $rates_array ['destination'] . "','" . $rates_array ['cost'] . "','" . $parent_id . "','" . @$parent_rates ['code'] . "','" . @$parent_rates ['destination'] . "','" . @$parent_rates ['cost'] . "','" . @$carrier_rates_array ['code'] . "','" . @$carrier_rates_array ['destination'] . "','" . @$carrier_rates_array ['cost'] . "','" . $xml_cdr->variables->call_direction . "','" . urldecode ( $xml_cdr->variables->profile_start_stamp ) . "','" . urldecode ( $xml_cdr->variables->answer_stamp ) . "','" . urldecode ( $xml_cdr->variables->bridge_stamp ) . "','" . urldecode ( $xml_cdr->variables->progress_stamp ) . "','" . urldecode ( $xml_cdr->variables->progress_media_stamp ) . "','" . urldecode ( $xml_cdr->variables->end_stamp ) . "'," . $xml_cdr->variables->billmsec . "," . $xml_cdr->variables->answermsec . "," . $xml_cdr->variables->waitmsec . "," . $xml_cdr->variables->progress_mediamsec . "," . $xml_cdr->variables->flow_billmsec;
	
	$query = "INSERT INTO tbl_cdrs (uniqueid,user_id,entity_id,callerid_name,callerid_number,dstnum,duration,carrier_id,carrierip,callerip,disposition,start_stamp,debit,cost,vendor_id,vendor_cost,ratecard_id,package_id,rate_code,rate_code_destination,rate_cost,parent_id,parent_code,parent_code_destination,parent_cost,carrier_code,carrier_code_destination ,carrier_cost,call_direction,profile_start_stamp,answer_stamp,bridge_stamp,progress_stamp,progress_media_stamp,end_stamp,billmsec,answermsec,waitmsec,progress_mediamsec,flow_billmsec) values ($query_string)";
	
	$logger->log ( "CDR Query : " . $query );
	$db->run ( $query );
}

// Process reseller cdr
function do_reseller_cdr_process($xml_cdr, $debit, $cost, $rates_array, $parent_id, $parent_rates, $logger, $db) {
	$query_string = "'" . $xml_cdr->variables->uuid . "','" . $xml_cdr->variables->user_id . "','" . urldecode ( $xml_cdr->variables->effective_caller_id_name ) . "','" . $xml_cdr->variables->effective_caller_id_number . "','" . $xml_cdr->variables->effective_destination_number . "'," . $xml_cdr->variables->duration . ",'" . $xml_cdr->variables->hangup_cause . "','" . urldecode ( $xml_cdr->variables->start_stamp ) . "'," . $debit . "," . $cost . "," . $rates_array ['ratecard_id'] . "," . $xml_cdr->variables->package_id . ",'" . $rates_array ['code'] . "','" . $rates_array ['destination'] . "','" . $rates_array ['cost'] . "','" . $parent_id . "','" . @$parent_rates ['code'] . "','" . @$parent_rates ['destination'] . "','" . @$parent_rates ['cost'] . "','" . $xml_cdr->variables->call_direction . "'";
	
	$query = "INSERT INTO tbl_cdrs_reseller (uniqueid,reseller_id,callerid_name,callerid_number,dstnum,duration,disposition,start_stamp,debit,cost,ratecard_id,package_id,rate_code,rate_code_destination,rate_cost,parent_id,parent_code,parent_code_destination,parent_cost,call_direction) values ($query_string)";
	
	$logger->log ( "CDR Query : " . $query );
	$db->run ( $query );
}

// Update user balance
/**
 *
 * @param integer $entity_id        	
 */
function update_balance($user_id, $amount, $entity_id, $logger, $db) {
	if (astpp_gt($amount, 0)) {
		$math_sign = (astpp_eq0($entity_id) || $entity_id == 1) ? '-' : '+';
		$query = "UPDATE tbl_users SET credit=credit$math_sign" . $amount . " WHERE id=" . $user_id;
		$logger->log ( "Balance update : " . $query );
		$db->run ( $query );
	}
}

// Get user info
function get_user_info($parent_id, $db) {
	$query = "SELECT * FROM tbl_users WHERE id=" . $parent_id;
	$res_user = $db->run ( $query );
	return $res_user [0];
}

?>
