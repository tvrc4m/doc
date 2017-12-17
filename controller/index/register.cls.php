<?php

class RegisterController extends Base{

    protected $bar_register=true;

    const EMAIL_REG='/[^@]+@\w+\.\w+/';

    public function index($params){

        $this->css[]="/static/css/login.css";

        $this->display("common/register.html");
    }

    /**
     * 发送确认邮件
     * @param  array $params 
     * @return 
     */
    public function email($params){

        $email=$params['email'];

        if(empty($email)) $this->error("邮箱不能为空");
        if(preg_match(self::EMAIL_REG, $email)==false) $this->error('邮箱格式不正确');

        $_SESSION['code']=$code=rand_str(6);

        $this->ok(['code'=>$code]);
    }

    /**
     * 登陆
     * @param  array $params 
     * @return 
     */
    public function do($params){

        $email=$params['email'];
        $code=$params['code'];
        $nick=$params['nick'];
        $pwd=$params['pwd'];

        if(empty($email)) $this->error('邮箱不能为空');
        if(preg_match(self::EMAIL_REG, $email)==false) $this->error('邮箱格式不正确');
        if(empty($code)) $this->error("验证码不能为空");
        if($_SESSION['code']!==$code) $this->error("验证码不正确");
        if(empty($nick)) $this->error('昵称不能为空');
        if(empty($pwd)) $this->error('密码不能为空');
        if(strlen($pwd)<6) $this->error('密码最小长度为6位');

        $user=t('user')->get(['nick'=>$nick,'stat'=>1],['nick','pwd']);

        if(!empty($user)) $this->error('用户已存在');

        $pwd=md5(sha1($pwd));

        $user_id=t('user')->insert(['nick'=>$nick,'email'=>$email,'pwd'=>$pwd,'stat'=>1]);

        if(empty($user_id)) $this->error("注册失败");

        $_SESSION['token']=$user_id;

        $_SESSION['user']=['id'=>$user_id,'nick'=>$nick,'email'=>$email];

        $this->ok();
    }
}