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
class Signup extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('signup_model');
        $this->load->helper('captcha');
        $this->load->library('astpp/common');
        $this->load->library('astpp/notification');
        $this->load->library('encrypt');
        $this->load->model('Astpp_common');
        $data['row'] = $this->signup_model->get_rate();
    }

    function index($key = "")
    {
        if (Common_model::$global_config['system_config']['enable_signup'] == 1) {
            redirect(base_url());
        }

        if ($key != '') {
            $this->session->set_userdata("signup_key", $key);
        }
        $data = array();
        $add_array = $this->input->post();
        if ((! empty($add_array)) && ((empty($add_array['telephone'])) || (empty($add_array['email'])) || (empty($add_array['userCaptcha'])) || (empty($add_array['first_name'])))) {
            redirect(base_url() . "login/");
        }
        $count = 0;
        $currency_info = (array) $this->db->get_where("currency", array(
            "currency" => Common_model::$global_config['system_config']['base_currency']
        ))->first_row();
        $data['country_id'] = Common_model::$global_config['system_config']['country'];
        $data['currency_id'] = $currency_info['id'];
        $data['timezone_id'] = Common_model::$global_config['system_config']['default_timezone'];

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $domain = "https://" . $_SERVER["HTTP_HOST"] . "/";
        } else {
            $domain = "http://" . $_SERVER["HTTP_HOST"] . "/";
        }
        if (! empty($add_array)) {

            $current_date = gmdate('Y-m-d H:i:s');
            $account_generate = common_model::$global_config['system_config']['telephone_as_account'];
            $country_code = $this->common->get_field_name("countrycode", "countrycode", array(
                "id" => $add_array['country_id']
            ));
            $error = "";
            $userCaptcha = $this->input->post('userCaptcha');

            $attempt = 1;
            $last_id = 0;
            $cnt_result = 0;
            $number = '';
            $insert_array = array();
            if (! is_numeric($add_array['telephone'])) {
                $data['error']['telephone'] = "<span class='text-danger'>".gettext('Telephone number is only numeric')."</span";
            }

            $reseller_id = '0';
            $this->db->select('accountid');
            $http_host = $_SERVER["HTTP_HOST"];
            $this->db->where("domain LIKE '%$domain%'");
            $this->db->or_where("domain LIKE '%$http_host%'");
            $account_id = (array) $this->db->get('invoice_conf')->first_row();
            // ASTPPCOM-1352_gautam_start 
            $signup_key = $this->session->userdata('signup_key');
            if ($signup_key != '') {
                 $reseller_id = $this->common->decode($this->common->decode_params(trim($signup_key)));
            }
            // ASTPPCOM-1352_gautam_end
            if ((! empty($account_id)) && ($account_id['accountid'] > 0)) {
                $reseller_id = $account_id['accountid'];
                $this->db->select("type");
                $type = (array) $this->db->get_where("accounts", array(
                    "id" => $account_id['accountid']
                ))->first_row();
                if (($type['type'] == - 1) || ($type['type'] == 2)) {
                    $reseller_id = 0;
                }
                $where_account = array(
                    "deleted" => "0",
                    "status" => "0",
                    "id" => $account_id['accountid']
                );
                $this->db->select('*');
                $this->db->where($where_account);
                $account_arr = (array) $this->db->get('accounts')->first_row();
            }
            if ($userCaptcha != $this->session->userdata('captchaWord')) {
                $data['error']['captcha_err'] = "<span class='text-danger'>".gettext("Please enter valid Captcha code")."</span>";
            } else {
                if (isset($account_arr) && empty($account_arr)) {
                    $data['error']['account_deleted'] = "<span class='text-danger'>".gettext("Please contact to administrator")."</span>";
                } else {
                    if ($account_generate == 0) {
                        $number = $country_code . $add_array['telephone'];
                        $where = array(
                            'number' => $number
                        );
                        $this->db->select('count(id) as count');
                        $this->db->where(array(
                            "number" => $number,
                            "deleted" => '0'
                        ));
                        $cnt_result = (array) $this->db->get('accounts')->first_row();
                    } else {
                        if (isset(common_model::$global_config['system_config']['minimum_accountlength'])) {
                            $number = $country_code . $this->common->find_uniq_rendno_customer_length(common_model::$global_config['system_config']['minimum_accountlength'], common_model::$global_config['system_config']['maximum_accountlength'], 'number', 'accounts');
                        } else {
                            $number = $country_code . $this->common->find_uniq_rendno_customer(common_model::$global_config['system_config']['cardlength'], 'number', 'accounts');
                        }

                        $where = array(
                            'email' => $add_array['email']
                        );
                        $this->db->select('count(id) as count');
                        $this->db->where(array(
                            "email" => $add_array['email'],
                            "deleted" => '0'
                        ));
                        $cnt_result = (array) $this->db->get('accounts')->first_row();
                    }
                    if ($cnt_result['count'] > 0) {
                        if ($account_generate == 0) {
                            $data['error']['account_number'] = "<span class='text-danger'>".gettext("Requested number is already exist")."</span>";
                        } else {
                            $data['error']['account_email'] = "<span class='text-danger'>".gettext("Requested email is already exist")."</span>";
                        }
                    } else {

                        $new_account_details = (array) $this->db->get_where("account_unverified", $where)->first_row();

                        if (! empty($new_account_details)) {
                            $last_id = $new_account_details['id'];
                            if ($new_account_details['retries'] == common_model::$global_config['system_config']['allow_retires']) {
                                $data['error']['account_deleted'] = "<span class='text-danger'>Please contact to administrator</span>";
                            } else {
                                $attemp = ($new_account_details['retries'] + 1);
                                $this->db->where($where);
                                $data = array(
                                    'creation_date' => $current_date,
                                    'retries' => $attemp
                                );
                                $this->db->update("account_unverified", $data);
                            }
                            $insert_array = $new_account_details;
                        } else {
                            $password = $this->common->password_store($this->common->generate_password()); // the user sets the password via the link in the welcome mail

                            $insert_array = array(
                                'number' => $number,
                                'reseller_id' => $reseller_id,
                                'telephone' => $add_array['telephone'],
                                'password' => $password,
                                'email' => $add_array['email'],
                                'first_name' => $add_array['first_name'],
                                'last_name' => $add_array['last_name'],
                                'company_name' => $add_array['company_name'],
                                'country_id' => $add_array['country_id'],
                                'currency_id' => $add_array['currency_id'],
                                'timezone_id' => $add_array['timezone_id'],
                                'retries' => $attempt,
                                'otp' => '',
                                'client_ip' => $_SERVER['REMOTE_ADDR'],
                                'creation_date' => $current_date
                            );
                            $this->db->insert("account_unverified", $insert_array);
                            $last_id = $this->db->insert_id();
                        }
                    }
                }
            }
            if (empty($data['error'])) {
                $verification_by = common_model::$global_config['system_config']['verification_by'];
                $numberlength = common_model::$global_config['system_config']['pinlength'];
                $numberlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['pinlength'];
                $insert_array['otp'] = random_int((int) pow(10, $numberlength - 1), (int) pow(10, $numberlength) - 1);
                $this->db->where("id", $last_id);
                $this->db->update("account_unverified", array(
                    "otp" => $insert_array['otp']
                ));
                if ($verification_by == '1' || $verification_by == '2') {
                    $this->send_sms($number, 'signup_confirmation', $insert_array);
                }
                if ($verification_by == '0' || $verification_by == '2') {

                    $this->send_mail('0', 'signup_confirmation', $insert_array,$number);
                }

                $signup_key = $this->session->userdata('key');
                $this->db->select('number,creation_date,email');
                $account_number = (array) $this->db->get_where("account_unverified", array(
                    "id" => $last_id
                ))->first_row();
                if ($account_generate == 1) {

                    $number = $account_number['number'];
                }
                $email = $account_number['email'];
                $this->load->helper('cookie');
                set_cookie('post_info', json_encode(array(
                    'number' => $number,
                    'email' => $email,
                    'creation_date' => $account_number['creation_date'],
                    'account_id' => $last_id,
                    'key' => $signup_key
                )), '20');

                redirect(base_url() . "otp_verification/");
                exit();
            } else {
                $random_number = substr(number_format(time() * random_int(0, mt_getrandmax()), 0, '', ''), 0, 6);
                $vals = array(
                    'word' => $random_number,
                    'img_path' => getcwd() . '/assets/captcha/',
                    'img_url' => base_url() . 'assets/captcha/',
                    'img_width' => '243',
                    'img_height' => '50',
                    'expiration' => '3600'
                );
                $data['captcha'] = create_captcha($vals);
                $this->session->set_userdata('captchaWord', $data['captcha']['word']);
                $data['country_id'] = $add_array['country_id'];
                $data['company_name'] = $add_array['company_name'];
                $data['telephone'] = $add_array['telephone'];
                $data['email'] = $add_array['email'];
                $data['first_name'] = $add_array['first_name'];
                $data['last_name'] = $add_array['last_name'];
                $data['currency_id'] = $add_array['currency_id'];
                $data['timezone_id'] = $add_array['timezone_id'];
                $data['error'] = $data['error'];
            }
        } else {

            $random_number = substr(number_format(time() * random_int(0, mt_getrandmax()), 0, '', ''), 0, 6);
            $vals = array(
                'word' => $random_number,
                'img_path' => getcwd() . '/assets/captcha/',
                'img_url' => base_url() . 'assets/captcha/',
                'img_width' => '243',
                'img_height' => '50',
                'expiration' => '3600'
            );
            $data['captcha'] = create_captcha($vals);
            $this->session->set_userdata('captchaWord', $data['captcha']['word']);

            $random_number = substr(number_format(time() * random_int(0, mt_getrandmax()), 0, '', ''), 0, 6);
            if ($key != '') {
                $reseller_id = $this->common->decode($this->common->decode_params(trim($key)));
                $data['key'] = $key;
                $account_result = (array) $this->db->get_where('accounts', array(
                    'id' => $reseller_id,
                    'deleted' => '1',
                    'status' => '1'
                ))->first_row();
                $account = (array) $this->db->get_where("accounts", array(
                    "id" => $reseller_id
                ))->first_row();
                $email = $account['email'];
                if (! empty($account_result)) {
                    $this->signup_inactive($email);
                }
            }
            $this->db->select("*");
            $http_host = $_SERVER["HTTP_HOST"];
            $this->db->where("domain LIKE '%$domain%'");
            $this->db->or_where("domain LIKE '%$http_host%'");
            $res = $this->db->get_where("invoice_conf");
            $logo_arr = $res->result();
            $data['user_logo'] = (isset($logo_arr[0]->logo) && $logo_arr[0]->logo != "") ? $logo_arr[0]->logo : "logo.png";
            $data['website_header'] = (isset($logo_arr[0]->website_title) && $logo_arr[0]->website_title != "") ? $logo_arr[0]->website_title : "ASTPP - Open Source Voip Billing Solution";
            $data['website_footer'] = (isset($logo_arr[0]->website_footer) && $logo_arr[0]->website_footer != "") ? $logo_arr[0]->website_footer : "Inextrix Technologies Pvt. Ltd All Rights Reserved.";
            // The signup page must not change the role of a session. It wrote the account type
            // behind the signup key into "userlevel_logintype", also into the session of a
            // logged-in user; that value is used for permission and data-scope decisions.
            // Only the login sets it; branding only for visitors without a session.
            if ($this->session->userdata('user_login') != TRUE) {
                $this->session->set_userdata('user_logo', $data['user_logo']);
                $this->session->set_userdata('user_header', $data['website_header']);
                $this->session->set_userdata('user_footer', $data['website_footer']);
            }
        }
        if (isset($key) && $key != '') {
            $data['key_unique'] = $key;
        }
        $this->db->select('*');
        $countrycode_info = $this->db->get('countrycode')->result_array();
        $countrycode_array = array();
        foreach ($countrycode_info as $key => $value) {
            $countrycode_array[$value['id']] = $value['countrycode'];
        }
        $data['countrycode_array'] = $countrycode_array;
        $this->db->where("name",'url');
            $this->db->where("field_type",'default_system_input');
            $this->db->where("group_title",'term_and_condition');
            $this->db->or_where("field_type",'textarea');
            $res = $this->db->get_where("system");
            $logo_arr = $res->result_array();
           
         foreach ($logo_arr as $key => $value) {
               if($value['name']=='url')
               {
                    $data['term_and_condition_url']=$value['value'];
               }
               if($value['field_type']=='textarea')
               {
                   $data['term_and_condition_text']=$value['value'];
                }   
            }
        $this->load->view('view_signup', $data);
    }

    public function otp_verification()
    {
        $this->load->helper('cookie');

        $data = get_cookie('post_info');

        if (! empty($data)) {
            $data = json_decode($data);
            delete_cookie('post_info');
            $this->load->view('view_otp_signup', $data);
        } else {
            redirect(base_url() . "signup/");
        }
    }

    public function check_captcha($str)
    {
        $word = $this->session->userdata('captchaWord');
        if (strcmp(strtoupper($str), strtoupper($word)) == 0) {
            return true;
        } else {
            $this->form_validation->set_message('check_captcha', gettext('Please enter correct words!'));
            return false;
        }
    }

    function terms_check()
    {
        if (isset($_POST['agreeCheck'])) {
            return true;
        }
        $this->form_validation->set_message('terms_check', gettext('THIS IS REQUIRED!'));
        return false;
    }

    function send_sms($number, $template_name, $user_data)
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $domain = "https://" . $_SERVER["HTTP_HOST"] . "/";
        } else {
            $domain = "http://" . $_SERVER["HTTP_HOST"] . "/";
        }
        $http_host = $_SERVER["HTTP_HOST"];
        $this->db->where("domain LIKE '%$domain%'");
        $this->db->or_where("domain LIKE '%$http_host%'");
        $this->db->or_where("accountid", 1);
        $this->db->order_by("id", "desc");
        $this->db->limit(1);
        $invoice_arr = (array) $this->db->get_where("invoice_conf")->first_row();
        $query = $this->db_model->getSelect("*", "default_templates", array(
            'name' => 'signup_confirmation'
        ))->result();
        if($query['is_sms_enable'] == '0'){
        $sms_message = $query[0]->sms_template;
        $sms_api_key = Common_model::$global_config['system_config']['sms_api_key'];
        $sms_secret_key = Common_model::$global_config['system_config']['sms_secret_key'];
        $otp_expire_time = common_model::$global_config['system_config']['otp_expire'];
        $otp_expire_min = ($otp_expire_time > 0) ? $otp_expire_time : 30;
        if ($template_name == 'signup_confirmation') {
            $sms_message = str_replace('#FIRST_NAME#', $user_data['first_name'], $sms_message);
            $sms_message = str_replace('#OTP#', $user_data['otp'], $sms_message);
            $sms_message = str_replace('#TIME#', $otp_expire_min, $sms_message);
            $sms_message = str_replace('#COMPANY_NAME#', $invoice_arr['company_name'], $sms_message);
        }
        if ($template_name == 'forgot_password_confirmation') {
            $sms_message = str_replace('#FIRST_NAME#', $user_data['first_name'], $sms_message);
            $sms_message = str_replace('#OTP#', $user_data['otp'], $sms_message);
            $sms_message = str_replace('#TIME#', $otp_expire_min, $sms_message);
            $sms_message = str_replace('#COMPANY_NAME#', $invoice_arr['company_name'], $sms_message);
        }
	$url = 'https://rest.nexmo.com/sms/json?' . http_build_query([
   			     'api_key' =>$sms_api_key,
   			     'api_secret' => $sms_secret_key,
        		     'to' => $number,
       			     'from' =>'ABC',
   			     'text' => "".$sms_message."".$user_data['otp'].""
   			 ]);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
	$verification_by = common_model::$global_config['system_config']['verification_by'];
	if($verification_by != "2"){
		$email_array = array(
		    'accountid' => isset($account_id) ? $account_id : "",
		    'date' => gmdate('Y-m-d H:i:s'),
		    'subject' => $query[0]->subject,
		    'body' => "",
		    'from' => $invoice_arr['emailaddress'],
		    'to' => $user_data['email'],
		    'status' => "1",
		    'attachment' => '',
		    'template' => '',
		    'reseller_id' => '0',
		    'to_number' => $number,
		    'sms_body' => $sms_message
		);
	$this->db->insert("mail_details", $email_array);
	}
        return true;
    }
}

   function send_mail($account_id, $temp_name, $user_data,$number="")
    {
        $system_config = common_model::$global_config['system_config'];
	$query = $this->db_model->getSelect("*", "default_templates", array(
            'name' => $temp_name
        ))->result();
        $sms_message = $query[0]->sms_template;
        $EmailTemplate = (array) $this->db->get_where("default_templates", array(
            'name' => $temp_name
        ))->first_row();
        if($EmailTemplate['is_email_enable'] == '0'){
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $domain = "https://" . $_SERVER["HTTP_HOST"] . "/";
        } else {
            $domain = "http://" . $_SERVER["HTTP_HOST"] . "/";
        }
        $http_host = $_SERVER["HTTP_HOST"];
        $this->db->where("domain LIKE '%$domain%'");
        $this->db->or_where("domain LIKE '%$http_host%'");
        $this->db->or_where("accountid", 1);
        $this->db->order_by("id", "desc");
        $this->db->limit(1);
        $invoice_arr = (array) $this->db->get_where("invoice_conf")->first_row();
        $company_email = $invoice_arr['emailaddress'];
        $company_website = $invoice_arr["website"];
        $company_name = $invoice_arr["company_name"];
        $otp_expire_time = common_model::$global_config['system_config']['otp_expire'];
        $otp_expire_min = ($otp_expire_time > 0) ? $otp_expire_time : 30;
        $TemplateData = array();
        $template = $EmailTemplate['template'];
        if (! empty($EmailTemplate)) {
            if ($EmailTemplate['name'] == 'signup_confirmation') {
		$sms_message = str_replace('#FIRST_NAME#', $user_data['first_name'], $sms_message);
		$sms_message = str_replace('#OTP#', $user_data['otp'], $sms_message);
		$sms_message = str_replace('#TIME#', $otp_expire_min, $sms_message);
		$sms_message = str_replace('#COMPANY_NAME#', $company_name, $sms_message);
                $TemplateData['subject'] = $EmailTemplate['subject'];
                $template = str_replace('#NAME#', $user_data['first_name'] . " " . $user_data['last_name'], $template);
                $template = str_replace('#OTP#', $user_data['otp'], $template);
                $template = str_replace('#TIME#', $otp_expire_min, $template);

                $template = str_replace('#COMPANY_WEBSITE#', $company_website, $template);
                $template = str_replace('#COMPANY_EMAIL#', $company_email, $template);
                $template = str_replace('#COMPANY_NAME#', $company_name, $template);
            }
            if ($EmailTemplate['name'] == 'forgot_password_confirmation') {
		$sms_message = str_replace('#FIRST_NAME#', $user_data['first_name'], $sms_message);
		$sms_message = str_replace('#OTP#', $user_data['otp'], $sms_message);
		$sms_message = str_replace('#TIME#', $otp_expire_min, $sms_message);
		$sms_message = str_replace('#COMPANY_NAME#', $company_name, $sms_message);
                $TemplateData['subject'] = $EmailTemplate['subject'];
                $template = str_replace('#NAME#', $user_data['first_name'] . " " . $user_data['last_name'], $template);
                $template = str_replace('#OTP#', $user_data['otp'], $template);
                $template = str_replace('#TIME#', $otp_expire_min, $template);

                $template = str_replace('#COMPANY_WEBSITE#', $company_website, $template);
                $template = str_replace('#COMPANY_EMAIL#', $company_email, $template);
                $template = str_replace('#COMPANY_NAME#', $company_name, $template);
            }
        }
	$TemplateData['subject']=strip_tags ($TemplateData['subject']);
        $template=strip_tags($template);
	$verification_by = common_model::$global_config['system_config']['verification_by'];
	if($verification_by == "0"){
		$sms_message = "";
	}
        $email_array = array(
            'accountid' => $account_id,
            'date' => gmdate('Y-m-d H:i:s'),
            'subject' => $TemplateData['subject'],
            'body' => $template,
            'from' => $invoice_arr['emailaddress'],
            'to' => $user_data['email'],
            'status' => "1",
            'attachment' => '',
            'template' => '',
            'reseller_id' => '0',
            'to_number' => isset($number) ? $number : '',
            'sms_body' => $sms_message
        );
        $this->db->insert("mail_details", $email_array);
        return true;
    }
}

    function successpassword()
    {
        // end of the password reset (view_successpassword expected undefined variables)
        $this->load->view('view_reset_password', array(
            'done' => true
        ));
    }

    function send_otp($username, $country_id, $account_id, $mg_type)
    {
        $numberlength = common_model::$global_config['system_config']['pinlength'];
        $numberlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['pinlength'];
        $uniq_rendno = random_int((int) pow(10, $numberlength - 1), (int) pow(10, $numberlength) - 1);
        $otp_array = array(
            'otp_number' => $uniq_rendno,
            'user_number' => $username,
            'account_id' => $account_id,
            'status' => 0,
            'country_id' => $country_id,
            'type' => $mg_type,
            'creation_date' => gmdate('Y-m-d H:i:s')
        );
        $this->db->insert("otp_number", $otp_array);
    }

    function signup_otp($number, $account_id)
    {
        $data['username'] = $number;
        $data['account_id'] = $account_id;
        $this->load->view('view_otp_signup', $data);
    }

    function signup_inactive($email)
    {
        $data['email'] = $email;
        $this->load->view('view_signup_inactive', $data);
        exit();
    }

    function check_otp()
    {
        // only the signup uses this check (the password reset has its own code
        // check in reset_password); with the signup switched off it creates no account.
        if (Common_model::$global_config['system_config']['enable_signup'] == 1) {
            echo 'false';
            return;
        }
        $post = $this->input->post();
        $current_date = gmdate('Y-m-d H:i:s');
        $this->db->where("number", $post['number']);
        $this->db->where("email", $post['email']);
        $account_array = (array) $this->db->get_where("accounts")->first_row();
        $this->db->where("number", $post['number']);
        $this->db->where("email", $post['email']);
        $account_details = (array) $this->db->get_where("account_unverified")->first_row();
        $otp_date = strtotime($account_details['creation_date']);
        $insert_date = strtotime($current_date);
        $remain_time = $insert_date - $otp_date;
        $message = 'false';
        $otp_expire_time = common_model::$global_config['system_config']['otp_expire'];
        $otp_expire_min = ($otp_expire_time > 0) ? $otp_expire_time : 30;
        $otp_expire_sec = ($otp_expire_min * 60);
        if ($remain_time <= $otp_expire_sec) {
            $account_details['pin'] = '';
            $pin = Common_model::$global_config['system_config']['generate_pin'];
            if ($pin == 0) {
                $numberlength = common_model::$global_config['system_config']['pinlength'];
                $numberlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['pinlength'];
                $account_details['pin'] = random_int((int) pow(10, $numberlength - 1), (int) pow(10, $numberlength) - 1);
            }
            if (empty($account_array)) {
                $account_details['pricelist_id'] = Common_model::$global_config['system_config']['default_signup_rategroup'];
                if ($account_details['reseller_id'] > 0) {
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
                if ($account_details['otp'] == $post['otp_number']) {
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
                    $this->signup_lib->create_account($account_insert_array);
                    $last_id = $this->db->insert_id();
                    if (! empty($last_id)) {
                        $message = 'success';
                    }
                }
                echo $message;
            } else {
                // the old password reset (account id from the POST, code never
                // void, new password mailed and set on the SIP device) - see reset_password
                echo $message;
            }
        } else {
            echo $message;
        }
    }

    function resend_otp()
    {
        $post = $this->input->post();
        // signup only; a new reset code comes from confirmpassword
        if (Common_model::$global_config['system_config']['enable_signup'] == 1) {
            return;
        }
        if (! empty($post)) {
            $acc_id = $post['account_id'];
            $otp_number = $post['otp_number'];
            $number = $post['number'];
            $email = $post['email'];
            $this->db->where("number", $number);
            $this->db->where("email", $email);
            if ($this->db->count_all_results("accounts") > 0) {
                return; // existing account: not a signup
            }
            $numberlength = common_model::$global_config['system_config']['pinlength'];
            $numberlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['pinlength'];
            $uniq_rendno = random_int((int) pow(10, $numberlength - 1), (int) pow(10, $numberlength) - 1);
            $otp_array = array(
                'otp' => $uniq_rendno,
                'creation_date' => date('Y-m-d H:i:s')
            );
            $this->db->where("number", $number);
            $this->db->where("email", $email);
            $this->db->update("account_unverified", $otp_array);
            $this->db->where("number", $number);
            $this->db->where("email", $email);
            $user_data = (array) $this->db->get_where("account_unverified")->first_row();
            $verification_by = common_model::$global_config['system_config']['verification_by'];
            $this->db->where("number", $number);
            $this->db->where("email", $email);
            $account_arr = (array) $this->db->get_where("accounts")->first_row();
            if ($verification_by == '1' || $verification_by == '2') {

                if (empty($account_arr)) {
                    $this->send_sms($user_number, 'signup_confirmation', $user_data);
                } else {
                    $this->send_sms($user_number, 'forgot_password_confirmation', $user_data);
                }
            }
            if ($verification_by == '0' || $verification_by == '2') {
                if (empty($account_arr)) {
                    $this->send_mail($acc_id, 'signup_confirmation', $user_data);
                } else {
                    $this->send_mail($acc_id, 'forgot_password_confirmation', $user_data);
                }
            }
        } else {
            redirect(base_url() . "signup/");
        }
    }

    /*
     * Password reset without account enumeration.
     *
     * "Forgot password" answered with different messages for unknown, deleted and
     * inactive accounts and showed the code page (with the account id in a hidden field) only
     * for existing accounts. The code lived in account_unverified (with a copy of the password),
     * check_otp took the account id from the POST, the code stayed valid after use and could be
     * guessed without limit; the new password was mailed and also set as password of the SIP
     * device named like the account. Now:
     *  - confirmpassword shows the same page with the same text for every input; only for an
     *    active customer/reseller/provider account with this number AND e-mail address a code is
     *    stored and mailed (template forgot_password_confirmation);
     *  - codes: RESET_CODE_DIGITS digits, stored as HMAC in password_resets, valid otp_expire
     *    minutes (default 30), RESET_MAX_ATTEMPTS attempts each, at most RESET_MAX_CODES codes per
     *    account and hour, void after use;
     *  - reset_password takes code and new password in one request and answers every failure
     *    with the same message. The user chooses the password (no password in a mail), the
     *    account gets the notification new_password, SIP devices are not touched;
     *  - the answers take at least RESET_MIN_TIME seconds, so the mail work for an existing
     *    account is not measurable. The web server should also limit these requests per address.
     */
    const RESET_CODE_DIGITS = 8;
    const RESET_MAX_ATTEMPTS = 5;
    const RESET_MAX_CODES = 3;
    const RESET_MIN_TIME = 0.5;

    function forgotpassword()
    {
        $this->load->view('view_forgotpassword');
    }

    function confirmpassword()
    {
        $start = microtime(true);
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect(base_url() . 'forgotpassword/');
        }
        $number = trim((string) $this->input->post('number'));
        $email = trim((string) $this->input->post('email'));
        if ($number === '' || $email === '') {
            $data['value'] = array(
                'number' => $number,
                'email' => $email
            );
            $data['error']['number'] = "<label class='error_label'><span id='error_mail' class='text-danger'>" . gettext("Please enter your account number and e-mail address.") . "</span></label>";
            $this->load->view('view_forgotpassword', $data);
            return;
        }
        $account = $this->reset_account($number, $email);
        if (! empty($account)) {
            $this->reset_issue_code($account);
        }
        $this->reset_wait($start);
        $this->load->view('view_reset_password', array(
            'number' => $number,
            'email' => $email,
            'sent' => true
        ));
    }

    function reset_password()
    {
        $start = microtime(true);
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect(base_url() . 'forgotpassword/');
        }
        $this->load->library('form_validation');
        $number = trim((string) $this->input->post('number'));
        $email = trim((string) $this->input->post('email'));
        $code = preg_replace('/\s+/', '', (string) $this->input->post('code'));
        $password = (string) $this->input->post('password');
        $confirm = (string) $this->input->post('confirm_password');
        // the password rules first: they say nothing about the account and cost no code attempt
        // (compared with the account number and e-mail address as entered, not with stored data)
        $this->load->library('astpp/password_policy');
        $rule = $this->password_policy->check($password, array($number, $email));
        if ($password === '' || $password !== $confirm) {
            $error = gettext("Please enter the same new password in both fields.");
        } elseif ($rule !== '') {
            $error = $this->password_policy->message($rule, gettext('The new password'));
        } else {
            $account = ($number !== '' && $email !== '') ? $this->reset_account($number, $email) : array();
            if (! empty($account) && $this->reset_check_code($account, $code)) {
                $this->db->where('id', $account['id']);
                $this->db->update('accounts', array(
                    'password' => $this->common->password_store($password)
                ));
                $account['password'] = ''; // the notice never carries a password
                $this->common->mail_to_users('new_password', $account);
                log_message('error', 'password reset: new password set for account ' . (int) $account['id']);
                redirect(base_url() . 'signup/successpassword/');
            }
            $error = gettext("The code is wrong or has expired. Please check it or request a new code.");
        }
        $this->reset_wait($start);
        $this->load->view('view_reset_password', array(
            'number' => $number,
            'email' => $email,
            'error' => $error
        ));
    }

    /** Active customer, reseller or provider account with this number and e-mail address. */
    private function reset_account($number, $email)
    {
        $this->db->where_in('type', array(
            '0',
            '1',
            '3'
        ));
        $this->db->where(array(
            'number' => $number,
            'email' => $email,
            'deleted' => 0,
            'status' => 0
        ));
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return (array) $this->db->get('accounts')->first_row();
    }

    private function reset_hash($accountid, $code)
    {
        return hash_hmac('sha256', (int) $accountid . '|' . $code, (string) $this->config->item('encryption_key'));
    }

    private function reset_issue_code($account)
    {
        $id = (int) $account['id'];
        $row = (array) $this->db->get_where('password_resets', array(
            'accountid' => $id
        ))->first_row();
        $issued = 0;
        $window = gmdate('Y-m-d H:i:s');
        if (! empty($row) && strtotime($row['window_start'] . ' UTC') > time() - 3600) {
            $issued = (int) $row['issued'];
            $window = $row['window_start'];
        }
        if ($issued >= self::RESET_MAX_CODES) {
            log_message('error', 'password reset: code limit reached for account ' . $id);
            return;
        }
        $code = str_pad((string) random_int(0, (10 ** self::RESET_CODE_DIGITS) - 1), self::RESET_CODE_DIGITS, '0', STR_PAD_LEFT);
        $this->db->query('INSERT INTO password_resets (accountid, code_hash, created_date, attempts, issued, window_start, client_ip) VALUES (?, ?, ?, 0, ?, ?, ?)
            ON DUPLICATE KEY UPDATE code_hash = VALUES(code_hash), created_date = VALUES(created_date), attempts = 0,
            issued = VALUES(issued), window_start = VALUES(window_start), client_ip = VALUES(client_ip)', array(
            $id,
            $this->reset_hash($id, $code),
            gmdate('Y-m-d H:i:s'),
            $issued + 1,
            $window,
            substr((string) $this->input->server('REMOTE_ADDR'), 0, 45)
        ));
        $account['otp'] = $code;
        $this->send_mail($id, 'forgot_password_confirmation', $account, $account['number']);
    }

    /** True once for the right code; every call counts as an attempt. */
    private function reset_check_code($account, $code)
    {
        $id = (int) $account['id'];
        $row = (array) $this->db->get_where('password_resets', array(
            'accountid' => $id
        ))->first_row();
        if (empty($row) || $row['code_hash'] === '') {
            return false;
        }
        $this->db->query('UPDATE password_resets SET attempts = attempts + 1 WHERE accountid = ? AND code_hash = ? AND attempts < ?', array(
            $id,
            $row['code_hash'],
            self::RESET_MAX_ATTEMPTS
        ));
        if ($this->db->affected_rows() !== 1) {
            return false;
        }
        $expire = common_model::$global_config['system_config']['otp_expire'];
        $expire = $expire > 0 ? (int) $expire : 30;
        if (strtotime($row['created_date'] . ' UTC') < time() - $expire * 60) {
            return false;
        }
        if (! ctype_digit($code) || ! hash_equals($row['code_hash'], $this->reset_hash($id, $code))) {
            return false;
        }
        // used up: the next reset needs a new code (the hourly limit stays)
        $this->db->query('UPDATE password_resets SET code_hash = \'\', attempts = ? WHERE accountid = ? AND code_hash = ?', array(
            self::RESET_MAX_ATTEMPTS,
            $id,
            $row['code_hash']
        ));
        return $this->db->affected_rows() === 1;
    }

    private function reset_wait($start)
    {
        $left = self::RESET_MIN_TIME - (microtime(true) - $start);
        if ($left > 0) {
            usleep((int) ($left * 1000000));
        }
    }
}
?>


