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
class Ringgroup extends MX_Controller {
    function __construct() {
        
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library("ringgroup_form");
        $this->load->library('astpp/form','ringgroup_form');
        $this->load->model('ringgroup_model');
        // $this->load->library('astpp/pbx_feature');
        $this->load->helper('security');
        $this->load->library('astpp/permission');
        if ($this->session->userdata('user_login') == FALSE) {
            redirect(base_url() . '/astpp/login');           
        } 
    }

    function ringgroup_list() { 
        $accountinfo = $this->session->userdata('accountinfo');
        if($accountinfo['type'] == 1 || $accountinfo['type'] == 2){
            $this->common->validate_module_access_level('ringgroup','list','ringgroup_list',"");
        }
        $data['username']    = $this->session->userdata('user_name');
        $data['page_title']  = gettext('Ring Group');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields']  = $this->ringgroup_form->build_ringgroup_list_for_admin();
        $data["grid_buttons"] = $this->ringgroup_form->build_grid_buttons();
        $data['form_search']  = $this->form->build_serach_form($this->ringgroup_form->get_ringgroup_search_form());
        $this->load->view('view_ringgroup_list', $data);
    }

    function ringgroup_list_json() {
        $accountinfo = $this->session->userdata('accountinfo');
        if($accountinfo['type'] == 1 || $accountinfo['type'] == 2){
            $this->common->validate_module_access_level('ringgroup','list','ringgroup_list',"");
        }
        $json_data   = array();
        $count_all   = $this->ringgroup_model->getringgroup_list(false);
        $paging_data = $this->form->load_grid_config($count_all,$_GET['rp'], $_GET['page']);
        $json_data   = $paging_data["json_paging"];
        $query       = $this->ringgroup_model->getringgroup_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->ringgroup_form->build_ringgroup_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }


    function ringgroup_add_sipdevices($type = "") {
        $data['username']   = $this->session->userdata('user_name');
        $data['flag']       = 'create';
        $data['page_title'] = gettext('Ring Group add');
        $this->load->view('view_ringgroup_add_sipdevices', $data);
    }

    function ringgroup_edit($edit_id = '') {
        $account_data   = $this->session->userdata ( "accountinfo" );
        if($accountinfo['type'] == 1 || $accountinfo['type'] == 2){
            if($edit_id == ''){
                $this->common->validate_module_access_level('ringgroup','create','ringgroup_list',"ringgroup/ringgroup_list/");
            }else{
                $this->common->validate_module_access_level('ringgroup','edit','ringgroup_list',"ringgroup/ringgroup_list/");
            }       
        }
        $data['page_title'] = gettext('Edit Ring Group');
        $data['edit_array'] = $this->ringgroup_model->edit_ringgroup($edit_id);
        $data['edit_array']['extensions'] = $data['edit_array']['no_answer'];
        $data['count']      = $data['edit_array']['count'];
        if ($account_data['type'] == -1 || $account_data['type'] == 1 || $account_data['type'] == 2) {
            $this->load->view('view_ringgroup_edit', $data); 
        }else{
            $this->load->view('view_user_edit_ringgroup', $data); 
        }
    }
    
    function ringgroup_save() {
        $accountinfo = $this->session->userdata('accountinfo');
        $add_array = $this->input->post();
        if($accountinfo['type'] == 1 || $accountinfo['type'] == 2){
            if($add_array['id'] == ''){
                $this->common->validate_module_access_level('ringgroup','create','ringgroup_list',"ringgroup/ringgroup_list/");
            }else{
                $this->common->validate_module_access_level('ringgroup','edit','ringgroup_list',"ringgroup/ringgroup_list/");
            }   
        }

        $this->form_validation->set_rules('name', 'Name', 'required|xss_clean');
        if($add_array['id'] == '') {
            if ($this->form_validation->run() == FALSE) {
                $data ['validation_errors'] = validation_errors ();
                $data ['page_title'] = gettext ( 'Create Ringgroup' );
                $data ['edit_array'] = $add_array;
                $this->load->view ( 'view_ringgroup_add', $data);
            } else {
                if (!astpp_array_key_exists("accountid",$add_array)) {
                    $add_array['accountid'] = '0';
                }
                
                $query = $this->ringgroup_model->add_ringgroup_list($add_array);
                $this->session->set_flashdata('astpp_errormsg', gettext('Ring group added successfully!'));
                redirect(base_url() . 'ringgroup/ringgroup_list/');
                exit ();
            }
        } else {
            if ($this->form_validation->run() == FALSE) {
                $data ['validation_errors'] = validation_errors ();
                $data ['page_title'] = gettext ( 'Edit Ringgroup' );
                $data['edit_array']=$add_array;
                $this->load->view ( 'view_ringgroup_edit', $data);
            } else {
                $query = $this->ringgroup_model->edit_ringgroup_list($add_array,$add_array['id']);
                $this->session->set_flashdata('astpp_errormsg', gettext('Ring group updated successfully!'));
                redirect(base_url() . 'ringgroup/ringgroup_list/');
                exit ();
            }
        }
    }
    function ringgroup_field_add($count,$val="",$extensions="",$type="") {
        $account_data = $this->session->userdata ( "accountinfo" );
        if(astpp_eq0($account_data ['type'])) {
            $data['accountid']  = $account_data ['id'] ;
        } else {
            $data['accountid']  = $this->uri->segment(4);
        }
        $data['count']      = $count;
        $data['val']        = $val;
        $data['rowcount']   = $data['count'];
        $this->load->view('view_ringgroup_field_add', $data);
    }

    function ringgroup_field_add_user() {
        $account_data = $this->session->userdata ( "accountinfo" );

        $data['count']      = $_POST ['count'];
        if (astpp_array_key_exists('value', $_POST)) {
            $data['val']        = $_POST ['value'];
        } else {
            $data['val']        = '';
        }
        if(astpp_eq0($account_data ['type'])) {
            $data['accountid']  = $account_data ['id'] ;
        } else {
            $data['accountid']  = $_POST ['extensions'];
        }

        $this->load->view('view_ringgroup_field_edit', $data);
    }

    function ringgroup_quick_add($count,$val) {
        $data['count']   = $count;
        $data['val']     = $val;
        $data['rowcount']= $data['count'];
        $this->load->view('view_ringgroup_field_add', $data);
    }

    function ringgroup_delete($id) {
        $this->session->set_flashdata('astpp_notification', gettext('Ring group deleted successfully!'));
        $query = $this->ringgroup_model->delete_ringgroup($id);
        redirect(base_url() . 'ringgroup/ringgroup_list/');
    }
    
    function ringgroup_list_search() {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('ringgroup_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'ringgroup/ringgroup_list/');
        }
    }

    function ringgroup_list_clearsearchfilter() {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }
    
    function ringgroup_delete_multiple() {
        $account_data = $this->session->userdata ( "accountinfo" );
        $ids = $this->input->post("selected_ids", true);
        if($account_data['type'] == '1'){
            $this->common->validate_module_access_level('ringgroup','delete','ringgroup_list',"ringgroup/ringgroup_list/");
            $ids = $this->permission->validate_multiple_delete_access($ids, 'pbx_ringgroup', 'products/products_list/',"reseller_id",true,"id");
        }
        if($ids != ''){
            $where = "id IN ($ids)";
            $this->db->where($where);
            $this->db->delete("pbx_ringgroup");
        }   
        echo TRUE;
    }
    
    function ringgroup_add() { 
        $account_data = $this->session->userdata ( "accountinfo" );
        if ($account_data['type'] == 2 || $account_data['type'] == 1 ) {
            $this->common->validate_module_access_level('ringgroup','create','ringgroup_list',"ringgroup/ringgroup_list/");
        }

        $post_arr     = $this->input->post();
        $destination  = array();
        $data['count']= 0;
        if(isset($post_arr['sip_device_count']) && $post_arr['sip_device_count'] != '') {
           $j = 0;
           for($i=1 ; $i <= $post_arr['sip_device_count'];$i++) {
              $uname       = $this->common->find_uniq_rendno('10', '', '');
              $password    = $this->common->generate_password();
              $parms_array = array(
                'password'       => $password,
                'vm-enabled'     => 'false',
                'vm-password'    => 1,
                'vm-mailto'      => '',
                'vm-attach-file' => 1,
                'vm-keep-local-after-email' => 1,
                'vm-email-all-messages'     => 1
            );
              $parms_array_vars = array(
                'effective_caller_id_name'   => $uname,
                'effective_caller_id_number' => $uname,
                'user_context'               => 'default'
            );
              $log_type       = $this->session->userdata("logintype");
              $sip_profile_id = $this->common->get_field_name('id','sip_profiles',array('name'=>'default'));
              $new_array = array(
                'username'       => $uname,
                'accountid'      => $post_arr['accountid'],
                'status'         => 0,
                'dir_params'     => json_encode($parms_array),
                'dir_vars'       => json_encode($parms_array_vars),
                'sip_profile_id' => $sip_profile_id
            );
              $this->db->insert('sip_devices', $new_array);
              $destination[] = $uname;
              $j++;
          }
          $data['destination']= $destination;
          $data['accountid']  = $post_arr['accountid'];
          $data['count']      = $j;
      }
      $data['page_title']   = gettext('Ring Group Add');
      if ($account_data['type'] == -1 || $account_data['type'] == 1 || $account_data['type'] == 2) {
        $this->load->view('view_ringgroup_add', $data); 
    }else{
        $this->load->view('view_user_add_ringgroup', $data); 
    }
}
function ringgroup_did_change($account_type,$did_id='') {
    if($account_type!='') {
        $whr = array(
            "accountid" => $account_type
        );
        $account = $this->db_model->getSelect("number,id,accountid", "dids", $whr);
        if ($account->num_rows() > 0) {
            $account_data = $account->result_array();
            $did_arr      = array();
            foreach ($account_data as  $value) {
                if(astpp_eq0($value['accountid'])) {
                    $admin_name= "Admin";
                } else {
                    $first_name = $this->common->get_field_name('first_name', 'accounts', array('id'=>$value['accountid']));
                    $last_name  = $this->common->get_field_name('last_name', 'accounts', array('id'=>$value['accountid']));
                    $admin_name = $first_name.' '.$last_name;
                }
                $did_arr[$value['id']] =  $value['number'];
            }
            $did_info = array(
                "name"  => "didid",
                "id"    => "did",
                "class" => "col-md-12 form-control form-control-lg selectpicker did"
            );
            echo form_dropdown_all($did_info, $did_arr,$did_id);     
        } else {
          echo '<select class="col-md-12 form-control selectpicker form-control-lg did" id="did" name="didid"><option>--</option></select>';
      }
  } else {
    echo '<input name="didid" value="" size="20" maxlength="180" class="col-md-12 form-control form-control-lg did" id="did" type="text">';
}
}

function customer_account_change($reseller_id)
{
    $reseller_id = $reseller_id > 1 ? $reseller_id : '0';
    $accounts = $this->db_model->getSelect("*", "accounts", array(
        'reseller_id' => $reseller_id,
        'type' => 0,
        'deleted' => 0,
        'status' => 0
    ));
    if ($accounts->num_rows() > 0) {
        $accounts_data = $accounts->result_array();
        foreach ($accounts_data as $value) {
            if(isset($value['company_name']) && $value['company_name'] != ''){
                echo "<option value=" . $value['id'] . ">" . $value['company_name'] . " ( " . $value['number'] . ") </option>";    
            }else{
                echo "<option value=" . $value['id'] . ">" . $value['first_name'] . " " . $value['last_name'] . " ( " . $value['number'] . ") </option>";
            }
        }
    } else {
        echo "<select><option value=''>".gettext('--Select--')."</option></select>";
    }
}

function ringgroup_quick_change($account_type) {
    
    if($account_type!='') {
        $whr = array(
            "accountid" => $account_type
        );
        $account = $this->db_model->getSelect("username,id", "sip_devices", $whr);
        if ($account->num_rows() > 0) {
            $account_data    = $account->result_array();
            $destination_arr = array();
            foreach ($account_data as  $value) {
                $value['accountid'] = $value['id'];

                if(astpp_eq0($value['accountid'])) {
                    $admin_name= "Admin";
                } else {
                    $first_name = $this->common->get_field_name('first_name', 'accounts', array('id'=>$value['accountid']));
                    $last_name  = $this->common->get_field_name('last_name', 'accounts', array('id'=>$value['accountid']));
                    $admin_name = $first_name.' '.$last_name;
                }
                $destination_arr[$value['username']] =  $value['username'];
            }
            $did_info = array("name" => "quick" ,"id" => "quickid", "class" => "col-md-2 form-control  quickid selectpicker");
            echo form_dropdown_all($did_info, $destination_arr);
        } else {
          echo '<select class="col-md-5 form-control quickid" id="quickid" name="quick"><option>--</option></select>';
      }
  } else {
    echo '<input name="quick" value="" size="20" maxlength="180" class="col-md-5 form-control quickid" id="quickid" type="text">';
}
}
function ringgroup_sip_list($act_id,$ext='',$count='') {
    $count = $this->uri->segment(4);

    if($act_id != "") {
        $whr = array (
            "accountid"   => $act_id,
            "status"      => 0
        );
        $sip_devices = $this->db_model->getSelect( '*' ,'sip_devices', $whr);
        if ($sip_devices->num_rows() > 0) {
            $sip_devices_data = $sip_devices->result_array();
            $sip_devices_arr  = array();
            foreach ($sip_devices_data as  $value) {
                if(astpp_eq0($value['accountid'])) {
                    $admin_name= "Admin";
                } else {
                    $first_name = $this->common->get_field_name('first_name', 'accounts', array('id'=>$value['accountid']));
                    $last_name  = $this->common->get_field_name('last_name', 'accounts', array('id'=>$value['accountid']));
                    $number     = $this->common->get_field_name('number', 'accounts', array('id'=>$value['accountid']));
                    $admin_name = $first_name.' '.$last_name." ( ".$number." )";
                }
                $sip_devices_arr[$value['username']] =  $value['username']." (".$admin_name.")";
            }
            $sip_devices_info = array("name" => "extensions_set_$count" ,"id" => "extensions_set_$count", "class" => " extensions_set_$count ");
            echo form_dropdown_mini($sip_devices_info, $sip_devices_arr, $ext);
        } else {
          echo '<select class="col-md-6 form-control form-control-lg selectpicker extensions_set_$count" id="extensions_set_$count" name="extensions_set_$count"><option>--</option></select>';
      }
  }
}
function ringgroup_announcement_change($account_type) {
    if($account_type!='') {
        $announcement_arr = array();
        $accountinfo = $this->session->userdata ( "accountinfo" );
        if($accountinfo['type'] == '-1' || $accountinfo['type'] == '2' ) {
            $whr= array("accountid"=>$account_type);
        } else if($accountinfo['type'] == '1')  {
            $whr= array("accountid"=>$account_type);
        } else {
            $whr= array("accountid"=>$accountinfo['id']);
        }
        $account = $this->db_model->getSelect("name,id", "pbx_recording",$whr);
        if ($account->num_rows() > 0) {
            $account_data=$account->result_array();
            foreach ($account_data as $value) {
              $announcement_arr[$value['id']] =  $value['name'];
          }
      }
      $did_info = array("name" => "announcement" ,"id" => "announcementid", "class" => "col-md-2 form-control selectpicker announcementid");
      echo form_dropdown_all($did_info, $announcement_arr);
  } else {
    echo '<select class="col-md-12 form-control selectpicker form-control-lg announcementid" id="announcementid" name="announcement"><option>--</option></select>';
}
}
function ringgroup_type_change($account_type) {
    if($account_type!='') {
        $final_array = $this->pbx_feature->build_dropdown_ivr_customer($account_type);
        $did_info = array("name" => "no_answer_call_type" ,"id" => "no_answer_call_type", "class" => "form-control form-control-lg selectpicker no_answer_call_type"  , "onchange"=>"jsfunction()");
        echo form_dropdown_all($did_info, $final_array);
    } else {
        echo '<select class="col-md-12 form-control selectpicker form-control-lg no_answer_call_type" id="no_answer_call_type" name="no_answer_call_type"><option>--</option></select>';
    }
}

}

