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
class Freeswitch extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library("freeswitch_form");
        $this->load->library('astpp/form', 'freeswitch_form');
        $this->load->library('astpp/permission');
        $this->load->library('freeswitch_lib');
        $this->load->library('ASTPP_Sms');
        $this->load->model('freeswitch_model');

        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function fssipdevices_add($type = "")
    {
        $data['username'] = $this->session->userdata('user_name');
        $account_data = $this->session->userdata("accountinfo");
        $data['flag'] = gettext('create');
        $data['page_title'] = gettext('Create SIP Device');
        if ($account_data['type'] == '-1' || $account_data['type'] == '1') {
            $data['form'] = $this->form->build_form($this->freeswitch_form->fsdevice_form_fields_for_customer($type), '');
            $this->load->view('view_freeswitch_add_edit', $data);
        } else {
            $data['form'] = $this->form->build_form($this->freeswitch_form->get_freeswith_form_fields(), '');
            $this->load->view('view_freeswitch_customer_add_edit', $data);
        }
    }

    function customer_fssipdevices_add($accountid)
    {
        $data['username'] = $this->session->userdata('user_name');
        $account_data = $this->session->userdata("accountinfo");
        $data['page_title'] = gettext('Create SIP Device');
        $data['form'] = $this->form->build_form($this->freeswitch_form->fsdevice_form_fields_for_customer($accountid), "");
        if ($account_data['type'] == '-1' || $account_data['type'] == '1') {
            $this->load->view('view_freeswitch_add_edit', $data);
        } else {
            $this->load->view('view_freeswitch_customer_add_edit', $data);
        }
    }

    function fssipdevices_edit($edit_id = '')
    {
        $this->permission->check_web_record_permission($edit_id, 'sip_devices', 'freeswitch/fssipdevices/');
        $data['page_title'] = gettext('Edit SIP device');
        $account_data = $this->session->userdata("accountinfo");
        $where = array(
            'id' => $edit_id
        );
        $account = $this->freeswitch_model->get_edited_data($edit_id);
        $data['reseller_id']=$account['reseller_id'] > 0 ? $account['reseller_id'] : '0';
    	$reseller_id=$data['reseller_id'];
        $account['reseller_id'] = $account['reseller_id'] > 0 ? $this->common->build_concat_string("first_name,last_name,number,company_name", 'accounts', $account['reseller_id']) : 'Admin';

        if ($account_data['type'] == '-1') {
            $data['form'] = $this->form->build_form($this->freeswitch_form->get_freeswith_form_fields($edit_id, $reseller_id), $account);
            $this->load->view('view_freeswitch_add_edit', $data);
        } else {
            $data['form'] = $this->form->build_form($this->freeswitch_form->get_freeswith_form_fields($edit_id), $account);
            $this->load->view('view_freeswitch_customer_add_edit', $data);
        }
    }

    function customer_fssipdevices_edit($edit_id, $accountid)
    {
        $this->permission->check_web_record_permission($edit_id, 'sip_devices', 'freeswitch/fssipdevices/');
        $data['username'] = $this->session->userdata('user_name');
        $account_data = $this->session->userdata("accountinfo");
        $data['page_title'] = gettext('Edit SIP device');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->freeswitch_model->get_edited_data($edit_id);
        if ($account_data['type'] == '-1' || $account_data['type'] == '1') {
            $data['form'] = $this->form->build_form($this->freeswitch_form->fsdevice_form_fields_for_customer($accountid, $edit_id), $account);
            $this->load->view('view_freeswitch_add_edit', $data);
        } else {

            $data['form'] = $this->form->build_form($this->freeswitch_form->fsdevice_form_fields_for_customer($accountid, $edit_id), $account);
            $this->load->view('view_freeswitch_customer_add_edit', $data);
        }
    }

    function fsgateway_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('fsgateway_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'freeswitch/fsgateway/');
        }
    }

    function fssipprofile_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('fssipprofile_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'freeswitch/fssipprofile/');
        }
    }

    function fssipdevices_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function fsgateway_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function fssipprofile_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function fssipdevices_save($user_flg = false)
    {
        $add_array = $this->input->post();
        if (! $user_flg) {
            $data['form'] = $this->form->build_form($this->freeswitch_form->get_freeswith_form_fields($add_array['id']), $add_array);
        } else {
            $data['form'] = $this->form->build_form($this->freeswitch_form->fsdevice_form_fields_for_customer($add_array["accountcode"], $add_array['id']), $add_array);
        }
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Freeswitch SIP Devices');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                unset($add_array['fs_username']);
                $this->db->select('username');
                $fs_username = (array) $this->db->get_where("sip_devices", array(
                    "id" => $add_array['id']
                ))->first_row();
                $add_array['fs_username'] = $fs_username['username'];
                $this->freeswitch_model->edit_freeswith($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext("SIP Device Updated Successfully!")
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Freeswitch SIP Devices');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->freeswitch_model->add_freeswith($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext("SIP Device Added Successfully!")
                ));
                exit();
            }
        }
    }

    function customer_fssipdevices_save($user_flg = false)
    {
        $add_array = $this->input->post();
        if (! $user_flg) {
            $data['form'] = $this->form->build_form($this->freeswitch_form->get_freeswith_form_fields(), $add_array);
        } else {
            $data['form'] = $this->form->build_form($this->freeswitch_form->fsdevice_form_fields_for_customer($add_array["accountcode"], $add_array['id']), $add_array);
        }
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Freeswitch SIP Devices');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->freeswitch_model->edit_freeswith($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext("SIP Device Updated Successfully!")
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Freeswitch SIP Devices');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];

                exit();
            } else {
                $this->freeswitch_model->add_freeswith($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext("SIP Device Added Successfully!")
                ));
                exit();
            }
        }
    }

    function user_fssipdevices_save($user_flg = false)
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->freeswitch_form->fsdevice_form_fields_for_customer($add_array["accountcode"], $add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Freeswitch SIP Devices');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->freeswitch_model->edit_freeswith($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext("SIP Device Updated Successfully!")
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Freeswitch SIP Devices');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $sip_profile_id = $this->common->get_field_name('id', 'sip_profiles', array(
                    'name' => 'default'
                ));
                $this->freeswitch_model->add_freeswith($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext("SIP Device Added Successfully!")
                ));
                exit();
            }
        }
    }

    function fssipdevices_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            $action['dir_params']['dir_params'] ="%". $action['dir_params']['dir_params'];  
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('fssipdevices_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'freeswitch/fssipdevices/');
        }
    }

    function fssipdevices()
    {
        $data['page_title'] = gettext('SIP Devices');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->freeswitch_form->build_system_list_for_admin();
        $data["grid_buttons"] = $this->freeswitch_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->freeswitch_form->get_sipdevice_search_form());
        $this->load->view('view_freeswitch_sip_devices_list', $data);
    }

    function fssipdevices_json()
    {
        $json_data = array();
        $count_all = $this->freeswitch_model->fs_retrieve_sip_user(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->freeswitch_model->fs_retrieve_sip_user(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $permissioninfo = $this->session->userdata('permissioninfo');
        // ASTPPCOM-943 Kinjal Start
        $command = "api sofia xmlstatus profile default reg";  
        $new_array = array();
        $id= 0;
        $response = $this->freeswitch_model->reload_freeswitch($command,$id);
        if($response != ""){
            $response_arr = json_decode(json_encode((array) simplexml_load_string(trim($response))),1);
            if(array_key_exists("registration",$response_arr["registrations"])){
                if(!array_key_exists("0",$response_arr["registrations"]['registration'])){
                    if($response_arr['registrations']['registration']['sip-auth-user'] != ''){
                        array_push($new_array, $response_arr['registrations']['registration']['sip-auth-user']);
                    }
                }else{
                    foreach ($response_arr['registrations']['registration'] as $key => $value) {
                        array_push($new_array, $value['sip-auth-user']);
                    }
                }
            }
        }
        // ASTPPCOM-943 Kinjal END
        foreach ($query as $key => $value) {
            $checkbox = array(
                '<input type="checkbox" name="chkAll" id=' . $value['id'] . ' class="ace chkRefNos" onclick="clickchkbox(' . $value['id'] . ')" value=' . $value['id'] . '><lable class="lbl"></lable>'
            );
            $path_true = base_url() . '/assets/images/true.png';
            $path_false = base_url() . '/assets/images/false.png';
            if ($value['voicemail_enabled'] == 'true') {
                $voicemail_enabled = '<img src=' . $path_true . ' style="height:20px;width:20px;" title="Enable">';
            } else {
                $voicemail_enabled = '<img src=' . $path_false . ' style="height:20px;width:20px;" title="Disable">';
            }
            // ASTPPCOM-943 Start
            $live_status = "<i class='fa fa-circle' style='color: #FF3131'></i>";
            if (in_array($value['username'],$new_array)) {
                $live_status = "<i class='fa fa-circle' style='color: #00FF00'></i>";
            }
            // ASTPPCOM-943 END
            $account_data = $this->session->userdata("accountinfo");
            $reseller_ids = $value['reseller_id'];
            if ($reseller_ids == 0) {
                $reseller_number = 'Admin';
            } else {
                $reseller_number = $this->common->get_field_name_coma_new('first_name,last_name,number,company_name', 'accounts', array(
                    '0' => $reseller_ids
                ));
            }
            // ASTPPCOM-943 Start
            $edit_permission=$value['username'];
            if ((isset($permissioninfo['freeswitch']['fssipdevices']['edit']) && $permissioninfo['freeswitch']['fssipdevices']['edit'] == 0 or $permissioninfo['login_type'] == '-1' )) {
                $base_url = base_url().'freeswitch/fssipdevices_edit/'.$value['id'];
                $edit_permission="<span style='margin-right:8px' id='live_status_".$value['username']."'>".$live_status."</span><a href='/freeswitch/fssipdevices_edit/" . $value['id'] . "' style='cursor:pointer;color:#3b3280' rel='facebox_medium' title='".gettext('User Name')."'>" . $value['username'] . "</a>";
            }
            // ASTPPCOM-943 END
            $current_row = array(
                $checkbox,
                $edit_permission,
                $value['password'],
                $this->common->get_field_name('name', '`sip_profiles', array(
                    'id' => $value['sip_profile_id']
                )),
                $this->common->get_field_name_coma_new('first_name,last_name,number,company_name', 'accounts', array(
                    '0' => $value['accountid']
                )),
                $value['effective_caller_id_name'],
                $value['effective_caller_id_number'],
                $voicemail_enabled,
                $reseller_number,
                $this->common->convert_GMT_to('', '', $value['creation_date']),
                $this->common->convert_GMT_to('', '', $value['last_modified_date']),
                $this->common->get_status('status', 'sip_devices', $value),
                $this->get_advance_buttons_fssipdevices ( $value ['id'] ),
		        $this->get_action_buttons_fssipdevices ( $value ['id'] ) 
            );
            if ($account_data['type'] == 1) {
                if (($key = array_search($reseller_number, $current_row)) !== false) {

                    unset($current_row[$key]);
                }

                $current_row = array_values($current_row);
            }

            $json_data['rows'][] = array(
                'cell' => $current_row
            );
        }
        echo json_encode($json_data);
    }

    //  ASTPPCOM-984 change 
     function get_domain_fssipdevices($accountid) { 
        $sip_routing_status = $this->db_model->countQuery ( "*", "addons", array("package_name"=>"multi_tenant_ip_pbx") );  
        if(isset($sip_routing_status) && $sip_routing_status ==1){  
            $ret_url=$this->common->get_field_name ( 'domain', '`domains', array (  
                'accountid' => $accountid   
            ) );    
        }else{  
            $ret_url='';    
        }   
        return $ret_url;    
    }

    function fssipdevices_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("sip_devices");
    }

    function user_fssipdevices_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        $this->db->delete("sip_devices");
        echo TRUE;
    }

    function customer_fssipdevices_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        $this->db->delete("sip_devices");
        echo TRUE;
    }

    function customer_fssipdevices_json($accountid, $entity_type = '')
    {
        $json_data = array();
        $count_all = $this->freeswitch_model->get_sipdevices_list(false, $accountid, $entity_type);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $devices_result = array();
        $query = $this->freeswitch_model->get_sipdevices_list(true, $accountid, $entity_type, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $this->load->module('accounts/accounts');
        foreach ($query as $key => $value) {
            $path_true = base_url() . '/assets/images/true.png';
            $path_false = base_url() . '/assets/images/false.png';
            $voicemail_enabled = $value['voicemail_enabled'] == 'true' ? '<img src=' . $path_true . ' style="height:20px;width:20px;" title="Enable">' : '<img src=' . $path_false . ' style="height:20px;width:20px;" title="Disable">';
            $json_data['rows'][] = array(
                'cell' => array(

                    '<input type="checkbox" name="chkAll" id="' . $value['id'] . '" class="ace chkRefNos" onclick="clickchkbox(' . $value['id'] . ')" value=' . $value['id'] . '><lable class="lbl"></lable>',
                    '<a href="' . base_url() . 'accounts/' . $entity_type . '_fssipdevices_action/edit/' . $value['id'] . '/' . $accountid . '/" rel="facebox_medium" style="cursor:pointer;color:#3b3280" title="Edit"><span title="Edit">' . $value['username'] . '</span></a>',
                    $value['password'],
                    $this->common->get_field_name('name', '`sip_profiles', array(
                        'id' => $value['sip_profile_id']
                    )),
                    $value['effective_caller_id_name'],
                    $value['effective_caller_id_number'],
                    $voicemail_enabled,
                    $this->common->convert_GMT_to('', '', $value['creation_date']),
                    $this->common->convert_GMT_to('', '', $value['last_modified_date']),
                    $this->common->get_status('status', 'sip_devices', $value),
                    // $this->get_action_fssipdevices_buttons($value['id'], $value['accountid'], $entity_type)
                    // Jaimin ASTPPCOM-984 
                        $this->get_advance_buttons_fssipdevices ( $value ['id'] ),
                    // End
                )
            );
        }
        echo json_encode($json_data);
    }

    function get_advance_buttons_fssipdevices($id) {    
        if ($this->session->userdata('logintype') == '0'){  
            $ret_url = '<a href="' . base_url () . 'user/user_fssipdevices_routing/' . $id . '/" class="btn btn-royelblue btn-sm"  rel="" title="Call Routing">&nbsp;<i class="fa fa-info fa-fw"></i></a>&nbsp;';   
        }else{  
            $ret_url = '<a href="' . base_url () . 'siprouting/fssipdevices_routing/' . $id . '/" class="btn btn-royelblue btn-sm"  rel="" title="Call Routing">&nbsp;<i class="fa fa-info fa-fw"></i></a>&nbsp;';  
        }       
        return $ret_url;    
    }

    function get_action_fssipdevices_buttons($id, $accountid, $entity_type = '')
    {
        $permissioninfo = $this->session->userdata('permissioninfo');
        $logintype = $this->session->userdata('logintype');
        $ret_url = '';
        if ($this->session->userdata("logintype") == '0' || $this->session->userdata("logintype") == '3') {
            $ret_url = '<a href="' . base_url() . 'user/user_fssipdevices_action/edit/' . $id . '/' . $accountid . '/" class="btn btn-royelblue btn-sm"  rel="facebox_medium" title="Edit">&nbsp;<i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;';
            $ret_url .= '<a href="' . base_url() . 'user/user_fssipdevices_action/delete/' . $id . '/' . $accountid . '/" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_msg();">&nbsp;<i class="fa fa-trash fa-fw"></i></a>';
        } else {
            if (isset($permissioninfo['freeswitch']['fssipdevices']['EDIT']) and $permissioninfo['freeswitch']['fssipdevices']['EDIT'] == 0 and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4') or $permissioninfo['login_type'] == '-1') {
                $ret_url = '<a href="' . base_url() . 'accounts/' . $entity_type . '_fssipdevices_action/edit/' . $id . '/' . $accountid . '/" class="btn btn-royelblue btn-sm"  rel="facebox_medium" title="Edit">&nbsp;<i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;';
            }
            if (isset($permissioninfo['freeswitch']['fssipdevices']['DELETE']) and $permissioninfo['freeswitch']['fssipdevices']['DELETE'] == 0 and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4') or $permissioninfo['login_type'] == '-1') {
                $ret_url .= '<a href="' . base_url() . 'accounts/' . $entity_type . '_fssipdevices_action/delete/' . $id . '/' . $accountid . '/" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_msg();">&nbsp;<i class="fa fa-trash fa-fw"></i></a>';
            }
        }
        return $ret_url;
    }

    function fssipdevices_delete($id)
    {
        $this->freeswitch_model->delete_freeswith_devices($id);
        $this->session->set_flashdata('astpp_notification', gettext('SIP Device Removed Successfully!'));
        redirect(base_url() . 'freeswitch/fssipdevices/');
        exit();
    }

    function get_action_buttons_fssipdevices($id)
    {
        $permissioninfo = $this->session->userdata('permissioninfo');
        $logintype = $this->session->userdata('logintype');
        $ret_url = '';
        if (isset($permissioninfo['freeswitch']['fssipdevices']['EDIT']) and $permissioninfo['freeswitch']['fssipdevices']['EDIT'] == 0 and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4') or $permissioninfo['login_type'] == '-1') {
            $ret_url = '<a href="' . base_url() . 'freeswitch/fssipdevices_edit/' . $id . '/" class="btn btn-royelblue btn-sm"  rel="facebox_medium" title="Edit">&nbsp;<i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;';
        }
        if (isset($permissioninfo['freeswitch']['fssipdevices']['DELETE']) and $permissioninfo['freeswitch']['fssipdevices']['DELETE'] == 0 and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4') or $permissioninfo['login_type'] == '-1') {
            $ret_url .= '<a href="' . base_url() . 'freeswitch/fssipdevices_delete/' . $id . '/" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_msg();">&nbsp;<i class="fa fa-trash fa-fw"></i></a>';
        }
        return $ret_url;
    }

    function livecall_report()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Live Calls');
        $this->load->view('view_fs_livecall_report', $data);
    }

    function livecall_delete_all_between($beginning, $end, $string)
    {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }
        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
        return str_replace($textToDelete, '', $string);
    }

    function livecall_report_json()
    {
        $command = "api show channels";
        $response = $this->freeswitch_model->reload_live_freeswitch($command);
        $calls = array();
        $calls_final = array();
        $data_header = array();
        $k = 0;
        $data = explode("\n", $response);
        for ($i = 0; $i < count($data) - 2; $i ++) {
            if (trim($data[$i]) != '') {
                if (count($data_header) == 0 || substr($data[$i], 0, 4) == "uuid") {
                    $data_header = explode(",", $data[$i]);
                } else {
                    // HP:Ignore , in bridge line.
                    $bridge_str = "";
                    $string = " " . $data[$i];
                    $ini = strpos($string, '[');
                    if ($ini != 0) {
                        $ini += strlen('[');
                        $len = strpos($string, ']', $ini) - $ini;
                        $bridge_str = substr($string, $ini, $len);
                    }
                    if ($bridge_str != '') {
                        $new_bridge_str = str_replace(',', '--', $bridge_str);
                        $data[$i] = str_replace($bridge_str, $new_bridge_str, $data[$i]);
                    }
                    // HP:END.
                    $data_call = explode(",", $data[$i]);
                    for ($j = 0; $j < count($data_call); $j ++) {
                        $calls[$k][@$data_header[$j]] = @$data_call[$j];
                        $calls_final[@$calls[$k]['uuid']] = @$calls[$k];
                    }
                    $k ++;
                }
            }
        }
        $json_data = array();
        $count = 0;

        // Defined color
        $status_color = array(
            "Answered" => "#28A745",
            "Connecting" => "#E40B80",
            "Unknown" => "#DC3545"
        );
        $org_color = "#D6D8D9";
        $term_color = "#3B3280";

        foreach ($calls as $key => $value) {
            if (isset($value['state']) && ($value['state'] == 'CS_EXCHANGE_MEDIA' || $value['state'] == 'CS_CONSUME_MEDIA')) {
                $calls[$i]['presence_data'] = @$calls_final[$value['call_uuid']]['presence_data'];
                $livecall_data = explode("|||", $calls[$i]['presence_data']);

                $org_data = explode("//", @$livecall_data[2]);
                $term_data = explode("//", @$livecall_data[3]);

                $trunk = $term_data[4];
                $trunk = explode("=",$trunk);
                $gateway = $this->common->get_field_name('name', 'trunks', array("id" => $trunk[1]));

                $value['callstate'] = ($value['state'] == 'CS_EXCHANGE_MEDIA') ? "Answered" : (($value['state'] == 'CS_CONSUME_MEDIA') ? "Connecting" : "Unknown");
                $amount = isset($term_data[3]) ? $term_data[3] : 0;
                $json_data['rows'][] = array(
                    'cell' => array(
                        "<a href='" . base_url() . "freeswitch/livecall_hangup?uuid=" . $value['uuid'] . "' class='btn btn-warning'> Hang Up </button>",
                        // Ashish ASTPPCOM-752
                        // ASTPPCOM-891 Ashish start
                        $this->common->convert_GMT_to('', '', $value['created'],'','livecall'),
                        // ASTPPCOM-891 Ashish end
                        // Ashish 752 End
                        $value['cid_name'] . " " . $value['cid_num'],
                        $value['ip_addr'],
                        @$livecall_data[1],
                        "<span style='color:" . $org_color . "'><b>" . @rtrim(ltrim($org_data[0], "^"), ".* ") . "</b></span>",
                        "<span style='color:" . $org_color . "'><b>" . @$org_data[1] . "</b></span>",
                        "<span style='color:" . $org_color . "'><b>" . @$org_data[2] . "</b></span>",
                        "<span style='color:" . $term_color . "'><b>" . @$gateway . "</b></span>",
                        "<span style='color:" . $term_color . "'><b>" . @rtrim(ltrim($term_data[1], " ^"), ".* ") . "</b></span>",
                        "<span style='color:" . $term_color . "'><b>" . @$term_data[2] . "</b></span>",
                        isset($term_data[3]) ? "<span style='color:" . $term_color . "'><b>" . $this->common_model->calculate_currency_customer(@trim($amount)) . "</b></span>" : "",
                        $value['dest'],
                        date("H:i:s", strtotime(date("Y-m-d H:i:s")) - $value['created_epoch']),
                        @$livecall_data[4],
                        "<span style='color:#fff;background-color:" . $status_color[$value['callstate']] . "'><b>" . $value['callstate'] . "</b></span>",
                        $value['read_codec'] . " / " . $value['write_codec']
                    )
                );
                $count ++;
            } else {
                unset($calls[$i]);
            }
        }
        $json_data['page'] = 1;
        $json_data['total'] = $count;
        echo json_encode($json_data);
    }

    function livecall_hangup()
    {

        $get_uuid = $_GET['uuid'];	
	$value = strip_slashes ( trim ( $get_uuid ) );
	$value = preg_replace ( '#<script.*</script>#is', '', $value );
	$value = strip_tags ( filter_var ( $value, FILTER_SANITIZE_STRING ) );
	$value=$this->security->xss_clean ( $value );
	$value_array = explode("-",$value);
	$segment_1=strlen($value_array['1']);
	$segment_2=strlen($value_array['2']);
	$segment_3=strlen($value_array['3']);
	$uuid = "";
	if(($segment_1 == 4) && ($segment_2 == 4) && ($segment_3 == 4)){
		$segment_0 = substr($value_array[0], -8);
		$segment_4 = substr($value_array[4], 0, 12);
		if(ctype_xdigit($segment_0) && ctype_xdigit($value_array['1']) && ctype_xdigit($value_array['2']) && ctype_xdigit($value_array['3']) && ctype_xdigit($segment_4)) {
			$uuid=$segment_0.'-'.$value_array['1'].'-'.$value_array['2'].'-'.$value_array['3'].'-'.$segment_4;
		}
	}
	if(!empty($uuid)){
		$command = "api uuid_kill " . $uuid;
		$response = $this->freeswitch_model->reload_live_freeswitch($command);
	}
        redirect(base_url() . 'freeswitch/livecall_report/');
    }

    function fsgateway()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Gateways');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->freeswitch_form->build_fsgateway_list_for_admin();
        $data["grid_buttons"] = $this->freeswitch_form->build_fdgateway_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->freeswitch_form->get_gateway_search_form());
        $this->load->view('view_fsgateway_list', $data);
    }

    function fsgateway_json()
    {
        $json_data = array();
        // ASTPPCOM-943 Kinjal Start
        $new_array = array();
        $command = "api sofia xmlstatus gateway"; 
        $response = $this->freeswitch_model->reload_freeswitch($command,$id);
        if($response != "" ){
           $response_arr = json_decode(json_encode((array) simplexml_load_string(trim($response))),1);
           if(array_key_exists("0",$response_arr["gateway"]) && $response_arr != ""){
                foreach ($response_arr['gateway'] as $key => $value) {
                    if($value['state'] != 'FAIL_WAIT' && $value['state'] != 'TRYING' && $value['state'] !='FAILED' && $value['state'] !='UNREGED'){
                        array_push($new_array, $value['name']);
                    }
                }
           }else{
                array_push($new_array, $response_arr['gateway']['name']);
           }
        }
        // ASTPPCOM-943 Kinjal END

        $count_all = $this->freeswitch_model->get_gateway_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $gateway_data = array();
        $query = $this->freeswitch_model->get_gateway_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $gateway_result = array();
        // Kinjal ASTPPCOM-943 Start
        $live_status = "<i class='fa fa-circle' style='color: #FF3131'></i>";
        // Kinjal ASTPPCOM-943 END
        if ($query->num_rows() > 0) {
            $query = $query->result_array();
            foreach ($query as $key => $query_value) {
                $gateway_data = array();
                $tmp = null;
                foreach ($query_value as $gateway_key => $gateway_val) {
                    if ($gateway_key != "gateway_data") {
                        // Kinjal ASTPPCOM-943 Start
                        if($gateway_key == "name"){
                            if (in_array($query_value['name'],$new_array)) {
                                $live_status = "<i class='fa fa-circle hello' style='color: #00FF00'></i>";
                                $element = "<span style='margin-right:8px' id='live_status_".$gateway_val."'>".$live_status."</span>";
                            }else{
                                $live_status = "<i class='fa fa-circle demo' style='color: #FF3131'></i>";
                                $element = "<span style='margin-right:8px' id='live_status_".$gateway_val."'>".$live_status."</span>";
                            }
                            $gateway_data[$gateway_key] = $element.$gateway_val;
                        }else{
                            $gateway_data[$gateway_key] = $gateway_val;
                        }
                        // Kinjal ASTPPCOM-943 END
                    } else {
                        $tmp = (array) json_decode($gateway_val);
                    }
                }
                $gateway_result[$key] = array_merge($gateway_data, $tmp);
            }
        }
        $grid_fields = json_decode($this->freeswitch_form->build_fsgateway_list_for_admin());
        $json_data['rows'] = $this->form->build_json_grid($gateway_result, $grid_fields);
        echo json_encode($json_data);
    }

    function fsgateway_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Create Gateway');
        $data['form'] = $this->form->build_form($this->freeswitch_form->get_gateway_form_fields(), '');
        $this->load->view('view_fsgateway_add', $data);
    }

    function fsgateway_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Gateway');
        $where = array(
            'id' => $edit_id
        );
        $query = $this->db_model->getSelect("*", "gateways", $where);
        $query = $query->result_array();
        $gateway_result = array();
        foreach ($query as $key => $query_value) {
            foreach ($query_value as $gateway_key => $gatewau_val) {
                $gateway_data["status"] = isset($query_value["status"]) ? $query_value["status"] : "";
                if ($gateway_key != "gateway_data") {
                    $gateway_data[$gateway_key] = $gatewau_val;
                } else if ($gateway_key == "status") {
                    $gateway_data[$gateway_key] = $gatewau_val;
                } else if ($gateway_key == "dialplan_variable") {
                    $gateway_data[$gateway_key] = $gatewau_val;
                } else {
                    $tmp = (array) json_decode($gatewau_val);
                    $gateway_result = array_merge($gateway_data, $tmp);
                }
            }
        }
        if (! empty($gateway_data['dialplan_variable']) && $gateway_data['dialplan_variable'] != '') {
            $gateway_result['dialplan_variable'] = $gateway_data['dialplan_variable'];
        } else {
            $gateway_result['dialplan_variable'] = '';
        }
        $data['form'] = $this->form->build_form($this->freeswitch_form->get_gateway_form_fields(), $gateway_result);
        $this->load->view('view_fsgateway_add', $data);
    }

    function fsgateway_save()
    {
        $gateway_data = $this->input->post();
        $data['form'] = $this->form->build_form($this->freeswitch_form->get_gateway_form_fields(), $gateway_data);
        $insert_arr = array();
        $gateway_arr = array();
        $insert_arr['dialplan_variable'] = "";
        // Kinjal ASTPPCOM-943 Start
        $mod_sofia = "api reload mod_sofia";
        // Kinjal ASTPPCOM-943 END
        foreach ($gateway_data as $key => $gateway_value) {
            if ($gateway_value != "") {
                if ($key == "sip_profile_id") {
                    $insert_arr['sip_profile_id'] = $gateway_data["sip_profile_id"];
                } else if ($key == "name") {
                    $insert_arr['name'] = $gateway_data["name"];
                } else if ($key == "sip_profile_id") {
                    $insert_arr['sip_profile_id'] = $gateway_data["sip_profile_id"];
                } else if ($key == "dialplan_variable") {
                    $insert_arr['dialplan_variable'] = $gateway_data["dialplan_variable"];
                } else if ($key == "status") {
                    $insert_arr[$key] = $gateway_data["status"];
                } else {
                    if ($key == "proxy" || $key == "outbound-proxy" || $key == "register-proxy") {
                        $gateway_value = str_replace(" ", "", $gateway_value);
                        $gateway_arr[$key] = $gateway_value;
                    } else {
                        if ($key != "id") {
                            $gateway_arr[$key] = $gateway_value;
                        }
                    }
                }
            }
        }

        $insert_arr["gateway_data"] = json_encode($gateway_arr);

        if ($gateway_data['id'] != '') {
            $data['page_title'] = gettext('Edit Gateway Details');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {

                if (isset($gateway_data['name'])) {
                    $query = $this->freeswitch_model->check_unique_gateway_name('edit', 'name', $gateway_data['name']);
                    $result = $query->result_array();
                    if (count($result) > 0) {
                        if ($result[0]['id'] != $gateway_data['id'] && $result[0]['name'] == $gateway_data['name']) {
                            echo json_encode(array(
                                "name_error" => gettext("Gateways already exist in system.")
                            ));
                            exit();
                        }
                    }
                }

                if (preg_match('/\s/', $insert_arr['name'])) {
                    echo json_encode(array(
                        "name_error" => gettext("Gateway name must not have any space.")
                    ));
                    exit();
                }

                $insert_arr['last_modified_date'] = gmdate('Y-m-d H:i:s');
                $this->db->select('name,sip_profile_id');
                $old_gateway_info = (array) $this->db->get_where('gateways', array(
                    'id' => $gateway_data['id']
                ))->first_row();

                $update = $this->db->update("gateways", $insert_arr, array(
                    'id' => $gateway_data['id']
                ));
                if ($update) {
                    $this->db->select('name,sip_ip');
                    $old_sip_profile_info = (array) $this->db->get_where('sip_profiles', array(
                        'id' => $old_gateway_info['sip_profile_id']
                    ))->first_row();
                    $sip_profile_info = (array) $this->db->get_where('sip_profiles', array(
                        'id' => $old_gateway_info['sip_profile_id']
                    ))->first_row();
                    $cmd = "api sofia profile " . $old_sip_profile_info['name'] . " killgw '" . $old_gateway_info['name'] . "' ";
                    $sip_ip = $sip_profile_info['id'];
                    $this->freeswitch_model->reload_freeswitch($cmd, $sip_ip);
                    $cmd2 = "api sofia profile " . $sip_profile_info['name'] . " rescan reloadacl reloadxml";
                    $this->freeswitch_model->reload_freeswitch($cmd2, $sip_ip);
                    // Kinjal ASTPPCOM-943 Start
                    $this->freeswitch_model->reload_freeswitch($mod_sofia);
                    // Kinjal ASTPPCOM-943 END
                }
                // Kinjal ASTPPCOM-943 Start
                sleep(1);
                // Kinjal ASTPPCOM-943 END
                echo json_encode(array(
                    "SUCCESS" => ucfirst($insert_arr['name']) . gettext("Gateway Updated Successfully!")
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Gateway Details');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {

                if (isset($gateway_data['name'])) {
                    $query = $this->freeswitch_model->check_unique_gateway_name('add', 'name', $gateway_data['name']);
                    if ($query > 0) {
                        echo json_encode(array(
                            "name_error" => gettext("Gateways already exist in system.")
                        ));
                        exit();
                    }
                }

                if (preg_match('/\s/', $insert_arr['name'])) {
                    echo json_encode(array(
                        "name_error" => gettext("Gateway name must not have any space.")
                    ));
                    exit();
                }
                $insert_arr['created_date'] = gmdate('Y-m-d H:i:s');
                $insert_arr['last_modified_date'] = gmdate('Y-m-d H:i:s');
                $insert = $this->db->insert("gateways", $insert_arr);
                if ($insert) {
                    $profile_name = $this->common->get_field_name('name', 'sip_profiles', $insert_arr['sip_profile_id']);
                    $sip_ip = $this->common->get_field_name('sip_ip', 'sip_profiles', $insert_arr['sip_profile_id']);
                    $cmd = "api sofia profile " . $profile_name . " rescan reloadacl reloadxml";
                    $this->freeswitch_model->reload_freeswitch($cmd, $sip_ip);
                }
                echo json_encode(array(
                    "SUCCESS" => ucfirst($insert_arr['name']) . gettext("Gateway Added Successfully!")
                ));
                exit();
            }
        }
    }

    function fsgateway_delete($gateway_id)
    {
        $this->db->select('name,sip_profile_id');
        $gateway_info = (array) $this->db->get_where('gateways', array(
            'id' => $gateway_id
        ))->first_row();
        $this->db->select('name,sip_ip');
        $profile_info = (array) $this->db->get_where('sip_profiles', array(
            'id' => $gateway_info['sip_profile_id']
        ))->first_row();
        $cmd = "api sofia profile " . $profile_info['name'] . " killgw " . $gateway_info['name'] . " rescan reloadacl reloadxml";
        $this->db_model->delete("gateways", array(
            "id" => $gateway_id
        ));
        $this->freeswitch_model->reload_freeswitch($cmd, $sip_ip);
        $this->session->set_flashdata('astpp_notification', gettext('Gateway Removed Successfully!'));
        redirect(base_url() . 'freeswitch/fsgateway/');
    }

    function fsgateway_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        $this->db->select('group_concat(sip_profile_id) as sip_profile_id');
        $this->db->from('gateways');
        $sip_profile_ids = (array) $this->db->get()->first_row();
        $sip_profile_where = "id IN (" . $sip_profile_ids['sip_profile_id'] . ")";
        $this->db->select('id,name,sip_ip');
        $this->db->where($sip_profile_where);
        $this->db->from('sip_profiles');
        $result_array = $this->db->get()->result_array();
        $sip_profile_arr = array();
        foreach ($result_array as $key => $value) {
            $sip_profile_arr[$value['id']] = array(
                "name" => $value['name'],
                "sip_ip" => $value['sip_ip']
            );
        }
        $this->db->select('id,name,sip_profile_id');
        $this->db->where($where);
        $this->db->from('gateways');
        $gateway_info = $this->db->get()->result_array();
        foreach ($gateway_info as $key => $value) {
            $cmd = "api sofia profile " . $sip_profile_arr[$value['sip_profile_id']]['name'] . " killgw " . $value['name'] . " rescan reloadacl reloadxml";
            $this->freeswitch_model->reload_freeswitch($cmd, $sip_profile_arr[$value['sip_profile_id']]['sip_ip']);
        }
        $this->db->where($where);
        echo $this->db->delete("gateways");
    }

    function fssipprofile()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('SIP Profiles');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->freeswitch_form->build_fssipprofile_list_for_admin();
        $data["grid_buttons"] = $this->freeswitch_form->build_fssipprofile_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->freeswitch_form->get_sipprofile_search_form());
        $data['button_name'] = "Add Setting";
        $this->load->view('view_fssipprofile_list', $data);
    }

    function fssipprofile_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("sip_profiles");
    }

    function fssipprofile_json()
    {
        $json_data = array();
        $count_all = $this->freeswitch_model->get_sipprofile_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $gateway_data = array();
        $query = $this->freeswitch_model->get_sipprofile_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->freeswitch_form->build_fssipprofile_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function fssipprofile_params_json($edited_id)
    {
        $json_data = array();
        $gateway_data = array();
        $where = array(
            'id' => $edited_id
        );
        $query = $this->db_model->getSelect("*", "sip_profiles", $where);
        $query = $query->result_array();
        $gateway_result = array();
        $i = 0;
        foreach ($query as $key => $query_value) {
            foreach ($query_value as $gateway_key => $gatewau_val) {
                if ($gateway_key != 'id' && $gateway_key != 'name' && $gateway_key != 'sip_ip' && $gateway_key != 'sip_port') {
                    if ($gateway_key != "profile_data") {
                        $gateway_data[$gateway_key] = $gatewau_val;
                    } else {
                        $tmp = (array) json_decode($gatewau_val);
                        $gateway_result = array_merge($gateway_data, $tmp);
                    }
                }
            }
        }
        $paging_data = $this->form->load_grid_config(count($gateway_result), $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        foreach ($gateway_result as $key => $value) {
            $json_data['rows'][] = array(
                'cell' => array(
                    $key,
                    $value,
                    array(
                        '<a href="/freeswitch/fssipprofile_edit/' . $edited_id . '/edit/' . $key . '/" class="btn btn-royelblue btn-sm"  title="Edit">&nbsp;<i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;<a href="/freeswitch/fssipprofile_delete_params/' . $edited_id . '/' . $key . '/" class="btn btn-royelblue btn-sm" title="Delete" onClick="return get_alert_msg();">&nbsp;<i class="fa fa-trash fa-fw"></i></a>'
                    )
                )
            );
        }
        echo json_encode($json_data);
    }

    function fssipprofile_action($button_name, $id)
    {
        $where = array(
            'id' => $id
        );
        $query = $this->db_model->getSelect("*", "sip_profiles", $where);
        $query = $query->result_array();
        $where = array(
            'sip_profile_id' => $id
        );
        if ($button_name == "start") {
            $cmd = "api sofia profile " . trim($query[0]['name']) . " start";
        } elseif ($button_name == "stop") {
            $cmd = "api sofia profile " . trim($query[0]['name']) . " stop"; 
        } elseif ($button_name == "reload") {
            $cmd = "api reload mod_sofia";
        } elseif ($button_name == "rescan") {
            $cmd = "api sofia profile " . trim($query[0]['name']) . " rescan";
        }
        $this->freeswitch_model->reload_freeswitch($cmd);
        redirect($_SERVER['HTTP_REFERER']);
    }

    function fssipprofile_add($add = '')
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Create SIP Profile');
        $sipprofile_data = $this->input->post();
        $sipprofile_data['status'] = $sipprofile_data['sipstatus'];
        $data['button_name'] = "Add Setting";
        if ($add == 'add') {
            if (isset($sipprofile_data['name']) && $sipprofile_data['name'] != "") {
                $query = $this->common_model->check_unique_data('add', 'name', $sipprofile_data['name'], 'sip_profiles');
                if ($query > 0) {
                    $this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|is_unique[sip_profiles.name]');
                }
            }
            if ($query > 0) {
                if ($this->form_validation->run() == FALSE) {
                    $data['details']['name'] = isset($sipprofile_data['name']) ? $sipprofile_data['name'] : $sipprofile_data['name'];
                    $data['details']['sip_ip'] = isset($sipprofile_data['sip_ip']) ? $sipprofile_data['sip_ip'] : $sipprofile_data['sip_ip'];
                    $data['details']['sip_port'] = isset($sipprofile_data['sip_port']) ? $sipprofile_data['sip_port'] : $sipprofile_data['sip_port'];
                    $data['details']['sipstatus'] = isset($sipprofile_data['sipstatus']) ? $sipprofile_data['sipstatus'] : $sipprofile_data['sipstatus'];
                    $data['validation_errors'] = validation_errors();
                }
            } else {

                unset($sipprofile_data['action']);
                unset($sipprofile_data['sipstatus']);
                $insert_data = $sipprofile_data;
                if ($sipprofile_data['name'] == '' || $sipprofile_data['sip_ip'] == '' || $sipprofile_data['sip_port'] == '') {
                    $this->session->set_flashdata('astpp_notification', gettext('Please enter All profile value!'));
                    redirect(base_url() . 'freeswitch/fssipprofile_add/');
                    exit();
                }
                if (preg_match('/\s/', $sipprofile_data['name'])) {
                    $this->session->set_flashdata('astpp_notification', gettext('SIP Profile name must not have any space!'));
                    redirect(base_url() . 'freeswitch/fssipprofile_add/');
                    exit();
                }

                $sipprofile_data['id'] = '';
                $check_authentication = $this->freeswitch_model->profile_authentication($sipprofile_data);
                if ($check_authentication->num_rows() == 0) {

                    $sipprofile_data['created_date'] = gmdate('Y-m-d H:i:s');

                    $sipprofile_data['profile_data'] = $this->common->sip_profile_date();

                    $sipprofile_data['name'] = trim($sipprofile_data['name']);
                    $insert = $this->db->insert("sip_profiles", $sipprofile_data);
                } else {
                    $this->session->set_flashdata('astpp_notification', gettext('Duplicate SIP IP OR Port found it must be unique!'));
                    redirect(base_url() . 'freeswitch/fssipprofile_add/');
                }
                $this->session->set_flashdata('astpp_errormsg', gettext('SIP Profile Added Successfully!'));
                redirect(base_url() . 'freeswitch/fssipprofile/');
            }
        }

        if ($add == 'edit') {

            $where = array(
                'id <>' => $sipprofile_data["id"],
                "name" => $sipprofile_data["name"]
            );

            $query = $this->db_model->getSelect("*", "sip_profiles", $where);
            $profile_data_res = $query->num_rows();

            if ($profile_data_res > 0) {
                $this->session->set_flashdata('astpp_notification', gettext('Name must be unique!'));
                redirect(base_url() . 'freeswitch/fssipprofile/');
            }

            $check_authentication = $this->freeswitch_model->profile_authentication($sipprofile_data);

            unset($sipprofile_data['action']);
            unset($sipprofile_data['sipstatus']);
            $insert_arr = $sipprofile_data;
            if ($check_authentication->num_rows() == 0) {
                $insert_arr['last_modified_date'] = gmdate("Y-m-d H:i:s");
                $insert_arr['name'] = trim($insert_arr['name']);
                $update = $this->db->update("sip_profiles", $insert_arr, array(
                    'id' => $sipprofile_data['id']
                ));
                $this->session->set_flashdata('astpp_errormsg', $sipprofile_data['name']." ".gettext('SIP Profile Updated Successfully!'));

                redirect(base_url() . 'freeswitch/fssipprofile/');
                exit();
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('Duplicate SIP IP OR Port found it must be unique!'));
                redirect(base_url() . 'freeswitch/fssipprofile/');
            }
            redirect(base_url() . 'freeswitch/fssipprofile/');
        }

        $this->load->view('view_fssipprofile_add', $data);
    }

    function fssipprofile_edit($edit_id = '', $type = '', $name_prams = '')
    {
        $data['page_title'] = gettext('Edit SIP Profile');
        $sipprofile_data = $this->input->post();
        if (! $edit_id) {
            $edit_id = $sipprofile_data['id'];
        }
        $where = array(
            'id' => $edit_id
        );
        $query = $this->db_model->getSelect("*", "sip_profiles", $where);
        $query = $query->result_array();
        $gateway_result = array();
        foreach ($query as $key => $query_value) {
            foreach ($query_value as $gateway_key => $gatewau_val) {
                if ($gateway_key != "profile_data") {
                    $gateway_data[$gateway_key] = $gatewau_val;
                } else {
                    $tmp = (array) json_decode($gatewau_val);
                    $gateway_result = array_merge($gateway_data, $tmp);
                }
            }
        }

        $data['grid_fields'] = $this->freeswitch_form->build_fssipprofile_params_list_for_admin();
        $data['edited_id'] = $edit_id;
        $data['sip_name'] = $query[0]['name'];
        $data['status'] = $query[0]['status'];
        $data['sip_ip'] = $query[0]['sip_ip'];
        $data['sip_port'] = $query[0]['sip_port'];
        $data['id'] = $query[0]['id'];

        $data['button_name'] = "Add Setting";
        if ($type == 'edit' || isset($sipprofile_data['type']) && $sipprofile_data['type'] == 'save') {
            if ($type == 'edit') {
                $data['params_name'] = $name_prams;
                $data['params_status'] = 0;
                if ($gateway_result[$name_prams] == "true" || $gateway_result[$name_prams] == "false") {
                    $data['params_status'] = 1;
                }
                $data['params_value'] = $gateway_result[$name_prams];
                $data['button_name'] = "Update Setting";
            }
            if (isset($sipprofile_data['type']) && $sipprofile_data['type'] == 'save') {
                $sipprofile_data = $this->input->post();
                $sipprofile_data['params_name'] = trim($sipprofile_data['params_name']);
                $sipprofile_data['params_value'] = trim($sipprofile_data['params_value']);
                $tmp[$sipprofile_data['params_name']] = $sipprofile_data['params_value'];
                $final_data = json_encode($tmp);
                $insert_arr["profile_data"] = json_encode($tmp);
                $update = $this->db->update("sip_profiles", $insert_arr, array(
                    'id' => $edit_id
                ));
                if ($sipprofile_data['type_settings'] == "add_setting") {
                    $this->session->set_flashdata('astpp_errormsg', $data['sip_name'].' '.gettext('SIP Setting Added Successfully!'));
                } else {
                    $this->session->set_flashdata('astpp_errormsg', $data['sip_name'].' '.gettext('SIP Setting Updated Successfully!'));
                }
                redirect(base_url() . 'freeswitch/fssipprofile_edit/' . $sipprofile_data['id']);
                exit();
            }
        }
        $this->load->view('view_fssipprofile_edit', $data);
    }

    function fssipprofile_save($id)
    {
        $sipprofile_data = $this->input->post();
        $insert_arr = array();
        $sipprofile_arr = array();
        $where = array(
            'id' => $id
        );
        $query = $this->db_model->getSelect("*", "sip_profiles", $where);
        $query = $query->result_array();
        $gateway_result = array();
        foreach ($query as $key => $query_value) {
            foreach ($query_value as $gateway_key => $gatewau_val) {
                if ($gateway_key != "profile_data") {
                    $gateway_data[$gateway_key] = $gatewau_val;
                } else {
                    $tmp = (array) json_decode($gatewau_val);
                    $gateway_result = array_merge($gateway_data, $tmp);
                }
            }
        }
        $tmp[$sipprofile_data['params_name']] = $sipprofile_data['params_value'];
        $final_data = json_encode($tmp);
        $insert_arr["profile_data"] = json_encode($tmp);
        $update = $this->db->update("sip_profiles", $insert_arr, array(
            'id' => $id
        ));
        $this->load->view('view_fssipprofile_edit', $data);
    }

    function fssipprofile_delete_params($id, $name)
    {
        $where = array(
            'id' => $id
        );
        $query = $this->db_model->getSelect("*", "sip_profiles", $where);
        $query = $query->result_array();
        $gateway_result = array();
        foreach ($query as $key => $query_value) {
            foreach ($query_value as $gateway_key => $gatewau_val) {
                if ($gateway_key != "profile_data") {
                    $gateway_data[$gateway_key] = $gatewau_val;
                } else {
                    $tmp = (array) json_decode($gatewau_val);
                    $gateway_result = array_merge($gateway_data, $tmp);
                }
            }
        }
        if (isset($tmp[$name])) {
            unset($tmp[$name]);
        }
        $insert_arr["profile_data"] = json_encode($tmp);
        $update = $this->db->update("sip_profiles", $insert_arr, array(
            'id' => $id
        ));
        $this->session->set_flashdata('astpp_notification', $name.' '. gettext('SIP Setting Removed Successfully!'));
        redirect(base_url() . 'freeswitch/fssipprofile_edit/' . $id);
    }

    function fssipprofile_delete($profile_id)
    {
        $profile_name = $this->common->get_field_name('name', 'sip_profiles', $profile_id);
        $sip_ip = $this->common->get_field_name('sip_ip', 'sip_profiles', $profile_id);
        $delete = $this->db_model->delete("sip_profiles", array(
            "id" => $profile_id
        ));
        $this->session->set_flashdata('astpp_notification', gettext('SIP Profile Removed Successfully!'));
        redirect(base_url() . 'freeswitch/fssipprofile/');
    }

   

    function fssipprofile_edit_validation($id, $name)
    {
        $sip_profile_data = $this->common->get_field_name('profile_data', 'sip_profiles', $id);
        $final_data = json_decode($sip_profile_data, true);
        foreach ($final_data as $key => $value) {
            if ($key == $name) {
                echo 1;
                return true;
            }
        }
        echo 0;
        return true;
    }

    function fssipdevices_reseller_customer()
    {
        $reseller_id = $_POST['reseller_id'];
        $query = $this->db_model->getSelect("*", "accounts", array(
            'reseller_id' => $reseller_id,
            'type' => 0,
            'deleted' => 0
        ));
        $result_array = $query->result_array();
        echo json_encode($result_array);
    }

}

?>