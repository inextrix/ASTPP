<?php

if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
/**
 *
 * @author Samir Doshi
 *         Custom implemented Class for API logger
 *        
 */

require_once 'system/libraries/Log.php';
#[\AllowDynamicProperties]
class api_log extends CI_Log {
	
	// --------------------------------------------------------------------
	var $CI;
	function __construct() {
		parent::__construct ();
		$this->CI = & get_instance ();
		$this->CI->load->library ( 'user_agent' );
	}
	
	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param
	 *        	string the error level
	 * @param
	 *        	string the error message
	 * @param
	 *        	bool whether the error is a native PHP error
	 * @return bool
	 */
	public function write_log($level, $msg, $php_error = FALSE) {
		$level = strtoupper ( $level );
		
		// if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		// if($this->CI->config->item('api_debug_log') == FALSE)
		if (Common_model::$global_config ['system_config'] ['api_debug_log'] == '1') {
			return FALSE;
		}
		
		$filepath = Common_model::$global_config ['system_config'] ['log_path'] . 'api_' . date ( 'Y-m-d' ) . '.log';
		$message = '';
		
		if (! $fp = @fopen ( $filepath, FOPEN_WRITE_CREATE )) {
			return FALSE;
		}
		
		$message .= "[" . date ( $this->_date_fmt ) . "] [" . $this->CI->input->ip_address () . "] [" . $_SERVER ['HTTP_USER_AGENT'] . "] [" . $level . "] " . $msg . "\n";
		
		flock ( $fp, LOCK_EX );
		fwrite ( $fp, $message );
		flock ( $fp, LOCK_UN );
		fclose ( $fp );
		
		@chmod ( $filepath, FILE_WRITE_MODE );
		return TRUE;
	}
}
// END Log Class

/* End of file Log.php */
/* Location: ./system/libraries/Log.php */
