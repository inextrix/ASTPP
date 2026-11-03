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
#[\AllowDynamicProperties]
class Ratedeck extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->library('astpp/form', 'ratedeck_form');
        $this->load->library('astpp/permission');
        $this->load->config('ratedeck');
        $this->load->model('ratedeck_model');
        $this->load->library("ratedeck_form");
        $this->load->library('csvreader');
        $this->load->library('ASTPP_Sms');
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "259200");
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/login/login');
    }

    function ratedeck_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Add Ratedeck');
        $accountinfo = $this->session->userdata('accountinfo');
        $data['country_id'] = $accountinfo['country_id'];
        $data['form'] = $this->form->build_form($this->ratedeck_form->get_ratedeck_form_fields(), '');
        $this->load->view('view_ratedeck_add_edit', $data);
    }

    function ratedeck_edit($edit_id = '')
    {
        $this->permission->check_web_record_permission($edit_id, 'taxes', 'taxes/taxes_list/');
        $data['page_title'] = gettext('Edit Ratedeck');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "ratedeck", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['country_id'] = $edit_data['country_id'];
        $edit_data['pattern'] = filter_var($edit_data['pattern'], FILTER_SANITIZE_NUMBER_INT);
        $data['form'] = $this->form->build_form($this->ratedeck_form->get_ratedeck_form_fields(), $edit_data);
        $this->load->view('view_ratedeck_add_edit', $data);
    }

    function ratedeck_save()
    {
        $add_array = $this->input->post();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['form'] = $this->form->build_form($this->ratedeck_form->get_ratedeck_form_fields(), $add_array);
            $add_array['pattern'] = trim($add_array['pattern']);
            if ($add_array['id'] != '') {
                $data['page_title'] = gettext('Edit Ratedeck');
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
                    $code = $add_array['pattern'];
                    $code = "^" . $code . ".*";
                    $where = array(
                        "pattern" => $code,
                        "country_id" => $add_array['country_id']
                    );
                    $query = $this->ratedeck_model->check_unique_ratedeck_for_edit($where);
                    $result = $query->result_array();
                    if ($result[0]['id'] != $add_array['id'] && $result[0]['pattern'] == $add_array['pattern'] && $result[0]['country_id'] == $add_array['country_id']) {
                        echo json_encode(array(
                            "pattern_error" => gettext("Code is already in system"),
                            "country_id_error" => gettext("Country is already in system")
                        ));
                        exit();
                    } else {
                        $this->ratedeck_model->edit_ratedeck($add_array, $add_array['id']);
                        echo json_encode(array(
                            "SUCCESS" => gettext("Ratedeck updated successfully!")
                        ));
                        exit();
                    }
                }
            } else {
                $data['page_title'] = gettext('Add Ratedeck');
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {
                    $code = $add_array['pattern'];
                    $code = "^" . $code . ".*";
                    $where = array(
                        "pattern" => $code,
                        "country_id" => $add_array['country_id']
                    );
                    $query = $this->ratedeck_model->check_unique_ratedeck($where);
                    if (astpp_gt($query, 0)) {
                        echo json_encode(array(
                            "pattern_error" => gettext("Code is already in system"),
                            "country_id_error" => gettext("Country is already in system")
                        ));
                        exit();
                    }
                    $this->ratedeck_model->add_ratedeck($add_array);
                    echo json_encode(array(
                        "SUCCESS" => gettext("Ratedeck added successfully!")
                    ));
                    exit();
                }
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'ratedeck/ratedeck_list/');
        }
    }

    function ratedeck_delete($id)
    {
        $this->permission->check_web_record_permission($id, 'ratedeck', 'ratedeck/ratedeck_list/');
        $this->ratedeck_model->remove_ratedeck($id);
        $this->session->set_flashdata('astpp_notification', gettext('Ratedeck removed successfully!'));
        redirect(base_url() . 'ratedeck/ratedeck_list/');
    }

    function ratedeck_list()
    {
        $data['page_title'] = gettext('Ratedeck');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->ratedeck_form->build_ratedeck_list_for_admin();
        $data["grid_buttons"] = $this->ratedeck_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->ratedeck_form->get_search_ratedeck_form());
        $this->load->view('view_ratedeck_list', $data);
    }

    function ratedeck_list_json()
    {
        $json_data = array();
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : 0;
        $count_all = $this->ratedeck_model->get_ratedeck_list($reseller_id, false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->ratedeck_model->get_ratedeck_list($reseller_id, true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->ratedeck_form->build_ratedeck_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function ratedeck_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            unset($_POST['action']);
            unset($_POST['advance_search']);
            $this->session->set_userdata('ratedeck_list_search', $this->input->post());
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'ratedeck/ratedeck_list/');
        }
    }

    function ratedeck_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('ratedeck_list_search', "");
    }

    function ratedeck_delete_multiple()
    {
        $this->db->where("id IN (" . $this->input->post("selected_ids", true) . ")");
        echo $this->db->delete("ratedeck");
    }

    function ratedeck_export()
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : 0;
        $query = $this->ratedeck_model->get_ratedeck_list($reseller_id, true, '', '', false);
        $header_arr = array();
        ob_clean();
        $outbound_array[] = array(
            gettext("Code"),
            gettext("Destination"),
            gettext("Call Type"),
            gettext("Country Name")
        );
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                $outbound_array[] = array(
                    $row['pattern'] = $this->common->get_only_numeric_val("", "", $row["pattern"]),
                    $row['destination'],
                    $this->common->get_field_name('call_type', 'calltype', $row["call_type"]),
                    $this->common->get_field_name('country', 'countrycode', $row["country_id"])
                );
            }
        }
        $this->load->helper('csv');
        array_to_csv($outbound_array, 'Ratedeck_' . date("Y-m-d") . '.csv');
    }

    function ratedeck_import()
    {
        $data['page_title'] = gettext('Import Ratedeck');
        $this->session->set_userdata('import_ratedeck_rate_csv', "");
        $error_data = $this->session->userdata('import_ratedeck_csv_error');
        $full_path = $this->config->item('rates-file-path');
        if (file_exists($full_path . $error_data) && $error_data != "") {
            unlink($full_path . $error_data);
            $this->session->set_userdata('import_ratedeck_csv_error', "");
        }
        $accountinfo = $this->session->userdata('accountinfo');
        $data['fields'] = gettext("Code,Destination,Country");
        $this->load->view('view_import_ratedeck', $data);
    }

    function ratedeck_preview_file()
    {
        $data['page_title'] = gettext('Import Ratedeck');

        $config_ratedeck_array = array(
            'Code' => 'pattern',
            'Destination' => 'destination',
            'Country' => 'country_id'
        );

        $accountinfo = $this->session->userdata('accountinfo');

        foreach ($config_ratedeck_array as $key => $value) {
            $ratedeck_fields_array[$key] = $value;
        }
        $check_header = $this->input->post('check_header', true);
        $invalid_flag = false;

        if (isset($_FILES['ratedeckimport']['name']) && $_FILES['ratedeckimport']['name'] != "") {
            list ($txt, $ext) = explode(".", $_FILES['ratedeckimport']['name']);
            if ($ext == "csv" && astpp_gt($_FILES["ratedeckimport"]['size'], 0)) {
                $error = $_FILES['ratedeckimport']['error'];
                if (astpp_eq0($error)) {
                    $uploadedFile = $_FILES["ratedeckimport"]["tmp_name"];
                    $full_path = $this->config->item('rates-file-path');
                    $actual_file_name = "ASTPP-ratedeck-" . date("Y-m-d H:i:s") . "." . $ext;
                    if (move_uploaded_file($uploadedFile, $full_path . $actual_file_name)) {
                        $data['page_title'] = gettext('Import Ratedeck Preview');
                        $data['csv_tmp_data'] = $this->csvreader->parse_file($full_path . $actual_file_name, $ratedeck_fields_array, $check_header);
                        $data['check_header'] = $check_header;
                        $this->session->set_userdata('import_ratedeck_rate_csv', $actual_file_name);
                    } else {
                        $data['error'] = gettext("File Uploading Fail Please Try Again");
                    }
                }
            } else {
                $data['fields'] = gettext("Code,Destination,Province/State,City,Status");
                $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
            }
        } else {
            $invalid_flag = true;
        }
        if ($invalid_flag) {
            $data['fields'] = gettext("Number,Country,Country");
            $str = '';
            if (empty($_FILES['ratedeckimport']['name'])) {
                $str .= '<br/>'.gettext("Please Select File.");
            }
            $data['error'] = $str;
        }
        $this->load->view('view_import_ratedeck', $data);
    }

    function ratedeck_import_file($check_header = false)
    {
        $new_final_arr = array();
        $invalid_array = array();
        $new_final_arr_key = array(
            'Code' => 'pattern',
            'Destination' => 'destination',
            'Country' => 'country_id'
        );
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $full_path = $this->config->item('rates-file-path');
        $ratedeck_file_name = $this->session->userdata('import_ratedeck_rate_csv');
        $csv_tmp_data = $this->csvreader->parse_file($full_path . $ratedeck_file_name, $new_final_arr_key, $check_header);
        $flag = false;
        $i = 0;
        $number_arr = array();
        foreach ($csv_tmp_data as $key => $csv_data) {
            if (isset($csv_data['pattern']) && $csv_data['pattern'] != '' && $i != 0) {
                $str = null;

                $str = $this->data_validate($csv_data);
                if ($str != "") {
                    $invalid_array[$i] = $csv_data;
                    $invalid_array[$i]['error'] = $str;
                } else {

                    if (! astpp_in_array($csv_data['pattern'], $number_arr)) {

                        $number_count = $this->db_model->countQuery('id', 'ratedeck', array(
                            'pattern' => "^" . $csv_data['pattern'] . ".*"
                        ));
                        if (astpp_gt($number_count, 0)) {
                            $invalid_array[$i] = $csv_data;
                            $invalid_array[$i]['error'] = gettext('Duplicate Ratedeck number found from database');
                        } else {
                            $csv_data['destination'] = $csv_data['destination'];
                            $csv_data['pattern'] = "^" . $csv_data['pattern'] . ".*";
                            $csv_data['creation_date'] = gmdate('Y-m-d H:i:s');
                            $csv_data['status'] = '0';
                            if (astpp_array_key_exists('country_id', $csv_data)) {
                                $csv_data['country_id'] = $this->common->get_field_name('id', 'countrycode', array(
                                    "country" => strtoupper($csv_data['country_id'])
                                ));
                            }
                            $new_final_arr[$i] = $csv_data;
                        }
                    } else {
                        $invalid_array[$i] = $csv_data;
                        $invalid_array[$i]['error'] = gettext('Duplicate Ratedeck Number found from import file.');
                    }
                }
                $number_arr[] = $csv_data['pattern'];
            }
            $i ++;
        }
        if (! empty($new_final_arr)) {
            $result = $this->ratedeck_model->bulk_insert_ratedeck($new_final_arr);
        }
        unlink($full_path . $ratedeck_file_name);
        $count = astpp_count($invalid_array);
        if ($count > 0) {
            $session_id = "-1";
            $fp = fopen($full_path . $session_id . '.csv', 'w');
            foreach ($new_final_arr_key as $key => $value) {
                $custom_array[0][$key] = ucfirst($key);
            }
            $custom_array[0]['error'] = "Error";
            $invalid_array = astpp_array_merge($custom_array, $invalid_array);
            foreach ($invalid_array as $err_data) {
                fputcsv($fp, $err_data);
            }
            fclose($fp);
            $this->session->set_userdata('import_ratedeck_csv_error', $session_id . ".csv");
            $data["error"] = $invalid_array;
            $data['import_record_count'] = astpp_count($new_final_arr);
            $data['failure_count'] = astpp_count($invalid_array) - 1;
            $data['page_title'] = gettext('Ratedeck Import Error');
            $this->load->view('view_import_error', $data);
        } else {
            $this->session->set_flashdata('astpp_errormsg', 'Total ' . astpp_count($new_final_arr) . 'Ratedeck Imported Successfully!');
            redirect(base_url() . "ratedeck/ratedeck_list/");
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
            $str .= preg_match("/^([a-z0-9])+$/i", $csvdata['pattern']) ? null : 'Code,';
            if ($str) {
                $str = rtrim($str, ',');
                $error_field = explode(',', $str);
                $count = astpp_count($error_field);
                $str .= $count > 1 ? ' are not valid' : ' is not Valid';
                return $str;
            } else {
                return false;
            }
        } else {
            $str = rtrim($str, ',');
            $error_field = explode(',', $str);
            $count = astpp_count($error_field);
            $str .= $count > 1 ? ' are required' : ' is Required';
            return $str;
        }
    }

    function ratedeck_error_download()
    {
        ob_clean();
        $this->load->helper('download');
        $error_data = $this->session->userdata('import_ratedeck_csv_error');
        $full_path = $this->config->item('rates-file-path');
        $data = file_get_contents($full_path . $error_data);
        ob_clean();
        force_download("error_ratedeck_rates.csv", $data);
        ob_clean();
    }

    function ratedeck_download_sample_file()
    {
        $file_name = 'ratedeck_sample';
        $this->load->helper('download');
        $full_path = "./assets/Rates_File/" . $file_name . ".csv";
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false
            )
        );
        $file = file_get_contents($full_path, false, stream_context_create($arrContextOptions));
        ob_clean();
        force_download("ratedeck_sample.csv", $file);
        ob_clean();
    }
}
?>
 

