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
class Low_balance_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function getlowbalance_list($flag, $start = 0, $limit = 0)
    {
        $accountinfo = $this->session->userdata ( 'accountinfo' );
        $this->db_model->build_search('low_balance_list_search');
        if($accountinfo['type'] == '1'){
            $reseller_id = $accountinfo['id'];
        }else{
            $reseller_id = "0";
        }
        $entity_array = array (
                "0",
                "1",
                "3" 
        );
        $where = "notify_flag = '" . 0 . "' AND deleted = '" . 0 .  " ' AND status = ' " . 0 . " ' AND (posttoexternal ='" . 0 . "' AND " . "balance <= notify_credit_limit"  . ") OR ( posttoexternal ='" . 1 . "' AND " . "credit_limit - balance <= notify_credit_limit"  . ")";

        $this->db->where_in ( "type", $entity_array );
        if ($flag) {
            $query = $this->db_model->select("*", "accounts", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "accounts", $where);
        }
        return $query;
    }
    
}
