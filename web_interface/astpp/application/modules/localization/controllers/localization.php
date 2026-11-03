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
class Localization extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library('localization_form');
        $this->load->library('astpp/form');
        $this->load->model('common_model');
        $this->load->helper('form');
        $this->load->model('localization_model');
        $this->load->library('astpp/permission');
        $this->load->library('ASTPP_Sms');
        $this->load->model('Astpp_common');
        $this->protected_pages = array(
            'localization_list'
        );
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/login/login');
    }

    function localization_list()
    {
        $data['page_title'] = gettext('Localizations');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->localization_form->build_localization_list_for_admin();
        $data["grid_buttons"] = $this->localization_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->localization_form->get_localization_form_search());
        $this->load->view('view_localization_list', $data);
    }

    function localization_list_json()
    {
        $json_data = array();
        $count_all = $this->localization_model->get_localization_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->localization_model->get_localization_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->localization_form->build_localization_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function localization_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('localization_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'localization/localization_list/');
        }
    }

    function localization_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('localization_list_search', "");
    }

    function localization_add()
    {
        $data['page_title'] = gettext('Create Localization');
        $query = $this->db_model->getSelect('id,country', 'countrycode', "");
        if ($query->num_rows() > 0) {
            $data['country_drp'] = $query->result_array();
        }
        $data['fieldsets'] = gettext('Create Localization');
        $this->load->view('view_location_add_edit', $data);
    }

    function localization_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Localization');
        $where = array(
            'id' => $edit_id
        );
        $query = $this->db_model->getSelect('id,country', 'countrycode', "");
        if ($query->num_rows() > 0) {
            $data['country_drp'] = $query->result_array();
        }
        $data['edit'] = true;
        $localization = $this->db_model->getSelect("*", "localization", $where);
        foreach ($localization->result_array() as $value) {
            $edit_data = $value;
        }
        $edit_data['number_originate'] = str_replace('"', "", $edit_data['number_originate']);
        $number_originate = explode(',', $edit_data['number_originate']);
        foreach ($number_originate as $key => $val) {
            $number_originate_explode[] = explode('/', $val);
        }
        $data['details']['number_originate'] = $number_originate_explode;
        $edit_data['in_caller_id_originate'] = str_replace('"', " ", $edit_data['in_caller_id_originate']);
        $in_caller_id_originate = explode(',', $edit_data['in_caller_id_originate']);
        foreach ($in_caller_id_originate as $key => $val) {
            $in_caller_id_originate_explode[] = explode('/', $val);
        }
        $data['details']['in_caller_id_originate'] = $in_caller_id_originate_explode;

        $edit_data['out_caller_id_originate'] = str_replace('"', " ", $edit_data['out_caller_id_originate']);
        $out_caller_id_originate = explode(',', $edit_data['out_caller_id_originate']);
        foreach ($out_caller_id_originate as $key => $val) {
            $out_caller_id_originate_explode[] = explode('/', $val);
        }
        $data['details']['out_caller_id_originate'] = $out_caller_id_originate_explode;

        $edit_data['number_terminate'] = str_replace('"', " ", $edit_data['number_terminate']);
        $number_terminate = explode(',', $edit_data['number_terminate']);
        foreach ($number_terminate as $key => $val) {
            $number_terminate_explode[] = explode('/', $val);
        }
        $data['details']['number_terminate'] = $number_terminate_explode;

        $edit_data['in_caller_id_terminate'] = str_replace('"', " ", $edit_data['in_caller_id_terminate']);
        $in_caller_id_terminate = explode(',', $edit_data['in_caller_id_terminate']);
        foreach ($in_caller_id_terminate as $key => $val) {
            $in_caller_id_terminate_explode[] = explode('/', $val);
        }
        $data['details']['in_caller_id_terminate'] = $in_caller_id_terminate_explode;

        $edit_data['out_caller_id_terminate'] = str_replace('"', " ", $edit_data['out_caller_id_terminate']);
        $out_caller_id_terminate = explode(',', $edit_data['out_caller_id_terminate']);
        foreach ($out_caller_id_terminate as $key => $val) {
            $out_caller_id_terminate_explode[] = explode('/', $val);
        }
        $data['details']['out_caller_id_terminate'] = $out_caller_id_terminate_explode;
        $data['details']['id'] = $edit_data['id'];
        $data['details']['name'] = $edit_data['name'];
        $data['details']['type'] = $edit_data['type'];
        $data['details']['status'] = $edit_data['status'];
        $data['details']['country_id'] = $edit_data['country_id'];
        $data['fieldsets'] = gettext('Edit Localization');
        $this->load->view('view_location_add_edit', $data);
    }

    function localization_save()
    {
        $add_array = $this->input->post();
        $data['details'] = $add_array;
        $query = $this->db_model->getSelect('id,country', 'countrycode', array());
        if ($query->num_rows() > 0) {
            $data['country_drp'] = $query->result_array();
        }
        $is_unique = '';
        if (isset($data['details']['name']) && $data['details']['name'] != "") {
            if (isset($data['details']['id']) != "") {
                $query = $this->common_model->check_unique_data('edit', 'name', $data['details']['name'], 'localization');
                $result = $query->result_array();
                if (astpp_count($result) > 0) {
                    if ($result[0]['id'] != $data['details']['id'] && $result[0]['name'] == $data['details']['name']) {
                        $is_unique = '|is_unique[localization.name]';
                    }
                } else {
                    $is_unique = '';
                }
            } else {
                $query = $this->common_model->check_unique_data('add', 'name', $data['details']['name'], 'localization');
                if (astpp_gt($query, 0)) {
                    $is_unique = '|is_unique[localization.name]';
                } else {
                    $is_unique = '';
                }
            }
        }
        $this->form_validation->set_rules('name', 'Name', 'required|trim|xss_clean' . $is_unique);
        $number_originate = '';
        $in_caller_id_originate = '';
        $out_caller_id_originate = '';
        $number_terminate = '';
        $in_caller_id_terminate = '';
        $out_caller_id_terminate = '';

        if ($add_array['id'] == "") {
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                $data['page_title'] = gettext('Create Localization');
                $data['fieldsets'] = gettext('Create Localization');
                $this->load->view('view_location_add_edit', $data);
            } else {
                if (isset($add_array['number_originate'])) {
                    foreach ($add_array['number_originate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $number_originate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($number_originate != "") {
                        if (substr($number_originate, - 1) == ',') {
                            $number_originate = substr($number_originate, 0, - 1);
                        }
                        $add_array['number_originate'] = '"' . trim($number_originate) . '"';
                    } else {
                        $add_array['number_originate'] = "";
                    }
                }
                if (isset($add_array['in_caller_id_originate'])) {
                    foreach ($add_array['in_caller_id_originate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $in_caller_id_originate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($in_caller_id_originate != "") {
                        if (substr($in_caller_id_originate, - 1) == ',') {
                            $in_caller_id_originate = substr($in_caller_id_originate, 0, - 1);
                        }
                        $add_array['in_caller_id_originate'] = '"' . trim($in_caller_id_originate) . '"';
                    } else {
                        $add_array['in_caller_id_originate'] = "";
                    }
                }
                if (isset($add_array['out_caller_id_originate'])) {
                    foreach ($add_array['out_caller_id_originate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $out_caller_id_originate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($out_caller_id_originate != "") {
                        if (substr($out_caller_id_originate, - 1) == ',') {
                            $out_caller_id_originate = substr($out_caller_id_originate, 0, - 1);
                        }
                        $add_array['out_caller_id_originate'] = '"' . trim($out_caller_id_originate) . '"';
                    } else {
                        $add_array['out_caller_id_originate'] = "";
                    }
                }

                if (isset($add_array['out_caller_id_terminate'])) {
                    foreach ($add_array['out_caller_id_terminate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $out_caller_id_terminate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($out_caller_id_terminate != "") {
                        if (substr($out_caller_id_terminate, - 1) == ',') {
                            $out_caller_id_terminate = substr($out_caller_id_terminate, 0, - 1);
                        }
                        $add_array['out_caller_id_terminate'] = '"' . trim($out_caller_id_terminate) . '"';
                    } else {
                        $add_array['out_caller_id_terminate'] = "";
                    }
                }

                if (isset($add_array['number_terminate'])) {
                    foreach ($add_array['number_terminate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $number_terminate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($number_terminate != "") {
                        if (substr($number_terminate, - 1) == ',') {
                            $number_terminate = substr($number_terminate, 0, - 1);
                        }

                        $add_array['number_terminate'] = '"' . trim($number_terminate) . '"';
                    } else {
                        $add_array['number_terminate'] = "";
                    }
                }

                $add_array['creation_date'] = gmdate('Y-m-d H:i:s');
                $add_array['modified_date'] = gmdate('Y-m-d H:i:s');
                $this->localization_model->insert_localization($add_array);
                $this->session->set_flashdata('astpp_errormsg', ucfirst($add_array['name'].' '.gettext('Localization Added Successfully!')));

                redirect(base_url() . 'localization/localization_list/');
                exit();
            }
        } else {
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                $data['page_title'] = gettext('Create Localization');
                $data['fieldsets'] = gettext('Create Localization');
                $this->load->view('view_location_add_edit', $data);
            } else {
                if (isset($add_array['number_originate'])) {
                    foreach ($add_array['number_originate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $number_originate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($number_originate != "") {
                        if (substr($number_originate, - 1) == ',') {
                            $number_originate = substr($number_originate, 0, - 1);
                        }
                        $add_array['number_originate'] = '"' . trim($number_originate) . '"';
                    } else {
                        $add_array['number_originate'] = "";
                    }
                }
                if (isset($add_array['in_caller_id_originate'])) {
                    foreach ($add_array['in_caller_id_originate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $in_caller_id_originate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($in_caller_id_originate != "") {
                        if (substr($in_caller_id_originate, - 1) == ',') {
                            $in_caller_id_originate = substr($in_caller_id_originate, 0, - 1);
                        }
                        $add_array['in_caller_id_originate'] = '"' . trim($in_caller_id_originate) . '"';
                    } else {
                        $add_array['in_caller_id_originate'] = "";
                    }
                }
                if (isset($add_array['out_caller_id_originate'])) {
                    foreach ($add_array['out_caller_id_originate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $out_caller_id_originate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($out_caller_id_originate != "") {
                        if (substr($out_caller_id_originate, - 1) == ',') {
                            $out_caller_id_originate = substr($out_caller_id_originate, 0, - 1);
                        }
                        $add_array['out_caller_id_originate'] = '"' . trim($out_caller_id_originate) . '"';
                    } else {
                        $add_array['out_caller_id_originate'] = "";
                    }
                }

                if (isset($add_array['out_caller_id_terminate'])) {
                    foreach ($add_array['out_caller_id_terminate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $out_caller_id_terminate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($out_caller_id_terminate != "") {
                        if (substr($out_caller_id_terminate, - 1) == ',') {
                            $out_caller_id_terminate = substr($out_caller_id_terminate, 0, - 1);
                        }
                        $add_array['out_caller_id_terminate'] = '"' . trim($out_caller_id_terminate) . '"';
                    } else {
                        $add_array['out_caller_id_terminate'] = "";
                    }
                }

                if (isset($add_array['number_terminate'])) {
                    foreach ($add_array['number_terminate'] as $key => $val) {
                        if ($val[0] != "" || $val[1] != "") {
                            $val[0] = str_replace(' ', '', $val[0]);
                            $val[1] = str_replace(' ', '', $val[1]);
                            $number_terminate .= trim($val[0]) . "/" . trim($val[1]) . ",";
                        }
                    }
                    if ($number_terminate != "") {
                        if (substr($number_terminate, - 1) == ',') {
                            $number_terminate = substr($number_terminate, 0, - 1);
                        }

                        $add_array['number_terminate'] = '"' . trim($number_terminate) . '"';
                    } else {
                        $add_array['number_terminate'] = "";
                    }
                }

                $add_array['modified_date'] = gmdate('Y-m-d H:i:s');
                $this->localization_model->edit_localization($add_array, $add_array['id']);
                $this->session->set_flashdata('astpp_errormsg', ucfirst($add_array['name']).' '. gettext('Localization Updated Successfully!'));
                redirect(base_url() . 'localization/localization_list/');
                exit();
            }
        }
    }

    function localization_delete($id)
    {
        $this->localization_model->remove_localization($id);
        $this->session->set_flashdata('astpp_notification', gettext('Localization Removed Successfully!'));
        redirect(base_url() . 'localization/localization_list/');
    }

    function localization_multiple_delete()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN (" . $ids . ")";
        $this->db->where($where);
        echo $this->db->delete("localization");
    }

    function localization_check_global_type($type = '')
    {
        if (isset($type)) {
            $query = $this->db_model->getSelect('type', 'localization', array(
                'type' => $type
            ));
            if ($query->num_rows() > 0) {
                echo gettext("Globalization is already exist in this system");
                exit();
            }
        }
    }
}
?>
 
