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
class db extends PDO {
	private $error;
	private $sql;
	private $bind;
	private $errorCallbackFunction;
	private $errorMsgFormat;
	public function __construct($cdr = "") {
		$config = parse_ini_file ( "/var/lib/astpp/astpp-config.conf" );
		
		// Never allow several statements in one query (stacked queries).
		$options = array (
				PDO::ATTR_PERSISTENT => true,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
		);
		
		try {
			$dbhost = (isset($config [$cdr.'dbhost']) && $config [$cdr.'dbhost'] != '')?$config [$cdr.'dbhost']:$config ['dbhost'];
			$dbname = (isset($config [$cdr.'dbname']) && $config [$cdr.'dbname'] != '')?$config [$cdr.'dbname']:$config ['dbname'];
			$dbuser = (isset($config [$cdr.'dbuser']) && $config [$cdr.'dbuser'] != '')?$config [$cdr.'dbuser']:$config ['dbuser'];
			$dbpass = (isset($config [$cdr.'dbpass']) && $config [$cdr.'dbpass'] != '')?$config [$cdr.'dbpass']:$config ['dbpass'];
			parent::__construct ( "mysql:host=" . $dbhost . ";dbname=" . $dbname . "", $dbuser, $dbpass, $options );
		} catch ( PDOException $e ) {
			
			$this->error = $e->getMessage ();
		}
		echo $this->error;
	}
	
	/**
	 * Escape a value for use inside a quoted SQL string literal.
	 */
	public function esc($value) {
		return substr ( $this->quote ( ( string ) $value ), 1, - 1 );
	}
	
	/**
	 *
	 * @param string $bind        	
	 */
	public function cleanup($bind) {
		if (! is_array ( $bind )) {
			if (! empty ( $bind ))
				$bind = array (
						$bind 
				);
			else
				$bind = array ();
		}
		return $bind;
	}
	public function run($sql, $bind = "") {
		$this->sql = trim ( $sql );
		$this->bind = $this->cleanup ( $bind );
		$this->error = "";
		
		try {
			$pdostmt = $this->prepare ( $this->sql );
			if ($pdostmt->execute ( $this->bind ) !== false) {
				if (preg_match ( "/^(" . implode ( "|", array (
						"select",
						"describe",
						"pragma" 
				) ) . ") /i", $this->sql ))
					return $pdostmt->fetchAll ( PDO::FETCH_ASSOC );
				elseif (preg_match ( "/^(" . implode ( "|", array (
						"delete",
						"insert",
						"update" 
				) ) . ") /i", $this->sql ))
					return $pdostmt->rowCount ();
			}
		} catch ( PDOException $e ) {
			$this->error = $e->getMessage ();
			return $this->error;
		}
	}
}

?>
