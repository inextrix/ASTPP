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
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
class did_lib extends MX_Controller {
	function did_billing_process($request_from,$accountid,$did,$skip_balance_check=false,$extra_info='') {

		$this->load->module ( 'did/did' );	

		$account = $this->db_model->getSelect ( "*", "accounts", array ('id' => $accountid ));
		$accountinfo = ( array ) $account->first_row ();


		$currency_name = $this->common->get_field_name ( 'currency', "currency", array (
				'id' => $accountinfo ['currency_id'] 
		) );


		$didinfo = $this->did->did_model->get_did_by_number ( $did );
		if ($request_from['logintype'] == 1 && $accountinfo ['reseller_id'] > 0){ 
			if ($didinfo['parent_id'] > 0 && $didinfo['accountid'] > 0 )
			{
				return array("NOT_AVAL_FOR_PURCHASE","This DID is already purchased by someone.");
			}else{
				// the offer of this reseller's parent (was: first row of any reseller)
				$reseller_pricing_query = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,reseller_products.buy_cost,reseller_products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.status,reseller_products.billing_days,reseller_products.billing_type,dids.last_modified_date,dids.product_id', array('dids.number'=>$didinfo['number'],'reseller_products.account_id'=>$accountinfo['reseller_id']), 'reseller_products','dids.product_id=reseller_products.product_id', 'inner','','','','');
			if(!empty($reseller_pricing_query)){ 
				$reseller_pricing_result = ( array ) $reseller_pricing_query->first_row ();
				$didinfo ['call_type'] = ($reseller_pricing_result ['call_type'] > 0)?$reseller_pricing_result ['call_type']:0;
				$didinfo ['extensions'] = ($reseller_pricing_result ['extensions']>0)?$reseller_pricing_result ['extensions']:'';
				$didinfo ['setup_fee'] = ($reseller_pricing_result ['setup_fee'] > 0)?$reseller_pricing_result ['setup_fee']:0;
				$didinfo ['price'] = ($reseller_pricing_result ['price'] > 0)?$reseller_pricing_result ['price']:0;
				$didinfo ['connectcost'] = ($reseller_pricing_result ['connectcost'] > 0)?$reseller_pricing_result ['connectcost']:0;
				$didinfo ['includedseconds'] = ($reseller_pricing_result ['includedseconds'] > 0)?$reseller_pricing_result ['includedseconds']:0;
				$didinfo ['cost'] = ($reseller_pricing_result ['cost'] > 0)?$reseller_pricing_result ['cost']:0;
				$didinfo ['inc'] = ($reseller_pricing_result ['inc']>0)?$reseller_pricing_result ['inc']:0;
				$didinfo ['billing_days'] = $reseller_pricing_result['billing_days'];
				$didinfo ['billing_type'] = $reseller_pricing_result['billing_type'];
				
			}

			}
		}
		if ($request_from['logintype'] == 1 && $accountinfo ['reseller_id'] == 0){ 
			if ($didinfo['parent_id'] > 0 && $didinfo['accountid'] > 0 )
			{
				return array("NOT_AVAL_FOR_PURCHASE","This DID is already purchased by someone.");
			}else{ 
				$reseller_pricing_query = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,products.buy_cost,products.commission,products.setup_fee,products.price,products.billing_type,products.billing_days,products.status,dids.last_modified_date,dids.product_id', array('dids.number'=>$didinfo['number']), 'products','dids.product_id=products.id', 'inner','','','','');
			if(!empty($reseller_pricing_query)){
				$reseller_pricing_result = ( array ) $reseller_pricing_query->first_row ();
				$didinfo ['call_type'] = ($reseller_pricing_result ['call_type'] > 0)?$reseller_pricing_result ['call_type']:0;
				$didinfo ['extensions'] = ($reseller_pricing_result ['extensions']>0)?$reseller_pricing_result ['extensions']:'';
				$didinfo ['setup_fee'] = ($reseller_pricing_result ['setup_fee'] > 0)?$reseller_pricing_result ['setup_fee']:0;
				$didinfo ['price'] = ($reseller_pricing_result ['price'] > 0)?$reseller_pricing_result ['price']:0;
				$didinfo ['connectcost'] = ($reseller_pricing_result ['connectcost'] > 0)?$reseller_pricing_result ['connectcost']:0;
				$didinfo ['includedseconds'] = ($reseller_pricing_result ['includedseconds'] > 0)?$reseller_pricing_result ['includedseconds']:0;
				$didinfo ['cost'] = ($reseller_pricing_result ['cost'] > 0)?$reseller_pricing_result ['cost']:0;
				$didinfo ['inc'] = ($reseller_pricing_result ['inc']>0)?$reseller_pricing_result ['inc']:0;
				$didinfo ['billing_days'] = $reseller_pricing_result['billing_days'];
				$didinfo ['billing_type'] = $reseller_pricing_result['billing_type'];
			}

		   }
		}

		if ($accountinfo ['reseller_id'] > 0 && $accountinfo ['type'] == 0  ) {
			if ($didinfo['accountid'] > 0)
			{
				return array("NOT_AVAL_FOR_PURCHASE","This DID is already purchased by someone.");
			}else{ 
				$customer_result_array = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,reseller_products.buy_cost,reseller_products.commission,reseller_products.setup_fee,reseller_products.price,reseller_products.billing_type,reseller_products.billing_days,reseller_products.status,dids.last_modified_date,dids.product_id', array('dids.number'=>$didinfo['number'],'reseller_products.account_id'=>$accountinfo['reseller_id']), 'reseller_products','dids.product_id=reseller_products.product_id', 'inner','','','','');
				$customer_result_array = ( array ) $customer_result_array->first_row ();
				$didinfo ['call_type'] = $customer_result_array ['call_type'];
				$didinfo ['extensions'] = $customer_result_array ['extensions'];
				$didinfo ['setup_fee'] = $customer_result_array ['setup_fee'];
				$didinfo ['price'] = $customer_result_array ['price'];
				$didinfo ['connectcost'] = $customer_result_array ['connectcost'];
				$didinfo ['includedseconds'] = $customer_result_array ['includedseconds'];
				$didinfo ['cost'] = $customer_result_array ['cost'];
				$didinfo ['inc'] = $customer_result_array ['inc'];
				$didinfo ['billing_days'] = $customer_result_array['billing_days'];
				$didinfo ['billing_type'] = $customer_result_array['billing_type'];

			}
		}
		if($accountinfo ['reseller_id'] == 0 && $accountinfo ['type'] == 0){
			if ($didinfo['accountid'] > 0)
			{
				return array("NOT_AVAL_FOR_PURCHASE","This DID is already purchased by someone.");
			}else{ 
				$did_info_arr = $this->db_model->getJionQuery('dids', 'dids.id,dids.number,dids.cost,dids.inc,dids.call_type,dids.extensions,dids.connectcost,dids.includedseconds,products.buy_cost,products.commission,products.setup_fee,products.price,products.billing_type,products.billing_days,products.status,dids.last_modified_date,dids.product_id', array('dids.number'=>$didinfo['number']), 'products','dids.product_id=products.id', 'inner','','','','');

				$did_info_arr = ( array ) $did_info_arr->first_row ();
				$didinfo ['call_type'] = $did_info_arr ['call_type'];
				$didinfo ['extensions'] = $did_info_arr ['extensions'];
				$didinfo ['setup_fee'] = $did_info_arr ['setup_fee'];
				$didinfo ['price'] = $did_info_arr ['price'];
				$didinfo ['connectcost'] = $did_info_arr ['connectcost'];
				$didinfo ['includedseconds'] = $did_info_arr ['includedseconds'];
				$didinfo ['cost'] = $did_info_arr ['cost'];
				$didinfo ['inc'] = $did_info_arr ['inc'];
				$didinfo ['billing_days'] = $did_info_arr['billing_days'];
				$didinfo ['billing_type'] = $did_info_arr['billing_type'];
			}


		}

		$available_bal = $this->db_model->get_available_bal ( $accountinfo );
		$accountinfo ['did_number'] = $didinfo ['number'];
		$accountinfo ['did_country_id'] = $didinfo ['country_id'];
		$accountinfo ['did_setup'] = $this->common_model->calculate_currency ( $didinfo ['setup_fee'], '', $currency_name, true, true );
		$accountinfo ['did_monthlycost'] = $this->common_model->calculate_currency ( $didinfo ['price'], '', $currency_name, true, true );
		$accountinfo ['did_maxchannels'] = $didinfo ['maxchannels'];

		$account_balance = $accountinfo ['posttoexternal'] == 1 ? $accountinfo ['credit_limit'] - ($accountinfo ['balance']) : $accountinfo ['balance'];

		// The price for this balance check was replaced by the selling price from the form
		// ($extra_info = POST of did_resellerdid_save): a reseller could buy a DID it cannot pay for
		// by entering a low selling price. The check uses what the order charges.


		if ($account_balance  >= ($didinfo ["setup_fee"]+$didinfo ["price"]) || $skip_balance_check==true) {
			
			if ($request_from['logintype'] == 2 || ($request_from['logintype'] == 1 && $request_from['accountinfo']['id']) == $accountinfo ['id']){

				$field_name = $accountinfo ['type'] == '1' ? "parent_id" : 'accountid';

						
			}elseif($request_from['logintype'] == 1){

				$field_name = $accountinfo ['type'] == 1 ? "parent_id" : 'accountid';
				if ($accountinfo ['type'] == 0 ||$accountinfo ['type'] == 3 )
				{
					$this->db_model->update ( "dids", array (
						"accountid" => $accountinfo ['id']
					), array (
						"id" => $didinfo ['id']
					) );
				}

				
			}elseif($request_from['logintype'] == 0 || $request_from['logintype'] == 4 || $request_from['logintype'] == 3){
				$this->db_model->update ( "dids", array (
					"accountid" => $accountinfo ['id'] 
				), array (
					"id" => $didinfo ['id']
				) );
			}

			
			$final_array = array_merge($accountinfo,$didinfo);
			$final_array['next_billing_date'] = ($final_array['billing_days'] == 0)?gmdate('Y-m-d 23:59:59', strtotime('+10 years')):gmdate("Y-m-d 23:59:59",strtotime("+".$final_array['billing_days']." days"));
			$final_array['name'] =$final_array['number'];
			$final_array['category_name'] ="DID";
			$final_array['payment_by'] ="Account Balance";
			$final_array['quantity']=1;
			$final_array['total_price_amount']=($final_array['setup_fee']+$final_array['price'])*($final_array['quantity']);
			$final_array['price']=($final_array['setup_fee']+$final_array['price']);
			$final_array['id'] = $accountinfo['id'];
			$final_array['number'] = $accountinfo['number'];
			$this->common->mail_to_users ( 'product_purchase',$final_array);			
			return array("SUCCESS","DID Purchased Successfully.");
		}else{			
			return array("INSUFFIECIENT_BALANCE","Insuffiecient fund to purchase this DID.");
		}
	}

	
	function did_release($final_array) {
		$this->common->mail_to_users('product_release',$final_array);
	}
}	
?>
