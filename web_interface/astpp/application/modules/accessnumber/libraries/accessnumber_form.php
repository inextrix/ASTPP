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
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

#[\AllowDynamicProperties]
class accessnumber_form
{

    protected $CI;

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function get_accessnumber_form_fields($id = false, $country_id = false)
    {
        if (! $country_id) {

            $country = array(
                gettext('Country'),
                array(
                    'name' => 'country_id',
                    'class' => 'country_id'
                ),
                'SELECT',
                '',
                array(
                    "name" => "country_id",
                    "rules" => "required"
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'country',
                'countrycode',
                'build_dropdown_country_camel',
                '',
                ''
            );
        } else {
            $country = array(
                gettext('Country'),
                array(
                    'name' => 'country_id',
                    'class' => 'country_id',
                    'vlaue' => $country_id
                ),
                'SELECT',
                '',
                array(
                    "name" => "country_id",
                    "rules" => "required",
                    'selected' => 'selected'
                ),
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'country',
                'countrycode',
                'build_dropdown_country_camel',
                '',
                ''
            );
        }
        $val = astpp_gt($id, 0) ? 'accessnumber.access_number.' . $id : 'accessnumber.access_number';
        $form['forms'] = array(
            base_url() . 'accessnumber/accessnumber_save/',
            array(
                'id' => 'accessnumber_form',
                'method' => 'POST',
                'name' => 'acessnumber_form'
            )
        );
        $form[gettext('Information')] = array(
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'id'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('Access Number'),
                'INPUT',
                array(
                    'name' => 'access_number',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|is_numeric|xss_clean|integer|is_unique[' . $val . ']',
                'tOOL TIP',
                'Please Enter Access number'
            ),
            array(
                gettext('Description'),
                'INPUT',
                array(
                    'name' => 'description',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim',
                'tOOL TIP',
                'Please Enter Access number'
            ),
            $country,
            array(
                gettext('Status'),
                'status',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Select Status',
                '',
                '',
                '',
                'set_status'
            )
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        return $form;
    }

    function get_accessnumber_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "accessnumber_search"
            )
        );
        $accountinfo = $this->CI->session->userdata('accountinfo');
        if ($accountinfo['type'] == - 1 || $accountinfo['type'] == 2) {
            $search_field_arr = array(

                array(
                    gettext('Access Number'),
                    'INPUT',
                    array(
                        'name' => 'access_number[access_number]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'access_number[access_number-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),

                array(
                    gettext('Country'),
                    'country_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'country',
                    'countrycode',
                    'build_dropdown_country_camel',
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
                array(
                    '',
                    'HIDDEN',
                    'ajax_search',
                    '1',
                    '',
                    '',
                    ''
                ),
                array(
                    '',
                    'HIDDEN',
                    'advance_search',
                    '1',
                    '',
                    '',
                    ''
                )
            );
        } else {
            $search_field_arr = array(

                array(
                    gettext('Access Number'),
                    'INPUT',
                    array(
                        'name' => 'access_number[access_number]',
                        '',
                        'size' => '20',
                        'class' => "text field"
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'access_number[access_number-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),

                array(
                    gettext('Country'),
                    'country_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'country',
                    'countrycode',
                    'build_dropdown_country_camel',
                    '',
                    ''
                ),
                array(
                    '',
                    'HIDDEN',
                    'ajax_search',
                    '1',
                    '',
                    '',
                    ''
                ),
                array(
                    '',
                    'HIDDEN',
                    'advance_search',
                    '1',
                    '',
                    '',
                    ''
                )
            );
        }
        $form[gettext('Search')] = $search_field_arr;
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "accessnumber_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right ml-2'
        );
        return $form;
    }

    function build_accessnumber_list_for_admin()
    {
        $accountinfo = $this->CI->session->userdata('accountinfo');
        if ($accountinfo['type'] == - 1 || $accountinfo['type'] == 2) {
            $grid_field_arr = array(
                array(
                    "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                    "30",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "false",
                    "center"
                ),
                array(
                    gettext("Access Number"),
                    "150",
                    "access_number",
                    "",
                    "",
                    "",
                    "EDITABLE",
                    "true",
                    "left"
                ),
                array(
                    gettext("Country"),
                    "150",
                    "country_id",
                    "country",
                    "countrycode",
                    "get_field_name",
                    "",
                    "true",
                    "center"
                ),

                array(
                    gettext("Created Date"),
                    "150",
                    "creation_date",
                    "creation_date",
                    "creation_date",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Modified Date"),
                    "150",
                    "last_modified_date",
                    "last_modified_date",
                    "last_modified_date",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Status"),
                    "30",
                    "status",
                    "status",
                    "accessnumber",
                    "get_status",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "150",
                    "",
                    "",
                    "",
                    array(
                        "EDIT" => array(
                            "url" => "accessnumber/accessnumber_edit/",
                            "mode" => "popup",
                            "layout" => "medium"
                        ),
                        "DELETE" => array(
                            "url" => "accessnumber/accessnumber_remove/",
                            "mode" => "single"
                        )
                    ),
                    "false"
                )
            );
        } else {
            $grid_field_arr = array(
                array(
                    gettext("Access Number"),
                    "150",
                    "access_number",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "left"
                ),
                array(
                    gettext("Country"),
                    "150",
                    "country_id",
                    "country",
                    "countrycode",
                    "get_field_name",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Created Date"),
                    "150",
                    "creation_date",
                    "creation_date",
                    "creation_date",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Modified Date"),
                    "150",
                    "last_modified_date",
                    "last_modified_date",
                    "last_modified_date",
                    "convert_GMT_to",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "150",
                    "",
                    "",
                    "",
                    array(
                        "EDIT" => array(
                            "url" => "accessnumber/accessnumber_edit/",
                            "mode" => "popup",
                            "layout" => "medium"
                        ),
                        "DELETE" => array(
                            "url" => "accessnumber/accessnumber_remove/",
                            "mode" => "single"
                        )
                    ),
                    "false"
                )
            );
        }

        return json_encode($grid_field_arr);
    }

    function build_grid_buttons()
    {
        $accountinfo = $this->CI->session->userdata('accountinfo');
        if (($accountinfo['type'] == - 1) || ($accountinfo['type'] == 2)) {
            $buttons_json = json_encode(array(
                array(
                    gettext("Create"),
                    "btn btn-line-warning btn",
                    "fa fa-plus-circle fa-lg",
                    "button_action",
                    "/accessnumber/accessnumber_add/",
                    "popup",
                    "medium",
                    "create"
                ),
                array(
                    gettext("Delete"),
                    "btn btn-line-danger",
                    "fa fa-times-circle fa-lg",
                    "button_action",
                    "/accessnumber/accessnumber_delete_multiple/",
                    "",
                    "",
                    "delete"
                ),
                array(
                    gettext("Import"),
                    "btn btn-line-blue",
                    "fa fa-download fa-lg",
                    "button_action",
                    "/accessnumber/accessnumber_import/",
                    '',
                    "small",
                    "import"
                ),
                array(
                    gettext("Export"),
                    "btn btn-xing",
                    "fa fa-upload fa-lg",
                    "button_action",
                    "/accessnumber/accessnumber_export_data_xls",
                    'single',
                    "",
                    "export"
                )
            ));
        } else {
            $buttons_json = json_encode(array(
                array(
                    gettext("Export"),
                    "btn btn-xing",
                    "fa fa-upload fa-lg",
                    "button_action",
                    "/accessnumber/accessnumber_export_data_xls",
                    'single',
                    "",
                    "export"
                )
            ));
        }
        return $buttons_json;
    }
}
?>
