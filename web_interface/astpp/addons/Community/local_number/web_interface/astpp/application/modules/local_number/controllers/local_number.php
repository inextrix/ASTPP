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
#[\AllowDynamicProperties]
class Local_number extends MX_Controller {
	function __construct() {
		parent::__construct ();		
		$this->load->helper ( 'template_inheritance' );
		$this->load->library ( 'session' );
		$this->load->library ( "local_number_form" );
		$this->load->library ( 'csvreader' );
		$this->load->library ( 'astpp/form','local_number_form' );
		$this->load->model ( 'local_number_model' );
		if ($this->session->userdata ( 'user_login' ) == FALSE)
			redirect ( base_url () . '/astpp/login' );
	}
	function local_number_list() {

		$accountinfo = $this->session->userdata ( 'accountinfo' );

		if ($accountinfo ['type'] == '3'  || $accountinfo ['type'] == '0') {
			$this->session->set_flashdata ( 'astpp_notification', ' Permission denied.' );
			redirect ( base_url () . "user/user/" );
		}

		$data ['username']    = $this->session->userdata ( 'user_name' );
		$data ['page_title']  = gettext ( 'Local Number' );
		$data ['search_flag'] = true;
		$this->session->set_userdata ( 'advance_search', 0 );
		$data ['grid_fields']  = $this->local_number_form->build_local_number_list_for_admin ();
		$data ["grid_buttons"] = $this->local_number_form->build_grid_buttons ();
		$data ['form_search']  = $this->form->build_serach_form ( $this->local_number_form->get_local_number_search_form () );
		$this->load->view ( 'view_local_number_list', $data );
	}
	
	function local_number_list_json() {

		$json_data = array ();
		$count_all = $this->local_number_model->get_local_number_list ( false );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data   = $paging_data ["json_paging"];
		$query       = $this->local_number_model->get_local_number_list ( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields = json_decode ( $this->local_number_form->build_local_number_list_for_admin () );
		$json_data ['rows'] = $this->form->build_grid ( $query, $grid_fields );
		echo json_encode ( $json_data );
	}
	function local_number_export_data_xls() {
		$account_info = $accountinfo = $this->session->userdata ( 'accountinfo' );
		$currency_id = $account_info ['currency_id'];
		$currency = $this->common->get_field_name ( 'currency', 'currency', $currency_id );
		$query = $this->local_number_model->get_local_number_list ( true, '0', '10000000' );
		ob_clean ();
		$outbound_array [] = array (
				gettext ( "Number" ),
				gettext ( "Country" ),
				gettext ( "Province/State" ),
				gettext ( "City" ),
				gettext ( "Status" )
		);
		if ($query->num_rows () > 0) {
			foreach ( $query->result_array () as $row ) {
				$outbound_array [] = array (
						$row ['number'],
						$this->common->get_field_name ( "country", "countrycode", $row ['country_id'] ),
						$row ['province'],
						$row ['city'],
						$this->common->get_status ( 'export', '', $row ['status'] )
				);
			}
		}
		$this->load->helper ( 'csv' );
		array_to_csv ( $outbound_array, 'Local_number_' . date ( "Y-m-d" ) . '.csv' );
	}
	function local_number_download_sample_file($file_name) {
		$this->load->helper ( 'download' );
		$full_path = base_url () . "assets/Rates_File/" . $file_name . ".csv";
		$arrContextOptions = array (
				"ssl" => array (
						"verify_peer" => false,
						"verify_peer_name" => false 
				) 
		);
		$file = file_get_contents ( $full_path, false, stream_context_create ( $arrContextOptions ) );
		force_download ( "samplefile.csv", $file );
	}
/*	function local_number_import() {
		$data ['page_title'] = gettext ( 'Import Local Number' );
		$this->session->set_userdata ( 'import_local_number_rate_csv', "" );
		$error_data = $this->session->userdata ( 'import_local_number_csv_error' );
		$full_path = $this->config->item ( 'rates-file-path' );
		if (file_exists ( $full_path . $error_data ) && $error_data != "") {
			unlink ( $full_path . $error_data );
			$this->session->set_userdata ( 'import_local_number_csv_error', "" );
		}
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$this->db->where ( 'id', $accountinfo ['currency_id'] );
		$this->db->select ( 'currency' );
		$currency_info = ( array ) $this->db->get ( 'currency' )->first_row ();
		$data ['fields'] = "Number,Country,Province/State,City,Status";
		$this->load->view ( 'view_import_local_number', $data );
	}*/


	function local_number_import() {
		$data ['page_title'] = gettext ( 'Import Local Number' );
		$this->session->set_userdata ( 'import_local_number_rate_csv', "" );
		$error_data = $this->session->userdata ( 'import_local_number_csv_error' );
		$full_path = $this->config->item ( 'rates-file-path' );
		if (file_exists ( $full_path . $error_data ) && $error_data != "") {
			unlink ( $full_path . $error_data );
			$this->session->set_userdata ( 'import_local_number_csv_error', "" );
		}
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$data ['fields'] = "Number,Country,Province/State,City,Status";
		$this->load->view ( 'view_import_local_number', $data );
	}

	function local_number_preview_file() {
		$data ['page_title'] = gettext ( 'Import Local Number' );

		$config_local_number_array = array (
			'Number' => 'number',
			'Country' => 'country_id',
			'Province/State' => 'province',
			'City' => 'city',
			"Status" => 'status'
		);

		$accountinfo = $this->session->userdata ( 'accountinfo' );

		foreach ( $config_local_number_array as $key => $value ) {
			$local_number_fields_array [$key] = $value;
		}
		$check_header = $this->input->post ( 'check_header', true );
		$invalid_flag = false;
		if (! empty($_SERVER['CONTENT_LENGTH']) && empty($_FILES) && empty($_POST)) {
            $data['error'] = "The uploaded file is too large. You must upload a file smaller than " . ini_get('upload_max_filesize');
        } else {
			if(isset($_FILES['localnumberimport']['name'])){
				$extension = explode(".", $_FILES['localnumberimport']['name']);
			}	
			if ((isset($extension[1])) && (! isset($extension[2]))) {
				if (isset ( $_FILES ['localnumberimport'] ['name'] ) && $_FILES ['localnumberimport'] ['name'] != "") {
					list ( $txt, $ext ) = explode ( ".", $_FILES ['localnumberimport'] ['name'] );
					if ($ext == "csv" && astpp_gt($_FILES ["localnumberimport"] ['size'], 0)) {
						$error = $_FILES ['localnumberimport'] ['error'];
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $_FILES["localnumberimport"]["tmp_name"]);
                        $acceptable_mime_types = array(
                            'application/csv',
                            'application/x-csv',
                            'text/csv',
                            'text/comma-separated-values',
                            'text/x-comma-separated-values',
                            'text/tab-separated-values',
                            'text/plain'
                        );
                        if (! astpp_in_array($mime_type, $acceptable_mime_types)) {
                            $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
                        } else {
							if (astpp_eq0($error)) {
								$uploadedFile = $_FILES ["localnumberimport"] ["tmp_name"];
								$full_path = $this->config->item ( 'rates-file-path' );
								$actual_file_name = "ASTPP-Local-Number-" . date ( "Y-m-d H:i:s" ) . "." . $ext;
								if (move_uploaded_file ( $uploadedFile, $full_path . $actual_file_name )) {
									$data ['page_title'] = gettext ( 'Import Local Number Preview' );
									$data ['csv_tmp_data'] = $this->csvreader->parse_file ( $full_path . $actual_file_name, $local_number_fields_array, $check_header );
									$data ['check_header'] = $check_header;
									$this->session->set_userdata ( 'import_local_number_rate_csv', $actual_file_name );
								} else {
									$data ['error'] = "File Uploading Fail Please Try Again";
								}
							}else{
								$data ['error'] = "File Uploading Fail Please Try Again";
							}
						}	
					} else {
						$data ['error'] = "Invalid file format : Only CSV file allows to import records(Can't import empty file)";
					}
					$data ['fields'] =  "Number,Country,Province/State,City,Status";
				} else {
					$invalid_flag = true;
				}
			}else {
                $invalid_flag = true;
                $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
            }	
		}	
		if ($invalid_flag) {
			$data ['fields'] =  "Number,Country,Province/State,City,Status";
			$str = '';
			if (empty ( $_FILES ['localnumberimport'] ['name'] )) {
				$str .= '<div class="col-12">Please Select  File.</div>';
			}
			$data ['error'] = $str;
		}
		$this->load->view ( 'view_import_local_number', $data );
	}

	function local_number_import_file($check_header = false) {
		$new_final_arr = array ();
		$invalid_array = array ();
		$new_final_arr_key = array (
			'Number'         => 'number',
			'Country'        => 'country_id',
			'Province/State' => 'province',
			'City'           => 'city',
			"Status"         => 'status'
		);
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
		$full_path   = $this->config->item ( 'rates-file-path' );
		$local_number_file_name = $this->session->userdata ( 'import_local_number_rate_csv' );
		$csv_tmp_data = $this->csvreader->parse_file ( $full_path . $local_number_file_name, $new_final_arr_key, $check_header );
		$flag = false;
		$i    = 0;
		$number_arr = array ();
		foreach ( $csv_tmp_data as $key => $csv_data ) {
			if (isset ( $csv_data ['number'] ) && $csv_data ['number'] != '' && $i != 0) {
				$str = null;
				$str = $this->data_validate ( $csv_data );
				if ($str != "") {
					$invalid_array [$i] = $csv_data;
					$invalid_array [$i] ['error'] = $str;
				} else {

					if (! astpp_in_array ( $csv_data ['number'], $number_arr )) {
						$number_count = $this->db_model->countQuery ( 'id', 'local_number', array (
								'number' => $csv_data ['number'] 
						) );
						if (astpp_gt($number_count, 0)) {
							$invalid_array [$i] = $csv_data;
							$invalid_array [$i] ['error'] = 'Duplicate Local number found from database';
						} else {
							$csv_data ['country_id'] = $this->common->get_field_name ( 'id', 'countrycode', array (
								"country" => strtoupper($csv_data ['country_id']) 
							));
							if($csv_data ['country_id'] != '')
							{
								$csv_data ['province'] = isset ( $csv_data ['province'] ) ? $csv_data ['province'] : '';
								$csv_data ['city'] = isset ( $csv_data ['city'] ) ? $csv_data ['city'] : '';
								$csv_data ['created_date'] = gmdate('Y-m-d H:i:s');
								$csv_data ['status'] = $this->common->get_import_status ( $csv_data ['status'] );
								
								$new_final_arr [$i] = $csv_data;
							}else{
								$invalid_array [$i] = $csv_data;
								$invalid_array [$i] ['error'] = 'Country not valid.';
							}
							
						}
					} else {
						$invalid_array [$i] = $csv_data;
						$invalid_array [$i] ['error'] = 'Duplicate Local number found from import file.';
					}
				}
				$number_arr [] = $csv_data ['number'];
			}
			$i ++;
		}
		// print_r($new_final_arr); die;
		if (! empty ( $new_final_arr )) {
			$result = $this->local_number_model->bulk_insert_local_number ( $new_final_arr );
		}
		unlink ( $full_path . $local_number_file_name );
		$count = astpp_count ( $invalid_array );
		if ($count > 0) {
			$session_id = "-1";
			$fp = fopen ( $full_path . $session_id . '.csv', 'w' );
			foreach ( $new_final_arr_key as $key => $value ) {
				$custom_array [0] [$key] = ucfirst ( $key );
			}
			$custom_array [0] ['error'] = "Error";
			$invalid_array = astpp_array_merge ( $custom_array, $invalid_array );
			foreach ( $invalid_array as $err_data ) {
				fputcsv ( $fp, $err_data );
			}
			fclose ( $fp );
			$this->session->set_userdata ( 'import_local_number_csv_error', $session_id . ".csv" );
			$data ["error"] = $invalid_array;
			$data ['import_record_count'] = astpp_count ( $new_final_arr );
			$data ['failure_count'] = astpp_count ( $invalid_array ) - 1;
			$data ['page_title'] = gettext ( 'Local Number Import Error' );
			$this->load->view ( 'view_import_error', $data );
		} else {
			$this->session->set_flashdata ( 'astpp_errormsg', 'Total ' . astpp_count ( $new_final_arr ) . ' Local Number Imported Successfully!' );
			redirect ( base_url () . "local_number/local_number_list/" );
		}
	}
	function local_number_error_download() {
		$this->load->helper ( 'download' );
		$error_data = $this->session->userdata ( 'import_local_number_csv_error' );
		$full_path = $this->config->item ( 'rates-file-path' );
		$data = file_get_contents ( $full_path . $error_data );
		force_download ( "error_local_number_rates.csv", $data );
	}
	function data_validate($csvdata) {
		
		$str = null;
		$alpha_regex = "/^[a-z ,.'-]+$/i";
		$alpha_numeric_regex = "/^[a-z0-9 ,.'-]+$/i";
		$email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";
		$str .= $csvdata ['number'] != '' ? null : 'Number,';
		$str = rtrim ( $str, ',' );
		if (! $str) {
			$str .= ! empty ( $csvdata ['number'] ) && is_numeric ( $csvdata ['number'] ) && ( astpp_gt($csvdata ['number'], 0)) ? null : (empty ( $csvdata ['number'] ) ? null : 'Number,');
			$str .= $csvdata['country_id'] != '' ? null : 'Country,';
			if ($str) {
				$str = rtrim ( $str, ',' );
				$error_field = explode ( ',', $str );
				$count = astpp_count ( $error_field );
				$str .= $count > 1 ? ' are not valid' : ' is not Valid';
				return $str;
			} else {
				return false;
			}
		} else {
			$str = rtrim ( $str, ',' );
			$error_field = explode ( ',', $str );
			$count = astpp_count ( $error_field );
			$str .= $count > 1 ? ' are required' : ' is Required';
			return $str;
		}
	}
	function local_number_add($type = "") {
		$data ['username'] = $this->session->userdata ( 'user_name' );
		$data ['flag']     = 'create';
		$accountinfo       = $this->session->userdata('accountinfo');
		$data['country_id']  = $accountinfo['country_id'];
		$data ['page_title'] = gettext ( 'Create Local Number' );
		$data ['form']       = $this->form->build_form ( $this->local_number_form->get_local_number_form_fields (), '' );
		$this->load->view ( 'view_local_number_add_edit', $data );
	}
	function local_number_edit($edit_id = '') {
		$data ['page_title'] = gettext ( 'Edit Local Number' );
		$where = array (
				'id' => $edit_id 
		);
		$account = $this->db_model->getSelect ( "*", "local_number", $where );
		foreach ( $account->result_array () as $key => $value ) {
			$edit_data = $value;
		}
		$data['country_id']  = $edit_data['country_id'];
		$data ['form']       = $this->form->build_form ( $this->local_number_form->get_local_number_form_fields ($edit_id), $edit_data );
		$this->load->view ( 'view_local_number_add_edit', $data );
	}
	function local_number_save() {

		$add_array = $this->input->post ();
		$data ['form'] = $this->form->build_form ( $this->local_number_form->get_local_number_form_fields ($add_array['id']), $add_array );
		if ($add_array ['id'] != '') {
			$data ['page_title'] = gettext ( 'Edit Local Number' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->local_number_model->edit_local_number ( $add_array, $add_array ['id'] );
				echo json_encode ( array (
						"SUCCESS" => $add_array ["number"] . " Local number updated successfully!" 
				) );
				exit ();
			}
		} else {
			$data ['page_title'] = gettext ( 'Termination Details' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->local_number_model->add_local_number ( $add_array );
				echo json_encode ( array (
						"SUCCESS" => $add_array ['number'] . " Local number added successfully!" 
				) );
				exit ();
			}
		}
	}
	function local_number_list_search() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			$this->session->set_userdata ( 'local_number_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'local_number/local_number_list/' );
		}
	}
	function local_number_list_clearsearchfilter() {
		$this->session->set_userdata ( 'advance_search', 0 );
		$this->session->set_userdata ( 'local_number_list_search', "" );
	}
	function local_number_remove($id) {
		$this->local_number_model->remove_local_number ( $id );
		$this->session->set_flashdata ( 'astpp_notification', ' Local number removed successfully!' );
		redirect ( base_url () . 'local_number/local_number_list/' );
	}
	function local_number_delete_multiple() {
		$add_array = $this->input->post ();
		$where = 'IN (' . $add_array ['selected_ids'] . ')';
		$this->db->where ( 'id ' . $where );
		$this->db->delete ( 'local_number' );
		echo TRUE;
	}
	function local_number_forwarding($edit_id){
		$data ['page_title'] = gettext ( "Local number" );
		// Get Account information from session.
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		// Get Parent informartion
		$reseller_id = ($accountinfo ['type'] == 1 || $accountinfo ['type'] == 5) ? $accountinfo ['id'] : 0;
		$where = array (
				'id' => $edit_id,
				"reseller_id" => $reseller_id
		);
		$account_res = $this->db_model->getSelect ( "type,country_id", "accounts", $where );
		if ($account_res->num_rows () > 0) {
			$account_data = ( array ) $account_res->first_row ();
			$city_info = array (
					"name" => "city_name",
					"id" => "city_id",
					"class" => "city_id"
			);
			$result_city_final = array();
			$data ['city'] = form_dropdown_all ( $city_info, $result_city_final, '' );
			$province_info = array (
					"name" => "province_name",
					"id" => "province_id",
					"class" => "province_id"
			);
			$result_province_final = array();
			$data ['province'] = form_dropdown_all ( $province_info, $result_province_final, '' );
			$local_number_info = array (
					"name" => "local_number_id",
					"id" => "local_number_id",
					"class" => "local_number_id"
			);
			$result_local_number_final = array();
			$data ['local_number'] = form_dropdown_all ( $local_number_info, $result_local_number_final, '' );
			$data ['country_id'] = $account_data ['country_id'];
			$data ['edit_id'] = $edit_id;
			$data ['grid_fields'] = $this->local_number_form->local_number_customer_grid ($edit_id );
			$this->load->view ( 'view_customer_local_numbers', $data );
		} else {
			redirect ( base_url () . 'accounts/customer_list/' );
			exit ();
		}
	}
	function local_number_forwarding_json($edit_id='',$accounttype='customer') {
		$json_data = array ();
		$instant_search = $this->session->userdata ( 'left_panel_search_' . $accounttype . '_local_number' );
		$account_arr = ( array ) $this->db->get_where ( 'accounts', array (
				"id" => $edit_id 
		) )->first_row ();

		$like_str = ! empty ( $instant_search ) ? "(destination_name like '%$instant_search%'
					OR  destination_number like '%$instant_search%'
					    )" : null;
		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
		$where = array (
				"account_id" => $edit_id 
		);
		$count_all = $this->db_model->countQuery ( "*", "local_number_destination", $where );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data = $paging_data ["json_paging"];
		if (! empty ( $like_str ))
			$this->db->where ( $like_str );

		$query = $this->db_model->select ( "*", "local_number_destination", $where, "id", "ASC", $paging_data ["paging"] ["page_no"], $paging_data ["paging"] ["start"] );

		$did_grid_fields = json_decode ( $this->local_number_form->local_number_customer_grid ($edit_id) );
		$json_data ['rows'] = $this->form->build_grid ( $query, $did_grid_fields );
		echo json_encode ( $json_data );
	}
	function local_number_city() {
		$country_id = $_POST ['country_id'];
		$province = $_POST ['province'];
		$local_number_list = $this->db_model->getSelect ( "id,city", "local_number", array (
				'country_id' => $country_id,
				'province'=> $province,
				'status' => '0'
		) );
		$city_arr = array ();
		if ($local_number_list->num_rows () > 0) {
			$local_number_data = $local_number_list->result_array ();
			foreach ( $local_number_data as $key => $value ) {
				$city_arr [$value ['city']] = $value ['city'];
			}
		}
		$city_info = array (
				"name"  => "city_name",
				"id"    => "city_id",
				"class" => "city_id",
				"onchange" => "harsh_test()"
		);
		echo form_dropdown_all ( $city_info, $city_arr, '' );
		exit;
	}
	function local_number_province() {
		$country_id = $_POST ['country_id'];

		$local_number_list = $this->db_model->getSelect ( "id,province", "local_number", array (
				'country_id' => $country_id,
				'status'     => '0'
		) );

		$province_arr = array ();
		if ($local_number_list->num_rows () > 0) {
			$local_number_data = $local_number_list->result_array ();
			foreach ( $local_number_data as $key => $value ) {
				$province_arr [$value ['province']] = $value ['province'];
			}
		}

		$province_info = array (
				"name"  => "province_name",
				"id"    => "province_id",
				"class" => "province_id"
		);
		echo form_dropdown_all ( $province_info, $province_arr, '' );
		exit;
	}

	function local_number_customer() {

		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$account_id  =  $accountinfo ['id'];
		$country_id  = $_POST ['country_id'];
		$city        = $_POST ['city'];
		$province    = $_POST ['province'];

		//$local_number_list = $this->db->query ("SELECT t1.id, t1.number FROM `local_number` as t1 INNER JOIN local_number_destination as t2 ON t1.id != t2.local_number_id WHERE `city` =  '$city' AND `province` =  '$province' AND `country_id` =  '$country_id' AND `status` =  '0'");
		$q = " SELECT * FROM  local_number where  id NOT IN(select  local_number_id from local_number_destination where account_id = '$account_id' ) and `city` =  '$city' AND `province` =  '$province' AND `country_id` =  '$country_id' AND `status` =  '0'";
		$local_number_list = $this->db->query($q);

		$local_arr = array ();
		if ($local_number_list->num_rows () > 0) {
			$local_number_data = $local_number_list->result_array ();
			foreach ( $local_number_data as $key => $value ) {
				$local_arr [$value ['id']] = $value ['number'];
			}
		}
		$local_info = array (
					"name"  => "local_number_id",
					"id"    => "local_number_id",
					"class" => "local_number_id"
		);
		echo form_dropdown_all ( $local_info, $local_arr, '' );
		exit;
	}

	function local_number_action($type,$edit_id) {

		if($type == 'add') {
			$insert_array = array(
						"local_number_id"=> $_POST['local_number_id'],
						"account_id"=> $edit_id,
						"destination_name"=> $_POST['name'],
						"destination_number"=> $_POST['number'],
						"creation_date"=> gmdate('Y-m-d H:i:s')
					);
			$this->db->insert('local_number_destination',$insert_array);
		}
		$row = $this->db->get_where('local_number', array('id' => $_POST['local_number_id']))->row();

		$insert_array_speeddial = array(
					"accountid" => $edit_id,
					"speed_num" => $row->number,
					"number"    => $_POST['number']
				);
		$this->db->insert('speed_dial',$insert_array_speeddial);
		$this->session->set_flashdata ( 'astpp_errormsg', ' Local number forwarding successfully!' );
		redirect ( base_url () . 'accounts/customer_local_number_forwarding/'.$edit_id.'/' );
	}

	function local_number_destination_remove($edit_id,$id) {
/* harsh s for remove speed dial along with local number destination */
		$query = $this->db->get_where('local_number_destination', array('id' => $id));
		$query = $query->first_row();
		$speed_dial_num = $query->destination_number;
		$this->db->where('number',$speed_dial_num);
		$this->db->delete('speed_dial');
		
		$this->db->where('id',$id);
		$this->db->delete('local_number_destination');
		$this->session->set_flashdata ( 'astpp_notification', 'Local number removed successfully!' );
		redirect ( base_url () . 'accounts/customer_local_number_forwarding/'.$edit_id.'/' );
	}

	function local_number_destination_customer_remove($edit_id,$id) {
/* harsh s for remove speed dial along with local number destination */
		$query = $this->db->get_where('local_number_destination', array('id' => $id));
		$query = $query->first_row();
		$speed_dial_num = $query->destination_number;
		$this->db->where('number',$speed_dial_num);
		$this->db->delete('speed_dial');
		$this->db->where('id',$id);
		$this->db->delete('local_number_destination');
		$this->session->set_flashdata ( 'astpp_notification', 'Local number removed successfully!' );
		redirect ( base_url () . 'local_number/local_number_list_customer/' );
	}

	function local_number_list_customer() {

		$accountinfo          = $this->session->userdata ( 'accountinfo' );
		$data ['username']    = $this->session->userdata ( 'user_name' );
		$data ['page_title']  = gettext ( 'Local Number' );
		$data ['search_flag'] = true;
		$this->session->set_userdata ( 'advance_search', 0 );

		$reseller_id=($accountinfo ['type'] == 1 || $accountinfo ['type'] == 5) ? $accountinfo ['id'] : 0;
			$where = array (
					'id'          => $accountinfo['id'],
					"reseller_id" => $reseller_id
			);
			$account_res = $this->db_model->getSelect ( "type", "accounts", $where );
			$account     = $this->db_model->getSelect ( "*", "accounts", $where );

			if ($account->num_rows () > 0) {
				$account_data = ( array ) $account->first_row ();
				$accounttype  = strtolower ( $this->common->get_entity_type ( '', '', $account_data ['type'] ) );
				$data ['accounttype'] = $accounttype; 
			}

			if ($account_res->num_rows () > 0) {
				$account_data = ( array ) $account_res->first_row ();
				$city_info    = array (
						"name"  => "city_name",
						"id"    => "city_id",
						"class" => "city_id"
				);
				$result_city_final = array();

				$data ['city'] = form_dropdown_all ( $city_info, $result_city_final, '' );
				$province_info = array (
						"name"  => "province_name",
						"id"    => "province_id",
						"class" => "province_id"
				);
				$result_province_final = array();
				$data ['province'] = form_dropdown_all ( $province_info, $result_province_final, '' );
				$local_number_info = array (
						"name"  => "local_number_id1",
						"id"    => "local_number_id1",
						"class" => "local_number_id1"
				);
				$result_local_number_final = array();
				$data ['local_number'] = form_dropdown_all ( $local_number_info, $result_local_number_final, '' );
				$data ['country_id']   = $accountinfo ['country_id'];
				$edit_id               = $accountinfo['id'];
				$data ['grid_buttons']  = $this->local_number_form->local_number_customerportal_button ();
				$data ['grid_fields']  = $this->local_number_form->local_number_customerportal_grid ();
				$data ['form_search']  = $this->form->build_serach_form ( $this->local_number_form->get_local_number_customer_search_form () );
				$this->load->view ( 'view_customer_local_numbers_list', $data );
			} else {
				redirect ( base_url () . 'accounts/customer_list/' );
				exit ();
			}
		}

	function local_number_list_customer_json() {

		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$edit_id     = $accountinfo['id'];
		$json_data   = array ();
		$count_all   = $this->local_number_model->get_local_number_list_customer ( false );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data   = $paging_data ["json_paging"];
		$query       = $this->local_number_model->get_local_number_list_customer ( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields = json_decode ( $this->local_number_form->local_number_customerportal_grid ($edit_id) );
		$json_data ['rows'] = $this->form->build_grid ( $query, $grid_fields );
		echo json_encode ( $json_data );
	}

	function local_number_delete_multiple_custoemr() {
	$ids   = $this->input->post ( "selected_ids", true );
    $this->db->select('local_number_id,destination_number');
    $this->db->from('local_number_destination');
    $this->db->where("id IN ($ids)");
    $query = $this->db->get();

	if ( $query->num_rows() > 0 ) {
        $row = $query->result_array();
		$where = array();
        foreach ($row as $key => $value) {
        	$where ['id'] = $value ['local_number_id'];
        	$local_number = $this->db_model->getSelect ( "number", "local_number", $where );
        	$local_number = $local_number->first_row();
        	$local_number = $local_number->number;
        	$where_speeddial = array(
				'speed_num' => $local_number,
				"number"    => $value ['destination_number']
        	);
			$this->db->where($where_speeddial);
			$this->db->delete('speed_dial');
        }
    }

		$where = "id IN ($ids)";
		$this->db->where ( $where );
		echo $this->db->delete ( "local_number_destination" );
	}

    function customer_local_number_forwarding($edit_id) {
		$accountinfo          = $this->session->userdata ( 'accountinfo' );
		$data ['username']    = $this->session->userdata ( 'user_name' );
		$data ['page_title']  = gettext ( 'Local Number' );
		$data ['search_flag'] = true;
		$this->session->set_userdata ( 'advance_search', 0 );

		$reseller_id = ($accountinfo ['type'] == 1 || $accountinfo ['type'] == 5) ? $accountinfo ['id'] : 0;
			$where = array (
					'id' => $edit_id,
					"reseller_id" => $reseller_id
			);
			$account_res = $this->db_model->getSelect ( "type", "accounts", $where );


			$account = $this->db_model->getSelect ( "*", "accounts", $where );
			if ($account->num_rows () > 0) {
					$account_data = ( array ) $account->first_row ();
					$accounttype = strtolower ( $this->common->get_entity_type ( '', '', $account_data ['type'] ) );
					$data ['accounttype'] = $accounttype; 
			}


			if ($account_res->num_rows () > 0) {

				$account_data = ( array ) $account_res->first_row ();
				$city_info = array (
						"name"  => "city_name",
						"id"    => "city_id",
						"class" => "city_id"
				);
				$result_city_final = array();

				$data ['city'] = form_dropdown_all ( $city_info, $result_city_final, '' );
				$province_info = array (
							"name"  => "province_name",
							"id"    => "province_id",
							"class" => "province_id"
				);
				$result_province_final = array();
				$data ['province'] = form_dropdown_all ( $province_info, $result_province_final, '' );
				$local_number_info = array (
							"name"  => "local_number_id",
							"id"    => "local_number_id",
							"class" => "local_number_id"
				);
				$result_local_number_final = array();
				$data ['local_number']     = form_dropdown_all ( $local_number_info, $result_local_number_final, '' );


				$data ['country_id']  = $accountinfo ['country_id'];
				$data ['edit_id']     = $edit_id;
				$data ['grid_fields'] = $this->local_number_form->local_number_customerportalleftpanel_grid_admin ($edit_id );

				$data ['form_search'] = $this->form->build_serach_form ( $this->local_number_form->get_local_number_search_form () );

				$this->load->view ( 'view_customer_local_numbers_leftpanel', $data );
			} else {
				redirect ( base_url () . 'accounts/customer_list/' );
				exit ();
			}
	}

	function customer_local_number_forwarding_json($edit_id='',$accounttype='customer') {

		$json_data      = array ();
		$instant_search = $this->session->userdata ( 'left_panel_search_' . $accounttype . '_local_number' );
		$account_arr    = ( array ) $this->db->get_where ( 'accounts', array (
								"id" => $edit_id ) )->first_row ();
		$like_str       = ! empty ( $instant_search ) ? "(destination_name like '%$instant_search%'
					OR  destination_number like '%$instant_search%'
					    )" : null;
		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
		$where = array (
				"account_id" => $edit_id 
		);
		$count_all    = $this->db_model->countQuery ( "local_number_id,destination_name,destination_number,creation_date", "local_number_destination", $where );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data   = $paging_data ["json_paging"];
		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
		$query       = $this->db_model->select ( "id,local_number_id,destination_name,destination_number,creation_date", "local_number_destination", $where, "id", "ASC", $paging_data ["paging"] ["page_no"], $paging_data ["paging"] ["start"] );

		$did_grid_fields    = json_decode ( $this->local_number_form->local_number_customerportalleftpanel_grid_admin ($edit_id) );
		$json_data ['rows'] = $this->form->build_grid ( $query, $did_grid_fields );
		echo json_encode ( $json_data );
	}
	function local_number_customer_add($type = "") {
		$data ['username']   = $this->session->userdata ( 'user_name' );
		$data ['flag']       = 'create';
		$data ['page_title'] = gettext ( 'Create Local Number' );
		$data ['form']       = $this->form->build_form ( $this->local_number_form->get_local_number_customer_form_field (), '' );
		$this->load->view ( 'view_local_number_add_edit_customer', $data );
	}
	function local_number_customer_edit($edit_id = '') {

		$data ['page_title'] = gettext ( 'Edit Local Number' );
		$where = array (
				'id' => $edit_id 
		);
		$account = $this->db_model->getSelect ( "*", "local_number_destination", $where );
		foreach ( $account->result_array () as $key => $value ) {
			$edit_data = $value;
		}
		$edit_data ["resellers_id"] = explode ( ",", $edit_data ["resellers_id"] );
		$data['country_id']  = '';
		$data ['form'] = $this->form->build_form ( $this->local_number_form->get_local_number_customer_form_field ($edit_id), $edit_data );
		$this->load->view ( 'view_local_number_add_edit', $data );
	}

	function local_number_customer_save() {

		$add_array     = $this->input->post ();
		$data ['form'] = $this->form->build_form ( $this->local_number_form->get_local_number_customer_form_field ($add_array['id']), $add_array );

		if ($add_array ['id'] != '') {
			$data ['page_title'] = gettext ( 'Edit Local Number' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->local_number_model->edit_local_number ( $add_array, $add_array ['id'] );
				echo json_encode ( array (
						"SUCCESS" => $add_array ["number"] . " local number updated successfully!" 
				) );
				exit ();
			}
		} else {
			$data ['page_title'] = gettext ( 'Termination Details' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->local_number_model->add_local_number_customer ( $add_array );
				echo json_encode ( array (
						"SUCCESS" => $add_array ["name"] . " local number added successfully!" 
				) );
				exit ();
			}
		}
	}
	function local_number_destination_customer_save() {
		$add_array     = $this->input->post ();
		$data ['form'] = $this->form->build_form ( $this->local_number_form->get_local_number_customer_form_field ($add_array['id']), $add_array );

		if ($add_array ['id'] != '') {
			$data ['page_title'] = gettext ( 'Edit Local Number' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->local_number_model->edit_local_number_destination ( $add_array, $add_array ['id'] );
				echo json_encode ( array (
						"SUCCESS" => " Local number updated successfully!" 
				) );
				exit ();
			}
		} else {
			$data ['page_title'] = gettext ( 'Termination Details' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->local_number_model->add_local_number_customer ( $add_array );
				echo json_encode ( array (
						"SUCCESS" => " Local number added successfully!" 
				) );
				exit ();
			}
		}
	}

	function local_number_destination_customer_save_admin() {

		$add_array     = $this->input->post ();
		$user_edit_id  = $this->session->userdata ( 'user_edit_id' );
		$add_array ['user_edit_id'] = $user_edit_id;
		$data ['form'] = $this->form->build_form ( $this->local_number_form->get_local_number_customer_form_field ($add_array['id']), $add_array );

		if ($add_array ['id'] != '') {
			$data ['page_title'] = gettext ( 'Edit Local Number' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->local_number_model->edit_local_number_destination_admin ( $add_array, $add_array ['id'] );
				echo json_encode ( array (
						// "SUCCESS" => $add_array ["number"] . " local number updated successfully!" 
						"SUCCESS" => "local number updated successfully!" 
				) );
				exit ();
			}
		} else {
			$data ['page_title'] = gettext ( 'Termination Details' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->local_number_model->add_local_number_customer ( $add_array );
				echo json_encode ( array (
						"SUCCESS" => "local number added successfully!" 
						// "SUCCESS" => $add_array ["name"] . " local number added successfully!" 
				) );
				exit ();
			}
		}
	}

	function local_number_add_customer($type = "") {
		$data ['username']   = $this->session->userdata ( 'user_name' );
		$data ['flag']       = 'create';
		$data ['page_title'] = gettext ( 'Create Local Number' );
		$data ['form']       = $this->form->build_form ( $this->local_number_form->get_local_number_customer_form_fields (), '' );
		$this->load->view ( 'view_local_number_add_edit_customer', $data );
	}

	function local_number_list_customer_search() {

		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			$this->session->set_userdata ( 'local_number_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'local_number/local_number_list/' );
		}
	}

	function local_number_customer_action($type,$edit_id){
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$edit_id = $accountinfo['id'];
		if($type == 'add') {
			$insert_array = array(
						"local_number_id"=> $_POST['local_number_id1'],
						"account_id"=> $edit_id,
						"destination_name"=> $_POST['name'],
						"destination_number"=> $_POST['number'],
						"creation_date"=> gmdate('Y-m-d H:i:s')
					);
			$this->db->insert('local_number_destination',$insert_array);

			$row = $this->db->get_where('local_number', array('id' => $_POST['local_number_id1']))->row();
			$insert_array_speeddial = array(
					"accountid" => $edit_id,
					"speed_num" => $row->number,
					"number"    => $_POST['number']
				);
			$this->db->insert('speed_dial',$insert_array_speeddial);
		}
		$this->session->set_flashdata ( 'astpp_errormsg', ' Local number forwarding successfully!' );
		redirect ( base_url () . 'local_number/local_number_list_customer/' );
	}

	function local_number_destination_customer_edit($edit_id = '') {
		$data ['page_title'] = gettext ( 'Edit Local Number' );
		$where = array (
				'id' => $edit_id 
		);

		$account = $this->db_model->getSelect ( "*", "local_number_destination", $where );
		foreach ( $account->result_array () as $key => $value ) {
			$edit_data = $value;
		}
		if(isset($edit_data ["resellers_id"])) {
			$edit_data ["resellers_id"] = explode ( ",", $edit_data ["resellers_id"] );
		} else {
			$edit_data ["resellers_id"] = '';
		}
		$data['country_id']  = '';
		$data ['form'] = $this->form->build_form ( $this->local_number_form->get_local_number_customer_form_field ($edit_id), $edit_data );
		$this->load->view ( 'view_local_number_add_edit', $data );
	}
	function local_number_destination_customer_edit_admin($edit_id,$id) {

		$this->session->set_userdata ( 'user_edit_id', $edit_id );
		$data ['page_title'] = gettext ( 'Edit Local Number' );
		$where = array (
				'id' => $id 
		);
		$account = $this->db_model->getSelect ( "*", "local_number_destination", $where );
		foreach ( $account->result_array () as $key => $value ) {
			$edit_data = $value;
		}
		if(astpp_array_key_exists('resellers_id', $edit_data)) {
			$edit_data ["resellers_id"] = explode ( ",", $edit_data ["resellers_id"] );
		}
		$data['country_id']  = '';
		$data ['form'] = $this->form->build_form ( $this->local_number_form->get_local_number_customer_form_field_admin ($edit_id), $edit_data );
		$this->load->view ( 'view_local_number_add_edit', $data );
	}
}
