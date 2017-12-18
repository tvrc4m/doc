<?php

/**
 * 退出登录
 */
class LogoutController{

    public function index(){

        $_SESSION['token']=null;

        session_destroy();

        go("/");
    }
}