<?php

class DocController extends Doc {

    /**
     * 开发规范
     * @return 
     */
    public function develop(){

        $api_list=['开发文档'=>['develop'=>['name'=>'开发规范','url'=>'/doc/develop#develop']]];

        $this->display('doc/develop.html',['api_list'=>$api_list,'tab_selected'=>'doc']);
    }
}