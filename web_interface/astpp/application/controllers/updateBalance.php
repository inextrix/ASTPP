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
class UpdateBalance extends MX_Controller {
	public $account_arr = array ();
	public $currentdate = '';
	function __construct() {
		parent::__construct ();
		// Cron controller: only reachable from this server (CLI, or wget from cron_settings)
		if (! astpp_internal_request ()) {
			show_404 ();
		}
		$this->load->model ( "db_model" );
		$this->load->model ( "common_model" );
		$this->load->library ( "astpp/common" );
		$this->currentdate = gmdate ( 'Y-m-d H:i:s' );
	}
	function GetUpdateBalance() {
		$accounts_data = $this->get_table_data ( "*", "accounts", array (
				"status" => "0",
				"deleted" => "0" 
		) );
		foreach ( $accounts_data as $account_val ) {
			$this->process_subscriptions ( $account_val, gmdate ( "Y-m-d H:i:s" ), gmdate ( "Y-m-d H:i:s" ) );
			$this->process_DID_charges ( $account_val, gmdate ( "Y-m-d H:i:s" ), gmdate ( "Y-m-d H:i:s" ) );
		}
	}
	function process_subscriptions($accountinfo, $startdate, $enddate, $Manualflg = false) {
		// Defined Original Sweep it for calculation start date for first time.
		$accountinfo ['original_sweep_id'] = $accountinfo ['sweep_id'];
		$invoiceid = 0;
		
		// Check Charge is applied that is for first time or already calculated before
		
		$where = "(charge_upto ='0000-00-00 00:00:00' OR charge_upto <='" . gmdate ( "Y-m-d H:i:s" ) . "')";
		$this->db->where ( 'accountid', $accountinfo ['id'] );
		$this->db->where ( $where );
		$this->db->select ( '*' );
		$account_charges = $this->db->get ( 'charge_to_account' );
		if ($account_charges->num_rows () > 0) {
			$accountchargs = $account_charges->result_array ();
			
			foreach ( $accountchargs as $accharges ) {
				// Get information from database of active charges.
				$charge_data = $this->get_table_data ( "*", "charges", array (
						"id" => $accharges ['charge_id'],
						"status" => "0" 
				) );
				if ($charge_data && ! empty ( $charge_data )) {
					$charge_data = $charge_data [0];
					// Get Start Date of Charge
					$charge_upto = ($accharges ["charge_upto"] != "0000-00-00 00:00:00" && $accharges ["charge_upto"] != "") ? date ( "Y-m-d H:i:s", strtotime ( "+1 Second", strtotime ( $accharges ["charge_upto"] ) ) ) : $accharges ["assign_date"];
					if ($accountinfo ['original_sweep_id'] == 2 && $charge_data ['pro_rate'] == 0 && $charge_data ['sweep_id'] == 2 && ($accountinfo ['invoice_day'] < gmdate ( "d", strtotime ( $accharges ['assign_date'] ) ))) {
						$charge_upto = date ( "Y-m-" . $accountinfo ['invoice_day'] . " H:i:s", strtotime ( $accharges ["assign_date"] ) );
					}
					// Overwrite Billing scehdule of customer assign proper charge
					if ($accountinfo ['sweep_id'] != $charge_data ['sweep_id']) {
						$accountinfo ['sweep_id'] = $charge_data ['sweep_id'];
					}
					// IF its already assigned before invoice start date then no need to applied same charge one more time
					if ($charge_upto != "0000-00-00 00:00:00" && $charge_upto != "" && strtotime ( $charge_upto ) < strtotime ( $enddate )) {
						$fromdate = gmdate ( "Y-m-d H:i:s", strtotime ( $charge_upto ) );
						if ($Manualflg) {
							$todate = gmdate ( "Y-m-d H:i:s", strtotime ( $enddate ) );
						} else {
							$todate = gmdate ( "Y-m-d H:i:s", strtotime ( $charge_upto ) );
						}
						/*
						 * Add assign_date and charge_upto variable for calculation purpose.
						 */
						$itemArr = array (
								'description' => $charge_data ['description'],
								'item_id' => $charge_data ['id'],
								"type" => "SUBCHRG",
								"cycle" => $charge_data ["sweep_id"],
								"assign_date" => $accharges ['assign_date'],
								"charge_upto" => $accharges ['charge_upto'] 
						);
						// Add a new arguement ($accharges) which have all information of charges.
						$lastdate = $this->calculate_charges ( $accountinfo, $itemArr, $charge_data ["charge"], $fromdate, $todate, $charge_data ["pro_rate"] );
						if ($lastdate) {
							$this->db->update ( "charge_to_account", array (
									"charge_upto" => $lastdate 
							), array (
									"charge_id" => $charge_data ["id"],
									"accountid" => $accountinfo ["id"] 
							) );
						}
					}
				}
			}
		}
	}
	function process_DID_charges($AccountDATA, $startdate, $enddate, $Manualflg = false) {
		$dids_data = $this->get_table_data ( "*", "dids", array (
				"status" => "0",
				"accountid " => $AccountDATA ["id"] 
		) );
		$AccountDATA ['original_sweep_id'] = $AccountDATA ['sweep_id'];
		$AccountDATA ['sweep_id'] = '2';
		if ($dids_data) {
			foreach ( $dids_data as $did_value ) {
				$charge_upto = ($did_value ["charge_upto"] != "0000-00-00 00:00:00" && $did_value ["charge_upto"] != "") ? $did_value ["charge_upto"] : $did_value ["assign_date"];
				
				if ($charge_upto != "0000-00-00 00:00:00" && $charge_upto != "" && strtotime ( $charge_upto ) < strtotime ( $enddate )) {
					$fromdate = gmdate ( "Y-m-d H:i:s", strtotime ( $charge_upto ) );
					if ($Manualflg) {
						$todate = gmdate ( "Y-m-d H:i:s", strtotime ( $enddate ) );
					} else {
						$todate = gmdate ( "Y-m-d H:i:s", strtotime ( $did_value ["upto_date"] ) );
					}
					$itemArr = array (
							'description' => $did_value ['number'],
							'item_id' => $did_value ['id'],
							"type" => "DIDCHRG",
							"cycle" => "2",
							"assign_date" => $did_value ['assign_date'],
							"charge_upto" => $did_value ['charge_upto'] 
					);
					if ($did_value ['parent_id'] > 0) {
						$parent_id = $did_value ['parent_id'];
						while ( $parent_id != 0 ) {
							$reseller_dids = $this->get_table_data ( "*", "reseller_pricing", array (
									"reseller_id" => $parent_id,
									"note" => $did_value ['number'] 
							) );
							$reseller_acc_data = $this->get_table_data ( "*", "accounts", array (
									"id" => $parent_id 
							) );
							$reseller_acc_data = $reseller_acc_data ['0'];
							$reseller_acc_data ['sweep_id'] = '2';
							$reseller_dids = $reseller_dids [0];
							if (($parent_id == $reseller_dids ['reseller_id'] && $did_value ['accountid'] > 0) || $reseller_dids ['parent_id'] == 0) {
								// Apply charges to resellers customers.
								$lastdate = $this->calculate_charges ( $reseller_acc_data, $itemArr, $reseller_dids ["monthlycost"], $fromdate, $todate, "1" );
								if ($lastdate)
									$this->db->update ( "reseller_pricing", array (
											"charge_upto" => $lastdate 
									), array (
											"note" => $did_value ["number"],
											"reseller_id" => $reseller_acc_data ["id"] 
									) );
							} else {
								// Apply charges to Resellers.
								$lastdate = $this->calculate_charges ( $reseller_acc_data, $itemArr, $reseller_dids ["monthlycost"], $fromdate, $todate, "1" );
								if ($lastdate)
									$this->db->update ( "reseller_pricing", array (
											"charge_upto" => $lastdate 
									), array (
											"note" => $did_value ["number"],
											"reseller_id" => $reseller_acc_data ["id"] 
									) );
							}
							$parent_id = $reseller_dids ['parent_id'];
						}
					}
					
					$lastdate = $this->calculate_charges ( $AccountDATA, $itemArr, $did_value ["monthlycost"], $fromdate, $todate, "1" );
					if ($lastdate)
						$this->db->update ( "dids", array (
								"charge_upto" => $lastdate 
						), array (
								"id" => $did_value ["id"],
								"accountid" => $AccountDATA ["id"] 
						) );
					
				}
			}
		}
	}
	function calculate_charges($AccountDATA, $itemArr, $charge, $fromdate, $todate, $pro_rate = "1") {
		$lastdate = false;
		$billing_cycle = ($AccountDATA ['sweep_id'] == "0") ? "1 day" : "1 month";
		$last_invoice_date = $this->common->get_invoice_date ( "invoice_date", $AccountDATA ['id'], $AccountDATA ['reseller_id'] );
		$last_invoice_date = ($last_invoice_date) ? $last_invoice_date : (($pro_rate == 0 && $AccountDATA ['original_sweep_id'] == 2 && ($AccountDATA ['invoice_day'] < gmdate ( "d", strtotime ( $itemArr ['assign_date'] ) ))) ? date ( "Y-m-" . $AccountDATA ['invoice_day'] . " 00:00:00" ) : (($pro_rate == 1 && $AccountDATA ['original_sweep_id'] == 0) ? date ( "Y-m-d 00:00:00", strtotime ( $itemArr ['assign_date'] ) ) : date ( "Y-m-d 00:00:00", strtotime ( $AccountDATA ['creation'] ) )));
		$last_invoice_date = ($last_invoice_date <= $fromdate) ? $last_invoice_date : $fromdate;
		$Charges_date_range = array ();
		$prorate_array = array ();
		$daylen = 60 * 60 * 24;
		$assign_day = ($AccountDATA ['original_sweep_id'] == 2 && $AccountDATA ['posttoexternal'] == 1) ? gmdate ( "d", strtotime ( $itemArr ['assign_date'] ) ) : 0;
		$billing_day = ($AccountDATA ['original_sweep_id'] == 2 && $AccountDATA ['posttoexternal'] == 1) ? $AccountDATA ['invoice_day'] : 0;
		if ($itemArr ['charge_upto'] == "0000-00-00 00:00:00" && ($billing_day > $assign_day) && $pro_rate == 0 && $itemArr ['cycle'] == 2 && $AccountDATA ['original_sweep_id'] == 2) {
			$last_invoice_date = gmdate ( "Y-m-d H:i:s", strtotime ( "-1 second", strtotime ( date ( "Y-m-" . $AccountDATA ['invoice_day'] . " 00:00:00", strtotime ( $itemArr ["assign_date"] ) ) ) ) );
			$prorate_array [] = array (
					"start_date" => date ( "Y-m-d H:i:s", strtotime ( $itemArr ['assign_date'] ) ),
					"end_date" => $last_invoice_date 
			);
		}
		while ( strtotime ( $last_invoice_date ) <= strtotime ( $todate ) ) {
			$startdate = $last_invoice_date;
			$last_invoice_date = gmdate ( "Y-m-d H:i:s", strtotime ( "+" . $billing_cycle, strtotime ( $last_invoice_date ) ) );
			$Charges_date_range [] = array (
					"start_date" => gmdate ( "Y-m-d H:i:s", strtotime ( "+1 Second", strtotime ( $startdate ) ) ),
					"end_date" => $last_invoice_date 
			);
		}
		if (! empty ( $prorate_array )) {
			array_pop ( $Charges_date_range );
			$Charges_date_range = array_merge ( $prorate_array, $Charges_date_range );
		}
		if (! empty ( $Charges_date_range )) {
			foreach ( $Charges_date_range as $ChargeVal ) {
				if (($pro_rate == 0 && $itemArr ['cycle'] != 2) || (($billing_day <= $assign_day) && $pro_rate == 0 && $itemArr ['cycle'] == 2 && $AccountDATA ['original_sweep_id'] == 2)) {
					$ChargeVal ['end_date'] = gmdate ( "Y-m-d 23:59:59", strtotime ( "-1 Day", strtotime ( $ChargeVal ['end_date'] ) ) );
				}
				if (! empty ( $itemArr ['charge_upto'] ) && empty ( $lastdate )) {
					$start_date = ($itemArr ['charge_upto'] == '0000-00-00 00:00:00' && $AccountDATA ['sweep_id'] == 2) ? $itemArr ['assign_date'] : $ChargeVal ['start_date'];
				} else {
					$start_date = (empty ( $lastdate )) ? $ChargeVal ['start_date'] : gmdate ( "Y-m-d H:i:s", strtotime ( "+1 second", strtotime ( $lastdate ) ) );
				}
				
				$lastdate = date ( "Y-m-d H:i:s", strtotime ( $ChargeVal ['end_date'] ) );
				$end_date_str_time = strtotime ( "+1 Seconds", strtotime ( $lastdate ) );
				$start_date_str_time = strtotime ( gmdate ( "Y-m-d 00:00:00", strtotime ( $start_date ) ) );
				$temp_f_date = new DateTime ( gmdate ( "Y-m-d H:i:s", strtotime ( "+1 second", strtotime ( $lastdate ) ) ) );
				$temp_t_date = new DateTime ( $start_date );
				$diff = $temp_f_date->diff ( $temp_t_date );
				$month = (($diff->format ( '%y' ) * 12) + $diff->format ( '%m' ));
				$temp_charge = $charge;
				if (($month != "1" || $pro_rate == "0") && $itemArr ['cycle'] != '0') {
					// Calculate Number of days in month from start date
					$total_num_of_day = cal_days_in_month ( CAL_GREGORIAN, date ( 'm', $start_date_str_time ), date ( 'Y', $start_date_str_time ) );
					$days_diff = floor ( ($end_date_str_time - $start_date_str_time) / $daylen );
					$chrg_per_day = ($charge / $total_num_of_day);
					$temp_charge = ($chrg_per_day * $days_diff);
				}
				
				if (strtotime ( $ChargeVal ['start_date'] ) < strtotime ( $ChargeVal ['end_date'] )) {
					$this->Manage_invoice_item ( $AccountDATA, $itemArr ['description'], $itemArr ['item_id'], $temp_charge, $itemArr ['type'], $start_date, $lastdate, $todate );
				}
				
			}
		}
		return $lastdate;
	}

	function Manage_invoice_item($AccountData, $description, $item_id, $charge, $type, $fromdate, $todate, $invoicedate) {
		$invoiceid = 0;
		$Bal = "0.00";
		if ($AccountData ["posttoexternal"] == 0) {
			$reseller_id = $AccountData ['type'] == 1 ? $AccountData ['id'] : 0;
			$where = "accountid IN ('" . $reseller_id . "','1')";
			$this->db->where ( $where );
			$this->db->select ( '*' );
			$this->db->order_by ( 'accountid', 'desc' );
			$this->db->limit ( 1 );
			$invoiceconf = $this->db->get ( 'invoice_conf' );
			$invoice_conf = ( array ) $invoiceconf->first_row ();
			$last_invoiceid = $this->common->get_invoice_date ( 'invoiceid', '', $AccountData ['reseller_id'] );
			if ($last_invoiceid && $last_invoiceid > 0) {
				$last_invoiceid = ($last_invoiceid + 1);
			} else {
				$last_invoiceid = $invoice_conf ['invoice_start_from'];
			}
			$last_invoiceid = str_pad ( $last_invoiceid, (strlen ( $last_invoiceid ) + 4), '0', STR_PAD_LEFT );
			$invoice_prefix = $invoice_conf ['invoice_prefix'];
			$due_date = gmdate ( "Y-m-d H:i:s", strtotime ( gmdate ( "Y-m-d H:i:s" ) . " +" . $invoice_conf ['interval'] . " days" ) );
			
			$invoiceid = $this->common_model->generate_receipt ( $AccountData ["id"], $charge, $AccountData, $last_invoiceid, $invoice_prefix, $due_date );
			$this->db->set ( 'balance', 'balance-' . $charge, FALSE );
			$this->db->where ( 'id', $AccountData ["id"] );
			$this->db->update ( "accounts" );
			$AccountData ['balance'] = ($AccountData ['balance'] - $charge);
			$Bal = $AccountData ['balance'];
		} else {
			$Bal = ($AccountData ["credit_limit"] - $AccountData ["balance"]);
		}
		if ($Bal <= 0) {
			$this->db->set ( 'status', "1", FALSE );
			$this->db->where ( 'id', $AccountData ["id"] );
			$this->db->update ( "accounts" );
		}
		$invoice_item_arr = array (
				"accountid" => $AccountData ["id"],
				"reseller_id" => $AccountData ["reseller_id"],
				"description" => trim ( $description . "-" . $fromdate . " to " . $todate ),
				"item_id" => $item_id,
				"debit" => $charge,
				"invoiceid" => $invoiceid,
				"created_date" => trim ( $invoicedate ),
				"item_type" => $type 
		);
		$this->manage_invoice ( $invoice_item_arr );
	}

	function get_table_data($select, $table, $where) {
		$query = $this->db_model->getSelect ( $select, $table, $where );
		if ($query->num_rows () > 0) {
			$query_result = $query->result_array ();
			return $query_result;
		} else {
			return false;
		}
	}
	function Manage_Invoice($invoice_item_arr) {
		$this->db->insert ( "invoice_details", $invoice_item_arr );
	}
}
?> 
