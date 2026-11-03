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
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
#[\AllowDynamicProperties]
class Internationalcredits_form extends common {
	function __construct() {
		$this->CI = & get_instance ();
	}
	function build_internationalcredits_list_for_admin(){
		$account_info = $accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$currency_id  = $account_info ['currency_id'];
		$currency     = $this->CI->common->get_field_name ( 'currency', 'currency', $currency_id );
		$grid_field_arr = json_encode ( array (
				array (
						gettext ( "Account" ),
						"110",
						"id",
						"first_name,last_name,number",
						"accounts",
						"get_field_name_coma_new",
						"",
						"true",
						"left" 
				),
				array (
						gettext ("First Name"),
						"80",
						"first_name",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Last Name" ),
						"80",
						"last_name",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Company" ),
						"90",
						"company_name",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Type" ),
						"90",
						"posttoexternal",
						"posttoexternal",
						"accounts",
						"set_account_type_recharge",
						"",
						"true",
						"center" 
				),
				
				array (
						gettext ( "Balance <br/>" ) ."($currency)",
						"80",
						"balance",
						"balance",
						"balance",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "Credit Limit <br/>" ) . "($currency)",
						"90",
						"credit_limit",
						"credit_limit",
						"credit_limit",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "International Balance <br/>" ) . "($currency)",
						"150",
						"int_balance",
						"int_balance",
						"int_balance",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
				array (
						gettext ( "International Credit Limit <br/>" ) . "($currency)",
						"200",
						"int_credit_limit",
						"int_credit_limit",
						"int_credit_limit",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
				),
			
				array (
						"Reseller",
						"85",
						"reseller_id",
						"first_name,last_name,number",
						"accounts",
						"reseller_select_value",
						"",
						"true",
						"center" 
				),
					
				array (
						gettext ( "Action" ),
						"125",
						"",
						"",
						"",
						array (
								"PAYMENT" => array (
										"url" => "accounts/customer_payment_process_add/",
										"mode" => "single" 
								),
								// "CALLERID" => array (
								// 		"url" => "accounts/customer_add_callerid/",
								// 		"mode" => "popup" 
								// ),
								"EDIT" => array (
										"url" => "accounts/customer_edit/",
										"mode" => "single" 
								)
						),
						"false"
				)
		) );
		return $grid_field_arr;
	}


	function build_grid_buttons(){
		$buttons_json = json_encode ( array (
			array (
					gettext ( "Recharge" ),
					"btn btn-line-warning btn",
					"fa fa-plus-circle fa-lg",
					"button_action",
					"/internationalcredits/internationalcredits_recharge_add/",
					"popup",
					"medium",
					"recharge" 
			)
			
		) );
		return $buttons_json;
	}


	function get_search_internationalcredits_form(){
		$account_data = $this->CI->session->userdata ( "accountinfo" );
		$reseller_id = $account_data ['type'] == 1 ? $account_data ['id'] : 0;
		$form ['forms'] = array (
				"",
				array (
						'id' => "internationalcredits_list_search" 
				) 
		);
		$form [gettext ( 'Search' )] = array (
				
				array (
					'Account',
					'id',
					'SELECT',
					'',
					array (
							"name" => "accountid",
							"rules" => "did_account_checking" 
					),
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'first_name,last_name,number',
					'accounts',
					'build_concat_dropdown',
					'where_arr',
					array (
							"type"    => "0,3",
							"deleted" => "0",
							"status"  => "0"
					)
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
		$form ['button_search'] = array (
				'name' => 'action',
				'id' => "internationalcredits_search",
				'content' => gettext ( 'Search' ),
				'value' => 'save',
				'type' => 'button',
				'class' => 'btn btn-success pull-right' 
		);
		$form ['button_reset'] = array (
				'name' => 'action',
				'id' => "id_reset",
				'content' => gettext ( 'Clear' ),
				'value' => 'cancel',
				'type' => 'reset',
				'class' => 'btn btn-secondary pull-right margin-x-10' 
		);
		
		return $form;
	}
	function internationalcredits_recharge_add_form() {
		$account_info = $accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$currency_id  = $account_info ['currency_id'];
		$currency     = $this->CI->common->get_field_name ( 'currency', 'currency', $currency_id );
		$form ['forms'] = array (
				base_url () . 'internationalcredits/internationalcredits_save/',
				array (
						"id" => "internationalcredits_add_form",
						'method' => 'POST',
						"name" => "internationalcredits_add_form" 
				) 
		);
		if ($this->CI->session->userdata ( 'logintype' ) != 1 && $this->CI->session->userdata ( 'logintype' ) != 5) {
			$form [gettext ( 'Basic Details' )] = array (
				array (
						gettext ( 'Reseller' ),
							array ( 
								'name'=>'reseller_id',
								'id'=>'reseller_id_drp',
								'class'=>'reseller_id_drp'
							),  
							'SELECT',
							'',
							'',
							'tOOL TIP',
							'Please Enter account number',
							'id',
							'first_name,last_name,number',
							'accounts',
							'build_concat_dropdown_reseller',
							'',
							''
	
						) ,
				   array (
					gettext ( 'Account' ),
						array ( 
							'name'=>'accountid',
							'id'=>'accountid_search_drp',
							'class'=>'accountid_search_drp'
						),  
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'number',
						'accounts',
						'build_dropdown',
						'where_arr',
						array (
								"reseller_id" => "0",
								"type" => "GLOBAL",
								"status"=>0 
						) 

					) ,
			array (
					gettext ( 'International Balance ('.$currency.') *' ),
					'INPUT',
					array (
							'name' => 'int_balance',
							'size' => '20',
							'class' => "text field medium" 
					),
					'trim',
					'TOOL TIP',
					'Please Enter International Balance'
			),	
			array (
					gettext ( 'International Credit Limit ('.$currency.') *' ),
					'INPUT',
					array (
							'name' => 'int_credit_limit',
							'size' => '20',
							'class' => "text field medium" 
					),
					'trim',
					'tOOL TIP',
					'Please Enter International Credit Limit'
			)
			);	
		}else{
			$form [gettext ( 'Basic Details' )] = array (
				array (
					gettext ( 'Account' ),
					array ( 
						'name'=>'accountid',
						'id'=>'accountid_search_drp',
						
					), 
					'SELECT',
					'',
					'',
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'first_name,last_name,number',
					'accounts',
					'build_concat_dropdown_cutomer_reseller',
					'',
					''
			),
			array (
					gettext ( 'International Balance ('.$currency.') *' ),
					'INPUT',
					array (
							'name' => 'int_balance',
							'size' => '20',
							'class' => "text field medium" 
					),
					'trim',
					'TOOL TIP',
					'Please Enter International Balance'
			),	
			array (
					gettext ( 'International Credit Limit ('.$currency.') *' ),
					'INPUT',
					array (
							'name' => 'int_credit_limit',
							'size' => '20',
							'class' => "text field medium" 
					),
					'trim',
					'tOOL TIP',
					'Please Enter International Credit Limit'
			)
			);	
		}
		
		$form ['button_save'] = array (
				'name' => 'action',
				'content' => gettext ( 'Save' ),
				'value' => 'save',
				'id' => 'submit',
				'type' => 'button',
				'class' => 'btn btn-success' 
		);
		$form ['button_cancel'] = array (
				'name' => 'action',
				'content' => gettext ( 'Close' ),
				'value' => 'cancel',
				'type' => 'button',
				'class' => 'btn btn-secondary mx-2',
				'onclick' => 'return redirect_page(\'NULL\')' 
		);
		return $form;
	}
}
