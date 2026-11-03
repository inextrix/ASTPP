<?php
// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
//
// Copyright (C) 2016 Inextrix Technologies Pvt. Ltd.
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
class Automated_report_form extends common
{

    function __construct()
    {
        $this->CI = & get_instance();
    }

    function build_grid_automated_report_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/automated_report/automated_report_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }
    function get_automated_report_form_fields($id = '')
    {
        $status_array = null;
        $interval_frequency_array = null;
        $week_days = null;
        if(astpp_gt($id, 0)){
            $status_array = array(
             gettext ( 'Status'),
             'status',
             'SELECT', 
             '',
             '', 
             'tOOL TIP',
             'Please Enter account number',
             '',
             '', 
             '',
             'set_status'
            ); 
            $interval_frequency_array = array(
                gettext('Interval Freq. of Email'),
                array(
                    'name' => 'interval_frequency_on',
                    'disabled' => 'disabled'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'get_automated_report_weeks'
            );
            $week_days = array(
                gettext('Selection of days'),
                array(
                    'name' => 'week_day',
                    'class' => 'week_days',
                    'id' => 'week_days',
                    'disabled' => 'disabled'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'get_automated_report_days'
            );
         }else{
            $interval_frequency_array = array(
                gettext('Interval Freq. of Email'),
                'interval_frequency_on',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'get_automated_report_weeks'
            );
            $week_days = array(
                gettext('Selection of days'),
                array(
                    'name' => 'week_day',
                    'class' => 'week_days',
                    'id' => 'week_days',
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'get_automated_report_days'
            );
         }
        $form['forms'] = array(
            base_url() . 'automated_report/automated_report_save/',
            array(
                'id' => 'automated_report_form',
                'method' => 'POST',
                'name' => 'automated_report_form'
            )
        );
        $form[gettext('Automated Report Information')] = array(
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
                    'name' => 'report_name',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Interval Type'),
                array(
                    'name' => 'report_interval_recurring',
                    'class' => 'report_interval_recurring',
                    'id' => 'report_interval_recurring'
                ),
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                '',
                '',
                '',
                'get_automated_report_status'
            ),

            array(
                gettext('Interval Unit'),
                'INPUT',
                array(
                    'name' => 'report_interval_days',
                    'size' => '20',
                    'maxlength' => '5',
                    'class' => "text field medium"
                ),
                'trim|required|numeric',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Email'),
                'INPUT',
                array(
                    'name' => 'account_email',
                    'class' => "text field medium"
                ),
                'trim|xss_clean|required|valid_email|',
                'tOOL TIP',
                ''
            ),
            $interval_frequency_array,
            $week_days,
            $status_array
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
    function get_search_automated_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "automated_report_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'report_name[report_name]',
                    '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'report_name[report_name-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Email'),
                'INPUT',
                array(
                    'name' => 'account_email[account_email]',
                    'value' => '',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'Tool tips info',
                '1',
                'account_email[account_email-string]',
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
            'id' => "automated_search_btn",
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

    function build_automated_report_list_for_admin() {
        $grid_field_arr = json_encode(array(
            array("<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>","30","","","","","","false","center"),
            array(gettext("Name"), "150", "report_name", "", "", "EDITABLE"),
            array(gettext("Module"), "150", "module","", "", ""),
            array(gettext("Interval Days"), "150", "report_interval_days","", "", ""),
            array(gettext("Interval Type"), "150", "report_interval_recurring","report_interval_recurring", "report_interval_recurring", "set_automated_report_status"),
            array(gettext("Email"), "150", "account_email", "", "", ""),
            array(gettext("Interval Freq. of Email"), "150", "interval_frequency_on", "interval_frequency_on", "automated_reports","set_automated_report_status"),
            array (gettext ( "Created Date" ), "150", "creation_date", "convert_GMT_to", "convert_GMT_to", "convert_GMT_to", "", "true", "center"),
            array (gettext ( "Modified Date" ), "150", "last_modified_date", "convert_GMT_to", "convert_GMT_to", "convert_GMT_to", "", "true", "center"),
            array (gettext ( "Next Execution Date" ), "150", "next_execution_date", "convert_GMT_to_date", "convert_GMT_to_date", "convert_GMT_to_date", "", "true", "center"),
            array(
				gettext("Status"),
				"100",
				"status",
				"status",
				"automated_reports",
				"get_status",
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
                        "url" => "automated_report/automated_report_edit/",
                        "mode" => "popup"
                    )
                ),
                "false"
            )
        ));
        return $grid_field_arr;
    }
}

?>
