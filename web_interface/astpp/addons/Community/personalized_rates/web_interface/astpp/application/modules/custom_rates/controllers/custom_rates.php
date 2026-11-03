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
class custom_rates extends MX_Controller {
	function __construct() {
		parent::__construct ();
		
		$this->load->helper ( 'template_inheritance' );
		$this->load->library ( 'session' );
		$this->load->library ( 'custom_rates_form' );
		// $this->load->library ( 'astpp/form','custom_rates_form' );
		$this->load->library ( 'astpp/form' );
		$this->load->library ( 'astpp/permission' );
		$this->load->model ( 'custom_rates_model' );
		$this->load->library ( 'csvreader' );
		$this->load->library ('ASTPP_Sms');
		// ini_set ( "memory_limit", "2048M" );
		// ini_set ( "max_execution_time", "259200" );
		if ($this->session->userdata('user_login') == FALSE)
			redirect(base_url() . '/astpp/login');

	}
	
	function customer_customerlist_customrates(){
		if(isset($_POST['reseller_id']) && $_POST['reseller_id'] != ""){
			$reseller_id = $_POST['reseller_id'];	
		}else{
			$reseller_id = 0;
		}

		$accounts_list = $this->db_model->build_concat_dropdown("id,first_name,last_name,number,company_name", "accounts","", array("reseller_id"=>$reseller_id,"type"=>'0,1,3',"status"=>0,"deleted"=>0));
		
		$did_info = array("id" => "accountid1","name" => "accountid", "class" => "accountid1");
		echo $this->form_dropdown($did_info, $accounts_list,"");
		exit;
	}
	
	function customer_customerlist_customrates_search(){
		if(isset($_POST['reseller_id']) && $_POST['reseller_id'] != ""){
			$reseller_id = $_POST['reseller_id'];	
		}else{
			$reseller_id = 0;
		}

		$accounts_list = $this->db_model->build_concat_dropdown("id,first_name,last_name,number,company_name", "accounts","", array("reseller_id"=>$reseller_id,"type"=>'0,1,3',"status"=>0,"deleted"=>0));
		
		$did_info = array("id" => "accountid2","name" => "accountid", "class" => "accountid2");
		echo $this->form_dropdown($did_info, $accounts_list,"");
		exit;
	}
	
	function form_dropdown($name = '', $options = array(), $selected = array())
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}
		if (astpp_count($selected) === 0)
		{
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}
		if(is_array($name)){
			$str=null;
			foreach($name as $key=>$value){
				if($key !='class' && $key!='disabled')
					$str.=$key."='$value' ";
			}
			if(isset($name['disabled']) && $name['disabled']== 'disabled'){
				$str.='disabled = "disabled"';
			}
			$form = "<select ".$str." required='true' class='form-control ".$name['class']."'>\n";
		}else{
			$form = '<select name="'.$name.'" class=form-control >\n>';
		}
		$form .= "<option value=''>".gettext('--Select--')."</option>\n";
		foreach ($options as $key => $val)
		{
			$key = (string) $key;
			if (is_array($val) && ! empty($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";
				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (astpp_in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';
					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
				}
				$form .= '</optgroup>'."\n";
			}
			else
			{
				$sel = (astpp_in_array($key, $selected)) ? ' selected="selected"' : '';
				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}
		$form .= '</select>';
		return $form;
	}
	
	function custom_rate_add($type = "") {
		$data ['username'] = $this->session->userdata ( 'user_name' );
		$data ['flag'] = 'create';
		$data ['page_title'] = gettext ( 'Create Personalized Rate' );
		$data ['routing_type']=0;
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$data ['country_id'] = $accountinfo ['country_id'];
		$data ['form'] = $this->form->build_form ( $this->custom_rates_form->get_custom_rate_form_fields (), '' );
		$data ['trunk_count']=Common_model::$global_config['system_config']['trunk_count'];
		$this->load->view ( 'view_custom_rate_add_edit', $data );
	}
	function custom_rate_edit($edit_id = '') { // print($edit_id);die;
		$this->permission->check_web_record_permission($edit_id,'routes','custom_rates/custom_rates_list/');
		$data ['page_title'] = gettext ( 'Edit Personalized Rate' );
		if ($this->session->userdata ( 'logintype' ) == 1 || $this->session->userdata ( 'logintype' ) == 5) {
			$account_data = $this->session->userdata ( "accountinfo" );
			$reseller = $account_data ['id'];
			$where = array (
				'id' => $edit_id,
				"reseller_id" => $reseller 
			);
		} else {
			$where = array (
				'id' => $edit_id 
			);
		}
		$account = $this->db_model->getSelect ( "*", "routes", $where );
		if ($account->num_rows() > 0) {
			foreach ( $account->result_array () as $key => $value ) {
				$edit_data = $value;
			}
			
			$reseller_id=$edit_data['reseller_id'];
			
			$edit_data ['connectcost'] = $this->common_model->to_calculate_currency ( $edit_data ['connectcost'], '', '', true, false );
			$edit_data ['cost'] = $this->common_model->to_calculate_currency ( $edit_data ['cost'], '', '', true, false );
			$edit_data ['pattern'] = filter_var ( $edit_data ['pattern'], FILTER_SANITIZE_NUMBER_INT );
			$edit_data['trunk_id']=isset($edit_data['trunk_id']) && $edit_data['trunk_id'] != '' ? $edit_data['trunk_id'] : 0;
			$edit_data['trunk_id']=explode(",",$edit_data['trunk_id']);
			$edit_data['percentage']=explode(",",$edit_data['percentage']);
			$data['trunk_count']=Common_model::$global_config['system_config']['trunk_count'];
			
			$data ['form'] = $this->form->build_form ( $this->custom_rates_form->get_custom_rate_form_fields ($edit_id,$reseller_id), $edit_data );



			$data['trunk_id']=$edit_data['trunk_id'];
			if(isset($edit_data['percentage'])){
				$data['percentage']=$edit_data['percentage'];
			}
			$data['routing_type']= $edit_data['routing_type'];
			$data['reseller_id'] = $edit_data['reseller_id'];
			$this->load->view ( 'view_custom_rate_add_edit', $data );
		} else {
			redirect ( base_url () . 'custom_rates/custom_rates_list/' );
		}
	}

	function customer_account_change($reseller_id)
	{
		$reseller_id = $reseller_id > 1 ? $reseller_id : '0';
		$accounts = $this->db_model->getSelect("*", "accounts", array(
			'reseller_id' => $reseller_id,
			'type' => 0,
			'deleted' => 0,
			'status' => 0
		));
		if ($accounts->num_rows() > 0) {
			$accounts_data = $accounts->result_array();
			foreach ($accounts_data as $value) {
				if(isset($value['company_name']) && $value['company_name'] != ''){
					echo "<option value=" . $value['id'] . ">" . $value['company_name'] . " ( " . $value['number'] . ") </option>";	
				}else{
					echo "<option value=" . $value['id'] . ">" . $value['first_name'] . " " . $value['last_name'] . " ( " . $value['number'] . ") </option>";
				}
			}
		} else {
			echo "<select><option value=''>".gettext('--Select--')."</option></select>";
		}
	}


	function custom_rate_save() {
		$add_array = $this->input->post ();
		$code='';
		if(isset($add_array) && (($add_array['country_id']=="") || ($add_array['comment']=="") || ($add_array['call_type']==""))){
			if($add_array['pattern']!=""){
				$string=$add_array ['pattern'];
				$value='';
				$pattern='';
				$str_len=strlen($string);
				for($i=$str_len;$i>0;$i--){
					$value=substr($string, 0, $i);
					$where=array("pattern"=>"^".$value.".*");
					$query=$this->db_model->getSelect ( "*", "ratedeck",$where);
					if($query->num_rows()>0){
						$result=$query->result_array();
						if(isset($add_array['country_id']) && $add_array['country_id']==""){
							$add_array['country_id']=$result[0]['country_id'];
						}
						if(isset($add_array['comment']) && $add_array['comment']==""){
							$add_array['comment']=$result[0]['destination'];
						}
						if(isset($add_array['call_type']) && $add_array['call_type']==""){
							$add_array['call_type']=$result[0]['call_type'];
						}
						break;
					}
				}
				
			}
		}
		$data['trunk_count'] = Common_model::$global_config['system_config']['trunk_count'];
		$data ['form'] = $this->form->build_form ( $this->custom_rates_form->get_custom_rate_form_fields (), $add_array );
		if ($add_array ['id'] != '') {
			$data ['page_title'] = gettext ( 'Edit Personalized Rate' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$add_array ['connectcost'] = isset($add_array ['connectcost']) && ($add_array ['connectcost']!="") ? $this->common_model->add_calculate_currency ( $add_array ['connectcost'], '', '', false, false ):0;
				$add_array ['cost'] = isset($add_array ['cost']) && ($add_array ['cost']!="") ? $this->common_model->add_calculate_currency ( $add_array ['cost'], '', '', false, false ):0;
				$implode_str=null;
				
				if(!empty($add_array['trunk_id_new'])){
					foreach($add_array['trunk_id_new'] as $key=>$value){
						if($value != "" && (!astpp_eq0($value))){
							$implode_str.=$value.",";
						}
					}
					$implode_str=rtrim($implode_str,",");
				}
				
				$add_array['trunk_id']=$implode_str;
				$implode_str_per=null;
				if(!empty($add_array['percentage'])){
					foreach($add_array['percentage'] as $key=>$val){
						if($val != ""){
							$implode_str_per.=$val.",";
						}else{
							$implode_str_per.="0,";
						}
					}
					$implode_str_per = rtrim($implode_str_per,",");
				}
				$add_array['percentage']=$implode_str_per;		
				unset($add_array['trunk_id_new']);
				$trunk_array = array();
				if(isset($add_array['trunk_id']) && $add_array['trunk_id'] != ""){
					$add_array['trunk_id'] = explode(",",$add_array['trunk_id']);
					$trunk_array = astpp_array_values($add_array['trunk_id']);
				}
				$add_array['trunk_id'] = astpp_implode(",",$trunk_array);
				
				$this->db->where('routes_id',$add_array['id']);
				$this->db->delete('routing');	
				
				if(isset($add_array['routing_type']) && $add_array['routing_type'] == 1){
					$this->origination_set_force_routing($add_array,$add_array['id']);
				}
				$this->custom_rates_model->edit_custom_rate ( $add_array, $add_array ['id'] );
				echo json_encode ( array (
					"SUCCESS" => $add_array['pattern'].' '.gettext("Personalized Rate Updated Successfully!") 
				) );
				exit ();
			}	
		} else {
			$data ['page_title'] = gettext ( 'Add Personalized Rate' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$add_array ['connectcost'] = isset($add_array ['connectcost']) && ($add_array ['connectcost']!="") ? $this->common_model->add_calculate_currency ( $add_array ['connectcost'], '', '', false, false ):0;
				$add_array ['cost'] = isset($add_array ['cost']) && ($add_array ['cost']!="") ? $this->common_model->add_calculate_currency ( $add_array ['cost'], '', '', false, false ):0;
				$implode_str=null;
				if(!empty($add_array['trunk_id_new'])){
					foreach($add_array['trunk_id_new'] as $key=>$value){
						if($value != "" && (!astpp_eq0($value))){
							$implode_str.=$value.",";
						}
					}
					$implode_str=rtrim($implode_str,",");
				}
				$add_array['trunk_id']=$implode_str;
				$implode_str_per=null;
				if(!empty($add_array['percentage'])){
					foreach($add_array['percentage'] as $key=>$val){
						if($val != ""){
							$implode_str_per.=$val.",";
						}
					}
					$implode_str_per =rtrim($implode_str_per,",");
				}
				$add_array['percentage']=$implode_str_per;
				unset($add_array['trunk_id_new']);
				$last_id = $this->custom_rates_model->add_custom_rate($add_array);
				if(astpp_gt($last_id, 0)){
					if(isset($add_array['routing_type']) && $add_array['routing_type'] == 1){
						$this->origination_set_force_routing($add_array,$last_id);
					}
				}
				echo json_encode ( array (
					"SUCCESS" =>$add_array['pattern'].' '.gettext("Personalized Rate Added Successfully!") 
				));
				exit ();
			}
		}
	}
	
	function origination_set_force_routing($add_array,$routes_id){
		$trunk_id= explode(",",$add_array['trunk_id']);
		$percentage= explode(",",$add_array['percentage']);
		$trunk_count =astpp_count($trunk_id);
		foreach($trunk_id as $key=>$value){
			if((!astpp_eq0($value))){
				$insert_array =array(
					"routes_id"=>$routes_id,
					"pricelist_id"=>0,
					"trunk_id"=>$value,
					"percentage"=>$percentage[$key],
				);
				$this->db->insert("routing", $insert_array);
			}
		}
	}
	
	function custom_rates_list_search() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			if (isset ( $action ['connectcost'] ['connectcost'] ) && $action ['connectcost'] ['connectcost'] != '') {
				$action ['connectcost'] ['connectcost'] = $this->common_model->add_calculate_currency ( $action ['connectcost'] ['connectcost'], "", '', false, false );
			}
			$this->session->set_userdata ( 'custom_rate_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'custom_rates/custom_rates_list/' );
		}
	}
	function custom_rates_list_clearsearchfilter() {
		$this->session->set_userdata ( 'advance_search', 0 );
		$this->session->set_userdata ( 'account_search', "" );
	}
	
	function custom_rate_delete($id) {
		$this->permission->check_web_record_permission($id,'routes','custom_rates/custom_rates_list/');
		$this->custom_rates_model->remove_custom_rate ( $id );
		$this->session->set_flashdata ( 'astpp_notification', gettext('Personalized rate removed successfully!') );
		redirect ( base_url () . 'custom_rates/custom_rates_list/' );
	}
	function custom_rates_list() { 
		$accountinfo=$this->session->userdata ( "accountinfo" );
		if($accountinfo['type'] == 1 || $accountinfo['type'] == 2){
			$this->common->validate_module_access_level('custom_rates','list','custom_rates_list',"");
		}
		$data ['username'] = $this->session->userdata ( 'user_name' );
		$data ['page_title'] = gettext ( 'Personalized Rates' );
		$data ['search_flag'] = true;
		$data ['batch_update_flag'] = true;
		$data ['delete_batch_flag'] = true;
		$this->session->set_userdata ( 'advance_search', 0 );
		
		$data ['grid_fields'] = $this->custom_rates_form->build_custom_rate_list_for_admin ();
		$data ["grid_buttons"] = $this->custom_rates_form->build_grid_buttons_custom_rate ();
		$data ['form_search'] = $this->form->build_serach_form ( $this->custom_rates_form->get_custom_rate_search_form () );
		$data ['form_batch_update'] = $this->form->build_batchupdate_form ( $this->custom_rates_form->custom_rate_batch_update_form () );

		$personalized_rates = $this->common->get_field_name ('permissions', 'permissions', array ('name' => 'reseller' ));
		$personalized_rates_decode = json_decode($personalized_rates, true);
		$personalized_edit = $personalized_rates_decode['personalized_rates']['personalized_rates_list'];
		if($accountinfo['type'] == 1){
			if(isset($personalized_edit['edit']) && astpp_eq0($personalized_edit['edit'])){
				$data ['permission'] = '1';		
			} else{
				$data ['permission'] = '0';
			}
		}
		$this->load->view ( 'view_custom_rate_list', $data );
	}
	
	function custom_rates_list_json() {
		$accountinfo=$this->session->userdata ( "accountinfo" );
		if($accountinfo['type'] == 1 || $accountinfo['type'] == 2){
			$this->common->validate_module_access_level('custom_rates','list','custom_rates_list',"");
		}
		$json_data = array ();
		$count_all = $this->custom_rates_model->get_custom_rate_list ( false );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data = $paging_data ["json_paging"];
		$query = $this->custom_rates_model->get_custom_rate_list ( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields = json_decode ( $this->custom_rates_form->build_custom_rate_list_for_admin () );
		$json_data ['rows'] = $this->form->build_grid ( $query, $grid_fields );
		$result = $query->result_array();
		foreach($result as $K => $val){
			$wherestr = " pattern = '".$val ['pattern']."'";
			$k=6;
			while($k >= 1){
				$wherestr.= " OR pattern = '".substr($val ['pattern'], 0, $k)."'";
				$k = astpp_n($k)-1;
			}
			$qr = "select destination from 	ratedeck where (".$wherestr.") order by LENGTH (pattern) DESC";
			$destination = $this->db->query($qr);
			if($destination->num_rows() >0){
				$destination = $destination->result_array();
				$destination =$destination[0];
				$json_data['rows'][$K]['cell'][2] =$destination['destination'];
			}
		} 
		echo json_encode ( $json_data );
	}
	function custom_rates_list_delete($flag = '') {
		$json_data = array ();
		$this->session->set_userdata ( 'advance_batch_data_delete', 1 );
		$count_all = $this->custom_rates_model->get_custom_rate_list ( false );
		echo $count_all;
	}
	/**
	 * ****************
	 */
	
	function customer_block_pattern_list($accountid, $accounttype) {
		$json_data = array ();
		$where = array (
			'accountid' => $accountid 
		);
		$instant_search = $this->session->userdata ( 'left_panel_search_' . $accounttype . '_pattern' );
		$like_str = ! empty ( $instant_search ) ? "(blocked_patterns like '%$instant_search%'  OR  destination like '%$instant_search%' )" : null;
		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
		$count_all = $this->db_model->countQuery ( "*", "block_patterns", $where );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data = $paging_data ["json_paging"];
		if (! empty ( $like_str ))
			$this->db->where ( $like_str );
		$this->db->limit($paging_data ["paging"] ["page_no"],$paging_data ["paging"] ["start"]);
		$pattern_data = $this->db_model->getSelect ( "*", "block_patterns", $where, "id", "ASC", $paging_data ["paging"] ["page_no"], $paging_data ["paging"] ["start"] );
		$grid_fields = json_decode ( $this->custom_rates_form->build_pattern_list_for_customer ( $accountid, $accounttype ) );
		$json_data ['rows'] = $this->form->build_grid ( $pattern_data, $grid_fields );
		echo json_encode ( $json_data );
	}
	
	function custom_rate_delete_multiple() {
		$ids = $this->input->post ( "selected_ids", true );
		$where = "id IN ($ids)";
		$this->db->where ( $where );
		echo $this->db->delete ( "routes" );
	}
	function user_custom_rate_list_json() {
		$json_data = array ();
		$account_data = $this->session->userdata ( "accountinfo" );
		$markup = $this->common->get_field_name ( 'markup', 'pricelists', array (
			'id' => $account_data ["pricelist_id"] 
		) );
		$markup = (astpp_gt($markup, 0)) ? $markup : 1;
		
		$count_all = $this->custom_rates_model->get_custom_rate_list_for_user ( false );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data = $paging_data ["json_paging"];
		
		$query = $this->custom_rates_model->get_custom_rate_list_for_user ( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields = json_decode ( $this->custom_rates_form->build_custom_rate_list_for_user () );
		foreach ( $query->result_array () as $key => $value ) {
			$json_data ['rows'] [] = array (
				'cell' => array (
					$this->common->get_only_numeric_val ( "", "", $value ["pattern"] ),
					$value ['comment'],
					$value ['inc'],
					$this->common_model->calculate_currency ( (astpp_n($value ['cost']) + (astpp_n($value ['cost']) * astpp_n($markup)) / 100), '', '', '', true ),
					$this->common_model->calculate_currency ( $value ['connectcost'], '', '', '', true ),
					$value ['includedseconds'] 
				) 
			);
		}
		echo json_encode ( $json_data );
	}
	function user_custom_rate_list_search() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			$this->session->set_userdata ( 'custom_rate_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'user/user_rates_list/' );
		}
	}
	function user_custom_rate_list_clearsearchfilter() {
		$this->session->set_userdata ( 'advance_search', 0 );
		$this->session->set_userdata ( 'account_search', "" );
	}
	function customer_rates_download_sample_file($file_name) {
		$this->load->helper ( 'download' );
		$full_path = base_url () . "assets/Rates_File/" . $file_name . ".csv";
		ob_clean ();
		$arrContextOptions = array (
			"ssl" => array (
				"verify_peer" => false,
				"verify_peer_name" => false 
			) 
		);
		$file = file_get_contents ( $full_path, false, stream_context_create ( $arrContextOptions ) );
		force_download ( "samplefile.csv", $file );
	}
	
	function custom_rate_batch_update() {
		$batch_update_arr = $this->input->post ();
		$batch_update_arr ["cost"] ["cost"] = (isset ( $batch_update_arr ["cost"] ["cost"] ) && $batch_update_arr ["cost"] ["cost"]!="") ? $this->common_model->add_calculate_currency ( $batch_update_arr ["cost"] ["cost"], '', '', true, false ) : "0.0000";
		$batch_update_arr ["connectcost"] ["connectcost"] = (isset ( $batch_update_arr ["connectcost"] ["connectcost"] ) &&  $batch_update_arr ["connectcost"] ["connectcost"]!="") ? $this->common_model->add_calculate_currency ( $batch_update_arr ["connectcost"] ["connectcost"], '', '', true, false ) : "0.0000";
		$result = $this->custom_rates_model->custom_rate_batch_update ( $batch_update_arr );
		echo json_encode ( array (
			"SUCCESS" => "Personalized Rates Batch Updated Successfully!" 
		) );
		exit ();
	}
	
	function custom_rate_export_cdr_xls() {
		$account_info = $accountinfo = $this->session->userdata ( 'accountinfo' );
		$currency_id = $account_info ['currency_id'];
		$reseller_id = astpp_gt($accountinfo ['reseller_id'], 0) ? $accountinfo ['reseller_id'] : 0;
		$currency = $this->common->get_field_name ( 'currency', 'currency', $currency_id );
		$query = $this->custom_rates_model->get_custom_rate ( true, '', '', false );
		
		
		$inbound_array = array ();
		ob_clean ();
		if(($account_info['type']==-1) || ($account_info['type']==2)){
			$inbound_array [] = array (
				gettext ( "Code" ),
				gettext ( "Destination" ),
				gettext ( "Country" ),
				gettext ( "Connection Cost" ) . "(" . $currency . ")",
				gettext ( "Grace Time" ),
				gettext ( "Cost / Min" ) . "(" . $currency . ")",
				gettext ( "Initial Increment" ),
				gettext ( "Increment" ),
				gettext ( "Account" ),
				gettext ( "Reseller" ),
				gettext ( "Created Date" ),
				gettext ( "Modified Date" ),
				gettext ( "Status" ) 
			);
		}else{
			$inbound_array [] = array (
				gettext ( "Code" ),
				gettext ( "Destination" ),
				gettext ( "Country" ),
				gettext ( "Connection Cost" ) . "(" . $currency . ")",
				gettext ( "Grace Time" ),
				gettext ( "Cost / Min" ) . "(" . $currency . ")",
				gettext ( "Initial Increment" ),
				gettext ( "Increment" ),
				gettext ( "Account" ),
				gettext ( "Created Date" ),
				gettext ( "Modified Date" ),
				gettext ( "Status" ) 
			);
		}	
		
		if ($query->num_rows () > 0) {
			foreach ( $query->result_array () as $row ) {
				if(($account_info['type']==-1) || ($account_info['type']==2)){
					$inbound_array [] = array (
						$row ['pattern'] = $this->common->get_only_numeric_val ( "", "", $row ["pattern"] ),
						$row ['comment'],
						$this->common->get_field_name( 'country', 'countrycode', $row ['country_id'] ),
						$this->common_model->calculate_currency ( $row ['connectcost'], '', '', true, false ),
						$row ['includedseconds'],
						$this->common_model->calculate_currency ( $row ['cost'], '', '', true, false ),
							/**
							 * ASTPP 3.0
							 * For Add Initial Increment field
							 * *
							 */
							$row ['init_inc'],
							/**
							 * *****************************************
							 */
							$row ['inc'],
							// $row['precedence'],
							$this->common->get_field_name ( "number", "accounts", $row ['accountid'] ),
							astpp_eq0($row ['reseller_id']) ? 'Admin':$this->common->build_concat_string ( "first_name,last_name,number,company_name", "accounts", $row ['reseller_id']),
							$this->common->convert_GMT_to ( '', '', $row ['creation_date'] ),
							$this->common->convert_GMT_to ( '', '', $row ['last_modified_date'] ),
							$this->common->get_status ( 'export', '', $row ['status'] )
							
						);
				}else{
					$inbound_array [] = array (
						$row ['pattern'] = $this->common->get_only_numeric_val ( "", "", $row ["pattern"] ),
						$row ['comment'],
						$this->common->get_field_name( 'country', 'countrycode', $row ['country_id'] ),
						$this->common_model->calculate_currency ( $row ['connectcost'], '', '', true, false ),
						$row ['includedseconds'],
						$this->common_model->calculate_currency ( $row ['cost'], '', '', true, false ),
							/**
							 * ASTPP 3.0
							 * For Add Initial Increment field
							 * *
							 */
							$row ['init_inc'],
							/**
							 * *****************************************
							 */
							$row ['inc'],
							// $row['precedence'],
							$this->common->get_field_name ( "number", "accounts", $row ['accountid'] ),
							$this->common->convert_GMT_to ( '', '', $row ['creation_date'] ),
							$this->common->convert_GMT_to ( '', '', $row ['last_modified_date'] ),
							$this->common->get_status ( 'export', '', $row ['status'] )
							
						);
				}	
			}
		}
		$this->load->helper ( 'csv' );
		array_to_csv ( $inbound_array, 'personalized_rates_' . date ( "Y-m-d" ) . '.csv' );
	}
	function custom_rate_export_cdr_pdf() {
		$query = $this->custom_rates_model->get_custom_rate ( true, '', '', false );
		
		$inbound_array = array ();
		$this->load->library ( 'fpdf' );
		$this->load->library ( 'pdf' );
		$this->fpdf = new PDF ( 'P', 'pt' );
		$this->fpdf->initialize ( 'P', 'mm', 'A4' );
		$this->fpdf->tablewidths = array (
			20,
			20,
			20,
			20,
			20,
			20 
		);
		$inbound_array [] = array (
			gettext ( "Code" ),
			gettext ( "Destination" ),
			gettext ( "Connect Cost" ),
			gettext ( "Included Seconds" ),
			gettext ( "Per Minute Cost" ),
			gettext ( "Initial Increment" ),
			gettext ( "Increment" ) 
		);
		if ($query->num_rows () > 0) {
			foreach ( $query->result_array () as $row ) {
				$inbound_array [] = array (
					$row ['pattern'] = $this->common->get_only_numeric_val ( "", "", $row ["pattern"] ),
					$row ['comment'],
					$row ['connectcost'],
					$row ['includedseconds'],
					$this->common_model->calculate_currency ( $row ['cost'], '', '', '', false ),
						/**
						 * ASTPP 3.0
						 * For Add Initial Increment field
						 * *
						 */
						$row ['init_inc'],
						/**
						 * **********************************************
						 */
						$row ['inc'] 
					);
			}
		}
		$this->fpdf->AliasNbPages ();
		$this->fpdf->AddPage ();
		
		$this->fpdf->SetFont ( 'Arial', '', 15 );
		$this->fpdf->SetXY ( 60, 5 );
		$this->fpdf->Cell ( 100, 10, "Personalized Rates Report " . date ( 'Y-m-d' ) );
		
		$this->fpdf->SetY ( 20 );
		$this->fpdf->SetFont ( 'Arial', '', 7 );
		$this->fpdf->SetFillColor ( 255, 255, 255 );
		$this->fpdf->lMargin = 2;
		
		$dimensions = $this->fpdf->export_pdf ( $inbound_array, "5" );
		$this->fpdf->Output ( 'custom_rate_' . date ( "Y-m-d" ) . '.pdf', "D" );
	}
	function user_custom_rate_cdr_pdf() {
		$query = $this->custom_rates_model->get_custom_rate_for_user ( true, '', '', false );
		$inbound_array = array ();
		$this->load->library ( 'fpdf' );
		$this->load->library ( 'pdf' );
		$this->fpdf = new PDF ( 'P', 'pt' );
		$this->fpdf->initialize ( 'P', 'mm', 'A4' );
		$this->fpdf->tablewidths = array (
			20,
			20,
			20,
			20,
			20,
			20 
		);
		$inbound_array [] = array (
			gettext ( "Code" ),
			gettext ( "Destination" ),
			gettext ( "Increment" ),
			gettext ( "Cost Per Minutes" ),
			gettext ( "Connect Charge" ),
			gettext ( "Included Seconds" ) 
		);
		if ($query->num_rows () > 0) {
			foreach ( $query->result_array () as $row ) {
				$inbound_array [] = array (
					$row ['pattern'] = $this->common->get_only_numeric_val ( "", "", $row ["pattern"] ),
					$row ['comment'],
					$row ['inc'],
					$this->common_model->calculate_currency ( $row ['cost'], '', '', '', false ),
					$row ['connectcost'],
					$row ['includedseconds'] 
				);
			}
		}
		
		$this->fpdf->AliasNbPages ();
		$this->fpdf->AddPage ();
		
		$this->fpdf->SetFont ( 'Arial', '', 15 );
		$this->fpdf->SetXY ( 60, 5 );
		$this->fpdf->Cell ( 100, 10, "Rates Report " . date ( 'Y-m-d' ) );
		
		$this->fpdf->SetY ( 20 );
		$this->fpdf->SetFont ( 'Arial', '', 7 );
		$this->fpdf->SetFillColor ( 255, 255, 255 );
		$this->fpdf->lMargin = 2;
		
		$dimensions = $this->fpdf->export_pdf ( $inbound_array, "5" );
		$this->fpdf->Output ( 'Rates_' . date ( "Y-m-d" ) . '.pdf', "D" );
	}
	function resellersrates_list() {
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$data ['username'] = $this->session->userdata ( 'user_name' );
		$data ['page_title'] = gettext ( 'My Rates' );
		$data ['search_flag'] = true;
		$this->session->set_userdata ( 'advance_search', 0 );
		$data ['grid_fields'] = $this->custom_rates_form->build_rates_list_for_reseller ();
		$data ["grid_buttons"] = $this->custom_rates_form->build_grid_buttons_rates ();
		$data ['form_search'] = $this->form->build_serach_form ( $this->custom_rates_form->get_reseller_custom_rate_search_form () );
		$this->load->view ( 'view_resellersrates_list', $data );
	}
	function resellersrates_list_json() {
		$json_data = array ();
		$account_data = $this->session->userdata ( "accountinfo" );
		$markup = $this->common->get_field_name ( 'markup', 'pricelists', array (
			'id' => $account_data ["pricelist_id"] 
		) );
		$count_all = $this->custom_rates_model->getreseller_rates_list ( false );
		$paging_data = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data = $paging_data ["json_paging"];
		$query = $this->custom_rates_model->getreseller_rates_list ( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields = json_decode ( $this->custom_rates_form->build_rates_list_for_reseller () );
		foreach ( $query->result_array () as $key => $value ) {
			$cost=0;
			if(isset($value ['cost']) && astpp_gt($value['cost'], 0))
			{
				$cost=$this->common_model->calculate_currency ( (astpp_n($value ['cost']) + (astpp_n($value ['cost']) * astpp_n($markup)) / 100), '', '', 'true', true );
			}
			$json_data ['rows'] [] = array (
				'cell' => array (
					$this->common->get_only_numeric_val ( "", "", $value ["pattern"] ),
					$value ['comment'],
					$this->common_model->calculate_currency ( $value ['connectcost'], '', '', 'true', true ),
					$value ['includedseconds'],
					$cost,
					$value ['inc'],
					$value ['precedence'] 
				) 
			);
		}
		echo json_encode ( $json_data );
	}
	function resellersrates_list_search() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			$this->session->set_userdata ( 'resellerrates_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'custom_rates/resellersrates_list/' );
		}
	}
	function resellersrates_list_clearsearchfilter() {
		$this->session->set_userdata ( 'advance_search', 0 );
		$this->session->set_userdata ( 'resellerrates_list_search', "" );
	}
	function resellersrates_xls() {
		$account_info = $accountinfo = $this->session->userdata ( 'accountinfo' );
		$currency_id = $account_info ['currency_id'];
		$currency = $this->common->get_field_name ( 'currency', 'currency', $currency_id );
		$query = $this->custom_rates_model->getreseller_rates_list ( true, '0', '0', '1' );
		$customer_array = array ();
		ob_clean ();
		
		$customer_array [] = array (
			gettext ( "Code" ),
			gettext ( "Destination" ),
			gettext ( "Connection Cost" ) . "(" . $currency . ")",
			gettext ( "Grace Time" ),
			gettext ( "Cost/Min" ) . "(" . $currency . ")",
			gettext ( "Increment" ),
			gettext ( "Priority" ) 
		);
		
		if ($query->num_rows() > 0) {
			foreach ( $query->result_array () as $row ) {
				
				$customer_array [] = array (
					$row ['pattern'] = $this->common->get_only_numeric_val ( "", "", $row ["pattern"] ),
					$row ['comment'],
					$row ['connectcost'],
					$row ['includedseconds'],
					$this->common_model->calculate_currency ( $row ['cost'] ),
					$row ['inc'],
					$row ['precedence'] 
				);
			}
		}
		$this->load->helper ( 'csv' );
		array_to_csv ( $customer_array, 'My_Own_Rate_' . date ( "Y-m-d" ) . '.csv' );
		exit ();
	}
	/**
	 * *********
	 * ASTPP 3.0
	 * Batch delete
	 * **********
	 */
	
	function custom_rates_list_batch_delete() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_batch_delete', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );
			$this->session->set_userdata ( 'custom_rate_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'custom_rates/custom_rates_list/' );
		}
	}
	/**
	 * ******* Import Mapper Code - ISSUE-142 *********
	 */

	function csv_to_array($filename = '', $delimiter = ',') {
		if (! file_exists ( $filename ) || ! is_readable ( $filename ))
			return FALSE;
		$header = NULL;
		$data = array ();
		if (($handle = fopen ( $filename, 'r' )) !== FALSE) {
			while ( ($row = fgetcsv ( $handle, 1000, $delimiter )) !== FALSE ) {
				
				if (! $header)
					$header = $row;
				else
					$data [] = astpp_array_combine ( $header, $row );
			}
			
			fclose ( $handle );
		}
		
		return $data;
	}
	function utf8_converter($array) {
		array_walk_recursive ( $array, function (&$item, $key) {
			if (! mb_detect_encoding ( $item, 'utf-8', true )) {
				$item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1') /* PHP8: utf8_encode() is deprecated */;
			}
		} );
		return $array;
	}
}
?>

