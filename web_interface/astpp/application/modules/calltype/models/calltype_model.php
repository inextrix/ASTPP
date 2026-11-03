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
class calltype_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function getcalltype_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('calltype_list_search');
        $where = array(
            "status != " => "2"
        );
        if ($flag) {
            $query = $this->db_model->select("*", "calltype", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "calltype", $where);
        }
        return $query;
    }

    function get_reseller_calltype_list($flag, $accountid, $accounttype, $start = 0, $limit = 0)
    {
        $instant_search = $this->session->userdata('left_panel_search_' . $accounttype . '_calltypes');
        $instant_search_currency = $this->common_model->add_calculate_currency($instant_search, "", '', true, false);
        $like_str = ! empty($instant_search) ? "(calltype_name like '%$instant_search%'  
                                        OR includedseconds like '%$instant_search%')" : null;
        $this->db->where('id', $accountid);
        $this->db->select('pricelist_id');
        $account_info = (array) $this->db->get('accounts')->first_row();
        $where = array(
            'pricelist_id' => $account_info['pricelist_id']
        );
        if (! empty($like_str))
            $this->db->where($like_str);
        if ($flag) {
            $query = $this->db_model->select("*", "calltypes", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "calltypes", $where);
        }
        return $query;
    }

    function add_calltype($add_array)
    {
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $add_array["reseller_id"] = $account_data['id'];
        }
        unset($add_array["action"]);
        $add_array['date'] = gmdate('Y-m-d H:i:s');
        $this->db->insert("calltype", $add_array);
        return true;
    }

    function edit_calltype($data, $id)
    {
        unset($data["action"]);
        $data['date'] = gmdate('Y-m-d H:i:s');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $add_array["reseller_id"] = $account_data['id'];
        }
        $this->db->where("id", $id);
        $this->db->update("calltype", $data);
        return true;
    }

    function remove_calltype($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('calltype');
        $this->db->where("id", $id);
        $this->db->delete("calltype");
        return true;
    }

    function getcalltype_counter_list($flag, $start = 0, $limit = 0)
    {
        $where = array();
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == - 1 ? 0 : $accountinfo['id'];
        $this->db->where('reseller_id', $reseller_id);
        $this->db->select('id');
        $result = $this->db->get('accounts');
        $this->db_model->build_search('calltype_list_search');
        if ($this->session->userdata('advance_search') != 1) {
            if ($result->num_rows() > 0) {
                $acc_arr = array();
                $result = $result->result_array();
                foreach ($result as $data) {
                    $acc_arr[] = $data['id'];
                }
                $this->db->where_in('accountid', $acc_arr);
                if ($flag) {
                    $this->db->select('*');
                } else {
                    $this->db->select('count(id) as count');
                }
                if ($flag) {
                    $this->db->limit($limit, $start);
                }
                if (isset($_GET['sortname']) && $_GET['sortname'] != 'undefined') {
                    $this->db->order_by($_GET['sortname'], ($_GET['sortorder'] == 'undefined') ? 'desc' : $_GET['sortorder']);
                } else {
                    $this->db->order_by('seconds', 'desc');
                }
                $result = $this->db->get('counters');
                if ($flag) {
                    return $result;
                } else {
                    $result = $result->result_array();
                    return $result[0]['count'];
                }
            } else {
                if ($flag) {
                    $query = (object) array(
                        'num_rows' => 0
                    );
                } else {
                    $query = 0;
                }
                return $query;
            }
        } else {

            if ($result->num_rows() > 0) {
                $acc_arr = array();
                $result = $result->result_array();
                foreach ($result as $data) {
                    $acc_arr[] = $data['id'];
                }
                $this->db->where_in('accountid', $acc_arr);
            }

            if ($flag) {
                $this->db->select('*');
            } else {
                $this->db->select('count(id) as count');
            }
            if ($flag) {
                $this->db->order_by('seconds', 'desc');
                $this->db->limit($limit, $start);
            }
            $result = $this->db->get('counters');
            if ($result->num_rows() > 0) {
                if ($flag) {

                    return $result;
                } else {
                    $result = $result->result_array();

                    return $result[0]['count'];
                }
            } else {
                if ($flag) {

                    $query = (object) array(
                        'num_rows' => 0
                    );
                } else {
                    $query = 0;
                }
                return $query;
            }
        }
    }

    function insert_calltype_pattern($data, $calltypeid)
    {
        $this->db->select("pattern,comment");
        $this->db->where("id IN (" . $data . ")");
        $result = $this->db->get("routes")->result_array();

        $tmp = array();
        foreach ($result as $key => $data_value) {
            $tmp[$key]["calltype_id"] = $calltypeid;
            $tmp[$key]["patterns"] = $data_value['pattern'];
            $tmp[$key]["destination"] = $data_value['comment'];
        }
        return $this->db->insert_batch("calltype_patterns", $tmp);
    }

    function bulk_insert_calltype_pattern($inserted_array)
    {
        $this->db->insert_batch('calltype_patterns', $inserted_array);
        $affected_row = $this->db->affected_rows();
        return $affected_row;
    }

    function check_unique_call_type($action, $Select, $value)
    {
        $where = array(
            $Select => $value,
            "status != " => 2
        );
        if ($action == 'edit') {
            $this->db->where($where);
            $this->db->select("*");
            $this->db->from('calltype');
            $query = $this->db->get();
        } else {
            $query = $this->db_model->countQuery("*", "calltype", $where);
        }

        return $query;
    }
}
