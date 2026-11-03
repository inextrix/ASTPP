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

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

#[\AllowDynamicProperties]
class supportticket_form {
	function __construct($library_name = '') {
		$this->CI = & get_instance();
	}
	function build_supportticket_list() {
		

if (astpp_eq0($this->CI->session->userdata('logintype')) || $this->CI->session->userdata('logintype') == 3) {
		$grid_field_arr = json_encode(array(array("<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>", "30", "", "", "", "","","false","center"),

		array(gettext("Ticket Number"), "120", "support_ticket_number", "support_ticket_number", "support_ticket_number", "set_support_ticket_id","EDITABLE","true","center"),
		array(gettext("Subject"), "252", "subject", "", "", "","EDITABLE","true","center"),
		array(gettext("Priority"), "90", "priority", "priority", "priority", "get_priority_type","","true","center"),
		array(gettext("Department"), "110", "department_id", "name", "department", "get_field_name","","true","center"),
		array(gettext("Status"), "100", "ticket_type", "ticket_type", "ticket_type", "get_ticket_type","","true","center"),
		array(gettext("Last Reply"), "130", "last_modified_date", "last_modified_date", "last_modified_date", "last_reply_time","","true","center"),
		//array(gettext("Action"), "120", "", "", "", array("EDIT" => array("url" => "supportticket/supportticket_edit/", "mode" => "single"),
		//	"DELETE" => array("url" => "supportticket/supportticket_delete/", "mode" => "single")))
			));
}else{

		$grid_field_arr = json_encode(array(array("<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>", "30", "", "", "", "","","false","center"),

		array(gettext("Ticket Number"), "90", "support_ticket_number", "support_ticket_number", "support_ticket_number", "set_support_ticket_id","EDITABLE","true","center"),
        	array(gettext("Subject"), "210", "subject", "", "", "","EDITABLE","true","center"),
		array(gettext("Account"), "100", "accountid", "first_name,last_name,number", "accounts", "get_field_name_coma_new","","true","center"),
		array(gettext("Priority"), "90", "priority", "priority", "priority", "get_priority_type","","true","center"),
		array(gettext("Department"), "110", "department_id", "name", "department", "get_field_name","","true","center"),
		array(gettext("Status"), "115", "ticket_type", "ticket_type", "ticket_type", "get_ticket_type","","true","center"),
		array(gettext("Last Reply"), "120", "last_modified_date", "last_modified_date", "last_modified_date", "last_reply_time","","true","center"),
		//array(gettext("Action"), "100", "", "", "", array("EDIT" => array("url" => "supportticket/supportticket_edit/", "mode" => "single"),
			//"DELETE" => array("url" => "supportticket/supportticket_delete/", "mode" => "single")))
			));
}

		return $grid_field_arr;
	}

	function build_grid_buttons() {
		  if($this->CI->session->userdata['logintype'] ==2 ){ 
			$buttons_json = json_encode(array(
			
			array(gettext("Create Support Ticket"), "btn btn-line-warning" ,"fa fa-plus-circle fa-lg", "button_action", "supportticket/supportticket_add/","","","create"),
			//array(gettext("Close"), "btn btn-outline-danger button_close","fa fa-times-circle fa-lg", "", "supportticket/supportticket_list/","","","close"),
			array(gettext("Delete"), "btn btn-line-danger","fa fa-times-circle fa-lg", "button_action", "supportticket/supportticket_delete_multiple/","","","delete")
			));
			
		}else{
			$buttons_json = json_encode(array(
			array(gettext("Create Support Ticket"), "btn btn-line-warning" ,"fa fa-plus-circle fa-lg", "button_action", "supportticket/supportticket_add/","","","create"),
			array(gettext("Delete"), "btn btn-line-danger","fa fa-times-circle fa-lg", "button_action", "supportticket/supportticket_delete_multiple/","","","delete")
			));
			
			//~ array(gettext("Close"), "btn btn-outline-danger button_close","ti-close mr-2", "", "supportticket/supportticket_list/",),
			 
			
			
		}
		return $buttons_json;
	}
	
	/* function notification_to_user($type, $accountinfo){
		 	
		 $where = array (
				 'name' => $type 
		 );
		 $query = $this->CI->db_model->getSelect ( "*", "default_notification_templates", $where );
 		  if($query->num_rows () > 0){
				 $query = $query->result ();
				 $query =$query[0];
				 if($query->status_code != '0'){
					 if (isset ( $accountinfo ['refill_amount'] ) && $accountinfo ['refill_amount'] != "") {
						 $refillamount = $accountinfo ['refill_amount'];
					 } else {
						 $refillamount = "0";
					 }
					//   echo "<pre>";print_r($accountinfo);exit;
					 $subject=$query->subject;
					 switch ($type) {
						 case 'email_add_user' :
							 $subject = $subject." number:".$accountinfo ['number']." password:".$accountinfo ['password'];
							 break;
						 case 'add_sip_device' :
							 $subject = $subject." number:".$accountinfo ['number']." password:".$accountinfo ['password'];
							 break;
					
						 case 'email_add_did' :
							 $subject = str_replace ( "#NUMBER#", $accountinfo ['number'], $subject );
							 $subject = str_replace ( "#DIDNUMBER#", $accountinfo ['did_number'], $subject );
							 break;
					
						 case 'email_remove_did' :
							 $subject = str_replace ( "#NUMBER#", $accountinfo ['number'], $subject );
							 $subject = str_replace ( "#DIDNUMBER#", $accountinfo ['did_number'], $subject );
							 break;
					
						 case 'auto_reply_mail_support' :
							 $subject = str_replace("#TICKET_ID#", sprintf('%0'.$accountinfo['ticket'].'d', $accountinfo['ticket_id']), $subject);
							 $subject = str_replace("#TICKET_SUBJECT#", $accountinfo['ticket_subject'],$subject);
							 $subject = $subject." Ticket created successfully.";
							 break;
						 case 'email_sent_support_ticket' :
							 $subject = str_replace("#TICKET_ID#", sprintf('%0'.$accountinfo['ticket'].'d', $accountinfo['ticket_id']), $subject);
							 $subject = str_replace("#TICKET_SUBJECT#", $accountinfo['ticket_subject'],$subject);
							 $subject = $subject." ".$type;

							 break;
						 case 'email_signup_confirmation' :
							 $accountinfo ['id']= $accountinfo ['last_inserted_id'];
							 break;
						 case 'email_forgot_confirmation' :
							 $accountinfo ['id']= $accountinfo ['last_inserted_id'];
							 break;
						 case 'email_low_balance' :							 
							 $subject = str_replace ( "#NUMBER#", $accountinfo ['number'], $subject );
							 break;
						 case 'email_new_invoice' :							 
							 $subject = str_replace ( "#INVOICE_NUMBER#", $accountinfo ['invoice_prefix'] . $accountinfo ['invoiceid'], $subject );
							 $subject= "Account number:".$accountinfo ['number']." Invoice created successfully. Invoice number:". $accountinfo ['invoice_prefix'] . $accountinfo ['invoiceid'];
							 break;
						 case 'add_subscription' :							 
							 $subject = str_replace ( "#NAME#",  $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $subject );
							 break;
						 case 'remove_subscription' :							 
							 $subject = str_replace ( "#NAME#", $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $subject );
							 break;
						 case 'add_package' :							 
							 $subject = str_replace ( "#NUMBER#",  $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $subject );
							 break;
						 case 'remove_package' :							 
							 $subject = str_replace ( "#NUMBER#", $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $subject );
							 break;
						 case 'group_create' :
							
							 $subject =$subject.". Account Number: ".$accountinfo ['number'];
							 break;
						 case 'group_edit' :
							
							 $subject =$subject.". Account Number: ".$accountinfo ['number'];
							 break;	 
					  
					  }
					 
					 $notification_array = array (
							 'accountid' => $accountinfo ['id'],
							 'message' => $subject,
							 'from' => 'calmex',
							 'to' => (isset($accountinfo['telephone_1']) )? $accountinfo ['telephone_1'] : "0",
							 'status' => "1",
							 'status_code' => $query->status_code
					 );
										 

 					  $this->CI->db->insert ( 'notification_details', $notification_array );
				 }
		 }
 		 return true;
		 
		
	 }*/
}

?>
 
