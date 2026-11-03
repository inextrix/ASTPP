<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
// ------------------------------------------------------------------------

/**
 * form_countries
 *
 * Generates a select list of countries
 *
 * @access	public
 * @param	string, boolean, array
 * @return	string
 */

if ( ! function_exists('form_countries'))
{
	function form_countries($name, $selected, $attributes, $form_name = "")
	{
		$country_list = Common_model::$global_config['country_list'];
		$form = '<select name="'.$name.'"';

		foreach ($attributes as $key => $value)
		{
			$form .= " ".$key.'="'.$value.'"';
		}

		$form .= ">";
		
		if ($form_name != "") {
			$form .= "\n".'<option value="" selected="selected" >'.$form_name.'</option>';
		}

		foreach ($country_list as $key => $value)
		{
			$form .= "\n".'<option value="'.ucwords(strtolower($value)).'"';

			if (strtolower(trim($value)) == strtolower(trim($selected)))
			{
                            
							$form .= ' selected="selected" >';
                                
			} else
			{
				$form .= '>';
			}
			
			$form .= ucwords(strtolower($value)).'</option>';
		}

		$form .= "\n</select>";

		return $form;
	}
}

if ( ! function_exists('form_devicetype'))
{
	function form_devicetype($name, $selected, $attributes)
	{
		$CI = & get_instance();

		$CI->config->load('countries');

		$type_list = $CI->config->item('device_types');

		$form = '<select name="'.$name.'"';

		foreach ($attributes as $key => $value)
		{
			$form .= " ".$key.'="'.$value.'"';
		}

		$form .= ">";

		foreach ($type_list as $key => $value)
		{
			$form .= "\n".'<option value="'.$key.'"';

			if (astpp_loose_eq($key, $selected)) // PHP8: PHP 7 comparison
			{
				$form .= '  selected="selected">';
			} else
			{
				$form .= '>';
			}

			$form .= ucwords(strtolower($value)).'</option>';
		}

		$form .= "\n</select>";

		return $form;
	} 
}

// ------------------------------------------------------------------------
if ( ! function_exists('form_timezone'))
{
	function form_timezone($name, $selected, $attributes)
	{
		$CI = & get_instance();

		$CI->config->load('countries');

		$country_list = $CI->config->item('timezone1_list');

		$form = '<select name="'.$name.'"';

		foreach ($attributes as $key => $value)
		{
			$form .= " ".$key.'="'.$value.'"';
		}

		$form .= ">";

		foreach ($country_list as $key => $value)
		{
			$form .= "\n".'<option value="'.ucwords(strtolower($value)).'"';

			if (strtolower($value) == strtolower($selected))
			{
				$form .= '  selected="selected">';
			} else
			{
				$form .= '>';
			}

			$form .= ucwords(strtolower($value)).'</option>';
		}

		$form .= "\n</select>";

		return $form;
	}
}
//=========================================
if ( ! function_exists('form_languagelist'))
{
	function form_languagelist($name, $selected, $attributes)
	{
		$language_list = Common_model::$global_config['language_list'];

		$form = '<select name="'.$name.'"';

		foreach ($attributes as $key => $value)
		{
			$form .= " ".$key.'="'.$value.'"';
		}

		$form .= ">";

		foreach ($language_list as $key => $value)
		{
			$form .= "\n".'<option value="'.(strtolower($key)).'"';

			if (astpp_loose_eq($key, $selected)) // PHP8: PHP 7 comparison
			{
				$form .= ' selected>';
			} else
			{
				$form .= '>';
			}

			$form .= ucfirst(strtolower($value)).'</option>';
		}

		$form .= "\n</select>";

		return $form;
	}
}

//-----------------------------------------
if ( ! function_exists('form_select_default'))
{
	function form_select_default($name, $data, $selected, $attributes, $form_name = "")
	{
		$form = '<select name="'.$name.'"';

		foreach ($attributes as $key => $value)
		{
			$form .= " ".$key.'="'.$value.'"';
		}

		$form .= ">";
		
		if ($form_name != "") {
			$form .= "\n".'<option value="" selected="selected" >'.$form_name.'</option>';
		}
		
		foreach ($data as $key => $value)
		{
			$form .= "\n".'<option value="'.$key.'"';
			
			if (astpp_loose_eq($key, $selected)) // PHP8: PHP 7 comparison
			{
				$form .= ' selected>';
			} else
			{
				$form .= '>';
			}

			$form .= ucwords(strtolower($value)).'</option>';
		}

		$form .= "\n</select>";

		return $form;
		
	}
}	
//-----------------------------------------
if ( ! function_exists('form_disposition'))
{
	function form_disposition($name, $selected, $attributes)
	{
		$CI = & get_instance();

		$CI->config->load('countries');

		$type_list = $CI->config->item('disposition');

		$form = '<select name="'.$name.'"';

		foreach ($attributes as $key => $value)
		{
			$form .= " ".$key.'="'.$value.'"';
		}

		$form .= ">";

		foreach ($type_list as $key => $value)
		{
			$form .= "\n".'<option value="'.$key.'"';

			if (astpp_loose_eq($key, $selected)) // PHP8: PHP 7 comparison
			{
				$form .= '  selected="selected">';
			} else
			{
				$form .= '>';
			}

			$form .= $value.'</option>';
		}

		$form .= "\n</select>";

		return $form;
	} 
}

//-----------------------------------------
if ( ! function_exists('form_table_row'))
{
	function form_table_row($label, $field, $span) {
		echo '<tr><th width="10%"><label>'.$label.'</label></th><td>';
		echo $field;
		echo '<br/><span class="helptext">'.$span.'</span>';
		echo '</td></tr>';	
	}
}	
if ( ! function_exists('form_table_row1'))
{
	function form_table_row1($label, $field, $span) {
		echo '<tr><th width="10%"><label>'.$label.'</label></th><td>';
		echo $field;
		echo '<br/><span class="helptext">'.$span.'</span>';
		echo '</td>';	
	}
}
if ( ! function_exists('form_table_row2'))
{
	function form_table_row2($label, $field, $span) {
		echo '<th width="10%" style="padding-left: 20px"><label>'.$label.'</label></th><td>';
		echo $field;
		echo '<br/><span class="helptext">'.$span.'</span>';
		echo '</td></tr>';	
	}
}
//-------------------------------------		
if ( ! function_exists('form_table_row_1'))
{
	function form_table_row_1($label, $field, $span, $wh = '5', $wd = '15') {
		echo '<tr><th width="'.$wh.'%"><label>'.$label.'</label></th><td width="'.$wd.'%">';
		echo $field;
		echo '<br/><span class="helptext">'.$span.'</span>';
		echo '</td>';	
	}
}
if ( ! function_exists('form_table_row_2'))
{
	function form_table_row_2($label, $field, $span, $wh = '10', $wd = '10') {
		echo '<th width="'.$wh.'%"><label>'.$label.'</label></th><td width="'.$wd.'%">';
		echo $field;
		echo '<br/><span class="helptext">'.$span.'</span>';
		echo '</td>';	
	}
}
if ( ! function_exists('form_table_row_3'))
{
	function form_table_row_3($label, $field, $span, $wh = '10', $wd = '15') {
		echo '<th width="'.$wh.'%"><label>'.$label.'</label></th><td width="'.$wd.'%">';
		echo $field;
		echo '<br/><span class="helptext">'.$span.'</span>';
		echo '</td>';	
	}
}
if ( ! function_exists('form_table_row_4'))
{
	function form_table_row_4($label, $field, $span, $wh = '10', $wd = '') {
		if ($wd == '') // BUGFIX: was the undefined constant "wd" (always false)
			echo '<th width="'.$wh.'%"><label>'.$label.'</label></th><td>';
		else 
			echo '<th width="'.$wh.'%"><label>'.$label.'</label></th><td width="'.$wd.'%">';
		echo $field;
		echo '<br/><span class="helptext">'.$span.'</span>';
		echo '</td></tr>';	
	}
}
//===================================
?>