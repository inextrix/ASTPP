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
class Pbx_Feature extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->model ( "db_model" );
		$this->load->library ( "astpp/common" );
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        //$this->load->library("queue_form");
        //$this->load->library('astpp/form');
        //$this->load->library('astpp/pbx_feature');
        //$this->load->model('queue_model');
	    $this->load->model('common_model');
	    $this->load->model('Astpp_common');
        
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
		//$this->load->model ( "db_model" );
		//$this->load->library ( "astpp/email_lib" );
	}
	//Hiral

    // ASTPPCOM-947
    function pbx_destination_change($call_type_code="", $did_id="", $extensions=""){
        $accountinfo=$this->session->userdata('accountinfo');
        $accountid = $this->common->get_field_name("accountid","dids",array("id"=>$did_id));
        if(astpp_eq0($accountid)){
            $whr=array();
        }
        else{
            $whr=array("accountid"=>$accountid);    
        }

        if($call_type_code==7)
        {
            $ringgroup = $this->db_model->getSelect( '*' ,'pbx_ringgroup',$whr);
            if ($ringgroup->num_rows() > 0) {
                $ringgroup_data =$ringgroup->result_array();
                $ringgroup_arr = array();
                foreach ($ringgroup_data as $value) {
                    $ringgroup_arr[$value['id']] =  $value['name'];
                }
                $ringgroup_info = array("name" => "extensions" ,"id" => "extensions_id", "class" => " extensions_set");
                echo form_dropdown($ringgroup_info, $ringgroup_arr,$extensions);
            }
            else{
                echo '<select class="col-md-12 form-control form-control-lg selectpicker extensions_set" id="extensions_id" name="extensions"><option>--</option></select>';
                } 
        }
    }
    //End
   
	function pbx_destination_change_for_did($call_type_code="", $did_id="", $extensions=""){
        //echo $extensions;exit;
        $accountinfo=$this->session->userdata('accountinfo');
        $accountid =  $accountinfo['id'];//$this->common->get_field_name("accountid","dids",array("id"=>$did_id));
        //echo $accountid;exit;
        if(astpp_eq0($accountid)){
            $whr=array();
        }
        else{
            $whr=array("accountid"=>$accountid);    
        }
       


        if($call_type_code==7)
        {
            $ringgroup = $this->db_model->getSelect( '*' ,'pbx_ringgroup',$whr);
            if ($ringgroup->num_rows() > 0) {

                $ringgroup_data =$ringgroup->result_array();
                $ringgroup_arr = array();
                foreach ($ringgroup_data as $value) {
                    $ringgroup_arr[$value['id']] =  $value['name'];
                }

                $ringgroup_info = array("name" => "extensions[extensions]" ,"id" => "extensions", "class" => " extensions");
                echo form_dropdown($ringgroup_info, $ringgroup_arr,$extensions);

            }
            else{
                echo '<select class="col-md-12 form-control form-control-lg selectpicker extensions_set" id="extensions" name="extensions[extensions]"><option>--</option></select>';
                }
            
        }



        if($call_type_code==8)
        {
            $conference = $this->db_model->getSelect( '*' ,'pbx_conference_specification',$whr);
            if ($conference->num_rows() > 0) {

                $conference_data =$conference->result_array();
                $conference_arr = array();
                foreach ($conference_data as  $value) {

                    $conference_arr[$value['id']] =  $value['name'];
                    
                }

                $conference_info = array("name" => "extensions[extensions]" ,"id" => "extensions", "class" => " extensions");
                echo form_dropdown($conference_info, $conference_arr, $extensions);

            }

            else{
              echo '<select class="col-md-12 form-control form-control-lg selectpicker extensions" id="extensions" name="extensions[extensions]"><option>--</option></select>';
            }
        }



        if($call_type_code==9)
        {
            $queue = $this->db_model->getSelect( '*' ,'pbx_queue',array("account_id"=>$accountid));
            if ($queue->num_rows() > 0) {

                $queue_data =$queue->result_array();
                $queue_arr = array();
                foreach ($queue_data as $value) {

                    $queue_arr[$value['id']] =  $value['name'];
                    
                }

                $queue_info = array("name" => "extensions[extensions]" ,"id" => "extensions", "class" => " extensions_set");
                echo form_dropdown($queue_info, $queue_arr, $extensions);

            }

            else{
              echo '<select class="col-md-12 form-control form-control-lg selectpicker extensions" id="extensions" name="extensions[extensions]"><option>--</option></select>';
            }
        }



        if($call_type_code==10)
        {
            $ivr = $this->db_model->getSelect( '*' ,'pbx_ivr_specification',$whr);
            if ($ivr->num_rows() > 0) {

                $ivr_data =$ivr->result_array();
                $ivr_arr = array();
                foreach ($ivr_data as $value) {
                    $ivr_arr[$value['id']] =  $value['name'];
                }

                $ivr_info = array("name" => "extensions[extensions]" ,"id" => "extensions", "class" => " extensions");
                echo form_dropdown($ivr_info, $ivr_arr, $extensions);
            }

            else{
              echo '<select class="col-md-12 form-control form-control-lg selectpicker extensions" id="extensions" name="extensions[extensions]"><option>--</option></select>';
            }
        }



        if($call_type_code==11)
        {
            $time_condition = $this->db_model->getSelect( '*' ,'time_condition',$whr);
            if ($time_condition->num_rows() > 0) {

                $time_condition_data =$time_condition->result_array();
                $time_condition_arr = array();
                foreach ($time_condition_data as  $value) {
                    $time_condition_arr[$value['id']] =  $value['name'];
                }

                $time_condition_info = array("name" => "extensions[extensions]" ,"id" => "extensions", "class" => " extensions");
                echo form_dropdown($time_condition_info, $time_condition_arr, $extensions);

            }

            else{
              echo '<select class="col-md-12 form-control test form-control-lg selectpicker extensions" id="extensions" name="extensions[extensions]"><option>--</option></select>';
            }
        }

    }
    // ASTPPENT-3146 Ashish MOH start
    function fssipdevices_get_music_on_hold_dropdown(){
        $add_array=$this->input->post();
        if(isset($add_array['music_on_hold_type'])){
            $selected=$add_array['music_on_hold'].','.$add_array['music_on_hold_type'];
        }else{
            $selected=$add_array['music_on_hold'];
        }
        $accountinfo = $this->session->userdata ( "accountinfo" );
        $account_id=isset($add_array['accountid']) && $add_array['accountid'] != '' ? $add_array['accountid'] :$accountinfo['id'];
        $reseller_id=$this->common->get_field_name("reseller_id", "accounts", array("id" => $account_id));
        $this->db->where_in('reseller_id',array("-1",$reseller_id));
        $this->db->where_in('accountid',array("0",$account_id));
        $query=$this->db->get('pbx_music_on_hold');
        $dropdown_params= array("name" => "music_on_hold" ,"id" => "music_on_hold", "class" => "form-control selectpicker form-control-lg music_on_hold col-md-3");
        $pbx_plan = $this->db_model->countQuery("*", "addons", array(
            "package_name" => "pbx_plans"
        ));
        if($pbx_plan == 1){
            $this->load->library('astpp/pbx_plan');
            $music_on_hold_status=$this->pbx_plan->get_music_on_hold_status($account_id);
            if(isset($music_on_hold_status) && $music_on_hold_status == 1){
                $dropdown_params= array("name" => "music_on_hold" ,"id" => "music_on_hold","disabled"=>"disabled", "class" => "form-control selectpicker form-control-lg music_on_hold col-md-3");
            }
        }
        $music_on_hold_arr = $recording_arr = $final_array = array();
        $final_array = array();
        $this->db->where('accountid',$account_id);
        $this->db->or_where('reseller_id','-1');
        $final_array = array("0,0"=>"--Select--");
        $music_on_hold =(array)$this->db->get_where('pbx_music_on_hold')->result_array();
        if(!empty($music_on_hold)){
            foreach ($music_on_hold as $key=>$value) {
                $music_on_hold_arr[$value['id'].',0'] =  $value['name'];
            }
        }
        $recording_query =(array)$this->db->get_where('pbx_recording',array("accountid"=>$account_id))->result_array();
        if(!empty($recording_query)){
            foreach ($recording_query as $key=>$value) {
                $recording_arr[$value['id'].',1'] =  $value['name'];
            }
        }
        if(!empty($music_on_hold_arr))
            $final_array['Music on Hold'] = $music_on_hold_arr;
        if(!empty($recording_arr))
            //sanket 5186 start
            $final_array['Prompt'] = $recording_arr;
            //sanket 5186 end
        echo form_dropdown($dropdown_params, $final_array, $selected);
    }
    // ASTPPENT-3146 MOH end
}
?>
