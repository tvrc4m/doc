<?php

class RegisterController{

    public function index($params){

        include_once(VIEW."common/register.html");
    }

    /**
     * 登陆
     * @param  array $params 
     * @return 
     */
    public function do($params){

        $realname=$params['realname'];
        $nick=$params['nick'];
        $pwd=$params['pwd'];

        if(empty($nick)) exit(json_encode(['errno'=>-1,'errmsg'=>'昵称不能为空']));
        if(empty($pwd)) exit(json_encode(['errno'=>-1,'errmsg'=>'密码不能为空']));
        if(strlen($pwd)<6) exit(json_encode(['errno'=>-1,'errmsg'=>'密码最小长度为6位']));

        $db=DB::init();

        $user=$db->get('kf_user',['nick'=>$nick,'stat'=>1],['nick','pwd']);

        if(!empty($user)) exit(json_encode(['errno'=>-1,'errmsg'=>'用户已存在']));

        $pwd=md5(sha1($pwd));

        $db->insert('kf_user',['nick'=>$nick,'realname'=>$realname,'pwd'=>$pwd,'stat'=>0,'create_date'=>date('Y-m-d H:i:s')]);

        $_SESSION['token']=$user_id;

        $_SESSION['user']=['id'=>$user_id,'nick'=>$nick,'realname'=>$realname];

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }
}