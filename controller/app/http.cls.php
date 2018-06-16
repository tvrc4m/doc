<?php

class HttpController extends BaseAuth {

    /**
     * 选中bar
     * @var boolean
     */
    protected $bar_http=true;

    public function index($params){

        $api_list=$this->getApiList();

        if(!empty($api_list)) foreach ($api_list as $api) $current=$api[0]['id'];
        
        $cats=$this->m_cat->getTypeCat($this->app_id,self::CAT_TYPE_HTTP);

        $this->display('http/index.html',['api_list'=>$api_list,'test_env'=>$test_env,'current'=>$current,'cats'=>$cats,'title'=>'发起请求','tab_selected'=>'http']);
    }

    public function get($params){

        $api_id=$params['id'];

        $m_api=require_model('api');

        $api=$m_api->getApiParams($api_id);

        $this->ok(['api'=>$api]);
    }

    /**
     * 执行
     * @return 
     */
    public function run($params){

        $env=$params['env'];

        empty($env) && $env='dev';

        $api_id=$params['api_id'];

        unset($params['api_id']);
        unset($params['env']);

        $api=t('api')->get(['id'=>$api_id,'app_id'=>$this->app_id,'stat'=>1]);

        $domain=$this->m_http->getTestEnvUrl($this->app_id,$env);

        $result=run($domain.'/'.$api['url'],$api['type'],$params);

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

        if($user_http_id){

            t('user_http')->update(['api_params'=>json_encode($api_params),'api_return'=>$api_return],['id'=>$user_http_id,'user_id'=>$this->user_id]);
        }else{

            if(empty($api_id)) $this->error('未关联接口id');
            if(empty($title)) $this->error('未设置标题');
            if(empty($cat_id)) $this->error('类别不能为空');

            t('user_http')->insert(['user_id'=>$this->user_id,'app_id'=>$this->app_id,'title'=>$title,'cat_id'=>$cat_id,'api_id'=>$api_id,'api_params'=>json_encode($api_params),'api_return'=>$api_return,'is_public'=>$is_public,'stat'=>1]);
        }

        $this->ok(['message'=>'保存成功']);
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/app/cat/'.self::CAT_TYPE_HTTP,'click'=>'redirectPage(this)'],
        ];
    }

    /**
     * 获取接口列表
     * @param  页面方法 $action 
     * @return array
     */
    public function getApiList($action='http'){
        // 获取API类别
        $cat_list=$this->m_cat->getTypeCat($this->app_id,self::CAT_TYPE_API);
        $api_list=t('api')->find(['app_id'=>$this->app_id,'stat'=>1]);
        $params_list=t('api_params')->find(['app_id'=>$this->app_id,'stat'=>1],null,['name'=>'asc']);        
        $return_list=t('api_return')->find(['app_id'=>$this->app_id,'stat'=>1],null,['name'=>'asc']);

        $cat_result=$params_result=$return_result=$api_result=[];

        foreach($cat_list as $cat) $cat_result[$cat['id']]=$cat['name'];
        foreach($params_list as $params) $params_result[$params['api_id']][]=$params;
        foreach($return_list as $return) $return_result[$return['api_id']][]=$return;
        
        foreach ($api_list as $api) {
            $api_id=$api['id'];
            // 类别名称
            $cat=$cat_result[$api['cat_id']];
            // 组装请求参数
            $api['params']=$params_result[$api['id']];
            // 组装返回参数
            $api['return']=$return_result[$api['id']];

            $example=t('api_example')->get(['stat'=>1,'api_id'=>$api_id]);

            !empty($example) && $example=$example['code'];

            $api['example']=$example;

            $api['side_url']='/app/http#'.$api['id'];
            // 按类别分组
            $api_result[$cat][]=$api;
        }

        return $api_result;
    }
}