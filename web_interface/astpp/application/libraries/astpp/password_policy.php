<?php
/*
 * Rule for portal passwords (accounts.password). chk_password_expression() returned NULL instead
 * of FALSE for most violations, so the account forms accepted almost anything ("abc").
 *
 *  - at least MIN_LENGTH characters (UTF-8 characters, at most MAX_LENGTH), no control characters;
 *    every other character is allowed (no composition rules);
 *  - not a common password: optional local list password_policy_list.txt next to this file (one
 *    password per line, lower case; no online lookup), compared without case. Also rejected: a
 *    listed word with only digits or symbols before or after it ("Sunshine2024!"), a repetition of
 *    a shorter piece ("abcabcabcabc") and plain sequences ("123456789012", "qwertzuiopas");
 *  - not equal to the account number, the e-mail address, its local part or the user name.
 *
 * Applies wherever a portal password is SET: account forms (admin, reseller, customer/provider),
 * change password, forgot password, CSV import. Existing passwords stay valid; the login shows the
 * "insecure password" warning for them. SIP device passwords keep their own rule
 * (chk_password_expression).
 */
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Password_policy {
	const MIN_LENGTH = 12;
	const MAX_LENGTH = 255;
	const LIST_FILE = 'password_policy_list.txt';
	// shortest list word the "word with digits/symbols around it" check looks for
	const MIN_CORE = 4;

	/** "\n" . list . "\n", loaded on first use ('' without list file) */
	private static $list = NULL;

	private static $sequences = array(
		'012345678901234567890123456789',
		'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz',
		'qwertyuiopasdfghjklzxcvbnm',
		'qwertzuiopasdfghjklyxcvbnm',
		'azertyuiopqsdfghjklmwxcvbn',
		'1q2w3e4r5t6y7u8i9o0p',
		'1qaz2wsx3edc4rfv5tgb6yhn7ujm8ik9ol0p'
	);

	/**
	 * '' if $password may be set, otherwise an error code: invalid_chars, too_short, too_long,
	 * personal, common. $personal: account number, e-mail address, user name (empty values are
	 * ignored).
	 */
	public function check($password, $personal = array()) {
		$password = (string) $password;
		if (! mb_check_encoding($password, 'UTF-8') || preg_match('/[\x00-\x1f\x7f]/', $password)) {
			return 'invalid_chars';
		}
		$length = mb_strlen($password, 'UTF-8');
		if ($length < self::MIN_LENGTH) {
			return 'too_short';
		}
		if ($length > self::MAX_LENGTH) {
			return 'too_long';
		}
		$lower = mb_strtolower($password, 'UTF-8');
		foreach ((array) $personal as $value) {
			$value = mb_strtolower(trim((string) $value), 'UTF-8');
			if ($value === '') {
				continue;
			}
			if ($lower === $value) {
				return 'personal';
			}
			$at = strrpos($value, '@');
			if ($at !== FALSE && $at > 0 && $lower === substr($value, 0, $at)) {
				return 'personal';
			}
		}
		if ($this->is_common($lower)) {
			return 'common';
		}
		return '';
	}

	private function text($code) {
		$texts = array(
			'invalid_chars' => 'must not contain control characters.',
			'too_short' => 'must be at least ' . self::MIN_LENGTH . ' characters long.',
			'too_long' => 'must not be longer than ' . self::MAX_LENGTH . ' characters.',
			'personal' => 'must not be the account number, the e-mail address or the user name.',
			'common' => 'is a commonly used password or a simple variant of one. Please choose another password.'
		);
		return isset($texts[$code]) ? $texts[$code] : 'is not allowed.';
	}

	/** Message for an error code of check(), translated; $label ("%s" for form validation) starts it. */
	public function message($code, $label = 'The password') {
		$text = $label . ' ' . $this->text($code);
		return function_exists('gettext') ? gettext($text) : $text;
	}

	/** Rule in one sentence, for hints next to password fields. */
	public function hint() {
		$text = 'At least ' . self::MIN_LENGTH . ' characters; not a common password, not the account number or e-mail address.';
		return function_exists('gettext') ? gettext($text) : $text;
	}

	private function is_common($lower) {
		if ($this->listed($lower)) {
			return TRUE;
		}
		// a listed word with digits/symbols before or after it
		$core = preg_replace('/^[^\p{L}]+|[^\p{L}]+$/u', '', $lower);
		if ($core !== $lower && mb_strlen($core, 'UTF-8') >= self::MIN_CORE && $this->listed($core)) {
			return TRUE;
		}
		// a shorter piece repeated: counts as the piece, which is shorter than the minimum
		if (preg_match('/^(.+?)\1+$/us', $lower, $m) && mb_strlen($m[1], 'UTF-8') < self::MIN_LENGTH) {
			return TRUE;
		}
		foreach (self::$sequences as $sequence) {
			if (strpos($sequence, $lower) !== FALSE || strpos(strrev($sequence), $lower) !== FALSE) {
				return TRUE;
			}
		}
		return FALSE;
	}

	private function listed($value) {
		if (self::$list === NULL) {
			$data = @file_get_contents(__DIR__ . '/' . self::LIST_FILE);
			if ($data === FALSE || $data === '') {
				// no list installed: only the other checks apply
				error_log('ASTPP password policy: ' . self::LIST_FILE . ' not found, common passwords are not checked against a list');
				self::$list = '';
			} else {
				self::$list = "\n" . str_replace("\r", '', $data) . "\n";
			}
		}
		return self::$list !== '' && strpos(self::$list, "\n" . $value . "\n") !== FALSE;
	}
}
