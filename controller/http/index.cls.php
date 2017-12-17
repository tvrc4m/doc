<?php

class IndexController extends BaseAuth {

    /**
     * 选中bar
     * @var boolean
     */
    protected $bar_http=true;

    public function index($params){

        $api_list=$this->getApiList('http');
        
        $cats=$this->_get_http_cat();

        $this->display('http/index.html',['api_list'=>$api_list,'test_env'=>$test_env,'cats'=>$cats,'title'=>'发起请求','tab_selected'=>'http']);
    }

    public function get($params){

        $api_id=$params['id'];

        $m_api=require_model('api');

        $api=$m_api->getApiParams($api_id);
        
        exit(json_encode($api));
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

        if($user_http_id){

            t('user_http')->update(['api_params'=>json_encode($api_params),'api_return'=>$api_return],['id'=>$user_http_id,'user_id'=>$this->user_id]);
        }else{

            if(empty($api_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未关联接口id']));
            if(empty($title)) exit(json_encode(['errno'=>-1,'errmsg'=>'未设置标题']));
            if(empty($cat_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'类别不能为空']));

            t('user_http')->insert(['user_id'=>$this->user_id,'title'=>$title,'cat_id'=>$cat_id,'api_id'=>$api_id,'api_params'=>json_encode($api_params),'api_return'=>$api_return,'is_public'=>$is_public]);
        }

        exit(json_encode(['errno'=>0]));
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
        $cat_list=$this->_get_api_cat();
        $api_list=t('api')->find(['stat'=>1]);
        $params_list=t('api_params')->find(['stat'=>1],null,['name'=>'asc']);        
        $return_list=t('api_return')->find(['stat'=>1],null,['name'=>'asc']);

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

            // empty($example) && $example=$this->_getApiExampleByApi($api);

            $api['example']=$example;

            if($action=='api'){

                $api['side_url']='/api#'.$api['code'];
            }elseif($action=='http'){
                $api['side_url']='/http#'.$api['code'];
            }
            // 按类别分组
            $api_result[$cat][]=$api;
        }

        return $api_result;
    }

    /**
     * 获取doc关联的cat
     * @return array
     */
    private function _get_api_cat(){

        return t('cat')->find(['type'=>self::CAT_TYPE_API,'stat'=>1]);
    }

    /**
     * 获取http关联的cat
     * @return array
     */
    private function _get_http_cat(){

        return t('cat')->find(['type'=>self::CAT_TYPE_HTTP,'stat'=>1]);
    }
}