<?php

class HttpController extends Api {

    public function index($params){

        $api_list=$this->getApiList('http');

        $this->display('http/index.html',['api_list'=>$api_list,'tab_selected'=>'http']);
    }

    public function get($params){

        $key=$params['key'];

        $api=$this->getApi($key);
        
        exit(json_encode($api));
    }

    /**
     * 执行
     * @return 
     */
    public function run($params){

        $env=$params['env'];

        empty($env) && $env='dev';

        $url=$params['url'];

        unset($params['url']);
        unset($params['env']);

        $result=run($dev,$url,$params);

        echo $result;
        
        exit;
    }
}