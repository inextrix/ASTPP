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
class dashboard extends MX_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper ( 'form' );
		$this->load->model ( 'Auth_model' );
		$this->load->library ( "astpp/form" );
		$this->load->model ( 'Astpp_common' );
		$this->load->model ( 'dashboard_model' );
		$this->load->library ( 'freeswitch_lib' );
		$this->load->library ('ASTPP_Sms');
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		if($accountinfo['type'] == '0' || $accountinfo['type'] == '3'){
			redirect ( base_url () . 'user/user/' );
		}
	}
	function index() {

		if ($this->session->userdata ( 'user_login' ) == FALSE)
			redirect ( base_url () . 'login/login' );
		$data ['page_title'] = gettext ( 'Dashboard' );
		if ($this->session->userdata ( 'logintype' ) == 0) {
			$this->load->view ( 'view_user_dashboard', $data );
		} else {
			$accountinfo = $this->session->userdata ( 'accountinfo' );
			// ASTPPCOM-1132 Kinjal Start
			if($accountinfo['type'] != 1){
				$data['dashboard_flag']=true;
			}
			// ASTPPCOM-1132 Kinjal END
			$gmtoffset = $this->common->get_timezone_offset ();
			$data['currency']= $this->common->get_field_name("currency","currency",array("id"=>$accountinfo['currency_id']));
			$this->load->view ( 'view_dashboard', $data );
		}
	}
	function user_recent_payments() {
		$this->customerReport_recent_payments ();
	}
	function customerReport_recent_payments() {
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$currency = $this->common->get_field_name ( 'currency', 'currency', array (
				"id" => $accountinfo ['currency_id'] 
		) );
		$json_data = array ();
		$i = 1;
		$result = $this->dashboard_model->get_recent_recharge ();
		$gmtoffset = $this->common->get_timezone_offset ();
		if ($result->num_rows () > 0) {
			$account_arr = $this->common->get_array ( 'id,number,first_name,last_name', 'accounts', '' );
			$json_data [0] ['accountid'] = 'Accounts';
			$json_data [0] ['credit'] = 'Amount(' . $currency . ")";
			$json_data [0] ['payment_date'] = 'Date';
			foreach ( $result->result_array () as $key => $data ) {
				$current_timestamp = strtotime ( $data ['payment_date'] );
				$modified_date = $current_timestamp + $gmtoffset;
				$data ['accountid'] = ($data ['accountid'] != '' && isset ( $account_arr [$data ['accountid']] )) ? $account_arr [$data ['accountid']] : "Anonymous";
				$json_data [$i] ['accountid'] = $data ['accountid'];
				$json_data [$i] ['credit'] = $this->common_model->calculate_currency ( $data ['credit'], '', '', true, false );
				$json_data [$i] ['payment_date'] = date ( 'Y-m-d H:i:s', strtotime ( $data ['payment_date'] ) + $gmtoffset );
				$i ++;
			}
		}
		echo json_encode ( $json_data );
	}
	function user_call_statistics_with_profit() {
		$this->customerReport_call_statistics_with_profit ();
	}
	function customerReport_call_statistics_with_profit() {
		$post=$this->input->post();
		$year=isset($post['year']) && $post['year'] >0 ? $post['year']:date("Y");
		$month=isset($post['month'])&& $post['month'] >0 ? $post['month']:date("m");
		$json_data = array();
		if($post['drop_val'] == "t_week"){
			// ASTPPCOM-891 Ashish start
			$start_date = (date('D')!='Mon') ? date('Y-m-d  H:i:s',strtotime('last Monday')) : date('Y-m-d  H:i:s');
			$end_date = date('Y-m-d H:i:s');
			$start_date_gmt = (date('D')!='Mon') ? date('Y-m-d',strtotime('last Monday')) : date('Y-m-d');
			$end_date_gmt = date('Y-m-d');
			$start_date = $this->common->convert_GMT_new($start_date);
			$end_date = $this->common->convert_GMT_new($end_date);
			// ASTPPCOM-891 Ashish end
		}else{
			$start_date=date($year.'-'.$month.'-01');
			$end_day= $year==date("Y") && $month ==date("m") ? date("d") :cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$gmtoffset=$this->common->get_timezone_offset();
			// ASTPPCOM-891 Ashish start
			$end_date =date($year."-".$month."-".$end_day.' H:i:s');
			$end_date=date('Y-m-d',strtotime($end_date));
			$start_date_gmt = $start_date;
			$end_date_gmt =$end_date;
			$start_date = $this->common->convert_GMT_new($start_date);
			$end_date = $this->common->convert_GMT_new($end_date);
			// ASTPPCOM-891 Ashish end
		}
		$current_date=(int)date("d");
		$count=0;
		$i=0;
		// ASTPPCOM-891 Ashish start
		$begin = new DateTime($start_date_gmt);
		$end = new DateTime($end_date_gmt);
		// ASTPPCOM-891 Ashish end
		$end=$end->modify('+1 day');
		$daterange = new DatePeriod($begin, new DateInterval('P1D'), $end);
		$records_date=array();
		$accountinfo=$this->session->userdata('accountinfo');
		$parent_id= ($accountinfo['type'] == 1) ? $accountinfo['id'] : 0;
		// ASTPPCOM-891 Ashish start
		$customerresult = $this->dashboard_model->get_call_statistics('cdrs_day_by_summary',$parent_id,$start_date_gmt,$end_date_gmt);
		// ASTPPCOM-891 Ashish end
		$acc_arr = array();
		$customer_total_result = array();
		$customer_total_result['sum'] = '0';
		$customer_total_result['answered'] = '0';
		$customer_total_result['mcd'] = '0';
		$customer_total_result['duration'] = '0';
		$customer_total_result['failed'] = '0';
		$customer_total_result['profit'] = '0';
		$customer_total_result['debit'] = '0';
		$customer_total_result['cost'] = '0';
		$customer_total_result['completed'] = '0';
		$customer_total_result['billable'] = '0';
		$mcd = 0;
		$res_mcd = 0;
		if($customerresult -> num_rows > 0){
			foreach ($customerresult->result_array() as $data) {
				// ASTPPCOM-891 Ashish start
				$data['day'] = date('d',strtotime($data['day']));
				// ASTPPCOM-891 Ashish end
				$acc_arr[$data['day']] = $data;
				$customer_total_result['sum'] += $data['sum'];
				$customer_total_result['answered'] += $data['answered'];
				if($data['mcd'] > $mcd){
					$mcd = $data['mcd'];
				}
				$customer_total_result['mcd'] = $mcd;
				$customer_total_result['duration'] += $data['duration'];
				$customer_total_result['billable'] += $data['billable'];
				$customer_total_result['failed'] += $data['failed'];
				$customer_total_result['profit'] += $data['profit'];
				$customer_total_result['debit'] += $data['debit'];
				$customer_total_result['cost'] += $data['cost'];
				$customer_total_result['completed'] += $data['completed'];
			}
		}

		if(!empty($acc_arr)){
			foreach($daterange as $date){
				$json_data['date'][]=$date->format("d");
				$day = (int) $date->format("d");
				if(isset($acc_arr[$day])){
				  $asr= ($acc_arr[$day]['sum'] > 0 ) ? (round(($acc_arr[$day]['completed'] / $acc_arr[$day]['sum']) * 100,2)) : 0;
				  $acd= ($acc_arr[$day]['completed'] > 0 ) ? round($acc_arr[$day]['billable'] / $acc_arr[$day]['completed'],2) : 0; 
				  $json_data['total'][]=  array((string)$acc_arr[$day]['day'],(int) $acc_arr[$day]['sum']);
				  $json_data['answered'][]=  array((string)$acc_arr[$day]['day'],(int) $acc_arr[$day]['answered']);
				  $json_data['failed'][]=  array((string)$acc_arr[$day]['day'],(int) $acc_arr[$day]['failed']);
				  $json_data['profit'][]=  array((string)$acc_arr[$day]['day'],(float)  str_replace(",", "", $this->common_model->calculate_currency($acc_arr[$day]['profit'])));
				  $json_data['acd'][]=array((string)$acc_arr[$day]['day'],(float)$acd);
				  $json_data['mcd'][]=array((string)$acc_arr[$day]['day'],(float)$acc_arr[$day]['mcd']);
				  $json_data['asr'][]=array((string)$acc_arr[$day]['day'],(float)$asr);
				}else{
				  $json_data['total'][]=  array($date->format("d"), 0);
				  $json_data['answered'][]=  array($date->format("d"), 0);
				  $json_data['failed'][]=  array($date->format("d"), 0);
				  $json_data['profit'][]=  array($date->format("d"), 0);
				  $json_data['acd'][]=array($date->format("d"), 0);
				  $json_data['mcd'][]=array($date->format("d"), 0);
				  $json_data['asr'][]=array($date->format("d"),0);
				}
			}
		} else{
			foreach($daterange as $date){
				$json_data['date'][]=$date->format("d");
				$day = (int) $date->format("d");
				$json_data['total'][]=  array($date->format("d"), 0);
				$json_data['answered'][]=  array($date->format("d"), 0);
				$json_data['failed'][]=  array($date->format("d"), 0);
				$json_data['profit'][]=  array($date->format("d"), 0);
				$json_data['acd'][]=array($date->format("d"), 0);
				$json_data['mcd'][]=array($date->format("d"), 0);
				$json_data['asr'][]=array($date->format("d"), 0);
			}
		}
		$json_data['total_count']['sum']=$customer_total_result['sum'];
		$json_data['total_count']['debit']=$this->common_model->to_calculate_currency($customer_total_result['debit'],'','',true,true);
		$json_data['total_count']['cost']=$this->common_model->to_calculate_currency($customer_total_result['cost'],'','',true,true);
		$json_data['total_count']['profit']=$this->common_model->to_calculate_currency($customer_total_result['profit'],'','',true,true);
		$json_data['total_count']['completed']=$customer_total_result['completed'];
		$json_data['total_count']['duration']=$customer_total_result['duration'];
		$json_data['total_count']['billable']=$customer_total_result['billable'];
		if(isset($json_data['total_count']['completed']) && $json_data['total_count']['completed'] == "0"){
			$json_data['total_count']['acd']= "0";
		}else{
			$json_data['total_count']['acd']=$json_data['total_count']['completed'] > 0 ? round($json_data['total_count']['billable']/$json_data['total_count']['completed'],2):0;
		}
		$json_data['total_count']['mcd']=($customer_total_result['mcd'] > 0 ) ? $customer_total_result['mcd'] : 0;
		$json_data['total_count']['asr']=($json_data['total_count']['sum'] > 0 ) ? (round(($json_data['total_count']['completed'] / $json_data['total_count']['sum']) * 100,2)) : 0;
		$json_data['total_count']['asr']=$this->common_model->format_currency($json_data['total_count']['asr']);
		echo json_encode($json_data);
	}
	function user_maximum_callminutes() {
		$this->customerReport_maximum_callminutes ();
	}
	function customerReport_maximum_callminutes() {
		
		$post = $this->input->post ();
		$year = isset ( $post ['year'] ) && $post ['year'] > 0 ? $post ['year'] : date ( "Y" );
		$month = isset ( $post ['month'] ) && $post ['month'] > 0 ? $post ['month'] : date ( "m" );
		
		if($post['drop_val'] == "t_week"){
			$start_date = $staticstart = (date('D')!='Mon') ? date('Y-m-d',strtotime('last Monday')) : date('Y-m-d');
			$end_date = date('Y-m-d');
		}else{
			$start_date = date ( $year . '-' . $month . '-01' );
			$end_day = $year == date ( "Y" ) && $month == date ( "m" ) ? date ( "d" ) : cal_days_in_month ( CAL_GREGORIAN, $month, $year );
			$gmtoffset = $this->common->get_timezone_offset ();
			$end_date = date ( $year . "-" . $month . "-" . $end_day . ' H:i:s' );
			$end_date = date ( 'Y-m-d', strtotime ( $end_date ) + $gmtoffset );
		}
		
		
		$json_data = array ();
		$result = $this->dashboard_model->get_customer_maximum_callminutes ( $start_date, $end_date );
		$i = 0;
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$reseller_id = ($accountinfo ['type'] == - 1 or $accountinfo ['type'] == 2) ? 0 : $accountinfo ['id'];
		if ($this->session->userdata ( 'userlevel_logintype' ) != 0 && $this->session->userdata ( 'userlevel_logintype' ) != 3) {
			$account_arr = $this->common->get_array ( 'id,number,first_name,last_name', 'accounts', array (
					'reseller_id' => $reseller_id 
			) );
		} else {
			$account_arr = $this->common->get_array ( 'id,number,first_name,last_name', 'accounts', array (
					'id' => $reseller_id 
			) );
		}
		if ($result->num_rows () > 0) {
			foreach ( $result->result_array () as $data ) {
				$data ['accountid'] = ($data ['account_id'] != '' && isset ( $account_arr [$data ['account_id']] )) ? $account_arr [$data ['account_id']] : "Anonymous";
				$json_data [$i] [] = $data ['accountid'];
				$json_data [$i] [] = round ( $data ['billseconds'] / 60, 0 );
				$i ++;
			} 
		} else {
			$json_data [] = array ();
		}
		echo json_encode ( $json_data );
	}
	function user_maximum_callcount() {
		$this->customerReport_maximum_callcount ();
	}
	function customerReport_maximum_callcount() {
		$post = $this->input->post ();
		$year = isset ( $post ['year'] ) && $post ['year'] > 0 ? $post ['year'] : date ( "Y" );
		$month = isset ( $post ['month'] ) && $post ['month'] > 0 ? $post ['month'] : date ( "m" );
		
		if($post['drop_val'] == "t_week"){
			$start_date = $staticstart = (date('D')!='Mon') ? date('Y-m-d',strtotime('last Monday')) : date('Y-m-d');
			$end_date = date('Y-m-d');
		}else{
			$start_date = date ( $year . '-' . $month . '-01' );
			$end_day = $year == date ( "Y" ) && $month == date ( "m" ) ? date ( "d" ) : cal_days_in_month ( CAL_GREGORIAN, $month, $year );
			$gmtoffset = $this->common->get_timezone_offset ();
			$end_date = date ( $year . "-" . $month . "-" . $end_day . ' H:i:s' );
			$end_date = date ( 'Y-m-d', strtotime ( $end_date ) + $gmtoffset );
		}
		$json_data = array ();
		$result = $this->dashboard_model->get_customer_maximum_callcount ( $start_date, $end_date );
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$reseller_id = ($accountinfo ['type'] == - 1 or $accountinfo ['type'] == 2) ? 0 : $accountinfo ['id'];
		if ($this->session->userdata ( 'userlevel_logintype' ) != 0 && $this->session->userdata ( 'userlevel_logintype' ) != 3) {
			$account_arr = $this->common->get_array ( 'id,number,first_name,last_name', 'accounts', array (
					'reseller_id' => $reseller_id 
			) );
		} else {
			$account_arr = $this->common->get_array ( 'id,number,first_name,last_name', 'accounts', array (
					'id' => $reseller_id 
			) );
		}
		$i = 0;
		if ($result->num_rows () > 0) {
			foreach ( $result->result_array () as $data ) {
				$data ['accountid'] = ($data ['account_id'] != '' && isset ( $account_arr [$data ['account_id']] )) ? $account_arr [$data ['account_id']] : "Anonymous";
				$json_data [$i] [] = $data ['accountid'];
				$json_data [$i] [] = ( int ) $data ['call_count'];
				$i ++;
			}
		} else {
			$json_data [] = array ();
		}
		echo json_encode ( $json_data );
	}
	
	function customerReport_maximum_countrycount() {
		$post = $this->input->post ();
		$year = isset ( $post ['year'] ) && $post ['year'] > 0 ? $post ['year'] : date ( "Y" );
		$month = isset ( $post ['month'] ) && $post ['month'] > 0 ? $post ['month'] : date ( "m" );
		
		if($post['drop_val'] == "t_week"){
			$start_date = $staticstart = (date('D')!='Mon') ? date('Y-m-d',strtotime('last Monday')) : date('Y-m-d');
			$end_date = date('Y-m-d');
		}else{
			$start_date = date ( $year . '-' . $month . '-01' );
			$end_day = $year == date ( "Y" ) && $month == date ( "m" ) ? date ( "d" ) : cal_days_in_month ( CAL_GREGORIAN, $month, $year );
			$gmtoffset = $this->common->get_timezone_offset ();
			$end_date = date ( $year . "-" . $month . "-" . $end_day . ' H:i:s' );
			$end_date = date ( 'Y-m-d', strtotime ( $end_date ) + $gmtoffset );
		}
		$json_data = array ();
		$result = $this->dashboard_model->get_customer_maximum_countrycount ( $start_date, $end_date );
		$country_arr = $this->common->get_array ( 'id,country', 'countrycode', "" );
		$i = 0;
		if ($result->num_rows () > 0) {
			foreach ( $result->result_array () as $data ) {
				$data ['country_id'] = ($data ['country_id'] != '' && isset ( $country_arr [$data ['country_id']] )) ? $country_arr [$data ['country_id']] : "Anonymous";
				$json_data [$i] [] = $data ['country_id'];
				$json_data [$i] [] = ( int ) $data ['call_count'];
				$i ++;
			}
		} else {
			$json_data [] = array ();
		}
		echo json_encode ( $json_data );
	}
	
	
	function customerReport_maximum_countryminutes() {
		
		$post = $this->input->post ();
		$year = isset ( $post ['year'] ) && $post ['year'] > 0 ? $post ['year'] : date ( "Y" );
		$month = isset ( $post ['month'] ) && $post ['month'] > 0 ? $post ['month'] : date ( "m" );
		
		if($post['drop_val'] == "t_week"){
			$start_date = $staticstart = (date('D')!='Mon') ? date('Y-m-d',strtotime('last Monday')) : date('Y-m-d');
			$end_date = date('Y-m-d');
		}else{
			$start_date = date ( $year . '-' . $month . '-01' );
			$end_day = $year == date ( "Y" ) && $month == date ( "m" ) ? date ( "d" ) : cal_days_in_month ( CAL_GREGORIAN, $month, $year );
			$gmtoffset = $this->common->get_timezone_offset ();
			$end_date = date ( $year . "-" . $month . "-" . $end_day . ' H:i:s' );
			$end_date = date ( 'Y-m-d', strtotime ( $end_date ) + $gmtoffset );
		}
		
		$json_data = array ();
		$result = $this->dashboard_model->get_customer_maximum_countryminutes ( $start_date, $end_date );
		$i = 0;
		$country_arr = $this->common->get_array ( 'id,country', 'countrycode', "" );
		if ($result->num_rows () > 0) {
			foreach ( $result->result_array () as $data ) {		
				$data ['country_id'] = ($data ['country_id'] != '' && isset ( $country_arr [$data ['country_id']] )) ? $country_arr [$data ['country_id']] : "Anonymous";
				$json_data [$i] [] = $data ['country_id'];
				$json_data [$i] [] = round ( $data ['billseconds'] / 60, 0 );
				$i ++;
			} 
		} else {
			$json_data [] = array ();
		}
		echo json_encode ( $json_data );
	}
	
	
	function customerReport_calculation(){
		// ASTPPCOM-891 Ashish start
		$current_date = $this->common->get_current_login_type_timezone();
		$today_start_date = date($current_date." 00:00:00");
		$today_end_date = date($current_date." 23:59:59");
		$today_start_date=$this->common->convert_GMT_new ($today_start_date);
		$today_end_date=$this->common->convert_GMT_new ($today_end_date);
		// ASTPPCOM-891 Ashish End
		$start_date = date('Y-m-01 00:00:00');
		$end_date = date('Y-m-d H:i:s');
		// ASTPPCOM-891 Ashish start
		$end_date=$this->common->convert_GMT_new ($end_date);
		// ASTPPCOM-891 Ashish End
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		if($accountinfo['type'] == '1'){
			$reseller_id = $accountinfo['id'];
		}else{
			$reseller_id = "0";
		}
		
		$today_query = 'select SUM(total_calls) as total_calls, SUM(debit) as total_debit, SUM(cost) as total_cost, (SUM(debit-cost))as profit, MAX(mcd)as mcd, IFNULL(ROUND(100.0 * SUM(total_answered_call)/SUM(total_calls),2),0) AS ASR,(SUM(billseconds) / SUM(total_answered_call)) as ACD from cdrs_day_by_summary where reseller_id="'.$reseller_id.'" and calldate <= "'.$today_end_date.'" and calldate >= "'.$today_start_date.'"';
		$result = $this->db->query($today_query);
		$today_result = (array) $result->first_row();
		
		if($today_result['mcd'] == ""){$today_result['mcd'] = "0";}
		if($today_result['total_calls'] == ""){$today_result['total_calls'] = "0";}
		if($today_result['ACD'] == ""){$today_result['ACD'] = "0";}else{$today_result['ACD'] =round($today_result['ACD']);}
		if($today_result['total_debit'] != ""){
			$today_result['total_debit'] = $this->common_model->calculate_currency($today_result['total_debit']);
		}else{
			$today_result['total_debit'] = $this->common_model->calculate_currency(0);
		}
		if($today_result['total_cost'] != ""){
			$today_result['total_cost'] =$this->common_model->calculate_currency($today_result['total_cost']);
		}else{
			$today_result['total_cost'] =$this->common_model->calculate_currency(0);;
		}
		if($today_result['profit'] != ""){
			$today_result['profit'] = $this->common_model->calculate_currency($today_result['profit']);
		}else{
			$today_result['profit'] = $this->common_model->calculate_currency(0);
		}
		
		$this_month_query = 'select SUM(total_calls) as total_calls_month, SUM(debit) as total_debit_month, SUM(cost) as total_cost_month, (SUM(debit-cost))as profit_month, MAX(mcd)as mcd_month, IFNULL(ROUND(100.0 * SUM(total_answered_call)/SUM(total_calls),2),0) AS ASR_month,(SUM(billseconds) / SUM(total_answered_call)) as ACD_month from cdrs_day_by_summary where reseller_id="'.$reseller_id.'" and calldate <= "'.$end_date.'" and calldate >= "'.$start_date.'"';
		$month_result = $this->db->query($this_month_query);
		$this_month_result = (array) $month_result->first_row();
		$result_array = array_merge($today_result,$this_month_result);
		
		if($result_array['mcd_month'] == ""){$result_array['mcd_month'] = "0";}
		if($result_array['total_calls_month'] == ""){$result_array['total_calls_month'] = "0";}
		if($result_array['ACD_month'] == ""){$result_array['ACD_month'] = "0";}else{$result_array['ACD_month'] =round($result_array['ACD_month']);}
		
		if($result_array['total_debit_month'] != ""){
			$result_array['total_debit_month'] = $this->common_model->calculate_currency($result_array['total_debit_month']);
		}else{
			$result_array['total_debit_month'] = $this->common_model->calculate_currency(0);
		}
	
		if($result_array['total_cost_month'] != ""){
			$result_array['total_cost_month'] = $this->common_model->calculate_currency($result_array['total_cost_month']);
		}else{
			$result_array['total_cost_month'] = $this->common_model->calculate_currency(0);
		}
		
		if($result_array['profit_month'] != ""){
			$result_array['profit_month'] = $this->common_model->calculate_currency($result_array['profit_month']);
		}else{
			$result_array['profit_month'] = $this->common_model->calculate_currency(0);
		}
		
		echo json_encode($result_array);
		
	}
	
	function account_count(){
		$post = $this->input->post ();
		$year = isset ( $post ['year'] ) && $post ['year'] > 0 ? $post ['year'] : date ( "Y" );
		$month = isset ( $post ['month'] ) && $post ['month'] > 0 ? $post ['month'] : date ( "m" );
		
		if($post['drop_val'] == "t_week"){
			$start_date = $staticstart = (date('D')!='Mon') ? date('Y-m-d 00:00:00',strtotime('last Monday')) : date('Y-m-d 00:00:00');
			$end_date = date('Y-m-d 23:59:59');
		}else{
			$start_date = date ( $year . '-' . $month . '-01 00:00:00' );
			$end_day = $year == date ( "Y" ) && $month == date ( "m" ) ? date ( "d" ) : cal_days_in_month ( CAL_GREGORIAN, $month, $year );
			$gmtoffset = $this->common->get_timezone_offset ();
			$end_date = date ( $year . "-" . $month . "-" . $end_day . ' H:i:s' );
			$end_date = date ( 'Y-m-d H:i:s', strtotime ( $end_date ) + $gmtoffset );
		}
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		if($accountinfo['type'] == '1'){
			$reseller_id = $accountinfo['id'];
		}else{
			$reseller_id = "0";
		}
		$query = 'Select count(*) as count from accounts where creation <= "'.$end_date.'" and creation >= "'.$start_date.'" and reseller_id="'.$reseller_id.'"';
		$result = $this->db->query($query);
		$count = (array) $result->first_row();
		if($count['count'] == "" OR $count['count'] == NULL){
			$count['count'] =0;
		}
		echo json_encode($count);
	}
	
	function call_count(){
		$post = $this->input->post ();
		$year = isset ( $post ['year'] ) && $post ['year'] > 0 ? $post ['year'] : date ( "Y" );
		$month = isset ( $post ['month'] ) && $post ['month'] > 0 ? $post ['month'] : date ( "m" );
		
		if($post['drop_val'] == "t_week"){
			$start_date = $staticstart = (date('D')!='Mon') ? date('Y-m-d 00:00:00',strtotime('last Monday')) : date('Y-m-d 00:00:00');
			$end_date = date('Y-m-d 23:59:59');
		}else{
			$start_date = date ( $year . '-' . $month . '-01 00:00:00' );
			$end_day = $year == date ( "Y" ) && $month == date ( "m" ) ? date ( "d" ) : cal_days_in_month ( CAL_GREGORIAN, $month, $year );
			$gmtoffset = $this->common->get_timezone_offset ();
			$end_date = date ( $year . "-" . $month . "-" . $end_day . ' H:i:s' );
			$end_date = date ( 'Y-m-d H:i:s', strtotime ( $end_date ) + $gmtoffset );
		}
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		if($accountinfo['type'] == '1'){
			$reseller_id = $accountinfo['id'];
		}else{
			$reseller_id = "0";
		}
		$query = 'Select SUM(total_calls) as total_calls from cdrs_day_by_summary where calldate <= "'.$end_date.'" and calldate >= "'.$start_date.'" and reseller_id="'.$reseller_id.'"';
		$result = $this->db->query($query);
		$count = (array) $result->first_row();
		if($count['total_calls'] == "" OR $count['total_calls'] == NULL){
			$count['total_calls'] =0;
		}
		echo json_encode($count);
	}
	
	function orders_count(){
		$post = $this->input->post ();
		$year = isset ( $post ['year'] ) && $post ['year'] > 0 ? $post ['year'] : date ( "Y" );
		$month = isset ( $post ['month'] ) && $post ['month'] > 0 ? $post ['month'] : date ( "m" );
		
		if($post['drop_val'] == "t_week"){
			$start_date = $staticstart = (date('D')!='Mon') ? date('Y-m-d 00:00:00',strtotime('last Monday')) : date('Y-m-d 00:00:00');
			$end_date = date('Y-m-d 23:59:59');
		}else{
			$start_date = date ( $year . '-' . $month . '-01 00:00:00' );
			$end_day = $year == date ( "Y" ) && $month == date ( "m" ) ? date ( "d" ) : cal_days_in_month ( CAL_GREGORIAN, $month, $year );
			$gmtoffset = $this->common->get_timezone_offset ();
			$end_date = date ( $year . "-" . $month . "-" . $end_day . ' H:i:s' );
			$end_date = date ( 'Y-m-d H:i:s', strtotime ( $end_date ) + $gmtoffset );
		}
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		if($accountinfo['type'] == '1'){
			$reseller_id = $accountinfo['id'];
		}else{
			$reseller_id = "0";
		}
		$query = 'Select count(*) as count from orders where order_date <= "'.$end_date.'" and order_date >= "'.$start_date.'" and reseller_id="'.$reseller_id.'"';
		$result = $this->db->query($query);
		$count = (array) $result->first_row();
		if($count['count'] == "" OR $count['count'] == NULL){
			$count['count'] =0;
		}
		echo json_encode($count);
	}
	function getrefill_value(){
		$post = $this->input->post ();
		$year = isset ( $post ['year'] ) && $post ['year'] > 0 ? $post ['year'] : date ( "Y" );
		$month = isset ( $post ['month'] ) && $post ['month'] > 0 ? $post ['month'] : date ( "m" );
		
		if($post['drop_val'] == "t_week"){
			$start_date = $staticstart = (date('D')!='Mon') ? date('Y-m-d 00:00:00',strtotime('last Monday')) : date('Y-m-d 00:00:00');
			$end_date = date('Y-m-d 23:59:59');
		}else{
			$start_date = date ( $year . '-' . $month . '-01 00:00:00' );
			$end_day = $year == date ( "Y" ) && $month == date ( "m" ) ? date ( "d" ) : cal_days_in_month ( CAL_GREGORIAN, $month, $year );
			$gmtoffset = $this->common->get_timezone_offset ();
			$end_date = date ( $year . "-" . $month . "-" . $end_day . ' H:i:s' );
			$end_date = date ( 'Y-m-d H:i:s', strtotime ( $end_date ) + $gmtoffset );
		}
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		if($accountinfo['type'] == '1'){
			$reseller_id = $accountinfo['id'];
		}else{
			$reseller_id = "0";
		}
		$query = 'Select sum(amount) as total_refill_amount from payment_transaction where date <= "'.$end_date.'" and date >= "'.$start_date.'" and reseller_id="'.$reseller_id.'"';
		$result = $this->db->query($query);
		$result_array = (array) $result->first_row();
		if($result_array['total_refill_amount'] == "" OR $result_array['total_refill_amount'] == NULL){
			$result_refill['total_refill_amount'] = $this->common_model->calculate_currency_customer(0);
		}else{
			$result_refill['total_refill_amount'] = $this->common_model->calculate_currency_customer($result_array['total_refill_amount'] );
		}
		echo json_encode($result_refill);
	}
	
	
	function get_today_result(){
			$accountinfo = $this->session->userdata ( 'accountinfo' );
			// ASTPPCOM-891 Ashish start
			$start_date = $this->common->convert_GMT_new (date("Y-m-d") . " 00:00:00");
			$end_date = $this->common->convert_GMT_new (date("Y-m-d") . " 23:59:59");
			// ASTPPCOM-891 Ashish end
			if($accountinfo['type'] == '1'){
				$reseller_id = $accountinfo['id'];
			}else{
				$reseller_id = "0";
			}
			// ASTPPCOM-891 Ashish start
			$query_refill = 'Select sum(amount) as today_refill_amount from payment_transaction where date >= "'.$start_date.'" and date <= "'.$end_date.'" and reseller_id="'.$reseller_id.'"';
			// ASTPPCOM-891 Ashish end
			$result_refill = $this->db->query($query_refill);
			$result_refill = (array) $result_refill->first_row();
			if($result_refill['today_refill_amount'] == "" OR $result_refill['today_refill_amount'] == NULL){
				$result_array['today_refill_amount'] =$this->common_model->calculate_currency(0);
			}else{
				$result_array['today_refill_amount'] = $this->common_model->calculate_currency( $result_refill['today_refill_amount'] ) ;
			}
			// ASTPPCOM-891 Ashish start
			$query_order = 'Select count(*) as order_count from orders where order_date <= "'.$end_date.'" and order_date >= "'.$start_date.'" and reseller_id="'.$reseller_id.'"';
			// ASTPPCOM-891 Ashish end
			$result_order = $this->db->query($query_order);
			$count = (array) $result_order->first_row();
			if($count['order_count'] == "" OR $count['order_count'] == NULL){
				$result_array['today_order_count'] = 0;
			}else{
				$result_array['today_order_count'] = $count['order_count'];
			}
			// ASTPPCOM-891 Ashish start
			$query = 'Select count(*) as account_count from accounts where creation <= "'.$end_date.'" and creation >= "'.$start_date.'" and reseller_id="'.$reseller_id.'" and status="0" and deleted="0"';
			// ASTPPCOM-891 Ashish end
			$result = $this->db->query($query);
			$count = (array) $result->first_row();
			if($count['account_count'] == "" OR $count['account_count'] == NULL){
				$result_array['today_account_count'] = 0;
			}else{
				$result_array['today_account_count'] = $count['account_count'];
			}
			// ASTPPCOM-891 Ashish start
			$query = 'Select SUM(total_calls) as total_calls from cdrs_day_by_summary where calldate <= "'.$end_date.'" and calldate >= "'.$start_date.'" and reseller_id="'.$reseller_id.'"';
			// ASTPPCOM-891 Ashish end
			$result = $this->db->query($query);
			$count = (array) $result->first_row();
			if($count['total_calls'] == "" OR $count['total_calls'] == NULL){
				$result_array['today_total_calls'] = 0;
			}else{
				$result_array['today_total_calls'] = $count['total_calls'];
			}	
			echo json_encode($result_array);
	}

	// ASTPPCOM-939 Start
	function send_notification(){
		if($_POST['accountid'] != ""){
			$accountinfo = $this->db_model->getSelect("*",'accounts',array("id" => $_POST['accountid'],"status" => 0,"deleted" => 0))->result_array();	
			if($accountinfo != ""){
				$this->common->mail_to_users('low_balance',$accountinfo[0]);
			}
		}
	}
	// END
}

?>
