<?php

class MyController extends BaseAuth{

    public function index($params){

        $m_http=require_model('http');

        $user_http=$m_http->getUserAndPublicHttp($this->user_id);

        $cats=$this->_get_http_cat();

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

    /**
     * 我的请求历史数据
     * @param  array $params 
     * @return 
     */
    public function history($params){

        $user_http_id=$params['id'];

        $user_http=t('user_http')->get(['stat'=>1,'id'=>$user_http_id]);

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

    /**
     * 删除我的请求记录
     * @param  array $params 
     * @return 
     */
    public function del($params){

        $user_http_id=$params['id'];

        t('user_http')->update(['stat'=>0],['id'=>$user_http_id,'user_id'=>$this->user_id]);

        exit(json_encode(['errno'=>0]));
    }
    
    /**
     * 获取http关联的cat
     * @return array
     */
    private function _get_http_cat(){

        return t('cat')->find(['type'=>self::CAT_TYPE_HTTP,'stat'=>1]);
    }
}