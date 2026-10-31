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


class Crons extends MX_Controller {
    public $CurrentDate = "";
	function __construct() {
		parent::__construct ();
		// Cron controller: only reachable from this server (CLI, or wget from cron_settings)
		if (! astpp_internal_request ()) {
			show_404 ();
		}
	        $this->load->model("db_model");
	        $this->CurrentDate = gmdate("Y-m-d H:i:s");
	}
	
	function index() {
		$crons = $this->db->where('status', 0)->where("'".$this->CurrentDate."' >= next_execution_date OR next_execution_date IS NULL", '', false)->from('cron_settings')->get();
		if($crons->num_rows > 0){
		       foreach ($crons->result() as $row) {
			 $this->db->set('next_execution_date', '"'.$this->calculateNextRun($row,$this->CurrentDate).'"', false)->where('id', $row->id)->update('cron_settings');
			 $row->file_path = str_replace("{BASE_URL}",base_url(),$row->file_path);
			 $output = shell_exec($row->file_path." > /dev/null 2>/dev/null &");
			 $this->db->set('last_execution_date', "'".gmdate("Y-m-d H:i:s")."'", false)->where('id', $row->id)->update('cron_settings');
		       }
		}
exit;
	}
	private function calculateNextRun($obj,$CurrentDate)
	{
	    return gmdate("Y-m-d H:i:s", strtotime($CurrentDate.' + '.$obj->exec_interval.' '.$obj->command));
	}
}
