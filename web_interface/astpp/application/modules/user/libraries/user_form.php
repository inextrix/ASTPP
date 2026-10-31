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

class User_form extends common
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function build_packages_list_for_user()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Name"),
                "310",
                "package_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Rate Group"),
                "250",
                "pricelist_id",
                "name",
                "pricelists",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Included Seconds"),
                "260",
                "includedseconds",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Status"),
                "160",
                "status",
                "status",
                "status",
                "get_status",
                "",
                "true",
                "center"
            )
        ));
        return $grid_field_arr;
    }

    function build_refill_list_for_user()
    {
        $grid_field_arr = json_encode(array(

            array(
                gettext("Date"),
                "225",
                "payment_date",
                "",
                "",
                ""
            ),
            array(
                gettext("Amount"),
                "250",
                "credit",
                "credit",
                "credit",
                "convert_to_currency"
            ),
            array(
                gettext("Refill By"),
                "230",
                "payment_by",
                "first_name,last_name,number",
                "accounts",
                "get_refill_by"
            ),
            array(
                gettext("Note"),
                "290",
                "notes",
                "",
                "",
                ""
            )
        ));
        return $grid_field_arr;
    }

    function build_emails_list_for_user()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "110",
                "date",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Subject"),
                "170",
                "subject",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("From"),
                "170",
                "from",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Body"),
                "550",
                "body",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Attachement"),
                "100",
                "attachment",
                "attachment",
                "attachment",
                "attachment_icons",
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
                "email_status",
                "",
                "true",
                "center"
            )
        ));
        return $grid_field_arr;
    }

    function build_user_emails_search()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "user_emails_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'date[]',
                    'id' => 'customer_cdr_from_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'date[date-date]'
            ),
            array(
                gettext('To Date'),
                'INPUT',
                array(
                    'name' => 'date[]',
                    'id' => 'customer_cdr_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'date[date-date]'
            ),
            array(
                gettext('Subject'),
                'INPUT',
                array(
                    'name' => 'subject[subject]',
                    '',
                    'id' => 'subject',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'subject[subject-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('From'),
                'INPUT',
                array(
                    'name' => 'from[from]',
                    '',
                    'id' => 'from',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'from[from-string]',
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
            'id' => "user_email_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success  float-right mx-2'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right'
        );
        return $form;
    }

    function get_userprofile_form_fields($dataArr = false)
    {
        if ($dataArr['id'] > 0) {
            $val = 'accounts.email.' . $dataArr['id'];
        } else {
            $val = 'accounts.email';
        }
        $uname = $this->CI->common->find_uniq_rendno(common_model::$global_config['system_config']['cardlength'], 'number', 'accounts');
        $password = $this->CI->common->generate_password();
        $logintype = $this->CI->session->userdata('logintype');
        $pin = ($logintype == '0') ? array(
            gettext('Pin'),
            'INPUT',
            array(
                'name' => 'pin',
                'size' => '20',
                'class' => "text field medium"
            ),
            'tOOL TIP',
            ''
        ) : array(
            '',
            'HIDDEN',
            array(
                'name' => 'Pin'
            ),
            '',
            '',
            '',
            ''
        );
        $form['forms'] = array(
            base_url() . 'user/user_myprofile/',
            array(
                "id" => "user_form",
                "name" => "user_form"
            )
        );

        $form[gettext('User Profile')] = array(
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
                    'name' => 'type',
                    'value' => '0'
                ),
                '',
                '',
                ''
            ),
            array(
                gettext('Account Number'),
                'INPUT',
                array(
                    'name' => 'number',
                    'value' => $uname,
                    'size' => '20',
                    'disabled' =>'true',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Company'),
                'INPUT',
                array(
                    'name' => 'company_name',
                    'size' => '15',
                    'class' => 'text field medium'
                ),
                'trim|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('First Name'),
                'INPUT',
                array(
                    'name' => 'first_name',
                    'id' => 'first_name',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Last Name'),
                'INPUT',
                array(
                    'name' => 'last_name',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'trim|alpha_numeric_space|xss_clean',
                'tOOL TIP',
                'Please Enter Password'
            ),
            array(
                gettext('Telephone 1'),
                'INPUT',
                array(
                    'name' => 'telephone_1',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'phn_number',
                'tOOL TIP',
                'Please Enter Password'
            ),
            array(
                gettext('Telephone 2'),
                'INPUT',
                array(
                    'name' => 'telephone_2',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'phn_number',
                'tOOL TIP',
                'Please Enter Password'
            ),
            array(
                gettext('Email'),
                'INPUT',
                array(
                    'name' => 'email',
                    'size' => '50',
                    'class' => "text field medium"
                ),
                'required|valid_email|is_unique[' . $val . ']',
                'tOOL TIP',
                'Please Enter Password'
            ),
            array(
                gettext('Address')." 1",
                'INPUT',
                array(
                    'name' => 'address_1',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter Password'
            ),
            array(
                gettext('Address')." 2",
                'INPUT',
                array(
                    'name' => 'address_2',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter Password'
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
                'Please Enter Password'
            ),
            array(
                gettext('Province/State'),
                'INPUT',
                array(
                    'name' => 'province',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter Password'
            ),
            array(
                gettext('Zip/Postal Code'),
                'INPUT',
                array(
                    'name' => 'postal_code',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                'Please Enter Password'
            ),
            array(
                gettext('Country'),
                array(
                    'name' => 'country_id',
                    'class' => 'country_id',
                    'disabled' =>true, 
                ),
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
                gettext('Timezone'),
                array(
                    'name' => 'timezone_id',
                    'class' => 'timezone_id'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'timezone_name',
                'timezone',
                'build_dropdown',
                '',
                ''
            ),
            array(
                gettext('Tax Number'),
                'INPUT',
                array(
                    'name' => 'tax_number',
                    'size' => '100',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            )
        );
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
            'onclick' => 'return redirect_page(\'/user/user_myprofile/\')'
        );
        return $form;
    }

    function get_userprofile_change_password()
    {

        // one rule for every portal password (libraries/astpp/password_policy.php); this chose by
        // password_type between strong_password (8+ with classes) and modrate_password (4 to 8)
        $new_password = array(
            gettext('New Password'),
            'PASSWORD',
            array(
                'name' => 'new_password',
                'size' => '20',
                'class' => "text field medium",
                'id' => 'new_password_show',
                'autocomplete' => 'new-password',
                'onmouseover' => 'seetext(new_password_show)',
                'onmouseout' => 'hidepassword(new_password_show)'
            ),
            'required|portal_password[session]|password_check_old[accounts]',
            'tOOL TIP',
            '',
            ''
        );

        $form['forms'] = array(
            base_url() . 'user/user_change_password/',
            array(
                "id" => "customer_alert_threshold",
                "name" => "user_change_password"
            )
        );
        $form[gettext('Change Password')] = array(
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
                gettext('Old Password'),
                'PASSWORD',
                array(
                    'name' => 'password',
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'old_password_show',
                    'onmouseover' => 'seetext(old_password_show)',
                    'onmouseout' => 'hidepassword(old_password_show)'
                ),
                'required|password_check[accounts]',
                'tOOL TIP',
                '',
                ''
            ),
            
            //Jaimin ASTPPCOM-844
            $new_password,
            //END

            array(
                gettext('Confirm Password'),
                'PASSWORD',
                array(
                    'name' => 'new_confirm_password',
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'password_show',
                    'onmouseover' => 'seetext(password_show)',
                    'onmouseout' => 'hidepassword(password_show)'
                ),
                "required|matches[new_password]",
                'tOOL TIP',
                '',
                ''
            )
        );
         $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Submit'),
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
            'onclick' => 'return redirect_page(\'/user/user_myprofile/\')'
        );
        return $form;
    }

    function build_user_invoices()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $url = ($this->CI->session->userdata('logintype') == 0) ? "/user/user_invoice_download/" : '/invoices/invoice_main_download/';
        $grid_field_arr = json_encode(array(
            array(
                gettext("Number"),
                "130",
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
                "130",
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
                "130",
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
                "due_date",
                "due_date",
                "due_date",
                "convert_to_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("To Date"),
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
                gettext("Amount") . "($currency)",
                "100",
                "id",
                "id",
                "id",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Outstanding Amount") . "<br>($currency)",
                "130",
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
                "130",
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

    function build_user_invoices_search()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "user_invoice_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'from_date[0]',
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
                    'name' => 'to_date[1]',
                    'id' => 'invoice_to_date',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '',
                'to_date[to_date-date]'
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'debit[debit]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'debit[debit-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Generated Date'),
                'INPUT',
                array(
                    'name' => 'generate_date[0]',
                    '',
                    'size' => '20',
                    'class' => "text field",
                    'id' => 'generate_date'
                ),
                '',
                'tOOL TIP',
                '',
                'generate_date[generate_date-date]'
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
            'id' => "user_invoice_search_btn",
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

    function build_user_subscription()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $grid_field_arr = json_encode(array(
            array(
                gettext("Name"),
                "335",
                "description",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Amount") . "($currency)",
                "335",
                "charge",
                "charge",
                "charge",
                "convert_to_currency",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Billing Cycle"),
                "335",
                "sweep_id",
                "sweep",
                "sweeplist",
                "get_field_name",
                "",
                "true",
                "center"
            )
        ));
        return $grid_field_arr;
    }

    function build_user_subscription_search()
    {
        $accountinfo = $this->CI->session->userdata("accountinfo");
        $form['forms'] = array(
            "",
            array(
                'id' => "user_subscription_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'description[description]',
                    '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '1',
                'description[description-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'charge[charge]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'Tool tips info',
                '1',
                'charge[charge-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Bill Cycle'),
                'sweep_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'sweep',
                'sweeplist',
                'build_dropdown',
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
            'id' => "user_subscriptions_button",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-line-parrot pull-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-line-sky pull-right margin-x-10'
        );

        return $form;
    }

    function build_user_didlist()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        if ($account_info['reseller_id'] > 0) {
            $billing_days = array(
                gettext("Billing Days"),
                "80",
                "productid",
                "billing_days",
                "products",
                "get_field_name",
                "",
                "true",
                "right"
            );
            $modified_date = array(
                gettext("Modified Date"),
                "100",
                "modified_date",
                "modified_date",
                "modified_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            );
        } else {
            $billing_days = array(
                gettext("Billing Days"),
                "80",
                "product_id",
                "billing_days",
                "products",
                "get_field_name",
                "",
                "true",
                "right"
            );
            $modified_date = array(
                gettext("Modified Date"),
                "100",
                "last_modified_date",
                "last_modified_date",
                "last_modified_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            );
        }

        $grid_field_arr = json_encode(array(
            array(
                gettext("DID"),
                "105",
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
                "110",
                "province",
                "",
                "dids",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("City"),
                "110",
                "city",
                "",
                "dids",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Cost / Min") . '</br>' . "($currency)",
                "120",
                "cost",
                "cost",
                "cost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Setup Fee") . '</br>' . "($currency)",
                "120",
                "setup_fee",
                "setup_fee",
                "setup_fee",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Price") . '</br>' . "($currency)",
                "100",
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
                "90",
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
                "30",
                "maxchannels",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            $billing_days,
            array(
                gettext("Is Purchased?"),
                "100",
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
                "90",
                "did_id_new",
                "did_id_new",
                "did_id_new",
                "get_call_type_grid",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Call Forward"),
                "90",
                "id",
                "id",
                "id",
                "build_did_forward",
                "",
                "true",
                "center"
            )/*,
            $modified_date*/
        ));
        return $grid_field_arr;
    }

    function build_user_didlist_search()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "user_did_search"
            )
        );
        $form[gettext('Search')] = array(
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
                gettext('Cost / Min'),
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
                gettext('Price'),
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
            'id' => "user_did_search_btn",
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

    function build_user_ipmap()
    {
        $grid_field_arr = json_encode(array(
            array(
                "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                "25",
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
                "140",
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
                "200",
                "ip",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Prefix"),
                "200",
                "prefix",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Created Date"),
                "150",
                "created_date",
                "created_date",
                "created_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            )
        ));
        return $grid_field_arr;
    }

    function build_user_ipmap_search()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "user_ipmap_search"
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
            'id' => "user_ipmap_search_btn",
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

    function build_user_sipdevices()
    {
        // Jaimin ASTPPCOM-984
            $sip_routing_status = $this->CI->db_model->countQuery ( "*", "addons", array    ("package_name" => "siprouting"));
            if(isset($sip_routing_status) && $sip_routing_status == 1){
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
                "90",
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
                "120",
                "password",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Caller Name"),
                "90",
                "effective_caller_id_name",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Caller Number"),
                "120",
                "effective_caller_id_number",
                "",
                "",
                "",
                "",
                "false",
                "right"
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
                gettext("Voicemail"),
                "90",
                "voicemail_enabled",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Status"),
                "70",
                "status",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
             // Jaimin ASTPPCOM-984
                $action_array
             // End
        ));
        return $grid_field_arr;
    }

    function build_user_sipdevices_search()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "user_sipdevices_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('User name'),
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
            'id' => "user_sipdevices_search_btn",
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

    function build_user_sipdevices_form($id = '')
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
        $form['forms'] = array(
            base_url() . 'user/user_sipdevices_save/',
            array(
                "id" => "user_sipdevices_form",
                "name" => "user_sipdevices_form"
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
                'Please Select Status',
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
                    'trim|xss_clean|numeric|integer',
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
                'Please Select Status',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('Local After Email'),
                'vm_keep_local_after_email',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Select Status',
                '',
                '',
                '',
                'set_sip_config_option'
            ),
            array(
                gettext('Send all Message'),
                'vm_send_all_message',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Select Status',
                '',
                '',
                '',
                'set_sip_config_option'
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

    function build_user_animap()
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
                gettext("Caller ID"),
                "500",
                "number",
                "",
                "",
                "",
                "",
                "true",
                "left"
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
                "170",
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
                "180",
                "status",
                "status",
                "ani_map",
                "get_status",
                "",
                "true",
                "center"
            )
        ));
        return $grid_field_arr;
    }

    function user_rates_list_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/user/user_rates_list_export/",
                'single',
                '',
                'export'
            )
        ));
        return $buttons_json;
    }

    function user_rates_list()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $grid_field_arr = json_encode(array(
            array(
                gettext("Code"),
                "150",
                "pattern",
                "pattern",
                "",
                "get_only_numeric_val",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Destination"),
                "200",
                "comment",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Connection Cost") . "($currency)",
                "200",
                "connectcost",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Grace Time"),
                "150",
                "includedseconds",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Cost / Min") . "($currency)",
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
                "120",
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
                "150",
                "inc",
                "",
                "",
                "",
                "",
                "true",
                "right"
            )
        ));
        return $grid_field_arr;
    }

    function user_rates_list_search()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "user_rates_list_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'pattern[pattern]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'pattern[pattern-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Destination'),
                'INPUT',
                array(
                    'name' => 'comment[comment]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'comment[comment-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Connection Cost'),
                'INPUT',
                array(
                    'name' => 'connectcost[connectcost]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'connectcost[connectcost-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Grace Time'),
                'INPUT',
                array(
                    'name' => 'includedseconds[includedseconds]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'includedseconds[includedseconds-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Cost / Min'),
                'INPUT',
                array(
                    'name' => 'cost[cost]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'cost[cost-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Initial Increment'),
                'INPUT',
                array(
                    'name' => 'init_inc[init_inc]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'init_inc[init_inc-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Increment'),
                'INPUT',
                array(
                    'name' => 'inc[inc]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'inc[inc-integer]',
                '',
                '',
                '',
                'search_int_type',
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
            'id' => "user_rates_list_search_btn",
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

    function user_alert_threshold($id = false)
    {
        if ($id > 0) {
            $email_address = 'accounts.notify_email.' . $id;
        } else {
            $email_address = 'accounts.notify_email';
        }

        $form['forms'] = array(
            base_url() . 'user/user_alert_threshold/',
            array(
                "id" => "customer_alert_threshold",
                "name" => "customer_alert_threshold"
            )
        );
        $form[gettext('Low Balance Alert Email')] = array(
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
                gettext('Enable Email Alerts ?'),
                array(
                    'name' => 'notify_flag',
                    'class' => 'notify_flag_drp'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'custom_status_recording'
            ),
            array(
                gettext('Low Balance Alert Level'),
                'INPUT',
                array(
                    'name' => 'notify_credit_limit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'currency_decimal',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Email Address'),
                'INPUT',
                array(
                    'name' => 'notify_email',
                    'size' => '50',
                    'class' => "text field medium"
                ),
                'valid_email|is_unique[' . $email_address . ']',
                'tOOL TIP',
                ''
            )
        );
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
            'onclick' => 'return redirect_page(\'/user/user_myprofile/\')'
        );
        return $form;
    }

    function build_cdrs_report($type)
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        if ($type == '0' || $type == '1') {
            $cost_array = array(
                gettext("Debit") . "($currency)",
                "140",
                "uniqueid",
                "uniqueid",
                "uniqueid",
                "convert_to_currency_cdrs_debit",
                "",
                "true",
                "right"
            );
        }
        if ($type == '3') {
            $cost_array = array(
                gettext("Debit") . "($currency)",
                "140",
                "cost",
                "cost",
                "cost",
                "convert_to_currency",
                "",
                "true",
                "right"
            );
        }
        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "100",
                "callstart",
                "callstart",
                "callstart",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Caller ID"),
                "100",
                "callerid",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Called Number"),
                "160",
                "callednum",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("SIP User"),
                "90",
                "sip_user",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Destination"),
                "160",
                "notes",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Duration"),
                "100",
                "billseconds",
                "user_cdrs_report_search",
                "billseconds",
                "convert_to_show_in",
                "",
                "true",
                "center"
            ),
            $cost_array,
            array(
                gettext("Disposition"),
                "140",
                "disposition",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Direction"),
                "150",
                "call_direction",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),

            array(
                gettext("Recording"),
                "127",
                "uid",
                "uid",
                "uid",
                "check_recording_exist",
                "",
                "false",
                "center"
            )
        ));
        return $grid_field_arr;
    }

    function build_cdrs_report_search($type)
    {
        if ($type == '0' || $type == '1') {
            $cost_array = array(
                gettext('Debit'),
                'INPUT',
                array(
                    'name' => 'debit[debit]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'debit[debit-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            );
        }
        if ($type == '3') {
            $cost_array = array(
                gettext('Debit'),
                'INPUT',
                array(
                    'name' => 'cost[cost]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'cost[cost-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            );
        }
        $form['forms'] = array(
            "",
            array(
                'id' => "user_cdrs_report_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'callstart[]',
                    'id' => 'customer_cdr_from_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'start_date[start_date-date]'
            ),
            array(
                gettext('To Date'),
                'INPUT',
                array(
                    'name' => 'callstart[]',
                    'id' => 'customer_cdr_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'end_date[end_date-date]'
            ),
            array(
                gettext('Caller ID'),
                'INPUT',
                array(
                    'name' => 'callerid[callerid]',
                    '',
                    'id' => 'first_name',
                    'size' => '15',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'callerid[callerid-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Called Number'),
                'INPUT',
                array(
                    'name' => 'callednum[callednum]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'callednum[callednum-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Destination'),
                'INPUT',
                array(
                    'name' => 'notes[notes]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'notes[notes-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Duration'),
                'INPUT',
                array(
                    'name' => 'billseconds[billseconds]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'billseconds[billseconds-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            $cost_array,
            array(
                gettext('Disposition'),
                'disposition',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_despostion'
            ),
            array(
                gettext('Call Type'),
                'calltype',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_calltype'
            ),
            array(
                gettext('Select Year'),
                'cdrs_year',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                'cdrs',
                'set_year_dropdown'
            ),
            array(
                gettext('Direction'),
                'call_direction',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                'cdrs',
                'direction_search_type'
            ),
            array(
                gettext('SIP User'),
                'INPUT',
                array(
                    'name' => 'sip_user[sip_user]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'sip_user[sip_user-string]',
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
        $form['display_in'] = array(
            'name' => 'search_in',
            "id" => "search_in",
            "function" => "search_report_in",
            "content" => gettext("Display records in"),
            'label_class' => "search_label col-md-7 text-right",
            "dropdown_class" => "form-control col-md-5 rm-col-md-12",
            "label_style" => "",
            "dropdown_style" => "background: #ddd; width: 21% !important;"
        );
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "user_cdr_search_btn",
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

    function build_cdrs_report_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/user/user_report_export/",
                'single',
                '',
                'export'
            )
        ));
        return $buttons_json;
    }

    function build_user_refill_report()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "100",
                "date",
                "date",
                "date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Amount")." ($currency)",
                "120",
                "amount",
                "amount",
                "amount",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Payment Method"),
                "90",
                "payment_method",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Transaction ID"),
                "150",
                "transaction_id",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Description"),
                "400",
                "description",
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

    function build_user_refill_report_search()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "user_refill_report_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'date[]',
                    'id' => 'customer_cdr_from_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'payment_date[payment_date-date]'
            ),
            array(
                gettext('To Date'),
                'INPUT',
                array(
                    'name' => 'date[]',
                    'id' => 'customer_cdr_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'payment_date[payment_date-date]'
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'credit[credit]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'Tool tips info',
                '1',
                'credit[credit-integer]',
                '',
                '',
                '',
                'search_int_type',
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
            'id' => "user_refill_report_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success  float-right mx-2'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right'
        );
        return $form;
    }

    function build_user_fund_transfer_form($number, $currency_id, $id)
    {
        $form['forms'] = array(
            base_url() . 'user/user_fund_transfer_save/',
            array(
                'id' => 'user_fund_transfer_form',
                'method' => 'POST',
                'class' => 'build_user_fund_transfer_frm',
                'name' => 'user_fund_transfer_form'
            )
        );
        $form[gettext('Send Credit')] = array(
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'id',
                    'value' => $id
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
                    'name' => 'account_currency',
                    'value' => $currency_id
                ),
                '',
                '',
                ''
            ),
            array(
                gettext('To Account'),
                'INPUT',
                array(
                    'name' => 'toaccountid',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|numeric',
                'tOOL TIP',
                'Please Enter to account number'
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'credit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|numeric',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Note'),
                'TEXTAREA',
                array(
                    'name' => 'notes',
                    'size' => '20',
                    'cols' => '63',
                    'rows' => '5',
                    'class' => "form-control col-md-12  text field medium mit-20",
                    'style' => 'height: 80px;'
                ),
                '',
                'tOOL TIP',
                ''
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => 'Transfer',
            'value' => gettext('save'),
            'id' => "submit",
            'type' => 'submit',
            'class' => 'btn btn-success'
        );
        return $form;
    }

    function build_user_opensips_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/user/user_opensips_add/",
                "popup"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/user/user_opensips_delete_multiple/"
            )
        ));
        return $buttons_json;
    }

    function build_user_opensips()
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
                gettext("Username"),
                "240",
                "username",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Password"),
                "240",
                "password",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Domain"),
                "240",
                "domain",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Action"),
                "200",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => 'user/user_opensips_edit/',
                        "mode" => "popup"
                    ),
                    "DELETE" => array(
                        "url" => 'user/user_opensips_delete/',
                        "mode" => "popup"
                    )
                )
            )
        ));
        return $grid_field_arr;
    }

    function build_user_opensips_form($id = false)
    {
        $val = $id > 0 ? 'subscriber.username.' . $id : 'subscriber.username';
        $uname_user = $this->CI->common->find_uniq_rendno('10', '', '');
        $password = $this->CI->common->generate_password();
        $accountinfo = $this->CI->session->userdata('accountinfo');
        $form['forms'] = array(
            base_url() . 'user/user_opensips_save/',
            array(
                "id" => "opensips_form",
                "name" => "opensips_form"
            )
        );
        $form['Opensips Device'] = array(
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
                    'value' => $accountinfo['number']
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('Username'),
                'INPUT',
                array(
                    'name' => 'username',
                    'size' => '20',
                    'id' => 'username',
                    'value' => $uname_user,
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter account number',
                '<i style="cursor:pointer; font-size: 17px; padding-left:10px; padding-top:6px;" title="Reset Password" class="change_number fa fa-refresh"></i>'
            ),
            array(
                gettext('Password'),
                'PASSWORD',
                array(
                    'name' => 'password',
                    'size' => '20',
                    'id' => 'password1',
                    'value' => $password,
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter Password',
                '<i style="cursor:pointer; font-size: 17px; padding-left:10px; padding-top:6px;" title="Reset Password" class="change_pass fa fa-refresh"></i>'
            ),
            array(
                gettext('Domain'),
                'INPUT',
                array(
                    'name' => 'domain',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
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
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'type' => 'button',
            'id' => 'submit',
            'class' => 'btn btn-line-parrot'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-line-sky margin-x-10',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function build_user_opensips_search()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "opensips_list_search"
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
                    'class' => "text field "
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
            'id' => "opensipsdevice_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-line-parrot pull-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-line-sky pull-right margin-x-10'
        );

        return $form;
    }

    function build_user_did_form()
    {
        $form['forms'] = array(
            base_url() . 'user/user_dids_action/edit/',
            array(
                "id" => "user_did_form",
                "name" => "user_did_form"
            )
        );
        $form[gettext('Edit')] = array(
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'free_didlist'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('DID'),
                'INPUT',
                array(
                    'name' => 'number',
                    'size' => '20',
                    'class' => "text field medium",
                    "readonly" => "true"
                ),
                'trim|required|is_numeric|xss_clean|integer',
                'tOOL TIP',
                'Please Enter account number'
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
                'set_call_type',
                ''
            ),
            array(
                gettext('Destination'),
                'INPUT',
                array(
                    'name' => 'extensions',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter Password'
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'type' => 'button',
            'id' => 'submit',
            'class' => 'btn btn-line-parrot'
        );
        return $form;
    }

    function build_provider_report_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                " fa fa-download fa-lg",
                "button_action",
                "/user/user_provider_cdrreport_export/",
                'single'
            )
        ));
        return $buttons_json;
    }

    function build_provider_report($type)
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        if ($type == '0' || $type == '1') {
            $cost_array = array(
                gettext("Debit")."($currency)",
                "140",
                "debit",
                "debit",
                "debit",
                "convert_to_currency",
                "",
                "true",
                "right"
            );
        }
        if ($type == '3') {
            $cost_array = array(
                gettext("Cost")."($currency)",
                "140",
                "cost",
                "cost",
                "cost",
                "convert_to_currency",
                "",
                "true",
                "right"
            );
        }
        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "170",
                "callstart",
                "callstart",
                "callstart",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Caller ID"),
                "110",
                "callerid",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Called Number"),
                "160",
                "callednum",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Destination"),
                "160",
                "notes",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Call Type"),
                "85",
                "calltype",
                "",
                "",
                ""
            ),
            array(
                gettext("Duration"),
                "140",
                "billseconds",
                "user_provider_cdrs_report_search",
                "billseconds",
                "convert_to_show_in",
                "",
                "true",
                "center"
            ),
            $cost_array,
            array(
                gettext("Disposition"),
                "100",
                "disposition",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Direction"),
                "100",
                "call_direction",
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

    function build_provider_report_search($type)
    {
        $cost_array = array(
            gettext('Cost '),
            'INPUT',
            array(
                'name' => 'cost[cost]',
                'value' => '',
                'size' => '20',
                'class' => "text field "
            ),
            '',
            'Tool tips info',
            '1',
            'cost[cost-integer]',
            '',
            '',
            '',
            'search_int_type',
            ''
        );
        $form['forms'] = array(
            "",
            array(
                'id' => "user_provider_cdrs_report_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                'From Date',
                'INPUT',
                array(
                    'name' => 'callstart[]',
                    'id' => 'customer_cdr_from_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'start_date[start_date-date]'
            ),
            array(
                gettext('To Date'),
                'INPUT',
                array(
                    'name' => 'callstart[]',
                    'id' => 'customer_cdr_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'end_date[end_date-date]'
            ),
            array(
                gettext('Caller ID'),
                'INPUT',
                array(
                    'name' => 'callerid[callerid]',
                    '',
                    'id' => 'first_name',
                    'size' => '15',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'callerid[callerid-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Called Number'),
                'INPUT',
                array(
                    'name' => 'callednum[callednum]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'callednum[callednum-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Destination '),
                'INPUT',
                array(
                    'name' => 'notes[notes]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'notes[notes-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Duration'),
                'INPUT',
                array(
                    'name' => 'billseconds[billseconds]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'billseconds[billseconds-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            $cost_array,
            array(
                gettext('Disposition'),
                'disposition',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_despostion'
            ),
            array(
                gettext('Call Type'),
                'calltype',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_calltype'
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
        $form['display_in'] = array(
            'name' => 'search_in',
            "id" => "search_in",
            "function" => "search_report_in",
            "content" => gettext("Display records in"),
            'label_class' => "search_label col-md-7 text-right",
            "dropdown_class" => "form-control col-md-5 rm-col-md-12",
            "label_style" => "font-size:13px;",
            "dropdown_style" => "background: #ddd; width: 21% !important;"
        );
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "user_cdr_search_btn",
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

    function build_products_list_for_user()
    {
        $account_info = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        if (($account_info['type'] == 3) || ($account_info['type'] == 0)) {
            $action_array = array(
                gettext("Action"),
                "120",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "user/user_orders_complete/",
                        "mode" => "single"
                    )
                ),
                "false"
            );
        }

        $grid_field_arr = json_encode(array(
            array(
                gettext("Name"),
                "150",
                "product_id",
                "name",
                "products",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Date"),
                "240",
                "order_date",
                "order_date",
                "order_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Order"),
                "240",
                "id",
                "order_id",
                "orders",
                "get_order_id",
                "EDITABLE",
                "true",
                "right"
            ),

            array(
                gettext("Payment Method"),
                "150",
                "payment_gateway",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Setup Fee")." ($currency)",
                "150",
                "setup_fee",
                "setup_fee",
                "setup_fee",
                "convert_to_currency_account",
                "",
                "true",
                "center"
            ),

            array(
                gettext("Price")." ($currency)",
                "150",
                "price",
                "price",
                "price",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Status"),
                "100",
                "payment_status",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            $action_array
        ));
        return $grid_field_arr;
    }

    function get_user_product_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "orders_list_search"
            )
        );
        $form['Search'] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'order_date[]',
                    'id' => 'billing_date_from_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'order_date[order_date-date]'
            ),
            array(
                gettext('To Date'),
                'INPUT',
                array(
                    'name' => 'order_date[]',
                    'id' => 'billing_date_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'order_date[order_date-date]'
            ),
            array(
                gettext('Order'),
                'INPUT',
                array(
                    'name' => 'order_id[order_id]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'order_id[order_id-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Status'),
                'payment_status',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'payment_status',
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
            'id' => "user_order_search_btn",
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
}
?>
