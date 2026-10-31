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
class Refill_coupon_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_refill_coupon_list($flag, $start = 0, $limit = 0, $export = false)
    {
        $this->db_model->build_search('refill_coupon_list_search');
        $accountinfo = $this->session->userdata('accountinfo');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
            $where = array(
                'reseller_id' => $reseller_id
            );
        } else {
            $where = array();
        }
        if ($flag) {
            if ($export)
                $query = $this->db_model->select("*", "refill_coupon", $where, "id", "ASC", '', '');
            else
                $query = $this->db_model->select("*", "refill_coupon", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "refill_coupon", $where);
        }
        return $query;
    }

    function get_customer_refill_coupon_list($flag, $start = 0, $limit = 0, $accountid)
    {
        $this->db_model->build_search('refill_coupon_list_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : ($accountinfo['type'] == 0 ? $accountinfo['reseller_id'] : 0);
        $where = array(
            'reseller_id' => $reseller_id,
            "account_id" => $accountid
        );
        if ($flag) {
            $query = $this->db_model->select("*", "refill_coupon", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "refill_coupon", $where);
        }
        return $query;
    }

    function add_refill_coupon($add_array)
    {
        $count = (int) $add_array['count'];
        $prefix = $add_array['prefix'];
        // only the form fields (the whole POST array was inserted: account_id, firstused, status
        // ... of the new coupons could be set)
        $add_array = array(
            'amount' => $add_array['amount'],
            'description' => isset($add_array['description']) ? $add_array['description'] : '',
            'status' => 0
        );
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $insert_arr = array();
        $account_length = Common_model::$global_config['system_config']['refill_coupon_length'];
        $length = strlen($prefix);
        if ($length != 0) {
            $number_length = $account_length - $length;
        } else {
            $number_length = $account_length;
        }
        $add_array['amount'] = $this->common_model->add_calculate_currency($add_array['amount'], '', '', false, false);
        $number = $this->common->find_uniq_rendno_accno($number_length, 'number', 'refill_coupon', $prefix, $count);
        $date = gmdate('Y-m-d H:i:s');
        for ($i = 0; $i < $count; $i ++) {
            $add_array['number'] = trim($number[$i]);
            $add_array['currency_id'] = $accountinfo['currency_id'];
            $add_array['reseller_id'] = $reseller_id;
            $add_array['creation_date'] = $date;
            $insert_arr[$i] = $add_array;
        }
        $this->db->insert_batch("refill_coupon", $insert_arr);
        return true;
    }

    function remove_refill_coupon($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("refill_coupon");
        return true;
    }

    function get_refill_coupon_details($id)
    {
        $this->db->where("id", $id);
        $result = $this->db->get('refill_coupon');
        return $result;
    }

    function refill_coupon_count($add_array)
    {
        $account_length = Common_model::$global_config['system_config']['refill_coupon_length'];
        $this->db->where("length(number)", $account_length);
        $this->db->like('number', $add_array['prefix'], 'after');
        $this->db->select("count(id) as count");
        $this->db->from('refill_coupon');
        $result = $this->db->get();
        $result = $result->result_array();
        return $result;
    }
}
