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
class Common_model extends CI_Model {
	var $host;
	var $user;
	var $pass;
	var $db2;
	var $db_link;
	var $conn = false;
	public static $global_config;
	public function __construct() {
		parent::__construct ();
		$this->load->library ( 'astpp/common' );
		$this->get_system_config ();
		$this->get_currencylist ();
		$this->get_country_list ();
		$this->get_admin_info ();
		$this->db->query ( 'SET time_zone = "+00:00"' );
	}
	function get_language_list() {
		$query = $this->db->get ( "languages" );
		$result = $query->result_array ();
		$language_list = array ();
		foreach ( $result as $row ) {
			$language_list [$row ['id']] = $row;
		}
		
		self::$global_config ['language_list'] = $language_list;
		return $language_list;
	}
	function get_system_config() {
		$query = $this->db->get ( "system" );
		$config = array ();
		$result = $query->result_array ();
		foreach ( $result as $row ) {
			if ($row ['name'] == 'decimal_points' || $row ['name'] == 'starting_digit' || $row ['name'] == 'card_length' || $row ['name'] == 'pin_length') {
				$row ['name'] = str_replace ( "_", "", $row ['name'] );
			}
			$config [$row ['name']] = $row ['value'];
		}
		
		self::$global_config ['system_config'] = $config;
		return $config;
	}
	function get_country_list() {
		$query = $this->db->get ( "countrycode" );
		$result = $query->result_array ();
		$country_list = array ();
		foreach ( $result as $row ) {
			$country_list [$row ['id']] = gettext($row ['country']);
		}
		self::$global_config ['country_list'] = $country_list;
		return $country_list;
	}
	function get_currencylist() {
		$query = $this->db->get ( "currency" );
		$currencylist = array ();
		$result = $query->result_array ();
		foreach ( $result as $row ) {
			$currencylist [$row ['currency']] = $row ['currencyrate'];
		}
		self::$global_config ['currency_list'] = $currencylist;
		return $currencylist;
	}
	function get_admin_info() {
		$result = $this->db->get_where ( 'accounts', array (
				'type' => '-1' 
		) );
		$result = $result->result_array ();
		self::$global_config ['admin_info'] = $result [0];
		return $result [0];
	}
	function generate_receipt($accountid, $amount, $accountinfo, $last_invoice_ID, $invoice_prefix, $due_date) {
		// ASTPPCOM-857 Ashish start
		$current_language=$this->session->userdata ( 'user_language' );
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$amount = str_replace ( '.', ',', $amount );
		}else{
			$amount = str_replace ( ',', '', $amount );
		}
		// ASTPPCOM-857 end
		$invoice_data = array (
				"accountid" => $accountid,
				"invoice_prefix" => $invoice_prefix,
				"invoiceid" => $last_invoice_ID,
				"reseller_id" => $accountinfo ['reseller_id'],
				"invoice_date" => gmdate ( "Y-m-d H:i:s" ),
				"from_date" => gmdate ( "Y-m-d H:i:s" ),
				"to_date" => gmdate ( "Y-m-d H:i:s" ),
				"due_date" => $due_date,
				"status" => 1,
				"balance" => $accountinfo ['balance'],
				"amount" => $amount,
				"type" => 'R',
				"confirm" => '1' 
		);
		$this->db->insert ( "invoices", $invoice_data );
		return $this->db->insert_id ();
	}
	function calculate_currency($amount = 0, $from_currency = '', $to_currency = '', $format_currency = true, $append_currency = true) {
		$from_currency = ($from_currency == '') ? self::$global_config ['system_config'] ['base_currency'] : $from_currency;
		
		if ($to_currency == '') {
			$to_currency1 = $this->session->userdata ['accountinfo'] ['currency_id'];
			$to_currency = $this->common->get_field_name ( 'currency', 'currency', $to_currency1 );
		}
		
		$from_cur_rate = (self::$global_config ['currency_list'] [$from_currency] > 0) ? self::$global_config ['currency_list'] [$from_currency] : 1;
		  
		 $to_cur_rate = (self::$global_config ['currency_list'] [$to_currency]) ? self::$global_config ['currency_list'] [$to_currency] : 1;

		// ASTPPCOM-857 Ashish start
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$amount = str_replace ( '.', ',', $amount );
		}else{
			$amount = str_replace ( ',', '', $amount );
		}
		// ASTPPCOM-857 end
		$cal_amount = ($amount * $to_cur_rate) / $from_cur_rate;
		if ($format_currency)
			$cal_amount = $this->format_currency ( $cal_amount );
		if ($append_currency) {
			$cal_amount = $cal_amount . " " . $to_currency;
		}
		//ASTPPCOM-857 Ashish start
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$cal_amount = str_replace ( '.', ',', $cal_amount );
		}else{
			$cal_amount = str_replace ( ',', '', $cal_amount );
		}
		// ASTPPCOM-857 end
		return $cal_amount;
	}
	function calculate_currency_customer($amount = 0, $from_currency = '', $to_currency = '', $format_currency = true, $append_currency = true) {
		$from_currency = ($from_currency == '') ? self::$global_config ['system_config'] ['base_currency'] : $from_currency;
		if ($to_currency == '') {
			$to_currency1 = $this->session->userdata ['accountinfo'] ['currency_id'];
			$to_currency = $this->common->get_field_name ( 'currency', 'currency', $to_currency1 );
		}

		$from_cur_rate = (self::$global_config ['currency_list'] [$from_currency] > 0) ? self::$global_config ['currency_list'] [$from_currency] : 1;

		$to_cur_rate = (self::$global_config ['currency_list'] [$to_currency]) ? self::$global_config ['currency_list'] [$to_currency] : 1;
		// ASTPPCOM-857 Ashish start
		$current_language=$this->session->userdata ( 'user_language' );
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$amount = str_replace ( ',', '.', $amount );
		}else{
			$amount = str_replace ( ',', '', $amount );
		}
		// ASTPPCOM-857 end
		$cal_amount = ($amount * $to_cur_rate) / $from_cur_rate;

		if ($format_currency)
			$cal_amount = $this->format_currency ( $cal_amount );
		if ($append_currency)
			$cal_amount = $cal_amount;
		// ASTPPCOM-857 Ashish start
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$cal_amount = str_replace ( '.', ',', $cal_amount );
		}else{
			$cal_amount = str_replace ( ',', '', $cal_amount );
		}
		// ASTPPCOM-857 end

		return $cal_amount;
	}
	// ASTPPCOM-1319 Start
	function add_calculate_currency($amount = 0, $from_currency = '', $to_currency = '', $format_currency = true, $append_currency = true,$current_language = '') {
		// ASTPPCOM-1319 END
		// ASTPPCOM-857 Ashish start		
		$current_language= $current_language == '' ? $this->session->userdata ( 'user_language' ):$current_language;		
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$amount = str_replace ( '.', ',', $amount );
		}else{
			$amount = str_replace ( ',', '', $amount );
		}
		// ASTPPCOM-857 end
		if ($from_currency == '') {
			$from_currency1 = $this->session->userdata ['accountinfo'] ['currency_id'];
			$from_currency = $this->common->get_field_name ( 'currency', 'currency', $from_currency1 );
		}
		$to_currency = ($to_currency == '') ? self::$global_config ['system_config'] ['base_currency'] : $to_currency;
		if (self::$global_config ['currency_list'] [$from_currency] > 0) { 
			$cal_amount = ($amount * self::$global_config ['currency_list'] [$to_currency]) / self::$global_config ['currency_list'] [$from_currency];
			// Kinjal ASTPPCOM-1319 Start
			$cal_amount = str_replace(',','.',$cal_amount);
			// Kinjal ASTPPCOM-1319 End
		} else {
			$cal_amount = $amount;
		}

		if ($format_currency)
			$cal_amount = $this->format_currency ( $cal_amount );
		if ($append_currency)
			$cal_amount = $cal_amount . " " . $to_currency;
		// ASTPPCOM-857 Ashish start
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$cal_amount = str_replace ( '.', ',', $cal_amount );
		}else{
			$cal_amount = str_replace ( ',', '', $cal_amount );
		}
		// ASTPPCOM-857 end		
		return $cal_amount;
	}
	function to_calculate_currency($amount = 0, $from_currency = '', $to_currency = '', $format_currency = true, $append_currency = true) {
		if ($to_currency == '') {
			$to_currency1 = $this->session->userdata ['accountinfo'] ['currency_id'];
			$to_currency = $this->common->get_field_name ( 'currency', 'currency', $to_currency1 );
		}
		$from_currency = ($from_currency == '') ? self::$global_config ['system_config'] ['base_currency'] : $from_currency;
		
		$from_cur_rate = (self::$global_config ['currency_list'] [$from_currency] > 0) ? self::$global_config ['currency_list'] [$from_currency] : 1;
		$to_cur_rate = (self::$global_config ['currency_list'] [$to_currency]) ? self::$global_config ['currency_list'] [$to_currency] : 1;
		// ASTPPCOM-857 Ashish start
		$current_language=$this->session->userdata ( 'user_language' );
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$amount = str_replace ( '.', ',', $amount );
		}else{
			$amount = str_replace ( ',', '', $amount );
		}
		// ASTPPCOM-857 end
		$cal_amount = ($amount * $to_cur_rate) / $from_cur_rate;
		if ($format_currency)
			$cal_amount = $this->format_currency ( $cal_amount );
		if ($append_currency)
			$cal_amount = $cal_amount . " " . $to_currency;
		// ASTPPCOM-857 Ashish start
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$cal_amount = str_replace ( '.', ',', $cal_amount );
		}else{
			$cal_amount = str_replace ( ',', '', $cal_amount );
		}
		// ASTPPCOM-857 end
		return $cal_amount;
	}

	function format_currency($amount) {
		// ASTPPCOM-857 Ashish start
		$amount=number_format ( $amount, Common_model::$global_config ['system_config'] ['decimalpoints'] );
		$current_language=$this->session->userdata ( 'user_language' );
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE')){
			$amount = str_replace ( '.', ',', $amount );
		}else{
			$amount = str_replace ( ',', '', $amount );
		}
		return $amount;
		// ASTPPCOM-857 end
	}
	function money_format($format, $number) {
		$regex = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?' . '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
		if (setlocale ( LC_MONETARY, 0 ) == 'C') {
			setlocale ( LC_MONETARY, '' );
		}
		$locale = localeconv ();
		preg_match_all ( $regex, $format, $matches, PREG_SET_ORDER );
		foreach ( $matches as $fmatch ) {
			$value = floatval ( $number );
			$flags = array (
					'fillchar' => preg_match ( '/\=(.)/', $fmatch [1], $match ) ? $match [1] : ' ',
					'nogroup' => preg_match ( '/\^/', $fmatch [1] ) > 0,
					'usesignal' => preg_match ( '/\+|\(/', $fmatch [1], $match ) ? $match [0] : '+',
					'nosimbol' => preg_match ( '/\!/', $fmatch [1] ) > 0,
					'isleft' => preg_match ( '/\-/', $fmatch [1] ) > 0 
			);
			$width = trim ( $fmatch [2] ) ? ( int ) $fmatch [2] : 0;
			$left = trim ( $fmatch [3] ) ? ( int ) $fmatch [3] : 0;
			$right = trim ( $fmatch [4] ) ? ( int ) $fmatch [4] : $locale ['int_frac_digits'];
			$conversion = $fmatch [5];
			
			$positive = true;
			if ($value < 0) {
				$positive = false;
				$value *= - 1;
			}
			$letter = $positive ? 'p' : 'n';
			
			$prefix = $suffix = $cprefix = $csuffix = $signal = '';
			
			$signal = $positive ? $locale ['positive_sign'] : $locale ['negative_sign'];
			switch (true) {
				case $locale ["{$letter}_sign_posn"] == 1 && $flags ['usesignal'] == '+' :
					$prefix = $signal;
					break;
				case $locale ["{$letter}_sign_posn"] == 2 && $flags ['usesignal'] == '+' :
					$suffix = $signal;
					break;
				case $locale ["{$letter}_sign_posn"] == 3 && $flags ['usesignal'] == '+' :
					$cprefix = $signal;
					break;
				case $locale ["{$letter}_sign_posn"] == 4 && $flags ['usesignal'] == '+' :
					$csuffix = $signal;
					break;
				case $flags ['usesignal'] == '(' :
				case $locale ["{$letter}_sign_posn"] == 0 :
					$prefix = '(';
					$suffix = ')';
					break;
			}
			if (! $flags ['nosimbol']) {
				$currency = $cprefix . ($conversion == 'i' ? $locale ['int_curr_symbol'] : $locale ['currency_symbol']) . $csuffix;
			} else {
				$currency = '';
			}
			$space = $locale ["{$letter}_sep_by_space"] ? ' ' : '';
			
			$value = number_format ( $value, $right, $locale ['mon_decimal_point'], $flags ['nogroup'] ? '' : $locale ['mon_thousands_sep'] );
			$value = @explode ( $locale ['mon_decimal_point'], $value );
			
			$n = strlen ( $prefix ) + strlen ( $currency ) + strlen ( $value [0] );
			if ($left > 0 && $left > $n) {
				$value [0] = str_repeat ( $flags ['fillchar'], $left - $n ) . $value [0];
			}
			$value = implode ( $locale ['mon_decimal_point'], $value );
			if ($locale ["{$letter}_cs_precedes"]) {
				$value = $prefix . $currency . $space . $value . $suffix;
			} else {
				$value = $prefix . $value . $space . $currency . $suffix;
			}
			if ($width > 0) {
				$value = str_pad ( $value, $width, $flags ['fillchar'], $flags ['isleft'] ? STR_PAD_RIGHT : STR_PAD_LEFT );
			}
			
			$format = str_replace ( $fmatch [0], $value, $format );
		}
		return $format;
	}
	function get_list_taxes() {
		$this->db->select ( 'id,taxes_description' );
		// resellers saw the taxes of all resellers; like the tax dropdowns only the admin's and their own
		$logintype = $this->session->userdata ( 'logintype' );
		if ($logintype == 1 || $logintype == 5) {
			$accountinfo = $this->session->userdata ( 'accountinfo' );
			$this->db->where_in ( 'reseller_id', array ( 0, ( int ) $accountinfo ['id'] ) );
		}
		$query = $this->db->get ( "taxes" );
		$taxesList = array ();
		if ($query->num_rows () > 0) {
			return $query->result ();
		}
	}
	function get_params($table_name, $select, $where) {
		if (is_array ( $select )) {
		} else {
			$this->db->select ( $select );
		}
		if (is_array ( $where )) {
		} else {
			$this->db->where ( $where );
		}
		$query = $this->db->get ( $table_name );
		$query = $query->result ();
		return $query;
	}
	function get_parent_info($accountid) {
		$this->db->where ( 'id', $accountid );
		$this->db->select ( 'reseller_id,type' );
		$account_result = $this->db->get ( 'accounts' );
		$account_result = ( array ) $account_result->first_row ();
		if (isset ( $account_result ['reseller_id'] ) && $account_result ['reseller_id'] > 0) {
			return $account_result ['reseller_id'];
		} else {
			return '0';
		}
	}
	function apply_invoice_taxes($invoiceid, $account, $start_date) {
		$tax_priority = "";
		$where = array (
				"accountid" => $account ['id'] 
		);
		$accounttax_query = $this->db_model->getSelectWithOrder ( "*", "taxes_to_accounts", $where, "ASC", "taxes_priority" );
		if ($accounttax_query->num_rows () > 0) {
			$accounttax_query = $accounttax_query->result_array ();
			foreach ( $accounttax_query as $tax_value ) {
				$taxes_info = $this->db->get_where ( 'taxes', array (
						'id' => $tax_value ['taxes_id'] 
				) );
				if ($taxes_info->num_rows () > 0) {
					$tax_value = $taxes_info->result_array ();
					$tax_value = $tax_value [0];
					if ($tax_value ["taxes_priority"] == "") {
						$tax_priority = $tax_value ["taxes_priority"];
					} else if ($tax_value ["taxes_priority"] > $tax_priority) {
						$query = $this->db_model->getSelect ( "SUM(debit) as total", "invoice_details", array (
								"invoiceid" => $invoiceid 
						) );
						$query = $query->result_array ();
						$sub_total = $query ["0"] ["total"];
					}
					$tax_total = (($sub_total * ($tax_value ['taxes_rate'] / 100)) + $tax_value ['taxes_amount']);
					$tax_total = round ( $tax_total, self::$global_config ['system_config'] ['decimalpoints'] );
					$tax_array = array (
							"accountid" => $account ['id'],
							"reseller_id" => $account ['reseller_id'],
							"invoiceid" => $invoiceid,
							"item_id" => "0",
							"description" => $tax_value ['taxes_description'],
							"debit" => $tax_total,
							"credit" => "",
							"item_type" => "TAX",
							"created_date" => $start_date 
					);
					$this->db->insert ( "invoice_details", $tax_array );
				}
			}
		}
		return TRUE;
	}
	function calculate_taxes($accountinfo,$amount){ 
		$taxes_info=$this->get_account_taxes($accountinfo,$amount);

		if(!empty($taxes_info)){ 
			$total_tax= 0;
			foreach ( $taxes_info as $taxe_val ) {
				$total_tax += $taxe_val;
			}
			$tax_arr['tax'] = $taxes_info;
			$tax_arr['amount_without_tax'] = $amount;
			$tax_arr['amount_with_tax'] =$total_tax+ $amount;
			$tax_arr['total_tax'] = $total_tax;

			return $tax_arr;
		}			
	}
 	function get_account_taxes($accountinfo,$amount){	
		$taxes = "select * from taxes inner join taxes_to_accounts on taxes.id = taxes_to_accounts.taxes_id where taxes_to_accounts.accountid= ".$accountinfo ['id'];

		$taxes = $this->db->query ( $taxes );
		$total_tax = 0;
		$taxes_count= $taxes->num_rows ();
		$data = array();
		if ($taxes->num_rows () > 0) {
			$taxe_res = $taxes->result_array ();
			if(!empty($taxe_res)){
				foreach ( $taxe_res as $taxe_res_val ) {
					$data [$taxe_res_val['taxes_description']."-".$taxe_res_val['taxes_rate']."(%)"] = (($amount*$taxe_res_val['taxes_rate'])/100)+$taxe_res_val['taxes_amount'];
				}
			}
		}
		return $data;	
	}
	
	function check_unique_data($action,$Select,$value,$tbl) {
		$where = array (
				$Select =>$value
		);
		if($action=='edit'){
			$this->db->where ($where);
			$this->db->select ( "*" );
			$this->db->from ($tbl);
			$query = $this->db->get ();
		}else{
			$query = $this->db_model->countQuery ( "*", $tbl, $where);
		}	
		
		return $query;
	}

	//Gautam ASTPPENT-6696 Start ASTPPCOM-1348_kanu_start
	function calculate_currency_locale($amount = 0, $from_currency = '', $to_currency = '', $format_currency = true, $append_currency = true) {

		$from_currency = ($from_currency == '') ? self::$global_config ['system_config'] ['base_currency'] : $from_currency;
		
		if ($to_currency == '') {
			$to_currency1 = $this->session->userdata ['accountinfo'] ['currency_id'];
			$to_currency = $this->common->get_field_name ( 'currency', 'currency', $to_currency1 );
		}
		
		$from_cur_rate = (self::$global_config ['currency_list'] [$from_currency] > 0) ? self::$global_config ['currency_list'] [$from_currency] : 1;
		  
		 $to_cur_rate = (self::$global_config ['currency_list'] [$to_currency]) ? self::$global_config ['currency_list'] [$to_currency] : 1;

		$current_language=$this->session->userdata ( 'user_language' );

		if(isset($current_language) && ($current_language == 'es_ES'  || $current_language == 'pt_BR' || $current_language == 'el_GR' || $current_language == 'de_DE' || $current_language == 'fr_FR')){
			$amount = str_replace ( ',', '.', $amount );
		}else{
			$amount = str_replace ( ',', '', $amount );
		}
		$cal_amount = ((float)$amount * (float)$to_cur_rate) / (float)$from_cur_rate;  

		if ($format_currency)
			$cal_amount = $this->format_currency ( $cal_amount );
		if ($append_currency) {
			$cal_amount = $cal_amount . " " . $to_currency;
		}
		if(isset($current_language) && ($current_language == 'es_ES' || $current_language == 'el_GR' || $current_language == 'de_DE' || $current_language == 'fr_FR')){
			$cal_amount = str_replace ( '.', ',', $cal_amount );
		}else{
			$cal_amount = str_replace ( ',', '', $cal_amount );
		}
		return $cal_amount;
	}
	//Gautam ASTPPENT-6696 End ASTPPCOM-1348_kanu_end

}