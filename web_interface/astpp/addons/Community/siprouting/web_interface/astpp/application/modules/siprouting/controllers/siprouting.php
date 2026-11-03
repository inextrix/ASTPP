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
class Siprouting extends MX_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper ( 'template_inheritance' );
		$this->load->library ( 'session' );
		$this->load->library ( "siprouting_form" );
		$this->load->library ( 'astpp/form','siprouting_form' );
		$this->load->library ( 'astpp/permission');
		$this->load->library ( 'freeswitch_lib' );
		$this->load->library ('ASTPP_Sms');
		$this->load->model ( 'siprouting_model' );
		
		if ($this->session->userdata ( 'user_login' ) == FALSE)
			redirect ( base_url () . '/astpp/login' );
	}
	
	function fssipdevices_routing_save() {
		$add_array = $this->input->post();
		// print($add_array); die;
		//Jaimin Issue No.3193 SIP Device advance routing page redesign
		if(!isset($add_array['call_forwarding_flag'])){
			$add_array['call_forwarding_flag'] = 1;
		}
		if(!isset($add_array['on_busy_flag'])){
			$add_array['on_busy_flag'] = 1;
		}
		if(!isset($add_array['no_answer_flag'])){
			$add_array['no_answer_flag'] = 1;
		}
		if(!isset($add_array['not_register_flag'])){
			$add_array['not_register_flag'] = 1;
		}
		if(!isset($add_array['follow_me_flag'])){
			$add_array['follow_me_flag'] = 1;
		}
		if(!isset($add_array['ingnore_busy_flag'])){
			$add_array['ingnore_busy_flag'] = 1;
		}
		if(!isset($add_array['do_not_disturb'])){
			$add_array['do_not_disturb'] = 1;
		}
		//END
		$redirect_url=$add_array['redirect_url'];
		unset($add_array['redirect_url']);
		if(isset($add_array['extension_call_forward']) && $add_array['extension_call_forward'] == "1"){
				$add_array['call_forwarding_destination'] = $add_array['call_forwarding_destination']."#";
		}else{
				$add_array['call_forwarding_destination'] = $add_array['call_forwarding_destination'];
		}
		
		if(isset($add_array['extension_on_busy']) && $add_array['extension_on_busy'] == "1"){
				$add_array['on_busy_destination'] = $add_array['on_busy_destination']."#";
		}else{
				$add_array['on_busy_destination'] = $add_array['on_busy_destination'];
		}
		
		if(isset($add_array['extension_no_answer']) && $add_array['extension_no_answer'] == "1"){
				$add_array['no_answer_destination'] = $add_array['no_answer_destination']."#";
		}else{
				$add_array['no_answer_destination'] = $add_array['no_answer_destination'];				
		}
		
		if(isset($add_array['extension_not_registered']) && $add_array['extension_not_registered'] == "1"){
				$add_array['not_register_destination'] = $add_array['not_register_destination']."#";
		}else{
				$add_array['not_register_destination'] = $add_array['not_register_destination'];				
		}
		if(!isset($add_array['pickup_ids'])){
			$add_array['pickup_ids']=array();
		}
		if(!isset($add_array['allow_pickup_ids'])){
			$add_array['allow_pickup_ids']=array();
		}
		unset($add_array['extension_call_forward']);
		unset($add_array['extension_on_busy']);
		unset($add_array['extension_no_answer']);
		unset($add_array['extension_not_registered']);
		if($add_array['id'] == ''){
		$query = $this->siprouting_model->add_sip_device_routing($add_array);
		$this->session->set_flashdata('astpp_errormsg', 'Sip Routing added successfully!');
		} else {
		$query = $this->siprouting_model->edit_sip_device_routing($add_array,$add_array['id']);
		$this->session->set_flashdata('astpp_errormsg', 'Sip Routing updated successfully!');
		}
		if ($this->session->userdata('logintype') == '0'){
			redirect(base_url() . 'user/user_sipdevices/');
		}else{
			redirect($redirect_url);
		}
		exit;
	}
	//Nirali  issue 3110 PBX Voicemail greeting based on SIP device status
	function fssipdevices_voicemail($edit_id = '',$type = "")
    {

	    $where = array(
		    'id' => $edit_id
    		);
	    $account = $this->db_model->getSelect("*", "sip_devices", $where);  
		$data['page_title'] = gettext('Voice Mail');
		//Nirali issue 3898 start 
		$data['back_flag'] = true;
		//Nirali issue 3898 END 
	    $result = $account->row_array();
	if(isset($result['unavailable_greeting']) && $result['unavailable_greeting'] !=''){
		$data['unavailable_greeting'] = $result['unavailable_greeting'];
		$data['unavailable_greeting_play'] = $this->siprouting_form->play_recoding_file('','unavailable_greeting',$data['unavailable_greeting']);
	}else{
		$data['unavailable_greeting'] = '';
		$data['unavailable_greeting_play'] = '';
	}
	if(isset($result['name_greeting']) && $result['name_greeting'] !=''){
		$data['name_greeting'] = $result['name_greeting'];
		$data['name_greeting_play'] = $this->siprouting_form->play_recoding_file('','',$data['name_greeting']);
	}else{
		$data['name_greeting'] = '';
		$data['name_greeting_play'] = '';
	}
	
	if(isset($result['busy_greeting']) && $result['busy_greeting'] !=''){
		$data['busy_greeting'] = $result['busy_greeting'];
		$data['busy_greeting_play'] = $this->siprouting_form->play_recoding_file('','',$data['busy_greeting']);
	}else{
		$data['busy_greeting'] = '';
		$data['busy_greeting_play'] = '';
	}
	
	if(isset($result['temporary_greeting']) && $result['temporary_greeting'] !=''){
		$data['temporary_greeting'] = $result['temporary_greeting'];
		$data['temporary_greeting_play'] = $this->siprouting_form->play_recoding_file('','',$data['temporary_greeting']);
	}else{
		$data['temporary_greeting'] = '';
		$data['temporary_greeting_play'] = '';
    }
	    $where = array( 
		    'accountid' => $result['accountid']
	    );
	    $query = $this->db_model->getSelect("*", "pbx_recording", $where);
	    $edit_data = $query->result_array();   
        $data['edit_data'] = $edit_data;
	    $data['edit_id']=$edit_id;
		//Nirali issue no 3453
	    if ($this->session->userdata('logintype') == '0'){
			$this->load->view("view_freeswitch_user_voicemail",$data);
			
		}else{
			$this->load->view("view_freeswitch_voicemail",$data);
		}
        //END
        }
		//END
		//Nirali  issue 3110 PBX Voicemail greeting based on SIP device status
		function fssipdevices_voice_mail_save($edit_id='')
		{
			$add_array = $this->input->post();
			$redirect_url=$add_array['redirect_url'];
			unset($add_array['redirect_url']);
			$this->siprouting_model->edit_voice($add_array, $edit_id);
			$this->session->set_flashdata('astpp_errormsg', gettext('Voicemail Updated Successfully!'));
			if ($this->session->userdata('logintype') == '0'){
				redirect(base_url() . 'user/user_sipdevices/');
			}else{
				redirect($redirect_url);
			}
			exit;
			
		}
		//END
		//Nirali  issue 3110 PBX Voicemail greeting based on SIP device status
		function fssipdevices_voicemail_file_play($file_name) {
			$file_name = FCPATH."upload/pbx/" . $file_name;
			ob_clean();
			flush();
			readfile($file_name);
			exit();
		}
		//END


	function fssipdevices_routing($id='') { 
		$data ['page_title'] = gettext ( 'Advance Sip Routing' );
		if(isset($id) && $id!=""){
			$data['sip_device_id']= $id;
			$data['back_flag'] = true;
			$account_id = $this->common->get_field_name ( 'accountid', 'sip_devices', array ('id' => $id) );
			$sip_device_routing_details = $this->db_model->getSelect("*","sip_device_routing",array('sip_device_id'=>$id));
			if($sip_device_routing_details->num_rows()>0){
				$sip_device_routing_details_array=$sip_device_routing_details->result_array();
				$sip_routing_data=$sip_device_routing_details_array[0];
				$sip_routing_data['extension_call_forward'] = (strpos($sip_routing_data['call_forwarding_destination'], '#') !== false) ? '1':'2'; 
		 		$sip_routing_data['extension_on_busy'] = (strpos($sip_routing_data['on_busy_destination'], '#') !== false) ? '1':'2';
		 		$sip_routing_data['extension_no_answer'] = (strpos($sip_routing_data['no_answer_destination'], '#') !== false) ? '1':'2'; 
		 		$sip_routing_data['extension_not_registered'] = (strpos($sip_routing_data['not_register_destination'], '#') !== false) ? '1':'2';

				$follow_me_destination = json_decode($sip_routing_data['follow_me_destination'],true);
				if($follow_me_destination == ''){
					$follow_me_destination = array('destination_1'=>"", 
					'time_out_1'=>"",
					'destination_2'=>"", 
					'time_out_2'=>"",
					'destination_3'=>"", 
					'time_out_3'=>"",
					'destination_4'=>"", 
					'time_out_4'=>"",
					'destination_5'=>"", 
					'time_out_5'=>"",
					);
				}
				$data['edit_array']=astpp_array_merge($sip_routing_data,$follow_me_destination);
	      	}
	      	else{
	      		$data['edit_array']=array(
	      			'id'=>"",
	      			'call_forwarding_flag'=>1,
					'call_forwarding_destination'=>"",
					'on_busy_flag'=>1,
					'on_busy_destination'=>"",
					'no_answer_flag'=>1,
					'no_answer_destination'=>"",
					'not_register_flag'=>1,
					'not_register_destination'=>"",
					'follow_me_flag'=>1,
					'destination_1'=>"", 
					'time_out_1'=>"",
					'destination_2'=>"",
					'time_out_2'=>"",
					'destination_3'=>"",
					'time_out_3'=>"",
					'destination_4'=>"",
					'time_out_4'=>"",
					'destination_5'=>"",
					'time_out_5'=>"",
					'ingnore_busy_flag'=>1,
					'do_not_disturb'=>1,
				);
	      	}
			$data['accountid'] = $account_id;
			// print($data); die;
			if ($this->session->userdata('logintype') == '0'){
				$this->load->view("view_follow_me_add_edit_user",$data);
			}else{
				$this->load->view("view_follow_me_add_edit",$data);
			}
		}
		else{
			if ($this->session->userdata('logintype') == '0'){
				$this->load->view("view_follow_me_add_edit_user",$data);
			}else{
				$this->load->view("view_follow_me_add_edit", $data);
			}
		}
	}

	function fssipdevices_build_extension_dropdown(){
		if($_POST['extension_value'] == '2'){
			$accountid = $_POST['accountid'];
			$name = $_POST['name'];
			if(isset($_POST['exten_num']) && $_POST['exten_num'] !=""){
				$value = str_replace("#","",$_POST['exten_num']);
			}else{
				$value = "";
			}
			$output = $this->common->sip_dropdown($name,$accountid,$value);
		}else{
			if(isset($_POST['exten_num']) && $_POST['exten_num'] !=""){
				$value = str_replace("#","",$_POST['exten_num']);
			}else{
				$value = "";
			}
			$name = $_POST['name'];
			//JAIMIN Issue No.3193 SIP Device advance routing page redesign
			$output = '<input type="text" value="'.$value.'" name="'.$name.'" id="'.$name.'" class="form-control form-control-lg col-md-12 float-left '.$name.'">';
			//END
		}
		echo $output;
	}
	// ASTPPENT-3146 Ashish MOH start
	function fssipdevices_get_music_on_hold_dropdown(){
		$add_array=$this->input->post();
		$selected=$add_array['music_on_hold'];
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$account_id=isset($add_array['accountid']) ? $add_array['accountid'] :$accountinfo['id'];
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
		$final_array = array("0,0"=>"--Select--");
		if($query->num_rows > 0){
			$music_on_hold=$query->result_array();
			foreach ($music_on_hold as $key=>$value) {	
				$music_on_hold_arr[$value['id'].',0'] =  $value['name'];
			}
		}
		$recording_query =$this->db->get_where('pbx_recording',array("accountid"=>$account_id));
		if($recording_query->num_rows > 0){
			$recording_result=$recording_query->result_array();
			foreach ($recording_result as $key=>$value) {		
				$recording_arr[$value['id'].',1'] =  $value['name'];
			}
		}
		if(!empty($music_on_hold_arr))
			$final_array['Music on Hold'] = $music_on_hold_arr;
		if(!empty($recording_arr))
			$final_array['Recording'] = $recording_arr; 
		echo form_dropdown($dropdown_params, $final_array, $selected);
	}
	// ASTPPENT-3146 MOH end

}

?>