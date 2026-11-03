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
class Orders_form
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function get_order_search_form()
    {
        $account_data = $this->CI->session->userdata("accountinfo");
        $reseller_id = $account_data['type'] == 1 ? $account_data['id'] : 0;

        if ($account_data['type'] == - 1) {
            $reseller_array = array(
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
                array(
                    "type" => "1"
                )
            );
        } else {
            $reseller_array = array(
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
                array(
                    "reseller_id" => $account_data['id'],
                    "type" => "1"
                )
            );
        }

        $form['forms'] = array(
            "",
            array(
                'id' => "orders_list_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'order_date[]',
                    'id' => 'billing_date_from_date',
                    'size' => '20',
                    // Ashish ASTPPCOM-821
                    'readonly' => 'readonly',
                    // Ashish End
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
                    // Ashish ASTPPCOM-821
                    'readonly' => 'readonly',
                    // Ashish End
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
            $reseller_array,
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
                    "reseller_id" => $reseller_id,
                    "type" => "GLOBAL",
                    "status" => 0
                )
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
            'id' => "order_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => 'id_reset',
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }

    function build_orders_list_for_admin()
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
                gettext("Date"),
                "150",
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
                "150",
                "id",
                "order_id",
                "orders",
                "get_order_id",
                "EDITABLE",
                "false",
                "right"
            ),
            array(
                gettext("Account"),
                "250",
                "accountid",
                "first_name,last_name,number,company_name",
                "accounts",
                "build_concat_string",
                "",
                "false",
                "center"
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
                "left"
            ),
            array(
                gettext("Setup Fee"),
                "100",
                "setup_fee",
                "setup_fee",
                "setup_fee",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Price"),
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
                gettext("Reseller"),
                "150",
                "reseller_id",
                "first_name,last_name,number,company_name",
                "accounts",
                "get_field_name_coma_new",
                "",
                "true",
                "center"
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
                "left"
            ),
            array(
                gettext("Action"),
                "120",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "orders/orders_complete/",
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
                gettext("New"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/orders/orders_add/",
                "",
                "",
                "new"
            )
        ));
        return $buttons_json;
    }

    function build_orders_list_for_reseller()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "150",
                "order_date",
                "order_date",
                "order_date",
                "convert_to_date",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Order"),
                "150",
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
                "left"
            ),
            array(
                gettext("Setup Fee"),
                "100",
                "setup_fee",
                "setup_fee",
                "setup_fee",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Price"),
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
                gettext("Status"),
                "100",
                "payment_status",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
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
            )
        ));
        return $grid_field_arr;
    }

    function get_reseller_order_search_form()
    {
        $account_data = $this->CI->session->userdata("accountinfo");
        $reseller_id = $account_data['type'] == 1 ? $account_data['id'] : 0;

        $form['forms'] = array(
            "",
            array(
                'id' => "reseller_orders_list_search"
            )
        );
        $form[gettext('Search')] = array(
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
            'id' => "order_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => 'id_reset',
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }
}
?>
