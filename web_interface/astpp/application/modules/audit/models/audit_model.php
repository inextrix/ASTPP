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
class Audit_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_audit_list($flag, $start = 0, $limit = 10, $export = false)
    {
        $search = $this->session->userdata('audit_list_search');
        $this->db_model->build_search('audit_list_search');
        if ($this->session->userdata('advance_search') != 1) {
            $this->db->where('timestamp >= "' . gmdate("Y-m-d") . " 00:00:00" . '"');
            $this->db->where('timestamp <= "' . gmdate("Y-m-d") . " 23:59:59" . '"');
        }
        $this->db->select('*');

        if ($flag) {
            $this->db->limit($limit, $start);
        }
        if (isset($_GET['sortname']) && $_GET['sortname'] != 'undefined') {
            $this->db->order_by($_GET['sortname'], ($_GET['sortorder'] == 'undefined') ? 'desc' : $_GET['sortorder']);
        } else {
            $this->db->order_by('timestamp', 'desc');
        }
        $result = $this->db->get('usertracking');
        if ($flag) {
            return $result;
        } else {
            return $result->num_rows();
        }

        return $result->num_rows();
    }
}
