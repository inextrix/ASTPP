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
class Activity_report extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library("activity_report_form");
        $this->load->library('astpp/form', 'activity_report_form');
        $this->load->library('astpp/permission');
        $this->load->library('ASTPP_Sms');
        $this->load->model('activity_report_model');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
        }

        function activityReport(){
            $data['username'] = $this->session->userdata('user_name');
            $data['page_title'] = gettext('Call Activity Report');
            $data['search_flag'] = true;
            $data['report_flag'] = true;
            $data['active'] = 0;
            $this->session->set_userdata('advance_search', 0);
            $data["grid_buttons"] = $this->activity_report_form->build_grid_activity_report_buttons();
            $data['grid_fields'] = $this->activity_report_form->build_customer_report_list_for_admin();
            $data['form_search'] = $this->form->build_serach_form($this->activity_report_form->get_customer_report_form());     
            $this->load->view('view_customer_report_list', $data);
        }
    
        function activityReport_json() {
            $json_data = array();
            $count_all = $this->activity_report_model->getcustomer_report_list(false);
            $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
            $json_data = $paging_data["json_paging"];
            $query = $this->activity_report_model->getcustomer_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
            $grid_fields = json_decode($this->activity_report_form->build_customer_report_list_for_admin());
            $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
            echo json_encode($json_data);
        }
        function activityReport_search() {
            $ajax_search = $this->input->post('ajax_search', 0);
            if ($this->input->post('advance_search', TRUE) == 1) {
                $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
                $action = $this->input->post();
                unset($action['action']);
                unset($action['advance_search']);
                // Kinjal Issue no 2362 Call activity report - Search - Searching is not working with date and time. 
                if(!empty($action['last_did_call_time'][0])){
                    $action['last_did_call_time'][0]=$this->common->convert_GMT_new ( $action['last_did_call_time'][0]);
                }
                if(!empty($action['last_did_call_time'][1])){
                    $action['last_did_call_time'][1]=$this->common->convert_GMT_new ( $action['last_did_call_time'][1]);
                }
                if(!empty($action['last_outbound_call_time'][0])){
                    $action['last_outbound_call_time'][0]=$this->common->convert_GMT_new ( $action['last_outbound_call_time'][0]);
                }
                if(!empty($action['last_outbound_call_time'][1])){
                    $action['last_outbound_call_time'][1]=$this->common->convert_GMT_new ( $action['last_outbound_call_time'][1]);
                }
                // END
                $this->session->set_userdata('activity_search', $action);
            }
            if (@$ajax_search != 1) {
                redirect(base_url() . 'reports/activityReport/');
            }
        }
    
        function activityReport_clearsearchfilter() {
            $this->session->set_userdata('advance_search', 0);
            $this->session->set_userdata('activity_search', "");
        }
        function activityReport_export()
        {
            $account_info = $accountinfo = $this->session->userdata('accountinfo');
            $query = $this->activity_report_model->getcustomer_report_list(true, '', '', false);
            $outbound_array = array();
            ob_clean();
            $outbound_array[] = array(
                gettext("Account"),
                gettext("Reseller"),
                gettext("Last DID Call Date"),
                gettext("Last Outbound Call Date"),
                gettext("Balance"),

               //rupa Report - Activity report - Export header (Column) is displaying variable instead of full name of each field
                gettext("Credit limit")
                //End
            );
            if ($query->num_rows() > 0) {
    
                foreach ($query->result_array() as $row) {
                    $outbound_array[] = array(
                        $this->common->build_concat_string("first_name,last_name,number", "accounts", $row['accountid']),
                        $this->common->reseller_select_value("first_name,last_name,number", "accounts", $row['reseller_id']),
                        $row['last_did_call_time'] = $this->common->convert_GMT_to("", "", $row["last_did_call_time"]),
                        $row['last_outbound_call_time'] = $this->common->convert_GMT_to("", "", $row["last_outbound_call_time"]),
                        $this->common->get_field_name_balance("balance", "accounts", $row['accountid_balance']),
                        $this->common->get_field_name_balance("credit_limit", "accounts", $row['accountid_creditlimit']),
                    );
                }
            }
            $this->load->helper('csv');
            array_to_csv($outbound_array, 'Activity_Report' . date("Y-m-d") . '.csv');
        }
}

?>
 
