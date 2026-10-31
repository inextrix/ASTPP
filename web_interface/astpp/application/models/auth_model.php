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
class Auth_model extends CI_Model {
	function __construct() {
		parent::__construct ();
	}
	/*
	 * $password is the password as typed. Returns the id of the account
	 * (0: login failed). The Blowfish value used to be compared in SQL and the login then took the
	 * first account with this number or e-mail address - with two accounts sharing an e-mail
	 * address not necessarily the one whose password matched.
	 * Stored hashes are checked with password_verify(); an old Blowfish value is replaced by a hash
	 * after the first successful login (only if nobody changed it meanwhile). Every attempt costs
	 * at least one hash check, so the answer time does not tell whether the account exists.
	 */
	function verify_login($username, $password) {
		$q = "SELECT id, password FROM accounts WHERE (number = BINARY '" . $this->db->escape_str ( $username ) . "'";
		$q .= " OR email = BINARY '" . $this->db->escape_str ( $username ) . "')";
		$q .= " AND type IN (1,2,3,4,5,0,-1) AND deleted = 0 AND status = 0 ORDER BY id";
		$rows = $this->db->query ( $q )->result_array ();
		$found = null;
		$hashed = false;
		foreach ( $rows as $row ) {
			$hashed = $hashed || $this->common->password_is_hash ( $row ['password'] );
			if ($this->common->password_matches ( $password, $row ['password'] )) {
				$found = $row;
				break;
			}
		}
		if (! $hashed) {
			$this->common->password_dummy_check ( $password );
		}
		if ($found === null) {
			return 0;
		}
		if ($this->common->password_needs_update ( $found ['password'] )) {
			$this->db->where ( array (
					'id' => $found ['id'],
					'password' => $found ['password']
			) );
			$this->db->update ( 'accounts', array (
					'password' => $this->common->password_store ( $password )
			) );
		}
		$this->session->set_userdata ( 'user_name', $username );
		return (int) $found ['id'];
	}
}

?>
