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
class Signup_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Astpp_common');
        $this->load->helper('form');
        $this->load->model('common_model');
        $this->load->library('session');
    }

    function get_rate()
    {
        $data = array();
        $this->load->database();
        $this->db->select("id,name");
        $this->db->from('pricelists');
        $this->db->where("status", "0");
        $query = $this->db->get();
        return $query->row();
    }

    function add_user($data)
    {
        $data['reseller_id'] = $data['key_unique'];
        unset($data['agreeCheck']);
        unset($data['key_unique']);
        $data['creation'] = gmdate('Y-m-d H:i:s');
        $data['expiry'] = astpp_date('Y-m-d H:i:s', strtotime('+10 years'));
        $data['type'] = 0;
        $this->db->insert("accounts", $data);
        $last_id = $this->db->insert_id();
        $tax = common_model::$global_config['system_config']['tax_type'];
        if (! empty($tax)) {
            $query = "select id as taxes_id,taxes_priority from taxes where id IN($tax)";
            $result = $this->db->query($query);
            if ($result->num_rows() > 0) {
                $tax_array = array();
                $taxes_value = $result->result_array();
                $i = 0;
                foreach ($taxes_value as $value) {
                    $tax_array[$i]['id'] = '';
                    $tax_array[$i]['taxes_id'] = $value['taxes_id'];
                    $tax_array[$i]['taxes_priority'] = $value['taxes_priority'];
                    $tax_array[$i]['accountid'] = $last_id;
                    $i ++;
                }
                $this->db->insert_batch("taxes_to_accounts", $tax_array);
            }
        }

        return $last_id;
    }
}

function check_user($accno, $email, $balance)
{
    $info = array(
        "number" => $accno,
        "email" => $email,
        "status" => 1
    );
    $this->db->where($info);
    $this->db->select('*');
    $acc_res = $this->db->get('accounts');
    if ($acc_res->num_rows() > 0) {
        $acc_res = $acc_res->result_array();
        $acc_res = $acc_res[0];
        $this->db->where('pricelist_id', $acc_res['pricelist_id']);
        $this->db->select("*");
        $charge_res = $this->db->get('charges');

        if ($charge_res->num_rows() > 0) {
            $charge_res = $charge_res->result_array();
            $charge_res = $charge_res[0];
            $charge_acc_arr = array(
                "charge_id" => $charge_res['id'],
                "accountid" => $acc_res['id'],
                "status" => 0,
                "assign_date" => date('Y-m-d H:i:s')
            );
        } else {
            $charge_res = $charge_res->result_array();
            $charge_acc_arr = array(
                "charge_id" => 'id',
                "accountid" => $acc_res['id'],
                "assign_date" => date('Y-m-d H:i:s')
            );
        }
        $result = $this->db->insert("charge_to_account", $charge_acc_arr);
        $update = array(
            "status" => 0,
            "balance" => $balance
        );
        $this->db->where($info);
        $result = $this->db->update('accounts', $update);
        $sip_device_update = array(
            'accountid' => $acc_res['id']
        );
        $update_sip = array(
            "status" => 0
        );
        $this->db->where($sip_device_update);
        $result = $this->db->update('sip_devices', $update_sip);
        return 1;
    } else {
        return 0;
    }
}

?>
