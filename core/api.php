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
                // 通过参数组装事例对象
                foreach ($api_data as $basename=>&$data) {
                    
                    if(!$data['example'] && $data['return']){

                        $example=[];

                        ksort($data['return']);
                       // print_r($data['return']); 
                        foreach ($data['return'] as $key=>$res) {

                            $desc="<span class='data-type'>[".$res['type']."]</span>".$res['desc'];

                            if(strpos($key, ".")!==FALSE){

                                list($first,$second,$third,$four)=explode('.', $key);

                                // 如果只存在二个参数 
                                if(!$third){

                                    if($res['type']=='array'){

                                        $example[$first][$second]=array();
                                    }else{
                                        
                                        if(is_object($example[$first])){
                                            $example[$first]->$second=$desc;                                            
                                        }else{
                                            $example[$first][$second]=$desc;    
                                        }

                                        
                                    }
                                }elseif(!$four){

                                    if(is_object($example[$first])){
                                        if(is_object($example->$first->$second)){
                                            $example->$first->$second->$third=$desc;        
                                        }elseif(is_array($example->$first->$second)){
                                            $example->$first->$second[$third]=$desc;        
                                        }
                                    }elseif(is_array($example[$first])){
                                        if(is_object($example[$first]->$second)){
                                            $example[$first]->$second->$third=$desc;        
                                        }elseif(is_array($example[$first]->$second)){
                                            $example[$first]->$second[$third]=$desc;        
                                        }
                                    }
                                }else{
                                    if(is_object($example[$first])){
                                        if(is_object($example->$first->$second)){
                                            if(is_object($example->$first->$second->$third)){
                                                $example->$first->$second->$third->$four=$desc;        
                                            }elseif(is_array($example->$first->$second->$third)){
                                                $example->$first->$second->$third[$four]=$desc;        
                                            }
                                        }elseif(is_array($example->$first->$second)){
                                            if(is_object($example->$first->$second[$third])){
                                                $example->$first->$second[$third]->$four=$desc;        
                                            }elseif(is_array($example->$first->$second[$third])){
                                                $example->$first->$second[$third][$four]=$desc;        
                                            }
                                        }
                                    }elseif(is_array($example[$first])){
                                        if(is_object($example[$first]->$second)){
                                            if(is_object($example[$first]->$second->$third)){
                                                $example[$first]->$second->$third->$four=$desc;     
                                            }elseif(is_array($example[$first]->$second->$third)){
                                                $example[$first]->$second->$third[$four]=$desc;    
                                            }
                                        }elseif(is_array($example[$first]->$second)){
                                            if(is_object($example[$first]->$second[$third])){
                                                $example[$first]->$second[$third]->$four=$desc;   
                                            }elseif(is_array($example[$first]->$second[$third])){
                                                $example[$first]->$second[$third][$four]=$desc;
                                            }
                                        }
                                    }
                                }
                            }else{
                                $example[$key]=($res['type']=='array' || $res['type']=='object')
                                    ?($res['type']=='array'?array():new stdClass())
                                    :$desc;
                            }
                        }

                        $example_result=[
                            'data'=>isset($example['data'])?$example['data']:$example,
                            'error_code'=>"[<span class='data-type'>int</span>]错误码:0 成功 1失败",
                            'error_msg'=>"[<span class='data-type'>string</span>]错误消息",
                            'api_version'=>"[<span class='data-type'>string</span>]1.0.0"
                        ];

                        $data['example']=json_encode($example_result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

                        // print_r($data);exit;
                    }
                }
            }
        }
        
        if(!empty($api)){

            $api_data=[$api_data[$api]];
        }else{

            $commonapi=file_get_contents(DATA.$type.'/'.$type.self::JSON_EXT);

            $common=json_decode($commonapi,true);
        }

        $tab_selected=$type;

        $data=['api_list'=>$api_list,'tab_selected'=>$tab_selected];

        include_once(VIEW.'common/header.html');
        
        include_once(VIEW.'app/content.html');

        include_once(VIEW.'common/footer.html');

        exit(0);
    }

    public function getApiList($type=self::API_TYPE_APP){

        $dirs=$this->getTypeCate($type);

        $api_list=[];

        foreach (glob(DATA.$type.'/*') as $filename) {
            
            if($filename==$type.self::JSON_EXT) continue;

            if(is_dir($filename)){

                $dir=basename($filename);

                foreach (glob($filename.'/*'.self::JSON_EXT) as $secname) {
                    
                    $api_basename=basename($secname,self::JSON_EXT);

                    $api_content=file_get_contents($secname);

                    $api_parse=json_decode($api_content,true);

                    if($api_parse['url'])
                        $api_list[$dirs[$dir]][$api_basename]=['name'=>$api_parse['title'],'url'=>'/api/http#'.$api_basename];
                    // $api_list[$dirs[$dir]][$api_basename]=['name'=>$api_parse['title'],'url'=>'javascript:void(0);'];
                }
            }
        }

        return $api_list;
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
            return ['channel'=>'频道','content'=>'内容','login'=>'登陆','bind'=>'绑定与解绑','user'=>'用户','other'=>'其他','live'=>'直播','message'=>'消息','live_v2'=>'直播改'];
            break;

            case self::API_TYPE_WEB:
            return ['channel'=>'频道','content'=>'内容','login'=>'登陆','user'=>'用户'];
            break;
        }
    }
}