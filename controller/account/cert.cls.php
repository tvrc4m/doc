<?php

class CertController extends BaseAuth{

    /**
     * 跳过身份认证
     * @var boolean
     */
    protected $skip_cert_auth=true;
    /**
     * 不显示header
     * @var boolean
     */
    protected $show_header=true;

    public function __construct(){

        parent::__construct();

        $this->css[]='/static/css/certification.css';
        $this->css[]='/static/css/certificate.css';
        $this->css[]='/static/css/login.css';
        $this->js[]='/static/js/jquery.ui.widget.js';
        $this->js[]='/static/js/jquery.fileupload.js';
    }

    /**
     * 身份认证
     * @return
     */
    public function index(){

        if($this->user['cert_status']==self::USER_CERT_STATUS_OK) go('/user');
        if($this->user['cert_status']==self::USER_CERT_STATUS_CHECK) go('/account/cert/verify');

        $data=['cert_status'=>$this->user['cert_status'],'phone'=>$this->user['phone'],'email'=>$this->user['email'],'realname'=>$this->user['realname'],'card_no'=>$this->user['card_no'],'user_type'=>$this->user['is_company']==1?2:1];

        $this->display('account/cert.html',$data);
    }

    /**
     * 审核状态
     * @return 
     */
    public function verify(){

        $cert_status=$this->user['cert_status'];

        switch ($cert_status) {
            case self::USER_CERT_STATUS_NONE:
                go("/account/cert/index");break;
            case self::USER_CERT_STATUS_CHECK:
                $cert_text='正在审核中...即将完成审核,请耐心等待<br>如果比较着急发邮件提醒我们: tvrc4m@itsvk.com';break;
            case self::USER_CERT_STATUS_OK:
                $cert_text='恭喜你完成身份认证<br><a class="btn-u" href="/account/app/add">创建应用</a>';break;
            case self::USER_CERT_STATUS_ERR:
                $cert_text='认证失败,请重新填写身份信息<br><a class="btn-u" href="/account/cert/index">重新修改</a>';break;
        }

        $this->display('account/verify.html',['cert_text'=>$cert_text]);
    }

    /**
     * 完善基本信息
     * @return
     */
    public function info($params){

        $user_type=$params['user_type'];
        $realname=$params['realname'];
        $phone=$params['phone'];
        $phoneCode=$params['phoneCode'];
        $idcard=$params['idcard'];
        $company=$params['company'];
        $licence_no=$params['license'];
        // $company=$params['company'];
        if($user_type!=self::USER_TYPE_COMPANY && $user_type!=self::USER_TYPE_PERSION) $this->error('不支持该用户类型');
        if(empty($realname)) $this->error('真实姓名不能为空');
        if(empty($phone)) $this->error('手机号不能为空');
        if(!preg_match('/(\d{11})/', $phone)) $this->error('手机号格式不对');

        $db=DB::init();

        try{

            $db->start();

            $is_admin=$is_company=$company_id=0;

            if($user_type==self::USER_TYPE_COMPANY){

                $is_admin=$is_company=1;
                $company_id=t('company')->insert(['name'=>$company,'licence_no'=>$licence_no]);
            }
            
            t('user')->update(['realname'=>$realname,'phone'=>$phone,'card_no'=>$idcard,'is_admin'=>$is_admin,'is_company'=>$is_company,'company_id'=>$company_id,'cert_status'=>self::USER_CERT_STATUS_CHECK],['id'=>$this->user_id]);
            // 默认免费使用15天,支持15个用户，可创建100个api接口,支持一个应用
            t('user_setting')->insert(['user_id'=>$this->user_id,'company_id'=>$company_id,'app_count'=>1,'api_count'=>100,'user_count'=>15,'start_date'=>date('Y-m-d H:i:s'),'valid_day'=>15]);

            $db->commit();
        }catch(Exception $e){

            $db->rollback();

            $this->error('操作异常,请稍后操作');
        }

        $this->ok(['redirect'=>'/account/cert/verify']);
    }

    protected function getLeftNavBar(){

        return [];
    }

    /**
     * 重新定义右边的navbar
     * @return array
     */
    protected function getRightNavBar(){

        return 
        [
            ['name'=>'价格','url'=>'/account/price/index','selected'=>$this->bar_price?$this->bar_selected:''],
            ['name'=>'@'.$_SESSION['user']['nick'],'selected'=>$this->bar_self?$this->bar_selected:'','children'=>[
                ['name'=>'退出','url'=>'/user/logout','selected'=>'','children'=>[]],        
            ]],
       ];
    }
}