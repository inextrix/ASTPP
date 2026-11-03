<?php

// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
//
// Copyright (C) 2016 Inextrix Technologies Pvt. Ltd.
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
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
#[\AllowDynamicProperties]
class Siprouting_form extends common {
	function __construct() {
		$this->CI = & get_instance ();
	}
	//Nirali  issue 3110 PBX Voicemail greeting based on SIP device status
	function play_recoding_file($select = "", $table = "", $status = ""){
        $path_file = '';
        $action ='';

        if(astpp_gt($status, 0)){
            $user_data = ( array ) $this->CI->db->get_where( 'pbx_recording',array("id"=>$status) )->first_row();
            $recording='';
            $path_file = FCPATH.'upload/pbx/'.$user_data['file_name'];
        }
        if(file_exists($path_file)){
            $account_data = $this->CI->session->userdata("accountinfo");
            $url =base_url()."freeswitch/fssipdevices_voicemail_file_play/".$user_data['file_name'];
            if ($account_data['type'] == '0'){
                     $url =base_url()."user/user_fssipdevices_voicemail_file_play/".$user_data['file_name'];
                 }
            else{
                     $url =base_url()."siprouting/fssipdevices_voicemail_file_play/".$user_data['file_name'];
                 }
                    
            $play_img_url =base_url()."assets/images/play_file.png";
            $pause_img_url =base_url()."assets/images/pause.png";
            $action = '<audio id="myAudio_'.$status.'">
                <source src="'.$url.'" type="audio/mpeg">
                Your browser does not support the audio element.
                </audio><button onclick="playAudio('.$status.')" type="button" class="btnplay mt-3"  id="play_'.$status.'"  style="display:block;border:0px !important; padding:0px"><img src='.$play_img_url.' height="22px" width="22px" style="cursor: pointer;"/></button>
                <button onclick="pauseAudio('.$status.')" type="button"  class="btnplay mt-3" id="pause_'.$status.'" style="display: none;border:0px !important;padding:0px"><img src='.$pause_img_url.' height="22px" width="22px" style="cursor: pointer;"/></button> ';
        }
        return $action;
     }
	 //END
	
}

?>
