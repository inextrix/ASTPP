<?php  if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}


 class astpp_sms{
	 protected $CI; 
	public function __construct(){
		   $this->CI = & get_instance();
		   $this->CI->load->library ('common');
	}

	function send_sms_brodcast ($account_value) {

			$sid = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_sid'));

			$token = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_api_key'));


			$twilio  = new Client($sid, $token);	
			$flag=0;
						
			try {
				$phone_number = $twilio->lookups->v1->phoneNumbers($account_value['to_number'])->fetch(array("type" => "carrier"));
				$flag = 0;
			} catch(Exception $e) {
				$flag = 1;
				
			}

			if ($flag == 0) {
				if ($account_value['to_number'] != '' && $account_value['sms_body'] != '') {
				
					$apiSecret     = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_secrete_key'));
					$twilio_number = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_from_nuber'));
					$body    = str_replace("<p>","",$body);
					$body    = str_replace("</p>","",$body);
					$to      = "+".$account_value['to_number'];
					$twilio  = new Client($sid, $token);	
					$message = $twilio->messages
			                  ->create($to, 
			                           array(
			                               "body"  => $account_value['sms_body'],
			                               "from"  => $twilio_number
			                           )
			                  );
				}
			}
	}
	function send_sms($template_name,$accountinfo,$destination_number) {
		$sid           = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_sid'));
		$token         = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_api_key'));
		$apiSecret     = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_secrete_key'));
		$twilio_number =   $this->CI->common->get_field_name('value','system',array ("name" => 'sms_from_nuber'));
		$this->CI->db->select("sms_template");
		
		$template = (array)$this->CI->db->get_where('default_templates',
								array("name"=>$template_name))->first_row();

		if ($template_name == 'send_sms_otp' || $template_name == 'otp_add_user' ) {

			$numberlength = 6;
			$uniq_rendno  = random_int((int) pow(10, $numberlength - 1), (int) pow(10, $numberlength) - 1);
			$template     = str_replace("#OTP#",$uniq_rendno,$template ['sms_template']);
			$body         = $template;

			
			$otp_array= array(
					'otp_number'    => $uniq_rendno,
					'user_number'   => $accountinfo['number'],
					'account_id'    => '',
					'status'        => 0,
					'country_id'    => $accountinfo['country_id'],
					'creation_date' => $accountinfo['creation']
				);
		
			$this->CI->db->insert ("otp_number", $otp_array );
			$last_id = $this->CI->db->insert_id();	
			$body               = str_replace("<p>","",$body);
			$body               = str_replace("</p>","",$body);
			$destination_number = $accountinfo ['number'];
			$to                 = "+".$destination_number;
			$account_value['to_number'] = "+".$destination_number;
		}
		if($template_name=="change_password"){
			$message            = $template ['sms_template'];
			$message            = str_replace ( '#password#', $accountinfo['new_password'] , $message );
			$message            = str_replace("<p>","",$message);
			$message            = str_replace("</p>","",$message);
			$body               = $message;
			$destination_number = $accountinfo ['number'];	
			$to                 = "+".$destination_number;
			$uniq_rendno        = 0;
			$account_value['to_number'] = "+".$destination_number;
		}
		if($template_name=="low_balance_alert") {

			$first_name    = $accountinfo ['first_name'];
	    	$amount        = $accountinfo ['amount'];
			$to_account    = $accountinfo ['to_account'];
			$COMPANY_NAME  = 'LUGERTEL INC';
			$template      = str_replace("#to_first_name#",$first_name,$template);
			$template      = str_replace("#AMOUNT#",$amount,$template);
		   	$template      = str_replace("#COMPANY_NAME#",$COMPANY_NAME,$template);
			$body          = $template['template'];
		   	$destination_number = $accountinfo ['number'];
			$to    				= "+".$destination_number;
		    $uniq_rendno 		= 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if ($template_name == 'email_remove_did') {
			$message = $template ['sms_template'];
			$message = str_replace ( '#NAME#', $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $message );
			$message = str_replace ( '#DIDNUMBER#', $accountinfo ['did_number'], $message );
			$message = str_replace ( '#NUMBER#', $accountinfo ['number'], $message );

			$body               		= $message;
		   	$destination_number 		= $accountinfo ['number'];
			$to                 		= "+".$destination_number;
		    $uniq_rendno                = 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if ($template_name == 'add_subscription') {
			$message  = $template ['sms_template'];
			$message  = str_replace ( '#NAME#', $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $message );
			$body               		= $message;
		   	$destination_number 		= $accountinfo ['number'];
			$to                 		= "+".$destination_number;
		    $uniq_rendno                = 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if ($template_name == 'remove_subscription') {
			$message = $template ['sms_template'];
			$message = str_replace ( '#NAME#', $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $message );
			$body               		= $message;
		   	$destination_number 		= $accountinfo ['number'];
			$to                 		= "+".$destination_number;
		    $uniq_rendno                = 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if ($template_name == 'add_package') {

			$message = $template ['sms_template'];
			$message = str_replace ( '#NAME#', $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $message );
			$body               		= $message;
		   	$destination_number 		= $accountinfo ['number'];
			$to                 		= "+".$destination_number;
		    $uniq_rendno                = 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if($template_name == 'otp_forgot_password') {

			$message    = $template ['sms_template'];
		    $message    = str_replace("#OTP_NUMBER#",$accountinfo ['decode_pass'], $message);
		    $body       = $message;
		    $destination_number = $accountinfo ['number'];
		    $to                 = "+".$destination_number;
			$uniq_rendno        = 0;
			$account_value['to_number'] = "+".$destination_number;
		}
		if($template_name == 'get_user_password') {
			$message            = $template ['sms_template'];
		    $message            = str_replace("#ACCOUNT_NUMBER#", $accountinfo['number'], $message);
			$message 			= str_replace("#PASSWORD#", $accountinfo['decode_pass'] , $message);
		    $body 				= $message;
		    $destination_number = $accountinfo ['number'];
		    $to                 = "+".$destination_number;
			$uniq_rendno        = 0;
			$account_value['to_number'] = "+".$destination_number;
		}
		if ($template_name == "forgot_password") {
			$message = $template['template'];
			$message = str_replace("#PASSWORD#", $accountinfo['decode_pass'], $message);
			$message = str_replace("<p>","",$message);
			$message = str_replace("</p>","",$message);
			$body               		= $message;
		   	$destination_number 		= $accountinfo ['number'];
			$to                 		= "+".$destination_number;
		    $uniq_rendno                = 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if ($template_name == 'voip_account_refilled') {
			$message     = $template ['sms_template'];
			$currency_id = $accountinfo ['currency_id'];
			$currency    = $this->CI->common->get_field_name ( 'currency', 'currency', $currency_id );
			$message     = str_replace ( '#NAME#', $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $message );
			$message     = str_replace ( '#REFILLBALANCE#', $this->convert_to_currency ( '', '', $accountinfo ['refill_amount'] ) . ' ' . $currency, $message );
			$message     = str_replace ( '#BALANCE#', $this->convert_to_currency ( '', '', $accountinfo ['refill_amount_balance'] ) . ' ' . $currency, $message );

			$body               		= $message;
		   	$destination_number 		= $accountinfo ['number'];
			$to                 		= "+".$destination_number;
		    $uniq_rendno                = 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if ($template_name == 'email_add_did') {
			$message = $template ['sms_template'];
			if (isset ( $accountinfo ['did_maxchannels'] ) && $accountinfo ['did_maxchannels'] == "") {
					$accountinfo ['did_maxchannels'] = "Unlimited";
			}
			$message = str_replace ( '#NAME#', $accountinfo ['first_name'] . " " . $accountinfo ['last_name'], $message );
			$message = str_replace ( '#DIDNUMBER#', $accountinfo ['did_number'], $message );
			$message = str_replace ( '#COUNTRYNAME#', $accountinfo ['did_country_id'], $message );
			$message = str_replace ( '#SETUPFEE#', $accountinfo ['did_setup'], $message );
			$message = str_replace ( '#MONTHLYFEE#', $accountinfo ['did_monthlycost'], $message );
			$message = str_replace ( '#MAXCHANNEL#', $accountinfo ['did_maxchannels'], $message );
			$message = str_replace ( '#NUMBER#', $accountinfo ['number'], $message );
			$body               		= $message;
		   	$destination_number 		= $accountinfo ['number'];
			$to                 		= "+".$destination_number;
		    $uniq_rendno                = 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if($template_name == 'add_user') {
			$body               = $template['sms_template'];
			$body               = str_replace("#NUMBER#", $accountinfo['number'], $template ['sms_template']);
			$destination_number = $accountinfo ['number'];
			$to                 = "+".$destination_number;
			$uniq_rendno        = 0;
			$account_value['to_number'] = "+".$destination_number;
		}

		if($template_name == 'user_bal_transfer') {
			$first_name    = $accountinfo ['first_name'];
	    	$amount        = $accountinfo ['amount'];
			$to_account    = $accountinfo ['to_account'];
			$COMPANY_NAME  = 'LUGERTEL INC';
			$template      = str_replace("#first_name#",$first_name,$template);
			$template      = str_replace("#amount#",$amount,$template);
			$template      = str_replace("#to_account#",$to_account,$template);
		    $template      = str_replace("#COMPANY_NAME#",$COMPANY_NAME,$template);
		    $body          = $template ['sms_template'];
		    $to            = "+".$destination_number;
		    $uniq_rendno   = 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if($template_name == 'voip_account_auto_recharge') {
			$first_name         = $accountinfo ['first_name'];
	    	$auto_recharge_amt  = $accountinfo ['auto_recharge_amt'];
			$balance            = $accountinfo ['balance'];
			$to_account    		= $accountinfo ['to_account'];
			$COMPANY_NAME  		= 'LUGERTEL INC';
			$template      		= str_replace("#NAME#,",$first_name,$template);
			$template      		= str_replace("#REFILLBALANCE#",$auto_recharge_amt,$template);
			$template      		= str_replace("#BALANCE#",$balance,$template);
		   	$template      		= str_replace("#COMPANY_NAME#",$COMPANY_NAME,$template);
		    $body               = $template['sms_template'];
		    $to            		= "+".$destination_number;
		    $uniq_rendno 		= 0;
		    $account_value['to_number'] = "+".$destination_number;
		}
		if($template_name == 'user_bal_receive') {
    		$to_first_name    = $accountinfo ['to_first_name'];
	    	$amount           = $accountinfo ['amount'];
			$from_account     = $accountinfo ['from_account'];
			$COMPANY_NAME     = 'LUGERTEL INC';
	    	$template         = str_replace("#to_first_name#",$to_first_name,$template);
			$template         = str_replace("#amount#",$amount,$template);
			$template         = str_replace("#from_account#",$from_account,$template);
		    $template         = str_replace("#COMPANY_NAME#",$COMPANY_NAME,$template);
	        $body             = $template['sms_template'];
	        $to               = "+".$destination_number;
			$uniq_rendno      = 0;
			$account_value['to_number'] = "+".$destination_number;
		}
		
			$body    = str_replace("<p>","",$body);	
			$body    = str_replace("</p>","",$body);
			$twilio  = new Client($sid, $token);	
			$flag    = 0;
			try {
				$phone_number = $twilio->lookups->v1->phoneNumbers($destination_number)->fetch(array("type" => "carrier"));
				$flag=0;
			} catch(Exception $e) {
				$flag=1;
			}

				$send_mail_details = array (
					'to_number' => $account_value['to_number'],
					'sms_body'  => $body,
					'status'    => '1',
				);
				$this->CI->db->insert ( 'mail_details', $send_mail_details );
				$insert_id = $this->CI->db->insert_id();

				if ($template_name == 'send_sms_otp' || $template_name == 'otp_add_user' ){					
					$data ['otp']       = $uniq_rendno;
					$data ['insert_id'] = $insert_id;
					return $data;
				}
   				return  $insert_id;

	}
	function forgot_password_send_sms($template_name,$accountinfo) { 
		$sid           = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_sid'));
		$token         = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_api_key'));
		$apiSecret     = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_secrete_key'));
		$twilio_number = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_from_nuber'));

		if ($template_name == "forgot_password") { 
			$body = "Your old password is ".$accountinfo['decode_pass']."";
			$body= str_replace("<p>","",$body);
			$body= str_replace("</p>","",$body);
		} else {
			$body = "Your username is ".$accountinfo['number']." and  password is  ".$accountinfo['decode_pass']."";
			$body = str_replace("<p>","",$body);
			$body = str_replace("</p>","",$body);	
		}

		$destination_number = $accountinfo ['number'];		
		$to     = "+".$destination_number;
		$twilio = new Client($sid, $token);
		$flag   = 0;
			try{
				$phone_number = $twilio->lookups->v1->phoneNumbers($destination_number)->fetch(array("type" => "carrier"));
				$flag=0;
			}catch(Exception $e){
				$flag=1;
			}
		if($flag == 0){	
		$message = $twilio->messages
                  ->create($to, // to
                           array(
                               "body" =>$body,
                               "from" => $twilio_number,
			       "statusCallback" => base_url()."smsupdate/"
                           )
                  );
		}
		 return true;	
	}
}
