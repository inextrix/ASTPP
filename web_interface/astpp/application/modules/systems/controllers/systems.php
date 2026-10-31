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
class Systems extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->helper('file');
        $this->load->library('session');
        $this->load->library("system_form");
        $this->load->library('astpp/form', "system_form");
        $this->load->library('form_validation');
        $this->load->library('ASTPP_Sms');
        $this->load->model('system_model');
        $this->load->model('Astpp_common');
        $this->load->dbutil();

        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function configuration_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Settings');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "system", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_configuration_form_fields(), $edit_data);
        $this->load->view('view_configuration_add_edit', $data);
    }

    function configuration_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('configuration_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/configuration/');
        }
    }

    function configuration_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('did_search', "");
    }

    function configuration_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->system_form->get_configuration_form_fields(), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Settings');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->system_model->edit_configuration($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext("setting updated successfully!")
                ));
                exit();
            }
        }
    }

    function configuration($group_title = '')
    {
        if ($group_title == "") {
            redirect(base_url() . 'dashboard');
        }
        if ($group_title == "email") {
            $data['test_email_flag'] = true;
        }
        $data['username'] = $this->session->userdata('user_name');
        // ASTPPCOM-982 Ashish start
        if ($group_title == 'payment_methods' || $group_title == 'ported_number' || $group_title == 'term_and_condition' || $group_title == 'voice_broadcast') {
        // ASTPPCOM-982 Ashish End
            $page_title = str_replace("_", " ", $group_title);
            $data['page_title'] = gettext(ucwords($page_title));
        } else {
            $data['page_title'] = gettext(ucfirst($group_title));
        }
        $data['group_title'] = $group_title;
        $data['details'] = $this->system_model->get_subcategory_menu($group_title);

        $add_array = $this->input->post();
        // Only when a welcome file was really uploaded: accept only .wav/.mp3 audio files and
        // never use the client supplied file name as is (the file is stored in the web root).
        if (isset($_FILES['calling_cards_welcome_file']['name']) && $_FILES['calling_cards_welcome_file']['name'] != ''
            && isset($add_array['file_type']) && $add_array['file_type'] == 0) {
            $upload = $_FILES['calling_cards_welcome_file'];
            $file_name = preg_replace('/[^A-Za-z0-9._-]/', '', basename(str_replace(' ', '', (string) $upload['name'])));
            $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($file_name !== '' && in_array($extension, array('wav', 'mp3'), true) && is_uploaded_file($upload['tmp_name'])) {
                $path = getcwd() . "/upload/" . $file_name;
                if (file_exists($path) || move_uploaded_file($upload['tmp_name'], $path)) {
                    $add_array['calling_cards_welcome_file'] = $file_name;
                }
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Only .wav or .mp3 files are allowed.'));
            }
        }
        if (! empty($add_array)) {
            if(isset($add_array['decimal_points'])){
                if( $add_array['decimal_points'] < 0 || is_numeric($add_array['decimal_points'] ) == ''){
                    $add_array['decimal_points'] = '2';
                }elseif($add_array['decimal_points'] < 6){
                    $add_array['decimal_points'] = $add_array['decimal_points'];
                }else{
                    $add_array['decimal_points'] = '5';
                }
            }
            if ($add_array['tax_type'] != '') {
                $selected_tax = implode(",", $add_array['tax_type']);
                $add_array['tax_type'] = $selected_tax;
                if ($add_array['tax_type'] != '0') {
                    $add_array['tax_type'] = str_replace('0,', '', $add_array['tax_type']);
                }
            }
            if (isset($add_array['version'])) {
                unset($add_array['version']);
            }
            foreach ($add_array as $key => $val) {
                $update_array = array(
                    'value' => $val
                );
                $this->system_model->edit_configuration($update_array, $key);
            }
            $this->session->set_flashdata('astpp_errormsg', gettext(sprintf('%s Settings updated sucessfully!', ucfirst($group_title))));

            redirect(base_url() . 'systems/configuration/' . $group_title);
        } else {
            $data['menu'] = $this->system_model->get_system_sidemenue();
            $this->load->view('view_systemconf', $data);
        }
    }

    function configuration_json()
    {
        $json_data = array();
        $count_all = $this->system_model->getsystem_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->getsystem_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_system_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function timezone_list(){
        $base_timezone = Common_model::$global_config['system_config']['default_timezone'];
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Timezone');
        $data['search_flag'] = true;
        $this->session->set_userdata('timezone_search', 0);
        $data['grid_fields'] = $this->system_form->build_timezone_list_for_admin();
        if ($this->session->userdata('logintype') == 2) {
            $data["grid_buttons"] = $this->system_form->build_admin_timezone_grid_buttons();
        } else {
            $data["grid_buttons"] = json_encode(array());
        }
        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_search_timezone_form());

        $timezone_list_session = $this->session->userdata("timezone_list_search");
        $data['timezone_name'] = $timezone_list_session['timezone_name']['timezone_name'];
        $this->load->view('view_timezone_list', $data);
    }
    function timezone_list_json()
    {
        $json_data = array();

        $count_all = $this->system_model->gettimezone_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->gettimezone_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_timezone_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }
    function timezone_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('timezone_list_search', "");
    }
    function timezone_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Timezone');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "timezone", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        if($edit_data['gmtoffset'] >= 0){
            $time = gmdate("H:i", $edit_data['gmtoffset']);
            $edit_data['gmtoffset'] = "+".$time;
        }else{
            $edit_data['gmtoffset'] = str_replace("-","",$edit_data['gmtoffset']);
            $time = gmdate("H:i", $edit_data['gmtoffset']);
            $edit_data['gmtoffset'] = "-".$time;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_timezone_form_fields($edit_id), $edit_data);
        $this->load->view('view_timezone_add_edit', $data);
    }
    function timezone_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);

            $this->session->set_userdata('timezone_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/timezone_list/');
        }
    }
    function timezone_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Add Timezone');
        $data['form'] = $this->form->build_form($this->system_form->get_timezone_form_fields(), '');
        $this->load->view('view_timezone_add_edit', $data);
    }
    function timezone_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->system_form->get_timezone_form_fields($add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Timezone');
            $data['page_title'] = gettext('Edit Timezone');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $time_explode = explode(":",$add_array['gmtoffset']);
                $hours = $time_explode[0] * 3600;
                $minutes = $time_explode[1] * 60;
                $time = $hours + $minutes;
                $add_array['gmtoffset'] = $time;
                $this->system_model->edit_timezone($add_array, $add_array['id']);
                echo json_encode(array(
                "SUCCESS" => $add_array["timezone_name"].' '.gettext('Timezone Updated Successfully!')
                ));
            exit();
            }
        } else {
            $data['page_title'] = gettext('Create Timezone');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $time_explode = explode(":",$add_array['gmtoffset']);
                $hours = $time_explode[0] * 3600;
                $minutes = $time_explode[1] * 60;
                $time = $hours + $minutes;
                $add_array['gmtoffset'] = $time;
                $response = $this->system_model->add_timezone($add_array);
                echo json_encode(array(
                "SUCCESS" => $add_array["timezone_name"].' '.gettext('Timezone Added Successfully!')
            ));
            exit();
            }
        }
    }
    function timezone_remove($id)
    {
        $timezonename = $this->common->get_field_name('timezone_name', 'timezone', $id);
        $this->system_model->remove_timezone($id);
        $this->session->set_flashdata('astpp_notification', $timezonename.' '.gettext('Timezone Removed Successfully!'));

        redirect(base_url() . 'systems/timezone_list/');
    }
    function timezone_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("timezone");
    }

    function template()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Templates');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->system_form->build_template_list_for_admin();
        $data["grid_buttons"] = $this->system_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_template_search_form());
        $this->load->view('view_template_list', $data);
    }

    function template_json()
    {
        $json_data = array();
        $count_all = $this->system_model->gettemplate_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->gettemplate_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_template_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function template_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Email template');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "default_templates", $where);
        $template_words = array();
        foreach ($account->result_array() as $key => $value) {
            $subject = $this->common->getContents($value['subject'], '#', '#');
            $template = $this->common->getContents($value['template'], '#', '#');
            $sms_template = $this->common->getContents($value['sms_template'], '#', '#');
            $edit_data = $value;
        }
        $template_words = array_merge($subject, $template, $sms_template);
        $data['template_words'] = $template_words;
        $data['form'] = $this->form->build_form($this->system_form->get_template_form_fields(), $edit_data);
        $this->load->view('view_template_add_edit', $data);
    }

    function template_save()
    {
        $add_array = $this->input->post();
        $template = preg_replace('<!-- (.|\s)*? -->', '', $add_array['template']);
        $add_array['template'] = str_replace("&lt;", "", str_replace("&gt;", "", $template));
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $reseller = $account_data['id'];
            $this->resellertemplate_save($add_array, $reseller);
        } else {
            $this->admintemplate_save($add_array);
        }
    }

    function resellertemplate_save($data, $resellerid)
    {
        $where = array(
            'name' => $data['name'],
            'reseller_id' => $resellerid
        );
        $count = $this->db_model->countQuery("*", "default_templates", $where);
        $data['form'] = $this->form->build_form($this->system_form->get_template_form_fields(), $data);
        if ($count > 0) {
            $data['page_title'] = gettext('Edit Template');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {

                $this->system_model->edit_resellertemplate($data, $data['id']);
                $this->session->set_flashdata('astpp_errormsg', gettext('Template Updated Successfully!'));
                redirect(base_url() . 'systems/template/');
                exit();
            }
        } else {
            if ($this->form_validation->run() == FALSE) {
                $data['page_title'] = gettext('Edit Template');
                $data['validation_errors'] = validation_errors();
            } else {
                unset($data['form']);
                $data['reseller_id'] = $resellerid;
                $this->system_model->add_resellertemplate($data);
                $this->session->set_flashdata('astpp_errormsg', gettext('Template Added Successfully!'));
                redirect(base_url() . 'systems/template/');
                exit();
            }
        }
        $this->load->view('view_template_add_edit', $data);
    }

    function admintemplate_save($data)
    {
        $data['form'] = $this->form->build_form($this->system_form->get_template_form_fields(), $data);
        if ($data['id'] != '') {
            $data['page_title'] = gettext('Edit Template');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                unset($data['form']);
                unset($data['page_title']);
                $this->system_model->edit_template($data, $data['id']);
                $this->session->set_flashdata('astpp_errormsg', gettext('Template updated successfully!'));
                redirect(base_url() . 'systems/template/');
                exit();
            }
        } else {
            $data['page_title'] = gettext('Termination Details');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                unset($data['form']);
                $this->system_model->add_template($data);
                $this->session->set_flashdata('astpp_errormsg', gettext('Template added successfully!'));
                redirect(base_url() . 'systems/template/');
                exit();
            }
        }
        $this->load->view('view_template_add_edit', $data);
    }

    function template_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('template_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/template/');
        }
    }

    function template_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('did_search', "");
    }

    function country_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Countries');
        $data['search_flag'] = true;
        $data['cur_menu_no'] = 4;
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('country_list_search', '');
        $data['grid_fields'] = $this->system_form->build_country_list_for_admin();
        if ($this->session->userdata('logintype') == 2) {
            $data["grid_buttons"] = $this->system_form->build_admin_grid_buttons();
        } else {
            $data["grid_buttons"] = json_encode(array());
        }
        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_search_country_form());
        $this->load->view('view_country_list', $data);
    }

    function country_list_json()
    {
        $json_data = array();
        $count_all = $this->system_model->getcountry_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->system_model->getcountry_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_country_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function country_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('country_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/country_list/');
        }
    }

    function country_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('country_list_search', "");
    }

    function country_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Add Country');
        $data['form'] = $this->form->build_form($this->system_form->get_country_form_fields(), '');
        $this->load->view('view_country_add_edit', $data);
    }

    function country_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Country');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "countrycode", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_country_form_fields($edit_id), $edit_data);
        $this->load->view('view_country_add_edit', $data);
    }

    function country_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->system_form->get_country_form_fields($add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Country');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->system_model->edit_country($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => $add_array["country"].' '.gettext('Country Updated successfully!')
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Add Country');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $response = $this->system_model->add_country($add_array);
                echo json_encode(array(
                    "SUCCESS" => $add_array["country"].' '.gettext('Country Added successfully!')
                ));
                exit();
            }
        }
    }

    function country_remove($id)
    {
        $this->system_model->remove_country($id);
        $country = $this->common->get_field_name('country', 'countrycode', $id);
        $this->session->set_flashdata('astpp_notification', $country.' '.gettext('Country removed successfully!'));

        redirect(base_url() . 'systems/country_list/');
    }

    function country_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("countrycode");
    }

    function currency_list()
    {
        $base_currency = Common_model::$global_config['system_config']['base_currency'];
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Currencies');
        $data['search_flag'] = true;
        $data['cur_menu_no'] = 4;
        $this->session->set_userdata('currency_search', 0);
        $data['grid_fields'] = $this->system_form->build_currency_list_for_admin();
        if ($this->session->userdata('logintype') == 2) {
            $data["grid_buttons"] = $this->system_form->build_admin_currency_grid_buttons();
        } else {
            $data["grid_buttons"] = json_encode(array());
        }
        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_search_currency_form());
        $this->load->view('view_currency_list', $data);
    }

    function currency_list_json()
    {
        $json_data = array();

        $count_all = $this->system_model->getcurrency_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->getcurrency_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_currency_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function currency_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);

            $this->session->set_userdata('currency_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/currency_list/');
        }
    }

    function currency_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('currency_search', "");
    }

    function currency_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Add Currency');
        $data['form'] = $this->form->build_form($this->system_form->get_currency_form_fields(), '');
        $this->load->view('view_currency_add_edit', $data);
    }

    function currency_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Currency');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "currency", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_currency_form_fields($edit_id), $edit_data);
        $this->load->view('view_country_add_edit', $data);
    }

    function currency_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->system_form->get_currency_form_fields($add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Currency');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->system_model->edit_currency($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" =>  $add_array["currencyname"].' '.gettext('Currency Updated Successfully!')
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Currency');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $response = $this->system_model->add_currency($add_array);
                echo json_encode(array(
                    "SUCCESS" => $add_array["currencyname"].' '.gettext('Currency Added Successfully!')
                ));
                exit();
            }
        }
    }

    function currency_remove($id)
    {
        $currencyname = $this->common->get_field_name('currencyname', 'currency', $id);
        $this->system_model->remove_currency($id);
        $this->session->set_flashdata('astpp_notification', $currencyname.' '.gettext('Currency Removed Successfully!'));

        redirect(base_url() . 'systems/currency_list/');
    }

    function currency_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("currency");
    }

    function database_backup()
    {
        $data = array();
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Database Backup');
        $filename = $this->db->database . "_" . date("YmdHms") . ".sql.gz";
        $data['form'] = $this->form->build_form($this->system_form->get_backup_database_form_fields($filename), '');
        $this->load->view('view_database_backup', $data);
    }

    function database_backup_save()
    {
        $add_array = $this->input->post();

        $data['form'] = $this->form->build_form($this->system_form->get_backup_database_form_fields($add_array['path'], $add_array['id']), $add_array);
        $data['page_title'] = gettext('Database Backup');
        if ($add_array['id'] != '') {} else {
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $db_name = $this->db->database;
                $db_username = $this->db->username;
                $db_password = $this->db->password;
                $db_hostname = $this->db->hostname;
                // The file name was used unquoted in a shell command (command
                // injection, path traversal) and the DB password was visible in `ps`.
                $filename = basename((string) $add_array['path']);
                if (! preg_match('/^[A-Za-z0-9._-]+\.sql(\.gz)?$/', $filename)) {
                    echo gettext('Invalid backup file name. Allowed: letters, digits, ".", "_", "-" and the extension .sql or .sql.gz');
                    exit();
                }
                $add_array['path'] = $filename;
                $backup_file = DATABASE_DIRECTORY . $filename;
                $do_gzip = 0;
                if (substr($backup_file, - 3) == '.gz') {
                    $backup_file = substr($backup_file, 0, - 3);
                    $do_gzip = 1;
                }

                $run_backup = "MYSQL_PWD=" . escapeshellarg($db_password) . " /usr/bin/mysqldump -all -h " . escapeshellarg($db_hostname) . " -u " . escapeshellarg($db_username) . " --databases " . escapeshellarg($db_name) . " > " . escapeshellarg($backup_file);
                $error_zip = 0;
                exec($run_backup, $output, $error);
                if ($do_gzip) {
                    $gzip = $this->config->item('gzip-path');
                    $run_gzip = $gzip . " " . escapeshellarg($backup_file);
                    exec($run_gzip, $output, $error_zip);
                }

                if ($error == 0 && $error_zip == 0) {
                    $this->system_model->backup_insert($add_array);
                    echo json_encode(array(
                        "SUCCESS" => $add_array['backup_name'].' ' .gettext('Backup Exported Successfully!')
                    ));

                    exit();
                } else {
                    echo gettext('An error occur when the system tried to backup of the database. Please check yours system settings for the backup section');
                    exit();
                }
            }
        }
    }

    function database_restore()
    {
        $data['page_title'] = gettext('Database Backup');
        $data['grid_fields'] = $this->system_form->build_backupdastabase_list();
        $data["grid_buttons"] = $this->system_form->build_backupdastabase_buttons();
        $this->load->view('view_database_list', $data);
    }

    function database_restore_json()
    {
        $json_data = array();
        $count_all = $this->system_model->getbackup_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->getbackup_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_backupdastabase_list());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
        exit();
    }

    function database_restore_one($id = '')
    {
        $result = $this->system_model->get_backup_data($id);
        $result_array = $result->result_array();
        if ($result->num_rows() > 0) {
            $db_name = $this->db->database;
            $db_username = $this->db->username;
            $db_password = $this->db->password;
            $db_hostname = $this->db->hostname;
            // Quote every shell argument, no password on the command line
            $path = DATABASE_DIRECTORY . basename((string) $result_array[0]['path']);
            if (file_exists($path)) {
                if (substr($path, - 3) == '.gz') {
                    $GUNZIP_EXE = $this->config->item('gunzip-path');
                    $run_gzip = $GUNZIP_EXE . " < " . escapeshellarg($path) . " | ";
                } else {
                    $run_gzip = "/bin/cat " . escapeshellarg($path) . " | ";
                }
                $MYSQL = "/usr/bin/mysql";
                $run_restore = $run_gzip . "MYSQL_PWD=" . escapeshellarg($db_password) . " " . $MYSQL . " -h " . escapeshellarg($db_hostname) . " -u " . escapeshellarg($db_username) . " " . escapeshellarg($db_name);
                exec($run_restore);
                $this->session->set_flashdata('astpp_errormsg', gettext('Database Restore successfully.'));
                redirect(base_url() . 'systems/database_restore/');
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('File not exists!'));
                redirect(base_url() . 'systems/database_restore/');
            }
        }
        redirect(base_url() . 'systems/database_restore/');
    }

    function database_download($id = '')
    {
        $result = $this->system_model->get_backup_data($id);
        $result_array = $result->result_array();
        if ($result->num_rows() > 0) {
            $path = DATABASE_DIRECTORY . basename((string) $result_array[0]['path']);
            $filename = basename($path);
            $len = filesize($path);

            header("Content-Encoding: binary");
            header("Content-Type: application/octet-stream");
            header("content-length: " . $len);
            header("content-disposition: attachment; filename=" . $filename);
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private");
            header("Pragma: public");
            ob_clean();
            $fp = fopen($path, "r");
            fpassthru($fp);
            exit();
        }
    }

    function database_import()
    {
        $data['page_title'] = gettext('Import Database');
        $this->load->view('view_import_database', $data);
    }

    function database_import_file()
    {
        if ($_POST['fname'] !='' && $_FILES['userfile']['name'] != '' && $_FILES['userfile']['type'] == "application/gzip") {
            $filename_text = $_POST['fname'];
            $upload_greeting_file = $_FILES['userfile']['name'];
            $filename = DATABASE_DIRECTORY . $upload_greeting_file;
            if (file_exists($filename)) {
                $data['error'] = gettext("This file " . basename($_FILES['userfile']['name']) . " is exists.");
                $data['page_title'] = gettext('Import Database');
                $this->load->view('view_import_database', $data);
            } else {
                // The old check accepted e.g. "x.sql.php" and stored it in the
                // web root (current working directory) -> remote code execution.
                $upload_name = basename((string) $upload_greeting_file);
                if (preg_match('/^[A-Za-z0-9._-]+\.(sql|sql\.gz|csv|tar)$/', $upload_name)) {
                    $target_path = DATABASE_DIRECTORY . $upload_name;
                    move_uploaded_file($_FILES["userfile"]["tmp_name"], $target_path);

                    $query = $this->system_model->import_database($filename_text, $_FILES['userfile']['name']);
                    $this->session->set_flashdata('astpp_errormsg', "The file " . basename($_FILES['userfile']['name']) . " has been uploaded");
                    redirect(base_url() . "systems/database_restore/");
                } else {
                    $this->session->set_flashdata('astpp_notification', gettext("There is a some issue or invalid file format."));
                    redirect(base_url() . "systems/database_restore/");
                }
            }
        }else{
            if($_POST['fname'] =='' && $_FILES['userfile']['name'] == ''){
                $data['error'] = gettext("Please enter name and select file.");
            }else if($_POST['fname'] =='' ){
                $data['error'] = gettext("Please enter name.");
            }else if($_FILES['userfile']['name'] == ''){
                $data['error'] = gettext("Please Select File");
            }else{
                $data['error'] = gettext("Please select valid file");
            }
            
            $data['page_title'] = gettext('Import Database');
            $this->load->view('view_import_database', $data);
        }
        
    }

    function database_delete($id)
    {
        $where = array(
            'id' => $id
        );
        $this->db->where($where);
        $this->db->delete("backup_database");

        $this->session->set_flashdata('astpp_errormsg', gettext('Database backup deleted successfully.'));
        redirect(base_url() . 'systems/database_restore/');
        return true;
    }

    function database_backup_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("backup_database");
    }

    function languages_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Languages');
        $data['search_flag'] = true;
        $data['cur_menu_no'] = 4;
        $this->session->set_userdata('languages_search', 0);
        $data['grid_fields'] = $this->system_form->build_languages_list_for_admin();

        if ($this->session->userdata('logintype') == 2) {
            $data["grid_buttons"] = $this->system_form->build_admin_languages_grid_buttons();
        } else {
            $data["grid_buttons"] = json_encode(array());
        }

        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_search_languages_form());
        $this->load->view('view_languages_list', $data);
    }

    function languages_list_json()
    {
        $json_data = array();
        $count_all = $this->system_model->getlanguages_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->getlanguages_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_languages_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function languages_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {

            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('currency_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/currency_list/');
        }
    }

    function languages_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('currency_search', "");
    }
	function languages_export($multiple_ids)
    {
        $multiple_ids=explode('_',$multiple_ids);
        $ids='';
        foreach ($multiple_ids as $key => $id) {
            $ids .=$id.',';
        }
        $ids=rtrim($ids,',');
        if($ids != ''){
            $where = "id IN ($ids)";
            $this->db->where($where);
            $result=$this->db->get("languages")->result_array();
            $select_columns='';
            if(!empty($result)){
                foreach ($result as $key => $value) {
                    $select_columns .= $value['locale'].',';
                    $languagename_selected[] = gettext($value ['name']);
                }
                $select_columns=rtrim($select_columns,',');
                $final_csv_array[] = $languagename_selected;
                $this->db->select($select_columns);
                $translation_query=$this->db->get("translations")->result_array();
                $columns=explode(',',$select_columns);
                if(!empty($translation_query)){
                    foreach ($translation_query as $key => $value) {
                        $data=array();
                        foreach ($columns as $key1 => $row) {
                            $data[]=$value[$row];
                        }
                        $final_csv_array[]=$data;
                    }
                }
                ob_clean();
                $this->load->helper('csv');
                array_to_csv($final_csv_array, 'Languages_' . date("Y-m-d") . '.csv');
            }else{
                redirect(base_url().'systems/languages_list/');
            }
        }else{
            redirect(base_url().'systems/languages_list/');
        }
    }
    function languages_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Languages');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "languages", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_languages_form_fields($edit_data['id']), $edit_data);
        $this->load->view('view_languages_edit', $data);
    }

    function languages_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Add Languages');
        $data['form'] = $this->form->build_form($this->system_form->get_languages_form_fields(), '');
        $this->load->view('view_languages_add_edit', $data);
    }
	function languages_default()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Set Default Language');
        $this->db->where("name", 'default_language');
        $query = $this->db->get("system");
        $language=array();
        if($query->num_rows() > 0){
            $languges_result=(array)$query->first_row();
            $language['name']=$languges_result['value'];
        }
        $data['form'] = $this->form->build_form($this->system_form->get_default_languages_form_fields($language), $language);
        $this->load->view('view_languages_default', $data);
    }
    function languages_set_default(){
        $this->db->where("name", 'default_language');
        $query = $this->db->get("system");
        if($query->num_rows() > 0){
            $this->db->where("name","default_language");
            $this->db->set('value', $this->input->post('name'));
            $this->db->update('system');
        }else{
            $data=array(
                "name"=>"default_language",
                "value"=>$this->input->post('name'), 
                "is_display"=>1 
            );
            $this->db->insert('system', $data);
        }
        echo json_encode(array(
            "SUCCESS" => $this->input->post('name').' '.gettext('Languages updated successfully!')
        ));
        exit();
        
    }
    function languages_remove($id)
    {
        $languagename = $this->common->get_field_name('*', 'languages', $id);
        $this->system_model->remove_languages($id);
        $this->session->set_flashdata('astpp_notification', $languagename.' '.gettext('Languages removed successfully!'));

        redirect(base_url() . 'systems/languages_list/');
    }

    function languages_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $data = explode(',', $ids);

        foreach ($data as $key => $value) {
            $value = str_replace("'", "", $value);
            $new_array[] = $value;
        }
        foreach ($new_array as $key => $value) {
            $query = $this->db->get_where('languages', array(
                'id' => $value
            ));
            $localedata = $query->first_row();
            $localename = $localedata->locale;
            if ($this->db->field_exists($localename, 'translations')) {
                $this->db->query('ALTER TABLE translations DROP `' . $localename . '` ');
            } else {
                redirect(base_url() . 'systems/languages_list/');
                exit();
            }
        }
        $this->db->where($where);
        echo $this->db->delete("languages");
    }

    function languages_save()
    {
        $add_array = $this->input->post();
        $response = array();
        foreach ($add_array as $key => $value) {
            if ($value == 'id' || $value == 'ID' || $value == 'Id' || $value == 'iD') {
                $response[$key . "_error"] = $key . " Field is not valid";
            }
        }
        $data['form'] = $this->form->build_form($this->system_form->get_languages_form_fields($add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Languages');
            if ($this->form_validation->run() == FALSE || ! empty($response)) {
                $data['validation_errors'] = validation_errors();
                if (! empty($response)) {
                    if (! empty($data['validation_errors'])) {
                        $json_decode = json_decode($data['validation_errors'], true);
                        $data['validation_errors'] = json_encode(array_merge($json_decode, $response));
                    } else {
                        $data['validation_errors'] = json_encode($response);
                    }
                }
                echo $data['validation_errors'];
                exit();
            } else {
                $this->system_model->edit_languages($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => $add_array["name"].' '.gettext('Languages updated successfully!')
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Languages');
            if ($this->form_validation->run() == FALSE || ! empty($response)) {
                $data['validation_errors'] = validation_errors();
                if (! empty($response)) {
                    if (! empty($data['validation_errors'])) {
                        $json_decode = json_decode($data['validation_errors'], true);
                        $data['validation_errors'] = json_encode(array_merge($json_decode, $response));
                    } else {
                        $data['validation_errors'] = json_encode($response);
                    }
                }
                echo $data['validation_errors'];
                exit();
            } else {
                $add_array['name'] = str_replace(' ', '_', trim($add_array['name']));
                $add_array['locale'] = str_replace(' ', '_', trim($add_array['locale']));
                $add_array['code'] = str_replace(' ', '_', trim($add_array['code']));
                $response = $this->system_model->add_languages($add_array);
                echo json_encode(array(
                    "SUCCESS" => $add_array["name"].' '.gettext('%s Languages added successfully!')
                ));
                exit();
            }
            $this->load->view('view_languages_add_edit', $data);
        }
    }

    function translation_list()
    {
        $data['accountinfo'] = $this->session->userdata("accountinfo");
        $data['page_title'] = gettext('Translations');
        if (isset($_POST['id']) && ! empty($_POST['id'])) {
            $this->session->set_userdata("opting_translation", $_POST['id']);
            $data['grid_fields'] = $this->system_form->build_translation_list_for_admin_translation();
        } else {
            $data['grid_fields'] = $this->system_form->build_translation_list_for_admin();
            $data["grid_buttons"] = $this->system_form->translation_build_grid_buttons();
        }
        $this->load->view('view_translation_list', $data);
    }

    function translation_list_json()
    {
        $json_data = array();
        $opting_id = $this->session->userdata("opting_process");
        if ($opting_id != '') {
            $count_res = $this->system_model->getproduct_list(false);
            $count_all = (array) $count_res->first_row();
            $paging_data = $this->form->load_grid_config($count_all['count'], $_GET['rp'], $_GET['page']);
            $json_data = $paging_data["json_paging"];
            $query = $this->system_model->getproduct_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
            $grid_fields = json_decode($this->system_form->build_translation_list_for_admin_translation());
            $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
            $this->session->unset_userdata('opting_process');
        } else {
            $count_all = $this->system_model->get_translation_list(false);
            $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
            $json_data = $paging_data["json_paging"];
            $query = $this->system_model->get_translation_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
            $grid_fields = json_decode($this->system_form->build_translation_list_for_admin());
            $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        }
        echo json_encode($json_data);
    }

    function translation_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Translation Language');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "translations", $where);
        foreach ($account->result_array() as $key => $value) {
            $data['edit_data'] = $value;
        }
        $this->load->view('view_translation_edit', $data);
    }

    function translation_add()
    {
        $data['page_title'] = gettext('Create Translation Languages');
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $fields_data = $this->db->list_fields('translations');
        unset($fields_data['0']);
        $this->db->from('languages');
        $query = $this->db->get();
        $query = $query->result_array();
        $this->load->view('view_translation_add', $data);
    }

    function translation_remove($id)
    {
        $translation_info = $this->common->get_field_name('*', 'translations', $id);
        $this->system_model->remove_translation($id);
        $this->session->set_flashdata('astpp_notification', gettext(sprintf('%s Translation removed successfully!', $translation_info)));

        redirect(base_url() . 'systems/translation_list/');
    }

    function translation_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("translations");
    }

    function translation_save()
    {
        $add_array = $this->input->post();
        if (isset($add_array) && count($add_array) > 0) {
            foreach ($add_array as $key => $val) {
                if ($key != "id" && $key != "action") {
                    if ($key == "module_name") {
                        $display_name = str_replace("_", " ", $key);
                        $display_name = ucwords($display_name);
                    } else {
                        $display_name = $this->common->get_field_name('name', 'languages', array(
                            'locale' => $key
                        ));
                    }
                    $this->form_validation->set_rules($key, $display_name, 'required|trim|xss_clean');
                }
            }
        }
        if (isset($add_array['id']) && $add_array['id'] != '') {
            if ($this->form_validation->run() == FALSE) {
                $data['page_title'] = gettext('Edit Translation');
                $data['flag'] = true;
                $data['edit_data'] = $add_array;
                $data['validation_errors'] = validation_errors();
                $this->load->view('view_translation_edit', $data);
            } else {
                $response = $this->system_model->edit_translation($add_array, $add_array['id']);
                $this->session->set_flashdata('astpp_errormsg', ucfirst($add_array["module_name"]) .' '. gettext('Translation updated successfully!'));
                redirect(base_url() . 'systems/translation_list/');
            }
        } else {
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                $data['page_title'] = gettext('Create Translation');
                $data['flag'] = true;
                $data['details'] = $add_array;
                $this->load->view('view_translation_add', $data);
            } else {
                $data['page_title'] = gettext('Create Translation');
                $response = $this->system_model->add_translation($add_array);
                $this->session->set_flashdata('astpp_errormsg', ucfirst($add_array["module_name"]) .' '. gettext('Translation added successfully!'));
                redirect(base_url() . 'systems/translation_list/');
            }
        }
    }

    function translation_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('translation_list', "");
    }

    function payment_methods()
    {
        $group_title = 'payment_methods';
        if ($group_title == "") {
            redirect(base_url() . '/dashboard');
        }
        if ($group_title == "email") {
            $data['test_email_flag'] = true;
        }
        $data['username'] = $this->session->userdata('user_name');
        if ($group_title == 'payment_methods') {
            $page_title = str_replace("_", " ", $group_title);
            $data['page_title'] = ucwords($page_title);
        } else {
            $data['page_title'] = gettext(ucfirst($group_title));
        }
        $data['group_title'] = $group_title;

        $data['details'] = $this->system_model->get_subcategory_menu($group_title);
        $add_array = $this->input->post();
        if (! empty($add_array)) {
            if ($add_array['tax_type'] != '') {
                $selected_tax = implode(",", $add_array['tax_type']);
                $add_array['tax_type'] = $selected_tax;
            }
            if (isset($add_array['version'])) {
                unset($add_array['version']);
            }

            foreach ($add_array as $key => $val) {
                $update_array = array(
                    'value' => $val
                );

                $this->system_model->edit_configuration($update_array, $key);
            }
            $this->session->set_flashdata('astpp_errormsg', ucfirst($group_title).' '.gettext('Settings updated sucessfully!'));

            redirect(base_url() . 'systems/configuration/' . $group_title);
        } else {
            $data['menu'] = $this->system_model->get_system_sidemenue();
            $this->load->view('view_systemconf', $data);
        }
    }
    // ASTPPCOM-975 Start
    function systems_list()
    {
        $this->load->view('system_enterprise_license');
    }
    // ASTPPCOM-975 END

}

