<?php
/**
 * Enter description here...
 *
 */
class Util_AuthorCrypt{

    public static function encrypt($string, $key) {
        return self::authorCode($string, "ENCODE", $key);
    }

    public static function decrypt($string, $key) {
        return self::authorCode($string, "DECODE", $key);
    }

    private static function authorCode($string, $operation, $key) {
        $test = array();

        $key = md5($key);
        $key_length = strlen($key);
        $string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
        $test['string'] = $string;
        $string_length = strlen($string);
        $test['string_length'] = $string_length;

        $rndkey = $box = array();
        $result = '';
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // $test['box'] = $box;
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        $test['result'] = $result;
        print_r($test);
        if($operation == 'DECODE') {
            if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
                return substr($result, 8);
            } else {
                return '';
            }
        } else {
            return str_replace('=', '', base64_encode($result));
        }
    }
}

$test['str'] = 'pierre';
$test['cookie'] = Util_AuthorCrypt::encrypt($test['str'], md5($_SERVER['HTTP_USER_AGENT']));
$test['answer'] = Util_AuthorCrypt::decrypt($test['cookie'], md5($_SERVER['HTTP_USER_AGENT']));


print_r($test);


