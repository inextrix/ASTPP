<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
/**
 * ****************************************************************
 * IMPORTANT!! : This is API belongs to Signup and Forgot password : IMPORTANT!!
 * ****************************************************************
 *
 * ===================================================
 * API Expected parameters :
 * ===================================================
 * String : first_name
 * String : last_name
 * String : username (for forgot password)
 *
 * ===================================================
 * API URL
 * ===================================================
 * For Balance : http://192.168.1.2:8081/api/signup
 * For Profile : http://192.168.1.2:8081/api/signup/forgot_password
 */
/**
 * Included accounts api controller as that is having all functions belogs and accounts and we are validating account for each api request.
 */
require APPPATH . '/controllers/common/account.php';
#[\AllowDynamicProperties]
class Signup extends Account {
	protected $current_date;
    protected $postdata = "";
	function __construct() {
		parent::__construct ();
		$this->load->library ( 'astpp/common' );
		$this->load->library('Form_validation');
		$this->current_date = gmdate("Y-m-d H:i:s");
	}
	function index() {
		if (empty($this->postdata['currency_id']) || empty($this->postdata['first_name']) || empty($this->postdata['telephone']) || empty($this->postdata['country_id']) || empty($this->postdata['timezone_id'])) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_param_missing' ) . "integer:currency_id,string:first_name,integer:country_id,integer:timezone_id,integer:telephone," 
			), 400 );
		}
		if (Common_model::$global_config['system_config']['enable_signup'] == '1') {
			$this->api_log->write_log ('ERROR',"signup has been disabled");
			$this->response ( array (
				'status' => false,
				'error'  => $this->lang->line('error_disable_signup')
			), 400 );
		}
		$this->api_log->write_log ( 'API URL : ',base_url()."".$_SERVER['REQUEST_URI']);
		$this->api_log->write_log ( 'Params : ', json_encode($postdata) );
		if(empty($this->postdata['email'])) {
			$this->api_log->write_log ('ERROR',"email parameter have blank value.");
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line ( 'enter_email' )
			), 400 );
		}
		if(isset($this->postdata['email'])) {
			if (!filter_var($this->postdata['email'], FILTER_VALIDATE_EMAIL)) {
				$this->api_log->write_log('ERROR',"Invalid email format");
				$this->response ( array (
					'status' => false,
					'error' => $this->lang->line( 'invalid_email_format' )
				), 400 );
			}
		}
		$this->db->where(array("number"=>$this->postdata['number'],"deleted"=>0));
		$cnt_number_account = $this->db->get('accounts')->first_row();
		if(isset($cnt_number_account) && !empty($cnt_number_account)) {
			$this->api_log->write_log ('ERROR',"duplicate number found.");
			$this->response ( array (
				'status' => false,
				'error'  => $this->lang->line ( 'signup_account_exist' )
			), 400 );
		}
		$this->db->where(array("email"=>$this->postdata['email'],"deleted"=>0));
		$cnt_email_account = $this->db->get('accounts')->first_row();
		if(isset($cnt_email_account) && !empty($cnt_email_account)) {
			$this->api_log->write_log ('ERROR',"duplicate email found.");
			$this->response ( array (
				'status' => false,
				'error'  => $this->lang->line ( 'email_already_used' )
			), 400 );
		}
		$country_id = $this->common->get_field_name('id','countrycode',array('id' => $this->postdata['country_id']));
		if(empty($country_id)){
			$this->api_log->write_log ('ERROR',"country not found");
			$this->response ( array (
				'status' => false,
				'error'  => $this->lang->line('country_not_found')
			), 400 );
		}
		$currency_id = $this->common->get_field_name('id','currency',array('id' => $this->postdata['currency_id']));
		if(empty($currency_id)){
			$this->api_log->write_log ('ERROR',"currency not found");
			$this->response ( array (
				'status' => false,
				'error'  => $this->lang->line('currency_not_found')
			), 400 );
		}
		$timezone_id = $this->common->get_field_name('id','timezone',array('id' => $this->postdata['timezone_id']));
		if(empty($timezone_id)){
			$this->api_log->write_log ('ERROR',"timezone not found");
			$this->response ( array (
				'status' => false,
				'error'  => $this->lang->line('timezone_not_found')
			), 400 );
		}
		$sip_devices_allow  = common_model::$global_config ['system_config'] ['create_sipdevice'];
		if ($sip_devices_allow != '0') {
			$this->api_log->write_log ('ERROR',"Create sip device on signup has been disabled.");
			$this->response ( array (
				'status' => false,
				'error'  => $this->lang->line ( 'something_wrong_contact_admin' )
			), 400 );
		}
		if(empty($this->postdata['telephone'] )) {
			$this->api_log->write_log ('ERROR',"telephone parameter have blank value.");
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line ( 'enter_telephone' )
			), 400 );
		}
		if(!empty($this->postdata['telephone']) && !is_numeric($this->postdata['telephone'])) {
			$this->api_log->write_log ('ERROR',"Enter valid telephone. Value must be numeric.");
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line( 'enter_valid_telephone' )
			), 400 );
		}
		if(empty($this->postdata['first_name'])) {
			$this->api_log->write_log ('ERROR',"first_name parameter have blank value.");
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line ( 'enter_first_name' )
			), 400 );
		}
		$this->db->where(array("username"=>$this->postdata['number']));
		$cnt_result_sip_devices = $this->db->get('sip_devices')->num_rows();
		if(astpp_gt($cnt_result_sip_devices, 0)) {
			$this->api_log->write_log ('ERROR',"Sip device username already exists.");
			$this->response ( array (
				'status'  => false,
				'error'    => $this->lang->line ( 'account_already_created' )
			), 400 );
		}
		if(!empty($this->postdata['number'])){
			if(strlen($this->postdata['number']) > 15){
				$this->response ( array (
					'status'  => false,
					'error'    => $this->lang->line ( 'invalid_username' )
				), 400 );
			}
			if(!$this->form_validation->integer($this->postdata['number'])){
				$this->response ( array (
					'status' => false,
					'success' =>  $this->lang->line ('invalid_username')  
				), 400 );
			}
		}
		$country_code = $this->common->get_field_name("countrycode","countrycode",array("id"=>$this->postdata['country_id']));
		$account_generate = common_model::$global_config['system_config']['telephone_as_account'];
		$numberlength = 6;
		if($this->postdata['number'] == ''){
			if (astpp_eq0($account_generate)) {
           		$this->postdata['number'] = $country_code . $this->postdata['telephone'];
        	}else{
        		 if (isset(common_model::$global_config['system_config']['minimum_accountlength_customer'])) {
                    $this->postdata['number'] =  $this->common->find_uniq_rendno_customer_length(common_model::$global_config['system_config']['minimum_accountlength_customer'], common_model::$global_config['system_config']['maximum_accountlength_customer'], 'number', 'accounts');
                } else {
                    $this->postdata['number'] = $country_code . $this->common->find_uniq_rendno_customer(common_model::$global_config['system_config']['cardlength'], 'number', 'accounts');
                }
            }
        }
        $this->postdata['password'] = $this->postdata['password'] != "" ? $this->postdata['password'] : $this->common->generate_password();
        $this->postdata['password'] = $this->common->encode($this->postdata['password']);
		$otp = rand(pow(10, astpp_n($numberlength) - 1), pow(10, $numberlength) - 1);
		$where = array('number'=>$this->postdata['number'] , 'email' => $this->postdata['email']);
		$account_unverified   = (array) $this->db->get_where('account_unverified',$where)->first_row();
		if(!empty($account_unverified)) {
			$this->response ( array (
				'status'    => false,
				'error'   => $this->lang->line ( 'account_already_exist' )
			), 400 );
		}
		$account_unverified = array(
			'number'       => !empty($this->postdata['number']) ? $this->postdata['number']:'',
			'reseller_id'  => 0,
			'telephone'    => !empty($this->postdata['telephone'])?$this->postdata['telephone']:'',
			'password'     =>  $this->postdata['password'],
			'email'        => !empty($this->postdata['email'])?$this->postdata['email']:'',
			'first_name'   => !empty($this->postdata['first_name'])?$this->postdata['first_name']:'',
			'last_name'    => !empty($this->postdata['last_name'])?$this->postdata['last_name']:'',
			'company_name' => !empty($this->postdata['company_name'])?$this->postdata['company_name']:'',
			'country_id'   => !empty($this->postdata['country_id'])?$this->postdata['country_id']:'',
			'currency_id'  => !empty($this->postdata['currency_id'])?$this->postdata['currency_id']:'',
			'timezone_id'  => !empty($this->postdata['timezone_id'])?$this->postdata['timezone_id']:'',
			'reseller_id'  => 0,
			'retries'      => (string)1,
			'otp'          => !empty($otp)?$otp:'',
			'client_ip'    => $_SERVER['REMOTE_ADDR'],
			'creation_date'=> $this->current_date 
		);
		$this->db->insert("account_unverified",$account_unverified);
		$this->api_log->write_log ( 'info', $this->db->last_query());
		$account_unverified['last_id'] = $this->db->insert_id();
		$verification_by = common_model::$global_config ['system_config']['verification_by'];
		if($verification_by == '1' || $verification_by == '2') {
			$this->_send_sms($this->postdata['number'],'signup_confirmation',$account_unverified);
		}
		if($verification_by == '0' || $verification_by == '2') {
		    $this->send_mail('0','signup_confirmation',$account_unverified);
		}
		$account_unverified['last_id'] = (string)$account_unverified['last_id'];
		$account_unverified['otp'] = (string)$account_unverified['otp'];
		unset($account_unverified['id']);
		$this->response ( array (
			'status'  => true,
			'data'    => $account_unverified,
			'success' => $this->lang->line('signup_successful')
		), 200 );
	}
	
	
	public function verify_otp(){
		$postdata = $this->postdata;
		$this->api_log->write_log ( 'Params : ', json_encode($postdata) );
		if (empty( $postdata ['number'] ) || empty ( $postdata ['last_id'] ) || empty( $postdata['last_id'] ) || empty($postdata['otp'])) {
			$this->response ( array (
				'status' => false,
				'error' => $this->lang->line ( 'error_param_missing' ) . "string:number,integer:last_id,integer:otp"
			), 400 );
		}
		$this->db->where("number", $postdata['number']);
        $account_array = (array) $this->db->get_where("accounts")->first_row();
		$where_array = array("number"=> $postdata['number'],'id'=> $postdata['last_id']);
        $this->db->where($where_array);
        $account_details = (array) $this->db->get_where("account_unverified")->first_row();
		if(empty($account_details)){
			$this->api_log->write_log ('ERROR',"This account is not found");
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line('account_not_found')
			), 400 );
		}
		if(!empty($account_array)){
			$this->api_log->write_log ('ERROR',"This account is not found");
			$this->response ( array (
				'status'  => false,
				'error'   => $this->lang->line('signup_account_exist')
			), 400 );
		}
        $otp_date = strtotime($account_details['creation_date']);
        $current_date  = gmdate("Y-m-d H:i:s");
        $insert_date = strtotime($current_date);
        $remain_time = astpp_n($insert_date) - astpp_n($otp_date);
        $message = 'false';
        $otp_expire_time = common_model::$global_config['system_config']['otp_expire'];
        $otp_expire_min = (astpp_gt($otp_expire_time, 0)) ? $otp_expire_time : 30;
        $otp_expire_sec = (astpp_n($otp_expire_min) * 60);
        if ($remain_time <= $otp_expire_sec) {
            $account_details['pin'] = '';
            $pin = Common_model::$global_config['system_config']['generate_pin'];
            if (astpp_eq0($pin)) {
                $numberlength = common_model::$global_config['system_config']['pinlength'];
                $numberlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['pinlength'];
                $account_details['pin'] = rand(pow(10, astpp_n($numberlength) - 1), pow(10, $numberlength) - 1);
            }
            if (empty($account_array)) {
				$account_details['pricelist_id'] = Common_model::$global_config['system_config']['default_signup_rategroup'];
                if (astpp_gt($account_details['reseller_id'], 0)) {
                    $this->db->select("id");
                    $pricelist_id = (array) $this->db->get_where("pricelists", array(
                        "reseller_id" => $account_details['reseller_id']
                    ))->first_row();
                    $account_details['pricelist_id'] = (!empty($pricelist_id)) ? $pricelist_id['id'] :$account_details['pricelist_id'] ;
                }
                $this->db->select("id,country_id");
                $localization_info = (array) $this->db->get_where("localization", array(
                    "country_id" => $account_details['country_id']
                ))->first_row();
                $account_details['localization_id'] = ! empty($localization_info) ? $localization_info['id'] : Common_model::$global_config['system_config']['localization_id'];

                if ($account_details['otp'] == $postdata['otp']) {
                    $account_insert_array = array(
                        "number" => $account_details['number'],
                        "reseller_id" => $account_details['reseller_id'],
                        "password" => $account_details['password'],
                        "first_name" => $account_details['first_name'],
                        "last_name" => $account_details['last_name'],
                        "company_name" => $account_details['company_name'],
                        "telephone_1" => $account_details['telephone'],
                        "email" => $account_details['email'],
                        "notification_email" => $account_details['email'],
                        "country_id" => $account_details['country_id'],
                        "currency_id" => $account_details['currency_id'],
                        "timezone_id" => $account_details['timezone_id'],
                        "localization_id" => $account_details['localization_id'],
                        "pricelist_id" => $account_details['pricelist_id'],
                        "pin" => $account_details['pin'],
                        "posttoexternal" => "0",
                        "local_call" => Common_model::$global_config['system_config']['local_call'],
						"maxchannels" => Common_model::$global_config['system_config']['maxchannels'],
						"cps" => Common_model::$global_config['system_config']['cps'],
                        "type" => "0"
                    );
                    $this->load->library('astpp/signup_lib');
                    $last_id = $this->signup_lib->create_account($account_insert_array);
                    if (! empty($last_id)) {
						$account_insert_array = $this->_token($last_id,"e",$account_insert_array);
						$this->response ( array (
							'status'    => true,
							'accountid' => (string) $last_id,
							'token' => (string)$account_insert_array['token'],
							'success'   => $this->lang->line ( 'account_created' )
						), 200 );
					}
                }else{
					$this->api_log->write_log ('ERROR',"Wrong OTP");
					$this->response ( array (
						'status'  => false,
						'error'   => $this->lang->line('correct_validation_code')
					), 400 );
				}
            } else {
                $account_arr = (array) $this->db->get_where("accounts", array(
                    "id" => $postdata['account_id']
                ))->first_row();
                $otp_verify = (array) $this->db->get_where("account_unverified", array(
                    "number" => $account_arr["number"]
                ))->first_row();
                $password = $this->common->encode($this->common->generate_password());
                if ($otp_verify['otp'] == $postdata['otp_number']) {
                    $this->db->where("id", $postdata['account_id']);
                    $this->db->update("accounts", array(
                        "password" => $password
                    ));
                    $sipdevice_array = array(
                        'dir_params' => json_encode(array(
                            "password" => $this->common->decode($password),
                            'vm-enabled' => "true",
                            "vm-password" => $this->common->decode($password),
                            "vm-mailto" => $account_arr['email'],
                            "vm-attach-file" => "true",
                            "vm-keep-local-after-email" => "true",
                            "vm-email-all-messages" => "true"
                        ))
                    );
                    $this->db->where("accountid", $postdata['account_id']);
                    $this->db->where("username", $account_arr['number']);
                    $this->db->update("sip_devices", $sipdevice_array);
                    $account_arr = (array) $this->db->get_where("accounts", array(
                        "id" => $post['account_id']
                    ))->first_row();
                    $account_arr['password'] = $this->common->decode($account_arr['password']);
                    $last_id = $this->common->mail_to_users("reset_password", $account_arr, "", "");
                    if (! empty($last_id)) {
                        $message = 'forgot';
                    }
                }
			}
		}else{
			$this->api_log->write_log ('ERROR',"OTP time is expired");
				$this->response ( array (
					'status'  => false,
					'error'   => $this->lang->line('otp_expired')
				), 400 );
			}
		}

	protected function send_mail($account_id, $temp_name, $user_data) {
		$system_config = common_model::$global_config ['system_config'];
		$EmailTemplate = (array) $this->db->get_where (  "default_templates", array('name'=>$temp_name) )->first_row();
		$sms_message = $EmailTemplate['sms_template'];
		$this->db->where('domain',$_SERVER['HTTP_HOST']);
		$this->db->or_where("accountid",1);
		$this->db->order_by("id","asc");
		$this->db->limit(1);
		$otp_expire_time = common_model::$global_config['system_config']['otp_expire'];
       	$otp_expire_time = astpp_gt($otp_expire_time, 0) ? $otp_expire_time : '30';
		$invoice_arr     = (array)$this->db->get_where("invoice_conf")->first_row();
		$company_email   = $invoice_arr ['emailaddress'];
		$company_website = $invoice_arr ["website"];
		$company_name    = $invoice_arr ["company_name"];
		$TemplateData    = array ();
		$template        = $EmailTemplate['template'];
		$otp_expire_time = common_model::$global_config['system_config']['otp_expire'];
        $otp_expire_min = (astpp_gt($otp_expire_time, 0)) ? $otp_expire_time : 30;
		if(!empty($EmailTemplate)) {
			if($EmailTemplate['name'] == 'signup_confirmation') {
				$TemplateData['subject'] = $EmailTemplate['subject'];		
				$template = str_replace ( '#NAME#', $user_data ['first_name'] . " " . $user_data ['last_name'], $template );
				$template = str_replace ( '#OTP#', $user_data ['otp'], $template);
                $template = str_replace('#TIME#', $otp_expire_time, $template);
				$template = str_replace ( '#COMPANY_WEBSITE#', $company_website, $template);
				$template = str_replace ( '#COMPANY_EMAIL#', $company_email, $template);
				$template = str_replace ( '#COMPANY_NAME#', $company_name, $template);
				$sms_message = str_replace('#FIRST_NAME#', $user_data['first_name'], $sms_message);
        		$sms_message = str_replace('#OTP#', $user_data['otp'], $sms_message);
        		$sms_message = str_replace('#TIME#', $otp_expire_min, $sms_message);
        		$sms_message = str_replace('#COMPANY_NAME#', $company_name, $sms_message);
			}
		}
		$TemplateData['subject']=strip_tags ($TemplateData['subject']);
        $template=strip_tags($template);
		$email_array = array (
			'accountid'     => !empty($account_id) ? $account_id : '0',
			'date'          => $this->current_date,
			'subject'       => $TemplateData ['subject'],
			'body'          => $template,
			'from'          => $invoice_arr ['emailaddress'],
			'to'            => $user_data ['email'],
			'status'        => '1',
			'attachment'    => '',
			'template'      => '',
			'reseller_id'   => '0',
			'to_number'     => '0',
			'to_number' => isset($user_data['telephone']) ? $user_data['telephone'] : '',
			'sms_body' => $sms_message
		);
		$this->db->insert ( "mail_details", $email_array );
		return true;
	}

	private function _send_sms($number,$template_name,$user_data) {
		$this->db->where('domain',$_SERVER['HTTP_HOST']);
		$this->db->or_where("accountid",1);
		$this->db->order_by("id","asc");
		$this->db->limit(1);
		$invoice_arr    = (array)$this->db->get_where("invoice_conf")->first_row();
		$query          = $this->db_model->getSelect("*", "default_templates", array('name'=>'signup_confirmation'))->result();
		$sms_message    = $query[0]->sms_template;
		$sms_api_key    = Common_model::$global_config ['system_config'] ['sms_api_key'];
		$sms_secret_key = Common_model::$global_config ['system_config'] ['sms_secret_key'];
		$otp_expire_time = Common_model::$global_config ['system_config'] ['otp_expire'];
		$otp_expire_time = astpp_gt($otp_expire_time, 0) ? $otp_expire_time : '30';
		if($template_name == 'signup_confirmation') {
			$sms_message = str_replace('#FIRST_NAME#', $user_data['first_name'], $sms_message);
			$sms_message = str_replace('#OTP#', $user_data['otp'], $sms_message);
			$sms_message = str_replace('#COMPANY_NAME#', $invoice_arr['company_name'], $sms_message);
			$sms_message = str_replace('#TIME#',$otp_expire_time, $sms_message);
		}
		$url = 'https://rest.nexmo.com/sms/json?' . http_build_query([
			'api_key'    => $sms_api_key,
			'api_secret' => $sms_secret_key,
			'to'         => $number,
			'from'       =>'ABC',
			'text'       => "".$sms_message."".$user_data['otp'].""
		]);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		return true;		
	}
}
