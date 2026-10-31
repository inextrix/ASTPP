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
class Orders extends MX_Controller
{

    var $GetProductitems;

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library('orders_form');
        $this->load->library('astpp/form');
        $this->load->model('orders_model');
        $this->load->library('csvreader');
        $this->load->library('astpp/order');
        $this->load->library('form_validation');
        $this->load->model('Astpp_common');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
        $this->get_product_lists();
    }

    function get_product_lists()
    {
        $categoryinfo = $this->db_model->getSelect("GROUP_CONCAT(id) as id", "category", array(
            "code <> " => 'REFILL',
            "code <>" => 'DID'
        ));
        if ($categoryinfo->num_rows > 0) {
            $categoryinfo = $categoryinfo->result_array()[0]['id'];
            $accountinfo = $this->session->userdata("accountinfo");
            $where_arr['where'] = $this->db->where("reseller_id", 0);
            $where_arr['where'] = $this->db->where("product_category IN (" . $categoryinfo . ")", NULL, false);
            $this->GetProductitems = $this->db_model->build_dropdown("id,name", "products", "", $where_arr);
        }
    }

    function orders_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['accountinfo'] = $this->session->userdata("accountinfo");
        $data['page_title'] = gettext('Orders');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->orders_form->build_orders_list_for_admin();
        $data["grid_buttons"] = $this->orders_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->orders_form->get_order_search_form());
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $data['reseller_id'] = $reseller_id;
        $this->load->view('view_orders_list', $data);
    }

    function orders_list_json()
    {
        $json_data = array();
        $count_all = $this->orders_model->getorders_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->orders_model->getorders_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->orders_form->build_orders_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function orders_add()
    {
        $data['add_array'] = $this->input->post();
        $accountinfo = $this->session->userdata("accountinfo");
        $data['product_item_list'] = $this->GetProductitems;
        $data['page_title'] = gettext('Place Order');
        if ($accountinfo['type'] == 1) {
            $data['accounts_list'] = $this->db_model->build_concat_select_dropdown("id,first_name,number", "accounts", "", array(
                "reseller_id" => $accountinfo['id'],
                "type" => 0,
                "status" => 0,
                "deleted" => 0
            ), "");
        } else {
            $data['accounts_list'] = $this->db_model->build_concat_select_dropdown("id,first_name,number", "accounts", "", array(
                "reseller_id" => 0,
                "type" => 0,
                "status" => 0,
                "deleted" => 0
            ), "");
        }
        $data['currency'] = $this->common->get_field_name("currency", "currency", array(
            "id" => $accountinfo['currency_id']
        ));

        $category_id = $this->common->get_field_name("product_category", "products", array(
            "id" => $data['add_array']['product_id']
        ));

        $where = array(
            "id" => $category_id
        );
        $data['category_list'] = $this->common->get_field_name("name", "category", $where);

        if ($this->session->userdata('logintype') == '-1' || $this->session->userdata('logintype') == '2') {
            $product_data = $this->db_model->getSelect("*", "products", array(
                "id" => $data['add_array']['product_id']
            ));
        } else {
            $where_str = "(reseller_products.is_optin=0 OR reseller_products.is_owner=0)";
            $this->db->where($where_str);
            if ($accountinfo['reseller_id'] > 0) {
                $product_data = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,reseller_products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                    'products.status' => 0,
                    'products.id' => $data['add_array']['product_id'],
                    'reseller_products.account_id' => $accountinfo["id"],
                    'reseller_products.reseller_id' => $accountinfo["reseller_id"]
                ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', "", "", 'DESC', 'products.id');
            } else {
                if ($data['add_array']['reseller_id'] != '0') {
                    $product_data = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,reseller_products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                        'products.status' => 0,
                        'products.id' => $data['add_array']['product_id'],
                        'reseller_products.account_id' => $data['add_array']['reseller_id']
                    ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', "", "", 'DESC', 'products.id');
                } else {
                    $product_data = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,reseller_products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                        'products.status' => 0,
                        'products.id' => $data['add_array']['product_id'],
                        'reseller_products.account_id' => $accountinfo['id']
                    ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', "", "", 'DESC', 'products.id');
                }
            }
        }
        $data['accountinfo'] = $accountinfo;
        if (isset($data['category_list']) && $data['category_list'] != '') {
            if ($product_data->num_rows > 0) {
                $data['product_info'] = $product_data->result_array();
                $data['product_info'] = $data['product_info'][0];
            }
            $this->load->view('view_orders_assign', $data);
        } else {

            $this->load->view('view_orders_assign', $data);
        }
    }

    function orders_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $order_item_where = "order_id IN ($ids)";
        $this->db->where($order_item_where);
        $this->db->delete("order_items");
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("orders");
    }

    function orders_save()
    {
        $ProductData = $this->input->post();
        // Kinjal ASTPPCOM-929 Start
        $ProductData_list = $this->db_model->getSelect("*", "products", array(
            "id" => $ProductData['product_id']
        ))->result_array()[0];
        if($ProductData_list != ""){
            $ProductData = array_merge($ProductData,$ProductData_list);
        }
        // Kinjal ASTPPCOM-929 END
        $account_id = $this->input->post('accountid');
        $accountinfo = $this->session->userdata("accountinfo");
        $data['page_title'] = gettext('Place Order');
        $data['currency'] = $this->common->get_field_name("currency", "currency", array(
            "id" => $accountinfo['currency_id']
        ));
        if ($accountinfo['type'] == 1) {
            $data['accounts_list'] = $this->db_model->build_concat_select_dropdown("id,first_name,number", "accounts", "", array(
                "reseller_id" => $accountinfo['id'],
                "type" => 0,
                "status" => 0,
                "deleted" => 0
            ), "");
        } else {
            $data['accounts_list'] = $this->db_model->build_concat_select_dropdown("id,first_name,number", "accounts", "", array(
                "reseller_id" => 0,
                "type" => 0,
                "status" => 0,
                "deleted" => 0
            ), "");
        }
        if ($account_id == 0) {
            $this->form_validation->set_rules('accountid', 'Accounts', 'required|dropdown_required|xss_clean');
        }
        $this->form_validation->set_rules('price', 'Price', 'numeric|greater_than[-1]|min_length[1]|max_length[10]|xss_clean');
        $this->form_validation->set_rules('setup_fee', 'Setup Fee', 'numeric|greater_than[-1]|min_length[1]|max_length[10]|xss_clean');
        $this->form_validation->set_message('max_length', '%s field can not excced  numbers in length %s');
        if ($this->form_validation->run() == FALSE) {
            $data['product_item_list'] = $this->GetProductitems;
            $data['accountinfo'] = $accountinfo;
            $data['add_array'] = $ProductData;
            $data['validation_errors'] = validation_errors();
            $this->load->view('view_orders_assign', $data);
        } else {
            if (! empty($ProductData) && isset($ProductData)) {
                $customer_data = array();
                $customer_data = $this->db_model->getSelect("*", "accounts", array(
                    "id" => $ProductData['accountid'],
                    "status" => 0,
                    "deleted" => 0,
                    "type" => 0
                ));
                if ($customer_data->num_rows > 0) {
                    $customer_data = $customer_data->result_array()[0];
                }
                // $ProductData holds the admin's product row merged over the POST data; the balance
                // check uses what confirm_order() books for this customer (seller's offer)
                $quantity = order::quantity(isset($ProductData['quantity']) ? $ProductData['quantity'] : '');
                $unit_price = $this->order->unit_price($ProductData['product_id'], $account_id);
                if ($quantity === false || $unit_price === false) {
                    $this->session->set_flashdata('astpp_notification', gettext('Invalid price, quantity or product!'));
                    redirect(base_url() . 'orders/orders_add/');
                }
                $total_amt = $unit_price * $quantity;
                $account_balance = $customer_data['posttoexternal'] == 1 ? $customer_data['credit_limit'] - ($customer_data['balance']) : $customer_data['balance'];
                if ($account_balance >= $total_amt) {
                    $ProductData['invoice_type'] = ($ProductData['category'] == 3) ? "credit" : "debit";
                    $ProductData['next_billing_date'] = ($ProductData['billing_days'] == 0) ? gmdate('Y-m-d 23:59:59', strtotime('+10 years')) : gmdate("Y-m-d 23:59:59", strtotime("+" . ($ProductData['billing_days'] - 1) . " days"));
                    $last_id = $this->order->confirm_order($ProductData, $account_id, $accountinfo);
                    if (! empty($customer_data) && $last_id != '' && $ProductData['email_notify'] == 1) {
                        $ProductData['payment_by'] = ($ProductData['payment_by'] == 0) ? "Account Balance" : "Account Balance";
                        $ProductData['category'] = $this->common->get_field_name("name", "category", array(
                            "id" => $ProductData['category']
                        ));
                        $final_array = array_merge($customer_data, $ProductData);
                       $final_array['quantity'] = (isset($ProductData['quantity']) && $ProductData['quantity'] > 1)?$ProductData['quantity']:1;
                        $final_array['price'] = ($ProductData['setup_fee'] + $ProductData['price']);
                        $final_array['total_price'] = ($ProductData['setup_fee'] + $ProductData['price']) * (isset($ProductData['quantity']) ? $ProductData['quantity'] : 1);
                        $final_array['total_price_amount'] = ($ProductData['setup_fee'] + $ProductData['price']);
                        $final_array['category_name'] = $ProductData['category'];
			$final_array['name'] = $ProductData['product_name'];
                        $this->common->mail_to_users('product_purchase', $final_array);
                    }
                    $this->session->set_flashdata('astpp_errormsg', gettext("Product assign successfully"));
                    redirect(base_url() . 'orders/orders_complete/' . $last_id . '');
                } else {
                    $this->session->set_flashdata('astpp_notification', gettext('Insufficient balance to assign product!'));
                    redirect(base_url() . 'orders/orders_add/');
                }
            } else {
                redirect(base_url() . 'orders/orders_list/');
            }
        }
    }

    public function orders_complete($orderid)
    {
        $this->db->select('order_id');
        $commission_orderid = (array) $this->db->get_where("commission", array(
            "id" => $orderid
        ))->first_row();
        if (! empty($commission_orderid)) {
            $this->db->select('order_id');
            $order_id = (array) $this->db->get_where("orders", array(
                "id" => $commission_orderid['order_id']
            ))->first_row();
            if (! empty($order_id['order_id']) && $order_id['order_id'] > 0) {
                $orderid = $order_id['order_id'];
            }
        }
        $accountinfo = $this->session->userdata("accountinfo");
        $data['back'] = true;
        if ($accountinfo['type'] == 0 || $accountinfo['type'] == 3) {
            $data['back_url'] = "user/user_products_list/";
        } else {
            $data['back_url'] = "orders/orders_list/";
        }
        if ($orderid != '') {
            $this->db->where("orders.id", $orderid);
            $this->db->or_where("orders.order_id", $orderid);
            $query = $this->db_model->getJionQuery('orders', '*,orders.order_id as orderid', '', 'order_items', 'orders.id=order_items.order_id', 'inner', '', '', '', '');
            if ($query->num_rows > 0) {
                $data['order_items'] = $query->result_array()[0];
                if ($this->session->userdata('logintype') == '1' || $this->session->userdata('logintype') == '5') {
                    $where_str = "(reseller_products.is_optin=0 OR reseller_products.is_owner=0)";
                    $this->db->where($where_str);
                    if ($accountinfo['reseller_id'] > 0) {
                        $product_data = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,reseller_products.status,reseller_products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                            'products.status' => 0,
                            'products.id' => $data['order_items']['product_id'],
                            'reseller_products.account_id' => $accountinfo["id"],
                            'reseller_products.reseller_id' => $accountinfo["reseller_id"]
                        ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', "", "", 'DESC', 'products.id');
                    } else {
                        $product_data = $this->db_model->getJionQuery('products', 'products.id,products.name,products.product_category,reseller_products.status,reseller_products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                            'products.status' => 0,
                            'products.id' => $data['order_items']['product_id']
                        ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', "", "", 'DESC', 'products.id');
                    }
                } else {
                    $product_data = $this->db_model->getSelect("*", "products", array(
                        "id" => $data['order_items']['product_id']
                    ));
                }

                $invoice_id = $this->common->get_field_name("invoiceid", "invoice_details", array(
                    "order_item_id" => $data['order_items']['order_id']
                ));
                if ($invoice_id > 0) {
                    $data['invoice_data'] = array();
                    $invoice_data = $this->db_model->getSelect("*", "invoices", array(
                        "id" => $invoice_id
                    ));
                    if ($invoice_data->num_rows > 0) {
                        $data['invoice_data'] = $invoice_data->result_array()[0];
                    }
                }
                if ($product_data->num_rows > 0) {
                    $data['product_info'] = $product_data->result_array();
                    $data['product_info'] = $data['product_info'][0];
                }
                $account_data = $this->db_model->getSelect("*", "accounts", array(
                    "id" => $data['order_items']['accountid'],
                    "status" => "0",
                    "deleted" => "0"
                ));
                if ($account_data->num_rows > 0) {
                    $account_info = $account_data->result_array();
                    $data['account_info'] = $account_info[0];
                }
                $data['currency'] = $this->common->get_field_name("currency", "currency", array(
                    "id" => $accountinfo['currency_id']
                ));
                $this->load->view('complete_order', $data);
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Order Not found!'));
                redirect(base_url() . 'orders/orders_list/');
            }
        }
    }

    function orders_reseller_accounts_dependency_dropdown()
    {
        if (! empty($this->input->post())) {
            $reseller_id = $this->input->post('reseller_id');
            $accountinfo = $this->session->userdata("accountinfo");
            if ($this->session->userdata('logintype') == 1) {
                if ($reseller_id == '' || $reseller_id == 0) {
                    $data['accounts_list'] = $this->db_model->build_concat_select_dropdown("id,first_name,number", "accounts", "", array(
                        "reseller_id" => $accountinfo['id'],
                        "type" => 0,
                        "status" => 0,
                        "deleted" => 0
                    ), "");
                } else {
                    $data['accounts_list'] = $this->db_model->build_concat_select_dropdown("id,first_name,number", "accounts", "", array(
                        "reseller_id" => $reseller_id,
                        "type" => 0,
                        "status" => 0,
                        "deleted" => 0
                    ), "");
                }
            } else {
                $data['accounts_list'] = $this->db_model->build_concat_select_dropdown("id,first_name,number", "accounts", "", array(
                    "reseller_id" => $reseller_id,
                    "type" => 0,
                    "status" => 0,
                    "deleted" => 0
                ), "");
            }
            $account_add = array(
                "id" => "accountid",
                "name" => "accountid",
                "class" => "accountid"
            );
            echo form_dropdown($account_add, $data['accounts_list'], $this->input->post('accountid'));
        }
    }

    function orders_get_availbale_product_lists()
    {
        $add_array = $this->input->post();
        $accountinfo = $this->session->userdata("accountinfo");
        $product_info = $this->db_model->getSelect("GROUP_CONCAT(product_id) as product_id", "order_items", array(
            "product_category" => $this->common->get_field_name("id", "category", array(
                "code" => "DID"
            )),
            "accountid" => $add_array['accountid'],
            "reseller_id" => $add_array['reseller_id']
        ));
        if ($product_info->num_rows > 0) {
            $product_info = $product_info->result_array()[0]['product_id'];
            if ($product_info) {
                $where_arr['where'] = $this->db->where("id  NOT IN (" . $product_info . ")", NULL, false);
            }
            if ($this->session->userdata('logintype') == 1) {
                $where_str = "(reseller_products.is_optin=0 OR reseller_products.is_owner=0)";
                $this->db->where($where_str);
                if ($accountinfo['reseller_id'] > 0) {
                    if ($add_array['reseller_id'] != 0 && $add_array['accountid'] != 0) {
                        $product_item_list = $this->db_model->getJionQuery('products', ' products.id,products.name,products.product_category,products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                            'reseller_products.account_id' => $add_array['reseller_id'],
                            'reseller_products.reseller_id' => $accountinfo['id'],
                            'reseller_products.status' => 0,
                            'products.is_deleted' => 0,
                            'products.product_category' => $add_array['category_id'],
                            'reseller_products.is_optin' => 0
                        ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', 'DESC', 'products.id');
                    } else {
                        $product_item_list = $this->db_model->getJionQuery('products', ' products.id,products.name,products.product_category,products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                            'reseller_products.account_id' => $accountinfo['id'],
                            'reseller_products.reseller_id' => $accountinfo['reseller_id'],
                            'reseller_products.status' => 0,
                            'products.product_category' => $add_array['category_id'],
                            'products.is_deleted' => 0
                        ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', 'DESC', 'products.id');
                    }
                } else {
                    if ($add_array['reseller_id'] != 0 && $add_array['accountid'] != 0) {
                        $product_item_list = $this->db_model->getJionQuery('products', ' products.id,products.name,products.product_category,products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                            'reseller_products.account_id' => $add_array['reseller_id'],
                            'reseller_products.reseller_id' => $accountinfo['id'],
                            'reseller_products.status' => 0,
                            'products.product_category' => $add_array['category_id'],
                            'reseller_products.is_optin' => 0,
                            'products.is_deleted' => 0
                        ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', 'DESC', 'products.id');
                    } else {
                        $product_item_list = $this->db_model->getJionQuery('products', ' products.id,products.name,products.product_category,reseller_products.buy_cost,reseller_products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                            'reseller_products.reseller_id' => $accountinfo['reseller_id'],
                            'reseller_products.account_id' => $accountinfo['id'],
                            'reseller_products.status' => 0,
                            'products.product_category' => $add_array['category_id'],
                            'products.is_deleted' => 0
                        ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', 'DESC', 'products.id');
                    }
                }
                $product_list = $product_item_list->result_array();
                $product_item_list = array();
                foreach ($product_list as $value) {
                    $product_item_list[$value['id']] = $value['name'];
                }
                $data['product_item_list'] = $product_item_list;
                $product_item = array(
                    "id" => "product_id",
                    "name" => "product_id",
                    "class" => "product_id"
                );
                $data['product_list'] = form_dropdown_all($product_item, $data['product_item_list'], $add_array['productid'], '');
            } else {
                if ($add_array['reseller_id'] != 0 && $add_array['accountid'] != 0) {
                    $product_item_list = $this->db_model->getJionQuery('products', ' products.id,products.name,products.product_category,products.buy_cost,products.commission,reseller_products.price,reseller_products.setup_fee,reseller_products.billing_type,reseller_products.billing_days,reseller_products.free_minutes,products.status,products.last_modified_date,reseller_products.product_id', array(
                        'reseller_products.account_id' => $add_array['reseller_id'],
                        'reseller_products.status' => 0,
                        'products.product_category' => $add_array['category_id'],
                        'reseller_products.is_optin' => 0,
                        'products.is_deleted' => 0
                    ), 'reseller_products', 'products.id=reseller_products.product_id', 'inner', '', '', 'DESC', 'products.id');
                    $product_list = $product_item_list->result_array();
                    $product_item_list = array();
                    foreach ($product_list as $value) {
                        $product_item_list[$value['id']] = $value['name'];
                    }
                    $data['product_item_list'] = $product_item_list;
                } else {
                    $reseller_id = $add_array['reseller_id'] ? $add_array['reseller_id'] : 0;
                    $where_arr['where'] = $this->db->where(array(
                        "product_category" => $add_array['category_id']
                    ));
                    $where_arr['where'] = $this->db->where(array(
                        "status" => 0
                    ));
                    $where_arr['where'] = $this->db->where(array(
                        "is_deleted" => 0
                    ));
                    $where_arr['where'] = $this->db->where(array(
                        "reseller_id" => $reseller_id
                    ));
                    $data['product_item_list'] = $this->db_model->build_dropdown("id,name", "products", "", $where_arr);
                }
            }
            $product_item = array(
                "id" => "product_id",
                "name" => "product_id",
                "class" => "product_id"
            );
            $data['product_list'] = form_dropdown_all($product_item, $data['product_item_list'], $add_array['productid'], '');
        }
        echo $data['product_list'];
        exit();
    }

    function orders_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            if(!empty($action['order_date'][0])){
                $action['order_date'][0]=$this->common->convert_GMT_new ( $action['order_date'][0]);
            }
            if(!empty($action['order_date'][1])){
                $action['order_date'][1]=$this->common->convert_GMT_new ( $action['order_date'][1]);
            }
            unset($action['action']);
            unset($action['advance_search']);
            if (isset($action['order_id']['order_id']) && $action['order_id']['order_id'] != '') {
                $action['order_id']['order_id'] = str_replace('#', "", $action['order_id']['order_id']);
            }
            $this->session->set_userdata('orders_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'accounts/customer_list/');
        }
    }

    function orders_terminate($orderid)
    {
        $data['orderid'] = $orderid;

        $ord_id = $this->common->get_field_name("id", "orders", array(
            "order_id" => $orderid
        ));

        $data['next_billing_date'] = $this->common->get_field_name("next_billing_date", "order_items", array(
            "order_id" => $ord_id
        ));
        $this->load->view("view_treminate_order", $data);
    }

    function orders_users_terminate()
    {
        $update_array = $this->input->post();
        $this->form_validation->set_rules('creation', 'Date', 'required|xss_clean');
        if ($this->form_validation->run() == FALSE) {

            $ord_id = $this->common->get_field_name("id", "orders", array(
                "order_id" => $update_array['order_id']
            ));
            $data['next_billing_date'] = $this->common->get_field_name("next_billing_date", "order_items", array(
                "order_id" => $ord_id
            ));
            $data['orderid'] = $update_array['order_id'];
            $data['validation_errors'] = validation_errors();
            echo $data['validation_errors'];
            exit();
        } else {
            if (! empty($update_array)) {
                // The date picker allows today up to the end of the paid period only; any date was
                // taken (also as gmdate() format string), so a later date kept the package active
                // (packages_view) after billing had stopped (is_terminated = 1).
                $order_id = $this->common->get_field_name("id", "orders", array(
                    "order_id" => $update_array['order_id']
                ));
                $next_billing = (string) $this->common->get_field_name("next_billing_date", "order_items", array(
                    "order_id" => $order_id
                ));
                $date = trim((string) $update_array['creation']);
                $parsed = DateTime::createFromFormat('!Y-m-d', $date, new DateTimeZone('UTC'));
                if (! $parsed || $parsed->format('Y-m-d') !== $date || $date < gmdate('Y-m-d') || ($next_billing !== '' && $date > substr($next_billing, 0, 10))) {
                    echo json_encode(array(
                        'creation_error' => gettext('Please select a date between today and the next billing date.')
                    ));
                    exit();
                }
                if ($update_array['creation'] != '') {
                    $order_update_array = array(
                        "is_terminated" => 1,
                        "termination_date" => gmdate("" . $update_array['creation'] . " H:i:s"),
                        "termination_note" => $update_array['note']
                    );
                    $this->db->where("order_id", $order_id);
                    $this->db->update("order_items", $order_update_array);
                    $accountinfo = $this->session->userdata("accountinfo");
                    $data = array();
                    $data = $this->db_model->getSelect("accountid,product_id,product_category", "order_items", array(
                        "order_id" => $order_id
                    ));
                    if (! empty($data)) {
                        $data = $data->result_array()[0];

                        $user_info = $this->db_model->getSelect("*", "accounts", array(
                            "id" => $data['accountid']
                        ));
                        $product_name = $this->common->get_field_name("name", "products", array(
                            "id" => $data['product_id']
                        ));
                        if ($data['product_category'] == 4) {
                            $this->load->module('did/did');
                            $accountinfo = $this->session->userdata('accountinfo');
                            $did_where = array(
                                'product_id' => $data['product_id']
                            );
                            $did_info = (array) $this->db->get_where("dids", $did_where)->result_array()[0];
                            $this->did_model->did_number_release($did_info, $accountinfo, 'release');
                        } else {
                            $user_info = (array) $user_info->first_row();
                            $user_info['name'] = $product_name;
                            $user_info['order_id'] = $update_array['order_id'];
                            $ord_id = '';
                            $termination_date = '';
                            $accountid = '';
                            $ord_id = $this->common->get_field_name("id", "orders", array(
                                "order_id" => $update_array['order_id']
                            ));
                          /*  $next_billing_date = $this->common->get_field_name("next_billing_date", "order_items", array(
                                "order_id" => $ord_id
                            ));*/


			     $termination_date = $order_update_array['termination_date'];
                            $accountid = $this->common->get_field_name("accountid", "orders", array(
                                "order_id" => $update_array['order_id']
                            ));
                            $user_info['number'] = $this->common->get_field_name("number", "accounts", array(
                                "id" => $accountid
                            ));
                            $user_info['next_billing_date'] = $termination_date;
                            $final_array = array_merge($user_info, $user_info);
                            $this->common->mail_to_users("product_release", $final_array);
                        }
                    }
                }
            }
            echo json_encode(array(
                "SUCCESS_ORDER" => gettext("Terminated Updated Successfully!")
            ));
            exit();
        }
    }

    function orders_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('orders_list_search', "");
    }
}

?>
