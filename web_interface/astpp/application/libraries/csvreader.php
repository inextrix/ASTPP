<?php

if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
/**
 * CSVReader Class
 *
 * $Id: csvreader.php 147 2007-07-09 23:12:45Z Pierre-Jean $
 *
 * Allows to retrieve a CSV file content as a two dimensional array.
 * The first text line shall contains the column names.
 *
 * @author Pierre-Jean Turpeau
 * @link http://www.codeigniter.com/wiki/CSVReader
 *      
 */
#[\AllowDynamicProperties]
class CSVReader {
	var $fields;
	/**
	 * columns names retrieved after parsing
	 */
	var $separator = ',';
	/**
	 * separator used to explode each line
	 */
	
	/**
	 * Parse a text containing CSV formatted data.
	 *
	 * @access public
	 * @param
	 *        	string
	 * @return array
	 */
	function parse_text($p_Text) {
		$lines = explode ( "\n", $p_Text );
		return $this->parse_lines ( $lines );
	}
	
	/**
	 * Parse a file containing CSV formatted data.
	 *
	 * @access public
	 * @param
	 *        	string
	 * @return array
	 */
	function parse_file($p_Filepath, $config_variable, $check_header_flag = false) {
		$lines = file ( $p_Filepath );
		// Giving line numbers
		for($i = 0; $i < astpp_sizeof ( $lines ); $i ++) {
			
			if (trim ( $lines [$i] ) != "") {
				$columnname = explode ( $this->separator, $lines [$i] );
				for($i = 0; $i < astpp_sizeof ( $columnname ); $i ++) {
					$columnname [$i] = $columnname [$i];
				}
				break;
			}
		}
		return $this->parse_lines ( $lines, $config_variable, $check_header_flag );
	}
	/**
	 * Parse an array of text lines containing CSV formatted data.
	 *
	 * @access public
	 * @param
	 *        	array
	 * @return array
	 */
	function parse_lines($p_CSVLines, $config_variable, $check_header_flag = false) {
		// echo "<pre>";
		$t = 0;
		$content = array ();
		$custom_array = array ();
		$i = 0;
		$flag_data = false;
		$data_arr [0] = $config_variable;
		$field_name_arr = astpp_array_keys ( $config_variable );
		foreach ( $p_CSVLines as $line_num => $line ) {
			$line = trim ( $line );
			if (! empty ( $line )) {
				// skip empty lines
				$elements = explode ( $this->separator, $line );
				
				if (astpp_array_filter ( $elements, 'trim' )) {
					$custom_array [] = $elements;
				}
				$i ++;
			}
		}
		
		if ($check_header_flag == 'on') {
			unset ( $custom_array [0] );
		}
		foreach ( $custom_array as $data ) {
			$j = 0;
			foreach ( $data as $key => $value ) {
				$value = str_replace ( '"', '', $value );
				$value = str_replace ( "'", '', $value );
				if (isset ( $field_name_arr [$j] )) {
					$field_key_value = $config_variable [$field_name_arr [$j]];
					$value = strip_slashes ( trim ( $value ) );
					$value = preg_replace ( '#<script.*</script>#is', '', $value );
					if (isset ( $field_key_value ) && ! empty ( $field_key_value ))
						$content [$field_key_value] = strip_tags ( filter_var ( $value, FILTER_SANITIZE_STRING ) );
					$j ++;
				}
			}
			$data_arr [] = $content;
		}
		return $data_arr;
	}
}
?>
