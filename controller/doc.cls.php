<?php

class DocController extends Doc {

    public function index($params){

        $doc=$params['doc'];

        $api_list=$this->getDocList();

        $this->display("doc/{$doc}.html",['api_list'=>$api_list,'tab_selected'=>'doc']);
    }

    private function getDocList(){

        return [
            '开发文档'=>[
                'develop'=>['name'=>'开发规范','url'=>'/doc/develop#develop'],
                'workflow'=>['name'=>'工作流','url'=>'/doc/workflow#workflow']
            ],
            'APP与JS交互文档'=>[
                'app2js'=>['name'=>'app与js交互','url'=>'/doc/app2js#app2js'],
            ]
        ];
    }
}