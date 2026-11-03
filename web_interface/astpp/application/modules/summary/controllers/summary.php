
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
#[\AllowDynamicProperties]
class Summary extends MX_Controller
{
 
    function __construct()
    {
        parent::__construct();
 
        $this->load->helper('template_inheritance');
 
        $this->load->library('session');
        $this->load->library('astpp/form', 'summary_form');
        $this->load->library("summary_form");
        $this->load->model('summary_model');
        $this->load->library('ASTPP_Sms');
 
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }
 
    function customer()
    {
        $data['page_title'] = gettext('Customer Summary Report');
        $data['search_flag'] = true;
        $session_info = $this->session->userdata('customersummary_reports_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ), concat( company_name, " ", "(", number, ")^" )), IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ), concat( company_name, " ", "(", number, ")" ))) as number', 'accounts', 'where_arr', array(
            'reseller_id' => $reseller_id,
            "type" => "GLOBAL"
        ));
        $data['cdrs_year'] = $this->common->set_year_dropdown('cdrs');
        $data['accountlist'] = $accountlist;
        $data['session_info'] = $session_info;
        $data['cdrs_year_val'] = $this->session->userdata('customer_cdrs_year');
        $data['search_string_type'] = $this->common->search_string_type();
        $data['search_report'] = $this->common->search_report_in();
        $new_column_arr = $this->summary_column_arr('customer');
        $data['grid_fields'] = $this->summary_form->build_customersummary($new_column_arr);
        $data["grid_buttons"] = $this->summary_form->build_grid_buttons_customersummary();
        $data['groupby_field'] = $this->common->set_summarycustomer_groupby();
        $data['groupby_time'] = $this->common->group_by_time();
        $data['accountinfo'] = $accountinfo;
        $this->load->view('view_customersummary_report', $data);
    }
 
    function customer_json()
    {
        $search_arr = $this->summary_search_info('customer');
        $count_all = $this->summary_model->get_customersummary_report_list(false, 0, 0, $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->summary_model->get_customersummary_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        if ($query->num_rows() > 0) {
            $json_data['rows'] = $this->summary_report_grid($search_arr, $query, 'customer', 'grid');
        }
        $this->session->set_userdata('customersummary_reports_export', $search_arr);
        echo json_encode($json_data);
    }
 
    function summary_column_arr($entity)
    {
        $new_column_arr = array();
        $total_width = '322';
        $column_name = 'accountid';
        if ($this->session->userdata('advance_search') == '1') {
            $search_array = $this->session->userdata($entity . 'summary_reports_search');
 
     if (isset($search_array['groupby_1']) && isset($search_array['groupby_2']) &&  $search_array['groupby_1'] == $search_array['groupby_2']) {
        unset($search_array['groupby_2']);
     }
 
            if (isset($search_array['time']) && ! empty($search_array['time'])) {
                $entity_order = $entity == 'product' ? 'order_date' : 'callstart';
                $new_column_arr[] = array(
                    ucfirst(strtolower($search_array['time'])),
                    "58",
                    $search_array['time'] . "(" . $entity_order . ")",
                    "",
                    "",
                    ""
                );
            }
            if (isset($search_array['groupby_1']) && ! empty($search_array['groupby_1'])) {
                $first_column_groupby = $search_array['groupby_1'];
 
                if ($first_column_groupby == 'accountid' || $first_column_groupby == 'order_items.accountid') {
                    $new_column_arr[] = array(
                        gettext("Account"),
                        "100",
                        'accountid',
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($first_column_groupby == 'sip_user') {
                    $new_column_arr[] = array(
                        gettext("SIP User"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($first_column_groupby == 'call_direction') {
                    $new_column_arr[] = array(
                        gettext("Direction"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($first_column_groupby == 'calltype') {
                    $new_column_arr[] = array(
                        gettext("Call Type"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($first_column_groupby == 'pattern') {
                    $new_column_arr[] = array(
                        gettext("Code"),
                        "45",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_column_arr[] = array(
                        gettext("Destination"),
                        "59",
                        "notes",
                        "",
                        "",
                        ""
                    );
                    } elseif ($first_column_groupby == 'trunk_id') {
                    $new_column_arr[] = array(
                        gettext("Trunk"),
                        "45",
                        "",
                        "",
                        "",
                        ""
                    );
                    } elseif ($first_column_groupby == 'package_id') {
                    $new_column_arr[] = array(
                        gettext("Package"),
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($first_column_groupby == 'order_items.product_id' || $first_column_groupby == 'product_id') {
                    $new_column_arr[] = array(
                        gettext("Products"),
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($first_column_groupby == 'order_items.product_category' || $first_column_groupby == 'product_category') {
                    $new_column_arr[] = array(
                        gettext("Category"),
                        "105",
                        'product_category',
                        "name",
                        "category",
                        "get_field_name"
                    );
                }
            }
            if (isset($search_array['groupby_2']) && ! empty($search_array['groupby_2'])) {
                $third_column_groupby = $search_array['groupby_2'];
                if ($third_column_groupby == 'accountid' || $third_column_groupby == 'order_items.accountid') {
                    $new_column_arr[] = array(
                        gettext("Account"),
                        "100",
                        'accountid',
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($third_column_groupby == 'sip_user') {
                    $new_column_arr[] = array(
                        gettext("SIP User"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'call_direction') {
                    $new_column_arr[] = array(
                        gettext("Direction"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'calltype') {
                    $new_column_arr[] = array(
                        gettext("Call Type"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'pattern') {
                    $new_column_arr[] = array(
                        gettext("Code"),
                        "45",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_column_arr[] = array(
                        gettext("Destination"),
                        "59",
                        "notes",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'trunk_id') {
                    $new_column_arr[] = array(
                        gettext("Trunk"),
                        "45",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($third_column_groupby == 'package_id') {
                    $new_column_arr[] = array(
                        gettext("Package"),
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($third_column_groupby == 'order_items.product_id' || $third_column_groupby == 'product_id') {
                    $new_column_arr[] = array(
                        gettext("Products"),
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($third_column_groupby == 'order_items.product_category' || $third_column_groupby == 'product_category') {
                    $new_column_arr[] = array(
                        gettext("Category"),
                        "105",
                        'product_category',
                        "name",
                        "category",
                        "get_field_name"
                    );
                }
            }
            if (isset($search_array['groupby_3']) && ! empty($search_array['groupby_3'])) {
                $fifth_column_groupby = $search_array['groupby_3'];
                if ($fifth_column_groupby == 'accountid' || $fifth_column_groupby == 'order_items.accountid') {
                    $new_column_arr[] = array(
                        gettext("Account"),
                        "105",
                        'accountid',
                        "first_name,last_name,number",
                        "accounts",
                        "build_concat_string"
                    );
                } elseif ($fifth_column_groupby == 'sip_user') {
                    $new_column_arr[] = array(
                        gettext("SIP User"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'call_direction') {
                    $new_column_arr[] = array(
                        gettext("Direction"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'calltype') {
                    $new_column_arr[] = array(
                        gettext("Call Type"),
                        "105",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'pattern') {
                    $new_column_arr[] = array(
                        gettext("Code"),
                        "45",
                        "pattern",
                        "pattern",
                        "",
                        "get_only_numeric_val"
                    );
                    $new_column_arr[] = array(
                        gettext("Destination"),
                        "59",
                        "notes",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'trunk_id') {
                    $new_column_arr[] = array(
                        gettext("Trunk"),
                        "45",
                        "",
                        "",
                        "",
                        ""
                    );
                } elseif ($fifth_column_groupby == 'package_id') {
                    $new_column_arr[] = array(
                        gettext("Package"),
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($fifth_column_groupby == 'order_items.product_id' || $fifth_column_groupby == 'product_id') {
                    $new_column_arr[] = array(
                        gettext("Products"),
                        "105",
                        'product_id',
                        "name",
                        "products",
                        "get_field_name"
                    );
                } elseif ($fifth_column_groupby == 'order_items.product_category' || $fifth_column_groupby == 'product_category') {
                    $new_column_arr[] = array(
                        gettext("Category"),
                        "105",
                        'product_category',
                        "name",
                        "category",
                        "get_field_name"
                    );
                }
            }
            if (empty($new_column_arr)) {
                $new_column_arr[] = array(
                    gettext("Account"),
                    '322',
                    'accountid',
                    "first_name,last_name,number",
                    "accounts",
                    "build_concat_string"
                );
            }
        } else {
            $new_column_arr[] = array(
                gettext("Account"),
                '322',
                'accountid',
                "first_name,last_name,number",
                "accounts",
                "build_concat_string"
            );
        }
 
        return $new_column_arr;
    }
 
    function summary_report_grid($search_arr, $query, $entity, $purpose)
    {
        $export_arr = array();
        $db_field_name = $entity == 'provider' ? 'provider_id' : 'accountid';
        $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
        $currency_info = $this->common->get_currency_info();
        foreach ($query->result_array() as $row1) {
            $atmpt = $row1['attempts'];
            $cmplt = ((!astpp_eq0($row1['completed']))) ? $row1['completed'] : 0;
            $acd = (astpp_gt($row1['completed'], 0)) ? astpp_round(astpp_div($row1['duration'], $row1['completed'])) : 0;
            $mcd = $row1['mcd'];
            if ($show_seconds == 'minutes') {
                $avgsec = astpp_gt($acd, 0) ? sprintf('%02d', astpp_n($acd) / 60) . ":" . sprintf('%02d', (astpp_n($acd) % 60)) : "00:00";
                $maxsec = astpp_gt($mcd, 0) ? sprintf('%02d', astpp_n($mcd) / 60) . ":" . sprintf('%02d', (astpp_n($mcd) % 60)) : "00:00";
                $duration = (astpp_gt($row1['duration'], 0)) ? sprintf('%02d', astpp_n($row1['duration']) / 60) . ":" . sprintf('%02d', (astpp_n($row1['duration']) % 60)) : "00:00";
                $billsec = (astpp_gt($row1['billable'], 0)) ? sprintf('%02d', astpp_n($row1['billable']) / 60) . ":" . sprintf('%02d', (astpp_n($row1['billable']) % 60)) : "00:00";
            } else {
                $duration = sprintf('%02d', $row1['duration']);
                $avgsec = $acd;
                $maxsec = $mcd;
                $billsec = sprintf('%02d', $row1['billable']);
            }
            if ($entity != 'provider') {
                $profit = $this->common->calculate_currency_manually($currency_info, astpp_n($row1['debit']) - astpp_n($row1['cost']), false);
                $debit = $this->common->calculate_currency_manually($currency_info, $row1['debit'], false);
            }
            $cost = $this->common->calculate_currency_manually($currency_info, $row1['cost'], false);
            $asr = (astpp_gt($atmpt, 0)) ? (astpp_round((astpp_div($cmplt, $atmpt)) * 100, 2)) : '0.00';
            $new_arr = array();
 
            if ($this->session->userdata('advance_search') == 1) {
                if (! empty($search_arr['groupby_time'])) {
                    $time = $row1[$search_arr['groupby_time']];
 
                    if ($search_arr['groupby_time'] == "HOUR" || $search_arr['groupby_time'] == "DAY") {
                        $time = sprintf('%02d', $time);
                    }
                    if ($search_arr['groupby_time'] == "MONTH") {
                        $dateObj = DateTime::createFromFormat('!m', $time);
                        $time = $dateObj->format('F');
                    }
                    $new_arr[] = $time;
                }
                if ($search_arr['groupby_1'] == $db_field_name) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
                } elseif ($search_arr['groupby_1'] == 'pattern') {
                    $new_arr[] = filter_var($row1['pattern'], FILTER_SANITIZE_NUMBER_INT);
                    $new_arr[] = $row1['notes'];
                } elseif ($search_arr['groupby_1'] == 'trunk_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'trunks', $row1['trunk_id']);
                } elseif ($search_arr['groupby_1'] == 'package_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['package_id']);
                } elseif ($search_arr['groupby_1'] == 'sip_user') {
                    $new_arr[] = $row1['sip_user'];
                } elseif ($search_arr['groupby_1'] == 'call_direction') {
                    $new_arr[] = $row1['call_direction'];
                } elseif ($search_arr['groupby_1'] == 'calltype') {
                    $new_arr[] = $row1['calltype'];
                } elseif ($search_arr['groupby_1'] == 'product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_1'] == "product_category") {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }
                if ($search_arr['groupby_2'] == $db_field_name) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
                } elseif ($search_arr['groupby_2'] == 'pattern') {
                    $new_arr[] = filter_var($row1['pattern'], FILTER_SANITIZE_NUMBER_INT);
                    $new_arr[] = $row1['notes'];
                } elseif ($search_arr['groupby_2'] == 'trunk_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'trunks', $row1['trunk_id']);
                } elseif ($search_arr['groupby_2'] == 'package_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['package_id']);
                } elseif ($search_arr['groupby_2'] == 'sip_user') {
                    $new_arr[] = $row1['sip_user'];
                } elseif ($search_arr['groupby_2'] == 'call_direction') {
                    $new_arr[] = $row1['call_direction'];
                } elseif ($search_arr['groupby_2'] == 'calltype') {
                    $new_arr[] = $row1['calltype'];
                } elseif ($search_arr['groupby_2'] == 'product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_2'] == "product_category") {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }
 
                if ($search_arr['groupby_3'] == $db_field_name) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
                } elseif ($search_arr['groupby_3'] == 'pattern') {
                    $new_arr[] = filter_var($row1['pattern'], FILTER_SANITIZE_NUMBER_INT);
                    $new_arr[] = $row1['notes'];
                } elseif ($search_arr['groupby_3'] == 'trunk_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'trunks', $row1['trunk_id']);
                } elseif ($search_arr['groupby_3'] == 'package_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['package_id']);
                } elseif ($search_arr['groupby_3'] == 'sip_user') {
                    $new_arr[] = $row1['sip_user'];
                } elseif ($search_arr['groupby_3'] == 'call_direction') {
                    $new_arr[] = $row1['call_direction'];
                } elseif ($search_arr['groupby_3'] == 'calltype') {
                    $new_arr[] = $row1['calltype'];
                } elseif ($search_arr['groupby_3'] == 'product_id') {
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } elseif ($search_arr['groupby_3'] == "product_category") {
                    $new_arr[] = $this->common->get_field_name('name', 'category', $row1['product_category']);
                }
 
                if (empty($new_arr)) {
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
                }
            } else {
                $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1[$db_field_name]);
            }
            if ($entity != 'provider') {
                $custom_array = array(
                    $atmpt,
                    $cmplt,
                    $duration,
                    astpp_round($asr, 2),
                    $avgsec,
                    $maxsec,
                    $billsec,
                    $debit,
                    $cost,
                    $profit
                );
            } else {
                $custom_array = array(
                    $atmpt,
                    $cmplt,
                    $duration,
                    astpp_round($asr, 2),
                    $avgsec,
                    $maxsec,
                    $billsec,
                    $cost
                );
            }
            $final_array = astpp_array_merge($new_arr, $custom_array);
            $json_data[] = array(
                'cell' => $final_array
            );
            $export_arr[] = $final_array;
        }
        $function_name = 'get_' . $entity . 'summary_report_list';
        $total_info = $this->summary_model->$function_name(true, '', '', '', $search_arr['select_str'], $search_arr['order_str'], true);
        $total_info = $total_info->result_array();
        $total_info = $total_info[0];
        $total_asr = (astpp_gt($total_info['attempts'], 0)) ? astpp_round((astpp_div($total_info['completed'], $total_info['attempts'])) * 100, 2) : 0;
        $total_acd = (astpp_gt($total_info['completed'], 0)) ? astpp_round(astpp_div($total_info['duration'], $total_info['completed'])) : 0;
        if ($show_seconds == 'minutes') {
            $total_info['duration'] = astpp_gt($total_info['duration'], 0) ? sprintf('%02d', astpp_n($total_info['duration']) / 60) . ":" . sprintf('%02d', (astpp_n($total_info['duration']) % 60)) : "00:00";
            $total_info['billable'] = astpp_gt($total_info['billable'], 0) ? sprintf('%02d', astpp_n($total_info['billable']) / 60) . ":" . sprintf('%02d', (astpp_n($total_info['billable']) % 60)) : "00:00";
            $total_acd = astpp_gt($total_acd, 0) ? sprintf('%02d', astpp_n($total_acd) / 60) . ":" . sprintf('%02d', (astpp_n($total_acd) % 60)) : "00:00";
            $total_info['mcd'] = astpp_gt($total_info['mcd'], 0) ? sprintf('%02d', astpp_n($total_info['mcd']) / 60) . ":" . sprintf('%02d', (astpp_n($total_info['mcd']) % 60)) : "00:00";
        }
        if ($entity != 'provider') {
            $total_profit = $this->common->calculate_currency_manually($currency_info, astpp_n($total_info['debit']) - astpp_n($total_info['cost']), false);
            $total_debit = $this->common->calculate_currency_manually($currency_info, $total_info['debit'], false);
        }
        $total_cost = $this->common->calculate_currency_manually($currency_info, $total_info['cost'], false);
        if ($entity != 'provider') {
            $last_array = array(
                "<b>" . $total_info['attempts'] . "</b>",
                "<b>" . $total_info['completed'] . "</b>",
                "<b>" . $total_info['duration'] . "</b>",
                "<b>" . $total_asr . "</b>",
                "<b>" . $total_acd . "</b>",
                "<b>" . $total_info['mcd'] . "</b>",
                "<b>" . $total_info['billable'] . "</b>",
                "<b>" . $total_debit . "</b>",
                "<b>" . $total_cost . "</b>",
                "<b>" . $total_profit . "</b>"
            );
        } else {
            $last_array = array(
                "<b>" . $total_info['attempts'] . "</b>",
                "<b>" . $total_info['completed'] . "</b>",
                "<b>" . $total_info['duration'] . "</b>",
                "<b>" . $total_asr . "</b>",
                "<b>" . $total_acd . "</b>",
                "<b>" . $total_info['mcd'] . "</b>",
                "<b>" . $total_info['billable'] . "</b>",
                "<b>" . $total_cost . "</b>"
            );
        }
        if ($purpose == 'export') {
            $search_arr['custom_total_array'][0] = "Grand Total";
        }
        $new_export_array = array();
        foreach ($last_array as $key => $value) {
            $value = str_replace("<b>", "", $value);
            $value = str_replace("</b>", '', $value);
            if ($key == 7 || $key == 8 || $key == 9) {
                $value = sprintf("%." . $currency_info['decimalpoints'] . "f", floatval($value));
            }
            $new_export_array[$key] = $value;
        }
        $total_array = astpp_array_merge($search_arr['custom_total_array'], $last_array);
        $custom_export_arr = astpp_array_merge($search_arr['custom_total_array'], $new_export_array);
        $export_arr[] = $custom_export_arr;
        $json_data[] = array(
            'cell' => $total_array
        );
        return $purpose == 'grid' ? $json_data : $export_arr;
    }
 
    function customer_export_csv()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $search_arr = $this->session->userdata('customersummary_reports_export');
        $data_arr = array();
        $query = $this->summary_model->get_customersummary_report_list(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $search_header = explode(",", $search_arr['export_str']);
 
        ob_clean();
        $fixed_header = array(
            gettext('Attempted Calls'),
            gettext('Completed Calls'),
            gettext('Duration'),
            gettext('ASR'),
            gettext('ACD'),
            gettext('MCD'),
            gettext('Billable'),
            gettext('Debit').'(' . $currency . ')',
            gettext('Cost').'(' . $currency . ')',
            gettext('Profit')
        );
        $header_arr[] = astpp_array_merge($search_header, $fixed_header);
        if ($query->num_rows() > 0) {
            $data_arr = $this->summary_report_grid($search_arr, $query, 'customer', 'export');
        }
        $customer_array = astpp_array_merge($header_arr, $data_arr);
 
        $this->load->helper('csv');
        array_to_csv($customer_array, 'Customer_Summary_Report_' . date("Y-m-d") . '.csv');
    }
 
    function customer_search()
    {
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('customer_cdrs_year', $this->input->post('cdrs_year'));
            unset($action['cdrs_year']);
            unset($_POST['action']);
            unset($_POST['advance_search']);
            $data = $this->input->post();
            if(!empty($data['callstart'][0])){
                $data['callstart'][0]=$this->common->convert_GMT_new ( $data['callstart'][0]);
            }
            if(!empty($data['callstart'][1])){
                $data['callstart'][1]=$this->common->convert_GMT_new ( $data['callstart'][1]);
            }
            $this->session->set_userdata('customersummary_reports_search', $this->input->post());
        }
        redirect(base_url() . 'summary/customer/');
    }
 
    function customer_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('customersummary_reports_search', "");
        $this->session->set_userdata('customersummary_reports_export', "");
        $this->session->unset_userdata('customer_cdrs_year');
        redirect(base_url() . 'summary/customer/');
    }
 
    function summary_search_info($entity)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $this->db->select('gmttime,gmtoffset');
        $timezone_info = (array) $this->db->get_where('timezone', array(
            "id" => $accountinfo['timezone_id']
        ))->first_row();
        if (! empty($timezone_info['gmttime']) && (!astpp_eq0($timezone_info['gmtoffset']))) {
            $user_timezone = $timezone_info['gmttime'];
        } else {
            $user_timezone = "GMT+00:00";
        }
        $user_timezone_arr = explode("GMT", $user_timezone);
        $user_timezone_gmttime = $user_timezone_arr[1];
        $group_by_str = null;
        $select_str = null;
        $group_by_time = null;
        $group_by_1 = null;
        $group_by_2 = null;
        $group_by_3 = null;
        $order_str = null;
        $custom_total_array = array();
        $custom_search = array();
        $export_select_str = null;
        $new_arr['search_in'] = 'minutes';
        $i = 0;
 
        $db_field_name =( $entity == 'provider') ? 'provider_id' :(($entity == 'product')?"product_id": 'accountid');
 
        if ($this->session->userdata('advance_search') == 1) {
            $custom_search = $this->session->userdata($entity . 'summary_reports_search');
            if (isset($custom_search['time']) && ! empty($custom_search['time'])) {
                if ($entity != "product") {
                    $group_by_str .= $custom_search['time'] . "(convert_tz(callstart,'+00:00','$user_timezone_gmttime')),";
                    $select_str .= $custom_search['time'] . "(convert_tz(callstart,'+00:00','$user_timezone_gmttime')) as " . $order_str .= $custom_search['time'] . ",";
                } else {
                    $group_by_str .= $custom_search['time'] . "(convert_tz(orders.order_date,'+00:00','$user_timezone_gmttime')),";
                    $select_str .= $custom_search['time'] . "(convert_tz(orders.order_date,'+00:00','$user_timezone_gmttime')) as " . $order_str .= $custom_search['time'] . ",";
                }
                $group_by_time = $custom_search['time'];
                $export_select_str .= $custom_search['time'] . ",";
                $custom_total_array[$i] = null;
                $i ++;
            }
 
            if (isset($custom_search['groupby_1']) && ! empty($custom_search['groupby_1'])) {
                $custom_group_by = $entity == 'product' ? "order_items." . $custom_search['groupby_1'] : $custom_search['groupby_1'];
                $select_str .= $custom_group_by . ",";
                $group_by_str .= $custom_group_by . ",";
                $order_str .= $custom_group_by . ",";
                $group_by_1 = $custom_group_by;
                if ($custom_search['groupby_1'] == $db_field_name) {
                    $export_select_str .= 'Account,';
                } elseif ($custom_search['groupby_1'] == 'trunk_id') {
                    $export_select_str .= 'Trunk,';
                } elseif ($custom_search['groupby_1'] == 'pattern') {
                    $select_str .= 'notes,';
                    $order_str .= 'notes,';
                    $export_select_str .= "Code,Destination,";
                    $custom_total_array[$i] = null;
                    $i ++;
                } elseif ($custom_search['groupby_1'] == 'package_id') {
                    $export_select_str .= 'Package,';
                } elseif ($custom_search['groupby_1'] == 'sip_user') {
                    $export_select_str .= 'SIP User,';
                } elseif ($custom_search['groupby_1'] == 'product_id') {
                    $export_select_str .= 'Product,';
                } elseif ($custom_search['groupby_1'] == 'call_direction') {
                    $export_select_str .= 'Direction,';
                } elseif ($custom_search['groupby_1'] == 'product_category') {
                    $export_select_str .= 'Category,';
                }
                $custom_total_array[$i] = null;
                $i ++;
            }
 
            if (isset($custom_search['groupby_2']) && ! empty($custom_search['groupby_2'])) {
                $custom_group_by = $entity == 'product' ? "order_items." . $custom_search['groupby_2'] : $custom_search['groupby_2'];
                $group_by_str .= $custom_group_by . ",";
                $select_str .= $custom_group_by . ",";
                $order_str .= $custom_group_by . ",";
                $group_by_2 = $custom_group_by;
                if ($custom_search['groupby_2'] == $db_field_name) {
                    $export_select_str .= 'Account,';
                } elseif ($custom_search['groupby_2'] == 'trunk_id') {
                    $export_select_str .= 'Trunk,';
                } elseif ($custom_search['groupby_2'] == 'pattern') {
                    $select_str .= 'notes,';
                    $order_str .= 'notes,';
                    $export_select_str .= "Code,Destination,";
                    $custom_total_array[$i] = null;
                    $i ++;
                } elseif ($custom_search['groupby_2'] == 'sip_user') {
                    $export_select_str .= 'SIP User,';
                } elseif ($custom_search['groupby_2'] == 'call_direction') {
                    $export_select_str .= 'Direction,';
                } elseif ($custom_search['groupby_2'] == 'package_id') {
                    $export_select_str .= 'Package,';
                } elseif ($custom_search['groupby_2'] == 'product_id') {
                    $export_select_str .= 'Product,';
                } elseif ($custom_search['groupby_2'] == 'product_category') {
                    $export_select_str .= 'Category,';
                }
                $custom_total_array[$i] = null;
                $i ++;
            }
 
            if (isset($custom_search['groupby_3']) && ! empty($custom_search['groupby_3'])) {
                $custom_group_by = $entity == 'product' ? "order_items." . $custom_search['groupby_3'] : $custom_search['groupby_3'];
                $group_by_str .= $custom_group_by . ",";
                $select_str .= $custom_group_by . ",";
                $order_str .= $custom_group_by . ",";
                $group_by_3 = $custom_group_by;
                if ($custom_search['groupby_3'] == 'accountid' || $custom_search['groupby_3'] == 'provider_id') {
                    $export_select_str .= 'Account,';
                } elseif ($custom_search['groupby_3'] == 'trunk_id') {
                    $export_select_str .= 'Trunk,';
                } elseif ($custom_search['groupby_3'] == 'pattern') {
                    $select_str .= 'notes,';
                    $order_str .= 'notes,';
                    $export_select_str .= "Code,Destination,";
                    $custom_total_array[$i] = null;
                    $i ++;
                } elseif ($custom_search['groupby_3'] == 'sip_user') {
                    $export_select_str .= 'SIP User,';
                } elseif ($custom_search['groupby_3'] == 'call_direction') {
                    $export_select_str .= 'Direction,';
                } elseif ($custom_search['groupby_3'] == 'package_id') {
                    $export_select_str .= 'Package,';
                } elseif ($custom_search['groupby_2'] == 'product_id') {
                    $export_select_str .= 'Product,';
                } elseif ($custom_search['groupby_2'] == 'product_category') {
                    $export_select_str .= 'Category,';
                }
                $custom_total_array[$i] = null;
                $i ++;
            }
            $new_arr['search_in'] = (isset($custom_search['search_in']) && ! empty($custom_search['search_in'])) ? $custom_search['search_in'] : 'minutes';
            unset($custom_search['groupby_1'], $custom_search['groupby_2'], $custom_search['groupby_3'], $custom_search['search_in']);
            $this->session->set_userdata('summary_' . $entity . '_search', $custom_search);
        }
 
        if (! empty($group_by_str)) {
            $group_by_str = rtrim($group_by_str, ",");
            $select_str = rtrim($select_str, ",");
            $order_str = rtrim($order_str, ",");
            $export_select_str = rtrim($export_select_str, ",");
        } else {
            if ($entity != "product") {
                $select_str = $db_field_name;
                $order_str = $db_field_name;
                $group_by_str = $db_field_name;
            } else {
                $select_str = "order_items." . $db_field_name;
                $order_str = "order_items." . $db_field_name;
                $group_by_str = "order_items." . $db_field_name;
        $export_select_str = "order_items." . $db_field_name;
            }
            if($entity == "product"){
        $export_select_str = "Product";
        }else{
             $export_select_str = "Account";
        }
 
        }
 
        array_pop($custom_total_array);
        array_unshift($custom_total_array, '<b>Grand Total</b>');
        $new_arr['export_str'] = $export_select_str;
        $new_arr['select_str'] = $select_str;
        $new_arr['order_str'] = $order_str;
        $new_arr['group_by_str'] = $group_by_str;
        $new_arr['groupby_1'] = $group_by_1;
        $new_arr['groupby_2'] = $group_by_2;
        $new_arr['groupby_3'] = $group_by_3;
        $new_arr['groupby_time'] = $group_by_time;
        $new_arr['custom_total_array'] = $custom_total_array;
        return $new_arr;
    }
 
    function provider()
    {
        $data['page_title'] = gettext('Provider Summary Report');
        $data['search_flag'] = true;
        $session_info = $this->session->userdata('providersummary_reports_search');
        $accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( company_name, " ", "(", number, ")^" )),IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ),concat( company_name, " ", "(", number, ")" ))) as number', 'accounts', 'where_arr', array(
            "type" => "3"
        ));
        $trunklist = $this->db_model->build_dropdown('id,name', 'trunks', '', array());
        $data['cdrs_year'] = $this->common->set_year_dropdown('cdrs');
        $data['trunklist'] = $trunklist;
        $data['accountlist'] = $accountlist;
        $data['session_info'] = $session_info;
        $data['cdrs_year_val'] = $this->session->userdata('provider_cdrs_year');
        $data['seconds'] = $this->session->userdata('provider_seconds');
        $data['search_report'] = $this->common->search_report_in();
        $data['grid_fields'] = $this->summary_form->build_providersummary();
        $data["grid_buttons"] = $this->summary_form->build_grid_buttons_providersummary();
        $data['search_string_type'] = $this->common->search_string_type();
        $data['groupby_field'] = $this->common->set_summaryprovider_groupby();
        $data['groupby_time'] = $this->common->group_by_time();
        $this->load->view('view_providersummary_report', $data);
    }
 
    function provider_json()
    {
        $search_arr = $this->summary_search_info('provider');
        $count_all = $this->summary_model->get_providersummary_report_list(false, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->summary_model->get_providersummary_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        if ($query->num_rows() > 0) {
            $json_data['rows'] = $this->summary_report_grid($search_arr, $query, 'provider', 'grid');
        }
        $this->session->set_userdata('providersummary_reports_export', $search_arr);
        echo json_encode($json_data);
    }
 
    function provider_export_csv()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $search_arr = $this->session->userdata('providersummary_reports_export');
        $data_arr = array();
        $query = $this->summary_model->get_providersummary_report_list(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $search_header = explode(",", $search_arr['export_str']);
        ob_clean();
        $fixed_header = array(
            gettext("Attempted Calls"),
            gettext("Completed Calls"),
            gettext("Duration"),
            gettext("ASR"),
            gettext("ACD"),
            gettext("MCD"),
            gettext("Billable"),
            gettext("Cost")."($currency)"
        );
        $header_arr[] = astpp_array_merge($search_header, $fixed_header);
        if ($query->num_rows() > 0) {
            $data_arr = $this->summary_report_grid($search_arr, $query, 'provider', 'export');
        }
        $provider_array = astpp_array_merge($header_arr, $data_arr);
 
        $this->load->helper('csv');
        array_to_csv($provider_array, 'Provider_Summary_Report_' . date("Y-m-d") . '.csv');
    }
 
    function provider_search()
    {
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('provider_cdrs_year', $this->input->post('cdrs_year'));
            unset($_POST['action'], $_POST['advance_search'], $_POST['cdrs_year']);
            unset($_POST['action'], $_POST['advance_search']);
            $data = $this->input->post();
            if(!empty($data['callstart'][0])){
                $data['callstart'][0]=$this->common->convert_GMT_new ( $data['callstart'][0]);
            }
            if(!empty($data['callstart'][1])){
                $data['callstart'][1]=$this->common->convert_GMT_new ( $data['callstart'][1]);
            }

            $this->session->set_userdata('providersummary_reports_search', $this->input->post());
        }
        redirect(base_url() . 'summary/provider/');
    }
 
    function provider_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('providersummary_reports_search', "");
        $this->session->set_userdata('providersummary_reports_export', "");
        $this->session->unset_userdata('provider_cdrs_year', "");
        redirect(base_url() . "summary/provider/");
    }
 
    function reseller()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Reseller Summary Report');
        $data['search_flag'] = true;
        $session_info = $this->session->userdata('resellersummary_reports_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( company_name, " ", "(", number, ")^" )),IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ),concat( company_name, " ", "(", number, ")" ))) as number', 'accounts', 'where_arr', array(
            'reseller_id' => $reseller_id,
            "type" => "1"
        ));
        $data['cdrs_year'] = $this->common->set_year_dropdown('reseller_cdrs');
        $data['accountlist'] = $accountlist;
        $data['seconds'] = $this->session->userdata('reseller_seconds');
        $data['session_info'] = $session_info;
        $data['cdrs_year_val'] = $this->session->userdata('reseller_cdrs_year');
        $data['search_report'] = $this->common->search_report_in();
        $data['search_string_type'] = $this->common->search_string_type();
        $new_column_arr = $this->summary_column_arr('reseller');
        $data['grid_fields'] = $this->summary_form->build_resellersummary($new_column_arr);
        $data["grid_buttons"] = $this->summary_form->build_grid_buttons_resellersummary();
        $data['groupby_field'] = $this->common->set_summarycustomer_groupby();
        unset($data['groupby_field']['sip_user']);
        $data['groupby_time'] = $this->common->group_by_time();
        $this->load->view('view_resellersummary_report', $data);
    }
 
    function reseller_json()
    {
        $search_arr = $this->summary_search_info('reseller');
        $count_all = $this->summary_model->get_resellersummary_report_list(false, 0, 0, $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->summary_model->get_resellersummary_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        if ($query->num_rows() > 0) {
            $json_data['rows'] = $this->summary_report_grid($search_arr, $query, 'reseller', 'grid');
        }
        $this->session->set_userdata('resellersummary_reports_export', $search_arr);
        echo json_encode($json_data);
    }
 
    function reseller_search()
    {
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('reseller_cdrs_year', $this->input->post('cdrs_year'));
            unset($_POST['action'], $_POST['advance_search'], $_POST['cdrs_year']);
            unset($_POST['action'], $_POST['advance_search']);
            $data = $this->input->post();
            if(!empty($data['callstart'][0])){
                $data['callstart'][0]=$this->common->convert_GMT_new ( $data['callstart'][0]);
            }
            if(!empty($data['callstart'][1])){
                $data['callstart'][1]=$this->common->convert_GMT_new ( $data['callstart'][1]);
            }
            $this->session->set_userdata('resellersummary_reports_search', $this->input->post());
        }
        redirect(base_url() . "summary/reseller/");
    }
 
    function reseller_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('resellersummary_reports_search', "");
        $this->session->set_userdata('resellersummary_reports_export', "");
        $this->session->unset_userdata('reseller_cdrs_year', "");
        redirect(base_url() . "summary/reseller/");
    }
 
    function reseller_export_csv()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
 
        $search_arr = $this->session->userdata('resellersummary_reports_export');
        $data_arr = array();
        $query = $this->summary_model->get_resellersummary_report_list(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $search_header = explode(",", $search_arr['export_str']);
        ob_clean();
        $fixed_header = array(
            gettext("Attempted Calls"),
            gettext("Completed Calls"),
            gettext("Duration"),
            gettext("ASR"),
            gettext("ACD"),
            gettext("MCD"),
            gettext("Billable"),
            gettext("Debit")."($currency)",
            gettext("Cost")."($currency)",
            gettext("Profit")
        );
        $header_arr[] = astpp_array_merge($search_header, $fixed_header);
        if ($query->num_rows() > 0) {
            $data_arr = $this->summary_report_grid($search_arr, $query, 'reseller', 'export');
        }
        $reseller_array = astpp_array_merge($header_arr, $data_arr);
        $this->load->helper('csv');
        array_to_csv($reseller_array, 'Reseller_Summary_Report_' . date("Y-m-d") . '.csv');
    }
    function product()
    {
        $data['page_title'] = gettext('Product Summary Report');
        $data['search_flag'] = true;
        $session_info = $this->session->userdata('productsummary_reports_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        if($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5){
		$accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( company_name, " ", "(", number, ")^" )),IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ),concat( company_name, " ", "(", number, ")" ))) as number', 'accounts', 'where_arr', array(
		    'reseller_id' => $reseller_id,
		    "type" => "GLOBAL"
		));
	}else{
		$accountlist = $this->db_model->build_dropdown_deleted('id,IF(`deleted`=1,IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")^" ),concat( company_name, " ", "(", number, ")^" )),IF( company_name = "",concat( first_name, " ", last_name, " ", "(", number, ")" ),concat( company_name, " ", "(", number, ")" ))) as number', 'accounts', 'where_arr', array(
		    "type" => "GLOBAL"
		));
	}
        $product_list = $this->db_model->build_dropdown('id,name', 'products','where_arr',array("status" =>0));
        $data['productlist'] = $product_list;
	$category_list = $this->db_model->build_dropdown("id,name", "category",'where_arr', array(
            "code <> " => 'REFILL',
            "code <>" => 'DID',
		
        ));

        $data['categorylist'] = $category_list;
        $data['order_items_year'] = $this->common->set_year_dropdown('order_items');
        $data['accountlist'] = $accountlist;
        $data['session_info'] = $session_info;
        $data['products_year_val'] = $this->session->userdata('product_year');
        $data['search_string_type'] = $this->common->search_string_type();
        $data['search_report'] = $this->common->search_report_in();
        $new_column_arr = $this->summary_column_arr('product');
        $data['grid_fields'] = $this->summary_form->build_product_summary($new_column_arr);
        $data["grid_buttons"] = $this->summary_form->build_grid_buttons_products_summary();
        $data['groupby_field'] = $this->common->set_product_groupby();
        $data['groupby_time'] = $this->common->group_by_time_for_product();
        $this->load->view('view_productsummary_report', $data);
    }

    function product_json()
    {
        $search_arr = $this->summary_search_info('product');
        $count_all = $this->summary_model->get_productsummary_report_list(false, 0, 0, $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->summary_model->get_productsummary_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], false);
        if ($query->num_rows() > 0) {
            $json_data['rows'] = $this->product_summary_report_grid($search_arr, $query, 'product', 'grid');
        }
        $this->session->set_userdata('productsummary_reports_export', $search_arr);
        echo json_encode($json_data);
    }
    function product_search()
    {
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($_POST['action']);
            unset($_POST['advance_search']);
             if(!empty($action['order_date'][0])){
                $action['order_date'][0]=$this->common->convert_GMT_new ( $action['order_date'][0]);
            }
            if(!empty($action['order_date'][1])){
                $action['order_date'][1]=$this->common->convert_GMT_new ( $action['order_date'][1]);
            }
            $_POST['order_items.accountid'] = $_POST['order_items#accountid'];
            unset($_POST['order_items#accountid']);
            $this->session->set_userdata('productsummary_reports_search', $this->input->post());
        }
        redirect(base_url() . 'summary/product/');
    }

    function product_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->unset_userdata('productsummary_reports_search', "");
        $this->session->set_userdata('productsummary_reports_export', "");
        $this->session->unset_userdata('product_cdrs_year');
	$this->session->unset_userdata('summary_product_search');
        redirect(base_url() . 'summary/product/');
    }

    function product_summary_report_grid($search_arr, $query, $entity, $purpose){
        $export_arr = array();
        $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
        $currency_info = $this->common->get_currency_info();
    $usedsec = 0;
    $custom_array = array();
    $session_info = $this->session->userdata('productsummary_reports_search');
    $reseller_id = $this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5 ? $this->session->userdata['accountinfo']['id'] : 0;
        foreach ($query->result_array() as $row1) {

            $new_arr = array();
            $free_minutes = $row1['free_minutes'];
        $used_seconds = 0;      
         if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
            $this->db->where('accountid',$session_info['order_items.accountid']); 
        }
        if((isset($search_arr['groupby_1']) || ($search_arr['groupby_2']) || ($search_arr['groupby_2'])) && ($search_arr['groupby_1'] == 'order_items.accountid' || $search_arr['groupby_2'] == 'order_items.accountid' ) ){
    
            /*$used_seconds = "select sum(used_seconds) as used_seconds from counters as C inner join packages_view as P on C.product_id=P.id where P.product_id=".$row1['product_id']." and C.accountid = ".$row1['accountid']." ";
            $used_seconds = $this->db->query($used_seconds);*/
        $this->db->select_sum('used_seconds');
            $this->db->from('counters');
            $this->db->where("product_id",$row1['product_id']);
             $this->db->where('accountid',$row1['accountid']); 
            $used_seconds=$this->db->get();

        }else{
            /* $used_seconds = "select sum(used_seconds) as used_seconds from counters as C inner join packages_view as P on C.product_id=P.id where P.product_id=".$row1['product_id']."";
            $used_seconds = $this->db->query($used_seconds);*/
         if(astpp_gt($reseller_id, 0)){
               $this->db->select_sum('used_seconds');
               $this->db->from('counters');
            //    $this->db->where_not_in("accountid",$row1['accountid']);
               $this->db->where("accountid",$row1['accountid']);               
               $this->db->where("product_id",$row1['product_id']);
               $used_seconds=$this->db->get();
         }else{
               $this->db->select_sum('used_seconds');
               $this->db->from('counters');
               $this->db->where("product_id",$row1['product_id']);
               // $this->db->where("package_id",$row1['id']);
               $this->db->where("type",0);
               $used_seconds=$this->db->get();
        }
        }

        if($used_seconds->num_rows > 0){
            $used_seconds = $used_seconds->result_array()[0]['used_seconds'];

        }

           if ($show_seconds == 'minutes') {
        $free_minutes = astpp_n($free_minutes)*60;
                $free_minutes_result = astpp_gt($free_minutes, 0) ? sprintf('%02d', astpp_n($free_minutes) / 60) . ":" . sprintf('%02d', (astpp_n($free_minutes) % 60)) : "00:00";
        $used_seconds_result = astpp_gt($used_seconds, 0) ? sprintf('%02d', astpp_n($used_seconds) / 60) . ":" . sprintf('%02d', (astpp_n($used_seconds) % 60)) : "00:00";
        
            } else {
        $free_minutes = astpp_n($free_minutes)*60;
                $free_minutes_result = sprintf('%02d', $free_minutes);
        $usedseconds = astpp_n($used_seconds) * 60;
        $usedseconds = sprintf('%02d', $usedseconds);
        $used_seconds_result = astpp_gt($usedseconds, 0) ? sprintf('%02d', astpp_n($usedseconds) / 60) : "00";
            }

       $usedsec = astpp_n($usedsec) + astpp_n($used_seconds);

            if ($this->session->userdata('advance_search') == 1 || isset($search_arr) ) {
                if (! empty($search_arr['groupby_time'])) {
                    $time = $row1[$search_arr['groupby_time']];
                    if ($search_arr['groupby_time'] == "HOUR" || $search_arr['groupby_time'] == "DAY") {
                        $time = sprintf('%02d', $time);
                    }
                    if ($search_arr['groupby_time'] == "MONTH") {
                        $dateObj = DateTime::createFromFormat('!m', $time);
                        $time = $dateObj->format('F');
                    }
                    $new_arr[] = $time;
                }

            if($search_arr['groupby_1'] == "order_items.product_id"){
                $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
            }

            // if ($search_arr['groupby_1'] == "order_items.accountid") {
            //         $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1["accountid"]);
            //     } 
             
            //     if ($search_arr['groupby_2'] == "order_items.accountid") {
            //         $new_arr[] = $this->common->build_concat_string("first_name,last_name,number", "accounts", $row1["accountid"]);
            //     } 
            
            if ($search_arr['groupby_1'] == "order_items.accountid" || $search_arr['groupby_2'] == "order_items.accountid") {
                if($reseller_id == '0'){
                    $account_id = $this->db_model->getSelect('*','accounts',array("reseller_id" => $row1["accountid"]))->result_array();
                    $account_id = $account_id[0]['id'];
                }else{
                    $account_id = $this->db_model->getSelect('*','accounts',array("id" => $row1["accountid"]))->result_array();
                    $account_id = $account_id[0]['id'];
                }
                    $new_arr[] = $this->common->build_concat_string("first_name,last_name,number,company_name", "accounts", $account_id);
                }

            if($search_arr['groupby_2'] == "order_items.product_id"){
                    $new_arr[] = $this->common->get_field_name('name', 'products', $row1['product_id']);
                } 
        
             if (isset($search_arr['groupby_1']) && isset($search_arr['groupby_2']) &&  $search_arr['groupby_1'] == $search_arr['groupby_2']) {
                unset($new_arr[1]);
                }   
        
       if($row1['product_category'] == 1 ){
         if((isset($search_arr['groupby_1']) && ($search_arr['groupby_1'] == "order_items.product_id" || $search_arr['groupby_2'] == "order_items.product_id" ) &&  ((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )))){ 
            $custom_array = array(
          
                $row1['quantity'],
                $row1['price'],
            $row1['setup_fee'],
                $free_minutes_result,
            $used_seconds_result,
            $this->common->get_available_seconds_for_package($row1['productid'],$free_minutes,$used_seconds,$show_seconds),
            $this->common->convert_to_currency_account("","",(astpp_n($row1['price'])+astpp_n($row1['setup_fee']))),

            $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"accountid"=>$row1['accountid'],"is_terminated"=>0)),
            $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"accountid"=>$row1['accountid'])),
            );
        }else if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.product_id") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.product_id" )){
         if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
            $this->db->where('accountid',$session_info['order_items.accountid']); 
        }
        if(astpp_gt($reseller_id, 0)){
            
            $active_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"is_terminated"=>0,"reseller_id"=>$reseller_id),"accountid");
             if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"reseller_id"=>$reseller_id),"accountid");
         }else{
            $active_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"is_terminated"=>0, "reseller_id" => 0),"accountid");
             if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
             }
            $total_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'], "reseller_id" => 0),"accountid");
         }
        $custom_array = array(
            //$product_name = $this->common->get_field_name("name","products",array("id"=>$row1['product_id'])),
            $row1['quantity'],
                $row1['price'],
            $row1['setup_fee'],
                $free_minutes_result,
            $used_seconds_result,
            $this->common->get_available_seconds_for_package($row1['productid'],$free_minutes,$used_seconds,$show_seconds),
            $this->common->convert_to_currency_account("","",(astpp_n($row1['price'])+astpp_n($row1['setup_fee']))),
            $active_user,
            $total_user,
        );
        }else if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )){//echo 4334; exit;
            $custom_array = array(
              
                $free_minutes_result,
            $used_seconds_result,
            $this->common->get_available_seconds_for_package($row1['productid'],$free_minutes,$used_seconds,$show_seconds),
            
            $this->db_model->countQuery("*","order_items",array("accountid"=>$row1['accountid'])),
            
            );
        }else{
         if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
            $this->db->where('accountid',$session_info['order_items.accountid']); 
        } 
         if(astpp_gt($reseller_id, 0)){
            $active_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"is_terminated"=>0,"reseller_id"=>$reseller_id),"accountid");
             if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"reseller_id"=>$reseller_id),"accountid");
         }else{
            $active_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"is_terminated"=>0, "reseller_id" => 0),"accountid");
             if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'], "reseller_id" => 0),"accountid");
         }
         $custom_array = array(         
            $product_name = $this->common->get_field_name("name","products",array("id"=>$row1['product_id'])),
                $row1['quantity'],
                $row1['price'],
            $row1['setup_fee'],
                $free_minutes_result,
            $used_seconds_result,
            $this->common->get_available_seconds_for_package($row1['productid'],$free_minutes,$used_seconds,$show_seconds),
            $this->common->convert_to_currency_account("","",(astpp_n($row1['price'])+astpp_n($row1['setup_fee']))),
            $active_user,
            $total_user
              
            );
        }
       }

       if($row1['product_category'] == 2){
            if((isset($search_arr['groupby_1']) && ($search_arr['groupby_1'] == "order_items.product_id" || $search_arr['groupby_2'] == "order_items.product_id" ) &&  ((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )))){ 
            $custom_array = array(
                $row1['quantity'],
                $row1['price'],
            $row1['setup_fee'],
            $this->common->convert_to_currency_account("","",(astpp_n($row1['price'])+astpp_n($row1['setup_fee']))),
            $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"accountid"=>$row1['accountid'],"is_terminated"=>0)),
            $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"accountid"=>$row1['accountid'])),
            );
        }else if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.product_id") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.product_id" )){
         if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
            $this->db->where('accountid',$session_info['order_items.accountid']); 
        }
        if(astpp_gt($reseller_id, 0)){
            $active_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"is_terminated"=>0,"reseller_id"=>$reseller_id),"accountid");
             if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"reseller_id"=>$reseller_id),"accountid");
         }else{
            $active_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"is_terminated"=>0, "reseller_id" => 0),"accountid");
            if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'], "reseller_id" => 0),"accountid");
         }
        $custom_array = array(
            $row1['quantity'],
                $row1['price'],
            $row1['setup_fee'],
            $this->common->convert_to_currency_account("","",(astpp_n($row1['price'])+astpp_n($row1['setup_fee']))),
            $active_user,
            $total_user,
        );
        }else if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )){//echo 4334; exit;
            $custom_array = array(
              
            $this->db_model->countQuery("*","order_items",array("accountid"=>$row1['accountid'])),
            
            );
        }else{
        if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
        if(astpp_gt($reseller_id, 0)){
            $active_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"is_terminated"=>0,"reseller_id"=>$reseller_id),"accountid");
            if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"reseller_id"=>$reseller_id),"accountid");
         }else{
            $active_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'],"is_terminated"=>0, "reseller_id" => 0),"accountid");
            if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = $this->db_model->countQuery("*","order_items",array("product_id"=>$row1['productid'], "reseller_id" => 0),"accountid");
         }
         $custom_array = array(         
            $product_name = $this->common->get_field_name("name","products",array("id"=>$row1['product_id'])),
                $row1['quantity'],
                $row1['price'],
            $row1['setup_fee'],
            $this->common->convert_to_currency_account("","",(astpp_n($row1['price'])+astpp_n($row1['setup_fee']))),
            $active_user,
            $total_user,
            );
        }
       }
    }
            $final_array = astpp_array_merge($new_arr, $custom_array);

            $json_data[] = array(
                'cell' => $final_array
            );

            $export_arr[] = $final_array;
        }

        $free_minutes = '';
        $function_name = 'get_' . $entity . 'summary_report_list';

        $total_info = $this->summary_model->$function_name(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $total_info = $total_info->result_array();

        $quantity = 0;
        $price = 0;
    $setup_fee = 0;
        $minutes = 0;
        //$billing_type = 0;
        $totalamt = 0;
    $active_user = 0;
    $total_user = 0;
    $total_product_count = 0;
    $avaiable_minutes = 0;
    $usedsec =0;
    
        foreach ($total_info as $key => $val) {
    if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
    if((isset($search_arr['groupby_1']) || ($search_arr['groupby_2']) || ($search_arr['groupby_2'])) && ($search_arr['groupby_1'] == 'order_items.accountid' || $search_arr['groupby_2'] == 'order_items.accountid' ) ){
/*          $used_seconds = "select sum(used_seconds) as used_seconds from counters as C inner join packages_view as P on C.product_id=P.id where P.product_id=".$val['productid']." and C.accountid = ".$val['accountid']." ";
            $used_seconds = $this->db->query($used_seconds);*/
        $this->db->select_sum('used_seconds');
            $this->db->from('counters');
            $this->db->where("product_id",$val['product_id']);
             $this->db->where('accountid',$val['accountid']); 
            $used_seconds=$this->db->get();
        

        }else{
            /*$used_seconds = "select sum(used_seconds) as used_seconds from counters as C inner join packages_view as P on C.product_id=P.id where P.product_id=".$val['productid']."";
            $used_seconds = $this->db->query($used_seconds);*/
         if(astpp_gt($reseller_id, 0)){
               $this->db->select_sum('used_seconds');
               $this->db->from('counters');
            //    $this->db->where_not_in("accountid",$reseller_id);
               $this->db->where("accountid",$reseller_id);            
               $this->db->where("product_id",$row1['product_id']);
               $used_seconds=$this->db->get();
         }else{
               $this->db->select_sum('used_seconds');
               $this->db->from('counters');
               $this->db->where("product_id",$row1['product_id']);
               // $this->db->where("package_id",$val['id']);
               $this->db->where("type",0);
               $used_seconds=$this->db->get();
        }
            /*$this->db->select_sum('used_seconds');
            $this->db->from('counters');
            $this->db->where("product_id",$val['product_id']);
            $used_seconds=$this->db->get();*/
        }
        if($used_seconds->num_rows > 0){
            $usedsec = astpp_n($usedsec) + astpp_n($used_seconds->result_array()[0]['used_seconds']);

        }
              if((isset($search_arr['groupby_1']) || ($search_arr['groupby_2']) || ($search_arr['groupby_2'])) && ($search_arr['groupby_1'] == 'order_items.accountid' || $search_arr['groupby_2'] == 'order_items.accountid' ) ){
            $quantity = astpp_n($quantity) + astpp_n($val['quantity']);
            $price = astpp_n($price) + astpp_n($val['price']);
            $setup_fee = astpp_n($setup_fee) + astpp_n($val['setup_fee']);
            $minutes = astpp_n($minutes) + astpp_n($val['free_minutes']);
            $totalamt = astpp_n($totalamt) + astpp_n($this->common->convert_to_currency_account("","",(astpp_n($val['price'])+astpp_n($val['setup_fee']))));
            $active_user = astpp_n($active_user) + astpp_n($this->db_model->countQuery("*","order_items",array("product_id"=>$val['productid'],"accountid"=>$val['accountid'],"is_terminated"=>0, "reseller_id" => 0),"accountid"));
            $total_user = astpp_n($total_user) + astpp_n($this->db_model->countQuery("*","order_items",array("product_id"=>$val['productid'],"accountid"=>$val['accountid'], "reseller_id" => 0),"accountid"));
            $total_product_count = astpp_n($total_product_count) + astpp_n($this->db_model->countQuery("*","order_items",array("accountid"=>$val['accountid'])));
       }else{
            if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            if(astpp_gt($reseller_id, 0)){
            $active_user = astpp_n($active_user) + astpp_n($this->db_model->countQuery("*","order_items",array("product_id"=>$val['productid'],"is_terminated"=>0,"reseller_id"=>$reseller_id),"accountid"));
            if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = astpp_n($total_user) + astpp_n($this->db_model->countQuery("*","order_items",array("product_id"=>$val['productid'],"reseller_id"=>$reseller_id),"accountid"));
         }else{
            $active_user = astpp_n($active_user) + astpp_n($this->db_model->countQuery("*","order_items",array("product_id"=>$val['productid'],"is_terminated"=>0, "reseller_id" => 0),"accountid"));
            if(isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] != "" ){
                $this->db->where('accountid',$session_info['order_items.accountid']); 
                }
            $total_user = astpp_n($total_user) + astpp_n($this->db_model->countQuery("*","order_items",array("product_id"=>$val['productid'], "reseller_id" => 0),"accountid"));
         }
            $quantity = astpp_n($quantity) + astpp_n($val['quantity']);
            $price = astpp_n($price) + astpp_n($val['price']);
            $setup_fee = astpp_n($setup_fee) + astpp_n($val['setup_fee']);
            $minutes = astpp_n($minutes) + astpp_n($val['free_minutes']);
            $totalamt = astpp_n($totalamt) + astpp_n($this->common->convert_to_currency_account("","",(astpp_n($val['price'])+astpp_n($val['setup_fee']))));
            $active_user;
            $total_user;
            $total_product_count = astpp_n($total_product_count) + astpp_n($this->db_model->countQuery("*","order_items",array("accountid"=>$val['accountid'])));
        }
        
        } 
//echo $active_user; exit;
    $free_min =astpp_n($minutes) *60;
    $avaiable_minutes = astpp_n($avaiable_minutes) + astpp_n($this->common->get_total_available_minutes($free_min,$usedsec));
        if ($show_seconds == 'minutes') {
        $minutes = astpp_n($minutes)*60;
            $free_minutes = astpp_gt($minutes, 0) ? sprintf('%02d', astpp_n($minutes) / 60) . ":" . sprintf('%02d', (astpp_n($minutes) % 60)) : "00:00";
        $avaiable_minutes = astpp_gt($avaiable_minutes, 0) ? sprintf('%02d', astpp_n($avaiable_minutes) / 60) . ":" . sprintf('%02d', (astpp_n($avaiable_minutes) % 60)) : "00:00";
        
       $usedsec = astpp_gt($usedsec, 0) ? sprintf('%02d', astpp_n($usedsec) / 60) . ":" . sprintf('%02d', (astpp_n($usedsec) % 60)) : "00:00";
        } else {
        $minutes = astpp_n($minutes)*60;
            $free_minutes = $minutes;
        $avaiable_minutes = sprintf('%02d', $avaiable_minutes);
        $usedsec = $usedsec;
        }
    if($row1['product_category'] == 1 ){
         if((isset($search_arr['groupby_1']) && ($search_arr['groupby_1'] == "order_items.product_id" || $search_arr['groupby_2'] == "order_items.product_id" ) &&  ((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )))){ 
        $last_array = array(
            
            "<b>" . $quantity . "</b>",
            "<b>" . $this->common->currency_decimal($price) . "</b>",
             "<b>" . $this->common->currency_decimal($setup_fee) . "</b>",
            "<b>" . $free_minutes . "</b>",
            "<b>" . $usedsec . "</b>",
            "<b>" . $avaiable_minutes . "</b>",
            "<b>" . $this->common->currency_decimal($totalamt) . "</b>",
            "<b>" . $active_user . "</b>",
            "<b>" . $total_user . "</b>",
        );  
        }else if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.product_id") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.product_id" )){
        if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.product_id") && (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.product_id" )){ 
            unset($search_arr['custom_total_array'][1]);
        }else{ 
            //unset($search_arr['custom_total_array'][1]);
        }
        $last_array = array(
            
            "<b>" . $quantity . "</b>",
            "<b>" . $this->common->currency_decimal($price) . "</b>",
             "<b>" . $this->common->currency_decimal($setup_fee) . "</b>",
            "<b>" . $free_minutes . "</b>",
            "<b>" . $usedsec . "</b>",
           "<b>" . $avaiable_minutes . "</b>",
            "<b>" . $this->common->currency_decimal($totalamt) . "</b>",
             "<b>" . $active_user . "</b>",
            "<b>" . $total_user . "</b>",
        );
        }else if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )){

            if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") && (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )){ 
                unset($search_arr['custom_total_array'][1]);
            }else{ 
                //unset($search_arr['custom_total_array'][1]);
            }
        $last_array = array(
           
           
            "<b>" . $free_minutes . "</b>",
            "<b>" . $usedsec . "</b>",
            "<b>" . $avaiable_minutes . "</b>",
            "<b>" .$total_product_count . "</b>",
           
        );
        }else{
        if(isset($search_arr['groupby_time']) && $search_arr['groupby_time'] !=""  ){
            $last_array = array(
               "",  
                "<b>" . $quantity . "</b>",
                "<b>" . $this->common->currency_decimal($price) . "</b>",
                 "<b>" . $this->common->currency_decimal($setup_fee) . "</b>",
                "<b>" . $free_minutes . "</b>",
                "<b>" . $usedsec . "</b>",
                 "<b>" . $avaiable_minutes . "</b>",
                "<b>" . $this->common->currency_decimal($totalamt) . "</b>",
               "<b>" . $active_user. "</b>",
                "<b>" . $total_user . "</b>",
            );

        }else{
            $last_array = array(
               
                "<b>" . $quantity . "</b>",
                "<b>" . $this->common->currency_decimal($price) . "</b>",
                 "<b>" . $this->common->currency_decimal($setup_fee) . "</b>",
                "<b>" . $free_minutes . "</b>",
                "<b>" . $usedsec . "</b>",
                 "<b>" . $avaiable_minutes . "</b>",
                "<b>" . $this->common->currency_decimal($totalamt) . "</b>",
               "<b>" . $active_user. "</b>",
                "<b>" . $total_user . "</b>",
            );
        }
    }   
    }
    if($row1['product_category'] == 2){
         if((isset($search_arr['groupby_1']) && ($search_arr['groupby_1'] == "order_items.product_id" || $search_arr['groupby_2'] == "order_items.product_id" ) &&  ((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )))){ 
            $last_array = array(
             
                "<b>" . $quantity . "</b>",
                "<b>" . $this->common->currency_decimal($price) . "</b>",
                 "<b>" . $this->common->currency_decimal($setup_fee) . "</b>",
                "<b>" . $this->common->currency_decimal($totalamt) . "</b>",
               "<b>" . $active_user . "</b>",
                    "<b>" . $total_user . "</b>",
            );
        }else if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.product_id") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.product_id" )){  
            if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.product_id") && (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.product_id" )){        

                    unset($search_arr['custom_total_array'][1] );
            }else{

            }
            $last_array = array(
                "<b>" . $quantity . "</b>", 
                "<b>" . $this->common->currency_decimal($price). "</b>",
                 "<b>" . $this->common->currency_decimal($setup_fee) . "</b>",
                "<b>" . $this->common->currency_decimal($totalamt) . "</b>",
                "<b>" . $active_user . "</b>",
                    "<b>" . $total_user . "</b>",
            );
        }else if((isset($search_arr['groupby_1']) && $search_arr['groupby_1'] == "order_items.accountid") || (isset($search_arr['groupby_2']) && $search_arr['groupby_2'] == "order_items.accountid" )){
            unset($search_arr['custom_total_array'][1] );
            $last_array = array(
                "<b>" . $total_product_count . "</b>",
            );
        }else{
            if(isset($search_arr['groupby_time']) && $search_arr['groupby_time'] !=""  ){
                $last_array = array(
                   "",
                    "<b>" . $quantity . "</b>",
                    "<b>" . $this->common->currency_decimal($price) . "</b>",
                     "<b>" . $this->common->currency_decimal($setup_fee) . "</b>",
                    "<b>" . $this->common->currency_decimal($totalamt) . "</b>",
                    "<b>" . $active_user . "</b>",
                    "<b>" . $total_user . "</b>",
                );
            }else{
                $last_array = array(
                    "<b>" . $quantity . "</b>",
                    "<b>" . $this->common->currency_decimal($price) . "</b>",
                     "<b>" . $this->common->currency_decimal($setup_fee) . "</b>",
                    "<b>" . $this->common->currency_decimal($totalamt) . "</b>",
                    "<b>" . $active_user . "</b>",
                    "<b>" . $total_user . "</b>",
                );


            }
        }
    }
        if ($purpose == 'export') {
            $search_arr['custom_total_array'][0] = "Grand Total";
        }
        $new_export_array = array();
        foreach ($last_array as $key => $value) {
            $value = str_replace("<b>", "", $value);
            $value = str_replace("</b>", '', $value);
            if ($key == 7 || $key == 8 || $key == 9) {
                $value = sprintf("%." . $currency_info['decimalpoints'] . "f", floatval($value));
            }
            $new_export_array[$key] = $value;
        }
        $total_array = astpp_array_merge($search_arr['custom_total_array'], $last_array);
        $custom_export_arr = astpp_array_merge($search_arr['custom_total_array'], $new_export_array);
        $export_arr[] = $custom_export_arr;
        $json_data[] = array(
            'cell' => $total_array
        );
            
        return $purpose == 'grid' ? $json_data : $export_arr;
    }


 function product_list_dropdown(){ 
	$add_array = $this->input->post();
	$reseller_id = $this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5 ? $this->session->userdata['accountinfo']['id'] : 0;
	if(astpp_gt($reseller_id, 0)){
		$product_item_list = $this->db_model->getJionQuery('products', ' products.id,products.name', array('reseller_products.account_id' =>$reseller_id,"products.product_category"=>$add_array['category_id']), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', '', '');
		if($product_item_list->num_rows  > 0){
			 $product_list = $product_item_list->result_array();
			 $product_item_list = array();
		        foreach ($product_list as $value) {
		            $product_item_list[$value['id']] = $value['name'];
		        }
		        $data['product_item_list'] = $product_item_list;
		}else{
			$data['product_item_list'] = array();
		}
	}else{
		$data['product_item_list'] = $this->db_model->build_dropdown("id,name", "products", "where_arr",array("product_category"=>$add_array['category_id']));
	}
	$product_item = array("id" => "product_id","name" => "product_id","class" => "product_id");
        $data['productlist'] = form_dropdown_all($product_item, $data['product_item_list'], $add_array['product_id'], '');
	echo $data['productlist'];
        exit();
   }
    function product_export_csv()
    {
        $search_arr = $this->session->userdata('productsummary_reports_export');
        $data_arr = array();
        $query = $this->summary_model->get_productsummary_report_list(true, '', '', $search_arr['group_by_str'], $search_arr['select_str'], $search_arr['order_str'], true);
        $search_header = explode(",", $search_arr['export_str']);
	if($query->num_rows > 0){
		$product_category = $query->result_array()[0]['product_category'];
	}else{
		$product_category = 1;
	}
        ob_clean();
	/*	if($product_category == 2){
			$fixed_header = array(
			    'Quantity',
			    'Price',
			    'Setup Fee',
			    'Total Price',
			    'Active User',
			    'Total User'

			);
	   	}else{
			$fixed_header = array(
			    'Quantity',
			    'Price',
			    'Setup Fee',
			    'Free Minutes',
			    'Used Minutes',
			    'Available Minutes',
			    'Total Price',
			    'Active User',
			    'Total User'

			);
		}*/
	$new_search_array = array();
        $new_column_arr = $this->summary_column_arr('product');
		$grid_field = $this->summary_form->build_product_summary($new_column_arr);
		$grid_field_arr = json_decode($grid_field,true);

		$fixed_header_new = array();
		foreach($grid_field_arr as $fix_val){
			if(!empty($fix_val)){
				$fixed_header_new[] = $fix_val[0];
			}
		}
//echo "<pre>"; print_r($fixed_header_new); exit;
//echo "<pre>"; print_r($fixed_header);

/*	if(!empty($new_column_arr) && (isset($search_arr['groupby_1']) || isset($search_arr['groupby_2']))){
		foreach($new_column_arr as $column_value){
			$new_search_array[] = $column_value[0];
		}
	}else{
			$new_search_array[] ="Product Name";
	}*/
//echo "<pre>"; print_r($fixed_header_new);// exit;
//        $header_arr[] = array_merge($new_search_array, $fixed_header_new);
        $header_arr[] = $fixed_header_new;
//echo "<pre>"; print_r($header_arr); exit;
        if ($query->num_rows() > 0) {
            $data_arr = $this->product_summary_report_grid($search_arr, $query, 'product', 'export');
        }
        $customer_array = astpp_array_merge($header_arr, $data_arr);
        $this->load->helper('csv');
        array_to_csv($customer_array, 'Product_Summary_Report_' . date("Y-m-d") . '.csv');
    }
}
?>

