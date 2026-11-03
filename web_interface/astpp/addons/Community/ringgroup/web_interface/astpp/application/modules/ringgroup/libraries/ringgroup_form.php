<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
#[\AllowDynamicProperties]
class ringgroup_form extends common {
    function __construct($library_name = '') {
        $this->CI = & get_instance();
    }
    function set_search_strategy($select = '') {
        $status_array = array("" => "--Select--",
            "sequence" => "Sequence",
            "simultaneous" => "Simultaneous"
        );
        return $status_array;
    }
    function get_ringgroup_form_fields() {
        $form['forms'] = array(base_url() . 'ringgroup/ringgroup_save/', array('id' => 'trunks_form', 'method' => 'POST', 'name' => 'trunks_form'));
        $form[gettext('Trunk Information')] = array(
            array('', 'HIDDEN', array('name' => 'id'), '', '', '', ''),
            array(gettext('Name'), 'INPUT', array('name' => 'name', 'size' => '20', 'maxlength' => '30', 'class' => "text field medium"), 'trim|required|min_length[2]|max_length[25]|xss_clean', 'tOOL TIP', ''),
            array(gettext('Account'), 'accountid', 'SELECT', '', 'trim|required|xss_clean', 'tOOL TIP', 'Please Enter account number', 'id', 'first_name,last_name,number,company_name', 'accounts', 'build_concat_dropdown', 'where_arr',array('type'=>3,"deleted"=>"0","status"=>"0")),
            array(gettext('Strategy'), 'strategy', 'SELECT', '', 'trim|required|xss_clean', 'tOOL TIP', 'Please select gateway first', 'id', 'name', 'gateways', 'build_dropdown','where_arr', array("status" => "0")),
            array(gettext('Fail Over Gateway'), 'failover_gateway_id', 'SELECT', '', '', 'tOOL TIP', 'Please select gateway first', 'id', 'name', 'gateways', 'build_dropdown', 'where_arr', array("status" => "0")),
            array(gettext('Fail Over Gateway'), 'failover_gateway_id1', 'SELECT', '', '', 'tOOL TIP', 'Please select gateway first', 'id', 'name', 'gateways', 'build_dropdown', 'where_arr', array("status" => "0")),            
            array(gettext('Max Channels'), 'INPUT', array('name' => 'maxchannels', 'value' => '0' , 'size' => '20', 'maxlength' => '4', 'class' => "text field medium"), '', 'tOOL TIP', ''));
        $form[gettext('Trunk Settings')] = array(    
            array(gettext('Number Translation'), 'INPUT', array('name' => 'dialed_modify', 'size' => '20', 'maxlength' => '200', 'class' => "text field medium"), '', 'tOOL TIP', ''),
            array(gettext('Codecs'), 'INPUT', array('name' => 'codec', 'size' => '20', 'maxlength' => '100', 'class' => "text field medium"), 'trim|xss_clean', 'tOOL TIP', ''),
            array(gettext('Precedence'), 'INPUT', array('name' => 'precedence', 'size' => '20', 'maxlength' => '4', 'class' => "text field medium"), '', 'tOOL TIP', ''),
            array(gettext('Status'), 'status', 'SELECT', '', '', 'tOOL TIP', 'Please Select Status', '', '', '', 'set_status'),
        );
        $form['button_cancel'] = array('name' => 'action', 'content' => gettext('Cancel'), 'value' => 'cancel', 'type' => 'button', 'class' => 'btn btn-primary margin-x-10', 'onclick' => 'return redirect_page(\'NULL\')');
        $form['button_save'] = array('name' => 'action', 'content' => gettext('Save'), 'value' => 'save', 'id' => 'submit', 'type' => 'button', 'class' => 'btn btn-success');
        return $form;
    }

    function get_ringgroup_search_form() {
        $form['forms'] = array("", array('id' => "ringgroup_search"));
        $account_info = $this->CI->session->userdata('accountinfo');
        
        if ($account_info['type'] == -1 || $account_info['type'] == 2) {
            $form['Search'] = 
            array(
             array(
                gettext('Name'), 
                'INPUT', 
                array(
                    'name' => 'name[name]', 
                    '',
                    'size' => '20',
                    'class' => "text field"
                ), 
                '',
                'tOOL TIP',
                '1', 
                'name[name-string]',
                '', 
                '', 
                '',
                'search_string_type',
                ''
            ),

             array(
                gettext('Reseller'),
                array(
                    'name' => 'reseller_id',
                    'class' => 'reseller',
                    'onchange' => 'account_change_add(this.value)'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',                           
                'first_name,last_name,number,company_name',  
                'accounts',
                'build_concat_dropdown_reseller', 
                '',
                ''
            ),
             array(
                gettext('Account'),
                array(
                    'name' => 'accountid',
                    'id' => 'accountcode'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',                           
                'first_name,last_name,number,company_name',  
                'accounts',
                'build_concat_dropdown', 
                'where_arr',
                array(
                    "reseller_id" => $account_info['type'],
                    "type"=>"0",
                    "deleted" => "0",
                    "status"=>0
                )
            ),

             array(
                gettext('Ring Strategy'), 
                'strategy', 
                'SELECT', 
                '', 
                '', 
                'tOOL TIP', 
                'Please Enter account number', 
                '', 
                '', 
                '', 
                'set_search_strategy', 
                '', 
                ''
            ),
             array(
                gettext('Status'), 
                'status', 
                'SELECT', 
                '', 
                '', 
                'tOOL TIP', 
                'Please Enter account number', 
                '', 
                '', 
                '', 
                'set_search_status', 
                '', 
                ''
            ),
             array('', 'HIDDEN', 'ajax_search', '1', '', '', ''),
             array('', 'HIDDEN', 'advance_search', '1', '', '', ''),
         );
        }elseif($account_info['type'] == 1){
            $form['Search'] = 
            array(
             array(
                gettext('Name'), 
                'INPUT', 
                array(
                    'name' => 'name[name]', 
                    '',
                    'size' => '20',
                    'class' => "text field"
                ), 
                '',
                'tOOL TIP',
                '1', 
                'name[name-string]',
                '', 
                '', 
                '',
                'search_string_type',
                ''
            ),
             array(
                gettext('Account'),
                array(
                    'name' => 'accountid'
                    
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',                           
                'first_name,last_name,number,company_name',  
                'accounts',
                'build_concat_dropdown', 
                'where_arr',
                array(
                    "reseller_id" => $account_info['type'],
                    "type"=>"0",
                    "deleted" => "0",
                    "status"=>0
                )
            ),

             array(
                gettext('Ring Strategy'), 
                'strategy', 
                'SELECT', 
                '', 
                '', 
                'tOOL TIP', 
                'Please Enter account number', 
                '', 
                '', 
                '', 
                'set_search_strategy', 
                '', 
                ''
            ),
             array(
                gettext('Status'), 
                'status', 
                'SELECT', 
                '', 
                '', 
                'tOOL TIP', 
                'Please Enter account number', 
                '', 
                '', 
                '', 
                'set_search_status', 
                '', 
                ''
            ),
             array('', 'HIDDEN', 'ajax_search', '1', '', '', ''),
             array('', 'HIDDEN', 'advance_search', '1', '', '', ''),
         );
            
        }else{
            $form['Search'] = 
            array(
             array(
                gettext('Name'), 
                'INPUT', 
                array(
                    'name' => 'name[name]', 
                    '',
                    'size' => '20',
                    'class' => "text field"
                ), 
                '',
                'tOOL TIP',
                '1', 
                'name[name-string]',
                '', 
                '', 
                '',
                'search_string_type',
                ''
            ),
             array(
                gettext('Ring Strategy'), 
                'strategy', 
                'SELECT', 
                '', 
                '', 
                'tOOL TIP', 
                'Please Enter account number', 
                '', 
                '', 
                '', 
                'set_search_strategy', 
                '', 
                ''
            ),
             array(
                gettext('Status'), 
                'status', 
                'SELECT', 
                '', 
                '', 
                'tOOL TIP', 
                'Please Enter account number', 
                '', 
                '', 
                '', 
                'set_search_status', 
                '', 
                ''
            ),
             array('', 'HIDDEN', 'ajax_search', '1', '', '', ''),
             array('', 'HIDDEN', 'advance_search', '1', '', '', ''),
         );
            
        }
        $form['button_search'] = array('name' => 'action', 'id' => "ringgroup_search_btn", 'content' => gettext('Search'), 'value' => 'save', 'type' => 'button', 'class' => 'btn btn-success pull-right');
        $form['button_reset'] = array('name' => 'action', 'id' => "id_reset", 'content' => gettext('Clear'), 'value' => 'cancel', 'type' => 'reset', 'class' => 'btn btn-secondary pull-right margin-x-10');
        
        return $form;
    }
    function build_ringgroup_list_for_admin() {
        $account_data = $this->CI->session->userdata ( "accountinfo" );
        if($account_data['type'] == -1 || $account_data['type'] == 2 ) {
            $grid_field_arr = json_encode(array(array("<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>", "30", "", "", "", ""),
                array(gettext("Name"), "100", "name", "", "", "","EDITABLE","","left"),
                array(
                    gettext("Reseller"), 
                    "150",
                    "reseller_id", 
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "reseller_select_value"
                ),
                array(
                    gettext ( "Account" ),
                    "150",
                    "accountid",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "get_field_name_coma_new"
                ),
                array(gettext("Ring Strategy"), "150", "strategy", "", "", ""),
                array(gettext("Description"), "270", "description", "", "", ""),
                array(
                    gettext("Status"),
                    "100",
                    "status",
                    "status",
                    "pbx_ringgroup",
                    "get_status",
                    "",
                    "true",
                    "center"),
                array("Action", "130", "", "", "", array("EDIT" => array("url" => "ringgroup/ringgroup_edit/", "mode" => "single"),
                    "DELETE" => array("url" => "ringgroup/ringgroup_delete/", "mode" => "single")),"false")
            ));
        }elseif($account_data['type'] == 1){
            $grid_field_arr = json_encode(array(array("<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>", "30", "", "", "", ""),
                array(gettext("Name"), "100", "name", "", "", "","EDITABLE","","left"),
                array(
                    gettext ( "Account" ),
                    "150",
                    "accountid",
                    "first_name,last_name,number,company_name",
                    "accounts",
                    "get_field_name_coma_new"
                ),
                array(gettext("Ring Strategy"), "170", "strategy", "", "", ""),
                array(gettext("Description"), "270", "description", "", "", ""),
                array(
                    gettext("Status"),
                    "100",
                    "status",
                    "status",
                    "pbx_ringgroup",
                    "get_status",
                    "",
                    "true",
                    "center"),
                array("Action", "130", "", "", "", array("EDIT" => array("url" => "ringgroup/ringgroup_edit/", "mode" => "single"),
                    "DELETE" => array("url" => "ringgroup/ringgroup_delete/", "mode" => "single")),"false")
            ));
        }else{
           $grid_field_arr = json_encode(array(array("<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>", "30", "", "", "", ""),
            array(gettext("Name"), "100", "name", "", "", "","EDITABLE","","left"),
            
            array(gettext("Ring Strategy"), "170", "strategy", "", "", ""),
            array(gettext("Description"), "270", "description", "", "", ""),
            array(
                gettext("Status"),
                "100",
                "status",
                "status",
                "pbx_ringgroup",
                "get_status",
                "",
                "true",
                "center"),
            array("Action", "130", "", "", "", array("EDIT" => array("url" => "ringgroup/ringgroup_edit/", "mode" => "single"),
                "DELETE" => array("url" => "ringgroup/ringgroup_delete/", "mode" => "single")),"false")
        ));
       }
       return $grid_field_arr;
   }
   function build_grid_buttons() {
    $buttons_json = json_encode(
        array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/ringgroup/ringgroup_add/",
                "",
                "",
                "create"
            ),
            array(
                gettext("Delete"), 
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/ringgroup/ringgroup_delete_multiple/",
                "",
                "",
                "delete"
            )
        )
    );
    return $buttons_json;
}
}
