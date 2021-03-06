<?php

class MyController extends BaseAuth{
    /**
     * 选中我的navbar
     * @var boolean
     */
    protected $bar_my=true;

    public function index($params){

        $user_http=$this->m_http->getUserAndPublicHttp($this->user_id);
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

        $this->display('http/my.html',['api_list'=>$result,'test_env'=>$test_env,'current'=>$current,'title'=>'我发起的请求','tab_selected'=>'my']);
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

            $api=$this->m_api->getApiParams($user_http['api_id']);

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

        $this->ok();
    }

    /**
     * 获取http关联的cat
     * @return array
     */
    private function _get_http_cat(){

        return t('cat')->find(['app_id'=>$this->app_id,'type'=>self::CAT_TYPE_HTTP,'stat'=>1]);
    }
}