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
class supportticket_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper ( 'file' );
	}

	function getsupportticket_list($flag, $start = 0, $limit = 0) {
		$this->db_model->build_search('supportticket_list_search');
		$accountinfo = $this->session->userdata("accountinfo");
		$accountid = $accountinfo['id'];
		$department_arr =array();
		if ($accountinfo['type'] == 1 || $accountinfo['type'] == 5) {
			$this->db->where('reseller_id',$accountinfo['id']);
		}else if(astpp_eq0($accountinfo['type']) || $accountinfo['type'] == '3'){
			$where = array(
				"accountid"=>$accountinfo['id'],
				"status"=>"0"
			);
			$this->db->where($where);
		}else if($accountinfo['type'] == 2) {
			$admin_where = "FIND_IN_SET ('".$accountinfo['id']."',admin_id_list)";
			$this->db->where($admin_where);
			$department_result = $this->db->get("department");
			if($department_result->num_rows () > 0){
				$department_result = $department_result->result_array();
				foreach($department_result as $key=>$value){
					$department_arr[$value['id']]= $value['id'];
				}
				if(!empty($department_arr)){
					$this->db->where_in('department_id',$department_arr);
				}else{
					$this->db->where('department_id',"-1");
				}
			}
		}
		if($this->session->userdata('advance_search') != 1){
			//$where = "close_ticket_display_flag LIKE '%$accountid%'";
			//$this->db->where($where);
			//$this->db->where_in("ticket_type",array("0","1","2"));
		}
		if ($flag) {
			$query = $this->db_model->Select("*", "support_ticket","", "last_modified_date", "DESC", $limit, $start);
		} else {
			$query = $this->db_model->countQuery("*", "support_ticket","", "last_modified_date", "DESC", $limit, $start);
		}
		//echo $this->db->last_query();exit;
		return $query;
	}
	function add_supportticket($data) {
		$account_data = $this->session->userdata("accountinfo");
		// add 
		$account_id=$account_data['id'];
		//harsh_supportticket_18_08
		if($account_data['type'] == '-1' || $account_data['type'] == 2 && isset($data['account_id'])){
			$account_id=$data['account_id'];
		}
		$parent_info = $this->common->get_parent_info($account_id,0);
		if(strcmp($parent_info,"1,") == 0) {
			$str_close_flag = rtrim($parent_info,",");
		} else {
			$str_close_flag = $parent_info. "1";
		}
		if ($this->session->userdata('logintype') == '-1' || $this->session->userdata('logintype') == 2) {
			if((!astpp_eq0($data['account_id']))){		
				$account_data=(array)$this->db->get_where('accounts',array("id"=>$data['account_id']))->first_row();
			}
		}
		if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
			$reseller_id = $account_data['id'];
		} else {
			$reseller_id = "0";
		}
//~ change by bansi faldu
//~ issue:#81
//~ for ticket length	
	 $where_ticket_number = array("name" => "ticket_digits");
	$ticket_number = $this->db_model->Select("value", "system", $where_ticket_number, "", "", "", "");
	$ticket_digits = $ticket_number->result_array();
	$ticket_digits = $ticket_digits[0]['value'];
	
	$support_ticket_number = $this->common->find_uniq_rendno($ticket_digits, 'number', 'accounts');
	$department_data=(array)$this->db->get_where('department',array('id'=>$data['departmentid']))->first_row();
	$add_array=array(
			'ticket_type'=>$data['ticket_type'],
			'priority'=>$data['priority'],
			'accountid'=> $data['account_id'] != "" && isset($data['account_id']) ? $data['account_id']: $account_data['id'],
			'reseller_id'=>isset($data['account_id'])?$this->common->get_field_name('reseller_id','accounts',$data['account_id']):$account_data['reseller_id'],
			'subject'=>$data['subject'],
			'creation_date'=>gmdate("Y-m-d H:i:s"),
			'last_modified_date'=>gmdate("Y-m-d H:i:s"),
			'department_id'=>$department_data['id'],
			'support_ticket_number'=>$support_ticket_number,
			'status'=>0,
			'close_ticket_display_flag' => $str_close_flag,
			);
	$this->db->insert("support_ticket", $add_array);
       	$support_ticket_id = $this->db->insert_id();
	$add_array_details=array(
			'support_ticket_id'=>$support_ticket_id,
			'generate_account_id'=>$account_data['id'],
			'message'=>$data['template'],
			'attachment'=>$data['file'],
			'creation_date'=>gmdate("Y-m-d H:i:s"),
			'status'=>0,
			);
	$this->db->insert("support_ticket_details", $add_array_details);
	
	$email_arr[0]=$this->common->get_field_name('email_id','department',array('id'=>$data['departmentid']));
	if ($this->session->userdata('logintype') == 2 || $this->session->userdata('logintype') == '-1') {
		$email_arr[1]=$this->common->get_field_name('email','accounts',array('id'=>$data['account_id']));	
	}
	$admin_id= $department_data['admin_id_list'];
	$sub_admin_id= $department_data['sub_admin_id_list'];
	$additional_email_address= $department_data['additional_email_address'];
	$admin_email_arr= array();
	if($admin_id != ''){
		$admin_id_explode= explode(',',$admin_id);
		foreach($admin_id_explode as $admin_key=>$adminid){
			$admin_email_arr[$admin_key]=$this->common->get_field_name('email','accounts',$adminid);
		}
	}
	$sub_admin_email_arr= array();
	if($sub_admin_id != ''){
		$sub_admin_id_explode= explode(',',$sub_admin_id);
		foreach($sub_admin_id_explode as $sub_admin_key=>$sub_adminid){
			$sub_admin_email_arr[$sub_admin_key]=$this->common->get_field_name('email','accounts',$sub_adminid);
		}
	}
	$additional_email_arr =array();

	if($additional_email_address != ''){
		$additional_email_address_explode= explode(',',$additional_email_address);
		foreach($additional_email_address_explode as $key=>$additional_email_address){
			if($additional_email_address != ''){
				$additional_email_arr[$key]=$additional_email_address;
			}
		}
	}
	$to_email_address_array=astpp_array_merge($email_arr,$admin_email_arr,$sub_admin_email_arr,$additional_email_arr);
/*echo '<pre>';
print_r($to_email_address_array);
 exit;*/ 

	foreach($to_email_address_array as $val){
                $val =trim($val);
		if($val !=''){
			$template_type['message']=$data['template'];
			$template_type['subject']=$data['subject'];
			$act_details = $this->db_model->getSelect("*", "accounts", array('email'=>$val,"deleted"=>0));
			$count=$act_details->num_rows();
			
			$account_info=array();
			
			$default_templates_info=(array)$this->db->get_where('default_templates',array("name"=>'email_sent_support_ticket'))->first_row();
			

			$subject= $default_templates_info['subject'];
			$message= $default_templates_info['template'];
 
			$message = html_entity_decode($message);
			
			$system_config = common_model::$global_config['system_config'];
			 
			
			$ticket = $system_config['ticket_digits']; 
			
			$message = str_replace("#TICKET_ID#", sprintf('%0'.$ticket.'d', $support_ticket_number), $message);
			

			$message = str_replace("#REPLY_TYPE#", $this->get_ticket_type_message('','',$data['ticket_type']), $message);
			$message = str_replace("#NAME#", $this->get_field_name_coma_new('first_name,last_name,number','accounts',$account_data['id']),$message);
			$message = str_replace("#MESSAGE#", $data['template'], $message);
			$subject = str_replace("#TICKET_ID#", sprintf('%0'.$ticket.'d', $support_ticket_number), $subject);
			$subject = str_replace("#TICKET_SUBJECT#", $data['subject'],$subject);
			
			//$email_template_path=$this->config->item( 'email_templates' );
//echo $email_template_path; exit;
			//$email_header=file_get_contents($email_template_path."views/email_template_header.php");

			//$email_header=str_replace ( base_url(),base_url(),$email_header);
			//$message = str_replace ( "#HEADER#",$email_header,$message);
			//$email_footer=file_get_contents($email_template_path."views/email_template_footer.php");
                //$email_footer = str_replace ( "<LOGO>", base_url().'assets/images/footer_img.png', $email_footer );
               // $message = str_replace ( "#FOOTER#", $email_footer, $message);
			//echo "<pre>"; print_r($data); exit;
			if($data['departmentid']== 8){
			        $cc = $account_data['billing_email'];
			}else{
			        $cc = $account_data['support_email'];
			}
			$cc_email_arr = explode(",",$cc);
                        $cc_email_str = null;
                        foreach($cc_email_arr as $key=>$value){
                          $cc_email_str.=trim($value).",";
                        }
                        $cc_email_str = rtrim($cc_email_str,",");
             //~ echo"<pre>";print_r($val);  
                       // $department_data['email_id']= "support@vovoTelco.com";
			$subject 		= strip_tags ($subject);
			$message 		= strip_tags ($message);
			$mail_details_array= array(
						   'accountid'=>isset($data['accountid'])?$data['accountid']:$account_data['id'],
						   'date'=>gmdate("Y-m-d H:i:s"),
						   'subject'=>$subject,
						   'body'=>$message,
						   'from'=>trim($department_data['email_id']),
						   'to'=>$val,
						   'cc'=> trim($cc_email_str),
						   'attachment'=>(isset($data['file']))?$data['file']:0,
						   'status'=>1,
						   'template' => 'email_sent_support_ticket',
						   'reseller_id'=>$reseller_id,

						);
 			$count_tele_1=$this->common->get_field_name('telephone_1','accounts',array('email'=>$val));

			if($count > 0){
				$noti_result=$act_details->result_array();
				$noti_result=$noti_result[0];
			}

			 $this->db->insert("mail_details", $mail_details_array);
			$noti_result['ticket']=$ticket;
			$noti_result['ticket_id']=$support_ticket_number;
			$noti_result['ticket_subject']=$data['subject'];
			$noti_result['telephone_1']=$count_tele_1;
	

			//~ $this->common->notification_to_user('email_sent_support_ticket',$noti_result);

		}
	}  
//Send Auto Reply mail_harsh_17_02
	$auto_templates_info=(array)$this->db->get_where('default_templates',array("name"=>'auto_reply_mail_support'))->first_row();
	$auto_subject= $auto_templates_info['subject'];
	$auto_message= $auto_templates_info['template'];

	$auto_message = html_entity_decode($auto_message);
	$auto_message = str_replace("#TICKET_ID#", sprintf('%0'.$ticket.'d', $support_ticket_number), $auto_message);
	$auto_message = str_replace("#REPLY_TYPE#", $this->get_ticket_type_message('','',$data['ticket_type']), $auto_message);

	$auto_message = str_replace("#NAME#", $this->get_field_name_coma_new('first_name,last_name,number','accounts',$account_data['id']),$auto_message);
	$auto_message = str_replace("#DEPARTMENT#",$department_data['name']." (".$department_data['email_id'].")" ,$auto_message);	
	

	if(astpp_eq0($data['priority'])){
		$priorty="High";
	}else if($data['priority'] == 1){
		$priorty="Normal";
	}else{
		$priorty="Low";
	}
	$invoice_templates_info=(array)$this->db->get_where('invoice_conf',array("id"=>'1'))->first_row();
	//echo "<pre>"; print_r($data); exit;
	$auto_message = str_replace("#PRIORITY#",$priorty ,$auto_message);	
	$auto_message = str_replace("#TICKET_SUBJECT#",$data['subject'] ,$auto_message);
	$auto_message = str_replace("#MESSAGE#",$data['template'] ,$auto_message);	
	$auto_message = str_replace("#COMPANY_NAME#",$invoice_templates_info['company_name'] ,$auto_message);	
	$auto_subject = str_replace("#TICKET_ID#",sprintf('%0'.$ticket.'d', $support_ticket_number),$auto_subject);
	$auto_subject = str_replace("#TICKET_SUBJECT#", $data['subject'],$auto_subject);

	$email_template_path=$this->config->item( 'email_templates' );
	$email_header=file_get_contents($email_template_path."views/email_template_header.php");
	

	
	$email_header=str_replace ( base_url(),base_url(),$email_header);
	$auto_message = str_replace ( "#HEADER#",$email_header,$auto_message);
	
	$email_footer=file_get_contents($email_template_path."views/email_template_footer.php");
	//$email_footer = str_replace ( "<LOGO>", base_url().'assets/images/footer_img.png', $email_footer );
	$auto_message = str_replace ( "#FOOTER#", $email_footer, $auto_message);
	$auto_message = strip_tags($auto_message, "<strong>");
	$mail_details_array= array(
				'accountid'=>$account_data['id'],
				'date'=>gmdate("Y-m-d H:i:s"),
				'subject'=>$auto_subject,
				'body'=>$auto_message,
				'from'=>$email_arr[0],
				'to'=>$account_data['email'],
				'cc'=>trim($cc_email_str),
				'attachment'=>'',
				'status'=>1,
				'template' => 'auto_reply_mail_support',
				'reseller_id'=>$account_data['reseller_id'],
				);
	//~ echo "<pre>"; print_r($mail_details_array); exit;
	//Auto-Update is not required while we are creating 
	$this->db->insert("mail_details", $mail_details_array);
		
	$account_data['ticket']=$ticket;
	$account_data['ticket_id']=$support_ticket_number;
	$account_data['ticket_subject']=$data['subject'];
	//$this->supportticket_form->notification_to_user('auto_reply_mail_support',$account_data);

	return true;
	}
function get_ticket_type_message($select, $table, $call_type) {
		 
        $call_type_array = array('0' => "Open", "1"=>"Answered", '2' =>  "Customer-Reply", '3' => "On-hold", '4'=>"Progress",'5'=>"Close");
        return $call_type_array[$call_type];
    }
	function edit_supportticket($data) { 
	 
	$account_data = $this->session->userdata("accountinfo");
	if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
		$reseller_id = $account_data['id'];
	} else {
		$reseller_id = "0";
	}
	$val=$data['email'];
		
	if($val!='')
	{
		
		$template_type['message']=$data['template1'];
		$template_type['subject']=$data['subject'];
		$act_details = $this->db_model->getSelect("*", "accounts", array('email'=>$val,"deleted"=>0));
		$account_data_arr = $act_details->result_array();
		$count=$act_details->num_rows();
		$account_info=array();
		
	        $default_templates_info=(array)$this->db->get_where('default_templates',array("name"=>'email_sent_support_ticket'))->first_row();
	        $subject= $default_templates_info['subject'];
	        $message= $default_templates_info['template'];
		$message = html_entity_decode($message);
		$system_config = common_model::$global_config['system_config'];
		$ticket = $system_config['ticket_digits']; 
		
//harsh_20_02
		$support_ticket_number=$this->common->get_field_name('support_ticket_number','support_ticket', $data['id']);
		
		$message = str_replace("#TICKET_ID#",sprintf('%0'.$ticket.'d', $support_ticket_number), $message);
		$message = str_replace("#REPLY_TYPE#", $this->get_ticket_type_message('','',$data['ticket_type']), $message);
		$message = str_replace("#NAME#", $this->get_field_name_coma_new('first_name,last_name,number','accounts',$account_data['id']),$message);
		$message = str_replace("#MESSAGE#", $data['template1'], $message);
		$subject = str_replace("#TICKET_ID#", sprintf('%0'.$ticket.'d', $support_ticket_number),$subject);
		$subject = str_replace("#TICKET_SUBJECT#", $data['subject'],$subject);
		
		$email_template_path=$this->config->item( 'email_templates' );
		$email_header=file_get_contents($email_template_path."views/email_template_header.php");

		$email_header=str_replace ( base_url(),base_url(),$email_header);
                $message = str_replace ( "#HEADER#",$email_header,$message);
				
                $email_footer=file_get_contents($email_template_path."views/email_template_footer.php");
                //$email_footer = str_replace ( "<LOGO>", base_url().'assets/images/footer_img.png', $email_footer );
                $message = str_replace ( "#FOOTER#", $email_footer, $message);
				
		
		$support_data=(array)$this->db->get_where('support_ticket',array("id"=>$data['id']))->first_row();
		

		if($this->session->userdata('logintype') == -1 || $this->session->userdata('logintype') == 2){
			
		        if($data['departmentid']== 8){
					 
			        $cc = $account_data_arr[0]['billing_email'];
			}else{
			        $cc = $account_data_arr[0]['support_email'];
			}
			$cc_email_arr = explode(",",$cc);
                        $cc_email_str = null;
                        foreach($cc_email_arr as $key=>$value){
                          $cc_email_str.=trim($value).",";
                        }
                        $cc_email_str = rtrim($cc_email_str,",");
                       
		}else{
	 
		        if($data['departmentid']== 8){
			        $cc = $account_data_arr[0]['billing_email'];
			 }else{
			        $cc = $account_data_arr[0]['support_email'];
			 }
			$cc_email_arr = explode(",",$cc);
                        $cc_email_str = null;
                        foreach($cc_email_arr as $key=>$value){
                          $cc_email_str.=trim($value).",";
                        }
                        $cc_email_str = rtrim($cc_email_str,",");
		}
		$subject 		= strip_tags ($subject);
		$message 		= strip_tags ($message);
		$mail_details_array= array(
					   'accountid'=>isset($data['accountid'])?$data['accountid']:$account_data['id'],
					   'date'=>gmdate("Y-m-d H:i:s"),
					   'subject'=>$subject,
					   'body'=>$message,
					   'from'=>$this->common->get_field_name('email_id','department',array('id'=>$support_data['department_id'])),
					   'to'=>trim($val),
					   'cc'=> trim($cc_email_str),
					   'template' => 'email_sent_support_ticket',
					   'attachment'=>$data['file'],
					   'status'=>1,						   
					   'reseller_id'=>$reseller_id,

					);
/*		echo '<pre>';
		print_r($mail_details_array);
		exit;*/
		$this->db->insert("mail_details", $mail_details_array);
			$account_data['ticket']=$ticket;
			$account_data['ticket_id']=$support_ticket_number;
			$account_data['ticket_subject']=$data['subject'];
			//~ $this->supportticket_form->notification_to_user('email_sent_support_ticket',$account_data);

		//$this->email_lib->send_email($template_type,$account_info,'',$data['file'],0,1);
	}
	$update_array=array(
			'ticket_type'=>$data['ticket_type'],
			'priority'=>$data['priority'],
			'subject'=>$data['subject'],
			'last_modified_date'=>gmdate("Y-m-d H:i:s"),
			'status'=>0,
			);
	$this->db->where('id',$data['id']);
	$this->db->update("support_ticket", $update_array);
       	$support_ticket_id = $data['id'];
	$add_array_details=array(
			'support_ticket_id'=>$support_ticket_id,
			'generate_account_id'=>$account_data['id'],
			'message'=>$data['template1'],
			'attachment'=>$data['file'],
			'creation_date'=>gmdate("Y-m-d H:i:s"),
			'status'=>0,
			);
	$this->db->insert("support_ticket_details", $add_array_details);	
	return true;
	}

	function get_supportticket_list_for_cdrs() {
		if ($this->session->userdata('username') != "" && $this->session->userdata('logintype') != 2) {
			$this->db->where('reseller', $this->session->userdata('username'));
		} else {
			$this->db->where(array('reseller' => "0"));
		}
		$this->db->where('status <', 2);
		$this->db->order_by('name', 'desc');
		$query = $this->db->get("pricelists");
		$price_list = array();
		$result = $query->result_array();
		foreach ($result as $row) {
			$price_list[$row['name']] = $row['name'];
		}
		return $price_list;
	}
	function build_concat_dropdown_departmnet($select, $table, $id_where = '', $id_value = '') {
		$select_params = explode(',', $select);
		if (isset($select_params[3])) {
			$cnt_str = " $select_params[1],' ',$select_params[2],' ',' ' ";
		} else {
			$cnt_str = " $select_params[1],'  ' ";
		}
		$select = $select_params[0] . ", concat($cnt_str) as $select_params[1] ";
		$logintype = $this->session->userdata('logintype');
		if(isset($id_value['type']) && $id_value['type'] == '0,3'){
			$twhere = "type IN (".$id_value["type"].")";
			$this->db->where($twhere);
			unset($id_value['type']);
		}  
 
		$where = $id_value;
		$drp_array = $this->db_model->getSelect($select, $table, $where);
		$drp_array = $drp_array->result();
		$drp_list = array();
		foreach ($drp_array as $drp_value) {
			$drp_list[$drp_value->id] = $drp_value->name;
		}
		return $drp_list;
	}
	function get_field_name_coma_new($select, $table, $where) {
		$value = '';
		if (is_array ( $where )) {
			$where = $where;
		} else {
			$where = explode ( ',', $where );
		}
		$select1 = explode ( ',', $select );
		for($i = 0; $i < astpp_count ( $where ); $i ++) {
			$where_in = array (
					"id" => $where [$i] 
			);
			
			$field_name = $this->db_model->getSelect ( $select, $table, $where_in );
			$field_name = $field_name->result ();
			if (isset ( $field_name ) && ! empty ( $field_name )) {
				foreach ( $select1 as $sel ) {
					if ($sel == 'number') {
						$value .= "(" . $field_name [0]->$sel . ")";
					} else {
						$value .= $field_name [0]->$sel . " ";
					}
				}
			} else {
				$value = "";
			}
		}
		return rtrim ( $value, ',' );
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
