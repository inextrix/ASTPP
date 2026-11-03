<?php

if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

/*
 * |--------------------------------------------------------------------------
 * | File and Directory Modes
 * |--------------------------------------------------------------------------
 * |
 * | These prefs are used when checking and setting modes when working
 * | with the file system. The defaults are fine on servers with proper
 * | security, but you may wish (or even need) to change the values in
 * | certain environments (Apache running a separate process for each
 * | user, PHP under CGI with Apache suEXEC, etc.). Octal values should
 * | always be used to set the mode correctly.
 * |
 */
define ( 'FILE_READ_MODE', 0644 );
define ( 'FILE_WRITE_MODE', 0666 );
define ( 'DIR_READ_MODE', 0755 );
define ( 'DIR_WRITE_MODE', 0777 );

/*
 * |--------------------------------------------------------------------------
 * | File Stream Modes
 * |--------------------------------------------------------------------------
 * |
 * | These modes are used when working with fopen()/popen()
 * |
 */

define ( 'FOPEN_READ', 'rb' );
define ( 'FOPEN_READ_WRITE', 'r+b' );
define ( 'FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb' ); // truncates existing file data, use with care
define ( 'FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b' ); // truncates existing file data, use with care
define ( 'FOPEN_WRITE_CREATE', 'ab' );
define ( 'FOPEN_READ_WRITE_CREATE', 'a+b' );
define ( 'FOPEN_WRITE_CREATE_STRICT', 'xb' );
define ( 'FOPEN_READ_WRITE_CREATE_STRICT', 'x+b' );

//HP: Permission changes 25-Jan-2019
$PERMISSION_EDIT_ARRAY=array (
		"SIP Devices" => "freeswitch/fssipdevices/main",
		"IP Settings" => "ipmap/ipmap_detail/main",
		"Caller ID" => "animap/animap_detail/main",
		"DID" => "did/did_list/main",
		"Invoice" => "invoices/invoice_list/main",
		"Refill Report" => "reports/refillreport/main",
		"CDRs" => "reports/customerReport/main",
		"Emails" => "email/email_history_list/main",
		"Products" => "products/products_list/main",
);

$CUSTOMEREDIT_ARRAY=array (
		"Customer Profile" => "accounts/customer_edit/",
		"SIP Devices" => "accounts/customer_sipdevices/",
		"Caller IDs" => "accounts/customer_animap/",
		"DIDs" => "accounts/customer_dids/",
		"Products" => "accounts/customer_product/",
		"Speed Dial" => "accounts/customer_speeddial/",
		"Blocked Codes" => "accounts/customer_blocked_prefixes/",
		"Alert Threshold" => "accounts/customer_alert_threshold/",
		"Invoices" => "accounts/customer_invoices/",
		"Refill Report" => "accounts/customer_refillreport/",
		"CDRs"=>"accounts/customer_cdrs/",
		"Emails" => "accounts/customer_emailhistory/",
);
$RESELLERPROFILE_ARRAY=array (
		"My Profile" => "user/user_myprofile/",
		"Change Password" => "user/user_change_password/",
		"Invoice" => "user/user_invoices_list/",
		"Refill Report" => "user/user_refill_report/",
		"CDRs" => "user/user_cdrs/",
		"Emails" => "user/user_emails/",
		"Alert Threshold" => "user/user_alert_threshold/",
		"My Order" => "user/user_get_order_list/"
);

$PROVIDEREDIT_ARRAY=array (
		"Provider Profile" => "accounts/provider_edit/"
);

$RESELLEREDIT_ARRAY=array (
		"Reseller Profile" => "accounts/reseller_edit/"
);

$PACKAGEEDIT_ARRAY=array (
		"Package Details" => "package/package_edit/",
		"Package Codes" => "package/package_pattern_list/" 
);

$CUSTOMERPROFILE_ARRAY=array (
		"My Profile" => "user/user_myprofile/",
		"SIP Devices" => "user/user_sipdevices/",
		"IP Settings"=>"user/user_ipmap/",
		"Speed Dial"=>"user/user_speeddial/",
		"Alert Threshold"=>"user/user_alert_threshold/",
		"Change Password"=>"user/user_change_password/"
);

$PROVIDERPROFILE_ARRAY=array (
		"My Profile" => "user/user_myprofile/",
		"Change Password" => "user/user_change_password" 
);
define("PLANS_ARRAY",  serialize( array(
                                "Plans Details"=>"plans/plans_edit/",

				 "Plans Code"=>"plans/plans_code_list/"
)));
//Make menu dynamic
$dir=getcwd()."/application/config/addons";
$a = scandir($dir);

foreach($a as $key=>$val){

	if($val!=='.' && $val!=='..' && is_dir($dir."/".$val)){ // BUGFIX: condition was always true (also scanned files like index.html)
		$function=str_replace(".php","",$val);
		if(file_exists($dir."/".$val."/constants.php")){
			include_once($dir."/".$val."/constants.php");
		}
	}
}


define ( "RESELLERPROFILE_ARRAY", serialize ($RESELLERPROFILE_ARRAY) );

define ( "CUSTOMEREDIT_ARRAY", serialize ($CUSTOMEREDIT_ARRAY) );

define ( "PROVIDEREDIT_ARRAY", serialize ($PROVIDEREDIT_ARRAY) );
define ( "RESELLEREDIT_ARRAY", serialize ($RESELLEREDIT_ARRAY) );

define ( "PERMISSION_EDIT_ARRAY", serialize ($PERMISSION_EDIT_ARRAY) );
define ( "PACKAGEEDIT_ARRAY", serialize ($PACKAGEEDIT_ARRAY) );
define ( "CUSTOMERPROFILE_ARRAY", serialize ($CUSTOMERPROFILE_ARRAY) );
define ( "PROVIDERPROFILE_ARRAY", serialize ($PROVIDERPROFILE_ARRAY) );
define ( "DATABASE_DIRECTORY", FCPATH . 'database_backup' . DIRECTORY_SEPARATOR );
define ( 'LOCALE_REQUEST_PARAM', 'lang' );
define ( 'WEBSITE_DOMAIN', 'messages' );

/*
 * |--------------------------------------------------------------------------
 * | Define default language for UI
 * |--------------------------------------------------------------------------
 * |
 * | Set en_EN for English language
 * | Set es_ES for Spanish language
 * | Set fr_FR for French language
 * | Set pt_BR for portuguese Brazilian language 
 * |
 */
define ('DEFAULT_LANGUAGE','en_EN');

/* End of file constants.php */
/* Location: ./application/config/constants.php */
