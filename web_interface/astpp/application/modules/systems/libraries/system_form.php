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
class System_form extends common
{

    function get_template_form_fields()
    {
        $form['forms'] = array(
            base_url() . 'systems/template_save/',
            array(
                "template_form",
                "name" => "template_form"
            )
        );
        $form[gettext('Email Template')] = array(
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'name',
                    'size' => '20',
                    'readonly' => true,
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Subject'),
                'INPUT',
                array(
                    'name' => 'subject',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Description'),
                'TEXTAREA',
                array(
                    'name' => 'description',
                    'id' => 'description',
                    'size' => '20',
                    'class' => "description form-control form-control-lg  mt-5"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('SMS Body') . "#Enterprise#" ,
                'TEXTAREA',
                array(
                    'name' => 'sms_template',
                    'id' => 'sms_template',
                    'size' => '20',
                    'class' => "sms_template form-control form-control-lg  mt-5"
                ),
                'trim|required',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Email Body'),
                'TEXTAREA',
                array(
                    'name' => 'template',
                    'id' => 'template',
                    'size' => '20',
                    'class' => "Emailtemplate"
                ),
                'trim|required',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Alert Body') . "#Enterprise#" ,
                'TEXTAREA',
                array(
                    'name' => 'alert_template',
                    'id' => 'alert_template',
                    'size' => '20',
                    'class' => "Emailtemplate2"
                ),
                'trim|required',
                'tOOL TIP',
                ''
            )
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Cancel'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'systems/template/\')'
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'type' => 'submit',
            'class' => 'btn btn-success'
        );

        return $form;
    }

    function get_template_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "template_search"
            )
        );
        $accountinfo = $this->CI->session->userdata('accountinfo');

        if ($accountinfo['type'] == - 1 || $accountinfo['type'] == 2) {
            $form[gettext('Search')] = array(

                array(
                    gettext('Name'),
                    'INPUT',
                    array(
                        'name' => 'name[name]',
                        '',
                        'size' => '20',
                        'class' => "text field "
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
                    gettext('Subject'),
                    'INPUT',
                    array(
                        'name' => 'subject[subject]',
                        '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'subject[subject-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Reseller'),
                    'reseller_id',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Enter account number',
                    'id',
                    'first_name,last_name,number',
                    'accounts',
                    'build_concat_dropdown_reseller',
                    'where_arr',
                    ''
                ),
                array(
                    gettext('Email Status'),
                    'is_email_enable',
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
                    gettext('SMS Status'),
                    'is_sms_enable',
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
                    gettext('Alert Status'),
                    'is_alert_enable',
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
            $form[gettext('Search')] = array(

                array(
                    gettext('Name'),
                    'INPUT',
                    array(
                        'name' => 'name[name]',
                        '',
                        'size' => '20',
                        'class' => "text field "
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
                    gettext('Subject'),
                    'INPUT',
                    array(
                        'name' => 'subject[subject]',
                        '',
                        'size' => '20',
                        'class' => "text field "
                    ),
                    '',
                    'tOOL TIP',
                    '1',
                    'subject[subject-string]',
                    '',
                    '',
                    '',
                    'search_string_type',
                    ''
                ),
                array(
                    gettext('Email Status'),
                    'is_email_enable',
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
                    gettext('SMS Status'),
                    'is_sms_enable',
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
                    gettext('Alert Status'),
                    'is_alert_enable',
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
        }
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "template_search_btn",
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
            'class' => 'btn btn-secondary float-right mx-2'
        );
        return $form;
    }

    function build_timezone_list_for_admin()
    {
        $action = 'systems/timezone_list_edit/';
        $action_remove = 'systems/timezone_remove/';
        $mode = "popup";

        $grid_field_arr = json_encode(array(
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
                gettext("Name"),
                "200",
                "timezone_name",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "center"
            ),
            array(
                gettext("GMT Offset"),
                "250",
                "timezone_name",
                "gmtoffset",
                "timezone",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Action"),
                "265",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "$action",
                        "mode" => "$mode"
                    ),
                    "DELETE" => array(
                        "url" => "$action_remove",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }
    function build_admin_timezone_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "systems/timezone_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "systems/timezone_delete_multiple",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }
    function get_search_timezone_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "timezone_search"
            )
        );
        $form['Search'] = array(

            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'timezone_name[timezone_name]',
                    'id' => 'timezone_name_id',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'timezone_name[timezone_name-string]',
                '',
                '',
                '',
                'search_string_type',
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

        $form['button_search'] = array(
            'name' => 'action',
            'id' => "timezone_search_btn",
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
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }
    function get_timezone_form_fields($id = '')
    {
        $timezone = astpp_gt($id, 0) ? 'timezone.timezone_name.' . $id : 'timezone.timezone_name';
        $timezone_code = astpp_gt($id, 0) ? 'timezone.timezone_name.' . $id : 'timezone.timezone_name';

        $form['forms'] = array(
            base_url() . 'systems/timezone_save/',
            array(
                'id' => 'system_form',
                'method' => 'POST',
                'name' => 'system_form'
            )
        );

        $form[gettext('Timezone List')] = array(
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'timezone_name',
                    'size' => '20',
                    'maxlength' => '40',
                    'class' => "text field medium"
                ),
                'trim|required|is_unique[' . $timezone . ']|min_length[2]|max_length[30]|xss_clean',
                'tOOL TIP',
                'Please Enter Timezone'
            ),
           
            array(
                gettext('GMT Offset'),
                'INPUT',
                array(
                    'name' => 'gmtoffset',
                    'size' => '20',
                    'maxlength' => '7',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter gmtoffset'
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }
    function get_configuration_form_fields()
    {
        $form['forms'] = array(
            base_url() . 'systems/configuration_save/',
            array(
                "id" => "config_form",
                "name" => "config_form"
            )
        );
        $form[gettext('Edit Settings ')] = array(
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'name',
                    'size' => '20',
                    'readonly' => true,
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Value'),
                'INPUT',
                array(
                    'name' => 'value',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Comment'),
                'INPUT',
                array(
                    'name' => 'comment',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            )
        );

        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Cancel'),
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

    function get_configuration_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "configuration_search"
            )
        );
        $form[gettext('Search')] = array(

            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'name[name]',
                    '',
                    'size' => '20',
                    'class' => "text field "
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
                gettext('Value'),
                'INPUT',
                array(
                    'name' => 'value[value]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'value[value-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Description'),
                'INPUT',
                array(
                    'name' => 'comment[comment]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'comment[comment-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Group'),
                'group_title',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'group_title',
                'group_title',
                'system',
                'build_dropdown',
                'where_arr',
                "group_title NOT IN ('asterisk','osc','freepbx')",
                'group_by',
                'group_title'
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
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "configuration_search_btn",
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success pull-right'
        );
        $form['button_reset'] = array(
            'name' => 'action',
            'id' => "id_reset",
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary pull-right ml-2'
        );
        return $form;
    }

    function build_system_list_for_admin()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Name"),
                "190",
                "name",
                "",
                "",
                ""
            ),
            array(
                gettext("Value"),
                "190",
                "value",
                "",
                "",
                ""
            ),
            array(
                gettext("Description"),
                "320",
                "comment",
                "",
                "",
                ""
            ),
            array(
                gettext("Group"),
                "120",
                "group_title",
                "",
                "",
                ""
            ),
            array(
                gettext("Action"),
                "442",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "systems/configuration_edit/",
                        "mode" => "popup"
                    )
                )
            )
        ));
        return $grid_field_arr;
    }

    function build_grid_buttons()
    {
        $buttons_json = json_encode(array());
        return $buttons_json;
    }

    function build_template_list_for_admin()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Name"),
                "150",
                "name",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("Subject"),
                "200",
                "subject",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Description"),
                "250",
                "description",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Reseller"),
                "100",
                "reseller_id",
                "first_name,last_name,number",
                "accounts",
                "reseller_select_value"
            ),
            array(
                gettext("Email Status"),
                "70",
                "is_email_enable",
                "is_email_enable",
                "default_templates",
                "get_email_status",
                "",
                "true",
                "center"
            ),
            array(
                gettext("SMS Status"),
                "70",
                "is_sms_enable",
                "is_sms_enable",
                "default_templates",
                "get_sms_status",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Alert Status"),
                "70",
                "is_alert_enable",
                "is_alert_enable",
                "default_templates",
                "get_alert_status",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Action"),
                "130",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "systems/template_edit/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_country_list_for_admin()
    {
        $action = 'systems/country_list_edit/';
        $action_remove = 'systems/country_remove/';
        $mode = 'popup';
        $grid_field_arr = json_encode(array(
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
                gettext("Name"),
                "180",
                "country",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("Nickname"),
                "180",
                "nicename",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),

            array(
                gettext("Capital"),
                "170",
                "capital",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Iso"),
                "150",
                "iso",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Iso3"),
                "150",
                "iso3",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Country Code"),
                "150",
                "countrycode",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Currency"),
                "150",
                "currency_id",
                "currencyname,currency",
                "currency",
                "build_concat_string",
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
                        "url" => "$action",
                        "mode" => "$mode"
                    ),
                    "DELETE" => array(
                        "url" => "$action_remove",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_admin_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "systems/country_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "systems/country_delete_multiple",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function get_search_country_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "country_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'country[country]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'country[country-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Nickname'),
                'INPUT',
                array(
                    'name' => 'nicename[nicename]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'nicename[nicename-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Capital'),
                'INPUT',
                array(
                    'name' => 'capital[capital]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'capital[capital-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('ISO'),
                'INPUT',
                array(
                    'name' => 'iso[iso]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'iso[iso-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('ISO3'),
                'INPUT',
                array(
                    'name' => 'iso3[iso3]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'iso3[iso3-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Country Code'),
                'INPUT',
                array(
                    'name' => 'countrycode[countrycode]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'countrycode[countrycode-integer]',
                '',
                '',
                '',
                'search_int_type',
                ''
            ),
            array(
                gettext('Currency'),
                'currency_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'currencyname,currency',
                'currency',
                'build_concat_dropdown_currency',
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

        $form['button_search'] = array(
            'name' => 'action',
            'id' => "country_search_btn",
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
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }

    function get_country_form_fields($id = '')
    {
        $iso = astpp_gt($id, 0) ? 'countrycode.iso.' . $id : 'countrycode.iso';
        $form['forms'] = array(
            base_url() . 'systems/country_save/',
            array(
                'id' => 'system_form',
                'method' => 'POST',
                'name' => 'system_form'
            )
        );
        $form[gettext('Country List')] = array(
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'country',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|min_length[2]|max_length[30]|xss_clean',
                'tOOL TIP',
                'Please Enter country'
            ),
            array(
                gettext('Nickname'),
                'INPUT',
                array(
                    'name' => 'nicename',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|min_length[2]|max_length[30]|xss_clean',
                'tOOL TIP',
                'Please Enter nickname'
            ),
            array(
                gettext('Capital'),
                'INPUT',
                array(
                    'name' => 'capital',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|min_length[2]|max_length[30]|xss_clean',
                'tOOL TIP',
                'Please Enter Capital'
            ),

            array(
                gettext('Iso'),
                'INPUT',
                array(
                    'name' => 'iso',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|is_unique[' . $iso . ']|char|xss_clean',
                'tOOL TIP',
                'Please Enter ios related to country'
            ),

            array(
                gettext('Iso3'),
                'INPUT',
                array(
                    'name' => 'iso3',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|char|xss_clean',
                'tOOL TIP',
                'Please Enter ios3 related to country'
            ),

            array(
                gettext('Country Code'),
                'INPUT',
                array(
                    'name' => 'countrycode',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|numeric|xss_clean',
                'tOOL TIP',
                'Please Enter countrycode'
            ),
            array(
                gettext('Currency'),
                'currency_id',
                'SELECT',
                '',
                'required',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'currencyname,currency',
                'currency',
                'build_concat_dropdown_currency',
                '',
                ''
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function build_currency_list_for_admin()
    {
        $action = 'systems/currency_list_edit/';
        $action_remove = 'systems/currency_remove/';
        $mode = "popup";

        $grid_field_arr = json_encode(array(
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
                gettext("Name"),
                "400",
                "currencyname",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("Code"),
                "250",
                "currency",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Rate"),
                "250",
                "currencyrate",
                "currencyrate",
                "currencyrate",
                "decimal_currency",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Action"),
                "265",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "$action",
                        "mode" => "$mode"
                    ),
                    "DELETE" => array(
                        "url" => "$action_remove",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function get_search_currency_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "currency_search"
            )
        );
        $form['Search'] = array(

            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'currencyname[currencyname]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'currencyname[currencyname-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'currency[currency]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'currency[currency-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Rate'),
                'INPUT',
                array(
                    'name' => 'currencyrate[currencyrate]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'currencyrate[currencyrate-integer]',
                '',
                '',
                '',
                'search_int_type',
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

        $form['button_search'] = array(
            'name' => 'action',
            'id' => "currency_search_btn",
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
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }

    function get_currency_form_fields($id = '')
    {
        $currency = astpp_gt($id, 0) ? 'currency.currencyname.' . $id : 'currency.currencyname';
        $currency_code = astpp_gt($id, 0) ? 'currency.currency.' . $id : 'currency.currency';

        $form['forms'] = array(
            base_url() . 'systems/currency_save/',
            array(
                'id' => 'system_form',
                'method' => 'POST',
                'name' => 'system_form'
            )
        );

        $form[gettext('Currency List')] = array(
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'currencyname',
                    'size' => '20',
                    'maxlength' => '40',
                    'class' => "text field medium"
                ),
                'trim|required|is_unique[' . $currency . ']|min_length[2]|max_length[30]|xss_clean',
                'tOOL TIP',
                'Please Enter country'
            ),
            array(
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'currency',
                    'size' => '20',
                    'maxlength' => '3',
                    'class' => "text field medium"
                ),
                'trim|required|is_unique[' . $currency . ']|alpha|xss_clean',
                'tOOL TIP',
                'Please Enter country'
            ),
            array(
                gettext('Rate'),
                'INPUT',
                array(
                    'name' => 'currencyrate',
                    'size' => '20',
                    'maxlength' => '7',
                    'class' => "text field medium"
                ),
                'trim|required|numeric|xss_clean',
                'tOOL TIP',
                'Please Enter country'
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function build_admin_currency_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "systems/currency_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Update Currencies"),
                "btn btn-line-blue",
                "fa fa-upload fa-lg",
                "button_action",
                "currencyupdate/update_currency/",
                'single',
                "",
                "currencies_update"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "systems/currency_delete_multiple",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function get_backup_database_form_fields($file_name, $id = '')
    {
        $val = astpp_gt($id, 0) ? "backup_database.path.$id" : 'backup_database.path';
        $form['forms'] = array(
            base_url() . 'systems/database_backup_save/',
            array(
                'id' => 'backup_form',
                'method' => 'POST',
                'name' => 'backup_form'
            )
        );
        $form[gettext('Database Information')] = array(
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'backup_name',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'required',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('File Name'),
                'INPUT',
                array(
                    'name' => 'path',
                    'size' => '20',
                    'value' => $file_name,
                    'class' => "text field medium"
                ),
                'trim|required|is_unique[' . $val . ']',
                'tOOL TIP',
                ''
            )
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary  ml-2',
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

    function build_backupdastabase_list()
    {
        $grid_field_arr = json_encode(array(
            array(
                "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                "50",
                "",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Date"),
                "260",
                "date",
                "date",
                "date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Name"),
                "295",
                "backup_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("File Name"),
                "400",
                "path",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Action"),
                "180",
                "",
                "",
                "",
                array(
                    "EDIT_RESTORE" => array(
                        "url" => "systems/database_restore_one/",
                        "mode" => ""
                    ),
                    "DOWNLOAD_DATABASE" => array(
                        "url" => "systems/database_download/",
                        "mode" => ""
                    ),
                    "Delete" => array(
                        "url" => "systems/database_delete/",
                        "mode" => ""
                    )
                ),
                "",
                "true",
                "center"
            )
        ));
        return $grid_field_arr;
    }

    function build_backupdastabase_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "systems/database_backup/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Import"),
                "btn btn-line-blue",
                "fa fa-upload fa-lg",
                "button_action",
                "systems/database_import/",
                "single",
                "",
                "import"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "systems/database_backup_delete_multiple",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function build_languages_list_for_admin()
    {
        $action = 'systems/languages_list_edit/';
        $action_remove = 'systems/languages_remove/';
        $mode = "popup";

        $grid_field_arr = json_encode(array(
            array(
                "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                "00",
                "",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Name"),
                "150",
                "name",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Languages"),
                "150",
                "code",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Locale code"),
                "150",
                "locale",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Action"),
                "100",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "$action",
                        "mode" => "$mode"
                    ),
                    "DELETE" => array(
                        "url" => "$action_remove",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }
	function get_default_languages_form_fields($id = '')
    {
       
        $form['forms'] = array(
            base_url() . 'systems/languages_set_default/',
            array(
                'id' => 'default_language_form',
                'method' => 'POST',
                'name' => 'default_language_form'
            )
        );
        $form['Languages'] = array(
            array(
                gettext('Default Language'),
                'name',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'language',
                'languages',
                'build_dropdown_languages',
                'where_arr',
                ''
            ),
            
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }
    function build_admin_languages_grid_buttons()
    {
        $buttons_json = json_encode(array(
             // array(
            //     gettext("Create"),
            //     "btn btn-line-warning btn",
            //     "fa fa-plus-circle fa-lg",
            //     "button_action",
            //     "systems/languages_add/",
            //     "popup",
            //     "",
            //     "create"
            // ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "systems/languages_delete_multiple",
                "",
                "",
                "delete"
            ),
            array(
                gettext("Update Languages"),
                "btn btn-line-blue",
                "fa fa-upload fa-lg",
                "button_action",
                "Translation_script/",
                'single',
                "",
                "update"
            ),
	array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "systems/languages_export",
                '',
                "",
                "delete"
            ),
	 array(
                gettext("Default Language"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "systems/languages_default/",
                "popup",
                "",
                "create"
            ),
        ));
        return $buttons_json;
    }

    function get_search_languages_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "languages_search"
            )
        );
        $form[gettext('Search')] = array(

            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'name[name]',
                    '',
                    'size' => '20',
                    'class' => "text field "
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
                gettext('Languages'),
                'INPUT',
                array(
                    'name' => 'code[code]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'code[code-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Locale code'),
                'INPUT',
                array(
                    'name' => 'locale[locale]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'locale[locale-integer]',
                '',
                '',
                '',
                'search_int_type',
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

        $form['button_search'] = array(
            'name' => 'action',
            'id' => "languages_search_btn",
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
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }

    function get_language_form_fields()
    {
        $form['forms'] = array(
            base_url() . 'systems/languages_save/',
            array(
                'id' => 'system_form',
                'method' => 'POST',
                'name' => 'system_form'
            )
        );
        $form['Currency List'] = array(
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'name',
                    'size' => '20',
                    'maxlength' => '40',
                    'class' => "text field medium"
                ),
                'trim|required|char|xss_clean',
                'tOOL TIP',
                'Please Enter country'
            ),
            array(
                gettext('Languages'),
                'INPUT',
                array(
                    'name' => 'code',
                    'size' => '20',
                    'maxlength' => '3',
                    'class' => "text field medium"
                ),
                'trim|required|char|xss_clean',
                'tOOL TIP',
                'Please Enter country'
            ),
            array(
                gettext('Locale code'),
                'INPUT',
                array(
                    'name' => 'locale',
                    'size' => '20',
                    'maxlength' => '7',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter country'
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function get_languages_form_fields($id = '')
    {
        $name = astpp_gt($id, 0) ? 'languages.name.' . $id : 'languages.name';
        $language = astpp_gt($id, 0) ? 'languages.code.' . $id : 'languages.code';
        $locale = astpp_gt($id, 0) ? 'languages.locale.' . $id : 'languages.locale';
        $form['forms'] = array(
            base_url() . 'systems/languages_save/',
            array(
                'id' => 'system_form',
                'method' => 'POST',
                'name' => 'system_form'
            )
        );
        $form['Languages'] = array(
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'name',
                    'size' => '20',
                    'maxlength' => '40',
                    'class' => "text field medium"
                ),
                'trim|required|char|xss_clean|alpha|is_unique[' . $name . ']',
                'tOOL TIP',
                'Please Enter country'
            ),
            array(
                gettext('Language'),
                'INPUT',
                array(
                    'name' => 'code',
                    'size' => '20',
                    'maxlength' => '3',
                    'class' => "text field medium"
                ),
                'trim|required|char|xss_clean|alpha|is_unique[' . $language . ']',
                'tOOL TIP',
                'Please Enter country'
            ),
            array(
                gettext('Locale code'),
                'INPUT',
                array(
                    'name' => 'locale',
                    'size' => '20',
                    'maxlength' => '7',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean|is_unique[' . $locale . ']',
                'tOOL TIP',
                'Please Enter country'
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function build_translation_list_for_admin_translation()
    {
        $action = 'systems/translation_list_edit/';
        $action_remove = 'systems/translation_remove/';

        $grid_field_arr = json_encode(array(
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
                gettext("Module"),
                "75",
                "module_name",
                "",
                "",
                "",
                "edit",
                "true",
                "center"
            ),
            array(
                gettext("en"),
                "80",
                "en_Es",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),

            array(
                gettext("Action"),
                "120",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => $action,
                        "mode" => "single"
                    ),
                    "DELETE" => array(
                        "url" => $action_remove,
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_translation_list_for_admin($opting_id = "")
    {
        $data = $this->CI->db->list_fields('translations');
        foreach ($data as $key => $value) {
            if ($value == 'id') {
                $fields[$key] = array(
                    "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                    "00",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "false",
                    "center"
                );
            } else if ($value == 'module_name') {
                $fields[$key] = array(
                    gettext($value),
                    "150",
                    $value,
                    "",
                    "",
                    "",
                    "EDITABLE",
                    "true",
                    "left"
                );
            } else {
                $language_name = (array) $this->CI->db->get_where('languages', array(
                    'locale' => $value
                ))->first_row();
                $fields[$key] = array(
                    gettext($language_name['name']),
                    "150",
                    $language_name['locale'],
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "left"
                );
            }
        }
        $fields[] = array(
            gettext("Action"),
            "120",
            "",
            "",
            "",
            array(
                "EDIT" => array(
                    "url" => "systems/translation_list_edit/",
                    "mode" => "single"
                ),
                "DELETE" => array(
                    "url" => "systems/translation_remove/",
                    "mode" => "single"
                )
            ),
            "false"
        );
        $fields = json_encode($fields);
        return $fields;
    }

    function translation_build_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/systems/translation_add/",
                "single",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/systems/translation_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function build_block_pattern_list_for_translation($productid = "")
    {
        // array(display name, width, db_field_parent_table,feidname, db_field_child_table,function name);
        $grid_field_arr = json_encode(array(

            array(
                gettext("Code"),
                "350",
                "pattern",
                "pattern",
                "",
                "get_only_numeric_val"
            ),
            array(
                gettext("Module"),
                "75",
                "module_name",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("en"),
                "80",
                "en_Es",
                "",
                "",
                "",
                "",
                "true",
                "center"
            )
            /*
         * array (
         * gettext ( "Action" ),
         * "100",
         * "",
         * "",
         * "",
         * array (
         * "DELETE" => array (
         * "url" => "products/products_patterns_delete/$packageid/",
         * "mode" => "single"
         * )
         * )
         * )
         */
        ));
        return $grid_field_arr;
    }

    function get_translation_form_fields($fields_data, $data_fields = '')
    {
        $id[] = array(
            '',
            'HIDDEN',
            array(
                'name' => 'id'
            ),
            '',
            '',
            '',
            ''
        );
        unset($fields_data['0']);
        foreach ($fields_data as $key => $value) {

            if ($value == 'module_name') {
                $module = str_replace("_", " ", $value);
                $module_name = ucwords($module);
                $data[$key] = array(
                    gettext($module),
                    'INPUT',
                    array(
                        'name' => $value,
                        'size' => '20',
                        'maxlength' => '255',
                        'class' => "text field medium"
                    ),
                    'trim|required|char|xss_clean',
                    'tOOL TIP',
                    'Please Enter country'
                );
            } else {
                $data[$key] = array(
                    gettext($value),
                    'INPUT',
                    array(
                        'name' => $value,
                        'size' => '20',
                        'maxlength' => '255',
                        'class' => "text field medium"
                    ),
                    'trim|required|char|xss_clean',
                    'tOOL TIP',
                    'Please Enter country'
                );
            }
        }

        $form['forms'] = array(
            base_url() . 'systems/translation_save/',
            array(
                'id' => 'system_form',
                'method' => 'POST',
                'name' => 'system_form'
            )
        );

        $form['Translations Language'] = astpp_array_merge($data, $id);

        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'submit',
            'class' => 'btn btn-success'
        );
        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Cancel'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => 'return redirect_page(\'/systems/translation_list/\')'
        );
        return $form;
    }

}

?>

