<?php

###############################################################################
# ASTPP - Open Source VoIP Billing Solution
#
# Copyright (C) 2016 Inextrix Technologies Pvt. Ltd.
# Samir Doshi <samir.doshi@inextrix.com>
# ASTPP Version 3.0 and above
# License https://www.gnu.org/licenses/agpl-3.0.html
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
# 
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
###############################################################################
#[\AllowDynamicProperties]
class AutomatedReport extends MX_Controller {
    public static $global_config;
    public $account_arr = array();
    public $currentdatetime = '';
	public $currentdate = '';
	public $Error_flag = false;
    public $fp = "";
    
    function __construct() {
        parent::__construct();
		$this->load->model("db_model");
		$this->load->library("astpp/common");
		$this->load->model("common_model");
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "259200");
        $this->load->library('zip');
		$this->load->library('fpdf');
        $this->load->library('pdf');
		$this->load->library('astpp/permission');
		
		$this->currentdatetime = gmdate("Y-m-d H:i:s");
		$this->date = gmdate('Y-m-d');
		$this->log_path = Common_model::$global_config ['system_config'] ['log_path'];
		$this->fp = fopen($this->log_path."astpp_automated_report_".$this->date.".log", "a+");
		$this->currentdate = astpp_date("Y-m-d",strtotime($this->currentdatetime));
		$this->$Error_flag = true;
    }
    function Automated_Report() {
		set_time_limit(0);
		$current_date = $this->currentdatetime;
		$query = $this->db_model->getSelect("*", "automated_reports", array('status' => 0));
		$this->PrintLogger($this->Error_flag,"::::: Autmated Report Process Start ::::: \n");
		$this->PrintLogger($this->Error_flag,"::::: Autmated Report Query ::::: \n\n");
		$this->PrintLogger($this->Error_flag, $this->db->last_query()."\n\n");
		$admin_info =  $this->db_model->getSelect('timezone_id,currency_id','accounts',array('id' => 1))->row_array();
		$admin_info['currency_name'] = $this->common->get_field_name('currency','currency',array('id' => $admin_info['currency_id']));
		$this->PrintLogger($this->Error_flag,"::::: Admin Info ::::: \n\n");
		$this->PrintLogger($this->Error_flag, print_r($admin_info,true)."\n\n");
        if ($query->num_rows() > 0) {
            $automated_report_data = $query->result_array();
			$this->PrintLogger($this->Error_flag, "::::::: Automated Report Data :::::::: \n\n");
			$this->PrintLogger($this->Error_flag, print_r($automated_report_data,true)."\n\n");
			$i=0;
            foreach ($automated_report_data as $data_key => $automated_report_value) {
				$this->PrintLogger($this->Error_flag, " ::::: Automated Report Name ::::::\n\n");
				$this->PrintLogger($this->Error_flag, print_r($automated_report_value['report_name'],true)."\n\n");
				$automated_report_value['filters_where'] = json_decode($automated_report_value['filters_where'],true);
				$automated_report_value['interval_frequency_on'] = (int)$automated_report_value['interval_frequency_on'];
				foreach ($automated_report_value['filters_where'] as $automated_report_key => $json_data_value) {
					$date =  astpp_eq0($automated_report_value['update_flag']) ? 'creation_date' :  'next_execution_date';
					if($date == 'creation_date'){
						$automated_report_value[$date] = astpp_date("Y-m-d ", strtotime($automated_report_value['creation_date']));
					}
					$this->PrintLogger($this->Error_flag, " ::::: Automated Report Date ::::::\n\n");
					$this->PrintLogger($this->Error_flag, print_r($automated_report_value[$date],true)."\n\n");
					$explode_date  = explode(" ",$automated_report_value[$date]); 
					
					$automated_report_value['execute_on'] = astpp_date("Y-m-d ", strtotime($explode_date[0]. "-". $automated_report_value['report_interval_days'].'days'));
					$this->PrintLogger($this->Error_flag,"::: Automated Report Execute On ::: \n\n");
					$this->PrintLogger($this->Error_flag, print_r($automated_report_value['execute_on'],true)."\n\n");
					$select_where = $json_data_value['select_where'];
					foreach ($select_where as $select_where_key => $select_where_value) {
						$where[$select_where_key] = $select_where_value;
						if (strpos($select_where_value, '_') !== false && !is_array($select_where_value)) {
							$explode_key =  explode('_',$select_where_value);
							// 1 = String, 2 = Integer
							if($explode_key[1] == 1 ){
								if($explode_key[2] == 1){
									$where[$select_where_key." LIKE "] = "%".$explode_key[0]."%";
								}else if($explode_key[2] == 2){
									$where[$select_where_key." NOT LIKE"] = "%".$explode_key[0]."%";
								}elseif($explode_key[2] == 3){
									$where[$select_where_key."  = "] = $explode_key[0];
								}elseif($explode_key[2] == 4){
									$where[$select_where_key." !="] = $explode_key[0];
								}elseif($explode_key[2] == 5){
									$where[$select_where_key." LIKE"] = $explode_key[0]."%";
								}elseif($explode_key[2] == 6){
									$where[$select_where_key." LIKE"] = "%".$explode_key[0];
								}
							}else{
								if($explode_key[2] == 1){
									$where[$select_where_key." ="] = $explode_key[0];
								}else if($explode_key[2] == 2){
									$where[$select_where_key." != "] = $explode_key[0];
								}elseif($explode_key[2] == 3){
									$where[$select_where_key. " >" ] = $explode_key[0];
								}elseif($explode_key[2] == 4){
									$where[$select_where_key." <"] = $explode_key[0];
								}elseif($explode_key[2] == 5){
									$where[$select_where_key." >="] = $explode_key[0];
								}elseif($explode_key[2] == 6){
									$where[$select_where_key." <= "] = $explode_key[0];
								}
							}
							unset($where[$select_where_key]);
						}
						if(is_array($where[$select_where_key])){
							$where[$select_where_key] = '('.$where[$select_where_key][$i].')';
						}
						$where["callstart >="] =  $automated_report_value['execute_on'].'00:00:00';
						$where["callstart <="] = $automated_report_value[$date].' 23:59:59';
					}
				}
				$this->PrintLogger($this->Error_flag,"::: Where Condition ::: \n\n");
				$this->PrintLogger($this->Error_flag, print_r($where,true)."\n\n");
				unset($where['ajax_search'],$where['array_params']);
				$automated_array['where'] = $where;
				// Kinjal issue no 4774
				unset($where);
				// END
				$automated_array['table_name'] = $table_name;
				$automated_array['json_data_value'] = $json_data_value;
				$automated_array['array_params'] = $select_where['array_params'];
				$automated_array['currency_name'] = $admin_info['currency_name'];
				if($this->currentdate == $automated_report_value['next_execution_date']){
					$this->process_sql_query_for_report($automated_array,$automated_report_value);
					$execution_date = $this->common->get_automatedreport_date($automated_report_value);
					$this->db->where('id', $automated_report_value['id']);
					$this->db->update('automated_reports', array('next_execution_date' => $execution_date,'update_flag' => 1));
					$i++;
				}
				$this->PrintLogger($this->Error_flag," ::::: Loop Iteration Value ::::\n\n");
				$this->PrintLogger($this->Error_flag, $i."\n\n");
			}
        }
    }

	function process_sql_query_for_report($automated_array,$automated_report_value){
		$this->PrintLogger($this->Error_flag," ::::: It's a ZIP ::::\n\n");
		$attachment_availalble = $this->automated_report_export_zip($automated_array,$automated_report_value);
		if($attachment_availalble !='')
		{
			$file = getcwd().'/attachments/'.$attachment_availalble;
			$filesize = filesize($file); // bytes
			$filesize = astpp_round(astpp_n($filesize) / 1024 / 1024, 1); // megabytes with 1 digit
			$this->largefile_mail_to_users($final_array,$attachment_availalble,$automated_report_value,$file);
		}
	}
	
	function largefile_mail_to_users($final_array,$attachment_availalble,$accountDetails,$file){
		// Kinjal issue no 5003
		if(common_model::$global_config['system_config']['automated_report_attachment_deleted'] !="" && common_model::$global_config['system_config']['automated_report_attachment_deleted']!="-1" && is_numeric(common_model::$global_config['system_config']['automated_report_attachment_deleted'])) {
			$purge_date = astpp_date('Y-m-d', strtotime('+'.common_model::$global_config['system_config']['automated_report_attachment_deleted']." days", strtotime($this->date)));
		}
		// END
		$uniq_string = md5(uniqid(rand(), true));
		$filePath =  base_url().'automatedReportDownload/Downloadzip?file='.$uniq_string;
		$attachments_details = array(
        	'filename'       => $attachment_availalble,
            'usercode'       => $uniq_string,
            'creation_date'   => gmdate('Y-m-d H:i:s'),
            'purge_date'   => $purge_date
        );
		$this->db->insert('automated_report_log', $attachments_details);
		$final_array['email']=$accountDetails['account_email'];
		$final_array['attachment']=$attachment_availalble;
		$final_array['subject_title']=$accountDetails['report_name'];
		$final_array['interval_freq_of_email']=astpp_eq0($accountDetails['interval_frequency_on']) ? "Day" : (($accountDetails['interval_frequency_on'] == 1)  ? "Week" : ($accountDetails['interval_frequency_on'] == 2 ? "Month" : "Biweekly")); // PHP8: nested ternary
		$final_array['interval_filter_on']='callstart';
		$final_array['report_interval_days']=$accountDetails['report_interval_days'];
		$final_array['reseller_id']='0';
		$final_array['id']='1';
		$final_array['report_interval_recurring']= 	astpp_eq0($accountDetails['report_interval_recurring']) ? "Day" : (($accountDetails['report_interval_recurring'] == 1)  ? "Week" : "Month");
		$this->common->mail_to_users ( 'automated_report', $final_array,"","",$filePath );
	}
	function automated_report_export_zip($automated_array, $automated_report_value){
		// Kinjal issue no 3452
		unset($automated_array['where']['search_in'],$automated_array['where']['type IN'],$automated_array['json_data_value']['select_where']['new_billseconds'],$automated_array['where']['new_billseconds =']);
		$this->db->escape_str($automated_array['where']);
		$this->db->where($automated_array['where']);
		$query = $this->db->get($automated_array['json_data_value']['select_table']);
		$this->PrintLogger($this->Error_flag,"::::: Row Count Query ::::: \n\n");
		$this->PrintLogger($this->Error_flag, $this->db->last_query()."\n\n");
		$num_rows = $query->num_rows();
		$this->PrintLogger($this->Error_flag,"::::: Row Count ::::: \n\n");
		$this->PrintLogger($this->Error_flag, $num_rows."\n\n");
		$string = 'reseller_id,accountid,resellerid,account_id';
		$csvFile ='';
		$Header = array();
		$full_path = FCPATH.'invoices/';
		$fileArr =array();
		$fileArr[]=$csv_file_path;	
		$array_params_keys = astpp_array_keys($automated_array['array_params']);
		$total_rows=$num_rows;
		$csv_limit=1000000;
		$limit = 50000;
		$zip_array=array();
		$zip_flag=false;
		$zip_limit=astpp_ceil(astpp_div($total_rows, $csv_limit));  
		$this->PrintLogger($this->Error_flag,":::: ZIP Limit :::: \n\n");
		$this->PrintLogger($this->Error_flag, $zip_limit."\n\n");
		$k=0;
		$j = 0;
		$random_number =  $this->common->random_string('2');
		$i = 0;
		for($k = 0 ; $k < $zip_limit; $k++){

			$file_name = 'AutomatedReport_' .date("Y-m-d-h-i-s").'-'.$random_number;
			$fp=null;
			$fp= fopen($full_path .$file_name . '.csv', 'w');
			$Header = explode(',',$automated_report_value['select_names']);
			fputcsv($fp,$Header);
			$zip_array[$k]=$full_path.$file_name;
			$zip_flag=true;
			$inner_limit=(astpp_div($csv_limit, $limit)) * (astpp_n($k)+1);
			if($inner_limit > $total_rows){
				$inner_limit =$total_rows;
			}
			$i=(astpp_div($csv_limit, $limit))*astpp_n($k);
			for ($i; $i < $inner_limit; $i++) {
					if(isset($automated_array['where']) && !empty($automated_array['where'])){
						// Kinjal issue no 3898 
						$this->db->select($automated_report_value['select_values']);
						$this->db->escape_str($automated_array['where']);
						$this->db->where($automated_array['where']);
						$this->db->limit($limit, $j);
						$records = $this->db->get($automated_array['json_data_value']['select_table']);
						// END
						$count = $records->num_rows();
						$automated_report_details = $records->result_array();
						$records->free_result();
						$records=null;
						if($automated_report_details != ""){
							foreach($automated_report_details as $automated_report_key => $automatedValue){
								
								foreach ($automatedValue as $key => $value) {
									if(astpp_in_array($key,$array_params_keys)!= FALSE){
										$explode_key =  explode	(',',$automated_array['array_params'][$key]);
										if($explode_key[0] != ""){
											if(strpos($string, $key) !== false){
												$automatedValue[$key] = (!astpp_eq0($value))? $this->common->build_concat_string('first_name,last_name,number,company_name', 'accounts', $automatedValue[$key]) : "Admin";
											}else{
												$automatedValue[$key] = $this->common->get_field_name($explode_key[1],$explode_key[0],array('id' => $automatedValue[$key]));
											}
										}else{
											$automatedValue[$key] = astpp_eq0($value) ? $explode_key[1] : $explode_key[2];
										}
									}
									// Kinjal issue no 4931
									if($key == 'balance' || $key == 'setup_fee' || $key == 'price' || $key == 'cost' || $key == 'debit' ){
										$automatedValue[$key] =  $this->common_model->calculate_currency_customer($value,'',$automated_array['currency_name']);
									}
									// END

									// Kinjal issue no 4791
									if($key == 'pattern' && strpos($value, '^') !== false){
										$automatedValue[$key] = str_replace(array('^','.*'), "", $value);
									}
									// END
								}
								$finalArr = $automatedValue;
								fputcsv($fp, $finalArr);
								$key=null;
								$value=null;
								$finalArr=null;
								}
								$result=null;
								$automatedValue=null;
								$j = astpp_n($j) + astpp_n($limit);
						}
					}
			}
			fclose($fp);
			if($zip_flag)
			{
				$fileName       = 'AutomatedReport_' .date("Y-m-d-h-i-s").'-'.$random_number;
				$this->PrintLogger($this->Error_flag,":::: ZIP File Name :::: \n\n");
				$this->PrintLogger($this->Error_flag, $fileName."\n\n");
				$zip_file_name = $fileName.".zip";
				foreach($zip_array as $key=>$value){
					$this->zip->read_file($value.".csv");
					unlink($value.".csv");
				}
				$this->zip->archive($full_path.$zip_file_name);
				$this->zip->clear_data();
				$dir_path = getcwd()."/attachments/";
				$FilePath = FCPATH.'invoices/'.$zip_file_name;
				$path = $dir_path.$zip_file_name;
				$command = "cp -Rf ".$FilePath." ".$path;
				exec($command);	
				unlink($FilePath);
			}
			return $zip_file_name;
		}
	}
	function PrintLogger($Error_flag,$Message)
    {
		if (!$Error_flag) {
			if (is_array($Message)) {
				foreach ($Message as $MessageKey => $MessageValue) {
					if (is_array($MessageValue)) {
						foreach ($MessageValue as $LogKey => $LogValue) {
							fwrite($this->fp, "::::: " . $LogKey . " ::::: " . $LogValue . " :::::\n");
						}
					} else {
						fwrite($this->fp, "::::: " . $MessageKey . " ::::: " . $MessageValue . " :::::\n");
					}
				}
			} else {
					fwrite($this->fp, "::::: " . $Message . " :::::\n");
			}
		}
    }
}
?>

