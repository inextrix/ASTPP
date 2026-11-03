<?php
// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
//
// Copyright (C) 2016 Inextrix Technologies Pvt. Ltd.
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
class Automated_report_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    function getcustomer_automated_report_list($flag, $start = 0, $limit = 0, $export = true) {
        $this->db_model->build_search('automated_report_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $where=array();
            if ($flag) {
                $this->db->limit($limit, $start);
                $query = $this->db_model->select("*", "automated_reports",$where , "id", "ASC");
            } else {
                $query = $this->db_model->countQuery("*", "automated_reports", $where);
            }
            return $query;
        }
        function edit_autommated_report($data, $id)
        {
            unset($data["action"]);
            $this->db->where("id", $id);
            $this->db->update("automated_reports", $data);
        }
        function add_automated_report($add_array)
        {
            unset($add_array["action"]);
            $this->db->insert("automated_reports", $add_array);
            return true;
        }
}
