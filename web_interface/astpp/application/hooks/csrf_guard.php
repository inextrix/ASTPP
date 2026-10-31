<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * Protection against cross-site request forgery (CSRF).
 *
 * The CSRF filter of CodeIgniter 2 is not used: it needs the token in every hand-built form,
 * AJAX call and popup. Instead (hooks registered in config/hooks.php):
 *  - token: one random value per session (random_bytes), renewed when the login state changes
 *    (login, login_as_*, back to the admin account), kept in the session (ci_sessions);
 *  - start() (hook pre_controller) buffers the output. On the way out the filter adds the token
 *    to every POST form that posts to this host (also in popups loaded by AJAX and in grid JSON),
 *    to the links of the state-changing GET actions ($get_protected), and on full HTML pages the
 *    meta tag "astpp-csrf" and assets/js/astpp_csrf.js (header X-CSRF-Token on every same-origin
 *    AJAX request, token for forms and action links built by script);
 *  - check() (hook post_controller_constructor, before Access_control) requires the token for
 *    every request that is not GET/HEAD and for the GET actions in $get_protected: POST field
 *    astpp_csrf, header X-CSRF-Token or query parameter astpp_csrf. An Origin or Referer of
 *    another host is rejected as well. Afterwards the token is removed from $_POST/$_GET, many
 *    controllers save the whole POST array;
 *  - not checked: root controllers for FreeSWITCH, cron and internal calls, except
 *    $root_checked; the payment callbacks ($callbacks); the pages before the login
 *    ($before_login): Origin/Referer check only.
 * Rejected: back to the previous page with a message, HTTP 403 with JSON for AJAX/JSON requests.
 *
 * $get_protected must be extended when a new controller method changes data on GET (rule:
 * new state-changing actions should be POST).
 */
class Csrf_guard
{
	const FIELD = 'astpp_csrf';
	const HEADER = 'HTTP_X_CSRF_TOKEN';

	/** GET actions that change data (patterns on module/method, as in Access_control). */
	private static $get_protected = array(
		'~(delete|remove|release)~',                    // delete links, DID release
		'~_(action|save|complete|terminate)$~',         // record actions by URL (add/delete/...), saves, orders
		'~^login/login_as_~',                           // switch the session to another account
		'~(_import_file|_rates_import|_mapper_import|^account_import/customer_import_data)$~', // import of an uploaded file
		'~^(did/did_local_change|pages/proceed_payment|products/products_patterns_add_info|systems/languages_set_default|systems/database_restore_one|addons/addons_install|freeswitch/livecall_hangup|email/email_resend_customer|currencyupdate/update_currency)$~',
		'~^translation_script/~',
	);

	/** Matched by $get_protected but changes nothing. */
	private static $get_harmless = array('invoices/invoice_delete_massege');

	/** Root controllers used by the logged-in browser (the others serve FreeSWITCH, cron and internal calls). */
	private static $root_checked = array('getstatus', 'currencyupdate', 'translation_script');

	/** Pages before the login: Origin/Referer check only. */
	private static $before_login = '~^(login/(index|set_lang_global|get_language_text)|signup/)~';

	/** Payment provider callbacks: no check (posted by the provider's site). */
	private static $callbacks = '~^(login|pages)/paypal_response$~';

	/** Token of the current session, null: no filter. */
	private static $token = null;
	private static $mode = null;
	private static $held = '';

	/** Hook pre_controller: buffer the output (before the loader records its buffer level). */
	public function start()
	{
		if (php_sapi_name() === 'cli' || defined('CRON')) {
			return;
		}
		$RTR = & load_class('Router', 'core');
		$module = method_exists($RTR, 'fetch_module') ? (string) $RTR->fetch_module() : '';
		if ($module === '' && ! in_array(strtolower($RTR->fetch_class()), self::$root_checked, true)) {
			return;
		}
		// Output so far (php.ini output_buffering, stray whitespace after the closing PHP tag of early
		// files) moves into this buffer: the downloads call ob_clean() before the file and expect it to
		// remove that.
		$early = '';
		for ($level = ob_get_level(); $level > 0; $level--) {
			$chunk = ob_get_clean();
			if ($chunk === false || ob_get_level() >= $level) {
				break;
			}
			$early = $chunk . $early;
		}
		// large downloads are passed on in chunks of 1 MB, HTML is filtered as a whole
		ob_start(array(__CLASS__, 'filter'), 1048576);
		echo $early;
	}

	/** Hook post_controller_constructor: check state-changing requests. */
	public function check()
	{
		if (php_sapi_name() === 'cli' || defined('CRON')) {
			return;
		}
		$CI = & get_instance();
		$RTR = $CI->router;
		$module = method_exists($RTR, 'fetch_module') ? (string) $RTR->fetch_module() : '';
		$class = strtolower($RTR->fetch_class());
		$method = strtolower($RTR->fetch_method());
		if ($module === '') {
			if (! in_array($class, self::$root_checked, true) || astpp_internal_request()) {
				return;
			}
			$key = $class . '/' . $method;
		} else {
			$key = $module . '/' . ($class === $module ? '' : $class . '/') . $method;
		}
		if (preg_match(self::$callbacks, $key)) {
			self::strip();
			return;
		}
		if (! isset($CI->session)) {
			$CI->load->library('session');
		}
		self::$token = $this->session_token($CI);

		$verb = strtoupper((string) $CI->input->server('REQUEST_METHOD'));
		// the URL as typed counts too (routes like get_status/... lead to other names)
		$raw = strtolower($CI->uri->segment(1, '') . '/' . $CI->uri->segment(2, 'index'));
		if (! in_array($verb, array('GET', 'HEAD'), true) || self::get_protected($key) || self::get_protected($raw)) {
			if (! $this->same_origin($CI)) {
				$this->deny($CI, $key, 'origin');
			}
			if (! preg_match(self::$before_login, $key)) {
				$sent = $this->sent_token($CI);
				if ($sent === '' && $verb === 'POST' && empty($_POST) && empty($_FILES) && self::too_large()) {
					$this->deny($CI, $key, 'size');
				}
				if (! hash_equals(self::$token, $sent)) {
					$this->deny($CI, $key, $sent === '' ? 'missing' : 'wrong');
				}
			}
		}
		self::strip();
	}

	/** True for module/method keys of state-changing GET actions. */
	public static function get_protected($key)
	{
		if (in_array($key, self::$get_harmless, true)) {
			return false;
		}
		foreach (self::$get_protected as $re) {
			if (preg_match($re, $key)) {
				return true;
			}
		}
		return false;
	}

	/** Token of the session; a new one when there is none or the login state changed. */
	private function session_token($CI)
	{
		$account = $CI->session->userdata('accountinfo');
		$owner = $CI->session->userdata('user_login')
			? (is_array($account) && isset($account['id']) ? $account['id'] : '?') . '/' . $CI->session->userdata('logintype')
			: '';
		$token = (string) $CI->session->userdata(self::FIELD);
		if (strlen($token) !== 64 || (string) $CI->session->userdata(self::FIELD . '_for') !== $owner) {
			$token = bin2hex(random_bytes(32));
			$CI->session->set_userdata(array(self::FIELD => $token, self::FIELD . '_for' => $owner));
		}
		return $token;
	}

	private function sent_token($CI)
	{
		foreach (array($CI->input->server(self::HEADER), $_POST[self::FIELD] ?? null, $_GET[self::FIELD] ?? null) as $t) {
			if (is_string($t) && $t !== '') {
				return $t;
			}
		}
		return '';
	}

	/** Origin, otherwise Referer, must be this host (requests without both: the token decides). */
	private function same_origin($CI)
	{
		$origin = $CI->input->server('HTTP_ORIGIN');
		if (is_string($origin) && $origin !== '') {
			return in_array(self::origin_of($origin), self::own_origins(), true); // "null" -> no match
		}
		$referer = $CI->input->server('HTTP_REFERER');
		if (is_string($referer) && $referer !== '') {
			return in_array(self::origin_of($referer), self::own_origins(), true);
		}
		return true;
	}

	/** Origins of this portal: the requested host and base_url (reseller domains set base_url per host). */
	private static function own_origins()
	{
		$https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
		$own = array(self::origin_of(($https !== '' && $https !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '')), self::origin_of(base_url()));
		return array_values(array_filter(array_unique($own)));
	}

	private static function origin_of($url)
	{
		$p = parse_url(trim((string) $url));
		if (empty($p['scheme']) || empty($p['host'])) {
			return '';
		}
		$scheme = strtolower($p['scheme']);
		$port = isset($p['port']) ? (int) $p['port'] : 0;
		if (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)) {
			$port = 0;
		}
		return $scheme . '://' . strtolower($p['host']) . ($port ? ':' . $port : '');
	}

	/** POST body dropped by PHP because it exceeds post_max_size (upload too large). */
	private static function too_large()
	{
		$max = trim((string) ini_get('post_max_size'));
		$n = (int) $max;
		switch (strtolower(substr($max, -1))) {
			case 'g': $n *= 1024;
			case 'm': $n *= 1024;
			case 'k': $n *= 1024;
		}
		return $n > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $n;
	}

	/** Remove the token from the request data (controllers often save the whole POST array). */
	private static function strip()
	{
		unset($_POST[self::FIELD], $_GET[self::FIELD], $_REQUEST[self::FIELD]);
		foreach (array('REQUEST_URI', 'QUERY_STRING') as $k) {
			if (isset($_SERVER[$k]) && strpos($_SERVER[$k], self::FIELD . '=') !== false) {
				$_SERVER[$k] = rtrim(preg_replace('~(^|[?&])' . self::FIELD . '=[^&#]*&?~', '$1', $_SERVER[$k]), '?&');
			}
		}
	}

	private function deny($CI, $key, $reason)
	{
		log_message('error', 'Csrf_guard: rejected ' . $key . ' (' . $reason . ', origin ' . (string) $CI->input->server('HTTP_ORIGIN') . ')');		if ($reason === 'size') {
			$msg = sprintf(gettext('The upload is too large (at most %s). Nothing was saved.'), ini_get('post_max_size'));
		} else {
			$msg = gettext('This page has expired or the request did not come from this portal. Nothing was saved. Please reload the page and try again.');
		}
		$json = $CI->input->is_ajax_request() || preg_match('~_json$~', $key)
			|| stripos((string) $CI->input->server('HTTP_ACCEPT'), 'application/json') !== false;
		if ($json) {
			set_status_header(403);
			header('Content-Type: application/json');
			header('X-CSRF-Failed: 1');
			echo json_encode(array('error' => $msg, 'csrf' => true));
			exit();
		}
		$CI->session->set_flashdata('astpp_errormsg', $msg);
		redirect($this->back($CI));
		exit();
	}

	/** Previous page of this portal, else the start page of the role. */
	private function back($CI)
	{
		$referer = (string) $CI->input->server('HTTP_REFERER');
		if ($referer !== '' && in_array(self::origin_of($referer), self::own_origins(), true)) {
			$path = trim((string) parse_url($referer, PHP_URL_PATH), '/');
			$seg = explode('/', strtolower($path));
			$key = $seg[0] . '/' . ($seg[1] ?? 'index');
			if ($path !== '' && $path !== trim($CI->uri->uri_string(), '/') && ! self::get_protected($key)) {
				return base_url() . $path . '/';
			}
		}
		if (! $CI->session->userdata('user_login')) {
			return base_url() . 'login/login/';
		}
		$type = (string) $CI->session->userdata('userlevel_logintype');
		return base_url() . (($type === '0' || $type === '3') ? 'user/user/' : 'dashboard/');
	}

	// ------------------------------------------------------------------ output filter

	/** Output handler (ob_start): HTML and JSON with HTML get the token, downloads pass unchanged. */
	public static function filter($buffer, $phase)
	{
		if ($phase & PHP_OUTPUT_HANDLER_CLEAN) {
			self::$held = '';
			return '';
		}
		if (self::$mode === 'pass') {
			return $buffer;
		}
		self::$held .= $buffer;
		if (! ($phase & (PHP_OUTPUT_HANDLER_FINAL | PHP_OUTPUT_HANDLER_FLUSH))) {
			if (self::$token === null || ! self::is_html()) {
				// a download: pass this and all further chunks unchanged
				self::$mode = 'pass';
				$out = self::$held;
				self::$held = '';
				return $out;
			}
			return '';
		}
		$out = self::$held;
		self::$held = '';
		return (self::$token !== null && self::is_html()) ? self::rewrite($out) : $out;
	}

	private static function is_html()
	{
		foreach (headers_list() as $h) {
			$h = strtolower($h);
			if ((strpos($h, 'content-type:') === 0 && strpos($h, 'text/html') === false)
				|| (strpos($h, 'content-disposition:') === 0 && strpos($h, 'attachment') !== false)
				|| strpos($h, 'content-length:') === 0) {
				return false;
			}
		}
		return true;
	}

	/** Token into POST forms, action links and (full pages) meta tag + script. */
	private static function rewrite($s)
	{
		$t = self::$token;
		if ($s === '' || (stripos($s, '<form') === false && stripos($s, 'href=') === false && stripos($s, '<head') === false)) {
			return $s;
		}
		// last field of the form (views read "the first hidden field"); no quotes: also safe inside JS and JSON strings
		$input = '<input type=hidden name=' . self::FIELD . ' value=' . $t . '>';
		$s = preg_replace_callback('~(<form\b[^>]*>)(.*?)(<\\\\?/form\s*>)~is', function ($m) use ($input) {
			$tag = $m[1];
			if (! preg_match('~\smethod=\\\\?["\']?post\b~i', $tag)) {
				return $m[0];
			}
			if (preg_match('~\saction=(\\\\?["\'])(.*?)\1~is', $tag, $a) && ! self::own_url(str_replace('\/', '/', $a[2]), true)) {
				return $m[0]; // posts to another site (payment provider): no token
			}
			return $tag . $m[2] . $input . $m[3];
		}, $s);
		$s = preg_replace_callback('~(?<=\s)href=(\\\\?["\'])(.*?)\1~is', function ($m) use ($t) {
			$url = $m[2];
			if (strpos($url, self::FIELD . '=') !== false || ! preg_match('~^(https?:\\\\?/\\\\?/[^\s"\'<>()+]+|\\\\?/[^\s"\'<>()+]*)$~i', $url)) {
				return $m[0];
			}
			$plain = str_replace('\/', '/', $url);
			if (! self::own_url($plain, false) || ! self::url_protected($plain)) {
				return $m[0];
			}
			$hash = strpos($url, '#');
			$base = $hash === false ? $url : substr($url, 0, $hash);
			$frag = $hash === false ? '' : substr($url, $hash);
			return 'href=' . $m[1] . $base . (strpos($base, '?') === false ? '?' : '&') . self::FIELD . '=' . $t . $frag . $m[1];
		}, $s);
		// full documents (not JSON, not popup fragments): meta tag and script first in <head>
		$start = ltrim(substr($s, 0, 2000));
		if (preg_match('~^(<!DOCTYPE|<html)~i', $start) && preg_match('~<head\b[^>]*>~i', $s, $m, PREG_OFFSET_CAPTURE)) {
			$p = array();
			foreach (self::$get_protected as $re) {
				$p[] = substr($re, 1, -1);
			}
			$conf = htmlspecialchars(json_encode(array('field' => self::FIELD, 'get' => $p, 'harmless' => self::$get_harmless)), ENT_QUOTES);
			$js = FCPATH . 'assets/js/astpp_csrf.js';
			$tag = '<meta name="astpp-csrf" content="' . $t . '" data-conf="' . $conf . '">'
				. '<script src="' . base_url() . 'assets/js/astpp_csrf.js?v=' . (is_file($js) ? filemtime($js) : 0) . '"></script>';
			$at = $m[0][1] + strlen($m[0][0]);
			$s = substr($s, 0, $at) . $tag . substr($s, $at);
		}
		return $s;
	}

	/** URL of this portal ($relative: also relative URLs and an empty form action). */
	private static function own_url($url, $relative)
	{
		$url = trim(html_entity_decode($url, ENT_QUOTES));
		if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $url)) {
			return preg_match('~^https?:~i', $url) && in_array(self::origin_of($url), self::own_origins(), true);
		}
		if (strpos($url, '//') === 0) {
			return in_array(self::origin_of((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http:' : 'https:') . $url), self::own_origins(), true);
		}
		return $relative || strpos($url, '/') === 0;
	}

	/** Absolute or root-relative URL of a state-changing GET action. */
	private static function url_protected($url)
	{
		$path = (string) parse_url($url, PHP_URL_PATH);
		$base = (string) parse_url(base_url(), PHP_URL_PATH);
		if ($base !== '' && $base !== '/' && stripos($path, $base) === 0) {
			$path = substr($path, strlen($base));
		}
		$seg = explode('/', strtolower(trim($path, '/')));
		return $seg[0] !== '' && self::get_protected($seg[0] . '/' . ($seg[1] ?? 'index'));
	}
}
