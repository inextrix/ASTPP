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
#[\AllowDynamicProperties]
class Summary_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_resellersummary_report_list($flag, $start, $limit, $group_by, $select, $order, $export = false)
    {
        $this->db_model->build_search('summary_reseller_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5 ? $this->session->userdata['accountinfo']['id'] : 0;
        $table_name = 'reseller_cdrs';
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            if ($this->session->userdata('advance_search') != 1) {

                $where = array(
                    'reseller_id' => $reseller_id,
                    'callstart >= ' =>$this->common->convert_GMT_new ( date('Y-m-d') . " 00:00:01"),
                    'callstart <=' =>$this->common->convert_GMT_new ( date("Y-m-d") . " 23:59:59")
                );
            } else {

                $where = array(
                    "reseller_id" => $reseller_id
                );
            }
        } else {
            if ($this->session->userdata('advance_search') != 1) {
                $where['callstart >='] =$this->common->convert_GMT_new ( date('Y-m-d') . " 00:00:00");
                $where['callstart <='] =$this->common->convert_GMT_new ( date('Y-m-d') . " 23:59:59");
            } else {

                if ($this->session->userdata('reseller_cdrs_year') != '' and $this->session->userdata('reseller_cdrs_year') != '0') {
                    $table_name = $this->session->userdata('reseller_cdrs_year');
                }
            }
        }
        if (isset($where) && $where != "") {
            $this->db->where($where);
        }
        if (! empty($group_by)) {
            $this->db->_protect_identifiers = false;
            $this->db->group_by($group_by, false);
            $this->db->_protect_identifiers = true;
        }
        if ($flag) {
            $this->db->select($select . ",COUNT(*) AS attempts, AVG(billseconds) AS acd,MAX(billseconds) AS mcd,SUM(billseconds) AS duration,SUM(CASE WHEN calltype !='free' THEN billseconds ELSE 0 END) as billable,SUM(CASE WHEN billseconds > 0 THEN 1 ELSE 0 END) as completed,SUM(debit) AS debit,SUM(cost) AS cost", false);
            $this->db->order_by($order, "ASC");
            if (! $export && astpp_gt($limit, 0)) {
                $this->db->limit($limit, $start);
            }
            $this->db->from($table_name);
            $result = $this->db->get();
        } else {
            $result = $this->db_model->getSelect("count(*) as total_count", $table_name, '');
            if ($result->num_rows() > 0) {
                return $result->num_rows();
            } else {
                return 0;
            }
        }
        return $result;
    }

    function get_providersummary_report_list($flag, $start, $limit, $group_by, $select, $order, $export = false)
    {
        $this->db_model->build_search('summary_provider_search');
        $where['provider_id >'] = 0;
        $table_name = 'cdrs';
        if ($this->session->userdata('advance_search') != 1) {
            $where['callstart >='] =$this->common->convert_GMT_new ( date('Y-m-d') . " 00:00:00");
            $where['callstart <='] =$this->common->convert_GMT_new (date('Y-m-d') . " 23:59:59");
        } else {
            if ($this->session->userdata('provider_cdrs_year') != '' and $this->session->userdata('provider_cdrs_year') != '0') {
                $table_name = $this->session->userdata('provider_cdrs_year');
            }
        }
        $this->db->where($where);
        if (! empty($group_by)) {
            $this->db->_protect_identifiers = false;
            $this->db->group_by($group_by, false);
            $this->db->_protect_identifiers = true;
        }
        if ($flag) {
            $this->db->select($select . ",COUNT(*) AS attempts, AVG(billseconds) AS acd,MAX(billseconds) AS mcd,SUM(billseconds) AS duration,SUM(CASE WHEN calltype !='free' THEN billseconds ELSE 0 END) as billable,SUM(CASE WHEN billseconds > 0 THEN 1 ELSE 0 END) as completed,SUM(cost) AS cost", false);

            $this->db->order_by($order, "ASC");
            if (! $export && astpp_gt($limit, 0)) {
                $this->db->limit($limit, $start);
            }

            $this->db->from($table_name);
            $result = $this->db->get();
        } else {

            $result = $this->db_model->getSelect("count(*) as total_count", $table_name, '');
            if ($result->num_rows() > 0) {
                return $result->num_rows();
            } else {
                return 0;
            }
        }
        return $result;
    }

    function get_customersummary_report_list($flag, $start, $limit, $group_by, $select, $order, $export)
    {
        $this->db_model->build_search('summary_customer_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5 ? $this->session->userdata['accountinfo']['id'] : 0;

        $table_name = 'cdrs';
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            if ($this->session->userdata('advance_search') != 1) {
                $where = array(
                    'reseller_id' => $reseller_id,
                   'callstart >= ' =>$this->common->convert_GMT_new ( date('Y-m-d') . " 00:00:01"),
                    'callstart <=' =>$this->common->convert_GMT_new ( date("Y-m-d") . " 23:59:59"),
                    "type" => 0
                );
            } else {
                $where = array(
                    "reseller_id" => $reseller_id,
                    "type" => 0
                );
            }
        } else {
            if ($this->session->userdata('advance_search') != 1) {
                $where['callstart >='] =$this->common->convert_GMT_new ( date('Y-m-d') . " 00:00:00");
                $where['callstart <='] =$this->common->convert_GMT_new ( date('Y-m-d') . " 23:59:59");
            } else {
                if ($this->session->userdata('customer_cdrs_year') != '' and $this->session->userdata('customer_cdrs_year') != '0') {
                    $table_name = $this->session->userdata('customer_cdrs_year');
                }
            }
            $where['type'] = 0;
        }
        if (isset($where) && $where != "") {
            $this->db->where($where);
        }
        $types = array(
            '0'
        );
        $this->db->where_in('type', $types);
        if (! empty($group_by)) {
            $this->db->_protect_identifiers = false;
            $this->db->group_by($group_by, false);
            $this->db->_protect_identifiers = true;
        }
        if ($flag) {
            $this->db->select($select . ",COUNT(*) AS attempts, AVG(billseconds) AS acd,MAX(billseconds) AS mcd,SUM(billseconds) AS duration,SUM(CASE WHEN calltype !='free' THEN billseconds ELSE 0 END) as billable,SUM(CASE WHEN billseconds > 0 THEN 1 ELSE 0 END) as completed,SUM(debit) AS debit,SUM(cost) AS cost", false);
            $this->db->order_by($order, "ASC");
            if (! $export && astpp_gt($limit, 0)) {
                $this->db->limit($limit, $start);
            }

            $this->db->from($table_name);
            $result = $this->db->get();
        } else {

            $result = $this->db_model->getSelect("count(*) as total_count", $table_name, '');
            if ($result->num_rows() > 0) {
                return $result->num_rows();
            } else {
                return 0;
            }
        }

        return $result;
    }

  function get_productsummary_report_list($flag, $start, $limit, $group_by, $select, $order, $export)
    {
        $this->db_model->build_search('summary_product_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $table_name = 'order_items';
        $join_table = 'orders';
        $logintype = $this->session->userdata('logintype');
        $product_summary_search = $this->session->userdata('productsummary_reports_search');

	$reseller_id = $this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5 ? $this->session->userdata['accountinfo']['id'] : 0;
        if (! empty($group_by)) {
            $this->db->_protect_identifiers = false;
            $this->db->group_by($group_by, false);
            $this->db->_protect_identifiers = true;
        }
	// if($reseller_id > 0){
		$this->db->where('orders.reseller_id',$reseller_id);  
	// }

	   if($this->session->userdata('advance_search') != 1 && isset($product_summary_search['order_items.accountid']) && $product_summary_search['order_items.accountid'] != ''){
            $this->db->where('orders.accountid',$product_summary_search['order_items.accountid']);         
            $where = array(    
                'orders.order_date >= ' => $this->common->convert_GMT_new (date('Y-m-d') . " 00:00:01"),
                'orders.order_date <=' => $this->common->convert_GMT_new (date("Y-m-d") . " 23:59:59")      
            );
    	}else{ 
    		$where = array();
    	}
	if((isset($product_summary_search) && $product_summary_search !="" )){
		$this->db->where("product_category",$product_summary_search['product_category']);
	}else{
		$this->db->where("product_category",1);
	}
	$this->db->where($where);
        $this->db->select($select . ",orders.id,orders.payment_status,orders.order_date,order_items.product_category,order_items.product_id,order_items.product_id as productid,order_items.order_id,sum(order_items.quantity) as quantity,sum(order_items.price) as price,sum(setup_fee) as setup_fee,sum(order_items.free_minutes) as free_minutes,order_items.billing_type,sum(order_items.billing_days) as billing_days,order_items.accountid", false);

        $this->db->order_by($order, "ASC");
        if (! $export && astpp_gt($limit, 0)) {
            $this->db->limit($limit, $start);
        }
        $this->db->from($table_name);
        $this->db->join($join_table, 'order_items.order_id = orders.id');
        $query = $this->db->get();
	//echo $this->db->last_query(); exit;
        if ($flag) {
            return $query;
        } else {
            return $query->num_rows();
        }
    }

}
