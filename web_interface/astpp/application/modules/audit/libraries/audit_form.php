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
class Audit_form
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function build_audit_list_for_admin()
    {
        $grid_field_arr = json_encode(array(

            array(
                gettext("Account"),
                "100",
                "user_identifier",
                "first_name,last_name,number,company_name",
                "accounts",
                "get_field_name_coma_new"
            ),
            array(
                gettext("Request URI"),
                "280",
                "request_uri",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Timestamp"),
                "130",
                "timestamp",
                "timestamp",
                "timestamp",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Client IP"),
                "120",
                "client_ip",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Client User Agent"),
                "295",
                "client_user_agent",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Referer Page"),
                "285",
                "referer_page",
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

    function build_grid_buttons_admin()
    {
        $buttons_json = json_encode(array());
        return $buttons_json;
    }

    function get_search_audit_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "audit_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Timestamp'),
                'INPUT',
                array(
                    'name' => 'timestamp[]',
                    'id' => 'customer_cdr_from_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'timestamp[timestamp-date]'
            ),
            array(
                gettext('To Timestamp'),
                'INPUT',
                array(
                    'name' => 'timestamp[]',
                    'id' => 'customer_cdr_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'timestamp[timestamp-date]'
            ),
            array(
                gettext('Request URI'),
                'INPUT',
                array(
                    'name' => 'request_uri[request_uri]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'request_uri[request_uri-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),

            array(
                gettext('Client IP'),
                'INPUT',
                array(
                    'name' => 'client_ip[client_ip]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'client_ip[client_ip-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Client User Agent'),
                'INPUT',
                array(
                    'name' => 'client_user_agent[client_user_agent]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'client_user_agent[client_user_agent-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Referer Page'),
                'INPUT',
                array(
                    'name' => 'referer_page[referer_page]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'referer_page[referer_page-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Account'),
                'user_identifier',
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
            'id' => "audit_search_btn",
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
}

?>
