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
class Orders_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function getorders_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('orders_list_search', 'orders.');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $where_arr = array(
                "orders.reseller_id" => $account_data['id']
            );
        } else {
            $where_arr = array();
        }
        if (isset($_GET['sortname']) && $_GET['sortname'] != 'undefined') {
            $this->db->order_by($_GET['sortname'], ($_GET['sortorder'] == 'undefined') ? 'desc' : $_GET['sortorder']);
        } else {
            $where = $this->db->order_by("order_date", "desc");
        }
        if ($flag) {
            $query = $this->db_model->getJionQuery('orders', 'orders.id,orders.order_id ,orders.order_date,orders.accountid,orders.payment_gateway,orders.payment_status,orders.reseller_id,orders.accountid,order_items.setup_fee,order_items.price', $where_arr, 'order_items', 'orders.id=order_items.order_id', 'inner', $limit, $start, '', '');
        } else {
            $query = $this->db_model->getJionQueryCount('orders', 'orders.id,orders.order_id,orders.order_date,orders.accountid,orders.payment_gateway,orders.payment_status,orders.reseller_id,orders.accountid,order_items.setup_fee,order_items.price', $where_arr, 'order_items', 'orders.id=order_items.order_id', 'inner', "", "", '', '');
        }

        return $query;
    }

    function remove_order($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("orders");
        return true;
    }

    function get_reseller_orders_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('reseller_orders_list_search', 'orders.');
        // "My Orders" - only administrators see all orders (customers and providers
        // got the orders of all accounts)
        if ($this->session->userdata('logintype') == 2 || $this->session->userdata('logintype') == 4) {
            $where_arr = array();
        } else {
            $account_data = $this->session->userdata("accountinfo");
            $where_arr = array(
                "orders.accountid" => $account_data['id']
            );
        }
        if (isset($_GET['sortname']) && $_GET['sortname'] != 'undefined') {
            $this->db->order_by($_GET['sortname'], ($_GET['sortorder'] == 'undefined') ? 'desc' : $_GET['sortorder']);
        } else {
            $where = $this->db->order_by("order_date", "desc");
        }
        if ($flag) {
            $query = $this->db_model->getJionQuery('orders', 'orders.id,orders.order_id ,orders.order_date,orders.accountid,orders.payment_gateway,orders.payment_status,orders.reseller_id,orders.accountid,order_items.setup_fee,order_items.price', $where_arr, 'order_items', 'orders.id=order_items.order_id', 'inner', $limit, $start, '', '');
        } else {
            $query = $this->db_model->getJionQueryCount('orders', 'orders.id,orders.order_id,orders.order_date,orders.accountid,orders.payment_gateway,orders.payment_status,orders.reseller_id,orders.accountid,order_items.setup_fee,order_items.price', $where_arr, 'order_items', 'orders.id=order_items.order_id', 'inner', "", "", '', '');
        }
        return $query;
    }
}
?>
