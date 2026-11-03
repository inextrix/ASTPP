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
class pricing_form extends common
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function get_pricing_form_fields($id = '')
    {
        $trunk_count = Common_model::$global_config['system_config']['trunk_count'];
        $account_data = $this->CI->session->userdata("accountinfo");
        $type_version = $this->CI->session->userdata("type_version");

        if ($type_version != 'E') {
            $Routing_Prefix = array(
                gettext('Routing Prefix').'#Enterprise#',
                'INPUT',
                array(
                    'name' => 'routing_prefix',
                    'size' => '8',
                    'class' => "text field medium"
                ),
                'trim|numeric|greater_than[-1]|integer|xss_clean',
                'tOOL TIP',
                'Please Enter routing prefix'
            );
        } else {
            $Routing_Prefix = array(
                gettext('Routing Prefix').'#Enterprise#',
                'INPUT',
                array(
                    'name' => 'routing_prefix',
                    'size' => '8',
                    'class' => "text field medium"
                ),
                'trim|numeric|greater_than[-1]|integer|xss_clean',
                'tOOL TIP',
                'Please Enter routing prefix'
            );
        }

        $logintype = $this->CI->session->userdata("logintype");
        if ($logintype == 1 || $logintype == 5) {
            $loginid = $account_data['id'];
        } else {
            $loginid = "0";
        }
        if (astpp_gt($id, 0)) {
            $routing_prefix = 'pricelists.routing_prefix.' . $id;
            $name = 'pricelists.name.' . $id;
            if ($this->CI->session->userdata("logintype") == '1') {
                $account_type = null;
            } else {
                $account_type = array(
                    gettext('Reseller'),
                    'INPUT',
                    array(
                        'name' => 'reseller_id',
                        'readonly' => 'true',
                        'size' => '20',
                        'maxlength' => '15',
                        'class' => "text field medium reseller_id"
                    ),
                    '',
                    'tOOL TIP',
                    'Please Enter account number'
                );
            }
        } else {
            $routing_prefix = 'pricelists.routing_prefix';
            $name = 'pricelists.name';
            if ($this->CI->session->userdata("logintype") == '1') {
                $account_type = null;
            } else {
                $account_type = array(
                    gettext('Reseller'),
                    array(
                        'name' => 'reseller_id',
                        'class' => 'reseller_id',
                        'id' => 'reseller'
                    ),
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'get_reseller_info'
                );
            }
        }
        if (astpp_eq0($account_data['reseller_id'])) {
            $reseller_rate_group_array = array(
                gettext('Admin Rate Group'),
                'pricelist_id_admin',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name,routing_prefix',
                'pricelists',
                'build_dropdown_reseller',
                'where_arr',
                array(
                    "status" => "0",
                    "routing_prefix <>" => " "
                )
            );
        } else {
            $reseller_rate_group_array = array(
                gettext('Reseller Rate Group'),
                'pricelist_id_admin',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name,routing_prefix',
                'pricelists',
                'build_dropdown_reseller',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => $account_data['reseller_id'],
                    "routing_prefix <>" => ""
                )
            );
        }

        $form['forms'] = array(
            base_url() . 'pricing/price_save/',
            array(
                'id' => 'pricing_form',
                'method' => 'POST',
                'name' => 'pricing_form'
            )
        );
        if ($this->CI->session->userdata('logintype') == 1 || $this->CI->session->userdata('logintype') == 5) {

            $form[gettext('Basic')] = array(
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
                        'name' => 'status',
                        'value' => '1'
                    ),
                    '',
                    '',
                    ''
                ),
                $account_type,
                array(
                    gettext('Name'),
                    'INPUT',
                    array(
                        'name' => 'name',
                        'size' => '20',
                        'maxlength' => '30',
                        'class' => "text field medium"
                    ),
                    'trim|required|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                $Routing_Prefix,
                $reseller_rate_group_array,
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
            $form[gettext('Billing')] = array(

                array(
                    gettext('Markup')."(%)",
                    'INPUT',
                    array(
                        'name' => 'markup',
                        'value' => "0",
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|numeric|greater_than[-1]|less_than[101]|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Initial Increment'),
                    'INPUT',
                    array(
                        'name' => 'initially_increment',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|numeric|greater_than[-1]|integer|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Increment'),
                    'INPUT',
                    array(
                        'name' => 'inc',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|numeric|greater_than[-1]|integer|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                )
            );
        } else {

            $form[gettext('Basic')] = array(
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
                        'name' => 'status',
                        'value' => '1'
                    ),
                    '',
                    '',
                    ''
                ),
                $account_type,
                array(
                    gettext('Name'),
                    'INPUT',
                    array(
                        'name' => 'name',
                        'size' => '20',
                        'maxlength' => '30',
                        'class' => "text field medium"
                    ),
                    'trim|required|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                $Routing_Prefix,
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
            $form[gettext('Billing')] = array(

                array(
                    gettext('Markup').'(%)',
                    'INPUT',
                    array(
                        'name' => 'markup',
                        'value' => "0",
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|numeric|greater_than[-1]|less_than[101]|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Initial Increment'),
                    'INPUT',
                    array(
                        'name' => 'initially_increment',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|numeric|greater_than[-1]|integer|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Increment'),
                    'INPUT',
                    array(
                        'name' => 'inc',
                        'size' => '20',
                        'class' => "text field medium"
                    ),
                    'trim|required|numeric|greater_than[-1]|integer|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Routing Type'),
                    array(
                        'id' => 'routing_type',
                        'name' => 'routing_type',
                        'class' => 'routing_type',
                        "onchange" => "trunk_change(this.value)"
                    ),
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Select Status',
                    '',
                    '',
                    '',
                    'set_routetype'
                ),
                array(
                    gettext('Trunks'),
                    'trunk_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Select Trunks',
                    'id',
                    'name',
                    'trunks',
                    'build_dropdown',
                    'where_arr',
                    array(
                        "status" => "0"
                    ),
                    'multi'
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

    function get_pricing_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "price_search"
            )
        );
        if ($this->CI->session->userdata('logintype') == 1 || $this->CI->session->userdata('logintype') == 5) {
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
                    gettext('Initial Increment'),
                    'INPUT',
                    array(
                        'name' => 'initially_increment[initially_increment]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'initially_increment[initially_increment-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Increment'),
                    'INPUT',
                    array(
                        'name' => 'inc[inc]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'inc[inc-string]',
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
        } else {
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
                    gettext('Routing Prefix').'#Enterprise#',
                    'INPUT',
                    array(
                        'name' => 'routing_prefix[routing_prefix]',
                        '',
                        'size' => '8',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'routing_prefix[routing_prefix-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Routing Type'),
                    'routing_type',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_routetype_status',
                    '',
                    ''
                ),
                array(
                    gettext('Initial Increment'),
                    'INPUT',
                    array(
                        'name' => 'initially_increment[initially_increment]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'initially_increment[initially_increment-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Increment'),
                    'INPUT',
                    array(
                        'name' => 'inc[inc]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'inc[inc-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Reseller'),
                    'reseller_id',
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
        }
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "price_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => "btn btn-success float-right"
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

    function build_pricing_list_for_admin()
    {
        if ($this->CI->session->userdata('logintype') == 1 || $this->CI->session->userdata('logintype') == 5) {
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
                    "110",
                    "name",
                    "",
                    "",
                    "",
                    "EDITABLE",
                    "true",
                    "left"
                ),
                array(
                    gettext("Initial Increment"),
                    "120",
                    "initially_increment",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "right"
                ),
                array(

                    gettext("Increment"),

                    "130",
                    "inc",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Markup")."(%)",
                    "80",
                    "markup",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Rates Count"),
                    "110",
                    "id",
                    "pricelist_id",
                    "routes",
                    "get_field_count",
                    "",
                    "false",
                    "right"
                ),
                array(
                    gettext("Created Date"),
                    "90",
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
                    "90",
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
                    "110",
                    "status",
                    "id",
                    "pricelists",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "150",
                    "",
                    "",
                    "",
                    array(
                        "EDIT" => array(
                            "url" => "pricing/price_edit/",
                            "mode" => "popup"
                        ),

                        "DELETE" => array(
                            "url" => "pricing/price_delete/",
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
                    "90",
                    "name",
                    "",
                    "",
                    "",
                    "EDITABLE",
                    "true",
                    "left"
                ),
                array(
                    gettext("Routing Prefix"),
                    "70",
                    "routing_prefix",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Routing Type"),
                    "80",
                    "routing_type",
                    "routing_type",
                    "routing_type",
                    "get_routetype"
                ),
                array(
                    gettext("Initial Increment"),
                    "80",
                    "initially_increment",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Increment"),
                    "80",
                    "inc",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Markup")." (%)",
                    "80",
                    "markup",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Rates Count"),
                    "110",
                    "id",
                    "pricelist_id",
                    "routes",
                    "get_field_count",
                    "",
                    "false",
                    "right"
                ),
                array(
                    gettext("Reseller"),
                    "110",
                    "reseller_id",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "reseller_select_value"
                ),
                array(
                    gettext("Created Date"),
                    "90",
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
                    "90",
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
                    "id",
                    "pricelists",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "150",
                    "",
                    "",
                    "",
                    array(
                        "EDIT" => array(
                            "url" => "pricing/price_edit/",
                            "mode" => "popup"
                        ),

                        "DELETE" => array(
                            "url" => "pricing/price_delete/",
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
                "/pricing/price_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/pricing/price_delete_multiple/",
                "",
                "",
                "delete"
            ),
            array(
                gettext("Duplicate"),
                "btn btn-line-blue",
                "fa fa-clone fa-lg",
                "button_action",
                "/pricing/price_duplicate/",
                "popup",
                "",
                "duplicate"
            )
        ));
        return $buttons_json;
    }

    function set_routetype($status = '')
    {
        $where = array(
            "name" => "trunk_count"
        );
        $this->CI->db->where($where);
        $this->CI->db->select('value');
        $trunk_count = (array) $this->CI->db->get('system')->first_row();

        $type_version = $this->CI->session->userdata("type_version");

        if ($type_version != 'E') {

            if ($trunk_count['value'] > 1) {
                $status_array = array(
                    '0' => gettext('LCR'),
                    '1' => gettext('COST'),
                    '2' => gettext('Priority (Enterprise)')
                );
            } else {
                $status_array = array(
                    '0' => gettext('LCR'),
                    '1' => gettext('Cost')
                );
            }
            return $status_array;
        } else {

            if ($trunk_count['value'] > 1) {
                $status_array = array(
                    '0' => gettext('LCR'),
                    '1' => gettext('Cost'),
                    '2' => gettext('Priority (Enterprise)'),
                    '3' => gettext('Percentage (Enterprise)')
                );
            } else {
                $status_array = array(
                    '0' => gettext('LCR'),
                    '1' => gettext('Cost')
                );
            }
            return $status_array;
        }
    }

    function set_routetype_res($status = '')
    {
        $status_array = array(
            '0' => gettext('LCR'),
            '1' => gettext('Cost')
        );
        return $status_array;
    }

    function get_routetype($select, $table, $status)
    {
        if (astpp_eq0($status)) {
            return "LCR";
        } else if ($status == 1) {
            return "Cost";
        } else if ($status == 2) {
            return "Priority";
        } else {
            return "Percentage";
        }
    }

    function set_routetype_origination($status = '')
    {
        $status_array = array(
            '0' => 'Priority',
            '1' => 'Percentage'
        );
        return $status_array;
    }

    function set_routetype_reseller($status = '')
    {
        $status_array = array(
            '0' => 'LCR',
            '1' => 'Cost'
        );
        return $status_array;
    }

    function set_routetype_status($select = '')
    {
        $where = array(
            "name" => "trunk_count"
        );
        $this->CI->db->where($where);
        $this->CI->db->select('value');
        $trunk_count = (array) $this->CI->db->get('system')->first_row();
        if ($trunk_count['value'] > 1) {
            $status_array = array(
                "" => gettext("--Select--"),
                '0' => gettext('LCR'),
                '1' => gettext('Cost'),
                '2' => gettext('Priority (Enterprise)'),
                '3' => gettext('Percentage')
            );
        } else {
            $status_array = array(
                "" => gettext("--Select--"),
                '0' => gettext('LCR'),
                '1' => gettext('Cost')
            );
        }
        return $status_array;
    }

    function get_pricing_duplicate_form_fields()
    {
        $form['forms'] = array(
            base_url() . 'pricing/price_duplicate_save/',
            array(
                'id' => 'pricing_duplicate_form',
                'method' => 'POST',
                'name' => 'pricing_duplicate_form'
            )
        );
        if ($this->CI->session->userdata('logintype') == 1 || $this->CI->session->userdata('logintype') == 5) {
            $form[gettext('Duplicate Rate Group Information')] = array(
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
                        'name' => 'status',
                        'value' => '1'
                    ),
                    '',
                    '',
                    ''
                ),
                array(
                    gettext('New Rate Group Name'),
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
                    gettext('Copy From Rate Group'),
                    'pricelist_id',
                    'SELECT',
                    '',
                    'trim|required|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'pricelists',
                    'build_dropdown',
                    'where_arr',
                    array(
                        "status" => "0",
                        'reseller_id' => 0
                    )
                )
            );
        } else {
            $form[gettext('Duplicate Rate Group Information')] = array(
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
                        'name' => 'status',
                        'value' => '1'
                    ),
                    '',
                    '',
                    ''
                ),
                array(
                    gettext('New Rate Group Name'),
                    'INPUT',
                    array(
                        'name' => 'name',
                        'size' => '20',
                        'maxlength' => '30',
                        'class' => "text field medium"
                    ),
                    'trim|required|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number'
                ),
                array(
                    gettext('Rate Group'),
                    'pricelist_id',
                    'SELECT',
                    '',
                    'trim|required|xss_clean',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'pricelists',
                    'build_dropdown',
                    'where_arr',
                    array(
                        "status" => "0"
                    )
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
