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
class Automated_report extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library("automated_report_form");
        $this->load->library('astpp/form', 'automated_report_form');
        $this->load->library('astpp/permission');
        $this->load->library('ASTPP_Sms');
        $this->load->model('automated_report_model');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
        }

        function automated_report_list(){
            $data['username'] = $this->session->userdata('user_name');
            $data['page_title'] = gettext('Automated Report');
            $data['search_flag'] = true;
            $this->session->set_userdata('advance_search', 0);
            // $customer_session = $this->session->userdata('customer_list_search');
            $data["grid_buttons"] = $this->automated_report_form->build_grid_automated_report_buttons();
            $data['grid_fields'] = $this->automated_report_form->build_automated_report_list_for_admin();
            $data['form_search'] = $this->form->build_serach_form($this->automated_report_form->get_search_automated_form());
            // $data['form_search'] = $this->form->build_serach_form($this->automated_report_form->get_customer_report_form());   
            $this->load->view('view_automated_report_list', $data);
        }
    
        function automated_report_list_json() {
            $json_data = array();
            $count_all = $this->automated_report_model->getcustomer_automated_report_list(false);
            $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
            $json_data = $paging_data["json_paging"];
            $query = $this->automated_report_model->getcustomer_automated_report_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
            // echo $this->db->last_query(); die;
            $grid_fields = json_decode($this->automated_report_form->build_automated_report_list_for_admin());
            $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
            echo json_encode($json_data);
        }
        function automated_report_edit($edit_id = '')
        {
            if($edit_id != ''){
                $where = array(
                    'id' => $edit_id
                );
            }
            $data['page_title'] = gettext('Edit Automated Report');
            $account = $this->db_model->getSelect("*", "automated_reports", $where);
            foreach ($account->result_array() as $key => $value) {
                $edit_data = $value;
            }
            $data['form'] = $this->form->build_form($this->automated_report_form->get_automated_report_form_fields($edit_id), $edit_data);
            $data['interval_fitler_on'] = $edit_data['interval_fitler_on'];
            $data['id'] = $edit_data['id'];
            $data['object_name'] = $edit_data['module'];
            $data['select_values'] = $edit_data['select_values'];
            $this->load->view('view_automated_report_edit', $data);
    }
   
    function automated_report_fields(){
        $object_name =$_POST['object_name'];
        $this->load->module($object_name);
        $function = 'subscription_'.$object_name.'_fields';
        $object_fields = $this->$object_name->$function();
        if($_POST['select_values'] != ''){
            $selected_values = explode(",",$_POST['select_values']);
            $select="selected=selected";
            echo '<select>';
            $i  =  0;
            foreach ($object_fields as $key => $value) {
              $newVal = $selected_values[$i];
              if($newVal == $key){
                    echo "<option value=" . $key . " $select > " . $value . "</option>";
                }else{
                    echo "<option value=" . $key . ">" . $value . "</option>";
                }
                $i++;
            }
            echo '</select>';
        }else{
            if($object_name != ''){ 
                echo '<select>';
                foreach ($object_fields as $key => $value) {
                    echo "<option value=" . $key . ">" . $value . "</option>";
                }
                echo '</select>';
            }else{
                echo "<select><option>--</option></select>";
            }
        }
    }
    function automated_report_add($object_name ='')
    {
        $data['object_name'] = $object_name;
        $data['page_title'] = gettext('Automated Report');
        $data['module_name'] = $object_name;
        $data['form'] = $this->form->build_form($this->automated_report_form->get_automated_report_form_fields(),'');
        $this->load->view('view_automated_report_add', $data);
    }
        function automated_report_save(){
            $add_array = $this->input->post();
            $data['form'] = $this->form->build_form($this->automated_report_form->get_automated_report_form_fields($add_array['id']), $add_array);
            if(isset($add_array['select_values']) && $add_array['select_values'] != ''){
                $add_array['select_values'] = astpp_implode(',',$add_array['select_values']);
            }
            if ($add_array['id'] != '') {
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
                    $add_array['last_modified_date'] = gmdate('Y-m-d H:i:s');
                    $this->automated_report_model->edit_autommated_report($add_array, $add_array['id']);
                    echo json_encode(array(
                        "SUCCESS" => $add_array["report_name"].' '.gettext('updated successfully!')
                    ));
                    exit();
                }
            }else{
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
              
                    $add_array['creation_date'] = gmdate('Y-m-d H:i:s'); 
                    $add_array['next_execution_date'] = $add_array['creation_date'];
                    $add_array['next_execution_date'] = $this->common->get_automatedreport_date($add_array);
                    $encoded_search_array = json_decode($this->session->userdata('reports_list_automated'),true);
                    $add_array['filters_where'] = $this->session->userdata('reports_list_automated');
                    $add_array['module'] = $encoded_search_array['reports_key_automated']['module'];
                    $add_array['select_names'] = 'Caller ID,Called Number,SIP User,Code,Destination,Duration,Debit,Cost,Disposition,Account,Country,Trunk,Rate Group,Call Type,Direction,Recording';
                    $add_array['select_values'] = 'callerid,callednum,sip_user,pattern,notes,billseconds,debit,cost,disposition,accountid,country_id,trunk_id,pricelist_id,calltype,call_direction,is_recording';
                    $add_array['last_modified_date'] = gmdate('Y-m-d H:i:s'); 
                    $this->automated_report_model->add_automated_report($add_array);
                    echo json_encode(array(
                        "SUCCESS" => $add_array["report_name"].' '.gettext('added successfully!')
                    ));
                    exit();
                }
            }
        }
        function automated_report_delete_multiple()
        {
             //sandip roles and permission
            $ids = $this->input->post("selected_ids", true);
            $where = "id IN ($ids)";
            $this->db->delete("automated_reports", $where);
            echo TRUE;
        }
        function automated_report_list_search() {
            $ajax_search = $this->input->post('ajax_search', 0);
            if ($this->input->post('advance_search', TRUE) == 1) {
                $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
                $action = $this->input->post();
                $this->session->set_userdata('automated_report_search', $action);
            }
            if (@$ajax_search != 1) {
                redirect(base_url() . 'automated_report/automated_report_list/');
            }
        }

        function automated_report_list_clearsearchfilter()
        {
            $this->session->set_userdata('advance_search', 0);
            $this->session->set_userdata('automated_report_search', "");
        }
    
}

?>
 
