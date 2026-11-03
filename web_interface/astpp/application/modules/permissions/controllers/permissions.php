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
class Permissions extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('astpp/permission');
        $this->load->library('session');
        $this->load->library("permissions_form");
        $this->load->library('ASTPP_Sms');
        $this->load->model('permissions_model');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function permissions_login_type_change()
    {
        $post_data = $this->input->post();
        if ($post_data['login_type'] != '') {
            $this->session->set_userdata('add_permission_login_session', $post_data['login_type']);
            $this->session->set_userdata('add_permission_role_name', $post_data['role_name']);
            $this->session->set_userdata('add_permission_description', $post_data['description']);
        }
        echo true;
        exit();
    }

    function permissions_list()
    {
        $this->load->library('astpp/form');
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Roles & Permissions');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->permissions_form->build_permissions_list_for_admin();
        $data["grid_buttons"] = $this->permissions_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->permissions_form->get_permissions_search_form());
        // print_r($data);exit;
        $this->load->view('view_permissions_list', $data);
    }

    function permissions_list_json()
    {
        $this->load->library('astpp/form');
        $json_data = array();
        $count_all = $this->permissions_model->getpermissions_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->permissions_model->getpermissions_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->permissions_form->build_permissions_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function permissions_add($type = "")
    {
        $this->load->library('astpp/form');
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['login_type'] = '';
        $data['role_name'] = '';
        $data['description'] = '';
        $login_type = 0;
        $login_type_session = $this->session->userdata('add_permission_login_session');
        if (isset($login_type_session) and $login_type_session != '') {
            $login_type = $login_type_session;
            $data['login_type'] = $login_type_session;
            $data['role_name'] = $this->session->userdata('add_permission_role_name');
            $data['description'] = $this->session->userdata('add_permission_description');
            $this->session->unset_userdata('add_permission_login_session');
            $this->session->unset_userdata('add_permission_role_name');
            $this->session->unset_userdata('add_permission_description');
        }
        $roles_and_permission_array = $this->db_model->select("*", "roles_and_permission", array(
            'login_type' => $login_type,
            'status' => 0
        ), "priority", "ASC", "", "")->result_array();
        $permission_array = array();
        $display_name_array = array();
        if (! empty($roles_and_permission_array)) {
            foreach ($roles_and_permission_array as $key => $value) {
                $permission_array[$value['menu_name']][$value['module_name']][$value['module_url']] = json_decode($value['permissions'], true);
                $display_name_array[$value['menu_name']][$value['module_name']][$value['module_url']] = $value['display_name'];
            }
        }
        $data['page_title'] = gettext('Create Roles & Permissions');
        $data['display_name_array'] = $display_name_array;
        $data['permission_main_array'] = $permission_array;
        $this->load->view('view_permissions_add', $data);
    }

    function permissions_edit($edit_id = '')
    {
        $this->load->library('astpp/form');
        $data['page_title'] = gettext('Edit Roles & Permissions');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "permissions", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $permission_decode = json_decode($edit_data['permissions'], true);
        $data['permission_result'] = $permission_decode;
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $edit_decode = json_decode($edit_data['edit_permissions'], true);
        $data['edit_result'] = $edit_decode;
        $data['name'] = $edit_data['name'];
        $data['description'] = $edit_data['description'];
        $data['login_type'] = $edit_data['login_type'];
        $data['id'] = $edit_data['id'];
        $login_type = $edit_data['login_type'];
        $roles_and_permission_array = $this->db_model->select("*", "roles_and_permission", array(
            'login_type' => $login_type,
            'status' => 0
        ), "priority", "ASC", "", "")->result_array();
        $permission_array = array();
        $display_name_array = array();
        if (! empty($roles_and_permission_array)) {
            foreach ($roles_and_permission_array as $key => $value) {
                $permission_array[$value['menu_name']][$value['module_name']][$value['module_url']] = json_decode($value['permissions'], true);
                $display_name_array[$value['menu_name']][$value['module_name']][$value['module_url']] = $value['display_name'];
            }
        }

        $data['display_name_array'] = $display_name_array;
        $data['permission_main_array'] = $permission_array;
        $this->load->view('view_permissions_edit', $data);
    }

    function permissions_save()
    {
        $this->load->library('astpp/form');
        $add_array = $this->input->post();
        if ($add_array['id'] != '') {
            $this->permissions_model->edit_permissions($add_array, $add_array['id']);
        } else {
            $this->permissions_model->add_permissions($add_array);
        }
        redirect(base_url() . 'permissions/permissions_list/');
    }

    function permissions_list_search()
    {
        $this->load->library('astpp/form');
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('permissions_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'permissions/permissions_list/');
        }
    }

    function permissions_list_clearsearchfilter()
    {
        $this->load->library('astpp/form');
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function permissions_remove($id)
    {
        $this->load->library('astpp/form');
        $this->permissions_model->remove_permissions($id);
        $this->session->set_flashdata('astpp_notification', gettext('Permissions removed successfully!'));
        redirect(base_url() . 'permissions/permissions_list/');
    }

    function permissions_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->delete("permissions", $where);
        echo TRUE;
    }
}

?>
 
