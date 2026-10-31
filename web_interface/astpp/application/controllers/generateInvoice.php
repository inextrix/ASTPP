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
class GenerateInvoice extends MX_Controller {
	public static $global_config;
	function __construct() {
		parent::__construct ();
		// Cron controller: only reachable from this server (CLI, or wget from cron_settings)
		if (! astpp_internal_request ()) {
			show_404 ();
		}
		$this->load->model ( "db_model" );
		$this->load->library ( "astpp/common" );
		$this->load->library ( 'html2pdf' );
		ini_set ( "memory_limit", "2048M" );
		ini_set ( "max_execution_time", "259200" );
		$this->get_system_config ();
	}
	function get_system_config() {
		$query = $this->db->get ( "system" );
		$config = array ();
		$result = $query->result_array ();
		foreach ( $result as $row ) {
			$config [$row ['name']] = $row ['value'];
		}
		self::$global_config ['system_config'] = $config;
	}
	function getInvoiceData() {
		$where = array (
				"posttoexternal" => 1,
				"deleted" => "0",
				"status" => "0" 
		);
		$query = $this->db_model->getSelect ( "*", "accounts", $where );
		if ($query->num_rows () > 0) {
			$account_data = $query->result_array ();
			foreach ( $account_data as $data_key => $account_value ) {
				$end_date = gmdate ( "Y-m-d" ) . " 23:59:59";
				$account_value ['sweep_id'] = ( int ) $account_value ['sweep_id'];
				switch ($account_value ['sweep_id']) {
					case 0 :
						$start_date = $this->validate_invoice_date ( $account_value );
						if (strtotime ( $start_date ) >= strtotime ( gmdate ( "Y-m-d H:i:s" ) )) {
							$start_date = gmdate ( "Y-m-d H:i:s" );
						}
						$end_date = gmdate ( "Y-m-d 23:59:59", strtotime ( $start_date . " + 1 days" ) );
						$this->Generate_Daily_invoice ( $account_value, $start_date, $end_date );
						break;
					case 2 :
						if (date ( "d" ) == $account_value ['invoice_day']) {
							$start_date = $this->validate_invoice_date ( $account_value );
							if (strtotime ( $start_date ) >= strtotime ( gmdate ( "Y-m-d H:i:s" ) )) {
								$start_date = gmdate ( "Y-m-d H:i:s" );
							}
							$end_date = gmdate ( "Y-m-d 23:59:59", strtotime ( $start_date . " + 1 month" ) );
							$this->Generate_Monthly_invoice ( $account_value, $start_date, $end_date );
						}
						break;
				}
			}
			astpp_cron_background('BroadcastEmail');
		}
	}
	function validate_invoice_date($account_value) {
		$last_invoice_date = $this->common->get_invoice_date ( "to_date", $account_value ["id"], $account_value ['reseller_id'], "to_date" );
		$last_invoice_date = ($last_invoice_date) ? $last_invoice_date : $account_value ['creation'];
		$last_invoice_date = gmdate ( "Y-m-d H:i:s", strtotime ( "+1 Second", strtotime ( $last_invoice_date ) ) );
		return $last_invoice_date;
	}
	function Generate_Daily_invoice($account_value, $start_date, $end_date) {
		require_once ('updateBalance.php');
		$updateBalance = new updateBalance ();
		$updateBalance->process_subscriptions ( $account_value, $start_date, $end_date, TRUE );
		$updateBalance->process_DID_charges ( $account_value, $start_date, $end_date, TRUE );
		$this->process_invoice ( $account_value, $start_date, $end_date );
	}
	function Generate_Monthly_invoice($account_value, $start_date, $end_date) {
		require_once ('updateBalance.php');
		$updateBalance = new updateBalance ();
		$updateBalance->process_subscriptions ( $account_value, $start_date, $end_date, TRUE );
		$updateBalance->process_DID_charges ( $account_value, $start_date, $end_date, TRUE );
		$this->process_invoice ( $account_value, $start_date, $end_date );
	}
	function process_invoice($accountdata, $start_date, $end_date) {
		$invoice_conf = array ();
		$reseller_id = ($accountdata ['reseller_id'] == 0) ? 1 : $accountdata ['reseller_id'];
		$where = "accountid IN ('" . $reseller_id . "','1')";
		$this->db->select ( '*' );
		$this->db->where ( $where );
		$this->db->order_by ( 'accountid', 'desc' );
		$this->db->limit ( 1 );
		$invoice_conf = $this->db->get ( 'invoice_conf' );
		$invoice_conf = ( array ) $invoice_conf->first_row ();
		$last_invoice_ID = $this->common->get_invoice_date ( "invoiceid", "", $accountdata ['reseller_id'] );
		if ($last_invoice_ID && $last_invoice_ID > 0) {
			$last_invoice_ID = ($last_invoice_ID + 1);
		} else {
			$last_invoice_ID = $invoice_conf ['invoice_start_from'];
		}
		$last_invoice_ID = str_pad ( $last_invoice_ID, (strlen ( $last_invoice_ID ) + 4), '0', STR_PAD_LEFT );
		$invoice_sub_total = $this->count_invoice_data ( $accountdata, $start_date, $end_date );
		if ($invoice_sub_total > 0) {
			$invoiceid = $this->create_invoice ( $accountdata, $start_date, $end_date, $last_invoice_ID, $invoice_conf ['invoice_prefix'], $invoice_conf );
			$this->update_cdrs_data ( $accountdata ['id'], $invoiceid, $start_date, $end_date );
			$sort_order = $this->common_model->apply_invoice_taxes ( $invoiceid, $accountdata, $start_date );
			$invoice_total = $this->set_invoice_total ( $invoiceid, $accountdata ['id'] );
			$this->download_invoice ( $invoiceid, $accountdata, $invoice_conf );
		} else {
			$invoiceid = $this->create_invoice ( $accountdata, $start_date, $end_date, $last_invoice_ID, $invoice_conf ['invoice_prefix'], $invoice_conf );
			$sort_order = $this->common_model->apply_invoice_taxes ( $invoiceid, $accountdata, $start_date );
			$invoice_total = $this->set_invoice_total ( $invoiceid, $accountdata ['id'] );
		}
	}
	function count_invoice_data($account, $start_date = "", $end_date = "") {
		$cdr_query = "";
		$inv_data_query = "";
		$cdr_query = "select calltype,sum(debit) as debit from cdrs where accountid = " . $account ['id'];
		$cdr_query .= " AND callstart >='" . $start_date . "' AND callstart <= '" . $end_date . "' AND invoiceid=0 group by calltype";
		$cdr_data = $this->db->query ( $cdr_query );
		if ($cdr_data->num_rows () > 0) {
			$cdr_data = $cdr_data->result_array ();
			foreach ( $cdr_data as $cdrvalue ) {
				$cdrvalue ['debit'] = round ( $cdrvalue ['debit'], self::$global_config ['system_config'] ['decimalpoints'] );
				$tempArr = array (
						"accountid" => $account ['id'],
						"reseller_id" => $account ['reseller_id'],
						"item_id" => "0",
						"description" => $cdrvalue ['calltype'] . " CALLS for the period (" . $start_date . " to " . $end_date,
						"debit" => $cdrvalue ['debit'],
						"item_type" => $cdrvalue ['calltype'],
						"created_date" => $end_date 
				);
				$this->db->insert ( "invoice_details", $tempArr );
			}
		}
		$inv_data_query = "select count(id) as count,sum(debit) as debit,sum(credit) as credit from invoice_details where accountid=" . $account ['id'] . " AND created_date >='" . $start_date . "' AND created_date <= '" . $end_date . "'  AND invoiceid=0 AND item_type != 'FREECALL'";
		$invoice_data = $this->db->query ( $inv_data_query );
		if ($invoice_data->num_rows () > 0) {
			$invoice_data = $invoice_data->result_array ();
			foreach ( $invoice_data as $data_value ) {
				if ($data_value ['count'] > 0) {
					$sub_total = ($data_value ['debit'] - $data_value ['credit']);
					$sub_total = round ( $sub_total, self::$global_config ['system_config'] ['decimalpoints'] );
					return $sub_total;
				}
			}
		}
		return "0";
	}
	function update_cdrs_data($accountid, $invoiceid, $start_date = "", $end_date = "") {
		$inv_data_query = "update invoice_details SET invoiceid = '" . $invoiceid . "' where accountid=" . $accountid;
		$inv_data_query .= " AND created_date >='" . $start_date . "' AND created_date <= '" . $end_date . "'  AND invoiceid=0 AND item_type !='PAYMENT'";
		$this->db->query ( $inv_data_query );
		return true;
	}
	function create_invoice($account, $from_date, $to_date, $last_invoice_ID, $INVprefix, $invoiceconf) {
		if ($invoiceconf ['interval'] > 0) {
			$due_date = gmdate ( "Y-m-d H:i:s", strtotime ( gmdate ( "Y-m-d H:i:s" ) . " +" . $invoiceconf ['interval'] . " days" ) );
		} else {
			$due_date = gmdate ( "Y-m-d H:i:s", strtotime ( gmdate ( "Y-m-d H:i:s" ) . " +7 days" ) );
		}
		$balance = ($account ['credit_limit'] - $account ['balance']);
		$automatic_flag = self::$global_config ['system_config'] ['automatic_invoice'];
		if ($automatic_flag == 1) {
			$invoice_data = array (
					"accountid" => $account ['id'],
					"invoice_prefix" => $INVprefix,
					"invoiceid" => $last_invoice_ID,
					"reseller_id" => $account ['reseller_id'],
					"invoice_date" => gmdate ( "Y-m-d H:i:s" ),
					"from_date" => $from_date,
					"to_date" => $to_date,
					"due_date" => $due_date,
					"status" => 1,
					"amount" => "0.00",
					"balance" => $balance 
			);
		} else {
			$invoice_data = array (
					"accountid" => $account ['id'],
					"invoice_prefix" => $INVprefix,
					"invoiceid" => $last_invoice_ID,
					"reseller_id" => $account ['reseller_id'],
					"invoice_date" => gmdate ( "Y-m-d H:i:s" ),
					"from_date" => $from_date,
					"to_date" => $to_date,
					"due_date" => $due_date,
					"status" => 1,
					"amount" => "0.00",
					"balance" => $balance,
					"confirm" => 1 
			);
		}
		$this->db->insert ( "invoices", $invoice_data );
		$invoiceid = $this->db->insert_id ();
		if ($automatic_flag == 0) {
			$this->download_invoice ( $invoiceid, $account, $invoiceconf );
		}
		return $invoiceid;
	}
	function set_invoice_total($invoiceid, $accountid) {
		$query = $this->db_model->getSelect ( "SUM(debit) as total", "invoice_details", array (
				"invoiceid" => $invoiceid,
				"item_type <>" => "FREECALL" 
		) );
		$query = $query->result_array ();
		$sub_total = $query ["0"] ["total"];
		$updateArr = array (
				"amount" => $sub_total 
		);
		$this->db->where ( array (
				"id" => $invoiceid 
		) );
		$this->db->update ( "invoices", $updateArr );
		
		$updateArr = array (
				"balance" => "0.00" 
		);
		$this->db->where ( array (
				"id" => $accountid 
		) );
		$this->db->update ( "accounts", $updateArr );
		
		return true;
	}
	function download_invoice($invoiceid, $accountdata, $invoice_conf) {
		$invoicedata = $this->db_model->getSelect ( "*", "invoices", array (
				"id" => $invoiceid 
		) );
		$invoicedata = $invoicedata->result_array ();
		$invoicedata = $invoicedata [0];
		$FilePath = FCPATH . "invoices/" . $accountdata ["id"] . '/' . $invoicedata ['invoice_prefix'] . "" . $invoicedata ['invoiceid'] . ".pdf";
		$Filenm = $invoicedata ['invoice_prefix'] . $invoicedata ['invoiceid'] . ".pdf";
		$this->common->get_invoice_template ( $invoicedata, $accountdata, false );
		if ($invoice_conf ['invoice_notification']) {
			$this->send_email_notification ( $FilePath, $Filenm, $accountdata, $invoice_conf, $invoicedata );
		}
	}
	function send_email_notification($FilePath, $Filenm, $AccountData, $invoice_conf, $invData) {
		$TemplateData = array ();
		$where = array (
				'name' => 'email_new_invoice' 
		);
		$EmailTemplate = $this->db_model->getSelect ( "*", "default_templates", $where );
		foreach ( $EmailTemplate->result_array () as $TemplateVal ) {
			$TemplateData = $TemplateVal;
			$TemplateData ['subject'] = str_replace ( '#NAME#', $AccountData ['first_name'] . " " . $AccountData ['last_name'], $TemplateData ['subject'] );
			$TemplateData ['subject'] = str_replace ( '#INVOICE_NUMBER#', $invData ['invoice_prefix'] . $invData ['invoiceid'], $TemplateData ['subject'] );
			$TemplateData ['template'] = str_replace ( '#NAME#', $AccountData ['first_name'] . " " . $AccountData ['last_name'], $TemplateData ['template'] );
			$TemplateData ['template'] = str_replace ( '#INVOICE_NUMBER#', $invData ['invoice_prefix'] . $invData ['invoiceid'], $TemplateData ['template'] );
			$TemplateData ['template'] = str_replace ( '#AMOUNT#', $invData ['amount'], $TemplateData ['template'] );
			
			$TemplateData ['template'] = str_replace ( "#COMPANY_EMAIL#", $invoice_conf ['emailaddress'], $TemplateData ['template'] );
			$TemplateData ['template'] = str_replace ( "#COMPANY_NAME#", $invoice_conf ['company_name'], $TemplateData ['template'] );
			$TemplateData ['template'] = str_replace ( "#COMPANY_WEBSITE#", $invoice_conf ['website'], $TemplateData ['template'] );
			$TemplateData ['template'] = str_replace ( "#INVOICE_DATE#", $invData ['invoice_date'], $TemplateData ['template'] );
			$TemplateData ['template'] = str_replace ( "#DUE_DATE#", $invData ['due_date'], $TemplateData ['template'] );
		}
		$dir_path = getcwd () . "/attachments/";
		// No shell and no directories from the file name: the invoice prefix is part of it and
		// resellers can edit it in their company profile.
		$Filenm = basename ( $Filenm );
		$FilePath = FCPATH . "invoices/" . ( int ) $AccountData ['id'] . "/" . $Filenm;
		$path = $dir_path . $Filenm;
		if (is_file ( $FilePath )) {
			@copy ( $FilePath, $path );
		}
		$email_array = array (
				'accountid' => $AccountData ['id'],
				'subject' => $TemplateData ['subject'],
				'body' => $TemplateData ['template'],
				'from' => $invoice_conf ['emailaddress'],
				'to' => $AccountData ['email'],
				'status' => "1",
				'attachment' => $Filenm,
				'template' => '' 
		);
		$this->db->insert ( "mail_details", $email_array );
	}
}

?>




