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
class Currencyupdate extends CI_Controller {
	function __construct() {
		parent::__construct ();
		// Only internal (cron) calls or a logged-in administrator
		if (! astpp_internal_request ()) {
			$this->load->library ( 'session' );
			if ($this->session->userdata ( 'user_login' ) != TRUE || ! in_array ( ( string ) $this->session->userdata ( 'logintype' ), array ( '2', '4' ), true )) {
				show_404 ();
			}
		}
		$this->load->model ( "db_model" );
		$this->load->library ( "astpp/common" );
	}
	function update_currency() {
		$url = "http://data.fixer.io/api/latest?access_key=".Common_model::$global_config ['system_config'] ['currency_conv_api_key']."&base=".Common_model::$global_config ['system_config'] ['base_currency'];
		$currencyData = $this->curl_response ( $url );
		$currencyData = json_decode($currencyData);
		if($currencyData->success != 1){
			$this->session->set_flashdata ( "astpp_notification","Currency exchange rates not updated successfully for this reason ".$currencyData->error->type." please check from fixer side." );
			redirect ( base_url () . "systems/currency_list/" );
		}
		$base_currency = $currencyData->base;
		$last_updated = date("Y-m-d H:i:s",$currencyData->timestamp);
		$currency_rates = (array)$currencyData->rates;	
		foreach ($currency_rates as $key => $value) {
			$value = number_format((float)$value, 3, '.', '');
			$sql = "UPDATE currency SET currencyrate = '".$value."',last_updated = '".$last_updated."' WHERE currency = '" . $key . "'\n";
			$this->db->query ( $sql );
		}
		$updatebasecurrency = "UPDATE currency SET currencyrate = '1.000',last_updated = '" .$last_updated."' WHERE currency = '" . Common_model::$global_config ['system_config'] ['base_currency'] . "'";
		$this->db->query ( $updatebasecurrency );
		$this->session->set_flashdata ( "astpp_errormsg", "Currency exchange rates successfully updated." );
		redirect ( base_url () . "systems/currency_list/" );
	}
	function curl_response($url) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_ENCODING, "" );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)" );
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		return $data;
	}
}
?>
