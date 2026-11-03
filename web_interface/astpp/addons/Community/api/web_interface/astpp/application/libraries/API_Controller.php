<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
#[\AllowDynamicProperties]
abstract class API_Controller extends CI_Controller {
	
	/**
	 * This defines the api format.
	 *
	 * Must be overridden it in a controller so that it is set.
	 *
	 * @var string|null
	 */
	protected $api_format = NULL;
	
	/**
	 * Defines the list of method properties such as limit, log and level
	 *
	 * @var array
	 */
	protected $methods = array ();
	
	/**
	 * List of allowed HTTP methods
	 *
	 * @var array
	 */
	protected $allowed_http_methods = array (
			'get',
			'delete',
			'post',
			'put' 
	);
	
	/**
	 * General request data and information.
	 * Stores accept, language, body, headers, etc.
	 *
	 * @var object
	 */
	protected $request = NULL;
	
	/**
	 * What is gonna happen in output?
	 *
	 * @var object
	 */
	protected $response = NULL;
	
	/**
	 * Stores DB, keys, key level, etc
	 *
	 * @var object
	 */
	protected $api = NULL;
	
	/**
	 * The arguments for the GET request method
	 *
	 * @var array
	 */
	protected $_get_args = array ();
	
	/**
	 * The arguments for the POST request method
	 *
	 * @var array
	 */
	protected $_post_args = array ();
	
	/**
	 * The arguments for the PUT request method
	 *
	 * @var array
	 */
	protected $_put_args = array ();
	
	/**
	 * The arguments for the DELETE request method
	 *
	 * @var array
	 */
	protected $_delete_args = array ();
	
	/**
	 * The arguments from GET, POST, PUT, DELETE request methods combined.
	 *
	 * @var array
	 */
	protected $_args = array ();
	
	/**
	 * If the request is allowed based on the API key provided.
	 *
	 * @var boolean
	 */
	protected $_allow = TRUE;
	
	/**
	 * Determines if output compression is enabled
	 *
	 * @var boolean
	 */
	protected $_zlib_oc = FALSE;
	
	/**
	 * List all supported methods, the first will be the default format
	 *
	 * @var array
	 */
	protected $_supported_formats = array (
			'xml' => 'application/xml',
			'json' => 'application/json',
			'jsonp' => 'application/javascript',
			'serialized' => 'application/vnd.php.serialized',
			'php' => 'text/plain',
			'html' => 'text/html',
			'csv' => 'application/csv' 
	);
	
	/**
	 * Constructor function
	 * 
	 * @todo Document more please.
	 */
	public function __construct() {
		parent::__construct ();
		
		// Loading my custom log class
		$this->load->library ( 'Api_log' );
		
		// Loading language file
		$this->lang->load ( 'api/api', config_item ( 'api_default_language' ) );
		
		// Lets grab the config and get ready to party
		$this->load->config ( 'api' );
		
		// How is this request being made? POST, DELETE, GET, PUT?
		$this->request = new stdClass ();
		$this->request->method = $this->_detect_method ();
		
		// Create argument container, if nonexistent
		if (! isset ( $this->{'_' . $this->request->method . '_args'} )) {
			$this->{'_' . $this->request->method . '_args'} = array ();
		}
		
		// Set up our GET variables
		$this->_get_args = astpp_array_merge ( $this->_get_args, $this->uri->ruri_to_assoc () );
		
		// $this->load->library('security');
		
		// This library is bundled with api_Controller 2.5+, but will eventually be part of CodeIgniter itself
		$this->load->library ( 'format' );
		
		// Try to find a format for the request (means we have a request body)
		$this->request->format = $this->_detect_input_format ();
		
		// Some Methods cant have a body
		$this->request->body = NULL;
		
		$this->{'_parse_' . $this->request->method} ();
		
		// Now we know all about our request, let's try and parse the body if it exists
		if ($this->request->format and $this->request->body) {
			$this->request->body = $this->format->factory ( $this->request->body, $this->request->format )->to_array ();
			// Assign payload arguments to proper method container
			$this->{'_' . $this->request->method . '_args'} = $this->request->body;
		}
		
		// Merge both for one mega-args variable
		$this->_args = astpp_array_merge ( $this->_get_args, $this->_put_args, $this->_post_args, $this->_delete_args, $this->{'_' . $this->request->method . '_args'} );
		
		// Which format should the data be returned in?
		$this->response = new stdClass ();
		$this->response->format = $this->_detect_output_format ();
		
		// Which format should the data be returned in?
		$this->response->lang = $this->_detect_lang ();
		
		// Developers can extend this class and add a check in here
		$this->early_checks ();
		
		$this->api = new StdClass ();
		// Load DB if its enabled
		$this->api->db = $this->db;
		
		// only allow ajax requests
		if (! $this->input->is_ajax_request () and config_item ( 'api_ajax_only' )) {
			$this->response ( array (
					'status' => false,
					'error' => 'Only AJAX requests are accepted.' 
			), 505 );
		}
	}
	
	/**
	 * Remap
	 *
	 * Requests are not made to methods directly, the request will be for
	 * an "object". This simply maps the object and method to the correct
	 * Controller method.
	 *
	 * @param string $object_called        	
	 * @param array $arguments
	 *        	The arguments passed to the controller method.
	 */
	public function _remap($object_called, $arguments) {
		$pattern = '/^(.*)\.(' . astpp_implode ( '|', astpp_array_keys ( $this->_supported_formats ) ) . ')$/';
		if (preg_match ( $pattern, $object_called, $matches )) {
			$object_called = $matches [1];
		}
		
		// Samir : Removed calling method like users_get or user_post as thats limitting us to use free Methods according to our requirement.
		// $controller_method = $object_called.'_'.$this->request->method;
		$controller_method = $object_called;
		// Samir : Ends
		
		// Do we want to log this method (if allowed by config)?
		$log_method = ! (isset ( $this->methods [$controller_method] ['log'] ) and $this->methods [$controller_method] ['log'] == FALSE);
		
		// Use keys for this method?
		$use_key = ! (isset ( $this->methods [$controller_method] ['key'] ) and $this->methods [$controller_method] ['key'] == FALSE);
		
		// Sure it exists, but can they do anything with it?
		if (! method_exists ( $this, $controller_method )) {
			$this->response ( array (
					'status' => false,
					'error' => $this->lang->line ( 'unknown_method' ) 
			), 404 );
		}
		
		// Doing key related stuff? Can only do it if they have a key right?
		if (config_item ( 'api_enable_keys' ) and ! empty ( $this->api->key )) {
			// Check the limit
			if (config_item ( 'api_enable_limits' ) and ! $this->_check_limit ( $controller_method )) {
				$this->response ( array (
						'status' => false,
						'error' => 'This API key has reached the hourly limit for this method.' 
				), 401 );
			}
			
			// If no level is set use 0, they probably aren't using permissions
			$level = isset ( $this->methods [$controller_method] ['level'] ) ? $this->methods [$controller_method] ['level'] : 0;
			
			// If no level is set, or it is lower than/equal to the key's level
			$authorized = $level <= $this->api->level;
			
			// IM TELLIN!
			if (config_item ( 'api_enable_logging' ) and $log_method) {
				$this->_log_request ( $authorized );
			}
			
			// They don't have good enough perms
			$authorized or $this->response ( array (
					'status' => false,
					'error' => 'This API key does not have enough permissions.' 
			), 401 );
		}
		
		// No key stuff, but record that stuff is happening
		/*
		 * else if (config_item('api_enable_logging') AND $log_method)
		 * {
		 * $this->_log_request($authorized = TRUE);
		 * }
		 */
		
		// And...... GO!
		$this->_fire_method ( array (
				$this,
				$controller_method 
		), $arguments );
	}
	
	/**
	 * Fire Method
	 *
	 * Fires the designated controller method with the given arguments.
	 *
	 * @param array $method
	 *        	The controller method to fire
	 * @param array $args
	 *        	The arguments to pass to the controller method
	 */
	protected function _fire_method($method, $args) {
		call_user_func_array ( $method, $args );
	}
	
	/**
	 * Response
	 *
	 * Takes pure data and optionally a status code, then creates the response.
	 *
	 * @param array $data        	
	 * @param null|int $http_code        	
	 */
	public function response($data = array(), $http_code = null) {
		global $CFG;
		
		// If data is empty and not code provide, error and bail
		if (empty ( $data ) && $http_code === null) {
			$http_code = 404;
			
			// create the output variable here in the case of $this->response(array());
			$output = NULL;
		} 		

		// Otherwise (if no data but 200 provided) or some data, carry on camping!
		else {
			// Adding status code into json array to send it in json response
			$data ['response_code'] = $http_code;
			
			// Is compression requested?
			if ($CFG->item ( 'compress_output' ) === TRUE && $this->_zlib_oc == FALSE) {
				if (extension_loaded ( 'zlib' )) {
					if (isset ( $_SERVER ['HTTP_ACCEPT_ENCODING'] ) and strpos ( $_SERVER ['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== FALSE) {
						ob_start ( 'ob_gzhandler' );
					}
				}
			}
			
			is_numeric ( $http_code ) or $http_code = 200;
			
			// If the format method exists, call and return the output in that format
			if (method_exists ( $this, '_format_' . $this->response->format )) {
				// Set the correct format header
				header ( 'Content-Type: ' . $this->_supported_formats [$this->response->format] );
				
				$output = $this->{'_format_' . $this->response->format} ( $data );
			}			

			// If the format method exists, call and return the output in that format
			elseif (method_exists ( $this->format, 'to_' . $this->response->format )) {
				// Set the correct format header
				header ( 'Content-Type: ' . $this->_supported_formats [$this->response->format] );
				
				$output = $this->format->factory ( $data )->{'to_' . $this->response->format} ();
			} 			

			// Format not supported, output directly
			else {
				$output = $data;
			}
		}
		
		header ( 'HTTP/1.1: ' . $http_code );
		header ( 'Status: ' . $http_code );
		
		// If zlib.output_compression is enabled it will compress the output,
		// but it will not modify the content-length header to compensate for
		// the reduction, causing the browser to hang waiting for more data.
		// We'll just skip content-length in those cases.
		if (! $this->_zlib_oc && ! $CFG->item ( 'compress_output' )) {
			// header('Content-Length: ' . strlen($output));
		}
		
		$loglevel = (isset ( $data ['error'] )) ? "error" : "info";
		$this->api_log->write_log ( $loglevel, "[" . $http_code . "] " . $output );
		exit ( $output );
	}
	
	/*
	 * Detect input format
	 *
	 * Detect which format the HTTP Body is provided in
	 */
	protected function _detect_input_format() {
		if ($this->input->server ( 'CONTENT_TYPE' )) {
			// Check all formats against the HTTP_ACCEPT header
			foreach ( $this->_supported_formats as $format => $mime ) {
				if (strpos ( $match = $this->input->server ( 'CONTENT_TYPE' ), ';' )) {
					$match = astpp_current ( explode ( ';', $match ) );
				}
				
				if ($match == $mime) {
					return $format;
				}
			}
		}
		
		return NULL;
	}
	
	/**
	 * Detect format
	 *
	 * Detect which format should be used to output the data.
	 *
	 * @return string The output format.
	 */
	protected function _detect_output_format() {
		$pattern = '/\.(' . astpp_implode ( '|', astpp_array_keys ( $this->_supported_formats ) ) . ')$/';
		
		// Check if a file extension is used
		if (preg_match ( $pattern, $this->uri->uri_string (), $matches )) {
			return $matches [1];
		}		

		// Check if a file extension is used
		elseif ($this->_get_args and ! is_array ( astpp_end ( $this->_get_args ) ) and preg_match ( $pattern, astpp_end ( $this->_get_args ), $matches )) {
			// The key of the last argument
			$last_key = astpp_end ( astpp_array_keys ( $this->_get_args ) );
			
			// Remove the extension from arguments too
			$this->_get_args [$last_key] = preg_replace ( $pattern, '', $this->_get_args [$last_key] );
			$this->_args [$last_key] = preg_replace ( $pattern, '', $this->_args [$last_key] );
			
			return $matches [1];
		}
		
		// A format has been passed as an argument in the URL and it is supported
		if (isset ( $this->_get_args ['format'] ) and astpp_array_key_exists ( $this->_get_args ['format'], $this->_supported_formats )) {
			return $this->_get_args ['format'];
		}
		
		// Otherwise, check the HTTP_ACCEPT (if it exists and we are allowed)
		if ($this->config->item ( 'api_ignore_http_accept' ) === FALSE and $this->input->server ( 'HTTP_ACCEPT' )) {
			// Check all formats against the HTTP_ACCEPT header
			foreach ( astpp_array_keys ( $this->_supported_formats ) as $format ) {
				// Has this format been requested?
				if (strpos ( $this->input->server ( 'HTTP_ACCEPT' ), $format ) !== FALSE) {
					// If not HTML or XML assume its right and send it on its way
					if ($format != 'html' and $format != 'xml') {
						
						return $format;
					} 					

					// HTML or XML have shown up as a match
					else {
						// If it is truly HTML, it wont want any XML
						if ($format == 'html' and strpos ( $this->input->server ( 'HTTP_ACCEPT' ), 'xml' ) === FALSE) {
							return $format;
						}						

						// If it is truly XML, it wont want any HTML
						elseif ($format == 'xml' and strpos ( $this->input->server ( 'HTTP_ACCEPT' ), 'html' ) === FALSE) {
							return $format;
						}
					}
				}
			}
		} // End HTTP_ACCEPT checking
		  
		// Well, none of that has worked! Let's see if the controller has a default
		if (! empty ( $this->api_format )) {
			return $this->api_format;
		}
		
		// Just use the default format
		return config_item ( 'api_default_format' );
	}
	
	/**
	 * Detect method
	 *
	 * Detect which HTTP method is being used
	 *
	 * @return string
	 */
	protected function _detect_method() {
		$method = strtolower ( $this->input->server ( 'REQUEST_METHOD' ) );
		
		if ($this->config->item ( 'enable_emulate_request' )) {
			if ($this->input->post ( '_method' )) {
				$method = strtolower ( $this->input->post ( '_method' ) );
			} elseif ($this->input->server ( 'HTTP_X_HTTP_METHOD_OVERRIDE' )) {
				$method = strtolower ( $this->input->server ( 'HTTP_X_HTTP_METHOD_OVERRIDE' ) );
			}
		}
		
		if (astpp_in_array ( $method, $this->allowed_http_methods ) && method_exists ( $this, '_parse_' . $method )) {
			return $method;
		}
		
		return 'get';
	}
	
	/**
	 * Detect language(s)
	 *
	 * What language do they want it in?
	 *
	 * @return null|string The language code.
	 */
	protected function _detect_lang() {
		if (! $lang = $this->input->server ( 'HTTP_ACCEPT_LANGUAGE' )) {
			return NULL;
		}
		
		// They might have sent a few, make it an array
		if (strpos ( $lang, ',' ) !== FALSE) {
			$langs = explode ( ',', $lang );
			
			$return_langs = array ();
			$i = 1;
			foreach ( $langs as $lang ) {
				// Remove weight and strip space
				list ( $lang ) = explode ( ';', $lang );
				$return_langs [] = trim ( $lang );
			}
			
			return $return_langs;
		}
		
		// Nope, just return the string
		return $lang;
	}
	
	/**
	 * Limiting requests
	 *
	 * Check if the requests are coming in a tad too fast.
	 *
	 * @param string $controller_method
	 *        	The method being called.
	 * @return boolean
	 */
	protected function _check_limit($controller_method) {
		// They are special, or it might not even have a limit
		if (! empty ( $this->api->ignore_limits ) or ! isset ( $this->methods [$controller_method] ['limit'] )) {
			// On your way sonny-jim.
			return TRUE;
		}
		
		// How many times can you get to this method an hour?
		$limit = $this->methods [$controller_method] ['limit'];
		
		// Get data on a keys usage
		$result = $this->api->db->where ( 'uri', $this->uri->uri_string () )->where ( 'api_key', $this->api->key )->get ( config_item ( 'api_limits_table' ) )->row ();
		
		// No calls yet, or been an hour since they called
		if (! $result or $result->hour_started < time () - (60 * 60)) {
			// Right, set one up from scratch
			$this->api->db->insert ( config_item ( 'api_limits_table' ), array (
					'uri' => $this->uri->uri_string (),
					'api_key' => isset ( $this->api->key ) ? $this->api->key : '',
					'count' => 1,
					'hour_started' => time () 
			) );
		} 		

		// They have called within the hour, so lets update
		else {
			// Your luck is out, you've called too many times!
			if ($result->count >= $limit) {
				return FALSE;
			}
			
			$this->api->db->where ( 'uri', $this->uri->uri_string () )->where ( 'api_key', $this->api->key )->set ( 'count', 'count + 1', FALSE )->update ( config_item ( 'api_limits_table' ) );
		}
		
		return TRUE;
	}
	
	/**
	 * Parse GET
	 */
	protected function _parse_get() {
		// Grab proper GET variables
		parse_str ( parse_url ( $_SERVER ['REQUEST_URI'], PHP_URL_QUERY ), $get );
		
		// Merge both the URI segments and GET params
		$this->_get_args = astpp_array_merge ( $this->_get_args, $get );
	}
	
	/**
	 * Parse POST
	 */
	protected function _parse_post() {
		$this->_post_args = $_POST;
		
		$this->request->format and $this->request->body = file_get_contents ( 'php://input' );
	}
	
	// INPUT FUNCTION --------------------------------------------------------------
	
	/**
	 * Retrieve a value from the GET request arguments.
	 *
	 * @param string $key
	 *        	The key for the GET request argument to retrieve
	 * @param boolean $xss_clean
	 *        	Whether the value should be XSS cleaned or not.
	 * @return string The GET argument value.
	 */
	public function get($key = NULL, $xss_clean = TRUE) {
		if ($key === NULL) {
			return $this->_get_args;
		}
		
		return astpp_array_key_exists ( $key, $this->_get_args ) ? $this->_xss_clean ( $this->_get_args [$key], $xss_clean ) : FALSE;
	}
	
	/**
	 * Retrieve a value from the POST request arguments.
	 *
	 * @param string $key
	 *        	The key for the POST request argument to retrieve
	 * @param boolean $xss_clean
	 *        	Whether the value should be XSS cleaned or not.
	 * @return string The POST argument value.
	 */
	public function post($key = NULL, $xss_clean = TRUE) {
		if ($key === NULL) {
			return $this->_post_args;
		}
		
		return astpp_array_key_exists ( $key, $this->_post_args ) ? $this->_xss_clean ( $this->_post_args [$key], $xss_clean ) : FALSE;
	}
	
	/**
	 * Process to protect from XSS attacks.
	 *
	 * @param string $val
	 *        	The input.
	 * @param boolean $process
	 *        	Do clean or note the input.
	 * @return string
	 */
	protected function _xss_clean($val, $process) {
		if (CI_VERSION < 2) {
			return $process ? $this->input->xss_clean ( $val ) : $val;
		}
		
		return $process ? $this->security->xss_clean ( $val ) : $val;
	}
	
	// SECURITY FUNCTIONS ---------------------------------------------------------
	
	/**
	 * Check if the client's ip is in the 'api_ip_whitelist' config
	 */
	protected function _check_whitelist_auth() {
		$whitelist = explode ( ',', config_item ( 'api_ip_whitelist' ) );
		
		array_push ( $whitelist, '127.0.0.1', '0.0.0.0' );
		
		foreach ( $whitelist as &$ip ) {
			$ip = trim ( $ip );
		}
		
		if (! astpp_in_array ( $this->input->ip_address (), $whitelist )) {
			$this->response ( array (
					'status' => false,
					'error' => 'Not authorized' 
			), 401 );
		}
	}
	
	/**
	 * Force it into an array
	 *
	 * @param object|array $data        	
	 * @return array
	 */
	protected function _force_loopable($data) {
		// Force it to be something useful
		if (! is_array ( $data ) and ! is_object ( $data )) {
			$data = ( array ) $data;
		}
		
		return $data;
	}
	
	// FORMATING FUNCTIONS ---------------------------------------------------------
	// Many of these have been moved to the Format class for better separation, but these methods will be checked too
	
	/**
	 * Encode as JSONP
	 *
	 * @param array $data
	 *        	The input data.
	 * @return string The JSONP data string (loadable from Javascript).
	 */
	protected function _format_jsonp($data = array()) {
		return $this->get ( 'callback' ) . '(' . json_encode ( $data ) . ')';
	}
	
	/*
	 * Functions for security
	 */
	protected function early_checks() {
		/*
		 * foreach ( $_SERVER as $key => $value )
		 * $this->api_log->write_log ( 'info', $key.">>>".$value );
		 */
		
		// Added patch to validate request from freeswitch to send push notification for callkit to ios
		// if ($_SERVER ['HTTP_USER_AGENT'] == 'freeswitch-curl/1.0' && isset ( $_POST ['uuid'] )) {
		// 	$_SERVER ['HTTP_X_AUTH_TOKEN'] = $this->post ( 'fs_key' );
		// }
		// Kinjal issue no 3405 ASTPPv5 Pro Dialer API
		if (($_SERVER['HTTP_USER_AGENT'] == 'freeswitch-curl/1.0' || $_SERVER['HTTP_USER_AGENT'] == 'freeswitch-curl/1.1' || (isset($_SERVER['HTTP_X_CDR_CALLSTART']) && !empty($_SERVER['HTTP_X_CDR_CALLSTART']) && !empty($_POST) && $_SERVER['HTTP_X_CDR_CALLSTART'] == $_POST['callstart'])) && isset($_POST['uuid'])) {
			$_SERVER['HTTP_X_AUTH_TOKEN'] = $this->post('fs_key');
		}
		// END
		if($this->_post_args['action'] != 'astpp_auth_token'){
			if (! isset ( $_SERVER ['HTTP_X_AUTH_TOKEN'] ) || config_item ( 'api_x_auth_token' ) != $_SERVER ['HTTP_X_AUTH_TOKEN']) {
				$this->response ( array (
						'status' => false,
						'error' => $this->lang->line ( 'error_authentication_invalid_token' ) 
				), 403 );
			}
		}
	}
}
