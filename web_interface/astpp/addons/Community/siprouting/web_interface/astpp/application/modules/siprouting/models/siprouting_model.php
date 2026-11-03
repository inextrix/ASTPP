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
// #############################################################################
#[\AllowDynamicProperties]
class Siprouting_model extends CI_Model {
	function __construct() {
		parent::__construct ();
	}
	function add_sip_device_routing($add_array) {
        unset($add_array["id"]);
		$final_arr=array(
			 'call_forwarding_flag'=>$add_array['call_forwarding_flag'],
			 'call_forwarding_destination'=>$add_array['call_forwarding_destination'],
			 'on_busy_flag'=>$add_array['on_busy_flag'],
			 'on_busy_destination'=>$add_array['on_busy_destination'],
			 'no_answer_flag'=>$add_array['no_answer_flag'],
			 'no_answer_destination'=>$add_array['no_answer_destination'],
			 'not_register_flag'=>$add_array['not_register_flag'],
			 'sip_device_id'=>$add_array['sip_device_id'],
			 'is_recording'=>$add_array['is_recording']
			);
        $this->db->insert("sip_device_routing", $final_arr);
        return true;
	}

	function edit_sip_device_routing($add_array,$edit_id) {
		$final_arr=array(
			 'call_forwarding_flag'=>$add_array['call_forwarding_flag'],
			 'call_forwarding_destination'=>$add_array['call_forwarding_destination'],
			 'on_busy_flag'=>$add_array['on_busy_flag'],
			 'on_busy_destination'=>$add_array['on_busy_destination'],
			 'no_answer_flag'=>$add_array['no_answer_flag'],
			 'no_answer_destination'=>$add_array['no_answer_destination'],
			 'not_register_flag'=>$add_array['not_register_flag'],
			//  ASTPPCOM-983 Ashish start
			 'not_register_destination'=>$add_array['not_register_destination'],
			//  ASTPPCOM-983 Ashish End
			 'sip_device_id'=>$add_array['sip_device_id'],
			 'is_recording'=>$add_array['is_recording']
			);
       	$this->db->where('id', $edit_id);
        $this->db->update('sip_device_routing', $final_arr);
        return true;
	}

	function edit_voice($data,$id)
	{
            unset($data["action"]);
            $this->db->where("id", $data["edit_id"]);
            unset($data["edit_id"]);
            $this->db->update("sip_devices", $data);
            return true;
        
	}

	function fs_retrieve_sip_user($flag, $start = 0, $limit = 0) {
		$deviceinfo = array ();
		$this->db_model->build_search ( 'fssipdevices_list_search' );
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		$query = array ();
		$logintype = $this->session->userdata ( "logintype" );
		$where ['reseller_id'] = $reseller_id;
		
		if ($flag) {
			$deviceinfo = $this->db_model->select ( "*", "sip_devices", $where, "id", "ASC", $limit, $start );
			if ($deviceinfo->num_rows () > 0) {
				$add_array = $deviceinfo->result_array ();
				foreach ( $add_array as $key => $value ) {
					$vars = json_decode ( $value ['dir_vars'] );
					$vars_new = json_decode ( $value ['dir_params'], true );
					$passowrds = json_decode ( $value ['dir_params'] );
					$query [] = array (
							'id' => $value ['id'],
							'username' => $value ['username'],
							'accountid' => $value ['accountid'],
							'status' => $value ['status'],
							'creation_date' => $value ['creation_date'],
							'last_modified_date' => $value ['last_modified_date'],
							'sip_profile_id' => $value ['sip_profile_id'],
							'effective_caller_id_name' => $vars->effective_caller_id_name,
							'voicemail_enabled' => $vars_new ['vm-enabled'],
							'voicemail_password' => $vars_new ['vm-password'],
							'voicemail_mail_to' => $vars_new ['vm-mailto'],
							'voicemail_attach_file' => $vars_new ['vm-attach-file'],
							'vm_keep_local_after_email' => $vars_new ['vm-keep-local-after-email'],
							'effective_caller_id_number' => $vars->effective_caller_id_number,
							'password' => $passowrds->password ,
							'is_recording' => $value ['is_recording'],
					);
				}
			}
		} else {
			$query = $this->db_model->countQuery ( "*", 'sip_devices', $where );
		}
		return $query;
	}
	
}
