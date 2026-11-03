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
class Cronsettings_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function build_cron_list_for_admin()
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
                "left"
            ),
            array(
                gettext("Name"),
                "130",
                "name",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("Command"),
                "130",
                "file_path",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Interval Type"),
                "100",
                "command",
                "command",
                "command",
                "get_cron_type",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Interval"),
                "130",
                "exec_interval",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Last Execution Date"),
                "130",
                "last_execution_date",
                "last_execution_date",
                "last_execution_date",
                "convert_GMT_to",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Next Execution Date"),
                "150",
                "next_execution_date",
                "next_execution_date",
                "next_execution_date",
                "convert_GMT_to",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Creation Date"),
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
                gettext("Last Modified Date"),
                "150",
                "last_modified_date",
                "last_modified_date",
                "last_modified_date",
                "convert_GMT_to",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Status"),
                "90",
                "status",
                "status",
                "cron_settings",
                "get_status",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Action"),
                "140",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "cronsettings/cronsettings_edit/",
                        "mode" => "popup"
                    ),
                    "DELETE" => array(
                        "url" => "cronsettings/cronsettings_delete/",
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
                "/cronsettings/cronsettings_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/cronsettings/cronsettings_multiple_delete/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }

    function get_cronsettings_form_fields($id = false)
    {
        $form['forms'] = array(
            base_url() . '/cronsettings/cronsettings_save/',
            array(
                'id' => 'cron_form',
                'method' => 'POST',
                'name' => 'cron_form'
            )
        );

        $form['Cron Settings'] = array(

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
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter Name'
            ),

            array(
                gettext('Command'),
                'INPUT',
                array(
                    'name' => 'file_path',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter Name'
            ),
            array(
                gettext('Interval Type'),
                'command',
                'SELECT',
                '',
                'trim|required|xss_clean',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_cron_type',
                ''
            ),
            array(
                gettext('Interval'),
                'INPUT',
                array(
                    'name' => 'exec_interval',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Next Execution Date'),
                'INPUT',
                array(
                    'id' => 'exeution_date',
                    'name' => 'next_execution_date',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                '',
                'tOOL TIP',
                ''
            ),
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

    function get_search_cron_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "cron_search"
            )
        );
        $form['Search'] = array(
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
                gettext('Interval Type'),
                'command',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '',
                '',
                '',
                'set_search_cron_type',
                '',
                ''
            ),
            array(
                gettext('Interval'),
                'INPUT',
                array(
                    'name' => 'exec_interval[exec_interval]',
                    '',
                    'size' => '20',
                    'class' => "text field"
                ),
                '',
                'tOOL TIP',
                '1',
                'exec_interval[exec_interval-string]',
                '',
                '',
                '',
                'search_string_type',
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

        $form['button_search'] = array(
            'name' => 'action',
            'id' => "cron_search_btn",
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
}

?>
