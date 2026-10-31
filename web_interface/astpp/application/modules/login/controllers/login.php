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
class Login extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('astpp/permission');
        $this->load->library('encrypt');
        $this->load->model('Auth_model');
        $this->load->model('db_model');
        $this->load->library('form_validation');
        $this->load->library('ASTPP_Sms');
        $this->load->library('user_agent');
        $this->load->helper('cookie');
    }

    function test()
    {
        $popup_flag = $_POST['flag'];

        $flag = '1';

        if ($flag == 'termination_rates') {
            $this->session->set_userdata($popup_flag . 'popup_flag', $flag);
        }

        return true;
    }

    function set_lang_global($post = false)
    {
        $language = $this->uri->segment(3);

        $this->session->set_userdata('user_language', $language);

        $this->locale->set_lang();
        return true;
    }

    function index()
    {
        $this->load->helper('cookie');
        $cookie_user = get_cookie('post_info');
        if (! empty($cookie_user)) {
            $data['astpp_notification'] = gettext("Please Check Your account is deleted or inactive from admin side, please contact to your administrator");
        }
        if (isset($this->session->userdata['key'])) {
            $key = $this->session->userdata('key');
            $reseller_id = $this->common->decode($this->common->decode_params(trim($key)));
        }
        if ($this->session->userdata('user_login') == FALSE) {
            if (! empty ( $_POST ) && isset($_POST ['username']) && isset($_POST ['password']) && trim ( $_POST ['username'] ) != '' && trim ( $_POST ['password'] ) != '') {
                $actual_password = $_POST['password'];
                // verify_login() checks the typed password against the stored hash
                // and returns the id of the account it matched
                $user_valid = $this->Auth_model->verify_login($_POST['username'], $actual_password);
                $_POST['password'] = '';

                if ($user_valid > 0) {
                    $this->session->set_userdata('user_login', TRUE);
                    $where = array(
                        'id' => $user_valid,
                        'deleted' => 0
                    );

                    $result = $this->db_model->getSelect("*", "accounts", $where);
                    $result = $result->result_array();
                    $result = $result[0];
                    // the row goes into the session (accountinfo, token) - without the password value
                    $result['password'] = '';

                    $user_multi_level = 0;
                    $addon_status = $this->db_model->countQuery("*", "addons", array(
                        'package_name' => 'pbx'
                    ));
                    if ($addon_status == 1 && $result['type'] == 0) {
                        $multidomain = $this->db_model->getSelect("*", "domains", array(
                            'domain' => $_SERVER["HTTP_HOST"],
                            'accountid' => $result['id'],
                            'status' => 0
                        ));
                        $multidomain_result = $multidomain->result_array();
                        if (! empty($multidomain_result)) {
                            $user_multi_level = 0;
                        } else {
                            $user_multi_level = 1;
                        }
                    }
                    if ($user_multi_level == 0) {

                        $logintype = $result['type'] == - 1 ? 2 : $result['type'];
                        $this->session->set_userdata('logintype', $logintype);
                        $this->session->set_userdata('userlevel_logintype', $result['type']);
                        $this->session->set_userdata('username', $_POST['username']);
                        $this->session->set_userdata('accountinfo', $result);
                        $permission_result = $this->db_model->getSelect("*", "permissions", array(
                            'id' => $result['permission_id']
                        ));
                        $permission_result = $permission_result->result_array();
                        $permission_result = $permission_result[0];
                        $permission_decode = json_decode($permission_result['permissions'], true);
                        $permission_decode['login_type'] = $result['type'];
                        $this->session->set_userdata('permissioninfo', $permission_decode);
                        $logintype = $result['type'] == - 1 ? 2 : $result['type'];
                        $this->session->set_userdata('logintype', $logintype);
                        $this->session->set_userdata('userlevel_logintype', $result['type']);
                        $this->session->set_userdata('username', $_POST['username']);
                        $this->session->set_userdata('accountinfo', $result);
                        $token = $this->token($result['id'], 'e', $result);
                        $this->session->set_userdata('token', $token);
                        // ASTPPCOM-941 Start
                        $login_activity_array=array(
                            "account_id"=>$result['id'],
                            "country_name"=>ucwords(strtolower(geoip_country_name_by_name($this->input->server('REMOTE_ADDR')))),
                            "timestamp"=>gmdate("Y-m-d H:i:s"),
                            "user_agent"=> $this->agent->agent_string(),
                            "ip"=>$this->input->server('REMOTE_ADDR')
                            );
                        $this->db->insert('login_activity_report', $login_activity_array);
                        // ASTPPCOM-941 END
                        $accessid = $this->encrypt($this->config->item('private_key'), $result['id'] . $result['type']);
                        $this->session->set_userdata('ipsettings_token', $accessid);
                        if ($result['status'] == 1) {
                            $this->session->set_flashdata('astpp_danger_alert', gettext("Your account has been deactive please contact to your administrator"));
                        } else {
                            // existing passwords stay valid; one that would not pass the
                            // portal password rule today only gets the warning
                            $this->load->library('astpp/password_policy');
                            if (($actual_password == 'admin') or ($this->password_policy->check($actual_password, array($result['number'], $result['email'])) !== '')) {
                                if ($logintype == - 1 || $logintype == 2 || $logintype == 4)
                                    $url = "/accounts/admin_edit/" . $result['id'];
                                elseif ($logintype == 0 || $logintype == 3 || $logintype == 1)
                                    $url = "/user/user_change_password";
                                else
                                    $url = "#";
                                $this->session->set_flashdata('astpp_danger_alert', gettext("Please do not use default or less secure password for your account!! You must change password from")." <a href='" . $url . "'><b>".gettext('HERE')."</b></a> .");

                                {}
                            }
                        }

                        $this->db->select("*");
                        if ($result['type'] == '2' || $result['type'] == '-1') {
                            $this->db->where(array(
                                "accountid" => "1"
                            ));
                        } else if ($result['type'] == '0') {
                            if ($result['reseller_id'] == 0) {
                                $this->db->where(array(
                                    "accountid" => "1"
                                ));
                            } else {
                                $this->db->where(array(
                                    "accountid" => $result["reseller_id"]
                                ));
                            }
                        } else if ($result['type'] == '1') {
                            if ($result['reseller_id'] == 0) {
                                $result_invoice = $this->common->get_field_name('id', 'invoice_conf', array(
                                    "accountid" => $result['id']
                                ));

                                if ($result_invoice) {
                                    $this->db->where(array(
                                        "accountid" => $result["id"]
                                    ));
                                } else {
                                    $this->db->where(array(
                                        "accountid" => "1"
                                    ));
                                }
                            } else {
                                $result_invoice = $this->common->get_field_name('id', 'invoice_conf', array(
                                    "accountid" => $result['reseller_id']
                                ));
                                if ($result_invoice) {
                                    $this->db->where(array(
                                        "accountid" => $result["reseller_id"]
                                    ));
                                } else {
                                    $this->db->where(array(
                                        "accountid" => "1"
                                    ));
                                }
                            }
                        } else {
                            $this->db->where(array(
                                "accountid" => "1"
                            ));
                        }
                        $res = $this->db->get("invoice_conf");
                        $logo_arr = $res->result();
                        $data['user_logo'] = (isset($logo_arr[0]->logo) && $logo_arr[0]->logo != "") ? $logo_arr[0]->accountid . "_" . $logo_arr[0]->logo : "logo.png";
                        $data['user_header'] = (isset($logo_arr[0]->website_title) && $logo_arr[0]->website_title != "") ? $logo_arr[0]->website_title : "ASTPP - Open Source Voip Billing Solution";
                        $data['user_footer'] = (isset($logo_arr[0]->website_footer) && $logo_arr[0]->website_footer != "") ? $logo_arr[0]->website_footer : "Inextrix Technologies Pvt. Ltd All Rights Reserved.";
                        $data['user_favicon'] = (isset($logo_arr[0]->favicon) && $logo_arr[0]->favicon != "") ? $logo_arr[0]->accountid . "_" . $logo_arr[0]->favicon : "favicon.ico";
                        $this->session->set_userdata('user_logo', $data['user_logo']);
                        $this->session->set_userdata('user_header', $data['user_header']);
                        $this->session->set_userdata('user_footer', $data['user_footer']);
                        $this->session->set_userdata('user_favicon', $data['user_favicon']);
                        if ($result['type'] == 0 || $result['type'] == 1 || $result['type'] == 3) {
                            $menu_list = $this->permission->get_module_access($result['type']);
                            $this->session->set_userdata('mode_cur', 'user');
                            if ($result['type'] == 1) {
                                $new_url = get_cookie('astpp_last_visit_url' . $result["id"]);
                                if (empty($new_url) || ! isset($new_url) || $new_url == '') {
                                    redirect(base_url() . 'dashboard/');
                                }
                                redirect($new_url);
                            } else {
                                $new_url = get_cookie('astpp_last_visit_url' . $result["id"]);
                                if (empty($new_url) || ! isset($new_url) || $new_url == '') {
                                    redirect(base_url() . 'user/user/');
                                }
                                if (strpos($new_url, 'addons') !== false) {
                                    redirect(base_url() . 'dashboard/');
                                }
                                redirect($new_url);
                            }
                        } else {
                            $menu_list = $this->permission->get_module_access($result['type']);
                            $this->session->set_userdata('mode_cur', 'admin');

                            $new_url = get_cookie('astpp_last_visit_url' . $result["id"]);
                            if (empty($new_url) || ! isset($new_url) || $new_url == '') {
                                redirect(base_url() . 'dashboard/');
                            }
                            if (strpos($new_url, 'addons') !== false) {
                                redirect(base_url() . 'dashboard/');
                            }
                            redirect($new_url);
                        }
                    } else {
                        $data['astpp_notification'] = gettext("Login unsuccessful. Please make sure you entered the correct username and password, and that your account is active");
                    }
                } else {
                    $data['astpp_notification'] = gettext("Login unsuccessful. Please make sure you entered the correct username and password, and that your account is active");
                }
            } else {
                if (! empty ( $_POST ) && ((isset($_POST ['password']) && ($_POST ['password'] == '')) || (isset($_POST ['password']) && ($_POST ['username'] == '')))) {
					$data ['astpp_notification'] = gettext("Please enter Username/email and Password.");
				}
            }

            if (isset($_SERVER['HTTP_HOST'])) {
		if($_SERVER['HTTP_HOST'] == $_SERVER['SERVER_NAME']){
			$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
		}else{
			$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];		
		}
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
                    $domain = "https://" . $_SERVER["HTTP_HOST"] . "/";
                } else {
                    $domain = "http://" . $_SERVER["HTTP_HOST"] . "/";
                }

                $http_host = $_SERVER["HTTP_HOST"];
                $this->db->select("*");
                $this->db->where("domain LIKE '%$domain%'");
                $this->db->or_where("domain LIKE '%$http_host%'");
                $this->db->order_by("id", "desc");
                $this->db->limit(1);
                $res = $this->db->get("invoice_conf");
                $logo_arr = $res->result();
                $data['user_logo'] = (isset($logo_arr[0]->logo) && $logo_arr[0]->logo != "") ? $logo_arr[0]->logo : "logo.png";
                $data['website_header'] = (isset($logo_arr[0]->website_title) && $logo_arr[0]->website_title != "") ? $logo_arr[0]->website_title : "ASTPP - Open Source Voip Billing Solution";
                $data['website_footer'] = (isset($logo_arr[0]->website_footer) && $logo_arr[0]->website_footer != "") ? $logo_arr[0]->website_footer : "Inextrix Technologies Pvt. Ltd All Rights Reserved.";
                $data['user_favicon'] = (isset($logo_arr[0]->favicon) && $logo_arr[0]->favicon != "") ? $logo_arr[0]->accountid . "_" . $logo_arr[0]->favicon : "favicon.ico";
                $this->session->set_userdata('user_logo', $data['user_logo']);
                $this->session->set_userdata('user_header', $data['website_header']);
                $this->session->set_userdata('user_footer', $data['website_footer']);
                $this->session->set_userdata('user_favicon', $data['user_favicon']);
            }

            $this->session->set_userdata('user_login', FALSE);
            $data['app_name'] = 'ASTPP - Open Source Billing Solution';
            $this->load->view('view_login', $data);
        } else {
	    if($_SERVER['HTTP_HOST'] == $_SERVER['SERVER_NAME']){
			$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
	    }else{
			$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];		
	    }
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
                $custom_domain = "https://" . $_SERVER["HTTP_HOST"];
            } else {
                $custom_domain = "http://" . $_SERVER["HTTP_HOST"];
            }
            $where = "domain in ('$custom_domain','" . $_SERVER["HTTP_HOST"] . "')";
            $this->db->select("*");
            $this->db->where($where);
            $this->db->or_where("reseller_id", $reseller_id);
            $this->db->order_by("id", "desc");
            $this->db->limit(1);
            $res = $this->db->get("invoice_conf");
            $logo_arr = $res->result();

            $data['user_logo'] = (isset($logo_arr[0]->logo) && $logo_arr[0]->logo != "") ? $logo_arr[0]->accountid . "_" . $logo_arr[0]->logo : "logo.png";
            $data['user_header'] = (isset($logo_arr[0]->website_title) && $logo_arr[0]->website_title != "") ? $logo_arr[0]->website_title : "ASTPP - Open Source Voip Billing Solution";
            $data['user_footer'] = (isset($logo_arr[0]->website_footer) && $logo_arr[0]->website_footer != "") ? $logo_arr[0]->website_footer : "Inextrix Technologies Pvt. Ltd All Rights Reserved.";
            $data['user_favicon'] = (isset($logo_arr[0]->favicon) && $logo_arr[0]->favicon != "") ? $logo_arr[0]->accountid . "_" . $logo_arr[0]->favicon : "favicon.ico";

            $this->session->set_userdata('user_logo', $data['user_logo']);
            $this->session->set_userdata('user_header', $data['user_header']);
            $this->session->set_userdata('user_footer', $data['user_footer']);
            $this->session->set_userdata('user_favicon', $data['user_favicon']);
            if ($this->session->userdata('logintype') == '2') {
                redirect(base_url() . 'dashboard/');
            } else {
                redirect(base_url() . 'user/user/');
            }
        }
    }

    protected function token($string, $action, $account_info = '')
    {
        $key = hash('sha256', config_item('token_key'));

        $secret_iv = config_item('iv_key');

        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == "e") {
            $token = base64_encode(openssl_encrypt($string, 'AES-256-CBC', $key, 0, $iv));

            if (is_array($account_info)) {
                $account_info['token'] = $token;
                return $account_info;
            }

            return $token;
        }

        if ($action == "d") {
            $token = openssl_decrypt(base64_decode($string), 'AES-256-CBC', $key, 0, $iv);
            return $token;
        }
    }

    function logout()
    {
        $userdata = $this->session->userdata('accountinfo');
        $this->session->set_userdata('key', "");
        $this->session->sess_destroy();
        $last_url_data = $_SERVER['HTTP_REFERER'];
        set_cookie('astpp_last_visit_url' . $userdata['id'], $last_url_data, '3600');
        redirect(base_url());
    }

    /*
     * This URL credited the amount from the POST field "custom" for any request that named a
     * pending top-up (no question to PayPal, also with PayPal switched off). PayPal payments are
     * booked only by the verified IPN in pages/paypal_response; this old notify/return URL books
     * nothing.
     */
    function paypal_response()
    {
        log_message('error', 'login/paypal_response: old PayPal URL called, nothing booked');
        show_404();
    }

    function generate_receipt($accountid, $amount, $accountinfo, $last_invoice_ID, $invoice_prefix, $due_date)
    {
        $invoice_data = array(
            "accountid" => $accountid,
            "invoice_prefix" => $invoice_prefix,
            "invoiceid" => '0000' . $last_invoice_ID,
            "reseller_id" => $accountinfo['reseller_id'],
            "invoice_date" => gmdate("Y-m-d H:i:s"),
            "from_date" => gmdate("Y-m-d H:i:s"),
            "to_date" => gmdate("Y-m-d H:i:s"),
            "due_date" => $due_date,
            "status" => 1,
            "balance" => $accountinfo['balance'],
            "amount" => $amount,
            "type" => 'R',
            "confirm" => '1'
        );
        $this->db->insert("invoices", $invoice_data);
        $invoiceid = $this->db->insert_id();
        return $invoiceid;
    }

    function get_language_text()
    {
        echo gettext($_POST['display']);
    }

    function encode_params($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array(
            '+',
            '/',
            '='
        ), array(
            '-',
            '$',
            ''
        ), $data);
        return $data;
    }

    function encode($value)
    {
        $ivSize = openssl_cipher_iv_length('BF-ECB');
        $iv = ($ivSize > 0) ? openssl_random_pseudo_bytes($ivSize) : false;
        $encrypted = openssl_encrypt($value, 'BF-ECB', $this->config->item('private_key'), OPENSSL_RAW_DATA, $iv);
        $encrypted = $this->encode_params($encrypted);
        return $encrypted;
    }

    function encrypt($api_key, $id = false)
    {
        $str = $this->encode($api_key . "#" . $id . "_" . $api_key);
        return $this->encode_params($str);
    }

    function login_as_reseller($select_id)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        if($accountinfo['type'] == '-1' || $accountinfo['type'] == '2' || $accountinfo['type'] == '1'){
		if($accountinfo['type'] == '1'){
                $where = array(
                'id' => $select_id,
                'reseller_id' => $accountinfo['id']
            );
            }else{
                $where = array(
                'id' => $select_id
            );
            }
        $where['deleted'] = 0;
        $result = $this->db_model->getSelect("*", "accounts", $where);
        $account_res = $result->row_array();
        $this->_login_as_found($account_res);
        $this->_relogin($account_res['id'],$accountinfo['id'] );
        }else{
        	$this->session->sess_destroy();
        	redirect(base_url() ."login/login/");
        }
    }


    function login_as_customer($select_id)
    {
       $accountinfo = $this->session->userdata('accountinfo');
       if($accountinfo['type'] == '-1' || $accountinfo['type'] == '2' || $accountinfo['type'] == '1'){
       		 if($accountinfo['type'] == '1'){
                $where = array(
                'id' => $select_id,
                'reseller_id' => $accountinfo['id']
            );
            }else{
                $where = array(
                'id' => $select_id
            );
            }
        $where['deleted'] = 0;
        $result = $this->db_model->getSelect("*", "accounts", $where);
        $account_res = $result->row_array();
        $this->_login_as_found($account_res);
        $this->_relogin($account_res['id'] , $accountinfo['id']);
        }else{
        	$this->session->sess_destroy();
        	redirect(base_url() ."login/login/");
        }
        
    }


    function login_as_admin($select_id)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        if($accountinfo['type'] == '0' || $accountinfo['type'] == '1'){
            $master_login_details_arr = $this->session->userdata('master_login_details');
            if(isset($master_login_details_arr) && $master_login_details_arr != "" ){
                if($master_login_details_arr['master_login_id'] == $select_id){
                    $this->session->unset_userdata('master_login_details');
                    $this->_relogin($select_id , '0');
                }else{
                    $this->session->sess_destroy();
                    redirect(base_url() ."login/login/");
                }
            }else{
                $this->session->sess_destroy();
                redirect(base_url() ."login/login/");
            }
        }else{
            $this->session->sess_destroy();
            redirect(base_url() ."login/login/");
        }
    }


    /** Login as another account only if it was found in the own scope. */
    function _login_as_found($account_res)
    {
        if (empty($account_res['id'])) {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'dashboard/');
            exit();
        }
    }

    function _relogin($new_login_id, $master_id = '0')
    {
        // The account was looked up again by number OR email; an id that was not
        // found gave an empty number and switched the session to the first account without
        // e-mail address. Use the row of the id and stop if there is none.
        $where = array(
            'id' => (int) $new_login_id,
            'deleted' => 0
        );
        $account_res = (array) $this->db->get_where("accounts", $where)->first_row();
        if (empty($account_res['id'])) {
            $this->session->sess_destroy();
            redirect(base_url() . "login/login/");
            exit();
        }
        $master_login_details = array();
        if ($master_id != '0') {
            $where = array(
                'id' => $master_id
            );
            $admin_res = (array) $this->db->get_where("accounts", $where)->first_row();
            // The decoded password of the admin was stored in the session (unused)
            $master_login_details = array(
                'master_login_id' => $admin_res['id'],
                'master_number' => $admin_res['number']
            );
        }
        $this->session->set_userdata('user_login', TRUE);
        $account_res['password'] = ''; // the row goes into the session without the password value
        $result = $account_res;
        $permission_result = $this->db_model->getSelect("*", "permissions", array(
            'id' => $result['permission_id']
        ));
        $permission_result = $permission_result->result_array();
        $permission_result = $permission_result[0];
        $permission_decode = json_decode($permission_result['permissions'], true);
        $permission_decode['login_type'] = $result['type'];
        $logintype = $result['type'] == - 1 ? 2 : $result['type'];
        if (! empty($master_login_details)) {
            $this->session->set_userdata('master_login_details', $master_login_details);
        }
        $this->session->set_userdata('logintype', $logintype);
        $this->session->set_userdata('userlevel_logintype', $result['type']);
        $this->session->set_userdata('username', $account_res['number']);
        $this->session->set_userdata('accountinfo', $result);
        $token = $this->token($result['id'], 'e', $result);
        $accessid = $this->encrypt($this->config->item('private_key'), $result['id'] . $result['type']);
        $this->session->set_userdata('ipsettings_token', $accessid);

        $this->session->set_userdata('permissioninfo', $permission_decode);
        // "login as" needs no password of either account. The password of the target
        // account used to be decoded here for the "weak password" warning; the warning is
        // shown at the real login of the account (the only moment its password is known).
        $this->db->select("*");
        if ($result['type'] == '2' || $result['type'] == '-1') {
            $this->db->where(array(
                "accountid" => "1"
            ));
        } else if ($result['type'] == '0') {
            if ($result['reseller_id'] == 0) {
                $this->db->where(array(
                    "accountid" => "1"
                ));
            } else {
                $this->db->where(array(
                    "accountid" => $result["reseller_id"]
                ));
            }
        } else if ($result['type'] == '1') {
            if ($result['reseller_id'] == 0) {
                $result_invoice = $this->common->get_field_name('id', 'invoice_conf', array(
                    "accountid" => $result['id']
                ));

                if ($result_invoice) {
                    $this->db->where(array(
                        "accountid" => $result["id"]
                    ));
                } else {
                    $this->db->where(array(
                        "accountid" => "1"
                    ));
                }
            } else {
                $result_invoice = $this->common->get_field_name('id', 'invoice_conf', array(
                    "accountid" => $result['reseller_id']
                ));
                if ($result_invoice) {
                    $this->db->where(array(
                        "accountid" => $result["reseller_id"]
                    ));
                } else {
                    $this->db->where(array(
                        "accountid" => "1"
                    ));
                }
            }
        } else {
            $this->db->where(array(
                "accountid" => "1"
            ));
        }
        $res = $this->db->get("invoice_conf");
        $logo_arr = $res->result();
        $data['user_logo'] = (isset($logo_arr[0]->logo) && $logo_arr[0]->logo != "") ? $logo_arr[0]->accountid . "_" . $logo_arr[0]->logo : "logo.png";
        $data['user_header'] = (isset($logo_arr[0]->website_title) && $logo_arr[0]->website_title != "") ? $logo_arr[0]->website_title : "ASTPP - Open Source Voip Billing Solution";
        $data['user_footer'] = (isset($logo_arr[0]->website_footer) && $logo_arr[0]->website_footer != "") ? $logo_arr[0]->website_footer : "Inextrix Technologies Pvt. Ltd All Rights Reserved.";
        $data['user_favicon'] = (isset($logo_arr[0]->favicon) && $logo_arr[0]->favicon != "") ? $logo_arr[0]->accountid . "_" . $logo_arr[0]->favicon : "favicon.ico";
        $this->session->set_userdata('user_logo', $data['user_logo']);
        $this->session->set_userdata('user_header', $data['user_header']);
        $this->session->set_userdata('user_footer', $data['user_footer']);
        $this->session->set_userdata('user_favicon', $data['user_favicon']);
        if ($result['type'] == 0 || $result['type'] == 1 || $result['type'] == 3) {
            $menu_list = $this->permission->get_module_access($result['type']);

            $this->session->set_userdata('mode_cur', 'user');
            if ($result['type'] == 1) {
                redirect(base_url() . 'dashboard/');
            } else {
                redirect(base_url() . 'user/user/');
            }
        } else {
            $menu_list = $this->permission->get_module_access($result['type']);
            $this->session->set_userdata('mode_cur', 'admin');
            redirect(base_url() . 'dashboard/');
        }
    }

    function customer_permission_list()
    {
        $button_array = $this->input->post();
        $permissioninfo = $this->session->userdata('permissioninfo');
        $currnet_url = $button_array['current_url'];
        $url_explode = explode('/', $currnet_url);
        $module_name = $url_explode[3];
        $sub_module_name = $url_explode[4];
        if ((isset($permissioninfo[$module_name][$sub_module_name][$button_array['button_name']]) && $permissioninfo[$module_name][$sub_module_name][$button_array['button_name']] == 0) or $permissioninfo['login_type'] == '-1' or $permissioninfo['login_type'] == '0' or $permissioninfo['login_type'] == '3') {
            echo 0;
        } else {
            echo 1;
        }
    }
}

?>
