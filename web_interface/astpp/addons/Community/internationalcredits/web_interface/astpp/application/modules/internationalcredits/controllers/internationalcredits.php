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
class Internationalcredits extends MX_Controller {
	function __construct() {
		
		parent::__construct ();
		$this->load->helper ( 'template_inheritance' );
		$this->load->library ( 'session' );
		$this->load->library ( "internationalcredits_form" );
		$this->load->library ( 'astpp/form','internationalcredits_form' );
		$this->load->library ( 'astpp/permission');
		$this->load->library ( 'astpp/payment');
		$this->load->model ( 'Astpp_common' );
		$this->load->model ( 'common_model' );
		$this->load->model ( 'internationalcredits_model' );
		if ($this->session->userdata ( 'user_login' ) == FALSE)
			redirect ( base_url () . '/astpp/login' );
	}
	function internationalcredits_list() {
		$data ['page_title']   = gettext ( 'International Credits' );
		$data ['search_flag']  = true;
		$this->session->set_userdata ( 'advance_search', 0 );
		$data ['grid_fields']  = $this->internationalcredits_form->build_internationalcredits_list_for_admin ();
		$data ["grid_buttons"] = $this->internationalcredits_form->build_grid_buttons ();
		$data ['form_search']  = $this->form->build_serach_form ( $this->internationalcredits_form->get_search_internationalcredits_form () );
		$this->load->view ( 'view_internationalcredits_list', $data );
	}
	function internationalcredits_list_json() {
		$json_data    = array ();
		$count_all    = $this->internationalcredits_model->get_customer_Account_list ( false );
		$paging_data  = $this->form->load_grid_config ( $count_all, $_GET ['rp'], $_GET ['page'] );
		$json_data    = $paging_data ["json_paging"];
		$query        = $this->internationalcredits_model->get_customer_Account_list ( true, $paging_data ["paging"] ["start"], $paging_data ["paging"] ["page_no"] );
		$grid_fields  = json_decode ( $this->internationalcredits_form->build_internationalcredits_list_for_admin () );
		$json_data ['rows'] = $this->form->build_grid ( $query, $grid_fields );
		echo json_encode ( $json_data );
	}
	function internationalcredits_list_search() {
		$ajax_search = $this->input->post ( 'ajax_search', 0 );
		if ($this->input->post ( 'advance_search', TRUE ) == 1) {
			$this->session->set_userdata ( 'advance_search', $this->input->post ( 'advance_search' ) );
			$action = $this->input->post ();
			unset ( $action ['action'] );
			unset ( $action ['advance_search'] );		
			$this->session->set_userdata ( 'internationalcredits_list_search', $action );
		}
		if (@$ajax_search != 1) {
			redirect ( base_url () . 'internationalcredits/internationalcredits_list/' );
		}
	}
	function internationalcredits_clearsearchfilter() {
		$this->session->set_userdata ( 'advance_search', 0 );
		$this->session->set_userdata ( 'internationalcredits_list_search', "" );
	}
	function internationalcredits_recharge_add() {
		$data ['page_title'] = gettext ( 'Recharge' );
		$data ['form'] = $this->form->build_form ( $this->internationalcredits_form->internationalcredits_recharge_add_form (), '' );
		$this->load->view ( 'view_recharge_add', $data );
	}
	function internationalcredits_save() {
		$add_array = $this->input->post ();
		if (isset($add_array ['int_balance']) && !empty($add_array ['int_balance'])) {
// for the international balance
			unset($add_array ['int_credit_limit']);
			$add_array ['int_balance'] = $this->common_model->add_calculate_currency ($add_array ['int_balance'], "", '', false, false );
			$data ['form']       = $this->form->build_form ( $this->internationalcredits_form->internationalcredits_recharge_add_form ($add_array ['accountid']), $add_array );
			$data ['page_title'] = gettext ( 'Recharge' );

			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->internationalcredits_model->add_recharge ( $add_array );
				$account_info = ( array ) $this->db->get_where ( 'accounts', array (
									"id" => $add_array ['accountid']
								) )->first_row ();
				if(!empty($account_info)) {
					$currency_info = ( array ) $this->db->get_where ( 'currency', array (
						"id" => $account_info ['currency_id']
					) )->first_row ();
				}

				$insert_payment_arr = array (
					"accountid"          => $account_info ['id'],
					"reseller_id"        => $account_info ['reseller_id'],
					"product_category"   => 3,
					"price"              => $add_array ['int_balance'],
					"payment_by"         => 'Manual',
					"payment_method"     => "Manual",
					"order_item_id"      =>  0,
					"charge_type"        => "REFILL",
					"description"        => "Account '(' ".$account_info['number']." ')' has been refilled by Administrator.",
					"invoice_type"       => "credit",
					"is_update_balance"  => "false"
				);
				$this->payment->add_payments_transcation($insert_payment_arr,$account_info,$currency_info);
				echo json_encode ( array (
						"SUCCESS" => " Recharge done successfully!"
				) );
				// echo json_encode ( array (
				// 		"SUCCESS" => " Recharge of ".$add_array ["int_balance"] ."   ".$currency_info ["currency"] ." done successfully!"
				// ) );
				exit ();
			}
		} else {
// for the international credit
			unset($add_array ['int_balance']);
			$add_array ['int_credit_limit'] = $this->common_model->add_calculate_currency ($add_array ['int_credit_limit'], "", '', false, false );
			$data ['form'] = $this->form->build_form ( $this->internationalcredits_form->internationalcredits_recharge_add_form ($add_array ['accountid']), $add_array );
		
			$data ['page_title'] = gettext ( 'Recharge' );
			if ($this->form_validation->run () == FALSE) {
				$data ['validation_errors'] = validation_errors ();
				echo $data ['validation_errors'];
				exit ();
			} else {
				$this->internationalcredits_model->add_recharge_credit ( $add_array );
				$account_info = ( array ) $this->db->get_where ( 'accounts', array (
					"id" => $add_array ['accountid']
				) )->first_row ();
				if(!empty($account_info)) {
					$currency_info = ( array ) $this->db->get_where ( 'currency', array (
						"id" => $account_info ['currency_id']
					) )->first_row ();
				}

				$insert_payment_arr = array (
					"accountid"          => $account_info ['id'],
					"reseller_id"        => $account_info ['reseller_id'],
					"product_category"   => 3,
					"price"              => $add_array ['int_credit_limit'],
					"payment_by"         => 'Manual',
					"payment_method"     => "Manual",
					"order_item_id"      =>  0,
					"charge_type"        => "REFILL",
					"description"        => "Account '(' ".$account_info['number']." ')' has been refilled by Administrator.",
					"invoice_type"       => "credit",
					"is_update_balance"  => "false"
				);
				$this->payment->add_payments_transcation($insert_payment_arr,$account_info,$currency_info);
				// $this->db->insert("payment_transaction",$insert_payment_arr);
				// echo json_encode ( array (
				// 		"SUCCESS" => " Recharge of ".$add_array ["int_credit_limit"] ."   ".$currency_info ["currency"] ." done successfully!"
				// ) );
				echo json_encode ( array (
						"SUCCESS" => " Recharge done successfully!"
				) );
				exit ();
			}
		}
	}
	function internationalcredits_account_status() {
		$accountid=$this->input->post('accountid');
		
		$query =(array)$this->db->get_where('accounts',array("id"=>$accountid))->first_row();
		//$query=(array)$this->db->query('select posttoexternal from accounts where id='.$accountid)->first_row();

		echo $query['posttoexternal'];
	}
	function getRealIpAddr() {
		    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		      	$ip=$_SERVER['HTTP_CLIENT_IP'];
		    }
		    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		      	$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		    }
		    elseif( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '') {
		      	$ip=$_SERVER['REMOTE_ADDR'];
		    } else {
				$ip= getHostByName(getHostName());
		    }
		    return $ip;
	 }
	 function customer_customerlist_for_internation_credit(){
		$add_array = $this->input->post();
		$reseller_id = $add_array['reseller_id'];
		$accountinfo = $this->session->userdata ( "accountinfo" );
		$reseller_id = $accountinfo['type'] ==1 || $accountinfo['type'] ==5 ?$accountinfo['id'] : $reseller_id;
		$this->db->where_in('type',array("0","3","1"));
		$this->db->where('deleted',"0");
		$account_arr = $reseller_arr = $final_array = array();
		$dropdown_params= array("name" => "accountid" ,"id" => "accountid_search_drp", "class" => "col-md-12 form-control selectpicker form-control-lg accountid_search_drp col-md-3");
		$final_array = array(""=>"--Select--");
		$account_result =$this->db->get_where('accounts',array("reseller_id"=>$reseller_id,"status"=>0));
		if($account_result->num_rows () > 0)
		{
			$final_array = array();
			$account_result = $account_result -> result_array();
			foreach ($account_result as $key=>$value) {
					if($value['type'] ==1){
						$reseller_arr[$value['id']] =  $value['first_name']." ".$value['last_name']."( ".$value['number']." )";
					}else{
						$account_arr[$value['id']] =  $value['first_name']." ".$value['last_name']."( ".$value['number']." )";
					}
			}
			if(!empty($reseller_arr))
				$final_array['Reseller'] = $reseller_arr;
			if(!empty($account_arr))
				$final_array['Customer'] = $account_arr; 
		}
		echo form_dropdown($dropdown_params, $final_array, "");
		exit;	
	}
}
?>
