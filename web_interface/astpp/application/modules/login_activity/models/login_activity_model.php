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
// MERCHANTABILITY or FITNESS FITNESSOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################
#[\AllowDynamicProperties]
class Login_activity_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_login_activity_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('login_activity_search');
        $where = array();
        if ($this->session->userdata('advance_search') != 1) {
                $where = array(
                    'timestamp >= ' =>$this->common->convert_GMT_new ( date('Y-m-d') . " 00:00:01"),
                    'timestamp <=' => $this->common->convert_GMT_new (date("Y-m-d") . " 23:59:59")
                );
            }
        
        if ($flag) {
            $query = $this->db_model->select("*", "login_activity_report", $where, "id", "DESC", $limit, $start);
              // print_r($this->db->last_query());exit;
        } else {
            $query = $this->db_model->countQuery("*", "login_activity_report", $where);
             // print_r($this->db->last_query());exit;
        }
        return $query;
    }

    
}
  