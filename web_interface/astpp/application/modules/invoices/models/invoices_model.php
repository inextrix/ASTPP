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
class Invoices_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_invoice_list($flag, $start = 100, $limit = 100)
    {
        $account_data = $this->session->userdata("accountinfo");
        $this->db_model->build_search('invoice_list_search');
        $where = '';
        if ($account_data['type'] == 1) {
            $where = array(
                "reseller_id" => $account_data['id']
            );
        }
        if ($flag) {
            $query = $this->db_model->select("*", "view_invoices", $where, "due_date", "DESC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "view_invoices", $where);
        }
        return $query;
    }

    function getCdrs_invoice($invoiceid)
    {
        $this->db->where('invoiceid', $invoiceid);
        $this->db->from('cdrs');
        $query = $this->db->get();
        return $query;
    }

    function get_account_including_closed($accountdata)
    {
        $q = "SELECT * FROM accounts WHERE number = '" . $this->db->escape_str($accountdata) . "'";
        $query = $this->db->query($q);
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row;
        }
        $q = "SELECT * FROM accounts WHERE accountid = '" . $this->db->escape_str($accountdata) . "'";
        $query = $this->db->query($q);
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row;
        }

        return NULL;
    }

    function get_user_invoice_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('invoice_list_search');
        $accountinfo = $this->session->userdata('accountinfo');
        $where = array(
            "accountid" => $accountinfo['id'],
            'confirm' => 1
        );
        if ($flag) {
            $query = $this->db_model->select("*", "invoices", $where, "", "", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "invoices", $where);
        }

        return $query;
    }

    function getinvoiceconf_list($flag, $start = 0, $limit = 0)
    {
        $where = array();
        $logintype = $this->session->userdata('logintype');

        if ($logintype == 1 || $logintype == 5) {

            $where = array(
                "accountid" => $this->session->userdata["accountinfo"]['id']
            );
        }
        $this->db_model->build_search('invoice_conf_search');
        if ($flag) {
            $query = $this->db_model->select("*", "invoice_conf", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "invoice_conf", $where);
        }
        return $query;
    }

    function get_invoiceconf($edit_id)
    {
        $return_array = array();
        $logintype = $this->session->userdata('logintype');
        if ($logintype == 1 || $logintype == 5) {

            $where = array(
                "accountid" => $this->session->userdata["accountinfo"]['id']
            );
        } else {
            if ($logintype == - 1 || $logintype == 2) {
                $accountid = '1';
            }
            $where = array(
                'id' => $edit_id
            );
        }
        $query = $this->db_model->getSelect("*", "invoice_conf", $where);
        foreach ($query->result_array() as $key => $value) {
            $return_array = $value;
        }
        return $return_array;
    }

    function check_invoiceconf_exist($accountid)
    {
        $count = $this->db_model->countQuery("*", "invoice_conf", array(
            "accountid" => $accountid
        ));
        return $count;
    }

    function save_invoiceconf($post_array)
    {
        $logintype = $this->session->userdata('logintype');
        $where_arr = array(
            'id' => $post_array['id']
        );
        unset($post_array['action']);
        if ($post_array['id'] != "") {
            $this->db->where($where_arr);
            unset($post_array['logo_main']);
            if ($logintype == 1) {
                unset($post_array['accountid']);
            }
            $this->db->update('invoice_conf', $post_array);
        } else {
            unset($post_array['logo_main']);

            $accountdata = $this->session->userdata('accountinfo');

            $post_array['accountid'] = $post_array['reseller_id'];
            $this->db->select('id');
            $this->db->where_in('accountid', $post_array['accountid']);
            $get_id = $this->db->get('invoice_conf')->row_array();
            unset($post_array['reseller_id']);
            $q = "SELECT reseller_id FROM accounts WHERE id = '" . $post_array['accountid'] . "'";
            $query = (array) $this->db->query($q)->first_row();
            $post_array['reseller_id'] = $query['reseller_id'] ? $query['reseller_id'] : 0;
            if (isset($get_id['id']) && $get_id['id'] != '') {
                unset($post_array['id']);
                $where_arr = array(
                    'id' => $get_id['id']
                );
                $this->db->where($where_arr);
                $this->db->update('invoice_conf', $post_array);
                return true;
            } else {

                $this->db->insert('invoice_conf', $post_array);
                return true;
            }
        }
    }
}
