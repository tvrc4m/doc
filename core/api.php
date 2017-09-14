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

        $api_list=[];

        foreach ($params as $key=>$param) {
            
            if($key!='common' && $key!='desc') $api_list[$key]=$param['title'];
        }
        
        // print_r($params);exit;
        if(!empty($api)){

            $params=[$params[$api]];
        }

        include_once(VIEW.'app/header.html');
        $footer=file_get_contents(VIEW.'app/footer.html');
        
        $content=include_once(VIEW.'app/content.html');

        exit($content);
    }
}