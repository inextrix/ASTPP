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
class Taxes_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_taxes_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('taxes_list_search');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $where = array(
                "reseller_id" => $this->session->userdata["accountinfo"]['id']
            );
        } else {
            $where = array();
        }
        if ($flag) {
            $query = $this->db_model->select("*", "taxes", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "taxes", $where);
        }
        return $query;
    }

    function add_tax($data)
    {
        unset($data["action"]);
        $data["creation_date"] = date("Y-m-d H:i:s");
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $data['reseller_id'] = $this->session->userdata["accountinfo"]['id'];
        }
        $this->db->insert("taxes", $data);
        if ($data['tax_type'] == '0') {
            $last_id = $this->db->insert_id();

            $query = "update `system` set value = if(value=' ',$last_id,concat(value,',',$last_id)) WHERE name = 'tax_type'";
            $this->db->query($query);
        }
    }

    function edit_tax($data, $id)
    {
        unset($data["action"]);
        $data["last_modified_date"] = date("Y-m-d H:i:s");
        $this->db->where("id", $id);
        $this->db->update("taxes", $data);
    }

    function remove_taxes($id)
    {
        $this->db->delete('taxes_to_accounts', array(
            'taxes_id' => $id
        ));
        // "Update system set value=REPLACE(value,'<id>','')" ran without WHERE on every setting
        // (IP addresses, ports, ...) and put the unescaped id into SQL. Remove the id from the
        // tax_type lists only.
        $tax_rows = $this->db->get_where('system', array(
            'name' => 'tax_type'
        ))->result_array();
        foreach ($tax_rows as $tax_row) {
            $remaining = array();
            foreach (explode(",", $tax_row['value']) as $value) {
                $value = trim($value);
                if ($value !== '' && $value !== (string) $id) {
                    $remaining[] = $value;
                }
            }
            $this->db->where('id', $tax_row['id']);
            $this->db->update('system', array(
                'value' => implode(',', $remaining)
            ));
        }
        return $this->db->delete("taxes", array(
            'id' => $id
        ));
    }
}
