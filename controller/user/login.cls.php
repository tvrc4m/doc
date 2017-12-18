<?php

class LoginController extends Base{

    protected $bar_login=true;

    public function index($params){

        if($this->user_id) go("/user/");

        $this->css[]="/static/css/login.css";

        $back=empty($params['back'])?'':urldecode($params['back']);

        $this->display("common/login.html",['back'=>$back]);
    }

    /**
     * 登陆
     * @param  array $params 
     * @return 
     */
    public function do($params){

        $nick=$params['nick'];
        $pwd=$params['pwd'];

        if(empty($nick)) $this->error('昵称不能为空');
        if(empty($pwd)) $this->error('密码不能为空');
        if(strlen($pwd)<6) $this->error('密码最小长度为6位');

        $user=t('user')->get(['nick'=>$nick,'stat'=>1],'id,nick,pwd');

        if(empty($user)) $this->error('用户不存在或者等待审核中');

        if(md5(sha1($pwd))!=$user['pwd']) $this->error('密码不正确');

        $_SESSION['token']=$user['id'];

        $this->ok();
    }
}