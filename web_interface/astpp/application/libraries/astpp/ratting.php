<?php
###########################################################################
# ASTPP - Open Source Voip Billing
# Copyright (C) 2004, Aleph Communications
#
# Contributor(s)
# "iNextrix Technologies Pvt. Ltd - <astpp@inextrix.com>"
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details..
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>
############################################################################

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Ratting {

    protected $CI; 
    protected $fields = array();  
    protected $form_title = 'Form';
    protected $form_id = 'form';
    protected $form_action = '';
    protected $form_class = '';
    protected $hidden = array();
    protected $multipart = FALSE; 
    protected $submit_button = 'Submit';
    protected $after_button = '';
    protected $rules = array(); 

    function __construct($library_name = '') {

        $this->CI = & get_instance();
        $this->CI->load->library("timezone");
        $this->CI->load->model('db_model');
        $this->CI->load->library('email');
        $this->CI->load->library('session');
    }

    function ratting_list($data,$account_data){
		$account_id =$data['accountid'];
		$fax_num =$data['number'];
		$file_type=$data['type'];
		$file_name=$data['text'];
		$account_details = $this->CI->db_model->getSelect("*", "accounts", array("id" => $account_id));
		$account_details = $account_details->result_array();
		if(!$account_details[0]['pricelist_id'] == ''){
			$rate_group =$account_details[0]['pricelist_id'];
			$balance =$account_details[0]['balance'];
		if($account_data['type']==1 && $balance > $account_data['balance']){
			$this->CI->session->set_flashdata('astpp_notification', 'Reseller balance is low!');
			redirect(base_url() . 'web2fax/web2fax_list/');
			exit;
		}	
		if($balance <= 0){
			$this->CI->session->set_flashdata('astpp_notification', 'Insuficient belance!');
			redirect(base_url() . 'web2fax/web2fax_list/');
			exit;
		}

		$origination_details = $this->CI->db_model->getSelect("*", "routes", array("pricelist_id" => $rate_group));
		$origination_details = $origination_details->result_array();
		$origination_cost = $origination_details[0]['cost'];
		$origination_trunk = $origination_details[0]['trunk_id'];
		$termination_details = $this->CI->db_model->getSelect("*", "outbound_routes", array("trunk_id" => $origination_trunk));
		$termination_details = $termination_details->result_array();
		$termination_cost = $termination_details[0]['cost'];
		
		if($termination_cost >= $origination_cost){
			$this->CI->session->set_flashdata('astpp_notification', 'Cost not correct!');
			redirect(base_url() . 'web2fax/web2fax_list/');
			exit;
		}
		echo "Fax number :"; print_r($fax_num); echo "<br>"; 	
		echo "Balance :"; print_r($balance); echo "<br>"; 	
		echo "Origination Charge:"; print_r($origination_cost); echo "<br>"; 	
		echo "termination Charge:"; print_r($termination_cost); echo "<br>"; 	
		echo "Rate group :"; print_r($rate_group); echo "<br>"; 	
		exit;
	}
	else{
		$this->CI->session->set_flashdata('astpp_notification', 'Rategroup not found!');
		redirect(base_url() . 'web2fax/web2fax_list/');
		exit;
	}
	return true;
  }
   
     function get_prefix_destination($destination){
	$field = "pattern";
    	$max_len_prefix  = strlen($destination);
	$number_prefix = '(';
	while ($max_len_prefix  > 0){
		$number_prefix .= "$field='^".substr($destination,0,$max_len_prefix).".*' OR ";
		$max_len_prefix--;
	}
    	$number_prefix .= "$field='^defaultprefix.*')";
	return $number_prefix;
     }
   
   
   function ratting_list_customer($data,$account_arr){
       $account_id =$account_arr['pricelist_id'];
       $fax_num =$data['number'];
       $file_type=$data['type'];
       $file_name=$data['text'];
 	$rate_group =$account_arr['pricelist_id'];
	$balance =$account_arr['balance'];
	if($balance <= 0)
	{

	 $this->CI->session->set_flashdata('astpp_notification', 'Insuficient belance!');
        $logintype = $this->CI->session->userdata('logintype');	 
		if($logintype == 0){
			redirect(base_url() . 'user/user_web2fax_list/');

		}else{
			redirect(base_url() . 'web2fax/web2fax_list/');
		}	
	}
	$number_prefix = $this->get_prefix_destination($data['number']);
	$this->CI->db->where($number_prefix);
	$origination_details = $this->CI->db_model->getSelect("*", "routes", array("pricelist_id" => $rate_group));
        $origination_details = $origination_details->result_array();


	return true;
   }
}
?>
