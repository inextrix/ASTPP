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
class Ratedeck_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function get_ratedeck_form_fields()
    {
        $form['forms'] = array(
            base_url() . 'ratedeck/ratedeck_save/',
            array(
                'id' => 'ratedeck_form',
                'method' => 'POST',
                'name' => 'ratedeck_form'
            )
        );
        $form[gettext('Ratedeck Information')] = array(
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
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'pattern',
                    'size' => '20',
                    'maxlength' => '39',
                    'class' => "text field medium"
                ),
                'trim|required|numeric|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Destination'),
                'INPUT',
                array(
                    'name' => 'destination',
                    'size' => '20',
                    'maxlength' => '50',
                    'class' => "text field medium"
                ),
                'trim|required',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Call Type'),
                array(
                    'name' => 'call_type',
                    'class' => 'call_type'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'call_type',
                'calltype',
                'build_dropdown',
                'where_arr',
                array(
                    'status' => 0
                )
            ),
            array(
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

    function get_search_ratedeck_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "ratedeck_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Code'),
                'INPUT',
                array(
                    'name' => 'pattern[pattern]',
                    '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '1',
                'pattern[pattern-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Destination'),
                'INPUT',
                array(
                    'name' => 'destination[destination]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'destination[destination-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Call Type'),
                'call_type',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'call_type',
                'calltype',
                'build_dropdown',
                'where_arr',
                array(
                    'status' => 0
                )
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
        $form['button_search'] = array(
            'name' => 'action',
            'id' => "ratedeck_search_btn",
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

    function build_ratedeck_list_for_admin()
    {
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
                gettext("Code"),
                "150",
                "pattern",
                "pattern",
                "",
                "get_only_numeric_val",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("Destination"),
                "150",
                "destination",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Call Type"),
                "150",
                "call_type",
                "call_type",
                "calltype",
                "get_field_name",
                "",
                "true",
                "center"
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
                gettext("Action"),
                "120",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "ratedeck/ratedeck_edit/",
                        "mode" => "popup"
                    ),
                    "DELETE" => array(
                        "url" => "ratedeck/ratedeck_delete/",
                        "mode" => "single"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }

    function build_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/ratedeck/ratedeck_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/ratedeck/ratedeck_delete_multiple/",
                "",
                "",
                "delete"
            ),
            array(
                gettext("Import"),
                "btn btn-line-blue",
                "fa fa-download fa-lg",
                "button_action",
                "/ratedeck/ratedeck_import/",
                'single',
                "",
                'import'
            ),
            array(
                gettext("Export"),
                "btn btn-xing",
                "fa fa-upload fa-lg",
                "button_action",
                "/ratedeck/ratedeck_export/",
                'single',
                "",
                "export"
            )
        ));
        return $buttons_json;
    }
}
?>
