<?php

class IndexController extends BaseAuth {

    /**
     * 选中bar
     * @var boolean
     */
    protected $bar_test=true;
    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $api_list=$this->getSideBar();

        foreach ($api_list as $cat) {
            
            $test=$cat[0];

            $current=$test['id'];

            $cases=t('test_case')->find(['stat'=>1,'test_id'=>$test['id']]);

            break;
        }
        
        $this->display("test/detail.html",['api_list'=>$api_list,'current'=>$current,'test'=>$test,'cases'=>$cases,'title'=>'测试用例','tab_selected'=>'test']);
    }

    /**
     * 指定的某个api接口
     * @param array $params 接收GET请求数组
     * @return 
     */
    public function detail($params){

        $test_id=$params['id'];

        empty($test_id) && exit('测试用例不存在');

        $api_list=$this->getSideBar();

        $test=t('test')->getById($test_id);

        $cases=t('test_case')->find(['test_id'=>$test_id,'stat'=>1]);

        $current=$test_id;

        $this->display("test/detail.html",['api_list'=>$api_list,'test'=>$test,'cases'=>$cases,'current'=>$current,'title'=>'测试用例','tab_selected'=>'test']);
    }

    /**
     * 新增测试用例
     * @param 
     */
    public function add($params){

        $m_api=require_model('api');

        $cats=$this->_get_test_cat();
        $apis=$m_api->getAllCatApi();
        // print_r($apis);exit;
        $this->display("test/add.html",['tab_selected'=>'test','cats'=>json_encode($cats),'apis'=>$apis,'title'=>'新增测试用例']);
    }

    /**
     * api编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $test_id=$params['id'];

        $test=t('test')->getById($test_id);

        $cases=t('test_case')->find(['test_id'=>$test_id,'stat'=>1]);

        foreach ($cases as $case) {
            
            !empty($case['api_params']) && $case['api_params']=json_decode($case['api_params'],true);

            $test['cases'][]=$case;
        }

        $m_cat=require_model('cat');
        $m_api=require_model('api');

        $cats=$this->_get_test_cat();
        $apis=$m_api->getAllCatApi();

        $this->display("test/edit.html",['tab_selected'=>'test','cats'=>json_encode($cats),'apis'=>$apis,'test'=>json_encode($test,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),'title'=>'编辑测试用例']);
    }

    /**
     * 保存测试用例
     * @param  array $params 
     * @return 
     */
    public function save($params){

        $title=$params['title'];
        $remark=$params['remark'];
        $cat_id=$params['cat_id'];
        $cases=$params['cases'];
        $test_id=$params['id'];
        $is_add=$params['add'];

        if(empty($title)) exit(json_encode(['errno'=>-1,'errmsg'=>'测试标题不能为空']));
        if(empty($cat_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'测试类别不能为空']));
        if(empty($cases)) exit(json_encode(['errno'=>-1,'errmsg'=>'测试用例不能为空']));

        $contents=array_filter(array_column($cases, 'content'));
        if(empty($contents)) exit(json_encode(['errno'=>-1,'errmsg'=>'测试用例不能为空']));

        try{

            $db=DB::init();

            $db->start();

            if($test_id){

                t('test')->update(['title'=>$title,'cat_id'=>$cat_id,'remark'=>$remark],['id'=>$test_id]);
            }elseif($is_add){

                t('test')->insert(['title'=>$title,'cat_id'=>$cat_id,'remark'=>$remark]);
            }else{

                throw new Exception('不支持的操作');
            }

            // params
            $case_exists=array_filter(array_column($params['cases'], 'id'));

            if(!empty($case_exists)){

                t('test_case')->update(['stat'=>0],['id'=>['$non'=>$case_exists],'test_id'=>$test_id]);
            }

            foreach ($params['cases'] as $name=>$case) {

                $case_data=['test_id'=>$test_id,'content'=>$case['content'],'api_id'=>$case['api_id'],'api_params'=>$case['api_params'],'stat'=>1];

                if($case['id']){

                    t('test_case')->update($case_data,['id'=>$case['id']]);
                }else{

                    t('test_case')->insert($case_data);
                }
            }

            $db->commit();

            exit(json_encode(['errno'=>0,'redirect'=>'/test/detail/'.$test_id.'#'.$test_id]));

        }catch(Exception $e){

            $db->rollback();

            exit(json_encode(['errno'=>-1,'errmsg'=>$e->getMessage()]));
        }
    }

    /**
     * 删除指定测试
     * @param  array $params 
     * @return 
     */
    public function del($params){

        $test_id=$params['id'];

        if(empty($test_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定测试']));

        t('test')->update(['stat'=>0],['id'=>$test_id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    /**
     * 获取api参数
     * @return json
     */
    public function api_params($params){

        $api_id=$params['api_id'];

        $params=t('api_params')->find(['stat'=>1,'api_id'=>$api_id]);

        exit(json_encode($params));
    }

    /**
     * 执行测试用例
     * @return
     */
    public function run($params){

        header('Content-Type:application/json');

        $env=$params['env'];
        $test_case_id=$params['test_case_id'];
        empty($env) && $env='dev';

        $m_test=require_model('test');
        $m_api=require_model('api');

        $test_case=t('test_case')->getById($test_case_id);

        $api_id=$test_case['api_id'];
        $params=$test_case['api_params'];

        if(empty($api_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未关联API接口']));

        $api_detail=$m_api->getApi($api_id);

        if(empty($api_detail)) exit(json_encode(['errno'=>-1,'errmsg'=>'关联API接口不存在']));

        $result=run($env,$api_detail['url'],json_decode($params,true));

        $result=json_encode(json_decode($result,true),JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit(json_encode(['errno'=>0,'errmsg'=>'','data'=>$result]));
    }

    private function getSideBar(){

        $test_list=t('test')->find(['stat'=>1]);
        $test_cat=$this->_get_test_cat();

        $cat_list=$api_list=[];

        foreach ($test_cat as $cat) $cat_list[$cat['id']]=$cat['name'];

        foreach ($test_list as $test) {

            $test['side_url']='/test/detail/'.$test['id'].'#'.$test['id'];
            $test['code']=$test['id'];

            $api_list[$cat_list[$test['cat_id']]][]=$test;
        }

        return $api_list;
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/app/cat/'.self::CAT_TYPE_TEST_CASE,'click'=>'redirectPage(this)'],
            ['name'=>'新增测试用例','url'=>'/test/add','click'=>'redirectPage(this)']
        ];
    }

    /**
     * 获取test关联的cat
     * @return array
     */
    private function _get_test_cat(){

        return t('cat')->find(['type'=>self::CAT_TYPE_TEST_CASE,'stat'=>1]);
    }
}