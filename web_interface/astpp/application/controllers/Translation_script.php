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
class Translation_script extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		// Only internal (cron) calls or a logged-in administrator
		if (! astpp_internal_request ()) {
			$this->load->library ( 'session' );
			if ($this->session->userdata ( 'user_login' ) != TRUE || ! in_array ( ( string ) $this->session->userdata ( 'logintype' ), array ( '2', '4' ), true )) {
				show_404 ();
			}
		}
		$this->load->library('csvreader');
	}
    public function index(){
    
        if (!file_exists(FCPATH. 'language')) {
            mkdir(FCPATH.'language', 0777, true);
        }
        $query = $this->db->get('translations');
        if ($query->num_rows () > 0) {
            $result    = $query->result_array();
            $this->db->select('locale');
            $languages = $this->db->get('languages');
            if ($languages->num_rows () > 0) {
                    $locale_data = $languages->result_array();

                        foreach ($locale_data as $locale_value) {

			    if(!is_dir(FCPATH.'language/'.$locale_value['locale'])){
	                        mkdir(FCPATH.'language/'.$locale_value['locale']);
                            	chmod(FCPATH.'language/'.$locale_value['locale'], 0777);
				mkdir(FCPATH.'language/'.$locale_value['locale'].'/LC_MESSAGES/');
                            	chmod(FCPATH.'language/'.$locale_value['locale'].'/LC_MESSAGES/', 0777);
			    }
                            

                            $myfile = fopen(FCPATH.'language/'.$locale_value['locale'].'/LC_MESSAGES/'.'messages'.'.po', "w");
			    if($myfile){
                                foreach ($result as $data_value) {
                                    unset($data_value['id']);
					$txt = "";
					    $msgid = $data_value['module_name'];
					    $msgstr = $data_value[$locale_value['locale']];
                                            $txt .= 'msgid'.' "'.$msgid.'"'. "\n";
                                            $txt .= 'msgstr'.' "'.$msgstr.'"'. "\n\n";
                                            fwrite($myfile, $txt);
                                }
				fclose($myfile);
	                        $command = 'cd '.FCPATH.'language/'.$locale_value['locale'].'/LC_MESSAGES/ && msgfmt '.'messages'. '.po -o ' .'messages'.'.mo';
                            	exec($command);
	                        
			    }else{
		                $this->session->set_flashdata ( 'astpp_errormsg', 'unable to write files.' );
			    }
                        }

                $this->session->set_flashdata ( 'astpp_errormsg', 'Language Translations sucessfull!' );
                redirect ( base_url () . 'systems/languages_list/' );
                die();
            } else {
                $this->session->set_flashdata ( 'astpp_notification', 'No data found!' );
                redirect ( base_url () . 'systems/languages_list/' );
                die();
            }
        } else {
            $this->session->set_flashdata ( 'astpp_notification', 'No data found!' );
            redirect ( base_url () . 'systems/languages_list/' );
            die();
        }
    }
	function insert_translation($language_name){
		if (!file_exists(FCPATH. 'language')) {
		    mkdir(FCPATH.'language', 0777, true);
		}
		$language_name=$this->get_language_locale($language_name);
		$new_final_arr_key= array (
			'en_En' => 'en_En',
			$language_name => $language_name,
		);
		$file_name=FCPATH.'language/'.$language_name.'.csv';
		$csv_tmp_data = $this->csvreader->parse_file($file_name, $new_final_arr_key, false);
		unset($csv_tmp_data[0],$csv_tmp_data[1]);
		foreach ($csv_tmp_data as $key => $csv_data) {
			if (isset($csv_data[$language_name]) && $csv_data[$language_name] != '') {
				//$this->db->where('en_En',$csv_data['en_En']);
				//$this->db->set($language_name,$csv_data[$language_name]);
				//$this->db->update('translations');
				$data[]=array(
					'en_En'=>$csv_data['en_En'],
					$language_name=>$csv_data[$language_name]
				);
			}
		}
		$this->db->update_batch('translations',$data,'en_En');
		$module_name='';
		$this->insert_translation_addons($module_name,$language_name);
       		$this->generate_file($language_name);
	}
	function generate_file($locale_language){
		$query = $this->db->get('translations');
		if ($query->num_rows () > 0) {
		    $result    = $query->result_array();
			if(!is_dir(FCPATH.'language/'.$locale_language)){
				mkdir(FCPATH.'language/'.$locale_language);
				chmod(FCPATH.'language/'.$locale_language, 0777);
				mkdir(FCPATH.'language/'.$locale_language.'/LC_MESSAGES/');
				chmod(FCPATH.'language/'.$locale_language.'/LC_MESSAGES/', 0777);
			}
			$myfile = fopen(FCPATH.'language/'.$locale_language.'/LC_MESSAGES/'.'messages'.'.po', "w");
			if($myfile){
				foreach ($result as $data_value) {
					unset($data_value['id']);
					$txt = "";
					$msgid = $data_value['en_En'];
					$msgstr = $data_value[$locale_language];
					$txt .= 'msgid'.' "'.$msgid.'"'. "\n";
					$txt .= 'msgstr'.' "'.$msgstr.'"'. "\n\n";
					fwrite($myfile, $txt);
				}
				fclose($myfile);
				$command = 'cd '.FCPATH.'language/'.$locale_language.'/LC_MESSAGES/ && msgfmt '.'messages'. '.po -o ' .'messages'.'.mo';
				$gs_command_output = system($command, $retval);	
			}
		}
	}
	function language_uninstall($language_name){
		$locale=$this->get_language_locale($language_name);
		if(is_dir(FCPATH.'language/'.$locale)){
			unlink(FCPATH.'language/'.$locale.'/LC_MESSAGES/messages.mo');
			unlink(FCPATH.'language/'.$locale.'/LC_MESSAGES/messages.po');
			rmdir(FCPATH.'language/'.$locale.'/LC_MESSAGES/');
			rmdir(FCPATH.'language/'.$locale);
		}
	}
	function get_language_locale($language_name){
		$explode_module=explode('_',$language_name);
		$language_name=ucfirst($explode_module[1]);
		$this->db->select('locale');
		$this->db->where('name',$language_name);
		$languages = (array)$this->db->get('languages')->first_row();
		return $languages['locale'];
	}
	function insert_translation_addons($module_name='',$locale=''){
		
		if (!file_exists(FCPATH. 'language')) {
		    mkdir(FCPATH.'language', 0777, true);
		}
		if(isset($locale) && $locale != ''){
			$this->db->where('locale',$locale);
		}
		$languages = $this->db->get('languages')->result_array();
		if($locale == ''){
			unset($languages[0]);
		}
		if(isset($module_name) && $module_name != ''){
			$this->db->where('package_name',$module_name);
		}
		$addons = $this->db->get('addons')->result_array();
		foreach ($addons as $key => $addons_arr) {
			if (strpos($addons_arr['package_name'], 'language') !== false) {
				unset($addons[$key]);
			}
		}
		if(!empty($addons)){
			///for insert and update keywords for addon
			foreach ($addons as $key => $addons_arr) {
				$this->insert_english_keyword($addons_arr['package_name']);
				if(!empty($languages)){
					foreach ($languages as $key => $value) {
						$new_final_arr_key= array (
							'en_En' => 'en_En',
							$value ['locale'] => $value ['locale'],
						);
						$file_name=FCPATH.'language/'.$addons_arr['package_name'].'_'.lcfirst($value ['locale']).'.csv';
						if (file_exists($file_name)) {
							$csv_tmp_data = $this->csvreader->parse_file($file_name, $new_final_arr_key, false);
							unset($csv_tmp_data[0],$csv_tmp_data[1]);
							$new_array=array();
							foreach ($csv_tmp_data as $key => $csv_data) {
								//$this->db->where('en_En',$csv_data['en_En']);
								//$this->db->set($value ['locale'],$csv_data[$value ['locale']]);
								//$this->db->update('translations');
								$data[]=array(
									'en_En'=>$csv_data['en_En'],
									$value ['locale'] => $csv_data[$value ['locale']],	
								);
							}
							$this->db->update_batch('translations',$data,'en_En');
							if($locale == ''){
								$this->generate_file($value ['locale']);
							}
						}
					}
				}
			}
		}
	}
	function insert_english_keyword($module_name){
		$new_final_arr_key= array (
			'en_En' => 'en_En',
		);
		$file_name=FCPATH.'language/'.$module_name.'_en_En'.'.csv';
		if (file_exists($file_name)) {  
			$csv_tmp_data = $this->csvreader->parse_file($file_name, $new_final_arr_key, false);
			unset($csv_tmp_data[0],$csv_tmp_data[1]);
			foreach ($csv_tmp_data as $key => $csv_data) {
				//$csv_data['module_name']=$module_name;
				//$insert_string=$this->db->insert_string('translations',$csv_data);
				//$insert_string = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_string);
				//$this->db->query($insert_string);
				$data[]=array(
					'module_name'=>$module_name,
					'en_En'=>$csv_data['en_En']
				
				);
			}  
			$this->db->custom_insert_ignore_batch('translations', $data);                     
		}
	}
}
