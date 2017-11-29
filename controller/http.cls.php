<?php

class HttpController extends Api {

    public function index($params){

        $api_list=$this->getApiList('http');

        $m_cat=require_model('cat');

        $cats=$m_cat->getCatsByType(self::CAT_TYPE_HTTP);

        $this->display('http/index.html',['api_list'=>$api_list,'cats'=>$cats,'title'=>'发起请求','tab_selected'=>'http']);
    }

    public function get($params){

        $key=$params['key'];

        $api=$this->getApi($key);
        
        exit(json_encode($api));
    }

    public function my($params){

        $m_cat=require_model('cat');
        $m_http=require_model('http');

        $user_http=$m_http->getUserAndPublicHttp($this->user_id);

        $cats=$m_cat->getCatsByType(self::CAT_TYPE_HTTP);

        $result=[];

        foreach ($cats as $cat) {
            
            foreach ($user_http as $http) {
                
                if($http['cat_id']==$cat['id']){

                    $http['side_url']="/http/my#".$http['id'];
                    $http['code']=$http['id'];

                    if(!isset($current)) $current=$http['id'];

                    $result[$cat['name']][]=$http; 
                }
            }
        }

        $this->display('http/my.html',['api_list'=>$result,'current'=>$current,'title'=>'我发起的请求','tab_selected'=>'my']);
    }

    public function history($params){

        $use_http_id=$params['id'];

        $m_http=require_model('http');

        $user_http=$m_http->getUserHttpDetail($use_http_id);

        if(!empty($user_http)){

            $api_params=json_decode($user_http['api_params'],true);

            $m_api=require_model('api');

            $api=$m_api->getApiParams($user_http['api_id']);

            foreach ($api['params'] as &$param) {
                
                foreach ($api_params as $k=>$v) {
                    
                    if($param['name']==$k){

                        $param['value']=$v;
                    }
                }
            }

            $user_http['api']=$api;
        }

        exit(json_encode($user_http));
    }

    public function del($params){

        $user_http_id=$params['id'];

        $m_http=require_model('http');

        $m_http->delUserHttp($user_http_id,$this->user_id);

        exit(json_encode(['errno'=>0]));
    }

    /**
     * 执行
     * @return 
     */
    public function run($params){

        $env=$params['env'];

        empty($env) && $env='dev';

        $url=$params['url'];

        unset($params['url']);
        unset($params['env']);

        $result=run($env,$url,$params);

        echo $result;
        
        exit;
    }

    public function save($params){

        $api_id=$params['api_id'];
        parse_str($params['api_params'],$api_params);
        $api_return=$params['api_return'];
        $title=$params['title'];
        $is_public=$params['is_public'];
        $cat_id=$params['cat_id'];
        $user_http_id=$params['user_http_id'];

        $m_http=require_model("http");

        if($user_http_id){

            $m_http->updateUserHttpParamsAndReturn($user_http_id,$this->user_id,json_encode($api_params),$api_return);
        }else{

            if(empty($api_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未关联接口id']));
            if(empty($title)) exit(json_encode(['errno'=>-1,'errmsg'=>'未设置标题']));
            if(empty($cat_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'类别不能为空']));

            $m_http->addUserHttp($this->user_id,$title,$cat_id,$api_id,json_encode($api_params),$api_return,$is_public);
        }

        exit(json_encode(['errno'=>0]));
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/cat/index?type=4','click'=>'redirectPage(this)'],
        ];
    }
}