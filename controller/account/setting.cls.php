<?php

class SettingController extends BaseAuth{

    public function index(){

        $this->css[]='/static/css/login.css';

        $data=[];

        $this->display('account/setting.html',$data);
    }
}