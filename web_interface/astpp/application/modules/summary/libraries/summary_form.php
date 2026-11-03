<?php
// ##########################################################################
// ASTPP - Open Source Voip Billing
// Copyright (C) 2004, Aleph Communications
//
// Contributor(s)
// "iNextrix Technologies Pvt. Ltd - <astpp@inextrix.com>"
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details..
//
// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>
// ###########################################################################
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Summary_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function get_providersummary_search_form()
    {
        $form['forms'] = array(
            '',
            array(
                'id' => "providersummary_search"
            )
        );
        $form['Search'] = array(
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
                'IF(`deleted`=1,concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( first_name, " ", last_name, " ", "(", number, ")" )) as number',
                'accounts',
                'build_dropdown_deleted',
                'where_arr',
                array(
                    "reseller_id" => "0",
                    "type" => "3"
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
                gettext('Code Destination'),
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

    function build_providersummary()
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        $new_arr = array();
        if ($this->CI->session->userdata('advance_search') == '1') {
            $search_array = $this->CI->session->userdata('providersummary_reports_search');
            if (isset($search_array['time']) && ! empty($search_array['time'])) {
                $new_arr[] = array(
                    $search_array['time'],
                    "151",
                    $search_array['time'] . "(callstart)",
                    "",
                    "",
                    ""
                );
            }
            if (isset($search_array['groupby_1']) && ! empty($search_array['groupby_1'])) {
                $first_column_groupby = $search_array['groupby_1'];
                if ($first_column_groupby == 'provider_id') {
                    $new_arr[] = array(
                        gettext("Account"),
                        "151",
                        "provider_id",
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($first_column_groupby == 'pattern') {
                    $new_arr[] = array(
                        gettext("Code"),
                        "65",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_arr[] = array(
                        gettext("Destination"),
                        "85",
                        "notes",
                        "",
                        "",
                        ""
                    );
                } elseif ($first_column_groupby == 'trunk_id') {
                    $new_arr[] = array(
                        gettext("Trunk"),
                        "151",
                        "trunk_id",
                        "name",
                        "trunks",
                        "get_field_name"
                    );
                } elseif ($first_column_groupby == 'package_id') {
                    $new_arr[] = array(
                        gettext("Package"),
                        "151",
                        "package_id",
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                }
            }
            if (isset($search_array['groupby_2']) && ! empty($search_array['groupby_2'])) {
                $third_column_groupby = $search_array['groupby_2'];
                if ($third_column_groupby == 'provider_id') {
                    $new_arr[] = array(
                        gettext("Account"),
                        "151",
                        "provider_id",
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($third_column_groupby == 'pattern') {
                    $new_arr[] = array(
                        gettext("Code"),
                        "65",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_arr[] = array(
                        gettext("Destination"),
                        "85",
                        "notes",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'trunk_id') {
                    $new_arr[] = array(
                        gettext("Trunk"),
                        "151",
                        "trunk_id",
                        "name",
                        "trunks",
                        "get_field_name"
                    );
                } elseif ($third_column_groupby == 'package_id') {
                    $new_arr[] = array(
                        gettext("Package"),
                        "151",
                        "package_id",
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                }
            }
            if (isset($search_array['groupby_3']) && ! empty($search_array['groupby_3'])) {
                $fifth_column_groupby = $search_array['groupby_3'];
                if ($fifth_column_groupby == 'provider_id') {
                    $new_arr[] = array(
                        gettext("Account"),
                        "151",
                        "provider_id",
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($fifth_column_groupby == 'pattern') {
                    $new_arr[] = array(
                        gettext("Code"),
                        "65",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_arr[] = array(
                        gettext("Destination"),
                        "85",
                        "notes",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'trunk_id') {
                    $new_arr[] = array(
                        gettext("Trunk"),
                        "151",
                        "trunk_id",
                        "name",
                        "trunks",
                        "get_field_name"
                    );
                } elseif ($fifth_column_groupby == 'package_id') {
                    $new_arr[] = array(
                        gettext("Package"),
                        "151",
                        "package_id",
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                }
            }
        }
        if (empty($new_arr))
            $new_arr[] = array(
                gettext("Account"),
                "300",
                "provider_id",
                "first_name,last_name,number",
                "accounts",
                "build_concat_string"
            );
        $fixed_arr = array(
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
                "130",
                "description",
                "",
                "",
                ""
            ),
            array(
                gettext("Duration"),
                "85",
                "billable",
                '',
                '',
                ''
            ),
            array(
                gettext("ASR"),
                "83",
                "asr",
                '',
                '',
                ''
            ),
            array(
                gettext("ACD"),
                "83",
                "acd  ",
                '',
                '',
                ''
            ),
            array(
                gettext("MCD"),
                "83",
                "mcd",
                '',
                '',
                ''
            ),
            array(
                gettext("Billable"),
                "102",
                "billable",
                '',
                '',
                ''
            ),
            array(
                gettext("Cost")." <br/>($currency)",
                "117",
                "cost",
                '',
                '',
                ''
            )
        );
        $grid_field_arr = json_encode(astpp_array_merge($new_arr, $fixed_arr));
        return $grid_field_arr;
    }

    function build_grid_buttons_providersummary()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/summary/provider_export_csv/",
                'single',
                "",
                "export"
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
        $form['Search'] = array(
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
                'IF(`deleted`=1,concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( first_name, " ", last_name, " ", "(", number, ")" )) as number',
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

    function build_resellersummary($new_column_arr)
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        $column_arr = array(
            array(
                gettext("Attempted Calls"),
                "115",
                "attempted_calls",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Completed Calls"),
                "115",
                "description",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Duration"),
                "85",
                "billable",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("ASR"),
                "78",
                "asr",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("ACD"),
                "78",
                "acd  ",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("MCD"),
                "78",
                "mcd",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("Billable"),
                "80",
                "billable",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("Debit")." <br/>($currency)",
                "80",
                "cost",
                '',
                '',
                '',
                "",
                "true",
                "right"
            ),
            array(
                gettext("Cost")." <br/>($currency)",
                "80",
                "price",
                '',
                '',
                '',
                "",
                "true",
                "right"
            ),
            array(
                gettext("Profit")." <br/>($currency)",
                "80",
                "profit",
                "",
                "",
                "",
                "",
                "true",
                "right"
            )
        );
        $grid_field_arr = json_encode(astpp_array_merge($new_column_arr, $column_arr));
        return $grid_field_arr;
    }

    function build_grid_buttons_resellersummary()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/summary/reseller_export_csv/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function get_customersummary_search_form()
    {
        $form['forms'] = array(
            base_url() . 'summary/customer_search',
            array(
                'id' => "customersummary_search",
                "name" => "customersummary_search"
            )
        );
        $form['Search'] = array(
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
                gettext('Accounts'),
                'accountid',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'IF(`deleted`=1,concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( first_name, " ", last_name, " ", "(", number, ")" )) as number',
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
                gettext('Code Destination'),
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
        $form['Group'] = array(
            array(
                gettext('Group By #1'),
                'groupby_1',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_summarycustomer_groupby'
            ),
            array(
                gettext('Group By #2'),
                'groupby_2',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_summarycustomer_groupby'
            ),
            array(
                gettext('Group By #3'),
                'groupby_3',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_summarycustomer_groupby'
            )
        );
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "customersummary_search_btn",
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

    function build_customersummary($new_column_arr)
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

        $column_arr = array(
            array(
                gettext("Attempted Calls"),
                "100",
                "attempted_calls",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),

            array(
                gettext("Completed Calls"),
                "90",
                "description",
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
                "billable",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("ASR"),
                "80",
                "asr",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("ACD"),
                "80",
                "acd  ",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("MCD"),
                "80",
                "mcd",
                '',
                '',
                '',
                "",
                "true",
                "center"
            ),
            array(
                gettext("Billable"),
                "90",
                "billable",
                '',
                '',
                '',
                "",
                "true",
                "right"
            ),
            array(
                gettext("Debit")."<br/> ($currency)",
                "87",
                "cost",
                "cost",
                "cost",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Cost")."<br/> ($currency)",
                "85",
                "price",
                "price",
                "price",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Profit")."<br/> ($currency)",
                "93",
                "profit",
                "profit",
                "profit",
                "convert_to_currency_account",
                "",
                "true",
                "right"
            )
        );
        $grid_field_arr = json_encode(astpp_array_merge($new_column_arr, $column_arr));
        return $grid_field_arr;
    }

    function build_grid_buttons_customersummary()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/summary/customer_export_csv/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }

    function build_product_summary($new_column_arr="")
    {
        $account_info = $accountinfo = $this->CI->session->userdata('accountinfo');
	$session_info = $this->CI->session->userdata('productsummary_reports_search');

	if((isset($session_info) && $session_info !="" )){
		$session_info['product_category'] = $session_info['product_category'];
	}else{
		$session_info['product_category'] = 1;
	}
        $currency_id = $account_info['currency_id'];
        $currency = $this->CI->common->get_field_name('currency', 'currency', $currency_id);

	if($session_info['product_category'] == 2){
		$free_minutes=array();
		$used_minutes=array();
		$available_minutes=array();
	}else{
		$free_minutes= array(
				gettext("Free Minutes"),
				"100",
				"free_minutes",
				"",
				"",
				"",
				"",
				"false",
				"right"
			    );
		$used_minutes= array(
				gettext("Used Minutes"),
				"93",
				"product_id",
				"product_id",
				"product_id",
				"get_available_seconds_for_package",
				"",
				"false",
				"right"
			    );
		$available_minutes=   array(
				gettext("Available Minutes"),
				"115",
				"productid",
				"productid",
				"productid",
				"get_available_seconds_for_package",
				"",
				"false",
				"right"
			    );

	}
	if(((isset($session_info['groupby_1']) && $session_info['groupby_1'] == "product_id" ) || (isset($session_info['groupby_2']) && $session_info['groupby_2'] == "product_id")) && ((isset($session_info['groupby_1']) && $session_info['groupby_1'] == "accountid") || (isset($session_info['groupby_2']) && $session_info['groupby_2'] == "accountid" ) ) ){ //echo 6565; exit;
	$new_column_arr = $new_column_arr;
	 $column_arr = array(
		array(
		        gettext("Quantity"),
		        "80",
		        "quantity",
		        '',
		        '',
		        '',
		        "",
		        "false",
		        "center"
		    ),

		    array(
		        gettext("Price"),
		        "80",
		        "price  ",
		        "price",
		        "price",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "center"
		    ),
		    array(
		        gettext("Setup Fee"),
		        "80",
		        "setup_fee  ",
		        "setup_fee",
		        "setup_fee",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "center"
		    ),
		   $free_minutes,
		   $used_minutes,
		   $available_minutes,
		    array(
		        gettext("Total Price"),
		        "93",
		        "",
		        "",
		        "",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "right"
		    ),
		    array(
		        gettext("Active User"),
		        "93",
		        "accountid",
		        "",
		        "",
		        "",
		        "",
		        "false",
		        "right"
		    ),
		    array(
		        gettext("Total User"),
		        "93",
		        "accountid",
		        "",
		        "",
		        "",
		        "",
		        "false",
		        "right"
		    ),

	);
	}else if((isset($session_info['groupby_1']) && $session_info['groupby_1'] == "product_id") || (isset($session_info['groupby_2']) && $session_info['groupby_2'] == "product_id" )){

	$new_column_arr = $new_column_arr;
	$column_arr = array(
		array(
		        gettext("Quantity"),
		        "80",
		        "quantity",
		        '',
		        '',
		        '',
		        "",
		        "false",
		        "center"
		    ),

		    array(
		        gettext("Price"),
		        "80",
		        "price  ",
		        "price",
		        "price",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "center"
		    ),
		    array(
		        gettext("Setup Fee"),
		        "80",
		        "setup_fee  ",
		        "setup_fee",
		        "setup_fee",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "center"
		    ),
		    $free_minutes,
		    $used_minutes,
		    $available_minutes,
		
		    array(
		        gettext("Total Price"),
		        "93",
		        "",
		        "",
		        "",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "right"
		    ),
		    array(
		        gettext("Active User"),
		        "93",
		        "accountid",
		        "",
		        "",
		        "",
		        "",
		        "false",
		        "right"
		    ),
		    array(
		        gettext("Total User"),
		        "93",
		        "accountid",
		        "",
		        "",
		        "",
		        "",
		        "false",
		        "right"
		    ),
	);
	}else if((isset($session_info['groupby_1']) && $session_info['groupby_1'] == "accountid") || (isset($session_info['groupby_2']) && $session_info['groupby_2'] == "accountid" )){ 
	$new_column_arr = $new_column_arr;
	$column_arr = array(
		
		    $free_minutes,
		    $used_minutes,
		    $available_minutes,
		    array(
		        gettext("Total Number of Product"),
		        "159",
		        "",
		        "",
		        "",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "right"
		    ),
		    

	);

	}else{ 
		if(isset($session_info['time']) && $session_info['time'] != ""){
			$new_column_arr = $new_column_arr;
		}else{
			$new_column_arr = array();
		}
	 $column_arr = array(
		array(
			gettext("Product Name"),
			"80",
			"name",
			'',
			'',
			'',
			"",
			"false",
			"center"
            	),
		array(
		        gettext("Quantity"),
		        "80",
		        "quantity",
		        '',
		        '',
		        '',
		        "",
		        "false",
		        "center"
		    ),

		    array(
		        gettext("Price"),
		        "80",
		        "price  ",
		        "price",
		        "price",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "center"
		    ),
		    array(
		        gettext("Setup Fee"),
		        "80",
		        "setup_fee  ",
		        "setup_fee",
		        "setup_fee",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "center"
		    ),
		    $free_minutes,
		    $used_minutes,
		    $available_minutes,
		 
		    array(
		        gettext("Total Price"),
		        "93",
		        "",
		        "",
		        "",
		        "convert_to_currency_account",
		        "",
		        "false",
		        "right"
		    ),
		    array(
		        gettext("Active User"),
		        "93",
		        "accountid",
		        "",
		        "",
		        "",
		        "",
		        "false",
		        "right"
		    ),
		    array(
		        gettext("Total User"),
		        "93",
		        "accountid",
		        "",
		        "",
		        "",
		        "",
		        "false",
		        "right"
		    ),
	);
	}	
	$grid_field_arr = json_encode(astpp_array_merge($new_column_arr, $column_arr));
        return $grid_field_arr;
    }
    function build_grid_buttons_products_summary()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/summary/product_export_csv/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }
}
?>
