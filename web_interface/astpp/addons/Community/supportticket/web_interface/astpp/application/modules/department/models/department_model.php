<?php
###############################################################################
# ASTPP - Open Source VoIP Billing Solution
#
# Copyright (C) 2016 iNextrix Technologies Pvt. Ltd.
# Samir Doshi <samir.doshi@inextrix.com>
# ASTPP Version 3.0 and above
# License https://www.gnu.org/licenses/agpl-3.0.html
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
# 
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
###############################################################################
#[\AllowDynamicProperties]
class Department_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getdepartment_list($flag, $start = 0, $limit = 0) {
        $this->db_model->build_search('department_list_search');
    	$where=array();
        // ASTPPCOM-925 Start
        $accountinfo = $this->session->userdata('accountinfo');
        if ($accountinfo['type'] == 1 ) {
            $where['reseller_id'] = $accountinfo['id']; 
        }
        // ASTPPCOM-925 END
        if ($flag) {
            $query = $this->db_model->select("*", "department", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "department", $where);
        }
        return $query;
    }

    function add_department($data) {
        unset($data["action"]);
        // ASTPPCOM-925 Start
        $accountinfo = $this->session->userdata('accountinfo');
        $data['reseller_id'] = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        // ASTPPCOM-925 END
    	$data['email_id'] = $data['smtp_user'];
        $this->db->insert("department", $data);
        return true;
    }

     function edit_department($data, $id) {
	$data['email_id'] = $data['smtp_user'];
        unset($data["action"]);
        $this->db->where("id", $id);
        $this->db->update("department", $data);
    }

    function remove_department($id) {
        $this->db->where("id", $id);
        $this->db->delete("department");
        return true;
    }
    
    function get_area_code($flag, $start = 0, $limit = 0, $export = true) {
        $this->db_model->build_search('department_list_search');
        $this->db->from('department');
        if ($flag) {
            if ($export)
                $this->db->limit($limit, $start);
            $result = $this->db->get();
        }else {
            $result = $this->db->count_all_results();
        }
        return $result;
    }
    
	function bulk_insert_area_code($inserted_array) {
	        $this->db->insert_batch('department', $inserted_array);
	        $affected_row = $this->db->affected_rows();
	        return $affected_row;
	}
	function drp_downlist(){
		$this->db->where('type', '2');
		$this->db->or_where('type', '-1');
		$where = array(); 
		$query = $this->db_model->Select("id,number,first_name,last_name", "accounts",$where, "", "","", "");
		return $query->result_array ();
	}
}
