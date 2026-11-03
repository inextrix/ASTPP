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
class Email_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_email_list($flag, $start = 0, $limit = 0)
    {
        $account_data = $this->session->userdata("accountinfo");

        $account_id = $account_data['id'];
        $account_email = $account_data['email'];
        $account_type = $account_data['type'];

        if (astpp_eq0($account_type)) {
            $this->db->where('accountid', $account_id);
        }
        if ($account_type == 1) {
            $this->db->where_in('reseller_id');
            $this->db->select('id');
            $email_address = $this->db->get('accounts');

            $email_address = $email_address->result_array();

            if (empty($email_address)) {
                $this->db->or_where('accountid', 0);
            } else {
                $this->db->where('reseller_id', $account_id);
            }
        }
        $this->db_model->build_search('email_search_list');
        if ($flag) {
            $query = $this->db_model->select("*", "mail_details", '', "id", "DESC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "mail_details", '');
        }
        return $query;
    }

    function add_email($add_array)
    {
        $this->db->insert("mail_details", $add_array);
        return true;
    }

    function remove_email($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("mail_details");
        return true;
    }

    function edit_email($data, $id)
    {
        $this->db->where("id", $id);
        $this->db->update("mail_details", $data);
    }

    function customer_get_email_list($flag, $accountid, $start = 0, $limit = 0)
    {
        $this->db->where('accountid', $accountid);
        if ($flag) {
            $query = $this->db_model->select("*", "mail_details", '', "id", "desc", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "mail_details", '');
        }

        return $query;
    }

}

