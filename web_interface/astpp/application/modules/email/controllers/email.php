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
class Email extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library('email_form');
        $this->load->library('astpp/form', 'email_form');
        $this->load->library('astpp/permission');
        $this->load->model('email_model');
        $this->load->library('csvreader');
        $this->load->library('astpp/email_lib');
        $this->load->library('ASTPP_Sms');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function email_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Email List');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $reseller = $account_data['id'];
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller
            );
        } else {
            $where = array(
                'id' => $edit_id
            );
        }
        $account = $this->db_model->getSelect("*", "mail_details", $where);
        if ($account->num_rows() > 0) {
            foreach ($account->result_array() as $key => $value) {
                $edit_data = $value;
            }
            $data['form'] = $this->form->build_form($this->email_form->get_form_fields_email(), $edit_data);
            $this->load->view('view_email_add_edit', $data);
        } else {
            redirect(base_url() . 'email/email_history_list/');
        }
        redirect(base_url() . 'email/email_history_list/');
    }

    function email_resend()
    {
        $add_array = $this->input->post();
        $add_array = $this->db_model->getSelect("*", 'mail_details', array(
            'id' => $add_array['id']
        ));
        $add_array = $add_array->result_array();
        $add_array = $add_array[0];
        $data['page_title'] = gettext('Resand Email');
        $where = array(
            'id' => $add_array['id']
        );
        $account = $this->db_model->getSelect("*", "mail_details", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $add_array = array(
            'accountid' => $edit_data['accountid'],
            'subject' => $add_array['subject'],
            'body' => $add_array['body'],
            'from' => $edit_data['from'],
            'to' => $edit_data['to'],
            'status' => $edit_data['status'],
            'template' => $edit_data['template'],
            'sms_body' => $edit_data['sms_body'],
            'to_number' => $edit_data['to_number'],
            'attachment' => $edit_data['attachment']
        );
        $this->email_re_send($add_array);
        $this->session->set_flashdata('astpp_errormsg', gettext('Email resend successfully!'));
        redirect(base_url() . 'email/email_history_list/');
    }

    function email_resend_edit($edit_id = '')
    {
        $this->permission->check_web_record_permission($edit_id, 'mail_details', "email/email_history_list/");
        $data['page_title'] = gettext('Resend Email');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "mail_details", $where);
        if ($account->num_rows() > 0) {
            foreach ($account->result_array() as $key => $value) {
                $edit_data = $value;
            }
            $data['maildata'] = $edit_data['attachment'];
            $data['form'] = $this->form->build_form($this->email_form->get_form_fields_email_edit(), $edit_data);
            $this->load->view('view_email_add_edit', $data);
        } else {
            redirect(base_url() . 'email/email_history_list/');
        }
    }

    function email_resend_edit_customer($edit_id = '')
    {
        $data['page_title'] = gettext('Resent Email');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "mail_details", $where);
        if ($account->num_rows() > 0) {
            foreach ($account->result_array() as $key => $value) {
                $edit_data = $value;
            }
            $data['maildata'] = $edit_data['attachment'];
            $data['form'] = $this->form->build_form($this->email_form->get_form_fields_email_view_cus_edit(), $edit_data);
            $this->load->view('view_email_add_edit', $data);
        } else {
            redirect(base_url() . 'email/email_history_list/');
        }
    }

    function email_resend_customer($edit_id = '') {
        $add_array = $this->input->post();
        $data['page_title'] = gettext('Resand Email');
        $where = array(
            'id' => $add_array['id']
        );
	$this->db->order_by('id', 'desc');
	$this->db->limit(1);
        $email_array = (array)$this->db->get_where("mail_details", $where)->first_row();
	unset($email_array['id']);
	$email_array['status'] = 1;
	$email_array['date'] = gmdate('Y-m-d H:i:s');
	$this->db->insert('mail_details',$email_array);
        $this->load->module('accounts/accounts');
        $this->session->set_flashdata('astpp_errormsg', gettext('Email Resend Successfully!'));
        redirect(base_url() . 'accounts/customer_emailhistory/' . $email_array['accountid'].'/');
    }

    function email_add($type = "")
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Create Commission Rate');
        $data['form'] = $this->form->build_form($this->email_form->get_form_fields_email(), '');

        $this->load->view('view_email_add_edit', $data);
    }

    function email_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->email_form->get_form_fields_email(), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit email List');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->email_model->edit_email($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext("Email list updated successfully!")
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Email List');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->email_model->add_email($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext("Email list added successfully!")
                ));
                exit();
            }
        }
    }

    function email_re_send($edit_data)
    {
        $this->email_lib->send_notifications('', $edit_data, '', $edit_data['attachment'], "");
        $this->session->set_flashdata('astpp_errormsg', gettext('Email resend successfully!'));
        redirect(base_url() . 'email/email_history_list/');
    }

    function email_view($edit_id = '')
    {
        $this->permission->check_web_record_permission($edit_id, 'mail_details', "email/email_history_list/");
        $data['page_title'] = gettext('View Email');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "mail_details", $where);
        if ($account->num_rows() > 0) {
            foreach ($account->result_array() as $key => $value) {
                $edit_data = $value;
            }
            if ($edit_data['status'] == 1) {
                $edit_data['status'] = gettext('Not Sent');
            } else {
                $edit_data['status'] = gettext('Sent');
            }
            $data['form'] = $this->form->build_form($this->email_form->get_form_fields_email_view(), $edit_data);

            $this->load->view('view_email_add_edit', $data);
        } else {
            redirect(base_url() . 'email/email_history_list/');
        }
    }

    function email_view_customer($edit_id = '')
    {
        $data['page_title'] = gettext('View Email');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "mail_details", $where);
        if ($account->num_rows() > 0) {
            foreach ($account->result_array() as $key => $value) {
                $edit_data = $value;
            }
            if ($edit_data['status'] == 1) {
                $edit_data['status'] = gettext('Not Sent');
            } else {
                $edit_data['status'] = gettext('Sent');
            }
            $data['form'] = $this->form->build_form($this->email_form->get_form_fields_email_view_cus(), $edit_data);
            $this->load->view('view_email_add_edit', $data);
        } else {
            redirect(base_url() . 'email/email_history_list/' . $edit_id);
        }
    }

    function email_delete($id)
    {
        $this->permission->check_web_record_permission($id, 'mail_details', "email/email_history_list/");
        $this->email_model->remove_email($id);
        $this->session->set_flashdata('astpp_notification', gettext('Email removed successfully!'));
        redirect(base_url() . 'email/email_history_list/');
    }

    function email_delete_customer($accounttype, $accountid, $id)
    {
        $this->permission->check_web_record_permission($id, 'mail_details', "email/email_history_list/");
        $this->email_model->remove_email($id);
        $where = array(
            'id' => $id
        );
        $account = $this->db_model->getSelect("*", "mail_details", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $url = "accounts/" . $accounttype . "_emailhistory/$accountid/";
        $this->session->set_flashdata('astpp_notification', gettext('Email removed successfully!'));
        $this->load->module('accounts/accounts');
        redirect(base_url() . $url);
    }

    function attachment_icons($select = "", $table = "", $attachement = "")
    {
        if ($attachement != "") {
            $array = explode(",", $attachement);
            $str = '';
            foreach ($array as $key => $val) {
                $link = base_url() . "email/email_history_list_attachment/" . $val;
                $str .= "<a href='" . $link . "' title='" . $val . "' class='btn btn-royelblue btn-sm'><i class='fa fa-paperclip fa-fw'></i></a>&nbsp;&nbsp;";
            }
            return $str;
        } else {
            return "";
        }
    }
    
    function email_history_list_customer()
    {
        $add_array = $this->input->post();
        $where = array(
            'id' => $add_array['id']
        );
        $account = $this->db_model->getSelect("*", "mail_details", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $this->load->module('accounts/accounts');
        redirect(base_url() . 'accounts/customer_edit/' . $value['accountid']);
    }
    function email_history_list()
    {
        $data['logintype'] = $this->session->userdata('logintype');
        $data['username'] = $this->session->userdata('user_name');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['page_title'] = gettext('Email History');
        $data['grid_fields'] = $this->email_form->build_list_for_email();
        $data["grid_buttons"] = $this->email_form->build_grid_buttons_email();
        $data['form_search'] = $this->form->build_serach_form($this->email_form->get_email_history_search_form());
        $this->load->view('view_email_list', $data);
    }
    function email_history_list_json()
    {
        $data['logintype'] = $this->session->userdata('logintype');
        $json_data = array();
        $count_all = $this->email_model->get_email_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->email_model->get_email_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->email_form->build_list_for_email());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function customer_mail_record($accountid, $accounttype)
    {
        $json_data = array();
        $count_all = $this->email_model->customer_get_email_list(false, $accountid, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->email_model->customer_get_email_list(true, $accountid, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->email_form->build_list_for_email_customer($accountid, $accounttype));
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function email_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("email");
    }

    function email_send_multipal()
    {
        $add_array = $this->input->post();
        if ($add_array['email'] == '' || $add_array['subject'] == '' || $add_array['template'] == '') {
            $this->session->set_flashdata('astpp_notification', gettext('Email address not found!'));
            redirect(base_url() . '/email/email_client_area/');
        }
        $this->email_model->multipal_email($add_array);
        astpp_cron_background('BroadcastEmail');
        $this->session->set_flashdata('astpp_errormsg', gettext('Email broad cast successfully!'));
        redirect(base_url() . 'email/email_history_list/');
    }

    function email_history_list_search()
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
            $this->session->set_userdata('email_search_list', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'email/email_history_list/');
        }
    }

    function email_history_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('email_search', "");
    }

    function email_history_list_attachment($file_name)
    {
        if (file_exists(getcwd() . '/attachments/' . $file_name)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $file_name);
            ob_clean();
            flush();
            readfile(getcwd() . '/attachments/' . $file_name);
        }
    }
}
?>
 
