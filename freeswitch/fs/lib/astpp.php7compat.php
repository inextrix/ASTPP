<?php
/*
 * PHP 7 comparison semantics for PHP 8 (ASTPP PHP 8.5 port).
 *
 * PHP 8 changed how numbers are compared with NON-numeric strings:
 *     '' == 0      PHP 7: true    PHP 8: false
 *     'abc' == 0   PHP 7: true    PHP 8: false
 *     '' < 0       PHP 7: false   PHP 8: true
 * ASTPP relies on the old behaviour in many places (empty form fields, "not found"
 * results of get_field_name(), empty settings ...). Comparisons of a possibly
 * string valued expression with 0 or a negative number were rewritten to these
 * helpers, which compare exactly like PHP 7 did: a string is converted to a number
 * (leading numeric part, '' -> 0) before comparing with the number.
 */
if (! function_exists('astpp_php7_num')) {
	function astpp_php7_num($v)
	{
		return is_string($v) ? (float) $v : $v;
	}
	function astpp_eq0($v)
	{
		return astpp_php7_num($v) == 0;
	}
	function astpp_lt($v, $n)
	{
		return astpp_php7_num($v) < $n;
	}
	function astpp_le($v, $n)
	{
		return astpp_php7_num($v) <= $n;
	}
	function astpp_gt($v, $n)
	{
		return astpp_php7_num($v) > $n;
	}
	function astpp_ge($v, $n)
	{
		return astpp_php7_num($v) >= $n;
	}
	// "$a == $b" with PHP 7 semantics (used where integer array keys of option
	// lists are compared with stored string values, e.g. '' == 0 selected option 0)
	function astpp_loose_eq($a, $b)
	{
		if ((is_int($a) || is_float($a)) && is_string($b)) {
			return $a == (float) $b;
		}
		if ((is_int($b) || is_float($b)) && is_string($a)) {
			return (float) $a == $b;
		}
		return $a == $b;
	}
}
/*
 * PHP 7 behaviour of internal functions that throw TypeError/ValueError in PHP 8.
 * PHP 7 emitted a warning and returned NULL (or false/0) instead - ASTPP relies on
 * that in many places (missing data, empty form fields). Calls were rewritten to
 * these wrappers. Results are verified against PHP 7.4 (tools/php7compat_test.php).
 */
if (! function_exists('astpp_zpp_num')) {
	// would PHP 7 accept $v for an int/float parameter? (numeric or leading-numeric
	// strings yes, '' / 'abc' / arrays / objects no)
	function astpp_zpp_num($v)
	{
		if ($v === null || is_bool($v) || is_int($v) || is_float($v)) {
			return true;
		}
		if (is_string($v)) {
			return is_numeric($v) || preg_match('/^\s*[+-]?(\d|\.\d)/', $v) === 1;
		}
		return false;
	}
	// PHP 7 conversion of a scalar to a number ('12abc' -> 12, 'abc' / '' -> 0)
	function astpp_php7_tonumber($v)
	{
		if (is_int($v) || is_float($v)) {
			return $v;
		}
		if ($v === null || $v === false) {
			return 0;
		}
		if ($v === true) {
			return 1;
		}
		if (is_string($v)) {
			if (is_numeric($v)) {
				return $v + 0;
			}
			if (preg_match('/^\s*[+-]?(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?/', $v, $m)) {
				return trim($m[0]) + 0;
			}
			return 0;
		}
		return is_object($v) ? 1 : 0;
	}
	// PHP 7 "<=>" for scalars (number vs. non-numeric string compared numerically)
	function astpp_php7_cmp($a, $b)
	{
		if ((is_int($a) || is_float($a)) && is_string($b)) {
			$b = astpp_php7_tonumber($b);
		} elseif ((is_int($b) || is_float($b)) && is_string($a)) {
			$a = astpp_php7_tonumber($a);
		}
		return $a <=> $b;
	}
	function astpp_count($v, $mode = COUNT_NORMAL)
	{
		if (is_array($v) || $v instanceof Countable) {
			return count($v, $mode);
		}
		return $v === null ? 0 : 1;
	}
	function astpp_sizeof($v, $mode = COUNT_NORMAL)
	{
		return astpp_count($v, $mode);
	}
	function astpp_in_array($needle, $haystack, $strict = false)
	{
		if (! is_array($haystack)) {
			return null;
		}
		if ($strict) {
			return in_array($needle, $haystack, true);
		}
		foreach ($haystack as $v) {
			if (astpp_loose_eq($v, $needle)) {
				return true;
			}
		}
		return false;
	}
	function astpp_array_search($needle, $haystack, $strict = false)
	{
		if (! is_array($haystack)) {
			return null;
		}
		if ($strict) {
			return array_search($needle, $haystack, true);
		}
		foreach ($haystack as $k => $v) {
			if (astpp_loose_eq($v, $needle)) {
				return $k;
			}
		}
		return false;
	}
	function astpp_array_key_exists($key, $array)
	{
		if ($key === null) {
			$key = '';
		}
		if (is_array($array)) {
			// PHP 7 only accepted string/int keys (float/bool keys: warning + false)
			return (is_int($key) || is_string($key)) ? array_key_exists($key, $array) : false;
		}
		if (is_object($array)) {
			return array_key_exists((string) $key, get_object_vars($array));
		}
		return null;
	}
	function astpp_implode($a = null, $b = null)
	{
		if (func_num_args() === 1) {
			return is_array($a) ? implode($a) : null;
		}
		if (is_array($b)) {
			return implode((string) $a, $b);
		}
		if (is_array($a)) {
			return implode((string) $b, $a);
		}
		return null;
	}
	function astpp_join($a = null, $b = null)
	{
		return func_num_args() === 1 ? astpp_implode($a) : astpp_implode($a, $b);
	}
	function astpp_array_merge(...$arrays)
	{
		foreach ($arrays as $a) {
			if (! is_array($a)) {
				return null;
			}
		}
		return $arrays ? array_merge(...$arrays) : array();
	}
	function astpp_array_keys($array, ...$rest)
	{
		return is_array($array) ? array_keys($array, ...$rest) : null;
	}
	function astpp_array_values($array)
	{
		return is_array($array) ? array_values($array) : null;
	}
	function astpp_array_unique($array, $flags = SORT_STRING)
	{
		return is_array($array) ? array_unique($array, $flags) : null;
	}
	function astpp_array_slice($array, $offset, $length = null, $preserve_keys = false)
	{
		if (! is_array($array) || ! astpp_zpp_num($offset)) {
			return null;
		}
		// PHP 7 converted a non-null length like a number ('' -> 0)
		return array_slice($array, (int) $offset, $length === null ? null : (int) astpp_php7_tonumber($length), $preserve_keys);
	}
	function astpp_array_filter($array, $callback = null, $mode = 0)
	{
		if (! is_array($array)) {
			return null;
		}
		return $callback === null ? array_filter($array) : array_filter($array, $callback, $mode);
	}
	function astpp_array_diff(...$arrays)
	{
		foreach ($arrays as $a) {
			if (! is_array($a)) {
				return null;
			}
		}
		return array_diff(...$arrays);
	}
	function astpp_array_combine($keys, $values)
	{
		if (! is_array($keys) || ! is_array($values)) {
			return null;
		}
		if (count($keys) !== count($values)) {
			return false;
		}
		return array_combine($keys, $values);
	}
	function astpp_array_map($callback, ...$arrays)
	{
		foreach ($arrays as $a) {
			if (! is_array($a)) {
				return null;
			}
		}
		return array_map($callback, ...$arrays);
	}
	// max()/min() with PHP 7 comparison order (mixed numbers / strings)
	function astpp_php7_extreme(array $values, $sign)
	{
		$best = null;
		$first = true;
		foreach ($values as $v) {
			if ($first || astpp_php7_cmp($v, $best) * $sign > 0) {
				$best = $v;
				$first = false;
			}
		}
		return $best;
	}
	function astpp_max(...$args)
	{
		if (count($args) === 1) {
			if (! is_array($args[0])) {
				return null;
			}
			return $args[0] ? astpp_php7_extreme($args[0], 1) : false;
		}
		return astpp_php7_extreme($args, 1);
	}
	function astpp_min(...$args)
	{
		if (count($args) === 1) {
			if (! is_array($args[0])) {
				return null;
			}
			return $args[0] ? astpp_php7_extreme($args[0], -1) : false;
		}
		return astpp_php7_extreme($args, -1);
	}
	function astpp_end(&$array)
	{
		return (is_array($array) || is_object($array)) ? end($array) : null;
	}
	function astpp_reset(&$array)
	{
		return (is_array($array) || is_object($array)) ? reset($array) : null;
	}
	function astpp_current($array)
	{
		return (is_array($array) || is_object($array)) ? current($array) : null;
	}
	function astpp_key($array)
	{
		return (is_array($array) || is_object($array)) ? key($array) : null;
	}
	function astpp_number_format($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',')
	{
		// PHP 7 accepted 1, 2 or 4 arguments only (3 -> warning + NULL)
		if (func_num_args() === 3 || ! astpp_zpp_num($number) || ! astpp_zpp_num($decimals)) {
			return null;
		}
		// PHP 7 treated negative decimals as 0 (PHP 8 rounds to tens, hundreds, ...)
		$decimals = max(0, (int) astpp_php7_tonumber($decimals));
		if (func_num_args() === 4) {
			return number_format((float) astpp_php7_tonumber($number), $decimals, (string) $dec_point, (string) $thousands_sep);
		}
		return number_format((float) astpp_php7_tonumber($number), $decimals);
	}
	// round/floor/ceil/abs: PHP 7 converted any scalar to a number ('abc' -> 0,
	// objects -> 1) and returned false for arrays
	function astpp_round($value, $precision = 0)
	{
		if (is_array($value) || ! astpp_zpp_num($precision)) {
			return is_array($value) ? false : null;
		}
		return round((float) astpp_php7_tonumber($value), (int) astpp_php7_tonumber($precision));
	}
	function astpp_floor($value)
	{
		return is_array($value) ? false : floor((float) astpp_php7_tonumber($value));
	}
	function astpp_ceil($value)
	{
		return is_array($value) ? false : ceil((float) astpp_php7_tonumber($value));
	}
	function astpp_abs($value)
	{
		return is_array($value) ? false : abs(astpp_php7_tonumber($value));
	}
	function astpp_str_repeat($input, $times)
	{
		if (! astpp_zpp_num($times) || (int) $times < 0 || is_array($input) || (is_object($input) && ! method_exists($input, '__toString'))) {
			return null;
		}
		return str_repeat((string) $input, (int) $times);
	}
	// date()/gmdate(): PHP 7 rejected non-numeric timestamps (warning + false) and
	// treated an explicit NULL timestamp as 0 (1970), PHP 8 treats NULL as "now".
	function astpp_date($format, $timestamp = null)
	{
		if (func_num_args() < 2) {
			return date($format);
		}
		if (! astpp_zpp_num($timestamp)) {
			return false;
		}
		return date($format, (int) astpp_php7_tonumber($timestamp));
	}
	function astpp_gmdate($format, $timestamp = null)
	{
		if (func_num_args() < 2) {
			return gmdate($format);
		}
		if (! astpp_zpp_num($timestamp)) {
			return false;
		}
		return gmdate($format, (int) astpp_php7_tonumber($timestamp));
	}
}
/*
 * PHP 8 throws TypeError for arithmetic with a non-numeric string
 * ('' + 1, 'abc' * 2, -''); PHP 7 used the leading numeric part (or 0) and warned.
 * Operands of + - * / % ** that may be strings were rewritten to astpp_n(...).
 * Numbers, null, bools, arrays and objects are returned unchanged, so the
 * operator itself behaves exactly as before (array union, TypeError for arrays).
 */
if (! function_exists('astpp_n')) {
	function astpp_n($v)
	{
		if (! is_string($v) || is_numeric($v)) {
			return $v;
		}
		return astpp_php7_tonumber($v);
	}
}
/*
 * PHP 8 throws DivisionByZeroError for "$a / 0"; PHP 7 warned and returned the IEEE
 * result (INF, -INF or NAN). Divisions by anything but a non-zero number literal were
 * rewritten to astpp_div($a, $b).
 */
if (! function_exists('astpp_div')) {
	function astpp_div($a, $b)
	{
		$a = astpp_n($a);
		$b = astpp_n($b);
		if (is_array($a) || is_array($b) || is_object($a) || is_object($b) || $b != 0) {
			return $a / $b;
		}
		// "* 1" converts like the operator does ('-0' is int 0, -0.0 keeps its sign)
		return fdiv((float) ($a * 1), (float) ($b * 1));
	}
}
/*
 * chr(): PHP 7 parsed its argument quietly and used 0 when it was not an int
 * ('' , 'abc', arrays, out of range floats); PHP 8 throws TypeError. TCPDF relies
 * on chr('') (invoice PDFs).
 */
if (! function_exists('astpp_chr')) {
	function astpp_chr($c)
	{
		if (is_int($c)) {
			return chr($c);
		}
		if (is_string($c)) {
			if (! astpp_zpp_num($c)) {
				return "\0";
			}
			$c = astpp_php7_tonumber($c);
			if (is_int($c)) {
				return chr($c);
			}
		}
		if (is_float($c)) {
			return (is_finite($c) && $c >= -9.2233720368547758E+18 && $c < 9.2233720368547758E+18) ? chr((int) $c) : "\0";
		}
		if ($c === null || is_bool($c)) {
			return chr((int) $c);
		}
		return "\0";
	}
}
