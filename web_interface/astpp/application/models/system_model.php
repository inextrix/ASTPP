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
class System_model extends CI_Model {
	function System_model() {
		parent::__construct ();
	}
	function add_config($data) {
		$this->load->library ( "curl" );
		$url = "astpp-wraper.cgi";
		$data ['mode'] = "Configuration";
		$data ['action'] = "Add Item";
		$data ['logintype'] = $this->session->userdata ( 'logintype' );
		$data ['username'] = $this->session->userdata ( 'username' );
		return $this->curl->sendRequestToPerlScript ( $url, $data );
	}
	function edit_config($data) {
		$this->load->library ( "curl" );
		$url = "astpp-wraper.cgi";
		$data ['mode'] = "Configuration";
		$data ['action'] = "Save Item";
		$data ['logintype'] = $this->session->userdata ( 'logintype' );
		$data ['username'] = $this->session->userdata ( 'username' );
		return $this->curl->sendRequestToPerlScript ( $url, $data );
	}
	function get_config_by_id($id) {
		$this->db->where ( "id", $id );
		$query = $this->db->get ( "system" );

		if ($query->num_rows () > 0)
			return $query->row_array ();
		else
			return false;
	}
	function remove_config($data) {
		$this->load->library ( "curl" );
		$url = "astpp-wraper.cgi";
		$data ['mode'] = "Configuration";
		$data ['action'] = "Delete";
		$data ['logintype'] = $this->session->userdata ( 'logintype' );
		$data ['username'] = $this->session->userdata ( 'username' );
		return $this->curl->sendRequestToPerlScript ( $url, $data );
	}
	function add_tax($data) {
		$this->load->library ( "curl" );
		$url = "astpp-wraper.cgi";
		$data ['mode'] = "Taxes";
		$data ['logintype'] = $this->session->userdata ( 'logintype' );
		$data ['username'] = $this->session->userdata ( 'username' );
		$this->curl->sendRequestToPerlScript ( $url, $data );
	}
	function edit_tax($data) {
		$this->load->library ( "curl" );
		$url = "astpp-wraper.cgi";
		$data ['mode'] = "Taxes";
		$data ['logintype'] = $this->session->userdata ( 'logintype' );
		$data ['username'] = $this->session->userdata ( 'username' );
		$this->curl->sendRequestToPerlScript ( $url, $data );
	}
	function get_tax_by_id($id) {
		$this->db->where ( "taxes_id", $id );
		$query = $this->db->get ( "taxes" );

		if ($query->num_rows () > 0)
			return $query->row_array ();
		else
			return false;
	}
	function remove_tax($data) {
		$this->load->library ( "curl" );
		$url = "astpp-wraper.cgi";
		$data ['mode'] = "Taxes";
		$data ['action'] = "Delete";
		$data ['logintype'] = $this->session->userdata ( 'logintype' );
		$data ['username'] = $this->session->userdata ( 'username' );
		$this->curl->sendRequestToPerlScript ( $url, $data );
	}
	function purge_deactivated_records() {
		$this->load->library ( "curl" );
		$url = "astpp-wraper.cgi";
		$data ['mode'] = "Purge Deactivated";
		$data ['action'] = "Yes, Drop Them";
		$data ['logintype'] = $this->session->userdata ( 'logintype' );
		$data ['username'] = $this->session->userdata ( 'username' );
		$feedback = $this->curl->sendRequestToPerlScript ( $url, $data );

		return $feedback = str_replace ( ".", ".<br />", $feedback );
	}
	function getTaxesCount() {
		if ($this->session->userdata ( 'advance_search' ) == 1) {

			$taxes_search = $this->session->userdata ( 'taxes_search' );

			$amount_operator = $taxes_search ['amount_operator'];

			if (! empty ( $taxes_search ['amount'] )) {
				switch ($amount_operator) {
					case "1" :
						$this->db->where ( 'taxes_amount ', $taxes_search ['amount'] );
						break;
					case "2" :
						$this->db->where ( 'taxes_amount <>', $taxes_search ['amount'] );
						break;
					case "3" :
						$this->db->where ( 'taxes_amount > ', $taxes_search ['amount'] );
						break;
					case "4" :
						$this->db->where ( 'taxes_amount < ', $taxes_search ['amount'] );
						break;
					case "5" :
						$this->db->where ( 'taxes_amount >= ', $taxes_search ['amount'] );
						break;
					case "6" :
						$this->db->where ( 'taxes_amount <= ', $taxes_search ['amount'] );
						break;
				}
			}

			$rate_operator = $taxes_search ['rate_operator'];

			if (! empty ( $taxes_search ['rate'] )) {
				switch ($rate_operator) {
					case "1" :
						$this->db->where ( 'taxes_rate ', $taxes_search ['rate'] );
						break;
					case "2" :
						$this->db->where ( 'taxes_rate <>', $taxes_search ['rate'] );
						break;
					case "3" :
						$this->db->where ( 'taxes_rate > ', $taxes_search ['rate'] );
						break;
					case "4" :
						$this->db->where ( 'taxes_rate < ', $taxes_search ['rate'] );
						break;
					case "5" :
						$this->db->where ( 'taxes_rate >= ', $taxes_search ['rate'] );
						break;
					case "6" :
						$this->db->where ( 'taxes_rate <= ', $taxes_search ['rate'] );
						break;
				}
			}

			$description_operator = $taxes_search ['description_operator'];
			if (! empty ( $taxes_search ['description'] )) {
				switch ($description_operator) {
					case "1" :
						$this->db->like ( 'taxes_description', $taxes_search ['description'] );
						break;
					case "2" :
						$this->db->not_like ( 'taxes_description', $taxes_search ['description'] );
						break;
					case "3" :
						$this->db->where ( 'taxes_description', $taxes_search ['description'] );
						break;
					case "4" :
						$this->db->where ( 'taxes_description <>', $taxes_search ['description'] );
						break;
				}
			}
		}
		$this->db->from ( 'taxes' );
		$providercnt = $this->db->count_all_results ();
		return $providercnt;
	}
	function getTaxesList($start, $limit) {
		if ($this->session->userdata ( 'advance_search' ) == 1) {

			$taxes_search = $this->session->userdata ( 'taxes_search' );

			$amount_operator = $taxes_search ['amount_operator'];

			if (! empty ( $taxes_search ['amount'] )) {
				switch ($amount_operator) {
					case "1" :
						$this->db->where ( 'taxes_amount =', $taxes_search ['amount'] );
						break;
					case "2" :
						$this->db->where ( 'taxes_amount <>', $taxes_search ['amount'] );
						break;
					case "3" :
						$this->db->where ( 'taxes_amount > ', $taxes_search ['amount'] );
						break;
					case "4" :
						$this->db->where ( 'taxes_amount < ', $taxes_search ['amount'] );
						break;
					case "5" :
						$this->db->where ( 'taxes_amount >= ', $taxes_search ['amount'] );
						break;
					case "6" :
						$this->db->where ( 'taxes_amount <= ', $taxes_search ['amount'] );
						break;
				}
			}

			$rate_operator = $taxes_search ['rate_operator'];

			if (! empty ( $taxes_search ['rate'] )) {
				switch ($rate_operator) {
					case "1" :
						$this->db->where ( 'taxes_rate == ', $taxes_search ['rate'] );
						break;
					case "2" :
						$this->db->where ( 'taxes_rate <>', $taxes_search ['rate'] );
						break;
					case "3" :
						$this->db->where ( 'taxes_rate > ', $taxes_search ['rate'] );
						break;
					case "4" :
						$this->db->where ( 'taxes_rate < ', $taxes_search ['rate'] );
						break;
					case "5" :
						$this->db->where ( 'taxes_rate >= ', $taxes_search ['rate'] );
						break;
					case "6" :
						$this->db->where ( 'taxes_rate <= ', $taxes_search ['rate'] );
						break;
				}
			}

			$description_operator = $taxes_search ['description_operator'];
			if (! empty ( $taxes_search ['description'] )) {
				switch ($description_operator) {
					case "1" :
						$this->db->like ( 'taxes_description', $taxes_search ['description'] );
						break;
					case "2" :
						$this->db->not_like ( 'taxes_description', $taxes_search ['description'] );
						break;
					case "3" :
						$this->db->where ( 'taxes_description', $taxes_search ['description'] );
						break;
					case "4" :
						$this->db->where ( 'taxes_description <>', $taxes_search ['description'] );
						break;
				}
			}
		}
		$this->db->limit ( $limit, $start );
		$this->db->order_by ( "taxes_priority,taxes_description desc" );
		$this->db->from ( 'taxes' );
		$query = $this->db->get ();
		return $query;
	}
	function getAuthInfo() {
		$this->db->where ( 'name', 'auth' );
		$this->db->limit ( 1 );
		$query = $this->db->get ( 'system' );
		if ($query->num_rows () > 0) {
			return $query->result ();
		}
	}
	function get_template_count() {
		if ($this->session->userdata ['userlevel_logintype'] == 1 || $this->session->userdata ['userlevel_logintype'] == 4 || $this->session->userdata ['userlevel_logintype'] == 5) {
			$acountid = $this->session->userdata ['accountinfo'] ['accountid'];
			$this->db->where ( 'accountid', $acountid );
		}
		return $this->db->get ( 'templates' );
	}
	function get_templates() {
		if ($this->session->userdata ( 'advance_search' ) == 1) {
			$templatesearch = $this->session->userdata ( 'template_search' );
			$template_name_operator = $templatesearch ['template_name_operator'];

			if (! empty ( $templatesearch ['template_name'] )) {
				switch ($template_name_operator) {
					case "1" :
						$this->db->like ( 'name', $templatesearch ['template_name'] );
						break;
					case "2" :
						$this->db->not_like ( 'name', $templatesearch ['template_name'] );
						break;
					case "3" :
						$this->db->where ( 'name', $templatesearch ['template_name'] );
						break;
					case "4" :
						$this->db->where ( 'name <>', $templatesearch ['template_name'] );
						break;
				}
			}

			$template_subject = $templatesearch ['subject_operator'];
			if (! empty ( $templatesearch ['subject'] )) {
				switch ($template_subject) {
					case "1" :
						$this->db->like ( 'subject', $templatesearch ['subject'] );
						break;
					case "2" :
						$this->db->not_like ( 'subject', $templatesearch ['subject'] );
						break;
					case "3" :
						$this->db->where ( 'subject', $templatesearch ['subject'] );
						break;
					case "4" :
						$this->db->where ( 'subject <>', $templatesearch ['subject'] );
						break;
				}
			}
			$template_op = $templatesearch ['template_operator'];
			if (! empty ( $templatesearch ['template_desc'] )) {

				$db = get_instance()->db->conn_id;

				switch ($template_op) {
					case "1" :
						$this->db->like ( 'template', mysqli_real_escape_string ( $db, ($templatesearch ['template_desc']) ) );
						break;
					case "2" :
						$this->db->not_like ( 'template', mysqli_real_escape_string ( $db, $templatesearch ['template_desc'] ) );
						break;
					case "3" :
						$this->db->where ( 'template', mysqli_real_escape_string ( $db, $templatesearch ['template_desc'] ) );
						break;
					case "4" :
						$this->db->where ( 'template <>', mysqli_real_escape_string ( $db, $templatesearch ['template_desc'] ) );
						break;
				}
			}

			if (! empty ( $templatesearch ['accountid'] )) {
				$this->db->like ( 'accountid', $templatesearch ['accountid'] );
			}
		}

		if ($this->session->userdata ['userlevel_logintype'] == 1 || $this->session->userdata ['userlevel_logintype'] == 4 || $this->session->userdata ['userlevel_logintype'] == 5) {
			$acountid = $this->session->userdata ['accountinfo'] ['accountid'];
			$this->db->where ( 'accountid', $acountid );
		}
		return $this->db->get ( 'templates' );
	}
	function get_template_by_id($id) {
		$this->db->delete ( 'templates', array (
				'id' => $id
		) );
		return true;
	}
	function get_template_by_id_all($id) {
		$this->db->where ( 'id', $id );
		$query = $this->db->get ( 'templates' );
		if ($query->num_rows () > 0)
			return $query->row_array ();
		else
			return false;
	}
	function edit_template($edit_id, $data) {
		$updatedata = array (
				"name" => trim ( $data ['tem_name'] ),
				"template" => trim ( $data ['template'] ),
				"subject" => trim ( $data ['subject'] ),
				"modified_date" => trim ( date ( 'Y-m-d H:i:s' ) )
		);
		$this->db->where ( 'id', $edit_id );
		$this->db->update ( 'templates', $updatedata );
		return true;
	}
	function build_systems_configuration() {
		if ($this->session->userdata ( 'advance_search' ) == 1) {
			$configuration_search = $this->session->userdata ( 'configuration_search' );

			if (! empty ( $configuration_search ['reseller'] )) {
				$this->db->where ( 'reseller ', $configuration_search ['reseller'] );
			}
			if (! empty ( $configuration_search ['brand'] )) {
				$this->db->where ( 'brand', $configuration_search ['brand'] );
			}
			if (! empty ( $configuration_search ['group_title'] )) {
				$this->db->where ( 'group_title', $configuration_search ['group_title'] );
			}

			$name_operator = $configuration_search ['name_operator'];

			if (! empty ( $configuration_search ['name'] )) {
				switch ($name_operator) {
					case "1" :
						$this->db->like ( 'name', $configuration_search ['name'] );
						break;
					case "2" :
						$this->db->not_like ( 'name', $configuration_search ['name'] );
						break;
					case "3" :
						$this->db->where ( 'name', $configuration_search ['name'] );
						break;
					case "4" :
						$this->db->where ( 'name <>', $configuration_search ['name'] );
						break;
				}
			}

			$value_operator = $configuration_search ['value_operator'];

			if (! empty ( $configuration_search ['value'] )) {
				switch ($value_operator) {
					case "1" :
						$this->db->like ( 'value', $configuration_search ['value'] );
						break;
					case "2" :
						$this->db->not_like ( 'value', $configuration_search ['value'] );
						break;
					case "3" :
						$this->db->where ( 'value', $configuration_search ['value'] );
						break;
					case "4" :
						$this->db->where ( 'value <>', $configuration_search ['value'] );
						break;
				}
			}

			$comment_operator = $configuration_search ['comment_operator'];

			if (! empty ( $configuration_search ['comment'] )) {
				switch ($comment_operator) {
					case "1" :
						$this->db->like ( 'comment', $configuration_search ['comment'] );
						break;
					case "2" :
						$this->db->not_like ( 'comment', $configuration_search ['comment'] );
						break;
					case "3" :
						$this->db->where ( 'comment', $configuration_search ['comment'] );
						break;
					case "4" :
						$this->db->where ( 'comment <>', $configuration_search ['comment'] );
						break;
				}
			}
		}
	}
	function systems_configuration($flag, $start = '', $limit = '') {
		$this->build_systems_configuration ();
		$this->db->from ( 'system' );
		if ($flag) {
			$this->db->order_by ( "id ASC" );
			$this->db->limit ( $limit, $start );
			$query = $this->db->get ();
		} else {
			$query = $this->db->count_all_results ();
		}
		return $query;
	}
}

?>
