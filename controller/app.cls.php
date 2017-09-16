<?php

class AppController extends Api {

    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $this->export(self::API_TYPE_APP);
    }

    /**
     * 指定的某个api接口
     * @param array $params 接收GET请求数组
     * @return 
     */
    public function detail($params){

        $api=$params['api'];

        empty($api) && exit('api接口地址不存在');

        $this->export(self::API_TYPE_APP,$api);
    }
}