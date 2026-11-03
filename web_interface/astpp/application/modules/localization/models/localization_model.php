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
class Localization_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_localization_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('localization_list_search');
        if ($flag) {
            $query = $this->db_model->Select("*", "localization", "", "modified_date", "desc", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "localization", "");
        }

        return $query;
    }

    function insert_localization($add_array)
    {
        unset($add_array["action"]);
        $this->db->insert("localization", $add_array);
        return true;
    }

    function edit_localization($add_array, $id)
    {
        unset($add_array["action"]);
        $this->db->where("id", $id);
        $this->db->update("localization", $add_array);
    }

    function remove_localization($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("localization");
    }
}
