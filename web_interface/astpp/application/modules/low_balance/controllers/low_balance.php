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
class Low_balance extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library("low_balance_form");
        $this->load->library('astpp/form', 'low_balance_form');
        $this->load->library('astpp/permission');
        $this->load->library('ASTPP_Sms');
        $this->load->model('low_balance_model');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
       
        }

    function low_balance_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['accountinfo'] = $this->session->userdata("accountinfo");
        $data['page_title'] = gettext('Low Balance');
        $data['grid_fields'] = $this->low_balance_form->build_lowbalance_list_for_admin();
        $data['form_search'] = $this->form->build_serach_form($this->low_balance_form->get_search_low_balance_form());
        $this->load->view('view_low_balance', $data);
    }

    function low_balance_list_json()
    {
        $json_data = array();
        $count_all = $this->low_balance_model->getlowbalance_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->low_balance_model->getlowbalance_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->low_balance_form->build_lowbalance_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }
    
    function low_balance_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            unset($_POST['action']);
            unset($_POST['advance_search']);
            $this->session->set_userdata('low_balance_list_search', $this->input->post());
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'low_balance/low_balance_list/');
        }
    }

    function low_balance_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('low_balance_list_search', "");
    }
    function customer_account_change($reseller_id)
    {
        $reseller_id = $reseller_id > 1 ? $reseller_id : '0';
        $type = array (
            "0",
            "1",
            "3" 
        );
        $this->db->where_in('type' , $type);
        $accounts = $this->db_model->getSelect("*", "accounts", array(
            'reseller_id' => $reseller_id,
            'deleted' => 0,
            'status' => 0
        ));
        // echo $this->db->last_query(); exit;
        if ($accounts->num_rows > 0) {
            $accounts_data = $accounts->result_array();
            foreach ($accounts_data as $value) {
                if(isset($value['company_name']) && $value['company_name'] != ''){
                    echo "<option value=" . $value['id'] . ">" . $value['company_name'] . " (".$value['number'].") </option>";    
                }else{
                    echo "<option value=" . $value['id'] . ">" . $value['first_name'] . " " . $value['last_name'] . " (".$value['number'].") </option>";
                }
            }
        } else {
            echo "<select><option value=''>".gettext('--Select--') ."</option></select>";
        }
    }

}

?>
 
