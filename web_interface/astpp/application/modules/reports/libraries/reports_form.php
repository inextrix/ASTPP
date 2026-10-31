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

class Reports_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function get_customer_cdr_form()
    {
        $logintype = $this->CI->session->userdata('userlevel_logintype');
        // Kinjal issue no ASTPPCOM-978 Start
        $report_flag = $this->CI->db_model->countQuery("*", "addons", array(
            "package_name" => "automated_reports"
        ));
        $accountinfo = $this->CI->session->userdata['accountinfo'];

        // Kinjal issue no ASTPPCOM-978 END

        if ($logintype != 1) {
            if ($this->CI->session->userdata('logintype') == 1 || $this->CI->session->userdata('logintype') == 5) {
                $reseller_id = $accountinfo["id"];
            } else {
                $reseller_id = "0";
            }
            $form['forms'] = array(
                "",
                array(
                    'id' => "cdr_customer_search"
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
                    gettext('Code'),
                    'INPUT',
                    array(
                        'name' => 'pattern[pattern]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
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
                array(
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
                ),
                array(
                    gettext('Cost'),
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
                    '',
                    ''
                ),
                array(
                    gettext('Rate Group'),
                    array(
                        'name' => 'pricelist_id',
                        'id' => 'pricelist_id_search_drp',
                        'class' => 'pricelist_id_search_drp'
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
                        "status" => "0"
                    )
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
                    'IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( company_name, " ", "(", number, ")^" )),IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ),concat( company_name, " ", "(", number, ")" ))) as number',
                    'accounts',
                    'build_dropdown_deleted',
                    'where_arr',
                    array(
                        "reseller_id" => "0",
                        "type" => "GLOBAL"
                    )
                ),
                array(
                    gettext('Disposition')." [Q.850]",
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
                    gettext('Trunk'),
                    'trunk_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'IF(`status`=2, concat(name,"","^"),name) as name',
                    'trunks',
                    'build_dropdown_deleted',
                    '',
                    array(
                        "status" => "1"
                    )
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
        } else {
            $form['forms'] = array(
                "",
                array(
                    'id' => "cdr_customer_search"
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
                    gettext('Code'),
                    'INPUT',
                    array(
                        'name' => 'pattern[pattern]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'Tool tips info',
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
                array(
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
                ),
                array(
                    gettext('Cost'),
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
                    gettext('Disposition')." [Q.850]",
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
                // Ashish ASTPPCOM-825
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
                    'first_name,last_name,number,company_name',
                    'accounts',
                    'build_concat_dropdown_reseller',
                    'where_arr',
                    ''
                ),
                // ASTPPCOM-825 END
                array(
                    gettext('Account'),
                    // Ashish ASTPPCOM-825
                    array(
                        'name' => 'accountid',
                        'id' => 'accountid_search_drp',
                        'class' => 'accountid_search_drp'
                    ),
                    // ASTPPCOM-825 end
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
                        "reseller_id" => "0",
                        "type" => "GLOBAL"
                    )
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
                    'IF(`status`=2, concat(name,"","^"),name) as name',
                    'pricelists',
                    'build_dropdown_deleted',
                    'where_arr',
                    array(
                        "reseller_id" => "0"
                    )
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
            'id' => "cusotmer_cdr_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        // Kinjal ASTPPCOM-978 Start
        if($report_flag == '1' && $accountinfo['type'] == '-1'){
            $form['button_subscribe'] = array(
                'name' => 'action',
                'id' => 'subscribe_search_btn',
                'disabled' => 'true',
                'content' => gettext('Subscribe'),
                'value' => 'save',
                'type' => 'button',
                'class' => 'btn btn-info float-right mx-2 subscribe_btn',
                "mode" =>"popup",
                "layout"=>"small",
                "data-link" => "automated_report/automated_report_add/reports/"
            );
        }
        // Kinjal ASTPPCOM-978 END
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => 'Clear',
            'value' => gettext('Cancel'),
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );

        return $form;
    }

    function get_reseller_cdr_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "cdr_reseller_search"
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
                'number[number-string]'
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
                'number[number-string]'
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
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'pattern[pattern]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
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
            array(
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
            ),
            array(
                gettext('Cost'),
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
                gettext('Disposition')." [Q.850]",
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
                    "reseller_id" => "0",
                    "type" => "1",
                    "deleted" => "0"
                )
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
                'IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( company_name, " ", "(", number, ")^" )),IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ),concat( company_name, " ", "(", number, ")" ))) as number',
                'accounts',
                'build_dropdown_deleted',
                'where_arr',
                array(
                    "reseller_id" => "0",
                    "type" => "1"
                )
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
                'IF(`status`=2, concat(name,"","^"),name) as name',
                'pricelists',
                'build_dropdown_deleted',
                'where_arr',
                array(
                    "reseller_id" => "0"
                )
            ),
            array(
                gettext('Trunk'),
                'trunk_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'IF(`status`=2, concat(name,"","^"),name) as name',
                'trunks',
                'build_dropdown_deleted',
                '',
                array(
                    "status" => "1"
                )
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
                'reseller_cdrs',
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
        if ($this->CI->session->userdata('logintype') != 1 && $this->CI->session->userdata('logintype') != 5) {
            $new_Array = array(
                gettext('Trunk'),
                'trunk_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'trunks',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "1"
                )
            );
        } else {
            // trunks belong to the admin, resellers do not see their names
            foreach ($form[gettext('Search')] as $key => $field) {
                if (isset($field[1]) && $field[1] === 'trunk_id') {
                    unset($form[gettext('Search')][$key]);
                }
            }
        }

        $form['display_in'] = array(
            'name' => 'search_in',
            "id" => "search_in",
            "function" => "search_report_in",
            "content" => gettext("Display records in"),
            'label_class' => "search_label col-md-7 text-right",
            "dropdown_class" => "form-control col-md-5 rm-col-md-12",
            "label_style" => "font-size:13px;text-align:right;",
            "dropdown_style" => "background: #ddd; width: 23% !important;"
        );
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "reseller_cdr_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => 'Clear',
            'value' => gettext('Cancel'),
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );

        return $form;
    }

    function get_provider_cdr_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "cdr_provider_search"
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
                'number[number-string]'
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
                'number[number-string]'
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
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'pattern[pattern]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
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
            array(
                gettext('Cost'),
                'INPUT',
                array(
                    'name' => 'provider_call_cost[provider_call_cost]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'provider_call_cost[provider_call_cost-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Disposition')." [Q.850]",
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
                gettext('Account'),
                'provider_id',
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
                    "reseller_id" => "0",
                    "type" => "3"
                )
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
            'id' => "provider_cdr_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'Cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );

        return $form;
    }

    function build_report_list_for_customer()
    {
        $logintype = $this->CI->session->userdata('userlevel_logintype');
        $recording = array(
            gettext("Recording"),
            "127",
            "recording",
            "",
            "",
            "",
            "",
            "false",
            ""
        );
        if ($logintype != 1) {
            $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
            $currency_id = $account_info['currency_id'];
            $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
            $account_data = $this->CI->session->userdata("accountinfo");
            $grid_field_arr = json_encode(array(
                array(
                    gettext("Date"),
                    "85",
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
                    "80",
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
                    "90",
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
                    "80",
                    "sip_user",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Code"),
                    "71",
                    "pattern",
                    "pattern",
                    "",
                    "get_only_numeric_val",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Destination"),
                    "90",
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
                    "80",
                    "billseconds",
                    "customer_cdr_list_search",
                    "billseconds",
                    "convert_to_show_in",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Debit")." ($currency)",
                    "75",
                    "debit",
                    "debit",
                    "debit",
                    "convert_to_currency",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Cost")." ($currency)",
                    "75",
                    "cost",
                    "cost",
                    "cost",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Disposition")." [Q.850]",
                    "130",
                    "disposition",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Account"),
                    "100",
                    "accountid",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "build_concat_string",
                    "",
                    "false",
                    "center"
                ),
                array(
                    gettext("Trunk"),
                    "85",
                    "trunk_id",
                    "name",
                    "trunks",
                    "get_field_name",
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
                    gettext("Call Type"),
                    "85",
                    "calltype",
                    "",
                    "",
                    ""
                ),
                array(
                    gettext("Direction"),
                    "90",
                    "call_direction",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),

                $recording
            ));
        } else {
            $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
            $currency_id = $account_info['currency_id'];
            $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
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
                    "103",
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
                    "80",
                    "sip_user",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Code"),
                    "55",
                    "pattern",
                    "pattern",
                    "",
                    "get_only_numeric_val",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Destination"),
                    "80",
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
                    "70",
                    "billseconds",
                    "customer_cdr_list_search",
                    "billseconds",
                    "convert_to_show_in",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Debit")."($currency)",
                    "75",
                    "debit",
                    "debit",
                    "debit",
                    "convert_to_currency",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Cost")."($currency)",
                    "75",
                    "cost",
                    "cost",
                    "cost",
                    "convert_to_currency",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Disposition")." [Q.850]",
                    "130",
                    "disposition",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Account"),
                    "110",
                    "accountid",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "build_concat_string",
                    "",
                    "false",
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
                    gettext("Direction"),
                    "80",
                    "call_direction",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),

                $recording
            ));
        }

        return $grid_field_arr;
    }

    function build_report_list_for_reseller()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $logintype = $this->CI->session->userdata('userlevel_logintype');
        if ($logintype != 1) {
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
                    "90",
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
                    "110",
                    "callednum",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Code"),
                    "80",
                    "pattern",
                    "pattern",
                    "",
                    "get_only_numeric_val",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Destination"),
                    "110",
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
                    "reseller_cdr_list_search",
                    "billseconds",
                    "convert_to_show_in",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Debit")." <br/> ($currency)",
                    "100",
                    "debit",
                    "debit",
                    "debit",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Cost")." <br/> ($currency)",
                    "90",
                    "cost",
                    "cost",
                    "cost",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Disposition")." [Q.850]",
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
                    gettext("Account"),
                    "110",
                    "accountid",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "build_concat_string",
                    "",
                    "false",
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
                    gettext("Trunks"),
                    "100",
                    "trunk_id",
                    "name",
                    "trunks",
                    "get_field_name",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Call Type"),
                    "100",
                    "calltype",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Direction"),
                    "80",
                    "call_direction",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                )
            ));
        } else {
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
                    "90",
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
                    "110",
                    "callednum",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Code"),
                    "80",
                    "pattern",
                    "pattern",
                    "",
                    "get_only_numeric_val",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Destination"),
                    "110",
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
                    "reseller_cdr_list_search",
                    "billseconds",
                    "convert_to_show_in",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Debit")." <br/> ($currency)",
                    "100",
                    "debit",
                    "debit",
                    "debit",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Cost")." <br/> ($currency)",
                    "90",
                    "cost",
                    "cost",
                    "cost",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Disposition")." [Q.850]",
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
                    gettext("Account"),
                    "110",
                    "accountid",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "build_concat_string",
                    "",
                    "false",
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
                    gettext("Direction"),
                    "80",
                    "call_direction",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                )
            ));
        }
        return $grid_field_arr;
    }

    function build_report_list_for_provider()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
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
                "120",
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
                "110",
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
                gettext("Code"),
                "90",
                "pattern",
                "pattern",
                "",
                "get_only_numeric_val",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Destination"),
                "130",
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
                "110",
                "billseconds",
                "provider_cdr_list_search",
                "billseconds",
                "convert_to_show_in",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Cost")."<br/>($currency)",
                "100",
                "provider_call_cost",
                "provider_cost",
                "provider_cost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),

            array(
                gettext("Disposition")." [Q.850]",
                "200",
                "disposition",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Account"),
                "120",
                "provider_id",
                "first_name,last_name,number,company_name",
                "accounts",
                "build_concat_string",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Call Type"),
                "100",
                "calltype",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Direction"),
                "110",
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

    function build_grid_customer()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/reports/customerReport_export/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function build_grid_buttons_user()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                " fa fa-download fa-lg",
                "button_action",
                "/user/user_report_export/",
                'single'
            )
        ));
        return $buttons_json;
    }

    function build_grid_buttons_reseller()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/reports/resellerReport_export/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function build_grid_buttons_provider()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/reports/providerReport_export/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function build_report_list_for_user($accounttype = 'customer')
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        if ($accounttype == 'customer' || $accounttype == 'reseller') {
            $cost_array = array(
                gettext("Debit")."($currency)",
                "100",
                "debit",
                "debit",
                "debit",
                "convert_to_currency",
                "",
                "true",
                "right"
            );
        }
        if (strtolower($accounttype) == 'provider') {
            $cost_array = array(
                gettext("Debit")."($currency)",
                "140",
                "cost",
                "cost",
                "cost",
                "convert_to_currency"
            );
        }
        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "130",
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
                "120",
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
                "135",
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
                "120",
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
                gettext("Disposition")." [Q.850]",
                "160",
                "disposition",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Call Type"),
                "140",
                "calltype",
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

    function build_refill_report_for_admin()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "100",
                "payment_date",
                "",
                "",
                "",
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
                "true",
                "center"
            ),
            array(
                gettext("Amount")." <br/> ($currency)",
                "80",
                "amount",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Payment Method"),
                "80",
                "",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Transaction ID"),
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
                gettext("Receiver Email"),
                "120",
                "",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Client IP"),
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
                gettext("Description"),
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
                gettext("Reseller"),
                "100",
                "reseller_id",
                "first_name,last_name,number",
                "accounts",
                "reseller_select_value",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Status"),
                "80",
                "",
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

    function build_refillreport_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/reports/refillreport_export/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function build_search_refill_report_for_admin()
    {
        $accountinfo = $this->CI->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $form['forms'] = array(
            "",
            array(
                'id' => "cdr_refill_search"
            )
        );
        $account_data = $this->CI->session->userdata("accountinfo");
        $acc_arr = array(
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
        );
        $logintype = $this->CI->session->userdata('logintype');
        if ($logintype == 1 || $logintype == 5) {
            $account_data = $this->CI->session->userdata("accountinfo");
            $loginid = $account_data['id'];
        } else {
            $loginid = "0";
        }
        if ($logintype == 0 || $logintype == 3) {
            $acc_arr = null;
        }

        if ($account_data['type'] == - 1) {
            $reseller_drp = array(
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
                '',
                ''
            );
        } else {
            $reseller_drp = null;
        }
        if ($logintype == - 1 || $logintype == 2) {
            $form[gettext('Search')] = array(
                array(
                    gettext('From Date'),
                    'INPUT',
                    array(
                        'name' => 'date[]',
                        'id' => 'refill_from_date',
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
                        'id' => 'refill_to_date',
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
                        'name' => 'amount[amount]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'amount[amount-integer]',
                    '',
                    '',
                    '',
                    'search_int_type',
                    ''
                ),
                $reseller_drp,
                $acc_arr,
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

            $form[gettext('Search')] = array(
                array(
                    gettext('From Date'),
                    'INPUT',
                    array(
                        'name' => 'date[]',
                        'id' => 'refill_from_date',
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
                        'id' => 'refill_to_date',
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
                        'name' => 'actual_amount[actual_amount]',
                        'value' => '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'Tool tips info',
                    '1',
                    'actual_amount[actual_amount-integer]',
                    '',
                    '',
                    '',
                    'search_int_type',
                    ''
                ),
                array(
                    gettext('Account'),
                    array(
                        'name' => 'accountid'
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
            'id' => "cusotmer_cdr_refill_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'Cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );
        return $form;
    }

    function build_commission_report_for_admin()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Account"),
                "150",
                "accountid",
                "first_name,last_name,number",
                "accounts",
                "build_concat_string"
            ),
            array(
                gettext("Amount"),
                "150",
                "amount",
                "credit",
                "credit",
                "convert_to_currency"
            ),
            array(
                gettext("Description"),
                "150",
                "description",
                "",
                "",
                ""
            ),
            array(
                gettext("Reseller"),
                "150",
                "reseller_id",
                "first_name,last_name,number",
                "accounts",
                "build_concat_string"
            ),
            array(
                gettext("Commission Rate")."(%)",
                "150",
                "commission_percent",
                "",
                "",
                ""
            ),
            array(
                gettext("Date"),
                "150",
                "date",
                "",
                "",
                ""
            )
        ));
        return $grid_field_arr;
    }

    function reseller_commission_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "reseller_commission_search"
            )
        );
        $form['User Refill Report'] = array(
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
            ),
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'date[]',
                    'id' => 'commission_from_date',
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
                    'name' => 'date[]',
                    'id' => 'commission_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'end_date[end_date-date]'
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
                    "type" => "1",
                    "deleted" => "0"
                )
            ),
            array(
                gettext('Amount'),
                'INPUT',
                array(
                    'name' => 'amount[amount]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'Tool tips info',
                '1',
                'amount[amount-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            )
        );

        $form['button_search'] = array(
            'name' => 'action',
            'id' => "commission_search_btn",
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

    function get_providersummary_search_form()
    {
        $form['forms'] = array(
            '',
            array(
                'id' => "providersummary_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'callstart[]',
                    'id' => 'customer_from_date',
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
                    'id' => 'customer_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'end_date[end_date-date]'
            ),
            array(
                gettext('Account'),
                'provider_id',
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
                    "reseller_id" => "0",
                    "type" => "3"
                )
            ),
            array(
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'pattern[pattern]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
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
            'id' => "providersummary_search_btn",
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

    function build_providersummary()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Provider"),
                "220",
                "provider_id",
                "first_name,last_name,number",
                "accounts",
                "build_concat_string"
            ),
            array(
                gettext("Code"),
                "120",
                "pattern",
                "pattern",
                "",
                "get_only_numeric_val"
            ),
            array(
                gettext("Destination"),
                "150",
                "notes",
                "",
                "",
                ""
            ),
            array(
                gettext("Attempted Calls"),
                "130",
                "attempted_calls",
                "",
                "",
                ""
            ),
            array(
                gettext("Completed Calls"),
                "150",
                "description",
                "",
                "",
                ""
            ),
            array(
                gettext("ASR"),
                "95",
                "asr",
                '',
                '',
                ''
            ),
            array(
                gettext("ACD"),
                "95",
                "acd  ",
                '',
                '',
                ''
            ),
            array(
                gettext("MCD"),
                "95",
                "mcd",
                '',
                '',
                ''
            ),
            array(
                gettext("Bilable"),
                "100",
                "billable",
                '',
                '',
                ''
            ),
            array(
                gettext("Cost"),
                "115",
                "cost",
                '',
                '',
                ''
            )
        ));
        return $grid_field_arr;
    }

    function build_grid_buttons_providersummary()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                " fa fa-download fa-lg",
                "button_action",
                "/reports/providersummary_export_cdr_xls",
                'single'
            )
        ));
        return $buttons_json;
    }

    function get_resellersummary_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "resellersummary_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'callstart[]',
                    'id' => 'customer_from_date',
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
                    'id' => 'customer_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'end_date[end_date-date]'
            ),
            array(
                gettext('Account'),
                'reseller_id',
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
                    "reseller_id" => "0",
                    "type" => "1"
                )
            ),
            array(
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'pattern[pattern]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
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
            'id' => "resellersummary_search_btn",
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

    function build_resellersummary()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Account"),
                "148",
                "accountid",
                "first_name,last_name,number",
                "accounts",
                "build_concat_string"
            ),
            array(
                gettext("Code"),
                "120",
                "pattern",
                "pattern",
                "",
                "get_only_numeric_val"
            ),
            array(
                gettext("Destination"),
                "150",
                "notes",
                "",
                "",
                ""
            ),
            array(
                gettext("Attempted Calls"),
                "120",
                "attempted_calls",
                "",
                "",
                ""
            ),
            array(
                gettext("Completed Calls"),
                "120",
                "description",
                "",
                "",
                ""
            ),
            array(
                gettext("ASR"),
                "80",
                "asr",
                '',
                '',
                ''
            ),
            array(
                gettext("ACD"),
                "80",
                "acd  ",
                '',
                '',
                ''
            ),
            array(
                gettext("MCD"),
                "80",
                "mcd",
                '',
                '',
                ''
            ),
            array(
                gettext("Bilable"),
                "90",
                "billable",
                '',
                '',
                ''
            ),
            array(
                gettext("Price"),
                "90",
                "price",
                '',
                '',
                ''
            ),
            array(
                gettext("Cost"),
                "90",
                "cost",
                '',
                '',
                ''
            ),
            array(
                gettext("Profit"),
                "100",
                "profit",
                "",
                "",
                ""
            )
        ));
        return $grid_field_arr;
    }

    function build_grid_buttons_resellersummary()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                " fa fa-download fa-lg",
                "button_action",
                "/reports/resellersummary_export_cdr_xls",
                'single'
            )
        ));
        return $buttons_json;
    }

    function get_customersummary_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "customersummary_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'callstart[]',
                    'id' => 'customer_from_date',
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
                    'id' => 'customer_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'end_date[end_date-date]'
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
                'IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( company_name, " ", "(", number, ")^" )),IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ),concat( company_name, " ", "(", number, ")" ))) as number',
                'accounts',
                'build_dropdown_deleted',
                'where_arr',
                array(
                    "reseller_id" => "0",
                    "type" => "GLOBAL"
                )
            ),
            array(
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'pattern[pattern]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
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
            'id' => "customersummary_search_btn",
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

    function build_customersummary()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Account"),
                "190",
                "accountid",
                "first_name,last_name,number",
                "accounts",
                "build_concat_string"
            ),
            array(
                gettext("Code"),
                "80",
                "pattern",
                "pattern",
                "",
                "get_only_numeric_val"
            ),
            array(
                gettext("Destination"),
                "110",
                "notes",
                "",
                "",
                ""
            ),
            array(
                gettext("Attempted Calls"),
                "140",
                "attempted_calls",
                "",
                "",
                ""
            ),
            array(
                gettext("Completed Calls"),
                "130",
                "description",
                "",
                "",
                ""
            ),
            array(
                gettext("ASR"),
                "70",
                "asr",
                '',
                '',
                ''
            ),
            array(
                gettext("ACD"),
                "70",
                "acd  ",
                '',
                '',
                ''
            ),
            array(
                gettext("MCD"),
                "80",
                "mcd",
                '',
                '',
                ''
            ),
            array(
                gettext("Bilable"),
                "80",
                "billable",
                '',
                '',
                ''
            ),
            array(
                gettext("Debit"),
                "85",
                "cost",
                '',
                '',
                ''
            ),
            array(
                gettext("Cost"),
                "110",
                "price",
                '',
                '',
                ''
            ),
            array(
                gettext("Profit"),
                "123",
                "profit",
                "",
                "",
                ""
            )
        ));
        return $grid_field_arr;
    }

    function build_grid_buttons_customersummary()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                " fa fa-download fa-lg",
                "button_action",
                "/reports/customersummary_export_cdr_xls",
                'single'
            )
        ));
        return $buttons_json;
    }

    function build_refillreport_for_customer()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "225",
                "date",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Amount")." ($currency)",
                "250",
                "actual_amount",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Refill By"),
                "230",
                "payment_method",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Note"),
                "325",
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

    function build_commissionreports_list_for_admin()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);
        if ($account_info['type'] == 1) {
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
                    gettext("Product Name"),
                    "110",
                    "product_id",
                    "name",
                    "products",
                    "get_field_name",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("Order"),
                    "140",
                    "order_id",
                    "order_id",
                    "orders",
                    "get_order_id",
                    "EDITABLE",
                    "true",
                    "right"
                ),
                array(
                    gettext("Account"),
                    "100",
                    "accountid",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "build_concat_string",
                    "",
                    "true",
                    "center"
                ),

                array(
                    gettext("Amount") ." ($currency)",
                    "140",
                    "amount",
                    "amount",
                    "amount",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Commission") ." ($currency)",
                    "140",
                    "commission",
                    "commission",
                    "commission",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Commission")." (%)",
                    "140",
                    "commission_rate",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Status"),
                    "140",
                    "commission_status",
                    "commission_status",
                    "commission_status",
                    "payment_status_icon",
                    "",
                    "true",
                    "center"
                ),

                array(
                    gettext("Created Date"),
                    "140",
                    "creation_date",
                    "creation_date",
                    "creation_date",
                    "convert_GMT_to"
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
        } else {
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
                    gettext("Product Name"),
                    "110",
                    "product_id",
                    "name",
                    "products",
                    "get_field_name",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("Order"),
                    "140",
                    "order_id",
                    "order_id",
                    "orders",
                    "get_order_id",
                    "EDITABLE",
                    "true",
                    "right"
                ),
                array(
                    gettext("Reseller"),
                    "80",
                    "reseller_id",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "build_concat_string",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Account"),
                    "100",
                    "accountid",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "build_concat_string",
                    "",
                    "true",
                    "center"
                ),

                array(
                    gettext("Amount") . " ($currency)",
                    "140",
                    "amount",
                    "amount",
                    "amount",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Commission") . " ($currency)",
                    "140",
                    "commission",
                    "commission",
                    "commission",
                    "convert_to_currency_account",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Commission")." (%)",
                    "140",
                    "commission_rate",
                    "commission_rate",
                    "commission_rate",
                    "decimal_currency",
                    "",
                    "true",
                    "right"
                ),
                array(
                    gettext("Status"),
                    "140",
                    "commission_status",
                    "commission_status",
                    "commission_status",
                    "payment_status_icon",
                    "",
                    "true",
                    "center"
                ),

                array(
                    gettext("Created Date"),
                    "140",
                    "creation_date",
                    "creation_date",
                    "creation_date",
                    "convert_GMT_to"
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
        }
        return $grid_field_arr;
    }

    function build_grid_buttons_commissionreports()
    {
        $buttons_json = json_encode(array(

            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/reports/commission_report_export_data_xls",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function get_commissionreports_search_form()
    {
        $account_data = $this->CI->session->userdata("accountinfo");
        $reseller_id = $account_data['type'] == 1 ? $account_data['id'] : 0;
        $form['forms'] = array(
            "",
            array(
                'id' => "commissions_report_search"
            )
        );
        if ($account_data['type'] == - 1 || $account_data['type'] == 2) {
            $form[gettext('Search')] = array(
                array(
                    gettext('Product Name'),
                    'product_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'products',
                    'build_dropdown',
                    'where_arr',
                    ""
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
                array(
                    gettext('Order ID'),
                    'order_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'order_id',
                    'orders',
                    'build_concat_dropdown_reseller_order',
                    'where_arr',
                    ""
                ),
                array(
                    gettext('Commission'),
                    'INPUT',
                    array(
                        'name' => 'commission[commission]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'commission[commission-integer]',
                    '',
                    '',
                    '',
                    'search_int_type',
                    ''
                ),
                array(
                    gettext('Commission Rate'),
                    'INPUT',
                    array(
                        'name' => 'commission_rate[commission_rate]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'commission_rate[commission_rate-integer]',
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
        } else {
            $form[gettext('Search')] = array(
                array(
                    gettext('Product Name'),
                    'product_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'name',
                    'products',
                    // listed the products of all resellers
                    'build_dropdown_reseller_products',
                    'where_arr',
                    ""
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
                        "reseller_id" => $reseller_id,
                        "type" => "GLOBAL"
                    )
                ),
                array(
                    gettext('Order ID'),
                    'order_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'order_id',
                    'orders',
                    'build_concat_dropdown_reseller_order',
                    'where_arr',
                    ""
                ),
                array(
                    gettext('Commission'),
                    'INPUT',
                    array(
                        'name' => 'commission[commission]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'commission[commission-integer]',
                    '',
                    '',
                    '',
                    'search_int_type',
                    ''
                ),
                array(
                    gettext('Commission Rate'),
                    'INPUT',
                    array(
                        'name' => 'commission_rate[commission_rate]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'commission_rate[commission_rate-integer]',
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
        }
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "commissions_report_search_btn",
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

