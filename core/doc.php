<?php

class Doc {

    const DOC_TYPE_APP  =   1;  // app接口文档
    const DOC_TYPE_PC   =   2;  // pc接口文档

    /**
     * 接口文档类型
     * @var int
     */
    protected $doc_type;

    protected $header;

    /**
     * footer
     * @var string
     */
    protected $footer;

    /**
     * api数据
     * @var array
     */
    protected $json;

    public function display($html,$data){

        $actions=$this->actions();

        $data['actions']=$actions;

        include_once(VIEW.'common/header.html');

        include_once(VIEW.$html);

        include_once(VIEW.'common/footer.html');
    }

    public function actions(){

        return [];
    }
}