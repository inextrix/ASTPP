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
class Permission {
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
		$this->CI->load->model ( "db_model" );
		$this->CI->load->library ( 'session' );
	}
	function get_module_access($user_type) 
	{
		$where = array (
				"userlevelid" => $user_type 
		);
		$modules_arr = $this->CI->db_model->getSelect ( "module_permissions", "userlevels", $where );
		$accountinfo = $this->CI->session->userdata('accountinfo');
		if ($modules_arr->num_rows () > 0) {
			$modules_arr = $modules_arr->result_array();
			$modules_arr = $modules_arr [0] ['module_permissions'];
			if($modules_arr!='')
			{
			$menu_arr = $this->CI->db_model->select ( "*", "menu_modules", "id IN ($modules_arr)", "priority", "asc", "", "", "" );
			}
			else
			{
			$menu_arr = $this->CI->db_model->select ( "*", "menu_modules", "", "priority", "asc", "", "", "");
			}
			$menu_list = array();
			$permited_modules = array ();
			$modules_seq_arr = array ();
			$modules_seq_arr = explode ( ",", $modules_arr );
			$label_arr = array ();
			if(!empty($menu_arr)){
			foreach ( $menu_arr->result_array () as $menu_key => $menu_value ) {
				if($accountinfo['posttoexternal'] == '1' && ($menu_value['menu_label']=='TopUp' || $menu_value['menu_label']=='Send Credit' || $menu_value['module_name']=='refill')){
					unset($menu_value);
				}
				if ( isset($menu_value) && !isset ( $label_arr [$menu_value ['menu_label']] ) && $menu_value ['menu_label'] != 'Configuration') {
					$label_arr [$menu_value ['menu_label']] = $menu_value ['menu_label'];
					$menu_value ["menu_image"] = ($menu_value ["menu_image"] == "") ? "Home.png" : $menu_value ["menu_image"];
					$menu_list [$menu_value ["menu_title"]] [$menu_value ["menu_subtitle"]] [] = array (
							"menu_label" => trim ( $menu_value ["menu_label"] ),
							"module_url" => trim ( $menu_value ["module_url"] ),
							"module" => trim ( $menu_value ["module_name"] ),
							"menu_image" => trim ( $menu_value ["menu_image"] ) 
					);
				}
			$permissioninfo = $this->CI->session->userdata('permissioninfo');
                        $module_url_explode=explode("/",$menu_value["module_url"]);
                        $main_module=$module_url_explode[0];
                        $sub_main_module=$module_url_explode[1];
			$sub_url_explode=explode('_',$sub_main_module);
                        $logintype = $this->CI->session->userdata('logintype');
                        if((isset($permissioninfo[$main_module][$sub_main_module]['list']) && astpp_eq0($permissioninfo[$main_module][$sub_main_module]['list']) or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3') or ($permissioninfo['login_type'] == '1' and $sub_main_module='resellersrates_list') or (isset($permissioninfo[$main_module][$sub_main_module]['list']) && astpp_eq0($permissioninfo[$main_module][$sub_main_module]['list']) and $permissioninfo['login_type'] == '2')){
                                $permited_modules[] = trim($sub_url_explode[0]);
                        }
                	}
			}
			$this->CI->session->set_userdata ( 'permited_modules', serialize ( $permited_modules ) );
			$this->CI->session->set_userdata ( 'menuinfo', serialize ( $menu_list ) );
			return true;
		}
	}
	private function _check_record_permission($table_name,$where_condition=array(),$select_field= 'id'){
		$this->CI->db->select($select_field);
		return (array)$this->CI->db->get_where($table_name,$where_condition)->first_row();
	}
	

	
	function check_web_record_permission($id,$table_name,$redirect_url,$check_function_access=false,$dependencies=array()){
		$accountinfo = $this->CI->session->userdata('accountinfo') ;
		$reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : 0 ;
		$permission_result =array();
		if(!empty($dependencies)){
			$table_row =$this->_check_record_permission($table_name,array('id'=>$id),$dependencies['field_name']);
			if(!empty($table_row)){
				$permission_result = $this->_check_record_permission($dependencies['parent_table'],array('id'=>$table_row[$dependencies['field_name']],'reseller_id'=>$reseller_id));	
			}
		}
		else if(!$check_function_access){
			$permission_result  = $this->_check_record_permission($table_name,array('id'=>$id,'reseller_id'=>$reseller_id));
		}
		if( astpp_gt($reseller_id, 0) && ((empty($permission_result) || $check_function_access) )){
				$this->permission_redirect_url($redirect_url);
		}	
	}
	
	function permission_redirect_url($redirect_url){
		$this->CI->session->set_flashdata ( 'astpp_notification','Permission Denied!');
		redirect(base_url().$redirect_url);
	}
	function customer_web_record_permission($id,$table,$redirect_url){
		$accountinfo = $this->CI->session->userdata('accountinfo');
		$query_result = $this->_check_record_permission($table,array("accountid"=>$accountinfo['id'],"id"=>$id));
		if(empty($query_result)){
			$this->permission_redirect_url($redirect_url);
		}
	}

	// ASTPPCOM-947
	function validate_multiple_delete_access($ids,$table,$redirect_url,$accountid="accountid",$subadmin_flag=false,$field_name="id",$parent_customer=""){
		$accountinfo = $this->CI->session->userdata('accountinfo');
		if($accountinfo['type'] != '-1' && $accountinfo['type'] != '2'){
			$this->CI->db->select($field_name);
			if($accountinfo['type'] == 1){
				$where = "`".$accountid."` = ".$accountinfo['id']." and ".$field_name." IN ($ids)";
			}else{
				if($parent_customer != ''){
					$where = "`".$accountid."` = ".$accountinfo['reseller_id']." and ".$field_name." IN ($ids)";
				}else{
					$where = "`".$accountid."` = ".$accountinfo['id']." and ".$field_name." IN ($ids)";
				}
			}
			$this->CI->db->where($where);
			$query = $this->CI->db->get($table);
			$new_ids='';
			if($query->num_rows() > 0){
				$result=$query->result_array();
				foreach ($result as $key => $value) {
					$new_ids .= "'".$value[$field_name]."',";
				}
				$new_ids=rtrim($new_ids,',');
			}
			return $new_ids;
		}else{
			return $ids;
		}
	}
	// END
}
?> 
