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
class Cronsettings extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library('cronsettings_form');
        $this->load->library('astpp/form');
        $this->load->model('common_model');
        $this->load->helper('form');
        $this->load->model('cronsettings_model');
        $this->load->library('astpp/permission');
        $this->load->library('ASTPP_Sms');
        $this->load->model('Astpp_common');
        $this->protected_pages = array(
            'cronsettings_list'
        );
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/login/login');
    }

    function cronsettings_list()
    {
        $data['page_title'] = gettext('Crons');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->cronsettings_form->build_cron_list_for_admin();
        $data["grid_buttons"] = $this->cronsettings_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->cronsettings_form->get_search_cron_form());
        $this->load->view('view_cronsettings_list', $data);
    }

    function cronsettings_list_json()
    {
        $json_data = array();
        $count_all = $this->cronsettings_model->get_cron_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->cronsettings_model->get_cron_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->cronsettings_form->build_cron_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function cronsettings_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('cronsettings_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'cronsettings/cronsettings_list/');
        }
    }

    function cronsettings_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('cronsettings_list_search', "");
    }

    function cronsettings_add()
    {
        $data['page_title'] = gettext('Create Cron Settings');
        $data['form'] = $this->form->build_form($this->cronsettings_form->get_cronsettings_form_fields('', ''), '');
        $this->load->view('view_cronsettings_add_edit', $data);
    }

    function cronsettings_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Cron Settings');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "cron_settings", $where);
        foreach ($account->result_array() as $value) {
            $edit_data = $value;
        }
        $data['next_execution_date'] = $this->common->convert_GMT_to($edit_data['next_execution_date'], $edit_data['next_execution_date'], $edit_data['next_execution_date']);
        $data['form'] = $this->form->build_form($this->cronsettings_form->get_cronsettings_form_fields($edit_id), $edit_data);
        $this->load->view('view_cronsettings_add_edit', $data);
    }

    function cronsettings_delete($id)
    {
        $this->cronsettings_model->remove_cron($id);
        $this->session->set_flashdata('astpp_notification', gettext('Cron Setting Removed Successfully!'));
        redirect(base_url() . 'cronsettings/cronsettings_list/');
    }

    function cronsettings_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->cronsettings_form->get_cronsettings_form_fields($add_array['id'], ''), $add_array);
        $accountid = isset($add_array['accountid']) && astpp_gt($add_array['accountid'], 0) ? $add_array['accountid'] : '';

        if ($add_array['id'] != '') {

            $data['page_title'] = gettext('Edit Cron Settings');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $no_of_rows = $this->cronsettings_model->check_unique_name_for_edit($add_array['name']);
                $result = $no_of_rows->result_array();
                if ($result[0]['id'] != $add_array['id'] && $result[0]['name'] == $add_array['name']) {
                    echo json_encode(array(
                        "name_error" => gettext("Name already exist in system.")
                    ));
                    exit();
                } else {
                    $this->db->where('id', $add_array['id']);
                    $did_info = (array) $this->db->get('cron_settings')->first_row();
                    unset($add_array['last_execution_date']);  
                    $this->cronsettings_model->edit_cron($add_array, $add_array['id']);
                    echo json_encode(array(
                        "SUCCESS" => $add_array['name'] . gettext("Updated Successfully!")
                    ));
                    exit();
                }
            }
        } else {
            $data['page_title'] = gettext('Create Cron Settings');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $check_cron_name = $this->cronsettings_model->check_unique_name($add_array['name']);
                if (astpp_gt($check_cron_name, 0)) {
                    echo json_encode(array(
                        "name_error" => gettext("Name already exist in system.")
                    ));
                    exit();
                }
                $add_array['creation_date'] = gmdate('Y-m-d H:i:s');
                $this->cronsettings_model->add_cron($add_array);
                echo json_encode(array(
                    "SUCCESS" => $add_array["name"] . gettext("Added Successfully!")
                ));
                exit();
            }
        }
    }

    function cronsettings_multiple_delete()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN (" . $ids . ")";
        $this->db->where($where);
        echo $this->db->delete("cron_settings");
    }
}
?>
 
