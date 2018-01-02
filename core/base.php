<?php

class Base{

    /**
     * cat类型分组
     */
    const CAT_TYPE_API=1;
    const CAT_TYPE_DOC=2;
    const CAT_TYPE_TEST_CASE=3;
    const CAT_TYPE_HTTP=4;
    const CAT_TYPE_CLASS_PHP=5;

    /**
     * 用户身份
     */
    const USER_TYPE_PERSION=1; #个人
    const USER_TYPE_COMPANY=2; #企业

    /**
     * 用户认证状态
     */
    const USER_CERT_STATUS_NONE=0; #未认证
    const USER_CERT_STATUS_CHECK=1; #正在审核中
    const USER_CERT_STATUS_OK=2; #认证通过
    const USER_CERT_STATUS_ERR=3; #认证失败

    const API_PARAMS_TYPE_INT=1;
    const API_PARAMS_TYPE_STRING=2;
    const API_PARAMS_TYPE_BOOLEAN=3;
    const API_PARAMS_TYPE_OBJECT=4;
    const API_PARAMS_TYPE_ARRAY=5;

    public $params_type=[
        self::API_PARAMS_TYPE_INT=>'int',
        self::API_PARAMS_TYPE_STRING=>'string',
        self::API_PARAMS_TYPE_BOOLEAN=>'boolean',
        self::API_PARAMS_TYPE_OBJECT=>'object',
        self::API_PARAMS_TYPE_ARRAY=>'array',
    ];

    /**
     * header page content
     * @var string
     */
    protected $header;

    /**
     * footer page content
     * @var string
     */
    protected $footer;

    /**
     * 要传递的js文件
     * @var array
     */
    protected $js=[];

    /**
     * 要传递的css文件
     * @var array
     */
    protected $css=[];

    /**
     * 是否显示header
     * @var boolean
     */
    protected $show_header=true;

    /**
     * 是否显示left bar
     * @var boolean
     */
    protected $hide_left_bar=false;

    /**
     * 选中的样式名
     * @var string
     */
    protected $bar_selected='active';

    /**
     * 登陆用户id
     * @var integer
     */
    protected $user_id=0;

    /**
     * 公司id
     * @var integer
     */
    protected $company_id=0;

    /**
     * 当前选择的应用id
     * @var int
     */
    protected $app_id=0;

    public function __construct(){
        // 加载全局css和js
        $this->css=['/static/css/main.min.css','/static/js/fancybox/jquery.fancybox.css'];
        $this->js=['/static/js/jquery.min.js','/static/js/fancybox/jquery.fancybox.pack.js','/static/js/global.js'];

        // 获取登陆用户id
        $this->user_id=$_SESSION['token'];
    }

    /**
     * 要呈现的html
     * @param  string $html 文件路径
     * @param  array $data  要传递的数组
     * @return 
     */
    protected function display($html,$data=[]){

        $actions=$this->actions();

        $data['actions']=$actions;
        $data['show_header']=$this->show_header;
        $data['show_header_login']=$this->show_header_login;
        $data['css']=$this->css;
        $data['js']=$this->js;
        $data['user_id']=$this->user_id;
        $data['navbar']['left']=$this->getLeftNavBar();
        $data['navbar']['right']=$this->getRightNavBar();
        // print_r($data);exit;
        include_once(VIEW.'common/header.html');

        include_once(VIEW.$html);

        include_once(VIEW.'common/footer.html');
    }

    /**
     * 支持的操作列表
     * @return array
     */
    protected function actions(){

        return [];
    }

    /**
     * json--输出错误
     * @param  string|array $errmsg 错误文本
     * @param  int $errno  错误码
     * @return json
     */
    protected function error($errmsg,$errno=-1){

        header('Content-type: application/json');

        exit(json_encode(['errono'=>$errno,'errmsg'=>$errmsg]));
    }

    /**
     * json--成功输出
     * @param  返回的数据 $data 
     * @return json 0 表示正常
     */
    protected function ok($data=[]){

        header('Content-type: application/json');

        exit(json_encode(['errno'=>0,'errmsg'=>'','data'=>$data]));
    }

    /**
     * 获取某个页面
     * @param  string $page
     * @param  array $data
     * @return string
     */
    public function fetch($page,$data=[]){

        return include_once(VIEW.$page);
    }

    /**
     * get test html
     * @param   $page [description]
     * @return [type]       [description]
     */
    public function getTestHtml($app_id){

        $test_env=$this->m_app->getAppTestEnv($app_id);

        $this->fetch('http/env.html',['test_env'=>$test_env]);
    }
    /**
     * 调用model方法
     * @param  string $name 
     * @return 
     */
    public function __get($name){
        // 如果以m_开头，则作为model调用
        if(strncmp($name,'m_',2)==0){

            return require_model(substr($name,2));
        }else{

            // exit('未定义属性:'.$name);
        }
    }

    protected function getLeftNavBar(){

        if($this->hide_left_bar) return [];

        if($this->user_id){

            return  
            [
                ['name'=>'接口文档','url'=>'/app/api','selected'=>$this->bar_api?$this->bar_selected:'','children'=>[]],
                ['name'=>'发起请求','url'=>'/app/http','selected'=>$this->bar_http?$this->bar_selected:'','children'=>[]],
                ['name'=>'测试用例','url'=>'/app/test','selected'=>$this->bar_test?$this->bar_selected:'','children'=>[]],
            ];
        }

        return 
        [
            ['name'=>'首页','url'=>'/','selected'=>$this->bar_home?$this->bar_selected:''],
            ['name'=>'价格','url'=>'/account/price/index','selected'=>$this->bar_price?$this->bar_selected:'']
        ];
    }

    protected function getRightNavBar(){

        if($this->user_id){

            return 
            [
                ['name'=>'我的应用','url'=>'/http','selected'=>$this->bar_my_app?$this->bar_selected:'','children'=>[
                    ['name'=>'看法app','url'=>'/http/my/index'],
                    ['name'=>'yicker','url'=>'/account/app/index'],
                ]], 
                ['name'=>'我发起的请求','url'=>'/http/my/','selected'=>$this->bar_my?$this->bar_selected:'','children'=>[]], 
                ['name'=>'价格','url'=>'/account/price/index','selected'=>$this->bar_price?$this->bar_selected:''],
                ['name'=>'设置','url'=>'/http','selected'=>$this->bar_setting?$this->bar_selected:'','children'=>[]],
                ['name'=>'@'.$_SESSION['user']['nick'],'selected'=>$this->bar_self?$this->bar_selected:'','children'=>[
                    ['name'=>'退出','url'=>'/user/logout','selected'=>'','children'=>[]],        
                ]],
           ];
        }

        return 
        [
            ['name'=>'注册','url'=>'/user/register','selected'=>$this->bar_register?$this->bar_selected:'','children'=>[]], 
            ['name'=>'登陆','url'=>'/user/login','selected'=>$this->bar_login?$this->bar_selected:'','children'=>[]]
       ];
    }

    /**
     * 获取操作按钮的权限及显示隐藏
     * @return 
     */
    protected function getActionRole(){


    }

    protected function call_in_trans(callable $trans,array $params){

        try{

            $db=DB::init();

            $db->start();

            $data=call_user_func_array($trans, $params);

            $db->commit();

            $this->ok((array)$data);
        }catch(Exception $e){

            $db->rollback();

            if(DEBUG) $this->error($e->getMessage());

            $this->error("执行失败,请稍后再试");
        }
    }
}

/**
 * 需要登陆的基类
 */
class BaseAuth extends Base{

    protected $user;
    /**
     * 是否跳过身份认证过程
     * @var boolean
     */
    protected $skip_cert_auth=false;

    public function __construct(){

        parent::__construct();

        $request='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        empty($this->user_id) && go("/user/login?back=".urlencode($request));

        $user=t('user')->get(['stat'=>1,'id'=>$this->user_id]);

        if(empty($user)) go("/user/login?back=".urlencode($request));

        $this->user_id=$user['id'];
        $this->company_id=$user['company_id'];
        $this->user=$user;

        $_SESSION['user']=$user;

        // if(!$user['app_count'] && $user['cert_status']==self::USER_CERT_STATUS_OK && !$this->skip_cert_auth){
        //     go('/account/app/index');
        // }

        // if((!$user['app_count'] || in_array($user['cert_status'], [self::USER_CERT_STATUS_NONE,self::USER_CERT_STATUS_ERR])) && 
        //     !$this->skip_cert_auth){ 
        //     $this->hide_left_bar=true;
        //     go('/account/cert/index');
        // }

        $this->get_user_app();
    }

    /**
     * 获取用户的app_id
     * @return 
     */
    public function get_user_app(){

        $user_app=$this->m_app->get_user_app($this->user_id,$this->user['company_id']);
        
        if(empty($user_app)){

            $this->hide_left_bar=true;

            return;
        }

        $this->app_id=$user_app['id'];
    }
}