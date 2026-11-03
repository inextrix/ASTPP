<?php
/*
 * Convert portal passwords (accounts.password) from the old Blowfish value (encrypted with the
 * PRIVATE_KEY of astpp-config.conf) to password_hash() (Argon2id, bcrypt where PHP has no Argon2).
 * After this, logins no longer depend on PRIVATE_KEY, so the key can be changed per installation.
 *
 * Run once, BEFORE PRIVATE_KEY is changed (the old key is needed to read the old values).
 * Back up the accounts table first. Safe to run again: hashed, locked and empty passwords are skipped.
 *
 *   php UpdatePasswordsToHash.php           dry run, writes nothing
 *   php UpdatePasswordsToHash.php --apply   writes the hashes
 */
if (php_sapi_name () !== 'cli') {
	exit ( "Run from the command line.\n" );
}
$apply = in_array ( '--apply', $argv, true );

$config = parse_ini_file ( '/var/lib/astpp/astpp-config.conf' );
if (empty ( $config ['PRIVATE_KEY'] )) {
	exit ( "PRIVATE_KEY missing in /var/lib/astpp/astpp-config.conf\n" );
}
$key = $config ['PRIVATE_KEY'];
if (! in_array ( 'bf-ecb', openssl_get_cipher_methods (), true )) {
	exit ( "OpenSSL has no Blowfish (bf-ecb): enable the OpenSSL legacy provider first.\n" );
}

mysqli_report ( MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );
$db = new mysqli ( $config ['dbhost'], $config ['dbuser'], $config ['dbpass'], $config ['dbname'] );
$db->set_charset ( 'utf8' );

$algo = defined ( 'PASSWORD_ARGON2ID' ) ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
// "AND password = ?": a password changed while the script runs is not overwritten
$update = $db->prepare ( 'UPDATE accounts SET password = ? WHERE id = ? AND password = ?' );
$count = array (
		'converted' => 0,
		'already_hash' => 0,
		'locked_or_empty' => 0,
		'skipped' => 0
);

$rows = $db->query ( 'SELECT id, number, password FROM accounts' );
while ( $row = $rows->fetch_assoc () ) {
	$stored = ( string ) $row ['password'];
	if ($stored === '' || $stored [0] === '!') {
		$count ['locked_or_empty'] ++;
		continue;
	}
	if (password_get_info ( $stored ) ['algoName'] !== 'unknown') {
		$count ['already_hash'] ++;
		continue;
	}
	// same as common::decode(); the value only counts as read if encrypting it again gives the stored value
	$plain = openssl_decrypt ( decode_params ( $stored ), 'BF-ECB', $key, OPENSSL_RAW_DATA );
	if ($plain === false || $plain === '' || encode_params ( openssl_encrypt ( $plain, 'BF-ECB', $key, OPENSSL_RAW_DATA ) ) !== $stored) {
		$count ['skipped'] ++;
		echo "SKIP  id {$row['id']} ({$row['number']}): cannot decrypt with PRIVATE_KEY\n";
		continue;
	}
	if ($apply) {
		$hash = password_hash ( $plain, $algo );
		$id = ( int ) $row ['id'];
		$update->bind_param ( 'sis', $hash, $id, $stored );
		$update->execute ();
	}
	$count ['converted'] ++;
}

echo ($apply ? "Converted" : "Would convert") . ": {$count['converted']}\n";
echo "Already hashed: {$count['already_hash']}, locked/empty: {$count['locked_or_empty']}, skipped: {$count['skipped']}\n";
if (! $apply) {
	echo "Dry run - nothing written. Run with --apply to convert.\n";
}

function decode_params($string) {
	$data = str_replace ( array (
			'-',
			'$'
	), array (
			'+',
			'/'
	), $string );
	$mod4 = strlen ( $data ) % 4;
	if ($mod4) {
		$data .= substr ( '====', $mod4 );
	}
	return base64_decode ( $data );
}
function encode_params($string) {
	return str_replace ( array (
			'+',
			'/',
			'='
	), array (
			'-',
			'$',
			''
	), base64_encode ( $string ) );
}
