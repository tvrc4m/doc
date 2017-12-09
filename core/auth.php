<?php

class Auth {

    protected $user_id;

    public function __construct(){

        $user_id=$_SESSION['token'];
        
        empty($user_id) && header("Location:/login");

        $db=db();

        $user=$db->get('kf_user',['stat'=>1,'id'=>$user_id]);

        if(empty($user)) header("Location:/login");

        $this->user_id=$user['id'];

        $_SESSION['user']=$user;
    }
}