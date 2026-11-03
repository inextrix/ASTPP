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
class Accessnumber_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function getaccessnumber_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('accessnumber_list_search');
        $accountdata = $this->session->userdata('accountinfo');

        if ($accountdata['type'] == '-1' || $accountdata['type'] == '2') {
            $where = array();
        } else {
            $where = array(
                "status" => "0"
            );
        }
        if ($flag) {
            $query = $this->db_model->select("*", "accessnumber", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "accessnumber", $where);
        }
        return $query;
    }

    function add_accessnumber($add_array)
    {
        unset($add_array["action"]);
        $add_array['creation_date'] = gmdate('Y-m-d H:i:s');
        $add_array['last_modified_date'] = gmdate('Y-m-d H:i:s');
        $this->db->insert("accessnumber", $add_array);
        return true;
    }

    function edit_accessnumber($data, $id)
    {
        unset($data["action"]);
        $data['last_modified_date'] = gmdate('Y-m-d H:i:s');
        $this->db->where("id", $id);
        $this->db->update("accessnumber", $data);
    }

    function remove_accessnumber($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('accessnumber');
        return true;
    }

    function bulk_insert_accessnumber($field_value)
    {
        $this->db->insert_batch('accessnumber', $field_value);
        $affected_row = $this->db->affected_rows();
        return $affected_row;
    }
}
