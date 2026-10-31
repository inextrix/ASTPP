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
class Rates extends MX_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->helper('template_inheritance');

        $this->load->library('session');
        $this->load->library('rates_form');
        $this->load->library('astpp/form', 'rates_form');
        $this->load->library('astpp/permission');
        $this->load->model('rates_model');
        $this->load->library('csvreader');
        $this->load->library('ASTPP_Sms');
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "259200");
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function termination_rates_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Termination Rates');
        $data['search_flag'] = true;
        $data['batch_update_flag'] = true;
        $data['delete_batch_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->rates_form->build_termination_rate_for_admin();
        $data["grid_buttons"] = $this->rates_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->rates_form->get_termination_rate_search_form());
        $data['form_batch_update'] = $this->form->build_batchupdate_form($this->rates_form->termination_rate_batch_update_form());
        $this->load->view('view_termination_rates_list', $data);
    }

    function termination_rates_list_json()
    {
        $json_data = array();
        $count_all = $this->rates_model->get_termination_rates_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->rates_model->get_termination_rates_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->rates_form->build_termination_rate_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function termination_rates_list_delete($flag = '')
    {
        $json_data = array();
        $this->session->set_userdata('advance_batch_data_delete', 1);
        $count_all = $this->rates_model->get_termination_rates_list(false);
        echo $count_all;
    }

    function termination_rate_import()
    {
        $data['page_title'] = gettext('Import Termination Rates');
        $this->session->set_userdata('import_termination_rate_csv', "");
        $this->session->set_userdata('import_termination_rate_csv_error', "");
        $account_info = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $data['currency'] = $currency;
        $this->load->view('view_import_termination_rate', $data);
    }

    function termination_rate_preview_file()
    {
        $invalid_flag = false;
        $check_header = $this->input->post('check_header', true);

        $data['page_title'] = gettext('Import Termination Rates');
        $account_info = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $data['currency'] = $currency;
        $config_termination_rate_array = $this->config->item('Termination-rates-field');
        $this->db->where('id', $account_info['currency_id']);
        $this->db->select('currency');
        $currency_info = (array) $this->db->get('currency')->first_row();
        foreach ($config_termination_rate_array as $key => $value) {
            $key = str_replace('CURRENCY', $currency_info['currency'], $key);
            $new_final_arr_key[$key] = $value;
        }
        if (! empty($_SERVER['CONTENT_LENGTH']) && empty($_FILES) && empty($_POST)) {
            $data['error'] = gettext("The uploaded file is too large. You must upload a file smaller than").' ' . ini_get('upload_max_filesize');
        } else {
            $extension = explode(".", $_FILES['termination_rate_import']['name']);
            if ((isset($extension[1])) && (! isset($extension[2]))) {
                if (isset($_FILES['termination_rate_import']['name']) && $_FILES['termination_rate_import']['name'] != "" && isset($_POST['trunk_id']) && $_POST['trunk_id'] != '') {
                    list ($txt, $ext) = explode(".", $_FILES['termination_rate_import']['name']);
                    if ($ext == "csv" && $_FILES['termination_rate_import']['size'] > 0) {
                        $error = $_FILES['termination_rate_import']['error'];
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $_FILES["termination_rate_import"]["tmp_name"]);
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
                                $uploadedFile = $_FILES["termination_rate_import"]["tmp_name"];

                                $file_data = $this->csv_to_array($uploadedFile);

                                $field_select = (array_keys($file_data[0]));

                                $data['file_data'] = $field_select;

                                $csv_data = $this->utf8_converter($this->csvreader->parse_file($uploadedFile, $field_select, $check_header));
                                // $csv_data = $this->utf8_converter($this->csvreader->parse_file($uploadedFile, $new_final_arr_key, $check_header));
                                if (! empty($csv_data)) {
                                    $full_path = $this->config->item('rates-file-path');
                                    $actual_file_name = "ASTPP-TERMINATION-RATES-" . date("Y-m-d H:i:s") . "." . $ext;
                                    if (move_uploaded_file($uploadedFile, $full_path . $actual_file_name)) {
                                        $data['field_select'] = serialize($field_select);
                                        $data['csv_tmp_data'] = $csv_data;
                                        $data['trunkid'] = $_POST['trunk_id'];
                                        $data['check_header'] = $check_header;
                                        $data['page_title'] = gettext('Termination Rates Preview');
                                        $this->session->set_userdata('import_termination_rate_csv', $actual_file_name);
                                    } else {
                                        $data['error'] = gettext("File Uploading Fail Please Try Again");
                                    }
                                }
                            } else {
                                $data['error'] == gettext("File Uploading Fail Please Try Again");
                            }
                        }
                    } else {
                        $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
                    }
                } else {
                    $invalid_flag = true;
                }
            } else {
                $invalid_flag = true;
                $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
            }
            if ($invalid_flag) {
                $str = '';
                if (! isset($_POST['trunk_id']) || empty($_POST['trunk_id'])) {
                    $str .= '<div class="col-12">Please Select Trunk.</div>';
                }
                if (empty($_FILES['termination_rate_import']['name'])) {
                    $str .= '<div class="col-12">Please Select  File.</div>';
                }
                $data['error'] = $str;
            }
        }

        $this->load->view('view_import_termination_rate', $data);
    }

    function termination_rate_rates_import($trunkID, $check_header = false)
    {
        $new_final_arr = array();
        $invalid_array = array();
        $row_count = 0;
        $new_final_arr_key = $this->config->item('Termination-rates-field');
        $screen_path = $this->config->item('screen_path');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
        }
        $full_path = $this->config->item('rates-file-path');
        $terminationrate_file_name = $this->session->userdata('import_termination_rate_csv');
        $csv_tmp_data = $this->csvreader->parse_file($full_path . $terminationrate_file_name, $new_final_arr_key, $check_header);
        $i = 0;
        foreach ($csv_tmp_data as $key => $csv_data) {
            if (isset($csv_data['pattern']) && $csv_data['pattern'] != '' && $i != 0) {
                $str = null;
                $csv_data['strip']=str_replace("”",'',str_replace("“",'', $csv_data['strip']));
                $csv_data['prepend']=str_replace("”",'',str_replace("“",'', $csv_data['prepend']));
                $csv_data['prepend'] = isset($csv_data['prepend']) ? $csv_data['prepend'] : '';
                $csv_data['comment'] = isset($csv_data['comment']) ? $csv_data['comment'] : '';
                $csv_data['connectcost'] = isset($csv_data['connectcost']) && is_numeric($csv_data['connectcost']) && ($csv_data['connectcost'] != "") ? $this->common_model->add_calculate_currency($csv_data['connectcost'], '', '', false, false) : $csv_data['connectcost'];
                $csv_data['includedseconds'] = isset($csv_data['includedseconds']) ? $csv_data['includedseconds'] : 0;
                $csv_data['init_inc'] = ! empty($csv_data['init_inc']) && is_numeric($csv_data['init_inc']) && ($csv_data['init_inc'] != "") ? $this->common_model->add_calculate_currency($csv_data['init_inc'], '', '', false, false) : $csv_data['init_inc'];
                $csv_data['cost'] = ! empty($csv_data['cost']) && is_numeric($csv_data['cost']) && ($csv_data['cost'] != "") ? $this->common_model->add_calculate_currency($csv_data['cost'], '', '', false, false) : $csv_data['cost'];
                $csv_data['inc'] = isset($csv_data['inc']) ? $csv_data['inc'] : 0;
                $csv_data['precedence'] = isset($csv_data['precedence']) ? $csv_data['precedence'] : '';
                $csv_data['strip'] = isset($csv_data['strip']) ? $csv_data['strip'] : '';
                $str = $this->data_validate($csv_data);
                if ($str != "") {
                    $invalid_array[$i] = $csv_data;
                    $invalid_array[$i]['error'] = $str;
                } else {
                    $csv_data['trunk_id'] = $trunkID;
                    $csv_data['pattern'] = "^" . $csv_data['pattern'] . ".*";
                    $csv_data['creation_date'] = gmdate("Y-m-d H:i:s");
                    $csv_data['last_modified_date'] = gmdate("Y-m-d H:i:s");
                    $new_final_arr[$i] = $csv_data;
                    $row_count ++;
                }
            }
            $i ++;
        }
        if (! empty($new_final_arr)) {
            $this->rates_model->bulk_insert_termination_rate($new_final_arr, $row_count);
        }

        unlink($full_path . $terminationrate_file_name);
        $count = count($invalid_array);
        if ($count > 0) {
            $session_id = "-1";
            $fp = fopen($full_path . $session_id . '.csv', 'w');
            $account_info = $this->session->userdata('accountinfo');
            $currency_name = $this->common->get_field_name('currency', 'currency', $account_info['currency_id']);
            foreach ($new_final_arr_key as $key => $value) {
                $key = str_replace('CURRENCY', $currency_name, $key);
                $custom_array[0][$key] = ucwords($key);
            }
            $custom_array[0]['error'] = "Error";
            $invalid_array = array_merge($custom_array, $invalid_array);
            foreach ($invalid_array as $err_data) {
                fputcsv($fp, $err_data);
            }
            fclose($fp);
            $this->session->set_userdata('import_termination_rate_csv_error', $session_id . ".csv");
            $data["error"] = $invalid_array;
            $data['trunkid'] = $trunkID;
            $data['impoted_count'] = count($new_final_arr);
            $data['failure_count'] = count($invalid_array) - 1;
            $data['page_title'] = gettext('Termination Rates Import Error');
            $this->load->view('view_import_error', $data);
        } else {
            $this->session->set_flashdata('astpp_errormsg', gettext('Total').' '. count($new_final_arr) .' '. gettext('Termination Rates Imported Successfully!'));
            redirect(base_url() . "rates/termination_rates_list/");
        }
    }

    function termination_rate_error_download()
    {
        $this->load->helper('download');
        $error_data = $this->session->userdata('import_termination_rate_csv_error');
        $full_path = $this->config->item('rates-file-path');
        ob_clean();
        $data = file_get_contents($full_path . $error_data);
        force_download(gettext("Termination_rate_error").".csv", $data);
    }

    function origination_rate_import()
    {
        $data['page_title'] = gettext('Import Origination Rates');
        $this->session->set_userdata('import_origination_rate_csv', "");
        $error_data = $this->session->userdata('import_origination_rate_csv_error');
        $full_path = $this->config->item('rates-file-path');
        $account_info = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $data['currency'] = $currency;
        if (file_exists($full_path . $error_data) && $error_data != "") {
            unlink($full_path . $error_data);
            $this->session->set_userdata('import_origination_rate_csv_error', "");
        }
        $this->load->view('view_import_origination_rate', $data);
    }

    function origination_rate_preview_file()
    {
        $invalid_flag = false;
        $data = array();
        $data['page_title'] = gettext('Import Origination Rates');
        $account_info = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $data['currency'] = $currency;
        $check_header = $this->input->post('check_header', true);

        if (empty($_FILES) || ! isset($_FILES)) {
            redirect(base_url() . "rates/origination_rates_list/");
        }
        $get_extension = strpos($_FILES['origination_rate_import']['name'], '.');
        $config_orignination_rate_array = $this->config->item('Origination-rates-field');
        $this->db->where('id', $account_info['currency_id']);
        $this->db->select('currency');
        $currency_info = (array) $this->db->get('currency')->first_row();
        foreach ($config_orignination_rate_array as $key => $value) {
            $key = str_replace('CURRENCY', $currency_info['currency'], $key);
            $new_final_arr_key[$key] = $value;
        }

        if (! $get_extension) {
            $data['error'] = gettext("Please Upload File Atleast");
        }
        $extension = explode(".", $_FILES['origination_rate_import']['name']);
        if ((isset($extension[1])) && (! isset($extension[2]))) {
            if (isset($_FILES['origination_rate_import']['name']) && $_FILES['origination_rate_import']['name'] != "" && isset($_POST['pricelist_id']) && $_POST['pricelist_id'] != '') {
                list ($txt, $ext) = explode(".", $_FILES['origination_rate_import']['name']);

                if ($ext == "csv" && $_FILES['origination_rate_import']['size'] > 0) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $_FILES["origination_rate_import"]["tmp_name"]);
                    $acceptable_mime_types = array(
                        'application/csv',
                        'application/x-csv',
                        'text/csv',
                        'text/comma-separated-values',
                        'text/x-comma-separated-values',
                        'text/tab-separated-values',
                        'text/plain'
                    );
                    $error = $_FILES['origination_rate_import']['error'];
                    if (! in_array($mime_type, $acceptable_mime_types)) {
                        $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
                    } else {
                        if ($error == 0) {
                            $uploadedFile = $_FILES["origination_rate_import"]["tmp_name"];
                            $csv_data = $this->csvreader->parse_file($uploadedFile, $new_final_arr_key, $check_header);
                            if (! empty($csv_data)) {
                                $full_path = $this->config->item('rates-file-path');
                                $actual_file_name = "ASTPP-ORIGIN-RATES-" . date("Y-m-d H:i:s") . "." . $ext;
                                if (move_uploaded_file($uploadedFile, $full_path . $actual_file_name)) {
                                    $flag = false;
                                    $data['trunkid'] = isset($_POST['trunk_id']) && $_POST['trunk_id'] > 0 ? $_POST['trunk_id'] : 0;
                                    $data['csv_tmp_data'] = $csv_data;
                                    $data['pricelistid'] = $_POST['pricelist_id'];
                                    $data['page_title'] = gettext("Origination Rates Preview");
                                    $data['check_header'] = $check_header;

                                    $this->session->set_userdata('import_origination_rate_csv', $actual_file_name);
                                } else {
                                    $data['error'] = gettext("File Uploading Fail Please Try Again");
                                }
                            }
                        } else {
                            $data['error'] == gettext("File Uploading Fail Please Try Again");
                        }
                    }
                } else {

                    $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
                }
            } else {
                $invalid_flag = true;
            }
        } else {
            $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
        }
        if ($invalid_flag) {
            $str = '';
            if (! isset($_POST['pricelist_id']) || empty($_POST['pricelist_id'])) {
                $str .= '<div class="col-12">Please Select Rate Group.</div>';
            }
            if (empty($_FILES['origination_rate_import']['name'])) {
                $str .= '<div class="col-12">'.gettext("Please Select File.").'</div>';
            }
            $data['error'] = $str;
        }
        $this->load->view('view_import_origination_rate', $data);
    }

    function get_ratedeck_details_for_prefix($prefix)
    {
        $where = "(pattern='-1'";
        $str_len = strlen($prefix);
        for ($j = $str_len; $j > 0; $j --) {
            $value = substr($prefix, 0, $j);
            $where .= " OR pattern='^" . $value . ".*'";
        }
        $where .= ")";
        return $this->db_model->getSelectWithOrderAndLimit("*", "ratedeck", $where, 'desc', "length(pattern)", 1);
    }

    function origination_rate_import_file($pricelistID, $trunkid, $check_header = false)
    {
        $new_final_arr = array();
        $invalid_array = array();
        $new_final_arr_key = $this->config->item('Origination-rates-field');
        $screen_path = $this->config->item('screen_path');
        $reseller_id = 0;
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $reseller_id = $this->session->userdata["accountinfo"]['id'];
        }

        $full_path = $this->config->item('rates-file-path');
        $originationrate_file_name = $this->session->userdata('import_origination_rate_csv');
        $csv_tmp_data = $this->csvreader->parse_file($full_path . $originationrate_file_name, $new_final_arr_key, $check_header);
        $i = 0;
        $row_count = 0;
        foreach ($csv_tmp_data as $key => $csv_data) {

            if (isset($csv_data['pattern']) && $csv_data['pattern'] != '' && $i != 0) {
                $str = null;

                $csv_data['comment'] = isset($csv_data['comment']) ? $csv_data['comment'] : '';
                $csv_data['connectcost'] = isset($csv_data['connectcost']) && is_numeric($csv_data['connectcost']) && ($csv_data['connectcost'] != "") ? $this->common_model->add_calculate_currency($csv_data['connectcost'], '', '', false, false) : $csv_data['connectcost'];
                $csv_data['includedseconds'] = isset($csv_data['includedseconds']) ? $csv_data['includedseconds'] : 0;
                $csv_data['init_inc'] = ! empty($csv_data['init_inc']) && is_numeric($csv_data['init_inc']) && ($csv_data['init_inc'] != "") ? $this->common_model->add_calculate_currency($csv_data['init_inc'], '', '', false, false) : $csv_data['init_inc'];
                $csv_data['cost'] = ! empty($csv_data['cost']) && is_numeric($csv_data['cost']) && ($csv_data['cost'] != "") ? $this->common_model->add_calculate_currency($csv_data['cost'], '', '', false, false) : $csv_data['cost'];
                $csv_data['inc'] = isset($csv_data['inc']) ? $csv_data['inc'] : 0;
                $csv_data['precedence'] = isset($csv_data['precedence']) ? $csv_data['precedence'] : '';
                $str = $this->data_validate($csv_data);
                if ($str != "") {
                    $invalid_array[$i] = $csv_data;
                    $invalid_array[$i]['error'] = $str;
                } else {
                    $csv_data['country_id'] = isset($csv_data['country_id']) ? $csv_data['country_id'] : '';
                    $csv_data['call_type'] = isset($csv_data['call_type']) ? $csv_data['call_type'] : '';
                    $csv_data['pricelist_id'] = $pricelistID;
                    $csv_data['trunk_id'] = $trunkid;
                    $csv_data['pattern'] = "^" . $csv_data['pattern'] . ".*";
                    $csv_data['reseller_id'] = $reseller_id;
                    $csv_data['creation_date'] = gmdate("Y-m-d H:i:s");
                    $csv_data['last_modified_date'] = gmdate("Y-m-d H:i:s");
                    if (isset($csv_data) && ((isset($csv_data['country_id'])) && ($csv_data['country_id'] == "") || ((isset($csv_data['comment'])) && ($csv_data['comment'] == "")) || ((isset($csv_data['call_type'])) && ($csv_data['call_type'] == "")))) {
                        if ($csv_data['pattern'] != "") {
                            $prefix = $this->common->get_only_numeric_val("", "", $csv_data['pattern']);
                            $value = '';
                            $pattern = '';

                            $query = $this->get_ratedeck_details_for_prefix($prefix);
                            if ($query->num_rows() > 0) {
                                $result = $query->result_array();
                                $result = $result[0];
                                if (isset($csv_data['country_id']) && $csv_data['country_id'] == "") {
                                    $csv_data['country_id'] = $result['country_id'];
                                }
                                if (isset($csv_data['comment']) && $csv_data['comment'] == "") {
                                    $csv_data['comment'] = $result['destination'];
                                }
                                if (isset($csv_data['call_type']) && $csv_data['call_type'] == "") {
                                    $csv_data['call_type'] = $result['call_type'];
                                }
                            }
                        }
                    }
                    $new_final_arr[$i] = $csv_data;
                    $row_count ++;
                }
            }

            $i ++;
        }
        if (! empty($new_final_arr)) {
            $this->rates_model->bulk_insert_origination_rate($new_final_arr, $row_count);
        }
        unlink($full_path . $originationrate_file_name);
        $count = count($invalid_array);
        if ($count > 0) {
            $session_id = "-1";
            $fp = fopen($full_path . $session_id . '.csv', 'w');
            $account_info = $this->session->userdata('accountinfo');
            $currency_name = $this->common->get_field_name('currency', 'currency', $account_info['currency_id']);
            foreach ($new_final_arr_key as $key => $value) {
                $key = str_replace('CURRENCY', $currency_name, $key);
                $custom_array[0][$key] = ucwords($key);
            }
            $custom_array[0]['error'] = "Error";
            $invalid_array = array_merge($custom_array, $invalid_array);
            foreach ($invalid_array as $err_data) {
                fputcsv($fp, $err_data);
            }
            fclose($fp);
            $this->session->set_userdata('import_origination_rate_csv_error', $session_id . ".csv");
            $data["error"] = $invalid_array;
            $data['pricelistid'] = $pricelistID;
            $data['impoted_count'] = count($new_final_arr);
            $data['failure_count'] = count($invalid_array) - 1;
            $data['page_title'] = gettext('Origination Rates Import Error');
            $this->load->view('view_import_error', $data);
        } else {
            $this->session->set_flashdata('astpp_errormsg', gettext('Total').' '.count($new_final_arr).' '.gettext('Origination Rate Imported Successfully!'));
            redirect(base_url() . "rates/origination_rates_list/");
        }
    }

    function data_validate($csvdata)
    {
        $str = null;
        $alpha_regex = "/^[a-z ,.'-]+$/i";
        $alpha_numeric_regex = "/^[a-z0-9 ,.'-]+$/i";
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";
        $str .= $csvdata['pattern'] != '' ? null : 'Code,';
        $str = rtrim($str, ',');
        if (! $str) {
           
            $str .= preg_match("/^([\d+0-9])+$/i", $csvdata['pattern']) ? null : 'Code,';

            $str .= ! empty($csvdata['connectcost']) && is_numeric($csvdata['connectcost']) && ($csvdata['connectcost'] >= 0) ? null : (empty($csvdata['connectcost']) ? null : 'Connection Cost,');
            $str .= ! empty($csvdata['includedseconds']) && is_numeric($csvdata['includedseconds']) ? null : (empty($csvdata['includedseconds']) ? null : 'Grace Time,');
            $str .= ! empty($csvdata['cost']) && is_numeric($csvdata['cost']) && ($csvdata['cost'] >= 0) ? null : (empty($csvdata['cost']) ? null : 'Cost / Min,');
            $str .= ! empty($csvdata['init_inc']) && is_numeric($csvdata['init_inc']) && filter_var($csvdata['init_inc'], FILTER_VALIDATE_INT) && ($csvdata['init_inc'] >= 0) ? null : (empty($csvdata['init_inc']) ? null : 'Initial Increment,');
            $str .= ! empty($csvdata['inc']) && is_numeric($csvdata['inc']) && filter_var($csvdata['inc'], FILTER_VALIDATE_INT) && ($csvdata['inc'] >= 0) ? null : (empty($csvdata['inc']) ? null : 'Increment,');
            $str .= ! empty($csvdata['strip']) && is_numeric($csvdata['strip']) && ($csvdata['strip'] >= -1) ? null : (empty($csvdata['strip']) ? null : 'Strip,');
            $str .= ! empty($csvdata['prepend']) && is_numeric($csvdata['prepend']) && ($csvdata['prepend'] >= -1) ? null : (empty($csvdata['prepend']) ? null : 'Prepend');


            if ($str) {
                $str = rtrim($str, ',');
                $error_field = explode(',', $str);
                $count = count($error_field);
                $str .= $count > 1 ? ' are not valid' : ' is not Valid';
                return $str;
            } else {
                return false;
            }
        } else {
            $str = rtrim($str, ',');
            $error_field = explode(',', $str);
            $count = count($error_field);
            $str .= $count > 1 ? ' are required' : ' is Required';
            return $str;
        }
    }

    function origination_rate_error_download()
    {
        $this->load->helper('download');
        $error_data = $this->session->userdata('import_origination_rate_csv_error');
        $full_path = $this->config->item('rates-file-path');
        ob_clean();
        $data = file_get_contents($full_path . $error_data);
        force_download(gettext("Origination_rate_error").".csv", $data);
    }

    function origination_rate_add($type = "")
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Create Origination Rate');
        $data['routing_type'] = 0;
        $data['reseller_id'] = 0;
        $accountinfo = $this->session->userdata('accountinfo');
        $data['country_id'] = $accountinfo['country_id'];
        $data['form'] = $this->form->build_form($this->rates_form->get_origination_rate_form_fields(), '');
        $data['trunk_count'] = Common_model::$global_config['system_config']['trunk_count'];
        $this->load->view('view_origination_rate_add_edit', $data);
    }

    function origination_rate_edit($edit_id = '')
    {
        $this->permission->check_web_record_permission($edit_id, 'routes', 'rates/origination_rates_list/');
        $data['page_title'] = gettext('Edit Origination Rate');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $reseller = $account_data['id'];
            $where = array(
                'id' => $edit_id,
                "reseller_id" => $reseller
            );
        } else {
            $where = array(
                'id' => $edit_id
            );
        }
        $account = $this->db_model->getSelect("*", "routes", $where);
        if ($account->num_rows() > 0) {
            foreach ($account->result_array() as $key => $value) {
                $edit_data = $value;
            }

            $reseller_id = $edit_data['reseller_id'];
            if ($edit_data['reseller_id'] == 0) {
                $edit_data['reseller_id'] = 'Admin';
            } else {
                $edit_data['reseller_id'] = $this->common->get_field_name('number', 'accounts', array(
                    'id' => $edit_data['reseller_id']
                ));
            }

            $edit_data['connectcost'] = $this->common_model->to_calculate_currency($edit_data['connectcost'], '', '', true, false);
            $edit_data['cost'] = $this->common_model->to_calculate_currency($edit_data['cost'], '', '', true, false);
            $edit_data['pattern'] = preg_replace('/[^\d+0-9]/', '', $edit_data['pattern']);
            $edit_data['trunk_id'] = explode(",", $edit_data['trunk_id']);
            $edit_data['percentage'] = explode(",", $edit_data['percentage']);
            $data['trunk_count'] = Common_model::$global_config['system_config']['trunk_count'];
            $data['form'] = $this->form->build_form($this->rates_form->get_origination_rate_form_fields($edit_id, $reseller_id), $edit_data);
            $data['trunk_id'] = $edit_data['trunk_id'];
            if (isset($edit_data['percentage'])) {
                $data['percentage'] = $edit_data['percentage'];
            }
            $data['routing_type'] = $edit_data['routing_type'];
            $data['reseller_id'] = $reseller_id;
            $this->load->view('view_origination_rate_add_edit', $data);
        } else {
            redirect(base_url() . 'rates/origination_rates_list/');
        }
    }

    function origination_rate_save()
    {
        $add_array = $this->input->post();
        $code = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($add_array) && (($add_array['country_id'] == "") || ($add_array['comment'] == "") || ($add_array['call_type'] == ""))) {
                if ($add_array['pattern'] != "") {
                    $string = $add_array['pattern'];
                    $value = '';
                    $pattern = '';
                    $query = $this->get_ratedeck_details_for_prefix($add_array['pattern']);
                    if ($query->num_rows() > 0) {
                        $result = $query->result_array();
                        if (isset($add_array['country_id']) && $add_array['country_id'] == "") {
                            $add_array['country_id'] = $result[0]['country_id'];
                        }
                        if (isset($add_array['comment']) && $add_array['comment'] == "") {
                            $add_array['comment'] = $result[0]['destination'];
                        }
                        if (isset($add_array['call_type']) && $add_array['call_type'] == "") {
                            $add_array['call_type'] = $result[0]['call_type'];
                        }
                    }
                }
            }
            $data['trunk_count'] = Common_model::$global_config['system_config']['trunk_count'];
            $data['form'] = $this->form->build_form($this->rates_form->get_origination_rate_form_fields(), $add_array);
            if ($add_array['id'] != '') {
                $data['page_title'] = gettext('Edit Origination Rate');
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
                    $add_array['connectcost'] = isset($add_array['connectcost']) && ($add_array['connectcost'] != "") ? $this->common_model->add_calculate_currency($add_array['connectcost'], '', '', false, false) : 0;
                    $add_array['cost'] = isset($add_array['cost']) && ($add_array['cost'] != "") ? $this->common_model->add_calculate_currency($add_array['cost'], '', '', false, false) : 0;
                    $implode_str = null;

            
                    $trunk_array = array();
                    $add_array['trunk_id'] = ($add_array['trunk_id'] > 0)?$add_array['trunk_id']:0;

                    $this->db->where('routes_id', $add_array['id']);
                    $this->db->delete('routing');

                    if (isset($add_array['routing_type']) && $add_array['routing_type'] == 1) {
                        $this->origination_set_force_routing($add_array, $add_array['id']);
                    }

                    if (isset($add_array['reseller_id'])) {
                        unset($add_array['reseller_id']);
                    }

                    $this->rates_model->edit_origination_rate($add_array, $add_array['id']);
                    echo json_encode(array(
                        "SUCCESS" => ucfirst($add_array['pattern'].' '.gettext('Origination Rate Updated Successfully!'))
                    ));

                    exit();
                }
            } else {
                $data['page_title'] = gettext('Add Origination Rate');
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
                    $add_array['connectcost'] = isset($add_array['connectcost']) && ($add_array['connectcost'] != "") ? $this->common_model->add_calculate_currency($add_array['connectcost'], '', '', false, false) : 0;
                    $add_array['cost'] = isset($add_array['cost']) && ($add_array['cost'] != "") ? $this->common_model->add_calculate_currency($add_array['cost'], '', '', false, false) : 0;
 
		    $add_array['trunk_id'] = (isset($add_array['trunk_id']) && $add_array['trunk_id'] > 0)?$add_array['trunk_id']:0;
                    $last_id = $this->rates_model->add_origination_rate($add_array);

                    if ($last_id > 0) {
                        if (isset($add_array['routing_type']) && $add_array['routing_type'] == 1) {
                            $this->origination_set_force_routing($add_array, $last_id);
                        }
                    }
                    echo json_encode(array(
                        "SUCCESS" => $add_array['pattern'].' '.gettext('Origination Rate Added Successfully!')
                    ));

                    exit();
                }
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'rates/origination_rates_list/');
        }
    }

    function origination_set_force_routing($add_array, $routes_id)
    {
        $trunk_id = explode(",", $add_array['trunk_id']);
        $percentage = explode(",", $add_array['percentage']);
        $trunk_count = count($trunk_id);
        foreach ($trunk_id as $key => $value) {
            if ($value != 0) {
                $insert_array = array(
                    "routes_id" => $routes_id,
                    "pricelist_id" => 0,
                    "trunk_id" => $value,
                    "percentage" => $percentage[$key]
                );
                $this->db->insert("routing", $insert_array);
            }
        }
    }

    function origination_rates_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            if (isset($action['connectcost']['connectcost']) && $action['connectcost']['connectcost'] != '') {
                $action['connectcost']['connectcost'] = $this->common_model->add_calculate_currency($action['connectcost']['connectcost'], "", '', false, false);
            }
            $this->session->set_userdata('origination_rate_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'rates/origination_rates_list/');
        }
    }

    function origination_rates_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function origination_rates_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Origination Rates');
        $data['search_flag'] = true;
        $data['batch_update_flag'] = true;
        $data['delete_batch_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->rates_form->build_origination_rate_list_for_admin();
        $data["grid_buttons"] = $this->rates_form->build_grid_buttons_origination_rate();
        $data['form_search'] = $this->form->build_serach_form($this->rates_form->get_origination_rate_search_form());
        $data['form_batch_update'] = $this->form->build_batchupdate_form($this->rates_form->origination_rate_batch_update_form());
        $this->load->view('view_origination_rate_list', $data);
    }

    function origination_rates_list_json()
    {
        $json_data = array();
        $count_all = $this->rates_model->get_origination_rate_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->rates_model->get_origination_rate_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);

        $grid_fields = json_decode($this->rates_form->build_origination_rate_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        $result = $query->result_array();

        echo json_encode($json_data);
    }

    function origination_rates_list_delete($flag = '')
    {
        $json_data = array();
        $this->session->set_userdata('advance_batch_data_delete', 1);
        $count_all = $this->rates_model->get_origination_rate_list(false);
        echo $count_all;
    }

    function termination_rate_add($type = "")
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Create Termination Rate');
        $data['form'] = $this->form->build_form($this->rates_form->get_termination_rate_form_fields(), '');
        $this->load->view('view_termination_rate_add_edit', $data);
    }

    function termination_rate_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Termination Rate');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "outbound_routes", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $edit_data['connectcost'] = $this->common_model->to_calculate_currency($edit_data['connectcost'], '', '', false, false);
        $edit_data['cost'] = $this->common_model->to_calculate_currency($edit_data['cost'], '', '', false, false);
        $edit_data['pattern'] = preg_replace('/[^\d+0-9]/', '', $edit_data['pattern']);
        $data['form'] = $this->form->build_form($this->rates_form->get_termination_rate_form_fields(), $edit_data);
        $this->load->view('view_termination_rate_add_edit', $data);
    }

    function termination_rate_save()
    {
        $add_array = $this->input->post();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['form'] = $this->form->build_form($this->rates_form->get_termination_rate_form_fields(), $add_array);
            if ($add_array['id'] != '') {
                $data['page_title'] = gettext('Edit Termination Rate');
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
                    $add_array['connectcost'] = isset($add_array['connectcost']) && ($add_array['connectcost'] != "") ? $this->common_model->add_calculate_currency($add_array['connectcost'], '', '', false, false) : 0;
                    $add_array['cost'] = isset($add_array['cost']) && ($add_array['cost'] != "") ? $this->common_model->add_calculate_currency($add_array['cost'], '', '', false, false) : 0;
                    $this->rates_model->edit_termination_rate($add_array, $add_array['id']);
                    echo json_encode(array(
                        "SUCCESS" => $add_array['pattern'].' '.gettext('Termination Rate Updated Successfully!')
                    ));
                    exit();
                }
            } else {
                $data['page_title'] = gettext('Add Termination Rate');
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
                    $add_array['connectcost'] = isset($add_array['connectcost']) && ($add_array['connectcost'] != "") ? $this->common_model->add_calculate_currency($add_array['connectcost'], '', '', false, false) : 0;
                    $add_array['cost'] = isset($add_array['cost']) && ($add_array['cost'] != "") ? $this->common_model->add_calculate_currency($add_array['cost'], '', '', false, false) : 0;
                    $this->rates_model->add_termination_rate($add_array);
                    echo json_encode(array(
                        "SUCCESS" => $add_array['pattern'].' '.gettext('Termination Rate Added Successfully!')
                    ));

                    exit();
                }
            }
            $this->load->view('view_termination_rate_add_edit', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'rates/termination_rates_list/');
        }
    }

    function termination_rates_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('termination_rates_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'rates/termination_rates_list/');
        }
    }

    function termination_rates_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function customer_block_pattern_list($accountid, $accounttype)
    {
        $json_data = array();
        $where = array(
            'accountid' => $accountid
        );
        $instant_search = $this->session->userdata('left_panel_search_' . $accounttype . '_pattern');
        $like_str = ! empty($instant_search) ? "(blocked_patterns like '%$instant_search%'  OR  destination like '%$instant_search%' )" : null;
        if (! empty($like_str))
            $this->db->where($like_str);
        $count_all = $this->db_model->countQuery("*", "block_patterns", $where);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        if (! empty($like_str))
            $this->db->where($like_str);
        $this->db->limit($paging_data["paging"]["page_no"], $paging_data["paging"]["start"]);
        $pattern_data = $this->db_model->getSelect("*", "block_patterns", $where, "id", "ASC", $paging_data["paging"]["page_no"], $paging_data["paging"]["start"]);
        $grid_fields = json_decode($this->rates_form->build_pattern_list_for_customer($accountid, $accounttype));
        $json_data['rows'] = $this->form->build_grid($pattern_data, $grid_fields);
        echo json_encode($json_data);
    }

    function termination_rate_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        if (isset($ids) && $ids != "") {
            $where = "id IN ($ids)";
            $this->db->where($where);
            echo $this->db->delete("outbound_routes");
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'rates/termination_rates_list/');
        }
    }

    function origination_rate_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        if (isset($ids) && $ids != "") {
            $where = "id IN ($ids)";
            $this->db->where($where);
            echo $this->db->delete("routes");
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'rates/origination_rates_list/');
        }
    }

    function user_origination_rate_list_json()
    {
        $json_data = array();
        $account_data = $this->session->userdata("accountinfo");
        $markup = $this->common->get_field_name('markup', 'pricelists', array(
            'id' => $account_data["pricelist_id"]
        ));
        $markup = ($markup > 0) ? $markup : 1;

        $count_all = $this->rates_model->get_origination_rate_list_for_user(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->rates_model->get_origination_rate_list_for_user(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->rates_form->build_origination_rate_list_for_user());
        foreach ($query->result_array() as $key => $value) {
            $json_data['rows'][] = array(
                'cell' => array(
                    $this->common->get_only_numeric_val("", "", $value["pattern"]),
                    $value['comment'],
                    $value['inc'],
                    $this->common_model->calculate_currency(($value['cost'] + ($value['cost'] * $markup) / 100), '', '', '', true),
                    $this->common_model->calculate_currency($value['connectcost'], '', '', '', true),
                    $value['includedseconds']
                )
            );
        }
        echo json_encode($json_data);
    }

    function user_origination_rate_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('origination_rate_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'user/user_rates_list/');
        }
    }

    function user_origination_rate_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function customer_rates_download_sample_file($file_name)
    {
        $this->load->helper('download');
        //ASTPPCOM-960 + 961 Start
        $full_path = "./assets/Rates_File/" . $file_name . ".csv";
        //ASTPPCOM-960 + 961 End
        ob_clean();
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false
            )
        );
        $file = file_get_contents($full_path, false, stream_context_create($arrContextOptions));
        //ASTPPCOM-960 + 961 Start
        force_download($file_name.".csv", $file);
        //ASTPPCOM-960 + 961 End
    }

    function termination_rate_batch_update()
    {
        $batch_update_arr = $this->input->post();
        $batch_update_arr["cost"]["cost"] = isset($batch_update_arr["cost"]["cost"]) ? $this->common_model->add_calculate_currency($batch_update_arr["cost"]["cost"], '', '', true, false) : "0.0000";
        $batch_update_arr["connectcost"]["connectcost"] = isset($batch_update_arr["connectcost"]["connectcost"]) ? $this->common_model->add_calculate_currency($batch_update_arr["connectcost"]["connectcost"], '', '', true, false) : "0.0000";
        $result = $this->rates_model->termination_rate_batch_update($batch_update_arr);
        echo json_encode(array(
            "SUCCESS" => gettext("Termination Rates Batch Updated Successfully!")
        ));
        exit();
    }

    function origination_rate_batch_update()
    {
        $batch_update_arr = $this->input->post();
        $batch_update_arr["cost"]["cost"] = (isset($batch_update_arr["cost"]["cost"]) && $batch_update_arr["cost"]["cost"] != "") ? $this->common_model->add_calculate_currency($batch_update_arr["cost"]["cost"], '', '', true, false) : "0.0000";
        $batch_update_arr["connectcost"]["connectcost"] = (isset($batch_update_arr["connectcost"]["connectcost"]) && $batch_update_arr["connectcost"]["connectcost"] != "") ? $this->common_model->add_calculate_currency($batch_update_arr["connectcost"]["connectcost"], '', '', true, false) : "0.0000";
        $result = $this->rates_model->origination_rate_batch_update($batch_update_arr);
        echo json_encode(array(
            "SUCCESS" => gettext("Origination Rates Batch Updated Successfully!")
        ));
        exit();
    }

    function termination_rate_export_cdr_xls()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $query = $this->rates_model->get_termination_rate(true, '', '', false);
        $outbound_array = array();
        ob_clean();
        $outbound_array[] = array(
            gettext("Code"),
            gettext("Destination"),
            gettext("Connection Cost") . " (" . $currency . ")",
            gettext("Grace Time"),
            gettext("Cost / Min") . " (" . $currency . ")",
            gettext("Initial Increment"),
            gettext("Increment"),
            gettext("Priority"),
            gettext("Strip"),
            gettext("Prepend"),
            gettext("Trunk"),
            gettext("Created Date"),
            gettext("Modified Date"),
            gettext("Status")
        );
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                $outbound_array[] = array(
                    $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                    $row['comment'],
                    $this->common_model->calculate_currency($row['connectcost'], '', '', TRUE, false),
                    $row['includedseconds'],
                    $this->common_model->calculate_currency($row['cost'], '', '', TRUE, false),
                    $row['init_inc'],
                    $row['inc'],
                    $row['precedence'],
                    $row['strip'],
                    $row['prepend'],
                    $this->common->get_field_name('name', 'trunks', $row["trunk_id"]),
                    $this->common->convert_GMT_to('', '', $row['creation_date']),
                    $this->common->convert_GMT_to('', '', $row['last_modified_date']),
                    $this->common->get_status('export', '', $row['status'])
                );
            }
        }
        $this->load->helper('csv');

        array_to_csv($outbound_array, gettext('Termination_Rates').'_' . date("Y-m-d") . '.csv');
    }

    function termination_rate_export_cdr_pdf()
    {
        $query = $this->rates_model->get_termination_rate(true, '', '', false);
        $outbound_array = array();
        $this->load->library('fpdf');
        $this->load->library('pdf');
        $this->fpdf = new PDF('P', 'pt');
        $this->fpdf->initialize('P', 'mm', 'A4');
        $this->fpdf->tablewidths = array(
            20,
            30,
            20,
            20,
            20,
            20,
            20,
            20,
            20
        );
        $outbound_array[] = array(
            gettext("Code"),
            gettext("Destination"),
            gettext("Connect Cost"),
            gettext("Included Seconds"),
            gettext("Per Minute Cost"),
            gettext("Initial Increment"),
            gettext("Increment"),
            gettext("Precedence"),
            gettext("Prepend"),
            gettext("Strip")
        );
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                $outbound_array[] = array(
                    $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                    $row['comment'],
                    $row['connectcost'],
                    $row['includedseconds'],
                    $this->common_model->calculate_currency($row['cost']),
                    $row['init_inc'],
                    $row['inc'],
                    $row['precedence'],
                    $row['prepend'],
                    $row['strip']
                );
            }
        }
        $this->fpdf->AliasNbPages();
        $this->fpdf->AddPage();

        $this->fpdf->SetFont('Arial', '', 15);
        $this->fpdf->SetXY(60, 5);
        $this->fpdf->Cell(100, 10, "Outbound Rates Report " . date('Y-m-d'));

        $this->fpdf->SetY(20);
        $this->fpdf->SetFont('Arial', '', 7);
        $this->fpdf->SetFillColor(255, 255, 255);
        $this->fpdf->lMargin = 2;

        $dimensions = $this->fpdf->export_pdf($outbound_array, "7");
        $this->fpdf->Output('Termination_Rate_' . date("Y-m-d") . '.pdf', "D");
    }

    function origination_rate_export_cdr_xls()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $reseller_id = $accountinfo['reseller_id'] > 0 ? $accountinfo['reseller_id'] : 0;
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $query = $this->rates_model->get_origination_rate(true, '', '', false);

        $inbound_array = array();
        ob_clean();
        if (($account_info['type'] == - 1) || ($account_info['type'] == 2)) {
            $inbound_array[] = array(
                gettext("Code"),
                gettext("Destination"),
                gettext("Country"),
                gettext("Connection Cost") . "(" . $currency . ")",
                gettext("Grace Time"),
                gettext("Cost / Min") . "(" . $currency . ")",
                gettext("Initial Increment"),
                gettext("Increment"),
                gettext("Rate Group"),
                gettext("Reseller"),
                gettext("Created Date"),
                gettext("Modified Date"),
                gettext("Status")
            );
        } else {
            $inbound_array[] = array(
                gettext("Code"),
                gettext("Destination"),
                gettext("Country"),
                gettext("Connection Cost") . "(" . $currency . ")",
                gettext("Grace Time"),
                gettext("Cost / Min") . "(" . $currency . ")",
                gettext("Initial Increment"),
                gettext("Increment"),
                gettext("Rate Group"),
                gettext("Created Date"),
                gettext("Modified Date"),
                gettext("Status")
            );
        }

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                $country_id = $this->common->get_field_name('country', 'countrycode', $row['country_id']);

                if (($account_info['type'] == - 1) || ($account_info['type'] == 2)) {
                    $inbound_array[] = array(
                        $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                        (isset($row['comment']) && $row['comment'] != "") ? $row['comment'] : '--',
                        (isset($country_id) && $country_id != "") ? $country_id : '--',
                        $this->common_model->calculate_currency($row['connectcost'], '', '', true, false),
                        $row['includedseconds'],
                        $this->common_model->calculate_currency($row['cost'], '', '', true, false),

                        $row['init_inc'],

                        $row['inc'],
                        $this->common->get_field_name('name', 'pricelists', $row['pricelist_id']),
                        $row['reseller_id'] == 0 ? 'Admin' : $this->common->build_concat_string("first_name,last_name,number", "accounts", $row['reseller_id']),
                        $this->common->convert_GMT_to('', '', $row['creation_date']),
                        $this->common->convert_GMT_to('', '', $row['last_modified_date']),
                        $this->common->get_status('export', '', $row['status'])
                    );
                } else {
                    $inbound_array[] = array(
                        $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                        (isset($row['comment']) && $row['comment'] != "") ? $row['comment'] : '--',
                        (isset($country_id) && $country_id != "") ? $country_id : '--',
                        $this->common_model->calculate_currency($row['connectcost'], '', '', true, false),
                        $row['includedseconds'],
                        $this->common_model->calculate_currency($row['cost'], '', '', true, false),
                        $row['init_inc'],
                        $row['inc'],
                        $this->common->get_field_name('name', 'pricelists', $row['pricelist_id']),
                        $this->common->convert_GMT_to('', '', $row['creation_date']),
                        $this->common->convert_GMT_to('', '', $row['last_modified_date']),
                        $this->common->get_status('export', '', $row['status'])
                    );
                }
            }
        }
        $this->load->helper('csv');
        array_to_csv($inbound_array, gettext('Origination_Rates').'_' . date("Y-m-d") . '.csv');
    }

    function origination_rate_export_cdr_pdf()
    {
        $query = $this->rates_model->get_origination_rate(true, '', '', false);

        $inbound_array = array();
        $this->load->library('fpdf');
        $this->load->library('pdf');
        $this->fpdf = new PDF('P', 'pt');
        $this->fpdf->initialize('P', 'mm', 'A4');
        $this->fpdf->tablewidths = array(
            20,
            20,
            20,
            20,
            20,
            20
        );
        $inbound_array[] = array(
            gettext("Code"),
            gettext("Destination"),
            gettext("Connect Cost"),
            gettext("Included Seconds"),
            gettext("Per Minute Cost"),
            gettext("Initial Increment"),
            gettext("Increment")
        );
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $inbound_array[] = array(
                    $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                    $row['comment'],
                    $row['connectcost'],
                    $row['includedseconds'],
                    $this->common_model->calculate_currency($row['cost'], '', '', '', false),
                    $row['init_inc'],
                    $row['inc']
                );
            }
        }
        $this->fpdf->AliasNbPages();
        $this->fpdf->AddPage();

        $this->fpdf->SetFont('Arial', '', 15);
        $this->fpdf->SetXY(60, 5);
        $this->fpdf->Cell(100, 10, "Origination Rates Report " . date('Y-m-d'));

        $this->fpdf->SetY(20);
        $this->fpdf->SetFont('Arial', '', 7);
        $this->fpdf->SetFillColor(255, 255, 255);
        $this->fpdf->lMargin = 2;

        $dimensions = $this->fpdf->export_pdf($inbound_array, "5");
        $this->fpdf->Output(gettext('Origination_Rate').'_' . date("Y-m-d") . '.pdf', "D");
    }

    function user_origination_rate_cdr_pdf()
    {
        $query = $this->rates_model->get_origination_rate_for_user(true, '', '', false);
        $inbound_array = array();
        $this->load->library('fpdf');
        $this->load->library('pdf');
        $this->fpdf = new PDF('P', 'pt');
        $this->fpdf->initialize('P', 'mm', 'A4');
        $this->fpdf->tablewidths = array(
            20,
            20,
            20,
            20,
            20,
            20
        );
        $inbound_array[] = array(
            gettext("Code"),
            gettext("Destination"),
            gettext("Increment"),
            gettext("Cost Per Minutes"),
            gettext("Connect Charge"),
            gettext("Included Seconds")
        );
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $inbound_array[] = array(
                    $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                    $row['comment'],
                    $row['inc'],
                    $this->common_model->calculate_currency($row['cost'], '', '', '', false),
                    $row['connectcost'],
                    $row['includedseconds']
                );
            }
        }

        $this->fpdf->AliasNbPages();
        $this->fpdf->AddPage();

        $this->fpdf->SetFont('Arial', '', 15);
        $this->fpdf->SetXY(60, 5);
        $this->fpdf->Cell(100, 10, "Rates Report " . date('Y-m-d'));

        $this->fpdf->SetY(20);
        $this->fpdf->SetFont('Arial', '', 7);
        $this->fpdf->SetFillColor(255, 255, 255);
        $this->fpdf->lMargin = 2;

        $dimensions = $this->fpdf->export_pdf($inbound_array, "5");
        $this->fpdf->Output(gettext('Rates').'_' . date("Y-m-d") . '.pdf', "D");
    }

    function resellersrates_list()
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('My Rates');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->rates_form->build_rates_list_for_reseller();
        $data["grid_buttons"] = $this->rates_form->build_grid_buttons_rates();
        $data['form_search'] = $this->form->build_serach_form($this->rates_form->get_reseller_origination_rate_search_form());
        $this->load->view('view_resellersrates_list', $data);
    }

    function resellersrates_list_json()
    {
        $json_data = array();
        $account_data = $this->session->userdata("accountinfo");
        $markup = $this->common->get_field_name('markup', 'pricelists', array(
            'id' => $account_data["pricelist_id"]
        ));
        $count_all = $this->rates_model->getreseller_rates_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->rates_model->getreseller_rates_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->rates_form->build_rates_list_for_reseller());
        foreach ($query->result_array() as $key => $value) {
            $cost = 0;
            if (isset($value['cost']) && $value['cost'] > 0) {
                $cost = $this->common_model->calculate_currency(($value['cost'] + ($value['cost'] * $markup) / 100), '', '', 'true', true);
            }
            $json_data['rows'][] = array(
                'cell' => array(
                    $this->common->get_only_numeric_val("", "", $value["pattern"]),
                    $value['comment'],
                    $this->common_model->calculate_currency($value['connectcost'], '', '', 'true', true),
                    $value['includedseconds'],
                    $cost,
                    $value['inc'],
                    $value['precedence']
                )
            );
        }
        echo json_encode($json_data);
    }

    function resellersrates_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();

            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('resellerrates_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'rates/resellersrates_list/');
        }
    }

    function resellersrates_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('resellerrates_list_search', "");
    }

    function resellersrates_xls()
    {
        $account_info = $accountinfo = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $query = $this->rates_model->getreseller_rates_list(true, '0', '0', '1');
        $customer_array = array();
        ob_clean();

        $customer_array[] = array(
            gettext("Code"),
            gettext("Destination"),
            gettext("Connection Cost") . "(" . $currency . ")",
            gettext("Grace Time"),
            gettext("Cost/Min") . "(" . $currency . ")",
            gettext("Increment"),
            gettext("Priority")
        );

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                $customer_array[] = array(
                    $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                    $row['comment'],
                    $row['connectcost'],
                    $row['includedseconds'],
                    $this->common_model->calculate_currency($row['cost']),
                    $row['inc'],
                    $row['precedence']
                );
            }
        }
        $this->load->helper('csv');
        array_to_csv($customer_array, 'My_Own_Rate_' . date("Y-m-d") . '.csv');
        exit();
    }

    function termination_rates_list_batch_delete()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_batch_delete', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('termination_rates_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'rates/termination_rates_list/');
        }
    }

    function origination_rates_list_batch_delete()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_batch_delete', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('origination_rate_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'rates/origination_rates_list/');
        }
    }

    function termination_rate_import_mapper()
    {
        $data['page_title'] = gettext('Import Termination Rates Using Field Mapper');
        $this->session->set_userdata('import_termination_rate_mapper_csv', "");
        $this->session->set_userdata('import_termination_rate_mapper_csv_error', "");
        $account_info = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $data['currency'] = $currency;
        $this->load->view('view_import_termination_rate_mapper', $data);
    }

    function csv_to_array($filename = '', $delimiter = ',')
    {
        // only regular files (a directory made the read loop endless)
        if (! is_file($filename) || ! is_readable($filename))
            return FALSE;
        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {

                if (! $header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }

            fclose($handle);
        }

        return $data;
    }

    function utf8_converter($array)
    {
        array_walk_recursive($array, function (&$item, $key) {
            if (! mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });
        return $array;
    }

    function termination_rate_mapper_preview_file()
    {
        $invalid_flag = false;
        $check_header = $this->input->post('check_header', true);
        $data['page_title'] = gettext('Import Termination Rates');
        $account_info = $this->session->userdata('accountinfo');
        $currency_id = $account_info['currency_id'];
        $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
        $data['currency'] = $currency;
        $new_final_arr_key = $this->config->item('Termination-rates-field');
        $this->db->where('id', $account_info['currency_id']);
        $this->db->select('currency');
        $currency_info = (array) $this->db->get('currency')->first_row();

        foreach ($new_final_arr_key as $key => $val) {
            $key = str_replace('CURRENCY', $currency_info['currency'], $key);
            $mapto_fields_array[$key] = $val;
        }

        $data['mapto_fields'] = $mapto_fields_array;
        if (! empty($_SERVER['CONTENT_LENGTH']) && empty($_FILES) && empty($_POST)) {
            $data['error'] = gettext("The uploaded file is too large. You must upload a file smaller than")." " . ini_get('upload_max_filesize');
        } else {
            $extension = explode(".", isset($_FILES['termination_rate_import_mapper']['name']) ? $_FILES['termination_rate_import_mapper']['name'] : '');
            if ((isset($extension[1])) && (! isset($extension[2]))) {
                if (isset($_FILES['termination_rate_import_mapper']['name']) && $_FILES['termination_rate_import_mapper']['name'] != "" && isset($_POST['trunk_id']) && $_POST['trunk_id'] != '') {
                    list ($txt, $ext) = explode(".", $_FILES['termination_rate_import_mapper']['name']);
                    if ($ext == "csv" && $_FILES['termination_rate_import_mapper']['size'] > 0) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $_FILES["termination_rate_import_mapper"]["tmp_name"]);
                        $acceptable_mime_types = array(
                            'application/csv',
                            'application/x-csv',
                            'text/csv',
                            'text/comma-separated-values',
                            'text/x-comma-separated-values',
                            'text/tab-separated-values',
                            'text/plain'
                        );
                        $error = $_FILES['termination_rate_import_mapper']['error'];
                        if (! in_array($mime_type, $acceptable_mime_types)) {
                            $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
                        } else {
                            if ($error == 0) {
                                $uploadedFile = $_FILES["termination_rate_import_mapper"]["tmp_name"];

                                $file_data = $this->csv_to_array($uploadedFile);

                                $field_select = (array_keys($file_data[0]));

                                $data['file_data'] = $field_select;

                                $csv_data = $this->utf8_converter($this->csvreader->parse_file($uploadedFile, $field_select, $check_header));

                                if (! empty($csv_data)) {
                                    $full_path = $this->config->item('rates-file-path');
                                    $actual_file_name = "ASTPP-TERMINATION-RATES-" . date("Y-m-d H:i:s") . "." . $ext;
                                    $actual_file_name = str_replace(' ', '-', $actual_file_name);
                                    $actual_file_name = str_replace(':', '-', $actual_file_name);

                                    if (move_uploaded_file($uploadedFile, $full_path . $actual_file_name)) {
                                        $data['field_select'] = serialize($field_select);
                                        $data['csv_tmp_data'] = $csv_data;
                                        $data['trunkid'] = $_POST['trunk_id'];
                                        $data['check_header'] = $check_header;
                                        $data['page_title'] = gettext('Map CSV to Termination Rates');
                                        $this->session->set_userdata('import_termination_rate_mapper_csv', $actual_file_name);
                                    } else {
                                        $data['error'] = gettext("File Uploading Fail Please Try Again");
                                    }
                                }
                            } else {
                                $data['error'] == gettext("File Uploading Fail Please Try Again");
                            }
                        }
                    } else {
                        $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
                    }
                } else {
                    $invalid_flag = true;
                }
            } else {
                $invalid_flag = true;
                $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
            }
        }
        if ($invalid_flag) {
            $str = '';
            if (! isset($_POST['trunk_id']) || empty($_POST['trunk_id'])) {
                $str .= '<div class="col-12">'.gettext("Please Select Trunk.").'</div>';
            }

            if (empty($_FILES['termination_rate_import_mapper']['name'])) {
                $str .= '<div class="col-12">'.gettext("Please Select File.").'</div>';
            }

            $data['error'] = $str;
        }

        $this->load->view('view_import_termination_rate_mapper', $data);
    }

    function termination_rate_rates_mapper_import()
    {
        $row_count = 0;
        $trunkID = $this->input->post("trunkid");
        $check_header = $this->input->post("check_header");
        $pattern_prefix = $this->input->post("pattern-prefix");
        $filefields = unserialize($this->input->post("filefields"), array('allowed_classes' => false));
        if (! is_array($filefields)) {
            $filefields = array();
        }
        $new_final_arr = array();
        $invalid_array = array();
        $new_final_arr_key = array();
        foreach ($filefields as $item) {
            $new_final_arr_key[$item] = $item;
        }
        $screen_path = $this->config->item('screen_path');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
        }

        $full_path = $this->config->item('rates-file-path');
        $terminationrate_file_name = $this->session->userdata('import_termination_rate_mapper_csv');
        $csv_tmp_data = $this->csvreader->parse_file($full_path . $terminationrate_file_name, $new_final_arr_key, $check_header);

        $i = 0;
        foreach ($csv_tmp_data as $key => $csv_data) {
            if ($i != 0 && $i != 1) {
                $str = null;
                $csv_data['pattern'] = ($this->input->post("pattern-prefix")) ? $this->input->post("pattern-prefix") : (! empty($csv_data[$this->input->post("pattern-select")]) ? $csv_data[$this->input->post("pattern-select")] : (isset($csv_data['Code']) ? $csv_data['Code'] : ''));

                $csv_data['comment'] = ($this->input->post("comment-prefix")) ? $this->input->post("comment-prefix") : (! empty($csv_data[$this->input->post("comment-select")]) ? $csv_data[$this->input->post("comment-select")] : (isset($csv_data['Destination']) ? $csv_data['Destination'] : ''));

                $csv_data['connectcost'] = ($this->input->post("connectcost-prefix")) ? $this->input->post("connectcost-prefix") : (! empty($csv_data[$this->input->post("connectcost-select")]) ? $csv_data[$this->input->post("connectcost-select")] : (isset($csv_data['Connection Cost (USD)']) ? $csv_data['Connection Cost (USD)'] : ''));

                $csv_data['includedseconds'] = ($this->input->post("includedseconds-prefix")) ? $this->input->post("includedseconds-prefix") : (! empty($csv_data[$this->input->post("includedseconds-select")]) ? $csv_data[$this->input->post("includedseconds-select")] : (isset($csv_data['Grace Time']) ? $csv_data['Grace Time'] : ''));

                $csv_data['cost'] = ($this->input->post("cost-prefix")) ? $this->input->post("cost-prefix") : (! empty($csv_data[$this->input->post("cost-select")]) ? $csv_data[$this->input->post("cost-select")] : (isset($csv_data['Cost / Min (USD)']) ? $csv_data['Cost / Min (USD)'] : ''));

                $csv_data['init_inc'] = ($this->input->post("init_inc-prefix")) ? $this->input->post("init_inc-prefix") : (! empty($csv_data[$this->input->post("init_inc-select")]) ? $csv_data[$this->input->post("init_inc-select")] : (isset($csv_data['Initial Increment']) ? $csv_data['Initial Increment'] : ''));

                $csv_data['inc'] = ($this->input->post("inc-prefix")) ? $this->input->post("inc-prefix") : (! empty($csv_data[$this->input->post("inc-select")]) ? $csv_data[$this->input->post("inc-select")] : (isset($csv_data['Increment']) ? $csv_data['Increment'] : ''));

                $strip =  str_replace("”",'',str_replace("“",'',$csv_data[$this->input->post("strip-select")]));
                $prepend =  str_replace("”",'',str_replace("“",'',$csv_data[$this->input->post("prepend-select")]));
                $csv_data['Strip']=str_replace("”",'',str_replace("“",'', $csv_data['Strip']));
                $csv_data['Prepend']=str_replace("”",'',str_replace("“",'', $csv_data['Prepend']));

                $csv_data['prepend'] = ($this->input->post("prepend-prefix")) ? $this->input->post("prepend-prefix") : (! empty($prepend) ? $prepend : (isset($csv_data['Prepend']) ? $csv_data['Prepend'] : ''));

                $csv_data['precedence'] = ($this->input->post("precedence-prefix")) ? $this->input->post("precedence-prefix") : (! empty($csv_data[$this->input->post("precedence-select")]) ? $csv_data[$this->input->post("precedence-select")] : (isset($csv_data['Priority']) ? $csv_data['Priority'] : ''));

                $csv_data['strip'] = ($this->input->post("strip-prefix")) ? $this->input->post("strip-prefix") : (! empty($strip) ? $strip : (isset($csv_data['Strip']) ? $csv_data['Strip'] : ''));

                $csv_data['last_modified_date'] = gmdate("Y-m-d H:i:s");
                $csv_data['creation_date'] = gmdate("Y-m-d H:i:s");
                if (! empty($csv_data['Status'])) {
                    $csv_data['status'] = (strtolower($csv_data['Status']) == 'active') ? '0' : '1';
                }
                $str = $this->data_validate($csv_data);
                if ($str != "") {
                    $invalid_array[$i] = $csv_data;
                    $invalid_array[$i]['error'] = $str;
                } else {
                    $new_final_arr[$i]['trunk_id'] = $trunkID;
                    $new_final_arr[$i]['pattern'] = "^" . $csv_data['pattern'] . ".*";
                    $new_final_arr[$i]['prepend'] = $csv_data['prepend'];
                    $new_final_arr[$i]['last_modified_date'] = $csv_data['last_modified_date'];
                    $new_final_arr[$i]['comment'] = $csv_data['comment'];
                    $new_final_arr[$i]['connectcost'] = $csv_data['connectcost'];
                    $new_final_arr[$i]['includedseconds'] = $csv_data['includedseconds'];
                    $new_final_arr[$i]['cost'] = ! empty($csv_data['cost']) && is_numeric($csv_data['cost']) ? $csv_data['cost'] : 0;
                    $new_final_arr[$i]['inc'] = isset($csv_data['inc']) ? $csv_data['inc'] : 0;
                    $new_final_arr[$i]['precedence'] = isset($csv_data['precedence']) ? $csv_data['precedence'] : '';
                    $new_final_arr[$i]['strip'] = isset($csv_data['strip']) ? $csv_data['strip'] : '';
                    $new_final_arr[$i]['status'] = isset($csv_data['status']) ? $csv_data['status'] : '0';
                    $new_final_arr[$i]['reseller_id'] = isset($csv_data['reseller_id']) ? $csv_data['reseller_id'] : '0';
                    $new_final_arr[$i]['creation_date'] = $csv_data['creation_date'];
                    $new_final_arr[$i]['init_inc'] = isset($csv_data['init_inc']) ? $csv_data['init_inc'] : 0;
                    $row_count ++;
                }
            }

            $i ++;
        }
        if (! empty($new_final_arr)) {
            $result = $this->rates_model->bulk_insert_termination_rate($new_final_arr, $row_count);
        }

        unlink($full_path . $terminationrate_file_name);
        $count = count($invalid_array);

        if ($count > 0) {
            $session_id = "-1";
            $fp = fopen($full_path . $session_id . '.csv', 'w');
            foreach ($new_final_arr_key as $key => $value) {
                $custom_array[0][$key] = ucfirst($key);
            }

            $custom_array[0]['error'] = "Error";
            $invalid_array = array_merge($custom_array, $invalid_array);
            foreach ($invalid_array as $err_data) {
                fputcsv($fp, $err_data);
            }

            fclose($fp);
            $this->session->set_userdata('import_termination_rate_mapper_csv_error', $session_id . ".csv");
            $data["error"] = $invalid_array;
            $data['trunkid'] = $trunkID;
            $data['impoted_count'] = count($new_final_arr);
            $data['failure_count'] = count($invalid_array) - 1;
            $data['page_title'] = gettext('Termination Rates Import Error');
            $this->load->view('view_import_error', $data);
        } else {
            $this->session->set_flashdata('astpp_errormsg', 'Total ' . count($new_final_arr) . ' Termination Rates Imported Successfully!');

            redirect(base_url() . "rates/termination_rates_list/");
        }
    }

    function termination_rate_mapper_error_download()
    {
        $this->load->helper('download');
        $error_data = $this->session->userdata('import_termination_rate_mapper_csv_error');
        $full_path = $this->config->item('rates-file-path');
        $data = file_get_contents($full_path . $error_data);
        force_download(gettext("Termination_rate_mapper_error").".csv", $data);
    }

    function reseller_pricelist()
    {
        $add_array = $this->input->post();
        $id = $add_array['id'];
        $pricelist_result = $this->db->get_where('pricelists', array(
            "id" => $id,
            "status" => 0
        ));
        if ($pricelist_result->num_rows() > 0) {
            $pricelist_result_array = $pricelist_result->result_array();

            if (count($pricelist_result_array) > 0) {
                echo json_encode($pricelist_result_array[0]);
            }
        }
        exit();
    }
   
}
?>
 
