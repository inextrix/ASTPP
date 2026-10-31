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
if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class invoices_form extends common
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function build_invoices_list_for_admin()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        $logintype = $this->CI->session->userdata('logintype');
        $url = ($logintype == 0 || $logintype == 3) ? "/user/user_invoice_download/" : '/invoices/invoice_main_download/';
        $grid_field_arr = json_encode(array(
            array(
                gettext("Number"),
                "100",
                "number",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Type"),
                "100",
                "id",
                "id,'',type",
                "invoices",
                "build_concat_string",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Account"),
                "100",
                "accountid",
                "first_name,last_name,number",
                "accounts",
                "build_concat_string",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Invoice Date"),
                "100",
                "generate_date",
                "generate_date",
                "generate_date",
                "convert_to_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("From Date"),
                "120",
                "from_date",
                "from_date",
                "",
                "get_from_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Due Date"),
                "120",
                "due_date",
                "due_date",
                "due_date",
                "convert_to_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Amount")." <br/> ($currency)",
                "100",
                "id",
                "id",
                "id",
                "get_invoice_total",
                "",
                "false",
                "right"
            ),

            array(
                gettext("Outstanding Amount")." <br/>($currency)",
                "140",
                "",
                "",
                "",
                "",
                "",
                "false",
                "right"
            ),
            array(
                gettext("Reseller"),
                "100",
                "reseller_id",
                "first_name",
                "accounts",
                "get_field_name",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Action"),
                "120",
                "",
                "",
                "",
                array(
                    "DOWNLOAD" => array(
                        "url" => $url,
                        "mode" => "single"
                    )
                )
            )
        ));
        return $grid_field_arr;
    }

    function build_invoices_list_for_customer_admin()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        $logintype = $this->CI->session->userdata('logintype');
        $url = ($logintype == 0 || $logintype == 3) ? "/user/user_invoice_download/" : '/invoices/invoice_main_download/';
        $grid_field_arr = json_encode(array(
            array(
                gettext("Number"),
                "110",
                "id",
                "id,'',type",
                "invoices",
                "build_concat_string",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Type"),
                "110",
                "id",
                "id,'',type",
                "invoices",
                "build_concat_string",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Generated Date"),
                "120",
                "invoice_date",
                "invoice_date",
                "",
                "get_invoice_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("From Date"),
                "120",
                "from_date",
                "from_date",
                "",
                "get_from_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Due Date"),
                "130",
                "",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Last Pay Date"),
                "100",
                "",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Amount")." ($currency)",
                "100",
                "id",
                "id",
                "id",
                "get_invoice_total",
                "",
                "true",
                "right"
            ),

            array(
                gettext("Outstanding Amount")." <br/>($currency)",
                "100",
                "",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Action"),
                "120",
                "",
                "",
                "",
                array(
                    "DOWNLOAD" => array(
                        "url" => $url,
                        "mode" => "single"
                    )
                )
            )
        ));
        return $grid_field_arr;
    }

    function build_invoices_list_for_customer()
    {
        $url = ($this->CI->session->userdata('logintype') == 0) ? "/user/user_invoice_download/" : '/invoices/invoice_main_download/';
        $grid_field_arr = json_encode(array(
            array(
                gettext("Number"),
                "100",
                "id",
                "id,'',type",
                "invoices",
                "build_concat_string",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Account"),
                "110",
                "accountid",
                "first_name,last_name,number",
                "accounts",
                "build_concat_string",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Generated Date"),
                "140",
                "invoice_date",
                "invoice_date",
                "",
                "get_invoice_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("From Date"),
                "140",
                "from_date",
                "from_date",
                "",
                "get_from_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Due Date"),
                "150",
                "",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Last Pay Date"),
                "150",
                "",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Amount)"),
                "150",
                "id",
                "id",
                "id",
                "get_invoice_total",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Outstanding Amount"),
                "150",
                "",
                "",
                "",
                ""
            ),
            array(
                gettext("Action"),
                "160",
                "",
                "",
                "",
                array(
                    "DOWNLOAD" => array(
                        "url" => $url,
                        "mode" => "single"
                    )
                )
            )
        ));
        return $grid_field_arr;
    }

    function get_invoice_search_form()
    {
        $account_data = $this->CI->session->userdata("accountinfo");
        $reseller_id = $account_data['type'] == 1 ? $account_data['id'] : 0;
        $form['forms'] = array(
            "",
            array(
                'id' => "invoice_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Number'),
                'INPUT',
                array(
                    'name' => 'number[number]',
                    '',
                    'id' => 'number',
                    'size' => '15',
                    'class' => "text field "
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
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'from_date[]',
                    'id' => 'invoice_from_date',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '',
                'from_date[from_date-date]'
            ),
            array(
                gettext('To Date'),
                'INPUT',
                array(
                    'name' => 'to_date[]',
                    'id' => 'invoice_to_date',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '',
                'from_date[from_date-date]'
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'debit_exchange_rate[debit_exchange_rate]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'debit_exchange_rate[debit_exchange_rate-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
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
                'first_name,last_name,number,company_name',
                'accounts',
                'build_dropdown',
                'where_arr',
                array(
                    "reseller_id" => $reseller_id,
                    "type" => "GLOBAL",
                    "status" => 0
                )
            ),
            array(
                gettext('Generated Date'),
                'INPUT',
                array(
                    'name' => 'generate_date[0]',
                    '',
                    'size' => '20',
                    'class' => "text field",
                    'id' => 'invoice_date'
                ),
                '',
                'tOOL TIP',
                '',
                'invoice_date[invoice_date-date]'
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
            'id' => "invoice_search_btn",
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

    function build_grid_buttons()
    {
        $buttons_json = json_encode(array());
        return $buttons_json;
    }

    function build_invoice_conf_list()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Name"),
                "200",
                "company_name",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),

            array(
                gettext("Account"),
                "95",
                "accountid",
                "first_name,last_name,number,company_name",
                "accounts",
                "build_concat_string"
            ),
            array(
                gettext("Reseller"),
                "100",
                "reseller_id",
                "first_name,last_name,number,company_name",
                "accounts",
                "reseller_select_value",
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
                ""
            ),
            array(
                gettext("Country"),
                "150",
                "country",
                "country",
                "countrycode",
                "get_field_name",
                "",
                "true",
                "center"
            ),

            array(
                gettext("Telephone"),
                "80",
                "telephone",
                "",
                "",
                ""
            ),
            array(
                gettext("Email"),
                "190",
                "emailaddress",
                "",
                "",
                ""
            ),
            array(
                gettext("Domain"),
                "120",
                "domain",
                "",
                "",
                ""
            ),

            array(
                gettext("Action"),
                "80",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "invoices/invoice_conf_edit/",
                        "mode" => "single"
                    ),
                    "DELETE" => array(
                        "url" => "invoices/invoice_conf_remove/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_grid_buttons_conf($count)
    {
        if ($count > 0) {
            $buttons_json = json_encode(array(
                ''
            ));
        } else {
            $buttons_json = json_encode(array(
                ''
            ));
        }
        return $buttons_json;
    }

    function get_invoiceconf_form_fields($invoiceconf = '0', $id = false)
    {
        $country_id = Common_model::$global_config['system_config']['country'];
        $account_info = $this->CI->session->userdata("accountinfo");
        $logintype = $this->CI->session->userdata('logintype');

        if (! empty($invoiceconf)) {
            if (isset($invoiceconf['logo']) && $invoiceconf['logo'] != '') {
                $logo = $invoiceconf['logo'];
            } else {
                $logo = '';
            }
            if (isset($invoiceconf['favicon']) && $invoiceconf['favicon'] != '') {
                $favicon = $invoiceconf['favicon'];
            } else {
                $favicon = '';
            }
            $accountid = $invoiceconf['accountid'];
            if ($logo != '') {
                $file_name = base_url() . "upload/$logo";
                $del_button = 'DEL_BUTTON';
            } else {
                $file_name = '';
                $del_button = 'HIDDEN';
            }
            $image_path = array(
                '',
                'IMAGE',
                array(
                    'type' => 'image',
                    'name' => 'image',
                    'style' => 'max-height:80px;',
                    'src' => $file_name,
                    'id' => "company_logo"
                ),
                '',
                'tOOL TIP',
                ''
            );
            $delete_logo = array(
                gettext('Delete logo'),
                $del_button,
                array(
                    'value' => 'ankit',
                    'style' => 'margin-top:20px;',
                    'name' => 'button',
                    'id' => 'logo_delete',
                    'size' => '20',
                    'class' => "btn btn-line-parrot"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            );

            if ($favicon != '') {
                $file_name_fav = base_url() . "upload/$favicon";
                $fav_delete = "DEL_BUTTON";
            } else {

                $fav_delete = "HIDDEN";
                $file_name_fav = "";
            }
            $image_fav = array(
                '',
                'IMAGE',
                array(
                    'type' => 'image',
                    'name' => 'image',
                    'style' => 'max-width:16px;',
                    'src' => $file_name_fav,
                    'id' => "company_fav"
                ),
                '',
                'tOOL TIP',
                ''
            );
            $delete_fav = array(
                gettext('Delete Favicon'),
                $fav_delete,
                array(
                    'value' => '',
                    'style' => 'margin-top:20px;',
                    'name' => 'button',
                    'id' => 'fav_delete',
                    'size' => '20',
                    'maxlength' => '100',
                    'class' => "btn btn-line-parrot"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            );
        } else {

            $logo = '';
            $file_name = '';
            $favicon = '';
            $file_name_fav = '';
            $accountid = 0;
            $image_path = array(
                '',
                'IMAGE',
                array(
                    'type' => 'image',
                    'name' => 'image',
                    'style' => 'max-height:80px;',
                    'src' => "",
                    'id' => "company_logo"
                ),
                '',
                'tOOL TIP',
                ''
            );
            $delete_logo = array(
                gettext('Delete logo'),
                'HIDDEN',
                array(
                    'value' => '',
                    'style' => 'margin-top:0px;',
                    'name' => 'button',
                    'onclick' => 'return image_delete(' . $accountid . ')',
                    'size' => '20',
                    'maxlength' => '100',
                    'class' => "btn btn-line-parrot"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            );
            $image_fav = array(
                '',
                'IMAGE',
                array(
                    'type' => 'image',
                    'name' => 'image',
                    'style' => 'max-width:16px;',
                    'src' => "",
                    'id' => "company_fav"
                ),
                '',
                'tOOL TIP',
                ''
            );
            $delete_fav = array(
                gettext('Delete Favicon'),
                'HIDDEN',
                array(
                    'value' => '',
                    'style' => 'margin-top:0px;',
                    'name' => 'button',
                    'onclick' => 'return image_delete(' . $accountid . ')',
                    'size' => '20',
                    'maxlength' => '100',
                    'class' => "btn btn-line-parrot"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            );
        }
        $form['forms'] = array(
            base_url() . 'invoices/invoice_conf/',
            array(
                'id' => 'invoice_conf_form',
                'method' => 'POST',
                'name' => 'invoice_conf_form',
                'enctype' => 'multipart/form-data'
            )
        );
        if ($logintype == 1) {

            $query = $this->CI->db->query("SELECT * FROM (`invoice_conf`) WHERE `accountid` = " . $account_info['id'] . "");
            $accountinfo = $query->result_array();
            $accountinfo = $accountinfo['0'];
            $reseller_id = ($accountinfo['reseller_id'] == '0') ? $accountinfo['accountid'] : $accountinfo['reseller_id'];

            $reseller = array(
                gettext('Reseller'),
                array(
                    'name' => 'reseller_id',
                    'class' => 'reseller_id',
                    'value' => $reseller_id
                ),
                'SELECT',
                '',
                array(
                    "name" => "reseller_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'first_name,last_name,number,company_name',
                'accounts',
                'build_concat_dropdown',
                '',
                // the dropdown listed all accounts of all tenants; a reseller only sees itself
                array(
                    'id' => (int) $account_info['id']
                )
            );
        } else {

            $reseller = array(
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
                'where_arr',
                ''
            );
        }
        $form[gettext('Configuration')] = array(
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
                    'name' => 'accountid'
                ),
                '',
                '',
                '',
                ''
            ),
            $reseller,
            array(
                gettext('Company'),
                'INPUT',
                array(
                    'name' => 'company_name',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'required|xss_clean',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Address')."1",
                'INPUT',
                array(
                    'name' => 'address',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('City'),
                'INPUT',
                array(
                    'name' => 'city',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Province'),
                'INPUT',
                array(
                    'name' => 'province',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Country'),
                array(
                    'name' => 'country',
                    'class' => 'country_id',
                    'value' => $country_id
                ),
                'SELECT',
                '',
                array(
                    "name" => "country_id",
                    "rules" => "required"
                ),
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
                gettext('Zip Code'),
                'INPUT',
                array(
                    'name' => 'zipcode',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Phone'),
                'INPUT',
                array(
                    'name' => 'telephone',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Fax'),
                'INPUT',
                array(
                    'name' => 'fax',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Email'),
                'INPUT',
                array(
                    'name' => 'emailaddress',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'required',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Website'),
                'INPUT',
                array(
                    'name' => 'website',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Company Tax number'),
                'INPUT',
                array(
                    'name' => 'invoice_taxes_number',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'required',
                'tOOL TIP',
                'Please Enter account number'
            )
        );
        $form[gettext('Invoice Configuration')] = array(
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
                    'name' => 'accountid'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('Invoice Notification'),
                'invoice_notification',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_allow_invoice'
            ),
            array(
                gettext('Invoice Due Notification'),
                'invoice_due_notification',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_allow_invoice'
            ),
            array(
                gettext('Invoice Due Days'),
                'INPUT',
                array(
                    'name' => 'interval',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Notify before due days'),
                'INPUT',
                array(
                    'name' => 'notify_before_day',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Invoice Prefix'),
                'INPUT',
                array(
                    'name' => 'invoice_prefix',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'required|alpha_dash_numeric_space',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Invoice Start From'),
                'INPUT',
                array(
                    'name' => 'invoice_start_from',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Generate Invoice for no usage'),
                'no_usage_invoice',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_allow_invoice'
            ),
            array(
                gettext('Invoice Note'),
                'INPUT',
                array(
                    'name' => 'invoice_note',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                '',
                'HIDDEN',
                array(
                    'name' => ''
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
                    'name' => ''
                ),
                '',
                '',
                '',
                ''
            )
        );
        if($logintype != 1 && $invoiceconf['id'] == 1){
        $form[gettext('Portal personalization')] = array(

            array(
                gettext('Domain'),
                'INPUT',
                array(
                    'name' => 'domain',
                    'size' => '20',
                    'maxlength' => '100',
                    'class' => "text field medium"
                ),
                'xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Header'),
                'INPUT',
                array(
                    'name' => 'website_title',
                    'size' => '100',
                    'maxlength' => '100',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Footer'),
                'INPUT',
                array(
                    'name' => 'website_footer',
                    'size' => '200',
                    'maxlength' => '200',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),

            array(
                gettext('Logo') . (' (250 * 60)') . (' ('.gettext("Allowed Extentions JPG, PNG, JPEG").')'),
                'INPUT',
                array(
                    'name' => 'file',
                    'size' => '20',
                    'maxlength' => '100',
                    'class' => "btn btn-primary btn-file float-right",
                    'id' => 'uploadFile',
                    'type' => 'file',
                    'style' => 'margin-top:20px;',
                ),
                'class' => 'btn btn-primary btn-file float-right',
                'tOOL TIP',
                'Please Enter account number'
            ),

            $delete_logo,
            $image_path,
            array(
                gettext('Favicon' . (' (16 * 16)')) . (' ('.gettext("Allowed Extentions ICO, PNG, JPG, JPEG").')'),
                'INPUT',
                array(
                    'name' => 'file_fav',
                    'size' => '20',
                    'maxlength' => '100',
                    'class' => "btn btn-primary btn-file float-right",
                    'id' => 'uploadFav',
                    'type' => 'file',
                    'style' => 'margin-top:20px;',
                ),
                'class' => '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            $delete_fav,
            $image_fav
        );
    }
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'type' => 'submit',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Cancel'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'/invoices/invoice_conf_list/\')'
        );

        return $form;
    }

    function get_invoiceconf_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "invoice_conf_search"
            )
        );
        $form['Search'] = array(

            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'company_name[company_name]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'company_name[company_name-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Domain'),
                'INPUT',
                array(
                    'name' => 'domain[domain]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'domain[domain-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Country'),
                'country',
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
                'where_arr',
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
            'id' => "company_profile_search_btn",
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
}		
