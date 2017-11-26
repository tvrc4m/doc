<?php

class TestController extends Api {

    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $api_list=$this->getSideBar();

        $m_test=require_model('test');

        foreach ($api_list as $cat) {
            
            $test=$cat[0];

            $current=$test['id'];

            $cases=$m_test->getTestCase($current);

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

        $m_test=require_model('test');

        $api_list=$this->getSideBar();

        $test=$m_test->getTest($test_id);

        $cases=$m_test->getTestCase($test_id);

        $current=$test_id;

        $this->display("test/detail.html",['api_list'=>$api_list,'test'=>$test,'cases'=>$cases,'current'=>$current,'tab_selected'=>'test']);
    }

    /**
     * 新增测试用例
     * @param 
     */
    public function add($params){

        $m_cat=require_model('cat');
        $m_api=require_model('api');

        $cats=$m_cat->getCatsByType(CAT_TYPE_TEST_CASE);
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

        $m_test=require_model('test');

        $test=$m_test->getTest($test_id);

        $cases=$m_test->getTestCase($test_id);

        foreach ($cases as $case) {
            
            !empty($case['api_params']) && $case['api_params']=json_decode($case['api_params'],true);

            $test['cases'][]=$case;
        }

        $m_cat=require_model('cat');
        $m_api=require_model('api');

        $cats=$m_cat->getCatsByType(CAT_TYPE_TEST_CASE);
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

            $m_test=require_model('test');

            $m_test->start();

            if($test_id){

                $m_test->updateTest($test_id,$title,$cat_id,$remark);

            }elseif($is_add){

                $test_id=$m_test->addTest($title,$cat_id,$remark);

            }else{

                throw new Exception('不支持的操作');
            }

            // params
            $case_exists=array_filter(array_column($params['cases'], 'id'));

            if(!empty($case_exists)){

                $sql="UPDATE kf_test_case SET stat=0 WHERE id NOT IN (".implode(',', $case_exists).") AND test_id=?";

                $m_test->update($sql,'i',[$test_id]);
            }

            foreach ($params['cases'] as $name=>$case) {

                if($case['id']){

                    $m_test->updateTestCase($test_id,$case['id'],$case['content'],$case['api_id'],$case['api_params'],1);
                }else{

                    $m_test->addTestCase($test_id,$case['content'],$case['api_id'],$case['api_params']);
                }
            }

            $m_test->commit();

            exit(json_encode(['errno'=>0,'redirect'=>'/test/detail/'.$test_id.'#'.$test_id]));

        }catch(Exception $e){

            $m_test->rollback();

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

        $sql="UPDATE kf_test SET stat=0 WHERE id=?";

        $db=new DB();

        $db->update($sql,'i',[$test_id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    /**
     * 获取api参数
     * @return json
     */
    public function api_params($params){

        $api_id=$params['api_id'];

        $m_api=require_model('api');

        $params=$m_api->getApiRequestParams($api_id);

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

        $test_case=$m_test->getTestCaseDetail($test_case_id);

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

        $m_test=require_model('test');
        $m_cat=require_model('cat');

        $test_list=$m_test->getAllTest();
        $test_cat=$m_cat->getCatsByType(CAT_TYPE_TEST_CASE);

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
            ['name'=>'类别管理','url'=>'/test/cat','click'=>'redirectPage(this)'],
            ['name'=>'新增测试用例','url'=>'/test/add','click'=>'redirectPage(this)']
        ];
    }
}