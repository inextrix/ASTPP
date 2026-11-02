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
	        $this->load->model("db_model");
	        $this->CurrentDate = gmdate("Y-m-d H:i:s");
	}
	
	function index() {
		$crons = $this->db->where('status', 0)->where("'".$this->CurrentDate."' >= next_execution_date OR next_execution_date IS NULL", '', false)->from('cron_settings')->get();
		if($crons->num_rows > 0){
		    foreach ($crons->result() as $row) {
			$this->db->set('next_execution_date', '"'.$this->calculateNextRun($row,$this->CurrentDate).'"', false)->where('id', $row->id)->update('cron_settings');
			// ASTPPENT-8702 Ashish start
			$base_url_array= explode("{BASE_URL}",$row->file_path);
			$url=explode('/',$base_url_array[1]);
			$class_name = $url[0];
			$function_name = $url[1];
			$file_path = FCPATH ; 
			$ps_path = exec("which ps");
			$command1 = trim($ps_path).' aux | grep "'.$class_name.'"'.' | grep "php"';
			$output = shell_exec($command1);
			if (strpos($output, 'index') !== false) {
				echo $class_name;
			}else{
				$command = 'cd '.$file_path.' && '.'php index.php '.$class_name.' '.$function_name;
				exec($command,$output1);
			}
			// ASTPPENT-8702 Ashish end
			$this->db->set('last_execution_date', "'".gmdate("Y-m-d H:i:s")."'", false)->where('id', $row->id)->update('cron_settings');
		    }
		}
exit;
	}
	private function calculateNextRun($obj,$CurrentDate)
	{
		// ASTPPENT-8702 Ashish start
	    return date("Y-m-d H:i:s", strtotime($CurrentDate.' + '.$obj->exec_interval.' '.$obj->command));
		// ASTPPENT-8702 Ashish end
	}
}
