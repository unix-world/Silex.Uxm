<?php
// Utils Library
// author: Radu Ovidiu I.
// 2015-02-21 r.150416
// License: BSD

namespace UXM;


final class Utils {

	// ::

	// this is a safer replacement for htmlspecialchars() in the HTML5 context
	public static function escape_html($y_string) {
		//--
		return htmlspecialchars($y_string,  ENT_HTML5 | ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
		//--
	} //END FUNCTION


	// escape PHP Variables to be safe used in Javascript and Unicode context because will escape unicode sequences
	public static function escape_js($str) {
		//-- encode as json
		$encoded = @json_encode((string)$str, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); // encode the string includding unicode chars, with all possible: <tag>, ' " &
		//-- the above will provide a json encoded string as: "mystring" ; we get just what's between double quotes as: mystring
		$between_quotes = substr(trim($encoded), 1, -1);
		//--
		return $between_quotes;
		//--
	} //END FUNCTION


	// safe encode unicode PHP to JSON (this is for JSON pages where is no need to escape the unicode sequences, to save memory and footprint)
	public static function json_encode($data) {
		//--
		return @json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); // encode the string excludding unicode chars, with all possible: <tag>, ' " &
		//--
	} //END FUNCTION


	// safe decode JSON to PHP (take care of string context)
	public static function json_decode($y_json, $y_ret_array=false, $y_depth=512) {
		//--
		return @json_decode((string)''.$y_json, $y_ret_array, $y_depth, 0);
		//--
	} //END FUNCTION


	// safe format number as integer with option to be signed or not (to use with SQL by example)
	public static function format_number_int($y_number, $y_signed='') {
		//--
		if((string)$y_signed == '+') { // unsigned integer
			if($y_number < 0) {
				$y_number = 0; // it must be zero if negative for the all logic in this framework
			} //end if
		} //end if
		//--
		return (int) (0 + $y_number);
		//--
	} //END FUNCTION


	// Safe format number to decimal (this is to be used instead of number_format() which throws a warning if passed a string since PHP 5.3)
	public static function format_number_dec($y_number, $y_decimals=0, $y_sep_decimals='.', $y_sep_thousands='') {
		//--
		return number_format((0+$y_number), self::format_number_int($y_decimals,'+'), (string)$y_sep_decimals, (string)$y_sep_thousands);
		//--
	} //END FUNCTION


} //END CLASS


//end of php code
?>