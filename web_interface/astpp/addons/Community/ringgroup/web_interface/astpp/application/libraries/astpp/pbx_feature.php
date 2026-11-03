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
class pbx_feature {
	function __construct($library_name = '') {
		$this->CI = & get_instance ();
		$this->CI->load->model ( "db_model" );
		// $this->CI->load->model ( "pbx_model" );
		$this->CI->load->library ( 'session' );
		// $this->CI->load->library("ivr/ivr_form");
		$this->CI->load->model('common_model');
	    $this->CI->load->model('Astpp_common');


	}
/* harsh s  08-oct   */ 
/*@Todo addded this string for default profile_data entry*/ 





/**    IVR Module Harsh s~       **/
	
	function ivr_list() {

        $this->CI->session->set_userdata('advance_search', 0);
		$data['username']     = $this->CI->session->userdata('user_name');
        $data['page_title']   = gettext('IVR List');
        $data['search_flag']  = true;
        $data['grid_fields']  = $this->CI->ivr_form->build_ivr_list_for_admin();
        $data["grid_buttons"] = $this->CI->ivr_form->build_grid_buttons();
        $data['form_search']  = $this->CI->form->build_serach_form($this->CI->ivr_form->get_ivr_search_form());

        return $data;
    }


    function ivr_list_json() {

        $json_data         = array();
        $count_all         = $this->CI->pbx_model->ivr_list(false);
        $paging_data       = $this->CI->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data         = $paging_data["json_paging"];
        $query             = $this->CI->pbx_model->ivr_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields       = json_decode($this->CI->ivr_form->build_ivr_list_for_admin());
        $json_data['rows'] = $this->CI->form->build_grid($query, $grid_fields);

		return $json_data;

    }

    function ivr_list_search() {

        $ajax_search = $this->CI->input->post('ajax_search', 0);
        if ($this->CI->input->post('advance_search', TRUE) == 1) {
            $this->CI->session->set_userdata('advance_search', $this->CI->input->post('advance_search'));
            $action = $this->CI->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            return $this->CI->session->set_userdata('ivr_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . ' ivr/ivr_list/');
        }
    }

    function ivr_list_clearsearchfilter() {
        $this->CI->session->set_userdata('advance_search', 0);
        $this->CI->session->set_userdata('ivr_list_search', "");
        return true;
    }


    function ivr_add($type = "") {

        $data['username']     = $this->CI->session->userdata('user_name');
        $data['flag']         = 'Create Ivr';
        $data['page_title']   = gettext('Create Ivr');
     //   echo "string";die();
        return $data;
    }



    function ivr_edit($edit_id) {


    	//print_r($edit_id);die();
      
        $data['page_title'] = gettext('Edit Ivr');
        $where = array('id' => $edit_id);
        $account = $this->CI->db_model->getSelect("*", " pbx_ivr_specification", $where);

        //print_r($account);die();


        $where_ivr_controls = array(
               'ivr_id' => $edit_id,
        );
        $ivr_controls_array = $this->CI->db_model->getSelect("*", "pbx_ivr_controls", $where_ivr_controls);
        $data['ivr_controls']=$ivr_controls_array->result_array();

//        print_r($data['ivr_controls']);die();



        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;

            //print_r($edit_data);die();
        }


        $data['id']              = $edit_data['id'];
        $data['name']            = $edit_data['name'];
        $data['status']          = $edit_data['status'];
        $data['description']     = $edit_data['description'];
        $data['direct_dial']     = $edit_data['direct_dial'];
        $data['accountid']       = $edit_data['accountid'];
        $data['reseller_id']     = $edit_data['reseller_id'];
        $data['didid']           = $edit_data['didid'];
        $data['greetingid']      = $edit_data['greetingid'];
        $data['invalid_msg_id']  = $edit_data['invalid_msg_id'];
        $data['calleridname']    = $edit_data['calleridname'];
        $data['calleridnumber']  = $edit_data['calleridnumber'];
        $data['timeout']         = $edit_data['timeout'];
        $data['invalid_retries'] = $edit_data['invalid_retries'];
        $data['ringbackid']      = $edit_data['ringbackid'];
        $data['count']           = astpp_count($data['ivr_controls']);
       
        // print_r($data);die();

        return $data;
    
    }

    function ivr_save($add_array) {

        if($add_array['id'] == '') {



	        $add_array['creation_date'] = gmdate('Y-m-d H:i:s');
	        $query = $this->CI->pbx_model->add_ivr($add_array);
	        return $this->CI->session->set_flashdata('astpp_errormsg', 'Ivr added successfully!');
        } else {


// echo "<pre>";
// print_r($add_array);die();

	        $add_array['last_modified_date'] = gmdate('Y-m-d H:i:s');
	        $query = $this->CI->pbx_model->edit_ivr($add_array,$add_array['id']);
	        return $this->CI->session->set_flashdata('astpp_errormsg', 'Ivr updated successfully!');
        }
    }

    function ivr_delete($ivr_id) {

	    $where = array("id" => $ivr_id);
        $this->CI->db->delete("pbx_ivr_specification",array("id"=>$ivr_id));
        $this->CI->session->set_flashdata('astpp_notification', 'Ivr removed successfully!');
        return true;
    }

    function ivr_selected_delete($ids) {

        $where = "id IN ($ids)";
        $this->CI->db->where($where);
        $this->CI->db->delete("pbx_ivr_specification");
       	return true;
    }


    function get_field_name($select, $table, $where) {
		if (is_array ( $where )) {
			$where = $where;
		} else {
			$where = array (
					"id" => $where 
			);
		}
		$field_name = $this->CI->db_model->getSelect ( $select, $table, $where );
		$field_name = $field_name->result ();
		if (isset ( $field_name ) && ! empty ( $field_name )) {
			return $field_name [0]->$select;
		} else {
			return "";
		}
	}


    function ivr_did_change($account_type,$did_id=''){

	    if($account_type!=''){

	        $whr = array("accountid" => $account_type);
	        $account = $this->CI->db_model->getSelect("number,id,accountid", "dids", $whr);
	       
	        if ($account->num_rows > 0) {

	            $account_data = $account->result_array();
	            $did_arr = array();

	            foreach ($account_data as  $value) {

	            	if(astpp_eq0($value['accountid'])){
	                        $admin_name= "Admin";
	                }

		            else{
		                $first_name = $this->get_field_name('first_name', 'accounts', array('id'=>$value['accountid']));
		                $last_name  = $this->get_field_name('last_name', 'accounts', array('id'=>$value['accountid']));
		                $admin_name = $first_name.' '.$last_name;
		            }
	                $did_arr[$value['id']] =  $value['number'];
	            }

	            $did_info = array("name" => "didid" ,"id" => "did", "class" => "col-md-2 form-control selectpicker did");

	            return form_dropdown_all($did_info, $did_arr,$did_id);
	        } else {
	            return '<select class="col-md-5 form-control did" id="did" name="didid"><option>--</option></select>';
	        }
	    } else {
	          return '<input name="didid" value="" size="20" maxlength="180" class="col-md-5 form-control did" id="did" type="text">';
	    }
	}

	function ivr_field_add($count,$val=""){

	       $data['count']    = $count;
	       $data['val']      = $val;
	       $data['rowcount'] = $data['count'];
	       //echo "string";die();

	  //     print_r($data);die();
	       return $data;
 	}


 	    function tone_array(){

 	    	//echo "string";die();
	$tone_array = array(
			'${au-ring}'=>'au-ring',
			'${be-ring}'=>'be-ring',
			'${bong-ring}'=>'bong-ring',
			'${ca-ring}'=>'ca-ring',
			'${cn-ring}'=>'cn-ring',
			'${cy-ring}'=>'cy-ring',
			'${cz-ring}'=>'cz-ring',
			'${de-ring}'=>'de-ring',
			'${dk-ring}'=>'dk-ring',
			'${dz-ring}'=>'dz-ring',
			'${eg-ring}'=>'eg-ring',
			'${fi-ring}'=>'fi-ring',
			'${fr-ring}'=>'fr-ring',
			'${hk-ring}'=>'hk-ring',
			'${hu-ring}'=>'hu-ring',
			'${il-ring}'=>'il-ring',
			'${in-ring}'=>'in-ring',
			'${it-ring}'=>'it-ring',
			'${jp-ring}'=>'jp-ring',
			'${ko-ring}'=>'ko-ring',
			'${pk-ring}'=>'pk-ring',
			'${pl-ring}'=>'pl-ring',
			'${pt-ring}'=>'pt-ring',
			'${ro-ring}'=>'ro-ring',
			'${rs-ring}'=>'rs-ring',
			'${ru-ring}'=>'ru-ring',
			'${sa-ring}'=>'sa-ring',
			'${tr-ring}'=>'tr-ring',
			'${uk-ring}'=>'uk-ring',
			'${us-ring}'=>'us-ring',
		);
	return $tone_array;
    }



    function build_dropdown_ivr() {
		$ringback_arr = array();
		$accountinfo = $this->CI->session->userdata('accountinfo'); 
			if($accountinfo['type'] == '-1') {
				
				$whr= array("reseller_id" => "0");
			}else if($accountinfo['type'] == '1') {
				$whr= array("reseller_id" => $accountinfo['id']);
			}else{
				$whr = array (
					"accountid" => $accountinfo['id'],
					"reseller_id" =>$accountinfo['reseller_id']              
				);
			}
			$account = $this->CI->db_model->getSelect("name,id", "pbx_ringgroup",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['ringgroup']['ringgroup_'.$value['id']] =  $value['name'];
				}
			}
		
		// if($accountinfo['type'] == '-1') {	      	
		// 	$whr= array("reseller_id" => "0");
			$account = $this->CI->db_model->getSelect("name,id", "pbx_conference_specification",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['conference']['conference_'.$value['id']] =  $value['name'];
				}
			}
		//}
		// if($accountinfo['type'] == '-1') {
		// 	$whr= array("reseller_id" => "0");
			$account = $this->CI->db_model->getSelect("name,id", "pbx_ivr_specification",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['Ivr']['ivr_'.$value['id']] =  $value['name'];
				}
			}
		//}
		// if($accountinfo['type'] == '-1') {
		// 	$whr= array("reseller_id" => "0");
			$account = $this->CI->db_model->getSelect("name,id", "pbx_queue",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['Queue']['queue'.$value['id']] =  $value['name'];
				}
			}
		//}
		// if($accountinfo['type'] == '-1') {
		// 	$whr= array("reseller_id" => "0");
			$account = $this->CI->db_model->getSelect("username,id,accountid", "sip_devices",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$get_domain = $this->CI->common->get_field_name('domain','domains',array('accountid'=>$value['accountid']));
					$ringback_arr['Extension']['extension_'.$value['id']] =  $value['username']." (".str_replace(':8017','',$get_domain).")";
				}
			}
		//}
		// if($accountinfo['type'] == '1') {
		// 	$whr= array("reseller_id" => $accountinfo['id']);
		// 	// print_r( '$whr'); die;
		// 	$account = $this->CI->db_model->getSelect("name,id", "pbx_ringgroup",$whr);
			
		// 	if ($account->num_rows > 0) {
		// 		$account_data=$account->result_array();
		// 		foreach ($account_data as $value) {
		// 			$ringback_arr['ringgroup']['ringgroup_'.$value['id']] =  $value['name'];

		// 		}
		// 	}
		// }
		// if($accountinfo['type'] == '1') {	      	
		// 	$whr= array("reseller_id" => $accountinfo['id']);
		// 	$account = $this->CI->db_model->getSelect("name,id", "pbx_conference_specification",$whr);
		// 	if ($account->num_rows > 0) {
		// 		$account_data=$account->result_array();
		// 		foreach ($account_data as $value) {
		// 			$ringback_arr['conference']['conference_'.$value['id']] =  $value['name'];

		// 		}
		// 	}
		// }
		// if($accountinfo['type'] == '1') {
		// 	$whr= array("reseller_id" => $accountinfo['id']);
		// 	$account = $this->CI->db_model->getSelect("name,id", "pbx_ivr_specification",$whr);
		// 	if ($account->num_rows > 0) {
		// 		$account_data=$account->result_array();
		// 		foreach ($account_data as $value) {
		// 			$ringback_arr['Ivr']['ivr_'.$value['id']] =  $value['name'];

		// 		}
		// 	}
		// }
		// if($accountinfo['type'] == '1') {
		// 	$whr= array("reseller_id" => $accountinfo['id']);
		// 	$account = $this->CI->db_model->getSelect("username,id,accountid", "sip_devices",$whr);
		// 	if ($account->num_rows > 0) {
		// 		$account_data=$account->result_array();
		// 		foreach ($account_data as $value) {
		// 			$get_domain = $this->CI->common->get_field_name('domain','domains',array('accountid'=>$value['accountid']));
		// 			$ringback_arr['Extension']['extension_'.$value['id']] =  $value['username']." (".str_replace(':8017','',$get_domain).")";
		// 			//echo "string";die();
		// 		}
		// 	}
		// }
		// if($accountinfo['type'] == '0') {
		// 	$ringback_arr = array();
		// 	$whr = array (
		// 			"accountid" => $accountinfo['id'],
		// 			"reseller_id" =>$accountinfo['reseller_id']              
		// 		);
		// 	$account = $this->CI->db_model->getSelect("name,id", "pbx_ringgroup",$whr);
		// 	if ($account->num_rows > 0) {
		// 		$account_data=$account->result_array();
		// 		foreach ($account_data as $value) {
		// 			$ringback_arr['ringgroup']['ringgroup_'.$value['id']] =  $value['name'];
		// 		}
		// 	}
		// }
		// if($accountinfo['type'] == '0') {	      	
		// 	$whr = array (
		// 			"accountid" => $accountinfo['id'],
		// 			"reseller_id" =>$accountinfo['reseller_id']              
		// 		);
		// 	$account = $this->CI->db_model->getSelect("name,id", "pbx_conference_specification",$whr);
		// 	if ($account->num_rows > 0) {
		// 		$account_data=$account->result_array();
		// 		foreach ($account_data as $value) {
		// 			$ringback_arr['conference']['conference_'.$value['id']] =  $value['name'];
		// 		}
		// 	}
		// }
		// if($accountinfo['type'] == '0') {
		// 	$whr = array (
		// 			"accountid" => $accountinfo['id'],
		// 			"reseller_id" =>$accountinfo['reseller_id']              
		// 		);
		// 	$account = $this->CI->db_model->getSelect("name,id", "pbx_ivr_specification",$whr);
		// 	if ($account->num_rows > 0) {
		// 		$account_data=$account->result_array();
		// 		foreach ($account_data as $value) {
		// 			$ringback_arr['Ivr']['ivr_'.$value['id']] =  $value['name'];
		// 		}
		// 	}
		// }
		// if($accountinfo['type'] == '0') {
		// 	$whr= array("accountid" => $accountinfo['id']);
		// 	$account = $this->CI->db_model->getSelect("username,id,accountid", "sip_devices",$whr);
		// 	if ($account->num_rows > 0) {
		// 		$account_data=$account->result_array();
		// 		foreach ($account_data as $value) {
		// 			$get_domain = $this->CI->common->get_field_name('domain','domains',array('accountid'=>$value['accountid']));
		// 			$ringback_arr['Extension']['extension_'.$value['id']] =  $value['username']." (".str_replace(':8017','',$get_domain).")";

		// 		}
		// 	}
		// }
		// echo "<pre>";
// print_r($ringback_arr);die;
		return $ringback_arr;
	}
	function build_dropdown_ivr_customer($account_id) {
		$ringback_arr = array();
		$accountinfo = $this->CI->session->userdata('accountinfo'); 
			
			if($accountinfo['type'] == '-1' || $accountinfo['type'] == '2' ) {	 
				$whr= array(
					// "reseller_id" => "0",
				"accountid"=>$account_id, "status"=>"0");
			}else if($accountinfo['type'] == '1') {	      	
				$whr= array("reseller_id" => $accountinfo['id'], "accountid" => $account_id, "status"=>"0");
			}else{
				$whr = array (
					"accountid" => $accountinfo['id'],
					"reseller_id" =>$accountinfo['reseller_id'],
					"status"=>"0"             
				);
			}
			$account = $this->CI->db_model->getSelect("username,id,accountid", "sip_devices",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$get_domain = $this->CI->common->get_field_name('domain','domains',array('accountid'=>$value['accountid']));
					$ringback_arr['Extension']['extension_'.$value['id']] =  $value['username']." (".str_replace(':8017','',$get_domain).")";
				}
			}
			
			$account = $this->CI->db_model->getSelect("username,id,accountid", "sip_devices",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$get_domain = $this->CI->common->get_field_name('domain','domains',array('accountid'=>$value['accountid']));
					$ringback_arr['voicemail']['voicemail_'.$value['id']] =  $value['username']." (".str_replace(':8017','',$get_domain).")";
				}
			}
			
			
			$account = $this->CI->db_model->getSelect("name,id", "pbx_ringgroup",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['ringgroup']['ringgroup_'.$value['id']] =  $value['name'];
				}
			}
		
			$account = $this->CI->db_model->getSelect("name,id", "pbx_conference_specification",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['conference']['conference_'.$value['id']] =  $value['name'];
				}
			}
		
			$account = $this->CI->db_model->getSelect("name,id", "pbx_ivr_specification",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['Ivr']['ivr_'.$value['id']] =  $value['name'];
				}
			}
			
			$account = $this->CI->db_model->getSelect("name,id", "`time_condition`",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['timecondition']['timecondition_'.$value['id']] =  $value['name'];
				}
			}
			//print_r($this->CI->db->last_query()); die;
			if($accountinfo['type'] == '-1') {
			$whr= array("reseller_id" => "0","account_id"=>$account_id, "status"=>"0");
			}else if($accountinfo['type'] == '1') {
				$whr= array("reseller_id" => $accountinfo['id'], "status"=>"0");
			}else{
				$whr = array (
					"account_id" => $accountinfo['id'],
					"reseller_id" =>$accountinfo['reseller_id'],
					"status"=>"0"              
				);
			}
			$account = $this->CI->db_model->getSelect("name,id", "pbx_queue",$whr);
			if ($account->num_rows > 0) {
				$account_data=$account->result_array();
				foreach ($account_data as $value) {
					$ringback_arr['Queue']['queue_'.$value['id']] =  $value['name'];
				}
			}
		
		$ringback_arr['PSTN']=array("pstn_1"=>"PSTN");
// 		echo "<pre>";
// print_r($ringback_arr);die;
		return $ringback_arr;
	}

	//Hiral
	function pbx_destination_name($call_type="",$extensions=""){
      
		if($call_type==7){
			$ringgroup_name = $this->get_field_name("name","pbx_ringgroup",array("id"=>$extensions));
			return $ringgroup_name;
		}

		if($call_type==8){
			$conference_name = $this->get_field_name("name","pbx_conference_specification",array("id"=>$extensions));
			return $conference_name;
		}

		if($call_type==9){
			$queue_name = $this->get_field_name("name","pbx_queue",array("id"=>$extensions));
			return $queue_name;
		}

		if($call_type==10){
			$ivr_name = $this->get_field_name("name","pbx_ivr_specification",array("id"=>$extensions));
			return $ivr_name;
		}

		if($call_type==11){
			$timecondition_name = $this->get_field_name("name","time_condition",array("id"=>$extensions));
			return $timecondition_name;
		}
    }
    //END

}
?> 
