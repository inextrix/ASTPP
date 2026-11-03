<?php  if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter CAPTCHA Helper
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/helpers/xml_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Create CAPTCHA
 *
 * @access	public
 * @param	array	array of data for the CAPTCHA
 * @param	string	path to create the image in
 * @param	string	URL to the CAPTCHA image folder
 * @param	string	server path to font
 * @return	string
 */
if ( ! function_exists('create_captcha'))
{
	function create_captcha($data = '', $img_path = '', $img_url = '', $font_path = '')
	{
		$defaults = array('word' => '', 'img_path' => '', 'img_url' => '', 'img_width' => '150', 'img_height' => '30', 'font_path' => '', 'expiration' => 7200);

		foreach ($defaults as $key => $val)
		{
			if ( ! is_array($data))
			{
				if ( ! isset($$key) OR $$key == '')
				{
					$$key = $val;
				}
			} else
			{
				$$key = ( ! isset($data[$key])) ? $val : $data[$key];
			}
		}

		if ($img_path == '' OR $img_url == '')
		{
			return FALSE;
		}

		if ( ! @is_dir($img_path))
		{
			return FALSE;
		}

		if ( ! is_writable($img_path))
		{
			return FALSE;
		}

		if ( ! extension_loaded('gd'))
		{
			return FALSE;
		}

		// -----------------------------------
		// Remove old images
		// -----------------------------------

		list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);

		$current_dir = @opendir($img_path);

		while ($filename = @readdir($current_dir))
		{
			if ($filename != "." and $filename != ".." and $filename != "index.html")
			{
				$name = str_replace(".jpg", "", $filename);
				$name =	intval($name);
				$now  = intval($now);
				if ((astpp_n($name) + astpp_n($expiration)) < $now)
				{
					@unlink($img_path.$filename);
				}
			}
		}

		@closedir($current_dir);

		// -----------------------------------
		// Do we have a "word" yet?
		// -----------------------------------

	   if ($word == '')
	   {
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			$str = '';
			for ($i = 0; $i < 8; $i++)
			{
				$str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
			}

			$word = $str;
	   }

		// -----------------------------------
		// Determine angle and position
		// -----------------------------------

		$length	= strlen($word);
		$angle = ($length >= 6) ? rand(-(astpp_n($length) - 6), (astpp_n($length) - 6)) : 0;
		$x_axis	= rand(6, (astpp_div(360, $length)) - 16);
		$y_axis = (astpp_ge($angle, 0)) ? rand($img_height, $img_width) : rand(6, $img_height);

		// -----------------------------------
		// Create image
		// -----------------------------------

		// PHP.net recommends imagecreatetruecolor(), but it isn't always available
		if (function_exists('imagecreatetruecolor'))
		{
			$im = imagecreatetruecolor($img_width, $img_height);
		} else
		{
			$im = imagecreate($img_width, $img_height);
		}

		// -----------------------------------
		//  Assign colors
		// -----------------------------------

		$bg_color = imagecolorallocate($im, 255, 255, 255);
		$border_color	= imagecolorallocate($im, 153, 102, 102);
		$text_color		= imagecolorallocate($im, 204, 153, 153);
		$grid_color		= imagecolorallocate($im, 255, 182, 182);
		$shadow_color	= imagecolorallocate($im, 255, 240, 240);

		// -----------------------------------
		//  Create the rectangle
		// -----------------------------------

		ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);

		// -----------------------------------
		//  Create the spiral pattern
		// -----------------------------------

		$theta = 1;
		$thetac		= 7;
		$radius		= 16;
		$circles	= 20;
		$points		= 32;

		for ($i = 0; $i < (astpp_n($circles) * astpp_n($points)) - 1; $i++)
		{
			$theta = astpp_n($theta) + astpp_n($thetac);
			$rad = astpp_n($radius) * (astpp_div($i, $points));
			$x = (astpp_n($rad) * cos($theta)) + astpp_n($x_axis);
			$y = (astpp_n($rad) * sin($theta)) + astpp_n($y_axis);
			$theta = astpp_n($theta) + astpp_n($thetac);
			$rad1 = astpp_n($radius) * (astpp_div(astpp_n($i) + 1, $points));
			$x1 = (astpp_n($rad1) * cos($theta)) + astpp_n($x_axis);
			$y1 = (astpp_n($rad1) * sin($theta)) + astpp_n($y_axis);
			imageline($im, (int) $x, (int) $y, (int) $x1, (int) $y1, $grid_color); // PHP8: float -> int is deprecated (PHP 7 truncated too)
			$theta = astpp_n($theta) - astpp_n($thetac);
		}

		// -----------------------------------
		//  Write the text
		// -----------------------------------

		$use_font = ($font_path != '' AND file_exists($font_path) AND function_exists('imagettftext')) ? TRUE : FALSE;

		if ($use_font == FALSE)
		{
			$font_size = 5;
			$x = rand(0, (int) astpp_div($img_width, astpp_n($length) / 3)); // PHP8: float -> int is deprecated (PHP 7 truncated too)
			$y = 0;
		} else
		{
			$font_size = 16;
			$x = rand(0, (int) astpp_div($img_width, astpp_n($length) / 1.5)); // PHP8: float -> int is deprecated (PHP 7 truncated too)
			$y = astpp_n($font_size) + 2;
		}

		for ($i = 0; $i < strlen($word); $i++)
		{
			if ($use_font == FALSE)
			{
				$y = rand(0, astpp_n($img_height) / 2);
				imagestring($im, $font_size, $x, $y, substr($word, $i, 1), $text_color);
				$x = astpp_n($x) + astpp_n(astpp_n($font_size) * 2);
			}
			else
			{
				$y = rand(astpp_n($img_height) / 2, astpp_n($img_height) - 3);
				imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_path, substr($word, $i, 1));
				$x = astpp_n($x) + astpp_n($font_size);
			}
		}


		// -----------------------------------
		//  Create the border
		// -----------------------------------

		imagerectangle($im, 0, 0, astpp_n($img_width) - 1, astpp_n($img_height) - 1, $border_color);

		// -----------------------------------
		//  Generate the image
		// -----------------------------------

		$img_name = $now.'.jpg';

		ImageJPEG($im, $img_path.$img_name);

		$img = "<img class=\"w-100\" src=\"$img_url$img_name\" style=\"border:0;\" alt=\" \" />";

		PHP_VERSION_ID < 80000 && ImageDestroy($im);

		return array('word' => $word, 'time' => $now, 'image' => $img);
	}
}

// ------------------------------------------------------------------------

/* End of file captcha_helper.php */
/* Location: ./system/heleprs/captcha_helper.php */
