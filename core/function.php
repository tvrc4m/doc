<?php

function require_model($class){

    $filepath=MODEL.$class.'.model.php';

    if(!file_exists($filepath)) exit('文件不存在:'.$filepath);

    include_once $filepath;

    $classname=ucfirst($class).'Model';

    return new $classname();
}

function run($url,$type,$params){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    // curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,strtoupper($type));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    
    if(($result = curl_exec($ch))===FALSE){

        return curl_error($ch);
    }
    
    curl_close($ch);

    return $result;
}

function getEnvList(){
    return [
        'test'=>'http://test.vrcdkj.cn/',
        'dev'=>'http://dev.vrcdkj.cn/',
        'staging'=>'http://staging.vrcdkj.cn/',
        // 'live'=>'https://www.kanfanews.com/',
        'live'=>'https://www.vrcdkj.cn/',
    ];
}

function go($url){
    header("Location:{$url}");
    exit;
}

function rand_str($length = 4) { 
    // 密码字符集，可任意添加你需要的字符 
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; 
    $password =''; 
    for ( $i = 0; $i < $length; $i++ ) { 
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ]; 
    } 
    return $password; 
} 