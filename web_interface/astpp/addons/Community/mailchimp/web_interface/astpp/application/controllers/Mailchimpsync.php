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

//*######################################################################
//
//	CRON : 0 0 * * * cd /var/www/html/astpp/cron/ && php cron.php MailchimpSync
//
//*######################################################################

#[\AllowDynamicProperties]
class Mailchimpsync extends CI_Controller {

	public $Error_flag = false;

	public $fp = "";

	function __construct() {		
		parent::__construct ();
		//if (! defined ( 'CRON' ))
		//	exit ();
		$this->load->model ( "db_model" );		
		$this->load->library('MailChimp');			
		if(file_exists(Common_model::$global_config ['system_config'] ['log_path']."astpp-mailchimp.log"))
		$this->fp = fopen(Common_model::$global_config ['system_config'] ['log_path']."astpp-mailchimp.log", "a+");
	}
	function sync_contacts() {

		//Get mailchimp list id 
		$list_id = Common_model::$global_config ['system_config'] ['mailchimp_audience_id'];

		//Get accounts list 
		$where = array ();
		$entity_array = array (
				"0",
				"1",
				"3" 
		);
		$this->db->where_in ( "type", $entity_array );
		$query = $this->db_model->getSelect ( "*", "accounts", $where );

		if ($query->num_rows () > 0) {
			$account_data = $query->result_array ();
			foreach ( $account_data as $data_key => $accountinfo ) {
				$subscribed_flag = 'subscribed';
				if($accountinfo['status'] == 1) $subscribed_flag = 'unsubscribed';
				if($accountinfo['deleted'] == 1) $subscribed_flag = 'cleaned';
				//Check if email is not blank
				if ($accountinfo['email'] != '')
				{
					if ($this->Error_flag) fwrite($this->fp, "[".gmdate('Y-m-d H:i:s')."] ".$accountinfo['email']."\n");

					//Call mailchimp api to insert astpp contacts to mailchimp
					$result = $this->mailchimp->put("lists/$list_id/members/".md5(strtolower($accountinfo['email'])), [
		                'email_address' => $accountinfo['email'],
		                'merge_fields' => ['FNAME'=>$accountinfo["first_name"], 'LNAME'=>$accountinfo["last_name"]],
		                'status'        => $subscribed_flag,
	            	]);
	            	if ($this->Error_flag) fwrite($this->fp, "[".gmdate('Y-m-d H:i:s')."] ".print_r($result,true)."\n");	        		
	        	}
				//Check if email is not blank
				if ($accountinfo['notification_email'] != '')
				{
					if ($this->Error_flag) fwrite($this->fp, "[".gmdate('Y-m-d H:i:s')."] ".$accountinfo['email']."\n");

					//Call mailchimp api to insert astpp contacts to mailchimp
					$result = $this->mailchimp->put("lists/$list_id/members/".md5(strtolower($accountinfo['notification_email'])), [
		                'email_address' => $accountinfo['notification_email'],
		                'merge_fields' => ['FNAME'=>$accountinfo["first_name"]."(Notify Email)", 'LNAME'=>$accountinfo["last_name"]],
		                'status'        => $subscribed_flag,
	            	]);
	            	if ($this->Error_flag) fwrite($this->fp, "[".gmdate('Y-m-d H:i:s')."] ".print_r($result,true)."\n");	        		
	        	}
			}
		}
		exit ();
	}
} 
