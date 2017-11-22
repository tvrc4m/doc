<?php

class Api extends Doc {

    const CAT_TYPE_API=1;
    const CAT_TYPE_DOC=2;

    public function __construct(){

        parent::__construct();
    }

    /**
     * 获取通用API说明文档
     * @return array
     */
    public function getCommonApi(){

        $commonapi=file_get_contents(ROOT.'/static/json/api.json');

        return json_decode($commonapi,true);
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

    public function getDocList(){

        $db=new DB();

        $cat_list=$this->getCatByType(self::CAT_TYPE_DOC);

        $doc_list=$db->find("SELECT * FROM kf_doc WHERE stat=1");

        $cat_result=$doc_result=[];

        foreach($cat_list as $cat) $cat_result[$cat['id']]=$cat['name'];
        
        foreach ($doc_list as $doc) {
            // 类别名称
            $cat=$cat_result[$doc['cat_id']];

            $doc['side_url']='/doc/detail/'.$doc['id'].'#'.$doc['id'];

            $doc['code']=$doc['id'];
            // 按类别分组
            $doc_result[$cat][]=$doc;
        }

        return $doc_result;
    }

    /**
     * 获取api接口的类别 
     * @return array
     */
    public function getApiCat(){

        $db=new DB();

        return $db->find("SELECT * FROM kf_cat WHERE type=1 AND stat=1");
    }

    /**
     * 获取app版本号
     * @return array
     */
    public function getAppVersion(){

        $db=new DB();

        return $db->find("SELECT * FROM kf_app_version WHERE stat=1");
    }

    /**
     * 通过type获取指定的类别
     * @param  int $type 
     * @return array
     */
    public function getCatByType($type){

        $db=new DB();

        return $db->find("SELECT * FROM kf_cat WHERE type='{$type}' AND stat=1");
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

                $desc="<span class='data-type'>[".$res['type']."]</span>".$res['remark'];

                $name=$res['name'];

                if(strpos($name, ".")!==FALSE){

                    list($first,$second,$third,$four)=explode('.', $name);

                    // 如果只存在二个参数 
                    if(!$third){

                        if(is_array($example[$first])){

                            if($res['type']=='array'){

                                $example[$first][0][$second]=array();    
                            }elseif($res['type']=='object'){
                                $example[$first][0][$second]=new stdClass();
                            }else{
                                $example[$first][0][$second]=$desc;
                            }
                        }else{
                            if($res['type']=='array'){

                                $example[$first]->$second=array();    
                            }elseif($res['type']=='object'){
                                $example[$first]->$second=new stdClass();
                            }else{
                                $example[$first]->$second=$desc;
                            }  
                        }
                    }elseif(!$four){

                        if(is_object($example[$first])){
                            if(is_object($example->$first->$second)){
                                $example->$first->$second->$third=$desc;        
                            }elseif(is_array($example->$first->$second)){
                                $example->$first->$second[0][$third]=$desc;        
                            }
                        }elseif(is_array($example[$first])){
                            if(is_object($example[$first]->$second)){
                                $example[$first][0]->$second->$third=$desc;        
                            }elseif(is_array($example[$first]->$second)){
                                $example[$first][0]->$second[0][$third]=$desc;        
                            }
                        }
                    }else{
                        if(is_object($example[$first])){
                            if(is_object($example->$first->$second)){
                                if(is_object($example->$first->$second->$third)){
                                    $example->$first->$second->$third->$four=$desc;        
                                }elseif(is_array($example->$first->$second->$third)){
                                    $example->$first->$second->$third[0][$four]=$desc;        
                                }
                            }elseif(is_array($example->$first->$second)){
                                if(is_object($example->$first->$second[$third])){
                                    $example->$first->$second[0][$third]->$four=$desc;        
                                }elseif(is_array($example->$first->$second[$third])){
                                    $example->$first->$second[0][$third][0][$four]=$desc;        
                                }
                            }
                        }elseif(is_array($example[$first])){
                            if(is_object($example[$first]->$second)){
                                if(is_object($example[$first]->$second->$third)){
                                    $example[$first][0]->$second->$third->$four=$desc;     
                                }elseif(is_array($example[$first]->$second->$third)){
                                    $example[$first][0]->$second->$third[$four]=$desc;    
                                }
                            }elseif(is_array($example[$first]->$second)){
                                if(is_object($example[$first]->$second[$third])){
                                    $example[$first][0]->$second[0][$third]->$four=$desc;   
                                }elseif(is_array($example[$first]->$second[$third])){
                                    $example[$first][0]->$second[0][$third][0][$four]=$desc;
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
            if($apidata['code']=='index_getlawlist'){

                // print_r($example);exit;
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
}