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
 class Accounts extends MX_Controller
 {

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('accounts_form');
        $this->load->library('astpp/form', 'accounts_form');
        $this->load->library('astpp/permission');
        $this->load->model('common_model');
        $this->load->library('astpp/invoice');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->library('astpp/order');
        $this->load->model('accounts_model');
        $this->load->model('Astpp_common');
        $this->load->library('ASTPP_Sms');
        $this->protected_pages = array(
            'account_list'
        );
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/login/login');
    }

    function customer_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Customers');
        $data['search_flag'] = true;
        $data['batch_update_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->accounts_form->build_account_list_for_customer();
        $data["grid_buttons"] = $this->accounts_form->build_grid_buttons_customer();
        $data['form_search'] = $this->form->build_serach_form($this->accounts_form->get_search_customer_form());
        $data['form_batch_update'] = $this->form->build_batchupdate_form($this->accounts_form->customer_batch_update_form());
        $this->load->view('view_accounts_list', $data);
    }

    function customer_list_json()
    {
        $json_data = array();
        $count_all = $this->accounts_model->get_customer_Account_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->accounts_model->get_customer_Account_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);

        $grid_fields = json_decode($this->accounts_form->build_account_list_for_customer());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function customer_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            if (isset($action['balance']['balance']) && $action['balance']['balance'] != '') {
                $action['balance']['balance'] = $this->common_model->add_calculate_currency($action['balance']['balance'], "", '', false, false);
            }
            if (isset($action['credit_limit']['credit_limit']) && $action['credit_limit']['credit_limit'] != '') {
                $action['credit_limit']['credit_limit'] = $this->common_model->add_calculate_currency($action['credit_limit']['credit_limit'], "", '', false, false);
            }
            $this->session->set_userdata('customer_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'accounts/customer_list/');
        }
    }

    function customer_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('customer_list_search', "");
    }

    function customer_export_cdr_xls()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $query = $this->accounts_model->get_customer_Account_list(true, '', '', true);
        ob_clean();
        $customer_array[] = array(
            gettext("Account"),
            gettext("First Name"),
            gettext("Last Name"),
            gettext("Company"),
            gettext("Rate Group"),
            gettext("Balance") . "(" . $currency . ")",
            gettext("Credit Limit") . "(" . $currency . ")",
            gettext("First Used"),
            gettext("Expiry Date"),
            gettext("CC"),
            gettext("Localization"),
            gettext("Reseller"),
            gettext("Status"),
            gettext("Created Date")
        );
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                if ($row['reseller_id'] == 0) {
                    $reseller_id = 'Admin';
                } else {
                    $reseller_id = $this->common->build_concat_string('first_name,last_name,number', 'accounts', $row['reseller_id']);
                }
                $customer_array[] = array(
                    (isset($row['number']) && $row['number'] != "")?$row['number']:' ',
                    (isset($row['first_name']) && $row['first_name'] != "")?$row['first_name']:' ',
                    (isset($row['last_name']) && $row['last_name'] != "")?$row['last_name']:' ',
                    (isset($row['company_name']) && $row['company_name'] != "")?$row['company_name']:' ',
                    (isset($row['pricelist_id']) && $row['pricelist_id'] != "")?$this->common->get_field_name('name', 'pricelists', $row['pricelist_id']):' ',
                    (isset($row['balance']) && $row['balance'] != "")?$this->common_model->calculate_currency_customer($row['balance']):' ',
                    (isset($row['credit_limit']) && $row['credit_limit'] != "")?$this->common_model->calculate_currency_customer($row['credit_limit']):' ',
                    (isset($row['first_used']) && $row['first_used'] != "")?$row['first_used']:' ',
                    (isset($row['expiry']) && $row['expiry'] != "")?$row['expiry']:' ',
                    (isset($row['maxchannels']) && $row['maxchannels'] != "")?$row['maxchannels']:' ',
                    (isset($row['localization_id']) && $row['localization_id'] !=0)?$this->common->get_field_name('name', 'localization', $row['localization_id']):' ',
                    (isset($reseller_id) && $reseller_id != "")?$reseller_id:' ',
                    (isset($row['status']) && $row['status'] != "")?$this->common->get_status('export', '', $row['status']):' ',
                    (isset($row['creation']) && $row['creation'] != "")?$row['creation']:' ',
                );
            }
        }
        $this->load->helper('csv');
        array_to_csv($customer_array, gettext('Customers').'_' . date("Y-m-d") . '.csv');
    }

    function provider_add()
    {
        $this->customer_add(3);
    }

    function provider_edit($edit_id = '')
    {
        $this->customer_edit($edit_id);
    }

    function provider_save()
    {
        $add_array = $this->input->post();
        $this->customer_save($add_array);
    }

    function customer_add($type = 0)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $entity_type = strtolower($this->common->get_entity_type('', '', $type));
        $data['entity_name'] = $entity_type;
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = ($type == 3) ? gettext('Create Provider') : gettext('Create Customer');

        $currency_info = (array) $this->db->get_where("currency", array(
            "currency" => Common_model::$global_config['system_config']['base_currency']
        ))->first_row();
        $data['back_flag'] = true;
        $selected_data['country_id'] = Common_model::$global_config['system_config']['country'];
        $selected_data['callingcard'] = Common_model::$global_config['system_config']['pinlength'];
        $selected_data['currency_id'] = $currency_info['id'];
        $selected_data['timezone_id'] = Common_model::$global_config['system_config']['default_timezone'];
        $selected_data['tax_id'] = Common_model::$global_config['system_config']['tax_type'];
        $selected_data['sip_device_flag'] = Common_model::$global_config['system_config']['create_sipdevice'];

        $data['form'] = $this->form->build_form($this->accounts_form->get_customer_form_fields($entity_type), '');
        $data['entity_name'] = $entity_type;
        $this->load->view('view_accounts_create', $data);
    }

    function customer_edit($edit_id = '')
    {
        if ((! empty($edit_id)) && (isset($edit_id))) {
            $access_edit = (array) $this->db_model->getSelect("deleted", "accounts", array(
                "id" => $edit_id
            ))->first_row();
        }
        if ((isset($access_edit)) && ($access_edit['deleted'] == 0)) {
            $accountinfo = $this->session->userdata('accountinfo');
            $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
            $where = array(
                'id' => $edit_id
            );
            if ($reseller_id > 0) {
                $where['reseller_id'] = $reseller_id;
            }
            $account = $this->db_model->getSelect("*", "accounts", $where);
            if ($account->num_rows() > 0) {

                $account_data = (array) $account->first_row();
                $entity_name = strtolower($this->common->get_entity_type('', '', $account_data['type']));
                $data['page_title'] = gettext(ucfirst($entity_name) . " Profile");
                $data['invoice_date'] = $account_data['invoice_day'];
                $data["account_data"] = $account_data;
                $data['back_flag'] = true;
                $data['callingcard'] = Common_model::$global_config['system_config']['pinlength'];
                $data['entity_name'] = $entity_name;
                $taxes_data = $this->db_model->getSelect("group_concat(taxes_id) as taxes_id", "taxes_to_accounts", array(
                    "accountid" => $edit_id
                ));
                if (isset($taxes_data) && $taxes_data->num_rows() > 0) {
                    $taxes_data = $taxes_data->result_array();
                    $account_data["tax_id"] = explode(",", $taxes_data[0]['taxes_id']);
                }
                $account_data['password'] = ''; // not readable, the form shows an empty field
                $account_data['first_used'] = $this->common->convert_GMT_to($account_data['first_used'], $account_data['first_used'], $account_data['first_used']);
                $reseller_id = ($account_data['reseller_id'] > 0) ? $account_data['reseller_id'] : 0;
                $data['form'] = $this->form->build_form($this->accounts_form->get_customer_form_fields($entity_name, $edit_id, $reseller_id), $account_data);
                $data['edit_id'] = $edit_id;
                if ($data['account_data']['type'] == 3) {
                    $data['page_title'] = gettext("Edit Provider");
                    $this->load->view('view_provider_details', $data);
                } else {
                    $this->load->view('view_customer_details', $data);
                }
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
                redirect(base_url() . 'accounts/customer_list/');
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
        }
    }

    function customer_save($add_array = false)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $add_array = $this->input->post();
            $current_date = gmdate("Y-m-d H:i:s");
            // this form creates customers (0) and providers (3) only; any type from the hidden field
            // was stored (also -1 = administrator, 2 = sub-administrator)
            $add_array['type'] = (isset($add_array['type']) && (string) $add_array['type'] === '3') ? '3' : '0';

            $entity_name = strtolower($this->common->get_entity_type('', '', $add_array['type']));
            $data['country_id'] = $add_array['country_id'];
            $data['timezone_id'] = $add_array['timezone_id'];
            $data['currency_id'] = $add_array['currency_id'];
            $data['entity_name'] = $entity_name;
            $data['callingcard'] = Common_model::$global_config['system_config']['pinlength'];
            $data['edit_id'] = $add_array['id'];
            $data['form'] = $this->form->build_form($this->accounts_form->get_customer_form_fields($entity_name, $add_array['id']), $add_array);
            if ($add_array['id'] != '') {
                $data['page_title'] = gettext('Edit ' . $this->common->get_entity_type('', '', $add_array['type']));
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                } else {
                    $this->db->select('posttoexternal,number');
                    $posttoexternal = (array) $this->db->get_where("accounts", array(
                        "id" => $add_array['id']
                    ))->first_row();
                    if ($posttoexternal['posttoexternal'] == 0) {
                        $add_array['credit_limit'] = 0;
                    }

                    $this->common->password_from_form($add_array); // hash, empty keeps the password
                    $add_array['credit_limit'] = $this->common_model->add_calculate_currency($add_array['credit_limit'], '', '', false, false);

                    $query = $this->accounts_model->remove_all_account_tax($add_array['id']);
                    if (isset($add_array['tax_id'])) {
                        foreach ($add_array['tax_id'] as $key => $val) {
                            $data1 = array(
                                'accountid' => $add_array['id'],
                                'taxes_id' => $val
                            );
                            $this->accounts_model->add_account_tax($data1);
                        }
                        unset($add_array['tax_id']);
                    }
                    unset($add_array['posttoexternal'], $add_array['number'], $add_array['balance'], $add_array['expiry']);
                    // edit_account() writes the whole POST array; account type, owner and internal
                    // columns are no form fields (type -1 turned a customer into an administrator)
                    unset($add_array['type'], $add_array['reseller_id'], $add_array['deleted'], $add_array['creation'], $add_array['is_distributor'], $add_array['permission_id'], $add_array['last_bill_date'], $add_array['inuse']);
                    $this->db->select('validfordays');
                    $validfordays = (array) $this->db->get_where("accounts", array(
                        "id" => $add_array["id"]
                    ))->first_row();
                    if ($validfordays['validfordays'] != $add_array['validfordays']) {
                        $add_array['expiry'] = gmdate('Y-m-d H:i:s', strtotime($current_date . '+' . $add_array['validfordays'] . ' days'));
                    }
                    $add_array['first_name'] = preg_replace('/[^A-Za-z0-9\-]/', '', $add_array['first_name']);
                    $this->accounts_model->edit_account($add_array, $add_array['id']);
                    $this->session->set_flashdata('astpp_errormsg', gettext(ucfirst($entity_name)).' '.gettext('Updated successfully!'));

                    redirect(base_url() . 'accounts/customer_list/');
                    exit();
                }
                $data["account_data"]["0"] = $add_array;
                $this->load->view('view_customer_details', $data);
            } else {
                $data['page_title'] = gettext('Create ' . $entity_name);
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                } else {
                    $this->common->password_from_form($add_array); // hash, empty keeps the password
                    $add_array['credit_limit'] = $this->common_model->add_calculate_currency($add_array['credit_limit'], '', '', false, false);
                    $add_array['notification_email'] = (! empty($add_array['notification_email'])) ? $add_array['notification_email'] : $add_array['email'];
                    if ($add_array['posttoexternal'] == 1) {
                        $add_array['balance'] = 0;
                    }
                    // the administrator may create the customer for a reseller - only for an existing one
                    if (isset($add_array['reseller_id']) && $add_array['reseller_id'] > 0 && $this->db->get_where('accounts', array('id' => (int) $add_array['reseller_id'], 'type' => 1, 'deleted' => 0))->num_rows() === 0) {
                        $add_array['reseller_id'] = 0;
                    }
                    unset($add_array['deleted'], $add_array['is_distributor'], $add_array['permission_id']);
                    $last_id = $this->accounts_model->add_account($add_array);
                    $this->session->set_flashdata('astpp_errormsg', gettext(ucfirst($entity_name)).' '.gettext('Added Successfully!'));

                    redirect(base_url() . 'accounts/customer_list/');
                    exit();
                }
                $this->load->view('view_accounts_create', $data);
            }
        } else {
            redirect(base_url() . 'accounts/customer_list/');
        }
    }

    function provider_speeddial($edit_id)
    {
        $this->customer_speeddial($edit_id);
    }

    function customer_speeddial($edit_id)
    {
        $data['page_title'] = gettext("Speed Dial");
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account = $this->db_model->getSelect("*", "accounts", $where);

        if ($account->num_rows() > 0) {
            $account_data = (array) $account->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $data['page_title'] = gettext('Speed Dial');
            $data['accounttype'] = $accounttype;
            $data['invoice_date'] = $account_data['invoice_day'];
            $data["account_data"] = $account_data;
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $speeddial_res = $this->db->get_where("speed_dial", array(
                "accountid" => $edit_id
            ));
            $speeddial_info = array();

            if ($speeddial_res->num_rows() > 0) {

                $speeddial_res = $speeddial_res->result_array();
                foreach ($speeddial_res as $key => $value) {
                    $speeddial_info[$value['speed_num']] = $value['number'];
                }
            }
            for ($i = 0; $i <= 9; $i ++) {
                $speeddial_info[$i] = (isset($speeddial_info[$i]) ? $speeddial_info[$i] : '');
            }
            $data['speeddial'] = $speeddial_info;
            $data['form'] = $this->form->build_form($this->accounts_form->get_customer_form_fields($accounttype, $edit_id), $account_data);
            $this->load->view('view_customer_speed_dial', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
        }
    }

    function customer_speeddial_save($number, $accountid, $speed_num)
    {
        if (is_numeric($number)) {
            $where = array(
                "accountid" => $accountid
            );
            $account = $this->db_model->getSelect("*", "speed_dial", $where);
            if ($account->num_rows() == 0) {
                for ($i = 0; $i <= 9; $i ++) {
                    $dest_number = $speed_num == $i ? $number : '';
                    $data[$i] = array(
                        "number" => $dest_number,
                        "speed_num" => $i,
                        'accountid' => $accountid
                    );
                }

                $this->db->insert_batch('speed_dial', $data);

                $this->session->set_flashdata('astpp_errormsg', $number.' '.gettext('Speed Dial Number Added Successfully'));
            } else {
                $updateinfo = array(
                    'number' => $number
                );
                if (is_numeric($updateinfo['number'])) {
                    $this->db->where('speed_num', $speed_num);
                    $this->db->where('accountid', $accountid);
                    $result = $this->db->update('speed_dial', $updateinfo);
                    $this->session->set_flashdata('astpp_errormsg', $updateinfo['number'] .' '.gettext('Speed Dial Number Updated Successfully'));
                } else {
                    $this->session->set_flashdata('astpp_notification', gettext('Please insert only numeric value!'));
                }
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Please insert only numeric value!'));
        }
    }

    function customer_speeddial_remove($accountid, $speed_num)
    {
        $where = array(
            "accountid" => $accountid,
            "speed_num" => $speed_num
        );
        $this->db->select('number');
        $number = (array) $this->db->get_where('speed_dial', $where)->first_row();

        if (! empty($number['number'])) {
            $updateinfo = array(
                'number' => ''
            );
            $this->db->where('speed_num', $speed_num);
            $this->db->where('accountid', $accountid);
            $result = $this->db->update('speed_dial', $updateinfo);
            $this->session->set_flashdata('astpp_notification', $number['number'].' '.gettext('Speed Dial Number Removed Successfully'));
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Speed Dial Number is Empty'));
        }
    }

    function provider_ipmap($edit_id)
    {
        $this->customer_ipmap($edit_id);
    }

    function provider_ipmap_action($action, $accountid, $accounttype, $ipmapid = "")
    {
        $this->customer_ipmap_action($action, $accountid, $accounttype, $ipmapid);
    }

    function customer_ipmap_action($action, $accountid, $accounttype, $ipmapid = "")
    {
        $add_array = $this->input->post();
        if ($action == "add" && ! empty($add_array)) {
            if ($add_array['ip'] != "") {
                // "address/bits", /32 or /128 if none given (/32 was appended to
                // IPv6 addresses too); anything else is refused, see common::ipmap_cidr()
                $ip_cidr = $this->common->ipmap_cidr($add_array['ip']);
                if ($ip_cidr === false) {
                    $this->session->set_flashdata('astpp_notification', gettext('Please enter a valid IP address or network, e.g. 192.0.2.10 or 192.0.2.0/24.'));
                    redirect(base_url() . "accounts/" . $accounttype . "_ipmap/" . $accountid . "/");
                }
                $add_array['ip'] = $ip_cidr;
                $where = array(
                    "ip" => trim($add_array['ip']),
                    "prefix" => trim($add_array['prefix'])
                );
                $getdata = $this->db_model->countQuery("*", "ip_map", $where);
                if ($getdata > 0) {
                    $this->session->set_flashdata('astpp_notification', gettext('IP already exist in system.'));
                } else {
                    if ($accounttype == "provider") {
                        $add_array['pricelist_id'] = 0;
                    }
                    unset($add_array['action']);
                    $add_array['context'] = 'default';
                    $add_array['accountid'] = $accountid;
                    $add_array['last_modified_date'] = gmdate('Y-m-d H:i:s');
                    $ip_flag = $this->db->insert("ip_map", $add_array);
                    if ($ip_flag) {
                        $this->load->library('freeswitch_lib');
                        $this->load->module('freeswitch/freeswitch');
                        $command = "api reloadacl";
                        $response = $this->freeswitch_model->reload_freeswitch($command);
                        $this->session->set_userdata('astpp_notification', $response);
                    }
                    $this->session->set_flashdata('astpp_errormsg', gettext('IP added sucessfully.'));
                }
            }
        }
        if ($action == "delete") {
            $ip_flag = $this->db_model->delete("ip_map", array(
                "id" => $ipmapid
            ));
            $this->load->library('freeswitch_lib');
            $this->load->module('freeswitch/freeswitch');
            $this->load->model("freeswitch_model");
            $command = "api reloadacl";
            $this->freeswitch_model->reload_freeswitch($command);
            $this->session->set_flashdata('astpp_notification', gettext('IP removed sucessfully.'));
        }
        redirect(base_url() . "accounts/" . $accounttype . "_ipmap/" . $accountid . "/");
    }

    function provider_animap($edit_id)
    {
        $this->customer_animap($edit_id, 'provider');
    }

    function customer_animap($edit_id, $entity_type = 'customer')
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("type", "accounts", $where);
        if ($account_res->num_rows() > 0) {
            $data['page_title'] = gettext("Caller ID");
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $data["grid_fields"] = $this->accounts_form->build_animap_list_for_customer($edit_id, $accounttype);
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $this->load->view('view_customer_animap', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function customer_animap_json($accountid, $accounttype)
    {
        $json_data = array();
        $instant_search = $this->session->userdata('left_panel_search_' . $accounttype . '_animap');
        $like_str = ! empty($instant_search) ? "(number like '%$instant_search%'  OR  creation_date like '%$instant_search%' )" : null;
        if (! empty($like_str))
            $this->db->where($like_str);
        $where = array(
            "accountid" => $accountid
        );
        $count_all = $this->db_model->countQuery("*", "ani_map", $where);

        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        if (! empty($like_str))
            $this->db->where($like_str);
        $query = $this->db_model->select("*", "ani_map", $where, "id", "ASC", $paging_data["paging"]["page_no"], $paging_data["paging"]["start"]);

        $grid_fields = json_decode($this->accounts_form->build_animap_list_for_customer($accountid, $accounttype));
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function provider_animap_action($action, $accountid, $aniid = "")
    {
        $this->customer_animap_action($action, $accountid, $aniid);
    }

    function customer_animap_action($action, $accountid, $aniid = "")
    {
        $entity_type = $this->common->get_field_name('type', 'accounts', array(
            'id' => $accountid
        ));

        $entity_type = strtolower($this->common->get_entity_type('', '', $entity_type));
        $url = "accounts/" . $entity_type . "_animap/$accountid/";
        if ($action == "add") {
            $ani = $this->input->post();
            $this->db->where('number', $ani['number']);
            $this->db->select('count(id) as count');
            $cnt_result = $this->db->get('ani_map');
            $cnt_result = $cnt_result->result_array();
            $count = $cnt_result[0]['count'];
            if ($count == 0) {
                if ($ani['number'] != "") {
                    $insert_arr = array(
                        'creation_date' => gmdate('Y-m-d H:i:s'),
                        'last_modified_date' => gmdate('Y-m-d H:i:s'),
                        "number" => $this->input->post('number'),
                        "accountid" => $accountid,
                        "context" => "default"
                    );
                    $this->db->insert("ani_map", $insert_arr);
                    $this->session->set_flashdata('astpp_errormsg', gettext('Caller ID added successfully!'));
                } else {
                    $this->session->set_flashdata('astpp_notification', gettext('Please Enter Caller ID value.'));
                }
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Caller ID already Exists.'));
            }
        }
        if ($action == "delete") {
            $this->session->set_flashdata('astpp_notification', gettext('Caller ID removed sucessfully!'));
            $this->db_model->delete("ani_map", array(
                "id" => $aniid
            ));
        }
        redirect(base_url() . $url);
    }

    function customer_details_json($module, $accountid)
    {
        $entity_type = $this->common->get_field_name('type', 'accounts', array(
            'id' => $accountid
        ));
        $entity_type = strtolower($this->common->get_entity_type('', '', $entity_type));
        if ($module == "pattern") {
            $this->load->module('rates/rates');
            $this->rates->customer_block_pattern_list($accountid, $entity_type);
        }
        if ($module == "freeswitch") {
            $this->load->module('freeswitch/freeswitch');
            $this->freeswitch->customer_fssipdevices_json($accountid, $entity_type);
        }
        if ($module == "did") {
            $this->load->module('did/did');
            $this->did->customer_did($accountid, $entity_type);
        }
        if ($module == "invoices") {
            $this->load->module('invoices/invoices');
            $this->invoices->customer_invoices($accountid, $entity_type, false);
        }
        if ($module == "subscription") {
            $this->load->module('charges/charges');
            $this->charges->customer_charge_list($accountid, $entity_type);
        }
        if ($module == "cdrs") {
            $this->load->module('reports/reports');
            $this->reports->customer_cdrreport($accountid, $entity_type);
        }
        if ($module == "charges") {
            $this->load->module('reports/reports');
            $this->reports->customer_charge_history($accountid, $entity_type);
        }
        if ($module == "refill") {
            $this->load->module('reports/reports');
            $this->reports->customer_refillreport($accountid, $entity_type);
        }
        if ($module == "emailhistory") {
            $this->load->module('email/email');
            $this->email->customer_mail_record($accountid, $entity_type);
        }
        if ($module == "plans") {
            $this->load->module('plans/plans');
            $this->plans->customer_plans_list($accountid, $entity_type);
        }
        if ($module == "products") {
            $this->load->module('products/products');
            $this->products->customer_products_list($accountid, $entity_type);
        }
    }

    function customer_details_search($module_name)
    {
        $action = $this->input->post();
        $this->session->set_userdata('left_panel_search_' . $module_name, "");
        if (! empty($action['left_panel_search'])) {
            $this->session->set_userdata('left_panel_search_' . $module_name, $action['left_panel_search']);
        }
    }

    function provider_sipdevices($edit_id)
    {
        $this->customer_sipdevices($edit_id);
    }

    function customer_sipdevices($edit_id)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("type", "accounts", $where);
        if (($account_res->num_rows() > 0) || ($accountinfo['type'] = - 1)) {
            $data['page_title'] = gettext("SIP Devices");
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $this->load->module('freeswitch/freeswitch');
            $data["grid_buttons"] = $this->freeswitch->freeswitch_form->fsdevices_build_grid_buttons($edit_id, $accounttype);
            $data['grid_fields'] = $this->freeswitch->freeswitch_form->build_devices_list_for_customer();
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $this->load->view('view_customer_sipdevices', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function provider_fssipdevices_action($action, $id, $accountid)
    {
        $this->customer_fssipdevices_action($action, $id, $accountid);
    }

    function customer_fssipdevices_action($action, $id, $accountid)
    {
        $this->permission->check_web_record_permission($id, 'sip_devices', 'accounts/customer_list/');
        $entity_type = $this->common->get_field_name('type', 'accounts', array(
            'id' => $accountid
        ));
        $entity_type = strtolower($this->common->get_entity_type('', '', $entity_type));
        $this->load->module('freeswitch/freeswitch');
        if ($action == "delete") {
            $this->freeswitch->freeswitch_model->delete_freeswith_devices($id);
            $this->session->set_flashdata('astpp_notification', gettext('Sip Device removed successfully!'));
            redirect(base_url() . "accounts/" . $entity_type . "_sipdevices/$accountid/");
        }
        if ($action == "edit") {
            $this->freeswitch->customer_fssipdevices_edit($id, $accountid);
            $this->session->set_flashdata('astpp_errormsg', gettext('Sip updated successfully!'));
        }
    }

    function provider_charges($edit_id)
    {
        $this->customer_charges($edit_id);
    }

    function customer_charges($edit_id)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("type", "accounts", $where);
        if ($account_res->num_rows() > 0) {
            $data['page_title'] = gettext("Charges History");
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $this->load->module('reports/reports');
            $data['grid_fields'] = $this->reports->reports_form->build_charge_list_for_customer($edit_id, $accounttype);
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $this->load->view('view_customer_charges', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function provider_dids($edit_id)
    {
        $this->customer_dids($edit_id);
    }

    function customer_dids($edit_id)
    {
        $data['page_title'] = gettext("DIDs");
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("type,country_id", "accounts", $where);
        if ($account_res->num_rows() > 0) {
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $this->load->module('did/did');
            $data['grid_fields'] = $this->did->did_form->build_did_list_for_customer($edit_id, $accounttype);
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $result_did_final = array();
            $did_info = array(
                "name" => "free_didlist",
                "id" => "free_didlist",
                "class" => "free_didlist"
            );
            $data['didlist'] = form_dropdown_all($did_info, $result_did_final, '');
            $data['country_id'] = $account_data['country_id'];
            $this->load->view('view_customer_dids', $data);
        } else {
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function provider_dids_action($action, $accountid, $accounttype, $did_id = "")
    {
        $this->customer_dids_action($action, $accountid, $accounttype, $did_id);
    }

    function customer_dids_action($action, $accountid, $accounttype, $did_id = "")
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $did_id = empty($did_id) ? $this->input->post("free_didlist", true) : $did_id;
        if ($did_id != "") {
            $account_arr = (array) $this->db->get_where("accounts", array(
                "id" => $accountid
            ))->first_row();
            $did_arr = (array) $this->db->get_where("dids", array(
                "id" => $did_id
            ))->first_row();
            $field_name = $account_arr['type'] == 1 ? "parent_id" : 'accountid';
            if ($action == "add") {
                $this->load->library('did_lib');
                $didid = $this->common->get_field_name("id", "dids", array(
                    "product_id" => $did_id
                ));
                $did_result = $this->did_lib->did_billing_process($this->session->userdata, $account_arr['id'], $didid);
                if ($did_result[0] == "SUCCESS") {
                    if($account_arr['reseller_id'] > 0){
                        $product_info = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,reseller_products.buy_cost,reseller_products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.status,dids.last_modified_date,dids.product_id', array('dids.product_id'=>$did_id,'dids.parent_id'=>$account_arr['reseller_id'],'reseller_products.account_id'=>$account_arr['reseller_id']),'reseller_products','dids.product_id=reseller_products.product_id', 'inner','','','','');
                    }else{
                    	$product_info = $this->db_model->getSelect("*", "products", array(
                           "id" => $did_id
                       ));
                    }
                    if ($product_info->num_rows > 0) {
                        $product_info = $product_info->result_array()[0];
                        $product_info['product_id'] = $did_id;
                        $last_id = $this->order->confirm_order($product_info, $accountid, $accountinfo);
                        if ($last_id != '') {
                            $this->db->where("product_id", $product_info['product_id']);
                            $this->db->update("dids", array(
                                "accountid" => $accountid
                            ));
                        }
                    }

                }
                $astpp_flash_message_type = ($did_result[0] == "SUCCESS") ? "astpp_errormsg" : "astpp_notification";
                $this->session->set_flashdata($astpp_flash_message_type, $did_result[1]);
		 /*else {

                    $astpp_flash_message_type = ($did_result[0] == "INSUFFIECIENT_BALANCE") ? "astpp_notification" : "astpp_errormsg";
                    $this->session->set_flashdata($astpp_flash_message_type, $did_result[1]);
                }*/
            }
            if ($action == "delete") {
                $data = array(
                    "accountid" => "0",
                    "assign_date" => "0000-00-00 00:00:00",
                    "charge_upto" => "0000-00-00 00:00:00"
                );
                if ($accounttype == 'reseller') {
                    $data = array(
                        "accountid" => "0",
                        "parent_id" => $reseller_id,
                        "assign_date" => "0000-00-00 00:00:00",
                        "charge_upto" => "0000-00-00 00:00:00"
                    );
                }
                if ($reseller_id > 0) {
                    $subreseller_id = $this->common->get_subreseller_info($accountinfo['id']);
                    $subreseller_id = rtrim($subreseller_id, ",");
                    $where = "parent_id IN ($subreseller_id)";
                    $this->db->where('id', $did_id);
                    $this->db->where($where);
                    $this->db->select('id');
                    $this->db->from('dids');
                    $result = (array) $this->db->get()->first_row();
                    if (empty($result)) {
                        $this->permission->permission_redirect_url('accounts/customer_list/');
                    }
                }
                $this->db_model->update("dids", $data, array(
                    "id" => $did_id
                ));
                if ($accounttype == 'reseller') {
                    $this->db->where('reseller_id', $account_arr['id']);
                    $this->db->where('note', $did_arr['number']);
                    $this->db->delete('reseller_pricing');
                }
                require_once (APPPATH . 'controllers/ProcessCharges.php');
                $ProcessCharges = new ProcessCharges();
                $Params = array(
                    "DIDid" => $did_id
                );

                $ProcessCharges->BillAccountCharges("DIDs", $Params);
                $account_arr['did_number'] = $did_arr['number'];
                $last_inserted_id = $this->astpp_sms->send_sms('email_remove_did', $account_arr, '');
                $account_arr['last_inserted_id'] = $last_inserted_id;
                $this->common->mail_to_users('email_remove_did', $account_arr);
                $this->session->set_flashdata('astpp_notification', gettext('Did Removed Successfully.'));
            }
        }
        redirect(base_url() . "accounts/" . $accounttype . "_dids/$accountid/");
    }

    function provider_invoices($edit_id)
    {
        $this->customer_invoices($edit_id);
    }

    function customer_invoices($edit_id)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("type", "accounts", $where);
        if ($account_res->num_rows() > 0) {
            $data['page_title'] = gettext("Invoice");
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $this->load->module('invoices/invoices');
            $data['grid_fields'] = $this->invoices->invoices_form->build_invoices_list_for_customer_admin(false);
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $this->load->view('view_customer_invoices', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function provider_blocked_prefixes($edit_id)
    {
        $this->customer_blocked_prefixes($edit_id);
    }

    function customer_blocked_prefixes($edit_id)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("type", "accounts", $where);
        if ($account_res->num_rows() > 0) {
            $data['page_title'] = gettext("Blocked Codes");
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $this->load->module('rates/rates');
            $data['grid_fields'] = $this->rates->rates_form->build_pattern_list_for_customer($edit_id, $accounttype);
            $data['grid_buttons'] = $this->rates->rates_form->set_pattern_grid_buttons($edit_id);
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $this->load->view('view_customer_blocked_prefixes', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function customer_add_blockpatterns($accountid)
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Unblocked Codes');
        $this->session->set_userdata('advance_search', 0);
        $this->load->module('rates/rates');
        $data['patters_grid_fields'] = $this->rates->rates_form->build_block_pattern_list_for_customer();
        $data["accountid"] = $accountid;
        $this->load->view('view_block_prefix_list', $data);
    }

    function customer_add_blockpatterns_json($accountid)
    {
        $this->load->module('rates/rates');
        $json_data = array();
        $count_all = $this->rates_model->getunblocked_pattern_list($accountid, false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->rates->rates_model->getunblocked_pattern_list($accountid, true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->rates->rates_form->build_block_pattern_list_for_customer());
        $json_data['rows'] = $this->rates->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function customer_block_prefix($accountid)
    {
        $result = $this->accounts_model->insert_block($this->input->post('prefixies', true), $accountid);
        echo $result;
        exit();
    }

    function provider_delete_block_pattern($accountid, $patternid)
    {
        $this->customer_delete_block_pattern($accountid, $patternid);
    }

    function customer_delete_block_pattern($accountid, $patternid)
    {
        $this->permission->check_web_record_permission($patternid, 'block_patterns', 'accounts/customer_list/', false, array(
            'field_name' => "accountid",
            "parent_table" => "accounts"
        ));
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        $where = array(
            'id' => $accountid,
            "reseller_id" => $reseller_id
        );

        $entity_type = $this->common->get_field_name('type', 'accounts', $where);
        if (! empty($entity_type)) {
            $entity_type = strtolower($this->common->get_entity_type('', '', $entity_type));
            $url = "accounts/" . $entity_type . "_blocked_prefixes/$accountid";
            $this->db_model->delete("block_patterns", array(
                "id" => $patternid
            ));
            $this->session->set_flashdata('astpp_notification', gettext('Block Code Removed Sucessfully!'));
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
        }
        redirect(base_url() . $url);
    }

    function customer_blockedprefixes_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->delete("block_patterns", $where);
        echo TRUE;
    }

    function provider_cdrs($edit_id)
    {
        $this->customer_cdrs($edit_id);
    }

    function customer_cdrs($edit_id)
    {
        $data['page_title'] = gettext("CDRs");
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("type", "accounts", $where);
        if ($account_res->num_rows() > 0) {
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $this->load->module('reports/reports');
            $data['grid_fields'] = $this->reports->reports_form->build_report_list_for_user($accounttype);
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $this->load->view('view_customer_cdrs_list', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function provider_refillreport($edit_id)
    {
        $this->customer_refillreport($edit_id);
    }

    function customer_refillreport($edit_id)
    {
        $data['page_title'] = gettext("Refill Report");
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("type", "accounts", $where);
        if ($account_res->num_rows() > 0) {
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            $this->load->module('reports/reports');
            $data['grid_fields'] = $this->reports->reports_form->build_refillreport_for_customer();
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $this->load->view('view_customer_refill_report', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function provider_emailhistory($edit_id)
    {
        $this->customer_emailhistory($edit_id);
    }

    function customer_emailhistory($edit_id)
    {
        if (isset($edit_id) && ($_SERVER['REQUEST_URI'] == '/accounts/customer_emailhistory/' . $edit_id . '/')) {
            $accountinfo = $this->session->userdata('accountinfo');
            $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
            if ($accountinfo['type'] == - 1) {
                $where = array(
                    'id' => $edit_id
                );
            } else {
                $where = array(
                    'id' => $edit_id,
                    "reseller_id" => $reseller_id
                );
            }
            $account_res = $this->db_model->getSelect("type", "accounts", $where);
            if ($account_res->num_rows() > 0) {
                $data['page_title'] = gettext("Emails");
                $account_data = (array) $account_res->first_row();
                $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
                $this->load->module('email/email');
                $data['grid_fields'] = $this->email->email_form->build_list_for_email_customer($edit_id, $accounttype);
                $data['edit_id'] = $edit_id;
                $data['accounttype'] = $accounttype;
                $this->load->view('view_customer_email_history', $data);
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
                redirect(base_url() . 'accounts/customer_list/');
                exit();
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function provider_alert_threshold($edit_id)
    {
        $this->customer_alert_threshold($edit_id);
    }

    function customer_alert_threshold($edit_id)
    {
        $data['page_title'] = gettext("Alert Threshold");
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account_res = $this->db_model->getSelect("notify_credit_limit,notify_flag,notify_email,type,id", "accounts", $where);
        if ($account_res->num_rows() > 0) {
            $account_data = (array) $account_res->first_row();
            $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));
            unset($account_data['type']);
            $data['form'] = $this->form->build_form($this->accounts_form->customer_alert_threshold($accounttype), $account_data);
            $data['edit_id'] = $edit_id;
            $data['accounttype'] = $accounttype;
            $this->load->view('view_customer_alert_threshold', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function reseller_alert_threshold_save($entity_type)
    {
        $this->customer_alert_threshold_save($entity_type);
    }

    function provider_alert_threshold_save($entity_type)
    {
        $this->customer_alert_threshold_save($entity_type);
    }

    function customer_alert_threshold_save($entity_type)
    {
        $add_array = $this->input->post();
        if (isset($add_array['id']) && ! empty($add_array['id'])) {
            $data['page_title'] = gettext("Alert Threshold");
            $data['form'] = $this->form->build_form($this->accounts_form->customer_alert_threshold($entity_type), $add_array);
            $data['edit_id'] = $add_array['id'];
            $data['accounttype'] = $entity_type;
            $accountinfo = $this->session->userdata('accountinfo');
            $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
            $where = array(
                'id' => $add_array['id'],
                "reseller_id" => $reseller_id
            );
            $account_res = $this->db_model->getSelect("type", "accounts", $where);
            if ($account_res->num_rows() > 0) {
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                } else {
                    $this->db->where('id', $add_array['id']);
                    $id = $add_array['id'];
                    unset($add_array['id'], $add_array['action']);
                    $this->db->update('accounts', $add_array);
                    $this->session->set_flashdata('astpp_errormsg', gettext('Alert threshold updated successfully!'));
                    redirect(base_url() . 'accounts/' . $entity_type . '_alert_threshold/' . $id . "/");
                    exit();
                }
            } else {
                redirect(base_url() . 'accounts/' . $entity_type . '_list/');
                exit();
            }
            $this->load->view('view_customer_alert_threshold', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/customer_list/');
            exit();
        }
    }

    function customer_bulk_creation()
    {
        $data['page_title'] = gettext('Mass Customer');
        $data['form'] = $this->form->build_form($this->accounts_form->customer_bulk_generate_form(), '');
        $this->load->view('view_bulk_account_creation', $data);
    }

    function customer_bulk_save()
    {
        $data['page_title'] = gettext('Create Bulk Customer');
        $add_array = $this->input->post();
        if (! empty($add_array) && isset($add_array)) {
            $add_array['count'] = filter_var($add_array['count'], FILTER_SANITIZE_NUMBER_INT);
            $add_array['prefix'] = filter_var($add_array['prefix'], FILTER_SANITIZE_NUMBER_INT);
            $add_array['account_length'] = filter_var($add_array['account_length'], FILTER_SANITIZE_NUMBER_INT);
            $currentlength = $this->accounts_model->get_max_limit($add_array);
            $account_data = $this->session->userdata("accountinfo");
            $add_array['reseller_id'] = $account_data['type'] == 1 ? $account_data['id'] : 0;
            $data['form'] = $this->form->build_form($this->accounts_form->customer_bulk_generate_form(), $add_array);
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else if ($currentlength <= 0) {
                echo json_encode(array(
                    "prefix_error" => gettext("Your Account Limit has been reached.Please Change Your Prefix.")
                ));
                exit();
            } else if ($add_array['account_length'] <= strlen($add_array['prefix'])) {
                echo json_encode(array(
                    "account_length_error" => gettext("Please Enter Proper Account Length.")
                ));
                exit();
            } else if ($currentlength > 0 && $add_array['count'] > $currentlength) {
                echo json_encode(array(
                    "count_error" => $currentlength.' '.gettext('accounts with'.' '.$add_array['prefix'].' '.gettext('prefix'))
                ));
                exit();
            } else {
                $this->accounts_model->bulk_insert_accounts($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext("Bulk customer generate successfully!")
                ));
                exit();
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . "accounts/customer_list/");
        }
    }

    function customer_invoice_option($value = false)
    {
        $sweepid = $this->input->post("sweepid", true);
        $invoice_dropdown = $this->common->set_invoice_option($sweepid, "", "", $value);
        echo $invoice_dropdown;
    }

    function customer_did_country()
    {
        $country_id = $_POST['country_id'];
        $provience = "";
        $city = "";
        $accountid = $this->input->post('accountid', true);
        $accountinfo = $this->session->userdata("accountinfo");
        $entity_info = (array) $this->db->get_where('accounts', array(
            "id" => $accountid
        ))->first_row();

        if (isset($_POST['provience']) && $_POST['provience'] != "") {
            $provience = $_POST['provience'];
        }
        if (isset($_POST['city']) && $_POST['city'] != "") {
            $city = $_POST['city'];
        }

        if (isset($country_id) && $country_id != "") {
            $state_list = array();
            $state_list_array = array();
            $this->db->where('province NOT LIKE', '');
            $state_list = $this->db_model->getSelect("distinct(province)", "dids", array(
                'country_id' => $country_id
            ));
            if ($state_list->num_rows() > 0) {
                $state_list_array = $state_list->result_array();
                foreach ($state_list_array as $key => $val) {
                    foreach ($val as $key1 => $val1) {
                        $data['state_list'][] = "<option value=" . $val1 . ">" . $val1 . "</option>";
                    }
                }
            }
            // ASTPPCOM-1333 Start
            if (isset($provience) && $provience != "") {
            // ASTPPCOM-1333 END
                $this->db->where('city NOT LIKE', '');
                $city_list = $this->db_model->getSelect("city", "dids", array(
                    // ASTPPCOM-1333 Start
                    'province' => $provience,
                    // ASTPPCOM-1333 END
                    'country_id' => $country_id
                ));
                if ($city_list->num_rows() > 0) {
                    $city_list_array = $city_list->result_array();                    
                    foreach ($city_list_array as $key => $val) {
                        foreach ($val as $key1 => $val1) {
                            $data['city_list'][] = '<option value="'. $val1 .'"> '. $val1 .' </option>';
                        }
                    }
                }
            }
        }

        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        if (isset($entity_info['reseller_id']) && $entity_info['reseller_id'] > 0) {
            $parent_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : $accountinfo['reseller_id'];
            if (isset($provience) && $provience != "") {
                $this->db->where('dids.province', $provience);
            }
            if (isset($city) && $city != "") {
                $this->db->where('dids.city', $city);
            }
            $did_list = $this->db_model->getJionQuery('dids', 'dids.id,dids.product_id,dids.number,reseller_products.buy_cost,reseller_products.commission,reseller_products.price,reseller_products.billing_type,reseller_products.setup_fee,reseller_products.billing_days,reseller_products.product_id', array(
                'dids.accountid' => 0,
                'dids.country_id' => $country_id,
                // ASTPPCOM-1333 Start
                'dids.parent_id' => $entity_info['reseller_id'],
                // ASTPPCOM-1333 END
                'dids.status' => 0
            ), 'reseller_products', 'dids.product_id=reseller_products.product_id', 'inner', "", "", 'DESC', 'dids.id');
        } else {
            if (isset($provience) && $provience != "") {
                $this->db->where('dids.province', $provience);
            }
            if (isset($city) && $city != "") {
                $this->db->where('dids.city', $city);
            }
            $did_list = $this->db_model->getJionQuery('dids', 'dids.id,dids.product_id,dids.number,products.buy_cost,products.commission,products.price,products.billing_type,products.setup_fee,products.billing_days,products.id', array(
                'dids.accountid' => 0,
                'dids.country_id' => $country_id,
                // ASTPPCOM-1333 Start
                'dids.parent_id' => $entity_info['reseller_id'],
                // ASTPPCOM-1333 END
                'dids.status' => 0
            ), 'products', 'dids.product_id=products.id', 'inner', "", "", 'DESC', 'dids.id');
        }
        $did_arr = array();
        if ($did_list->num_rows() > 0) {
            $did_data = $did_list->result_array();
            foreach ($did_data as $key => $value) {
                $did_arr[$value['product_id']] = $value['number'] . " (Setup Cost : " . $this->common_model->calculate_currency($value['setup_fee'], '', '', true) . ") (Monthly cost : " . $this->common_model->calculate_currency($value['price'], '', '', true) . ")";
            }
        }
        $did_info = array(
            "name" => "free_didlist",
            "id" => "free_didlist",
            "class" => "free_didlist"
        );

        $data['didlist'] = form_dropdown_all($did_info, $did_arr, '');
        echo json_encode($data);
        exit();
    }

    function customer_payment_process_add($id = '')
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $where = array(
            'id' => $id
        );
        if (($reseller_id > 0) && ($accountinfo['type'] != - 1)) {
            $where['reseller_id'] = $reseller_id;
        }

        $customer_info = $this->db->get_where('accounts', $where);
        if ($customer_info->num_rows > 0 || $accountinfo['type'] == - 1) {
            $customer_info = (array) $customer_info->first_row();
            $currency = $this->accounts_model->get_currency_by_id($customer_info['currency_id']);
            $data['username'] = $this->session->userdata('user_name');
            $data['page_title'] = gettext('​Refill Process');
            $data['form'] = $this->form->build_form($this->accounts_form->get_customer_payment_fields($currency['currency'], $customer_info['number'], $currency['currency'], $id), '');
            $this->load->view('view_accounts_process_payment', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied'));
            redirect(base_url() . 'accounts/customer_list/');
        }
    }

    // Jaimin ASTPPCOM-738
    function customer_payment_save($id = '')
    {
        $post_array = $this->input->post();
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $post_array['id']
            );
        } else {
            $where = array(
                'id' => $post_array['id'],
                'reseller_id' => $reseller_id
            );
        }
        $customer_info = $this->db->get_where('accounts', $where);
        if ($customer_info->num_rows() > 0) {
            $data['page_title'] = gettext('Process Payment');
            $customer_info = (array) $customer_info->first_row();
            $currency = $this->accounts_model->get_currency_by_id($customer_info['currency_id']);
            $data['form'] = $this->form->build_form($this->accounts_form->get_customer_payment_fields($currency['currency'], $customer_info['number'], $currency['currency'], $id), $post_array);

            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
              // plain amount > 0 (greater_than[0] also accepted 1e308, 0x1A ...); payment type only
              // refill (0) or post charge (1)
              $credit = order::amount(trim((string) $post_array['credit']), 999999999);
              if ($credit === false || $credit <= 0 || ! in_array((string) $post_array['payment_type'], array('0', '1'), true)) {
                  echo json_encode(array(
                      'credit_error' => gettext('Please enter valid amount.')
                  ));
                  exit();
              }
              $post_array['credit'] = $this->common_model->add_calculate_currency($credit, '', '', false, false);
              $response = $this->accounts_model->account_process_payment($post_array);
              $customer_info['refill_amount'] = $post_array['credit'];
              if ($post_array['payment_type'] == 1) {
                $customer_info['balance'] = $customer_info['balance'] - $customer_info['refill_amount'];
                $this->common->mail_to_users('account_postcharge', $customer_info);
            }else{
                $customer_info['balance'] = $customer_info['balance'] + $customer_info['refill_amount'];
                $this->common->mail_to_users('account_refilled', $customer_info);
            }
            $message = $post_array['payment_type'] == 0 ? gettext("Recharge successfully!") : gettext("Post charge applied successfully.");
            echo json_encode(array(
                "SUCCESS" => gettext($message)
            ));
            exit();
        }
        $this->load->view('view_accounts_process_payment', $data);
    } else {
        $this->session->set_flashdata('astpp_notification', gettext('Permission Denied'));
        redirect(base_url() . 'accounts/customer_list/');
    }
} //END

function get_invoice_date($select, $accountid)
{
    $query = $this->db_model->select($select, "invoices", '', "id", "DESC", "1", "0");
    if ($query->num_rows() > 0) {
        $invoiceid = $query->result_array();
        $invoice_date = $invoiceid[0][$select];
        return $invoice_date;
    }
    return false;
}

  //   function customer_add_callerid($id)
  //   {
  //       $data['username'] = $this->session->userdata('user_name');
  //       $data['page_title'] = gettext('​Force Caller ID');
  //       $account_num = $this->accounts_model->get_account_number($id);
  //       $result = $this->accounts_model->get_callerid($id);
  //       if ($result->num_rows() > 0) {
  //           foreach ($result->result_array() as $values) {
  //               $data['accountid'] = $values['accountid'];
  //               $data['callerid_name'] = $values['callerid_name'];
  //               $data['callerid_number'] = $values['callerid_number'];
  //               $data['status'] = $values['status'];
  //               $data['flag'] = '1';
  //           }
  //       } else {
  //           $data['accountid'] = $id;
  //           $data['callerid_name'] = '';
  //           $data['callerid_number'] = '';
  //           $data['status'] = '1';
  //           $data['flag'] = '0';
  //       }
  //       $data['accountid'] = $account_num['number'];
  //       $post_array = $this->input->post();
  //       if(isset($post_array) && !empty($post_array)){
		// 		if(isset($post_array['status']) && $post_array['status']!=""){
		// 			$data['status']=$post_array['status'];
		// 		}
		// 		if(isset($post_array['callerid_name']) && $post_array['callerid_name']!=""){
		// 			$data['callerid_name']=$post_array['callerid_name'];
		// 		}
		// 		if(isset($post_array['callerid_number']) && $post_array['callerid_number']!=""){
		// 			$data['callerid_number']=$post_array['callerid_number'];
		// 		}	
		// }
  //       $data['form'] = $this->form->build_form($this->accounts_form->get_customer_callerid_fields($id,$post_array), $data);
  //       $post_array['accountid'] = $this->uri->segment('3');
  //       $id = $this->uri->segment('3');

  //       if (! empty($post_array)) {
  //           if ($this->form_validation->run() == FALSE) {
  //               $data['validation_errors'] = validation_errors();
  //           } else {
  //               $post_array['callerid_name'] = trim($post_array['callerid_name']);
  //               $post_array['callerid_number'] = trim($post_array['callerid_number']);
  //               if ($post_array['flag'] == '1') {
  //                   $this->accounts_model->edit_callerid($post_array);
  //                   $entity_type = 'customer';
  //                   $this->session->set_flashdata('astpp_errormsg', gettext('Account callerID updated successfully!'));
  //                   redirect(base_url() . 'accounts/' . $entity_type . '_add_callerid/' . $id . "/");
  //                   exit();
  //               } else {
  //                   $this->accounts_model->add_callerid($post_array);
  //                   $entity_type = 'customer';
  //                   $this->session->set_flashdata('astpp_errormsg', gettext('Account callerID added successfully!'));
  //                   redirect(base_url() . 'accounts/' . $entity_type . '_add_callerid/' . $id . "/");
  //               }
  //           }
  //       }
  //       $data['edit_id'] = $id;
  //       $data['accounttype'] = 'customer';
  //       $this->load->view('view_accounts_add_callerid', $data);
  //   }

function reseller_add($type = "1")
{
    $accountinfo = $this->session->userdata('accountinfo');
    $entity_type = strtolower($this->common->get_entity_type('', '', $type));
    $data['entity_name'] = $entity_type;
    $data['username'] = $this->session->userdata('user_name');
    $data['flag'] = 'create';
    $data['page_title'] = gettext('Create Reseller');
    $data['back_flag'] = true;
    $data['form'] = $this->form->build_form($this->accounts_form->get_form_reseller_fields(), '');
    $this->load->view('view_accounts_create', $data);
}

function reseller_edit($edit_id = '')
{
    if ((! empty($edit_id)) && (isset($edit_id))) {
        $access_edit = (array) $this->db_model->getSelect("deleted", "accounts", array(
            "id" => $edit_id
        ))->first_row();
    }
    if ((isset($access_edit)) && ($access_edit['deleted'] == 0)) {
        $data['page_title'] = gettext('Edit Reseller');
        $data['back_flag'] = true;
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
        if ($accountinfo['type'] == - 1) {
            $where = array(
                'id' => $edit_id
            );
        } else {
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller_id
            );
        }
        $account = $this->db_model->getSelect("*", "accounts", $where);
        $data["account_data"] = $account->result_array();

        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        if (count($edit_data) == 0) {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'accounts/reseller_list/');
            exit();
        }

        $taxes_data = $this->db_model->getSelect("group_concat(taxes_id) as taxes_id", "taxes_to_accounts", array(
            "accountid" => $edit_id
        ));
        if (isset($taxes_data) && $taxes_data->num_rows() > 0) {
            $taxes_data = $taxes_data->result_array();
            $edit_data["tax_id"] = explode(",", $taxes_data[0]['taxes_id']);
        }
        $encrypted_string = $this->common->encode($edit_data['id']);
        $encrypt = $this->common->encode_params($encrypted_string);
        $edit_data['registration_url'] = base_url() . "signup/" . $encrypt;
        $edit_data['password'] = ''; // not readable, the form shows an empty field
        $edit_data['credit_limit'] = $this->common_model->calculate_currency(($edit_data['credit_limit']), '', '', true, false);

        $entity_name = strtolower($this->common->get_entity_type('', '', $edit_data['type']));
        $data['edit_id'] = $edit_id;
        $data['entity_name'] = $entity_name;
        $data['invoice_date'] = $edit_data['invoice_day'];
        $data['form'] = $this->form->build_form($this->accounts_form->get_form_reseller_fields($edit_id), $edit_data);
        $data['reseller_id'] = $edit_data['reseller_id'];
        $this->load->view('view_reseller_details', $data);
    } else {
        $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
        redirect(base_url() . 'accounts/reseller_list/');
        exit();
    }
}

function reseller_save()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $add_array = $this->input->post();
        // this form creates resellers only (any type from the hidden field was stored)
        $add_array['type'] = '1';
        $entity_name = strtolower($this->common->get_entity_type('', '', $add_array['type']));
        $data['country_id'] = $add_array['country_id'];
        $data['timezone_id'] = $add_array['timezone_id'];
        $data['currency_id'] = $add_array['currency_id'];
        $data['entity_name'] = $entity_name;
        $data['edit_id'] = $add_array['id'];
        $data['form'] = $this->form->build_form($this->accounts_form->get_form_reseller_fields($add_array['id']), $add_array);

        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Reseller');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                $add_array['credit_limit'] = $this->common_model->add_calculate_currency($add_array['credit_limit'], '', '', false, false);

                $query = $this->accounts_model->remove_all_account_tax($add_array['id']);
                if (isset($add_array['tax_id'])) {
                    foreach ($add_array['tax_id'] as $key => $val) {
                        $data1 = array(
                            'accountid' => $add_array['id'],
                            'taxes_id' => $val
                        );
                        $this->accounts_model->add_account_tax($data1);
                    }
                    unset($add_array['tax_id']);
                }

                unset($add_array['number'], $add_array['registration_url']);
                $reseller_id_list = $this->common->get_subreseller_info($add_array['id']);
                $reseller_id_list = rtrim($reseller_id_list, ',');
                $this->db->select('posttoexternal');
                $posttoexternal = (array) $this->db->get_where("accounts", array(
                    "id" => $add_array['id']
                ))->first_row();
                if ($posttoexternal['posttoexternal'] == 0) {
                    $add_array['credit_limit'] = 0;
                }
                $this->db->select('reseller_id');
                $reseller_id = (array) $this->db->get_where("accounts", array(
                    "id" => $add_array['id']
                ))->first_row();
                unset($add_array['is_distributor']);
                // edit_account() writes the whole POST array - not the account type, the owner,
                // internal columns or the balance (changed through payments only, as for customers)
                unset($add_array['type'], $add_array['reseller_id'], $add_array['deleted'], $add_array['creation'], $add_array['balance'], $add_array['last_bill_date'], $add_array['inuse']);
                $this->common->password_from_form($add_array); // hash, empty keeps the password
                $this->accounts_model->edit_account($add_array, $add_array['id']);
                $this->session->set_flashdata('astpp_errormsg', gettext('Reseller updated successfully!'));
                redirect(base_url() . 'accounts/reseller_list/');
                exit();
            }
            $data["account_data"]["0"] = $add_array;
            $edit_id = $add_array["id"];
            
            $data["sipiax_grid_field"] = json_decode($this->accounts_form->build_sipiax_list_for_customer());

            $this->load->module('did/did');
            $data['did_grid_fields'] = $this->did->did_form->build_did_list_for_customer($edit_id, "reseller");
            $data['didlist'] = form_dropdown('free_did_list', $this->db_model->build_dropdown("id,number", "dids", "accountid", "0"), '');

            $this->load->module('invoices/invoices');
            $data['invoice_grid_fields'] = $this->invoices->invoices_form->build_invoices_list_for_admin();

            $this->load->module('reports/reports');
            $data['cdrs_grid_fields'] = $this->reports->reports_form->build_report_list_for_user();
            $this->load->view('view_reseller_details', $data);
        } else {

            $data['page_title'] = gettext('Create Reseller');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                // a parent chosen by the administrator must be an existing reseller
                if (isset($add_array['reseller_id']) && $add_array['reseller_id'] > 0 && $this->db->get_where('accounts', array('id' => (int) $add_array['reseller_id'], 'type' => 1, 'deleted' => 0))->num_rows() === 0) {
                    $add_array['reseller_id'] = 0;
                }
                unset($add_array['deleted']);
                if ($add_array['reseller_id'] > 0) {
                    $this->db->select("is_distributor");
                    $is_distributor = (array) $this->db->get_where("accounts", array(
                        "id" => $add_array['reseller_id']
                    ))->first_row();
                    $add_array['is_distributor'] = $is_distributor['is_distributor'];
                }
                $this->common->password_from_form($add_array); // hash, empty keeps the password
                $add_array['credit_limit'] = $this->common_model->add_calculate_currency($add_array['credit_limit'], '', '', false, false);
                $add_array['notification_email'] = (! empty($add_array['notification_email'])) ? $add_array['notification_email'] : $add_array['email'];
                $last_id = $this->accounts_model->add_account($add_array);
                $this->session->set_flashdata('astpp_errormsg', gettext('Reseller Added Successfully!'));
                redirect(base_url() . 'accounts/reseller_list/');
                exit();
            }
            $this->load->view('view_accounts_create', $data);
        }
    } else {
        redirect(base_url() . 'accounts/reseller_list/');
    }
}

function customer_generate_password()
{
    echo $this->common->generate_password();
}

function customer_generate_number($digit = '')
{
    if (isset(common_model::$global_config['system_config']['minimum_accountlength'])) {
        echo $this->common->find_uniq_rendno_customer_length(common_model::$global_config['system_config']['minimum_accountlength'], common_model::$global_config['system_config']['maximum_accountlength'], 'number', 'accounts');
    } else {
        echo $this->common->find_uniq_rendno_customer(common_model::$global_config['system_config']['cardlength'], 'number', 'accounts');
    }
}

function customer_sipdevice_number($digit = '')
{
    echo $this->common->find_uniq_rendno($digit, 'number', 'accounts');
}

function customer_sipdevice_random_password()
{
    echo $this->common->generate_password();
}

function customer_sipdevice_voicemail_random_password($digit = '')
{
    $digit = max(1, min(9, (int) $digit));
    echo random_int((int) pow(10, $digit - 1), (int) pow(10, $digit) - 1);
}

function customer_generate_pin($digit = '')
{
    $numberlength = common_model::$global_config['system_config']['pinlength'];
    $numberlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['pinlength'];
    echo $this->common->find_uniq_rendno_customer($numberlength, 'number', 'accounts');
}

function admin_add($type = 2)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $entity_type = strtolower($this->common->get_entity_type('', '', $type));
    $entitytype = str_replace(' ', '', $entity_type);
    $data['username'] = $this->session->userdata('user_name');
    $data['flag'] = 'create';
    $data['page_title'] = gettext('Create ' . ucfirst($entity_type));
    $data['back_flag'] = true;
    $data['form'] = $this->form->build_form($this->accounts_form->get_form_admin_fields($entitytype), '');
    $data['country_id'] = $accountinfo['country_id'];
    $data['currency_id'] = $accountinfo['currency_id'];
    $data['timezone_id'] = $accountinfo['timezone_id'];
    if (! $data['timezone_id']) {
        $data['timezone_id'] = 1;
    }
    if (! $data['currency_id']) {
        $data['currency_id'] = 1;
    }
    if (! $data['country_id']) {
        $data['country_id'] = 1;
    }
    $data['entity_name'] = $entity_type;
    $this->load->view('view_accounts_create', $data);
}

function admin_edit($edit_id = '')
{
    if ((! empty($edit_id)) && (isset($edit_id))) {
        $access_edit = (array) $this->db_model->getSelect("deleted", "accounts", array(
            "id" => $edit_id
        ))->first_row();
    }
    if ((isset($access_edit)) && ($access_edit['deleted'] == 0)) {
        $data['back_flag'] = true;
        $accountinfo = (array) $this->db->get_where('accounts', array(
            "id" => $edit_id
        ))->first_row();
        $type = $accountinfo['type'] == - 1 ? 2 : $accountinfo['type'];
        $entity_type = strtolower($this->common->get_entity_type('', '', $type));
        $entitytype = str_replace(' ', '', $entity_type);
        $accountinfo['password'] = ''; // not readable, the form shows an empty field
        $admin_type = $this->common->get_field_name('type', 'accounts', array(
            'id' => $edit_id
        ));
        $data['form'] = $this->form->build_form($this->accounts_form->get_form_admin_fields($entitytype, $edit_id, $admin_type), $accountinfo);
        $data['page_title'] = gettext('Edit ' . ucfirst($entity_type));
        $this->load->view('view_admin_details', $data);
    } else {
        $this->session->set_flashdata('astpp_notification', gettext('Permission Denied'));
        redirect(base_url() . 'accounts/admin_list/');
        exit();
    }
}

function admin_save($add_array = false)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $add_array = $this->input->post();
        $accountinfo = $this->session->userdata('accountinfo');
        $type = $add_array['type'] == - 1 ? 2 : $add_array['type'];
        $entity_type = strtolower($this->common->get_entity_type('', '', $type));
        $entitytype = str_replace(' ', '', $entity_type);
        
        $data['entity_name'] = $entitytype;
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Create ' . $entity_type);
        $data['form'] = $this->form->build_form($this->accounts_form->get_form_admin_fields($entitytype, $add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit ' . $entity_type);
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                $this->common->password_from_form($add_array); // hash, empty keeps the password
                if ($add_array['type'] == - 1) {
                    unset($add_array['status']);
                }
                unset($add_array['number']);

                $this->accounts_model->edit_account($add_array, $add_array['id']);
                if ($add_array['id'] == $accountinfo['id']) {
                    $result = $this->db->get_where('accounts', array(
                        'id' => $add_array['id']
                    ));
                    $result = $result->result_array();
                    $this->session->set_userdata('accountinfo', $result[0]);
                }
                $this->session->set_flashdata('astpp_errormsg', gettext(ucfirst($entity_type)).' '.gettext('updated successfully!'));

                redirect(base_url() . 'accounts/admin_list/');
                exit();
            }
            $this->load->view('view_admin_details', $data);
        } else {
            $data['page_title'] = gettext('Create ' . ucfirst($entity_type));
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                $this->common->password_from_form($add_array); // hash, empty keeps the password
                $add_array['notification_email'] = (! empty($add_array['notification_email'])) ? $add_array['notification_email'] : $add_array['email'];
                $last_id = $this->accounts_model->add_account($add_array);
                $this->session->set_flashdata('astpp_errormsg', gettext(ucfirst($entity_type)) .' '.gettext('Added Successfully!'));
                redirect(base_url() . 'accounts/admin_list/');
                exit();
            }
            $this->load->view('view_accounts_create', $data);
        }
    } else {
        redirect(base_url() . 'accounts/admin_list/');
    }
}

function subadmin_add($type = "")
{
    $this->admin_add(4);
}

function subadmin_edit($edit_id = '')
{
    $this->admin_edit($edit_id);
}

function subadmin_save()
{
    $add_array = $this->input->post();
    $this->admin_save($add_array);
}

function chargelist_json($accountid)
{
    $json_data = array();
    $sweeplist = $this->common_model->get_sweep_list();

    $select = "charges.description,charges.charge,charges.sweep";
    $table = "charges";
    $jionTable = array(
        'charge_to_account',
        'accounts'
    );
    $jionCondition = array(
        'charges.id = charge_to_account.charge_id',
        'accounts.number = charge_to_account.cardnum'
    );
    $type = array(
        'left',
        'inner'
    );
    $where = array(
        'accounts.accountid' => $accountid
    );
    $order_type = 'charges.id';
    $order_by = "ASC";

    $account_charge_count = $this->db_model->getCountWithJion($table, $select, $where, $jionTable, $jionCondition, $type);

    $count_all = $account_charge_count;
    $config['total_rows'] = $count_all;
    $config['per_page'] = $_GET['rp'];

    $page_no = $_GET['page'];
    $json_data['page'] = $page_no;

    $json_data['total'] = $config['total_rows'];
    $perpage = $config['per_page'];
    $start = ($page_no - 1) * $perpage;
    if ($start < 0)
        $start = 0;

    $account_charge_list = $this->db_model->getAllJionQuery($table, $select, $where, $jionTable, $jionCondition, $type, $perpage, $start, $order_by, $order_type, "");
    if ($account_charge_list->num_rows() > 0) {
        foreach ($account_charge_list->result() as $key => $charges_value) {
            $json_data['rows'][] = array(
                'cell' => array(
                    $charges_value->description,
                    $charges_value->charge,
                    $sweeplist[$charges_value->sweep]
                )
            );
        }
    }
    echo json_encode($json_data);
}

function admin_list()
{
    $data['username'] = $this->session->userdata('user_name');
    $data['page_title'] = gettext('Admins');
    $data['search_flag'] = true;
    $data['cur_menu_no'] = 1;
    $this->session->set_userdata('advance_search', 0);
    $data['grid_fields'] = $this->accounts_form->build_account_list_for_admin();
    $data["grid_buttons"] = $this->accounts_form->build_grid_buttons_admin();
    $data['form_search'] = $this->form->build_serach_form($this->accounts_form->get_admin_search_form());
    $this->load->view('view_accounts_list', $data);
}

function admin_list_json()
{
    $json_data = array();
    $account_data = $this->session->userdata("accountinfo");
    $reseller_id = $account_data['type'] == 1 ? $account_data['id'] : 0;
    $count_all = $this->accounts_model->get_admin_Account_list(false, '', '', $reseller_id);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->accounts_model->get_admin_Account_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $reseller_id);
    $grid_fields = json_decode($this->accounts_form->build_account_list_for_admin());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

    echo json_encode($json_data);
}

function customer_batch_update()
{
    $batch_update_arr = $this->input->post();
    $result = $this->accounts_model->customer_rates_batch_update($batch_update_arr);
    echo json_encode(array(
        "SUCCESS" => gettext("Customer batch updated successfully!")
    ));
    exit();
}

function reseller_list()
{
    $data['username'] = $this->session->userdata('user_name');
    $data['page_title'] = gettext('Resellers');
    $data['search_flag'] = true;
    $data['batch_update_flag'] = true;
    $this->session->set_userdata('advance_search', 0);
    $data['grid_fields'] = $this->accounts_form->build_account_list_for_reseller();
    $data["grid_buttons"] = $this->accounts_form->build_grid_buttons_reseller();
    $data['form_search'] = $this->form->build_serach_form($this->accounts_form->get_reseller_search_form());
    $data['form_batch_update'] = $this->form->build_batchupdate_form($this->accounts_form->reseller_batch_update_form());
    $this->load->view('view_accounts_list', $data);
}

function reseller_list_json()
{
    $json_data = array();
    $count_all = $this->accounts_model->get_reseller_Account_list(false);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];

    $query = $this->accounts_model->get_reseller_Account_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    $grid_fields = json_decode($this->accounts_form->build_account_list_for_reseller());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

    echo json_encode($json_data);
}

function reseller_list_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action']);
        unset($action['advance_search']);
        if (isset($action['balance']['balance']) && $action['balance']['balance'] != '') {
            $action['balance']['balance'] = $this->common_model->add_calculate_currency($action['balance']['balance'], "", '', true, false);
        }
        if (isset($action['credit_limit']['credit_limit']) && $action['credit_limit']['credit_limit'] != '') {
            $action['credit_limit']['credit_limit'] = $this->common_model->add_calculate_currency($action['credit_limit']['credit_limit'], "", '', true, false);
        }
        $this->session->set_userdata('reseller_list_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'accounts/reseller_list/');
    }
}

function reseller_batch_update()
{
    $batch_update_arr = $this->input->post();
    $result = $this->accounts_model->reseller_rates_batch_update($batch_update_arr);
    echo json_encode(array(
        "SUCCESS" => gettext("Reseller batch updated successfully!")
    ));
    exit();
}

function admin_list_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('admin_list_search', "");
}

function admin_list_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action']);
        unset($action['advance_search']);
        $this->session->set_userdata('admin_list_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'accounts/admin_list/');
    }
}

function reseller_list_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('reseller_list_search', "");
}

function customer_delete($id)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
    $where = array(
        'id' => $id,
        "reseller_id" => $reseller_id
    );
    $account_res = $this->db_model->getSelect("type", "accounts", $where);
    if ($account_res->num_rows > 0) {
        $this->common->customer_delete_dependencies($id);
        $this->session->set_flashdata('astpp_notification', gettext('Customer removed successfully!'));
    } else {
        $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
    }
    redirect(base_url() . 'accounts/customer_list/');
}

function reseller_delete($id)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
    $where = array(
        'id' => $id,
        "reseller_id" => $reseller_id
    );
    $account_res = $this->db_model->getSelect("type", "accounts", $where);
    if ($account_res->num_rows > 0) {
        $this->common->subreseller_list($id);
        $this->session->set_flashdata('astpp_notification', gettext('Reseller removed successfully!'));
    } else {
        $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
    }
    redirect(base_url() . 'accounts/reseller_list/');
}

function free_customer_did($accountid)
{
    $this->db->where(array(
        "accountid" => $accountid
    ));
    $this->db->update("dids", array(
        'accountid' => "0"
    ));
    return true;
}

function free_ani_map($accountid)
{
    $this->db->where(array(
        "accountid" => $accountid
    ));
    $this->db->delete('ani_map');
    return true;
}

function free_reseller_did($ids)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $reseller_id = $accountinfo['type'] != 1 ? 0 : $accountinfo['id'];
    $data = array(
        'parent_id' => $reseller_id,
        'accountid' => 0
    );
    $where = "parent_id IN ($ids)";
    $this->db->where($where);
    $this->db->update('dids', $data);
    $where = "reseller_id IN ($ids)";
    $this->db->where($where);
    $this->db->delete('reseller_pricing');
    return true;
}

function provider_delete($id)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $where = array(
        'id' => $id,
        "reseller_id" => 0
    );
    $account_res = $this->db_model->getSelect("type", "accounts", $where);
    if ($account_res->num_rows > 0) {
        $this->common->customer_delete_dependencies($id, 3);
        $this->session->set_flashdata('astpp_notification', gettext('Provider removed successfully!'));
    }
    redirect(base_url() . 'accounts/customer_list/');
}

function admin_delete($id)
{
    $this->accounts_model->remove_customer($id, 2);
    $this->session->set_flashdata('astpp_notification', gettext('Admin removed successfully!'));
    redirect(base_url() . 'accounts/admin_list/');
}

function subadmin_delete($id)
{
    $this->accounts_model->remove_customer($id, 4);
    $this->session->set_flashdata('astpp_notification', gettext('Sub admin removed successfully!'));
    redirect(base_url() . 'accounts/admin_list/');
}

function reseller_details_json($module, $accountid)
{
    if ($module == "did") {
        $this->load->module('did/did');
        $this->did->reseller_did($accountid, "reseller");
    }
    if ($module == "invoices") {
        $this->load->module('invoices/invoices');
        $this->invoices->customer_invoices($accountid, "reseller");
    }
    if ($module == "charges") {
        $this->load->module('charges/charges');
        $this->charges->customer_charge_list($accountid, "reseller");
    }
    if ($module == 'packages') {
        $this->load->module('package/package');
        $this->package->package_list_reseller($accountid, "reseller");
    }
}

function provider_details_json($module, $accountid)
{
    if ($module == "invoices") {
        $this->load->module('invoices/invoices');
        $this->invoices->customer_invoices($accountid);
    }
}

function customer_add_postcharges($accounttype, $accountid)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
    $where = array(
        'id' => $edit_id,
        "reseller_id" => $reseller_id
    );
    $account_res = $this->db_model->getSelect("type", "accounts", $where);
    if ($account_res->num_rows() > 0) {
        $charge = $this->input->post("amount", true);
        if ($charge != "") {
            $charge = $this->common_model->add_calculate_currency($charge, "", '', false, false);
            $date = date('Y-m-d H:i:s');
            $insert_arr = array(
                "accountid" => $accountid,
                "description" => $this->input->post("desc", true),
                "created_date" => $date,
                "debit" => $charge,
                "charge_type" => "post_charge"
            );
            $this->db->insert("invoice_item", $insert_arr);
            $this->accounts_model->update_balance($charge, $accountid, "debit");
            redirect(base_url() . "accounts/" . $accounttype . "_edit/$accountid#packages");
        } else {
            redirect(base_url() . "accounts/" . $accounttype . "_edit/$accountid#packages");
        }
    } else {
        $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
        redirect(base_url() . "accounts/" . $accounttype . "_list/");
    }
}

function reseller_did_action($action, $accountid, $accounttype, $did_id = "")
{
    $did_id = $this->input->post("free_did_list", true);
    $accountinfo = $this->session->userdata('accountinfo');
    if ($action == "add" && ! empty($did_id)) {
        $account_query = $this->db_model->getSelect("*", "accounts", array(
            "id" => $accountid
        ));
        $account_arr = $account_query->result_array();
        $idofaccount = $accountid;
        $this->db_model->update("dids", array(
            "parent_id" => $accountid,
            'assign_date' => gmdate('Y-m-d H:i:s')
        ), array(
            "id" => $did_id
        ));
        $accountid = $idofaccount;
        $this->load->module('did/did');
        $this->did->did_model->add_reseller_pricing($accountid, $did_id);

        $this->session->set_flashdata('astpp_errormsg', gettext('DID added successfully.'));
        redirect(base_url() . "accounts/" . $accounttype . "_edit/$accountid#did");
    } else {
        redirect(base_url() . "accounts/" . $accounttype . "_edit/$accountid#did");
    }
    if ($action == "delete") {
        $this->db->where('id', $did_id);
        $this->db->select('note');
        $pricing_res = $this->db->get('reseller_pricing');
        if ($pricing_res->num_rows() > 0) {
            $pricing_res = $pricing_res->result_array();
            $did_number = $pricing_res[0]['note'];
            $accountinfo = $this->session->userdata('accountinfo');
            if ($this->session->userdata['userlevel_logintype'] == - 1) {
                $parent_id = 0;
            } else {
                $parent_id = $accountinfo['reseller_id'];
            }

            $reseller_ids = $this->common->subreseller_list($accountinfo['id']);
            $pricing_where = "parent_id = $parent_id AND note = $did_number";
            $this->db->where($pricing_where);
            $this->db->delete('reseller_pricing');
            $dids_where = "parent_id IN ($reseller_ids) AND number = $did_number";
            $this->db->where($dids_where);
            $data = array(
                'accountid' => 0,
                'parent_id' => $accountinfo['id']
            );
            $this->db->update('dids', $data);
            $this->session->set_flashdata('astpp_notification', gettext('DID removed successfully.'));
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('DID already removed before.'));
        }
        redirect(base_url() . "accounts/" . $accounttype . "_edit/$accountid#did");
    }
}

function customer_selected_delete()
{
    $ids = $this->input->post("selected_ids", true);
    $customer_ids = explode(",", $ids);
    foreach ($customer_ids as $customer_id) {
        $customer_id = str_replace("'", "", $customer_id);
        $this->common->customer_delete_dependencies($customer_id);
    }
    echo TRUE;
}

function reseller_selected_delete()
{
    $ids = $this->input->post("selected_ids", true);
    $id_arr = explode(',', $ids);
    foreach ($id_arr as $data) {
        $data = str_replace("'", "", $data);
        $this->common->subreseller_list($data);
    }
    echo TRUE;
}

function callshop_selected_delete()
{
    echo $this->delete_multiple();
}

function provider_selected_delete()
{
    echo $this->delete_multiple();
}

function subadmin_selected_delete()
{
    echo $this->delete_multiple();
}

function admin_selected_delete()
{
    echo $this->delete_multiple();
}

function delete_multiple()
{
    $ids = $this->input->post("selected_ids", true);
    $where = "id IN ($ids)";
    $data = array(
        'deleted' => 1,
        'deleted_date' => gmdate('Y-m-d H:i:s')
    );
    $this->db->where($where);
    $this->db->where('type <>', '-1');
    $this->db->update("accounts", $data);
    echo TRUE;
}

function customer_account_taxes($action = false, $id = false)
{
    $data['username'] = $this->session->userdata('user_name');
    $data['page_title'] = gettext('Account Taxes');

    if ($action == false)
        $action = "list";

    if ($action == 'list') {
        $this->load->view('view_account_taxes_list', $data);
    } elseif ($action == 'add') {

        if (($this->input->post())) {
            $post_array = $this->input->post();
            $query = $this->accounts_model->remove_all_account_tax($post_array['account_id']);

            foreach ($post_array as $key => $value) {
                $id = explode("_", $key);
                if ($id[0] == 'tax') {
                    $data = array(
                        'accountid' => $post_array['account_id'],
                        'taxes_id' => $post_array[$key]
                    );
                    $this->accounts_model->add_account_tax($data);
                }
            }
            $this->session->set_flashdata('astpp_errormsg', gettext('Account tax added successfully!'));
            redirect(base_url() . 'accounts/customer_list/');
        }
        $data['id'] = array();
        $data['taxesList'] = $this->common_model->get_list_taxes();
        $this->load->view('view_accounting_taxes_add', $data);
    } elseif ($action == 'edit') {
        $taxes_id = $this->accounts_model->get_accounttax_by_id($id);
        $account_num = $this->accounts_model->get_account_number($id);
        $data['accountnum'] = $account_num['number'];
        $data['account_id'] = $id;
        for ($i = 0; $i < count($taxes_id); $i ++) {
            $tax_ids[] = $taxes_id[$i]['taxes_id'];
        }
        $data['tax_ids'] = $tax_ids;

        $data['tax_id'] = $taxes_id;

        if (($this->input->post())) {
            $post_array = $this->input->post();
            $accountinfo = $this->accounts_model->get_account_by_number($post_array['account_id']);
            $query = $this->accounts_model->remove_all_account_tax($post_array['account_id']);
            foreach ($post_array as $key => $value) {
                $id = explode("_", $key);
                if ($id[0] == 'tax') {
                    $data = array(
                        'accountid' => $post_array['account_id'],
                        'taxes_id' => $post_array[$key]
                    );
                    $this->accounts_model->add_account_tax($data);
                }
            }
            if ($accountinfo['type'] == '0') {
                $link = base_url() . '/accounts/customer_list/';
            } else {
                $link = base_url() . '/accounts/reseller_list/';
            }
            $this->session->set_flashdata('astpp_errormsg', gettext('Account tax added successfully!'));
            redirect($link);
        }
        $data['taxesList'] = $this->common_model->get_list_taxes();
        $this->load->view('view_accounting_taxes_add', $data);
    } elseif ($action == 'delete') {
        $this->accounting_model->remove_account_tax($id);
        $this->session->set_flashdata('astpp_notification', gettext('Account tax removed successfully!'));
        redirect(base_url() . 'accounting/account_taxes/');
    }
}

function valid_account_tax()
{
    $tax_id = '';
    if (! empty($_POST['username'])) {
        $account_num = mysqli_real_escape_string(get_instance()->db->conn_id, $_POST['username']);
        $row = $this->accounts_model->check_account_num($account_num);
        if (isset($row['accountid']) && $row['accountid'] != '') {
            $taxes_id = $this->accounts_model->get_accounttax_by_id($row['accountid']);
            if ($taxes_id) {
                foreach ($taxes_id as $id) {
                    $tax_id .= $id['taxes_id'] . ",";
                }

                $tax_id = rtrim($tax_id, ",");
                echo $row['accountid'] . ',' . $tax_id;
            } else {
                echo $row['accountid'];
            }
        }
    }
}

function reseller_edit_account()
{
    $account_data = $this->session->userdata("accountinfo");

    $add_array = $this->input->post();
    $data['form'] = $this->form->build_form($this->accounts_form->get_reseller_own_form_fields(), $add_array);
    if ($add_array['id'] != '') {
        $data['page_title'] = gettext('Edit Reseller');
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
        } else {
            $this->accounts_model->edit_account($add_array, $add_array['id']);
            $accountinfo = $this->session->userdata('accountinfo');
            if ($add_array['id'] == $accountinfo['id']) {
                $result = $this->db->get_where('accounts', array(
                    'id' => $add_array['id']
                ));
                $result = $result->result_array();
                $this->session->set_userdata('accountinfo', $result[0]);
            }
            $this->session->set_flashdata('astpp_errormsg', gettext('Reseller updated successfully!'));
            redirect(base_url() . '/dashboard/');
        }
        $this->load->view('view_reseller_edit_details_own', $data);
    } else {
        $data['page_title'] = gettext('Edit Reseller');
        $where = array(
            'id' => $account_data["id"]
        );
        $account = $this->db_model->getSelect("*", "accounts", $where);
        $data["account_data"] = $account->result_array();
        foreach ($account->result_array() as $key => $value) {
            $editable_data = $value;
        }
        $data['form'] = $this->form->build_form($this->accounts_form->get_reseller_own_form_fields(), $editable_data);
        $this->load->view('view_reseller_edit_details_own', $data);
    }
}

function customer_animap_list($id = '')
{
    $data['animap_id'] = $id;
    $data['username'] = $this->session->userdata('user_name');
    $data['page_title'] = gettext("Caller Id List");
    $this->session->set_userdata('animap_search', 0);
    $data['grid_fields'] = $this->accounts_form->build_animap_list();
    $data["grid_buttons"] = $this->accounts_form->build_grid_buttons_destination();
    $this->load->view('view_ani_map', $data);
}

function customer_animap_list_json($id = '')
{
    $json_data = array();
    $count_all = $this->accounts_model->get_animap(false, '', '', $id);
    $data['callingcard_id'] = $id;
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->accounts_model->get_animap(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $id);
    $grid_fields = json_decode($this->accounts_form->build_animap_list());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
    exit();
}

function customer_animap_list_action($id = '')
{
    $add_array = $this->input->post();
    $add_array['id'] = trim($add_array['id']);
    $add_array['number'] = trim($add_array['number']);
    if (isset($add_array['id']) && $add_array['id'] != '') {
        $add_array['id'] = trim($add_array['id']);
        $id = $add_array['id'];
    }
    $where = array(
        "number" => $add_array['number']
    );
    $pro = $this->accounts_model->animap_authentication($where, $id);
    if ($pro > 0) {
        echo "2";
        exit();
    }
    if (isset($add_array['number']) && ! empty($add_array['number'])) {
        if (isset($add_array['id']) && $add_array['id'] != '') {
            unset($add_array['animap_id']);
            $response = $this->accounts_model->edit_animap($add_array, $add_array['id']);
            echo "1";
            exit();
        } else {
            $add_array['context'] = "default";
            unset($add_array['animap_id']);
            $add_array['accountid'] = $id;
            $response = $this->accounts_model->add_animap($add_array);
            echo "0";
            exit();
        }
    } else {
        echo "3";
        exit();
    }
}

function customer_animap_list_remove($id)
{
    $this->accounts_model->remove_ani_map($id);
    echo "1";
    exit();
}

function customer_animap_list_edit($id)
{
    $where = array(
        'id' => $id
    );
    $account = $this->db_model->getSelect("*", "ani_map", $where);
    foreach ($account->result_array() as $key => $value) {
        $edit_data = $value;
    }
    $value_edit = '';
    foreach ($edit_data as $key => $value) {
        $value_edit .= $value . ",";
    }
    echo rtrim($value_edit, ',');
    exit();
}

function provider_edit_account()
{
    $this->customer_edit_account();
}

/*
 * returned the decoded portal password of an account (no page calls it).
 * Portal passwords are not readable any more.
 */
function customer_show_password($id)
{
    show_404();
}

function reseller_export_cdr_xls()
{
    $account_info = $accountinfo = $this->session->userdata('accountinfo');
    $currency_id = $account_info['currency_id'];
    $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
    ob_clean();
    $query = $this->accounts_model->get_reseller_Account_list(true, '', '', true);
    $customer_array[] = array(
        gettext("Account"),
        gettext("First Name"),
        gettext("Last Name"),
        gettext("Company"),
        gettext("Rate Group"),
        gettext("Account Type"),
        gettext("Balance") . "(" . $currency . ")",
        gettext("Credit Limit") . "(" . $currency . ")",
        gettext("Status"),
        gettext("Created Date")
    );
    if ($query->num_rows() > 0) {

        foreach ($query->result_array() as $row) {
            $customer_array[] = array(
                $row['number'],
                $row['first_name'],
                $row['last_name'],
                $row['company_name'],
                $this->common->get_field_name('name', 'pricelists', $row['pricelist_id']),
                $this->common->get_account_type('', '', $row['posttoexternal']),
                $this->common_model->calculate_currency($row['balance'], false, false),
                $this->common_model->calculate_currency($row['credit_limit'], false, false),
                $this->common->get_status('export', '', $row['status']),
                $row['creation']
            );
        }
    }

    $this->load->helper('csv');
    array_to_csv($customer_array, 'Resellers_' . date("Y-m-d") . '.csv');
}

function customer_validate_ip()
{
    $add_array = $this->input->post();
    if (! empty($add_array)) {
        // same form as stored by customer_ipmap_action(); not an IP / network = invalid
        $add_array['ip'] = $this->common->ipmap_cidr($add_array['ip']);
        if ($add_array['ip'] === false) {
            echo 'FALSE';
            return;
        }
        $this->db->where('ip', $add_array['ip']);
        $this->db->where('prefix', $add_array['prefix']);
        $this->db->select('count(ip) as count');
        $ip_map_result = (array) $this->db->get('ip_map')->first_row();
        if ($ip_map_result['count'] > 0) {
            echo 'FALSE';
        } else {
            echo 'TRUE';
        }
    } else {
        echo 'FALSE';
    }
}

function customer_global_grid_list()
{
    echo gettext($_POST['display']);
}

function customer_permission_list()
{
    $button_array = $this->input->post();
    $permissioninfo = $this->session->userdata('permissioninfo');
    $currnet_url = $button_array['current_url'];
    $url_explode = explode('/', $currnet_url);
    $module_name = $url_explode[3];
    $sub_module_name = $url_explode[4];
    $logintype = $this->session->userdata('logintype');
    if ((isset($permissioninfo[$module_name][$sub_module_name][$button_array['button_name']]) && $permissioninfo[$module_name][$sub_module_name][$button_array['button_name']] == 0) or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3') {
        echo 0;
    } else {
        echo 1;
    }
}

function customer_rategroup_change()
{
    $sub_res_id = $_POST['sub_reseller_id'];
    $pricelists = $this->db_model->getSelect("*", "pricelists", array(
        'reseller_id' => $sub_res_id
    ));
    if ($pricelists->num_rows > 0) {
        $pricelists_data = $pricelists->result_array();
        echo '<select>';
        foreach ($pricelists_data as $value) {
            echo "<option value=" . $value['id'] . ">" . $value['name'] . "</option>";
        }
        echo '</select>';
    } else {
        echo "<select><option>--</option></select>";
    }
}

function customer_account_change($reseller_id)
{
    $accounts = $this->db_model->getSelect("*", "accounts", array(
        'reseller_id' => $reseller_id,
        'type' => 0,
        'deleted' => 0,
        'status' => 0
    ));
    if ($accounts->num_rows > 0) {
        $accounts_data = $accounts->result_array();
        foreach ($accounts_data as $value) {
            if(isset($value['company_name']) && $value['company_name'] != ''){
                echo "<option value=" . $value['id'] . ">" . $value['company_name'] . "(" . $value['number'] . ")</option>";
            }else{
                echo "<option value=" . $value['id'] . ">" . $value['first_name'] . " " . $value['last_name'] . "(" . $value['number'] . ")</option>";
            }
        }
    } else {
        echo '<select><option value="">--Select--</option></select>';
    }
}

function customer_product($edit_id)
{
    $data['page_title'] = gettext("Products");
    $accountinfo = $this->session->userdata('accountinfo');
    $reseller_id = ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) ? $accountinfo['id'] : 0;
    if ($accountinfo['type'] == - 1) {
        $where = array(
            'id' => $edit_id
        );
    } else {
        $where = array(
            'id' => $edit_id,
            "reseller_id" => $reseller_id
        );
    }
    $account_res = $this->db_model->getSelect("type", "accounts", $where);

    if ($account_res->num_rows > 0) {
        $account_data = (array) $account_res->first_row();
        $accounttype = strtolower($this->common->get_entity_type('', '', $account_data['type']));

        $data['productslist'] = form_dropdown_all(array(
            "name" => 'applayable_product',
            'id' => 'applayable_product',
            'class' => "applayable_product"
        ), $this->Astpp_common->list_applyable_products($edit_id), '');
        $this->load->module('products/products');
        $data['grid_fields'] = $this->products->product_form->build_products_list_for_customer($edit_id, $accounttype);

        $data['edit_id'] = $edit_id;
        $data['accounttype'] = $accounttype;
        $this->load->view('view_customer_products', $data);
    } else {
        redirect(base_url() . 'accounts/customer_list/');
        exit();
    }
}

function customer_products_action($action, $accountid, $accounttype, $product_id = "")
{
    $post_array = $this->input->post();
    $accountinfo = $this->db_model->getSelect("*", "accounts", array(
        "id" => $accountid
    ));

    $data['account_arr'] = (array) $accountinfo->first_row();
    if ($action == "add") {
        $product_id = $this->input->post("applayable_product", true);
        $data['page_title'] = gettext("Assign Product");
        $date = gmdate('Y-m-d H:i:s');
        $product_info = $this->db_model->getSelect("*", "products", array(
            "id" => $product_id
        ));
        $data['product_info'] = (array) $product_info->first_row();
        // a reseller sells at its own offer: start the form with it, not with the admin's price
        // (an unchanged form sold at the admin's price)
        $me = $this->session->userdata('accountinfo');
        if (isset($me['type']) && ($me['type'] == 1 || $me['type'] == 5)) {
            $offer = (array) $this->db->get_where('reseller_products', array(
                'product_id' => $product_id,
                'account_id' => $me['id']
            ))->first_row();
            if (! empty($offer)) {
                $data['product_info']['price'] = $offer['price'];
                $data['product_info']['setup_fee'] = $offer['setup_fee'];
            }
        }
        $this->load->view("view_customer_orders_assign", $data);
    }

    if ($action == "delete") {

        $this->db->where("id", $accountid);
        $this->db->delete("order_items");
        $this->session->set_flashdata('astpp_notification', gettext('Product Removed Sucessfully.'));
    }
}

function customer_orders_save()
{
    $ProductData = $this->input->post();
    $account_id = $_POST['accountid'];
    $accountinfo = $this->session->userdata("accountinfo");
    $customer_data = $this->db_model->getSelect("*", "accounts", array(
        "id" => $ProductData['accountid'],
        "status" => 0,
        "deleted" => 0,
        "type" => 0
    ));
    if ($customer_data->num_rows > 0) {
        $customer_data = $customer_data->result_array()[0];
    }
    if ($ProductData['accountid'] == 0) {
        $this->form_validation->set_rules('accountant_name', 'Accounts', 'required|dropdown_required|xss_clean');
    }
    $this->form_validation->set_rules('setup_fee', 'Setup Fee', 'numeric|greater_than[-1]|min_length[1]|max_length[10]|required|xss_clean');
    $this->form_validation->set_rules('price', 'Price', 'numeric|greater_than[-1]|min_length[1]|max_length[10]|required|xss_clean');
    $this->form_validation->set_rules('billing_days', 'Billing Days', '|required|numeric|min_length[0]|max_length[10]|is_natural|xss_clean');
    $this->form_validation->set_rules('quantity', 'Quantity', 'numeric|is_natural|xss_clean');
    $this->form_validation->set_message('max_length', '%s field can not excced  numbers in length %s');

    // The seller may set price, setup fee and billing period for this customer (fields of the
    // assignment form); everything else comes from the product or the seller's offer. The balance
    // check uses the amount that confirm_order() really books (incl. quantity).
    $order_options = array(
        'overrides' => array(
            'price' => isset($ProductData['price']) ? $ProductData['price'] : '',
            'setup_fee' => isset($ProductData['setup_fee']) ? $ProductData['setup_fee'] : '',
            'billing_days' => isset($ProductData['billing_days']) ? $ProductData['billing_days'] : ''
        )
    );
    $unit_price = $this->order->unit_price($ProductData['product_id'], $account_id, $order_options['overrides']);
    $quantity = order::quantity(isset($ProductData['quantity']) ? $ProductData['quantity'] : '');
    $total_amt = ($unit_price === false || $quantity === false) ? false : $unit_price * $quantity;
    $account_balance = $customer_data['posttoexternal'] == 1 ? $customer_data['credit_limit'] - ($customer_data['balance']) : $customer_data['balance'];
    $category_id = $this->common->get_field_name('product_category', 'products', array(
        'id' => $ProductData['product_id']
    ));
    $ProductData['category_name'] = $this->common->get_field_name('name', 'category', array(
        'id' => $category_id
    ));
    $order_id = $this->common->get_field_name('order_id', 'order_items', array(
        'product_id' => $ProductData['product_id']
    ));

    $payment_gateway = $this->common->get_field_name('payment_gateway', 'orders', array(
        'id' => $order_id,
        'accountid' => $account_id
    ));

    $ProductData['payment_by'] = $payment_gateway;
    if ($this->form_validation->run() == FALSE) {
        $data['page_title'] = gettext("Assign Product");
        $date = gmdate('Y-m-d H:i:s');
        $product_info = $this->db_model->getSelect("*", "products", array(
            "id" => $ProductData['product_id']
        ));
        $data['product_info'] = (array) $product_info->first_row();
        $accountinfo = $this->db_model->getSelect("*", "accounts", array(
            "id" => $ProductData['accountid']
        ));
        $data['account_arr'] = (array) $accountinfo->first_row();
        $data['product_data'] = $ProductData;
        $data['validation_errors'] = validation_errors();
        $this->load->view("view_customer_orders_assign", $data);
    } else {

        if ($total_amt === false) {
            $this->session->set_flashdata('astpp_notification', gettext('Invalid price, quantity or product!'));
            redirect(base_url() . 'accounts/customer_product/' . $account_id . '/');
        }
        // ">=": a free package (price 0) could not be assigned to a prepaid customer with balance 0
        if ($account_balance >= $total_amt) {
            $last_id = $this->order->confirm_order($ProductData, $account_id, $accountinfo, $order_options);
            if ($last_id != "") {
                $ProductData['name'] = $this->common->get_field_name("name", "products", array(
                    "id" => $ProductData['product_id']
                ));
                $ProductData['next_billing_date'] = ($ProductData['billing_days'] = 0) ? gmdate('Y-m-d 23:59:59', strtotime('+10 years')) : gmdate("Y-m-d 23:59:59", strtotime("+" . ($ProductData['billing_days'] - 1) . " days"));
                $final_array = array_merge($customer_data, $ProductData);
                if (isset($category_id) && $category_id == 2) {
                    $final_array['quantity'] = isset($ProductData['quantity']) ? $ProductData['quantity'] : 1;
                } else {
                    $final_array['quantity'] = 1;
                }
                $final_array['total_price'] = ($ProductData['setup_fee'] + $ProductData['price']) * ($final_array['quantity']);
                $final_array['price'] = ($ProductData['setup_fee'] + $ProductData['price']);
                $this->common->mail_to_users('product_purchase', $final_array);
            }
            $this->session->set_flashdata('astpp_errormsg', gettext('Product assigned successfully!'));
            redirect(base_url() . 'accounts/customer_product/' . $account_id . '/');
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Insufficient balance to assign product!'));
            redirect(base_url() . 'accounts/customer_product/' . $account_id . '/');
        }
    }
}

function customer_pricelist()
{
    $add_array = $this->input->post();
    $reseller_id = $add_array['reseller_id'];
    $accountinfo = $this->session->userdata("accountinfo");
    $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : $reseller_id;
    $pricelist_result = $this->db->get_where('pricelists', array(
        "reseller_id" => $reseller_id,
        "status" => 0
    ));
    if ($pricelist_result->num_rows() > 0) {
        $pricelist_result_array = $pricelist_result->result_array();

        foreach ($pricelist_result_array as $key => $value) {
            echo "<option value=" . $value['id'] . ">" . $value['name'] . "</option>";
        }
    } else {
        echo '';
    }

    exit();
}

function reseller_distributor()
{
    $add_array = $this->input->post();
    $reseller_id = $add_array['reseller_id'];
    $message = 'Yes';
    if ($reseller_id > 0) {
        $this->db->select('type');
        $type = (array) $this->db->get_where('accounts', array(
            "reseller_id" => $reseller_id
        ))->first_row();
        if (($type['type'] == - 1) || ($type['type'] == 2)) {
            $message = 'Yes';
        } else {
            $message = 'No';
        }
    }
    echo $message;
}

function customer_customerlist()
{
    $add_array = $this->input->post();
    $reseller_id = $add_array['reseller_id'];
    $accountinfo = $this->session->userdata("accountinfo");
    $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : $reseller_id;
    $pricelist_result = $this->db->get_where('accounts', array(
        "reseller_id" => $reseller_id,
        "status" => 0,
        "type" => 3,
        "type" => 0
    ));
    if ($pricelist_result->num_rows() > 0) {
        $pricelist_result_array = $pricelist_result->result_array();
        foreach ($pricelist_result_array as $key => $value) {
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

function customer_subresellerlist()
{
    $add_array = $this->input->post();
    $reseller_id = $add_array['reseller_id'];
    $accountinfo = $this->session->userdata("accountinfo");
    if(!isset($reseller_id) && $reseller_id == ''){
        $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : $reseller_id;
    }
    if($accountinfo['type'] == 1){
        $type = array('type' => 1,0);
        $this->db->where_in('type', $type);
    }else{
        $type = array('type' => 1);
    }
    $pricelist_result = $this->db->get_where('accounts', array(
        "reseller_id" => $reseller_id,
        "status" => 0
    ));
    if ($pricelist_result->num_rows() > 0) {
        $pricelist_result_array = $pricelist_result->result_array();

        foreach ($pricelist_result_array as $key => $value) {
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

function customer_depend_list()
{
    $add_array = $this->input->post();
    $reseller_id = $add_array['reseller_id'];
    $accountinfo = $this->session->userdata("accountinfo");
    $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : $reseller_id;
    $pricelist_result = $this->db->get_where('accounts', array(
        "reseller_id" => $reseller_id,
        "status" => 0,
        "deleted" => 0
    ));
    if ($pricelist_result->num_rows() > 0) {
        $pricelist_result_array = $pricelist_result->result_array();

        foreach ($pricelist_result_array as $key => $value) {
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

function customer_product_delete($accountid)
{
    $ids = $this->input->post("selected_ids", true);
    $accountinfo = $this->session->userdata("accountinfo");
    $where_arr['where'] = $this->db->where("id IN (" . $ids . ")", NULL, false);
    $this->db->where("accountid", $accountid);
    $order_item = $this->db_model->getSelect("*", "order_items", '', $where_arr);

    if ($order_item->num_rows > 0) {
        $order_item = $order_item->result_array();
        foreach ($order_item as $key => $item) {
            $this->db->where("id", $item['id']);
            $this->db->update("order_items", array(
                "is_terminated" => 1,
                "termination_date" => gmdate("Y-m-d"),
                "termination_note" => "Product  has been released by " . $accountinfo['number'] . "( " . $accountinfo['first_name'] . " " . $accountinfo['last_name'] . ") "
            ));
        }
    }
    echo 1;
}

function customer_did_delete($accountid)
{
    $ids = $this->input->post("selected_ids", true);
    $accountinfo = $this->session->userdata("accountinfo");
    $where_arr['where'] = $this->db->where("product_id IN (" . $ids . ")", NULL, false);
    $this->db->where("accountid", $accountid);
    $order_item = $this->db_model->getSelect("*", "order_items", '', $where_arr);
    if ($order_item->num_rows > 0) {
        $order_item = $order_item->result_array();
        foreach ($order_item as $key => $item) {
            // The update had no WHERE and terminated the order items of all accounts
            $this->db->where("id", $item['id']);
            $this->db->update("order_items", array(
                "is_terminated" => 1,
                "termination_date" => gmdate("Y-m-d"),
                "termination_note" => "Product  has been released by " . $accountinfo['number'] . "( " . $accountinfo['first_name'] . " " . $accountinfo['last_name'] . ") "
            ));
            $this->db->where("product_id", $item['product_id']);
            $this->db->update("dids", array(
                "accountid" => 0
            ));
        }
    }
    echo 1;
}
    // Ashish ASTPPCOM-825
function reseller_customerlist()
{
    $add_array = $this->input->post();
    $reseller_id = $add_array['reseller_id'];
    $accountinfo = $this->session->userdata("accountinfo");
    $pricelist_result = $this->db->get_where('accounts', array(
     "reseller_id" => $reseller_id,
     "status" => 0,
     "type" => 0
 ));
    if ($pricelist_result->num_rows() > 0) {
        $pricelist_result_array = $pricelist_result->result_array();
        foreach ($pricelist_result_array as $key => $value) {
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
    // ASTPPCOM-825 end
}
?>


