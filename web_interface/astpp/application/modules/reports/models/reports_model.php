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
class Reports_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function getcustomer_cdrs($flag, $start, $limit, $export = false)
    {
        $this->db_model->build_search('customer_cdr_list_search');
        $account_data = $this->session->userdata("accountinfo");
        // Ashish ASTPPCOM-825
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $where['reseller_id'] = $account_data['type'] == 1 ? $account_data['id'] : 0;
        }
        // ASTPPCOM-825 end
        $table_name = 'cdrs';
        if ($this->session->userdata('advance_search_date') == 1) {
            // ASTPPCOM-891 Ashish start
            $timezone = $this->common->get_current_login_type_timezone();
            $where['callstart >= '] =$this->common->convert_GMT_new ( $timezone . " 00:00:00");
            $where['callstart <='] =$this->common->convert_GMT_new ( $timezone . " 23:59:59");
            // ASTPPENT-891 Ashish end
        } else {
            if ($this->session->userdata('customerReport_cdrs_year') != '' and $this->session->userdata('customerReport_cdrs_year') != '0') {
                $table_name = $this->session->userdata('customerReport_cdrs_year');
            }
        }

        $types = array(
            '0',
            '3'
        );
        $this->db->where_in('type', $types);
        if (isset($where) && $where != "") {
            $this->db->where($where);
        }
        if (isset($_GET['sortname']) && $_GET['sortname'] != 'undefined') {
            $this->db->order_by($_GET['sortname'], ($_GET['sortorder'] == 'undefined') ? 'desc' : $_GET['sortorder']);
        } else {
            $this->db->order_by("callstart desc");
        }
        if ($flag) {
            if (! $export)
                $this->db->limit($limit, $start);
            $this->db->select('callstart,is_recording,sip_user,call_direction,callerid,callednum,pattern,notes,billseconds,disposition,debit,cost,accountid,pricelist_id,calltype,trunk_id,uniqueid');
        } else {
            $this->db->select('count(*) as count,sum(billseconds) as billseconds,sum(debit) as total_debit,SUM(CASE WHEN calltype = "FREE" THEN debit ELSE 0 END) AS free_debit,sum(cost) as total_cost,group_concat(distinct(pricelist_id)) as pricelist_ids,group_concat(distinct(trunk_id)) as trunk_ids,group_concat(distinct(accountid)) as accounts_ids');
        }
        $result = $this->db->get($table_name);
        // Kinjal ASTPPCOM-978 Start
        $customer_cdr_list_search = $this->session->userdata('customer_cdr_list_search');
        if(!empty($customer_cdr_list_search)){
            $select_value = array('select_table' => 'cdrs','module' => 'cdrs');
            $select_value['select_where']['type IN'] = array('0,3');
            foreach ($customer_cdr_list_search as $key => $value) {
                if(is_array($value) && isset($value[$key]) && $value[$key] != ''){
                    $type = isset($value[$key."-integer"]) ? (string)2 : (string)1;
                    $operator = isset($value[$key."-integer"]) ? $value[$key."-integer"] : $value[$key."-string"];
                    $select_value['select_where'][$key]=$value[$key]."_".$type."_".$operator;
                    if(isset($value['new_billseconds']) && $value['new_billseconds'] != '' ){
                        $select_value['select_where']['new_billseconds'] = $value['new_billseconds']."_".$type."_".$operator;
                    }
                }elseif(!is_array($value) && $value!=''){
                    $select_value['select_where'][$key]=$value;
                }
            }
            $select_value['select_where']['array_params'] = array('country_id' => 'countrycode,country','trunk_id'=>'trunks,name','pricelist_id' => 'pricelists,name','is_recording' => ',Yes,No');
            $select_value['search_field_array'] = array('Caller ID' => 'callerid','Called Number' =>'callednum','Code' => 'pattern','Destination' =>'notes','Duration' => 'billseconds','Duration' => 'new_billseconds','Debit' => 'debit','Cost' => 'cost','Reseller' => 'reseller_id,accounts,reseller_id','Rate Group' => 'pricelist_id,pricelists,id,name','Account' => 'accountid,accounts,id','Country' => 'country_id,countrycode,id,country','Disposition[Q.850]' => 'disposition,','Trunk' => 'trunk_id,trunks,id,name','Call Type' => 'calltype,','Select Year' => 'cdrs_year,','Direction' => 'call_direction,','SIP User' => 'sip_user','Display records in' => 'search_in,');
            $select_values['reports_key_automated'] = $select_value;
            $encode = json_encode($select_values);
            $this->session->set_userdata('reports_list_automated', $encode);
        }
        // Kinjal ASTPPCOM-978 END
        return $result;
    }

    function getreseller_cdrs($flag, $start, $limit, $export = false)
    {
        $this->db_model->build_search('reseller_cdr_list_search');
        $account_data = $this->session->userdata("accountinfo");
        $where = array();
        $table_name = 'reseller_cdrs';
        if ($this->session->userdata('advance_search') != 1) {
            if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
                $account_data = $this->session->userdata("accountinfo");
                $where = array(
                    "reseller_id" => $account_data['id'],
                    "accountid <>" => $account_data['id']
                );
                if ($this->session->userdata('advance_search_date') == 1) {
                    // ASTPPENT-891 Ashish start
                    $timezone = $this->common->get_current_login_type_timezone();
                    $where['callstart >= '] =$this->common->convert_GMT_new ( $timezone . " 00:00:00");
                    $where['callstart <='] =$this->common->convert_GMT_new ( $timezone . " 23:59:59");
                    // ASTPPENT-891 Ashish end
                }
            } else {
                if ($this->session->userdata('advance_search_date') == 1) {
                    $where = array(
                        // ASTPPENT-891 Ashish start
                        'callstart >= ' =>$this->common->convert_GMT_new ( date("Y-m-d") . " 00:00:00"),
                        'callstart <=' => $this->common->convert_GMT_new (date("Y-m-d") . " 23:59:59")
                        // ASTPPENT-891 Ashish end
                    );
                }
            }
        } else {
            if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
                $account_data = $this->session->userdata("accountinfo");
                $where = array(
                    "reseller_id" => $account_data['id'],
                    "accountid <>" => $account_data['id']
                );
            } else {
                $where = array();
            }
            if ($this->session->userdata('resellerreport_cdrs_year') != '' and $this->session->userdata('resellerreport_cdrs_year') != '0') {
                $table_name = $this->session->userdata('resellerreport_cdrs_year');
            }
        }

        $this->db->where($where);
        if (isset($_GET['sortname']) && $_GET['sortname'] != 'undefined') {
            $this->db->order_by($_GET['sortname'], ($_GET['sortorder'] == 'undefined') ? 'desc' : $_GET['sortorder']);
        } else {
            $this->db->order_by("callstart desc");
        }
        if ($flag) {
            if (! $export)
                $this->db->limit($limit, $start);
            $this->db->select('callstart,call_direction,callerid,country_id,callednum,pattern,notes,billseconds,disposition,debit,cost,accountid,pricelist_id,calltype,trunk_id');
        } else {
            $this->db->select('count(*) as count,sum(billseconds) as billseconds,sum(debit) as total_debit,SUM(CASE WHEN calltype = "FREE" THEN debit ELSE 0 END) AS free_debit,sum(cost) as total_cost,group_concat(distinct(pricelist_id)) as pricelist_ids,group_concat(distinct(trunk_id)) as trunk_ids');
        }
        $result = $this->db->get($table_name);
        return $result;
    }

    function getprovider_cdrs($flag, $start, $limit, $export = false)
    {
        $this->db_model->build_search('provider_cdr_list_search');
        $account_data = $this->session->userdata("accountinfo");
        $where = array();
        $table_name = 'cdrs';
        if ($account_data['type'] == 3) {
            $where['provider_id'] = $account_data['id'];
        }

        if ($this->session->userdata('advance_search') != 1) {
            if ($this->session->userdata('advance_search_date') == 1) {
                // ASTPPENT-891 Ashish start
                $timezone = $this->common->get_current_login_type_timezone();
                $where['callstart >= '] =$this->common->convert_GMT_new ( $timezone . " 00:00:00");
                $where['callstart <='] =$this->common->convert_GMT_new ( $timezone . " 23:59:59");
                // ASTPPENT-891 Ashish end
            }
        } else {
            if ($this->session->userdata('providerreport_cdrs_year') != '' and $this->session->userdata('providerreport_cdrs_year') != '0') {
                $table_name = $this->session->userdata('providerreport_cdrs_year');
            }
        }
        $this->db->where('trunk_id !=', '');
        $this->db->where($where);
        if (isset($_GET['sortname']) && $_GET['sortname'] != 'undefined') {
            $this->db->order_by($_GET['sortname'], ($_GET['sortorder'] == 'undefined') ? 'desc' : $_GET['sortorder']);
        } else {
            $this->db->order_by("callstart desc");
        }
        if ($flag) {
            if (! $export)
                $this->db->limit($limit, $start);
            $this->db->select('calltype,callstart,sip_user,call_direction,callerid,callednum,pattern,notes,billseconds,provider_call_cost,disposition,provider_id,cost');
        } else {
            $this->db->select('count(*) as count,sum(billseconds) as billseconds,sum(cost) as total_cost');
        }
        $result = $this->db->get($table_name);
        return $result;
    }

    function users_cdrs_list($flag, $accountid, $entity_type, $start, $limit)
    {
        $where = "callstart >= '" . date('Y-m-d 00:00:00') . "' AND callstart <='" . date('Y-m-d 23:59:59') . "' AND ";
        $account_type = $entity_type == 'provider' ? 'provider_id' : 'accountid';
        $where .= "accountid = '" . $accountid . "' ";
        $table = $entity_type == 'reseller' ? 'reseller_cdrs' : 'cdrs';
        if ($flag) {
            $query = $this->db_model->select("*", $table, $where, "callstart", "DESC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", $table, $where);
        }
        return $query;
    }

    function getuser_refill_list($flag, $start, $limit)
    {
        $this->db_model->build_search('cdr_refill_search');
        $account_data = $this->session->userdata("accountinfo");
        $this->db_model->build_search('customer_cdr_list_search');
        $where = array(
            "accountid" => $account_data["id"]
        );
        if ($flag) {
            $query = $this->db_model->select("*", "payments", $where, "payment_date", "DESC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "payments", $where);
        }

        return $query;
    }

    function get_refill_list($flag, $start, $limit, $export = false)
    {
        $this->db_model->build_search('cdr_refill_search', 'payment_transaction.');
        $accountinfo = $this->session->userdata('accountinfo');
        $where_date = array();
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $accountinfo = $this->session->userdata['accountinfo'];
            $this->db->where("payment_transaction.reseller_id", $accountinfo['id']);
        } else {
            if ($this->session->userdata('cdr_refill_search') != '' and $this->session->userdata('cdr_refill_search') != '0') {
                $refill_search = $this->session->userdata('cdr_refill_search');

                if (isset($refill_search) && $refill_search['date'][0] != "") {
                    $where_date['date >='] = $refill_search['date'][0];
                }
                if (isset($refill_search) && $refill_search['date'][1] != "") {
                    $where_date['date <='] = $refill_search['date'][1];
                }
            } else {

                $where_date['date >='] = date('Y-m-d 00:00:00');
                $where_date['date <='] = date('Y-m-d 23:59:59');
            }
        }
        $where = "(invoice_details.charge_type= 'Voucher' OR invoice_details.charge_type='REFILL')";
        if (isset($where_date)) {
            $this->db->where($where_date);
        }
        $this->db->where("invoice_details.is_tax", 0);
        if ($flag) {
            $this->db->order_by("payment_transaction.date", "desc");
            if ($export)
                $query = $this->db_model->getJionQuery('payment_transaction', 'payment_transaction.accountid,payment_transaction.amount,payment_transaction.date,payment_transaction.payment_method,
payment_transaction.transaction_details,payment_transaction.customer_ip,payment_transaction.actual_amount,
payment_transaction.reseller_id,payment_transaction.transaction_id,
invoice_details.charge_type,invoice_details.credit,invoice_details.description,invoice_details.debit', $where, 'invoice_details', 'payment_transaction.id=invoice_details.payment_id', 'inner', '', '', '', '');
            else

                $query = $this->db_model->getJionQuery('payment_transaction', 'payment_transaction.accountid,payment_transaction.amount,payment_transaction.date,payment_transaction.payment_method,payment_transaction.actual_amount,
payment_transaction.transaction_details,payment_transaction.customer_ip,payment_transaction.reseller_id,
payment_transaction.transaction_id,invoice_details.charge_type,invoice_details.description,invoice_details.credit,invoice_details.debit', $where, 'invoice_details', 'payment_transaction.id=invoice_details.payment_id', 'inner', $limit, $start, '', '');
        } else {
            $this->db->order_by("payment_transaction.date", "desc");
            $query = $this->db_model->getJionQueryCount('payment_transaction', 'payment_transaction.accountid,payment_transaction.amount,payment_transaction.date,payment_transaction.payment_method,
payment_transaction.transaction_details,payment_transaction.customer_ip,payment_transaction.reseller_id,payment_transaction.actual_amount,
payment_transaction.transaction_id,invoice_details.charge_type,invoice_details.description,invoice_details.credit,invoice_details.debit', $where, 'invoice_details', 'payment_transaction.id=invoice_details.payment_id', 'inner', '', '', '', '');
        }
        return $query;
    }

    function getreseller_commission_list($flag, $start, $limit)
    {
        $this->db_model->build_search('reseller_commission_search');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $accountinfo = $this->session->userdata['accountinfo'];
            $reseller_id = $accountinfo["id"];
        } else {
            $reseller_id = "0";
        }
        if ($flag) {
            $query = $this->db_model->select_by_in("*", "commission", "", "date", "DESC", $limit, $start, "", "reseller_id", $reseller_id);
        } else {
            $query = $this->db_model->countQuery_by_in("*", "commission", "", "reseller_id", $reseller_id);
        }

        return $query;
    }


    function get_customer_refillreport($flag, $accountid, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('cdr_refill_search', 'payment_transaction.');
        $accountinfo = $this->session->userdata('accountinfo');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $accountinfo = $this->session->userdata['accountinfo'];
        } else {
            if ($this->session->userdata('cdr_refill_search') != '' and $this->session->userdata('cdr_refill_search') != '0') {
                $refill_search = $this->session->userdata('cdr_refill_search');
                if (isset($refill_search) && $refill_search['date'][0] != "") {
                    $where['date >='] = $refill_search['date'][0];
                }
                if (isset($refill_search) && $refill_search['date'][1] != "") {
                    $where['date <='] = $refill_search['date'][1];
                }
            } else {
                $where['date >='] = date('Y-m-d 00:00:00');
                $where['date <='] = date('Y-m-d 23:59:59');
            }
        }
        $where = "(invoice_details.charge_type= 'Voucher' OR invoice_details.charge_type='REFILL ')";
        $this->db->where("payment_transaction.accountid", $accountid);

        if ($flag) {
            $this->db->order_by("payment_transaction.date", "desc");
            $query = $this->db_model->getJionQuery('payment_transaction', 'payment_transaction.accountid,payment_transaction.amount,payment_transaction.date,payment_transaction.payment_method,payment_transaction.actual_amount,
payment_transaction.transaction_details,payment_transaction.customer_ip,payment_transaction.reseller_id,
payment_transaction.transaction_id,invoice_details.charge_type,invoice_details.description,invoice_details.credit,invoice_details.debit', $where, 'invoice_details', 'payment_transaction.id=invoice_details.payment_id', 'inner', $limit, $start, '', '');
        } else {
            $this->db->order_by("payment_transaction.date", "desc");
            $query = $this->db_model->getJionQueryCount('payment_transaction', 'payment_transaction.accountid,payment_transaction.amount,payment_transaction.date,payment_transaction.payment_method,
payment_transaction.transaction_details,payment_transaction.customer_ip,payment_transaction.reseller_id,payment_transaction.actual_amount,
payment_transaction.transaction_id,invoice_details.charge_type,invoice_details.description,invoice_details.credit,invoice_details.debit', $where, 'invoice_details', 'payment_transaction.id=invoice_details.payment_id', 'inner', '', '', '', '');
        }
        return $query;
    }

    function getcommissionreports_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('commissions_report_list_search');
        $accountinfo = $this->session->userdata('accountinfo');

        $where = array();
        if ($accountinfo['type'] == 1) {
            $where['reseller_id'] = $accountinfo['id'];
        }

        if ($flag) {
            $query = $this->db_model->select("*,order_id as orderid", "commission", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*,order_id as orderid", "commission", $where);
        }
        return $query;
    }
}
