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
class Reports extends MX_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->helper('template_inheritance');

        $this->load->library('session');
        $this->load->library("reports_form");
        $this->load->library('astpp/form', 'reports_form');
        $this->load->model('reports_model');
        $this->load->library('ASTPP_Sms');

        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function customerReport()
    {
        $data['page_title'] = gettext('Customer CDRs Report');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('advance_search_date', 1);
        $data['grid_fields'] = $this->reports_form->build_report_list_for_customer();
        $data["grid_buttons"] = $this->reports_form->build_grid_customer();
        $data['form_search'] = $this->form->build_serach_form($this->reports_form->get_customer_cdr_form());
        $accountinfo = $this->session->userdata('accountinfo');
        $this->load->view('view_cdr_customer_list', $data);
    }

    function customerReport_json()
    {
        $count_res = $this->reports_model->getcustomer_cdrs(false, "", "");
        $accountinfo = $this->session->userdata('accountinfo');
        $count_all = (array) $count_res->first_row();
        $paging_data = $this->form->load_grid_config($count_all['count'], $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->reports_model->getcustomer_cdrs(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);

        if ($query->num_rows() > 0) {
            $pricelist_arr = array();
            $trunk_arr = array();
            $search_arr = $this->session->userdata('customer_cdr_list_search');
            $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
            $query = $query->result_array();

            $where = "id IN (" . $count_all['pricelist_ids'] . ")";
            $this->db->where($where);
            $this->db->select('id,name');
            $pricelist_res = $this->db->get('pricelists');
            $pricelist_res = $pricelist_res->result_array();
            foreach ($pricelist_res as $value) {
                $pricelist_arr[$value['id']] = $value['name'];
            }

            $where = "id IN (" . $count_all['trunk_ids'] . ")";
            $this->db->where($where);
            $this->db->select('id,name');
            $trunk_res = $this->db->get('trunks');
            $trunk_res = $trunk_res->result_array();
            foreach ($trunk_res as $value) {
                $trunk_arr[$value['id']] = $value['name'];
            }
            $where = "id IN (" . $count_all['accounts_ids'] . ")";
            $this->db->where($where);
            $this->db->select('id,number,first_name,last_name,is_recording');
            $account_res = $this->db->get('accounts');
            foreach ($account_res->result_array() as $value) {
                $account_arr[$value['id']] = $value['first_name'] . " " . $value['last_name'] . ' (' . $value['number'] . ')';
                $account_is_recording[$value['id']] = $value['is_recording'];
            }
            $currency_info = $this->common->get_currency_info();
            foreach ($query as $value) {
                $duration = ($show_seconds == 'minutes') ? ($value['billseconds'] > 0) ? sprintf('%02d', $value['billseconds'] / 60) . ":" . sprintf('%02d', $value['billseconds'] % 60) : "00:00" : $value['billseconds'];
                $account = isset($account_arr[$value['accountid']]) ? $account_arr[$value['accountid']] : 'Anonymous';
                $is_recording = isset($account_is_recording[$value['accountid']]) ? $account_is_recording[$value['accountid']] : '1';
                $uid = $value['uniqueid'];
                if (($value['calltype'] == 'LOCAL' || $value['calltype'] == 'STANDARD') && $value['call_direction'] == 'inbound') {
                    $uid = rtrim($uid, $value['calltype'] . '_' . $value['accountid']);
                }
                $file_name = $this->config->item('recordings_path') . $uid . ".wav";
                if (file_exists($file_name) && $value['calltype'] != 'FAX') {
                    $billseconds = $value['billseconds'];
                    $url = base_url() . "reports/customerReport_recording_download/" . $uid . ".wav";
                    $play_img_url = base_url() . "assets/images/play_file.png";
                    $pause_img_url = base_url() . "assets/images/pause.png";
                    $action = '<audio id="myAudio_' . $uid . '">
					<source src="' . $url . '" type="audio/mpeg">
					Your browser does not support the audio element.
					</audio>';
                    $action .= "<button onclick='playAudio(\"$uid\",\"$billseconds\")' type='button' class='btnplay'  id='play_" . $uid . "'  style='display:block;margin:0px 0 0 25px;border:0px !important; float:left; padding:0px'><img src=" . $play_img_url . " height='25px' width='25px' style='cursor: pointer;'/></button>";

                    $action .= "<button onclick='pauseAudio(\"$uid\")' type='button'  class='btnplay' id='pause_" . $uid . "' style='display: none;margin:0px 0 0 25px;border:0px !important; float:left;padding:0px'><img src=" . $pause_img_url . " height='25px' width='25px' style='cursor: pointer;'/></button>";
                    $recording = ($is_recording == 0) ? '<a title="Recording file" href="' . $url . '"><img src="' . base_url() . 'assets/images/download.png" height="20px" width="20px"/></a>' : '<img src="' . base_url() . 'assets/images/false.png" height="20px" alt="file not found" width="20px"/>';
                } else {
                    $recording = '<img src="' . base_url() . 'assets/images/false.png" height="20px" title="Record file is not available" width="20px"/>';
                    $action = '<img src="' . base_url() . 'assets/images/false.png" height="20px" title="Play file is not available" width="20px"/>';
                }
                if ($accountinfo['type'] == 1) {
                    $json_data['rows'][] = array(
                        'cell' => array(
                            $this->common->convert_GMT_to('', '', $value['callstart']),
                            $value['callerid'],
                            $value['callednum'],
                            $value['sip_user'],
                            filter_var($value['pattern'], FILTER_SANITIZE_NUMBER_INT),
                            $value['notes'],
                            $duration,
                            $this->common->calculate_currency_manually($currency_info, $value['debit'], false),
                            $this->common->calculate_currency_manually($currency_info, $value['cost'], false),
                            $value['disposition'],
                            $account,
                            isset($pricelist_arr[$value['pricelist_id']]) ? $pricelist_arr[$value['pricelist_id']] : '',
                            $value['call_direction'],
                            $recording . "  " . $action
                        )
                    );
                } else {
                    $json_data['rows'][] = array(
                        'cell' => array(
                            $this->common->convert_GMT_to('', '', $value['callstart']),
                            $value['callerid'],
                            $value['callednum'],
                            $value['sip_user'],
                            filter_var($value['pattern'], FILTER_SANITIZE_NUMBER_INT),
                            $value['notes'],
                            $duration,
                            $this->common->calculate_currency_manually($currency_info, $value['debit'], false),
                            $this->common->calculate_currency_manually($currency_info, $value['cost'], false),
                            $value['disposition'],
                            $account,
                            isset($trunk_arr[$value['trunk_id']]) ? $trunk_arr[$value['trunk_id']] : '',
                            isset($pricelist_arr[$value['pricelist_id']]) ? $pricelist_arr[$value['pricelist_id']] : '',
                            $value['calltype'],
                            $value['call_direction'],
                            $recording . "  " . $action
                        )
                    );
                }
            }
            $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf('%02d', $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
            $json_data['rows'][] = array(
                "cell" => array(
                    "<b>".gettext("Grand Total")."</b>",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "<b>$duration</b>",
                    "<b>" . $this->common->calculate_currency_manually($currency_info, $count_all['total_debit'] - $count_all['free_debit'], false) . "</b>",
                    "<b>" . $this->common->calculate_currency_manually($currency_info, $count_all['total_cost'], false) . "</b>",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",

                    ""
                )
            );
        }
        echo json_encode($json_data);
    }

    function customerReport_recording_download($file_name)
    {
        $file_name = $this->common->recording_file($file_name);
        if ($file_name === false) {
            show_404();
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_name));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();
        readfile($file_name);
        exit();
    }

    function customerReport_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('customerReport_cdrs_year', $this->input->post('cdrs_year'));
            $action = $this->input->post();
            if ($action['search_in'] == 'minutes') {
                if (isset($action['billseconds']['billseconds'])) {
                    $duration = explode(':', $action['billseconds']['billseconds']);
                    if (isset($duration[0]) && isset($duration[1])) {

                        if (is_numeric($duration[0]) && is_numeric($duration[1])) {
                            $action['billseconds']['billseconds'] = (60 * $duration[0]) + $duration[1];
                        }
                    }
                }
            }
            $this->session->set_userdata('advance_search_date', 0);
            unset($action['action']);
            unset($action['advance_search']);
            if(!empty($action['callstart'][0])){
                $action['callstart'][0]=$this->common->convert_GMT_new ( $action['callstart'][0]);
            }
            if(!empty($action['callstart'][1])){
                $action['callstart'][1]=$this->common->convert_GMT_new ( $action['callstart'][1]);
            }
            unset($action['cdrs_year']);
            $this->session->set_userdata('customer_cdr_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'reports/customerReport/');
        }
    }

    function customerReport_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('advance_search_date', 0);
        $this->session->set_userdata('customer_cdr_list_search', "");
        $this->session->unset_userdata('customerReport_cdrs_year');
    }

    function customerReport_export()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $count_res = $this->reports_model->getcustomer_cdrs(false, "", "");
        $count_all = (array) $count_res->first_row();
        ob_clean();
        if ($count_all['count'] > 0) {
            $pricelist_arr = array();
            $trunk_arr = array();
            $account_arr = array();
            $query = $this->reports_model->getcustomer_cdrs(true, '', '', true);
            $currency_info = $this->common->get_currency_info();
            $search_arr = $this->session->userdata('customer_cdr_list_search');
            $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
            $where = "id IN (" . $count_all['pricelist_ids'] . ")";
            $this->db->where($where);
            $this->db->select('id,name');
            $pricelist_res = $this->db->get('pricelists');
            $pricelist_res = $pricelist_res->result_array();
            foreach ($pricelist_res as $value) {
                $pricelist_arr[$value['id']] = $value['name'];
            }
            $where = "id IN (" . $count_all['accounts_ids'] . ")";
            $this->db->where($where);
            $this->db->select('id,number,first_name,last_name');
            $account_res = $this->db->get('accounts');
            foreach ($account_res->result_array() as $value) {
                $account_arr[$value['id']] = $value['first_name'] . " " . $value['last_name'] . ' (' . $value['number'] . ')';
            }

            if ($accountinfo['type'] != 1) {
                $customer_array[] = array(
                    gettext("Date"),
                    gettext("Caller ID"),
                    gettext("Called Number"),
                    gettext("Code"),
                    gettext("Destination"),
                    gettext("Duration"),
                    gettext("Debit") . "(" . $currency . ")",
                    gettext("Cost") . "(" . $currency . ")",
                    gettext("Disposition"),
                    gettext("Account"),
                    gettext("Trunk"),
                    gettext("Rate Group"),
                    gettext("Call Type"),
                    gettext("Direction"),
                    gettext("SIP User")
                );
                $where = "id IN (" . $count_all['trunk_ids'] . ")";
                $this->db->where($where);
                $this->db->select('id,name');
                $trunk_res = $this->db->get('trunks');
                $trunk_res = $trunk_res->result_array();
                foreach ($trunk_res as $value) {
                    $trunk_arr[$value['id']] = $value['name'];
                }
                foreach ($query->result_array() as $value) {
                    $duration = ($show_seconds == 'minutes') ? ($value['billseconds'] > 0) ? floor($value['billseconds'] / 60) . ":" . sprintf('%02d', $value['billseconds'] % 60) : "00:00" : $value['billseconds'];
                    $account = isset($account_arr[$value['accountid']]) ? $account_arr[$value['accountid']] : 'Anonymous';
                    $customer_array[] = array(
                        $this->common->convert_GMT_to('', '', $value['callstart']),
                        $value['callerid'],
                        $value['callednum'],
                        filter_var($value['pattern'], FILTER_SANITIZE_NUMBER_INT),
                        $value['notes'],
                        $duration,
                        $this->common->calculate_currency_manually($currency_info, $value['debit'], false, false),
                        $this->common->calculate_currency_manually($currency_info, $value['cost'], false, false),
                        $value['disposition'],
                        $account,
                        isset($trunk_arr[$value['trunk_id']]) ? $trunk_arr[$value['trunk_id']] : '',
                        isset($pricelist_arr[$value['pricelist_id']]) ? $pricelist_arr[$value['pricelist_id']] : '',
                        $value['calltype'],
                        $value['call_direction'],
                        $value['sip_user']
                    );
                }
                $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf('%02d', $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
                $customer_array[] = array(
                    "Grand Total",
                    "",
                    "",
                    "",
                    "",
                    $duration,
                    $this->common->calculate_currency_manually($currency_info, $count_all['total_debit'], false, false),
                    $this->common->calculate_currency_manually($currency_info, $count_all['total_cost'], false, false),
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                );
            } else {
                $customer_array[] = array(
                    gettext("Date"),
                    gettext("CallerID"),
                    gettext("Called Number"),
                    gettext("Code"),
                    gettext("Destination"),
                    gettext("Duration"),
                    gettext("Debit") . "(" . $currency . ")",
                    gettext("Cost") . "(" . $currency . ")",
                    gettext("Disposition"),
                    gettext("Account"),
                    gettext("Rate Group"),
                    gettext("Direction"),
                    gettext("SIP User")
                );
                foreach ($query->result_array() as $value) {
                    $duration = ($show_seconds == 'minutes') ? ($value['billseconds'] > 0) ? floor($value['billseconds'] / 60) . ":" . sprintf('%02d', $value['billseconds'] % 60) : "00:00" : $value['billseconds'];
                    $account = isset($account_arr[$value['accountid']]) ? $account_arr[$value['accountid']] : 'Anonymous';
                    $customer_array[] = array(
                        $this->common->convert_GMT_to('', '', $value['callstart']),
                        $value['callerid'],
                        $value['callednum'],
                        filter_var($value['pattern'], FILTER_SANITIZE_NUMBER_INT),
                        $value['notes'],
                        $duration,
                        $this->common->calculate_currency_manually($currency_info, $value['debit'], false, false),
                        $this->common->calculate_currency_manually($currency_info, $value['cost'], false, false),
                        $value['disposition'],
                        $account,
                        isset($pricelist_arr[$value['pricelist_id']]) ? $pricelist_arr[$value['pricelist_id']] : '',
                        $value['call_direction'],
                        $value['sip_user']
                    );
                }
                $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf('%02d', $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
                $customer_array[] = array(
                    "Grand Total",
                    "",
                    "",
                    "",
                    "",
                    $duration,
                    $this->common->calculate_currency_manually($currency_info, $count_all['total_debit'], false, false),
                    $this->common->calculate_currency_manually($currency_info, $count_all['total_cost'], false, false),
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                );
            }
        }
        $this->load->helper('csv');
        if (isset($customer_array)) {
            array_to_csv($customer_array, 'Customer_CDR_' . date("Y-m-d") . '.csv');
        } else {
            $customer_array[] = array(
                gettext("Date"),
                gettext("Caller ID"),
                gettext("Called Number"),
                gettext("Code"),
                gettext("Destination"),
                gettext("Duration"),
                gettext("Debit") . "(" . $currency . ")",
                gettext("Cost") . "(" . $currency . ")",
                gettext("Disposition"),
                gettext("Account"),
                gettext("Rate Group"),
                gettext("Call Type"),
                gettext("Direction"),
                gettext("SIP User")
            );
            array_to_csv($customer_array, 'Customer_CDR_' . date("Y-m-d") . '.csv');
        }
    }

    function resellerReport()
    {
        $data['page_title'] = gettext('Resellers CDRs Report');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('advance_search_date', 1);
        $data['grid_fields'] = $this->reports_form->build_report_list_for_reseller();
        $data["grid_buttons"] = $this->reports_form->build_grid_buttons_reseller();
        $data['form_search'] = $this->form->build_serach_form($this->reports_form->get_reseller_cdr_form());
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $data['reseller_id'] = $reseller_id;
        $this->load->view('view_cdr_reseller_list', $data);
    }

    function resellerReport_json()
    {
        $count_res = $this->reports_model->getreseller_cdrs(false, "", "");
        $count_all = (array) $count_res->first_row();
        $paging_data = $this->form->load_grid_config($count_all['count'], $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->reports_model->getreseller_cdrs(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->reports_form->build_report_list_for_reseller());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        if ($count_all['count'] > 0) {
            $search_arr = $this->session->userdata('reseller_cdr_list_search');
            $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
            $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? sprintf('%02d', $count_all['billseconds'] / 60) . ":" . sprintf('%02d', $count_all['billseconds'] % 60) : "00:00" : sprintf('%02d', $count_all['billseconds']);
            $json_data['rows'][] = array(
                "cell" => array(
                    "<b>".gettext("Grand Total")."</b>",
                    "",
                    "",
                    "",
                    "",
                    "<b>$duration</b>",
                    "<b>" . $this->common_model->calculate_currency($count_all['total_debit'] - $count_all['free_debit'], '', '', true, false) . "</b>",
                    "<b>" . $this->common_model->calculate_currency($count_all['total_cost'], '', '', true, false) . "</b>",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                )
            );
        }
        echo json_encode($json_data);
    }

    function resellerReport_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('resellerreport_cdrs_year', $this->input->post('cdrs_year'));
            $action = $this->input->post();
            if ($action['search_in'] == 'minutes') {
                if (isset($action['billseconds']['billseconds'])) {
                    $duration = explode(':', $action['billseconds']['billseconds']);
                    if (isset($duration[0]) && isset($duration[1])) {

                        if (is_numeric($duration[0]) && is_numeric($duration[1])) {
                            $action['billseconds']['billseconds'] = (60 * $duration[0]) + $duration[1];
                        }
                    }
                }
            }
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('advance_search_date', 0);
            unset($action['cdrs_year']);
            $this->session->set_userdata('reseller_cdr_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'reports/resellerReport/');
        }
    }

    function resellerReport_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('advance_search_date', 0);
        $this->session->set_userdata('account_search', "");
        $this->session->unset_userdata('resellerreport_cdrs_year');
    }

    function resellerReport_export()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $count_res = $this->reports_model->getreseller_cdrs(false, "", "");
        $count_all = (array) $count_res->first_row();
        ob_clean();
        $reseller_array[] = array(
            gettext("Date"),
            gettext("Caller ID"),
            gettext("Called Number"),
            gettext("Code"),
            gettext("Destination"),
            gettext("Duration"),
            gettext("Debit") . "(" . $currency . ")",
            gettext("Cost") . "(" . $currency . ")",
            gettext("Disposition"),
            gettext("Account"),
            gettext("Rate Group"),
            gettext("Trunk"),
            gettext("Call Type"),
            gettext("Direction")
        );
        if ($count_all['count'] > 0) {
            $pricelist_arr = array();
            $query = $this->reports_model->getreseller_cdrs(true, '', '', true);
            $currency_info = $this->common->get_currency_info();
            $search_arr = $this->session->userdata('reseller_cdr_list_search');
            $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
            $where = "id IN (" . $count_all['pricelist_ids'] . ")";
            $this->db->where($where);
            $this->db->select('id,name');
            $pricelist_res = $this->db->get('pricelists');
            $pricelist_res = $pricelist_res->result_array();
            foreach ($pricelist_res as $value) {
                $pricelist_arr[$value['id']] = $value['name'];
            }
            $where = "id IN (" . $count_all['trunk_ids'] . ")";
            $this->db->where($where);
            $this->db->select('id,name');
            $trunk_res = $this->db->get('trunks');
            $trunk_res = $trunk_res->result_array();
            foreach ($trunk_res as $value) {
                $trunk_arr[$value['id']] = $value['name'];
            }
            foreach ($query->result_array() as $value) {
                $duration = ($show_seconds == 'minutes') ? ($value['billseconds'] > 0) ? sprintf('%02d', $value['billseconds'] / 60) . ":" . sprintf('%02d', $value['billseconds'] % 60) : "00:00" : $value['billseconds'];
                $reseller_array[] = array(
                    $this->common->convert_GMT_to('', '', $value['callstart']),
                    $value['callerid'],
                    $value['callednum'],
                    filter_var($value['pattern'], FILTER_SANITIZE_NUMBER_INT),
                    $value['notes'],
                    $duration,
                    $this->common->calculate_currency_manually($currency_info, $value['debit'], false, false),
                    $this->common->calculate_currency_manually($currency_info, $value['cost'], false, false),
                    $value['disposition'],
                    $this->common->build_concat_string("first_name,last_name,number", "accounts", $value['accountid']),
                    isset($pricelist_arr[$value['pricelist_id']]) ? $pricelist_arr[$value['pricelist_id']] : '',
                    isset($trunk_arr[$value['trunk_id']]) ? $trunk_arr[$value['trunk_id']] : '',
                    $value['calltype'],
                    $value['call_direction']
                );
            }
            $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf('%02d', $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
            $reseller_array[] = array(
                gettext("Grand Total"),
                "",
                "",
                "",
                "",
                $duration,
                $this->common->calculate_currency_manually($currency_info, $count_all['total_debit'], false, false),
                $this->common->calculate_currency_manually($currency_info, $count_all['total_cost'], false, false),
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                ""
            );
        }
        $this->load->helper('csv');
        array_to_csv($reseller_array, 'Reseller_CDR_' . date("Y-m-d") . '.csv');
    }

    function providerReport()
    {
        $data['page_title'] = gettext('Provider CDRs Report');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('advance_search_date', 1);
        $data['grid_fields'] = $this->reports_form->build_report_list_for_provider();
        $data["grid_buttons"] = $this->reports_form->build_grid_buttons_provider();
        $data['form_search'] = $this->form->build_serach_form($this->reports_form->get_provider_cdr_form());
        $this->load->view('view_cdr_provider_list', $data);
    }

    function providerReport_json()
    {
        $count_res = $this->reports_model->getprovider_cdrs(false, "", "");
        $count_all = (array) $count_res->first_row();
        $paging_data = $this->form->load_grid_config($count_all['count'], $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->reports_model->getprovider_cdrs(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->reports_form->build_report_list_for_provider());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        if ($count_all['count'] > 0) {
            $search_arr = $this->session->userdata('provider_cdr_list_search');
            $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
            $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf("%02d", $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
            $json_data['rows'][] = array(
                "cell" => array(
                    "<b>".gettext("Grand Total")."</b>",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "<b>$duration</b>",
                    "<b>" . $this->common_model->calculate_currency($count_all['total_cost'], '', '', true, false) . "</b>",
                    "",
                    "",
                    "",
                    ""
                )
            );
        }
        echo json_encode($json_data);
    }

    function providerReport_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $this->session->set_userdata('providerreport_cdrs_year', $this->input->post('cdrs_year'));
            $action = $this->input->post();
            if ($action['search_in'] == 'minutes') {
                if (isset($action['billseconds']['billseconds'])) {
                    $duration = explode(':', $action['billseconds']['billseconds']);
                    if (isset($duration[0]) && isset($duration[1])) {
                        if (is_numeric($duration[0]) && is_numeric($duration[1])) {
                            $action['billseconds']['billseconds'] = (60 * $duration[0]) + $duration[1];
                        }
                    }
                }
            }
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('advance_search_date', 0);
            unset($action['cdrs_year']);
            if(!empty($action['callstart'][0])){
                $action['callstart'][0]=$this->common->convert_GMT_new ( $action['callstart'][0]);
            }
            if(!empty($action['callstart'][1])){
                $action['callstart'][1]=$this->common->convert_GMT_new ( $action['callstart'][1]);
            }
            $this->session->set_userdata('provider_cdr_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'reports/providerReport/');
        }
    }

    function providerReport_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('advance_search_date', 0);
        $this->session->set_userdata('account_search', "");
        $this->session->unset_userdata('providerreport_cdrs_year');
    }

    function providerReport_export()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $count_res = $this->reports_model->getprovider_cdrs(false, "", "");
        $count_all = (array) $count_res->first_row();
        ob_clean();
        $provider_array[] = array(
            gettext("Date"),
            gettext("Caller ID"),
            gettext("Called Number"),
            gettext("SIP User"),
            gettext("Code"),
            gettext("Destination"),
            gettext("Duration"),
            gettext("Cost") . "(" . $currency . ")",
            gettext("Disposition"),
            gettext("Account"),
            gettext("Direction")
        );
        if ($count_all['count'] > 0) {
            $query = $this->reports_model->getprovider_cdrs(true, '', '', true);
            $currency_info = $this->common->get_currency_info();
            $search_arr = $this->session->userdata('provider_cdr_list_search');
            $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
            foreach ($query->result_array() as $value) {
                $duration = ($show_seconds == 'minutes') ? ($value['billseconds'] > 0) ? floor($value['billseconds'] / 60) . ":" . sprintf("%02d", $value['billseconds'] % 60) : "00:00" : $value['billseconds'];
                $account_arr = $this->db_model->getSelect('*', 'accounts', array(
                    'id' => $value['provider_id']
                ));
                if ($account_arr->num_rows() > 0) {
                    $account_array = $account_arr->result_array();
                    $account = $account_array[0]['first_name'] . " " . $account_array[0]['last_name'] . ' (' . $account_array[0]['number'] . ')';
                } else {
                    $account = "Anonymous";
                }
                $provider_array[] = array(
                    $this->common->convert_GMT_to('', '', $value['callstart']),
                    $value['callerid'],
                    $value['callednum'],
                    $value['sip_user'],
                    filter_var($value['pattern'], FILTER_SANITIZE_NUMBER_INT),
                    $value['notes'],
                    $duration,
                    $this->common->calculate_currency_manually($currency_info, $value['cost'], false, false),
                    $value['disposition'],
                    $account,
                    $value['call_direction']
                );
            }
            $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf("%02d", $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
            $provider_array[] = array(
                gettext("Grand Total"),
                "",
                "",
                "",
                "",
                "",
                $duration,
                $this->common->calculate_currency_manually($currency_info, $count_all['total_cost'], false, false),
                "",
                "",
                "",
                "",
                "",
                "",
                ""
            );
        }
        $this->load->helper('csv');
        array_to_csv($provider_array, 'Provider_CDR_' . date("Y-m-d") . '.csv');
    }

    function user_refillreport()
    {
        $json_data = array();
        $count_all = $this->reports_model->getuser_refill_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->reports_model->getuser_refill_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->reports_form->build_refill_report_for_user());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function user_refillreport_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('cdr_refill_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'user/user_cdrs_report/');
        }
    }

    function user_refillreport_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function customer_cdrreport($accountid, $accounttype)
    {
        $instant_search = $this->session->userdata('left_panel_search_' . $accounttype . '_cdrs');

        if (isset($instant_search) && $instant_search != "") {
            $like_str = ! empty($instant_search) ? "(callstart like '%$instant_search%'  
                                            OR  callerid like '%$instant_search%'
                                            OR  callednum like '%$instant_search%' 
                                            OR  notes like '%$instant_search%'
                                            OR  disposition like '%$instant_search%' 
                                            OR  calltype like '%$instant_search%' 
  
                                                )" : null;
        }
        $json_data = array();
        if (! empty($like_str))
            $this->db->where($like_str);
        $count_all = $this->reports_model->users_cdrs_list(false, $accountid, $accounttype, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        if (! empty($like_str))
            $this->db->where($like_str);
        $query = $this->reports_model->users_cdrs_list(true, $accountid, $accounttype, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->reports_form->build_report_list_for_user($accounttype));
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function refillreport()
    {
        $data['page_title'] = gettext('Refill Report');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_buttons'] = $this->reports_form->build_refillreport_buttons();
        $data['grid_fields'] = $this->reports_form->build_refill_report_for_admin();
        $data['form_search'] = $this->form->build_serach_form($this->reports_form->build_search_refill_report_for_admin());
        $this->load->view('view_refill_report', $data);
    }

    function refillreport_json()
    {
        $json_data = array();
        $count_all = $this->reports_model->get_refill_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->reports_model->get_refill_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);

        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $transaction_details = json_decode($value['transaction_details'], true);
            $payer_email = Common_model::$global_config['system_config']['paypal_id'];
            $where = array(
                "id" => $value['accountid']
            );
            $account_arr = $this->db_model->getSelect("*", "accounts", $where);
            if ($account_arr->num_rows() > 0) {
                $account_array = $account_arr->result_array();
                if(isset($account_array[0]['company_name']) && !empty($account_array[0]['company_name'])){
                    $firstname = $account_array[0]['company_name'] . " " . ' (' . $account_array[0]['number'] . ')';
                }else{
                    $firstname = $account_array[0]['first_name'] . " " . $account_array[0]['last_name'] . ' (' . $account_array[0]['number'] . ')';
                }
            }
            if ($value['reseller_id'] != '') {
                $reseller_name = $this->common->reseller_select_value('first_name,last_name,number,company_name', 'accounts', $value['reseller_id']);
            }

            $payeremail = ($value['reseller_id'] != '') ? $this->common->get_field_name("email", "accounts", array(
                "id" => $value['reseller_id']
            )) : $this->common->get_field_name("email", "accounts", array(
                "id" => 1
            ));
            if ($value['payment_method'] == "Paypal") {
                $json_data['rows'][] = array(
                    'cell' => array(
                        $this->common->convert_GMT_to('', '', $value['date']),
                        $firstname,
                        $this->common->convert_to_currency_account("", "", $value['amount']),
                        $value['payment_method'],
                        $value['transaction_id'],
                        $payer_email,
                        $value['customer_ip'],
                        $transaction_details['description'],
                        $reseller_name,
                        "PAID"
                    )
                );
            } else {
                $json_data['rows'][] = array(
                    'cell' => array(
                        $this->common->convert_GMT_to('', '', $value['date']),
                        $firstname,
                        $this->common->convert_to_currency_account("", "", $value['amount']),
                        $value['payment_method'],
                        $value['transaction_id'],
                        $payeremail,
                        $value['customer_ip'],
                        $value['description'],
                        $reseller_name,
                        "PAID"
                    )
                );
            }
        }

        echo json_encode($json_data);
    }

    function refillreport_export()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        if($account_info['type'] == 1){
            $reseller_id = $account_info['id'];
        }else{
            $reseller_id = $account_info['reseller_id'] > 0 ? $account_info['reseller_id'] : 0;
        }
        $account_arr = array();
        $transaction_details = array();
        $currency_info = $this->common->get_currency_info();
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);

        ob_clean();
        $customer_array[] = array(
            gettext("Date"),
            gettext("Account"),
            gettext("Amount") . "(" . $currency . ")",
            gettext("Payment Method"),
            gettext("Transaction ID"),
            gettext("Receiver Email"),
            gettext("Client IP"),
            gettext("Description"),
            gettext("Reseller"),
            gettext("Status")
        );
        $query = $this->reports_model->get_refill_list(true, "", "", true);

        $this->db->select("concat(first_name,' ',last_name,' ','(',number,')') as first_name,id", false);
        $this->db->where('reseller_id', $reseller_id);
        $this->db->where_not_in('type', array(
            "-1,2,4"
        ));
        $account_res = $this->db->get('accounts');
        if ($account_res->num_rows() > 0) {
            $account_res = $account_res->result_array();
            foreach ($account_res as $key => $value) {
                $account_arr[$value['id']] = $value['first_name'];
            }
        }
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $payeremail = ($row['reseller_id'] != '') ? $this->common->get_field_name("email", "accounts", array(
                    "id" => $row['reseller_id']
                )) : $this->common->get_field_name("email", "accounts", array(
                    "id" => 1
                ));
                $transaction_details = json_decode($row['transaction_details'], true);

                $customer_array[] = array(
                    $row['date'],
                    isset($account_arr[$row['accountid']]) ? $account_arr[$row['accountid']] : 'Anonymous',

                    $this->common->convert_to_currency_account("", "", $row['amount']),
                    $row['payment_method'],
                    $row['transaction_id'],
                    $payeremail,
                    $row['customer_ip'],
                    $transaction_details['description'],
                    $this->common->reseller_select_value('first_name,last_name,number', 'accounts', $row['reseller_id']),
                    "PAID"
                );
            }
        }
        $this->load->helper('csv');
        array_to_csv($customer_array, 'Refill_Report_' . date("Y-m-d") . '.csv');
    }

    function refillreport_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            if(!empty($action['date'][0])){
                $action['date'][0]=$this->common->convert_GMT_new ( $action['date'][0]);
            }
            if(!empty($action['date'][1])){
                $action['date'][1]=$this->common->convert_GMT_new ( $action['date'][1]);
            }
            if (isset($action['amount']['amount']) && $action['amount']['amount'] != '') {
                $action['amount']['amount'] = $this->common_model->add_calculate_currency($action['amount']['amount'], "", '', false, false);
            }
            $this->session->set_userdata('cdr_refill_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'reports/refillreport/');
        }
    }

    function refillreport_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('cdr_refill_search', "");
        redirect(base_url() . 'reports/refillreport/');
    }

    function customer_refillreport_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            if (isset($action['amount']['amount']) && $action['amount']['amount'] != '') {
                $action['amount']['amount'] = $this->common_model->add_calculate_currency($action['amount']['amount'], "", '', false, false);
            }
            $this->session->set_userdata('cdr_refill_search', $action);
        }

        redirect(base_url() . 'reports/refillreport/');
    }

    function customer_refillreport_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('cdr_refill_search', "");
        redirect(base_url() . 'reports/refillreport/');
    }

    function customer_refillreport($accountid, $accounttype)
    {
        $json_data = array();
        $instant_search = $this->session->userdata('left_panel_search_' . $accounttype . '_refill');
        $like_str = ! empty($instant_search) ? "(date like '%$instant_search%'
                                            OR  actual_amount like '%$instant_search%'
                                            OR  payment_method like '%$instant_search%'
                                            OR  description like '%$instant_search%'
                                                )" : null;
        if (! empty($like_str))
            $this->db->where($like_str);
        $count_all = $this->reports_model->get_customer_refillreport(false, $accountid);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        if (! empty($like_str))
            $this->db->where($like_str);
        $query = $this->reports_model->get_customer_refillreport(true, $accountid, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $transaction_details = json_decode($value['transaction_details'], true);

            if ($value['payment_method'] == "Paypal") {
                $json_data['rows'][] = array(
                    'cell' => array(
                        $value['date'],
                        $value['actual_amount'],
                        $value['payment_method'],
                        $transaction_details['description']
                    )
                );
            } else {
                $json_data['rows'][] = array(
                    'cell' => array(
                        $value['date'],
                        $value['actual_amount'],
                        $value['payment_method'],
                        $value['description']
                    )
                );
            }
        }
        echo json_encode($json_data);
    }

    function getRealIpAddr()
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    function commission_report_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['accountinfo'] = $this->session->userdata("accountinfo");
        $data['page_title'] = gettext('Commission Report');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->reports_form->build_commissionreports_list_for_admin();
        $data["grid_buttons"] = $this->reports_form->build_grid_buttons_commissionreports();
        $data['form_search'] = $this->form->build_serach_form($this->reports_form->get_commissionreports_search_form());
        $this->load->view('view_commission_reports_list', $data);
    }

    function commission_report_list_json()
    {
        $json_data = array();
        $count_all = $this->reports_model->getcommissionreports_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->reports_model->getcommissionreports_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->reports_form->build_commissionreports_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function commission_report_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $action['table'] = 'commission';
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            if (isset($action['commission']['commission']) && $action['commission']['commission'] != '') {
                $action['commission']['commission'] = $this->common_model->add_calculate_currency($action['commission']['commission'], "", '', false, false);
            }
            $this->session->set_userdata('commissions_report_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'reports/commission_report_list/');
        }
    }

    function commission_report_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function commission_report_export_data_xls()
    {
        $query = $this->reports_model->getcommissionreports_list(true, '0', '10000000');
        ob_clean();
        $outbound_array[] = array(
            gettext("Product Name"),
            gettext("Order ID"),
            gettext("Reseller"),
            gettext("Account"),
            gettext("Amount"),
            gettext("Commission"),
            gettext("Commission (%)"),
            gettext("Status"),
            gettext("Creation Date")
        );
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $outbound_array[] = array(
                    $this->common->get_field_name("name", "products", $row['product_id']),
                    $this->common->get_order_id("order_id", "orders", $row['order_id']),
                    $this->common->get_field_name_coma_new("first_name,last_name,number", "accounts", $row['reseller_id']),
                    $this->common->get_field_name_coma_new("first_name,last_name,number", "accounts", $row['accountid']),

                    $this->common_model->calculate_currency($row['amount'], "", "", true, false),
                    $this->common_model->calculate_currency($row['commission'], "", "", true, false),
                    $row['commission_rate'],
                    $row['commission_status'],
                    $this->common->convert_GMT_to('', '', $row['creation_date'])
                );
            }
        }
        $this->load->helper('csv');
        array_to_csv($outbound_array, 'Commission_Reports_' . date("Y-m-d") . '.csv');
    }

    function reseller_customerlist()
    {
        $add_array = $this->input->post();
        $reseller_id = $add_array['reseller_id'];
        $accountinfo = $this->session->userdata("accountinfo");
        $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : $reseller_id;
        $accounts_result = $this->db->get_where('accounts', array(
            "reseller_id" => $reseller_id,
            "status" => 0,
            "type" => "GLOBAL"
        ));
        if ($accounts_result->num_rows() > 0) {
            $accounts_result_array = $accounts_result->result_array();
            foreach ($accounts_result_array as $key => $value) {
                if(isset($value['company_name']) && $value['company_name'] != ''){
                    echo "<option value=" . $value['id'] . ">" . $value['company_name'] . "(" . $value['number'] . ")</option>";
                }else{
                    echo "<option value=" . $value['id'] . ">" . $value['first_name'] . " " . $value['last_name'] . "(" . $value['number'] . ")</option>";
                }
            }
        } else {
            echo '';
        }

        exit();
    }
}
?>
 
