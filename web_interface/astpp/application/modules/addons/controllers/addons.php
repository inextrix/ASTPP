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

// sudo apt-get install php5.6-zip
// sudo service apache2 restart
class Addons extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library('astpp/form');
        $this->load->library('astpp/permission');
        $this->config->load('addons');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    /*
     * This functions are used for build array of files and directories
     */
    function recursiveFiles($dir, $astpp_path = '', $remove_path = '')
    {
        $files_array = array();
        $tree = glob(rtrim($dir, '/') . '/*');
        if (is_array($tree)) {

            foreach ($tree as $file) {
                if (is_dir($file)) {
                    // ~ $file1="";
                    $file1 = str_replace($remove_path, $astpp_path, $file);
                    if (! is_dir($file1)) {
                        $files_array[] = $file1;
                    }
                    $files_array[] = $this->recursiveFiles($file, $astpp_path, $remove_path);
                } elseif (is_file($file)) {
                    $files_array[] = $file;
                }
            }
        }
        return $files_array;
    }

    function flatten_array($a, $remove_path, $add_path)
    {
        $output = array();
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($a));
        foreach ($it as $v) {
            $v = str_replace($remove_path, $add_path, $v);
            // ~ $v = base64_encode($v);
            $output[] = $v;
        }
        return $output;
    }

    /**
     * *******************************
     */
    // The add-on type, package name and version come from URL segments and end up in shell
    // commands (cp, rm), file paths and SQL: only plain names and version numbers.
    private function _valid_addon_args($type, $module = '', $version = '', $current_version = '')
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', (string) $type) || ! is_dir($this->config->item('addons_path') . $type)) {
            return false;
        }
        if ((string) $module !== '' && ! preg_match('/^[A-Za-z0-9_]+$/', (string) $module)) {
            return false;
        }
        foreach (array($version, $current_version) as $v) {
            if ((string) $v !== '' && ! preg_match('/^[0-9][0-9.]*$/', (string) $v)) {
                return false;
            }
        }
        return true;
    }

    // Main function for all addon activities
    function addons_list($type = "opensource")
    {
        if (! $this->_valid_addon_args($type)) {
            show_404();
        }

        // Set page information
        // echo $type; exit;
        $data['addon_flag'] = true;
        $addons_path = $this->config->item('addons_path');
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Addons');
        $module_xml = $this->config->item('module_xml');
        // ~ $dir="/backup/html/ADDONS/";
        $addons_path = $this->config->item('addons_path');
        $dir = $addons_path . $type;


        $a = scandir($dir);
        // 77
        $i = 1;
        foreach ($a as $key => $val) {
            if ($val != '.' && $val != '..' && $val != '.git') {
                $module_xml = glob($addons_path . $type . "/" . $val . "/*.xml");
                if (isset($module_xml) && ! empty($module_xml)) {
                    $module_xml = $module_xml[0];
                    $xml = file_get_contents($module_xml);
                    $module_array = simplexml_load_string($xml);
                    // echo "<pre>"; print_r($module_array); /*exit;*/
                    if (@$module_array->status == "production") {
                        $data['module_array'][$i] = $module_array;
                        $i ++;
                    }
                }
            }
        }
        $query = "select * from addons";
        $result = $this->db->query($query);
        $installed_addon_list = $result->result_array();
        $installed_addons = array();
        for ($i = 0; $i < count($installed_addon_list); $i ++) {
            $installed_addons[$installed_addon_list[$i]['package_name']] = $installed_addon_list[$i];
        }
        $data['installed_addons'] = $installed_addons;
        $data['type'] = $type;
        $this->load->view('addons', $data);
    }

    public function addons_install($type = '', $module = '', $action = '', $version = "", $current_version = "")
    {
        if (! $this->_valid_addon_args($type, $module, $version, $current_version)) {
            show_404();
        }
        $addons_path = $this->config->item('addons_path');
        $astpp_path = FCPATH;
        $fs_path = $this->config->item('fs_path');
        $fs_usr_path = $this->config->item('fs_usr_path');
        $paths_of_file = array();

        if ($module != "" && $action != "") {
            $messages = "";
            if ($action == 'install' or $action == "update") {
                $file_list = array();
                $file_list['web_interface'] = array();
                $file_list['freeswitch'] = array();
                $file_list['freeswitch_usr'] = array();
                if ($action == "update") {
                    $result = $this->db->query("Select files from addons where package_name='" . $module . "'");
                    $encoded = (array) $result->first_row();
                    foreach ($encoded as $key => $val) {
                        $file_list = json_decode($val, TRUE);
                    }
                }
                // ============================ WEB COPY START ======================
                if (is_dir($addons_path . $type . "/" . $module . "/web_interface/astpp")) {

                    // ~ echo $addons_path.$type."/".$module."/web_interface/astpp"; exit;
                    // Copy files to web folder
                    echo "Copying file to web directories...<br/>";
                    $remove_path = $addons_path . $type . "/" . $module . "/web_interface/astpp/";
                    $add_path = $astpp_path;
                    $path = $addons_path . $type . "/" . $module . "/web_interface/astpp/";
                    $scans = scandir($path);
                    $result_array = $this->recursiveFiles($path, $astpp_path, $remove_path);
                    $files_array = $this->flatten_array($result_array, $remove_path, $add_path);
                    $merge_array = array_unique(array_merge($file_list['web_interface'], $files_array));
                    $decoded = $merge_array;
                    $paths_of_decoded_files['web_interface'] = $merge_array;
                    $paths_of_file['web_interface'] = $merge_array;

                    $last_line = system("/bin/cp -rf " . $addons_path . $type . "/" . $module . "/web_interface/astpp/. " . $astpp_path, $retval);
                    if ($retval == '1') {
                        echo "<span style='color:red;'>Failed!! Unable to copy files!!!</span><br/><br/>";
                        echo "<a href='" . base_url() . "addons/addons_list/" . $type . "'>Back</a>";
                        if ($action != "update") {
                            foreach ($paths_of_decoded_files as $key => $val) {
                                foreach ($val as $file_key => $file_path) {
                                    if ($file_path != "") {
                                        exec("/bin/rm -rf " . $file_path, $retval);
                                    }
                                }
                            }
                        }
                        exit();
                    } else {
                        echo "Copy process completed for web directories!!<br/><br/>";
                    }
                }
                // ============================ WEB COPY END ======================

                // ============================ FREESWITCH COPY START ======================

                if (is_dir($addons_path . $type . "/" . $module . "/freeswitch/fs/lib/addons")) {
                    // Copy files to web folder but in fs folder
                    echo "Copying file for freeswitch CDR...<br/>";

                    // Copy freeswitch cdr process files
                    $remove_path = $addons_path . $type . "/" . $module . "/freeswitch/fs/";
                    $add_path = $fs_path;
                    $path = $addons_path . $type . "/" . $module . "/freeswitch/fs/lib/addons/";
                    $scans = scandir($path);
                    $result_array = $this->recursiveFiles($path);
                    $files_array = $this->flatten_array($result_array, $remove_path, $add_path);
                    $merge_array = array_unique(array_merge($file_list['freeswitch'], $files_array));
                    $decoded = $merge_array;
                    $paths_of_decoded_files['freeswitch'] = $merge_array;
                    $paths_of_file['freeswitch'] = $merge_array;
                    system("/bin/mkdir -p " . $fs_path . "lib/addons/");
                    $last_line = system("/bin/cp -rf " . $addons_path . $type . "/" . $module . "/freeswitch/fs/lib/addons/. " . $fs_path . "/lib/addons/.", $retval);

                    if ($retval == '1') {
                        echo "<span style='color:red;'>Failed!! Unable to copy files to /var/www/html/fs/lib/addons/ folder!!!</span><br/><br/>";
                        echo "<a href='" . base_url() . "addons/addons_list/" . $type . "'>Back</a>";
                        if ($action != "update") {
                            foreach ($paths_of_decoded_files as $key => $val) {
                                foreach ($val as $file_key => $file_path) {
                                    if ($file_path != "") {
                                        exec("/bin/rm -rf " . $file_path, $retval);
                                    }
                                }
                            }
                        }
                        exit();
                    } else {
                        echo "Copy process completed for freeswitch CDR directories!!<br/><br/>";
                    }
                }

                // if (is_dir ($addons_path.$type."/".$module."/freeswitch/scripts/astpp/lib/addons/"))
                if (is_dir($addons_path . $type . "/" . $module . "/freeswitch/scripts/astpp/lib/")) {
                    echo "Copying file for freeswitch call scripts...<br/>";
                    // Copy files to scripts folder
                    $remove_path = $addons_path . $type . "/" . $module . "/freeswitch/";
                    $add_path = $fs_usr_path;
                    // $path = $addons_path.$type."/".$module."/freeswitch/scripts/astpp/lib/addons/";
                    $path = $addons_path . $type . "/" . $module . "/freeswitch/scripts/astpp/";


                    $scans = scandir($path);

                    $result_array = $this->recursiveFiles($path);
                    // print_r($scans);die();
                    // 9211
                    $files_array = $this->flatten_array($result_array, $remove_path, $add_path);
                    $merge_array = array_unique(array_merge($file_list['freeswitch_usr'], $files_array));
                    $decoded = $merge_array;
                    $paths_of_decoded_files['freeswitch_usr'] = $merge_array;
                    $paths_of_file['freeswitch_usr'] = $merge_array;
                    // ~ echo "<pre>"; print_r($paths_of_file); exit;
                    system("/bin/mkdir -p " . $fs_usr_path . "scripts/astpp/lib/");
                    $last_line = system("/bin/cp -rf " . $addons_path . $type . "/" . $module . "/freeswitch/scripts/astpp/lib/. " . $fs_usr_path . "scripts/astpp/lib/.", $retval);

                    if ($retval == '1') {
                        if ($action != "update") {
                            foreach ($paths_of_decoded_files as $key => $val) {
                                foreach ($val as $file_key => $file_path) {
                                    if ($file_path != "") {
                                        exec("/bin/rm -rf " . $file_path, $retval);
                                    }
                                }
                            }
                        }
                        echo "<span style='color:red;'>Failed!! Unable to copy files to /usr/share/freeswitch/scripts/astpp/lib/ folder!!!</span><br/><br/>";
                        echo "<a href='" . base_url() . "addons/addons_list/" . $type . "'>Back</a>";

                        exit();
                    } else {
                        echo "Copy process completed for freeswitch directories!!<br/><br/>";
                    }
                }
                // ============================ FREESWITCH COPY END ======================
                $paths_of_file = json_encode($paths_of_file);

                // Import sql file
                echo "Importing database queries...<br/>";
                if ($current_version != "") {
                    $current_version = str_replace('.', '', $current_version);
                    $new_version = str_replace('.', '', $version);

                    for ($i = ($current_version + 1); $i <= $new_version; $i ++) {
                        $version = implode('.', str_split($i));
                        if (file_exists($addons_path . $type . "/" . $module . "/database/" . $module . "_" . $version . ".sql")) {
                            $sql = file_get_contents($addons_path . $type . "/" . $module . "/database/" . $module . "_" . $version . ".sql");

                            // HP: - Trigger issue. start pbx addon changes
                            $find_functionalities = explode('\n', $sql);

                            foreach ($find_functionalities as $key => $value) {
                                if (strpos($value, '-- start-event') !== false) {
                                    $custom_statement = $this->_get_string_between($sql, '-- start-event', '-- end-event');
                                    if (! empty($custom_statement)) {
                                        // echo $custom_statement; exit;
                                        $sql = str_replace($custom_statement, "", $sql);
                                        $sql = str_replace("-- start-event", "", $sql);
                                        $sql = str_replace("-- end-event", "", $sql);
                                        $this->db->db_debug = false;
                                        $custom_statement_break = explode("-- break-event", $custom_statement);

                                        foreach ($custom_statement_break as $custom_statement_value) {
                                            $this->db->query($custom_statement_value);
                                            $error_no = $this->db->_error_number();
                                            $error_msg = $this->db->_error_message();
                                            if ($error_msg != "") {
                                                $this->db->trans_rollback();
                                                foreach ($paths_of_decoded_files as $key => $val) {
                                                    foreach ($val as $file_key => $file_path) {
                                                        if ($file_path != "") {
                                                            exec("/bin/rm -rf " . $file_path, $retval);
                                                        }
                                                    }
                                                }
                                                echo "Error Code : " . $error_no . "<br/>";
                                                echo "Error Message : " . $error_msg . "<br/>";
                                                echo "Error in this query : " . $custom_statement_value . "<br/>";
                                                echo "<a href='" . base_url() . "addons/addons_list/" . $type . "'>Back</a>";
                                                exit();
                                            }
                                        }
                                    }
                                }
                            }
                            // HP: end ;
                            $sqls = explode(';', $sql);
                            array_pop($sqls);
                            // When we get an error in a query then we have to rollback all the queries which we are inserted.
                            // Limitation: [Using this we have to rollback only insert,update and delete queries.]
                            // Transaction Start
                            $this->db->trans_start();
                            // If we need to rollback queries then we must be set autocommit=0
                            $this->db->query("SET autocommit=0");
                            foreach ($sqls as $statement) {
                                // we need to get error message and error code using de_debug=false.
                                $this->db->db_debug = false;
                                $this->db->query($statement);
                                $error_no = $this->db->_error_number();
                                $error_msg = $this->db->_error_message();
                                if ($error_msg != "") {
                                    // Rollback all the queries when we get any error
                                    $this->db->trans_rollback();
                                    if ($action != "update") {
                                        foreach ($paths_of_decoded_files as $key => $val) {
                                            foreach ($val as $file_key => $file_path) {
                                                if ($file_path != "") {
                                                    exec("/bin/rm -rf " . $file_path, $retval);
                                                }
                                            }
                                        }
                                    }
                                    echo "Error Code : " . $error_no . "<br/>";
                                    echo "Error Message : " . $error_msg . "<br/>";
                                    echo "Error in this query : " . $statement . "<br/>";
                                    echo "<a href='" . base_url() . "addons/addons_list/" . $type . "'>Back</a>";
                                    exit();
                                }
                            }
                            // Commit queries means queries are successfully run
                            $this->db->trans_commit();
                            // Transaction complete
                            $this->db->trans_complete();
                        }
                    }
                }
                echo "Database queries import process completed!!<br/><br/>";

                // Update addons table
                if ($action == "install") {
                    $insert = array(
                        "package_name" => $module,
                        "version" => $version,
                        "installed_date" => gmdate("Y-m-d H:i:s"),
                        "last_updated_date" => gmdate("Y-m-d H:i:s"),
                        "files" => $paths_of_file
                    );
                    $this->db->insert("addons", $insert);
                } else {
                    $update = array(
                        "version" => $version,
                        "last_updated_date" => gmdate("Y-m-d H:i:s"),
                        "files" => $paths_of_file
                    );
                    $this->db_model->update("addons", $update, array(
                        "package_name" => $module
                    ));
                }
                // ASTPPCOM-982 Ashish Start
                if($module=='voice_broadcast'){
                    $dir_path = "/opt/ASTPP/web_interface/astpp/upload/voice_broadcast";
                    mkdir($dir_path, 0777, true);
                    chmod($dir_path, 0777);
                }
                // ASTPPCOM-982 Ashish End
		//AD: Language
                if (strpos($module, 'language') !== false) {
                    $command = "wget --no-check-certificate -q -O- ".base_url() ."Translation_script/insert_translation/".$module;
                    system($command);
                }
                $command = "wget --no-check-certificate -q -O- ".base_url() ."Translation_script/insert_translation_addons/".$module;
                system($command);
                echo "<i><b>Module successfully imported!!</b></i><br/><br/>";
                echo "Please re-login to get impact of addon!! <a href='/logout'>Re-login</a> | <a href='" . base_url() . "addons/addons_list/" . $type . "'>Back</a><br/><br/>";
                exit();
            } elseif ($action == "uninstall") {
                echo "Uninstalling module...<br/>";
                // AD: Below code is using to drop events when alarm addons uninstalled.
                $uninstall_file_controllers = ucfirst($module) . "addons.php";
                $uninstall_file_controller_path = FCPATH . APPPATH . "controllers/" . $uninstall_file_controllers;
                if (file_exists($uninstall_file_controller_path)) {
                    $class_name = ucfirst($module) . "addons";

                    include_once ($uninstall_file_controller_path);
                    $addon_class = new $class_name();
                    if (method_exists($addon_class, '_uninstall')) {
                        $reflection_class = new ReflectionClass($class_name);
                        $reflection_method = $reflection_class->getMethod("_uninstall");
                        $reflection_method->setAccessible(true);
                        $result = $reflection_method->invoke($addon_class);
                    }
                }
		if (strpos($module, 'language') !== false) {
                    $command = "wget --no-check-certificate -q -O- ".base_url() ."Translation_script/language_uninstall/".$module;
                    system($command);
                }
                // AD: Done
                if (file_exists($addons_path . $type . "/" . $module . "/database/uninstall.sql")) {
                    $sql = file_get_contents($addons_path . $type . "/" . $module . "/database/uninstall.sql");
                    $sqls = explode(';', $sql);
                    array_pop($sqls);

                    foreach ($sqls as $statement) {
                        $statement = $statement . ";";
                        // ~ $this->db->query($statement);
                        $this->db->db_debug = false;
                        $this->db->query($statement);
                        $error_no = $this->db->_error_number();
                        $error_msg = $this->db->_error_message();
                        if ($error_msg != "") {
                            $this->db->trans_rollback();
                            foreach ($paths_of_decoded_files as $key => $val) {
                                foreach ($val as $file_key => $file_path) {
                                    if ($file_path != "") {
                                        exec("/bin/rm -rf " . $file_path, $retval);
                                    }
                                }
                            }
                            echo "Error Code : " . $error_no . "<br/>";
                            echo "Error Message : " . $error_msg . "<br/>";
                            echo "Error in this query : " . $statement . "<br/>";
                            echo "<a href='" . base_url() . "addons/addons_list/" . $type . "'>Back</a>";
                            exit();
                        }
                    }
                }
                $result = $this->db->query("Select files from addons where package_name='" . $module . "'");
                $encoded = (array) $result->first_row();
                foreach ($encoded as $key => $val) {
                    $decoded = json_decode($val, TRUE);
                    foreach ($decoded as $key => $value) {
                        foreach ($value as $file_key => $file_path) {
                            if (file_exists($file_path)) {
                                exec("/bin/rm -rf " . $file_path);
                            }
                        }
                    }
                }

                // Delete module information from addons table
                $this->db_model->delete("addons", array(
                    "package_name" => $module
                ));
                // ASTPPCOM-982 Ashish Start
                if($module == 'voice_broadcast' ){
                    exec("/bin/rm -rf /opt/ASTPP/web_interface/astpp/upload/voice_broadcast");
                }
                // ASTPPCOM-982 Ashish End
                echo "Module uninstall process completed!!<br/><br/>";
                echo "Please re-login to get impact of addon!! <a href='/logout'>Re-login</a> | <a href='" . base_url() . "addons/addons_list/" . $type . "'>Back</a><br/><br/>";
                exit();
            } else {
                echo "Invalid action!!!";
                exit();
            }
        } else {
            return redirect("<?= base_url()?>addons/addons_list/" . $type);
        }

        $this->load->view('addons', "");
    }

    public function addons_free()
    {
        $this->load->view('addons_free', "");
    }

    public function addons_third_party()
    {
        $this->load->view('addons_third_party', "");
    }

    public function addons_details($type, $package_name, $old_version)
    {
        if (! $this->_valid_addon_args($type, $package_name, $old_version)) {
            show_404();
        }
        $addons_path = $this->config->item('addons_path');
        $data_array['page_title'] = ucwords(str_replace('_', ' ', $package_name));
        $val = '';
        $module_xml = glob($addons_path . $type . "/" . $package_name . $val . "/*.xml");
        if (isset($module_xml) && ! empty($module_xml)) {
            $module_xml = $module_xml[0];
            $xml = file_get_contents($module_xml);
            $module_array = simplexml_load_string($xml);
            $data = $module_array;
            // $i++;
        }
            if(empty($data)){
                $this->session->set_flashdata('astpp_notification', gettext('Permission Denied!'));
                redirect(base_url() . '/dashboard/');
             }
        $data_array['author'] = $data->author;
        $data_array['description'] = $data->description;
        $data_array['new_version'] = $data->version;
        $data_array['old_version'] = $old_version;
        $data_array['package_name'] = $package_name;
        $data_array['addon_name'] = $data->name;
        $data_array['license'] = $data->license;
        $data_array['type'] = $type;

        $query = "select * from addons where package_name='" . $package_name . "'";
        $result = $this->db->query($query);
        $installed_addon_list = (array) $result->first_row();

        // If addon source version is older than installed version then set flag to hide uninstall and update button.
        $data_array['version_error'] = 'false';
        if ($data->version < $old_version) {
            $data_array['version_error'] = 'true';
        }

        $data_array['update_flag'] = 'false';
        if (isset($installed_addon_list) && ! empty($installed_addon_list)) {
            if ($data->version > $installed_addon_list['version']) {
                $data_array['update_flag'] = 'true';
            }
        }
        $data_array['addon_detail_flag'] = true;
        $this->load->view('addons_details', $data_array);
    }

    public function addons_addproduct()
    {
        $this->load->view('add_product_view', "");
    }

    function addons_refill()
    {
        $this->load->view('refill', $data);
    }

    function addons_packages()
    {
        $this->load->view('packages', $data);
    }

    function addons_package_order()
    {
        $this->load->view('package_order', $data);
    }

    // HP: - Trigger issue. pbx addon changes
    private function _get_string_between($str, $from, $to)
    {
        $sub = substr($str, strpos($str, $from) + strlen($from), strlen($str));
        return substr($sub, 0, strpos($sub, $to));
    }

    function addons_enterprise_license($type, $package_name, $action, $new_version, $old_version)
    {
        $data['type'] = $type;
        $data['package_name'] = $package_name;
        $data['action'] = $action;
        $data['new_version'] = $new_version;
        $data['old_version'] = $old_version;

        $this->load->view('view_enterprise_license', $data);
    }
}
 
