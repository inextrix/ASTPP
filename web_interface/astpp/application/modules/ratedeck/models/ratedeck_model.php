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
class Ratedeck_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_ratedeck_list($reseller_id, $flag, $start = 0, $limit = 0, $export = true)
    {
        $this->db_model->build_search('ratedeck_list_search');
        $where = '';
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $where = array(
                "reseller_id" => $reseller_id
            );
        }
        if ($flag) {
            $query = $export == true ? $this->db_model->select("*", "ratedeck", $where, "id", "ASC", $limit, $start) : $this->db_model->select("*", "ratedeck", $where, "id", "ASC");
        } else {
            $query = $this->db_model->countQuery("*", "ratedeck", $where);
        }
        return $query;
    }

    function add_ratedeck($data)
    {
        unset($data["action"]);
        $data["creation_date"] = date("Y-m-d H:i:s");
        $data['pattern'] = "^" . $data['pattern'] . ".*";
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $data['reseller_id'] = $this->session->userdata["accountinfo"]['id'];
        } else {
            $data['reseller_id'] = 0;
        }
        $this->db->insert("ratedeck", $data);
    }

    function edit_ratedeck($data, $id)
    {
        $data['pattern'] = "^" . $data['pattern'] . ".*";
        unset($data["action"]);
        $data["last_modified_date"] = date("Y-m-d H:i:s");
        $this->db->where("id", $id);
        $this->db->update("ratedeck", $data);
    }

    function remove_ratedeck($id)
    {
        return $this->db->delete("ratedeck", array(
            'id' => $id
        ));
    }

    function bulk_insert_ratedeck($new_final_arr)
    {
        $this->db->insert_batch('ratedeck', $new_final_arr);
        $affected_row = $this->db->affected_rows();
        return $affected_row;
    }

    function check_unique_ratedeck($where)
    {
        $query = $this->db_model->countQuery("*", "ratedeck", $where);
        return $query;
    }

    function check_unique_ratedeck_for_edit($where)
    {
        $this->db->where($where);
        $this->db->select("*");
        $this->db->from('ratedeck');
        $query = $this->db->get();
        return $query;
    }
}
