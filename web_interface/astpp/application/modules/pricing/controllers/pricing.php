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
class pricing extends MX_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->helper('template_inheritance');

        $this->load->library('session');
        $this->load->library("pricing_form");
        $this->load->library('astpp/form', 'pricing_form');
        $this->load->library('astpp/permission');
        $this->load->library('ASTPP_Sms');
        $this->load->model('pricing_model');

        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function price_add($type = "")
    {
        $data['username'] = $this->session->userdata('user_name');
        $this->session->set_userdata('type_version', $this->config->item('edition'));
        $data['flag'] = 'Create Rate Group';
        $data['page_title'] = gettext('Create Rate Group');
        $data['trunk_count'] = Common_model::$global_config['system_config']['trunk_count'];
        $data['routing_type'] = 0;
        $data['reseller_id'] = 0;
        $data['form'] = $this->form->build_form($this->pricing_form->get_pricing_form_fields(''), '');
        $this->load->view('view_price_add_edit', $data);
    }

    function price_edit($edit_id = '')
    {
        $this->permission->check_web_record_permission($edit_id, 'pricelists', 'pricing/price_list/');
        $data['page_title'] = gettext('Edit Rate Group');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", " pricelists", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $routing_data = $this->db_model->getSelect("*", "routing", array(
            "pricelist_id" => $edit_id
        ));
        if ($account->num_rows > 0) {
            foreach ($routing_data->result_array() as $trunkid) {
                $edit_data["trunk_id"][] = $trunkid["trunk_id"];
            }
            $data['reseller_id'] = $edit_data['reseller_id'];
            if (astpp_eq0($edit_data['reseller_id'])) {
                $edit_data['reseller_id'] = 'Admin';
            } else {
                $edit_data['reseller_id'] = $this->common->get_field_name('number', 'accounts', array(
                    'id' => $edit_data['reseller_id']
                ));
            }
            $data['trunk_count'] = Common_model::$global_config['system_config']['trunk_count'];
            $data['routing_type'] = $edit_data['routing_type'];        }

        $data['form'] = $this->form->build_form($this->pricing_form->get_pricing_form_fields($edit_data['id']), $edit_data);
        $this->load->view('view_price_add_edit', $data);
    }

    function price_save()
    {
        $add_array = $this->input->post();
        $i = 1;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($add_array as $key => $value) {
                if (strpos($key, 'percentage') !== FALSE) {
                    $add_array['percentage'][$i] = $value;
                    $i ++;
                    unset($add_array[$key]);
                }
            }
            $add_array['markup'] = (isset($add_array['markup']) && $add_array['markup'] != "") ? $add_array['markup'] : 0;

            $data['form'] = $this->form->build_form($this->pricing_form->get_pricing_form_fields($add_array['id']), $add_array);
            if ($add_array['id'] != '') {
                $data['page_title'] = gettext('Edit Price Details');
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {

                    if (isset($add_array['routing_prefix']) && $add_array['routing_prefix'] != "") {
                        $no_of_rows = $this->pricing_model->check_unique_prefix_for_edit($add_array['routing_prefix']);
                        $result = $no_of_rows->result_array();
                        if (astpp_count($result) > 0) {
                            if ($result[0]['id'] != $add_array['id'] && $result[0]['routing_prefix'] == $add_array['routing_prefix']) {
                                echo json_encode(array(
                                    "routing_prefix_error" => gettext("Routing Prefix already exist in system.")
                                ));
                                exit();
                            }
                        }
                    }

                    $where = array(
                        "pricelist_id" => $add_array['id']
                    );
                    $this->db->delete("routing", $where);
                    if (isset($add_array['trunk_id']) || isset($add_array['routing_type'])) {
                        if (isset($add_array['trunk_id']) || ($add_array['routing_type'] == 2 || $add_array['routing_type'] == 3)) {
                                $this->set_force_routing($add_array['id'], $add_array['trunk_id']);
                        }
                    }
                    if (isset($add_array['trunk_id'])) {
                        unset($add_array['trunk_id']);
                        if (isset($add_array['trunk_id']) && $add_array['trunk_id'] != '') {
                            $this->set_force_routing($add_array['id'], $add_array['trunk_id']);
                            unset($add_array['trunk_id']);
                        }
                    }
                    if (isset($add_array['reseller_id']) && $add_array['reseller_id'] != '') {
                        unset($add_array['reseller_id']);
                    }

                    $this->pricing_model->edit_price($add_array, $add_array['id']);
                    echo json_encode(array(
                        "SUCCESS" => ucfirst($add_array['name']).' '.gettext('Rate Group Updated Successfully!')
                    ));
                    exit();
                }

                $this->load->view('view_price_add_edit', $data);
            } else {
                $data['page_title'] = gettext('Create Price Details');
                if ($this->form_validation->run() == FALSE) {
                    $data['validation_errors'] = validation_errors();
                    echo $data['validation_errors'];
                    exit();
                } else {

                    if (isset($add_array['routing_prefix']) && $add_array['routing_prefix'] != "") {
                        $prefix = $this->pricing_model->check_unique_prefix($add_array['routing_prefix']);
                        if (astpp_gt($prefix, 0)) {
                            echo json_encode(array(
                                "routing_prefix_error" => gettext("Routing Prefix already exist in system.")
                            ));
                            exit();
                        }
                    }
                    if (isset($add_array['trunk_id']) || isset($add_array['routing_type'])) {
                        if (isset($add_array['trunk_id']) || $add_array['routing_type'] == 2 || $add_array['routing_type'] == 3) {
                                $trunk_id = $add_array['trunk_id'];
                        }
                    }
                    unset($add_array['trunk_id']);
                    $priceid = $this->pricing_model->add_price($add_array);
                    if (isset($trunk_id)) {
                        if ($add_array['routing_type'] == 2) {
                            foreach ($trunk_id as $value) {
                                if (! empty($value) && $value != '')
                                    $this->set_force_routing_new($priceid, $value, $add_array['routing_type']);
                            }
                        } else if ($add_array['routing_type'] == 3) {
                            foreach ($trunk_id as $key => $value) {
                                if (! empty($value) && $value != '')
                                    $this->set_force_routing_new($priceid, $value, $add_array['routing_type'], $percentage[$key]);
                            }
                        } else {
                            $this->set_force_routing($priceid, $trunk_id);
                        }
                    }
                    echo json_encode(array(
                        "SUCCESS" => ucfirst($add_array['name']). ' '.gettext('Rate Group Added Successfully!')
                    ));
                    exit();
                }
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'pricing/price_list/');
        }
    }

    function set_force_routing($priceid, $trunkid)
    {
        foreach ($trunkid as $id) {
            $routing_arr = array(
                "trunk_id" => $id,
                "pricelist_id" => $priceid
            );
            $this->db->insert("routing", $routing_arr);
        }
    }

    function price_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('price_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'accounts/customer_list/');
        }
    }

    function price_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('account_search', "");
    }

    function price_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Rate Groups');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->pricing_form->build_pricing_list_for_admin();
        $data["grid_buttons"] = $this->pricing_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->pricing_form->get_pricing_search_form());
        $this->load->view('view_price_list', $data);
    }

    function price_list_json()
    {
        $json_data = array();
        $count_all = $this->pricing_model->getpricing_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->pricing_model->getpricing_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->pricing_form->build_pricing_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function price_delete_multiple()
    {
        $add_array = $this->input->post();
        $where = 'IN (' . $add_array['selected_ids'] . ')';
        if (! empty($add_array) && isset($add_array['selected_ids'])) {
            if (isset($add_array['flag'])) {
                $update_data = array(
                    'status' => '2'
                );
                $this->db->where('pricelist_id ' . $where);
                $this->db->delete('routes');
                $this->db->delete("routing", array(
                    "pricelist_id" => $where
                ));
                $this->db->where('id ' . $where);
                echo $this->db->update('pricelists', $update_data);
            } else {
                $pricelist_arr = array();
                $this->db->select('id,name');
                $this->db->where('id ' . $where);
                $pricelist_res = $this->db->get('pricelists');
                $pricelist_res = $pricelist_res->result_array();
                foreach ($pricelist_res as $value) {
                    $pricelist_arr[$value['id']]['name'] = $value['name'];
                }
                $this->db->where('pricelist_id ' . $where);
                $this->db->where('deleted', 0);
                $this->db->select('count(id) as cnt,pricelist_id');
                $this->db->group_by('pricelist_id');
                $account_res = $this->db->get('accounts');
                if ($account_res->num_rows() > 0) {
                    $account_res = $account_res->result_array();
                    foreach ($account_res as $key => $value) {
                        $pricelist_arr[$value['pricelist_id']]['account'] = $value['cnt'];
                    }
                }
                $this->db->where('pricelist_id ' . $where);
                $this->db->select('count(id) as cnt,pricelist_id');
                $this->db->group_by('pricelist_id');
                $routes_res = $this->db->get('routes');
                if ($routes_res->num_rows() > 0) {
                    $routes_res = $routes_res->result_array();
                    foreach ($routes_res as $key => $value) {
                        $pricelist_arr[$value['pricelist_id']]['routes'] = $value['cnt'];
                    }
                }
                $str = null;
                foreach ($pricelist_arr as $key => $value) {
                    $custom_str = null;
                    if (isset($value['account']) || isset($value['routes'])) {
                        if (isset($value['account'])) {
                            $custom_str .= $value['account'] . " accounts and ";
                        }
                        if (isset($value['routes'])) {
                            $custom_str .= $value['routes'] . " origination rates and ";
                        }
                        $str .= " Rate group Name : " . $value['name'] . " using by " . rtrim($custom_str, " and ") . "\n";
                    }
                }
                if (! empty($str)) {
                    $data['str'] = $str;
                }
                $data['selected_ids'] = $add_array['selected_ids'];
                echo json_encode($data);
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
            redirect(base_url() . 'pricing/price_list/');
        }
    }

    Public function price_duplicate()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = gettext('Create Duplicate Rate Group');
        $data['page_title'] = gettext('Create Duplicate Rate Group');
        $data['form'] = $this->form->build_form($this->pricing_form->get_pricing_duplicate_form_fields(), '');
        $this->load->view('view_price_duplicate_add_edit', $data);
    }

    Public function price_duplicate_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->pricing_form->get_pricing_duplicate_form_fields(), $add_array);

        $data['page_title'] = gettext('Edit Price Details');
        if ($this->form_validation->run() == FALSE) {
            $data['validation_errors'] = validation_errors();
            echo $data['validation_errors'];
            die();
        } else {
            if ($add_array['pricelist_id'] != '') {
                $selected_pricegroup_id = $add_array['pricelist_id'];
                $this->db->where('id ', $selected_pricegroup_id);
                $this->db->select('*');
                $price_grp_res = $this->db->get('pricelists');
                if ($price_grp_res->num_rows() > 0) {
                    $price_grp_res = $price_grp_res->result_array();
                    $new_duplicate_price_group_name = $add_array['name'];
                    $add_price_array = array(
                        'name' => $new_duplicate_price_group_name,
                        'markup' => $price_grp_res['0']['markup'],
                        'routing_prefix' => $price_grp_res['0']['routing_prefix'],
                        'routing_type' => $price_grp_res['0']['routing_type'],
                        'initially_increment' => $price_grp_res['0']['initially_increment'],
                        'inc' => $price_grp_res['0']['inc'],
                        'status' => $price_grp_res['0']['status'],
                        'reseller_id' => $price_grp_res['0']['reseller_id'],
                        'creation_date' => date('Y-m-d H:i:s')
                    );
                    $this->pricing_model->add_price($add_price_array);
                    $insert_id = $this->db->insert_id();
                    $rate_group_label_id = $price_grp_res['0']['id'];
                    $this->db->where('pricelist_id ', $rate_group_label_id);
                    $this->db->select('*');
                    $routes_grp_res = $this->db->get('routes');
                    if ($routes_grp_res->num_rows() > 0) {
                        $routes_grp_res = $routes_grp_res->result_array();
                        $data = $routes_grp_res;
                        foreach ($data as $key => $value) {
                            $value['pricelist_id'] = $insert_id;
                            $data1[] = $value;
                        }
                        foreach ($data1 as $key => $value) {
                            unset($value['id']);
                            $this->pricing_model->add_origination($value);
                        }

                        echo json_encode(array(
                            "SUCCESS" => ucfirst($add_array["name"]) .' '. gettext("Duplicate Rate Group Added Successfully!")
                        ));
                        die();
                    } else {
                        echo json_encode(array(
                            "SUCCESS" => ucfirst($add_array["name"]) .' '. gettext("Duplicate Rate Group Added Successfully!")
                        ));
                        exit();
                    }
                } else {
                    echo gettext("error");
                    exit();
                }
            }
        }
    }
}
?>
