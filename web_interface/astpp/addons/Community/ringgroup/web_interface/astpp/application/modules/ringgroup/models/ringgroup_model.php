<?php
###########################################################################
# ASTPP - Open Source Voip Billing
# Copyright (C) 2004, Aleph Communications
#
# Contributor(s)
# "iNextrix Technologies Pvt. Ltd - <astpp@inextrix.com>"
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details..
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>
############################################################################
#[\AllowDynamicProperties]
class ringgroup_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }
    function getringgroup_list($flag, $start = 0, $limit = 0) {
        $this->db_model->build_search('ringgroup_list_search');
        $accountinfo = $this->session->userdata('accountinfo');
        if ($this->session->userdata ( 'logintype' ) == 1) {    
            $where = array ( "reseller_id" => $accountinfo['id'] );
        } else {
            $where = array (  );
        }
        if ($accountinfo ['type'] == '0') {
            $where = array ( 'accountid' => $accountinfo ['id'] );
        }
        if ($flag) {
            $query = $this->db_model->select("*", "pbx_ringgroup", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "pbx_ringgroup", $where);
        }

        return $query;
    }


    function add_ringgroup_list($add_array) {
        unset($add_array["id"]);
        $new_array = array();
        $j=1;
        for ($i = 1; $i <= 10; $i++) {
         if($add_array['strategy'] == 'simultaneous') {
            if (astpp_array_key_exists('time_out_' . $i, $add_array)) {
                
                $add_array['time_out_' . $i] = $add_array['time_out_' . $i];
            }
            if (astpp_array_key_exists('delay_' . $i, $add_array)) {
                
                $add_array['delay_' . $i] = '1';
            }
            if (astpp_array_key_exists('Promptdropdown_' . $i, $add_array)) {
                
                $add_array['Promptdropdown_' . $i] = '1';
            }
            if (astpp_array_key_exists('extensions_type_' . $i, $add_array)) {
                
                if (isset($add_array['extensions_type_' . $i]) && $add_array['extensions_type_' . $i] != '') {

                    if ($add_array['extensions_type_' . $i] == "0") {
                        $new_array['destination_' . $j]=$add_array['extensions_set_' . $i];
                    }
                    if ($add_array['extensions_type_' . $i] == "1") {
                        $new_array['destination_' . $j]=$add_array['input_extensions_set_' . $i]."#";
                    }
                    $new_array['delay_' . $j] = '1';
                    $new_array['time_out_' . $j] = $add_array['time_out_' . $i];
                    $new_array['Promptdropdown_' . $j] = '1';
                    $j++;
                }
            }
        }

        if($add_array['strategy'] == 'sequence') {
            if (astpp_array_key_exists('time_out_' . $i, $add_array)) {
                
                $add_array['time_out_' . $i] = $add_array['time_out_' . $i];
            }
            if (astpp_array_key_exists('delay_' . $i, $add_array)) {
                
                $add_array['delay_' . $i] = '1';
            }
            if (astpp_array_key_exists('Promptdropdown_' . $i, $add_array)) {
                $add_array['Promptdropdown_' . $i] = '1';
            }
            if (astpp_array_key_exists('extensions_type_' . $i, $add_array)) {
                if (isset($add_array['extensions_type_' . $i]) && $add_array['extensions_type_' . $i] != '') {

                    if ($add_array['extensions_type_' . $i] == "0") {
                        $new_array['destination_' . $j]=$add_array['extensions_set_' . $i];
                    }
                    if ($add_array['extensions_type_' . $i] == "1") {
                        $new_array['destination_' . $j]=$add_array['input_extensions_set_' . $i]."#";
                    }
                    $new_array['delay_' . $j] = '1';
                    $new_array['time_out_' . $j] = $add_array['time_out_' . $i];
                    $new_array['Promptdropdown_' . $j] = '1';
                    $j++;
                }
            }
        }
    }


    $destination_array = json_encode($new_array);
    $accountinfo = $this->session->userdata('accountinfo');
    if($accountinfo['type'] == -1 || $accountinfo['type'] == 2){
     $account_id = $add_array['accountid'];
     $reseller_id = $add_array['reseller_id_'];
 }
 if($accountinfo['type'] == 1){
     $account_id = $add_array['accountid'];
     $reseller_id = $accountinfo['id'];
 }
 if(astpp_eq0($accountinfo['type'])){
     $account_id = $accountinfo['id']; 
     $reseller_id = (astpp_gt($accountinfo['reseller_id'], 0))?$accountinfo['reseller_id']:0;
 }
 
 $final_arr=array(
   'name'               =>trim(preg_replace('/\s+/', ' ', $add_array['name'])),
   'accountid'          => $account_id,
   'reseller_id'        => $reseller_id,
   'creation_date'      => gmdate('Y-m-d H:i:s'),
   'strategy'           =>$add_array['strategy'],
   'destinations'       =>$destination_array, 
   'description'        =>$add_array['description'],
   'status'             => $add_array['status']
);

 if ($add_array ['no_answer_call_type'] == 'pstn_1') {
    $final_arr['no_answer_call_type_value'] = $add_array['no_answer_call_type_value'];
    $final_arr['no_answer_call_type'] = 'pstn_1';
} else {
    $description_explode = explode('_', $add_array ['no_answer_call_type']);
    $final_arr['no_answer_call_type_value'] = $description_explode[1];   
    $final_arr['no_answer_call_type'] = $description_explode[0];   
}

if ($final_arr ['no_answer_call_type_value'] == '' || !isset($final_arr ['no_answer_call_type_value'])) {
    $final_arr ['no_answer_call_type_value'] = 0;
}

$this->db->insert("pbx_ringgroup", $final_arr);
$last_inserted_id = $this->db->insert_id();
return true;
}

function edit_ringgroup_list($add_array,$edit_id) {
   $new_array = array();
   $j         = 1;

   for ($i = 1; $i <= 25; $i++) {

    if($add_array['strategy'] == 'simultaneous') {
        if (astpp_array_key_exists('time_out_' . $i, $add_array)) {
            
            $add_array['time_out_' . $i] = $add_array['time_out_' . $i];
        }
        if (astpp_array_key_exists('delay_' . $i, $add_array)) {
            
            $add_array['delay_' . $i] = '1';
        }
        if (astpp_array_key_exists('Promptdropdown_' . $i, $add_array)) {
            $add_array['Promptdropdown_' . $i] = '1';
        }
        if (astpp_array_key_exists('extensions_type_' . $i, $add_array)) {
            
            if (isset($add_array['extensions_type_' . $i]) && $add_array['extensions_type_' . $i] != '') {

                if ($add_array['extensions_type_' . $i] == "0") {
                    $new_array['destination_' . $j]=$add_array['extensions_set_' . $i];
                }
                if ($add_array['extensions_type_' . $i] == "1") {
                    $new_array['destination_' . $j]=$add_array['input_extensions_set_' . $i]."#";
                }
                $new_array['delay_' . $j] = '1';
                $new_array['time_out_' . $j] = $add_array['time_out_' . $i];
                $new_array['Promptdropdown_' . $j] = '1';
                $j++;
            }
        }
    }

    if($add_array['strategy'] == 'sequence') {
        if (astpp_array_key_exists('time_out_' . $i, $add_array)) {
            
            $add_array['time_out_' . $i] = $add_array['time_out_' . $i];
        }
        if (astpp_array_key_exists('delay_' . $i, $add_array)) {
            
            $add_array['delay_' . $i] = '1';
        }
        if (astpp_array_key_exists('Promptdropdown_' . $i, $add_array)) {
            $add_array['Promptdropdown_' . $i] = '1';
        }
        if (astpp_array_key_exists('extensions_type_' . $i, $add_array)) {
            
            if (isset($add_array['extensions_type_' . $i]) && $add_array['extensions_type_' . $i] != '') {

                if ($add_array['extensions_type_' . $i] == "0") {
                    $new_array['destination_' . $j]=$add_array['extensions_set_' . $i];
                }
                if ($add_array['extensions_type_' . $i] == "1") {
                    $new_array['destination_' . $j]=$add_array['input_extensions_set_' . $i]."#";
                }
                $new_array['delay_' . $j] = '1';
                $new_array['time_out_' . $j] = $add_array['time_out_' . $i];
                $new_array['Promptdropdown_' . $j] = '1';
                $j++;
            }
        }
    }
}

$destination_array = json_encode($new_array);
$accountinfo       = $this->session->userdata('accountinfo');

$final_arr=array(
   'name'          => trim(preg_replace('/\s+/', ' ', $add_array['name'])),
   'strategy'      => $add_array['strategy'],
   'destinations'  => $destination_array, 
   'description'   => trim(preg_replace('/\s+/', ' ', $add_array['description'])),
   'no_answer_call_type'=> $add_array['no_answer_call_type'],
   'status'=> $add_array['status']
);

if ($add_array ['no_answer_call_type'] == 'pstn_1') {
    $final_arr['no_answer_call_type_value'] = $add_array['no_answer_call_type_value'];
    $final_arr['no_answer_call_type']       = 'pstn_1';
} else {
    $description_explode = explode('_', $add_array ['no_answer_call_type']);
    $final_arr['no_answer_call_type_value'] = $description_explode[1];   
    $final_arr['no_answer_call_type']       = $description_explode[0];   
}

if ($final_arr ['no_answer_call_type_value'] == '' || !isset($final_arr ['no_answer_call_type_value'])) {
    $final_arr ['no_answer_call_type_value'] = 0;
}
$this->db->where('id', $edit_id);
$result = $this->db->update('pbx_ringgroup', $final_arr);
return true;
}


function delete_ringgroup($id) {
    $this->db->where('id', $id);
    $this->db->delete('pbx_ringgroup');
    return true;
}
function edit_ringgroup($edit_id) {
    $ringgroup     = $this->db_model->getSelect("*", "pbx_ringgroup", array('id'=>$edit_id));
    $ringgroup_res = $ringgroup->result_array();
    $ringgroup     = $ringgroup_res[0];
    $destinations  = json_decode($ringgroup['destinations'],true);
    unset($ringgroup['destinations']);
    $ringgroup['count'] = astpp_count($destinations)/4;
    $ringgroup=astpp_array_merge($ringgroup,isset($destinations) ?$destinations : array());
    return $ringgroup;
}
}
