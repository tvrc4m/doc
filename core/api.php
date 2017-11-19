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

                    $api_parse['filename']=$type.'/'.$dir.'/'.basename($secname);

                    $api_list[$dirs[$dir]][$api_basename]=['name'=>$api_parse['title'],'url'=>'/api/app#'.$api_basename];
                    $api_data[$api_basename]=$api_parse;
                }

                foreach (glob($filename.'/example/*'.self::EXAMPLE_EXT) as $emamplename) {
                    
                    $api_basename=basename($emamplename,self::EXAMPLE_EXT);

                    $example_content=file_get_contents($emamplename);
                    
                    $api_data[$api_basename]['example']=$example_content;
                }
                // 通过参数组装事例对象
                foreach ($api_data as $basename=>&$apidata) {
                    
                    if(!$apidata['example'] && $apidata['return']){

                        $example=[];

                        ksort($apidata['return']);
                       // print_r($data['return']); 
                        foreach ($apidata['return'] as $key=>$res) {

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

                        $apidata['example']=json_encode($example_result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

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
        
        $db=new DB();

        $cat_list=[];

        foreach ($api_list as $name=>$api) {
            
            $sql="insert into kf_api_cat(name,create_date) values (?,now())";

            $cat_id=$db->insert($sql,'s',[$name]);

            foreach ($api as $key=>$detail) {
                
                $cat_list[$key]=$cat_id;
            }
        }

        foreach ($api_data as $key=>$api) {

            $remark=$db->escape($api['desc']);
            
            $sql="INSERT INTO kf_api(title,code,type,cat_id,url,version,remark,create_date) values ('{$api['title']}','{$key}',1,'{$cat_list[$key]}','{$api['url']}','{$api['version']}','{$remark}',now())";

            $api_id=$db->insert($sql);

            foreach ($api['params'] as $name=>$param) {

                $must=$param['must']=='是'?1:0;

                $remark=$db->escape($param['desc']);

                $sql="insert into kf_api_params(name,api_id,type,must,remark,create_date) values ('{$name}',{$api_id},'{$param['type']}','{$must}','{$remark}',now())"; 

                $db->insert($sql);   
            }

            foreach ($api['return'] as $name=>$param) {

                $must=$param['must']=='是'?1:0;

                $remark=$db->escape($param['desc']);

                $sql="insert into kf_api_return(name,api_id,type,must,remark,create_date) values ('{$name}',{$api_id},'{$param['type']}','{$must}','{$remark}',now())"; 

                $db->insert($sql);
            }
            
        }
        exit;
        $tab_selected=$type;

        $data=['api_list'=>$api_list,'tab_selected'=>$tab_selected];

        include_once(VIEW.'common/header.html');

        include_once(VIEW.'app/content.html');

        include_once(VIEW.'common/footer.html');

        exit(0);
    }

    public function getApiList1($type=self::API_TYPE_APP){

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
     * 获取接口基本信息
     * @param  string $code 唯一值
     * @return array
     */
    public function getApi($code){

        $db=new DB();
        // 接口基本信息
        $api=$db->get("SELECT * FROM kf_api WHERE code='{$code}' AND stat=1");

        if(empty($api)) return [];
        // 请求参数
        $params=$db->find("SELECT * FROM kf_api_params WHERE stat=1 AND api_id=".$api['id']);        
        // 返回参数
        $return=$db->find("SELECT * FROM kf_api_return WHERE stat=1 AND api_id=".$api['id']);

        $api['params']=$params;

        $api['return']=$return;

        return $api;
    }

    /**
     * 获取接口列表
     * @param  页面方法 $action 
     * @return array
     */
    public function getApiList($action='api'){

        $db=new DB();

        $cat_list=$this->getApiCat();
        $api_list=$db->find("SELECT * FROM kf_api WHERE stat=1");
        $params_list=$db->find("SELECT * FROM kf_api_params WHERE stat=1 ORDER BY name ASC");        
        $return_list=$db->find("SELECT * FROM kf_api_return WHERE stat=1 ORDER BY name ASC");        

        $cat_result=$params_result=$return_result=$api_result=[];

        foreach($cat_list as $cat) $cat_result[$cat['id']]=$cat['name'];
        foreach($params_list as $params) $params_result[$params['api_id']][]=$params;
        foreach($return_list as $return) $return_result[$return['api_id']][]=$return;
        
        foreach ($api_list as $api) {
            // 类别名称
            $cat=$cat_result[$api['cat_id']];
            // 组装请求参数
            $api['params']=$params_result[$api['id']];
            // 组装返回参数
            $api['return']=$return_result[$api['id']];

            $example=$this->getApiExample($api['id']);

            empty($example) && $example=$this->getApiExampleByApi($api);

            $api['example']=$example;

            if($action=='api'){

                $api['side_url']='/api/app#'.$api['code'];
            }elseif($action=='http'){
                $api['side_url']='/api/http#'.$api['code'];
            }
            // 按类别分组
            $api_result[$cat][]=$api;
        }

        return $api_result;
    }

    /**
     * 获取api接口的类别 
     * @return array
     */
    public function getApiCat(){

        $db=new DB();

        return $db->find("SELECT * FROM kf_api_cat WHERE stat=1");
    }

    /**
     * 获取接口的事例代码
     * @param  int $id 接口id
     * @return string
     */
    public function getApiExample($api_id){

        $db=new DB();

        $sql="SELECT code FROM kf_api_example WHERE stat=1 AND api_id=".intval($api_id)." LIMIT 1";

        $result=$db->get($sql);

        return $result['code'];
    }

    /**
     * 通过参数组装事例对象
     * @param  array $apidata 
     * @return string
     */
    public function getApiExampleByApi($apidata){
       
       if($apidata['return']){

            $example=[];
            ksort($apidata['return']);

            foreach ($apidata['return'] as $index=>$res) {

                $desc="<span class='data-type'>[".$res['type']."]</span>".$res['desc'];

                $name=$res['name'];

                if(strpos($name, ".")!==FALSE){

                    list($first,$second,$third,$four)=explode('.', $name);

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
                    $example[$name]=($res['type']=='array' || $res['type']=='object')
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

            return json_encode($example_result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        }

        return '';
    }

    /**
     * 获取某类别下的分组
     * @param  string $type 
     * @return array
     */
    public function getTypeCate($type){

        switch ($type) {

            case self::API_TYPE_APP:
            return ['channel'=>'频道','content'=>'内容','login'=>'登陆','bind'=>'绑定与解绑','user'=>'用户','other'=>'其他','live'=>'直播','message'=>'消息','live_v2'=>'直播一期','video'=>'视频一期','activity'=>'分享原创拉新活动'];
            break;

            case self::API_TYPE_WEB:
            return ['channel'=>'频道','content'=>'内容','login'=>'登陆','user'=>'用户'];
            break;
        }
    }
}