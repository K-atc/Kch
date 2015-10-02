<?php
require_once dirname(__FILE__) .'/lib/Kch.php';
// use Kch;
require_once dirname(__FILE__) .'/lib/WAF.php';
// use WAF;

// API codes
const API_VARIDATION_ERROR = -1;
const API_PARAM_ERROR      = -2;
const LOAD_THREAD          = 1;
const POST_CONTRIBUTION    = 2;
const DELETE_CONTRIBUTION  = 3; // future 
const SHOW_THREADS         = 4; // future

date_default_timezone_set('Asia/Tokyo');
ini_set('default_charset', 'UTF-8');

// ----------------
// ユーザー入力のチェック
// ----------------
$api = 0;
// 本当はPOSTにすべきなんだけど、説明上の都合でGET
if(array_key_exists('load_thread', $_GET)){
    $api = LOAD_THREAD;
    if(!isset($_GET['thread'])){
        $api = API_PARAM_ERROR;
    }
    else{
        $thread = WAF::filter($_GET['thread'], WAF_FILTER_ALPHABET);
        if(!$thread){
            $api = API_VARIDATION_ERROR;
        }
    }
}
else if(array_key_exists('post_contribution', $_GET)){
    $api = POST_CONTRIBUTION;
    // --- 本来不要 ---
    if(!isset($_GET['thread'])){
        $api = API_PARAM_ERROR;
    }
    else{
        $thread = WAF::filter($_GET['thread'], WAF_FILTER_ALPHABET);
        if(!$thread){
            $api = API_VARIDATION_ERROR;
        }
    }  
    // --------------
    if(!isset($_GET['user_name']) || !isset($_GET['screen_name']) || !isset($_GET['content'])){
        $api = API_PARAM_ERROR;
    }
    else{
        $contribution = (object) array();
        $contribution->time_stamp = Date('Y-m-d H:m:s');
        if(isset($_GET['trust'])){ // (説明用の実装)
            // No Check 
            $contribution->user_name = $_GET['user_name'];
            $contribution->screen_name = $_GET['screen_name'];
            $contribution->content = $_GET['content'];
        }
        else{
            $contribution->user_name = WAF::filter($_GET['user_name'], WAF_FILTER_ALPHABET);
            $contribution->screen_name = WAF::filter($_GET['screen_name'], WAF_FILTER_TEXT);
            $contribution->content = WAF::filter($_GET['content'], WAF_FILTER_TEXT_CONTENT);
        }
    }
}

// -------
// APIの実行
// -------
switch ($api){
    case LOAD_THREAD:
        $json = Kch::load($thread);
        if($json === Null){
            Kch::throw_404();
            $res = array("status" => "ERROR", "message" => "thread not found");
        }
        else{
            $res = array("status" => "OK", "result" => $json);
        }
        break;
    case POST_CONTRIBUTION:
        $bool = Kch::save($thread, $contribution);
        if($bool === false){
            $res = array("status" => "ERROR", "message" => "post error");
        }
        // ここからbreakまで説明上の都合により普通じゃない実装
        else{
            $json = Kch::load($thread);
            if($json === Null){
                Kch::throw_404();
                $res = array("status" => "ERROR", "message" => "thread not found");
            }
            else{
                $res = array("status" => "OK", "result" => $json);
            }            
        }
        break;
    case API_PARAM_ERROR:
        Kch::throw_404();
        $res = array("status" => "ERROR", "message" => "parameter error");
        break;
    case API_VALIDATION_ERROR:
        Kch::throw_404();
        $res = array("status" => "ERROR", "message" => "validation error");
        break;        
    default:
        Kch::throw_404();
        $res = array("status" => "ERROR", "message" => "unsupported API");
}

Kch::send($res);
