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

/**
 * @cont: Run_CRON
 *
 * : non-web controller
 *
 * @site: www.example.com
 * 
 * @author : Michael Pope
 *         nyndesigns.com
 *        
 *         @file: run_cron.php
 *         @date: 10.03.2011 - 11:53:06 [Michael Pope]
 */
#[\AllowDynamicProperties]
class Run_CRON extends CI_Controller {
	function __construct() {
		parent::__construct ();
		if (! defined ( 'CRON' ))
			exit ();
	}
	function __destruct() {
	}
	
	/**
	 * Index
	 *
	 * not used
	 */
	function index() {
	}
	
	/**
	 * All
	 */
	function all() {
		$this->generate_sitemap ();
	}
	
	/**
	 * Generate Sitemap XML
	 */
	function generate_sitemap() {
		// Live Mode:
		if (! CRON_BETA_MODE)
			$cron_id = $this->cron->create ( 'Sitemap (Google|Bing|Ask|Yahoo!)' );
			
			// Example Code
		
		$this->load->library ( 'sitemap' );
		$this->load->config ( 'sitemap' );
		
		// ...
		// ...
		// ...
		
		// Sandbox Mode:
		if (CRON_BETA_MODE)
			$this->sitemap->generate_xml ( null, false );
			
			// Live Mode:
		else {
			echo 'live';
			
			$this->sitemap->generate_xml ();
			$this->cron->update ( $cron_id );
		}
	}
}
/* End of file run_cron.php */
/* Location: ./application/controllers/run_cron.php */
