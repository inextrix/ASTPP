<?php  (! defined('BASEPATH')) and exit('No direct script access allowed');

#[\AllowDynamicProperties]
class DepartmentMail extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model("db_model");
		$this->load->library("astpp/common");
	}
	
    function index(){
	$EmailArr = array();
	/* connect to gmail */
	$department_details = $this->db_model->getSelect("*", "department", array('status'=>0));
	//$department_details = $this->db_model->getSelect("*", "department", array('id'=>9));
	$department_count=$department_details->num_rows();
	$department_arr=$department_details->result_array();

	foreach($department_arr as $department_value){
//	if($department_value['email_id'] == 'harshmpatel207@gmail.com'){
	$hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
	//$hostname = "{imap.gmail.com:993/imap/ssl/novalidate-cert}";
	$username = $department_value['smtp_user'];
	$password = $this->common->decode($department_value['smtp_password']);
	/* try to connect */
	$inbox = imap_open($hostname,$username,$password, NULL, 1) or die('Cannot connect to Gmail: ' . imap_last_error());
	/* grab emails */
	$emails = imap_search($inbox,'UNSEEN');
	//echo "<pre>";
	$this->log("Receiver Email address  :".json_encode($emails));
	/* if emails are returned, cycle through each... */
	if($emails) {
		/* begin output var */
		$output = '';
		/* put the newest emails on top */
		rsort($emails);

		/* for every email... */
		foreach($emails as $email_number) {
			$overview = imap_fetch_overview($inbox, $email_number, 0);
			$email_subject = trim($overview[0]->subject);
			$email_sender = trim($overview[0]->from);
			$email_receiver = trim($overview[0]->to);
			$this->log("Email sender ".$email_sender." Receiver ".$email_receiver);
			$explode_subject= explode(']',$email_subject);
			$explode_ticket_details=explode('[Ticket ID:',$explode_subject[0]);
			$this->log("Export Ticket details  :".json_encode($explode_ticket_details));
			if(isset($explode_ticket_details[1]) && !empty($explode_ticket_details[1])){
				$this->log("First IF");
				$sender= explode('<',$email_sender);
				if(isset($sender[1])){
					$from_email_id= str_replace('>','',$sender[1]);
				}else{
					$from_email_id= $email_sender;
				}
				$account_data=(array)$this->db->get_where('accounts',array("email"=>$from_email_id,"deleted"=>0,"status"=>0))->first_row();
				$this->log("Accountdata :".json_encode($account_data));
				//print_r($account_data);exit;
				$receiver= explode('<',$email_receiver);
				if(isset($receiver[1])){
					$receiver_email_id= str_replace('>','',$receiver[1]);
				}else{
					$receiver_email_id= $email_sender;
				}
				$this->log("Receiver Email address  :".$receiver_email_id);
				$this->log("From Email address  :".$from_email_id);
				if(empty($account_data)){
					$this->log("If");			
					if($receiver_email_id == $from_email_id){
						continue;
					}
					$support_ticket_id=ltrim($explode_ticket_details[1], '0');
					$support_ticket_id = str_replace(' ','',$support_ticket_id);
					$support_ticket_data=(array)$this->db->get_where('support_ticket',array("support_ticket_number"=>$support_ticket_id))->first_row();
					$this->log("Support ticket last query  :".$this->db->last_query());
					$update_array=array();
					$account_data=(array)$this->db->get_where('accounts',array("id"=>$support_ticket_data['accountid'],"deleted"=>0,"status"=>0))->first_row();
					$this->log("Accounts last query  :".$this->db->last_query());
					$account_id=$account_data['id'];
					$parent_info = $this->common->get_parent_info($account_id,0);
					$this->log("Get Parent last query  :".$this->db->last_query());
					if(strcmp($parent_info,"1,") == 0) {
						$str_close_flag = rtrim($parent_info,",");
					} else {
						$str_close_flag = $parent_info. "1";
					}
					$this->log("Close Flag  :".$str_close_flag);
					$this->log("Support ticket data :".$support_ticket_data);
					if($support_ticket_data['ticket_type'] == 5 && $support_ticket_data['close_ticket_display_flag'] == 1){
//14_09_harsh
						// add 
						$this->db->where('support_ticket_number',$support_ticket_id);
						$this->db->update("support_ticket", array('close_ticket_display_flag'=>$str_close_flag));
					}
					$update_array=array(
								'ticket_type'=>2,
								'last_modified_date'=>gmdate("Y-m-d H:i:s"),
								'status'=>0,
								'close_ticket_display_flag'=>$str_close_flag
							);
					$email_body = trim(imap_fetchbody($inbox, $email_number, 1));
					$this->log("Email Body :".$email_body);
					if($email_body != '' && !empty($email_body)){
						$this->db->where('support_ticket_number',$support_ticket_id);
						$this->db->update("support_ticket", $update_array);
						$this->log("Support Ticket update query  :".$this->db->last_query());
					}
					$add_array_details=array(
								'support_ticket_id'=>$support_ticket_data['id'],
								'generate_account_id'=>$support_ticket_data['accountid'],
								'message'=>$email_body,
								'attachment'=>'',
								'creation_date'=>gmdate("Y-m-d H:i:s"),
								'status'=>0,
							);
					$this->db->insert("support_ticket_details", $add_array_details);
					$this->log("Support Ticket insert query  :".$this->db->last_query());
					$receiver= explode('<',$email_receiver);
					if(isset($receiver[1])){
						$receiver_email_id= str_replace('>','',$receiver[1]);
					}else{
						$receiver_email_id= $email_sender;
					}
					$this->log("Receiver email address  :".$receiver_email_id);
					$default_templates_info=(array)$this->db->get_where('default_templates',array("name"=>'email_sent_support_ticket'))->first_row();
					$this->log("Default template query :".$this->db->last_query());
					$subject= $default_templates_info['subject'];
					$message= $default_templates_info['template'];
					
					$message = html_entity_decode($message);
					$this->log("Subject :".$subject);
					$this->log("Message1 :".$message);
					$message = str_replace("#TICKET_ID#", sprintf('%06d', $support_ticket_id), $message);
					$message = str_replace("#REPLY_TYPE#", $this->_get_ticket_type_message('','',2), $message);

					$message = str_replace("#CLIENT_NAME#", $this->common->get_field_name_coma_new('first_name,last_name,number','accounts',$account_data['id']),$message);
					$message = str_replace("#MESSAGE#", $email_body, $message);
					$this->log("Message2 :".$message);	
					$mail_details_array= array(
								'accountid'=>$support_ticket_data['accountid'],
								'date'=>gmdate("Y-m-d H:i:s"),
								'subject'=>$email_subject,
								'body'=>$message,
								'from'=>$receiver_email_id,
								'to'=>$account_data['email'],
								'attachment'=>'',
								'status'=>1,
								'reseller_id'=>$account_data['reseller_id'],
			  				   );
					$this->db->insert("mail_details", $mail_details_array);
					$this->log("Mail details insert query :".$this->db->last_query());
				}else{
					$this->log("Else");
					$receiver= explode('<',$email_receiver);
					if(isset($receiver[1])){
						$receiver_email_id= str_replace('>','',$receiver[1]);
					}else{
						$receiver_email_id= $email_sender;
					}
					$this->log("Receiver email id :".$receiver_email_id);
					$department_data=(array)$this->db->get_where('department',array("email_id"=>$receiver_email_id))->first_row();	
					$this->log("Department select query email id :".$this->db->last_query());
					$send_mail_email_id= array();
					$admin_id= $department_data['admin_id_list'];
					$this->log("Admin ID :".json_encode($admin_id));
					$sub_admin_id= $department_data['sub_admin_id_list'];
					$this->log("Sub Admin ID :".json_encode($sub_admin_id));
					$additional_email_address= $department_data['additional_email_address'];
					$this->log("Additional Email ID :".json_encode($additional_email_address));
					$admin_email_arr= array();
					if($admin_id != ''){
						$admin_id_explode= explode(',',$admin_id);
						foreach($admin_id_explode as $admin_key=>$adminid){
							$admin_email_arr[$admin_key]=$this->common->get_field_name('email','accounts',$adminid);
						}
					}
					$this->log("Admin ID2 :".json_encode($admin_email_arr));
					$sub_admin_email_arr= array();
					if($sub_admin_id != ''){
						$sub_admin_id_explode= explode(',',$sub_admin_id);
						foreach($sub_admin_id_explode as $sub_admin_key=>$sub_adminid){
							$sub_admin_email_arr[$sub_admin_key]=$this->common->get_field_name('email','accounts',$sub_adminid);
						}
					}
					$this->log("Sub Admin ID2 :".json_encode($sub_admin_email_arr));
					$additional_email_arr =array();
					if($additional_email_address != ''){
						$additional_email_address_explode= explode(',',$additional_email_address);
						foreach($additional_email_address_explode as $key=>$additional_email_address){
							if($additional_email_address != ''){
								$additional_email_arr[$key]=$additional_email_address;
							}
						}
					}
					$this->log("Additional Email ID2 :".json_encode($additional_email_arr));
					$to_email_address_array=astpp_array_merge($admin_email_arr,$sub_admin_email_arr,$additional_email_arr);
					$this->log("To email address array :".json_encode($to_email_address_array));
					$support_ticket_id=ltrim($explode_ticket_details[1], '0');
					$support_ticket_id = str_replace(' ','',$support_ticket_id);
					if(isset($support_ticket_id) && $support_ticket_id !=''){
						$support_ticket_data=(array)$this->db->get_where('support_ticket',array("support_ticket_number"=>$support_ticket_id))->first_row();
						$this->log("support_ticket query :".$this->db->last_query());
						$account_id=$account_data['id'];
						$parent_info = $this->common->get_parent_info($account_id,0);
						$this->log("Parent Info query :".$this->db->last_query());
						if(strcmp($parent_info,"1,") == 0) {
							$str_close_flag = rtrim($parent_info,",");
						} else {
							$str_close_flag = $parent_info. "1";
						}
						$this->log("str_close_flag :".$str_close_flag);
						if($support_ticket_data['ticket_type'] == 5 && $support_ticket_data['close_ticket_display_flag'] == 1){
	//14_09_harsh
							// add 
							$this->db->where('support_ticket_number',$support_ticket_id);
							$this->db->update("support_ticket", array('close_ticket_display_flag'=>$str_close_flag));
							$this->log("support_ticket update query1 :".$this->db->last_query());
						}
						$update_array=array(
									'ticket_type'=>2,
									'last_modified_date'=>gmdate("Y-m-d H:i:s"),
									'status'=>0,
									'close_ticket_display_flag'=>$str_close_flag
								);
						$email_body = trim(imap_fetchbody($inbox, $email_number, 1));
						if($email_body != '' && !empty($email_body)){
							$this->db->where('support_ticket_number',$support_ticket_id);
							$this->db->update("support_ticket", $update_array);
							$this->log("support_ticket update query2 :".$this->db->last_query());
						}
	/*echo "\n=======\n".$this->db->last_query(); 
	exit;*/
						$from_email_accountid=$this->common->get_field_name('id','accounts',array('email'=>$from_email_id,"deleted"=>0));
						if($from_email_accountid == ''){
							$from_email_accountid=1;
						}
						$add_array_details=array(
									'support_ticket_id'=>$support_ticket_data['id'],
									'generate_account_id'=>$from_email_accountid,
									'message'=>$email_body,
									'attachment'=>'',
									'creation_date'=>gmdate("Y-m-d H:i:s"),
									'status'=>0,
								);
						$this->db->insert("support_ticket_details", $add_array_details);
						$this->log("support_ticket_details insert query :".$this->db->last_query());
						$receiver= explode('<',$email_receiver);
						if(isset($receiver[1])){
							$receiver_email_id= str_replace('>','',$receiver[1]);
						}else{
							$receiver_email_id= $email_sender;
						}
						$this->log("Receiver email id : ".$receiver_email_id);
						$account_data=(array)$this->db->get_where('accounts',array("id"=>$support_ticket_data['accountid']))->first_row();
						$this->log("accounts select query2 :".$this->db->last_query());
						if($email_receiver !=''){
							$template_type['message']=$email_body;
							$template_type['subject']=$email_subject;
							$act_details = $this->db_model->getSelect("*", "accounts", array('email'=>$from_email_id,"deleted"=>0));
							$this->log("accounts select query3 :".$this->db->last_query());
							$count=$act_details->num_rows();
							$account_info=array();
							$default_templates_info=(array)$this->db->get_where('default_templates',array("name"=>'email_sent_support_ticket'))->first_row();
							$subject= $default_templates_info['subject'];
							$message= $default_templates_info['template'];
							$this->log("subject122 :".$subject);
							$this->log("Message11 :".$message);
							$message = html_entity_decode($message);

							$message = str_replace("#TICKET_ID#", sprintf('%06d', $support_ticket_id), $message);
							$message = str_replace("#REPLY_TYPE#", $this->_get_ticket_type_message('','',2), $message);

							$message = str_replace("#CLIENT_NAME#", $this->common->get_field_name_coma_new('first_name,last_name,number','accounts',$account_data['id']),$message);
							$message = str_replace("#MESSAGE#", $email_body, $message);
							$this->log("Message22 :".$message);
							$subject = str_replace("#TICKET_ID#",sprintf('%06d', $support_ticket_id),$subject);
							$subject = str_replace("#TICKET_SUBJECT#", $email_subject,$subject);

	//print_r($to_email_address_array);
							$this->log("subject22 :".$message);
							$this->log("To email address :".json_encode($to_email_address_array));
							foreach($to_email_address_array as $receiver_emailid){
								$mail_details_array= array(
											'accountid'=>$support_ticket_data['accountid'],
											'date'=>gmdate("Y-m-d H:i:s"),
											'subject'=>$email_subject,
											'body'=>$message,
											'from'=>$receiver_email_id,
											'to'=>$receiver_emailid,
											'attachment'=>'',
											'status'=>1,
											'reseller_id'=>$account_data['reseller_id'],
											);
								$this->db->insert("mail_details", $mail_details_array);
	//echo $this->db->last_query()."\n";
								$this->log("mail_details insert query12 :".$this->db->last_query());
							}
						}
					}
				}
					exit;
			}else{
				$this->log("Else2s");
				$sender= explode('<',$email_sender);
				if(isset($sender[1])){
					$from_email_id= str_replace('>','',$sender[1]);
				}else{
					$from_email_id= $email_sender;
				}
				$this->log("From Email ID ".$from_email_id);
				$account_data=(array)$this->db->get_where('accounts',array("email"=>$from_email_id,"deleted"=>0,"status"=>0))->first_row();
//echo $this->db->last_query();
//print_r($account_data); exit;
				$this->log("Account select query :".$this->db->last_query());
				if(!empty($account_data)){
					$receiver= explode('<',$email_receiver);
					if(isset($receiver[1])){
						$receiver_email_id= str_replace('>','',$receiver[1]);
					}else{
						$receiver_email_id= $email_sender;
					}
					$this->log("Receiver_email_id ID ".$receiver_email_id);
					$send_mail_email_id= array();
					$admin_id= $department_value['admin_id_list'];
					$sub_admin_id= $department_value['sub_admin_id_list'];
					$additional_email_address= $department_value['additional_email_address'];
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
					$to_email_address_array=astpp_array_merge($admin_email_arr,$sub_admin_email_arr,$additional_email_arr);
					$this->log("To email address array ".json_encode($to_email_address_array));
					$email_body = trim(imap_fetchbody($inbox, $email_number, 1));
					$this->log("Email Body ".$email_body);
					$support_ticket_number = $this->common->find_uniq_rendno('6', 'number', 'accounts');
					$add_array=array(
							'ticket_type'=>2,
							'priority'=>1,
							'accountid'=>$account_data['id'],
							'reseller_id'=>$account_data['reseller_id'],
							'subject'=>$email_subject,
							'creation_date'=>gmdate("Y-m-d H:i:s"),
							'last_modified_date'=>gmdate("Y-m-d H:i:s"),
							'department_id'=>$department_value['id'],
							'support_ticket_number'=>$support_ticket_number,
							'status'=>0,
						   );
					$this->db->insert("support_ticket", $add_array);
					$this->log("Support ticket insert query ".$this->db->last_query());
					$support_ticket_id = $this->db->insert_id();
					$add_array_details=array(
								'support_ticket_id'=>$support_ticket_id,
								'generate_account_id'=>$account_data['id'],
								'message'=>$email_body,
								'attachment'=>'', //remaining
								'creation_date'=>gmdate("Y-m-d H:i:s"),
								'status'=>0,
							    );
					$this->db->insert("support_ticket_details", $add_array_details);
					$this->log("Support ticket details insert query ".$this->db->last_query());
					$this->log("Email Receiver ".$email_receiver);
					if($email_receiver !=''){
						$template_type['message']=$email_body;
						$template_type['subject']=$email_subject;
						$act_details = $this->db_model->getSelect("*", "accounts", array('email'=>$from_email_id,"deleted"=>0));
						$count=$act_details->num_rows();
						$account_info=array();
						$default_templates_info=(array)$this->db->get_where('default_templates',array("name"=>'email_sent_support_ticket'))->first_row();
						$subject= $default_templates_info['subject'];
						$message= $default_templates_info['template'];

						$message = html_entity_decode($message);

						$message = str_replace("#TICKET_ID#", sprintf('%06d', $this->common->get_field_name('support_ticket_number','support_ticket',$support_ticket_id)), $message);
						$message = str_replace("#REPLY_TYPE#", $this->_get_ticket_type_message('','',2), $message);

						$message = str_replace("#CLIENT_NAME#", $this->common->get_field_name_coma_new('first_name,last_name,number','accounts',$account_data['id']),$message);
						$message = str_replace("#MESSAGE#", $email_body, $message);

						$subject = str_replace("#TICKET_ID#",sprintf('%06d', $this->common->get_field_name('support_ticket_number','support_ticket',$support_ticket_id)),$subject);
						$subject = str_replace("#TICKET_SUBJECT#", $email_subject,$subject);
						$message = strip_tags($message);
						foreach($to_email_address_array as $receiver_emailid){
							$mail_details_array= array(
										'accountid'=>$account_data['id'],
										'date'=>gmdate("Y-m-d H:i:s"),
										'subject'=>$subject,
										'body'=>$message,
										'from'=>$receiver_email_id,
										'to'=>$receiver_emailid,
										'attachment'=>'',
										'status'=>1,
										'reseller_id'=>$account_data['reseller_id'],
									    );
							$this->db->insert("mail_details", $mail_details_array);
							$this->log("Mail Details query222 ".$this->db->last_query());
						}
						$auto_templates_info=(array)$this->db->get_where('default_templates',array("name"=>'auto_reply_mail_support'))->first_row();
						$auto_subject= $auto_templates_info['subject'];
						$auto_message= $auto_templates_info['template'];

						$auto_message = html_entity_decode($auto_message);

						$auto_message = str_replace("#TICKET_ID#", $this->common->get_field_name('support_ticket_number','support_ticket',$support_ticket_id), $auto_message);
						$auto_message = str_replace("#REPLY_TYPE#", $this->_get_ticket_type_message('','',2), $auto_message);

						$auto_message = str_replace("#CLIENT_NAME#", $this->common->get_field_name_coma_new('first_name,last_name,number','accounts',$account_data['id']),$auto_message);
						$auto_subject = str_replace("#TICKET_ID#",sprintf('%06d',$this->common->get_field_name('support_ticket_number','support_ticket',$support_ticket_id)),$auto_subject);
						$auto_subject = str_replace("#TICKET_SUBJECT#", $email_subject,$auto_subject);

						$priorty="Normal";
						$invoice_templates_info=(array)$this->db->get_where('invoice_conf',array("id"=>'1'))->first_row();
						$auto_message = str_replace("#PRIORITY#",$priorty ,$auto_message);	
						$auto_message = str_replace("#TICKET_SUBJECT#",$email_subject ,$auto_message);
						$auto_message = str_replace("#MESSAGE#","" ,$auto_message);	
						$auto_message = str_replace("#COMPANY_NAME#",$invoice_templates_info['company_name'] ,$auto_message);
						$auto_message = str_replace("#NAME#",$this->common->get_field_name_coma_new('first_name,last_name','accounts',$account_data['id']) ,$auto_message);
						$department_data=(array)$this->db->get_where('department',array('email_id'=>$department_value['email_id']))->first_row();	
						$auto_message = str_replace("#DEPARTMENT#",$department_data['name'],$auto_message);	


						$mail_details_array= array(
									'accountid'=>$account_data['id'],
									'date'=>gmdate("Y-m-d H:i:s"),
									'subject'=>$auto_subject,
									'body'=>$auto_message,
									'from'=>$receiver_email_id,
									'to'=>$from_email_id,
									'attachment'=>'',
									'status'=>1,
									'reseller_id'=>$account_data['reseller_id'],
								    );
						$this->db->insert("mail_details", $mail_details_array);
						$this->log("Mail Details query11 ".$this->db->last_query());
					}
				}
			}
		}
	} 
	/* close the connection */
	imap_close($inbox);
	if(!empty($EmailArr)){
	foreach($EmailArr as $EmailNum => $EmailData){
	$this->Email2Fax($EmailData['senderemail'],$EmailData['sender'],$EmailData['receiver'],$EmailData['fax_title'],$EmailData['attachment']);
	}
	}
	//echo "<pre>"; print_r($EmailArr); exit;
	}
//	}
   }
	function log($message){
		$log_value=0;
		if($log_value==1){
			$fp=fopen("/var/log/astpp/astpp_support_ticket.log","a+");
			fwrite($fp,$message."\n");
			fclose($fp);
		}
	}
	private function _get_ticket_type_message($select, $table, $call_type) {
        $call_type_array = array('0' => "Open", "1"=>"Answered", '2' =>  "Customer-Reply", '3' => "On-hold", '4'=>"Progress",'5'=>"Close");
        return $call_type_array[$call_type];
    }
}


