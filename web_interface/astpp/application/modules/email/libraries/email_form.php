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
class Email_form extends common
{

    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    function get_form_fields_email()
    {
        $form['forms'] = array(
            base_url() . 'email/email_re_send/',
            array(
                'id' => 'commission_form',
                'method' => 'POST',
                'name' => 'commission_form'
            )
        );
        $form[gettext('Resend Email')] = array(
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
                gettext('To'),
                'INPUT',
                array(
                    'name' => 'to',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('From'),
                'INPUT',
                array(
                    'name' => 'from',
                    'size' => '20',
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
                gettext('Body'),
                'TEXTAREA',
                array(
                    'name' => 'body',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
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
                'Please Enter account number',
                '',
                '',
                '',
                'email_search_status',
                '',
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
            'type' => 'submit',
            'class' => 'btn btn-success'
        );

        return $form;
    }

    function get_form_fields_email_edit()
    {
        $readable = 'disabled';
        $form['forms'] = array(
            base_url() . 'email/email_resend/',
            array(
                'id' => 'commission_form',
                'method' => 'POST',
                'name' => 'commission_form'
            )
        );
        $form[gettext('Resent Email')] = array(
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
                '',
                'HIDDEN',
                array(
                    'name' => 'status'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('From'),
                'TEXTAREA',
                array(
                    'name' => 'from',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('To'),
                'TEXTAREA',
                array(
                    'name' => 'to',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Subject'),
                'TEXTAREA',
                array(
                    'name' => 'subject',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Body'),
                'TEXTAREA',
                array(
                    'name' => 'body',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 15,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
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
            'content' => gettext('Sent'),
            'value' => 'save',
            'id' => 'button',
            'type' => 'submit',
            'class' => 'btn btn-success'
        );
        return $form;
    }

    function get_form_fields_email_view()
    {
        $readable = 'disabled';
        $form['forms'] = array(
            base_url() . 'email/email_history_list/',
            array(
                'id' => 'commission_form',
                'method' => 'POST',
                'name' => 'commission_form'
            )
        );
        $form[gettext('View Email')] = array(
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
                '',
                'HIDDEN',
                array(
                    'name' => 'status'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('From'),
                'TEXTAREA',
                array(
                    'name' => 'from',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('To'),
                'TEXTAREA',
                array(
                    'name' => 'to',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Subject'),
                'TEXTAREA',
                array(
                    'name' => 'subject',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Body'),
                'TEXTAREA',
                array(
                    'name' => 'body',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 5,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Status'),
                'INPUT',
                array(
                    'name' => 'status',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function get_form_fields_email_view_cus()
    {
        $readable = 'disabled';
        $form['forms'] = array(
            base_url() . 'email/email_history_list_customer/',
            array(
                'id' => 'commission_form',
                'method' => 'POST',
                'name' => 'commission_form'
            )
        );
        $form[gettext('View Email')] = array(
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
                '',
                'HIDDEN',
                array(
                    'name' => 'status'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('From'),
                'TEXTAREA',
                array(
                    'name' => 'from',
                    'size' => '20',
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('To'),
                'TEXTAREA',
                array(
                    'name' => 'to',
                    'size' => '20',
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Subject'),
                'TEXTAREA',
                array(
                    'name' => 'subject',
                    'size' => '20',
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Body'),
                'TEXTAREA',
                array(
                    'name' => 'body',
                    'size' => '20',
                    'rows' => 7,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),

            array(
                gettext('Status'),
                'INPUT',
                array(
                    'name' => 'status',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            )
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary ml-2',
            'onclick' => 'return redirect_page(\'NULL\')'
        );
        return $form;
    }

    function get_form_fields_email_view_cus_edit()
    {
        $readable = 'disabled';
        $form['forms'] = array(
            base_url() . 'email/email_resend_customer/',
            array(
                'id' => 'commission_form',
                'method' => 'POST',
                'name' => 'commission_form'
            )
        );
        $form[gettext('Resent Email')] = array(
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
                '',
                'HIDDEN',
                array(
                    'name' => 'status'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('From'),
                'TEXTAREA',
                array(
                    'name' => 'from',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('To'),
                'TEXTAREA',
                array(
                    'name' => 'to',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'readonly' => true,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Subject'),
                'TEXTAREA',
                array(
                    'name' => 'subject',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 1,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Body'),
                'TEXTAREA',
                array(
                    'name' => 'body',
                    'size' => '20',
                    'cols' => 50,
                    'rows' => 7,
                    'class' => "form-control form-control-lg mit-20 col-md-12"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
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
            'content' => gettext('Sent'),
            'value' => 'save',
            'id' => 'button',
            'type' => 'submit',
            'class' => 'btn btn-success'
        );
        return $form;
    }

    function build_list_for_email()
    {
        $grid_field_arr = json_encode(array(
            array(
                gettext("Date"),
                "80",
                "date",
                "date",
                "date",
                "convert_GMT_to",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Account"),
                "87",
                "accountid",
                "first_name,last_name,number,company_name",
                "accounts",
                "get_field_name_coma_new",
                "",
                "true",
                "center"
            ),
            array(
                gettext("From"),
                "100",
                "from",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("To"),
                "100",
                "to",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Subject"),
                "120",
                "subject",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Body"),
                "175",
                "body",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("To Number"),
                "90",
                "to_number",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("SMS Body"),
                "175",
                "sms_body",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Status"),
                "70",
                "status",
                "status",
                "status",
                "email_status",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Attachement"),
                "90",
                "attachment",
                "attachment",
                "attachment",
                "attachment_icons",
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
                    "RESEND" => array(
                        "url" => "email/email_resend_edit/",
                        "mode" => "popup"
                    ),
                    "VIEW" => array(
                        "url" => "email/email_view/",
                        "mode" => "popup",
                        "layout" => "medium"
                    ),
                    "DELETE" => array(
                        "url" => "email/email_delete/",
                        "mode" => "single"
                    )
                ),
                ""
            )
        ));
        return $grid_field_arr;
    }

    function build_list_for_email_customer($accountid, $accounttype)
    {
        $logintype = $this->CI->session->userdata('logintype');
        if (($logintype == - 1) || ($logintype == 2)) {
            $grid_field_arr = json_encode(array(
                array(
                    gettext("Date"),
                    "140",
                    "date",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Subject"),
                    "230",
                    "subject",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Body"),
                    "350",
                    "body",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Attachement"),
                    "120",
                    "attachment",
                    "attachment",
                    "attachment",
                    "attachment_icons",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Status"),
                    "100",
                    "status",
                    "status",
                    "status",
                    "email_status",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Action"),
                    "100",
                    "",
                    "",
                    "",
                    array(
                        "RESEND" => array(
                            "url" => "email/email_resend_edit_customer/",
                            "mode" => "popup"
                        ),
                        "VIEW" => array(
                            "url" => "email/email_view_customer/",
                            "mode" => "popup",
                            "layout" => "medium"
                        ),
                        "DELETE" => array(
                            "url" => "email/email_delete_customer/" . $accounttype . "/" . $accountid . "/",
                            "mode" => "single"
                        )
                    ),
                    ""
                )
            ));
        } else {
            $grid_field_arr = json_encode(array(
                array(
                    gettext("Date"),
                    "140",
                    "date",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Subject"),
                    "230",
                    "subject",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Body"),
                    "350",
                    "body",
                    "",
                    "",
                    "",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Attachement"),
                    "120",
                    "attachment",
                    "attachment",
                    "attachment",
                    "attachment_icons",
                    "",
                    "true",
                    "center"
                ),
                array(
                    gettext("Status"),
                    "100",
                    "status",
                    "status",
                    "status",
                    "email_status",
                    "",
                    "true",
                    "center"
                )
            ));
        }
        return $grid_field_arr;
    }

    function get_form_fields_email_view_client($add_arr)
    {
        if ($add_arr['type'] == '') {
            $email_add = array(
                'To',
                'email',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter receipent Email',
                'email',
                'email',
                'accounts',
                'build_dropdown',
                'where_arr',
                array(
                    'status' => $add_arr['status'],
                    'posttoexternal' => $add_arr['posttoexternal'],
                    'pricelist_id' => $add_arr['pricelist_id']
                ),
                'multi'
            );
        } else {
            $email_add = array(
                'To',
                'email',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Please Enter receipent Email',
                'email',
                'email',
                'accounts',
                'build_dropdown',
                'where_arr',
                array(
                    'status' => $add_arr['status'],
                    'posttoexternal' => $add_arr['posttoexternal'],
                    'pricelist_id' => $add_arr['pricelist_id'],
                    'type' => $add_arr['type']
                ),
                'multi'
            );
        }

        $form['forms'] = array(
            base_url() . 'email/email_send_multipal/',
            array(
                'id' => 'commission_form',
                'method' => 'POST',
                'name' => 'commission_form'
            )
        );
        $form[gettext('Compose Email')] = array(
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'accountid'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'temp'
                ),
                '',
                '',
                '',
                ''
            ),
            array(
                gettext('From'),
                'INPUT',
                array(
                    'name' => 'from',
                    'size' => '20',
                    'readonly' => true,
                    'value' => '',
                    'class' => "text field medium"
                ),
                'trim|required|xss_clean',
                'tOOL TIP',
                ''
            ),
            $email_add,
            array(
                gettext('Subject'),
                'INPUT',
                array(
                    'name' => 'subject',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Body'),
                'TEXTAREA',
                array(
                    'name' => 'template',
                    'id' => 'template',
                    'size' => '20',
                    'class' => "textarea medium"
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
            'onclick' => 'return redirect_page(\'/email/email_mass/\')'
        );
        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Send'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'submit',
            'class' => 'btn btn-success'
        );
        return $form;
    }

    function build_grid_buttons_email()
    {
        $buttons_json = json_encode(array());
        return $buttons_json;
    }

    function get_email_history_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => "email_search"
            )
        );
        $form[gettext('Search')] = array(
            array(
                gettext('From Date'),
                'INPUT',
                array(
                    'name' => 'date[]',
                    'id' => 'customer_cdr_from_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'date[date-date]'
            ),
            array(
                gettext('To Date'),
                'INPUT',
                array(
                    'name' => 'date[]',
                    'id' => 'customer_cdr_to_date',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '',
                'date[date-date]'
            ),
            array(
                gettext('Account'),
                'accountid',
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
                    "reseller_id" => "0",
                    "deleted" => "0"
                )
            ),
            array(
                gettext('From'),
                'INPUT',
                array(
                    'name' => 'from[from]',
                    '',
                    'id' => 'from',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'from[from-string]',
                '',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('To'),
                'INPUT',
                array(
                    'name' => 'to[to]',
                    '',
                    'id' => 'to',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'to[to-string]',
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
                    'id' => 'body',
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
                gettext('Body'),
                'INPUT',
                array(
                    'name' => 'body[body]',
                    '',
                    'id' => 'body',
                    'size' => '20',
                    'class' => "text field "
                ),
                '',
                'tOOL TIP',
                '1',
                'body[body-string]',
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
            'id' => "email_search_btn",
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
}

?>
