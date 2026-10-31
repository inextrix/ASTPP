<?php  if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

 class astpp_sms{
	 protected $CI; 
	public function __construct(){
		   $this->CI = & get_instance();
		   $this->CI->load->library ('Api_log');
		   $this->CI->load->library ('common');
	}
	
	function send_sms($template_name,$accountinfo,$destination_number){


		$sid       = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_sid'));
		$token     = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_api_key'));
		$apiSecret = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_secrete_key'));
		$twilio_number =   $this->CI->common->get_field_name('value','system',array ("name" => 'sms_from_nuber'));

		$this->CI->db->select("template");
		$template = (array)$this->CI->db->get_where('default_templates',
								array("name"=>$template_name))->first_row();

		if ($template_name == 'send_sms_otp' || $template_name == 'otp_add_user' ){
				$numberlength=6;
				$uniq_rendno = random_int((int) pow(10, $numberlength - 1), (int) pow(10, $numberlength) - 1);
				$body ="Your otp number is ".$uniq_rendno." ";
				
				$otp_array= array(
						'otp_number' =>$uniq_rendno,
						'user_number' =>$accountinfo['number'],
						'account_id'=>'',
						'status'=>0,
						'country_id' => $accountinfo['country_id'],
						'creation_date' => $accountinfo['creation']
					);
			
				$this->CI->db->insert ("otp_number", $otp_array );
				$last_id = $this->CI->db->insert_id();	
				$body= str_replace("<p>","",$body);
				$body= str_replace("</p>","",$body);

				
				$to    ="+".$accountinfo ['number'];

		}
		if($template_name=="low_balance_alert"){
			$first_name    = $accountinfo ['first_name'];
		    	$amount        = $accountinfo ['amount'];
				$to_account    = $accountinfo ['to_account'];
				$COMPANY_NAME  = 'LUGERTEL INC';
				$template      = str_replace("#to_first_name#",$first_name,$template);
				$template      = str_replace("#AMOUNT#",$amount,$template);
			   	 $template      = str_replace("#COMPANY_NAME#",$COMPANY_NAME,$template);
				$body          = $template['template'];
			   	$to            = "+".$accountinfo['number'];
			    	$uniq_rendno = 0;

		}
		if($template_name == 'sms_add_user'){
			$body = $template['template'];
			$body = str_replace("#NUMBER#", $accountinfo['number'], $body);
			$to   = "+".$destination_number;
		}
		if($template_name == 'sms_bal_transfer') {

				$first_name    = $accountinfo ['first_name'];
		    	$amount        = $accountinfo ['amount'];
				$to_account    = $accountinfo ['to_account'];
				$COMPANY_NAME  = 'LUGERTEL INC';
				$template      = str_replace("#first_name#",$first_name,$template);
				$template      = str_replace("#amount#",$amount,$template);
				$template      = str_replace("#to_account#",$to_account,$template);
			    $template      = str_replace("#COMPANY_NAME#",$COMPANY_NAME,$template);
			    $body          = $template['template'];
			    $to            = "+".$destination_number;
			    $uniq_rendno = 0;
		}
		if($template_name == 'voip_account_auto_recharge_sms') {

				$first_name    = $accountinfo ['first_name'];
		    		$auto_recharge_amt  = $accountinfo ['auto_recharge_amt'];
				$balance=  = $accountinfo ['balance'];
				$to_account    = $accountinfo ['to_account'];
				$COMPANY_NAME  = 'LUGERTEL INC';
				$template      = str_replace("#NAME#,",$first_name,$template);
				$template      = str_replace("#REFILLBALANCE#",$auto_recharge_amt,$template);
				$template      = str_replace("#BALANCE#",$balance,$template);
			   	$template      = str_replace("#COMPANY_NAME#",$COMPANY_NAME,$template);
			    $body          = $template['template'];
			    $to            = "+".$destination_number;
			    $uniq_rendno = 0;
		}
		if($template_name == 'sms_bal_receive') {

	    		$to_first_name    = $accountinfo ['to_first_name'];
		    	$amount           = $accountinfo ['amount'];
				$from_account     = $accountinfo ['from_account'];
				$COMPANY_NAME     = 'LUGERTEL INC';

		    	$template    = str_replace("#to_first_name#",$to_first_name,$template);
				$template    = str_replace("#amount#",$amount,$template);
				$template    = str_replace("#from_account#",$from_account,$template);
			    $template    = str_replace("#COMPANY_NAME#",$COMPANY_NAME,$template);

		        $body          = $template['template'];
		        $to            = "+".$destination_number;
			$uniq_rendno = 0;
		}
		$body    = str_replace("<p>","",$body);	
		$body    = str_replace("</p>","",$body);	

		$twilio = new Client($sid, $token);	

		$message = $twilio->messages
                  ->create($to, 
                           array(
                               "body" =>$body,
                               "from" => $twilio_number,
			       "statusCallback" => base_url()."smsupdate/"
                           )
                  );

		if ($template_name == 'send_sms_otp' || $template_name == 'otp_add_user' ){
			$update_arr = array("sid"=>$message->sid,"sms_status"=>$message->status);
			$this->CI->db->where("id",$last_id);
			$this->CI->db->update("otp_number",$update_arr);
		 }		
		 return $uniq_rendno;
	}

	function forgot_password_send_sms($template_name,$accountinfo){
		$sid       = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_sid'));
		$token     = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_api_key'));
		$apiSecret = $this->CI->common->get_field_name('value','system',array ("name" => 'sms_secrete_key'));
		$twilio_number =   $this->CI->common->get_field_name('value','system',array ("name" => 'sms_from_nuber'));
		if($template_name == "forgot_password"){
		$body = "Your old password id ".$accountinfo['decode_pass']."";
		$body= str_replace("<p>","",$body);
		$body= str_replace("</p>","",$body);
		}else{
		$body = "Your username is ".$accountinfo['number']." and  password is  ".$accountinfo['decode_pass']."";
		$body= str_replace("<p>","",$body);
		$body= str_replace("</p>","",$body);	
		}
		$to    ="+".$accountinfo ['number'];
		$twilio = new Client($sid, $token);	
		$message = $twilio->messages
                  ->create($to, 
                           array(
                               "body" =>$body,
                               "from" => $twilio_number,
			       "statusCallback" => base_url()."smsupdate/"
                           )
                  );

		 return true;

	}
}
