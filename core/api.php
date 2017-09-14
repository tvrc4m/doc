<?php

class Api extends Doc {

    const API_TYPE_APP  =   'app';
    const API_TYPE_WEB  =   'web';
    const API_TYPE_PC   =   'pc';

    const JSON_EXT=".json";

    /**
     * 页面输出
     * @param  string $type 类型api
     * @param  string $api  是否输出指定的某个api
     * @return 
     */
    public function export($type=self::API_TYPE_APP,$api=null){

        $dirs=$this->getTypeCate($type);

        $api_list=$api_data=[];

        foreach (glob(DATA.$type.'/*') as $filename) {
            
            if($filename==$type.self::JSON_EXT) continue;

            if(is_dir($filename)){

                $dir=basename($filename);

                foreach (glob($filename.'/*'.self::JSON_EXT) as $secname) {
                    
                    $api_basename=basename($secname,self::JSON_EXT);

                    $api_content=file_get_contents($secname);

                    $api_parse=json_decode($api_content,true);

                    $api_list[$dirs[$dir]][$api_basename]=$api_parse['title'];
                    $api_data[$api_basename]=$api_parse;
                }
            }
        }

        // print_r($api_data);exit;

        if(!empty($api)){

            $api_data=[$api_data[$api]];
        }else{
            
            $commonapi=file_get_contents(DATA.$type.'/'.$type.self::JSON_EXT);

            $common=json_decode($commonapi,true);
        }

        include_once(VIEW.'app/header.html');
        $footer=file_get_contents(VIEW.'app/footer.html');
        
        $content=include_once(VIEW.'app/content.html');

        exit($content);
    }

    /**
     * 获取某类别下的分组
     * @param  string $type 
     * @return array
     */
    public function getTypeCate($type){

        switch ($type) {

            case self::API_TYPE_APP:
            return ['channel'=>'频道','content'=>'内容','login'=>'登陆','user'=>'用户'];
            break;

            case self::API_TYPE_WEB:
            return ['channel'=>'频道','content'=>'内容','login'=>'登陆','user'=>'用户'];
            break;
        }
    }
}