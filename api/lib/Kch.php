<?php
class Kch {

    // 投稿形式 $contribution:Object
    // time_stamp:  タイムスタンプ (Y-m-d H:m:s)
    // user_name:   ユーザー名
    // screen_name: 表示名
    // content:     投稿内容（発言内容）

    // note: プレフィックスを変更できると別のアプリケーションから使うことができるようになる
    //       つまりこのlibのソースを一々コピーしなくても良くなる
    public static $path_prefix = "./data/threads/";

    // NOTE: 実際はDBへ格納する操作を行わないので一時的に投稿を保持する場所が必要
    public static $tmp_post = Null;

    public static function je($json){
        return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public static function throw_404(){
        header("HTTP/1.0 404 Not Found");
    }

    public static function send($json){
        header("Content-Type: application/json; charset=utf-8");
        echo self::je($json);
    }

    public static function load($thread){
        // todo: PDOを使って本当にデータベースにアクセスする
        $f = file_get_contents(self::$path_prefix . $thread . '.json');
        if(!$f){
            return Null;
        }
        $j = json_decode($f);
        if(self::$tmp_post){ // 説明用の実装
            $j[] = self::$tmp_post;
        }
        return $j;
    }

    // 実行の成功・失敗を論理値で返す
    // note: （省略を認める）引数が多くなりそうなときはオブジェクトを利用する
    public static function save($thread, $contribution){
        // todo: スレッドに投稿を保存する機能
        self::$tmp_post = $contribution;
        return true;
    }
}