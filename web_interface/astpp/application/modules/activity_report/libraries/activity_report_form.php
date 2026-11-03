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
class Activity_report_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function build_grid_activity_report_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-download fa-lg",
                "button_action",
                "/activity_report/activityReport_export",
               'single',
                "",
                "export"
            )
          
        ));
        return $buttons_json;
    }
    function build_customer_report_list_for_admin() {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $grid_field_arr = json_encode(array(
            
        // Kinjal issue no 2357 Account Name doesn't display as Company name in Account login/logout activity (80)
        array(gettext("Account"), "250", "accountid", "first_name,last_name,number,company_name", "accounts", "get_field_name_coma_new"),
        // END
        array(gettext("Reseller"), "250", "reseller_id", "first_name,last_name,number,company_name", "accounts", "reseller_select_value"),
		array(gettext("Last DID Call Date"), "180", "last_did_call_time","last_did_call_time", "last_did_call_time", "convert_GMT_to"),
		array(gettext("Last Outbound Call Date"), "180", "last_outbound_call_time","last_outbound_call_time", "last_outbound_call_time", "convert_GMT_to"),
        array(
            gettext("Balance") . "<br/> ($currency)",
            "130",
            "accountid_balance",
            "balance",
            "accounts",
            "get_field_name_balance"
            
        ),
        array(
            // Kinjal issue no 2358 Call activity report - "Available credit limit" title should be displayed.
            gettext("Available Credit limit") . "<br/> ($currency)",
            // END
            "130",
            "accountid_creditlimit",
            "credit_limit",
            "accounts",
            "get_field_name_balance"
        )
    ));
        return $grid_field_arr;
    }
    function get_customer_report_form() {
        $account_data = $this->CI->session->userdata("accountinfo");
           $reseller_id = $account_data['type'] == 1 ? $account_data['id'] : 0;
           $form['forms'] = array("",array('id' => "activity_search"));
           $form['Search'] = array(
            // Kinjal issue no 2490 Date-time manual enter random value is creating issue
               array(gettext('Last DID Call From Date'), 'INPUT', array('name' => 'last_did_call_time[]','readonly' => 'readonly',  'id' => 'last_did_from_date', 'size' => '20', 'class' => "text field "), '', 'tOOL TIP', '', 'start_date[start_date-date]'),
               array(gettext('Last DID Call To Date'), 'INPUT', array('name' => 'last_did_call_time[]','readonly' => 'readonly',  'id' => 'last_did_to_date', 'size' => '20', 'class' => "text field "), '', 'tOOL TIP', '', 'end_date[end_date-date]'),
           array(gettext('Last Outbound Call From Date'), 'INPUT', array('name' => 'last_outbound_call_time[]','readonly' => 'readonly',  'id' => 'last_outbound_from_date', 'size' => '20', 'class' => "text field "), '', 'tOOL TIP', '', 'start_date[start_date-date]'),
               array(gettext('Last Outbound Call To Date'), 'INPUT', array('name' => 'last_outbound_call_time[]','readonly' => 'readonly',  'id' => 'last_outbound_to_date', 'size' => '20', 'class' => "text field "), '', 'tOOL TIP', '', 'end_date[end_date-date]'),
            // END
       array(gettext('Reseller'),
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
                       'first_name,last_name,number,company_name',
                       'accounts',
                       'build_concat_dropdown_reseller',
                       '',
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
                       'number',
                       'accounts',
                       'build_dropdown',
                       'where_arr',
                       array(
                           "reseller_id" => $reseller_id,
                           "type" => "GLOBAL"
                       )
                   ),
   
       array('', 'HIDDEN', 'ajax_search', '1', '', '', ''),array('', 'HIDDEN', 'advance_search', '1', '', '', ''));
           $form['button_search'] = array('name' => 'action', 'id' => "activity_search_btn", 'content' => 'Search', 'value' => 'save', 'type' => 'button',  'class' => 'btn btn-success float-right');
           $form['button_reset'] = array('name' => 'action', 'id' => "id_reset", 'content' => 'Clear', 'value' => 'cancel', 'type' => 'reset',  'class' => 'btn btn-secondary float-right ml-2');
           return $form;
       }
}

?>
