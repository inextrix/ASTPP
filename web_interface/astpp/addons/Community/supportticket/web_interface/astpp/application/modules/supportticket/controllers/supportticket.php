<?php
###############################################################################
# ASTPP - Open Source VoIP Billing Solution
#
# Copyright (C) 2016 Inextrix Technologies Pvt. Ltd.
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
class Supportticket extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->helper('template_inheritance');
		$this->load->library('session');
		$this->load->library("supportticket_form");
		$this->load->library('astpp/form');
		$this->load->library('astpp/email_lib');	
		$this->load->model('supportticket_model');
		if(file_exists(FCPATH."application/modules/supportticket/tooltip.php")){
            include_once(FCPATH."application/modules/supportticket/tooltip.php");
            $this->tooltip_data = $tooltip_data;
		}
		if ($this->session->userdata('user_login') == FALSE)
			redirect(base_url() . '/astpp/login');
	}

	function supportticket_add($type = "") {
		
		$data['username'] = $this->session->userdata('user_name');
		$data['flag'] = 'Create Support Ticket';
		$data['page_title'] = gettext('Create Support Ticket');
 	    $account_data = $this->session->userdata("accountinfo");
		$data['account_data']=$account_data;
		
		$data['drop']=form_dropdown('departmentid',$this->supportticket_model->build_concat_dropdown_departmnet("id,name,email_id", " department", "where_arr", array("status"=>"0")), '');
		$add_arr ['template'] = "";
		$this->load->view('view_supportticket_add', $data);
	}

	function supportticket_edit($edit_id = '') {
//		if($this->session->userdata('logintype') == 0 ){
		// add
		$where = array('id'=>$edit_id);
		$this->db->where($where);
		$support_ticket_query = $this->db->get('support_ticket');
		$support_ticket_result = (array)$support_ticket_query->first_row();
		if(isset($support_ticket_result) && !empty($support_ticket_result)){
	        $account_data = $this->session->userdata("accountinfo");
		// $support_ticket_type=$this->common->get_field_name('ticket_type','support_ticket', $edit_id);
		$support_ticket_type = $support_ticket_result['ticket_type'];
		if($support_ticket_type == 5){
			$explode_display_flag = explode(',',$support_ticket_result['close_ticket_display_flag']);
			foreach($explode_display_flag as $key =>$value){
				if($value == $account_data['id']){
					unset($explode_display_flag[$key]);
				}
			}
			$implode_display_flag = astpp_implode(',',$explode_display_flag);
			$this->db->where('id',$edit_id);
			$this->db->update("support_ticket", array('close_ticket_display_flag'=>$implode_display_flag));
		}
//		}
		$account_data = $this->session->userdata("accountinfo");
		
		$where = array('id' => $edit_id);
		$account = $this->db_model->getSelect("*", "support_ticket", $where);
		foreach ($account->result_array() as $key => $value) {
			$edit_data = $value;
		}
		$data['support_ticket']	=$edit_data;
		$support_department=$this->common->get_field_name('name','department',array('id'=>$edit_data['department_id']));
		$support_id=$edit_data['support_ticket_number'];
		$data['page_title'] = gettext('Edit Support Ticket:&nbsp; #'.$support_id. '('.$support_department .')');
		$this->db->order_by('id','DESC');
		$support_ticket_details = $this->db_model->getSelect("*", "support_ticket_details", array('support_ticket_id'=>$edit_id));
		$data['details_arr'] = $support_ticket_details->result_array();
		
		if($account_data['id'] == $edit_data['accountid']){
			$data['ticket_lable']= "Customer-Reply";
			$data['ticket_type']= "2";
		}else{
			$data['ticket_lable']= "Answer";
			$data['ticket_type']= "1";
		}
		if(astpp_count($data['details_arr']) == 1 && (!astpp_eq0($this->session->userdata('logintype'))) && $this->session->userdata('logintype') != 1){
			$data['ticket_lable']= "Answered";
		}else{
			$data['ticket_lable']= "Customer-Reply";
		}
		if($account_data['type'] == '-1' || $account_data['type'] == '2' || $account_data['type'] == '1'){
			$data['ticket_lable']= "Answered";
			if($data['support_ticket']['ticket_type'] == 2){
				$data['support_ticket']['ticket_type'] = "1";
			}
			//$data['support_ticket']['ticket_type'] = "1"; // AD : Hotfix to resolve issue:4137
		}
		$this->load->view('view_supportticket_edit', $data);
		}else{
			redirect(base_url() . 'supportticket/supportticket_list/');
		}
	}
	function supportticket_list_search() {
		$ajax_search = $this->input->post('ajax_search', 0);
 
		if ($this->input->post('advance_search', TRUE) == 1) {
			$this->session->set_userdata('advance_search', $this->input->post('advance_search'));
			$action = $this->input->post();
			unset($action['action']);
			unset($action['advance_search']);
			$this->session->set_userdata('supportticket_list_search', $action);
		}
		if (@$ajax_search != 1) {
			redirect(base_url() . 'supportticket/supportticket_list/');
		}
	}

	function supportticket_list_clearsearchfilter() {
		$this->session->set_userdata('advance_search', 0);
		$this->session->set_userdata('account_search', "");
	}

	function supportticket_list() {
		$data['username'] = $this->session->userdata('user_name');
		$data['page_title'] = gettext('Support Tickets');
		$data['search_flag'] = true;
		$this->session->set_userdata('advance_search', 0);
		$data['grid_fields'] = $this->supportticket_form->build_supportticket_list();
		$data["grid_buttons"] = $this->supportticket_form->build_grid_buttons();
		//~ $data['form_search'] = $this->form->build_serach_form($this->supportticket_form->get_supportticket_search_form());
		$department_list_array=$this->db_model->getSelect("*", "department",array());
		$data['department_list_result']=$department_list_array->result_array();
		$accounts_data = $this->db_model->getSelect("*", "accounts",array("type" => "0" , "status" => "0" , "deleted" => 0));
		$data['accounts_data']=$accounts_data->result_array();
		$this->load->view('view_supportticket_list', $data);
	 
}
	/**
	 * -------Here we write code for controller accounts functions account_list------
	 * Listing of Accounts table data through php function json_encode
	 */
	function supportticket_list_json() {
		$json_data = array();
		$count_all = $this->supportticket_model->getsupportticket_list(false);
		$paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
		$json_data = $paging_data["json_paging"];
		$query = $this->supportticket_model->getsupportticket_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
		$className ="";
		if ($query->num_rows () > 0) {
			$query = $query->result_array ();
			if($this->session->userdata['logintype'] == 2 || $this->session->userdata['logintype'] == 1){ 
				foreach ( $query as $value ) {
					$json_data ['rows'] [] = array (
						'cell' => array (
						'<input type="checkbox" name="chkAll" id=' . $value ['id'] . ' class="ace chkRefNos' . $className . '" onclick="clickchkbox(' . $value ['id'] . ')" value=' . $value ['id'] . '><lable class="lbl"></lable>',
						'<a href="'. base_url() .'supportticket/supportticket_edit/' . $value ['id'] . '/" class="" title="Edit">'.$value ['support_ticket_number'].'</a>&nbsp',
						'<a href="'. base_url() .'supportticket/supportticket_edit/' . $value ['id'] . '/" class="" title="Edit">'.$value ['subject'].'</a>&nbsp',
						$this->common->get_field_name_coma_new('first_name,last_name,number','accounts',$value ['accountid']),
						$this->get_priority_type($value ['priority']),
						$this->common->get_field_name('name','department',$value ['department_id']),
						$this->get_ticket_type($value ['ticket_type']),
						$this->common->convert_GMT_to('', '', $value['last_modified_date']),
						$this->get_action_buttons_supportticket($value ['id']),
					));
				}
			}else{
				foreach ( $query as $value ) {
					$json_data ['rows'] [] = array (
						'cell' => array (
							'<input type="checkbox" name="chkAll" id=' . $value ['id'] . ' class="custom-control-input ace chkRefNos' . $className . '" onclick="clickchkbox(' . $value ['id'] . ')" value=' . $value ['id'] . '><lable class="lbl"></lable>',
							'<a href="'. base_url() .'supportticket/supportticket_edit/' . $value ['id'] . '/" class="" title="Edit">'.$value ['support_ticket_number'].'</a>&nbsp',
							'<a href="'. base_url() .'supportticket/supportticket_edit/' . $value ['id'] . '/" class="" title="Edit">'.$value ['subject'].'</a>&nbsp',		 
							$this->get_priority_type($value ['priority']),
							$this->common->get_field_name('name','department',$value ['department_id']),
							$this->get_ticket_type($value ['ticket_type']),
							$this->common->convert_GMT_to('', '', $value['last_modified_date']),
							$this->get_action_buttons_supportticket($value ['id']),
						)
					);
				}
			}
		}
		echo json_encode($json_data);
	}
	
	function get_action_buttons_supportticket($id) {
        	$ret_url = '<a class="btn btn-royelblue btn-sm" href="'. base_url() .'supportticket/supportticket_edit/' . $id . '/" "  title="Edit">&nbsp;<i class="fa fa-pencil-square-o fa-fw"></i></a>&nbsp;';
		if($this->session->userdata['logintype'] == 2){
			$ret_url .= '<a class="btn btn-royelblue btn-sm" href="'. base_url() .'supportticket/supportticket_delete/' . $id . '/"  title="Delete" onClick="return get_alert_msg();">&nbsp;<i class="fa fa-trash fa-fw"></i></a>';
		}
		return $ret_url;
	}
	
	
	function last_reply_time($date){
		$seconds = strtotime(gmdate("Y-m-d H:i:s")) - strtotime($date);
		$days    = astpp_floor(astpp_n($seconds) / 86400);
		$hours   = astpp_floor((astpp_n($seconds) - (astpp_n($days) * 86400)) / 3600);
		$minutes = astpp_floor((astpp_n($seconds) - (astpp_n($days) * 86400) - (astpp_n($hours) * 3600))/60);
		$seconds = astpp_floor((astpp_n($seconds) - (astpp_n($days) * 86400) - (astpp_n($hours) * 3600) - (astpp_n($minutes)*60)));
		return $days.' Day '.$hours.' Hour '.$minutes .' Min '.$seconds.' Sec' ;
    	}
	
	function get_ticket_type($call_type) {
        $call_type_array = array('0' => '<span class="badge " style="line-height: 1.0; font-size: 81%;background:#f7331d !important;">Open</span>', "1"=>'<span class="badge " style="line-height: 1.0; font-size: 81%; background:#ed652f !important;">Answered</span>', '2' =>  '<span class="badge " style="line-height: 1.0; font-size: 81%;background:#E6A800 !important;">Customer-Reply</span>', '3' => '<span class="badge " style="line-height: 1.0; font-size: 81%;background:#0e4da0 !important;">On-hold</span>', '4'=>'<span class="badge " style="line-height: 1.0; font-size: 81%;background:#22961c !important;">Progress</span>','5'=>'<span class="badge " style="line-height: 1.0; font-size: 81%;background:#7a6c6c !important;">Close</span>');
        return $call_type_array[$call_type];
    }

	/*function get_priority_type($call_type) {
        $call_type_array = array('0' => '<span class="badge " style="line-height: 1.0; font-size: 81%; background:#f7331d !important;">High</span>', "1"=>'<span  style="line-height: 1.0; font-size: 81%;background:#E6A800 !important;" class="badge ">Normal</span>', '2' => '<span style="line-height: 1.0; font-size: 81%;background:#22961c !important;" class="badge " >Low</span>');
        return $call_type_array[$call_type];
    }*/
	function get_priority_type($priority) {
		$priority_array = array('0' => '<span class = "btn badge-danger p-2" style="line-height: 1.0; font-size: 81%;"> High </span>',
					 "1" => '<span class = "btn badge-warning p-2" style="line-height: 1.0; font-size: 81%;"> Normal </span>',
					 '2' => '<span class = "btn badge-success p-2" style="line-height: 1.0; font-size: 81%;"> Low </span>');
        return $priority_array[$priority];
	}
	function supportticket_delete($support_id) {
		$where = array("id" => $support_id);
		$this->db_model->update("support_ticket", array("status" => "1"), $where);

		$where_details = array("support_ticket_id" => $support_id);
		$this->db_model->update("support_ticket_details", array("status" => "1"), $where_details);

		$this->session->set_flashdata('astpp_notification', gettext('Support ticket removed successfully!'));
		redirect(base_url() . 'supportticket/supportticket_list/');
	}
	function supportticket_details_save(){
	       $files=$_FILES;
	       $add_array = $this->input->post();
	       $add_array['page_title'] = gettext('Support Ticket');
	       $nooffile= $files['file']['name'];
	       $count=astpp_count($nooffile);
	       $add_array['attachment']='';
	       $add_array['file']='';
	       for($i=0;$i<$count;$i++){
		       $tmp_name[]= $files['file']['tmp_name'][$i];
		       if(astpp_eq0($files['file']['error'][$i])){
			       $cur_name = $files['file']['name'][$i];
			       $parts = explode(".", $cur_name);
			       $reverse_array = array_reverse($parts);
			       $extension = $reverse_array[0];
			       $add_array['attachment'].=date('ymdhis').$i.'.'.$extension.',';
			       $add_array['file'].=date('ymdhis').$i.'.'.$extension.',';
		     	       $uploadedFile1 = $files['file']['tmp_name'][$i];
			       $actual_file_name=date('ymdhis').$i.'.'.$extension;
			       $dir_path=  getcwd()."/attachments/";
			       $path =$dir_path.$actual_file_name;
			       if (move_uploaded_file($uploadedFile1,$path)) {
				   $this->session->set_flashdata('astpp_errormsg', gettext('Files added successfully!'));
			       }
			       else{
				    $this->session->set_flashdata('astpp_errormsg', gettext('Please try again!'));
			       }
		       }
	       }
	       $account_data = $this->session->userdata("accountinfo");
	       $add_array['attachment']=trim($add_array['attachment'],',');
	       $add_array['file']=trim($add_array['file'],',');
	       $res_id=$account_data['reseller_id'];
	       $account_id=$account_data['id'];
	     
	       if(isset($add_array['id']) && $add_array['id'] != ''){
			  
			$support_ticket_info=(array)$this->db->get_where('support_ticket',array("id"=>$add_array['id']))->first_row();	
			$support_ticket_accountid = $support_ticket_info['accountid'];   		
			$support_ticket_type=$support_ticket_info['ticket_type'];
						
			$close_ticket_display_flag = $support_ticket_info['close_ticket_display_flag'];
			 
			if($support_ticket_type == 5 && $add_array['template1']){
				$add_array['ticket_type']=2;
				$parent_info = $this->common->get_parent_info($account_id,0);
				if(strcmp($parent_info,"1,") == 0) {
					$str_close_flag = rtrim($parent_info,",");
				} else {
					$str_close_flag = $parent_info. "1";
				}
				$this->db->select('id');
				$admin_result = $this->db->get_where('accounts',array("type"=>2,"deleted"=>0));
				$admin_str='';
				if($admin_result->num_rows() > 0 ){
					$admin_result = $admin_result->result_array();
					foreach($admin_result as $key=>$value){
						$admin_str= $value['id'].",";
					}
					$admin_str= rtrim($admin_str,",");
				}
				$str_close_flag = $str_close_flag.",".$admin_str;
			}
			if($add_array['ticket_type'] != 5){
				
				// add 
				$account_id=$support_ticket_info['accountid'];
				$parent_info = $this->common->get_parent_info($account_id,0);
				
				if(strcmp($parent_info,"1,") == 0) {
					$str_close_flag = rtrim($parent_info,",");
				} else {
					$str_close_flag = $parent_info. "1";
				}
				$this->db->select('id');
				$admin_result = $this->db->get_where('accounts',array("type"=>2,"deleted"=>0));
				$admin_str='';
				if($admin_result->num_rows() > 0 ){
					$admin_result = $admin_result->result_array();
					foreach($admin_result as $key=>$value){
						$admin_str= $value['id'].",";
					}
					$admin_str= rtrim($admin_str,",");
				}
				$str_close_flag = $str_close_flag.",".$admin_str;

				$this->db->where('id',$add_array['id']);
				$this->db->update("support_ticket", array('close_ticket_display_flag'=>$str_close_flag));
			}
			$act_info=(array)$this->db->get_where('accounts',array("id"=>$support_ticket_accountid))->first_row();	
				
			$act_type = $act_info['type'];   		
			$act_email = $act_info['email'];   		
			$add_array['email']=$act_email;
			
			$add_array['departmentid']=$support_ticket_info['department_id'];
			$this->supportticket_model->edit_supportticket($add_array);	
			if($account_data['type'] == '-1'){
				$this->session->set_flashdata('astpp_errormsg', gettext('Support ticket replied successfully!'));
				redirect(base_url() . 'supportticket/supportticket_list/');
				exit; 
				}else{
					$this->session->set_flashdata('astpp_errormsg', gettext('Support ticket edited successfully!'));
					redirect(base_url() . 'supportticket/supportticket_list/');
					exit; 
				}	
			}else{
					$this->supportticket_model->add_supportticket($add_array);	
					$this->session->set_flashdata('astpp_errormsg', gettext('Support ticket added successfully!'));
						redirect(base_url() . 'supportticket/supportticket_list/');
				
			}
             /*  $screen_path = getcwd()."/cron";
               $screen_filename = "Email_Broadcast_".strtotime('now');
               $command = "cd ".$screen_path." && /usr/bin/screen -d -m -S  $screen_filename php cron.php BroadcastEmail";
          //    echo $command;exit;
               exec($command);*/

               $this->session->set_flashdata('astpp_errormsg', gettext('Support ticket generated successfully!'));
               redirect(base_url() . 'supportticket/supportticket_list/');
               exit; 
        }
	function supportticket_delete_multiple() {
		$add_array = $this->input->post ();
 		$where = 'IN ('.$add_array['selected_ids'].')';
		$this->db->where('id '.$where);
		$this->db->delete("support_ticket");
		$this->db->where('support_ticket_id '.$where);
		$this->db->delete("support_ticket_details");
		echo TRUE;
	}
    function supportticket_close_multiple() {
		
		$add_array = $this->input->post ();
		$data=$add_array['selected_ids'];
		if($add_array['selected_ids'] != ''){
			$where = 'IN ('.$add_array['selected_ids'].')';
			$this->db->where('id '.$where);
				
			echo $this->db->update("support_ticket", array("ticket_type" => "5",'last_modified_date'=>gmdate("Y-m-d H:i:s")));
			$ticket_numbers = $this->db_model->getSelect('*','support_ticket','id '.$where)->result_array();
			$accountinfo = $this->session->userdata('accountinfo');
			
			foreach ($ticket_numbers as $key => $value) {
				$accountinfo['cc_email_ids'] = strtolower($this->common->get_field_name("additional_email_address","department",array("id" => $value['department_id'])));
				//$accountinfo['ticket_message'] = $this->common->get_field_name("message","support_ticket_details",array("support_ticket_id" => $value['id']));
				$accountinfo['ticket_number'] = $value['support_ticket_number'];
				$accountinfo['ticket_subject'] = $value['subject'];
				$accountinfo['customer_account_id'] = $value['accountid'];
				$this->common->mail_to_users('email_sent_support_ticket',$accountinfo);
			}			
		} 
	}
	 
	function supportticket_list_attachment($file_name) {
	if(file_exists(getcwd().'/attachments/'.$file_name)){
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$file_name);
		ob_clean();
		flush();
		readfile(getcwd().'/attachments/'.$file_name);
	}
	}
	function set_support_ticket_id($select, $table, $support_id){
	$system_config = common_model::$global_config['system_config'];
		$support_ticket = $system_config['ticket_digits'];    
		return sprintf('%0'.$support_ticket.'d', $support_id);

    }
     function set_support_drop($select, $table, $id_where = '', $id_value = ''){
		 
		   $this->load->model('supportticket_model');
   
    $department_id = form_dropdown('departmentid', $this->supportticket_model->build_concat_dropdown_departmnet("id,name,email_id", " department", "where_arr", array("status"=>"0")), '');
																	   
		$data['department_id'] = $department_id;
    $this->load->view('view_supportticket_add', $data);
    }
    function set_priority_status($select = '') {
        $status_array = array("" => "--Select--",
            "1" => "Normal",
            "0" => "High",
            "2" => "Low"
        );
        return $status_array;
	}
	function set_ticket_status($select = '') {
        $status_array = array("" => "--All--",
            "0" => "Open",
            "1" => "Answered",
            "2" => "Customer-Reply",
            "3" => "On-hold",
            "4" => "Progress",
            "5" => "Close",
        );
        return $status_array;
	}
	
    
}

?>
 
