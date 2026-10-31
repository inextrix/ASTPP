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
class Account_import extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->config('account_import');
        $this->load->library('astpp/form');
        $this->load->library('astpp/permission');
        $this->load->library('ASTPP_Sms');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function customer_import_mapper()
    {
        $data['page_title'] = gettext('Import Customer Using Field Mapper');
        $data['config_array'] = $this->_create_common_array();
        $this->session->set_userdata('import_customer_mapper_csv', "");
        $this->session->set_userdata('import_customer_mapper_csv_error', "");
        $data['invoice_date'] = gmdate("d") > 28 ? gmdate("28") : gmdate("d");
        $this->load->view('view_import_customer_mapper', $data);
    }

    function customer_import_download_sample_file()
    {
        $file_name = "customers_sample";
        $this->load->helper('download');
        $full_path = "./assets/Rates_File/" . $file_name . ".csv";
        ob_clean();
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false
            )
        );
        $file = file_get_contents($full_path, false, stream_context_create($arrContextOptions));
        force_download("samplefile.csv", $file);
    }

    function customer_import_preview()
    {
        $invalid_flag = false;
        $error = null;
        $data['page_title'] = gettext('Import Customer');
        if (empty($_FILES) || ! isset($_FILES)) {
            redirect(base_url() . "accounts/customer_list/");
        }
        $data['mapto_fields'] = $this->config->item('Customers-mapper-fields');
        $extension = explode(".", $_FILES['customer_import_mapper']['name']);
        if ((isset($extension[1])) && (! isset($extension[2]))) {
            if (isset($_FILES['customer_import_mapper']['name']) && $_FILES['customer_import_mapper']['name'] != "") {
                $segments = explode(".", $_FILES['customer_import_mapper']['name']);
                $txt = $ext = '';
                if (count($segments) !== 2) {
                    $invalid_flag = true;
                    $error = gettext("File Uploading Fail Please Try Again");
                } else {
                    list ($txt, $ext) = $segments;
                }
                if ($ext == "csv" && $_FILES['customer_import_mapper']['size'] > 0) {
                    $error = $_FILES['customer_import_mapper']['error'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $_FILES["customer_import_mapper"]["tmp_name"]);
                    $acceptable_mime_types = array(
                        'application/csv',
                        'application/x-csv',
                        'text/csv',
                        'text/comma-separated-values',
                        'text/x-comma-separated-values',
                        'text/tab-separated-values',
                        'text/plain'
                    );
                    if (! in_array($mime_type, $acceptable_mime_types)) {
                        $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
                    } else {
                        if ($error == 0) {
                            $uploadedFile = $_FILES["customer_import_mapper"]["tmp_name"];
                            $file_data = $this->common->csv_to_array($uploadedFile);
                            $field_select = array_combine(array_keys($file_data[0]), array_keys($file_data[0]));
                            $data['file_data'] = $field_select;
                            if (! empty($file_data)) {
                                $sipdevice_flag = $this->input->post('sipdevice_flag', TRUE);
                                if ($sipdevice_flag == 1) {
                                    unset($data['mapto_fields']['settings']['SIP Username'], $data['mapto_fields']['settings']['SIP Password']);
                                }
                                $full_path = $this->config->item('rates-file-path');
                                $actual_file_name = "ASTPP-Customer-import" . date("Y-m-d H:i:s") . "." . $ext;
                                $actual_file_name = str_replace(' ', '-', $actual_file_name);
                                $actual_file_name = str_replace(':', '-', $actual_file_name);
                                $default_value_array = array();
                                if (move_uploaded_file($uploadedFile, $full_path . $actual_file_name)) {
                                    array_unshift($file_data, $field_select);
                                    $data['csv_tmp_data'] = $file_data;
                                    $data['page_title'] = gettext('Map CSV to Customers');
                                    $this->session->set_userdata('import_customer_mapper_csv', $actual_file_name);
                                    $data['post_array'] = serialize($this->input->post());
                                    $data['mapper_array'] = $this->_create_mapper_array();
                                } else {
                                    $invalid_flag = true;
                                    $error = gettext("File Uploading Fail Please Try Again");
                                }
                            }
                        } else {
                            $invalid_flag = true;
                            $error = gettext("File Uploading Fail Please Try Again");
                        }
                    }
                } else {
                    $invalid_flag = true;
                    $error = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
                }
            } else {
                $invalid_flag = true;
                $error = gettext("Please Select  File.");
            }
        } else {
            $invalid_flag = true;
            $error = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
        }

        if ($invalid_flag) {
            $data['invoice_date'] = gmdate("d") > 28 ? gmdate("28") : gmdate("d");
            $data['error'] = $error;
        }
        $data['config_array'] = $this->_create_common_array();
        $this->load->view('view_import_customer_mapper', $data);
    }

    function customer_import_data()
    {
        $add_array = $this->input->post();
        $tax_id = Common_model::$global_config['system_config']['tax_type'];
        $this->load->library("astpp/signup_lib");
        $default_fields = '';
        $default_fields = unserialize($add_array['post_array'], array('allowed_classes' => false));
        $current_date = gmdate("Y-m-d H:i:s");
        $config_array = $this->config->item('Customers-mapper-fields');
        $custom_config_array = array_merge($config_array['general_info'], $config_array['settings']);
        $accountinfo = $this->session->userdata("accountinfo");
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $full_path = $this->config->item('rates-file-path');
        $customer_file_name = $this->session->userdata('import_customer_mapper_csv');
        $csv_tmp_data = $this->common->csv_to_array($full_path . $customer_file_name);
        // no uploaded file in this session
        if (! is_array($csv_tmp_data)) {
            $this->session->set_flashdata('astpp_notification', gettext('Please upload a CSV file first.'));
            redirect(base_url() . "account_import/customer_import_mapper/");
        }
        $i = 0;
        $this->db->select("id,name");
        $pricelist_result = $this->db->get_where('pricelists', array(
            "reseller_id" => $reseller_id,
            "status" => 0
        ))->result_array();
        $pricelist_id_array = array();
        if (! empty($pricelist_result)) {
            foreach ($pricelist_result as $key => $value) {
                $pricelist_id_array[$value['name']] = $value['id'];
            }
        }
        $is_recording_array = $this->common->custom_status_recording();
        $sweepid_result = $this->db->get_where('sweeplist')->result_array();
        $sweep_id_array = array();
        if (! empty($sweepid_result)) {
            foreach ($sweepid_result as $key => $value) {
                $sweep_id_array[$value['sweep']] = $value['id'];
            }
        }
        $count_data = count($csv_tmp_data);
        $numberlength = common_model::$global_config['system_config']['pinlength'];
        $numberlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['pinlength'];
        $pin_array = $this->common->find_uniq_rendno_accno($numberlength, 'pin', 'accounts', '', $count_data);
        $cardlength = common_model::$global_config['system_config']['cardlength'];
        $cardlength = ($numberlength < 6) ? 6 : common_model::$global_config['system_config']['cardlength'];
        $number_array = $this->common->find_uniq_rendno_accno($cardlength, 'number', 'accounts', '', $count_data);
        $username_array = $number_array = $this->common->find_uniq_rendno_accno($cardlength, 'username', 'sip_devices', '', $count_data);
        $i = 0;
        $sip_profile_result = (array) $this->db->get_where("sip_profiles", array(
            'status' => "0"
        ))->first_row();
        $sip_profile_id = $sip_profile_result['id'];
        $sip_device_array = array();
        $sip_username_array = array();
        $invoice_details_array = array();
        $number = array();
        $email = array();
        if (! empty($csv_tmp_data)) {
            $this->db->select('id');
            $this->db->order_by('id', 'DESC');
            $this->db->limit(1);
            $customer_invoice_info = (array) $this->db->get('invoices')->first_row();
            if (empty($customer_invoice_info)) {
                $customer_invoice_info['invoiceid'] = "00001";
            }
            $where = "accountid IN ('" . $reseller_id . "','1')";
            $this->db->where($where);
            $this->db->select('invoice_prefix,interval');
            $this->db->order_by('accountid', 'desc');
            $this->db->limit(1);
            $invoiceconf = $this->db->get('invoice_conf');
            $invoiceconf = (array) $invoiceconf->first_row();

            $count = 0;
            $invalid_count = 0;
            foreach ($csv_tmp_data as $key => $csv_data) {
                $new_array = array();
                $error = null;
                $invalid_flag = FALSE;
                if (! empty($add_array["number-select"]) && $csv_data[$add_array["number-select"]] && is_numeric($csv_data[$add_array["number-select"]]) && ! isset($number[$csv_data[$add_array["number-select"]]])) {
                    $this->db->select('id');
                    $number_result = (array) $this->db->get_where('accounts', array(
                        'number' => $csv_data[$add_array["number-select"]],
                        "deleted" => 0
                    ))->first_row();
                    if (empty($number_result)) {
                        $new_array['number'] = $csv_data[$add_array["number-select"]];
                        $number[$new_array['number']] = $new_array['number'];
                    } else {
                        $invalid_flag = TRUE;
                    }
                } else {
                    $invalid_flag = TRUE;
                }
                if (! empty($add_array["email-select"]) && $csv_data[$add_array["email-select"]] && ! isset($email[$csv_data[$add_array["email-select"]]])) {
                    $this->db->select('id');
                    $email_result = (array) $this->db->get_where('accounts', array(
                        'email' => $csv_data[$add_array["email-select"]],
                        "deleted" => 0
                    ))->first_row();
                    if (empty($email_result)) {
                        if($add_array["email-select"] == "Email"){
                            $new_array['email'] = $csv_data[$add_array["email-select"]];
                        }else{
                            $new_array['email'] = $csv_data[$add_array["email-select"]] . '@' . $csv_data[$add_array["email-select"]] . '.com';
                            $email[$csv_data[$add_array["email-select"]]] = $new_array['email'];
                        }
                    } else {
                        $invalid_flag = TRUE;
                    }
                } else {
                    $invalid_flag = TRUE;
                }
                if ($invalid_flag == FALSE) {
                    $new_array['password'] = ! empty($add_array["password-select"]) && ! empty($csv_data[$add_array["password-select"]]) ? $csv_data[$add_array["password-select"]] : (! empty($add_array['password']) ? $add_array['password'] : $this->common->generate_password());
                    // a password from the file or the form must pass the portal password rule,
                    // otherwise the row counts as invalid (like a duplicate number)
                    $this->load->library('astpp/password_policy');
                    if ($this->password_policy->check($new_array['password'], array($new_array['number'], isset($new_array['email']) ? $new_array['email'] : '')) !== '') {
                        $invalid_flag = TRUE;
                    }
                }
                if ($invalid_flag == FALSE) {
                    $current_password = $new_array['password'];
                    $new_array['password'] = $this->common->password_store($current_password); // hash
                    $new_array['first_name'] = ! empty($add_array["first_name-select"]) && ! empty($csv_data[$add_array["first_name-select"]]) ? $csv_data[$add_array["first_name-select"]] : (! empty($add_array['first_name']) ? $add_array['first_name'] : $new_array['number']);

                    $new_array['last_name'] = ! empty($add_array["last_name-select"]) && ! empty($csv_data[$add_array["last_name-select"]]) ? $csv_data[$add_array["last_name-select"]] : $add_array['last_name'];

                    $new_array['company_name'] = ! empty($add_array["company-select"]) && ! empty($csv_data[$add_array["company-select"]]) ? $csv_data[$add_array["company-select"]] : $add_array['company'];

                    $new_array['telephone_1'] = ! empty($add_array["telephone_1-select"]) && ! empty($csv_data[$add_array["telephone_1-select"]]) ? $csv_data[$add_array["telephone_1-select"]] : $add_array['telephone_1'];

                    $new_array['telephone_2'] = ! empty($add_array["telephone_2-select"]) && ! empty($csv_data[$add_array["telephone_2-select"]]) ? $csv_data[$add_array["telephone_2-select"]] : $add_array['telephone_2'];

                    $new_array['address_1'] = ! empty($add_array["address_1-select"]) && ! empty($csv_data[$add_array["address_1-select"]]) ? $csv_data[$add_array["address_1-select"]] : $add_array['address_1'];

                    $new_array['city'] = ! empty($add_array["city-select"]) && ! empty($csv_data[$add_array["city-select"]]) ? $csv_data[$add_array["city-select"]] : $add_array['city'];

                    $new_array['province'] = ! empty($add_array["province-select"]) && ! empty($csv_data[$add_array["province-select"]]) ? $csv_data[$add_array["province-select"]] : $add_array['province'];

                    $new_array['postal_code'] = ! empty($add_array["postal_code-select"]) && ! empty($csv_data[$add_array["postal_code-select"]]) ? $csv_data[$add_array["postal_code-select"]] : $add_array['postal_code'];
                    $new_array['dialed_modify'] = ! empty($add_array["dialed_modify-select"]) && ! empty($csv_data[$add_array["dialed_modify-select"]]) ? '"' . str_replace(',', '","', $csv_data[$add_array["dialed_modify-select"]]) . '"' : (! empty($add_array['dialed_modify']) ? '"' . str_replace(',', '","', $add_array['dialed_modify']) . '"' : '');

                    $new_array['maxchannels'] = ! empty($add_array["maxchannels-select"]) && ! empty($csv_data[$add_array["maxchannels-select"]]) ? $csv_data[$add_array["maxchannels-select"]] : $add_array['maxchannels'];

                    $new_array['cps'] = ! empty($add_array["cps-select"]) && ! empty($csv_data[$add_array["cps-select"]]) ? $csv_data[$add_array["cps-select"]] : $add_array['cps'];

                    $balance_prepaid = $default_fields['posttoexternal'] == 0 && ! empty($add_array["balance-select"]) && ! empty($csv_data[$add_array["balance-select"]]) > 0 ? $this->common_model->add_calculate_currency($csv_data[$add_array["balance-select"]], '', '', false, false) : ($default_fields['posttoexternal'] == 0 && $add_array['balance'] > 0 ? $this->common_model->add_calculate_currency($add_array['balance'], '', '', false, false) : 0);
                    $new_array['balance'] = $balance_prepaid;

                    $credit_limit = $default_fields['posttoexternal'] == 1 && ! empty($add_array["credit_limit-select"]) && ! empty($csv_data[$add_array["credit_limit-select"]]) > 0 ? $this->common_model->add_calculate_currency($csv_data[$add_array["credit_limit-select"]], '', '', false, false) : ($default_fields['posttoexternal'] == 1 && $add_array['credit_limit'] > 0 ? $this->common_model->add_calculate_currency($add_array['credit_limit'], '', '', false, false) : 0);

                    $new_array['credit_limit'] = $credit_limit;

                    $new_array['pin'] = $default_fields['pin'] == 0 ? $pin_array[$i] : '';

                    $new_array['is_recording'] = $default_fields['is_recording'];

                    $new_array['pricelist_id'] = $default_fields['pricelist_id'];

                    $new_array['timezone_id'] = $default_fields['timezone_id'];

                    $new_array['localization_id'] = $default_fields['localization_id'];

                    $new_array['country_id'] = $default_fields['country_id'];

                    $new_array['currency_id'] = $default_fields['currency_id'];

                    $new_array['posttoexternal'] = $default_fields['posttoexternal'];

                    $new_array['sweep_id'] = $default_fields['sweep_id'];

                    $new_array['invoice_day'] = (($default_fields['sweep_id'] != 0) ? $default_fields['invoice_day'] : '');

                    $new_array['local_call'] = $default_fields['local_call'];

                    $new_array['notify_flag'] = $default_fields['notify_flag'];

                    $new_array['charge_per_min'] = $default_fields['charge_per_min'];

                    $new_array['reseller_id'] = $reseller_id;

                    $new_array['type'] = 0;

                    $new_array['deleted_date'] = '0000-00-00 00:00:00';

                    $new_array['allow_ip_management'] = $default_fields['allow_ip_management'];

                    $new_array['creation'] = $current_date;
                    $new_array['expiry'] = gmdate('Y-m-d H:i:s', strtotime($current_date . '+10 years'));

                    $new_array['notify_credit_limit'] = '';

                    $final_new_array=$new_array;
                    unset($final_new_array['balance']);
                    $this->db->insert('accounts', $final_new_array);

                    $accountid = $this->db->insert_id();
                    if ($default_fields['sipdevice_flag'] == 0 && $accountid > 0) {
                        $username = $username_array[$i];
                        if (! empty($add_array["sip_username-select"]) && isset($csv_data[$add_array["sip_username-select"]]) && ! isset($sip_username_array[$csv_data[$add_array["sip_username-select"]]])) {
                            $this->db->select('id');
                            $sipdevice_result = $this->db->get_where('sip_devices', array(
                                'username' => $csv_data[$add_array["sip_username-select"]]
                            ))->first_row();
                            if (empty($sipdevice_result)) {
                                $username = $csv_data[$add_array["sip_username-select"]];
                            } else {
                                if ($add_array['sip_username'] == 'number') {
                                    $this->db->select('id');
                                    $sipdevice_result = $this->db->get_where('sip_devices', array(
                                        'username' => $add_array['sip_username']
                                    ))->first_row();
                                    if (empty($sipdevice_result)) {
                                        $username = $csv_data[$add_array["sip_username-select"]];
                                    } else {
                                        $username = $username_array[$i];
                                    }
                                } else {
                                    $username = $username_array[$i];
                                }
                            }
                        } else {
                            if ($add_array['sip_username'] == 'number' && ! isset($sip_username_array[$new_array['number']])) {
                                $this->db->select('id');
                                $sipdevice_result = $this->db->get_where('sip_devices', array(
                                    'username' => $new_array['number']
                                ))->first_row();
                                if (empty($sipdevice_result)) {
                                    $username = $new_array['number'];
                                } else {
                                    $username = $username_array[$i];
                                }
                            } else {
                                $username = $username_array[$i];
                            }
                        }
                        $sip_username_array[$username] = $username;
                        $params_array = array(
                            // "Random": generate_password() like the SIP device forms (this was a
                            // 10-digit number, which the form rule chk_password_expression rejects)
                            'password' => ! empty($add_array["sip_password-select"]) && isset($csv_data[$add_array["sip_password-select"]]) && ! empty($csv_data[$add_array["sip_password-select"]]) ? $csv_data[$add_array["sip_password-select"]] : ($add_array['sip_password'] == 'number' ? $new_array['number'] : (($add_array['sip_password'] == "random" || $add_array['sip_password'] == "password") ? $this->common->generate_password() : '')),
                            "vm-enabled" => "false",
                            "vm-password" => "",
                            "vm-mailto" => "",
                            "vm-attach-file" => "false",
                            "vm-keep-local-after-email" => "true",
                            "vm-email-all-messages" => "false"
                        );
                        $params_array_vars = array(
                            'effective_caller_id_name' => '',
                            'effective_caller_id_number' => '',
                            'user_context' => 'default'
                        );
                        $sip_device_array[$i] = array(
                            'username' => $username,
                            'sip_profile_id' => $sip_profile_id,
                            'reseller_id' => $reseller_id,
                            'accountid' => $accountid,
                            'dir_params' => json_encode($params_array),
                            'dir_vars' => json_encode($params_array_vars),
                            'status' => 0,
                            'creation_date' => $new_array['creation'],
                            'last_modified_date' => $new_array['creation']
                        );
                    }
                    if (! empty($tax_id)) {
                        $this->signup_lib->account_taxes($tax_id, $accountid, $current_date);
                    }
                    $new_array['id'] = $accountid;
                    if ($balance_prepaid > 0 || $credit_limit > 0) {
                        $this->signup_lib->generate_receipt($new_array, $balance_prepaid, $credit_limit);
                    }
                    $count ++;
                } else {
                    $invalid_count ++;
                }
                $i ++;
            }
            if (! empty($sip_device_array)) {
                $this->db->insert_batch('sip_devices', $sip_device_array);
            }
        }
        $data = array();
        $data['invalid_count'] = $invalid_count;
        $data['count'] = $count;
        $data['page_title'] = gettext("Account Import Error");
        $this->load->view('view_import_error', $data);
    }

    private function _create_common_array()
    {
        $add_array = $this->input->post();

        $custom_config_array = $this->config->item('Customers-field');

        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $this->db->select("id,name");

        $pricelist_result = $this->db->get_where('pricelists', array(
            "reseller_id" => $reseller_id,
            "status" => 0
        ))->result_array();
        $pricelist_id_array = array();
        if (! empty($pricelist_result)) {
            foreach ($pricelist_result as $key => $value) {
                $pricelist_id_array[$value['id']] = $value['name'];
            }
        }

        $sweepid_result = $this->db->get_where('sweeplist')->result_array();
        $sweep_id_array = array();
        if (! empty($sweepid_result)) {
            foreach ($sweepid_result as $key => $value) {
                $sweep_id_array[$value['id']] = gettext($value['sweep']);
            }
        }

        $localization_id = Common_model::$global_config['system_config']['localization_id'];
        $localization_result = $this->db->get_where('localization', array(
            "id" => $localization_id,
            "status" => 0
        ))->result_array();

        $localization_array = array();
        if (! empty($localization_result)) {
            foreach ($localization_result as $key => $value) {
                $localization_array[$value['id']] = $value['name'];
            }
        }

        $custom_status_array = $this->common->custom_status();
        $cli_pool_array = $this->common->set_cli_pool();

        $timezone_result = $this->db->get_where('timezone')->result_array();
        $timezone_array = array();
        if (! empty($timezone_result)) {
            foreach ($timezone_result as $key => $value) {
                $timezone_array[$value['id']] = gettext($value['timezone_name']);
            }
        }
        $country_result = $this->db->get_where('countrycode')->result_array();
        $country_array = array();
        if (! empty($country_result)) {
            foreach ($country_result as $key => $value) {
                $country_array[$value['id']] = gettext($value['country']);
            }
        }

        $currency_result = $this->db->get_where('currency')->result_array();
        $currency_array = array();
        $default_currency_id = 1;
        if (! empty($currency_result)) {
            foreach ($currency_result as $key => $value) {
                $currency_array[$value['id']] = gettext($value['currencyname']);
                if (Common_model::$global_config['system_config']['base_currency'] == $value['currency']) {
                    $default_currency_id = $value['id'];
                }
            }
        }
        $tax_result = $this->db->get_where('taxes', array(
            "reseller_id" => $reseller_id
        ))->result_array();
        $tax_array = array();
        if (! empty($tax_result)) {
            foreach ($tax_result as $key => $value) {
                $tax_array[$value['id']] = $value['taxes_description'];
            }
        }

        foreach ($custom_config_array as $key => $value) {
            $params_arr = array(
                "id" => $value,
                "name" => $value,
                "class" => $value
            );
            $current_value = isset($add_array[$value]) ? $add_array[$value] : '';
            if ($value == "pricelist_id" || $value == 'non_cli_pricelist_id') {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['default_signup_rategroup'];
                $custom_array[$value] = form_dropdown($params_arr, $pricelist_id_array, $current_value);
            }

            if ($value == "localization_id") {
                $localization_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['localization_id'];
                if($localization_value == 0){
                    $localization_array = "";
                    $custom_array[$value] =form_dropdown($params_arr, $localization_array, $localization_value);
                }else{
                    $custom_array[$value] = form_dropdown($params_arr, $localization_array, $localization_value);
                }
            }
            if ($value == "timezone_id") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['default_timezone'];
                $custom_array[$value] = form_dropdown($params_arr, $timezone_array, $current_value);
            }
            if ($value == "cli_pool") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['default_timezone'];
                $custom_array[$value] = form_dropdown($params_arr, $cli_pool_array, $current_value);
            }
            if ($value == "country_id") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['country'];
                $custom_array[$value] = form_dropdown($params_arr, $country_array, $current_value);
            }
            if ($value == "currency_id") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : $default_currency_id;
                $custom_array[$value] = form_dropdown($params_arr, $currency_array, $current_value);
            }
            if ($value == "pin") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['generate_pin'];
                $custom_array[$value] = form_dropdown($params_arr, $custom_status_array, $current_value);
            }
            if ($value == "sipdevice_flag") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['create_sipdevice'];
                $custom_array[$value] = form_dropdown($params_arr, $custom_status_array, $current_value);
            }
            if ($value == "allow_ip_management") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['allow_ip_management'];
                $custom_array[$value] = form_dropdown($params_arr, $custom_status_array, $current_value);
            }
            if ($value == "local_call") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['local_call'];
                $custom_array[$value] = form_dropdown($params_arr, $custom_status_array, $current_value);
            }
            if ($value == "is_recording") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['is_recording'];
                $custom_array[$value] = form_dropdown($params_arr, $custom_status_array, $current_value);
            }
            if ($value == "invoice_day") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['allow_ip_management'];
                $custom_array[$value] = form_dropdown($params_arr, $custom_status_array, $current_value);
            }
            if ($value == "charge_per_min") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['charge_per_min'];
                $custom_array[$value] = form_input($params_arr, $current_value);
            }
            if ($value == "notify_flag") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : Common_model::$global_config['system_config']['notify_flag'];
                $custom_array[$value] = form_dropdown($params_arr, $custom_status_array, $current_value);
            }

            if ($value == "posttoexternal") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : '';
                $custom_array[$value] = form_dropdown($params_arr, $this->common->set_account_type(), $current_value);
            }
            if ($value == "sweep_id") {
                $current_value = isset($add_array[$value]) ? $add_array[$value] : 2;
                $custom_array[$value] = form_dropdown($params_arr, $sweep_id_array, $current_value);
            }
            if ($value == "validfordays") {
                $custom_array[$value] = form_input($params_arr, '');
            }
            if (! empty($custom_array[$value])) {
                $custom_array[$value] = str_replace('col-md-5', 'col-md-12', $custom_array[$value]);
            }
        }
        return $custom_array;
    }

    function _create_mapper_array()
    {
        $custom_config_array = $this->config->item('Customers-mapper-fields');
        $custom_array = array();
        $sipdevice_flag = $this->input->post('sipdevice_flag', true);
        foreach ($custom_config_array as $first_key => $first_value) {
            foreach ($first_value as $key => $value) {

                $params_arr = array(
                    'id' => $value,
                    "class" => $value,
                    "name" => $value
                );
                if ($value == "sip_username" || $value == "sip_password") {
                    if ($sipdevice_flag == 0) {
                        if ($value == "sip_username") {
                            $custom_array[$value] = str_replace('col-md-5', 'col-md-12', str_replace('form-control', '', form_dropdown($params_arr, $this->_sip_user_option(), '')));
                        }
                        if ($value == "sip_password") {
                            // no "same as password" any more - the portal password must not be readable in sip_devices (old value "password" = random)
                            $custom_array[$value] = str_replace('col-md-5', 'col-md-12', str_replace('form-control', '', form_dropdown($params_arr, $this->_sip_user_option(), '')));
                        }
                    }
                } elseif ($value == "number") {
                    $custom_array[$value] = str_replace('col-md-5', 'col-md-12', str_replace('form-control', '', form_dropdown($params_arr, array(
                        "" => ""
                    ), '')));
                } else {
                    if ($value == "telephone_1") {
                        $params_arr['placeholder'] = " Ex: 12025550120";
                    }
                    if ($value == "telephone_2") {
                        $params_arr['placeholder'] = " Ex: 911234567890";
                    }
                    if ($value == "email") {
                        $params_arr['placeholder'] = "Ex: customer@example.com";
                    }
                    $custom_array[$value] = str_replace('col-md-5', 'col-md-12', form_input($params_arr));
                }
            }
        }
        return $custom_array;
    }

    function customer_import_type()
    {
        $custom_value = "sweep_id";
        $sweepid_result = $this->db->get_where('sweeplist')->result_array();
        $sweep_id_array = array();
        if (! empty($sweepid_result)) {
            foreach ($sweepid_result as $key => $value) {
                $sweep_id_array[$value['id']] = $value['sweep'];
            }
        }
        $params_arr = array(
            "id" => $custom_value,
            "name" => $custom_value,
            "class" => $custom_value
        );
    }

    function _sip_user_option()
    {
        return array(
            "number" => "Same as Account Number",
            "random" => "Random"
        );
    }
}

?>


