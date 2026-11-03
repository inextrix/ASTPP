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
 * CodeIgniter Form Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/helpers/form_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Form Declaration
 *
 * Creates the opening portion of the form.
 *
 * @access	public
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */
if ( ! function_exists('form_open'))
{
	function form_open($action = '', $attributes = '', $hidden = array())
	{
		$CI = & get_instance();

		if ($attributes == '')
		{
			$attributes = 'method="post"';
		}

		// If an action is not a full URL then turn it into one
		if ($action && strpos($action, '://') === FALSE)
		{
			$action = $CI->config->site_url($action);
		}

		// If no action is provided then set to the current url
		$action OR $action = $CI->config->site_url($CI->uri->uri_string());

		$form = '<form action="'.$action.'"';

		$form .= _attributes_to_string($attributes, TRUE);

		$form .= '>';

		// Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
		if ($CI->config->item('csrf_protection') === TRUE AND ! (strpos($action, $CI->config->site_url()) === FALSE OR strpos($form, 'method="get"')))
		{
			$hidden[$CI->security->get_csrf_token_name()] = $CI->security->get_csrf_hash();
		}

		if (is_array($hidden) AND astpp_count($hidden) > 0)
		{
			$form .= sprintf("<div style=\"display:none\">%s</div>", form_hidden($hidden));
		}

		return $form;
	}
}
if(!function_exists('form_breadcrumb')){
	function form_breadcrumb($info=''){
	 $form=false;
	 if(!empty($info))
 	$form.="<div class='breadcrumb'>
	        <a href='".$info['url']."'>".$info['name']."</a>
		</div>";
	return $form;
	}
}
// ------------------------------------------------------------------------

/**
 * Form Declaration - Multipart type
 *
 * Creates the opening portion of the form, but with "multipart/form-data".
 *
 * @access	public
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */
if ( ! function_exists('form_open_multipart'))
{
	function form_open_multipart($action = '', $attributes = array(), $hidden = array())
	{
		if (is_string($attributes))
		{
			$attributes .= ' enctype="multipart/form-data"';
		} else
		{
			$attributes['enctype'] = 'multipart/form-data';
		}

		return form_open($action, $attributes, $hidden);
	}
}
//HP: PBX_ADDON -- Check in Opensource
if ( ! function_exists('form_dropdown_mini'))
{
	function form_dropdown_mini($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (astpp_count($selected) === 0)
		{
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}

		if ($extra != '') $extra = ' '.$extra;
		$multiple = (astpp_count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
				if(is_array($name)){
					 $str=null;
					 foreach($name as $key=>$value){
					  if($key !='class' && $key!='disabled')
					  $str.=$key."='$value' ";
					 }
					 if(isset($name['disabled']) && $name['disabled']== 'disabled'){
					  $str.='disabled = "disabled"';
					 }
					 $name['class'] = isset($name['class']) ? $name['class'] : '';
					 $form = '<select '.$str." class='col-md-6 form-control selectpicker form-control-lg ".$name['class'].$extra."' data-live-search='true'>\n";
				}else{
					if(!empty($extra)){
						$form = '<select  name="'.$name.'"' .$multiple." class='col-md-6 form-control selectpicker form-control-lg ".$extra."' data-live-search='true'>\n";
					}else{
						$form = '<select  name="'.$name.'"' .$multiple." class='col-md-6 form-control selectpicker form-control-lg' data-live-search='true'>\n";
					}
				}
//                if($extra != '' ){
//                    $form .= '<option value=""></option>';
//                }
//                 echo $form;exit;
		foreach ($options as $key => $val)
		{
			$key = (string)$key;

			if (is_array($val) && ! empty($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (astpp_in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string)$optgroup_val."</option>\n";
				}

				$form .= '</optgroup>'."\n";
			} else
			{
				$sel = (astpp_in_array($key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}
				if(isset($name['option_value']) && isset($name['option_text'])){
		  $sel= isset($name['value']) && $name['value']==$name['option_value']?'selected ="selected"':'';
		  $form .= '<option value="'.$name['option_value'].'"'.$sel.'>'.(string) $name['option_text']."</option>\n";
				}
		$form .= '</select>';
//echo $form; exit;
		return $form;
	}
}

// ------------------------------------------------------------------------

/**
 * Hidden Input Field
 *
 * Generates hidden fields.  You can pass a simple key/value string or an associative
 * array with multiple values.
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_hidden'))
{
	function form_hidden($name, $value = '', $recursing = FALSE)
	{
		static $form;

		if ($recursing === FALSE)
		{
			$form = "\n";
		}

		if (is_array($name))
		{
			foreach ($name as $key => $val)
			{
				form_hidden($key, $val, TRUE);
			}
			return $form;
		}

		if ( ! is_array($value))
		{
			$form .= '<input type="hidden" name="'.$name.'" value="'.form_prep($value, $name).'" />'."\n";
		} else
		{
			foreach ($value as $k => $v)
			{
				$k = (is_int($k)) ? '' : $k;
				form_hidden($name.'['.$k.']', $v, TRUE);
			}
		}

		return $form;
	}
}

// ------------------------------------------------------------------------

/**
 * Text Input Field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_input'))
{
	function form_input($data = '', $value = '', $extra = '')
	{
	//	echo "<pre>";print_r($data);
		$data["class"] = "col-md-12 form-control form-control-lg";
		$defaults = array('type' => 'text', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value,'placeholder'=>(( ! is_array($data)) ? $data['placeholder'] : ''));

		return "<input data-ripple "._parse_form_attributes($data, $defaults).$extra." />";
	}
}
/* ASTPP  3.0
 * For Image upload
 */
//  IMAGE VIEW  //
if ( ! function_exists('form_image'))
{
	function form_image($data = '', $value = '', $extra = '')
	{
		//print_r($data);exit;
		//$data["class"] = "col-md-5 form-control";
		$defaults = array('type' => 'image', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
		if ($data['value'] != '') {
			return	'<div class="col-md-12 p-0">
                   <div class="fileinput fileinput-new input-group" data-provides="fileinput">
	                    <div class="form-control" data-trigger="fileinput">
	                        <span class="fileinput-filename"></span>
	                    </div>
	                   <span class="input-group-addon btn btn-primary btn-file" style="display: table-cell;">
    	                 <span class="fileinput-new">Select file</span>
                   		 <input style="height:33px;"'._parse_form_attributes($data, $defaults).$extra.'">
                   		</span>
                   </div>
               </div>';
		}else{
			return "<div class='col-md-12 p-0'><image "._parse_form_attributes($data, $defaults).$extra." /></div>";
		}

	}
}
//  button tag delete image  //

if ( ! function_exists('form_img_delete'))
{
	function form_img_delete($data = '', $value = '', $extra = '')
	{
				//  echo '<pre>'; print_r($data); exit;
			//$data["class"] = "col-md-5 form-control";
			$data['value']='Delete';
		$defaults = array('type' => 'button', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => 'Delete');

		return "<div class='col-md-12 p-0'><input "._parse_form_attributes($data, $defaults).$extra." /></div>";
	}
}
// ------------------------------------------------------------------------

/**
 * Password Field
 *
 * Identical to the input function but adds the "password" type
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_password'))
{
	function form_password($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'password';
		$data['autocomplete'] = "off";
		return form_input($data, $value, $extra);
	}
}

// ------------------------------------------------------------------------

/**
 * Upload Field
 *
 * Identical to the input function but adds the "file" type
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_upload'))
{
	function form_upload($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'file';
		return form_input($data, $value, $extra);
	}
}

// ------------------------------------------------------------------------

/**
 * Textarea field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_textarea'))
{
	function form_textarea($data = '', $value = '', $extra = '')
	{
		// $defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'cols' => '40', 'rows' => '10');
		$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'cols' => '40');

		if ( ! is_array($data) OR ! isset($data['value']))
		{
			$val = $value;
		} else
		{
			$val = $data['value'];
			unset($data['value']); // textareas don't use the value attribute
		}

		$name = (is_array($data)) ? $data['name'] : $data;
		return "<textarea "._parse_form_attributes($data, $defaults).$extra.">".form_prep($val, $name)."</textarea>";
	}
}

// ------------------------------------------------------------------------

/**
 * Multi-select menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	mixed
 * @param	string
 * @return	type
 */
if ( ! function_exists('form_multiselect'))
{
	function form_multiselect($name = '', $options = array(), $selected = array(), $extra = '')
	{

			if ( ! strpos($extra, 'multiple'))
		{

			$extra .= ' multiple="multiple"';
		}

		return form_dropdown($name, $options, $selected, $extra);
	}
}

// --------------------------------------------------------------------

/**
 * Drop-down Menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_dropdown'))
{
	function form_dropdown($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (astpp_count($selected) === 0)
		{
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}

		if ($extra != '') $extra = ' '.$extra;
		$multiple = (astpp_count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
				if(is_array($name)){
					 $str=null;
					 foreach($name as $key=>$value){
					  if($key !='class' && $key!='disabled')
					  $str.=$key."='$value' ";
					 }
					 if(isset($name['disabled']) && $name['disabled']== 'disabled'){
					  $str.='disabled = "disabled"';
					 }
					 $name['class'] = isset($name['class']) ? $name['class'] : '';
					 $form = '<select '.$str." class='col-md-12 form-control selectpicker form-control-lg ".$name['class'].$extra."' data-live-search='true'>\n";
				}else{
					if(!empty($extra)){
						$form = '<select  name="'.$name.'"' .$multiple." class='col-md-12 form-control selectpicker form-control-lg ".$extra."' data-live-search='true'>\n";
					}else{
						$form = '<select  name="'.$name.'"' .$multiple." class='col-md-12 form-control selectpicker form-control-lg' data-live-search='true'>\n";
					}
				}
//                if($extra != '' ){
//                    $form .= '<option value=""></option>';
//                }
//                 echo $form;exit;
		foreach ($options as $key => $val)
		{
			$key = (string)$key;

			if (is_array($val) && ! empty($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (astpp_in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string)$optgroup_val."</option>\n";
				}

				$form .= '</optgroup>'."\n";
			} else
			{
				$sel = (astpp_in_array($key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}
				if(isset($name['option_value']) && isset($name['option_text'])){
		  $sel= isset($name['value']) && $name['value']==$name['option_value']?'selected ="selected"':'';
		  $form .= '<option value="'.$name['option_value'].'"'.$sel.'>'.(string) $name['option_text']."</option>\n";
				}
		$form .= '</select>';
//echo $form; exit;
		return $form;
	}
}
if ( ! function_exists('form_dropdown_all'))
{
//HP: PBX_ADDON
	function form_dropdown_all($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (astpp_count($selected) === 0)
		{

			if (!empty($_POST)) {
				if (isset($_POST[$name]) && $_POST[$name] !='') {
					$selected = array($_POST[$name]);
				}
			}
		}
		$onchange = "";
		if(isset($name['onchange']) && $name['onchange'] != ''){
			$onchange = "onchange = ".$name['onchange'];
		}
		if ($extra != '') $extra = ' '.$extra;
				$class= isset($name['class']) && !empty($name['class']) ? "col-md-12 form-control form-control-lg selectpicker $name[class]" :"col-md-12 form-control form-control-lg selectpicker";
		$multiple = (astpp_count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
		if(is_array($name) && !isset($name["id"])){
					$form = '<select name="'.$name['name'].'"'." class='$class selectpicker' data-live-search='true' ".$onchange.">\n";
				}else if(is_array($name) && isset($name["id"])){
					$form = '<select name="'.$name['name'].'" id="'.$name['id'].'"'."class='$class selectpicker' data-live-search='true' ".$onchange.">\n";
				}
				else{
					$form = '<select name="'.$name.'"' .$multiple." class='$class selectpicker' data-live-search='true' ".$onchange.">\n";
				}

		$form .= '<option value="">'. gettext("--Select--") .' </option>';
		foreach ($options as $key => $val)
		{
			$key = (string) $key;

			if (is_array($val) && ! empty($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (astpp_in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string)$optgroup_val."</option>\n";
				}

				$form .= '</optgroup>'."\n";
			} else
			{
				$sel = (astpp_in_array($key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}
				if(isset($name['option_value']) && isset($name['option_text'])){
		  $sel= isset($name['value']) && $name['value']==$name['option_value']?'selected ="selected"':'';
		  $form .= '<option value="'.$name['option_value'].'"'.$sel.'>'.(string) $name['option_text']."</option>\n";
				}
		$form .= '</select>';

		return $form;

	}
}


if ( ! function_exists('form_dropdown_all_search'))
{
	function form_dropdown_all_search($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (astpp_count($selected) === 0)
		{
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}

		/*
		  ASTPP  3.0  For Search Display In
		*/
		if ($extra != '' && !is_array($extra)) $extra = ' '.$extra;
			/**********************************************************/

		$class= isset($name['class']) && !empty($name['class']) ? "col-md-12 form-control selectpicker form-control-lg $name[class]" :"col-md-12 form-control form-control-lg selectpicker";

		$multiple = (astpp_count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
				/*
		  ASTPP  3.0  For Search Display In
		*/
				if(empty($extra)){
				/*********************************/
		if(is_array($name) && !isset($name["id"])){
					$form = '<select name="'.$name['name'].'"'." class='col-md-12 form-control form-control-lg selectpicker $class' style='margin-left:5px;' data-live-search='true'>\n";
				}else if(is_array($name) && isset($name["id"])){
					$form = '<select name="'.$name['name'].'" id="'.$name['id'].'"'."class='col-md-12 form-control form-control-lg selectpicker $class' style='margin-left:5px;' data-live-search='true'>\n";
				}else{
					$form = '<select name="'.$name.'"' .$multiple." class='col-md-12 form-control form-control-lg selectpicker $class' style='margin-left:5px;' data-live-search='true'>\n";
				}
 /*
		  ASTPP  3.0  For Search Display In
		*/
				}else{

					$form = '<select name="'.$name['name'].'" id="'.$name['id'].'"'."class='".$extra['class']." selectpicker $class' style='".$extra['style']."' data-live-search='true'>\n";
				 }
		//$form .= '<option value=""> --Select-- </option>';
		foreach ($options as $key => $val)
		{
			$key = (string) $key;

			if (is_array($val) && ! empty($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (astpp_in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string)$optgroup_val."</option>\n";
				}

				$form .= '</optgroup>'."\n";
			} else
			{
				$sel = (astpp_in_array($key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$key.'"'.$sel.'>'.(string)$val."</option>\n";
			}
		}

		$form .= '</select>';

		return $form;
	}
}


if ( ! function_exists('form_dropdown_multiselect'))
{
	function form_dropdown_multiselect($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (astpp_count($selected) === 0)
		{
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}

		if ($extra != '') {
			$extra = ' '.$extra;
		}

		$multiple = (astpp_count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
		$form = '<select name="'.$name.'"'." multiple='multiple' class='selectpicker select field multiselectable col-md-12 form-control form-control-lg' data-hide-disabled='true' data-actions-box='true'>\n";

		foreach ($options as $key => $val)
		{
			$key = (string)$key;

			if (is_array($val) && ! empty($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (astpp_in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string)$optgroup_val."</option>\n";
				}

				$form .= '</optgroup>'."\n";
			} else
			{
				$sel = (astpp_in_array($key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$key.'"'.$sel.'>'.(string)$val."</option>\n";
			}
		}

		$form .= '</select>';

		return $form;
	}
}

// ------------------------------------------------------------------------

/**
 * Checkbox Field
 * @access	public
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */


/**
ASTPP3.0
Add button for checkbox
**/
if ( ! function_exists('form_checkbox'))
{
	function form_checkbox($data = '', $value = '', $checked = FALSE, $extra = '')
	{

		if (isset($data) && $data != '') {
			$name = "'".$data."'";
		} else {
			$name = '';
		}

		/*if(isset($value) && $value != ''){
			$value = "'".$value."'";
		}else{
			$value='0';
		}*/

		$defaults = array('class'=>'onoffswitch-checkbox', 'type' => 'checkbox', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

		if (is_array($data) AND astpp_array_key_exists('checked', $data))
		{
			$checked = $data['checked'];

			if ($checked == FALSE)
			{
				unset($data['checked']);
			} else
			{
				$data['checked'] = 'checked';
			}
		}
		if ($checked == TRUE)
		{
			$defaults['checked'] = 'checked';
		} else
		{
			unset($defaults['checked']);
		}
		$class=NULL;

//echo "<pre>".$value; print_r($extra); exit;
		if(isset($extra[$value]) && !empty($extra) && $extra[$value] == '0'){
		$class='onoffswitch-inner';
		}else{
		$class='onoffswitch-inner';
 		}
/*            if(isset($extra) && $extra != ''){
	      if(isset($extra[0])){
	      if(strtolower($extra[0]) == 'enable' || $value == "0"){
		$class='onoffswitch-inner_enable';
	      }elseif(strtolower($extra[0])=='true' || $value == "0" ){
		$class='onoffswitch-inner_true';
	      }elseif(strtolower($extra[0])=='active' || $value == "0"){
		$class='onoffswitch-inner_active';
	      }else{
		$class='onoffswitch-inner';
	      }
	      }

            /* if(isset($extra[1]) && $extra[1] == 'Disable'){
            $enable='onoffswitch-inner';
            }else{
            $enable='onoffswitch-inner_enable';
            }*/
//            }
		//return "<input "._parse_form_attributes($data, $defaults).$extra." />";
		if ($class == "onoffswitch-inner_true") {
				return '<div style="width: 49%; text-align: -moz-center; padding: 0;">
				<input '._parse_form_attributes($data, $defaults).' type="checkbox" id="switch'.$name.'" name="onoffswitch" class="onoffswitch-checkbox" content="Yes">
				<label class="onoffswitch-label" for="switch'.$name.'"><span class="'.$class.'"></span></label></div>';
		} else {
				return '<div style="width: 41%; text-align: -moz-center; padding: 0;">
				<input  type="checkbox" id="switch'.$name.'" name="onoffswitch"  class="onoffswitch-checkbox" content="Yes">
				<label class="onoffswitch-label" for="switch'.$name.'"><span class="'.$class.'"></span></label></div>';
		}

	}
}
/**********************************************************/
// ------------------------------------------------------------------------

/**
 * Radio Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_radio'))
{
	function form_radio($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'radio';
		return form_checkbox($data, $value, $checked, $extra);
	}
}

// ------------------------------------------------------------------------

/**
 * Submit Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_submit'))
{
	function form_submit($data = '', $value = '', $extra = '')
	{
		$defaults = array('type' => 'submit', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

		return "<input "._parse_form_attributes($data, $defaults).$extra." />";
	}
}

// ------------------------------------------------------------------------

/**
 * Reset Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_reset'))
{
	function form_reset($data = '', $value = '', $extra = '')
	{
		$defaults = array('type' => 'reset', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

		return "<input "._parse_form_attributes($data, $defaults).$extra." />";
	}
}

// ------------------------------------------------------------------------

/**
 * Form Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_button'))
{
	function form_button($data = '', $content = '', $extra = '')
	{
		$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'type' => 'button');

		if (is_array($data) AND isset($data['content']))
		{
			$content = $data['content'];
			unset($data['content']); // content is not an attribute
		}

		return "<button "._parse_form_attributes($data, $defaults).$extra.">".gettext($content)."</button>";
		//return "<button "._parse_form_attributes($data, $defaults).$extra.">".$content."</button>";
	}
}

// ------------------------------------------------------------------------

/**
 * Form Label Tag
 *
 * @access	public
 * @param	string	The text to appear onscreen
 * @param	string	The id the label applies to
 * @param	string	Additional attributes
 * @return	string
 */
if ( ! function_exists('form_label'))
{
	function form_label($label_text = '', $id = '', $attributes = array())
	{

		$label = '<label';

		if ($id != '')
		{
			$label .= " for=\"$id\"";
		}

		if (is_array($attributes) AND astpp_count($attributes) > 0)
		{
			foreach ($attributes as $key => $val)
			{
				$label .= ' '.$key.'="'.$val.'"';
			}
		}
		$label .= ">";
		$label_text =str_replace('#Enterprise#', '<span id = "Enterprise" class="badge badge-warning Enterprise" onclick="myFunction()"> Enterprise</span> ', $label_text );
		$label .= "$label_text</label>";

		return $label;
	}
}

// ------------------------------------------------------------------------
/**
 * Fieldset Tag
 *
 * Used to produce <fieldset><legend>text</legend>.  To close fieldset
 * use form_fieldset_close()
 *
 * @access	public
 * @param	string	The legend text
 * @param	string	Additional attributes
 * @return	string
 */
if ( ! function_exists('form_fieldset'))
{
	function form_fieldset($legend_text = '', $attributes = array())
	{

		$fieldset = "<h3 class='bg-secondary text-light p-3 rounded-top'";

		$fieldset .= _attributes_to_string($attributes, FALSE);

		$fieldset .= ">\n";

		if ($legend_text != '')
		{
			$fieldset .= "$legend_text</h3>\n";
		}

		return $fieldset;
	}
}

// ------------------------------------------------------------------------

/**
 * Fieldset Close Tag
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_fieldset_close'))
{
	function form_fieldset_close($extra = '')
	{
		return "</fieldset>".$extra;
	}
}

// ------------------------------------------------------------------------

/**
 * Form Close Tag
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_close'))
{
	function form_close($extra = '')
	{
		return "</form>".$extra;
	}
}

// ------------------------------------------------------------------------

/**
 * Form Prep
 *
 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_prep'))
{
	function form_prep($str = '', $field_name = '')
	{
		static $prepped_fields = array();

		// if the field name is an array we do this recursively
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = form_prep($val);
			}

			return $str;
		}

		if ($str === '')
		{
			return '';
		}

		// we've already prepped a field with this name
		// @todo need to figure out a way to namespace this so
		// that we know the *exact* field and not just one with
		// the same name
		if (isset($prepped_fields[$field_name]))
		{
			return $str;
		}

		$str = htmlspecialchars((string) $str); // PHP8: null argument

		// In case htmlspecialchars misses these.
		$str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);

		if ($field_name != '')
		{
			$prepped_fields[$field_name] = $field_name;
		}

		return $str;
	}
}

// ------------------------------------------------------------------------

/**
 * Form Value
 *
 * Grabs a value from the POST array for the specified field so you can
 * re-populate an input field or textarea.  If Form Validation
 * is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @return	mixed
 */
if ( ! function_exists('set_value'))
{
	function set_value($field = '', $default = '')
	{
		if (FALSE === ($OBJ = & _get_validation_object()))
		{
			if ( ! isset($_POST[$field]))
			{
				return $default;
			}

			return form_prep($_POST[$field], $field);
		}

		return form_prep($OBJ->set_value($field, $default), $field);
	}
}

// ------------------------------------------------------------------------

/**
 * Set Select
 *
 * Let's you set the selected value of a <select> menu via data in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
if ( ! function_exists('set_select'))
{
	function set_select($field = '', $value = '', $default = FALSE)
	{
		$OBJ = & _get_validation_object();

		if ($OBJ === FALSE)
		{
			if ( ! isset($_POST[$field]))
			{
				if (astpp_count($_POST) === 0 AND $default == TRUE)
				{
					return ' selected="selected"';
				}
				return '';
			}

			$field = $_POST[$field];

			if (is_array($field))
			{
				if ( ! astpp_in_array($value, $field))
				{
					return '';
				}
			} else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' selected="selected"';
		}

		return $OBJ->set_select($field, $value, $default);
	}
}

// ------------------------------------------------------------------------

/**
 * Set Checkbox
 *
 * Let's you set the selected value of a checkbox via the value in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
if ( ! function_exists('set_checkbox'))
{
	function set_checkbox($field = '', $value = '', $default = FALSE)
	{
		$OBJ = & _get_validation_object();

		if ($OBJ === FALSE)
		{
			if ( ! isset($_POST[$field]))
			{
				if (astpp_count($_POST) === 0 AND $default == TRUE)
				{
					return ' checked="checked"';
				}
				return '';
			}

			$field = $_POST[$field];

			if (is_array($field))
			{
				if ( ! astpp_in_array($value, $field))
				{
					return '';
				}
			} else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' checked="checked"';
		}

		return $OBJ->set_checkbox($field, $value, $default);
	}
}

// ------------------------------------------------------------------------

/**
 * Set Radio
 *
 * Let's you set the selected value of a radio field via info in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
if ( ! function_exists('set_radio'))
{
	function set_radio($field = '', $value = '', $default = FALSE)
	{
		$OBJ = & _get_validation_object();

		if ($OBJ === FALSE)
		{
			if ( ! isset($_POST[$field]))
			{
				if (astpp_count($_POST) === 0 AND $default == TRUE)
				{
					return ' checked="checked"';
				}
				return '';
			}

			$field = $_POST[$field];

			if (is_array($field))
			{
				if ( ! astpp_in_array($value, $field))
				{
					return '';
				}
			} else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' checked="checked"';
		}

		return $OBJ->set_radio($field, $value, $default);
	}
}

// ------------------------------------------------------------------------

/**
 * Form Error
 *
 * Returns the error for a specific form field.  This is a helper for the
 * form validation class.
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_error'))
{
	function form_error($field = '', $prefix = '', $suffix = '')
	{
		if (FALSE === ($OBJ = & _get_validation_object()))
		{
			return '';
		}

		return $OBJ->error($field, $prefix, $suffix);
	}
}

// ------------------------------------------------------------------------

/**
 * Validation Error String
 *
 * Returns all the errors associated with a form submission.  This is a helper
 * function for the form validation class.
 *
 * @access	public
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('validation_errors'))
{
	function validation_errors($prefix = '', $suffix = '')
	{
		if (FALSE === ($OBJ = & _get_validation_object()))
		{
			return '';
		}

		return $OBJ->error_string_custom($prefix, $suffix);
	}
}

// ------------------------------------------------------------------------

/**
 * Parse the form attributes
 *
 * Helper function used by some of the form helpers
 *
 * @access	private
 * @param	array
 * @param	array
 * @return	string
 */
if ( ! function_exists('_parse_form_attributes'))
{
	function _parse_form_attributes($attributes, $default)
	{
		if (is_array($attributes))
		{
			foreach ($default as $key => $val)
			{
				if (isset($attributes[$key]))
				{
					$default[$key] = $attributes[$key];
					unset($attributes[$key]);
				}
			}

			if (astpp_count($attributes) > 0)
			{
				$default = astpp_array_merge($default, $attributes);
			}
		}

		$att = '';

		foreach ($default as $key => $val)
		{
			if ($key == 'value')
			{
				$val = form_prep($val, $default['name']);
			}

			$att .= $key.'="'.$val.'" ';
		}

		return $att;
	}
}

// ------------------------------------------------------------------------

/**
 * Attributes To String
 *
 * Helper function used by some of the form helpers
 *
 * @access	private
 * @param	mixed
 * @param	bool
 * @return	string
 */
if ( ! function_exists('_attributes_to_string'))
{
	function _attributes_to_string($attributes, $formtag = FALSE)
	{
		if (is_string($attributes) AND strlen($attributes) > 0)
		{
			if ($formtag == TRUE AND strpos($attributes, 'method=') === FALSE)
			{
				$attributes .= ' method="post"';
			}

			if ($formtag == TRUE AND strpos($attributes, 'accept-charset=') === FALSE)
			{
				$attributes .= ' accept-charset="'.strtolower(config_item('charset')).'"';
			}

		return ' '.$attributes;
		}

		if (is_object($attributes) AND astpp_count($attributes) > 0)
		{
			$attributes = (array)$attributes;
		}

		if (is_array($attributes) AND astpp_count($attributes) > 0)
		{
			$atts = '';

			if ( ! isset($attributes['method']) AND $formtag === TRUE)
			{
				$atts .= ' method="post"';
			}

			if ( ! isset($attributes['accept-charset']) AND $formtag === TRUE)
			{
				$atts .= ' accept-charset="'.strtolower(config_item('charset')).'"';
			}

			foreach ($attributes as $key => $val)
			{
				$atts .= ' '.$key.'="'.$val.'"';
			}

			return $atts;
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Validation Object
 *
 * Determines what the form validation class was instantiated as, fetches
 * the object and returns it.
 *
 * @access	private
 * @return	mixed
 */
if ( ! function_exists('_get_validation_object'))
{
	function &_get_validation_object()
	{
		$CI = & get_instance();

		// We set this as a variable since we're returning by reference.
		$return = FALSE;

		if (FALSE !== ($object = $CI->load->is_loaded('form_validation')))
		{
			if ( ! isset($CI->{$object}) OR ! is_object($CI->{$object}))
			{
				return $return;
			}

			return $CI->{$object};
		}

		return $return;
	}
}


/**start code to here
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


/* End of file form_helper.php */
/* Location: ./system/helpers/form_helper.php */
