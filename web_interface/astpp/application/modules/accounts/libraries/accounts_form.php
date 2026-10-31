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

class Accounts_form extends common
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function get_customer_form_fields($entity_type = false, $id = false, $reseller_id = '')
    {
        $account_data = $this->CI->session->userdata("accountinfo");
        $reseller_id = ($reseller_id > 0) ? $reseller_id : (($account_data['type'] == 1 || $account_data['type'] == 5) ? $account_data['id'] : 0);
        $expiry_date = gmdate('Y-m-d H:i:s', strtotime('+10 years'));
        $readable = FALSE;
        $type = $entity_type == 'customer' ? 0 : 3;
        if (isset(common_model::$global_config['system_config']['minimum_accountlength'])) {
            $uname = $this->CI->common->find_uniq_rendno_customer_length(common_model::$global_config['system_config']['minimum_accountlength'], common_model::$global_config['system_config']['maximum_accountlength'], 'number', 'accounts');
        } else {
            $uname = $this->CI->common->find_uniq_rendno_customer(common_model::$global_config['system_config']['cardlength'], 'number', 'accounts');
        }
        $uname_user = $this->CI->common->find_uniq_rendno('10', 'number', 'accounts');
        $currency_info = (array) $this->CI->db->get_where("currency", array(
            "currency" => Common_model::$global_config['system_config']['base_currency']
        ))->first_row();
        $country_id = Common_model::$global_config['system_config']['country'];
        $currency_id = $currency_info['id'];
        $timezone_id = Common_model::$global_config['system_config']['default_timezone'];
        $notifications = Common_model::$global_config['system_config']['notifications'];
        $paypal_permission = Common_model::$global_config['system_config']['paypal_permission'];
        $sip_device_arr = null;
        $taxes_array = null;
        $expiry_date_array = null;
        $first_used = null;
        $account_valid_days = null;
        $expiry_date_array = null;
        $status = null;
        $localization_arr = '';
        $allow_lc_charge = null;
        $allow_loss_less_routing = null;
        $allow_recording = null;
        $allow_ip_management = null;
        $balnce_below = null;
        $reseller = null;
        $change_account_number = '';
        $change_password = '';
        $password = '';
        $change_pin = '';
        $pin_number = '';
        $pin_generate = Common_model::$global_config['system_config']['generate_pin'];
        if ($pin_generate == 0) {
            $numberlength = common_model::$global_config['system_config']['pinlength'];
            $numberlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['pinlength'];
            $pin_number = random_int((int) pow(10, $numberlength - 1), (int) pow(10, $numberlength) - 1);
        }
        $account_val = 'accounts.number';
        $cps = Common_model::$global_config['system_config']['cps'];
        $concurrent_calls = Common_model::$global_config['system_config']['maxchannels'];
        $pricelist_id = Common_model::$global_config['system_config']['default_signup_rategroup'];
        $smsrategroup_array = null;
        if (isset(common_model::$global_config['system_config']['signup_sms_pricelist_id'])) {
            $smsrategroup_array = array(
                gettext('SMS Rate Group'),
                'sms_pricelist_id',
                'SELECT',
                '',
                array(
                    "name" => "sms_pricelist_id",
                    "rules" => "dropdown"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'sms_pricelists',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => $reseller_id
                )
            );
        }
        $allow_local_calls = array(
            gettext('Allow Local Calls'),
            'local_call',
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            '',
            '',
            '',
            'custom_status'
        );
        $localization_arr = array(
            gettext('Localization'),
            array(
                'name' => 'localization_id',
                'class' => 'localization_id'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            'id',
            'name',
            'localization',
            // Kinjal ASTPPCOM-986 Start
            'build_dropdown',
            // Kinjal ASTPPCOM-986 END
            'where_arr',
            array(
                "status" => "0"
            )
        );
       
        if (! $entity_type) {
            $entity_type = 'customer';
        }
        $params = array(
            'name' => 'number',
            'value' => $uname,
            'size' => '20',
            'class' => "text field medium",
            'id' => 'number'
        );
        $account_number_editable = Common_model::$global_config['system_config']['account_number_editable'];
        if (isset(common_model::$global_config['system_config']['minimum_accountlength'])) {
            if ($account_number_editable == 0) {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'value' => $uname,
                        'size' => '20',
                        'class' => "text field medium",
                        'id' => 'number'
                    ),
                    'required|integer|greater_than[0]|is_unique[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    ' <i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            } else {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'value' => $uname,
                        'size' => '20',
                        'class' => "text field medium",
                        'id' => 'number',
                        'readonly' => true
                    ),
                    'required|integer|greater_than[0]|is_unique[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    ' <i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            }
        } else {
            if ($account_number_editable == 0) {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'value' => $uname,
                        'size' => '20',
                        'class' => "text field medium",
                        'id' => 'number'
                    ),
                    'required|integer|greater_than[0]|is_unique[' . $account_val . ']|is_match_number[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    ' <i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            } else {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'value' => $uname,
                        'size' => '20',
                        'class' => "text field medium",
                        'id' => 'number',
                        'readonly' => true
                    ),
                    'required|integer|greater_than[0]|is_unique[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    ' <i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            }
        }
        if ($account_data['type'] == - 1) {
            if ($entity_type == 'customer') {
                $reseller = array(
                    gettext('Reseller'),
                    array(
                        'name' => 'reseller_id',
                        'class' => 'reseller',
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
            } else {
                $notifications = 1;
            }
        }
        if ($id > 0) {
            $readable = 'disabled';
            $val = 'accounts.email.' . $id;
            $account_val = 'accounts.number.' . $id;
            $taxes_array = array(
                gettext('Taxes'),
                "tax_id",
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'taxes_description',
                'taxes',
                'build_dropdown',
                'where_arr',
                array(
                    'status' => 0
                ),
                'multi'
            );
            $account = array(
                gettext('Account'),
                'INPUT',
                array(
                    'name' => 'number',
                    'value' => $uname,
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'number',
                    'readonly' => true
                ),
                'required|integer|greater_than[0]|is_unique[' . $account_val . ']',
                'tOOL TIP',
                '',
                ''
            );
            $password = array(
                gettext('Password'),
                'PASSWORD',
                array(
                    'name' => 'password',
                    'id' => 'password_show',
                    'onmouseover' => 'seetext(password_show)',
                    'onmouseout' => 'hidepassword(password_show)',
                    'placeholder' => gettext('unchanged'),
                    'autocomplete' => 'new-password',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'portal_password', // empty keeps the stored password; rule in libraries/astpp/password_policy.php
                'tOOL TIP',
                ''
            );
            $reseller = array(
                gettext('Reseller'),
                array(
                    'name' => 'reseller_id',
                    'disabled' => $readable,
                    'class' => 'reseller',
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
            $expiry_date_array = array(
                gettext('Expiry Date'),
                'INPUT',
                array(
                    'name' => 'expiry',
                    'disabled' => $readable,
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            );
            $first_used = array(
                gettext('First Used'),
                'INPUT',
                array(
                    'name' => 'first_used',
                    'size' => '20',
                    'readonly' => true,
                    'class' => "text field medium",
                    'value' => '0000-00-00 00:00:00'
                ),
                '',
                'tOOL TIP',
                ''
            );

            $account_valid_days = array(
                gettext('Account Valid Days'),
                'INPUT',
                array(
                    'name' => 'validfordays',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|numeric|xss_clean',
                'tOOL TIP',
                ''
            );
            if ($account_data['type'] == - 1) {
                $localization_arr = array(
                    gettext('Localization'),
                    array(
                        'name' => 'localization_id',
                        'class' => 'localization_id'
                    ),
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'localization',
                    // Kinjal ASTPPCOM-986 Start
                    'build_dropdown',
                    // Kinjal ASTPPCOM-986 END
                    'where_arr',
                    array(
                        "status" => "0"
                    )
                );
               
            }
            $status = array(
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
                'set_status'
            );
            $allow_lc_charge = array(
                gettext('LC Charge / Min'),
                'INPUT',
                array(
                    'name' => 'charge_per_min',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|greater_than[-1]',
                'tOOL TIP',
                ''
            );
            $allow_recording = array(
                gettext('Allow Recording'),
                'is_recording',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status_recording'
            );
            $allow_ip_management = array(
                gettext('Allow IP Management'),
                'allow_ip_management',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status'
            );
            $balnce_below = array(
                gettext('Balance Below'),
                'INPUT',
                array(
                    'name' => 'notify_credit_limit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'currency_decimal',
                'tOOL TIP',
                ''
            );
        } else {
            $val = 'accounts.email';
            $account_val = 'accounts.number';
            $password = $this->CI->common->generate_password();
            $password = array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'password',
                    'value' => $password,
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'password',
                    'onmouseover' => 'seetext(password)',
                    'onmouseout' => 'hidepassword(password)'
                ),
                'required|portal_password',
                'tOOL TIP',
                '',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_pass align-self-end text-success fa fa-refresh" ></i>'
            );
            $change_password = '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" onmouseover="seetext(password)" onmouseout="hidepassword(password)" class="change_pass align-self-end text-success fa fa-refresh" ></i>';
            $change_pin = '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Pin" class="change_pin align-self-end text-success fa fa-refresh" ></i>';

            $sip_device_arr = array(
                'Create SIP Device',
                array(
                    "name" => "sip_device_flag",
                    "value" => Common_model::$global_config['system_config']['create_sipdevice']
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_prorate'
            );
        }
        if (isset($id) && $id != '') {
            $form['forms'] = array(
                base_url() . 'accounts/' . $entity_type . '_save/' . $id . "/",
                array(
                    "id" => "customer_form",
                    "name" => "customer_form"
                )
            );
        } else {
            $form['forms'] = array(
                base_url() . 'accounts/' . $entity_type . '_save/',
                array(
                    "id" => "customer_form",
                    "name" => "customer_form"
                )
            );
        }
        $form[gettext('Panel Access')] = array(
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
                    'value' => $type
                ),
                '',
                '',
                ''
            ),
            $reseller,
            $account,
            $password,
            array(
                gettext('Pin'),
                'INPUT',
                array(
                    'name' => 'pin',
                    'value' => $pin_number,
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'change_pin'
                ),
                'is_numeric',
                'tOOL TIP',
                '',
                $change_pin
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
                ''
            ),
            $sip_device_arr,
            $first_used,
            $account_valid_days,
            $expiry_date_array,
            $status
        );

        $form[gettext('Account Settings')] = array(

            array(
                gettext('Concurrent Calls'),
                'INPUT',
                array(
                    'name' => 'maxchannels',
                    'size' => '20',
                    'class' => "text field medium",
                    'value' => $concurrent_calls
                ),
                'numeric',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('CPS'),
                'INPUT',
                array(
                    'name' => 'cps',
                    'size' => '20',
                    'class' => "text field medium",
                    'value' => $cps
                ),
                'numeric',
                'tOOL TIP',
                ''
            ),
            $localization_arr,
            $allow_local_calls,
            $allow_lc_charge,
            $allow_recording,
            $allow_ip_management,
            array(
                gettext('Notifications'),
                array(
                    'name' => 'notifications',
                    'class' => 'notifications',
                    'id' => 'notifications',
                    'value' => $notifications
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status'
            ),
            array(
                gettext('Payment Gateway Permission'),
                array(
                    "name" => "paypal_permission",
                    "class" => "paypal_permission",
                    "value" => $paypal_permission
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status'
            )
        );
        $form[gettext('Profile')] = array(
            array(
                gettext('First Name'),
                'INPUT',
                array(
                    'name' => 'first_name',
                    'id' => 'first_name',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'required',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Last Name'),
                'INPUT',
                array(
                    'name' => 'last_name',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                ''
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
                gettext('Phone'),
                'INPUT',
                array(
                    'name' => 'telephone_1',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'phn_number',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Notification Email'),
                'INPUT',
                array(
                    'name' => 'notification_email',
                    'size' => '50',
                    'class' => "text field medium"
                ),
                'valid_email',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Address'). ' 1',
                'INPUT',
                array(
                    'name' => 'address_1',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Address').' 2',
                'INPUT',
                array(
                    'name' => 'address_2',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
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
                ''
            ),
            array(
                gettext('Province'),
                'INPUT',
                array(
                    'name' => 'province',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Zip Code'),
                'INPUT',
                array(
                    'name' => 'postal_code',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Country'),
                array(
                    'name' => 'country_id',
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
                gettext('Timezone'),
                array(
                    'name' => 'timezone_id',
                    'class' => 'timezone_id',
                    'value' => $timezone_id
                ),
                'SELECT',
                '',
                array(
                    "name" => "timezone_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'timezone_name,gmtoffset',
                'timezone',
                'build_concat_dropdown_timezone',
                '',
                ''
            ),
            array(
                gettext('Currency'),
                array(
                    'name' => 'currency_id',
                    'class' => 'currency_id',
                    'value' => $currency_id
                ),
                'SELECT',
                '',
                array(
                    "name" => "currency_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'currencyname,currency',
                'currency',
                'build_concat_dropdown_currency',
                '',
                array()
            )
        );

        $form[gettext('Billing Settings')] = array(
            array(
                gettext('Account Type'),
                array(
                    'name' => 'posttoexternal',
                    'disabled' => $readable,
                    'class' => 'posttoexternal',
                    'id' => 'posttoexternal'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_account_type'
            ),

            array(
                gettext('Credit Limit'),
                'INPUT',
                array(
                    'name' => 'credit_limit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'currency_decimal',
                'tOOL TIP',
                ''
            ),

            array(
                gettext('Rate Group'),
                array(
                    'name' => 'pricelist_id',
                    'id' => 'pricelist_id'
                ),
                'SELECT',
                '',
                array(
                    "name" => "pricelist_id",
                    "rules" => "dropdown"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'pricelists',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => $reseller_id
                )
            ),
            $smsrategroup_array,
            array(
                gettext('Billing Schedule'),
                array(
                    'name' => 'sweep_id',
                    'class' => 'sweep_id',
                    'id' => 'sweep_id'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                'id',
                'sweep',
                'sweeplist',
                'build_dropdown',
                '',
                ''
            ),
            array(
                gettext('Billing Day'),
                array(
                    "name" => 'invoice_day',
                    "class" => "invoice_day",
			"id" => "invoice_day"
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_invoice_option'
            ),
            $taxes_array,
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
            ),
            array(
                gettext('Generate Invoice'),
                array(
                    "name" => "generate_invoice",
                    "class" => "generate_invoice"
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status'
            ),
            array(
                gettext('Invoice Note'),
                'TEXTAREA',
                array(
                    'name' => 'invoice_note',
                    'size' => '20',
                    'cols' => '50',
                    // ASTPPCOM-973_kanu_start
                    'maxlength' => '110',
                    // ASTPPCOM-973_kanu_end
                    'rows' => '3',
                    'class' => "form-control form-control-lg mit-20 text col-md-12 field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Reference'),
                'INPUT',
                array(
                    'name' => 'reference',
                    'id' => 'reference',
                    'size' => '15',
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
            'onclick' => 'return redirect_page(\'/accounts/customer_list/\')'
        );
        return $form;
    }

    function customer_alert_threshold($entity_type)
    {
        $form['forms'] = array(
            base_url() . 'accounts/' . $entity_type . '_alert_threshold_save/' . $entity_type . "/",
            array(
                "id" => "customer_alert_threshold",
                "name" => "customer_alert_threshold"
            )
        );
        $form[gettext('Alert Threshold')] = array(
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
                gettext('Email Alerts ?'),
                'notify_flag',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'custom_status'
            ),
            array(
                gettext('Balance Below'),
                'INPUT',
                array(
                    'name' => 'notify_credit_limit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'currency_decimal',
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
        return $form;
    }

    function customer_bulk_generate_form()
    {
        $accountinfo = $this->CI->session->userdata("accountinfo");
        $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : 0;
        $currency_info = (array) $this->CI->db->get_where("currency", array(
            "currency" => Common_model::$global_config['system_config']['base_currency']
        ))->first_row();
        $country_id = Common_model::$global_config['system_config']['country'];
        $currency_id = $currency_info['id'];
        $timezone_id = Common_model::$global_config['system_config']['default_timezone'];
        $balance = Common_model::$global_config['system_config']['balance'];
        $validfordays = Common_model::$global_config['system_config']['validfordays'];
        $pin = Common_model::$global_config['system_config']['generate_pin'] == 0 ? $this->CI->common->find_uniq_rendno(Common_model::$global_config['system_config']['pinlength'], 'pin', 'accounts') : '';
        $form['forms'] = array(
            base_url() . 'accounts/customer_bulk_save/',
            array(
                "id" => "customer_bulk_form",
                "name" => "customer_bulk_form"
            )
        );
        $form[gettext('Account Details')] = array(
            array(
                gettext('Account Count'),
                'INPUT',
                array(
                    'name' => 'count',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|is_numeric|greater_than[0]|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Start prefix'),
                'INPUT',
                array(
                    'name' => 'prefix',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|is_numeric|greater_than[0]|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Account Number Length'),
                'INPUT',
                array(
                    'name' => 'account_length',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|greater_than[0]|required|is_numeric|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Generate Pin'),
                array(
                    "name" => 'pin',
                    "id" => "pin",
                    "class" => "pin",
                    "value" => $pin
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_prorate'
            ),
            array(
                gettext('Valid Days'),
                'INPUT',
                array(
                    'name' => 'validfordays',
                    'size' => '20',
                    'class' => "text field medium",
                    'value' => $validfordays
                ),
                'trim|numeric|greater_than[0]|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Currency'),
                array(
                    'name' => 'currency_id',
                    'class' => 'currency_id',
                    'value' => $currency_id
                ),
                'SELECT',
                '',
                array(
                    "name" => "currency_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'currencyname,currency',
                'currency',
                'build_concat_dropdown_currency',
                '',
                array()
            ),
            array(
                gettext('Country'),
                array(
                    'name' => 'country_id',
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
                gettext('Timezone'),
                array(
                    'name' => 'timezone_id',
                    'class' => 'timezone_id',
                    'value' => $timezone_id
                ),
                'SELECT',
                '',
                array(
                    "name" => "timezone_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'timezone_name',
                'timezone',
                'build_concat_dropdown_timezone',
                '',
                ''
            )
        );
        $form[gettext('Billing Settings')] = array(

            array(
                gettext('Account Type'),
                'posttoexternal',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_account_type'
            ),
            array(
                gettext('Balance'),
                'INPUT',
                array(
                    'name' => 'balance',
                    'size' => '20',
                    'class' => "text field medium",
                    "value" => $balance
                ),
                'trim|numeric|currency_decimal|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Credit Limit'),
                'INPUT',
                array(
                    'name' => 'credit_limit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|currency_decimal|xss_clean',
                'tOOL TIP',
                ''
            ),
           
            array(
                gettext('Rate Group'),
                array(
                    'name' => 'pricelist_id',
                    'class' => 'pricelist_id'
                ),
                'SELECT',
                '',
                "required",
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'pricelists',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => $reseller_id
                )
            ),
            
            array(
                gettext('Billing Schedule'),
                array(
                    'name' => 'sweep_id',
                    'id' => 'sweep_id',
                    'class' => 'sweep_id'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                'id',
                'sweep',
                'sweeplist',
                'build_dropdown',
                '',
                ''
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function get_customer_payment_fields($currency, $number, $currency_id, $id)
    {
        $form['forms'] = array(
            base_url() . 'accounts/customer_payment_save/',
            array(
                'id' => 'acccount_charges_form',
                'method' => 'POST',
                'name' => 'acccount_charges_form'
            )
        );
        $form[gettext('​Refill information')] = array(
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
                gettext('Account'),
                'INPUT',
                array(
                    'name' => 'accountid',
                    'size' => '20',
                    'value' => $number,
                    'readonly' => true,
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'credit',
                    'size' => '20',
                    'class' => "text col-md-5 field medium"
                ),
                'trim|required|greater_than[0]',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Type'),
                'payment_type',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_payment_type'
            ),
            array(
                gettext('Note'),
                'TEXTAREA',
                array(
                    'name' => 'notes',
                    'size' => '20',
                    'rows' => '3',
                    'class' => "form-control form-control-lg mt-4 text col-md-12 field medium"
                ),
                '',
                'tOOL TIP',
                ''
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Process'),
            'value' => 'save',
            'id' => "submit",
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function get_form_reseller_fields($id = false)
    {
        $accountinfo = $this->CI->session->userdata("accountinfo");
        $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : 0;
        $reseller = '';
        $role = '';
        $readable = false;
        $invoice_config = '';
        $new_password = '';
        $val = 'accounts.email';
        $account_val = 'accounts.number';
        $taxes_arr = "";
        $account_number = '';
        if (isset(common_model::$global_config['system_config']['minimum_accountlength'])) {
            $account_number = $this->CI->common->find_uniq_rendno_customer_length(common_model::$global_config['system_config']['minimum_accountlength'], common_model::$global_config['system_config']['maximum_accountlength'], 'number', 'accounts');
        } else {
            $account_number = $this->CI->common->find_uniq_rendno_customer(common_model::$global_config['system_config']['cardlength'], 'number', 'accounts');
        }
        $reg_url = '';
        $status = '';
        $permission = '';
        $balnce_below = '';
        $cli_pool_array = '';
        $low_balance_alert = '';
        $low_balance_alert_email = '';
        $allow_loss_less_routing = '';
        $is_distributor = '';
        $currency_info = (array) $this->CI->db->get_where("currency", array(
            "currency" => Common_model::$global_config['system_config']['base_currency']
        ))->first_row();
        $country_id = Common_model::$global_config['system_config']['country'];
        $currency_id = $currency_info['id'];
        $timezone_id = Common_model::$global_config['system_config']['default_timezone'];
        $cps = Common_model::$global_config['system_config']['cps'];
        $concurrent_calls = Common_model::$global_config['system_config']['maxchannels'];
        $notifications = Common_model::$global_config['system_config']['notifications'];
        $account_number_editable = Common_model::$global_config['system_config']['account_number_editable'];
        if (isset(common_model::$global_config['system_config']['minimum_accountlength'])) {
            if ($account_number_editable == 0) {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'value' => $account_number,
                        'size' => '20',
                        'class' => "text field medium",
                        'id' => 'number'
                    ),
                    'required|integer|greater_than[0]|is_unique[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    ' <i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            } else {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'value' => $account_number,
                        'size' => '20',
                        'class' => "text field medium",
                        'id' => 'number',
                        'readonly' => true
                    ),
                    'required|integer|greater_than[0]|is_unique[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    ' <i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            }
        } else {
            if ($account_number_editable == 0) {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'value' => $account_number,
                        'size' => '20',
                        'class' => "text field medium",
                        'id' => 'number'
                    ),
                    'required|integer|greater_than[0]|is_unique[' . $account_val . ']|is_match_number[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    ' <i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            } else {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number',
                        'value' => $account_number,
                        'size' => '20',
                        'class' => "text field medium",
                        'id' => 'number',
                        'readonly' => true
                    ),
                    'required|integer|greater_than[0]|is_unique[' . $account_val . ']|is_match_number[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    ' <i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            }
        }
        if ($accountinfo['type'] == - 1 || $accountinfo['type'] == 2) {
            $permission = array(
                gettext('Role'),
                'permission_id',
                'SELECT',
                '',
                array(
                    "name" => "permission_id",
                    "class" => "permission_id",
                    "id" => "permission_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'permissions',
                'build_dropdown',
                'where_arr',
                array(
                    "login_type" => "1"
                )
            );
            $reseller = array(
                gettext('Reseller'),
                array(
                    'name' => 'reseller_id',
                    'class' => 'reseller',
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
            $is_distributor = array(
                gettext('Reseller Type'),
                array(
                    "name" => 'is_distributor',
                    "id" => 'is_distributor',
                    "class" => 'is_distributor'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'reseller_type_dropdown'
            );
        }

        // AD :sms_addons
        $smsrategroup_array = null;
        if (isset(common_model::$global_config['system_config']['signup_sms_pricelist_id'])) {
            $smsrategroup_array = array(
                gettext('SMS Rate Group'),
                'sms_pricelist_id',
                'SELECT',
                '',
                array(
                    "name" => "sms_pricelist_id",
                    "rules" => "dropdown"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'sms_pricelists',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => $loginid
                )
            );
        }
        // AD : sms_addons end

        $account_type = array(
            gettext('Account Type'),
            array(
                'name' => 'posttoexternal',
                'disabled' => $readable,
                'class' => 'posttoexternal',
                'id' => 'posttoexternal'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            '',
            '',
            '',
            'set_account_type'
        );
        $credit_limit = array(
            gettext('Credit Limit'),
            'INPUT',
            array(
                'name' => 'credit_limit',
                'size' => '20',
                'class' => "text field medium"
            ),
            '',
            'tOOL TIP',
            ''
        );

        $invoice_note = array(
            gettext('Invoice Note'),
            'TEXTAREA',
            array(
                'name' => 'invoice_note',
                'size' => '20',
                'cols' => '50',
                'rows' => '3',
                'class' => "form-control form-control-lg mit-20 text col-md-12 field medium"
            ),
            '',
            'tOOL TIP',
            ''
        );
        $reference = array(
            gettext('Reference'),
            'INPUT',
            array(
                'name' => 'reference',
                'id' => 'reference',
                'size' => '15',
                'class' => "text field medium"
            ),
            '',
            'tOOL TIP',
            ''
        );
        if ($id > 0) {
            $val = 'accounts.email.' . $id;
            $account_val = 'accounts.number.' . $id;
            $readable = 'disabled';
            $account = array(
                gettext('Account'),
                'INPUT',
                array(
                    'name' => 'number',
                    'value' => $account_number,
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'number',
                    'readonly' => true
                ),
                'required|integer|greater_than[0]|is_unique[' . $account_val . ']',
                'tOOL TIP',
                '',
                ''
            );
            $password = array(
                gettext('Password'),
                'PASSWORD',
                array(
                    'name' => 'password',
                    'id' => 'password_show',
                    'onmouseover' => 'seetext(password_show)',
                    'onmouseout' => 'hidepassword(password_show)',
                    'placeholder' => gettext('unchanged'),
                    'autocomplete' => 'new-password',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'portal_password', // empty keeps the stored password; rule in libraries/astpp/password_policy.php
                'tOOL TIP',
                ''
            );
            $reg_url = array(
                gettext('Registration URL'),
                'INPUT',
                array(
                    'name' => 'registration_url',
                    'size' => '20',
                    'readonly' => true,
                    'class' => "text field medium"
                ),
                'tOOL TIP',
                ''
            );
            $reseller = array(
                gettext('Reseller'),
                array(
                    'name' => 'reseller_id',
                    'disabled' => $readable,
                    'class' => 'reseller',
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
            $status = array(
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
                'set_status'
            );
            $taxes_arr = array(
                gettext('Taxes'),
                "tax_id",
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'taxes_description',
                'taxes',
                'build_dropdown',
                'where_arr',
                array(
                    'status' => 0
                ),
                'multi'
            );
            $balnce_below = array(
                gettext('Balance Below'),
                'INPUT',
                array(
                    'name' => 'notify_credit_limit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'currency_decimal',
                'tOOL TIP',
                ''
            );
            $low_balance_alert = array(
                gettext('Low balance Alert?'),
                'notify_flag',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status'
            );
            $balnce_below = array(
                gettext('Balance Below'),
                'INPUT',
                array(
                    'name' => 'notify_credit_limit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'currency_decimal',
                'tOOL TIP',
                ''
            );
            $low_balance_alert_email = array(
                gettext('Low Balance Alert Email'),
                'notify_flag',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status'
            );
        } else {
            $invoice_config = array(
                gettext('Use same credential for Invoice Config'),
                'invoice_config_flag',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_prorate'
            );
            $password = $this->CI->common->generate_password();
            $password = array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'password',
                    'value' => $password,
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'password',
                    'onmouseover' => 'seetext(password)',
                    'onmouseout' => 'hidepassword(password)'
                ),
                'required|portal_password',
                'tOOL TIP',
                '',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_pass align-self-end text-success fa fa-refresh" ></i>'
            );
        }
        $form['forms'] = array(
            base_url() . 'accounts/reseller_save/',
            array(
                "id" => "reseller_form",
                "name" => "reseller_form"
            )
        );
        $form[gettext('Panel Access')] = array(
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
                    'value' => '1'
                ),
                '',
                '',
                ''
            ),
            $reseller,
            $account,
            $password,
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
                ''
            ),
            $permission,
            $reg_url,
            $status,
            $is_distributor
        );
        $form[gettext('Account Settings')] = array(
            array(
                gettext('Concurrent Calls'),
                'INPUT',
                array(
                    'name' => 'maxchannels',
                    'size' => '20',
                    'class' => "text field medium",
                    "value" => $id == 0 ? $concurrent_calls : ''
                ),
                'numeric',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('CPS'),
                'INPUT',
                array(
                    'name' => 'cps',
                    'size' => '20',
                    'class' => "text field medium",
                    "value" => $id == 0 ? $cps : ''
                ),
                'numeric',
                'tOOL TIP',
                ''
            ),
            $low_balance_alert,
            $balnce_below,
            $low_balance_alert_email,
            array(
                gettext('Notifications'),
                array(
                    'name' => 'notifications',
                    'class' => 'notifications',
                    'id' => 'notifications',
                    'value' => $notifications
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'custom_status'
            )
        );
        $form[gettext('Profile')] = array(

            array(
                gettext('First Name'),
                'INPUT',
                array(
                    'name' => 'first_name',
                    'id' => 'first_name',
                    'size' => '50',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Last Name'),
                'INPUT',
                array(
                    'name' => 'last_name',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Company'),
                'INPUT',
                array(
                    'name' => 'company_name',
                    'size' => '50',
                    'class' => 'text field medium'
                ),
                'trim|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Phone'),
                'INPUT',
                array(
                    'name' => 'telephone_1',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'trim|xss_clean|is_numeric',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Notification Email'),
                'INPUT',
                array(
                    'name' => 'notification_email',
                    'size' => '50',
                    'class' => "text field medium"
                ),
                'valid_email',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Address').' 1',
                'INPUT',
                array(
                    'name' => 'address_1',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Address').' 2',
                'INPUT',
                array(
                    'name' => 'address_2',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
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
                ''
            ),
            array(
                gettext('Province'),
                'INPUT',
                array(
                    'name' => 'province',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Zip Code'),
                'INPUT',
                array(
                    'name' => 'postal_code',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Country'),
                array(
                    'name' => 'country_id',
                    'class' => 'country_id',
                    "value" => $id == 0 ? $country_id : ''
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
                gettext('Timezone'),
                array(
                    'name' => 'timezone_id',
                    'class' => 'timezone_id',
                    "value" => $id == 0 ? $timezone_id : ''
                ),
                'SELECT',
                '',
                array(
                    "name" => "timezone_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'timezone_name,gmtoffset',
                'timezone',
                'build_concat_dropdown_timezone',
                '',
                ''
            ),
            array(
                gettext('Currency'),
                array(
                    'name' => 'currency_id',
                    'class' => 'currency_id',
                    "value" => $id == 0 ? $currency_id : ''
                ),
                'SELECT',
                '',
                array(
                    "name" => "currency_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'currencyname,currency',
                'currency',
                'build_concat_dropdown_currency',
                '',
                array()
            ),
            $invoice_config
        );
        $form[gettext('Billing Settings')] = array(
            array(
                gettext('Account Type'),
                array(
                    'name' => 'posttoexternal',
                    'disabled' => $readable,
                    'class' => 'posttoexternal',
                    'id' => 'posttoexternal'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_account_type'
            ),
            array(
                gettext('Credit Limit'),
                'INPUT',
                array(
                    'name' => 'credit_limit',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Rate Group'),
                array(
                    'name' => 'pricelist_id',
                    'id' => 'pricelist_id'
                ),
                'SELECT',
                '',
                array(
                    "name" => "pricelist_id",
                    "rules" => "dropdown"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'pricelists',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => $reseller_id
                )
            ),
           
            array(
                gettext('Billing Schedule'),
                array(
                    'name' => 'sweep_id',
                    'class' => 'sweep_id'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                'id',
                'sweep',
                'sweeplist',
                'build_dropdown',
                '',
                ''
            ),
            array(
                gettext('Billing Day'),
                array(
                    "name" => 'invoice_day',
                    "class" => "invoice_day",
                    'id' => "invoice_day"
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_invoice_option'
            ),
            $taxes_arr,
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
            ),
            array(
                gettext('Generate Invoice'),
                array(
                    "name" => "generate_invoice",
                    "class" => "generate_invoice"
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'paypal_status'
            ),
            array(
                gettext('Invoice Date Interval'),
                'INPUT',
                array(
                    'name' => 'invoice_interval',
                    'size' => '100',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Invoice Note'),
                'TEXTAREA',
                array(
                    'name' => 'invoice_note',
                    'size' => '20',
                    'cols' => '50',
                    'rows' => '3',
                    'class' => "form-control form-control-lg mit-20 text col-md-12 field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Reference'),
                'INPUT',
                array(
                    'name' => 'reference',
                    'id' => 'reference',
                    'size' => '15',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            )
        );

        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Cancel'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'/accounts/reseller_list/\')'
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'type' => 'submit',
            'class' => 'btn btn-success'
        );
        return $form;
    }

    function get_form_admin_fields($entity_type = '', $id = false)
    {
        $uname = $this->CI->common->find_uniq_rendno(common_model::$global_config['system_config']['cardlength'], 'number', 'accounts');
        if ($id > 0) {
            $params = array(
                'name' => 'number',
                'value' => $uname,
                'size' => '20',
                'class' => "text field medium",
                'id' => 'number',
                'readonly' => true
            );
        } else {
            $account_number_editable = Common_model::$global_config['system_config']['account_number_editable'];
            if ($account_number_editable == 0) {
                $params = array(
                    'name' => 'number',
                    'value' => $uname,
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'number'
                );
            } else {
                $params = array(
                    'name' => 'number',
                    'value' => $uname,
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'number',
                    'readonly' => true
                );
            }
        }
        if ($id > 0) {
            $val = 'accounts.email.' . $id;
            $account_val = 'accounts.number.' . $id;
            $password = array(
                gettext('Password'),
                'PASSWORD',
                array(
                    'name' => 'password',
                    'id' => 'password_show',
                    'onmouseover' => 'seetext(password_show)',
                    'onmouseout' => 'hidepassword(password_show)',
                    'placeholder' => gettext('unchanged'),
                    'autocomplete' => 'new-password',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'portal_password', // empty keeps the stored password; rule in libraries/astpp/password_policy.php
                'tOOL TIP',
                ''
            );
            $account = array(
                gettext('Account'),
                'INPUT',
                $params,
                'required|is_unique[' . $account_val . ']',
                'tOOL TIP',
                ''
            );
            if ($entity_type == 'subadmin') {
                $account_status = array(
                    gettext('Account Status'),
                    'status',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_status'
                );
            } else {
                $account_status = null;
                $company = array(
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
                );

                $Address_1 = array(
                    gettext('Address').' 1',
                    'INPUT',
                    array(
                        'name' => 'address_1',
                        'size' => '15',
                        'class' => "text field medium"
                    ),
                    '',
                    'tOOL TIP',
                    'Please Enter Password'
                );

                $Address_2 = array(
                    gettext('Address').' 2',
                    'INPUT',
                    array(
                        'name' => 'address_2',
                        'size' => '15',
                        'class' => "text field medium"
                    ),
                    '',
                    'tOOL TIP',
                    'Please Enter Password'
                );

                $City = array(
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
                );

                $Province = array(
                    gettext('Province'),
                    'INPUT',
                    array(
                        'name' => 'province',
                        'size' => '15',
                        'class' => "text field medium"
                    ),
                    '',
                    'tOOL TIP',
                    'Please Enter Password'
                );

                $Zip_Code = array(
                    gettext('Zip Code'),
                    'INPUT',
                    array(
                        'name' => 'postal_code',
                        'size' => '15',
                        'class' => "text field medium"
                    ),
                    'trim|xss_clean',
                    'tOOL TIP',
                    'Please Enter Password'
                );

                $Country = array(
                    gettext('Country'),
                    array(
                        'name' => 'country_id',
                        'class' => 'country_id'
                    ),
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
                );
                 $Timezone = array(
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
                    'timezone_name,gmtoffset',
                    'timezone',
                    'build_concat_dropdown_timezone',
                    '',
                    ''
                );
                $Currency = array(
                    gettext('Currency'),
                    array(
                        'name' => 'currency_id',
                        'class' => 'currency_id'
                    ),
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'currencyname,currency',
                    'currency',
                    'build_concat_dropdown_currency',
                    '',
                    array()
                );
                $account_status = array(
                    gettext('Account Status'),
                    'status',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_status'
                );
            }
        } else {
            $val = 'accounts.email';
            $account_val = 'accounts.number';
            $password = $this->CI->common->generate_password();
            $password = array(
                gettext('Password'),
                'INPUT',
                array(
                    'name' => 'password',
                    'value' => $password,
                    'size' => '20',
                    'class' => "text field medium",
                    'id' => 'password',
                    'onmouseover' => 'seetext(password)',
                    'onmouseout' => 'hidepassword(password)'
                ),
                'required|portal_password',
                'tOOL TIP',
                '',
                '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Reset Password" class="change_pass align-self-end text-success fa fa-refresh" ></i>'
            );
            if (isset(common_model::$global_config['system_config']['minimum_accountlength'])) {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    $params,
                    'required|is_unique[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            } else {
                $account = array(
                    gettext('Account'),
                    'INPUT',
                    $params,
                    'required|is_unique[' . $account_val . ']|is_match_number[' . $account_val . ']',
                    'tOOL TIP',
                    '',
                    '<i style="cursor:pointer; font-size: 17px; position:absolute; right:20px; bottom: 7px;" title="Generate Account" class="change_number align-self-end text-success fa fa-refresh" ></i>'
                );
            }

            $company = null;
            $Address_1 = null;
            $Address_2 = null;
            $City = null;
            $Province = null;
            $Zip_Code = null;
            $Country = null;
            $Timezone = null;
            $account_status = null;
            $Currency = null;
        }
        $permission = array();
        if ($this->CI->session->userdata('userlevel_logintype') == - 1) {
            $permission = array(
                gettext('Role'),
                'permission_id',
                'SELECT',
                '',
                array(
                    "name" => "permission_id",
                    "class" => "permission_id",
                    "id" => "permission_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'permissions',
                'build_dropdown',
                'where_arr',
                array(
                    "login_type" => "0"
                )
            );
        } else {
            $accountinfo = $this->CI->session->userdata('accountinfo');

            $permission = array(
                '',
                'HIDDEN',
                array(
                    'name' => 'permission_id',
                    'value' => $accountinfo['permission_id']
                ),
                '',
                '',
                '',
                ''
            );
        }
        $type = $entity_type == 'admin' ? 2 : 4;
        $form['forms'] = array(
            base_url() . 'accounts/' . $entity_type . '_save/',
            array(
                "id" => "admin_form",
                "name" => "admin_form"
            )
        );
        $form[gettext('Panel Access')] = array(
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
                    'value' => $type
                ),
                '',
                '',
                ''
            ),
            $account,
            $password,
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
                ''
            ),
            $permission,
            $account_status
        );
        if ($id > 0) {
            $account_status = null;
        }
        $form[gettext('Profile')] = array(
            array(
                gettext('First Name'),
                'INPUT',
                array(
                    'name' => 'first_name',
                    'id' => 'first_name',
                    'size' => '15',
                    'maxlength' => '40',
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
                'trim|xss_clean',
                'tOOL TIP',
                'Please Enter Password'
            ),
            $company,
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
                gettext('Notification Email'),
                'INPUT',
                array(
                    'name' => 'notification_email',
                    'size' => '50',
                    'class' => "text field medium"
                ),
                'valid_email',
                'tOOL TIP',
                ''
            ),
            $Address_1,
            $Address_2,
            $City,
            $Province,
            $Zip_Code,
            $Country,
            $Timezone,
            $Currency,
            $account_status
        );

        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Cancel'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'/accounts/admin_list/\')'
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'type' => 'submit',
            'class' => 'btn btn-success'
        );
        return $form;
    }

    function reseller_batch_update_form()
    {
        $status = array(
            gettext('Status'),
            array(
                'name' => 'status[status]',
                'id' => 'status',
                'class' => 'status'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            'id',
            'name',
            '',
            'set_status',
            '',
            '',
            array(
                'name' => 'status[operator]',
                'class' => 'update_drp'
            ),
            'update_drp_type'
        );
        $notify_flag = array(
            gettext('Low balance Alert?'),
            array(
                'name' => 'notify_flag[notify_flag]',
                'id' => 'notify_flag',
                'class' => 'notify_flag'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            'id',
            'name',
            '',
            'set_status',
            '',
            '',
            array(
                'name' => 'notify_flag[operator]',
                'class' => 'update_drp'
            ),
            'update_drp_type'
        );
        
        $form['forms'] = array(
            "accounts/reseller_batch_update/",
            array(
                'id' => "reseller_batch_update"
            )
        );
        $form[gettext('Batch Update')] = array(
            array(
                gettext('Rate Group'),
                array(
                    'name' => 'pricelist_id[pricelist_id]',
                    'id' => 'pricelist_id',
                    'class' => 'pricelist_id'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'pricelists',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => "0"
                ),
                array(
                    'name' => 'pricelist_id[operator]',
                    'class' => 'update_drp'
                ),
                'update_drp_type'
            ),
            array(
                gettext('Balance Below Notification'),
                'INPUT',
                array(
                    'name' => 'notify_credit_limit[notify_credit_limit]',
                    'id' => 'notify_credit_limit',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'notify_credit_limit[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('Balance'),
                'INPUT',
                array(
                    'name' => 'balance[balance]',
                    'id' => 'balance',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'balance[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('Credit Limit'),
                'INPUT',
                array(
                    'name' => 'credit_limit[credit_limit]',
                    'id' => 'credit_limit',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'credit_limit[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('CC'),
                'INPUT',
                array(
                    'name' => 'maxchannels[maxchannels]',
                    'id' => 'maxchannels',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'maxchannels[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            $status,
            $notify_flag
        );
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "batch_update_btn",
            'content' => gettext('Update'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_batch_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }

    function customer_batch_update_form()
    {
        $status = array(
            gettext('Status'),
            array(
                'name' => 'status[status]',
                'id' => 'status',
                'class' => 'status'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            'id',
            'name',
            '',
            'set_status',
            '',
            '',
            array(
                'name' => 'status[operator]',
                'class' => 'update_drp'
            ),
            'update_drp_type'
        );
        $local_call = array(
            gettext('Allow Local Call'),
            array(
                'name' => 'local_call[local_call]',
                'id' => 'local_call',
                'class' => 'local_call'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            'id',
            'name',
            '',
            'set_status',
            '',
            '',
            array(
                'name' => 'local_call[operator]',
                'class' => 'update_drp'
            ),
            'update_drp_type'
        );
        $allow_recording = array(
            gettext('Allow Recording'),
            array(
                'name' => 'is_recording[is_recording]',
                'id' => 'is_recording',
                'class' => 'is_recording'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            'id',
            'name',
            '',
            'set_status',
            '',
            '',
            array(
                'name' => 'is_recording[operator]',
                'class' => 'update_drp'
            ),
            'update_drp_type'
        );
        $notify_flag = array(
            gettext('Low balance Alert?'),
            array(
                'name' => 'notify_flag[notify_flag]',
                'id' => 'notify_flag',
                'class' => 'notify_flag'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            'id',
            'name',
            '',
            'set_status',
            '',
            '',
            array(
                'name' => 'notify_flag[operator]',
                'class' => 'update_drp'
            ),
            'update_drp_type'
        );
        $allow_ip_management = array(
            gettext('Allow IP Management'),
            array(
                'name' => 'allow_ip_management[allow_ip_management]',
                'id' => 'allow_ip_management',
                'class' => 'allow_ip_management'
            ),
            'SELECT',
            '',
            '',
            'tOOL TIP',
            'Please Enter account number',
            'id',
            'name',
            '',
            'set_status',
            '',
            '',
            array(
                'name' => 'allow_ip_management[operator]',
                'class' => 'update_drp'
            ),
            'update_drp_type'
        );
        $form['forms'] = array(
            "accounts/customer_batch_update/",
            array(
                'id' => "customer_batch_update"
            )
        );
        $form[gettext('Batch Update')] = array(
            array(
                gettext('Rate Group'),
                array(
                    'name' => 'pricelist_id[pricelist_id]',
                    'id' => 'pricelist_id',
                    'class' => 'pricelist_id'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'pricelists',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => "0"
                ),
                array(
                    'name' => 'pricelist_id[operator]',
                    'class' => 'update_drp'
                ),
                'update_drp_type'
            ),
            array(
                gettext('Balance'),
                'INPUT',
                array(
                    'name' => 'balance[balance]',
                    'id' => 'balance',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'balance[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('Balance Below Notification'),
                'INPUT',
                array(
                    'name' => 'notify_credit_limit[notify_credit_limit]',
                    'id' => 'notify_credit_limit',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'notify_credit_limit[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('Expiry Date'),
                'INPUT',
                array(
                    'name' => 'expiry[expiry]',
                    'id' => 'expiry1',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                array(
                    'name' => 'expiry[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('Credit Limit'),
                'INPUT',
                array(
                    'name' => 'credit_limit[credit_limit]',
                    'id' => 'credit_limit',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'credit_limit[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('LC Charge/Min'),
                'INPUT',
                array(
                    'name' => 'charge_per_min[charge_per_min]',
                    'id' => 'charge_per_min',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'charge_per_min[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('CC'),
                'INPUT',
                array(
                    'name' => 'maxchannels[maxchannels]',
                    'id' => 'maxchannels',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'maxchannels[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            array(
                gettext('CPS'),
                'INPUT',
                array(
                    'name' => 'cps[cps]',
                    'id' => 'cps',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                array(
                    'name' => 'cps[operator]',
                    'class' => 'update_drp'
                ),
                '',
                '',
                '',
                'update_int_type',
                ''
            ),
            $status,
            $allow_recording,
            $allow_ip_management,
            $local_call,
            $notify_flag
        );
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "customer_batch_update_form",
            'content' => gettext('Update'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_batch_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }

    function get_search_customer_form()
    {
        $logintype = $this->CI->session->userdata('userlevel_logintype');
        if ($logintype == - 1) {
            $reseller_drp = array(
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
                array(
                    "deleted" => 0
                )
            );
        } else {
            $reseller_drp = null;
        }

        if ($logintype != 1) {
            $form['forms'] = array(
                "",
                array(
                    'id' => "account_search"
                )
            );
            $form[gettext('Search')] = array(
                array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number[number]',
                        '',
                        'size' => '20',
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
                    gettext('First Name'),
                    'INPUT',
                    array(
                        'name' => 'first_name[first_name]',
                        '',
                        'id' => 'first_name',
                        'size' => '15',
                        'class' => "text field "
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'first_name[first_name-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Last Name'),
                    'INPUT',
                    array(
                        'name' => 'last_name[last_name]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'last_name[last_name-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Company'),
                    'INPUT',
                    array(
                        'name' => 'company_name[company_name]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'company_name[company_name-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    'CC',
                    'INPUT',
                    array(
                        'name' => 'maxchannels[maxchannels]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'maxchannels[maxchannels-integer]',
                    '',
                    '',
                    '',
                    'search_int_type'
                ),

                array(
                    gettext('Balance'),
                    'INPUT',
                    array(
                        'name' => 'balance[balance]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'balance[balance-integer]',
                    '',
                    '',
                    '',
                    'search_int_type',
                    ''
                ),
                array(
                    gettext('Credit Limit'),
                    'INPUT',
                    array(
                        'name' => 'credit_limit[credit_limit]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'credit_limit[credit_limit-integer]',
                    '',
                    '',
                    '',
                    'search_int_type',
                    ''
                ),
                array(
                    gettext('Email'),
                    'INPUT',
                    array(
                        'name' => 'email[email]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'email[email-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('First Used'),
                    'INPUT',
                    array(
                        'name' => 'first_used[0]',
                        '',
                        'size' => '20',
                        'class' => "text field",
                        'id' => 'first_used'
                    ),
                    '',
                    'tOOL TIP',
                    '',
                    'first_used[first_used-date]'
                ),
                array(
                    gettext('Expiry Date'),
                    'INPUT',
                    array(
                        'name' => 'expiry[0]',
                        'size' => '20',
                        'class' => "text field",
                        'id' => 'expiry'
                    ),
                    '',
                    'tOOL TIP',
                    '',
                    'expiry[expiry-date]'
                ),
                array(
                    gettext('Rate Group'),
                    'pricelist_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'pricelists',
                    'build_dropdown',
                    'where_arr',
                    array(
                        "status" => "0",
                        "reseller_id" => "0"
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
                    gettext('Created Date'),
                    'INPUT',
                    array(
                        'name' => 'creation[0]',
                        '',
                        'size' => '20',
                        'class' => "text field",
                        'id' => 'creation'
                    ),
                    '',
                    'tOOL TIP',
                    '',
                    'creation[creation-date]'
                ),
                array(
                    gettext('Entity Type'),
                    'type',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_entity_type_customer'
                ),
                array(
                    gettext('Account Type'),
                    'posttoexternal',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_account_type_search'
                ),
                array(
                    gettext('Billing Cycle'),
                    'sweep_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_Billing_Schedule_status'
                ),
                $reseller_drp,
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

            $form['forms'] = array(
                "",
                array(
                    'id' => "account_search"
                )
            );
            $form[gettext('Search')] = array(
                array(
                    gettext('Account'),
                    'INPUT',
                    array(
                        'name' => 'number[number]',
                        '',
                        'size' => '20',
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
                    gettext('First Name'),
                    'INPUT',
                    array(
                        'name' => 'first_name[first_name]',
                        '',
                        'id' => 'first_name',
                        'size' => '15',
                        'class' => "text field "
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'first_name[first_name-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Last Name'),
                    'INPUT',
                    array(
                        'name' => 'last_name[last_name]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'last_name[last_name-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Company'),
                    'INPUT',
                    array(
                        'name' => 'company_name[company_name]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'company_name[company_name-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Rate Group'),
                    'pricelist_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'pricelists',
                    'build_dropdown',
                    'where_arr',
                    array(
                        "status" => "0",
                        "reseller_id" => "0"
                    )
                ),
                array(
                    gettext('Balance'),
                    'INPUT',
                    array(
                        'name' => 'balance[balance]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'balance[balance-integer]',
                    '',
                    '',
                    '',
                    'search_int_type',
                    ''
                ),
                array(
                    gettext('Credit Limit'),
                    'INPUT',
                    array(
                        'name' => 'credit_limit[credit_limit]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'credit_limit[credit_limit-integer]',
                    '',
                    '',
                    '',
                    'search_int_type',
                    ''
                ),
                array(
                    gettext('Email'),
                    'INPUT',
                    array(
                        'name' => 'email[email]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'email[email-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('First Used'),
                    'INPUT',
                    array(
                        'name' => 'first_used[0]',
                        '',
                        'size' => '20',
                        'class' => "text field",
                        'id' => 'first_used'
                    ),
                    '',
                    'tOOL TIP',
                    '',
                    'first_used[first_used-date]'
                ),
                array(
                    gettext('Expiry Date'),
                    'INPUT',
                    array(
                        'name' => 'expiry[0]',
                        'id' => 'expiry',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'tOOL TIP',
                    '',
                    'expiry[expiry-date]'
                ),
                array(
                    'CC',
                    'INPUT',
                    array(
                        'name' => 'maxchannels[maxchannels]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'maxchannels[maxchannels-integer]',
                    '',
                    '',
                    '',
                    'search_int_type'
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
                    gettext('Created Date'),
                    'INPUT',
                    array(
                        'name' => 'creation[0]',
                        '',
                        'size' => '20',
                        'class' => "text field",
                        'id' => 'creation'
                    ),
                    '',
                    'tOOL TIP',
                    '',
                    'creation[creation-date]'
                ),
                array(
                    gettext('Account Type'),
                    'posttoexternal',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_account_type_search'
                ),
                array(
                    gettext('Billing Cycle'),
                    'sweep_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    '',
                    '',
                    '',
                    'set_Billing_Schedule_status'
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
            'id' => "account_search_btn",
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

    function get_reseller_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "account_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Account'),
                'INPUT',
                array(
                    'name' => 'number[number]',
                    '',
                    'size' => '20',
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
                gettext('First Name'),
                'INPUT',
                array(
                    'name' => 'first_name[first_name]',
                    '',
                    'id' => 'first_name',
                    'size' => '15',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'first_name[first_name-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Last Name'),
                'INPUT',
                array(
                    'name' => 'last_name[last_name]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'last_name[last_name-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Email'),
                'INPUT',
                array(
                    'name' => 'email[email]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'email[email-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Company'),
                'INPUT',
                array(
                    'name' => 'company_name[company_name]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'company_name[company_name-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Rate Group'),
                'pricelist_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'pricelists',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0",
                    "reseller_id" => "0"
                )
            ),
            array(
                gettext('Account Type'),
                'posttoexternal',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_account_type_search'
            ),
            array(
                gettext('Balance'),
                'INPUT',
                array(
                    'name' => 'balance[balance]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'balance[balance-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),

            array(
                gettext('Credit Limit'),
                'INPUT',
                array(
                    'name' => 'credit_limit[credit_limit]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'credit_limit[credit_limit-integer]',
                '',
                '',
                '',
                'search_int_type',
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
                gettext('Created Date'),
                'INPUT',
                array(
                    'name' => 'creation[0]',
                    '',
                    'size' => '20',
                    'class' => "text field",
                    'id' => 'creation'
                ),
                '',
                'tOOL TIP',
                '',
                'creation[creation-date]'
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
            'id' => "account_search_btn",
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

    function get_admin_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "account_search"
            )
        );
        $form['Search'] = array(
            array(
                gettext('Account'),
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
                gettext('First Name'),
                'INPUT',
                array(
                    'name' => 'first_name[first_name]',
                    '',
                    'id' => 'first_name',
                    'size' => '15',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'first_name[first_name-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Last Name'),
                'INPUT',
                array(
                    'name' => 'last_name[last_name]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'last_name[last_name-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Company'),
                'INPUT',
                array(
                    'name' => 'company_name[company_name]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'company_name[company_name-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Email'),
                'INPUT',
                array(
                    'name' => 'email[email]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'email[email-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Entity Type'),
                'type',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_entity_type_admin'
            ),
            array(
                gettext('Phone'),
                'INPUT',
                array(
                    'name' => 'telephone_1[telephone_1]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'telephone_1[telephone_1-integer]',
                '',
                '',
                '',
                'search_int_type',
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
                'Status',
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
            'id' => "account_search_btn",
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

    function build_account_list_for_admin()
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
                gettext("Account"),
                "135",
                "number",
                "number",
                "accounts",
                "account_number_icon",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("First Name"),
                "130",
                "first_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Last Name"),
                "130",
                "last_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Company"),
                "150",
                "company_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Email"),
                "170",
                "email",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Phone"),
                "150",
                "telephone_1",
                "",
                "",
                "",
                "",
                "true",
                "center"
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
                gettext("Role"),
                "80",
                "permission_id",
                "name",
                "permissions",
                "get_field_name",
                "",
                "true",
                "center"
            ),

            array(
                gettext("Status"),
                "110",
                "status",
                "status",
                "accounts",
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
                        "url" => "accounts/admin_edit/",
                        "mode" => "single"
                    ),
                    "DELETE" => array(
                        "url" => "accounts/admin_delete/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_account_list_for_customer()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        if ($account_info['type'] == - 1) {

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
                    gettext("Account"),
                    "110",
                    "number",
                    "number",
                    "accounts",
                    "account_number_icon",
                    "EDITABLE",
                    "true",
                    "left"
                ),
                array(
                    gettext("First Name"),
                    "80",
                    "first_name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Last Name"),
                    "80",
                    "last_name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Company"),
                    "90",
                    "company_name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Rate Group"),
                    "80",
                    "pricelist_id",
                    "name",
                    "pricelists",
                    "get_field_name",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Balance") . " <br/>($currency)",
                    //Gautam ASTPPCOM-1085 start
                    "150",
                    //Gautam ASTPPCOM-1085  end
                    "balance",
                    "balance",
                    "balance",
                    "convert_to_currency_account",
                    array(
                        "EDITABLE",
                        "PAYMENT",
                        "accounts/customer_payment_process_add/"
                    ),
                    "true",
                    "right"
                ),
                array(
                    gettext("Credit Limit") . " <br/>($currency)",
                    "90",
                    "credit_limit",
                    "credit_limit",
                    "credit_limit",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("First Used"),
                    "80",
                    "first_used",
                    "first_used",
                    "first_used",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Expiry Date"),
                    "80",
                    "expiry",
                    "expiry",
                    "expiry",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    "CC",
                    "30",
                    "maxchannels",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Localization"),
                    "75",
                    "localization_id",
                    "name",
                    "localization",
                    "get_field_name",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Reseller"),
                    "85",
                    "reseller_id",
                    "first_name,last_name,number",
                    "accounts",
                    "reseller_select_value",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Created Date"),
                    "90",
                    "creation",
                    "creation",
                    "creation",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Status"),
                    "60",
                    "status",
                    "status",
                    "accounts",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "125",
                    "",
                    "",
                    "",
                    array(
                        "PAYMENT" => array(
                            "url" => "accounts/customer_payment_process_add/",
                            "mode" => "single"
                        ),
                        // "CALLERID" => array(
                        //     "url" => "accounts/customer_add_callerid/",
                        //     "mode" => "popup"
                        // ),
                        "EDIT" => array(
                            "url" => "accounts/customer_edit/",
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
                    gettext("Account"),
                    "110",
                    "number",
                    "number",
                    "accounts",
                    "account_number_icon",
                    "EDITABLE",
                    "true",
                    "left"
                ),
                array(
                    gettext("First Name"),
                    "80",
                    "first_name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Last Name"),
                    "80",
                    "last_name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Company"),
                    "90",
                    "company_name",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Rate Group"),
                    "80",
                    "pricelist_id",
                    "name",
                    "pricelists",
                    "get_field_name",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Balance") . " <br/>($currency)",
                    //Gautam ASTPPCOM-1085 start
                    "150",
                    //Gautam ASTPPCOM-1085 end
                    "balance",
                    "balance",
                    "balance",
                    "convert_to_currency_account",
                    array(
                        "EDITABLE",
                        "payment",
                        "accounts/customer_payment_process_add/"
                    ),
                    "true",
                    "right"
                ),
                array(
                    gettext("Credit Limit") . " <br/>($currency)",
                    "90",
                    "credit_limit",
                    "credit_limit",
                    "credit_limit",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("First Used"),
                    "80",
                    "first_used",
                    "first_used",
                    "first_used",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Expiry Date"),
                    "80",
                    "expiry",
                    "expiry",
                    "expiry",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    "CC",
                    "30",
                    "maxchannels",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Reseller"),
                    "85",
                    "reseller_id",
                    "first_name,last_name,number",
                    "accounts",
                    "reseller_select_value",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Created Date"),
                    "90",
                    "creation",
                    "creation",
                    "creation",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Status"),
                    "60",
                    "status",
                    "status",
                    "accounts",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "125",
                    "",
                    "",
                    "",
                    array(
                        "PAYMENT" => array(
                            "url" => "accounts/customer_payment_process_add/",
                            "mode" => "single"
                        ),
                        // "CALLERID" => array(
                        //     "url" => "accounts/customer_add_callerid/",
                        //     "mode" => "popup"
                        // ),
                        "EDIT" => array(
                            "url" => "accounts/customer_edit/",
                            "mode" => "single"
                        )
                    ),
                    "false"
                )
            ));
        }
        return $grid_field_arr;
    }

    function build_account_list_for_reseller()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
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
                gettext("Account"),
                "100",
                "number",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("First Name"),
                "90",
                "first_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Last Name"),
                "90",
                "last_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Company"),
                "200",
                "company_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Rate Group"),
                "90",
                "pricelist_id",
                "name",
                "pricelists",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Account Type"),
                "90",
                "posttoexternal",
                "posttoexternal",
                "posttoexternal",
                "get_account_type",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Balance") . " <br/> ($currency)",
                //Gautam ASTPPCOM-1085 start
                "150",
                //Gautam ASTPPCOM-1085 end
                "balance",
                "balance",
                "balance",
                "convert_to_currency_account",
                array(
                    "EDITABLE",
                    "payment",
                    "accounts/customer_payment_process_add/"
                ),
                "true",
                "right"
            ),
            array(
                gettext("Credit Limit") . " <br/> ($currency)",
                "90",
                "credit_limit",
                "credit_limit",
                "credit_limit",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("CC"),
                "45",
                "maxchannels",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Reseller"),
                "85",
                "reseller_id",
                "first_name,last_name,number,company_name",
                "accounts",
                "reseller_select_value",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Role"),
                "80",
                "permission_id",
                "name",
                "permissions",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Created Date"),
                "90",
                "creation",
                "creation",
                "creation",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Status"),
                "60",
                "status",
                "status",
                "accounts",
                "get_status",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Action"),
                "139",
                "",
                "",
                "",
                array(
                    "PAYMENT" => array(
                        "url" => "accounts/customer_payment_process_add/",
                        "mode" => "single"
                    ),
                    // "CALLERID" => array(
                    //     "url" => "accounts/customer_add_callerid/",
                    //     "mode" => 'popup'
                    // ),
                    "EDIT" => array(
                        "url" => "accounts/reseller_edit/",
                        "mode" => "single"
                    ),
                    "DELETE" => array(
                        "url" => "accounts/reseller_delete/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_grid_buttons_customer()
    {
        $logintype = $this->CI->session->userdata('userlevel_logintype');
        $provider = null;
        $account_import = array();
        if ($logintype != 1) {
            $account_import = array(
                gettext("Import Customers"),
                "btn btn-line-warning",
                "fa fa-download fa-lg",
                "button_action",
                "/account_import/customer_import_mapper/",
                'single',
                "small",
                "import"
            );
            $provider = array(
                gettext("Create Provider"),
                "btn btn-line-blue btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/accounts/provider_add/",
                "single",
                "medium",
                "create_provider"
            );
        }
        $buttons_json = json_encode(array(
            array(
                gettext("Create Customer"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/accounts/customer_add/",
                "single",
                "medium",
                "create"
            ),
            array(
                gettext("Mass Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/accounts/customer_bulk_creation/",
                "popup",
                "medium",
                "mass_create"
            ),
            $account_import,
            $provider,
            array(
                gettext("Export"),
                "btn btn-xing",
                " fa fa-upload fa-lg",
                "button_action",
                "/accounts/customer_export_cdr_xls/",
                'single',
                "",
                "export"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/accounts/customer_selected_delete/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function build_grid_buttons_admin()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/accounts/admin_add/",
                "single",
                "medium",
                "create_admin"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/accounts/admin_selected_delete/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function build_grid_buttons_reseller()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/accounts/reseller_add/",
                "single",
                "medium",
                "create"
            ),
            array(
                gettext("Export"),
                "btn btn-xing",
                " fa fa-upload fa-lg",
                "button_action",
                "/accounts/reseller_export_cdr_xls",
                'single',
                "",
                "export"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/accounts/reseller_selected_delete/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function build_animap_list_for_customer($accountid, $accounttype)
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
                "200",
                "number",
                "",
                "",
                "",
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
            )
        ));
        return $grid_field_arr;
    }

    function build_sipiax_list_for_customer()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Tech"),
                "150",
                "tech",
                "",
                "",
                ""
            ),
            array(
                gettext("Type"),
                "150",
                "type",
                "",
                "",
                ""
            ),
            array(
                gettext("User Name"),
                "150",
                "username",
                "sweep",
                "sweeplist",
                "get_field_name"
            ),
            array(
                gettext("Password"),
                "150",
                "secret",
                "",
                "",
                ""
            ),
            array(
                gettext("Context"),
                "150",
                "context",
                "",
                "",
                ""
            )
        ));
        return $grid_field_arr;
    }

    function set_block_pattern_action_buttons($id)
    {
        $ret_url = '';
        $ret_url .= '<a href="/did/delete/' . $id . '/" class="icon delete_image" title="Delete" onClick="return get_alert_msg();">&nbsp;</a>';
        return $ret_url;
    }

    function build_animap_list()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Caller ID"),
                "180",
                "number",
                "",
                "",
                ""
            ),
            array(
                gettext("status"),
                "180",
                "status",
                "status",
                "animap",
                "get_status"
            ),
            array(
                gettext("Action"),
                "130",
                "",
                "",
                "",
                array(
                    "EDIT_ANIMAP" => array(
                        "url" => "accounts/callingcards_animap_list_edit/",
                        "mode" => "single"
                    ),
                    "DELETE_ANIMAP" => array(
                        "url" => "accounts/callingcards_animap_list_remove/",
                        "mode" => "single"
                    )
                )
            )
        ));
        return $grid_field_arr;
    }

    function build_grid_buttons_destination()
    {
        $buttons_json = json_encode(array());
        return $buttons_json;
    }

    function custom_status_option($status)
    {
        $status_array = array(
            '1' => gettext('No'),
            '0' => gettext('Yes')
        );
        return $status_array;
    }
}

?>

