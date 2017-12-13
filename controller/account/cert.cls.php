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
    protected $show_header=false;

    public function __construct(){

        parent::__construct();

        $this->css[]='/static/css/certification.css';
        $this->css[]='/static/css/certificate.css';
        $this->css[]='/static/css/login.css';
    }

    /**
     * 身份认证
     * @return
     */
    public function index(){

        if($this->user['cert_status']==self::USER_CERT_STATUS_OK) header('/user');
        if($this->user['cert_status']==self::USER_CERT_STATUS_CHECK) header('/account/cert/verify');

        $data=['cert_status'=>$this->user['cert_status'],'phone'=>$this->user['phone'],'email'=>$this->user['email'],'realname'=>$this->user['realname'],'card_no'=>$this->user['card_no'],'user_type'=>$this->user['is_company']==1?2:1];

        $this->display('account/cert.html',$data);
    }

    /**
     * 审核状态
     * @return 
     */
    public function verify(){

        $cert_status=$this->user['cert_status'];

        $this->display('account/verify.html',['cert_status'=>$cert_status]);
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
        $email=$params['email'];
        $idcard=$params['idcard'];
        $company=$params['company'];
        // $company=$params['company'];
        if($user_type!=self::USER_TYPE_COMPANY && $user_type!=self::USER_TYPE_PERSION) $this->error(-1,'不支持该用户类型');
        if(empty($realname)) $this->error(-1,'真实姓名不能为空');
        if(empty($phone)) $this->error(-1,'手机号不能为空');
        if(!preg_match('/(\d{11})/', $phone)) $this->error(-1,'手机号格式不对');
        if(empty($email)) $this->error(-1,'邮箱不能为空');
        if(!preg_match('/^[^@]+@\w+\.\w+/', $email)) $this->error(-1,'邮箱格式不对'); #TODO::正则待优化

        $db=DB::init();

        try{

            $db->start();

            $is_admin=$is_company=$company_id=0;

            if($user_type==self::USER_TYPE_COMPANY){

                $is_admin=$is_company=1;
                $company_id=t('company')->insert(['name'=>$company]);
            }
            
            t('user')->update(['realname'=>$realname,'phone'=>$phone,'email'=>$email,'card_no'=>$idcard,'is_admin'=>$is_admin,'is_company'=>$is_company,'company_id'=>$company_id,'cert_status'=>self::USER_CERT_STATUS_CHECK],['id'=>$this->user_id]);

            $db->commit();
        }catch(Exception $e){

            $db->rollback();

            $this->error(-1,'操作异常,请稍后操作');
        }

        $this->ok();
    }
}