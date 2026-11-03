<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
/**
 * ****************************************************************
 * IMPORTANT!! : This is API belongs to Accounts only : IMPORTANT!!
 * ****************************************************************
 *
 * ===================================================
 * API Expected parameters :
 * ===================================================
 * Integer : id
 * String : token
 * String : action (To call profile_update, profile_password_change. Expected value : update, password_chnage)
 * JSON : object_update_params FORMAT : "{\"first_name\":\"Samir Doshi\",\"last_name\":\"Samir Doshi\"}",
 * String : username
 * String : old_password
 * String : password
 * String : object (If you want to update account password as well then set value account)
 *
 * ===================================================
 * API URL
 * ===================================================
 * For Balance : http://192.168.1.2:8081/api/account/balance
 * For Profile : http://192.168.1.2:8081/api/account/profile
 */
/**
 *
 * @todo :
 *       Confirm validation and response codes
 *       Expection handing for query fail case
 */
/**
 * Included accounts api controller as that is having all functions belogs and accounts and we are validating account for each api request.
 */
require APPPATH . '/controllers/api/account.php';
#[\AllowDynamicProperties]
class Common_function extends Account {
    function __construct() {
        parent::__construct ();
		$this->load->model ( 'common_model' );
		
		$this->load->library ( 'common' );
		
		$this->load->model ( 'db_model' );
    }

}