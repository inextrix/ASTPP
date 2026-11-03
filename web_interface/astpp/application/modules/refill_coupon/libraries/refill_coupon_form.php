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
class Refill_coupon_form extends common
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function get_refill_coupon_form_fields()
    {
        $start_prefix_max_length = Common_model::$global_config['system_config']['refill_coupon_length'];
        $form['forms'] = array(
            base_url() . 'refill_coupon/refill_coupon_save/',
            array(
                "id" => "refill_coupon_form",
                "name" => "refill_coupon_form"
            )
        );
        $form[gettext('Coupon Information')] = array(
            array(
                gettext('Description'),
                'INPUT',
                array(
                    'name' => 'description',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Start prefix'),
                'INPUT',
                array(
                    'name' => 'prefix',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|numeric|xss_clean|greater_than[0]|max_length[' . $start_prefix_max_length . ']',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Quantity'),
                'INPUT',
                array(
                    'name' => 'count',
                    'size' => '20',
                    'maxlength' => '5',
                    'class' => "text field medium"
                ),
                'trim|required|is_numeric|greater_than[0]|xss_clean',
                'tOOL TIP',
                'Please Enter account number'
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'amount',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|is_numeric|greater_than[0]|xss_clean',
                'tOOL TIP',
                'Please Enter account number'
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

    function build_grid_buttons_refill_coupon()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/refill_coupon/refill_coupon_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/refill_coupon/refill_coupon_list_delete/",
                "",
                "",
                "delete"
            ),
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/refill_coupon/refill_coupon_export/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function build_user_grid_buttons_refill_coupon()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Refresh"),
                "reload",
                "/refill_coupon/refill_coupon_clearsearchfilter/"
            )
        ));
        return $buttons_json;
    }

    function get_refill_coupon_search_form()
    {
        $accountinfo = $this->CI->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $form['forms'] = array(
            "",
            array(
                'id' => "refill_coupon_list_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Coupon Number'),
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
                gettext('Description'),
                'INPUT',
                array(
                    'name' => 'description[description]',
                    '',
                    'id' => 'description',
                    'size' => '15',
                    'class' => "text field "
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
                gettext('Account'),
                'account_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( company_name, " ", "(", number, ")^" )),IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ),concat( company_name, " ", "(", number, ")" ))) as number',
                'accounts',
                'build_dropdown_deleted',
                'where_arr',
                array(
                    "reseller_id" => $reseller_id,
                    "type" => "GLOBAL"
                )
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'amount[amount]',
                    '',
                    'id' => 'amount',
                    'size' => '15',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'amount[amount-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Used?'),
                'status',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'set_refill_coupon_status',
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
            'id' => "refill_coupon_search_btn",
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

    function get_user_refill_coupon_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "user_refill_coupon_list_search"
            )
        );
        $form[gettext('Search Refill Coupon')] = array(
            array(
                gettext('Coupon Number'),
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
                gettext('Description'),
                'INPUT',
                array(
                    'name' => 'description[description]',
                    '',
                    'id' => 'description',
                    'size' => '15',
                    'class' => "text field "
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
                    'name' => 'amount[amount]',
                    '',
                    'id' => 'amount',
                    'size' => '15',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'amount[amount-integer]',
                '',
                '',
                '',
                'search_int_type',
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
                'set_refill_coupon_status',
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
            'id' => "user_refill_coupon_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'ui-state-default float-right ui-corner-all ui-button'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear Search Filter'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'ui-state-default float-right ui-corner-all ui-button'
        );
        return $form;
    }

    function build_refill_coupon_grid()
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
                gettext("Coupon Number"),
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
                gettext("Description"),
                "150",
                "description",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Account"),
                "130",
                "account_id",
                "first_name,last_name,number,company_name",
                "accounts",
                "build_concat_string",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Amount")."<br/> ($currency)",
                "100",
                "amount",
                "amount",
                "amount",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Created Date"),
                "150",
                "creation_date",
                "creation_date",
                "creation_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Used?"),
                "135",
                "status",
                'status',
                'status',
                'get_refill_coupon_used',
                "",
                "true",
                "center"
            ),
            array(
                gettext("Used Date"),
                "180",
                "firstused",
                "firstused",
                "firstused",
                "firstused_check",
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
                    "DELETE" => array(
                        "url" => "refill_coupon/refill_coupon_list_delete/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_user_refill_coupon_grid()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $grid_field_arr = json_encode(array(
            array(
                gettext("Coupon Number"),
                "230",
                "number",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Description"),
                "210",
                "description",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Amount")." ($currency)",
                "190",
                "amount",
                "amount",
                "amount",
                "convert_to_currency",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Created Date"),
                "250",
                "creation_date",
                "creation_date",
                "creation_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Used Date"),
                "250",
                "firstused",
                "firstused",
                "firstused",
                "convert_GMT_to",
                "",
                "true",
                "center"
            )
        ));
        return $grid_field_arr;
    }
}
?>
