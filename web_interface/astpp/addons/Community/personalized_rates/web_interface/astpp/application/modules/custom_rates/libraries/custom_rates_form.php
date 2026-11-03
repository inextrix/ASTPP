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
class custom_rates_form extends common {
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
	}


	function get_custom_rate_form_fields($id ='',$reseller_id='') {
		$logintype = $this->CI->session->userdata ( 'userlevel_logintype' );
		$account_info = $accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo ['id'] : 0;
		$currency_id  = $account_info ['currency_id'];
		$currency     = $this->CI->common->get_field_name ( 'currency', 'currency', $currency_id );
		$accountinfo = $this->CI->session->userdata("accountinfo");
		$reseller_drp ='';
		$account_field='';
		if($account_info['type'] == -1 || $account_info['type'] == 2 ){	
			if (astpp_gt($id, 0)) {	
				$account_info = $this->CI->session->userdata('accountinfo');
				$reseller_drp = array(
					gettext('Reseller'),
					array(
						'name' => 'reseller_id',
						'class' => 'reseller',
						'onchange' => 'account_change_add(this.value)'
					),
					'SELECT',
					'',
					'',
					'tOOL TIP',
					'Please Enter account number',
					'id',                           
					'first_name,last_name,number,company_name',  
					'accounts',
					'build_concat_dropdown_reseller', 
					'',
					''
				);

				$account_drp = array(
					gettext('Account'),
					array(
						'name' => 'accountid',
						'id' => 'account_id'
					),
					'SELECT',
					'',
					'trim|xss_clean|required',
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'first_name,last_name,number,company_name',
					'accounts',
					'build_concat_dropdown',
					'where_arr'
				);
			}else{
				$account_info = $this->CI->session->userdata('accountinfo');
				$reseller_drp = array(
					gettext('Reseller'),
					array(
						'name' => 'reseller_id',
						'class' => 'reseller',
						'onchange' => 'account_change_add(this.value)'
					),
					'SELECT',
					'',
					'',
					'tOOL TIP',
					'Please Enter account number',
					'id',                           
					'first_name,last_name,number,company_name',  
					'accounts',
					'build_concat_dropdown_reseller', 
					'',
					''
				);
				$account_drp = array(
					gettext('Account'),
					array(
						'name' => 'accountid',
						'id' => 'account_id'
					),
					'SELECT',
					'',
					'trim|xss_clean|required',
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'first_name,last_name,number,company_name',
					'accounts',
					'build_concat_dropdown',
					'where_arr',
					array(
						"reseller_id" => $account_info['reseller_id'],
						"type" => "0",
						"deleted" => "0",
						"status" => "0"
					)
				);
			}
		}else{
			if (astpp_gt($id, 0)) {
				if($account_info['type'] == 1 ){
					$account_drp = array(
						gettext('Account'),
						array(
							'name' => 'accountid',
							'id' => 'account_id',
						),
						'SELECT',
						'',
						'trim|xss_clean|required',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'first_name,last_name,number,company_name',
						'accounts',
						'build_concat_dropdown',
						'where_arr',
						array(
							"reseller_id" => $account_info['reseller_id'],
							"type" => "0",
							"status" => "0",
							"deleted" => "0"
						)
					);
				}
			}else{
				$account_drp = array(
					gettext('Account'),
					array(
						'name' => 'accountid',
						'id' => 'account_id',
					),
					'SELECT',
					'',
					'trim|xss_clean|required',
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'first_name,last_name,number,company_name',
					'accounts',
					'build_concat_dropdown',
					'where_arr',
					array(
						"reseller_id" => $account_info['reseller_id'],
						"type" => "0",
						"status" => "0",
						"deleted" => "0"
					)
				);
			}
		}
		$trunk = null;
		if ($logintype != 1){
			$routing_type =array(
				gettext('Routing Type')."#Enterprise#",
				array(
					'id'=>'routing_type',
					'name'=>'routing_type',
					'class'=>'routing_type',
				), 
				'SELECT',
				'', 
				'', 
				'tOOL TIP',
				'Please Select Status',
				'',
				'', 
				'', 
				'set_routetype_origination'
			);
		}else{
			$routing_type=null;
		}     
		$form ['forms'] = array (
			base_url () . 'personalized_rates/personalized_rates_save/',
			array (
				'id' => 'custom_rate_form',
				'method' => 'POST',
				'name' => 'custom_rate_form' 
			) 
		);
		$form [gettext ( 'Rate Information' )] = array (
			array (
				'',
				'HIDDEN',
				array (
					'name' => 'id' 
				),
				'',
				'',
				'',
				'' 
			),

			$reseller_drp,
			$account_drp,

			array (
				gettext ( 'Code' ),
				'INPUT',
				array (
					'name' => 'pattern',
					'size' => '20',
					'class' => "text field medium" 
				),
				'trim|required|numeric|xss_clean',
				'tOOL TIP',
				'' 
			),
			array (
				gettext ( 'Destination' ),
				'INPUT',
				array (
					'name' => 'comment',
					'size' => '20',
					'class' => "text field medium" 
				),
				'tOOL TIP',
				'' 
			),
			array (
				gettext ( 'Country' ),
				"country_id",
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
				gettext ( 'Call Type' ),
				'call_type',
				'SELECT',
				'',
				'',
				'tOOL TIP',
				'Please Enter account number',
				'id',
				'call_type',
				'calltype',
				'build_dropdown',
				'where_arr',
				array(
					"status" => "0"
				)
			),
			$routing_type
		)
		;
		$form [gettext ( 'Billing Information' )] = array (
			array (
				gettext ( 'Connection Cost' ).' ('.$currency.')',
				'INPUT',
				array (
					'name' => 'connectcost',
					'size' => '20',
					'class' => "text field medium" 
				),
				'trim|numeric|currency_decimal|(decimal|integer)|xss_clean',
				'tOOL TIP',
				'' 
			),
			array (
				gettext ( 'Grace Time' ),
				'INPUT',
				array (
					'name' => 'includedseconds',
					'size' => '20',
					'class' => "text field medium" 
				),
				'trim|numeric|xss_clean',
				'tOOL TIP',
				'' 
			),
			array (
				gettext ( 'Cost / Min' ).' ('.$currency.')',
				'INPUT',
				array (
					'name' => 'cost',
					'size' => '20',
					'class' => "text field medium" 
				),
				'trim|numeric|currency_decimal|(decimal|integer)|xss_clean',
				'tOOL TIP',
				'' 
			),
				/**
				 * ASTPP 3.0
				 * For Add Initial Increment field
				 * *
				 */
				array (
					gettext ( 'Initial Increment' ),
					'INPUT',
					array (
						'name' => 'init_inc',
						'size' => '20',
						'class' => "text field medium" 
					),
					'trim|numeric|greater_than[-1]|integer|xss_clean',
					'tOOL TIP',
					'' 
				),
				/**
				 * *****************************************************************
				 */
				array (
					gettext ( 'Increment' ),
					'INPUT',
					array (
						'name' => 'inc',
						'size' => '20',
						'class' => "text field medium" 
					),
					'trim|numeric|greater_than[-1]|integer|xss_clean',
					'tOOL TIP',
					'' 
				),
				$trunk,
			);
		$logintype=$this->CI->session->userdata('userlevel_logintype');
		if($logintype != 1){
			for($i = 1; $i <= Common_model::$global_config['system_config']['trunk_count']; $i++){
				$form['Rate Information'][] = array(gettext('Force Trunk').$i, array('id'=>'trunk_id_'.$i,'name'=>'trunk_id_new['.$i.']',"class"=>'trunk_id_'.$i), 'SELECT', '', '', 'tOOL TIP', 'Please select gateway first', 'id', 'name', 'trunks', 'build_dropdown_origination_rate', 'where_arr', array('status' => '0'));

				$form['Rate Information'][] = array('', 'INPUT', array('id'=>'trunk_percentage_'.$i,'name' => 'percentage['.$i.']', 'size' => '20', 'maxlength' => '3','value'=>'0',"class"=>'trunk_percentage_'.$i), 'trim|xss_clean', 'tOOL TIP', 'Please Enter account number');
			}                
		}
		
		$form['Rate Information'][]=array (
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
			'set_status' 
		);
		
		$form ['button_cancel'] = array (
			'name' => 'action',
			'content' => gettext ( 'Close' ),
			'value' => 'cancel',
			'type' => 'button',
			'class' => 'btn btn-secondary ml-2',
			'onclick' => 'return redirect_page(\'NULL\')' 
		);
		$form ['button_save'] = array (
			'name' => 'action',
			'content' => gettext ( 'Save' ),
			'value' => 'save',
			'id' => 'submit',
			'type' => 'button',
			'class' => 'btn btn-success' 
		);
		
		return $form;
	}

	
	function custom_rate_batch_update_form() {
		$logintype = $this->CI->session->userdata ( 'userlevel_logintype' );
		$trunk = null;
		if ($logintype != 1)
			$trunk = array (
				'Force Trunk',
				array (
					'name' => 'trunk_id[trunk_id]',
					'id' => 'trunk_id',
					'class'=>'trunk_id' 
				),
				'SELECT',
				'',
				'',
				'tOOL TIP',
				'Please Enter account number',
				'id',
				'name',
				'trunks',
				'build_dropdown',
				'where_arr',
				array (
					"status" => "0" 
				),
				array (
					'name' => 'trunk_id[operator]',
					'class' => 'update_drp' 
				),
				'update_drp_type' 
			);
		$form ['forms'] = array (
			"personalized_rates/personalized_rates_batch_update/",
			array (
				'id' => "custom_rate_batch_update" 
			) 
		);
		$form [gettext ( 'Batch Update' )] = array (
			array (
				gettext ( 'Connection Cost' ),
				'INPUT',
				array (
					'name' => 'connectcost[connectcost]',
					'id' => 'connectcost',
					'value' => '',
					'size' => '20',
					'class' => "text field" 
				),
				'',
				'Tool tips info',
				'1',
				array (
					'name' => 'connectcost[operator]',
					'class' => 'update_drp' 
				),
				'',
				'',
				'',
				'update_int_type',
				'' 
			),
			array (
				gettext ( 'Grace Time' ),
				'INPUT',
				array (
					'name' => 'includedseconds[includedseconds]',
					'id' => 'includedseconds',
					'value' => '',
					'size' => '20',
					'class' => "text field" 
				),
				'',
				'Tool tips info',
				'1',
				array (
					'name' => 'includedseconds[operator]',
					'class' => 'update_drp' 
				),
				'',
				'',
				'',
				'update_int_type',
				'' 
			),
			array (
				gettext ( 'Cost / Min' ),
				'INPUT',
				array (
					'name' => 'cost[cost]',
					'id' => 'cost',
					'value' => '',
					'size' => '20',
					'class' => "text field" 
				),
				'',
				'Tool tips info',
				'1',
				array (
					'name' => 'cost[operator]',
					'class' => 'update_drp' 
				),
				'',
				'',
				'',
				'update_int_type',
				'' 
			),
			array (
				gettext ( 'Increment' ),
				'INPUT',
				array (
					'name' => 'inc[inc]',
					'id' => 'inc',
					'size' => '20',
					'class' => "text field" 
				),
				'',
				'tOOL TIP',
				'1',
				array (
					'name' => 'inc[operator]',
					'class' => 'update_drp' 
				),
				'',
				'',
				'',
				'update_int_type',
				'' 
			),

			$trunk 
		);
		
		$form ['button_search'] = array (
			'name' => 'action',
			'id' => "batch_update",
			'content' => gettext ( 'Update' ),
			'value' => 'save',
			'type' => 'button',
			'class' => 'btn btn-success float-right' 
		);
		$form ['button_reset'] = array (
			'name' => 'action',
			'id' => "id_batch_reset",
			'content' => gettext ( 'Clear' ),
			'value' => 'cancel',
			'type' => 'reset',
			'class' => 'btn btn-secondary float-right ml-2' 
		);
		
		return $form;
	}
	function build_rates_list_for_reseller() {
		$account_info = $accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$currency_id = $account_info ['currency_id'];
		$currency = $this->CI->common->get_field_name ( 'currency', 'currency', $currency_id );
		
		$grid_field_arr = json_encode ( array (
			array (
				gettext ( 'Code' ),
				"130",
				"pattern",
				"pattern",
				"",
				"get_only_numeric_val",
				"",
				"true",
				"left" 
			),
			array (
				gettext ( 'Destination' ),
				"200",
				"comment",
				"",
				"",
				"" ,
				"",
				"true",
				"center" 
			),
			array (
				gettext ( 'Connection Cost' ).'(' . $currency . ')',
				"210",
				"connectcost",
				"connectcost",
				"connectcost",
				"convert_to_currency",
				"",
				"true",
				"right" 
			),
			array (
				gettext ( 'Grace Time' ),
				"180",
				"includedseconds",
				"",
				"",
				"",
				"",
				"true",
				"center" 
			),
			array (
				gettext ( 'Cost/Min' ).' (' . $currency . ')',
				"180",
				"cost",
				"cost",
				"cost",
				"convert_to_currency",
				"",
				"true",
				"right" 
			),
			array (
				gettext ( 'Increment' ),
				"140",
				"inc",
				"",
				"",
				"",
				"",
				"true",
				"center" 
			),
			array (
				gettext ( 'Priority' ),
				"150",
				"precedence",
				"",
				"",
				"",
				"",
				"true",
				"center" 
			) 
		) );
		return $grid_field_arr;
	}
	function get_reseller_custom_rate_search_form() {
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		
		$form ['forms'] = array (
			"",
			array (
				'id' => "resellerrates_list_search" 
			) 
		);
		$form [gettext ( 'Search My Rates' )] = array (

			array (
				gettext ( 'Code' ),
				'INPUT',
				array (
					'name' => 'pattern[pattern]',
					'',
					'size' => '20',
					'class' => "text field " 
				),
				'',
				'tOOL TIP',
				'1',
				'pattern[pattern-string]',
				'',
				'',
				'',
				'search_string_type',
				'' 
			),
			array (
				gettext ( 'Destination' ),
				'INPUT',
				array (
					'name' => 'comment[comment]',
					'',
					'size' => '20',
					'class' => "text field " 
				),
				'',
				'tOOL TIP',
				'1',
				'comment[comment-string]',
				'',
				'',
				'',
				'search_string_type',
				'' 
			),
			array (
				gettext ( 'Connection Cost' ),
				'INPUT',
				array (
					'name' => 'connectcost[connectcost]',
					'value' => '',
					'size' => '20',
					'class' => "text field " 
				),
				'',
				'Tool tips info',
				'1',
				'connectcost[connectcost-integer]',
				'',
				'',
				'',
				'search_int_type',
				'' 
			),
			array (
				gettext ( 'Grace Time' ),
				'INPUT',
				array (
					'name' => 'includedseconds[includedseconds]',
					'value' => '',
					'size' => '20',
					'class' => "text field " 
				),
				'',
				'Tool tips info',
				'1',
				'includedseconds[includedseconds-integer]',
				'',
				'',
				'',
				'search_int_type',
				'' 
			),
			array (
				gettext ( 'Cost/Min' ),
				'INPUT',
				array (
					'name' => 'cost[cost]',
					'value' => '',
					'size' => '20',
					'class' => "text field " 
				),
				'',
				'Tool tips info',
				'1',
				'cost[cost-integer]',
				'',
				'',
				'',
				'search_int_type',
				'' 
			),
			array (
				gettext ( 'Increment' ),
				'INPUT',
				array (
					'name' => 'inc[inc]',
					'',
					'size' => '20',
					'class' => "text field " 
				),
				'',
				'tOOL TIP',
				'1',
				'inc[inc-integer]',
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
		
		$form ['button_search'] = array (
			'name' => 'action',
			'id' => "resellerrates_list_search_btn",
			'content' => gettext ( 'Search' ),
			'value' => 'save',
			'type' => 'button',
			'class' => 'btn btn-success float-right' 
		);
		$form ['button_reset'] = array (
			'name' => 'action',
			'id' => "id_reset",
			'content' => 'Clear',
			'value' => gettext ( 'cancel' ),
			'type' => 'reset',
			'class' => 'btn btn-secondary float-right ml-2' 
		);
		
		return $form;
	}
	function get_custom_rate_search_form() {
		$login_type=$this->CI->session->userdata ( 'userlevel_logintype' );
		$accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		if($login_type==1){
			$reseller_id=$accountinfo['id'];	
		}
		if($login_type==-1 || $login_type==2){
			$form ['forms'] = array (
				"",
				array (
					'id' => "custom_rate_list_search" 
				) 
			);
			$form [gettext ( 'Search' )] = array (
				array (
					gettext ( 'Code' ),
					'INPUT',
					array (
						'name' => 'pattern[pattern]',
						'',
						'size' => '20',
						'class' => "text field " 
					),
					'',
					'tOOL TIP',
					'1',
					'pattern[pattern-string]',
					'',
					'',
					'',
					'search_string_type',
					'' 
				),
				array (
					gettext ( 'Destination' ),
					'INPUT',
					array (
						'name' => 'comment[comment]',
						'',
						'size' => '20',
						'class' => "text field " 
					),
					'',
					'tOOL TIP',
					'1',
					'comment[comment-string]',
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
					'Please Enter country number',
					'id',
					'country',
					'countrycode',
					'build_dropdown_country_camel',
					'',
					'' 
				),
				array (
					gettext ( 'Connection Cost' ),
					'INPUT',
					array (
						'name' => 'connectcost[connectcost]',
						'value' => '',
						'size' => '20',
						'class' => "text field " 
					),
					'',
					'Tool tips info',
					'1',
					'connectcost[connectcost-integer]',
					'',
					'',
					'',
					'search_int_type',
					'' 
				),
				array (
					gettext ( 'Grace Time' ),
					'INPUT',
					array (
						'name' => 'includedseconds[includedseconds]',
						'value' => '',
						'size' => '20',
						'class' => "text field " 
					),
					'',
					'Tool tips info',
					'1',
					'includedseconds[includedseconds-integer]',
					'',
					'',
					'',
					'search_int_type',
					'' 
				),
				array (
					gettext ( 'Cost / Min' ),
					'INPUT',
					array (
						'name' => 'cost[cost]',
						'value' => '',
						'size' => '20',
						'class' => "text field " 
					),
					'',
					'Tool tips info',
					'1',
					'cost[cost-integer]',
					'',
					'',
					'',
					'search_int_type',
					'' 
				),
					/**
					 * ASTPP 3.0
					 * For Add Initial Increment field
					 * *
					 */
					array (
						gettext ( 'Initial Increment' ),
						'INPUT',
						array (
							'name' => 'init_inc[init_inc]',
							'',
							'size' => '20',
							'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'init_inc[init_inc-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
					),
					/**
					 * ***********************************************************
					 */
					array (
						gettext ( 'Increment' ),
						'INPUT',
						array (
							'name' => 'inc[inc]',
							'',
							'size' => '20',
							'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'inc[inc-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
					),
					array(
						gettext ( 'Reseller'), 
						array (
							'name' => 'reseller_id',
							'class' => 'reseller_id_search_drp',
							'id' => 'reseller1' 
						),
						'SELECT',
						'',
						'', 
						'tOOL TIP',
						'Please Enter account number', 
						'id', 
						'first_name,last_name,number,company_name',
						'accounts',
						'build_concat_dropdown_reseller',
						'',
						''
					),
					array (
						gettext ( 'Accounts' ),
						array(
							'name'=>'accountid',
							'id'=>'accountid2'
						),
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'first_name,last_name,number,company_name',
						'accounts',
						'build_concat_dropdown',
						'where_arr',
						array (
							"status" => "0",
							"type" => "0,3"
						) 
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
}else{

	$form ['forms'] = array (
		"",
		array (
			'id' => "custom_rate_list_search" 
		) 
	);
	$form [gettext ( 'Search' )] = array (
		array (
			gettext ( 'Code' ),
			'INPUT',
			array (
				'name' => 'pattern[pattern]',
				'',
				'size' => '20',
				'class' => "text field " 
			),
			'',
			'tOOL TIP',
			'1',
			'pattern[pattern-string]',
			'',
			'',
			'',
			'search_string_type',
			'' 
		),
		array (
			gettext ( 'Destination' ),
			'INPUT',
			array (
				'name' => 'comment[comment]',
				'',
				'size' => '20',
				'class' => "text field " 
			),
			'',
			'tOOL TIP',
			'1',
			'comment[comment-string]',
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
			'Please Enter country number',
			'id',
			'country',
			'countrycode',
			'build_dropdown_country_camel',
			'',
			'' 
		),
		array (
			gettext ( 'Connection Cost' ),
			'INPUT',
			array (
				'name' => 'connectcost[connectcost]',
				'value' => '',
				'size' => '20',
				'class' => "text field " 
			),
			'',
			'Tool tips info',
			'1',
			'connectcost[connectcost-integer]',
			'',
			'',
			'',
			'search_int_type',
			'' 
		),
		array (
			gettext ( 'Grace Time' ),
			'INPUT',
			array (
				'name' => 'includedseconds[includedseconds]',
				'value' => '',
				'size' => '20',
				'class' => "text field " 
			),
			'',
			'Tool tips info',
			'1',
			'includedseconds[includedseconds-integer]',
			'',
			'',
			'',
			'search_int_type',
			'' 
		),
		array (
			gettext ( 'Cost / Min' ),
			'INPUT',
			array (
				'name' => 'cost[cost]',
				'value' => '',
				'size' => '20',
				'class' => "text field " 
			),
			'',
			'Tool tips info',
			'1',
			'cost[cost-integer]',
			'',
			'',
			'',
			'search_int_type',
			'' 
		),
					/**
					 * ASTPP 3.0
					 * For Add Initial Increment field
					 * *
					 */
					array (
						gettext ( 'Initial Increment' ),
						'INPUT',
						array (
							'name' => 'init_inc[init_inc]',
							'',
							'size' => '20',
							'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'init_inc[init_inc-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
					),
					/**
					 * ***********************************************************
					 */
					array (
						gettext ( 'Increment' ),
						'INPUT',
						array (
							'name' => 'inc[inc]',
							'',
							'size' => '20',
							'class' => "text field " 
						),
						'',
						'tOOL TIP',
						'1',
						'inc[inc-integer]',
						'',
						'',
						'',
						'search_int_type',
						'' 
					),
					array (
						gettext ( 'Accounts' ),
						'accountid',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Enter account number',
						'id',
						'first_name,last_name,number,company_name',
						'accounts',
						'build_concat_dropdown',
						'where_arr',
						array (
							"status" => "0",
							"type" => "0,3"
						) 
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
		/**
		 * ****
		 * ASTPP 3.0
		 * Batch Delete
		 * *****
		 */
		$form ['button_search_delete'] = array (
			'name' => 'action',
			'id' => "custom_rate_batch_dlt",
			'onclick' => "check_btn();",
			'content' => gettext ( 'Delete Search Record' ),
			'style' => 'display:none;',
			'value' => 'save',
			'type' => 'button',
			'class' => 'btn float-right btn btn-line-danger ' 
		);
		
		/**
		 * ********************
		 */
		/**
		 * ******
		 * ASTPP 3.0
		 * Batch delete
		 * ********
		 */
		$form ['button_search'] = array (
			'name' => 'action',
			'id' => "custom_rate_list_search_btn",
			'onclick' => 'search_btn();',
			'content' => gettext ( 'Search' ),
			'value' => 'save',
			'type' => 'button',
			'class' => 'btn btn-success float-right  ml-2' 
		);
		/**
		 * ***********************
		 */
		$form ['button_reset'] = array (
			'name' => 'action',
			'id' => "id_reset",
			'content' => gettext ( 'Clear' ),
			'value' => 'cancel',
			'type' => 'reset',
			'class' => 'btn btn-secondary float-right ml-2' 
		);
		
		return $form;
	}
	
	/*
	 * ASTPP 3.0
	 * changes in grid size
	 */
	
	
	/*
	 * ASTPP 3.0 
	 * changes in grid size
	 */
	function build_custom_rate_list_for_admin() {
		$account_info = $accountinfo = $this->CI->session->userdata ( 'accountinfo' );
		$currency_id  = $account_info ['currency_id'];
		$currency     = $this->CI->common->get_field_name ( 'currency', 'currency', $currency_id );
		$login_type=$this->CI->session->userdata ( 'userlevel_logintype' );
		if($login_type==-1 || $login_type==2){
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
					/**
					 * ASTPP 3.0
					 * For Origination rate edit on code
					 * *
					 */
					array (
						gettext ( "Code" ),
						"75",
						"pattern",
						"pattern",
						"",
						"get_only_numeric_val",
						"EDITABLE",
						"true",
						"left" 
					),
					/**
					 * *********************************
					 */
					array (
						gettext ( "Destination" ),
						"100",
						"comment",
						"",
						"",
						"",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Country" ),
						"120",
						"country_id",
						"country",
						"countrycode",
						"get_field_name_country_camel",
						"",
						"true",
						"center" 
					),

					array (
						gettext ( "Connection Cost" )."<br>  ($currency)",
						"110",
						"connectcost",
						"connectcost",
						"connectcost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
					),
					array (
						gettext ( "Grace Time" ),
						"100",
						"includedseconds",
						"",
						"",
						"",
						"",
						"true",
						"right" 
					),
					array (
						gettext ( "Cost / Min" )." <br> ($currency)",
						"100",
						"cost",
						"cost",
						"cost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
					),
					array (
						gettext ( "Initial Increment" ),
						"110",
						"init_inc",
						"",
						"",
						"",
						"",
						"true",
						"right" 
					),
					/**
					 * ****************************************************************
					 */
					array (
						gettext ( "Increment" ),
						"80",
						"inc",
						"",
						"",
						"",
						"",
						"true",
						"right" 
					),
					array (
						gettext ( "Accounts" ),
						"105",
						"accountid",
						"first_name,last_name,number,company_name",
						"accounts",
						"get_field_name_coma_new",
						"",
						"true",
						"center" 
					),
					array(
						"Reseller",
						"130", 
						"reseller_id", 
						"first_name,last_name,number,company_name", 
						"accounts", 
						"reseller_select_value"
					),
					array (
						gettext ( "Created Date" ),
						"100",
						"creation_date",
						"creation_date",
						"creation_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Modified Date" ),
						"100",
						"last_modified_date",
						"last_modified_date",
						"last_modified_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Status" ),
						"80",
						"status",
						"status",
						"routes",
						"get_status",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Action" ),
						"95",
						"",
						"",
						"",
						array (
							"EDIT" => array (
								"url" => "personalized_rates/personalized_rates_edit/",
								"mode" => "popup",
								"layout" => "medium" 
							),
							"DELETE" => array (
								"url" => "personalized_rates/personalized_rates_delete/",
								"mode" => "single" 
							) 
						) ,
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
					/**
					 * ASTPP 3.0
					 * For Origination rate edit on code
					 * *
					 */
					array (
						gettext ( "Code" ),
						"75",
						"pattern",
						"pattern",
						"",
						"get_only_numeric_val",
						"EDITABLE",
						"true",
						"left" 
					),
					/**
					 * *********************************
					 */
					array (
						gettext ( "Destination" ),
						"100",
						"comment",
						"",
						"",
						"",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Country" ),
						"120",
						"country_id",
						"country",
						"countrycode",
						"get_field_name_country_camel",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Connection Cost" )."<br>  ($currency)",
						"110", 
						"connectcost",
						"connectcost",
						"connectcost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
					),
					array (
						gettext ( "Grace Time" ),
						"100",
						"includedseconds",
						"",
						"",
						"",
						"",
						"true",
						"right" 
					),
					array (
						gettext ( "Cost / Min" )." <br> ($currency)",
						"100",
						"cost",
						"cost",
						"cost",
						"convert_to_currency_account",
						"",
						"true",
						"right" 
					),
					array (
						gettext ( "Initial Increment" ),
						"110", 
						"init_inc",
						"",
						"",
						"",
						"",
						"true",
						"right" 
					),
					/**
					 * ****************************************************************
					 */
					array (
						gettext ( "Increment" ),
						"80",
						"inc",
						"",
						"",
						"",
						"",
						"true",
						"right" 
					),
					array (
						gettext ( "Accounts" ),
						"105",
						"accountid",
						"first_name,last_name,number,company_name",
						"accounts",
						"get_field_name_coma_new",
						"",
						"true",
						"center"  
					),
					array (
						gettext ( "Created Date" ),
						"100",
						"creation_date",
						"creation_date",
						"creation_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Modified Date" ),
						"100",
						"last_modified_date",
						"last_modified_date",
						"last_modified_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Status" ),
						"80",
						"status",
						"status",
						"routes",
						"get_status",
						"",
						"true",
						"center" 
					),
					array (
						gettext ( "Action" ),
						"95",
						"",
						"",
						"",
						array (
							"EDIT" => array (
								"url" => "personalized_rates/personalized_rates_edit/",
								"mode" => "popup",
								"layout" => "medium" 
							),
							"DELETE" => array (
								"url" => "personalized_rates/personalized_rates_delete/",
								"mode" => "single" 
							) 
						) ,
						"false"
					) 
				) );	
			
		}
		return $grid_field_arr;
	}
	/**
	 * *************************************************************************************
	 */
	
	function build_grid_buttons_custom_rate() {
		$buttons_json = json_encode ( array (
			array (
				gettext ( "Create" ),
				"btn btn-line-warning btn",
				"fa fa-plus-circle fa-lg",
				"button_action",
				"/personalized_rates/personalized_rates_add/",
				"popup",
				"medium",
				"create"
			),
			array (
				gettext ( "Delete" ),
				"btn btn-line-danger",
				"fa fa-times-circle fa-lg",
				"button_action",
				"/personalized_rates/personalized_rates_delete_multiple/",
				"",
				"",
				"delete"
			),
			array (
				gettext ( "Export" ),
				"btn btn-xing",
				"fa fa-upload fa-lg",
				"button_action",
				"/personalized_rates/personalized_rates_export_cdr_xls/",
				'single',
				"",
				"export"
			) 
		)
	);
		return $buttons_json;
	}
	function build_block_pattern_list_for_customer() {
		$grid_field_arr = json_encode ( array (
			array (
				"<input type='checkbox' name='chkAll1' class='ace checking'/><label class='lbl'></label>",
				"30",
				"",
				"",
				"",
				"",
				"",
				"false",
				"center",
				"PatternChkBox" 
			),
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
				"150",
				"comment",
				"",
				"",
				"" 
			) 
		) );
		return $grid_field_arr;
	}
	function build_pattern_list_for_customer($accountid, $accounttype) {
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
				"blocked_patterns",
				"blocked_patterns",
				"",
				"get_only_numeric_val",
				"",
				"true",
				"left" 
			),
			array (
				gettext ( "Destination" ),
				"150",
				"destination",
				"",
				"",
				"" 
			),
			array (
				gettext ( "Action" ),
				"100",
				"",
				"",
				"",
				array (
					"DELETE" => array (
						"url" => "accounts/" . $accounttype . "_delete_block_pattern/$accountid/",
						"mode" => "single" 
					) 
				), 
				"false"
			) 
		) );
		return $grid_field_arr;
	}
	function set_pattern_grid_buttons($accountid) {
		$buttons_json = json_encode ( array (
			array (
				gettext ( "Add Prefixes" ),
				"btn btn-line-warning btn",
				"fa fa-plus-circle fa-lg",
				"button_action",
				"/accounts/customer_add_blockpatterns/$accountid",
				"popup" 
			) 
		) );
		return $buttons_json;
	}
	function build_custom_rate_list_for_user() {
		$grid_field_arr = json_encode ( array (
			array (
				gettext ( "Code" ),
				"155",
				"pattern",
				"pattern",
				"",
				"get_only_numeric_val" 
			),
			array (
				gettext ( "Destination" ),
				"225",
				"comment",
				"",
				"",
				"" 
			),
			array (
				gettext ( "Increment" ),
				"235",
				"inc",
				"",
				"",
				"" 
			),
			array (
				gettext ( "Cost per Minutes" ),
				"240",
				"cost",
				"cost",
				"cost",
				"convert_to_currency" 
			),
			array (
				gettext ( "Connect Charge" ),
				"200",
				"connectcost",
				"connectcost",
				"connectcost",
				"convert_to_currency" 
			),
			array (
				gettext ( "Included Seconds" ),
				"200",
				"includedseconds",
				"",
				"",
				"" 
			) 
		) );
		return $grid_field_arr;
	}
	function build_grid_buttons_rates() {
		$buttons_json = json_encode ( array (
			array (
				gettext ( "Export" ),
				"btn btn-xing",
				" fa fa-download fa-lg",
				"button_action",
				"/personalized_rates/resellersrates_xls/",
				'single',
				"",
				"export" 
			) 
		) );
		return $buttons_json;
	}
}
?>
