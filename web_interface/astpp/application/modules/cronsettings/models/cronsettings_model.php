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
class Cronsettings_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_cron_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('cronsettings_list_search');
        $this->db->select('*');
        if ($flag) {
            $this->db->limit($limit, $start);
        }
        if (isset($_GET['sortname']) && $_GET['sortname'] != 'undefined') {
            $this->db->order_by($_GET['sortname'], ($_GET['sortorder'] == 'undefined') ? 'desc' : $_GET['sortorder']);
        } else {
            $this->db->order_by('creation_date', 'desc');
        }
        $result = $this->db->get('cron_settings');
        if ($flag) {
            return $result;
        } else {
            return $result->num_rows();
        }

        return $result->num_rows();
    }

    function check_unique_name($name)
    {
        $where = array(
            'name' => $name
        );
        $query = $this->db_model->countQuery("*", "cron_settings", $where);
        return $query;
    }

    function check_unique_name_for_edit($name)
    {
        $where = array(
            'name' => $name
        );
        $this->db->where($where);
        $this->db->select("*");
        $this->db->from('cron_settings');
        $query = $this->db->get();
        return $query;
    }

    function add_cron($add_array)
    {
        // print_r($add_array); die;
	$add_array['next_execution_date'] = $this->common->convert_GMT($add_array['next_execution_date']);
	$add_array["last_execution_date"] = '0000-00-00 00:00:00';
        unset($add_array["action"]);
        $add_array["last_modified_date"] = gmdate('Y-m-d H:i:s');
        $this->db->insert("cron_settings", $add_array);
        return true;
    }

    function edit_cron($add_array, $id)
    {
    $add_array['next_execution_date'] = $this->common->convert_GMT_new($add_array['next_execution_date']);
	$add_array["last_modified_date"] = gmdate('Y-m-d H:i:s');
        unset($add_array["action"]);
        $this->db->where("id", $id);
        $this->db->update("cron_settings", $add_array);
    }

    function remove_cron($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("cron_settings");
    }
}
