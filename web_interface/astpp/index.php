<?php
/*
 * ---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 * ---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 * development
 * testing
 * production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 *
 */
ini_set('display_errors','off'); // errors go to the PHP error log, never to the browser
ini_set ( "date.timezone", "UTC" );
define ( 'ENVIRONMENT', 'production' );
/*
 * ---------------------------------------------------------------
 * ERROR REPORTING
 * ---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */

if (defined ( 'ENVIRONMENT' )) {
	switch (ENVIRONMENT) {
		case 'development' :
			error_reporting ( E_ALL );
			break;
		
		case 'testing' :
		case 'production' :
			error_reporting ( 0 );
			break;
		
		default :
			exit ( 'The application environment is not set correctly.' );
	}
}

/*
 * ---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 * ---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" folder.
 * Include the path if the folder is not in the same directory
 * as this file.
 *
 */
$system_path = 'system';
/*
 * ---------------------------------------------------------------
 * ASSETS FOLDER NAME
 * ---------------------------------------------------------------
 *
 * This variable must contain the name of your "assets" folder.
 * Include the path if the folder is not in the same directory
 * as this file.
 *
 */
$assets_path = 'assets';
/*
 * ---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 * ---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server. If
 * you do, use a full server path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 *
 */
$application_folder = 'application';

/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here. For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT: If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller. Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 *
 */
// The directory name, relative to the "controllers" folder. Leave blank
// if your controller is not in a sub-folder within the "controllers" folder
// $routing['directory'] = '';

// The controller class file name. Example: Mycontroller
// $routing['controller'] = '';

// The controller function you wish to be called.
// $routing['function'] = '';

/*
 * -------------------------------------------------------------------
 * CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 *
 */
// $assign_to_config['name_of_config_item'] = 'value of config item';

// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS. DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 * Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

// Set the current directory correctly for CLI requests
if (defined ( 'STDIN' )) {
	chdir ( dirname ( __FILE__ ) );
}

if (realpath ( $system_path ) !== FALSE) {
	$system_path = realpath ( $system_path ) . '/';
}

// ensure there's a trailing slash
$system_path = rtrim ( $system_path, '/' ) . '/';

// Is the system path correct?
if (! is_dir ( $system_path )) {
	exit ( "Your system folder path does not appear to be set correctly. Please open the following file and correct this: " . pathinfo ( __FILE__, PATHINFO_BASENAME ) );
}

// Kinjal ASTPPCOM-975 Start
if((substr($_SERVER['REQUEST_URI'], 0, strlen('//')) === '//')){
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'],1);
}
$folder_name = explode('/',$_SERVER['REQUEST_URI']) ;
if($folder_name['1'] == 'api' || $folder_name['1'] == 'admin'){
        if (! is_dir ($application_folder . '/controllers/'.$folder_name['1'])) {
        $array = array (
            'status'  => false,
            'error'   => 'API Addon is not installed, Please contact administrator',
            'response_code' => 400
        );
        echo json_encode($array); die;
    }
}
// Kinjal ASTPPCOM-975 END

/*
 * -------------------------------------------------------------------
 * Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
// The name of THIS file
define ( 'SELF', pathinfo ( __FILE__, PATHINFO_BASENAME ) );

// The PHP file extension
// this global constant is deprecated.
define ( 'EXT', '.php' );

// Path to the system folder
define ( 'BASEPATH', str_replace ( "\\", "/", $system_path ) );

// Path to the front controller (this file)
define ( 'FCPATH', str_replace ( SELF, '', __FILE__ ) );

// Name of the "system folder"
define ( 'SYSDIR', trim ( strrchr ( trim ( BASEPATH, '/' ), '/' ), '/' ) );

// Name of the "assets folder'
define ( 'ASSETSDIR', realpath ( $assets_path ) . '/' );

// The path to the "application" folder
if (is_dir ( $application_folder )) {
	define ( 'APPPATH', $application_folder . '/' );
} else {
	if (! is_dir ( BASEPATH . $application_folder . '/' )) {
		exit ( "Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . SELF );
	}
	
	define ( 'APPPATH', BASEPATH . $application_folder . '/' );
}

/*
 * TRUE for CLI/cron runs and for HTTP requests the server makes to itself.
 * The jobs in `cron_settings` call the cron controllers via "wget {BASE_URL}...",
 * so these controllers must stay reachable over HTTP, but only from this host.
 */
function astpp_internal_request() {
	if (defined ( 'CRON' ) || php_sapi_name () === 'cli') {
		return TRUE;
	}
	$remote = isset ( $_SERVER ['REMOTE_ADDR'] ) ? ( string ) $_SERVER ['REMOTE_ADDR'] : '';
	if (strpos ( $remote, '127.' ) === 0 || $remote === '::1') {
		return TRUE;
	}
	return ($remote !== '' && isset ( $_SERVER ['SERVER_ADDR'] ) && $remote === ( string ) $_SERVER ['SERVER_ADDR']);
}

/*
 * Start a cron task (key of $config['argv'] in config/cron.php, e.g. "BroadcastEmail") in the
 * background, right after mails were queued or the base currency changed. Replaces
 * "cd <dir>/cron && screen -d -m php cron.php <task>": "php" is the system default CLI, which
 * need not be the PHP version of the web server, one caller used a development path, and screen
 * is not needed for a detached process. The minute cron runs the same tasks, so a failed start
 * only delays them.
 */
function astpp_cron_background($task) {
	if (! preg_match ( '/^[A-Za-z]+$/', ( string ) $task )) {
		return;
	}
	// CLI of the same PHP version as the web server (PHP_BINARY is php-fpm under the web server)
	$php = PHP_BINDIR . '/php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
	if (! is_executable ( $php )) {
		$php = 'php';
	}
	exec ( 'cd ' . escapeshellarg ( FCPATH . 'cron' ) . ' && ' . escapeshellarg ( $php ) . ' cron.php ' . $task . ' >/dev/null 2>&1 &' );
}

/*
 * May an SMS notification go out? $settings = name => value of the system settings group
 * "notifications" (Configuration -> Notifications, part "SMS"): sms_notications "0" means
 * enabled (ASTPP convention; anything else or missing = off), plus the operator's own
 * sms_api_key / sms_secret_key. The installation seeds the placeholders "EnterAPIKey" /
 * "EnterSecretKey"; like empty values they count as "not configured", so no request with them
 * goes to the SMS provider.
 */
function astpp_sms_configured($settings) {
	$enabled = isset ( $settings ['sms_notications'] ) ? trim ( ( string ) $settings ['sms_notications'] ) : '';
	if ($enabled !== '0') {
		return FALSE;
	}
	foreach ( array ('sms_api_key', 'sms_secret_key') as $name ) {
		$value = isset ( $settings [$name] ) ? trim ( ( string ) $settings [$name] ) : '';
		if ($value === '' || in_array ( strtolower ( $value ), array ('enterapikey', 'entersecretkey'), TRUE )) {
			return FALSE;
		}
	}
	return TRUE;
}

/*
 * Request parameters that ASTPP concatenates into SQL in many places (bulk actions of
 * the grids and the left panel quick search):
 *  - selected_ids:       "'1','2','3'" -> only quoted integers survive
 *  - left_panel_search:  quotes, backslash, backtick and semicolon removed
 */
if (isset ( $_POST ['selected_ids'] )) {
	$astpp_ids = array ();
	foreach ( explode ( ',', ( string ) $_POST ['selected_ids'] ) as $astpp_id ) {
		$astpp_id = trim ( $astpp_id, " '\"" );
		if ($astpp_id !== '' && ctype_digit ( $astpp_id )) {
			$astpp_ids [] = "'" . $astpp_id . "'";
		}
	}
	$_POST ['selected_ids'] = empty ( $astpp_ids ) ? "'0'" : implode ( ',', $astpp_ids );
	unset ( $astpp_ids, $astpp_id );
}
if (isset ( $_POST ['left_panel_search'] )) {
	$_POST ['left_panel_search'] = str_replace ( array (
			"'",
			'"',
			chr ( 92 ), // backslash
			'`',
			';' 
	), '', ( string ) $_POST ['left_panel_search'] );
}

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 *
 */
require_once BASEPATH . 'core/CodeIgniter.php';

/* End of file index.php */
/* Location: ./index.php */
