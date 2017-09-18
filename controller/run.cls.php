<?php

class RunController extends Api {

    public function index($params){

        $key=$params['key'];

        $json=$this->getJsonByKey('app',$key);

        $request=$json['params'];

        print_r($request);
    }

    /**
     * 执行
     * @return 
     */
    public function exec(){


    }
}