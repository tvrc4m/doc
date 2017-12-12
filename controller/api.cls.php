<?php

class ApiController extends Api {

    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $api_common=$this->getCommonApi();

        $api_list=$this->getApiList('api');
        
        $this->display("api/content.html",['api_list'=>$api_list,'api_common'=>$api_common,'title'=>'APP接口文档','tab_selected'=>'app']);
    }

    /**
     * 添加api接口
     * @param array $params 
     */
    public function add($params){

        $cats=$this->getCatByType(self::CAT_TYPE_API);

        $versions=$this->getAppVersion();

        $this->display("api/add.html",['tab_selected'=>'app','versions'=>json_encode($versions),'cats'=>json_encode($cats),'title'=>'添加APP接口文档']);
    }

    /**
     * api编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $code=$params['code'];

        $api=$this->getApi($code);

        $api['cats']=$this->getCatByType(self::CAT_TYPE_API);

        $api['versions']=$this->getAppVersion();
        // print_r($cat_list);exit;
        $this->display("api/edit.html",['tab_selected'=>'app','api'=>json_encode($api,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),'title'=>'编辑APP接口文档']);
    }

    /**
     * 编辑保存功能
     * @param  array $params 
     * @return 
     */
    public function save($data){

        $id=$data['id'];
        $is_add=$data['add'];
        $title=$data['title'];
        $url=$data['url'];
        $cat_id=$data['cat_id'];
        $params=$data['params'];
        $return=$data['return'];
        $version=$data['version'];
        $remark=$data['remark'];

        if(!$is_add && empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定接口']));

        if(empty($title)) exit(json_encode(['errno'=>-1,'errmsg'=>'接口标题不能为空']));
        if(empty($url)) exit(json_encode(['errno'=>-1,'errmsg'=>'接口地址不能为空']));
        if(empty($cat_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'接口类型不能为空']));
        
        foreach ($params as $name=>$param) {
            if(empty($name) || is_numeric($name))  exit(json_encode(['errno'=>-1,'errmsg'=>'请求参数名不能为空或为数字']));
            if(empty($param['type']))  exit(json_encode(['errno'=>-1,'errmsg'=>'请求参数类型不能为空']));
        }

        // 返回参数检测
        foreach ($return as $name=>$param) {
            if(empty($name) || is_numeric($name))  exit(json_encode(['errno'=>-1,'errmsg'=>'返回参数名不能为空或为数字']));
            if(empty($param['type']))  exit(json_encode(['errno'=>-1,'errmsg'=>'返回参数类型不能为空']));
        }

        list($app,$controller,$action)=explode('/', $url);

        $code=strtolower($controller.'_'.$action);

        try{

            $db=DB::init();

            $db->start();

            $api_data=['title'=>$title,'url'=>$url,'cat_id'=>$cat_id,'code'=>$code,'version'=>$version,'remark'=>$remark];

            if($id){

                t('api')->update($api_data,['id'=>$id]);
            }elseif($is_add){

                t('api')->insert($api_data);
            }else{

                throw new Exception('不支持的操作');
            }

            // params
            $params_exists=array_filter(array_column($params, 'id'));

            if(!empty($params_exists)){

                t('api_params')->update(['stat'=>0],['id'=>['$non'=>$params_exists],'api_id'=>$id]);
            }

            foreach ($params as $name=>$param) {

                $api_params_data=['name'=>$name,'type'=>$param['type'],'must'=>intval($param['must']),'version'=>$param['version'],'remark'=>$param['remark']];

                if($param['id']){

                    t('api_params')->update($api_params_data,['id'=>$param['id']]);
                }else{

                    t('api_params')->insert($api_params_data);
                }
            }

            // return
            $return_exists=array_filter(array_column($return, 'id'));
            
            if(!empty($return_exists)){

                t('api_return')->update(['stat'=>0],['id'=>['$non'=>$return_exists],'api_id'=>$id]);
            }

            foreach ($return as $name=>$ret) {

                $api_ret_data=['name'=>$name,'type'=>$ret['type'],'must'=>intval($ret['must']),'version'=>$ret['version'],'remark'=>$ret['remark']];

                if($ret['id']){

                    t('api_return')->update($api_ret_data,['id'=>$ret['id']]);
                }else{

                    t('api_return')->insert($api_ret_data);
                }
            }

            $db->commit();

            exit(json_encode(['errno'=>0,'redirect'=>'/api#'.$code]));

        }catch(Exception $e){

            $db->rollback();

            exit(json_encode(['errno'=>-1,'errmsg'=>$e->getMessage()]));
        }
    }

    /**
     * 获取接口关联的测试用例
     * @return 
     */
    public function case($params){

        $api_id=$params['id'];

        $m_api=require_model("api");
        $m_test=require_model("test");

        $api=$m_api->getApi($api_id);

        $tests=$m_test->getApiTestCast($api_id);

        $this->display("api/case.html",['api'=>$api,'tests'=>$tests,'tab_selected'=>'app']);
    }

    /**
     * 保存example
     * @params array
     * @return 
     */
    public function example($params){

        $code=$params['code'];
        $api_id=$params['api_id'];

        $example=t('api_example')->get(['stat'=>1,'api_id'=>$api_id]);

        if(empty($example)){

            t('api_example')->insert(['api_id'=>$api_id,'code'=>$code]);
        }else{

            t('api_example')->insert(['code'=>$code],['id'=>$example['id']]);
        }

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/api/cat','click'=>'redirectPage(this)'],
            ['name'=>'APP版本管理','url'=>'/version','click'=>'redirectPage(this)'],
            ['name'=>'新增接口','url'=>'/api/add','click'=>'redirectPage(this)']
        ];
    }
}