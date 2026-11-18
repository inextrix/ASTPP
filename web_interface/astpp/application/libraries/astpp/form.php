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

class Form {
	protected $CI; 
	protected $fields = array (); 
	protected $form_title = 'Form';
	protected $form_id = 'form';
	protected $form_action = '';
	protected $form_class = '';
	protected $hidden = array ();
	protected $multipart = FALSE; 
	protected $submit_button = 'Submit';
	protected $after_button = '';
	protected $rules = array (); 

	protected $lib_class = "common";

	function __construct($common_lib = '') {
		$this->CI = & get_instance ();
		$this->CI->load->library ( 'form_validation' );
		$this->CI->load->library ( 'astpp/common' );
		$this->CI->load->model ( 'db_model' );
		$this->check_permissions ();
		if ($common_lib != '')
			$this->lib_class = $common_lib;
	}

	function check_permissions() { 
		if ($this->CI->session->userdata ( 'user_login' ) == TRUE) {
			$module_info = unserialize ( $this->CI->session->userdata ( "permited_modules" ) );
			if ($this->CI->session->userdata ( 'userlevel_logintype' ) != 0 && $this->CI->session->userdata ( 'userlevel_logintype' ) != 3) {
				$module_info [] = 'dashboard';
			}
			$url = $this->CI->uri->uri_string;
			$url_array = explode ( "/", $url );
			if (isset ( $url_array ['1'] )) {
				$module = explode ( '_', $url_array ['1'] );
			} else {
				$module = $url_array;
			}
			if ($this->CI->session->userdata ( 'userlevel_logintype' ) != -1) {
				$module_info [] = 'user';
			}
			$permissioninfo = $this->CI->session->userdata('permissioninfo');
			$permissioninfo_default_array = $this->CI->common->permission_info();

			$currnet_url=current_url();
			$url_explode= explode('/',$currnet_url);

			if(isset($url_explode[3]) and ($permissioninfo['login_type'] == '4' or $permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2')){

				$module_name= $url_explode[3];

			   if((isset($url_explode[4]) && isset($permissioninfo[$module_name])) || (isset($url_explode[3]) && $url_explode[3] == 'dashboard')){
				$sub_module_name= isset($url_explode[4])?$url_explode[4]:"";
				$sub_module_explode= explode('_',$sub_module_name);
				$logintype = $this->CI->session->userdata('logintype');
				if($module_name!="dashboard"){
				foreach($permissioninfo[$module_name] as $first_permission_key=>$first_permission_value){
				   $first_key_explode=explode('_',$first_permission_key);
				   $first_key_explode[1] = (isset($first_key_explode[1]))?'_'.$first_key_explode[1]:'';

				   if(isset($sub_module_explode[1])){
					$last_menu_name ='';
					if($sub_module_explode[1] == 'add')$last_menu_name='Create';
					if($sub_module_explode[1] == 'list')$last_menu_name='list';
					if($sub_module_explode[1] == 'edit')$last_menu_name='EDIT';
					if($sub_module_explode[1] == 'delete')$last_menu_name='DELETE';
					if($sub_module_explode[1] == 'add' && $sub_module_explode[0] == 'customer')$last_menu_name='Create Customer';
					if(isset($sub_module_explode[2]) && $sub_module_explode[2] == 'list')$last_menu_name='list';
	  			       $first_key_explode[1] = (isset($first_key_explode[2]))?$first_key_explode[1].'_'.$first_key_explode[2]:$first_key_explode[1];
					$Default_flag= 0;
					foreach( $permissioninfo_default_array as $default_permission_value=>$default_permission_key){
						if(isset($default_permission_key[$module_name][$sub_module_explode[0].$first_key_explode[1]])){
							$default_key_arr= $default_permission_key[$module_name][$sub_module_explode[0].$first_key_explode[1]];
							foreach($default_key_arr as $default_final_key =>$default_final_value){
								if($last_menu_name == $default_final_value){
									$Default_flag= 1;
								}
							}
						}
					}
					if(!isset($permissioninfo[$module_name][$sub_module_explode[0].$first_key_explode[1]][$last_menu_name]) && $last_menu_name != '' && $Default_flag == 1){
						if ($this->CI->session->userdata('userlevel_logintype') == '-1' || $this->CI->session->userdata('userlevel_logintype') == '2' || $this->CI->session->userdata('userlevel_logintype') == '4' || $this->CI->session->userdata('logintype') == '1') {
						    redirect(base_url().'dashboard/');
						} else {
						    redirect(base_url().'user/user/');
						}
					}
				   }					
				}
			      }
			   }
			}
			// ASTPPCOM-941 Start
			if (in_array ( $module [0], $module_info ) || (isset($url_explode[3]) && $url_explode[3] == 'dashboard') || (isset($url_explode[3]) && $url_explode[3] == 'login_activity') || (isset($url_explode[3]) && $url_explode[4] == 'systems_list') ) {
			// ASTPPCOM-941 END

				if ($this->CI->session->userdata ( 'userlevel_logintype' ) == 0 && $module [0] == 'customer' && isset ( $url_array [1] ) && $url_array [1] != 'customer_transfer') {
					redirect ( base_url () . 'user/user/' );
				} else {
					return true;
				}
			} else {
			  //Gautam ASTPPCOM-1402 start
			  if( $this->CI->session->userdata ( 'userlevel_logintype' ) != 0){ 
				$this->CI->load->library('astpp/permission');
				$this->CI->session->set_userdata ( 'astpp_errormsg', 'You do not have permission to access this module..!' );
				$url= $this->CI->session->userdata ( 'userlevel_logintype' ) ==0 ? 'user/user/' : "dashboard/";
				$this->CI->permission->permission_redirect_url($url);
			  }
			  //Gautam ASTPPCOM-1402 end
			}
		} else {
			redirect ( base_url () );
		}
	}
	function build_form($fields_array, $values) {
		$form_contents = '';
		$form_contents .= '<div class="pop_md col-12 pb-4">';
		if (isset ( $fields_array ['breadcrumb'] )) {
			$form_contents .= form_breadcrumb ( $fields_array ['breadcrumb'] );
			unset ( $fields_array ['breadcrumb'] );
		}
		$form_contents .= form_open ( $fields_array ['forms'] [0], $fields_array ['forms'] [1] );
		$form_name=$fields_array ['forms'] [1]['name'];
		if(file_exists(FCPATH."application/modules/".$this->CI->router->class.'/tooltip.php')){
			$file_name=FCPATH."application/modules/".$this->CI->router->class.'/tooltip.php';
				 include $file_name;
		}
		unset ( $fields_array ['forms'] );
		$button_array = array ();
		if (isset ( $fields_array ['button_save'] ) || isset ( $fields_array ['button_cancel'] ) || isset ( $fields_array ['additional_button'] )) {
			$save = $fields_array ['button_save'];
			unset ( $fields_array ['button_save'] );
			if (isset ( $fields_array ['button_cancel'] )) {
				$cancel = $fields_array ['button_cancel'];
				unset ( $fields_array ['button_cancel'] );
			}
			if (isset ( $fields_array ['additional_button'] )) {
				$additiopnal_button = $fields_array ['additional_button'];
				unset ( $fields_array ['additional_button'] );
			}
		}
		if (isset ( $additiopnal_button )) {
			$form_contents .= form_button ( gettext ( $additiopnal_button ) );
		}
		$i = 0;
		if(count ( $fields_array )>1 && count ( $fields_array )<3){
			$form_contents .= '<div class="card-two card-columns">';
		}else{
			if(count ( $fields_array )==1){
				$form_contents .= '<div class="card-one card-columns">';
			}else{	
					$form_contents .= '<div class="card-columns">';
			}
		}
		foreach ( $fields_array as $fieldset_key => $form_fileds ) {
			$form_contents .= '<div class="card">';
			$form_contents .= '<div class="col-12">';
			$form_contents .= '<ul class="p-0">';
			$form_contents .= '<div class="pb-4 col-12" id="floating-label">';
			if ($i == 1 || $i == 3) {
				$form_contents .= form_fieldset ( gettext ( $fieldset_key ) );
			} else {
				$form_contents .= form_fieldset ( gettext ( $fieldset_key ) );
			}
			foreach ( $form_fileds as $fieldkey => $fieldvalue ) {
				if (isset ( $fieldvalue ) && $fieldvalue != '') {
					$placeholder = $fieldvalue [0];
					$form_contents .= '<li class="col-md-12 form-group">';
					if ($fieldvalue [1] == 'HIDDEN') {
						if (isset ( $this->CI->input->post ))
							$fieldvalue [2] ['value'] = (! $this->CI->input->post ( $fieldvalue [2] ['name'] )) ? @$fieldvalue [2] ['value'] : $this->CI->input->post ( $fieldvalue [2] ['name'] );
						else
							$fieldvalue [2] ['value'] = ($values) ? (isset ( $values [$fieldvalue [2] ['name']] ) ? $values [$fieldvalue [2] ['name']] : '') : (isset ( $fieldvalue [2] ['value'] ) ? $fieldvalue [2] ['value'] : '');
						
						$form_contents .= form_hidden ( $fieldvalue [2] ['name'], $fieldvalue [2] ['value'] );
					} else {
						$validation_arr = array ();
						if ($fieldvalue [1] == 'INPUT') {
							if (! empty ( $fieldvalue [3] )) {
								$validation_arr = explode ( "|", $fieldvalue [3] );
							}
						} elseif ($fieldvalue [2] == 'SELECT') {
							
							if (is_array ( $fieldvalue [4] )) {
								$validation_arr = explode ( "|", $fieldvalue [4] ['rules'] );
							} else {
								$validation_arr = explode ( "|", $fieldvalue [4] );
							}
						}
						if (! empty ( $validation_arr )) {
							$fieldvalue [0] = in_array ( 'required', $validation_arr ) ? $fieldvalue [0] . "<span style='color:black;'> *</span>" : $fieldvalue [0];
							$fieldvalue [0] = in_array ( 'dropdown', $validation_arr ) ? $fieldvalue [0] . "<span style='color:black;'> *</span>" : $fieldvalue [0];
						}
						if(is_array($fieldvalue [1])){
							$form_names= $form_name."_".$fieldvalue [1]['name'];
						}else if(is_array($fieldvalue [2])){
							$form_names= $form_name."_".$fieldvalue [2]['name'];
						}else{
							$form_names= $form_name."_".$fieldvalue [1];
						}
						if (is_array ( $fieldvalue [1] ) || (is_array ( $fieldvalue [2] ) && isset ( $fieldvalue [2] ['hidden'] ))) {
							$form_contents .= form_label ( gettext ( $fieldvalue [0] ), $fieldvalue [0], array (
									'class' => 'p-0 pr-1 control-label add_settings',
									'data-toggle'=>"tooltip",
									'data-html'=>"true",
									'data-original-title'=> $tooltip_data[$form_names],
									'data-placement'=>"right"	 
							) );
						} else {
							$form_contents .= form_label (  $fieldvalue [0] , "", array (
									"class" => "p-0 pr-1 control-label " ,
									"for"   => $fieldvalue [0],
									'data-toggle'=>"tooltip",
									'data-html'=>"true",
									'data-original-title'=> $tooltip_data[$form_names],
									'data-placement'=>"right"	
							) );
						}
					}
					if ($fieldvalue [2] == 'SELECT' && ! isset ( $fieldvalue [13] )) {
						$extra = isset ( $fieldvalue [1] ['extra'] ) ? $fieldvalue [1] ['extra'] : '';
						if ($fieldvalue [7] != '' && $fieldvalue [8] != '') {
							$str = $fieldvalue [7] . "," . $fieldvalue [8];
							
							if (isset ( $this->CI->input->post )) {
								$fieldvalue ['value'] = (! $this->CI->input->post ( $fieldvalue [1] )) ? @$fieldvalue [1] : $this->CI->input->post ( $fieldvalue [1] );
							} else {
								if (is_array ( $fieldvalue [1] )) {
									$fieldvalue ['value'] = ($values) ? @$values [$fieldvalue [1] ['name']] : @$fieldvalue [1];
								} else {
									$fieldvalue ['value'] = ($values) ? @$values [$fieldvalue [1]] : @$fieldvalue [1];
								}
							}
							$drp_array = call_user_func_array ( array (
									$this->CI->db_model,
									$fieldvalue [10] 
							), array (
									$str,
									$fieldvalue [9],
									$fieldvalue [11],
									$fieldvalue [12] 
							) );
							
							if ($fieldset_key == gettext ( 'System Configuration Information' ) || ($fieldset_key == 'Billing Information' && $fieldvalue [0] == 'Force Trunk') || ($fieldset_key == 'Card Information' && $fieldvalue [0] == 'Rate Group') || ($fieldset_key == 'Billing Information' && $fieldvalue [0] == 'Account')|| ($fieldset_key == 'Rate Information' && ($fieldvalue [0] == 'Country' || $fieldvalue [0] == 'Call Type'))|| ($fieldset_key == 'Ratedeck Information' && ($fieldvalue [0] == 'Call Type')) || $fieldset_key == 'Freeswitch Devices' && $fieldvalue [0] == 'Rate Group' || ($fieldset_key == 'Origination Rate Add/Edit' && $fieldvalue [0] == 'Trunks') || $fieldset_key == 'Billing Information' && $fieldvalue [0] == 'Rate Group' || ($fieldset_key == 'Information' && $fieldvalue [0] == 'Failover GW Name #1') || ($fieldset_key == 'Information' && $fieldvalue [0] == 'Failover GW Name #2') || ($fieldset_key == 'Information' && $fieldvalue [0] == 'Rate Group') || ($fieldset_key == 'Sip Devices' && $fieldvalue [0] == 'Sip Profile') || ($fieldset_key == 'Sip Devices' && $fieldvalue [0] == 'Account') || ($fieldset_key == 'Account Settings' && $fieldvalue [0] == 'Localization') || ($fieldset_key == 'Settings' && $fieldvalue [0] == 'Localization')) {
								$form_contents .= form_dropdown_all ( $fieldvalue [1], $drp_array, $fieldvalue ['value'], $extra );
							} else {
								$form_contents .= form_dropdown ( $fieldvalue [1], $drp_array, $fieldvalue ['value'], $extra );
							}
							if (isset ( $fieldvalue [4] ) && $fieldvalue [4] != '') {
								if (is_array ( $fieldvalue [4] )) {
									
									if (isset ( $fieldvalue [1] ['name'] )) {
										$fieldvalue_pass = $fieldvalue [1] ['name'];
									} else {
										$fieldvalue_pass = $fieldvalue [1];
									}
									
									$this->CI->form_validation->set_rules ( $fieldvalue_pass, $fieldvalue [0], $fieldvalue [4] ['rules'] );
								} else {
									
									if (isset ( $fieldvalue [1] ['name'] )) {
										$fieldvalue_pass = $fieldvalue [1] ['name'];
									} else {
										$fieldvalue_pass = $fieldvalue [1];
									}
									
									$this->CI->form_validation->set_rules ( $fieldvalue_pass, $fieldvalue [0], $fieldvalue [4] );
								}
							}
							$form_contents .= '<div class="tooltips error_div pull-left p-0" id="' . (is_array ( $fieldvalue [1] ) ? $fieldvalue [1] ['name'] : $fieldvalue [1]) . '_error_div" ><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i>';
							$form_contents .= '<span class="popup_error error  p-0" id="' . (gettext ( is_array ( $fieldvalue [1] ) ? $fieldvalue [1] ['name'] : $fieldvalue [1] )) . '_error">
                        </span></div>';
						} else {
							if (isset ( $this->CI->input->post )) {
								$fieldvalue ['value'] = (! $this->CI->input->post ( $fieldvalue [1] )) ? @$fieldvalue [1] : $this->CI->input->post ( $fieldvalue [1] );
							} else {
								if (is_array ( $fieldvalue [1] )) {
									$fieldvalue ['value'] = ($values) ? @$values [$fieldvalue [1] ['name']] : @$fieldvalue [1];
								} else {
									$fieldvalue ['value'] = ($values) ? @$values [$fieldvalue [1]] : @$fieldvalue [1];
								}
							}
							
							$str = $fieldvalue [7] . "," . $fieldvalue [8];
							$drp_array = call_user_func_array ( array (
									$this->CI->{$this->lib_class},
									$fieldvalue [10] 
							), array (
									$fieldvalue [9] 
							) );
							$form_contents .= form_dropdown ( $fieldvalue [1], $drp_array, $fieldvalue ['value'], $extra );
							if (isset ( $fieldvalue [4] ) && $fieldvalue [4] != '') {
								$this->CI->form_validation->set_rules ( $fieldvalue [1], $fieldvalue [0], $fieldvalue [4] );
							}
							$form_contents .= '<div class="tooltips error_div pull-left p-0" id="' . (is_array ( $fieldvalue [1] ) ? $fieldvalue [1] ['name'] : $fieldvalue [1]) . '_error_div" ><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i>';
							$form_contents .= '<span class="popup_error error  p-0" id="' . (is_array ( $fieldvalue [1] ) ? $fieldvalue [1] ['name'] : $fieldvalue [1]) . '_error">
                        </span></div>';
						}
					} else if (isset ( $fieldvalue [13] ) && $fieldvalue [13] != '') {
						$str = $fieldvalue [7] . "," . $fieldvalue [8];
						
						if (isset ( $this->CI->input->post ))
							$fieldvalue ['value'] = (! $this->CI->input->post ( $fieldvalue [1] )) ? @$fieldvalue [1] : $this->CI->input->post ( $fieldvalue [1] );
						else
							$fieldvalue ['value'] = ($values) ? @$values [$fieldvalue [1]] : @$fieldvalue [1];
						
						$drp_array = call_user_func_array ( array (
								$this->CI->db_model,
								$fieldvalue [10] 
						), array (
								$str,
								$fieldvalue [9],
								$fieldvalue [11],
								$fieldvalue [12] 
						) );
						if ($fieldset_key === 'System Configuration Information') {
							$form_contents .= form_dropdown_multiselect ( $fieldvalue [1], $drp_array, '' );
						} else {
							$form_contents .= form_dropdown_multiselect ( $fieldvalue [1] . "[]", $drp_array, $fieldvalue ['value'] );
						}
						if (isset ( $fieldvalue [4] ) && $fieldvalue [4] != '') {
							$this->CI->form_validation->set_rules ( $fieldvalue [1], $fieldvalue [0], $fieldvalue [4] );
						}
						$form_contents .= '<div class="tooltips error_div pull-left p-0" id="' . $fieldvalue [1] . '_error_div" ><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i>';
						$form_contents .= '<span class="popup_error error  p-0" id="' . $fieldvalue [1] . '_error"></span></div>';
					} else if ($fieldvalue [1] == 'INPUT') {

						if (isset ( $this->CI->input->post ))
							$fieldvalue [2] ['value'] = (! $this->CI->input->post ( $fieldvalue [2] ['name'] )) ? $fieldvalue [2] ['value'] : $this->CI->input->post ( $fieldvalue [2] ['name'] );
						else {
							$fieldvalue [2] ['value'] = ($values) ? (isset ( $values [$fieldvalue [2] ['name']] ) ? $values [$fieldvalue [2] ['name']] : '') : (isset ( $fieldvalue [2] ['value'] ) ? $fieldvalue [2] ['value'] : '');
						
						}
						$form_contents .= form_input ( $fieldvalue [2], 'readonly' );
						if (isset ( $fieldvalue [6] ) && ! empty ( $fieldvalue [6] )) {
							$form_contents .= $fieldvalue [6];
						}
						$this->CI->form_validation->set_rules ( $fieldvalue [2] ['name'], $fieldvalue [0], $fieldvalue [3] );
						$form_contents .= '<div class="tooltips error_div pull-left p-0" id="' . $fieldvalue [2] ['name'] . '_error_div" ><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i>';
						$form_contents .= '<span class="popup_error error  p-0" id="' . $fieldvalue [2] ['name'] . '_error">
                    </span></div>';
					} 					
					else if ($fieldvalue [1] == 'IMAGE') {
						if (isset ( $this->CI->input->post ))
							$fieldvalue [2] ['value'] = (! $this->CI->input->post ( $fieldvalue [2] ['name'] )) ? @$fieldvalue [2] ['value'] : $this->CI->input->post ( $fieldvalue [2] ['name'] );
						
						else
							$fieldvalue [2] ['value'] = ($values) ? @$values [$fieldvalue [2] ['name']] : @$fieldvalue [2] ['value'];
						$fieldvalue [2] ['style'] = isset ( $fieldvalue [2] ['style'] ) ? $fieldvalue [2] ['style'] : "";
						$form_contents .= form_image ( $fieldvalue [2], 'readonly', $fieldvalue [2] ['style'] );
						$form_contents .= @$fieldvalue [6];
						$this->CI->form_validation->set_rules ( $fieldvalue [2] ['name'], $fieldvalue [0], $fieldvalue [3] );
						$form_contents .= '<div class="tooltips error_div pull-left p-0" id="' . $fieldvalue [2] ['name'] . '_error_div" ><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i>';
						$form_contents .= '<span class="popup_error error  p-0" id="' . $fieldvalue [2] ['name'] . '_error">
                    </span></div>';
					} else if ($fieldvalue [1] == 'DEL_BUTTON') {
						if (isset ( $this->CI->input->post ))
							$fieldvalue [2] ['value'] = (! $this->CI->input->post ( $fieldvalue [2] ['name'] )) ? @$fieldvalue [2] ['value'] : $this->CI->input->post ( $fieldvalue [2] ['name'] );
						
						else
							$fieldvalue [2] ['value'] = ($values) ? @$values [$fieldvalue [2] ['name']] : @$fieldvalue [2] ['value'];
						$fieldvalue [2] ['style'] = isset ( $fieldvalue [2] ['style'] ) ? $fieldvalue [2] ['style'] : "";
						$form_contents .= form_img_delete ( $fieldvalue [2], 'readonly', $fieldvalue [2] ['style'] );
						$form_contents .= @$fieldvalue [6];
						$this->CI->form_validation->set_rules ( $fieldvalue [2] ['name'], $fieldvalue [0], $fieldvalue [3] );
						$form_contents .= '<div class="tooltips error_div pull-left p-0" id="' . $fieldvalue [2] ['name'] . '_error_div" ><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i>';
						$form_contents .= '<span class="popup_error error  p-0" id="' . $fieldvalue [2] ['name'] . '_error">
                    </span></div>';
					} 
					else if ($fieldvalue [1] == 'PASSWORD') {
						if (isset ( $this->CI->input->post ))
							$fieldvalue [2] ['value'] = (! $this->CI->input->post ( $fieldvalue [2] ['name'] )) ? @$fieldvalue [2] ['value'] : $this->CI->input->post ( $fieldvalue [2] ['name'] );
						else
							$fieldvalue [2] ['value'] = ($values) ? @$values [$fieldvalue [2] ['name']] : @$fieldvalue [2] ['value'];
						$form_contents .= form_password ( $fieldvalue [2] );
						$this->CI->form_validation->set_rules ( $fieldvalue [2] ['name'], $fieldvalue [0], $fieldvalue [3] );
						$form_contents .= '<div class="tooltips error_div pull-left p-0" id="' . $fieldvalue [2] ['name'] . '_error_div" ><i style="color:#D95C5C; padding-right: 6px; padding-top: 10px;" class="fa fa-exclamation-triangle"></i>';
						$form_contents .= '<span class="popup_error error  p-0" id="' . $fieldvalue [2] ['name'] . '_error">
                    </span></div>';
					} else if ($fieldvalue [2] == 'CHECKBOX') {
						$OptionArray = array ();
						
						if (isset ( $fieldvalue [7] ) && $fieldvalue [7] != '')
							$OptionArray = call_user_func_array ( array (
									$this->CI->{$this->lib_class},
									$fieldvalue [7] 
							), array (
									$fieldvalue [6] 
							) );
						if (isset ( $this->CI->input->post )) {
							$fieldvalue [3] ['value'] = (! $this->CI->input->post ( $fieldvalue [1] )) ? @$fieldvalue [3] ['value'] : $this->CI->input->post ( $fieldvalue [1] );
						} else {
							$fieldvalue [3] ['value'] = ($values) ? (isset ( $values [$fieldvalue [1]] ) && $values [$fieldvalue [1]] ? 1 : 0) : @$fieldvalue [3] ['value'];
						}
						if ($fieldvalue [3] ['value'] == "1") {
							$checked = true;
						} else {
							$checked = false;
						}
						;
						if (isset ( $fieldvalue [3] ['table_name'] ) && $fieldvalue [3] ['table_name'] != "") {
							
							$form_contents .= form_checkbox ( $fieldvalue [1], $fieldvalue [3] ['value'], $checked, $OptionArray );
						} else {
							$form_contents .= form_checkbox ( $fieldvalue [1], $fieldvalue [3] ['value'], $checked, $OptionArray );
						}
					} else if ($fieldvalue [1] == 'TEXTAREA') {
						
						if (isset ( $this->CI->input->post ))
							$fieldvalue [2] ['value'] = (! $this->CI->input->post ( $fieldvalue [2] ['name'] )) ? @$fieldvalue [2] ['value'] : $this->CI->input->post ( $fieldvalue [2] ['name'] );
						else
							$fieldvalue [2] ['value'] = ($values) ? $values [$fieldvalue [2] ['name']] : @$fieldvalue [2] ['value'];
						$form_contents .= form_textarea ( $fieldvalue [2] );
					} else if ($fieldvalue [2] == 'RADIO') {
						
						$form_contents .= form_radio ( $fieldvalue [1], $fieldvalue [3] ['value'], $fieldvalue [3] ['checked'] );
					}
					$form_contents .= '</li>';
				}
			}
			$form_contents .= '</ul>';
			$form_contents .= '</div>';
			$form_contents .= '</div>';
			$i ++;
		}
		$form_contents .= '</div>';
		$form_contents .= '<div class="col-12 my-4 text-center">';
		
		$form_contents .= form_button ( $save );
		
		if (isset ( $cancel )) {
			$form_contents .= form_button ( $cancel );
		}
		
		$form_contents .= '</div>';
		$form_contents .= form_close ();
		$form_contents .= '</div>';
		
		return $form_contents;
	}
	function build_serach_form($fields_array) {
		$form_contents = '';
		$form_contents .= '<div class="card">';
		$form_contents .= form_open ( $fields_array ['forms'] [0], $fields_array ['forms'] [1] );
		unset ( $fields_array ['forms'] );
		$button_array = array ();
		
		if (isset ( $fields_array ['button_search'] ) || isset ( $fields_array ['button_reset'] ) || isset ( $fields_array ['button_search_delete'] ) || isset ( $fields_array ['display_in'] )) {
			$save = $fields_array ['button_search'];
			unset ( $fields_array ['button_search'] );
			if ($fields_array ['button_reset']) {
				$cancel = $fields_array ['button_reset'];
				unset ( $fields_array ['button_reset'] );
			}
			$button_search_delete = '';
			if (isset ( $fields_array ['button_search_delete'] )) {
				$button_search_delete = $fields_array ['button_search_delete'];
				unset ( $fields_array ['button_search_delete'] );
			}
			if (isset ( $fields_array ['display_in'] )) {
				$display_in = $fields_array ['display_in'];
				unset ( $fields_array ['display_in'] );
			}
			// Kinjal ASTPPCOM-978 Start
			if (isset ($fields_array ['button_subscribe'])) {
				$button_subscribe=$fields_array ['button_subscribe'];
				unset ($fields_array ['button_subscribe']);
			}
			// Kinjal ASTPPCOM-978 END
		}
		$i = 1;
		foreach ( $fields_array as $fieldset_key => $form_fileds ) {
			$form_contents .= '<div class="float-right panel_close text-light p-3" id="global_clearsearch_filter" style="cursor:pointer;"><i class="fa fa-remove"></i></div>';
			
			$form_contents .= '<ul id="floating-label" class="px-0 pb-4">';
			$form_contents .= form_fieldset ( gettext ( $fieldset_key ), "search" );
		        $form_contents.= '<div class="col-md-12"><div class="row">';
			foreach ( $form_fileds as $fieldkey => $fieldvalue ) {
				if ($i == 0) {
					$form_contents .= '<li class="col-md-12">';
				}
				$form_contents .= '<div class="col-md-6 col-lg-3 input-group">';
				if ($fieldvalue [1] == 'HIDDEN') {
					$form_contents .= form_hidden ( $fieldvalue [2], $fieldvalue [3] );
				} else {
					$form_contents .= form_label ( gettext ( $fieldvalue [0] ), "", array (
							"class" => "search_label col-md-12 p-0" 
					) );
				}
				if ($fieldvalue [1] == 'INPUT') {
					$form_contents .= form_input ( $fieldvalue [2] );
				}
				
				if ($fieldvalue [2] == 'SELECT' || $fieldvalue [5] == '1') {

					if ($fieldvalue [7] != '' && $fieldvalue [8] != '') {
						$str = $fieldvalue [7] . "," . $fieldvalue [8];
						
						$drp_array = call_user_func_array ( array (
								$this->CI->db_model,
								$fieldvalue [10] 
						), array (
								$str,
								$fieldvalue [9],
								$fieldvalue [11],
								$fieldvalue [12] 
						) );
						$form_contents .= form_dropdown_all ( $fieldvalue [1], $drp_array, '' );
					} else {
						
						if ($fieldvalue [1] == 'INPUT') {
							$fieldvalue [1] = $fieldvalue [6];
						} 
						$drp_array = call_user_func_array ( array (
								$this->CI->{$this->lib_class},
								$fieldvalue [10] 
						), array (
								$fieldvalue [9] 
						) );
						$form_contents .= form_dropdown_all_search ( $fieldvalue [1], $drp_array, '' );
					}
				} else if ($fieldvalue [1] == 'PASSWORD') {
					$form_contents .= form_password ( $fieldvalue [2] );
				} else if ($fieldvalue [2] == 'CHECKBOX') {
					$form_contents .= form_checkbox ( $fieldvalue [1], $fieldvalue [3] ['value'], $fieldvalue [3] ['checked'] );
				}
				$form_contents .= '</div>';
				if ($i % 5 == 0) {
					$form_contents .= '</li>';
					$i = 0;
				}
				$i ++;
			}
        		$form_contents.= '</div></div>';
		}
		$form_contents .= '<div class="col-12 p-4">';
		$form_contents .= form_button ( $cancel );
		// Kinjal ASTPPCOM-978 Start
		if (isset ($button_subscribe) && $button_subscribe !='') {
			if($button_subscribe['mode'] == 'popup'){
				$form_contents.=form_button($button_subscribe);
			}
		}
		// Kinjal ASTPPCOM-978 END
		$form_contents .= form_button ( $save );
		if (! empty ( $display_in )) {
			$form_contents .= "<div class='col-md-5 float-right'>";
			$form_contents .= "<div class='col-md-3'></div>";
			$extra_parameters ['class'] = $display_in ['label_class'];
			$extra_parameters ['style'] = $display_in ['label_style'];
			$form_contents .= form_label ( $display_in ['content'], "", $extra_parameters );
			$drp_array = call_user_func_array ( array (
					$this->CI->{$this->lib_class},
					$display_in ['function'] 
			), array () );
			$extra_parameters ['class'] = $display_in ['dropdown_class'];
			$extra_parameters ['style'] = $display_in ['dropdown_style'];
			$form_contents .= form_dropdown_all_search ( $display_in, $drp_array, '', $extra_parameters );
			$form_contents .= "</div>";
		}
		if (isset ( $button_search_delete ) && $button_search_delete != '') {
			$form_contents .= form_button ( $button_search_delete );
		}
		$form_contents .= '</ul>';
		$form_contents .= '</div>';
		$form_contents .= form_fieldset_close ();
		$form_contents .= form_close ();
		$form_contents .= '</div>';
		
		return $form_contents;
	}
	function build_batchupdate_form($fields_array) {
		$form_contents = '';
		$form_contents .= '<div class="card">';
		$form_contents .= form_open ( $fields_array ['forms'] [0], $fields_array ['forms'] [1] );
		unset ( $fields_array ['forms'] );
		$button_array = array ();
		if (isset ( $fields_array ['button_search'] ) || isset ( $fields_array ['button_reset'] )) {
			$save = $fields_array ['button_search'];
			unset ( $fields_array ['button_search'] );
			if ($fields_array ['button_reset']) {
				$cancel = $fields_array ['button_reset'];
				unset ( $fields_array ['button_reset'] );
			}
		}
		$i = 1;
		foreach ( $fields_array as $fieldset_key => $form_fileds ) {
			$form_contents .= '<div class="float-right panel_close text-light p-3" id="global_clearbatchupdate_filter" style="cursor:pointer;"><i class="fa fa-remove"></i></div>';
			
			$form_contents .= '<ul id="floating-label" class="p-0">';
			$form_contents .= form_fieldset ( gettext ( $fieldset_key ) );
			$form_contents .= '<div class="col-md-12">';
			$form_contents .= '<div class="row">';
			foreach ( $form_fileds as $fieldkey => $fieldvalue ) {
				if ($i == 0) {
					$form_contents .= '<li>';
				}
				$form_contents .= '<div class="col-md-4 input-group">';
				if ($fieldvalue [1] == 'HIDDEN') {
					$form_contents .= form_hidden ( $fieldvalue [2], $fieldvalue [3] );
				} else {
					$form_contents .= form_label ( gettext ( $fieldvalue [0] ), "", array (
							"class" => "search_label col-md-12 p-0" 
					) );
				}
				if ($fieldvalue [2] == 'SELECT' || $fieldvalue [5] == '1') {
					if ($fieldvalue [7] != '' && $fieldvalue [8] != '') {
						$str = $fieldvalue [7] . "," . $fieldvalue [8];
						if (is_array ( $fieldvalue [13] )) {
							$drp_array = call_user_func_array ( array (
									$this->CI->{$this->lib_class},
									$fieldvalue [14] 
							), array (
									$fieldvalue [13] 
							) );
							$form_contents .= form_dropdown ( $fieldvalue [13], $drp_array, '' );
						}
						if ($fieldvalue [10] == 'set_status') {
							$drp_array = array (
								'0' => gettext('Active'),
								'1' => gettext('Inactive')
							);
						} 
						else {
							$drp_array = call_user_func_array ( array (
									$this->CI->db_model,
									$fieldvalue [10] 
							), array (
									$str,
									$fieldvalue [9],
									$fieldvalue [11],
									$fieldvalue [12] 
							) );
						}
						$form_contents .= form_dropdown_all ( $fieldvalue [1], $drp_array, '' );
					} else {
						if ($fieldvalue [1] == 'INPUT') {
							$drp_name = $fieldvalue [6];
						}
						$drp_array = call_user_func_array ( array (
								$this->CI->{$this->lib_class},
								$fieldvalue [10] 
						), array (
								$fieldvalue [9] 
						) );
						$form_contents .= form_dropdown ( $drp_name, $drp_array, '' );
					}
				}
				if ($fieldvalue [1] == 'INPUT') {
					$form_contents .= form_input ( $fieldvalue [2] );
				} else if ($fieldvalue [2] == 'CHECKBOX') {
					$form_contents .= form_checkbox ( $fieldvalue [1], $fieldvalue [3] ['value'], $fieldvalue [3] ['checked'] );
				}
				$form_contents .= '</div>';
				if ($i % 5 == 0) {
					$form_contents .= '</li>';
					$i = 0;
				}
				$i ++;
			}
		}
		
		$form_contents .= '</div>';
		$form_contents .= '</div>';
		$form_contents .= '</ul>';
		$form_contents .= '<div class="col-12 p-4">';
		
		$form_contents .= form_button ( $cancel );
		$form_contents .= form_button ( $save );
		
		$form_contents .= form_fieldset_close ();
		$form_contents .= form_close ();
		$form_contents .= '</div>';
		$form_contents .= '</div>';
		
		return $form_contents;
	}
	function load_grid_config($count_all, $rp, $page) {
		$json_data = array ();
		$config ['total_rows'] = $count_all;
		$config ['per_page'] = $rp;
		
		$page_no = $page;
		$json_data ["json_paging"] ['page'] = $page_no;
		
		$json_data ["json_paging"] ['total'] = $config ['total_rows'];
		$perpage = $config ['per_page'];
		$start = ($page_no - 1) * $perpage;
		if ($start < 0)
			$start = 0;
		$json_data ["paging"] ['start'] = $start;
		$json_data ["paging"] ['page_no'] = $perpage;
		return $json_data;
	}
	
	function build_grid($query, $grid_fields) {
		$jsn_tmp = array ();
		$json_data = array ();
		if ($query->num_rows () > 0) {
			$permissioninfo = $this->CI->session->userdata('permissioninfo');
			$accountinfo = $this->CI->session->userdata("accountinfo");
			$default_reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] ==5 ? $accountinfo ['id']  : ($accountinfo['type'] ==1 ? $accountinfo['reseller_id'] : 0);
			$currnet_url=current_url();
			$url_explode= explode('/',$currnet_url);
			$module_name= $url_explode[3];
			$sub_module_name= $url_explode[4];
			$sub_module_name= str_replace("_json","",$sub_module_name);

			$logintype = $this->CI->session->userdata('logintype');

			$Actionkey = array_search (gettext('Action'),array_column ( $grid_fields, 0 ) );
			// if ($Actionkey == '') {
			// 	$Actionkey = array_search ('action',array_column ( $grid_fields, 0 ) );
			// }
			// if ($Actionkey == '') {
			// 	$Actionkey = array_search ('Acción',array_column ( $grid_fields, 0 ) );
			// }
			// if ($Actionkey == '') {
			// 	$Actionkey = array_search ('Ação',array_column ( $grid_fields, 0 ) );
			// }
			// if ($Actionkey == '') {
			// 	$Actionkey = array_search ('действие',array_column ( $grid_fields, 0 ) );
			// }
			// if ($Actionkey == '') {
			// 	$Actionkey = array_search ('Açao',array_column ( $grid_fields, 0 ) );
			// }
			$ActionArr = $grid_fields [$Actionkey];

			$current_button_url = '';
			if(isset($ActionArr [5]) && isset($ActionArr [5]->EDIT) && isset($ActionArr [5]->EDIT->url) && !empty($ActionArr [5]) && !empty($ActionArr [5]->EDIT) && !empty($ActionArr [5]->EDIT->url)){
				$current_button_url = $ActionArr [5]->EDIT->url;
			}
			foreach ( $query->result_array () as $row ) {
				$row_id = isset ( $row ['id'] ) ? $row ["id"] : '';
				if ($current_button_url == "accounts/customer_edit/") {
						$account_type = strtolower($this->CI->{$this->lib_class}->get_entity_type ( "", "", $row ["type"] ));
						$ActionArr [5]->EDIT->url = $account_type == 'administrator' ? "accounts/admin_edit/": "accounts/".$account_type."_edit/";
				}
				$acctype = "";
				if (isset ( $row ["type"] ) && ($row ["type"] == '0' || $row ["type"] == '1' || $row ["type"] == '3')) {

					$acctype = (isset ( $row ["posttoexternal"] ) && $row ["posttoexternal"] != '') ? "<span class='badge badge-dark float-left ml-1 mt-1'>" . $this->CI->{$this->lib_class}->get_account_type ( "", "", $row ["posttoexternal"] ) . "</span>" : "";
				}
				$reseller_id = $default_reseller_id;
				if($default_reseller_id  == 0){
						$reseller_id = isset($row['reseller_id']) ? $row['reseller_id'] : $reseller_id;
				}
				foreach ( $grid_fields as $field_key => $field_arr ) {
						
					if ($field_arr [2] != "") {
						if ($field_arr [3] != "") {
							if ($field_arr [2] == "status" || $field_arr [2] == "is_email_enable" || $field_arr [2] == "is_sms_enable" || $field_arr [2] == "is_alert_enable" || $field_arr [2] == "optin") {
								$row ['id'] = $row_id;
								$jsn_tmp [$field_key] = call_user_func_array ( array (
										$this->CI->{$this->lib_class},
										$field_arr [5] 
								), array (
										$field_arr [3],
										$field_arr [4],
										$row 
								) );
							} else {
								$jsn_tmp [$field_key] = call_user_func_array ( array (
										$this->CI->{$this->lib_class},
										$field_arr [5] 
								), array (
										$field_arr [3],
										$field_arr [4],
										$row [$field_arr [2]] 
								) );
							}
							$row [$field_arr [2]] = $jsn_tmp [$field_key];
							
							
						}
						
						if(isset($field_arr[6]) && !empty($field_arr[6]) && is_array($field_arr[6]) && $field_arr[6][0]== 'EDITABLE' && ((isset($permissioninfo[$module_name][$sub_module_name][$field_arr[6][1]]) && $permissioninfo[$module_name][$sub_module_name][$field_arr[6][1]] == 0 and $permissioninfo['login_type'] != '-1' and $permissioninfo['login_type'] != '3')  or ($permissioninfo['login_type'] == '-1' ) or ($permissioninfo['login_type'] == '3'))){
									

									$button_name = strtoupper($field_arr[6][1]);
									if(isset($ActionArr[5]->$button_name)){
									$current_button_html = $this->CI->{$this->lib_class}->get_action_buttons(array($button_name=>$ActionArr[5]->$button_name),$row['id']);
									$jsn_tmp [$field_key] = str_replace($button_name, $row [$field_arr [2]],$current_button_html);					
									}
									
						 
						}else if(array_search("EDITABLE", $field_arr) && (isset($permissioninfo[$module_name][$sub_module_name]['edit']) && $permissioninfo[$module_name][$sub_module_name]['edit'] == 0 or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '3' or $permissioninfo['login_type'] == '0') ){
									
							$fieldstr = $this->CI->{$this->lib_class}->build_custome_edit_button ( $ActionArr [5]->EDIT, $row [$field_arr [2]], $row ["id"] );
							$jsn_tmp [$field_key] = $acctype != '' ? $fieldstr . $acctype : $fieldstr;
							$sub_login_arr = '';
							if ($ActionArr [5]->EDIT->url == "accounts/customer_edit/") {
								$sub_login_arr = "<a href='".base_url()."login/login_as_customer/".$row ['id']."' title='Login As Customer'><i class='fa fa-sign-in' aria-hidden='true'></i></a>";
								$jsn_tmp [$field_key] = $fieldstr . $acctype . $sub_login_arr;
							}

							if ($ActionArr [5]->EDIT->url == "accounts/reseller_edit/") {
								$sub_login_arr = "<a href='".base_url()."login/login_as_reseller/".$row ['id']."' title='Login As Reseller'><i class='fa fa-sign-in' aria-hidden='true'></i></a>";
								$jsn_tmp [$field_key] = $fieldstr . $acctype . $sub_login_arr;
							}
						} else {
							$jsn_tmp [$field_key] = $row [$field_arr [2]];
						}
					} else {
						if ($field_arr [0] == gettext ( "Action" )) {
							if(isset($field_arr[6]) && $field_arr[6] != "false"){
								$jsn_tmp [$field_key] = $this->CI->{$this->lib_class}->get_action_buttons ( $field_arr [5], $row_id );
							}
						} elseif ($field_arr [0] == gettext ( "Profile Action" )) {
							if (isset ( $field_arr [5] ) && isset ( $field_arr [5]->START ) && isset ( $field_arr [5]->STOP ) && isset ( $field_arr [5]->RELOAD ) && isset ( $field_arr [5]->RESCAN )) {
							}
							$jsn_tmp [$field_key] = $this->CI->{$this->lib_class}->get_action_buttons ( $field_arr [5], $row ["id"] );
						} else {
							$className = (isset ( $field_arr ['9'] ) && $field_arr ['9'] != '') ? $field_arr ['9'] : "chkRefNos";
							
							$custom_value =!empty($field_arr[3]) ? $row[$field_arr[3]] : $row['id'];
							$jsn_tmp [$field_key] = '<input type="checkbox" name="chkAll" id=' . $row ['id'] . ' class="ace ' . $className . '" onclick="clickchkbox(' . $custom_value . ')" value=' .$custom_value. '><lable class="lbl"></lable>';
						}
					}
				}
				$json_data [] = array (
						'cell' => $jsn_tmp 
				);
			}
		}
		return $json_data;
	}
	function build_json_grid($query, $grid_fields) {
		$jsn_tmp = array ();
		$json_data = array ();
		foreach ( $query as $row ) {
			foreach ( $grid_fields as $field_key => $field_arr ) {
				$row_id = isset ( $row ['id'] ) ? $row ["id"] : '';
				$Actionkey = array_search ( 'Action', $this->CI->{$this->lib_class}->array_column ( $grid_fields, 0 ) );
				
				if ($field_arr [2] != "") {
					if ($field_arr [3] != "") {
						if ($field_arr [2] == "status") {
							$row ['id'] = $row_id;
							$jsn_tmp [$field_key] = call_user_func_array ( array (
									$this->CI->{$this->lib_class},
									$field_arr [5] 
							), array (
									$field_arr [3],
									$field_arr [4],
									$row 
							) );
						} else {
							$jsn_tmp [$field_key] = call_user_func_array ( array (
									$this->CI->{$this->lib_class},
									$field_arr [5] 
							), array (
									$field_arr [3],
									$field_arr [4],
									$row [$field_arr [2]] 
							) );
						}
						$row [$field_arr [2]] = $jsn_tmp [$field_key];
					}
					if (array_search ( "EDITABLE", $field_arr )) {
						$ActionArr = $grid_fields [$Actionkey];
						if ($ActionArr [5]->EDIT->url == "accounts/customer_edit/" || $ActionArr [5]->EDIT->url == "accounts/provider_edit/") {
							$ActionArr [5]->EDIT->url = $row ['type'] == 0 ? "accounts/customer_edit/" : "accounts/provider_edit/";
						}
						if ($ActionArr [5]->EDIT->url == "accounts/admin_edit/" || $ActionArr [5]->EDIT->url == "accounts/subadmin_edit/") {
							$ActionArr [5]->EDIT->url = $row ['type'] == 4 ? "accounts/subadmin_edit/" : "accounts/admin_edit/";
						}
						$jsn_tmp [$field_key] = $this->CI->{$this->lib_class}->build_custome_edit_button ( $ActionArr [5]->EDIT, $row [$field_arr [2]], $row ["id"] );
					} else {
						$jsn_tmp [$field_key] = isset ( $row [$field_arr [2]] ) ? $row [$field_arr [2]] : "";
					}
				} else {
					if ($field_arr [0] == "Action") {
						$jsn_tmp [$field_key] = $this->CI->{$this->lib_class}->get_action_buttons ( $field_arr [5], $row ["id"] );
					} else {
						$jsn_tmp [$field_key] = '<input type="checkbox" name="chkAll" id=' . $row ['id'] . ' class="ace chkRefNos" onclick="clickchkbox(' . $row ['id'] . ')" value=' . $row ['id'] . '><lable class="lbl"></lable>';
					}
				}
			}
			$json_data [] = array (
					'cell' => $jsn_tmp 
			);
		}
		return $json_data;
	}
	function build_seraching_form($fields_array) {
        $form_contents = '';
        $form_contents.= '<div>';
        $form_contents.= form_open($fields_array['forms'][0], $fields_array['forms'][1]);
        unset($fields_array['forms']);
        $button_array = array();
        if (isset($fields_array['button_search']) || isset($fields_array['button_reset'])) {
            $save = $fields_array['button_search'];
            unset($fields_array['button_search']);
            if ($fields_array['button_reset']) {
                $cancel = $fields_array['button_reset'];
                unset($fields_array['button_reset']);
            }
        }
        $i = 1;
        foreach ($fields_array as $fieldset_key => $form_fileds) {

            $form_contents.= '<ul class="padding-15">';
            $form_contents.= form_fieldset($fieldset_key);

            foreach ($form_fileds as $fieldkey => $fieldvalue) {
                if ($i == 0) {
                    $form_contents.= '<li class="col-md-12">';
                }
                $form_contents.= '<div class="col-md-4 no-padding">';
                if ($fieldvalue[1] == 'HIDDEN') {
                    $form_contents.= form_hidden($fieldvalue[2], $fieldvalue[3]);
                } else {
                    $form_contents.= form_label($fieldvalue[0], "", array("class" => "search_label col-md-12 no-padding"));
                }
                if ($fieldvalue[1] == 'INPUT') {
                    $form_contents.= form_input($fieldvalue[2]);
                }
                if ($fieldvalue[2] == 'SELECT' || $fieldvalue[5] == '1') {
                    if ($fieldvalue[7] != '' && $fieldvalue[8] != '') {
	                $accountinfo=$this->CI->session->userdata('accountinfo');	
                        $str = $fieldvalue[7] . "," . $fieldvalue[8];

                        $drp_array = call_user_func_array(array($this->CI->db_model, $fieldvalue[10]), array($str, $fieldvalue[9], $fieldvalue[11], $fieldvalue[12]));
			if($fieldvalue[4] == 'Account Search' && $accountinfo['type']=='-1'){

	                   $form_contents.=form_dropdown_all($fieldvalue[1], $drp_array,'' , '123');
			   $account_dropdown="<select name='accountid' class='col-md-5 form-control' id='account_dropdown' style='margin-left:5px;'>
<option value=''>-Select-</option></select>";
                           $form_contents.=$account_dropdown;

			}else if($fieldvalue[4] == 'Account Batch Download' && $accountinfo['type']=='-1'){

	                   $form_contents.=form_dropdown_all($fieldvalue[1], $drp_array,'' , '123');
			   $account_dropdown="<select name='accountid' class='col-md-5 form-control' id='account_dropdown_download' style='margin-left:5px;'>
<option value=''>-Select-</option></select>";
                           $form_contents.=$account_dropdown;

			}else if($fieldvalue[4] == 'Rate Group Search' && $accountinfo['type']=='-1'){
	                   $form_contents.=form_dropdown_all($fieldvalue[1], $drp_array,'' , '456');
			   $account_dropdown="<select name='pricelist_id' class='col-md-5 form-control' id='account_dropdown_rategroup' style='margin-left:5px;'>
<option value=''>-Select-</option></select>";
                           $form_contents.=$account_dropdown;

			} else{
                        $form_contents.=form_dropdown_all($fieldvalue[1], $drp_array, '');
			}
                    } else {

                        if ($fieldvalue[1] == 'INPUT') {
                            $fieldvalue[1] = $fieldvalue[6];
                        }
                        $drp_array = call_user_func_array(array($this->CI->common, $fieldvalue[10]), array($fieldvalue[9]));
                        $form_contents.=form_dropdown_all_search($fieldvalue[1], $drp_array, '');
                    }
                } else if ($fieldvalue[1] == 'PASSWORD') {
                    $form_contents.= form_password($fieldvalue[2]);
                } else if ($fieldvalue[2] == 'CHECKBOX') {
                    $form_contents.= form_checkbox($fieldvalue[1], $fieldvalue[3]['value'], $fieldvalue[3]['checked']);
                }
                $form_contents.= '</div>';
                if ($i % 5 == 0) {
                    $form_contents.= '</li>';
                    $i = 0;
                }
                $i++;
            }
        }
        $form_contents.= '<div class="col-md-12 margin-t-20 margin-b-20">';
        $form_contents.= form_button($cancel);
        $form_contents.= form_button($save);
        $form_contents.= '</ul>';        
        $form_contents.= '</div>';
        $form_contents.= form_fieldset_close();
        $form_contents.= form_close();
        $form_contents.= '</div>';

        return $form_contents;
    }
	/*Ending for activity*/
}
