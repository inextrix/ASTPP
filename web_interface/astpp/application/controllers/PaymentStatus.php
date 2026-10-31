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
class PaymentStatus extends CI_Controller {
	function __construct() {
		parent::__construct ();
		// Cron controller: only reachable from this server (CLI, or wget from cron_settings)
		if (! astpp_internal_request ()) {
			show_404 ();
		}
		$this->load->model ( "db_model" );
		$this->load->library ( "astpp/common" );
	}
	function Index() { 
		 $this->db->where("orders.order_date <=",date("Y-m-d H:i:s",strtotime("-30 minutes")));	
		 $this->db->where("orders.payment_status",'PENDING');		
		 $order_data = $this->db_model->getJionQuery('orders', 'orders.order_date,orders.id,orders.order_id,orders.payment_status,order_items.billing_date',"",'order_items','orders.id=order_items.order_id','inner','', '','','');
		if($order_data->num_rows > 0 ){
		   	$order_info = $order_data->result_array();
			if(!empty($order_info)){
				foreach($order_info as $key => $order){
					$this->db->where("id",$order['id']);
					$this->db->update("orders",array("payment_status"=>"FAIL"),"");
					$update_order_arr = array("is_terminated"=>'1',
							      "termination_date"=> gmdate("Y-m-d H:i:s"),
							      "termination_note" => "Product has been terminated"
					);
					$this->db->where("order_id",$order['id']);
					$this->db->update("order_items",$update_order_arr,"");
				} 
			}
		}
		exit ();
	}
} 
