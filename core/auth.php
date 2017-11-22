<?php

class Auth {

    public function __construct(){

        $user_id=$_SESSION['token'];
        
        empty($user_id) && header("Location:/login");

        $sql="SELECT * FROM kf_user WHERE stat=1 AND id=".intval($user_id);

        $db=new DB();

        $user=$db->get($sql);

        if(empty($user)) header("Location:/login");

        $_SESSION['user']=$user;
    }
}