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
     * 是否显示header的login部分
     * @var boolean
     */
    protected $show_header_login=true;

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

    public function __construct(){
        // 加载全局css和js
        $this->css=['/static/css/main.min.css','/static/js/fancybox/jquery.fancybox.css'];
        $this->js=['/static/js/jquery.min.js'];

        // 获取登陆用户id
        $this->user_id=$_SESSION['token'];
    }

    /**
     * 要呈现的html
     * @param  string $html 文件路径
     * @param  array $data  要传递的数组
     * @return 
     */
    protected function display($html,$data){

        $actions=$this->actions();

        $data['actions']=$actions;
        $data['show_header']=$this->show_header;
        $data['show_header_login']=$this->show_header_login;
        $data['css']=$this->css;
        $data['js']=$this->js;
        $data['user_id']=$this->user_id;
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
     * @param  int $errno  错误码
     * @param  string|array $errmsg 错误文本
     * @return json
     */
    protected function error($errno,$errmsg){

        header('Content-type: application/json');

        exit(json_encode(['errono'=>$errno,'errmsg'=>$errmsg]));
    }

    /**
     * json--成功输出
     * @param  返回的数据 $data 
     * @return json
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
    public function fetch($page,$data){

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

            exit('未定义属性:'.$name);
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

        empty($this->user_id) && header("Location:/login");

        $user=t('user')->get(['stat'=>1,'id'=>$this->user_id]);

        if(empty($user)) header("Location:/login");

        $this->user_id=$user['id'];
        $this->user=$user;

        $_SESSION['user']=$user;
        // print_r($user);exit;
        if(!$this->skip_cert_auth && in_array($user['cert_status'], [self::USER_CERT_STATUS_NONE,self::USER_CERT_STATUS_ERR])) 
            header("Location:/account/cert/index");
    }
}