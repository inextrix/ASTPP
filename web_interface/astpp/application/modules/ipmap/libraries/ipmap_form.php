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

#[\AllowDynamicProperties]
class Ipmap_form extends common
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function get_ipmap_form_fields($id = '', $reseller_id = '', $account_type = '')
    {
        $logintype = $this->CI->session->userdata('logintype');
        if ($account_type == '0' || $logintype == '0') {
            $account = null;
            $form['forms'] = array(
                base_url() . 'user/user_ipmap_save/',
                array(
                    'id' => 'ipmap_form',
                    'method' => 'POST',
                    'name' => 'ipmap_form'
                )
            );
        } else {
            //ASTPPCOM-1202_gautam_start
            if(astpp_gt($reseller_id, 0)){    
            $account = array(
                gettext('Account'),
                array(
                    'name' => 'accountid',
                    'id' => 'account_drp',
                    'class' => 'account_drp'
                ),
                'SELECT',
                '',
                array(
                    "name" => "accountid",
                    "rules" => "dropdown"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'first_name,last_name,number',
                'accounts',
                'build_concat_dropdown',
                'where_arr',
                array(
                    "reseller_id" => $reseller_id,
                    "type" => "0,3",
                    "deleted" => "0"
                )
            );
            }
            else{
                $account = array(
                    gettext('Account'),
                    array(
                        'name' => 'accountid',
                        'id' => 'account_drp',
                        'class' => 'account_drp'
                    ),
                    'SELECT',
                    '',
                    array(
                        "name" => "accountid",
                        "rules" => "dropdown"
                    ),
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'first_name,last_name,number,company_name',
                    'accounts',
                    'build_concat_dropdown',
                    'where_arr',
                    array(
                        "type" => "0,3",
                        "deleted" => "0"
                    )
                );
            }
            //ASTPPCOM-1202_gautam_end
            if (astpp_gt($id, 0)) {
                $reseller_drp = array(
                    gettext('Reseller'),
                    'INPUT',
                    array(
                        'name' => 'reseller_id',
                        'readonly' => 'true',
                        'size' => '20',
                        'maxlength' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|min_length[2]|max_length[20]|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                );
            } else {
                $reseller_drp = array(
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
            $form['forms'] = array(
                base_url() . 'ipmap/ipmap_save/',
                array(
                    'id' => 'ipmap_form',
                    'method' => 'POST',
                    'name' => 'ipmap_form'
                )
            );
        }
        if ($logintype == 2) {
            $form['IP map'] = array(
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
                $reseller_drp,
                $account,
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
                    'Please Enter account number'
                ),
                array(
                    gettext('IP'),
                    'INPUT',
                    array(
                        'name' => 'ip',
                        'id' => 'ip_match',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|valid_ip',
                    'tOOL TIP',
                    'Please Enter proper ip'
                ),
                array(
                    gettext('Prefix'),
                    'INPUT',
                    array(
                        'name' => 'prefix',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|xss_clean',
                    'tOOL TIP',
                    'Please Enter prefix number'
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
        } else {
            $form[gettext('IP map')] = array(
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
                    gettext('Account'),
                    array(
                        'name' => 'accountid',
                        'id' => 'account_drp',
                        'class' => 'account_drp'
                    ),
                    'SELECT',
                    '',
                    array(
                        "name" => "accountid",
                        "rules" => "dropdown"
                    ),
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'first_name,last_name,number',
                    'accounts',
                    'build_concat_dropdown',
                    'where_arr',
                    array(
                        "reseller_id" => "0",
                        "type" => "0,3",
                        "deleted" => "0"
                    )
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
                    'Please Enter account number'
                ),
                array(
                    gettext('IP'),
                    'INPUT',
                    array(
                        'name' => 'ip',
                        'id' => 'ip_match',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|valid_ip',
                    'tOOL TIP',
                    'Please Enter proper ip'
                ),
                array(
                    gettext('Prefix'),
                    'INPUT',
                    array(
                        'name' => 'prefix',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|xss_clean',
                    'tOOL TIP',
                    'Please Enter prefix number'
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
        }
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
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

    function get_ipmap_search_form($account_data = '')
    {
        $accountinfo = $this->CI->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;

        if ($accountinfo['type'] == - 1) {
            $reseller_drp = array(
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
            );
        } else {
            $reseller_drp = null;
        }
        if ($account_data == '0') {
            $account = null;
        } else {
            $account = array(
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
                    "reseller_id" => "0",
                    "type" => "0,3",
                    "deleted" => "0"
                )
            );
        }
        $form['forms'] = array(
            "",
            array(
                'id' => "ipmap_search"
            )
        );
        if (($accountinfo['type'] == - 1) || ($accountinfo['type'] == 2)) {
            $form['Search'] = array(
                $reseller_drp,
                $account,
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
                    gettext('IP'),
                    'INPUT',
                    array(
                        'name' => 'ip[ip]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'ip[ip-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Prefix'),
                    'INPUT',
                    array(
                        'name' => 'prefix[prefix]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'prefix[prefix-string]',
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
        } else {
            $form[gettext('Search')] = array(
                $account,
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
                    gettext('IP'),
                    'INPUT',
                    array(
                        'name' => 'ip[ip]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'ip[ip-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Prefix'),
                    'INPUT',
                    array(
                        'name' => 'prefix[prefix]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'prefix[prefix-string]',
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
        }
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "ipmap_search_btn",
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
            'class' => "btn btn-secondary float-right mx-2"
        );

        return $form;
    }

    function build_ipmap_list_for_admin($account_data = '')
    {
        $logintype = $this->CI->session->userdata('userlevel_logintype');
        if ($logintype == '-1' || $logintype == '0') {

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
                    "150",
                    "name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("IP"),
                    "130",
                    "ip",
                    "",
                    "",
                    "",
                    "EDITABLE",
                    "true",
                    "center"
                ),
                array(
                    gettext("Prefix"),
                    "100",
                    "prefix",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Account"),
                    "200",
                    "accountid",
                    "first_name,last_name,number",
                    "accounts",
                    "get_field_name_coma_new",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Reseller"),
                    "150",
                    "accountid",
                    "first_name,last_name,number",
                    "accounts",
                    "get_field_name_coma_new",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Created Date"),
                    "150",
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
                    "150",
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
                    "140",
                    "status",
                    "status",
                    "ip_map",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "100",
                    "",
                    "",
                    "",
                    array(
                        "EDIT" => array(
                            "url" => "ipmap/ipmap_edit/",
                            "mode" => "popup",
                            'popup'
                        ),
                        "DELETE" => array(
                            "url" => "ipmap/ipmap_delete/",
                            "mode" => "single"
                        )
                    ),
                    "false"
                )
            ));
        } else {
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
                    "150",
                    "name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("IP"),
                    "130",
                    "ip",
                    "",
                    "",
                    "",
                    "EDITABLE",
                    "true",
                    "center"
                ),
                array(
                    gettext("Prefix"),
                    "100",
                    "prefix",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Account"),
                    "200",
                    "accountid",
                    "first_name,last_name,number",
                    "accounts",
                    "get_field_name_coma_new",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Created Date"),
                    "150",
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
                    "150",
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
                    "140",
                    "status",
                    "status",
                    "ip_map",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "100",
                    "",
                    "",
                    "",
                    array(
                        "EDIT" => array(
                            "url" => "ipmap/ipmap_edit/",
                            "mode" => "popup",
                            'popup'
                        ),
                        "DELETE" => array(
                            "url" => "ipmap/ipmap_delete/",
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
                gettext("Add"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/ipmap/ipmap_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/ipmap/ipmap_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function build_grid_buttons_user()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/user/user_ipmap_add/",
                "popup"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/user/user_ipmap_delete_multiple/"
            )
        ));
        return $buttons_json;
    }
}

?>
