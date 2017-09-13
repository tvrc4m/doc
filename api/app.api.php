<?php

class AppApi extends Api {

    protected $json='app.api.json';

    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $this->export($this->json);
    }

    /**
     * 指定的某个api接口
     * @param array $params 接收GET请求数组
     * @return 
     */
    public function detail($params){

        $api=$params['api'];

        empty($api) && exit('api接口地址不存在');

        $this->export($this->json,$api);
    }
}