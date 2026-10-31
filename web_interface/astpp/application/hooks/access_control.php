<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * Central authorization check (hook post_controller_constructor, config/hooks.php).
 *
 * The controllers check the owner of a record only in some methods, so resellers,
 * customers and providers could read, change or delete records of other accounts by passing
 * their id in the URL or in a POST field. This hook runs after the controller constructor
 * (which checks the login) and before the method is called:
 *  - all roles: helper methods that are public only for internal calls cannot be called by
 *    URL, bulk actions (POST field selected_ids) are accepted by POST only,
 *  - administrators (-1, 2): no further restrictions,
 *  - resellers (1, 5): no admin-only modules or methods; every record id must belong to one
 *    of their customers/sub-resellers or carry their reseller_id,
 *  - customers (0), providers (3) and unknown types: customer portal only; every record id
 *    must belong to the own account.
 * Denied: redirect with "Permission Denied!", HTTP 403 with JSON for AJAX/JSON requests.
 *
 * The tables below are the one place that says which URI parameter or POST field points to
 * which table. Rule format: array(source, check, options):
 *  source  int n = n-th URI parameter of the method, 'post:<field>' = POST field
 *  check   table name (owner rules in scope()) or a special check (see allowed())
 *  options 'self' => true   the own account/own records are allowed too
 *          'col' => name    column the value refers to (default id)
 *          'when' => [n, v] rule applies only if URI parameter n equals v
 *          'unless' => [n, v] rule applies only if URI parameter n differs from v
 *          'required' => true a missing value is denied too
 *          'empty' => true    check 'reseller': '' and '0' select the own customers (logintype 1)
 *          'topup' => true    check 'product_buyable': also top-up products of the parents up to the admin
 * Values '' and '0' mean "no record" and are not checked (except check 'reseller' and
 * 'required').
 */
class Access_control
{
	/** Public helper methods meant for internal calls only (any role). */
	private static $internal = array(
		'accounts/free_customer_did', 'accounts/free_ani_map', 'accounts/free_reseller_did', 'accounts/chargelist_json',
		'accounts/get_invoice_date', 'accounts/delete_multiple', 'accounts/reseller_edit_account', 'accounts/provider_edit_account',
		'accessnumber/data_validate', 'did/data_validate', 'ratedeck/data_validate', 'rates/data_validate',
		'email/email_re_send', 'email/attachment_icons',
		'freeswitch/get_domain_fssipdevices', 'freeswitch/get_advance_buttons_fssipdevices', 'freeswitch/get_action_fssipdevices_buttons',
		'freeswitch/get_action_buttons_fssipdevices', 'freeswitch/livecall_delete_all_between',
		'invoices/generate_receipt', 'invoices/invoice_send_notification', 'invoices/currency_decimal', 'invoices/incr',
		'invoices/get_invoice_date', 'invoices/sec2minutes', 'invoices/calculate_currency', 'invoices/format_currency',
		'invoices/date_diff_custom', 'invoices/insert_invoice_total_data', 'invoices/apply_invoice_taxes', 'invoices/set_invoice_total',
		'invoices/getrealipaddr', 'reports/getrealipaddr',
		'login/generate_receipt', 'login/encode_params', 'login/encode', 'login/encrypt',
		'pages/refill_auto_form', 'pages/oerder_request_form',
		'products/products_reseller_edit', 'products/assign_product_to_exiting_account', 'products/package_product',
		'products/did_product', 'products/refill_product', 'products/get_product_category',
		'rates/csv_to_array', 'rates/utf8_converter', 'rates/origination_set_force_routing',
		'summary/summary_column_arr', 'summary/summary_report_grid', 'summary/summary_search_info', 'summary/product_summary_report_grid',
		'systems/resellertemplate_save', 'systems/admintemplate_save',
		'user/user_invoice_date', 'user/validate_device_data',
	);

	/** Admin-only modules and methods (denied for resellers; customers/providers see the portal only). */
	private static $admin_only = array(
		'~^(trunk|calltype|cronsettings|localization|login_activity|audit|activity_report|addons|permissions)/~',
		'~^accessnumber/(?!accessnumber_(list(_json|_search|_clearsearchfilter)?|export_data_xls)$)~',
		'~^accounts/(admin|subadmin|callshop)_~',
		'~^did/(did_import|did_preview_file|did_error_download)~',
		'~^freeswitch/(fsgateway|fssipprofile|livecall)~',
		'~^invoices/invoice_admin_payment~',
		'~^pricing/set_force_routing~',
		'~^products/products_did$~',
		'~^rates/termination_~',
		'~^reports/provider~',
		'~^summary/provider~',
		'~^systems/(?!(configuration|payment_methods|template|template_json|template_edit|template_save|template_search|template_clearsearchfilter|translation_list|translation_list_json|translation_list_clearsearchfilter)$)~',
	);

	/** Customer portal (customers, providers): everything else is denied. */
	private static $portal = array(
		'~^(user|login|dashboard|signup)/~',
		'~^pages/(services|topup_reseller|checkout|proceed_payment|get_product_info|payment_proceed|products_purchase|refill_coupon_add_view|user_refill_coupon_number|paypal_return)$~',
		'~^products/products_topuplist$~',
		'~^accessnumber/accessnumber_(list(_json|_search|_clearsearchfilter)?|export_data_xls)$~',
		'~^invoices/invoice_main_download$~',
		'~^freeswitch/user_fssipdevices_(save|delete_multiple)$~',
		'~^reports/user_refillreport(_search|_clearsearchfilter)?$~',
		'~^rates/user_origination_rate_(list_json|list_search|list_clearsearchfilter|cdr_pdf)$~',
	);

	/** Bulk actions: POST field selected_ids -> table (column). */
	private static $bulk = array(
		'accounts/customer_selected_delete' => 'accounts',
		'accounts/reseller_selected_delete' => 'accounts',
		'accounts/provider_selected_delete' => 'accounts',
		'accounts/customer_blockedprefixes_delete_multiple' => 'block_patterns',
		'accounts/customer_product_delete' => 'order_items',
		// DID products; their order items include the reseller's own purchase, so check the DID itself
		'accounts/customer_did_delete' => array('dids', 'product_id'),
		'animap/animap_delete_multiple' => 'ani_map',
		'did/did_delete_multiple' => array('dids', 'product_id'),
		'email/email_delete_multiple' => 'mail_details',
		'freeswitch/fssipdevices_delete_multiple' => 'sip_devices',
		'freeswitch/customer_fssipdevices_delete_multiple' => 'sip_devices',
		'freeswitch/user_fssipdevices_delete_multiple' => 'sip_devices',
		'invoices/invoice_delete' => 'invoices',
		'ipmap/ipmap_delete_multiple' => 'ip_map',
		'orders/orders_delete_multiple' => 'orders',
		'pricing/price_delete_multiple' => 'pricelists',
		'products/products_delete_multiple' => 'products_optin',
		'products/products_patterns_selected_delete' => 'package_patterns',
		'ratedeck/ratedeck_delete_multiple' => 'ratedeck',
		'rates/origination_rate_delete_multiple' => 'routes',
		'refill_coupon/refill_coupon_list_delete' => 'refill_coupon',
		'taxes/taxes_delete_multiple' => 'taxes',
		'user/user_sipdevices_delete_multiple' => 'sip_devices',
		'user/user_ipmap_delete_multiple' => 'ip_map',
		'user/user_animap_delete_multiple' => 'ani_map',
		'user/user_opensips_delete_multiple' => null,
	);

	/** Record references per module/method (URI parameters and POST fields). */
	private static $rules = array(
		// accounts: managing customers, providers and sub-resellers
		'accounts/customer_edit' => array(array(1, 'accounts')),
		'accounts/provider_edit' => array(array(1, 'accounts')),
		'accounts/reseller_edit' => array(array(1, 'accounts')),
		'accounts/customer_save' => array(array('post:id', 'accounts'), array('post:pricelist_id', 'pricelists')),
		'accounts/provider_save' => array(array('post:id', 'accounts'), array('post:pricelist_id', 'pricelists')),
		'accounts/reseller_save' => array(array('post:id', 'accounts'), array('post:pricelist_id', 'pricelists')),
		'accounts/customer_speeddial' => array(array(1, 'accounts')),
		'accounts/provider_speeddial' => array(array(1, 'accounts')),
		'accounts/customer_speeddial_save' => array(array(2, 'accounts')),
		'accounts/customer_speeddial_remove' => array(array(1, 'accounts')),
		'accounts/customer_ipmap' => array(array(1, 'accounts')),
		'accounts/provider_ipmap' => array(array(1, 'accounts')),
		'accounts/customer_ipmap_action' => array(array(2, 'accounts'), array(4, 'ip_map'), array('post:id', 'ip_map')),
		'accounts/provider_ipmap_action' => array(array(2, 'accounts'), array(4, 'ip_map'), array('post:id', 'ip_map')),
		'accounts/customer_animap' => array(array(1, 'accounts')),
		'accounts/provider_animap' => array(array(1, 'accounts')),
		'accounts/customer_animap_json' => array(array(1, 'accounts')),
		'accounts/customer_animap_action' => array(array(2, 'accounts'), array(3, 'ani_map'), array('post:id', 'ani_map')),
		'accounts/provider_animap_action' => array(array(2, 'accounts'), array(3, 'ani_map'), array('post:id', 'ani_map')),
		'accounts/customer_details_json' => array(array(2, 'accounts')),
		'accounts/reseller_details_json' => array(array(2, 'accounts')),
		'accounts/provider_details_json' => array(array(2, 'accounts')),
		'accounts/customer_sipdevices' => array(array(1, 'accounts')),
		'accounts/provider_sipdevices' => array(array(1, 'accounts')),
		'accounts/customer_fssipdevices_action' => array(array(2, 'sip_devices'), array(3, 'accounts')),
		'accounts/provider_fssipdevices_action' => array(array(2, 'sip_devices'), array(3, 'accounts')),
		'accounts/customer_charges' => array(array(1, 'accounts')),
		'accounts/provider_charges' => array(array(1, 'accounts')),
		'accounts/customer_dids' => array(array(1, 'accounts')),
		'accounts/provider_dids' => array(array(1, 'accounts')),
		'accounts/customer_dids_action' => array(array(2, 'accounts'), array(4, 'dids', 'col' => 'product_id', 'when' => array(1, 'add')),
			array(4, 'dids', 'unless' => array(1, 'add')), array('post:free_didlist', 'dids', 'col' => 'product_id')),
		'accounts/provider_dids_action' => array(array(2, 'accounts'), array(4, 'dids', 'col' => 'product_id', 'when' => array(1, 'add')),
			array(4, 'dids', 'unless' => array(1, 'add')), array('post:free_didlist', 'dids', 'col' => 'product_id')),
		'accounts/reseller_did_action' => array(array(2, 'accounts'), array('post:free_did_list', 'dids')),
		'accounts/customer_invoices' => array(array(1, 'accounts')),
		'accounts/provider_invoices' => array(array(1, 'accounts')),
		'accounts/customer_blocked_prefixes' => array(array(1, 'accounts')),
		'accounts/provider_blocked_prefixes' => array(array(1, 'accounts')),
		'accounts/customer_add_blockpatterns' => array(array(1, 'accounts')),
		'accounts/customer_add_blockpatterns_json' => array(array(1, 'accounts')),
		'accounts/customer_block_prefix' => array(array(1, 'accounts')),
		'accounts/customer_delete_block_pattern' => array(array(1, 'accounts'), array(2, 'block_patterns')),
		'accounts/provider_delete_block_pattern' => array(array(1, 'accounts'), array(2, 'block_patterns')),
		'accounts/customer_cdrs' => array(array(1, 'accounts')),
		'accounts/provider_cdrs' => array(array(1, 'accounts')),
		'accounts/customer_refillreport' => array(array(1, 'accounts')),
		'accounts/provider_refillreport' => array(array(1, 'accounts')),
		'accounts/customer_emailhistory' => array(array(1, 'accounts')),
		'accounts/provider_emailhistory' => array(array(1, 'accounts')),
		'accounts/customer_alert_threshold' => array(array(1, 'accounts')),
		'accounts/provider_alert_threshold' => array(array(1, 'accounts')),
		'accounts/customer_alert_threshold_save' => array(array('post:id', 'accounts')),
		'accounts/provider_alert_threshold_save' => array(array('post:id', 'accounts')),
		'accounts/reseller_alert_threshold_save' => array(array('post:id', 'accounts')),
		'accounts/customer_payment_process_add' => array(array(1, 'accounts')),
		'accounts/customer_payment_save' => array(array(1, 'accounts'), array('post:id', 'accounts')),
		'accounts/customer_delete' => array(array(1, 'accounts')),
		'accounts/reseller_delete' => array(array(1, 'accounts')),
		'accounts/provider_delete' => array(array(1, 'accounts')),
		'accounts/customer_add_postcharges' => array(array(2, 'accounts')),
		'accounts/customer_account_taxes' => array(array(2, 'accounts')),
		'accounts/customer_animap_list' => array(array(1, 'accounts')),
		'accounts/customer_animap_list_json' => array(array(1, 'accounts')),
		'accounts/customer_animap_list_action' => array(array(1, 'accounts'), array('post:id', 'ani_map')),
		'accounts/customer_animap_list_remove' => array(array(1, 'ani_map')),
		'accounts/customer_animap_list_edit' => array(array(1, 'ani_map')),
		'accounts/customer_show_password' => array(array(1, 'accounts')),
		'accounts/customer_account_change' => array(array(1, 'reseller')),
		'accounts/customer_product' => array(array(1, 'accounts')),
		'accounts/customer_products_action' => array(array(2, 'order_items', 'when' => array(1, 'delete')), array(2, 'accounts', 'unless' => array(1, 'delete')),
			array('post:applayable_product', 'product_visible')),
		'accounts/customer_product_delete' => array(array(1, 'accounts')),
		'accounts/customer_did_delete' => array(array(1, 'accounts')),
		'accounts/reseller_customerlist' => array(array('post:reseller_id', 'reseller')),
		'accounts/customer_customerlist' => array(array('post:reseller_id', 'reseller')),
		'accounts/customer_subresellerlist' => array(array('post:reseller_id', 'reseller')),
		'accounts/customer_depend_list' => array(array('post:reseller_id', 'reseller')),
		'accounts/customer_pricelist' => array(array('post:reseller_id', 'reseller')),
		'accounts/reseller_distributor' => array(array('post:reseller_id', 'reseller')),
		'animap/animap_edit' => array(array(1, 'ani_map')),
		'animap/animap_delete' => array(array(1, 'ani_map')),
		'animap/animap_save' => array(array('post:id', 'ani_map')),
		'animap/reseller_customerlist' => array(array('post:reseller_id', 'reseller')),
		'ipmap/ipmap_edit' => array(array(1, 'ip_map')),
		'ipmap/ipmap_delete' => array(array(1, 'ip_map')),
		'ipmap/ipmap_save' => array(array('post:id', 'ip_map')),
		'ipmap/reseller_customerlist' => array(array('post:reseller_id', 'reseller')),
		// DIDs
		'did/did_available_purchase' => array(array(1, 'dids_available')),
		'did/did_assgin_reseller' => array(array(1, 'dids')),
		'did/did_list_release' => array(array(1, 'dids')),
		'did/did_forward' => array(array(1, 'dids_forward', 'required' => true)),
		'did/did_forward_save' => array(array('post:id', 'dids')),
		'did/did_local_change' => array(array(2, 'dids')),
		'did/customer_did' => array(array(1, 'accounts')),
		// e-mail history
		'email/email_edit' => array(array(1, 'mail_details')),
		'email/email_resend' => array(array('post:id', 'mail_details')),
		'email/email_resend_edit' => array(array(1, 'mail_details')),
		'email/email_resend_edit_customer' => array(array(1, 'mail_details')),
		'email/email_resend_customer' => array(array(1, 'mail_details'), array('post:id', 'mail_details')),
		'email/email_view' => array(array(1, 'mail_details', 'self' => true)),
		'email/email_view_customer' => array(array(1, 'mail_details', 'self' => true)),
		'email/email_delete' => array(array(1, 'mail_details')),
		'email/email_delete_customer' => array(array(2, 'accounts'), array(3, 'mail_details')),
		'email/customer_mail_record' => array(array(1, 'accounts')),
		'email/email_history_list_attachment' => array(array(1, 'attachment')),
		// SIP devices
		'freeswitch/customer_fssipdevices_add' => array(array(1, 'accounts', 'self' => true)),
		'freeswitch/fssipdevices_edit' => array(array(1, 'sip_devices', 'self' => true)),
		'freeswitch/customer_fssipdevices_edit' => array(array(1, 'sip_devices', 'self' => true), array(2, 'accounts', 'self' => true)),
		'freeswitch/fssipdevices_save' => array(array('post:id', 'sip_devices', 'self' => true)),
		'freeswitch/customer_fssipdevices_save' => array(array('post:id', 'sip_devices', 'self' => true)),
		'freeswitch/user_fssipdevices_save' => array(array('post:id', 'sip_devices', 'self' => true)),
		'freeswitch/customer_fssipdevices_json' => array(array(1, 'accounts', 'self' => true)),
		'freeswitch/fssipdevices_delete' => array(array(1, 'sip_devices', 'self' => true)),
		'freeswitch/fssipdevices_reseller_customer' => array(array('post:reseller_id', 'reseller')),
		// invoices and company profile
		'invoices/invoice_download' => array(array(1, 'invoices', 'self' => true)),
		'invoices/invoice_main_download' => array(array(1, 'invoices', 'self' => true)),
		'invoices/invoice_manually_edit' => array(array(1, 'invoices')),
		'invoices/invoice_automatically_edit' => array(array(1, 'invoices')),
		'invoices/invoice_manually_payment_edit_save' => array(array('post:id', 'invoices'), array('post:invoiceid', 'invoices', 'required' => true)),
		'invoices/invoice_automatically_payment_edit_save' => array(array('post:id', 'invoices'), array('post:invoiceid', 'invoices', 'required' => true)),
		'invoices/invoice_summary' => array(array(1, 'invoices', 'self' => true)),
		'invoices/invoice_conf' => array(array('post:id', 'invoice_conf')),
		'invoices/invoice_conf_edit' => array(array(1, 'invoice_conf')),
		'invoices/invoice_conf_remove' => array(array(1, 'invoice_conf')),
		'invoices/invoice_list_image_delete' => array(array('post:id', 'invoice_conf')),
		'invoices/invoice_logo_delete' => array(array(1, 'invoice_conf')),
		'invoices/customer_invoices' => array(array(1, 'accounts')),
		'invoices/user_invoices' => array(array(1, 'accounts', 'self' => true)),
		'invoices/invoice_list_view_invoice' => array(array(1, 'invoices', 'self' => true)),
		'invoices/invoice_delete_statically' => array(array(1, 'invoices')),
		'invoices/reseller_customerlist' => array(array('post:reseller_id', 'reseller')),
		'low_balance/customer_account_change' => array(array(1, 'reseller')),
		// orders and products
		'orders/orders_complete' => array(array(1, 'orders_any')),
		'orders/orders_terminate' => array(array(1, 'orders', 'col' => 'order_id')),
		'orders/orders_users_terminate' => array(array('post:order_id', 'orders', 'col' => 'order_id')),
		'orders/orders_reseller_accounts_dependency_dropdown' => array(array('post:reseller_id', 'reseller', 'empty' => true)),
		'pages/checkout' => array(array(1, 'product_buyable')),
		'pages/proceed_payment' => array(array(1, 'product_buyable', 'topup' => true)),
		'pages/payment_proceed' => array(array(1, 'product_buyable', 'topup' => true)),
		'pages/get_product_info' => array(array('post:id', 'product_buyable', 'topup' => true)),
		'pages/user_refill_coupon_number' => array(array(2, 'accounts', 'self' => true)),
		'products/products_edit' => array(array(1, 'products')),
		'products/products_save' => array(array('post:id', 'products')),
		'products/products_package_pattern' => array(array(1, 'products')),
		'products/products_patterns_delete' => array(array(1, 'products'), array(2, 'package_patterns')),
		'products/products_delete' => array(array(1, 'products')),
		'products/products_pattern_list_json' => array(array(1, 'products')),
		'products/products_patterns_add_info' => array(array(1, 'products')),
		'products/customer_products_list' => array(array(1, 'accounts')),
		'products/products_edit_reseller_optinproduct' => array(array(1, 'product_visible')),
		'products/products_reseller_save' => array(array('post:product_id', 'product_visible')),
		'products/products_reseller_confirm_order' => array(array('post:product_id', 'product_visible')),
		'products/products_reseller_optin_save' => array(array('post:product_id', 'product_buyable')),
		'products/products_reseller_option_save' => array(array('post:productid', 'product_buyable')),
		'products/products_optin' => array(array('post:product_id', 'product_buyable')),
		'did/did_resellerdid_save' => array(array('post:product_id', 'product_buyable')),
		// rates, price lists, taxes, refill coupons, templates
		'pricing/price_edit' => array(array(1, 'pricelists')),
		'pricing/price_save' => array(array('post:id', 'pricelists')),
		'ratedeck/ratedeck_edit' => array(array(1, 'ratedeck')),
		'ratedeck/ratedeck_delete' => array(array(1, 'ratedeck')),
		'ratedeck/ratedeck_save' => array(array('post:id', 'ratedeck')),
		'rates/origination_rate_edit' => array(array(1, 'routes')),
		'rates/origination_rate_save' => array(array('post:id', 'routes')),
		'rates/origination_rate_import_file' => array(array(1, 'pricelists')),
		'rates/customer_block_pattern_list' => array(array(1, 'accounts')),
		'rates/reseller_pricelist' => array(array('post:id', 'pricelists')),
		'refill_coupon/refill_coupon_list_view' => array(array(1, 'refill_coupon')),
		'refill_coupon/refill_coupon_customer_json' => array(array(1, 'accounts')),
		'reports/customer_cdrreport' => array(array(1, 'accounts')),
		'reports/customer_refillreport' => array(array(1, 'accounts')),
		'reports/reseller_customerlist' => array(array('post:reseller_id', 'reseller')),
		'systems/template_edit' => array(array(1, 'template')),
		'systems/template_save' => array(array('post:id', 'template')),
		'taxes/taxes_edit' => array(array(1, 'taxes')),
		'taxes/taxes_delete' => array(array(1, 'taxes')),
		'taxes/taxes_save' => array(array('post:id', 'taxes')),
		// own account (customer portal, also used by resellers for their own account)
		'user/user_did_edit' => array(array(1, 'dids', 'self' => true)),
		'user/user_dids_action' => array(array(2, 'dids_available', 'when' => array(1, 'add')), array(2, 'dids', 'self' => true, 'unless' => array(1, 'add')),
			array('post:free_didlist', 'dids_available', 'when' => array(1, 'add'))),
		'user/user_did_forward' => array(array(1, 'dids_forward', 'self' => true, 'required' => true)),
		'user/user_did_forward_save' => array(array('post:id', 'dids', 'self' => true)),
		'user/user_did_local_change' => array(array(2, 'dids', 'self' => true, 'required' => true)),
		'user/user_invoice_logo_delete' => array(array(1, 'accounts', 'self' => true)),
		'user/user_invoices_download' => array(array(1, 'invoices', 'self' => true)),
		'user/user_invoice_payment' => array(array(1, 'invoices', 'self' => true)),
		'user/user_invoice_download' => array(array(1, 'invoices', 'self' => true)),
		'user/user_list_release' => array(array(1, 'dids', 'self' => true)),
		'user/user_ipmap_action' => array(array(2, 'ip_map', 'self' => true)),
		'user/user_sipdevices_edit' => array(array(1, 'sip_devices', 'self' => true)),
		'user/user_sipdevices_delete' => array(array(1, 'sip_devices', 'self' => true)),
		'user/user_sipdevices_save' => array(array('post:id', 'sip_devices', 'self' => true)),
		'user/user_animap_action' => array(array(2, 'ani_map', 'self' => true)),
		'user/user_orders_complete' => array(array(1, 'orders_any', 'self' => true)),
		'user/user_orders_terminate' => array(array(1, 'orders', 'col' => 'order_id', 'self' => true)),
		'user/user_orders_termination' => array(array('post:order_id', 'orders', 'col' => 'order_id', 'self' => true)),
		'user/user_fssipdevices_routing' => array(array(1, 'sip_devices', 'self' => true)),
		'user/user_myprofile' => array(array('post:id', 'own')),
		'user/user_change_password' => array(array('post:id', 'own')),
		'user/user_alert_threshold' => array(array('post:id', 'own')),
		'user/user_fund_transfer_save' => array(array('post:id', 'own')),
		'user/user_invoice_config' => array(array('post:id', 'invoice_conf')),
	);

	/** POST fields checked on every request of resellers, customers and providers. */
	private static $post_accounts = array('accountid', 'account_id', 'accountcode');

	/** Methods whose POST field accountid carries the account NUMBER (read-only display field of the form). */
	private static $post_account_number = array('accounts/customer_payment_save');

	/** Scope names that are checked on another table. */
	private static $alias = array('products_optin' => 'products');

	/** Owner columns: a = accountid, r = reseller_id, ar = both. */
	private static $owner = array(
		'ani_map' => 'ar', 'ip_map' => 'ar', 'sip_devices' => 'ar', 'invoices' => 'ar', 'mail_details' => 'ar',
		'orders' => 'ar', 'order_items' => 'ar', 'payment_transaction' => 'ar', 'commission' => 'ar',
		'block_patterns' => 'a', 'speed_dial' => 'a', 'accounts_callerid' => 'a', 'taxes_to_accounts' => 'a', 'counters' => 'a',
		'products' => 'r', 'pricelists' => 'r', 'routes' => 'r', 'taxes' => 'r', 'ratedeck' => 'r', 'default_templates' => 'r',
		'refill_coupon' => 'r', 'cli_group' => 'r',
	);

	private $CI;
	private $role;
	private $me;
	private $parent;

	public function check()
	{
		$CI = & get_instance();
		$this->CI = $CI;
		$RTR = $CI->router;
		$module = method_exists($RTR, 'fetch_module') ? (string) $RTR->fetch_module() : '';
		if ($module === '') {
			return; // root controllers (API, cron, status) have their own checks
		}
		$class = strtolower($RTR->fetch_class());
		$method = strtolower($RTR->fetch_method());
		$key = $module . '/' . ($class === $module ? '' : $class . '/') . $method;
		$params = array_values(array_slice($CI->uri->rsegment_array(), 2));

		if (in_array($key, self::$internal, true)) {
			$this->deny($key);
		}
		$bulk = array_key_exists($key, self::$bulk);
		if ($bulk && $CI->input->server('REQUEST_METHOD') !== 'POST') {
			$this->deny($key);
		}
		if (! $CI->session->userdata('user_login')) {
			return;
		}
		$account = $CI->session->userdata('accountinfo');
		$type = is_array($account) && isset($account['type']) ? (string) $account['type'] : '';
		if ($type === '-1' || $type === '2') {
			return;
		}
		$this->role = ($type === '1' || $type === '5') ? 'reseller' : 'portal';
		$this->me = is_array($account) && isset($account['id']) ? (int) $account['id'] : 0;
		$this->parent = is_array($account) && isset($account['reseller_id']) ? (int) $account['reseller_id'] : 0;
		if ($this->me <= 0) {
			$this->deny($key);
		}

		if ($this->role === 'reseller') {
			foreach (self::$admin_only as $re) {
				if (preg_match($re, $key)) {
					$this->deny($key);
				}
			}
		} else {
			$allowed = false;
			foreach (self::$portal as $re) {
				if (preg_match($re, $key)) {
					$allowed = true;
					break;
				}
			}
			if (! $allowed) {
				$this->deny($key);
			}
		}

		if ($bulk) {
			$target = self::$bulk[$key];
			if ($target === null) {
				$this->deny($key);
			}
			$target = (array) $target;
			$ids = array();
			foreach (explode(',', (string) $CI->input->post('selected_ids')) as $id) {
				$id = trim($id, " '\"");
				if (ctype_digit($id) && $id !== '0') {
					$ids[] = $id;
				}
			}
			if (empty($ids) || ! $this->owned_all($target[0], isset($target[1]) ? $target[1] : 'id', $ids)) {
				$this->deny($key);
			}
		}

		$post = $CI->input->server('REQUEST_METHOD') === 'POST';
		if ($post) {
			foreach (self::$post_accounts as $field) {
				$value = $CI->input->post($field);
				if ($value === false) {
					continue;
				}
				$rule = array('post:' . $field, 'accounts', 'self' => true);
				if ($field === 'accountid' && in_array($key, self::$post_account_number, true)) {
					// refill form: shows the account number in accountid (the controller uses the posted id)
					$rule['col'] = 'number';
				}
				foreach ((array) $value as $v) {
					if (is_array($v) || ! $this->allowed($rule, (string) $v)) {
						$this->deny($key);
					}
				}
			}
		}
		if (isset(self::$rules[$key])) {
			foreach (self::$rules[$key] as $rule) {
				if (isset($rule['when']) && (string) ($params[$rule['when'][0] - 1] ?? '') !== $rule['when'][1]) {
					continue;
				}
				if (isset($rule['unless']) && (string) ($params[$rule['unless'][0] - 1] ?? '') === $rule['unless'][1]) {
					continue;
				}
				if (is_int($rule[0])) {
					if (! isset($params[$rule[0] - 1])) {
						if ($rule[1] === 'reseller' || ! empty($rule['required'])) {
							$this->deny($key);
						}
						continue;
					}
					$value = (string) $params[$rule[0] - 1];
				} else {
					if (! $post) {
						if ($rule[1] === 'reseller') {
							$this->deny($key);
						}
						continue;
					}
					$value = $CI->input->post(substr($rule[0], 5));
					if ($value === false) {
						if ($rule[1] === 'reseller') {
							$this->deny($key);
						}
						continue;
					}
				}
				if (is_array($value) || ! $this->allowed($rule, (string) $value)) {
					$this->deny($key);
				}
			}
		}
	}

	/** One value of a rule. */
	private function allowed($rule, $value)
	{
		$check = $rule[1];
		$value = trim($value);
		if ($check === 'reseller') {
			// list of accounts below a reseller: own id or one of the own sub-resellers
			if ($this->role !== 'reseller') {
				return false;
			}
			if (($value === '' || $value === '0') && ! empty($rule['empty'])) {
				// the method lists the own customers for an empty selection, but only for logintype 1
				return (string) $this->CI->session->userdata('logintype') === '1';
			}
			if (! ctype_digit($value)) {
				return false;
			}
			return (int) $value === $this->me || $this->count("SELECT COUNT(*) AS n FROM accounts WHERE id = " . (int) $value . " AND reseller_id = {$this->me}") > 0;
		}
		if ($value === '' || $value === '0') {
			return empty($rule['required']);
		}
		if ($check === 'attachment') {
			if (! preg_match('/^[A-Za-z0-9._-]+$/', $value) || strpos($value, '..') !== false) {
				return false;
			}
			$where = $this->scope('mail_details', true);
			return $where !== null && $this->count("SELECT COUNT(*) AS n FROM mail_details WHERE FIND_IN_SET(" . $this->CI->db->escape($value) . ", attachment) AND ($where)") > 0;
		}
		if (! ctype_digit($value)) {
			return false;
		}
		$v = (int) $value;
		$me = $this->me;
		$parent = $this->parent;
		switch ($check) {
			case 'own':
				return $v === $me;
			case 'product_buyable':
				// products the account may buy: those of its parent (reseller or admin); with 'topup' also the
				// top-up products (category 3) of the resellers above and the admin, which pages/topup_reseller
				// lists without owner filter
				$sql = "SELECT COUNT(*) AS n FROM products WHERE id = $v AND is_deleted = 0 AND (reseller_id = $parent"
					. ($parent > 0 ? " OR id IN (SELECT product_id FROM reseller_products WHERE account_id = $parent)" : '')
					. (! empty($rule['topup']) ? " OR (product_category = 3 AND reseller_id IN (" . implode(',', $this->ancestors()) . "))" : '') . ")";
				return $this->count($sql) > 0;
			case 'product_visible':
				if ($this->role !== 'reseller') {
					return false;
				}
				$sql = "SELECT COUNT(*) AS n FROM products WHERE id = $v AND (reseller_id IN ($me, $parent)"
					. " OR id IN (SELECT product_id FROM reseller_products WHERE account_id = $me))";
				return $this->count($sql) > 0;
			case 'dids_available':
				$where = $this->scope('dids', true);
				return $this->count("SELECT COUNT(*) AS n FROM dids WHERE id = $v AND ((accountid = 0 AND parent_id = $parent) OR ($where))") > 0;
			case 'dids_forward':
				// did_forward() takes a product id for resellers, a DID id otherwise
				return $this->owned_all('dids', $this->role === 'reseller' ? 'product_id' : 'id', array($v), ! empty($rule['self']));
			case 'orders_any':
				// orders_complete() accepts a commission id, an order id or an order number
				$self = ! empty($rule['self']);
				$commission = $this->count("SELECT COUNT(*) AS n FROM commission WHERE id = $v");
				if ($commission > 0 && ! $this->owned_all('commission', 'id', array($v), $self)) {
					return false;
				}
				$where = $this->scope('orders', $self);
				$sql = "SELECT COUNT(*) AS n, COALESCE(SUM(CASE WHEN ($where) THEN 1 ELSE 0 END), 0) AS ok FROM orders WHERE id = $v OR order_id = " . $this->CI->db->escape((string) $v);
				$row = $this->CI->db->query($sql)->row_array();
				return ($commission > 0 || $row['n'] > 0) && $row['n'] == $row['ok'];
			case 'template':
				// resellers edit their own templates or start from the admin default
				if ($this->role !== 'reseller') {
					return false;
				}
				return $this->count("SELECT COUNT(*) AS n FROM default_templates WHERE id = $v AND reseller_id IN (0, $me)") > 0;
		}
		return $this->owned_all($check, isset($rule['col']) ? $rule['col'] : 'id', array($v), ! empty($rule['self']));
	}

	/** True if all given ids exist in $table and belong to the current account. */
	private function owned_all($table, $col, $ids, $self = false)
	{
		$where = $this->scope($table, $self);
		if ($where === null) {
			return false;
		}
		$in = implode(',', array_map(function ($id) {
			return $this->CI->db->escape((string) $id);
		}, $ids));
		$sql = "SELECT COUNT(DISTINCT `$col`) AS ids, COUNT(*) AS n, COALESCE(SUM(CASE WHEN ($where) THEN 1 ELSE 0 END), 0) AS ok FROM `" . (isset(self::$alias[$table]) ? self::$alias[$table] : $table) . "` WHERE `$col` IN ($in)";
		$row = $this->CI->db->query($sql)->row_array();
		return $row['ids'] == count(array_unique($ids)) && $row['n'] == $row['ok'];
	}

	/** SQL condition for "row belongs to the current account", null if the table is not accessible. */
	private function scope($table, $self)
	{
		$me = $this->me;
		$owner = isset(self::$owner[$table]) ? self::$owner[$table] : '';
		if ($this->role === 'reseller') {
			$children = "(SELECT id FROM accounts WHERE reseller_id = $me)";
			switch ($table) {
				case 'accounts':
					return "reseller_id = $me" . ($self ? " OR id = $me" : '');
				case 'dids':
					return "parent_id = $me OR accountid IN $children" . ($self ? " OR accountid = $me" : '');
				case 'invoice_conf':
					return "accountid = $me OR reseller_id = $me";
				case 'products_optin':
					// own products and products of the parent the reseller opted in (opt-out by bulk delete)
					return "reseller_id = $me OR id IN (SELECT product_id FROM reseller_products WHERE account_id = $me)";
				case 'package_patterns':
					return "product_id IN (SELECT id FROM products WHERE reseller_id = $me)";
			}
			if ($owner === 'r') {
				return "reseller_id = $me";
			}
			if ($owner === 'a' || $owner === 'ar') {
				return "accountid IN $children" . ($self ? " OR accountid = $me" : '') . ($owner === 'ar' ? " OR (accountid = 0 AND reseller_id = $me)" : '');
			}
			return null;
		}
		switch ($table) {
			case 'accounts':
				return "id = $me";
			case 'dids':
			case 'invoice_conf':
				return "accountid = $me";
		}
		return ($owner === 'a' || $owner === 'ar') ? "accountid = $me" : null;
	}

	/** Parent accounts of the current account up to the admin (0). */
	private function ancestors()
	{
		$ids = array();
		for ($id = $this->parent; $id > 0 && count($ids) < 10 && ! in_array($id, $ids, true); ) {
			$ids[] = $id;
			$id = $this->count("SELECT COALESCE(MAX(reseller_id), 0) AS n FROM accounts WHERE id = $id");
		}
		$ids[] = 0;
		return $ids;
	}

	private function count($sql)
	{
		$row = $this->CI->db->query($sql)->row_array();
		return (int) $row['n'];
	}

	private function deny($key)
	{
		$CI = $this->CI ?: get_instance();
		log_message('error', 'Access_control: denied ' . $key . ' for account ' . (int) $this->me);
		$json = $CI->input->is_ajax_request() || preg_match('~(_json|_list_json)$~', $key)
			|| stripos((string) $CI->input->server('HTTP_ACCEPT'), 'application/json') !== false;
		if ($json) {
			set_status_header(403);
			header('Content-Type: application/json');
			echo json_encode(array('error' => 'Permission Denied!'));
			exit();
		}
		$CI->session->set_flashdata('astpp_notification', 'Permission Denied!');
		$type = $CI->session->userdata('userlevel_logintype');
		redirect(base_url() . (($type === '0' || $type === 0 || $type === '3' || $type === 3) ? 'user/user/' : 'dashboard/'));
		exit();
	}
}
