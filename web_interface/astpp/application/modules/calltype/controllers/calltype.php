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
class calltype extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library('calltype_form');
        $this->load->library('astpp/form');
        $this->load->library('astpp/permission');
        $this->load->library('ASTPP_Sms');
        $this->load->model('calltype_model');
        $this->load->library('csvreader');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function calltype_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext(gettext('Call Types'));
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->calltype_form->build_calltype_list_for_admin();
        $data["grid_buttons"] = $this->calltype_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->calltype_form->get_calltype_search_form());
        $this->load->view('view_calltype_list', $data);
    }

    function calltype_list_json()
    {
        $json_data = array();
        $count_all = $this->calltype_model->getcalltype_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->calltype_model->getcalltype_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->calltype_form->build_calltype_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function calltype_add($type = "")
    {
        $data['page_title'] = gettext('Create calltype');
        $data['form'] = $this->form->build_form($this->calltype_form->get_calltype_form_fields(), '');
        $this->load->view('view_calltype_add', $data);
    }

    function calltype_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('calltype_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'calltype/calltype_list/');
        }
    }

    function calltype_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('calltype_list_search', "");
    }

    function calltype_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Calltype');
        $accountinfo = $this->session->userdata("accountinfo");
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $calltype_result = $this->db_model->getSelect("*", " calltype", array(
            'id' => $edit_id
        ));

        if ($calltype_result->num_rows() > 0) {
            $calltype_info = (array) $calltype_result->first_row();
            $data['form'] = $this->form->build_form($this->calltype_form->get_calltype_form_fields($calltype_info['id']), $calltype_info);
            $data['edit_id'] = $calltype_info['id'];
            $this->load->view('view_calltype_add', $data);
        } else {
            redirect(base_url() . 'calltype/calltype_list/');
        }
    }

    function calltype_save()
    {
        $add_array = $this->input->post();

        $data['form'] = $this->form->build_form($this->calltype_form->get_calltype_form_fields($add_array['id']), $add_array);

        if ($add_array['id'] != '') {
            if ($this->form_validation->run() == FALSE) {
                $data['edit_id'] = $add_array['id'];
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {

                if (isset($add_array['call_type'])) {
                    $query = $this->calltype_model->check_unique_call_type('edit', 'call_type', $add_array['call_type']);
                    $result = $query->result_array();
                    if (astpp_count($result) > 0) {
                        if ($result[0]['id'] != $add_array['id'] && $result[0]['call_type'] == $add_array['call_type']) {
                            echo json_encode(array(
                                "call_type_error" => gettext("Calltype already exist in system.")
                            ));
                            exit();
                        }
                    }
                }

                $this->calltype_model->edit_calltype($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => ucfirst($add_array["call_type"]) .' '. gettext("Calltype Updated Successfully!")
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create calltype');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {

                if (isset($add_array['call_type'])) {
                    $query = $this->calltype_model->check_unique_call_type('add', 'call_type', $add_array['call_type']);
                    if (astpp_gt($query, 0)) {
                        echo json_encode(array(
                            "call_type_error" => gettext("Calltype already exist in system.")
                        ));
                        exit();
                    }
                }

                $this->calltype_model->add_calltype($add_array);
                $accountinfo = $this->db_model->getSelect("*", "accounts", array(
                    "status" => 0,
                    "deleted" => 0
                ));
                $accountinfo = $accountinfo->result_array();
                $this->session->set_flashdata('astpp_errormsg', gettext('Calltype Added Successfully!'));
                echo json_encode(array(
                    "SUCCESS" => ucfirst($add_array["call_type"]) . gettext("Calltype Added Successfully!")
                ));
                exit();
            }
        }
    }

    function calltype_delete($id)
    {
        $calltype_detail = $this->db_model->getSelect("*", "calltype", array(
            "id" => $id
        ));
        $calltype_detail = $calltype_detail->result_array();
        $calltype_detail = $calltype_detail[0];
        $accountinfo = $this->db_model->getSelect("*", "accounts", array(
            "pricelist_id" => $calltype_detail['pricelist_id'],
            "status" => 0,
            "deleted" => 0
        ));
        $this->calltype_model->remove_calltype($id);
        $this->session->set_flashdata('astpp_notification', gettext('calltype removed successfully!'));
        foreach ($accountinfo->result_array() as $key => $value) {

            // ------------------------------------------------------------------------------------------------------------
            $last_inserted_id = $this->astpp_sms->send_sms('remove_calltype', $value, '');
            // ------------------------------------------------------------------------------------------------------------
            $value['last_inserted_id'] = $last_inserted_id;
            // ------------------------------------------------------------------------------------------------------------
            $this->common->mail_to_users('remove_calltype', $value);
            // ------------------------------------------------------------------------------------------------------------
            // @ Harsh S changes End Here
            // ------------------------------------------------------------------------------------------------------------
        }
        /**
         * ***************************************
         */
        redirect(base_url() . 'calltype/calltype_list/');
    }

    function calltype_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $update_data = array(
            'status' => '2'
        );
        $this->db->where($where);
        echo $this->db->update('calltype', $update_data);
    }
}

?>
 
