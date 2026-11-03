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
class Low_balance_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function build_lowbalance_list_for_admin($id = '')
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        if ($account_info['type'] == -1 ||$account_info['type'] == 2 ) {
            $grid_field_arr = json_encode(array(
                array(
                    gettext ( "Account" ),
                    "150",
                    "id",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "get_field_name_coma_new"
                ),
                array(
                    gettext("Reseller"),
                    "150",
                    "reseller_id",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "reseller_select_value"
                ),
                array(
                    gettext("Low Balance"),
                    "150",
                    "balance",
                    "balance",
                    "accounts",
                    "convert_to_currency"
                )
            ));
                return $grid_field_arr;
    }
}
function get_search_low_balance_form()
{
    $account_data = $this->CI->session->userdata ( "accountinfo" );
    $form['forms'] = array(
        "",
        array(
            'id' => "low_balance_search",
        )
    );
    if ($account_data['type'] == -1 ||$account_data['type'] == 2 ) {
    $form[gettext('Search')] = array(
       
        array(
            gettext('Reseller'),
            array(
                'name' => 'reseller_id',
                'class' => 'reseller',
                'onchange' => 'account_change_add(this.value)'
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
            'name' => 'id',
            'id' => 'accountcode'
        ),
        'SELECT',
        '',
        '',
        'tOOL TIP',
        'Please Enter account number',
        'id',                           
        'first_name,last_name,number,company_name',  
        'accounts',
        'build_concat_dropdown', 
        '',
        array(
                "reseller_id" => $account_data['type'],
                "deleted" => "0",
                "status"=>"0",
               
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
        'id' => "low_balance_search_btn",
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

}

?>
