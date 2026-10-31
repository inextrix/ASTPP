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
class System_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function getsystem_list($flag, $start, $limit)
    {
        $this->db_model->build_search('configuration_search');
        $where = "group_title NOT IN ('asterisk','osc','freepbx')";
        $this->db->where($where);
        if ($flag) {
            $query = $this->db_model->select("*", "system", "", "group_title,name", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "system", "");
        }
        return $query;
    }

    function gettemplate_list($flag = "", $start, $limit = "")
    {
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $reseller = $account_data['id'];
            $this->db->where('reseller_id', $reseller);
            $query = $this->db_model->select("*", "default_templates", "", "", "", "", "");

            if ($query->num_rows() > 0) {
                $result = $query->result_array();
                $match_array = array();
                $unmatch_array = array();
                $i = 0;
                $str = 0;
                foreach ($result as $value) {
                    $this->db->where('name', $value['name']);
                    $this->db->where('reseller_id', 0);
                    $query = $this->db_model->select("id", "default_templates", "", "id", "ASC", $limit, $start);
                    $innerresult = $query->result_array();
                    foreach ($innerresult as $value) {
                        $str .= $value['id'] . ",";
                    }
                }
                $str = rtrim($str, ',');

                $where = "id NOT IN ($str)";
                $this->db->where('reseller_id', $reseller);
                $this->db->or_where('reseller_id', 0);
                $this->db->where($where);
            } else {
                $this->db->where('reseller_id', 0);
            }
            $this->db_model->build_search('template_search');
            if ($flag) {
                $query = $this->db_model->select("*", "default_templates", "", "id", "ASC", $limit, $start);
            } else {

                $query = $this->db_model->countQuery("*", "default_templates", "");
            }
        } else {
            $this->db_model->build_search('template_search');
            if ($flag) {
                $query = $this->db_model->select("*", "default_templates", "", "id", "ASC", $limit, $start);
            } else {
                $query = $this->db_model->countQuery("*", "default_templates", "");
            }
        }
        return $query;
    }

    function edit_configuration($add_array, $name)
    {
        if ($this->session->userdata('logintype') == 1) {
            $account_data = $this->session->userdata("accountinfo");
            $reseller_id = $account_data['id'];
        } else {
            $reseller_id = 0;
        }
        unset($add_array["action"]);
        $this->db->where("reseller_id", $reseller_id);
        $this->db->where("name", $name);
        $this->db->update("system", $add_array);
        // Only for the system-wide setting: the currency table is global, and resellers reach this
        // method too (systems/configuration) although they have no base_currency row of their own;
        // otherwise a reseller could set the rate of any currency to 1 for the whole system.
        if ($name == 'base_currency' && $reseller_id == 0 && isset($add_array['value']) && ! is_array($add_array['value'])) {

            astpp_cron_background('CurrencyUpdate');
            // the new base currency has the rate 1 (this always set INR)
            $this->db->update("currency", array(
                "currencyrate" => '1'
            ), array(
                "currency" => (string) $add_array['value']
            ));
        }
    }

    function edit_template($data, $id)
    {
        unset($data["action"]);
        $data["last_modified_date"] = date("Y-m-d H:i:s");
        $this->db->where("id", $id);
        $this->db->update("default_templates", $data);
    }

    function edit_resellertemplate($data, $id)
    {
        $arraydata = $data;
        // Only the own template of the reseller (the posted id could be any
        // template, and without reseller session the update had no WHERE at all)
        $account_data = $this->session->userdata("accountinfo");
        $this->db->where("id", $id);
        $this->db->where("reseller_id", (int) $account_data['id']);
        $arraydata['reseller_id'] = (int) $account_data['id'];
        unset($arraydata["action"]);
        unset($arraydata["form"]);
        unset($arraydata["page_title"]);
        $this->db->update("default_templates", $arraydata);
    }

    function remove_template($id)
    {
        $this->db->where("id", $id);
        $this->db->update("default_templates", array(
            "status" => 2
        ));
        return true;
    }

    function add_resellertemplate($data)
    {
        unset($data["action"]);
        unset($data['id']);
        $this->db->insert('default_templates', $data);
        return true;
    }

    function getcountry_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('country_list_search');
        $where = array();
        if ($flag) {
            if (isset($_GET['sortname']) && $_GET['sortname'] == 'id') {
                $_GET['sortname'] = "country";
                $_GET['sortorder'] = 'ASC';
            }
            $query = $this->db_model->select("*", "countrycode", $where, "country", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "countrycode", $where);
        }
        return $query;
    }

    function add_country($add_array)
    {
        unset($add_array["action"]);
        $this->db->insert("countrycode", $add_array);
        return true;
    }

    function edit_country($data, $id)
    {
        unset($data["action"]);
        $this->db->where("id", $id);
        $this->db->update("countrycode", $data);
    }

    function remove_country($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("countrycode");
        return true;
    }

    function getcurrency_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('currency_list_search');

        $where = array(
            'currency <>' => Common_model::$global_config['system_config']['base_currency']
        );
        if (isset($_GET['sortname']) && $_GET['sortname'] == 'id') {
            $_GET['sortname'] = "currencyname";
            $_GET['sortorder'] = 'ASC';
        }

        if ($flag) {
            $query = $this->db_model->select("*", "currency", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "currency", $where);
        }
        return $query;
    }

    function add_currency($add_array)
    {
        unset($add_array["action"], $add_array["id"]);
        $this->db->insert("currency", $add_array);
        return true;
    }

    function edit_currency($data, $id)
    {
        unset($data["action"]);
        $this->db->where("id", $id);
        $this->db->update("currency", $data);
    }

    function remove_currency($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("currency");
        return true;
    }

    function backup_insert($add_array = '')
    {
        unset($add_array["action"]);
        $this->db->insert("backup_database", $add_array);
        return true;
    }

    function getbackup_list($flag, $start, $limit)
    {
        if ($flag) {
            $query = $this->db_model->select("*", "backup_database", "", "date", "DESC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "backup_database", "");
        }
        return $query;
    }

    function get_backup_data($id)
    {
        $where = array(
            'id' => $id
        );
        $query = $this->db_model->getSelect("*", "backup_database", $where);
        return $query;
    }

    function import_database($filename, $target_path)
    {
        $this->db->insert("backup_database", array(
            'backup_name' => $filename,
            'path' => $target_path
        ));
        return true;
    }

    function get_system_sidemenue()
    {
        $this->db->select("distinct group_title", false);
        $this->db->order_by('group_title');
        $query = $this->db->get("system");
        $group_title = array();
        $result = $query->result_array();
        foreach ($result as $row) {
            $group_title[$row['group_title']] = $row['group_title'];
        }
        $order_array = array(
            'global',
            'calls',
            'homer',
            'opensips',
            'payment_methods',
            'purge',
            'signup',
            'database',
            'notifications'
        );
        $order_array = $this->sort_array_by_subgroup($group_title, $order_array);

        if ($this->session->userdata('logintype') == '1') {

            $new_order_array['payment_methods'] = $order_array['payment_methods'];
            return $new_order_array;
        } else {
            return $order_array;
        }
    }

    function sort_array_by_subgroup($array, $orderArray)
    {
        $ordered = array();
        foreach ($orderArray as $key) {
            if (array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }

    function get_subcategory_menu($group_title)
    {
        if ($this->session->userdata('logintype') == 1) {
            $account_data = $this->session->userdata("accountinfo");
            $reseller_id = $account_data['id'];
        } else {
            $reseller_id = 0;
        }
        $where = array(
            'group_title' => $group_title,
            'is_display' => 0,
            'reseller_id' => $reseller_id
        );
        $this->db->where($where);
        $query = $this->db->get("system");
        $fieldset_array = array();
        $result = $query->result_array();
        foreach ($result as $row) {
            $fieldset_array[$row['sub_group']][] = $row;
        }
        return $fieldset_array;
    }

    function getlanguages_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('currency_list_search');
        $where = array(
            'currency <>' => Common_model::$global_config['system_config']['base_currency']
        );
        if ($flag) {
            $query = $this->db_model->select("*", "languages", "", "", "", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "languages", "");
        }
        return $query;
    }

    function add_languages($add_array)
    {
        unset($add_array["action"], $add_array["id"]);
        // The locale becomes a column name (ALTER TABLE) and a directory/shell path
        if (! isset($add_array['locale']) || ! preg_match('/^[A-Za-z]{2,3}(_[A-Za-z]{2,4})?$/', $add_array['locale'])) {
            return false;
        }
        $this->db->insert("languages", $add_array);
        $this->db->query('ALTER TABLE translations ADD ' . "`" . $add_array['locale'] . "`" . ' VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
        $this->db->query('UPDATE translations SET ' . "`" . $add_array['locale'] . "`" . '= en_En');
        return true;
    }

    function edit_languages($data, $id)
    {
        unset($data["action"]);
        $this->db->where("id", $id);
        $this->db->update("languages", $data);
    }

    function remove_languages($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("languages");
        return true;
    }

    function get_translation_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('product_list_search');
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            if ($flag) {
                if (isset($_GET['sortname']) && $_GET['sortname'] == 'id') {
                    $_GET['sortname'] = "module_name";
                    $_GET['sortorder'] = 'ASC';
                }
                $query = $this->db_model->select("*", "translations", '', "", "", $limit, $start);
            } else {
                $query = $this->db_model->select("*", "translations", '', "", "", $limit, $start);
            }
            return $query;
        } else {
            if ($flag) {
                $query = $this->db_model->select("*", "translations", '', "", "", $limit, $start);
            } else {
                $query = $this->db_model->countQuery("*", "translations", '');
            }
            return $query;
        }
    }

    function add_translation($add_array)
    {
        unset($add_array["action"], $add_array["id"]);
        $this->db->insert("translations", $add_array);
        return true;
    }

    function edit_translation($data, $id)
    {
        unset($data["action"]);
        $this->db->where("id", $id);
        $this->db->update("translations", $data);
    }

    function remove_translation($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("translations");
        return true;
    }

    function gettimezone_list($flag, $start = 0, $limit = 0)
    {
        $this->db_model->build_search('timezone_list_search');

        $where = array(
            'timezone_name <>' => Common_model::$global_config['system_config']['default_timezone']
        );
        if (isset($_GET['sortname']) && $_GET['sortname'] == 'id') {
            $_GET['sortname'] = "timezone_name";
            $_GET['sortorder'] = 'ASC';
        }

        if ($flag) {
            $query = $this->db_model->select("*", "timezone", $where, "id", "ASC", $limit, $start);
        } else {
            $query = $this->db_model->countQuery("*", "timezone", $where);
        }
        return $query;
    }
    function edit_timezone($data, $id)
    {
        unset($data["action"]);
        $this->db->where("id", $id);
        $this->db->update("timezone", $data);
    }
    function remove_timezone($id)
    {
        $this->db->where("id", $id);
        $this->db->delete("timezone");
        return true;
    }
    function add_timezone($add_array)
    {
        unset($add_array["action"], $add_array["id"]);
        $this->db->insert("timezone", $add_array);
        return true;
    }
}
