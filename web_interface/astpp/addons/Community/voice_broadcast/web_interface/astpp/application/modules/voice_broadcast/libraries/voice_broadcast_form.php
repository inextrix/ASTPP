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
class voice_broadcast_form extends common {
	function get_voice_broadcast_form_fields($id=false) {
		$accountinfo = $this->CI->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
		$form ['forms'] = array (
			base_url () . 'voice_broadcast/voice_broadcast_save/',
			array (
				'id' => 'voice_broadcast_form',
				'method' => 'post',
				'name' => 'voice_broadcast_form',
				'enctype' => 'multipart/form-data'
			)
		);
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
					gettext ( 'Name' ),
					'INPUT',
					array (
						'name' => 'name',
						'size' => '20',
						'class' => "text field medium" 
					),
					'trim|required|xss_clean',
					'tOOL TIP',
					''
				),
				array (
					gettext('Reseller'),
					array (
						'id' => 'reseller_id_search_drp',
						'name' => 'reseller_id_search_drp',
						'class' => 'reseller_id_search_drp' 
					),
					'SELECT',
					'',
					array (
						"name" => "reseller_id",
						"rules" => "required" 
					),
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'first_name,last_name,number,company_name',
					'accounts',
					'build_concat_dropdown_reseller',
					'where_arr',
					array(
						'reseller_id' => $reseller_id,
						'deleted'=>0,
						'status'=>0,
					) 
				),
				array(
					gettext('Account'),
					array(
						'name' => 'accountid_search_drp',
						'class' => 'accountid_search_drp',
						'id' => 'accountid_search_drp'
					),
					'SELECT',
					'',
					'trim|dropdown|xss_clean',
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'first_name,last_name,number,company_name',
					'accounts',
					'build_concat_dropdown',
					'where_arr',
					array(
						'reseller_id' => $reseller_id,
						'deleted'=>0,
						'status'=>0,
						'type'=>0
					)
				),
				array(
					gettext('Sip Devices'),
					array(
						'name' => 'sip_device_id_search_drp',
						'class' => 'sip_device_id_search_drp',
						'id' => 'sip_device_id_search_drp'
					),
					'SELECT',
					'',
					'trim|dropdown|xss_clean',
					'tOOL TIP',
					'Please Enter account number',
					'id',
					'username',
					'sip_devices',
					'build_dropdown',
					'where_arr',
					array(
						'reseller_id' => $reseller_id,
						'status'=>0,
					)
				),
				array (
					gettext ( 'Broadcast' ),
					'INPUT',
					array (
						'type' => 'file',
						'name' => 'broadcast',
						'class' => 'custom-file-input fileupload',
						'id' => 'broadcast',
						'onchange' => 'uploadfile_broadcast()'
					),
					'trim',
					'tOOL TIP',
					''
				),
				array (
					gettext ( 'Destination Number' ),
					'INPUT',
					array (
						'type' => 'file',
						'name' => 'destination_number',
						'class' => "custom-file-input fileupload",
						'id' => 'destination_number',
						'onchange' => 'uploadfile_destination()'
					),
					'trim',
					'tOOL TIP',
					''
				),
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
				'type' => 'submit',
				'class' => 'btn btn-success' 
		);
		return $form;
	}

	function get_voice_broadcast_search_form() {
		$accountinfo = $this->CI->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
		$form ['forms'] = array (
				"",
				array (
					'id' => "voice_broadcast_list_search" 
				)
		);
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
			array(
				gettext('Reseller'),
				array(
					'name' => 'reseller_id',
					'id' => 'reseller_id',
					'class' => 'reseller_id'
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
				'where_arr',
				array(
					"reseller_id" => $reseller_id,
					"deleted" => '0',
					"status" => '0'
				)
			),
			array(
				gettext('Account'),
				array(
					'name' => 'accountid',
					'id' => 'accountid',
					'class' => 'accountid'
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
				array(
					"reseller_id" => $reseller_id,
					"deleted" => "0",
					'status' => '0',
					'type' => '0',
				)
			),
			array(
                gettext('SIP Device'),
                array(
                    'name' => 'sip_device_id',
                    'class' => 'sip_device_id',
					'id' => 'sip_device_id'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'username',
                'sip_devices',
                'build_dropdown',
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
				'',
				'',
				'',
				'',
				'set_voice_broadcast_status',
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
			array(
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
				'id' => "voice_broadcast_search_btn",
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
	function build_voice_broadcast_list_for_admin() {
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
				"100",
				"name",
				"",
				"",
				"",
				"",
				"true",
				"center" 
			),
			array(
				gettext("Reseller"),
				"90",
				"reseller_id",
				"first_name,last_name,number,company_name",
				"accounts",
				"reseller_select_value",
				"",
				"true",
				"center"
			),
			array(
				gettext("Account"),
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
				gettext ( "SIP Device" ),
				"150",
				"sip_device_id",
				"username",
				"sip_devices",
				"get_field_name",
				"",
				"true",
				"center" 
			),
			array (
				gettext ("File Name"),
				"150",
				"broadcast",
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
				"80",
				"status",
				"status",
				"voice_broadcast",
				"get_status_voice_broadcast",
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
						"url" => "voice_broadcast/voice_broadcast_edit/",
						"mode" => "popup",
						"layout" => "small" 
					),
					"DELETE" => array (
						"url" => "voice_broadcast/voice_broadcast_remove/",
						"mode" => "single" 
					)
				),
				"false"
			)
			)   
		);
		return $grid_field_arr;
	}

	function build_grid_buttons() { 
		$buttons_json = json_encode ( array (
				array (
					gettext("Create"),
					"btn btn-line-warning btn",
					"fa fa-plus-circle fa-lg",
					"button_action",
					"/voice_broadcast/voice_broadcast_add/",
					"popup",
					"small",
					"create" 
				),
				array (
					gettext ( "Delete" ),
					"btn btn-line-danger",
					"fa fa-times-circle fa-lg",
					"button_action",
					"/voice_broadcast/voice_broadcast_delete_multiple/",
					"",
					"",
					"delete" 
				),
				array (
					gettext ( "Download Sample File" ),
					"btn btn-line-blue",
					"fa fa-download fa-lg",
					"button_action",
					"/voice_broadcast/voice_broadcast_download_sample_file/voice_broadcast_sample",
					'single',
					"",
					"download_sample_file"
				),
		) );
		return $buttons_json;
	}

}