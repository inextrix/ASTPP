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

// Process CDR
function process_cdr($data, $db, $logger, $decimal_points, $config) {
	// $logger->log(print_r($data,true));//exit;
	
	// Initializing variables
	$origination_rate = array ();
	$termination_rate = array ();
	
	// FS CDR variables
	$dataVariable = $data ['variables'];
	
	// Use Milliseconds to calculate the billing of calls
	$dataVariable['billsec'] = ceil($dataVariable['billmsec'] / 1000);

	//Added condition to remove bad cdr entries	
	if ($dataVariable ['callstart'] == ""){return;}
	
	// FS CDR Call flow variables
	$dataCallflow = $data ['callflow'];
	
	// Get account id
	$accountid = isset ( $dataVariable ['account_id'] ) ? $dataVariable ['account_id'] : '0';
	if($accountid == '' || $accountid == '0'){
		return true;
	}
	$dataVariable ['origination_call_type'] = 0;
	// Get caller id name and number
	$dataVariable ['effective_caller_id_name'] = (isset ( $dataVariable ['effective_caller_id_name'] )) ? $dataVariable ['effective_caller_id_name'] : $dataCallflow ['caller_profile'] ['caller_id_name'];
	$dataVariable ['effective_caller_id_number'] = (isset ( $dataVariable ['effective_caller_id_number'] )) ? $dataVariable ['effective_caller_id_number'] : $dataCallflow ['caller_profile'] ['caller_id_number'];
	
	if ($dataVariable ['billsec'] == 0 && $dataVariable ['hangup_cause'] == 'NORMAL_CLEARING') {
		$hangup_cause = isset ( $dataVariable ['last_bridge_hangup_cause'] ) ? $dataVariable ['last_bridge_hangup_cause'] : $dataVariable ['hangup_cause'];
	} else {
		$hangup_cause = $dataVariable ['hangup_cause'];
	}
	
	if ($dataVariable ['error_cdr'] == '1') {
		// Get actual hangup cause
		$hangup_cause = (isset ( $dataVariable ['error_cdr'] )) ? $dataVariable ['last_bridge_hangup_cause'] : (isset ( $dataVariable ['last_bridge_hangup_cause'] ) ? $dataVariable ['last_bridge_hangup_cause'] : $dataVariable ['hangup_cause']);
	}
	
	/* #### PATCH FOR ONE WAY AUDIO #### */
	if ($hangup_cause == "NORMAL_UNSPECIFIED" && $dataVariable ['billsec'] > 0) {
		$hangup_cause = "NORMAL_CLEARING";
	}
	/* #### ************* END *************#### */
	
	// Don't thing this will be useful anytime. Need to remove it after testing.
	if ($hangup_cause == "NONE") {
		$hangup_cause = $dataVariable ['current_application_data'];
	}
	
	$dataVariable ['hangup_cause'] = $hangup_cause;
	
	/*
	 * if ( ($dataVariable['hangup_cause'] != 'NORMAL_CLEARING') && ($dataVariable['hangup_cause'] != 'ALLOTTED_TIMEOUT')) {
	 * $dataVariable['billsec'] = 0;
	 * }
	 */
	
	$account_type = (isset ( $dataVariable ['entity_id'] )) ? $dataVariable ['entity_id'] : '0';
	$parentid = isset ( $dataVariable ['parent_id'] ) ? $dataVariable ['parent_id'] : '0';
	$parent_cost = 0;
	$cost = 0;
//HP:	$dataVariable ['package_id'] = 0;
	$actual_call_direction = $dataVariable ['call_direction'];
	$actual_duration = $dataVariable ['billsec'];
	$dataVariable ['calltype'] = isset ( $dataVariable ['calltype'] ) ? $dataVariable ['calltype'] : "STANDARD";
	$actual_calltype = $dataVariable ['calltype'];
	
	// Normalize origination and termination rates array
	if (isset ( $dataVariable ['origination_rates'] ))
		$origination_rate = normalize_origination_rate ( $dataVariable ['origination_rates'],$logger );
	if (isset ( $dataVariable ['termination_rates'] ))
		$termination_rate = normalize_rate ( $dataVariable ['termination_rates'] );
	$dataVariable ['origination_call_type'] = 0;
	$logger->log("Origination Rates Array:::");
	$dataVariable ['origination_call_type'] = (isset($origination_rate[$accountid]['CT']) && $origination_rate[$accountid]['CT'] != '')?$origination_rate[$accountid]['CT']:0;
	//$logger->log(print_r($origination_rate,true));

		
	if($actual_duration > 0 || $dataVariable['hangup_cause'] == 'NORMAL_CLEARING' || $dataVariable['hangup_cause'] == 'SUCCESS' || $dataVariable['hangup_cause']  == 'ALLOTTED_TIMEOUT' || $dataVariable['hangup_cause'] == 'USER_BUSY' || $dataVariable['hangup_cause']  == 'INTERWORKING'){
		if($dataVariable['force_trunk_flag'] > 0 ){
			$route_query = "update routes SET call_count = call_count-1 where id =".(int)$dataVariable['force_trunk_flag'];
			$logger->log("GET Update routes count decrase  : " . $route_query);
			$route_user = $db->run($route_query);
		}else{
			if($dataVariable['rate_flag'] == 3){
				$rate_group=$origination_rate[$accountid]['RATEGROUP'];
				$logger->log("*********************** Harsh_rategroup in CDRs: **".$rate_group."***********");
				$query = "update pricelists SET call_count = call_count-1 where id =".(int)$rate_group;
				$logger->log("GET Update pricelist count decrase  : " . $query);
				$rate_user = $db->run($query);
			}
		}
		
		$trunk_id= $termination_rate['TRUNK'];
		$logger->log("*********************** Harsh_trunk in CDRs: **".$rate_group."***********");
		if($dataVariable['force_trunk_flag'] > 0){
			$trunk_query = "update routing SET call_count = call_count-1 where trunk_id=".(int)$trunk_id." and routes_id =".(int)$dataVariable['force_trunk_flag'];
		}else{
			//~ if($dataVariable['rate_flag'] == 3){
				$trunk_query = "update routing SET call_count = call_count-1 where trunk_id=".(int)$trunk_id." and pricelist_id =".(int)$rate_group;
			//~ }else{
				//~ $trunk_query = "update routing SET call_count = call_count-1 where trunk_id=".(int)$trunk_id." and pricelist_id =".(int)$rate_group;
			//~ }
		}
		$logger->log("GET Update routing count decrase  : " . $trunk_query);
		$trunk__user = $db->run($trunk_query);	
	}		
			
		// If receiver account id found then explicitly set call direction and call type
	if (isset ( $dataVariable ['receiver_accid'] )) {
		$dataVariable ['call_direction'] = "outbound";
		//$dataVariable ['calltype'] = "STANDARD";
	}
//check custom function	
//HP: PBX_ADDON
	$addon_prefix = "";
	if(isset($dataVariable ['call_type']) && $dataVariable ['call_type'] != ""){
		$addon_prefix = "_".$dataVariable ['call_type'];
	}
	$call_custom_function= 'custom_process_hook'.$addon_prefix;		
	if(function_exists($call_custom_function)){
		$logger->log(":::HARSH:::::::::::::::".$call_custom_function);
		$return_array = $call_custom_function($actual_calltype,$dataVariable,$accountid,$check_type,$logger,$db);
		if(!empty($return_array)){	
//HP: PBX_ADDON		
			$accountid = isset($return_array['accountid'])?$return_array['accountid']:$accountid;
		}
	}

//check custom end
$logger->log("*********************** Harsh_trunk in package_id: **".$dataVariable ['package_id']."***********");	
	// Check if cusotmer have any package seconds left to use
	if ($actual_duration > 0 && isset($dataVariable ['package_id']) && $dataVariable ['package_id'] > 0 ) {
		$package_array = package_calculation ( $dataVariable ['effective_destination_number'], $dataVariable ['package_id'], $actual_duration, $dataVariable ['call_direction'], $accountid,$dataVariable, $db, $logger );
//		$package_array = package_calculation ( $dataVariable ['effective_destination_number'], $dataVariable ['package_id'], $actual_duration, $actual_call_direction,$dataVariable, $accountid, $db, $logger );
		if (! empty ( $package_array )) {
			$dataVariable ['calltype'] = "FREE";
			$dataVariable ['package_id'] = $package_array ['package_id'];
		}
	}
	
	// Calculate debit of customer call
	$debit = calc_cost ( $dataVariable, $origination_rate [$accountid], $logger, $decimal_points );
	
	// Calculate cost for customer call for provider
	$provider_cost = calc_cost ( $dataVariable, $termination_rate, $logger, $decimal_points );
	
	// Calculate parent cost if customer have any parent
	$parent_cost = ($parentid > 0) ? calc_cost ( $dataVariable, $origination_rate [$parentid], $logger, $decimal_points ) : $provider_cost;
	$logger->log ( "Debit :" . $debit . " Cost : " . $cost . " Provider Cost : " . $parent_cost );
	
	// Initialize final cost variable to use for billing
	$cost = ($parent_cost > 0) ? $parent_cost : $provider_cost;
	
	// Outbound call entry for all type of calls
	$logger->log ( "*********************** OUTBOUND CALL ENTRY START *************" );
	$logger->log ( "*********calltype::::::**************".$dataVariable ['calltype']."*************" );	
	$cdr_string = get_cdr_string ( $dataVariable, $accountid, $account_type, $actual_duration, $termination_rate, $origination_rate, $provider_cost, $parentid, $debit, $cost, $logger, $db );
	
	$query = "INSERT INTO cdrs (uniqueid,accountid,type,callerid,callednum,billseconds,trunk_id,trunkip,callerip,disposition,callstart,debit,cost,provider_id,pricelist_id,package_id,pattern,notes,rate_cost,reseller_id,reseller_code,reseller_code_destination,reseller_cost,provider_code,provider_code_destination,provider_cost,provider_call_cost,call_direction,calltype,call_request,country_id,sip_user,ct,end_stamp)  values ($cdr_string)";
	$logger->log ( $query );
	$db->run ( $query );
	
	// Update customer balance
	if ($debit > 0 && $dataVariable ['calltype'] != "FREE") {
		update_balance ( $accountid, $debit, 0, $logger, $db, $config,$dataVariable );
	}
	
	// Update parent or provider balance
	if ($parent_cost > 0) {
		update_balance ( $termination_rate ['PROVIDER'], ($parent_cost * - 1), 3, $logger, $db, $config,$dataVariable );
	}
	
	// Resellers CDR entry
	$flag_parent = false;
	insert_parent_data ( $dataVariable, $actual_calltype, $parentid, $origination_rate, $actual_duration, $provider_cost, $flag_parent, $logger, $db, $decimal_points, $config,$trunk_id );
	
	$logger->log ( "*********************** OUTBOUND CALL ENTRY END *************" );
	
	// ************ ADDING EXTRA ENTRY For local/DID Inbound call ****************************
	$receiver_parentid = 0;
	if (isset ( $dataVariable ['receiver_accid'] ) && $dataVariable ['receiver_accid'] != "") {
		$logger->log ( "*********************** EXTRA ENTRY SECTION FOR BILLING START *************" );
		
		// Explicitly set call direction and call type
		$dataVariable ['call_direction'] = "inbound";
		if($dataVariable ['calltype'] == 'DID')
			$dataVariable ['sip_user'] = '';
		//$dataVariable ['calltype'] = "DID";
		// For inbound package calculation
		if ($actual_duration > 0 && isset($dataVariable ['package_id']) && $dataVariable ['package_id'] > 0) {
			$package_array = package_calculation ( $dataVariable ['effective_destination_number'], $dataVariable ['package_id'], $actual_duration, $dataVariable ['call_direction'], $accountid,$dataVariable, $db, $logger );
			if (! empty ( $package_array )) {
				$dataVariable ['calltype'] = "FREE";
				$dataVariable ['package_id'] = $package_array ['package_id'];
			}
		}
		// Override variables if call for DID PSTN
		if (isset ( $dataVariable ['caller_did_account_id'] )) {
			$dataVariable ['receiver_accid'] = $dataVariable ['caller_did_account_id'];
			$dataVariable ['call_direction'] = "outbound";
			//$dataVariable ['calltype'] = "STANDARD";
			$dataVariable ['effective_destination_number'] = $dataVariable ['sip_to_user'];
			unset ( $termination_rate );
			unset ( $provider_cost );
		}
		
		// Get call receiver account information
		$receiver_carddata = get_accounts ( $dataVariable ['receiver_accid'], $logger, $db );
		$receiver_parentid = $receiver_carddata ['reseller_id'];
		
		// For additional cdr entry of receiver
		insert_extra_receiver_entry ( $dataVariable, $origination_rate, $termination_rate, $account_type, $actual_duration, $provider_cost, $receiver_parentid, $flag_parent, $dataVariable ['receiver_accid'], $logger, $db, $decimal_points, $config,$trunk_id );
		$flag_parent = true;
		$dataVariable ['uuid'] = $dataVariable ['uuid'] . $dataVariable ['calltype'] . "_" . $receiver_parentid;
		
		// Insert parent reseller cdr
		insert_parent_data ( $dataVariable, $actual_calltype, $receiver_parentid, $origination_rate, $actual_duration, $provider_cost, $flag_parent, $logger, $db, $decimal_points, $config );
		$logger->log ( "*********************** EXTRA ENTRY SECTION FOR BILLING END *************" );
	}
	// *****************************************************************************************
	$logger->log ( "*************************** CDR ends ********************************" );
	return get_defined_vars();
}

// Insert parent resellers cdr
/**
 *
 * @param string $provider_cost        	
 * @param boolean $flag_parent        	
 */
function insert_parent_data($dataVariable, $actual_calltype, $parentid, $origination_rate, $actual_duration, $provider_cost, $flag_parent, $logger, $db, $decimal_points, $config, $trunk_id='') {
	while ( $parentid > 0 ) {
		$logger->log ( "*************** IN PARENT DATA SECTION ********" );
		$dataVariable ['calltype'] = $actual_calltype;
		$carddata = get_accounts ( $parentid, $logger, $db );
		$accountid = $carddata ['id'];
		if($accountid == '' || $accountid == '0'){
			return true;
		}
		$debit = calc_cost ( $dataVariable, $origination_rate [$accountid], $logger, $decimal_points );
		
		// If receiver account id found then explicitly set call direction and call type
		/*
		 * if(isset($dataVariable['receiver_accid']))
		 * {
		 * $dataVariable['call_direction'] = "outbound";
		 * $dataVariable['calltype'] = "STANDARD";
		 * }
		 */
		
		// Check if reseller have any package seconds left to use
		if ($actual_duration > 0 && isset($dataVariable ['package_id']) && $dataVariable ['package_id'] > 0) {
			$package_array = package_calculation ( $dataVariable ['effective_destination_number'],$dataVariable ['package_id'], $actual_duration, $dataVariable ['call_direction'], $accountid,$dataVariable, $db, $logger );
			if (! empty ( $package_array )) {
				$dataVariable ['calltype'] = "FREE";
				$dataVariable ['package_id'] = $package_array ['package_id'];
			}
		}
		
		// Get parent id for cost calculation
		$parentid = $carddata ['reseller_id'];
		$parent_cost = ($parentid > 0) ? calc_cost ( $dataVariable, $origination_rate [$parentid], $logger, $decimal_points ) : $provider_cost;
		$cost = ($parent_cost > 0) ? $parent_cost : $provider_cost;
		
		if (isset ( $dataVariable ['receiver_accid'] ) && $dataVariable ['receiver_accid'] != "" && $flag_parent == true) {
			$logger->log ( "********* IN RESELLER FOR RECEIVER ENTRY START ******" );
			$flag_parent = true;
			insert_extra_receiver_entry ( $dataVariable, $origination_rate, $termination_rate, $account_type, $actual_duration, $provider_cost, $parentid, $flag_parent, $accountid, $logger, $db, $decimal_points, $config ,$trunk_id);
			
			$logger->log ( "********* IN RESELLER FOR RECEIVER ENTRY END ******" );
		} else {
			
			$cdr_string = get_reseller_cdr_string ( $dataVariable, $accountid, $account_type, $actual_duration, $termination_rate, $origination_rate, $provider_cost, $parentid, $debit, $cost,$logger,$db,$trunk_id);
			$query = "INSERT INTO reseller_cdrs (uniqueid,accountid,callerid,callednum,billseconds,disposition,callstart,debit,cost,pricelist_id,package_id,pattern,notes,rate_cost,reseller_id,reseller_code,reseller_code_destination,reseller_cost,call_direction,calltype,call_request,country_id,end_stamp,trunk_id) values ($cdr_string)";
			$logger->log ( $query );
			$db->run ( $query );
			
			// Update reseller balance
			if ($debit > 0 && $dataVariable ['calltype'] != "FREE") {
				update_balance ( $accountid, $debit, 0, $logger, $db, $config,$dataVariable );
			}
		}
	}
}

// Insert callee cdr entry for DID calls
/**
 *
 * @param boolean $flag_parent        	
 */
function insert_extra_receiver_entry($dataVariable, $origination_rate, $termination_rate, $account_type, $actual_duration, $provider_cost, $parentid, $flag_parent, $accountid, $logger, $db, $decimal_points, $config,$trunk_id='') {
	$localVariable = $dataVariable;
	$localVariable ['call_direction'] = "inbound";
	$localVariable ['uuid'] = $localVariable ['uuid'] . $dataVariable ['calltype'] . "_" . $accountid;
	
	if ($dataVariable ['calltype'] == "LOCAL") {
		$origination_rate [$accountid] ['CODE'] = $dataVariable ['effective_destination_number'];
		$origination_rate [$accountid] ['DESTINATION'] = 'Local';//$dataVariable ['calltype'];
		if ($flag_parent == false) {
			$cdr_string = get_cdr_string ( $localVariable, $accountid, $account_type, $actual_duration, $termination_rate, $origination_rate, $provider_cost, $parentid, 0, 0, $logger, $db );
		} else {
			$cdr_string = get_reseller_cdr_string ( $dataVariable, $accountid, $account_type, $actual_duration, $termination_rate, $origination_rate, $provider_cost, $parentid, $debit, $cost,$logger,$db ,$trunk_id);
		}
	} else {
		
		$origination_rate_did = normalize_origination_rate ( $dataVariable ['origination_rates_did'],$logger );
		$debit = calc_cost ( $dataVariable, $origination_rate_did [$accountid], $logger, $decimal_points );

		$dataVariable ['origination_call_type'] = (isset($origination_rate[$accountid]['CT']) && $origination_rate[$accountid]['CT'] != '')?$origination_rate[$accountid]['CT']:0;
		
		if ($flag_parent == false) {
			
			$cdr_string = get_cdr_string ( $localVariable, $accountid, $account_type, $actual_duration, $termination_rate, $origination_rate_did, $provider_cost, $parentid, $debit, 0, $logger, $db );
		} else {
			$cdr_string = get_reseller_cdr_string ( $dataVariable, $accountid, $account_type, $actual_duration, $termination_rate, $origination_rate, $provider_cost, $parentid, $debit, $cost,$logger,$db );
		}
	}
//	$dataVariable ['sip_user'] = '';
	if ($flag_parent == false) {
//HP: Add sip_user in cdrs string for DID
		$query = "INSERT INTO cdrs(uniqueid,accountid,type,callerid,callednum,billseconds,trunk_id,trunkip,callerip,disposition,callstart,debit,cost,provider_id,pricelist_id,package_id,pattern,notes,rate_cost,reseller_id,reseller_code,reseller_code_destination,reseller_cost,provider_code,provider_code_destination,provider_cost,provider_call_cost,call_direction,calltype,call_request,country_id,sip_user,ct,end_stamp) values ($cdr_string)";
	} else {
		$query = "INSERT INTO reseller_cdrs (uniqueid,accountid,callerid,callednum,billseconds,disposition,callstart,debit,cost,pricelist_id,package_id,pattern,notes,rate_cost,reseller_id,reseller_code,reseller_code_destination,reseller_cost,call_direction,calltype,call_request,country_id,end_stamp) values ($cdr_string)";
	}
	
	$logger->log ( $query );
	$db->run ( $query );
	
	//if ($debit > 0 && ($dataVariable ['calltype'] != "FREE" && $dataVariable ['calltype'] != "LOCAL")) {
	if ($debit > 0 && ($dataVariable ['calltype'] != "FREE" && $dataVariable ['calltype'] != "LOCAL")) {
		update_balance ( $accountid, $debit, 0, $logger, $db, $config,$dataVariable );
	}
	return true;
}

// Escape every value that get_cdr_string()/get_reseller_cdr_string() put into quoted SQL
// literals. Caller ID name/number, dialled number, SIP hosts etc. are set by the caller.
// Keys that are urldecode()d later are decoded here first and protected again, so the
// later urldecode() cannot re-introduce quotes.
function astpp_cdr_escape($data, $db) {
	$urldecoded_keys = array (
			'effective_caller_id_name',
			'effective_caller_id_number',
			'original_caller_id_name',
			'original_caller_id_number',
			'callstart',
			'end_stamp'
	);
	if (! is_array ( $data ))
		return $data;
	foreach ( $data as $key => $value ) {
		if (is_array ( $value )) {
			$data [$key] = astpp_cdr_escape ( $value, $db );
		} elseif (is_string ( $value )) {
			if (in_array ( $key, $urldecoded_keys, true )) {
				$data [$key] = str_replace ( array (
						'%',
						'+'
				), array (
						'%25',
						'%2B'
				), $db->esc ( urldecode ( $value ) ) );
			} else {
				$data [$key] = $db->esc ( $value );
			}
		}
	}
	return $data;
}

// Generate CDR string for insert query for customer.
function get_cdr_string($dataVariable, $accountid, $account_type, $actual_duration, $termination_rate, $origination_rate, $provider_cost, $parentid, $debit, $cost, $logger, $db) {
	$dataVariable = astpp_cdr_escape ( $dataVariable, $db );
	$termination_rate = astpp_cdr_escape ( $termination_rate, $db );
	$origination_rate = astpp_cdr_escape ( $origination_rate, $db );

	$get_current_time = gmdate("Y-m-d H:i:s"); // progress_media_stamp
	$get_current_microseconds = round(microtime(true)/1000); // progress_mediamsec
	
	$dataVariable ['calltype'] = ($dataVariable ['calltype'] == 'DID-LOCAL' || $dataVariable ['calltype'] == 'SIP-DID' || $dataVariable ['calltype'] == 'OTHER') ? "DID" : $dataVariable ['calltype'];
	// $callerIdNumber = isset($dataVariable['effective_caller_id_number']) && !empty($dataVariable['effective_caller_id_number'])? $dataVariable['effective_caller_id_number'] :$dataVariable['caller_id'];
	// ASTPPCOM-592 Ashish start
	$callerIdNumber = ($dataVariable ['calltype'] == "DID" || $dataVariable ['calltype'] == "CALLINGCARD") ? $dataVariable ['effective_caller_id_name'] . " <" . $dataVariable ['effective_caller_id_number'] . ">" : $dataVariable ['original_caller_id_name'] . " <" . $dataVariable ['original_caller_id_number'] . ">";
	// ASTPPCOM-592 Ashish end

	$dataVariable ['hangup_cause'] = get_q850code($dataVariable, $db);	
	
	//return $cdr_string = "'" . ($dataVariable ['uuid']) . "','" . $accountid . "','" . $account_type . "','" . (urldecode ( $callerIdNumber )) . "','" . ($dataVariable ['effective_destination_number']) . "','" . $actual_duration . "'," . (($termination_rate ['TRUNK']) ? $termination_rate ['TRUNK'] : '0') . "," . (($dataVariable ['sip_via_host']) ? "'" . $dataVariable ['sip_via_host'] . "'" : '""') . "," . (($dataVariable ['sip_contact_host']) ? "'" . $dataVariable ['sip_contact_host'] . "'" : '""') . ",'" . ($dataVariable ['hangup_cause']) . "','" . urldecode ( $dataVariable ['callstart'] ) . "','" . $debit . "','" . $cost . "'," . (($termination_rate ['PROVIDER']) ? $termination_rate ['PROVIDER'] : '0') . ",'" . $origination_rate [$accountid] ['RATEGROUP'] . "','" . $dataVariable ['package_id'] . "','" . ($origination_rate [$accountid] ['CODE']) . "'," . (($origination_rate [$accountid] ['DESTINATION']) ? "'" . htmlentities ( $origination_rate [$accountid] ['DESTINATION'], ENT_COMPAT, 'UTF-8' ) . "'" : "'" . '' . "'") . "," . (($origination_rate [$accountid] ['COST']) ? "'" . $origination_rate [$accountid] ['COST'] . "'" : "'" . '0' . "'") . ",'" . $parentid . "'," . (($origination_rate [$parentid] ['CODE']) ? "'" . $origination_rate [$parentid] ['CODE'] . "'" : "'" . '0' . "'") . "," . (($origination_rate [$parentid] ['DESTINATION']) ? "'" . $origination_rate [$parentid] ['DESTINATION'] . "'" : "'" . '' . "'") . "," . (($origination_rate [$parentid] ['COST']) ? "'" . $origination_rate [$parentid] ['COST'] . "'" : '0') . "," . (($termination_rate ['CODE']) ? "'" . $termination_rate ['CODE'] . "'" : "'" . '' . "'") . "," . (($termination_rate ['DESTINATION']) ? "'" . $termination_rate ['DESTINATION'] . "'" : "'" . '' . "'") . "," . (($termination_rate ['COST']) ? "'" . $termination_rate ['COST'] . "'" : '0') . ",'" . $provider_cost . "'," . (($dataVariable ['call_direction']) ? "'" . $dataVariable ['call_direction'] . "'" : "'internal'") . ",'" . ($dataVariable ['calltype']) . "','" . $dataVariable ['call_request'] . "','" . $origination_rate [$accountid] ['CI'] . "','".$dataVariable ['sip_user']."','".$dataVariable ['origination_call_type']."','" . urldecode ( $dataVariable ['end_stamp'] ) . "'";
	if($dataVariable ['calltype'] == 'CALLINGCARD'){
		$dataVariable ['uuid']=isset($dataVariable ['originating_leg_uuid']) ? $dataVariable ['originating_leg_uuid'] : $dataVariable ['uuid'];
	}
	return $cdr_string = "'" . ($dataVariable ['uuid']) . "','" . $accountid . "','" . $account_type . "','" . (urldecode ( $callerIdNumber )) . "','" . ($dataVariable ['effective_destination_number']) . "','" . $actual_duration . "'," . (($termination_rate ['TRUNK']) ? $termination_rate ['TRUNK'] : '0') . "," . (($dataVariable ['sip_via_host']) ? "'" . $dataVariable ['sip_via_host'] . "'" : '""') . "," . (($dataVariable ['sip_contact_host']) ? "'" . $dataVariable ['sip_contact_host'] . "'" : '""') . ",'" . ($dataVariable ['hangup_cause']) . "','" . urldecode ( $dataVariable ['callstart'] ) . "','" . $debit . "','" . $cost . "'," . (($termination_rate ['PROVIDER']) ? $termination_rate ['PROVIDER'] : '0') . ",'" . $origination_rate [$accountid] ['RATEGROUP'] . "','" . $dataVariable ['package_id'] . "','" . ($origination_rate [$accountid] ['CODE']) . "'," . (($origination_rate [$accountid] ['DESTINATION']) ? "'" . htmlentities ( $origination_rate [$accountid] ['DESTINATION'], ENT_COMPAT, 'UTF-8' ) . "'" : "'" . '' . "'") . "," . (($origination_rate [$accountid] ['COST']) ? "'" . $origination_rate [$accountid] ['COST'] . "'" : "'" . '0' . "'") . ",'" . $parentid . "'," . (($origination_rate [$parentid] ['CODE']) ? "'" . $origination_rate [$parentid] ['CODE'] . "'" : "'" . '0' . "'") . "," . (($origination_rate [$parentid] ['DESTINATION']) ? "'" . $origination_rate [$parentid] ['DESTINATION'] . "'" : "'" . '' . "'") . "," . (($origination_rate [$parentid] ['COST']) ? "'" . $origination_rate [$parentid] ['COST'] . "'" : '0') . "," . (($termination_rate ['CODE']) ? "'" . $termination_rate ['CODE'] . "'" : "'" . '' . "'") . "," . (($termination_rate ['DESTINATION']) ? "'" . $termination_rate ['DESTINATION'] . "'" : "'" . '' . "'") . "," . (($termination_rate ['COST']) ? "'" . $termination_rate ['COST'] . "'" : '0') . ",'" . $provider_cost . "'," . (($dataVariable ['call_direction']) ? "'" .ucfirst($dataVariable ['call_direction']) . "'" : "'internal'") . ",'" . ($dataVariable ['calltype']) . "','" . $dataVariable ['call_request'] . "','" . $origination_rate [$accountid] ['CI'] . "','".$dataVariable ['sip_user']."','".$dataVariable ['origination_call_type']."','" .date("Y-m-d H:i:s", (strtotime(date(urldecode ( $dataVariable ['callstart'] ))) + $actual_duration)) . "'";
	
}

// Generate CDR string for insert query for reseller
function get_reseller_cdr_string($dataVariable, $accountid, $account_type, $actual_duration, $termination_rate, $origination_rate, $provider_cost, $parentid, $debit, $cost,$logger,$db,$trunk_id='') {
	$dataVariable = astpp_cdr_escape ( $dataVariable, $db );
	$termination_rate = astpp_cdr_escape ( $termination_rate, $db );
	$origination_rate = astpp_cdr_escape ( $origination_rate, $db );
	$trunk_id = ( int ) $trunk_id;
	$dataVariable ['calltype'] = ($dataVariable ['calltype'] == 'DID-LOCAL' || $dataVariable ['calltype'] == 'SIP-DID' || $dataVariable ['calltype'] == 'OTHER') ? "DID" : $dataVariable ['calltype'];
	// $callerIdNumber = isset($dataVariable['effective_caller_id_number']) && !empty($dataVariable['effective_caller_id_number'])? $dataVariable['effective_caller_id_number'] :$dataVariable['caller_id'];
	// ASTPPCOM-592 Ashish start
	$callerIdNumber = ($dataVariable ['calltype'] == "DID" || $dataVariable ['calltype'] == "CALLINGCARD") ? $dataVariable ['effective_caller_id_name'] . " <" . $dataVariable ['effective_caller_id_number'] . ">" : $dataVariable ['original_caller_id_name'] . " <" . $dataVariable ['original_caller_id_number'] . ">";
	// ASTPPCOM-592 Ashish end

	$dataVariable ['hangup_cause'] = get_q850code($dataVariable, $db);
	
	//return $cdr_string = "'" . ($dataVariable ['uuid']) . "','" . $accountid . "','" . (urldecode ( $callerIdNumber )) . "','" . ($dataVariable ['effective_destination_number']) . "','" . $actual_duration . "','" . ($dataVariable ['hangup_cause']) . "','" . urldecode ( $dataVariable ['callstart'] ) . "','" . $debit . "','" . $cost . "','" . $origination_rate [$accountid] ['RATEGROUP'] . "','" . $dataVariable ['package_id'] . "','" . ($origination_rate [$accountid] ['CODE']) . "'," . (($origination_rate [$accountid] ['DESTINATION']) ? "'" . $origination_rate [$accountid] ['DESTINATION'] . "'" : "'" . '' . "'") . "," . (($origination_rate [$accountid] ['COST']) ? "'" . $origination_rate [$accountid] ['COST'] . "'" : "'" . '0' . "'") . ",'" . $parentid . "'," . (($origination_rate [$parentid] ['CODE']) ? "'" . $origination_rate [$parentid] ['CODE'] . "'" : "'" . '0' . "'") . "," . (($origination_rate [$parentid] ['DESTINATION']) ? "'" . $origination_rate [$parentid] ['DESTINATION'] . "'" : "'" . '' . "'") . "," . (($origination_rate [$parentid] ['COST']) ? "'" . $origination_rate [$parentid] ['COST'] . "'" : '0') . "," . (($dataVariable ['call_direction']) ? "'" . $dataVariable ['call_direction'] . "'" : "'internal'") . ",'" . ($dataVariable ['calltype']) . "','" . ($dataVariable ['call_request']) . "','" . $origination_rate [$accountid]['CI'] . "','" . urldecode ( $dataVariable ['end_stamp'] ) . "'";
	return $cdr_string = "'" . ($dataVariable ['uuid']) . "','" . $accountid . "','" . (urldecode ( $callerIdNumber )) . "','" . ($dataVariable ['effective_destination_number']) . "','" . $actual_duration . "','" . ($dataVariable ['hangup_cause']) . "','" . urldecode ( $dataVariable ['callstart'] ) . "','" . $debit . "','" . $cost . "','" . $origination_rate [$accountid] ['RATEGROUP'] . "','" . $dataVariable ['package_id'] . "','" . ($origination_rate [$accountid] ['CODE']) . "'," . (($origination_rate [$accountid] ['DESTINATION']) ? "'" . $origination_rate [$accountid] ['DESTINATION'] . "'" : "'" . '' . "'") . "," . (($origination_rate [$accountid] ['COST']) ? "'" . $origination_rate [$accountid] ['COST'] . "'" : "'" . '0' . "'") . ",'" . $parentid . "'," . (($origination_rate [$parentid] ['CODE']) ? "'" . $origination_rate [$parentid] ['CODE'] . "'" : "'" . '0' . "'") . "," . (($origination_rate [$parentid] ['DESTINATION']) ? "'" . $origination_rate [$parentid] ['DESTINATION'] . "'" : "'" . '' . "'") . "," . (($origination_rate [$parentid] ['COST']) ? "'" . $origination_rate [$parentid] ['COST'] . "'" : '0') . "," . (($dataVariable ['call_direction']) ? "'" . ucfirst($dataVariable ['call_direction']) . "'" : "'internal'") . ",'" . ($dataVariable ['calltype']) . "','" . ($dataVariable ['call_request']) . "','" . $origination_rate [$accountid]['CI'] . "','" . date("Y-m-d H:i:s", (strtotime(date(urldecode ( $dataVariable ['callstart'] ))) + $actual_duration)) . "',". (($trunk_id) ? $trunk_id : '0');
}

// Update user balance
/**
 *
 * @param integer $entity_id        	
 */
function update_balance($user_id, $amount, $entity_id, $logger, $db, $config, $dataVariable) {
		$math_sign = ($entity_id == 0 || $entity_id == 1) ? '-' : '+';
		$tmp_prefix=($dataVariable['intcall']==1)?'int_':'';
		$query = "UPDATE accounts SET ".$tmp_prefix."balance=IF(posttoexternal=1,".$tmp_prefix."balance+" . $amount . ",".$tmp_prefix."balance-" . $amount . ") WHERE id=" . $user_id;
		$logger->log ( "Balance update : " . $query );
		$db->run ( $query );
}

// Normalize rate string which we are getting from dialplan
function normalize_rate($dataVariable) {
	$rates = urldecode ( $dataVariable );
	$data = explode ( "|", $rates );
	
	$newarray = array ();
	foreach ( $data as $key => $value ) {
		$data1 = explode ( ":", $value );
		foreach ( $data1 as $newkey => $newvalue ) {
			$newarray [$data1 [0]] = $data1 [$newkey];
		}
	}	
	return $newarray;
}

// Normalize originaion rate string which we are getting from dialplan
function normalize_origination_rate($dataVariable,$logger='') {
	$rates = urldecode ( $dataVariable );
	$data = explode ( "|", $rates );
	$newarray = $clnewarray = array ();
	$newarray1 = array ();
	foreach ( $data as $key => $value ) {
		$data1 = explode ( ":", $value );
//	$logger->log("Origination Rates Array data1:::");
//	$logger->log(print_r($data1,true));

		foreach ( $data1 as $newkey => $newvalue ) {
			$newarray [$data1 [0]] = $data1 [$newkey];
			if ($newvalue == "ACCID") {
				$newarray1 [$data1 [1]] = $newarray;
			}
			/*$clnewarray [$data1 [0]] = $data1 [1];
			if ($newvalue == "CL") {
				$newarray1 [$data1 [1]] = $clnewarray;
			}*/
		}
	}
	return $newarray1;
}

//Checking table existance
function get_q850code($dataVariable, $db) {
        
        $query = "SELECT code FROM q850code WHERE cause='" . $db->esc ( $dataVariable ['hangup_cause'] ) . "'";    	
        $q850code = $db->run ( $query );    	
    	$q850code [0]['code'] = ($q850code [0]['code']=='')?'31':$q850code [0]['code'];
        return $dataVariable ['hangup_cause'] .' ['. $q850code [0]['code'].']';
}


// Calculate cost for billing
function calc_cost($dataVariable, $rates, $logger, $decimal_points) {
	// $logger->log(print_r($rates,true));
	$duration = (int) isset($dataVariable['billsec'])?$dataVariable ['billsec']:0;

	// If the call was less than 1 second), or we don't have any rates, it didn't cost anything
	if ($duration < 1 || empty($rates)) {
		return 0;
	}

	$call_cost = $rates['CONNECTIONCOST'];
	$rates['COST'] = (int) empty($rates['COST'])?"0":$rates['COST'];

	if (!empty($rates['INCLUDEDSECONDS'])) {
		$duration -= $rates ['INCLUDEDSECONDS'];
	}

	// If there is any duration left, we need to bill for that.
	if ($duration > 0) {
		
		// Take off the 'Initial Increment', and bill for that.
		$rates ['INITIALBLOCK'] = (empty($rates['INITIALBLOCK']) || $rates['INITIALBLOCK'] < 1) ? 0 : $rates ['INITIALBLOCK']; //HP: change default value 1 to 0 when 'INITIALBLOCK' emotry or lessthen 1.
		$call_cost += ($rates['COST'] / 60) * $rates ['INITIALBLOCK'];
		$duration -= $rates ['INITIALBLOCK'];
		
		if ($duration > 0) {

			$rates ['INC'] = (empty($rates['INC']) || $rates['INC'] < 1) ? 1 : $rates ['INC'];
			$call_cost += (ceil ( $duration / $rates ['INC'] ) * $rates ['INC']) * ($rates ['COST'] / 60);
		}
	}
	$call_cost = number_format ( $call_cost, $decimal_points );
	$logger->log ( "Return cost " . $call_cost );
	return $call_cost;
}
//HP: Changes code for check multiple package.
// get intial package information
function package_calculation($destination_number, $package_id, $duration, $call_direction, $accountid,$dataVariable, $db, $logger) {
	$package_array = array ();
	$logger->log ( "Package call_direction is  : " . $call_direction." AND receiver_accid is :::".$dataVariable ['receiver_accid']);
	if($call_direction == 'inbound' && isset($dataVariable ['receiver_accid']) && $dataVariable ['receiver_accid'] > 0){
		$accountid = ( int ) $dataVariable ['receiver_accid'];
		$logger->log ( "Package call_direction is  : " . $call_direction." AND Account ID is :::".$accountid );
	}
	$custom_destination = number_loop ( $destination_number, "patterns", $db );
	
	// equal pattern lengths in order of the package id, as in the Lua dialplan (MariaDB returned
	// equal packages in varying order, so the counted package changed from call to call)
	$query = "SELECT *,P.id as package_id,P.product_id as product_id FROM packages_view  as P inner join package_patterns as PKGPTR on P.product_id = PKGPTR.product_id WHERE " . $custom_destination . " AND accountid = " . $accountid . " ORDER BY LENGTH(PKGPTR.patterns) DESC, P.id, PKGPTR.id";
	$logger->log ( "Package query  : " . $query );
	$package_info_arr = $db->run ( $query );
	// The packages covering the destination form one pool. Seconds a package cannot take any more
	// are counted on the next one (they were dropped, while the dialplan allows a call as long as
	// the minutes left in all packages). package_id = first package used.
	$seconds_left = $duration;
	$seen_packages = array ();
	if (!empty($package_info_arr)) {
		foreach($package_info_arr as $package_info){
			if ($seconds_left <= 0 || isset ( $seen_packages [$package_info ['package_id']] )) {
				continue;
			}
			$seen_packages [$package_info ['package_id']] = true;
			// Ashish ASTPPCOM-823
			$account_data = get_accounts ( $package_info["accountid"], $logger, $db );
			// Ashish 823 End
	$logger->log ( "applicable_for  : " . $package_info ['applicable_for']."=====call_direction:".$call_direction."===0:Inbound,1:Outbound,2:Both" );
	//HP: change type according to GUI changes.
//			$package_info = $package_info [0];
			if (($package_info ['applicable_for'] == "0" && $call_direction == "inbound") || ($package_info ['applicable_for'] == "1" && $call_direction == "outbound") || ($package_info ['applicable_for'] == "2")) {					

				$counter_info = get_counters ( $accountid, $package_info ['package_id'], $db, $logger );
			
				if (! $counter_info) {
					// Ashish ASTPPCOM-823
					$Insert_Query = "INSERT INTO counters (product_id,package_id,accountid,type) VALUES (" . $package_info ['product_id'] . "," . $package_info ['package_id'] . "," . $accountid . "," . $account_data['type'] . ")";
					// Ashish 823 End
					$logger->log ( "Insert Counters  : " . $Insert_Query );
					$db->run ( $Insert_Query );
					$counter_info = get_counters ( $accountid, $package_info ['package_id'], $db, $logger );
				}
				$package_info ['free_seconds'] = $package_info ['free_minutes']*60;
				if ($package_info ['free_seconds'] > ($counter_info ['used_seconds'])) {
					$available_seconds = $package_info ['free_seconds'] - $counter_info ['used_seconds'];
					$logger->log ( "available_seconds  : " . $available_seconds."\n" );
					$logger->log ( "free_minutes  : " . $package_info ['free_minutes']."\n" );
					$logger->log ( "used_seconds  : " . $counter_info ['used_seconds']."\n" );
					$logger->log ( "duration  : " . $duration." (not yet counted: " . $seconds_left . ")\n" );
					$free_seconds = ($available_seconds >= $seconds_left) ? $seconds_left : $available_seconds;
					$seconds_left = $seconds_left - $free_seconds;
	//				$duration = ($available_seconds >= $duration) ? $duration : $available_seconds;
					$final_min = $counter_info ['used_seconds'] + $free_seconds;
					$final_min =  ceil($final_min/60)*60;
					//$freeminutes ['free_minutes'] = ceil($freeminutes ['free_minutes']/60)*60;
					$update_query = "UPDATE counters SET used_seconds = " . ($final_min) . " WHERE id = " . $counter_info ['id'];
					$logger->log ( "Update Counters  : " . $update_query );
					$db->run ( $update_query );
					if (empty ( $package_array )) {
						$package_array ['package_id'] = $package_info ['package_id'];
						$package_array ['calltype'] = "FREE";
					}
				}
			}
		}
	}
	return $package_array;
}

// Getting used package minutes in counter table
function get_counters($accountid, $package_id, $db, $logger) {
	$query_counter = "SELECT id,used_seconds FROM counters  WHERE  accountid = " . $accountid . " AND package_id = " . $package_id . " AND status=1 LIMIT 1";
	$counter = $db->run ( $query_counter );
	$logger->log ( "GET Counters  : " . $query_counter );
	if ($counter)
		return $counter [0];
	else
		return "";
}

// Get user info
function get_accounts($parent_id, $logger, $db) {
	$query = "SELECT * FROM accounts WHERE id=" . $parent_id;
	$logger->log ( "GET configuration  : " . $query );
	$res_user = $db->run ( $query );
	return $res_user [0];
}

// Get configuration
function load_configuration($logger) {
	$query = "SELECT name,value FROM system WHERE name IN ('decimal_points','realtime_billing') and group_title = 'global'";
	$config = $db->run ( $query );
	$logger->log ( "GET configuration  : " . $query );
	return $config [0];
}
 /*
 * @param string $field        	
 */
function number_loop($destination, $field, $db) {
	$max_len_prefix = strlen ( $destination );
	$number_prefix = '(';
	while ( $max_len_prefix > 0 ) {
		$number_prefix .= "$field='^" . $db->esc ( substr ( $destination, 0, $max_len_prefix ) ) . ".*' OR ";
		$max_len_prefix --;
	}
	$number_prefix .= "$field='^defaultprefix.*')"; // echo $number_prefix;exit;
	return $number_prefix;
}

// Convert current time to GMT
/**
 *
 * @param string $date        	
 */
function convert_to_gmt($date) {
	return gmdate ( 'Y-m-d H:i:s', strtotime ( $date ) );
}
?>
