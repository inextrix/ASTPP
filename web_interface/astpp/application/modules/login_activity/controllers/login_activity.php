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
class Login_activity extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('login_activity_form');
        $this->load->library('astpp/form');
        $this->load->library('ASTPP_Sms');
        $this->load->model('common_model');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->model('login_activity_model');
        $this->load->model('Astpp_common');
        $this->load->library('astpp/permission');
        $this->protected_pages = array(
            'audit_list'
        );
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/login/login');
        $accountinfo = $this->session->userdata('accountinfo');
        if($accountinfo['type'] != '-1' && $accountinfo['type'] != '2'){
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url().'dashboard/'); 
            exit();
        }
    }

    function login_activity_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Login Activity Report');
        $data['search_flag'] = true;
        $data['report_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->login_activity_form->build_login_activity_list_for_admin();
        $data["grid_buttons"] = $this->login_activity_form->build_grid_buttons_admin();
        $data['form_search'] = $this->form->build_serach_form($this->login_activity_form->get_search_login_activity_form());
        $this->load->view('view_login_activity_list', $data);
    }

    function login_activity_list_json()
    {
        $json_data = array();
        $count_all = $this->login_activity_model->get_login_activity_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->login_activity_model->get_login_activity_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->login_activity_form->build_login_activity_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function login_activity_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            if (isset($action['timestamp'][0]) && $action['timestamp'][0] != "") {
                $action['timestamp'][0] = $this->common->convert_GMT_new($action['timestamp'][0]);
            }
            if (isset($action['timestamp'][1]) && $action['timestamp'][1] != '') {
                $action['timestamp'][1] = $this->common->convert_GMT_new($action['timestamp'][1]);
            }
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('login_activity_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'login_activity/login_activity_list/');
        }
    }

    function login_activity_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->unset_userdata('audit_list_search');
    }
}
?>
 
