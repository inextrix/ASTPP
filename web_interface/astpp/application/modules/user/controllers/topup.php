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
/*
 * PayPal top-up of the customer portal, loaded by user/user_payment and user/user_invoice_payment_pay.
 * This was the controller user/payment, class Payment: PHP class names are case-insensitive, and
 * the User controller has already loaded the library astpp/payment (class payment). MX then took
 * the library class for this controller, and $this->payment->index() ended with a fatal error.
 */
class Topup extends MX_Controller
{

    // __construct() instead of the PHP 4 style constructor Payment(), which is no constructor from
    // PHP 8 on and is also callable by URL; login check added (there was none in this controller)
    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->library('ASTPP_Sms');
        $this->load->helper('form');
        if ($this->session->userdata('user_login') == FALSE) {
            redirect(base_url() . 'login/login');
        }
    }

    /*
     * Top-up with PayPal. The page stored a placeholder payment_transaction on every view and let
     * the browser post the amount straight to PayPal; login/paypal_response then credited the
     * field "custom" without asking PayPal. Now the page posts the amount to checkout(), which
     * stores the expected payment (libraries/astpp/paypal.php); only the verified IPN books it.
     */
    function index()
    {
        $account_data = $this->session->userdata("accountinfo");
        $this->load->library('astpp/paypal');
        $account = (array) $this->db->get_where('accounts', array('id' => $account_data['id']))->row_array();
        $settings = $this->paypal->for_account($account);
        if ($settings === null || $account['posttoexternal'] == 1) {
            $this->session->set_flashdata('astpp_notification', gettext('PayPal is not available.'));
            redirect(base_url() . 'user/user/');
            exit();
        }
        $data["accountid"] = $account_data["id"];
        $data["page_title"] = gettext("Recharge");
        $data["paypal_tax"] = $settings['fee'];
        $data["from_currency"] = $this->common->get_field_name('currency', 'currency', $account_data["currency_id"]);
        $data["to_currency"] = Common_model::$global_config['system_config']['base_currency'];
        $this->load->view("user_payment", $data);
    }

    function checkout()
    {
        $account_data = $this->session->userdata("accountinfo");
        $this->load->library('astpp/paypal');
        $account = (array) $this->db->get_where('accounts', array('id' => $account_data['id']))->row_array();
        $settings = $this->paypal->for_account($account);
        // credit in base currency as converted by user/user_payment/GET_AMT (field custom)
        $credit = paypal::amount(str_replace(',', '', trim((string) $this->input->post('custom'))));
        $row = null;
        if ($settings !== null && $account['posttoexternal'] != 1 && $credit !== false && $credit > 0) {
            $row = $this->paypal->create($account, 'topup', 0, $credit + ($credit * $settings['fee'] / 100), $credit, $settings);
        }
        if ($row === null) {
            $this->session->set_flashdata('astpp_notification', gettext('PayPal is not available or the amount is invalid.'));
            redirect(base_url() . 'user/user_payment/');
            exit();
        }
        $this->load->view('paypal_redirect', $this->paypal->form_data($row, $settings, 'Recharge'));
    }

    function convert_amount($amount)
    {
        $amount = $this->common_model->add_calculate_currency($amount, "", "", true, false);
        echo number_format((float) $amount, 2);
    }
}
?> 
