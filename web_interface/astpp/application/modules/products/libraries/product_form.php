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
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
#[\AllowDynamicProperties]
class Product_form {
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
	}
	function get_product_search_form() {
		$form ['forms'] = array (
				"",
				array (
						'id' => "product_search" 
				) 
		);
		$accountinfo=$this->CI->session->userdata('accountinfo');
		if($accountinfo['type'] == 1 && astpp_eq0($accountinfo['is_distributor']) ){
		$form [gettext ( 'Search' )] = array (
				array (
						gettext ( 'Name' ),
						'INPUT',
						array (
								'name' => 'name[name]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'name[name-string]',
						'',
						'',
						'',
						'search_string_type',
						'' 
				),
				array (
						gettext ( 'Product Category' ),
						'product_category',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'name',
						'category',
						'build_dropdown',
						'',
						'' 
				),
				array (
						gettext ( 'Country' ),
						'country_id',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'country',
						'countrycode',
						'build_dropdown_country_camel',
						'',
						'' 
				),
				array (
						gettext ( 'Buy Cost' ),
						'INPUT',
						array (
								'name' => 'buy_cost[buy_cost]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'buy_cost[buy_cost-integer]',
						'',
						'',
						'',
						'search_int_type',
				),
				array (
						gettext ( 'Setup Fee' ),
						'INPUT',
						array (
								'name' => 'setup_fee[setup_fee]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'setup_fee[setup_fee-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Price' ),
						'INPUT',
						array (
								'name' => 'price[price]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'price[price-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Billing Days' ),
						'INPUT',
						array (
								'name' => 'billing_days[billing_days]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'billing_days[billing_days-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Billing Type' ),
						'billing_type',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'',
						'',
						'',
						'',
						'renewal_type_category',
						'',
						'' 
				),
				array (
						gettext ( 'Free Minutes' ),
						'INPUT',
						array (
								'name' => 'free_minutes[free_minutes]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'free_minutes[free_minutes-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						'',
						'HIDDEN',
						'ajax_search',
						'1',
						'',
						'',
						'' 
				),
				array (
						'',
						'HIDDEN',
						'advance_search',
						'1',
						'',
						'',
						'' 
				) 
		);
		}else{
		$form [gettext ( 'Search' )] = array (
				array (
						gettext ( 'Name' ),
						'INPUT',
						array (
								'name' => 'name[name]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'name[name-string]',
						'',
						'',
						'',
						'search_string_type',
						'' 
				),
				array (
						gettext ( 'Country' ),
						'country_id',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'country',
						'countrycode',
						'build_dropdown_country_camel',
						'',
						'' 
				),
				array (
						gettext ( 'Product Category' ),
						'product_category',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'name',
						'category',
						'build_dropdown',
						'',
						'' 
				),
				array (
						gettext ( 'Buy Cost' ),
						'INPUT',
						array (
								'name' => 'buy_cost[buy_cost]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'buy_cost[buy_cost-integer]',
						'',
						'',
						'',
						'search_int_type',
				),
				array (
						gettext ( 'Setup Fee' ),
						'INPUT',
						array (
								'name' => 'setup_fee[setup_fee]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'setup_fee[setup_fee-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Price' ),
						'INPUT',
						array (
								'name' => 'price[price]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'price[price-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				
				array (
						gettext ( 'Commission' ),
						'INPUT',
						array (
								'name' => 'commission[commission]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'commission[commission-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Billing Days' ),
						'INPUT',
						array (
								'name' => 'billing_days[billing_days]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'billing_days[billing_days-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Billing Type' ),
						'billing_type',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'',
						'',
						'',
						'',
						'renewal_type_category',
						'',
						'' 
				),
				array (
						gettext ( 'Status' ),
						'status',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'',
						'',
						'',
						'set_search_status',
						'',
						'' 
				),
				
				array (
						'',
						'HIDDEN',
						'ajax_search',
						'1',
						'',
						'',
						'' 
				),
				array (
						'',
						'HIDDEN',
						'advance_search',
						'1',
						'',
						'',
						'' 
				) 
		);
	   }
		
		if($accountinfo['type'] == 1 ){ 
			unset($form['Search']['1']);
		}
		$form ['button_search'] = array (
				'name' => 'action',
				'id' => "product_search_btn",
				'content' => gettext ( 'Search' ),
				'value' => 'save',
				'type' => 'button',
				'class' => 'btn btn-success float-right' 
		);
		$form ['button_reset'] = array (
				'name' => 'action',
				'id' => "id_reset",
				'content' => gettext ( 'Clear' ),
				'value' => 'cancel',
				'type' => 'reset',
				'class' => 'btn btn-secondary float-right  ml-2' 
		);
		
		return $form;
	}
	

	function get_product_listing_search_form() {
		$form ['forms'] = array (
				"",
				array (
						'id' => "product_search" 
				) 
		);
		$accountinfo=$this->CI->session->userdata('accountinfo');
		if($accountinfo['type'] == 1 && astpp_eq0($accountinfo['is_distributor']) ){
		$form [gettext ( 'Search' )] = array (
				array (
						gettext ( 'Name' ),
						'INPUT',
						array (
								'name' => 'name[name]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'name[name-string]',
						'',
						'',
						'',
						'search_string_type',
						'' 
				),
				
				array (
						gettext ( 'Product Category' ),
						'product_category',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'name',
						'category',
						'build_dropdown',
						'',
						'' 
				),
				array (
						gettext ( 'Country' ),
						'country_id',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'country',
						'countrycode',
						'build_dropdown_country_camel',
						'',
						'' 
				),
				array (
						gettext ( 'Setup Fee' ),
						'INPUT',
						array (
								'name' => 'setup_fee[setup_fee]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'setup_fee[setup_fee-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Price' ),
						'INPUT',
						array (
								'name' => 'price[price]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'price[price-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Billing Days' ),
						'INPUT',
						array (
								'name' => 'billing_days[billing_days]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'billing_days[billing_days-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Billing Type' ),
						'billing_type',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'',
						'',
						'',
						'',
						'renewal_type_category',
						'',
						'' 
				),
				array (
						gettext ( 'Free Minutes' ),
						'INPUT',
						array (
								'name' => 'free_minutes[free_minutes]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'free_minutes[free_minutes-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						'',
						'HIDDEN',
						'ajax_search',
						'1',
						'',
						'',
						'' 
				),
				array (
						'',
						'HIDDEN',
						'advance_search',
						'1',
						'',
						'',
						'' 
				) 
		);
		}else{
		$form [gettext ( 'Search' )] = array (
				array (
						gettext ( 'Name' ),
						'INPUT',
						array (
								'name' => 'name[name]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'name[name-string]',
						'',
						'',
						'',
						'search_string_type',
						'' 
				),
				array (
						gettext ( 'Country' ),
						'country_id',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'country',
						'countrycode',
						'build_dropdown_country_camel',
						'',
						'' 
				),
				array (
						gettext ( 'Product Category' ),
						'product_category',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'name',
						'category',
						'build_dropdown',
						'',
						'' 
				),
				array (
						gettext ( 'Setup Fee' ),
						'INPUT',
						array (
								'name' => 'setup_fee[setup_fee]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'setup_fee[setup_fee-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Price' ),
						'INPUT',
						array (
								'name' => 'price[price]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'price[price-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				
				array (
						gettext ( 'Commission' ),
						'INPUT',
						array (
								'name' => 'commission[commission]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'commission[commission-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Billing Days' ),
						'INPUT',
						array (
								'name' => 'billing_days[billing_days]',
								'',
								'size' => '20',
								'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'billing_days[billing_days-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Billing Type' ),
						'billing_type',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'',
						'',
						'',
						'',
						'renewal_type_category',
						'',
						'' 
				),
				array (
						gettext ( 'Status' ),
						'status',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'',
						'',
						'',
						'set_search_status',
						'',
						'' 
				),
				
				array (
						'',
						'HIDDEN',
						'ajax_search',
						'1',
						'',
						'',
						'' 
				),
				array (
						'',
						'HIDDEN',
						'advance_search',
						'1',
						'',
						'',
						'' 
				) 
		);
	   }
		
		if($accountinfo['type'] == 1 ){ 
			unset($form['Search']['1']);
		}
		$form ['button_search'] = array (
				'name' => 'action',
				'id' => "product_search_btn",
				'content' => gettext ( 'Search' ),
				'value' => 'save',
				'type' => 'button',
				'class' => 'btn btn-success float-right' 
		);
		$form ['button_reset'] = array (
				'name' => 'action',
				'id' => "id_reset",
				'content' => gettext ( 'Clear' ),
				'value' => 'cancel',
				'type' => 'reset',
				'class' => 'btn btn-secondary float-right  ml-2' 
		);
		
		return $form;
	}
	
	function build_product_list_for_admin($opting_id = "") {
		$account_info = $accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$currency_id = $account_info ['currency_id'];
		$currency = $this->CI->common->get_field_name ( 'currency', 'currency', $currency_id );
		if($account_info['type'] == 1){
			$status = array (
						gettext ( "Status" ),
						"90",
						"status",
						"status",
						"reseller_products",
						"get_status",
						"",
						"false",
						"center" 
				);

		}else{
			$status = array (
						gettext ( "Status" ),
						"90",
						"status",
						"status",
						"products",
						"get_status",
						"",
						"false",
						"center" 
				);
		}
		if(($this->CI->session->userdata ( 'logintype' ) == 1 || $this->CI->session->userdata ( 'logintype' ) == 5) && (astpp_eq0($account_info['is_distributor']))){
		$grid_field_arr = json_encode ( array (
				array (
						"<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
						"30",
						"",
						"",
						"",
						"",
						"",
						"false",
						"center" 
				),
				array (
						gettext ( "Name" ),
						"130",
						"name",
						"",
						"",
						"",
						"EDITABLE",
						"true",
						"left" 
				),
				array (
						gettext ( "Country" ),
						"130",
						"country_id",
						"country",
						"countrycode",
						"get_field_name",
						"",
						"true",
						"left" 
				),
				array (				          												
					gettext( 'Category' ),												
						"85",
						"product_category",
						"name",
						"category",
						"get_field_name",
						"",
						"true",
						"left"
				),

				array (
						gettext ( "Buy Cost"  )."<br>($currency)",
						"85",
						"buy_cost",
						"buy_cost",
						"buy_cost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Setup Fee" )."<br>($currency)",
						"85",
						"setup_fee",
						"setup_fee",
						"setup_fee",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Price" )." ($currency)",
						"90",
						"price",
						"price",
						"price",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Billing Days" ),
						"115",
						"billing_days",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( 'Billing Type' ),
						"85",
						"billing_type",
						"billing_type",
						"billing_type",
						"get_renewal_type_category_list",
						"",
						"true",
						"left" 
						
				),
				array (
						gettext ( "Free Minutes" ),
						"150",
						"free_minutes",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Modified Date" ),
						"140",
						"last_modified_date",
						"last_modified_date",
						"last_modified_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
				),
				$status,
				
				array (
						gettext ( "Action" ),
						"120",
						"",
						"",
						"",
						array (
								"EDIT" => array (
										"url" => "products/products_edit/",
										"mode" => "single" 
								),
								"DELETE" => array (
										"url" => "products/products_delete/",
										"mode" => "single" 
								) 
						),
						"false" 
				) 
		) );
		}elseif(($this->CI->session->userdata ( 'logintype' ) == 1 || $this->CI->session->userdata ( 'logintype' ) == 5) && ($account_info['is_distributor'] == 1)){
		$grid_field_arr = json_encode ( array (
				array (
						"<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
						"30",
						"",
						"",
						"",
						"",
						"",
						"false",
						"center" 
				),
				array (
						gettext ( "Name" ),
						"130",
						"name",
						"",
						"",
						"",
						"EDITABLE",
						"true",
						"left" 
				),
				array (
						gettext ( "Country" ),
						"130",
						"country_id",
						"country",
						"countrycode",
						"get_field_name",
						"",
						"true",
						"left" 
				),
				array (
						gettext( 'Category' ),												
						"85",
						"product_category",
						"name",
						"category",
						"get_field_name",
						"",
						"true",
						"left"
				),

				array (
						gettext ( "Buy Cost"  )."<br>($currency)",
						"85",
						"buy_cost",
						"buy_cost",
						"buy_cost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Setup Fee" )."<br>($currency)",
						"85",
						"setup_fee",
						"setup_fee",
						"setup_fee",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Price" )." ($currency)",
						"90",
						"price",
						"price",
						"price",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Commission")." (%)",
						"120",
						"commission",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),

				array (
						gettext ( "Billing Days" ),
						"115",
						"billing_days",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( 'Billing Type' ),
						"85",
						"billing_type",
						"billing_type",
						"billing_type",
						"get_renewal_type_category_list",
						"",
						"true",
						"left" 
						
				),
				array (
						gettext ( "Free Minutes" ),
						"150",
						"free_minutes",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Modified Date" ),
						"140",
						"last_modified_date",
						"last_modified_date",
						"last_modified_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
				),
				$status,
				
				array (
						gettext ( "Action" ),
						"120",
						"",
						"",
						"",
						array (
								"EDIT" => array (
										"url" => "products/products_edit/",
										"mode" => "single" 
								),
								"DELETE" => array (
										"url" => "products/products_delete/",
										"mode" => "single" 
								) 
						),
						"false" 
				) 
		) );
	}else{
		$grid_field_arr = json_encode ( array (
				array (
						"<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
						"30",
						"",
						"",
						"",
						"",
						"",
						"false",
						"center" 
				),
				array (
						gettext ( "Name" ),
						"130",
						"name",
						"",
						"",
						"",
						"EDITABLE",
						"true",
						"left" 
				),
				array (
						gettext ( "Country" ),
						"130",
						"country_id",
						"country",
						"countrycode",
						"get_field_name",
						"",
						"true",
						"left" 
				),
				array (
						gettext( 'Category' ),												
						"85",
						"product_category",
						"name",
						"category",
						"get_field_name",
						"",
						"true",
						"left"
				),

				array (
						gettext ( "Buy Cost"  )."<br>($currency)",
						"85",
						"buy_cost",
						"buy_cost",
						"buy_cost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Setup Fee" )."<br>($currency)",
						"85",
						"setup_fee",
						"setup_fee",
						"setup_fee",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Price" )." ($currency)",
						"90",
						"price",
						"price",
						"price",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Commission")." (%)",
						"120",
						"commission",
						"commission",
						"commission",
						"decimal_currency",
						"",
						"",
						"true",
						"right" 
				),

				array (
						gettext ( "Billing Days" ),
						"115",
						"billing_days",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( 'Billing Type' ),
						"85",
						"billing_type",
						"billing_type",
						"billing_type",
						"get_renewal_type_category_list",
						"",
						"true",
						"left" 
						
				),
				array (
						gettext ( "Free Minutes" ),
						"150",
						"free_minutes",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),
				array (
					gettext ( "Reseller"),
						"80",
						"reseller_id",
						"first_name",
						"accounts",
						"get_field_name",
						"",
						"true",
						"center"
				),
				array (
						gettext ( "Modified Date" ),
						"140",
						"last_modified_date",
						"last_modified_date",
						"last_modified_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
				),
				$status,
				array (
						gettext ( "Action" ),
						"120",
						"",
						"",
						"",
						array (
								"EDIT" => array (
										"url" => "products/products_edit/",
										"mode" => "single" 
								),
								"DELETE" => array (
										"url" => "products/products_delete/",
										"mode" => "single" 
								) 
						),
						"false" 
				) 
		) );
	  }
		return $grid_field_arr;
	}
	function build_product_list_for_admin_products() {
		$account_info = $this->CI->session->userdata ( 'accountinfo' );
		$currency_id = $account_info ['currency_id'];
		$currency = $this->CI->common->get_field_name ( 'currency', 'currency', $currency_id );
		if(astpp_gt($account_info['reseller_id'], 0) ){
			$buy_cost =array (
						gettext ( "Buy Cost" )." ($currency)",
						"85",
						"buycost",
						"buycost",
						"buycost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
					);


		}else if($this->CI->session->userdata ( 'logintype' ) == 1 && astpp_eq0($account_info['reseller_id']) ){
			$buy_cost =array (
						gettext ( "Buy Cost" )." ($currency)",
						"85",
						"buycst",
						"buycst",
						"buycst",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
					);
		}else{
			$buy_cost =array (
						gettext ( "Buy Cost" )." ($currency)",
						"85",
						"buy_cost",
						"buy_cost",
						"buy_cost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				         );


		}
		if(($this->CI->session->userdata ( 'logintype' ) == 1 || $this->CI->session->userdata ( 'logintype' ) == 5) && (astpp_eq0($account_info['is_distributor']))){
			$grid_field_arr = json_encode ( array (
					
					
					array (
							gettext ( "Name" ),
							"130",
							"name",
							"",
							"",
							"",
							"",
							"true",
							"left" 
					),
					array (
						gettext ( "Country" ),
						"130",
						"country_id",
						"country",
						"countrycode",
						"get_field_name",
						"",
						"true",
						"left" 
				),
					array (
							gettext ( 'Category' ),												
							"85",
							"product_category",
							"name",
							"category",
							"get_field_name",
							"",
							"true",
							"left"
					),
					$buy_cost,
					array (
							gettext ( "Setup Fee" )." ($currency)",
							"90",
							"setup_fee",
							"setup_fee",
							"setup_fee",
							"convert_to_currency_account",
							"",
							"true",
							"right" 
					),
					array (
							gettext ( "Price" )." ($currency)",
							"90",
							"price",
							"price",
							"price",
							"convert_to_currency_account",
							"",
							"true",
							"right" 
					),
					array (
							gettext ( 'Billing Type' ),
							"85",
							"billing_type",
							"billing_type",
							"billing_type",
							"get_renewal_type_category_list",
							"",
							"true",
							"left" 
							
					),
					array (
							gettext ( "Billing Days" ),
							"115",
							"billing_days",
							"",
							"",
							"",
							"",
							"true",
							"right" 
					),
					array (
							gettext ( "Free Minutes" ),
							"150",
							"free_minutes",
							"",
							"",
							"",
							"",
							"true",
							"left" 
					),
					array (
							gettext ( "Optin" ),
							"90",
							"id",
							"id",
							"id",
							"optin_status",
							"",
							"false",
							"center" 
					),
					array (
							gettext ( "Modified Date" ),
							"140",
							"last_modified_date",
							"last_modified_date",
							"last_modified_date",
							"convert_GMT_to",
							"",
							"true",
							"center" 
					),
					array (
							gettext ( "Action" ),
							"120",
							"",
							"",
							"",
							array (
									"EDIT" => array (
											"url" => "products/products_edit/",
											"mode" => "single" 
									),
									"DELETE" => array (
											"url" => "products/products_delete/",
											"mode" => "single" 
									) 
							),
							"false" 
					) 
			) );
		}else if(($this->CI->session->userdata ( 'logintype' ) == 1 || $this->CI->session->userdata ( 'logintype' ) == 5) && ($account_info['is_distributor'] == 1)){
			$grid_field_arr = json_encode ( array (
				
				
				array (
						gettext ( "Name" ),
						"130",
						"name",
						"",
						"",
						"",
						"",
						"true",
						"left" 
				),
				array (
						gettext ( 'Category' ),												
						"85",
						"product_category",
						"name",
						"category",
						"get_field_name",
						"",
						"true",
						"left"
				),
				$buy_cost,
				
				array (
						gettext ( "Setup Fee" )." ($currency)",
						"90",
						"setup_fee",
						"setup_fee",
						"setup_fee",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Price" )." ($currency)",
						"90",
						"price",
						"price",
						"price",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				 array (
						gettext ( "Commission")." (%)",
						"120",
						"commission",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),

				array (
						gettext ( 'Billing Type' ),
						"85",
						"billing_type",
						"billing_type",
						"billing_type",
						"get_renewal_type_category_list",
						"",
						"true",
						"left" 
						
				),
				array (
						gettext ( "Billing Days" ),
						"115",
						"billing_days",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Free Minutes" ),
						"150",
						"free_minutes",
						"",
						"",
						"",
						"",
						"true",
						"left" 
				),
				array (
						gettext ( "Optin" ),
						"90",
						"id",
						"id",
						"id",
						"optin_status",
						"",
						"false",
						"center" 
				),
				array (
						gettext ( "Modified Date" ),
						"140",
						"last_modified_date",
						"last_modified_date",
						"last_modified_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Action" ),
						"120",
						"",
						"",
						"",
						array (
								"EDIT" => array (
										"url" => "products/products_edit/",
										"mode" => "single" 
								),
								"DELETE" => array (
										"url" => "products/products_delete/",
										"mode" => "single" 
								) 
						),
						"false" 
				) 
		) );

		}else{
		$grid_field_arr = json_encode ( array (
					array (
							"<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
							"30",
							"",
							"",
							"",
							"",
							"",
							"false",
							"center" 
					),
					
					array (
							gettext ( "Name" ),
							"130",
							"name",
							"",
							"",
							"",
							"",
							"true",
							"left" 
					),
					array (
							gettext ( 'Category' ),												
							"85",
							"product_category",
							"name",
							"category",
							"get_field_name",
							"",
							"true",
							"left"
					),
					$buy_cost,
					
					array (
							gettext ( "Setup Fee" )." ($currency)",
							"90",
							"setup_fee",
							"setup_fee",
							"setup_fee",
							"convert_to_currency_account",
							"",
							"true",
							"right" 
					),
					array (
							gettext ( "Price" )." ($currency)",
							"90",
							"price",
							"price",
							"price",
							"convert_to_currency_account",
							"",
							"true",
							"right" 
					),
					array (
							gettext ( 'Billing Type' ),
							"85",
							"billing_type",
							"billing_type",
							"billing_type",
							"get_renewal_type_category_list",
							"",
							"true",
							"left" 
							
					),
					array (
							gettext ( "Billing Days" ),
							"115",
							"billing_days",
							"",
							"",
							"",
							"",
							"true",
							"right" 
					),
					array (
							gettext ( "Free Minutes" ),
							"150",
							"free_minutes",
							"",
							"",
							"",
							"",
							"true",
							"left" 
					),
					array (
							gettext ( "Optin" ),
							"90",
							"id",
							"id",
							"id",
							"optin_status",
							"",
							"false",
							"center" 
					),
					array (
							gettext ( "Modified Date" ),
							"140",
							"last_modified_date",
							"last_modified_date",
							"last_modified_date",
							"convert_GMT_to",
							"",
							"true",
							"center" 
					),
					array (
							gettext ( "Action" ),
							"120",
							"",
							"",
							"",
							array (
									"EDIT" => array (
											"url" => "products/products_edit/",
											"mode" => "single" 
									),
									"DELETE" => array (
											"url" => "products/products_delete/",
											"mode" => "single" 
									) 
							),
							"false" 
					) 
			) );
		}
		return $grid_field_arr;
	}
	function build_grid_buttons() {
		if($this->CI->session->userdata ( 'logintype' ) == 1 || $this->CI->session->userdata ( 'logintype' ) == 5){
			$buttons_json = json_encode ( array (
				array (
					gettext ( "Delete" ),
					"btn btn-line-danger",
					"fa fa-times-circle fa-lg",
					"button_action",
					"/products/products_delete_multiple/",
					"",
					"",
					"delete"
					)
				) );
		}else{
			$buttons_json = json_encode ( array (
				array (
					gettext ( "Create" ),
						"btn btn-line-warning btn",
						"fa fa-plus-circle fa-lg",
						"button_action",
						"/products/products_add/",
						"",
						"",
						"create"
					),
					array (
						gettext ( "Delete" ),
						"btn btn-line-danger",
						"fa fa-times-circle fa-lg",
						"button_action",
						"/products/products_delete_multiple/",
						"",
						"",
						"delete"
				)
			) );
		}
		return $buttons_json;
		}
	function build_pattern_list_for_customer($productid) {
		$grid_field_arr = json_encode ( array (
				array (
						"<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
						"30",
						"",
						"",
						"",
						"",
						"",
						"false",
						"center" 
				),
				array (
						gettext ( "Code" ),
						"100",
						"patterns",
						"patterns",
						"",
						"get_only_numeric_val" 
				),
				array (
						gettext ( "Destination" ),
						"100",
						"destination",
						"",
						"",
						"" 
				),
				array (
						gettext ( "Country Name" ),
						"100",
						"country_id",
						"country",
						"countrycode",
						"get_field_name",
						"",
						"true",
						"left" 
				)
				
				
		) );
		return $grid_field_arr;
	}
	function build_block_pattern_list_for_customer($productid = "") {
		$grid_field_arr = json_encode ( array (
				
				array (
						gettext ( "Code" ),
						"100",
						"pattern",
						"pattern",
						"",
						"get_only_numeric_val" 
				),
				array (
						gettext ( "Destination" ),
						"100",
						"comment",
						"",
						"",
						"" 
				) ,
				array (
						gettext ( "Country Name" ),
						"100",
						"country_id",
						"country",
						"countrycode",
						"get_field_name",
						"",
						"true",
						"left" 
				)
		) );
		return $grid_field_arr;
	}
	function build_products_list_for_customer($accountid, $accounttype) { 	
	        $accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$grid_field_arr = json_encode ( array (
				array (
						"<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
						"20",
						"",
						"id1",
						"",
						"",
						"",
						"false",
						"center" 
				),
				array (
						gettext ( "Name" ),
						"200",
						"name",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),	
				array (
						gettext ( "Setup Fee" ),
						"200",
						"setup_fee",
						"setup_fee",
						"setup_fee",
						"convert_to_currency_account",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Price" ),
						"200",
						"price",
						"price",
						"price",
						"convert_to_currency_account",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( 'Billing Type' ),
						"85",
						"billing_type",
						"billing_type",
						"billing_type",
						"get_renewal_type_category_list",
						"",
						"true",
						"left" 
							
				),
				array (
						gettext ( "Billing Days" ),
						"115",
						"billing_days",
						"",
						"",
						"",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Free Minutes" ),
						"150",
						"free_minutes",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),

				array (
						gettext ( "Action" ),
						"126",
						"",
						"",
						"",
						array (
								"DELETE" => array (
										"url" => "accounts/".$accounttype. "_products_action/delete/$accountid/$accounttype/",
										"mode" => "single" 
								) 
						),
						"false" 
				) 
		) );
		return $grid_field_arr;
	}
}
?>
