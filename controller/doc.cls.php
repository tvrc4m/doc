<?php

class DocController extends Doc {

    /**
     * 开发规范
     * @return 
     */
    public function develop(){

        $api_list=$this->getDocList();

        $this->display('doc/develop.html',['api_list'=>$api_list,'tab_selected'=>'doc']);
    }

    public function workflow(){

        $api_list=$this->getDocList();

        $this->display('doc/workflow.html',['api_list'=>$api_list,'tab_selected'=>'workflow']);
    }

    private function getDocList(){

        return [
            '开发文档'=>[
                'develop'=>['name'=>'开发规范','url'=>'/doc/develop#develop'],
                'workflow'=>['name'=>'工作流','url'=>'/doc/workflow#workflow']
            ]
        ];
    }
}