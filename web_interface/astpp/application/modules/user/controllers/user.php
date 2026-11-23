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
class User extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->helper('form');
        $this->load->library("astpp/form", 'user_form');
        $this->load->library("user_form");
        $this->load->library("astpp/permission");
        $this->load->library('astpp/order');
        $this->load->model('Auth_model');
        $this->load->model('Astpp_common');
        $this->load->model('user_model');
        $this->load->library('did_lib');
        $this->load->library('astpp/payment');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . 'login/login');
        $accountinfo = $this->session->userdata('accountinfo');
        $account_arr = (array) $this->db->get_where("accounts", array(
            "id" => $accountinfo['id'],
            "deleted" => "0",
            "status" => "0"
        ))->first_row();
        if (empty($account_arr)) {
            $this->session->sess_destroy();
            $this->load->helper('cookie');
            set_cookie('post_info', json_encode("text"), '20');
            redirect(base_url() . "login/");
        }
    }

    function index()
    {
        $data['page_title'] = gettext('Dashboard');
        $account_info = $this->session->userdata("accountinfo");
        $data['accountinfo'] = $account_info;
        if ($account_info['reseller_id'] > 0) {
            $where = "products.product_category IN (1,2)";
            $this->db->where($where);
            $productdata = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.buy_cost,products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                'reseller_products.status' => 0,
                'products.can_purchase' => 0,
                'products.is_deleted' => 0,
                'reseller_products.account_id' => $account_info['reseller_id']
            ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '10', '', 'desc', 'products.id');
        } else {
            $where = "products.product_category IN (1,2)";
            $this->db->where($where);
            $productdata = $this->db_model->select("*", "products", array(
                'status' => 0,
                'can_purchase' => 0,
                'is_deleted' => 0,
                'reseller_id' => 0
            ), "id", "desc", "10", "");
        }
        if ($productdata->num_rows > 0) {
            $data['productdata'] = $productdata->result_array();
        }

        $data['currency'] = $this->common->get_field_name("currency", "currency", array(
            "id" => $account_info['currency_id']
        ));

        $this->load->view('view_user_dashboard', $data);
    }

    function user_dashboard_recent_payments()
    {
        $result = $this->user_model->user_dashboard_recent_recharge_info();
        $gmtoffset = $this->common->get_timezone_offset();
        $i = 0;
        $json_data = array();
        if ($result->num_rows() > 0) {
            $account_arr = $this->common->get_array('id,number,first_name,last_name', 'accounts', '');
            $json_data[0]['accountid'] = 'Accounts';
            $json_data[0]['credit'] = 'Amount';
            $json_data[0]['payment_date'] = 'Date';
            $json_data[0]['notes'] = 'Notes';
            foreach ($result->result_array() as $key => $data) {
                $current_timestamp = strtotime($data['payment_date']);
                $modified_date = $current_timestamp + $gmtoffset;
                $data['accountid'] = ($data['accountid'] != '' && isset($account_arr[$data['accountid']])) ? $account_arr[$data['accountid']] : "Anonymous";
                $json_data[$i]['accountid'] = $data['accountid'];
                $json_data[$i]['credit'] = $this->common_model->calculate_currency($data['credit'], '', '', true, false);
                $json_data[$i]['payment_date'] = date('Y-m-d H:i:s', strtotime($data['payment_date']) + $gmtoffset);
                $json_data[$i]['notes'] = $data['notes'];
                $i ++;
            }
        }
        echo json_encode($json_data);
    }

    function user_dashboard_package_data()
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $json_data = array();
        $this->db->where('pricelist_id', $accountinfo['pricelist_id']);
        $this->db->select('*');
        $result = $this->db->get('packages', 10);
        $i = 1;
        if ($result->num_rows() > 0) {
            $json_data[0]['package_name'] = gettext('Package Name');
            $json_data[0]['includedseconds'] = gettext('Included Seconds');
            $json_data[0]['status'] = 'Status';
            $result = $result->result_array();
            foreach ($result as $data) {
                $json_data[$i]['package_name'] = $data['package_name'];
                $json_data[$i]['includedseconds'] = $data['includedseconds'];
                $json_data[$i]['status'] = $this->common->get_status('export', '', $data['status']);
                $i ++;
            }
        }
        echo json_encode($json_data);
    }

    function user_dashboard_invoices_data()
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $currency = $this->common->get_field_name('currency', 'currency', array(
            "id" => $accountinfo['currency_id']
        ));
        $this->db->where('accountid', $accountinfo['id']);
        $this->db->where('confirm', 1);
        $this->db->select('*');
        $this->db->order_by('generate_date', 'desc');
        $result = $this->db->get('invoices', 10);
        $json_data = array();
        $gmtoffset = $this->common->get_timezone_offset();
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
            $json_data[0]['type'] = 'Invoice Type';
            $json_data[0]['id'] = 'Number';
            $json_data[0]['from_date'] = 'From Date';
            $json_data[0]['invoice_date'] = 'Generated Date';
            $json_data[0]['amount'] = 'Amount (' . $currency . ')';
            $i = 1;
            foreach ($result as $key => $data) {
                $invoice_prefix = $entity_type = $this->common->get_field_name('prefix', 'invoices', array(
                    'id' => $data['id']
                ));

                $invoiceid = $entity_type = $this->common->get_field_name('invoiceid', 'invoices', array(
                    'id' => $data['id']
                ));
                $invoice_num = $invoice_prefix . $invoiceid;
                $inv_type = $this->common->get_invoice_total('item_type', '', $data['id']);
                if ($inv_type == '') {
                    $inv_type = gettext('Automatically');
                }
                if ($inv_type == 'manual_inv') {
                    $inv_type = gettext('Manually');
                }
                $inv_debit = $this->common->convert_to_currency('', '', $data['amount']);
                if ($inv_debit == '') {
                    $inv_debit = $this->common->convert_to_currency('', '', 0);
                }
                $json_data[$i]['type'] = $data['type'];
                $json_data[$i]['id'] = $invoice_num;
                $json_data[$i]['from_date'] = date('Y-m-d H:i:s', strtotime($data['from_date']) + $gmtoffset);
                $json_data[$i]['invoice_date'] = date('Y-m-d H:i:s', strtotime($data['generate_date']) + $gmtoffset);
                $json_data[$i]['amount'] = $inv_debit;
                $i ++;
            }
        }
        echo json_encode($json_data);
    }

    function user_dashboard_subscription_data()
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $this->db->where('accountid', $accountinfo['id']);
        $this->db->select('*');
        $this->db->order_by('assign_date', 'desc');
        $result = $this->db->get('charge_to_account', 10);
        $json_data = array();

        $gmtoffset = $this->common->get_timezone_offset();
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
            $charge_str = null;
            $charges_arr = array();
            foreach ($result as $charges_data) {
                $charge_str .= $charges_data['charge_id'] . ",";
            }
            $charge_str = rtrim($charge_str, ",");
            $where = "id IN ($charge_str)";
            $this->db->where($where);
            $this->db->select('id,description,sweep_id');
            $charge_result = $this->db->get('charges');
            foreach ($charge_result->result_array() as $data) {
                $charges_arr[$data['id']]['description'] = $data['description'];
                $charges_arr[$data['id']]['sweep_id'] = $data['sweep_id'];
            }
            $json_data[0]['charge_id'] = 'Charge Name';
            $json_data[0]['assign_date'] = 'Assign Date';
            $json_data[0]['sweep_id'] = 'Billing Cycle';
            $i = 1;
            foreach ($result as $key => $data) {
                if (isset($charges_arr[$data['charge_id']]['sweep_id'])) {
                    $sweep_id = $charges_arr[$data['charge_id']]['sweep_id'];
                }
                $data['charge_id'] = isset($charges_arr[$data['charge_id']]['description']) ? $charges_arr[$data['charge_id']]['description'] : "Anonymous";
                $json_data[$i]['charge_id'] = $data['charge_id'];
                if ($data['assign_date'] != '0000-00-00 00:00:00') {
                    $json_data[$i]['assign_date'] = date('Y-m-d H:i:s', strtotime($data['assign_date']) + $gmtoffset);
                } else {
                    $json_data[$i]['assign_date'] = $data['assign_date'];
                }
                if (isset($sweep_id)) {
                    if ($sweep_id == 0) {
                        $json_data[$i]['sweep_id'] = gettext('Daily');
                    } else {
                        $json_data[$i]['sweep_id'] = gettext('Monthly');
                    }
                } else {
                    $json_data[$i]['sweep_id'] = gettext('Anonymous');
                }
                $i ++;
            }
        }
        echo json_encode($json_data);
    }

    function user_edit_account()
    {
        if ($add_array['id'] != '') {
            $data['form'] = $this->form->build_form($this->accounts->accounts_form->get_user_form_fields($add_array['id']), $add_array);
            $data['page_title'] = gettext('Edit ' . $entity_name);
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                $add_array['password'] = $this->common->encode($add_array['password']);
                unset($add_array['number']);
                $this->accounts->accounts_model->edit_account($add_array, $add_array['id']);
                $accountinfo = $this->session->userdata('accountinfo');
                if ($add_array['id'] == $accountinfo['id']) {
                    $this->session->set_userdata('accountinfo', (array) $this->db->get_where('accounts', array(
                        'id' => $add_array['id']
                    ))
                    ->first_row());
                }
                $this->session->set_flashdata('astpp_errormsg', ucfirst($entity_name).' '. gettext('updated successfully!'));

                redirect(base_url() . 'user/user/');
            }
            $this->load->view('view_user_details', $data);
        } else {
            $data['page_title'] = gettext('Edit ' . $entity_name);
            $where = array(
                'id' => $account_data["id"]
            );
            $account = $this->db_model->getSelect("*", "accounts", $where);
            $data["account_data"] = $account->result_array();

            foreach ($account->result_array() as $key => $value) {
                $editable_data = $value;
            }
            $editable_data['password'] = $this->common->decode($editable_data['password']);
            $data['form'] = $this->form->build_form($this->accounts->accounts_form->get_user_form_fields($editable_data['id']), $editable_data);
            $this->load->view('view_user_details', $data);
        }
    }

    function user_did_edit($edit_id = '')
    {
        $this->permission->customer_web_record_permission($edit_id, 'dids', 'user/user_didlist/');
        $data['page_title'] = gettext('Edit DIDs');
        $account_data = $this->session->userdata("accountinfo");
        $this->db->where('id', $edit_id);
        $this->db->select('id,call_type,extensions,number');
        $did_info = (array) $this->db->get('dids')->first_row();
        $did_info['free_didlist'] = $did_info['id'];
        $data['form'] = $this->form->build_form($this->user_form->build_user_did_form(), $did_info);
        $this->load->view('view_user_did_edit', $data);
    }

    function user_dids_action($action, $did_id = "")
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['reseller_id'];
        $did_id = empty($did_id) ? $this->input->post("free_didlist", true) : $did_id;
        $product_array = array();
        if ($did_id != '') {
            $account_query = $this->db_model->getSelect("*", "accounts", array(
                'id' => $accountinfo['id']
            ));
            $account_arr = (array) $account_query->first_row();
            $did_query = $this->db_model->getSelect("*", "dids", array(
                "id" => $did_id
            ));
            $did_arr = (array) $did_query->first_row();
            if ($action == "add") {
                if ($accountinfo['type'] == 0 && (!$this->check_calls_running())) {
                    $this->session->set_flashdata('astpp_notification', gettext('You can not purchase any new DID when your calls are running!'));
                    redirect(base_url() . "user/user_didlist/");
                    exit;
                }
                $this->load->library('did_lib');
                $did_result = $this->did_lib->did_billing_process($this->session->userdata, $accountinfo['id'], $did_id);
                if ($did_result[0] == "SUCCESS") {
                    $did_arr['is_parent_billing'] = "false";
                    $order_id = '';
                    $payment_gateway = '';
                    if ($reseller_id > 0) {
                        $product_query = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,products.buy_cost,products.can_purchase,products.can_resell,products.commission,reseller_products.price,reseller_products.setup_fee,products.billing_type,products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id,reseller_products.setup_fee,reseller_products.is_optin', array(
                            'products.status' => 0,
                            'reseller_products.product_id' => $did_arr['product_id'],
                            'reseller_products.account_id' => $reseller_id
                        ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', 'DESC', 'products.id');
                        $product_array = (array) $product_query->first_row();
                    } else {
                        $product_query = $this->db_model->getSelect("*", "products", array(
                            "id" => $did_arr['product_id']
                        ));
                        $product_array = (array) $product_query->first_row();
                    }

                    $did_arr['product_name'] = $product_array['name'];
                    $did_arr['category_name'] = $this->common->get_field_name('name', 'category', array(
                        'id' => $product_array['product_category']
                    ));
                    $did_arr['price'] = $this->common_model->calculate_currency($product_array['price'], '', '', true, false);
                    $did_arr['setup_fee'] = $this->common_model->calculate_currency($product_array['setup_fee'], '', '', true, false);
                    $did_arr['next_billing_date'] = ($product_array['billing_days'] == 0) ? gmdate('Y-m-d 23:59:59', strtotime('+10 years')) : gmdate("Y-m-d 23:59:59", strtotime("+" . $product_array['billing_days'] . " days"));
                    $last_id = $this->order->confirm_order($did_arr, $accountinfo['id'], $accountinfo);
                    $final_array = array_merge($account_arr, $did_arr);
                    if ($last_id > 0) {
                        $final_array['quantity'] = 1;
                        $final_array['price'] = ($did_arr['setup_fee'] + $did_arr['price']);
                        $final_array['total_price'] = ($product_array['setup_fee'] + $product_array['price']) * ($final_array['quantity']);
                        $final_array['total_price_amount'] = ($product_array['setup_fee'] + $product_array['price']);
                        $final_array['payment_by'] = "Account Balance";
                    }
                }
                $astpp_flash_message_type = ($did_result[0] == "SUCCESS") ? "astpp_errormsg" : "astpp_notification";
                $this->session->set_flashdata($astpp_flash_message_type, gettext($did_result[1]));

                redirect(base_url() . "user/user_didlist/");
            }
            if ($action == "edit") {
                $this->permission->customer_web_record_permission($did_id, 'dids', 'user/user_didlist/');
                $add_array = $this->input->post();
                $data['form'] = $this->form->build_form($this->user_form->build_user_did_form($add_array['free_didlist']), $add_array);
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
                    $update_arr = array(
                        "call_type" => $add_array['call_type'],
                        "extensions" => $add_array['extensions'],
                        "last_modified_date" => gmdate("Y-m-d H:i:s")
                    );
                    $this->db->update("dids", $update_arr, array(
                        "id" => $did_id
                    ));
                    if ($accountinfo['reseller_id'] > 0) {

                        $this->db->update('reseller_pricing', $update_arr, array(
                            'note' => $did_arr['number']
                        ));
                    }
                    echo json_encode(array(
                        "SUCCESS" => $did_arr['number'].' '.gettext('DID Updated Successfully!')
                    ));
                    exit();
                }
                $this->load->view('view_user_did_edit', $data);
            }
            if ($action == "delete") {
                $this->permission->customer_web_record_permission($did_id, 'dids', 'user/user_didlist/');
                $this->db->update("dids", array(
                    "accountid" => 0,
                    "assign_date" => "0000-00-00 00:00:00",
                    'charge_upto' => "0000-00-00 00:00:00"
                ), array(
                    "id" => $did_id
                ));
                $this->session->set_flashdata('astpp_notification', gettext('DID Removed Successfully.'));
                redirect(base_url() . "user/user_didlist/");
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('DID not found.'));
            redirect(base_url() . "user/user_didlist/");
        }
    }

    function user_rates_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('My Rates');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $this->load->module('rates/rates');
        $data["grid_buttons"] = $this->user_form->user_rates_list_buttons();
        $data['grid_fields'] = $this->user_form->user_rates_list();
        $data['form_search'] = $this->form->build_serach_form($this->user_form->user_rates_list_search());
        $this->load->view('view_user_rates_list', $data);
    }

    function user_rates_list_json()
    {
        $account_data = $this->session->userdata("accountinfo");
        $markup = $this->common->get_field_name('markup', 'pricelists', array(
            'id' => $account_data["pricelist_id"]
        ));
        $count_all = $this->user_model->get_user_rates_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->user_model->get_user_rates_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);

        $grid_fields = json_decode($this->user_form->user_rates_list());
        foreach ($query->result_array() as $key => $value) {
            $cost = $account_data['type'] != 3 ? ($value['cost'] + (($value['cost'] * $markup) / 100)) : $value['cost'];
            $json_data['rows'][] = array(
                'cell' => array(
                    $this->common->get_only_numeric_val("", "", $value["pattern"]),
                    $value['comment'],
                    //ASTPPCOM-1348_kanu_start
                    $this->common_model->calculate_currency_locale($value['connectcost'], '', '', true, false),
                    $value['includedseconds'],
                    $this->common_model->calculate_currency_locale(($cost), '', '', true, false),
                    //ASTPPCOM-1348_kanu_end
                    $value['init_inc'],
                    $value['inc']
                )
            );
        }
        echo json_encode($json_data);
    }

    function user_rates_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action'], $action['advance_search']);
            if (isset($action['connectcost']['connectcost']) && $action['connectcost']['connectcost'] != '') {
                $action['connectcost']['connectcost'] = $this->common_model->add_calculate_currency($action['connectcost']['connectcost'], "", '', true, false);
            }
            if (isset($action['cost']['cost']) && $action['cost']['cost'] != '') {
                $account_data = $this->session->userdata("accountinfo");
                $markup = $this->common->get_field_name('markup', 'pricelists', array(
                    'id' => $account_data["pricelist_id"]
                ));
                $markup = ($markup > 0) ? $markup : 1;
                $action['cost']['cost'] = $this->common_model->add_calculate_currency($action['cost']['cost'], "", '', true, false);
            }
            $this->session->set_userdata('user_rates_list_search', $action);
        }
        if ($ajax_search != 1) {
            redirect(base_url() . 'user/user_rates_list/');
        }
    }

    function user_rates_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('user_rates_list_search', "");
    }

    function user_rates_list_export()
    {
        $account_data = $this->session->userdata('accountinfo');
        $currency_id = $account_data['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $query = $this->user_model->get_user_rates_list(true, '', '', false);
        $markup = $this->common->get_field_name('markup', 'pricelists', array(
            'id' => $account_data["pricelist_id"]
        ));
        ob_clean();
        $inbound_array[] = array(
            gettext("Code"),
            gettext("Destination"),
            gettext("Connection Cost") . "(" . $currency . ")",
            gettext("Grace Time"),
            gettext("Cost / Min") . "(" . $currency . ")",
            gettext("Initial Increment"),
            gettext("Increment")
        );
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $cost = $account_data['type'] != 3 ? ($row['cost'] + ($row['cost'] * $markup) / 100) : $row['cost'];
                $inbound_array[] = array(
                    $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                    $row['comment'],
                    $this->common_model->calculate_currency($row['connectcost'], '', '', true, false),
                    $row['includedseconds'],
                    $this->common_model->calculate_currency($cost, '', '', true, false),
                    $row['init_inc'],
                    $row['inc']
                );
            }
        }
        $this->load->helper('csv');
        array_to_csv($inbound_array, 'Rates_' . date("Y-m-d") . '.csv');
    }

    function user_refill($action = "")
    {
        if (common_model::$global_config['system_config']['paypal_status'] == 1) {
            redirect(base_url() . 'user/user/');
        }
        $this->load->module("user/refill");
        if ($action == "GET_AMT") {
            $amount = $this->input->post("value", true);
            $this->refill->convert_amount($amount);
        } else {
            $this->refill->index();
        }
    }

    function user_convert_amount($amount)
    {
        $amount = $this->common_model->add_calculate_currency($amount, "", "", false, false);
        echo number_format($amount, 5);
    }

    function user_report_export()
    {
        $this->load->module('reports/reports');
        $this->user_cdrreport_export();
    }

    function change_password()
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $id = $accountinfo['id'];
        $this->load->model('user_model');

        $query = $this->user_model->change_password($id);
        foreach ($query as $row) {
            $data['password'] = $row->password;
        }
        $databasepassword = $data['password'];
        $password = $_POST['oldpassword'];
        $newpassword = $_POST['newpassword'];
        $conformpassword = $_POST['conformpassword'];
        if ($databasepassword == $password) {

            if ($conformpassword == $newpassword) {
                $update = $newpassword;
                $this->load->model('user_model');
                $this->user_model->change_db_password($update, $id);
                $this->session->set_flashdata('astpp_errormsg', gettext("Password changed Sucessfully....!!!"));
                redirect(base_url() . 'user/user/changepassword/');
            } else {
                $this->session->set_flashdata('astpp_notification', gettext("New Password & Conformpassword not match."));
                redirect(base_url() . 'user/user/changepassword/');
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext("Invalid old passwword."));
            redirect(base_url() . 'user/user/changepassword/');
        }
    }

    function changepassword()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Change Password');
        $this->load->view('view_changepassword', $data);
    }

    function user_generate_password()
    {
        echo $this->common->generate_password();
    }

    function user_generate_number($digit)
    {
        echo $this->common->find_uniq_rendno($digit, 'number', 'accounts');
    }

    function user_sipdevice_voicemail_random_password()
    {
       $digit='5';
       echo rand(pow(10, $digit - 1), pow(10, $digit) - 1);exit;
   }

   function user_refill_coupon_list()
   {
    $accountinfo = $this->session->userdata('accountinfo');
    if ($accountinfo['posttoexternal'] != '1') {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Refill Coupon');
        $this->load->module('refill_coupon/refill_coupon');
        $data['grid_fields'] = $this->refill_coupon->refill_coupon_form->build_user_refill_coupon_grid();
        $acc_data = $this->session->userdata("accountinfo");
        $reseller_id = $acc_data['reseller_id'];

        $drp_data = $this->db->query("SELECT id,CONCAT(number,'(',amount,')') as details,number FROM refill_coupon WHERE status = '0' and reseller_id='" . $reseller_id . "'");
        $reseller_data = array();
        $data['refill_coupon_list'] = form_dropdown_all('refill_coupon_list', $reseller_data, '');
        $this->load->view('view_refill_coupon_list', $data);
    } else {
        $this->session->set_flashdata('astpp_danger_alert', gettext('Permission Denied!'));
        redirect(base_url() . 'user/user/');
    }
}

function user_refill_coupon_list_json()
{
    $account_data = $this->session->userdata("accountinfo");

    $this->load->module('refill_coupon/refill_coupon');
    $this->refill_coupon->refill_coupon_customer_json($account_data["id"]);
}

function user_refill_coupon_number($refill_coupon_no)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $reseller_id = $accountinfo['reseller_id'];
    $customer_id = $accountinfo['id'];
    $this->db->where('reseller_id', $reseller_id);
    $this->db->where('number', $refill_coupon_no);
    $this->db->select('*');
    $refill_coupon_result = $this->db->get('refill_coupon');
    if ($refill_coupon_result->num_rows() > 0) {
        $refill_coupon_result = $refill_coupon_result->result_array();
        $refill_coupon_result = $refill_coupon_result[0];
        if ($refill_coupon_result['status'] == 1) {
            echo json_encode(1);
        } elseif ($refill_coupon_result['status'] == 2) {
            echo json_encode(2);
        } else {
            $logintype = $this->session->userdata('logintype');
            if ($logintype == '3') {
                $balance = $this->common->get_field_name('balance', 'accounts', array(
                    'id' => $accountinfo['id'],
                    'status' => 0,
                    'type' => 3,
                    'deleted' => 0
                ));
            } else {
                $balance = $this->common->get_field_name('balance', 'accounts', array(
                    'id' => $accountinfo['id'],
                    'status' => 0,
                    'type' => 0,
                    'deleted' => 0
                ));
            }
            $user_balance = ($accountinfo['posttoexternal'] == 1) ? $accountinfo['credit_limit'] - $balance : $balance;
            $original_balance = $refill_coupon_result['amount'];
            $refill_coupon_result['amount'] = $this->common_model->to_calculate_currency($original_balance, '', '', TRUE, TRUE);

            if ($user_balance == '') {

                $user_balance = $accountinfo['balance'];
            }

            $refill_coupon_result['new_balance'] = $this->common_model->to_calculate_currency($user_balance + $original_balance, '', '', TRUE, TRUE);
            echo json_encode($refill_coupon_result);
        }
    } else {
        echo json_encode(3);
    }
}

function user_refill_coupon_action($refill_coupon_no)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $reseller_id = $accountinfo['reseller_id'];
    if ($reseller_id == 0) {
        $reseller_id = '-1';
    }
    $date = gmdate('Y-m-d H:i:s');

    $customer_id = $accountinfo['id'];
    $result = $this->db_model->getSelect("*", "refill_coupon", array(
        "number" => $refill_coupon_no
    ));
    if ($result->num_rows() > 0) {
        $result = $result->result_array()[0];
        $balance = $this->common->get_field_name("balance", "accounts", array(
            "id" => $customer_id
        ));
        $new_balance = ($accountinfo["posttoexternal"] == 1) ? ($balance - $result['amount']) : ($balance + $result['amount']);

        $this->db->where('number', $refill_coupon_no);
        $refill_coupon_data = array(
            'status' => 2,
            "account_id" => $customer_id,
            'firstused' => $date
        );
        $this->db->update('refill_coupon', $refill_coupon_data);

        $payment_info = array(
            "price" => $result['amount'],
            "payment_by" => "Voucher",
            "payment_fee" => 0,
            "invoice_type" => "credit",
            "product_category" => 3,
            "description" => "Refill done using voucher'('" . $refill_coupon_no . " ')'",
            "order_item_id" => 0,
            "charge_type" => "Voucher"
        );

        $where = array(
            'id' => $accountinfo['currency_id']
        );
        $currency_info = (array) $this->db->get_where("currency", $where)->result_array()[0];
        $this->payment->add_payments_transcation($payment_info, $accountinfo, $currency_info);
        $this->session->set_flashdata('astpp_errormsg', gettext('Refill Coupon amount is added successfully to your account'));
    }
    redirect(base_url() . "user/user_refill_coupon_list/");
}

function user_invoice_date($select, $accountid)
{
    $query = $this->db_model->select($select, "invoices", '', "id", "DESC", "1", "0");
    if ($query->num_rows() > 0) {
        $invoiceid = $query->result_array();
        $invoice_date = $invoiceid[0][$select];
        return $invoice_date;
    }
    return false;
}

function user_invoices()
{
    $data['page_title'] = gettext('Invoices');
    $this->load->view('view_user_invoices_list', $data);
}

function user_invoices_json()
{
    $json_data = array();
    $count_all = $this->user_model->get_user_invoices_list(false);
    $paging_data = $this->form->load_grid_config($count_all, 10, 1);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->get_user_invoices_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    $this->load->module('invoices/invoices');
    $grid_fields = json_decode($this->user_form->build_invoices_list_for_user());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
}

function user_emails()
{
    $data['page_title'] = gettext('EMails');
    $data['grid_fields'] = $this->user_form->build_emails_list_for_user();
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_user_emails_search());
    $this->load->view('view_user_emails_list', $data);
}

function user_emails_json()
{
    $json_data = array();
    $count_all = $this->user_model->get_user_emails_list(false);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->get_user_emails_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    $grid_fields = json_decode($this->user_form->build_emails_list_for_user());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
}

function user_emails_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action'], $action['advance_search']);
        $this->session->set_userdata('user_emails_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'user/user_emails/');
    }
}

function user_emails_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('user_emails_search', "");
}

function user_invoice_config()
{
    $data['page_title'] = gettext('Company Profile');
    $accountinfo = $this->session->userdata("accountinfo");
    $add_array = $this->input->post();
    $data["account_data"] = $add_array;
    if (isset($add_array['submit'])) {
        if ($_FILES['file']['name'] == '') {
            $invoiceconf = $this->user_model->get_invoiceconf();
            $file_name = ($invoiceconf['logo'] != '') ? $invoiceconf['logo'] : '';
        }
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $files = $_FILES['file'];
            if ($files['size'] < 0) {
                $this->session->set_flashdata('astpp_notification', gettext('PLease upload maximum file'));
                redirect(base_url() . "accounts/reseller_invoice_config/" . $add_array['accountid'] . "/");
            }
            $file = $_FILES['file'];
            $uploadedFile = $file["tmp_name"];
            $file_name = $file['name'];
            $file_type = $file['type'];
            if ($file_type == 'image/jpg' || $file_type == 'image/png' || $file_type == 'image/jpeg') {
                $dir_path = FCPATH . "upload/";
                $path = $dir_path . $add_array['accountid'] . "_" . $file['name'];
                if (move_uploaded_file($uploadedFile, $path)) {
                    $this->session->set_flashdata('astpp_errormsg', gettext('files added successfully!'));
                } else {
                    $this->session->set_flashdata('astpp_notification', gettext("File Uploading Fail Please Try Again"));
                    redirect(base_url() . 'user/user_invoice_config/');
                }
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Please upload only image!'));
                redirect(base_url() . 'user/user_invoice_config/');
            }
        }
        $add_array['logo'] = $file_name;
        unset($add_array['submit']);
        if ($add_array['id'] == '') {
            $add_array['accountid'] = $accountinfo['id'];
            $this->user_model->add_invoice_config($add_array);
        } else {
            $this->user_model->edit_invoice_config($add_array, $add_array['id']);
        }
        $this->session->set_flashdata('astpp_errormsg', gettext('Invoice config updated successfully!'));
        redirect(base_url() . 'user/user_invoice_config/');
    } else {
        $data["account_data"] = (array) $this->db->get_where('invoice_conf', array(
            "accountid" => $accountinfo['id']
        ))->first_row();
        if (isset($data["account_data"]['logo'])) {
            $data["account_data"]['file'] = $accountinfo['id'] . "_" . $data["account_data"]['logo'];
        }
        $this->load->view('view_user_invoices_config', $data);
    }
}

function user_invoice_logo_delete($accountid)
{
    $invoiceconf = $this->db_model->getSelect("*", "invoice_conf", array(
        "accountid" => $accountid
    ));
    $result = $invoiceconf->result_array();
    $logo = $result[0]['logo'];
    $post_arr = array(
        'logo' => ''
    );
    $where_arr = array(
        'logo' => $logo
    );
    $this->db->where($where_arr);
    $this->db->update('invoice_conf', $post_arr);
}

function user_myprofile()
{
    $accountinfo = $this->session->userdata("accountinfo");
    $entity_name = strtolower($this->common->get_entity_type('', '', $accountinfo['type']));
    $data['page_title'] = gettext('My Profile');
    $add_array = $this->input->post();

    if ($add_array['id'] != '') {
        $add_array['type'] = $accountinfo['type'];
        $data['form'] = $this->form->build_form($this->user_form->get_userprofile_form_fields($add_array), $add_array);
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
        } else {
            if ($add_array['id'] == $accountinfo['id']) {
                unset($add_array['number']);
                unset($add_array['country_id']);
                $this->user_model->edit_account($add_array, $add_array['id']);
                $result = $this->db->get_where('accounts', array(
                    'id' => $add_array['id']
                ));
                $result = $result->result_array();
                $this->session->set_userdata('accountinfo', $result[0]);
                $this->session->set_flashdata('astpp_errormsg', gettext('Your Profile Updated Successfully!'));
                redirect(base_url() . 'user/user_myprofile/');
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Something wrong.Please contact to administrator.'));
            }
        }
    } else {
        $where = array(
            'id' => $accountinfo["id"]
        );
        $account = $this->db_model->getSelect("*", "accounts", $where);
        $data["account_data"] = $account->result_array();

        foreach ($account->result_array() as $key => $value) {
            $editable_data = $value;
        }
        $editable_data['password'] = $this->common->decode($editable_data['password']);
        $data['form'] = $this->form->build_form($this->user_form->get_userprofile_form_fields($editable_data), $editable_data);
    }
    $this->load->view('view_user_details', $data);
}

function user_change_password()
{
    $accountinfo = $this->session->userdata("accountinfo");
    $data['page_title'] = gettext("Change Password");
    $add_array = $this->input->post();
    if (! empty($add_array)) {
        $data['form'] = $this->form->build_form($this->user_form->get_userprofile_change_password(), $add_array);
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
        } else {
            $password_encode = $this->common->encode($add_array['new_password']);
            $data = array(
                'password' => $password_encode
            );
            $this->db->where('id', $add_array['id']);
            $this->db->update('accounts', $data);

            $this->db->where('accountid', $accountinfo['id']);
            $this->db->where('username', $accountinfo['number']);
            $sip_info = (array) $this->db->get_where("sip_devices")->first_row();
            if (! empty($sip_info)) {
                $did_params = (array) (json_decode($sip_info['dir_params']));
                $sipdevice_array = array(
                    'dir_params' => json_encode(array(
                        "password" => $add_array['new_password'],
                        'vm-enabled' => "true",
                        "vm-password" => $did_params['vm-password'],
                        "vm-mailto" => $did_params['vm-mailto'],
                        "vm-attach-file" => "true",
                        "vm-keep-local-after-email" => "true",
                        "vm-email-all-messages" => "true"
                    ))
                );
                $this->db->where('accountid', $accountinfo['id']);
                $this->db->where('username', $accountinfo['number']);
                $this->db->update('sip_devices', $sipdevice_array);
            }
            $this->session->set_flashdata('astpp_errormsg', gettext('Password Updated Successfully!'));
            redirect(base_url() . 'user/user_change_password/');
        }
    } else {
        $data_array['id'] = $accountinfo['id'];
        $data['form'] = $this->form->build_form($this->user_form->get_userprofile_change_password(), $data_array);
    }
    $this->load->view('view_user_change_password', $data);
}

function user_refill_report()
{
    $accountinfo = $this->session->userdata('accountinfo');
    $data['page_title'] = gettext('Refill Report');
    $data['search_flag'] = true;
    $this->session->set_userdata('advance_search', 0);
    $data['grid_fields'] = $this->user_form->build_user_refill_report();
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_user_refill_report_search());
    if ($accountinfo['type'] == 1) {
        $this->load->view('view_reseller_refill_report', $data);
    } else {
        $this->load->view('view_user_refill_report', $data);
    }
}

function user_refill_report_json()
{
    $json_data = array();
    $count_all = $this->user_model->get_user_refill_list(false);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->get_user_refill_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);

    $grid_fields = json_decode($this->user_form->build_user_refill_report());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
}

function user_refill_report_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action'], $action['advance_search']);
        if (isset($action['credit']['credit']) && $action['credit']['credit'] != '') {
            $action['credit']['credit'] = $this->common_model->add_calculate_currency($action['credit']['credit'], "", '', true, false);
        }
        if(!empty($action['date'][0])){
            $action['date'][0]=$this->common->convert_GMT_new ( $action['date'][0]);
        }
        if(!empty($action['date'][1])){
            $action['date'][1]=$this->common->convert_GMT_new ( $action['date'][1]);
        }
        $this->session->set_userdata('user_refill_report_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'user/user_refill_report/');
    }
}

function user_refill_report_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('user_refill_report_search', "");
}

function user_invoices_list()
{
    $data['page_title'] = gettext('Invoices');
    $data['search_flag'] = true;
    $accountinfo = $this->session->userdata('accountinfo');
    $this->session->set_userdata('advance_search', 0);
    $data['grid_fields'] = $this->user_form->build_user_invoices();
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_user_invoices_search());
    if ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) {
        $this->load->view('view_invoices_list', $data);
    } else {
        $this->load->view('view_user_invoices_list', $data);
    }
}

function user_invoices_list_json()
{
    $accountinfo = $this->session->userdata('accountinfo');
    $where = array(
        "accountid" => $accountinfo['id']
    );
    $count_all = $this->user_model->get_user_invoice_list(false, '', '', $where);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $user_currency = $this->common->get_field_name('currency', 'currency', $accountinfo['currency_id']);
    $invoices_query = $this->user_model->get_user_invoice_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], $where);
    $invoices_result = $invoices_query->result_array();
    $ountstanding_value = 0;
    $total_amount = 0;
    $this->db->where('accountid', $accountinfo['id']);
    $this->db->select('*');
    $invoice_details_result = $this->db->get('invoice_details');
    $total_credit = (array) $invoice_details_result->first_row();
    foreach ($invoices_result as $key => $value) {

        $total_amount += $total_credit['debit'];
        $invoice_date = $this->common->convert_GMT_to_new('','',$value['generate_date']);
        $from_date = $this->common->convert_GMT_to_new('','',$value['from_date']);
        $due_date = $this->common->convert_GMT_to_new('','',$value['due_date']);
        $from_currency = Common_model::$global_config['system_config']['base_currency'];
        $to_currency = $this->common->get_field_name('currency', 'currency', $accountinfo['currency_id']);
        $charge_type = $this->common->get_field_name("charge_type","invoice_details",array("invoiceid"=>$value['id'])); 
        
        if ($from_currency != $to_currency) {
            $outstanding = ($value['is_paid'] == 1) ? $value['debit'] - $value['credit'] : 0.00;
            $outstanding = $this->common_model->calculate_currency($outstanding, "", "", true, false);

            if($charge_type == "REFILL" || $charge_type == "Voucher" || $charge_type == "COMMISSION"){

              $amount =  $value['credit'];
              $amount = $this->common_model->calculate_currency($amount, "", "", true, false);
          }else{
             $amount = ($value['debit'] > 0) ? $value['debit'] : $value['credit'];
             $amount = $this->common_model->calculate_currency($amount, "", "", true, false);
         }
     } else {
        $outstanding = ($value['is_paid'] == 1) ? $value['debit'] - $value['credit'] : 0.00;

        if($charge_type == "REFILL" || $charge_type == "Voucher" || $charge_type == "COMMISSION"){
         $amount =  $value['credit'];
     }else{
       $amount = ($value['debit'] > 0) ? $value['debit'] : $value['credit'];
   }
}

$payment_last_date = '';
$payment_last_date = $this->common->get_field_name("created_date", "invoice_details", array(
    "invoiceid" => $value['id']
));
$payment_last = ($payment_last_date) ? date("Y-m-d", strtotime($payment_last_date)) : '';
$download = '<a href="' . base_url() . '/user/user_invoice_download/' . $value['id'] . '" class="btn btn-royelblue btn-sm"  title="Download Invoice" ><i class="fa fa-cloud-download fa-fw"></i></a>&nbsp';
if ($value['is_paid'] == 1 && $outstanding > 0) {
    $payment = ' <a style="padding: 0 8px;" href="' . base_url() . 'user/user_invoice_payment/' . $value['id'] . '" class="btn btn-warning"  title="Payment">'.gettext("Unpaid").'</a>';
} else {
    $payment = ' <button style="padding: 0 8px;" class="btn btn-success" type="button">'.gettext("Paid").'</button>';
}

if ($value['generate_type'] == 1) {
    $invoice_type = 'Manually';
} else {
    $invoice_type = 'Automatically';
}
$json_data['rows'][] = array(
    'cell' => array(
        $value['number'],
        $invoice_date,
        $from_date,
        $due_date,
        $payment_last,
        $this->common->currency_decimal($amount),
        $this->common->currency_decimal($outstanding),
        $download . $payment
    )
);
$ountstanding_value = $ountstanding_value + $outstanding;
}
echo json_encode($json_data);
}
function user_invoices_list_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        print_r($action);
        unset($action['action']);
        unset($action['advance_search']);
        if(!empty($action['from_date'][0])){
            $action['from_date'][0]=$this->common->convert_GMT_to_new( '','',$action['from_date'][0]);
        }
        if(!empty($action['to_date'][1])){
            $action['to_date'][1]=$this->common->convert_GMT_to_new ('','', $action['to_date'][1]);
        }
        $action['generate_date'][0] = $action['generate_date'][0] ? $action['generate_date'][0] . " 00:00:00" : '';
        $this->session->set_userdata('user_invoice_list_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'user/user_invoice_list/');
    }
}

function user_invoices_list_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('user_invoice_list_search', "");
}

function user_invoices_download($invoiceid)
{
    $this->load->module('invoices/invoices');
    $this->invoices->invoice_main_download($invoiceid);
}

function user_list_responce()
{
    $this->load->module('invoices/invoices');
    $this->invoices->invoice_list_responce();
}

function user_invoice_payment($invoiceid)
{
    $this->load->module('invoices/invoices');
    $this->invoices->invoice_summary($invoiceid);
}

function user_invoice_payment_pay($action = "")
{
    $this->load->module("user/payment");
    if ($action == "GET_AMT") {
        $amount = $this->input->post("value", true);
        $amount = $this->common_model->add_calculate_currency($amount, "", "", true, false);
        echo number_format($amount, 2);
    } else {
        $this->payment->index();
    }
}

function user_invoice_download($invoiceid)
{
    $this->permission->customer_web_record_permission($invoiceid, 'invoices', 'user/user_invoices_list/');
    $this->load->module('invoices/invoices');
    $this->invoices->invoice_download($invoiceid);
}

function user_didlist()
{
    $this->session->set_userdata('advance_search', 0);
    $accountinfo = $this->session->userdata('accountinfo');
    $data['page_title'] = gettext('Purchase DIDs');
    $data['search_flag'] = true;
    $data['grid_fields'] = $this->user_form->build_user_didlist();
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_user_didlist_search());
    $data["grid_buttons"] = array();
    $acc_data = $this->session->userdata("accountinfo");
    $data['accountid'] = $acc_data['id'];
    $data['country_id'] = $acc_data['country_id'];
    $result_did_final = array();
    $data['didlist'] = form_dropdown_all(array(
        "name" => "free_didlist",
        "id" => "free_didlist",
        "class" => "did_dropdown"
    ), array(), '');
    $this->load->view('view_user_did_list', $data);
}

function user_purchase_did()
{
    $account_data = $this->session->userdata("accountinfo");
    $drp_list = array();
    $country_id = $_POST['country_id'];
    
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
                        // $data['state_list'][] = "<option value=" . $val1 . ">" . $val1 . "</option>";
                    $data['state_list'][] = '<option value="'. $val1 .'"> '. $val1 .' </option>';
                }
            }
        }

        if(!isset($_POST['provience'])){
            $this->db->where('city NOT LIKE', '');
            $city_list = $this->db_model->getSelect("city", "dids", array(
                'country_id' => $country_id
            ));
            if ($city_list->num_rows() > 0) {
                $city_list_array = $city_list->result_array();                    
                foreach ($city_list_array as $key => $val) {
                    foreach ($val as $key1 => $val1) {
                                // print_r($val1); 
                                // $data['city_list'][] = "<option value=" . $val1 . ">" . $val1 . "</option>";
                        $data['city_list'][] = '<option value="'. $val1 .'"> '. $val1 .' </option>';
                    }
                }
            }
        }
    }

    if (isset($provience) && $provience != "") {
        $city_list = array();
        $city_list_array = array();
        $this->db->where('city NOT LIKE', '');
        $city_list = $this->db_model->getSelect("distinct(city)", "dids", array(
            'province' => $provience,
            'country_id' => $country_id
        ));
        if ($city_list->num_rows() > 0) {
            $city_list_array = $city_list->result_array();
            foreach ($city_list_array as $key => $val) {
                foreach ($val as $key1 => $val1) {
                    $data['city_list'][] = "<option value=" . $val1 . ">" . $val1 . "</option>";
                }
            }
        }
    }

    if ($account_data['reseller_id'] > 0) {
        $this->db->select('dids.id, dids.number, reseller_products.setup_fee, reseller_products.price');
        $this->db->where('dids.accountid', 0);
        $this->db->where('dids.parent_id', $account_data['reseller_id']);
        $this->db->where('dids.country_id', $country_id);
        if (isset($provience) && $provience != "") {
            $this->db->where('dids.province', $provience);
        }
        if (isset($city) && $city != "") {
            $this->db->where('dids.city', $city);
        }
        $this->db->where('dids.status', 0);
        $this->db->from('dids');
        $this->db->join('reseller_products', 'dids.product_id = reseller_products.product_id');
        $dids_array = (array) $this->db->get()->result_array();
    } else {
        $this->db->select('dids.id, dids.number,products.setup_fee,products.price');
        $this->db->where('dids.accountid', '0');
        $this->db->where('dids.parent_id', $account_data['reseller_id']);
        $this->db->where('dids.country_id', $country_id);
        if (isset($provience) && $provience != "") {
            $this->db->where('dids.province', $provience);
        }
        if (isset($city) && $city != "") {
            $this->db->where('dids.city', $city);
        }
        $this->db->where('dids.status', 0);
        $this->db->from('dids');
        $this->db->join('products', 'dids.product_id = products.id');
        $dids_array = (array) $this->db->get()->result_array();

    }

    $drp_list = array();
    if (! empty($dids_array)) {
        foreach ($dids_array as $drp_value) {
            if (! empty($drp_value['price']) && $drp_value['price'] != 0) {
                $did_cost = $this->common_model->to_calculate_currency($drp_value['price'], '', '', true, true);
            } else {
                $did_cost = 0;
            }
            if (! empty($drp_value['setup_fee']) && $drp_value['setup_fee'] != 0) {
                $did_setup = $this->common_model->to_calculate_currency($drp_value['setup_fee'], '', '', true, true);
            } else {
                $did_setup = 0;
            }
            $drp_list[$drp_value['id']] = $drp_value['number'] . ' ( Setup : ' . $did_setup . ')' . '( Monthly : ' . $did_cost . ' )';
        }
    }
    $data['didlist'] = form_dropdown_all(array(
        "name" => "free_didlist",
        "id" => "free_didlist",
        "class" => "free_didlist"
    ), $drp_list, '');
    echo json_encode($data);
}

function user_list_release($id)
{
    $accountinfo = $this->session->userdata('accountinfo');
    $where = array(
        'id' => $id
    );
    $did_info = (array) $this->db->get_where("dids", $where)->result_array()[0];
    $this->load->module('did/did');
    $this->did_model->did_number_release($did_info, $accountinfo, 'release');
    $did_info['product_name'] = $did_info['number'];
    $final_array = array_merge($did_info, $accountinfo);
    $this->did_lib->did_release($final_array);
    $this->session->set_flashdata('astpp_errormsg', gettext('DID Released Successfully!'));
    redirect(base_url() . 'user/user_didlist/');
}

function user_didlist_json()
{
    $account_data = $this->session->userdata("accountinfo");

    $json_data = array();
    $count_all = $this->user_model->getdids_list(false);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->getdids_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    $grid_fields = json_decode($this->user_form->build_user_didlist());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
}

function user_did_forward($id = '')
{
    $accountinfo = $this->session->userdata('accountinfo');
    $this->load->module('did/did');
    $this->did->did_forward($id);
}

function user_did_forward_save()
{
    $add_array = $this->input->post();
    if (isset($add_array['call_type_vm_flag']) && $add_array['call_type_vm_flag'] == 'on') {
        $add_array['call_type_vm_flag'] = 0;
    } else {
        $add_array['call_type_vm_flag'] = 1;
    }
    $this->load->model('user_model');
    $did_forward = $this->user_model->update_user_did_forward($add_array);
    $this->session->set_flashdata('astpp_errormsg', gettext('DID forwading set sucessfully!'));
    redirect(base_url() . 'user/user_didlist/');
}

function user_did_country()
{
    $this->load->module('accounts/accounts');
    $this->accounts->customer_did_country();
}

function user_didlist_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        $accountinfo = $this->session->userdata('accountinfo');
        if ($accountinfo['reseller_id'] > 0 && $action['call_type'] > 0) {
            $action['dids.call_type'] = $action['call_type'];
            unset($action['call_type']);
        }

        if (isset($action['country_id']) && $action['country_id'] != '') {
            $action['dids.country_id'] = $action['country_id'];
            unset($action['country_id']);
        }
        
        if (isset($action['number']) && $action['number'] != '') {
            $action['dids.number'] = $action['number'];
            $action['dids.number']['dids.number'] = $action['number']['number'];
            $action['dids.number']['dids.number-string'] = $action['number']['number-string'];
            unset($action['number']);
            unset($action['dids.number']['number']);
            unset($action['dids.number']['number-string']);
            
        }   


        
        if (isset($action['province']) && $action['province'] != '') {
            $action['dids.province'] = $action['province'];
            $action['dids.province']['dids.province'] = $action['province']['province'];
            $action['dids.province']['dids.province-string'] = $action['province']['province-string'];
            unset($action['province']);
            unset($action['dids.province']['province']);
            unset($action['dids.province']['province-string']);
        }

        if(isset($action['city']) && $action['city'] != ''){
          $action['dids.city'] = $action['city'];
          $action['dids.city']['dids.city'] = $action['city']['city'];
          $action['dids.city']['dids.city-string'] = $action['city']['city-string'];
          unset($action['city']);
          unset($action['dids.city']['city']);
          unset($action['dids.city']['city-string']);
      }
      
      if(isset($action['cost']) && $action['cost'] != ''){
          $action['dids.cost'] = $action['cost'];
          $action['dids.cost']['dids.cost'] = $action['cost']['cost'];
          $action['dids.cost']['dids.cost-integer'] = $action['cost']['cost-integer'];
          unset($action['cost']);
          unset($action['dids.cost']['cost']);
          unset($action['dids.cost']['cost-integer']);
      }
      
      if(isset($action['extensions']) && $action['extensions'] != ''){
          $action['dids.extensions'] = $action['extensions'];
          $action['dids.extensions']['dids.extensions'] = $action['extensions']['extensions'];
          $action['dids.extensions']['dids.extensions-string'] = $action['extensions']['extensions-string'];
          unset($action['extensions']);
          unset($action['dids.extensions']['extensions']);
          unset($action['dids.extensions']['extensions-string']);
      }
      unset($action['action']);
      unset($action['advance_search']);
      $this->session->set_userdata('user_did_search', $action);
  }
  if (@$ajax_search != 1) {
    redirect(base_url() . 'user/user_didlist/');
}
}
function user_didlist_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('user_did_search', "");
}

function user_ipmap()
{
    $this->session->set_userdata('advance_search', 0);
    $data['grid_fields'] = $this->user_form->build_user_ipmap();
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_user_ipmap_search());
    $this->load->view('view_user_ipmap_list', $data);
}

function user_ipmap_json()
{
    $json_data = array();
    $account_data = $this->session->userdata("accountinfo");
    $count_all = $this->user_model->user_ipmap_list(false);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->user_ipmap_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    $ipmap_grid_fields = json_decode($this->user_form->build_user_ipmap());
    $json_data['rows'] = $this->form->build_grid($query, $ipmap_grid_fields);
    echo json_encode($json_data);
}

function user_ipmap_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action']);
        unset($action['advance_search']);
        $this->session->set_userdata('user_ipmap_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'user/user_ipmap/');
    }
}

function user_ipmap_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('user_ipmap_search', "");
}

function user_ipmap_action($action = 'delete', $id = false)
{
    $add_array = $this->input->post();
    $accountinfo = $this->session->userdata('accountinfo');
    if ($action == 'add') {
        $ip = $add_array['ip'];
        if (strpos($ip, '/') !== false) {
            $add_array['ip'] = $add_array['ip'];
        } else {
            $add_array['ip'] = $add_array['ip'] . '/32';
        }
        $where = array(
            "ip" => trim($add_array['ip']),
            "prefix" => trim($add_array['prefix'])
        );
        $getdata = $this->db_model->countQuery("*", "ip_map", $where);
        if ($getdata > 0) {
            $this->session->set_flashdata('astpp_notification', gettext('IP already exist in system.'));
        } else {
            unset($add_array['action']);
            $add_array['context'] = 'default';
            $add_array['accountid'] = $accountinfo['id'];
            $ip_flag = $this->db->insert("ip_map", $add_array);
            if ($ip_flag) {
                $this->load->library('freeswitch_lib');
                $this->load->module('freeswitch/freeswitch');
                $command = "api reloadacl";
                $response = $this->freeswitch_model->reload_freeswitch($command);
                $this->session->set_userdata('astpp_notification', $response);
            }
            $this->session->set_flashdata('astpp_errormsg', gettext('IP Added Sucessfully.'));
        }
    }
    if ($action == 'delete') {
        $this->permission->customer_web_record_permission($id, 'ip_map', 'user/user_ipmap/');
        $this->db->delete('ip_map', array(
            'id' => $id
        ));
        $this->session->set_flashdata('astpp_notification', gettext('IP Removed Sucessfully.'));
    }
    redirect(base_url() . "user/user_ipmap/");
}

function user_sipdevices()
{
    $data['page_title'] = gettext('SIP Devices');
    $this->session->set_userdata('advance_search', 0);
    $data['grid_fields'] = $this->user_form->build_user_sipdevices();
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_user_sipdevices_search());
    $this->load->view('view_user_sipdevices_list', $data);
}

function user_sipdevices_json()
{
    $account_data = $this->session->userdata("accountinfo");
    $json_data = array();
    $count_all = $this->user_model->user_sipdevices_list(false, $account_data['id']);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $devices_result = array();
    $query = $this->user_model->user_sipdevices_list(true, $account_data['id'], $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    foreach ($query as $key => $value) {
        $path_true = base_url() . '/assets/images/true.png';
        $path_false = base_url() . '/assets/images/false.png';
        $voicemail_enabled = $value['voicemail_enabled'] == 'true' ? '<img src=' . $path_true . ' style="height:20px;width:20px;" title="Enable">' : '<img src=' . $path_false . ' style="height:20px;width:20px;" title="Disable">';
        $this->load->module ( "freeswitch" );
        $json_data['rows'][] = array(
            'cell' => array(
                '<input type="checkbox" name="chkAll" id="' . $value['id'] . '" class="ace chkRefNos" onclick="clickchkbox(' . $value['id'] . ')" value=' . $value['id'] . '><lable class="lbl"></lable>',
                '<a href="' . base_url() . 'user/user_sipdevices_edit/' . $value['id'] . '/" style="cursor:pointer;color:#3b3280" rel="facebox" title="Edit">' . $value['username'] . '</a>',
                $value['password'],
                $value['effective_caller_id_name'],
                $value['effective_caller_id_number'],
                $this->common->convert_GMT_to('', '', $value['creation_date']),
                $this->common->convert_GMT_to('', '', $value['last_modified_date']),
                $voicemail_enabled,
                $this->common->get_status('status', 'sip_devices', $value),
                    // Jaimin ASTPPCOM-984
                $this->freeswitch->get_advance_buttons_fssipdevices ( $value ['id'] )
                    // End
            )
        );
    }
    echo json_encode($json_data);
}

function user_sipdevices_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action']);
        unset($action['advance_search']);
        $this->session->set_userdata('user_sipdevices_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'user/user_sipdevices/');
    }
}

function user_sipdevices_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('user_sipdevices_search', "");
}

function user_sipdevices_add()
{
    $data['page_title'] = gettext('Create SIP Device');
    $data['form'] = $this->form->build_form($this->user_form->build_user_sipdevices_form(), "");
    $this->load->view('view_user_sipdevices_add_edit', $data);
}

function user_sipdevices_edit($edit_id = '')
{
    $this->permission->customer_web_record_permission($edit_id, 'sip_devices', 'user/user_sipdevices/');
    $account_data = $this->session->userdata("accountinfo");
    $data['page_title'] = gettext('Edit SIP device');
    $where = array(
        'id' => $edit_id
    );
    $sipdevice_info = $this->user_model->user_sipdevice_info($edit_id);
    $data['form'] = $this->form->build_form($this->user_form->build_user_sipdevices_form($edit_id), $sipdevice_info);
    $this->load->view('view_user_sipdevices_add_edit', $data);
}

function user_sipdevices_save()
{
    $add_array = $this->input->post();
    $data['form'] = $this->form->build_form($this->user_form->build_user_sipdevices_form($add_array['id']), $add_array);
    if ($add_array['id'] != '') {
        $data['page_title'] = gettext('Edit SIP Devices');
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
            echo $data['validation_errors'];
            exit();
        } else {
            unset($add_array['fs_username']);
            $this->db->select('username');
            $fs_username = (array) $this->db->get_where("sip_devices", array(
                "id" => $add_array['id']
            ))->first_row();
            $add_array['fs_username'] = $fs_username['username'];
            $this->user_model->user_sipdevice_edit($add_array, $add_array['id']);
            echo json_encode(array(
                "SUCCESS" => gettext("SIP Device Updated Successfully!")
            ));
            exit();
        }
    } else {
        $data['page_title'] = gettext('Create SIP Device');
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
            echo $data['validation_errors'];
            exit();
        } else {
            $this->user_model->user_sipdevice_add($add_array);
            echo json_encode(array(
                "SUCCESS" => gettext("SIP Device Added Successfully!")
            ));
            exit();
        }
    }
}

function user_sipdevices_delete($id)
{
    $this->permission->customer_web_record_permission($id, 'sip_devices', 'user/user_sipdevices/');
    $this->db->delete('sip_devices', array(
        'id' => $id
    ));
    $this->session->set_flashdata('astpp_notification', gettext('SIP Device Removed Sucessfully!'));
    redirect(base_url() . "user/user_sipdevices/");
}

function user_sipdevices_delete_multiple()
{
    $ids = $this->input->post("selected_ids", true);
    $where = "id IN ($ids)";
    $this->db->delete("sip_devices", $where);
    echo TRUE;
}

function user_animap_list()
{
    $data['page_title'] = gettext('Pinless CLI');
    $this->session->set_userdata('advance_search', 0);
    $data['grid_fields'] = $this->user_form->build_user_animap();
    $this->load->view('view_user_animap', $data);
}

function user_animap_list_json()
{
    $account_data = $this->session->userdata("accountinfo");
    $json_data = array();
    $where = array(
        "accountid" => $account_data['id']
    );
    $count_all = $this->db_model->countQuery("*", "ani_map", $where);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->db_model->select("*", "ani_map", $where, "id", "ASC", $paging_data["paging"]["page_no"], $paging_data["paging"]["start"]);
    $grid_fields = json_decode($this->user_form->build_user_animap());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
}

function user_animap_action($action = "", $aniid = "")
{
    $accountinfo = $this->session->userdata("accountinfo");
    if ($aniid == '' && $_POST['number'] != '') {

        $this->db->where('number', $_POST['number']);
        $this->db->select('count(id) as count');
        $cnt_result = $this->db->get('ani_map');
        $cnt_result = $cnt_result->first_row();
        if ($cnt_result->count == 0) {
            if ($_POST['number'] != "") {

                $insert_arr = array(
                    "number" => $_POST['number'],
                    "accountid" => $accountinfo['id'],
                    "context" => "default",
                    "reseller_id" => $accountinfo['reseller_id'],
                    "creation_date" => gmdate("Y-m-d H:i:s"),
                    "last_modified_date" => gmdate("Y-m-d H:i:s")
                );
                $this->db->insert("ani_map", $insert_arr);
                $this->session->set_flashdata('astpp_errormsg', gettext('Caller ID Added Sucessfully!'));
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Please Enter Caller ID value.'));
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Caller ID already Exists.'));
        }
    }
    if ($action == "delete") {
        $this->db_model->delete("ani_map", array(
            "id" => $aniid
        ));
        $this->session->set_flashdata('astpp_notification', gettext('Caller ID removed sucessfully!'));
    }
    redirect(base_url() . "user/user_animap_list/");
}

function user_alert_threshold()
{
    $data['page_title'] = gettext('Alert Threshold');
    $accountinfo = $this->session->userdata("accountinfo");
    $add_array = $this->input->post();

    if (! empty($add_array)) {
        $data['form'] = $this->form->build_form($this->user_form->user_alert_threshold($accountinfo['id']), $add_array);
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
        } else {
            unset($add_array['action'], $add_array['id']);
            $this->user_model->edit_alert_threshold($add_array, $accountinfo['id']);
            $this->session->set_flashdata('astpp_errormsg', gettext('Alert Threshold Updated Successfully!'));
            redirect(base_url() . 'user/user_alert_threshold/');
        }
    } else {

        $where = array(
            'id' => $accountinfo["id"]
        );
        $account = $this->db_model->getSelect("notify_credit_limit,notify_flag,notify_email", "accounts", $where);
        $data['form'] = $this->form->build_form($this->user_form->user_alert_threshold(), (array) $account->first_row());
    }
    $this->load->view('view_user_alert_threshold', $data);
}

function user_cdrs_report()
{
    $this->session->set_userdata('advance_search_date', 1);
    $accountinfo = $this->session->userdata('accountinfo');
    $data['page_title'] = gettext('CDRs');
    $data['search_flag'] = true;
    $data["grid_buttons"] = $this->user_form->build_cdrs_report_buttons();
    $data['grid_fields'] = $this->user_form->build_cdrs_report($accountinfo['type']);
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_cdrs_report_search($accountinfo['type']));
    $this->load->view('view_user_cdrs_report', $data);
}

function user_cdrs_report_json()
{
    $accountinfo = $this->session->userdata('accountinfo');
    $variable = $accountinfo['type'] != 3 ? 'total_debit' : 'total_cost';
    $count_res = $this->user_model->getuser_cdrs_list(false, "", "");
    $count_all = (array) $count_res->first_row();
    $paging_data = $this->form->load_grid_config($count_all['count'], $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->getuser_cdrs_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"], false);
    $grid_fields = json_decode($this->user_form->build_cdrs_report($accountinfo['type']));
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

    if ($count_all['count'] > 0) {
        $search_arr = $this->session->userdata('user_cdrs_report_search');
        $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
        $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf("%02d", $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
        $json_data['rows'][] = array(
            "cell" => array(
                "<b>Grand Total</b>",
                "",
                "",
                "",
                "",
                "<b>" . $duration . "</b>",
                //ASTPPCOM-1348_kanu_start
                "<b>" . $this->common_model->calculate_currency_locale($count_all[$variable] - $count_all['free_debit'], "", "", true, false) . "</b>",
                //ASTPPCOM-1348_kanu_end
                "",
                "",
                ""
            )
        );
    }
    echo json_encode($json_data);
}

function user_report_recording_download($file_name)
{
    $file_name = $this->config->item('recordings_path') . $file_name;
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file_name));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    ob_clean();
    flush();
    readfile($file_name);
    exit();
}

function user_cdrs_report_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $this->session->set_userdata('cdrs_year', $this->input->post('cdrs_year'));
        $action = $this->input->post();
        if ($action['search_in'] == 'minutes') {
            if (isset($action['billseconds']['billseconds'])) {
                $duration = explode(':', $action['billseconds']['billseconds']);
                if (isset($duration[0]) && isset($duration[1])) {
                    if (is_numeric($duration[0]) && is_numeric($duration[1])) {
                        $action['billseconds']['billseconds'] = (60 * $duration[0]) + $duration[1];
                    }
                }
            }
        }
        unset($action['action']);
        $this->session->set_userdata('advance_search_date', 0);
        unset($action['cdrs_year']);
        unset($action['advance_search']);
        if (isset($action['debit']['debit']) && $action['debit']['debit'] != '') {
            $action['debit']['debit'] = $this->common_model->add_calculate_currency($action['debit']['debit'], "", '', true, false);
        }
        if(!empty($action['callstart'][0])){
            $action['callstart'][0]=$this->common->convert_GMT_new ( $action['callstart'][0]);
        }
        if(!empty($action['callstart'][1])){
            $action['callstart'][1]=$this->common->convert_GMT_new ( $action['callstart'][1]);
        }
        $this->session->set_userdata('user_cdrs_report_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'user/user_cdrs_report/');
    }
}

function user_cdrs_report_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('advance_search_date', 0);
    $this->session->set_userdata('user_cdrs_report_search', "");
}

function user_cdrreport_export()
{
    $account_info = $accountinfo = $this->session->userdata('accountinfo');
    $currency_id = $account_info['currency_id'];
    $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
    $count_res = $this->user_model->getuser_cdrs_list(false, "", "");
    $count_all = (array) $count_res->first_row();
    ob_clean();
    $customer_array[] = array(
        gettext("Date"),
        gettext("Caller ID"),
        gettext("Called Number"),
        gettext("SIP User"),
        gettext("Destination"),
        gettext("Duration"),
        gettext("Debit") . "(" . $currency . ")",
        gettext("Disposition"),
        gettext("Direction")
    );
    if ($count_all['count'] > 0) {
        $query = $this->user_model->getuser_cdrs_list(true, '', '', true);
        $currency_info = $this->common->get_currency_info();
        $search_arr = $this->session->userdata('user_cdrs_report_search');
        $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
        foreach ($query->result_array() as $value) {
            $duration = ($show_seconds == 'minutes') ? ($value['billseconds'] > 0) ? floor($value['billseconds'] / 60) . ":" . sprintf("%02d", $value['billseconds'] % 60) : "00:00" : $value['billseconds'];
            $customer_array[] = array(
                $this->common->convert_GMT_to('', '', $value['callstart']),
                $value['callerid'],
                $value['callednum'],
                $value['sip_user'],
                $value['notes'],
                $duration,
                $this->common->calculate_currency_manually($currency_info, $value['debit'], false),
                $value['disposition'],
                $value['call_direction']
            );
        }
        $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf("%02d", $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
        $customer_array[] = array(
            gettext("Grand Total"),
            "",
            "",
            "",
            "",
            "",
            $duration,
            $this->common->calculate_currency_manually($currency_info, $count_all['total_debit']),
            ""
        );
    }
    $this->load->helper('csv');
    array_to_csv($customer_array, 'Customer_CDR_' . date("Y-m-d") . '.csv');
}

function user_payment($action = "")
{
    $this->load->module("user/payment");
    if ($action == "GET_AMT") {
        $amount = $this->input->post("value", true);
        $this->user_convert_amount($amount);
    } else {
        $this->payment->index();
    }
}

function user_fund_transfer()
{
    $accountinfo = $this->session->userdata('accountinfo');
    if ($accountinfo['posttoexternal'] != '1') {
        $data['page_title'] = gettext('Send Credit');
        $account = (array) $this->db->get_where('accounts', array(
            "id" => $accountinfo['id']
        ))->first_row();
        $currency = (array) $this->db->get_where('currency', array(
            "id" => $account['currency_id']
        ))->first_row();
        $data['form'] = $this->form->build_form($this->user_form->build_user_fund_transfer_form($account['number'], $currency['currency'], $accountinfo['id']), '');
        $this->load->view('view_user_fund_transfer', $data);
    } else {

        $this->session->set_flashdata('astpp_danger_alert', 'Permission Denied!');
        redirect(base_url() . 'user/user/');
    }
}

function user_fund_transfer_save()
{
    $accountinfo = $this->session->userdata('accountinfo');
    if ($accountinfo['posttoexternal'] != '1') {
        $data['page_title'] = gettext('Send Credit');
        $post_array = $this->input->post();

        $accountinfo = $this->session->userdata('accountinfo');
        $account = (array) $this->db->get_where('accounts', array(
            "id" => $accountinfo['id']
        ))->first_row();
        $currency = (array) $this->db->get_where('currency', array(
            "id" => $account['currency_id']
        ))->first_row();
        $data['form'] = $this->form->build_form($this->user_form->build_user_fund_transfer_form($account['number'], $currency['currency'], $accountinfo['id']), $post_array);
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
        } else {

            if (trim($post_array['fromaccountid']) != trim($post_array['toaccountid'])) {

                $account_info = $this->session->userdata('accountinfo');
                if ($this->session->userdata('logintype') == '0') {
                    $acc_balance = $this->common->get_field_name('balance', 'accounts', array(
                        'id' => $account_info['id'],
                        'status' => 0,
                        'deleted' => 0
                    ));

                    $balance = ($account_info["posttoexternal"] == 1) ? ($account_info["credit_limit"] - $acc_balance) : ($acc_balance);

                    $toid = $this->common->get_field_name('id', 'accounts', array(
                        'number' => $post_array['toaccountid'],
                        'status' => 0,
                        'deleted' => 0
                    ));

                    $toaccountinfo = (array) $this->db->get_where('accounts', array(
                        'number' => $post_array['toaccountid'],
                        'status' => 0,
                        'deleted' => 0
                    ), 1)->first_row();
                } else {
                    $acc_balance = $this->common->get_field_name('balance', 'accounts', array(
                        'id' => $account_info['id'],
                        'status' => 0,
                        'deleted' => 0
                    ));

                    $balance = ($account_info["posttoexternal"] == 1) ? ($account_info["credit_limit"] - $acc_balance) : ($acc_balance);

                    $toid = $this->common->get_field_name('id', 'accounts', array(
                        'number' => $post_array['toaccountid'],
                        'status' => 0,
                        'deleted' => 0
                    ));
                    $toaccountinfo = (array) $this->db->get_where('accounts', array(
                        'number' => $post_array['toaccountid'],
                        'status' => 0,
                        'deleted' => 0
                    ), 1)->first_row();
                }
                if (! empty($toaccountinfo)) {
                    if ($toaccountinfo['posttoexternal'] == 1) {
                        $this->session->set_flashdata('astpp_notification', gettext('Can not trnasfer fund to postpaid customer'));
                        redirect(base_url() . 'user/user_fund_transfer/');
                    } else {
                        if ($post_array['toaccountid']) {
                            $reseller_id = $toaccountinfo['reseller_id'];

                            if (($toaccountinfo['type'] != 3) && ($toaccountinfo['type'] != 0)) {

                                $this->session->set_flashdata('astpp_notification', gettext('Access Denied! unable transfer fund to this account'));
                            }                            
                            // Kinjal ASTPPCOM-1319 Start
                            $post_array['credit'] = number_format($this->common_model->add_calculate_currency($post_array['credit'], '', '', false, false,'en_En'), 4);
                            // Kinjal ASTPPCOM-1319 END

                            $minimum_fund = (array) $this->db->get_where('system', array(
                                "name" => "minimum_fund_transfer"
                            ), 1)->first_row();

                            // Kinjal ASTPPCOM-1319 Start
                            $minimum_fund['value'] = number_format($this->common_model->add_calculate_currency($minimum_fund['value'], '', '', false, false,'en_En'), 4);
                            $from_currency = common_model::$global_config['system_config'] ['base_currency'];
                            $minimum_fund_value = number_format($this->common_model->add_calculate_currency($minimum_fund['value'],$from_currency, $currency['currency'], false, false,'en_En'), 4);
                            // Kinjal ASTPPCOM-1319 END
                            if ($post_array['toaccountid'] == $account_info['number']) {
                                $this->session->set_flashdata('astpp_notification', gettext('You can not transfer fund in same account.'));
                            } elseif ($reseller_id != $account_info['reseller_id']) {
                                $this->session->set_flashdata('astpp_notification', gettext('You can only transfer fund in same level account.'));
                            } elseif ($post_array['toaccountid'] == '') {
                                $this->session->set_flashdata('astpp_notification', gettext('Please enter To account number.'));
                            } elseif (empty($post_array['credit'])) {
                                $this->session->set_flashdata('astpp_notification', gettext('Please enter valid amount.'));
                            } elseif ($post_array['credit'] > $balance) {
                                $this->session->set_flashdata('astpp_notification', gettext('You have insufficient balance.'));
                            } elseif ($toid <= 0 || ! isset($post_array['toaccountid'])) {
                                $this->session->set_flashdata('astpp_notification', gettext('Please enter valid account number.'));
                            } elseif ($post_array['credit'] < 0) {
                                $this->session->set_flashdata('astpp_notification', gettext('Please enter amount greater then 0.'));
                            } elseif ($minimum_fund['value'] > $post_array['credit']) {
                                  // Kinjal ASTPPCOM-1319 Start
                                  $this->session->set_flashdata('astpp_notification', gettext('You need to enter minimum').' '.$minimum_fund_value.' '.gettext('for fund transfer.'));
                                  // Kinjal ASTPPCOM-1319 END
                            } elseif (! isset($toid) || ! isset($post_array['toaccountid'])) {
                                $this->session->set_flashdata('astpp_notification', gettext('Please enter valid account number!'));
                            } elseif ($post_array['credit'] < 0 || $post_array['credit'] > $balance) {
                                $this->session->set_flashdata('astpp_notification', gettext('Insuffiecient amount !'));
                            } elseif (!$this->check_calls_running()) {
                                $this->session->set_flashdata('astpp_notification', gettext('You can not transfer fund when your calls are running!'));
                            } else {
                                $from['id'] = $post_array['id'];
                                $from['account_currency'] = $post_array['account_currency'];
                                $from['accountid'] = $post_array['fromaccountid'];

                                if ($account['posttoexternal'] == 1) {
                                    $from['credit'] = abs($post_array['credit']);
                                    $from['payment_type'] = '0';
                                } else {
                                    $from['credit'] = abs($post_array['credit']);
                                    $from['payment_type'] = 'debit';
                                }
                                // Kinjal ASTPPCOM-1319 Start
                                $from['credit'] = str_replace(',', '.', $from['credit']);
                                // Kinjal ASTPPCOM-1319 END
                                $from['posttoexternal'] = $account['posttoexternal'];

                                $from['notes'] = $post_array['notes'];
                                $from['action'] = 'save';
                                $to['id'] = $toid;
                                $to['account_currency'] = $post_array['account_currency'];
                                $to['accountid'] = $post_array['toaccountid'];
                                if ($toaccountinfo['posttoexternal'] == 0) {
                                    $to['credit'] = abs($post_array['credit']);
                                    $to['payment_type'] = '0';
                                } else {
                                    $to['credit'] = abs($post_array['credit']);
                                    $to['payment_type'] = 'debit';
                                }
                                $to['notes'] = $post_array['notes'];
                                $to['action'] = 'save';

                                if ($account['reseller_id'] == 0) {
                                    $where = array(
                                        "accountid" => 1
                                    );
                                } else {
                                    $where = array(
                                        "accountid" => $account['id']
                                    );
                                }
                                $from_account_info = (array) $this->db->get_where('accounts', array(
                                    "id" => $from['id']
                                ))->first_row();

                                $from_currency_info = (array) $this->db->get_where('currency', array(
                                    "id" => $from_account_info['currency_id']
                                ))->first_row();

                                $to_account_info = (array) $this->db->get_where('accounts', array(
                                    "id" => $to['id']
                                ))->first_row();

                                $to_currency_info = (array) $this->db->get_where('currency', array(
                                    "id" => $to_account_info['currency_id']
                                ))->first_row();

                                $insert_payment_arr_from = array(
                                    "accountid" => $from_account_info['id'],
                                    "reseller_id" => $from_account_info['reseller_id'],
                                    "product_category" => '3',
                                    "price" => $from['credit'],
                                    "payment_by" => 'Manual',
                                    "payment_method" => "Manual",
                                    "order_item_id" => 0,
                                    "charge_type" => "FUNDTRANSFER",
                                    "description" => "Fund has been transfered from " . $from_account_info['number'] . " '(' " . $to_account_info['number'] . " ')' ",
                                    "invoice_type" => "debit",
                                    "is_update_balance" => "true"
                                );
                                $insert_payment_arr_from['is_apply_tax'] = "false";
                                $this->payment->add_payments_transcation($insert_payment_arr_from, $from_account_info, $from_currency_info);

                                $insert_payment_arr_to = array(
                                    "accountid" => $to_account_info['id'],
                                    "reseller_id" => $to_account_info['reseller_id'],
                                    "product_category" => '3',
                                    "price" => $from['credit'],
                                    "payment_by" => 'Manual',
                                    "payment_method" => "Manual",
                                    "order_item_id" => 0,
                                    "charge_type" => "REFILL",
                                    "description" => "Fund has been sucessfully received from " . $from_account_info['number'],
                                    "invoice_type" => "credit",
                                    "is_update_balance" => "true"
                                );
                                $insert_payment_arr_to['is_apply_tax'] = "false";

                                $this->payment->add_payments_transcation($insert_payment_arr_to, $to_account_info, $to_currency_info);
                                $from_account_info['amount'] = $from['credit'];
                                $to_account_info['to_currency'] = $this->common->get_field_name ( 'currency', 'currency', $to_account_info['currency_id']);
                                $to_account_info['number'] =   $from_account_info['number'];
                                $to_account_info['amount'] = $from['credit'];
                                $this->common->mail_to_users('customer_refill_balance', $to_account_info);
                                $from_account_info['number'] =  $to_account_info['number'];
                                $this->common->mail_to_users('balance_transfer', $from_account_info);

                                $this->session->set_flashdata('astpp_errormsg', gettext('Transfer successfully!'));
                            }
                        } else {
                            $this->session->set_flashdata('astpp_notification', gettext('Account number not found.'));
                        }
                    }
                } else {
                    $this->session->set_flashdata('astpp_notification', gettext('Account number not found.'));
                }
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('You can not transfer fund in same account.'));
            }
            redirect(base_url() . 'user/user_fund_transfer/');
        }
        $this->load->view('view_user_fund_transfer', $data);
    } else {
        $this->session->set_flashdata('astpp_danger_alert', gettext('Permission Denied!'));
        redirect(base_url() . 'user/user/');
    }
}
private function check_calls_running(){
 $accountinfo = $this->session->userdata('accountinfo');
 $command = "api show channels";
 $this->load->module('freeswitch');
 $response = $this->freeswitch_model->reload_live_freeswitch($command);
 $calls = array();
 $calls_final = array();
 $data_header = array();
 $k = 0;
 $data = explode("\n", $response);
 for ($i = 0; $i < count($data) - 2; $i ++) {
     if (trim($data[$i]) != '') {
         if (count($data_header) == 0 || substr($data[$i], 0, 4) == "uuid") {
             $data_header = explode(",", $data[$i]);
         } else {
             $bridge_str = "";
             $string = " " . $data[$i];
             $ini = strpos($string, '[');
             if ($ini != 0) {
                 $ini += strlen('[');
                 $len = strpos($string, ']', $ini) - $ini;
                 $bridge_str = substr($string, $ini, $len);
             }
             if ($bridge_str != '') {
                 $new_bridge_str = str_replace(',', '--', $bridge_str);
                 $data[$i] = str_replace($bridge_str, $new_bridge_str, $data[$i]);
             }
             $data_call = explode(",", $data[$i]);
             for ($j = 0; $j < count($data_call); $j ++) {
                 $calls[$k][@$data_header[$j]] = @$data_call[$j];
                 $calls_final[@$calls[$k]['uuid']] = @$calls[$k];
             }
             $k ++;
         }
     }
 }
 $json_data = array();
 $count = 0;
 $query = $this->db_model->select("username", "sip_devices", array(
  'status' => 0,
  'accountid'=>$accountinfo['id']), "id", "desc", "10", "");
 $sip_device_array=$query->result_array();
 
 $sip_device_final_array=array();
 foreach ($sip_device_array as $key => $value) {
    $sip_device_final_array[$value['username']] =$value['username'];
}
$response_return =true;
if(!empty($calls)){
 
 foreach ($calls as $key => $value) {
    $calls[$i]['presence_data'] = @$calls_final[$value['call_uuid']]['presence_data'];
    $livecall_data = explode("|||", $calls[$i]['presence_data']);
    $account_explode=explode('(',$livecall_data[1]);    
    if (isset($value['state']) && ($value['state'] == 'CS_EXCHANGE_MEDIA' || $value['state'] == 'CS_CONSUME_MEDIA') ) {
       if($accountinfo['number'] == rtrim($account_explode[1],')') || array_key_exists($value['initial_dest'],$sip_device_final_array)){
           $response_return=false;              
       }
   } 
}
}
return $response_return;
}
function user_opensips()
{
    if (common_model::$global_config['system_config']['opensips'] == 1) {
        $this->permission->permission_redirect_url("user/user/");
    }
    $data['username'] = $this->session->userdata('user_name');
    $data['page_title'] = gettext('Opensips List');
    $data['search_flag'] = true;
    $data["fs_grid_buttons"] = $this->user_form->build_user_opensips_buttons();
    $data['grid_fields'] = $this->user_form->build_user_opensips();
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_user_opensips_search());
    $this->load->view('view_opensips_list', $data);
}

function user_opensips_json()
{
    $accountinfo = $this->session->userdata("accountinfo");
    $json_data = array();
    $count_all = $this->user_model->get_user_opensips(false, $accountinfo['number']);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->get_user_opensips(true, $accountinfo['number'], $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    $grid_fields = json_decode($this->user_form->build_user_opensips());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
}

function user_opensips_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('user_opensips_search', "");
}

function user_opensips_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);

    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action']);
        unset($action['advance_search']);
        $this->session->set_userdata('user_opensips_search', $action);
    }
    if ($ajax_search != 1) {
        redirect(base_url() . 'user/user_opensips/');
    }
}

function user_opensips_add()
{
    $accountinfo = $this->session->userdata("accountinfo");
    $data['username'] = $this->session->userdata('user_name');
    $data['flag'] = 'create';
    $data['page_title'] = gettext('Create Opensips');
    $data['form'] = $this->form->build_form($this->user_form->build_user_opensips_form(), '');
    $this->load->view('view_opensips_add_edit', $data);
}

function user_opensips_edit($edit_id)
{
    $data['page_title'] = gettext('Edit Opensips');
    $db_config = Common_model::$global_config['system_config'];
    $opensipdsn = "mysqli://" . $db_config['opensips_dbuser'] . ":" . $db_config['opensips_dbpass'] . "@" . $db_config['opensips_dbhost'] . "/" . $db_config['opensips_dbname'] . "?char_set=utf8&dbcollat=utf8_general_ci&cache_on=true&cachedir=";
    $this->opensips_db = $this->load->database($opensipdsn, true);
    $where = array(
        'id' => $edit_id
    );
    $this->opensips_db->where($where);
    $account = $this->opensips_db->get("subscriber");
    foreach ($account->result_array() as $key => $value) {
        $edit_data = $value;
    }
    $data['form'] = $this->form->build_form($this->user_form->build_user_opensips_form($edit_id), $edit_data);
    $this->load->view('view_opensips_add_edit', $data);
}

function user_opensips_save()
{
    $add_array = $this->input->post();
    $data['form'] = $this->form->build_form($this->user_form->build_user_opensips_form(), $add_array);
    if ($add_array['id'] != '') {
        $data['page_title'] = gettext('Edit Opensips');
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
            echo $data['validation_errors'];
            exit();
        } else {
            $auth_flag = $this->validate_device_data($add_array);
            if ($auth_flag == "TRUE") {
                $this->user_model->user_opensips_edit($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext("OpenSips updated successfully!")
                ));
                exit();
            } else {
                echo json_encode($auth_flag);
                exit();
            }
        }
    } else {
        $data['page_title'] = gettext('Add Opensips');
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
            echo $data['validation_errors'];
            exit();
        } else {
            $auth_flag = $this->validate_device_data($add_array);
            if ($auth_flag == "TRUE") {
                $this->user_model->user_opensips_add($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext("OpenSips added successfully!")
                ));
                exit();
            } else {
                echo json_encode($auth_flag);
                exit();
            }
        }
    }
}

function validate_device_data($data)
{
    if (isset($data["username"]) && $data["username"] != "") {
        $db_config = Common_model::$global_config['system_config'];
        $opensipdsn = "mysqli://" . $db_config['opensips_dbuser'] . ":" . $db_config['opensips_dbpass'] . "@" . $db_config['opensips_dbhost'] . "/" . $db_config['opensips_dbname'] . "?char_set=utf8&dbcollat=utf8_general_ci&cache_on=true&cachedir=";
        $this->opensips_db = $this->load->database($opensipdsn, true);
        $where = array(
            "username" => $data["username"]
        );
        if ($data['id'] != "") {
            $this->opensips_db->where("id <>", $data['id']);
        }
        $this->opensips_db->where($where);
        $auth_flag = $this->opensips_db->get("subscriber");
        $auth_flag = $auth_flag->num_rows();
        if ($auth_flag == 0) {
            return "TRUE";
        } else {
            return array(
                "username_error" => gettext("Duplicate Username Found.Username Must be Unique")
            );
        }
    } else {
        return array(
            "username_error" => gettext("User name is required field.")
        );
    }
    return "0";
}

function user_opensips_delete($id)
{
    $this->user_model->user_opensips_delete($id);
    $this->session->set_flashdata('astpp_errormsg', gettext('Opensips Device Removed Successfully!.'));
    redirect(base_url() . "user/user_opensips/");
}

function user_opensips_delete_multiple()
{
    $db_config = Common_model::$global_config['system_config'];
    $opensipdsn = "mysqli://" . $db_config['opensips_dbuser'] . ":" . $db_config['opensips_dbpass'] . "@" . $db_config['opensips_dbhost'] . "/" . $db_config['opensips_dbname'] . "?char_set=utf8&dbcollat=utf8_general_ci&cache_on=true&cachedir=";
    $this->opensips_db = $this->load->database($opensipdsn, true);
    $ids = $this->input->post("selected_ids", true);
    $where = "id IN ($ids)";
    $this->opensips_db->where($where);
    $this->opensips_db->delete("subscriber");
    echo TRUE;
}

function user_cdrs()
{
    $data['username'] = $this->session->userdata('user_name');
    $accountinfo = $this->session->userdata('accountinfo');
    $data['page_title'] = gettext('CDRs');
    $accounttype = strtolower($this->common->get_entity_type('', '', $accountinfo['type']));
    $this->load->module('reports/reports');
    $data['grid_fields'] = $this->reports->reports_form->build_report_list_for_user($accounttype);
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_user_opensips_search());
    $data['accounttype'] = $accounttype;
    $this->load->view('view_user_cdrs', $data);
}

function user_cdrs_json()
{
    $accountinfo = $this->session->userdata('accountinfo');
    $accounttype = strtolower($this->common->get_entity_type('', '', $accountinfo['type']));
    $this->load->module('reports/reports');
    $this->reports->customer_cdrreport($accountinfo['id'], $accounttype);
}

function user_details_search($module_name)
{
    $action = $this->input->post();
    $this->session->set_userdata('left_panel_search_' . $module_name, "");
    if (! empty($action['left_panel_search'])) {
        $this->session->set_userdata('left_panel_search_' . $module_name, $action['left_panel_search']);
    }
}

function user_provider_report_export()
{
    $this->load->module('reports/reports');
    $this->provider_cdrreport_export();
}

function user_provider_cdrs_report()
{
    $this->session->set_userdata('advance_search_date', 1);
    $accountinfo = $this->session->userdata('accountinfo');
    $data['page_title'] = gettext('Provider Outbound');
    $data['search_flag'] = true;
    $data["grid_buttons"] = $this->user_form->build_provider_report_buttons();
    $data['grid_fields'] = $this->user_form->build_provider_report($accountinfo['type']);
    $data['form_search'] = $this->form->build_serach_form($this->user_form->build_provider_report_search($accountinfo['type']));
    $this->load->view('view_provider_cdrs_report', $data);
}

function user_provider_cdrs_report_json()
{
    $accountinfo = $this->session->userdata('accountinfo');
    $json_data = array();
    $count_all = $this->user_model->getprovider_cdrs_list(false);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->getprovider_cdrs_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    $grid_fields = json_decode($this->user_form->build_provider_report($accountinfo['type']));
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
}

function user_provider_cdrs_report_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    $this->session->set_userdata('advance_search_date', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        if (isset($action['cost']['cost']) && $action['cost']['cost'] != '') {
            $action['cost']['cost'] = $this->common_model->add_calculate_currency($action['cost']['cost'], "", '', true, false);
        }
        if ($action['search_in'] == 'minutes') {
            if (isset($action['billseconds']['billseconds'])) {
                $duration = explode(':', $action['billseconds']['billseconds']);
                if (isset($duration[0]) && isset($duration[1])) {
                    if (is_numeric($duration[0]) && is_numeric($duration[1])) {
                        $action['billseconds']['billseconds'] = (60 * $duration[0]) + $duration[1];
                    }
                }
            }
        }
        unset($action['action']);
        unset($action['advance_search']);

        $this->session->set_userdata('user_provider_cdrs_report_search', $action);
    }
    if ($ajax_search != 1) {
        redirect(base_url() . 'user/user_provider_cdrs_report/');
    }
}

function user_provider_cdrs_report_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('advance_search_date', 0);
    $this->session->set_userdata('user_provider_cdrs_report_search', "");
}

function user_provider_cdrreport_export()
{
    $account_info = $accountinfo = $this->session->userdata('accountinfo');
    $currency_id = $account_info['currency_id'];
    $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
    $count_res = $this->user_model->getprovider_cdrs_list(false, "", "");
    $count_all = (array) $count_res->first_row();
    ob_clean();
    $customer_array[] = array(
        gettext("Date"),
        gettext("Caller ID"),
        gettext("Called Number"),
        gettext("Destination"),
        gettext("Duration"),
        gettext("Cost") . "(" . $currency . ")",
        gettext("Disposition")
    );
    if ($count_all['count'] > 0) {
        $query = $this->user_model->getuser_cdrs_list(true, '', '', true);
        $currency_info = $this->common->get_currency_info();
        $search_arr = $this->session->userdata('user_provider_cdrs_report_search');
        $show_seconds = (! empty($search_arr['search_in'])) ? $search_arr['search_in'] : 'minutes';
        foreach ($query->result_array() as $value) {
            $duration = ($show_seconds == 'minutes') ? ($value['billseconds'] > 0) ? floor($value['billseconds'] / 60) . ":" . sprintf("%02d", $value['billseconds'] % 60) : "00:00" : $value['billseconds'];
            $customer_array[] = array(
                $this->common->convert_GMT_to('', '', $value['callstart']),
                $value['callerid'],
                $value['callednum'],
                filter_var($value['pattern'], FILTER_SANITIZE_NUMBER_INT),
                $duration,
                $this->common->calculate_currency_manually($currency_info, $value['cost'], false),
                $value['disposition']
            );
        }
        $duration = ($show_seconds == 'minutes') ? ($count_all['billseconds'] > 0) ? floor($count_all['billseconds'] / 60) . ":" . sprintf("%02d", $count_all['billseconds'] % 60) : "00:00" : $count_all['billseconds'];
        $customer_array[] = array(
            "Grand Total",
            "",
            "",
            "",
            $duration,
            $this->common->calculate_currency_manually($currency_info, $count_all['total_cost'], false, true),
            "",
            ""
        );
    }
    $this->load->helper('csv');
    array_to_csv($customer_array, 'Provider_CDR_' . date("Y-m-d") . '.csv');
}

function user_speeddial()
{
    $data['page_title'] = gettext("Speed Dial");
    $accountinfo = $this->session->userdata('accountinfo');
    $speeddial_res = $this->db->get_where("speed_dial", array(
        "accountid" => $accountinfo['id']
    ));
    $speeddial_info = array();
    if ($speeddial_res->num_rows() > 0) {
        $speeddial_res = $speeddial_res->result_array();
        foreach ($speeddial_res as $key => $value) {
            $speeddial_info[$value['speed_num']] = $value['number'];
        }
    }
    $data['speeddial'] = $speeddial_info;
    $data['account_data'] = $accountinfo;

    $this->load->view('view_user_speeddial', $data);
}

function user_speeddial_save()
{
    $add_array = $this->input->post();
    $accountinfo = $this->session->userdata('accountinfo');
    $where = array(
        "accountid" => $accountinfo['id']
    );
    $this->db->select('count(id) as count');
    $this->db->where($where);
    $speed_dial_result = (array) $this->db->get('speed_dial')->first_row();
    if ($speed_dial_result['count'] == 0) {
        for ($i = 0; $i <= 9; $i ++) {
            $dest_number = $add_array['number'] == $i ? $add_array['destination'] : '';
            $data[$i] = array(
                "number" => $dest_number,
                "speed_num" => $i,
                'accountid' => $accountinfo['id']
            );
        }
        $this->db->insert_batch('speed_dial', $data);
        $this->session->set_flashdata('astpp_errormsg', $data['number'].' '.gettext('Speed-dial Number Added Successfully!'));
    } else {
        $this->db->where('speed_num', $add_array['number']);
        $this->db->where('accountid', $accountinfo['id']);
        $result = $this->db->update('speed_dial', array(
            'number' => $add_array['destination']
        ));

        $this->session->set_flashdata('astpp_errormsg', $add_array['destination'].' '.gettext('Speed-dial Number Added Successfully!'));
    }
}

function user_speeddial_remove()
{
    $accountinfo = $this->session->userdata('accountinfo');
    $add_array = $this->input->post();
    if (empty($add_array['destination'])) {
        $this->session->set_flashdata('astpp_notification', gettext('Can not delete blank speeddial number'));
        die();
    }

    $updateinfo = array(
        'number' => ''
    );
    $this->db->where('speed_num', $add_array['number']);
    $this->db->where('accountid', $accountinfo['id']);
    $result = $this->db->update('speed_dial', $updateinfo);
    $this->session->set_flashdata('astpp_notification', $add_array['destination'].' '.gettext('Speed-dial Number Removed Successfully!'));
}

function user_available_products()
{
    $this->load->module("pages/pages");
    $this->pages->services();
}

function user_products_list()
{
    $accountinfo = $this->session->userdata('accountinfo');
    $data['page_title'] = gettext('My Order');
    $data['search_flag'] = true;
    $this->session->set_userdata('advance_search', 0);
    $data['grid_fields'] = $this->user_form->build_products_list_for_user();
    $data['form_search'] = $this->form->build_serach_form($this->user_form->get_user_product_search_form());
    $data["grid_buttons"] = array();
    $this->load->view('view_user_products_list', $data);
}

function user_products_list_json()
{
    $account_data = $this->session->userdata("accountinfo");
    $json_data = array();
    $count_all = $this->user_model->getproducts_list(false);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->user_model->getproducts_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    $grid_fields = json_decode($this->user_form->build_products_list_for_user());
    $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
    echo json_encode($json_data);
}

function user_products_list_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action']);
        unset($action['advance_search']);
        if (isset($action['order_id']['order_id']) && $action['order_id']['order_id'] != '') {
            $action['order_id']['order_id'] = str_replace('#', "", $action['order_id']['order_id']);
        }
        if(!empty($action['order_date'][0])){
            $action['order_date'][0]=$this->common->convert_GMT_new ( $action['order_date'][0]);
        }
        if(!empty($action['order_date'][1])){
            $action['order_date'][1]=$this->common->convert_GMT_new ( $action['order_date'][1]);
        }
        $this->session->set_userdata('orders_list_search', $action);
    }
    if (@$ajax_search != 1) {
        redirect(base_url() . 'user/user_ipmap/');
    }
}

function user_products_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('orders_list_search', "");
}

function user_pin_add()
{
    $account_info = $this->session->userdata('accountinfo');
    $this->db->where('id', $account_info['id']);
    $this->db->select('*');
    $query = $this->db->get('accounts');
    $result = $query->result_array();
    $data['pin_info'] = $result[0];
    $data['page_title'] = gettext('Pin');
    $this->load->view('view_user_pin', $data);
}

function user_pin_save()
{
    $add_array = $this->input->post();
    $account_info = $this->session->userdata('accountinfo');
    $id = $account_info['id'];
    $this->user_model->edit_user_pin($add_array, $id);
    $this->session->set_flashdata('astpp_errormsg', gettext('Pin Updated Successfully!.'));
    redirect(base_url() . "user/user_pin_add/");
}

function user_recharge_info()
{
    $data['page_title'] = gettext('Refill Coupon List');
    $this->load->view('view_user_recharge_info', $data);
}

function user_ipmap_delete_multiple()
{
    $ids = $this->input->post("selected_ids", true);
    $where = "id IN ($ids)";
    $this->db->where($where);
    echo $this->db->delete("ip_map");
}

function user_customer_validate_ip()
{
    $add_array = $this->input->post();
    if (! empty($add_array)) {
        $ip = $add_array['ip'];
        if (strpos($ip, '/') !== false) {
            $add_array['ip'] = $add_array['ip'];
        } else {
            $add_array['ip'] = $add_array['ip'] . '/32';
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

function user_animap_delete_multiple()
{
    $ids = $this->input->post("selected_ids", true);
    $where = "id IN ($ids)";
    $this->db->where($where);
    echo $this->db->delete("ani_map");
}

function user_orders_complete($order_id)
{
    $this->load->module('orders/orders');
    $this->orders->orders_complete($order_id);
}

function user_orders_terminate($order_id)
{
    $this->load->module('orders/orders');
    $this->orders->orders_terminate($order_id);
}

function user_orders_termination()
{
    $this->load->module('orders/orders');
    $this->orders->orders_users_terminate();
}

function user_invoice_unpaid_pay()
{
    $paymnet_array = $this->input->post();
    $data['paypal_url'] = $paymnet_array['paypal_url'];
    $data['total_amt'] = $paymnet_array['amount'];
    $data['paypal_email_id'] = $paymnet_array['business'];
    $data['item_name'] = (isset($paymnet_array['item_name']) && $paymnet_array['item_name'] != '') ? $paymnet_array['item_name'] : "Product";
    $data['account_id'] = $paymnet_array['custom'];
    $data['order_id'] = $paymnet_array['item_number'];
    $data['to_currency'] = $paymnet_array['currency_code'];
    $data['notify_url'] = $paymnet_array['notify_url'];
    $data['return'] = $paymnet_array['return'];
    $data['cancel_return'] = $paymnet_array['cancel_return'];
    $this->load->view("paypal_redirect", $data);
}

function user_get_current_info()
{
    $account_data = $this->session->userdata("accountinfo");
    // ASTPPCOM-891 Ashish start
    $start_date = $this->common->convert_GMT_new (date("Y-m-d") . " 00:00:01");
	$end_date = $this->common->convert_GMT_new (date("Y-m-d H:i:s"));
    // ASTPPCOM-891 Ashish end
    $where = array(
        "accountid" => $account_data['id'],
        "order_date >=" => date("Y-m-d 00:00:01"),
        "order_date <=" => date("Y-m-d H:i:s")
    );
    $count_all = $this->db_model->countQuery("*", "orders", $where);
    $result_array['product_count'] = $count_all;
    $where_cdr = array(
        "accountid" => $account_data['id'],
        // ASTPPCOM-891 Ashish start
        "callstart >=" => $start_date,
        "callstart <=" => $end_date
        // ASTPPCOM-891 Ashish end
    );
    $count_cdrs = $this->db_model->countQuery("*", "cdrs", $where_cdr);
    $result_array['call_count'] = $count_cdrs;

    $balance_array = $this->db_model->getSelect("posttoexternal,credit_limit,balance", "accounts", array(
        'id' => $account_data['id']
    ));
    $balance = (array) $balance_array->first_row();
    $amount = $balance['posttoexternal'] == 1 ? $balance['credit_limit'] - $balance['balance'] : $balance['balance'];
    $result_array['balance'] = $this->common_model->calculate_currency_customer($amount);

    $where_invoice = array(
        "is_paid" => "1",
        "accountid" => $account_data['id']
    );
    $query = $this->db_model->select("SUM(`debit`-`credit`) as invoice_amount", "view_invoices", $where_invoice, "", "", "", "");
    $invoice_amount = (array) $query->first_row();
    if (isset($invoice_amount['invoice_amount']) && $invoice_amount['invoice_amount'] != "") {
        $invoice_amount['invoice_amount'] = $invoice_amount['invoice_amount'];
    } else {
        $invoice_amount['invoice_amount'] = 0;
    }
    $result_array['invoice_amount'] = $this->common_model->calculate_currency_customer($invoice_amount['invoice_amount']);
    echo json_encode($result_array);
}

function user_get_provience()
{
    $country_id = $_POST['country_id'];
    $state_list = array();
    $state_list_array = array();
    $state_list = $this->db_model->getSelect("province", "dids", array(
        'country_id' => $country_id
    ));
    if ($state_list->num_rows() > 0) {
        $state_list_array = $state_list->result_array();
        foreach ($state_list_array as $key => $val) {
            foreach ($val as $key1 => $val1) {
                echo "<option value=" . $val1 . ">" . $val1 . "</option>";
            }
        }
    } else {
        echo '';
    }
    exit();
}

function user_get_city()
{
    $country = $_POST['country_id'];
    $provience = $_POST['provience'];
    $city_list = array();
    $city_list_array = array();
    $city_list = $this->db_model->getSelect("city", "dids", array(
        'province' => $provience,
        'country_id' => $country
    ));
    if ($city_list->num_rows() > 0) {
        $city_list_array = $state_list->result_array();
        foreach ($city_list_array as $key => $val) {
            foreach ($val as $key1 => $val1) {
                echo "<option value=" . $val1 . ">" . $val1 . "</option>";
            }
        }
    } else {
        echo '';
    }
    exit();
}

function user_get_order_list()
{
    $data['accountinfo'] = $this->session->userdata("accountinfo");
    $data['page_title'] = gettext('Orders');
    $data['search_flag'] = true;
    $this->session->set_userdata('advance_search', 0);
    $this->load->module("orders/orders");
    $data['grid_fields'] = $this->orders_form->build_orders_list_for_reseller();
    $data['form_search'] = $this->form->build_serach_form($this->orders_form->get_reseller_order_search_form());
    $this->load->view('view_reseller_order_list', $data);
}

function user_get_order_list_json()
{
    $json_data = array();
    $this->load->module("orders/orders");
    $count_all = $this->orders_model->get_reseller_orders_list(false);
    $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
    $json_data = $paging_data["json_paging"];
    $query = $this->orders_model->get_reseller_orders_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
    foreach ($query->result_array() as $key => $value) {
        $order_url = '<a href="' . base_url() . '/user/user_orders_complete/' . $value['order_id'] . '" style="color:#3b3280"  title="Edit" >' . $this->common->get_order_id("order_id", "orders", $value['id']) . '</a>';
        $json_data['rows'][] = array(
            'cell' => array(
                $value['order_date'],
                $order_url,
                $value['payment_gateway'],
                $value['setup_fee'],
                $value['price'],
                $value['payment_status']
            )
        );
    }
    echo json_encode($json_data);
}

function user_get_order_list_search()
{
    $ajax_search = $this->input->post('ajax_search', 0);
    if ($this->input->post('advance_search', TRUE) == 1) {
        $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
        $action = $this->input->post();
        unset($action['action'], $action['advance_search']);
        $this->session->set_userdata('reseller_orders_list_search', $action);
    }
    if ($ajax_search != 1) {
        redirect(base_url() . 'user/user_get_order_list/');
    }
}

function user_get_order_list_clearsearchfilter()
{
    $this->session->set_userdata('advance_search', 0);
    $this->session->set_userdata('reseller_orders_list_search', "");
}

    // Jaimin ASTPPCOM-984
function user_fssipdevices_routing($id=''){
    $this->load->module ( "siprouting" );
    $this->siprouting->fssipdevices_routing ( $id );
}

function user_fssipdevices_build_extension_dropdown(){
    $this->load->module ( "siprouting" );
    $this->siprouting->fssipdevices_build_extension_dropdown ();
}   

function user_fssipdevices_routing_save(){
    $this->load->module ( "siprouting" );
    $this->siprouting->fssipdevices_routing_save ();
}
    // End

}
?>
