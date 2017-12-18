<?php

class IndexController extends Base{

    /**
     * 不显示header navbar
     * @var boolean
     */
    protected $show_header=true;

    public function index(){

        $this->css[]='/static/css/pricing.css';

        $this->display('common/home.html');
    }
}