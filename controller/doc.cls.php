<?php

class DocController extends Doc {

    protected $data=[];

    public function __construct(){

        // $this->data['upload']=include_once(VIEW.'common/upload.html');
        $this->data['tab_selected']='doc';
        $this->data['api_list']=$this->getDocList();
    }

    public function index($params){

        $doc=$params['doc'];

        $this->display("doc/{$doc}.html",$this->data);
    }

    public function pdf($params){

        $doc=$params['doc'];

        $filename="{$doc}.pdf";

        $url="/static/doc/".$filename;

        $this->data['filename']=$filename;
        $this->data['url']=$url;

        $this->display("doc/pdf.html",$this->data);
    }

    private function getDocList(){

        return [
            '开发文档'=>[
                'develop'=>['name'=>'开发规范','url'=>'/doc/develop#develop'],
                'workflow'=>['name'=>'工作流','url'=>'/doc/workflow#workflow']
            ],
            'APP与JS交互文档'=>[
                'app2js'=>['name'=>'app与js交互','url'=>'/doc/app2js#app2js'],
            ],
            '文档'=>[
                
            ]
        ];
    }
}