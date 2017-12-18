<?php

class ApiController extends BaseAuth {

    /**
     * 选中bar
     * @var boolean
     */
    protected $bar_api=true;
    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $commonapi=file_get_contents(ROOT.'/static/json/api.json');
        $api_common=json_decode($commonapi,true);

        $api_list=$this->getApiList();
        
        $this->display("api/content.html",['api_list'=>$api_list,'api_common'=>$api_common,'title'=>'APP接口文档','tab_selected'=>'app']);
    }

    /**
     * 添加api接口
     * @param array $params 
     */
    public function add($params){

        $cats=$this->_get_api_cat();

        $versions=$this->_get_api_version();

        $this->display("api/add.html",['tab_selected'=>'app','versions'=>json_encode($versions),'cats'=>json_encode($cats),'title'=>'添加APP接口文档']);
    }

    /**
     * api编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $api_id=$params['id'];

        $api=$this->m_api->getApiDetail($api_id);

        $api['cats']=$this->_get_api_cat();

        $api['versions']=$this->_get_api_version();
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

        if(!$is_add && empty($id)) $this->error('未指定接口');

        if(empty($title)) $this->error('接口标题不能为空');
        if(empty($url)) $this->error('接口地址不能为空');
        if(empty($cat_id)) $this->error('接口类型不能为空');
        
        foreach ($params as $name=>$param) {
            if(empty($name) || is_numeric($name))  $this->error('请求参数名不能为空或为数字');
            if(empty($param['type']))  $this->error('请求参数类型不能为空');
        }

        // 返回参数检测
        foreach ($return as $name=>$param) {
            if(empty($name) || is_numeric($name))  $this->error('返回参数名不能为空或为数字');
            if(empty($param['type']))  $this->error('返回参数类型不能为空');
        }

        list($app,$controller,$action)=explode('/', $url);

        try{

            $db=DB::init();

            $db->start();

            $api_data=['app_id'=>$this->app_id,'title'=>$title,'url'=>$url,'cat_id'=>$cat_id,'version'=>$version,'remark'=>$remark];

            if($id){

                t('api')->update($api_data,['id'=>$id]);
            }elseif($is_add){

                $id=t('api')->insert($api_data);
            }else{

                throw new Exception('不支持的操作');
            }

            // params
            $params_exists=array_filter(array_column($params, 'id'));

            if(!empty($params_exists)){

                t('api_params')->update(['stat'=>0],['id'=>['$non'=>$params_exists],'api_id'=>$id]);
            }

            foreach ($params as $name=>$param) {

                $api_params_data=['app_id'=>$this->app_id,'api_id'=>$id,'name'=>$name,'type'=>$param['type'],'must'=>intval($param['must']),'version'=>$param['version'],'remark'=>$param['remark']];

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

                $api_ret_data=['app_id'=>$this->app_id,'api_id'=>$id,'name'=>$name,'type'=>$ret['type'],'must'=>intval($ret['must']),'version'=>$ret['version'],'remark'=>$ret['remark']];

                if($ret['id']){

                    t('api_return')->update($api_ret_data,['id'=>$ret['id']]);
                }else{

                    t('api_return')->insert($api_ret_data);
                }
            }

            $db->commit();

            exit(json_encode(['errno'=>0,'redirect'=>'/app/api#'.$id]));

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

        $api=t('api')->getById($api_id);

        $tests=$this->m_test->getApiTestCast($api_id);

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
            ['name'=>'类别管理','url'=>'/app/cat/'.self::CAT_TYPE_API,'click'=>'redirectPage(this)'],
            ['name'=>'APP版本管理','url'=>'/api/version','click'=>'redirectPage(this)'],
            ['name'=>'新增接口','url'=>'/app/api/add','click'=>'redirectPage(this)']
        ];
    }

    /**
     * 获取接口列表
     * @param  页面方法 $action 
     * @return array
     */
    public function getApiList(){
        // 获取API类别
        $cat_list=$this->_get_api_cat();
        $api_list=t('api')->find(['app_id'=>$this->app_id,'stat'=>1]);
        $params_list=t('api_params')->find(['app_id'=>$this->app_id,'stat'=>1],null,['name'=>'asc']);        
        $return_list=t('api_return')->find(['app_id'=>$this->app_id,'stat'=>1],null,['name'=>'asc']);

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

            $example=t('api_example')->get(['stat'=>1,'api_id'=>$api['id']]);

            !empty($example) && $example=$example['code'];

            empty($example) && $example=$this->_getApiExampleByApi($api);

            $api['example']=$example;

            $api['side_url']='/app/api#'.$api['id'];
            // 按类别分组
            $api_result[$cat][]=$api;
        }

        return $api_result;
    }

    /**
     * 通过参数组装事例对象
     * @param  array $apidata 
     * @return string
     */
    private function _getApiExampleByApi($apidata){
       
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

    /**
     * 获取doc关联的cat
     * @return array
     */
    private function _get_api_cat(){

        return t('cat')->find(['type'=>self::CAT_TYPE_API,'app_id'=>$this->app_id,'stat'=>1]);
    }

    /**
     * 获取api版本
     * @return array
     */
    private function _get_api_version(){

        return t('app_version')->find(['stat'=>1,'app_id'=>$this->app_id]);
    }
}