<?php

const WAF_FILTER_TEXT          = 0;
const WAF_FILTER_TEXT_CONTENT  = 1;
const WAF_FILTER_ALPHABET      = 10;
const WAF_FILTER_ASCII_CHARS   = 11;
const WAF_FILTER_UTF8_CHARS    = 100;

mb_regex_encoding("UTF-8");

class WAF{

    // Original Author: Ogaki Yasuo 
    // (http://blog.ohgaki.net/input-validation-disables-most-injection-attacks)
    // Default relatively safe validation function for all kinds of injections.
    // Single line alpha numeric and UTF-8 is allowed.
    // To allow more chars, use $regex_opt.
    private static function validation_default($val, $min_len = 0, $max_len=60, $regex_opt='') {
        if (!is_string($val)) {
            // validation_exit('Invalid type', gettype($val)); 
            return "";       
        }
        if (strlen($val) < $min_len || strlen($val) > $max_len) {
            validation_exit('Too long value', strlen($val));
            return "";
        }
        // Only UTF-8 is supported.
        // WARNING: This code assumes UTF-8 only script.
        if (ini_get('default_charset') != 'UTF-8') {
            // validation_exit('Only UTF-8 is supported', $val);
            return "";
        }
        if (!mb_check_encoding($val, 'UTF-8')) {
            // validation_exit('Invalid encoding', $val);
            return "";
        }
        // Allow only alpha numeric and UTF-8.
        // UTF-8 encoding:
        //   0xxxxxxx
        //   110yyyyx + 10xxxxxx
        //   1110yyyy + 10yxxxxx + 10xxxxxx
        //   11110yyy + 10yyxxxx + 10xxxxxx + 10xxxxxx
        // Since validity of UTF-8 encoding is checked, simply allow \x80-\xFF.
        if (!mb_ereg('\A[0-9A-Za-z\x80-\xFF'.$regex_opt.']*\z', $val)) {
            // validation_exit('Invalid char', $val);
        }
        return $val;
    }

    private static function filter_text($unsafe){
        return self::validation_default($unsafe, 0, 1024, '\s');
    }

    private static function filter_text_content($unsafe){
        return strip_tags($unsafe);
    }

    private static function filter_alphabet($unsafe){
        if(!preg_match('/([a-zA-Z\d_]+)/', $unsafe, $matches)){
            return "";
        }
        return ($matches[1] === $unsafe) ? $unsafe : "";
    }

    private static function filter_ascii_chars($unsafe){
        for($i = 0, $len = count($unsafe); $i < $len; $i++){
            $u = ord($unsafe[$i]);
            if($u < 32 || 126 < $u){ // ' ' ~ '~'
                return "";
            }
        }
        return $unsafe;        
    }

    public static function filter($unsafe, $FILTER_TYPE){

        switch($FILTER_TYPE){
            case WAF_FILTER_TEXT:
                return self::filter_text($unsafe);
            case WAF_FILTER_TEXT_CONTENT:
                return self::filter_text_content($unsafe);
            case WAF_FILTER_ALPHABET:
                return self::filter_alphabet($unsafe);
            case WAF_FILTER_ASCII_CHARS:
                return self::filter_ascii_chars($unsafe);
            case WAF_FILTER_UTF8_CHARS:
                // todo: UTF-8の攻撃性のないテキストかどうかでフィルタリングするやつ
                // return 
            default:
                return "";
        }
        return "";
    }
}