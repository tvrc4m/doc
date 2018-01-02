<?php

class AppController extends BaseAuth{

    /**
     * 选中我的navbar
     * @var boolean
     */
    protected $bar_my_app=true;
    // 调用actions方法
    protected $call_method_actions=true;
    // 禁止跳转到account/cert/index
    protected $skip_cert_auth=true;
    // 隐藏left bar
    // protected $hide_left_bar=true;

    public function index(){

        $app=t('user_app')->find(['user_id'=>$this->user_id,'stat'=>1]);

        if(empty($app)) $this->call_method_actions=false;

        $title='我的应用';

        $this->display('account/app/index.html',['app_list'=>$app,'title'=>$title]);
    }

    /**
     * 添加应用信息
     * @param 
     */
    public function add($params){
        // 检查是否能添加app
        if(!$this->check_add_app()) go('/account/app/');

        $this->call_method_actions=false;

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

        if(empty($name)) $this->error('应用名称不能为空');
        if(empty($test_env)) $this->error('测试环境不能为空');

        array_walk($test_env, function($value){
            if(empty($value['name'])) $this->error('测试环境名称不能为空');
            if(empty($value['url'])) $this->error('测试环境域名不能为空');
        });

        try{

            $db=DB::init();

            $db->start();

            $app_data=['name'=>$name,'remark'=>$remark,'user_id'=>$this->user_id,'company_id'=>$this->company_id];

            if($app_id){

                t('user_app')->update($app_data,['id'=>$app_id]);
            }elseif($is_add){

                $app_id=t('user_app')->insert($app_data);

                t('user')->update(['app_count'=>['$inc'=>1]],['id'=>$this->user_id]);
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

            $this->ok(['redirect'=>'/account/app/'.$app_id]);

        }catch(Exception $e){

            $db->rollback();

            $this->error($e->getMessage());
        }
    }
    /**
     * 删除指定应用
     * @param  array $params 
     * @return 
     */
    public function del($params){

        $id=$params['id'];

        if(empty($id)) $this->error('未指定应用');

        t('user_app')->update(['stat'=>0],['id'=>$id]);

        $this->ok();
    }

    public function actions(){

        if(!$this->call_method_actions) return [];
        // 检测是否到达应用限制
        if(!$this->check_add_app()) return [];

        return [
            ['name'=>'新增应用','url'=>'/account/app/add','click'=>'redirectPage(this)']
        ];
    }

    /**
     * 检查权限
     * @return boolean
     */
    public function check_add_app(){

        if($this->user['company_id']){

            $user_setting=t('user_setting')->get(['company_id'=>$this->user['company_id']]);
        }else{
            $user_setting=t('user_setting')->get(['user_id'=>$this->user_id]);
        }
        // 检测是否到达应用限制
        if($user_setting['app_count']<=$this->user['app_count']) return false;

        return true;
    }
}