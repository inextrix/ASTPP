<?php
###############################################################################
# ASTPP - Open Source VoIP Billing Solution
#
# Copyright (C) 2016 Inextrix Technologies Pvt. Ltd.
# Samir Doshi <samir.doshi@inextrix.com>
# ASTPP Version 3.0 and above
# License https://www.gnu.org/licenses/agpl-3.0.html
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
# 
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
###############################################################################

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Department_form {
//harsh_10_01
      function build_department_list_for_admin() {        
            $action = 'department/department_list_edit/';
            $action_remove = 'department/department_remove/';
            $mode="single";
        
        $grid_field_arr = json_encode(array(
	    array("<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>", "40", "", "", "", "","","false","center"),
            array(gettext("Name"), "100", "name", "", "", "","EDITABLE","","true","center"),
	    array(gettext("Email Address"), "200", "email_id", "", "", "","","true","center"),
	    array(gettext("SMTP Host"), "140", "smtp_host", "", "", "","","true","center"),
	    array(gettext("SMTP Port"), "120", "smtp_port", "", "", "","","true","center"),
	    array(gettext("SMTP User"), "181", "smtp_user", "", "", "","","true","center"),
	   // array(gettext("SMTP Password"), "150", "smtp_password", "", "", "","","true","center"),
	array(
                    gettext("Status"),
                    "80",
                    "status",
                    "id",
                    "department",
                    "get_status",
                    "",
                    "true",
                    "center"
        ),
	    //array(gettext("Status"), "80", "status", "id", "department", "get_status","","true","center"),
               array(gettext("Action"), "90", "", "", "", array("EDIT" => array("url" => "$action", "mode" => "$mode"),
                    "DELETE" => array("url" => "$action_remove", "mode" => "single")
                ),"false")
                ));
        return $grid_field_arr;
     }
//~ change by bansi faldu
//~ issue:#89
//~ integer to string in name field
    function get_search_department_form() {
	
        $form['forms'] = array("", array('id' => "department_search"));
        $form['Search'] = array(
            
            array('Name', 'INPUT', array('name' => 'name[name]', '', 'size' => '20', 'class' => "text field "), '', 'tOOL TIP', '1', 'name[name-string]', '', '', '', 'search_string_type', ''),
            array('Email Address', 'INPUT', array('name' => 'email_id[email_id]', '', 'size' => '20', 'class' => "text field "), '', 'tOOL TIP', '1', 'email_id[email_id-string]', '', '', '', 'search_string_type', ''),
	   array(gettext('Status'), 'status', 'SELECT', '', '', 'tOOL TIP', 'Please Enter account number', '', '', '', 'set_search_status'),
            array('', 'HIDDEN', 'ajax_search', '1', '', '', ''),
            array('', 'HIDDEN', 'advance_search', '1', '', '', ''));

        $form['button_search'] = array('name' => 'action', 'id' => "department_search_btn", 'content' => 'Search', 'value' => 'save', 'type' => 'button', 'class' => 'btn btn-line-parrot pull-right');
        $form['button_reset'] = array('name' => 'action', 'id' => "id_reset", 'content' => 'Clear', 'value' => 'cancel', 'type' => 'reset', 'class' => 'btn btn-secondary float-right ml-2');

        return $form;
    }
//~ change by bansi faldu
//~ issue:#42
    function get_department_form_fields() {
     $account_val = 'department.name';
        
        $form['forms'] = array(base_url() . 'department/department_save/', array('id' => 'department_form', 'method' => 'POST', 'name' => 'department_form'));
        $form['Department List'] = array(
            array('', 'HIDDEN', array('name' => 'id'), '', '', '', ''),
            array('Name', 'INPUT', array('name' => 'name', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean|is_unique[' . $account_val . ']', 'tOOL TIP', 'Please Enter country'),
	   // array('Email Address', 'INPUT', array('name' => 'email_id', 'size' => '50',  'class' => "text field medium"), 'required|valid_email', 'tOOL TIP', ''),
          //  array('Password', 'INPUT', array('name' => 'password', 'size' => '50',  'class' => "text field medium"), 'required', 'tOOL TIP', ''),
            //~ array('Password', 'PASSWORD', array('name' => 'password', 'id' => 'password_show', 'onmouseover' => 'seetext(password_show)', 'onmouseout' => 'hidepassword(password_show)', 'size' => '50', 'class' => "text field medium"), 'required', 'tOOL TIP', ''),
  	   array('Status', 'status', 'SELECT', '', '', 'tOOL TIP', 'Please Enter account number', '', '', '', 'set_status'),
        );
//harsh_16_01
        $form[gettext('Department User')] = array(
	    array(gettext('Admin'), 'admin_user_id', 'SELECT', '','', 'tOOL TIP', 'Please Enter account number', 'id', 'first_name,last_name,number', 'accounts', 'build_concat_dropdown', 'where_arr', array("status" => "0", "deleted" => 0,'type'=>2,"id !="=>1),'multi'),
	    array(gettext('Sub Admin'), 'sub_admin_user_id', 'SELECT', '','', 'tOOL TIP', 'Please Enter account number', 'id', 'first_name,last_name,number', 'accounts', 'build_concat_dropdown', 'where_arr', array("status" => "0", "deleted" => 0,"Type"=>4),'multi'),
        );
//harsh_10_01
        $form[gettext('SMTP Details')] = array(
            array('SMTP Host', 'INPUT', array('name' => 'smtp_host', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
            array('SMTP Port', 'INPUT', array('name' => 'smtp_port', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
            array('SMTP User', 'INPUT', array('name' => 'smtp_user', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
            //array('SMTP Password', 'INPUT', array('name' => 'smtp_password', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
            array('SMTP Password', 'PASSWORD', array('name' => 'smtp_password', 'id' => 'smtp_password_show', 'onmouseover' => 'seetext(smtp_password_show)', 'onmouseout' => 'hidepassword(smtp_password_show)', 'size' => '20','maxlength' => '40', 'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', ''),
        );
//harsh_16_01
        $form[gettext('Department Additional Email Address')] = array( 
        	    array('Email 1', 'INPUT', array('name' => 'email_id_new1', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),
        	    array('Email 2', 'INPUT', array('name' => 'email_id_new2', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),
        	    array('Email 3', 'INPUT', array('name' => 'email_id_new3', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),
        	    array('Email 4', 'INPUT', array('name' => 'email_id_new4', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),
        	    array('Email 5', 'INPUT', array('name' => 'email_id_new5', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),

        
               );
     //   $form['Department Additional Email Address'] = array(
	         //~ for($i = 1; $i <= 5; $i++){
        	         //~ $form['Department Additional Email Address'][] = array('Email ', 'INPUT', array('name' => 'email_id_new['.$i.']', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', '');
	        //~ }    
 //       );
        $form['button_save'] = array('name' => 'action', 'content' => 'Save', 'value' => 'save', 'id' => 'submit', 'type' => 'submit', 'class' => 'btn btn-success');
        $form['button_cancel'] = array('name' => 'action', 'content' => 'Close', 'value' => 'cancel', 'type' => 'button', 'class' => 'btn btn-info ml-2 ', 'onclick' => 'return redirect_page(\'/department/department_list/\')');
        return $form;
    }
    function get_department_form_fields_edit() {
      
        
        $form['forms'] = array(base_url() . 'department/department_save/', array('id' => 'department_form', 'method' => 'POST', 'name' => 'department_form'));
        $form[gettext('Department List')] = array(
            array('', 'HIDDEN', array('name' => 'id'), '', '', '', ''),
            array('Name', 'INPUT', array('name' => 'name', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
	    //array('Email Address', 'INPUT', array('name' => 'email_id', 'size' => '50',  'class' => "text field medium"), 'required|valid_email', 'tOOL TIP', ''),
          //  array('Password', 'INPUT', array('name' => 'password', 'size' => '50',  'class' => "text field medium"), 'required', 'tOOL TIP', ''),
            //~ array('Password', 'PASSWORD', array('name' => 'password', 'id' => 'password_show', 'onmouseover' => 'seetext(password_show)', 'onmouseout' => 'hidepassword(password_show)', 'size' => '50', 'class' => "text field medium"), 'required', 'tOOL TIP', ''),
  	   array('Status', 'status', 'SELECT', '', '', 'tOOL TIP', 'Please Enter account number', '', '', '', 'set_status'),
        );
//harsh_16_01
        $form[gettext('Department User')] = array(
	    array(gettext('Admin'), 'admin_user_id', 'SELECT', '','', 'tOOL TIP', 'Please Enter account number', 'id', 'first_name,last_name,number', 'accounts', 'build_concat_dropdown', 'where_arr', array("status" => "0", "deleted" => 0,'type'=>2),'multi'),
	   // array(gettext('Sub Admin'), 'sub_admin_user_id', 'SELECT', '','', 'tOOL TIP', 'Please Enter account number', 'id', 'first_name,last_name,number', 'accounts', 'build_concat_dropdown', 'where_arr', array("status" => "0", "deleted" => 0,"Type"=>4),'multi'),
        );
//harsh_10_01
        $form[gettext('SMTP Details')] = array(
            array('SMTP Host', 'INPUT', array('name' => 'smtp_host', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
            array('SMTP Port', 'INPUT', array('name' => 'smtp_port', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
            array('SMTP User', 'INPUT', array('name' => 'smtp_user', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
            //array('SMTP Password', 'INPUT', array('name' => 'smtp_password', 'size' => '20','maxlength' => '40',  'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter country'),
            array('SMTP Password', 'PASSWORD', array('name' => 'smtp_password', 'id' => 'smtp_password_show', 'onmouseover' => 'seetext(smtp_password_show)', 'onmouseout' => 'hidepassword(smtp_password_show)', 'size' => '20','maxlength' => '40', 'class' => "text field medium"), 'trim|required|xss_clean', 'tOOL TIP', ''),
        );
//harsh_16_01
        $form[gettext('Department Additional Email Address')] = array( 
        	    array('Email 1', 'INPUT', array('name' => 'email_id_new1', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),
        	    array('Email 2', 'INPUT', array('name' => 'email_id_new2', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),
        	    array('Email 3', 'INPUT', array('name' => 'email_id_new3', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),
        	    array('Email 4', 'INPUT', array('name' => 'email_id_new4', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),
        	    array('Email 5', 'INPUT', array('name' => 'email_id_new5', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', ''),

        
               );
     //   $form['Department Additional Email Address'] = array(
	         //~ for($i = 1; $i <= 5; $i++){
        	         //~ $form['Department Additional Email Address'][] = array('Email ', 'INPUT', array('name' => 'email_id_new['.$i.']', 'size' => '50',  'class' => "text field medium"), 'valid_email', 'tOOL TIP', '');
	        //~ }    
 //       );
        $form['button_save'] = array('name' => 'action', 'content' => 'Save', 'value' => 'save', 'id' => 'submit', 'type' => 'submit', 'class' => 'btn btn-success');
        $form['button_cancel'] = array('name' => 'action', 'content' => 'Close', 'value' => 'cancel', 'type' => 'button', 'class' => 'btn btn-info ml-2 ', 'onclick' => 'return redirect_page(\'/department/department_list/\')');
        return $form;
    }

     function build_admin_department_grid_buttons() {
        $buttons_json = json_encode(array(array(gettext("Create"),"btn btn-line-warning","fa fa-plus-circle fa-lg", "button_action", "department/department_add/", "single","medium","create"),
            array(gettext("Delete"),"btn btn-line-danger","fa fa-times-circle fa-lg", "button_action", "department/department_delete_multiple","","","delete"),
           ));
        return $buttons_json;
    }
    
}

?>
