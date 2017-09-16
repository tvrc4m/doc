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

    public function display($html,$api_list,$tab_selected){

        include_once(VIEW.'common/header.html');

        $footer=file_get_contents(VIEW.'common/footer.html');
        
        $content=include_once(VIEW.$html);

        exit($content); 
    }
}