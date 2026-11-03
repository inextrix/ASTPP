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
class local_number_form extends common {
	function get_local_number_form_fields($id=false) {
		$form ['forms'] = array (
				base_url () . 'local_number/local_number_save/',
				array (
						'id' => 'local_number_form',
						'method' => 'POST',
						'name' => 'local_number_form' 
				) 
		);
		$val = astpp_gt($id, 0) ? 'local_number.number.' . $id : 'local_number.number';
		$form [gettext ( 'Information' )] = array (
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
				array (
						gettext ( 'Number' ),
						'INPUT',
						array (
								'name' => 'number',
								'size' => '20',
								'class' => "text field medium" 
						),
						'trim|required|is_numeric|xss_clean|is_unique[' . $val . ']',
						'tOOL TIP',
						''
				),
				array (
					'Country',
					array (
							'name' => 'country_id',
							'class' => 'country_id' 
					),
					'SELECT',
					'',
					array (
							"name" => "country_id",
							"rules" => "required" 
					),
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'country',
					'countrycode',
					'build_dropdown',
					'',
					'' 
				),
				array (
						gettext ( 'Province/State' ),
						'INPUT',
						array (
								'name' => 'province',
								'size' => '20',
								'class' => "text field medium" 
						),
						'trim|required',
						'tOOL TIP',
						'' 
				),
				array (
						gettext ( 'city' ),
						'INPUT',
						array (
								'name' => 'city',
								'size' => '20',
								'class' => "text field medium" 
						),
						'trim|required',
						'tOOL TIP',
						'' 
				),
				array (
						gettext ( 'Status' ),
						'status',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Select Status',
						'',
						'',
						'',
						'set_status' 
				)
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

	function get_local_number_customer_form_fields($id=false) {
		$form ['forms'] = array (
				base_url () . 'local_number/local_number_save/',
				array (
						'id' => 'local_number_form',
						'method' => 'POST',
						'name' => 'local_number_form' 
				) 
		);
		$val = astpp_gt($id, 0) ? 'local_number_destination.destination_number.' . $id : 'local_number_destination.destination_number';
		$form [gettext ( 'Information' )] = array (
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
				array (
						gettext ( 'Number' ),
						'INPUT',
						array (
								'name' => 'number',
								'size' => '20',
								'class' => "text field medium" 
						),
						'trim|required|is_numeric|xss_clean|is_unique[' . $val . ']',
						'tOOL TIP',
						'' 
				),

				array (
						gettext ( 'Province/State' ),
						'INPUT',
						array (
								'name' => 'province',
								'size' => '20',
								'class' => "text field medium" 
						),
						'trim|required',
						'tOOL TIP',
						'' 
				),
				array (
						gettext ( 'city' ),
						'INPUT',
						array (
								'name' => 'city',
								'size' => '20',
								'class' => "text field medium" 
						),
						'trim|required',
						'tOOL TIP',
						'' 
				)
			
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
	function get_local_number_search_form() {
		$form ['forms'] = array (
				"",
				array (
						'id' => "local_number_list_search" 
				) 
		);
		$form [gettext ( 'Search' )] = array (
				array (
						gettext ( 'Number' ),
						'INPUT',
						array (
								'name' => 'number[number]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'number[number-string]',
						'',
						'',
						'',
						'search_string_type',
						'' 
				),
				// array (
				// 		gettext ( 'Country' ),
				// 		'country_id',
				// 		'SELECT',
				// 		'',
				// 		'',
				// 		'tOOL TIP',
				// 		'Please Enter account number',
				// 		'id',
				// 		'country',
				// 		'countrycode',
				// 		'build_dropdown',
				// 		'where_arr',""
				// ),
				/*	array (
						gettext ( 'Country' ),
						'country_id',
						'SELECT',
						'',
						'',
						'tOOL TIP',
						'Please Select Status',
						'',
						'',
						'',
						'set_country' 
				),*/
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
						'build_dropdown',
						'',
						'' 
				),
				array (
						gettext ( 'Province/State' ),
						'INPUT',
						array (
								'name' => 'province[province]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'province[province-string]',
						'',
						'',
						'',
						'search_string_type',
						'' 
				),
				array (
						gettext ( 'City' ),
						'INPUT',
						array (
								'name' => 'city[city]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'city[city-string]',
						'',
						'',
						'',
						'search_string_type',
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
		$form ['button_search'] = array (
				'name' => 'action',
				'id' => "local_number_search_btn",
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
				'class' => 'btn btn-secondary float-right ml-2' 
		);
		return $form;
	}
	function build_local_number_list_for_admin() {
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
						gettext ( "Number" ),
						"100",
						"number",
						"",
						"",
						"",
						"EDITABLE",
						"true",
						"left" 
				),
				array (
						gettext ( "Country" ),
						"100",
						"country_id",
						"country",
						"countrycode",
						"get_field_name",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Province/State" ),
						"150",
						"province",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "City" ),
						"100",
						"city",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Created Date" ),
						"130",
						"created_date",
						"created_date",
						"created_date",
						"convert_GMT_to",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Modified Date" ),
						"130",
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
						"40",
						"status",
						"status",
						"local_number",
						"get_status",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Action" ),
						"150",
						"",
						"",
						"",
						array (
								"EDIT" => array (
										"url" => "local_number/local_number_edit/",
										"mode" => "popup",
										"layout" => "small" 
								),
								"DELETE" => array (
										"url" => "local_number/local_number_remove/",
										"mode" => "single" 
								)
						),
						"false"
				)
		) );
		return $grid_field_arr;
	}
	function build_grid_buttons() { 
		$buttons_json = json_encode ( array (
				array (
						("Create"),
						"btn btn-line-warning btn",
						"fa fa-plus-circle fa-lg",
						"button_action",
						"/local_number/local_number_add/",
						"popup",
						"small",
						"create" 
				),
				array (
						gettext ( "Delete" ),
						"btn btn-line-danger",
						"fa fa-times-circle fa-lg",
						"button_action",
						"/local_number/local_number_delete_multiple/",
						"",
						"",
						"delete" 
				),
				array (
						gettext ( "Import" ),
						"btn btn-line-blue",
						"fa fa-upload fa-lg",
						"button_action",
						"/local_number/local_number_import/",
						'',
						"small",
						"import"
				),
				array (
						gettext ( "Export" ),
						"btn btn-xing",
						"fa fa-download fa-lg",
						"button_action",
						"/local_number/local_number_export_data_xls",
						'single',
						"",
						"export" 
				)  
		) );
		return $buttons_json;
	}


	function build_grid_customer_buttons() {
		$buttons_json = json_encode ( array (
				array (
						("Create"),
						"btn btn-line-warning btn",
						"fa fa-plus-circle fa-lg",
						"button_action",
						"/local_number/local_number_customer_add/",
						"popup",
						"small" 
				),
				array (
						gettext ( "Delete" ),
						"btn btn-line-danger",
						"fa fa-times-circle fa-lg",
						"button_action",
						"/local_number/local_number_delete_multiple/" 
				) 
		) );
		return $buttons_json;
	}



	function local_number_customerportal_button() {
		$buttons_json = json_encode ( array (
				array (
						gettext ( "Delete" ),
						"btn btn-line-danger",
						"fa fa-times-circle fa-lg",
						"button_action",
						"/local_number/local_number_delete_multiple_custoemr/",
						"",
						"",
						"delete"
				) 
		) );
		return $buttons_json;
	}
 

	








	function local_number_customer_grid($edit_id) {
		$grid_field_arr = json_encode ( array (
				array (
						gettext ( "Local Number" ),
						"160",
						"local_number_id",
						"number",
						"local_number",
						"get_field_name",
						"",
						"true",
						"center" 
				),

				array (
						'Destination_number',
						'INPUT',
						array (
								'name' => 'destination_number',
								'size' => '20',
								'class' => "text field medium" 
						),
						'',
						'tOOL TIP',
						'Please Enter destination_number' 
				),
				array (
						gettext ( "Destination Name" ),
						"180",
						"destination_name",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Destination Number" ),
						"180",
						"destination_number",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Created Date" ),
						"200",
						"creation_date",
						"creation_date",
						"creation_date",
						"convert_GMT_to" 
				),
				array (
						gettext ( "Action" ),
						"200",
						"",
						"",
						"",
						array (
								"DELETE" => array (
										"url" => "local_number/local_number_destination_remove/$edit_id/",
										"mode" => "single" 
								) 
						) 
				) 
		) );
		return $grid_field_arr;
	}



	function local_number_customerportal_grid($edit_id = '') {

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
						gettext ( "Local Number" ),
						"135",
						"local_number_id",
						"number",
						"local_number",
						"get_field_name",
						"EDITABLE",
						"true",
						"left" 
				),
				array (
						gettext ( "Destination Name" ),
						"180",
						"destination_name",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Destination Number" ),
						"180",
						"destination_number",
						"",
						"",
						"",
						"",
						"true",
						"center"
				),
				array (
						gettext ( "Country" ),
						"100",
						"country_id",
						"country",
						"countrycode",
						"get_field_name",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Province" ),
						"180",
						"province",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "City" ),
						"180",
						"city",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				
				array (
						gettext ( "Added Date" ),
						"200",
						"creation_date",
						"creation_date",
						"creation_date",
						"convert_GMT_to" 
				),
				
				array (
						gettext ( "Action" ),
						"150",
						"",
						"",
						"",
						array (
								// "EDIT" => array (
								// 		"url" => "local_number/local_number_customer_edit/",
								// 		"mode" => "popup",
								// 		"layout" => "small" 
								// ),
								"EDIT" => array (
										"url" => "local_number/local_number_destination_customer_edit/",
										"mode" => "popup",
										"layout" => "small" 
								),
								"DELETE" => array (
										"url" => "local_number/local_number_destination_customer_remove/$edit_id/",
										"mode" => "single" 
								) 
						),
						'false'
				)
		) );
		return $grid_field_arr;
	}



	function local_number_customerportalleftpanel_grid($edit_id) {

		$grid_field_arr = json_encode ( array (
				

				array (
						gettext ( "Local Number" ),
						"160",
						"local_number_id",
						"number",
						"local_number",
						"get_field_name",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Destination Name" ),
						"180",
						"destination_name",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Destination Number" ),
						"180",
						"destination_number",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Created Date" ),
						"200",
						"creation_date",
						"creation_date",
						"creation_date",
						"convert_GMT_to" 
				),
				
				array (
						gettext ( "Action" ),
						"150",
						"",
						"",
						"",
						array (
								// "EDIT" => array (
								// 		"url" => "local_number/local_number_customer_edit/",
								// 		"mode" => "popup",
								// 		"layout" => "small" 
								// ),
							"EDIT" => array (
										"url" => "local_number/local_number_destination_customer_edit/",
										"mode" => "popup",
										"layout" => "small" 
								),

								"DELETE" => array (
										"url" => "local_number/local_number_destination_remove/$edit_id/",
										"mode" => "single" 
								) 
						) 
				) 
		) );
		return $grid_field_arr;
	}



		function local_number_customerportalleftpanel_grid_admin($edit_id) {

		$grid_field_arr = json_encode ( array (
				

				array (
						gettext ( "Local Number" ),
						"160",
						"local_number_id",
						"number",
						"local_number",
						"get_field_name",
						"EDITABLE",
						"true",
						"left" 
				),
				array (
						gettext ( "Destination Name" ),
						"180",
						"destination_name",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Destination Number" ),
						"180",
						"destination_number",
						"",
						"",
						"",
						"",
						"true",
						"center" 
				),
				array (
						gettext ( "Created Date" ),
						"200",
						"creation_date",
						"creation_date",
						"creation_date",
						"convert_GMT_to" 
				),
				
				array (
						gettext ( "Action" ),
						"150",
						"",
						"",
						"",
						array (
								// "EDIT" => array (
								// 		"url" => "local_number/local_number_customer_edit/",
								// 		"mode" => "popup",
								// 		"layout" => "small" 
								// ),
							"EDIT" => array (
										"url" => "local_number/local_number_destination_customer_edit_admin/$edit_id/",
										"mode" => "popup",
										"layout" => "small" 
								),

								"DELETE" => array (
										"url" => "local_number/local_number_destination_remove/$edit_id/",
										"mode" => "single" 
								) 
						),
						"false" 
				) 
		) );
		return $grid_field_arr;
	}

	function get_local_number_customer_form_field($id='') {
		$form ['forms'] = array (
				base_url () . 'local_number/local_number_destination_customer_save/',
				array (
						'id' => 'local_number_form',
						'method' => 'POST',
						'name' => 'local_number_form' 
				) 
		);
		$val = astpp_gt($id, 0) ? 'local_number_destination.destination_number.' . $id : 'local_number_destination.destination_number';
		$form [gettext ( 'Information' )] = array (
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
				array (
						gettext ( 'Destination Name' ),
						'INPUT',
						array (
								'name' => 'destination_name',
								'size' => '50',
								'class' => "text field medium" 
						),
						'trim|required',
						'tOOL TIP',
						'' 
				),
				array (
						gettext ( 'Destination Number' ),
						'INPUT',
						array (
								'name' => 'destination_number',
								'size' => '50',
								'class' => "text field medium" 
						),
						'trim|required',
						'tOOL TIP',
						'' 
				)
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
	function get_local_number_customer_form_field_admin($id='') {
		$form ['forms'] = array (
				base_url () . 'local_number/local_number_destination_customer_save_admin/',
				array (
						'id' => 'local_number_form',
						'method' => 'POST',
						'name' => 'local_number_form' 
				) 
		);
		$val = astpp_gt($id, 0) ? 'local_number_destination.destination_number.' . $id : 'local_number_destination.destination_number';
		$form [gettext ( 'Information' )] = array (
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
				array (
						gettext ( 'Destination Name' ),
						'INPUT',
						array (
								'name' => 'destination_name',
								'size' => '50',
								'class' => "text field medium" 
						),
						'trim|required',
						'tOOL TIP',
						'' 
				),
				array (
						gettext ( 'Destination Number' ),
						'INPUT',
						array (
								'name' => 'destination_number',
								'size' => '50',
								'class' => "text field medium" 
						),
						'trim|required',
						'tOOL TIP',
						'' 
				)
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
	function get_local_number_customer_search_form() {
		$form ['forms'] = array (
				"",
				array (
						'id' => "local_number_list_search" 
				) 
		);
		$form [gettext ( 'Search' )] = array (
				array (
						gettext ( 'Local Number' ),
						'INPUT',
						array (
								'name' => 'number[number]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'number[number-string]',
						'',
						'',
						'',
						'search_int_type',
						'' 
				),
				array (
						gettext ( 'Destination Name' ),
						'INPUT',
						array (
								'name' => 'destination_name[destination_name]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'destination_name[destination_name-string]',
						'',
						'',
						'',
						'search_string_type',
						''
				),
				array (
						gettext ( 'Destination Number' ),
						'INPUT',
						array (
								'name' => 'destination_number[destination_number]',
								'',
								'size' => '20',
								'class' => "text field" 
						),
						'',
						'tOOL TIP',
						'1',
						'destination_number[destination_number-string]',
						'',
						'',
						'',
						'search_string_type',
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
				'id' => "local_number_search_btn",
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
				'class' => 'btn btn-secondary float-right ml-2' 
		);
		return $form;
	}
}
