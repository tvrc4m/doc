<?php

class LoginController extends Base{

    protected $bar_login=true;

    public function index($params){

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

        if(empty($nick)) exit(json_encode(['errno'=>-1,'errmsg'=>'昵称不能为空']));
        if(empty($pwd)) exit(json_encode(['errno'=>-1,'errmsg'=>'密码不能为空']));
        if(strlen($pwd)<6) exit(json_encode(['errno'=>-1,'errmsg'=>'密码最小长度为6位']));

        $user=t('user')->get(['nick'=>$nick,'stat'=>1],'id,nick,pwd');

        if(empty($user)) exit(json_encode(['errno'=>-1,'errmsg'=>'用户不存在或者等待审核中']));

        if(md5(sha1($pwd))!=$user['pwd']) exit(json_encode(['errno'=>-1,'errmsg'=>'密码不正确']));

        $_SESSION['token']=$user['id'];

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }
}