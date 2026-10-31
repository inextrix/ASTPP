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
class Invoices extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->library('session');
        $this->load->library('invoices_form');
        $this->load->library('astpp/form', 'invoices_form');
        $this->load->library('astpp/permission');
        $this->load->library('astpp/payment');
        $this->load->model('invoices_model');
        $this->load->model('Astpp_common');
        $this->load->model('common_model');
        $this->load->library("astpp/email_lib");
        $this->load->library('fpdf');
        $this->load->library('pdf');
        $this->load->library('ASTPP_Sms');
        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function invoice_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Invoices');
        $data['login_type'] = $this->session->userdata['userlevel_logintype'];
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->invoices_form->build_invoices_list_for_admin();
        $data["grid_buttons"] = $this->invoices_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->invoices_form->get_invoice_search_form());
        $account = $this->db_model->getSelect('id,first_name,last_name,number', 'accounts', array(
            'status' => 0,
            'type' => '0,3',
            'deleted' => 0
        ));
        $data['account_value'] = $account->result_array();
        $accountinfo = $this->session->userdata('accountinfo');
        $reseller_id = $accountinfo['type'] == 1 ? $accountinfo['id'] : 0;
        $data['reseller_id'] = $reseller_id;
        $this->load->view('view_invoices_list', $data);
    }

    function invoice_list_json()
    {
        $login_info = $this->session->userdata('accountinfo');
        $logintype = $this->session->userdata('logintype');
        $json_data = array();
        $count_all = $this->invoices_model->get_invoice_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $result_query = $this->invoices_model->get_invoice_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        if ($logintype == - 1) {
            $currency_id = Common_model::$global_config['system_config']['base_currency'];
        } else {
            $accountdata["currency_id"] = $this->common->get_field_name('currency', 'currency', $login_info["currency_id"]);
            $currency_id = $accountdata["currency_id"];
        }
        $grid_fields = json_decode($this->invoices_form->build_invoices_list_for_admin());
        $url = ($logintype == 0 || $logintype == 3) ? "/user/user_invoice_download/" : '/invoices/invoice_download/';
        if ($result_query->num_rows() > 0) {
            $query = $result_query->result_array();
            $total_value = 0;
            $ountstanding_value = 0;

            foreach ($query as $key => $value) {
                $delete_button = '';
                $date = strtotime($value['generate_date']);

                $invoice_date = $this->common->convert_GMT_to_new('','',$value['generate_date']);
                $from_date = $this->common->convert_GMT_to_new('','',$value['from_date']);
                $due_date = $this->common->convert_GMT_to_new('','',$value['due_date']);
                $to_date = $this->common->convert_GMT_to_new('','',$from_date);

                $outstanding = ($value['is_paid'] == 1) ? $value['debit'] - $value['credit'] : "0.00";
                $invoice_total = '';
                $accountinfo = $this->session->userdata('accountinfo');
                $id = $accountinfo['id'];
                $charge_type = $this->common->get_field_name("charge_type", "invoice_details", array(
                    "invoiceid" => $value['id']
                ));

                $download = "<a  href=" . $url . $value['id'] . " class='btn btn-royelblue btn-sm'  title='Download Invoice' ><i class='fa fa-cloud-download fa-fw'></i></a>&nbsp";
                if ($value['type'] == 'I') {
                    if ($value['confirm'] == 0) {
                        if ($value['generate_type'] == 1) {
                            $payment = '<a href="' . base_url() . 'invoices/invoice_manually_edit/' . $value['id'] . '" class="btn btn-royelblue btn-sm"  title="Edit"><i class="fa fa-pencil-square-o fa-fw"></i></a>';
                        } else {
                            $payment = '<a href="' . base_url() . 'invoices/invoice_automatically_edit/' . $value['id'] . '" class="btn btn-royelblue btn-sm"  title="Edit"><i class="fa fa-pencil-square-o fa-fw"></i></a>';
                        }
                        $id = $value['id'];
                        $delete_button = "<a onclick='invoice_delete($id)' class='btn btn-royelblue btn-sm'  title='Delete' ><i class='fa fa-trash fa-fw'></i></a>&nbsp";
                    } else {

                        if ($value['is_paid'] == 1 && $outstanding > 0) {
                            $payment = "<a style='padding: 0 8px;' href='" . base_url() . "invoices/invoice_summary/" . $value["id"] . "' class='btn btn-warning'  title='Payment'>".gettext("Unpaid")."</i></a>";
                        } else {
                            $payment = "<button style='padding: 0 17px;' type='button'  class='btn btn-success'>".gettext("Paid")."</button>";
                        }
                        $delete_button = "&nbsp";
                    }
                } else {
                    $payment = '';
                }
                $account_arr = $this->db_model->getSelect('first_name,number,last_name,company_name', 'accounts', array(
                    'id' => $value['accountid']
                ));
                $account_array = array();
                if ($account_arr->num_rows > 0) {
                    $account_array = $account_arr->result_array()[0];
                }

                if ($value['generate_type'] == 1) {
                    $invoice_type = 'Manually';
                } else {
                    $invoice_type = 'Automatically';
                }
                if ($value['is_deleted'] == 1) {
                    $download = '';
                    $payment = '<button style="padding: 0 17px;" type="button"  class="btn btn-line-sky">Deleted</button>';
                    $delete_button = '';
                }

                $permissioninfo = $this->session->userdata('permissioninfo');
                $logintype = $this->session->userdata('logintype');

                if (! isset($permissioninfo['invoices']['invoice_list']['download']) and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4')) {
                    $download = '';
                }
                if ((! isset($permissioninfo['invoices']['invoice_list']['edit']) && $value['confirm'] == 0) and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4')) {
                    $payment = '';
                }
                if ((! isset($permissioninfo['invoices']['invoice_list']['payment']) && $value['confirm'] != 0) and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4')) {
                    $payment = '';
                }
                if (! isset($permissioninfo['invoices']['invoice_list']['delete']) and ($permissioninfo['login_type'] == '1' or $permissioninfo['login_type'] == '2' or $permissioninfo['login_type'] == '4')) {
                    $delete_button = '';
                }
                $from_currency = Common_model::$global_config['system_config']['base_currency'];
                $to_currency = $this->common->get_field_name('currency', 'currency', $accountinfo['currency_id']);
                if ($from_currency != $to_currency) {
			if($value['debit']  >  $value['credit']){
                    		$outstanding = ($value['is_paid'] == 1) ? $value['debit'] - $value['credit'] : 0.00;
			}else{
				$outstanding = ($value['is_paid'] == 1) ? $value['credit'] - $value['debit'] : 0.00;
			}
                    $outstanding = $this->common_model->calculate_currency($outstanding, "", "", true, false);
		   if($charge_type == "REFILL" || $charge_type == "Voucher" || $charge_type == "COMMISSION" ){
			    $amount = $value['credit'];
		            $amount = $this->common_model->calculate_currency($amount, "", "", true, false);
		    }else{
		            $amount = ($value['debit'] > 0) ? $value['debit'] : $value['credit'];
		            $amount = $this->common_model->calculate_currency($amount, "", "", true, false);
		    }
                } else {

		
                   if($value['debit']  >  $value['credit']){
                    		$outstanding = ($value['is_paid'] == 1) ? $value['debit'] - $value['credit'] : 0.00;
			}else{
				$outstanding = ($value['is_paid'] == 1) ? $value['credit'] - $value['debit'] : 0.00;
			}
		    if($charge_type == "REFILL" || $charge_type == "Voucher" || $charge_type == "COMMISSION" ){
			$amount = $value['credit'];
		    }else{
                    	$amount = ($value['debit'] > 0) ? $value['debit'] : $value['credit'];
		    }
                }

                $json_data['rows'][] = array(

                    'cell' => array(

                        $value['number'],

                        $invoice_type,
                        isset($account_array['company_name']) && !empty($account_array['company_name']) ? $account_array['company_name'] . ' ' . '</br>' . $account_array['number'] : $account_array['first_name'] . ' ' . $account_array['last_name'] . '</br>' . $account_array['number'],
                        $invoice_date,
                        $from_date,
                        $due_date,
                        $this->common->currency_decimal($amount),
                        $this->common->currency_decimal($outstanding),
                        $this->common->reseller_select_value("first_name,last_name,number,company_name", "accounts", $value['reseller_id']),
                        $download . '' . $payment . ' ' . $delete_button
                    )
                );
                $total_value = $total_value + $value['debit'];
                $ountstanding_value = $ountstanding_value + $outstanding;
            }
        }

        echo json_encode($json_data);
    }

 function invoice_download($invoiceid)
    {
        if ($invoiceid != '' && isset($invoiceid)) {

            $query = $this->db->get_where('view_invoices', array(
                'id' => $invoiceid
            ));
            $invoicedata = $query->first_row();
            $data['invoicenumber'] = $invoicedata->number;
	    $posttoexternal = $this->common->get_field_name("posttoexternal","accounts",array("id"=>$invoicedata->accountid));
	    if($posttoexternal == 0 && $invoicedata->credit > 0 && $invoicedata->credit !=''  ){
			$data['all_total_count'] = ($invoicedata->credit );
	    }else{
            		$data['all_total_count'] = ($invoicedata->debit);
	    }

	  
	   $data['this_month_recharges'] =$this->common->currency_decimal(0);
	   if($posttoexternal == 0 && $invoicedata->credit != "" && $invoicedata->credit > 0 ){
		        $data['this_month_recharges'] = ($invoicedata->credit - $invoicedata->debit) ; 
	   }
		
	   if($posttoexternal == 1 && $invoicedata->credit != "" && $invoicedata->credit > 0 ){
			$data['this_month_recharges'] = $invoicedata->credit; 
	   }
	
	    $data['posttoexternal'] = $posttoexternal;
            $total_calls_amount = "select sum(debit) as debit from invoice_details where charge_type IN('STANDARD','DID','LOCAL','CALLINGCARD') AND  order_item_id = 0 AND  invoiceid ='" . $invoiceid . "'  ";
            $total_calls_amount = (array) $this->db->query($total_calls_amount)->first_row();
            if (! empty($total_calls_amount)) {
                $data['total_calls_amount'] = (float) $total_calls_amount['debit'];
            } else {
                $data['total_calls_amount'] = '0.0000';
            }

            $product_services = "select sum(debit) from invoice_details where  order_item_id > 0 AND is_tax = 0 AND product_category !=3 AND invoiceid ='" . $invoiceid . "' ";
            $product_services = (array) $this->db->query($product_services)->first_row();
            $product_services = $product_services['sum(debit)'];

            if ($product_services != "") {
                $data['product_service_total'] = $this->common->currency_decimal($product_services);
            } else {
                $data['product_service_total'] = '0.0000';
            }
            $login_info = $this->session->userdata('accountinfo');
            $data["currency"] = $this->common->get_field_name('currency', 'currency', $login_info["currency_id"]);

            if ($invoicedata->accountid != '' && isset($invoicedata->accountid)) {

                $query = $this->db->get_where('accounts', array(
                    'id' => $invoicedata->accountid
                ));
                $accountsdata = $query->first_row();

                if ($accountsdata->company_name == '') {
                    $data['fullname'] = $accountsdata->first_name . ' ' . $accountsdata->last_name;
                } else {
                    $data['fullname'] = ucfirst($accountsdata->company_name);
                }

                $data['address_1'] = $accountsdata->address_1;
                $data['address_2'] = $accountsdata->address_2;

                if ($accountsdata->city != '' && $accountsdata->postal_code != '') {
                    $data['city_postalcode'] = $accountsdata->city . ' - ' . $accountsdata->postal_code;
                }
                if ($accountsdata->city == '' && $accountsdata->postal_code != '') {
                    $data['city_postalcode'] = $accountsdata->postal_code;
                }
                if ($accountsdata->city != '' && $accountsdata->postal_code == '') {
                    $data['city_postalcode'] = $accountsdata->city;
                }

                if ($accountsdata->country_id != '0') {
                    $this->db->select('country');
                    $this->db->from('countrycode');
                    $this->db->where('id', $accountsdata->country_id);
                    $query = $this->db->get();
                    $country_name = $query->first_row();

                    if ($accountsdata->province != '' && $country_name->country != '') {
                        $data['province_country'] = $accountsdata->province . ' , ' . $country_name->country;
                    }
                    if ($accountsdata->province == '' && $country_name->country != '') {
                        $data['province_country'] = $country_name->country;
                    }
                    if ($accountsdata->province != '' && $country_name->country == '') {
                        $data['province_country'] = $accountsdata->province;
                    }
                } else {
                    if ($accountsdata->province != '') {
                        $data['province_country'] = $accountsdata->province;
                    } else {
                        $data['province_country'] = '';
                    }
                }

                if ($accountsdata->tax_number != '') {
                    $data['tax_number'] = $accountsdata->tax_number;
                }

                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
                    $domain = "https://" . $_SERVER["HTTP_HOST"] . "/";
                } else {
                    $domain = "http://" . $_SERVER["HTTP_HOST"] . "/";
                }
                $http_host = $_SERVER["HTTP_HOST"];
                $this->db->select('accountid');
                $this->db->where("domain LIKE '%$domain%'");
                $this->db->or_where("domain LIKE '%$http_host%'");
                $invoice_details = (array) $this->db->get_where("invoice_conf")->first_row();
                $accountid_invoice = ((!empty($invoice_details)) && ($invoice_details['accountid'] != '')) ? $invoice_details['accountid'] : 1;

                $query = $this->db->get_where('invoice_conf', array(
                    'accountid' => $accountid_invoice
                ));
                $company_data = $query->first_row();

		$logo = explode (".",$company_data->logo);
		if((!isset($logo[2])) && ($logo[1] == "png")){
			$sourceFile = FCPATH.'upload/'.$company_data->logo;
			$DestFile = FCPATH.'upload/'.$logo[0].'.jpg';
			$filePath = FCPATH.'upload/';
			$convert_png  = "convert " . escapeshellarg($sourceFile) . " -background white -flatten " . escapeshellarg($DestFile);
			$convert_png_file = system($convert_png, $retval);
			$company_data->logo = $logo[0].'.jpg';
		}

                $data['cmp_name'] = $company_data->company_name;
                $data['cmp_address'] = $company_data->address;
                $data['cmp_city_zipcode'] = $company_data->city . ' - ' . $company_data->zipcode;
                //astppcom-972 start
                $country = $this->common->get_field_name('nicename','countrycode', $company_data->country);
                $data['cmp_province_country'] = $company_data->province . ' , ' . $country;
                //astppcom-972 end
                $data['cmp_telephone'] = $company_data->telephone;
                $data['cmp_tax'] = $company_data->invoice_taxes_number;
                $data['cmp_invoice_note'] = $company_data->invoice_note;
                $data['invoice_date'] = $invoicedata->generate_date;
                $data['invoice_due_date'] = $invoicedata->due_date;
                $data['account_number'] = $accountsdata->number;
                $data['invoice_notes'] = $accountsdata->invoice_note;
                $data['logo'] = (! empty($company_data->logo)) ? $company_data->logo : 'logo.jpg';
		
		$debit_data = $this->common->currency_decimal(0);
		$debit_data = $this->common->get_field_name("debit","view_invoices",array("id"=>$invoiceid));
		$data['debit_data'] = $debit_data;
		
		if($posttoexternal == 1){
		        $query = $this->db->get_where('invoice_details', array(
		            'invoiceid' => $invoiceid,
		            'is_tax' => '0',
			    'charge_type <>' =>'INVPAY',
			    'charge_type <>' =>'REFILL'
			
		        ));
		
		}else{
			$query = $this->db->get_where('invoice_details', array(
		            'invoiceid' => $invoiceid,
		            'is_tax' => '0',
			    'charge_type <>' =>'INVPAY'
		        ));

		}
                $invoice_details_data = $query->result_array();

                $query = $this->db->get_where('invoice_details', array(
                    'invoiceid' => $invoiceid,
                    'is_tax' => '1'
                ));
                $invoicetax_details_data = $query->result_array();

                $data['invoicetax_details_data'] = $invoicetax_details_data;
                $data['invoice_details_data'] = $invoice_details_data;
            }

            ob_start();
            $this->load->library('/html2pdf/html2pdf');
            $this->html2pdf = new HTML2PDF('P', 'A4', 'en');
            $this->html2pdf->pdf->SetDisplayMode('fullpage');
            $content = ob_get_clean();
            ob_clean();

            $content = $this->load->view('view_invoice_template', $data, 'TRUE');
            $this->html2pdf->pdf->SetDisplayMode('fullpage');
            $this->html2pdf->writeHTML($content);
            $this->html2pdf->Output($data['invoicenumber'].'.pdf', "D");
            exit();
        }
    }
    function invoice_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);

            if(!empty($action['from_date'][0])){
                 $action['from_date'][0]=$this->common->convert_GMT_to_new ('' ,'', $action['from_date'][0]);
            }
            if(!empty($action['to_date'][1])){
                 $action['to_date'][1]=$this->common->convert_GMT_to_new ( '','',$action['to_date'][1]);
            }
            if (isset($action['amount']['amount']) && $action['amount']['amount'] != '') {
                $action['amount']['amount'] = $this->common_model->add_calculate_currency($action['amount']['amount'], "", '', false, false);
            }
            $action['invoice_date'][0] = $action['invoice_date'][0] ? $action['invoice_date'][0] . " 00:00:00" : '';
            $this->session->set_userdata('invoice_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'invoices/invoice_list/');
        }
    }

    function invoice_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('invoice_list_search', "");
    }

    function generate_receipt($accountid, $amount, $accountinfo, $last_invoice_ID, $invoice_prefix, $due_date)
    {
        $invoice_data = array(
            "accountid" => $accountid,
            "prefix" => $invoice_prefix,
            "invoiceid" => '0000' . $last_invoice_ID,
            "reseller_id" => $accountinfo['reseller_id'],
            "invoice_date" => gmdate("Y-m-d H:i:s"),
            "from_date" => gmdate("Y-m-d H:i:s"),
            "to_date" => gmdate("Y-m-d H:i:s"),
            "due_date" => $due_date,
            "status" => 1,
            "balance" => $accountinfo['balance'],
            "amount" => $amount,
            "type" => 'R',
            "confirm" => '1'
        );
        $this->db->insert("invoices", $invoice_data);
        $invoiceid = $this->db->insert_id();
        return $invoiceid;
    }

    function invoice_manually_edit($id)
    {
        $confirm = $this->common->get_field_name('confirm', 'invoices', $id);
        $deleted = $this->common->get_field_name('is_deleted', 'invoices', $id);
        if ($confirm == 1 || $deleted == 1) {
            redirect(base_url() . 'invoices/invoice_list/');
        }
        $data['total_tax_dis'] = 0;
        $data['total_credit_dis'] = 0;
        $query = "SELECT  * from invoice_details where generate_type=1 AND invoiceid='$id' ";
        $invoice_total_query = $this->db->query($query);

        $data['count'] = 0;
        $data['row_count'] = 5;
        $data['totaltaxdis'] = 0;
        if ($invoice_total_query->num_rows() > 0) {
            $count = $invoice_total_query->num_rows();
            $data['count'] = $count;
            $invoice_total_query = $invoice_total_query->result_array();
            $i = 1;
            $taxi = 0;

            $get_data = array();
            $data['total_tax_dis'] = array();
            foreach ($invoice_total_query as $invkey => $value) {
                if ($value['is_tax'] == 1) {
                    $data['total_tax_dis'][$taxi] = $value['debit'];
                    $taxi ++;
                    $data['totaltaxdis'] += $value['debit'];
                } else {

                    if ($i >= 5) {
                        $data['row_count'] = $i + 1;
                    }
                    $get_data['invoice_from_date_' . $i] = $value['created_date'];
                    $get_data['invoice_description_' . $i] = $value['description'];
                    $get_data['invoice_amount_' . $i] = $value['debit'];
                    $i ++;
                    $data['total_credit_dis'] += $value['debit'];
                }
            }
            $data['get_data'] = $get_data;
        }

        $account_data = $this->session->userdata("accountinfo");
        $logintype = $this->session->userdata('logintype');
        $invoice_total = '';
        $invoice = $this->db_model->getSelect("*", "invoices", array(
            "id" => $id
        ));
        if ($invoice->num_rows() > 0) {
            $invoice = $invoice->result_array();
            $result = $invoice[0];
            $data['payment_due_date'] = $result['due_date'];
        }

        $accountdata = $this->db_model->getSelect("*", "accounts", array(
            "id" => $result['accountid']
        ));
        if ($accountdata->num_rows() > 0) {
            $accountdata = $accountdata->result_array();
            $accountdata = $accountdata[0];
        }
        $data['taxes_count'] = 0;
        $taxes = $this->db_model->getSelect("*", "taxes_to_accounts", array(
            "accountid" => $result['accountid']
        ));

        $total_tax = 0;
        $data['taxes_count'] = $taxes->num_rows();

        if ($taxes->num_rows() > 0) {
            $taxes = $taxes->result_array();
            foreach ($taxes as $tax_value) {
                $taxe_res = $this->db_model->getSelect("*", "taxes", array(
                    "id" => $tax_value['taxes_id']
                ));
                if ($taxe_res->num_rows() > 0) {
                    $taxe_res = $taxe_res->result_array();
                    foreach ($taxe_res as $taxe_res_val) {
                        $data['taxes_to_accounts'][] = $taxe_res_val;
                        $total_tax += $taxe_res_val['taxes_rate'];
                    }
                }
            }
        }

        $system_config = common_model::$global_config['system_config'];
        if ($system_config["paypal_mode"] == 0) {
            $data["paypal_url"] = $system_config["paypal_url"];
            $data["paypal_email_id"] = $system_config["paypal_id"];
        } else {
            $data["paypal_url"] = $system_config["paypal_sandbox_url"];
            $data["paypal_email_id"] = $system_config["paypal_sandbox_id"];
        }
        $date = strtotime($result['generate_date']);
        $data['time'] = date("Y-m-d h:i:s ", $date);
        $data["paypal_tax"] = $system_config["paypal_tax"];

        $data['total_tax'] = $total_tax;
        $data["to_currency"] = $this->common->get_field_name('account_currency', 'invoice_details', array(
            "invoiceid" => $id
        ));
        $data['invoice_notes'] = $result['notes'];
        $data['from_date'] = $result['from_date'];
        $data['to_date'] = $result['to_date'];
        $data['invoice_date'] = $result['generate_date'];
        $data['invoice_prefix'] = $result['prefix'];
        $data['page_title'] = gettext('Invoice Summary');
        $data['invoice_date'] = $result['generate_date'];
        $data['return'] = base_url() . "invoices/invoice_list_modified";
        $data['cancel_return'] = base_url() . "invoice/invoice_list_cancel";
        $data['paypal_mode'] = 1;
        $data['prefix_id'] = $result['number'];
        $data['logintype'] = $logintype;
        $data['accountdata'] = $accountdata;
        $data['id'] = $id;
        $data['notify_url'] = base_url() . "invoices/invoice_list_get_data";
        if ($account_data['type'] == '1') {
            $data['response_url'] = base_url() . "invoices/invoice_list_responce/";
        } else {
            $data['response_url'] = base_url() . "user/user_list_responce/";
        }
        $data['sucess_url'] = base_url() . "invoices/invoice_list_sucess";
        $this->load->view('view_invoice_edit_manually', $data);
    }

    function invoice_automatically_edit($id)
    {
        $confirm = $this->common->get_field_name('confirm', 'invoices', $id);
        $deleted = $this->common->get_field_name('is_deleted', 'invoices', $id);

        $invoices = $this->db_model->getSelect("*", "invoices", array(
            "id" => $id
        ));
        if ($invoices->num_rows > 0) {
            $invoices = $invoices->result_array()[0];
            $invoice_details = $this->db_model->getSelect("*", "invoice_details", array(
                "invoiceid" => $id,
                "is_tax" => 0
            ));
            if ($invoice_details->num_rows > 0) {
                $count = $invoice_details->num_rows();
                $invoice_details = $invoice_details->result_array();
                $data['accountdata'] = (array) $this->db->get_where("accounts", array(
                    "id" => $invoices['accountid']
                ))->result_array()[0];
                $data['invoices'] = $invoices;

                $data['count'] = 0;
                $data['row_count'] = 5;
                $data['count'] = $count;
                $i = 1;

                $data['amount'] = 0;
                $get_data = array();
                $data['total_tax_dis'] = array();
                $data['total_credit_sum'] = 0;
                $data['total_credit_dis'] = 0;
                foreach ($invoice_details as $invkey => $value) {
                    $data['amount'] += $value['debit'];

                    if ($value['generate_type'] == 1) {
                        if ($i >= 5) {
                            $data['row_count'] = $i + 1;
                        }
                        $get_data['invoice_from_date_' . $i] = $value['created_date'];
                        $get_data['invoice_description_' . $i] = $value['description'];
                        $get_data['invoice_amount_' . $i] = $value['debit'];
                        $i ++;
                        $data['total_credit_dis'] += $value['debit'];
                        unset($invoice_details[$invkey]);
                    }
                    $data['total_credit_sum'] += $value['debit'];
                }

                $data['get_data'] = $get_data;
                $data['invoice_details'] = $invoice_details;
                $data['invoice_info'] = $invoices;
                $tax_details = $this->db_model->getSelect("*", "invoice_details", array(
                    "invoiceid" => $id,
                    "is_tax" => 1
                ));
                $data['total_tax'] = 0;

                if ($tax_details->num_rows > 0) {
                    $data['taxes_to_accounts'] = $tax_details->result_array();
                    foreach ($data['taxes_to_accounts'] as $taxkey => $tax_debit) {
                        $data['total_tax'] += $tax_debit['debit'];
                    }
                }

                $data['taxes_count'] = $tax_details->num_rows();
                $data['invoiceid'] = $id;
                $invoice = $this->db_model->getSelect("*", "invoice_details", array(
                    "invoiceid" => $id
                ));
                if ($invoice->num_rows() > 0) {
                    $invoice = $invoice->result_array();
                    $result = $invoice[0];
                }
                $data['amount'] = $this->common_model->calculate_currency($result['debit'], '', '', '', '');
                $data['page_title'] = gettext('Invoice Summary');
                $data['return'] = base_url() . "invoices/invoice_list_modified";
                $data['cancel_return'] = base_url() . "invoice/invoice_list_cancel";
                $data['paypal_mode'] = 1;
                $system_config = common_model::$global_config['system_config'];
                if ($system_config["paypal_mode"] == 0) {
                    $data["paypal_url"] = $system_config["paypal_url"];
                    $data["paypal_email_id"] = $system_config["paypal_id"];
                } else {
                    $data["paypal_url"] = $system_config["paypal_sandbox_url"];
                    $data["paypal_email_id"] = $system_config["paypal_sandbox_id"];
                }
                $data["paypalid"] = '';
                $data["paypal_tax"] = $system_config["paypal_tax"];
                $data['taxes_count'] = 0;
                $taxes = $this->db_model->getSelect("*", "taxes_to_accounts", array(
                    "accountid" => $invoices['accountid']
                ));
                $data['taxes_count'] = $taxes->num_rows();
                $query = "SELECT  * from invoice_details where invoiceid='$id' and generate_type=0 ORDER BY id ASC";
                $invoice_total_query = $this->db->query($query);
                $data['auto_count'] = 0;
                if ($invoice_total_query->num_rows() > 0) {
                    $data['auto_count'] = $invoice_total_query->num_rows();
                    $invoice_total_query = $invoice_total_query->result_array();
                    $data['invoice_total_query'] = $invoice_total_query;
                }
                $invoice_auto_res = $this->db_model->getSelect("sum(debit) as debit", "invoice_details", array(
                    "invoiceid" => $id,
                    'generate_type' => 0
                ));
                $data['invoice_auto_res'] = 0;
                if ($invoice_auto_res->num_rows() > 0) {
                    $invoice_auto_res = $invoice_auto_res->result_array();
                    $result_auto_res = $invoice_auto_res[0];
                    $data['invoice_auto_res'] = $result_auto_res['debit'];
                }
            }
        }

        $data['sucess_url'] = base_url() . "invoices/invoice_list_sucess";
        $this->load->view('view_invoice_edit_automatically', $data);
    }

    /*
     * An invoice that can still be edited (not confirmed, not deleted); its id, account and
     * reseller come from the database, not from the hidden form fields. Redirects otherwise.
     */
    private function editable_invoice($id)
    {
        $id = trim((string) $id);
        $invoice = ctype_digit($id) ? $this->db->get_where('invoices', array('id' => (int) $id))->row_array() : array();
        if (empty($invoice) || $invoice['confirm'] == 1 || $invoice['is_deleted'] == 1) {
            $this->session->set_flashdata('astpp_notification', gettext('This invoice can not be changed any more.'));
            redirect(base_url() . 'invoices/invoice_list/');
            exit();
        }
        return $invoice;
    }

    /* Amount of an invoice line: '' (empty line) or a plain number, in base currency; redirects otherwise. */
    private function invoice_amount($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $this->load->library('astpp/order');
        $amount = order::amount($value, 999999999);
        if ($amount === false) {
            $this->session->set_flashdata('astpp_notification', gettext('Please enter valid amount.'));
            redirect(base_url() . 'invoices/invoice_list/');
            exit();
        }
        return (float) $this->common_model->add_calculate_currency($amount, "", "", false, false);
    }

    function invoice_manually_payment_edit_save()
    {
        $response_arr = $_POST;
        // invoice, account and reseller from the database (they came from hidden fields)
        $invoice = $this->editable_invoice(isset($response_arr['invoiceid']) ? $response_arr['invoiceid'] : '');
        $response_arr['invoiceid'] = $invoice['id'];
        $response_arr['accountid'] = $invoice['accountid'];
        $response_arr['reseller_id'] = $invoice['reseller_id'];
        $row_count = min(100, max(0, (int) (isset($response_arr['row_count']) ? $response_arr['row_count'] : 0)));
        $taxes_count = min(20, max(0, (int) (isset($response_arr['taxes_count']) ? $response_arr['taxes_count'] : 0)));
        // check every amount before anything is deleted or written
        $amounts = array();
        for ($i = 1; $i <= $row_count; $i ++) {
            $amounts[$i] = $this->invoice_amount(isset($response_arr['invoice_amount_' . $i]) ? $response_arr['invoice_amount_' . $i] : '');
        }
        $tax_amounts = array();
        for ($a = 0; $a < $taxes_count; $a ++) {
            $tax_amounts[$a] = $this->invoice_amount(isset($response_arr['abc_total_tax_input_' . $a]) ? $response_arr['abc_total_tax_input_' . $a] : '');
        }
        if (isset($response_arr['save'])) {
            $confirm = 0;
        } else {
            $confirm = 1;
        }
        $where = array(
            'invoiceid' => $response_arr['invoiceid'],
            'generate_type' => 1
        );
        $this->db->where($where);
        $this->db->delete("invoice_details");
        $final_bal = 0;
        $final_tax_bal = 0;

        for ($a = 0; $a < $taxes_count; $a ++) {
            if ($tax_amounts[$a] === '') {
                continue;
            }
            $add_arr = array(
                'accountid' => $response_arr['accountid'],
                'reseller_id' => $response_arr['reseller_id'],
                'invoiceid' => $response_arr['invoiceid'],
                'order_item_id' => 0,
                'generate_type' => 1,
                'is_tax' => 1,
                'description' => isset($response_arr['description_total_tax_input_' . $a]) ? $response_arr['description_total_tax_input_' . $a] : '',
                'debit' => $tax_amounts[$a],
                'created_date' => gmdate("Y-m-d H:i:s")
            );
            $final_tax_bal += $tax_amounts[$a];
            $this->db->insert("invoice_details", $add_arr);
        }

        for ($i = 1; $i <= $row_count; $i ++) {
            if ($amounts[$i] !== '') {
                $add_arr = array(
                    'accountid' => $response_arr['accountid'],
                    'reseller_id' => $response_arr['reseller_id'],
                    'invoiceid' => $response_arr['invoiceid'],
                    'order_item_id' => 0,
                    'generate_type' => 1,
                    'description' => isset($response_arr['invoice_description_' . $i]) ? $response_arr['invoice_description_' . $i] : '',
                    'debit' => $amounts[$i],
                    'created_date' => isset($response_arr['invoice_from_date_' . $i]) ? $response_arr['invoice_from_date_' . $i] : gmdate("Y-m-d H:i:s")
                );

                $this->db->insert("invoice_details", $add_arr);
                $final_bal += $amounts[$i];
            }
        }
        $data = array(
            'confirm' => $confirm,
            'notes' => isset($response_arr['invoice_notes']) ? $response_arr['invoice_notes'] : ''
        );
        $this->db->where("id", $response_arr['invoiceid']);
        $this->db->update("invoices", $data);
        if ($confirm == 1) {

            $account_data = $this->db_model->getSelect("*", "accounts", array(
                "id" => $response_arr["accountid"]
            ));
            $account_data = $account_data->result_array();

            $account_balance = $this->common->get_field_name('balance', 'accounts', $response_arr['accountid']);
            $account_balance = ($account_data[0]['posttoexternal'] == 1) ? ($account_data[0]['credit_limit'] - $account_balance) : $account_balance;

            $invoice_details = $this->db_model->getSelect("*", "invoice_details", array(
                "invoiceid" => $response_arr["invoiceid"]
            ));
            if ($invoice_details->num_rows() > 0) {
                $invoice_details_res = $invoice_details->result_array();
                $after_bal = 0;

                foreach ($invoice_details_res as $details_key => $details_value) {
                    if ($details_value['debit'] > 0) {

                        $before_balance_add = $account_balance - $after_bal;
                        $after_balance_add = $before_balance_add - $details_value['debit'];
                        $after_bal += $details_value['debit'];
                    } else {
                        $before_balance_add = $account_balance - $after_bal;
                        $after_balance_add = $before_balance_add + $details_value['credit'];
                        $after_bal += $details_value['credit'];
                    }
                    $balnace_update = array(
                        'before_balance' => $before_balance_add,
                        'after_balance' => $after_balance_add
                    );

                    $this->db->where("id", $details_value['id']);
                    $this->db->update("invoice_details", $balnace_update);
                }
            }
            // total of the lines saved above (this debited the total computed in the browser,
            // total_val_final)
            $amount = $final_bal + $final_tax_bal;
            $query = "update accounts set balance =  IF(posttoexternal=1,balance+" . sprintf('%.5F', $amount) . ",balance-" . sprintf('%.5F', $amount) . ") where id =" . (int) $response_arr['accountid'];
            $this->db->query($query);

            // Ashish ASTPPCOM-748
            $invoice_prefix = $this->common->get_field_name("prefix","invoices",array("id"=>$response_arr["invoiceid"]));
            $invoice_prefix_id = $this->common->get_field_name("number","invoices",array("id"=>$response_arr["invoiceid"]));
            $due_date = $this->common->get_field_name("due_date","invoices",array("id"=>$response_arr["invoiceid"]));

            $mail_array["invoice_number"] = $invoice_prefix . $invoice_prefix_id;
            // Ashish ASTPPCOM-748 End
            $mail_array["amount"] = $this->common_model->calculate_currency($amount, '', '', true, false);
            // $mail_array["due_date"] = $details_value['due_date'];
            // Ashish ASTPPCOM-748
            $mail_array["due_date"] = $due_date;
            // Ashish ASTPPCOM-748 End
            $accountdata = $account_data[0];
            $final_array = array_merge($accountdata, $mail_array);
            // Ashish ASTPPCOM-748
            $generate_date = (array)$this->db->get_where('invoices',array("id"=>$response_arr["invoiceid"]))->first_row();
            $final_array['generate_date'] = $generate_date['generate_date'];
            // Ashish ASTPPCOM-748 End
            $this->common->mail_to_users("new_invoice", $final_array);
        }

        $this->session->set_flashdata('astpp_errormsg', gettext('Invoice updated successfully!'));
        redirect(base_url() . 'invoices/invoice_list/');
    }

    function invoice_automatically_payment_edit_save()
    {
        $response_arr = $_POST;
        // invoice, account and amounts from the server; only lines of this invoice (this updated
        // invoice_details rows by the ids in the POST keys, of any invoice)
        $invoice = $this->editable_invoice(isset($response_arr['invoiceid']) ? $response_arr['invoiceid'] : '');
        $response_arr['invoiceid'] = $invoice['id'];
        $response_arr['accountid'] = $invoice['accountid'];
        $response_arr['reseller_id'] = $invoice['reseller_id'];
        $row_count = min(100, max(0, (int) (isset($response_arr['row_count']) ? $response_arr['row_count'] : 0)));
        $taxes_count = min(20, max(0, (int) (isset($response_arr['taxes_count']) ? $response_arr['taxes_count'] : 0)));
        $auto = array();
        foreach ((array) (isset($response_arr['auto_invoice_date']) ? $response_arr['auto_invoice_date'] : array()) as $key => $val) {
            $auto[(int) $key] = $this->invoice_amount(isset($response_arr['auto_invoice_amount'][$key]) ? $response_arr['auto_invoice_amount'][$key] : '');
        }
        $tax_amounts = array();
        for ($a = 0; $a < $taxes_count; $a ++) {
            $tax_amounts[$a] = $this->invoice_amount(isset($response_arr['total_tax_id_' . $a]) ? $response_arr['total_tax_id_' . $a] : '');
        }
        $amounts = array();
        for ($i = 1; $i <= $row_count; $i ++) {
            $amounts[$i] = $this->invoice_amount(isset($response_arr['invoice_amount_' . $i]) ? $response_arr['invoice_amount_' . $i] : '');
        }

        if (isset($response_arr['save'])) {
            $confirm = 0;
        } else {
            $confirm = 1;
        }
        $where = array(
            'invoiceid' => $response_arr['invoiceid'],
            'generate_type' => 1
        );
        $this->db->where($where);
        $this->db->delete("invoice_details");
        foreach ($auto as $key => $amount) {
            $data = array(
                'debit' => ($amount === '') ? 0 : $amount,
                'created_date' => $response_arr['auto_invoice_date'][$key],
                'description' => isset($response_arr['auto_invoice_description'][$key]) ? $response_arr['auto_invoice_description'][$key] : '',
                'generate_type' => 0
            );

            $this->db->where(array("id" => $key, "invoiceid" => $response_arr['invoiceid']));
            $this->db->update("invoice_details", $data);
        }

        $final_bal = 0;
        $final_tax_bal = 0;
        $account_balance = $this->common->get_field_name('balance', 'accounts', $response_arr['accountid']);
        for ($a = 0; $a < $taxes_count; $a ++) {
            if ($tax_amounts[$a] === '') {
                continue;
            }
            $update_arr = array(
                'debit' => $tax_amounts[$a]
            );
            $final_tax_bal += $tax_amounts[$a];
            $arr_update = array(
                'id' => (int) (isset($response_arr['description_total_tax_input_' . $a]) ? $response_arr['description_total_tax_input_' . $a] : 0),
                'invoiceid' => $response_arr['invoiceid']
            );
            $this->db->where($arr_update);
            $this->db->update("invoice_details", $update_arr);
        }
        for ($i = 1; $i <= $row_count; $i ++) {
            if ($amounts[$i] !== '') {
                $add_arr = array(
                    'accountid' => $response_arr['accountid'],
                    'reseller_id' => $response_arr['reseller_id'],
                    'invoiceid' => $response_arr['invoiceid'],
                    'order_item_id' => 0,
                    'generate_type' => 1,
                    'description' => isset($response_arr['invoice_description_' . $i]) ? $response_arr['invoice_description_' . $i] : '',
                    'debit' => $amounts[$i],
                    'created_date' => isset($response_arr['invoice_from_date_' . $i]) ? $response_arr['invoice_from_date_' . $i] : gmdate("Y-m-d H:i:s")
                );
                $this->db->insert("invoice_details", $add_arr);
            }
        }

        $query = "select  sum(debit) as credit from invoice_details where invoiceid = " . (int) $response_arr['invoiceid'];
        $invoice_total_query = $this->db->query($query);
        $invoice_total_query = $invoice_total_query->result_array();
        // total of the saved lines instead of the browser's total_val_final
        $invoice_total = $invoice_total_query[0]['credit'];
        $data = array(

            'confirm' => $confirm,
            'notes' => $response_arr['invoice_notes']
        );
        $this->db->where("id", $response_arr['invoiceid']);
        $this->db->update("invoices", $data);
        if ($confirm == 1) {
            $invoice_details = $this->db_model->getSelect("*", "invoice_details", array(
                "invoiceid" => $response_arr["invoiceid"]
            ));
            if ($invoice_details->num_rows() > 0) {
                $invoice_details_res = $invoice_details->result_array();
                $after_bal = 0;
                foreach ($invoice_details_res as $details_key => $details_value) {
                    if ($details_value['charge_type'] != 'STANDARD') {
                        $before_balance_add = $account_balance - $after_bal;
                        $after_balance_add = $before_balance_add - $details_value['debit'];
                        $balnace_update = array(
                            'before_balance' => $before_balance_add,
                            'after_balance' => $after_balance_add
                        );
                        $after_bal += $details_value['debit'];
                        $this->db->where("id", $details_value['id']);
                        $this->db->update("invoice_details", $balnace_update);
                    }
                }
            }
            $account_data = $this->db_model->getSelect("*", "accounts", array(
                "id" => $response_arr["accountid"]
            ));
            $account_data = $account_data->result_array();
            $invoice_not_deduct = $this->db_model->getSelect("*", "invoice_details", array(
                "invoiceid" => $response_arr['invoiceid']
            ));
            $standard_call_balance = 0;
            $invoice_not_deduct = $invoice_not_deduct->result_array();
            foreach ($invoice_not_deduct as $key => $invoice_nodeduct_val) {
                if ($invoice_nodeduct_val['charge_type'] == 'STANDARD') {
                    $standard_call_balance = $invoice_nodeduct_val['debit'];
                }
            }

            if ($account_data[0]['posttoexternal'] == 1) {
                $finaldeduct_bal = $invoice_total - $standard_call_balance;
                $bal_data = $account_data[0]['balance'] - $finaldeduct_bal;
            } else {
                $bal_data = 0;
            }

            $this->db->where("id", $response_arr['accountid']);
            $balance_data = array(
                'balance' => $bal_data
            );
            $this->db->update("accounts", $balance_data);
        }
        $this->session->set_flashdata('astpp_errormsg', gettext('Invoice updated successfully!'));
        redirect(base_url() . 'invoices/invoice_list/');
    }

    function invoice_send_notification($invoice_id, $accountdata, $inv_flag)
    {
        $invoicedata = $this->db_model->getSelect("*", "invoices", array(
            "id" => $invoice_id
        ));
        $invoicedata = $invoicedata->result_array();
        $invoicedata = $invoicedata[0];
        $invoice_conf = array();
        if ($accountdata['reseller_id'] == 0) {
            $where = array(
                "accountid" => 1
            );
        } else {
            $where = array(
                "accountid" => $accountdata['reseller_id']
            );
        }
        $query = $this->db_model->getSelect("*", "invoice_conf", $where);
        if ($query->num_rows() > 0) {
            $invoice_conf = $query->result_array();
            $invoice_conf = $invoice_conf[0];
        } else {
            $query = $this->db_model->getSelect("*", "invoice_conf", array(
                "accountid" => 1
            ));
            $invoice_conf = $query->result_array();
            $invoice_conf = $invoice_conf[0];
        }
        $template_config = $this->config->item('invoice_screen');
        include ($template_config . 'generateInvoice.php');
        $generateInvoice = new generateInvoice();
        $generateInvoice->download_invoice($invoicedata['id'], $accountdata, $invoice_conf, $inv_flag);
        return true;
    }

    function currency_decimal($amount)
    {
        $decimal_amount = Common_model::$global_config['system_config']['decimalpoints'];
        $number_convert = number_format((float) $amount, $decimal_amount, '.', '');
        return $number_convert;
    }

    function invoice_summary($id)
    {
        $account_data = $this->session->userdata("accountinfo");
        $logintype = $this->session->userdata('logintype');
        $data = array();
        $invoice_info = $this->db_model->getSelect("*", "view_invoices", array(
            "id" => $id
        ));
        if ($invoice_info->num_rows() > 0) {
            $invoice = $invoice_info->result_array();
            $invoice_info = $invoice[0];
            $data['invoice_info'] = $invoice_info;

            $query = "SELECT  * from invoice_details where  invoiceid='$id' ORDER BY created_date ASC";
            $invoice_detail_info = $this->db->query($query);

            if ($invoice_detail_info->num_rows() > 0) {
                $data['invoice_detail_info'] = $invoice_detail_info->result_array();
            }
            $accountdata = $this->db_model->getSelect("*", "accounts", array(
                "id" => $data['invoice_info']['accountid']
            ));

            if ($accountdata->num_rows() > 0) {
                $accountdata = $accountdata->result_array();
                $accountdata = $accountdata[0];
                $data['accountdata'] = $accountdata;
            }
            $system_config = common_model::$global_config['system_config'];
            if ($system_config["paypal_mode"] == 0) {
                $data["paypal_url"] = $system_config["paypal_url"];
                $data["paypal_email_id"] = $system_config["paypal_id"];
            } else {
                $data["paypal_url"] = $system_config["paypal_sandbox_url"];
                $data["paypal_email_id"] = $system_config["paypal_sandbox_id"];
            }
            $data["paypal_tax"] = $system_config["paypal_tax"];
            $data['notify_url'] = base_url() . "invoices/invoice_list_get_data/";
            if ($account_data['type'] == '1') {
                $data['response_url'] = base_url() . "invoices/invoice_list_responce/";
            } else {
                $data['response_url'] = base_url() . "user/user_list_responce/";
            }
            $data['sucess_url'] = base_url() . "invoices/invoice_list_sucess/";

            $data['page_title'] = gettext('Invoice Summary');
            $data['logintype'] = $logintype;
            $data['accountdata'] = $accountdata;
            $data['id'] = $id;

            $data['cancel_return'] = base_url() . "invoice/invoice_list_cancel";

            $data["from_currency"] = $this->common->get_field_name('currency', 'currency', $account_data["currency_id"]);
            $data["to_currency"] = Common_model::$global_config['system_config']['base_currency'];

            if ($account_data['type'] == - 1) {

                $currencyid = $this->common->get_field_name('currency', 'currency', $account_data["currency_id"]);
                $data["to_currency"] = $currencyid;
            } elseif ($account_data['type'] == 1) {
                $currencyid = $this->common->get_field_name('currency', 'currency', $account_data["currency_id"]);
                $data["to_currency"] = $currencyid;
            } else {
                $currencyid = $this->common->get_field_name('currency', 'currency', $account_data["currency_id"]);
                $data["to_currency"] = $currencyid;
            }

            $data["system_currency"] = Common_model::$global_config['system_config']['base_currency'];
        } else {

            redirect(base_url() . 'dashboard/');
        }

        $this->load->view('view_invoice_payment', $data);
    }

    function invoice_list_get_data()
    {
        redirect(base_url() . 'invoices/invoice_list/');
    }

    function convert_amount($amount)
    {
        $amount = $this->common_model->add_calculate_currency($amount, "", "", true, false);
        echo number_format($amount, 2);
    }

    /*
     * Invoice payments were booked from the POST data of the PayPal return (no question to
     * PayPal; account from the field "custom", amount from payment_gross). Invoice payments with
     * PayPal are booked only by the verified IPN in pages/paypal_response.
     */
    function invoice_list_responce()
    {
        log_message('error', 'invoices/invoice_list_responce: old PayPal URL called, nothing booked');
        show_404();
    }

    function invoice_admin_payment()
    {
        $response_arr = $_POST;
        if (! empty($response_arr)) {

            // amount as a plain number > 0, the account of the invoice (this was the POST field
            // custom), invoice id as number (it went unquoted into SQL)
            $this->load->library('astpp/order');
            $amount = order::amount(isset($response_arr['amount']) ? $response_arr['amount'] : '', 999999999);
            $invoice_id = (int) (isset($response_arr['item_number']) ? $response_arr['item_number'] : 0);
            $invoice_row = $this->db->get_where('invoices', array('id' => $invoice_id))->row_array();
            if ($amount === false || $amount <= 0 || empty($invoice_row)) {
                $this->session->set_flashdata('astpp_notification', gettext('Please enter valid amount.'));
                redirect(base_url() . 'invoices/invoice_list/');
            }
            $response_arr['custom'] = $invoice_row['accountid'];
            $description = $response_arr['item_name'];
            $date = date('Y-m-d H:i:s');
            $invoice_info = "SELECT  * from view_invoices where  id='$invoice_id' ORDER BY generate_date ASC";
            $invoice_info = $this->db->query($invoice_info);
            if ($invoice_info->num_rows() > 0) {
                $invoice_info = $invoice_info->result_array();
                $debit = $invoice_info[0]['debit'];
            }

            $query = "select  sum(credit) as credit from invoice_details where invoiceid = " . $invoice_id . " Group By invoiceid";
            $invoice_total_query = $this->db->query($query);
            if ($invoice_total_query->num_rows() > 0) {
                $invoice_total_query = $invoice_total_query->result_array();
                $total_debit = $invoice_total_query[0]['credit'];
            }
            $credit_total = $total_debit + $amount;
            if ($debit >= $credit_total) {
                $this->db->where('id', $invoice_id);
                $this->db->update("invoices", array(
                    "notes" => $response_arr['item_name']
                ));
                if ($debit == $credit_total) {

                    $this->db->where("id", $invoice_id);
                    $data = array(
                        'status' => '0'
                    );
                    $this->db->update("invoices", $data);
                } else {
                    $this->db->where("id", $invoice_id);
                    $data = array(
                        'status' => '2'
                    );
                    $this->db->update("invoices", $data);
                }

                if ($amount > $debit) {
                    $this->session->set_flashdata('astpp_notification', gettext('Invoice payment amount should be higher then the invoice amount.'));
                    redirect(base_url() . 'invoices/invoice_summary/' . $invoice_id);
                }

                $debit_amount = "0.00";
                $account_balance = 0.00;
                $account_data = $this->db_model->getSelect('*', 'accounts', array(
                    "id" => $response_arr['custom']
                ));
                if ($account_data->num_rows > 0) {
                    $account_data = (array) $account_data->first_row();
                    $account_balance = $account_data['balance'];
                }
                $paymnet_insert_array = array(
                    "accountid" => $response_arr["custom"],
                    "reseller_id" => ($account_data['reseller_id'] > 0) ? $account_data['reseller_id'] : 0,
                    "amount" => $credit_total,
                    "tax" => 0,
                    'payment_method' => "Mannual",
                    'actual_amount' => $amount,
                    "payment_fee" => 0,
                    "user_currency" => isset($invoice_total_query[0]['account_currency']) ? $invoice_total_query[0]['account_currency'] : 0,
                    "currency_rate" => isset($invoice_total_query[0]['exchange_rate']) ? $invoice_total_query[0]['exchange_rate'] : 0,
                    "customer_ip" => $this->getRealIpAddr(),
                    "transaction_details" => json_encode($response_arr),
                    "transaction_id" => crc32(uniqid()),
                    "date" => gmdate('Y-m-d H:i:s')
                );
                $this->db->insert("payment_transaction", $paymnet_insert_array);
                $payment_last_id = $this->db->insert_id();
                $invoice_insert_array = array(
                    "accountid" => $response_arr["custom"],
                    "reseller_id" => ($account_data['reseller_id'] > 0) ? $account_data['reseller_id'] : 0,
                    "invoiceid" => $invoice_id,
                    "order_item_id" => 0,
                    "charge_type" => "INVPAY",
                    "payment_id" => $payment_last_id,
                    "product_category" => 0,
                    "is_tax" => 0,
                    "base_currency" => isset($invoice_total_query[0]['base_currency']) ? $invoice_total_query[0]['base_currency'] : 0,
                    "exchange_rate" => isset($invoice_total_query[0]['exchange_rate']) ? $invoice_total_query[0]['exchange_rate'] : 0,
                    "account_currency" => isset($invoice_total_query[0]['account_currency']) ? $invoice_total_query[0]['account_currency'] : 0,
                    "description" => "Invoice payment for unpaid",
                    "debit" => "0.00",
                    "credit" => $amount,
                    // "item_type" => "INVPAY",
                    "created_date" => $date,
                    'before_balance' => $account_balance,
                    'after_balance' => $account_balance + $amount,
                    'quantity' => 1
                );
                $this->db->insert("invoice_details", $invoice_insert_array);
            } else {

                $this->session->set_flashdata('astpp_notification', gettext('Invoice payment amount should be higher then the invoice amount.'));
                redirect(base_url() . 'invoices/invoice_summary/' . $invoice_id);
            }

            $this->load->module('accounts/accounts');
            $this->accounts_model->update_balance($amount, $response_arr["custom"], "debit");
            $this->session->set_flashdata('astpp_errormsg', gettext('Invoice payment done successfully!'));
        }
        $account_data = $this->db_model->getSelect("*", "accounts", array(
            "id" => $response_arr["custom"]
        ));
        $account_data = $account_data->result_array();
        $account_data = $account_data[0];
        $account_data['accountid'] = $account_data['id'];
        redirect(base_url() . 'invoices/invoice_list/');
    }

    function invoice_list_sucess()
    {
        echo 'sucess';
        exit();
    }

    function invoice_list_modified()
    {
        echo 'sucess';
        exit();
    }

    function invoice_delete()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("invoices");
    }

    function invoice_conf()
    {
        $data['page_title'] = 'Edit Company Profile';
        $post_array = $this->input->post();
        $accountinfo = $this->session->userdata('accountinfo');
        $logintype = $this->session->userdata('logintype');
        if ($logintype == 1 || $logintype == 2 || $logintype == - 1) {
            if (! empty($post_array)) {
                $file_error = '';

                if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
                    $files = $_FILES['file'];

                    if ($files['size'] < 0 && $files['size'] > 1024) {
                        $error_file = gettext("The Logo file size shoud not exceed 1MB!");
                    } else {

                        $file = $_FILES['file'];
                        $uploadedFile = $file["tmp_name"];
                        $file_name = $file['name'];
                        $file_type = $file['type'];
                        if ($file_type == 'image/jpg' || $file_type == 'image/png' || $file_type == 'image/jpeg') {
                            $imageInformation = getimagesize($_FILES['file']['tmp_name']);
                            if ($imageInformation === FALSE) {
                                $error_file = gettext("Logo only allows file types of JPG, PNG and JPEG.");
                            } else {
                                $imageWidth = $imageInformation[0];

                                $imageHeight = $imageInformation[1];
                                if ($imageWidth > '250' && $imageHeight > '60') {

                                    $error_file = gettext("Please upload 250 * 60 size file");
                                } else {

                                    $file_status = true;
                                }
                            }
                        } else {

                            $error_file = gettext("Please upload only image!");
                        }
                    }
                }
                if (isset($_FILES['file_fav']['name']) && $_FILES['file_fav']['name'] != '') {
                    $files = $_FILES['file_fav'];
                    $error = '';
                    if ($files['size'] < 0 && $files['size'] > 1024) {

                        $error_fav = gettext("The Favicon file size shoud not exceed 1MB!");
                    } else {
                        $file = $_FILES['file_fav'];
                        $uploadedFile1 = $file["tmp_name"];
                        $file_name_fav = $file['name'];
                        $file_type = $file['type'];
                        if ($file_type == 'image/jpg' || $file_type == 'image/x-icon' || $file_type == 'image/png' || $file_type == 'image/jpeg' || $file_type == 'image/vnd.microsoft.icon') {
                            $imageInformation1 = getimagesize($_FILES['file_fav']['tmp_name']);
                            if ($imageInformation1 === FALSE) {
                                $error_fav = gettext("Favicon only allows file types of ICO, PNG, JPG and JPEG.");
                            } else {
                                $imageWidth = $imageInformation1[0];

                                $imageHeight = $imageInformation1[1];

                                if ($imageWidth > '16' && $imageHeight > '16') {

                                    $error_fav = gettext("Please upload 16 * 16 size of favicon.");
                                } else {
                                    $favicon_status = true;
                                }
                            }
                        } else {

                            $error_fav = gettext("Please upload only image!");
                        }
                    }
                }
                $data['form'] = $this->form->build_form($this->invoices_form->get_invoiceconf_form_fields($post_array, $post_array['id']), $post_array);
                if ($this->form_validation->run() == FALSE || isset($error_fav) || isset($error_file)) {
                    if (isset($post_array['id']) && $post_array['id'] != '') {
                        $data['page_title'] = 'Edit Company Profile';
                        $data_new = $this->invoices_model->get_invoiceconf($post_array['id']);
                        $invoices = $post_array;
                        $invoices['logo'] = $data_new['logo'];
                        $invoices['favicon'] = $data_new['favicon'];
                        if (isset($error_fav)) {
                            $data['error_fav'] = $error_fav;
                            $invoices['favicon'] = '';
                        }
                        if (isset($error_file)) {
                            $data['error_file'] = $error_file;
                            $invoices['logo'] = '';
                        }
                        $data['form'] = $this->form->build_form($this->invoices_form->get_invoiceconf_form_fields($invoices), $invoices);
                    } else {
                        $data['flag'] = 'create';
                        $data['page_title'] = gettext('Create Company Profile');
                    }

                    $data['validation_errors'] = validation_errors();
                } else {
                    unset($post_array['action']);
                    unset($post_array['button']);
                    unset($post_array['file']);
                    $invoice_prefix = trim($post_array['invoice_prefix']);

                    if (isset($file_status)) {

                        $dir_path = FCPATH . "upload/";

                        $file_name_extention = explode(".", $file_name);

                        // Extension whitelist (was taken from the 2nd dot-segment, e.g. "x.php.png")
                        $file_name_extention[1] = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        if (! in_array($file_name_extention[1], array('png', 'jpg', 'jpeg'), true)) {
                            $file_name_extention[1] = 'png';
                        }
                        $file_name = crc32($file_name) . '.' . $file_name_extention[1];
                        $path = $dir_path . $file_name;
                        if (move_uploaded_file($uploadedFile, $path)) {

                            $post_array['logo'] = $file_name;
                        } else {

                            $this->session->set_flashdata('astpp_notification', gettext("File Uploading Fail Please Try Again"));
                        }
                    }
                    if (isset($favicon_status)) {
                        $dir_path = FCPATH . "upload/";
                        $file_name_fav_exten = explode(".", $file_name_fav);
                        // Extension whitelist
                        $file_name_fav_exten[1] = strtolower(pathinfo($file_name_fav, PATHINFO_EXTENSION));
                        if (! in_array($file_name_fav_exten[1], array('png', 'jpg', 'jpeg', 'ico'), true)) {
                            $file_name_fav_exten[1] = 'ico';
                        }
                        $file_name_fav = crc32($file_name_fav) . '.' . $file_name_fav_exten[1];
                        $path = $dir_path . $file_name_fav;
                        if (move_uploaded_file($uploadedFile1, $path)) {

                            $post_array['favicon'] = $file_name_fav;
                        } else {

                            $this->session->set_flashdata('astpp_notification', gettext("File Uploading Fail Please Try Again"));
                        }
                    }
                    $result = $this->invoices_model->save_invoiceconf($post_array);
                    if (isset($post_array['id']) && $post_array['id'] != '') {
                        $this->session->set_flashdata('astpp_errormsg', gettext('Company profile updated sucessfully!'));
                    } else {
                        $this->session->set_flashdata('astpp_errormsg', gettext('Company profile added sucessfully!'));
                    }
                    redirect(base_url() . 'invoices/invoice_conf_list/');
                }

                $this->load->view('view_invoiceconf', $data);
            } else {
                $data['page_title'] = gettext('Company Profile');
                $invoiceconf = $this->invoices_model->get_invoiceconf($accountinfo['id']);
                if (! empty($invoiceconf)) {
                    $data['file_name'] = $accountinfo['id'] . "_" . $invoiceconf['logo'];
                    $invoiceconf['file'] = $accountinfo['id'] . "_" . $invoiceconf['logo'];
                    $invoiceconf['file_fav'] = $accountinfo['id'] . "_" . $invoiceconf['favicon'];
                    $data['file_name_fav'] = $accountinfo['id'] . "_" . $invoiceconf['favicon'];
                }

                $data['form'] = $this->form->build_form($this->invoices_form->get_invoiceconf_form_fields($invoiceconf), $invoiceconf);
                $this->load->view('view_invoiceconf', $data);
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied.'));
            redirect(base_url());
        }
    }

    function invoice_conf_list()
    {
        $accountdata = $this->session->userdata('accountinfo');
        $logintype = $this->session->userdata('logintype');
        if ($logintype == 1 || $logintype == 2) {
            $data['username'] = $this->session->userdata('user_name');
            $data['page_title'] = gettext('Company Profiles');
            $data['search_flag'] = true;
            $this->session->set_userdata('advance_search', 0);
            $data['grid_fields'] = $this->invoices_form->build_invoice_conf_list();
            $count = 0;
            if ($logintype == 1) {
                $count = $this->common->get_field_name('id', 'invoice_conf', array(
                    'accountid' => $accountdata["id"]
                ));
                if ($count == '') {
                    $count = 0;
                }
            }
            $data["grid_buttons"] = $this->invoices_form->build_grid_buttons_conf($count);
            $data['form_search'] = $this->form->build_serach_form($this->invoices_form->get_invoiceconf_search_form());
            $this->load->view('view_invoic_conf_list', $data);
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied.'));
            redirect(base_url());
        }
    }

    function invoice_conf_list_json()
    {
        $json_data = array();
        $count_all = $this->invoices_model->getinvoiceconf_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->invoices_model->getinvoiceconf_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->invoices_form->build_invoice_conf_list());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function invoice_conf_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);

            $this->session->set_userdata('invoice_conf_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'invoices/invoice_conf_list/');
        }
    }

    function invoice_confsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('did_search', "");
    }

    function invoice_conf_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Company Profile');
        $where = array(
            'id' => $edit_id
        );
        $accountinfo = $this->session->userdata('accountinfo');
        $invoices = $this->invoices_model->get_invoiceconf($edit_id);
        $data['form'] = $this->form->build_form($this->invoices_form->get_invoiceconf_form_fields($invoices), $invoices);
        $this->load->view('view_invoiceconf', $data);
    }

    function invoice_conf_remove($id)
    {
        $this->db->where('id', $id);
        $this->db->delete("invoice_conf");
        redirect(base_url() . 'invoices/invoice_conf_list/');
    }

    function incr($inteval)
    {
        $inteval ++;
        return $inteval;
    }

    function invoice_list_image_delete()
    {
        if (! empty($_POST['id']) && isset($_POST['id'])) {
            $id = $_POST['id'];
            $logo_data = $this->db_model->getSelect("logo,favicon", "invoice_conf", array(
                "id" => $_POST['id']
            ));
            $logo_data = (array) $logo_data->first_row();
            if ($this->input->post('logo') == 'logo') {
                $msg = "Logo";
                $file_name = $logo_data['logo'];
                $this->db->where('id', $id);
                $this->db->update("invoice_conf", array(
                    'logo' => ''
                ));
            } else {
                $msg = "Favicon";
                $file_name = $logo_data['favicon'];
                $this->db->where('id', $id);
                $this->db->update("invoice_conf", array(
                    'favicon' => ''
                ));
            }
            $path_user = FCPATH . 'upload/';
            if (file_exists($path_user . $file_name)) {
                if (is_file($path_user . $file_name)) {
                    unlink($path_user . $file_name);
                }
            }
            $this->session->set_flashdata('astpp_errormsg', gettext(sprintf('%s  is Deleted Sucessfully!', $msg)));

            echo json_encode(true);
        }
    }

    function customer_invoices($accountid, $accounttype)
    {
        $accountinfo = $this->session->userdata('accountinfo');
        $json_data = array();
        $instant_search = $this->session->userdata('left_panel_search_' . $accounttype . '_invoices');

        if (isset($instant_search) && $instant_search != "") {
            $like_str = isset($instant_search) ? "(`number` like '%$instant_search%'  
		                                    OR IF(`generate_type`=0, 'Automatically', 'Manually') like '%$instant_search%'
		                                    OR `credit` like '%$instant_search%'
						    AND (`accountid` like '%$accountid%'
						    AND `confirm` like '%1%'))" : null;
        }

        $where = array(
            'accountid' => $accountid,
            'confirm' => 1
        );
        if ($instant_search == "") {
            $count_all = $this->db_model->countQuery("*", "invoices", $where);
        } else {
            $count_all = $this->db_model->countQuery("*", "view_invoices", $like_str);
        }
        $currency_id = Common_model::$global_config['system_config']['base_currency'];
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        if ($instant_search == "") {

            $Invoice_grid_data = $this->db_model->select("*", "view_invoices", $where, "from_date", "desc", $paging_data["paging"]["page_no"], $paging_data["paging"]["start"]);
        } else {
            $where1 = $like_str;
            $Invoice_grid_data = $this->db_model->select("*", "view_invoices", $where1, "from_date", "desc", $paging_data["paging"]["page_no"], $paging_data["paging"]["start"]);
        }
        $grid_fields = json_decode($this->invoices_form->build_invoices_list_for_admin());
        $logintype = $this->session->userdata('logintype');
        $url = ($logintype == 0 || $logintype == 3) ? "/user/user_invoice_download/" : '/invoices/invoice_download/';
        $currency = $this->common->get_field_name("currency_id", "accounts", array(
            "id" => $accountid
        ));
        if ($Invoice_grid_data->num_rows() > 0) {
            $query = $Invoice_grid_data->result_array();
            $total_value = 0;
            $ountstanding_value = 0;

            foreach ($query as $key => $value) {
                $date = strtotime($value['to_date']);
                $invoice_date = date("Y-m-d", $date);
                $fromdate = strtotime($value['from_date']);
                $from_date = date("Y-m-d", $fromdate);
                $duedate = strtotime($value['due_date']);
                $due_date = date("Y-m-d", $duedate);
                $from_currency = Common_model::$global_config['system_config']['base_currency'];
                $to_currency = $this->common->get_field_name('currency', 'currency', $accountinfo['currency_id']);
                if ($from_currency != $to_currency) {
                    $outstanding = ($value['is_paid'] == 1) ? $value['debit_exchange_rate'] - $value['credit_exchange_rate'] : 0.00;

                    $amount = ($value['debit_exchange_rate'] > 0) ? $value['debit_exchange_rate'] : $value['credit_exchange_rate'];
                } else {
                    $outstanding = ($value['is_paid'] == 1) ? $value['debit'] - $value['credit'] : 0.00;

                    $amount = ($value['debit'] > 0) ? $value['debit'] : $value['credit'];
                }
                $last_payment_date = '';

                $invoice_total_query = $this->db_model->select("sum(debit) as debit,sum(credit) as credit,created_date", "invoice_details", array(
                    "invoiceid" => $value['id']
                ), "created_date", "DESC", "1", "0");
                if ($invoice_total_query->num_rows() > 0) {
                    $invoice_total_query = $invoice_total_query->result_array();

                    $last_payment_date = $invoice_total_query[0]['created_date'];
                    if ($last_payment_date) {
                        $payment_date = strtotime($last_payment_date);
                        $payment_last = date("Y-m-d", $payment_date);
                    } else {
                        $payment_last = '';
                    }
                }
                $invoice_total = '';
                $accountinfo = $this->session->userdata('accountinfo');
                $id = $accountinfo['id'];
                $query = "select sum(credit) as grand_total from view_invoices where confirm=1 and accountid=$accountid";

                $ext_query = $this->db->query($query);
                if ($ext_query->num_rows() > 0) {
                    $result_total = $ext_query->result_array();

                    $grandtotal = $result_total[0]['grand_total'];
                    $grand_total = $this->common->currency_decimal($grandtotal);
                }

                $invoice_query = "select sum(debit) as grand_credit from view_invoices where accountid=$accountid";
                $credit_query = $this->db->query($invoice_query);
                if ($credit_query->num_rows() > 0) {
                    $credit_total = $credit_query->result_array();

                    $grand_credit_total = $credit_total[0]['grand_credit'];
                    $grandcredit = $grand_total - $grand_credit_total;
                    $grand_credit = $this->common->currency_decimal($grandcredit) . ' ' . $currency_id;
                }

                $download = "<a href=" . $url . $value['id'] . " class='btn btn-royelblue btn-sm'  title='Download Invoice' ><i class='fa fa-cloud-download fa-fw'></i></a>&nbsp";
                if ($value['type'] == 'R') {
                    $payment = '';
                    $payment_last = $invoice_date;
                    $outstanding = 0;
                } else {
                    if ($outstanding > 0) {
                        $payment = "<a style='padding: 0 8px;' href='" . base_url() . "invoices/invoice_summary/" . $value["id"] . "' class='btn btn-warning'  title='Payment'>".gettext("Unpaid")."</i></a>";
                    } else {
                        $payment = " <button style='padding: 0 8px;' type='button'  class='btn btn-success'>".gettext("Paid")."</button>";
                    }
                }

                $account_arr = $this->db_model->getSelect('first_name,number,last_name', 'accounts', array(
                    'id' => $value['accountid']
                ));
                $account_array = $account_arr->result_array();
                if ($value['generate_type'] == 1) {
                    $invoice_type = 'Manually';
                } else {
                    $invoice_type = 'Automatically';
                }

                if ($value['type'] == 'R') {
                    $icon = '<div class="flx_font flx_magenta">R</div>';
                } else {
                    $icon = '<div class="flx_font flx_drk_pink">I</div>';
                }

                $json_data['rows'][] = array(
                    'cell' => array(
                        $value['number'] . $icon,

                        $invoice_type,

                        $invoice_date,
                        $from_date,
                        $due_date,
                        $payment_last,
                        $this->common->currency_decimal($amount),
                        $this->common->currency_decimal($outstanding),
                        $download . '' . $payment
                    )
                );
                $total_value = $total_value + $value['credit'];
                $ountstanding_value = $ountstanding_value + $outstanding;
            }
        }

        echo json_encode($json_data);
    }

    function user_invoices($accountid)
    {
        $json_data = array();
        $count_all = $this->invoices_model->get_user_invoice_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $currency_id = Common_model::$global_config['system_config']['base_currency'];
        $query = $this->invoices_model->get_user_invoice_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->invoices_form->build_invoices_list_for_customer());
        $query = $query->result_array();
        $account_arr = '';
        $created_date = '';
        foreach ($query as $key => $value) {
            $date = strtotime($value['invoice_date']);
            $invoice_date = date("d/m/Y", $date);
            $fromdate = strtotime($value['from_date']);
            $from_date = date("d/m/Y", $fromdate);
            $duedate = strtotime($value['due_date']);
            $due_date = date("d/m/Y", $duedate);
            $outstanding = $value['amount'];
            $invoice_total_query = $this->db_model->select("sum(debit) as debit,sum(credit) as credit,created_date", "invoice_details", array(
                "invoiceid" => $value['id']
            ), "created_date", "DESC", "1", "0");
            if ($invoice_total_query->num_rows() > 0) {
                $invoice_total_query = $invoice_total_query->result_array();
                $outstanding -= $invoice_total_query[0]['credit'];

                $last_payment_date = $invoice_total_query[0]['created_date'];
                if ($last_payment_date) {
                    $payment_date = strtotime($last_payment_date);
                    $payment_last = date("d/m/Y", $payment_date);
                } else {
                    $payment_last = '';
                }
            }
            $invoice_total_query = $this->db_model->select("debit,created_date", "invoice_details", array(
                "invoiceid" => $value['id']
            ), "created_date", "DESC", "1", "0");
            if ($invoice_total_query->num_rows() > 0) {
                $invoice_total_query = $invoice_total_query->result_array();
                $created_date = $invoice_total_query[0]['created_date'];
            }
            $accountinfo = $this->session->userdata('accountinfo');
            $query = "select sum(amount) as grand_total from invoices where  confirm=1 and accountid=$accountid";

            $ext_query = $this->db->query($query);
            if ($ext_query->num_rows() > 0) {
                $result_total = $ext_query->result_array();
                $grandtotal = $result_total[0]['grand_total'];
                $grand_total = $this->common->currency_decimal($grandtotal) . ' ' . $currency_id;
            }

            $invoice_query = "select sum(credit) as grand_credit from invoice_details where accountid=$accountid";
            $credit_query = $this->db->query($invoice_query);
            if ($credit_query->num_rows() > 0) {
                $credit_total = $credit_query->result_array();
                $grand_credit_total = $credit_total[0]['grand_credit'];
                $grandcredit = $grand_total - $grand_credit_total;
                $grand_credit = $this->common->currency_decimal($grandcredit) . ' ' . $currency_id;
            }
            $download = '<a href="' . base_url() . '/user/user_invoice_download/' . $value['id'] . '/00' . $value['invoice_prefix'] . $value['invoiceid'] . '" class="btn btn-royelblue btn-sm"  title="Download Invoice" ><i class="fa fa-cloud-download fa-fw"></i></a>&nbsp';
            if ($outstanding > 0) {
                $payment = " <a style='padding: 0 8px;' href='" . base_url() . "user/user_invoice_payment/" . $value["id"] . "' class='btn btn-warning'  title='Payment'>".gettext("Unpaid")."</a>";
            } else {
                $payment = " <button style='padding: 0 8px;' class='btn btn-success' type='button'>".gettext("Paid")."</button>";
            }
            $account_arr = $this->db_model->getSelect('first_name,number,last_name', 'accounts', array(
                'id' => $value['accountid']
            ));
            $account_array = $account_arr->result_array();
            $date = strtotime($value['invoice_date']);
            $date = strtotime("+7 day", $date);
            $time = date("Y-m-d h:i:s ", $date);
            $json_data['rows'][] = array(
                'cell' => array(
                    $value['invoice_prefix'] . $value['invoiceid'] . ' (' . $value['type'] . ')',
                    $account_array[0]['first_name'] . ' ' . $account_array[0]['last_name'] . '</br>' . $account_array[0]['number'],
                    $invoice_date,
                    $from_date,
                    $due_date,
                    $payment_last,
                    $this->common->currency_decimal($value['amount']) . ' ' . $currency_id,
                    $this->common->currency_decimal($outstanding) . ' ' . $currency_id,
                    $download . $payment
                )
            );
        }

        $json_data['rows'][] = array(
            'cell' => array(

                '<b>Grand Total</b>',
                '',
                '',
                '',
                '',
                '',
                "<b>" . $grand_total . "</b>",
                "<b>" . $grand_credit . "<b>",
                ''
            )
        );
        echo json_encode($json_data);
    }

    function invoice_logo_delete($accountid)
    {
        $invoiceconf = $this->db_model->getSelect("*", "invoice_conf", array(
            "id" => $accountid
        ));
        $result = $invoiceconf->result_array();
        $logo = $result[0]['logo'];
        $post_arr = array(
            'logo' => ''
        );
        $where_arr = array(
            'logo' => $logo
        );
        $this->db->where($where_arr);
        $this->db->update('invoice_conf', $post_arr);
    }

    function invoice_list_view_invoice($invoiceid = false)
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Invoice Detail');

        $cdrs_query = $this->invoices_model->getCdrs_invoice($invoiceid);

        $invoice_cdr_list = array();
        $cdr_list = array();
        if ($cdrs_query->num_rows() > 0) {
            foreach ($cdrs_query->result_array() as $cdr) {
                $cdr['charge'] = $this->common_model->calculate_currency($cdr['debit'] - $cdr['credit']);
                array_push($cdr_list, $cdr);
            }
        }
        $data['invoice_cdr_list'] = $cdr_list;

        $invoice_total_query = $this->Astpp_common->get_invoice_total($invoiceid);

        $total_list = array();
        $invoice_total_list = array();

        if ($invoice_total_query->num_rows() > 0) {
            foreach ($invoice_total_query->result_array() as $total) {
                array_push($total_list, $total);
            }
        }
        $data['invoice_total_list'] = $total_list;
        $invoicedata = $this->Astpp_common->get_invoice($invoiceid);
        $data['invoiceid'] = @$invoicedata[0]['invoiceid'];
        $data['invoicedate'] = @$invoicedata[0]['date'];
        $data['accountid'] = @$invoicedata[0]['accountid'];
        if (! empty($invoicedata)) {
            $accountinfo = $this->invoices_model->get_account_including_closed(@$invoicedata[0]['accountid']);
            $data['accountinfo'] = $accountinfo;
        }
        $invoiceconf = $this->invoices_model->get_invoiceconf($accountinfo['reseller']);
        $data['invoiceconf'] = $invoiceconf;
        $this->load->view('view_account_invoice_detail', $data);
    }

    function invoice_screen()
    {
        $login_type = $this->session->userdata['userlevel_logintype'];
        if ($login_type == - 1 || $login_type == 2 || $login_type == 1 || $login_type == 4) {
            if ($this->input->post()) {
                $data = $this->input->post();

                if ($data['accountid'] == '' || $data['accountid'] == '-Select-') {
                    $this->session->set_flashdata('astpp_notification', 'Please select accounts');
                    redirect(base_url() . "invoices/invoice_list/");
                }
                if (! empty($data)) {
                    if (isset($data['notes']) && $data['notes'] != '') {
                        $this->session->set_userdata('invoice_note', $data['notes']);
                    }
                    $date = date('Y-m-d');
                    $feture_date = date('Y-m-d', strtotime($date));
                    $from_date = $data['fromdate'];
                    $genrated_date = $data['todate'];
                    $to_date = date('Y-m-d', strtotime($genrated_date));
                    if ($to_date > $feture_date) {
                        $this->session->set_flashdata('astpp_notification', gettext('To date should not be greater than current date.'));
                        redirect(base_url() . "invoices/invoice_list/");
                    } else {
                        $todate = $data['todate'] . ' ' . '23:59:59';
                        $from_date = $data['fromdate'] . ' ' . '00:00:01';
                        $accountid = $data['accountid'];
                        $acc_query = $this->db_model->getSelect("*", "accounts", array(
                            "id" => $accountid
                        ));
                        $accountdata = $acc_query->result_array();

                        $accountdata = $accountdata[0];
                        astpp_cron_background('BroadcastEmail');
                        $invoice_data_count = 0;
                        $invoice_conf = array();
                        if ($accountdata['reseller_id'] == 0) {
                            $where = array(
                                "accountid" => 1
                            );
                        } else {
                            $where = array(
                                "accountid" => $accountdata['reseller_id']
                            );
                        }
                        $query = $this->db_model->getSelect("*", "invoice_conf", $where);
                        if ($query->num_rows() > 0) {
                            $invoice_conf = $query->result_array();
                            $invoice_conf = $invoice_conf[0];
                        } else {
                            $query = $this->db_model->getSelect("*", "invoice_conf", array(
                                "accountid" => 1
                            ));
                            $invoice_conf = $query->result_array();
                            $invoice_conf = $invoice_conf[0];
                        }

                        $last_invoice_ID = $this->get_invoice_date("number");

                        if ($last_invoice_ID && $last_invoice_ID > 0) {
                            $last_invoice_ID = ($last_invoice_ID + 1);
                        } else {
                            $last_invoice_ID = $invoice_conf['invoice_start_from'];
                        }
                        $last_invoice_ID = str_pad($last_invoice_ID, (strlen($last_invoice_ID) + 4), '0', STR_PAD_LEFT);
                        if ($accountdata['posttoexternal'] == 1) {
                            $balance = ($accountdata['credit_limit'] - $accountdata['balance']);
                        } else {
                            $balance = $accountdata['balance'];
                        }
                        if ($invoice_conf['interval'] > 0) {
                            $due_date = gmdate("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s") . " +" . $invoice_conf['interval'] . " days"));
                        } else {
                            $due_date = gmdate("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s") . " +7 days"));
                        }

                        $invoice_data = array(
                            "accountid" => $accountdata['id'],
                            "prefix" => $invoice_conf['invoice_prefix'],
                            "number" => $last_invoice_ID,
                            "reseller_id" => $accountdata['reseller_id'],
                            "generate_date" => gmdate("Y-m-d H:i:s"),
                            "from_date" => $from_date,
                            "to_date" => $todate,
                            "due_date" => $due_date,
                            "status" => 1,
                            'generate_type' => 1,
                            'confirm' => 0,
                            'notes' => $data['notes']
                        );

                        $this->db->insert("invoices", $invoice_data);
                        $invoiceid = $this->db->insert_id();

                        $insert_arr = array(
                            "accountid" => $accountdata['id'],
                            "description" => '',
                            "created_date" => gmdate("Y-m-d H:i:s"),
                            "invoiceid" => $invoiceid,
                            "reseller_id" => $accountdata['reseller_id'],
                            "is_tax" => 0,
                            "order_item_id" => 0,
                            "payment_id" => 0,
                            "generate_type" => 1,
                            'before_balance' => 0.000,
                            'product_category' => 0,
                            'charge_type' => '',
                            'after_balance' => 0.000,
                            'base_currency' => "USD",
                            'exchange_rate' => 0,
                            'account_currency' => 0,
                            'debit' => 0.00,
                            'credit' => 0.00
                        );
                        $this->db->insert("invoice_details", $insert_arr);
                        $this->session->set_flashdata('astpp_errormsg', gettext('Invoice generation completed .'));
                        redirect(base_url() . "invoices/invoice_manually_edit/" . $invoiceid);
                    }
                }
            } else {
                $this->session->set_flashdata('astpp_errormsg', gettext('No data found.').'....');
                redirect(base_url() . "invoices/invoice_list/");
            }
        } else {
            $this->session->set_flashdata('astpp_notification', gettext('Permission Denied.'));
            redirect(base_url() . "invoices/invoice_list/");
        }
    }

    function get_invoice_date($select, $accountid = false)
    {
        if ($accountid) {
            $where = array(
                'type' => "I",
                "accountid" => $accountid
            );
            $query = $this->db_model->select($select, "invoices", $where, "to_date", "DESC", "1", "0");
        } else {
            $where = array(
                'type' => "I"
            );
            $query = $this->db_model->select($select, "invoices", $where, "id", "DESC", "1", "0");
        }
        if ($query->num_rows() > 0) {
            $invoiceid = $query->result_array();
            $invoice_date = $invoiceid[0][$select];
            return $invoice_date;
        }
        return false;
    }

    function Sec2Minutes($seconds)
    {
        return sprintf("%02.2d:%02.2d", floor($seconds / 60), $seconds % 60);
    }

    function calculate_currency($amount, $accountdata)
    {
        $base_currency = Common_model::$global_config['system_config']['base_currency'];
        $from_currency = Common_model::$global_config['currency_list'][$base_currency];
        $to_currency = $this->db_model->getSelect("currencyrate", "currency", array(
            "currency" => $accountdata["currency_id"]
        ));
        if ($to_currency->num_rows() > 0) {
            $to_currency_arr = $to_currency->result_array();
            $to_currency = $to_currency_arr[0]["currencyrate"];
        } else {
            $to_currency = $from_currency;
        }

        $cal_amount = ($amount * $to_currency) / $from_currency;
        return $cal_amount;
    }

    function format_currency($amount)
    {
        $dp = $this->db_model->getSelect("value", "system", array(
            "name" => "decimalpoints"
        ));
        $dp = $dp->result_array();
        $dp = $dp[0]["value"];

        return money_format('%.' . $dp . 'n', $amount);
    }

    function date_diff_custom($end = '2020-06-09 10:30:00', $out_in_array = false)
    {
        $intervalo = date_diff(date_create(), date_create($end));
        $out = $intervalo->format("Years:%Y,Months:%M,Days:%d,Hours:%H,Minutes:%i,Seconds:%s");
        if (! $out_in_array)
            return $out;
        $a_out = array();
        array_walk(explode(',', $out), function ($val, $key) use (&$a_out) {
            $v = explode(':', $val);
            $a_out[$v[0]] = $v[1];
        });
        return $a_out;
    }

    function insert_invoice_total_data($invoiceid, $sub_total, $sort_order)
    {
        $invoice_total_arr = array(
            "invoiceid" => $invoiceid,
            "sort_order" => $sort_order,
            "value" => $sub_total,
            "title" => "Sub Total",
            "text" => "Sub Total",
            "class" => "1"
        );
        $this->db->insert("invoices_total", $invoice_total_arr);
        return $sort_order ++;
    }

    function apply_invoice_taxes($invoiceid, $accountid, $sort_order)
    {
        $tax_priority = "";
        $where = array(
            "accountid" => $accountid
        );
        $accounttax_query = $this->db_model->getSelectWithOrder("*", "taxes_to_accounts", $where, "ASC", "taxes_priority");
        if ($accounttax_query->num_rows() > 0) {
            $accounttax_query = $accounttax_query->result_array();
            foreach ($accounttax_query as $tax_key => $tax_value) {
                $taxes_info = $this->db->get_where('taxes', array(
                    'id' => $tax_value['taxes_id']
                ));
                if ($taxes_info->num_rows() > 0) {
                    $tax_value = $taxes_info->result_array();
                    $tax_value = $tax_value[0];
                    if ($tax_value["taxes_priority"] == "") {
                        $tax_priority = $tax_value["taxes_priority"];
                    } else if ($tax_value["taxes_priority"] > $tax_priority) {
                        $query = $this->db_model->getSelect("SUM(value) as total", "invoices_total", array(
                            "invoiceid" => $invoiceid
                        ));
                        $query = $query->result_array();
                        $sub_total = $query["0"]["total"];
                    }
                    $tax_total = (($sub_total * ($tax_value['taxes_rate'] / 100)) + $tax_value['taxes_amount']);
                    $tax_array = array(
                        "invoiceid" => $invoiceid,
                        "title" => "TAX",
                        "text" => $tax_value['taxes_description'],
                        "value" => $tax_total,
                        "class" => "2",
                        "sort_order" => $sort_order
                    );
                    $this->db->insert("invoices_total", $tax_array);
                    $sort_order ++;
                }
            }
        }
        return $sort_order;
    }

    function set_invoice_total($invoiceid, $sort_order)
    {
        $query = $this->db_model->getSelect("SUM(value) as total", "invoices_total", array(
            "invoiceid" => $invoiceid
        ));
        $query = $query->result_array();
        $sub_total = $query["0"]["total"];

        $invoice_total_arr = array(
            "invoiceid" => $invoiceid,
            "sort_order" => $sort_order,
            "value" => $sub_total,
            "title" => "Total",
            "text" => "Total",
            "class" => "9"
        );
        $this->db->insert("invoices_total", $invoice_total_arr);
        return true;
    }

    function invoice_delete_statically($inv_id)
    {
        $data = array(
            'is_deleted' => 1
        );
        $this->db->where('id', $inv_id);
        $this->db->update("invoices", $data);
        $this->session->set_flashdata('astpp_notification', gettext('Invoices removed successfully'));
        redirect(base_url() . 'invoices/invoice_list/');
    }

    function invoice_delete_massege()
    {
        $this->session->set_flashdata('astpp_notification', gettext('Invoices removed successfully'));
        redirect(base_url() . 'invoices/invoice_list/');
    }

    function getRealIpAddr()
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '') {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = getHostByName(getHostName());
        }
        return $ip;
    }

    function reseller_customerlist()
    {
        $add_array = $this->input->post();
        $reseller_id = $add_array['reseller_id'];
        $accountinfo = $this->session->userdata("accountinfo");
         if(!isset($reseller_id) && $reseller_id == ''){
            $reseller_id = $accountinfo['type'] == 1 || $accountinfo['type'] == 5 ? $accountinfo['id'] : $reseller_id;
         }
        $type = array('type' => 1,0);
        $this->db->where_in('type', $type);
        $accounts_result = $this->db->get_where('accounts', array(
            "reseller_id" => $reseller_id,
            "status" => 0
        ));
        if ($accounts_result->num_rows() > 0) {
            $accounts_result_array = $accounts_result->result_array();
            foreach ($accounts_result_array as $key => $value) {
                if(isset($value['company_name']) && $value['company_name'] != ''){
                    echo "<option value=" . $value['id'] . ">" . $value['company_name'] . "(" . $value['number'] . ")</option>";
                }else{
                    echo "<option value=" . $value['id'] . ">" . $value['first_name'] . " " . $value['last_name'] . "(" . $value['number'] . ")</option>";
                }
            }
        } else {
            echo '';
        }

        exit();
    }
}

?>
 
