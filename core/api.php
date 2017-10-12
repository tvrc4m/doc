<?php

class Api extends Doc {

    const API_TYPE_APP  =   'app';
    const API_TYPE_WEB  =   'web';
    const API_TYPE_PC   =   'pc';

    const JSON_EXT=".json";
    const EXAMPLE_EXT='.example';

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

                    $api_list[$dirs[$dir]][$api_basename]=['name'=>$api_parse['title'],'url'=>'/api/app#'.$api_basename];
                    $api_data[$api_basename]=$api_parse;
                }

                foreach (glob($filename.'/example/*'.self::EXAMPLE_EXT) as $emamplename) {
                    
                    $api_basename=basename($emamplename,self::EXAMPLE_EXT);

                    $example_content=file_get_contents($emamplename);
                    
                    $api_data[$api_basename]['example']=$example_content;
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

        $tab_selected=$type;

        include_once(VIEW.'common/header.html');
        
        include_once(VIEW.'app/content.html');

        include_once(VIEW.'common/footer.html');

        exit(0);
    }


    /**
     * 通过指定的key获取josn文件数据
     * @param  string $type 指定api类型
     * @param  string $key 指定api接口的key
     * @return array
     */
    public function getJsonByKey($type,$key){

        foreach (glob(DATA.$type.'/*') as $filename) {
            
            if($filename==$type.self::JSON_EXT) continue;

            if(is_dir($filename)){

                $dir=basename($filename);

                foreach (glob($filename.'/*'.self::JSON_EXT) as $secname) {
                    
                    $api_basename=basename($secname,self::JSON_EXT);

                    if($key==$api_basename){

                        $api_content=file_get_contents($secname);    

                        $api_parse=json_decode($api_content,true);

                        return $api_parse;
                    }
                }
            }
        }

        return [];
    }

    /**
     * 获取某类别下的分组
     * @param  string $type 
     * @return array
     */
    public function getTypeCate($type){

        switch ($type) {

            case self::API_TYPE_APP:
            return ['channel'=>'频道','content'=>'内容','login'=>'登陆','bind'=>'绑定与解绑','user'=>'用户','other'=>'其他','live'=>'直播','message'=>'消息'];
            break;

            case self::API_TYPE_WEB:
            return ['channel'=>'频道','content'=>'内容','login'=>'登陆','user'=>'用户'];
            break;
        }
    }
}