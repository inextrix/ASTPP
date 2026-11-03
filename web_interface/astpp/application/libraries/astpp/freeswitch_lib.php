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
#[\AllowDynamicProperties]
class freeswitch_lib {
	function event_socket_create($host = '127.0.0.1', $port = '8021', $password = 'ClueCon') {
		$fp = @fsockopen ( $host, $port, $errno, $errdesc );		
		if ($fp) {
			socket_set_blocking ( $fp, false );
			while ( ! feof ( $fp ) ) {
				$buffer = fgets ( $fp, 1024 );
				usleep ( 100 ); 
				if (trim ( $buffer ) == "Content-Type: auth/request") {
					fputs ( $fp, "auth $password\n\n" );
					break;
				}
			}
			return $fp;
		} else {
			return false;
		}
	}
	function event_socket_request($fp, $cmd) {
		if ($fp) {
			fputs ( $fp, $cmd . "\n\n" );
			usleep ( 100 ); 
			
			$response = "";
			$i = 0;
			$contentlength = 0;
			while ( ! feof ( $fp ) ) {
				$buffer = fgets ( $fp, 4096 );
				if (astpp_gt($contentlength, 0)) {
					$response .= $buffer;
				}
				
				if (astpp_eq0($contentlength)) { 
					if (strlen ( trim ( $buffer ) ) > 0) { 
						$temparray = explode ( ":", trim ( $buffer ) );
						if ($temparray [0] == "Content-Length") {
							$contentlength = trim ( $temparray [1] );
						}
					}
				}
				
				usleep ( 100 ); 
				             
				if ($i > 10000) {
					break;
				}
				
				if (astpp_gt($contentlength, 0)) { 
				                          
					if (strlen ( $response ) >= $contentlength) {
						break;
					}
				}
				$i ++;
			}
			
			return $response;
		} else {
			
		}
	}
}
?>
