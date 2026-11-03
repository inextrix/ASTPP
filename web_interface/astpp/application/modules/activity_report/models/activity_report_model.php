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
class Activity_report_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function getcustomer_report_list($flag, $start = 0, $limit = 0, $export = true) {
        $this->db_model->build_search('activity_search');
            $accountinfo = $this->session->userdata('accountinfo');
        $where=array();
       
         if ($this->session->userdata('activity_search') != '' and $this->session->userdata('activity_search') != '0') {
                   $activity_search = $this->session->userdata('activity_search');
              
            if (isset($activity_search) && $activity_search['last_did_call_time'][0] != "") {
                        $where_date['last_did_call_time >='] = $activity_search['last_did_call_time'][0];
                    }
                    if (isset($activity_search) && $activity_search['last_did_call_time'][1] != "") {
                        $where_date['last_did_call_time <='] = $activity_search['last_did_call_time'][1];
                    }

            if (isset($activity_search) && $activity_search['last_outbound_call_time'][0] != "") {
                        $where_date['last_outbound_call_time >='] = $activity_search['last_outbound_call_time'][0];
                    }
                    if (isset($activity_search) && $activity_search['last_outbound_call_time'][1] != "") {
                        $where_date['last_outbound_call_time <='] = $activity_search['last_outbound_call_time'][1];
                    }
        } else {
            
            $where_date['last_did_call_time >='] = date('1000-01-01 00:00:00');
            $where_date['last_did_call_time <='] = date('Y-m-d 23:59:59');
            $where_date['last_outbound_call_time >='] = date('1000-01-01 00:00:00');
            $where_date['last_outbound_call_time <='] = date('Y-m-d 23:59:59');
        }
            
         if (isset($where_date)) {
                $this->db->where($where_date);
            }
            if ($flag) {
            if ($export)
                $this->db->limit($limit, $start);
                $query = $this->db_model->select("*, accountid as accountid_balance,accountid as accountid_creditlimit", "activity_reports",$where , "id", "ASC");
            } else {
                $query = $this->db_model->countQuery("*", "activity_reports", $where);
            }
            return $query;
        }
}
