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
class Getstatus extends MX_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->model ( "db_model" );
		$this->load->library ( "astpp/common" );
	}
	// Internal helper: the leading underscore keeps CodeIgniter from routing it (it sends
	// the command unchanged to the event socket of every FreeSWITCH server).
	function _reload_freeswitch($command, $server_host = "") {
                $response = '';
                $query = $this->db_model->getSelect ( "*", "freeswich_servers", "" );
                $fs_data = $query->result_array ();
                foreach ( $fs_data as $fs_key => $fs_value ) {
                        $fp = $this->freeswitch_lib->event_socket_create ( $fs_value ["freeswitch_host"], $fs_value ["freeswitch_port"], $fs_value ["freeswitch_password"] );
                        if ($fp) {
                                $response .= $this->freeswitch_lib->event_socket_request ( $fp, $command );
                                fclose ( $fp );
                        }
                }
                return $response;
        }

	/*
	 * The status switches post a table name (encrypted, but one token per
	 * table that is valid for every row) and a row id. Before, every logged-in account could switch
	 * any row of any table - e.g. a customer could lock other customers' accounts, SIP devices or
	 * gateways. Now only the tables that have a switch in the grids, and per role:
	 *  - admin / sub admin: every row of these tables
	 *  - reseller: rows of its own customers or its own data (reseller_id), its own SIP devices /
	 *    IP / ANI entries (accountid), its own entry of an opted-in product (reseller_products)
	 *  - customer, provider, callshop: only own SIP devices / IP / ANI entries (accountid)
	 * $flags: the e-mail/SMS/alert switches of the notification templates.
	 */
	function _toggle_allowed($table, $id, $flags = false) {
		$logintype = $this->session->userdata ( 'logintype' );
		$accountinfo = $this->session->userdata ( 'accountinfo' );
		$self = isset ( $accountinfo ['id'] ) ? ( int ) $accountinfo ['id'] : 0;
		if ($self <= 0 || $id <= 0 || ! is_string ( $table )) {
			return false;
		}
		if ($flags) {
			$admin_tables = array ('default_templates');
			$reseller_tables = array ('default_templates');
			$own_tables = array ();
		} else {
			$admin_tables = array ('accessnumber', 'accounts', 'ani_map', 'call_barring', 'calltype', 'cron_settings', 'department', 'dids', 'gateways', 'ip_map', 'localization', 'outbound_routes', 'pricelists', 'products', 'routes', 'sip_devices', 'sip_profiles', 'taxes', 'trunks');
			$reseller_tables = array ('accounts', 'ani_map', 'ip_map', 'localization', 'outbound_routes', 'pricelists', 'routes', 'sip_devices', 'taxes');
			$own_tables = array ('ani_map', 'ip_map', 'sip_devices');
		}
		if ($logintype == 2 || $logintype == 4) {
			return in_array ( $table, $admin_tables, true );
		}
		if ($logintype == 1 && $table === 'reseller_products' && ! $flags) {
			// the grid row id is the product id
			return $this->db->get_where ( 'reseller_products', array ('product_id' => $id, 'account_id' => $self) )->num_rows () > 0;
		}
		if ($logintype == 1 && in_array ( $table, $reseller_tables, true )) {
			$row = $this->db->get_where ( $table, array ('id' => $id) )->row_array ();
			if (empty ( $row )) {
				return false;
			}
			if (isset ( $row ['reseller_id'] ) && ( int ) $row ['reseller_id'] === $self) {
				return true;
			}
			return in_array ( $table, $own_tables, true ) && isset ( $row ['accountid'] ) && ( int ) $row ['accountid'] === $self;
		}
		if ($logintype != 1 && in_array ( $table, $own_tables, true )) {
			$row = $this->db->get_where ( $table, array ('id' => $id) )->row_array ();
			return ! empty ( $row ) && isset ( $row ['accountid'] ) && ( int ) $row ['accountid'] === $self;
		}
		return false;
	}
	function _toggle_denied() {
		$this->output->set_status_header ( 403 );
		echo FALSE;
	}
	// table token and row id of a switch request, false if not allowed
	function _toggle_request($flags = false) {
		$post_data = $this->input->post ();
		if (empty ( $post_data ['table'] ) || ! is_string ( $post_data ['table'] )) {
			return false;
		}
		$post_data ['table'] = $this->common->decode ( $post_data ['table'] );
		$post_data ['id'] = (isset ( $post_data ['id'] ) && is_scalar ( $post_data ['id'] )) ? ( int ) $post_data ['id'] : 0;
		return $this->_toggle_allowed ( $post_data ['table'], $post_data ['id'], $flags ) ? $post_data : false;
	}

	function customer_list_status($id) {

		if ($this->session->userdata ( 'user_login' ) == TRUE) {
		$post_data = $this->input->post ();
			if(isset($post_data['table']) && $post_data['table']!=""){
			$post_data = $this->_toggle_request ();
			if ($post_data === false) {
				$this->_toggle_denied ();
				return;
			}

			$data ['status'] = $post_data ['status'] == 'true' ? 0 : 1;
			$status=$post_data ['status'] == 'true' ? 0 : 1;
			$last_modified_date= gmdate ( 'Y-m-d H:i:s' );
			if ($post_data ['table'] == 'accounts') {
				$where = array (
						'id' => $post_data ['id'] 
				);
				$account_data = ( array ) $this->db_model->getSelect ( "*", "accounts", $where )->first_row ();
			}
			if($post_data ['table'] == 'products'){
					$this->db->update ( 'dids', $data, array (
						"product_id" => $post_data ['id'] 
				) );
			}
			if($post_data ['table'] == 'dids'){
					$this->db->update ( 'dids', $data, array (
						"product_id" => $post_data ['id'] 
				) );
					$this->db->update ( 'products', $data, array (
						"id" => $post_data ['id'] 
				) );
			}
			if($post_data ['table'] == 'reseller_products'){
					// was every reseller's entry of this product and the product
					// itself, even an admin product. Now this reseller's entry, and the product only
					// if it is the reseller's own.
					$accountinfo = $this->session->userdata ( 'accountinfo' );
					$this->db->update ( 'reseller_products', $data, array (
						"product_id" => $post_data ['id'],
						"account_id" => $accountinfo ['id']
				) );
					$this->db->update ( 'products', $data, array (
						"id" => $post_data ['id'],
						"reseller_id" => $accountinfo ['id']
				) );
			}
			// ASTPPCOM-923 Start
			// Written as the intended if/elseif chain: PHP 7 evaluated the nested ternary
			// left-associative, which skipped the protection of the admin account (id 1) and
			// ran several UPDATEs in a row (PHP 8 rejects the syntax).
			if ($post_data ['table'] == 'accounts' && $post_data ['id'] == 1) {
				$result = null;
			} elseif ($post_data ['table'] == 'reseller_products') {
				// done above (the generic UPDATE below used the product id as
				// reseller_products.id and a column that table does not have)
				$result = true;
			} elseif ($post_data ['table'] == 'dids' || $post_data ['table'] == 'accounts' || $post_data ['table'] == 'calltype') {
				$result = $this->db->update ( $post_data ['table'], $data, array ("id" => $post_data ['id']) );
			} elseif ($post_data ['table'] == 'localization' || $post_data ['table'] == 'call_barring') {
				$result = $this->db->update ( $post_data ['table'],array('status'=>$status,'modified_date'=>$last_modified_date), array ("id" => $post_data ['id']) );
			} elseif ($post_data ['table'] == 'department') {
				$result = $this->db->update ( $post_data ['table'],array('status'=>$status), array ("id" => $post_data ['id']));
			} else {
				$result = $this->db->update ( $post_data ['table'],array('status'=>$status,'last_modified_date'=>$last_modified_date), array ("id" => $post_data ['id']) );
			}
			// ASTPPCOM-923 END
			if ($post_data ['table'] == "ip_map") {
							$this->load->library ( 'freeswitch_lib' );
							$command = "api reloadacl";
							$response = $this->_reload_freeswitch ( $command );
					}
				echo TRUE;
			}
			else{
				$this->session->set_flashdata ( 'astpp_notification','Permission Denied!');
				redirect ( base_url () . 'dashboard/' );
			}
		}else{
			redirect ( base_url () . 'dashboard/' );
		}
	}
	
	function get_email_status($id) {
		if ($this->session->userdata ( 'user_login' ) == TRUE) {
		$post_data = $this->_toggle_request ( true );
		if ($post_data === false) {
			$this->_toggle_denied ();
			return;
		}
		$data ['is_email_enable'] = $post_data ['is_email_enable'] == 'true' ? 0 : 1;
		$this->db->update ( $post_data ['table'], $data, array (
				"id" => $post_data ['id'] 
		) );
		
		}else{
			redirect ( base_url () . 'dashboard/' );
		}
	}
	
	function get_sms_status($id) {
		if ($this->session->userdata ( 'user_login' ) == TRUE) {
		$post_data = $this->_toggle_request ( true );
		if ($post_data === false) {
			$this->_toggle_denied ();
			return;
		}
		$data ['is_sms_enable'] = $post_data ['is_sms_enable'] == 'true' ? 0 : 1;
		$this->db->update ( $post_data ['table'], $data, array (
				"id" => $post_data ['id'] 
		) );
		
		}else{
			redirect ( base_url () . 'dashboard/' );
		}
	}
	
	function get_alert_status($id) {
		if ($this->session->userdata ( 'user_login' ) == TRUE) {
		$post_data = $this->_toggle_request ( true );
		if ($post_data === false) {
			$this->_toggle_denied ();
			return;
		}
		$data ['is_alert_enable'] = $post_data ['is_alert_enable'] == 'true' ? 0 : 1;
		$this->db->update ( $post_data ['table'], $data, array (
				"id" => $post_data ['id'] 
		) );
		
		}else{
			redirect ( base_url () . 'dashboard/' );
		}
	}
	
	
}
?>
