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
class trunk_form extends common
{

    function get_trunk_form_fields()
    {
        $form['forms'] = array(
            base_url() . 'trunk/trunk_save/',
            array(
                'id' => 'trunks_form',
                'method' => 'POST',
                'name' => 'trunks_form'
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
                gettext('Name'),
                'INPUT',
                array(
                    'name' => 'name',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Provider'),
                'provider_id',
                'SELECT',
                '',
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'first_name,last_name,number',
                'accounts',
                'build_concat_dropdown',
                'where_arr',
                array(
                    'type' => 3,
                    "deleted" => "0",
                    "status" => "0"
                )
            ),
            array(
                gettext('Gateway Name'),
                'gateway_id',
                'SELECT',
                '',
                'trim|required|xss_clean',
                'tOOL TIP',
                'Please select gateway first',
                'id',
                'name',
                'gateways',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
            ),
            array(
                gettext('Failover GW Name #1'),
                'failover_gateway_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please select gateway first',
                'id',
                'name',
                'gateways',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
            ),
            array(
                gettext('Failover GW Name #2'),
                'failover_gateway_id1',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please select gateway first',
                'id',
                'name',
                'gateways',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
            ),
            array(
                gettext('Localization'),
                "localization_id",
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'name',
                'localization',
                'build_dropdown_reseller',
                'where_arr',
                array(
                    "status" => "0"
                )
            ),
             
        // ASTPPCOM-944 Jaimin Start
             array(
                    gettext('Remote ID'),
                    'sip_cid_type',
                    'SELECT',
                    '',
                    '',
                    'tOOL TIP',
                    'Please Select Status',
                    '',
                    '',
                    '',
                    'sip_cid_types'
             )
        // END
        );

        $form[gettext('Settings')] = array(

            array(
                gettext('Codecs'),
                'INPUT',
                array(
                    'name' => 'codec',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Call Timeout (Sec.)'),
                'INPUT',
                array(
                    'name' => 'leg_timeout',
                    'size' => '4',
                    'class' => "text field medium"
                ),
                'numeric|greater_than[-1]|integer|xss_clean',
                'tOOL TIP',
                'Please Enter Call Leg Timeout'
            ),
            array(
                gettext('Concurrent Calls'),
                'INPUT',
                array(
                    'name' => 'maxchannels',
                    'value' => '0',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'numeric|greater_than[-1]|integer|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('CPS'),
                'INPUT',
                array(
                    'name' => 'cps',
                    'value' => '0',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'numeric|greater_than[-1]|integer|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Priority'),
                'INPUT',
                array(
                    'name' => 'precedence',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'numeric|greater_than[-1]|integer|xss_clean',
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

    function get_trunk_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "trunk_search"
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
                gettext('Provider'),
                'provider_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter account number',
                'id',
                'first_name,last_name,number',
                'accounts',
                'build_concat_dropdown',
                'where_arr',
                array(
                    'type' => 3,
                    "status" => 0,
                    "deleted" => 0
                )
            ),
            array(
                gettext('Gateway Name'),
                'gateway_id',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please select gateway first',
                'id',
                'name',
                'gateways',
                'build_dropdown',
                'where_arr',
                array(
                    "status" => "0"
                )
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
            'id' => "trunk_search_btn",
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

    function build_trunk_list_for_admin()
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
                gettext("Name"),
                "120",
                "name",
                "",
                "",
                "",
                "EDITABLE",
                "true",
                "left"
            ),
            array(
                gettext("Provider"),
                "100",
                "provider_id",
                "first_name,last_name,number,company_name",
                "accounts",
                "build_concat_string",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Gateway")."<br/>".gettext("Name"),
                "100",
                "gateway_id",
                "name",
                "gateways",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Failover")."<br/>".gettext("GW Name #1"),
                "130",
                "failover_gateway_id",
                "name",
                "gateways",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Failover")."<br/>".gettext("GW Name #2"),
                "130",
                "failover_gateway_id1",
                "name",
                "gateways",
                "get_field_name",
                "",
                "true",
                "center"
            ),
            // ASTPPCOM-944 Jaimin Start
            array(
                gettext("Remote ID"),
                "130",
                "sip_cid_type",
                "sip_cid_type",
                "trunks",
                "get_sip_cid_type",
                "",
                "true",
                "center"
            ),
            //  End
            array(
                gettext("CC"),
                "50",
                "maxchannels",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("CPS"),
                "50",
                "cps",
                "",
                "",
                "",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Codecs"),
                "80",
                "codec",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Rate")." <br>".gettext("Count"),
                "60",
                "id",
                "trunk_id",
                "outbound_routes",
                "get_field_count",
                "",
                "true",
                "right"
            ),
            array(
                gettext("Created")."<br/>".gettext("Date"),
                "100",
                "creation_date",
                "creation_date",
                "creation_date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Modified")."<br/>".gettext("Date"),
                "100",
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
                "60",
                "status",
                "status",
                "trunks",
                "get_status",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Action"),
                "95",
                "",
                "",
                "",
                array(
                    "EDIT" => array(
                        "url" => "trunk/trunk_edit/",
                        "mode" => "popup",
                        "layout" => "medium"
                    ),
                    "DELETE" => array(
                        "url" => "trunk/trunk_remove/",
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
                "/trunk/trunk_add/",
                "popup",
                "medium",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/trunk/trunk_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));
        return $buttons_json;
    }
}

?>
