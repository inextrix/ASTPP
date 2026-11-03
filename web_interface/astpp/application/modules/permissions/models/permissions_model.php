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
class permissions_model extends CI_Model
{

    function __construct() // PHP 4 style constructor (permissions_model()) is no constructor since PHP 8
    {
        parent::__construct();
    }

    function getpermissions_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('permissions_list_search');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $reseller = $account_data['id'];
            $where = array(
                "reseller_id" => $reseller
            );
        } else {
            $where = array(
                "reseller_id" => "0"
            );
        }
        if ($flag) {
            $query = $this->db_model->select("*", "permissions", $where, "id", "DESC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "permissions", $where);
        }
        return $query;
    }

    function add_permissions($add_array)
    {
        $permission_array = array();
        unset($add_array["save_button"]);
        $permission_array = $add_array['permission'];
        $permission_encode = json_encode($permission_array);
        $insert_array = array();
        $insert_array['permissions'] = $permission_encode;
        $edit_array = $add_array['edit'];
        $edit_encode = json_encode($edit_array);
        $insert_array['name'] = $add_array['name'];
        $insert_array['description'] = $add_array['description'];
        $insert_array['login_type'] = $add_array['login_type'];
        $insert_array['creation_date'] = gmdate('Y-m-d H:i:s');
        $insert_array['modification_date'] = '0000-00-00 00:00:00';
        $accountinfo = $this->session->userdata("accountinfo");
        $insert_array['reseller_id'] = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $this->db->insert("permissions", $insert_array);
        return true;
    }

    function edit_permissions($add_array, $id)
    {
        $permission_array = array();
        unset($add_array["save_button"]);
        $permission_array = $add_array['permission'];
        $permission_encode = json_encode($permission_array);
        $update_array = array();
        $update_array['permissions'] = $permission_encode;
        $edit_array = $add_array['edit'];
        $edit_encode = json_encode($edit_array);
        $update_array['name'] = $add_array['name'];
        $update_array['description'] = $add_array['description'];
        $update_array['modification_date'] = gmdate('Y-m-d H:i:s');
        $this->db->where("id", $id);
        $this->db->update("permissions", $update_array);
    }

    function remove_permissions($id)
    {
        $this->db->delete("permissions", array(
            "id" => $id
        ));
        return true;
    }
}
