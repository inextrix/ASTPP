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
class lib {
	var $config;
	function get_configurations($db) {
//HP: change query for mysql 8.0 version
		$query = "SELECT * FROM `system` WHERE group_title IN ('global','opensips','callingcard','calls','homer')";
		$res_conf = $db->run ( $query );
		foreach ( $res_conf as $res_conf_key => $res_conf_value )
			$this->config [$res_conf_value ['name']] = $res_conf_value ['value'];
		
		return $this->config;
	}
}

?>
