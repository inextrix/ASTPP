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
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class AutomatedReportDownload extends CI_Controller {
  public function __construct(){
     parent::__construct();
     $this->load->model("db_model");
     $this->load->helper('url');
     $this->load->library('zip');
  }
  public function index(){
     $this->load->view('automated_report_download');
  }

  public function Downloadzip(){
     $this->load->view('automated_report_download');
     $file_identifier = $_GET['file'];
     $file_info = (array)$this->db->get_where('automated_report_log',array("usercode"=> $file_identifier ))->first_row();
     if(!empty($file_info))
     {
         $dir_path = getcwd()."/attachments/".$file_info['filename'];
         ob_end_clean();
         $this->zip->read_file($dir_path);
         $filename = $file_info['filename'];
         $this->zip->download($filename);
     }else{  
         $dir_path = getcwd()."/assets/Rates_File/automated_reports_sample/automated_report_message.csv";
         ob_end_clean();
         $this->zip->read_file($dir_path);
         $filename = 'no_records_found';
         $this->zip->download($filename);   
     }
   }
}