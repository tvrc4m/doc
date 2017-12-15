<?php

class AppController extends BaseAuth{

    public function index(){

        $app=t('user_app')->find(['user_id'=>$this->user_id,'stat'=>1]);

        $title='我的应用';

        $this->display('account/app/index.html',['app_list'=>$app,'title'=>$title]);
    }

    /**
     * 添加应用信息
     * @param 
     */
    public function add($params){

        $this->display('account/app/add.html',[]);
    }

    /**
     * 编辑应用
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $app_id=$params['id'];

        $app=t('user_app')->get(['id'=>$app_id,'stat'=>1]);

        $app['test_env']=t('user_app_test_env')->find(['app_id'=>$app_id,'stat'=>1]);

        $this->display('account/app/edit.html',['app'=>json_encode($app)]);
    }

    /**
     * 保存应用信息
     * @param  array $params 
     * @return 
     */
    public function save($params){

        $name=$params['name'];
        $remark=$params['remark'];
        $test_env=$params['test_env'];
        $app_id=$params['id'];
        $is_add=$params['is_add'];

        if(empty($name)) exit(json_encode(['errno'=>-1,'errmsg'=>'应用名称不能为空']));
        if(empty($test_env)) exit(json_encode(['errno'=>-1,'errmsg'=>'测试环境不能为空']));

        array_walk($test_env, function($value){
            if(empty($value['name'])) exit(json_encode(['errno'=>-1,'errmsg'=>'测试环境名称不能为空']));
            if(empty($value['url'])) exit(json_encode(['errno'=>-1,'errmsg'=>'测试环境域名不能为空']));
        });

        try{

            $db=DB::init();

            $db->start();

            $app_data=['name'=>$name,'remark'=>$remark,'user_id'=>$this->user_id,'company_id'=>$this->company_id];

            if($app_id){

                t('user_app')->update($app_data,['id'=>$app_id]);
            }elseif($is_add){

                $app_id=t('user_app')->insert($app_data);
            }else{

                throw new Exception('不支持的操作');
            }
            
            // params
            $test_env_exists=array_filter(array_column($params['test_env'], 'id'));

            if(!empty($test_env_exists)){

                t('user_app_test_env')->update(['stat'=>0],['id'=>['$non'=>$test_env_exists],'app_id'=>$app_id]);
            }

            foreach ($test_env as $name=>$env) {

                $env_data=['app_id'=>$app_id,'user_id'=>$this->user_id,'name'=>$env['name'],'url'=>$env['url'],'is_default'=>intval($env['is_default']),'stat'=>1];
                // print_r($env_data);
                if($env['id']){

                    t('user_app_test_env')->update($env_data,['id'=>$env['id']]);
                }else{

                    t('user_app_test_env')->insert($env_data);
                }
            }

            $db->commit();

            exit(json_encode(['errno'=>0,'redirect'=>'/account/app/'.$app_id]));

        }catch(Exception $e){

            $db->rollback();

            exit(json_encode(['errno'=>-1,'errmsg'=>$e->getMessage()]));
        }
    }
    /**
     * 删除指定应用
     * @param  array $params 
     * @return 
     */
    public function del($params){

        $id=$params['id'];

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定应用']));

        t('user_app')->update(['stat'=>0],['id'=>$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    public function actions(){

        // TODO::检测是否到达应用限制

        return [
            ['name'=>'新增应用','url'=>'/account/app/add','click'=>'redirectPage(this)']
        ];
    }
}