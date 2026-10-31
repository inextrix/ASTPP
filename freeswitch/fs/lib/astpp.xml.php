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

// If module/app not found
function xml_not_found() {
	header ( 'Content-Type: text/xml' );
	
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n";
	$xml .= "<document type=\"freeswitch/xml\">\n";
	$xml .= "  <section name=\"result\">\n";
	$xml .= "    <result status=\"not found\"/>\n";
	$xml .= "  </section>\n";
	$xml .= "</document>\n";
	echo $xml;
	exit ();
}

// Build acl xml
function load_acl($logger, $db, $config) {
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n";
	$xml .= "<document type=\"freeswitch/xml\">\n";
	$xml .= "   <section name=\"Configuration\" description=\"Configuration\">\n";
	$xml .= "       <configuration name=\"acl.conf\" description=\"Network List\">\n";
	$xml .= "           <network-lists>\n";
	$xml .= "       <list name=\"default\" default=\"deny\">\n";
	
	// For customer and provider ips
	$query = "SELECT ip FROM ip_map,accounts WHERE ip_map.accountid=accounts.id AND ip_map.status=0 AND accounts.status=0 AND accounts.deleted=0";
	$logger->log ( "ACL Query : " . $query );
	$res_acl = $db->run ( $query );
	$logger->log ( $res_acl );
	
	foreach ( $res_acl as $res_acl_key => $res_acl_value ) {
		if(preg_match("/[a-zA-Z\-]/i", $res_acl_value ['ip'])){
                        $ips = gethostbynamel($res_acl_value ['ip']);
                        foreach ($ips as $ip => $value){
                                $res_acl_value ['ip'] = $value . "/32";
                        }
                }
		$xml .= "       <node type=\"allow\" cidr=\"" . astpp_xml_attr ( $res_acl_value ['ip'] ) . "\"/>\n";
	}
	
	// For gateways
/*	$query = "SELECT * FROM gateways WHERE status=0";
	$logger->log ( "Sofia Gateway Query : " . $query );
	$sp_gw = $db->run ( $query );
	$logger->log ( $sp_gw );
	
	foreach ( $sp_gw as $sp_gw_key => $sp_gw_value ) {
		
		$sp_gw_settings = json_decode ( $sp_gw_value ['gateway_data'], true );
		foreach ( $sp_gw_settings as $sp_gw_settings_key => $sp_gw_settings_value ) {
			if ($sp_gw_settings_value != "" && $sp_gw_settings_key == "proxy") {
				$tmp_ip_arr = explode ( ":", $sp_gw_settings_value );
				if (filter_var($tmp_ip_arr [0], FILTER_VALIDATE_IP) || preg_match("/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/", $tmp_ip_arr [0])) {
                                        if(preg_match("/[a-zA-Z\-]/i", $tmp_ip_arr [0])){
                                                $ips = gethostbynamel($tmp_ip_arr [0]);
                                                foreach ($ips as $ip => $value){
                                                        $tmp_ip_arr [0] = $value;
                                                }
                                        }
					$xml .= "   <node type=\"allow\" cidr=\"" . $tmp_ip_arr [0] . "/32\"/>\n";
				}
			}
		}
	}*/

	// For opensips
	if ($config ['opensips'] == '0') {
		if(preg_match("/[a-zA-Z\-]/i", $config ['opensips_domain'])){
                        $ips = gethostbynamel($config ['opensips_domain']);
                        foreach ($ips as $ip => $value){ 
                                $config ['opensips_domain'] = $value;
                        }
                }
                $xml .= "<node type=\"allow\" cidr=\"" . $config ['opensips_domain'] . "/32\"/>\n";
                $xml .= "       </list>\n";
		// For loopback
                $xml .= "<list name=\"loopback.auto\" default=\"allow\">\n";
                $xml .= "<node type=\"allow\" cidr=\"" . $config ['opensips_domain'] . "/32\"/>\n";
                $xml .= "</list>\n";
		// For event handing
                $xml .= "<list name=\"event\" default=\"deny\">\n";
                $xml .= "<node type=\"allow\" cidr=\"" . $config ['opensips_domain'] . "/32\"/>\n";
	}
	else{
                $xml .= "       </list>\n";
		// For event handing
                $xml .= "<list name=\"event\" default=\"deny\">\n";
        }
	
	$xml .= "<node type=\"allow\" cidr=\"127.0.0.0/8\"/>\n";
	$xml .= "</list>\n";
	$xml .= "           </network-lists>\n";
	$xml .= "       </configuration>\n";
	$xml .= "   </section>\n";
	$xml .= "</document>\n";
	$logger->log ( $xml );
	return $xml;
}

// Build sofia xml
function load_sofia($logger, $db, $config) {
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n";
	$xml .= "<document type=\"freeswitch/xml\">\n";
	$xml .= "   <section name=\"Configuration\" description=\"Configuration\">\n";
	$xml .= "   <configuration name=\"sofia.conf\" description=\"SIP Profile\">\n";
	
	//Added homer integration code
	if ($config['homer_capture_server'] != "") {
		$xml .= " <global_settings>\n";
		$xml .= " <param name=\"sip-capture\" value=\"yes\"/>\n";
		$xml .= " <param name=\"capture-server\" value=\"" . astpp_xml_attr ( $config['homer_capture_server'] ) . "\"/>\n";
		$xml .= " </global_settings>\n";
	}

	$xml .= "   <profiles>\n";
	
	$query = "SELECT * FROM sip_profiles WHERE status=0";
	$logger->log ( "Sofia Query : " . $query );
	$res_sp = $db->run ( $query );
	// $logger->log($res_sp);
	
	foreach ( $res_sp as $sp_key => $sp_value ) {
		
		$settings = json_decode ( $sp_value ['profile_data'], true );
		// $logger->log(print_r($settings,true));
		// Profile and gateway values are escaped - a gateway password containing
		// & < or " used to break the whole sofia.conf, and passwords are masked in the debug log
		$xml .= "   <profile name=\"" . astpp_xml_attr ( $sp_value ['name'] ) . "\">\n";
		
		$xml .= "   <domains>\n";
		$xml .= "       <domain name=\"" . astpp_xml_attr ( $sp_value ['sip_ip'] ) . "\" alias=\"true\" parse=\"true\"/>\n";
		$xml .= "   </domains>\n";
		/*
		 * $xml .= " <aliases>\n";
		 * $xml .= " <alias name=\"" . $sp_value['sip_ip'] . "\"/>\n";
		 * $xml .= " </aliases>\n";
		 */
		$xml .= "   <settings>\n";
		$xml .= "       <param name=\"sip-ip\" value=\"" . astpp_xml_attr ( $sp_value ['sip_ip'] ) . "\"/>\n";
		$xml .= "       <param name=\"sip-port\" value=\"" . astpp_xml_attr ( $sp_value ['sip_port'] ) . "\"/>\n";
		foreach ( ( array ) $settings as $set_key => $set_val ) {
			$xml .= "       <param name=\"" . astpp_xml_attr ( $set_key ) . "\" value=\"" . astpp_xml_attr ( $set_val ) . "\"/>\n";
		}
		$xml .= "       <param name=\"user-agent-string\" value=\"ASTPP\"/>\n";

		$xml .= "   </settings>\n";
		
		// Gateway block start
		$xml .= "   <gateways>\n";
		$query = "SELECT * FROM gateways WHERE sip_profile_id=" . $sp_value ['id'] . " AND status=0";
		$logger->log ( "Sofia Gateway Query : " . $query );
		$sp_gw = $db->run ( $query );
		$logger->log ( $sp_gw );
		foreach ( $sp_gw as $sp_gw_key => $sp_gw_value ) {
			$xml .= "       <gateway name=\"" . astpp_xml_attr ( $sp_gw_value ['name'] ) . "\">\n";
			
			$sp_gw_settings = json_decode ( $sp_gw_value ['gateway_data'], true );
			foreach ( ( array ) $sp_gw_settings as $sp_gw_settings_key => $sp_gw_settings_value ) {
				if ($sp_gw_settings_value != "")
					$xml .= "           <param name=\"" . astpp_xml_attr ( $sp_gw_settings_key ) . "\" value=\"" . astpp_xml_attr ( $sp_gw_settings_value ) . "\"/>\n";
			}
			$xml .= "       </gateway>\n";
		}
		$xml .= "   </gateways>\n";
		// Gateway block end
		
		$xml .= "   </profile>\n";
	}
	// echo $xml;
	$xml .= "   </profiles>\n";
	$xml .= "   </configuration>\n";
	$xml .= "   </section>\n";
	$xml .= "</document>\n";
	$logger->log ( preg_replace ( '/(<param name="[^"]*password[^"]*" value=")[^"]*"/i', '$1***"', $xml ) );
	return $xml;
}
function update_vm_data($logger, $db, $password, $user) {
	// The SIP user name comes from the request: always bind it as a parameter.
	$query = "SELECT * FROM sip_devices where username=? limit 1";
	$logger->log ( "Directory Query : " . $query );
	$res_dir = $db->run ( $query, array ( ( string ) $_REQUEST ['user'] ) );
	if (! is_array ( $res_dir ) || empty ( $res_dir ))
		return;
	$params = json_decode ( $res_dir [0] ['dir_params'], true );
	$params ['vm-password'] = $password;

	$query = "update sip_devices set dir_params = ? where username=? limit 1";
	$res_dir = $db->run ( $query, array ( json_encode ( $params, true ), ( string ) $_REQUEST ['user'] ) );
}

// Escape a value for an XML attribute of the documents returned to FreeSWITCH
function astpp_xml_attr($value) {
	return htmlspecialchars ( ( string ) $value, ENT_QUOTES | ENT_XML1, 'UTF-8' );
}

// Build directory xml
function load_directory($logger, $db) {
	$xml = "";

	// The SIP user name is sent by the client before any authentication: bind it as a parameter.
	$query = "SELECT username,dir_params,dir_vars,number as accountcode,accountid FROM sip_devices,accounts WHERE sip_devices.status=0 AND accounts.status=0 AND accounts.deleted=0 AND accounts.id=sip_devices.accountid AND username=? limit 1";
	$logger->log ( "Directory Query : " . $query );
	$res_dir = $db->run ( $query, array ( ( string ) $_REQUEST ['user'] ) );
	$logger->log ( $res_dir );
	if (! is_array ( $res_dir ))
		$res_dir = array ();

	foreach ( $res_dir as $res_dir_key => $res_dir_value ) {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n";
		$xml .= "<document type=\"freeswitch/xml\">\n";
		$xml .= "   <section name=\"Directory\" description=\"Directory\">\n";
		$xml .= "       <domain name=\"" . astpp_xml_attr ( $_REQUEST ['domain'] ) . "\" alias=\"true\">\n";
		$xml .= "           <user id=\"" . astpp_xml_attr ( $_REQUEST ['user'] ) . "\">\n";

		$params = json_decode ( $res_dir_value ['dir_params'], true );

		$vars = json_decode ( $res_dir_value ['dir_vars'], true );
		$param_xml = $var_xml = "";
		foreach ( ( array ) $params as $parms_key => $res_dir_params ) {
			$param_xml .= "<param name=\"" . astpp_xml_attr ( $parms_key ) . "\" value=\"" . astpp_xml_attr ( $res_dir_params ) . "\"/>\n";
		}

		foreach ( ( array ) $vars as $var_key => $res_dir_vars ) {
			$var_xml .= "<variable name=\"" . astpp_xml_attr ( $var_key ) . "\" value=\"" . astpp_xml_attr ( $res_dir_vars ) . "\"/>\n";
		}
		
		$xml .= "               <params>\n";
		$xml .= $param_xml;
		$xml .= "<param name=\"allow-empty-password\" value=\"false\"/>\n";
		$xml .= "<param name=\"dial-string\" value=\"{sip_invite_domain=\${domain_name},presence_id=\${dialed_user}@\${domain_name}}\${sofia_contact(*/\${dialed_user}@\${domain_name})}\"/>\n";
		$xml .= "               </params>\n";
		$xml .= "               <variables>\n";
		$xml .= $var_xml;
		$xml .= "<variable name=\"sipcall\" value=\"true\"/>\n";
		$xml .= "<variable name=\"sip_user\" value=\"" . astpp_xml_attr ( $_REQUEST ['user'] ) . "\"/>\n";
		$xml .= "<variable name=\"accountcode\" value=\"" . astpp_xml_attr ( $res_dir_value ['accountcode'] ) . "\"/>\n";
		$xml .= "<variable name=\"domain_name\" value=\"" . astpp_xml_attr ( $_REQUEST ['domain'] ) . "\"/>\n";
		$xml .= "               </variables>\n";
		
		$xml .= "           </user>\n";
		$xml .= "       </domain>\n";
		$xml .= "   </section>\n";
		$xml .= "</document>\n";
	}
	
	$logger->log ( $xml );
	return $xml;
}

?>
