<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * PayPal payments with verified IPN.
 *
 * PayPal payments used to be booked from the POST data of the return/notify request alone (no
 * question to PayPal, amount from the field "custom", status "Pending" counted as paid, no check
 * of receiver, currency or a repeated transaction id), even with PayPal switched off.
 *
 * Now:
 *  - a payment form is only built while PayPal is active for the receiving account (system
 *    paypal_status = 0 of the admin, or of the customer's reseller - customers of a reseller pay
 *    the reseller's PayPal account, never the admin's on its behalf);
 *  - create() stores the expected amount, currency and receiver in paypal_payments; the form
 *    sends only the random token (item_number) of that row;
 *  - ipn() accepts a notification only if PayPal confirms it (cmd=_notify-validate -> VERIFIED),
 *    payment_status is Completed, receiver, currency and amount match the row and the txn_id
 *    has not been booked before; the row is claimed in one UPDATE (status 0 -> 1, txn_id unique),
 *    so the same payment is booked once even if PayPal repeats the notification.
 * Booking itself is done by the caller (pages/paypal_response) per kind of payment.
 */
class paypal {
	/** Largest amount of one PayPal payment (base currency). */
	const MAX_AMOUNT = 10000;

	private $CI;

	function __construct() {
		$this->CI = & get_instance();
	}

	/** Amount entered in a form: plain number 0..$max with '.' as separator, false otherwise. */
	public static function amount($value, $max = self::MAX_AMOUNT) {
		$value = (is_int($value) || is_float($value)) ? (string) $value : $value;
		if (! is_string($value) || ! preg_match('/^\d{1,9}(\.\d{1,5})?$/', trim($value))) {
			return false;
		}
		$f = (float) trim($value);
		return $f <= $max ? $f : false;
	}

	/** Receiver as configured: e-mail address (lower case) or PayPal merchant ID (as entered). */
	private static function receiver_id($value) {
		$value = trim((string) $value);
		return filter_var($value, FILTER_VALIDATE_EMAIL) ? strtolower($value) : $value;
	}

	/** E-mail address or PayPal merchant account ID (13 letters/digits). */
	private static function receiver_ok($value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false || preg_match('/^[A-Za-z0-9]{13}$/', (string) $value) === 1;
	}

	/** PayPal settings of an account that receives payments (0 = admin), null if none. */
	public function settings($owner) {
		$owner = (int) $owner;
		$rows = $this->CI->db->get_where('system', array('sub_group' => 'Paypal', 'reseller_id' => $owner))->result_array();
		if (empty($rows)) {
			return null;
		}
		$s = array();
		foreach ($rows as $r) {
			$s[$r['name']] = trim((string) $r['value']);
		}
		$live = isset($s['paypal_mode']) && $s['paypal_mode'] === '0';
		return array(
			'owner' => $owner,
			'active' => isset($s['paypal_status']) && $s['paypal_status'] === '0',
			'live' => $live,
			'url' => $live ? (isset($s['paypal_url']) ? $s['paypal_url'] : '') : (isset($s['paypal_sandbox_url']) ? $s['paypal_sandbox_url'] : ''),
			'receiver' => self::receiver_id($live ? (isset($s['paypal_id']) ? $s['paypal_id'] : '') : (isset($s['paypal_sandbox_id']) ? $s['paypal_sandbox_id'] : '')),
			'fee' => (isset($s['paypal_fee']) && $s['paypal_fee'] === '0' && isset($s['paypal_tax'])) ? (float) $s['paypal_tax'] : 0.0
		);
	}

	/**
	 * Settings of the PayPal account that receives payments of $account (array with id, type,
	 * reseller_id), null if PayPal is not active for it. Customers of a reseller pay the
	 * reseller (if it has no active PayPal account: no PayPal); everyone else pays the admin.
	 */
	public function for_account($account) {
		$owner = (isset($account['type']) && in_array((string) $account['type'], array('0', '3'), true)) ? (int) $account['reseller_id'] : 0;
		$s = $this->settings($owner);
		if ($s === null || ! $s['active'] || $s['url'] === '' || ! self::receiver_ok($s['receiver']) || ! $this->url_ok($s['url'])) {
			return null;
		}
		return $s;
	}

	/** Form and verification URL: https only. */
	private function url_ok($url) {
		$p = parse_url($url);
		return ! empty($p['scheme']) && ! empty($p['host']) && strtolower($p['scheme']) === 'https';
	}

	/**
	 * Pending payment: the amount the customer has to pay and the credit to book, both in base
	 * currency. Returns the row (with token) or null.
	 */
	public function create($account, $kind, $ref_id, $amount, $credit, $settings) {
		$amount = round((float) $amount, 2);
		if ($amount <= 0 || $amount > self::MAX_AMOUNT || ! in_array($kind, array('order', 'topup', 'invoice'), true)) {
			return null;
		}
		$row = array(
			'token' => bin2hex(random_bytes(16)),
			'accountid' => (int) $account['id'],
			'kind' => $kind,
			'ref_id' => (int) $ref_id,
			'amount' => $amount,
			'currency' => strtoupper((string) Common_model::$global_config['system_config']['base_currency']),
			'credit' => round((float) $credit, 5),
			'receiver' => $settings['receiver'],
			'receiver_reseller_id' => (int) $settings['owner'],
			'status' => 0,
			'created_date' => gmdate('Y-m-d H:i:s')
		);
		$this->CI->db->insert('paypal_payments', $row);
		$row['id'] = $this->CI->db->insert_id();
		return $row;
	}

	/** Data for views/paypal_redirect.php. */
	public function form_data($row, $settings, $item_name) {
		return array(
			'paypal_url' => $settings['url'],
			'paypal_email_id' => $settings['receiver'],
			'total_amt' => number_format((float) $row['amount'], 2, '.', ''),
			'currency_code' => $row['currency'],
			'order_id' => $row['token'],
			'item_name' => $item_name,
			'account_id' => $row['accountid'],
			'notify_url' => base_url() . 'pages/paypal_response/',
			'return' => base_url() . 'pages/paypal_return/',
			'cancel_return' => base_url() . 'pages/paypal_return/'
		);
	}

	/**
	 * Check an IPN (raw POST body). Returns array('row' => pending row, 'ipn' => fields) after the
	 * row has been claimed for this txn_id, or a string with the reason it was not booked.
	 */
	public function ipn($raw) {
		if (! is_string($raw) || $raw === '' || strlen($raw) > 20000) {
			return 'empty';
		}
		parse_str($raw, $ipn);
		$token = isset($ipn['item_number']) ? (string) $ipn['item_number'] : '';
		if (! preg_match('/^[0-9a-f]{32}$/', $token)) {
			return 'unknown item_number';
		}
		$row = $this->CI->db->get_where('paypal_payments', array('token' => $token))->row_array();
		if (empty($row)) {
			return 'unknown item_number';
		}
		$settings = $this->settings($row['receiver_reseller_id']);
		if ($settings === null || ! $settings['active'] || ! $this->url_ok($settings['url'])) {
			return 'paypal inactive';
		}
		if (! $this->verify($raw, $settings['url'])) {
			return 'not verified by PayPal';
		}
		$txn = isset($ipn['txn_id']) ? (string) $ipn['txn_id'] : '';
		// the receiver may be configured as e-mail address or as PayPal merchant ID (IPN field receiver_id)
		$receivers = array();
		foreach (array('receiver_email', 'business', 'receiver_id') as $k) {
			if (isset($ipn[$k]) && $ipn[$k] !== '') {
				$receivers[] = strtolower((string) $ipn[$k]);
			}
		}
		$status = isset($ipn['payment_status']) ? (string) $ipn['payment_status'] : '';
		if ($status !== 'Completed') {
			return 'payment_status ' . $status;
		}
		if (! preg_match('/^[A-Za-z0-9]{8,32}$/', $txn)) {
			return 'invalid txn_id';
		}
		if (! in_array(strtolower($row['receiver']), $receivers, true)) {
			return 'receiver mismatch';
		}
		if (strtoupper(isset($ipn['mc_currency']) ? (string) $ipn['mc_currency'] : '') !== $row['currency']) {
			return 'currency mismatch';
		}
		$gross = isset($ipn['mc_gross']) ? (string) $ipn['mc_gross'] : '';
		if (! preg_match('/^\d+(\.\d{1,2})?$/', $gross) || abs((float) $gross - (float) $row['amount']) > 0.005) {
			return 'amount mismatch';
		}
		if ($this->CI->db->get_where('paypal_payments', array('txn_id' => $txn))->num_rows() > 0) {
			return 'txn_id already booked';
		}
		// claim: only one request books this row
		$this->CI->db->where(array('id' => $row['id'], 'status' => 0));
		$this->CI->db->update('paypal_payments', array('status' => 1, 'txn_id' => $txn, 'booked_date' => gmdate('Y-m-d H:i:s')));
		if ($this->CI->db->affected_rows() !== 1) {
			return 'already booked';
		}
		$row['txn_id'] = $txn;
		return array('row' => $row, 'ipn' => $ipn);
	}

	/** Ask PayPal whether it sent this notification (postback of the unchanged message). */
	private function verify($raw, $form_url) {
		$p = parse_url($form_url);
		$host = strtolower($p['host']);
		// PayPal's postback hosts for its form hosts; other hosts as configured
		$map = array('www.paypal.com' => 'ipnpb.paypal.com', 'www.sandbox.paypal.com' => 'ipnpb.sandbox.paypal.com');
		$url = isset($map[$host]) ? 'https://' . $map[$host] . '/cgi-bin/webscr' : $form_url;
		$c = curl_init($url);
		curl_setopt_array($c, array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => 'cmd=_notify-validate&' . $raw,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTPHEADER => array('Connection: Close', 'User-Agent: ASTPP-IPN-Verification'),
			CURLOPT_PROTOCOLS => CURLPROTO_HTTPS
		));
		$res = curl_exec($c);
		$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);
		return $code == 200 && is_string($res) && trim($res) === 'VERIFIED';
	}
}
