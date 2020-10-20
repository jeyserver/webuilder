<?php
namespace packages\base\Utility;

class Safe {
	/**
	 * Safe compare two float value to avoid the floating-point problem
	 *
	 * @param float $a
	 * @param float $b
	 * @return int positive value if a > b, zero if a = b and a negative value if a < b.
	 * @see https://www.php.net/manual/en/language.types.float.php#language.types.float.comparison
	 */
	public static function floats_cmp(float $a, float $b): int {
		if ($a == 0 or $b == 0) {
			return $a <=> $b;
		} else if (abs(($a - $b) / $b) < PHP_FLOAT_EPSILON) { // PHP_FLOAT_EPSILON available as of PHP 7.2.0.
			return 0;
		} else if ($a - $b > 0) {
			return 1;
		} else {
			return -1;
		}
	}
	static function string($str){
        $str = trim($str);
        $str = str_replace(array('\\', '\'', '"'), "", $str);
        $str = htmlentities($str , ENT_IGNORE|ENT_SUBSTITUTE 	|ENT_DISALLOWED, 'UTF-8');
        return($str);
    }
    static function number($num, $negative =false){
    	if(preg_match($negative ? "/(-?\d+)/" : "/(\d+)/", $num, $matches)){
    		return((int)$matches[1]);
    	}
    }
    static function date($str){
        $str = trim($str);
        return(preg_match('/(\d{4})\/(\d{2})\/(\d{2})/', $str, $matches) ? array('year' => $matches[1],'month' => $matches[2],'day' => $matches[3]) :  '');
    }
    static function is_date($str){
    	$str = trim($str);
    	if(preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})((\s+)(\d{1,2}))?(:(\d{1,2}))?(:(\d{1,2}))?$/', $str, $matches)){
    		$d = array(
    			'Y' => $matches[1],
    			'm' => $matches[2],
    			'd' => $matches[3],
    		);
    		if(isset($matches[6]) and $matches[6]>=0 and $matches[6]< 24){
    			$d['h'] = $matches[6];
    		}
    		if(isset($matches[8]) and $matches[8]>=0 and $matches[8]< 60){
    			$d['i'] = $matches[8];
    		}
    		if(isset($matches[10]) and $matches[10]>=0 and $matches[8]< 60){
    			$d['s'] = $matches[10];
    		}
    		return $d;
    	}else{
    		return false;
    	}
    }
    static function is_email($address){
        return preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $address);
    }
    public static function is_cellphone_ir(string $cellphone): bool {
		$length = strlen($cellphone);
		if (($length == 10 and substr($cellphone, 0, 1) == '9') or // 9131101234
			($length == 11 and substr($cellphone, 0, 2) == '09') or // 09131101234
			($length == 12 and substr($cellphone, 0, 3) == '989') or // 989131101234
			($length == 13 and substr($cellphone, 0, 4) == '9809') or // 9809131101234
			($length == 13 and substr($cellphone, 0, 4) == '+989') or // +989131101234
			($length == 14 and substr($cellphone, 0, 5) == '98989')) // 98989131101234
		{
			$sub4 = '';
			switch ($length) {
				case(10): // 913
					$sub4 = '0' . substr($cellphone, 0, 3);
					break;
				case(11): // 0913
					$sub4 = substr($cellphone, 0, 4);
					break;
				case(12): // 98913
					$sub4 = '0' . substr($cellphone, 2, 3);
					break;
				case(13): // 9809 || +98913
					if (substr($cellphone, 0, 4) == '9809') {
						$sub4 = substr($cellphone, 2, 4);
					} else if (substr($cellphone, 0, 4) == '+989') {
						$sub4 = '0' . substr($cellphone, 3, 3);
					}
					break;
				case(14): // 9898913
					$sub4 = '0' . substr($cellphone, 4, 3);
					break;
			}
			switch ($sub4) {
				case('0910'):case('0911'):case('0912'):case('0913'):case('0914'):case('0915'):case('0916'):case('0917'):case('0918'):case('0919'):case('0990'):case('0991'):case('0992'): // TCI
				case('0930'):case('0933'):case('0935'):case('0936'):case('0937'):case('0938'):case('0939'): // IranCell
				case('0901'):case('0902'):case('0903'):case('0905'): // IranCell - ISim
				case('0920'):case('0921'):case('0922'): // RighTel
				case('0931'): // Spadan
				case('0932'): // Taliya
				case('0934'): // TKC
				case('0998'): // ShuttleMobile
				case('0999'): // Private Sector: ApTel, Azartel, LOTUSTEL, SamanTel
					return true;
				default:
					return false;
			}
		}
		return false;
	}
    static function cellphone_ir(string $cellphone) {
		$length = strlen($cellphone);
		if (($length == 10 and substr($cellphone, 0, 1) == '9') or // 9131101234
			($length == 11 and substr($cellphone, 0, 2) == '09') or // 09131101234
			($length == 12 and substr($cellphone, 0, 3) == '989') or // 989131101234
			($length == 13 and substr($cellphone, 0, 4) == '9809') or // 9809131101234
			($length == 13 and substr($cellphone, 0, 4) == '+989') or // +989131101234
			($length == 14 and substr($cellphone, 0, 5) == '98989')) // 98989131101234
		{
			switch ($length) { // should return somthing like this: 989131101234
				case(10): // 913
					return '98' . $cellphone;
				case(11): // 0913
					return '98' . substr($cellphone, 1);
				case(12): // 98913
					return $cellphone;
				case(13): // 980913 || +98913
					if (substr($cellphone, 0, 4) == '9809') {
						return '98' . substr($cellphone, 3);
					} else if (substr($cellphone, 0, 4) == '+989') {
						return substr($cellphone, 1);
					}
				case(14):
					return substr($cellphone, 2);
				break;
			}
		}
		return false;
	}
    static function bool($value){
        return ($value == 'true' or $value == 1);
    }
	static function is_ip4($ip){
		$parts = explode('.',$ip);
		if(count($parts) != 4){
			return false;
		}
		foreach($parts as $key => $part){
			if($key == 0){
				if($part <= 0 or $part > 255){
					return false;
				}
			}elseif($part < 0 or $part > 255){
				return false;
			}
		}
		return true;
	}
	public static function htmlentities (string $value, ?array $replaces = null): string {
		if (!$replaces) {
			$replaces = array(
				'"' => "&quot;",
				"'" => "&apos;",
				"<" => "&lt;",
				">" => "&gt;",
			);
		}
		return str_replace(array_keys($replaces), array_values($replaces), $value);
	}
}
