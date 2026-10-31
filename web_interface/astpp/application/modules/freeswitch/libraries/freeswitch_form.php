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
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Freeswitch_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function get_freeswith_form_fields($id = false, $reseller_id = '')
    {
        $log_type = $this->CI->session->userdata("logintype");
        if ($log_type == 0 || $log_type == 3 || $log_type == 1) {
            $sip_pro = null;
            $account_type = null;
            $account_Arr = null;
        } else {
            $sip_pro = array(
                gettext('SIP Profile'),
                'sip_profile_id',
                'SELECT',
                '',
                'trim|dropdown|xss_clean',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'sip_profiles',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
            );
            if ($id > 0) {
                $account_type = array(
                    gettext('Reseller'),
                    'INPUT',
                    array(
                        'name' => 'reseller_id',
                        'readonly' => 'true',
                        'size' => '20',
                        'maxlength' => '15',
                        'class' => "text field medium"
                    ),
                    '',
                    'tOOL TIP',
                    'Please Enter account number'
                );
            } else {
                $account_type = array(
                    gettext('Account Type'),
                    array(
                        'name' => 'accounttype',
                        'id' => 'accounttype',
                        'class' => '',
                        'onchange' => 'account_change_add(this.value)'
                    ),
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Select Status',
                    '',
                    '',
                    '',
                    'set_accounttype'
                );
            }
            $account_Arr = array(
                gettext('Account'),
                array(
                    'name' => 'accountcode',
                    'class' => '',
                    'id' => 'accountcode'
                ),
                'SELECT',
                '',
                'trim|dropdown|xss_clean',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'first_name,last_name,number',
                'accounts',
                'build_concat_dropdown',
                'where_arr',
                array(
                    'reseller_id' => $reseller_id,
                    'deleted'=>0,
                    'status'=>0,
                    'type'=>0
                )
            );
        }
        $val = $id > 0 ? 'sip_devices.username.' . $id : 'sip_devices.username';
        $uname_user = $this->CI->common->find_uniq_rendno('10', '', '');
        $digits = 5;
        $random_password = random_int((int) pow(10, $digits - 1), (int) pow(10, $digits) - 1);
        $password = $this->CI->common->generate_password();
        if ($id > 0) {
            $password_field = array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'fs_password',
                    'size' => '20',
                    'id' => 'password1',
                    'value' => $password,
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean|chk_password_expression',
                'tOOL TIP',
                'Please Enter Password',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_pass align-self-end text-success fa fa-refresh" ></i>'
            );
            $user_name = array(
                gettext('Username'),
                'INPUT',
                array(
                    'name' => 'fs_username',
                    'size' => '20',
                    'value' => $uname_user,
                    'id' => 'username',
                    'class' => "text field medium",
                    'readonly' => true
                ),
                'trim|required|is_unique[' . $val . ']|xss_clean',
                'tOOL TIP',
                'Please Enter account number',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_number align-self-end text-success fa fa-refresh" ></i>'
            );
        } else {
            $password_field = array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'fs_password',
                    'size' => '20',
                    'id' => 'password1',
                    'value' => $password,
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean|chk_password_expression',
                'tOOL TIP',
                'Please Enter Password',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_pass align-self-end text-success fa fa-refresh" ></i>'
            );
            $user_name = array(
                gettext('Username'),
                'INPUT',
                array(
                    'name' => 'fs_username',
                    'size' => '20',
                    'value' => $uname_user,
                    'id' => 'username',
                    'class' => "text field medium"
                ),
                'trim|required|is_unique[' . $val . ']|xss_clean',
                'tOOL TIP',
                'Please Enter account number',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_number align-self-end text-success fa fa-refresh" ></i>'
            );
        }
        $form['forms'] = array(
            base_url() . 'freeswitch/fssipdevices_save/',
            array(
                "id" => "sipdevices_form",
                "name" => "sipdevices_form"
            )
        );
        $form[gettext('Device Information')] = array(
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'id'
                ),
                '',
                '',
                '',
                ''
            ),
            $user_name,
            $password_field,
            $account_type,
            $account_Arr,
            array(
                gettext('Caller Name'),
                'INPUT',
                array(
                    'name' => 'effective_caller_id_name',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'xss_clean',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Caller Number'),
                'INPUT',
                array(
                    'name' => 'effective_caller_id_number',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'xss_clean',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Status'),
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
            ),
            //  ASTPPCOM-945 Jaimin Start
             array(
                gettext('Codec'),
                'INPUT',
                array(
                    'name' => 'codec',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'xss_clean',
                'tOOL TIP',
                'Please Enter account number'
            ),
            // End
            $sip_pro
        );
        $form[gettext('Voicemail Options')] = array(
            array(
                gettext('Enable'),
                'voicemail_enabled',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_sip_config_option'
            ),

            array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'voicemail_password',
                    'size' => '20',
                    'value' => $random_password,
                    'id' => 'random_password',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                'Please Enter Password',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_password align-self-end text-success fa fa-refresh"></i>'
            ),
            array(
                gettext('Mail To'),
                'INPUT',
                array(
                    'name' => 'voicemail_mail_to',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),

            array(
                gettext('Attach File'),
                'voicemail_attach_file',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status_voicemail'
            ),

            array(
                gettext('Local After Email'),
                'vm_keep_local_after_email',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status_voicemail'
            ),

            array(
                gettext('Send all Message'),
                'vm_send_all_message',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status_voicemail'
            )
        );

        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        return $form;
    }

    function get_freeswith_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "freeswith_search"
            )
        );
        $form[gettext('Search')] = array(

            array(
                gettext('SIP Profile'),
                'sip_profile_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'sip_profiles',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
            ),
            array(
                gettext('Username'),
                'INPUT',
                array(
                    'name' => 'username[username]',
                    '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '1',
                'username[username-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Gateway'),
                'gateway_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please select gateway first',
                'id',
                'name',
                'gateways',
                'build_dropdown',
                'where_arr',
                ''
            ),
            array(
                gettext('Account'),
                'accountid',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'first_name,last_name,number',
                'accounts',
                'build_concat_dropdown',
                'where_arr',
                array(
                    "reseller_id" => "0",
                    "type" => "0",
                    "deleted" => "0"
                )
            ),
            array(
                gettext('Status'),
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
            array(
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
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "freeswith_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );
        return $form;
    }

    function get_sipdevices_search_form_user()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "sipdevices_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Username'),
                'INPUT',
                array(
                    'name' => 'username[username]',
                    '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '1',
                'username[username-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('SIP Profile'),
                'sip_profile_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'sip_profiles',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
            ),
            array(
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
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "sipdevices_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );
        return $form;
    }

    function get_gateway_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "freeswith_search"
            )
        );
        $form[gettext('Search')] = array(

            array(
                gettext('Name'),
                'INPUT',
                array(
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
                gettext('SIP Profile'),
                'sip_profile_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'sip_profiles',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
            ),

            array(
                gettext('Status'),
                'status',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_search_status'
            ),
            array(
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
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "fsgateway_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );
        return $form;
    }

    function get_sipprofile_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "freeswitch_search"
            )
        );
        $form[gettext('Search')] = array(

            array(
                gettext('Name'),
                'INPUT',
                array(
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
                gettext('SIP IP'),
                'INPUT',
                array(
                    'name' => 'sip_ip[sip_ip]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'sip_ip[sip_ip-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('SIP Port'),
                'INPUT',
                array(
                    'name' => 'sip_port[sip_port]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'sip_port[sip_port-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),

            array(
                gettext('Status'),
                'status',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_search_status'
            ),
            array(
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
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "fssipprofile_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );
        return $form;
    }

    function get_sipdevice_search_form()
    {
        $log_type = $this->CI->session->userdata("logintype");
        $accountinfo = $this->CI->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $form['forms'] = array(
            "",
            array(
                'id' => "freeswith_search"
            )
        );

        if (($log_type == - 1) || ($log_type == 2)) {
            $form[gettext('Search')] = array(

                array(
                    gettext('Username'),
                    'INPUT',
                    array(
                        'name' => 'username[username]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'username[username-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Mail To'),
                    'INPUT',
                    array(
                        'name' => 'dir_params[dir_params]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'dir_params[dir_params-string]',
                    '',
                    '',
                    '',
                    // Ashish ASTPPCOM-751
                    'search_mail_to_type',
                    // Ashish End
                    ''
                ),
                array(
                    gettext('SIP Profile'),
                    'sip_profile_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'sip_profiles',
                    'build_dropdown',
                    'where_arr',
                    array(
                        "status" => "0"
                    )
                ),
                array(
                    gettext('Reseller'),
                    array(
                        'name' => 'reseller_id',
                        'class' => 'reseller_id_search_drp'
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
                    'where_arr',
                    ''
                ),
                array(
                    gettext('Account'),
                    array(
                        'name' => 'accountid',
                        'id' => 'accountid_search_drp',
                        'class' => 'accountid_search_drp'
                    ),
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'first_name,last_name,number',
                    'accounts',
                    'build_concat_dropdown',
                    'where_arr',
                    array(
                        "deleted" => "0"
                    )
                ),

                array(
                    gettext('Status'),
                    'status',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_search_status'
                ),
                array(
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
        } else {
            $form[gettext('Search')] = array(

                array(
                    gettext('Username'),
                    'INPUT',
                    array(
                        'name' => 'username[username]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'username[username-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('SIP Profile'),
                    'sip_profile_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'sip_profiles',
                    'build_dropdown',
                    'where_arr',
                    array(
                        "status" => "0"
                    )
                ),
                array(
                    gettext('Account'),
                    'accountid',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'first_name,last_name,number',
                    'accounts',
                    'build_concat_dropdown',
                    'where_arr',
                    array(
                        "deleted" => "0"
                    )
                ),
                array(
                    gettext('Status'),
                    'status',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_search_status'
                ),

                array(
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
        }
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "fssipdevice_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );
        return $form;
    }

    function build_system_list_for_admin()
    {
        // Jaimin ASTPPCOM-984
            $status = $this->CI->db_model->countQuery("*", "addons", array(
                "package_name" => "siprouting"
            ));
            if(isset($status) && $status == 1 ){
                $action_array = array (
                    gettext ( "Advance" ),
                    "50",
                    "",
                    "",
                    "",
                    array (
                        "EDIT" => array (
                        "url" => "/siprouting/siprouting_edit/",
                        "mode" => "single",
                        "layout" => "medium"
                    )
                ),
                "false"
                );
            }else{
                $action_array = array();
            }
        // End
        $checkbox = array(
            "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
            "30",
            "",
            "",
            "",
            "",
            "",
            "false",
            "center"
        );
        $account_data = $this->CI->session->userdata("accountinfo");
        if ($account_data['type'] == 1) {

            $grid_field_arr = json_encode(array(

                $checkbox,
                array(
                    gettext("Username"),
                    "80",
                    "username",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("Password"),
                    "110",
                    "password",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("SIP Profile"),
                    "70",
                    "sip_profile_id",
                    "name",
                    "sip_profiles",
                    "get_field_name",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Account"),
                    "90",
                    "accountid",
                    "first_name,last_name,number",
                    "accounts",
                    "build_concat_string",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Caller Name"),
                    "80",
                    "effective_caller_id_name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Caller Number"),
                    "100",
                    "effective_caller_id_number",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Voicemail"),
                    "60",
                    "voicemail_enabled",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Created Date"),
                    "80",
                    "creation_date",
                    "creation_date",
                    "creation_date",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Modified Date"),
                    "80",
                    "last_modified_date",
                    "last_modified_date",
                    "last_modified_date",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Status"),
                    "50",
                    "status",
                    "status",
                    "sip_devies",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                // Jaimin ASTPPCOM-984
                 $action_array,
                // End
                array(
                    gettext("Action"),
                    "107",
                    "",
                    "",
                    "",
                    array(
                        "EDIT" => array(
                            "url" => "/freeswitch/fssipdevices_edit/",
                            "mode" => "single",
                            "layout" => "medium"
                        ),
                        "DELETE" => array(
                            "url" => "/freeswitch/fssipdevices_delete/",
                            "mode" => "single"
                        )
                    ),
                    "false"
                )
            ));
        } else {
            $grid_field_arr = json_encode(array(
                $checkbox,
                array(
                    gettext("Username"),
                    "80",
                    "username",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("Password"),
                    "110",
                    "password",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("SIP Profile"),
                    "70",
                    "sip_profile_id",
                    "name",
                    "sip_profiles",
                    "get_field_name",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Account"),
                    "90",
                    "accountid",
                    "first_name,last_name,number",
                    "accounts",
                    "build_concat_string",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Caller Name"),
                    "80",
                    "effective_caller_id_name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Caller Number"),
                    "100",
                    "effective_caller_id_number",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Voicemail"),
                    "60",
                    "voicemail_enabled",
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
                    "reseller_select_value"
                ),
                array(
                    gettext("Created Date"),
                    "80",
                    "creation_date",
                    "creation_date",
                    "creation_date",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Modified Date"),
                    "80",
                    "last_modified_date",
                    "last_modified_date",
                    "last_modified_date",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Status"),
                    "50",
                    "status",
                    "status",
                    "sip_devies",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                // Jaimin ASTPPCOM-984
                 $action_array,
                // End
                array(
                    gettext("Action"),
                    "107",
                    "",
                    "",
                    "",
                    array(
                        "EDIT" => array(
                            "url" => "/freeswitch/fssipdevices_edit/",
                            "mode" => "single",
                            "layout" => "medium"
                        ),
                        "DELETE" => array(
                            "url" => "/freeswitch/fssipdevices_delete/",
                            "mode" => "single"
                        )
                    ),
                    "false"
                )
            ));
        }
        return $grid_field_arr;
    }

    function build_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/freeswitch/fssipdevices_add/",
                "popup",
                "medium",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/freeswitch/fssipdevices_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function fsdevices_build_grid_buttons($accountid)
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/freeswitch/customer_fssipdevices_add/$accountid/",
                "popup",
                "medium",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/freeswitch/customer_fssipdevices_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function get_gateway_form_fields()
    {
        $form['forms'] = array(
            base_url() . 'freeswitch/fsgateway_save/',
            array(
                "id" => "gateway_form",
                "name" => "gateway_form"
            )
        );
        $form[gettext('Basic Information')] = array(
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'id'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'name',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter Gateway Name'
            ),
            array(
                gettext('SIP Profile'),
                'sip_profile_id',
                'SELECT',
                '',
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter SIP Profile',
                'id',
                'name',
                'sip_profiles',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
            ),
            array(
                gettext('Username'),
                'INPUT',
                array(
                    'name' => 'username',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter user name'
            ),
            array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'password',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter Password'
            ),
            array(
                gettext('Proxy'),
                'INPUT',
                array(
                    'name' => 'proxy',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Outbound-Proxy'),
                'INPUT',
                array(
                    'name' => 'outbound-proxy',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Register'),
                array(
                    'name' => 'register',
                    'class' => 'add_settings'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_options'
            ),
            array(
                gettext('Caller-Id-In-From'),
                array(
                    'name' => 'caller-id-in-from',
                    'class' => 'add_settings'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('Status'),
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
        $form[gettext('Optional Information')] = array(
            array(
                gettext('From- Domain'),
                'INPUT',
                array(
                    'name' => 'from-domain',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('From User'),
                'INPUT',
                array(
                    'name' => 'from-user',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Realm'),
                'INPUT',
                array(
                    'name' => 'realm',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Extension-In-Contact'),
                array(
                    'name' => 'extension-in-contact',
                    'class' => 'add_settings'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_option_default_false'
            ),
            array(
                gettext('Extension'),
                'INPUT',
                array(
                    'name' => 'extension',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Expire Seconds'),
                'INPUT',
                array(
                    'name' => 'expire-seconds',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Reg-Transport'),
                'INPUT',
                array(
                    'name' => 'register-transport',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Contact Params'),
                'INPUT',
                array(
                    'name' => 'contact-params',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Ping'),
                'INPUT',
                array(
                    'name' => 'ping',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Retry-Seconds'),
                'INPUT',
                array(
                    'name' => 'retry-seconds',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Register-Proxy'),
                'INPUT',
                array(
                    'name' => 'register-proxy',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Dialplan Variable'),
                'TEXTAREA',
                array(
                    'name' => 'dialplan_variable',
                    'size' => '0',
                    'rows' => '1',
                    'class' => "form-control form-control-lg mit-20"
                ),
                '',
                'tOOL TIP',
                ''
            )
        );

        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );

        return $form;
    }

    function build_fsgateway_list_for_admin()
    {
        $grid_field_arr = json_encode(array(
            array(
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
            array(
                gettext("Name"),
                "120",
                "name",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("SIP Profile"),
                "110",
                "sip_profile_id",
                "name",
                "sip_profiles",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Username"),
                "110",
                "username",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Proxy"),
                "130",
                "proxy",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Register"),
                "100",
                "register",
                "register",
                "register",
                "convert_to_ucfirst",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Caller-Id-In-From"),
                "100",
                "caller-id-in-from",
                "caller-id-in-from",
                "caller-id-in-from",
                "convert_to_ucfirst",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Created Date"),
                "120",
                "created_date",
                "creation_date",
                "creation_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Modified Date"),
                "130",
                "last_modified_date",
                "last_modified_date",
                "last_modified_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Status"),
                "70",
                "status",
                "status",
                "gateways",
                "get_status",
                "",
                "true",
                "center"
            ),
            array(
                "Action",
                "106",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "/freeswitch/fsgateway_edit/",
                        "mode" => "popup",
                        "layout" => "medium"
                    ),
                    "DELETE" => array(
                        "url" => "/freeswitch/fsgateway_delete/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_fdgateway_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/freeswitch/fsgateway_add/",
                "popup",
                "medium",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/freeswitch/fsgateway_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function get_sipprofile_form_fields()
    {
        $form['forms'] = array(
            base_url() . 'freeswitch/fssipprofile_save/',
            array(
                "id" => "fssipprofile_form",
                "name" => "fssipprofile_form"
            )
        );
        $form[gettext('Basic Information')] = array(
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'id'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('Profile name'),
                'INPUT',
                array(
                    'name' => 'name',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter Name'
            ),
            array(
                gettext('sip-ip'),
                'INPUT',
                array(
                    'name' => 'sip_ip',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter SIP IP Name'
            ),
            array(
                gettext('sip-port'),
                'INPUT',
                array(
                    'name' => 'sip_port',
                    'size' => '20',
                    'value' => '5060',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter SIP Port'
            ),
            array(
                gettext('rtp-ip'),
                'INPUT',
                array(
                    'name' => 'rtp_ip',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Dial Plan'),
                'INPUT',
                array(
                    'name' => 'dialplan',
                    'size' => '20',
                    'value' => 'XML',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('user-agent-string'),
                'INPUT',
                array(
                    'name' => 'user-agent-string',
                    'size' => '20',
                    'value' => 'ASTPP',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('debug'),
                'INPUT',
                array(
                    'name' => 'debug',
                    'size' => '20',
                    'value' => '0',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('sip-trace'),
                'sip-trace',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_drp_option'
            ),
            array(
                gettext('tls'),
                'tls',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_options'
            ),
            array(
                gettext('inbound-reg-force-matching-username'),
                'inbound-reg-force-matching-username',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('disable-transcoding'),
                'disable-transcoding',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('all-reg-options-ping'),
                'all-reg-options-ping',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('unregister-on-options-fail'),
                'unregister-on-options-fail',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('log-auth-failures'),
                'log-auth-failures',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('Status'),
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

        $form[gettext('Others Information')] = array(
            array(
                gettext('inbound-bypass-media'),
                'inbound-bypass-media',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_options'
            ),
            array(
                gettext('inbound-proxy-media'),
                'inbound-proxy-media',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_options'
            ),
            array(
                gettext('disable-transfer'),
                'disable-transfer',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('enable-100rel'),
                'enable-100rel',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_options'
            ),
            array(
                gettext('rtp-timeout-sec'),
                'INPUT',
                array(
                    'name' => 'rtp-timeout-sec',
                    'size' => '20',
                    'value' => '60',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('dtmf-duration'),
                'INPUT',
                array(
                    'name' => 'dtmf-duration',
                    'size' => '20',
                    'value' => '2000',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('manual-redirect'),
                'manual-redirect',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_options'
            ),
            array(
                gettext('aggressive-nat-detection'),
                'aggressive-nat-detection',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('enable-Timer'),
                'enable-timer',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_options'
            ),
            array(
                gettext('minimum-session-expires'),
                'INPUT',
                array(
                    'name' => 'minimum-session-expires',
                    'value' => '120',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('session-timeout'),
                'INPUT',
                array(
                    'name' => 'session-timeout-pt',
                    'value' => '1800',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('auth-calls'),
                'auth-calls',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('apply-inbound-acl'),
                'INPUT',
                array(
                    'name' => 'apply-inbound-acl',
                    'value' => 'default',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('inbound-codec-prefs'),
                'INPUT',
                array(
                    'name' => 'inbound-codec-prefs',
                    'size' => '25',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('outbound-codec-prefs'),
                'INPUT',
                array(
                    'name' => 'outbound-codec-prefs',
                    'size' => '25',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('inbound-late-negotiation'),
                'inbound-late-negotiation',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_sip_config_options'
            ),
            array(
                gettext('inbound-codec-negotiation'),
                'INPUT',
                array(
                    'name' => 'inbound-codec-negotiation',
                    'size' => '25',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            )
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        $form['button_save'] = array(
            'content' => gettext('Save'),
            'value' => 'save',
            'type' => 'button',
            'id' => 'submit',
            'class' => 'btn btn-success'
        );

        return $form;
    }

    function build_fssipprofile_list_for_admin()
    {
        $grid_field_arr = json_encode(array(
            array(
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
            array(
                gettext("Name"),
                "190",
                "name",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("SIP IP"),
                "205",
                "sip_ip",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("SIP Port"),
                "200",
                "sip_port",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Profile Action"),
                "282",
                "",
                "",
                "",
                array(
                    "START" => array(
                        "url" => "/freeswitch/fssipprofile_action/start/",
                        "mode" => "single"
                    ),
                    "STOP" => array(
                        "url" => "/freeswitch/fssipprofile_action/stop/",
                        "mode" => "single"
                    ),
                    "RELOAD" => array(
                        "url" => "/freeswitch/fssipprofile_action/reload/",
                        "mode" => "single"
                    ),
                    "RESCAN" => array(
                        "url" => "/freeswitch/fssipprofile_action/rescan/",
                        "mode" => "single"
                    )
                )
            ),
            array(
                gettext("Status"),
                "160",
                "status",
                "status",
                "sip_profiles",
                "get_status",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Action"),
                "202",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "/freeswitch/fssipprofile_edit/",
                        "mode" => ""
                    ),
                    "DELETE" => array(
                        "url" => "/freeswitch/fssipprofile_delete/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_fssipprofile_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/freeswitch/fssipprofile_add/",
                "",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/freeswitch/fssipprofile_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function build_fssipprofile_params_list_for_admin()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Name"),
                "450",
                "name",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("VALUE"),
                "414",
                "sip_ip",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Action"),
                "400",
                "",
                "",
                "",
                array(
                    "DELETE" => array(
                        "url" => "/freeswitch/fssipprofile_delete/",
                        "mode" => "single"
                    )
                )
            )
        ));
        return $grid_field_arr;
    }




    function build_devices_list_for_customer()
    {
         // Jaimin ASTPPCOM-984
            $status = $this->CI->db_model->countQuery("*", "addons", array(
                "package_name" => "siprouting"
            ));
            if(isset($status) && $status == 1){
                    $action_array = array (
                        gettext ( "Advance" ),
                        "60",
                        "",
                        "",
                        "",
                        array (
                            "EDIT" => array (
                            "url" => "/siprouting/siprouting_edit/",
                            "mode" => "single",
                            "layout" => "medium"
                        )
                    ),
                    "false"
                    );
                }else{
                    $action_array = array();
                }
        // End
        $grid_field_arr = json_encode(array(
            array(
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
            array(
                gettext("User name"),
                "80",
                "username",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("Password"),
                "150",
                "password",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("SIP Profile"),
                "70",
                "sip_profile_id",
                "name",
                "sip_profiles",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Caller Name"),
                "100",
                "effective_caller_id_name",
                "",
                "",
                "",
                "",
                "false",
                "right"
            ),
            array(
                gettext("Caller Number"),
                "100",
                "effective_caller_id_number",
                "",
                "",
                "",
                "",
                "false",
                "right"
            ),
            array(
                gettext("Voicemail"),
                "70",
                "voicemail_enabled",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Created Date"),
                "120",
                "creation_date",
                "creation_date",
                "creation_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Modified Date"),
                "120",
                "last_modified_date",
                "last_modified_date",
                "last_modified_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Status"),
                "60",
                "status",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),

            // Jaimin ASTPPCOM-984
                $action_array,
            // End

            array(
                gettext("Action"),
                "110",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "/accounts/fssipdevices_action/edit/",
                        "mode" => "single"
                    ),
                    "DELETE" => array(
                        "url" => "/accounts/fssipdevices_action/delete/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function fsdevice_form_fields_for_customer($accountid, $id = false)
    {
        $val = $id > 0 ? 'sip_devices.username.' . $id : 'sip_devices.username';
        $uname_user = $this->CI->common->find_uniq_rendno('10', '', '');
        $digits = 5;
        $random_password = random_int((int) pow(10, $digits - 1), (int) pow(10, $digits) - 1);
        $password = $this->CI->common->generate_password();
        if ($id > 0) {
            $password_field = array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'fs_password',
                    'size' => '20',
                    'id' => 'password1',
                    'value' => $password,
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean|chk_password_expression',
                'tOOL TIP',
                'Please Enter Password',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_pass align-self-end text-success fa fa-refresh" ></i>'
            );
            $user_name = array(
                gettext('Username'),
                'INPUT',
                array(
                    'name' => 'fs_username',
                    'size' => '20',
                    'value' => $uname_user,
                    'id' => 'username',
                    'class' => "text field medium",
                    'readonly' => true
                ),
                'trim|required|is_unique[' . $val . ']|xss_clean',
                'tOOL TIP',
                'Please Enter account number',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_number align-self-end text-success fa fa-refresh" ></i>'
            );
        } else {
            $password_field = array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'fs_password',
                    'size' => '20',
                    'id' => 'password1',
                    'value' => $password,
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean|chk_password_expression',
                'tOOL TIP',
                'Please Enter Password',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_pass align-self-end text-success fa fa-refresh" ></i>'
            );
            $user_name = array(
                gettext('Username'),
                'INPUT',
                array(
                    'name' => 'fs_username',
                    'size' => '20',
                    'value' => $uname_user,
                    'id' => 'username',
                    'class' => "text field medium"
                ),
                'trim|required|is_unique[' . $val . ']|xss_clean',
                'tOOL TIP',
                'Please Enter account number',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_number align-self-end text-success fa fa-refresh" ></i>'
            );
        }
        if ($this->CI->session->userdata("logintype") == '0' || $this->CI->session->userdata("logintype") == '3') {
            $link = base_url() . 'freeswitch/user_fssipdevices_save/true';
            $form['forms'] = array(
                $link,
                array(
                    "id" => "sipdevices_form",
                    "name" => "sipdevices_form"
                )
            );
            $form[gettext('Device Information')] = array(
                array(
                    '',
                    'HIDDEN',
                    array(
                        'name' => 'id'
                    ),
                    '',
                    '',
                    '',
                    ''
                ),
                array(
                    '',
                    'HIDDEN',
                    array(
                        'name' => 'accountcode',
                        'value' => $accountid
                    ),
                    '',
                    '',
                    '',
                    ''
                ),
                $user_name,
                $password_field,
                array(
                    gettext('Caller Name'),
                    'INPUT',
                    array(
                        'name' => 'effective_caller_id_name',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Caller Number'),
                    'INPUT',
                    array(
                        'name' => 'effective_caller_id_number',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Status'),
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
            $form[gettext('Voicemail Options')] = array(

                array(
                    gettext('Enable'),
                    'voicemail_enabled',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_sip_config_option'
                ),

                array(
                    gettext('Password'),
                    'INPUT',
                    array(
                        'name' => 'voicemail_password',
                        'size' => '20',
                        'value' => $random_password,
                        'id' => 'random_password',
                        'class' => "text field medium"
                    ),
                    'trim|xss_clean',
                    'tOOL TIP',
                    'Please Enter Password',
                    '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_password align-self-end text-success fa fa-refresh"></i>'
                ),
                array(
                    gettext('Mail To'),
                    'INPUT',
                    array(
                        'name' => 'voicemail_mail_to',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    '',
                    'tOOL TIP',
                    'Please Enter account number'
                ),

                array(
                    gettext('Attach File'),
                    'voicemail_attach_file',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'custom_status_voicemail'
                ),
                array(
                    gettext('Local After Email'),
                    'vm_keep_local_after_email',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'custom_status_voicemail'
                ),

                array(
                    gettext('Send all Message'),
                    'vm_send_all_message',
                    'CHECKBOX',
                    array(
                        'name' => 'vm_send_all_message',
                        'value' => 'on',
                        'checked' => false
                    ),
                    '',
                    'tOOL TIP',
                    'Please Select Status',
                    'custom_status_voicemail',
                    '',
                    '',
                    ''
                )
            );
        } else {
            if ($accountid) {
                $account_Arr = null;
                $account_type = null;
            } else {
                $log_type = $this->CI->session->userdata("logintype");
                if ($log_type == 1) {
                    $account_type = null;
                } else {
                    $account_type = array(
                        gettext('Reseller'),
                        array(
                            'name' => 'reseller_id',
                            'class' => 'reseller_drp',
                            'id' => 'reseller_id',
                            'onchange' => 'account_change_add(this.value)'
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
                        'where_arr',
                        ''
                    );
                }
                $account_Arr = array(
                    gettext('Account'),
                    array(
                        'name' => 'accountcode',
                        'id' => 'accountcode'
                    ),
                    'SELECT',
                    '',
                    'trim|dropdown|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'first_name,last_name,number',
                    'accounts',
                    'build_concat_dropdown',
                    'where_arr',
                    array(
                        "reseller_id" => 0,
                        "type" => "0",
                        "deleted" => "0"
                    )
                );
            }
            if ($this->CI->session->userdata("logintype") == '1') {
                $sip_pro = null;
                $link = base_url() . 'freeswitch/customer_fssipdevices_save/true';
            } else {
                $sip_pro = array(
                    gettext('SIP Profile'),
                    'sip_profile_id',
                    'SELECT',
                    '',
                    'trim|dropdown|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'sip_profiles',
                    'build_dropdown',
                    'where_arr',
                    array(
                        "status" => "0"
                    )
                );
                $link = base_url() . 'freeswitch/fssipdevices_save/true';
            }
            $form['forms'] = array(
                $link,
                array(
                    "id" => "sipdevices_form",
                    "name" => "sipdevices_form"
                )
            );
            $form[gettext('Device Information')] = array(
                array(
                    '',
                    'HIDDEN',
                    array(
                        'name' => 'id'
                    ),
                    '',
                    '',
                    '',
                    ''
                ),
                array(
                    '',
                    'HIDDEN',
                    array(
                        'name' => 'accountcode',
                        'value' => $accountid
                    ),
                    '',
                    '',
                    '',
                    ''
                ),
                $user_name,
                $password_field,
                array(
                    gettext('Caller Name'),
                    'INPUT',
                    array(
                        'name' => 'effective_caller_id_name',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Caller Number'),
                    'INPUT',
                    array(
                        'name' => 'effective_caller_id_number',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                $account_type,
                $account_Arr,
                array(
                    gettext('Status'),
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
                ),
            
            // ASTPPCOM-945 Jaimin Start
                array(
                    gettext('Codec'),
                    'INPUT',
                    array(
                        'name' => 'codec',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    '',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
            // End
                $sip_pro
            );
            $form[gettext('Voicemail Options')] = array(
                array(
                    gettext('Enable'),
                    'voicemail_enabled',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_sip_config_option'
                ),
                array(
                    gettext('Password'),
                    'INPUT',
                    array(
                        'name' => 'voicemail_password',
                        'size' => '20',
                        'value' => $random_password,
                        'id' => 'random_password',
                        'class' => "text field medium"
                    ),
                    'trim|xss_clean',
                    'tOOL TIP',
                    'Please Enter Password',
                    '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_password align-self-end text-success fa fa-refresh"></i>'
                ),
                array(
                    gettext('Mail To'),
                    'INPUT',
                    array(
                        'name' => 'voicemail_mail_to',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    '',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Attach File'),
                    'voicemail_attach_file',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'custom_status_voicemail'
                ),
                array(
                    gettext('Local After Email'),
                    'vm_keep_local_after_email',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'custom_status_voicemail'
                ),
                array(
                    gettext('Send all Message'),
                    'vm_send_all_message',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'custom_status_voicemail'
                )
            );
        }

        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );

        return $form;
    }
}

?>
