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
class Voice_broadcast extends MX_Controller {
	function __construct() {
		parent::__construct ();		
		$this->load->helper ( 'template_inheritance' );
		$this->load->library ( 'session' );
		$this->load->library ( "voice_broadcast_form" );
		$this->load->library ( 'csvreader' );
		$this->load->library ( 'astpp/form','voice_broadcast_form' );
		$this->load->model ( 'voice_broadcast_model' );
		if ($this->session->userdata ( 'user_login' ) == FALSE)
			redirect ( base_url () . '/astpp/login' );
	}

	function voice_broadcast_list() {
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		if ($accountinfo ['type'] == '3'  || $accountinfo ['type'] == '0') {
			$this->session->set_flashdata ( 'astpp_notification', ' Permission denied.' );
			redirect ( base_url () . "user/user/" );
		}
		$data ['username']    = $this->session->userdata ( 'user_name' );
		$data ['page_title']  = gettext ( 'Voice Broadcast' );
		$data ['search_flag'] = true;
		$this->session->set_userdata ( 'advance_search', 0 );
		$data ['grid_fields']  = $this->voice_broadcast_form->build_voice_broadcast_list_for_admin ();
		$data ["grid_buttons"] = $this->voice_broadcast_form->build_grid_buttons ();
		$data ['form_search']  = $this->form->build_serach_form ( $this->voice_broadcast_form->get_voice_broadcast_search_form () );
		$this->load->view ( 'view_voice_broadcast_list', $data );
	}
	
	function voice_broadcast_list_json() {
		$json_data = array ();
		$count_all = $this->voice_broadcast_model->get_voice_broadcast_list ( false );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data   = $paging_data ["json_paging"];
		$query       = $this->voice_broadcast_model->get_voice_broadcast_list ( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields = json_decode ( $this->voice_broadcast_form->build_voice_broadcast_list_for_admin () );
		$json_data ['rows'] = $this->form->build_grid ( $query, $grid_fields );
		echo json_encode ( $json_data );
	}

	function voice_broadcast_download_sample_file($file_name) {
		$this->load->helper ( 'download' );
		$full_path = "./assets/Rates_File/voice_broadcast_sample/" . $file_name . ".csv";
        ob_clean();
		$arrContextOptions = array (
			"ssl" => array (
				"verify_peer" => false,
				"verify_peer_name" => false
			) 
		);
		$file = file_get_contents ( $full_path, false, stream_context_create ( $arrContextOptions ) );
		force_download ( $file_name.".csv", $file );
	}

	function data_validate($csvdata) {		
		$str = null;
		$alpha_regex = "/^[a-z ,.'-]+$/i";
		$alpha_numeric_regex = "/^[a-z0-9 ,.'-]+$/i";
		$email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";
		$str .= $csvdata ['destination_number'] != '' ? null : 'Destination Number,';
		$str = rtrim ( $str, ',' );
		if (! $str) {
			$str .= ! empty ( $csvdata ['destination_number'] ) && is_numeric ( $csvdata ['destination_number'] ) && ( astpp_gt($csvdata ['destination_number'], 0)) ? null : (empty ( $csvdata ['destination_number'] ) ? null : 'Destination Number,');
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

	function customer_depend_list(){
		$add_array = $this->input->post();
		$reseller_id = $add_array['reseller_id'];
		$accountinfo = $this->session->userdata("accountinfo");
		$account_result = $this->db->get_where('accounts', array(
			"reseller_id" => $reseller_id,
			"status" => 0,
			"deleted" => 0,
			"type" => 0
		));
		if ($account_result->num_rows() > 0) {
			$account_result_array = $account_result->result_array();
			foreach ($account_result_array as $key => $value) {
				if(isset($value['company_name']) && $value['company_name'] != ''){
					echo "<option value=" . $value['id'] . ">" . $value['company_name'] ." ". "(". $value['number'] .")". "</option>";
				}else{
					echo "<option value=" . $value['id'] . ">" . $value['first_name'] ." ". $value['last_name'] ." ". "(". $value['number'] .")". "</option>";
				}
			}
		} else {
			echo '';
		}
		exit();
	}

	function voice_broadcast_sip_devices_list(){
		$add_array = $this->input->post();
		$accountid = $add_array['accountid'];
		$accountinfo = $this->session->userdata("accountinfo");
		$account_result = $this->db->get_where('sip_devices', array(
			"accountid" => $accountid,
			"status" => 0,
		));
		if ($account_result->num_rows() > 0) {
			$account_result_array = $account_result->result_array();
			foreach ($account_result_array as $key => $value) {
				echo "<option value=" . $value['id'] . ">" . $value['username'] . "</option>";
			}
		}
		exit();
	}

	function voice_broadcast_add($type = "") {
		$data ['username'] = $this->session->userdata ( 'user_name' );
		$data ['flag']     = 'create';
		$accountinfo       = $this->session->userdata('accountinfo');
		$data['country_id']  = $accountinfo['country_id'];
		$data ['page_title'] = gettext ( 'Create Voice Broadcast' );
		$data ['form']       = $this->form->build_form ( $this->voice_broadcast_form->get_voice_broadcast_form_fields (), '' );
		$this->load->view ( 'view_voice_broadcast_add_edit', $data );
	}

	function voice_broadcast_save() {
		$add_array = $this->input->post ();
		$add_array['reseller_id'] = $add_array['reseller_id_search_drp'];
		$add_array['accountid'] = $add_array['accountid_search_drp'];
		$add_array['sip_device_id'] = $add_array['sip_device_id_search_drp'];
		$add_array['status'] = 1;
		$data ['form'] = $this->form->build_form ( $this->voice_broadcast_form->get_voice_broadcast_form_fields ($add_array['id']), $add_array );
		if($add_array['id'] == ''){
			$data ['page_title'] = gettext ( 'Create Voice Broadcast' );
			if ($this->form_validation->run() == FALSE){
				$data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
			}else{
				$filename=$_FILES;
				$file = $filename['broadcast']['type'];
				$actual_file_name=$filename['broadcast']['name'];
				if($actual_file_name == ''){
					$data['validation_errors'] = json_encode(array('broadcast_error' => "Please upload .wav file."));
					echo $data['validation_errors'];
                	exit();
				}else{
					$actual_file_name = explode(".",$actual_file_name);
					if($actual_file_name[1] != 'wav'){
						$data['validation_errors'] = json_encode(array('broadcast_error' => "Please upload only .wav file."));
						echo $data['validation_errors'];
                		exit();
					}
				}
				$actual_file_name=str_replace(' ', '', $filename['broadcast']['name']);
				$path_parts = pathinfo($actual_file_name);
				$start_file = $path_parts['filename'];
				$uploadedFile1 = $filename['broadcast']['tmp_name'];
				$dir_path=  getcwd()."/upload/voice_broadcast/";
				$file_name= $start_file.'_'.$date.'.'.$path_parts['extension'];
				$path =$dir_path.$file_name;
				if(file_exists(getcwd().'/upload/voice_broadcast/' . $filename)){
					$add_array['broadcast'] = $file_name;
				}else{			
					if (move_uploaded_file($uploadedFile1,$path)) {
						$add_array['broadcast'] =$file_name;
					}
				}
				if(isset($_FILES['destination_number']['name'])){
					$extension = explode(".", $_FILES['destination_number']['name']);
				}
				if($_FILES ['destination_number']['name'] == ''){
					$data['validation_errors'] = json_encode(array('destination_number_error' => "Please upload .csv file."));
					echo $data['validation_errors'];
                	exit();
				}else{
					if($extension[1] != 'csv'){
						$data['validation_errors'] = json_encode(array('destination_number_error' => "Please upload only csv file."));
						echo $data['validation_errors'];
                		exit();
					}
				}
				if ((isset($extension[1])) && (! isset($extension[2]))) {
					if (isset ( $_FILES ['destination_number'] ['name'] ) && $_FILES ['destination_number'] ['name'] != "") {
						list ( $txt, $ext ) = explode ( ".", $_FILES ['destination_number'] ['name'] );
						if ($ext == "csv" && astpp_gt($_FILES ["destination_number"] ['size'], 0)) {
							$error = $_FILES ['destination_number'] ['error'];
							$finfo = finfo_open(FILEINFO_MIME_TYPE);
							$mime_type = finfo_file($finfo, $_FILES["destination_number"]["tmp_name"]);
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
									$uploadedFile = $_FILES ["destination_number"] ["tmp_name"];
									$full_path = $this->config->item ( 'rates-file-path' );
									$actual_file_name = "ASTPP-voice-broadcast-Number-" . date ( "Y-m-d H:i:s" ) . "." . $ext;
									if (move_uploaded_file ( $uploadedFile, $full_path . $actual_file_name )) {
										$check_header = true;
										$data ['csv_tmp_data'] = $this->csvreader->parse_file ( $full_path . $actual_file_name, $voice_broadcast_fields_array, $check_header );
										$this->session->set_userdata ( 'import_voice_broadcast_rate_csv', $actual_file_name );
									} else {
										$data ['error'] = "File Uploading Fail Please Try Again";
									}
								}else{
									$data ['error'] = "File Uploading Fail Please Try Again";
								}
							}
						}else{
							$data ['error'] = "Invalid file format : Only CSV file allows to import records(Can't import empty file)";
						}
						$data ['fields'] =  "Destination Number";
					}else{
						$invalid_flag = true;
					}
				}else{
					$invalid_flag = true;
	                $data['error'] = gettext("Invalid file format : Only CSV file allows to import records(Can't import empty file)");
				}
				$new_final_arr = array ();
				$invalid_array = array ();
				$new_final_arr_key = array (
					'Destination Number' => 'destination_number',
				);
				$check_header = true;
				$accountinfo = $this->session->userdata ( 'accountinfo' );
				$reseller_id = $accountinfo ['type'] == 1 ? $accountinfo ['id'] : 0;
				$full_path   = $this->config->item ( 'rates-file-path' );
				$voice_broadcast_file_name = $this->session->userdata ( 'import_voice_broadcast_rate_csv' );

				$csv_tmp_data = $this->csvreader->parse_file ( $full_path . $voice_broadcast_file_name, $new_final_arr_key, $check_header );
				$flag = false;
				$i    = 0;
				$number_arr = array ();
				foreach ( $csv_tmp_data as $key => $csv_data ) {
					if (isset ( $csv_data ['destination_number'] ) && $csv_data ['destination_number'] != '' && $i != 0) {
						$str = null;
						$str = $this->data_validate ( $csv_data );
						if ($str != "") {
							$invalid_array [$i] = $csv_data;
							$invalid_array [$i] ['error'] = $str;
						} else {
							$csv_data ['destination_number'] = isset ( $csv_data ['destination_number'] ) ? $csv_data ['destination_number'] : '';
							$new_final_arr [$i] = $csv_data;
						}
						$number_arr [] = $csv_data ['destination_number'];
					}
					$i ++;
				}
				$destination_number = '';
				foreach($new_final_arr as $new_destination_number){
					$destination_number .= $new_destination_number['destination_number'].',';
				}
				$destination_number = substr($destination_number, 0, -1);
				$add_array['destination_number'] =$destination_number;
				$this->voice_broadcast_model->add_voice_broadcast($add_array);
				echo json_encode(array(
                    "SUCCESS" => $add_array["voice_broadcast"] .' '. gettext("Voice Broadcast Added Successfully!")
                ));
                exit();
			}
		}
	}

	function voice_broadcast_list_search() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			$this->session->set_userdata ( 'voice_broadcast_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'voice_broadcast/voice_broadcast_list/' );
		}
	}

	function voice_broadcast_list_clearsearchfilter() {
		$this->session->set_userdata ( 'advance_search', 0 );
		$this->session->set_userdata ( 'voice_broadcast_list_search', "" );
	}

	function voice_broadcast_delete_multiple() {
		$add_array = $this->input->post ();
		$where = 'IN (' . $add_array ['selected_ids'] . ')';
		$this->db->where ( 'id ' . $where );
		$this->db->delete ( 'voice_broadcast' );
		echo TRUE;
	}

}