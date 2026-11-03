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
class Animap_form extends common
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function get_animap_form_fields($edit_id)
    {
        $id = $edit_id;
        $form['forms'] = array(
            base_url() . 'animap/animap_save/',
            array(
                'id' => 'animap_form',
                'method' => 'POST',
                'name' => 'animap_form'
            )
        );
        $logintype = $this->CI->session->userdata('logintype');
        $val = astpp_gt($id, 0) ? 'ani_map.number.' . $id : 'ani_map.number';
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
        if ($logintype == 2) {
            $form[gettext('Caller ID')] = array(
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
                array(
                    gettext('Account'),
                    array(
                        'name' => 'accountid',
                        'class' => 'account_drp',
                        'id' => 'account_drp'
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
                ),
                array(
                    gettext('Caller ID'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|is_unique[' . $val . ']|numeric|xss_clean',
                    'tOOL TIP',
                    'Please Enter ANI number'
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
            $form[gettext('Caller ID')] = array(
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
                        "type" => "0,3",
                        "deleted" => "0"
                    )
                ),
                array(
                    gettext('Caller ID'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|is_unique[' . $val . ']|numeric|xss_clean',
                    'tOOL TIP',
                    'Please Enter ANI number'
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

    function get_animap_search_form()
    {
        $logintype = $this->CI->session->userdata('userlevel_logintype');
        $accountinfo = $this->CI->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $form['forms'] = array(
            "",
            array(
                'id' => "animap_search"
            )
        );
        if (($logintype == - 1) || ($logintype == 2)) {

            $form[gettext('Search')] = array(
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
                        "reseller_id" => "0",
                        "type" => "0,3",
                        "deleted" => "0"
                    )
                ),
                array(
                    gettext('Caller ID'),
                    'INPUT',
                    array(
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
                    gettext('Caller ID'),
                    'INPUT',
                    array(
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
            'id' => "animap_search_btn",
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
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }

    function build_animap_list_for_admin()
    {
        $logintype = $this->CI->session->userdata('logintype');
        if ($logintype == 2) {
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
                    gettext("Caller ID"),
                    "100",
                    "number",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("Account"),
                    "220",
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
                    "250",
                    "reseller_id",
                    "first_name,last_name,number",
                    "accounts",
                    "get_field_name_coma_new",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Created Date"),
                    "200",
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
                    "200",
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
                    "100",
                    "status",
                    "status",
                    "status",
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
                            "url" => "animap/animap_edit/",
                            "mode" => "popup",
                            'popup'
                        ),
                        "DELETE" => array(
                            "url" => "/animap/animap_delete/",
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
                    gettext("Caller ID"),
                    "100",
                    "number",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("Account"),
                    "220",
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
                    "200",
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
                    "200",
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
                    "100",
                    "status",
                    "status",
                    "status",
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
                            "url" => "animap/animap_edit/",
                            "mode" => "popup",
                            'popup'
                        ),
                        "DELETE" => array(
                            "url" => "/animap/animap_delete/",
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
                "/animap/animap_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/animap/animap_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }
}

?>
