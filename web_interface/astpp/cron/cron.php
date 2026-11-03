#!/usr/bin/php
<?php
// Run: php cron.php controller/method/
// Block non-CLI calls
define ( 'CRON', TRUE );

// Load CRON config
require ('../application/config/cron.php');

// Set CRON mode ( live or beta )
define ( 'CRON_BETA_MODE', $config ['CRON_BETA_MODE'] );

// Set index.php location
if (isset ( $config ['CRON_CI_INDEX'] ) && $config ['CRON_CI_INDEX'])
	define ( 'CRON_CI_INDEX', $config ['CRON_CI_INDEX'] );
else
	define ( 'CRON_CI_INDEX', '../index.php' );

if (count ( $argv ) <= 2)
	if (count ( $config ['argv'] )) {
		$path = $argv [1];
		unset ( $argv [1] );
		// $argv = array_merge($argv, $config["argv"][$path]);
		$argv [0] = $argv [0];
		$argv [1] = $config ["argv"] [$path];
		$_SERVER ['argv'] = $argv;
	} else
		die ( 'Use: php cron.php controller/method' );
	
	// Simulate an HTTP request
$_SERVER ['PATH_INFO'] = $argv [1];
$_SERVER ['REQUEST_URI'] = $argv [1];
$_SERVER ['SERVER_NAME'] = $config ['SERVER_NAME'];

// print_r($_SERVER); exit;

// Set run time limit
set_time_limit ( $config ['CRON_TIME_LIMIT'] );

// Run CI and capture the output
ob_start ();

// chdir( dirname( CRON_CI_INDEX ) );
require (CRON_CI_INDEX); // main CI index.php file
$output = ob_get_contents ();

if (defined ( 'CRON_FLUSH_BUFFERS' ) && CRON_FLUSH_BUFFERS === TRUE) // PHP8: was an undefined constant (PHP 7: string, always false)
	while ( @ob_end_flush () )
		; // display buffer contents
else
	ob_end_clean ();

echo "\n";
?>
