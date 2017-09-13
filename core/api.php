<?php

class Api extends Doc {

    /**
     * 页面输出
     * @param  string $jsonfile json文件路径
     * @param  string $api  是否输出指定的某个api
     * @return 
     */
    public function export($jsonfile,$api=null){

        $json=file_get_contents(DATA.$jsonfile);

        $params=json_decode($json,true);
        // print_r($params);exit;
        if(!empty($api)){

            $params=[$params[$api]];
        }

        $header=file_get_contents(VIEW.'app/header.html');
        $footer=file_get_contents(VIEW.'app/footer.html');
        
        $content=include_once(VIEW.'app/content.html');

        exit($content);
    }
}