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
class did_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function get_search_did_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "did_search"
            )
        );
        $form['Search'] = array(
            array(
                gettext('DID'),
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
                gettext('Country'),
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
            array(
                gettext('City'),
                'INPUT',
                array(
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
            array(
                gettext('Province'),
                'INPUT',
                array(
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
                gettext('Cost'),
                'INPUT',
                array(
                    'name' => 'cost[cost]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'cost[cost-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Setup Fee'),
                'INPUT',
                array(
                    'name' => 'setup[setup]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'setup[setup-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Monthly Fee'),
                'INPUT',
                array(
                    'name' => 'monthlycost[monthlycost]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'monthlycost[monthlycost-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Call Type'),
                'call_type',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_call_type_search',
                '',
                ''
            ),
            array(
                gettext('Destination'),
                'INPUT',
                array(
                    'name' => 'extensions[extensions]',
                    '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '1',
                'extensions[extensions-string]',
                '',
                '',
                '',
                'search_string_type',
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
            'id' => "did_search_btn",
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

    function get_search_did_form_for_reseller()
    {
        $accountinfo = $this->CI->session->userdata('accountinfo');
        $form['forms'] = array(
            "",
            array(
                'id' => "did_search"
            )
        );
        $form['Search'] = array(
            array(
                gettext('DID'),
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
                gettext('Country'),
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
            array(
                gettext('Province'),
                'INPUT',
                array(
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
            array(
                gettext('City'),
                'INPUT',
                array(
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
                gettext('Cost'),
                'INPUT',
                array(
                    'name' => 'cost[cost]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'cost[cost-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Setup Fee'),
                'INPUT',
                array(
                    'name' => 'setup[setup]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'setup[setup-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Monthly Fee'),
                'INPUT',
                array(
                    'name' => 'monthlycost[monthlycost]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'monthlycost[monthlycost-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Call Type'),
                'call_type',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_call_type_search',
                '',
                ''
            ),
            array(
                gettext('Destination'),
                'INPUT',
                array(
                    'name' => 'extensions[extensions]',
                    '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '1',
                'extensions[extensions-string]',
                '',
                '',
                '',
                'search_string_type',
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
            'id' => "did_search_btn",
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

    function build_did_list_for_admin()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        $grid_field_arr = json_encode(array(
            array(
                "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                "40",
                "",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("DID"),
                "70",
                "number",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "center"
            ),
            array(
                gettext("Country"),
                "100",
                "country_id",
                "country",
                "countrycode",
                "get_field_name",
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
            array(
                gettext("Reseller"),
                "100",
                "parent_id",
                "first_name,last_name,number,company_name",
                "accounts",
                "reseller_select_value",
                "",
                "true",
                "center"
            ),
            array(
                gettext("City"),
                "60",
                "city",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Province"),
                "60",
                "province",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Cost/Min")." ($currency)",
                "60",
                "cost",
                "cost",
                "cost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Setup Fee")." ($currency)",
                "80",
                "setup",
                "setup",
                "setup",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Monthly Fee")." ($currency)",
                "80",
                "monthlycost",
                "monthlycost",
                "monthlycost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Call Timeout"),
                "60",
                "leg_timeout",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("CC"),
                "60",
                "maxchannels",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Billing Type"),
                "80",
                "id",
                "id",
                "id",
                "get_did_billing_type",
                "",
                "true",
                "right"
            ),

            array(
                gettext("Billing Days"),
                "80",
                "productid",
                "billing_days",
                "products",
                "get_field_name",
                "",
                "true",
                "right"
            ),

            array(
                gettext("Is Purchased?"),
                "110",
                "number",
                "number",
                "number",
                "check_did_avl",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Call Type"),
                "90",
                "call_type",
                "call_type",
                "call_type",
                "get_call_type",
                "",
                "true",
                "center"
            ),
            // Hiral
            // HP: PBX_ADDON
            array(
                gettext("Destination"),
                "80",
                "did_id_new",
                "did_id_new",
                "did_id_new",
                "get_call_type_grid",
                "",
                "true",
                "center"
            ),
            // END
            array(
                gettext("Forwarding"),
                "80",
                "did_id",
                "did_id",
                "did_id",
                "build_did_forward",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Status"),
                "90",
                "status",
                "status",
                "dids",
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
                        "url" => "products/products_edit/",
                        "mode" => "single",
                        "layout" => "medium"
                    ),
                    "DELETE" => array(
                        "url" => "did/did_remove/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_did_list_for_reseller_login()
    {
        $account_info = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        if (astpp_gt($account_info['reseller_id'], 0)) {
            $account = array(
                gettext("Account"),
                "105",
                "buyer_accountid",
                "first_name,last_name,number,company_name",
                "accounts",
                "get_field_name_coma_new",
                "",
                "true",
                "center"
            );
        } else {
            $account = array(
                gettext("Account"),
                "105",
                "accountid",
                "first_name,last_name,number,company_name",
                "accounts",
                "get_field_name_coma_new",
                "",
                "true",
                "center"
            );
        }

        $grid_field_arr = json_encode(array(
            array(
                "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                "40",
                "",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("DID"),
                "70",
                "number",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "center"
            ),
            array(
                gettext("Country"),
                "100",
                "country_id",
                "country",
                "countrycode",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Province"),
                "100",
                "province",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("City"),
                "100",
                "city",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            $account,
            array(
                gettext("Cost/Min")."<br>($currency)",
                "85",
                "cost",
                "cost",
                "cost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Setup Fee")." ($currency)",
                "80",
                "setup_fee",
                "setup_fee",
                "setup_fee",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),

            array(
                gettext("Monthly Fee")." ($currency)",
                "80",
                "price",
                "price",
                "price",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Call Timeout"),
                "60",
                "leg_timeout",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("CC"),
                "60",
                "maxchannels",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Billing Type"),
                "80",
                "billing_type",
                "billing_type",
                "billing_type",
                "get_did_billing_type",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Billing Days"),
                "80",
                "billing_days",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Is Purchased?"),
                "110",
                "number",
                "number",
                "number",
                "check_did_avl",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Call Type"),
                "90",
                "call_type",
                "call_type",
                "call_type",
                "get_call_type",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Destination"),
                "80",
                "extensions",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Forwarding"),
                "80",
                "id",
                "id",
                "id",
                "build_did_forward",
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
                        "url" => "products/products_edit/",
                        "mode" => "single",
                        "layout" => "medium"
                    ),
                    "DELETE" => array(
                        "url" => "did/did_remove/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
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
                "/products/products_did/",
                "single",
                "medium",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/did/did_delete_multiple/",
                "",
                "",
                "delete"
            ),
            array(
                gettext("Import"),
                "btn btn-line-blue",
                "fa fa-download fa-lg",
                "button_action",
                "/did/did_import/",
                '',
                "small",
                "import"
            ),
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/did/did_export_data_xls",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function build_grid_buttons_reseller()
    {
        $buttons_json = json_encode(array(

            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/did/did_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function build_did_list_for_customer($accountid, $accounttype)
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $grid_field_arr = json_encode(array(
            array(
                "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                "40",
                "",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("DID"),
                "110",
                "number",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Country"),
                "110",
                "country_id",
                "country",
                "countrycode",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Province"),
                "100",
                "province",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("City"),
                "100",
                "city",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Per Minute Cost")." ($currency)",
                "150",
                "cost",
                "cost",
                "cost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Initial Increment"),
                "140",
                "init_inc",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Increment"),
                "120",
                "inc",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Setup Fee")." ($currency)",
                "140",
                "setup_fee",
                "setup_fee",
                "setup_fee",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Monthly Fee")." ($currency)",
                "140",
                "price",
                "price",
                "price",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            )
        ));
        return $grid_field_arr;
    }

    function build_did_list_for_reseller($accountid, $accounttype)
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("DID Number"),
                "120",
                "number",
                "",
                "",
                ""
            ),
            array(
                gettext("Increment"),
                "120",
                "inc",
                "",
                "",
                ""
            ),
            array(
                gettext("Is purchased?"),
                "120",
                "number",
                "number",
                "number",
                "check_did_avl_reseller"
            ),
            array(
                gettext("Per Minute Cost"),
                "120",
                "cost",
                "cost",
                "cost",
                "convert_to_currency_account"
            ),
            array(
                gettext("Included Seconds"),
                "100",
                "includedseconds",
                "",
                "",
                ""
            ),
            array(
                gettext("Setup Fee"),
                "109",
                "setup",
                "setup",
                "setup",
                "convert_to_currency_account"
            ),
            array(
                gettext("Monthly Fee"),
                "140",
                "monthlycost",
                "monthlycost",
                "monthlycost",
                "convert_to_currency_account"
            ),
            array(
                gettext("Connection Cost"),
                "149",
                "connectcost",
                "connectcost",
                "connectcost",
                "convert_to_currency_account"
            ),
            array(
                gettext("Disconnection Fee"),
                "140",
                "disconnectionfee",
                "disconnectionfee",
                "disconnectionfee",
                "convert_to_currency_account"
            ),
            array(
                gettext("Action"),
                "100",
                "",
                "",
                "",
                array(
                    "DELETE" => array(
                        "url" => "/accounts/reseller_did_action/delete/$accountid/$accounttype/",
                        "mode" => "single"
                    )
                )
            )
        ));
        return $grid_field_arr;
    }

    /* -------------Ekta DID Change start--------------- */
    function build_did_list_for_available_dids()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        if (astpp_gt($account_info['reseller_id'], 0)) {
            $setup_fee = array(
                gettext("Setup Fee")." ($currency)",
                "80",
                "setup_fee",
                "setup_fee",
                "setup_fee",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            );
            $price = array(
                gettext("Monthly Fee")." ($currency)",
                "80",
                "price",
                "price",
                "price",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            );
        } else {
            $setup_fee = array(
                gettext("Setup Fee")." ($currency)",
                "80",
                "setup",
                "setup",
                "setup",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            );
            $price = array(
                gettext("Monthly Fee")." ($currency)",
                "80",
                "monthlycost",
                "monthlycost",
                "monthlycost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            );
        }

        $grid_field_arr = json_encode(array(
            array(
                gettext("DID"),
                "70",
                "number",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "center"
            ),
            array(
                gettext("Country"),
                "100",
                "country_id",
                "country",
                "countrycode",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Province"),
                "100",
                "province",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("City"),
                "100",
                "city",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Cost/Min")." ($currency)",
                "85",
                "cost",
                "cost",
                "cost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),

            $setup_fee,
            $price,

            array(
                gettext("Call Timeout"),
                "60",
                "leg_timeout",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("CC"),
                "60",
                "maxchannels",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Billing Type"),
                "80",
                "product_id",
                "product_id",
                "product_id",
                "get_did_billing_type",
                "",
                "true",
                "right"
            ),

            array(
                gettext("Billing Days"),
                "80",
                "id",
                "billing_days",
                "products",
                "get_field_name",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Is Purchased?"),
                "110",
                "number",
                "number",
                "number",
                "check_did_available_reseller",
                "",
                "true",
                "center"
            ),

            array(
                gettext("Call Type"),
                "90",
                "call_type",
                "call_type",
                "call_type",
                "get_call_type",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Destination"),
                "80",
                "extensions",
                "",
                "",
                "",
                "",
                "true",
                "center"
            )
        ));
        return $grid_field_arr;
    }

    function get_available_search_did_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "available_did_search"
            )
        );
        $form['Search'] = array(
            array(
                gettext('Country'),
                'country_id',
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
            array(
                gettext('Province'),
                'INPUT',
                array(
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
            array(
                gettext('City'),
                'INPUT',
                array(
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
            'id' => "available_did_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "available_did_id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );

        return $form;
    }
}
?>
