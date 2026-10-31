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
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class notification {
	protected $CI; 
	public $email = '';
	public $smtp = '';
	public $smtp_host = '';
	public $smtp_user = '';
	public $smtp_pass = '';
	public $smtp_port = '';
	public $message = '';
	public $from = '';
	public $to = '';
	public $subject = '';
	public $company_name = '';
	public $company_website = '';
	public $account_id = '';
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
		$this->CI->load->model ( 'db_model' );
		$this->CI->load->library ( 'email' );
		$this->CI->load->library ( 'session' );
	}
	


	function send_notification($notification_template,$accountid,$email_params,$attachment = "",$extra = ""){  
		$error_msg = '';
		$accountinfo = array();
		$invoiceconf = array();
		$notification_template_data = array();
		
		$query = $this->CI->db_model->getSelect ( "*", "accounts", array("id"=>$accountid) );
		$accountinfo = $query->result();
		if(isset($accountinfo) && !empty($accountinfo)){
			$accountinfo = (array)$accountinfo[0];
			$reseller_id = $accountinfo['reseller_id'] > 0 ? $accountinfo['reseller_id'] : 1;
			$where = "accountid IN ('" . $reseller_id . "')";
			$this->CI->db->where ( $where );
			$this->CI->db->select ( '*' );
			$this->CI->db->order_by ( 'accountid', 'desc' );
			$this->CI->db->limit ( 1 );
			$invoiceconf = $this->CI->db->get ( 'invoice_conf' );

			if($invoiceconf->num_rows > 0){ 
				$invoiceconf = ( array ) $invoiceconf->first_row ();
				$query = $this->CI->db_model->getSelect ( "*", "default_templates", array ('name' => $notification_template) );
				$notification_template_data = $query->result_array()[0];
				$notification_template_data['attachment'] = $attachment;
				$this->$notification_template($notification_template_data,$accountinfo,$email_params,$invoiceconf);

				if(Common_model::$global_config ['system_config']['email']==0){
					$this->send_email_notification($notification_template_data,$accountinfo,$invoiceconf);
				}

				// send_sms_notification() checks the SMS settings itself; the setting
				// "account_generate" asked here does not exist (always true, PHP notice)
				$this->send_sms_notification($notification_template_data,$accountinfo,$invoiceconf);

			}else{
				$error_msg = gettext('counld not find invoice configuration.');
			}	
		}else{
			$error_msg = gettext('unable to find account details');
		}

	}
	
	function send_email_notification($notification_template_data,$accountinfo,$invoiceconf){
		 $send_mail_details = array (
				'from' => $invoiceconf['emailaddress'],
				'to' => $accountinfo['email'],
				'subject' => $notification_template_data['subject'],
				'body' => $notification_template_data['template'],
				'accountid' => $accountinfo['id'],
				'status' => '1',
				'attachment' => $notification_template_data['attachment'],
				'reseller_id' => $accountinfo['reseller_id']
		);
		$this->CI->db->insert ( 'mail_details', $send_mail_details );
		return $this->CI->db->insert_id ();
		
	}
	function send_sms_notification($notification_template_data,$accountinfo,$invoiceconf){
		$numberlength=6;
		$uniq_rendno = random_int((int) pow(10, $numberlength - 1), (int) pow(10, $numberlength) - 1);
		// Every notification was sent as SMS through a hard-coded third-party Nexmo account (API
		// key and secret in the source), unencoded and without TLS verification. Now only if SMS
		// notifications are enabled, with the operator's own SMS credentials (Configuration ->
		// Notifications -> SMS); the seeded placeholder keys count as not configured
		// (astpp_sms_configured() in index.php).
		$sms = array();
		$query = $this->CI->db_model->getSelect ( "name,value", "system", array ( 'group_title' => 'notifications' ) );
		foreach ( $query->result_array () as $row ) {
			$sms [strtolower ( $row ['name'] )] = $row ['value'];
		}
		if (! astpp_sms_configured ( $sms )) {
			return $uniq_rendno;
		}
		$message = str_replace ( array ( "<p>", "</p>" ), "", $notification_template_data['template'] );
		$url = 'https://rest.nexmo.com/sms/json?' . http_build_query ( array (
				'api_key' => $sms ['sms_api_key'],
				'api_secret' => $sms ['sms_secret_key'],
				'from' => 'NEXMO',
				'to' => $accountinfo['number'],
				'text' => $message
		) );
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 15 );
		curl_exec ( $ch );
		return $uniq_rendno;
	}
	function get_sms_settings_details(){
		$where = array (
				'group_title' => 'notifications'
		);
		$query = $this->db_model->getSelect ( "*", "system", $where );
		$query = $query->result_array ();
		foreach ( $query as $key => $val ) {
			$tempvar = strtolower ( $val ['name'] );
			$this->$tempvar = $val ['value'];
		}
		
	}
	function otp_verification(&$notification_template_data,$accountinfo,$email_params,$invoiceconf){ 

		$email_template = $notification_template_data['template'];
		$subject = $notification_template_data['subject'];
		$sms_template = $notification_template_data['template'];
		$push_alert_template = $notification_template_data['template'];
			$email_template = str_replace ( '#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $email_template);
			$email_template = str_replace ( '#otp#',1234,$email_template );
			$email_template = str_replace ( '#COMPANY_NAME#',$invoiceconf['company_name'],$email_template);
			$notification_template_data['template'] = $email_template;

	}

	function email_add_user(&$notification_template_data,$accountinfo,$email_params,$invoiceconf){ 
		$subject = $notification_template_data['subject'];
		$email_template = $notification_template_data['template'];
		$sms_template = $notification_template_data['template'];
		$push_alert_template = $notification_template_data['template'];
			$email_template = str_replace ( '#NAME#', $accountinfo['first_name'] . " " . $accountinfo['last_name'], $email_template);
			$email_template = str_replace ( '#NUMBER#', $accountinfo['number'],$email_template);
			$email_template = str_replace ( '#PASSWORD#', $this->CI->common->password_mail_hint (), $email_template); // portal passwords are not readable
			$email_template = str_replace ( '#COMPANY_WEBSITE#',$invoiceconf['website'],$email_template);
			$email_template = str_replace ( '#COMPANY_EMAIL#',$invoiceconf['emailaddress'],$email_template);
			$email_template = str_replace ( '#COMPANY_NAME#',$invoiceconf['company_name'],$email_template);
			$notification_template_data['template'] = $email_template;

	}
	
}

